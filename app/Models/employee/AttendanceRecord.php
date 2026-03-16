<?php

namespace App\Models\employee;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'time_out',
        'hours_worked',
        'late_minutes',
        'undertime_minutes',
        'overtime_hours',
        'status',
        'notes',
        'is_biometric',
    ];

    protected function casts(): array
    {
        return [
            'date'               => 'date',
            'hours_worked'       => 'decimal:2',
            'late_minutes'       => 'decimal:2',
            'undertime_minutes'  => 'decimal:2',
            'overtime_hours'     => 'decimal:2',
            'is_biometric'       => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getIsCompleteAttribute(): bool
    {
        return filled($this->time_in) && filled($this->time_out);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present'    => 'Present',
            'absent'     => 'Absent',
            'late'       => 'Late',
            'half_day'   => 'Half Day',
            'leave'      => 'On Leave',
            'holiday'    => 'Holiday',
            'rest_day'   => 'Rest Day',
            'incomplete' => 'Incomplete',
            default      => ucfirst($this->status),
        };
    }
}