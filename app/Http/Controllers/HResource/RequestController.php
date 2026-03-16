<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use App\Models\employee\LeaveRequest;
use App\Models\employee\OvertimeRequest;
use App\Models\ProfileUpdateRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // =========================================================================
    // PAGE
    // =========================================================================

    public function index()
    {
        return view('hresource.requests.show');
    }

    // =========================================================================
    // STATS
    // =========================================================================

    public function pendingCounts(): JsonResponse
    {
        $leave    = LeaveRequest::pending()->count();
        $overtime = OvertimeRequest::pending()->count();
        $profile  = ProfileUpdateRequest::where('status', 'pending')->count();

        return response()->json([
            'leave'    => $leave,
            'overtime' => $overtime,
            'profile'  => $profile,
            'total'    => $leave + $overtime + $profile,
        ]);
    }

    // =========================================================================
    // TAB DATA
    // =========================================================================

    public function leaveRequests(): JsonResponse
    {
        $records = LeaveRequest::with(['user', 'leaveType'])
            ->pending()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'employee_id'  => $r->user_id,
                'employee'     => $r->user?->fullName ?? '—',
                'leave_type'   => $r->leaveType?->name ?? '—',
                'start_date'   => $r->start_date?->format('M d, Y'),
                'end_date'     => $r->end_date?->format('M d, Y'),
                'days'         => $r->days,
                'reason'       => $r->reason,
                'attachments'  => $r->attachments ?? [],
                'submitted_at' => $r->created_at?->format('M d, Y'),
            ]);

        return response()->json($records);
    }

    public function overtimeRequests(): JsonResponse
    {
        $records = OvertimeRequest::with('employee')
            ->pending()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'              => $r->id,
                'employee_id'     => $r->user_id,
                'employee'        => $r->employee?->fullName ?? '—',
                'date'            => Carbon::parse($r->date)->format('M d, Y'),
                'ot_type'         => $r->ot_type,
                'hours'           => $r->hours,
                'rate_multiplier' => $r->rate_multiplier,
                'estimated_pay'   => number_format($r->estimated_pay, 2),
                'reason'          => $r->reason,
                'submitted_at'    => $r->created_at?->format('M d, Y'),
            ]);

        return response()->json($records);
    }

    public function profileRequests(): JsonResponse
    {
        $records = ProfileUpdateRequest::where('status', 'pending')
            ->orderByDesc('submittedDate')
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'employee_id'  => $r->employeeId,
                'employee'     => $r->employeeName,
                'field'        => $r->field,
                'old_value'    => $r->oldValue ?? '—',
                'new_value'    => $r->newValue,
                'reason'       => $r->reason,
                'submitted_at' => $r->submittedDate
                    ? Carbon::parse($r->submittedDate)->format('M d, Y')
                    : '—',
            ]);

        return response()->json($records);
    }

    public function history(Request $request): JsonResponse
    {
        $type   = $request->query('type', 'all');
        $limit  = 30;
        $result = collect();

        if (in_array($type, ['all', 'leave'], true)) {
            $result = $result->concat(
                LeaveRequest::with(['user', 'leaveType', 'reviewer'])
                    ->whereIn('status', ['approved', 'rejected'])
                    ->orderByDesc('reviewed_at')
                    ->limit($limit)
                    ->get()
                    ->map(fn ($r) => [
                        'id'               => $r->id,
                        'category'         => 'leave',
                        'category_label'   => 'Leave',
                        'employee'         => $r->user?->fullName ?? '—',
                        'employee_id'      => $r->user_id,
                        'detail'           => ($r->leaveType?->name ?? '—') . ' · ' . $r->days . ' day(s)',
                        'period'           => $r->start_date?->format('M d') . ' – ' . $r->end_date?->format('M d, Y'),
                        'status'           => $r->status,
                        'reviewed_by'      => $r->reviewer?->fullName ?? '—',
                        'reviewed_at'      => $r->reviewed_at?->format('M d, Y'),
                        'rejection_reason' => $r->rejection_reason,
                        '_sort_date'       => $r->reviewed_at?->timestamp ?? 0,
                    ])
            );
        }

        if (in_array($type, ['all', 'overtime'], true)) {
            $result = $result->concat(
                OvertimeRequest::with(['employee', 'reviewer'])
                    ->whereIn('status', ['approved', 'rejected', 'paid'])
                    ->orderByDesc('reviewed_at')
                    ->limit($limit)
                    ->get()
                    ->map(fn ($r) => [
                        'id'               => $r->id,
                        'category'         => 'overtime',
                        'category_label'   => 'Overtime',
                        'employee'         => $r->employee?->fullName ?? '—',
                        'employee_id'      => $r->user_id,
                        'detail'           => $r->ot_type . ' · ' . $r->hours . 'h · ₱' . number_format($r->estimated_pay, 2),
                        'period'           => Carbon::parse($r->date)->format('M d, Y'),
                        'status'           => $r->status,
                        'reviewed_by'      => $r->reviewer?->fullName ?? '—',
                        'reviewed_at'      => $r->reviewed_at?->format('M d, Y'),
                        'rejection_reason' => $r->rejection_note,
                        '_sort_date'       => $r->reviewed_at?->timestamp ?? 0,
                    ])
            );
        }

        if (in_array($type, ['all', 'profile'], true)) {
            $result = $result->concat(
                ProfileUpdateRequest::whereIn('status', ['approved', 'rejected'])
                    ->orderByDesc('reviewDate')
                    ->limit($limit)
                    ->get()
                    ->map(fn ($r) => [
                        'id'               => $r->id,
                        'category'         => 'profile',
                        'category_label'   => 'Profile',
                        'employee'         => $r->employeeName,
                        'employee_id'      => $r->employeeId,
                        'detail'           => $r->field . ': ' . ($r->oldValue ?? '—') . ' → ' . $r->newValue,
                        'period'           => '—',
                        'status'           => $r->status,
                        'reviewed_by'      => $r->reviewedBy ?? '—',
                        'reviewed_at'      => $r->reviewDate
                            ? Carbon::parse($r->reviewDate)->format('M d, Y')
                            : '—',
                        'rejection_reason' => null,
                        '_sort_date'       => $r->reviewDate
                            ? Carbon::parse($r->reviewDate)->timestamp
                            : 0,
                    ])
            );
        }

        return response()->json(
            $result->sortByDesc('_sort_date')->values()
        );
    }

    // =========================================================================
    // LEAVE ACTIONS
    // =========================================================================

    public function approveLeave(Request $request, LeaveRequest $leave): JsonResponse
    {
        if (! $leave->isPending()) {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        DB::transaction(function () use ($leave) {
            $leave->update([
                'status'      => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Reflect usage in leave_balances
            $balance = $leave->user
                ?->leaveBalances()
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $leave->start_date->year)
                ->first();

            if ($balance) {
                $newUsed    = $balance->used_days + $leave->days;
                $newPending = max(0, $balance->pending_days - $leave->days);
                $newBalance = max(0, $balance->balance - $leave->days);

                $balance->update([
                    'used_days'    => $newUsed,
                    'pending_days' => $newPending,
                    'balance'      => $newBalance,
                ]);
            }
        });

        return response()->json(['message' => 'Leave request approved.']);
    }

    public function rejectLeave(Request $request, LeaveRequest $leave): JsonResponse
    {
        $request->validate(['reason' => 'required|string|min:5|max:500']);

        if (! $leave->isPending()) {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        DB::transaction(function () use ($leave, $request) {
            // Release the pending_days hold
            $balance = $leave->user
                ?->leaveBalances()
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $leave->start_date->year)
                ->first();

            if ($balance) {
                $balance->update([
                    'pending_days' => max(0, $balance->pending_days - $leave->days),
                ]);
            }

            $leave->update([
                'status'           => 'rejected',
                'rejection_reason' => $request->reason,
                'reviewed_by'      => Auth::id(),
                'reviewed_at'      => now(),
            ]);
        });

        return response()->json(['message' => 'Leave request rejected.']);
    }

    // =========================================================================
    // OVERTIME ACTIONS
    // =========================================================================

    public function approveOvertime(Request $request, OvertimeRequest $overtime): JsonResponse
    {
        if ($overtime->status !== 'pending') {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        $overtime->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return response()->json(['message' => 'Overtime request approved.']);
    }

    public function rejectOvertime(Request $request, OvertimeRequest $overtime): JsonResponse
    {
        $request->validate(['reason' => 'required|string|min:5|max:500']);

        if ($overtime->status !== 'pending') {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        $overtime->update([
            'status'         => 'rejected',
            'rejection_note' => $request->reason,
            'reviewed_by'    => Auth::id(),
            'reviewed_at'    => now(),
        ]);

        return response()->json(['message' => 'Overtime request rejected.']);
    }

    // =========================================================================
    // PROFILE ACTIONS
    // =========================================================================

    public function approveProfile(Request $request, ProfileUpdateRequest $profile): JsonResponse
    {
        if (! $profile->isPending()) {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        if (! $profile->isFieldAllowed()) {
            return response()->json(['message' => 'This field cannot be updated via request.'], 422);
        }

        DB::transaction(function () use ($profile) {
            $user = User::find($profile->employeeId);

            if ($user) {
                $user->update([$profile->field => $profile->newValue]);
            }

            $profile->update([
                'status'     => 'approved',
                'reviewedBy' => Auth::user()->fullName,
                'reviewDate' => now(),
            ]);
        });

        return response()->json(['message' => 'Profile update approved and applied.']);
    }

    public function rejectProfile(Request $request, ProfileUpdateRequest $profile): JsonResponse
    {
        if (! $profile->isPending()) {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        $profile->update([
            'status'     => 'rejected',
            'reviewedBy' => Auth::user()->fullName,
            'reviewDate' => now(),
        ]);

        return response()->json(['message' => 'Profile update rejected.']);
    }
}