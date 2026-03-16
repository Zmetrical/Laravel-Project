{{-- resources/views/employee/loan.blade.php --}}

@extends('layouts.main')

@section('title', 'My Loans')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">My Loans</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

<div class="mb-3">
    <h4 class="mb-0">My Loans</h4>
    <small class="text-muted">View your SSS and PAG-IBIG loan status and payment progress.</small>
</div>

{{-- Info Banner --}}
<div class="callout callout-info mb-4">
    <strong>How to Apply for SSS / PAG-IBIG Loans</strong>
    <p class="mb-1 mt-1 small text-muted">
        To apply for SSS or PAG-IBIG loans, please visit the respective government offices or their online portals.
        Once approved, HR will encode your loan details into the system for automatic monthly deduction.
    </p>
    <div class="d-flex gap-3 mt-1">
        <a href="https://www.sss.gov.ph" target="_blank" rel="noopener noreferrer" class="small">
            SSS Website &rarr;
        </a>
        <a href="https://www.pagibigfund.gov.ph" target="_blank" rel="noopener noreferrer" class="small">
            PAG-IBIG Website &rarr;
        </a>
    </div>
</div>

@php
    $activeLoans    = collect($loansData)->where('status', 'active')->values();
    $completedLoans = collect($loansData)->where('status', 'completed')->values();
    $totalBalance   = $activeLoans->sum('remaining_balance');
@endphp

@if(count($loansData) > 0)

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="info-box mb-0 shadow-sm">
                <div class="info-box-content">
                    <span class="info-box-text">Active Loans</span>
                    <span class="info-box-number">{{ $activeLoans->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box mb-0 shadow-sm">
                <div class="info-box-content">
                    <span class="info-box-text">Completed Loans</span>
                    <span class="info-box-number">{{ $completedLoans->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box mb-0 shadow-sm">
                <div class="info-box-content">
                    <span class="info-box-text">Total Remaining Balance</span>
                    <span class="info-box-number">₱{{ number_format($totalBalance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

@endif

{{-- Active Loans --}}
@if($activeLoans->count() > 0)

    <h5 class="mb-3">Active Loans</h5>

    @foreach($activeLoans as $loan)
        @php
            $progress = $loan['term_months'] > 0
                ? min(100, round(($loan['payments_made'] / $loan['term_months']) * 100))
                : 0;
        @endphp

        <div class="card card-outline card-secondary mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ $loan['loan_type_name'] }}</h6>
                <div class="card-tools">
                    <span class="badge badge-secondary">Active</span>
                </div>
            </div>
            <div class="card-body">

                {{-- Amounts --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <div class="text-muted small mb-1">Total Loan Amount</div>
                            <div class="font-weight-bold h5 mb-0">
                                ₱{{ number_format($loan['amount'], 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <div class="text-muted small mb-1">Monthly Deduction</div>
                            <div class="font-weight-bold h5 mb-0">
                                ₱{{ number_format($loan['monthly_amortization'], 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <div class="text-muted small mb-1">Remaining Balance</div>
                            <div class="font-weight-bold h5 mb-0">
                                ₱{{ number_format($loan['remaining_balance'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Payment Progress</span>
                        <span class="small font-weight-bold">
                            {{ $loan['payments_made'] }} of {{ $loan['term_months'] }} payments
                        </span>
                    </div>
                    <div class="progress" style="height: 16px;">
                        <div class="progress-bar bg-secondary"
                             role="progressbar"
                             style="width: {{ $progress }}%"
                             aria-valuenow="{{ $progress }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            {{ $progress }}%
                        </div>
                    </div>
                </div>

                {{-- Meta --}}
                <div class="row small mb-0">
                    <div class="col-md-6">
                        <span class="text-muted">Start Date:</span>
                        <span class="font-weight-bold ml-1">
                            {{ \Carbon\Carbon::parse($loan['start_date'])->format('M d, Y') }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">Loan Type:</span>
                        <span class="font-weight-bold ml-1">{{ strtoupper($loan['loan_type']) }}</span>
                    </div>
                </div>

                @if($loan['notes'])
                    <div class="mt-2">
                        <small class="text-muted">Note: {{ $loan['notes'] }}</small>
                    </div>
                @endif

            </div>
        </div>
    @endforeach

@else

    {{-- No Active Loans --}}
    <div class="card mb-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-credit-card fa-2x text-muted mb-3 d-block"></i>
            <h5>No Active Loans</h5>
            <p class="text-muted mb-0">You don't have any active SSS or PAG-IBIG loans at the moment.</p>
            <small class="text-muted">To apply, please visit SSS or PAG-IBIG offices or their online portals.</small>
        </div>
    </div>

@endif

{{-- Completed Loans --}}
@if($completedLoans->count() > 0)

    <h5 class="mb-3 mt-4">Completed Loans</h5>

    @foreach($completedLoans as $loan)
        <div class="card mb-2">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="font-weight-bold mb-1">{{ $loan['loan_type_name'] }}</div>
                        <div class="small text-muted">
                            Amount: ₱{{ number_format($loan['amount'], 2) }}
                            &nbsp;&bull;&nbsp;
                            {{ $loan['payments_made'] }} payments
                            &nbsp;&bull;&nbsp;
                            @if($loan['completed_date'])
                                Completed: {{ \Carbon\Carbon::parse($loan['completed_date'])->format('M d, Y') }}
                            @endif
                        </div>
                    </div>
                    <span class="badge badge-secondary">Paid</span>
                </div>
            </div>
        </div>
    @endforeach

@endif

@endsection