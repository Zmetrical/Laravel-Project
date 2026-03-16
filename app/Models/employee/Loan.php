<?php

namespace App\Models\employee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\employee\LoanPayment;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'loan_type',
        'loan_type_name',
        'amount',
        'monthly_amortization',
        'term_months',
        'start_date',
        'completed_date',
        'status',
        'encoded_by',
        'notes',
    ];
 
    protected $casts = [
        'start_date'     => 'date',
        'completed_date' => 'date',
        'amount'         => 'decimal:2',
        'monthly_amortization' => 'decimal:2',
    ];
 
    // ── Relationships ────────────────────────────────────────────────────────
 
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
 
    public function encodedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by', 'id');
    }
 
    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
 
    // ── Computed ─────────────────────────────────────────────────────────────
 
    /**
     * Total amount paid so far — derived from payment records, never stored.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }
 
    /**
     * Remaining balance — derived from payment records.
     */
    public function getRemainingBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - $this->total_paid);
    }
 
    /**
     * Number of payments made.
     */
    public function getPaymentsMadeAttribute(): int
    {
        return $this->payments()->count();
    }
 
    /**
     * Progress percentage (0–100).
     */
    public function getProgressAttribute(): int
    {
        if (!$this->term_months) return 0;
        return (int) min(100, round(($this->payments_made / $this->term_months) * 100));
    }
 
    // ── Status Helpers ───────────────────────────────────────────────────────
 
    public function isActive(): bool    { return $this->status === 'active'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
 
    /**
     * Mark as completed if all payments have been made.
     */
    public function checkAndComplete(): void
    {
        if ($this->isActive() && $this->remaining_balance <= 0) {
            $this->update([
                'status'         => 'completed',
                'completed_date' => now()->toDateString(),
            ]);
        }
    }
 
    // ── Scopes ───────────────────────────────────────────────────────────────
 
    public function scopeActive($query)    { return $query->where('status', 'active'); }
    public function scopeCompleted($query) { return $query->where('status', 'completed'); }
}