@extends('layouts.main')

@section('title', 'HR Reports')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">HR Reports</li>
    </ol>
@endsection

@section('content')

@include('components.alerts')

{{-- Page Header --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">HR Reports</h4>
        <p class="text-muted mb-0 small">Generate employee and payroll reports</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <div class="input-group input-group-sm" style="width:185px">
            <span class="input-group-text">Period</span>
            <input type="month" id="selectedMonth" class="form-control">
        </div>
        <select id="cutoffPeriod" class="form-select form-select-sm" style="width:175px">
            <option value="full">Full Month</option>
            <option value="first">1st Cutoff (1–15)</option>
            <option value="second">2nd Cutoff (16–end)</option>
        </select>
    </div>
</div>

{{-- Category Filters --}}
<div class="d-flex gap-2 flex-wrap mb-4" id="categoryFilters"></div>

{{-- Report Cards --}}
<div class="row g-3" id="reportCardsContainer"></div>

<div id="noResults" class="text-center py-5 d-none">
    <p class="text-muted mb-0">No reports match your search or filter.</p>
</div>

{{-- ===== MODAL: PREVIEW ===== --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-semibold mb-0" id="previewModalLabel">—</h5>
                    <small class="text-muted" id="previewModalPeriod"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="reportPreviewArea" style="max-height:68vh;overflow-y:auto">
                <div class="text-center py-4 text-muted">Generating…</div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button class="btn btn-secondary btn-sm" onclick="printPreview()">Print</button>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportCSV()">Export CSV</button>
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .report-card { cursor: pointer; transition: box-shadow .15s; }
    .report-card:hover { box-shadow: 0 0 0 2px #6c757d; }
    #previewModal .modal-dialog { max-width: 92vw; }
    #reportPreviewArea table { font-size: 11px; }
    #reportPreviewArea thead th,
    #reportPreviewArea td { white-space: nowrap; }
    .status-pill {
        font-size: 10px; font-weight: 500; padding: 2px 8px;
        border-radius: 20px; background: #e9ecef; color: #495057;
        display: inline-block;
    }
    @media print {
        #reportPreviewArea table { font-size: 9px; }
        #reportPreviewArea th,
        #reportPreviewArea td { padding: 2px 4px !important; }
        @page { size: landscape; margin: .5cm; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    // ─── CONFIG ──────────────────────────────────────────────────────────────
    const BASE = '{{ url("/hresource/reports") }}';

    // ─── REPORT DEFINITIONS ──────────────────────────────────────────────────
    const REPORTS = [
        {
            id: 'employee-masterlist',
            title: 'Employee Master List',
            description: 'Complete employee roster with personal and employment details.',
            category: 'analytics', categoryLabel: 'Analytics',
            requirement: 'DOLE', frequency: 'As needed',
        },
        {
            id: 'dtr',
            title: 'Daily Time Record (DTR) Summary',
            description: 'Attendance, tardiness, undertime, and absence records per cutoff.',
            category: 'attendance', categoryLabel: 'Attendance',
            requirement: 'DOLE', frequency: 'Daily / Monthly',
        },
        {
            id: 'payroll-register',
            title: 'Payroll Register',
            description: 'Full payroll summary with gross earnings, deductions, and net pay.',
            category: 'payroll', categoryLabel: 'Payroll',
            requirement: 'Internal / DOLE', frequency: 'Per payroll period',
        },
        {
            id: 'sss-loans',
            title: 'SSS Loans Summary',
            description: 'All SSS loans with amortization schedule and outstanding balances.',
            category: 'payroll', categoryLabel: 'Payroll',
            requirement: 'SSS', frequency: 'As needed',
        },
        {
            id: 'pagibig-loans',
            title: 'Pag-IBIG Loans Summary',
            description: 'All Pag-IBIG loans with amortization schedule and outstanding balances.',
            category: 'payroll', categoryLabel: 'Payroll',
            requirement: 'Pag-IBIG', frequency: 'As needed',
        },
    ];

    const CATEGORIES = [
        { value: 'all',        label: 'All Reports' },
        { value: 'payroll',    label: 'Payroll'     },
        { value: 'attendance', label: 'Attendance'  },
        { value: 'analytics',  label: 'Analytics'   },
    ];

    // ─── STATE ────────────────────────────────────────────────────────────────
    let activeCategory     = 'all';
    let currentReportId    = null;
    let currentData        = [];
    let currentPeriodLabel = '';

    // ─── INIT ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const now = new Date();
        document.getElementById('selectedMonth').value =
            now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');

        renderCategoryFilters();
        renderCards();
    });

    // ─── CATEGORY FILTERS ─────────────────────────────────────────────────────
    function renderCategoryFilters() {
        document.getElementById('categoryFilters').innerHTML = CATEGORIES.map(function (c) {
            const active = activeCategory === c.value;
            return '<button type="button" class="btn btn-sm ' +
                (active ? 'btn-secondary' : 'btn-outline-secondary') + '" ' +
                'onclick="setCategory(\'' + c.value + '\')">' +
                c.label + '</button>';
        }).join('');
    }

    window.setCategory = function (val) {
        activeCategory = val;
        renderCategoryFilters();
        renderCards();
    };

    // ─── REPORT CARDS ─────────────────────────────────────────────────────────
    function renderCards() {
        const container = document.getElementById('reportCardsContainer');
        const noRes     = document.getElementById('noResults');

        const filtered = REPORTS.filter(function (r) {
            return activeCategory === 'all' || r.category === activeCategory;
        });

        if (!filtered.length) {
            container.innerHTML = '';
            noRes.classList.remove('d-none');
            return;
        }
        noRes.classList.add('d-none');

        container.innerHTML = filtered.map(function (r) {
            return '<div class="col-sm-6 col-xl-4">' +
                '<div class="card report-card h-100 border" onclick="openReport(\'' + r.id + '\')">' +
                    '<div class="card-body d-flex flex-column gap-2 pb-2">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<span class="badge bg-secondary">' + r.categoryLabel + '</span>' +
                        '</div>' +
                        '<div>' +
                            '<h6 class="mb-1 fw-semibold" style="font-size:13.5px">' + x(r.title) + '</h6>' +
                            '<p class="text-muted mb-0 small">' + x(r.description) + '</p>' +
                        '</div>' +
                        '<div class="mt-auto pt-2 border-top d-flex justify-content-between" style="font-size:11px">' +
                            '<span class="text-muted">Required by: <strong class="text-body">' + x(r.requirement) + '</strong></span>' +
                            '<span class="text-muted">' + x(r.frequency) + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">' +
                        '<button type="button" class="btn btn-secondary btn-sm w-100" ' +
                            'onclick="event.stopPropagation(); openReport(\'' + r.id + '\')">' +
                            'Preview Report</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    // ─── OPEN REPORT ──────────────────────────────────────────────────────────
    window.openReport = function (reportId) {
        const monthVal = document.getElementById('selectedMonth').value;
        const cutoff   = document.getElementById('cutoffPeriod').value;

        if (!monthVal) {
            Swal.fire({ icon: 'warning', title: 'Select a Period', text: 'Please select a month before generating a report.' });
            return;
        }

        const [y, m] = monthVal.split('-').map(Number);
        const lastDay = new Date(y, m, 0).getDate();
        const mName   = new Date(y, m - 1).toLocaleString('en-PH', { month: 'long' });

        if      (cutoff === 'first')  currentPeriodLabel = mName + ' 1\u201315, ' + y;
        else if (cutoff === 'second') currentPeriodLabel = mName + ' 16\u2013' + lastDay + ', ' + y;
        else                          currentPeriodLabel = mName + ' ' + y;

        currentReportId = reportId;
        const report    = REPORTS.find(function (r) { return r.id === reportId; });

        document.getElementById('previewModalLabel').textContent  = report.title;
        document.getElementById('previewModalPeriod').textContent = 'Period: ' + currentPeriodLabel;
        document.getElementById('reportPreviewArea').innerHTML     =
            '<div class="text-center py-4 text-muted">' +
            '<span class="spinner-border spinner-border-sm me-2"></span>Generating\u2026</div>';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('previewModal')).show();

        fetchReport(reportId, y, m, cutoff)
            .then(function (data) {
                currentData = data;
                document.getElementById('reportPreviewArea').innerHTML =
                    buildPreview(reportId, data, currentPeriodLabel);
            })
            .catch(function (err) {
                document.getElementById('reportPreviewArea').innerHTML =
                    '<div class="text-center py-4 text-muted small">Failed to load report. ' +
                    (err && err.message ? err.message : 'Please try again.') + '</div>';
            });
    };

    // ─── FETCH ────────────────────────────────────────────────────────────────
    function fetchReport(reportId, year, month, cutoff) {
        const params = new URLSearchParams({ year, month, cutoff });

        const urlMap = {
            'employee-masterlist': BASE + '/employee-masterlist',
            'dtr':                 BASE + '/dtr?' + params,
            'payroll-register':    BASE + '/payroll-register?' + params,
            'sss-loans':           BASE + '/loans?type=sss',
            'pagibig-loans':       BASE + '/loans?type=pagibig',
        };

        const url = urlMap[reportId];
        if (!url) return Promise.reject(new Error('Unknown report.'));

        return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) {
                return r.json().then(function (data) {
                    if (!r.ok) return Promise.reject(data);
                    return data;
                });
            });
    }

    // ─── PREVIEW BUILDERS ─────────────────────────────────────────────────────

    function peso(n) {
        return '\u20b1' + parseFloat(('' + n).replace(/,/g, '') || 0)
            .toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function sumCol(arr, key) {
        return arr.reduce(function (a, r) {
            return a + parseFloat(('' + (r[key] || 0)).replace(/,/g, ''));
        }, 0);
    }

    function reportWrap(title, inner, periodLabel) {
        return '<p class="fw-bold text-center mb-1">' + x(title) + '</p>' +
               '<p class="text-center text-muted mb-3 small">Period: ' + x(periodLabel) + '</p>' +
               '<div class="table-responsive">' + inner + '</div>';
    }

    function emptyState(msg) {
        return '<div class="text-center py-5 text-muted">' + x(msg) + '</div>';
    }

    function buildPreview(id, data, periodLabel) {
        switch (id) {
            case 'employee-masterlist': return buildEmployeeMasterlist(data, periodLabel);
            case 'dtr':                 return buildDTR(data, periodLabel);
            case 'payroll-register':    return buildPayrollRegister(data, periodLabel);
            case 'sss-loans':           return buildLoans(data, 'SSS', periodLabel);
            case 'pagibig-loans':       return buildLoans(data, 'PAG-IBIG', periodLabel);
            default: return '<p class="text-muted p-3">No preview available.</p>';
        }
    }

    // Employee Master List
    function buildEmployeeMasterlist(data, periodLabel) {
        if (!data.length) return emptyState('No active employees found.');

        const rows = data.map(function (e) {
            return '<tr>' +
                '<td>' + x(e.employee_id) + '</td>' +
                '<td class="fw-medium">' + x(e.full_name) + '</td>' +
                '<td>' + x(e.position) + '</td>' +
                '<td>' + x(e.department) + '</td>' +
                '<td>' + x(e.branch) + '</td>' +
                '<td><span class="status-pill">' + x(e.employment_status) + '</span></td>' +
                '<td>' + x(e.hire_date) + '</td>' +
                '<td>' + x(e.gender) + '</td>' +
                '<td>' + x(e.email) + '</td>' +
                '<td>' + x(e.phone) + '</td>' +
                '</tr>';
        }).join('');

        const table = '<table class="table table-bordered table-sm table-hover align-middle mb-0">' +
            '<thead class="table-light"><tr>' +
            '<th>Emp ID</th><th>Full Name</th><th>Position</th><th>Department</th><th>Branch</th>' +
            '<th>Status</th><th>Hire Date</th><th>Gender</th><th>Email</th><th>Phone</th>' +
            '</tr></thead>' +
            '<tbody>' + rows + '</tbody>' +
            '<tfoot class="table-light"><tr>' +
            '<td colspan="10" class="text-end fw-medium">Total: ' + data.length + ' employee(s)</td>' +
            '</tr></tfoot>' +
            '</table>';

        return reportWrap('EMPLOYEE MASTER LIST', table, periodLabel);
    }

    // DTR Summary
    function buildDTR(data, periodLabel) {
        if (!data.length) return emptyState('No attendance records found for this period.');

        const rows = data.map(function (r) {
            return '<tr>' +
                '<td>' + x(r.employee_id) + '</td>' +
                '<td class="fw-medium">' + x(r.employee) + '</td>' +
                '<td>' + x(r.department) + '</td>' +
                '<td class="text-nowrap">' + x(r.date) + '</td>' +
                '<td>' + x(r.time_in) + '</td>' +
                '<td>' + x(r.time_out) + '</td>' +
                '<td class="text-end">' + x(r.hours_worked) + '</td>' +
                '<td class="text-end">' + r.late_minutes + '</td>' +
                '<td class="text-end">' + r.undertime_minutes + '</td>' +
                '<td class="text-end">' + x(r.overtime_hours) + '</td>' +
                '<td><span class="status-pill">' + x(r.status) + '</span></td>' +
                '</tr>';
        }).join('');

        const table = '<table class="table table-bordered table-sm table-hover align-middle mb-0">' +
            '<thead class="table-light"><tr>' +
            '<th>Emp ID</th><th>Employee</th><th>Department</th><th>Date</th>' +
            '<th>Time In</th><th>Time Out</th>' +
            '<th class="text-end">Hrs</th>' +
            '<th class="text-end">Late (min)</th>' +
            '<th class="text-end">UT (min)</th>' +
            '<th class="text-end">OT (hrs)</th>' +
            '<th>Status</th>' +
            '</tr></thead>' +
            '<tbody>' + rows + '</tbody>' +
            '<tfoot class="table-light"><tr>' +
            '<td colspan="11" class="text-end fw-medium">Total Records: ' + data.length + '</td>' +
            '</tr></tfoot>' +
            '</table>';

        return reportWrap('DAILY TIME RECORD (DTR) SUMMARY', table, periodLabel);
    }

    // Payroll Register
    function buildPayrollRegister(data, periodLabel) {
        if (!data.length) {
            return emptyState('No released payroll records found for this period. Payroll must be in Released or Closed status to appear here.');
        }

        const rows = data.map(function (p) {
            const lateUT = parseFloat((p.late_deductions + '').replace(/,/g, '')) +
                           parseFloat((p.undertime_deductions + '').replace(/,/g, ''));
            return '<tr>' +
                '<td>' + x(p.employee_id) + '</td>' +
                '<td class="fw-medium">' + x(p.employee) + '</td>' +
                '<td>' + x(p.department) + '</td>' +
                '<td class="text-end">' + peso(p.basic_pay) + '</td>' +
                '<td class="text-end">' + peso(p.overtime_pay) + '</td>' +
                '<td class="text-end">' + peso(p.night_diff_pay) + '</td>' +
                '<td class="text-end">' + peso(p.holiday_pay) + '</td>' +
                '<td class="text-end">' + peso(p.allowances) + '</td>' +
                '<td class="text-end fw-medium">' + peso(p.gross_pay) + '</td>' +
                '<td class="text-end">' + peso(p.sss) + '</td>' +
                '<td class="text-end">' + peso(p.philhealth) + '</td>' +
                '<td class="text-end">' + peso(p.pagibig) + '</td>' +
                '<td class="text-end">' + peso(p.withholding_tax) + '</td>' +
                '<td class="text-end">' + peso(lateUT) + '</td>' +
                '<td class="text-end">' + peso(p.absent_deductions) + '</td>' +
                '<td class="text-end">' + peso(p.other_deductions) + '</td>' +
                '<td class="text-end fw-medium">' + peso(p.total_deductions) + '</td>' +
                '<td class="text-end fw-bold">' + peso(p.net_pay) + '</td>' +
                '</tr>';
        }).join('');

        const lateUTTotal = sumCol(data, 'late_deductions') + sumCol(data, 'undertime_deductions');

        const table = '<table class="table table-bordered table-sm table-hover align-middle mb-0">' +
            '<thead class="table-light">' +
            '<tr>' +
            '<th rowspan="2">Emp ID</th><th rowspan="2">Employee</th><th rowspan="2">Dept</th>' +
            '<th colspan="6" class="text-center">EARNINGS</th>' +
            '<th colspan="7" class="text-center">DEDUCTIONS</th>' +
            '<th rowspan="2" class="text-end">NET PAY</th>' +
            '</tr>' +
            '<tr>' +
            '<th class="text-end">Basic</th><th class="text-end">OT</th>' +
            '<th class="text-end">Night Diff</th><th class="text-end">Holiday</th>' +
            '<th class="text-end">Allow.</th><th class="text-end">Gross</th>' +
            '<th class="text-end">SSS</th><th class="text-end">PhilHlth</th>' +
            '<th class="text-end">Pag-IBIG</th><th class="text-end">Tax</th>' +
            '<th class="text-end">Late/UT</th><th class="text-end">Absent</th>' +
            '<th class="text-end">Other</th><th class="text-end">Total Ded.</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody>' + rows + '</tbody>' +
            '<tfoot class="table-light fw-medium">' +
            '<tr>' +
            '<td colspan="3" class="text-end">TOTALS</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'basic_pay'))       + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'overtime_pay'))    + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'night_diff_pay'))  + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'holiday_pay'))     + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'allowances'))      + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'gross_pay'))       + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'sss'))             + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'philhealth'))      + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'pagibig'))         + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'withholding_tax')) + '</td>' +
            '<td class="text-end">' + peso(lateUTTotal)                     + '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'absent_deductions'))+ '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'other_deductions'))+ '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'total_deductions'))+ '</td>' +
            '<td class="text-end">' + peso(sumCol(data, 'net_pay'))         + '</td>' +
            '</tr>' +
            '</tfoot>' +
            '</table>';

        return reportWrap('PAYROLL REGISTER', table, periodLabel);
    }

    // Loans (SSS or PAG-IBIG)
    function buildLoans(data, type, periodLabel) {
        if (!data.length) return emptyState('No ' + type + ' loans found.');

        const active    = data.filter(function (l) { return l.status === 'Active'; }).length;
        const completed = data.filter(function (l) { return l.status === 'Completed'; }).length;
        const totalBal  = sumCol(data, 'remaining_balance');
        const totalAmt  = sumCol(data, 'amount');

        const summary = '<div class="row g-3 mb-3">' +
            stat('Total Loans',   data.length) +
            stat('Active',        active) +
            stat('Completed',     completed) +
            stat('Total Balance', '\u20b1' + totalBal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) +
            '</div>';

        const rows = data.map(function (l) {
            return '<tr>' +
                '<td>' + x(l.employee_id) + '</td>' +
                '<td class="fw-medium">' + x(l.employee) + '</td>' +
                '<td>' + x(l.department) + '</td>' +
                '<td class="text-end fw-medium">' + peso(l.amount) + '</td>' +
                '<td class="text-end">' + peso(l.monthly_amortization) + '</td>' +
                '<td class="text-center">' + l.term_months + ' mos</td>' +
                '<td>' + x(l.start_date) + '</td>' +
                '<td class="text-center">' + l.payments_made + ' / ' + l.term_months + '</td>' +
                '<td class="text-end fw-medium">' + peso(l.remaining_balance) + '</td>' +
                '<td><span class="status-pill">' + x(l.status) + '</span></td>' +
                '</tr>';
        }).join('');

        const table = '<div class="table-responsive">' +
            '<table class="table table-bordered table-sm table-hover align-middle mb-0">' +
            '<thead class="table-light"><tr>' +
            '<th>Emp ID</th><th>Employee</th><th>Department</th>' +
            '<th class="text-end">Loan Amt</th><th class="text-end">Monthly</th>' +
            '<th class="text-center">Term</th><th>Start Date</th>' +
            '<th class="text-center">Payments</th>' +
            '<th class="text-end">Balance</th><th>Status</th>' +
            '</tr></thead>' +
            '<tbody>' + rows + '</tbody>' +
            '<tfoot class="table-light fw-medium"><tr>' +
            '<td colspan="3" class="text-end">TOTALS</td>' +
            '<td class="text-end">' + peso(totalAmt) + '</td>' +
            '<td colspan="4"></td>' +
            '<td class="text-end">' + peso(totalBal) + '</td>' +
            '<td></td>' +
            '</tr></tfoot>' +
            '</table></div>';

        return '<p class="fw-bold text-center mb-1">' + type + ' LOANS SUMMARY</p>' +
               '<p class="text-center text-muted mb-3 small">As of ' + x(periodLabel) + '</p>' +
               summary + table;
    }

    function stat(label, value) {
        return '<div class="col-6 col-md-3">' +
            '<div class="card border text-center p-3">' +
            '<div class="text-muted small mb-1">' + x(label) + '</div>' +
            '<div class="fw-bold">' + x(String(value)) + '</div>' +
            '</div></div>';
    }

    // ─── PRINT ────────────────────────────────────────────────────────────────
    window.printPreview = function () {
        const content = document.getElementById('reportPreviewArea').innerHTML;
        const win = window.open('', '_blank');
        if (!win) { alert('Please allow pop-ups to use this feature.'); return; }
        win.document.write('<!DOCTYPE html><html><head>' +
            '<title>HR Report \u2013 ' + currentPeriodLabel + '</title>' +
            '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">' +
            '<style>body{padding:20px;font-size:11px}table{font-size:10px}' +
            '.status-pill{font-size:9px;padding:1px 6px;border-radius:20px;background:#e9ecef;color:#495057}' +
            '@media print{@page{size:landscape;margin:.5cm}}</style>' +
            '</head><body>' + content + '</body></html>');
        win.document.close();
        win.onload = function () { win.focus(); setTimeout(function () { win.print(); win.close(); }, 350); };
    };

    // ─── EXPORT CSV ───────────────────────────────────────────────────────────
    window.exportCSV = function () {
        if (!currentData || !currentData.length) {
            Swal.fire({ icon: 'info', title: 'No Data', text: 'Nothing to export for the selected period.' });
            return;
        }
        const headers = Object.keys(currentData[0]).map(function (k) {
            return k.replace(/_/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
        });
        const rows = currentData.map(function (row) {
            return Object.values(row).map(function (v) {
                if (v === null || v === undefined) return '';
                const s = String(v);
                return (s.includes(',') || s.includes('"'))
                    ? '"' + s.replace(/"/g, '""') + '"' : s;
            }).join(',');
        });
        const csv  = '\uFEFF' + [headers.join(',')].concat(rows).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = currentReportId + '_' + document.getElementById('selectedMonth').value + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    };

    // ─── UTIL ─────────────────────────────────────────────────────────────────
    function x(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})();
</script>
@endpush