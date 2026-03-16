@extends('layouts.main')

@section('title', 'My Schedule')

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">My Schedule</li>
    </ol>
@endsection

@push('styles')
<style>
    /* ── Calendar Grid ───────────────────────────────────────────────── */
    .cal-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 3px;
    }
    .cal-table th {
        text-align: center;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: .4rem 0;
        color: #6c757d;
    }
    .cal-cell {
        vertical-align: top;
        height: 88px;
        padding: .4rem .5rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #fff;
        font-size: .78rem;
        cursor: default;
        transition: background .1s;
    }
    .cal-cell.is-off-month {
        background: #f8f9fa;
        opacity: .4;
    }
    .cal-cell.is-rest {
        background: #f8f9fa;
    }
    .cal-cell.is-today {
        border: 2px solid #6c757d;
    }
    .cal-cell.is-today .cal-day-num {
        background: #6c757d;
        color: #fff;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .73rem;
    }

    /* ── Day number ─────────────────────────────────────────────────── */
    .cal-day-num {
        font-weight: 700;
        font-size: .82rem;
        line-height: 1;
    }

    /* ── Status pill ────────────────────────────────────────────────── */
    .cal-status {
        display: inline-block;
        font-size: .6rem;
        font-weight: 600;
        padding: .05rem .35rem;
        border-radius: 2px;
        margin-top: .2rem;
        border: 1px solid transparent;
    }
    .cal-status-present    { border-color: #adb5bd; color: #495057; }
    .cal-status-absent     { border-color: #adb5bd; color: #868e96; }
    .cal-status-late       { border-color: #adb5bd; color: #495057; font-style: italic; }
    .cal-status-half       { border-color: #adb5bd; color: #495057; }
    .cal-status-leave      { border-color: #6c757d; color: #495057; }
    .cal-status-holiday    { border-color: #6c757d; color: #495057; }
    .cal-status-rest       { color: #adb5bd; font-style: italic; }
    .cal-status-incomplete { color: #adb5bd; }

    /* ── Attendance times ───────────────────────────────────────────── */
    .cal-times {
        font-size: .58rem;
        color: #6c757d;
        margin-top: .18rem;
        line-height: 1.45;
    }
    .cal-times .bi {
        font-size: .52rem;
        vertical-align: middle;
    }

    /* ── Holiday tag ────────────────────────────────────────────────── */
    .cal-holiday {
        font-size: .58rem;
        color: #495057;
        border-top: 1px solid #dee2e6;
        margin-top: .2rem;
        padding-top: .15rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Summary chips ──────────────────────────────────────────────── */
    .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .28rem .65rem;
        border: 1px solid #dee2e6;
        border-radius: 2rem;
        font-size: .78rem;
        font-weight: 600;
        background: #fff;
    }
    .summary-chip .chip-count {
        font-size: .9rem;
        font-weight: 700;
        color: #343a40;
    }
    .summary-chip .chip-label {
        color: #6c757d;
        font-weight: 500;
    }

    /* ── Today info boxes ───────────────────────────────────────────── */
    .schedule-info-box {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .8rem 1rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #fff;
        height: 100%;
    }
    .schedule-info-box .sib-icon {
        width: 34px;
        height: 34px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #6c757d;
        font-size: 1rem;
    }
    .schedule-info-box .sib-label {
        font-size: .68rem;
        color: #6c757d;
        line-height: 1;
        margin-bottom: .2rem;
    }
    .schedule-info-box .sib-value {
        font-weight: 600;
        font-size: .9rem;
        color: #343a40;
        line-height: 1.2;
    }
    .schedule-info-box .sib-sub {
        font-size: .65rem;
        color: #868e96;
        margin-top: .12rem;
    }

    /* ── Legend ─────────────────────────────────────────────────────── */
    .legend-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: .2rem .9rem;
        padding-top: .6rem;
        border-top: 1px solid #dee2e6;
        margin-top: .5rem;
    }
    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .71rem;
        color: #6c757d;
    }
    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        border: 1px solid #adb5bd;
        display: inline-block;
        flex-shrink: 0;
    }
    .legend-dot.dot-present  { background: #343a40; }
    .legend-dot.dot-absent   { background: #fff; }
    .legend-dot.dot-late     { background: #6c757d; }
    .legend-dot.dot-leave    { background: #495057; border-style: dashed; }
    .legend-dot.dot-holiday  { background: #dee2e6; }
    .legend-dot.dot-rest     { background: #f8f9fa; }
    .legend-today-box {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid #6c757d;
        border-radius: 2px;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')

{{-- ── Page Header ──────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0 font-weight-bold">My Schedule</h4>
    </div>
    <span class="summary-chip">
        <span class="chip-count">{{ $todayData['date']->format('d') }}</span>
        <span class="chip-label">{{ $todayData['date']->format('D, M Y') }}</span>
    </span>
</div>

{{-- ── Today's Schedule Card ───────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            Today's Schedule
        </h3>
        <div class="card-tools">
            <span class="badge badge-secondary">
                {{ $todayData['date']->format('l, F j, Y') }}
            </span>
        </div>
    </div>
    <div class="card-body">

        @if($todayData['isRestDay'])
            <p class="text-muted mb-3 small">
                <i class="bi bi-info-circle mr-1"></i>
                Today is a scheduled rest day.
            </p>
        @elseif($todayData['leave'])
            <p class="text-muted mb-3 small">
                <i class="bi bi-info-circle mr-1"></i>
                You are on <strong>{{ $todayData['leave']->leaveType->name }}</strong> today.
            </p>
        @endif

        <div class="row">
            <div class="col-md-4 mb-2">
                <div class="schedule-info-box">
                    <div class="sib-icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <div class="sib-label">Status</div>
                        <div class="sib-value">{{ $todayData['status'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="schedule-info-box">
                    <div class="sib-icon">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <div>
                        <div class="sib-label">Time In</div>
                        <div class="sib-value">
                            {{ $todayData['attendance']?->time_in
                                ? \Carbon\Carbon::parse($todayData['attendance']->time_in)->format('h:i A')
                                : '—' }}
                        </div>
                        <div class="sib-sub">
                            Scheduled: {{ \Carbon\Carbon::parse($todayData['workStart'])->format('h:i A') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="schedule-info-box">
                    <div class="sib-icon">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <div>
                        <div class="sib-label">Time Out</div>
                        <div class="sib-value">
                            {{ $todayData['attendance']?->time_out
                                ? \Carbon\Carbon::parse($todayData['attendance']->time_out)->format('h:i A')
                                : '—' }}
                        </div>
                        <div class="sib-sub">
                            Scheduled: {{ \Carbon\Carbon::parse($todayData['workEnd'])->format('h:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($todayData['holiday'])
            <div class="callout callout-default mt-3 mb-0">
                <h6 class="mb-0">
                    <i class="bi bi-flag mr-1"></i>
                    {{ $todayData['holiday']['name'] }}
                    &mdash;
                    <span class="text-muted font-weight-normal">
                        {{ $todayData['holiday']['type'] === 'regular' ? 'Regular Holiday (200%)' : 'Special Non-Working Holiday (130%)' }}
                    </span>
                </h6>
            </div>
        @endif

    </div>
</div>

{{-- ── Monthly Summary Chips ───────────────────────────────────────────── --}}
<div class="mb-3 d-flex flex-wrap gap-1" style="gap:.4rem">
    <span class="summary-chip">
        <span class="chip-count">{{ $summary['present'] }}</span>
        <span class="chip-label">Present</span>
    </span>
    <span class="summary-chip">
        <span class="chip-count">{{ $summary['late'] }}</span>
        <span class="chip-label">Late</span>
    </span>
    <span class="summary-chip">
        <span class="chip-count">{{ $summary['absent'] }}</span>
        <span class="chip-label">Absent</span>
    </span>
    <span class="summary-chip">
        <span class="chip-count">{{ $summary['leave'] }}</span>
        <span class="chip-label">On Leave</span>
    </span>
</div>

{{-- ── Calendar Card ───────────────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            {{ $date->format('F Y') }}
        </h3>
        <div class="card-tools d-flex align-items-center">
            <a href="{{ route('employee.schedule.index', ['month' => $prevMonth]) }}"
               class="btn btn-sm btn-secondary mr-1">
                <i class="bi bi-chevron-left"></i>
            </a>
            @if($date->format('Y-m') !== now()->format('Y-m'))
                <a href="{{ route('employee.schedule.index') }}"
                   class="btn btn-sm btn-secondary mr-1">
                    Today
                </a>
            @endif
            <a href="{{ route('employee.schedule.index', ['month' => $nextMonth]) }}"
               class="btn btn-sm btn-secondary">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-2">

        <table class="cal-table">
            <thead>
                <tr>
                    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach(array_chunk($calendarCells, 7) as $week)
                    <tr>
                        @foreach($week as $cell)
                            @php
                                $att     = $cell['attendance'];
                                $leave   = $cell['leave'];
                                $holiday = $cell['holiday'];

                                $cellClass = 'cal-cell';
                                if (! $cell['isCurrentMonth']) $cellClass .= ' is-off-month';
                                if ($cell['isRestDay'])        $cellClass .= ' is-rest';
                                if ($cell['isToday'])          $cellClass .= ' is-today';

                                if (! $cell['isCurrentMonth']) {
                                    $statusClass = '';
                                    $statusLabel = '';
                                } elseif ($cell['isRestDay']) {
                                    $statusClass = 'cal-status-rest';
                                    $statusLabel = 'Rest';
                                } elseif ($leave) {
                                    $statusClass = 'cal-status-leave';
                                    $statusLabel = $leave->leaveType->name ?? 'Leave';
                                } elseif ($att) {
                                    [$statusClass, $statusLabel] = match($att->status) {
                                        'present'    => ['cal-status-present',    'Present'],
                                        'absent'     => ['cal-status-absent',     'Absent'],
                                        'late'       => ['cal-status-late',       'Late'],
                                        'half_day'   => ['cal-status-half',       'Half Day'],
                                        'leave'      => ['cal-status-leave',      'On Leave'],
                                        'holiday'    => ['cal-status-holiday',    'Holiday'],
                                        'incomplete' => ['cal-status-incomplete', 'Incomplete'],
                                        default      => ['cal-status-incomplete', ucfirst($att->status)],
                                    };
                                } elseif ($holiday) {
                                    $statusClass = 'cal-status-holiday';
                                    $statusLabel = 'Holiday';
                                } elseif ($cell['isCurrentMonth'] && $cell['date']->isPast()) {
                                    $statusClass = 'cal-status-absent';
                                    $statusLabel = 'No record';
                                } else {
                                    $statusClass = '';
                                    $statusLabel = '';
                                }
                            @endphp

                            <td class="{{ $cellClass }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="cal-day-num">{{ $cell['date']->day }}</span>
                                </div>

                                @if($statusLabel)
                                    <div>
                                        <span class="cal-status {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                @endif

                                @if($att && $att->time_in && in_array($att->status, ['present','late','half_day','incomplete']))
                                    <div class="cal-times">
                                        <i class="bi bi-arrow-right"></i>
                                        {{ \Carbon\Carbon::parse($att->time_in)->format('h:i A') }}
                                        @if($att->time_out)
                                            <br>
                                            <i class="bi bi-arrow-left"></i>
                                            {{ \Carbon\Carbon::parse($att->time_out)->format('h:i A') }}
                                        @endif
                                    </div>
                                @endif

                                @if($holiday && $cell['isCurrentMonth'])
                                    <div class="cal-holiday" title="{{ $holiday['name'] }}">
                                        {{ $holiday['name'] }}
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Legend --}}
        <div class="legend-wrap">
            <span class="legend-item">
                <span class="legend-dot dot-present"></span> Present
            </span>
            <span class="legend-item">
                <span class="legend-dot dot-late"></span> Late
            </span>
            <span class="legend-item">
                <span class="legend-dot dot-absent"></span> Absent / No record
            </span>
            <span class="legend-item">
                <span class="legend-dot dot-leave"></span> On Leave
            </span>
            <span class="legend-item">
                <span class="legend-dot dot-holiday"></span> Holiday
            </span>
            <span class="legend-item">
                <span class="legend-dot dot-rest"></span> Rest Day
            </span>
            <span class="legend-item">
                <span class="legend-today-box"></span> Today
            </span>
        </div>

    </div>
</div>

{{-- ── Schedule & Leave Info ───────────────────────────────────────────── --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Work Schedule</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width:45%">Assigned Schedule</td>
                            <td class="font-weight-bold">
                                {{ $todayData['template']->name ?? 'No Schedule Assigned' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Time In</td>
                            <td>
                                {{ isset($todayData['template']) ? \Carbon\Carbon::parse($todayData['template']->shift_in)->format('h:i A') : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Time Out</td>
                            <td>
                                {{ isset($todayData['template']) ? \Carbon\Carbon::parse($todayData['template']->shift_out)->format('h:i A') : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Rest Day(s)</td>
                            <td>
                                @if(count($todayData['restDaysList']) > 0)
                                    {{ implode(', ', $todayData['restDaysList']) }}
                                @else
                                    <span class="text-muted">None configured</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Leave Balances ({{ date('Y') }})</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        @forelse($leaveBalances as $balance)
                            <tr>
                                <td class="text-muted" style="width:65%">{{ $balance->leaveType->name }}</td>
                                <td class="font-weight-bold text-right">
                                    {{ number_format($balance->balance, 1) }} days
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">
                                    No leave balances found for this year.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection