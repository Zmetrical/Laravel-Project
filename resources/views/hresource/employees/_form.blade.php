{{--
    resources/views/hresource/employees/_form.blade.php
    Shared by create.blade.php and edit.blade.php
    Expects: $employee (nullable), $scheduleTemplates, $action, $method
--}}

<form action="{{ $action }}" method="POST">
    @csrf
    @method($method)

    {{-- Nav Tabs --}}
    <ul class="nav nav-tabs mb-4" id="emp-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $errors->hasAny(['firstName','middleName','lastName','fullName','gender','civilStatus','dateOfBirth','email','phoneNumber']) ? 'text-danger' : '' }} active"
               data-bs-toggle="tab" href="#tab-personal">
                Personal
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $errors->hasAny(['department','position','branch','employmentStatus','role','hireDate','basicSalary','username','password']) ? 'text-danger' : '' }}"
               data-bs-toggle="tab" href="#tab-employment">
                Employment
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $errors->hasAny(['addressStreet','addressBarangay','addressCity','addressProvince','addressRegion','addressZipCode']) ? 'text-danger' : '' }}"
               data-bs-toggle="tab" href="#tab-address">
                Address
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-schedule">
                Schedule
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ── Personal ──────────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="tab-personal">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">First Name</label>
                    <input type="text" name="firstName"
                           class="form-control form-control-sm @error('firstName') is-invalid @enderror"
                           value="{{ old('firstName', $employee->firstName ?? '') }}">
                    @error('firstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Middle Name</label>
                    <input type="text" name="middleName"
                           class="form-control form-control-sm @error('middleName') is-invalid @enderror"
                           value="{{ old('middleName', $employee->middleName ?? '') }}">
                    @error('middleName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Last Name</label>
                    <input type="text" name="lastName"
                           class="form-control form-control-sm @error('lastName') is-invalid @enderror"
                           value="{{ old('lastName', $employee->lastName ?? '') }}">
                    @error('lastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small text-muted">
                        Full Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="fullName"
                           class="form-control form-control-sm @error('fullName') is-invalid @enderror"
                           value="{{ old('fullName', $employee->fullName ?? '') }}">
                    @error('fullName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Gender</label>
                    <select name="gender"
                            class="form-select form-select-sm @error('gender') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach(['Male','Female','Other'] as $g)
                            <option value="{{ $g }}"
                                {{ old('gender', $employee->gender ?? '') === $g ? 'selected' : '' }}>
                                {{ $g }}
                            </option>
                        @endforeach
                    </select>
                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Civil Status</label>
                    <select name="civilStatus"
                            class="form-select form-select-sm @error('civilStatus') is-invalid @enderror">
                        <option value="">— Select —</option>
                        @foreach(['Single','Married','Widowed','Separated'] as $cs)
                            <option value="{{ $cs }}"
                                {{ old('civilStatus', $employee->civilStatus ?? '') === $cs ? 'selected' : '' }}>
                                {{ $cs }}
                            </option>
                        @endforeach
                    </select>
                    @error('civilStatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted">Date of Birth</label>
                    <input type="date" name="dateOfBirth"
                           class="form-control form-control-sm @error('dateOfBirth') is-invalid @enderror"
                           value="{{ old('dateOfBirth', isset($employee->dateOfBirth) ? $employee->dateOfBirth->format('Y-m-d') : '') }}">
                    @error('dateOfBirth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted">Email</label>
                    <input type="email" name="email"
                           class="form-control form-control-sm @error('email') is-invalid @enderror"
                           value="{{ old('email', $employee->email ?? '') }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Phone Number</label>
                    <input type="text" name="phoneNumber"
                           class="form-control form-control-sm @error('phoneNumber') is-invalid @enderror"
                           value="{{ old('phoneNumber', $employee->phoneNumber ?? '') }}">
                    @error('phoneNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- ── Employment ─────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tab-employment">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Department</label>
                    <input type="text" name="department" list="dept-list"
                           class="form-control form-control-sm @error('department') is-invalid @enderror"
                           value="{{ old('department', $employee->department ?? '') }}">
                    <datalist id="dept-list"></datalist>
                    @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Position</label>
                    <input type="text" name="position" list="pos-list"
                           class="form-control form-control-sm @error('position') is-invalid @enderror"
                           value="{{ old('position', $employee->position ?? '') }}">
                    <datalist id="pos-list"></datalist>
                    @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">
                        Branch <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="branch" list="branch-list"
                           class="form-control form-control-sm @error('branch') is-invalid @enderror"
                           value="{{ old('branch', $employee->branch ?? '') }}">
                    <datalist id="branch-list"></datalist>
                    @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted">
                        Employment Status <span class="text-danger">*</span>
                    </label>
                    <select name="employmentStatus"
                            class="form-select form-select-sm @error('employmentStatus') is-invalid @enderror">
                        @foreach(['probationary','regular','resigned','terminated'] as $s)
                            <option value="{{ $s }}"
                                {{ old('employmentStatus', $employee->employmentStatus ?? 'probationary') === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                    @error('employmentStatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">
                        Role <span class="text-danger">*</span>
                    </label>
                    <select name="role"
                            class="form-select form-select-sm @error('role') is-invalid @enderror">
                        @foreach(['employee','hr','accounting','admin'] as $r)
                            <option value="{{ $r }}"
                                {{ old('role', $employee->role ?? 'employee') === $r ? 'selected' : '' }}>
                                {{ ucfirst($r) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Hire Date</label>
                    <input type="date" name="hireDate"
                           class="form-control form-control-sm @error('hireDate') is-invalid @enderror"
                           value="{{ old('hireDate', isset($employee->hireDate) ? $employee->hireDate->format('Y-m-d') : '') }}">
                    @error('hireDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">
                        Monthly Basic Salary (₱) <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="basicSalary" id="f-basicSalary"
                           class="form-control form-control-sm @error('basicSalary') is-invalid @enderror"
                           min="0" step="0.01" placeholder="0.00"
                           value="{{ old('basicSalary', $employee->basicSalary ?? '') }}">
                    @error('basicSalary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <div class="row text-muted small mt-1">
                        <div class="col-6">Daily Rate: <strong id="f-daily-preview">—</strong></div>
                        <div class="col-6">Hourly Rate: <strong id="f-hourly-preview">—</strong></div>
                    </div>
                </div>

                {{-- Only shown on create --}}
                @if(!isset($employee))
                <div class="col-md-4">
                    <label class="form-label small text-muted">Username</label>
                    <input type="text" name="username"
                           class="form-control form-control-sm @error('username') is-invalid @enderror"
                           placeholder="Auto-generated if blank"
                           value="{{ old('username') }}">
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Password</label>
                    <input type="password" name="password"
                           class="form-control form-control-sm @error('password') is-invalid @enderror"
                           placeholder="Default: password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @endif
            </div>
        </div>

        {{-- ── Address ─────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tab-address">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted">Street / House No.</label>
                    <input type="text" name="addressStreet"
                           class="form-control form-control-sm @error('addressStreet') is-invalid @enderror"
                           value="{{ old('addressStreet', $employee->addressStreet ?? '') }}">
                    @error('addressStreet') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted">Barangay</label>
                    <input type="text" name="addressBarangay"
                           class="form-control form-control-sm @error('addressBarangay') is-invalid @enderror"
                           value="{{ old('addressBarangay', $employee->addressBarangay ?? '') }}">
                    @error('addressBarangay') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">City / Municipality</label>
                    <input type="text" name="addressCity"
                           class="form-control form-control-sm @error('addressCity') is-invalid @enderror"
                           value="{{ old('addressCity', $employee->addressCity ?? '') }}">
                    @error('addressCity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Province</label>
                    <input type="text" name="addressProvince"
                           class="form-control form-control-sm @error('addressProvince') is-invalid @enderror"
                           value="{{ old('addressProvince', $employee->addressProvince ?? '') }}">
                    @error('addressProvince') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Region</label>
                    <input type="text" name="addressRegion"
                           class="form-control form-control-sm @error('addressRegion') is-invalid @enderror"
                           value="{{ old('addressRegion', $employee->addressRegion ?? '') }}">
                    @error('addressRegion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Zip Code</label>
                    <input type="text" name="addressZipCode"
                           class="form-control form-control-sm @error('addressZipCode') is-invalid @enderror"
                           value="{{ old('addressZipCode', $employee->addressZipCode ?? '') }}">
                    @error('addressZipCode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- ── Schedule ─────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tab-schedule">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small text-muted">Schedule Template</label>
                    <select name="template_id" id="f-template-id"
                            class="form-select form-select-sm">
                        <option value="">— No Schedule —</option>
                        @foreach($scheduleTemplates as $tpl)
                            <option value="{{ $tpl->id }}"
                                {{ old('template_id', $employee->currentSchedule->template_id ?? '') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Effective Date</label>
                    <input type="date" name="effective_date" id="f-effective-date"
                           class="form-control form-control-sm"
                           value="{{ old('effective_date', isset($employee->currentSchedule) ? $employee->currentSchedule->effective_date->format('Y-m-d') : date('Y-m-d')) }}">
                </div>
            </div>

            <div id="schedule-preview" class="mt-3 d-none">
                <p class="text-muted small fw-semibold text-uppercase mb-2">Preview</p>
                <div class="d-flex gap-2 flex-wrap" id="schedule-days"></div>
            </div>
        </div>

    </div>{{-- /tab-content --}}

    {{-- Footer Buttons --}}
    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <a href="{{ route('hresource.employees.index') }}"
           class="btn btn-secondary btn-sm">
            Cancel
        </a>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-floppy me-1"></i>
            {{ isset($employee) ? 'Save Changes' : 'Create Employee' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const peso = n => '₱' + parseFloat(n || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
    });

    const scheduleTemplates = @json($scheduleTemplates);

    // ── Salary preview ──────────────────────────────────────
    const salaryInput = document.getElementById('f-basicSalary');
    const dailyPrev   = document.getElementById('f-daily-preview');
    const hourlyPrev  = document.getElementById('f-hourly-preview');

    function refreshPreview(val) {
        const n = parseFloat(val);
        const ok = !isNaN(n) && n > 0;
        dailyPrev.textContent  = ok ? peso(n / 26)     : '—';
        hourlyPrev.textContent = ok ? peso(n / 26 / 8) : '—';
    }

    if (salaryInput) {
        salaryInput.addEventListener('input', e => refreshPreview(e.target.value));
        refreshPreview(salaryInput.value);  // on edit, seed the preview
    }

    // ── Schedule preview ─────────────────────────────────────
    const templateSel  = document.getElementById('f-template-id');
    const previewWrap  = document.getElementById('schedule-preview');
    const previewDays  = document.getElementById('schedule-days');
    const dayNames     = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    function renderSchedulePreview(templateId) {
        const tpl = scheduleTemplates.find(t => t.id == templateId);
        if (!tpl) { previewWrap.classList.add('d-none'); return; }

        previewDays.innerHTML = tpl.days.map(d =>
            `<span class="badge ${d.is_working_day
                ? 'bg-primary bg-opacity-10 text-primary'
                : 'bg-secondary bg-opacity-10 text-secondary'}">
                ${dayNames[d.day_of_week]}
                ${d.is_working_day && d.shift_in
                    ? `<small class="d-block text-muted">
                        ${d.shift_in.slice(0,5)}–${(d.shift_out ?? '').slice(0,5)}
                       </small>`
                    : ''}
            </span>`
        ).join('');

        previewWrap.classList.remove('d-none');
    }

    if (templateSel) {
        templateSel.addEventListener('change', e => renderSchedulePreview(e.target.value));
        // seed preview on edit page
        if (templateSel.value) renderSchedulePreview(templateSel.value);
    }

    // ── Datalists: fill dept/pos/branch from existing employees ──
    fetch('{{ route('hresource.employees.list') }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(employees => {
        const unique = (key) =>
            [...new Set(employees.map(e => e[key]).filter(Boolean))].sort();

        const fill = (id, items) => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = items.map(v => `<option value="${v}">`).join('');
        };

        fill('dept-list',   unique('department'));
        fill('pos-list',    unique('position'));
        fill('branch-list', unique('branch'));
    })
    .catch(() => {});

    // ── Auto-open errored tab on validation failure ──────────
    const tabLinks = document.querySelectorAll('#emp-tabs .nav-link');
    const firstErrorTab = document.querySelector('#emp-tabs .nav-link.text-danger');
    if (firstErrorTab) firstErrorTab.click();
})();
</script>
@endpush