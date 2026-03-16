<?php

namespace App\Models\employee;

use Illuminate\Database\Eloquent\Model;

class OvertimeRate extends Model
{
    protected $fillable = [
        'name',
        'multiplier',
        'is_active',
    ];

    protected $casts = [
        'multiplier' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}