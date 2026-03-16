<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\employee\AttendanceRecord;
use App\Models\employee\LeaveRequest;
use App\Http\Services\PhilippineHolidayService;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function __construct(
        private readonly PhilippineHolidayService $holidayService
    ) {}

    /**
     * Builds the full calendar grid (42 cells = 6 weeks) for a given user and month.
     */
    public function buildCalendarData(string $userId, int $year, int $month): array
    {
        // 1. Fetch user schedules
        $schedules = DB::table('user_schedules')
            ->join('schedule_templates', 'user_schedules.template_id', '=', 'schedule_templates.id')
            ->where('user_schedules.user_id', $userId)
            ->orderBy('user_schedules.effective_date', 'desc')
            ->select('schedule_templates.id as template_id', 'schedule_templates.name', 'user_schedules.effective_date')
            ->get();

        // 2. Fetch daily rules for those templates
        $templateIds = $schedules->pluck('template_id')->unique();
        $templateDays = DB::table('schedule_template_days')
            ->whereIn('template_id', $templateIds)
            ->get()
            ->groupBy('template_id');

        $attendance = AttendanceRecord::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($r) => $r->date->toDateString());

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $leaves = LeaveRequest::with('leaveType')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $monthEnd->toDateString())
            ->where('end_date', '>=', $monthStart->toDateString())
            ->get();

        $holidays     = $this->holidayService->forYear($year);
        $today        = Carbon::today();
        $firstOfMonth = Carbon::create($year, $month, 1);
        $calStart     = $firstOfMonth->copy()->startOfWeek(Carbon::SUNDAY);

        $cells = [];

        for ($i = 0; $i < 42; $i++) {
            $date    = $calStart->copy()->addDays($i);
            $dateStr = $date->toDateString();

            $activeSchedule = $schedules->firstWhere('effective_date', '<=', $dateStr);
            
            // Default to a rest day if no schedule is found
            $isRestDay = true;
            if ($activeSchedule) {
                // Find the specific rule for this day of the week (0-6)
                $days = $templateDays->get($activeSchedule->template_id) ?? collect();
                $dayConfig = $days->firstWhere('day_of_week', $date->dayOfWeek);
                $isRestDay = !$dayConfig || !$dayConfig->is_working_day;
            }

            $cells[] = [
                'date'           => $date,
                'dateStr'        => $dateStr,
                'isCurrentMonth' => $date->month === $month,
                'isToday'        => $date->isSameDay($today),
                'isRestDay'      => $isRestDay,
                'holiday'        => $holidays[$dateStr] ?? null,
                'attendance'     => $attendance->get($dateStr),
                'leave'          => $this->findLeaveForDate($leaves, $dateStr),
            ];
        }

        return $cells;
    }

    /**
     * Returns the data needed for the "Today's Schedule" card.
     */
    public function getTodayData(string $userId): array
    {
        $today   = Carbon::today();
        $dateStr = $today->toDateString();

        // Retrieve the schedule + the specific day configuration for TODAY
        $activeSchedule = DB::table('user_schedules')
            ->join('schedule_templates', 'user_schedules.template_id', '=', 'schedule_templates.id')
            ->leftJoin('schedule_template_days', function($join) use ($today) {
                $join->on('schedule_templates.id', '=', 'schedule_template_days.template_id')
                     ->where('schedule_template_days.day_of_week', '=', $today->dayOfWeek);
            })
            ->where('user_schedules.user_id', $userId)
            ->where('user_schedules.effective_date', '<=', $dateStr)
            ->orderBy('user_schedules.effective_date', 'desc')
            ->select(
                'schedule_templates.id as template_id',
                'schedule_templates.name', 
                'schedule_template_days.is_working_day', 
                'schedule_template_days.shift_in', 
                'schedule_template_days.shift_out'
            )
            ->first();

        $attendance = AttendanceRecord::where('user_id', $userId)->where('date', $dateStr)->first();

        $leave = LeaveRequest::with('leaveType')
            ->where('user_id', $userId)        
            ->where('status', 'approved')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->first();

        $holiday = $this->holidayService->forDate($today);

        $isRestDay = !$activeSchedule || !$activeSchedule->is_working_day;

        $status = match (true) {
            $isRestDay           => 'Rest Day',
            $leave !== null      => 'On Leave',
            $holiday !== null    => 'Holiday',
            $attendance !== null => ucfirst($attendance->status),
            default              => 'Not yet recorded',
        };

        // Format a neat list of rest days for the UI
        $restDaysList = [];
        if ($activeSchedule) {
            $allRestDays = DB::table('schedule_template_days')
                ->where('template_id', $activeSchedule->template_id)
                ->where('is_working_day', false)
                ->pluck('day_of_week')
                ->toArray();
            
            $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
            foreach ($allRestDays as $d) {
                $restDaysList[] = $dayNames[$d];
            }
        }

        $templateData = $activeSchedule ? (object) [
            'name'      => $activeSchedule->name,
            'shift_in'  => $activeSchedule->shift_in,
            'shift_out' => $activeSchedule->shift_out,
        ] : null;

        return [
            'date'         => $today,
            'template'     => $templateData, 
            'workStart'    => $activeSchedule?->shift_in,
            'workEnd'      => $activeSchedule?->shift_out,
            'isRestDay'    => $isRestDay,
            'holiday'      => $holiday,
            'attendance'   => $attendance,
            'leave'        => $leave,
            'status'       => $status,
            'restDaysList' => $restDaysList,
        ];
    }

    /**
     * Returns monthly attendance summary counts.
     */
    public function getMonthlySummary(string $userId, int $year, int $month): array
    {
        $records = AttendanceRecord::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return [
            'present' => $records->where('status', 'present')->count(),
            'absent'  => $records->where('status', 'absent')->count(),
            'late'    => $records->where('status', 'late')->count(),
            'leave'   => $records->where('status', 'leave')->count(),
            'holiday' => $records->where('status', 'holiday')->count(),
        ];
    }

    private function findLeaveForDate(Collection $leaves, string $dateStr): ?LeaveRequest
    {
        return $leaves->first(
            fn (LeaveRequest $r) => $dateStr >= $r->start_date->toDateString()
                                 && $dateStr <= $r->end_date->toDateString()
        );
    }
}