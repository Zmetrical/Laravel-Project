<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Department;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    private const LEADER_KEYWORDS = [
        'supervisor', 'manager', 'team leader', 'head', 'officer', 'lead',
    ];

    // =========================================================================
    // PAGE
    // =========================================================================

    public function index()
    {
        return view('admin.departments');
    }

    // =========================================================================
    // STATS
    // =========================================================================

    public function stats(): JsonResponse
    {
        $empCounts = User::active()
            ->select('department', DB::raw('count(*) as total'))
            ->whereNotNull('department')
            ->groupBy('department')
            ->pluck('total', 'department');

        $depts  = Department::all();
        $total  = $depts->count();
        $active = $depts->where('status', 'active')->count();

        $totalEmp    = $empCounts->sum();
        $largestName = $empCounts->sortDesc()->keys()->first() ?? '—';
        $largestCnt  = $empCounts->max() ?? 0;

        $branches    = User::active()->whereNotNull('branch')->distinct()->pluck('branch');

        return response()->json([
            'total'           => $total,
            'active'          => $active,
            'total_employees' => $totalEmp,
            'largest_name'    => $largestName,
            'largest_count'   => $largestCnt,
            'branch_count'    => $branches->count(),
            'main_branch'     => $branches->first() ?? '—',
        ]);
    }

    // =========================================================================
    // LIST
    // =========================================================================

    public function list(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $branch = $request->query('branch', '');
        $status = $request->query('status', '');

        $empCounts = User::active()
            ->select('department', DB::raw('count(*) as total'))
            ->whereNotNull('department')
            ->groupBy('department')
            ->pluck('total', 'department');

        $query = Department::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',        'like', "%{$search}%")
                  ->orWhere('code',        'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($branch) $query->where('branch', $branch);
        if ($status) $query->where('status', $status);

        $departments = $query->orderBy('name')->get();

        // Batch-fetch all heads referenced across all departments
        $allHeadIds = $departments
            ->flatMap(fn ($d) => $d->head_employee_ids ?? [])
            ->filter()->unique()->values();

        $heads = $allHeadIds->isNotEmpty()
            ? User::whereIn('id', $allHeadIds)->get(['id', 'fullName'])->keyBy('id')
            : collect();

        $records = $departments->map(function ($d) use ($empCounts, $heads) {
            $headIds   = $d->head_employee_ids ?? [];
            $headNames = collect($headIds)
                ->map(fn ($id) => $heads->get($id)?->fullName)
                ->filter()
                ->implode(', ');

            return [
                'id'             => $d->id,
                'name'           => $d->name,
                'code'           => $d->code,
                'description'    => $d->description ?? '',
                'branch'         => $d->branch       ?? '—',
                'status'         => $d->status,
                'head_ids'       => $headIds,
                'head_names'     => $headNames ?: '—',
                'employee_count' => $empCounts->get($d->name, 0),
            ];
        });

        return response()->json($records);
    }

    // =========================================================================
    // BRANCHES (filter dropdown)
    // =========================================================================

    public function branches(): JsonResponse
    {
        $branches = User::whereNotNull('branch')
            ->distinct()
            ->orderBy('branch')
            ->pluck('branch');

        return response()->json($branches);
    }

    // =========================================================================
    // HEAD CANDIDATES
    // =========================================================================

    public function headCandidates(Request $request): JsonResponse
    {
        $deptName = $request->query('department', '');
        $keywords = self::LEADER_KEYWORDS;

        $query = User::active()
            ->select('id', 'fullName', 'position', 'department')
            ->orderBy('fullName');

        if ($deptName) {
            $query->where('department', $deptName);
        }

        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $kw) {
                $q->orWhere('position', 'like', "%{$kw}%");
            }
        });

        return response()->json(
            $query->get()->map(fn ($u) => [
                'id'        => $u->id,
                'full_name' => $u->fullName,
                'position'  => $u->position   ?? '—',
                'department'=> $u->department  ?? '—',
            ])
        );
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100', 'unique:departments,name'],
            'code'               => ['required', 'string', 'max:20',  'unique:departments,code'],
            'description'        => ['nullable', 'string', 'max:500'],
            'branch'             => ['nullable', 'string', 'max:100'],
            'status'             => ['required', Rule::in(['active', 'inactive'])],
            'head_employee_ids'  => ['nullable', 'array'],
            'head_employee_ids.*'=> ['string', 'exists:users,id'],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $dept = Department::create($validated);

        return response()->json([
            'message' => 'Department created successfully.',
            'id'      => $dept->id,
        ], 201);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100',
                                     Rule::unique('departments', 'name')->ignore($department->id)],
            'code'               => ['required', 'string', 'max:20',
                                     Rule::unique('departments', 'code')->ignore($department->id)],
            'description'        => ['nullable', 'string', 'max:500'],
            'branch'             => ['nullable', 'string', 'max:100'],
            'status'             => ['required', Rule::in(['active', 'inactive'])],
            'head_employee_ids'  => ['nullable', 'array'],
            'head_employee_ids.*'=> ['string', 'exists:users,id'],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        // Cascade name change to users.department
        if ($department->name !== $validated['name']) {
            User::where('department', $department->name)
                ->update(['department' => $validated['name']]);
        }

        $department->update($validated);

        return response()->json(['message' => 'Department updated successfully.']);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(Department $department): JsonResponse
    {
        $count = User::active()->where('department', $department->name)->count();

        if ($count > 0) {
            return response()->json([
                'message' => "Cannot delete \"{$department->name}\" — {$count} active employee(s) are assigned. Reassign them first.",
            ], 422);
        }

        $department->delete();

        return response()->json(['message' => "Department \"{$department->name}\" deleted."]);
    }
}