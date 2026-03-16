{{-- resources/views/employee/payroll.blade.php --}}

@extends('layouts.main')

@section('title', 'Payroll & Payslips')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Payroll &amp; Payslips</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="mb-3">
    <h4 class="mb-1">Payroll &amp; Payslips</h4>
    <p class="text-muted mb-0">View your salary details and government contributions.</p>
</div>

{{-- Latest Payslip Card --}}
<div id="latest-card" class="card card-outline card-primary mb-4 d-none">
    <div class="card-header">
        <h5 class="card-title mb-0">Latest Payslip</h5>
        <div class="card-tools">
            <small id="latest-period-label" class="text-muted me-2"></small>
            <span class="badge badge-success">Released</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="info-box mb-0 shadow-none border">
                    <div class="info-box-content">
                        <span class="info-box-text">Gross Pay</span>
                        <span class="info-box-number" id="latest-gross">₱0.00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box mb-0 shadow-none border">
                    <div class="info-box-content">
                        <span class="info-box-text">Total Deductions</span>
                        <span class="info-box-number" id="latest-deductions">₱0.00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box mb-0 shadow-none border">
                    <div class="info-box-content">
                        <span class="info-box-text">Net Pay</span>
                        <span class="info-box-number fw-bold" id="latest-net">₱0.00</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" id="btn-view-latest">
                View Breakdown
            </button>
            <button class="btn btn-outline-secondary btn-sm" id="btn-download-latest">
                Download PDF
            </button>
        </div>
    </div>
</div>

{{-- Payslip History --}}
<div class="card card-outline card-secondary">
    <div class="card-header">
        <h5 class="card-title mb-0">Payslip History</h5>
        <div class="card-tools d-flex gap-2">
            <select id="filter-year" class="form-control form-control-sm" style="width:90px;"></select>
            <select id="filter-month" class="form-control form-control-sm" style="width:130px;">
                <option value="">All Months</option>
                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body p-0">

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0" id="payslip-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Type</th>
                        <th class="text-end">Gross Pay</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">Net Pay</th>
                        <th>Pay Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="payslip-tbody">
                    {{-- Populated by JS --}}
                </tbody>
            </table>
        </div>

        {{-- Empty State --}}
        <div id="empty-state" class="text-center py-5 d-none">
            <i class="fas fa-file-invoice-dollar fa-2x text-muted mb-2 d-block"></i>
            <p class="text-muted mb-1">No payslips found.</p>
            <small class="text-muted">Your payslips will appear here once released by Accounting.</small>
        </div>

    </div>
</div>

{{-- ── Breakdown Modal ──────────────────────────────────────────────── --}}
<div class="modal fade" id="breakdownModal" tabindex="-1" aria-labelledby="breakdownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="breakdownModalLabel">Payslip Breakdown</h5>
                <button type="button" class="close" onclick="closeModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong id="modal-employee-name">{{ auth()->user()->fullName }}</strong>
                        <div class="text-muted small">
                            {{ auth()->user()->position }} &mdash; {{ auth()->user()->department }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="font-weight-bold" id="modal-period-label"></div>
                        <div class="text-muted small">Pay Date: <span id="modal-pay-date"></span></div>
                    </div>
                </div>

                {{-- Deferred Balance Notice --}}
                <div id="modal-deferred-notice" class="alert alert-warning alert-dismissible d-none mb-3">
                    <h6 class="alert-heading mb-1">Deferred Balance Applied</h6>
                    <p class="mb-1 small">
                        An unpaid balance of <strong id="modal-deferred-amount"></strong> from the previous period
                        has been carried over and is included in your total deductions.
                    </p>
                </div>

                {{-- Earnings --}}
                <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">Earnings</p>
                <table class="table table-sm table-borderless mb-3">
                    <tbody id="modal-earnings-body"></tbody>
                    <tfoot>
                        <tr class="border-top font-weight-bold">
                            <td>Gross Pay</td>
                            <td class="text-right" id="modal-gross-pay"></td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Statutory & Attendance Deductions --}}
                <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">Deductions</p>
                <table class="table table-sm table-borderless mb-3">
                    <tbody id="modal-deductions-body"></tbody>
                    <tfoot>
                        <tr class="border-top font-weight-bold">
                            <td>Subtotal</td>
                            <td class="text-right" id="modal-deductions-subtotal"></td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Loan Deductions --}}
                <div id="modal-loans-section">
                    <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">Loans</p>
                    <table class="table table-sm table-borderless mb-3">
                        <tbody id="modal-loans-body"></tbody>
                        <tfoot>
                            <tr class="border-top font-weight-bold">
                                <td>Subtotal</td>
                                <td class="text-right" id="modal-loans-subtotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Total Deductions --}}
                <div class="d-flex justify-content-between border-top pt-2 mb-3">
                    <span class="font-weight-bold">Total Deductions</span>
                    <span class="font-weight-bold" id="modal-total-deductions"></span>
                </div>
                <div class="callout callout-info mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Net Pay</h5>
                        <h4 class="mb-0 font-weight-bold" id="modal-net-pay"></h4>
                    </div>
                    <small id="modal-notes-wrap" class="text-muted d-none">
                        Note: <span id="modal-notes"></span>
                    </small>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-modal-download">
                    Download PDF
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal()">Close</button>
            </div>

        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
// ── Data from server ──────────────────────────────────────────────────────────
const PAYSLIPS = @json($payslips);

// ── State ─────────────────────────────────────────────────────────────────────
let activePayslip = null;

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    populateYearFilter();
    renderLatestCard();
    renderTable();

    document.getElementById('filter-year').addEventListener('change', renderTable);
    document.getElementById('filter-month').addEventListener('change', renderTable);

    document.getElementById('btn-view-latest')?.addEventListener('click', () => {
        if (PAYSLIPS.length) openModal(PAYSLIPS[0]);
    });

    document.getElementById('btn-download-latest')?.addEventListener('click', () => {
        if (PAYSLIPS.length) downloadPayslip(PAYSLIPS[0]);
    });

    document.getElementById('btn-modal-download')?.addEventListener('click', () => {
        if (activePayslip) downloadPayslip(activePayslip);
    });
});

// ── Year filter ───────────────────────────────────────────────────────────────
function populateYearFilter() {
    const sel   = document.getElementById('filter-year');
    const years = [...new Set(PAYSLIPS.map(p => p.pay_date?.substring(0, 4)).filter(Boolean))].sort().reverse();

    const allOpt = document.createElement('option');
    allOpt.value = '';
    allOpt.textContent = 'All Years';
    sel.appendChild(allOpt);

    years.forEach(y => {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        sel.appendChild(opt);
    });
}

// ── Latest card ───────────────────────────────────────────────────────────────
function renderLatestCard() {
    if (!PAYSLIPS.length) return;
    const p = PAYSLIPS[0];
    document.getElementById('latest-card').classList.remove('d-none');
    document.getElementById('latest-period-label').textContent = p.period;
    document.getElementById('latest-gross').textContent        = peso(p.gross_pay);
    document.getElementById('latest-deductions').textContent   = peso(p.total_deductions);
    document.getElementById('latest-net').textContent          = peso(p.net_pay);
}

// ── Table ─────────────────────────────────────────────────────────────────────
function renderTable() {
    const year  = document.getElementById('filter-year').value;
    const month = document.getElementById('filter-month').value.toLowerCase();

    const filtered = PAYSLIPS.filter(p => {
        const matchYear  = !year  || (p.pay_date?.startsWith(year));
        const matchMonth = !month || p.period.toLowerCase().includes(month);
        return matchYear && matchMonth;
    });

    const tbody      = document.getElementById('payslip-tbody');
    const emptyState = document.getElementById('empty-state');
    tbody.innerHTML  = '';

    if (!filtered.length) {
        emptyState.classList.remove('d-none');
        return;
    }

    emptyState.classList.add('d-none');

    filtered.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="align-middle">${esc(p.period)}</td>
            <td class="align-middle">
                <span class="badge badge-secondary">${periodTypeLabel(p.period_type)}</span>
            </td>
            <td class="align-middle text-right">${peso(p.gross_pay)}</td>
            <td class="align-middle text-right">${peso(p.total_deductions)}</td>
            <td class="align-middle text-right font-weight-bold">${peso(p.net_pay)}</td>
            <td class="align-middle">${formatDate(p.pay_date)}</td>
            <td class="align-middle text-center">
                <button class="btn btn-xs btn-outline-primary mr-1" title="View Breakdown"
                    onclick="openModal(PAYSLIPS.find(x => x.id === ${p.id}))">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-xs btn-outline-secondary" title="Download PDF"
                    onclick="downloadPayslip(PAYSLIPS.find(x => x.id === ${p.id}))">
                    <i class="fas fa-download"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// ── Breakdown Modal ───────────────────────────────────────────────────────────
function openModal(payslip) {
    if (!payslip) return;
    activePayslip = payslip;

    document.getElementById('modal-period-label').textContent = payslip.period;
    document.getElementById('modal-pay-date').textContent     = formatDate(payslip.pay_date);

    // Deferred notice
    const deferredNotice = document.getElementById('modal-deferred-notice');
    if (payslip.deferred_balance > 0) {
        document.getElementById('modal-deferred-amount').textContent = peso(payslip.deferred_balance);
        deferredNotice.classList.remove('d-none');
    } else {
        deferredNotice.classList.add('d-none');
    }

    // Earnings
    document.getElementById('modal-earnings-body').innerHTML = buildEarnings(payslip);
    document.getElementById('modal-gross-pay').textContent   = peso(payslip.gross_pay);

    // Deductions — mandatory (statutory + attendance)
    document.getElementById('modal-deductions-body').innerHTML     = buildMandatoryDeductions(payslip);
    document.getElementById('modal-deductions-subtotal').textContent = peso(calcMandatoryTotal(payslip));

    // Deductions — loans
    const { html: loanHtml, total: loanTotal } = buildLoanDeductions(payslip);
    const loansSection = document.getElementById('modal-loans-section');
    if (loanTotal > 0) {
        document.getElementById('modal-loans-body').innerHTML    = loanHtml;
        document.getElementById('modal-loans-subtotal').textContent = peso(loanTotal);
        loansSection.classList.remove('d-none');
    } else {
        loansSection.classList.add('d-none');
    }

    document.getElementById('modal-total-deductions').textContent = peso(payslip.total_deductions);

    // Net pay
    document.getElementById('modal-net-pay').textContent = peso(payslip.net_pay);

    // Notes
    const notesWrap = document.getElementById('modal-notes-wrap');
    if (payslip.notes) {
        document.getElementById('modal-notes').textContent = payslip.notes;
        notesWrap.classList.remove('d-none');
    } else {
        notesWrap.classList.add('d-none');
    }

    const modalEl = document.getElementById('breakdownModal');
    modalEl.style.display = 'block';
    modalEl.classList.add('show');
    document.body.classList.add('modal-open');

    let backdrop = document.getElementById('modal-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'modal-backdrop';
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }
}

function closeModal() {
    const modalEl = document.getElementById('breakdownModal');
    modalEl.style.display = 'none';
    modalEl.classList.remove('show');
    document.body.classList.remove('modal-open');
    const backdrop = document.getElementById('modal-backdrop');
    if (backdrop) backdrop.remove();
}

function buildEarnings(p) {
    const rows = [];
    rows.push(row('Basic Pay',             p.basic_pay));
    if (p.overtime_pay > 0)         rows.push(row('Overtime Pay',             p.overtime_pay));
    if (p.night_diff_pay > 0)       rows.push(row('Night Differential',       p.night_diff_pay));
    if (p.holiday_pay > 0)          rows.push(row('Holiday Pay',              p.holiday_pay));
    if (p.rest_day_pay > 0)         rows.push(row('Rest Day Pay',             p.rest_day_pay));
    if (p.leave_pay > 0)            rows.push(row('Leave Pay',                p.leave_pay));
    if (p.additional_shift_pay > 0) rows.push(row('Additional Shift Pay',     p.additional_shift_pay));
    if (p.allowances > 0)           rows.push(row('Allowances',               p.allowances));
    return rows.join('');
}

// ── Mandatory deductions (statutory + attendance) ─────────────────────────────
function buildMandatoryDeductions(p) {
    const rows = [];
    if (p.sss > 0)                  rows.push(row('SSS Contribution',    p.sss));
    if (p.philhealth > 0)           rows.push(row('PhilHealth',          p.philhealth));
    if (p.pagibig > 0)              rows.push(row('Pag-IBIG',            p.pagibig));
    if (p.withholding_tax > 0)      rows.push(row('Withholding Tax',     p.withholding_tax));
    if (p.late_deductions > 0)      rows.push(row('Late',                p.late_deductions));
    if (p.undertime_deductions > 0) rows.push(row('Undertime',           p.undertime_deductions));
    if (p.absent_deductions > 0)    rows.push(row('Absent',              p.absent_deductions));
    if (p.other_deductions > 0)     rows.push(row('Other Deductions',    p.other_deductions));
    if (p.deferred_balance > 0)     rows.push(row('Deferred Balance (Prior Period)', p.deferred_balance));
    return rows.join('');
}

function calcMandatoryTotal(p) {
    return (p.sss || 0) + (p.philhealth || 0) + (p.pagibig || 0) +
           (p.withholding_tax || 0) + (p.late_deductions || 0) +
           (p.undertime_deductions || 0) + (p.absent_deductions || 0) +
           (p.other_deductions || 0) + (p.deferred_balance || 0);
}

// ── Loan deductions — named per loan from DB ──────────────────────────────────
function buildLoanDeductions(p) {
    const rows  = [];
    let   total = 0;

    if (Array.isArray(p.loan_deductions) && p.loan_deductions.length > 0) {
        p.loan_deductions.forEach(ld => {
            if (ld.amount > 0) {
                rows.push(row(ld.label, ld.amount));
                total += ld.amount;
            }
        });
    }

    return { html: rows.join(''), total };
}

function row(label, amount) {
    return `<tr>
        <td class="text-muted">${esc(label)}</td>
        <td class="text-right">${peso(amount)}</td>
    </tr>`;
}

// ── Download stub ─────────────────────────────────────────────────────────────
function downloadPayslip(payslip) {
    if (!payslip) return;
    Swal.fire({
        icon: 'info',
        title: 'Coming Soon',
        text: `PDF download for ${payslip.period} will be available soon.`,
        confirmButtonColor: '#6c757d',
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function peso(amount) {
    return '₱' + Number(amount || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('-');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return `${months[parseInt(m) - 1]} ${parseInt(d)}, ${y}`;
}

function periodTypeLabel(type) {
    return type === '1st-15th' ? '1st–15th' : '16th–End';
}

function esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str ?? ''));
    return d.innerHTML;
}
</script>
@endpush