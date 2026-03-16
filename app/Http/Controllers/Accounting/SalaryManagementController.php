<?php

// app/Http/Controllers/Accounting/SalaryManagementController.php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Services\SalaryService;
use App\Http\Services\GovernmentContributionService;

class SalaryManagementController extends Controller
{
    public function __construct(
        protected SalaryService $salaryService,
        protected GovernmentContributionService $govService,
    ) {}

    public function index()
    {
        $stats = $this->salaryService->getStats();

        $departments = User::active()->distinct()->pluck('department')->filter()->sort()->values();
        $positions   = User::active()->distinct()->pluck('position')->filter()->sort()->values();

        return view('accounting.salary.show', compact('stats', 'departments', 'positions'));
    }

    // AJAX — load employee table
    public function list(Request $request)
    {
        $query = User::active()
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q) =>
                    $q->where('fullName', 'like', "%$s%")
                      ->orWhere('id', 'like', "%$s%")
                      ->orWhere('position', 'like', "%$s%")
                      ->orWhere('department', 'like', "%$s%")
                ))
            ->when($request->department, fn($q, $d) => $q->where('department', $d))
            ->when($request->position,   fn($q, $p) => $q->where('position',   $p))
            ->select(['id','fullName','email','department','position',
                      'basicSalary','dailyRate','hourlyRate',
                      'employmentStatus','branch','hireDate'])
            ->orderBy('fullName');

        return response()->json($query->get());
    }

    // AJAX — single employee details (with gov contributions)
    public function show(User $user)
    {
        return response()->json([
            'employee'      => $user,
            'contributions' => $this->govService->compute($user->basicSalary),
        ]);
    }

    // AJAX — update single salary
    public function update(Request $request, User $user)
    {
        $request->validate([
            'basicSalary' => ['required', 'numeric', 'min:0'],
        ]);

        $updated = $this->salaryService->updateSalary($user, $request->basicSalary);

        return response()->json([
            'message'  => 'Salary updated successfully.',
            'employee' => $updated,
        ]);
    }

    // AJAX — bulk update
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'user_ids'    => ['required', 'array'],
            'user_ids.*'  => ['exists:users,id'],
            'basicSalary' => ['required', 'numeric', 'min:0'],
        ]);

        $count = $this->salaryService->bulkUpdate(
            $request->user_ids,
            $request->basicSalary
        );

        return response()->json([
            'message' => "$count employee(s) updated successfully.",
            'count'   => $count,
        ]);
    }
}