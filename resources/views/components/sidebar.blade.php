{{-- resources/views/components/sidebar.blade.php --}}

@php
    $isEmployee   = request()->routeIs('employee.*');
    $isAdmin      = request()->routeIs('admin.*');
    $isHr         = request()->routeIs('hresource.*');
    $isAccounting = request()->routeIs('accounting.*');

    $role = auth()->user()->role ?? '';
    $user = auth()->user();
@endphp

{{-- ── Brand ─────────────────────────────────────────────── --}}
<div class="sidebar-brand" style="background:#161616;border-bottom:1px solid #2e2e2e;">
    <a href="{{ route('admin.index') }}" class="brand-link d-flex align-items-center gap-2 px-3 py-3 text-decoration-none">
        <span class="d-inline-flex align-items-center justify-content-center rounded fw-bold"
              style="width:34px;height:34px;background:#C9A227;color:#161616;font-size:.9rem;flex-shrink:0;">
        </span>
        <div class="lh-1">
            <span class="d-block fw-bold" style="color:#C9A227;font-size:.9rem;letter-spacing:.05em;">CLDG OFFICE</span>
            <span class="d-block" style="color:rgba(255,255,255,.55);font-size:.7rem;letter-spacing:.08em;">PAYROLL SYSTEM</span>
        </div>
    </a>
</div>

{{-- ── Navigation ────────────────────────────────────────── --}}
<div class="sidebar-wrapper" style="display:flex;flex-direction:column;height:calc(100vh - 70px);">
    <nav class="mt-2 pb-3">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">

            {{-- ─── EMPLOYEE (with profile card) ─────────────── --}}
            @if(in_array($role, ['employee', 'admin']))
            <li class="nav-item {{ $isEmployee ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isEmployee ? 'active' : '' }}">

                    {{-- Avatar --}}
                    @if($user->profile_photo ?? null)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}"
                             alt="{{ $user->fullName }}"
                             class="nav-icon"
                             style="width:28px;height:28px;border-radius:50%;object-fit:cover;
                                    border:2px solid #C9A227;flex-shrink:0;">
                    @else
                        <span class="nav-icon d-inline-flex align-items-center justify-content-center rounded-circle fw-bold"
                              style="width:28px;height:28px;background:#C9A227;color:#161616;
                                     font-size:.75rem;flex-shrink:0;">
                            {{ strtoupper(substr($user->fullName ?? 'U', 0, 1)) }}
                        </span>
                    @endif

                    <p class="d-flex flex-column lh-1 ms-1" style="gap:1px;">
                        <span style="font-size:.85rem;font-weight:600;">{{ $user->fullName ?? 'Employee' }}</span>
                        <span style="font-size:.7rem;opacity:.55;font-weight:400;">{{ ucfirst($role) }}</span>
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">

                    <li class="nav-item">
                        <a href="{{ route('employee.profile.index') }}"
                           class="nav-link {{ request()->routeIs('employee.profile.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-circle"></i>
                            <p>Profile</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employee.schedule.index') }}"
                           class="nav-link {{ request()->routeIs('employee.schedule.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar3"></i>
                            <p>Schedule</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employee.timekeeping.index') }}"
                           class="nav-link {{ request()->routeIs('employee.timekeeping.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-clock-history"></i>
                            <p>Timekeeping</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employee.overtime.index') }}"
                           class="nav-link {{ request()->routeIs('employee.overtime.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-hourglass-split"></i>
                            <p>Overtime</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employee.leave.index') }}"
                           class="nav-link {{ request()->routeIs('employee.leave.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-x"></i>
                            <p>Leave</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employee.payroll.index') }}"
                           class="nav-link {{ request()->routeIs('employee.payroll.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-receipt"></i>
                            <p>Payroll</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('employee.loan.index') }}"
                           class="nav-link {{ request()->routeIs('employee.loan.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-bank"></i>
                            <p>Loan</p>
                        </a>
                    </li>

                </ul>
            </li>
            @endif

            {{-- ─── Section divider ───────────────────────────── --}}
            @if(in_array($role, ['hr', 'admin']))
            <li class="nav-header" style="color:rgba(201,162,39,.75);font-size:.65rem;letter-spacing:.12em;padding:.75rem 1rem .25rem;">
                MANAGEMENT
            </li>
            @endif

            {{-- ─── HR ────────────────────────────────────────── --}}
            @if(in_array($role, ['hr', 'admin']))
            <li class="nav-item {{ $isHr ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isHr ? 'active' : '' }}">
                    <i class="nav-icon bi bi-people-fill"></i>
                    <p>
                        Human Resources
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">

                    <li class="nav-item">
                        <a href="{{ route('hresource.employees.index') }}"
                           class="nav-link {{ request()->routeIs('hresource.employees.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-lines-fill"></i>
                            <p>Employees</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('hresource.team_attendance.index') }}"
                           class="nav-link {{ request()->routeIs('hresource.team_attendance.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-clipboard-check"></i>
                            <p>Team Attendance</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('hresource.team_schedule.index') }}"
                           class="nav-link {{ request()->routeIs('hresource.team_schedule.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-week"></i>
                            <p>Team Schedule</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('hresource.requests.index') }}"
                           class="nav-link {{ request()->routeIs('hresource.requests.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-inbox-fill"></i>
                            <p>Requests</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('hresource.loans.index') }}"
                           class="nav-link {{ request()->routeIs('hresource.loans.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-bank"></i>
                            <p>Loans</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('hresource.reports.index') }}"
                           class="nav-link {{ request()->routeIs('hresource.reports.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-bar-chart-line-fill"></i>
                            <p>Reports</p>
                        </a>
                    </li>

                </ul>
            </li>
            @endif

            {{-- ─── ACCOUNTING ─────────────────────────────────── --}}
            @if(in_array($role, ['accounting', 'admin']))
            <li class="nav-item {{ $isAccounting ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isAccounting ? 'active' : '' }}">
                    <i class="nav-icon bi bi-calculator-fill"></i>
                    <p>
                        Accounting
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">

                    <li class="nav-item">
                        <a href="{{ route('accounting.payroll.periods.index') }}"
                           class="nav-link {{ request()->routeIs('accounting.payroll.periods.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-cash-stack"></i>
                            <p>Payroll Period</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('accounting.salary.index') }}"
                           class="nav-link {{ request()->routeIs('accounting.salary.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-wallet2"></i>
                            <p>Salary</p>
                        </a>
                    </li>

                </ul>
            </li>
            @endif

            {{-- ─── Section divider ───────────────────────────── --}}
            @if($role === 'admin')
            <li class="nav-header" style="color:rgba(201,162,39,.75);font-size:.65rem;letter-spacing:.12em;padding:.75rem 1rem .25rem;">
                ADMINISTRATION
            </li>
            @endif

            {{-- ─── ADMIN ──────────────────────────────────────── --}}
            @if($role === 'admin')
            <li class="nav-item {{ $isAdmin ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isAdmin ? 'active' : '' }}">
                    <i class="nav-icon bi bi-shield-lock-fill"></i>
                    <p>
                        Admin
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">

                    <li class="nav-item">
                        <a href="{{ route('admin.index') }}"
                           class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.accounts.index') }}"
                           class="nav-link {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-gear"></i>
                            <p>Accounts</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.departments.index') }}"
                           class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-diagram-3-fill"></i>
                            <p>Departments</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.positions.index') }}"
                           class="nav-link {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-badge-fill"></i>
                            <p>Positions</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.branches.index') }}"
                           class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-building-fill"></i>
                            <p>Branches</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.settings.index') }}"
                           class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-gear-fill"></i>
                            <p>Settings</p>
                        </a>
                    </li>

                </ul>
            </li>
            @endif
        </ul>
    </nav>

    {{-- ── Logout ───────────────────────────────────────────── --}}
    <div style="padding:1rem;border-top:1px solid #2e2e2e;margin-top:auto;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="btn w-100 d-flex align-items-center gap-2"
                    style="background:transparent;border:1px solid #3a3a3a;
                           color:rgba(255,255,255,.75);font-size:.85rem;
                           transition:all .2s ease;">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</div>