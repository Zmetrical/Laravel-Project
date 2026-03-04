@extends('layouts.main')

@section('title', 'Pending Requests – HR Validation')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Pending Requests</li>
    </ol>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-3">
    <h4 class="mb-1 fw-semibold">Pending Requests</h4>
    <p class="text-muted mb-0 small">Review and approve employee requests. HR is the final approver.</p>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Total Pending</span>
                    <span class="badge bg-primary rounded-pill" id="badgeTotalPending">0</span>
                </div>
                <h3 class="mb-0 fw-bold text-primary" id="countTotalPending">0</h3>
                <p class="text-muted small mb-0 mt-1">Requires validation</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Profile Updates</span>
                    <span class="badge bg-secondary rounded-pill" id="badgeProfilePending">0</span>
                </div>
                <h3 class="mb-0 fw-bold text-secondary" id="countProfilePending">0</h3>
                <p class="text-muted small mb-0 mt-1">Update requests</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Leave Requests</span>
                    <span class="badge bg-primary rounded-pill" id="badgeLeavePending">0</span>
                </div>
                <h3 class="mb-0 fw-bold text-primary" id="countLeavePending">0</h3>
                <p class="text-muted small mb-0 mt-1">Awaiting approval</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Overtime</span>
                    <span class="badge bg-secondary rounded-pill" id="badgeOTPending">0</span>
                </div>
                <h3 class="mb-0 fw-bold text-secondary" id="countOTPending">0</h3>
                <p class="text-muted small mb-0 mt-1">Awaiting approval</p>
            </div>
        </div>
    </div>
</div>

{{-- Main Card with Tabs --}}
<div class="card shadow-sm border-0">
    <div class="card-header p-0 border-bottom bg-transparent">
        <ul class="nav nav-tabs border-0 px-3 pt-2">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tabProfile">
                    Profile Updates
                    <span class="badge bg-secondary ms-1" id="tabBadgeProfile">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tabLeave">
                    Leave Validation
                    <span class="badge bg-primary ms-1" id="tabBadgeLeave">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tabOvertime">
                    Overtime Validation
                    <span class="badge bg-secondary ms-1" id="tabBadgeOT">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tabHistory">History</a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content">

            {{-- ===================== PROFILE UPDATES ===================== --}}
            <div class="tab-pane fade show active" id="tabProfile">
                <div id="profileList"></div>
                <div id="profileEmpty" class="text-center py-5 text-muted d-none">
                    <i class="bi bi-person-x display-6 d-block mb-2 opacity-50"></i>
                    No pending profile updates
                </div>
            </div>

            {{-- ===================== LEAVE VALIDATION ===================== --}}
            <div class="tab-pane fade" id="tabLeave">
                <div id="leaveList"></div>
                <div id="leaveEmpty" class="text-center py-5 text-muted d-none">
                    <i class="bi bi-calendar-x display-6 d-block mb-2 opacity-50"></i>
                    No pending leave validations
                </div>
            </div>

            {{-- ===================== OVERTIME VALIDATION ===================== --}}
            <div class="tab-pane fade" id="tabOvertime">
                <div id="otList"></div>
                <div id="otEmpty" class="text-center py-5 text-muted d-none">
                    <i class="bi bi-clock-history display-6 d-block mb-2 opacity-50"></i>
                    No pending overtime validations
                </div>
            </div>

            {{-- ===================== HISTORY ===================== --}}
            <div class="tab-pane fade" id="tabHistory">

                {{-- History Summary --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border shadow-none">
                            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Profile Updates</p>
                                    <h4 class="mb-0 fw-bold text-secondary" id="histCountProfile">0</h4>
                                </div>
                                <i class="bi bi-person-check fs-2 text-secondary opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border shadow-none">
                            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Leave Requests</p>
                                    <h4 class="mb-0 fw-bold text-primary" id="histCountLeave">0</h4>
                                </div>
                                <i class="bi bi-calendar-check fs-2 text-primary opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border shadow-none">
                            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Overtime</p>
                                    <h4 class="mb-0 fw-bold text-secondary" id="histCountOT">0</h4>
                                </div>
                                <i class="bi bi-clock fs-2 text-secondary opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- History Filter Buttons --}}
                <div class="d-flex gap-2 mb-3 flex-wrap" id="historyFilterBtns">
                    <button class="btn btn-primary btn-sm" onclick="setHistoryFilter('all')" data-filter="all">All</button>
                    <button class="btn btn-secondary btn-sm" onclick="setHistoryFilter('profile')" data-filter="profile">Profile</button>
                    <button class="btn btn-secondary btn-sm" onclick="setHistoryFilter('leave')" data-filter="leave">Leave</button>
                    <button class="btn btn-secondary btn-sm" onclick="setHistoryFilter('overtime')" data-filter="overtime">Overtime</button>
                </div>

                <div id="historyList"></div>
                <div id="historyEmpty" class="text-center py-5 text-muted d-none">
                    <i class="bi bi-archive display-6 d-block mb-2 opacity-50"></i>
                    No history records found
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ===================== MODAL: REJECT LEAVE ===================== --}}
<div class="modal fade" id="rejectLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 mb-3 border rounded bg-body-secondary small" id="rejectLeaveDetails"></div>
                <label class="form-label fw-medium">Rejection Reason <span class="text-danger">*</span></label>
                <textarea class="form-control" id="rejectionReasonInput" rows="4"
                    placeholder="Provide a clear reason for rejecting this leave request..."></textarea>
                <div class="form-text">This reason will be visible to the employee.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmRejectLeave()">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL: OVERRIDE LEAVE ===================== --}}
<div class="modal fade" id="overrideLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daily Leave Limit Exceeded — Override Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 mb-3 border rounded bg-body-secondary small" id="overrideLeaveDetails"></div>
                <div class="alert border-start border-4 border-primary bg-body-secondary mb-3">
                    <strong>Notice:</strong> Approving this request may result in insufficient manpower.
                    Ensure adequate staffing coverage before proceeding.
                </div>
                <label class="form-label fw-medium">Override Justification <span class="text-danger">*</span></label>
                <textarea class="form-control" id="overrideReasonInput" rows="4"
                    placeholder="Explain why you are approving this leave despite the daily limit being reached..."></textarea>
                <div class="form-text">This justification will be recorded in the audit trail.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-secondary" onclick="confirmOverrideReject()">Reject Request</button>
                <button type="button" class="btn btn-primary" onclick="confirmOverrideApprove()">Approve with Override</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL: ATTACHMENT PREVIEW ===================== --}}
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentModalTitle">Attachment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="attachmentModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>

let pendingProfileUpdates = [
    {
        id: 'PU-001', employeeId: 'EMP-001', employeeName: 'Maria Santos',
        field: 'email', oldValue: 'msantos@old.com', newValue: 'mariasantos@gmail.com',
        reason: 'Old email no longer active. Requesting update for payslip notifications.',
        status: 'pending', submittedDate: '2025-03-01',
    },
    {
        id: 'PU-002', employeeId: 'EMP-003', employeeName: 'Jose dela Cruz',
        field: 'civilStatus', oldValue: 'Single', newValue: 'Married',
        reason: 'Got married February 14, 2025. Requesting civil status and name update.',
        status: 'pending', submittedDate: '2025-03-02',
        newMiddleName: 'Reyes', newLastName: 'dela Cruz',
    },
    {
        id: 'PU-003', employeeId: 'EMP-007', employeeName: 'Ana Reyes',
        field: 'emergencyContact', oldValue: 'Pedro Reyes – 09171234567',
        newValue: 'Carmen Reyes – 09281234567',
        reason: 'Previous contact relocated abroad. Updated to sister.',
        status: 'pending', submittedDate: '2025-03-03',
    },
];

let pendingLeaveRequests = [
    {
        id: 'LR-001', employeeId: 'EMP-002', employeeName: 'Roberto Lim',
        type: 'Vacation Leave', startDate: '2025-03-10', endDate: '2025-03-12', days: 3,
        reason: 'Annual family vacation to Batangas. Pre-arranged with immediate supervisor.',
        status: 'pending', submittedDate: '2025-03-01', attachments: [],
    },
    {
        id: 'LR-002', employeeId: 'EMP-005', employeeName: 'Cecilia Torres',
        type: 'Sick Leave', startDate: '2025-03-05', endDate: '2025-03-06', days: 2,
        reason: 'Diagnosed with flu. Medical certificate attached.',
        status: 'pending', submittedDate: '2025-03-04',
        attachments: [
            { id: 'ATT-001', fileName: 'medical_certificate.pdf', fileSize: 245000 },
            { id: 'ATT-002', fileName: 'prescription.jpg',        fileSize: 98000  },
        ],
    },
    {
        id: 'LR-003', employeeId: 'EMP-008', employeeName: 'Rafael Ocampo',
        type: 'Emergency Leave', startDate: '2025-03-06', endDate: '2025-03-07', days: 2,
        reason: 'Family emergency — father admitted to hospital.',
        status: 'pending', submittedDate: '2025-03-05', attachments: [],
    },
];

let pendingOTRequests = [
    {
        id: 'OT-001', employeeId: 'EMP-004', employeeName: 'Diana Aguilar',
        type: 'Regular Overtime', date: '2025-03-04',
        startTime: '17:00', endTime: '20:00', hours: 3,
        rate: 125.25, estimatedPay: 375.75,
        reason: 'Month-end closing of financial reports.',
        status: 'pending', submittedDate: '2025-03-04',
    },
    {
        id: 'OT-002', employeeId: 'EMP-006', employeeName: 'Victor Mendoza',
        type: 'Rest Day Overtime', date: '2025-03-08',
        startTime: '08:00', endTime: '17:00', hours: 8,
        rate: 156.50, estimatedPay: 1252.00,
        reason: 'System migration scheduled on weekend to avoid service disruption.',
        status: 'pending', submittedDate: '2025-03-05',
    },
];

let historyRequests = [
    {
        id: 'LR-H01', category: 'leave',
        employeeId: 'EMP-010', employeeName: 'Lourdes Fernandez',
        type: 'Vacation Leave', startDate: '2025-02-10', endDate: '2025-02-12', days: 3,
        reason: 'Rest and recreation.',
        status: 'approved', reviewedBy: 'HR Manager', reviewDate: '2025-02-08',
    },
    {
        id: 'OT-H01', category: 'overtime',
        employeeId: 'EMP-002', employeeName: 'Roberto Lim',
        type: 'Regular Overtime', date: '2025-02-15', hours: 2, estimatedPay: 250.50,
        reason: 'Urgent client deliverable.',
        status: 'approved', reviewedBy: 'HR Manager', reviewDate: '2025-02-15',
    },
    {
        id: 'PU-H01', category: 'profile',
        employeeId: 'EMP-009', employeeName: 'Glenda Cruz',
        field: 'phoneNumber', oldValue: '09171111111', newValue: '09289999999',
        reason: 'Number changed.',
        status: 'approved', reviewedBy: 'HR Manager', reviewDate: '2025-02-20',
    },
    {
        id: 'LR-H02', category: 'leave',
        employeeId: 'EMP-011', employeeName: 'Emilio Ramos',
        type: 'Sick Leave', startDate: '2025-02-18', endDate: '2025-02-18', days: 1,
        reason: 'Headache and fever.',
        status: 'rejected', reviewedBy: 'HR Manager', reviewDate: '2025-02-17',
        rejectionReason: 'No medical certificate provided.',
    },
    {
        id: 'OT-H02', category: 'overtime',
        employeeId: 'EMP-005', employeeName: 'Cecilia Torres',
        type: 'Rest Day Overtime', date: '2025-01-25', hours: 4, estimatedPay: 626.00,
        reason: 'Quarterly inventory.',
        status: 'rejected', reviewedBy: 'HR Manager', reviewDate: '2025-01-24',
        rejectionReason: 'Manpower limit exceeded for the day.',
    },
];

// =============================================================================
// STATE
// =============================================================================
let activeHistoryFilter = 'all';
let pendingRejectId     = null;
let pendingOverrideId   = null;

// =============================================================================
// INIT
// =============================================================================
document.addEventListener('DOMContentLoaded', refreshAll);

function refreshAll() {
    updateSummaryCounts();
    renderProfileList();
    renderLeaveList();
    renderOTList();
    renderHistoryList();
}

// =============================================================================
// COUNTS
// =============================================================================
function updateSummaryCounts() {
    const total = pendingProfileUpdates.length + pendingLeaveRequests.length + pendingOTRequests.length;
    setText('countTotalPending',   total);
    setText('badgeTotalPending',   total);
    setText('countProfilePending', pendingProfileUpdates.length);
    setText('badgeProfilePending', pendingProfileUpdates.length);
    setText('countLeavePending',   pendingLeaveRequests.length);
    setText('badgeLeavePending',   pendingLeaveRequests.length);
    setText('countOTPending',      pendingOTRequests.length);
    setText('badgeOTPending',      pendingOTRequests.length);
    setText('tabBadgeProfile',     pendingProfileUpdates.length);
    setText('tabBadgeLeave',       pendingLeaveRequests.length);
    setText('tabBadgeOT',          pendingOTRequests.length);
    setText('histCountProfile', historyRequests.filter(r => r.category === 'profile').length);
    setText('histCountLeave',   historyRequests.filter(r => r.category === 'leave').length);
    setText('histCountOT',      historyRequests.filter(r => r.category === 'overtime').length);
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

// =============================================================================
// RENDER: PROFILE UPDATES
// =============================================================================
function renderProfileList() {
    const list  = document.getElementById('profileList');
    const empty = document.getElementById('profileEmpty');
    if (!pendingProfileUpdates.length) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');
    list.innerHTML = pendingProfileUpdates.map(req => `
        <div class="card border mb-3 shadow-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1 fw-semibold">${x(req.employeeName)}</h6>
                        <span class="text-muted small">${x(req.employeeId)}</span>
                    </div>
                    <span class="badge bg-secondary">${x(req.field)}</span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2 border rounded bg-body-secondary">
                            <p class="text-muted small mb-1">Current Value</p>
                            <p class="mb-0 small">${x(req.oldValue || '—')}</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-body-secondary">
                            <p class="text-muted small mb-1">New Value</p>
                            <p class="mb-0 small fw-medium">${x(req.newValue)}</p>
                        </div>
                    </div>
                </div>

                ${req.field === 'civilStatus' && req.newValue === 'Married' && req.newMiddleName ? `
                <div class="alert border-start border-4 border-primary bg-body-secondary small mb-3">
                    <strong>Automatic Name Change (Philippine Law)</strong>
                    <div class="row mt-2 g-2">
                        <div class="col-6">
                            <span class="text-muted">New Middle Name</span><br>
                            <strong>${x(req.newMiddleName)}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">New Last Name</span><br>
                            <strong>${x(req.newLastName)}</strong>
                        </div>
                    </div>
                </div>` : ''}

                <div class="p-2 border rounded bg-body-secondary mb-3">
                    <p class="text-muted small mb-1">Reason</p>
                    <p class="mb-0 small">${x(req.reason)}</p>
                </div>

                <p class="text-muted small mb-3">Submitted: ${x(req.submittedDate)}</p>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm flex-fill"
                        onclick="approveProfile('${req.id}')">
                        <i class="bi bi-check-circle me-1"></i> Approve Update
                    </button>
                    <button class="btn btn-secondary btn-sm flex-fill"
                        onclick="rejectProfile('${req.id}')">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// =============================================================================
// RENDER: LEAVE REQUESTS
// =============================================================================
function renderLeaveList() {
    const list  = document.getElementById('leaveList');
    const empty = document.getElementById('leaveEmpty');
    if (!pendingLeaveRequests.length) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');
    list.innerHTML = pendingLeaveRequests.map(req => {
        const attachHtml = req.attachments && req.attachments.length ? `
            <div class="p-2 border rounded bg-body-secondary mb-3">
                <p class="text-muted small mb-2">
                    <i class="bi bi-paperclip me-1"></i>Supporting Documents (${req.attachments.length})
                </p>
                <div class="d-flex flex-wrap gap-2">
                    ${req.attachments.map((f, idx) => `
                        <button class="btn btn-secondary btn-sm"
                            onclick="previewAttachment('${req.id}', ${idx})">
                            <i class="bi bi-file-earmark me-1"></i>${x(f.fileName)}
                            <span class="text-muted ms-1">${(f.fileSize/1024).toFixed(0)} KB</span>
                        </button>`).join('')}
                </div>
            </div>` : '';

        return `
        <div class="card border mb-3 shadow-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1 fw-semibold">${x(req.employeeName)}</h6>
                        <span class="text-muted small">${x(req.employeeId)}</span>
                    </div>
                    <span class="badge bg-primary">${x(req.type)}</span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Start Date</p>
                        <p class="mb-0 small fw-medium">${x(req.startDate)}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">End Date</p>
                        <p class="mb-0 small fw-medium">${x(req.endDate)}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Duration</p>
                        <p class="mb-0 small fw-semibold text-primary">${req.days} day(s)</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Submitted</p>
                        <p class="mb-0 small">${x(req.submittedDate)}</p>
                    </div>
                </div>

                <div class="p-2 border rounded bg-body-secondary mb-3">
                    <p class="text-muted small mb-1">Reason</p>
                    <p class="mb-0 small">${x(req.reason)}</p>
                </div>

                ${attachHtml}

                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm flex-fill"
                        onclick="approveLeave('${req.id}')">
                        <i class="bi bi-check-circle me-1"></i> Validate &amp; Apply to System
                    </button>
                    <button class="btn btn-secondary btn-sm flex-fill"
                        onclick="openRejectLeaveModal('${req.id}')">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');
}

// =============================================================================
// RENDER: OVERTIME REQUESTS
// =============================================================================
function renderOTList() {
    const list  = document.getElementById('otList');
    const empty = document.getElementById('otEmpty');
    if (!pendingOTRequests.length) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');
    list.innerHTML = pendingOTRequests.map(req => `
        <div class="card border mb-3 shadow-none">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1 fw-semibold">${x(req.employeeName)}</h6>
                        <span class="text-muted small">${x(req.employeeId)}</span>
                    </div>
                    <span class="badge bg-secondary">${x(req.type)}</span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Date</p>
                        <p class="mb-0 small fw-medium">${x(req.date)}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Time</p>
                        <p class="mb-0 small">${x(req.startTime)} – ${x(req.endTime)}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Hours</p>
                        <p class="mb-0 small fw-semibold">${req.hours} hrs</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Estimated Pay</p>
                        <p class="mb-0 small fw-semibold text-primary">&#8369;${req.estimatedPay.toFixed(2)}</p>
                    </div>
                </div>

                <div class="p-2 border rounded bg-body-secondary mb-3">
                    <p class="text-muted small mb-1">Reason</p>
                    <p class="mb-0 small">${x(req.reason)}</p>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm flex-fill"
                        onclick="approveOT('${req.id}')">
                        <i class="bi bi-check-circle me-1"></i> Validate &amp; Apply to System
                    </button>
                    <button class="btn btn-secondary btn-sm flex-fill"
                        onclick="rejectOT('${req.id}')">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// =============================================================================
// RENDER: HISTORY
// =============================================================================
function renderHistoryList() {
    const list  = document.getElementById('historyList');
    const empty = document.getElementById('historyEmpty');

    const filtered = activeHistoryFilter === 'all'
        ? historyRequests
        : historyRequests.filter(r => r.category === activeHistoryFilter);

    const sorted = [...filtered].sort((a, b) =>
        new Date(b.reviewDate || b.submittedDate) - new Date(a.reviewDate || a.submittedDate)
    );

    if (!sorted.length) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');

    const catLabel = { profile: 'Profile Update', leave: 'Leave Request', overtime: 'Overtime' };

    list.innerHTML = sorted.map(req => {
        const isApproved = req.status === 'approved' || req.status === 'validated';
        const statusBadge = isApproved
            ? `<span class="badge bg-primary">Approved</span>`
            : `<span class="badge bg-secondary">Rejected</span>`;

        let detail = '';
        if (req.category === 'leave') {
            detail = `
                <p class="small mb-1">
                    <span class="text-muted">Period:</span> ${x(req.startDate)} – ${x(req.endDate)}
                    <span class="text-muted ms-1">(${req.days} day(s))</span>
                </p>
                <p class="small text-muted mb-0">${x(req.reason)}</p>
                ${req.rejectionReason ? `<p class="small text-muted mb-0 mt-1 fst-italic">Rejection reason: ${x(req.rejectionReason)}</p>` : ''}`;
        } else if (req.category === 'overtime') {
            detail = `
                <p class="small mb-1">
                    <span class="text-muted">Date:</span> ${x(req.date)} &nbsp;
                    <span class="text-muted">Hours:</span> ${req.hours}h &nbsp;
                    <span class="text-muted">Pay:</span> &#8369;${req.estimatedPay.toFixed(2)}
                </p>
                <p class="small text-muted mb-0">${x(req.reason)}</p>
                ${req.rejectionReason ? `<p class="small text-muted mb-0 mt-1 fst-italic">Rejection reason: ${x(req.rejectionReason)}</p>` : ''}`;
        } else {
            detail = `
                <p class="small mb-1"><span class="text-muted">Field:</span> <strong>${x(req.field)}</strong></p>
                <p class="small mb-0">
                    <span class="text-muted">${x(req.oldValue || '—')}</span>
                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                    <strong>${x(req.newValue)}</strong>
                </p>`;
        }

        return `
        <div class="card border mb-2 shadow-none">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-fill">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge bg-secondary">${catLabel[req.category] || req.category}</span>
                            ${statusBadge}
                            <span class="text-muted small">${x(req.reviewDate || '')}</span>
                        </div>
                        <h6 class="mb-2 fw-semibold">
                            ${x(req.employeeName)}
                            <span class="text-muted fw-normal small ms-1">${x(req.employeeId)}</span>
                        </h6>
                        ${detail}
                        <p class="text-muted small mb-0 mt-2">
                            <i class="bi bi-shield-check me-1"></i>
                            Reviewed by: <strong>${x(req.reviewedBy || 'HR')}</strong>
                        </p>
                    </div>
                    <button class="btn btn-secondary btn-sm ms-3"
                        onclick="deleteHistory('${req.id}')" title="Delete record">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');
}

// =============================================================================
// HISTORY FILTER
// =============================================================================
function setHistoryFilter(filter) {
    activeHistoryFilter = filter;
    document.querySelectorAll('#historyFilterBtns button').forEach(btn => {
        const isActive = btn.getAttribute('data-filter') === filter;
        btn.classList.toggle('btn-primary',   isActive);
        btn.classList.toggle('btn-secondary', !isActive);
    });
    renderHistoryList();
}

// =============================================================================
// PROFILE ACTIONS
// =============================================================================
function approveProfile(id) {
    const req = pendingProfileUpdates.find(r => r.id === id);
    if (!req) return;
    Swal.fire({
        title: 'Approve Profile Update?',
        text: `Apply "${req.field}" change for ${req.employeeName}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        confirmButtonColor: '#0d6efd',
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;
        moveToHistory(req, 'profile', 'approved');
        pendingProfileUpdates = pendingProfileUpdates.filter(r => r.id !== id);
        refreshAll();
        toast('Profile update approved and applied.', 'success');
    });
}

function rejectProfile(id) {
    const req = pendingProfileUpdates.find(r => r.id === id);
    if (!req) return;
    Swal.fire({
        title: 'Reject Profile Update?',
        text: `Reject ${req.employeeName}'s ${req.field} update?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Yes, Reject', confirmButtonColor: '#6c757d',
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;
        moveToHistory(req, 'profile', 'rejected');
        pendingProfileUpdates = pendingProfileUpdates.filter(r => r.id !== id);
        refreshAll();
        toast('Profile update rejected.', 'info');
    });
}

// =============================================================================
// LEAVE ACTIONS
// =============================================================================
function approveLeave(id) {
    const req = pendingLeaveRequests.find(r => r.id === id);
    if (!req) return;
    // Simulate daily limit exceeded: vacation >= 3 days triggers override modal
    if (req.days >= 3 && req.type.toLowerCase().includes('vacation')) {
        openOverrideLeaveModal(id);
        return;
    }
    moveToHistory(req, 'leave', 'approved');
    pendingLeaveRequests = pendingLeaveRequests.filter(r => r.id !== id);
    refreshAll();
    toast(`${req.employeeName}'s ${req.type} approved (${req.days} day(s)).`, 'success');
}

function openRejectLeaveModal(id) {
    pendingRejectId = id;
    const req = pendingLeaveRequests.find(r => r.id === id);
    if (!req) return;
    document.getElementById('rejectLeaveDetails').innerHTML = `
        <p class="mb-1"><strong>Employee:</strong> ${x(req.employeeName)}</p>
        <p class="mb-1"><strong>Leave Type:</strong> ${x(req.type)}</p>
        <p class="mb-0"><strong>Duration:</strong> ${req.days} day(s) (${x(req.startDate)} – ${x(req.endDate)})</p>`;
    document.getElementById('rejectionReasonInput').value = '';
    document.getElementById('rejectionReasonInput').classList.remove('is-invalid');
    new bootstrap.Modal(document.getElementById('rejectLeaveModal')).show();
}

function confirmRejectLeave() {
    const reason = document.getElementById('rejectionReasonInput').value.trim();
    if (!reason) { document.getElementById('rejectionReasonInput').classList.add('is-invalid'); return; }
    const req = pendingLeaveRequests.find(r => r.id === pendingRejectId);
    if (req) {
        req.rejectionReason = reason;
        moveToHistory(req, 'leave', 'rejected');
        pendingLeaveRequests = pendingLeaveRequests.filter(r => r.id !== pendingRejectId);
    }
    bootstrap.Modal.getInstance(document.getElementById('rejectLeaveModal')).hide();
    pendingRejectId = null;
    refreshAll();
    toast('Leave request rejected.', 'info');
}

// =============================================================================
// OVERRIDE ACTIONS
// =============================================================================
function openOverrideLeaveModal(id) {
    pendingOverrideId = id;
    const req = pendingLeaveRequests.find(r => r.id === id);
    if (!req) return;
    document.getElementById('overrideLeaveDetails').innerHTML = `
        <p class="mb-1"><strong>Employee:</strong> ${x(req.employeeName)}</p>
        <p class="mb-1"><strong>Leave Type:</strong> ${x(req.type)}</p>
        <p class="mb-1"><strong>Period:</strong> ${x(req.startDate)} – ${x(req.endDate)}</p>
        <p class="mb-0"><strong>Duration:</strong> ${req.days} day(s)</p>`;
    document.getElementById('overrideReasonInput').value = '';
    document.getElementById('overrideReasonInput').classList.remove('is-invalid');
    new bootstrap.Modal(document.getElementById('overrideLeaveModal')).show();
}

function confirmOverrideApprove() {
    const reason = document.getElementById('overrideReasonInput').value.trim();
    if (!reason) { document.getElementById('overrideReasonInput').classList.add('is-invalid'); return; }
    const req = pendingLeaveRequests.find(r => r.id === pendingOverrideId);
    if (req) {
        req.overrideReason = reason;
        moveToHistory(req, 'leave', 'approved');
        pendingLeaveRequests = pendingLeaveRequests.filter(r => r.id !== pendingOverrideId);
    }
    bootstrap.Modal.getInstance(document.getElementById('overrideLeaveModal')).hide();
    pendingOverrideId = null;
    refreshAll();
    toast('Leave approved with override.', 'success');
}

function confirmOverrideReject() {
    const req = pendingLeaveRequests.find(r => r.id === pendingOverrideId);
    if (req) {
        moveToHistory(req, 'leave', 'rejected');
        pendingLeaveRequests = pendingLeaveRequests.filter(r => r.id !== pendingOverrideId);
    }
    bootstrap.Modal.getInstance(document.getElementById('overrideLeaveModal')).hide();
    pendingOverrideId = null;
    refreshAll();
    toast('Leave request rejected due to daily limit.', 'info');
}

// =============================================================================
// OVERTIME ACTIONS
// =============================================================================
function approveOT(id) {
    const req = pendingOTRequests.find(r => r.id === id);
    if (!req) return;
    moveToHistory(req, 'overtime', 'approved');
    pendingOTRequests = pendingOTRequests.filter(r => r.id !== id);
    refreshAll();
    toast(`${req.employeeName}'s OT approved. &#8369;${req.estimatedPay.toFixed(2)}`, 'success');
}

function rejectOT(id) {
    const req = pendingOTRequests.find(r => r.id === id);
    if (!req) return;
    Swal.fire({
        title: 'Reject Overtime?',
        text: `Reject ${req.employeeName}'s overtime request?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Yes, Reject', confirmButtonColor: '#6c757d',
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;
        moveToHistory(req, 'overtime', 'rejected');
        pendingOTRequests = pendingOTRequests.filter(r => r.id !== id);
        refreshAll();
        toast('Overtime request rejected.', 'info');
    });
}

// =============================================================================
// HISTORY DELETE
// =============================================================================
function deleteHistory(id) {
    Swal.fire({
        title: 'Delete Record?', text: 'This history record will be permanently removed.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Delete', confirmButtonColor: '#6c757d',
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;
        historyRequests = historyRequests.filter(r => r.id !== id);
        renderHistoryList();
        updateSummaryCounts();
        toast('History record deleted.', 'info');
    });
}

// =============================================================================
// ATTACHMENT PREVIEW
// =============================================================================
function previewAttachment(leaveId, idx) {
    const req  = pendingLeaveRequests.find(r => r.id === leaveId);
    if (!req || !req.attachments[idx]) return;
    const file = req.attachments[idx];
    document.getElementById('attachmentModalTitle').textContent = file.fileName;
    document.getElementById('attachmentModalBody').innerHTML = `
        <div class="py-4 text-muted">
            <i class="bi bi-file-earmark-text display-4 d-block mb-3 opacity-50"></i>
            <p class="mb-1 fw-medium">${x(file.fileName)}</p>
            <p class="small mb-0">${(file.fileSize / 1024).toFixed(0)} KB</p>
            <p class="small text-muted mt-3">File preview available after backend integration.</p>
        </div>`;
    new bootstrap.Modal(document.getElementById('attachmentModal')).show();
}

// =============================================================================
// UTILITIES
// =============================================================================
function moveToHistory(req, category, status) {
    historyRequests.unshift({
        ...req, category, status,
        reviewedBy: 'HR Manager',
        reviewDate: new Date().toISOString().split('T')[0],
    });
}

function toast(message, type = 'success') {
    Swal.fire({
        toast: true, position: 'top-end',
        icon: type, title: message,
        showConfirmButton: false, timer: 3000, timerProgressBar: true,
    });
}

// XSS-safe string escape for use in innerHTML templates
function x(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endpush