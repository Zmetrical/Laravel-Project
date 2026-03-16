<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\employee\ScheduleTemplate;

class UserSchedule extends Model
{
    protected $table = 'user_schedules';

    protected $fillable = [
        'user_id',
        'template_id',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ScheduleTemplate::class, 'template_id', 'id');
    }
}