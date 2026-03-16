<?php

namespace App\Models\employee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleTemplateDay extends Model
{
    const DAY_NAMES = [
        0 => 'Sun', 1 => 'Mon', 2 => 'Tue',
        3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat',
    ];

    protected $table = 'schedule_template_days';

    protected $fillable = [
        'template_id',
        'day_of_week',
        'is_working_day',
        'shift_in',
        'shift_out',
    ];

    protected $casts = [
        'is_working_day' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ScheduleTemplate::class, 'template_id', 'id');
    }

    public function getDayNameAttribute(): string
    {
        return self::DAY_NAMES[$this->day_of_week] ?? '?';
    }
}