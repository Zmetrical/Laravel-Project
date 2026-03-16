<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveBalanceSeeder extends Seeder
{
public function run(): void
{
    DB::table('leave_balances')->truncate(); // ← add this line only
    
    $now  = now();
    $year = now()->year;

    $users      = DB::table('users')->get(['id', 'gender']);
    $leaveTypes = DB::table('leave_types')->get(['id', 'name', 'max_days_per_year', 'applicable_to']);

    $rows = [];

    foreach ($users as $user) {
        foreach ($leaveTypes as $lt) {
            if ($lt->applicable_to === 'male'   && $user->gender !== 'Male')   continue;
            if ($lt->applicable_to === 'female' && $user->gender !== 'Female') continue;

            $entitled = $lt->max_days_per_year;

            $rows[] = [
                'user_id'           => $user->id,
                'leave_type_id'     => $lt->id,
                'year'              => $year,
                'entitled_days'     => $entitled,
                'carried_over_days' => 0,
                'used_days'         => 0,
                'pending_days'      => 0,
                'balance'           => $entitled,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }
    }

    DB::table('leave_balances')->insert($rows);
}
}