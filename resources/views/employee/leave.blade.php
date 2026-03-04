@extends('layouts.main')

@section('title', 'Leave Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Leave Management</li>
    </ol>
@endsection

@section('content')

{{-- ── Leave Balance Cards ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-4" id="leaveBalanceCards"></div>

{{-- ── Leave Application Form (hidden by default) ─────────────────────── --}}
<div class="card mb-4 d-none" id="leaveFormCard">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0 fw-semibold" id="leaveFormTitle">New Leave Application</h3>
        <button type="button" class="btn btn-sm btn-secondary" id="cancelLeaveBtn">
            <i class="bi bi-x-lg me-1"></i>Cancel
        </button>
    </div>
    <div class="card-body">
        <div class="row g-4">

            {{-- LEFT: Interactive Calendar --}}
            <div class="col-lg-6">
                <label class="form-label fw-semibold text-muted small text-uppercase" style="letter-spacing:.05em;font-size:10px">
                    Select Leave Dates
                </label>

                {{-- Month navigation --}}
                <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="prevMonth">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="fw-semibold" id="calendarMonthLabel"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="nextMonth">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                {{-- Weekday headers --}}
                <div class="row row-cols-7 g-0 text-center mb-1" id="calWeekdays"></div>

                {{-- Calendar Grid --}}
                <div class="row row-cols-7 g-1 text-center" id="calendarGrid"></div>

                {{-- Selection hint --}}
                <div class="mt-2" id="selectionInfo">
                    <div class="alert alert-secondary py-2 mb-2 small" id="selectionHint">
                        <i class="bi bi-info-circle me-1"></i>Click a date to set your leave start date.
                    </div>
                </div>

                {{-- Summary when both dates picked --}}
                <div class="card bg-body-secondary border-0 mb-2 d-none" id="leaveSummaryBox">
                    <div class="card-body py-2 px-3">
                        <p class="mb-1 small fw-semibold text-muted text-uppercase" style="font-size:10px;letter-spacing:.05em">Leave Summary</p>
                        <div class="d-flex justify-content-between small border-bottom pb-1 mb-1">
                            <span class="text-muted">Start Date</span>
                            <span class="fw-semibold" id="summaryStart">—</span>
                        </div>
                        <div class="d-flex justify-content-between small border-bottom pb-1 mb-1">
                            <span class="text-muted">End Date</span>
                            <span class="fw-semibold" id="summaryEnd">—</span>
                        </div>
                        <div class="d-flex justify-content-between small border-bottom pb-1 mb-1">
                            <span class="text-muted">Working Days</span>
                            <span class="fw-semibold text-primary" id="summaryDays">—</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Available Balance</span>
                            <span class="fw-semibold" id="summaryBalance">—</span>
                        </div>
                    </div>
                </div>

                {{-- Balance warning --}}
                <div class="alert alert-secondary py-2 small d-none mb-2" id="balanceWarning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <span id="balanceWarningText"></span>
                </div>

                {{-- Legend --}}
                <div class="d-flex flex-wrap gap-3 mt-1">
                    <span class="d-flex align-items-center gap-1 small text-muted">
                        <span class="d-inline-block rounded-1 bg-primary" style="width:14px;height:14px"></span> Selected
                    </span>
                    <span class="d-flex align-items-center gap-1 small text-muted">
                        <span class="d-inline-block rounded-1 border border-primary" style="width:14px;height:14px;background:rgba(var(--bs-primary-rgb),.12)"></span> In Range
                    </span>
                    <span class="d-flex align-items-center gap-1 small text-muted">
                        <span class="d-inline-block rounded-1 bg-secondary bg-opacity-25" style="width:14px;height:14px"></span> Used / Off
                    </span>
                </div>
            </div>

            {{-- RIGHT: Form fields --}}
            <div class="col-lg-6">

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase" style="letter-spacing:.05em;font-size:10px">Leave Type</label>
                    <div class="form-control bg-body-secondary fw-semibold" id="selectedLeaveTypeDisplay">—</div>
                    <input type="hidden" id="selectedLeaveType">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase" style="letter-spacing:.05em;font-size:10px">
                        Reason <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="leaveReason" rows="5"
                        placeholder="Briefly describe the reason for your leave..."></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex">
                    <button type="button" class="btn btn-primary flex-grow-1" id="submitLeaveBtn" disabled>
                        <i class="bi bi-send me-1"></i>Submit Application
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="resetDatesBtn" title="Clear date selection">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>

        </div>{{-- /row --}}
    </div>
</div>

{{-- ── Leave History ────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0 fw-semibold">Leave Application History</h3>
        <div class="card-tools d-flex gap-2">
            <select class="form-select form-select-sm" id="historyStatusFilter" style="width:130px">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <select class="form-select form-select-sm" id="historyTypeFilter" style="width:160px">
                <option value="">All Types</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="leaveHistoryEmpty" class="text-center py-5 text-muted d-none">
            <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
            <p class="mb-0">No leave requests found.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="leaveHistoryTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:36px">#</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Submitted</th>
                        <th>Reviewed By</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody id="leaveHistoryBody"></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ============================================================
//  SAMPLE DATA  (replace with Blade / AJAX when connecting DB)
// ============================================================
const currentUser = {
    id: 'EMP001',
    fullName: 'Maria Santos',
    gender: 'Female',        // 'Male' | 'Female'
    civilStatus: 'Single',   // 'Married' for paternity
    department: 'Accounting',
    defaultShift: 'Day',
    dayOffs: ['Sunday'],     // days with no work
};

const leaveTypes = [
    {
        id: 'vl',
        name: 'Vacation Leave',
        isPaid: true,
        maxDaysPerYear: 5,
        balance: 5,
        colorClass: 'primary',
        icon: 'bi-sun',
        description: 'For rest and recreation',
        minDateRule: 'next-monday',    // 'today' | 'next-monday'
    },
    {
        id: 'sl',
        name: 'Sick Leave',
        isPaid: true,
        maxDaysPerYear: 5,
        balance: 4,
        colorClass: 'secondary',
        icon: 'bi-heart-pulse',
        description: 'For illness or medical appointments',
        minDateRule: 'today',
    },
    {
        id: 'el',
        name: 'Emergency Leave',
        isPaid: true,
        maxDaysPerYear: 5,
        balance: 5,
        colorClass: 'secondary',
        icon: 'bi-lightning-charge',
        description: 'For unforeseen emergencies',
        minDateRule: 'today',
    },
    {
        id: 'ml',
        name: 'Maternity Leave',
        isPaid: true,
        maxDaysPerYear: 105,
        balance: 105,
        colorClass: 'primary',
        icon: 'bi-person-heart',
        description: 'Paid by SSS – company advances first',
        minDateRule: 'next-monday',
        genderRequired: 'Female',
    },
    {
        id: 'pl',
        name: 'Paternity Leave',
        isPaid: true,
        maxDaysPerYear: 7,
        balance: 7,
        colorClass: 'primary',
        icon: 'bi-person-heart',
        description: 'For married male employees',
        minDateRule: 'next-monday',
        genderRequired: 'Male',
        civilStatusRequired: 'Married',
    },
];

let leaveHistory = [
    {
        id: 'LR001', employeeId: 'EMP001',
        type: 'Vacation Leave',
        startDate: '2025-07-07', endDate: '2025-07-09', days: 3,
        reason: 'Family vacation to Tagaytay.',
        status: 'approved',
        submittedDate: '2025-06-28',
        reviewedBy: 'HR Manager',
        rejectionReason: null,
    },
    {
        id: 'LR002', employeeId: 'EMP001',
        type: 'Sick Leave',
        startDate: '2025-07-14', endDate: '2025-07-14', days: 1,
        reason: 'Fever and flu symptoms.',
        status: 'pending',
        submittedDate: '2025-07-14',
        reviewedBy: null,
        rejectionReason: null,
    },
    {
        id: 'LR003', employeeId: 'EMP001',
        type: 'Emergency Leave',
        startDate: '2025-06-02', endDate: '2025-06-03', days: 2,
        reason: 'Father was hospitalized unexpectedly.',
        status: 'rejected',
        submittedDate: '2025-06-01',
        reviewedBy: 'HR Manager',
        rejectionReason: 'Insufficient documentation provided. Please resubmit with hospital documents.',
    },
];

// ============================================================
//  STATE
// ============================================================
let selectedLeaveType = null;   // full leave type object
let calendarViewDate  = new Date();
let startDate         = null;   // 'YYYY-MM-DD'
let endDate           = null;

// ============================================================
//  UTILITIES
// ============================================================
function toYMD(d) {
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

function fmt(ds) {
    if (!ds) return '—';
    const [y,m,d] = ds.split('-');
    return `${d}/${m}/${y}`;
}

const DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
function dayOfWeekName(ds) { return DAY_NAMES[new Date(ds + 'T00:00:00').getDay()]; }
function isDayOff(ds)      { return currentUser.dayOffs.includes(dayOfWeekName(ds)); }

function countWorkingDays(s, e) {
    let n = 0;
    const cur = new Date(s + 'T00:00:00');
    const end = new Date(e + 'T00:00:00');
    while (cur <= end) { if (!isDayOff(toYMD(cur))) n++; cur.setDate(cur.getDate()+1); }
    return n;
}

function getNextMonday() {
    const t   = new Date();
    const dow = t.getDay();
    const add = dow === 0 ? 8 : (8 - dow);
    const nm  = new Date(t);
    nm.setDate(t.getDate() + add);
    return toYMD(nm);
}

function getMinDate() {
    if (!selectedLeaveType) return toYMD(new Date());
    return selectedLeaveType.minDateRule === 'today' ? toYMD(new Date()) : getNextMonday();
}

function getUsedDates(typeName) {
    const out = [];
    leaveHistory
        .filter(r => r.type === typeName && ['approved','pending'].includes(r.status))
        .forEach(r => {
            const cur = new Date(r.startDate + 'T00:00:00');
            const end = new Date(r.endDate   + 'T00:00:00');
            while (cur <= end) { out.push(toYMD(cur)); cur.setDate(cur.getDate()+1); }
        });
    return out;
}

function isSelectable(ds) {
    if (ds < getMinDate()) return false;
    if (isDayOff(ds))      return false;
    if (getUsedDates(selectedLeaveType?.name || '').includes(ds)) return false;
    return true;
}

function calcBalance(lt) {
    const used = leaveHistory
        .filter(r => r.type === lt.name && r.status === 'approved')
        .reduce((s, r) => s + r.days, 0);
    return { available: lt.balance - used, used, total: lt.balance };
}

function visibleLeaveTypes() {
    return leaveTypes.filter(lt => {
        if (lt.genderRequired && lt.genderRequired !== currentUser.gender) return false;
        if (lt.civilStatusRequired && lt.civilStatusRequired !== currentUser.civilStatus) return false;
        return true;
    });
}

function newId() { return 'LR' + String(Date.now()).slice(-6); }

// ============================================================
//  RENDER BALANCE CARDS
// ============================================================
function renderBalanceCards() {
    const wrap = document.getElementById('leaveBalanceCards');
    wrap.innerHTML = '';

    visibleLeaveTypes().forEach(lt => {
        const bal = calcBalance(lt);
        const pct = bal.total > 0 ? Math.round((bal.available / bal.total) * 100) : 0;

        const col  = document.createElement('div');
        col.className = 'col-sm-6 col-xl-3';
        col.innerHTML = `
            <div class="card h-100 border-0 shadow-sm" style="cursor:pointer" data-lt-id="${lt.id}">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-0 fw-semibold text-uppercase" style="font-size:10px;letter-spacing:.05em">${lt.name}</p>
                            <h2 class="fw-bold mb-0 text-${lt.colorClass}" style="line-height:1">${bal.available}</h2>
                            <small class="text-muted">days available</small>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-${lt.colorClass} bg-${lt.colorClass} bg-opacity-10" style="width:44px;height:44px;font-size:20px">
                            <i class="bi ${lt.icon}"></i>
                        </div>
                    </div>
                    <div class="progress mb-2" style="height:4px;border-radius:2px">
                        <div class="progress-bar bg-${lt.colorClass}" style="width:${pct}%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Used: <strong>${bal.used}</strong></small>
                        <small class="text-muted">Max: <strong>${bal.total}</strong></small>
                    </div>
                    ${lt.description ? `<p class="text-muted mt-2 mb-0" style="font-size:11px">${lt.description}</p>` : ''}
                </div>
            </div>`;

        col.querySelector('.card').addEventListener('click', () => openLeaveForm(lt));
        wrap.appendChild(col);
    });
}

// ============================================================
//  OPEN LEAVE FORM
// ============================================================
function openLeaveForm(lt) {
    selectedLeaveType = lt;
    startDate = null;
    endDate   = null;
    calendarViewDate  = new Date();

    document.getElementById('leaveFormCard').classList.remove('d-none');
    document.getElementById('leaveFormTitle').textContent     = `New ${lt.name} Application`;
    document.getElementById('selectedLeaveTypeDisplay').textContent = lt.name;
    document.getElementById('selectedLeaveType').value        = lt.name;
    document.getElementById('leaveReason').value              = '';
    document.getElementById('leaveSummaryBox').classList.add('d-none');
    document.getElementById('balanceWarning').classList.add('d-none');
    document.getElementById('submitLeaveBtn').disabled        = true;
    document.getElementById('selectionHint').innerHTML =
        `<i class="bi bi-info-circle me-1"></i>Click a date to start. Minimum date: <strong>${fmt(getMinDate())}</strong>.`;

    renderCalendar();
    document.getElementById('leaveFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ============================================================
//  RENDER WEEKDAY HEADERS (once)
// ============================================================
(function renderWeekdays() {
    const row = document.getElementById('calWeekdays');
    ['Su','Mo','Tu','We','Th','Fr','Sa'].forEach(d => {
        const col = document.createElement('div');
        col.className = 'col';
        col.innerHTML = `<small class="text-muted fw-bold" style="font-size:10px">${d}</small>`;
        row.appendChild(col);
    });
})();

// ============================================================
//  RENDER CALENDAR
// ============================================================
function renderCalendar() {
    const year  = calendarViewDate.getFullYear();
    const month = calendarViewDate.getMonth();

    document.getElementById('calendarMonthLabel').textContent =
        calendarViewDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

    const firstDow   = new Date(year, month, 1).getDay();
    const daysInMon  = new Date(year, month + 1, 0).getDate();
    const prevLastDay = new Date(year, month, 0).getDate();
    const grid       = document.getElementById('calendarGrid');
    grid.innerHTML   = '';

    const today    = toYMD(new Date());
    const minDt    = getMinDate();
    const usedDts  = getUsedDates(selectedLeaveType?.name || '');

    // Build cell list (always 42 cells)
    const cells = [];
    for (let i = firstDow - 1; i >= 0; i--)
        cells.push({ day: prevLastDay - i, cur: false, ds: null });
    for (let d = 1; d <= daysInMon; d++) {
        const ds = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        cells.push({ day: d, cur: true, ds });
    }
    const rem = 42 - cells.length;
    for (let d = 1; d <= rem; d++)
        cells.push({ day: d, cur: false, ds: null });

    cells.forEach(cell => {
        const col = document.createElement('div');
        col.className = 'col';

        if (!cell.cur) {
            col.innerHTML = `<div class="rounded-2 py-1" style="font-size:11px;color:#ccc;opacity:.3">${cell.day}</div>`;
            grid.appendChild(col);
            return;
        }

        const { ds } = cell;
        const sel   = isSelectable(ds);
        const isS   = ds === startDate;
        const isE   = ds === endDate;
        const inRng = startDate && endDate && ds > startDate && ds < endDate;
        const isToday = ds === today;
        const isUsed  = usedDts.includes(ds);

        let divClass = 'rounded-2 py-1 position-relative';
        let divStyle = 'font-size:12px;';
        let badge    = '';

        if (isS || isE) {
            divClass += ' bg-primary text-white fw-bold';
        } else if (inRng) {
            divClass += ' fw-semibold text-primary';
            divStyle += 'background:rgba(var(--bs-primary-rgb),.1);border:1px solid rgba(var(--bs-primary-rgb),.25);';
        } else if (isUsed) {
            divClass += ' text-muted bg-secondary bg-opacity-10';
            badge = `<span class="position-absolute top-0 end-0" style="font-size:7px;line-height:1;color:var(--bs-secondary)">●</span>`;
        } else if (!sel) {
            divClass += ' text-muted';
            divStyle += 'opacity:.35;';
        } else {
            divClass += ' text-body';
            divStyle += 'cursor:pointer;';
        }

        if (isToday && !isS && !isE) {
            badge += `<span class="position-absolute bottom-0 start-50 translate-middle-x rounded-circle bg-primary" style="width:3px;height:3px"></span>`;
        }

        col.innerHTML = `<div class="${divClass}" style="${divStyle}">${cell.day}${badge}</div>`;

        if (sel) {
            col.querySelector('div').addEventListener('click', () => handleDateClick(ds));
        }

        grid.appendChild(col);
    });
}

// ============================================================
//  DATE CLICK
// ============================================================
function handleDateClick(ds) {
    if (!startDate) {
        startDate = ds; endDate = null;
        setHint(`Start: <strong>${fmt(ds)}</strong> — Select an end date, or <a href="#" class="link-primary" onclick="useSingleDay(event)">submit as 1-day leave</a>.`);
    } else if (!endDate) {
        if (ds === startDate) {
            // Deselect
            startDate = null;
            setHint('Click a date to set your leave start date.');
            renderCalendar(); updateSummary(); return;
        }
        let s = startDate, e = ds;
        if (e < s) { s = ds; e = startDate; }

        const days = countWorkingDays(s, e);
        const bal  = calcBalance(selectedLeaveType);
        if (days > bal.available) {
            Swal.fire({
                icon: 'warning', title: 'Insufficient Balance',
                text: `Selected range is ${days} working day(s) but you only have ${bal.available} available for ${selectedLeaveType.name}.`,
                confirmButtonColor: 'var(--bs-primary)',
            });
            endDate = null; renderCalendar(); return;
        }

        startDate = s; endDate = e;
        setHint(`<i class="bi bi-check-circle text-primary me-1"></i><strong>${fmt(s)}</strong> → <strong>${fmt(e)}</strong> — <span class="text-primary fw-semibold">${days} working day(s)</span>`);
    } else {
        // Both already set — restart
        startDate = ds; endDate = null;
        setHint(`Start: <strong>${fmt(ds)}</strong> — Select an end date.`);
    }

    renderCalendar();
    updateSummary();
}

function useSingleDay(e) {
    e.preventDefault();
    endDate = startDate;
    setHint(`<i class="bi bi-check-circle text-primary me-1"></i><strong>${fmt(startDate)}</strong> — 1 working day`);
    renderCalendar(); updateSummary();
}

function setHint(html) {
    document.getElementById('selectionHint').innerHTML = html;
}

// ============================================================
//  UPDATE SUMMARY
// ============================================================
function updateSummary() {
    const box = document.getElementById('leaveSummaryBox');
    const btn = document.getElementById('submitLeaveBtn');

    if (!startDate) { box.classList.add('d-none'); btn.disabled = true; return; }

    const e    = endDate || startDate;
    const days = countWorkingDays(startDate, e);
    const bal  = calcBalance(selectedLeaveType);

    document.getElementById('summaryStart').textContent   = fmt(startDate);
    document.getElementById('summaryEnd').textContent     = fmt(e);
    document.getElementById('summaryDays').textContent    = `${days} day(s)`;
    document.getElementById('summaryBalance').textContent = `${bal.available} available`;

    box.classList.remove('d-none');

    const warn = document.getElementById('balanceWarning');
    if (bal.available <= 0) {
        document.getElementById('balanceWarningText').textContent =
            `No leave balance remaining for ${selectedLeaveType.name}. Please contact HR.`;
        warn.classList.remove('d-none');
        btn.disabled = true;
    } else if (days > bal.available) {
        document.getElementById('balanceWarningText').textContent =
            `Selected range (${days} days) exceeds your available balance (${bal.available} days).`;
        warn.classList.remove('d-none');
        btn.disabled = true;
    } else {
        warn.classList.add('d-none');
        btn.disabled = false;
    }
}

// ============================================================
//  SUBMIT
// ============================================================
document.getElementById('submitLeaveBtn').addEventListener('click', () => {
    const reason = document.getElementById('leaveReason').value.trim();
    if (!reason) {
        Swal.fire({ icon: 'warning', title: 'Reason Required', text: 'Please enter a reason for your leave.', confirmButtonColor: 'var(--bs-primary)' });
        document.getElementById('leaveReason').focus();
        return;
    }
    if (!startDate) return;

    const finalEnd = endDate || startDate;
    const days     = countWorkingDays(startDate, finalEnd);

    // Duplicate date-overlap check
    const dup = leaveHistory.find(r => {
        if (r.type !== selectedLeaveType.name) return false;
        if (!['pending','approved'].includes(r.status)) return false;
        return startDate <= r.endDate && finalEnd >= r.startDate;
    });

    if (dup) {
        Swal.fire({
            icon: 'error', title: 'Duplicate Request',
            text: `You already have a ${dup.status.toUpperCase()} ${dup.type} request covering these dates.`,
            confirmButtonColor: 'var(--bs-primary)',
        });
        return;
    }

    Swal.fire({
        title: 'Submit Leave Application?',
        html: `<strong>${selectedLeaveType.name}</strong><br><span class="text-muted">${fmt(startDate)} → ${fmt(finalEnd)} &nbsp;|&nbsp; ${days} working day(s)</span>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit',
        cancelButtonText: 'Go Back',
        confirmButtonColor: 'var(--bs-primary)',
    }).then(res => {
        if (!res.isConfirmed) return;

        leaveHistory.unshift({
            id: newId(),
            employeeId: currentUser.id,
            type: selectedLeaveType.name,
            startDate,
            endDate: finalEnd,
            days,
            reason,
            status: 'pending',
            submittedDate: toYMD(new Date()),
            reviewedBy: null,
            rejectionReason: null,
        });

        closeForm();
        renderBalanceCards();
        renderHistory();

        Swal.fire({
            icon: 'success',
            title: 'Application Submitted!',
            text: `${selectedLeaveType.name} for ${days} day(s) has been submitted for approval.`,
            confirmButtonColor: 'var(--bs-primary)',
        });
    });
});

// ============================================================
//  CANCEL / RESET
// ============================================================
function closeForm() {
    document.getElementById('leaveFormCard').classList.add('d-none');
    startDate = null; endDate = null;
}

document.getElementById('cancelLeaveBtn').addEventListener('click', closeForm);

document.getElementById('resetDatesBtn').addEventListener('click', () => {
    startDate = null; endDate = null;
    setHint('Click a date to set your leave start date.');
    document.getElementById('leaveSummaryBox').classList.add('d-none');
    document.getElementById('balanceWarning').classList.add('d-none');
    document.getElementById('submitLeaveBtn').disabled = true;
    renderCalendar();
});

document.getElementById('prevMonth').addEventListener('click', () => {
    calendarViewDate = new Date(calendarViewDate.getFullYear(), calendarViewDate.getMonth() - 1, 1);
    renderCalendar();
});
document.getElementById('nextMonth').addEventListener('click', () => {
    calendarViewDate = new Date(calendarViewDate.getFullYear(), calendarViewDate.getMonth() + 1, 1);
    renderCalendar();
});

// ============================================================
//  RENDER HISTORY TABLE
// ============================================================
function statusBadge(status) {
    const map = {
        approved: 'bg-secondary',
        pending:  'bg-primary',
        rejected: 'bg-secondary',
    };
    const cls = map[status] || 'bg-secondary';
    return `<span class="badge ${cls}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
}

function renderHistory() {
    const sf  = document.getElementById('historyStatusFilter').value;
    const tf  = document.getElementById('historyTypeFilter').value;

    let rows  = leaveHistory.filter(r => r.employeeId === currentUser.id);
    if (sf)   rows = rows.filter(r => r.status === sf);
    if (tf)   rows = rows.filter(r => r.type === tf);

    const tbody = document.getElementById('leaveHistoryBody');
    const empty = document.getElementById('leaveHistoryEmpty');
    const table = document.getElementById('leaveHistoryTable');

    if (rows.length === 0) {
        tbody.innerHTML = '';
        empty.classList.remove('d-none');
        table.classList.add('d-none');
        return;
    }

    empty.classList.add('d-none');
    table.classList.remove('d-none');

    tbody.innerHTML = rows.map((r, i) => `
        <tr>
            <td class="ps-3 text-muted small">${i + 1}</td>
            <td class="fw-semibold">${r.type}</td>
            <td class="text-nowrap small">
                ${fmt(r.startDate)}${r.startDate !== r.endDate ? ` <span class="text-muted">→</span> ${fmt(r.endDate)}` : ''}
            </td>
            <td>
                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold border border-primary border-opacity-25">${r.days}d</span>
            </td>
            <td style="max-width:220px">
                <small class="text-muted text-truncate d-block" title="${r.reason}">${r.reason}</small>
                ${r.status === 'rejected' && r.rejectionReason
                    ? `<small class="text-danger d-block mt-1"><i class="bi bi-x-circle me-1"></i>${r.rejectionReason}</small>`
                    : ''}
            </td>
            <td class="text-nowrap"><small class="text-muted">${fmt(r.submittedDate)}</small></td>
            <td><small class="text-muted">${r.reviewedBy || '—'}</small></td>
            <td>${statusBadge(r.status)}</td>
            <td class="text-end pe-3">
                ${r.status === 'pending'
                    ? `<button class="btn btn-sm btn-outline-secondary" onclick="withdrawRequest('${r.id}')" title="Withdraw">
                           <i class="bi bi-trash3"></i>
                       </button>`
                    : '<span class="text-muted small">—</span>'}
            </td>
        </tr>`).join('');
}

function withdrawRequest(id) {
    Swal.fire({
        title: 'Withdraw this request?',
        text: 'This will permanently remove the leave application.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Withdraw',
        cancelButtonText: 'Keep it',
        confirmButtonColor: 'var(--bs-primary)',
    }).then(res => {
        if (!res.isConfirmed) return;
        leaveHistory = leaveHistory.filter(r => r.id !== id);
        renderBalanceCards();
        renderHistory();
    });
}

// ============================================================
//  POPULATE TYPE FILTER
// ============================================================
(function populateTypeFilter() {
    const sel = document.getElementById('historyTypeFilter');
    visibleLeaveTypes().forEach(lt => {
        const opt = document.createElement('option');
        opt.value = lt.name; opt.textContent = lt.name;
        sel.appendChild(opt);
    });
})();

document.getElementById('historyStatusFilter').addEventListener('change', renderHistory);
document.getElementById('historyTypeFilter').addEventListener('change', renderHistory);

// ============================================================
//  INIT
// ============================================================
renderBalanceCards();
renderHistory();
</script>
@endpush