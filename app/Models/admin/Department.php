<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
 
class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'branch',
        'status',
        'head_employee_ids',
    ];
 
    protected function casts(): array
    {
        return [
            'head_employee_ids' => 'array',
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
     * Count of active employees assigned to this department.
     * Derived from users.department varchar — not a FK relation.
     */
    public function employeeCount(): int
    {
        return User::active()
            ->where('department', $this->name)
            ->count();
    }
}
 