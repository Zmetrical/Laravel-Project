@extends('layouts.main')

@section('title', 'Payroll & Payslips')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Payroll & Payslips</li>
    </ol>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-3">
    <h4 class="mb-1">Payroll &amp; Payslips</h4>
    <p class="text-muted mb-0">View your salary details and government contributions</p>
</div>

{{-- Suspension Notice (conditionally rendered via JS) --}}
<div id="suspension-notice" class="alert alert-secondary border-start border-5 border-secondary d-none" role="alert">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-slash-circle fs-3 text-secondary"></i>
        <div>
            <h5 class="alert-heading mb-1">Account Suspended</h5>
            <p class="mb-2">Your account has been suspended due to a negative payroll balance during the 16th–End cutoff period.</p>
            <p class="mb-2 small"><strong>Reason:</strong> Your deductions exceeded your gross pay, resulting in a negative net pay. Deferred balances are only allowed for 1st–15th cutoff periods per company policy.</p>
            <p class="mb-0 small"><strong>Action Required:</strong> Please contact HR immediately to resolve this issue.</p>
        </div>
    </div>
</div>

{{-- Latest Payslip Summary --}}
<div id="latest-payslip-card" class="card shadow-sm mb-4 d-none">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Latest Payslip</h5>
            <small id="latest-period" class="text-muted"></small>
        </div>
        <span class="badge bg-secondary">Released</span>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="p-3 border rounded">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-graph-up text-secondary"></i>
                        <small class="text-muted">Gross Pay</small>
                    </div>
                    <h4 id="latest-gross" class="mb-0">₱0.00</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-graph-down text-muted"></i>
                        <small class="text-muted">Total Deductions</small>
                    </div>
                    <h4 id="latest-deductions" class="mb-0">₱0.00</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-body-secondary">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-cash-stack text-secondary"></i>
                        <small class="text-muted">Net Pay</small>
                    </div>
                    <h3 id="latest-net" class="mb-0 text-secondary fw-bold">₱0.00</h3>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button id="btn-view-latest" class="btn btn-outline-primary w-50" onclick="openBreakdownModal(latestPayslip)">
                <i class="bi bi-eye me-1"></i> View Breakdown
            </button>
            <button id="btn-download-latest" class="btn btn-outline-secondary w-50" onclick="downloadPayslip(latestPayslip)">
                <i class="bi bi-download me-1"></i> Download PDF
            </button>
        </div>
    </div>
</div>

{{-- Payslip History --}}
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="card-title mb-0">Payslip History</h5>
    </div>
    <div class="card-body">

        {{-- Filters --}}
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <select id="filter-month" class="form-select form-select-sm" onchange="applyFilters()">
                    <option value="all">All Months</option>
                    <option value="January">January</option>
                    <option value="February">February</option>
                    <option value="March">March</option>
                    <option value="April">April</option>
                    <option value="May">May</option>
                    <option value="June">June</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                    <option value="September">September</option>
                    <option value="October">October</option>
                    <option value="November">November</option>
                    <option value="December">December</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter-year" class="form-select form-select-sm" onchange="applyFilters()">
                    {{-- Populated by JS --}}
                </select>
            </div>
        </div>

        {{-- Payslip List --}}
        <div id="payslip-list"></div>

        {{-- Empty State --}}
        <div id="empty-state" class="text-center py-5 d-none">
            <i class="bi bi-file-earmark-text fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted mb-1">No Payslips Available</p>
            <small class="text-muted">Your payslips will appear here once released by Accounting.</small>
        </div>

    </div>
</div>

{{-- ===== BREAKDOWN MODAL ===== --}}
<div class="modal fade" id="breakdownModal" tabindex="-1" aria-labelledby="breakdownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="breakdownModalLabel">Payslip Breakdown</h5>
                    <small id="modal-period" class="text-muted"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Deferred Notice --}}
                <div id="modal-deferred-notice" class="alert alert-secondary border-start border-4 border-secondary d-none mb-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <div>
                            <strong>Deferred from Previous Period</strong>
                            <p class="mb-1 small">Unpaid deductions from the previous payroll period have been applied to this period.</p>
                            <div class="d-flex justify-content-between">
                                <span>Previous Deferred Amount</span>
                                <strong id="modal-deferred-amount"></strong>
                            </div>
                            <small class="text-muted">This amount is included in Total Deductions below.</small>
                        </div>
                    </div>
                </div>

                {{-- Earnings --}}
                <h6 class="text-uppercase text-muted small fw-semibold mb-2">Earnings</h6>
                <table class="table table-sm table-borderless mb-3">
                    <tbody id="modal-earnings"></tbody>
                    <tfoot>
                        <tr class="fw-semibold border-top">
                            <td>Gross Pay</td>
                            <td class="text-end" id="modal-gross"></td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Deductions --}}
                <h6 class="text-uppercase text-muted small fw-semibold mb-2">Deductions</h6>
                <table class="table table-sm table-borderless mb-3">
                    <tbody id="modal-deductions"></tbody>
                    <tfoot>
                        <tr class="fw-semibold border-top text-danger">
                            <td>Total Deductions</td>
                            <td class="text-end" id="modal-total-deductions"></td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Net Pay --}}
                <div class="p-3 bg-body-secondary rounded border">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Net Pay</h5>
                        <h3 class="mb-0 text-secondary fw-bold" id="modal-net-pay"></h3>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadPayslip(activePayslip)">
                    <i class="bi bi-download me-1"></i> Download PDF
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
const currentEmployee = {
    id: 'EMP001',
    fullName: 'Juan dela Cruz',
    department: 'Operations',
    position: 'Supervisor',
    isSuspended: false
};

const payslips = [
    {
        id: 'PAY-2025-012',
        period: 'June 16-30, 2025',
        payDate: '2025-07-05',
        basicPay: 12500.00,
        overtime: 1200.00,
        nightDiff: 450.00,
        leavePay: 0,
        restDayPay: 0,
        restDayType: null,
        restDayHasNightShift: false,
        holidayPay: 0,
        additionalShiftPay: 0,
        additionalShiftCount: 0,
        allowances: 1500.00,
        grossPay: 15650.00,
        sss: 900.00,
        philHealth: 437.50,
        pagibig: 100.00,
        tax: 312.50,
        loanDeductions: 500.00,
        lateDeductions: 0,
        undertimeDeductions: 0,
        absentDeductions: 0,
        otherDeductions: 0,
        totalDeductions: 2250.00,
        netPay: 13400.00,
        deferredBalance: 0,
        released: true
    },
    {
        id: 'PAY-2025-011',
        period: 'June 1-15, 2025',
        payDate: '2025-06-20',
        basicPay: 12500.00,
        overtime: 0,
        nightDiff: 0,
        leavePay: 1041.67,
        restDayPay: 1625.00,
        restDayType: 'regular',
        restDayHasNightShift: false,
        holidayPay: 0,
        additionalShiftPay: 0,
        additionalShiftCount: 0,
        allowances: 1500.00,
        grossPay: 16666.67,
        sss: 900.00,
        philHealth: 437.50,
        pagibig: 100.00,
        tax: 416.67,
        loanDeductions: 500.00,
        lateDeductions: 150.00,
        undertimeDeductions: 0,
        absentDeductions: 0,
        otherDeductions: 0,
        totalDeductions: 2504.17,
        netPay: 14162.50,
        deferredBalance: 0,
        released: true
    },
    {
        id: 'PAY-2025-010',
        period: 'May 16-31, 2025',
        payDate: '2025-06-05',
        basicPay: 12500.00,
        overtime: 800.00,
        nightDiff: 0,
        leavePay: 0,
        restDayPay: 0,
        restDayType: null,
        restDayHasNightShift: false,
        holidayPay: 2500.00,
        additionalShiftPay: 2600.00,
        additionalShiftCount: 2,
        allowances: 1500.00,
        grossPay: 19900.00,
        sss: 900.00,
        philHealth: 437.50,
        pagibig: 100.00,
        tax: 650.00,
        loanDeductions: 500.00,
        lateDeductions: 0,
        undertimeDeductions: 200.00,
        absentDeductions: 0,
        otherDeductions: 0,
        totalDeductions: 2787.50,
        netPay: 17112.50,
        deferredBalance: 320.00,
        released: true
    },
    {
        id: 'PAY-2025-009',
        period: 'May 1-15, 2025',
        payDate: '2025-05-20',
        basicPay: 12500.00,
        overtime: 0,
        nightDiff: 800.00,
        leavePay: 0,
        restDayPay: 0,
        restDayType: null,
        restDayHasNightShift: false,
        holidayPay: 0,
        additionalShiftPay: 0,
        additionalShiftCount: 0,
        allowances: 1500.00,
        grossPay: 14800.00,
        sss: 900.00,
        philHealth: 437.50,
        pagibig: 100.00,
        tax: 312.50,
        loanDeductions: 500.00,
        lateDeductions: 320.00,
        undertimeDeductions: 0,
        absentDeductions: 0,
        otherDeductions: 0,
        totalDeductions: 2570.00,
        netPay: 12230.00,
        deferredBalance: 0,
        released: true
    }
];

// ============================================================
// STATE
// ============================================================
let filteredPayslips  = [...payslips];
let latestPayslip     = payslips.length ? payslips[0] : null;
let activePayslip     = null;

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    if (currentEmployee.isSuspended) {
        document.getElementById('suspension-notice').classList.remove('d-none');
    }

    populateYearFilter();
    renderLatestCard();
    applyFilters();
});

// ============================================================
// YEAR FILTER
// ============================================================
function populateYearFilter() {
    const yearSelect = document.getElementById('filter-year');
    const years = [...new Set(payslips.map(p => extractYear(p.period)))].sort((a, b) => b - a);

    if (!years.length) years.push(new Date().getFullYear());

    years.forEach(y => {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        yearSelect.appendChild(opt);
    });
}

function extractYear(period) {
    const m = period.match(/\d{4}/);
    return m ? parseInt(m[0]) : new Date().getFullYear();
}

// ============================================================
// LATEST PAYSLIP CARD
// ============================================================
function renderLatestCard() {
    if (!latestPayslip) return;

    document.getElementById('latest-payslip-card').classList.remove('d-none');
    document.getElementById('latest-period').textContent     = latestPayslip.period;
    document.getElementById('latest-gross').textContent      = formatPeso(latestPayslip.grossPay);
    document.getElementById('latest-deductions').textContent = formatPeso(latestPayslip.totalDeductions);
    document.getElementById('latest-net').textContent        = formatPeso(latestPayslip.netPay);
}

// ============================================================
// FILTER
// ============================================================
function applyFilters() {
    const month = document.getElementById('filter-month').value;
    const year  = document.getElementById('filter-year').value;

    filteredPayslips = payslips.filter(p => {
        const matchMonth = month === 'all' || p.period.toLowerCase().includes(month.toLowerCase());
        const matchYear  = p.period.includes(year);
        return matchMonth && matchYear;
    });

    renderPayslipList();
}

// ============================================================
// RENDER LIST
// ============================================================
function renderPayslipList() {
    const container  = document.getElementById('payslip-list');
    const emptyState = document.getElementById('empty-state');
    container.innerHTML = '';

    if (!filteredPayslips.length) {
        emptyState.classList.remove('d-none');
        return;
    }

    emptyState.classList.add('d-none');

    filteredPayslips.forEach(p => {
        const row = document.createElement('div');
        row.className = 'border rounded p-3 mb-2 d-flex align-items-center justify-content-between gap-3';
        row.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <div class="bg-body-secondary rounded p-2 text-secondary">
                    <i class="bi bi-file-earmark-text fs-5"></i>
                </div>
                <div>
                    <div class="fw-semibold">${p.period}</div>
                    <small class="text-muted">Pay Date: ${formatDate(p.payDate)}</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-4">
                <div class="text-end d-none d-md-block">
                    <div class="fw-semibold">${formatPeso(p.netPay)}</div>
                    <small class="text-muted">Net Pay</small>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" title="View Breakdown" onclick="openBreakdownModal(payslips.find(x => x.id === '${p.id}'))">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" title="Download PDF" onclick="downloadPayslip(payslips.find(x => x.id === '${p.id}'))">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(row);
    });
}

// ============================================================
// BREAKDOWN MODAL
// ============================================================
function openBreakdownModal(payslip) {
    if (!payslip) return;
    activePayslip = payslip;

    document.getElementById('modal-period').textContent = payslip.period;

    // ── Deferred Notice
    const deferredNotice = document.getElementById('modal-deferred-notice');
    if ((payslip.deferredBalance || 0) > 0) {
        deferredNotice.classList.remove('d-none');
        document.getElementById('modal-deferred-amount').textContent = formatPeso(payslip.deferredBalance);
    } else {
        deferredNotice.classList.add('d-none');
    }

    // ── Earnings
    const earningsRows = buildEarningsRows(payslip);
    document.getElementById('modal-earnings').innerHTML = earningsRows;
    document.getElementById('modal-gross').textContent  = formatPeso(payslip.grossPay);

    // ── Deductions
    document.getElementById('modal-deductions').innerHTML  = buildDeductionRows(payslip);
    document.getElementById('modal-total-deductions').textContent = formatPeso(payslip.totalDeductions);

    // ── Net Pay
    document.getElementById('modal-net-pay').textContent = formatPeso(payslip.netPay);

    const modal = new bootstrap.Modal(document.getElementById('breakdownModal'));
    modal.show();
}

function buildEarningsRows(p) {
    const rows = [];

    rows.push(row('Basic Pay', p.basicPay));

    if ((p.overtime || 0) > 0) rows.push(row('Overtime Pay', p.overtime));

    if ((p.restDayPay || 0) > 0) {
        const label = restDayLabel(p);
        rows.push(row(label, p.restDayPay));
    } else {
        if ((p.nightDiff || 0) > 0) rows.push(row('Night Differential', p.nightDiff));
        if ((p.holidayPay || 0) > 0) rows.push(row('Holiday Pay', p.holidayPay));
    }

    if ((p.leavePay || 0) > 0) rows.push(row('Leave Pay', p.leavePay));

    if ((p.additionalShiftPay || 0) > 0 && (p.additionalShiftCount || 0) > 0) {
        const label = `Additional Shift Pay (${p.additionalShiftCount} shift${p.additionalShiftCount > 1 ? 's' : ''})`;
        rows.push(row(label, p.additionalShiftPay));
    }

    if ((p.allowances || 0) > 0) rows.push(row('Allowances', p.allowances));

    return rows.join('');
}

function buildDeductionRows(p) {
    const rows = [];
    if ((p.sss || 0) > 0)        rows.push(row('SSS Contribution', p.sss));
    if ((p.philHealth || 0) > 0)  rows.push(row('PhilHealth', p.philHealth));
    if ((p.pagibig || 0) > 0)     rows.push(row('Pag-IBIG', p.pagibig));
    if ((p.tax || 0) > 0)         rows.push(row('Withholding Tax', p.tax));
    if ((p.loanDeductions || 0) > 0) rows.push(row('Loan Deductions', p.loanDeductions));

    const tardiness = (p.lateDeductions || 0) + (p.undertimeDeductions || 0) + (p.absentDeductions || 0);
    if (tardiness > 0) rows.push(row('Tardiness (Late/Undertime/Absent)', tardiness));

    if ((p.otherDeductions || 0) > 0) rows.push(row('Other Deductions', p.otherDeductions));

    return rows.join('');
}

function row(label, amount) {
    return `<tr>
        <td class="text-muted">${label}</td>
        <td class="text-end">${formatPeso(amount || 0)}</td>
    </tr>`;
}

function restDayLabel(p) {
    const nd = p.restDayHasNightShift;
    switch (p.restDayType) {
        case 'regular_holiday': return nd ? 'Rest Day Pay (RH+RD+ND: 2.86×)' : 'Rest Day Pay (2.60×)';
        case 'special_holiday': return nd ? 'Rest Day Pay (SH+RD+ND: 1.65×)' : 'Rest Day Pay (1.50×)';
        default:                return nd ? 'Rest Day Pay (RD+ND: 1.43×)'     : 'Rest Day Pay (1.30×)';
    }
}

// ============================================================
// DOWNLOAD (stub — wire to real PDF util)
// ============================================================
function downloadPayslip(payslip) {
    if (!payslip) return;
    Swal.fire({
        icon: 'success',
        title: 'Payslip Downloaded',
        text: `Payslip for ${payslip.period} saved as PDF.`,
        confirmButtonColor: '#6c757d',
        timer: 2000,
        showConfirmButton: false
    });
}

// ============================================================
// HELPERS
// ============================================================
function formatPeso(amount) {
    return '₱' + Number(amount || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
}
</script>
@endpush