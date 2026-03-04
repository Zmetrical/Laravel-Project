@extends('layouts.main')

@section('title', 'Overtime Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Overtime Management</li>
    </ol>
@endsection

@push('styles')
<style>
    .ot-calendar-cell {
        min-height: 56px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 4px;
        cursor: default;
        transition: background-color .15s;
        position: relative;
        font-size: .75rem;
    }
    .ot-calendar-cell.eligible {
        background-color: rgba(var(--bs-primary-rgb), .07);
        border-color: rgba(var(--bs-primary-rgb), .35);
        cursor: pointer;
    }
    .ot-calendar-cell.eligible:hover {
        background-color: rgba(var(--bs-primary-rgb), .15);
    }
    .ot-calendar-cell.selected {
        background-color: rgba(var(--bs-primary-rgb), .2);
        border-color: var(--bs-primary);
    }
    .ot-calendar-cell.requested {
        background-color: rgba(var(--bs-secondary-rgb), .08);
        border-color: rgba(var(--bs-secondary-rgb), .3);
        cursor: not-allowed;
        opacity: .6;
    }
    .ot-calendar-cell.inactive {
        opacity: .25;
        cursor: default;
    }
    .ot-day-number {
        font-weight: 600;
        line-height: 1;
        color: #6c757d;
    }
    .ot-calendar-cell.eligible .ot-day-number,
    .ot-calendar-cell.selected .ot-day-number {
        color: var(--bs-primary);
    }
    .ot-hours-badge {
        font-size: .65rem;
        margin-top: 3px;
        display: block;
        white-space: nowrap;
    }
    .calendar-weekday {
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        text-align: center;
        padding: 4px 2px;
    }
    .ot-indicator {
        font-size: .6rem;
        line-height: 1;
    }
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Overtime Management</h4>
        <small class="text-muted">File overtime requests and track your OT hours</small>
    </div>
    <button class="btn btn-secondary" id="toggleFormBtn" onclick="toggleForm()">
        <i class="bi bi-plus-lg me-1"></i> File OT Request
    </button>
</div>

{{-- Summary stats --}}
<div class="row g-3 mb-3" id="statsRow"></div>

{{-- OT Request Form --}}
<div class="card mb-3 d-none" id="otFormCard">
    <div class="card-header">
        <h5 class="card-title mb-0">New Overtime Request</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">

            {{-- Left: Calendar --}}
            <div class="col-lg-6">
                <label class="form-label fw-semibold">Select OT Date</label>

                {{-- Month navigation --}}
                <div class="d-flex align-items-center justify-content-between mb-2 border rounded px-3 py-2 bg-light">
                    <button class="btn btn-sm btn-outline-secondary" onclick="prevMonth()">&#8592;</button>
                    <span class="fw-semibold" id="calMonthLabel"></span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="nextMonth()">&#8594;</button>
                </div>

                {{-- Weekday headers --}}
                <div class="row g-1 mb-1">
                    <div class="col calendar-weekday">Sun</div>
                    <div class="col calendar-weekday">Mon</div>
                    <div class="col calendar-weekday">Tue</div>
                    <div class="col calendar-weekday">Wed</div>
                    <div class="col calendar-weekday">Thu</div>
                    <div class="col calendar-weekday">Fri</div>
                    <div class="col calendar-weekday">Sat</div>
                </div>

                {{-- Calendar grid --}}
                <div id="calGrid"></div>

                {{-- Selected date info --}}
                <div class="mt-2" id="selectedDateInfo"></div>

                {{-- Legend --}}
                <div class="mt-2 p-2 border rounded bg-light">
                    <div class="row g-1">
                        <div class="col-6 d-flex align-items-center gap-1" style="font-size:.7rem">
                            <div style="width:12px;height:12px;border-radius:3px;background:rgba(var(--bs-primary-rgb),.12);border:1px solid rgba(var(--bs-primary-rgb),.35)"></div>
                            <span class="text-muted">Has Overtime</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-1" style="font-size:.7rem">
                            <div style="width:12px;height:12px;border-radius:3px;background:rgba(var(--bs-primary-rgb),.2);border:1px solid var(--bs-primary)"></div>
                            <span class="text-muted">Selected</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-1" style="font-size:.7rem">
                            <span class="text-primary fw-bold">H</span>
                            <span class="text-muted">Holiday</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-1" style="font-size:.7rem">
                            <span class="text-secondary fw-bold">N</span>
                            <span class="text-muted">Night Shift</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: OT Type + Reason --}}
            <div class="col-lg-6">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        OT Type
                        <span class="badge bg-primary ms-1" style="font-size:.65rem">Auto-Detected</span>
                    </label>
                    <div class="border rounded p-3 bg-light" id="otTypeDisplay">
                        <span class="text-muted small">Select a date to auto-detect OT type</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Reason <span class="text-muted fw-normal">(Optional)</span>
                    </label>
                    <textarea class="form-control" id="otReason" rows="4"
                        placeholder="Brief reason for overtime..."></textarea>
                </div>

                <div class="mb-3 d-none" id="estimatedPayBox">
                    <div class="border rounded p-3 bg-light">
                        <p class="mb-1 small fw-semibold">Estimated OT Pay</p>
                        <p class="mb-0 fs-5 fw-bold" id="estimatedPayAmt">₱0.00</p>
                        <p class="mb-0 text-muted small" id="estimatedPayFormula"></p>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" id="submitOTBtn"
                        onclick="submitOTRequest()" disabled>
                        Submit Request
                    </button>
                    <button class="btn btn-outline-secondary px-4" onclick="toggleForm()">
                        Cancel
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- History Filters + Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Overtime Request History</h5>
        <small class="text-muted" id="historyCount"></small>
    </div>
    <div class="card-body pb-2">

        {{-- Filters --}}
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label small">Date From</label>
                <input type="date" class="form-control form-control-sm" id="filterFrom"
                    oninput="renderHistory()">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Date To</label>
                <input type="date" class="form-control form-control-sm" id="filterTo"
                    oninput="renderHistory()">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select class="form-select form-select-sm" id="filterStatus" onchange="renderHistory()">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="supervisor-approved">Supervisor Approved</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-outline-secondary btn-sm w-100"
                    onclick="clearFilters()">Clear Filters</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Hours</th>
                        <th>OT Type</th>
                        <th>Rate</th>
                        <th>Est. Pay</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Reviewed By</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody"></tbody>
            </table>
        </div>

        <div class="text-center text-muted py-5 d-none" id="emptyState">
            <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-25"></i>
            No overtime requests yet. Click &quot;File OT Request&quot; to get started.
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
/* =====================================================================
   SAMPLE DATA
   ===================================================================== */

const HOURLY_RATE = 92.59; // Based on ₱20,000 / 26 days / 8 hrs

const OVERTIME_RATES = [
    { name: 'Regular Overtime',                         multiplier: 1.25  },
    { name: 'Regular Overtime + Night Shift',           multiplier: 1.375 },
    { name: 'Rest Day Overtime',                        multiplier: 1.69  },
    { name: 'Rest Day Overtime + Night Shift',          multiplier: 1.859 },
    { name: 'Special Holiday Overtime',                 multiplier: 1.69  },
    { name: 'Special Holiday Overtime on Rest Day',     multiplier: 1.95  },
    { name: 'Regular Holiday Overtime',                 multiplier: 2.60  },
    { name: 'Regular Holiday Overtime on Rest Day',     multiplier: 3.38  },
];

const PH_HOLIDAYS_2025 = [
    { date: '2025-01-01', name: "New Year's Day",          type: 'regular'  },
    { date: '2025-04-09', name: 'Araw ng Kagitingan',      type: 'regular'  },
    { date: '2025-04-17', name: 'Maundy Thursday',         type: 'regular'  },
    { date: '2025-04-18', name: 'Good Friday',             type: 'regular'  },
    { date: '2025-05-01', name: 'Labor Day',               type: 'regular'  },
    { date: '2025-06-12', name: 'Independence Day',        type: 'regular'  },
    { date: '2025-08-25', name: 'National Heroes Day',     type: 'regular'  },
    { date: '2025-11-30', name: 'Bonifacio Day',           type: 'regular'  },
    { date: '2025-12-25', name: 'Christmas Day',           type: 'regular'  },
    { date: '2025-12-30', name: 'Rizal Day',               type: 'regular'  },
    { date: '2025-02-25', name: 'EDSA Revolution',         type: 'special'  },
    { date: '2025-08-21', name: 'Ninoy Aquino Day',        type: 'special'  },
    { date: '2025-11-01', name: "All Saints' Day",         type: 'special'  },
    { date: '2025-12-08', name: 'Immaculate Conception',   type: 'special'  },
    { date: '2025-12-24', name: 'Christmas Eve',           type: 'special'  },
    { date: '2025-12-31', name: "New Year's Eve",          type: 'special'  },
];

// Attendance records — dates the employee worked overtime
const ATTENDANCE_RECORDS = [
    { date: '2025-01-06', hoursWorked: 10.5, shift: 'Day',   status: 'overtime' },
    { date: '2025-01-10', hoursWorked: 9.0,  shift: 'Night', status: 'overtime' },
    { date: '2025-01-14', hoursWorked: 11.0, shift: 'Day',   status: 'overtime' },
    { date: '2025-01-20', hoursWorked: 9.5,  shift: 'Day',   status: 'overtime' },
    { date: '2025-01-25', hoursWorked: 10.0, shift: 'Night', status: 'overtime' },
    { date: '2025-02-03', hoursWorked: 9.0,  shift: 'Day',   status: 'overtime' },
    { date: '2025-02-10', hoursWorked: 10.0, shift: 'Day',   status: 'overtime' },
    { date: '2025-02-17', hoursWorked: 9.5,  shift: 'Night', status: 'overtime' },
    { date: '2025-02-24', hoursWorked: 8.5,  shift: 'Day',   status: 'overtime' }, // Special holiday (EDSA)
    { date: '2025-02-25', hoursWorked: 10.0, shift: 'Day',   status: 'overtime' },
    { date: '2025-03-03', hoursWorked: 9.0,  shift: 'Day',   status: 'overtime' },
    { date: '2025-03-08', hoursWorked: 11.5, shift: 'Night', status: 'overtime' },
    { date: '2025-03-15', hoursWorked: 9.0,  shift: 'Day',   status: 'overtime' },
];

// Day-off config — Sundays are off by default
const DAY_OFF = ['Sunday'];

// Pre-existing OT requests (with various statuses)
let otHistory = [
    {
        id: 'OT-2025-001',
        date: '2025-01-06',
        hours: 2.5,
        type: 'Regular Overtime',
        reason: 'Urgent project delivery',
        status: 'paid',
        rate: 1.25,
        estimatedPay: 289.34,
        submittedDate: '2025-01-07',
        reviewedBy: 'Maria Santos',
    },
    {
        id: 'OT-2025-002',
        date: '2025-01-10',
        hours: 1.0,
        type: 'Regular Overtime + Night Shift',
        reason: 'System maintenance',
        status: 'approved',
        rate: 1.375,
        estimatedPay: 127.31,
        submittedDate: '2025-01-11',
        reviewedBy: 'Jose Reyes',
    },
    {
        id: 'OT-2025-003',
        date: '2025-01-14',
        hours: 3.0,
        type: 'Regular Overtime',
        reason: 'Month-end reporting',
        status: 'approved',
        rate: 1.25,
        estimatedPay: 347.21,
        submittedDate: '2025-01-15',
        reviewedBy: 'Maria Santos',
    },
    {
        id: 'OT-2025-004',
        date: '2025-01-20',
        hours: 1.5,
        type: 'Regular Overtime',
        reason: '',
        status: 'rejected',
        rate: 1.25,
        estimatedPay: 173.61,
        submittedDate: '2025-01-21',
        reviewedBy: 'Jose Reyes',
    },
    {
        id: 'OT-2025-005',
        date: '2025-02-03',
        hours: 1.0,
        type: 'Regular Overtime',
        reason: 'Backlog clearance',
        status: 'supervisor-approved',
        rate: 1.25,
        estimatedPay: 115.74,
        submittedDate: '2025-02-04',
        reviewedBy: '',
    },
    {
        id: 'OT-2025-006',
        date: '2025-02-10',
        hours: 2.0,
        type: 'Regular Overtime',
        reason: 'Client requirements',
        status: 'pending',
        rate: 1.25,
        estimatedPay: 231.48,
        submittedDate: '2025-02-11',
        reviewedBy: '',
    },
    {
        id: 'OT-2025-007',
        date: '2025-02-25',
        hours: 2.0,
        type: 'Special Holiday Overtime',
        reason: 'Critical deployment',
        status: 'pending',
        rate: 1.69,
        estimatedPay: 312.96,
        submittedDate: '2025-02-26',
        reviewedBy: '',
    },
];

/* =====================================================================
   STATE
   ===================================================================== */

let calMonth   = new Date(2025, 2, 1); // March 2025
let selectedDate = null;
let detectedOTType = null;
let formVisible = false;

/* =====================================================================
   HELPERS
   ===================================================================== */

function pad(n) { return String(n).padStart(2, '0'); }

function toYMD(d) {
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

function getHoliday(dateStr) {
    return PH_HOLIDAYS_2025.find(h => h.date === dateStr) || null;
}

function getAttendance(dateStr) {
    return ATTENDANCE_RECORDS.find(a => a.date === dateStr) || null;
}

function hasExistingRequest(dateStr) {
    return otHistory.some(o => o.date === dateStr);
}

function getDayName(dateStr) {
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    return days[new Date(dateStr).getDay()];
}

function isRestDay(dateStr) {
    return DAY_OFF.includes(getDayName(dateStr));
}

function getMultiplier(typeName) {
    const r = OVERTIME_RATES.find(r => r.name === typeName);
    return r ? r.multiplier : 1.25;
}

function autoDetectOTType(dateStr) {
    const att    = getAttendance(dateStr);
    const hol    = getHoliday(dateStr);
    const rest   = isRestDay(dateStr);
    const night  = att && att.shift && att.shift.toLowerCase() === 'night';

    let base = 'Regular Overtime';

    if (hol && rest) {
        base = hol.type === 'regular'
            ? 'Regular Holiday Overtime on Rest Day'
            : 'Special Holiday Overtime on Rest Day';
    } else if (hol) {
        base = hol.type === 'regular'
            ? 'Regular Holiday Overtime'
            : 'Special Holiday Overtime';
    } else if (rest) {
        base = 'Rest Day Overtime';
    }

    // Night shift modifier (only for non-compound holiday scenarios)
    const noNight = ['Regular Holiday Overtime', 'Special Holiday Overtime',
                     'Regular Holiday Overtime on Rest Day', 'Special Holiday Overtime on Rest Day'];
    if (night && !noNight.includes(base)) {
        base += ' + Night Shift';
    }

    return base;
}

function otTypeDescription(typeName) {
    const r = OVERTIME_RATES.find(r => r.name === typeName);
    if (!r) return '';
    switch (typeName) {
        case 'Regular Overtime':                      return `Regular OT rate (1.25×)`;
        case 'Regular Overtime + Night Shift':        return `OT (1.25) × Night Shift (1.10) = 1.375×`;
        case 'Rest Day Overtime':                     return `Rest Day (1.30) × OT (1.30) = 1.69×`;
        case 'Rest Day Overtime + Night Shift':       return `Rest Day OT (1.69) × Night (1.10) = 1.859×`;
        case 'Special Holiday Overtime':              return `Special Holiday (1.30) × OT (1.30) = 1.69×`;
        case 'Special Holiday Overtime on Rest Day':  return `Special+Rest (1.50) × OT (1.30) = 1.95×`;
        case 'Regular Holiday Overtime':              return `Regular Holiday (2.00) × OT (1.30) = 2.60×`;
        case 'Regular Holiday Overtime on Rest Day':  return `Regular+Rest (2.60) × OT (1.30) = 3.38×`;
        default: return '';
    }
}

function formatCurrency(n) {
    return '₱' + parseFloat(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}

function statusBadge(status) {
    const map = {
        'pending':             ['bg-secondary',        'Pending'],
        'supervisor-approved': ['bg-secondary',        'Supervisor Approved'],
        'approved':            ['bg-primary',          'Approved'],
        'accounting-approved': ['bg-primary',          'Acctg. Approved'],
        'paid':                ['bg-primary',          'Paid'],
        'rejected':            ['bg-secondary text-decoration-line-through', 'Rejected'],
    };
    const [cls, label] = map[status] || ['bg-secondary', status];
    return `<span class="badge ${cls}">${label}</span>`;
}

function generateOTId() {
    return 'OT-' + Date.now();
}

/* =====================================================================
   STATS
   ===================================================================== */

function renderStats() {
    const approvedPaid = otHistory.filter(o => ['approved','paid'].includes(o.status));
    const totalHours   = approvedPaid.reduce((s, o) => s + o.hours, 0);
    const totalPay     = approvedPaid.reduce((s, o) => s + o.estimatedPay, 0);
    const pending      = otHistory.filter(o => o.status === 'pending').length;

    const stats = [
        {
            label: 'Approved OT Hours',
            value: totalHours.toFixed(1) + ' hrs',
            icon: 'bi-clock',
            sub: 'Approved & paid requests'
        },
        {
            label: 'OT Earnings',
            value: formatCurrency(totalPay),
            icon: 'bi-cash-coin',
            sub: 'Estimated approved earnings'
        },
        {
            label: 'Pending Requests',
            value: pending,
            icon: 'bi-hourglass-split',
            sub: 'Awaiting approval'
        },
    ];

    document.getElementById('statsRow').innerHTML = stats.map(s => `
        <div class="col-md-4">
            <div class="card mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded border d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;flex-shrink:0">
                        <i class="bi ${s.icon} fs-5 text-secondary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">${s.label}</div>
                        <div class="fw-bold fs-5">${s.value}</div>
                        <div class="text-muted" style="font-size:.72rem">${s.sub}</div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

/* =====================================================================
   CALENDAR
   ===================================================================== */

function renderCalendar() {
    const year  = calMonth.getFullYear();
    const month = calMonth.getMonth();

    document.getElementById('calMonthLabel').textContent =
        calMonth.toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });

    const firstDow   = new Date(year, month, 1).getDay();
    const daysInMon  = new Date(year, month + 1, 0).getDate();
    const prevDays   = new Date(year, month, 0).getDate();

    let cells = [];

    // Prev month padding
    for (let i = firstDow - 1; i >= 0; i--) {
        cells.push({ day: prevDays - i, current: false, dateStr: null });
    }

    // Current month
    for (let d = 1; d <= daysInMon; d++) {
        const ds = `${year}-${pad(month + 1)}-${pad(d)}`;
        cells.push({ day: d, current: true, dateStr: ds });
    }

    // Next month padding
    const remaining = 42 - cells.length;
    for (let d = 1; d <= remaining; d++) {
        cells.push({ day: d, current: false, dateStr: null });
    }

    // Build 6 rows × 7 cols
    let html = '';
    for (let row = 0; row < 6; row++) {
        html += '<div class="row g-1 mb-1">';
        for (let col = 0; col < 7; col++) {
            const cell = cells[row * 7 + col];
            const ds   = cell.dateStr;

            if (!cell.current || !ds) {
                html += `<div class="col"><div class="ot-calendar-cell inactive">
                    <div class="ot-day-number">${cell.day}</div>
                </div></div>`;
                continue;
            }

            const att       = getAttendance(ds);
            const hasOT     = att && (att.hoursWorked > 8 || att.overtimeHours > 0 || att.status === 'overtime');
            const requested = hasExistingRequest(ds);
            const holiday   = getHoliday(ds);
            const night     = att && att.shift && att.shift.toLowerCase() === 'night';
            const isSelected = ds === selectedDate;

            let cls = 'ot-calendar-cell';
            let onclick = '';

            if (requested) {
                cls += ' requested';
            } else if (hasOT) {
                cls += isSelected ? ' selected' : ' eligible';
                onclick = `onclick="selectDate('${ds}')"`;
            } else {
                cls += ' inactive';
                onclick = `onclick="noOTAlert('${ds}')"`;
            }

            const otHrs = hasOT ? Math.max(0, att.hoursWorked - 8).toFixed(1) : null;

            let indicators = '';
            if (holiday) indicators += `<span class="ot-indicator text-primary fw-bold ms-1">H</span>`;
            if (night)   indicators += `<span class="ot-indicator text-secondary fw-bold ms-1">N</span>`;

            let badge = '';
            if (requested) {
                badge = `<span class="ot-hours-badge badge bg-secondary" style="font-size:.6rem">✓ Filed</span>`;
            } else if (hasOT && otHrs > 0) {
                badge = `<span class="ot-hours-badge badge ${isSelected ? 'bg-primary' : 'bg-light text-primary border'}">${otHrs}h OT</span>`;
            }

            html += `
                <div class="col">
                    <div class="${cls}" ${onclick}>
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="ot-day-number">${cell.day}</div>
                            <div>${indicators}</div>
                        </div>
                        ${badge}
                    </div>
                </div>`;
        }
        html += '</div>';
    }

    document.getElementById('calGrid').innerHTML = html;
}

function prevMonth() {
    calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth() - 1, 1);
    renderCalendar();
}

function nextMonth() {
    calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth() + 1, 1);
    renderCalendar();
}

function selectDate(ds) {
    if (hasExistingRequest(ds)) {
        Swal.fire({
            icon: 'info',
            title: 'Already Filed',
            text: `You already have an overtime request for ${formatDate(ds)}.`,
            confirmButtonColor: '#6c757d',
        });
        return;
    }

    selectedDate   = ds;
    detectedOTType = autoDetectOTType(ds);
    renderCalendar();
    updateOTTypeDisplay();
    updateSelectedDateInfo();
    updateEstimatedPay();
    document.getElementById('submitOTBtn').disabled = false;
}

function noOTAlert(ds) {
    const att = getAttendance(ds);
    if (att) {
        Swal.fire({
            icon: 'info',
            title: 'No Overtime',
            text: `You worked ${att.hoursWorked} hours on ${formatDate(ds)} — no overtime to claim.`,
            confirmButtonColor: '#6c757d',
        });
    } else {
        Swal.fire({
            icon: 'info',
            title: 'No Attendance Record',
            text: `No attendance record found for ${formatDate(ds)}.`,
            confirmButtonColor: '#6c757d',
        });
    }
}

function updateSelectedDateInfo() {
    const box = document.getElementById('selectedDateInfo');
    if (!selectedDate) { box.innerHTML = ''; return; }

    const att     = getAttendance(selectedDate);
    const hol     = getHoliday(selectedDate);
    const otHrs   = att ? Math.max(0, att.hoursWorked - 8) : 0;

    let holNote = '';
    if (hol) holNote = `<span class="badge bg-primary ms-1">${hol.name}</span>`;

    box.innerHTML = `
        <div class="border rounded p-2 bg-light">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <i class="bi bi-check-circle text-primary"></i>
                <span class="fw-semibold small">${formatDate(selectedDate)}</span>
                ${holNote}
            </div>
            <div class="text-muted small mt-1">
                Worked <strong>${att ? att.hoursWorked : 0} hrs</strong> — 
                <span class="text-primary">${otHrs.toFixed(1)} hrs overtime available</span>
            </div>
        </div>`;
}

function updateOTTypeDisplay() {
    const box = document.getElementById('otTypeDisplay');
    if (!detectedOTType) {
        box.innerHTML = '<span class="text-muted small">Select a date to auto-detect OT type</span>';
        return;
    }

    const multiplier = getMultiplier(detectedOTType);
    const desc       = otTypeDescription(detectedOTType);

    // Context tags
    const hol  = selectedDate ? getHoliday(selectedDate) : null;
    const att  = selectedDate ? getAttendance(selectedDate) : null;
    const rest = selectedDate ? isRestDay(selectedDate) : false;
    const night = att && att.shift && att.shift.toLowerCase() === 'night';

    let tags = '';
    if (hol)   tags += `<span class="badge bg-secondary me-1">${hol.type === 'regular' ? 'Regular Holiday' : 'Special Holiday'}</span>`;
    if (rest)  tags += `<span class="badge bg-secondary me-1">Rest Day</span>`;
    if (night) tags += `<span class="badge bg-secondary me-1">Night Shift</span>`;

    box.innerHTML = `
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
                <div class="fw-semibold">${detectedOTType}</div>
                <div class="text-muted small mt-1">${desc}</div>
                ${tags ? `<div class="mt-2">${tags}</div>` : ''}
            </div>
            <span class="badge bg-primary fs-6">${multiplier}×</span>
        </div>`;
}

function updateEstimatedPay() {
    const box     = document.getElementById('estimatedPayBox');
    const amtEl   = document.getElementById('estimatedPayAmt');
    const frmEl   = document.getElementById('estimatedPayFormula');

    if (!selectedDate || !detectedOTType) {
        box.classList.add('d-none');
        return;
    }

    const att        = getAttendance(selectedDate);
    const otHrs      = att ? Math.max(0, att.hoursWorked - 8) : 0;
    const multiplier = getMultiplier(detectedOTType);
    const pay        = otHrs * HOURLY_RATE * multiplier;

    amtEl.textContent  = formatCurrency(pay);
    frmEl.textContent  = `${otHrs.toFixed(1)} hrs × ${formatCurrency(HOURLY_RATE)}/hr × ${multiplier}×`;
    box.classList.remove('d-none');
}

/* =====================================================================
   FORM
   ===================================================================== */

function toggleForm() {
    formVisible = !formVisible;
    const card = document.getElementById('otFormCard');
    const btn  = document.getElementById('toggleFormBtn');

    if (formVisible) {
        card.classList.remove('d-none');
        btn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Cancel';
    } else {
        card.classList.add('d-none');
        btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> File OT Request';
        resetForm();
    }
}

function resetForm() {
    selectedDate   = null;
    detectedOTType = null;
    document.getElementById('otReason').value = '';
    document.getElementById('submitOTBtn').disabled = true;
    document.getElementById('estimatedPayBox').classList.add('d-none');
    document.getElementById('selectedDateInfo').innerHTML = '';
    document.getElementById('otTypeDisplay').innerHTML =
        '<span class="text-muted small">Select a date to auto-detect OT type</span>';
    renderCalendar();
}

function submitOTRequest() {
    if (!selectedDate || !detectedOTType) return;

    const att        = getAttendance(selectedDate);
    const otHrs      = att ? parseFloat(Math.max(0, att.hoursWorked - 8).toFixed(1)) : 0;
    const multiplier = getMultiplier(detectedOTType);
    const pay        = parseFloat((otHrs * HOURLY_RATE * multiplier).toFixed(2));
    const reason     = document.getElementById('otReason').value.trim();

    const newEntry = {
        id:            generateOTId(),
        date:          selectedDate,
        hours:         otHrs,
        type:          detectedOTType,
        reason:        reason,
        status:        'pending',
        rate:          multiplier,
        estimatedPay:  pay,
        submittedDate: toYMD(new Date()),
        reviewedBy:    '',
    };

    otHistory.unshift(newEntry);

    Swal.fire({
        icon: 'success',
        title: 'Request Submitted',
        html: `<strong>${otHrs} hrs</strong> on ${formatDate(selectedDate)}<br>
               Est. Pay: <strong>${formatCurrency(pay)}</strong>`,
        confirmButtonColor: '#0d6efd',
        timer: 2500,
        timerProgressBar: true,
    });

    toggleForm();
    renderStats();
    renderHistory();
}

/* =====================================================================
   HISTORY
   ===================================================================== */

function getFiltered() {
    const from   = document.getElementById('filterFrom').value;
    const to     = document.getElementById('filterTo').value;
    const status = document.getElementById('filterStatus').value;

    return otHistory.filter(o => {
        if (status !== 'all' && o.status !== status) return false;
        if (from && o.date < from) return false;
        if (to   && o.date > to)   return false;
        return true;
    });
}

function renderHistory() {
    const filtered = getFiltered();
    const tbody    = document.getElementById('historyTableBody');
    const empty    = document.getElementById('emptyState');
    const countEl  = document.getElementById('historyCount');

    countEl.textContent = `${filtered.length} of ${otHistory.length} record(s)`;

    if (filtered.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('d-none');
        return;
    }

    empty.classList.add('d-none');

    tbody.innerHTML = filtered.map(o => `
        <tr>
            <td class="text-nowrap">${formatDate(o.date)}</td>
            <td>${o.hours} hrs</td>
            <td><span class="small">${o.type}</span></td>
            <td class="text-nowrap">${o.rate}×</td>
            <td class="text-nowrap fw-semibold">${formatCurrency(o.estimatedPay)}</td>
            <td class="text-muted small" style="max-width:160px;white-space:normal">
                ${o.reason || '<span class="text-muted fst-italic">—</span>'}
            </td>
            <td class="text-nowrap">${statusBadge(o.status)}</td>
            <td class="text-nowrap text-muted small">${formatDate(o.submittedDate)}</td>
            <td class="text-nowrap text-muted small">${o.reviewedBy || '—'}</td>
            <td class="text-center text-nowrap">
                <button class="btn btn-sm btn-outline-secondary"
                    onclick="deleteOT('${o.id}', '${o.status}', '${formatDate(o.date)}')">
                    <i class="bi bi-trash3"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function clearFilters() {
    document.getElementById('filterFrom').value   = '';
    document.getElementById('filterTo').value     = '';
    document.getElementById('filterStatus').value = 'all';
    renderHistory();
}

function deleteOT(id, status, dateLabel) {
    const isProcessed = ['approved', 'paid', 'accounting-approved'].includes(status);
    const msg = isProcessed
        ? `This request (${dateLabel}) is already <strong>${status}</strong>. Deleting it may affect payroll records.`
        : `Delete overtime request for <strong>${dateLabel}</strong>?`;

    Swal.fire({
        icon: isProcessed ? 'warning' : 'question',
        title: 'Delete Request?',
        html: msg,
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: isProcessed ? '#6c757d' : '#0d6efd',
        cancelButtonColor: '#6c757d',
    }).then(result => {
        if (!result.isConfirmed) return;

        const idx = otHistory.findIndex(o => o.id === id);
        if (idx !== -1) otHistory.splice(idx, 1);

        renderStats();
        renderCalendar();
        renderHistory();

        Swal.fire({
            icon: 'success',
            title: 'Deleted',
            text: 'The overtime request has been removed.',
            timer: 1500,
            showConfirmButton: false,
        });
    });
}

/* =====================================================================
   INIT
   ===================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    renderStats();
    renderCalendar();
    renderHistory();
});
</script>
@endpush