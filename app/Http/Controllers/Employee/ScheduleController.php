<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Services\ScheduleService;
use App\Models\employee\LeaveBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    private readonly ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $monthStr = $validated['month'] ?? now()->format('Y-m');
        $date     = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        $userId   = auth()->id();

        $calendarCells = $this->scheduleService->buildCalendarData($userId, $date->year, $date->month);
        $todayData     = $this->scheduleService->getTodayData($userId);
        $summary       = $this->scheduleService->getMonthlySummary($userId, $date->year, $date->month);

        // Fetch dynamic leave balances for the current year
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('user_id', $userId)
            ->where('year', date('Y'))
            ->get();

        $prevMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        return view('employee.schedule', compact(
            'calendarCells', 'todayData', 'summary',
            'date', 'prevMonth', 'nextMonth', 'leaveBalances'
        ));
    }
}