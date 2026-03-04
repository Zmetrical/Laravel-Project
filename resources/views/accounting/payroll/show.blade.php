@extends('layouts.main')

@section('title', 'Employee Payroll & DTR')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Employee Payroll & DTR</li>
    </ol>
@endsection

@push('styles')
<style>
    .employee-item { cursor: pointer; transition: border-color .15s; }
    .employee-item:hover { border-left: 3px solid var(--bs-secondary); }
    .employee-item.active { border-left: 3px solid var(--bs-primary); background: rgba(var(--bs-primary-rgb), .06); }
    .employee-avatar {
        width: 38px; height: 38px; border-radius: 6px;
        background: var(--bs-secondary); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 600; font-size: .8rem; flex-shrink: 0;
    }
    .dtr-list { max-height: 430px; overflow-y: auto; }
    .employee-list-scroll { max-height: 620px; overflow-y: auto; }
    .payroll-label { font-size: .72rem; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: .05em; }
    .payroll-row { display: flex; justify-content: space-between; align-items: center; padding: .45rem .6rem; border-bottom: 1px solid var(--bs-border-color); }
    .payroll-row:last-child { border-bottom: none; }
    .net-pay-box { background: rgba(var(--bs-primary-rgb), .08); border: 1px solid var(--bs-primary); border-radius: 6px; }
    .badge-shift-day  { background: rgba(var(--bs-warning-rgb), .15); color: var(--bs-warning-text-emphasis); border: 1px solid rgba(var(--bs-warning-rgb), .3); }
    .badge-shift-night{ background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary-text-emphasis); border: 1px solid rgba(var(--bs-primary-rgb), .25); }
    .dtr-record { border-left: 3px solid transparent; }
    .dtr-record.has-late  { border-left-color: var(--bs-warning); }
    .dtr-record.is-absent { border-left-color: var(--bs-secondary); opacity: .7; }
    #panel-detail { display: none; }
    .stat-mini { padding: .5rem .8rem; border: 1px solid var(--bs-border-color); border-radius: 6px; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Employee Payroll &amp; DTR</h4>
        <small class="text-muted">View attendance records and compute payslips</small>
    </div>
    <div class="d-flex gap-2" id="header-actions" style="display:none!important">
        <button class="btn btn-secondary btn-sm" id="btn-release-payslip">
            <i class="bi bi-send me-1"></i> Release Payslip
        </button>
        <button class="btn btn-primary btn-sm" id="btn-print-payslip">
            <i class="bi bi-printer me-1"></i> Print Payslip
        </button>
    </div>
</div>

<div class="row g-3">

    {{-- ===================== LEFT: EMPLOYEE LIST ===================== --}}
    <div class="col-lg-4" id="col-employees">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">Employees <span class="badge bg-secondary ms-1" id="emp-count">0</span></h6>
                    <button class="btn btn-sm btn-outline-secondary d-none" id="btn-close-detail" title="Clear selection">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <div class="card-body pb-1">
                {{-- Search --}}
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="emp-search" placeholder="Search name, position…">
                </div>
                {{-- Department filter --}}
                <select class="form-select form-select-sm mb-2" id="dept-filter">
                    <option value="all">All Departments</option>
                </select>
            </div>
            <div class="employee-list-scroll px-2 pb-2" id="employee-list">
                {{-- Rendered by JS --}}
            </div>
        </div>
    </div>

    {{-- ===================== RIGHT: DETAIL PANEL ===================== --}}
    <div class="col-lg-8" id="panel-detail">

        {{-- Employee Info --}}
        <div class="card shadow-sm mb-3" id="card-emp-info">
            <div class="card-body py-3">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="employee-avatar fs-5" id="detail-avatar"></div>
                        <div>
                            <h5 class="mb-0" id="detail-name"></h5>
                            <div class="text-muted small" id="detail-position"></div>
                            <div class="text-muted" style="font-size:.75rem" id="detail-dept"></div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="payroll-label">Monthly Salary</div>
                        <div class="fw-bold fs-5 text-primary" id="detail-salary"></div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row g-2">
                    <div class="col-4">
                        <div class="stat-mini">
                            <div class="payroll-label">Employee ID</div>
                            <div class="fw-semibold small" id="detail-id"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-mini">
                            <div class="payroll-label">Daily Rate</div>
                            <div class="fw-semibold small text-primary" id="detail-daily-rate"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-mini">
                            <div class="payroll-label">Hourly Rate</div>
                            <div class="fw-semibold small text-primary" id="detail-hourly-rate"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DTR --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="card-title mb-0"><i class="bi bi-calendar3 me-1"></i> Daily Time Record</h6>
                    <span class="badge bg-secondary small" id="dtr-record-count">0 records</span>
                </div>
                {{-- Period controls --}}
                <div class="row g-2 mt-1">
                    <div class="col-sm-3">
                        <label class="payroll-label mb-1">Month</label>
                        <select class="form-select form-select-sm" id="dtr-month"></select>
                    </div>
                    <div class="col-sm-2">
                        <label class="payroll-label mb-1">Year</label>
                        <select class="form-select form-select-sm" id="dtr-year"></select>
                    </div>
                    <div class="col-sm-4">
                        <label class="payroll-label mb-1">Cutoff Period</label>
                        <select class="form-select form-select-sm" id="cutoff-period">
                            <option value="first-half">1st – 15th</option>
                            <option value="second-half">16th – End</option>
                            <option value="full-month">Full Month</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="payroll-label mb-1">Attendance</label>
                        <div class="stat-mini py-1">
                            <span class="fw-bold" id="days-present-stat">0</span>
                            <span class="text-muted"> / </span>
                            <span id="working-days-stat">0</span>
                            <span class="text-muted small"> days</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="dtr-list" id="dtr-records">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-clock fs-3 d-block mb-2"></i>
                        Select an employee to view records
                    </div>
                </div>
            </div>
        </div>

        {{-- Payroll Computation --}}
        <div class="card shadow-sm" id="card-payroll" style="display:none">
            <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="card-title mb-0"><i class="bi bi-calculator me-1"></i> Payroll Computation</h6>
                <div class="d-flex gap-2">
                    <span class="badge bg-secondary small" id="payslip-status-badge">Not Released</span>
                    <button class="btn btn-sm btn-outline-secondary" id="btn-release-payslip-2">
                        <i class="bi bi-send me-1"></i> Release
                    </button>
                    <button class="btn btn-sm btn-primary" id="btn-print-payslip-2">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Earnings column --}}
                    <div class="col-md-6">
                        <div class="payroll-label mb-2">Earnings</div>
                        <div id="earnings-breakdown"></div>
                    </div>
                    {{-- Deductions column --}}
                    <div class="col-md-6">
                        <div class="payroll-label mb-2">Deductions</div>
                        <div id="deductions-breakdown"></div>
                    </div>
                </div>
                <hr>
                {{-- Summary --}}
                <div class="row g-2" id="payroll-summary"></div>
            </div>
        </div>

    </div>{{-- /panel-detail --}}
</div>

{{-- ===================== PRINT MODAL ===================== --}}
<div class="modal fade" id="modal-payslip" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payslip Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="payslip-print-area">
                {{-- Populated by JS --}}
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
// =============================================================
//  PAYROLL MODULE  –  All data & logic in plain JS
//  No external API calls. Replace arrays with fetch() later.
// =============================================================
const PayrollModule = (() => {

    // ─── SAMPLE DATA ──────────────────────────────────────────
    const EMPLOYEES = [
        {
            id: 'EMP001', username: 'jdcruz',
            firstName: 'Jose',  lastName: 'De la Cruz', fullName: 'De la Cruz, Jose',
            position: 'Software Developer', department: 'IT Department',
            employmentStatus: 'Regular', basicSalary: 45000,
            email: 'jdcruz@company.com', defaultShift: 'Day',
        },
        {
            id: 'EMP002', username: 'msantos',
            firstName: 'Maria', lastName: 'Santos', fullName: 'Santos, Maria',
            position: 'Payroll Officer', department: 'Accounting',
            employmentStatus: 'Regular', basicSalary: 38000,
            email: 'msantos@company.com', defaultShift: 'Day',
        },
        {
            id: 'EMP003', username: 'rreyes',
            firstName: 'Ramon', lastName: 'Reyes', fullName: 'Reyes, Ramon',
            position: 'Security Guard', department: 'Security',
            employmentStatus: 'Probationary', basicSalary: 22000,
            email: 'rreyes@company.com', defaultShift: 'Night',
        },
        {
            id: 'EMP004', username: 'abautista',
            firstName: 'Ana', lastName: 'Bautista', fullName: 'Bautista, Ana',
            position: 'HR Specialist', department: 'Human Resources',
            employmentStatus: 'Regular', basicSalary: 35000,
            email: 'abautista@company.com', defaultShift: 'Day',
        },
        {
            id: 'EMP005', username: 'cgarcia',
            firstName: 'Carlos', lastName: 'Garcia', fullName: 'Garcia, Carlos',
            position: 'Warehouse Staff', department: 'Operations',
            employmentStatus: 'Probationary', basicSalary: 20000,
            email: 'cgarcia@company.com', defaultShift: 'Day',
        },
    ];

    // Attendance records keyed by employeeId → array of records
    const ATTENDANCE = {
        'EMP001': [
            { date:'2025-12-01', shift:'Day Shift',   shiftType:'day',   timeIn:'06:02', timeOut:'14:05', hoursWorked:7.98, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-02', shift:'Day Shift',   shiftType:'day',   timeIn:'06:15', timeOut:'14:00', hoursWorked:7.75, status:'late',    scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-03', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'18:00', hoursWorked:9.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-04', shift:'Day Shift',   shiftType:'day',   timeIn:'06:05', timeOut:'14:00', hoursWorked:7.92, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-05', shift:'Day Shift',   shiftType:'day',   timeIn:null,    timeOut:null,    hoursWorked:0,    status:'absent',  scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-08', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-09', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-10', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-11', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-12', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'13:30', hoursWorked:7.50, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-15', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-16', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-17', shift:'Day Shift',   shiftType:'day',   timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-18', shift:'Night Shift', shiftType:'night', timeIn:'22:00', timeOut:'06:00', hoursWorked:8.00, status:'present', scheduledStart:'22:00', scheduledEnd:'06:00', isAdditionalShift:true },
        ],
        'EMP002': [
            { date:'2025-12-01', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-02', shift:'Day Shift', shiftType:'day', timeIn:'06:20', timeOut:'14:00', hoursWorked:7.67, status:'late',    scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-03', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-04', shift:'Day Shift', shiftType:'day', timeIn:null,    timeOut:null,    hoursWorked:0,    status:'absent',  scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-05', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-08', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-09', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
        ],
        'EMP003': [
            { date:'2025-12-01', shift:'Night Shift', shiftType:'night', timeIn:'22:00', timeOut:'06:00', hoursWorked:8.00, status:'present', scheduledStart:'22:00', scheduledEnd:'06:00' },
            { date:'2025-12-02', shift:'Night Shift', shiftType:'night', timeIn:'22:15', timeOut:'06:00', hoursWorked:7.75, status:'late',    scheduledStart:'22:00', scheduledEnd:'06:00' },
            { date:'2025-12-03', shift:'Night Shift', shiftType:'night', timeIn:'22:00', timeOut:'06:00', hoursWorked:8.00, status:'present', scheduledStart:'22:00', scheduledEnd:'06:00' },
            { date:'2025-12-04', shift:'Night Shift', shiftType:'night', timeIn:'22:00', timeOut:'06:00', hoursWorked:8.00, status:'present', scheduledStart:'22:00', scheduledEnd:'06:00' },
            { date:'2025-12-08', shift:'Night Shift', shiftType:'night', timeIn:null,    timeOut:null,    hoursWorked:0,    status:'absent',  scheduledStart:'22:00', scheduledEnd:'06:00' },
            { date:'2025-12-09', shift:'Night Shift', shiftType:'night', timeIn:'22:00', timeOut:'06:00', hoursWorked:8.00, status:'present', scheduledStart:'22:00', scheduledEnd:'06:00' },
        ],
        'EMP004': [
            { date:'2025-12-01', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-02', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-03', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-04', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-05', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
        ],
        'EMP005': [
            { date:'2025-12-01', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-02', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'14:00', hoursWorked:8.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
            { date:'2025-12-03', shift:'Day Shift', shiftType:'day', timeIn:'06:00', timeOut:'12:00', hoursWorked:6.00, status:'present', scheduledStart:'06:00', scheduledEnd:'14:00' },
        ],
    };

    // Approved overtime requests
    const OVERTIME_REQUESTS = [
        { id:'OT001', employeeId:'EMP001', date:'2025-12-03', hours:1, estimatedPay: 703.125, status:'approved', type:'Regular OT' },
    ];

    // Active loan deductions per employee per cutoff
    const LOAN_DEDUCTIONS = {
        'EMP001': 2000,
        'EMP002': 1500,
    };

    // Philippine holidays Dec 2025
    const PH_HOLIDAYS = [
        { date:'2025-12-08', name:'Feast of Immaculate Conception', type:'special' },
        { date:'2025-12-24', name:'Christmas Eve', type:'special' },
        { date:'2025-12-25', name:'Christmas Day', type:'regular' },
        { date:'2025-12-30', name:'Rizal Day', type:'regular' },
        { date:'2025-12-31', name:'New Year\'s Eve', type:'special' },
    ];

    // Settings
    const GRACE_PERIOD_MINUTES = 10;
    const STD_WORKING_HOURS    = 8;
    const NIGHT_DIFF_RATE      = 0.10;   // 10% per DOLE Art.86
    const REG_OT_RATE          = 1.25;
    const REST_DAY_RATE        = 1.30;
    const REG_HOLIDAY_RATE     = 2.00;
    const SPEC_HOLIDAY_RATE    = 1.30;
    const FULL_MONTH_WORK_DAYS = 26;     // Standard PH semi-monthly base

    // ─── STATE ────────────────────────────────────────────────
    let state = {
        selectedEmployee : null,
        dtrMonth  : new Date().getMonth(),
        dtrYear   : new Date().getFullYear(),
        cutoff    : 'first-half',
        payroll   : null,
        releasedPeriods: {},  // { "EMP001|Dec 1-15, 2025": true }
    };

    // ─── HELPERS ──────────────────────────────────────────────
    const fmt = (n, dp=2) => n.toLocaleString('en-PH', { minimumFractionDigits:dp, maximumFractionDigits:dp });
    const fmtPeso = n => '₱' + fmt(n);

    function initials(fullName) {
        return fullName.split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase();
    }

    function cutoffDates(year, month, cutoff) {
        const m = month + 1;
        const pad = n => String(n).padStart(2,'0');
        const lastDay = new Date(year, month+1, 0).getDate();
        if (cutoff === 'first-half')  return { start:`${year}-${pad(m)}-01`,  end:`${year}-${pad(m)}-15` };
        if (cutoff === 'second-half') return { start:`${year}-${pad(m)}-16`,  end:`${year}-${pad(m)}-${lastDay}` };
        return { start:`${year}-${pad(m)}-01`, end:`${year}-${pad(m)}-${lastDay}` };
    }

    function isHoliday(dateStr) {
        return PH_HOLIDAYS.find(h => h.date === dateStr) || null;
    }

    function lateMinutes(timeIn, scheduledStart) {
        if (!timeIn || !scheduledStart) return 0;
        const [ih, im] = timeIn.split(':').map(Number);
        const [sh, sm] = scheduledStart.split(':').map(Number);
        let diff = (ih * 60 + im) - (sh * 60 + sm);
        if (diff < -720) diff += 1440;
        return Math.max(0, diff - GRACE_PERIOD_MINUTES);
    }

    function undertimeMinutes(timeOut, scheduledEnd) {
        if (!timeOut || !scheduledEnd) return 0;
        const [oh, om] = timeOut.split(':').map(Number);
        const [eh, em] = scheduledEnd.split(':').map(Number);
        let diff = (eh * 60 + em) - (oh * 60 + om);
        if (diff < -720) diff += 1440;
        return Math.max(0, diff);
    }

    // Strict DOLE Art.86 night diff hours (22:00–06:00)
    function nightDiffHours(timeIn, timeOut) {
        if (!timeIn || !timeOut) return 0;
        const parse = t => { const [h,m] = t.split(':').map(Number); return h*60+m; };
        let tin  = parse(timeIn);
        let tout = parse(timeOut);
        if (tout <= tin) tout += 1440;
        const ND_START = 22*60, ND_END = 6*60;
        let nd = 0;
        const evStart = Math.max(tin, ND_START);
        const evEnd   = Math.min(tout, 1440);
        if (evStart < evEnd && evStart >= ND_START) nd += evEnd - evStart;
        if (tout > 1440) {
            const mrEnd = Math.min(tout, 1440 + ND_END);
            nd += mrEnd - 1440;
        } else if (tin < ND_END) {
            nd += Math.min(tout, ND_END) - Math.max(tin, 0);
        }
        return Math.max(0, nd / 60);
    }

    // Government deductions (simplified tables)
    function govDeductions(monthlySalary) {
        // SSS (simplified bracket)
        let sssEE = Math.min(Math.max(monthlySalary * 0.045, 135), 900);
        // PhilHealth (5% split 50/50, cap monthly contribution)
        let phEE  = Math.min(monthlySalary * 0.025, 1250);
        // Pag-IBIG (2%, cap 100)
        let piEE  = Math.min(monthlySalary * 0.02, 100);
        // Withholding tax (simplified annual bracket)
        let annual = monthlySalary * 12;
        let tax = 0;
        if (annual > 8000000) tax = (annual - 8000000) * 0.35 + 2202500;
        else if (annual > 2000000) tax = (annual - 2000000) * 0.32 + 490000;
        else if (annual > 800000)  tax = (annual - 800000)  * 0.30 + 130000;
        else if (annual > 400000)  tax = (annual - 400000)  * 0.25 + 22500;
        else if (annual > 250000)  tax = (annual - 250000)  * 0.15;
        return { sss: sssEE, philhealth: phEE, pagibig: piEE, tax: tax / 24 }; // /24 for semi-monthly
    }

    // ─── CORE: Compute payroll ────────────────────────────────
    function computePayroll(emp, records) {
        const { start, end } = cutoffDates(state.dtrYear, state.dtrMonth, state.cutoff);
        const period = records.filter(r => r.date >= start && r.date <= end);

        // Working days in full month (Mon–Sat, Sun off)
        let fullMonthWorkDays = 0;
        for (let d=1; d <= new Date(state.dtrYear, state.dtrMonth+1, 0).getDate(); d++) {
            const dow = new Date(state.dtrYear, state.dtrMonth, d).getDay();
            if (dow !== 0) fullMonthWorkDays++;
        }
        fullMonthWorkDays = fullMonthWorkDays || FULL_MONTH_WORK_DAYS;

        // Cutoff working days
        let cutoffWorkDays = 0;
        {
            const s = new Date(start+'T00:00:00'), e = new Date(end+'T00:00:00');
            for (let d=new Date(s); d<=e; d.setDate(d.getDate()+1)) {
                if (d.getDay() !== 0) cutoffWorkDays++;
            }
        }

        const dailyRate  = emp.basicSalary / fullMonthWorkDays;
        const hourlyRate = dailyRate / STD_WORKING_HOURS;
        const minRate    = hourlyRate / 60;

        // Basic pay for this cutoff
        let basicPay = dailyRate * cutoffWorkDays;
        if (state.cutoff === 'full-month') basicPay = emp.basicSalary;

        // Aggregate per-record metrics
        let lateMin=0, utMin=0, absentDays=0, ndHrs=0, otHrs=0;
        let addShiftBasePay=0, addShiftND=0, addShiftCount=0;
        let restDayPay=0;
        let holidayPay=0;

        period.forEach(r => {
            if (r.status === 'absent') { absentDays++; return; }
            const late = lateMinutes(r.timeIn, r.scheduledStart);
            const ut   = undertimeMinutes(r.timeOut, r.scheduledEnd);
            const ot   = Math.max(0, r.hoursWorked - STD_WORKING_HOURS);
            const holiday = isHoliday(r.date);

            if (r.isAdditionalShift) {
                // Additional shift: full hours × hourly rate
                addShiftBasePay += r.hoursWorked * hourlyRate;
                if (r.shiftType === 'night') addShiftND += nightDiffHours(r.timeIn, r.timeOut) * hourlyRate * NIGHT_DIFF_RATE;
                addShiftCount++;
            } else {
                lateMin += late;
                utMin   += ut;
                if (r.shiftType === 'night') ndHrs += nightDiffHours(r.timeIn, r.timeOut);

                // Holiday pay premium
                if (holiday && r.timeIn) {
                    const hrs = Math.min(r.hoursWorked, STD_WORKING_HOURS);
                    if (holiday.type === 'regular')  holidayPay += hrs * hourlyRate * 1.00; // +100%
                    if (holiday.type === 'special')  holidayPay += hrs * hourlyRate * 0.30; // +30%
                }

                // OT only tracked for approved requests below
                otHrs += ot;
            }
        });

        // Approved OT pay
        let otPay = 0, approvedOTHrs = 0;
        OVERTIME_REQUESTS.filter(o =>
            o.employeeId === emp.id &&
            (o.status === 'approved' || o.status === 'paid') &&
            o.date >= start && o.date <= end
        ).forEach(o => { otPay += o.estimatedPay; approvedOTHrs += o.hours; });

        const nightDiffPay  = ndHrs * hourlyRate * NIGHT_DIFF_RATE;
        const lateDeduction = lateMin  * minRate;
        const utDeduction   = utMin    * minRate;
        const absentDeduct  = absentDays * dailyRate;
        const loanDeduct    = LOAN_DEDUCTIONS[emp.id] || 0;
        const addShiftPay   = addShiftBasePay + addShiftND;

        const grossPay = basicPay + otPay + nightDiffPay + holidayPay + restDayPay + addShiftPay;

        // Gov deductions (prorated)
        const actualDays = period.filter(r => r.timeIn).length;
        const expectedDays = cutoffWorkDays;
        const proration = expectedDays > 0 ? Math.min(actualDays / expectedDays, 1) : 1;
        const gov = govDeductions(emp.basicSalary);
        const sss      = gov.sss      * (state.cutoff === 'full-month' ? 1 : 0.5) * proration;
        const ph        = gov.philhealth * (state.cutoff === 'full-month' ? 1 : 0.5) * proration;
        const pi        = gov.pagibig  * (state.cutoff === 'full-month' ? 1 : 0.5) * proration;
        const tax       = gov.tax      * (state.cutoff === 'full-month' ? 2 : 1)   * proration;

        const totalGov  = sss + ph + pi + tax;
        const totalDeductions = lateDeduction + utDeduction + absentDeduct + totalGov + loanDeduct;
        const netPay    = Math.round(grossPay - totalDeductions);

        return {
            dailyRate, hourlyRate, basicPay, grossPay, netPay,
            otPay, approvedOTHrs, otHrs,
            nightDiffPay, ndHrs,
            holidayPay, restDayPay,
            addShiftPay, addShiftBasePay, addShiftND, addShiftCount,
            lateDeduction, lateMin,
            utDeduction, utMin,
            absentDeduct, absentDays,
            sss, ph, pi, tax, totalGov,
            loanDeduct, totalDeductions,
            period,
        };
    }

    // ─── RENDER: Employee list ────────────────────────────────
    function renderEmployeeList(list) {
        const el = document.getElementById('employee-list');
        el.innerHTML = list.map(emp => `
            <div class="list-group-item list-group-item-action employee-item mb-1 rounded border px-3 py-2 d-flex align-items-center gap-2
                        ${state.selectedEmployee?.id === emp.id ? 'active' : ''}"
                 data-id="${emp.id}" role="button">
                <div class="employee-avatar">${initials(emp.fullName)}</div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate small">${emp.fullName}</div>
                    <div class="text-muted" style="font-size:.72rem">${emp.position}</div>
                    <div class="text-muted" style="font-size:.68rem">${emp.department}</div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        `).join('');

        el.querySelectorAll('.employee-item').forEach(item => {
            item.addEventListener('click', () => selectEmployee(item.dataset.id));
        });
    }

    // ─── RENDER: DTR table ────────────────────────────────────
    function renderDTR(records) {
        const { start, end } = cutoffDates(state.dtrYear, state.dtrMonth, state.cutoff);
        const period = records.filter(r => r.date >= start && r.date <= end);

        document.getElementById('dtr-record-count').textContent = period.length + ' records';

        const daysPresent = new Set(period.filter(r => r.timeIn).map(r => r.date)).size;
        let workDays = 0;
        { const s = new Date(start+'T00:00:00'), e = new Date(end+'T00:00:00');
          for (let d=new Date(s); d<=e; d.setDate(d.getDate()+1)) if (d.getDay()!==0) workDays++; }

        document.getElementById('days-present-stat').textContent = daysPresent;
        document.getElementById('working-days-stat').textContent  = workDays;

        if (!period.length) {
            document.getElementById('dtr-records').innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>No records for this period
                </div>`;
            return;
        }

        const rows = period.map(r => {
            const holiday = isHoliday(r.date);
            const late    = r.timeIn ? lateMinutes(r.timeIn, r.scheduledStart) : 0;
            const ut      = r.timeOut ? undertimeMinutes(r.timeOut, r.scheduledEnd) : 0;
            const isNight = r.shiftType === 'night';
            const rowClass = !r.timeIn ? 'is-absent' : late > 0 ? 'has-late' : '';

            const shiftBadge = `<span class="badge ${isNight ? 'badge-shift-night' : 'badge-shift-day'} fw-normal">
                ${r.shift.replace(' Shift','')}</span>`;
            const addBadge   = r.isAdditionalShift ? `<span class="badge bg-secondary-subtle text-secondary-emphasis border fw-normal ms-1">+Shift</span>` : '';
            const holBadge   = holiday ? `<span class="badge bg-secondary-subtle text-secondary-emphasis border fw-normal ms-1">${holiday.name}</span>` : '';
            const lateBadge  = late > 0 ? `<span class="badge bg-warning-subtle text-warning-emphasis border fw-normal ms-1">Late ${late}m</span>` : '';
            const utBadge    = ut > 0   ? `<span class="badge bg-danger-subtle  text-danger-emphasis  border fw-normal ms-1">UT ${ut}m</span>` : '';
            const absLabel   = !r.timeIn ? `<span class="badge bg-secondary fw-normal">Absent</span>` : '';

            return `
            <div class="dtr-record ${rowClass} px-3 py-2 border-bottom d-flex align-items-start gap-2">
                <div style="min-width:85px">
                    <div class="fw-semibold small">${r.date}</div>
                    <div style="font-size:.7rem" class="text-muted">${new Date(r.date+'T12:00').toLocaleDateString('en-US',{weekday:'short'})}</div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-1 mb-1">
                        ${shiftBadge}${addBadge}${holBadge}${lateBadge}${utBadge}${absLabel}
                    </div>
                    <div class="d-flex gap-4" style="font-size:.76rem">
                        <span class="text-muted">In: <strong class="text-body">${r.timeIn ?? '—'}</strong>
                            <span class="text-muted">(${r.scheduledStart})</span></span>
                        <span class="text-muted">Out: <strong class="text-body">${r.timeOut ?? '—'}</strong>
                            <span class="text-muted">(${r.scheduledEnd})</span></span>
                        <span class="text-muted">Hrs: <strong class="text-body">${r.hoursWorked.toFixed(2)}</strong></span>
                    </div>
                </div>
            </div>`;
        }).join('');

        document.getElementById('dtr-records').innerHTML = rows;
    }

    // ─── RENDER: Payroll computation ─────────────────────────
    function renderPayroll(p) {
        const earnings = [
            { label:'Basic Pay',          value: p.basicPay,     sub:null },
            { label:'Overtime Pay',       value: p.otPay,        sub: p.approvedOTHrs > 0 ? `${p.approvedOTHrs}h approved` : (p.otHrs > 0 ? 'Pending approval' : null) },
            { label:'Night Differential', value: p.nightDiffPay, sub: p.ndHrs > 0 ? `${p.ndHrs.toFixed(2)}h × 10%` : null },
            { label:'Holiday Pay',        value: p.holidayPay,   sub: null },
            { label:'Additional Shift',   value: p.addShiftPay,  sub: p.addShiftCount > 0 ? `${p.addShiftCount} shift(s)` : null },
            { label:'Rest Day Pay',       value: p.restDayPay,   sub: null },
        ].filter(e => e.value > 0);

        const deductions = [
            { label:'Late Deductions',     value: p.lateDeduction,  sub: p.lateMin > 0 ? `${p.lateMin}m × ₱${fmt(p.hourlyRate/60)}/min` : null },
            { label:'Undertime',           value: p.utDeduction,    sub: p.utMin > 0 ? `${p.utMin}m` : null },
            { label:'Absent Deductions',   value: p.absentDeduct,   sub: p.absentDays > 0 ? `${p.absentDays} day(s) × ₱${fmt(p.dailyRate)}/day` : null },
            { label:'SSS',                 value: p.sss,            sub: null },
            { label:'PhilHealth',          value: p.ph,             sub: null },
            { label:'Pag-IBIG',            value: p.pi,             sub: null },
            { label:'Withholding Tax',     value: p.tax,            sub: null },
            { label:'Loan Deductions',     value: p.loanDeduct,     sub: null },
        ].filter(d => d.value > 0);

        const makeRows = (items, isDeduction=false) => items.map(item => `
            <div class="payroll-row">
                <div>
                    <div class="small">${item.label}</div>
                    ${item.sub ? `<div style="font-size:.7rem" class="text-muted">${item.sub}</div>` : ''}
                </div>
                <div class="fw-semibold small ${isDeduction ? 'text-danger' : ''}">${isDeduction ? '−' : ''}${fmtPeso(item.value)}</div>
            </div>`).join('') || `<div class="text-muted small px-2 py-2">None</div>`;

        document.getElementById('earnings-breakdown').innerHTML    = makeRows(earnings, false);
        document.getElementById('deductions-breakdown').innerHTML  = makeRows(deductions, true);

        document.getElementById('payroll-summary').innerHTML = `
            <div class="col-md-4">
                <div class="stat-mini text-center py-2">
                    <div class="payroll-label">Gross Pay</div>
                    <div class="fw-bold fs-6 text-primary">${fmtPeso(p.grossPay)}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-mini text-center py-2">
                    <div class="payroll-label">Total Deductions</div>
                    <div class="fw-bold fs-6 text-danger">−${fmtPeso(p.totalDeductions)}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="net-pay-box text-center py-2 px-3">
                    <div class="payroll-label">Net Pay</div>
                    <div class="fw-bold fs-5 text-primary">${fmtPeso(p.netPay)}</div>
                </div>
            </div>`;

        document.getElementById('card-payroll').style.display = '';
    }

    // ─── RENDER: Payslip print modal ─────────────────────────
    function renderPayslip(emp, p) {
        const { start, end } = cutoffDates(state.dtrYear, state.dtrMonth, state.cutoff);
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const periodLabel = `${months[state.dtrMonth]} ${start.slice(8)} – ${end.slice(8)}, ${state.dtrYear}`;

        document.getElementById('payslip-print-area').innerHTML = `
            <div style="font-family:Arial,sans-serif;font-size:13px;max-width:640px;margin:auto">
                <div class="text-center mb-3">
                    <h5 class="mb-0 fw-bold">PAYSLIP</h5>
                    <div class="text-muted small">Period: ${periodLabel}</div>
                </div>
                <table class="table table-sm table-bordered mb-3">
                    <tr><td class="fw-semibold">Employee</td><td>${emp.fullName}</td>
                        <td class="fw-semibold">ID</td><td>${emp.id}</td></tr>
                    <tr><td class="fw-semibold">Position</td><td>${emp.position}</td>
                        <td class="fw-semibold">Department</td><td>${emp.department}</td></tr>
                </table>
                <div class="row g-3">
                    <div class="col-6">
                        <table class="table table-sm">
                            <thead><tr><th colspan="2" class="text-muted small fw-normal text-uppercase">Earnings</th></tr></thead>
                            <tbody>
                                <tr><td>Basic Pay</td><td class="text-end">${fmtPeso(p.basicPay)}</td></tr>
                                ${p.otPay > 0 ? `<tr><td>Overtime</td><td class="text-end">${fmtPeso(p.otPay)}</td></tr>` : ''}
                                ${p.nightDiffPay > 0 ? `<tr><td>Night Diff</td><td class="text-end">${fmtPeso(p.nightDiffPay)}</td></tr>` : ''}
                                ${p.holidayPay > 0 ? `<tr><td>Holiday Pay</td><td class="text-end">${fmtPeso(p.holidayPay)}</td></tr>` : ''}
                                ${p.addShiftPay > 0 ? `<tr><td>Additional Shift</td><td class="text-end">${fmtPeso(p.addShiftPay)}</td></tr>` : ''}
                                <tr class="fw-bold"><td>Gross Pay</td><td class="text-end">${fmtPeso(p.grossPay)}</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-sm">
                            <thead><tr><th colspan="2" class="text-muted small fw-normal text-uppercase">Deductions</th></tr></thead>
                            <tbody>
                                ${p.sss > 0 ? `<tr><td>SSS</td><td class="text-end">${fmtPeso(p.sss)}</td></tr>` : ''}
                                ${p.ph > 0 ? `<tr><td>PhilHealth</td><td class="text-end">${fmtPeso(p.ph)}</td></tr>` : ''}
                                ${p.pi > 0 ? `<tr><td>Pag-IBIG</td><td class="text-end">${fmtPeso(p.pi)}</td></tr>` : ''}
                                ${p.tax > 0 ? `<tr><td>Withholding Tax</td><td class="text-end">${fmtPeso(p.tax)}</td></tr>` : ''}
                                ${p.loanDeduct > 0 ? `<tr><td>Loan</td><td class="text-end">${fmtPeso(p.loanDeduct)}</td></tr>` : ''}
                                ${p.lateDeduction > 0 ? `<tr><td>Late</td><td class="text-end">−${fmtPeso(p.lateDeduction)}</td></tr>` : ''}
                                ${p.utDeduction > 0 ? `<tr><td>Undertime</td><td class="text-end">−${fmtPeso(p.utDeduction)}</td></tr>` : ''}
                                ${p.absentDeduct > 0 ? `<tr><td>Absent</td><td class="text-end">−${fmtPeso(p.absentDeduct)}</td></tr>` : ''}
                                <tr class="fw-bold"><td>Total Deductions</td><td class="text-end">${fmtPeso(p.totalDeductions)}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-end border-top pt-2 mt-1">
                    <span class="fw-bold fs-5">NET PAY: ${fmtPeso(p.netPay)}</span>
                </div>
                <div class="mt-4 row g-4 text-center" style="font-size:.78rem">
                    <div class="col-4"><div class="border-top pt-1">Prepared by</div></div>
                    <div class="col-4"><div class="border-top pt-1">Checked by</div></div>
                    <div class="col-4"><div class="border-top pt-1">Received by</div></div>
                </div>
            </div>`;
    }

    // ─── ACTION: Select employee ──────────────────────────────
    function selectEmployee(id) {
        state.selectedEmployee = EMPLOYEES.find(e => e.id === id);
        if (!state.selectedEmployee) return;
        const emp = state.selectedEmployee;

        // Show detail panel
        document.getElementById('panel-detail').style.display = '';
        document.getElementById('btn-close-detail').classList.remove('d-none');

        // Recalculate daily/hourly using current month working days
        const now = new Date();
        let fmwd = 0;
        for (let d=1; d<=new Date(now.getFullYear(), now.getMonth()+1, 0).getDate(); d++) {
            if (new Date(now.getFullYear(), now.getMonth(), d).getDay() !== 0) fmwd++;
        }
        const dr = emp.basicSalary / fmwd;
        const hr = dr / STD_WORKING_HOURS;

        // Populate header
        document.getElementById('detail-avatar').textContent   = initials(emp.fullName);
        document.getElementById('detail-name').textContent     = emp.fullName;
        document.getElementById('detail-position').textContent = emp.position;
        document.getElementById('detail-dept').textContent     = emp.department + ' · ' + emp.employmentStatus;
        document.getElementById('detail-salary').textContent   = fmtPeso(emp.basicSalary);
        document.getElementById('detail-id').textContent       = emp.id;
        document.getElementById('detail-daily-rate').textContent  = fmtPeso(dr);
        document.getElementById('detail-hourly-rate').textContent = fmtPeso(hr);

        // Header actions visible
        document.getElementById('header-actions').style.cssText = '';

        // Re-render employee list to highlight active
        renderFilteredList();

        // Load DTR + payroll
        loadDTR();
    }

    function loadDTR() {
        if (!state.selectedEmployee) return;
        const emp     = state.selectedEmployee;
        const records = ATTENDANCE[emp.id] || [];

        renderDTR(records);

        const p = computePayroll(emp, records);
        state.payroll = p;
        renderPayroll(p);

        // Release status
        const periodKey = releasedKey();
        const released  = state.releasedPeriods[periodKey];
        const badge = document.getElementById('payslip-status-badge');
        badge.textContent = released ? 'Released' : 'Not Released';
        badge.className   = released
            ? 'badge bg-primary small'
            : 'badge bg-secondary small';
    }

    function releasedKey() {
        if (!state.selectedEmployee) return '';
        const { start, end } = cutoffDates(state.dtrYear, state.dtrMonth, state.cutoff);
        return `${state.selectedEmployee.id}|${start}|${end}`;
    }

    // ─── FILTER & SEARCH ─────────────────────────────────────
    function renderFilteredList() {
        const q    = document.getElementById('emp-search').value.toLowerCase();
        const dept = document.getElementById('dept-filter').value;
        const list = EMPLOYEES.filter(e => {
            const matchQ = !q || e.fullName.toLowerCase().includes(q)
                              || e.position.toLowerCase().includes(q)
                              || e.email.toLowerCase().includes(q);
            const matchD = dept === 'all' || e.department === dept;
            return matchQ && matchD;
        });
        document.getElementById('emp-count').textContent = list.length;
        renderEmployeeList(list);
    }

    // ─── INIT ────────────────────────────────────────────────
    function init() {
        // Populate month dropdown
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const mSel = document.getElementById('dtr-month');
        months.forEach((m, i) => {
            const o = document.createElement('option');
            o.value = i; o.textContent = m;
            if (i === state.dtrMonth) o.selected = true;
            mSel.appendChild(o);
        });

        // Year dropdown (last 3 years)
        const ySel = document.getElementById('dtr-year');
        const cy   = new Date().getFullYear();
        for (let y = cy; y >= cy-2; y--) {
            const o = document.createElement('option');
            o.value = y; o.textContent = y;
            if (y === state.dtrYear) o.selected = true;
            ySel.appendChild(o);
        }

        // Department filter
        const depts = [...new Set(EMPLOYEES.map(e => e.department))].sort();
        const dSel  = document.getElementById('dept-filter');
        depts.forEach(d => {
            const o = document.createElement('option'); o.value = d; o.textContent = d;
            dSel.appendChild(o);
        });

        // Initial list render
        document.getElementById('emp-count').textContent = EMPLOYEES.length;
        renderFilteredList();

        // Event bindings
        document.getElementById('emp-search').addEventListener('input', renderFilteredList);
        document.getElementById('dept-filter').addEventListener('change', renderFilteredList);

        mSel.addEventListener('change', () => { state.dtrMonth = +mSel.value; loadDTR(); });
        ySel.addEventListener('change', () => { state.dtrYear  = +ySel.value; loadDTR(); });
        document.getElementById('cutoff-period').addEventListener('change', e => {
            state.cutoff = e.target.value; loadDTR();
        });

        // Close detail
        document.getElementById('btn-close-detail').addEventListener('click', () => {
            state.selectedEmployee = null;
            document.getElementById('panel-detail').style.display = 'none';
            document.getElementById('btn-close-detail').classList.add('d-none');
            document.getElementById('header-actions').style.cssText = 'display:none!important';
            document.getElementById('card-payroll').style.display = 'none';
            renderFilteredList();
        });

        // Release payslip
        ['btn-release-payslip','btn-release-payslip-2'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', () => {
                if (!state.selectedEmployee || !state.payroll) return;
                const key = releasedKey();
                state.releasedPeriods[key] = true;
                const badge = document.getElementById('payslip-status-badge');
                badge.textContent = 'Released';
                badge.className   = 'badge bg-primary small';
                Swal.fire({ icon:'success', title:'Payslip Released', text:`${state.selectedEmployee.fullName}'s payslip has been released.`, confirmButtonColor:'var(--bs-primary)' });
            });
        });

        // Print payslip
        ['btn-print-payslip','btn-print-payslip-2'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', () => {
                if (!state.selectedEmployee || !state.payroll) return;
                renderPayslip(state.selectedEmployee, state.payroll);
                new bootstrap.Modal(document.getElementById('modal-payslip')).show();
            });
        });
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', () => PayrollModule.init());
</script>
@endpush