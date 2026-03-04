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

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Branch Management</h4>
        <small class="text-muted">Manage company branches and locations</small>
    </div>
    <button class="btn btn-primary btn-sm" id="btnAddBranch" data-bs-toggle="modal" data-bs-target="#branchModal">
        <i class="bi bi-plus-lg me-1"></i> Add Branch
    </button>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4" id="statsRow"></div>

{{-- Branch Cards --}}
<div class="row g-3" id="branchGrid"></div>

{{-- Empty State --}}
<div id="emptyState" class="text-center py-5 d-none">
    <i class="bi bi-building fs-1 text-muted d-block mb-2"></i>
    <p class="text-muted mb-0">No branches found. Add one to get started.</p>
</div>


{{-- ============================================================
     BRANCH MODAL
     ============================================================ --}}
<div class="modal fade" id="branchModal" tabindex="-1" aria-labelledby="branchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="branchModalLabel">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="branchForm" novalidate>
                    <input type="hidden" id="branchId">

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldName" placeholder="e.g., Meycauayan Main Office">
                            <div class="invalid-feedback">Branch name is required.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Branch Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldCode" placeholder="e.g., MYC-MAIN" maxlength="20">
                            <div class="invalid-feedback">Branch code is required.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldAddress" placeholder="Street address, Barangay">
                            <div class="invalid-feedback">Address is required.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City / Municipality <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldCity" placeholder="e.g., Meycauayan, Bulacan">
                            <div class="invalid-feedback">City is required.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldContact" placeholder="+63 XX XXX XXXX">
                            <div class="invalid-feedback">Contact number is required.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="fieldEmail" placeholder="branch@company.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Branch Manager <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldManager" placeholder="Manager's full name">
                            <div class="invalid-feedback">Branch manager is required.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="fieldStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldIsMain">
                                <label class="form-check-label" for="fieldIsMain">
                                    Set as main branch
                                </label>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveBranch">
                    <i class="bi bi-check-lg me-1"></i> <span id="btnSaveLabel">Save Branch</span>
                </button>
            </div>

        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================================================================
    // Sample Data (replace with Axios/fetch calls to your API routes)
    // =========================================================================
    let branches = [
        {
            id: 1,
            name:          'Meycauayan Main Office',
            code:          'MYC-MAIN',
            address:       '123 McArthur Highway, Brgy. Calvario',
            city:          'Meycauayan, Bulacan',
            contactNumber: '+63 44 123 4567',
            email:         'main@fastservices.ph',
            managerName:   'Maria Santos',
            employeeCount: 48,
            departmentCount: 6,
            isMain:  true,
            status:  'active',
            createdDate: '2021-01-15',
        },
        {
            id: 2,
            name:          'Valenzuela Branch',
            code:          'VLZ-01',
            address:       '45 Maysan Road, Brgy. Karuhatan',
            city:          'Valenzuela City, Metro Manila',
            contactNumber: '+63 2 8123 4567',
            email:         'valenzuela@fastservices.ph',
            managerName:   'Jose Reyes',
            employeeCount: 22,
            departmentCount: 3,
            isMain:  false,
            status:  'active',
            createdDate: '2022-03-20',
        },
        {
            id: 3,
            name:          'Caloocan Office',
            code:          'CLC-01',
            address:       '88 A. Mabini St., Brgy. Deparo',
            city:          'Caloocan City, Metro Manila',
            contactNumber: '+63 2 8987 6543',
            email:         'caloocan@fastservices.ph',
            managerName:   'Ana Dela Cruz',
            employeeCount: 15,
            departmentCount: 2,
            isMain:  false,
            status:  'inactive',
            createdDate: '2023-06-01',
        },
        {
            id: 4,
            name:          'Malabon Branch',
            code:          'MLB-01',
            address:       '12 Governor Pascual Ave., Brgy. Longos',
            city:          'Malabon City, Metro Manila',
            contactNumber: '+63 2 8765 4321',
            email:         '',
            managerName:   'Roberto Lim',
            employeeCount: 10,
            departmentCount: 2,
            isMain:  false,
            status:  'active',
            createdDate: '2024-01-10',
        },
    ];

    let nextId = 5;

    // =========================================================================
    // Render: Stats
    // =========================================================================
    function renderStats() {
        const total      = branches.length;
        const active     = branches.filter(b => b.status === 'active').length;
        const totalEmp   = branches.reduce((s, b) => s + b.employeeCount, 0);
        const totalDept  = branches.reduce((s, b) => s + b.departmentCount, 0);
        const cities     = new Set(branches.map(b => b.city)).size;

        const items = [
            { label: 'Total Branches',  value: total,    sub: `${active} active`,        icon: 'bi-building'   },
            { label: 'Total Employees', value: totalEmp, sub: 'Across all branches',     icon: 'bi-people'     },
            { label: 'Departments',     value: totalDept,sub: 'Total departments',        icon: 'bi-diagram-3'  },
            { label: 'Locations',       value: cities,   sub: 'Cities covered',           icon: 'bi-geo-alt'    },
        ];

        document.getElementById('statsRow').innerHTML = items.map(item => `
            <div class="col-6 col-xl-3">
                <div class="card card-body shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-primary bg-primary bg-opacity-10 rounded p-2 flex-shrink-0">
                            <i class="bi ${item.icon} fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">${item.label}</div>
                            <div class="fw-semibold fs-5 lh-sm">${item.value}</div>
                            <div class="text-muted" style="font-size:.72rem">${item.sub}</div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // =========================================================================
    // Render: Branch Cards
    // =========================================================================
    function renderBranches() {
        const grid  = document.getElementById('branchGrid');
        const empty = document.getElementById('emptyState');

        if (!branches.length) {
            grid.innerHTML = '';
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');

        grid.innerHTML = branches.map(b => `
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">

                        {{-- Card Header --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-2 flex-grow-1 min-width-0">
                                <div class="text-primary bg-primary bg-opacity-10 rounded p-2 flex-shrink-0">
                                    <i class="bi bi-building fs-5"></i>
                                </div>
                                <div class="min-width-0">
                                    <div class="fw-semibold lh-sm text-truncate">
                                        ${escHtml(b.name)}
                                        ${b.isMain ? '<span class="badge bg-secondary ms-1" style="font-size:.65rem">MAIN</span>' : ''}
                                    </div>
                                    <small class="text-muted">${escHtml(b.code)}</small>
                                </div>
                            </div>
                            <span class="badge ms-2 flex-shrink-0 ${b.status === 'active'
                                ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25'
                                : 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25'}">
                                ${b.status === 'active' ? 'Active' : 'Inactive'}
                            </span>
                        </div>

                        {{-- Details --}}
                        <ul class="list-unstyled small flex-grow-1 mb-3">
                            <li class="d-flex gap-2 mb-2">
                                <i class="bi bi-geo-alt text-muted mt-1 flex-shrink-0"></i>
                                <span>
                                    ${escHtml(b.address)}<br>
                                    <span class="text-muted">${escHtml(b.city)}</span>
                                </span>
                            </li>
                            <li class="d-flex gap-2 mb-2">
                                <i class="bi bi-telephone text-muted flex-shrink-0"></i>
                                <span>${escHtml(b.contactNumber)}</span>
                            </li>
                            ${b.email ? `
                            <li class="d-flex gap-2 mb-2">
                                <i class="bi bi-envelope text-muted flex-shrink-0"></i>
                                <span class="text-truncate">${escHtml(b.email)}</span>
                            </li>` : ''}
                            <li class="d-flex gap-2">
                                <i class="bi bi-person text-muted flex-shrink-0"></i>
                                <span><span class="text-muted">Manager:</span> ${escHtml(b.managerName)}</span>
                            </li>
                        </ul>

                        {{-- Counts --}}
                        <div class="row g-2 text-center mb-3">
                            <div class="col-6">
                                <div class="border rounded py-2">
                                    <div class="fw-semibold">${b.employeeCount}</div>
                                    <div class="text-muted" style="font-size:.72rem">Employees</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded py-2">
                                    <div class="fw-semibold">${b.departmentCount}</div>
                                    <div class="text-muted" style="font-size:.72rem">Departments</div>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm flex-grow-1"
                                    onclick="openEdit(${b.id})">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <button class="btn btn-outline-secondary btn-sm"
                                    onclick="confirmDelete(${b.id})"
                                    title="Delete branch">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        `).join('');
    }

    // =========================================================================
    // Helpers
    // =========================================================================
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    const REQUIRED_FIELDS = ['fieldName','fieldCode','fieldAddress','fieldCity','fieldContact','fieldManager'];

    function validateForm() {
        let valid = true;
        REQUIRED_FIELDS.forEach(id => {
            const el = document.getElementById(id);
            if (!el.value.trim()) { el.classList.add('is-invalid');    valid = false; }
            else                  { el.classList.remove('is-invalid'); }
        });
        return valid;
    }

    function resetForm() {
        document.getElementById('branchId').value      = '';
        document.getElementById('fieldName').value     = '';
        document.getElementById('fieldCode').value     = '';
        document.getElementById('fieldAddress').value  = '';
        document.getElementById('fieldCity').value     = '';
        document.getElementById('fieldContact').value  = '';
        document.getElementById('fieldEmail').value    = '';
        document.getElementById('fieldManager').value  = '';
        document.getElementById('fieldStatus').value   = 'active';
        document.getElementById('fieldIsMain').checked = false;
        REQUIRED_FIELDS.forEach(id => document.getElementById(id).classList.remove('is-invalid'));
    }

    function readForm() {
        return {
            name:          document.getElementById('fieldName').value.trim(),
            code:          document.getElementById('fieldCode').value.trim().toUpperCase(),
            address:       document.getElementById('fieldAddress').value.trim(),
            city:          document.getElementById('fieldCity').value.trim(),
            contactNumber: document.getElementById('fieldContact').value.trim(),
            email:         document.getElementById('fieldEmail').value.trim(),
            managerName:   document.getElementById('fieldManager').value.trim(),
            status:        document.getElementById('fieldStatus').value,
            isMain:        document.getElementById('fieldIsMain').checked,
        };
    }

    function refresh() {
        renderStats();
        renderBranches();
    }

    // =========================================================================
    // Event: Add Branch — reset form before modal opens
    // =========================================================================
    document.getElementById('btnAddBranch').addEventListener('click', () => {
        resetForm();
        document.getElementById('branchModalLabel').textContent = 'Add New Branch';
        document.getElementById('btnSaveLabel').textContent     = 'Save Branch';
    });

    // =========================================================================
    // Event: Save (Create / Update)
    // =========================================================================
    document.getElementById('btnSaveBranch').addEventListener('click', () => {
        if (!validateForm()) return;

        const id      = document.getElementById('branchId').value;
        const payload = readForm();

        if (id) {
            const idx = branches.findIndex(b => b.id == id);
            if (idx !== -1) branches[idx] = { ...branches[idx], ...payload };
        } else {
            branches.push({
                id:             nextId++,
                employeeCount:  0,
                departmentCount:0,
                createdDate:    new Date().toISOString().slice(0, 10),
                ...payload,
            });
        }

        bootstrap.Modal.getInstance(document.getElementById('branchModal')).hide();
        refresh();

        Swal.fire({
            icon:              'success',
            title:             id ? 'Branch Updated' : 'Branch Created',
            text:              `"${payload.name}" has been ${id ? 'updated' : 'created'} successfully.`,
            confirmButtonColor:'#0d6efd',
            timer:             2200,
            timerProgressBar:  true,
        });
    });

    // =========================================================================
    // Edit
    // =========================================================================
    window.openEdit = function (id) {
        const b = branches.find(b => b.id === id);
        if (!b) return;

        resetForm();
        document.getElementById('branchModalLabel').textContent = 'Edit Branch';
        document.getElementById('btnSaveLabel').textContent     = 'Update Branch';
        document.getElementById('branchId').value      = b.id;
        document.getElementById('fieldName').value     = b.name;
        document.getElementById('fieldCode').value     = b.code;
        document.getElementById('fieldAddress').value  = b.address;
        document.getElementById('fieldCity').value     = b.city;
        document.getElementById('fieldContact').value  = b.contactNumber;
        document.getElementById('fieldEmail').value    = b.email;
        document.getElementById('fieldManager').value  = b.managerName;
        document.getElementById('fieldStatus').value   = b.status;
        document.getElementById('fieldIsMain').checked = b.isMain;

        new bootstrap.Modal(document.getElementById('branchModal')).show();
    };

    // =========================================================================
    // Delete
    // =========================================================================
    window.confirmDelete = function (id) {
        const b = branches.find(b => b.id === id);
        if (!b) return;

        Swal.fire({
            title:              'Delete Branch?',
            html:               `You are about to delete <strong>${escHtml(b.name)}</strong>. This cannot be undone.`,
            icon:               'warning',
            showCancelButton:   true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor:  '#0d6efd',
            confirmButtonText:  'Yes, Delete',
            cancelButtonText:   'Cancel',
        }).then(result => {
            if (!result.isConfirmed) return;
            branches = branches.filter(b => b.id !== id);
            refresh();
            Swal.fire({
                icon: 'success', title: 'Deleted',
                text: `${b.name} has been removed.`,
                timer: 1800, showConfirmButton: false,
            });
        });
    };

    // =========================================================================
    // Auto-uppercase branch code
    // =========================================================================
    document.getElementById('fieldCode').addEventListener('input', function () {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });

    // =========================================================================
    // Init
    // =========================================================================
    refresh();
});
</script>
@endpush