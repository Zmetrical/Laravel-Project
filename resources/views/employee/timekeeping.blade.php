@extends('layouts.main')

@section('title', 'Timekeeping')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Timekeeping</li>
    </ol>
@endsection

@section('content')

{{-- SweetAlert flash messages --}}
<x-alerts />

{{-- Test mode banner --}}
@if ($testMode)
    <div class="alert alert-warning d-flex align-items-center justify-content-between py-2 mb-3">
        <span class="small">
            <strong>Testing Mode Active</strong> —
            Date: <strong>{{ \Carbon\Carbon::parse($testDate)->format('M d, Y') }}</strong>,
            Time: <strong>{{ \Carbon\Carbon::createFromFormat('H:i', $testTime)->format('h:i A') }}</strong>
        </span>
        <form method="POST" action="{{ route('employee.timekeeping.test-mode') }}" class="mb-0">
            @csrf
            <input type="hidden" name="action" value="disable">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Exit Testing Mode</button>
        </form>
    </div>
@endif

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Timekeeping</h4>
        <small class="text-muted">Track your daily attendance and work hours</small>
    </div>
</div>

{{-- Live Clock --}}
<div class="card mb-3">
    <div class="card-body text-center py-3">
        <h2 id="liveClock" class="mb-1 fw-bold" style="letter-spacing:3px;font-variant-numeric:tabular-nums;">
            --:--:--
        </h2>
        <p id="liveDate" class="text-muted small mb-0"></p>
    </div>
</div>

{{-- Quick Action + Calendar --}}
<div class="row g-3 mb-3">

    {{-- Quick Action --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">Quick Action</h6>
            </div>
            <div class="card-body">

                @if (! $isClockedIn)
                    {{-- CLOCK IN --}}
                    <form method="POST" action="{{ route('employee.timekeeping.clock-in') }}">
                        @csrf
                        @if ($testMode)
                            <div class="mb-2">
                                <label class="form-label small mb-1">Test Time</label>
                                <input type="time" name="test_time"
                                    class="form-control form-control-sm"
                                    value="{{ $testTime }}">
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Clock In
                        </button>
                    </form>
                @else
                    {{-- CLOCK OUT --}}
                    <form method="POST" action="{{ route('employee.timekeeping.clock-out') }}">
                        @csrf
                        @if ($testMode)
                            <div class="mb-2">
                                <label class="form-label small mb-1">Test Time</label>
                                <input type="time" name="test_time"
                                    class="form-control form-control-sm"
                                    value="{{ $testTime }}">
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary w-100 mb-2">Clock Out</button>
                    </form>

                    {{-- Elapsed timer --}}
                    <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                        <span class="text-muted small">Hours Worked</span>
                        <span id="elapsedTimer" class="fw-bold"
                            data-clock-in="{{ $todayRecord->time_in }}"
                            data-clock-date="{{ $activeDate }}">
                            00:00:00
                        </span>
                    </div>
                @endif

                <hr class="my-3">

                @if (! $testMode)
                    {{-- Enable Testing Mode --}}
                    <form method="POST" action="{{ route('employee.timekeeping.test-mode') }}">
                        @csrf
                        <input type="hidden" name="action" value="enable">
                        <input type="hidden" name="test_date" value="{{ now()->toDateString() }}">
                        <input type="hidden" name="test_time" value="{{ now()->format('H:i') }}">
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                            Enable Testing Mode
                        </button>
                    </form>
                @else
                    {{-- Debug Tools --}}
                    <p class="small fw-semibold text-muted mb-2">Testing Tools</p>

                    {{-- Selected date display — updated by clicking the calendar --}}
                    <div class="mb-3">
                        <label class="form-label small mb-1">
                            Selected Date 
                        </label>
                        <div id="debugDateDisplay"
                            class="form-control form-control-sm bg-light small"
                            style="cursor:default;">
                            {{ \Carbon\Carbon::parse($testDate)->format('D, M d, Y') }}
                        </div>
                    </div>

                    {{-- Delete attendance for selected date --}}
                    <form method="POST"
                        action="{{ route('employee.timekeeping.delete-attendance') }}"
                        id="debugDeleteForm"
                        onsubmit="return confirm('Delete attendance for ' + document.getElementById('debugDateDisplay').textContent.trim() + '?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="date" id="debugDeleteDateInput" value="{{ $testDate }}">
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                            Delete Attendance
                        </button>
                    </form>

                    {{-- Hidden form to update test date when calendar day is clicked --}}
                    <form method="POST" action="{{ route('employee.timekeeping.test-mode') }}" id="debugDateSyncForm">
                        @csrf
                        <input type="hidden" name="action" value="enable">
                        <input type="hidden" name="test_date" id="debugTestDateInput" value="{{ $testDate }}">
                        <input type="hidden" name="test_time" value="{{ $testTime }}">
                    </form>
                @endif

            </div>
        </div>
    </div>

    {{-- Attendance Calendar --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">Attendance Calendar</h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $calPrevHref }}" class="btn btn-sm btn-outline-secondary px-2">&#8592;</a>
                    <span class="small fw-semibold" style="min-width:140px;text-align:center;">
                        {{ $calendarLabel }}
                    </span>
                    <a href="{{ $calNextHref }}" class="btn btn-sm btn-outline-secondary px-2">&#8594;</a>
                </div>
            </div>
            <div class="card-body pt-2">

                {{-- Day-of-week headers --}}
                <div class="row g-0 mb-1 text-center">
                    @foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
                        <div class="col small text-muted fw-semibold py-1">{{ $d }}</div>
                    @endforeach
                </div>

                {{-- Calendar rows --}}
                @foreach (array_chunk($calendarDays, 7) as $week)
                    <div class="row g-1 mb-1">
                        @foreach ($week as $cell)
                            <div class="col">
                                @if ($cell === null)
                                    <div class="border rounded p-1 opacity-25 bg-light"
                                        style="min-height:52px;font-size:11px;"></div>
                                @else
                                    @php
                                        $att    = $cell['attendance'];
                                        $hasAtt = $att && $att->time_in;

                                        $cellClass = 'border rounded p-1 text-center debug-cal-cell';
                                        if ($cell['is_today'])    $cellClass .= ' border-primary';
                                        if ($cell['is_rest_day']) $cellClass .= ' bg-light';
                                        if ($cell['is_leave'])    $cellClass .= ' bg-light';
                                        if ($cell['date'] === $testDate) $cellClass .= ' border-secondary border-2';

                                        $numClass  = 'fw-bold ';
                                        $numClass .= ($cell['is_today'] || $hasAtt) ? 'text-primary' : 'text-muted';
                                    @endphp

                                    <div class="{{ $cellClass }}"
                                        style="min-height:52px;font-size:11px;cursor:pointer;"
                                        data-date="{{ $cell['date'] }}"
                                        data-label="{{ \Carbon\Carbon::parse($cell['date'])->format('D, M d, Y') }}"
                                        onclick="selectDebugDate(this)">

                                        <div class="{{ $numClass }}">{{ $cell['day'] }}</div>

                                        @if ($cell['holiday'])
                                            <div>
                                                <span class="badge bg-secondary" style="font-size:9px;">
                                                    {{ \Illuminate\Support\Str::limit($cell['holiday']['name'], 8, '') }}
                                                </span>
                                            </div>
                                        @endif

                                        @if ($hasAtt)
                                            @if (! $att->time_out)
                                                <span class="badge bg-primary" style="font-size:9px;">Ongoing</span>
                                            @elseif ($att->status === 'late')
                                                <span class="badge bg-secondary" style="font-size:9px;">Late</span>
                                            @else
                                                <span class="badge bg-primary" style="font-size:9px;">Done</span>
                                            @endif
                                        @elseif ($cell['is_leave'])
                                            <span class="badge bg-secondary" style="font-size:9px;">Leave</span>
                                        @elseif ($cell['is_rest_day'])
                                            <span class="badge bg-secondary" style="font-size:9px;">Off</span>
                                        @elseif (! $cell['is_past'])
                                            <div class="text-muted" style="font-size:9px;">
                                                {{ $cell['shift']['type'] === 'night' ? 'Night' : 'Day' }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach

                {{-- Legend --}}
                <div class="border-top mt-2 pt-2">
                    <div class="row g-2" style="font-size:11px;">
                        <div class="col-6 text-muted d-flex align-items-center gap-1">
                            <span class="badge bg-primary">&nbsp;&nbsp;</span> Present / Ongoing
                        </div>
                        <div class="col-6 text-muted d-flex align-items-center gap-1">
                            <span class="badge bg-secondary">&nbsp;&nbsp;</span> Late / Leave / Day Off
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- Quick Stats --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small mb-1">This Week</p>
                <h5 class="mb-0 fw-bold">{{ $stats['week_hours'] }} hrs</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small mb-1">This Month</p>
                <h5 class="mb-0 fw-bold">{{ $stats['month_hours'] }} hrs</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <p class="text-muted small mb-1">Days Present (Month)</p>
                <h5 class="mb-0 fw-bold">{{ $stats['days_present'] }} / {{ $stats['work_days'] }}</h5>
            </div>
        </div>
    </div>
</div>

{{-- DTR Table --}}
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h6 class="card-title mb-0 fw-bold">Daily Time Record</h6>
        </div>
    </div>
    <div class="card-body">

        {{-- Filters --}}
        <form method="GET" action="{{ route('employee.timekeeping.index') }}" class="row g-2 mb-4">
            <div class="col-sm-4">
                <label class="small text-muted mb-1">Month</label>
                <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ([
                        1  => 'January',   2  => 'February', 3  => 'March',
                        4  => 'April',     5  => 'May',      6  => 'June',
                        7  => 'July',      8  => 'August',   9  => 'September',
                        10 => 'October',   11 => 'November', 12 => 'December',
                    ] as $num => $name)
                        <option value="{{ $num }}" @selected($filters['month'] == $num)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label class="small text-muted mb-1">Year</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($availableYears as $yr)
                        <option value="{{ $yr }}" @selected($filters['year'] == $yr)>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label class="small text-muted mb-1">Cutoff Period</label>
                <select name="cutoff" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="full"   @selected($filters['cutoff'] === 'full')>Full Month</option>
                    <option value="first"  @selected($filters['cutoff'] === 'first')>1st – 15th</option>
                    <option value="second" @selected($filters['cutoff'] === 'second')>16th – End</option>
                </select>
            </div>
        </form>

        {{-- Table --}}
        @if ($records->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-calendar-x d-block mb-2" style="font-size: 2rem;"></i>
                No attendance records found for this period.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-top-0">Date</th>
                            <th class="border-top-0">Time In</th>
                            <th class="border-top-0">Time Out</th>
                            <th class="border-top-0">Hours</th>
                            <th class="border-top-0 text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            @php
                                $isOngoing = $record->time_in && ! $record->time_out;
                            @endphp
                            <tr>
                                <td class="text-nowrap fw-semibold small">
                                    {{ $record->date->format('D, M d, Y') }}
                                </td>
                                <td class="text-nowrap">
                                    {{ $record->time_in
                                        ? \Carbon\Carbon::parse($record->time_in)->format('h:i A')
                                        : '—' }}
                                </td>
                                <td class="text-nowrap">
                                    {{ $record->time_out
                                        ? \Carbon\Carbon::parse($record->time_out)->format('h:i A')
                                        : '—' }}
                                </td>
                                <td class="text-nowrap">
                                    @if ($isOngoing)
                                        <span class="text-primary small fw-bold">Ongoing…</span>
                                    @elseif ($record->time_out)
                                        {{ number_format($record->hours_worked, 2) }} <small class="text-muted">hrs</small>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    @switch($record->status)
                                        @case('present')
                                            <span class="badge bg-primary">Present</span>
                                            @break
                                        @case('late')
                                            <span class="badge bg-warning text-dark">Late</span>
                                            @break
                                        @case('absent')
                                            <span class="badge bg-danger">Absent</span>
                                            @break
                                        @case('leave')
                                            <span class="badge bg-info text-dark">Leave</span>
                                            @break
                                        @case('rest_day')
                                            <span class="badge bg-light text-dark border">Rest Day</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">
                                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                            </span>
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $records->links() }}
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
/* ─────────────────────────────────────────────────────────────
   Live clock
   ───────────────────────────────────────────────────────────── */
(function () {
    function tick() {
        const now = new Date();
        document.getElementById('liveClock').textContent =
            now.toLocaleTimeString('en-US', { hour12: false });
        document.getElementById('liveDate').textContent =
            now.toLocaleDateString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
            });
    }
    tick();
    setInterval(tick, 1000);
})();

/* ─────────────────────────────────────────────────────────────
   Elapsed timer — only present in DOM when clocked in
   ───────────────────────────────────────────────────────────── */
(function () {
    const el = document.getElementById('elapsedTimer');
    if (!el) return;

    const timeIn  = el.dataset.clockIn;
    const dateStr = el.dataset.clockDate;
    if (!timeIn || !dateStr) return;

    const base = new Date(dateStr + 'T' + timeIn);

    function pad(n) { return String(n).padStart(2, '0'); }
    function tick() {
        const diff = Math.max(0, Math.floor((Date.now() - base.getTime()) / 1000));
        el.textContent =
            pad(Math.floor(diff / 3600))        + ':' +
            pad(Math.floor((diff % 3600) / 60)) + ':' +
            pad(diff % 60);
    }
    tick();
    setInterval(tick, 1000);
})();

/* ─────────────────────────────────────────────────────────────
   Debug calendar date picker
   Clicking a calendar day syncs the date to all debug inputs,
   updates the display label, and auto-submits the hidden sync
   form so the session test_date updates immediately.
   ───────────────────────────────────────────────────────────── */
function selectDebugDate(el) {
    @if ($testMode)
    const dateVal   = el.dataset.date;
    const dateLabel = el.dataset.label;

    // Sync hidden inputs
    const testInput   = document.getElementById('debugTestDateInput');
    const deleteInput = document.getElementById('debugDeleteDateInput');
    if (testInput)   testInput.value   = dateVal;
    if (deleteInput) deleteInput.value = dateVal;

    // Update the display label
    const display = document.getElementById('debugDateDisplay');
    if (display) display.textContent = dateLabel;

    // Move the highlight border to the clicked cell
    document.querySelectorAll('.debug-cal-cell').forEach(function (c) {
        c.classList.remove('border-secondary', 'border-2');
    });
    el.classList.add('border-secondary', 'border-2');

    // Auto-submit to sync session test_date without a manual button
    const syncForm = document.getElementById('debugDateSyncForm');
    if (syncForm) syncForm.submit();
    @endif
}
</script>
@endpush