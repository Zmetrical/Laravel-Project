<?php

namespace App\Models\employee;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeConfiguration extends Model
{
    protected $fillable = [
        'daily_max_hours',
        'weekly_max_hours',
        'monthly_max_hours',
        'enforce_limit',
        'updated_by',
    ];

    protected $casts = [
        'daily_max_hours'   => 'decimal:2',
        'weekly_max_hours'  => 'decimal:2',
        'monthly_max_hours' => 'decimal:2',
        'enforce_limit'     => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}