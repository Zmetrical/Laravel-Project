<?php

namespace App\Models\employee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
class OvertimeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'hours',
        'ot_type',
        'rate_multiplier',
        'estimated_pay',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_note',
        'paid_at',
    ];

    protected $casts = [
        'date'            => 'date',
        'hours'           => 'decimal:2',
        'rate_multiplier' => 'decimal:2',
        'estimated_pay'   => 'decimal:2',
        'reviewed_at'     => 'datetime',
        'paid_at'         => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'paid']);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Accessors ─────────────────────────────────────────────────────

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, ['pending', 'rejected']);
    }
}