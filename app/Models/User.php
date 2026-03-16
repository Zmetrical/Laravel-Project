<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\employee\AttendanceRecord;
use App\Models\employee\LeaveRequest;
use App\Models\employee\OvertimeRequest;
use App\Models\accounting\PayrollRecord;
use App\Models\UserSchedule;
use App\Models\employee\LeaveBalance;
use App\Models\employee\Loan;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table      = 'users';
    protected $primaryKey = 'id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        // Identity
        'id', 'username', 'password', 'fullName',
        'role', 'employmentStatus', 'isActive',

        // Personal
        'firstName', 'middleName', 'lastName',
        'gender', 'dateOfBirth', 'civilStatus',
        'email', 'phoneNumber',

        // Address
        'addressStreet', 'addressBarangay', 'addressCity',
        'addressProvince', 'addressRegion', 'addressZipCode',

        // Employment
        'department', 'position', 'branch', 'hireDate',

        // Compensation
        'basicSalary', 'dailyRate', 'hourlyRate',

        // Biometric
        'biometricEnrolled', 'enrolledFingerType', 'biometricEnrollmentDate',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'                => 'hashed',
            'dateOfBirth'             => 'date',
            'hireDate'                => 'date',
            'isActive'                => 'boolean',
            'biometricEnrolled'       => 'boolean',
            'biometricEnrollmentDate' => 'datetime',
            'basicSalary'             => 'decimal:2',
            'dailyRate'               => 'decimal:2',
            'hourlyRate'              => 'decimal:2',
        ];
    }

    // ── Role Helpers ──────────────────────────────────────────────────────────

    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isHr(): bool         { return $this->role === 'hr'; }
    public function isAccounting(): bool { return $this->role === 'accounting'; }
    public function isEmployee(): bool   { return $this->role === 'employee'; }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'user_id', 'id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'user_id', 'id');
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class, 'user_id', 'id');
    }

    /**
     * Schedule assignments — links to schedule_templates via user_schedules.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(UserSchedule::class, 'user_id', 'id');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class, 'user_id', 'id');
    }


    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('isActive', true);
    }

    public function scopeEmployees(Builder $query): Builder
    {
        return $query->where('role', 'employee');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getDayOffNumbersAttribute(): array
    {
        return $this->dayOffConfigurations->pluck('day_of_week')->toArray();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->fullName ?: trim("{$this->firstName} {$this->lastName}");
    }

    
public function currentSchedule()
{
    return $this->hasOne(UserSchedule::class, 'user_id', 'id')
                ->with('template.days')
                ->latestOfMany('effective_date');
}

public function leaveBalances(): HasMany
{
    return $this->hasMany(LeaveBalance::class, 'user_id', 'id');
}

public function loans(): HasMany
{
    return $this->hasMany(Loan::class, 'user_id', 'id');
}

}