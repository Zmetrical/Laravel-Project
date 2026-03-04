@extends('layouts.main')

@section('title', 'My Schedule')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">My Schedule</li>
    </ol>
@endsection

@push('styles')
<style>
    .calendar-day {
        min-height: 110px;
        border: 1px solid var(--bs-border-color);
        border-radius: .375rem;
        padding: .5rem;
        transition: box-shadow .15s ease;
        background: var(--bs-body-bg);
    }
    .calendar-day.is-today {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 .15rem rgba(var(--bs-primary-rgb), .2);
    }
    .calendar-day.is-off-month {
        opacity: .4;
    }
    .calendar-day.is-rest {
        background: var(--bs-secondary-bg);
    }
    .calendar-day.has-slot {
        border-color: var(--bs-success);
        cursor: pointer;
    }
    .calendar-day.has-slot:hover {
        box-shadow: 0 0 0 .15rem rgba(var(--bs-success-rgb), .25);
    }
    .day-number {
        font-weight: 700;
        font-size: .95rem;
    }
    .shift-badge {
        font-size: .65rem;
        font-weight: 600;
        padding: .15rem .4rem;
        border-radius: .25rem;
        display: inline-flex;
        align-items: center;
        gap: .2rem;
    }
    .shift-day  { background: rgba(var(--bs-warning-rgb), .15); color: var(--bs-warning-text-emphasis); }
    .shift-night { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary-text-emphasis); }
    .shift-leave  { background: rgba(var(--bs-secondary-rgb), .15); color: var(--bs-secondary-text-emphasis); }
    .shift-rest   { background: rgba(var(--bs-secondary-rgb), .1);  color: var(--bs-secondary); }
    .time-label {
        font-size: .62rem;
        color: var(--bs-secondary-color);
        margin-top: .1rem;
    }
    .weekday-header {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--bs-secondary-color);
        text-align: center;
        padding: .4rem 0;
    }
    .holiday-tag {
        font-size: .6rem;
        font-weight: 600;
        padding: .1rem .35rem;
        border-radius: .2rem;
        margin-top: .2rem;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .holiday-regular   { background: rgba(var(--bs-danger-rgb), .12); color: var(--bs-danger-text-emphasis); }
    .holiday-special   { background: rgba(var(--bs-warning-rgb), .12); color: var(--bs-warning-text-emphasis); }
    .calendar-nav-btn {
        width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: .375rem;
    }
    .stat-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .78rem; font-weight: 600;
        padding: .2rem .6rem;
        border-radius: 2rem;
        border: 1px solid var(--bs-border-color);
    }
    .completed-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: var(--bs-success);
        display: inline-block;
        flex-shrink: 0;
    }
    .filter-btn.active { opacity: 1; }
    .filter-btn { opacity: .65; }
    .filter-btn:hover { opacity: 1; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">My Schedule</h4>
        <small class="text-secondary">View your work schedule and shift rotation</small>
    </div>
    <span class="stat-chip">
        <span class="completed-dot"></span>
        <span id="header-today-label">Loading…</span>
    </span>
</div>

{{-- Today's Schedule Card --}}
<div class="card mb-4 border-primary border-opacity-25">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h5 class="card-title mb-0 fw-bold">Today's Schedule</h5>
            <small class="text-secondary" id="today-label">—</small>
        </div>
        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3">
            ● Active
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded border bg-body-secondary">
                    <div class="p-2 rounded bg-body" id="shift-icon-wrap">
                        <i class="bi bi-sun fs-5 text-warning" id="shift-icon"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">Shift Type</div>
                        <div class="fw-semibold" id="today-shift-type">—</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded border bg-body-secondary">
                    <div class="p-2 rounded bg-body">
                        <i class="bi bi-clock fs-5 text-success"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">Time In</div>
                        <div class="fw-semibold" id="today-time-in">Not clocked in</div>
                        <div class="text-secondary" style="font-size:.7rem" id="today-sched-start">Scheduled: —</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded border bg-body-secondary">
                    <div class="p-2 rounded bg-body">
                        <i class="bi bi-clock-history fs-5 text-secondary"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">Time Out</div>
                        <div class="fw-semibold" id="today-time-out">Not clocked out</div>
                        <div class="text-secondary" style="font-size:.7rem" id="today-sched-end">Scheduled: —</div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Holiday Banner (hidden by default) --}}
        <div id="today-holiday-banner" class="alert alert-secondary border mt-3 mb-0 py-2 d-none" role="alert">
            <div class="d-flex align-items-center justify-content-between">
                <span class="fw-semibold" id="today-holiday-name">—</span>
                <span class="badge bg-secondary-subtle text-secondary-emphasis ms-2" id="today-holiday-rate">—</span>
            </div>
        </div>
    </div>
</div>

{{-- Calendar Card --}}
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            {{-- Month Navigation --}}
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-secondary calendar-nav-btn" id="prev-month">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="fw-bold fs-6" id="month-label" style="min-width:160px; text-align:center">—</span>
                <button class="btn btn-sm btn-secondary calendar-nav-btn" id="next-month">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            {{-- Shift Filters --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button class="btn btn-sm btn-outline-secondary filter-btn active" data-filter="all">All Days</button>
                <button class="btn btn-sm btn-outline-warning filter-btn" data-filter="Day">
                    <i class="bi bi-sun"></i> Day <span id="day-count" class="ms-1">0</span>
                </button>
                <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="Night">
                    <i class="bi bi-moon"></i> Night <span id="night-count" class="ms-1">0</span>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-2 p-md-3">
        {{-- Weekday Headers --}}
        <div class="row g-1 mb-1" id="weekday-headers">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
            <div class="col"><div class="weekday-header">{{ $d }}</div></div>
            @endforeach
        </div>
        {{-- Calendar Grid --}}
        <div class="row g-1" id="calendar-grid">
            {{-- Populated by JS --}}
        </div>
        {{-- Legend --}}
        <div class="border-top mt-3 pt-3">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <span class="shift-badge shift-day"><i class="bi bi-sun"></i> Day</span>
                </div>
                <div class="col-auto">
                    <span class="shift-badge shift-night"><i class="bi bi-moon"></i> Night</span>
                </div>
                <div class="col-auto">
                    <span class="shift-badge shift-leave"><i class="bi bi-umbrella"></i> Leave</span>
                </div>
                <div class="col-auto">
                    <span class="shift-badge shift-rest">Rest Day</span>
                </div>
                <div class="col-auto">
                    <span class="shift-badge">
                        <span class="completed-dot"></span> Completed
                    </span>
                </div>
                <div class="col-auto">
                    <span class="shift-badge" style="border:1px solid var(--bs-primary-border-subtle)">Today</span>
                </div>
                <div class="col-auto">
                    <span class="shift-badge text-danger"><i class="bi bi-calendar-event"></i> Holiday</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Shift Pattern Info --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clock me-2"></i>Shift Pattern</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between p-3 rounded border mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-sun text-warning fs-5"></i>
                        <span class="fw-semibold">Day Shift</span>
                    </div>
                    <span class="text-secondary small">6:00 AM – 4:00 PM (2h break)</span>
                </div>
                <div class="d-flex align-items-center justify-content-between p-3 rounded border">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-moon text-primary fs-5"></i>
                        <span class="fw-semibold">Night Shift</span>
                    </div>
                    <span class="text-secondary small">10:00 PM – 6:00 AM (no break)</span>
                </div>
                <p class="text-secondary small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Schedule follows an automated day/night rotation pattern.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-flag me-2"></i>Holiday Pay Rules</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th class="ps-3">Type</th>
                            <th class="text-end pe-3">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold">Regular Holiday</span>
                                <div class="text-secondary" style="font-size:.7rem">e.g. New Year, Christmas</div>
                            </td>
                            <td class="text-end pe-3">
                                <span class="badge bg-danger-subtle text-danger-emphasis fw-bold">200%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold">Special Non-Working</span>
                                <div class="text-secondary" style="font-size:.7rem">e.g. EDSA Anniversary</div>
                            </td>
                            <td class="text-end pe-3">
                                <span class="badge bg-warning-subtle text-warning-emphasis fw-bold">130%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold">Islamic Holidays</span>
                                <div class="text-secondary" style="font-size:.7rem">Movable dates</div>
                            </td>
                            <td class="text-end pe-3">
                                <span class="badge bg-success-subtle text-success-emphasis fw-bold">130%</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold">No Work on Regular Holiday</span>
                                <div class="text-secondary" style="font-size:.7rem">Rest</div>
                            </td>
                            <td class="text-end pe-3">
                                <span class="badge bg-secondary-subtle text-secondary-emphasis fw-bold">100%</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Shift Swap Confirmation Modal --}}
<div class="modal fade" id="shiftSwapModal" tabindex="-1" aria-labelledby="shiftSwapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="shiftSwapModalLabel">Confirm Shift Swap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="swap-modal-body">
                    {{-- Populated by JS --}}
                </div>
                <div class="alert alert-secondary border mt-3 mb-0 small">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>First Come First Serve (FCFS):</strong> Once confirmed, this slot is immediately assigned to you.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-swap-btn">
                    <i class="bi bi-check2-circle me-1"></i> Confirm Swap
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CURRENT_USER = {
    id: 'U001',
    fullName: 'Juan dela Cruz',
    department: 'Operations',
    defaultShift: 'Day',      // 'Day' | 'Night'
    shiftRotationWeek: 1,
};

// Philippine holidays for the current year – keyed by YYYY-MM-DD
const HOLIDAYS = {
    '2025-01-01': { name: "New Year's Day",         type: 'regular' },
    '2025-04-09': { name: 'Araw ng Kagitingan',      type: 'regular' },
    '2025-04-17': { name: 'Maundy Thursday',         type: 'regular' },
    '2025-04-18': { name: 'Good Friday',             type: 'regular' },
    '2025-04-19': { name: 'Black Saturday',          type: 'special' },
    '2025-05-01': { name: 'Labor Day',               type: 'regular' },
    '2025-06-12': { name: 'Independence Day',        type: 'regular' },
    '2025-08-25': { name: 'National Heroes Day',     type: 'regular' },
    '2025-11-01': { name: "All Saints' Day",         type: 'special' },
    '2025-11-02': { name: "All Souls' Day",          type: 'special' },
    '2025-11-30': { name: 'Bonifacio Day',           type: 'regular' },
    '2025-12-08': { name: 'Immaculate Conception',   type: 'special' },
    '2025-12-24': { name: 'Christmas Eve',           type: 'special' },
    '2025-12-25': { name: 'Christmas Day',           type: 'regular' },
    '2025-12-30': { name: 'Rizal Day',               type: 'regular' },
    '2025-12-31': { name: "New Year's Eve",          type: 'special' },
};

// Sample attendance records  { date: 'YYYY-MM-DD', timeIn: '06:02', timeOut: '16:05' }
const ATTENDANCE = [
    { date: todayStr(-2), timeIn: '06:01', timeOut: '16:03' },
    { date: todayStr(-1), timeIn: '06:00', timeOut: '16:00' },
];

// Sample approved leave requests
const LEAVE_REQUESTS = [
    // { startDate: '2025-07-14', endDate: '2025-07-15', type: 'Vacation Leave', status: 'approved' },
];

// Sample available shift-swap slots for this employee
const SHIFT_SWAP_SLOTS = [
    // { id: 'SS001', date: '2025-07-18', originalEmployeeName: 'Maria Santos', originalShift: 'Night', startTime: '22:00', endTime: '06:00' },
];

// Rest days for this employee (0 = Sun, 6 = Sat)
const REST_DAYS = [0]; // Sunday off by default

// =======================================================
//  HELPERS
// =======================================================
function todayStr(offset = 0) {
    const d = new Date();
    d.setDate(d.getDate() + offset);
    return fmtDate(d);
}

function fmtDate(d) {
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

function fmtDayOfWeek(dateStr) {
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const d = new Date(dateStr + 'T12:00:00');
    return days[d.getDay()];
}

// Shift rotation: alternates each week based on employee's defaultShift
function getExpectedShift(dateStr, userId = CURRENT_USER.id, defaultShift = CURRENT_USER.defaultShift) {
    const d = new Date(dateStr + 'T12:00:00');
    const dayOfWeek = d.getDay();

    // Rest day
    if (REST_DAYS.includes(dayOfWeek)) return 'Off';

    // Week number since epoch (simple rotation every 2 weeks)
    const epoch = new Date('2024-01-01T00:00:00');
    const weekNum = Math.floor((d - epoch) / (7 * 86400000));
    const isEvenWeek = weekNum % 2 === 0;

    if (defaultShift === 'Day') {
        return isEvenWeek ? 'Day' : 'Night';
    } else {
        return isEvenWeek ? 'Night' : 'Day';
    }
}

function getShiftTimes(shift) {
    if (shift === 'Day')   return { start: '06:00', end: '16:00' };
    if (shift === 'Night') return { start: '22:00', end: '06:00' };
    return { start: '—', end: '—' };
}

function getHoliday(dateStr)     { return HOLIDAYS[dateStr] || null; }
function getAttendance(dateStr)  { return ATTENDANCE.find(a => a.date === dateStr) || null; }
function getLeave(dateStr) {
    return LEAVE_REQUESTS.find(r => {
        if (r.status !== 'approved') return false;
        return dateStr >= r.startDate && dateStr <= r.endDate;
    }) || null;
}
function getSwapSlot(dateStr)    { return SHIFT_SWAP_SLOTS.find(s => s.date === dateStr) || null; }

// =======================================================
//  STATE
// =======================================================
let viewMonth = new Date();
let activeFilter = 'all';
let pendingSwap = null;

// =======================================================
//  RENDER TODAY'S SCHEDULE
// =======================================================
function renderToday() {
    const dateStr = fmtDate(new Date());
    const dow     = fmtDayOfWeek(dateStr);
    const shift   = getExpectedShift(dateStr);
    const times   = getShiftTimes(shift);
    const att     = getAttendance(dateStr);
    const leave   = getLeave(dateStr);
    const holiday = getHoliday(dateStr);

    document.getElementById('header-today-label').textContent = `${dow}, ${dateStr}`;
    document.getElementById('today-label').textContent        = `${dow}, ${dateStr}`;

    // Shift type
    const shiftLabel = leave ? `On Leave (${leave.type})` : shift === 'Off' ? 'Rest Day' : `${shift} Shift`;
    document.getElementById('today-shift-type').textContent = shiftLabel;

    // Icon
    const iconEl = document.getElementById('shift-icon');
    iconEl.className = 'bi fs-5';
    if (leave)         { iconEl.classList.add('bi-umbrella', 'text-secondary'); }
    else if (shift === 'Day')   { iconEl.classList.add('bi-sun', 'text-warning'); }
    else if (shift === 'Night') { iconEl.classList.add('bi-moon', 'text-primary'); }
    else               { iconEl.classList.add('bi-dash-circle', 'text-secondary'); }

    // Time in / out
    document.getElementById('today-time-in').textContent     = att?.timeIn  || '—';
    document.getElementById('today-time-out').textContent    = att?.timeOut || '—';
    document.getElementById('today-sched-start').textContent = `Scheduled: ${times.start}`;
    document.getElementById('today-sched-end').textContent   = `Scheduled: ${times.end}`;

    // Holiday banner
    if (holiday) {
        const rateMap = { regular: '200%', special: '130%' };
        document.getElementById('today-holiday-name').textContent = holiday.name;
        document.getElementById('today-holiday-rate').textContent = rateMap[holiday.type] || '—';
        document.getElementById('today-holiday-banner').classList.remove('d-none');
    }
}

// =======================================================
//  RENDER CALENDAR
// =======================================================
function renderCalendar() {
    const year  = viewMonth.getFullYear();
    const month = viewMonth.getMonth();
    const today = fmtDate(new Date());

    document.getElementById('month-label').textContent =
        viewMonth.toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });

    // Build day array (42 cells, 6 weeks)
    const firstDay  = new Date(year, month, 1);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - startDate.getDay());

    let dayShiftCount = 0, nightShiftCount = 0;
    const grid = document.getElementById('calendar-grid');
    grid.innerHTML = '';

    for (let i = 0; i < 42; i++) {
        const cellDate   = new Date(startDate);
        cellDate.setDate(startDate.getDate() + i);
        const dateStr    = fmtDate(cellDate);
        const isThisMonth = cellDate.getMonth() === month;

        const shift   = getExpectedShift(dateStr);
        const times   = getShiftTimes(shift);
        const holiday = getHoliday(dateStr);
        const leave   = getLeave(dateStr);
        const att     = getAttendance(dateStr);
        const slot    = isThisMonth ? getSwapSlot(dateStr) : null;
        const isToday = dateStr === today;
        const completed = !!(att?.timeIn && att?.timeOut);

        if (isThisMonth) {
            if (shift === 'Day')   dayShiftCount++;
            if (shift === 'Night') nightShiftCount++;
        }

        // Filter dimming
        let dimmed = false;
        if (activeFilter !== 'all' && isThisMonth && shift !== activeFilter) dimmed = true;

        // Cell classes
        let cellClass = 'calendar-day col';
        if (!isThisMonth) cellClass += ' is-off-month';
        if (isToday)      cellClass += ' is-today';
        if (shift === 'Off') cellClass += ' is-rest';
        if (slot)         cellClass += ' has-slot';
        if (dimmed)       cellClass += ' opacity-25';

        // Badge HTML
        let badgeHtml = '';
        if (isThisMonth) {
            if (leave) {
                badgeHtml = `<span class="shift-badge shift-leave"><i class="bi bi-umbrella"></i> Leave</span>`;
            } else if (shift === 'Day') {
                badgeHtml = `<span class="shift-badge shift-day"><i class="bi bi-sun"></i> Day</span>`;
            } else if (shift === 'Night') {
                badgeHtml = `<span class="shift-badge shift-night"><i class="bi bi-moon"></i> Night</span>`;
            } else {
                badgeHtml = `<span class="shift-badge shift-rest">Rest</span>`;
            }
        }

        // Time label
        let timeHtml = '';
        if (isThisMonth && shift !== 'Off' && !leave) {
            timeHtml = `<div class="time-label">${times.start} – ${times.end}</div>`;
        }

        // Holiday tag
        let holidayHtml = '';
        if (isThisMonth && holiday) {
            const cls = holiday.type === 'regular' ? 'holiday-regular' : 'holiday-special';
            holidayHtml = `<span class="holiday-tag ${cls}">${holiday.name}</span>`;
        }

        // Shift swap available slot
        let swapHtml = '';
        if (slot) {
            swapHtml = `<div class="mt-1 p-1 rounded border border-success bg-success-subtle" style="font-size:.62rem; cursor:pointer;"
                data-slot-id="${slot.id}" data-slot-date="${dateStr}" data-slot-shift="${slot.originalShift}"
                data-slot-from="${slot.originalEmployeeName}">
                <i class="bi bi-arrow-left-right text-success"></i>
                <span class="text-success fw-semibold">Slot: ${slot.originalShift}</span>
                <div class="text-secondary" style="font-size:.58rem">from ${slot.originalEmployeeName}</div>
            </div>`;
        }

        // Completed dot
        const completedHtml = completed && isThisMonth
            ? `<span class="completed-dot ms-1" title="Completed"></span>` : '';

        grid.insertAdjacentHTML('beforeend', `
            <div class="${cellClass}" data-date="${dateStr}">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="day-number ${isToday ? 'text-primary' : ''}">${cellDate.getDate()}</span>
                    ${completedHtml}
                </div>
                ${badgeHtml}
                ${timeHtml}
                ${holidayHtml}
                ${swapHtml}
            </div>
        `);
    }

    document.getElementById('day-count').textContent   = dayShiftCount;
    document.getElementById('night-count').textContent = nightShiftCount;

    // Bind swap slot clicks
    grid.querySelectorAll('[data-slot-id]').forEach(el => {
        el.addEventListener('click', e => {
            e.stopPropagation();
            pendingSwap = {
                id:   el.dataset.slotId,
                date: el.dataset.slotDate,
                shift: el.dataset.slotShift,
                from: el.dataset.slotFrom,
            };
            openSwapModal(pendingSwap);
        });
    });
}

// =======================================================
//  SHIFT SWAP MODAL
// =======================================================
function openSwapModal(swap) {
    const bodyEl = document.getElementById('swap-modal-body');
    bodyEl.innerHTML = `
        <div class="col-6">
            <div class="p-2 rounded border bg-body-secondary small">
                <div class="text-secondary">Date</div>
                <div class="fw-semibold">${swap.date}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-2 rounded border bg-body-secondary small">
                <div class="text-secondary">New Shift</div>
                <div class="fw-semibold">${swap.shift}</div>
            </div>
        </div>
        <div class="col-12">
            <div class="p-2 rounded border bg-body-secondary small">
                <div class="text-secondary">Slot offered by</div>
                <div class="fw-semibold">${swap.from}</div>
            </div>
        </div>
    `;
    const modal = new bootstrap.Modal(document.getElementById('shiftSwapModal'));
    modal.show();
}

document.getElementById('confirm-swap-btn').addEventListener('click', function () {
    if (!pendingSwap) return;
    // TODO: AJAX call to /schedule/swap-slot with pendingSwap data
    Swal.fire({
        icon: 'success',
        title: 'Swap Confirmed!',
        text: `You now have a ${pendingSwap.shift} shift on ${pendingSwap.date}.`,
        confirmButtonColor: 'var(--bs-primary)',
    });
    // Remove slot from sample data
    const idx = SHIFT_SWAP_SLOTS.findIndex(s => s.id === pendingSwap.id);
    if (idx > -1) SHIFT_SWAP_SLOTS.splice(idx, 1);
    pendingSwap = null;
    bootstrap.Modal.getInstance(document.getElementById('shiftSwapModal')).hide();
    renderCalendar();
});

// =======================================================
//  NAVIGATION & FILTERS
// =======================================================
document.getElementById('prev-month').addEventListener('click', () => {
    viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth() - 1, 1);
    renderCalendar();
});
document.getElementById('next-month').addEventListener('click', () => {
    viewMonth = new Date(viewMonth.getFullYear(), viewMonth.getMonth() + 1, 1);
    renderCalendar();
});

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeFilter = this.dataset.filter;
        renderCalendar();
    });
});

// =======================================================
//  INIT
// =======================================================
document.addEventListener('DOMContentLoaded', () => {
    renderToday();
    renderCalendar();
});
</script>
@endpush