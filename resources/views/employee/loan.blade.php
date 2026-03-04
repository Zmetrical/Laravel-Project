@extends('layouts.main')

@section('title', 'My Loans')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">My Loans</li>
    </ol>
@endsection

@section('content')

<div class="row mb-3">
    <div class="col-12">
        <h4 class="mb-0 fw-semibold">My Loans</h4>
        <small class="text-muted">View your SSS and PAG-IBIG loan status</small>
    </div>
</div>

{{-- Info Banner --}}
<div class="callout callout-info mb-4" style="border-left-color: var(--bs-secondary);">
    <div class="d-flex gap-2">
        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0 text-secondary"></i>
        <div>
            <strong class="text-secondary">How to Apply for SSS / PAG-IBIG Loans</strong>
            <p class="mb-1 mt-1 small text-muted">
                To apply for SSS or PAG-IBIG loans, please visit the respective government offices or their online portals directly.
                Once approved, HR will encode your loan details into the system for automatic monthly deduction.
            </p>
            <div class="d-flex gap-3 mt-1">
                <a href="https://www.sss.gov.ph" target="_blank" rel="noopener noreferrer" class="small text-secondary">
                    🏛 SSS Website
                </a>
                <a href="https://www.pagibigfund.gov.ph" target="_blank" rel="noopener noreferrer" class="small text-secondary">
                    🏠 PAG-IBIG Website
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4" id="summary-section" style="display:none!important;">
    <div class="col-md-4">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Active Loans</div>
                        <div class="fs-3 fw-bold" id="stat-active">0</div>
                    </div>
                    <i class="bi bi-credit-card fs-1 text-secondary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Completed Loans</div>
                        <div class="fs-3 fw-bold" id="stat-completed">0</div>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-secondary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Remaining Balance</div>
                        <div class="fs-3 fw-bold text-secondary" id="stat-balance">₱0.00</div>
                    </div>
                    <i class="bi bi-wallet2 fs-1 text-secondary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Active Loans --}}
<div id="active-loans-section">
    <h5 class="mb-3 fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-graph-up-arrow text-secondary"></i> Active Loans
    </h5>
    <div id="active-loans-container"></div>
</div>

{{-- No Active Loans --}}
<div id="no-loans-msg" class="card shadow-sm mb-4" style="display:none;">
    <div class="card-body text-center py-5">
        <i class="bi bi-credit-card fs-1 text-muted mb-3 d-block"></i>
        <h5 class="fw-semibold">No Active Loans</h5>
        <p class="text-muted mb-0">You don't have any active SSS or PAG-IBIG loans at the moment.</p>
        <small class="text-muted">To apply for loans, please visit SSS or PAG-IBIG offices or their online portals.</small>
    </div>
</div>

{{-- Completed Loans --}}
<div id="completed-loans-section" style="display:none;">
    <h5 class="mb-3 fw-semibold d-flex align-items-center gap-2 mt-4">
        <i class="bi bi-check-circle text-muted"></i> Completed Loans
    </h5>
    <div id="completed-loans-container"></div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ─── Sample Data ───────────────────────────────────────────────────────────
    const currentUser = { id: 'EMP-001', name: 'Juan dela Cruz' };

    const loans = [
        {
            id: 'LOAN-2024-001',
            employeeId: 'EMP-001',
            loanTypeId: 'sss',
            loanTypeName: 'SSS Salary Loan',
            amount: 24000.00,
            monthlyAmortization: 1000.00,
            term: 24,
            paymentsMade: 10,
            remainingBalance: 14000.00,
            startDate: '2024-01-15',
            status: 'active',
            completedDate: null,
        },
        {
            id: 'LOAN-2024-002',
            employeeId: 'EMP-001',
            loanTypeId: 'pagibig',
            loanTypeName: 'PAG-IBIG Multi-Purpose Loan',
            amount: 50000.00,
            monthlyAmortization: 2083.33,
            term: 24,
            paymentsMade: 6,
            remainingBalance: 37500.00,
            startDate: '2024-06-01',
            status: 'active',
            completedDate: null,
        },
        {
            id: 'LOAN-2022-001',
            employeeId: 'EMP-001',
            loanTypeId: 'sss',
            loanTypeName: 'SSS Salary Loan',
            amount: 15000.00,
            monthlyAmortization: 625.00,
            term: 24,
            paymentsMade: 24,
            remainingBalance: 0.00,
            startDate: '2022-03-01',
            status: 'completed',
            completedDate: '2024-03-01',
        },
    ];

    // ─── Config ────────────────────────────────────────────────────────────────
    const typeConfig = {
        sss:    { label: 'SSS',    badgeClass: 'badge text-bg-secondary' },
        pagibig:{ label: 'PAG-IBIG', badgeClass: 'badge text-bg-primary' },
    };

    // ─── Helpers ───────────────────────────────────────────────────────────────
    function formatPeso(amount) {
        return '₱' + parseFloat(amount || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        return new Date(dateStr).toLocaleDateString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric'
        });
    }

    function getProgress(paymentsMade, term) {
        if (!term) return 0;
        return Math.round((paymentsMade / term) * 100);
    }

    function getConfig(loanTypeId) {
        return typeConfig[loanTypeId] || typeConfig.sss;
    }

    // ─── Render Active Loan Card ───────────────────────────────────────────────
    function renderActiveLoan(loan) {
        const progress = getProgress(loan.paymentsMade, loan.term);
        const cfg = getConfig(loan.loanTypeId);

        return `
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card fs-5 text-secondary"></i>
                    <div>
                        <div class="fw-semibold">${loan.loanTypeName}</div>
                        <small class="text-muted">Loan ID: ${loan.id}</small>
                    </div>
                </div>
                <span class="badge text-bg-secondary">
                    <i class="bi bi-check-circle me-1"></i>Active
                </span>
            </div>
            <div class="card-body">

                {{-- Amounts --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="bg-light rounded p-3">
                            <div class="text-muted small mb-1">Total Loan Amount</div>
                            <div class="fs-4 fw-bold">${formatPeso(loan.amount)}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded p-3">
                            <div class="text-muted small mb-1">Monthly Deduction</div>
                            <div class="fs-4 fw-bold text-secondary">${formatPeso(loan.monthlyAmortization)}</div>
                        </div>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Payment Progress</span>
                        <span class="small fw-semibold">${loan.paymentsMade} of ${loan.term} payments</span>
                    </div>
                    <div class="progress" style="height: 18px;">
                        <div class="progress-bar bg-secondary"
                             role="progressbar"
                             style="width: ${progress}%"
                             aria-valuenow="${progress}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            ${progress}%
                        </div>
                    </div>
                </div>

                {{-- Remaining Balance --}}
                <div class="rounded p-3 mb-3 border border-secondary-subtle">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small mb-1">Remaining Balance</div>
                            <div class="fs-3 fw-bold text-secondary">${formatPeso(loan.remainingBalance)}</div>
                        </div>
                        <i class="bi bi-wallet2 fs-1 text-secondary opacity-25"></i>
                    </div>
                </div>

                {{-- Meta --}}
                <div class="row g-2 small mb-3">
                    <div class="col-md-6">
                        <span class="text-muted">Start Date: </span>
                        <span class="fw-semibold">
                            <i class="bi bi-calendar3 me-1"></i>${formatDate(loan.startDate)}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">Payments Made: </span>
                        <span class="fw-semibold">${loan.paymentsMade} time(s)</span>
                    </div>
                </div>

                {{-- Next Payment Notice --}}
                ${loan.paymentsMade < loan.term ? `
                <div class="callout callout-info py-2 mb-0" style="border-left-color: var(--bs-secondary);">
                    <small class="text-muted d-block">Next Deduction</small>
                    <small>${formatPeso(loan.monthlyAmortization)} will be deducted in the next payroll period.</small>
                </div>` : ''}

            </div>
        </div>`;
    }

    // ─── Render Completed Loan Row ─────────────────────────────────────────────
    function renderCompletedLoan(loan) {
        const cfg = getConfig(loan.loanTypeId);
        return `
        <div class="card shadow-sm mb-2">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-check-circle-fill text-muted fs-5"></i>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="${cfg.badgeClass} small">${loan.loanTypeName}</span>
                                <span class="text-muted small">• ${loan.id}</span>
                            </div>
                            <div class="small">
                                <span class="text-muted">Amount: </span>
                                <span class="fw-semibold">${formatPeso(loan.amount)}</span>
                                <span class="text-muted ms-3">Completed: </span>
                                <span class="fw-semibold">${formatDate(loan.completedDate)}</span>
                            </div>
                        </div>
                    </div>
                    <span class="badge text-bg-secondary">✓ Paid</span>
                </div>
            </div>
        </div>`;
    }

    // ─── Main Render ───────────────────────────────────────────────────────────
    function renderAll() {
        const myLoans       = loans.filter(l => l.employeeId === currentUser.id);
        const activeLoans   = myLoans.filter(l => l.status === 'active');
        const completedLoans= myLoans.filter(l => l.status === 'completed');

        // Summary
        if (myLoans.length > 0) {
            document.getElementById('summary-section').style.removeProperty('display');
            document.getElementById('stat-active').textContent    = activeLoans.length;
            document.getElementById('stat-completed').textContent  = completedLoans.length;
            const totalBalance = activeLoans.reduce((sum, l) => sum + parseFloat(l.remainingBalance || 0), 0);
            document.getElementById('stat-balance').textContent   = formatPeso(totalBalance);
        }

        // Active Loans
        if (activeLoans.length > 0) {
            document.getElementById('active-loans-container').innerHTML =
                activeLoans.map(renderActiveLoan).join('');
        } else {
            document.getElementById('active-loans-section').style.display = 'none';
            document.getElementById('no-loans-msg').style.display = '';
        }

        // Completed Loans
        if (completedLoans.length > 0) {
            document.getElementById('completed-loans-section').style.display = '';
            document.getElementById('completed-loans-container').innerHTML =
                completedLoans.map(renderCompletedLoan).join('');
        }
    }

    renderAll();
});
</script>
@endpush