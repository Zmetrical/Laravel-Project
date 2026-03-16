@extends('layouts.main')

@section('title', 'Process Payroll – ' . $period->label)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('accounting.payroll.periods.index') }}">Payroll Periods</a></li>
        <li class="breadcrumb-item active">Process – {{ $period->label }}</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">Process Payroll — {{ $period->label }}</h4>
        <p class="text-muted mb-0">
            Pay Date: {{ $period->pay_date->format('F d, Y') }}
            &mdash;
            <span id="progress-label">{{ $savedCount }} of {{ $totalEmployees }} saved</span>
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-sm btn-outline-secondary" id="btn-save-all">
            Save All
        </button>
        <button class="btn btn-sm btn-secondary" id="btn-release-all">
            Release Payroll
        </button>
    </div>
</div>

{{-- Progress bar --}}
<div class="progress mb-4" style="height:6px">
    <div class="progress-bar"
         id="progress-bar"
         role="progressbar"
         style="width: {{ $totalEmployees > 0 ? round(($savedCount / $totalEmployees) * 100) : 0 }}%">
    </div>
</div>

<div class="row g-3">

    {{-- ===== LEFT: EMPLOYEE LIST ===== --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        Employees
                        <span class="badge badge-secondary ml-1" id="emp-count">{{ $totalEmployees }}</span>
                    </h6>
                </div>
            </div>
            <div class="card-body pb-2">
                <input type="text" id="emp-search"
                       class="form-control form-control-sm mb-2"
                       placeholder="Search name or position…">
                <select id="dept-filter" class="form-control form-control-sm">
                    <option value="all">All Departments</option>
                    @foreach ($employees->pluck('department')->filter()->unique()->sort() as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-auto" style="max-height:560px" id="employee-list">
                @foreach ($employees as $emp)
                    @php $recordStatus = $processedIds[$emp->id] ?? null; @endphp
                    <div class="list-group-item list-group-item-action border-0 border-bottom px-3 py-2
                                d-flex align-items-center gap-2 employee-item"
                         data-id="{{ $emp->id }}"
                         data-name="{{ strtolower($emp->fullName) }}"
                         data-position="{{ strtolower($emp->position) }}"
                         data-dept="{{ $emp->department }}"
                         role="button">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="font-weight-semibold small text-truncate">{{ $emp->fullName }}</span>
                                <span class="record-badge badge ml-1 flex-shrink-0"
                                      style="font-size:.6rem"
                                      data-default="{{ $recordStatus }}">
                                    @if ($recordStatus === 'released')
                                        <span class="badge badge-success">Released</span>
                                    @elseif ($recordStatus === 'draft')
                                        <span class="badge badge-secondary">Saved</span>
                                    @endif
                                </span>
                            </div>
                            <div class="text-muted" style="font-size:.72rem">{{ $emp->position }}</div>
                            <div class="text-muted" style="font-size:.68rem">{{ $emp->department }}</div>
                        </div>
                        <i class="fas fa-chevron-right text-muted small"></i>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== RIGHT: DETAIL PANEL ===== --}}
    <div class="col-lg-8" id="panel-detail" style="display:none">

        {{-- Employee Info --}}
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-0" id="detail-name"></h5>
                        <div class="text-muted small" id="detail-meta"></div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted" style="font-size:.72rem">Monthly Salary</div>
                        <div class="font-weight-bold" id="detail-salary"></div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="border rounded py-2 px-1">
                            <div class="text-muted" style="font-size:.68rem">Employee ID</div>
                            <div class="font-weight-semibold small" id="detail-id"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded py-2 px-1">
                            <div class="text-muted" style="font-size:.68rem">Daily Rate</div>
                            <div class="font-weight-semibold small" id="detail-daily-rate"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded py-2 px-1">
                            <div class="text-muted" style="font-size:.68rem">Hourly Rate</div>
                            <div class="font-weight-semibold small" id="detail-hourly-rate"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DTR Card --}}
        <div class="card mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Daily Time Record</h6>
                <span class="badge badge-secondary" id="dtr-count">0 records</span>
            </div>

            <div id="dtr-loading" class="text-center py-4 d-none">
                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                <div class="text-muted small mt-1">Loading records…</div>
            </div>

            <div id="dtr-summary" class="border-bottom px-3 py-2 d-none">
                <div class="row g-2 text-center">
                    <div class="col-3">
                        <div class="text-muted" style="font-size:.68rem">Work Days</div>
                        <div class="font-weight-semibold small" id="stat-workdays">—</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted" style="font-size:.68rem">Absent</div>
                        <div class="font-weight-semibold small" id="stat-absent">—</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted" style="font-size:.68rem">Late (min)</div>
                        <div class="font-weight-semibold small" id="stat-late">—</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted" style="font-size:.68rem">Undertime</div>
                        <div class="font-weight-semibold small" id="stat-ut">—</div>
                    </div>
                </div>
            </div>

            <div class="overflow-auto" style="max-height:320px" id="dtr-records">
                <div class="text-center text-muted py-5" id="dtr-placeholder">
                    Select an employee to view records
                </div>
            </div>
        </div>

        {{-- Payroll Computation Card --}}
        <div class="card" id="card-payroll" style="display:none">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Payroll Computation</h6>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge badge-secondary small" id="record-status-badge">Not Saved</span>
                    <button class="btn btn-sm btn-outline-secondary" id="btn-save-record">
                        Save Record
                    </button>
                </div>
            </div>
            <div class="card-body">

                {{-- Deferred balance notice --}}
                <div class="callout callout-warning d-none mb-3" id="deferred-notice">
                    <p class="mb-0 small">
                        Deferred balance of <strong id="deferred-amount"></strong> from the previous
                        period is included in deductions.
                    </p>
                </div>

                <div class="row g-3">
                    {{-- Earnings --}}
                    <div class="col-md-6">
                        <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">Earnings</p>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody id="earnings-body"></tbody>
                            <tfoot>
                                <tr class="border-top font-weight-bold">
                                    <td>Gross Pay</td>
                                    <td class="text-right" id="summary-gross"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Deductions --}}
                    <div class="col-md-6">
                        <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">Deductions</p>
                        <table class="table table-sm table-borderless mb-0">
                            <tbody id="deductions-body"></tbody>
                            <tfoot>
                                <tr class="border-top font-weight-bold">
                                    <td>Total Deductions</td>
                                    <td class="text-right" id="summary-deductions"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Loan Deductions (own row, full width) --}}
                <div id="loans-section" class="mt-3 d-none">
                    <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">Loan Deductions</p>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody id="loans-body"></tbody>
                        <tfoot>
                            <tr class="border-top font-weight-bold">
                                <td>Loans Subtotal</td>
                                <td class="text-right" id="summary-loans"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Net Pay --}}
                <div class="callout callout-info mt-3 mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Net Pay</h6>
                        <h4 class="mb-0 font-weight-bold" id="summary-net"></h4>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Empty state --}}
    <div class="col-lg-8 d-flex align-items-center justify-content-center" id="panel-empty">
        <div class="text-center text-muted py-5">
            <i class="fas fa-user fa-2x mb-2 d-block"></i>
            <div>Select an employee to view their DTR and compute payroll</div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {

    // ── Config ───────────────────────────────────────────────────────────────
    const BASE_URL = '{{ url("/accounting/payroll/periods/{$period->id}/process") }}';
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let SAVED      = {{ $savedCount }};
    const TOTAL    = {{ $totalEmployees }};

    // ── State ────────────────────────────────────────────────────────────────
    let activeEmployeeId = null;

    // ── Helpers ──────────────────────────────────────────────────────────────
    const peso = n => '₱' + Number(n || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    function tableRow(label, value, sub = null) {
        return `<tr>
            <td class="text-muted small">
                ${esc(label)}
                ${sub ? `<div style="font-size:.7rem" class="text-muted">${esc(sub)}</div>` : ''}
            </td>
            <td class="text-right small">${peso(value)}</td>
        </tr>`;
    }

    function esc(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str ?? ''));
        return d.innerHTML;
    }

    function updateProgress(delta = 0) {
        SAVED = Math.max(0, Math.min(TOTAL, SAVED + delta));
        const pct = TOTAL > 0 ? Math.round((SAVED / TOTAL) * 100) : 0;
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-label').textContent = `${SAVED} of ${TOTAL} saved`;
    }

    // ── Employee filter ──────────────────────────────────────────────────────
    function filterList() {
        const q    = document.getElementById('emp-search').value.toLowerCase();
        const dept = document.getElementById('dept-filter').value;
        let visible = 0;

        document.querySelectorAll('.employee-item').forEach(el => {
            const match = (!q || el.dataset.name.includes(q) || el.dataset.position.includes(q))
                       && (dept === 'all' || el.dataset.dept === dept);
            el.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        document.getElementById('emp-count').textContent = visible;
    }

    // ── Select employee ──────────────────────────────────────────────────────
    function selectEmployee(id) {
        if (activeEmployeeId === id) return;
        activeEmployeeId = id;

        document.querySelectorAll('.employee-item').forEach(el =>
            el.classList.toggle('active', el.dataset.id === id)
        );

        document.getElementById('panel-detail').style.display = '';
        document.getElementById('panel-empty').style.display  = 'none';
        document.getElementById('dtr-loading').classList.remove('d-none');
        document.getElementById('dtr-records').innerHTML = '';
        document.getElementById('dtr-summary').classList.add('d-none');
        document.getElementById('card-payroll').style.display = 'none';
        document.getElementById('dtr-count').textContent = '0 records';

        fetch(`${BASE_URL}/${id}/data`, {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('dtr-loading').classList.add('d-none');
            renderEmployeeInfo(data.employee);
            renderDTR(data.attendance, data.meta);
            renderPayroll(data.computed, data.meta, data.existing);
        })
        .catch(() => {
            document.getElementById('dtr-loading').classList.add('d-none');
            document.getElementById('dtr-records').innerHTML =
                `<div class="text-center text-muted py-4">Failed to load data.</div>`;
        });
    }

    // ── Employee info ─────────────────────────────────────────────────────────
    function renderEmployeeInfo(emp) {
        document.getElementById('detail-name').textContent      = emp.fullName;
        document.getElementById('detail-meta').textContent      = `${emp.position} · ${emp.department} · ${emp.employmentStatus}`;
        document.getElementById('detail-salary').textContent    = peso(emp.basicSalary);
        document.getElementById('detail-id').textContent        = emp.id;
        document.getElementById('detail-daily-rate').textContent  = peso(emp.dailyRate);
        document.getElementById('detail-hourly-rate').textContent = peso(emp.hourlyRate);
    }

    // ── DTR ───────────────────────────────────────────────────────────────────
    function renderDTR(records, meta) {
        document.getElementById('dtr-count').textContent =
            `${records.length} record${records.length !== 1 ? 's' : ''}`;

        document.getElementById('stat-workdays').textContent = meta.work_days ?? '—';
        document.getElementById('stat-absent').textContent   = meta.absent_days ?? '—';
        document.getElementById('stat-late').textContent     = meta.late_minutes ?? '—';
        document.getElementById('stat-ut').textContent       = meta.ut_minutes ?? '—';
        document.getElementById('dtr-summary').classList.remove('d-none');

        if (!records.length) {
            document.getElementById('dtr-records').innerHTML =
                `<div class="text-center text-muted py-4">No attendance records for this period</div>`;
            return;
        }

        const STATUS_BADGE = {
            present    : '',
            absent     : '<span class="badge badge-secondary">Absent</span>',
            late       : '<span class="badge badge-warning">Late</span>',
            half_day   : '<span class="badge badge-secondary">Half Day</span>',
            leave      : '<span class="badge badge-secondary">Leave</span>',
            holiday    : '<span class="badge badge-secondary">Holiday</span>',
            incomplete : '<span class="badge badge-secondary">Incomplete</span>',
            rest_day   : '<span class="badge badge-secondary">Rest Day</span>',
        };

        document.getElementById('dtr-records').innerHTML = records.map(r => {
            const lateBadge = r.late_minutes > 0
                ? `<span class="badge badge-warning ml-1">${r.late_minutes}m late</span>` : '';
            const utBadge   = r.undertime_minutes > 0
                ? `<span class="badge badge-secondary ml-1">${r.undertime_minutes}m UT</span>` : '';
            const bioBadge  = r.is_biometric
                ? `<span class="badge badge-secondary ml-1">Bio</span>` : '';

            return `
            <div class="px-3 py-2 border-bottom d-flex align-items-start gap-3
                        ${r.status === 'absent' ? 'text-muted' : ''}">
                <div style="min-width:80px">
                    <div class="font-weight-semibold small">${r.date}</div>
                    <div class="text-muted" style="font-size:.7rem">${r.day_name}</div>
                </div>
                <div class="flex-grow-1">
                    <div class="mb-1">
                        ${STATUS_BADGE[r.status] ?? ''}${lateBadge}${utBadge}${bioBadge}
                    </div>
                    <div class="d-flex gap-3" style="font-size:.76rem">
                        <span class="text-muted">In: <strong>${r.time_in ?? '—'}</strong></span>
                        <span class="text-muted">Out: <strong>${r.time_out ?? '—'}</strong></span>
                        <span class="text-muted">Hrs: <strong>${r.hours_worked.toFixed(2)}</strong></span>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    // ── Payroll Computation ───────────────────────────────────────────────────
    function renderPayroll(computed, meta, existing) {

        // Deferred balance notice
        const deferredNotice = document.getElementById('deferred-notice');
        if (meta.deferred_from_prev > 0) {
            document.getElementById('deferred-amount').textContent = peso(meta.deferred_from_prev);
            deferredNotice.classList.remove('d-none');
        } else {
            deferredNotice.classList.add('d-none');
        }

        // Earnings
        const earnings = [
            { label: 'Basic Pay',          value: computed.basic_pay,    sub: null },
            { label: 'Overtime Pay',       value: computed.overtime_pay,
              sub: meta.ot_hours > 0 ? `${meta.ot_hours}h approved` : null },
            { label: 'Night Differential', value: computed.night_diff_pay,
              sub: meta.nd_hours > 0 ? `${meta.nd_hours}h × 10%` : null },
            { label: 'Holiday Pay',        value: computed.holiday_pay,  sub: null },
            { label: 'Leave Pay',          value: computed.leave_pay,
              sub: meta.leave_days > 0 ? `${meta.leave_days} day(s)` : null },
            { label: 'Allowances',         value: computed.allowances,   sub: null },
        ].filter(e => e.value > 0);

        document.getElementById('earnings-body').innerHTML =
            earnings.map(e => tableRow(e.label, e.value, e.sub)).join('') ||
            '<tr><td colspan="2" class="text-muted small">No earnings</td></tr>';

        // Deductions
        const deductions = [
            { label: 'SSS',             value: computed.sss },
            { label: 'PhilHealth',      value: computed.philhealth },
            { label: 'Pag-IBIG',        value: computed.pagibig },
            { label: 'Withholding Tax', value: computed.withholding_tax },
            { label: 'Late',            value: computed.late_deductions,
              sub: meta.late_minutes > 0 ? `${meta.late_minutes} min` : null },
            { label: 'Undertime',       value: computed.undertime_deductions,
              sub: meta.ut_minutes > 0 ? `${meta.ut_minutes} min` : null },
            { label: 'Absent',          value: computed.absent_deductions,
              sub: meta.absent_days > 0 ? `${meta.absent_days} day(s)` : null },
            { label: 'Other',           value: computed.other_deductions },
            { label: 'Deferred (prev period)', value: meta.deferred_from_prev },
        ].filter(d => d.value > 0);

        document.getElementById('deductions-body').innerHTML =
            deductions.map(d => tableRow(d.label, d.value, d.sub ?? null)).join('') ||
            '<tr><td colspan="2" class="text-muted small">No deductions</td></tr>';

        // Loan deductions
        const loansSection = document.getElementById('loans-section');
        const loanRows     = meta.loan_deductions ?? [];
        const loanTotal    = loanRows.reduce((s, l) => s + l.amount, 0);

        if (loanRows.length > 0) {
            document.getElementById('loans-body').innerHTML = loanRows.map(l =>
                tableRow(l.label, l.amount,
                    `Balance after: ${peso(l.balance_after)}`)
            ).join('');
            document.getElementById('summary-loans').textContent = peso(loanTotal);
            loansSection.classList.remove('d-none');
        } else {
            loansSection.classList.add('d-none');
        }

        // Totals
        document.getElementById('summary-gross').textContent      = peso(computed.gross_pay);
        document.getElementById('summary-deductions').textContent = peso(computed.total_deductions);
        document.getElementById('summary-net').textContent        = peso(computed.net_pay);

        // Record status badge
        const badge = document.getElementById('record-status-badge');
        if (existing) {
            badge.textContent = existing.status === 'released' ? 'Released' : 'Saved';
            badge.className   = `badge small ${existing.status === 'released' ? 'badge-success' : 'badge-secondary'}`;
        } else {
            badge.textContent = 'Not Saved';
            badge.className   = 'badge badge-secondary small';
        }

        // Disable save for released records
        const saveBtn = document.getElementById('btn-save-record');
        saveBtn.disabled = existing?.status === 'released';

        document.getElementById('card-payroll').style.display = '';
    }

    // ── Save single record ────────────────────────────────────────────────────
    function saveRecord() {
        if (!activeEmployeeId) return;

        const btn = document.getElementById('btn-save-record');
        btn.disabled    = true;
        btn.textContent = 'Saving…';

        fetch(`${BASE_URL}/${activeEmployeeId}/save`, {
            method : 'POST',
            headers: {
                Accept          : 'application/json',
                'Content-Type'  : 'application/json',
                'X-CSRF-TOKEN'  : CSRF,
            },
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled    = false;
            btn.textContent = 'Save Record';

            if (!data.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                return;
            }

            // Update badge on computation card
            const badge = document.getElementById('record-status-badge');
            badge.textContent = 'Saved';
            badge.className   = 'badge badge-secondary small';

            // Update employee list badge
            const empItem = document.querySelector(`.employee-item[data-id="${activeEmployeeId}"]`);
            if (empItem) {
                const badgeSpan = empItem.querySelector('.record-badge');
                if (badgeSpan) {
                    badgeSpan.innerHTML = '<span class="badge badge-secondary">Saved</span>';
                }
            }

            updateProgress(1);

            Swal.fire({
                icon: 'success', title: 'Saved',
                text: data.message,
                timer: 2000, showConfirmButton: false,
                toast: true, position: 'top-end',
            });
        })
        .catch(() => {
            btn.disabled    = false;
            btn.textContent = 'Save Record';
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save. Please try again.' });
        });
    }

    // ── Save All ──────────────────────────────────────────────────────────────
    function saveAll() {
        Swal.fire({
            title             : 'Compute & Save All?',
            text              : 'This will compute and save payroll for all active employees. Already-released records will be skipped.',
            icon              : 'question',
            showCancelButton  : true,
            confirmButtonText : 'Save All',
            confirmButtonColor: '#6c757d',
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById('btn-save-all');
            btn.disabled    = true;
            btn.textContent = 'Saving…';

            fetch(`{{ url("/accounting/payroll/periods/{$period->id}/process/save-all") }}`, {
                method : 'POST',
                headers: {
                    Accept         : 'application/json',
                    'Content-Type' : 'application/json',
                    'X-CSRF-TOKEN' : CSRF,
                },
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled    = false;
                btn.textContent = 'Save All';

                if (data.success) {
                    // Refresh page to reflect updated badges
                    Swal.fire({
                        icon              : 'success',
                        title             : 'Done',
                        text              : data.message,
                        confirmButtonColor: '#6c757d',
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(() => {
                btn.disabled    = false;
                btn.textContent = 'Save All';
                Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed.' });
            });
        });
    }

    // ── Release All ───────────────────────────────────────────────────────────
    function releaseAll() {
        Swal.fire({
            title             : 'Release Payroll?',
            text              : 'All saved (draft) records will be released and employees will be able to view their payslips. This cannot be undone.',
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonText : 'Yes, Release',
            confirmButtonColor: '#6c757d',
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById('btn-release-all');
            btn.disabled    = true;
            btn.textContent = 'Releasing…';

            fetch(`{{ url("/accounting/payroll/periods/{$period->id}/process/release-all") }}`, {
                method : 'POST',
                headers: {
                    Accept         : 'application/json',
                    'Content-Type' : 'application/json',
                    'X-CSRF-TOKEN' : CSRF,
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon              : 'success',
                        title             : 'Payroll Released',
                        text              : data.message,
                        confirmButtonColor: '#6c757d',
                    }).then(() => {
                        if (data.redirect) window.location.href = data.redirect;
                        else window.location.reload();
                    });
                } else {
                    btn.disabled    = false;
                    btn.textContent = 'Release Payroll';
                    Swal.fire({ icon: 'error', title: 'Cannot Release', text: data.message });
                }
            })
            .catch(() => {
                btn.disabled    = false;
                btn.textContent = 'Release Payroll';
                Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed.' });
            });
        });
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    document.getElementById('emp-search').addEventListener('input', filterList);
    document.getElementById('dept-filter').addEventListener('change', filterList);
    document.getElementById('btn-save-record').addEventListener('click', saveRecord);
    document.getElementById('btn-save-all').addEventListener('click', saveAll);
    document.getElementById('btn-release-all').addEventListener('click', releaseAll);

    document.querySelectorAll('.employee-item').forEach(el =>
        el.addEventListener('click', () => selectEmployee(el.dataset.id))
    );

})();
</script>
@endpush