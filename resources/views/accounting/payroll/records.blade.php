@extends('layouts.main')

@section('title', 'Payroll Records — ' . $period->label)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('accounting.payroll.periods.index') }}">Payroll Periods</a></li>
        <li class="breadcrumb-item active">Records — {{ $period->label }}</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">Payroll Records</h4>
        <p class="text-muted mb-0">
            {{ $period->label }}
            &mdash; Pay Date: {{ $period->pay_date->format('F d, Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        @if ($period->isReleased() || $period->isClosed())
            <a href="{{ route('accounting.payroll.periods.summary', $period) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-bar-chart-line me-1"></i> Summary
            </a>
        @endif
        @if ($period->isProcessing())
            <a href="{{ route('accounting.payroll.periods.process', $period) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calculator me-1"></i> Process
            </a>
        @endif
    </div>
</div>

{{-- Totals Strip --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="info-box shadow-none border mb-0">
            <div class="info-box-content">
                <span class="info-box-text">Employees</span>
                <span class="info-box-number">{{ $totals['count'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-none border mb-0">
            <div class="info-box-content">
                <span class="info-box-text">Total Gross Pay</span>
                <span class="info-box-number">₱{{ number_format($totals['gross_pay'], 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-none border mb-0">
            <div class="info-box-content">
                <span class="info-box-text">Total Deductions</span>
                <span class="info-box-number">₱{{ number_format($totals['total_deductions'], 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box shadow-none border mb-0">
            <div class="info-box-content">
                <span class="info-box-text">Total Net Pay</span>
                <span class="info-box-number">₱{{ number_format($totals['net_pay'], 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Records Table --}}
<div class="card">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            Records
            <span class="badge badge-secondary ml-1">{{ $totals['count'] }}</span>
        </h6>
        <div class="d-flex gap-2">
            <input type="text" id="record-search"
                   class="form-control form-control-sm"
                   style="width:200px"
                   placeholder="Search employee…">
            <select id="dept-filter" class="form-control form-control-sm" style="width:160px">
                <option value="">All Departments</option>
                @foreach ($records->pluck('employee.department')->filter()->unique()->sort() as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th class="text-right">Basic Pay</th>
                        <th class="text-right">Gross Pay</th>
                        <th class="text-right">Deductions</th>
                        <th class="text-right">Net Pay</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                    <tr class="record-row"
                        data-name="{{ strtolower($record->employee?->fullName) }}"
                        data-dept="{{ $record->employee?->department }}">
                        <td class="align-middle">
                            <div class="font-weight-semibold small">
                                {{ $record->employee?->fullName ?? 'Unknown' }}
                            </div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ $record->employee?->position }}
                            </div>
                        </td>
                        <td class="align-middle">
                            <small class="text-muted">{{ $record->employee?->department ?? '—' }}</small>
                        </td>
                        <td class="align-middle text-right">
                            <small>₱{{ number_format($record->basic_pay, 2) }}</small>
                        </td>
                        <td class="align-middle text-right">
                            <small>₱{{ number_format($record->gross_pay, 2) }}</small>
                        </td>
                        <td class="align-middle text-right">
                            <small>₱{{ number_format($record->total_deductions, 2) }}</small>
                        </td>
                        <td class="align-middle text-right font-weight-bold">
                            <small>₱{{ number_format($record->net_pay, 2) }}</small>
                        </td>
                        <td class="align-middle text-center">
                            @if ($record->status === 'released')
                                <span class="badge badge-success">Released</span>
                            @else
                                <span class="badge badge-secondary">Draft</span>
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            <button class="btn btn-xs btn-outline-secondary"
                                    title="View Breakdown"
                                    onclick="openBreakdown({{ $record->id }})">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:1.5rem"></i>
                            No records found. Go to the process page to compute payroll.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Breakdown Modal --}}
<div class="modal fade" id="breakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payroll Breakdown</h5>
                <button type="button" class="close" onclick="closeBreakdown()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="breakdown-body">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-secondary"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeBreakdown()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const RECORDS = @json($recordsJson);

// ── Helpers ───────────────────────────────────────────────────────────────────
const peso = n => '₱' + Number(n || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

function trow(label, value) {
    return `<tr>
        <td class="text-muted small">${label}</td>
        <td class="text-right small">${peso(value)}</td>
    </tr>`;
}

// ── Filter ────────────────────────────────────────────────────────────────────
document.getElementById('record-search').addEventListener('input', filterRows);
document.getElementById('dept-filter').addEventListener('change', filterRows);

function filterRows() {
    const q    = document.getElementById('record-search').value.toLowerCase();
    const dept = document.getElementById('dept-filter').value.toLowerCase();

    document.querySelectorAll('.record-row').forEach(row => {
        const matchName = !q    || row.dataset.name.includes(q);
        const matchDept = !dept || row.dataset.dept.toLowerCase() === dept;
        row.style.display = matchName && matchDept ? '' : 'none';
    });
}

// ── Breakdown modal ───────────────────────────────────────────────────────────
function openBreakdown(id) {
    const r = RECORDS.find(x => x.id === id);
    if (!r) return;

    const earnings = [
        ['Basic Pay',            r.basic_pay],
        ['Overtime Pay',         r.overtime_pay],
        ['Night Differential',   r.night_diff_pay],
        ['Holiday Pay',          r.holiday_pay],
        ['Rest Day Pay',         r.rest_day_pay],
        ['Leave Pay',            r.leave_pay],
        ['Additional Shift Pay', r.additional_shift_pay],
        ['Allowances',           r.allowances],
    ].filter(([, v]) => v > 0);

    const deductions = [
        ['SSS',             r.sss],
        ['PhilHealth',      r.philhealth],
        ['Pag-IBIG',        r.pagibig],
        ['Withholding Tax', r.withholding_tax],
        ['Late',            r.late_deductions],
        ['Undertime',       r.undertime_deductions],
        ['Absent',          r.absent_deductions],
        ['Other',           r.other_deductions],
        ['Deferred (prev)', r.deferred_balance],
    ].filter(([, v]) => v > 0);

    const statusBadge = r.status === 'released'
        ? '<span class="badge badge-success">Released</span>'
        : '<span class="badge badge-secondary">Draft</span>';

    document.getElementById('breakdown-body').innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom">
            <div>
                <strong>${r.employee_name}</strong>
                <div class="text-muted small">${r.employee_position} &mdash; ${r.employee_department}</div>
            </div>
            <div class="text-right">
                ${statusBadge}
                ${r.released_at ? `<div class="text-muted small mt-1">Released: ${r.released_at}</div>` : ''}
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">Earnings</p>
                <table class="table table-sm table-borderless mb-0">
                    <tbody>${earnings.map(([l, v]) => trow(l, v)).join('')}</tbody>
                    <tfoot>
                        <tr class="border-top font-weight-bold">
                            <td class="small">Gross Pay</td>
                            <td class="text-right small">${peso(r.gross_pay)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md-6">
                <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">Deductions</p>
                <table class="table table-sm table-borderless mb-0">
                    <tbody>${deductions.map(([l, v]) => trow(l, v)).join('')}</tbody>
                    <tfoot>
                        <tr class="border-top font-weight-bold">
                            <td class="small">Total Deductions</td>
                            <td class="text-right small">${peso(r.total_deductions)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="callout callout-info mb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Net Pay</h6>
                <h4 class="mb-0 font-weight-bold">${peso(r.net_pay)}</h4>
            </div>
            ${r.notes ? `<small class="text-muted">Note: ${r.notes}</small>` : ''}
        </div>
    `;

    const modalEl = document.getElementById('breakdownModal');
    modalEl.style.display = 'block';
    modalEl.classList.add('show');
    document.body.classList.add('modal-open');

    let backdrop = document.getElementById('modal-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id        = 'modal-backdrop';
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }
}

function closeBreakdown() {
    const modalEl = document.getElementById('breakdownModal');
    modalEl.style.display = 'none';
    modalEl.classList.remove('show');
    document.body.classList.remove('modal-open');
    const backdrop = document.getElementById('modal-backdrop');
    if (backdrop) backdrop.remove();
}
</script>
@endpush