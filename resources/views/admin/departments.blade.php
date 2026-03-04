@extends('layouts.main')

@section('title', 'Department Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Organization</a></li>
        <li class="breadcrumb-item active">Departments</li>
    </ol>
@endsection

@push('styles')
<style>
    .stat-card { transition: box-shadow .15s; }
    .stat-card:hover { box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.12); }
    .table > tbody > tr > td { vertical-align: middle; }
    #dept-table tbody tr { cursor: default; }
    .badge-dept { font-size: .7rem; letter-spacing: .04em; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-semibold">Department Management</h4>
        <small class="text-muted">Organize and manage company departments</small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openAddModal()">
        <i class="bi bi-plus-lg me-1"></i> Add Department
    </button>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4" id="stat-cards">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3 flex-shrink-0">
                    <i class="bi bi-building fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Departments</div>
                    <div class="fs-4 fw-bold" id="stat-total">—</div>
                    <div class="text-muted" style="font-size:.75rem;" id="stat-active-sub">— active</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-secondary bg-opacity-10 p-3 flex-shrink-0">
                    <i class="bi bi-people fs-4 text-secondary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Employees</div>
                    <div class="fs-4 fw-bold" id="stat-employees">—</div>
                    <div class="text-muted" style="font-size:.75rem;">Across all departments</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3 flex-shrink-0">
                    <i class="bi bi-bar-chart fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Largest Department</div>
                    <div class="fw-bold" id="stat-largest-name" style="font-size:1rem;">—</div>
                    <div class="text-muted" style="font-size:.75rem;" id="stat-largest-count">—</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-secondary bg-opacity-10 p-3 flex-shrink-0">
                    <i class="bi bi-geo-alt fs-4 text-secondary"></i>
                </div>
                <div>
                    <div class="text-muted small">Branches</div>
                    <div class="fs-4 fw-bold" id="stat-branches">—</div>
                    <div class="text-muted" style="font-size:.75rem;" id="stat-branches-sub">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter + Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-muted small mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="filter-search" class="form-control border-start-0 ps-0"
                        placeholder="Name, code, or head…">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1">Branch</label>
                <select id="filter-branch" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Status</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dept-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:22%">Department</th>
                        <th style="width:10%">Code</th>
                        <th style="width:20%">Head(s)</th>
                        <th style="width:10%">Employees</th>
                        <th style="width:16%">Branch</th>
                        <th style="width:10%">Status</th>
                        <th style="width:12%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="dept-tbody">
                    <tr id="empty-row">
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
                            No departments found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white text-muted small d-flex justify-content-between align-items-center">
        <span id="table-count">Showing 0 departments</span>
        <span id="table-total-employees"></span>
    </div>
</div>

{{-- ===================== ADD / EDIT MODAL ===================== --}}
<div class="modal fade" id="deptModal" tabindex="-1" aria-labelledby="deptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deptModalLabel">Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-dept-id">

                <div class="row g-3">
                    {{-- Name + Code --}}
                    <div class="col-md-7">
                        <label class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" id="modal-name" class="form-control" placeholder="e.g., Production">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Department Code <span class="text-danger">*</span></label>
                        <input type="text" id="modal-code" class="form-control" placeholder="e.g., PROD"
                            oninput="this.value = this.value.toUpperCase()">
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea id="modal-description" class="form-control" rows="2"
                            placeholder="Brief description of this department…"></textarea>
                    </div>

                    {{-- Branch + Status --}}
                    <div class="col-md-8">
                        <label class="form-label">Branch</label>
                        <select id="modal-branch" class="form-select"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select id="modal-status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    {{-- Department Heads --}}
                    <div class="col-12">
                        <label class="form-label">Department Head(s)</label>
                        <div id="heads-container" class="d-flex flex-column gap-2"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                            onclick="addHeadRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Another Head
                        </button>
                        <div class="form-text">Only supervisors / managers within the selected department are shown.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="modal-save-btn" onclick="saveDepartment()">
                    Create Department
                </button>
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script>

let branches = [
    { id: 1, name: 'Meycauayan Main' },
    { id: 2, name: 'Obando Branch' },
    { id: 3, name: 'Marilao Branch' },
];

let employees = [
    { id: 'EMP001', name: 'Maria Santos',    position: 'Production Manager',   department: 'Production',     status: 'active' },
    { id: 'EMP002', name: 'Jose Reyes',      position: 'QA Supervisor',        department: 'Quality Assurance', status: 'active' },
    { id: 'EMP003', name: 'Ana Cruz',        position: 'HR Officer',           department: 'Human Resources', status: 'active' },
    { id: 'EMP004', name: 'Pedro Dela Cruz', position: 'Accounting Supervisor',department: 'Accounting',     status: 'active' },
    { id: 'EMP005', name: 'Linda Garcia',    position: 'IT Manager',           department: 'IT',             status: 'active' },
    { id: 'EMP006', name: 'Ramon Torres',    position: 'Logistics Supervisor', department: 'Logistics',      status: 'active' },
    { id: 'EMP007', name: 'Cynthia Lim',     position: 'Admin Head',           department: 'Administration', status: 'active' },
    { id: 'EMP008', name: 'Danilo Bautista', position: 'Maintenance Supervisor',department: 'Maintenance',  status: 'active' },
    { id: 'EMP009', name: 'Rosa Mendoza',    position: 'Sales Manager',        department: 'Sales',          status: 'active' },
    { id: 'EMP010', name: 'Carlo Navarro',   position: 'Security Team Leader', department: 'Security',       status: 'active' },
    // extra rank-and-file
    { id: 'EMP011', name: 'Mark Villanueva', position: 'Production Operator',  department: 'Production',     status: 'active' },
    { id: 'EMP012', name: 'Susan Aquino',    position: 'QA Inspector',         department: 'Quality Assurance', status: 'active' },
    { id: 'EMP013', name: 'Jerome Castillo', position: 'Accountant',           department: 'Accounting',     status: 'active' },
    { id: 'EMP014', name: 'Nora Espinosa',   position: 'HR Assistant',         department: 'Human Resources', status: 'active' },
    { id: 'EMP015', name: 'Rina Delos Reyes',position: 'IT Support',           department: 'IT',             status: 'active' },
];

let departments = [
    { id: 'DEPT001', name: 'Production',        code: 'PROD', description: 'Core manufacturing operations', headName: 'Maria Santos',    headId: 'EMP001', branch: 'Meycauayan Main', status: 'active',   employeeCount: 85 },
    { id: 'DEPT002', name: 'Quality Assurance', code: 'QA',   description: 'Quality control and testing',   headName: 'Jose Reyes',      headId: 'EMP002', branch: 'Meycauayan Main', status: 'active',   employeeCount: 32 },
    { id: 'DEPT003', name: 'Human Resources',   code: 'HR',   description: 'Talent and employee relations', headName: 'Ana Cruz',        headId: 'EMP003', branch: 'Meycauayan Main', status: 'active',   employeeCount: 18 },
    { id: 'DEPT004', name: 'Accounting',        code: 'ACCT', description: 'Finance and payroll',           headName: 'Pedro Dela Cruz', headId: 'EMP004', branch: 'Meycauayan Main', status: 'active',   employeeCount: 24 },
    { id: 'DEPT005', name: 'IT',                code: 'IT',   description: 'Systems and infrastructure',    headName: 'Linda Garcia',    headId: 'EMP005', branch: 'Meycauayan Main', status: 'active',   employeeCount: 12 },
    { id: 'DEPT006', name: 'Logistics',         code: 'LOG',  description: 'Warehouse and delivery',        headName: 'Ramon Torres',    headId: 'EMP006', branch: 'Obando Branch',   status: 'active',   employeeCount: 41 },
    { id: 'DEPT007', name: 'Administration',    code: 'ADMIN',description: 'General admin and support',     headName: 'Cynthia Lim',     headId: 'EMP007', branch: 'Meycauayan Main', status: 'active',   employeeCount: 9  },
    { id: 'DEPT008', name: 'Maintenance',       code: 'MAINT',description: 'Facilities and equipment',      headName: 'Danilo Bautista', headId: 'EMP008', branch: 'Marilao Branch',  status: 'active',   employeeCount: 22 },
    { id: 'DEPT009', name: 'Sales',             code: 'SALES',description: 'Client relations and sales',    headName: 'Rosa Mendoza',    headId: 'EMP009', branch: 'Obando Branch',   status: 'inactive', employeeCount: 15 },
    { id: 'DEPT010', name: 'Security',          code: 'SEC',  description: 'Site security and access',      headName: 'Carlo Navarro',   headId: 'EMP010', branch: 'Meycauayan Main', status: 'active',   employeeCount: 19 },
];

/* ================================================================
   LEADERSHIP KEYWORDS for head candidate filtering
   ================================================================ */
const LEADER_KEYWORDS = ['supervisor','manager','team leader','head','officer','lead'];

function isLeader(position) {
    if (!position) return false;
    const p = position.toLowerCase();
    return LEADER_KEYWORDS.some(k => p.includes(k));
}

/* ================================================================
   HELPERS
   ================================================================ */
function uniqueBranches() {
    return [...new Set(branches.map(b => b.name))];
}

function getLeadersForDept(deptName) {
    return employees.filter(e =>
        e.department === deptName &&
        e.status === 'active' &&
        isLeader(e.position)
    );
}

function computeStats() {
    const total    = departments.length;
    const active   = departments.filter(d => d.status === 'active').length;
    const totalEmp = departments.reduce((s, d) => s + d.employeeCount, 0);
    const largest  = departments.reduce((m, d) => d.employeeCount > (m?.employeeCount ?? -1) ? d : m, null);
    const branchCount = uniqueBranches().length;
    const mainBranch  = branches[0]?.name ?? '';

    document.getElementById('stat-total').textContent       = total;
    document.getElementById('stat-active-sub').textContent  = `${active} active`;
    document.getElementById('stat-employees').textContent   = totalEmp.toLocaleString();
    document.getElementById('stat-largest-name').textContent = largest?.name ?? '—';
    document.getElementById('stat-largest-count').textContent = largest ? `${largest.employeeCount} employees` : '';
    document.getElementById('stat-branches').textContent    = branchCount;
    document.getElementById('stat-branches-sub').textContent = mainBranch;
}

function populateBranchFilters() {
    const branchSelect  = document.getElementById('filter-branch');
    const modalBranch   = document.getElementById('modal-branch');
    branchSelect.innerHTML = '<option value="">All Branches</option>';
    modalBranch.innerHTML  = '';
    uniqueBranches().forEach(b => {
        branchSelect.innerHTML += `<option value="${b}">${b}</option>`;
        modalBranch.innerHTML  += `<option value="${b}">${b}</option>`;
    });
}

/* ================================================================
   TABLE RENDERING
   ================================================================ */
function renderTable() {
    const search = document.getElementById('filter-search').value.toLowerCase().trim();
    const branch = document.getElementById('filter-branch').value;
    const status = document.getElementById('filter-status').value;

    const filtered = departments.filter(d => {
        const matchSearch = !search ||
            d.name.toLowerCase().includes(search) ||
            d.code.toLowerCase().includes(search) ||
            d.headName.toLowerCase().includes(search);
        const matchBranch = !branch || d.branch === branch;
        const matchStatus = !status || d.status === status;
        return matchSearch && matchBranch && matchStatus;
    });

    const tbody = document.getElementById('dept-tbody');
    const emptyRow = document.getElementById('empty-row');

    // Remove existing data rows (not the empty-row template)
    tbody.querySelectorAll('tr.data-row').forEach(r => r.remove());

    if (filtered.length === 0) {
        emptyRow.style.display = '';
    } else {
        emptyRow.style.display = 'none';
        filtered.forEach(d => {
            const tr = document.createElement('tr');
            tr.className = 'data-row';
            tr.innerHTML = `
                <td class="ps-4">
                    <div class="fw-semibold">${escHtml(d.name)}</div>
                    <small class="text-muted">${escHtml(d.description)}</small>
                </td>
                <td>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 badge-dept fw-semibold">
                        ${escHtml(d.code)}
                    </span>
                </td>
                <td>
                    <div class="fw-medium">${escHtml(d.headName) || '<span class="text-muted">—</span>'}</div>
                    <small class="text-muted">${escHtml(d.headId)}</small>
                </td>
                <td>
                    <span class="fw-semibold">${d.employeeCount}</span>
                </td>
                <td class="text-muted">${escHtml(d.branch)}</td>
                <td>
                    ${d.status === 'active'
                        ? `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 badge-dept">Active</span>`
                        : `<span class="badge bg-light text-muted border badge-dept">Inactive</span>`
                    }
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-icon me-1" title="Edit"
                        onclick="openEditModal('${d.id}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary btn-icon" title="Delete"
                        onclick="deleteDepartment('${d.id}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Footer counts
    const shownEmp = filtered.reduce((s, d) => s + d.employeeCount, 0);
    document.getElementById('table-count').textContent =
        `Showing ${filtered.length} of ${departments.length} department${departments.length !== 1 ? 's' : ''}`;
    document.getElementById('table-total-employees').textContent =
        filtered.length < departments.length ? `${shownEmp} employees in view` : '';
}

function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ================================================================
   HEAD ROWS (modal)
   ================================================================ */
let headRows = []; // array of { value }

function renderHeadRows(deptName) {
    const container  = document.getElementById('heads-container');
    const candidates = getLeadersForDept(deptName);
    container.innerHTML = '';

    if (headRows.length === 0) headRows = [{ value: '' }];

    headRows.forEach((row, idx) => {
        const div = document.createElement('div');
        div.className = 'input-group input-group-sm';
        div.innerHTML = `
            <select class="form-select" onchange="headRows[${idx}].value = this.value">
                <option value="">— Select department head —</option>
                ${candidates.map(c =>
                    `<option value="${escHtml(c.id)}" ${row.value === c.id ? 'selected' : ''}>
                        ${escHtml(c.name)} — ${escHtml(c.position)}
                    </option>`
                ).join('')}
            </select>
            ${headRows.length > 1
                ? `<button class="btn btn-outline-secondary" type="button" onclick="removeHeadRow(${idx})">
                        <i class="bi bi-x"></i>
                   </button>`
                : ''}
        `;
        container.appendChild(div);
    });
}

function addHeadRow() {
    const deptName = getModalDeptName();
    headRows.push({ value: '' });
    renderHeadRows(deptName);
}

function removeHeadRow(idx) {
    headRows.splice(idx, 1);
    renderHeadRows(getModalDeptName());
}

function getModalDeptName() {
    return document.getElementById('modal-name').value.trim();
}

/* ================================================================
   MODAL: ADD
   ================================================================ */
function openAddModal() {
    document.getElementById('deptModalLabel').textContent = 'Add New Department';
    document.getElementById('modal-save-btn').textContent = 'Create Department';
    document.getElementById('modal-dept-id').value    = '';
    document.getElementById('modal-name').value       = '';
    document.getElementById('modal-code').value       = '';
    document.getElementById('modal-description').value = '';
    document.getElementById('modal-status').value     = 'active';
    document.getElementById('modal-branch').value     = branches[0]?.name ?? '';

    headRows = [{ value: '' }];
    renderHeadRows('');

    // Refresh heads when name changes
    document.getElementById('modal-name').oninput = () => renderHeadRows(getModalDeptName());

    new bootstrap.Modal(document.getElementById('deptModal')).show();
}

/* ================================================================
   MODAL: EDIT
   ================================================================ */
function openEditModal(id) {
    const dept = departments.find(d => d.id === id);
    if (!dept) return;

    document.getElementById('deptModalLabel').textContent   = 'Edit Department';
    document.getElementById('modal-save-btn').textContent   = 'Save Changes';
    document.getElementById('modal-dept-id').value          = dept.id;
    document.getElementById('modal-name').value             = dept.name;
    document.getElementById('modal-code').value             = dept.code;
    document.getElementById('modal-description').value      = dept.description;
    document.getElementById('modal-status').value           = dept.status;

    // Set branch (wait for DOM)
    const branchSel = document.getElementById('modal-branch');
    setTimeout(() => { branchSel.value = dept.branch; }, 0);

    // Build head rows from stored headId(s)
    const ids = dept.headId ? dept.headId.split(',').map(s => s.trim()).filter(Boolean) : [];
    headRows = ids.length ? ids.map(v => ({ value: v })) : [{ value: '' }];
    renderHeadRows(dept.name);

    document.getElementById('modal-name').oninput = () => renderHeadRows(getModalDeptName());

    new bootstrap.Modal(document.getElementById('deptModal')).show();
}

/* ================================================================
   SAVE (create or update)
   ================================================================ */
function saveDepartment() {
    const id          = document.getElementById('modal-dept-id').value;
    const name        = document.getElementById('modal-name').value.trim();
    const code        = document.getElementById('modal-code').value.trim();
    const description = document.getElementById('modal-description').value.trim();
    const branch      = document.getElementById('modal-branch').value;
    const status      = document.getElementById('modal-status').value;

    if (!name) { showToast('Department name is required.', 'error'); return; }
    if (!code) { showToast('Department code is required.', 'error'); return; }

    // Resolve head IDs → names
    const selectedIds   = headRows.map(r => r.value).filter(Boolean);
    const resolvedNames = selectedIds.map(eid => {
        const e = employees.find(emp => emp.id === eid);
        return e ? e.name : '';
    }).filter(Boolean);

    const headId   = selectedIds.join(', ');
    const headName = resolvedNames.join(', ');

    if (id) {
        // UPDATE
        const idx = departments.findIndex(d => d.id === id);
        if (idx !== -1) {
            departments[idx] = { ...departments[idx], name, code, description, branch, status, headId, headName };
            showToast('Department updated successfully.', 'success');
        }
    } else {
        // CREATE
        // Guard duplicate code
        if (departments.some(d => d.code === code)) {
            showToast(`Code "${code}" is already in use.`, 'error');
            return;
        }
        departments.push({
            id: 'DEPT' + Date.now(),
            name, code, description, branch, status,
            headId, headName, employeeCount: 0,
        });
        showToast('Department created successfully.', 'success');
    }

    bootstrap.Modal.getInstance(document.getElementById('deptModal')).hide();
    refresh();
}

/* ================================================================
   DELETE
   ================================================================ */
function deleteDepartment(id) {
    const dept = departments.find(d => d.id === id);
    if (!dept) return;

    if (dept.employeeCount > 0) {
        showToast(`Cannot delete "${dept.name}" — it has ${dept.employeeCount} employee(s). Reassign them first.`, 'error');
        return;
    }

    Swal.fire({
        title: 'Delete Department?',
        html: `<p class="mb-1"><strong>${escHtml(dept.name)}</strong> (${escHtml(dept.code)})</p>
               <p class="text-muted small">Branch: ${escHtml(dept.branch)}</p>
               <p class="text-danger small mb-0">This action cannot be undone.</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        confirmButtonColor: '#6c757d',
        cancelButtonColor: '#0d6efd',
        reverseButtons: true,
    }).then(result => {
        if (result.isConfirmed) {
            departments = departments.filter(d => d.id !== id);
            showToast(`"${dept.name}" has been deleted.`, 'success');
            refresh();
        }
    });
}

/* ================================================================
   FILTERS
   ================================================================ */
function resetFilters() {
    document.getElementById('filter-search').value = '';
    document.getElementById('filter-branch').value = '';
    document.getElementById('filter-status').value = '';
    renderTable();
}

/* ================================================================
   TOAST (uses SweetAlert2 mixin)
   ================================================================ */
const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false,
    timer: 3000, timerProgressBar: true,
});

function showToast(msg, type = 'success') {
    Toast.fire({ icon: type, title: msg });
}

/* ================================================================
   REFRESH
   ================================================================ */
function refresh() {
    computeStats();
    renderTable();
}

/* ================================================================
   BOOT
   ================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    populateBranchFilters();
    refresh();

    // Live filters
    ['filter-search', 'filter-branch', 'filter-status'].forEach(id => {
        document.getElementById(id).addEventListener('input', renderTable);
        document.getElementById(id).addEventListener('change', renderTable);
    });
});
</script>
@endpush