<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use App\Models\accounting\PayrollPeriod;
use App\Models\accounting\PayrollRecord;
use App\Models\employee\AttendanceRecord;
use App\Models\employee\Loan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // =========================================================================
    // PAGE
    // =========================================================================

    public function index()
    {
        return view('hresource.reports');
    }

    // =========================================================================
    // EMPLOYEE MASTER LIST
    // =========================================================================

    public function employeeMasterlist(Request $request): JsonResponse
    {
        $employees = User::active()
            ->orderBy('fullName')
            ->get([
                'id', 'fullName', 'position', 'department', 'branch',
                'employmentStatus', 'hireDate', 'gender',
                'email', 'phoneNumber',
            ])
            ->map(fn ($u) => [
                'employee_id'       => $u->id,
                'full_name'         => $u->fullName,
                'position'          => $u->position          ?? '—',
                'department'        => $u->department        ?? '—',
                'branch'            => $u->branch            ?? '—',
                'employment_status' => $u->employmentStatus  ?? '—',
                'hire_date'         => $u->hireDate?->format('M d, Y') ?? '—',
                'gender'            => $u->gender            ?? '—',
                'email'             => $u->email             ?? '—',
                'phone'             => $u->phoneNumber       ?? '—',
            ]);

        return response()->json($employees);
    }

    // =========================================================================
    // DTR SUMMARY
    // =========================================================================

    public function dtr(Request $request): JsonResponse
    {
        $request->validate([
            'year'   => ['required', 'integer', 'min:2000'],
            'month'  => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff' => ['required', 'in:full,first,second'],
        ]);

        $year   = (int) $request->year;
        $month  = (int) $request->month;
        $cutoff = $request->cutoff;

        $query = AttendanceRecord::with('user:id,fullName,department')
            ->forMonth($year, $month)
            ->orderBy('date')
            ->orderBy('user_id');

        if ($cutoff === 'first') {
            $query->whereDay('date', '<=', 15);
        } elseif ($cutoff === 'second') {
            $query->whereDay('date', '>=', 16);
        }

        $records = $query->get()->map(fn ($r) => [
            'employee_id'       => $r->user_id,
            'employee'          => $r->user?->fullName   ?? '—',
            'department'        => $r->user?->department ?? '—',
            'date'              => $r->date?->format('M d, Y'),
            'time_in'           => $r->time_in           ?? '—',
            'time_out'          => $r->time_out          ?? '—',
            'hours_worked'      => number_format($r->hours_worked, 2),
            'late_minutes'      => (int) $r->late_minutes,
            'undertime_minutes' => (int) $r->undertime_minutes,
            'overtime_hours'    => number_format($r->overtime_hours, 2),
            'status'            => $r->status_label,
            'is_biometric'      => $r->is_biometric,
        ]);

        return response()->json($records);
    }

    // =========================================================================
    // PAYROLL REGISTER
    // =========================================================================

    public function payrollRegister(Request $request): JsonResponse
    {
        $request->validate([
            'year'   => ['required', 'integer', 'min:2000'],
            'month'  => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff' => ['required', 'in:full,first,second'],
        ]);

        $year   = (int) $request->year;
        $month  = (int) $request->month;
        $cutoff = $request->cutoff;

        // Map the page's cutoff value to period_type in the DB
        // 'full' returns records across both periods for the month
        $periodTypes = match ($cutoff) {
            'first'  => ['1st-15th'],
            'second' => ['16th-end'],
            default  => ['1st-15th', '16th-end'],
        };

        // Only show released/closed payroll — draft/processing data is not a report
        $periods = PayrollPeriod::whereIn('period_type', $periodTypes)
            ->where('month', $month)
            ->where('year',  $year)
            ->whereIn('status', ['released', 'closed'])
            ->pluck('id');

        if ($periods->isEmpty()) {
            return response()->json([]);
        }

        $records = PayrollRecord::with('employee:id,fullName,department,position')
            ->whereIn('payroll_period_id', $periods)
            ->orderBy('user_id')
            ->get()
            ->map(fn ($r) => [
                'employee_id'          => $r->user_id,
                'employee'             => $r->employee?->fullName  ?? '—',
                'department'           => $r->employee?->department ?? '—',
                'position'             => $r->employee?->position   ?? '—',
                // Earnings
                'basic_pay'            => number_format($r->basic_pay,             2),
                'overtime_pay'         => number_format($r->overtime_pay,          2),
                'night_diff_pay'       => number_format($r->night_diff_pay,        2),
                'holiday_pay'          => number_format($r->holiday_pay,           2),
                'rest_day_pay'         => number_format($r->rest_day_pay,          2),
                'leave_pay'            => number_format($r->leave_pay,             2),
                'allowances'           => number_format($r->allowances,            2),
                'gross_pay'            => number_format($r->gross_pay,             2),
                // Deductions
                'sss'                  => number_format($r->sss,                   2),
                'philhealth'           => number_format($r->philhealth,            2),
                'pagibig'              => number_format($r->pagibig,               2),
                'withholding_tax'      => number_format($r->withholding_tax,       2),
                'late_deductions'      => number_format($r->late_deductions,       2),
                'undertime_deductions' => number_format($r->undertime_deductions,  2),
                'absent_deductions'    => number_format($r->absent_deductions,     2),
                'other_deductions'     => number_format($r->other_deductions,      2),
                'total_deductions'     => number_format($r->total_deductions,      2),
                'net_pay'              => number_format($r->net_pay,               2),
                'status'               => $r->status,
            ]);

        return response()->json($records);
    }

    // =========================================================================
    // LOANS (shared for SSS and PAG-IBIG)
    // =========================================================================

    public function loans(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:sss,pagibig'],
        ]);

        $records = Loan::with(['employee:id,fullName,department'])
            ->withSum('payments', 'amount')   // payments_sum_amount
            ->withCount('payments')            // payments_count
            ->where('loan_type', $request->type)
            ->orderBy('status')               // active first
            ->orderBy('created_at')
            ->get()
            ->map(function ($loan) {
                $totalPaid        = (float) ($loan->payments_sum_amount ?? 0);
                $remainingBalance = max(0, (float) $loan->amount - $totalPaid);
                $paymentsMade     = (int)  ($loan->payments_count ?? 0);

                return [
                    'employee_id'          => $loan->user_id,
                    'employee'             => $loan->employee?->fullName  ?? '—',
                    'department'           => $loan->employee?->department ?? '—',
                    'loan_type_name'       => $loan->loan_type_name,
                    'amount'               => number_format($loan->amount, 2),
                    'monthly_amortization' => number_format($loan->monthly_amortization, 2),
                    'term_months'          => $loan->term_months,
                    'payments_made'        => $paymentsMade,
                    'remaining_balance'    => number_format($remainingBalance, 2),
                    'start_date'           => $loan->start_date?->format('M d, Y')       ?? '—',
                    'completed_date'       => $loan->completed_date?->format('M d, Y')   ?? '—',
                    'status'               => ucfirst($loan->status),
                ];
            });

        return response()->json($records);
    }
}