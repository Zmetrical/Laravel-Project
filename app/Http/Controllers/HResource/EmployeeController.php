<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\employee\ScheduleTemplate;
use App\Http\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    // ── Pages ─────────────────────────────────────────────────────────────────

    public function index()
    {
        return view('hresource.employees.index');
    }

    public function create()
    {
        $scheduleTemplates = ScheduleTemplate::with('days')->get();
        return view('hresource.employees.create', compact('scheduleTemplates'));
    }

    public function edit(User $employee)
    {
        $scheduleTemplates = ScheduleTemplate::with('days')->get();
        $employee->load(['currentSchedule.template.days']);
        return view('hresource.employees.edit', compact('employee', 'scheduleTemplates'));
    }

    public function show(User $employee)
    {
        $employee->load([
            'currentSchedule.template.days',
            'leaveBalances.leaveType',
            'loans' => fn($q) => $q->where('status', 'active'),
        ]);
        return view('hresource.employees.show', compact('employee'));
    }

    // ── AJAX ──────────────────────────────────────────────────────────────────

    /**
     * Employee list for the index table (GET /employees/data/list)
     */
    public function list(Request $request)
    {
        $employees = User::with(['currentSchedule.template'])
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q) =>
                    $q->where('fullName',    'like', "%$s%")
                      ->orWhere('id',         'like', "%$s%")
                      ->orWhere('position',   'like', "%$s%")
                      ->orWhere('department', 'like', "%$s%")
                ))
            ->when($request->department,       fn($q, $v) => $q->where('department',       $v))
            ->when($request->employmentStatus, fn($q, $v) => $q->where('employmentStatus', $v))
            ->when($request->branch,           fn($q, $v) => $q->where('branch',           $v))
            ->when($request->filled('isActive'), fn($q)   => $q->where('isActive', (bool) $request->isActive))
            ->orderBy('fullName')
            ->select([
                'id', 'fullName', 'email', 'department', 'position',
                'branch', 'employmentStatus', 'isActive',
                'basicSalary', 'dailyRate', 'hourlyRate', 'hireDate',
            ])
            ->get();

        return response()->json($employees);
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullName'         => ['required', 'string', 'max:100'],
            'firstName'        => ['nullable', 'string', 'max:50'],
            'middleName'       => ['nullable', 'string', 'max:50'],
            'lastName'         => ['nullable', 'string', 'max:50'],
            'gender'           => ['nullable', 'in:Male,Female,Other'],
            'civilStatus'      => ['nullable', 'string', 'max:20'],
            'dateOfBirth'      => ['nullable', 'date'],
            'email'            => ['nullable', 'email', 'unique:users,email'],
            'phoneNumber'      => ['nullable', 'string', 'max:20'],
            'addressStreet'    => ['nullable', 'string', 'max:255'],
            'addressBarangay'  => ['nullable', 'string', 'max:100'],
            'addressCity'      => ['nullable', 'string', 'max:100'],
            'addressProvince'  => ['nullable', 'string', 'max:100'],
            'addressRegion'    => ['nullable', 'string', 'max:100'],
            'addressZipCode'   => ['nullable', 'string', 'max:10'],
            'department'       => ['nullable', 'string', 'max:100'],
            'position'         => ['nullable', 'string', 'max:100'],
            'branch'           => ['required', 'string', 'max:100'],
            'hireDate'         => ['nullable', 'date'],
            'basicSalary'      => ['required', 'numeric', 'min:0'],
            'employmentStatus' => ['required', 'in:probationary,regular,resigned,terminated'],
            'role'             => ['required', 'in:employee,hr,accounting,admin'],
            'username'         => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'password'         => ['nullable', 'string', 'min:6'],
            'template_id'      => ['nullable', 'exists:schedule_templates,id'],
            'effective_date'   => ['nullable', 'date'],
        ]);

        $employee = $this->employeeService->create($validated);

        // Assign schedule if provided
        if (!empty($validated['template_id'])) {
            $this->employeeService->assignSchedule(
                $employee,
                $validated['template_id'],
                $validated['effective_date'] ?? now()->toDateString()
            );
        }

        return redirect()
            ->route('hresource.employees.show', $employee)
            ->with('success', "Employee {$employee->fullName} created successfully.");
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'fullName'         => ['required', 'string', 'max:100'],
            'firstName'        => ['nullable', 'string', 'max:50'],
            'middleName'       => ['nullable', 'string', 'max:50'],
            'lastName'         => ['nullable', 'string', 'max:50'],
            'gender'           => ['nullable', 'in:Male,Female,Other'],
            'civilStatus'      => ['nullable', 'string', 'max:20'],
            'dateOfBirth'      => ['nullable', 'date'],
            'email'            => ['nullable', 'email', "unique:users,email,{$employee->id},id"],
            'phoneNumber'      => ['nullable', 'string', 'max:20'],
            'addressStreet'    => ['nullable', 'string', 'max:255'],
            'addressBarangay'  => ['nullable', 'string', 'max:100'],
            'addressCity'      => ['nullable', 'string', 'max:100'],
            'addressProvince'  => ['nullable', 'string', 'max:100'],
            'addressRegion'    => ['nullable', 'string', 'max:100'],
            'addressZipCode'   => ['nullable', 'string', 'max:10'],
            'department'       => ['nullable', 'string', 'max:100'],
            'position'         => ['nullable', 'string', 'max:100'],
            'branch'           => ['required', 'string', 'max:100'],
            'hireDate'         => ['nullable', 'date'],
            'basicSalary'      => ['required', 'numeric', 'min:0'],
            'employmentStatus' => ['required', 'in:probationary,regular,resigned,terminated'],
            'role'             => ['required', 'in:employee,hr,accounting,admin'],
            'template_id'      => ['nullable', 'exists:schedule_templates,id'],
            'effective_date'   => ['nullable', 'date'],
        ]);

        $this->employeeService->update($employee, $validated);

        // Reassign schedule if changed
        if (!empty($validated['template_id'])) {
            $this->employeeService->assignSchedule(
                $employee,
                $validated['template_id'],
                $validated['effective_date'] ?? now()->toDateString()
            );
        }

        return redirect()
            ->route('hresource.employees.show', $employee)
            ->with('success', "Employee {$employee->fullName} updated successfully.");
    }

    public function toggleStatus(User $employee)
    {
        $updated = $this->employeeService->toggleStatus($employee);
        $label   = $updated->isActive ? 'activated' : 'deactivated';

        return redirect()
            ->route('hresource.employees.show', $employee)
            ->with('success', "Employee {$employee->fullName} {$label} successfully.");
    }
}