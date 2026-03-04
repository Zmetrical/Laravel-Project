@extends('layouts.main')

@section('title', 'Loan Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Loan Management</li>
    </ol>
@endsection

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">Loan Management</h4>
        <small class="text-muted">Manage SSS and PAG-IBIG loan deductions</small>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="bi bi-plus-circle me-1"></i> Add Loan
    </button>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-lg">
        <div class="card card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Active Loans</div>
                    <div class="fs-4 fw-bold" id="statActive">0</div>
                </div>
                <i class="bi bi-graph-up fs-1 text-secondary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Completed</div>
                    <div class="fs-4 fw-bold" id="statCompleted">0</div>
                </div>
                <i class="bi bi-check-circle fs-1 text-secondary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">SSS Loans</div>
                    <div class="fs-4 fw-bold" id="statSSS">0</div>
                </div>
                <i class="bi bi-credit-card fs-1 text-secondary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">PAG-IBIG Loans</div>
                    <div class="fs-4 fw-bold" id="statPagibig">0</div>
                </div>
                <i class="bi bi-credit-card-2-front fs-1 text-secondary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Total Balance</div>
                    <div class="fs-5 fw-bold" id="statBalance">0.00</div>
                </div>
                <i class="bi bi-cash-stack fs-1 text-secondary opacity-25"></i>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-center">
            <div class="col-md">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control border-start-0"
                           placeholder="Search by name, loan ID, or type..." oninput="renderTable()">
                </div>
            </div>
            <div class="col-md-auto">
                <select id="typeFilter" class="form-select" onchange="renderTable()">
                    <option value="all">All Types</option>
                    <option value="sss">SSS Loan</option>
                    <option value="pagibig">PAG-IBIG Loan</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="card">
    <div class="card-header pb-0 border-bottom-0">
        <ul class="nav nav-tabs card-header-tabs" id="loanTabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" onclick="setTab('active', this); return false;">
                    Active <span class="badge bg-secondary ms-1" id="tabActiveCount">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="setTab('completed', this); return false;">
                    Completed <span class="badge bg-secondary ms-1" id="tabCompletedCount">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="setTab('all', this); return false;">
                    All <span class="badge bg-secondary ms-1" id="tabAllCount">0</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Loan ID</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th class="text-end">Loan Amount</th>
                        <th class="text-end">Monthly</th>
                        <th style="min-width:150px">Progress</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="loanTableBody">
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-credit-card fs-1 d-block mb-2 opacity-25"></i>
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" id="addEmpSearch" class="form-control"
                               placeholder="Search by name, ID, or department..." autocomplete="off"
                               oninput="filterEmployeeDropdown('add')">
                        <div id="addEmpDropdown"
                             class="list-group position-absolute w-100 shadow z-3 d-none"
                             style="max-height:200px;overflow-y:auto;top:100%;"></div>
                    </div>
                    <input type="hidden" id="addEmpId">
                    <div id="addEmpSelected" class="mt-2 d-none">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 py-2 px-3">
                            <i class="bi bi-check-circle me-1"></i>
                            <span id="addEmpSelectedName"></span>
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Loan Type <span class="text-danger">*</span></label>
                    <select id="addLoanType" class="form-select">
                        <option value="">Select loan type</option>
                        <option value="sss">SSS Loan</option>
                        <option value="pagibig">PAG-IBIG Loan</option>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Total Loan Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8369;</span>
                            <input type="number" id="addAmount" class="form-control"
                                   placeholder="24000" min="0" step="100" oninput="autoCalcAmortization()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Monthly Amortization <span class="text-danger">*</span>
                            <small class="text-muted fw-normal">(auto-calculated)</small>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">&#8369;</span>
                            <input type="number" id="addAmortization" class="form-control bg-light"
                                   placeholder="1000.00" min="0" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" id="addStartDate" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Number of Payments <span class="text-danger">*</span></label>
                        <select id="addTerm" class="form-select" onchange="autoCalcAmortization()">
                            <option value="12">12 months (1 year)</option>
                            <option value="18">18 months (1.5 years)</option>
                            <option value="24" selected>24 months (2 years)</option>
                            <option value="36">36 months (3 years)</option>
                            <option value="48">48 months (4 years)</option>
                        </select>
                    </div>
                </div>
                <div id="addSummary" class="alert alert-secondary d-none">
                    <strong><i class="bi bi-info-circle me-1"></i> Loan Summary Preview</strong>
                    <div class="row mt-2 g-2 small">
                        <div class="col-6">
                            <span class="text-muted">Total Amount:</span>
                            <span id="sumAmount" class="fw-semibold ms-1"></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Monthly Payment:</span>
                            <span id="sumMonthly" class="fw-semibold ms-1"></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Term:</span>
                            <span id="sumTerm" class="fw-semibold ms-1"></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Total to Pay:</span>
                            <span id="sumTotal" class="fw-semibold ms-1"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="submitAddLoan()">
                    <i class="bi bi-plus-circle me-1"></i> Add Loan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editLoanId">
                <div class="alert alert-secondary py-2 small mb-3">
                    <strong id="editEmpName"></strong> &mdash;
                    <span id="editLoanTypeName" class="text-muted"></span>
                    &nbsp;|&nbsp; Payments made: <span id="editPaymentsMade" class="fw-semibold"></span>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Total Loan Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8369;</span>
                            <input type="number" id="editAmount" class="form-control"
                                   min="0" step="100" oninput="updateEditSummary()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Monthly Amortization <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8369;</span>
                            <input type="number" id="editAmortization" class="form-control"
                                   min="0" step="50" oninput="updateEditSummary()">
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" id="editStartDate" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Number of Payments <span class="text-danger">*</span></label>
                        <select id="editTerm" class="form-select">
                            <option value="12">12 months</option>
                            <option value="18">18 months</option>
                            <option value="24">24 months</option>
                            <option value="36">36 months</option>
                            <option value="48">48 months</option>
                        </select>
                    </div>
                </div>
                <div id="editSummary" class="alert alert-secondary d-none">
                    <strong><i class="bi bi-calculator me-1"></i> Updated Balance Calculation</strong>
                    <div class="row mt-2 g-2 small">
                        <div class="col-4">
                            <span class="text-muted">Payments Made:</span>
                            <span id="editSumPaid" class="fw-semibold ms-1"></span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted">Total Paid:</span>
                            <span id="editSumTotalPaid" class="fw-semibold ms-1"></span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted">New Balance:</span>
                            <span id="editSumBalance" class="fw-semibold ms-1"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="submitEditLoan()">
                    <i class="bi bi-pencil me-1"></i> Update Loan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- VIEW MODAL --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Loan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">This action cannot be undone.</p>
                <div class="border rounded p-2 small" id="deleteInfo"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script>
/* ================================================
   SAMPLE DATA — replace with Blade/API data later
   ================================================ */
const EMPLOYEES = [
    { id: 'EMP001', fullName: 'Maria Santos',    department: 'Operations', role: 'employee'   },
    { id: 'EMP002', fullName: 'Juan dela Cruz',  department: 'HR',         role: 'hr'         },
    { id: 'EMP003', fullName: 'Ana Reyes',       department: 'Finance',    role: 'accounting' },
    { id: 'EMP004', fullName: 'Mark Villanueva', department: 'IT',         role: 'employee'   },
    { id: 'EMP005', fullName: 'Liza Mendoza',    department: 'Operations', role: 'employee'   },
    { id: 'EMP006', fullName: 'Carlo Bautista',  department: 'Management', role: 'admin'      },
    { id: 'EMP007', fullName: 'Rose Aquino',     department: 'Sales',      role: 'employee'   },
    { id: 'EMP008', fullName: 'Jose Ramos',      department: 'Finance',    role: 'employee'   },
];

let loans = [
    {
        id: 'LN-2024-001', employeeId: 'EMP001', employeeName: 'Maria Santos',
        loanTypeId: 'sss', loanTypeName: 'SSS Loan',
        amount: 24000, monthlyAmortization: 1000, term: 24,
        startDate: '2024-01-15', paymentsMade: 14, remainingBalance: 10000,
        status: 'active', createdDate: '2024-01-10', createdBy: 'Admin'
    },
    {
        id: 'LN-2024-002', employeeId: 'EMP002', employeeName: 'Juan dela Cruz',
        loanTypeId: 'pagibig', loanTypeName: 'PAG-IBIG Loan',
        amount: 60000, monthlyAmortization: 1250, term: 48,
        startDate: '2023-06-01', paymentsMade: 20, remainingBalance: 35000,
        status: 'active', createdDate: '2023-05-28', createdBy: 'Admin'
    },
    {
        id: 'LN-2023-005', employeeId: 'EMP003', employeeName: 'Ana Reyes',
        loanTypeId: 'sss', loanTypeName: 'SSS Loan',
        amount: 18000, monthlyAmortization: 1500, term: 12,
        startDate: '2023-01-01', paymentsMade: 12, remainingBalance: 0,
        status: 'completed', createdDate: '2022-12-20', createdBy: 'Admin',
        completedDate: '2023-12-15'
    },
    {
        id: 'LN-2024-003', employeeId: 'EMP004', employeeName: 'Mark Villanueva',
        loanTypeId: 'pagibig', loanTypeName: 'PAG-IBIG Loan',
        amount: 30000, monthlyAmortization: 833.33, term: 36,
        startDate: '2024-03-01', paymentsMade: 8, remainingBalance: 23333.36,
        status: 'active', createdDate: '2024-02-25', createdBy: 'Admin'
    },
    {
        id: 'LN-2024-004', employeeId: 'EMP005', employeeName: 'Liza Mendoza',
        loanTypeId: 'sss', loanTypeName: 'SSS Loan',
        amount: 12000, monthlyAmortization: 500, term: 24,
        startDate: '2024-02-01', paymentsMade: 24, remainingBalance: 0,
        status: 'completed', createdDate: '2024-01-28', createdBy: 'Admin',
        completedDate: '2026-01-31'
    },
    {
        id: 'LN-2025-001', employeeId: 'EMP007', employeeName: 'Rose Aquino',
        loanTypeId: 'sss', loanTypeName: 'SSS Loan',
        amount: 20000, monthlyAmortization: 833.33, term: 24,
        startDate: '2025-01-01', paymentsMade: 2, remainingBalance: 18333.34,
        status: 'active', createdDate: '2024-12-20', createdBy: 'Admin'
    },
];

/* ================================================
   STATE
   ================================================ */
var currentTab   = 'active';
var deleteLoanId = null;

/* ================================================
   HELPERS
   ================================================ */
function peso(n) {
    return '\u20b1' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function generateLoanId() {
    var year = new Date().getFullYear();
    var seq  = String(loans.length + 1).padStart(3, '0');
    return 'LN-' + year + '-' + seq;
}

function loanTypeBadge(id, name) {
    return '<span class="badge ' + (id === 'sss' ? 'bg-primary' : 'bg-secondary') + ' bg-opacity-75">' + name + '</span>';
}

function statusBadge(status) {
    return status === 'active'
        ? '<span class="badge bg-primary bg-opacity-10 border border-primary border-opacity-25 text-primary">Active</span>'
        : '<span class="badge bg-secondary bg-opacity-10 border text-secondary">Completed</span>';
}

function progressBar(paymentsMade, term, loanTypeId) {
    var pct = term > 0 ? Math.min(100, Math.round((paymentsMade / term) * 100)) : 0;
    var cls = loanTypeId === 'sss' ? 'bg-primary' : 'bg-secondary';
    return '<div class="d-flex flex-column align-items-center gap-1">' +
        '<span class="small fw-semibold">' + paymentsMade + '/' + term + '</span>' +
        '<div class="progress w-100" style="height:6px"><div class="progress-bar ' + cls + '" style="width:' + pct + '%"></div></div>' +
        '<span class="text-muted" style="font-size:.72rem">' + pct + '%</span>' +
        '</div>';
}

/* ================================================
   STATS
   ================================================ */
function updateStats() {
    var active    = loans.filter(function(l) { return l.status === 'active'; });
    var completed = loans.filter(function(l) { return l.status === 'completed'; });

    document.getElementById('statActive').textContent    = active.length;
    document.getElementById('statCompleted').textContent = completed.length;
    document.getElementById('statSSS').textContent       = active.filter(function(l) { return l.loanTypeId === 'sss'; }).length;
    document.getElementById('statPagibig').textContent   = active.filter(function(l) { return l.loanTypeId === 'pagibig'; }).length;
    document.getElementById('statBalance').textContent   =
        peso(active.reduce(function(s, l) { return s + (l.remainingBalance || 0); }, 0));

    document.getElementById('tabActiveCount').textContent    = active.length;
    document.getElementById('tabCompletedCount').textContent = completed.length;
    document.getElementById('tabAllCount').textContent       = loans.length;
}

/* ================================================
   TABLE RENDER
   ================================================ */
function renderTable() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var type   = document.getElementById('typeFilter').value;
    var tbody  = document.getElementById('loanTableBody');

    var data = loans.filter(function(l) {
        if (currentTab === 'active'    && l.status !== 'active')    return false;
        if (currentTab === 'completed' && l.status !== 'completed') return false;
        if (type !== 'all' && l.loanTypeId !== type)                return false;
        if (search && !(
            l.id.toLowerCase().indexOf(search) >= 0            ||
            l.employeeName.toLowerCase().indexOf(search) >= 0  ||
            l.loanTypeName.toLowerCase().indexOf(search) >= 0
        )) return false;
        return true;
    });

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-5">' +
            '<i class="bi bi-credit-card fs-1 d-block mb-2 opacity-25"></i>No loans found</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(function(l) {
        var actions = '<button class="btn btn-sm btn-link p-1 text-secondary" title="View" onclick="openViewModal(\'' + l.id + '\')">' +
            '<i class="bi bi-eye"></i></button>';
        if (l.status === 'active') {
            actions += '<button class="btn btn-sm btn-link p-1 text-secondary" title="Edit" onclick="openEditModal(\'' + l.id + '\')">' +
                '<i class="bi bi-pencil"></i></button>';
            actions += '<button class="btn btn-sm btn-link p-1 text-secondary" title="Delete" onclick="openDeleteModal(\'' + l.id + '\')">' +
                '<i class="bi bi-trash"></i></button>';
        }
        return '<tr>' +
            '<td class="ps-3"><code class="text-primary small">' + l.id + '</code></td>' +
            '<td><div class="fw-semibold">' + l.employeeName + '</div>' +
                '<div class="text-muted" style="font-size:.78rem">' + l.employeeId + '</div></td>' +
            '<td>' + loanTypeBadge(l.loanTypeId, l.loanTypeName) + '</td>' +
            '<td class="text-end fw-semibold">' + peso(l.amount) + '</td>' +
            '<td class="text-end text-muted">' + peso(l.monthlyAmortization) + '</td>' +
            '<td>' + progressBar(l.paymentsMade, l.term, l.loanTypeId) + '</td>' +
            '<td class="text-end fw-bold' + (l.remainingBalance > 0 ? '' : ' text-muted') + '">' + peso(l.remainingBalance) + '</td>' +
            '<td class="text-center">' + statusBadge(l.status) + '</td>' +
            '<td class="text-center pe-3">' + actions + '</td>' +
            '</tr>';
    }).join('');
}

/* ================================================
   TABS
   ================================================ */
function setTab(tab, el) {
    currentTab = tab;
    document.querySelectorAll('#loanTabs .nav-link').forEach(function(a) { a.classList.remove('active'); });
    el.classList.add('active');
    renderTable();
}

/* ================================================
   EMPLOYEE DROPDOWN
   ================================================ */
function filterEmployeeDropdown(prefix) {
    var input    = document.getElementById(prefix + 'EmpSearch');
    var dropdown = document.getElementById(prefix + 'EmpDropdown');
    var val      = input.value.trim().toLowerCase();

    document.getElementById(prefix + 'EmpId').value = '';
    document.getElementById(prefix + 'EmpSelected').classList.add('d-none');

    if (!val) { dropdown.classList.add('d-none'); return; }

    var results = EMPLOYEES.filter(function(e) {
        return e.fullName.toLowerCase().indexOf(val) >= 0   ||
               e.id.toLowerCase().indexOf(val) >= 0          ||
               e.department.toLowerCase().indexOf(val) >= 0;
    });

    dropdown.innerHTML = results.length
        ? results.map(function(e) {
            return '<button type="button" class="list-group-item list-group-item-action py-2 small"' +
                ' onclick="selectEmployee(\'' + prefix + '\',\'' + e.id + '\',\'' + e.fullName + '\')">' +
                '<strong>' + e.fullName + '</strong> ' +
                '<span class="text-muted">(' + e.id + ')</span> ' +
                '<span class="badge bg-secondary bg-opacity-25 text-secondary">' + e.department + '</span>' +
                '</button>';
          }).join('')
        : '<div class="list-group-item text-muted small py-2">No results for "' + input.value + '"</div>';

    dropdown.classList.remove('d-none');
}

function selectEmployee(prefix, id, name) {
    document.getElementById(prefix + 'EmpSearch').value  = name;
    document.getElementById(prefix + 'EmpId').value      = id;
    document.getElementById(prefix + 'EmpDropdown').classList.add('d-none');
    document.getElementById(prefix + 'EmpSelectedName').textContent = name + ' (' + id + ')';
    document.getElementById(prefix + 'EmpSelected').classList.remove('d-none');
}

document.addEventListener('click', function(e) {
    var dd = document.getElementById('addEmpDropdown');
    if (dd && !dd.contains(e.target) && e.target.id !== 'addEmpSearch') {
        dd.classList.add('d-none');
    }
});

/* ================================================
   AUTO AMORTIZATION
   ================================================ */
function autoCalcAmortization() {
    var amount = parseFloat(document.getElementById('addAmount').value) || 0;
    var term   = parseInt(document.getElementById('addTerm').value)     || 0;
    var sumEl  = document.getElementById('addSummary');

    if (amount > 0 && term > 0) {
        var monthly = (amount / term).toFixed(2);
        document.getElementById('addAmortization').value = monthly;
        document.getElementById('sumAmount').textContent  = peso(amount);
        document.getElementById('sumMonthly').textContent = peso(monthly);
        document.getElementById('sumTerm').textContent    = term + ' months';
        document.getElementById('sumTotal').textContent   = peso(monthly * term);
        sumEl.classList.remove('d-none');
    } else {
        sumEl.classList.add('d-none');
    }
}

/* ================================================
   ADD LOAN
   ================================================ */
function openAddModal() {
    ['addEmpSearch','addAmount','addAmortization','addStartDate','addEmpId'].forEach(function(id) {
        document.getElementById(id).value = '';
    });
    document.getElementById('addLoanType').value = '';
    document.getElementById('addTerm').value = '24';
    document.getElementById('addEmpDropdown').classList.add('d-none');
    document.getElementById('addEmpSelected').classList.add('d-none');
    document.getElementById('addSummary').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

function submitAddLoan() {
    var empId  = document.getElementById('addEmpId').value;
    var type   = document.getElementById('addLoanType').value;
    var amount = parseFloat(document.getElementById('addAmount').value);
    var amor   = parseFloat(document.getElementById('addAmortization').value);
    var date   = document.getElementById('addStartDate').value;
    var term   = parseInt(document.getElementById('addTerm').value);
    var emp    = EMPLOYEES.find(function(e) { return e.id === empId; });

    if (!empId || !type || !amount || !amor || !date || !term || !emp) {
        Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please fill in all required fields.', confirmButtonColor: '#6c757d' });
        return;
    }

    loans.unshift({
        id: generateLoanId(), employeeId: emp.id, employeeName: emp.fullName,
        loanTypeId: type, loanTypeName: type === 'sss' ? 'SSS Loan' : 'PAG-IBIG Loan',
        amount: amount, monthlyAmortization: amor, term: term,
        startDate: date, paymentsMade: 0, remainingBalance: amount,
        status: 'active', createdDate: new Date().toISOString().split('T')[0], createdBy: 'Admin'
    });

    bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
    refresh();
    Swal.fire({ icon: 'success', title: 'Loan Added', timer: 2000, showConfirmButton: false });
}

/* ================================================
   EDIT LOAN
   ================================================ */
function openEditModal(loanId) {
    var l = loans.find(function(x) { return x.id === loanId; });
    if (!l) return;

    document.getElementById('editLoanId').value             = l.id;
    document.getElementById('editEmpName').textContent      = l.employeeName;
    document.getElementById('editLoanTypeName').textContent = l.loanTypeName;
    document.getElementById('editPaymentsMade').textContent = l.paymentsMade + '/' + l.term;
    document.getElementById('editAmount').value             = l.amount;
    document.getElementById('editAmortization').value       = l.monthlyAmortization;
    document.getElementById('editStartDate').value          = l.startDate;
    document.getElementById('editTerm').value               = l.term;
    document.getElementById('editSummary').classList.add('d-none');

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function updateEditSummary() {
    var loanId = document.getElementById('editLoanId').value;
    var l      = loans.find(function(x) { return x.id === loanId; });
    var amount = parseFloat(document.getElementById('editAmount').value) || 0;
    var amor   = parseFloat(document.getElementById('editAmortization').value) || 0;
    var sumEl  = document.getElementById('editSummary');

    if (!l || !amount || !amor) { sumEl.classList.add('d-none'); return; }

    var totalPaid = amor * l.paymentsMade;
    var balance   = Math.max(0, amount - totalPaid);

    document.getElementById('editSumPaid').textContent      = l.paymentsMade;
    document.getElementById('editSumTotalPaid').textContent = peso(totalPaid);
    document.getElementById('editSumBalance').textContent   = peso(balance);
    sumEl.classList.remove('d-none');
}

function submitEditLoan() {
    var loanId = document.getElementById('editLoanId').value;
    var amount = parseFloat(document.getElementById('editAmount').value);
    var amor   = parseFloat(document.getElementById('editAmortization').value);
    var date   = document.getElementById('editStartDate').value;
    var term   = parseInt(document.getElementById('editTerm').value);

    if (!amount || !amor || !date || !term) {
        Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please fill in all required fields.', confirmButtonColor: '#6c757d' });
        return;
    }

    var idx = loans.findIndex(function(x) { return x.id === loanId; });
    if (idx === -1) return;

    var l          = loans[idx];
    var newBalance = Math.max(0, amount - (amor * l.paymentsMade));
    loans[idx]     = Object.assign({}, l, { amount: amount, monthlyAmortization: amor, startDate: date, term: term, remainingBalance: newBalance });

    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    refresh();
    Swal.fire({ icon: 'success', title: 'Loan Updated', timer: 2000, showConfirmButton: false });
}

/* ================================================
   VIEW LOAN
   ================================================ */
function openViewModal(loanId) {
    var l = loans.find(function(x) { return x.id === loanId; });
    if (!l) return;

    var pct    = l.term > 0 ? Math.min(100, Math.round((l.paymentsMade / l.term) * 100)) : 0;
    var barCls = l.loanTypeId === 'sss' ? 'bg-primary' : 'bg-secondary';

    var d = new Date(l.startDate);
    d.setMonth(d.getMonth() + l.paymentsMade);
    var nextDate = d.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });

    var nextSection = '';
    if (l.status === 'active' && l.remainingBalance > 0) {
        nextSection = '<div class="alert alert-secondary py-2 small">' +
            '<div class="row g-2">' +
            '<div class="col-md-6"><div class="text-muted">Next Payment Date</div><div class="fw-semibold">' + nextDate + '</div></div>' +
            '<div class="col-md-6"><div class="text-muted">Next Payment Amount</div><div class="fw-bold">' + peso(l.monthlyAmortization) + '</div></div>' +
            '</div></div>';
    }

    var completedNote = l.completedDate
        ? ' &nbsp;|&nbsp; Completed: <strong>' + new Date(l.completedDate).toLocaleDateString('en-PH') + '</strong>'
        : '';

    document.getElementById('viewModalBody').innerHTML =
        '<div class="row g-2 text-center mb-3">' +
            '<div class="col-3"><div class="text-muted small mb-1">Loan ID</div><code class="text-primary small">' + l.id + '</code></div>' +
            '<div class="col-3"><div class="text-muted small mb-1">Status</div>' + statusBadge(l.status) + '</div>' +
            '<div class="col-3"><div class="text-muted small mb-1">Start Date</div><div class="fw-semibold small">' + new Date(l.startDate).toLocaleDateString('en-PH') + '</div></div>' +
            '<div class="col-3"><div class="text-muted small mb-1">Created</div><div class="fw-semibold small">' + new Date(l.createdDate).toLocaleDateString('en-PH') + '</div></div>' +
        '</div>' +
        '<hr class="my-2">' +
        '<div class="row g-3 mb-3">' +
            '<div class="col-md-6"><div class="text-muted small mb-1">Employee</div><div class="fw-bold">' + l.employeeName + '</div><div class="text-muted small">' + l.employeeId + '</div></div>' +
            '<div class="col-md-6"><div class="text-muted small mb-1">Loan Type</div>' + loanTypeBadge(l.loanTypeId, l.loanTypeName) + '</div>' +
        '</div>' +
        '<div class="mb-3">' +
            '<div class="text-muted small mb-1">Payment Progress</div>' +
            '<div class="d-flex align-items-center gap-2">' +
                '<div class="progress flex-grow-1" style="height:10px"><div class="progress-bar ' + barCls + '" style="width:' + pct + '%"></div></div>' +
                '<span class="fw-bold small text-nowrap">' + l.paymentsMade + '/' + l.term + '</span>' +
            '</div>' +
            '<small class="text-muted">' + pct + '% completed</small>' +
        '</div>' +
        '<div class="row g-3 mb-3">' +
            '<div class="col-md-6"><div class="border rounded p-3"><div class="text-muted small">Remaining Balance</div>' +
                '<div class="fs-4 fw-bold' + (l.remainingBalance > 0 ? '' : ' text-muted') + '">' + peso(l.remainingBalance) + '</div></div></div>' +
            '<div class="col-md-6"><div class="border rounded p-3"><div class="text-muted small">Loan Amount</div>' +
                '<div class="fs-5 fw-semibold">' + peso(l.amount) + '</div>' +
                '<div class="text-muted small">Monthly: ' + peso(l.monthlyAmortization) + '</div></div></div>' +
        '</div>' +
        nextSection +
        '<div class="text-muted small">Created by: <strong>' + (l.createdBy || '\u2014') + '</strong>' + completedNote + '</div>';

    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

/* ================================================
   DELETE LOAN
   ================================================ */
function openDeleteModal(loanId) {
    var l = loans.find(function(x) { return x.id === loanId; });
    if (!l) return;
    deleteLoanId = loanId;
    document.getElementById('deleteInfo').innerHTML =
        '<div><strong>Loan ID:</strong> ' + l.id + '</div>' +
        '<div><strong>Employee:</strong> ' + l.employeeName + '</div>' +
        '<div><strong>Type:</strong> ' + l.loanTypeName + '</div>' +
        '<div><strong>Balance:</strong> ' + peso(l.remainingBalance) + '</div>';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function confirmDelete() {
    loans = loans.filter(function(l) { return l.id !== deleteLoanId; });
    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    deleteLoanId = null;
    refresh();
    Swal.fire({ icon: 'success', title: 'Loan Deleted', timer: 1800, showConfirmButton: false });
}

/* ================================================
   INIT
   ================================================ */
function refresh() {
    updateStats();
    renderTable();
}

document.addEventListener('DOMContentLoaded', function() { refresh(); });
</script>
@endpush