@extends('layouts.main')

@section('title', 'Employee Master Records')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">HR Management</a></li>
        <li class="breadcrumb-item active">Employee Records</li>
    </ol>
@endsection

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Employee Master Records</h4>
        <small class="text-muted">Manage and maintain employee information</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-secondary" id="btnExport">
            <i class="bi bi-download me-1"></i> Export
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="bi bi-person-plus me-1"></i> Add Employee
        </button>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="row g-3 mb-4" id="statsCards">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Total Employees</p>
                        <h3 class="mb-0 text-primary" id="statTotal">0</h3>
                        <small class="text-muted">Active records</small>
                    </div>
                    <span class="text-primary"><i class="bi bi-people fs-4"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Regular</p>
                        <h3 class="mb-0 text-secondary" id="statRegular">0</h3>
                        <small class="text-muted">Permanent employees</small>
                    </div>
                    <span class="text-secondary"><i class="bi bi-person-check fs-4"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Probationary</p>
                        <h3 class="mb-0 text-secondary" id="statProbationary">0</h3>
                        <small class="text-muted">Under evaluation</small>
                    </div>
                    <span class="text-secondary"><i class="bi bi-person-dash fs-4"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Ready for Regular</p>
                        <h3 class="mb-0 text-primary" id="statReady">0</h3>
                        <small class="text-muted">Ready for promotion</small>
                    </div>
                    <span class="text-primary"><i class="bi bi-arrow-up-circle fs-4"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted small">Search Employee</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Search by name or ID...">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Department</label>
                <select class="form-select" id="filterDepartment">
                    <option value="all">All Departments</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Employment Status</label>
                <select class="form-select" id="filterStatus">
                    <option value="Probationary">Probationary</option>
                    <option value="ready-regular">→ REGULAR (Ready for Promotion)</option>
                    <option value="Regular">Regular</option>
                    <option value="Resigned">Resigned</option>
                    <option value="Retired">Retired</option>
                    <option value="Suspended">Suspended</option>
                    <option value="deactivated">Deactivated Accounts</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Employee Table --}}
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Employee List <span class="badge bg-secondary ms-2" id="recordCount">0</span></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="employeeTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Employee ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Hire Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="employeeTableBody">
                    <tr id="loadingRow">
                        <td colspan="7" class="text-center py-4 text-muted">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===================== VIEW EMPLOYEE MODAL ===================== --}}
<div class="modal fade" id="viewEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                {{-- Dynamically populated --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewToEditBtn">
                    <i class="bi bi-pencil me-1"></i> Edit Record
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== EDIT EMPLOYEE MODAL ===================== --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    <input type="hidden" id="editEmployeeId">
                    <ul class="nav nav-tabs mb-3" id="editTabs">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#editPersonal">Personal</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#editEmployment">Employment</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#editSalary">Salary</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#editGovernment">Government IDs</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="editPersonal">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="editFirstName">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="editMiddleName">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="editLastName">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editEmail">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="editPhone">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" id="editGender">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="editDateOfBirth">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Civil Status</label>
                                    <select class="form-select" id="editCivilStatus">
                                        <option>Single</option>
                                        <option>Married</option>
                                        <option>Widowed</option>
                                        <option>Separated</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="editEmployment">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <select class="form-select" id="editDepartment"></select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Position</label>
                                    <input type="text" class="form-control" id="editPosition">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Employment Status</label>
                                    <select class="form-select" id="editEmploymentStatus">
                                        <option value="Probationary">Probationary</option>
                                        <option value="Regular">Regular</option>
                                        <option value="Resigned">Resigned</option>
                                        <option value="Retired">Retired</option>
                                        <option value="Suspended">Suspended</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" id="editHireDate">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Default Shift</label>
                                    <select class="form-select" id="editDefaultShift">
                                        <option value="Day">Day</option>
                                        <option value="Night">Night</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Branch</label>
                                    <input type="text" class="form-control" id="editBranch">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="editSalary">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Basic Salary</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="editBasicSalary" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Daily Rate</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="editDailyRate" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Hourly Rate</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="editHourlyRate" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tax Status</label>
                                    <select class="form-select" id="editTaxStatus">
                                        <option value="S">S - Single</option>
                                        <option value="ME">ME - Married</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="ME1">ME1</option>
                                        <option value="ME2">ME2</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="editGovernment">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">SSS Number</label>
                                    <input type="text" class="form-control" id="editSssNumber">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PhilHealth Number</label>
                                    <input type="text" class="form-control" id="editPhilHealthNumber">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pag-IBIG Number</label>
                                    <input type="text" class="form-control" id="editPagibigNumber">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">TIN Number</label>
                                    <input type="text" class="form-control" id="editTinNumber">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditBtn">
                    <i class="bi bi-floppy me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== ADD EMPLOYEE MODAL ===================== --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addFirstName" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="addMiddleName">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addLastName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="addEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="addPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="addDepartment" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addPosition" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hire Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="addHireDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Basic Salary</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="addBasicSalary" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Shift</label>
                            <select class="form-select" id="addDefaultShift">
                                <option value="Day">Day</option>
                                <option value="Night">Night</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employment Status</label>
                            <select class="form-select" id="addEmploymentStatus">
                                <option value="Probationary">Probationary</option>
                                <option value="Regular">Regular</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAddBtn">
                    <i class="bi bi-person-plus me-1"></i> Add Employee
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ============================================================
//  EMPLOYEE RECORDS — Payroll System PH
//  All data lives in this JS layer (replace with API calls)
// ============================================================

// ── SAMPLE DATA ──────────────────────────────────────────────
let employees = [
    {
        id: 'EMP-0001', username: 'jdela.cruz', fullName: 'Juan Dela Cruz',
        firstName: 'Juan', middleName: 'Santos', lastName: 'Dela Cruz',
        email: 'juan.delacruz@fastservices.com', phoneNumber: '09171234567',
        gender: 'Male', dateOfBirth: '1990-03-15', civilStatus: 'Married',
        department: 'Operations', position: 'Operations Supervisor',
        branch: 'Meycauayan Main', hireDate: '2022-01-10',
        employmentStatus: 'Regular', status: 'active',
        defaultShift: 'Day', taxStatus: 'ME',
        basicSalary: 35000.00, dailyRate: 1346.15, hourlyRate: 168.27,
        sssNumber: '34-1234567-8', philHealthNumber: '12-345678901-2',
        pagIbigNumber: '1234-5678-9012', tinNumber: '123-456-789-000',
        sickLeaveBalance: 5, vacationLeaveBalance: 5,
        regularizationDate: null
    },
    {
        id: 'EMP-0002', username: 'm.santos', fullName: 'Maria Clara Santos',
        firstName: 'Maria Clara', middleName: '', lastName: 'Santos',
        email: 'maria.santos@fastservices.com', phoneNumber: '09189876543',
        gender: 'Female', dateOfBirth: '1995-07-22', civilStatus: 'Single',
        department: 'Human Resources', position: 'HR Specialist',
        branch: 'Meycauayan Main', hireDate: '2023-08-01',
        employmentStatus: 'Probationary', status: 'active',
        defaultShift: 'Day', taxStatus: 'S',
        basicSalary: 28000.00, dailyRate: 1076.92, hourlyRate: 134.62,
        sssNumber: '34-9876543-2', philHealthNumber: '12-987654321-0',
        pagIbigNumber: '9876-5432-1098', tinNumber: '987-654-321-000',
        sickLeaveBalance: 5, vacationLeaveBalance: 5,
        regularizationDate: '2024-02-01'
    },
    {
        id: 'EMP-0003', username: 'r.garcia', fullName: 'Roberto Garcia',
        firstName: 'Roberto', middleName: 'Lim', lastName: 'Garcia',
        email: 'roberto.garcia@fastservices.com', phoneNumber: '09205551234',
        gender: 'Male', dateOfBirth: '1988-11-05', civilStatus: 'Married',
        department: 'Accounting', position: 'Senior Accountant',
        branch: 'Meycauayan Main', hireDate: '2019-05-20',
        employmentStatus: 'Regular', status: 'active',
        defaultShift: 'Day', taxStatus: 'ME1',
        basicSalary: 45000.00, dailyRate: 1730.77, hourlyRate: 216.35,
        sssNumber: '34-5555555-5', philHealthNumber: '12-555555555-5',
        pagIbigNumber: '5555-5555-5555', tinNumber: '555-555-555-000',
        sickLeaveBalance: 10, vacationLeaveBalance: 12,
        regularizationDate: null
    },
    {
        id: 'EMP-0004', username: 'a.reyes', fullName: 'Ana Marie Reyes',
        firstName: 'Ana Marie', middleName: 'Cruz', lastName: 'Reyes',
        email: 'ana.reyes@fastservices.com', phoneNumber: '09271112233',
        gender: 'Female', dateOfBirth: '1993-02-28', civilStatus: 'Single',
        department: 'IT', position: 'Software Developer',
        branch: 'Meycauayan Main', hireDate: '2024-07-15',
        employmentStatus: 'Probationary', status: 'active',
        defaultShift: 'Day', taxStatus: 'S',
        basicSalary: 32000.00, dailyRate: 1230.77, hourlyRate: 153.85,
        sssNumber: '34-4444444-4', philHealthNumber: '12-444444444-4',
        pagIbigNumber: '4444-4444-4444', tinNumber: '444-444-444-000',
        sickLeaveBalance: 3, vacationLeaveBalance: 3,
        regularizationDate: '2025-01-15'
    },
    {
        id: 'EMP-0005', username: 'b.torres', fullName: 'Benjamin Torres',
        firstName: 'Benjamin', middleName: '', lastName: 'Torres',
        email: 'benjamin.torres@fastservices.com', phoneNumber: '09331234567',
        gender: 'Male', dateOfBirth: '1975-09-10', civilStatus: 'Married',
        department: 'Operations', position: 'Logistics Manager',
        branch: 'Meycauayan Main', hireDate: '2015-03-01',
        employmentStatus: 'Regular', status: 'active',
        defaultShift: 'Night', taxStatus: 'ME2',
        basicSalary: 55000.00, dailyRate: 2115.38, hourlyRate: 264.42,
        sssNumber: '34-3333333-3', philHealthNumber: '12-333333333-3',
        pagIbigNumber: '3333-3333-3333', tinNumber: '333-333-333-000',
        sickLeaveBalance: 15, vacationLeaveBalance: 15,
        regularizationDate: null
    },
    {
        id: 'EMP-0006', username: 'c.flores', fullName: 'Carmen Flores',
        firstName: 'Carmen', middleName: 'Bautista', lastName: 'Flores',
        email: 'carmen.flores@fastservices.com', phoneNumber: '09451231234',
        gender: 'Female', dateOfBirth: '1998-06-14', civilStatus: 'Single',
        department: 'HR', position: 'HR Assistant',
        branch: 'Meycauayan Main', hireDate: '2024-09-01',
        employmentStatus: 'Probationary', status: 'active',
        defaultShift: 'Day', taxStatus: 'S',
        basicSalary: 20000.00, dailyRate: 769.23, hourlyRate: 96.15,
        sssNumber: '34-2222222-2', philHealthNumber: '12-222222222-2',
        pagIbigNumber: '2222-2222-2222', tinNumber: '222-222-222-000',
        sickLeaveBalance: 2, vacationLeaveBalance: 2,
        regularizationDate: '2025-03-01'
    },
    {
        id: 'EMP-0007', username: 'd.mendoza', fullName: 'Diego Mendoza',
        firstName: 'Diego', middleName: '', lastName: 'Mendoza',
        email: 'diego.mendoza@fastservices.com', phoneNumber: '09561239999',
        gender: 'Male', dateOfBirth: '1980-12-01', civilStatus: 'Widowed',
        department: 'Security', position: 'Security Officer',
        branch: 'Meycauayan Main', hireDate: '2020-06-15',
        employmentStatus: 'Resigned', status: 'active',
        defaultShift: 'Night', taxStatus: 'S',
        basicSalary: 22000.00, dailyRate: 846.15, hourlyRate: 105.77,
        sssNumber: '34-1111111-1', philHealthNumber: '12-111111111-1',
        pagIbigNumber: '1111-1111-1111', tinNumber: '111-111-111-000',
        sickLeaveBalance: 0, vacationLeaveBalance: 0,
        regularizationDate: null
    },
    {
        id: 'EMP-0008', username: 'e.villanueva', fullName: 'Elena Villanueva',
        firstName: 'Elena', middleName: 'Ramos', lastName: 'Villanueva',
        email: 'elena.villanueva@fastservices.com', phoneNumber: '09671231111',
        gender: 'Female', dateOfBirth: '1965-04-20', civilStatus: 'Married',
        department: 'Finance', position: 'Finance Manager',
        branch: 'Meycauayan Main', hireDate: '2010-01-05',
        employmentStatus: 'Retired', status: 'active',
        defaultShift: 'Day', taxStatus: 'ME3',
        basicSalary: 65000.00, dailyRate: 2500.00, hourlyRate: 312.50,
        sssNumber: '34-0000000-0', philHealthNumber: '12-000000000-0',
        pagIbigNumber: '0000-0000-0000', tinNumber: '000-000-000-000',
        sickLeaveBalance: 0, vacationLeaveBalance: 0,
        regularizationDate: null
    },
    {
        id: 'EMP-0009', username: 'f.aquino', fullName: 'Fernando Aquino',
        firstName: 'Fernando', middleName: '', lastName: 'Aquino',
        email: 'fernando.aquino@fastservices.com', phoneNumber: '09781238888',
        gender: 'Male', dateOfBirth: '1992-08-30', civilStatus: 'Married',
        department: 'IT', position: 'System Administrator',
        branch: 'Meycauayan Main', hireDate: '2021-11-10',
        employmentStatus: 'Suspended', status: 'active',
        defaultShift: 'Day', taxStatus: 'ME',
        basicSalary: 38000.00, dailyRate: 1461.54, hourlyRate: 182.69,
        sssNumber: '34-8888888-8', philHealthNumber: '12-888888888-8',
        pagIbigNumber: '8888-8888-8888', tinNumber: '888-888-888-000',
        sickLeaveBalance: 7, vacationLeaveBalance: 8,
        regularizationDate: null
    },
    {
        id: 'EMP-0010', username: 'g.bautista', fullName: 'Gloria Bautista',
        firstName: 'Gloria', middleName: 'dela Rosa', lastName: 'Bautista',
        email: 'gloria.bautista@fastservices.com', phoneNumber: '09891237777',
        gender: 'Female', dateOfBirth: '1997-01-17', civilStatus: 'Single',
        department: 'Accounting', position: 'Payroll Officer',
        branch: 'Meycauayan Main', hireDate: '2023-05-05',
        employmentStatus: 'Regular', status: 'inactive',
        defaultShift: 'Day', taxStatus: 'S',
        basicSalary: 30000.00, dailyRate: 1153.85, hourlyRate: 144.23,
        sssNumber: '34-7777777-7', philHealthNumber: '12-777777777-7',
        pagIbigNumber: '7777-7777-7777', tinNumber: '777-777-777-000',
        sickLeaveBalance: 5, vacationLeaveBalance: 5,
        regularizationDate: null
    },
];

const departments = [
    'Accounting', 'Finance', 'Human Resources', 'HR', 'IT',
    'Logistics', 'Operations', 'Security'
];

// ── HELPERS ──────────────────────────────────────────────────
function formatName(emp) {
    const mid = emp.middleName ? ` ${emp.middleName}` : '';
    return `${emp.lastName}, ${emp.firstName}${mid}`;
}

function formatCurrency(val) {
    return '₱' + parseFloat(val || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: '2-digit' });
}

function getDaysUntilRegularization(emp) {
    if (emp.employmentStatus !== 'Probationary' || !emp.regularizationDate) return null;
    const today = new Date();
    const regDate = new Date(emp.regularizationDate);
    const diff = Math.ceil((regDate - today) / (1000 * 60 * 60 * 24));
    return diff <= 30 ? diff : null;
}

function generateId() {
    const max = employees.reduce((acc, e) => {
        const num = parseInt(e.id.replace('EMP-', '')) || 0;
        return Math.max(acc, num);
    }, 0);
    return 'EMP-' + String(max + 1).padStart(4, '0');
}

// ── STATUS BADGE ──────────────────────────────────────────────
function statusBadgeClass(status) {
    const map = {
        'Regular':      'bg-success-subtle text-success',
        'Probationary': 'bg-warning-subtle text-warning',
        'Resigned':     'bg-secondary-subtle text-secondary',
        'Retired':      'bg-secondary-subtle text-secondary',
        'Suspended':    'bg-danger-subtle text-danger',
    };
    return map[status] || 'bg-secondary-subtle text-secondary';
}

// ── FILTERING ────────────────────────────────────────────────
function getFilteredEmployees() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const dept   = document.getElementById('filterDepartment').value;
    const status = document.getElementById('filterStatus').value;

    return employees.filter(emp => {
        const name = formatName(emp).toLowerCase();
        const matchSearch = name.includes(search) || emp.id.toLowerCase().includes(search)
                         || (emp.email || '').toLowerCase().includes(search);
        const matchDept = dept === 'all' || emp.department === dept;

        let matchStatus = false;
        if (status === 'deactivated') {
            matchStatus = emp.status === 'inactive';
        } else if (status === 'ready-regular') {
            const d = getDaysUntilRegularization(emp);
            matchStatus = d !== null && d <= 0 && emp.status === 'active';
        } else if (status === 'Suspended') {
            matchStatus = emp.employmentStatus === 'Suspended' && emp.status === 'active';
        } else {
            matchStatus = emp.employmentStatus === status && emp.status === 'active';
        }

        return matchSearch && matchDept && matchStatus;
    });
}

// ── POPULATE DEPARTMENT DROPDOWNS ────────────────────────────
function populateDepartmentSelects() {
    ['filterDepartment', 'editDepartment', 'addDepartment'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;

        if (id === 'filterDepartment') {
            el.innerHTML = '<option value="all">All Departments</option>';
        } else {
            el.innerHTML = '<option value="">— Select Department —</option>';
        }

        departments.sort().forEach(dept => {
            const opt = document.createElement('option');
            opt.value = opt.textContent = dept;
            el.appendChild(opt);
        });
    });
}

// ── RENDER TABLE ─────────────────────────────────────────────
function renderTable() {
    const list = getFilteredEmployees();
    const tbody = document.getElementById('employeeTableBody');
    document.getElementById('recordCount').textContent = list.length;

    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No records found.</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(emp => {
        const daysLeft = getDaysUntilRegularization(emp);
        const isDeactivated = emp.status === 'inactive';

        let extraBadges = '';
        if (isDeactivated) {
            extraBadges += `<span class="badge bg-danger-subtle text-danger ms-1">Deactivated</span>`;
        }
        if (emp.employmentStatus === 'Suspended') {
            extraBadges += `<span class="badge bg-danger-subtle text-danger ms-1">Suspended</span>`;
        }
        if (daysLeft !== null) {
            if (daysLeft <= 0) {
                extraBadges += `<span class="badge bg-success-subtle text-success ms-1">→ REGULAR</span>`;
            } else {
                extraBadges += `<span class="badge bg-warning-subtle text-warning ms-1">${daysLeft}d left</span>`;
            }
        }

        const activeBtnClass = isDeactivated
            ? 'btn-outline-primary'
            : 'btn-outline-secondary';
        const activeBtnIcon = isDeactivated
            ? '<i class="bi bi-person-check"></i>'
            : '<i class="bi bi-person-x"></i>';
        const activeBtnTitle = isDeactivated ? 'Activate Account' : 'Deactivate Account';

        return `
        <tr>
            <td class="ps-3"><span class="fw-semibold">${emp.id}</span></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:32px;height:32px;flex-shrink:0;">
                        <i class="bi bi-person text-secondary"></i>
                    </div>
                    <span>${formatName(emp)}</span>
                </div>
            </td>
            <td>${emp.position}</td>
            <td class="text-muted">${emp.department}</td>
            <td>
                <span class="badge ${statusBadgeClass(emp.employmentStatus)}">${emp.employmentStatus}</span>
                ${extraBadges}
            </td>
            <td class="text-muted">${formatDate(emp.hireDate)}</td>
            <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                    <button class="btn btn-sm btn-outline-secondary" title="View Details"
                            onclick="openViewModal('${emp.id}')">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary" title="Edit Record"
                            onclick="openEditModal('${emp.id}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm ${activeBtnClass}" title="${activeBtnTitle}"
                            onclick="toggleAccountStatus('${emp.id}')">
                        ${activeBtnIcon}
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ── RENDER STATS ─────────────────────────────────────────────
function renderStats() {
    const active = employees.filter(e => e.employmentStatus !== 'Resigned' && e.employmentStatus !== 'Retired');
    document.getElementById('statTotal').textContent        = active.length;
    document.getElementById('statRegular').textContent      = active.filter(e => e.employmentStatus === 'Regular').length;
    document.getElementById('statProbationary').textContent = active.filter(e => e.employmentStatus === 'Probationary').length;
    document.getElementById('statReady').textContent        = active.filter(e => {
        const d = getDaysUntilRegularization(e);
        return d !== null && d <= 0;
    }).length;
}

// ── VIEW MODAL ───────────────────────────────────────────────
function openViewModal(id) {
    const emp = employees.find(e => e.id === id);
    if (!emp) return;

    document.getElementById('viewModalBody').innerHTML = `
        <div class="row g-3">
            <div class="col-12">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;">
                        <i class="bi bi-person fs-3 text-secondary"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">${formatName(emp)}</h5>
                        <small class="text-muted">${emp.id} &middot; ${emp.position}</small>
                    </div>
                    <span class="badge ${statusBadgeClass(emp.employmentStatus)} ms-auto">${emp.employmentStatus}</span>
                </div>
                <hr>
            </div>
            <div class="col-md-6">
                <p class="small text-muted mb-1">Email</p>
                <p class="mb-3">${emp.email || 'N/A'}</p>
                <p class="small text-muted mb-1">Phone</p>
                <p class="mb-3">${emp.phoneNumber || 'N/A'}</p>
                <p class="small text-muted mb-1">Department</p>
                <p class="mb-3">${emp.department}</p>
                <p class="small text-muted mb-1">Shift</p>
                <p class="mb-0">${emp.defaultShift}</p>
            </div>
            <div class="col-md-6">
                <p class="small text-muted mb-1">Basic Salary</p>
                <p class="mb-3">${formatCurrency(emp.basicSalary)}</p>
                <p class="small text-muted mb-1">Daily Rate</p>
                <p class="mb-3">${formatCurrency(emp.dailyRate)}</p>
                <p class="small text-muted mb-1">Hire Date</p>
                <p class="mb-3">${formatDate(emp.hireDate)}</p>
                <p class="small text-muted mb-1">Tax Status</p>
                <p class="mb-0">${emp.taxStatus}</p>
            </div>
            <div class="col-12"><hr class="my-1"></div>
            <div class="col-md-3">
                <p class="small text-muted mb-1">SSS</p>
                <p class="mb-0">${emp.sssNumber || 'N/A'}</p>
            </div>
            <div class="col-md-3">
                <p class="small text-muted mb-1">PhilHealth</p>
                <p class="mb-0">${emp.philHealthNumber || 'N/A'}</p>
            </div>
            <div class="col-md-3">
                <p class="small text-muted mb-1">Pag-IBIG</p>
                <p class="mb-0">${emp.pagIbigNumber || 'N/A'}</p>
            </div>
            <div class="col-md-3">
                <p class="small text-muted mb-1">TIN</p>
                <p class="mb-0">${emp.tinNumber || 'N/A'}</p>
            </div>
        </div>`;

    document.getElementById('viewToEditBtn').onclick = () => {
        bootstrap.Modal.getInstance(document.getElementById('viewEmployeeModal')).hide();
        openEditModal(id);
    };

    new bootstrap.Modal(document.getElementById('viewEmployeeModal')).show();
}

// ── EDIT MODAL ───────────────────────────────────────────────
function openEditModal(id) {
    const emp = employees.find(e => e.id === id);
    if (!emp) return;

    document.getElementById('editEmployeeId').value     = emp.id;
    document.getElementById('editFirstName').value      = emp.firstName || '';
    document.getElementById('editMiddleName').value     = emp.middleName || '';
    document.getElementById('editLastName').value       = emp.lastName || '';
    document.getElementById('editEmail').value          = emp.email || '';
    document.getElementById('editPhone').value          = emp.phoneNumber || '';
    document.getElementById('editGender').value         = emp.gender || 'Male';
    document.getElementById('editDateOfBirth').value    = emp.dateOfBirth || '';
    document.getElementById('editCivilStatus').value    = emp.civilStatus || 'Single';
    document.getElementById('editDepartment').value     = emp.department || '';
    document.getElementById('editPosition').value       = emp.position || '';
    document.getElementById('editEmploymentStatus').value = emp.employmentStatus || 'Probationary';
    document.getElementById('editHireDate').value       = emp.hireDate || '';
    document.getElementById('editDefaultShift').value   = emp.defaultShift || 'Day';
    document.getElementById('editBranch').value         = emp.branch || '';
    document.getElementById('editBasicSalary').value    = emp.basicSalary || 0;
    document.getElementById('editDailyRate').value      = emp.dailyRate || 0;
    document.getElementById('editHourlyRate').value     = emp.hourlyRate || 0;
    document.getElementById('editTaxStatus').value      = emp.taxStatus || 'S';
    document.getElementById('editSssNumber').value      = emp.sssNumber || '';
    document.getElementById('editPhilHealthNumber').value = emp.philHealthNumber || '';
    document.getElementById('editPagibigNumber').value  = emp.pagIbigNumber || '';
    document.getElementById('editTinNumber').value      = emp.tinNumber || '';

    // Reset to first tab
    document.querySelector('#editTabs .nav-link.active').classList.remove('active');
    document.querySelector('#editTabs .nav-link').classList.add('active');
    document.querySelector('.tab-pane.show.active').classList.remove('show', 'active');
    document.getElementById('editPersonal').classList.add('show', 'active');

    new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
}

document.getElementById('saveEditBtn').addEventListener('click', () => {
    const id  = document.getElementById('editEmployeeId').value;
    const idx = employees.findIndex(e => e.id === id);
    if (idx === -1) return;

    employees[idx] = {
        ...employees[idx],
        firstName:        document.getElementById('editFirstName').value,
        middleName:       document.getElementById('editMiddleName').value,
        lastName:         document.getElementById('editLastName').value,
        fullName:         `${document.getElementById('editFirstName').value} ${document.getElementById('editLastName').value}`,
        email:            document.getElementById('editEmail').value,
        phoneNumber:      document.getElementById('editPhone').value,
        gender:           document.getElementById('editGender').value,
        dateOfBirth:      document.getElementById('editDateOfBirth').value,
        civilStatus:      document.getElementById('editCivilStatus').value,
        department:       document.getElementById('editDepartment').value,
        position:         document.getElementById('editPosition').value,
        employmentStatus: document.getElementById('editEmploymentStatus').value,
        hireDate:         document.getElementById('editHireDate').value,
        defaultShift:     document.getElementById('editDefaultShift').value,
        branch:           document.getElementById('editBranch').value,
        basicSalary:      parseFloat(document.getElementById('editBasicSalary').value) || 0,
        dailyRate:        parseFloat(document.getElementById('editDailyRate').value) || 0,
        hourlyRate:       parseFloat(document.getElementById('editHourlyRate').value) || 0,
        taxStatus:        document.getElementById('editTaxStatus').value,
        sssNumber:        document.getElementById('editSssNumber').value,
        philHealthNumber: document.getElementById('editPhilHealthNumber').value,
        pagIbigNumber:    document.getElementById('editPagibigNumber').value,
        tinNumber:        document.getElementById('editTinNumber').value,
    };

    bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal')).hide();

    Swal.fire({ icon: 'success', title: 'Saved!', text: 'Employee record has been updated.', timer: 1800, showConfirmButton: false });

    renderStats();
    renderTable();
});

// ── ADD EMPLOYEE ─────────────────────────────────────────────
document.getElementById('saveAddBtn').addEventListener('click', () => {
    const firstName = document.getElementById('addFirstName').value.trim();
    const lastName  = document.getElementById('addLastName').value.trim();
    const email     = document.getElementById('addEmail').value.trim();
    const dept      = document.getElementById('addDepartment').value;
    const position  = document.getElementById('addPosition').value.trim();
    const hireDate  = document.getElementById('addHireDate').value;

    if (!firstName || !lastName || !email || !dept || !position || !hireDate) {
        Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please fill in all required fields.' });
        return;
    }

    const salary     = parseFloat(document.getElementById('addBasicSalary').value) || 0;
    const dailyRate  = salary > 0 ? +(salary / 26).toFixed(2) : 0;
    const hourlyRate = dailyRate > 0 ? +(dailyRate / 8).toFixed(2) : 0;

    // Compute regularization date: hire date + 6 months
    const regDate = new Date(hireDate);
    regDate.setMonth(regDate.getMonth() + 6);

    const newEmp = {
        id: generateId(),
        username: `${firstName.toLowerCase().replace(/\s/g,'')}.${lastName.toLowerCase()}`,
        fullName: `${firstName} ${lastName}`,
        firstName, middleName: '', lastName,
        email, phoneNumber: '', gender: 'Male',
        dateOfBirth: '', civilStatus: 'Single',
        department: dept, position,
        branch: 'Meycauayan Main', hireDate,
        employmentStatus: document.getElementById('addEmploymentStatus').value,
        status: 'active',
        defaultShift: document.getElementById('addDefaultShift').value,
        taxStatus: 'S',
        basicSalary: salary, dailyRate, hourlyRate,
        sssNumber: '', philHealthNumber: '', pagIbigNumber: '', tinNumber: '',
        sickLeaveBalance: 5, vacationLeaveBalance: 5,
        regularizationDate: regDate.toISOString().split('T')[0],
    };

    employees.push(newEmp);
    document.getElementById('addEmployeeForm').reset();
    bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal')).hide();

    Swal.fire({ icon: 'success', title: 'Added!', text: `${formatName(newEmp)} has been added.`, timer: 1800, showConfirmButton: false });

    renderStats();
    renderTable();
});

// ── TOGGLE ACCOUNT STATUS ─────────────────────────────────────
function toggleAccountStatus(id) {
    const emp = employees.find(e => e.id === id);
    if (!emp) return;

    const isActive  = emp.status === 'active';
    const action    = isActive ? 'deactivate' : 'activate';
    const name      = formatName(emp);

    Swal.fire({
        title: `${isActive ? 'Deactivate' : 'Activate'} Account?`,
        text: `This will ${action} ${name}'s system access.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${action}`,
        confirmButtonColor: isActive ? '#6c757d' : '#0d6efd',
    }).then(result => {
        if (!result.isConfirmed) return;
        emp.status = isActive ? 'inactive' : 'active';
        Swal.fire({ icon: 'success', title: 'Done!', text: `Account has been ${action}d.`, timer: 1500, showConfirmButton: false });
        renderStats();
        renderTable();
    });
}

// ── EXPORT (STUB) ────────────────────────────────────────────
document.getElementById('btnExport').addEventListener('click', () => {
    Swal.fire({ icon: 'success', title: 'Exported!', text: 'Employee master list has been downloaded.', timer: 1800, showConfirmButton: false });
});

// ── FILTER LISTENERS ─────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', renderTable);
document.getElementById('filterDepartment').addEventListener('change', renderTable);
document.getElementById('filterStatus').addEventListener('change', () => {
    document.getElementById('filterDepartment').value = 'all';
    renderTable();
});

// ── INIT ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    populateDepartmentSelects();
    renderStats();
    renderTable();
});
</script>
@endpush