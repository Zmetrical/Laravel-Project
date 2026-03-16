@extends('layouts.main')

@section('title', $employee->fullName)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('hresource.employees.index') }}">Employees</a></li>
        <li class="breadcrumb-item active">{{ $employee->fullName }}</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle
                     bg-secondary bg-opacity-10 text-secondary fw-bold"
              style="width:52px;height:52px;font-size:1.1rem;flex-shrink:0;">
            {{ strtoupper(substr($employee->fullName, 0, 1)) }}{{ strtoupper(substr(strrchr($employee->fullName, ' '), 1, 1)) }}
        </span>
        <div>
            <h4 class="mb-0 fw-semibold">{{ $employee->fullName }}</h4>
            <div class="text-muted small">
                {{ $employee->id }}
                @if($employee->position)
                    &middot; {{ $employee->position }}
                @endif
                @if($employee->department)
                    &middot; {{ $employee->department }}
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('hresource.employees.edit', $employee) }}"
           class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <form method="POST"
              action="{{ route('hresource.employees.toggle', $employee) }}">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="btn btn-secondary btn-sm"
                    onclick="return confirm('{{ $employee->isActive ? 'Deactivate' : 'Activate' }} this employee?')">
                <i class="bi bi-{{ $employee->isActive ? 'person-dash' : 'person-check' }} me-1"></i>
                {{ $employee->isActive ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
    </div>
</div>

<div class="row g-4">

    {{-- ── Left Column ──────────────────────────────────────── --}}
    <div class="col-md-6">

        {{-- Personal --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0 small text-uppercase text-muted fw-semibold">Personal</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-3" style="width:140px">Full Name</td>
                            <td class="fw-semibold">{{ $employee->fullName }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Gender</td>
                            <td>{{ $employee->gender ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Civil Status</td>
                            <td>{{ $employee->civilStatus ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Date of Birth</td>
                            <td>{{ $employee->dateOfBirth?->format('F d, Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Email</td>
                            <td>{{ $employee->email ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Phone</td>
                            <td>{{ $employee->phoneNumber ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Address --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0 small text-uppercase text-muted fw-semibold">Address</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-3" style="width:140px">Street</td>
                            <td>{{ $employee->addressStreet ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Barangay</td>
                            <td>{{ $employee->addressBarangay ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">City</td>
                            <td>{{ $employee->addressCity ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Province</td>
                            <td>{{ $employee->addressProvince ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Region</td>
                            <td>{{ $employee->addressRegion ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Zip Code</td>
                            <td>{{ $employee->addressZipCode ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ── Right Column ─────────────────────────────────────── --}}
    <div class="col-md-6">

        {{-- Employment --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0 small text-uppercase text-muted fw-semibold">Employment</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-3" style="width:140px">Employee ID</td>
                            <td class="fw-semibold">{{ $employee->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Department</td>
                            <td>{{ $employee->department ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Position</td>
                            <td>{{ $employee->position ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Branch</td>
                            <td>{{ $employee->branch ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Status</td>
                            <td>
                                <span class="badge {{ $employee->employmentStatus === 'regular' ? 'bg-primary' : 'bg-secondary' }} bg-opacity-10 text-capitalize">
                                    {{ $employee->employmentStatus ?? '—' }}
                                </span>
                                @if(!$employee->isActive)
                                    <span class="badge bg-secondary bg-opacity-10 ms-1">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Role</td>
                            <td class="text-capitalize">{{ $employee->role ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Hire Date</td>
                            <td>{{ $employee->hireDate?->format('F d, Y') ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Compensation --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0 small text-uppercase text-muted fw-semibold">Compensation</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-3" style="width:140px">Basic Salary</td>
                            <td class="fw-semibold">₱{{ number_format($employee->basicSalary, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Daily Rate</td>
                            <td>₱{{ number_format($employee->dailyRate, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-3">Hourly Rate</td>
                            <td>₱{{ number_format($employee->hourlyRate, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0 small text-uppercase text-muted fw-semibold">Schedule</h3>
            </div>
            <div class="card-body">
                @if($employee->currentSchedule && $employee->currentSchedule->template)
                    @php $tpl = $employee->currentSchedule->template; @endphp
                    <div class="fw-semibold mb-2">{{ $tpl->name }}</div>
                    <div class="d-flex gap-2 flex-wrap">
                        @php
                            $dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                        @endphp
                        @foreach($tpl->days as $day)
                            <span class="badge {{ $day->is_working_day ? 'bg-primary bg-opacity-10 text-primary' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                                {{ $dayNames[$day->day_of_week] }}
                                @if($day->is_working_day && $day->shift_in)
                                    <small class="d-block text-muted">
                                        {{ substr($day->shift_in, 0, 5) }}–{{ substr($day->shift_out ?? '', 0, 5) }}
                                    </small>
                                @endif
                            </span>
                        @endforeach
                    </div>
                    <div class="text-muted small mt-2">
                        Effective: {{ $employee->currentSchedule->effective_date->format('M d, Y') }}
                    </div>
                @else
                    <span class="text-muted small">No schedule assigned</span>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Active Loans --}}
@if($employee->loans->count())
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title mb-0 small text-uppercase text-muted fw-semibold">Active Loans</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Monthly Amortization</th>
                    <th>Start Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->loans as $loan)
                <tr>
                    <td>{{ $loan->loan_type_name }}</td>
                    <td>₱{{ number_format($loan->amount, 2) }}</td>
                    <td>₱{{ number_format($loan->monthly_amortization, 2) }}</td>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($loan->start_date)->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Leave Balances --}}
@if($employee->leaveBalances->count())
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0 small text-uppercase text-muted fw-semibold">Leave Balances</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Leave Type</th>
                    <th class="text-end">Entitled</th>
                    <th class="text-end">Used</th>
                    <th class="text-end">Pending</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee->leaveBalances as $lb)
                <tr>
                    <td>{{ $lb->leaveType->name ?? '—' }}</td>
                    <td class="text-end">{{ $lb->entitled_days }}</td>
                    <td class="text-end">{{ $lb->used_days }}</td>
                    <td class="text-end">{{ $lb->pending_days }}</td>
                    <td class="text-end fw-semibold">{{ $lb->balance }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection