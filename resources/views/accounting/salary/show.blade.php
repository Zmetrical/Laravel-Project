@extends('layouts.main')

@section('title', 'Employee Salary Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Salary Management</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-outline card-primary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-people fs-4 text-primary"></i>
                    <div>
                        <div class="text-muted small">Total Employees</div>
                        <div class="fs-4 fw-semibold" id="stat-total">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-secondary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-cash-stack fs-4 text-secondary"></i>
                    <div>
                        <div class="text-muted small">Total Monthly Payroll</div>
                        <div class="fs-4 fw-semibold" id="stat-payroll">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-primary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-bar-chart fs-4 text-primary"></i>
                    <div>
                        <div class="text-muted small">Average Salary</div>
                        <div class="fs-4 fw-semibold" id="stat-avg">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Card --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">Employee Salary Management</h3>
        <button class="btn btn-secondary btn-sm" id="btn-bulk-toggle">
            <i class="bi bi-lightning me-1"></i> Bulk Update
        </button>
    </div>

    {{-- Bulk Panel --}}
    <div class="card-body border-bottom d-none" id="bulk-panel">
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
                <label class="form-label small mb-1">Filter by Position</label>
                <select class="form-select form-select-sm" id="bulk-position">
                    <option value="">— Select Position —</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}">{{ $pos }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">New Monthly Salary (₱)</label>
                <input type="number" class="form-control form-control-sm" id="bulk-salary"
                       placeholder="e.g. 25000" min="0" step="0.01">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="btn-bulk-apply">
                    <i class="bi bi-floppy me-1"></i> Apply
                </button>
                <button class="btn btn-secondary btn-sm" id="btn-bulk-cancel">
                    <i class="bi bi-x me-1"></i> Cancel
                </button>
            </div>
        </div>

        <div id="bulk-employee-list" class="d-none">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted" id="bulk-count-label">0 found</span>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="bulk-select-all">
                    <label class="form-check-label small" for="bulk-select-all">Select All</label>
                </div>
            </div>
            <div class="row g-2" id="bulk-employees"></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card-body border-bottom">
        <div class="row g-2">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control" id="filter-search"
                           placeholder="Search by name, ID, position…">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" id="filter-department">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" id="filter-position">
                    <option value="">All Positions</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}">{{ $pos }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:48px"></th>
                        <th>Employee</th>
                        <th>Department / Position</th>
                        <th>Monthly Salary</th>
                        <th>Daily Rate</th>
                        <th>Hourly Rate</th>
                        <th style="width:90px">Actions</th>
                    </tr>
                </thead>
                <tbody id="salary-tbody">
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Loading…
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

{{-- Edit Modal --}}
<div class="modal fade" id="edit-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Salary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-employee-id">
                <div class="mb-3">
                    <label class="form-label small text-muted">Employee</label>
                    <input type="text" class="form-control form-control-sm"
                           id="edit-employee-name" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">
                        Monthly Basic Salary (₱) <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control form-control-sm"
                           id="edit-basic-salary" min="0" step="0.01" placeholder="e.g. 25000">
                </div>
                <div class="row text-muted small" id="edit-derived-rates">
                    <div class="col-6">Daily Rate: <strong id="edit-daily-preview">—</strong></div>
                    <div class="col-6">Hourly Rate: <strong id="edit-hourly-preview">—</strong></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm" id="btn-save-edit">
                    <i class="bi bi-floppy me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Details Modal --}}
<div class="modal fade" id="details-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="details-modal-title">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="details-body">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Loading…
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const SalaryManager = (() => {

    /* ─── Config ────────────────────────────────────────────── */
    const ROUTES = {
        list:        '{{ route('accounting.salary.list') }}',
        show:        '{{ url('accounting/salary') }}',      // /{id}
        update:      '{{ url('accounting/salary') }}',      // PATCH /{id}
        bulkUpdate:  '{{ route('accounting.salary.bulk-update') }}',
    };

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ─── Helpers ───────────────────────────────────────────── */
    const peso = n =>
        '₱' + parseFloat(n || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

    async function apiFetch(url, options = {}) {
        const res = await fetch(url, {
            headers: {
                'Accept':       'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            ...options,
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message ?? `HTTP ${res.status}`);
        }

        return res.json();
    }

    /* ─── State ─────────────────────────────────────────────── */
    let allEmployees = [];      // full list from server
    let filtered     = [];      // after client-side filter
    let bulkChecked  = new Set();

    /* ─── Lazy modals (bootstrap may not be ready on DOMContentLoaded) ── */
    let _editModal   = null;
    let _detailModal = null;
    const getEditModal   = () => _editModal   ??= new bootstrap.Modal(document.getElementById('edit-modal'));
    const getDetailModal = () => _detailModal ??= new bootstrap.Modal(document.getElementById('details-modal'));

    /* ─── DOM refs ──────────────────────────────────────────── */
    const $ = id => document.getElementById(id);
    const tbody         = $('salary-tbody');
    const filterSearch  = $('filter-search');
    const filterDept    = $('filter-department');
    const filterPos     = $('filter-position');
    const bulkPanel     = $('bulk-panel');
    const bulkPosSel    = $('bulk-position');
    const bulkSalaryInp = $('bulk-salary');
    const bulkEmpList   = $('bulk-employee-list');
    const bulkEmps      = $('bulk-employees');
    const bulkSelectAll = $('bulk-select-all');

    /* ─── Load employees from server ────────────────────────── */
    async function loadEmployees() {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading…</td></tr>`;

        const params = new URLSearchParams({
            search:     filterSearch.value,
            department: filterDept.value,
            position:   filterPos.value,
        });

        try {
            allEmployees = await apiFetch(`${ROUTES.list}?${params}`);
            filtered     = [...allEmployees];
            updateStats();
            renderTable();
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>${e.message}</td></tr>`;
        }
    }

    /* ─── Stats ─────────────────────────────────────────────── */
    function updateStats() {
        const total   = allEmployees.length;
        const payroll = allEmployees.reduce((s, e) => s + parseFloat(e.basicSalary || 0), 0);
        $('stat-total').textContent   = total;
        $('stat-payroll').textContent = peso(payroll);
        $('stat-avg').textContent     = peso(total ? payroll / total : 0);
    }

    /* ─── Render Table ──────────────────────────────────────── */
    function renderTable() {
        if (!filtered.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-search me-2"></i>No employees found.</td></tr>`;
            $('table-footer').textContent = 'No results';
            return;
        }

        tbody.innerHTML = filtered.map(emp => {
            const initials = emp.fullName
                .split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
            return `<tr>
                <td class="text-center">
                    <span class="d-inline-flex align-items-center justify-content-center
                          rounded-circle bg-secondary bg-opacity-10 text-secondary fw-semibold"
                          style="width:36px;height:36px;font-size:.75rem">
                        ${initials}
                    </span>
                </td>
                <td>
                    <div class="fw-semibold">${emp.fullName}</div>
                    <div class="text-muted small">${emp.id}
                        ${emp.email ? '&middot; ' + emp.email : ''}
                    </div>
                </td>
                <td>
                    <div>${emp.department ?? '—'}</div>
                    <div class="text-muted small">${emp.position ?? '—'}</div>
                </td>
                <td class="fw-semibold">${peso(emp.basicSalary)}</td>
                <td class="text-muted">${peso(emp.dailyRate)}</td>
                <td class="text-muted">${peso(emp.hourlyRate)}</td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 btn-view"
                                data-id="${emp.id}" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary py-1 px-2 btn-edit"
                                data-id="${emp.id}"
                                data-name="${emp.fullName}"
                                data-salary="${emp.basicSalary}"
                                title="Edit Salary">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        $('table-footer').textContent =
            `Showing ${filtered.length} of ${allEmployees.length} employee(s)`;
    }

    /* ─── Client-side filter ────────────────────────────────── */
    function applyFilters() {
        const q    = filterSearch.value.toLowerCase();
        const dept = filterDept.value;
        const pos  = filterPos.value;

        filtered = allEmployees.filter(e => {
            const matchQ    = !q    || [e.fullName, e.id, e.position, e.department]
                                .some(v => (v ?? '').toLowerCase().includes(q));
            const matchDept = !dept || e.department === dept;
            const matchPos  = !pos  || e.position   === pos;
            return matchQ && matchDept && matchPos;
        });

        renderTable();
    }

    /* ─── Edit Modal ────────────────────────────────────────── */
    function openEdit(btn) {
        $('edit-employee-id').value   = btn.dataset.id;
        $('edit-employee-name').value = btn.dataset.name;
        $('edit-basic-salary').value  = btn.dataset.salary;
        refreshEditPreview(btn.dataset.salary);
        getEditModal().show();
    }

    function refreshEditPreview(val) {
        const n  = parseFloat(val);
        const ok = !isNaN(n) && n > 0;
        // Display-only preview using 26 working days / 8 hrs
        $('edit-daily-preview').textContent  = ok ? peso(n / 26)      : '—';
        $('edit-hourly-preview').textContent = ok ? peso(n / 26 / 8)  : '—';
    }

    async function saveEdit() {
        const id  = $('edit-employee-id').value;
        const val = parseFloat($('edit-basic-salary').value);
        const btn = $('btn-save-edit');

        if (!val || val <= 0) {
            Swal.fire({ icon: 'warning', title: 'Invalid Amount',
                text: 'Enter a valid salary amount.', confirmButtonColor: '#6c757d' });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

        try {
            await apiFetch(`${ROUTES.update}/${id}`, {
                method: 'PATCH',
                body:   JSON.stringify({ basicSalary: val }),
            });

            Swal.fire({ icon: 'success', title: 'Salary Updated',
                timer: 2000, showConfirmButton: false,
                toast: true, position: 'top-end' });

            getEditModal().hide();
            await loadEmployees();   // reload table + stats from server

        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy me-1"></i>Save Changes';
        }
    }

    /* ─── Details Modal ─────────────────────────────────────── */
    async function openDetails(id) {
        $('details-modal-title').textContent = 'Loading…';
        $('details-body').innerHTML = `<div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm me-2"></div>Loading…</div>`;
        getDetailModal().show();

        try {
            const { employee: emp, contributions: gc } =
                await apiFetch(`${ROUTES.show}/${id}`);

            $('details-modal-title').textContent = emp.fullName;
            $('details-body').innerHTML = buildDetailsHtml(emp, gc);

        } catch (e) {
            $('details-body').innerHTML =
                `<div class="text-danger text-center py-4">${e.message}</div>`;
        }
    }

    function buildDetailsHtml(emp, gc) {
        const status = (emp.employmentStatus ?? '').toLowerCase();
        const badgeCls = status === 'regular' ? 'bg-primary' : 'bg-secondary';

        return `
        <div class="row g-4">
            <div class="col-md-6">
                <p class="text-muted text-uppercase small fw-semibold mb-2">Employment</p>
                <table class="table table-sm table-borderless mb-3">
                    <tr><td class="text-muted" style="width:130px">Employee ID</td><td class="fw-semibold">${emp.id}</td></tr>
                    <tr><td class="text-muted">Department</td><td>${emp.department ?? '—'}</td></tr>
                    <tr><td class="text-muted">Position</td><td>${emp.position ?? '—'}</td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge ${badgeCls} bg-opacity-10">${emp.employmentStatus ?? '—'}</span></td></tr>
                    <tr><td class="text-muted">Hire Date</td><td>${emp.hireDate ?? '—'}</td></tr>
                    <tr><td class="text-muted">Branch</td><td>${emp.branch ?? '—'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <p class="text-muted text-uppercase small fw-semibold mb-2">Salary Breakdown</p>
                <table class="table table-sm table-borderless mb-3">
                    <tr><td class="text-muted" style="width:140px">Monthly Basic</td>
                        <td class="fw-semibold">${peso(emp.basicSalary)}</td></tr>
                    <tr><td class="text-muted">Daily Rate</td>
                        <td>${peso(emp.dailyRate)}</td></tr>
                    <tr><td class="text-muted">Hourly Rate</td>
                        <td>${peso(emp.hourlyRate)}</td></tr>
                </table>

                <p class="text-muted text-uppercase small fw-semibold mb-2">Est. Monthly Contributions</p>
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th></th><th class="text-end">EE Share</th><th class="text-end">ER Share</th></tr>
                    </thead>
                    <tbody>
                        <tr><td class="text-muted">SSS</td>
                            <td class="text-end">${peso(gc.sss * 2)}</td>
                            <td class="text-end text-muted">—</td></tr>
                        <tr><td class="text-muted">PhilHealth</td>
                            <td class="text-end">${peso(gc.philhealth * 2)}</td>
                            <td class="text-end text-muted">—</td></tr>
                        <tr><td class="text-muted">Pag-IBIG</td>
                            <td class="text-end">${peso(gc.pagibig * 2)}</td>
                            <td class="text-end text-muted">—</td></tr>
                        <tr><td class="text-muted">Withholding Tax</td>
                            <td class="text-end">${peso(gc.tax * 12)}</td>
                            <td class="text-end text-muted">—</td></tr>
                        <tr class="fw-semibold table-light">
                            <td>Est. Net Pay</td>
                            <td class="text-end">
                                ${peso(
                                    parseFloat(emp.basicSalary)
                                    - (gc.sss * 2)
                                    - (gc.philhealth * 2)
                                    - (gc.pagibig * 2)
                                    - (gc.tax * 12)
                                )}
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>`;
    }

    /* ─── Bulk Update ───────────────────────────────────────── */
    function renderBulkList() {
        const pos = bulkPosSel.value;
        if (!pos) { bulkEmpList.classList.add('d-none'); return; }

        const list = allEmployees.filter(e => e.position === pos);
        $('bulk-count-label').textContent = `${list.length} employee(s) in this position`;
        bulkChecked.clear();
        bulkSelectAll.checked = false;

        bulkEmps.innerHTML = list.map(emp => `
            <div class="col-md-6">
                <div class="border rounded p-3">
                    <div class="form-check">
                        <input class="form-check-input bulk-chk" type="checkbox"
                               value="${emp.id}" id="bc-${emp.id}">
                        <label class="form-check-label w-100" for="bc-${emp.id}" style="cursor:pointer">
                            <div class="fw-semibold small">${emp.fullName}</div>
                            <div class="text-muted small">${emp.id}&middot; ${emp.department ?? ''}</div>
                            <div class="text-muted small mt-1">Current: ${peso(emp.basicSalary)}</div>
                        </label>
                    </div>
                </div>
            </div>`).join('');

        bulkEmpList.classList.remove('d-none');

        bulkEmps.querySelectorAll('.bulk-chk').forEach(chk => {
            chk.addEventListener('change', () =>
                chk.checked ? bulkChecked.add(chk.value) : bulkChecked.delete(chk.value));
        });
    }

    async function applyBulk() {
        const newSalary = parseFloat(bulkSalaryInp.value);

        if (!newSalary || newSalary <= 0) {
            Swal.fire({ icon: 'warning', title: 'Missing Salary',
                text: 'Enter a valid salary amount.', confirmButtonColor: '#6c757d' });
            return;
        }
        if (!bulkChecked.size) {
            Swal.fire({ icon: 'warning', title: 'No Selection',
                text: 'Select at least one employee.', confirmButtonColor: '#6c757d' });
            return;
        }

        const confirm = await Swal.fire({
            title: 'Confirm Bulk Update',
            html: `Update <strong>${bulkChecked.size}</strong> employee(s) to <strong>${peso(newSalary)}</strong>?`,
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#0d6efd', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Update',
        });

        if (!confirm.isConfirmed) return;

        const btn = $('btn-bulk-apply');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

        try {
            const data = await apiFetch(ROUTES.bulkUpdate, {
                method: 'POST',
                body:   JSON.stringify({
                    user_ids:    [...bulkChecked],
                    basicSalary: newSalary,
                }),
            });

            Swal.fire({ icon: 'success', title: 'Done!',
                text: data.message, timer: 2000, showConfirmButton: false });

            closeBulk();
            await loadEmployees();

        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message });
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy me-1"></i>Apply';
        }
    }

    function closeBulk() {
        bulkPanel.classList.add('d-none');
        bulkPosSel.value    = '';
        bulkSalaryInp.value = '';
        bulkChecked.clear();
        bulkEmpList.classList.add('d-none');
        $('btn-bulk-toggle').innerHTML = '<i class="bi bi-lightning me-1"></i> Bulk Update';
    }

    /* ─── Event Binding ─────────────────────────────────────── */
    function bindEvents() {
        // Filters — debounce search, instant for dropdowns
        let searchTimer;
        filterSearch.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(applyFilters, 300);
        });
        filterDept.addEventListener('change', applyFilters);
        filterPos.addEventListener('change',  applyFilters);

        // Bulk panel toggle
        $('btn-bulk-toggle').addEventListener('click', () => {
            const isOpen = !bulkPanel.classList.toggle('d-none');
            $('btn-bulk-toggle').innerHTML = isOpen
                ? '<i class="bi bi-x me-1"></i> Close'
                : '<i class="bi bi-lightning me-1"></i> Bulk Update';
        });

        $('btn-bulk-cancel').addEventListener('click', closeBulk);
        $('btn-bulk-apply').addEventListener('click',  applyBulk);
        bulkPosSel.addEventListener('change', renderBulkList);

        bulkSelectAll.addEventListener('change', () => {
            bulkEmps.querySelectorAll('.bulk-chk').forEach(chk => {
                chk.checked = bulkSelectAll.checked;
                bulkSelectAll.checked
                    ? bulkChecked.add(chk.value)
                    : bulkChecked.delete(chk.value);
            });
        });

        // Edit modal preview
        $('edit-basic-salary').addEventListener('input',
            e => refreshEditPreview(e.target.value));
        $('btn-save-edit').addEventListener('click', saveEdit);

        // Table row buttons (event delegation)
        tbody.addEventListener('click', e => {
            const editBtn = e.target.closest('.btn-edit');
            const viewBtn = e.target.closest('.btn-view');
            if (editBtn) openEdit(editBtn);
            if (viewBtn) openDetails(viewBtn.dataset.id);
        });
    }

    /* ─── Init ──────────────────────────────────────────────── */
    async function init() {
        bindEvents();
        await loadEmployees();
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', () => SalaryManager.init());
</script>
@endpush