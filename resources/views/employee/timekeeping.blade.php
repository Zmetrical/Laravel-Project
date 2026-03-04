@extends('layouts.main')

@section('title', 'Timekeeping')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Timekeeping</li>
    </ol>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-3">
    <h4 class="mb-1 fw-semibold">Timekeeping</h4>
    <p class="text-muted small mb-0">Track your work hours and attendance</p>
</div>

{{-- Suspension Notice (hidden by default, shown via JS if suspended) --}}
<div id="suspensionNotice" class="alert border-0 d-none mb-3" style="background:#f8d7da;">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-slash-circle fs-4 text-danger mt-1"></i>
        <div>
            <strong class="text-danger">TIMEKEEPING DISABLED — ACCOUNT SUSPENDED</strong>
            <p class="mb-1 mt-1 small">Your timekeeping has been disabled due to a negative payroll balance. Contact HR to resolve this issue.</p>
        </div>
    </div>
</div>

{{-- Live Clock --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body text-center py-4">
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10" style="width:72px;height:72px;">
            <i class="bi bi-clock fs-2 text-primary"></i>
        </div>
        <h2 id="liveClock" class="fw-bold mb-1" style="letter-spacing:2px;">00:00:00</h2>
        <p id="liveDate" class="text-muted small mb-0"></p>
    </div>
</div>

{{-- Clock In/Out + Calendar --}}
<div class="row g-3 mb-3">

    {{-- Quick Action --}}
    <div class="col-lg-5">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h6 class="card-title fw-semibold mb-0">Quick Action</h6>
            </div>
            <div class="card-body pt-2">

                <button id="clockBtn" class="btn btn-primary w-100 py-2 mb-2" onclick="handleClockToggle()">
                    <i class="bi bi-play-fill me-2"></i> Clock In
                </button>

                <div id="scannerMsg" class="text-center small text-muted border rounded p-2 mb-2">
                    <i class="bi bi-fingerprint me-1"></i> Use fingerprint scanner on LAN device
                </div>

                {{-- Clocked In Info (hidden by default) --}}
                <div id="clockedInInfo" class="d-none">
                    <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-2">
                        <span class="text-muted small">Status</span>
                        <span class="badge bg-success rounded-pill">● Clocked In</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-2">
                        <span class="text-muted small">Time In</span>
                        <span id="displayTimeIn" class="fw-semibold"></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-primary bg-opacity-10 border border-primary rounded px-3 py-2">
                        <span class="text-primary small fw-semibold">Hours Worked</span>
                        <span id="elapsedTimer" class="text-primary fw-bold fs-5">00:00:00</span>
                    </div>
                </div>

                <hr class="my-3">

                {{-- Testing Mode Toggle --}}
                <div id="testModeOff">
                    <button class="btn btn-secondary btn-sm w-100" onclick="enableTestMode()">
                        <i class="bi bi-bug me-1"></i> Enable Testing Mode
                    </button>
                </div>

                <div id="testModePanel" class="d-none">
                    <div class="border rounded p-2 mb-2 bg-light">
                        <p class="small fw-semibold text-secondary mb-1"><i class="bi bi-calendar2 me-1"></i> SYSTEM DATE TESTING</p>
                        <p class="small text-muted mb-1">Select a date from the calendar on the right.</p>
                        <p id="testDateDisplay" class="small fw-bold text-secondary mb-0"></p>
                    </div>
                    <div class="border rounded p-2 mb-2 bg-light">
                        <p class="small fw-semibold text-secondary mb-1"><i class="bi bi-clock me-1"></i> TIME TESTING</p>
                        <input type="time" id="testTimeInput" class="form-control form-control-sm mb-1" value="06:00">
                        <p class="text-muted" style="font-size:11px;">This affects Clock In/Out timestamps</p>
                    </div>
                    <button class="btn btn-secondary btn-sm w-100" onclick="disableTestMode()">
                        <i class="bi bi-clock-history me-1"></i> Back to Real-time
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Attendance Calendar --}}
    <div class="col-lg-7">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                <h6 class="card-title fw-semibold mb-0">Attendance Calendar</h6>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-secondary px-2" onclick="prevCalendarMonth()">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span id="calMonthLabel" class="small fw-semibold" style="min-width:140px;text-align:center;"></span>
                    <button class="btn btn-sm btn-secondary px-2" onclick="nextCalendarMonth()">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="card-body pt-2">
                {{-- Day Headers --}}
                <div class="row g-0 mb-1">
                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
                    <div class="col text-center small text-muted fw-semibold py-1">{{ $d }}</div>
                    @endforeach
                </div>

                {{-- Calendar Grid --}}
                <div id="calendarGrid" class="row g-1"></div>

                {{-- Legend --}}
                <div class="border-top mt-2 pt-2">
                    <div class="row g-2 small text-muted">
                        <div class="col-6 d-flex align-items-center gap-1">
                            <i class="bi bi-sun text-warning" style="font-size:12px;"></i> Day Shift
                        </div>
                        <div class="col-6 d-flex align-items-center gap-1">
                            <i class="bi bi-moon text-secondary" style="font-size:12px;"></i> Night Shift
                        </div>
                        <div class="col-6 d-flex align-items-center gap-1">
                            <span class="rounded-circle bg-danger d-inline-block" style="width:8px;height:8px;"></span> Regular Holiday
                        </div>
                        <div class="col-6 d-flex align-items-center gap-1">
                            <i class="bi bi-briefcase" style="font-size:12px;"></i> On Leave
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DTR Table --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <h6 class="card-title fw-semibold mb-0">Daily Time Record (DTR)</h6>
            <span id="dtrCount" class="badge bg-secondary rounded-pill">0 records</span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-secondary btn-sm" onclick="generateSchedules()">
                <i class="bi bi-calendar-check me-1"></i> Generate Schedules
            </button>
            <button class="btn btn-secondary btn-sm" onclick="downloadDTR()">
                <i class="bi bi-download me-1"></i> Download DTR
            </button>
        </div>
    </div>
    <div class="card-body pb-2">
        {{-- Filters --}}
        <div class="row g-2 mb-3">
            <div class="col-sm-4">
                <select id="filterMonth" class="form-select form-select-sm" onchange="applyFilters()">
                    <option value="0">January</option><option value="1">February</option>
                    <option value="2">March</option><option value="3">April</option>
                    <option value="4">May</option><option value="5">June</option>
                    <option value="6">July</option><option value="7">August</option>
                    <option value="8">September</option><option value="9">October</option>
                    <option value="10">November</option><option value="11">December</option>
                </select>
            </div>
            <div class="col-sm-4">
                <select id="filterYear" class="form-select form-select-sm" onchange="applyFilters()"></select>
            </div>
            <div class="col-sm-4">
                <select id="filterCutoff" class="form-select form-select-sm" onchange="applyFilters()">
                    <option value="full">Full Month</option>
                    <option value="first">1st – 15th</option>
                    <option value="second">16th – End</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours Worked</th>
                        <th>Late / UT</th>
                        <th>Status</th>
                        <th id="actionsHeader" class="d-none">Actions</th>
                    </tr>
                </thead>
                <tbody id="dtrTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Quick Stats --}}
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10" style="width:48px;height:48px;min-width:48px;">
                    <i class="bi bi-calendar3 text-primary fs-5"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0">This Week</p>
                    <h5 id="statWeek" class="fw-bold mb-0">0 hrs</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded bg-secondary bg-opacity-10" style="width:48px;height:48px;min-width:48px;">
                    <i class="bi bi-clock text-secondary fs-5"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0">This Month</p>
                    <h5 id="statMonth" class="fw-bold mb-0">0 hrs</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10" style="width:48px;height:48px;min-width:48px;">
                    <i class="bi bi-calendar-day text-primary fs-5"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0">Today</p>
                    <h5 id="statToday" class="fw-bold mb-0">—</h5>
                    <p id="statPresent" class="text-muted mb-0" style="font-size:11px;"></p>
                    <div id="statHoliday" class="d-none mt-1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .cal-cell {
        min-height: 64px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        padding: 4px 5px;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        font-size: 11px;
        position: relative;
    }
    .cal-cell.other-month   { opacity: .25; pointer-events:none; background:#f8f9fa; }
    .cal-cell.disabled      { pointer-events:none; opacity:.5; background:#f8f9fa; }
    .cal-cell.is-today      { border-color: var(--bs-primary); }
    .cal-cell.has-attendance{ background:#d1e7dd; border-color:#a3cfbb; pointer-events:none; }
    .cal-cell.is-restday    { background:#fff3cd; border-color:#ffd97d; }
    .cal-cell.is-leave      { background:#f8d7da; border-color:#f5c6cb; pointer-events:none; opacity:.7; }
    .cal-cell.is-selected   { outline:2px solid var(--bs-primary); background: #cfe2ff; }
    .cal-cell:not(.disabled):not(.other-month):not(.has-attendance):not(.is-leave):hover { border-color:var(--bs-primary); background:#e9f0ff; }
    .cal-day-num { font-weight:700; font-size:13px; }
    .cal-badge   { display:inline-block; padding:1px 5px; border-radius:4px; font-size:10px; font-weight:600; line-height:1.4; }
</style>
@endpush

@push('scripts')
<script>

const EMPLOYEE = {
    id: 'U001',
    name: 'Juan dela Cruz',
    department: 'Operations',
    defaultShift: 'Day',
    isSuspended: false,
};

const PHILIPPINE_HOLIDAYS_2026 = [
    { date:'2026-01-01', name:"New Year's Day",   type:'regular'  },
    { date:'2026-04-02', name:"Maundy Thursday",  type:'regular'  },
    { date:'2026-04-03', name:"Good Friday",      type:'regular'  },
    { date:'2026-04-09', name:"Araw ng Kagitingan",type:'regular' },
    { date:'2026-05-01', name:"Labor Day",         type:'regular'  },
    { date:'2026-06-12', name:"Independence Day",  type:'regular'  },
    { date:'2026-08-31', name:"National Heroes Day",type:'regular' },
    { date:'2026-11-30', name:"Bonifacio Day",     type:'regular'  },
    { date:'2026-12-25', name:"Christmas Day",     type:'regular'  },
    { date:'2026-12-30', name:"Rizal Day",         type:'regular'  },
    { date:'2026-11-01', name:"All Saints Day",    type:'special'  },
    { date:'2026-12-08', name:"Immaculate Conception",type:'special'},
    { date:'2026-12-31', name:"New Year's Eve",    type:'special'  },
];

const ATTENDANCE_RECORDS = [
    { id:'ATT001', date:'2026-03-02', timeIn:'06:03', timeOut:'16:10', hoursWorked:8.00, status:'present', shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT002', date:'2026-03-01', timeIn:'06:15', timeOut:'16:00', hoursWorked:7.75, status:'late',    shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT003', date:'2026-02-28', timeIn:'22:00', timeOut:'06:00', hoursWorked:8.00, status:'present', shift:'Night Shift',shiftType:'night',scheduledStart:'22:00',scheduledEnd:'06:00'},
    { id:'ATT004', date:'2026-02-27', timeIn:'06:00', timeOut:'15:30', hoursWorked:7.50, status:'present', shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT005', date:'2026-02-26', timeIn:null,    timeOut:null,    hoursWorked:0,    status:'absent',  shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT006', date:'2026-02-25', timeIn:'06:01', timeOut:'16:05', hoursWorked:8.00, status:'present', shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT007', date:'2026-02-24', timeIn:'22:05', timeOut:'06:00', hoursWorked:8.00, status:'present', shift:'Night Shift',shiftType:'night',scheduledStart:'22:00',scheduledEnd:'06:00'},
    { id:'ATT008', date:'2026-02-23', timeIn:'06:00', timeOut:'16:00', hoursWorked:8.00, status:'present', shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT009', date:'2026-02-22', timeIn:'06:45', timeOut:'16:00', hoursWorked:7.25, status:'late',    shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT010', date:'2026-02-21', timeIn:'22:00', timeOut:'06:00', hoursWorked:8.00, status:'present', shift:'Night Shift',shiftType:'night',scheduledStart:'22:00',scheduledEnd:'06:00'},
    { id:'ATT011', date:'2026-02-20', timeIn:null,    timeOut:null,    hoursWorked:0,    status:'leave',   shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT012', date:'2026-02-19', timeIn:'06:00', timeOut:'16:00', hoursWorked:8.00, status:'present', shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT013', date:'2026-02-18', timeIn:'22:10', timeOut:'06:00', hoursWorked:7.83, status:'late',    shift:'Night Shift',shiftType:'night',scheduledStart:'22:00',scheduledEnd:'06:00'},
    { id:'ATT014', date:'2026-02-17', timeIn:'06:00', timeOut:'16:00', hoursWorked:8.00, status:'present', shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
    { id:'ATT015', date:'2026-02-16', timeIn:'06:02', timeOut:'16:00', hoursWorked:8.00, status:'present', shift:'Day Shift', shiftType:'day', scheduledStart:'06:00', scheduledEnd:'16:00' },
];

const LEAVE_REQUESTS = [
    { id:'LR001', employeeId:'U001', type:'Vacation Leave', startDate:'2026-02-20', endDate:'2026-02-20', status:'approved' },
];

const REST_DAYS = ['Sunday']; // Days off for this employee

/* ============================================================
   STATE
   ============================================================ */
const state = {
    isClockedIn: false,
    clockInTime: null,
    clockInTimeDisplay: '',
    elapsedSeconds: 0,
    elapsedInterval: null,
    clockInterval: null,
    testMode: false,
    testDate: null,      // string 'YYYY-MM-DD'
    testTime: '06:00',
    calendarDate: new Date(2026, 2, 1), // March 2026
    records: [...ATTENDANCE_RECORDS],
};

/* ============================================================
   HELPERS
   ============================================================ */
const DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
const MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];

function today() {
    if (state.testMode && state.testDate) return state.testDate;
    return new Date().toISOString().split('T')[0];
}

function getHoliday(dateStr) {
    return PHILIPPINE_HOLIDAYS_2026.find(h => h.date === dateStr) || null;
}

function isRestDay(dateStr) {
    const d = new Date(dateStr + 'T12:00:00');
    return REST_DAYS.includes(DAY_NAMES[d.getDay()]);
}

function isOnLeave(dateStr) {
    return LEAVE_REQUESTS.some(l =>
        l.status === 'approved' && l.employeeId === EMPLOYEE.id &&
        dateStr >= l.startDate && dateStr <= l.endDate
    );
}

function getShiftForDate(dateStr) {
    // Simple rotation: odd weeks Day, even weeks Night (starting from Jan 1, 2026)
    const weekNum = Math.floor((new Date(dateStr) - new Date('2026-01-04')) / (7*86400000));
    return weekNum % 2 === 0 ? 'Day Shift' : 'Night Shift';
}

function formatTime(date) {
    return date.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}

function formatDate(date) {
    return date.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}

function pad(n) { return String(n).padStart(2,'0'); }

function secondsToHMS(s) {
    const h = Math.floor(s/3600), m = Math.floor((s%3600)/60), sc = s%60;
    return `${pad(h)}:${pad(m)}:${pad(sc)}`;
}

function calcLateUT(record) {
    if (!record.timeIn) return { late: 0, ut: 0 };
    const [inH, inM] = record.timeIn.split(':').map(Number);
    const [sH, sM] = (record.scheduledStart || '06:00').split(':').map(Number);
    const [eH, eM] = (record.scheduledEnd   || '16:00').split(':').map(Number);
    const grace = 10;
    const actualIn = inH*60+inM;
    const scheduled = sH*60+sM;
    const late = Math.max(0, actualIn - (scheduled+grace));

    let ut = 0;
    if (record.timeOut) {
        const [outH, outM] = record.timeOut.split(':').map(Number);
        const actualOut = outH*60+outM;
        const scheduledEnd = eH*60+eM;
        // Day shift
        if (record.shiftType === 'day' && actualOut < scheduledEnd) {
            ut = scheduledEnd - actualOut;
        }
        // Night shift end in AM
        if (record.shiftType === 'night' && actualOut < 6*60) {
            ut = 6*60 - actualOut;
        }
    }
    return { late, ut };
}

/* ============================================================
   LIVE CLOCK
   ============================================================ */
function startClock() {
    state.clockInterval = setInterval(() => {
        const now = new Date();
        document.getElementById('liveClock').textContent = formatTime(now);
        document.getElementById('liveDate').textContent = formatDate(now);
    }, 1000);
    const now = new Date();
    document.getElementById('liveClock').textContent = formatTime(now);
    document.getElementById('liveDate').textContent = formatDate(now);
}

/* ============================================================
   CLOCK IN / OUT
   ============================================================ */
function handleClockToggle() {
    if (EMPLOYEE.isSuspended) {
        Swal.fire('Account Suspended','Contact HR to resolve your account.','error');
        return;
    }

    const btn = document.getElementById('clockBtn');
    btn.disabled = true;
    document.getElementById('scannerMsg').innerHTML = `<i class="bi bi-fingerprint me-1 text-primary"></i> Scanning fingerprint…`;

    setTimeout(() => {
        if (!state.isClockedIn) {
            clockIn();
        } else {
            clockOut();
        }
        btn.disabled = false;
        document.getElementById('scannerMsg').innerHTML = `<i class="bi bi-fingerprint me-1"></i> Use fingerprint scanner on LAN device`;
    }, 1800);
}

function clockIn() {
    const now = new Date();
    let displayTime;

    if (state.testMode && state.testTime) {
        const [h, m] = state.testTime.split(':').map(Number);
        const t = new Date(); t.setHours(h,m,0,0);
        displayTime = t.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', hour12:true });
    } else {
        displayTime = now.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', hour12:true });
    }

    state.isClockedIn  = true;
    state.clockInTime  = now;
    state.clockInTimeDisplay = displayTime;
    state.elapsedSeconds = 0;

    const btn = document.getElementById('clockBtn');
    btn.className = 'btn btn-danger w-100 py-2 mb-2';
    btn.innerHTML = '<i class="bi bi-stop-fill me-2"></i> Clock Out';

    document.getElementById('clockedInInfo').classList.remove('d-none');
    document.getElementById('displayTimeIn').textContent = displayTime;

    state.elapsedInterval = setInterval(() => {
        state.elapsedSeconds++;
        document.getElementById('elapsedTimer').textContent = secondsToHMS(state.elapsedSeconds);
    }, 1000);

    // Add a temporary "ongoing" record
    const todayStr = today();
    const shift = getShiftForDate(todayStr);
    state.records.unshift({
        id: 'TMP_' + Date.now(),
        date: todayStr,
        timeIn: displayTime,
        timeOut: null,
        hoursWorked: 0,
        status: 'ongoing',
        shift,
        shiftType: shift.includes('Night') ? 'night' : 'day',
        scheduledStart: shift.includes('Night') ? '22:00' : '06:00',
        scheduledEnd:   shift.includes('Night') ? '06:00' : '16:00',
    });

    renderDTR();
    renderCalendar();
    Swal.fire({ icon:'success', title:'Clocked In!', text:`Time In: ${displayTime}`, timer:2000, showConfirmButton:false });
}

function clockOut() {
    const now = new Date();
    let displayTime;
    let hours = state.elapsedSeconds / 3600;

    if (state.testMode && state.testTime) {
        const [h, m] = state.testTime.split(':').map(Number);
        const t = new Date(); t.setHours(h,m,0,0);
        displayTime = t.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', hour12:true });
    } else {
        displayTime = now.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', hour12:true });
        hours = (now - state.clockInTime) / 3600000;
    }

    clearInterval(state.elapsedInterval);

    // Update the ongoing record
    const idx = state.records.findIndex(r => r.status === 'ongoing');
    if (idx > -1) {
        state.records[idx].timeOut    = displayTime;
        state.records[idx].hoursWorked = parseFloat(hours.toFixed(2));
        state.records[idx].status     = 'present';
    }

    state.isClockedIn = false;
    state.clockInTime = null;

    const btn = document.getElementById('clockBtn');
    btn.className = 'btn btn-primary w-100 py-2 mb-2';
    btn.innerHTML = '<i class="bi bi-play-fill me-2"></i> Clock In';
    document.getElementById('clockedInInfo').classList.add('d-none');

    renderDTR();
    renderCalendar();
    Swal.fire({ icon:'success', title:'Clocked Out!', text:`Time Out: ${displayTime} | ${hours.toFixed(2)}h`, timer:2000, showConfirmButton:false });
}

/* ============================================================
   DTR TABLE
   ============================================================ */
function applyFilters() {
    const month   = parseInt(document.getElementById('filterMonth').value);
    const year    = parseInt(document.getElementById('filterYear').value);
    const cutoff  = document.getElementById('filterCutoff').value;

    const filtered = state.records.filter(r => {
        const d = new Date(r.date);
        if (d.getMonth() !== month || d.getFullYear() !== year) return false;
        const day = d.getDate();
        if (cutoff === 'first')  return day <= 15;
        if (cutoff === 'second') return day >= 16;
        return true;
    });

    renderDTR(filtered);
    renderStats(filtered);
}

function renderDTR(records) {
    const month  = parseInt(document.getElementById('filterMonth').value);
    const year   = parseInt(document.getElementById('filterYear').value);
    const cutoff = document.getElementById('filterCutoff').value;

    if (!records) {
        records = state.records.filter(r => {
            const d = new Date(r.date);
            if (d.getMonth() !== month || d.getFullYear() !== year) return false;
            const day = d.getDate();
            if (cutoff === 'first')  return day <= 15;
            if (cutoff === 'second') return day >= 16;
            return true;
        });
    }

    document.getElementById('dtrCount').textContent = `${records.length} record${records.length!==1?'s':''}`;

    const isTest = state.testMode;
    document.getElementById('actionsHeader').classList.toggle('d-none', !isTest);

    const tbody = document.getElementById('dtrTableBody');

    if (records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="${isTest?8:7}" class="text-center py-5 text-muted">
                    <i class="bi bi-calendar3 fs-2 d-block mb-2 text-secondary"></i>
                    No records for this period.
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = records.sort((a,b) => b.date.localeCompare(a.date)).map(r => {
        const lut = calcLateUT(r);
        const isAdditional = false; // extend this with real additional-shift logic

        // Shift badge
        const shiftBadge = r.shift.includes('Night')
            ? `<span class="badge bg-secondary">${r.shift}</span>`
            : `<span class="badge" style="background:#ffc107;color:#000;">${r.shift}</span>`;

        // Late/UT cell
        let lutHtml = '<span class="text-success small">✓ On time</span>';
        if (lut.late > 0 || lut.ut > 0) {
            lutHtml = '';
            if (lut.late > 0) lutHtml += `<span class="badge bg-secondary me-1">L: ${Math.floor(lut.late/60)}h ${lut.late%60}m</span>`;
            if (lut.ut   > 0) lutHtml += `<span class="badge bg-secondary">UT: ${Math.floor(lut.ut/60)}h ${lut.ut%60}m</span>`;
        }

        // Status badge
        const statusMap = {
            present: 'bg-primary',
            late:    'bg-secondary',
            absent:  'bg-secondary text-white',
            leave:   'bg-secondary',
            ongoing: 'bg-primary',
        };
        const statusClass = statusMap[r.status] || 'bg-secondary';
        const statusLabel = r.status === 'ongoing' ? 'Ongoing' : r.status.charAt(0).toUpperCase()+r.status.slice(1);

        // Hours
        const hoursCell = r.status === 'ongoing'
            ? `<span class="text-primary small"><i class="bi bi-hourglass-split me-1"></i>In Progress…</span>`
            : r.timeOut ? `${r.hoursWorked.toFixed(2)} hrs` : '—';

        // Actions (test mode only)
        const actionsCell = isTest
            ? `<td><button class="btn btn-sm btn-secondary" onclick="deleteRecord('${r.id}')"><i class="bi bi-trash"></i></button></td>`
            : '';

        return `
            <tr>
                <td class="fw-semibold">${r.date}</td>
                <td>${shiftBadge}</td>
                <td>${r.timeIn || '—'}</td>
                <td>${r.timeOut || '—'}</td>
                <td>${hoursCell}</td>
                <td>${lutHtml}</td>
                <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                ${actionsCell}
            </tr>`;
    }).join('');
}

function deleteRecord(id) {
    if (!state.testMode) return;
    state.records = state.records.filter(r => r.id !== id);
    renderDTR();
    renderCalendar();
    renderStats();
}

/* ============================================================
   STATS
   ============================================================ */
function renderStats(filtered) {
    const todayStr = today();
    const now = new Date(todayStr);
    const firstOfWeek = new Date(now);
    firstOfWeek.setDate(now.getDate() - (now.getDay() === 0 ? 6 : now.getDay()-1));
    const firstOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);

    const weekRecs  = state.records.filter(r => r.date >= firstOfWeek.toISOString().split('T')[0] && r.date <= todayStr);
    const monthRecs = state.records.filter(r => r.date >= firstOfMonth.toISOString().split('T')[0] && r.date <= todayStr);

    const weekHrs  = weekRecs.reduce((s,r)  => s + (r.hoursWorked||0), 0);
    const monthHrs = monthRecs.reduce((s,r) => s + (r.hoursWorked||0), 0);

    const daysPresent = new Set(monthRecs.filter(r => r.timeIn).map(r => r.date)).size;
    let workDays = 0;
    const cur = new Date(firstOfMonth);
    while (cur <= now) { if ([1,2,3,4,5].includes(cur.getDay())) workDays++; cur.setDate(cur.getDate()+1); }

    document.getElementById('statWeek').textContent  = `${weekHrs.toFixed(1)} hrs`;
    document.getElementById('statMonth').textContent = `${Math.round(monthHrs)} hrs`;

    const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    document.getElementById('statToday').textContent = `${now.getDate()} — ${dayNames[now.getDay()]}`;
    document.getElementById('statPresent').textContent = `(${daysPresent} / ${workDays} days present)`;

    // Holiday
    const hol = getHoliday(todayStr);
    const holDiv = document.getElementById('statHoliday');
    if (hol) {
        holDiv.classList.remove('d-none');
        const cls = hol.type === 'regular' ? 'text-danger' : 'text-secondary';
        holDiv.innerHTML = `<span class="badge bg-secondary">${hol.name}</span>
            <p class="${cls} mb-0" style="font-size:10px;">${hol.type==='regular'?'200% if working':'130% if working'}</p>`;
    } else {
        holDiv.classList.add('d-none');
    }
}

/* ============================================================
   CALENDAR
   ============================================================ */
function renderCalendar() {
    const year  = state.calendarDate.getFullYear();
    const month = state.calendarDate.getMonth();

    document.getElementById('calMonthLabel').textContent = `${MONTH_NAMES[month]} ${year}`;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month+1, 0).getDate();
    const prevMonthDays = new Date(year, month, 0).getDate();
    const todayStr = today();

    let html = '';

    // Prev month padding
    for (let i = firstDay-1; i >= 0; i--) {
        html += `<div class="col"><div class="cal-cell other-month"><span class="cal-day-num text-muted">${prevMonthDays-i}</span></div></div>`;
    }

    // Current month
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${year}-${pad(month+1)}-${pad(d)}`;
        const isToday  = dateStr === todayStr;
        const isPast   = dateStr < todayStr;
        const hasAtt   = state.records.some(r => r.date === dateStr && r.timeIn);
        const onLeave  = isOnLeave(dateStr);
        const restDay  = isRestDay(dateStr);
        const holiday  = getHoliday(dateStr);
        const shift    = getShiftForDate(dateStr);
        const isSelected = state.testMode && state.testDate === dateStr;

        let classes = 'cal-cell';
        if (isPast && !hasAtt && !isToday)  classes += ' disabled';
        if (hasAtt)    classes += ' has-attendance';
        if (onLeave)   classes += ' is-leave';
        if (restDay && !hasAtt) classes += ' is-restday';
        if (isToday)   classes += ' is-today';
        if (isSelected) classes += ' is-selected';

        const clickable = !isPast && !onLeave && !hasAtt;

        let badges = '';
        if (holiday) {
            const hClass = holiday.type === 'regular' ? 'bg-danger' : 'bg-secondary';
            badges += `<span class="cal-badge ${hClass} text-white d-block mb-1">${holiday.name.split(' ')[0]}</span>`;
        }
        if (!onLeave && !restDay && !hasAtt && !isPast) {
            const shiftIcon = shift.includes('Night')
                ? `<i class="bi bi-moon text-secondary" style="font-size:10px;"></i>`
                : `<i class="bi bi-sun text-warning" style="font-size:10px;"></i>`;
            badges += `<span class="d-block" style="font-size:10px;">${shiftIcon} ${shift.replace(' Shift','')}</span>`;
        }
        if (hasAtt) {
            badges += `<span class="cal-badge bg-primary text-white d-block">✓ Done</span>`;
        }
        if (restDay && !hasAtt) {
            badges += `<span class="cal-badge bg-secondary text-white d-block">Day Off</span>`;
        }
        if (onLeave) {
            badges += `<span class="cal-badge bg-secondary text-white d-block"><i class="bi bi-briefcase"></i> Leave</span>`;
        }

        const dayNumClass = hasAtt ? 'text-primary' : restDay ? 'text-secondary' : isToday ? 'text-primary' : '';
        const onclick = state.testMode && !onLeave ? `handleCalendarClick('${dateStr}')` : '';

        html += `
            <div class="col" style="width:14.28%;flex:0 0 14.28%;max-width:14.28%;">
                <div class="${classes}" ${onclick ? `onclick="${onclick}"` : ''}>
                    <span class="cal-day-num ${dayNumClass}">${d}</span>
                    ${badges}
                </div>
            </div>`;
    }

    // Next month padding
    const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
    const remaining  = totalCells - firstDay - daysInMonth;
    for (let i = 1; i <= remaining; i++) {
        html += `<div class="col"><div class="cal-cell other-month"><span class="cal-day-num text-muted">${i}</span></div></div>`;
    }

    document.getElementById('calendarGrid').innerHTML = html;
}

function prevCalendarMonth() {
    state.calendarDate = new Date(state.calendarDate.getFullYear(), state.calendarDate.getMonth()-1, 1);
    renderCalendar();
}

function nextCalendarMonth() {
    state.calendarDate = new Date(state.calendarDate.getFullYear(), state.calendarDate.getMonth()+1, 1);
    renderCalendar();
}

function handleCalendarClick(dateStr) {
    if (!state.testMode) return;
    state.testDate = dateStr;
    document.getElementById('testDateDisplay').textContent = new Date(dateStr+'T12:00:00').toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
    renderCalendar();
    applyFilters();
    renderStats();
    Swal.fire({ icon:'success', title:'System Date Changed', text:dateStr, timer:1500, showConfirmButton:false });
}

/* ============================================================
   YEAR FILTER INIT
   ============================================================ */
function initYearFilter() {
    const years = [...new Set(state.records.map(r => new Date(r.date).getFullYear()))];
    const cur = new Date().getFullYear();
    if (!years.includes(cur)) years.push(cur);
    years.sort((a,b) => b-a);

    const sel = document.getElementById('filterYear');
    sel.innerHTML = years.map(y => `<option value="${y}">${y}</option>`).join('');
}

function initMonthFilter() {
    const now = new Date();
    document.getElementById('filterMonth').value = now.getMonth();
    document.getElementById('filterYear').value  = now.getFullYear();
}

/* ============================================================
   TESTING MODE
   ============================================================ */
function enableTestMode() {
    state.testMode = true;
    state.testDate = today();
    document.getElementById('testModeOff').classList.add('d-none');
    document.getElementById('testModePanel').classList.remove('d-none');
    document.getElementById('testDateDisplay').textContent = new Date(state.testDate+'T12:00:00').toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
    renderCalendar();
    renderDTR();
    document.getElementById('actionsHeader').classList.remove('d-none');
}

function disableTestMode() {
    state.testMode = false;
    state.testDate = null;
    document.getElementById('testModeOff').classList.remove('d-none');
    document.getElementById('testModePanel').classList.add('d-none');
    renderCalendar();
    renderDTR();
    document.getElementById('actionsHeader').classList.add('d-none');
}

/* ============================================================
   MISC ACTIONS
   ============================================================ */
function generateSchedules() {
    Swal.fire({ icon:'success', title:'Schedules Generated', text:'All schedules are up to date.', timer:2000, showConfirmButton:false });
}

function downloadDTR() {
    Swal.fire({ icon:'success', title:'DTR Downloaded', text:'Your daily time record has been saved.', timer:2000, showConfirmButton:false });
}

/* ============================================================
   SUSPENSION NOTICE
   ============================================================ */
if (EMPLOYEE.isSuspended) {
    document.getElementById('suspensionNotice').classList.remove('d-none');
    document.getElementById('clockBtn').disabled = true;
    document.getElementById('clockBtn').textContent = 'Account Suspended';
}

/* ============================================================
   INIT
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    startClock();
    initYearFilter();
    initMonthFilter();
    renderCalendar();
    applyFilters();
    renderStats();

    document.getElementById('testTimeInput').addEventListener('change', function() {
        state.testTime = this.value;
    });
});
</script>
@endpush