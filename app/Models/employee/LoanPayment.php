<?php

namespace App\Models\employee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\accounting\PayrollRecord;
use App\Models\employee\Loan;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'user_id',
        'payroll_record_id',
        'amount',
        'balance_after',
        'payment_date',
        'payment_type',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
        'balance_after'=> 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function payrollRecord(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class);
    }
}