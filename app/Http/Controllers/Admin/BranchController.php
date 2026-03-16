<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Branch;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    // =========================================================================
    // PAGE
    // =========================================================================

    public function index()
    {
        return view('admin.branches');
    }

    // =========================================================================
    // STATS
    // =========================================================================

    public function stats(): JsonResponse
    {
        $empCounts  = User::active()
            ->select('branch', DB::raw('count(*) as total'))
            ->whereNotNull('branch')
            ->groupBy('branch')
            ->pluck('total', 'branch');

        $deptCounts = User::active()
            ->select('branch', 'department')
            ->whereNotNull('branch')
            ->whereNotNull('department')
            ->distinct()
            ->get()
            ->groupBy('branch')
            ->map(fn ($rows) => $rows->count());

        $branches = Branch::all();

        return response()->json([
            'total'       => $branches->count(),
            'active'      => $branches->where('status', 'active')->count(),
            'total_emp'   => $empCounts->sum(),
            'total_depts' => $deptCounts->sum(),
            'cities'      => $branches->whereNotNull('city')->pluck('city')->unique()->count(),
        ]);
    }

    // =========================================================================
    // LIST
    // =========================================================================

    public function list(): JsonResponse
    {
        $empCounts = User::active()
            ->select('branch', DB::raw('count(*) as total'))
            ->whereNotNull('branch')
            ->groupBy('branch')
            ->pluck('total', 'branch');

        $deptCounts = User::active()
            ->select('branch', 'department')
            ->whereNotNull('branch')
            ->whereNotNull('department')
            ->distinct()
            ->get()
            ->groupBy('branch')
            ->map(fn ($rows) => $rows->count());

        $records = Branch::orderByDesc('is_main')->orderBy('name')->get()
            ->map(fn ($b) => [
                'id'              => $b->id,
                'name'            => $b->name,
                'code'            => $b->code,
                'address'         => $b->address       ?? '',
                'city'            => $b->city          ?? '',
                'contact_number'  => $b->contact_number ?? '',
                'email'           => $b->email         ?? '',
                'manager_name'    => $b->manager_name  ?? '',
                'is_main'         => $b->is_main,
                'status'          => $b->status,
                'employee_count'  => $empCounts->get($b->name, 0),
                'dept_count'      => $deptCounts->get($b->name, 0),
                'created_at'      => $b->created_at?->format('M d, Y'),
            ]);

        return response()->json($records);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100', 'unique:branches,name'],
            'code'           => ['required', 'string', 'max:20',  'unique:branches,code'],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email',  'max:100'],
            'manager_name'   => ['nullable', 'string', 'max:100'],
            'is_main'        => ['boolean'],
            'status'         => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        // Only one main branch at a time
        if (! empty($validated['is_main'])) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch = Branch::create($validated);

        return response()->json([
            'message' => "Branch \"{$branch->name}\" created successfully.",
            'id'      => $branch->id,
        ], 201);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100',
                                 Rule::unique('branches', 'name')->ignore($branch->id)],
            'code'           => ['required', 'string', 'max:20',
                                 Rule::unique('branches', 'code')->ignore($branch->id)],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email',  'max:100'],
            'manager_name'   => ['nullable', 'string', 'max:100'],
            'is_main'        => ['boolean'],
            'status'         => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        // Cascade name change to users.branch
        if ($branch->name !== $validated['name']) {
            User::where('branch', $branch->name)
                ->update(['branch' => $validated['name']]);
        }

        // Only one main branch
        if (! empty($validated['is_main'])) {
            Branch::where('is_main', true)
                ->where('id', '!=', $branch->id)
                ->update(['is_main' => false]);
        }

        $branch->update($validated);

        return response()->json(['message' => "Branch \"{$branch->name}\" updated successfully."]);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(Branch $branch): JsonResponse
    {
        if ($branch->is_main) {
            return response()->json([
                'message' => 'Cannot delete the main branch. Set another branch as main first.',
            ], 422);
        }

        $count = User::active()->where('branch', $branch->name)->count();

        if ($count > 0) {
            return response()->json([
                'message' => "Cannot delete \"{$branch->name}\" — {$count} active employee(s) are assigned. Reassign them first.",
            ], 422);
        }

        $branch->delete();

        return response()->json(['message' => "Branch \"{$branch->name}\" deleted."]);
    }
}