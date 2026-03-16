<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * LoanSeeder
 *
 * Responsibilities:
 *  - Create all loan records for all employees.
 *  - Create HISTORICAL payments only (those that occurred BEFORE the
 *    PayrollSeeder range: November 16, 2025).
 *
 */
class LoanSeeder extends Seeder
{
    /**
     * PayrollSeeder starts from this date.
     * Loan payments on or after this date will be created by PayrollSeeder.
     */
    const PAYROLL_SEEDER_STARTS = '2025-11-16';

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('loan_payments')->delete();
        DB::table('loans')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now   = Carbon::now();
        $cutoff = Carbon::parse(self::PAYROLL_SEEDER_STARTS);

        $loans = $this->loanDefinitions($now);

        foreach ($loans as $loanData) {
            $loanId = DB::table('loans')->insertGetId($loanData);

            // Only seed historical payments — those before the payroll seeder range
            $this->seedHistoricalPayments(
                $loanId,
                $loanData['user_id'],
                (float) $loanData['amount'],
                (float) $loanData['monthly_amortization'],
                $loanData['start_date'],
                $cutoff,
                $now
            );
        }

        $this->command->info('✓ LoanSeeder: ' . count($loans) . ' loans created with historical payments.');
    }

    // ── Loan Definitions ──────────────────────────────────────────────────────

    private function loanDefinitions(Carbon $now): array
    {
        return [
            // ── EMP-0001 ──────────────────────────────────────────────────
            [
                'user_id'              => 'EMP-0001',
                'loan_type'            => 'sss',
                'loan_type_name'       => 'SSS Salary Loan',
                'amount'               => 24000.00,
                'monthly_amortization' => 1000.00,
                'term_months'          => 24,
                'start_date'           => '2024-01-15',
                'completed_date'       => null,
                'status'               => 'active',
                'encoded_by'           => 'EMP-0002',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'user_id'              => 'EMP-0001',
                'loan_type'            => 'pagibig',
                'loan_type_name'       => 'PAG-IBIG Multi-Purpose Loan',
                'amount'               => 50000.00,
                'monthly_amortization' => 2083.33,
                'term_months'          => 24,
                'start_date'           => '2024-06-01',
                'completed_date'       => null,
                'status'               => 'active',
                'encoded_by'           => 'EMP-0002',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                // Completed — all 24 payments are historical, PayrollSeeder won't touch it
                'user_id'              => 'EMP-0001',
                'loan_type'            => 'sss',
                'loan_type_name'       => 'SSS Salary Loan',
                'amount'               => 15000.00,
                'monthly_amortization' => 625.00,
                'term_months'          => 24,
                'start_date'           => '2022-03-01',
                'completed_date'       => '2024-03-01',
                'status'               => 'completed',
                'encoded_by'           => 'EMP-0002',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],

            // ── EMP-0002 ──────────────────────────────────────────────────
            [
                'user_id'              => 'EMP-0002',
                'loan_type'            => 'pagibig',
                'loan_type_name'       => 'PAG-IBIG Calamity Loan',
                'amount'               => 20000.00,
                'monthly_amortization' => 1666.67,
                'term_months'          => 12,
                'start_date'           => '2025-06-01',
                'completed_date'       => null,
                'status'               => 'active',
                'encoded_by'           => 'EMP-0004',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'user_id'              => 'EMP-0002',
                'loan_type'            => 'sss',
                'loan_type_name'       => 'SSS Salary Loan',
                'amount'               => 30000.00,
                'monthly_amortization' => 1250.00,
                'term_months'          => 24,
                'start_date'           => '2023-01-01',
                'completed_date'       => '2025-01-01',
                'status'               => 'completed',
                'encoded_by'           => 'EMP-0004',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],

            // ── EMP-0003 ──────────────────────────────────────────────────
            [
                'user_id'              => 'EMP-0003',
                'loan_type'            => 'sss',
                'loan_type_name'       => 'SSS Salary Loan',
                'amount'               => 36000.00,
                'monthly_amortization' => 1500.00,
                'term_months'          => 24,
                'start_date'           => '2025-03-01',
                'completed_date'       => null,
                'status'               => 'active',
                'encoded_by'           => 'EMP-0002',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'user_id'              => 'EMP-0003',
                'loan_type'            => 'pagibig',
                'loan_type_name'       => 'PAG-IBIG Multi-Purpose Loan',
                'amount'               => 60000.00,
                'monthly_amortization' => 2500.00,
                'term_months'          => 24,
                'start_date'           => '2024-09-01',
                'completed_date'       => null,
                'status'               => 'active',
                'encoded_by'           => 'EMP-0002',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'user_id'              => 'EMP-0003',
                'loan_type'            => 'pagibig',
                'loan_type_name'       => 'PAG-IBIG Calamity Loan',
                'amount'               => 20000.00,
                'monthly_amortization' => 1666.67,
                'term_months'          => 12,
                'start_date'           => '2023-06-01',
                'completed_date'       => '2024-06-01',
                'status'               => 'completed',
                'encoded_by'           => 'EMP-0004',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],

            // ── EMP-0004 ──────────────────────────────────────────────────
            [
                'user_id'              => 'EMP-0004',
                'loan_type'            => 'pagibig',
                'loan_type_name'       => 'PAG-IBIG Multi-Purpose Loan',
                'amount'               => 80000.00,
                'monthly_amortization' => 3333.33,
                'term_months'          => 24,
                'start_date'           => '2025-01-01',
                'completed_date'       => null,
                'status'               => 'active',
                'encoded_by'           => 'EMP-0002',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'user_id'              => 'EMP-0004',
                'loan_type'            => 'sss',
                'loan_type_name'       => 'SSS Salary Loan',
                'amount'               => 24000.00,
                'monthly_amortization' => 1000.00,
                'term_months'          => 24,
                'start_date'           => '2022-06-01',
                'completed_date'       => '2024-06-01',
                'status'               => 'completed',
                'encoded_by'           => 'EMP-0002',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'user_id'              => 'EMP-0004',
                'loan_type'            => 'sss',
                'loan_type_name'       => 'SSS Salary Loan',
                'amount'               => 48000.00,
                'monthly_amortization' => 2000.00,
                'term_months'          => 24,
                'start_date'           => '2025-07-01',
                'completed_date'       => null,
                'status'               => 'active',
                'encoded_by'           => 'EMP-0002',
                'notes'                => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
        ];
    }

    // ── Historical Payment Seeder ─────────────────────────────────────────────

    /**
     * Insert monthly loan payments from start_date up to (but NOT including)
     * the PayrollSeeder cutoff date. These have payroll_record_id = null
     * because no payroll record exists for them in the seeded data.
     */
    private function seedHistoricalPayments(
        int $loanId,
        string $userId,
        float $amount,
        float $amortization,
        string $startDate,
        Carbon $cutoff,
        Carbon $now
    ): void {
        $balance     = $amount;
        $paymentDate = Carbon::parse($startDate);

        while ($paymentDate->lt($cutoff) && $balance > 0) {
            $paid    = min(round($amortization, 2), round($balance, 2));
            $balance = round($balance - $paid, 2);

            DB::table('loan_payments')->insert([
                'loan_id'           => $loanId,
                'user_id'           => $userId,
                'payroll_record_id' => null, // historical — no payroll record link
                'amount'            => $paid,
                'balance_after'     => $balance,
                'payment_date'      => $paymentDate->toDateString(),
                'payment_type'      => 'payroll_deduction',
                'notes'             => 'Historical (seeded)',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $paymentDate->addMonth();
        }
    }
}