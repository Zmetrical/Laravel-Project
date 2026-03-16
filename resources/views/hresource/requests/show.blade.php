@extends('layouts.main')

@section('title', 'Pending Requests')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Pending Requests</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="mb-3">
    <h4 class="mb-1 fw-semibold">Pending Requests</h4>
    <p class="text-muted small mb-0">Review and validate employee requests — leave, overtime, and profile updates.</p>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    @foreach ([
        ['id' => 'statTotal',   'label' => 'Total Pending',    'sub' => 'Requires action'],
        ['id' => 'statLeave',   'label' => 'Leave Requests',   'sub' => 'Awaiting approval'],
        ['id' => 'statOT',      'label' => 'Overtime',         'sub' => 'Awaiting approval'],
        ['id' => 'statProfile', 'label' => 'Profile Updates',  'sub' => 'Awaiting review'],
    ] as $card)
    <div class="col-6 col-md-3">
        <div class="card card-body shadow-sm">
            <p class="text-muted small mb-1">{{ $card['label'] }}</p>
            <h3 class="mb-0 fw-bold" id="{{ $card['id'] }}">—</h3>
            <small class="text-muted">{{ $card['sub'] }}</small>
        </div>
    </div>
    @endforeach
</div>

{{-- Main Card with Tabs --}}
<div class="card shadow-sm">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs border-0 px-3 pt-2" id="reqTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="tab-leave-btn" data-bs-toggle="tab"
                   href="#tabLeave" role="tab">
                    Leave
                    <span class="badge bg-secondary ms-1" id="bLeave">—</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-ot-btn" data-bs-toggle="tab"
                   href="#tabOvertime" role="tab">
                    Overtime
                    <span class="badge bg-secondary ms-1" id="bOT">—</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-profile-btn" data-bs-toggle="tab"
                   href="#tabProfile" role="tab">
                    Profile Updates
                    <span class="badge bg-secondary ms-1" id="bProfile">—</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-history-btn" data-bs-toggle="tab"
                   href="#tabHistory" role="tab">
                    History
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="tab-content">

            {{-- ===== LEAVE ===== --}}
            <div class="tab-pane fade show active p-3" id="tabLeave" role="tabpanel">
                <div id="leaveContainer"></div>
            </div>

            {{-- ===== OVERTIME ===== --}}
            <div class="tab-pane fade p-3" id="tabOvertime" role="tabpanel">
                <div id="overtimeContainer"></div>
            </div>

            {{-- ===== PROFILE UPDATES ===== --}}
            <div class="tab-pane fade p-3" id="tabProfile" role="tabpanel">
                <div id="profileContainer"></div>
            </div>

            {{-- ===== HISTORY ===== --}}
            <div class="tab-pane fade p-3" id="tabHistory" role="tabpanel">
                <div class="d-flex gap-2 mb-3 flex-wrap" id="historyFilters">
                    <button class="btn btn-secondary btn-sm" data-hfilter="all"
                        onclick="loadHistory('all')">All</button>
                    <button class="btn btn-outline-secondary btn-sm" data-hfilter="leave"
                        onclick="loadHistory('leave')">Leave</button>
                    <button class="btn btn-outline-secondary btn-sm" data-hfilter="overtime"
                        onclick="loadHistory('overtime')">Overtime</button>
                    <button class="btn btn-outline-secondary btn-sm" data-hfilter="profile"
                        onclick="loadHistory('profile')">Profile</button>
                </div>
                <div id="historyContainer"></div>
            </div>

        </div>
    </div>
</div>

{{-- ===== MODAL: REJECT (shared) ===== --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded p-2 mb-3 bg-light small" id="rejectDetails"></div>
                <label class="form-label fw-medium">
                    Rejection Reason <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="rejectReason" rows="3"
                    placeholder="Provide a clear reason visible to the employee…"></textarea>
                <div class="invalid-feedback">Please enter a rejection reason.</div>
                <div class="form-text">This reason will be recorded and shown to the employee.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                    id="confirmRejectBtn">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: LEAVE DETAIL (attachments + reason) ===== --}}
<div class="modal fade" id="leaveDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Leave Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leaveDetailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                    id="detailRejectBtn">Reject</button>
                <button type="button" class="btn btn-secondary btn-sm"
                    id="detailApproveBtn">Approve</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIG
    // ─────────────────────────────────────────────────────────────────────────

    const CSRF = '{{ csrf_token() }}';
    const BASE = '{{ url("/hresource/requests") }}';

    // ─────────────────────────────────────────────────────────────────────────
    // STATE
    // ─────────────────────────────────────────────────────────────────────────

    let rejectState   = null;  // { type: string, id: number|string }
    let detailState   = null;  // current leave row being viewed in detail modal
    let tabsLoaded    = { leave: false, overtime: false, profile: false, history: false };

    // ─────────────────────────────────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        loadCounts();
        loadLeave();
        tabsLoaded.leave = true;

        // Lazy-load remaining tabs
        document.querySelectorAll('#reqTabs .nav-link').forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function () {
                const target = this.getAttribute('href');
                if (target === '#tabOvertime' && !tabsLoaded.overtime) {
                    loadOvertime();
                    tabsLoaded.overtime = true;
                }
                if (target === '#tabProfile' && !tabsLoaded.profile) {
                    loadProfile();
                    tabsLoaded.profile = true;
                }
                if (target === '#tabHistory' && !tabsLoaded.history) {
                    loadHistory('all');
                    tabsLoaded.history = true;
                }
            });
        });

        // Shared reject modal — confirm button
        document.getElementById('confirmRejectBtn').addEventListener('click', submitReject);
    });

    // ─────────────────────────────────────────────────────────────────────────
    // COUNTS
    // ─────────────────────────────────────────────────────────────────────────

    function loadCounts() {
        fetch(BASE + '/pending-counts')
            .then(function (r) { return r.json(); })
            .then(function (d) {
                setText('statTotal',   d.total);
                setText('statLeave',   d.leave);
                setText('statOT',      d.overtime);
                setText('statProfile', d.profile);
                setText('bLeave',      d.leave);
                setText('bOT',         d.overtime);
                setText('bProfile',    d.profile);
            })
            .catch(console.error);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOAD: LEAVE
    // ─────────────────────────────────────────────────────────────────────────

    function loadLeave() {
        const c = document.getElementById('leaveContainer');
        c.innerHTML = spinner();

        fetch(BASE + '/leave')
            .then(r => r.json())
            .then(function (data) {
                if (!data.length) { c.innerHTML = emptyState('No pending leave requests.'); return; }

                c.innerHTML =
                    '<div class="table-responsive">' +
                    '<table class="table table-bordered table-hover table-sm mb-0">' +
                    '<thead class="table-light"><tr>' +
                    '<th>Employee</th><th>Leave Type</th><th>Period</th>' +
                    '<th>Days</th><th>Submitted</th><th style="width:160px">Actions</th>' +
                    '</tr></thead><tbody>' +
                    data.map(function (r) {
                        return '<tr>' +
                            '<td>' + x(r.employee) + '<br><small class="text-muted">' + x(r.employee_id) + '</small></td>' +
                            '<td>' + x(r.leave_type) + '</td>' +
                            '<td class="text-nowrap">' + x(r.start_date) + ' – ' + x(r.end_date) + '</td>' +
                            '<td class="text-center">' + r.days + '</td>' +
                            '<td class="text-nowrap">' + x(r.submitted_at) + '</td>' +
                            '<td>' +
                                '<button class="btn btn-secondary btn-sm me-1" ' +
                                    'onclick="doApproveLeave(' + r.id + ', \'' + x(r.employee) + '\')">' +
                                    'Approve</button>' +
                                '<button class="btn btn-outline-secondary btn-sm" ' +
                                    'onclick="openReject(\'leave\',' + r.id + ',\'' + x(r.employee) + ' – ' + x(r.leave_type) + ' (' + r.days + ' day(s))\')">' +
                                    'Reject</button>' +
                            '</td></tr>';
                    }).join('') +
                    '</tbody></table></div>';
            })
            .catch(function () { document.getElementById('leaveContainer').innerHTML = errorState(); });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOAD: OVERTIME
    // ─────────────────────────────────────────────────────────────────────────

    function loadOvertime() {
        const c = document.getElementById('overtimeContainer');
        c.innerHTML = spinner();

        fetch(BASE + '/overtime')
            .then(r => r.json())
            .then(function (data) {
                if (!data.length) { c.innerHTML = emptyState('No pending overtime requests.'); return; }

                c.innerHTML =
                    '<div class="table-responsive">' +
                    '<table class="table table-bordered table-hover table-sm mb-0">' +
                    '<thead class="table-light"><tr>' +
                    '<th>Employee</th><th>Date</th><th>OT Type</th>' +
                    '<th>Hours</th><th>Est. Pay</th><th>Submitted</th>' +
                    '<th style="width:160px">Actions</th>' +
                    '</tr></thead><tbody>' +
                    data.map(function (r) {
                        return '<tr>' +
                            '<td>' + x(r.employee) + '<br><small class="text-muted">' + x(r.employee_id) + '</small></td>' +
                            '<td class="text-nowrap">' + x(r.date) + '</td>' +
                            '<td>' + x(r.ot_type) + '</td>' +
                            '<td class="text-center">' + r.hours + 'h</td>' +
                            '<td class="text-nowrap">₱' + x(r.estimated_pay) + '</td>' +
                            '<td class="text-nowrap">' + x(r.submitted_at) + '</td>' +
                            '<td>' +
                                '<button class="btn btn-secondary btn-sm me-1" ' +
                                    'onclick="doApproveOT(' + r.id + ', \'' + x(r.employee) + '\')">' +
                                    'Approve</button>' +
                                '<button class="btn btn-outline-secondary btn-sm" ' +
                                    'onclick="openReject(\'overtime\',' + r.id + ',\'' + x(r.employee) + ' – ' + x(r.ot_type) + ' (' + r.hours + 'h)\')">' +
                                    'Reject</button>' +
                            '</td></tr>';
                    }).join('') +
                    '</tbody></table></div>';
            })
            .catch(function () { document.getElementById('overtimeContainer').innerHTML = errorState(); });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOAD: PROFILE
    // ─────────────────────────────────────────────────────────────────────────

    function loadProfile() {
        const c = document.getElementById('profileContainer');
        c.innerHTML = spinner();

        fetch(BASE + '/profile')
            .then(r => r.json())
            .then(function (data) {
                if (!data.length) { c.innerHTML = emptyState('No pending profile update requests.'); return; }

                c.innerHTML =
                    '<div class="table-responsive">' +
                    '<table class="table table-bordered table-hover table-sm mb-0">' +
                    '<thead class="table-light"><tr>' +
                    '<th>Employee</th><th>Field</th><th>Current Value</th>' +
                    '<th>Requested Value</th><th>Submitted</th>' +
                    '<th style="width:160px">Actions</th>' +
                    '</tr></thead><tbody>' +
                    data.map(function (r) {
                        return '<tr>' +
                            '<td>' + x(r.employee) + '<br><small class="text-muted">' + x(r.employee_id) + '</small></td>' +
                            '<td><span class="badge bg-secondary">' + x(r.field) + '</span></td>' +
                            '<td class="text-muted small">' + x(r.old_value) + '</td>' +
                            '<td class="fw-medium small">' + x(r.new_value) + '</td>' +
                            '<td class="text-nowrap">' + x(r.submitted_at) + '</td>' +
                            '<td>' +
                                '<button class="btn btn-secondary btn-sm me-1" ' +
                                    'onclick="doApproveProfile(\'' + r.id + '\', \'' + x(r.employee) + '\', \'' + x(r.field) + '\')">' +
                                    'Approve</button>' +
                                '<button class="btn btn-outline-secondary btn-sm" ' +
                                    'onclick="openReject(\'profile\',\'' + r.id + '\',\'' + x(r.employee) + ' – ' + x(r.field) + '\')">' +
                                    'Reject</button>' +
                            '</td></tr>';
                    }).join('') +
                    '</tbody></table></div>';
            })
            .catch(function () { document.getElementById('profileContainer').innerHTML = errorState(); });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOAD: HISTORY
    // ─────────────────────────────────────────────────────────────────────────

    window.loadHistory = function (type) {
        // Update filter button styles
        document.querySelectorAll('#historyFilters button').forEach(function (btn) {
            const active = btn.getAttribute('data-hfilter') === type;
            btn.className = active
                ? 'btn btn-secondary btn-sm'
                : 'btn btn-outline-secondary btn-sm';
        });

        const c = document.getElementById('historyContainer');
        c.innerHTML = spinner();

        fetch(BASE + '/history?type=' + type)
            .then(r => r.json())
            .then(function (data) {
                if (!data.length) { c.innerHTML = emptyState('No history records found.'); return; }

                c.innerHTML =
                    '<div class="table-responsive">' +
                    '<table class="table table-bordered table-hover table-sm mb-0">' +
                    '<thead class="table-light"><tr>' +
                    '<th>Type</th><th>Employee</th><th>Detail</th>' +
                    '<th>Status</th><th>Reviewed By</th><th>Reviewed At</th>' +
                    '</tr></thead><tbody>' +
                    data.map(function (r) {
                        const statusBadge = r.status === 'approved' || r.status === 'paid'
                            ? '<span class="badge bg-secondary">Approved</span>'
                            : '<span class="badge bg-primary">Rejected</span>';

                        const rejNote = r.rejection_reason
                            ? '<br><small class="text-muted fst-italic">' + x(r.rejection_reason) + '</small>'
                            : '';

                        return '<tr>' +
                            '<td><span class="badge bg-secondary">' + x(r.category_label) + '</span></td>' +
                            '<td>' + x(r.employee) + '<br><small class="text-muted">' + x(r.employee_id) + '</small></td>' +
                            '<td class="small">' + x(r.detail) + '<br><span class="text-muted">' + x(r.period) + '</span>' + rejNote + '</td>' +
                            '<td>' + statusBadge + '</td>' +
                            '<td class="small">' + x(r.reviewed_by) + '</td>' +
                            '<td class="text-nowrap small">' + x(r.reviewed_at) + '</td>' +
                            '</tr>';
                    }).join('') +
                    '</tbody></table></div>';
            })
            .catch(function () { document.getElementById('historyContainer').innerHTML = errorState(); });
    };

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS — LEAVE
    // ─────────────────────────────────────────────────────────────────────────

    window.doApproveLeave = function (id, employee) {
        Swal.fire({
            title: 'Approve leave request?',
            text: employee + '\'s leave will be validated and balance deducted.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            confirmButtonColor: '#6c757d',
        }).then(function ({ isConfirmed }) {
            if (!isConfirmed) return;
            patchRequest('/leave/' + id + '/approve', {})
                .then(function (res) {
                    toast(res.message);
                    tabsLoaded.leave = false;
                    loadLeave();
                    loadCounts();
                })
                .catch(handleError);
        });
    };

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS — OVERTIME
    // ─────────────────────────────────────────────────────────────────────────

    window.doApproveOT = function (id, employee) {
        Swal.fire({
            title: 'Approve overtime?',
            text: employee + '\'s overtime will be flagged for payroll inclusion.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            confirmButtonColor: '#6c757d',
        }).then(function ({ isConfirmed }) {
            if (!isConfirmed) return;
            patchRequest('/overtime/' + id + '/approve', {})
                .then(function (res) {
                    toast(res.message);
                    tabsLoaded.overtime = false;
                    loadOvertime();
                    loadCounts();
                })
                .catch(handleError);
        });
    };

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS — PROFILE
    // ─────────────────────────────────────────────────────────────────────────

    window.doApproveProfile = function (id, employee, field) {
        Swal.fire({
            title: 'Approve profile update?',
            text: 'The ' + field + ' field for ' + employee + ' will be updated immediately.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Apply',
            confirmButtonColor: '#6c757d',
        }).then(function ({ isConfirmed }) {
            if (!isConfirmed) return;
            patchRequest('/profile/' + id + '/approve', {})
                .then(function (res) {
                    toast(res.message);
                    tabsLoaded.profile = false;
                    loadProfile();
                    loadCounts();
                })
                .catch(handleError);
        });
    };

    // ─────────────────────────────────────────────────────────────────────────
    // SHARED REJECT MODAL
    // ─────────────────────────────────────────────────────────────────────────

    window.openReject = function (type, id, detail) {
        rejectState = { type: type, id: id };
        document.getElementById('rejectDetails').textContent = detail;
        document.getElementById('rejectReason').value = '';
        document.getElementById('rejectReason').classList.remove('is-invalid');
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    };

    function submitReject() {
        const reason = document.getElementById('rejectReason').value.trim();
        if (!reason) {
            document.getElementById('rejectReason').classList.add('is-invalid');
            return;
        }

        const { type, id } = rejectState;

        patchRequest('/' + type + '/' + id + '/reject', { reason: reason })
            .then(function (res) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                toast(res.message);
                loadCounts();

                if (type === 'leave')    { tabsLoaded.leave    = false; loadLeave(); }
                if (type === 'overtime') { tabsLoaded.overtime = false; loadOvertime(); }
                if (type === 'profile')  { tabsLoaded.profile  = false; loadProfile(); }
            })
            .catch(handleError);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    function patchRequest(path, body) {
        return fetch(BASE + path, {
            method: 'PATCH',
            headers: {
                'Content-Type':  'application/json',
                'Accept':        'application/json',
                'X-CSRF-TOKEN':  CSRF,
            },
            body: JSON.stringify(body),
        }).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) return Promise.reject(data);
                return data;
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UI HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    function spinner() {
        return '<div class="text-center py-4 text-muted">' +
               '<span class="spinner-border spinner-border-sm me-2"></span>Loading…</div>';
    }

    function emptyState(msg) {
        return '<div class="text-center py-5 text-muted small">' + x(msg) + '</div>';
    }

    function errorState() {
        return '<div class="text-center py-4 text-muted small">Failed to load data. Please refresh the page.</div>';
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = (val !== undefined && val !== null) ? val : '—';
    }

    function toast(msg, icon) {
        Swal.fire({
            toast: true, position: 'top-end',
            icon: icon || 'success',
            title: msg,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    function handleError(err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err && err.message ? err.message : 'Something went wrong. Please try again.',
        });
    }

    /** XSS-safe string for innerHTML interpolation */
    function x(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})();
</script>
@endpush