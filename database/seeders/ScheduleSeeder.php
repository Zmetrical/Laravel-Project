<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Create the Parent Template
        $templateId = DB::table('schedule_templates')->insertGetId([
            'name'                 => 'Standard Shift + Saturday 8-3',
            'grace_period_minutes' => 15,
            'is_active'            => true,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // 2. Configure the 7 Days for this Template
        $days = [];
        
        // Sunday (Rest Day)
        $days[] = ['template_id' => $templateId, 'day_of_week' => 0, 'is_working_day' => false, 'shift_in' => null, 'shift_out' => null, 'created_at' => $now, 'updated_at' => $now];
        
        // Monday to Friday (8 AM - 5 PM)
        for ($i = 1; $i <= 5; $i++) {
            $days[] = ['template_id' => $templateId, 'day_of_week' => $i, 'is_working_day' => true, 'shift_in' => '08:00:00', 'shift_out' => '17:00:00', 'created_at' => $now, 'updated_at' => $now];
        }

        // Saturday (8 AM - 3 PM)
        $days[] = ['template_id' => $templateId, 'day_of_week' => 6, 'is_working_day' => true, 'shift_in' => '08:00:00', 'shift_out' => '15:00:00', 'created_at' => $now, 'updated_at' => $now];

        DB::table('schedule_template_days')->insert($days);

        // 3. Assign to users
        $users = ['EMP-0001', 'EMP-0002', 'EMP-0003', 'EMP-0004'];
        $userSchedules = [];
        foreach ($users as $userId) {
            $userSchedules[] = [
                'user_id'        => $userId,
                'template_id'    => $templateId,
                'effective_date' => '2026-01-01',
                'created_at'     => $now,
                'updated_at'     => $now
            ];
        }
        DB::table('user_schedules')->insert($userSchedules);
    }
}