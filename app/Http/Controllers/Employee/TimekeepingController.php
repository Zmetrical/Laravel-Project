<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\employee\AttendanceRecord;
use App\Models\employee\Holiday;
use App\Models\employee\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimekeepingController extends Controller
{
    // -------------------------------------------------------------------------
    // INDEX  GET /employee/timekeeping
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $user       = Auth::user();
        $testMode   = session('tk_test_mode', false);
        $today      = now()->toDateString();

        // ── DTR filter params ─────────────────────────────────────────────────
        $filterMonth  = (int) $request->get('month',  now()->month);
        $filterYear   = (int) $request->get('year',   now()->year);
        $filterCutoff = $request->get('cutoff', 'full');

        // ── Calendar navigation params ────────────────────────────────────────
        $calYear  = (int) $request->get('cal_year',  now()->year);
        $calMonth = (int) $request->get('cal_month', now()->month);

        // ── Date range for DTR ────────────────────────────────────────────────
        [$rangeStart, $rangeEnd] = $this->cutoffRange($filterYear, $filterMonth, $filterCutoff);

        // ── Paginated DTR records ─────────────────────────────────────────────
        $records = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$rangeStart, $rangeEnd])
            ->orderByDesc('date')
            ->paginate(15)
            ->withQueryString();

        // ── Today's record (for clock state) ──────────────────────────────────
        $activeDate  = $testMode ? session('tk_test_date', $today) : $today;
        
        $todayRecord = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $activeDate)
            ->first();

        $isClockedIn = $todayRecord && $todayRecord->time_in && ! $todayRecord->time_out;

        // ── Calendar ──────────────────────────────────────────────────────────
        $calendarDays  = $this->buildCalendarDays($user, $calYear, $calMonth, $activeDate);
        $calendarLabel = Carbon::create($calYear, $calMonth, 1)->format('F Y');

        $baseParams   = $request->except(['cal_year', 'cal_month']);
        $prevMonth    = Carbon::create($calYear, $calMonth, 1)->subMonth();
        $nextMonth    = Carbon::create($calYear, $calMonth, 1)->addMonth();
        $calPrevHref  = route('employee.timekeeping.index', array_merge($baseParams, [
            'cal_year'  => $prevMonth->year,
            'cal_month' => $prevMonth->month,
        ]));
        $calNextHref  = route('employee.timekeeping.index', array_merge($baseParams, [
            'cal_year'  => $nextMonth->year,
            'cal_month' => $nextMonth->month,
        ]));

        // ── Stats ─────────────────────────────────────────────────────────────
        $stats = $this->computeStats($user, $activeDate);

        // ── Year options for DTR filter dropdown ──────────────────────────────
        $availableYears = AttendanceRecord::where('user_id', $user->id)
            ->selectRaw('YEAR(date) as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        if (! in_array(now()->year, $availableYears)) {
            array_unshift($availableYears, now()->year);
        }

        return view('employee.timekeeping', [
            'user'           => $user,
            'records'        => $records,
            'isClockedIn'    => $isClockedIn,
            'todayRecord'    => $todayRecord,
            'activeDate'     => $activeDate,
            'calendarDays'   => $calendarDays,
            'calendarLabel'  => $calendarLabel,
            'calPrevHref'    => $calPrevHref,
            'calNextHref'    => $calNextHref,
            'stats'          => $stats,
            'availableYears' => $availableYears,
            'testMode'       => $testMode,
            'testDate'       => session('tk_test_date', $today),
            'testTime'       => session('tk_test_time', '08:00'),
            'filters'        => [
                'month'  => $filterMonth,
                'year'   => $filterYear,
                'cutoff' => $filterCutoff,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // TEST MODE  POST /employee/timekeeping/test-mode
    // -------------------------------------------------------------------------

    public function testMode(Request $request)
    {
        if ($request->input('action') === 'enable') {
            session([
                'tk_test_mode' => true,
                'tk_test_date' => $request->input('test_date', now()->toDateString()),
                'tk_test_time' => $request->input('test_time', '08:00'),
            ]);
        } else {
            session()->forget(['tk_test_mode', 'tk_test_date', 'tk_test_time']);
        }

        return back();
    }

    // -------------------------------------------------------------------------
    // CLOCK IN  POST /employee/timekeeping/clock-in
    // -------------------------------------------------------------------------

    public function clockIn(Request $request)
    {
        $user     = Auth::user();
        $testMode = session('tk_test_mode', false);

        $activeDate = $testMode ? session('tk_test_date', now()->toDateString()) : now()->toDateString();
        $now        = $testMode
            ? Carbon::parse($activeDate . ' ' . $request->input('test_time', session('tk_test_time', '08:00')))
            : now();

        $existing = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $activeDate)
            ->first();

        if ($existing && $existing->time_in) {
            return back()->with('error', 'Already clocked in for ' . Carbon::parse($activeDate)->format('M d, Y') . '.');
        }

        // Use the new dynamic shift resolver
        $shift = $this->resolveShift($user->id, $now);
        
        if (! $shift['is_working_day']) {
            $lateMinutes = 0;
            $status = 'rest_day'; // Allows clock in on rest days, tracks as rest_day duty
        } else {
            $lateMinutes = $this->calcLateMinutes($now, $shift['start'], $activeDate, $shift['grace']);
            $status      = $lateMinutes > 0 ? 'late' : 'present';
        }

        $data = [
            'time_in'      => $now->format('H:i:s'),
            'status'       => $status,
            'late_minutes' => $lateMinutes,
            'is_biometric' => 0,
        ];

        $existing
            ? $existing->update($data)
            : AttendanceRecord::create(array_merge($data, [
                'user_id' => $user->id,
                'date'    => $activeDate,
            ]));

        $msg = 'Clocked in at ' . $now->format('h:i A');
        if ($lateMinutes > 0) {
            $msg .= " — {$lateMinutes} min late.";
        }

        return back()->with('success', $msg);
    }

    // -------------------------------------------------------------------------
    // CLOCK OUT  POST /employee/timekeeping/clock-out
    // -------------------------------------------------------------------------

    public function clockOut(Request $request)
    {
        $user     = Auth::user();
        $testMode = session('tk_test_mode', false);

        $activeDate = $testMode ? session('tk_test_date', now()->toDateString()) : now()->toDateString();
        $now        = $testMode
            ? Carbon::parse($activeDate . ' ' . $request->input('test_time', session('tk_test_time', '16:00')))
            : now();

        $record = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $activeDate)
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->first();

        if (! $record) {
            return back()->with('error', 'No active clock-in found for ' . Carbon::parse($activeDate)->format('M d, Y') . '.');
        }

        // Use the new dynamic shift resolver
        $shift = $this->resolveShift($user->id, Carbon::parse($activeDate));

        $timeIn  = Carbon::parse($activeDate . ' ' . $record->time_in);
        $timeOut = Carbon::parse($activeDate . ' ' . $now->format('H:i:s'));

        if ($shift['type'] === 'night' && $timeOut->lt($timeIn)) {
            $timeOut->addDay();
        }

        $hoursWorked = max(0.0, round($timeIn->diffInMinutes($timeOut) / 60, 2));

        if (! $shift['is_working_day']) {
            $undertimeMinutes = 0;
            $overtimeHours    = $hoursWorked; // Working on a rest day counts entirely as OT
        } else {
            $undertimeMinutes = $this->calcUndertime($timeOut, $shift, $activeDate);
            $overtimeHours    = $this->calcOvertime($timeOut, $shift, $activeDate);
        }

        $record->update([
            'time_out'          => $now->format('H:i:s'),
            'hours_worked'      => $hoursWorked,
            'undertime_minutes' => $undertimeMinutes,
            'overtime_hours'    => $overtimeHours,
        ]);

        return back()->with('success',
            'Clocked out at ' . $now->format('h:i A') . ' — ' . number_format($hoursWorked, 2) . ' hrs worked.'
        );
    }

    public function deleteAttendance(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        AttendanceRecord::where('user_id', Auth::id())
            ->whereDate('date', $request->date)
            ->delete();

        return back()->with('success', 'Attendance for ' . 
            Carbon::parse($request->date)->format('M d, Y') . ' deleted.');
    }

    // =========================================================================
    // PRIVATE — CALENDAR BUILDER
    // =========================================================================

    private function buildCalendarDays($user, int $year, int $month, string $activeDate): array
    {
        $firstOfMonth = Carbon::create($year, $month, 1);

        $attendanceMap = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->get()->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        $holidayMap = Holiday::where('year', $year)->whereMonth('date', $month)
            ->get()->keyBy(fn ($h) => $h->date->format('Y-m-d'));

        $leaveSet = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $firstOfMonth->copy()->endOfMonth()->toDateString())
            ->where('end_date',   '>=', $firstOfMonth->toDateString())
            ->get()
            ->flatMap(fn ($lr) => $this->expandDateRange($lr->start_date, $lr->end_date))
            ->flip();

        $days = array_fill(0, $firstOfMonth->dayOfWeek, null);

        for ($d = 1; $d <= $firstOfMonth->daysInMonth; $d++) {
            $ds    = Carbon::create($year, $month, $d)->toDateString();
            $shift = $this->resolveShift($user->id, Carbon::parse($ds)); // Fetch the specific shift

            $days[] = [
                'date'        => $ds,
                'day'         => $d,
                'is_today'    => $ds === $activeDate,
                'is_past'     => $ds < $activeDate, 
                'is_rest_day' => !$shift['is_working_day'], // Check new logic
                'is_leave'    => isset($leaveSet[$ds]),
                'holiday'     => isset($holidayMap[$ds])
                    ? ['name' => $holidayMap[$ds]->name, 'type' => $holidayMap[$ds]->type]
                    : null,
                'attendance'  => $attendanceMap[$ds] ?? null,
                'shift'       => $shift,
            ];
        }

        while (count($days) % 7 !== 0) {
            $days[] = null;
        }

        return $days;
    }

    // =========================================================================
    // PRIVATE — STATS
    // =========================================================================

    private function computeStats($user, string $activeDate): array
    {
        $now        = Carbon::parse($activeDate);
        $weekStart  = $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $monthStart = $now->copy()->startOfMonth(); 

        $monthRecords = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$monthStart->toDateString(), $activeDate])
            ->get();

        $weekHrs = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $activeDate])
            ->sum('hours_worked');

        $daysPresent = $monthRecords
            ->whereNotNull('time_in')
            ->whereNotIn('status', ['absent'])
            ->unique('date')
            ->count();

        // Count work days using the new template logic
        $workDays = 0;
        $cursor   = $monthStart->copy();
        $endOfMonth = $monthStart->copy()->endOfMonth();

        while ($cursor->lte($endOfMonth)) {
            $shift = $this->resolveShift($user->id, $cursor);
            if ($shift['is_working_day']) {
                $workDays++;
            }
            $cursor->addDay();
        }

        return [
            'week_hours'   => round((float) $weekHrs, 1),
            'month_hours'  => round((float) $monthRecords->sum('hours_worked'), 1),
            'days_present' => $daysPresent,
            'work_days'    => $workDays,
        ];
    }

    // =========================================================================
    // PRIVATE — HELPERS
    // =========================================================================

    private function cutoffRange(int $year, int $month, string $cutoff): array
    {
        $first = Carbon::create($year, $month, 1);
        $last  = $first->copy()->endOfMonth();

        return match ($cutoff) {
            'first'  => [$first->toDateString(), Carbon::create($year, $month, 15)->toDateString()],
            'second' => [Carbon::create($year, $month, 16)->toDateString(), $last->toDateString()],
            default  => [$first->toDateString(), $last->toDateString()],
        };
    }

    /**
     * Replaces the old static schedule fetching. 
     * Finds the specific rule for a specific day using schedule_template_days.
     */
    private function resolveShift($userId, Carbon $date): array
    {
        $dateStr = $date->toDateString();
        $dayOfWeek = $date->dayOfWeek;

        $activeSchedule = DB::table('user_schedules')
            ->join('schedule_templates', 'user_schedules.template_id', '=', 'schedule_templates.id')
            ->leftJoin('schedule_template_days', function($join) use ($dayOfWeek) {
                $join->on('schedule_templates.id', '=', 'schedule_template_days.template_id')
                     ->where('schedule_template_days.day_of_week', '=', $dayOfWeek);
            })
            ->where('user_schedules.user_id', $userId)
            ->where('user_schedules.effective_date', '<=', $dateStr)
            ->orderBy('user_schedules.effective_date', 'desc')
            ->select(
                'schedule_templates.name',
                'schedule_templates.grace_period_minutes',
                'schedule_template_days.is_working_day',
                'schedule_template_days.shift_in',
                'schedule_template_days.shift_out'
            )
            ->first();

        // If a valid working day configuration is found
        if ($activeSchedule && $activeSchedule->is_working_day && $activeSchedule->shift_in) {
            $isNight = $activeSchedule->shift_out < $activeSchedule->shift_in;
            return [
                'is_working_day' => true,
                'start'          => $activeSchedule->shift_in,
                'end'            => $activeSchedule->shift_out,
                'type'           => $isNight ? 'night' : 'day',
                'grace'          => $activeSchedule->grace_period_minutes,
                'label'          => $activeSchedule->name,
            ];
        }

        // Fallback or Rest Day
        return [
            'is_working_day' => false,
            'start'          => null,
            'end'            => null,
            'type'           => 'day',
            'grace'          => 0,
            'label'          => 'Rest Day',
        ];
    }

    private function calcLateMinutes(Carbon $timeIn, string $scheduledStart, string $date, int $gracePeriod): int
    {
        $cutoff = Carbon::parse($date . ' ' . $scheduledStart)->addMinutes($gracePeriod);

        return max(0, (int) $cutoff->diffInMinutes($timeIn, false));
    }

    private function calcUndertime(Carbon $timeOut, array $shift, string $date): int
    {
        $end = Carbon::parse($date . ' ' . $shift['end']);
        if ($shift['type'] === 'night' && $shift['end'] <= '06:00:00') {
            $end->addDay();
        }

        return max(0, (int) $timeOut->diffInMinutes($end, false));
    }

    private function calcOvertime(Carbon $timeOut, array $shift, string $date): float
    {
        $end = Carbon::parse($date . ' ' . $shift['end']);
        if ($shift['type'] === 'night' && $shift['end'] <= '06:00:00') {
            $end->addDay();
        }

        return max(0.0, round($end->diffInMinutes($timeOut, false) / 60, 2));
    }

    private function expandDateRange(string $start, string $end): array
    {
        return collect(CarbonPeriod::create($start, $end))
            ->map(fn ($d) => $d->toDateString())
            ->toArray();
    }
}