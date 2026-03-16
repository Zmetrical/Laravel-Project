<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * PayrollSeeder
 *
 * Must run AFTER LoanSeeder.
 *
 * For each payroll record of a released period, this seeder:
 *  1. Finds active loans for the employee during that pay period.
 *  2. Inserts a loan_payment row with payroll_record_id set.
 *  3. Includes loan deduction amounts in total_deductions and net_pay.
 *
 */
class PayrollSeeder extends Seeder
{
    private array $employees = [
        ['id' => 'EMP-0001', 'basic_salary' => 18000.00, 'daily_rate' => 692.31,  'hourly_rate' => 86.54],
        ['id' => 'EMP-0002', 'basic_salary' => 25000.00, 'daily_rate' => 961.54,  'hourly_rate' => 120.19],
        ['id' => 'EMP-0003', 'basic_salary' => 28000.00, 'daily_rate' => 1076.92, 'hourly_rate' => 134.62],
        ['id' => 'EMP-0004', 'basic_salary' => 35000.00, 'daily_rate' => 1346.15, 'hourly_rate' => 168.27],
    ];

    private array $periods = [
        ['1st-15th', 3, 2026],
        ['16th-end', 2, 2026],
        ['1st-15th', 2, 2026],
        ['16th-end', 1, 2026],
        ['1st-15th', 1, 2026],
        ['16th-end', 12, 2025],
        ['1st-15th', 12, 2025],
        ['16th-end', 11, 2025],
    ];

    public function run(): void
    {
        // ── Clean slate (child before parent) ────────────────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('payroll_records')->delete();
        DB::table('payroll_periods')->delete();
        // Remove loan payments that were previously linked to payroll records
        // (historical ones with null payroll_record_id are kept)
        DB::table('loan_payments')->whereNotNull('payroll_record_id')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = Carbon::now();

        // Load all loans upfront — we'll reference these per employee per period
        $allLoans = DB::table('loans')->get()->groupBy('user_id');

        // ── Create periods ────────────────────────────────────────────────
        foreach ($this->periods as [$type, $month, $year]) {
            $dates   = $this->computeDates($type, $month, $year);
            $payDate = $this->computePayDate($type, $month, $year);

            $isCurrent = ($month === 3 && $year === 2026 && $type === '1st-15th');
            $periodStatus = $isCurrent ? 'draft' : 'released';

            $periodId = DB::table('payroll_periods')->insertGetId([
                'period_type'  => $type,
                'month'        => $month,
                'year'         => $year,
                'start_date'   => $dates['start_date'],
                'end_date'     => $dates['end_date'],
                'pay_date'     => $payDate,
                'status'       => $periodStatus,
                'processed_by' => $periodStatus === 'released' ? 'EMP-0004' : null,
                'released_by'  => $periodStatus === 'released' ? 'EMP-0004' : null,
                'notes'        => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            // ── Create one payroll record per employee per period ─────────
            foreach ($this->employees as $emp) {
                $employeeLoans = $allLoans->get($emp['id'], collect());

                // Build base earnings + statutory deductions
                $record = $this->buildBaseRecord($emp, $type);

                // Find loans active during this pay period and compute deductions
                $loanDeductions = $this->resolveLoanDeductions(
                    $employeeLoans,
                    $dates['start_date'],
                    $dates['end_date']
                );

                $loanTotal = array_sum(array_column($loanDeductions, 'amount'));

                // Bake loan total into stored totals
                $record['total_deductions'] = round(
                    $record['total_deductions'] + $loanTotal,
                    2
                );
                $record['net_pay'] = round(
                    $record['gross_pay'] - $record['total_deductions'],
                    2
                );

                $recordStatus = $periodStatus === 'released' ? 'released' : 'draft';
                $releasedAt   = $recordStatus === 'released'
                    ? Carbon::now()->subDays(rand(1, 3))->toDateTimeString()
                    : null;

                // Insert payroll record — one-by-one to get the ID
                $payrollRecordId = DB::table('payroll_records')->insertGetId(
                    array_merge($record, [
                        'payroll_period_id' => $periodId,
                        'user_id'           => $emp['id'],
                        'status'            => $recordStatus,
                        'notes'             => null,
                        'released_at'       => $releasedAt,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ])
                );

                // Insert a loan_payment row per active loan, linked to this record
                if ($recordStatus === 'released') {
                    foreach ($loanDeductions as $ld) {
                        // Update the running balance on the loan payment
                        DB::table('loan_payments')->insert([
                            'loan_id'           => $ld['loan_id'],
                            'user_id'           => $emp['id'],
                            'payroll_record_id' => $payrollRecordId,
                            'amount'            => $ld['amount'],
                            'balance_after'     => $ld['balance_after'],
                            'payment_date'      => $dates['end_date'],
                            'payment_type'      => 'payroll_deduction',
                            'notes'             => null,
                            'created_at'        => $now,
                            'updated_at'        => $now,
                        ]);
                    }
                }
            }
        }

        $periodCount = count($this->periods);
        $recordCount = $periodCount * count($this->employees);
        $this->command->info("✓ PayrollSeeder: {$periodCount} periods, {$recordCount} records created with loan deductions linked.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Loan Resolution
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * For a given pay period date range, find which loans were active
     * and return the semi-monthly deduction amount for each.
     *
     * Semi-monthly split: half the monthly amortization per cutoff.
     * The deduction applies if the loan start_date <= period end_date
     * and the loan is not yet completed by the period start.
     */
    private function resolveLoanDeductions(
        \Illuminate\Support\Collection $loans,
        string $periodStart,
        string $periodEnd
    ): array {
        $deductions = [];
        $start = Carbon::parse($periodStart);
        $end   = Carbon::parse($periodEnd);

        foreach ($loans as $loan) {
            $loanStart     = Carbon::parse($loan->start_date);
            $loanCompleted = $loan->completed_date
                ? Carbon::parse($loan->completed_date)
                : null;

            // Skip if loan hasn't started yet
            if ($loanStart->gt($end)) continue;

            // Skip if loan was already completed before this period
            if ($loanCompleted && $loanCompleted->lt($start)) continue;

            // Skip cancelled loans
            if ($loan->status === 'cancelled') continue;

            // Semi-monthly: half the monthly amortization per period
            $semiMonthlyAmount = round($loan->monthly_amortization / 2, 2);

            // Calculate remaining balance at the start of this period
            // by summing all payments made before this period
            $paidSoFar = DB::table('loan_payments')
                ->where('loan_id', $loan->id)
                ->where('payment_date', '<', $periodStart)
                ->sum('amount');

            $balanceBefore = max(0, round($loan->amount - $paidSoFar, 2));

            // If balance is already zero, skip
            if ($balanceBefore <= 0) continue;

            // Cap deduction at remaining balance
            $deductionAmount = min($semiMonthlyAmount, $balanceBefore);
            $balanceAfter    = round($balanceBefore - $deductionAmount, 2);

            $deductions[] = [
                'loan_id'      => $loan->id,
                'label'        => $loan->loan_type_name,
                'amount'       => $deductionAmount,
                'balance_after'=> $balanceAfter,
            ];
        }

        return $deductions;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Base Record Builder (no loans)
    // ─────────────────────────────────────────────────────────────────────────

    private function buildBaseRecord(array $emp, string $type): array
    {
        $semiMonthlyBasic = round($emp['basic_salary'] / 2, 2);
        $hourlyRate       = $emp['hourly_rate'];
        $dailyRate        = $emp['daily_rate'];

        $overtimePay     = $this->chance(40) ? round($hourlyRate * rand(2, 8) * 1.25, 2) : 0.00;
        $nightDiffPay    = $this->chance(20) ? round($hourlyRate * rand(1, 4) * 0.10, 2) : 0.00;
        $holidayPay      = ($type === '1st-15th' && $this->chance(25)) ? round($dailyRate * 2.0, 2) : 0.00;
        $restDayPay      = $this->chance(15) ? round($dailyRate * 1.30, 2) : 0.00;
        $leavePay        = $this->chance(20) ? round($dailyRate * rand(1, 2), 2) : 0.00;
        $additionalShift = $this->chance(10) ? round($dailyRate * 1.0, 2) : 0.00;
        $allowances      = round($emp['basic_salary'] * 0.05, 2);

        $grossPay = $semiMonthlyBasic + $overtimePay + $nightDiffPay
            + $holidayPay + $restDayPay + $leavePay + $additionalShift + $allowances;

        // Statutory deductions
        $sss        = round(min($emp['basic_salary'] * 0.045, 900) / 2, 2);
        $philHealth = round(min($emp['basic_salary'] * 0.05 / 2, 2500) / 2, 2);
        $pagibig    = 50.00;
        $tax        = $this->estimateTax($grossPay);

        // Attendance deductions
        $lateDeductions      = $this->chance(30) ? round($hourlyRate * (rand(15, 60) / 60), 2) : 0.00;
        $undertimeDeductions = $this->chance(20) ? round($hourlyRate * (rand(30, 120) / 60), 2) : 0.00;
        $absentDeductions    = $this->chance(10) ? round($dailyRate * rand(1, 2), 2) : 0.00;
        $deferredBalance     = ($type === '16th-end' && $this->chance(5)) ? round(rand(100, 500), 2) : 0.00;

        // total_deductions WITHOUT loans (loans added after)
        $totalDeductions = $sss + $philHealth + $pagibig + $tax
            + $lateDeductions + $undertimeDeductions + $absentDeductions + $deferredBalance;

        return [
            'basic_pay'            => $semiMonthlyBasic,
            'overtime_pay'         => $overtimePay,
            'night_diff_pay'       => $nightDiffPay,
            'holiday_pay'          => $holidayPay,
            'rest_day_pay'         => $restDayPay,
            'leave_pay'            => $leavePay,
            'additional_shift_pay' => $additionalShift,
            'allowances'           => $allowances,
            'gross_pay'            => round($grossPay, 2),
            'sss'                  => $sss,
            'philhealth'           => $philHealth,
            'pagibig'              => $pagibig,
            'withholding_tax'      => $tax,
            'late_deductions'      => $lateDeductions,
            'undertime_deductions' => $undertimeDeductions,
            'absent_deductions'    => $absentDeductions,
            'other_deductions'     => 0.00,
            'deferred_balance'     => $deferredBalance,
            'total_deductions'     => round($totalDeductions, 2),
            'net_pay'              => round($grossPay - $totalDeductions, 2),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function chance(int $percent): bool
    {
        return rand(1, 100) <= $percent;
    }

    private function estimateTax(float $semiMonthlyGross): float
    {
        $annual = $semiMonthlyGross * 24;

        if ($annual <= 250000)  return 0.00;
        if ($annual <= 400000)  return round((($annual - 250000) * 0.20) / 24, 2);
        if ($annual <= 800000)  return round((30000 + ($annual - 400000) * 0.25) / 24, 2);
        if ($annual <= 2000000) return round((130000 + ($annual - 800000) * 0.30) / 24, 2);
        if ($annual <= 8000000) return round((490000 + ($annual - 2000000) * 0.32) / 24, 2);
        return round((2410000 + ($annual - 8000000) * 0.35) / 24, 2);
    }

    private function computeDates(string $type, int $month, int $year): array
    {
        if ($type === '1st-15th') {
            return [
                'start_date' => Carbon::create($year, $month, 1)->toDateString(),
                'end_date'   => Carbon::create($year, $month, 15)->toDateString(),
            ];
        }
        return [
            'start_date' => Carbon::create($year, $month, 16)->toDateString(),
            'end_date'   => Carbon::create($year, $month)->endOfMonth()->toDateString(),
        ];
    }

    private function computePayDate(string $type, int $month, int $year): string
    {
        if ($type === '1st-15th') {
            return Carbon::create($year, $month, 20)->toDateString();
        }
        return Carbon::create($year, $month, 1)->addMonth()->day(5)->toDateString();
    }
}