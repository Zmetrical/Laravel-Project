<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\employee\Holiday;
use App\Models\employee\Loan;
use App\Models\employee\LoanPayment;
use App\Models\employee\LeaveRequest;
use App\Models\accounting\PayrollPeriod;
use App\Models\accounting\PayrollRecord;
use Carbon\Carbon;

class PayrollComputationService
{
    const STD_WORKING_HOURS = 8;
    const NIGHT_DIFF_RATE   = 0.10; // Art. 86 DOLE

    /**
     * Compute payroll for one employee in a given period.
     * Returns an array ready to fill a PayrollRecord + _meta for the controller.
     */
    public function compute(User $employee, PayrollPeriod $period): array
    {
        // ── 1. Schedule-aware work days ──────────────────────────────────────
        $workingDays = $this->getWorkingDaysOfWeek($employee, $period->start_date);

        $periodWorkDays   = $this->countScheduledWorkDays(
            $period->start_date,
            $period->end_date,
            $workingDays
        );

        $fullMonthWorkDays = $this->countScheduledWorkDays(
            $period->start_date->copy()->startOfMonth(),
            $period->start_date->copy()->endOfMonth(),
            $workingDays
        );

        // ── 2. Rates ─────────────────────────────────────────────────────────
        $dailyRate  = $fullMonthWorkDays > 0
            ? round($employee->basicSalary / $fullMonthWorkDays, 4)
            : 0;
        $hourlyRate = $dailyRate > 0
            ? round($dailyRate / self::STD_WORKING_HOURS, 4)
            : 0;
        $minRate    = $hourlyRate / 60;

        // ── 3. Attendance ────────────────────────────────────────────────────
        $attendance = $employee->attendanceRecords()
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->orderBy('date')
            ->get();

        $lateMin    = 0;
        $utMin      = 0;
        $absentDays = 0;
        $ndHrs      = 0;
        $holidayPay = 0;

        // Pre-load holidays for the period (avoid N+1)
        $holidays = Holiday::whereBetween('date', [$period->start_date, $period->end_date])
            ->get()
            ->keyBy(fn($h) => $h->date->toDateString());

        foreach ($attendance as $record) {
            if ($record->status === 'absent') {
                $absentDays++;
                continue;
            }

            $lateMin += (float) $record->late_minutes;
            $utMin   += (float) $record->undertime_minutes;
            $ndHrs   += $this->nightDiffHours($record->time_in, $record->time_out);

            // Holiday pay — only if employee actually clocked in
            $dateKey = $record->date->toDateString();
            if (isset($holidays[$dateKey]) && $record->time_in) {
                $workedHrs   = min((float) $record->hours_worked, self::STD_WORKING_HOURS);
                $holidayPay += $holidays[$dateKey]->type === 'regular'
                    ? $workedHrs * $hourlyRate * 1.00   // +100% regular holiday
                    : $workedHrs * $hourlyRate * 0.30;  // +30% special holiday
            }
        }

        // ── 4. Approved overtime ─────────────────────────────────────────────
        $approvedOT = $employee->overtimeRequests()
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->get();

        $otPay  = (float) $approvedOT->sum('estimated_pay');
        $otHrs  = (float) $approvedOT->sum('hours');

        // ── 5. Night differential ────────────────────────────────────────────
        $nightDiffPay = round($ndHrs * $hourlyRate * self::NIGHT_DIFF_RATE, 2);

        // ── 6. Leave pay (approved + paid leave types overlapping period) ────
        [$leavePay, $leaveDays] = $this->computeLeavePay($employee, $period, $dailyRate);

        // ── 7. Basic pay (scheduled days minus absents) ──────────────────────
        // Absent deduction is separate; basic pay reflects scheduled days worked
        $basicPay = round($dailyRate * $periodWorkDays, 2);

        // ── 8. Attendance deductions ─────────────────────────────────────────
        $lateDeductions      = round($lateMin * $minRate, 2);
        $undertimeDeductions = round($utMin   * $minRate, 2);
        $absentDeductions    = round($absentDays * $dailyRate, 2);

        // ── 9. Government contributions ──────────────────────────────────────
        $gov = $this->governmentDeductions($employee->basicSalary);

        // ── 10. Loan deductions ──────────────────────────────────────────────
        [$loanRows, $totalLoanDeduction] = $this->computeLoanDeductions($employee, $period);

        // ── 11. Deferred balance from previous payslip ───────────────────────
        $deferredFromPrev = $this->getPreviousDeferredBalance($employee, $period);

        // ── 12. Gross pay ────────────────────────────────────────────────────
        $grossPay = round(
            $basicPay + $otPay + $nightDiffPay + $holidayPay + $leavePay,
            2
        );

        // ── 13. Total deductions ─────────────────────────────────────────────
        $totalDeductions = round(
            $lateDeductions + $undertimeDeductions + $absentDeductions
            + $gov['sss'] + $gov['philhealth'] + $gov['pagibig'] + $gov['tax']
            + $totalLoanDeduction
            + $deferredFromPrev,
            2
        );

        // ── 14. Net pay & deferred overflow ──────────────────────────────────
        $netPay          = round($grossPay - $totalDeductions, 2);
        $deferredBalance = 0;

        if ($netPay < 0) {
            // Cannot have negative net pay — carry excess to next period
            $deferredBalance = abs($netPay);
            $netPay          = 0.00;
        }

        return [
            // ── Earnings (stored on payroll_records) ─────────────────────────
            'basic_pay'            => $basicPay,
            'overtime_pay'         => round($otPay, 2),
            'night_diff_pay'       => $nightDiffPay,
            'holiday_pay'          => round($holidayPay, 2),
            'rest_day_pay'         => 0.00,
            'leave_pay'            => round($leavePay, 2),
            'additional_shift_pay' => 0.00,
            'allowances'           => 0.00,
            'gross_pay'            => $grossPay,

            // ── Deductions (stored on payroll_records) ────────────────────────
            'sss'                  => $gov['sss'],
            'philhealth'           => $gov['philhealth'],
            'pagibig'              => $gov['pagibig'],
            'withholding_tax'      => $gov['tax'],
            'late_deductions'      => $lateDeductions,
            'undertime_deductions' => $undertimeDeductions,
            'absent_deductions'    => $absentDeductions,
            'other_deductions'     => 0.00,
            'deferred_balance'     => $deferredBalance,  // overflow to NEXT period
            'total_deductions'     => $totalDeductions,
            'net_pay'              => $netPay,

            // ── Meta (NOT stored on payroll_records, used by controller) ─────
            '_meta' => [
                'daily_rate'         => $dailyRate,
                'hourly_rate'        => $hourlyRate,
                'work_days'          => $periodWorkDays,
                'late_minutes'       => $lateMin,
                'ut_minutes'         => $utMin,
                'absent_days'        => $absentDays,
                'leave_days'         => $leaveDays,
                'nd_hours'           => round($ndHrs, 2),
                'ot_hours'           => $otHrs,
                'deferred_from_prev' => $deferredFromPrev, // carried IN from prev
                'loan_deductions'    => $loanRows,         // array for loan_payments insert
            ],
        ];
    }

    // ── Leave Pay ─────────────────────────────────────────────────────────────

    /**
     * Sum leave pay from approved paid-leave requests that overlap this period.
     * Returns [leavePay, leaveDays].
     */
    private function computeLeavePay(User $employee, PayrollPeriod $period, float $dailyRate): array
    {
        $leaves = LeaveRequest::where('user_id', $employee->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', fn($q) => $q->where('is_paid', true))
            ->where(function ($q) use ($period) {
                $q->whereBetween('start_date', [$period->start_date, $period->end_date])
                  ->orWhereBetween('end_date',   [$period->start_date, $period->end_date])
                  ->orWhere(function ($q2) use ($period) {
                      $q2->where('start_date', '<=', $period->start_date)
                         ->where('end_date',   '>=', $period->end_date);
                  });
            })
            ->get();

        $totalDays = 0;
        $totalPay  = 0.00;

        foreach ($leaves as $leave) {
            // Clamp to period range
            $start = max($leave->start_date->toDateString(), $period->start_date->toDateString());
            $end   = min($leave->end_date->toDateString(),   $period->end_date->toDateString());

            $days       = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
            $totalDays += $days;
            $totalPay  += $days * $dailyRate;
        }

        return [round($totalPay, 2), $totalDays];
    }

    // ── Loan Deductions ───────────────────────────────────────────────────────

    /**
     * Compute semi-monthly loan deductions for all active loans.
     * Returns [array of loan rows, total deduction amount].
     *
     * Each row: ['loan_id', 'label', 'amount', 'balance_after']
     * These rows are used by the controller to insert loan_payments.
     */
    private function computeLoanDeductions(User $employee, PayrollPeriod $period): array
    {
        $loans = Loan::where('user_id', $employee->id)
            ->where('status', 'active')
            ->where('start_date', '<=', $period->end_date)
            ->get();

        $rows  = [];
        $total = 0.00;

        foreach ($loans as $loan) {
            // Total paid BEFORE this period (excludes current period's payment)
            $paidBefore = (float) LoanPayment::where('loan_id', $loan->id)
                ->where('payment_date', '<', $period->start_date)
                ->sum('amount');

            $currentBalance = max(0, (float) $loan->amount - $paidBefore);

            if ($currentBalance <= 0) {
                continue; // Already fully paid
            }

            // Semi-monthly = half of monthly amortization, capped at remaining balance
            $semiMonthly = round($loan->monthly_amortization / 2, 2);
            $deduction   = min($semiMonthly, $currentBalance);
            $balanceAfter = round($currentBalance - $deduction, 2);

            $rows[] = [
                'loan_id'      => $loan->id,
                'label'        => $loan->loan_type_name,
                'amount'       => $deduction,
                'balance_after'=> $balanceAfter,
            ];

            $total += $deduction;
        }

        return [$rows, round($total, 2)];
    }

    // ── Deferred Balance ──────────────────────────────────────────────────────

    /**
     * Find any unpaid deferred balance from the most recent previous payroll record.
     */
    private function getPreviousDeferredBalance(User $employee, PayrollPeriod $period): float
    {
        $previous = PayrollRecord::where('user_id', $employee->id)
            ->whereHas('period', fn($q) => $q->where('pay_date', '<', $period->pay_date))
            ->orderByDesc('id')
            ->value('deferred_balance');

        return max(0, (float) $previous);
    }

    // ── Schedule-Aware Work Days ──────────────────────────────────────────────

    /**
     * Get the working days-of-week (0=Sun…6=Sat) from the employee's
     * assigned schedule template. Falls back to Mon–Sat (1–6) if no schedule.
     */
    private function getWorkingDaysOfWeek(User $employee, Carbon $asOf): array
    {
        $schedule = $employee->schedules()
            ->with('template.days')
            ->where('effective_date', '<=', $asOf->toDateString())
            ->orderByDesc('effective_date')
            ->first();

        if (! $schedule || ! $schedule->template) {
            // Default: Monday–Saturday
            return [1, 2, 3, 4, 5, 6];
        }

        return $schedule->template->days
            ->where('is_working_day', true)
            ->pluck('day_of_week')
            ->toArray();
    }

    /**
     * Count days between two dates (inclusive) that fall on working days.
     */
    public function countScheduledWorkDays(Carbon $start, Carbon $end, array $workingDays): int
    {
        $count   = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, $workingDays, true)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Convenience alias kept for backward compatibility.
     */
    public function countWorkDays(Carbon $start, Carbon $end): int
    {
        // Default Mon–Sat
        return $this->countScheduledWorkDays($start, $end, [1, 2, 3, 4, 5, 6]);
    }

    // ── Government Contributions ──────────────────────────────────────────────

    /**
     * PH statutory contributions — employee share, semi-monthly (÷2).
     *
     * SSS      : 4.5% employee share  | min ₱135, max ₱900/month
     * PhilHealth: 2.5% employee share | cap ₱1,250/month
     * Pag-IBIG : 2%                   | cap ₱100/month
     * Tax      : TRAIN Law annual brackets ÷ 24 payroll periods
     */
    private function governmentDeductions(float $monthlySalary): array
    {
        // SSS
        $sssMonthly = min(max($monthlySalary * 0.045, 135.00), 900.00);

        // PhilHealth
        $phMonthly = min($monthlySalary * 0.025, 1250.00);

        // Pag-IBIG
        $piMonthly = min($monthlySalary * 0.02, 100.00);

        // Withholding tax (TRAIN Law)
        $annual    = $monthlySalary * 12;
        $annualTax = 0.00;

        if ($annual > 8_000_000) {
            $annualTax = ($annual - 8_000_000) * 0.35 + 2_202_500;
        } elseif ($annual > 2_000_000) {
            $annualTax = ($annual - 2_000_000) * 0.32 + 490_000;
        } elseif ($annual > 800_000) {
            $annualTax = ($annual - 800_000)   * 0.30 + 130_000;
        } elseif ($annual > 400_000) {
            $annualTax = ($annual - 400_000)   * 0.25 + 22_500;
        } elseif ($annual > 250_000) {
            $annualTax = ($annual - 250_000)   * 0.15;
        }
        // ≤ 250,000 annual: tax exempt

        return [
            'sss'        => round($sssMonthly / 2, 2),
            'philhealth' => round($phMonthly   / 2, 2),
            'pagibig'    => round($piMonthly   / 2, 2),
            'tax'        => round($annualTax   / 24, 2),
        ];
    }

    // ── Night Differential ────────────────────────────────────────────────────

    /**
     * Compute hours worked between 22:00–06:00 (DOLE Art. 86).
     */
    private function nightDiffHours(?string $timeIn, ?string $timeOut): float
    {
        if (! $timeIn || ! $timeOut) {
            return 0.0;
        }

        $toMin = fn(string $t): int => (int) explode(':', $t)[0] * 60
                                     + (int) explode(':', $t)[1];

        $tin  = $toMin($timeIn);
        $tout = $toMin($timeOut);

        // Handle overnight shift
        if ($tout <= $tin) {
            $tout += 1440;
        }

        // Night diff window: 22:00 → 30:00 (06:00 next day expressed as 1440+360)
        $ndStart = 22 * 60;
        $ndEnd   = 1440 + 6 * 60;

        // Early morning window: 00:00 → 06:00 for shifts that don't cross midnight
        $overlap = max(0, min($tout, $ndEnd) - max($tin, $ndStart));

        // If shift is entirely within 00:00–06:00
        if ($tin < 360 && $tout <= 360) {
            $overlap = $tout - $tin;
        } elseif ($tin < 360) {
            $overlap += max(0, min($tout, 360) - $tin);
        }

        return round(max(0, $overlap) / 60, 2);
    }
}