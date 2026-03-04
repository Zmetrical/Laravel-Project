@extends('layouts.main')

@section('title', 'Add New Employee')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Employees</a></li>
        <li class="breadcrumb-item active">Add New Employee</li>
    </ol>
@endsection

@section('content')

{{-- Step Progress Bar --}}
<div class="card card-outline card-primary mb-3">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-12">
                <div class="d-flex align-items-center gap-3" id="stepIndicator">
                    {{-- Step 1 --}}
                    <div class="d-flex align-items-center gap-2 step-item" data-step="1">
                        <div class="step-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                             style="width:36px;height:36px;font-size:14px;" id="stepCircle1">1</div>
                        <span class="fw-semibold text-primary" id="stepLabel1">Employee Information</span>
                    </div>
                    <div class="text-muted mx-2">&#8250;</div>
                    {{-- Step 2 --}}
                    <div class="d-flex align-items-center gap-2 step-item" data-step="2">
                        <div class="step-circle bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                             style="width:36px;height:36px;font-size:14px;" id="stepCircle2">2</div>
                        <span class="fw-semibold text-secondary" id="stepLabel2">Biometric Enrollment</span>
                    </div>
                    <div class="text-muted mx-2">&#8250;</div>
                    {{-- Step 3 --}}
                    <div class="d-flex align-items-center gap-2 step-item" data-step="3">
                        <div class="step-circle bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                             style="width:36px;height:36px;font-size:14px;" id="stepCircle3">3</div>
                        <span class="fw-semibold text-secondary" id="stepLabel3">Complete</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 1: Employee Information --}}
{{-- ============================================================ --}}
<div id="step1Panel">

    {{-- Required Fields Card --}}
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-circle me-2 text-secondary"></i>
                Required Information for Registration
            </h3>
        </div>
        <div class="card-body">

            {{-- Account Credentials --}}
            <p class="text-secondary fw-semibold mb-2 border-bottom pb-1">
                <i class="fas fa-key me-1"></i> Account Credentials
            </p>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="fUsername" maxlength="30"
                           autocomplete="off" placeholder="e.g., jdoe">
                    <div class="invalid-feedback" id="errUsername"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Default Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="fPassword" maxlength="50"
                               autocomplete="new-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback d-block" id="errPassword"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="fEmail" maxlength="100"
                           autocomplete="off" placeholder="user@company.com">
                    <div class="invalid-feedback" id="errEmail"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">System Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="fRole">
                        <option value="employee">Employee</option>
                        <option value="hr">HR</option>
                        <option value="accounting">Accounting</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            {{-- Organization Assignment --}}
            <p class="text-secondary fw-semibold mb-2 border-bottom pb-1">
                <i class="fas fa-sitemap me-1"></i> Organization Assignment
            </p>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Department <span class="text-danger">*</span></label>
                    <select class="form-select" id="fDepartment">
                        <option value="">Select Department</option>
                    </select>
                    <div class="invalid-feedback" id="errDepartment"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Position <span class="text-danger">*</span></label>
                    <select class="form-select" id="fPosition" disabled>
                        <option value="">Select Department first</option>
                    </select>
                    <div class="invalid-feedback" id="errPosition"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    <select class="form-select" id="fBranch">
                        <option value="">Select Branch</option>
                    </select>
                    <div class="invalid-feedback" id="errBranch"></div>
                </div>
            </div>

            {{-- Personal Details --}}
            <p class="text-secondary fw-semibold mb-2 border-bottom pb-1">
                <i class="fas fa-user me-1"></i> Personal Details
            </p>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="fFirstName" maxlength="50"
                           placeholder="e.g., Juan">
                    <div class="invalid-feedback" id="errFirstName"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="fLastName" maxlength="50"
                           placeholder="e.g., Dela Cruz">
                    <div class="invalid-feedback" id="errLastName"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Employee ID <small class="text-muted">(Auto-generated)</small></label>
                    <input type="text" class="form-control bg-light" id="fEmployeeId" readonly>
                </div>
            </div>

            {{-- Payroll --}}
            <p class="text-secondary fw-semibold mb-2 border-bottom pb-1">
                <i class="fas fa-peso-sign me-1"></i> Payroll
            </p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Basic Monthly Salary <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" id="fBasicSalary"
                               min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="invalid-feedback d-block" id="errBasicSalary"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Details Tabs --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="detailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="tab-personal" data-bs-toggle="tab"
                       href="#panel-personal" role="tab">
                        <i class="fas fa-user me-1"></i> Personal Info
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="tab-employment" data-bs-toggle="tab"
                       href="#panel-employment" role="tab">
                        <i class="fas fa-briefcase me-1"></i> Employment
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="tab-payroll" data-bs-toggle="tab"
                       href="#panel-payroll" role="tab">
                        <i class="fas fa-money-bill me-1"></i> Payroll
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="tab-gov" data-bs-toggle="tab"
                       href="#panel-gov" role="tab">
                        <i class="fas fa-id-card me-1"></i> Government IDs
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="tab-leave" data-bs-toggle="tab"
                       href="#panel-leave" role="tab">
                        <i class="fas fa-calendar-check me-1"></i> Leave Credits
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="detailTabContent">

                {{-- Personal Info Tab --}}
                <div class="tab-pane fade show active" id="panel-personal" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="fMiddleName" maxlength="50">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Suffix</label>
                            <input type="text" class="form-control" id="fSuffix"
                                   maxlength="10" placeholder="Jr., Sr., III">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Full Name <small class="text-muted">(Auto)</small></label>
                            <input type="text" class="form-control bg-light" id="fFullName" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="fDob">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Age <small class="text-muted">(Auto)</small></label>
                            <input type="text" class="form-control bg-light" id="fAge" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" id="fGender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Civil Status</label>
                            <select class="form-select" id="fCivilStatus">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nationality</label>
                            <input type="text" class="form-control" id="fNationality"
                                   maxlength="50" value="Filipino">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Place of Birth</label>
                            <input type="text" class="form-control" id="fPlaceOfBirth" maxlength="100">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Blood Type</label>
                            <select class="form-select" id="fBloodType">
                                <option value="">Unknown</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+63</span>
                                <input type="tel" class="form-control" id="fPhone"
                                       maxlength="10" placeholder="9XXXXXXXXX">
                            </div>
                        </div>
                        <div class="col-12">
                            <hr class="my-1">
                            <p class="text-secondary fw-semibold mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i> Address
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Region</label>
                            <select class="form-select" id="fRegion">
                                <option value="">Select Region</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Province</label>
                            <select class="form-select" id="fProvince" disabled>
                                <option value="">Select Region first</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City / Municipality</label>
                            <select class="form-select" id="fCity" disabled>
                                <option value="">Select Province first</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" class="form-control" id="fBarangay"
                                   maxlength="100" placeholder="Enter barangay">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Street / House No. / Building</label>
                            <input type="text" class="form-control" id="fStreet" maxlength="200"
                                   placeholder="e.g., 123 Rizal Street, Unit 4B">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" id="fZip"
                                   maxlength="4" placeholder="0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Complete Address <small class="text-muted">(Auto)</small></label>
                            <textarea class="form-control bg-light" id="fCompleteAddress"
                                      rows="2" readonly></textarea>
                        </div>
                    </div>
                </div>

                {{-- Employment Tab --}}
                <div class="tab-pane fade" id="panel-employment" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Hire Date <small class="text-muted">(Auto-set to today)</small></label>
                            <input type="date" class="form-control bg-light" id="fHireDate" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employment Status</label>
                            <select class="form-select" id="fEmploymentStatus">
                                <option value="probationary">Probationary</option>
                                <option value="regular">Regular</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employment Type</label>
                            <select class="form-select" id="fEmploymentType">
                                <option value="full-time">Full-time</option>
                                <option value="part-time">Part-time</option>
                                <option value="contractual">Contractual</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Work Schedule</label>
                            <select class="form-select" id="fWorkSchedule">
                                <option value="Monday - Friday">Monday – Friday</option>
                                <option value="Monday - Saturday">Monday – Saturday</option>
                                <option value="Shifting">Shifting</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Default Shift</label>
                            <select class="form-select" id="fDefaultShift">
                                <option value="Day">Day</option>
                                <option value="Night">Night</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Direct Supervisor</label>
                            <select class="form-select" id="fSupervisor">
                                <option value="">Select Department first</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="regularizationDateGroup" style="display:none;">
                            <label class="form-label">
                                Regularization Date <small class="text-muted">(Auto-computed)</small>
                            </label>
                            <input type="date" class="form-control bg-light" id="fRegularizationDate" readonly>
                        </div>
                    </div>
                </div>

                {{-- Payroll Tab --}}
                <div class="tab-pane fade" id="panel-payroll" role="tabpanel">
                    <div class="callout callout-secondary mb-3">
                        <small>Basic Salary (required) is set in the Required Information section above.</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Payroll Frequency</label>
                            <select class="form-select" id="fPayrollFrequency">
                                <option value="semi-monthly">Semi-monthly</option>
                                <option value="monthly">Monthly</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" id="fPaymentMethod">
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                                <option value="Check">Check</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bank Name</label>
                            <select class="form-select" id="fBankName">
                                <option value="">— None —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bank Account Number</label>
                            <input type="text" class="form-control" id="fBankAccount"
                                   maxlength="20" placeholder="Account number">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Daily Rate <small class="text-muted">(Auto)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control bg-light" id="fDailyRate" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hourly Rate <small class="text-muted">(Auto)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control bg-light" id="fHourlyRate" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Government IDs Tab --}}
                <div class="tab-pane fade" id="panel-gov" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">SSS Number</label>
                            <input type="text" class="form-control" id="fSss"
                                   maxlength="12" placeholder="XX-XXXXXXX-X">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">TIN Number</label>
                            <input type="text" class="form-control" id="fTin"
                                   maxlength="15" placeholder="XXX-XXX-XXX-XXX">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PhilHealth Number</label>
                            <input type="text" class="form-control" id="fPhilhealth"
                                   maxlength="14" placeholder="XX-XXXXXXXXX-X">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pag-IBIG (HDMF) Number</label>
                            <input type="text" class="form-control" id="fPagibig"
                                   maxlength="14" placeholder="XXXX-XXXX-XXXX">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tax Status</label>
                            <select class="form-select" id="fTaxStatus">
                                <option value="S">S – Single</option>
                                <option value="ME">ME – Married</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                                <option value="S4">S4</option>
                                <option value="ME1">ME1</option>
                                <option value="ME2">ME2</option>
                                <option value="ME3">ME3</option>
                                <option value="ME4">ME4</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Leave Credits Tab --}}
                <div class="tab-pane fade" id="panel-leave" role="tabpanel">
                    <div class="callout callout-secondary mb-3">
                        <small>
                            <strong>Regular employees</strong> automatically receive 5 days each (Vacation, Sick, Emergency).
                            <strong>Probationary</strong> employees receive 0 credits until regularization.
                        </small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Vacation Leave <small class="text-muted">(Auto)</small></label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-light" id="fVacLeave" readonly value="0">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sick Leave <small class="text-muted">(Auto)</small></label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-light" id="fSickLeave" readonly value="0">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Emergency Leave <small class="text-muted">(Auto)</small></label>
                            <div class="input-group">
                                <input type="number" class="form-control bg-light" id="fEmerLeave" readonly value="0">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Leave Credits Start Date <small class="text-muted">(Auto)</small></label>
                            <input type="date" class="form-control bg-light" id="fLeaveStartDate" readonly>
                        </div>
                    </div>
                </div>

            </div>{{-- /tab-content --}}
        </div>
    </div>

    {{-- Step 1 Actions --}}
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="#" class="btn btn-secondary px-4">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button class="btn btn-primary px-4" id="btnNextBiometric">
            Next: Enroll Biometric <i class="fas fa-chevron-right ms-1"></i>
        </button>
    </div>

</div>
{{-- /step1Panel --}}


{{-- ============================================================ --}}
{{-- STEP 2: Biometric Enrollment --}}
{{-- ============================================================ --}}
<div id="step2Panel" style="display:none;">

    {{-- Finger Selection --}}
    <div id="biometricIdle">
        <div class="card card-outline card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-fingerprint me-2"></i> Select Finger for Enrollment
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Choose which finger to register for biometric authentication.</p>

                <div class="row g-2 mb-4" id="fingerGrid">
                    {{-- Populated by JS --}}
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary px-4" id="btnBackToInfo">
                        <i class="fas fa-chevron-left me-1"></i> Back
                    </button>
                    <button class="btn btn-primary px-4" id="btnStartScan">
                        <i class="fas fa-fingerprint me-1"></i> Start Enrollment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Scanning in progress --}}
    <div id="biometricScanning" style="display:none;">
        <div class="card card-outline card-primary mb-3">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-fingerprint fa-5x text-primary" id="scanIcon"></i>
                </div>
                <h4 class="mb-1">Scanning Fingerprint…</h4>
                <p class="text-muted mb-4">Place your selected finger firmly on the scanner.</p>
                <div class="mx-auto" style="max-width:400px;">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Progress</small>
                        <small class="text-primary" id="scanProgressLabel">0 / 3 scans</small>
                    </div>
                    <div class="progress" style="height:12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             id="scanProgressBar" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Success state --}}
    <div id="biometricSuccess" style="display:none;">
        <div class="card card-outline card-primary mb-3">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle fa-5x text-primary"></i>
                </div>
                <h4 class="mb-1">Enrollment Successful!</h4>
                <p class="text-muted">Biometric data has been captured. Finalizing registration…</p>
            </div>
        </div>
    </div>

    {{-- Error state --}}
    <div id="biometricError" style="display:none;">
        <div class="card card-outline card-secondary mb-3">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-times-circle fa-5x text-secondary"></i>
                </div>
                <h4 class="mb-1">Enrollment Failed</h4>
                <p class="text-muted mb-4">Please try again or choose a different finger.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-secondary px-4" id="btnRetryBiometric">
                        <i class="fas fa-redo me-1"></i> Try Again
                    </button>
                    <button class="btn btn-primary px-4" id="btnBackToInfoFromError">
                        <i class="fas fa-chevron-left me-1"></i> Back to Info
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
{{-- /step2Panel --}}


{{-- ============================================================ --}}
{{-- STEP 3: Complete --}}
{{-- ============================================================ --}}
<div id="step3Panel" style="display:none;">
    <div class="card card-outline card-primary mb-3">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-user-check fa-5x text-primary"></i>
            </div>
            <h3 class="mb-2">Registration Complete!</h3>
            <p class="text-muted mb-1">
                <strong id="completeName"></strong> has been successfully registered.
            </p>
            <p class="text-muted mb-4">
                Employee ID: <strong id="completeId" class="text-primary"></strong>
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="#" class="btn btn-secondary px-4">
                    <i class="fas fa-list me-1"></i> View Employee List
                </a>
                <button class="btn btn-primary px-4" id="btnAddAnother">
                    <i class="fas fa-plus me-1"></i> Add Another Employee
                </button>
            </div>
        </div>
    </div>
</div>
{{-- /step3Panel --}}

@endsection


@push('scripts')
<script>
// ============================================================
// SAMPLE DATA — replace with AJAX calls to your API routes
// ============================================================
const DATA = {
    departments: [
        { id: 1, name: 'Operations',       head: 'Rodrigo Marasigan' },
        { id: 2, name: 'Human Resources',  head: 'Maria Santos' },
        { id: 3, name: 'Finance',          head: 'Eduardo Reyes' },
        { id: 4, name: 'Information Technology', head: 'Carlo Buenaventura' },
        { id: 5, name: 'Sales & Marketing', head: 'Anna Lim' },
        { id: 6, name: 'Logistics',        head: 'Ramon Torres' },
    ],
    positions: {
        'Operations':              ['Production Operator', 'Line Supervisor', 'Quality Inspector'],
        'Human Resources':        ['HR Officer', 'Recruitment Specialist', 'HR Assistant'],
        'Finance':                 ['Accountant', 'Bookkeeper', 'Finance Analyst', 'Payroll Officer'],
        'Information Technology': ['Systems Developer', 'Network Administrator', 'IT Support'],
        'Sales & Marketing':       ['Sales Representative', 'Marketing Officer', 'Account Manager'],
        'Logistics':               ['Logistics Coordinator', 'Warehouse Staff', 'Driver'],
    },
    branches: [
        'Meycauayan Main',
        'Valenzuela Branch',
        'Caloocan Branch',
        'Malabon Branch',
    ],
    banks: [
        'BDO Unibank',
        'Bank of the Philippine Islands (BPI)',
        'Metrobank',
        'Land Bank of the Philippines',
        'UnionBank',
        'Philippine National Bank (PNB)',
        'Security Bank',
        'EastWest Bank',
    ],
    // Simplified PH address data (Region → Provinces → Cities)
    regions: [
        { id: 'NCR',  name: 'NCR – National Capital Region' },
        { id: 'III',  name: 'Region III – Central Luzon' },
        { id: 'IV-A', name: 'Region IV-A – CALABARZON' },
        { id: 'VII',  name: 'Region VII – Central Visayas' },
        { id: 'XI',   name: 'Region XI – Davao Region' },
    ],
    provinces: {
        'NCR':  [{ id: 'ncr_mm', name: 'Metro Manila' }],
        'III':  [{ id: 'bul', name: 'Bulacan' }, { id: 'pam', name: 'Pampanga' }, { id: 'zam', name: 'Zambales' }],
        'IV-A': [{ id: 'cav', name: 'Cavite' }, { id: 'lag', name: 'Laguna' }, { id: 'riz', name: 'Rizal' }],
        'VII':  [{ id: 'ceb', name: 'Cebu' }, { id: 'boh', name: 'Bohol' }],
        'XI':   [{ id: 'dav', name: 'Davao del Sur' }, { id: 'com', name: 'Compostela Valley' }],
    },
    cities: {
        'ncr_mm': ['Caloocan', 'Las Piñas', 'Makati', 'Malabon', 'Mandaluyong', 'Manila', 'Marikina', 'Muntinlupa', 'Navotas', 'Parañaque', 'Pasay', 'Pasig', 'Pateros', 'Quezon City', 'San Juan', 'Taguig', 'Valenzuela'],
        'bul':    ['Meycauayan', 'Malolos', 'Marilao', 'Bulacan', 'Bocaue', 'Balagtas', 'Guiguinto'],
        'pam':    ['Angeles City', 'San Fernando', 'Mabalacat', 'Clark'],
        'zam':    ['Olongapo City', 'Iba', 'San Antonio'],
        'cav':    ['Bacoor', 'Dasmariñas', 'General Trias', 'Imus', 'Tagaytay'],
        'lag':    ['Calamba', 'San Pedro', 'Biñan', 'Santa Rosa'],
        'riz':    ['Antipolo', 'Cainta', 'Taytay', 'Angono'],
        'ceb':    ['Cebu City', 'Mandaue', 'Lapu-Lapu', 'Talisay'],
        'boh':    ['Tagbilaran', 'Panglao'],
        'dav':    ['Davao City', 'Digos', 'Sta. Cruz'],
        'com':    ['Nabunturan', 'Monkayo'],
    },
    fingers: [
        { id: 'left-pinky',  label: 'L. Pinky' },
        { id: 'left-ring',   label: 'L. Ring' },
        { id: 'left-middle', label: 'L. Middle' },
        { id: 'left-index',  label: 'L. Index' },
        { id: 'left-thumb',  label: 'L. Thumb' },
        { id: 'right-thumb', label: 'R. Thumb' },
        { id: 'right-index', label: 'R. Index' },
        { id: 'right-middle',label: 'R. Middle' },
        { id: 'right-ring',  label: 'R. Ring' },
        { id: 'right-pinky', label: 'R. Pinky' },
    ],
    // Existing employees — used to determine next ID
    existingEmployees: [
        { id: 'U001', name: 'Maria Santos' },
        { id: 'U002', name: 'Eduardo Reyes' },
        { id: 'U003', name: 'Carlo Buenaventura' },
        { id: 'U004', name: 'Anna Lim' },
        { id: 'U005', name: 'Ramon Torres' },
    ],
};

// ============================================================
// STATE
// ============================================================
const state = {
    currentStep: 1,
    selectedFinger: 'right-index',
    scansCompleted: 0,
    totalScans: 3,
    scanInterval: null,
};

// ============================================================
// HELPERS
// ============================================================
function generateNextEmployeeId() {
    const nums = DATA.existingEmployees
        .map(e => e.id.replace('U', ''))
        .map(n => parseInt(n, 10))
        .filter(n => !isNaN(n));
    const max = nums.length ? Math.max(...nums) : 0;
    return 'U' + String(max + 1).padStart(3, '0');
}

function getTodayString() {
    return new Date().toISOString().split('T')[0];
}

function calcAge(dob) {
    const d = new Date(dob), t = new Date();
    let age = t.getFullYear() - d.getFullYear();
    const m = t.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && t.getDate() < d.getDate())) age--;
    return age;
}

function addRegularizationMonths(dateStr, months) {
    const d = new Date(dateStr);
    d.setMonth(d.getMonth() + months);
    return d.toISOString().split('T')[0];
}

function populateSelect(selectId, options, valueFn, labelFn, placeholder) {
    const el = document.getElementById(selectId);
    el.innerHTML = `<option value="">${placeholder}</option>`;
    options.forEach(opt => {
        const o = document.createElement('option');
        o.value = valueFn(opt);
        o.textContent = labelFn(opt);
        el.appendChild(o);
    });
}

// ============================================================
// POPULATE DROPDOWNS
// ============================================================
function initDropdowns() {
    // Departments
    populateSelect('fDepartment', DATA.departments,
        d => d.name, d => d.name, 'Select Department');

    // Branches
    populateSelect('fBranch', DATA.branches,
        b => b, b => b, 'Select Branch');

    // Banks
    populateSelect('fBankName', DATA.banks,
        b => b, b => b, '— None —');

    // Regions
    populateSelect('fRegion', DATA.regions,
        r => r.id, r => r.name, 'Select Region');

    // Employee ID
    document.getElementById('fEmployeeId').value = generateNextEmployeeId();

    // Hire date
    document.getElementById('fHireDate').value = getTodayString();

    // Finger grid
    renderFingerGrid();
}

// ============================================================
// CASCADING DROPDOWNS: Department → Position & Supervisor
// ============================================================
document.getElementById('fDepartment').addEventListener('change', function () {
    const dept = this.value;
    const posEl = document.getElementById('fPosition');
    const supEl = document.getElementById('fSupervisor');

    // Positions
    if (dept && DATA.positions[dept]) {
        posEl.disabled = false;
        populateSelect('fPosition', DATA.positions[dept],
            p => p, p => p, 'Select Position');
    } else {
        posEl.disabled = true;
        posEl.innerHTML = '<option value="">Select Department first</option>';
    }

    // Supervisor (Department Head)
    supEl.innerHTML = '<option value="">— None —</option>';
    const found = DATA.departments.find(d => d.name === dept);
    if (found && found.head) {
        const o = document.createElement('option');
        o.value = found.head;
        o.textContent = found.head + ' (Department Head)';
        supEl.appendChild(o);
        supEl.value = found.head;
    }
});

// ============================================================
// CASCADING ADDRESS
// ============================================================
document.getElementById('fRegion').addEventListener('change', function () {
    const regId = this.value;
    const provEl = document.getElementById('fProvince');
    const cityEl = document.getElementById('fCity');
    provEl.disabled = !regId;
    cityEl.disabled = true;
    cityEl.innerHTML = '<option value="">Select Province first</option>';
    if (regId && DATA.provinces[regId]) {
        provEl.disabled = false;
        populateSelect('fProvince', DATA.provinces[regId],
            p => p.id, p => p.name, 'Select Province');
    } else {
        provEl.disabled = true;
        provEl.innerHTML = '<option value="">Select Region first</option>';
    }
    updateCompleteAddress();
});

document.getElementById('fProvince').addEventListener('change', function () {
    const provId = this.value;
    const cityEl = document.getElementById('fCity');
    if (provId && DATA.cities[provId]) {
        cityEl.disabled = false;
        populateSelect('fCity', DATA.cities[provId],
            c => c, c => c, 'Select City / Municipality');
    } else {
        cityEl.disabled = true;
        cityEl.innerHTML = '<option value="">Select Province first</option>';
    }
    updateCompleteAddress();
});

document.getElementById('fCity').addEventListener('change', updateCompleteAddress);
['fBarangay', 'fStreet', 'fZip'].forEach(id =>
    document.getElementById(id).addEventListener('input', updateCompleteAddress)
);

function updateCompleteAddress() {
    const parts = [
        document.getElementById('fStreet').value.trim(),
        document.getElementById('fBarangay').value.trim(),
        document.getElementById('fCity').value.trim(),
        (document.getElementById('fProvince').selectedOptions[0]?.text || '').replace('Select Province first','').trim(),
        (document.getElementById('fRegion').selectedOptions[0]?.text || '').replace('Select Region','').trim(),
        document.getElementById('fZip').value.trim(),
    ].filter(Boolean);
    document.getElementById('fCompleteAddress').value = parts.join(', ');
}

// ============================================================
// AUTO-COMPUTE: Full Name
// ============================================================
['fFirstName','fMiddleName','fLastName','fSuffix'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateFullName);
});
function updateFullName() {
    const parts = [
        document.getElementById('fFirstName').value.trim(),
        document.getElementById('fMiddleName').value.trim(),
        document.getElementById('fLastName').value.trim(),
        document.getElementById('fSuffix').value.trim(),
    ].filter(Boolean);
    document.getElementById('fFullName').value = parts.join(' ');
}

// ============================================================
// AUTO-COMPUTE: Age
// ============================================================
document.getElementById('fDob').addEventListener('change', function () {
    if (this.value) {
        const age = calcAge(this.value);
        document.getElementById('fAge').value = age >= 0 ? age : '';
    } else {
        document.getElementById('fAge').value = '';
    }
});

// ============================================================
// AUTO-COMPUTE: Salary Rates
// ============================================================
document.getElementById('fBasicSalary').addEventListener('input', updateSalaryRates);
function updateSalaryRates() {
    const monthly = parseFloat(document.getElementById('fBasicSalary').value) || 0;
    const daily  = monthly > 0 ? (monthly / 22).toFixed(2) : '';
    const hourly = monthly > 0 ? (monthly / 22 / 8).toFixed(2) : '';
    document.getElementById('fDailyRate').value  = daily;
    document.getElementById('fHourlyRate').value = hourly;
}

// ============================================================
// AUTO-COMPUTE: Leave Credits based on Employment Status
// ============================================================
document.getElementById('fEmploymentStatus').addEventListener('change', updateLeaveCredits);
function updateLeaveCredits() {
    const isRegular = document.getElementById('fEmploymentStatus').value === 'regular';
    const val = isRegular ? 5 : 0;
    document.getElementById('fVacLeave').value   = val;
    document.getElementById('fSickLeave').value  = val;
    document.getElementById('fEmerLeave').value  = val;
    document.getElementById('fLeaveStartDate').value = isRegular ? getTodayString() : '';

    // Regularization date
    const regGroup = document.getElementById('regularizationDateGroup');
    const isProbationary = document.getElementById('fEmploymentStatus').value === 'probationary';
    regGroup.style.display = isProbationary ? 'block' : 'none';
    if (isProbationary) {
        const hd = document.getElementById('fHireDate').value;
        if (hd) document.getElementById('fRegularizationDate').value = addRegularizationMonths(hd, 6);
    }
}

// Init regularization date on load (default = probationary)
document.addEventListener('DOMContentLoaded', () => {
    const hd = getTodayString();
    document.getElementById('fRegularizationDate').value = addRegularizationMonths(hd, 6);
    document.getElementById('regularizationDateGroup').style.display = 'block';
});

// ============================================================
// PASSWORD TOGGLE
// ============================================================
document.getElementById('togglePassword').addEventListener('click', function () {
    const inp = document.getElementById('fPassword');
    const ico = document.getElementById('togglePasswordIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        inp.type = 'password';
        ico.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

// ============================================================
// NAME KEY RESTRICTION (letters, spaces, dots, hyphens, apostrophes)
// ============================================================
['fFirstName','fMiddleName','fLastName'].forEach(id => {
    document.getElementById(id).addEventListener('keydown', function (e) {
        const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
        if (allowed.includes(e.key) || e.ctrlKey || e.metaKey) return;
        if (!/^[a-zA-Z\s.\-']$/.test(e.key)) e.preventDefault();
    });
});

// ============================================================
// VALIDATION
// ============================================================
function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('[id^="err"]').forEach(el => el.textContent = '');
}

function showError(inputId, errId, msg) {
    const inp = document.getElementById(inputId);
    if (inp) inp.classList.add('is-invalid');
    const err = document.getElementById(errId);
    if (err) err.textContent = msg;
}

function validateStep1() {
    clearErrors();
    let valid = true;
    const rules = [
        { field: 'fUsername',    err: 'errUsername',    label: 'Username' },
        { field: 'fPassword',    err: 'errPassword',    label: 'Password' },
        { field: 'fEmail',       err: 'errEmail',       label: 'Email',    type: 'email' },
        { field: 'fDepartment',  err: 'errDepartment',  label: 'Department' },
        { field: 'fPosition',    err: 'errPosition',    label: 'Position' },
        { field: 'fBranch',      err: 'errBranch',      label: 'Branch' },
        { field: 'fFirstName',   err: 'errFirstName',   label: 'First Name' },
        { field: 'fLastName',    err: 'errLastName',    label: 'Last Name' },
        { field: 'fBasicSalary', err: 'errBasicSalary', label: 'Basic Salary', type: 'number' },
    ];

    rules.forEach(r => {
        const el = document.getElementById(r.field);
        const val = el ? el.value.trim() : '';
        if (!val) {
            showError(r.field, r.err, `${r.label} is required.`);
            valid = false;
        } else if (r.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            showError(r.field, r.err, 'Enter a valid email address.');
            valid = false;
        } else if (r.type === 'number' && (isNaN(parseFloat(val)) || parseFloat(val) <= 0)) {
            showError(r.field, r.err, `${r.label} must be greater than 0.`);
            valid = false;
        }
    });
    return valid;
}

// ============================================================
// STEP NAVIGATION
// ============================================================
function goToStep(step) {
    state.currentStep = step;
    document.getElementById('step1Panel').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('step2Panel').style.display = step === 2 ? 'block' : 'none';
    document.getElementById('step3Panel').style.display = step === 3 ? 'block' : 'none';
    updateStepIndicator(step);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateStepIndicator(activeStep) {
    [1, 2, 3].forEach(s => {
        const circle = document.getElementById('stepCircle' + s);
        const label  = document.getElementById('stepLabel' + s);
        circle.className = 'step-circle rounded-circle d-flex align-items-center justify-content-center fw-bold text-white';
        circle.style.cssText = 'width:36px;height:36px;font-size:14px;';

        if (s < activeStep) {
            // completed
            circle.classList.add('bg-primary');
            circle.innerHTML = '<i class="fas fa-check" style="font-size:13px;"></i>';
            label.className = 'fw-semibold text-primary';
        } else if (s === activeStep) {
            circle.classList.add('bg-primary');
            circle.textContent = s;
            label.className = 'fw-semibold text-primary';
        } else {
            circle.classList.add('bg-secondary');
            circle.textContent = s;
            label.className = 'fw-semibold text-secondary';
        }
    });
}

// Step 1 → Step 2
document.getElementById('btnNextBiometric').addEventListener('click', function () {
    if (!validateStep1()) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Form',
            text: 'Please fill in all required fields before proceeding.',
            confirmButtonColor: 'var(--bs-primary)',
        });
        return;
    }
    goToStep(2);
});

// Step 2 Back → Step 1
document.getElementById('btnBackToInfo').addEventListener('click', () => goToStep(1));
document.getElementById('btnBackToInfoFromError').addEventListener('click', () => {
    showBiometricState('idle');
    goToStep(1);
});

// Retry biometric
document.getElementById('btnRetryBiometric').addEventListener('click', () => {
    state.scansCompleted = 0;
    showBiometricState('idle');
});

// Add another
document.getElementById('btnAddAnother').addEventListener('click', () => {
    resetForm();
    goToStep(1);
});

// ============================================================
// BIOMETRIC ENROLLMENT
// ============================================================
function renderFingerGrid() {
    const grid = document.getElementById('fingerGrid');
    grid.innerHTML = '';
    DATA.fingers.forEach(f => {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-2 col-lg-1 text-center';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn w-100 py-3 ' +
            (f.id === state.selectedFinger ? 'btn-primary' : 'btn-outline-secondary');
        btn.setAttribute('data-finger', f.id);
        btn.innerHTML = `<i class="fas fa-fingerprint d-block mb-1" style="font-size:1.4rem;"></i>
                         <small>${f.label}</small>`;
        btn.addEventListener('click', () => selectFinger(f.id));

        col.appendChild(btn);
        grid.appendChild(col);
    });
}

function selectFinger(fingerId) {
    state.selectedFinger = fingerId;
    document.querySelectorAll('#fingerGrid button').forEach(btn => {
        const isActive = btn.getAttribute('data-finger') === fingerId;
        btn.className = 'btn w-100 py-3 ' + (isActive ? 'btn-primary' : 'btn-outline-secondary');
    });
}

function showBiometricState(which) {
    // which: 'idle' | 'scanning' | 'success' | 'error'
    document.getElementById('biometricIdle').style.display    = which === 'idle'     ? 'block' : 'none';
    document.getElementById('biometricScanning').style.display = which === 'scanning' ? 'block' : 'none';
    document.getElementById('biometricSuccess').style.display  = which === 'success'  ? 'block' : 'none';
    document.getElementById('biometricError').style.display    = which === 'error'    ? 'block' : 'none';
}

document.getElementById('btnStartScan').addEventListener('click', startBiometricEnrollment);

function startBiometricEnrollment() {
    state.scansCompleted = 0;
    showBiometricState('scanning');
    updateScanProgress(0);

    state.scanInterval = setInterval(() => {
        state.scansCompleted++;
        updateScanProgress(state.scansCompleted);

        if (state.scansCompleted >= state.totalScans) {
            clearInterval(state.scanInterval);
            showBiometricState('success');
            setTimeout(() => completeRegistration(), 1500);
        }
    }, 2000);
}

function updateScanProgress(scans) {
    const pct = (scans / state.totalScans) * 100;
    document.getElementById('scanProgressBar').style.width = pct + '%';
    document.getElementById('scanProgressLabel').textContent = `${scans} / ${state.totalScans} scans`;
}

// ============================================================
// COMPLETE REGISTRATION
// ============================================================
function completeRegistration() {
    const fullName   = document.getElementById('fFullName').value ||
                       (document.getElementById('fFirstName').value + ' ' + document.getElementById('fLastName').value).trim();
    const employeeId = document.getElementById('fEmployeeId').value;

    document.getElementById('completeName').textContent = fullName;
    document.getElementById('completeId').textContent   = employeeId;

    goToStep(3);
}

// ============================================================
// RESET FORM
// ============================================================
function resetForm() {
    document.querySelectorAll('#step1Panel input, #step1Panel select, #step1Panel textarea')
        .forEach(el => {
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else if (el.type !== 'checkbox') el.value = '';
        });
    clearErrors();
    // Re-init defaults
    document.getElementById('fEmployeeId').value  = generateNextEmployeeId();
    document.getElementById('fHireDate').value    = getTodayString();
    document.getElementById('fNationality').value = 'Filipino';
    document.getElementById('fGender').value      = 'Male';
    document.getElementById('fCivilStatus').value = 'Single';
    document.getElementById('fPayrollFrequency').value = 'semi-monthly';
    document.getElementById('fPaymentMethod').value   = 'Bank Transfer';
    document.getElementById('fEmploymentStatus').value = 'probationary';
    document.getElementById('fRole').value          = 'employee';
    document.getElementById('fVacLeave').value      = 0;
    document.getElementById('fSickLeave').value     = 0;
    document.getElementById('fEmerLeave').value     = 0;

    // Re-enable position
    const posEl = document.getElementById('fPosition');
    posEl.disabled = true;
    posEl.innerHTML = '<option value="">Select Department first</option>';

    // Re-disable address cascades
    document.getElementById('fProvince').disabled = true;
    document.getElementById('fCity').disabled     = true;

    // Regularization
    document.getElementById('regularizationDateGroup').style.display = 'block';
    document.getElementById('fRegularizationDate').value = addRegularizationMonths(getTodayString(), 6);

    // Biometric state
    state.selectedFinger = 'right-index';
    state.scansCompleted = 0;
    showBiometricState('idle');
    renderFingerGrid();
}

// ============================================================
// BOOTSTRAP TAB: scroll to top on switch
// ============================================================
document.querySelectorAll('#detailTabs .nav-link').forEach(tab => {
    tab.addEventListener('shown.bs.tab', () => {
        document.getElementById('step1Panel')
            .scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

// ============================================================
// INIT
// ============================================================
initDropdowns();
goToStep(1);
</script>
@endpush