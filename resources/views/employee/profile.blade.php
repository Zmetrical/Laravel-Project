@extends('layouts.main')

@section('title', 'My Profile')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">My Profile</li>
    </ol>
@endsection

@push('styles')
<style>
    .profile-tab-nav .nav-link {
        color: var(--bs-secondary-color);
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: .6rem 1.1rem;
        font-size: .875rem;
    }
    .profile-tab-nav .nav-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: transparent;
    }
    .profile-tab-nav .nav-link:hover:not(.active) {
        color: var(--bs-body-color);
        background: transparent;
    }
    .field-card {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: .375rem;
        padding: .9rem 1rem;
        height: 100%;
    }
    .field-card .field-label {
        font-size: .75rem;
        color: var(--bs-secondary-color);
        margin-bottom: .25rem;
        display: flex;
        align-items: center;
        gap: .3rem;
    }
    .field-card .field-value {
        font-size: .9rem;
        font-weight: 500;
        color: var(--bs-body-color);
        word-break: break-word;
    }
    .field-card .field-tag {
        font-size: .68rem;
        color: var(--bs-secondary-color);
        margin-left: auto;
        opacity: .7;
    }
    .section-heading {
        font-size: .8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--bs-secondary-color);
        border-bottom: 1px solid var(--bs-border-color);
        padding-bottom: .5rem;
        margin-bottom: 1rem;
    }
    .leave-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        font-size: 1rem;
        font-weight: 700;
        background: var(--bs-primary-bg-subtle);
        color: var(--bs-primary);
        border: 1px solid var(--bs-primary-border-subtle);
        flex-shrink: 0;
    }
    .notification-bar {
        border-left: 3px solid;
        border-radius: 0 .375rem .375rem 0;
    }
    .notification-bar.pending {
        border-left-color: var(--bs-primary);
        background: var(--bs-primary-bg-subtle);
    }
    .notification-bar.approved {
        border-left-color: var(--bs-success);
        background: var(--bs-success-bg-subtle);
    }
    .notification-bar.regularized {
        border-left-color: var(--bs-success);
        background: var(--bs-success-bg-subtle);
    }
    #requestModal .modal-dialog {
        max-width: 600px;
    }
    .name-preview-box {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: .375rem;
        padding: .75rem 1rem;
    }
</style>
@endpush

@section('content')

<div id="profileApp">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">My Profile</h4>
            <small class="text-muted">View and manage your basic information</small>
        </div>
        <button class="btn btn-outline-primary btn-sm" onclick="openRequestModal()">
            <i class="bi bi-pencil-square me-1"></i> Request Information Update
        </button>
    </div>

    {{-- Notifications --}}
    <div id="notificationsArea"></div>

    {{-- Card with Tabs --}}
    <div class="card mb-0">
        <div class="card-header p-0">
            <ul class="nav profile-tab-nav" id="profileTabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#" onclick="switchTab('personal', this); return false;">
                        <i class="bi bi-person me-1"></i> Personal Info
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="switchTab('employment', this); return false;">
                        <i class="bi bi-briefcase me-1"></i> Employment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="switchTab('payroll', this); return false;">
                        <i class="bi bi-cash-stack me-1"></i> Payroll
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="switchTab('government', this); return false;">
                        <i class="bi bi-shield-check me-1"></i> Government IDs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="switchTab('leave', this); return false;">
                        <i class="bi bi-calendar-check me-1"></i> Leave Credits
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            {{-- Personal Tab --}}
            <div id="tab-personal" class="tab-pane-profile">
                <div class="mb-4">
                    <div class="section-heading"><i class="bi bi-person me-1"></i> Basic Information</div>
                    <div class="row g-3" id="fields-basic"></div>
                </div>
                <div class="mb-4">
                    <div class="section-heading"><i class="bi bi-telephone me-1"></i> Contact Information</div>
                    <div class="row g-3" id="fields-contact"></div>
                </div>
                <div class="mb-4">
                    <div class="section-heading"><i class="bi bi-geo-alt me-1"></i> Current Address</div>
                    <div class="row g-3" id="fields-current-address"></div>
                </div>
                <div class="mb-4">
                    <div class="section-heading"><i class="bi bi-house me-1"></i> Permanent Address</div>
                    <div class="row g-3" id="fields-permanent-address"></div>
                </div>
                <div>
                    <div class="section-heading"><i class="bi bi-people me-1"></i> Emergency Contact</div>
                    <div class="row g-3" id="fields-emergency"></div>
                </div>
            </div>

            {{-- Employment Tab --}}
            <div id="tab-employment" class="tab-pane-profile d-none">
                <div class="section-heading"><i class="bi bi-briefcase me-1"></i> Employment Details</div>
                <div class="row g-3" id="fields-employment"></div>
            </div>

            {{-- Payroll Tab --}}
            <div id="tab-payroll" class="tab-pane-profile d-none">
                <div class="section-heading"><i class="bi bi-cash-stack me-1"></i> Salary & Compensation</div>
                <div class="row g-3" id="fields-payroll"></div>
            </div>

            {{-- Government Tab --}}
            <div id="tab-government" class="tab-pane-profile d-none">
                <div class="section-heading"><i class="bi bi-shield-check me-1"></i> Government IDs & Contributions</div>
                <div class="row g-3" id="fields-government"></div>
            </div>

            {{-- Leave Tab --}}
            <div id="tab-leave" class="tab-pane-profile d-none">
                <div class="section-heading"><i class="bi bi-calendar-check me-1"></i> Leave Credits</div>
                <div id="leave-cards"></div>
            </div>
        </div>
    </div>

</div>

{{-- Request Update Modal --}}
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Information Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Field Selector --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Field to Update <span class="text-danger">*</span></label>
                    <select class="form-select" id="modalField" onchange="handleFieldChange()">
                        <option value="">Select a field...</option>
                        <option value="email">Email Address</option>
                        <option value="phone">Phone Number</option>
                        <option value="currentAddress">Current Address</option>
                        <option value="civilStatus">Civil Status</option>
                        <option value="emergencyContact">Emergency Contact Person</option>
                    </select>
                    <div id="pendingFieldWarning" class="mt-2 d-none">
                        <small class="text-primary">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            You already have a pending request for this field. Please wait for HR to review it.
                        </small>
                    </div>
                </div>

                {{-- Current Value --}}
                <div class="mb-3 d-none" id="currentValueRow">
                    <label class="form-label fw-semibold text-muted">Current Value</label>
                    <div class="field-card"><span id="currentValueDisplay" class="text-muted">—</span></div>
                </div>

                {{-- Dynamic Input Area --}}
                <div id="dynamicInputArea" class="d-none">
                    {{-- Injected by JS --}}
                </div>

                {{-- Marriage Name Change (Female only) --}}
                <div id="nameChangeSection" class="d-none mb-3">
                    <div class="border rounded p-3 bg-body-tertiary">
                        <p class="mb-2 fw-semibold small">Name Change (Required — Philippine Law)</p>
                        <p class="text-muted small mb-3">Your current last name will automatically become your new middle name.</p>
                        <div class="mb-2 name-preview-box">
                            <small class="text-muted">New Middle Name (Automatic)</small>
                            <div class="fw-semibold" id="autoMiddleName">—</div>
                        </div>
                        <label class="form-label small fw-semibold">New Last Name (Husband's Surname) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newLastName" placeholder="Enter husband's last name...">
                        <div id="namePreviewBox" class="mt-3 d-none name-preview-box">
                            <small class="text-muted d-block mb-2">Name Preview After Marriage:</small>
                            <div class="row text-center g-2">
                                <div class="col-4"><small class="text-muted d-block">First</small><span id="previewFirst" class="fw-semibold small"></span></div>
                                <div class="col-4"><small class="text-muted d-block">Middle</small><span id="previewMiddle" class="fw-semibold small text-success"></span></div>
                                <div class="col-4"><small class="text-muted d-block">Last</small><span id="previewLast" class="fw-semibold small text-primary"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reason --}}
                <div class="mb-3 d-none" id="reasonRow">
                    <label class="form-label fw-semibold">Reason for Update <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="modalReason" rows="3" placeholder="Please explain why you need to update this information..."></textarea>
                </div>

                {{-- Note --}}
                <div class="d-none" id="noteRow">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Your request will be reviewed by the HR Department. You will be notified once your information has been updated.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitRequestBtn" onclick="submitRequest()">
                    <i class="bi bi-send me-1"></i> Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script>

const profile = {
    employeeId:               'EMP-0042',
    fullName:                 'Maria Santos Cruz',
    firstName:                'Maria',
    middleName:               'Santos',
    lastName:                 'Cruz',
    suffix:                   '',
    email:                    'maria.cruz@fastservices.com',
    phone:                    '09171234567',
    birthDate:                '1995-03-15',
    age:                      '29',
    gender:                   'Female',
    civilStatus:              'Single',
    nationality:              'Filipino',
    placeOfBirth:             'Manila, Metro Manila',
    bloodType:                'O+',
    soloParent:               false,

    currentAddressStreet:     '123 Sampaguita St., Unit 2B',
    currentAddressBarangay:   'Barangay Poblacion',
    currentAddressCity:       'Meycauayan City',
    currentAddressProvince:   'Bulacan',
    currentAddressRegion:     'Region III – Central Luzon',
    currentAddressZipCode:    '3020',

    permanentAddressStreet:   '88 Rizal Ave.',
    permanentAddressBarangay: 'Barangay San Isidro',
    permanentAddressCity:     'Malolos City',
    permanentAddressProvince: 'Bulacan',
    permanentAddressRegion:   'Region III – Central Luzon',
    permanentAddressZipCode:  '3000',

    emergencyContactName:         'Jose Cruz',
    emergencyContactRelationship: 'Spouse',
    emergencyContactPhone:        '09189876543',
    emergencyContactAddress:      '123 Sampaguita St., Meycauayan City',

    // Employment
    position:            'HR Specialist',
    department:          'Human Resources',
    branch:              'Meycauayan Main',
    employmentStatus:    'Regular',
    hireDate:            '2021-07-01',
    regularizationDate:  '2022-01-01',

    // Payroll
    basicSalary:    25000,
    paymentMethod:  'Bank Transfer',
    bankName:       'BDO Unibank',
    bankAccount:    '****-****-5678',

    // Government IDs
    tin:        '123-456-789-000',
    sss:        '33-1234567-8',
    pagibig:    '1234-5678-9012',
    philhealth: '12-123456789-1',

    // Leave
    vacationLeaveBalance:  5,
    sickLeaveBalance:      5,
    emergencyLeaveBalance: 5,
    maternityLeaveBalance: 0,
    paternityLeaveBalance: 0,
};

// Simulated pending profile update requests
const pendingRequests = [
    // { field: 'email', status: 'pending', submittedDate: '2025-12-01T09:00:00' }
];

const approvedRequests = [
    // { field: 'phone', status: 'approved', reviewedBy: 'HR Admin', reviewedDate: '2025-12-03T10:00:00' }
];


// ============================================================
// HELPERS
// ============================================================
function formatAddress(parts) {
    return parts.filter(Boolean).join(', ') || 'N/A';
}

function getCurrentValue(field) {
    switch (field) {
        case 'email':           return profile.email;
        case 'phone':           return profile.phone;
        case 'civilStatus':     return profile.civilStatus;
        case 'currentAddress':  return formatAddress([
            profile.currentAddressStreet, profile.currentAddressBarangay,
            profile.currentAddressCity,   profile.currentAddressProvince,
            profile.currentAddressRegion, profile.currentAddressZipCode
        ]);
        case 'emergencyContact': return `${profile.emergencyContactName || 'N/A'} (${profile.emergencyContactRelationship || 'N/A'}) — ${profile.emergencyContactPhone || 'N/A'}`;
        default: return '';
    }
}

function hasFieldPending(field) {
    return pendingRequests.some(r => r.field === field && r.status === 'pending');
}

function fieldCard(label, value, icon, tag) {
    const tagHtml = tag ? `<span class="field-tag">${tag}</span>` : '';
    return `
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="field-card">
                <div class="field-label">
                    <i class="bi bi-${icon}"></i>
                    <span>${label}</span>
                    ${tagHtml}
                </div>
                <div class="field-value">${value || '<span class="text-muted fst-italic">N/A</span>'}</div>
            </div>
        </div>`;
}

function fieldCardFull(label, value, icon, tag) {
    const tagHtml = tag ? `<span class="field-tag">${tag}</span>` : '';
    return `
        <div class="col-12">
            <div class="field-card">
                <div class="field-label">
                    <i class="bi bi-${icon}"></i>
                    <span>${label}</span>
                    ${tagHtml}
                </div>
                <div class="field-value">${value || '<span class="text-muted fst-italic">N/A</span>'}</div>
            </div>
        </div>`;
}


// ============================================================
// RENDER FIELDS
// ============================================================
function renderAllFields() {
    // Basic Info
    document.getElementById('fields-basic').innerHTML = [
        fieldCard('Employee ID',    profile.employeeId,  'person-badge',    'HR Only'),
        fieldCard('First Name',     profile.firstName,   'person',          'HR Only'),
        fieldCard('Middle Name',    profile.middleName || '—', 'person',    'HR Only'),
        fieldCard('Last Name',      profile.lastName,    'person',          'HR Only'),
        fieldCard('Suffix',         profile.suffix || '—', 'person',        'HR Only'),
        fieldCard('Full Name',      profile.fullName,    'person-lines-fill','HR Only'),
        fieldCard('Date of Birth',  profile.birthDate,   'calendar',        'HR Only'),
        fieldCard('Age',            profile.age,         '123',             'HR Only'),
        fieldCard('Gender',         profile.gender,      'gender-ambiguous','HR Only'),
        fieldCard('Civil Status',   profile.civilStatus, 'heart',           'HR Only'),
        fieldCard('Nationality',    profile.nationality, 'globe',           'HR Only'),
        fieldCard('Place of Birth', profile.placeOfBirth,'geo-alt',         'HR Only'),
        fieldCard('Blood Type',     profile.bloodType,   'droplet',         'HR Only'),
        ...(profile.gender === 'Female' ? [`
            <div class="col-12 col-sm-6">
                <div class="field-card">
                    <div class="field-label">
                        <i class="bi bi-people"></i>
                        <span>Solo Parent Status</span>
                        <span class="field-tag">HR Only</span>
                    </div>
                    <div class="field-value d-flex align-items-center gap-2">
                        ${profile.soloParent ? 'Yes' : 'No'}
                        ${profile.soloParent ? '<span class="badge text-bg-secondary ms-1 fw-normal">Qualified: 120-day Maternity Leave</span>' : ''}
                    </div>
                </div>
            </div>`] : [])
    ].join('');

    // Contact
    document.getElementById('fields-contact').innerHTML = [
        fieldCard('Email Address', profile.email, 'envelope',       'HR Only'),
        fieldCard('Phone Number',  profile.phone, 'telephone',      'HR Only'),
    ].join('');

    // Current Address
    const cAddr = formatAddress([
        profile.currentAddressStreet, profile.currentAddressBarangay,
        profile.currentAddressCity,   profile.currentAddressProvince,
        profile.currentAddressRegion, profile.currentAddressZipCode
    ]);
    document.getElementById('fields-current-address').innerHTML = [
        fieldCard('Street / House No.', profile.currentAddressStreet, 'sign-intersection-y', 'HR Only'),
        fieldCard('Barangay',           profile.currentAddressBarangay, 'geo',              'HR Only'),
        fieldCard('City / Municipality',profile.currentAddressCity,    'buildings',         'HR Only'),
        fieldCard('Province',           profile.currentAddressProvince,'map',               'HR Only'),
        fieldCard('Zip Code',           profile.currentAddressZipCode, 'mailbox',           'HR Only'),
        fieldCardFull('Complete Address', cAddr, 'geo-alt-fill', 'HR Only'),
    ].join('');

    // Permanent Address
    const pAddr = formatAddress([
        profile.permanentAddressStreet, profile.permanentAddressBarangay,
        profile.permanentAddressCity,   profile.permanentAddressProvince,
        profile.permanentAddressRegion, profile.permanentAddressZipCode
    ]);
    document.getElementById('fields-permanent-address').innerHTML = [
        fieldCard('Street / House No.', profile.permanentAddressStreet, 'sign-intersection-y', 'HR Only'),
        fieldCard('Barangay',           profile.permanentAddressBarangay, 'geo',               'HR Only'),
        fieldCard('City / Municipality',profile.permanentAddressCity,    'buildings',          'HR Only'),
        fieldCard('Province',           profile.permanentAddressProvince,'map',                'HR Only'),
        fieldCard('Zip Code',           profile.permanentAddressZipCode, 'mailbox',            'HR Only'),
        fieldCardFull('Complete Address', pAddr, 'geo-alt-fill', 'HR Only'),
    ].join('');

    // Emergency Contact
    document.getElementById('fields-emergency').innerHTML = [
        fieldCard('Contact Name',        profile.emergencyContactName,         'person-heart',  'HR Only'),
        fieldCard('Relationship',        profile.emergencyContactRelationship, 'diagram-3',     'HR Only'),
        fieldCard('Contact Phone',       profile.emergencyContactPhone,        'telephone-plus','HR Only'),
        fieldCard('Contact Address',     profile.emergencyContactAddress,      'geo-alt',       'HR Only'),
    ].join('');

    // Employment
    const regBadge = `<span class="badge text-bg-secondary fw-normal ms-1" style="font-size:.68rem;">Auto: Hire Date + 6 months</span>`;
    document.getElementById('fields-employment').innerHTML = [
        fieldCard('Position / Job Title', profile.position,         'briefcase',       'HR Only'),
        fieldCard('Department',           profile.department,       'diagram-2',       'HR Only'),
        fieldCard('Branch',               profile.branch,           'building',        'HR Only'),
        fieldCard('Employment Status',    profile.employmentStatus, 'person-check',    'HR Only'),
        fieldCard('Date Hired',           profile.hireDate,         'calendar-event',  'HR Only'),
        `<div class="col-12 col-sm-6 col-lg-4">
            <div class="field-card">
                <div class="field-label">
                    <i class="bi bi-calendar2-check"></i>
                    <span>Regularization Date</span>
                    <span class="field-tag">HR Only</span>
                </div>
                <div class="field-value d-flex align-items-center flex-wrap gap-1">
                    ${profile.regularizationDate || '<span class="text-muted fst-italic">N/A</span>'}
                    ${regBadge}
                </div>
            </div>
        </div>`,
    ].join('');

    // Payroll
    document.getElementById('fields-payroll').innerHTML = [
        fieldCard('Basic Salary (Monthly)', `₱ ${Number(profile.basicSalary).toLocaleString('en-PH', {minimumFractionDigits:2})}`, 'cash',        'HR Only'),
        fieldCard('Payment Method',          profile.paymentMethod,  'bank',            'HR Only'),
        fieldCard('Bank Name',               profile.bankName,       'building-fill',   'HR Only'),
        fieldCard('Bank Account Number',     profile.bankAccount,    'credit-card',     'HR Only'),
    ].join('');

    // Government
    document.getElementById('fields-government').innerHTML = [
        fieldCard('TIN',       profile.tin,        'file-earmark-text', 'HR Only'),
        fieldCard('SSS',       profile.sss,        'shield',            'HR Only'),
        fieldCard('Pag-IBIG',  profile.pagibig,    'house-heart',       'HR Only'),
        fieldCard('PhilHealth',profile.philhealth, 'heart-pulse',       'HR Only'),
    ].join('');

    // Leave
    const leaveItems = [
        { label: 'Vacation Leave',   balance: profile.vacationLeaveBalance,  icon: 'airplane' },
        { label: 'Sick Leave',       balance: profile.sickLeaveBalance,       icon: 'bandaid' },
        { label: 'Emergency Leave',  balance: profile.emergencyLeaveBalance,  icon: 'lightning-charge' },
        { label: 'Maternity Leave',  balance: profile.maternityLeaveBalance,  icon: 'person-hearts' },
        { label: 'Paternity Leave',  balance: profile.paternityLeaveBalance,  icon: 'person-hearts' },
    ];
    document.getElementById('leave-cards').innerHTML = `
        <div class="row g-3">
            ${leaveItems.map(item => `
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="field-card d-flex align-items-center gap-3">
                        <div class="leave-badge">${item.balance}</div>
                        <div>
                            <div class="field-label mb-0"><i class="bi bi-${item.icon} me-1"></i>${item.label}</div>
                            <small class="text-muted">${item.balance} day${item.balance !== 1 ? 's' : ''} remaining</small>
                        </div>
                    </div>
                </div>`).join('')}
        </div>`;
}


// ============================================================
// NOTIFICATIONS
// ============================================================
function renderNotifications() {
    let html = '';

    // Regularization congratulations (only if status just became Regular today)
    const today = new Date().toISOString().split('T')[0];
    if (profile.employmentStatus === 'Regular' && profile.regularizationDate === today) {
        html += `
            <div class="notification-bar regularized p-3 mb-3 rounded-end">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-patch-check-fill text-success fs-5 mt-1"></i>
                    <div>
                        <div class="fw-semibold text-success mb-1">Congratulations! You are now a Regular Employee.</div>
                        <small class="text-muted">Effective: ${profile.regularizationDate}</small>
                    </div>
                </div>
            </div>`;
    }

    // Pending request notifications
    pendingRequests.forEach(req => {
        html += `
            <div class="notification-bar pending p-3 mb-3 rounded-end">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-hourglass-split text-primary fs-5 mt-1"></i>
                    <div>
                        <div class="fw-semibold text-primary mb-1">Profile Update Request — Pending Review</div>
                        <small class="text-muted">Field: <strong>${req.field}</strong> &nbsp;·&nbsp; Submitted: ${req.submittedDate.split('T')[0]}</small>
                    </div>
                </div>
            </div>`;
    });

    // Approved request notifications (within 48 hrs)
    approvedRequests.forEach(req => {
        html += `
            <div class="notification-bar approved p-3 mb-3 rounded-end">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-check-circle-fill text-success fs-5 mt-1"></i>
                    <div>
                        <div class="fw-semibold text-success mb-1">Profile Update Approved</div>
                        <small class="text-muted">Field: <strong>${req.field}</strong> &nbsp;·&nbsp; Reviewed by: ${req.reviewedBy}</small>
                    </div>
                </div>
            </div>`;
    });

    document.getElementById('notificationsArea').innerHTML = html;
}


// ============================================================
// TABS
// ============================================================
function switchTab(tab, el) {
    document.querySelectorAll('.tab-pane-profile').forEach(p => p.classList.add('d-none'));
    document.querySelectorAll('#profileTabs .nav-link').forEach(l => l.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.remove('d-none');
    el.classList.add('active');
}


// ============================================================
// MODAL
// ============================================================
function openRequestModal() {
    document.getElementById('modalField').value       = '';
    document.getElementById('modalReason').value      = '';
    document.getElementById('currentValueRow').classList.add('d-none');
    document.getElementById('dynamicInputArea').classList.add('d-none');
    document.getElementById('reasonRow').classList.add('d-none');
    document.getElementById('noteRow').classList.add('d-none');
    document.getElementById('nameChangeSection').classList.add('d-none');
    document.getElementById('pendingFieldWarning').classList.add('d-none');
    document.getElementById('dynamicInputArea').innerHTML = '';

    const modal = new bootstrap.Modal(document.getElementById('requestModal'));
    modal.show();
}

function handleFieldChange() {
    const field   = document.getElementById('modalField').value;
    const pending = hasFieldPending(field);

    // Pending warning
    document.getElementById('pendingFieldWarning').classList.toggle('d-none', !pending);

    if (!field) {
        document.getElementById('currentValueRow').classList.add('d-none');
        document.getElementById('dynamicInputArea').classList.add('d-none');
        document.getElementById('reasonRow').classList.add('d-none');
        document.getElementById('noteRow').classList.add('d-none');
        document.getElementById('nameChangeSection').classList.add('d-none');
        return;
    }

    // Current value
    document.getElementById('currentValueDisplay').textContent = getCurrentValue(field);
    document.getElementById('currentValueRow').classList.remove('d-none');

    // Build dynamic input
    let inputHtml = '';
    if (field === 'email') {
        inputHtml = `
            <div>
                <label class="form-label fw-semibold">New Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="newFieldValue" placeholder="e.g. newemail@example.com">
                <div class="invalid-feedback" id="fieldError"></div>
            </div>`;
    } else if (field === 'phone') {
        inputHtml = `
            <div>
                <label class="form-label fw-semibold">New Phone Number <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="newFieldValue" maxlength="11" placeholder="11-digit number" oninput="this.value=this.value.replace(/\\D/g,'').slice(0,11)">
                <small class="text-muted"><span id="phoneCount">0</span>/11 digits</small>
                <div class="invalid-feedback" id="fieldError"></div>
            </div>`;
    } else if (field === 'civilStatus') {
        inputHtml = `
            <div>
                <label class="form-label fw-semibold">New Civil Status <span class="text-danger">*</span></label>
                <select class="form-select" id="newFieldValue" onchange="handleCivilStatusChange()">
                    <option value="">Select...</option>
                    <option>Single</option>
                    <option>Married</option>
                    <option>Widowed</option>
                    <option>Separated</option>
                </select>
            </div>`;
    } else if (field === 'currentAddress') {
        inputHtml = `
            <div class="border rounded p-3 bg-body-tertiary">
                <p class="small fw-semibold mb-3">New Address Details</p>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small">Street / House No.</label>
                        <input type="text" class="form-control form-control-sm" id="addrStreet" placeholder="e.g. 123 Main Street">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">Barangay</label>
                        <input type="text" class="form-control form-control-sm" id="addrBarangay">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">City / Municipality</label>
                        <input type="text" class="form-control form-control-sm" id="addrCity">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">Province</label>
                        <input type="text" class="form-control form-control-sm" id="addrProvince">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">Zip Code</label>
                        <input type="text" class="form-control form-control-sm" id="addrZip" maxlength="4" oninput="this.value=this.value.replace(/\\D/g,'').slice(0,4)">
                    </div>
                </div>
            </div>`;
    } else if (field === 'emergencyContact') {
        inputHtml = `
            <div class="border rounded p-3 bg-body-tertiary">
                <p class="small fw-semibold mb-3">New Emergency Contact</p>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="ecName" placeholder="Full name">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">Relationship <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="ecRelationship">
                            <option value="">Select...</option>
                            <option>Spouse</option><option>Parent</option><option>Sibling</option>
                            <option>Child</option><option>Relative</option><option>Friend</option><option>Other</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control form-control-sm" id="ecPhone" maxlength="11" placeholder="11 digits" oninput="this.value=this.value.replace(/\\D/g,'').slice(0,11)">
                    </div>
                </div>
            </div>`;
    }

    document.getElementById('dynamicInputArea').innerHTML = inputHtml;
    document.getElementById('dynamicInputArea').classList.remove('d-none');
    document.getElementById('reasonRow').classList.remove('d-none');
    document.getElementById('noteRow').classList.remove('d-none');
    document.getElementById('nameChangeSection').classList.add('d-none');

    // Wire up phone counter
    if (field === 'phone') {
        document.getElementById('newFieldValue').addEventListener('input', function () {
            document.getElementById('phoneCount').textContent = this.value.length;
        });
    }
}

function handleCivilStatusChange() {
    const val = document.getElementById('newFieldValue').value;
    const nameSection = document.getElementById('nameChangeSection');
    if (val === 'Married' && profile.gender === 'Female') {
        document.getElementById('autoMiddleName').textContent = profile.lastName || '—';
        document.getElementById('previewFirst').textContent  = profile.firstName;
        document.getElementById('previewMiddle').textContent = profile.lastName;
        nameSection.classList.remove('d-none');

        document.getElementById('newLastName').addEventListener('input', function () {
            document.getElementById('previewLast').textContent = this.value || '—';
            document.getElementById('namePreviewBox').classList.toggle('d-none', !this.value);
        });
    } else {
        nameSection.classList.add('d-none');
    }
}

function submitRequest() {
    const field  = document.getElementById('modalField').value;
    const reason = document.getElementById('modalReason').value.trim();

    if (!field) { showAlert('Please select a field to update.', 'warning'); return; }
    if (!reason) { showAlert('Please provide a reason for this update.', 'warning'); return; }

    // Duplicate pending check
    if (hasFieldPending(field)) {
        showAlert('You already have a pending request for this field. Please wait for HR to review it.', 'error'); return;
    }

    // Field-specific validation
    if (field === 'email') {
        const val = document.getElementById('newFieldValue').value;
        if (!val || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            showAlert('Please enter a valid email address.', 'warning'); return;
        }
    }

    if (field === 'phone') {
        const val = document.getElementById('newFieldValue').value;
        if (!val || val.length !== 11) {
            showAlert('Phone number must be exactly 11 digits.', 'warning'); return;
        }
    }

    if (field === 'civilStatus') {
        const val = document.getElementById('newFieldValue').value;
        if (!val) { showAlert('Please select a civil status.', 'warning'); return; }
        if (val === 'Married' && profile.gender === 'Female') {
            const newLast = document.getElementById('newLastName').value.trim();
            if (!newLast) { showAlert("Please enter the husband's last name.", 'warning'); return; }
        }
    }

    if (field === 'currentAddress') {
        const parts = ['addrStreet','addrBarangay','addrCity','addrProvince','addrZip'].map(id => document.getElementById(id)?.value.trim()).filter(Boolean);
        if (parts.length === 0) { showAlert('Please fill in at least one address field.', 'warning'); return; }
    }

    if (field === 'emergencyContact') {
        const name = document.getElementById('ecName').value.trim();
        const rel  = document.getElementById('ecRelationship').value;
        const ph   = document.getElementById('ecPhone').value;
        if (!name)            { showAlert('Please enter the contact name.', 'warning'); return; }
        if (!rel)             { showAlert('Please select a relationship.', 'warning'); return; }
        if (ph.length !== 11) { showAlert('Emergency contact phone must be exactly 11 digits.', 'warning'); return; }
    }

    // Success — in real app, send via AJAX/form
    bootstrap.Modal.getInstance(document.getElementById('requestModal')).hide();
    Swal.fire({ icon: 'success', title: 'Request Submitted', text: 'HR will review your request and update your information.', confirmButtonColor: 'var(--bs-primary)' });
}

function showAlert(msg, type) {
    Swal.fire({ icon: type, title: type === 'error' ? 'Error' : 'Notice', text: msg, confirmButtonColor: 'var(--bs-primary)' });
}


// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    renderAllFields();
    renderNotifications();
});
</script>
@endpush