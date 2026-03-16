@extends('layouts.main')

@section('title', 'Team Schedule')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Team Schedule</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- ── Section 1: Templates ──────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">Schedule Templates</h3>
        <button class="btn btn-primary btn-sm" id="btn-add-template">
            <i class="bi bi-plus me-1"></i> New Template
        </button>
    </div>
    <div class="card-body">
        <div class="row g-3" id="template-cards">
            <div class="col-12 text-center py-4 text-muted" id="template-loading">
                <div class="spinner-border spinner-border-sm me-2"></div> Loading…
            </div>
        </div>
    </div>
</div>

{{-- ── Section 2: Assignments ────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">Employee Assignments</h3>
        <button class="btn btn-secondary btn-sm" id="btn-assign">
            <i class="bi bi-people me-1"></i> Assign Schedule
        </button>
    </div>

    {{-- Filters --}}
    <div class="card-body border-bottom">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control" id="assign-search"
                           placeholder="Search by name, ID, department…">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" id="assign-dept">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" id="assign-template-filter">
                    <option value="">All Schedules</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Assignment Table --}}
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:48px"></th>
                        <th>Employee</th>
                        <th>Department / Position</th>
                        <th>Current Schedule</th>
                        <th>Effective Date</th>
                        <th style="width:80px">Action</th>
                    </tr>
                </thead>
                <tbody id="assign-tbody">
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm me-2"></div> Loading…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer py-2">
        <small class="text-muted" id="assign-footer">—</small>
    </div>
</div>

{{-- ── Template Modal (Add / Edit) ──────────────────────────── --}}
<div class="modal fade" id="template-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="template-modal-title">New Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="t-id">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">
                            Template Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm" id="t-name"
                               placeholder="e.g. Morning Shift">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Grace Period (minutes)</label>
                        <input type="number" class="form-control form-control-sm" id="t-grace"
                               min="0" max="60" value="0">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="t-active" checked>
                            <label class="form-check-label small" for="t-active">Active</label>
                        </div>
                    </div>
                </div>

                {{-- Day Rows --}}
                <p class="text-muted small fw-semibold text-uppercase mb-2">Work Days & Shifts</p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" id="days-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px"></th>
                                <th style="width:90px">Day</th>
                                <th>Working Day</th>
                                <th>Shift In</th>
                                <th>Shift Out</th>
                            </tr>
                        </thead>
                        <tbody id="days-tbody">
                            {{-- Rendered by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="btn-save-template">
                    <i class="bi bi-floppy me-1"></i> Save Template
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Assign Modal ──────────────────────────────────────────── --}}
<div class="modal fade" id="assign-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Step 1: pick template + effective date --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">
                            Schedule Template <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm" id="a-template-id">
                            <option value="">— Select Template —</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">
                            Effective Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm" id="a-effective-date"
                               value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                {{-- Step 2: select employees --}}
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <p class="text-muted small fw-semibold text-uppercase mb-0">Select Employees</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="input-group input-group-sm" style="width:200px">
                            <span class="input-group-text bg-transparent">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control" id="a-emp-search"
                                   placeholder="Search…">
                        </div>
                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" id="a-select-all">
                            <label class="form-check-label small" for="a-select-all">Select All</label>
                        </div>
                    </div>
                </div>

                <div class="border rounded" style="max-height:320px;overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <tbody id="a-emp-tbody">
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2"></div>Loading…
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <small class="text-muted" id="a-selected-count">0 selected</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="btn-save-assign">
                    <i class="bi bi-check2 me-1"></i> Assign
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const TeamSchedule = (() => {

    /* ─── Routes ────────────────────────────────────────────── */
    const ROUTES = {
        templates:   '{{ route('hresource.team_schedule.templates') }}',
        storeT:      '{{ route('hresource.team_schedule.templates.store') }}',
        updateT:     '{{ url('hresource/team-schedule/templates') }}',   // + /{id}
        destroyT:    '{{ url('hresource/team-schedule/templates') }}',   // + /{id}
        assignments: '{{ route('hresource.team_schedule.assignments') }}',
        assign:      '{{ route('hresource.team_schedule.assign') }}',
    };
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const DAY_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    /* ─── API ───────────────────────────────────────────────── */
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

    /* ─── Lazy modals ───────────────────────────────────────── */
    let _tplModal    = null;
    let _assignModal = null;
    const getTplModal    = () => _tplModal    ??= new bootstrap.Modal(document.getElementById('template-modal'));
    const getAssignModal = () => _assignModal ??= new bootstrap.Modal(document.getElementById('assign-modal'));

    /* ─── State ─────────────────────────────────────────────── */
    let templates    = [];
    let allEmployees = [];
    let filtered     = [];
    let editingTplId = null;
    let selectedEmpIds = new Set();

    /* ─── DOM ───────────────────────────────────────────────── */
    const $ = id => document.getElementById(id);

    /* ═══════════════════════════════════════════════════════════
       SECTION 1 — TEMPLATES
    ═══════════════════════════════════════════════════════════ */

    async function loadTemplates() {
        try {
            templates = await api(ROUTES.templates);
            renderTemplateCards();
            populateTemplateFilters();
        } catch (e) {
            $('template-cards').innerHTML =
                `<div class="col-12 text-center text-danger py-3">${e.message}</div>`;
        }
    }

    function renderTemplateCards() {
        const container = $('template-cards');

        if (!templates.length) {
            container.innerHTML = `<div class="col-12 text-center text-muted py-4">
                No templates yet. Create one to get started.</div>`;
            return;
        }

        container.innerHTML = templates.map(tpl => {
            const workDays = tpl.days.filter(d => d.is_working_day);
            const dayBadges = tpl.days.map(d =>
                `<span class="badge ${d.is_working_day
                    ? 'bg-primary bg-opacity-10 text-primary'
                    : 'bg-secondary bg-opacity-10 text-secondary'}">
                    ${DAY_SHORT[d.day_of_week]}
                </span>`
            ).join('');

            // Shift time from first working day
            const first = workDays[0];
            const shiftLabel = first?.shift_in
                ? `${first.shift_in.slice(0,5)} – ${(first.shift_out ?? '').slice(0,5)}`
                : '—';

            return `
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 ${tpl.is_active ? '' : 'opacity-50'}">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <div class="fw-semibold">${tpl.name}</div>
                                <div class="text-muted small">${shiftLabel}</div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-secondary py-1 px-2 btn-edit-tpl"
                                        data-id="${tpl.id}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary py-1 px-2 btn-del-tpl"
                                        data-id="${tpl.id}" data-name="${tpl.name}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex gap-1 flex-wrap mb-2">${dayBadges}</div>

                        <div class="d-flex justify-content-between text-muted small">
                            <span>${workDays.length} working day(s)</span>
                            <span>${tpl.employee_count ?? 0} employee(s)</span>
                        </div>
                        ${tpl.grace_period_minutes > 0
                            ? `<div class="text-muted small mt-1">${tpl.grace_period_minutes} min grace</div>`
                            : ''}
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    function populateTemplateFilters() {
        // Filter dropdown in assignments section
        const sel = $('assign-template-filter');
        const asel = $('a-template-id');

        const opts = templates.map(t =>
            `<option value="${t.id}">${t.name}</option>`
        ).join('');

        sel.innerHTML  = '<option value="">All Schedules</option>' + opts;
        asel.innerHTML = '<option value="">— Select Template —</option>' + opts;
    }

    /* ─── Template Modal ────────────────────────────────────── */
    function buildDayRows(existing = []) {
        const tbody = $('days-tbody');
        tbody.innerHTML = Array.from({ length: 7 }, (_, i) => {
            const ex = existing.find(d => d.day_of_week === i) ?? {};
            const isWorking = ex.is_working_day ?? (i >= 1 && i <= 6); // Mon–Sat default
            return `
            <tr data-dow="${i}">
                <td class="text-center">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">${DAY_SHORT[i]}</span>
                </td>
                <td class="text-muted small">${DAY_NAMES[i]}</td>
                <td>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input day-working"
                               ${isWorking ? 'checked' : ''}>
                    </div>
                </td>
                <td>
                    <input type="time" class="form-control form-control-sm day-in"
                           value="${ex.shift_in ? ex.shift_in.slice(0,5) : ''}"
                           ${!isWorking ? 'disabled' : ''}>
                </td>
                <td>
                    <input type="time" class="form-control form-control-sm day-out"
                           value="${ex.shift_out ? ex.shift_out.slice(0,5) : ''}"
                           ${!isWorking ? 'disabled' : ''}>
                </td>
            </tr>`;
        }).join('');

        // Toggle shift inputs when working day checkbox changes
        tbody.querySelectorAll('.day-working').forEach(chk => {
            chk.addEventListener('change', () => {
                const row = chk.closest('tr');
                row.querySelector('.day-in').disabled  = !chk.checked;
                row.querySelector('.day-out').disabled = !chk.checked;
                if (!chk.checked) {
                    row.querySelector('.day-in').value  = '';
                    row.querySelector('.day-out').value = '';
                }
            });
        });
    }

    function openAddTemplate() {
        editingTplId = null;
        $('template-modal-title').textContent = 'New Template';
        $('t-id').value    = '';
        $('t-name').value  = '';
        $('t-grace').value = '0';
        $('t-active').checked = true;
        buildDayRows();
        getTplModal().show();
    }

    function openEditTemplate(id) {
        const tpl = templates.find(t => t.id == id);
        if (!tpl) return;

        editingTplId = id;
        $('template-modal-title').textContent = 'Edit Template';
        $('t-id').value    = tpl.id;
        $('t-name').value  = tpl.name;
        $('t-grace').value = tpl.grace_period_minutes;
        $('t-active').checked = tpl.is_active;
        buildDayRows(tpl.days);
        getTplModal().show();
    }

    function collectDayRows() {
        return Array.from($('days-tbody').querySelectorAll('tr')).map(row => ({
            day_of_week:    parseInt(row.dataset.dow),
            is_working_day: row.querySelector('.day-working').checked,
            shift_in:       row.querySelector('.day-in').value  || null,
            shift_out:      row.querySelector('.day-out').value || null,
        }));
    }

    async function saveTemplate() {
        const btn  = $('btn-save-template');
        const name = $('t-name').value.trim();

        if (!name) {
            Swal.fire({ icon: 'warning', title: 'Required', text: 'Template name is required.' });
            return;
        }

        const payload = {
            name:                 name,
            grace_period_minutes: parseInt($('t-grace').value) || 0,
            is_active:            $('t-active').checked,
            days:                 collectDayRows(),
        };

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

        try {
            const isEdit = !!editingTplId;
            const url    = isEdit ? `${ROUTES.updateT}/${editingTplId}` : ROUTES.storeT;
            const method = isEdit ? 'PATCH' : 'POST';

            await api(url, { method, body: JSON.stringify(payload) });

            Swal.fire({ icon: 'success', title: isEdit ? 'Updated!' : 'Created!',
                timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });

            getTplModal().hide();
            await loadTemplates();

        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy me-1"></i> Save Template';
        }
    }

    async function deleteTemplate(id, name) {
        const confirm = await Swal.fire({
            title: `Delete "${name}"?`,
            text:  'This cannot be undone.',
            icon:  'warning',
            showCancelButton:   true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  'Yes, Delete',
        });
        if (!confirm.isConfirmed) return;

        try {
            await api(`${ROUTES.destroyT}/${id}`, { method: 'DELETE' });
            Swal.fire({ icon: 'success', title: 'Deleted',
                timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
            await loadTemplates();
            await loadAssignments();
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Cannot Delete', text: e.message });
        }
    }

    /* ═══════════════════════════════════════════════════════════
       SECTION 2 — ASSIGNMENTS
    ═══════════════════════════════════════════════════════════ */

    async function loadAssignments() {
        const tbody = $('assign-tbody');
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading…</td></tr>`;

        try {
            allEmployees = await api(ROUTES.assignments);
            filtered     = [...allEmployees];
            populateDeptFilter();
            renderAssignments();
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-danger">${e.message}</td></tr>`;
        }
    }

    function populateDeptFilter() {
        const depts = [...new Set(allEmployees.map(e => e.department).filter(Boolean))].sort();
        $('assign-dept').innerHTML = '<option value="">All Departments</option>'
            + depts.map(d => `<option value="${d}">${d}</option>`).join('');
    }

    function renderAssignments() {
        const tbody = $('assign-tbody');

        if (!filtered.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No employees found.</td></tr>`;
            $('assign-footer').textContent = 'No results';
            return;
        }

        tbody.innerHTML = filtered.map(emp => {
            const schedule = emp.current_schedule;
            const tplName  = schedule?.template?.name ?? '—';
            const effDate  = schedule?.effective_date  ?? '—';
            const initials = emp.fullName.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase();

            return `<tr>
                <td class="text-center">
                    <span class="d-inline-flex align-items-center justify-content-center
                          rounded-circle bg-secondary bg-opacity-10 text-secondary fw-semibold"
                          style="width:32px;height:32px;font-size:.7rem">
                        ${initials}
                    </span>
                </td>
                <td>
                    <div class="fw-semibold">${emp.fullName}</div>
                    <div class="text-muted small">${emp.id}</div>
                </td>
                <td>
                    <div>${emp.department ?? '—'}</div>
                    <div class="text-muted small">${emp.position ?? '—'}</div>
                </td>
                <td>
                    ${schedule
                        ? `<span class="badge bg-primary bg-opacity-10 text-primary">${tplName}</span>`
                        : `<span class="text-muted small">No schedule</span>`}
                </td>
                <td class="text-muted small">${effDate}</td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary py-1 px-2 btn-quick-assign"
                            data-id="${emp.id}" data-name="${emp.fullName}" title="Assign Schedule">
                        <i class="bi bi-calendar-plus"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');

        $('assign-footer').textContent =
            `Showing ${filtered.length} of ${allEmployees.length} employee(s)`;
    }

    function applyAssignFilters() {
        const q    = $('assign-search').value.toLowerCase();
        const dept = $('assign-dept').value;
        const tpl  = $('assign-template-filter').value;

        filtered = allEmployees.filter(e => {
            const matchQ    = !q   || [e.fullName, e.id, e.department]
                                .some(v => (v ?? '').toLowerCase().includes(q));
            const matchDept = !dept || e.department === dept;
            const matchTpl  = !tpl  ||
                String(e.current_schedule?.template_id) === String(tpl);
            return matchQ && matchDept && matchTpl;
        });

        renderAssignments();
    }

    /* ─── Assign Modal ──────────────────────────────────────── */
    function openAssignModal(preselectedId = null) {
        selectedEmpIds.clear();
        $('a-select-all').checked = false;
        $('a-emp-search').value   = '';

        if (preselectedId) {
            selectedEmpIds.add(preselectedId);
        }

        renderAssignEmployeeList();
        getAssignModal().show();
    }

    function renderAssignEmployeeList(searchVal = '') {
        const tbody = $('a-emp-tbody');
        const q     = searchVal.toLowerCase();

        const list = allEmployees.filter(e =>
            !q || [e.fullName, e.id, e.department]
                    .some(v => (v ?? '').toLowerCase().includes(q))
        );

        if (!list.length) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center py-3 text-muted">No employees found.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map(emp => `
            <tr>
                <td style="width:36px">
                    <input type="checkbox" class="form-check-input a-emp-chk"
                           value="${emp.id}" ${selectedEmpIds.has(emp.id) ? 'checked' : ''}>
                </td>
                <td>
                    <div class="fw-semibold small">${emp.fullName}</div>
                    <div class="text-muted small">${emp.id}</div>
                </td>
                <td class="text-muted small">${emp.department ?? '—'}</td>
            </tr>`).join('');

        // Bind checkboxes
        tbody.querySelectorAll('.a-emp-chk').forEach(chk => {
            chk.addEventListener('change', () => {
                chk.checked
                    ? selectedEmpIds.add(chk.value)
                    : selectedEmpIds.delete(chk.value);
                updateSelectedCount();
            });
        });

        updateSelectedCount();
    }

    function updateSelectedCount() {
        $('a-selected-count').textContent = `${selectedEmpIds.size} selected`;
    }

    async function saveAssignment() {
        const btn        = $('btn-save-assign');
        const templateId = $('a-template-id').value;
        const effDate    = $('a-effective-date').value;

        if (!templateId) {
            Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a schedule template.' });
            return;
        }
        if (!selectedEmpIds.size) {
            Swal.fire({ icon: 'warning', title: 'Required', text: 'Select at least one employee.' });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Assigning…';

        try {
            const data = await api(ROUTES.assign, {
                method: 'POST',
                body:   JSON.stringify({
                    user_ids:       [...selectedEmpIds],
                    template_id:    templateId,
                    effective_date: effDate,
                }),
            });

            Swal.fire({ icon: 'success', title: 'Assigned!',
                text: data.message, timer: 2000, showConfirmButton: false,
                toast: true, position: 'top-end' });

            getAssignModal().hide();
            await loadAssignments();
            await loadTemplates(); // refresh employee counts

        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Assign';
        }
    }

    /* ─── Event Binding ─────────────────────────────────────── */
    function bind() {
        // Template section
        $('btn-add-template').addEventListener('click', openAddTemplate);
        $('btn-save-template').addEventListener('click', saveTemplate);

        $('template-cards').addEventListener('click', e => {
            const editBtn = e.target.closest('.btn-edit-tpl');
            const delBtn  = e.target.closest('.btn-del-tpl');
            if (editBtn) openEditTemplate(editBtn.dataset.id);
            if (delBtn)  deleteTemplate(delBtn.dataset.id, delBtn.dataset.name);
        });

        // Assignment section filters
        let t;
        $('assign-search').addEventListener('input', () => {
            clearTimeout(t); t = setTimeout(applyAssignFilters, 300);
        });
        $('assign-dept').addEventListener('change',            applyAssignFilters);
        $('assign-template-filter').addEventListener('change', applyAssignFilters);

        // Assign modal
        $('btn-assign').addEventListener('click', () => openAssignModal());
        $('btn-save-assign').addEventListener('click', saveAssignment);

        // Quick-assign from table row
        $('assign-tbody').addEventListener('click', e => {
            const btn = e.target.closest('.btn-quick-assign');
            if (btn) openAssignModal(btn.dataset.id);
        });

        // Select all in assign modal
        $('a-select-all').addEventListener('change', () => {
            document.querySelectorAll('.a-emp-chk').forEach(chk => {
                chk.checked = $('a-select-all').checked;
                $('a-select-all').checked
                    ? selectedEmpIds.add(chk.value)
                    : selectedEmpIds.delete(chk.value);
            });
            updateSelectedCount();
        });

        // Search inside assign modal
        $('a-emp-search').addEventListener('input', e =>
            renderAssignEmployeeList(e.target.value)
        );
    }

    /* ─── Init ──────────────────────────────────────────────── */
    async function init() {
        bind();
        await Promise.all([loadTemplates(), loadAssignments()]);
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', () => TeamSchedule.init());
</script>
@endpush