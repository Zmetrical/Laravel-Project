<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSchedule;
use App\Models\employee\ScheduleTemplate;
use App\Models\employee\ScheduleTemplateDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamScheduleController extends Controller
{
    // ── Pages ─────────────────────────────────────────────────────────────────

    public function index()
    {
        return view('hresource.team.schedule');
    }

    // ── AJAX: Templates ───────────────────────────────────────────────────────

    /**
     * GET /team-schedule/templates
     * All templates with days + how many employees are currently assigned.
     */
    public function templates()
    {
        $templates = ScheduleTemplate::with('days')
            ->withCount([
                'userSchedules as employee_count' => function ($q) {
                    // Only count the latest schedule per user
                    $q->whereIn('id', function ($sub) {
                        $sub->selectRaw('MAX(id)')
                            ->from('user_schedules')
                            ->groupBy('user_id');
                    });
                },
            ])
            ->orderBy('name')
            ->get();

        return response()->json($templates);
    }

    /**
     * POST /team-schedule/templates
     * Create a new template with its 7 day rows.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255', 'unique:schedule_templates,name'],
            'grace_period_minutes'  => ['required', 'integer', 'min:0', 'max:60'],
            'is_active'             => ['boolean'],
            'days'                  => ['required', 'array', 'size:7'],
            'days.*.day_of_week'    => ['required', 'integer', 'between:0,6'],
            'days.*.is_working_day' => ['required', 'boolean'],
            'days.*.shift_in'       => ['nullable', 'date_format:H:i'],
            'days.*.shift_out'      => ['nullable', 'date_format:H:i'],
        ]);

        $template = DB::transaction(function () use ($request) {
            $template = ScheduleTemplate::create([
                'name'                 => $request->name,
                'grace_period_minutes' => $request->grace_period_minutes,
                'is_active'            => $request->boolean('is_active', true),
            ]);

            foreach ($request->days as $day) {
                $template->days()->create([
                    'day_of_week'    => $day['day_of_week'],
                    'is_working_day' => (bool) $day['is_working_day'],
                    'shift_in'       => $day['is_working_day'] ? ($day['shift_in']  ?? null) : null,
                    'shift_out'      => $day['is_working_day'] ? ($day['shift_out'] ?? null) : null,
                ]);
            }

            return $template->load('days');
        });

        return response()->json([
            'message'  => 'Schedule template created.',
            'template' => $template,
        ], 201);
    }

    /**
     * PATCH /team-schedule/templates/{template}
     * Update template name/grace period/active + replace all day rows.
     */
    public function update(Request $request, ScheduleTemplate $template)
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255', "unique:schedule_templates,name,{$template->id}"],
            'grace_period_minutes'  => ['required', 'integer', 'min:0', 'max:60'],
            'is_active'             => ['boolean'],
            'days'                  => ['required', 'array', 'size:7'],
            'days.*.day_of_week'    => ['required', 'integer', 'between:0,6'],
            'days.*.is_working_day' => ['required', 'boolean'],
            'days.*.shift_in'       => ['nullable', 'date_format:H:i'],
            'days.*.shift_out'      => ['nullable', 'date_format:H:i'],
        ]);

        DB::transaction(function () use ($request, $template) {
            $template->update([
                'name'                 => $request->name,
                'grace_period_minutes' => $request->grace_period_minutes,
                'is_active'            => $request->boolean('is_active', true),
            ]);

            // Replace days entirely
            $template->days()->delete();

            foreach ($request->days as $day) {
                $template->days()->create([
                    'day_of_week'    => $day['day_of_week'],
                    'is_working_day' => (bool) $day['is_working_day'],
                    'shift_in'       => $day['is_working_day'] ? ($day['shift_in']  ?? null) : null,
                    'shift_out'      => $day['is_working_day'] ? ($day['shift_out'] ?? null) : null,
                ]);
            }
        });

        return response()->json([
            'message'  => 'Template updated.',
            'template' => $template->fresh('days'),
        ]);
    }

    /**
     * DELETE /team-schedule/templates/{template}
     * Only allowed if no employees are currently assigned to this template.
     */
    public function destroy(ScheduleTemplate $template)
    {
        $activeCount = UserSchedule::whereIn('id', function ($q) {
            $q->selectRaw('MAX(id)')->from('user_schedules')->groupBy('user_id');
        })->where('template_id', $template->id)->count();

        if ($activeCount > 0) {
            return response()->json([
                'message' => "Cannot delete — {$activeCount} employee(s) are currently assigned to this template.",
            ], 422);
        }

        $template->days()->delete();
        $template->delete();

        return response()->json(['message' => 'Template deleted.']);
    }

    // ── AJAX: Assignments ─────────────────────────────────────────────────────

    /**
     * GET /team-schedule/assignments
     * All active employees with their current schedule assignment.
     */
    public function assignments(Request $request)
    {
        $employees = User::with(['currentSchedule.template'])
            ->active()
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q) =>
                    $q->where('fullName',    'like', "%$s%")
                      ->orWhere('id',         'like', "%$s%")
                      ->orWhere('department', 'like', "%$s%")
                ))
            ->when($request->department, fn($q, $v) => $q->where('department', $v))
            ->when($request->template_id, fn($q, $v) =>
                $q->whereHas('currentSchedule', fn($q) => $q->where('template_id', $v))
            )
            ->orderBy('fullName')
            ->select(['id', 'fullName', 'department', 'position', 'branch'])
            ->get();

        return response()->json($employees);
    }

    /**
     * POST /team-schedule/assign
     * Assign a template to one or many employees.
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'user_ids'       => ['required', 'array', 'min:1'],
            'user_ids.*'     => ['exists:users,id'],
            'template_id'    => ['required', 'exists:schedule_templates,id'],
            'effective_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->user_ids as $userId) {
                UserSchedule::create([
                    'user_id'        => $userId,
                    'template_id'    => $request->template_id,
                    'effective_date' => $request->effective_date,
                ]);
            }
        });

        $count    = count($request->user_ids);
        $template = ScheduleTemplate::find($request->template_id);

        return response()->json([
            'message' => "{$count} employee(s) assigned to {$template->name}.",
            'count'   => $count,
        ]);
    }
}