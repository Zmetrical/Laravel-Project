@extends('layouts.main')

@section('title', 'Edit — ' . $employee->fullName)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('hresource.employees.index') }}">Employees</a></li>
        <li class="breadcrumb-item">
            <a href="{{ route('hresource.employees.show', $employee) }}">{{ $employee->fullName }}</a>
        </li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">Edit — {{ $employee->fullName }}</h3>
        <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $employee->id }}</span>
    </div>
    <div class="card-body">
        @include('hresource.employees._form', [
            'employee'          => $employee,
            'scheduleTemplates' => $scheduleTemplates,
            'action'            => route('hresource.employees.update', $employee),
            'method'            => 'PATCH',
        ])
    </div>
</div>

@endsection