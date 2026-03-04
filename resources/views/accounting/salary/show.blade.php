@extends('layouts.main')

@section('title', 'Employee Salary Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Salary Management</li>
    </ol>
@endsection

@section('content')

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-outline card-primary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded p-2">
                        <i class="fas fa-users fa-lg text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Employees</div>
                        <div class="fs-4 fw-semibold" id="stat-total">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-secondary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-secondary bg-opacity-10 rounded p-2">
                        <i class="fas fa-money-bill-wave fa-lg text-secondary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Monthly Payroll</div>
                        <div class="fs-4 fw-semibold" id="stat-payroll">₱0.00</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-primary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded p-2">
                        <i class="fas fa-chart-line fa-lg text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Average Salary</div>
                        <div class="fs-4 fw-semibold" id="stat-avg">₱0.00</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Card --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">Employee Salary Management</h3>
        <button class="btn btn-secondary btn-sm" id="btn-bulk-toggle">
            <i class="fas fa-bolt me-1"></i> Bulk Update
        </button>
    </div>

    {{-- Bulk Panel --}}
    <div class="card-body border-bottom d-none" id="bulk-panel">
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
                <label class="form-label small mb-1">Filter by Position</label>
                <select class="form-select form-select-sm" id="bulk-position">
                    <option value="">-- Select Position --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">New Monthly Salary (₱)</label>
                <input type="number" class="form-control form-control-sm" id="bulk-salary"
                       placeholder="e.g. 25000" min="0">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="btn-bulk-apply">
                    <i class="fas fa-save me-1"></i> Apply
                </button>
                <button class="btn btn-secondary btn-sm" id="btn-bulk-cancel">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
            </div>
        </div>

        <div id="bulk-employee-list" class="d-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted" id="bulk-count-label">0 found</span>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="bulk-select-all">
                    <label class="form-check-label small" for="bulk-select-all">Select All</label>
                </div>
            </div>
            <div class="row g-2" id="bulk-employees"></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card-body border-bottom">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control" id="filter-search"
                           placeholder="Search by name, ID, position…">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" id="filter-department">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" id="filter-position">
                    <option value="">All Positions</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:48px"></th>
                        <th>Employee</th>
                        <th>Department / Position</th>
                        <th>Monthly Salary</th>
                        <th>Daily Rate</th>
                        <th>Hourly Rate</th>
                        <th style="width:100px">Actions</th>
                    </tr>
                </thead>
                <tbody id="salary-tbody">
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            Loading…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer py-2">
        <small class="text-muted" id="table-footer">Showing 0 employees</small>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="edit-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Salary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-employee-id">
                <div class="mb-3">
                    <label class="form-label small text-muted">Employee</label>
                    <input type="text" class="form-control form-control-sm"
                           id="edit-employee-name" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Monthly Basic Salary (₱) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm"
                           id="edit-basic-salary" min="0" placeholder="e.g. 25000">
                </div>
                <div class="row text-muted small" id="edit-derived-rates">
                    <div class="col-6">Daily Rate: <strong id="edit-daily-preview">—</strong></div>
                    <div class="col-6">Hourly Rate: <strong id="edit-hourly-preview">—</strong></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="btn-save-edit">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Details Modal --}}
<div class="modal fade" id="details-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="details-modal-title">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="details-body"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const SalaryManager = (() => {

    /* ─── Constants ─────────────────────────────────────────── */
    const WORKING_DAYS = 26;

    /* ─── Sample Data ───────────────────────────────────────── */
    let employees = [
        { id:'EMP-001', fullName:'Maria Santos',      department:'Operations', position:'Team Leader',     basicSalary:35000, email:'maria.santos@company.ph',    hireDate:'2019-03-15', employmentStatus:'Regular',      branch:'Meycauayan Main', taxStatus:'S',  sssNo:'34-1234567-8', philNo:'03-123456789-0', pagibigNo:'1234-5678-9012', tin:'123-456-789' },
        { id:'EMP-002', fullName:'Jose Reyes',        department:'Finance',    position:'Accountant',      basicSalary:32000, email:'jose.reyes@company.ph',      hireDate:'2020-07-01', employmentStatus:'Regular',      branch:'Meycauayan Main', taxStatus:'ME', sssNo:'34-2345678-9', philNo:'03-234567890-1', pagibigNo:'2345-6789-0123', tin:'234-567-890' },
        { id:'EMP-003', fullName:'Ana Dela Cruz',     department:'HR',         position:'HR Officer',      basicSalary:28000, email:'ana.delacruz@company.ph',    hireDate:'2021-01-10', employmentStatus:'Regular',      branch:'Meycauayan Main', taxStatus:'S',  sssNo:'34-3456789-0', philNo:'03-345678901-2', pagibigNo:'3456-7890-1234', tin:'345-678-901' },
        { id:'EMP-004', fullName:'Carlo Mendoza',     department:'Operations', position:'Supervisor',      basicSalary:38000, email:'carlo.mendoza@company.ph',   hireDate:'2018-11-20', employmentStatus:'Regular',      branch:'Fairview Branch', taxStatus:'ME', sssNo:'34-4567890-1', philNo:'03-456789012-3', pagibigNo:'4567-8901-2345', tin:'456-789-012' },
        { id:'EMP-005', fullName:'Liza Garcia',       department:'IT',         position:'Developer',       basicSalary:40000, email:'liza.garcia@company.ph',     hireDate:'2020-05-12', employmentStatus:'Regular',      branch:'Meycauayan Main', taxStatus:'S',  sssNo:'34-5678901-2', philNo:'03-567890123-4', pagibigNo:'5678-9012-3456', tin:'567-890-123' },
        { id:'EMP-006', fullName:'Ramon Flores',      department:'IT',         position:'Developer',       basicSalary:38000, email:'ramon.flores@company.ph',    hireDate:'2021-08-03', employmentStatus:'Probationary', branch:'Meycauayan Main', taxStatus:'S',  sssNo:'34-6789012-3', philNo:'03-678901234-5', pagibigNo:'6789-0123-4567', tin:'678-901-234' },
        { id:'EMP-007', fullName:'Sheila Ramos',      department:'Finance',    position:'Accountant',      basicSalary:30000, email:'sheila.ramos@company.ph',    hireDate:'2022-02-14', employmentStatus:'Probationary', branch:'Fairview Branch', taxStatus:'S',  sssNo:'34-7890123-4', philNo:'03-789012345-6', pagibigNo:'7890-1234-5678', tin:'789-012-345' },
        { id:'EMP-008', fullName:'Andres Torres',     department:'Operations', position:'Team Leader',     basicSalary:34000, email:'andres.torres@company.ph',   hireDate:'2019-09-25', employmentStatus:'Regular',      branch:'Meycauayan Main', taxStatus:'ME', sssNo:'34-8901234-5', philNo:'03-890123456-7', pagibigNo:'8901-2345-6789', tin:'890-123-456' },
        { id:'EMP-009', fullName:'Patricia Lim',      department:'HR',         position:'HR Manager',      basicSalary:48000, email:'patricia.lim@company.ph',    hireDate:'2017-06-08', employmentStatus:'Regular',      branch:'Meycauayan Main', taxStatus:'ME', sssNo:'34-9012345-6', philNo:'03-901234567-8', pagibigNo:'9012-3456-7890', tin:'901-234-567' },
        { id:'EMP-010', fullName:'Miguel Castro',     department:'Operations', position:'Supervisor',      basicSalary:37000, email:'miguel.castro@company.ph',   hireDate:'2018-04-30', employmentStatus:'Regular',      branch:'Fairview Branch', taxStatus:'S',  sssNo:'34-0123456-7', philNo:'03-012345678-9', pagibigNo:'0123-4567-8901', tin:'012-345-678' },
        { id:'EMP-011', fullName:'Joanna Villanueva', department:'IT',         position:'QA Engineer',     basicSalary:32000, email:'joanna.v@company.ph',        hireDate:'2022-10-17', employmentStatus:'Probationary', branch:'Meycauayan Main', taxStatus:'S',  sssNo:'34-1122334-5', philNo:'03-112233445-6', pagibigNo:'1122-3344-5566', tin:'112-233-445' },
        { id:'EMP-012', fullName:'Roberto Aguilar',   department:'Finance',    position:'Finance Manager', basicSalary:52000, email:'roberto.a@company.ph',       hireDate:'2016-01-05', employmentStatus:'Regular',      branch:'Meycauayan Main', taxStatus:'ME', sssNo:'34-5566778-9', philNo:'03-556677889-0', pagibigNo:'5566-7788-9900', tin:'556-677-889' },
    ];

    /* ─── Rate Helpers ──────────────────────────────────────── */
    const toDaily  = s => s / WORKING_DAYS;
    const toHourly = s => toDaily(s) / 8;
    const peso     = n => '₱' + parseFloat(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    /* ─── Government Contributions (Simplified PH 2024) ────── */
    function govContrib(basicSalary) {
        // SSS lookup (simplified bracket)
        let sssEE = 0, sssER = 0;
        const sssBrackets = [
            [4250,180,380],[6750,270,570],[9250,360,760],[11750,450,950],
            [14250,540,1140],[16750,630,1330],[19250,720,1520],[21750,810,1710],
            [24250,900,1900],[Infinity,990,2090]
        ];
        for (const [cap, ee, er] of sssBrackets) {
            if (basicSalary <= cap) { sssEE = ee; sssER = er; break; }
        }
        // PhilHealth: 5% split, cap at 100k
        const philBase = Math.min(basicSalary, 100000);
        const philEE = (philBase * 0.05) / 2;
        // Pag-IBIG: 2% capped at ₱100
        const pagEE = Math.min(basicSalary * 0.02, 100);
        const pagER = pagEE;
        // Withholding tax (annualized then monthly)
        const annualTaxable = (basicSalary - sssEE - philEE - pagEE) * 12;
        let annualTax = 0;
        if      (annualTaxable <= 250000)  annualTax = 0;
        else if (annualTaxable <= 400000)  annualTax = (annualTaxable - 250000) * 0.20;
        else if (annualTaxable <= 800000)  annualTax = 30000  + (annualTaxable - 400000) * 0.25;
        else if (annualTaxable <= 2000000) annualTax = 130000 + (annualTaxable - 800000) * 0.30;
        else                               annualTax = 490000 + (annualTaxable - 2000000) * 0.35;
        const tax = annualTax / 12;

        return {
            sssEE, sssER,
            philEE: +philEE.toFixed(2), philER: +philEE.toFixed(2),
            pagEE:  +pagEE.toFixed(2),  pagER:  +pagER.toFixed(2),
            tax:    +tax.toFixed(2),
            totalEE: +(sssEE + philEE + pagEE + tax).toFixed(2)
        };
    }

    /* ─── State ─────────────────────────────────────────────── */
    let filtered    = [...employees];
    let bulkChecked = new Set();

    /* ─── DOM ───────────────────────────────────────────────── */
    const $ = id => document.getElementById(id);
    const tbody         = $('salary-tbody');
    const filterSearch  = $('filter-search');
    const filterDept    = $('filter-department');
    const filterPos     = $('filter-position');
    const bulkPanel     = $('bulk-panel');
    const bulkPosSel    = $('bulk-position');
    const bulkSalaryInp = $('bulk-salary');
    const bulkEmpList   = $('bulk-employee-list');
    const bulkEmps      = $('bulk-employees');
    const bulkSelectAll = $('bulk-select-all');
    let   editModal, detailsModal;

    /* ─── Stats ─────────────────────────────────────────────── */
    function updateStats() {
        const total   = employees.length;
        const payroll = employees.reduce((s, e) => s + e.basicSalary, 0);
        $('stat-total').textContent   = total;
        $('stat-payroll').textContent = peso(payroll);
        $('stat-avg').textContent     = peso(total ? payroll / total : 0);
    }

    /* ─── Populate Filter Dropdowns ─────────────────────────── */
    function populateFilters() {
        const depts = [...new Set(employees.map(e => e.department))].sort();
        const poses  = [...new Set(employees.map(e => e.position))].sort();

        depts.forEach(d => {
            filterDept.add(new Option(d, d));
        });
        poses.forEach(p => {
            filterPos.add(new Option(p, p));
            bulkPosSel.add(new Option(p, p));
        });
    }

    /* ─── Render Table ──────────────────────────────────────── */
    function renderTable() {
        if (!filtered.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">
                <i class="fas fa-search me-2"></i>No employees found.</td></tr>`;
            $('table-footer').textContent = 'No results';
            return;
        }

        tbody.innerHTML = filtered.map(emp => {
            const d = toDaily(emp.basicSalary);
            const h = toHourly(emp.basicSalary);
            const initials = emp.fullName.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase();
            return `<tr>
                <td class="text-center">
                    <div class="d-inline-flex align-items-center justify-content-center
                         bg-primary bg-opacity-10 rounded-circle text-primary fw-semibold"
                         style="width:36px;height:36px;font-size:0.75rem">
                        ${initials}
                    </div>
                </td>
                <td>
                    <div class="fw-semibold">${emp.fullName}</div>
                    <div class="text-muted small">${emp.id} &middot; ${emp.email}</div>
                </td>
                <td>
                    <div>${emp.department}</div>
                    <div class="text-muted small">${emp.position}</div>
                </td>
                <td class="fw-semibold">${peso(emp.basicSalary)}</td>
                <td class="text-muted">${peso(d)}</td>
                <td class="text-muted">${peso(h)}</td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 btn-view"
                                data-id="${emp.id}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary py-1 px-2 btn-edit"
                                data-id="${emp.id}" title="Edit Salary">
                            <i class="fas fa-pencil"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        $('table-footer').textContent = `Showing ${filtered.length} of ${employees.length} employee(s)`;
    }

    /* ─── Filter Logic ──────────────────────────────────────── */
    function applyFilters() {
        const q    = filterSearch.value.toLowerCase();
        const dept = filterDept.value;
        const pos  = filterPos.value;

        filtered = employees.filter(e => {
            const matchQ    = !q    || e.fullName.toLowerCase().includes(q) || e.id.toLowerCase().includes(q) || e.position.toLowerCase().includes(q) || e.department.toLowerCase().includes(q);
            const matchDept = !dept || e.department === dept;
            const matchPos  = !pos  || e.position  === pos;
            return matchQ && matchDept && matchPos;
        });

        renderTable();
    }

    /* ─── Edit Modal ────────────────────────────────────────── */
    function openEdit(id) {
        const emp = employees.find(e => e.id === id);
        if (!emp) return;
        $('edit-employee-id').value   = emp.id;
        $('edit-employee-name').value = emp.fullName;
        $('edit-basic-salary').value  = emp.basicSalary;
        refreshEditPreview(emp.basicSalary);
        editModal.show();
    }

    function refreshEditPreview(val) {
        const n = parseFloat(val);
        const ok = !isNaN(n) && n > 0;
        $('edit-daily-preview').textContent  = ok ? peso(toDaily(n))  : '—';
        $('edit-hourly-preview').textContent = ok ? peso(toHourly(n)) : '—';
    }

    function saveEdit() {
        const id  = $('edit-employee-id').value;
        const val = parseFloat($('edit-basic-salary').value);

        if (!val || val <= 0) {
            Swal.fire({ icon:'warning', title:'Invalid Amount', text:'Enter a valid salary.', confirmButtonColor:'#6c757d' });
            return;
        }

        const emp = employees.find(e => e.id === id);
        if (!emp) return;
        emp.basicSalary = val;

        Swal.fire({ icon:'success', title:'Salary Updated',
            text:`${emp.fullName} → ${peso(val)}`,
            confirmButtonColor:'#0d6efd', timer:2000, showConfirmButton:false });

        editModal.hide();
        updateStats();
        applyFilters();
    }

    /* ─── Details Modal ─────────────────────────────────────── */
    function openDetails(id) {
        const emp = employees.find(e => e.id === id);
        if (!emp) return;
        const gc = govContrib(emp.basicSalary);
        $('details-modal-title').textContent = emp.fullName;

        $('details-body').innerHTML = `
        <div class="row g-4">
            <div class="col-md-6">
                <p class="text-muted text-uppercase small fw-semibold mb-2">Employment Information</p>
                <table class="table table-sm table-borderless mb-3">
                    <tr><td class="text-muted" style="width:130px">Employee ID</td><td class="fw-semibold">${emp.id}</td></tr>
                    <tr><td class="text-muted">Department</td><td>${emp.department}</td></tr>
                    <tr><td class="text-muted">Position</td><td>${emp.position}</td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge text-bg-${emp.employmentStatus==='Regular'?'primary':'secondary'} bg-opacity-10">${emp.employmentStatus}</span></td></tr>
                    <tr><td class="text-muted">Hire Date</td><td>${emp.hireDate}</td></tr>
                    <tr><td class="text-muted">Branch</td><td>${emp.branch}</td></tr>
                    <tr><td class="text-muted">Tax Status</td><td>${emp.taxStatus}</td></tr>
                </table>

                <p class="text-muted text-uppercase small fw-semibold mb-2">Government Numbers</p>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" style="width:130px">SSS No.</td><td>${emp.sssNo}</td></tr>
                    <tr><td class="text-muted">PhilHealth No.</td><td>${emp.philNo}</td></tr>
                    <tr><td class="text-muted">Pag-IBIG No.</td><td>${emp.pagibigNo}</td></tr>
                    <tr><td class="text-muted">TIN</td><td>${emp.tin}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <p class="text-muted text-uppercase small fw-semibold mb-2">Salary Breakdown</p>
                <table class="table table-sm table-borderless mb-3">
                    <tr><td class="text-muted" style="width:130px">Monthly Basic</td><td class="fw-semibold">${peso(emp.basicSalary)}</td></tr>
                    <tr><td class="text-muted">Daily Rate</td><td>${peso(toDaily(emp.basicSalary))} <span class="text-muted small">/ ${WORKING_DAYS} days</span></td></tr>
                    <tr><td class="text-muted">Hourly Rate</td><td>${peso(toHourly(emp.basicSalary))}</td></tr>
                    <tr><td class="text-muted">OT Rate (×1.25)</td><td>${peso(toHourly(emp.basicSalary) * 1.25)}</td></tr>
                </table>

                <p class="text-muted text-uppercase small fw-semibold mb-2">Est. Monthly Contributions</p>
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th></th><th class="text-end">EE Share</th><th class="text-end">ER Share</th></tr>
                    </thead>
                    <tbody>
                        <tr><td class="text-muted">SSS</td>         <td class="text-end">${peso(gc.sssEE)}</td><td class="text-end text-muted">${peso(gc.sssER)}</td></tr>
                        <tr><td class="text-muted">PhilHealth</td>  <td class="text-end">${peso(gc.philEE)}</td><td class="text-end text-muted">${peso(gc.philER)}</td></tr>
                        <tr><td class="text-muted">Pag-IBIG</td>    <td class="text-end">${peso(gc.pagEE)}</td><td class="text-end text-muted">${peso(gc.pagER)}</td></tr>
                        <tr><td class="text-muted">Withholding Tax</td><td class="text-end">${peso(gc.tax)}</td><td class="text-end text-muted">—</td></tr>
                        <tr class="fw-semibold table-light">
                            <td>Total EE Deductions</td>
                            <td class="text-end">${peso(gc.totalEE)}</td><td></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Est. Net Pay</td>
                            <td class="text-end fw-semibold">${peso(emp.basicSalary - gc.totalEE)}</td><td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>`;

        detailsModal.show();
    }

    /* ─── Bulk Update ───────────────────────────────────────── */
    function renderBulkList() {
        const pos = bulkPosSel.value;
        if (!pos) { bulkEmpList.classList.add('d-none'); return; }

        const list = employees.filter(e => e.position === pos);
        $('bulk-count-label').textContent = `${list.length} employee(s) in this position`;
        bulkChecked.clear();
        bulkSelectAll.checked = false;

        bulkEmps.innerHTML = list.map(emp => `
            <div class="col-md-6">
                <div class="border rounded p-3">
                    <div class="form-check">
                        <input class="form-check-input bulk-chk" type="checkbox"
                               value="${emp.id}" id="bc-${emp.id}">
                        <label class="form-check-label w-100" for="bc-${emp.id}" style="cursor:pointer">
                            <div class="fw-semibold small">${emp.fullName}</div>
                            <div class="text-muted small">${emp.id} &middot; ${emp.department}</div>
                            <div class="text-muted small mt-1">Current: ${peso(emp.basicSalary)}</div>
                        </label>
                    </div>
                </div>
            </div>`).join('');

        bulkEmpList.classList.remove('d-none');

        bulkEmps.querySelectorAll('.bulk-chk').forEach(chk => {
            chk.addEventListener('change', () => {
                chk.checked ? bulkChecked.add(chk.value) : bulkChecked.delete(chk.value);
            });
        });
    }

    function applyBulk() {
        const newSalary = parseFloat(bulkSalaryInp.value);
        if (!newSalary || newSalary <= 0) {
            Swal.fire({ icon:'warning', title:'Missing Salary', text:'Enter a valid salary amount.', confirmButtonColor:'#6c757d' }); return;
        }
        if (!bulkChecked.size) {
            Swal.fire({ icon:'warning', title:'No Selection', text:'Select at least one employee.', confirmButtonColor:'#6c757d' }); return;
        }

        Swal.fire({
            title:'Confirm Bulk Update',
            html:`Update <strong>${bulkChecked.size}</strong> employee(s) to <strong>${peso(newSalary)}</strong>?`,
            icon:'question', showCancelButton:true,
            confirmButtonColor:'#0d6efd', cancelButtonColor:'#6c757d',
            confirmButtonText:'Yes, Update'
        }).then(r => {
            if (!r.isConfirmed) return;
            bulkChecked.forEach(id => {
                const emp = employees.find(e => e.id === id);
                if (emp) emp.basicSalary = newSalary;
            });
            Swal.fire({ icon:'success', title:'Done!', text:`${bulkChecked.size} employee(s) updated.`, confirmButtonColor:'#0d6efd', timer:2000, showConfirmButton:false });
            closeBulk();
            updateStats();
            applyFilters();
        });
    }

    function closeBulk() {
        bulkPanel.classList.add('d-none');
        bulkPosSel.value    = '';
        bulkSalaryInp.value = '';
        bulkChecked.clear();
        bulkEmpList.classList.add('d-none');
        $('btn-bulk-toggle').innerHTML = '<i class="fas fa-bolt me-1"></i> Bulk Update';
    }

    /* ─── Event Binding ─────────────────────────────────────── */
    function bindEvents() {
        filterSearch.addEventListener('input',  applyFilters);
        filterDept.addEventListener('change',   applyFilters);
        filterPos.addEventListener('change',    applyFilters);
        bulkPosSel.addEventListener('change',   renderBulkList);

        $('btn-bulk-toggle').addEventListener('click', () => {
            const open = bulkPanel.classList.toggle('d-none');
            $('btn-bulk-toggle').innerHTML = open
                ? '<i class="fas fa-bolt me-1"></i> Bulk Update'
                : '<i class="fas fa-times me-1"></i> Close';
        });

        $('btn-bulk-cancel').addEventListener('click', closeBulk);
        $('btn-bulk-apply').addEventListener('click',  applyBulk);

        bulkSelectAll.addEventListener('change', () => {
            bulkEmps.querySelectorAll('.bulk-chk').forEach(chk => {
                chk.checked = bulkSelectAll.checked;
                bulkSelectAll.checked ? bulkChecked.add(chk.value) : bulkChecked.delete(chk.value);
            });
        });

        $('edit-basic-salary').addEventListener('input', e => refreshEditPreview(e.target.value));
        $('btn-save-edit').addEventListener('click', saveEdit);

        tbody.addEventListener('click', e => {
            const edit = e.target.closest('.btn-edit');
            const view = e.target.closest('.btn-view');
            if (edit) openEdit(edit.dataset.id);
            if (view) openDetails(view.dataset.id);
        });
    }

    /* ─── Init ──────────────────────────────────────────────── */
    function init() {
        editModal    = new bootstrap.Modal(document.getElementById('edit-modal'));
        detailsModal = new bootstrap.Modal(document.getElementById('details-modal'));
        populateFilters();
        updateStats();
        applyFilters();
        bindEvents();
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', () => SalaryManager.init());
</script>
@endpush