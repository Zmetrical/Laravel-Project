<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\employee\AttendanceRecord;
use App\Http\Services\AttendanceComputationService;
use Illuminate\Http\Request;

class TeamAttendanceController extends Controller
{
    public function __construct(
        protected AttendanceComputationService $computationService
    ) {}

    // ── Page ──────────────────────────────────────────────────────────────────

    public function index()
    {
        return view('hresource.team.attendance');
    }

    // ── AJAX: Employee list for dropdowns ─────────────────────────────────────

    public function employees()
    {
        $employees = User::active()
            ->orderBy('fullName')
            ->select(['id', 'fullName', 'department', 'position', 'branch'])
            ->get();

        return response()->json($employees);
    }

    // ── AJAX: Records ─────────────────────────────────────────────────────────

    /**
     * GET /team-attendance/records
     *
     * Two modes:
     *  ?date=2025-03-03               → daily view (all employees that day)
     *  ?user_id=EMP-001&year=2025&month=3  → employee monthly view
     */
    public function records(Request $request)
    {
        $request->validate([
            'date'    => ['nullable', 'date'],
            'user_id' => ['nullable', 'exists:users,id'],
            'year'    => ['nullable', 'integer'],
            'month'   => ['nullable', 'integer', 'between:1,12'],
        ]);

        $query = AttendanceRecord::with(['user:id,fullName,department,position'])
            ->when($request->filled('date'), fn($q) =>
                $q->forDate($request->date)
            )
            ->when($request->filled('user_id'), fn($q) =>
                $q->where('user_id', $request->user_id)
            )
            ->when($request->filled('year') && $request->filled('month'), fn($q) =>
                $q->forMonth($request->year, $request->month)
            )
            ->when($request->filled('department'), fn($q) =>
                $q->whereHas('user', fn($q) =>
                    $q->where('department', $request->department)
                )
            )
            ->when($request->filled('status') && $request->status !== 'all', fn($q) =>
                $request->status === 'issues'
                    ? $q->whereIn('status', ['incomplete', 'absent'])
                    : $q->where('status', $request->status)
            )
            ->orderBy('date')
            ->orderByRaw("(SELECT fullName FROM users WHERE users.id = attendance_records.user_id)");

        return response()->json($query->get());
    }

    // ── AJAX: Create or update a single record ────────────────────────────────

    /**
     * POST /team-attendance/upsert
     * Creates or updates an attendance record for a given user+date.
     * Auto-computes hours_worked, late_minutes, undertime_minutes, status.
     */
    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'user_id'  => ['required', 'exists:users,id'],
            'date'     => ['required', 'date'],
            'time_in'  => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'status'   => ['nullable', 'in:present,absent,late,half_day,leave,holiday,rest_day,incomplete'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ]);

        $employee = User::findOrFail($validated['user_id']);

        // Auto-compute unless HR explicitly sets the status
        $computed = $this->computationService->compute(
            $employee,
            $validated['date'],
            $validated['time_in']  ?? null,
            $validated['time_out'] ?? null,
        );

        // HR can override the computed status
        if (!empty($validated['status'])) {
            $computed['status'] = $validated['status'];
        }

        $record = AttendanceRecord::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'date'    => $validated['date'],
            ],
            array_merge($computed, [
                'time_in'      => $validated['time_in']  ?? null,
                'time_out'     => $validated['time_out'] ?? null,
                'notes'        => $validated['notes']    ?? null,
                'is_biometric' => false,
            ])
        );

        return response()->json([
            'message' => 'Attendance record saved.',
            'record'  => $record->load('user:id,fullName,department,position'),
        ]);
    }

    // ── AJAX: Bulk upsert (paste a whole day's records) ───────────────────────

    /**
     * POST /team-attendance/bulk-upsert
     * Accepts array of records for a single date.
     */
    public function bulkUpsert(Request $request)
    {
        $request->validate([
            'date'             => ['required', 'date'],
            'records'          => ['required', 'array', 'min:1'],
            'records.*.user_id'  => ['required', 'exists:users,id'],
            'records.*.time_in'  => ['nullable', 'date_format:H:i'],
            'records.*.time_out' => ['nullable', 'date_format:H:i'],
            'records.*.status'   => ['nullable', 'in:present,absent,late,half_day,leave,holiday,rest_day,incomplete'],
            'records.*.notes'    => ['nullable', 'string', 'max:500'],
        ]);

        $saved = [];

        foreach ($request->records as $row) {
            $employee = User::find($row['user_id']);
            if (!$employee) continue;

            $computed = $this->computationService->compute(
                $employee,
                $request->date,
                $row['time_in']  ?? null,
                $row['time_out'] ?? null,
            );

            if (!empty($row['status'])) {
                $computed['status'] = $row['status'];
            }

            $saved[] = AttendanceRecord::updateOrCreate(
                ['user_id' => $row['user_id'], 'date' => $request->date],
                array_merge($computed, [
                    'time_in'      => $row['time_in']  ?? null,
                    'time_out'     => $row['time_out'] ?? null,
                    'notes'        => $row['notes']    ?? null,
                    'is_biometric' => false,
                ])
            );
        }

        return response()->json([
            'message' => count($saved) . ' record(s) saved.',
            'count'   => count($saved),
        ]);
    }
}