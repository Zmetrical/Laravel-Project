<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\accounting\PayrollPeriod;
use App\Models\accounting\PayrollRecord;

class PayrollRecordsController extends Controller
{
    /**
     * List all payroll records for a period.
     * Accessible from 'processing' onwards.
     *
     * GET /accounting/payroll/periods/{period}/records
     */
public function index(PayrollPeriod $period)
{
    abort_if($period->isDraft(), 403, 'No records yet — period is still in Draft.');

    $records = PayrollRecord::with('employee')
        ->where('payroll_period_id', $period->id)
        ->orderBy('user_id')
        ->get();

    $totals = [
        'gross_pay'        => $records->sum('gross_pay'),
        'total_deductions' => $records->sum('total_deductions'),
        'net_pay'          => $records->sum('net_pay'),
        'count'            => $records->count(),
    ];

    // ↓ Add this block before the return
    $recordsJson = $records->map(fn($r) => [
        'id'                   => $r->id,
        'employee_name'        => $r->employee?->fullName ?? 'Unknown',
        'employee_position'    => $r->employee?->position ?? '',
        'employee_department'  => $r->employee?->department ?? '',
        'status'               => $r->status,
        'basic_pay'            => (float) $r->basic_pay,
        'overtime_pay'         => (float) $r->overtime_pay,
        'night_diff_pay'       => (float) $r->night_diff_pay,
        'holiday_pay'          => (float) $r->holiday_pay,
        'rest_day_pay'         => (float) $r->rest_day_pay,
        'leave_pay'            => (float) $r->leave_pay,
        'additional_shift_pay' => (float) $r->additional_shift_pay,
        'allowances'           => (float) $r->allowances,
        'gross_pay'            => (float) $r->gross_pay,
        'sss'                  => (float) $r->sss,
        'philhealth'           => (float) $r->philhealth,
        'pagibig'              => (float) $r->pagibig,
        'withholding_tax'      => (float) $r->withholding_tax,
        'late_deductions'      => (float) $r->late_deductions,
        'undertime_deductions' => (float) $r->undertime_deductions,
        'absent_deductions'    => (float) $r->absent_deductions,
        'other_deductions'     => (float) $r->other_deductions,
        'deferred_balance'     => (float) $r->deferred_balance,
        'total_deductions'     => (float) $r->total_deductions,
        'net_pay'              => (float) $r->net_pay,
        'notes'                => $r->notes,
        'released_at'          => $r->released_at?->format('M d, Y'),
    ])->values();

    // ↓ Updated return — adds $recordsJson
    return view('accounting.payroll.records', compact(
        'period', 'records', 'totals', 'recordsJson'
    ));
}

    /**
     * Summary view — department breakdown + deduction totals.
     * Only accessible after period is released or closed.
     *
     * GET /accounting/payroll/periods/{period}/summary
     */
    public function summary(PayrollPeriod $period)
    {
        abort_unless($period->isReleased() || $period->isClosed(), 403, 'Summary is only available after payroll is released.');

        $records = PayrollRecord::with('employee')
            ->where('payroll_period_id', $period->id)
            ->where('status', 'released')
            ->get();

        // ── Overall totals ────────────────────────────────────────────────────
        $totals = [
            'count'                => $records->count(),
            'basic_pay'            => $records->sum('basic_pay'),
            'overtime_pay'         => $records->sum('overtime_pay'),
            'night_diff_pay'       => $records->sum('night_diff_pay'),
            'holiday_pay'          => $records->sum('holiday_pay'),
            'leave_pay'            => $records->sum('leave_pay'),
            'allowances'           => $records->sum('allowances'),
            'gross_pay'            => $records->sum('gross_pay'),
            'sss'                  => $records->sum('sss'),
            'philhealth'           => $records->sum('philhealth'),
            'pagibig'              => $records->sum('pagibig'),
            'withholding_tax'      => $records->sum('withholding_tax'),
            'late_deductions'      => $records->sum('late_deductions'),
            'undertime_deductions' => $records->sum('undertime_deductions'),
            'absent_deductions'    => $records->sum('absent_deductions'),
            'other_deductions'     => $records->sum('other_deductions'),
            'total_deductions'     => $records->sum('total_deductions'),
            'net_pay'              => $records->sum('net_pay'),
        ];

        // ── Per-department breakdown ───────────────────────────────────────────
        $byDepartment = $records
            ->groupBy(fn($r) => $r->employee?->department ?? 'Unassigned')
            ->map(fn($group, $dept) => [
                'department'       => $dept,
                'count'            => $group->count(),
                'gross_pay'        => $group->sum('gross_pay'),
                'total_deductions' => $group->sum('total_deductions'),
                'net_pay'          => $group->sum('net_pay'),
            ])
            ->sortBy('department')
            ->values();

        return view('accounting.payroll.summary', compact('period', 'totals', 'byDepartment', 'records'));
    }
}