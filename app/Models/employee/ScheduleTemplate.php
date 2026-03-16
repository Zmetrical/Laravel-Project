<?php

namespace App\Models\employee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\employee\ScheduleTemplateDay;
use App\Models\UserSchedule;

class ScheduleTemplate extends Model
{
    protected $table = 'schedule_templates';

    protected $fillable = [
        'name',
        'grace_period_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function days(): HasMany
    {
        return $this->hasMany(ScheduleTemplateDay::class, 'template_id', 'id');
    }

    public function userSchedules(): HasMany
    {
        return $this->hasMany(UserSchedule::class, 'template_id', 'id');
    }
    public function scopeActive($query)
{
    return $query->where('is_active', true);
}
}