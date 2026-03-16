@extends('layouts.main')

@section('title', 'Department Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Organization</a></li>
        <li class="breadcrumb-item active">Departments</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">Department Management</h4>
        <small class="text-muted">Organize and manage company departments</small>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="openAddModal()">Add Department</button>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body h-100">
            <div class="text-muted small mb-1">Total Departments</div>
            <div class="fs-4 fw-bold" id="stat-total">—</div>
            <div class="text-muted small" id="stat-active-sub">— active</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body h-100">
            <div class="text-muted small mb-1">Total Employees</div>
            <div class="fs-4 fw-bold" id="stat-employees">—</div>
            <div class="text-muted small">Across all departments</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body h-100">
            <div class="text-muted small mb-1">Largest Department</div>
            <div class="fw-bold" id="stat-largest-name">—</div>
            <div class="text-muted small" id="stat-largest-count">—</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-body h-100">
            <div class="text-muted small mb-1">Branches</div>
            <div class="fs-4 fw-bold" id="stat-branches">—</div>
            <div class="text-muted small" id="stat-branches-sub">—</div>
        </div>
    </div>
</div>

{{-- Filter + Table --}}
<div class="card shadow-sm">
    <div class="card-header">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" id="filter-search" class="form-control form-control-sm"
                    placeholder="Search name, code…" oninput="debounceList()">
            </div>
            <div class="col-md-3">
                <select id="filter-branch" class="form-select form-select-sm" onchange="loadList()">
                    <option value="">All Branches</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filter-status" class="form-select form-select-sm" onchange="loadList()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:22%">Department</th>
                        <th style="width:10%">Code</th>
                        <th style="width:22%">Head(s)</th>
                        <th style="width:10%">Employees</th>
                        <th style="width:16%">Branch</th>
                        <th style="width:10%">Status</th>
                        <th class="text-center pe-3" style="width:10%">Actions</th>
                    </tr>
                </thead>
                <tbody id="dept-tbody">
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <span class="spinner-border spinner-border-sm me-2"></span>Loading…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted small d-flex justify-content-between">
        <span id="table-count">—</span>
        <span id="table-emp-count"></span>
    </div>
</div>

{{-- ===== MODAL: ADD / EDIT ===== --}}
<div class="modal fade" id="deptModal" tabindex="-1" aria-labelledby="deptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deptModalLabel">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-id">

                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-medium">
                            Department Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="modal-name" class="form-control form-control-sm"
                            placeholder="e.g., Production"
                            oninput="onNameInput()">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-medium">
                            Code <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="modal-code" class="form-control form-control-sm"
                            placeholder="e.g., PROD"
                            oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Description</label>
                        <textarea id="modal-description" class="form-control form-control-sm"
                            rows="2" placeholder="Brief description…"></textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-medium">Branch</label>
                        <select id="modal-branch" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Status</label>
                        <select id="modal-status" class="form-select form-select-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    {{-- Department Heads --}}
                    <div class="col-12">
                        <label class="form-label fw-medium">Department Head(s)</label>
                        <div id="heads-container" class="d-flex flex-column gap-2"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                            onclick="addHeadRow()">+ Add Another Head</button>
                        <div class="form-text">
                            Shows employees with supervisory/managerial positions in this department.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-secondary btn-sm" id="modal-save-btn"
                    onclick="saveDepartment()">Create Department</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ─── CONFIG ──────────────────────────────────────────────────────────────
    const CSRF = '{{ csrf_token() }}';
    const BASE = '{{ url("/admin/departments") }}';

    // ─── STATE ───────────────────────────────────────────────────────────────
    let headRows      = [];   // [{ value: 'EMP001' }, …]
    let headCandidates= [];   // fetched from server
    let searchTimer   = null;

    // ─── INIT ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadStats();
        loadBranches();
        loadList();

        document.getElementById('filter-search').addEventListener('input', debounceList);
        document.getElementById('filter-branch').addEventListener('change', loadList);
        document.getElementById('filter-status').addEventListener('change', loadList);
    });

    // ─── STATS ────────────────────────────────────────────────────────────────
    function loadStats() {
        fetch(BASE + '/stats', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (d) {
                setText('stat-total',       d.total);
                setText('stat-active-sub',  d.active + ' active');
                setText('stat-employees',   Number(d.total_employees).toLocaleString());
                setText('stat-largest-name',d.largest_name);
                setText('stat-largest-count', d.largest_count + ' employees');
                setText('stat-branches',    d.branch_count);
                setText('stat-branches-sub',d.main_branch);
            })
            .catch(console.error);
    }

    // ─── BRANCHES (filter + modal) ────────────────────────────────────────────
    function loadBranches() {
        fetch(BASE + '/branches', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (branches) {
                const filterSel = document.getElementById('filter-branch');
                const modalSel  = document.getElementById('modal-branch');

                filterSel.innerHTML = '<option value="">All Branches</option>';
                modalSel.innerHTML  = '<option value="">— None —</option>';

                branches.forEach(function (b) {
                    filterSel.innerHTML += '<option value="' + x(b) + '">' + x(b) + '</option>';
                    modalSel.innerHTML  += '<option value="' + x(b) + '">' + x(b) + '</option>';
                });
            })
            .catch(console.error);
    }

    // ─── LIST ─────────────────────────────────────────────────────────────────
    window.loadList = function () {
        const tbody  = document.getElementById('dept-tbody');
        const search = document.getElementById('filter-search').value;
        const branch = document.getElementById('filter-branch').value;
        const status = document.getElementById('filter-status').value;

        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">' +
            '<span class="spinner-border spinner-border-sm me-2"></span>Loading\u2026</td></tr>';

        const url = BASE + '/list?' + new URLSearchParams({ search, branch, status });

        fetch(url, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(renderTable)
            .catch(function () {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">' +
                    'Failed to load data.</td></tr>';
            });
    };

    function renderTable(data) {
        const tbody = document.getElementById('dept-tbody');

        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5">' +
                'No departments found.</td></tr>';
            setText('table-count', 'Showing 0 departments');
            setText('table-emp-count', '');
            return;
        }

        const totalEmp = data.reduce(function (s, d) { return s + (d.employee_count || 0); }, 0);

        tbody.innerHTML = data.map(function (d) {
            const statusBadge = d.status === 'active'
                ? '<span class="badge bg-secondary">Active</span>'
                : '<span class="badge bg-primary">Inactive</span>';

            const actions = '<button class="btn btn-sm btn-link p-1 text-secondary" title="Edit" ' +
                'onclick="openEditModal(' + d.id + ')"><i class="bi bi-pencil"></i></button>' +
                '<button class="btn btn-sm btn-link p-1 text-secondary" title="Delete" ' +
                'onclick="deleteDept(' + d.id + ', \'' + x(d.name) + '\', ' + d.employee_count + ')"><i class="bi bi-trash"></i></button>';

            return '<tr>' +
                '<td class="ps-3">' +
                    '<div class="fw-medium">' + x(d.name) + '</div>' +
                    '<small class="text-muted">' + x(d.description) + '</small>' +
                '</td>' +
                '<td><span class="badge bg-secondary">' + x(d.code) + '</span></td>' +
                '<td>' +
                    '<div class="small">' + x(d.head_names) + '</div>' +
                '</td>' +
                '<td class="fw-medium">' + d.employee_count + '</td>' +
                '<td class="text-muted small">' + x(d.branch) + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td class="text-center pe-3">' + actions + '</td>' +
                '</tr>';
        }).join('');

        setText('table-count', 'Showing ' + data.length + ' department(s)');
        setText('table-emp-count', totalEmp + ' employees in view');
    }

    // ─── HEAD ROWS ────────────────────────────────────────────────────────────
    function loadHeadCandidates(deptName, callback) {
        const url = BASE + '/head-candidates?' + new URLSearchParams({ department: deptName || '' });
        fetch(url, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (data) {
                headCandidates = data;
                if (callback) callback();
            })
            .catch(console.error);
    }

    function renderHeadRows() {
        const container = document.getElementById('heads-container');
        container.innerHTML = '';

        if (!headRows.length) headRows = [{ value: '' }];

        headRows.forEach(function (row, idx) {
            const div = document.createElement('div');
            div.className = 'input-group input-group-sm';

            const options = headCandidates.map(function (c) {
                const sel = row.value === c.id ? ' selected' : '';
                return '<option value="' + x(c.id) + '"' + sel + '>' +
                    x(c.full_name) + ' \u2014 ' + x(c.position) + '</option>';
            }).join('');

            const removeBtn = headRows.length > 1
                ? '<button class="btn btn-outline-secondary" type="button" ' +
                  'onclick="removeHeadRow(' + idx + ')"><i class="bi bi-x"></i></button>'
                : '';

            div.innerHTML = '<select class="form-select" ' +
                'onchange="headRows[' + idx + '].value = this.value">' +
                '<option value="">\u2014 Select head \u2014</option>' +
                options +
                '</select>' + removeBtn;

            container.appendChild(div);
        });
    }

    window.addHeadRow = function () {
        headRows.push({ value: '' });
        renderHeadRows();
    };

    window.removeHeadRow = function (idx) {
        headRows.splice(idx, 1);
        renderHeadRows();
    };

    window.onNameInput = function () {
        const name = document.getElementById('modal-name').value.trim();
        loadHeadCandidates(name, renderHeadRows);
    };

    // ─── ADD MODAL ────────────────────────────────────────────────────────────
    window.openAddModal = function () {
        setText('deptModalLabel', 'Add Department');
        document.getElementById('modal-save-btn').textContent = 'Create Department';
        document.getElementById('modal-id').value          = '';
        document.getElementById('modal-name').value        = '';
        document.getElementById('modal-code').value        = '';
        document.getElementById('modal-description').value = '';
        document.getElementById('modal-status').value      = 'active';

        headRows       = [{ value: '' }];
        headCandidates = [];
        renderHeadRows();

        new bootstrap.Modal(document.getElementById('deptModal')).show();
    };

    // ─── EDIT MODAL ───────────────────────────────────────────────────────────
    window.openEditModal = function (id) {
        // Fetch from list (already loaded) — find in DOM data or re-fetch
        fetch(BASE + '/list', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (data) {
                const d = data.find(function (r) { return r.id === id; });
                if (!d) return;

                setText('deptModalLabel', 'Edit Department');
                document.getElementById('modal-save-btn').textContent = 'Save Changes';
                document.getElementById('modal-id').value          = d.id;
                document.getElementById('modal-name').value        = d.name;
                document.getElementById('modal-code').value        = d.code;
                document.getElementById('modal-description').value = d.description;
                document.getElementById('modal-status').value      = d.status;

                // Set branch after branches are populated
                setTimeout(function () {
                    document.getElementById('modal-branch').value = d.branch !== '—' ? d.branch : '';
                }, 50);

                headRows = (d.head_ids || []).map(function (v) { return { value: v }; });
                if (!headRows.length) headRows = [{ value: '' }];

                loadHeadCandidates(d.name, renderHeadRows);

                new bootstrap.Modal(document.getElementById('deptModal')).show();
            });
    };

    // ─── SAVE ─────────────────────────────────────────────────────────────────
    window.saveDepartment = function () {
        const id          = document.getElementById('modal-id').value;
        const name        = document.getElementById('modal-name').value.trim();
        const code        = document.getElementById('modal-code').value.trim();
        const description = document.getElementById('modal-description').value.trim();
        const branch      = document.getElementById('modal-branch').value;
        const status      = document.getElementById('modal-status').value;
        const headIds     = headRows.map(function (r) { return r.value; }).filter(Boolean);

        if (!name) { toast('Department name is required.', 'warning'); return; }
        if (!code) { toast('Department code is required.', 'warning'); return; }

        const btn     = document.getElementById('modal-save-btn');
        btn.disabled  = true;
        const origTxt = btn.textContent;
        btn.textContent = 'Saving\u2026';

        const url    = id ? BASE + '/' + id : BASE;
        const method = id ? 'PATCH' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({
                name, code, description, branch, status,
                head_employee_ids: headIds,
            }),
        })
        .then(handleJson)
        .then(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('deptModal')).hide();
            toast(res.message);
            loadStats();
            loadList();
        })
        .catch(handleError)
        .finally(function () {
            btn.disabled    = false;
            btn.textContent = origTxt;
        });
    };

    // ─── DELETE ───────────────────────────────────────────────────────────────
    window.deleteDept = function (id, name, empCount) {
        if (empCount > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Delete',
                text: '"' + name + '" has ' + empCount + ' active employee(s). Reassign them first.',
            });
            return;
        }

        Swal.fire({
            title: 'Delete Department?',
            html: '<strong>' + x(name) + '</strong> will be permanently removed.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch(BASE + '/' + id, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
            })
            .then(handleJson)
            .then(function (res) {
                toast(res.message);
                loadStats();
                loadList();
            })
            .catch(handleError);
        });
    };

    // ─── FILTERS ──────────────────────────────────────────────────────────────
    window.resetFilters = function () {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-branch').value = '';
        document.getElementById('filter-status').value = '';
        loadList();
    };

    window.debounceList = function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadList, 350);
    };

    // ─── UTILITIES ────────────────────────────────────────────────────────────
    function handleJson(r) {
        return r.json().then(function (data) {
            if (!r.ok) return Promise.reject(data);
            return data;
        });
    }

    function handleError(err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err && err.message ? err.message : 'Something went wrong.',
        });
    }

    function toast(msg, icon) {
        Swal.fire({
            toast: true, position: 'top-end',
            icon: icon || 'success', title: msg,
            showConfirmButton: false, timer: 2800, timerProgressBar: true,
        });
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val !== null && val !== undefined ? val : '—';
    }

    function x(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})();
</script>
@endpush