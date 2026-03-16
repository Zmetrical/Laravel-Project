<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\employee\AttendanceRecord;
use App\Models\employee\Holiday;
use App\Models\employee\OvertimeConfiguration;
use App\Models\employee\OvertimeRate;
use App\Models\employee\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OvertimeController extends Controller
{
public function index(Request $request)
    {
        $user = Auth::user();
        $year = now()->year;

        // ── FIX: Fetch ALL attendance records for the year, not just ones with OT
        $attendanceRecords = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->get(['date', 'hours_worked', 'overtime_hours', 'time_in', 'time_out'])
            ->keyBy(fn ($r) => $r->date->format('Y-m-d'))
            ->map(fn ($r) => [
                'hours_worked'   => (float) $r->hours_worked,
                'overtime_hours' => (float) $r->overtime_hours,
                'is_ongoing'     => $r->time_in && ! $r->time_out, // Check if shift is ongoing
            ]);

        // ── Holidays: current year, keyed by date ─────────────────────────
        $holidays = Holiday::where('year', $year)
            ->get(['name', 'date', 'type'])
            ->keyBy(fn ($h) => $h->date->format('Y-m-d'))
            ->map(fn ($h) => [
                'name' => $h->name,
                'type' => $h->type,
            ]);

        // ── OT Rates: active only, keyed by name ──────────────────────────
        $overtimeRates = OvertimeRate::where('is_active', true)
            ->get(['name', 'multiplier'])
            ->keyBy('name')
            ->map(fn ($r) => (float) $r->multiplier);

        // ── OT Configuration: single-row settings ─────────────────────────
        $config = OvertimeConfiguration::first();

        // ── OT Request History ────────────────────────────────────────────
        $query = OvertimeRequest::where('user_id', $user->id)
            ->with('reviewer:id,fullName')
            ->orderByDesc('date');

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15)->withQueryString();

        // ── Flat date list for calendar "already filed" check ────────────
        $filedDates = OvertimeRequest::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereNotIn('status', ['rejected'])
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->flip(); 

        // ── Stats: server-side aggregates ─────────────────────────────────
        $approvedHours = OvertimeRequest::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('hours');

        $approvedEarnings = OvertimeRequest::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('estimated_pay');

        $pendingCount = OvertimeRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $restDaysArray = $this->getRestDaysArray($user->id);

        return view('employee.overtime', [
            'user'              => $user,
            'attendanceRecords' => $attendanceRecords,
            'holidays'          => $holidays,
            'overtimeRates'     => $overtimeRates,
            'config'            => $config,
            'requests'          => $requests,
            'filedDates'        => $filedDates,
            'restDaysArray'     => $restDaysArray,
            'stats'             => [
                'approved_hours'    => (float) $approvedHours,
                'approved_earnings' => (float) $approvedEarnings,
                'pending_count'     => (int)   $pendingCount,
            ],
            'filters' => $request->only(['from', 'to', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date'            => ['required', 'date'], // Removed past-date restriction
            'hours'           => ['required', 'numeric', 'min:0.5', 'max:24'], 
            'ot_type'         => ['required', 'string', 'max:100'],
            'rate_multiplier' => ['required', 'numeric', 'min:1'],
            'estimated_pay'   => ['required', 'numeric', 'min:0'],
            'reason'          => ['required', 'string', 'max:1000'],
        ]);

        // FIX: Removed the strict guard requiring an existing attendance record.
        // Advance filing means they might not have an attendance record yet!

        // Guard: no duplicate active request for the same date
        $exists = OvertimeRequest::where('user_id', $user->id)
            ->whereDate('date', $validated['date'])
            ->whereNotIn('status', ['rejected'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'You already have an overtime request for this date.']);
        }

        // Guard: OT limit enforcement based on requested hours
        $config = OvertimeConfiguration::first();
        if ($config && $config->enforce_limit) {
            $weekStart = now()->parse($validated['date'])->startOfWeek();
            $weekEnd   = now()->parse($validated['date'])->endOfWeek();

            $weeklyHours = OvertimeRequest::where('user_id', $user->id)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->whereNotIn('status', ['rejected'])
                ->sum('hours');

            if (($weeklyHours + $validated['hours']) > $config->weekly_max_hours) {
                return back()->withErrors([
                    'date' => "This request exceeds the weekly OT limit of {$config->weekly_max_hours} hours.",
                ]);
            }
        }

        OvertimeRequest::create([
            'user_id'         => $user->id,
            'date'            => $validated['date'],
            'hours'           => $validated['hours'],
            'ot_type'         => $validated['ot_type'],
            'rate_multiplier' => $validated['rate_multiplier'],
            'estimated_pay'   => $validated['estimated_pay'],
            'reason'          => $validated['reason'],
            'status'          => 'pending',
        ]);

        return back()->with('success', 'Overtime request submitted successfully.');
    }

    
    public function destroy(OvertimeRequest $overtimeRequest)
    {
        if ($overtimeRequest->user_id !== Auth::id()) {
            abort(403);
        }

        if (! in_array($overtimeRequest->status, ['pending', 'rejected'])) {
            return back()->withErrors(['delete' => 'Only pending requests can be cancelled.']);
        }

        $overtimeRequest->delete();

        return back()->with('success', 'Overtime request cancelled.');
    }

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
            return [0, 6]; 
        }

        return DB::table('schedule_template_days')
            ->where('template_id', $activeSchedule->template_id)
            ->where('is_working_day', false)
            ->pluck('day_of_week')
            ->toArray();
    }
}