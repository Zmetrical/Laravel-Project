@extends('layouts.main')

@section('title', 'Add Employee')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('hresource.employees.index') }}">Employees</a></li>
        <li class="breadcrumb-item active">Add Employee</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Add New Employee</h3>
    </div>
    <div class="card-body">
        @include('hresource.employees._form', [
            'employee'          => null,
            'scheduleTemplates' => $scheduleTemplates,
            'action'            => route('hresource.employees.store'),
            'method'            => 'POST',
        ])
    </div>
</div>

@endsection