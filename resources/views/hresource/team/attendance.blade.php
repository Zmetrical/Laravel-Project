@extends('layouts.main')

@section('title', 'Employee Attendance')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Employee Attendance</li>
    </ol>
@endsection

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        .app-header, .app-sidebar { display: none !important; }
        .app-main { margin: 0 !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    }
    .print-only { display: none; }
    .stat-card { cursor: pointer; transition: transform .15s ease; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card.active { outline: 2px solid var(--bs-primary); }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h4 class="mb-0">Employee Attendance</h4>
        <small class="text-muted">Monitor employee attendance and identify issues</small>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="handlePrint()">
        <i class="bi bi-printer me-1"></i> Print Attendance List
    </button>
</div>

{{-- Print Header (only visible on print) --}}
<div class="print-only mb-3">
    <div class="text-center">
        <h5 class="mb-0">FAST SERVICES CORPORATION</h5>
        <p class="mb-0">Employee Attendance Report</p>
        <small id="print-period"></small><br>
        <small id="print-generated"></small>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-3 no-print">
    <div class="col-6 col-md">
        <div class="card stat-card h-100" data-filter="all" onclick="setStatusFilter('all')">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <small class="text-muted">Total Records</small>
                    <i class="bi bi-people text-muted"></i>
                </div>
                <h3 class="mb-0" id="stat-total">0</h3>
                <small class="text-muted">This period</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100" data-filter="present" onclick="setStatusFilter('present')">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <small class="text-muted">Present</small>
                    <i class="bi bi-check-circle text-muted"></i>
                </div>
                <h3 class="mb-0" id="stat-present">0</h3>
                <small class="text-muted">On time</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100" data-filter="late" onclick="setStatusFilter('late')">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <small class="text-muted">Late</small>
                    <i class="bi bi-clock text-muted"></i>
                </div>
                <h3 class="mb-0" id="stat-late">0</h3>
                <small class="text-muted">Delayed</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100" data-filter="absent" onclick="setStatusFilter('absent')">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <small class="text-muted">Absent</small>
                    <i class="bi bi-x-circle text-muted"></i>
                </div>
                <h3 class="mb-0" id="stat-absent">0</h3>
                <small class="text-muted">Missing</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card stat-card h-100" data-filter="issues" onclick="setStatusFilter('issues')">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <small class="text-muted">Issues</small>
                    <i class="bi bi-exclamation-triangle text-muted"></i>
                </div>
                <h3 class="mb-0" id="stat-issues">0</h3>
                <small class="text-muted">Need attention</small>
            </div>
        </div>
    </div>
</div>

{{-- Filters Card --}}
<div class="card mb-3 no-print">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-funnel me-1"></i> Filters</span>
        <button class="btn btn-secondary btn-sm" onclick="resetFilters()">Reset All</button>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label form-label-sm">Month</label>
                <select class="form-select form-select-sm" id="filter-month" onchange="applyFilters()">
                    <option value="01">January</option><option value="02">February</option>
                    <option value="03">March</option><option value="04">April</option>
                    <option value="05">May</option><option value="06">June</option>
                    <option value="07">July</option><option value="08">August</option>
                    <option value="09">September</option><option value="10">October</option>
                    <option value="11">November</option><option value="12">December</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label form-label-sm">Year</label>
                <select class="form-select form-select-sm" id="filter-year" onchange="applyFilters()"></select>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label form-label-sm">Status</label>
                <select class="form-select form-select-sm" id="filter-status" onchange="applyFilters()">
                    <option value="all">All Status</option>
                    <option value="present">Present Only</option>
                    <option value="late">Late Only</option>
                    <option value="absent">Absent Only</option>
                    <option value="incomplete">Incomplete Only</option>
                    <option value="issues">With Issues Only</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label form-label-sm">Department</label>
                <select class="form-select form-select-sm" id="filter-department" onchange="onDepartmentChange()">
                    <option value="all">All Departments</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label form-label-sm">Position</label>
                <select class="form-select form-select-sm" id="filter-position" onchange="onPositionChange()">
                    <option value="all">All Positions</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label form-label-sm">Employee</label>
                <select class="form-select form-select-sm" id="filter-employee" onchange="applyFilters()">
                    <option value="all">All Employees</option>
                </select>
            </div>
        </div>
        <div id="filter-summary" class="mt-3 p-2 bg-body-tertiary border rounded d-none">
            <small>
                <i class="bi bi-funnel-fill me-1 text-primary"></i>
                <span id="filter-summary-text" class="text-muted"></span>
            </small>
        </div>
    </div>
</div>

{{-- Issues Alert --}}
<div id="issues-alert" class="alert alert-secondary d-none mb-3 no-print" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Attendance Issues Detected &mdash; </strong>
    <span id="issues-alert-text"></span>
</div>

{{-- Records Card --}}
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span id="records-title">Employee Attendance</span>
        <small class="text-muted">
            Showing <strong id="records-shown">0</strong> of <strong id="records-total">0</strong> records
        </small>
    </div>
    <div class="card-body p-0">
        <div id="attendance-list"></div>
        <div id="attendance-empty" class="text-center py-5 d-none">
            <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">No Attendance Records</p>
            <small class="text-muted">No records match your current filters</small>
        </div>
    </div>
</div>

{{-- Summary Card --}}
<div id="summary-card" class="card mb-3 d-none no-print">
    <div class="card-header">Attendance Summary</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded text-center">
                    <small class="text-muted d-block mb-1">Attendance Rate</small>
                    <h4 id="sum-attendance-rate" class="mb-0">0%</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded text-center">
                    <small class="text-muted d-block mb-1">On-Time Rate</small>
                    <h4 id="sum-ontime-rate" class="mb-0">0%</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded text-center">
                    <small class="text-muted d-block mb-1">Late Rate</small>
                    <h4 id="sum-late-rate" class="mb-0">0%</h4>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded text-center">
                    <small class="text-muted d-block mb-1">Absence Rate</small>
                    <h4 id="sum-absence-rate" class="mb-0">0%</h4>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>

const sampleEmployees = [
    { id: 'EMP001', name: 'Maria Santos',      department: 'Operations', position: 'Supervisor',      role: 'employee',   status: 'active' },
    { id: 'EMP002', name: 'Jose Reyes',        department: 'Operations', position: 'Staff',           role: 'employee',   status: 'active' },
    { id: 'EMP003', name: 'Ana Cruz',          department: 'HR',         position: 'HR Officer',      role: 'hr',         status: 'active' },
    { id: 'EMP004', name: 'Carlos Dela Cruz',  department: 'Finance',    position: 'Accountant',      role: 'accounting', status: 'active' },
    { id: 'EMP005', name: 'Liza Mendoza',      department: 'Operations', position: 'Staff',           role: 'employee',   status: 'active' },
    { id: 'EMP006', name: 'Roberto Bautista',  department: 'IT',         position: 'IT Technician',   role: 'employee',   status: 'active' },
    { id: 'EMP007', name: 'Jenny Garcia',      department: 'Finance',    position: 'Finance Officer', role: 'accounting', status: 'active' },
    { id: 'EMP008', name: 'Miguel Torres',     department: 'IT',         position: 'Developer',       role: 'employee',   status: 'active' },
];

const sampleAttendanceRecords = [
    { id: 'ATT001', employeeId: 'EMP001', employeeName: 'Maria Santos',     date: '2025-03-03', timeIn: '08:02', timeOut: '17:05', hoursWorked: 8.97, status: 'present',    shift: 'Day',   isAdditionalShift: false, issues: [] },
    { id: 'ATT002', employeeId: 'EMP002', employeeName: 'Jose Reyes',       date: '2025-03-03', timeIn: '08:45', timeOut: '17:10', hoursWorked: 8.40, status: 'late',       shift: 'Day',   isAdditionalShift: false, issues: ['Arrived 45 minutes late'] },
    { id: 'ATT003', employeeId: 'EMP003', employeeName: 'Ana Cruz',         date: '2025-03-03', timeIn: null,    timeOut: null,    hoursWorked: 0,    status: 'absent',     shift: 'Day',   isAdditionalShift: false, issues: ['No time-in recorded', 'No time-out recorded'] },
    { id: 'ATT004', employeeId: 'EMP004', employeeName: 'Carlos Dela Cruz', date: '2025-03-03', timeIn: '08:00', timeOut: '17:00', hoursWorked: 8.00, status: 'present',    shift: 'Day',   isAdditionalShift: false, issues: [] },
    { id: 'ATT005', employeeId: 'EMP005', employeeName: 'Liza Mendoza',     date: '2025-03-03', timeIn: '20:00', timeOut: '05:00', hoursWorked: 9.00, status: 'present',    shift: 'Night', isAdditionalShift: false, issues: [] },
    { id: 'ATT006', employeeId: 'EMP006', employeeName: 'Roberto Bautista', date: '2025-03-03', timeIn: '08:00', timeOut: null,    hoursWorked: 0,    status: 'incomplete', shift: 'Day',   isAdditionalShift: false, issues: ['No time-out recorded'] },
    { id: 'ATT007', employeeId: 'EMP007', employeeName: 'Jenny Garcia',     date: '2025-03-03', timeIn: '08:05', timeOut: '20:10', hoursWorked: 12.0, status: 'present',    shift: 'Day',   isAdditionalShift: false, issues: [] },
    { id: 'ATT008', employeeId: 'EMP008', employeeName: 'Miguel Torres',    date: '2025-03-03', timeIn: '08:00', timeOut: '17:00', hoursWorked: 8.00, status: 'present',    shift: 'Day',   isAdditionalShift: true,  issues: [] },
    { id: 'ATT009', employeeId: 'EMP001', employeeName: 'Maria Santos',     date: '2025-03-04', timeIn: '08:00', timeOut: '17:00', hoursWorked: 8.00, status: 'present',    shift: 'Day',   isAdditionalShift: false, issues: [] },
    { id: 'ATT010', employeeId: 'EMP002', employeeName: 'Jose Reyes',       date: '2025-03-04', timeIn: '09:10', timeOut: '17:05', hoursWorked: 7.92, status: 'late',       shift: 'Day',   isAdditionalShift: false, issues: ['Arrived 70 minutes late'] },
    { id: 'ATT011', employeeId: 'EMP003', employeeName: 'Ana Cruz',         date: '2025-03-04', timeIn: '08:00', timeOut: '17:00', hoursWorked: 8.00, status: 'present',    shift: 'Day',   isAdditionalShift: false, issues: [] },
    { id: 'ATT012', employeeId: 'EMP005', employeeName: 'Liza Mendoza',     date: '2025-03-05', timeIn: '20:05', timeOut: '05:00', hoursWorked: 8.92, status: 'present',    shift: 'Night', isAdditionalShift: false, issues: [] },
    { id: 'ATT013', employeeId: 'EMP004', employeeName: 'Carlos Dela Cruz', date: '2025-03-05', timeIn: '08:00', timeOut: '17:00', hoursWorked: 8.00, status: 'present',    shift: 'Day',   isAdditionalShift: false, issues: [] },
    { id: 'ATT014', employeeId: 'EMP006', employeeName: 'Roberto Bautista', date: '2025-03-05', timeIn: '08:15', timeOut: '17:00', hoursWorked: 7.75, status: 'late',       shift: 'Day',   isAdditionalShift: false, issues: ['Arrived 15 minutes late'] },
];

/* =====================================================
   STATE
   ===================================================== */
const state = {
    month: '', year: '',
    status: 'all', department: 'all', position: 'all', employee: 'all'
};

const MONTH_NAMES = ['January','February','March','April','May','June',
                     'July','August','September','October','November','December'];

/* =====================================================
   DATA HELPERS
   ===================================================== */
const getMonthName = mm => MONTH_NAMES[parseInt(mm) - 1] || '';
const getEmp       = id => sampleEmployees.find(e => e.id === id) || {};
const cap          = s  => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';

function getRecordsForPeriod() {
    return sampleAttendanceRecords
        .filter(r => {
            const [y, m] = r.date.split('-');
            return y === state.year && m === state.month;
        })
        .map(r => {
            const emp = getEmp(r.employeeId);
            return { ...r, department: emp.department || 'Unknown', position: emp.position || 'Unknown', role: emp.role || 'Unknown' };
        });
}

function getFilteredRecords() {
    return getRecordsForPeriod().filter(r => {
        if (state.status === 'issues') { if (!r.issues || !r.issues.length) return false; }
        else if (state.status !== 'all' && r.status !== state.status) return false;
        if (state.department !== 'all' && r.department !== state.department) return false;
        if (state.position   !== 'all' && r.position   !== state.position)   return false;
        if (state.employee   !== 'all' && r.employeeId !== state.employee)    return false;
        return true;
    });
}

/* =====================================================
   CASCADING DROPDOWNS
   ===================================================== */
function rebuildDropdown(selId, items, valueFn, labelFn, current) {
    const sel   = document.getElementById(selId);
    const first = sel.options[0].outerHTML;
    sel.innerHTML = first;
    items.forEach(item => {
        const o = document.createElement('option');
        o.value = valueFn(item); o.textContent = labelFn(item);
        if (o.value === current) o.selected = true;
        sel.appendChild(o);
    });
}

function rebuildDeptFilter() {
    const depts = [...new Set(sampleEmployees.filter(e => e.status === 'active').map(e => e.department))].sort();
    rebuildDropdown('filter-department', depts, d => d, d => d, state.department);
}

function rebuildPosFilter() {
    let emps = sampleEmployees.filter(e => e.status === 'active');
    if (state.department !== 'all') emps = emps.filter(e => e.department === state.department);
    const positions = [...new Set(emps.map(e => e.position))].sort();
    rebuildDropdown('filter-position', positions, p => p, p => p, state.position);
}

function rebuildEmpFilter() {
    let emps = sampleEmployees.filter(e => e.status === 'active');
    if (state.department !== 'all') emps = emps.filter(e => e.department === state.department);
    if (state.position   !== 'all') emps = emps.filter(e => e.position   === state.position);
    emps.sort((a, b) => a.name.localeCompare(b.name));
    rebuildDropdown('filter-employee', emps, e => e.id, e => `${e.name} (${e.id})`, state.employee);
}

/* =====================================================
   RENDER
   ===================================================== */
function renderAll() {
    const all      = getRecordsForPeriod();
    const filtered = getFilteredRecords();
    const total   = filtered.length;
    const present = filtered.filter(r => r.status === 'present').length;
    const late    = filtered.filter(r => r.status === 'late').length;
    const absent  = filtered.filter(r => r.status === 'absent').length;
    const issues  = filtered.filter(r => r.issues && r.issues.length).length;

    // Stat numbers
    document.getElementById('stat-total').textContent   = total;
    document.getElementById('stat-present').textContent = present;
    document.getElementById('stat-late').textContent    = late;
    document.getElementById('stat-absent').textContent  = absent;
    document.getElementById('stat-issues').textContent  = issues;

    // Active card highlight
    document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
    const ac = document.querySelector(`.stat-card[data-filter="${state.status}"]`);
    if (ac) ac.classList.add('active');

    // Header & count
    const period = `${getMonthName(state.month)} ${state.year}`;
    document.getElementById('records-title').textContent = `Employee Attendance \u2014 ${period}`;
    document.getElementById('records-shown').textContent = filtered.length;
    document.getElementById('records-total').textContent = all.length;

    // Issues alert
    const alertEl = document.getElementById('issues-alert');
    if (issues > 0) {
        alertEl.classList.remove('d-none');
        document.getElementById('issues-alert-text').textContent =
            `${issues} record(s) have attendance issues that require attention.`;
    } else { alertEl.classList.add('d-none'); }

    // Active filter summary bar
    const parts = [];
    if (state.status !== 'all')     parts.push(`Status: ${cap(state.status)}`);
    if (state.department !== 'all') parts.push(`Dept: ${state.department}`);
    if (state.position   !== 'all') parts.push(`Position: ${state.position}`);
    if (state.employee   !== 'all') {
        const e = sampleEmployees.find(e => e.id === state.employee);
        if (e) parts.push(`Employee: ${e.name}`);
    }
    const sumEl = document.getElementById('filter-summary');
    if (parts.length) {
        sumEl.classList.remove('d-none');
        document.getElementById('filter-summary-text').textContent = parts.join(' | ');
    } else { sumEl.classList.add('d-none'); }

    renderRecords(filtered);
    renderSummary(total, present, late, absent);

    // Print meta
    document.getElementById('print-period').textContent    = `Period: ${period}`;
    document.getElementById('print-generated').textContent = `Generated: ${new Date().toLocaleString()}`;
}

/* -- Badge & Icon helpers -------------------------------- */
function statusBadge(status) {
    const map = { present: 'bg-primary', late: 'bg-secondary', absent: 'bg-secondary',
                  incomplete: 'bg-secondary', halfday: 'bg-secondary', overtime: 'bg-primary' };
    return `<span class="badge ${map[status] || 'bg-secondary'}">${cap(status || 'Unknown')}</span>`;
}

function statusIconHtml(status) {
    const map = {
        present:    ['bi-check-circle-fill', 'text-primary'],
        late:       ['bi-clock-fill',         'text-secondary'],
        absent:     ['bi-x-circle-fill',      'text-secondary'],
        incomplete: ['bi-exclamation-circle-fill', 'text-secondary'],
    };
    const [icon, color] = map[status] || ['bi-dash-circle', 'text-muted'];
    return `<i class="bi ${icon} ${color}" style="font-size:1.15rem;flex-shrink:0;margin-top:2px;"></i>`;
}

function renderRecords(records) {
    const list  = document.getElementById('attendance-list');
    const empty = document.getElementById('attendance-empty');

    if (!records.length) { list.innerHTML = ''; empty.classList.remove('d-none'); return; }
    empty.classList.add('d-none');

    list.innerHTML = records.map(r => `
        <div class="border-bottom px-3 py-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <div class="d-flex align-items-start gap-2">
                    ${statusIconHtml(r.status)}
                    <div>
                        <div class="fw-semibold lh-sm">
                            ${r.isAdditionalShift
                                ? '<span class="badge bg-secondary me-1" style="font-size:.7rem;">Additional Shift</span>'
                                : ''}
                            ${r.employeeName}
                        </div>
                        <small class="text-muted">
                            ${r.employeeId} &bull; ${r.department} &bull; ${r.position} &bull; ${cap(r.role)}
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <small class="text-muted">${r.date}</small>
                    ${statusBadge(r.status)}
                </div>
            </div>
            <div class="row g-2 rounded p-2 mx-0 bg-body-tertiary">
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Time In</small>
                    <span>${r.timeIn || '&mdash;'}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Time Out</small>
                    <span>${r.timeOut || '&mdash;'}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Hours Worked</small>
                    <span>${r.hoursWorked ? r.hoursWorked.toFixed(2) + ' hrs' : '&mdash;'}</span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Shift</small>
                    <span>${r.shift || '&mdash;'}</span>
                </div>
            </div>
            ${r.issues && r.issues.length ? `
            <div class="mt-2 px-2 py-2 border rounded bg-body-secondary">
                <small class="fw-semibold d-block mb-1">
                    <i class="bi bi-exclamation-triangle me-1"></i>Issues:
                </small>
                ${r.issues.map(i => `<small class="d-block text-muted ms-2">&bull; ${i}</small>`).join('')}
            </div>` : ''}
        </div>
    `).join('');
}

function renderSummary(total, present, late, absent) {
    const card = document.getElementById('summary-card');
    if (!total) { card.classList.add('d-none'); return; }
    card.classList.remove('d-none');
    const pct = n => total > 0 ? ((n / total) * 100).toFixed(1) + '%' : '0%';
    document.getElementById('sum-attendance-rate').textContent = pct(present + late);
    document.getElementById('sum-ontime-rate').textContent     = pct(present);
    document.getElementById('sum-late-rate').textContent       = pct(late);
    document.getElementById('sum-absence-rate').textContent    = pct(absent);
}

/* =====================================================
   FILTER HANDLERS
   ===================================================== */
function applyFilters() {
    state.month      = document.getElementById('filter-month').value;
    state.year       = document.getElementById('filter-year').value;
    state.status     = document.getElementById('filter-status').value;
    state.department = document.getElementById('filter-department').value;
    state.position   = document.getElementById('filter-position').value;
    state.employee   = document.getElementById('filter-employee').value;
    renderAll();
}

function onDepartmentChange() {
    state.department = document.getElementById('filter-department').value;
    state.position = 'all'; state.employee = 'all';
    rebuildPosFilter(); rebuildEmpFilter();
    applyFilters();
}

function onPositionChange() {
    state.position = document.getElementById('filter-position').value;
    state.employee = 'all';
    rebuildEmpFilter();
    applyFilters();
}

function setStatusFilter(value) {
    state.status = value;
    document.getElementById('filter-status').value = value;
    renderAll();
}

function resetFilters() {
    const now = new Date();
    Object.assign(state, {
        month: String(now.getMonth() + 1).padStart(2, '0'),
        year:  String(now.getFullYear()),
        status: 'all', department: 'all', position: 'all', employee: 'all'
    });
    document.getElementById('filter-month').value  = state.month;
    document.getElementById('filter-year').value   = state.year;
    document.getElementById('filter-status').value = 'all';
    rebuildDeptFilter(); rebuildPosFilter(); rebuildEmpFilter();
    renderAll();
}

function handlePrint() { window.print(); }

/* =====================================================
   INIT
   ===================================================== */
document.addEventListener('DOMContentLoaded', () => {
    // Build year dropdown
    const yearSel = document.getElementById('filter-year');
    const cy = new Date().getFullYear();
    for (let y = cy - 4; y <= cy + 1; y++) {
        const o = document.createElement('option');
        o.value = String(y); o.textContent = y;
        if (y === cy) o.selected = true;
        yearSel.appendChild(o);
    }

    // Set initial state to current month/year
    const now = new Date();
    state.month = String(now.getMonth() + 1).padStart(2, '0');
    state.year  = String(now.getFullYear());
    document.getElementById('filter-month').value = state.month;

    // Build cascading dropdowns, then render
    rebuildDeptFilter();
    rebuildPosFilter();
    rebuildEmpFilter();
    renderAll();
});
</script>
@endpush