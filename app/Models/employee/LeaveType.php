<?php

namespace App\Models\employee;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'is_paid',
        'max_days_per_year',
        'requires_approval',
        'description',
        'is_carry_over_allowed',
        'max_carry_over_days',
        'applicable_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_paid'               => 'boolean',
            'requires_approval'     => 'boolean',
            'is_carry_over_allowed' => 'boolean',
            'is_active'             => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter leave types applicable to this user based on gender.
     * applicable_to: 'all' | 'male' | 'female'
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('applicable_to', 'all');

            $gender = strtolower($user->gender ?? '');

            if ($gender === 'male') {
                $q->orWhere('applicable_to', 'male');
            }

            if ($gender === 'female') {
                $q->orWhere('applicable_to', 'female');
            }
        });
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Sick/emergency leave can start today; others require advance notice (next Monday).
     */
    public function getMinDateRuleAttribute(): string
    {
        $name = strtolower($this->name);

        if (str_contains($name, 'sick') || str_contains($name, 'emergency')) {
            return 'today';
        }

        return 'next-monday';
    }

    public function getColorClassAttribute(): string
    {
        $name = strtolower($this->name);

        return match (true) {
            str_contains($name, 'vacation')  => 'primary',
            str_contains($name, 'sick')      => 'secondary',
            str_contains($name, 'emergency') => 'secondary',
            str_contains($name, 'maternity') => 'primary',
            str_contains($name, 'paternity') => 'primary',
            default                          => 'secondary',
        };
    }

    public function getIconAttribute(): string
    {
        $name = strtolower($this->name);

        return match (true) {
            str_contains($name, 'vacation')  => 'bi-sun',
            str_contains($name, 'sick')      => 'bi-heart-pulse',
            str_contains($name, 'emergency') => 'bi-lightning-charge',
            str_contains($name, 'maternity') => 'bi-person-heart',
            str_contains($name, 'paternity') => 'bi-person-heart',
            default                          => 'bi-calendar',
        };
    }
}