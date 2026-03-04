@extends('layouts.main')

@section('title', 'System Settings')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">System Settings</li>
    </ol>
@endsection

@push('styles')
<style>
    .settings-tab-link {
        color: var(--bs-secondary-color);
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: .6rem 1.1rem;
        font-weight: 500;
        background: transparent;
        transition: color .15s, border-color .15s;
    }
    .settings-tab-link:hover {
        color: var(--bs-primary);
    }
    .settings-tab-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: transparent;
    }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }
    .stat-card {
        border: 1px solid var(--bs-border-color);
        border-radius: .5rem;
        padding: 1rem;
        background: var(--bs-body-bg);
    }
    .stat-card .stat-label { font-size: .78rem; color: var(--bs-secondary-color); margin-bottom: .25rem; }
    .stat-card .stat-value { font-size: 1.1rem; font-weight: 600; }
    .progress { height: 6px; }
    .section-divider { border-top: 1px solid var(--bs-border-color); padding-top: 1.5rem; margin-top: 1.5rem; }
    .form-check-input:checked { background-color: var(--bs-primary); border-color: var(--bs-primary); }
    .btn-save { min-width: 120px; }
    #hasChangesAlert { display: none; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">System Settings</h4>
        <small class="text-secondary">Configure system-wide preferences and integrations</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span id="hasChangesAlert" class="badge bg-secondary me-2">Unsaved changes</span>
        <button id="btnSaveSettings" class="btn btn-primary btn-save" disabled>
            <i class="bi bi-floppy me-1"></i> Save Settings
        </button>
    </div>
</div>

{{-- Settings Card --}}
<div class="card card-flush">
    <div class="card-header border-bottom p-0 px-3">
        <div class="d-flex gap-1" id="settingsTabs" role="tablist">
            {{-- Tabs injected by JS --}}
        </div>
    </div>
    <div class="card-body pt-4" id="settingsTabContent">
        {{-- Panes injected by JS --}}
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ─── State ─────────────────────────────────────────────────────────────────
    let hasChanges = false;
    let state = {
        biometric: {
            status: 'disconnected', // connected | disconnected | error
            baudRate: 57600,
            portName: '',
            firmwareVersion: '',
            lastTested: null,
            enrolledCount: 0,
        },
        backup: {
            lastBackup: '2025-06-01T08:00:00',
            frequency: 'daily',
            autoBackup: true,
            dbSizeMb: 2.47,
        },
        security: {
            autoLogoutMinutes: 30,
            auditLogRetentionDays: 90,
            allowConcurrentLogin: false,
        },
        maintenance: {
            storageUsedMb: 2.47,
            lastCacheCleared: '2025-06-10',
            lastArchive: '2025-05-31',
        },
        payroll: {
            nightShiftDifferential: 10,
            regularOt: 1.25,
            restDayOt: 1.30,
            regularHoliday: 2.00,
            specialHoliday: 1.30,
            nightShift: 0.10,
            gracePeriod: 10,
            regularLeaveCredits: 15,
            maxConsecutiveLeaveDays: 5,
            leaveCarryOver: false,
            thirteenthMonthMethod: 'basic_divided_12',
        },
        leaveGlobal: {
            enableDailyLimits: false,
            globalPercentageLimit: 20,
            fcfsEnabled: true,
            hrOverrideEnabled: true,
            showWarnings: true,
            autoReject: false,
        },
        leaveShift: {
            enablePerShiftLimits: false,
            dayShiftPercentage: 50,
            nightShiftPercentage: 50,
            minStaffingWarning: true,
            minStaffingThreshold: 80,
        },
        leaveDepartments: [
            { id: 1, name: 'Operations',   percentage: 20, dailyLimit: 2, dayShiftMax: 1, nightShiftMax: 1 },
            { id: 2, name: 'Finance',      percentage: 25, dailyLimit: 1, dayShiftMax: 1, nightShiftMax: 0 },
            { id: 3, name: 'HR & Admin',   percentage: 30, dailyLimit: 1, dayShiftMax: 1, nightShiftMax: 0 },
            { id: 4, name: 'IT Support',   percentage: 20, dailyLimit: 1, dayShiftMax: 1, nightShiftMax: 1 },
        ]
    };

    // ─── Tabs Definition ────────────────────────────────────────────────────────
    const tabs = [
        { id: 'general',          label: 'General Settings',     icon: 'bi-gear' },
        { id: 'leave-limits',     label: 'Leave Limit Settings', icon: 'bi-calendar-check' },
        { id: 'payroll-attendance', label: 'Payroll & Attendance', icon: 'bi-currency-dollar' },
    ];

    // ─── Helpers ───────────────────────────────────────────────────────────────
    function markChanged() {
        hasChanges = true;
        document.getElementById('hasChangesAlert').style.display = 'inline-block';
        document.getElementById('btnSaveSettings').disabled = false;
    }

    function showToast(title, message, type = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: title,
            text: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    function badge(status) {
        const map = {
            connected:    'bg-success',
            disconnected: 'bg-secondary',
            error:        'bg-danger',
        };
        return `<span class="badge ${map[status] || 'bg-secondary'} text-capitalize">${status}</span>`;
    }

    // ─── Render Tabs ──────────────────────────────────────────────────────────
    function renderTabs() {
        const tabBar = document.getElementById('settingsTabs');
        tabBar.innerHTML = tabs.map((t, i) => `
            <button class="btn settings-tab-link ${i === 0 ? 'active' : ''}"
                    data-tab="${t.id}" role="tab">
                <i class="bi ${t.icon} me-1"></i>${t.label}
            </button>
        `).join('');

        tabBar.querySelectorAll('[data-tab]').forEach(btn => {
            btn.addEventListener('click', function () {
                tabBar.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                renderPane(this.dataset.tab);
                // Hide save button on leave-limits (it has its own save)
                const saveBtn = document.getElementById('btnSaveSettings');
                saveBtn.style.display = this.dataset.tab === 'leave-limits' ? 'none' : '';
            });
        });
    }

    // ─── Pane Router ─────────────────────────────────────────────────────────
    function renderPane(tabId) {
        const content = document.getElementById('settingsTabContent');
        if (tabId === 'general')            content.innerHTML = buildGeneralPane();
        else if (tabId === 'leave-limits')  content.innerHTML = buildLeaveLimitsPane();
        else if (tabId === 'payroll-attendance') content.innerHTML = buildPayrollPane();
        bindPaneEvents(tabId);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PANE: General Settings
    // ─────────────────────────────────────────────────────────────────────────
    function buildGeneralPane() {
        const d = state;
        const statusBadge = badge(d.biometric.status);
        const lastTested  = d.biometric.lastTested
            ? new Date(d.biometric.lastTested).toLocaleTimeString() : '—';
        const lastBackup  = new Date(d.backup.lastBackup).toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });
        const storagePercent = Math.min((d.maintenance.storageUsedMb / 10) * 100, 100).toFixed(0);

        return `
        {{-- Biometric --}}
        <h6 class="fw-semibold mb-3"><i class="bi bi-usb-symbol me-2 text-primary"></i>Biometric Device Integration</h6>

        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="fw-semibold">Device Status ${statusBadge}</div>
                            <small class="text-secondary">Arduino + Adafruit Fingerprint Sensor (AS608 / R307)</small>
                            ${d.biometric.firmwareVersion ? `<div><small class="text-secondary">Firmware: ${d.biometric.firmwareVersion}</small></div>` : ''}
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="stat-card text-center">
                                <div class="stat-label">Enrolled</div>
                                <div class="stat-value">${d.biometric.enrolledCount}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-card text-center">
                                <div class="stat-label">Baud Rate</div>
                                <div class="stat-value">${d.biometric.baudRate}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-card text-center">
                                <div class="stat-label">Last Tested</div>
                                <div class="stat-value" style="font-size:.9rem">${lastTested}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Baud Rate</label>
                <select class="form-select" id="baudRate">
                    ${[9600, 57600, 115200].map(v => `<option value="${v}" ${d.biometric.baudRate == v ? 'selected' : ''}>${v}</option>`).join('')}
                </select>
                <div class="form-text">AS608 / R307 default: 57600</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Port Name <small class="text-secondary">(info only)</small></label>
                <input type="text" class="form-control" id="portName"
                    value="${d.biometric.portName}" placeholder="e.g. COM3 or /dev/ttyUSB0">
                <div class="form-text">Web Serial picks the port via browser dialog</div>
            </div>
        </div>

        <button class="btn btn-outline-primary btn-sm" id="btnTestBiometric">
            <i class="bi bi-wifi me-1"></i> Test Connection (PING)
        </button>
        <div class="alert alert-secondary mt-3 mb-0 py-2 small">
            <i class="bi bi-info-circle me-1"></i>
            Connect your Arduino via USB, then click <strong>Test Connection</strong>.
            The browser will prompt you to select the COM port.
            Biometric data is stored locally in the database.
        </div>

        {{-- Backup & Maintenance --}}
        <div class="section-divider">
            <h6 class="fw-semibold mb-3"><i class="bi bi-database me-2 text-primary"></i>Database Backup & Maintenance</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Last Backup</div>
                        <div class="stat-value" style="font-size:.95rem">${lastBackup}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Database Size</div>
                        <div class="stat-value">${d.backup.dbSizeMb.toFixed(2)} MB</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Backup Frequency</div>
                        <select class="form-select form-select-sm mt-1" id="backupFrequency">
                            ${['daily','weekly','monthly'].map(f => `<option value="${f}" ${d.backup.frequency === f ? 'selected' : ''}>${f.charAt(0).toUpperCase()+f.slice(1)}</option>`).join('')}
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center stat-card mb-3">
                <div>
                    <div class="fw-semibold">Automatic Backup</div>
                    <small class="text-secondary">Automatically backup database based on schedule</small>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="autoBackup" ${d.backup.autoBackup ? 'checked' : ''}>
                </div>
            </div>
            <button class="btn btn-outline-secondary btn-sm" id="btnBackupNow">
                <i class="bi bi-download me-1"></i> Backup Now
            </button>
        </div>

        {{-- Session & Security --}}
        <div class="section-divider">
            <h6 class="fw-semibold mb-3"><i class="bi bi-shield-lock me-2 text-primary"></i>Session & Security</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Auto-Logout Timer <small class="text-secondary">(minutes)</small></label>
                    <input type="number" class="form-control" id="autoLogout"
                        min="5" max="120" value="${d.security.autoLogoutMinutes}">
                    <div class="form-text">Automatically log out users after inactivity</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Audit Log Retention</label>
                    <select class="form-select" id="auditRetention">
                        ${[
                            [30,'30 days'], [60,'60 days'], [90,'90 days'],
                            [180,'180 days'], [365,'1 year']
                        ].map(([v,l]) => `<option value="${v}" ${d.security.auditLogRetentionDays == v ? 'selected' : ''}>${l}</option>`).join('')}
                    </select>
                    <div class="form-text">How long to keep audit logs</div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center stat-card">
                <div>
                    <div class="fw-semibold">Allow Concurrent Login</div>
                    <small class="text-secondary">Allow same user to login from multiple devices</small>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="concurrentLogin" ${d.security.allowConcurrentLogin ? 'checked' : ''}>
                </div>
            </div>
        </div>

        {{-- System Maintenance --}}
        <div class="section-divider">
            <h6 class="fw-semibold mb-3"><i class="bi bi-hdd me-2 text-primary"></i>System Maintenance</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Storage Used</div>
                        <div class="stat-value">${d.maintenance.storageUsedMb.toFixed(2)} MB</div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-primary" style="width:${storagePercent}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Last Cache Cleared</div>
                        <div class="stat-value" style="font-size:.95rem">
                            ${new Date(d.maintenance.lastCacheCleared).toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric'})}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Last Archive</div>
                        <div class="stat-value" style="font-size:.95rem">
                            ${new Date(d.maintenance.lastArchive).toLocaleDateString('en-PH', {month:'short',day:'numeric',year:'numeric'})}
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-outline-secondary btn-sm" id="btnClearCache">
                <i class="bi bi-trash me-1"></i> Clear System Cache
            </button>
            <div class="alert alert-secondary mt-3 mb-0 py-2 small">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Cache Clearing:</strong> Removes temporary files and cached data.
                User data, payroll records, and attendance logs will <strong>not</strong> be affected.
            </div>
        </div>
        `;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PANE: Leave Limit Settings
    // ─────────────────────────────────────────────────────────────────────────
    function buildLeaveLimitsPane() {
        const g = state.leaveGlobal;
        const s = state.leaveShift;

        const deptRows = state.leaveDepartments.map(d => `
            <tr>
                <td class="align-middle fw-medium">${d.name}</td>
                <td><input type="number" class="form-control form-control-sm dept-pct"
                    data-id="${d.id}" value="${d.percentage}" min="0" max="100" style="width:80px"></td>
                <td><input type="number" class="form-control form-control-sm dept-daily"
                    data-id="${d.id}" value="${d.dailyLimit}" min="0" style="width:70px"></td>
                <td><input type="number" class="form-control form-control-sm dept-day"
                    data-id="${d.id}" value="${d.dayShiftMax}" min="0" style="width:70px"></td>
                <td><input type="number" class="form-control form-control-sm dept-night"
                    data-id="${d.id}" value="${d.nightShiftMax}" min="0" style="width:70px"></td>
            </tr>
        `).join('');

        return `
        {{-- Global Leave Limits --}}
        <h6 class="fw-semibold mb-3"><i class="bi bi-sliders me-2 text-primary"></i>Global Leave Limits</h6>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center stat-card mb-3">
                    <div>
                        <div class="fw-semibold">Enable Daily Limits</div>
                        <small class="text-secondary">Restrict number of employees on leave per day</small>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="enableDailyLimits" ${g.enableDailyLimits ? 'checked' : ''}>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Global Percentage Limit <small class="text-secondary">(%)</small></label>
                <input type="number" class="form-control" id="globalPercentageLimit"
                    min="0" max="100" value="${g.globalPercentageLimit}">
                <div class="form-text">Maximum % of department on leave per day</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            ${[
                ['fcfsEnabled',       'First Come, First Served',    'Approve leave requests based on submission order'],
                ['hrOverrideEnabled', 'HR Override Enabled',         'Allow HR to override leave limit restrictions'],
                ['showWarnings',      'Show Limit Warnings',         'Display warnings when approaching leave limits'],
                ['autoReject',        'Auto-Reject Over Limit',      'Automatically reject requests that exceed the limit'],
            ].map(([id, label, desc]) => `
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center stat-card">
                        <div>
                            <div class="fw-semibold">${label}</div>
                            <small class="text-secondary">${desc}</small>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="${id}" ${g[id] ? 'checked' : ''}>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>

        {{-- Per-Shift Limits --}}
        <div class="section-divider">
            <h6 class="fw-semibold mb-3"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Per-Shift Limits</h6>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center stat-card mb-3">
                        <div>
                            <div class="fw-semibold">Enable Per-Shift Limits</div>
                            <small class="text-secondary">Set separate leave limits for day and night shifts</small>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="enablePerShiftLimits" ${s.enablePerShiftLimits ? 'checked' : ''}>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Day Shift % Limit</label>
                    <input type="number" class="form-control" id="dayShiftPct"
                        min="0" max="100" value="${s.dayShiftPercentage}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Night Shift % Limit</label>
                    <input type="number" class="form-control" id="nightShiftPct"
                        min="0" max="100" value="${s.nightShiftPercentage}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Min. Staffing Threshold <small class="text-secondary">(%)</small></label>
                    <input type="number" class="form-control" id="minStaffingThreshold"
                        min="0" max="100" value="${s.minStaffingThreshold}">
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center stat-card">
                        <div>
                            <div class="fw-semibold">Minimum Staffing Warning</div>
                            <small class="text-secondary">Warn when staffing falls below threshold</small>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="minStaffingWarning" ${s.minStaffingWarning ? 'checked' : ''}>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Department Limits --}}
        <div class="section-divider">
            <h6 class="fw-semibold mb-3"><i class="bi bi-building me-2 text-primary"></i>Department-Level Limits</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Limit&nbsp;%</th>
                            <th>Daily&nbsp;Max</th>
                            <th>Day Shift Max</th>
                            <th>Night Shift Max</th>
                        </tr>
                    </thead>
                    <tbody>${deptRows}</tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary btn-sm" id="btnSaveLeaveLimits">
                <i class="bi bi-floppy me-1"></i> Save Leave Limit Settings
            </button>
        </div>
        `;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PANE: Payroll & Attendance
    // ─────────────────────────────────────────────────────────────────────────
    function buildPayrollPane() {
        const p = state.payroll;

        const otFields = [
            { id: 'regularOt',       label: 'Regular OT',       note: 'Default: 1.25x', min: 1,    max: 5,   step: 0.01 },
            { id: 'restDayOt',       label: 'Rest Day OT',      note: 'Default: 1.30x', min: 1,    max: 5,   step: 0.01 },
            { id: 'regularHoliday',  label: 'Regular Holiday',  note: 'Default: 2.00x', min: 1,    max: 5,   step: 0.01 },
            { id: 'specialHoliday',  label: 'Special Holiday',  note: 'Default: 1.30x', min: 1,    max: 5,   step: 0.01 },
            { id: 'nightShift',      label: 'Night Shift Add.',  note: 'Default: 0.10',  min: 0.01, max: 1,   step: 0.01 },
        ];

        return `
        {{-- Payroll Computation --}}
        <h6 class="fw-semibold mb-3"><i class="bi bi-calculator me-2 text-primary"></i>Payroll Computation Rules</h6>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Night Shift Differential <small class="text-secondary">(%)</small></label>
                <input type="number" class="form-control" id="nightShiftDifferential"
                    min="0" max="100" step="0.1" value="${p.nightShiftDifferential}">
                <div class="form-text">Default: 10% — as per Philippine Labor Code</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">13th Month Computation Method</label>
                <select class="form-select" id="thirteenthMonthMethod">
                    <option value="basic_divided_12"   ${p.thirteenthMonthMethod === 'basic_divided_12'   ? 'selected' : ''}>Basic Salary ÷ 12</option>
                    <option value="total_basic_earned" ${p.thirteenthMonthMethod === 'total_basic_earned' ? 'selected' : ''}>Total Basic Salary Earned</option>
                </select>
            </div>
        </div>

        <div class="stat-card mb-4">
            <div class="fw-semibold mb-3">Overtime Multipliers</div>
            <div class="row g-3">
                ${otFields.map(f => `
                    <div class="col-md-4">
                        <label class="form-label">${f.label}</label>
                        <input type="number" class="form-control" id="${f.id}"
                            min="${f.min}" max="${f.max}" step="${f.step}" value="${p[f.id]}">
                        <div class="form-text">${f.note}</div>
                    </div>
                `).join('')}
            </div>
        </div>

        {{-- Attendance & Leave Rules --}}
        <div class="section-divider">
            <h6 class="fw-semibold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Attendance & Leave Rules</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Grace Period <small class="text-secondary">(minutes)</small></label>
                    <input type="number" class="form-control" id="gracePeriod"
                        min="0" max="60" value="${p.gracePeriod}">
                    <div class="form-text">Late tolerance before deduction</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Regular Leave Credits <small class="text-secondary">(days/yr)</small></label>
                    <input type="number" class="form-control" id="regularLeaveCredits"
                        min="0" max="30" value="${p.regularLeaveCredits}">
                    <div class="form-text">Annual leave days for regular employees</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Consecutive Leave Days</label>
                    <input type="number" class="form-control" id="maxConsecutiveLeaveDays"
                        min="1" max="30" value="${p.maxConsecutiveLeaveDays}">
                    <div class="form-text">Maximum days per single leave request</div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center stat-card">
                <div>
                    <div class="fw-semibold">Leave Carry-Over</div>
                    <small class="text-secondary">Allow unused leave credits to carry over to the next year</small>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="leaveCarryOver" ${p.leaveCarryOver ? 'checked' : ''}>
                </div>
            </div>
        </div>
        `;
    }

    // ─── Bind Events per Pane ─────────────────────────────────────────────────
    function bindPaneEvents(tabId) {
        if (tabId === 'general') {
            // Biometric inputs
            document.getElementById('baudRate').addEventListener('change', e => {
                state.biometric.baudRate = parseInt(e.target.value); markChanged();
            });
            document.getElementById('portName').addEventListener('input', e => {
                state.biometric.portName = e.target.value; markChanged();
            });
            document.getElementById('btnTestBiometric').addEventListener('click', handleTestBiometric);

            // Backup
            document.getElementById('backupFrequency').addEventListener('change', e => {
                state.backup.frequency = e.target.value; markChanged();
            });
            document.getElementById('autoBackup').addEventListener('change', e => {
                state.backup.autoBackup = e.target.checked; markChanged();
            });
            document.getElementById('btnBackupNow').addEventListener('click', handleBackupNow);

            // Security
            document.getElementById('autoLogout').addEventListener('input', e => {
                state.security.autoLogoutMinutes = parseInt(e.target.value); markChanged();
            });
            document.getElementById('auditRetention').addEventListener('change', e => {
                state.security.auditLogRetentionDays = parseInt(e.target.value); markChanged();
            });
            document.getElementById('concurrentLogin').addEventListener('change', e => {
                state.security.allowConcurrentLogin = e.target.checked; markChanged();
            });

            // Maintenance
            document.getElementById('btnClearCache').addEventListener('click', handleClearCache);
        }

        if (tabId === 'leave-limits') {
            // Global toggles
            ['enableDailyLimits','fcfsEnabled','hrOverrideEnabled','showWarnings','autoReject'].forEach(id => {
                document.getElementById(id)?.addEventListener('change', e => {
                    state.leaveGlobal[id] = e.target.checked; markChanged();
                });
            });
            document.getElementById('globalPercentageLimit').addEventListener('input', e => {
                state.leaveGlobal.globalPercentageLimit = parseFloat(e.target.value); markChanged();
            });

            // Per-shift
            ['enablePerShiftLimits','minStaffingWarning'].forEach(id => {
                document.getElementById(id)?.addEventListener('change', e => {
                    state.leaveShift[id] = e.target.checked; markChanged();
                });
            });
            ['dayShiftPct','nightShiftPct','minStaffingThreshold'].forEach(id => {
                document.getElementById(id)?.addEventListener('input', e => { markChanged(); });
            });

            // Department table
            document.querySelectorAll('.dept-pct, .dept-daily, .dept-day, .dept-night').forEach(el => {
                el.addEventListener('input', function () {
                    const id = parseInt(this.dataset.id);
                    const dept = state.leaveDepartments.find(d => d.id === id);
                    if (!dept) return;
                    if (this.classList.contains('dept-pct'))   dept.percentage  = parseFloat(this.value);
                    if (this.classList.contains('dept-daily')) dept.dailyLimit  = parseInt(this.value);
                    if (this.classList.contains('dept-day'))   dept.dayShiftMax = parseInt(this.value);
                    if (this.classList.contains('dept-night')) dept.nightShiftMax = parseInt(this.value);
                    markChanged();
                });
            });

            document.getElementById('btnSaveLeaveLimits').addEventListener('click', handleSave);
        }

        if (tabId === 'payroll-attendance') {
            // Payroll fields
            ['nightShiftDifferential','gracePeriod','regularLeaveCredits','maxConsecutiveLeaveDays',
             'regularOt','restDayOt','regularHoliday','specialHoliday','nightShift'].forEach(id => {
                document.getElementById(id)?.addEventListener('input', e => {
                    state.payroll[id] = parseFloat(e.target.value); markChanged();
                });
            });
            document.getElementById('thirteenthMonthMethod').addEventListener('change', e => {
                state.payroll.thirteenthMonthMethod = e.target.value; markChanged();
            });
            document.getElementById('leaveCarryOver').addEventListener('change', e => {
                state.payroll.leaveCarryOver = e.target.checked; markChanged();
            });
        }
    }

    // ─── Action Handlers ──────────────────────────────────────────────────────
    function handleTestBiometric() {
        const btn = document.getElementById('btnTestBiometric');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Connecting...';

        // Simulate async test (replace with real Web Serial call)
        setTimeout(() => {
            state.biometric.status = 'connected';
            state.biometric.lastTested = new Date().toISOString();
            state.biometric.firmwareVersion = 'v2.1.4';
            state.biometric.enrolledCount = 48;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-wifi me-1"></i> Test Connection (PING)';
            showToast('Biometric Connection Successful', 'Firmware: v2.1.4 | Enrolled: 48 templates');
            renderPane('general'); // re-render to reflect new status
        }, 1800);
    }

    function handleBackupNow() {
        state.backup.lastBackup = new Date().toISOString();
        showToast('Database Backup Complete', `FastServices_Backup_${Date.now()}.db (${state.backup.dbSizeMb.toFixed(2)} MB) saved to /backups/`);
        markChanged();
        renderPane('general');
    }

    function handleClearCache() {
        Swal.fire({
            title: 'Clear System Cache?',
            text: 'Temporary files will be removed. User and payroll data will not be affected.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Clear Cache',
            confirmButtonColor: 'var(--bs-primary)',
        }).then(result => {
            if (result.isConfirmed) {
                state.maintenance.lastCacheCleared = new Date().toISOString().split('T')[0];
                state.maintenance.storageUsedMb = parseFloat((state.maintenance.storageUsedMb * 0.6).toFixed(2));
                showToast('Cache Cleared', 'System cache has been cleared successfully.');
                renderPane('general');
            }
        });
    }

    function handleSave() {
        // Simulate save; replace with AJAX/fetch to your Laravel route
        showToast('Settings Saved', 'All configuration changes have been applied.');
        hasChanges = false;
        document.getElementById('hasChangesAlert').style.display = 'none';
        document.getElementById('btnSaveSettings').disabled = true;
    }

    // ─── Global Save Button ───────────────────────────────────────────────────
    document.getElementById('btnSaveSettings').addEventListener('click', handleSave);

    // ─── Init ─────────────────────────────────────────────────────────────────
    renderTabs();
    renderPane('general');
});
</script>
@endpush