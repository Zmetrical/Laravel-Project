@extends('layouts.main')

@section('title', 'Employees')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Employees</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
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
    <div class="col-md-3">
        <div class="card card-outline card-secondary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-person-check fs-4 text-secondary"></i>
                    <div>
                        <div class="text-muted small">Active</div>
                        <div class="fs-4 fw-semibold" id="stat-active">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-outline card-primary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-person-badge fs-4 text-primary"></i>
                    <div>
                        <div class="text-muted small">Regular</div>
                        <div class="fs-4 fw-semibold" id="stat-regular">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-outline card-secondary mb-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-person-clock fs-4 text-secondary"></i>
                    <div>
                        <div class="text-muted small">Probationary</div>
                        <div class="fs-4 fw-semibold" id="stat-probationary">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Card --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">Employee List</h3>
        <a href="{{ route('hresource.employees.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus me-1"></i> Add Employee
        </a>
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
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter-department">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter-status">
                    <option value="">All Status</option>
                    <option value="probationary">Probationary</option>
                    <option value="regular">Regular</option>
                    <option value="resigned">Resigned</option>
                    <option value="terminated">Terminated</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter-active">
                    <option value="">Active & Inactive</option>
                    <option value="1">Active Only</option>
                    <option value="0">Inactive Only</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter-branch">
                    <option value="">All Branches</option>
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
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Hire Date</th>
                        <th style="width:90px">Actions</th>
                    </tr>
                </thead>
                <tbody id="emp-tbody">
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

@endsection

@push('scripts')
<script>
const EmployeeList = (() => {

    const ROUTES = {
        list:   '{{ route('hresource.employees.list') }}',
        show:   '{{ url('hresource/employees') }}',    // + /{id}
        edit:   '{{ url('hresource/employees') }}',    // + /{id}/edit
    };

    const $ = id => document.getElementById(id);
    const tbody  = $('emp-tbody');
    const fSearch = $('filter-search');
    const fDept   = $('filter-department');
    const fStatus = $('filter-status');
    const fActive = $('filter-active');
    const fBranch = $('filter-branch');

    let all      = [];
    let filtered = [];

    /* ─── Load ──────────────────────────────────────────────── */
    async function load() {
        try {
            const res = await fetch(ROUTES.list, {
                headers: { 'Accept': 'application/json' },
            });
            all      = await res.json();
            filtered = [...all];
            populateDropdowns();
            updateStats();
            render();
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>${e.message}</td></tr>`;
        }
    }

    /* ─── Stats ─────────────────────────────────────────────── */
    function updateStats() {
        $('stat-total').textContent        = all.length;
        $('stat-active').textContent       = all.filter(e => e.isActive).length;
        $('stat-regular').textContent      = all.filter(e => e.employmentStatus === 'regular').length;
        $('stat-probationary').textContent = all.filter(e => e.employmentStatus === 'probationary').length;
    }

    /* ─── Dropdowns ─────────────────────────────────────────── */
    function populateDropdowns() {
        const depts    = [...new Set(all.map(e => e.department).filter(Boolean))].sort();
        const branches = [...new Set(all.map(e => e.branch).filter(Boolean))].sort();

        fDept.innerHTML   = '<option value="">All Departments</option>'
            + depts.map(d => `<option value="${d}">${d}</option>`).join('');
        fBranch.innerHTML = '<option value="">All Branches</option>'
            + branches.map(b => `<option value="${b}">${b}</option>`).join('');
    }

    /* ─── Render ─────────────────────────────────────────────── */
    function render() {
        if (!filtered.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-search me-2"></i>No employees found.</td></tr>`;
            $('table-footer').textContent = 'No results';
            return;
        }

        tbody.innerHTML = filtered.map(emp => {
            const initials  = emp.fullName.split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase();
            const statusCls = emp.employmentStatus === 'regular' ? 'bg-primary' : 'bg-secondary';

            return `<tr class="${emp.isActive ? '' : 'opacity-50'}">
                <td class="text-center">
                    <span class="d-inline-flex align-items-center justify-content-center
                          rounded-circle bg-secondary bg-opacity-10 text-secondary fw-semibold"
                          style="width:36px;height:36px;font-size:.75rem">
                        ${initials}
                    </span>
                </td>
                <td>
                    <div class="fw-semibold">${emp.fullName}</div>
                    <div class="text-muted small">${emp.id}${emp.email ? ' &middot; ' + emp.email : ''}</div>
                </td>
                <td>
                    <div>${emp.department ?? '—'}</div>
                    <div class="text-muted small">${emp.position ?? '—'}</div>
                </td>
                <td class="text-muted small">${emp.branch ?? '—'}</td>
                <td>
                    <span class="badge ${statusCls} bg-opacity-10 text-capitalize">
                        ${emp.employmentStatus ?? '—'}
                    </span>
                    ${!emp.isActive ? '<span class="badge bg-secondary bg-opacity-10 ms-1">Inactive</span>' : ''}
                </td>
                <td class="text-muted small">${emp.hireDate ?? '—'}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="${ROUTES.show}/${emp.id}"
                           class="btn btn-sm btn-outline-secondary py-1 px-2" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="${ROUTES.edit}/${emp.id}/edit"
                           class="btn btn-sm btn-primary py-1 px-2" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </td>
            </tr>`;
        }).join('');

        $('table-footer').textContent =
            `Showing ${filtered.length} of ${all.length} employee(s)`;
    }

    /* ─── Filter ─────────────────────────────────────────────── */
    function applyFilters() {
        const q      = fSearch.value.toLowerCase();
        const dept   = fDept.value;
        const status = fStatus.value;
        const active = fActive.value;
        const branch = fBranch.value;

        filtered = all.filter(e => {
            const matchQ      = !q      || [e.fullName, e.id, e.position, e.department]
                                    .some(v => (v ?? '').toLowerCase().includes(q));
            const matchDept   = !dept   || e.department       === dept;
            const matchStatus = !status || e.employmentStatus === status;
            const matchActive = active === '' || String(e.isActive ? 1 : 0) === active;
            const matchBranch = !branch || e.branch           === branch;
            return matchQ && matchDept && matchStatus && matchActive && matchBranch;
        });

        render();
    }

    /* ─── Bind ───────────────────────────────────────────────── */
    function bind() {
        let t;
        fSearch.addEventListener('input',  () => { clearTimeout(t); t = setTimeout(applyFilters, 300); });
        fDept.addEventListener('change',   applyFilters);
        fStatus.addEventListener('change', applyFilters);
        fActive.addEventListener('change', applyFilters);
        fBranch.addEventListener('change', applyFilters);
    }

    return {
        init() { bind(); load(); }
    };
})();

document.addEventListener('DOMContentLoaded', () => EmployeeList.init());
</script>
@endpush