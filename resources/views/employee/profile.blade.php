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
    }
    .field-card .field-value {
        font-size: .9rem;
        font-weight: 500;
        color: var(--bs-body-color);
        word-break: break-word;
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
    .notification-bar {
        border-left: 3px solid var(--bs-border-color);
        border-radius: 0 .375rem .375rem 0;
        background: #ffffff;
        padding: .75rem 1rem;
        margin-bottom: .75rem;
    }
</style>
@endpush

@section('content')

{{-- ── Page Header ──────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">My Profile</h4>
    </div>
    <button class="btn btn-outline-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#requestModal">
        <i class="bi bi-pencil-square me-1"></i> Request Information Update
    </button>
</div>

{{-- ── Notifications ────────────────────────────────────────────── --}}
@foreach ($pendingRequests as $req)
    <div class="notification-bar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-hourglass-split text-secondary"></i>
            <div>
                <span class="fw-semibold">Profile Update Pending —</span>
                <span class="text-muted small">
                    {{ $req->field }} &middot; Submitted {{ \Carbon\Carbon::parse($req->submittedDate)->format('M d, Y') }}
                </span>
            </div>
        </div>
    </div>
@endforeach

@foreach ($recentApproved as $req)
    <div class="notification-bar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle text-secondary"></i>
            <div>
                <span class="fw-semibold">Profile Update Approved —</span>
                <span class="text-muted small">
                    {{ $req->field }} &middot; Reviewed by {{ $req->reviewedBy }}
                </span>
            </div>
        </div>
    </div>
@endforeach

{{-- ── Card with Tabs ───────────────────────────────────────────── --}}
<div class="card mb-0">
    <div class="card-header p-0">
        <ul class="nav profile-tab-nav">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-personal">
                    <i class="bi bi-person me-1"></i> Personal Info
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-employment">
                    <i class="bi bi-briefcase me-1"></i> Employment
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-payroll">
                    <i class="bi bi-cash-stack me-1"></i> Payroll
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-leave">
                    <i class="bi bi-calendar-check me-1"></i> Leave Credits
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body tab-content">

        {{-- ════════════════════════════════════════════════════════
             PERSONAL TAB
             ════════════════════════════════════════════════════════ --}}
        <div id="tab-personal" class="tab-pane fade show active">

            {{-- Basic Information --}}
            <div class="mb-4">
                <div class="section-heading">
                    <i class="bi bi-person me-1"></i> Basic Information
                </div>
                <div class="row g-3">

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Employee ID</div>
                            <div class="field-value">{{ $user->id ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">First Name</div>
                            <div class="field-value">{{ $user->firstName ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Middle Name</div>
                            <div class="field-value">{{ $user->middleName ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Last Name</div>
                            <div class="field-value">{{ $user->lastName ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Gender</div>
                            <div class="field-value">{{ $user->gender ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Date of Birth</div>
                            <div class="field-value">
                                {{ $user->dateOfBirth
                                    ? \Carbon\Carbon::parse($user->dateOfBirth)->format('F d, Y')
                                    : '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Civil Status</div>
                            <div class="field-value">{{ $user->civilStatus ?? '—' }}</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Contact Information --}}
            <div class="mb-4">
                <div class="section-heading">
                    <i class="bi bi-telephone me-1"></i> Contact Information
                </div>
                <div class="row g-3">

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Email Address</div>
                            <div class="field-value">{{ $user->email ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Phone Number</div>
                            <div class="field-value">{{ $user->phoneNumber ?? '—' }}</div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Address --}}
            <div>
                <div class="section-heading">
                    <i class="bi bi-geo-alt me-1"></i> Address
                </div>

                @php
                    $fullAddress = collect([
                        $user->addressStreet,
                        $user->addressBarangay,
                        $user->addressCity,
                        $user->addressProvince,
                        $user->addressRegion,
                        $user->addressZipCode,
                    ])->filter()->implode(', ');
                @endphp

                <div class="row g-3">

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Street / House No.</div>
                            <div class="field-value">{{ $user->addressStreet ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Barangay</div>
                            <div class="field-value">{{ $user->addressBarangay ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">City / Municipality</div>
                            <div class="field-value">{{ $user->addressCity ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Province</div>
                            <div class="field-value">{{ $user->addressProvince ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Region</div>
                            <div class="field-value">{{ $user->addressRegion ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="field-card">
                            <div class="field-label">Zip Code</div>
                            <div class="field-value">{{ $user->addressZipCode ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="field-card">
                            <div class="field-label">Complete Address</div>
                            <div class="field-value">{{ $fullAddress ?: '—' }}</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- ════════════════════════════════════════════════════════
             EMPLOYMENT TAB
             ════════════════════════════════════════════════════════ --}}
        <div id="tab-employment" class="tab-pane fade">
            <div class="section-heading">
                <i class="bi bi-briefcase me-1"></i> Employment Details
            </div>
            <div class="row g-3">

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Position</div>
                        <div class="field-value">{{ $user->position ?? '—' }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Department</div>
                        <div class="field-value">{{ $user->department ?? '—' }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Branch</div>
                        <div class="field-value">{{ $user->branch ?? '—' }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Employment Status</div>
                        <div class="field-value">{{ ucfirst($user->employmentStatus ?? '—') }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Default Shift</div>
                        <div class="field-value">{{ $user->defaultShift ?? '—' }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Date Hired</div>
                        <div class="field-value">
                            {{ $user->hireDate
                                ? \Carbon\Carbon::parse($user->hireDate)->format('F d, Y')
                                : '—' }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════
             PAYROLL TAB
             ════════════════════════════════════════════════════════ --}}
        <div id="tab-payroll" class="tab-pane fade">
            <div class="section-heading">
                <i class="bi bi-cash-stack me-1"></i> Salary & Compensation
            </div>
            <div class="row g-3">

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Basic Salary (Monthly)</div>
                        <div class="field-value">
                            ₱ {{ $user->basicSalary ? number_format($user->basicSalary, 2) : '—' }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Daily Rate</div>
                        <div class="field-value">
                            ₱ {{ $user->dailyRate ? number_format($user->dailyRate, 2) : '—' }}
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-card">
                        <div class="field-label">Hourly Rate</div>
                        <div class="field-value">
                            ₱ {{ $user->hourlyRate ? number_format($user->hourlyRate, 2) : '—' }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════
             LEAVE TAB
             ════════════════════════════════════════════════════════ --}}
        <div id="tab-leave" class="tab-pane fade">
            <div class="section-heading">
                <i class="bi bi-calendar-check me-1"></i> Leave Credits
            </div>
            <div class="row g-3">

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="field-card d-flex align-items-center gap-3">
                        <div>
                            <div class="field-label mb-0">Vacation Leave</div>
                            <div class="field-value">
                                {{ (int) $user->vacationLeaveBalance }}
                                <small class="text-muted fw-normal">days remaining</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="field-card d-flex align-items-center gap-3">
                        <div>
                            <div class="field-label mb-0">Sick Leave</div>
                            <div class="field-value">
                                {{ (int) $user->sickLeaveBalance }}
                                <small class="text-muted fw-normal">days remaining</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="field-card d-flex align-items-center gap-3">
                        <div>
                            <div class="field-label mb-0">Emergency Leave</div>
                            <div class="field-value">
                                {{ (int) $user->emergencyLeaveBalance }}
                                <small class="text-muted fw-normal">days remaining</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="field-card d-flex align-items-center gap-3">
                        <div>
                            <div class="field-label mb-0">Maternity Leave</div>
                            <div class="field-value">
                                {{ (int) $user->maternityLeaveBalance }}
                                <small class="text-muted fw-normal">days remaining</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="field-card d-flex align-items-center gap-3">
                        <div>
                            <div class="field-label mb-0">Paternity Leave</div>
                            <div class="field-value">
                                {{ (int) $user->paternityLeaveBalance }}
                                <small class="text-muted fw-normal">days remaining</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     REQUEST UPDATE MODAL
     ════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('employee.profile.requests.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Request Information Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Field to Update <span class="text-danger">*</span>
                        </label>
                        <select name="field" class="form-select">
                            <option value="">Select a field...</option>
                            <option value="email"       @selected(old('field') === 'email')>Email Address</option>
                            <option value="phone"       @selected(old('field') === 'phone')>Phone Number</option>
                            <option value="civilStatus" @selected(old('field') === 'civilStatus')>Civil Status</option>
                            <option value="address"     @selected(old('field') === 'address')>Address</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            New Value <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="new_value"
                               class="form-control"
                               value="{{ old('new_value') }}"
                               placeholder="Enter the new value">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Reason for Update <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Please explain why you need to update this information...">{{ old('reason') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Submit Request
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')

@endpush