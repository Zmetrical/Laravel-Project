<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('leave_types')->insert([
            [
                'name'                  => 'Vacation Leave',
                'is_paid'               => 1,
                'max_days_per_year'     => 5,
                'requires_approval'     => 1,
                'description'           => 'For rest and recreation',
                'is_carry_over_allowed' => 0,
                'max_carry_over_days'   => 0,
                'applicable_to'         => 'all',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'name'                  => 'Sick Leave',
                'is_paid'               => 1,
                'max_days_per_year'     => 5,
                'requires_approval'     => 1,
                'description'           => 'For illness or medical appointments',
                'is_carry_over_allowed' => 0,
                'max_carry_over_days'   => 0,
                'applicable_to'         => 'all',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'name'                  => 'Emergency Leave',
                'is_paid'               => 1,
                'max_days_per_year'     => 5,
                'requires_approval'     => 1,
                'description'           => 'For unforeseen emergencies',
                'is_carry_over_allowed' => 0,
                'max_carry_over_days'   => 0,
                'applicable_to'         => 'all',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'name'                  => 'Maternity Leave',
                'is_paid'               => 1,
                'max_days_per_year'     => 105,
                'requires_approval'     => 1,
                'description'           => 'Paid by SSS – company advances first',
                'is_carry_over_allowed' => 0,
                'max_carry_over_days'   => 0,
                'applicable_to'         => 'female',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'name'                  => 'Paternity Leave',
                'is_paid'               => 1,
                'max_days_per_year'     => 7,
                'requires_approval'     => 1,
                'description'           => 'For married male employees upon birth of child',
                'is_carry_over_allowed' => 0,
                'max_carry_over_days'   => 0,
                'applicable_to'         => 'male',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
        ]);
    }
}