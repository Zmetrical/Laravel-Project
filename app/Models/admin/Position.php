<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
class Position extends Model
{
    protected $fillable = [
        'name',
        'department',
        'description',
        'status',
    ];
 
    // ── Scopes ────────────────────────────────────────────────────────────────
 
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
 
    public function scopeForDepartment(Builder $query, string $dept): Builder
    {
        return $query->where('department', $dept);
    }
 
    // ── Helpers ───────────────────────────────────────────────────────────────
 
    public function isActive(): bool { return $this->status === 'active'; }
 
    /**
     * Count of active employees holding this position.
     * Derived from users.position varchar — not a FK.
     */
    public function employeeCount(): int
    {
        return User::active()->where('position', $this->name)->count();
    }
}
 