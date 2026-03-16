<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Position;
use App\Models\admin\Department;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    // =========================================================================
    // PAGE
    // =========================================================================

    public function index()
    {
        return view('admin.positions');
    }

    // =========================================================================
    // STATS
    // =========================================================================

    public function stats(): JsonResponse
    {
        return response()->json([
            'total'  => Position::count(),
            'active' => Position::active()->count(),
            'depts'  => Position::whereNotNull('department')
                            ->distinct('department')
                            ->count('department'),
        ]);
    }

    // =========================================================================
    // DEPARTMENTS (filter + modal dropdown)
    // Prefers departments table; falls back to distinct users.department
    // =========================================================================

    public function departments(): JsonResponse
    {
        if (DB::getSchemaBuilder()->hasTable('departments')) {
            $depts = Department::active()->orderBy('name')->pluck('name');
        } else {
            $depts = User::whereNotNull('department')
                ->distinct()->orderBy('department')->pluck('department');
        }

        return response()->json($depts);
    }

    // =========================================================================
    // LIST
    // =========================================================================

    public function list(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $dept   = $request->query('department', '');
        $status = $request->query('status', '');

        $empCounts = User::active()
            ->select('position', DB::raw('count(*) as total'))
            ->whereNotNull('position')
            ->groupBy('position')
            ->pluck('total', 'position');

        $query = Position::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',        'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('description','like', "%{$search}%");
            });
        }

        if ($dept)   $query->where('department', $dept);
        if ($status) $query->where('status', $status);

        $records = $query->orderBy('department')->orderBy('name')->get()
            ->map(fn ($p) => [
                'id'             => $p->id,
                'name'           => $p->name,
                'department'     => $p->department ?? '—',
                'description'    => $p->description ?? '',
                'status'         => $p->status,
                'employee_count' => $empCounts->get($p->name, 0),
            ]);

        return response()->json($records);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:positions,name'],
            'department'  => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $pos = Position::create($validated);

        return response()->json([
            'message' => "Position \"{$pos->name}\" created successfully.",
            'id'      => $pos->id,
        ], 201);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, Position $position): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100',
                              Rule::unique('positions', 'name')->ignore($position->id)],
            'department'  => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Cascade name change to users.position
        if ($position->name !== $validated['name']) {
            User::where('position', $position->name)
                ->update(['position' => $validated['name']]);
        }

        $position->update($validated);

        return response()->json(['message' => "Position \"{$position->name}\" updated successfully."]);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(Position $position): JsonResponse
    {
        $count = User::active()->where('position', $position->name)->count();

        if ($count > 0) {
            return response()->json([
                'message' => "Cannot delete \"{$position->name}\" — {$count} active employee(s) hold this position. Reassign them first.",
            ], 422);
        }

        $position->delete();

        return response()->json(['message' => "Position \"{$position->name}\" deleted."]);
    }
}