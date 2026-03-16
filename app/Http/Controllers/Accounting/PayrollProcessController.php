<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\accounting\PayrollPeriod;
use App\Models\accounting\PayrollRecord;
use App\Models\employee\Loan;
use App\Models\employee\LoanPayment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Services\PayrollComputationService;

class PayrollProcessController extends Controller
{
    public function __construct(
        private PayrollComputationService $payrollService
    ) {}

    /**
     * Show the process page for a period in 'processing' status.
     *
     * GET /accounting/payroll/periods/{period}/process
     */
    public function show(PayrollPeriod $period)
    {
        abort_unless($period->isProcessing(), 403, 'This period is not in processing status.');

        $employees = User::where('isActive', 1)
            ->whereNotIn('employmentStatus', ['resigned', 'terminated'])
            ->orderBy('department')
            ->orderBy('fullName')
            ->get([
                'id', 'fullName', 'firstName', 'lastName',
                'department', 'position', 'basicSalary',
                'dailyRate', 'hourlyRate', 'employmentStatus',
            ]);

        // Map employee_id → payroll record status for list badges
        $processedIds = PayrollRecord::where('payroll_period_id', $period->id)
            ->pluck('status', 'user_id');

        $totalEmployees = $employees->count();
        $savedCount     = $processedIds->count();

        return view('accounting.payroll.process', compact(
            'period', 'employees', 'processedIds', 'totalEmployees', 'savedCount'
        ));
    }

    /**
     * AJAX: Return attendance + computed payroll for one employee.
     *
     * GET /accounting/payroll/periods/{period}/process/{employee}/data
     */
    public function employeeData(PayrollPeriod $period, User $employee): JsonResponse
    {
        abort_unless($period->isProcessing(), 403);

        $attendance = $employee->attendanceRecords()
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->orderBy('date')
            ->get()
            ->map(fn($r) => [
                'date'              => $r->date->format('Y-m-d'),
                'day_name'          => $r->date->format('D'),
                'time_in'           => $r->time_in  ? substr($r->time_in,  0, 5) : null,
                'time_out'          => $r->time_out ? substr($r->time_out, 0, 5) : null,
                'hours_worked'      => (float) $r->hours_worked,
                'late_minutes'      => (float) $r->late_minutes,
                'undertime_minutes' => (float) $r->undertime_minutes,
                'overtime_hours'    => (float) $r->overtime_hours,
                'status'            => $r->status,
                'notes'             => $r->notes,
                'is_biometric'      => (bool)  $r->is_biometric,
            ]);

        $existing = PayrollRecord::where('payroll_period_id', $period->id)
            ->where('user_id', $employee->id)
            ->first();

        $computed = $this->payrollService->compute($employee, $period);

        return response()->json([
            'employee' => [
                'id'               => $employee->id,
                'fullName'         => $employee->fullName,
                'position'         => $employee->position,
                'department'       => $employee->department,
                'employmentStatus' => $employee->employmentStatus,
                'basicSalary'      => (float) $employee->basicSalary,
                'dailyRate'        => $computed['_meta']['daily_rate'],
                'hourlyRate'       => $computed['_meta']['hourly_rate'],
            ],
            'attendance' => $attendance,
            'computed'   => array_diff_key($computed, ['_meta' => null]),
            'meta'       => $computed['_meta'],
            'existing'   => $existing ? [
                'id'          => $existing->id,
                'status'      => $existing->status,
                'net_pay'     => (float) $existing->net_pay,
                'released_at' => $existing->released_at?->toDateTimeString(),
            ] : null,
        ]);
    }

    /**
     * AJAX: Save computed payroll record + insert loan_payments for one employee.
     *
     * POST /accounting/payroll/periods/{period}/process/{employee}/save
     */
    public function saveRecord(PayrollPeriod $period, User $employee): JsonResponse
    {
        abort_unless($period->isProcessing(), 403);

        $computed = $this->payrollService->compute($employee, $period);
        $meta     = $computed['_meta'];

        try {
            DB::transaction(function () use ($period, $employee, $computed, $meta) {

                // 1. Upsert the payroll record (draft)
                $record = PayrollRecord::updateOrCreate(
                    [
                        'payroll_period_id' => $period->id,
                        'user_id'           => $employee->id,
                    ],
                    array_merge(
                        array_diff_key($computed, ['_meta' => null]),
                        ['status' => 'draft']
                    )
                );

                // 2. Sync loan_payments for this payroll record
                //    Delete existing ones tied to this record, then re-insert.
                //    This is safe because the record is still draft.
                LoanPayment::where('payroll_record_id', $record->id)->delete();

                foreach ($meta['loan_deductions'] as $loanRow) {
                    LoanPayment::create([
                        'loan_id'           => $loanRow['loan_id'],
                        'user_id'           => $employee->id,
                        'payroll_record_id' => $record->id,
                        'amount'            => $loanRow['amount'],
                        'balance_after'     => $loanRow['balance_after'],
                        'payment_date'      => $period->pay_date,
                        'payment_type'      => 'payroll_deduction',
                    ]);

                    // Mark loan as completed if balance is now 0
                    if ($loanRow['balance_after'] <= 0) {
                        Loan::where('id', $loanRow['loan_id'])
                            ->update([
                                'status'         => 'completed',
                                'completed_date' => $period->pay_date,
                            ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save record. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Payroll record saved for {$employee->fullName}.",
        ]);
    }

    /**
     * AJAX: Compute and save ALL active employees for this period in one pass.
     * Skips already-released records.
     *
     * POST /accounting/payroll/periods/{period}/process/save-all
     */
    public function saveAll(PayrollPeriod $period): JsonResponse
    {
        abort_unless($period->isProcessing(), 403);

        $employees = User::where('isActive', 1)
            ->whereNotIn('employmentStatus', ['resigned', 'terminated'])
            ->get();

        // Don't overwrite released records
        $releasedIds = PayrollRecord::where('payroll_period_id', $period->id)
            ->where('status', 'released')
            ->pluck('user_id')
            ->toArray();

        $saved   = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($employees as $employee) {
            if (in_array($employee->id, $releasedIds, true)) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($period, $employee, &$saved) {
                    $computed = $this->payrollService->compute($employee, $period);
                    $meta     = $computed['_meta'];

                    $record = PayrollRecord::updateOrCreate(
                        [
                            'payroll_period_id' => $period->id,
                            'user_id'           => $employee->id,
                        ],
                        array_merge(
                            array_diff_key($computed, ['_meta' => null]),
                            ['status' => 'draft']
                        )
                    );

                    LoanPayment::where('payroll_record_id', $record->id)->delete();

                    foreach ($meta['loan_deductions'] as $loanRow) {
                        LoanPayment::create([
                            'loan_id'           => $loanRow['loan_id'],
                            'user_id'           => $employee->id,
                            'payroll_record_id' => $record->id,
                            'amount'            => $loanRow['amount'],
                            'balance_after'     => $loanRow['balance_after'],
                            'payment_date'      => $period->pay_date,
                            'payment_type'      => 'payroll_deduction',
                        ]);

                        if ($loanRow['balance_after'] <= 0) {
                            Loan::where('id', $loanRow['loan_id'])
                                ->update([
                                    'status'         => 'completed',
                                    'completed_date' => $period->pay_date,
                                ]);
                        }
                    }

                    $saved++;
                });
            } catch (\Throwable $e) {
                $errors[] = $employee->fullName;
            }
        }

        return response()->json([
            'success' => true,
            'saved'   => $saved,
            'skipped' => $skipped,
            'errors'  => $errors,
            'message' => "{$saved} record(s) saved." .
                         ($skipped > 0 ? " {$skipped} already released, skipped." : '') .
                         (count($errors) > 0 ? ' Failed: ' . implode(', ', $errors) . '.' : ''),
        ]);
    }

    /**
     * AJAX: Release all draft records for this period + advance period to 'released'.
     * Only callable when period is still 'processing'.
     *
     * POST /accounting/payroll/periods/{period}/process/release-all
     */
    public function releaseAll(PayrollPeriod $period): JsonResponse
    {
        abort_unless($period->isProcessing(), 403);

        // Must have at least one saved record before releasing
        $draftCount = PayrollRecord::where('payroll_period_id', $period->id)
            ->where('status', 'draft')
            ->count();

        if ($draftCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No draft records to release. Save at least one employee first.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($period) {
                $now = now();

                // Release all draft records
                PayrollRecord::where('payroll_period_id', $period->id)
                    ->where('status', 'draft')
                    ->update([
                        'status'      => 'released',
                        'released_at' => $now,
                    ]);

                // Advance period status
                $period->update([
                    'status'      => 'released',
                    'released_by' => Auth::id(),
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to release payroll. Please try again.',
            ], 500);
        }

        return response()->json([
            'success'  => true,
            'message'  => "Payroll released. Employees can now view their payslips.",
            'redirect' => route('accounting.payroll.periods.index'),
        ]);
    }
}