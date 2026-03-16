@extends('layouts.main')

@section('title', 'Payroll Periods')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Payroll Periods</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">Payroll Periods</h4>
        <p class="text-muted mb-0">Manage cutoff periods and control payroll workflow</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPeriodModal">
        <i class="fas fa-plus me-1"></i> New Period
    </button>
</div>

{{-- Workflow Guide --}}
<div class="alert alert-secondary border-start border-4 border-secondary mb-4 py-2">
    <small class="text-muted">
        <strong>Workflow:</strong>
        <span class="badge bg-secondary mx-1">Draft</span>
        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:.65rem"></i>
        <span class="badge bg-warning text-dark mx-1">Processing</span>
        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:.65rem"></i>
        <span class="badge bg-success mx-1">Released</span>
        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:.65rem"></i>
        <span class="badge bg-dark mx-1">Closed</span>
        &mdash; Employees see payslips only after <strong>Released</strong>.
    </small>
</div>

{{-- Periods Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Period</th>
                        <th>Type</th>
                        <th>Coverage</th>
                        <th>Pay Date</th>
                        <th class="text-center">Records</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periods as $period)
                    <tr>
                        {{-- Period Label --}}
                        <td>
                            <span class="fw-semibold">{{ $period->label }}</span>
                        </td>

                        {{-- Type --}}
                        <td>
                            <small class="text-muted">{{ $period->period_type }}</small>
                        </td>

                        {{-- Coverage dates --}}
                        <td>
                            <small class="text-muted">
                                {{ $period->start_date->format('M d') }}
                                &ndash;
                                {{ $period->end_date->format('M d, Y') }}
                            </small>
                        </td>

                        {{-- Pay Date --}}
                        <td>
                            <small>{{ $period->pay_date->format('M d, Y') }}</small>
                        </td>

                        {{-- Records count --}}
                        <td class="text-center">
                            <small>{{ $period->records_count }}</small>
                        </td>

                        {{-- Status Badge --}}
                        <td class="text-center">
                            @switch($period->status)
                                @case('draft')
                                    <span class="badge bg-secondary">Draft</span>
                                    @break
                                @case('processing')
                                    <span class="badge bg-warning text-dark">Processing</span>
                                    @break
                                @case('released')
                                    <span class="badge bg-success">Released</span>
                                    @break
                                @case('closed')
                                    <span class="badge bg-dark">Closed</span>
                                    @break
                            @endswitch
                        </td>

                        {{-- Actions --}}
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">

                                {{-- View / Process button (context-sensitive) --}}
@if ($period->isDraft())
    <form action="{{ route('accounting.payroll.periods.update-status', $period) }}" method="POST" class="d-inline">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-sm btn-outline-warning"
                title="Start Processing"
                onclick="return confirm('Mark this period as Processing?')">
            <i class="bi bi-play-fill"></i>
        </button>
    </form>

@elseif ($period->isProcessing())
    <a href="{{ route('accounting.payroll.periods.process', $period) }}"
       class="btn btn-sm btn-outline-secondary" title="Compute Payroll">
        <i class="bi bi-calculator"></i>
    </a>
    <a href="{{ route('accounting.payroll.periods.records', $period) }}"
       class="btn btn-sm btn-outline-secondary" title="View Records">
        <i class="bi bi-list-ul"></i>
    </a>
    <form action="{{ route('accounting.payroll.periods.update-status', $period) }}" method="POST" class="d-inline">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-sm btn-outline-success"
                title="Release Payslips"
                onclick="return confirm('Release payslips? Employees will be able to view them.')">
            <i class="bi bi-send-fill"></i>
        </button>
    </form>

@elseif ($period->isReleased())
    <a href="{{ route('accounting.payroll.periods.records', $period) }}"
       class="btn btn-sm btn-outline-secondary" title="View Records">
        <i class="bi bi-list-ul"></i>
    </a>
    <a href="{{ route('accounting.payroll.periods.summary', $period) }}"
       class="btn btn-sm btn-outline-secondary" title="Summary">
        <i class="bi bi-bar-chart-line"></i>
    </a>
    <form action="{{ route('accounting.payroll.periods.update-status', $period) }}" method="POST" class="d-inline">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-sm btn-outline-dark"
                title="Close Period"
                onclick="return confirm('Close this payroll period? This cannot be undone.')">
            <i class="bi bi-lock-fill"></i>
        </button>
    </form>

@elseif ($period->isClosed())
    <a href="{{ route('accounting.payroll.periods.records', $period) }}"
       class="btn btn-sm btn-outline-secondary" title="View Records">
        <i class="bi bi-list-ul"></i>
    </a>
    <a href="{{ route('accounting.payroll.periods.summary', $period) }}"
       class="btn btn-sm btn-outline-secondary" title="Summary">
        <i class="bi bi-bar-chart-line"></i>
    </a>
@endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-alt mb-2 d-block" style="font-size:1.5rem"></i>
                            No payroll periods yet. Create one to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($periods->hasPages())
            <div class="px-3 py-2 border-top">
                {{ $periods->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ===== CREATE PERIOD MODAL ===== --}}
<div class="modal fade" id="createPeriodModal" tabindex="-1"
     aria-labelledby="createPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="createPeriodModalLabel">New Payroll Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('accounting.payroll.periods.store') }}" method="POST">
                @csrf

                <div class="modal-body">

                    {{-- Period Type --}}
                    <div class="mb-3">
                        <label class="form-label">Period Type <span class="text-danger">*</span></label>
                        <select name="period_type" class="form-select @error('period_type') is-invalid @enderror" required
                                id="periodTypeSelect">
                            <option value="" disabled selected>Select type</option>
                            <option value="1st-15th"  {{ old('period_type') === '1st-15th'  ? 'selected' : '' }}>1st – 15th</option>
                            <option value="16th-end"  {{ old('period_type') === '16th-end'  ? 'selected' : '' }}>16th – End of Month</option>
                        </select>
                        @error('period_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Month --}}
                        <div class="col-md-6">
                            <label class="form-label">Month <span class="text-danger">*</span></label>
                            <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                <option value="" disabled selected>Select month</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ old('month') == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Year --}}
                        <div class="col-md-6">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <select name="year" class="form-select @error('year') is-invalid @enderror" required>
                                <option value="" disabled selected>Select year</option>
                                @foreach(range(now()->year - 1, now()->year + 1) as $y)
                                    <option value="{{ $y }}" {{ (old('year', now()->year) == $y) ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Coverage Preview (read-only, updated by JS) --}}
                    <div class="mb-3">
                        <label class="form-label text-muted">Coverage (auto-computed)</label>
                        <input type="text" id="coveragePreview" class="form-control bg-light"
                               readonly placeholder="Select type, month, and year above">
                    </div>

                    {{-- Pay Date --}}
                    <div class="mb-3">
                        <label class="form-label">Pay Date <span class="text-danger">*</span></label>
                        <input type="date" name="pay_date"
                               class="form-control @error('pay_date') is-invalid @enderror"
                               value="{{ old('pay_date') }}" required>
                        <div class="form-text">Date employees will receive their pay.</div>
                        @error('pay_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="mb-1">
                        <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2" placeholder="e.g. Holiday adjustments included">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Period</button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * Auto-compute and preview the coverage dates
 * whenever the user changes type, month, or year.
 */
(function () {
    const monthNames = [
        '', 'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    function daysInMonth(month, year) {
        return new Date(year, month, 0).getDate();
    }

    function updatePreview() {
        const type  = document.querySelector('[name="period_type"]').value;
        const month = parseInt(document.querySelector('[name="month"]').value);
        const year  = parseInt(document.querySelector('[name="year"]').value);
        const preview = document.getElementById('coveragePreview');

        if (!type || !month || !year) {
            preview.value = '';
            return;
        }

        const mName = monthNames[month];

        if (type === '1st-15th') {
            preview.value = `${mName} 1 – 15, ${year}`;
        } else {
            const last = daysInMonth(month, year);
            preview.value = `${mName} 16 – ${last}, ${year}`;
        }
    }

    ['[name="period_type"]', '[name="month"]', '[name="year"]'].forEach(sel => {
        document.querySelector(sel)?.addEventListener('change', updatePreview);
    });

    // Re-open modal with old input if validation failed
    @if ($errors->any())
        const modal = new bootstrap.Modal(document.getElementById('createPeriodModal'));
        modal.show();
        updatePreview();
    @endif
})();
</script>
@endpush