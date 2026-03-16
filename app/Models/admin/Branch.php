<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'contact_number',
        'email',
        'manager_name',
        'is_main',
        'status',
    ];
 
    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }
 
    // ── Scopes ────────────────────────────────────────────────────────────────
 
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
 
    // ── Helpers ───────────────────────────────────────────────────────────────
 
    public function isActive(): bool { return $this->status === 'active'; }
 
    /**
     * Count of active employees assigned to this branch.
     * Derived from users.branch varchar — not a FK.
     */
    public function employeeCount(): int
    {
        return User::active()->where('branch', $this->name)->count();
    }
}