<?php

namespace App\Models\employee;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'type',
        'year',
    ];

    protected $casts = [
        'date' => 'date',
        'year' => 'integer',
    ];

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeRegular($query)
    {
        return $query->where('type', 'regular');
    }

    public function scopeSpecial($query)
    {
        return $query->where('type', 'special');
    }
}
