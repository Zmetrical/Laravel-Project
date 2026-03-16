@extends('layouts.main')

@section('title', 'Overtime Management')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Overtime Management</li>
    </ol>
@endsection

@section('content')

{{-- Flash messages --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Overtime Management</h4>
        <small class="text-muted">File overtime requests and track your OT hours</small>
    </div>
    <button class="btn btn-secondary" id="toggleFormBtn" onclick="toggleForm()">
        File OT Request
    </button>
</div>

{{-- Summary stats --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card mb-0">
            <div class="card-body">
                <div class="text-muted small">Approved OT Hours</div>
                <div class="fw-bold fs-5">{{ number_format($stats['approved_hours'], 1) }} hrs</div>
                <div class="text-muted" style="font-size:.75rem">Approved &amp; paid requests</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-0">
            <div class="card-body">
                <div class="text-muted small">OT Earnings</div>
                <div class="fw-bold fs-5">₱{{ number_format($stats['approved_earnings'], 2) }}</div>
                <div class="text-muted" style="font-size:.75rem">Estimated approved earnings</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-0">
            <div class="card-body">
                <div class="text-muted small">Pending Requests</div>
                <div class="fw-bold fs-5">{{ $stats['pending_count'] }}</div>
                <div class="text-muted" style="font-size:.75rem">Awaiting approval</div>
            </div>
        </div>
    </div>
</div>

{{-- OT Request Form --}}
<div class="card mb-3 d-none" id="otFormCard">
    <div class="card-header">
        <h5 class="card-title mb-0">New Overtime Request</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('employee.overtime.store') }}" id="otForm">
            @csrf
            <input type="hidden" name="date"            id="inputDate">
            <input type="hidden" name="ot_type"         id="inputOtType">
            <input type="hidden" name="rate_multiplier" id="inputRateMultiplier">
            <input type="hidden" name="estimated_pay"   id="inputEstimatedPay">

            <div class="row g-4">

                {{-- Left: Calendar --}}
                <div class="col-lg-6">
                    <label class="form-label fw-semibold">Select OT Date</label>

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

                    {{-- Selected date info --}}
                    <div class="mt-2" id="selectedDateInfo"></div>

                    {{-- Legend --}}
                    <div class="mt-2 p-2 border rounded bg-light">
                        <div class="row g-1" style="font-size:.7rem">
                            <div class="col-6 d-flex align-items-center gap-1">
                                <div style="width:12px;height:12px;border-radius:3px;background:rgba(var(--bs-primary-rgb),.12);border:1px solid rgba(var(--bs-primary-rgb),.35)"></div>
                                <span class="text-muted">Has Auto-OT</span>
                            </div>
                            <div class="col-6 d-flex align-items-center gap-1">
                                <div style="width:12px;height:12px;border-radius:3px;background:rgba(var(--bs-secondary-rgb),.2);border:1px solid var(--bs-secondary)"></div>
                                <span class="text-muted">Already Filed</span>
                            </div>
                            <div class="col-6 d-flex align-items-center gap-1">
                                <span class="fw-bold text-danger" style="font-size:.7rem">H</span>
                                <span class="text-muted">Holiday</span>
                            </div>
                            <div class="col-6 d-flex align-items-center gap-1">
                                <span class="fw-bold text-success" style="font-size:.7rem">R</span>
                                <span class="text-muted">Rest Day</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: OT Type + Reason --}}
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            OT Type
                        </label>
                        <div class="border rounded p-3 bg-light" id="otTypeDisplay">
                            <span class="text-muted small">Select a date to auto-detect OT type</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Requested Hours <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="hours" id="inputHours" 
                                class="form-control" step="0.5" min="0.5" max="24" 
                                placeholder="e.g., 2.5" oninput="updateEstimatedPay()" required disabled>
                            <span class="input-group-text">hrs</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Reason <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="reason" id="otReason" rows="3"
                            placeholder="Brief reason for overtime..." required></textarea>
                    </div>

                    <div class="mb-3 d-none" id="estimatedPayBox">
                        <div class="border rounded p-3 bg-light">
                            <p class="mb-1 small fw-semibold">Estimated OT Pay</p>
                            <p class="mb-0 fs-5 fw-bold" id="estimatedPayAmt">₱0.00</p>
                            <p class="mb-0 text-muted small" id="estimatedPayFormula"></p>
                        </div>
                    </div>

                    @if ($config && ! $config->enforce_limit)
                        <div class="alert alert-warning py-2 small mb-3">
                            OT limits are in warning mode — you may still file but HR will review flagged requests.
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" id="submitOTBtn" disabled>
                            Submit Request
                        </button>
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="toggleForm()">
                            Cancel
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- History Filters + Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0">Overtime Request History</h5>
        <small class="text-muted">{{ $requests->total() }} record(s)</small>
    </div>
    <div class="card-body pb-2">

        {{-- Filters --}}
        <form method="GET" action="{{ route('employee.overtime.index') }}" class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label small">Date From</label>
                <input type="date" name="from" class="form-control form-control-sm"
                    value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Date To</label>
                <input type="date" name="to" class="form-control form-control-sm"
                    value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all"     @selected(($filters['status'] ?? 'all') === 'all')>All Status</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                    <option value="approved"@selected(($filters['status'] ?? '') === 'approved')>Approved</option>
                    <option value="paid"    @selected(($filters['status'] ?? '') === 'paid')>Paid</option>
                    <option value="rejected"@selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-secondary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('employee.overtime.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>

        {{-- Table --}}
        @if ($requests->isEmpty())
            <div class="text-center text-muted py-5">
                No overtime requests found.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>OT Type</th>
                            <th>Rate</th>
                            <th>Est. Pay</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Reviewed By</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            <tr>
                                <td class="text-nowrap">
                                    {{ $req->date->format('M d, Y') }}
                                </td>
                                <td>{{ $req->hours }} hrs</td>
                                <td><span class="small">{{ $req->ot_type }}</span></td>
                                <td class="text-nowrap">{{ $req->rate_multiplier }}×</td>
                                <td class="text-nowrap fw-semibold">
                                    ₱{{ number_format($req->estimated_pay, 2) }}
                                </td>
                                <td class="text-muted small" style="max-width:160px;white-space:normal">
                                    {{ $req->reason ?: '—' }}
                                </td>
                                <td class="text-nowrap">
                                    @switch($req->status)
                                        @case('pending')
                                            <span class="badge bg-secondary">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-primary">Approved</span>
                                            @break
                                        @case('paid')
                                            <span class="badge bg-primary">Paid</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-secondary">Rejected</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="text-nowrap text-muted small">
                                    {{ $req->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-nowrap text-muted small">
                                    {{ $req->reviewer?->fullName ?? '—' }}
                                </td>
                                <td class="text-center">
                                    @if (in_array($req->status, ['pending', 'rejected']))
                                        <form method="POST"
                                            action="{{ route('employee.overtime.destroy', $req) }}"
                                            onsubmit="return confirm('Cancel this overtime request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                Cancel
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $requests->links() }}
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
const HOURLY_RATE       = {{ (float) $user->hourlyRate }};
const ATTENDANCE        = @json($attendanceRecords);   
const HOLIDAYS          = @json($holidays);            
const OT_RATES          = @json($overtimeRates);       
const FILED_DATES       = @json($filedDates->keys());  
const DAY_OFF_MAP       = @json($restDaysArray ?? [0, 6]); 

/* ─────────────────────────────────────────────────────────────
   STATE
   ───────────────────────────────────────────────────────────── */
let calMonth     = new Date();
calMonth.setDate(1);
let selectedDate = null;
let detectedType = null;
let formVisible  = false;

/* ─────────────────────────────────────────────────────────────
   HELPERS
   ───────────────────────────────────────────────────────────── */
const pad     = n => String(n).padStart(2, '0');
const toYMD   = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
const fmtDate = s => {
    const d = new Date(s + 'T00:00:00');
    return d.toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });
};
const fmtPHP  = n => '₱' + parseFloat(n).toLocaleString('en-PH', {
    minimumFractionDigits: 2, maximumFractionDigits: 2
});

const getHoliday   = ds => HOLIDAYS[ds] || null;
const getAtt       = ds => ATTENDANCE[ds] || null;
const isFiled      = ds => FILED_DATES.includes(ds);
const isRestDay    = ds => DAY_OFF_MAP.includes(new Date(ds + 'T00:00:00').getDay());
const getRate      = name => OT_RATES[name] || 1.25;

function autoDetectType(ds) {
    const hol   = getHoliday(ds);
    const rest  = isRestDay(ds);
    const att   = getAtt(ds);
    const night = att && String(att.shift_type || '').toLowerCase() === 'night';

    let base = 'Regular Overtime';

    if (hol && rest) {
        base = hol.type === 'regular'
            ? 'Regular Holiday Overtime on Rest Day'
            : 'Special Holiday Overtime on Rest Day';
    } else if (hol) {
        base = hol.type === 'regular'
            ? 'Regular Holiday Overtime'
            : 'Special Holiday Overtime';
    } else if (rest) {
        base = 'Rest Day Overtime';
    }

    const noNight = [
        'Regular Holiday Overtime', 'Special Holiday Overtime',
        'Regular Holiday Overtime on Rest Day', 'Special Holiday Overtime on Rest Day',
    ];
    if (night && !noNight.includes(base)) base += ' + Night Shift';

    return base;
}

function typeDescription(name) {
    const descriptions = {
        'Regular Overtime':                      'Regular OT rate (1.25×)',
        'Regular Overtime + Night Shift':        'OT (1.25) × Night Shift (1.10) = 1.375×',
        'Rest Day Overtime':                     'Rest Day (1.30) × OT (1.30) = 1.69×',
        'Rest Day Overtime + Night Shift':       'Rest Day OT (1.69) × Night (1.10) = 1.859×',
        'Special Holiday Overtime':              'Special Holiday (1.30) × OT (1.30) = 1.69×',
        'Special Holiday Overtime on Rest Day':  'Special+Rest (1.50) × OT (1.30) = 1.95×',
        'Regular Holiday Overtime':              'Regular Holiday (2.00) × OT (1.30) = 2.60×',
        'Regular Holiday Overtime on Rest Day':  'Regular+Rest (2.60) × OT (1.30) = 3.38×',
    };
    return descriptions[name] || '';
}

/* ─────────────────────────────────────────────────────────────
   CALENDAR
   ───────────────────────────────────────────────────────────── */
function renderCalendar() {
    const year  = calMonth.getFullYear();
    const month = calMonth.getMonth();

    document.getElementById('calMonthLabel').textContent =
        calMonth.toLocaleDateString('en-PH', { month:'long', year:'numeric' });

    const firstDow  = new Date(year, month, 1).getDay();
    const daysInMon = new Date(year, month + 1, 0).getDate();
    const prevDays  = new Date(year, month, 0).getDate();

    let cells = [];
    for (let i = firstDow - 1; i >= 0; i--)
        cells.push({ day: prevDays - i, current: false, ds: null });
    for (let d = 1; d <= daysInMon; d++)
        cells.push({ day: d, current: true, ds: `${year}-${pad(month+1)}-${pad(d)}` });
    while (cells.length < 42)
        cells.push({ day: cells.length - firstDow - daysInMon + 1, current: false, ds: null });

    let html = '';
    for (let row = 0; row < 6; row++) {
        html += '<div class="row g-1 mb-1">';
        for (let col = 0; col < 7; col++) {
            const cell = cells[row * 7 + col];
            const ds   = cell.ds;

            if (!cell.current || !ds) {
                html += `<div class="col"><div class="border rounded p-1 text-center opacity-25" style="min-height:52px;font-size:.75rem">
                    <div class="fw-semibold text-muted">${cell.day}</div>
                </div></div>`;
                continue;
            }

            const att      = getAtt(ds);
            const hasOT    = att && att.overtime_hours > 0;
            const filed    = isFiled(ds);
            const hol      = getHoliday(ds);
            const rest     = isRestDay(ds);
            const selected = ds === selectedDate;

            let style = 'min-height:52px;font-size:.75rem;border-radius:4px;padding:4px;border:1px solid #dee2e6;';
            let click = '';
            let badge = '';

            const isPast = ds < toYMD(new Date());

            if (filed) {
                style += 'opacity:.5;cursor:not-allowed;background:rgba(var(--bs-secondary-rgb),.1);';
                badge  = `<span style="font-size:.6rem" class="badge bg-secondary mt-1 d-block">Filed</span>`;
            } else if (isPast && !att) {
                // NEW RULE: Disable past days if they don't have an attendance record
                style += 'opacity:.4;cursor:not-allowed;background:#f8f9fa;';
            } else {
                // Clickable for Future/Today (Advance) OR Past with Attendance (Post-Rendered)
                style += 'cursor:pointer;';
                click  = `onclick="selectDate('${ds}')"`;
                
                if (selected) {
                    style += 'background:rgba(var(--bs-primary-rgb),.18);border-color:var(--bs-primary);';
                } else if (hasOT) {
                    style += 'background:rgba(var(--bs-primary-rgb),.07);border-color:rgba(var(--bs-primary-rgb),.35);';
                    badge  = `<span style="font-size:.6rem" class="badge bg-light border text-primary mt-1 d-block">OT Logged</span>`;
                } else {
                    style += 'background:#fff;';
                }
            }

            let indicators = '';
            if (hol)  indicators += `<span class="fw-bold text-danger" style="font-size:.6rem">H</span> `;
            if (rest) indicators += `<span class="fw-bold text-success" style="font-size:.6rem">R</span>`;

            html += `
                <div class="col">
                    <div style="${style}" ${click} class="date-cell transition-all">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="fw-semibold ${selected ? 'text-primary' : 'text-dark'}">${cell.day}</span>
                            <span>${indicators}</span>
                        </div>
                        ${badge}
                    </div>
                </div>`;
        }
        html += '</div>';
    }

    document.getElementById('calGrid').innerHTML = html;
}

function prevMonth() {
    calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth() - 1, 1);
    renderCalendar();
}
function nextMonth() {
    calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth() + 1, 1);
    renderCalendar();
}

/* ─────────────────────────────────────────────────────────────
   DATE SELECTION
   ───────────────────────────────────────────────────────────── */
function selectDate(ds) {
    if (isFiled(ds)) return;

    selectedDate = ds;
    detectedType = autoDetectType(ds);

    document.getElementById('inputDate').value           = ds;
    document.getElementById('inputOtType').value         = detectedType;
    document.getElementById('inputRateMultiplier').value = getRate(detectedType);
    
    // Unlock the hours input
    const hoursInput = document.getElementById('inputHours');
    hoursInput.disabled = false;
    
    // If they already have recorded OT (Post-rendered), auto-fill the hours!
    const att = getAtt(ds);
    if (att && att.overtime_hours > 0) {
        hoursInput.value = att.overtime_hours;
    } else {
        hoursInput.value = ''; // Blank for advance filing
    }

    renderCalendar();
    updateOTTypeDisplay();
    updateSelectedDateInfo();
    updateEstimatedPay();
}

function updateSelectedDateInfo() {
    const box = document.getElementById('selectedDateInfo');
    if (!selectedDate) { box.innerHTML = ''; return; }

    const att  = getAtt(selectedDate);
    const hol  = getHoliday(selectedDate);
    const otH  = att ? att.overtime_hours : 0;

    const holBadge = hol
        ? `<span class="badge bg-secondary ms-1">${hol.name}</span>`
        : '';

    let textInfo = '';
    if (att && att.is_ongoing) {
        textInfo = `<span class="text-primary fw-semibold"><i class="bi bi-clock-history me-1"></i>Shift currently ongoing</span>`;
    } else if (att) {
        textInfo = `Recorded: <strong>${att.hours_worked} total hrs</strong> — <span>${otH} hrs auto-detected OT</span>`;
    } else {
        textInfo = `<span class="text-muted"><i class="bi bi-calendar-plus me-1"></i>Advance filing (No attendance record yet)</span>`;
    }

    box.innerHTML = `
        <div class="border rounded p-2 bg-light">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-semibold small">${fmtDate(selectedDate)}</span>
                ${holBadge}
            </div>
            <div class="small mt-1">
                ${textInfo}
            </div>
        </div>`;
}

function updateOTTypeDisplay() {
    const box = document.getElementById('otTypeDisplay');
    if (!detectedType) {
        box.innerHTML = '<span class="text-muted small">Select a date to auto-detect OT type</span>';
        return;
    }

    const multiplier = getRate(detectedType);
    const desc       = typeDescription(detectedType);
    const hol        = getHoliday(selectedDate);
    const rest       = isRestDay(selectedDate);
    const att        = getAtt(selectedDate);
    const night      = att && String(att.shift_type || '').toLowerCase() === 'night';

    let tags = '';
    if (hol)   tags += `<span class="badge bg-danger me-1">${hol.type === 'regular' ? 'Regular Holiday' : 'Special Holiday'}</span>`;
    if (rest)  tags += `<span class="badge bg-success me-1">Rest Day</span>`;
    if (night) tags += `<span class="badge bg-secondary me-1">Night Shift</span>`;

    box.innerHTML = `
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
                <div class="fw-semibold">${detectedType}</div>
                <div class="text-muted small mt-1">${desc}</div>
                ${tags ? `<div class="mt-2">${tags}</div>` : ''}
            </div>
            <span class="badge bg-primary fs-6">${multiplier}×</span>
        </div>`;
}

function updateEstimatedPay() {
    const box      = document.getElementById('estimatedPayBox');
    const amtEl    = document.getElementById('estimatedPayAmt');
    const frmEl    = document.getElementById('estimatedPayFormula');
    const hoursVal = document.getElementById('inputHours').value;
    const btn      = document.getElementById('submitOTBtn');

    if (!selectedDate || !detectedType || !hoursVal || hoursVal <= 0) { 
        box.classList.add('d-none'); 
        btn.disabled = true;
        return; 
    }

    const otH        = parseFloat(hoursVal);
    const multiplier = getRate(detectedType);
    const pay        = otH * HOURLY_RATE * multiplier;

    document.getElementById('inputEstimatedPay').value = pay.toFixed(2);

    amtEl.textContent = fmtPHP(pay);
    frmEl.textContent = `${otH} hrs × ${fmtPHP(HOURLY_RATE)}/hr × ${multiplier}×`;
    box.classList.remove('d-none');
    
    // Enable submit button now that hours are valid
    btn.disabled = false;
}

/* ─────────────────────────────────────────────────────────────
   FORM TOGGLE
   ───────────────────────────────────────────────────────────── */
function toggleForm() {
    formVisible = !formVisible;
    const card = document.getElementById('otFormCard');
    const btn  = document.getElementById('toggleFormBtn');

    if (formVisible) {
        card.classList.remove('d-none');
        btn.textContent = 'Cancel';
    } else {
        card.classList.add('d-none');
        btn.textContent = 'File OT Request';
        resetForm();
    }
}

function resetForm() {
    selectedDate = null;
    detectedType = null;
    document.getElementById('otReason').value                    = '';
    document.getElementById('inputDate').value                   = '';
    document.getElementById('inputHours').value                  = '';
    document.getElementById('inputHours').disabled               = true;
    document.getElementById('submitOTBtn').disabled              = true;
    document.getElementById('estimatedPayBox').classList.add('d-none');
    document.getElementById('selectedDateInfo').innerHTML        = '';
    document.getElementById('otTypeDisplay').innerHTML           =
        '<span class="text-muted small">Select a date to auto-detect OT type</span>';
    renderCalendar();
}

/* ─────────────────────────────────────────────────────────────
   INIT
   ───────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
});
</script>
@endpush