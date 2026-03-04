@extends('layouts.main')

@section('title', 'HR Reports')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">HR Reports</li>
    </ol>
@endsection

@push('styles')
<style>
    .report-card { transition: box-shadow 0.15s; cursor: pointer; }
    .report-card:hover { box-shadow: 0 0 0 2px var(--bs-primary); }
    #previewModal .modal-dialog { max-width: 92vw; }
    #previewModal .modal-body  { max-height: 68vh; overflow-y: auto; }
    #reportPreviewArea table   { font-size: 11px; }
    #reportPreviewArea thead th { white-space: nowrap; }
    #reportPreviewArea td       { white-space: nowrap; }
    .status-pill {
        font-size: 10px; font-weight: 500; padding: 2px 8px;
        border-radius: 20px; background: #e9ecef; color: #495057;
        display: inline-block;
    }
    @media print {
        #reportPreviewArea table { font-size: 9px; }
        #reportPreviewArea th, #reportPreviewArea td { padding: 2px 4px !important; }
        @page { size: landscape; margin: .5cm; }
    }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">HR Reports</h4>
        <p class="text-muted mb-0 mt-1" style="font-size:13px">
            Generate essential employee and payroll reports
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <div class="input-group input-group-sm" style="width:185px">
            <span class="input-group-text">Period</span>
            <input type="month" id="selectedMonth" class="form-control">
        </div>
        <select id="cutoffPeriod" class="form-select form-select-sm" style="width:175px">
            <option value="full">Full Month</option>
            <option value="first">1st Cutoff (1–15)</option>
            <option value="second">2nd Cutoff (16–end)</option>
        </select>
    </div>
</div>

{{-- Search + Category Filters --}}
<div class="row g-2 mb-4 align-items-center">
    <div class="col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="searchReports" class="form-control"
                   placeholder="Search reports…">
            <button class="btn btn-outline-secondary" type="button"
                    onclick="document.getElementById('searchReports').value=''; renderCards()">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
    <div class="col-md-8 d-flex gap-2 flex-wrap" id="categoryFilters"></div>
</div>

{{-- Report Cards --}}
<div class="row g-3" id="reportCardsContainer"></div>

{{-- No results --}}
<div id="noResults" class="text-center py-5 d-none">
    <i class="bi bi-file-earmark-x fs-1 text-muted d-block mb-2"></i>
    <p class="text-muted mb-0">No reports match your search or filter.</p>
</div>

{{-- ======================================================== PREVIEW MODAL --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0 fw-semibold" id="previewModalTitle">—</h5>
                    <small class="text-muted" id="previewModalPeriod"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="reportPreviewArea">
                <div class="text-center py-4 text-muted">Generating…</div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button class="btn btn-secondary btn-sm" onclick="printPreview()">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary btn-sm" onclick="exportCSV()">
                        <i class="bi bi-filetype-csv me-1"></i>Export CSV
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ================================================================
   SAMPLE DATA
   ================================================================ */
const sampleEmployees = [
    { employeeId:'EMP001', fullName:'Maria Santos',      position:'Payroll Officer',    department:'Accounting', employmentStatus:'regular',      hireDate:'2021-03-15', gender:'Female', contactNumber:'09171234567', sss:'34-1234567-8', philHealth:'0123456789', pagIbig:'1234-5678-9012', tin:'123-456-789-000' },
    { employeeId:'EMP002', fullName:'Juan dela Cruz',    position:'HR Specialist',      department:'HR',         employmentStatus:'regular',      hireDate:'2020-07-01', gender:'Male',   contactNumber:'09281234567', sss:'34-2345678-9', philHealth:'1234567890', pagIbig:'2345-6789-0123', tin:'234-567-890-000' },
    { employeeId:'EMP003', fullName:'Anna Reyes',        position:'IT Support',         department:'IT',         employmentStatus:'probationary', hireDate:'2024-11-01', gender:'Female', contactNumber:'09391234567', sss:'34-3456789-0', philHealth:'2345678901', pagIbig:'3456-7890-1234', tin:'345-678-901-000' },
    { employeeId:'EMP004', fullName:'Carlos Manalo',     position:'Operations Manager', department:'Operations', employmentStatus:'regular',      hireDate:'2019-01-20', gender:'Male',   contactNumber:'09451234567', sss:'34-4567890-1', philHealth:'3456789012', pagIbig:'4567-8901-2345', tin:'456-789-012-000' },
    { employeeId:'EMP005', fullName:'Liza Gutierrez',    position:'Accounting Clerk',   department:'Accounting', employmentStatus:'regular',      hireDate:'2022-05-10', gender:'Female', contactNumber:'09561234567', sss:'34-5678901-2', philHealth:'4567890123', pagIbig:'5678-9012-3456', tin:'567-890-123-000' },
    { employeeId:'EMP006', fullName:'Ramon Bautista',    position:'Security Guard',     department:'Security',   employmentStatus:'probationary', hireDate:'2025-01-10', gender:'Male',   contactNumber:'09671234567', sss:'34-6789012-3', philHealth:'5678901234', pagIbig:'6789-0123-4567', tin:'678-901-234-000' },
    { employeeId:'EMP007', fullName:'Christine Flores',  position:'Nurse',              department:'Clinic',     employmentStatus:'regular',      hireDate:'2021-09-01', gender:'Female', contactNumber:'09781234567', sss:'34-7890123-4', philHealth:'6789012345', pagIbig:'7890-1234-5678', tin:'789-012-345-000' },
    { employeeId:'EMP008', fullName:'Danilo Cruz',       position:'Driver',             department:'Logistics',  employmentStatus:'regular',      hireDate:'2018-06-15', gender:'Male',   contactNumber:'09891234567', sss:'34-8901234-5', philHealth:'7890123456', pagIbig:'8901-2345-6789', tin:'890-123-456-000' },
];

const sampleAttendance = [
    { employeeId:'EMP001', employeeName:'Maria Santos',     department:'Accounting', date:'2025-05-02', shift:'Day',   timeIn:'08:02', timeOut:'17:05', hoursWorked:9.00, status:'present', lateMinutes:2,  undertimeMinutes:0  },
    { employeeId:'EMP001', employeeName:'Maria Santos',     department:'Accounting', date:'2025-05-03', shift:'Day',   timeIn:'08:15', timeOut:'17:00', hoursWorked:8.75, status:'late',    lateMinutes:15, undertimeMinutes:0  },
    { employeeId:'EMP002', employeeName:'Juan dela Cruz',   department:'HR',         date:'2025-05-02', shift:'Day',   timeIn:'08:00', timeOut:'17:00', hoursWorked:9.00, status:'present', lateMinutes:0,  undertimeMinutes:0  },
    { employeeId:'EMP002', employeeName:'Juan dela Cruz',   department:'HR',         date:'2025-05-03', shift:'Day',   timeIn:null,    timeOut:null,    hoursWorked:0.00, status:'absent',  lateMinutes:0,  undertimeMinutes:0  },
    { employeeId:'EMP003', employeeName:'Anna Reyes',       department:'IT',         date:'2025-05-02', shift:'Night', timeIn:'22:00', timeOut:'06:00', hoursWorked:8.00, status:'present', lateMinutes:0,  undertimeMinutes:0  },
    { employeeId:'EMP004', employeeName:'Carlos Manalo',    department:'Operations', date:'2025-05-02', shift:'Day',   timeIn:'07:58', timeOut:'17:00', hoursWorked:9.03, status:'present', lateMinutes:0,  undertimeMinutes:0  },
    { employeeId:'EMP005', employeeName:'Liza Gutierrez',   department:'Accounting', date:'2025-05-02', shift:'Day',   timeIn:'08:00', timeOut:'16:45', hoursWorked:8.75, status:'present', lateMinutes:0,  undertimeMinutes:15 },
    { employeeId:'EMP007', employeeName:'Christine Flores', department:'Clinic',     date:'2025-05-02', shift:'Day',   timeIn:'08:05', timeOut:'17:00', hoursWorked:8.92, status:'present', lateMinutes:5,  undertimeMinutes:0  },
    { employeeId:'EMP008', employeeName:'Danilo Cruz',      department:'Logistics',  date:'2025-05-03', shift:'Day',   timeIn:'08:30', timeOut:'17:00', hoursWorked:8.50, status:'late',    lateMinutes:30, undertimeMinutes:0  },
];

const samplePayroll = [
    { employeeId:'EMP001', employeeName:'Maria Santos',     department:'Accounting', basicPay:12000, overtimePay:750,  allowances:3000, grossPay:15750, sss:630,   philHealth:187.50, pagibig:100, tax:625,  loanDeductions:1000, lateDeductions:45, undertimeDeductions:0,  totalDeductions:2587.50, netPay:13162.50 },
    { employeeId:'EMP002', employeeName:'Juan dela Cruz',   department:'HR',         basicPay:13000, overtimePay:0,    allowances:3000, grossPay:16000, sss:680,   philHealth:200.00, pagibig:100, tax:750,  loanDeductions:0,    lateDeductions:0,  undertimeDeductions:0,  totalDeductions:1730.00, netPay:14270.00 },
    { employeeId:'EMP003', employeeName:'Anna Reyes',       department:'IT',         basicPay:11000, overtimePay:500,  allowances:2500, grossPay:14000, sss:580,   philHealth:175.00, pagibig:100, tax:500,  loanDeductions:0,    lateDeductions:0,  undertimeDeductions:0,  totalDeductions:1355.00, netPay:12645.00 },
    { employeeId:'EMP004', employeeName:'Carlos Manalo',    department:'Operations', basicPay:18000, overtimePay:1200, allowances:4000, grossPay:23200, sss:900,   philHealth:275.00, pagibig:100, tax:1800, loanDeductions:2500, lateDeductions:0,  undertimeDeductions:0,  totalDeductions:5575.00, netPay:17625.00 },
    { employeeId:'EMP005', employeeName:'Liza Gutierrez',   department:'Accounting', basicPay:10000, overtimePay:0,    allowances:2500, grossPay:12500, sss:530,   philHealth:156.25, pagibig:100, tax:375,  loanDeductions:0,    lateDeductions:0,  undertimeDeductions:85, totalDeductions:1246.25, netPay:11253.75 },
    { employeeId:'EMP007', employeeName:'Christine Flores', department:'Clinic',     basicPay:11500, overtimePay:300,  allowances:2500, grossPay:14300, sss:605,   philHealth:178.75, pagibig:100, tax:530,  loanDeductions:800,  lateDeductions:75, undertimeDeductions:0,  totalDeductions:2288.75, netPay:12011.25 },
    { employeeId:'EMP008', employeeName:'Danilo Cruz',      department:'Logistics',  basicPay:9500,  overtimePay:950,  allowances:2500, grossPay:12950, sss:505,   philHealth:161.88, pagibig:100, tax:312,  loanDeductions:0,    lateDeductions:0,  undertimeDeductions:0,  totalDeductions:1078.88, netPay:11871.12 },
];

const sampleSSSLoans = [
    { employeeId:'EMP001', employeeName:'Maria Santos',     department:'Accounting', loanAmount:20000, monthlyAmortization:1000, term:24, startDate:'2024-01-01', paymentsMade:4,  remainingBalance:16000, status:'Active',    createdDate:'2023-12-15' },
    { employeeId:'EMP004', employeeName:'Carlos Manalo',    department:'Operations', loanAmount:36000, monthlyAmortization:1500, term:24, startDate:'2023-06-01', paymentsMade:20, remainingBalance:6000,  status:'Active',    createdDate:'2023-05-20' },
    { employeeId:'EMP007', employeeName:'Christine Flores', department:'Clinic',     loanAmount:15000, monthlyAmortization:800,  term:24, startDate:'2022-01-01', paymentsMade:24, remainingBalance:0,     status:'Completed', createdDate:'2021-12-10' },
];

const samplePagibigLoans = [
    { employeeId:'EMP002', employeeName:'Juan dela Cruz',  department:'HR',         loanAmount:50000, monthlyAmortization:1200, term:48, startDate:'2023-01-01', paymentsMade:16, remainingBalance:30800, status:'Active',    createdDate:'2022-12-01' },
    { employeeId:'EMP008', employeeName:'Danilo Cruz',     department:'Logistics',  loanAmount:30000, monthlyAmortization:900,  term:36, startDate:'2022-05-01', paymentsMade:36, remainingBalance:0,     status:'Completed', createdDate:'2022-04-15' },
    { employeeId:'EMP005', employeeName:'Liza Gutierrez',  department:'Accounting', loanAmount:25000, monthlyAmortization:700,  term:36, startDate:'2024-03-01', paymentsMade:2,  remainingBalance:23600, status:'Active',    createdDate:'2024-02-20' },
];

/* ================================================================
   REPORT DEFINITIONS
   ================================================================ */
const REPORTS = [
    {
        id: 'employee-masterlist',
        title: 'Employee Master List (201 Files)',
        description: 'Complete employee roster with personal, employment, and government number details.',
        category: 'analytics', categoryLabel: 'Analytics',
        requirement: 'DOLE', frequency: 'As needed', icon: 'bi-people',
    },
    {
        id: 'daily-attendance',
        title: 'Daily Time Record (DTR) Summary',
        description: 'Employee attendance, tardiness, and absence records per cutoff period.',
        category: 'attendance', categoryLabel: 'Attendance',
        requirement: 'DOLE', frequency: 'Daily / Monthly', icon: 'bi-clock-history',
    },
    {
        id: 'payroll-register',
        title: 'Payroll Register',
        description: 'Full payroll summary with gross earnings, government deductions, and net pay.',
        category: 'payroll', categoryLabel: 'Payroll',
        requirement: 'Internal / DOLE', frequency: 'Per payroll period', icon: 'bi-cash-stack',
    },
    {
        id: 'sss-loans-summary',
        title: 'SSS Loans Summary',
        description: 'All SSS loans with monthly amortization schedule and outstanding balances.',
        category: 'payroll', categoryLabel: 'Payroll',
        requirement: 'SSS', frequency: 'As needed', icon: 'bi-credit-card',
    },
    {
        id: 'pagibig-loans-summary',
        title: 'Pag-IBIG Loans Summary',
        description: 'All Pag-IBIG loans with monthly amortization schedule and outstanding balances.',
        category: 'payroll', categoryLabel: 'Payroll',
        requirement: 'Pag-IBIG', frequency: 'As needed', icon: 'bi-credit-card-2-back',
    },
];

const CATEGORIES = [
    { value: 'all',        label: 'All Reports' },
    { value: 'payroll',    label: 'Payroll'     },
    { value: 'attendance', label: 'Attendance'  },
    { value: 'analytics',  label: 'Analytics'   },
];

/* ================================================================
   STATE
   ================================================================ */
let activeCategory     = 'all';
let currentReportId    = null;
let currentData        = null;
let currentPeriodLabel = '';

/* ================================================================
   INIT
   ================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const now = new Date();
    document.getElementById('selectedMonth').value =
        `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
    renderCategoryFilters();
    renderCards();
    document.getElementById('searchReports').addEventListener('input', renderCards);
});

/* ================================================================
   CATEGORY FILTERS
   ================================================================ */
function renderCategoryFilters() {
    document.getElementById('categoryFilters').innerHTML = CATEGORIES.map(c => `
        <button type="button"
                class="btn btn-sm ${activeCategory === c.value ? 'btn-primary' : 'btn-secondary'}"
                onclick="setCategory('${c.value}')">
            ${c.label}
        </button>`).join('');
}

function setCategory(val) {
    activeCategory = val;
    renderCategoryFilters();
    renderCards();
}

/* ================================================================
   RENDER CARDS
   ================================================================ */
function renderCards() {
    const q         = document.getElementById('searchReports').value.trim().toLowerCase();
    const container = document.getElementById('reportCardsContainer');
    const noRes     = document.getElementById('noResults');

    const filtered = REPORTS.filter(r => {
        const catOk  = activeCategory === 'all' || r.category === activeCategory;
        const srchOk = !q
            || r.title.toLowerCase().includes(q)
            || r.description.toLowerCase().includes(q)
            || r.requirement.toLowerCase().includes(q)
            || r.categoryLabel.toLowerCase().includes(q);
        return catOk && srchOk;
    });

    if (!filtered.length) {
        container.innerHTML = '';
        noRes.classList.remove('d-none');
        return;
    }
    noRes.classList.add('d-none');

    container.innerHTML = filtered.map(r => `
        <div class="col-sm-6 col-xl-4">
            <div class="card report-card h-100 mb-0 border">
                <div class="card-body d-flex flex-column gap-2 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-secondary fw-normal"
                              style="font-size:10px;letter-spacing:.4px">
                            ${r.categoryLabel}
                        </span>
                        <i class="bi ${r.icon} text-muted" style="font-size:1.25rem"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold" style="font-size:13.5px">${r.title}</h6>
                        <p class="text-muted mb-0"
                           style="font-size:11.5px;line-height:1.45">${r.description}</p>
                    </div>
                    <div class="mt-auto pt-2 border-top d-flex justify-content-between"
                         style="font-size:11px">
                        <span class="text-muted">
                            Required by&colon;
                            <strong class="text-body">${r.requirement}</strong>
                        </span>
                        <span class="text-muted">${r.frequency}</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                    <button type="button" class="btn btn-primary btn-sm w-100"
                            onclick="openReport('${r.id}')">
                        <i class="bi bi-eye me-1"></i>Preview Report
                    </button>
                </div>
            </div>
        </div>`).join('');
}

/* ================================================================
   OPEN / GENERATE REPORT
   ================================================================ */
function openReport(reportId) {
    const monthVal = document.getElementById('selectedMonth').value;
    const cutoff   = document.getElementById('cutoffPeriod').value;
    const [y, m]   = monthVal.split('-').map(Number);
    const lastDay  = new Date(y, m, 0).getDate();
    const mName    = new Date(y, m - 1).toLocaleString('en-PH', { month: 'long' });

    if      (cutoff === 'first')  currentPeriodLabel = `${mName} 1–15, ${y}`;
    else if (cutoff === 'second') currentPeriodLabel = `${mName} 16–${lastDay}, ${y}`;
    else                          currentPeriodLabel = `${mName} ${y}`;

    currentReportId = reportId;
    const report    = REPORTS.find(r => r.id === reportId);

    const MAP = {
        'employee-masterlist':   sampleEmployees,
        'daily-attendance':      sampleAttendance,
        'payroll-register':      samplePayroll,
        'sss-loans-summary':     sampleSSSLoans,
        'pagibig-loans-summary': samplePagibigLoans,
    };
    currentData = MAP[reportId] || [];

    document.getElementById('previewModalTitle').textContent  = report.title;
    document.getElementById('previewModalPeriod').textContent = 'Period: ' + currentPeriodLabel;
    document.getElementById('reportPreviewArea').innerHTML    = buildPreview(reportId, currentData);

    bootstrap.Modal.getOrCreateInstance(document.getElementById('previewModal')).show();
}

/* ================================================================
   PREVIEW BUILDERS
   ================================================================ */
function peso(n) {
    return '₱' + parseFloat(n || 0)
        .toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function sumCol(arr, key) { return arr.reduce((a, r) => a + (parseFloat(r[key]) || 0), 0); }

function reportWrap(title, inner) {
    return `<p class="fw-bold text-center mb-1">${title}</p>
            <p class="text-center text-muted mb-3" style="font-size:12px">
                Period: ${currentPeriodLabel}
            </p>
            <div class="table-responsive">${inner}</div>`;
}
function emptyState(msg) {
    return `<div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>${msg}
            </div>`;
}

function buildPreview(id, data) {
    switch (id) {
        case 'employee-masterlist':   return buildEmployeeMasterList(data);
        case 'daily-attendance':      return buildDTR(data);
        case 'payroll-register':      return buildPayrollRegister(data);
        case 'sss-loans-summary':     return buildLoans(data, 'SSS');
        case 'pagibig-loans-summary': return buildLoans(data, 'PAG-IBIG');
        default: return '<p class="text-muted p-3">No preview available.</p>';
    }
}

function buildEmployeeMasterList(data) {
    if (!data.length) return emptyState('No employees found.');
    const rows = data.map(e => `<tr>
        <td>${e.employeeId}</td>
        <td class="fw-semibold">${e.fullName}</td>
        <td>${e.position}</td><td>${e.department}</td>
        <td><span class="status-pill">${e.employmentStatus}</span></td>
        <td>${e.hireDate}</td><td>${e.gender}</td><td>${e.contactNumber}</td>
        <td>${e.sss}</td><td>${e.philHealth}</td><td>${e.pagIbig}</td><td>${e.tin}</td>
    </tr>`).join('');
    return reportWrap('EMPLOYEE MASTER LIST (201 FILES)', `
        <table class="table table-bordered table-sm table-hover align-middle mb-0">
            <thead class="table-secondary">
                <tr>
                    <th>Emp ID</th><th>Full Name</th><th>Position</th><th>Department</th>
                    <th>Status</th><th>Hire Date</th><th>Gender</th><th>Contact</th>
                    <th>SSS No.</th><th>PhilHealth No.</th><th>Pag-IBIG No.</th><th>TIN</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
            <tfoot class="table-secondary">
                <tr><td colspan="12" class="text-end fw-semibold">
                    Total Employees: ${data.length}
                </td></tr>
            </tfoot>
        </table>`);
}

function buildDTR(data) {
    if (!data.length) return emptyState('No attendance records found for this period.');
    const rows = data.map(r => `<tr>
        <td>${r.employeeId}</td>
        <td class="fw-semibold">${r.employeeName}</td>
        <td>${r.department}</td><td>${r.date}</td><td>${r.shift}</td>
        <td>${r.timeIn  || '—'}</td><td>${r.timeOut || '—'}</td>
        <td class="text-end">${parseFloat(r.hoursWorked).toFixed(2)}</td>
        <td><span class="status-pill">${r.status}</span></td>
        <td class="text-end">${r.lateMinutes}</td>
        <td class="text-end">${r.undertimeMinutes}</td>
    </tr>`).join('');
    return reportWrap('DAILY TIME RECORD (DTR) SUMMARY', `
        <table class="table table-bordered table-sm table-hover align-middle mb-0">
            <thead class="table-secondary">
                <tr>
                    <th>Emp ID</th><th>Employee Name</th><th>Department</th>
                    <th>Date</th><th>Shift</th><th>Time In</th><th>Time Out</th>
                    <th class="text-end">Hrs</th><th>Status</th>
                    <th class="text-end">Late (min)</th><th class="text-end">UT (min)</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
            <tfoot class="table-secondary">
                <tr><td colspan="11" class="text-end fw-semibold">
                    Total Records: ${data.length}
                </td></tr>
            </tfoot>
        </table>`);
}

function buildPayrollRegister(data) {
    if (!data.length) return emptyState('No payroll records found for this period.');
    const rows = data.map(p => `<tr>
        <td>${p.employeeId}</td>
        <td class="fw-semibold">${p.employeeName}</td>
        <td>${p.department}</td>
        <td class="text-end">${peso(p.basicPay)}</td>
        <td class="text-end">${peso(p.overtimePay)}</td>
        <td class="text-end">${peso(p.allowances)}</td>
        <td class="text-end fw-semibold">${peso(p.grossPay)}</td>
        <td class="text-end">${peso(p.sss)}</td>
        <td class="text-end">${peso(p.philHealth)}</td>
        <td class="text-end">${peso(p.pagibig)}</td>
        <td class="text-end">${peso(p.tax)}</td>
        <td class="text-end">${peso(p.loanDeductions)}</td>
        <td class="text-end">${peso(p.lateDeductions + p.undertimeDeductions)}</td>
        <td class="text-end fw-semibold">${peso(p.totalDeductions)}</td>
        <td class="text-end fw-bold">${peso(p.netPay)}</td>
    </tr>`).join('');
    const lateTotal = data.reduce((a, p) => a + p.lateDeductions + p.undertimeDeductions, 0);
    return reportWrap('PAYROLL REGISTER', `
        <table class="table table-bordered table-sm table-hover align-middle mb-0">
            <thead class="table-secondary">
                <tr>
                    <th rowspan="2">Emp ID</th>
                    <th rowspan="2">Employee Name</th>
                    <th rowspan="2">Department</th>
                    <th colspan="4" class="text-center">EARNINGS</th>
                    <th colspan="7" class="text-center">DEDUCTIONS</th>
                    <th rowspan="2">NET PAY</th>
                </tr>
                <tr>
                    <th class="text-end">Basic</th><th class="text-end">OT Pay</th>
                    <th class="text-end">Allow.</th><th class="text-end">Gross</th>
                    <th class="text-end">SSS</th><th class="text-end">PhilHlth</th>
                    <th class="text-end">Pag-IBIG</th><th class="text-end">Tax</th>
                    <th class="text-end">Loan</th><th class="text-end">Late/UT</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
            <tfoot class="table-secondary fw-semibold">
                <tr>
                    <td colspan="3" class="text-end">TOTALS</td>
                    <td class="text-end">${peso(sumCol(data,'basicPay'))}</td>
                    <td class="text-end">${peso(sumCol(data,'overtimePay'))}</td>
                    <td class="text-end">${peso(sumCol(data,'allowances'))}</td>
                    <td class="text-end">${peso(sumCol(data,'grossPay'))}</td>
                    <td class="text-end">${peso(sumCol(data,'sss'))}</td>
                    <td class="text-end">${peso(sumCol(data,'philHealth'))}</td>
                    <td class="text-end">${peso(sumCol(data,'pagibig'))}</td>
                    <td class="text-end">${peso(sumCol(data,'tax'))}</td>
                    <td class="text-end">${peso(sumCol(data,'loanDeductions'))}</td>
                    <td class="text-end">${peso(lateTotal)}</td>
                    <td class="text-end">${peso(sumCol(data,'totalDeductions'))}</td>
                    <td class="text-end">${peso(sumCol(data,'netPay'))}</td>
                </tr>
            </tfoot>
        </table>`);
}

function buildLoans(data, type) {
    if (!data.length) return emptyState(`No ${type} loans found.`);
    const totalAmt  = data.reduce((a, l) => a + l.loanAmount, 0);
    const totalBal  = data.reduce((a, l) => a + l.remainingBalance, 0);
    const active    = data.filter(l => l.status === 'Active').length;
    const completed = data.filter(l => l.status === 'Completed').length;

    const summary = `
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border text-center p-3">
                    <div class="text-muted mb-1" style="font-size:11px">Total Loans</div>
                    <div class="fs-4 fw-bold">${data.length}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border text-center p-3">
                    <div class="text-muted mb-1" style="font-size:11px">Active</div>
                    <div class="fs-4 fw-bold">${active}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border text-center p-3">
                    <div class="text-muted mb-1" style="font-size:11px">Completed</div>
                    <div class="fs-4 fw-bold">${completed}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border text-center p-3">
                    <div class="text-muted mb-1" style="font-size:11px">Total Balance</div>
                    <div class="fw-bold" style="font-size:15px">${peso(totalBal)}</div>
                </div>
            </div>
        </div>`;

    const rows = data.map(l => `<tr>
        <td>${l.employeeId}</td>
        <td class="fw-semibold">${l.employeeName}</td>
        <td>${l.department}</td>
        <td class="text-end fw-semibold">${peso(l.loanAmount)}</td>
        <td class="text-end">${peso(l.monthlyAmortization)}</td>
        <td class="text-center">${l.term} mos</td>
        <td>${l.startDate}</td>
        <td class="text-center">${l.paymentsMade} / ${l.term}</td>
        <td class="text-end fw-semibold">${peso(l.remainingBalance)}</td>
        <td><span class="status-pill">${l.status}</span></td>
    </tr>`).join('');

    const table = `
        <table class="table table-bordered table-sm table-hover align-middle mb-0">
            <thead class="table-secondary">
                <tr>
                    <th>Emp ID</th><th>Employee Name</th><th>Department</th>
                    <th class="text-end">Loan Amt</th><th class="text-end">Monthly</th>
                    <th class="text-center">Term</th><th>Start Date</th>
                    <th class="text-center">Payments</th>
                    <th class="text-end">Balance</th><th>Status</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
            <tfoot class="table-secondary fw-semibold">
                <tr>
                    <td colspan="3" class="text-end">TOTALS</td>
                    <td class="text-end">${peso(totalAmt)}</td>
                    <td colspan="4"></td>
                    <td class="text-end">${peso(totalBal)}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>`;

    return `<p class="fw-bold text-center mb-1">${type} LOANS SUMMARY</p>
            <p class="text-center text-muted mb-3" style="font-size:12px">
                As of ${currentPeriodLabel}
            </p>
            ${summary}
            <div class="table-responsive">${table}</div>`;
}

/* ================================================================
   PRINT
   ================================================================ */
function printPreview() {
    const content = document.getElementById('reportPreviewArea').innerHTML;
    const win = window.open('', '_blank');
    if (!win) { alert('Please allow pop-ups to use this feature.'); return; }
    win.document.write(`<!DOCTYPE html><html><head>
        <title>HR Report – ${currentPeriodLabel}</title>
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <style>
            body { padding: 20px; font-size: 11px; }
            table { font-size: 10px; }
            .status-pill { font-size:9px; padding:1px 6px; border-radius:20px;
                           background:#e9ecef; color:#495057; }
            @media print { @page { size: landscape; margin: .5cm; } }
        </style>
    </head><body>${content}</body></html>`);
    win.document.close();
    win.onload = () => { win.focus(); setTimeout(() => { win.print(); win.close(); }, 350); };
}

/* ================================================================
   EXPORT CSV
   ================================================================ */
function exportCSV() {
    if (!currentData || !currentData.length) {
        Swal.fire({
            icon: 'info', title: 'No Data',
            text: 'Nothing to export for the selected period.',
            confirmButtonColor: '#0d6efd',
        });
        return;
    }
    const headers = Object.keys(currentData[0]).map(k =>
        k.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase()).trim()
    );
    const rows = currentData.map(row =>
        Object.values(row).map(v => {
            if (v === null || v === undefined) return '';
            const s = String(v);
            return (s.includes(',') || s.includes('"'))
                ? `"${s.replace(/"/g, '""')}"` : s;
        }).join(',')
    );
    const csv  = '\uFEFF' + [headers.join(','), ...rows].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), {
        href: url,
        download: `${currentReportId}_${document.getElementById('selectedMonth').value}.csv`,
    });
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endpush