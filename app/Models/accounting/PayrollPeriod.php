<?php

namespace App\Models\accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class PayrollPeriod extends Model
{
    protected $fillable = [
        'period_type',
        'month',
        'year',
        'start_date',
        'end_date',
        'pay_date',
        'status',
        'processed_by',
        'released_by',
        'notes',
    ];
 
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'pay_date'   => 'date',
    ];
 
    // ── Relationships ────────────────────────────────────────────────────
 
    public function records(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }
 
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }
 
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by', 'id');
    }
 
    // ── Accessors ────────────────────────────────────────────────────────
 
    /**
     * Human-readable label e.g. "March 1–15, 2026"
     */
    public function getLabelAttribute(): string
    {
        $monthName = \Carbon\Carbon::create()->month($this->month)->format('F');
 
        return $this->period_type === '1st-15th'
            ? "{$monthName} 1–15, {$this->year}"
            : "{$monthName} 16–" . $this->end_date->format('d') . ", {$this->year}";
    }
 
    // ── Status Helpers ───────────────────────────────────────────────────
 
    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isReleased(): bool   { return $this->status === 'released'; }
    public function isClosed(): bool     { return $this->status === 'closed'; }
 
    // ── Scopes ───────────────────────────────────────────────────────────
 
    public function scopeDraft($query)      { return $query->where('status', 'draft'); }
    public function scopeProcessing($query) { return $query->where('status', 'processing'); }
    public function scopeReleased($query)   { return $query->where('status', 'released'); }
 
    // ── Static Helpers ───────────────────────────────────────────────────
 
    /**
     * Auto-compute start_date and end_date from period_type + month + year.
     */
    public static function computeDates(string $periodType, int $month, int $year): array
    {
        if ($periodType === '1st-15th') {
            return [
                'start_date' => \Carbon\Carbon::create($year, $month, 1)->toDateString(),
                'end_date'   => \Carbon\Carbon::create($year, $month, 15)->toDateString(),
            ];
        }
 
        $lastDay = \Carbon\Carbon::create($year, $month)->endOfMonth()->day;
 
        return [
            'start_date' => \Carbon\Carbon::create($year, $month, 16)->toDateString(),
            'end_date'   => \Carbon\Carbon::create($year, $month, $lastDay)->toDateString(),
        ];
    }
}