<?php

// app/Services/EmployeeService.php

namespace App\Http\Services;

use App\Models\User;
use App\Models\UserSchedule;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function __construct(
        protected SalaryService $salaryService
    ) {}

    // Auto-generate next employee ID: EMP-001, EMP-002…
    public function generateId(): string
    {
        $last = User::where('id', 'like', 'EMP-%')
            ->orderByDesc('id')
            ->value('id');

        $next = $last
            ? str_pad((int) substr($last, 4) + 1, 3, '0', STR_PAD_LEFT)
            : '001';

        return "EMP-{$next}";
    }

    public function create(array $data): User
    {
        $data['id']       = $this->generateId();
        $data['password'] = Hash::make($data['password'] ?? 'password');
        $data['username'] = $data['username'] ?? strtolower(str_replace(' ', '.', $data['fullName']));

        // Auto-compute daily/hourly from basicSalary
        $rates = $this->salaryService->computeRates($data['basicSalary'] ?? 0);
        $data  = array_merge($data, $rates);

        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        // Recompute rates if salary changed
        if (isset($data['basicSalary'])) {
            $rates = $this->salaryService->computeRates($data['basicSalary']);
            $data  = array_merge($data, $rates);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function assignSchedule(User $user, int $templateId, string $effectiveDate): UserSchedule
    {
        return UserSchedule::create([
            'user_id'        => $user->id,
            'template_id'    => $templateId,
            'effective_date' => $effectiveDate,
        ]);
    }

    public function toggleStatus(User $user): User
    {
        $user->update(['isActive' => !$user->isActive]);
        return $user->fresh();
    }
}