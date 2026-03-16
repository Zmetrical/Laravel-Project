@extends('layouts.main')

@section('title', 'Payroll Summary — ' . $period->label)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('accounting.payroll.periods.index') }}">Payroll Periods</a></li>
        <li class="breadcrumb-item"><a href="{{ route('accounting.payroll.periods.records', $period) }}">Records</a></li>
        <li class="breadcrumb-item active">Summary</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="mb-1">Payroll Summary</h4>
        <p class="text-muted mb-0">
            {{ $period->label }}
            &mdash; Pay Date: {{ $period->pay_date->format('F d, Y') }}
            &mdash;
            @if ($period->isReleased())
                <span class="badge badge-success">Released</span>
            @else
                <span class="badge badge-dark">Closed</span>
            @endif
        </p>
    </div>
    <a href="{{ route('accounting.payroll.periods.records', $period) }}"
       class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-list-ul me-1"></i> View Records
    </a>
</div>

{{-- ── Overview Cards ────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="info-box shadow-none border mb-0">
            <div class="info-box-content">
                <span class="info-box-text">Employees Paid</span>
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

<div class="row g-3">

    {{-- ── Left Column ──────────────────────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Department Breakdown --}}
        <div class="card mb-3">
            <div class="card-header py-2">
                <h6 class="card-title mb-0">By Department</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th class="text-center">Employees</th>
                                <th class="text-right">Gross Pay</th>
                                <th class="text-right">Deductions</th>
                                <th class="text-right">Net Pay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($byDepartment as $dept)
                            <tr>
                                <td class="align-middle">
                                    <span class="small">{{ $dept['department'] }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <small>{{ $dept['count'] }}</small>
                                </td>
                                <td class="align-middle text-right">
                                    <small>₱{{ number_format($dept['gross_pay'], 2) }}</small>
                                </td>
                                <td class="align-middle text-right">
                                    <small>₱{{ number_format($dept['total_deductions'], 2) }}</small>
                                </td>
                                <td class="align-middle text-right font-weight-bold">
                                    <small>₱{{ number_format($dept['net_pay'], 2) }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No records</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if ($byDepartment->count() > 1)
                        <tfoot class="border-top">
                            <tr class="font-weight-bold">
                                <td class="small">Total</td>
                                <td class="text-center small">{{ $totals['count'] }}</td>
                                <td class="text-right small">₱{{ number_format($totals['gross_pay'], 2) }}</td>
                                <td class="text-right small">₱{{ number_format($totals['total_deductions'], 2) }}</td>
                                <td class="text-right small">₱{{ number_format($totals['net_pay'], 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Employee List (collapsed, for reference) --}}
        <div class="card">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Employee Breakdown</h6>
                <button class="btn btn-xs btn-outline-secondary"
                        type="button"
                        data-toggle="collapse"
                        data-target="#emp-collapse">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse" id="emp-collapse">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-right">Gross</th>
                                    <th class="text-right">Deductions</th>
                                    <th class="text-right">Net Pay</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($records->sortBy(fn($r) => $r->employee?->fullName) as $record)
                                <tr>
                                    <td class="align-middle">
                                        <div class="small font-weight-semibold">
                                            {{ $record->employee?->fullName ?? 'Unknown' }}
                                        </div>
                                        <div class="text-muted" style="font-size:.7rem">
                                            {{ $record->employee?->department }}
                                        </div>
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
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Right Column ─────────────────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Earnings Breakdown --}}
        <div class="card mb-3">
            <div class="card-header py-2">
                <h6 class="card-title mb-0">Earnings Breakdown</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        @php
                            $earningRows = [
                                'Basic Pay'           => $totals['basic_pay'],
                                'Overtime Pay'        => $totals['overtime_pay'],
                                'Night Differential'  => $totals['night_diff_pay'],
                                'Holiday Pay'         => $totals['holiday_pay'],
                                'Leave Pay'           => $totals['leave_pay'],
                                'Allowances'          => $totals['allowances'],
                            ];
                        @endphp
                        @foreach ($earningRows as $label => $amount)
                            @if ($amount > 0)
                            <tr>
                                <td class="text-muted small px-3">{{ $label }}</td>
                                <td class="text-right small px-3">₱{{ number_format($amount, 2) }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-top font-weight-bold">
                            <td class="small px-3">Gross Pay</td>
                            <td class="text-right small px-3">₱{{ number_format($totals['gross_pay'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Deductions Breakdown --}}
        <div class="card">
            <div class="card-header py-2">
                <h6 class="card-title mb-0">Deductions Breakdown</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        @php
                            $deductionRows = [
                                'SSS'              => $totals['sss'],
                                'PhilHealth'       => $totals['philhealth'],
                                'Pag-IBIG'         => $totals['pagibig'],
                                'Withholding Tax'  => $totals['withholding_tax'],
                                'Late'             => $totals['late_deductions'],
                                'Undertime'        => $totals['undertime_deductions'],
                                'Absent'           => $totals['absent_deductions'],
                                'Other'            => $totals['other_deductions'],
                            ];
                        @endphp
                        @foreach ($deductionRows as $label => $amount)
                            @if ($amount > 0)
                            <tr>
                                <td class="text-muted small px-3">{{ $label }}</td>
                                <td class="text-right small px-3">₱{{ number_format($amount, 2) }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-top font-weight-bold">
                            <td class="small px-3">Total Deductions</td>
                            <td class="text-right small px-3">₱{{ number_format($totals['total_deductions'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Net Pay callout --}}
            <div class="card-footer">
                <div class="callout callout-info mb-0 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold">Total Net Pay</span>
                        <span class="font-weight-bold h5 mb-0">
                            ₱{{ number_format($totals['net_pay'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection