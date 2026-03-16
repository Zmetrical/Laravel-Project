<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\accounting\PayrollRecord;
use App\Models\accounting\PayrollPeriod;
use Illuminate\Support\Facades\Auth;
use App\Models\employee\Loan;
use App\Models\employee\LoanPayment;
class PayrollController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $records = PayrollRecord::with([
                'period',
                // Eager-load each loan payment + its parent loan for the label
                'loanPayments.loan',
            ])
            ->where('user_id', $userId)
            ->where('status', 'released')
            ->whereHas('period', fn ($q) => $q->where('status', 'released'))
            ->orderByDesc(
                PayrollPeriod::select('pay_date')
                    ->whereColumn('id', 'payroll_records.payroll_period_id')
                    ->limit(1)
            )
            ->get();

        $payslips = $records->map(function (PayrollRecord $r) {
            $period = $r->period;

            // ── Attempt 1: loan payments linked to this payroll record ────
            $loanDeductions = $r->loanPayments->map(fn ($lp) => [
                'label'  => $lp->loan?->loan_type_name ?? 'Loan Deduction',
                'amount' => (float) $lp->amount,
            ])->filter(fn ($ld) => $ld['amount'] > 0)->values()->toArray();

            // ── Attempt 2: relationship empty — derive from active loans ──
            // This fires when payroll_record_id was not set on loan_payments
            // (e.g. seeder ran out of order, or loans were added after payroll).
            // We find active loans for this employee during this pay period
            // and compute the semi-monthly deduction amount directly.
            if (empty($loanDeductions) && $period) {
                $loanDeductions = Loan::where('user_id', $r->user_id)
                    ->where('status', '!=', 'cancelled')
                    ->where('start_date', '<=', $period->end_date)
                    ->where(function ($q) use ($period) {
                        $q->whereNull('completed_date')
                          ->orWhere('completed_date', '>=', $period->start_date);
                    })
                    ->get()
                    ->map(function ($loan) use ($period) {
                        // Calculate balance at start of this period
                        $paidBefore = LoanPayment::where('loan_id', $loan->id)
                            ->where('payment_date', '<', $period->start_date)
                            ->sum('amount');

                        $balanceBefore = max(0, (float) $loan->amount - (float) $paidBefore);
                        if ($balanceBefore <= 0) return null;

                        // Semi-monthly: half the monthly amortization
                        $semiMonthly = round($loan->monthly_amortization / 2, 2);
                        $amount      = min($semiMonthly, $balanceBefore);

                        return [
                            'label'  => $loan->loan_type_name,
                            'amount' => $amount,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->toArray();
            }

            return [
                'id'                   => $r->id,
                'period'               => $period?->label ?? '—',
                'period_type'          => $period?->period_type,
                'pay_date'             => $period?->pay_date?->toDateString(),
                'start_date'           => $period?->start_date?->toDateString(),
                'end_date'             => $period?->end_date?->toDateString(),

                // Earnings
                'basic_pay'            => (float) $r->basic_pay,
                'overtime_pay'         => (float) $r->overtime_pay,
                'night_diff_pay'       => (float) $r->night_diff_pay,
                'holiday_pay'          => (float) $r->holiday_pay,
                'rest_day_pay'         => (float) $r->rest_day_pay,
                'leave_pay'            => (float) $r->leave_pay,
                'additional_shift_pay' => (float) $r->additional_shift_pay,
                'allowances'           => (float) $r->allowances,
                'gross_pay'            => (float) $r->gross_pay,

                // Statutory deductions
                'sss'                  => (float) $r->sss,
                'philhealth'           => (float) $r->philhealth,
                'pagibig'              => (float) $r->pagibig,
                'withholding_tax'      => (float) $r->withholding_tax,

                // Attendance deductions
                'late_deductions'      => (float) $r->late_deductions,
                'undertime_deductions' => (float) $r->undertime_deductions,
                'absent_deductions'    => (float) $r->absent_deductions,

                // Other
                'other_deductions'     => (float) $r->other_deductions,
                'deferred_balance'     => (float) $r->deferred_balance,

                // Loan deductions — named per loan, array for the JS modal
                'loan_deductions'      => $loanDeductions,

                // Totals stored on the record
                'total_deductions'     => (float) $r->total_deductions,
                'net_pay'              => (float) $r->net_pay,

                'notes'                => $r->notes,
                'released_at'          => $r->released_at?->toDateString(),
            ];
        })->values()->toArray();

        return view('employee.payroll', compact('payslips'));
    }
}