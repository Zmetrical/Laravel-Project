<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\employee\Holiday;
use Carbon\Carbon;

class AttendanceComputationService
{
    const STD_HOURS = 8;

    /**
     * Given time_in, time_out, and the employee's schedule for that date,
     * compute hours_worked, late_minutes, undertime_minutes, and suggested status.
     *
     * Returns array ready to merge into AttendanceRecord fillable.
     */
    public function compute(
        User   $employee,
        string $date,
        ?string $timeIn,
        ?string $timeOut
    ): array {
        // Defaults
        $result = [
            'hours_worked'      => 0,
            'late_minutes'      => 0,
            'undertime_minutes' => 0,
            'status'            => 'incomplete',
        ];

        // No time in at all → absent
        if (!$timeIn) {
            $result['status'] = 'absent';
            return $result;
        }

        // Time in but no time out → incomplete
        if (!$timeOut) {
            $result['status'] = 'incomplete';
            return $result;
        }

        // Get employee's schedule for this date
        $carbon   = Carbon::parse($date);
        $schedule = $employee->schedules()
            ->with('template.days')
            ->where('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->first();

        $scheduleDay = null;
        if ($schedule && $schedule->template) {
            $scheduleDay = $schedule->template->days
                ->firstWhere('day_of_week', $carbon->dayOfWeek);
        }

        // Check holiday
        $isHoliday = Holiday::where('date', $date)->exists();
        if ($isHoliday) {
            $result['status'] = 'holiday';
            // Still compute hours if they worked
        }

        // Check rest day (not a working day in schedule)
        if ($scheduleDay && !$scheduleDay->is_working_day) {
            $result['status'] = 'rest_day';
        }

        // Compute hours worked
        $inCarbon  = Carbon::parse("{$date} {$timeIn}");
        $outCarbon = Carbon::parse("{$date} {$timeOut}");

        // Handle overnight shift
        if ($outCarbon->lt($inCarbon)) {
            $outCarbon->addDay();
        }

        $hoursWorked = $inCarbon->floatDiffInHours($outCarbon);
        $result['hours_worked'] = round(min($hoursWorked, 24), 2);

        // Late & undertime only apply on regular working days
        if ($scheduleDay && $scheduleDay->is_working_day && $scheduleDay->shift_in) {
            $gracePeriod = $schedule->template->grace_period_minutes ?? 0;

            $shiftIn  = Carbon::parse("{$date} {$scheduleDay->shift_in}");
            $shiftOut = $scheduleDay->shift_out
                ? Carbon::parse("{$date} {$scheduleDay->shift_out}")
                : null;

            // Handle overnight shift_out
            if ($shiftOut && $shiftOut->lt($shiftIn)) {
                $shiftOut->addDay();
            }

            // Late minutes (after grace period)
            $lateMinutes = max(0, $inCarbon->diffInMinutes($shiftIn, false) * -1);
            $lateMinutes = max(0, $lateMinutes - $gracePeriod);
            $result['late_minutes'] = round($lateMinutes, 2);

            // Undertime minutes (left before shift end)
            $undertimeMinutes = 0;
            if ($shiftOut) {
                $undertimeMinutes = max(0, $outCarbon->diffInMinutes($shiftOut, false) * -1);
            }
            $result['undertime_minutes'] = round($undertimeMinutes, 2);

            // Determine status
            if ($result['status'] !== 'holiday' && $result['status'] !== 'rest_day') {
                if ($lateMinutes > 0) {
                    $result['status'] = 'late';
                } elseif ($undertimeMinutes > ($gracePeriod * 2)) {
                    $result['status'] = 'half_day';
                } else {
                    $result['status'] = 'present';
                }
            }
        } else {
            // No schedule → just mark present if they have both times
            if ($result['status'] === 'incomplete') {
                $result['status'] = 'present';
            }
        }

        return $result;
    }
}