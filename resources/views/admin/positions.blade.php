@extends('layouts.main')

@section('title', 'Position Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Organization</a></li>
        <li class="breadcrumb-item active">Positions</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">Position Management</h4>
        <small class="text-muted">Manage job positions and their departments</small>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="openAddModal()">Add Position</button>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card card-body h-100">
            <div class="text-muted small mb-1">Total Positions</div>
            <div class="fw-bold fs-4" id="stat-total">—</div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card card-body h-100">
            <div class="text-muted small mb-1">Active Positions</div>
            <div class="fw-bold fs-4" id="stat-active">—</div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card card-body h-100">
            <div class="text-muted small mb-1">Departments Covered</div>
            <div class="fw-bold fs-4" id="stat-depts">—</div>
        </div>
    </div>
</div>

{{-- Filters + Table --}}
<div class="card shadow-sm">
    <div class="card-header">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" id="filter-search" class="form-control form-control-sm"
                    placeholder="Search position or department…"
                    oninput="debounceList()">
            </div>
            <div class="col-md-4">
                <select id="filter-dept" class="form-select form-select-sm" onchange="loadList()">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filter-status" class="form-select form-select-sm" onchange="loadList()">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                    Clear
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Position</th>
                        <th>Department</th>
                        <th class="d-none d-md-table-cell">Description</th>
                        <th class="text-center">Employees</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="positions-tbody">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <span class="spinner-border spinner-border-sm me-2"></span>Loading…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted small">
        <span id="table-count">—</span>
    </div>
</div>

{{-- ===== MODAL: ADD / EDIT (shared) ===== --}}
<div class="modal fade" id="posModal" tabindex="-1" aria-labelledby="posModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="posModalLabel">Add Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-id">

                <div class="mb-3">
                    <label class="form-label fw-medium">
                        Position Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="modal-name" class="form-control form-control-sm"
                        placeholder="e.g., Production Operator">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">
                        Department <span class="text-danger">*</span>
                    </label>
                    <select id="modal-dept" class="form-select form-select-sm">
                        <option value="">Select Department</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Description</label>
                    <textarea id="modal-description" class="form-control form-control-sm"
                        rows="3" placeholder="Brief description of the position…"></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label fw-medium">Status</label>
                    <select id="modal-status" class="form-select form-select-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-secondary btn-sm" id="modal-save-btn"
                    onclick="savePosition()">Add Position</button>
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
    const BASE = '{{ url("/admin/positions") }}';

    // ─── STATE ───────────────────────────────────────────────────────────────
    let searchTimer = null;

    // ─── INIT ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadStats();
        loadDepartments();
        loadList();

        document.getElementById('filter-search').addEventListener('input', debounceList);
        document.getElementById('filter-dept').addEventListener('change', loadList);
        document.getElementById('filter-status').addEventListener('change', loadList);
    });

    // ─── STATS ────────────────────────────────────────────────────────────────
    function loadStats() {
        fetch(BASE + '/stats', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (d) {
                setText('stat-total',  d.total);
                setText('stat-active', d.active);
                setText('stat-depts',  d.depts);
            })
            .catch(console.error);
    }

    // ─── DEPARTMENTS ──────────────────────────────────────────────────────────
    function loadDepartments() {
        fetch(BASE + '/departments', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (depts) {
                const opts = depts.map(function (d) {
                    return '<option value="' + x(d) + '">' + x(d) + '</option>';
                }).join('');

                document.getElementById('filter-dept').innerHTML =
                    '<option value="">All Departments</option>' + opts;
                document.getElementById('modal-dept').innerHTML =
                    '<option value="">Select Department</option>' + opts;
            })
            .catch(console.error);
    }

    // ─── LIST ─────────────────────────────────────────────────────────────────
    window.loadList = function () {
        const tbody  = document.getElementById('positions-tbody');
        const search = document.getElementById('filter-search').value;
        const dept   = document.getElementById('filter-dept').value;
        const status = document.getElementById('filter-status').value;

        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">' +
            '<span class="spinner-border spinner-border-sm me-2"></span>Loading\u2026</td></tr>';

        const url = BASE + '/list?' + new URLSearchParams({ search, department: dept, status });

        fetch(url, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(renderTable)
            .catch(function () {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">' +
                    'Failed to load data.</td></tr>';
            });
    };

    function renderTable(data) {
        const tbody = document.getElementById('positions-tbody');

        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-5">' +
                'No positions found.</td></tr>';
            setText('table-count', 'Showing 0 positions');
            return;
        }

        tbody.innerHTML = data.map(function (p) {
            const statusBadge = p.status === 'active'
                ? '<span class="badge bg-secondary">Active</span>'
                : '<span class="badge bg-primary">Inactive</span>';

            return '<tr>' +
                '<td class="ps-3 fw-medium">' + x(p.name) + '</td>' +
                '<td class="text-muted small">' + x(p.department) + '</td>' +
                '<td class="d-none d-md-table-cell text-muted small">' + x(p.description || '—') + '</td>' +
                '<td class="text-center">' + p.employee_count + '</td>' +
                '<td class="text-center">' + statusBadge + '</td>' +
                '<td class="text-center pe-3">' +
                    '<button class="btn btn-sm btn-link p-1 text-secondary" title="Edit" ' +
                        'onclick="openEditModal(' + p.id + ')"><i class="bi bi-pencil"></i></button>' +
                    '<button class="btn btn-sm btn-link p-1 text-secondary" title="Delete" ' +
                        'onclick="deletePosition(' + p.id + ', \'' + x(p.name) + '\', ' + p.employee_count + ')"><i class="bi bi-trash"></i></button>' +
                '</td>' +
                '</tr>';
        }).join('');

        setText('table-count', 'Showing ' + data.length + ' position(s)');
    }

    // ─── ADD MODAL ────────────────────────────────────────────────────────────
    window.openAddModal = function () {
        setText('posModalLabel', 'Add Position');
        document.getElementById('modal-save-btn').textContent = 'Add Position';
        document.getElementById('modal-id').value          = '';
        document.getElementById('modal-name').value        = '';
        document.getElementById('modal-dept').value        = '';
        document.getElementById('modal-description').value = '';
        document.getElementById('modal-status').value      = 'active';
        new bootstrap.Modal(document.getElementById('posModal')).show();
    };

    // ─── EDIT MODAL ───────────────────────────────────────────────────────────
    window.openEditModal = function (id) {
        // Re-fetch the row from the list to avoid stale data
        fetch(BASE + '/list', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (data) {
                const p = data.find(function (r) { return r.id === id; });
                if (!p) return;

                setText('posModalLabel', 'Edit Position');
                document.getElementById('modal-save-btn').textContent = 'Save Changes';
                document.getElementById('modal-id').value          = p.id;
                document.getElementById('modal-name').value        = p.name;
                document.getElementById('modal-description').value = p.description;
                document.getElementById('modal-status').value      = p.status;

                setTimeout(function () {
                    document.getElementById('modal-dept').value = p.department !== '—' ? p.department : '';
                }, 50);

                new bootstrap.Modal(document.getElementById('posModal')).show();
            });
    };

    // ─── SAVE ─────────────────────────────────────────────────────────────────
    window.savePosition = function () {
        const id   = document.getElementById('modal-id').value;
        const name = document.getElementById('modal-name').value.trim();
        const dept = document.getElementById('modal-dept').value;
        const desc = document.getElementById('modal-description').value.trim();
        const stat = document.getElementById('modal-status').value;

        if (!name) { toast('Position name is required.', 'warning'); return; }
        if (!dept) { toast('Department is required.', 'warning');     return; }

        const btn     = document.getElementById('modal-save-btn');
        btn.disabled  = true;
        const origTxt = btn.textContent;
        btn.textContent = 'Saving\u2026';

        fetch(id ? BASE + '/' + id : BASE, {
            method:  id ? 'PATCH' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ name, department: dept, description: desc, status: stat }),
        })
        .then(handleJson)
        .then(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('posModal')).hide();
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
    window.deletePosition = function (id, name, empCount) {
        if (empCount > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Delete',
                text: '"' + name + '" has ' + empCount + ' active employee(s). Reassign them first.',
            });
            return;
        }

        Swal.fire({
            title: 'Delete Position?',
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
        document.getElementById('filter-dept').value   = '';
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