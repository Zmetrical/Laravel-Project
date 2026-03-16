@extends('layouts.main')

@section('title', 'Team Attendance')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Team Attendance</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- ── View Toggle + Header ──────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm active" id="btn-view-daily">
            Daily View
        </button>
        <button class="btn btn-secondary btn-sm" id="btn-view-employee">
            Employee View
        </button>
    </div>
    <button class="btn btn-secondary btn-sm" id="btn-add-record">
        <i class="bi bi-plus me-1"></i> Add Record
    </button>
</div>

{{-- ── Stats ─────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="card card-outline card-primary mb-0 stat-card" data-filter="all">
            <div class="card-body py-3">
                <div class="text-muted small">Total</div>
                <div class="fs-4 fw-semibold" id="stat-total">—</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card card-outline card-primary mb-0 stat-card" data-filter="present">
            <div class="card-body py-3">
                <div class="text-muted small">Present</div>
                <div class="fs-4 fw-semibold" id="stat-present">—</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card card-outline card-secondary mb-0 stat-card" data-filter="late">
            <div class="card-body py-3">
                <div class="text-muted small">Late</div>
                <div class="fs-4 fw-semibold" id="stat-late">—</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card card-outline card-secondary mb-0 stat-card" data-filter="absent">
            <div class="card-body py-3">
                <div class="text-muted small">Absent</div>
                <div class="fs-4 fw-semibold" id="stat-absent">—</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card card-outline card-secondary mb-0 stat-card" data-filter="issues">
            <div class="card-body py-3">
                <div class="text-muted small">Issues</div>
                <div class="fs-4 fw-semibold" id="stat-issues">—</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filters Card ──────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body">

        {{-- Daily View Filters --}}
        <div id="filters-daily">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Date</label>
                    <input type="date" class="form-control form-control-sm"
                           id="f-date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Department</label>
                    <select class="form-select form-select-sm" id="f-department">
                        <option value="">All Departments</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select class="form-select form-select-sm" id="f-status">
                        <option value="all">All Status</option>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                        <option value="incomplete">Incomplete</option>
                        <option value="issues">With Issues</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm w-100" id="btn-load-daily">
                        <i class="bi bi-search me-1"></i> Load
                    </button>
                </div>
            </div>
        </div>

        {{-- Employee View Filters --}}
        <div id="filters-employee" class="d-none">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Employee</label>
                    <select class="form-select form-select-sm" id="f-employee">
                        <option value="">— Select Employee —</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Month</label>
                    <select class="form-select form-select-sm" id="f-month">
                        @foreach(['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'] as $v => $l)
                            <option value="{{ $v }}" {{ $v == date('m') ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Year</label>
                    <select class="form-select form-select-sm" id="f-year">
                        @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm w-100" id="btn-load-employee">
                        <i class="bi bi-search me-1"></i> Load
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Records Table ─────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span id="records-title">Attendance Records</span>
        <small class="text-muted" id="records-count">—</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr id="table-head-daily">
                        <th style="width:48px"></th>
                        <th>Employee</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours</th>
                        <th>Late</th>
                        <th>Undertime</th>
                        <th>Status</th>
                        <th style="width:80px">Actions</th>
                    </tr>
                </thead>
                <tbody id="attendance-tbody">
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            Select a date and click Load to view records.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer py-2">
        <small class="text-muted" id="table-footer">—</small>
    </div>
</div>

{{-- ── Add / Edit Record Modal ───────────────────────────────── --}}
<div class="modal fade" id="record-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="record-modal-title">Add Attendance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="r-record-id">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small text-muted">
                            Employee <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm" id="r-user-id">
                            <option value="">— Select Employee —</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">
                            Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm"
                               id="r-date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Time In</label>
                        <input type="time" class="form-control form-control-sm" id="r-time-in">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Time Out</label>
                        <input type="time" class="form-control form-control-sm" id="r-time-out">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Override Status</label>
                        <select class="form-select form-select-sm" id="r-status">
                            <option value="">Auto-compute</option>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                            <option value="leave">On Leave</option>
                            <option value="holiday">Holiday</option>
                            <option value="rest_day">Rest Day</option>
                            <option value="incomplete">Incomplete</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted">Notes</label>
                        <textarea class="form-control form-control-sm" id="r-notes"
                                  rows="2" placeholder="Optional notes…"></textarea>
                    </div>
                </div>

                {{-- Computed preview --}}
                <div id="r-preview" class="mt-3 p-3 border rounded bg-light d-none">
                    <p class="text-muted small fw-semibold text-uppercase mb-2">Computed Values</p>
                    <div class="row g-2 text-sm">
                        <div class="col-4">
                            <div class="text-muted small">Hours Worked</div>
                            <strong id="r-prev-hours">—</strong>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Late (min)</div>
                            <strong id="r-prev-late">—</strong>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Undertime (min)</div>
                            <strong id="r-prev-ut">—</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="btn-save-record">
                    <i class="bi bi-floppy me-1"></i> Save Record
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const TeamAttendance = (() => {

    /* ─── Routes ────────────────────────────────────────────── */
    const ROUTES = {
        employees: '{{ route('hresource.team_attendance.employees') }}',
        records:   '{{ route('hresource.team_attendance.records') }}',
        upsert:    '{{ route('hresource.team_attendance.upsert') }}',
    };
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ─── Helpers ───────────────────────────────────────────── */
    async function api(url, options = {}) {
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            ...options,
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message ?? `HTTP ${res.status}`);
        }
        return res.json();
    }

    /* ─── Lazy modal ────────────────────────────────────────── */
    let _modal = null;
    const getModal = () => _modal ??= new bootstrap.Modal(document.getElementById('record-modal'));

    /* ─── State ─────────────────────────────────────────────── */
    let view      = 'daily';   // 'daily' | 'employee'
    let records   = [];
    let filtered  = [];
    let employees = [];
    let activeFilter = 'all';

    /* ─── DOM ───────────────────────────────────────────────── */
    const $ = id => document.getElementById(id);
    const tbody = $('attendance-tbody');

    /* ─── Load employees once ───────────────────────────────── */
    async function loadEmployees() {
        try {
            employees = await api(ROUTES.employees);
            populateEmployeeDropdowns();
            populateDeptFilter();
        } catch (e) {
            console.error('Failed to load employees', e);
        }
    }

    function populateEmployeeDropdowns() {
        const opts = employees.map(e =>
            `<option value="${e.id}">${e.fullName} (${e.id})</option>`
        ).join('');

        $('f-employee').innerHTML = '<option value="">— Select Employee —</option>' + opts;
        $('r-user-id').innerHTML  = '<option value="">— Select Employee —</option>' + opts;
    }

    function populateDeptFilter() {
        const depts = [...new Set(employees.map(e => e.department).filter(Boolean))].sort();
        $('f-department').innerHTML = '<option value="">All Departments</option>'
            + depts.map(d => `<option value="${d}">${d}</option>`).join('');
    }

    /* ─── Load Records ──────────────────────────────────────── */
    async function loadRecords() {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading…</td></tr>`;

        const params = new URLSearchParams();

        if (view === 'daily') {
            params.set('date', $('f-date').value);
            if ($('f-department').value) params.set('department', $('f-department').value);
        } else {
            const empId = $('f-employee').value;
            if (!empId) {
                tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">
                    Select an employee to view records.</td></tr>`;
                return;
            }
            params.set('user_id', empId);
            params.set('month',   parseInt($('f-month').value));
            params.set('year',    $('f-year').value);
        }

        // Status filter — only pass if not 'all'
        const status = $('f-status')?.value ?? 'all';
        if (status && status !== 'all') params.set('status', status);

        try {
            records  = await api(`${ROUTES.records}?${params}`);
            filtered = [...records];
            updateTitle();
            updateStats();
            renderTable();
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>${e.message}</td></tr>`;
        }
    }

    /* ─── Stats ─────────────────────────────────────────────── */
    function updateStats() {
        const total   = records.length;
        const present = records.filter(r => r.status === 'present').length;
        const late    = records.filter(r => r.status === 'late').length;
        const absent  = records.filter(r => r.status === 'absent').length;
        const issues  = records.filter(r => ['incomplete','absent'].includes(r.status)).length;

        $('stat-total').textContent   = total;
        $('stat-present').textContent = present;
        $('stat-late').textContent    = late;
        $('stat-absent').textContent  = absent;
        $('stat-issues').textContent  = issues;

        // Highlight active stat card
        document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('border-primary'));
        const active = document.querySelector(`.stat-card[data-filter="${activeFilter}"]`);
        if (active) active.classList.add('border-primary');
    }

    function updateTitle() {
        if (view === 'daily') {
            $('records-title').textContent =
                `Attendance — ${$('f-date').value}`;
        } else {
            const emp = employees.find(e => e.id === $('f-employee').value);
            $('records-title').textContent = emp
                ? `${emp.fullName} — ${$('f-month').options[$('f-month').selectedIndex].text} ${$('f-year').value}`
                : 'Attendance Records';
        }
    }

    /* ─── Render Table ──────────────────────────────────────── */
    function renderTable() {
        if (!filtered.length) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x me-2"></i>No records found.</td></tr>`;
            $('table-footer').textContent = 'No results';
            return;
        }

        const statusBadge = s => {
            const cls = {
                present:    'bg-primary',
                late:       'bg-secondary',
                absent:     'bg-secondary',
                incomplete: 'bg-secondary',
                half_day:   'bg-secondary',
                leave:      'bg-secondary',
                holiday:    'bg-primary',
                rest_day:   'bg-secondary',
            }[s] ?? 'bg-secondary';
            return `<span class="badge ${cls} bg-opacity-10 text-capitalize">${s?.replace('_',' ') ?? '—'}</span>`;
        };

        tbody.innerHTML = filtered.map(r => {
            const name     = r.user?.fullName ?? '—';
            const dept     = r.user?.department ?? '—';
            const pos      = r.user?.position  ?? '—';
            const initials = name.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase();
            const hasIssue = ['incomplete','absent'].includes(r.status);

            return `<tr class="${hasIssue ? 'table-warning' : ''}">
                <td class="text-center">
                    <span class="d-inline-flex align-items-center justify-content-center
                          rounded-circle bg-secondary bg-opacity-10 text-secondary fw-semibold"
                          style="width:32px;height:32px;font-size:.7rem">
                        ${initials}
                    </span>
                </td>
                <td>
                    <div class="fw-semibold">${name}</div>
                    <div class="text-muted small">${r.user_id} &middot; ${dept}
                        ${view === 'employee' ? `&middot; ${r.date}` : ''}
                    </div>
                </td>
                <td>${r.time_in  ? r.time_in.slice(0,5)  : '<span class="text-muted">—</span>'}</td>
                <td>${r.time_out ? r.time_out.slice(0,5) : '<span class="text-muted">—</span>'}</td>
                <td>${r.hours_worked > 0 ? parseFloat(r.hours_worked).toFixed(2) : '<span class="text-muted">—</span>'}</td>
                <td>${r.late_minutes > 0
                    ? `<span class="text-secondary">${parseFloat(r.late_minutes).toFixed(0)} min</span>`
                    : '<span class="text-muted">—</span>'}</td>
                <td>${r.undertime_minutes > 0
                    ? `<span class="text-secondary">${parseFloat(r.undertime_minutes).toFixed(0)} min</span>`
                    : '<span class="text-muted">—</span>'}</td>
                <td>${statusBadge(r.status)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary py-1 px-2 btn-edit-record"
                            data-record='${JSON.stringify({
                                id:       r.id,
                                user_id:  r.user_id,
                                date:     r.date,
                                time_in:  r.time_in  ? r.time_in.slice(0,5)  : '',
                                time_out: r.time_out ? r.time_out.slice(0,5) : '',
                                status:   r.status,
                                notes:    r.notes ?? '',
                            }).replace(/'/g, "&apos;")}'
                            title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');

        $('table-footer').textContent =
            `Showing ${filtered.length} of ${records.length} record(s)`;
        $('records-count').textContent = `${filtered.length} record(s)`;
    }

    /* ─── View Toggle ───────────────────────────────────────── */
    function setView(v) {
        view = v;

        $('filters-daily').classList.toggle('d-none',    v === 'employee');
        $('filters-employee').classList.toggle('d-none', v === 'daily');
        $('btn-view-daily').className    = `btn btn-sm ${v === 'daily'    ? 'btn-primary'   : 'btn-secondary'}`;
        $('btn-view-employee').className = `btn btn-sm ${v === 'employee' ? 'btn-primary'   : 'btn-secondary'}`;

        // Reset
        records  = [];
        filtered = [];
        updateStats();
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">
            ${v === 'daily' ? 'Select a date and click Load.' : 'Select an employee and click Load.'}</td></tr>`;
        $('records-title').textContent = 'Attendance Records';
        $('table-footer').textContent  = '—';
    }

    /* ─── Add / Edit Modal ──────────────────────────────────── */
    function openAdd() {
        $('record-modal-title').textContent = 'Add Attendance Record';
        $('r-record-id').value = '';
        $('r-user-id').value   = '';
        $('r-date').value      = $('f-date').value || '{{ date('Y-m-d') }}';
        $('r-time-in').value   = '';
        $('r-time-out').value  = '';
        $('r-status').value    = '';
        $('r-notes').value     = '';
        $('r-preview').classList.add('d-none');
        getModal().show();
    }

    function openEdit(data) {
        $('record-modal-title').textContent = 'Edit Attendance Record';
        $('r-record-id').value = data.id      ?? '';
        $('r-user-id').value   = data.user_id ?? '';
        $('r-date').value      = data.date    ?? '';
        $('r-time-in').value   = data.time_in  ?? '';
        $('r-time-out').value  = data.time_out ?? '';
        $('r-status').value    = data.status   ?? '';
        $('r-notes').value     = data.notes    ?? '';
        $('r-preview').classList.add('d-none');
        getModal().show();
    }

    function previewComputed() {
        const tin  = $('r-time-in').value;
        const tout = $('r-time-out').value;
        if (!tin || !tout) { $('r-preview').classList.add('d-none'); return; }

        // Client-side rough preview (server does the real calc)
        const [h1,m1] = tin.split(':').map(Number);
        const [h2,m2] = tout.split(':').map(Number);
        let mins = (h2 * 60 + m2) - (h1 * 60 + m1);
        if (mins < 0) mins += 1440;
        const hrs = (mins / 60).toFixed(2);

        $('r-prev-hours').textContent = `${hrs} hrs`;
        $('r-prev-late').textContent  = '(server)';
        $('r-prev-ut').textContent    = '(server)';
        $('r-preview').classList.remove('d-none');
    }

    async function saveRecord() {
        const btn = $('btn-save-record');
        const userId = $('r-user-id').value;
        const date   = $('r-date').value;

        if (!userId || !date) {
            Swal.fire({ icon: 'warning', title: 'Required', text: 'Employee and date are required.' });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

        try {
            await api(ROUTES.upsert, {
                method: 'POST',
                body:   JSON.stringify({
                    user_id:  userId,
                    date:     date,
                    time_in:  $('r-time-in').value  || null,
                    time_out: $('r-time-out').value || null,
                    status:   $('r-status').value   || null,
                    notes:    $('r-notes').value    || null,
                }),
            });

            Swal.fire({ icon: 'success', title: 'Saved!',
                timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });

            getModal().hide();
            await loadRecords();

        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy me-1"></i> Save Record';
        }
    }

    /* ─── Stat card click filter ────────────────────────────── */
    function applyStatFilter(filter) {
        activeFilter = filter;

        filtered = filter === 'all'
            ? [...records]
            : filter === 'issues'
                ? records.filter(r => ['incomplete','absent'].includes(r.status))
                : records.filter(r => r.status === filter);

        updateStats();
        renderTable();
    }

    /* ─── Bind ───────────────────────────────────────────────── */
    function bind() {
        $('btn-view-daily').addEventListener('click',    () => setView('daily'));
        $('btn-view-employee').addEventListener('click', () => setView('employee'));
        $('btn-load-daily').addEventListener('click',    loadRecords);
        $('btn-load-employee').addEventListener('click', loadRecords);
        $('btn-add-record').addEventListener('click',    openAdd);
        $('btn-save-record').addEventListener('click',   saveRecord);

        // Enter key on date field triggers load
        $('f-date').addEventListener('keydown', e => { if (e.key === 'Enter') loadRecords(); });

        // Preview hours when times change
        $('r-time-in').addEventListener('change',  previewComputed);
        $('r-time-out').addEventListener('change', previewComputed);

        // Edit from table row
        tbody.addEventListener('click', e => {
            const btn = e.target.closest('.btn-edit-record');
            if (btn) {
                const data = JSON.parse(btn.dataset.record.replace(/&apos;/g, "'"));
                openEdit(data);
            }
        });

        // Stat card click
        document.querySelectorAll('.stat-card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', () => applyStatFilter(card.dataset.filter));
        });
    }

    /* ─── Init ──────────────────────────────────────────────── */
    async function init() {
        bind();
        await loadEmployees();
        // Auto-load today's records on page load
        await loadRecords();
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', () => TeamAttendance.init());
</script>
@endpush