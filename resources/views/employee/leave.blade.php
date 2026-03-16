@extends('layouts.main')

@section('title', 'Leave Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Leave Management</li>
    </ol>
@endsection

@section('content')

{{-- ── Flash Messages ─────────────────────────────────────────────────── --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Page Header ─────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Leave Management</h4>
        <small class="text-muted">File leave requests and track your leave credits</small>
    </div>
    <button class="btn btn-secondary" id="toggleFormBtn" onclick="toggleForm()">
        Apply for Leave
    </button>
</div>

{{-- ── Leave Balance Summary Cards ─────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    @foreach ($leaveTypes as $lt)
        @php
            $bal   = $balances->get($lt->id);
            $avail = $bal ? $bal->balance        : 0;
            $used  = $bal ? $bal->used_days      : 0;
            $total = $bal ? $bal->total_entitled : $lt->max_days_per_year;
        @endphp
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="text-muted small">{{ $lt->name }}</div>
                    <div class="fw-bold fs-5">{{ number_format($avail, 0) }} days</div>
                    <div class="text-muted" style="font-size:.75rem">
                        Used: {{ $used }} &nbsp;/&nbsp; Total: {{ $total }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ── Leave Application Form ───────────────────────────────────────────── --}}
<div class="card mb-3 {{ $errors->any() || old('leave_type_id') ? '' : 'd-none' }}"
     id="leaveFormCard">
    <div class="card-header">
        <h5 class="card-title mb-0">New Leave Application</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('employee.leave.store') }}">
            @csrf
            {{-- Hidden fields populated by JS --}}
            <input type="hidden" name="start_date" id="inputStartDate" value="{{ old('start_date') }}">
            <input type="hidden" name="end_date"   id="inputEndDate"   value="{{ old('end_date') }}">

            <div class="row g-4">

                {{-- ── LEFT: Calendar ─────────────────────────────────── --}}
                <div class="col-lg-6">
                    <label class="form-label fw-semibold">Select Leave Dates</label>

                    {{-- Month navigation --}}
                    <div class="d-flex align-items-center justify-content-between mb-2 border rounded px-3 py-2 bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="prevMonth()">&#8592;</button>
                        <span class="fw-semibold" id="calMonthLabel"></span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="nextMonth()">&#8594;</button>
                    </div>

                    {{-- Weekday headers --}}
                    <div class="row g-1 mb-1 text-center">
                        @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                            <div class="col" style="font-size:.7rem;font-weight:600;color:#6c757d;text-transform:uppercase">
                                {{ $day }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Calendar grid --}}
                    <div id="calGrid"></div>

                    {{-- Selected range info --}}
                    <div class="mt-2" id="selectedRangeInfo"></div>

                    {{-- Legend --}}
                    <div class="mt-2 p-2 border rounded bg-light">
                        <div class="row g-1" style="font-size:.7rem">
                            <div class="col-6 d-flex align-items-center gap-1">
                                <div style="width:12px;height:12px;border-radius:3px;background:rgba(var(--bs-primary-rgb),.18);border:1px solid rgba(var(--bs-primary-rgb),.5)"></div>
                                <span class="text-muted">Selected</span>
                            </div>
                            <div class="col-6 d-flex align-items-center gap-1">
                                <div style="width:12px;height:12px;border-radius:3px;background:rgba(var(--bs-primary-rgb),.07);border:1px solid rgba(var(--bs-primary-rgb),.25)"></div>
                                <span class="text-muted">In Range</span>
                            </div>
                            <div class="col-6 d-flex align-items-center gap-1">
                                <div style="width:12px;height:12px;border-radius:3px;background:rgba(var(--bs-secondary-rgb),.2);border:1px solid var(--bs-secondary)"></div>
                                <span class="text-muted">Already Filed</span>
                            </div>
                            <div class="col-6 d-flex align-items-center gap-1">
                                <div style="width:12px;height:12px;border-radius:3px;background:#f1f1f1;border:1px solid #dee2e6;opacity:.5"></div>
                                <span class="text-muted">Day Off</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── RIGHT: Leave Type + Balance + Reason ────────────── --}}
                <div class="col-lg-6">

                    {{-- Leave Type --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Leave Type</label>
                        <select name="leave_type_id"
                                id="leaveTypeSelect"
                                class="form-select @error('leave_type_id') is-invalid @enderror"
                                onchange="onTypeChange()"
                                required>
                            <option value="">— Select Leave Type —</option>
                            @foreach ($leaveTypes as $lt)
                                @php
                                    $bal   = $balances->get($lt->id);
                                    $avail = $bal ? $bal->balance        : 0;
                                    $used  = $bal ? $bal->used_days      : 0;
                                    $total = $bal ? $bal->total_entitled : $lt->max_days_per_year;
                                @endphp
                                <option value="{{ $lt->id }}"
                                        data-balance="{{ $avail }}"
                                        data-used="{{ $used }}"
                                        data-total="{{ $total }}"
                                        data-name="{{ $lt->name }}"
                                        data-desc="{{ $lt->description }}"
                                        {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                    {{ $lt->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Balance info box (mirrors OT type display box) --}}
                    <div class="mb-3">
                        <div class="border rounded p-3 bg-light" id="balanceInfoBox">
                            <span class="text-muted small">Select a leave type to see your balance</span>
                        </div>
                    </div>

                    {{-- Leave Summary box (mirrors OT estimated pay box) --}}
                    <div class="mb-3 d-none" id="leaveSummaryBox">
                        <div class="border rounded p-3 bg-light">
                            <p class="mb-1 small fw-semibold">Leave Summary</p>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Start Date</span>
                                <span class="fw-semibold" id="summaryStart">—</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">End Date</span>
                                <span class="fw-semibold" id="summaryEnd">—</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Working Days</span>
                                <span class="fw-bold" id="summaryDays">—</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Remaining After</span>
                                <span class="fw-bold" id="summaryRemaining">—</span>
                            </div>
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Reason <span class="text-muted fw-normal">(Required)</span>
                        </label>
                        <textarea name="reason"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Briefly describe the reason for your leave..."
                                  required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"
                                id="submitLeaveBtn" disabled>
                            Submit Request
                        </button>
                        <button type="button" class="btn btn-outline-secondary px-4"
                                onclick="toggleForm()">
                            Cancel
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Leave History ────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0">Leave Request History</h5>
        <small class="text-muted">{{ $history->count() }} record(s)</small>
    </div>
    <div class="card-body pb-2">

        {{-- Filters --}}
        <form method="GET" action="{{ route('employee.leave.index') }}" class="row g-2 mb-3">
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach (['pending', 'approved', 'rejected'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Leave Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach ($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ request('type') == $lt->id ? 'selected' : '' }}>
                            {{ $lt->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-secondary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('employee.leave.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>

        {{-- Table --}}
        @if ($history->isEmpty())
            <div class="text-center text-muted py-5">
                No leave requests found.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Reviewed By</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $lr)
                            <tr>
                                <td class="fw-semibold text-nowrap">
                                    {{ $lr->leaveType?->name ?? '—' }}
                                </td>
                                <td class="text-nowrap">{{ $lr->start_date->format('M d, Y') }}</td>
                                <td class="text-nowrap">{{ $lr->end_date->format('M d, Y') }}</td>
                                <td>{{ $lr->days }}d</td>
                                <td class="text-muted small" style="max-width:180px;white-space:normal">
                                    {{ $lr->reason }}
                                    @if ($lr->isRejected() && $lr->rejection_reason)
                                        <div class="text-danger mt-1">
                                            <i class="bi bi-x-circle me-1"></i>{{ $lr->rejection_reason }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @switch($lr->status)
                                        @case('pending')
                                            <span class="badge bg-secondary">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-primary">Approved</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-secondary">Rejected</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="text-nowrap text-muted small">
                                    {{ $lr->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-nowrap text-muted small">
                                    {{ $lr->reviewer?->fullName ?? '—' }}
                                </td>
                                <td class="text-center">
                                    @if ($lr->isPending())
                                        <form method="POST"
                                              action="{{ route('employee.leave.destroy', $lr->id) }}"
                                              id="withdraw-form-{{ $lr->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                onclick="confirmWithdraw({{ $lr->id }})">
                                            Cancel
                                        </button>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
/* ─────────────────────────────────────────────────────────────
   SERVER DATA
   ───────────────────────────────────────────────────────────── */
const DAY_OFFS   = @json($restDaysArray ?? [0, 6]);
// Filed dates: all pending/approved requests for this user, flattened to individual dates
const FILED_DATES = @json($filedDates ?? []);  // array of "YYYY-MM-DD" strings

/* ─────────────────────────────────────────────────────────────
   STATE
   ───────────────────────────────────────────────────────────── */
let calMonth    = new Date(); calMonth.setDate(1);
let startDate   = null;  // "YYYY-MM-DD"
let endDate     = null;
let formVisible = {{ $errors->any() || old('leave_type_id') ? 'true' : 'false' }};

/* ─────────────────────────────────────────────────────────────
   HELPERS
   ───────────────────────────────────────────────────────────── */
const pad     = n => String(n).padStart(2, '0');
const toYMD   = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
const fmtDate = s => {
    const [y,m,d] = s.split('-');
    return new Date(s+'T00:00:00').toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
};

const isOff    = ds => DAY_OFFS.includes(new Date(ds+'T00:00:00').getDay());
const isFiled  = ds => FILED_DATES.includes(ds);
const isPast   = ds => ds < toYMD(new Date());

function countWorkingDays(s, e) {
    let n = 0;
    const cur = new Date(s+'T00:00:00');
    const end = new Date(e+'T00:00:00');
    while (cur <= end) {
        if (!isOff(toYMD(cur))) n++;
        cur.setDate(cur.getDate()+1);
    }
    return n;
}

/* ─────────────────────────────────────────────────────────────
   CALENDAR
   ───────────────────────────────────────────────────────────── */
function renderCalendar() {
    const year     = calMonth.getFullYear();
    const month    = calMonth.getMonth();
    const firstDow = new Date(year, month, 1).getDay();
    const days     = new Date(year, month+1, 0).getDate();
    const prevLast = new Date(year, month, 0).getDate();

    document.getElementById('calMonthLabel').textContent =
        calMonth.toLocaleDateString('en-PH',{month:'long',year:'numeric'});

    let cells = [];
    for (let i = firstDow-1; i >= 0; i--)
        cells.push({day: prevLast-i, cur: false, ds: null});
    for (let d = 1; d <= days; d++)
        cells.push({day: d, cur: true, ds:`${year}-${pad(month+1)}-${pad(d)}`});
    while (cells.length < 42)
        cells.push({day: cells.length-firstDow-days+1, cur: false, ds: null});

    let html = '';
    for (let row = 0; row < 6; row++) {
        html += '<div class="row g-1 mb-1">';
        for (let col = 0; col < 7; col++) {
            const cell = cells[row*7+col];
            if (!cell.cur || !cell.ds) {
                html += `<div class="col"><div class="border rounded text-center opacity-25"
                    style="min-height:46px;font-size:.75rem;padding:4px">
                    <div class="fw-semibold text-muted">${cell.day}</div></div></div>`;
                continue;
            }

            const ds       = cell.ds;
            const off      = isOff(ds);
            const filed    = isFiled(ds);
            const past     = isPast(ds);
            const isStart  = ds === startDate;
            const isEnd    = ds === endDate;
            const inRange  = startDate && endDate && ds > startDate && ds < endDate;
            const selectable = !off && !filed && !past;

            let style  = 'min-height:46px;font-size:.75rem;border-radius:4px;padding:4px;border:1px solid #dee2e6;text-align:center;';
            let numCls = 'fw-semibold text-muted';
            let click  = '';
            let badge  = '';

            if (isStart || isEnd) {
                style  += 'background:rgba(var(--bs-primary-rgb),.18);border-color:rgba(var(--bs-primary-rgb),.6);cursor:pointer;';
                numCls  = 'fw-bold text-primary';
            } else if (inRange) {
                style  += 'background:rgba(var(--bs-primary-rgb),.07);border-color:rgba(var(--bs-primary-rgb),.25);cursor:pointer;';
                numCls  = 'fw-semibold text-primary';
            } else if (filed) {
                style  += 'opacity:.55;cursor:not-allowed;background:rgba(var(--bs-secondary-rgb),.12);border-color:var(--bs-secondary);';
                badge   = `<div style="font-size:.6rem" class="text-secondary mt-1">Filed</div>`;
            } else if (off || past) {
                style  += 'opacity:.35;cursor:not-allowed;';
            } else {
                style  += 'cursor:pointer;';
                numCls  = 'fw-semibold';
            }

            if (selectable) {
                click = `onclick="selectDay('${ds}')"`;
            }

            html += `<div class="col"><div style="${style}" ${click}>
                <div class="${numCls}">${cell.day}</div>${badge}
            </div></div>`;
        }
        html += '</div>';
    }

    document.getElementById('calGrid').innerHTML = html;
}

function prevMonth() {
    calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth()-1, 1);
    renderCalendar();
}
function nextMonth() {
    calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth()+1, 1);
    renderCalendar();
}

/* ─────────────────────────────────────────────────────────────
   DATE RANGE SELECTION
   ───────────────────────────────────────────────────────────── */
function selectDay(ds) {
    if (!startDate || (startDate && endDate)) {
        // First click — set start, clear end
        startDate = ds;
        endDate   = null;
    } else {
        // Second click — set end (swap if needed)
        if (ds < startDate) {
            endDate   = startDate;
            startDate = ds;
        } else {
            endDate = ds;
        }
    }

    document.getElementById('inputStartDate').value = startDate;
    document.getElementById('inputEndDate').value   = endDate ?? startDate;

    renderCalendar();
    updateRangeInfo();
    updateSummaryBox();
    updateSubmitState();
}

function updateRangeInfo() {
    const box = document.getElementById('selectedRangeInfo');
    if (!startDate) { box.innerHTML = ''; return; }

    const e    = endDate ?? startDate;
    const days = countWorkingDays(startDate, e);

    box.innerHTML = `
        <div class="border rounded p-2 bg-light">
            <div class="d-flex align-items-center justify-content-between">
                <span class="fw-semibold small">${fmtDate(startDate)}</span>
                ${endDate && endDate !== startDate
                    ? `<span class="text-muted small">→</span>
                       <span class="fw-semibold small">${fmtDate(endDate)}</span>`
                    : ''}
            </div>
            <div class="text-muted small mt-1">
                <strong>${days}</strong> working day${days !== 1 ? 's' : ''} selected
            </div>
        </div>`;
}

/* ─────────────────────────────────────────────────────────────
   BALANCE INFO + SUMMARY BOX
   ───────────────────────────────────────────────────────────── */
function onTypeChange() {
    updateBalanceInfo();
    updateSummaryBox();
    updateSubmitState();
}

function updateBalanceInfo() {
    const sel = document.getElementById('leaveTypeSelect');
    const box = document.getElementById('balanceInfoBox');

    if (!sel.value) {
        box.innerHTML = '<span class="text-muted small">Select a leave type to see your balance</span>';
        return;
    }

    const opt     = sel.options[sel.selectedIndex];
    const balance = parseFloat(opt.dataset.balance ?? 0);
    const used    = parseFloat(opt.dataset.used    ?? 0);
    const total   = parseFloat(opt.dataset.total   ?? 0);
    const name    = opt.dataset.name ?? '';
    const desc    = opt.dataset.desc ?? '';
    const pct     = total > 0 ? Math.round((balance / total) * 100) : 0;

    box.innerHTML = `
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="flex-grow-1">
                <div class="fw-semibold">${name}</div>
                ${desc ? `<div class="text-muted small mt-1">${desc}</div>` : ''}
                <div class="mt-2">
                    <div class="progress mb-1" style="height:4px;border-radius:2px">
                        <div class="progress-bar" style="width:${pct}%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Used: <strong>${used}</strong></span>
                        <span>Total: <strong>${total}</strong></span>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <div class="fs-4 fw-bold text-primary lh-1">${balance}</div>
                <div class="text-muted small">days left</div>
            </div>
        </div>`;
}

function updateSummaryBox() {
    const box = document.getElementById('leaveSummaryBox');
    if (!startDate) { box.classList.add('d-none'); return; }

    const e    = endDate ?? startDate;
    const days = countWorkingDays(startDate, e);

    const sel  = document.getElementById('leaveTypeSelect');
    const opt  = sel.options[sel.selectedIndex];
    const bal  = sel.value ? parseFloat(opt.dataset.balance ?? 0) : null;
    const rem  = bal !== null ? (bal - days) : null;

    document.getElementById('summaryStart').textContent     = fmtDate(startDate);
    document.getElementById('summaryEnd').textContent       = fmtDate(e);
    document.getElementById('summaryDays').textContent      = `${days} day${days !== 1 ? 's' : ''}`;
    document.getElementById('summaryRemaining').textContent = rem !== null
        ? `${rem} day${rem !== 1 ? 's' : ''}`
        : '—';

    box.classList.remove('d-none');
}

function updateSubmitState() {
    const sel  = document.getElementById('leaveTypeSelect');
    const btn  = document.getElementById('submitLeaveBtn');
    const hasType  = !!sel.value;
    const hasDate  = !!startDate;

    if (!hasType || !hasDate) { btn.disabled = true; return; }

    const e    = endDate ?? startDate;
    const days = countWorkingDays(startDate, e);
    const opt  = sel.options[sel.selectedIndex];
    const bal  = parseFloat(opt.dataset.balance ?? 0);

    btn.disabled = days < 1 || days > bal;
}

/* ─────────────────────────────────────────────────────────────
   FORM TOGGLE
   ───────────────────────────────────────────────────────────── */
function toggleForm() {
    formVisible = !formVisible;
    const card = document.getElementById('leaveFormCard');
    const btn  = document.getElementById('toggleFormBtn');

    if (formVisible) {
        card.classList.remove('d-none');
        btn.textContent = 'Cancel';
        card.scrollIntoView({behavior:'smooth', block:'start'});
    } else {
        card.classList.add('d-none');
        btn.textContent = 'Apply for Leave';
    }
}

if (formVisible) {
    document.getElementById('toggleFormBtn').textContent = 'Cancel';
}

/* ─────────────────────────────────────────────────────────────
   WITHDRAW CONFIRM
   ───────────────────────────────────────────────────────────── */
function confirmWithdraw(id) {
    Swal.fire({
        title: 'Cancel this request?',
        text: 'This will permanently remove the leave application.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel It',
        cancelButtonText: 'Keep it',
        confirmButtonColor: 'var(--bs-primary)',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('withdraw-form-' + id).submit();
        }
    });
}

/* ─────────────────────────────────────────────────────────────
   INIT
   ───────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
    updateBalanceInfo();
});
</script>
@endpush