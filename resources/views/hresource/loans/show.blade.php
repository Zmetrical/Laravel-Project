@extends('layouts.main')

@section('title', 'Loan Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Loan Management</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">Loan Management</h4>
        <small class="text-muted">Manage SSS and PAG-IBIG loan deductions</small>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="openAddModal()">Add Loan</button>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-3">
    @foreach ([
        ['id' => 'statActive',   'label' => 'Active Loans'],
        ['id' => 'statCompleted','label' => 'Completed'],
        ['id' => 'statSSS',      'label' => 'SSS Loans'],
        ['id' => 'statPagibig',  'label' => 'PAG-IBIG Loans'],
        ['id' => 'statBalance',  'label' => 'Total Balance'],
    ] as $c)
    <div class="col-6 col-md-4 col-lg">
        <div class="card card-body py-3">
            <div class="text-muted small mb-1">{{ $c['label'] }}</div>
            <div class="fs-5 fw-bold" id="{{ $c['id'] }}">—</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-md">
                <input type="text" id="searchInput" class="form-control form-control-sm"
                    placeholder="Search by name or loan ID…" oninput="debounceLoad()">
            </div>
            <div class="col-md-auto">
                <select id="typeFilter" class="form-select form-select-sm" onchange="loadList()">
                    <option value="all">All Types</option>
                    <option value="sss">SSS Loan</option>
                    <option value="pagibig">PAG-IBIG Loan</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Table Card --}}
<div class="card">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs border-0 px-3 pt-2" id="loanTabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" data-status="active"
                    onclick="setTab(this); return false;">
                    Active <span class="badge bg-secondary ms-1" id="tabActive">—</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-status="completed"
                    onclick="setTab(this); return false;">
                    Completed <span class="badge bg-secondary ms-1" id="tabCompleted">—</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-status="all"
                    onclick="setTab(this); return false;">
                    All <span class="badge bg-secondary ms-1" id="tabAll">—</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Loan ID</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th class="text-end">Loan Amount</th>
                        <th class="text-end">Monthly</th>
                        <th style="min-width:140px">Progress</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="loanTableBody">
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <span class="spinner-border spinner-border-sm me-2"></span>Loading…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===== MODAL: ADD ===== --}}
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Add New Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Employee Search --}}
                <div class="mb-3">
                    <label class="form-label fw-medium">Employee <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" id="addEmpSearch" class="form-control form-control-sm"
                            placeholder="Search by name, ID, or department…"
                            autocomplete="off" oninput="searchEmployees()">
                        <div id="addEmpDropdown"
                            class="list-group position-absolute w-100 shadow z-3 d-none"
                            style="max-height:200px;overflow-y:auto;top:100%;"></div>
                    </div>
                    <input type="hidden" id="addEmpId">
                    <div id="addEmpSelected" class="mt-2 d-none">
                        <span class="badge bg-secondary py-2 px-3" id="addEmpSelectedName"></span>
                    </div>
                </div>

                {{-- Loan Type --}}
                <div class="mb-3">
                    <label class="form-label fw-medium">Loan Type <span class="text-danger">*</span></label>
                    <select id="addLoanType" class="form-select form-select-sm">
                        <option value="">Select loan type</option>
                        <option value="sss">SSS Loan</option>
                        <option value="pagibig">PAG-IBIG Loan</option>
                    </select>
                </div>

                {{-- Amount + Amortization --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Total Loan Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">&#8369;</span>
                            <input type="number" id="addAmount" class="form-control"
                                placeholder="24000" min="1" step="100"
                                oninput="autoCalcAmortization()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">
                            Monthly Amortization <span class="text-danger">*</span>
                            <small class="text-muted fw-normal">(auto-calculated)</small>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">&#8369;</span>
                            <input type="number" id="addAmortization" class="form-control"
                                placeholder="1000.00" min="1" step="0.01">
                        </div>
                    </div>
                </div>

                {{-- Start Date + Term --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Start Date <span class="text-danger">*</span></label>
                        <input type="date" id="addStartDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Number of Payments <span class="text-danger">*</span></label>
                        <select id="addTerm" class="form-select form-select-sm" onchange="autoCalcAmortization()">
                            <option value="12">12 months (1 year)</option>
                            <option value="18">18 months (1.5 years)</option>
                            <option value="24" selected>24 months (2 years)</option>
                            <option value="36">36 months (3 years)</option>
                            <option value="48">48 months (4 years)</option>
                        </select>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-3">
                    <label class="form-label fw-medium">Notes</label>
                    <textarea id="addNotes" class="form-control form-control-sm" rows="2"
                        placeholder="Optional notes…"></textarea>
                </div>

                {{-- Summary Preview --}}
                <div id="addSummary" class="border rounded p-2 bg-light d-none small">
                    <strong>Loan Summary</strong>
                    <div class="row mt-1 g-1">
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Total:</span>
                            <span id="sumAmount" class="fw-medium ms-1"></span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Monthly:</span>
                            <span id="sumMonthly" class="fw-medium ms-1"></span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Term:</span>
                            <span id="sumTerm" class="fw-medium ms-1"></span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted">Total Pay:</span>
                            <span id="sumTotal" class="fw-medium ms-1"></span>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-secondary btn-sm" id="addSubmitBtn"
                    onclick="submitAddLoan()">Add Loan</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: EDIT ===== --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editLoanId">

                <div class="border rounded p-2 bg-light small mb-3">
                    <strong id="editEmpName"></strong> &mdash;
                    <span id="editLoanTypeName" class="text-muted"></span>
                    &nbsp;|&nbsp; Payments made:
                    <span id="editPaymentsMade" class="fw-medium"></span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Total Loan Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">&#8369;</span>
                            <input type="number" id="editAmount" class="form-control"
                                min="1" step="100" oninput="updateEditSummary()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Monthly Amortization <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">&#8369;</span>
                            <input type="number" id="editAmortization" class="form-control"
                                min="1" step="0.01" oninput="updateEditSummary()">
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Start Date <span class="text-danger">*</span></label>
                        <input type="date" id="editStartDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Number of Payments <span class="text-danger">*</span></label>
                        <select id="editTerm" class="form-select form-select-sm">
                            <option value="12">12 months</option>
                            <option value="18">18 months</option>
                            <option value="24">24 months</option>
                            <option value="36">36 months</option>
                            <option value="48">48 months</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Notes</label>
                    <textarea id="editNotes" class="form-control form-control-sm" rows="2"></textarea>
                </div>

                <div id="editSummary" class="border rounded p-2 bg-light d-none small">
                    <strong>Recalculated Balance</strong>
                    <div class="row mt-1 g-1">
                        <div class="col-4">
                            <span class="text-muted">Payments Made:</span>
                            <span id="editSumPaid" class="fw-medium ms-1"></span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted">Total Paid:</span>
                            <span id="editSumTotalPaid" class="fw-medium ms-1"></span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted">New Balance:</span>
                            <span id="editSumBalance" class="fw-medium ms-1"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-secondary btn-sm" onclick="submitEditLoan()">Update Loan</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: VIEW ===== --}}
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Loan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-4">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: DELETE ===== --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">This action cannot be undone.</p>
                <div class="border rounded p-2 small" id="deleteInfo"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-outline-secondary btn-sm" id="confirmDeleteBtn"
                    onclick="confirmDelete()">Delete</button>
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
    const BASE = '{{ url("/hresource/loans") }}';

    // ─── STATE ───────────────────────────────────────────────────────────────
    let currentStatus  = 'active';
    let deleteLoanId   = null;
    let editPayments   = 0;
    let empSearchTimer = null;

    // ─── INIT ────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadStats();
        loadList();

        // Close employee dropdown on outside click
        document.addEventListener('click', function (e) {
            const dd = document.getElementById('addEmpDropdown');
            if (dd && !dd.contains(e.target) && e.target.id !== 'addEmpSearch') {
                dd.classList.add('d-none');
            }
        });
    });

    // ─── STATS ───────────────────────────────────────────────────────────────
    function loadStats() {
        fetch(BASE + '/stats')
            .then(r => r.json())
            .then(function (d) {
                setText('statActive',    d.active);
                setText('statCompleted', d.completed);
                setText('statSSS',       d.sss);
                setText('statPagibig',   d.pagibig);
                setText('statBalance',   '\u20b1' + d.total_balance);
            })
            .catch(console.error);
    }

    // ─── LIST ─────────────────────────────────────────────────────────────────
    window.loadList = function () {
        const tbody  = document.getElementById('loanTableBody');
        const search = document.getElementById('searchInput').value;
        const type   = document.getElementById('typeFilter').value;

        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">' +
            '<span class="spinner-border spinner-border-sm me-2"></span>Loading\u2026</td></tr>';

        const url = BASE + '/list?status=' + currentStatus +
            '&type=' + type +
            '&search=' + encodeURIComponent(search);

        fetch(url)
            .then(r => r.json())
            .then(function (data) {
                // Update tab counts only on full load
                updateTabCounts(data);
                renderTable(data);
            })
            .catch(function () {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">Failed to load data.</td></tr>';
            });
    };

    function renderTable(data) {
        const tbody = document.getElementById('loanTableBody');

        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-5">No loans found.</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(function (l) {
            const typeBadge = '<span class="badge ' +
                (l.loan_type === 'sss' ? 'bg-primary' : 'bg-secondary') + '">' +
                x(l.loan_type_name) + '</span>';

            const statusBadge = l.status === 'active'
                ? '<span class="badge bg-secondary">Active</span>'
                : '<span class="badge bg-primary">Completed</span>';

            const barClass = l.loan_type === 'sss' ? 'bg-primary' : 'bg-secondary';
            const progress =
                '<div class="small text-center">' + l.payments_made + '/' + l.term_months + '</div>' +
                '<div class="progress" style="height:5px">' +
                    '<div class="progress-bar ' + barClass + '" style="width:' + l.progress_percent + '%"></div>' +
                '</div>';

            const balance = parseFloat(l.remaining_balance.replace(/,/g, ''));
            const balanceCell = '<span class="fw-bold' + (balance <= 0 ? ' text-muted' : '') + '">' +
                '\u20b1' + x(l.remaining_balance) + '</span>';

            let actions = '<button class="btn btn-sm btn-link p-1 text-secondary" title="View" ' +
                'onclick="openViewModal(' + l.id + ')"><i class="bi bi-eye"></i></button>';

            if (l.status === 'active') {
                actions += '<button class="btn btn-sm btn-link p-1 text-secondary" title="Edit" ' +
                    'onclick="openEditModal(' + l.id + ')"><i class="bi bi-pencil"></i></button>' +
                    '<button class="btn btn-sm btn-link p-1 text-secondary" title="Delete" ' +
                    'onclick="openDeleteModal(' + l.id + ', \'' + x(l.employee) + '\', \'' + x(l.loan_type_name) + '\', \'' + x(l.remaining_balance) + '\')"><i class="bi bi-trash"></i></button>';
            }

            return '<tr>' +
                '<td class="ps-3"><code class="small">' + x(l.id) + '</code></td>' +
                '<td><div class="fw-medium">' + x(l.employee) + '</div>' +
                    '<small class="text-muted">' + x(l.employee_id) + '</small></td>' +
                '<td>' + typeBadge + '</td>' +
                '<td class="text-end">\u20b1' + x(l.amount) + '</td>' +
                '<td class="text-end text-muted small">\u20b1' + x(l.monthly_amortization) + '</td>' +
                '<td>' + progress + '</td>' +
                '<td class="text-end">' + balanceCell + '</td>' +
                '<td class="text-center">' + statusBadge + '</td>' +
                '<td class="text-center pe-3">' + actions + '</td>' +
                '</tr>';
        }).join('');
    }

    function updateTabCounts(data) {
        // Counts per status within the current type/search filter
        const active    = data.filter(l => l.status === 'active').length;
        const completed = data.filter(l => l.status === 'completed').length;
        setText('tabActive',    active);
        setText('tabCompleted', completed);
        setText('tabAll',       data.length);
    }

    // ─── TABS ─────────────────────────────────────────────────────────────────
    window.setTab = function (el) {
        document.querySelectorAll('#loanTabs .nav-link').forEach(a => a.classList.remove('active'));
        el.classList.add('active');
        currentStatus = el.getAttribute('data-status');
        loadList();
    };

    // ─── DEBOUNCE SEARCH ─────────────────────────────────────────────────────
    window.debounceLoad = function () {
        clearTimeout(empSearchTimer);
        empSearchTimer = setTimeout(loadList, 350);
    };

    // ─── EMPLOYEE SEARCH (add modal) ─────────────────────────────────────────
    window.searchEmployees = function () {
        const input    = document.getElementById('addEmpSearch');
        const dropdown = document.getElementById('addEmpDropdown');
        const val      = input.value.trim();

        document.getElementById('addEmpId').value = '';
        document.getElementById('addEmpSelected').classList.add('d-none');

        if (!val) { dropdown.classList.add('d-none'); return; }

        fetch(BASE + '/employees?q=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(function (results) {
                if (!results.length) {
                    dropdown.innerHTML = '<div class="list-group-item text-muted small py-2">No results found.</div>';
                } else {
                    dropdown.innerHTML = results.map(function (e) {
                        return '<button type="button" class="list-group-item list-group-item-action py-2 small" ' +
                            'onclick="selectEmployee(\'' + x(e.id) + '\', \'' + x(e.full_name) + '\')">' +
                            '<strong>' + x(e.full_name) + '</strong> ' +
                            '<span class="text-muted">(' + x(e.id) + ')</span> ' +
                            '<span class="badge bg-secondary">' + x(e.department) + '</span>' +
                            '</button>';
                    }).join('');
                }
                dropdown.classList.remove('d-none');
            })
            .catch(console.error);
    };

    window.selectEmployee = function (id, name) {
        document.getElementById('addEmpSearch').value         = name;
        document.getElementById('addEmpId').value             = id;
        document.getElementById('addEmpDropdown').classList.add('d-none');
        document.getElementById('addEmpSelectedName').textContent = name + ' (' + id + ')';
        document.getElementById('addEmpSelected').classList.remove('d-none');
    };

    // ─── AUTO AMORTIZATION ───────────────────────────────────────────────────
    window.autoCalcAmortization = function () {
        const amount = parseFloat(document.getElementById('addAmount').value) || 0;
        const term   = parseInt(document.getElementById('addTerm').value)     || 0;
        const sumEl  = document.getElementById('addSummary');

        if (amount > 0 && term > 0) {
            const monthly = (amount / term).toFixed(2);
            document.getElementById('addAmortization').value = monthly;
            setText('sumAmount',  '\u20b1' + fmt(amount));
            setText('sumMonthly', '\u20b1' + fmt(monthly));
            setText('sumTerm',    term + ' months');
            setText('sumTotal',   '\u20b1' + fmt((monthly * term).toFixed(2)));
            sumEl.classList.remove('d-none');
        } else {
            sumEl.classList.add('d-none');
        }
    };

    // ─── ADD LOAN ─────────────────────────────────────────────────────────────
    window.openAddModal = function () {
        ['addEmpSearch', 'addAmount', 'addAmortization', 'addStartDate', 'addEmpId', 'addNotes']
            .forEach(id => { document.getElementById(id).value = ''; });
        document.getElementById('addLoanType').value = '';
        document.getElementById('addTerm').value     = '24';
        document.getElementById('addEmpDropdown').classList.add('d-none');
        document.getElementById('addEmpSelected').classList.add('d-none');
        document.getElementById('addSummary').classList.add('d-none');
        new bootstrap.Modal(document.getElementById('addModal')).show();
    };

    window.submitAddLoan = function () {
        const empId = document.getElementById('addEmpId').value;
        const type  = document.getElementById('addLoanType').value;
        const amt   = document.getElementById('addAmount').value;
        const amor  = document.getElementById('addAmortization').value;
        const date  = document.getElementById('addStartDate').value;
        const term  = document.getElementById('addTerm').value;
        const notes = document.getElementById('addNotes').value;

        if (!empId || !type || !amt || !amor || !date || !term) {
            Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please fill in all required fields.' });
            return;
        }

        const btn = document.getElementById('addSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'Saving…';

        fetch(BASE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                user_id:               empId,
                loan_type:             type,
                amount:                amt,
                monthly_amortization:  amor,
                term_months:           term,
                start_date:            date,
                notes:                 notes,
            }),
        })
        .then(handleJsonResponse)
        .then(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
            toast(res.message);
            loadStats();
            loadList();
        })
        .catch(handleError)
        .finally(function () {
            btn.disabled    = false;
            btn.textContent = 'Add Loan';
        });
    };

    // ─── EDIT LOAN ────────────────────────────────────────────────────────────
    window.openEditModal = function (id) {
        fetch(BASE + '/' + id, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(function (l) {
                editPayments = l.payments_made;
                document.getElementById('editLoanId').value             = l.id;
                document.getElementById('editEmpName').textContent      = l.employee;
                document.getElementById('editLoanTypeName').textContent = l.loan_type_name;
                document.getElementById('editPaymentsMade').textContent = l.payments_made + '/' + l.term_months;

                // Parse raw numbers from formatted strings for inputs
                document.getElementById('editAmount').value        = l.amount.replace(/,/g, '');
                document.getElementById('editAmortization').value  = l.monthly_amortization.replace(/,/g, '');
                document.getElementById('editStartDate').value     = rawDate(l.start_date);
                document.getElementById('editTerm').value          = l.term_months;
                document.getElementById('editNotes').value         = l.notes || '';
                document.getElementById('editSummary').classList.add('d-none');

                new bootstrap.Modal(document.getElementById('editModal')).show();
            })
            .catch(handleError);
    };

    window.updateEditSummary = function () {
        const amount = parseFloat(document.getElementById('editAmount').value)       || 0;
        const amor   = parseFloat(document.getElementById('editAmortization').value) || 0;
        const sumEl  = document.getElementById('editSummary');

        if (!amount || !amor) { sumEl.classList.add('d-none'); return; }

        const totalPaid = amor * editPayments;
        const balance   = Math.max(0, amount - totalPaid);
        setText('editSumPaid',      editPayments);
        setText('editSumTotalPaid', '\u20b1' + fmt(totalPaid));
        setText('editSumBalance',   '\u20b1' + fmt(balance));
        sumEl.classList.remove('d-none');
    };

    window.submitEditLoan = function () {
        const id   = document.getElementById('editLoanId').value;
        const amt  = document.getElementById('editAmount').value;
        const amor = document.getElementById('editAmortization').value;
        const date = document.getElementById('editStartDate').value;
        const term = document.getElementById('editTerm').value;
        const notes= document.getElementById('editNotes').value;

        if (!amt || !amor || !date || !term) {
            Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please fill in all required fields.' });
            return;
        }

        fetch(BASE + '/' + id, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                amount: amt, monthly_amortization: amor,
                term_months: term, start_date: date, notes: notes,
            }),
        })
        .then(handleJsonResponse)
        .then(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            toast(res.message);
            loadStats();
            loadList();
        })
        .catch(handleError);
    };

    // ─── VIEW LOAN ────────────────────────────────────────────────────────────
    window.openViewModal = function (id) {
        const body = document.getElementById('viewModalBody');
        body.innerHTML = '<div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></div>';
        new bootstrap.Modal(document.getElementById('viewModal')).show();

        fetch(BASE + '/' + id, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(function (l) {
                const barClass = l.loan_type === 'sss' ? 'bg-primary' : 'bg-secondary';
                const typeBadge = '<span class="badge ' + barClass + '">' + x(l.loan_type_name) + '</span>';
                const statusBadge = l.status === 'active'
                    ? '<span class="badge bg-secondary">Active</span>'
                    : '<span class="badge bg-primary">Completed</span>';

                const nextSection = l.status === 'active' && l.next_payment_date ? `
                    <div class="border rounded p-2 bg-light small mb-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <span class="text-muted">Next Payment Date</span><br>
                                <strong>${x(l.next_payment_date)}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted">Next Payment Amount</span><br>
                                <strong>&#8369;${x(l.monthly_amortization)}</strong>
                            </div>
                        </div>
                    </div>` : '';

                const recentPayments = l.recent_payments && l.recent_payments.length
                    ? '<div class="mt-3"><p class="text-muted small mb-1">Recent Payments</p>' +
                      '<table class="table table-sm table-bordered mb-0 small"><thead class="table-light"><tr>' +
                      '<th>Date</th><th class="text-end">Amount</th><th class="text-end">Balance After</th><th>Type</th>' +
                      '</tr></thead><tbody>' +
                      l.recent_payments.map(p =>
                          '<tr><td>' + x(p.date) + '</td>' +
                          '<td class="text-end">\u20b1' + x(p.amount) + '</td>' +
                          '<td class="text-end">\u20b1' + x(p.balance_after) + '</td>' +
                          '<td>' + x(p.type) + '</td></tr>'
                      ).join('') +
                      '</tbody></table></div>'
                    : '<p class="text-muted small mt-3 mb-0">No payment records yet.</p>';

                body.innerHTML = `
                    <div class="row g-2 text-center mb-3">
                        <div class="col-3"><div class="text-muted small">Loan ID</div><code class="small">${x(l.id)}</code></div>
                        <div class="col-3"><div class="text-muted small">Status</div>${statusBadge}</div>
                        <div class="col-3"><div class="text-muted small">Start Date</div><div class="small fw-medium">${x(l.start_date)}</div></div>
                        <div class="col-3"><div class="text-muted small">Created</div><div class="small">${x(l.created_at)}</div></div>
                    </div>
                    <hr class="my-2">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Employee</div>
                            <div class="fw-medium">${x(l.employee)}</div>
                            <small class="text-muted">${x(l.employee_id)}</small>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Loan Type</div>
                            ${typeBadge}
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Payment Progress — ${l.payments_made}/${l.term_months} (${l.progress_percent}%)</div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar ${barClass}" style="width:${l.progress_percent}%"></div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <div class="text-muted small">Remaining Balance</div>
                                <div class="fs-5 fw-bold">&#8369;${x(l.remaining_balance)}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <div class="text-muted small">Loan Amount</div>
                                <div class="fw-medium">&#8369;${x(l.amount)}</div>
                                <small class="text-muted">Monthly: &#8369;${x(l.monthly_amortization)}</small>
                            </div>
                        </div>
                    </div>
                    ${nextSection}
                    ${recentPayments}
                    <p class="text-muted small mt-3 mb-0">Encoded by: <strong>${x(l.encoded_by)}</strong></p>`;
            })
            .catch(handleError);
    };

    // ─── DELETE LOAN ─────────────────────────────────────────────────────────
    window.openDeleteModal = function (id, employee, loanTypeName, balance) {
        deleteLoanId = id;
        document.getElementById('deleteInfo').innerHTML =
            '<div><span class="text-muted">Employee:</span> <strong>' + x(employee) + '</strong></div>' +
            '<div><span class="text-muted">Type:</span> ' + x(loanTypeName) + '</div>' +
            '<div><span class="text-muted">Balance:</span> <strong>\u20b1' + x(balance) + '</strong></div>';
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    };

    window.confirmDelete = function () {
        const btn = document.getElementById('confirmDeleteBtn');
        btn.disabled = true;

        fetch(BASE + '/' + deleteLoanId, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        })
        .then(handleJsonResponse)
        .then(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            toast(res.message);
            loadStats();
            loadList();
        })
        .catch(handleError)
        .finally(function () {
            btn.disabled = false;
        });
    };

    // ─── UTILITIES ────────────────────────────────────────────────────────────
    function handleJsonResponse(r) {
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

    function toast(msg) {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: msg, showConfirmButton: false,
            timer: 2500, timerProgressBar: true,
        });
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val !== null && val !== undefined ? val : '—';
    }

    function fmt(n) {
        return Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /**
     * Convert "Mar 01, 2025" back to "2025-03-01" for date inputs.
     * Falls back to empty string if the format is not parseable.
     */
    function rawDate(str) {
        if (!str) return '';
        const d = new Date(str);
        if (isNaN(d)) return '';
        return d.toISOString().split('T')[0];
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