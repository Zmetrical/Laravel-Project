<?php

namespace App\Models\accounting;

use App\Models\employee\LoanPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRecord extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'user_id',
        'basic_pay',
        'overtime_pay',
        'night_diff_pay',
        'holiday_pay',
        'rest_day_pay',
        'leave_pay',
        'additional_shift_pay',
        'allowances',
        'gross_pay',
        'sss',
        'philhealth',
        'pagibig',
        'withholding_tax',
        'late_deductions',
        'undertime_deductions',
        'absent_deductions',
        'other_deductions',
        'deferred_balance',
        'total_deductions',
        'net_pay',
        'status',
        'notes',
        'released_at',
    ];

    protected $casts = [
        'released_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Loan payments deducted within this payroll record.
     * Each row = one loan's deduction for this pay period.
     */
    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class, 'payroll_record_id');
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    /**
     * Recompute gross_pay, total_deductions, net_pay from individual fields.
     * Call this before saving whenever individual fields are updated.
     */
    public function recompute(): void
    {
        $this->gross_pay = $this->basic_pay
            + $this->overtime_pay
            + $this->night_diff_pay
            + $this->holiday_pay
            + $this->rest_day_pay
            + $this->leave_pay
            + $this->additional_shift_pay
            + $this->allowances;

        $this->total_deductions = $this->sss
            + $this->philhealth
            + $this->pagibig
            + $this->withholding_tax
            + $this->late_deductions
            + $this->undertime_deductions
            + $this->absent_deductions
            + $this->other_deductions
            + $this->deferred_balance
            + $this->loanPayments()->sum('amount'); // include live loan deductions

        $this->net_pay = $this->gross_pay - $this->total_deductions;
    }
}