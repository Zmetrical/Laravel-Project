@extends('layouts.main')

@section('title', 'Branch Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Organization</a></li>
        <li class="breadcrumb-item active">Branch Management</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">Branch Management</h4>
        <small class="text-muted">Manage company branches and locations</small>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="openAddModal()">Add Branch</button>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach ([
        ['id' => 'stat-total',  'label' => 'Total Branches',  'sub_id' => 'stat-active-sub'],
        ['id' => 'stat-emp',    'label' => 'Total Employees',  'sub' => 'Across all branches'],
        ['id' => 'stat-depts',  'label' => 'Departments',      'sub' => 'Total departments'],
        ['id' => 'stat-cities', 'label' => 'Cities Covered',   'sub' => 'Unique locations'],
    ] as $c)
    <div class="col-6 col-xl-3">
        <div class="card card-body shadow-sm">
            <div class="text-muted small mb-1">{{ $c['label'] }}</div>
            <div class="fw-semibold fs-5" id="{{ $c['id'] }}">—</div>
            @if(isset($c['sub_id']))
                <div class="text-muted small" id="{{ $c['sub_id'] }}">—</div>
            @else
                <div class="text-muted small">{{ $c['sub'] }}</div>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Branch Cards --}}
<div class="row g-3" id="branchGrid"></div>

<div id="emptyState" class="text-center py-5 d-none">
    <p class="text-muted mb-0">No branches found. Add one to get started.</p>
</div>

{{-- ===== MODAL: ADD / EDIT ===== --}}
<div class="modal fade" id="branchModal" tabindex="-1" aria-labelledby="branchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="branchModalLabel">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-id">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-medium">
                            Branch Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="modal-name" class="form-control form-control-sm"
                            placeholder="e.g., Meycauayan Main Office">
                        <div class="invalid-feedback">Branch name is required.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">
                            Branch Code <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="modal-code" class="form-control form-control-sm"
                            placeholder="e.g., MYC-MAIN" maxlength="20"
                            oninput="this.value = this.value.toUpperCase()">
                        <div class="invalid-feedback">Branch code is required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">
                            Address <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="modal-address" class="form-control form-control-sm"
                            placeholder="Street address, Barangay">
                        <div class="invalid-feedback">Address is required.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">
                            City / Municipality <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="modal-city" class="form-control form-control-sm"
                            placeholder="e.g., Meycauayan, Bulacan">
                        <div class="invalid-feedback">City is required.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">
                            Contact Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="modal-contact" class="form-control form-control-sm"
                            placeholder="+63 XX XXX XXXX">
                        <div class="invalid-feedback">Contact number is required.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email Address</label>
                        <input type="email" id="modal-email" class="form-control form-control-sm"
                            placeholder="branch@company.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">
                            Branch Manager <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="modal-manager" class="form-control form-control-sm"
                            placeholder="Manager's full name">
                        <div class="invalid-feedback">Branch manager is required.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Status</label>
                        <select id="modal-status" class="form-select form-select-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end pb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modal-is-main">
                            <label class="form-check-label fw-medium" for="modal-is-main">
                                Set as main branch
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-secondary btn-sm" id="modal-save-btn"
                    onclick="saveBranch()">Save Branch</button>
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
    const BASE = '{{ url("/admin/branches") }}';

    const REQUIRED = ['modal-name', 'modal-code', 'modal-address', 'modal-city', 'modal-contact', 'modal-manager'];

    // ─── INIT ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadStats();
        loadBranches();
    });

    // ─── STATS ────────────────────────────────────────────────────────────────
    function loadStats() {
        fetch(BASE + '/stats', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (d) {
                setText('stat-total',      d.total);
                setText('stat-active-sub', d.active + ' active');
                setText('stat-emp',        Number(d.total_emp).toLocaleString());
                setText('stat-depts',      d.total_depts);
                setText('stat-cities',     d.cities);
            })
            .catch(console.error);
    }

    // ─── CARDS ────────────────────────────────────────────────────────────────
    function loadBranches() {
        const grid  = document.getElementById('branchGrid');
        const empty = document.getElementById('emptyState');

        grid.innerHTML = '<div class="col-12 text-center text-muted py-5">' +
            '<span class="spinner-border spinner-border-sm me-2"></span>Loading\u2026</div>';
        empty.classList.add('d-none');

        fetch(BASE + '/list', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (data) {
                if (!data.length) {
                    grid.innerHTML = '';
                    empty.classList.remove('d-none');
                    return;
                }
                grid.innerHTML = data.map(buildCard).join('');
            })
            .catch(function () {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-4">Failed to load branches.</div>';
            });
    }

    function buildCard(b) {
        const statusBadge = b.status === 'active'
            ? '<span class="badge bg-secondary">Active</span>'
            : '<span class="badge bg-primary">Inactive</span>';

        const mainBadge = b.is_main
            ? '<span class="badge bg-secondary ms-1" style="font-size:.65rem">MAIN</span>'
            : '';

        const emailRow = b.email
            ? '<li class="d-flex gap-2 mb-1">' +
              '<i class="bi bi-envelope text-muted flex-shrink-0 mt-1"></i>' +
              '<span class="text-truncate small">' + x(b.email) + '</span></li>'
            : '';

        return '<div class="col-md-6 col-xl-4">' +
            '<div class="card shadow-sm h-100">' +
            '<div class="card-body d-flex flex-column">' +

            // Header
            '<div class="d-flex justify-content-between align-items-start mb-3">' +
                '<div>' +
                    '<div class="fw-semibold">' + x(b.name) + mainBadge + '</div>' +
                    '<small class="text-muted">' + x(b.code) + '</small>' +
                '</div>' +
                statusBadge +
            '</div>' +

            // Details
            '<ul class="list-unstyled flex-grow-1 mb-3">' +
                '<li class="d-flex gap-2 mb-1">' +
                    '<i class="bi bi-geo-alt text-muted flex-shrink-0 mt-1"></i>' +
                    '<span class="small">' + x(b.address) + '<br>' +
                    '<span class="text-muted">' + x(b.city) + '</span></span>' +
                '</li>' +
                '<li class="d-flex gap-2 mb-1">' +
                    '<i class="bi bi-telephone text-muted flex-shrink-0 mt-1"></i>' +
                    '<span class="small">' + x(b.contact_number) + '</span>' +
                '</li>' +
                emailRow +
                '<li class="d-flex gap-2 mb-1">' +
                    '<i class="bi bi-person text-muted flex-shrink-0 mt-1"></i>' +
                    '<span class="small"><span class="text-muted">Manager:</span> ' + x(b.manager_name) + '</span>' +
                '</li>' +
            '</ul>' +

            // Counts
            '<div class="row g-2 text-center mb-3">' +
                '<div class="col-6">' +
                    '<div class="border rounded py-2">' +
                        '<div class="fw-semibold">' + b.employee_count + '</div>' +
                        '<div class="text-muted" style="font-size:.72rem">Employees</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-6">' +
                    '<div class="border rounded py-2">' +
                        '<div class="fw-semibold">' + b.dept_count + '</div>' +
                        '<div class="text-muted" style="font-size:.72rem">Departments</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +

            // Actions
            '<div class="d-flex gap-2">' +
                '<button class="btn btn-outline-secondary btn-sm flex-grow-1" ' +
                    'onclick="openEditModal(' + b.id + ')">Edit</button>' +
                '<button class="btn btn-outline-secondary btn-sm" ' +
                    'onclick="deleteBranch(' + b.id + ', \'' + x(b.name) + '\', ' + b.employee_count + ', ' + (b.is_main ? 'true' : 'false') + ')" ' +
                    'title="Delete"><i class="bi bi-trash"></i></button>' +
            '</div>' +

            '</div></div></div>';
    }

    // ─── ADD MODAL ────────────────────────────────────────────────────────────
    window.openAddModal = function () {
        resetForm();
        setText('branchModalLabel', 'Add New Branch');
        document.getElementById('modal-save-btn').textContent = 'Save Branch';
        new bootstrap.Modal(document.getElementById('branchModal')).show();
    };

    // ─── EDIT MODAL ───────────────────────────────────────────────────────────
    window.openEditModal = function (id) {
        fetch(BASE + '/list', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(function (data) {
                const b = data.find(function (r) { return r.id === id; });
                if (!b) return;

                resetForm();
                setText('branchModalLabel', 'Edit Branch');
                document.getElementById('modal-save-btn').textContent = 'Update Branch';
                document.getElementById('modal-id').value       = b.id;
                document.getElementById('modal-name').value     = b.name;
                document.getElementById('modal-code').value     = b.code;
                document.getElementById('modal-address').value  = b.address;
                document.getElementById('modal-city').value     = b.city;
                document.getElementById('modal-contact').value  = b.contact_number;
                document.getElementById('modal-email').value    = b.email;
                document.getElementById('modal-manager').value  = b.manager_name;
                document.getElementById('modal-status').value   = b.status;
                document.getElementById('modal-is-main').checked= b.is_main;

                new bootstrap.Modal(document.getElementById('branchModal')).show();
            });
    };

    // ─── SAVE ─────────────────────────────────────────────────────────────────
    window.saveBranch = function () {
        if (!validateForm()) return;

        const id   = document.getElementById('modal-id').value;
        const body = {
            name:           document.getElementById('modal-name').value.trim(),
            code:           document.getElementById('modal-code').value.trim().toUpperCase(),
            address:        document.getElementById('modal-address').value.trim(),
            city:           document.getElementById('modal-city').value.trim(),
            contact_number: document.getElementById('modal-contact').value.trim(),
            email:          document.getElementById('modal-email').value.trim(),
            manager_name:   document.getElementById('modal-manager').value.trim(),
            status:         document.getElementById('modal-status').value,
            is_main:        document.getElementById('modal-is-main').checked,
        };

        const btn    = document.getElementById('modal-save-btn');
        btn.disabled = true;
        const orig   = btn.textContent;
        btn.textContent = 'Saving\u2026';

        fetch(id ? BASE + '/' + id : BASE, {
            method:  id ? 'PATCH' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify(body),
        })
        .then(handleJson)
        .then(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('branchModal')).hide();
            toast(res.message);
            loadStats();
            loadBranches();
        })
        .catch(handleError)
        .finally(function () {
            btn.disabled    = false;
            btn.textContent = orig;
        });
    };

    // ─── DELETE ───────────────────────────────────────────────────────────────
    window.deleteBranch = function (id, name, empCount, isMain) {
        if (isMain) {
            Swal.fire({ icon: 'warning', title: 'Cannot Delete',
                text: 'Cannot delete the main branch. Set another branch as main first.' });
            return;
        }
        if (empCount > 0) {
            Swal.fire({ icon: 'warning', title: 'Cannot Delete',
                text: '"' + name + '" has ' + empCount + ' active employee(s). Reassign them first.' });
            return;
        }

        Swal.fire({
            title: 'Delete Branch?',
            html:  '<strong>' + x(name) + '</strong> will be permanently removed.',
            icon:  'question',
            showCancelButton:   true,
            confirmButtonText:  'Yes, Delete',
            confirmButtonColor: '#6c757d',
            reverseButtons:     true,
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
                loadBranches();
            })
            .catch(handleError);
        });
    };

    // ─── FORM HELPERS ─────────────────────────────────────────────────────────
    function resetForm() {
        document.getElementById('modal-id').value        = '';
        document.getElementById('modal-name').value      = '';
        document.getElementById('modal-code').value      = '';
        document.getElementById('modal-address').value   = '';
        document.getElementById('modal-city').value      = '';
        document.getElementById('modal-contact').value   = '';
        document.getElementById('modal-email').value     = '';
        document.getElementById('modal-manager').value   = '';
        document.getElementById('modal-status').value    = 'active';
        document.getElementById('modal-is-main').checked = false;
        REQUIRED.forEach(function (id) {
            document.getElementById(id).classList.remove('is-invalid');
        });
    }

    function validateForm() {
        let valid = true;
        REQUIRED.forEach(function (id) {
            const el = document.getElementById(id);
            if (!el.value.trim()) {
                el.classList.add('is-invalid');
                valid = false;
            } else {
                el.classList.remove('is-invalid');
            }
        });
        return valid;
    }

    // ─── UTILITIES ────────────────────────────────────────────────────────────
    function handleJson(r) {
        return r.json().then(function (data) {
            if (!r.ok) return Promise.reject(data);
            return data;
        });
    }

    function handleError(err) {
        Swal.fire({
            icon: 'error', title: 'Error',
            text: err && err.message ? err.message : 'Something went wrong.',
        });
    }

    function toast(msg) {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: msg, showConfirmButton: false,
            timer: 2800, timerProgressBar: true,
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