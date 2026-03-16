<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\employee\LeaveBalance;
use App\Models\employee\LeaveRequest;
use App\Models\employee\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaveController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $user = auth()->user();
        $year = now()->year;

        // Leave types visible to this employee
        $leaveTypes = LeaveType::active()
            ->visibleTo($user)
            ->orderBy('name')
            ->get();

        // Balances keyed by leave_type_id
        $balances = LeaveBalance::where('user_id', $user->id)
            ->where('year', $year)
            ->get()
            ->keyBy('leave_type_id');

        // Seed missing balance rows from legacy user columns (first-time use)
        foreach ($leaveTypes as $lt) {
            if (! $balances->has($lt->id)) {
                $entitled = $this->legacyBalance($user, $lt);
                $bal = LeaveBalance::create([
                    'user_id'           => $user->id,
                    'leave_type_id'     => $lt->id,
                    'year'              => $year,
                    'entitled_days'     => $entitled,
                    'carried_over_days' => 0,
                    'used_days'         => 0,
                    'pending_days'      => 0,
                    'balance'           => $entitled,
                ]);
                $balances->put($lt->id, $bal);
            }
        }

        // History with filters
        $history = LeaveRequest::with(['leaveType', 'reviewer'])
            ->forUser($user->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'),   fn ($q) => $q->where('leave_type_id', $request->type))
            ->orderByDesc('created_at')
            ->get();

        // Collect all individual dates covered by pending/approved requests
        $filedDates = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get(['start_date', 'end_date'])
            ->flatMap(function ($lr) {
                $dates = [];
                $cur   = $lr->start_date->copy();
                while ($cur->lte($lr->end_date)) {
                    $dates[] = $cur->format('Y-m-d');
                    $cur->addDay();
                }
                return $dates;
            })
            ->unique()
            ->values();

        // NEW: Get rest days from the active schedule template
        $restDaysArray = $this->getRestDaysArray($user->id);

        return view('employee.leave', compact('user', 'leaveTypes', 'balances', 'history', 'year', 'filedDates', 'restDaysArray'));
    }

    // ──────────────────────────────────────────────────────────────
    //  STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date', 'after_or_equal:start_date'],
            'reason'        => ['required', 'string', 'max:2000'],
        ]);

        $user      = auth()->user();
        $year      = now()->year;
        $leaveType = LeaveType::active()->visibleTo($user)->findOrFail($data['leave_type_id']);

        // Count working days (excluding user's day-offs based on schedule)
        $days = $this->countWorkingDays($user->id, $data['start_date'], $data['end_date']);

        if ($days < 1) {
            return back()
                ->withInput()
                ->with('error', 'Your selected date range contains no working days.');
        }

        // Guard: overlapping request
        $overlap = LeaveRequest::where('user_id', $user->id)
            ->where('leave_type_id', $data['leave_type_id'])
            ->whereIn('status', ['pending', 'approved'])
            ->overlapping($data['start_date'], $data['end_date'])
            ->exists();

        if ($overlap) {
            return back()
                ->withInput()
                ->with('error', 'You already have a pending or approved request overlapping those dates.');
        }

        // Guard: balance
        $balance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $data['leave_type_id'])
            ->where('year', $year)
            ->first();

        if ($balance && $days > $balance->balance) {
            return back()
                ->withInput()
                ->with('error', "Insufficient balance. You have {$balance->balance} day(s) available for {$leaveType->name}.");
        }

        // Create leave request
        LeaveRequest::create([
            'user_id'       => $user->id,
            'leave_type_id' => $data['leave_type_id'],
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'days'          => $days,
            'reason'        => $data['reason'],
            'status'        => 'pending',
        ]);

        // Deduct from balance (pending)
        if ($balance) {
            $balance->increment('pending_days', $days);
            $balance->decrement('balance', $days);
        }

        return redirect()
            ->route('employee.leave.index')
            ->with('success', "{$leaveType->name} for {$days} day(s) submitted for approval.");
    }

    // ──────────────────────────────────────────────────────────────
    //  DESTROY  (withdraw a pending request)
    // ──────────────────────────────────────────────────────────────
    public function destroy(int $id): RedirectResponse
    {
        $user = auth()->user();

        $lr = LeaveRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $days = $lr->days;
        $ltId = $lr->leave_type_id;

        $lr->delete();

        // Restore balance
        LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $ltId)
            ->where('year', now()->year)
            ->first()?->tap(function ($bal) use ($days) {
                $bal->decrement('pending_days', $days);
                $bal->increment('balance', $days);
            });

        return redirect()
            ->route('employee.leave.index')
            ->with('success', 'Leave request withdrawn successfully.');
    }

    // ──────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────

    /**
     * Gets the rest days for the user based on their current active schedule template.
     */
    private function getRestDaysArray(string $userId): array
    {
        $todayStr = now()->toDateString();

        $activeSchedule = DB::table('user_schedules')
            ->where('user_id', $userId)
            ->where('effective_date', '<=', $todayStr)
            ->orderBy('effective_date', 'desc')
            ->first();

        if (! $activeSchedule) {
            return [0, 6]; // Default to weekends off if no schedule found
        }

        // Find all days where is_working_day is false for this template
        return DB::table('schedule_template_days')
            ->where('template_id', $activeSchedule->template_id)
            ->where('is_working_day', false)
            ->pluck('day_of_week')
            ->toArray();
    }

    /**
     * Accurately counts working days by checking each date against its respective active schedule.
     */
    private function countWorkingDays(string $userId, string $start, string $end): int
    {
        $count   = 0;
        $current = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        // Fetch all user schedules to handle cases where a leave crosses a schedule change boundary
        $schedules = DB::table('user_schedules')
            ->where('user_id', $userId)
            ->orderBy('effective_date', 'desc')
            ->get();

        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $dayOfWeek = $current->dayOfWeek;

            $activeSchedule = $schedules->firstWhere('effective_date', '<=', $dateStr);

            if ($activeSchedule) {
                $isWorkingDay = DB::table('schedule_template_days')
                    ->where('template_id', $activeSchedule->template_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->value('is_working_day');

                if ($isWorkingDay) {
                    $count++;
                }
            } else {
                // Fallback: assume Mon-Fri working days if no schedule is found
                if ($dayOfWeek !== 0 && $dayOfWeek !== 6) {
                    $count++;
                }
            }

            $current->addDay();
        }

        return $count;
    }

    private function legacyBalance($user, LeaveType $lt): float
    {
        return match (true) {
            str_contains(strtolower($lt->name), 'vacation')  => (float) $user->vacationLeaveBalance,
            str_contains(strtolower($lt->name), 'sick')      => (float) $user->sickLeaveBalance,
            str_contains(strtolower($lt->name), 'emergency') => (float) $user->emergencyLeaveBalance,
            str_contains(strtolower($lt->name), 'maternity') => (float) $user->maternityLeaveBalance,
            str_contains(strtolower($lt->name), 'paternity') => (float) $user->paternityLeaveBalance,
            default                                          => (float) $lt->max_days_per_year,
        };
    }
}