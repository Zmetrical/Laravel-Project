<?php

namespace App\Models\employee;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id', 'leave_type_id', 'year',
        'entitled_days', 'carried_over_days', 'used_days',
        'pending_days', 'balance',
    ];

    protected $casts = [
        'entitled_days'    => 'float',
        'carried_over_days'=> 'float',
        'used_days'        => 'float',
        'pending_days'     => 'float',
        'balance'          => 'float',
    ];

    // ── Relationships ────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    // ── Helpers ──────────────────────────────────────────────────
    public function getTotalEntitledAttribute(): float
    {
        return $this->entitled_days + $this->carried_over_days;
    }
    
}