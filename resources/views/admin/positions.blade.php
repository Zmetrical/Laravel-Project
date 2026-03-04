@extends('layouts.main')

@section('title', 'Position Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Organization</a></li>
        <li class="breadcrumb-item active">Positions</li>
    </ol>
@endsection

@push('styles')
<style>
    .stat-card { transition: box-shadow .15s; }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
    .badge-active   { background-color: var(--bs-secondary); }
    .badge-inactive { background-color: #6c757d; }
</style>
@endpush

@section('content')

{{-- Page Heading --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">Position Management</h4>
        <small class="text-muted">Manage job positions and their departments</small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openAddModal()">
        <i class="bi bi-plus-lg me-1"></i> Add Position
    </button>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-briefcase fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Positions</div>
                    <div class="fw-bold fs-4" id="stat-total">—</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-2 bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-check-circle fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Active Positions</div>
                    <div class="fw-bold fs-4" id="stat-active">—</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded p-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-diagram-3 fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Departments</div>
                    <div class="fw-bold fs-4" id="stat-depts">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="search-input" class="form-control"
                           placeholder="Search by position or department…">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select id="dept-filter" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select id="status-filter" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-2">
        <span class="fw-semibold small">
            Positions <span id="row-count" class="text-muted fw-normal"></span>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-2 text-muted small fw-semibold">Position</th>
                        <th class="py-2 text-muted small fw-semibold">Department</th>
                        <th class="py-2 text-muted small fw-semibold d-none d-md-table-cell">Description</th>
                        <th class="py-2 text-muted small fw-semibold text-center">Status</th>
                        <th class="py-2 text-muted small fw-semibold text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="positions-tbody">
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


{{-- ===================== ADD MODAL ===================== --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Add New Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Position Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="add-name" class="form-control form-control-sm"
                           placeholder="e.g. Production Operator">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Department <span class="text-danger">*</span>
                    </label>
                    <select id="add-department" class="form-select form-select-sm">
                        <option value="">Select Department</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea id="add-description" class="form-control form-control-sm" rows="3"
                              placeholder="Brief description of the position…"></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Status</label>
                    <select id="add-status" class="form-select form-select-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary" onclick="saveAdd()">
                    <i class="bi bi-check-lg me-1"></i>Add Position
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== EDIT MODAL ===================== --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="bi bi-pencil me-2 text-secondary"></i>Edit Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Position Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="edit-name" class="form-control form-control-sm">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Department <span class="text-danger">*</span>
                    </label>
                    <select id="edit-department" class="form-select form-select-sm">
                        <option value="">Select Department</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea id="edit-description" class="form-control form-control-sm" rows="3"></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Status</label>
                    <select id="edit-status" class="form-select form-select-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-secondary" onclick="saveEdit()">
                    <i class="bi bi-check-lg me-1"></i>Update Position
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ============================================================
   SAMPLE DATA — replace with fetch('/api/positions') as needed
   ============================================================ */
const DEPARTMENTS = [
    'Production',
    'Quality Assurance',
    'Warehouse & Logistics',
    'Human Resources',
    'Finance & Accounting',
    'Information Technology',
    'Administration',
    'Maintenance',
    'Sales & Marketing',
    'Research & Development',
];

let positions = [
    { id: 1,  name: 'Production Operator',      department: 'Production',             description: 'Operates machinery on the production floor.',       status: 'active'   },
    { id: 2,  name: 'Production Supervisor',    department: 'Production',             description: 'Oversees daily production activities.',             status: 'active'   },
    { id: 3,  name: 'Line Leader',              department: 'Production',             description: 'Leads a production line team.',                     status: 'active'   },
    { id: 4,  name: 'QA Inspector',             department: 'Quality Assurance',      description: 'Inspects finished goods for quality compliance.',   status: 'active'   },
    { id: 5,  name: 'QA Analyst',               department: 'Quality Assurance',      description: 'Analyzes quality data and prepares reports.',       status: 'active'   },
    { id: 6,  name: 'QA Supervisor',            department: 'Quality Assurance',      description: 'Manages the QA team and QA processes.',            status: 'active'   },
    { id: 7,  name: 'Warehouse Clerk',          department: 'Warehouse & Logistics',  description: 'Manages incoming and outgoing inventory.',          status: 'active'   },
    { id: 8,  name: 'Forklift Operator',        department: 'Warehouse & Logistics',  description: 'Operates forklifts for material handling.',         status: 'active'   },
    { id: 9,  name: 'Logistics Coordinator',    department: 'Warehouse & Logistics',  description: 'Coordinates shipments and delivery schedules.',     status: 'active'   },
    { id: 10, name: 'HR Officer',               department: 'Human Resources',        description: 'Handles recruitment, records and HR processes.',    status: 'active'   },
    { id: 11, name: 'HR Assistant',             department: 'Human Resources',        description: 'Provides administrative support to the HR team.',   status: 'active'   },
    { id: 12, name: 'HR Manager',               department: 'Human Resources',        description: 'Leads the Human Resources department.',             status: 'active'   },
    { id: 13, name: 'Accountant',               department: 'Finance & Accounting',   description: 'Manages financial records and reporting.',          status: 'active'   },
    { id: 14, name: 'Payroll Specialist',       department: 'Finance & Accounting',   description: 'Processes employee payroll and deductions.',        status: 'active'   },
    { id: 15, name: 'Finance Manager',          department: 'Finance & Accounting',   description: 'Oversees all financial operations.',               status: 'active'   },
    { id: 16, name: 'Bookkeeper',               department: 'Finance & Accounting',   description: 'Records day-to-day financial transactions.',       status: 'inactive' },
    { id: 17, name: 'IT Support Specialist',    department: 'Information Technology', description: 'Provides technical support to end users.',          status: 'active'   },
    { id: 18, name: 'Systems Administrator',    department: 'Information Technology', description: 'Maintains servers and network infrastructure.',     status: 'active'   },
    { id: 19, name: 'Software Developer',       department: 'Information Technology', description: 'Develops and maintains internal software systems.', status: 'active'   },
    { id: 20, name: 'Administrative Assistant', department: 'Administration',         description: 'Handles clerical and administrative tasks.',        status: 'active'   },
    { id: 21, name: 'Executive Secretary',      department: 'Administration',         description: 'Provides high-level administrative support.',       status: 'active'   },
    { id: 22, name: 'Receptionist',             department: 'Administration',         description: 'Manages front desk and visitor coordination.',      status: 'active'   },
    { id: 23, name: 'Maintenance Technician',   department: 'Maintenance',            description: 'Performs preventive and corrective maintenance.',   status: 'active'   },
    { id: 24, name: 'Electrician',              department: 'Maintenance',            description: 'Handles electrical installations and repairs.',     status: 'active'   },
    { id: 25, name: 'Sales Representative',     department: 'Sales & Marketing',      description: 'Handles client accounts and drives sales targets.',  status: 'active'   },
    { id: 26, name: 'Marketing Specialist',     department: 'Sales & Marketing',      description: 'Develops and executes marketing campaigns.',        status: 'active'   },
    { id: 27, name: 'R&D Technician',           department: 'Research & Development', description: 'Conducts product research and testing.',            status: 'active'   },
    { id: 28, name: 'R&D Supervisor',           department: 'Research & Development', description: 'Manages R&D projects and the team.',               status: 'inactive' },
];

let nextId = positions.length + 1;

/* ============================================================
   HELPERS
   ============================================================ */
function getUniqueDepts() {
    return [...new Set(positions.map(p => p.department))].sort();
}

function populateDeptDropdowns() {
    const opts = getUniqueDepts()
        .map(d => `<option value="${escHtml(d)}">${escHtml(d)}</option>`)
        .join('');

    document.getElementById('dept-filter').innerHTML     = '<option value="">All Departments</option>' + opts;
    document.getElementById('add-department').innerHTML  = '<option value="">Select Department</option>' + opts;
    document.getElementById('edit-department').innerHTML = '<option value="">Select Department</option>' + opts;
}

function updateStats() {
    document.getElementById('stat-total').textContent  = positions.length;
    document.getElementById('stat-active').textContent = positions.filter(p => p.status === 'active').length;
    document.getElementById('stat-depts').textContent  = getUniqueDepts().length;
}

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/* ============================================================
   RENDER TABLE
   ============================================================ */
function renderTable() {
    const search  = document.getElementById('search-input').value.toLowerCase();
    const deptVal = document.getElementById('dept-filter').value;
    const statVal = document.getElementById('status-filter').value;

    const filtered = positions.filter(p => {
        const matchSearch = !search ||
            p.name.toLowerCase().includes(search) ||
            p.department.toLowerCase().includes(search);
        const matchDept   = !deptVal || p.department === deptVal;
        const matchStatus = !statVal || p.status === statVal;
        return matchSearch && matchDept && matchStatus;
    });

    document.getElementById('row-count').textContent = `(${filtered.length})`;

    const tbody = document.getElementById('positions-tbody');

    if (!filtered.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-inbox me-1"></i> No positions found.
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(p => `
        <tr>
            <td class="ps-3 py-2">
                <span class="fw-semibold small">${escHtml(p.name)}</span>
            </td>
            <td class="py-2">
                <span class="small text-muted">${escHtml(p.department)}</span>
            </td>
            <td class="py-2 d-none d-md-table-cell">
                <span class="small text-muted">${escHtml(p.description) || '—'}</span>
            </td>
            <td class="py-2 text-center">
                <span class="badge rounded-pill ${p.status === 'active' ? 'badge-active' : 'badge-inactive'}">
                    ${p.status.charAt(0).toUpperCase() + p.status.slice(1)}
                </span>
            </td>
            <td class="py-2 text-center pe-3">
                <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                        onclick="openEditModal(${p.id})" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger py-0 px-2"
                        onclick="confirmDelete(${p.id})" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/* ============================================================
   ADD
   ============================================================ */
function openAddModal() {
    document.getElementById('add-name').value        = '';
    document.getElementById('add-department').value  = '';
    document.getElementById('add-description').value = '';
    document.getElementById('add-status').value      = 'active';
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

function saveAdd() {
    const name = document.getElementById('add-name').value.trim();
    const dept = document.getElementById('add-department').value;
    const desc = document.getElementById('add-description').value.trim();
    const stat = document.getElementById('add-status').value;

    if (!name || !dept) {
        Swal.fire({
            icon: 'warning',
            title: 'Required Fields',
            text: 'Position name and department are required.',
            confirmButtonColor: 'var(--bs-primary)',
        });
        return;
    }

    positions.push({ id: nextId++, name, department: dept, description: desc, status: stat });

    bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
    populateDeptDropdowns();
    updateStats();
    renderTable();

    Swal.fire({
        icon: 'success',
        title: 'Position Added',
        text: `"${name}" has been added.`,
        timer: 1800,
        showConfirmButton: false,
    });
}

/* ============================================================
   EDIT
   ============================================================ */
function openEditModal(id) {
    const pos = positions.find(p => p.id === id);
    if (!pos) return;

    // Re-populate dropdowns so the selected dept can be set
    populateDeptDropdowns();

    document.getElementById('edit-id').value          = pos.id;
    document.getElementById('edit-name').value        = pos.name;
    document.getElementById('edit-department').value  = pos.department;
    document.getElementById('edit-description').value = pos.description;
    document.getElementById('edit-status').value      = pos.status;

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function saveEdit() {
    const id   = parseInt(document.getElementById('edit-id').value);
    const name = document.getElementById('edit-name').value.trim();
    const dept = document.getElementById('edit-department').value;
    const desc = document.getElementById('edit-description').value.trim();
    const stat = document.getElementById('edit-status').value;

    if (!name || !dept) {
        Swal.fire({
            icon: 'warning',
            title: 'Required Fields',
            text: 'Position name and department are required.',
            confirmButtonColor: 'var(--bs-primary)',
        });
        return;
    }

    const idx = positions.findIndex(p => p.id === id);
    if (idx === -1) return;
    positions[idx] = { ...positions[idx], name, department: dept, description: desc, status: stat };

    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    populateDeptDropdowns();
    updateStats();
    renderTable();

    Swal.fire({
        icon: 'success',
        title: 'Position Updated',
        text: `"${name}" has been updated.`,
        timer: 1800,
        showConfirmButton: false,
    });
}

/* ============================================================
   DELETE
   ============================================================ */
function confirmDelete(id) {
    const pos = positions.find(p => p.id === id);
    if (!pos) return;

    Swal.fire({
        title: 'Delete Position?',
        text: `"${pos.name}" will be permanently removed.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
    }).then(result => {
        if (!result.isConfirmed) return;
        positions = positions.filter(p => p.id !== id);
        populateDeptDropdowns();
        updateStats();
        renderTable();
        Swal.fire({
            icon: 'success',
            title: 'Deleted',
            text: `"${pos.name}" has been removed.`,
            timer: 1600,
            showConfirmButton: false,
        });
    });
}

/* ============================================================
   INIT
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    populateDeptDropdowns();
    updateStats();
    renderTable();

    ['search-input', 'dept-filter', 'status-filter'].forEach(id => {
        document.getElementById(id).addEventListener('input', renderTable);
    });
});
</script>
@endpush