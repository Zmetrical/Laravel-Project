@extends('layouts.main')

@section('title', 'Team Scheduling')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Team Scheduling</li>
    </ol>
@endsection

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">Employee Scheduling</h4>
        <p class="text-muted mb-0">View and manage automated weekly employee schedules</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-secondary btn-sm" onclick="handlePrint()">
            <i class="fas fa-print me-1"></i> Print Template
        </button>
        <button class="btn btn-primary btn-sm" onclick="handleDownload()">
            <i class="fas fa-download me-1"></i> Download CSV
        </button>
    </div>
</div>

{{-- Stats Row --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Day Shift Coverage</p>
                    <h2 class="mb-0 text-primary" id="statDayCount">0</h2>
                    <p class="text-muted small mb-0">Employees assigned</p>
                </div>
                <i class="fas fa-sun fa-2x text-primary" style="opacity:.15"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Night Shift Coverage</p>
                    <h2 class="mb-0 text-secondary" id="statNightCount">0</h2>
                    <p class="text-muted small mb-0">Employees assigned</p>
                </div>
                <i class="fas fa-moon fa-2x text-secondary" style="opacity:.15"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Team Members</p>
                    <h2 class="mb-0" id="statTotalCount">0</h2>
                    <p class="text-muted small mb-0">Active employees</p>
                </div>
                <i class="fas fa-users fa-2x" style="opacity:.1"></i>
            </div>
        </div>
    </div>
</div>

{{-- Main Schedule Card --}}
<div class="card mb-3">
    <div class="card-header pb-0 border-bottom-0">

        {{-- Week Navigation --}}
        <div class="d-flex align-items-center justify-content-between p-3 mb-3 bg-light rounded border">
            <button class="btn btn-secondary btn-sm" onclick="navigateWeek(-1)">
                <i class="fas fa-chevron-left me-1"></i> Previous Week
            </button>
            <div class="text-center">
                <p class="text-muted small mb-0">Selected Week</p>
                <strong id="weekLabel" class="fs-6">—</strong>
                <p class="text-muted small mb-0 mt-1" id="todayLabel">—</p>
            </div>
            <button class="btn btn-secondary btn-sm" onclick="navigateWeek(1)">
                Next Week <i class="fas fa-chevron-right ms-1"></i>
            </button>
        </div>

        {{-- Filters Row --}}
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label small mb-1">Department</label>
                <select class="form-select form-select-sm" id="filterDept" onchange="onDeptChange()"></select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Position</label>
                <select class="form-select form-select-sm" id="filterPosition" onchange="onPositionChange()">
                    <option value="">All Positions</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Employee</label>
                <select class="form-select form-select-sm" id="filterEmployee" onchange="renderTable()">
                    <option value="">All Employees</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary btn-sm w-100"
                        data-bs-toggle="modal" data-bs-target="#dayOffModal"
                        onclick="openDayOffModal()">
                    <i class="fas fa-cog me-1"></i> Configure Day Offs
                </button>
            </div>
        </div>

        {{-- Shift Filter Bar --}}
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
            <span class="text-muted small me-1">Filter by shift:</span>
            <button id="btnFilterAll"   class="btn btn-primary btn-sm"           onclick="setShiftFilter('All')">
                <i class="fas fa-users me-1"></i><span id="statAll">0</span> Total
            </button>
            <button id="btnFilterDay"   class="btn btn-outline-secondary btn-sm" onclick="setShiftFilter('Day')">
                <i class="fas fa-sun me-1"></i><span id="statDay">0</span> Day
            </button>
            <button id="btnFilterNight" class="btn btn-outline-secondary btn-sm" onclick="setShiftFilter('Night')">
                <i class="fas fa-moon me-1"></i><span id="statNight">0</span> Night
            </button>
        </div>

    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:200px">Employee</th>
                        <th class="text-center" style="min-width:90px">Mon</th>
                        <th class="text-center" style="min-width:90px">Tue</th>
                        <th class="text-center" style="min-width:90px">Wed</th>
                        <th class="text-center" style="min-width:90px">Thu</th>
                        <th class="text-center" style="min-width:90px">Fri</th>
                        <th class="text-center" style="min-width:90px">Sat</th>
                        <th class="text-center" style="min-width:90px">Sun</th>
                    </tr>
                </thead>
                <tbody id="scheduleTableBody">
                    <tr><td colspan="8" class="text-center text-muted py-4">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Legend --}}
<div class="card">
    <div class="card-header">
        <h6 class="card-title mb-0">Schedule Legend</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-2 border rounded">
                    <i class="fas fa-sun text-primary fa-lg"></i>
                    <div>
                        <p class="fw-semibold small mb-0">Day Shift</p>
                        <p class="text-muted small mb-0">06:00 AM – 04:00 PM</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-2 border rounded">
                    <i class="fas fa-moon text-secondary fa-lg"></i>
                    <div>
                        <p class="fw-semibold small mb-0">Night Shift</p>
                        <p class="text-muted small mb-0">10:00 PM – 06:00 AM</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-2 border rounded">
                    <span class="text-muted px-1 fs-5">—</span>
                    <div>
                        <p class="fw-semibold small mb-0">Rest Day / Day Off</p>
                        <p class="text-muted small mb-0">No scheduled work</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- =====================================================================
     MODAL — Day Off Configuration
     ===================================================================== --}}
<div class="modal fade" id="dayOffModal" tabindex="-1" aria-labelledby="dayOffModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dayOffModalLabel">
                    <i class="fas fa-cog me-2 text-primary"></i>Configure Day Offs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Day Selector + Auto --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Select Day to Configure</label>
                        <select class="form-select" id="modalDaySelect" onchange="onModalDayChange()">
                            <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                            <option>Thursday</option><option>Friday</option><option>Saturday</option>
                            <option>Sunday</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end gap-2 flex-wrap">
                        <button class="btn btn-secondary btn-sm" onclick="handleAutoAssignDayOffs()">
                            <i class="fas fa-bolt me-1"></i> Auto-Distribute Day Offs
                        </button>
                        <small class="text-muted align-self-center">
                            Evenly assigns 1 rest day per employee across the week
                        </small>
                    </div>
                </div>

                {{-- Week Distribution Summary --}}
                <div class="mb-4">
                    <p class="text-muted small fw-semibold text-uppercase mb-2">
                        Week Off Distribution — click a day to jump to it
                    </p>
                    <div class="d-flex gap-2 flex-wrap" id="weekSummaryBadges"></div>
                </div>

                <hr>

                {{-- Filters --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" id="modalSearch"
                               placeholder="Search by name or ID…" oninput="renderModalList()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="modalFilterDept"
                                onchange="onModalDeptChange()"></select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="modalFilterPosition"
                                onchange="renderModalList()">
                            <option value="">All Positions</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary btn-sm w-100" onclick="resetModalFilters()">Reset</button>
                    </div>
                </div>

                {{-- Bulk Actions --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-secondary" onclick="selectAllModal()">Select All</button>
                        <button class="btn btn-sm btn-secondary" onclick="deselectAllModal()">Deselect All</button>
                        <span class="badge bg-secondary" id="selectedCount">0 selected</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-secondary" onclick="bulkSetRest()">
                            <i class="fas fa-moon me-1"></i> Set Selected → Rest
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="bulkResetShift()">
                            <i class="fas fa-bolt me-1"></i> Reset Selected → Shift
                        </button>
                    </div>
                </div>

                {{-- Counts --}}
                <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                    <small class="text-muted me-1" id="modalEmpCount">—</small>
                    <span class="badge bg-primary"   id="modalDayCount">0 Day</span>
                    <span class="badge bg-secondary" id="modalNightCount">0 Night</span>
                    <span class="badge text-bg-dark" id="modalRestCount">0 Rest</span>
                </div>

                <div id="modalEmployeeList"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveDayOffConfig()">
                    <i class="fas fa-check-circle me-1"></i> Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

@endsection


@push('styles')
<style>
    @media print {
        .app-header, .app-sidebar, .app-footer,
        .card-header .row, .card-header .d-flex { display: none !important; }
        .app-main { margin: 0 !important; padding: 0 !important; }
        .card     { box-shadow: none !important; border: none !important; }
        .table    { font-size: 11px; }
    }
    #scheduleTableBody td     { vertical-align: middle; }
    .day-cell                 { line-height: 1.35; }
    #modalEmployeeList .emp-row:hover { background: rgba(0,0,0,.03); }
</style>
@endpush


@push('scripts')
<script>

const EMPLOYEES = [
    { id:'U001', name:'Juan dela Cruz',    position:'Senior Developer',  department:'Information Technology', defaultShift:'Day'   },
    { id:'U002', name:'Maria Santos',      position:'Senior Developer',  department:'Information Technology', defaultShift:'Night' },
    { id:'U003', name:'Pedro Reyes',       position:'Junior Developer',  department:'Information Technology', defaultShift:'Day'   },
    { id:'U004', name:'Ana Gomez',         position:'Junior Developer',  department:'Information Technology', defaultShift:'Night' },
    { id:'U005', name:'Jose Villanueva',   position:'QA Engineer',       department:'Information Technology', defaultShift:'Day'   },
    { id:'U006', name:'Luz Mendoza',       position:'QA Engineer',       department:'Information Technology', defaultShift:'Night' },
    { id:'U007', name:'Carlos Bautista',   position:'Team Lead',         department:'Operations',             defaultShift:'Day'   },
    { id:'U008', name:'Elena Cruz',        position:'Operations Staff',  department:'Operations',             defaultShift:'Night' },
    { id:'U009', name:'Ramon Aquino',      position:'Operations Staff',  department:'Operations',             defaultShift:'Day'   },
    { id:'U010', name:'Rosario Dela Vega', position:'Operations Staff',  department:'Operations',             defaultShift:'Night' },
    { id:'U011', name:'Fernando Ocampo',   position:'Logistics Staff',   department:'Operations',             defaultShift:'Day'   },
    { id:'U012', name:'Grace Tolentino',   position:'Logistics Staff',   department:'Operations',             defaultShift:'Night' },
    { id:'U013', name:'Lorna Pascual',     position:'HR Officer',        department:'Human Resources',        defaultShift:'Day'   },
    { id:'U014', name:'Manny Torres',      position:'HR Staff',          department:'Human Resources',        defaultShift:'Night' },
    { id:'U015', name:'Cecile Navarro',    position:'Accountant',        department:'Accounting & Finance',   defaultShift:'Day'   },
    { id:'U016', name:'Albert Flores',     position:'Accounting Staff',  department:'Accounting & Finance',   defaultShift:'Night' },
];

const SAMPLE_LEAVES = [
    { employeeId:'U003', startDate:'2026-03-02', endDate:'2026-03-04', type:'Vacation Leave'  },
    { employeeId:'U008', startDate:'2026-03-03', endDate:'2026-03-03', type:'Sick Leave'      },
    { employeeId:'U015', startDate:'2026-03-05', endDate:'2026-03-05', type:'Emergency Leave' },
];


// ============================================================
//  APPLICATION STATE — single source of truth
// ============================================================
const AppState = {
    weekOffset  : 0,       // 0 = current week, -1 = previous, +1 = next
    shiftFilter : 'All',   // 'All' | 'Day' | 'Night'

    // Live day-off config: { Monday: ['U001', ...], Tuesday: [...], … }
    dayOffConfig : {
        Monday    : ['U001', 'U007', 'U013', 'U015'],
        Tuesday   : ['U002', 'U008', 'U014', 'U016'],
        Wednesday : ['U003', 'U009'],
        Thursday  : ['U004', 'U010'],
        Friday    : ['U005', 'U011'],
        Saturday  : ['U006', 'U012'],
        Sunday    : [],
    },

    // Working copy inside the modal — discarded on Cancel, committed on Save
    tempConfig    : {},
    modalSelected : new Set(),
};


// ============================================================
//  DATE / WEEK HELPERS
// ============================================================
function getCurrentMonday() {
    const today = new Date();
    const dow   = today.getDay();
    const diff  = today.getDate() - dow + (dow === 0 ? -6 : 1);
    const d     = new Date(today);
    d.setDate(diff);
    d.setHours(0, 0, 0, 0);
    return d;
}

function getViewMonday() {
    const base = getCurrentMonday();
    base.setDate(base.getDate() + AppState.weekOffset * 7);
    return base;
}

function getWeekLabel(monday) {
    const sunday = new Date(monday);
    sunday.setDate(sunday.getDate() + 6);
    return monday.toLocaleDateString('en-US', { month:'short', day:'numeric' })
         + ' – '
         + sunday.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
}

// Week number relative to anchor 2026-01-06
// Even = 0,2,4… → keep defaultShift | Odd = 1,3,5… → flip it
function getWeekNumber(monday) {
    const anchor = new Date('2026-01-06T00:00:00');
    return Math.floor((monday - anchor) / (7 * 24 * 60 * 60 * 1000));
}

function toDateStr(d) {
    return d.getFullYear()
         + '-' + String(d.getMonth() + 1).padStart(2, '0')
         + '-' + String(d.getDate()).padStart(2, '0');
}

const DAY_NAMES  = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
const DAY_OFFSET = { Sunday:6, Monday:0, Tuesday:1, Wednesday:2, Thursday:3, Friday:4, Saturday:5 };


// ============================================================
//  SCHEDULE COMPUTATION
// ============================================================
// Priority: Leave > Configured Rest > Weekly Rotation
function getShiftForDate(emp, date) {
    const ds = toDateStr(date);

    const leave = SAMPLE_LEAVES.find(l =>
        l.employeeId === emp.id && ds >= l.startDate && ds <= l.endDate
    );
    if (leave) return { shift:'Leave', time: leave.type };

    const dayName = DAY_NAMES[date.getDay()];
    if ((AppState.dayOffConfig[dayName] || []).includes(emp.id)) {
        return { shift:'Off', time:'—' };
    }

    const isEven = getWeekNumber(getViewMonday()) % 2 === 0;
    let   shift  = emp.defaultShift;
    if (!isEven) shift = shift === 'Day' ? 'Night' : 'Day';

    return { shift, time: shift === 'Day' ? '06:00–16:00' : '22:00–06:00' };
}

function buildSchedule() {
    const monday  = getViewMonday();
    const dayKeys = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    return EMPLOYEES.map(emp => {
        const row = { ...emp };
        dayKeys.forEach((key, i) => {
            const d = new Date(monday);
            d.setDate(monday.getDate() + i);
            row[key] = getShiftForDate(emp, d);
        });
        return row;
    });
}


// ============================================================
//  PAGE FILTERS (cascading dept → position → employee)
// ============================================================
function uniqueDepts()      { return [...new Set(EMPLOYEES.map(e => e.department))]; }
function positionsFor(dept) { return [...new Set(EMPLOYEES.filter(e => e.department === dept).map(e => e.position))]; }

function initPageFilters() {
    const depts = uniqueDepts();
    const el    = document.getElementById('filterDept');
    el.innerHTML = depts.map(d => `<option value="${h(d)}">${h(d)}</option>`).join('');
    el.value     = depts[0] ?? '';
    onDeptChange();
}

function onDeptChange() {
    const dept = document.getElementById('filterDept').value;
    const el   = document.getElementById('filterPosition');
    el.innerHTML = '<option value="">All Positions</option>'
        + positionsFor(dept).map(p => `<option value="${h(p)}">${h(p)}</option>`).join('');
    el.value = '';
    onPositionChange();
}

function onPositionChange() {
    const dept = document.getElementById('filterDept').value;
    const pos  = document.getElementById('filterPosition').value;
    let   emps = EMPLOYEES.filter(e => e.department === dept);
    if (pos) emps = emps.filter(e => e.position === pos);
    const el = document.getElementById('filterEmployee');
    el.innerHTML = '<option value="">All Employees</option>'
        + emps.map(e => `<option value="${e.id}">${h(e.name)}</option>`).join('');
    el.value = '';
    renderTable();
}

function setShiftFilter(val) {
    AppState.shiftFilter = val;
    ['All','Day','Night'].forEach(v => {
        const btn = document.getElementById('btnFilter' + v);
        btn.className = v === val
            ? 'btn btn-sm ' + (v === 'Night' ? 'btn-secondary' : 'btn-primary')
            : 'btn btn-sm btn-outline-secondary';
    });
    renderTable();
}


// ============================================================
//  TABLE RENDER
// ============================================================
function shiftCellHtml(day) {
    if (day.shift === 'Day')
        return `<span class="badge bg-primary">Day</span><br><small class="text-muted">${day.time}</small>`;
    if (day.shift === 'Night')
        return `<span class="badge bg-secondary">Night</span><br><small class="text-muted">${day.time}</small>`;
    if (day.shift === 'Leave')
        return `<span class="badge text-bg-dark">Leave</span><br>
                <small class="text-muted" style="font-size:10px;line-height:1.2">${h(day.time)}</small>`;
    return `<span class="text-muted">—</span>`;
}

function primaryShift(emp) {
    const keys = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    const dc   = keys.filter(k => emp[k].shift === 'Day').length;
    const nc   = keys.filter(k => emp[k].shift === 'Night').length;
    if (!dc && !nc) return null;
    return dc >= nc ? 'Day' : 'Night';
}

function renderTable() {
    const dept  = document.getElementById('filterDept').value;
    const pos   = document.getElementById('filterPosition').value;
    const empId = document.getElementById('filterEmployee').value;
    const full  = buildSchedule();

    let base = full.filter(e => e.department === dept);
    if (pos)   base = base.filter(e => e.position === pos);
    if (empId) base = base.filter(e => e.id === empId);

    // Update counters (always from pre-shift-filter data)
    document.getElementById('statAll').textContent   = base.length;
    document.getElementById('statDay').textContent   = base.filter(e => primaryShift(e) === 'Day').length;
    document.getElementById('statNight').textContent = base.filter(e => primaryShift(e) === 'Night').length;

    const dept_rows = full.filter(e => e.department === dept);
    document.getElementById('statDayCount').textContent   = dept_rows.filter(e => primaryShift(e) === 'Day').length;
    document.getElementById('statNightCount').textContent = dept_rows.filter(e => primaryShift(e) === 'Night').length;
    document.getElementById('statTotalCount').textContent = EMPLOYEES.length;
    document.getElementById('weekLabel').textContent      = getWeekLabel(getViewMonday());
    document.getElementById('todayLabel').textContent     = 'Today: '
        + new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'short', day:'numeric' });

    // Apply shift filter for rows
    let rows = base;
    if (AppState.shiftFilter !== 'All') {
        const sf = AppState.shiftFilter;
        rows = base.filter(e =>
            ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'].some(k => e[k].shift === sf)
        );
    }

    const keys  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    const tbody = document.getElementById('scheduleTableBody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No employees match the filters.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(e => `
        <tr>
            <td>
                <div class="fw-semibold small">${h(e.name)}</div>
                <small class="text-muted">${e.id} &middot; ${h(e.position)}</small>
            </td>
            ${keys.map(k => `<td class="text-center day-cell">${shiftCellHtml(e[k])}</td>`).join('')}
        </tr>`).join('');
}

function navigateWeek(dir) { AppState.weekOffset += dir; renderTable(); }


// ============================================================
//  DAY OFF MODAL
// ============================================================
function openDayOffModal() {
    // Deep-copy live → temp
    AppState.tempConfig = {};
    Object.keys(AppState.dayOffConfig).forEach(d => {
        AppState.tempConfig[d] = [...AppState.dayOffConfig[d]];
    });
    AppState.modalSelected.clear();

    const depts  = uniqueDepts();
    const deptEl = document.getElementById('modalFilterDept');
    deptEl.innerHTML = depts.map(d => `<option value="${h(d)}">${h(d)}</option>`).join('');
    deptEl.value     = document.getElementById('filterDept').value || depts[0];
    onModalDeptChange();
    document.getElementById('modalDaySelect').value = 'Monday';
    renderWeekSummary();
    renderModalList();
}

function onModalDayChange()  { AppState.modalSelected.clear(); renderWeekSummary(); renderModalList(); }

function onModalDeptChange() {
    const dept = document.getElementById('modalFilterDept').value;
    const el   = document.getElementById('modalFilterPosition');
    el.innerHTML = '<option value="">All Positions</option>'
        + positionsFor(dept).map(p => `<option value="${h(p)}">${h(p)}</option>`).join('');
    el.value = '';
    renderModalList();
}

function resetModalFilters() {
    document.getElementById('modalSearch').value = '';
    document.getElementById('modalFilterPosition').value = '';
    renderModalList();
}

function getModalEmployees() {
    const dept   = document.getElementById('modalFilterDept').value;
    const pos    = document.getElementById('modalFilterPosition').value;
    const search = (document.getElementById('modalSearch').value || '').toLowerCase().trim();
    return EMPLOYEES.filter(e => {
        if (dept   && e.department !== dept)   return false;
        if (pos    && e.position   !== pos)    return false;
        if (search && !e.name.toLowerCase().includes(search) && !e.id.toLowerCase().includes(search)) return false;
        return true;
    });
}

// The shift the employee *would* be on (ignores dayOffConfig — for display purposes)
function automatedShiftOnDay(emp) {
    const day    = document.getElementById('modalDaySelect').value;
    const monday = getViewMonday();
    const target = new Date(monday);
    target.setDate(monday.getDate() + DAY_OFFSET[day]);
    const ds = toDateStr(target);
    const onLeave = SAMPLE_LEAVES.find(l =>
        l.employeeId === emp.id && ds >= l.startDate && ds <= l.endDate
    );
    if (onLeave) return 'Leave';
    const isEven = getWeekNumber(monday) % 2 === 0;
    let   shift  = emp.defaultShift;
    if (!isEven) shift = shift === 'Day' ? 'Night' : 'Day';
    return shift;
}

function renderWeekSummary() {
    const days   = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    const curDay = document.getElementById('modalDaySelect').value;
    document.getElementById('weekSummaryBadges').innerHTML = days.map(day => {
        const cnt    = (AppState.tempConfig[day] || []).length;
        const active = day === curDay;
        return `<div class="text-center px-3 py-2 border rounded flex-shrink-0"
                     style="min-width:58px;cursor:pointer;${active
                         ? 'border-color:var(--bs-primary)!important;background:rgba(var(--bs-primary-rgb),.06)'
                         : ''}"
                     onclick="jumpToDay('${day}')">
                    <div class="text-muted" style="font-size:11px">${day.slice(0,3).toUpperCase()}</div>
                    <div class="fw-bold ${active ? 'text-primary' : (cnt ? '' : 'text-muted')}">${cnt}</div>
                    <div class="text-muted" style="font-size:10px">off</div>
                </div>`;
    }).join('');
}

function jumpToDay(day) {
    document.getElementById('modalDaySelect').value = day;
    onModalDayChange();
}

function renderModalList() {
    const day     = document.getElementById('modalDaySelect').value;
    const offList = AppState.tempConfig[day] || [];
    const emps    = getModalEmployees();
    const sel     = AppState.modalSelected;

    let dc = 0, nc = 0, rc = 0;
    emps.forEach(e => {
        if (offList.includes(e.id)) { rc++; return; }
        automatedShiftOnDay(e) === 'Day' ? dc++ : nc++;
    });
    document.getElementById('modalEmpCount').textContent   = `Showing ${emps.length} employee(s)`;
    document.getElementById('modalDayCount').textContent   = `${dc} Day`;
    document.getElementById('modalNightCount').textContent = `${nc} Night`;
    document.getElementById('modalRestCount').textContent  = `${rc} Rest`;
    document.getElementById('selectedCount').textContent   = `${sel.size} selected`;

    if (!emps.length) {
        document.getElementById('modalEmployeeList').innerHTML =
            '<p class="text-muted text-center py-3">No employees found.</p>';
        return;
    }

    document.getElementById('modalEmployeeList').innerHTML = emps.map(emp => {
        const isRest    = offList.includes(emp.id);
        const isSel     = sel.has(emp.id);
        const autoShift = automatedShiftOnDay(emp);
        const onLeave   = autoShift === 'Leave';

        const badge = isRest
            ? `<span class="badge bg-secondary me-2">Rest Day</span>`
            : onLeave
                ? `<span class="badge text-bg-dark me-2">On Leave</span>`
                : autoShift === 'Day'
                    ? `<span class="badge bg-primary me-2">Day Shift</span>`
                    : `<span class="badge bg-secondary me-2">Night Shift</span>`;

        const btn = isRest
            ? `<button class="btn btn-sm btn-outline-primary"
                       onclick="event.stopPropagation();toggleEmpRest('${emp.id}')">
                   <i class="fas fa-bolt me-1"></i>Reset to Shift</button>`
            : `<button class="btn btn-sm btn-outline-secondary" ${onLeave ? 'disabled' : ''}
                       onclick="event.stopPropagation();toggleEmpRest('${emp.id}')">
                   <i class="fas fa-moon me-1"></i>Rest Day</button>`;

        return `<div class="d-flex align-items-center gap-3 p-2 mb-2 border rounded emp-row"
                     style="cursor:pointer;${isSel
                         ? 'background:rgba(var(--bs-primary-rgb),.06);border-color:var(--bs-primary)!important'
                         : ''}"
                     onclick="toggleModalSelect('${emp.id}')">
                    <input type="checkbox" class="form-check-input mt-0 flex-shrink-0"
                           ${isSel ? 'checked' : ''}
                           onclick="event.stopPropagation();toggleModalSelect('${emp.id}')">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold small text-truncate">${h(emp.name)}</div>
                        <small class="text-muted">${emp.id} &middot; ${h(emp.position)} &middot; ${h(emp.department)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">${badge}${btn}</div>
                </div>`;
    }).join('');
}

function toggleModalSelect(id) {
    AppState.modalSelected.has(id)
        ? AppState.modalSelected.delete(id)
        : AppState.modalSelected.add(id);
    renderModalList();
}

function toggleEmpRest(id) {
    const day  = document.getElementById('modalDaySelect').value;
    const list = AppState.tempConfig[day] || [];
    const idx  = list.indexOf(id);
    if (idx === -1) list.push(id); else list.splice(idx, 1);
    AppState.tempConfig[day] = list;
    renderWeekSummary(); renderModalList();
}

function selectAllModal()   { getModalEmployees().forEach(e => AppState.modalSelected.add(e.id)); renderModalList(); }
function deselectAllModal() { AppState.modalSelected.clear(); renderModalList(); }

function bulkSetRest() {
    if (!AppState.modalSelected.size) return Swal.fire({ icon:'warning', title:'No employees selected', timer:1500, showConfirmButton:false });
    const day  = document.getElementById('modalDaySelect').value;
    const list = AppState.tempConfig[day] || [];
    AppState.modalSelected.forEach(id => { if (!list.includes(id)) list.push(id); });
    AppState.tempConfig[day] = list;
    AppState.modalSelected.clear();
    renderWeekSummary(); renderModalList();
}

function bulkResetShift() {
    if (!AppState.modalSelected.size) return Swal.fire({ icon:'warning', title:'No employees selected', timer:1500, showConfirmButton:false });
    const day  = document.getElementById('modalDaySelect').value;
    const list = AppState.tempConfig[day] || [];
    AppState.modalSelected.forEach(id => { const i = list.indexOf(id); if (i !== -1) list.splice(i,1); });
    AppState.tempConfig[day] = list;
    AppState.modalSelected.clear();
    renderWeekSummary(); renderModalList();
}

function handleAutoAssignDayOffs() {
    Swal.fire({
        title:'Auto-Distribute Day Offs?',
        text:'Evenly spread one rest day per employee across the week, replacing the current config.',
        icon:'question', showCancelButton:true, confirmButtonText:'Yes, auto-assign',
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;
        const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        days.forEach(d => AppState.tempConfig[d] = []);
        let idx = 0;
        EMPLOYEES.forEach(emp => { AppState.tempConfig[days[idx]].push(emp.id); idx = (idx+1) % days.length; });
        renderWeekSummary(); renderModalList();
        Swal.fire({ icon:'success', title:'Done!', text:'Rest days distributed evenly.', timer:1500, showConfirmButton:false });
    });
}

function saveDayOffConfig() {
    AppState.dayOffConfig = {};
    Object.keys(AppState.tempConfig).forEach(day => {
        AppState.dayOffConfig[day] = [...AppState.tempConfig[day]];
    });
    bootstrap.Modal.getInstance(document.getElementById('dayOffModal')).hide();
    renderTable();
    Swal.fire({ icon:'success', title:'Saved!', text:'Day off configuration applied.', timer:1500, showConfirmButton:false });
}


// ============================================================
//  PRINT & CSV DOWNLOAD
// ============================================================
function handlePrint() { window.print(); }

function handleDownload() {
    const monday   = getViewMonday();
    const dayKeys  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    const dayLabels= ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    const schedule = buildSchedule();

    const csv = [
        ['Employee ID','Name','Department','Position',...dayLabels].join(','),
        ...schedule.map(e =>
            [e.id, `"${e.name}"`, `"${e.department}"`, `"${e.position}"`,
             ...dayKeys.map(k => e[k].shift)].join(',')
        ),
    ].join('\n');

    const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const link = Object.assign(document.createElement('a'), {
        href: url,
        download: `schedule_${getWeekLabel(monday).replace(/[\s,–]/g,'_')}.csv`,
    });
    link.click();
    URL.revokeObjectURL(url);
}


// ============================================================
//  UTILITY
// ============================================================
function h(str) {
    return String(str ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}


// ============================================================
//  INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    initPageFilters(); // cascades → onDeptChange → onPositionChange → renderTable
});
</script>
@endpush