<?php

namespace App\Http\Services;
// app/Services/SalaryService.php

use App\Models\User;

class SalaryService
{
    const WORKING_DAYS_PER_MONTH = 26;
    const HOURS_PER_DAY          = 8;

    public function computeRates(float $basicSalary): array
    {
        $dailyRate  = $basicSalary / self::WORKING_DAYS_PER_MONTH;
        $hourlyRate = $dailyRate   / self::HOURS_PER_DAY;

        return [
            'basicSalary' => round($basicSalary, 2),
            'dailyRate'   => round($dailyRate,   2),
            'hourlyRate'  => round($hourlyRate,  2),
        ];
    }

    public function updateSalary(User $user, float $basicSalary): User
    {
        $rates = $this->computeRates($basicSalary);
        $user->update($rates);
        return $user->fresh();
    }

    public function bulkUpdate(array $userIds, float $basicSalary): int
    {
        $rates = $this->computeRates($basicSalary);

        return User::whereIn('id', $userIds)->update($rates);
    }

    public function getStats(): array
    {
        $employees = User::active()->get();

        return [
            'total'   => $employees->count(),
            'payroll' => $employees->sum('basicSalary'),
            'average' => $employees->avg('basicSalary') ?? 0,
        ];
    }
}