<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Taaluma SMS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Taaluma brand — green, gold, white (see taalumasms.vercel.app) */
            --brand-green: #012622;
            --brand-green-dark: #01201D;
            --brand-green-light: #EBEEED;
            --brand-green-mid: #4D6764;
            --brand-gold: #C9972F;
            --brand-gold-dark: #A97C1F;
            --brand-gold-light: #FBF1DC;
            --brand-white: #ffffff;
        }
        * { font-family: 'Poppins', sans-serif; }
        html, body { height: 100%; }
        body {
            background: linear-gradient(180deg, #f2f8f4 0%, #fbfaf5 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Soft floating blobs behind everything — green + gold ambience */
        body::before, body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            z-index: -1;
            pointer-events: none;
            opacity: .3;
        }
        body::before {
            width: 420px; height: 420px;
            top: -120px; right: -100px;
            background: radial-gradient(circle, var(--brand-green-mid), transparent 70%);
            animation: floatBlob 16s ease-in-out infinite;
        }
        body::after {
            width: 380px; height: 380px;
            bottom: -140px; left: -100px;
            background: radial-gradient(circle, var(--brand-gold), transparent 70%);
            animation: floatBlob 20s ease-in-out infinite reverse;
        }
        @keyframes floatBlob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 40px) scale(1.08); }
        }

        /* Top navbar */
        .navbar {
            background: linear-gradient(90deg, var(--brand-green-dark), var(--brand-green), var(--brand-green-mid)) !important;
            background-size: 200% 100%;
            animation: navShine 12s ease infinite;
            box-shadow: 0 2px 12px rgba(15, 122, 69, .25);
            position: relative;
            z-index: 3;
            border-bottom: 2px solid var(--brand-gold);
        }
        @keyframes navShine {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .navbar-brand { font-weight: 700; letter-spacing: .3px; }
        .navbar-brand i { color: var(--brand-gold-light); }
        .navbar .badge { background: var(--brand-gold) !important; color: #2b2107 !important; }
        .navbar .btn-outline-light:hover { background: #fff; color: var(--brand-green-dark); }

        /* Layout shell */
        .app-shell { position: relative; }

        /* Sidebar — fixed width always, never shrinks, own scroll if content is tall */
        .sidebar {
            flex: 0 0 230px;
            max-width: 230px;
            min-width: 230px;
            width: 230px;
            min-height: calc(100vh - 56px);
            max-height: calc(100vh - 56px);
            overflow-y: auto;
            background: linear-gradient(180deg, var(--brand-gold), var(--brand-gold-dark));
            border-right: 1px solid var(--brand-gold-dark);
            box-shadow: 6px 0 24px -6px rgba(1, 38, 34, .25), 2px 0 6px rgba(1, 38, 34, .12);
            position: sticky;
            top: 56px;
            z-index: 2;
        }
        .main-content {
            flex: 1 1 auto;
            min-width: 0; /* prevents flex item from forcing the sidebar to shrink */
            background: var(--brand-white);
        }
        .sidebar .nav-link {
            color: var(--brand-green-dark);
            font-weight: 500;
            padding: .65rem 1rem;
            border-radius: .55rem;
            transition: all .15s ease-in-out;
            white-space: nowrap;
        }
        .sidebar .nav-link i { color: var(--brand-green-dark); margin-right: .4rem; width: 1.1rem; text-align: center; }
        .sidebar .nav-link:hover {
            background: rgba(1, 38, 34, .12);
            color: var(--brand-green-dark);
            transform: translateX(2px);
        }
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, var(--brand-green), var(--brand-green-dark));
            color: var(--brand-gold-light) !important;
            box-shadow: 0 4px 10px rgba(1, 38, 34, .35);
            border-left: 3px solid var(--brand-white);
        }
        .sidebar .nav-link.active i { color: var(--brand-gold-light); }
        .sidebar .nav-group-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--brand-green-dark);
            padding: .9rem 1rem .25rem;
        }
        .sidebar .collapse .nav-link { font-size: .87rem; color: var(--brand-green-dark); padding: .45rem .85rem; }
        .sidebar .collapse .nav-link:hover { background: rgba(1, 38, 34, .12); color: var(--brand-green-dark); }
        .sidebar .collapse .nav-link.active { background: linear-gradient(90deg, var(--brand-green), var(--brand-green-dark)); color: var(--brand-gold-light); box-shadow: none; }
        .sidebar [data-bs-toggle="collapse"] .bi-chevron-down { transition: transform .2s ease; color: var(--brand-green-dark); }
        .sidebar [aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
        /* Nested sub-menu (e.g. Finance > Accounting) — slightly indented and
           a touch dimmer so the hierarchy is visually obvious at a glance. */
        .sidebar .nav-subgroup { margin-left: .5rem; border-left: 2px solid rgba(1, 38, 34, .18); padding-left: .5rem; }
        .sidebar .nav-subgroup .nav-link { font-size: .82rem; padding: .4rem .7rem; }

        /* Cards */
        .stat-card { border: none; border-radius: .9rem; box-shadow: 0 4px 16px rgba(1, 38, 34, .08); border-top: 3px solid var(--brand-gold); transition: transform .15s ease; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card i { font-size: 1.4rem; color: var(--brand-green) !important; }
        .stat-card .text-muted { color: var(--brand-gold-dark) !important; font-weight: 600; }
        .card { border-radius: .9rem; border: 1px solid #e6f0ea; box-shadow: 0 2px 10px rgba(15, 122, 69, .05); }
        .card-header { background: var(--brand-green-light); border-bottom: 1px solid #d8ece0; font-weight: 600; color: var(--brand-green-dark); border-radius: .9rem .9rem 0 0 !important; }

        /* Buttons */
        .btn-primary { background: var(--brand-green); border-color: var(--brand-green); }
        .btn-primary:hover { background: var(--brand-green-dark); border-color: var(--brand-green-dark); }
        .btn-dark { background: var(--brand-green-dark); border-color: var(--brand-green-dark); }
        .btn-dark:hover { background: var(--brand-green); border-color: var(--brand-green); }
        .btn-outline-dark { color: var(--brand-green-dark); border-color: var(--brand-green-dark); }
        .btn-outline-dark:hover { background: var(--brand-green-dark); border-color: var(--brand-green-dark); }
        .btn-warning { background: var(--brand-gold); border-color: var(--brand-gold); color: #2b2107; }
        .btn-warning:hover { background: var(--brand-gold-dark); border-color: var(--brand-gold-dark); color: #2b2107; }
        .badge.bg-secondary { background: var(--brand-green) !important; }

        /* Tables */
        .table thead { background: var(--brand-green-light); color: var(--brand-green-dark); }
    </style>
</head>
<body>
<nav class="navbar navbar-dark px-3">
    <span class="navbar-brand"><i class="bi bi-mortarboard-fill"></i> Taaluma SMS</span>
    @auth
        <div class="d-flex align-items-center gap-3">
            <span class="text-light small">{{ auth()->user()->name }} <span class="badge text-uppercase">{{ auth()->user()->role }}</span></span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-light">Logout</button>
            </form>
        </div>
    @endauth
</nav>

<div class="d-flex app-shell">
    @auth
    <div class="sidebar p-3">
        <ul class="nav nav-pills flex-column gap-1">
            @if(auth()->user()->role === 'admin')
                @php $u = auth()->user(); @endphp
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

                @if($u->hasPermission('view_teachers') || $u->hasPermission('create_teacher') || $u->hasPermission('edit_teacher') || $u->hasPermission('delete_teacher'))
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}" href="{{ route('admin.teachers.index') }}"><i class="bi bi-person-badge"></i> Teachers</a></li>
                @endif

                @if($u->hasPermission('view_students') || $u->hasPermission('create_student') || $u->hasPermission('edit_student') || $u->hasPermission('delete_student'))
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people"></i> Students</a></li>
                @endif

                @if($u->hasPermission('manage_classes') || $u->hasPermission('manage_activities'))
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.classes.*') || request()->routeIs('admin.sections.*') || request()->routeIs('admin.activities.*') ? 'active' : '' }}" href="{{ route('admin.classes.index') }}"><i class="bi bi-building"></i> Classes</a></li>
                @endif

                {{-- Academics: Subjects, Timetable, Exams & Results grouped together --}}
                @php
                    $academicsActive = request()->routeIs('admin.subjects.*')
                        || request()->routeIs('admin.timetable.*')
                        || request()->routeIs('admin.exams.*');
                    $academicsLinks = [
                        ['perm' => 'manage_subjects', 'route' => 'admin.subjects.index', 'routeIs' => 'admin.subjects.*', 'icon' => 'bi-book', 'label' => 'Subjects'],
                        ['perm' => 'manage_timetable', 'route' => 'admin.timetable.index', 'routeIs' => 'admin.timetable.*', 'icon' => 'bi-calendar-week', 'label' => 'Timetable'],
                        ['perm' => null, 'perms' => ['manage_exams', 'enter_results', 'manage_grading_scales'], 'route' => 'admin.exams.index', 'routeIs' => 'admin.exams.*', 'icon' => 'bi-clipboard-check', 'label' => 'Exams &amp; Results'],
                    ];
                    $visibleAcademicsLinks = collect($academicsLinks)->filter(function ($l) use ($u) {
                        return $l['perm'] ? $u->hasPermission($l['perm']) : collect($l['perms'])->contains(fn ($p) => $u->hasPermission($p));
                    });
                @endphp
                @if($visibleAcademicsLinks->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $academicsActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#academicsMenu" role="button" aria-expanded="{{ $academicsActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-mortarboard"></i> Academics</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ $academicsActive ? 'show' : '' }}" id="academicsMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            @foreach($visibleAcademicsLinks as $link)
                                <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs($link['routeIs']) ? 'active' : '' }}" href="{{ route($link['route']) }}"><i class="bi {{ $link['icon'] }}"></i> {!! $link['label'] !!}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                @endif

                @php
                    $financeActive = request()->routeIs('admin.fee_types.*')
                        || request()->routeIs('admin.invoices.*')
                        || request()->routeIs('admin.finance.*')
                        || request()->routeIs('admin.accounting.*');
                    $financeLinks = [
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.overview', 'routeIs' => 'admin.accounting.overview', 'icon' => 'bi-graph-up-arrow', 'label' => 'Finance Overview'],
                        ['perm' => 'manage_fee_types', 'route' => 'admin.fee_types.index', 'routeIs' => 'admin.fee_types.*', 'icon' => 'bi-cash-coin', 'label' => 'Fee Types'],
                        ['perm' => 'manage_invoices', 'route' => 'admin.invoices.index', 'routeIs' => 'admin.invoices.*', 'icon' => 'bi-receipt', 'label' => 'Invoices &amp; Payments'],
                        ['perm' => 'manage_finance_ledger', 'route' => 'admin.finance.ledger.index', 'routeIs' => 'admin.finance.*', 'icon' => 'bi-bank', 'label' => 'Bank &amp; M-Pesa Ledger'],
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.chart_of_accounts', 'routeIs' => 'admin.accounting.chart_of_accounts', 'icon' => 'bi-diagram-3', 'label' => 'Chart of Accounts'],
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.journal_entries', 'routeIs' => 'admin.accounting.journal_entries', 'icon' => 'bi-journal-text', 'label' => 'Journal Entries'],
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.ledger', 'routeIs' => 'admin.accounting.ledger', 'icon' => 'bi-list-columns-reverse', 'label' => 'General Ledger'],
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.trial_balance', 'routeIs' => 'admin.accounting.trial_balance', 'icon' => 'bi-clipboard-check', 'label' => 'Trial Balance'],
                    ];
                    $visibleFinanceLinks = collect($financeLinks)->filter(fn ($l) => $u->hasPermission($l['perm']));
                @endphp
                @if($visibleFinanceLinks->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $financeActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#financeMenu" role="button" aria-expanded="{{ $financeActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-cash-stack"></i> Accounting &amp; Finance</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ $financeActive ? 'show' : '' }}" id="financeMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            @foreach($visibleFinanceLinks as $link)
                                <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs($link['routeIs']) ? 'active' : '' }}" href="{{ route($link['route']) }}"><i class="bi {{ $link['icon'] }}"></i> {!! $link['label'] !!}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                @endif

                @php
                    $inventoryActive = request()->routeIs('admin.inventory.*') || request()->routeIs('admin.textbooks.*');
                    $inventoryLinks = [
                        ['perm' => 'manage_inventory', 'route' => 'admin.inventory.index', 'routeIs' => 'admin.inventory.*', 'icon' => 'bi-box-seam', 'label' => 'Store &amp; Assets'],
                        ['perm' => 'manage_textbooks', 'route' => 'admin.textbooks.index', 'routeIs' => 'admin.textbooks.*', 'icon' => 'bi-journal-bookmark', 'label' => 'Textbooks'],
                    ];
                    $visibleInventoryLinks = collect($inventoryLinks)->filter(fn ($l) => $u->hasPermission($l['perm']));
                @endphp
                @if($visibleInventoryLinks->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $inventoryActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#inventoryMenu" role="button" aria-expanded="{{ $inventoryActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-box-seam"></i> Inventory &amp; Logistics</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ $inventoryActive ? 'show' : '' }}" id="inventoryMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            @foreach($visibleInventoryLinks as $link)
                                <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs($link['routeIs']) ? 'active' : '' }}" href="{{ route($link['route']) }}"><i class="bi {{ $link['icon'] }}"></i> {!! $link['label'] !!}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                @endif

                @if($u->hasPermission('manage_cbc'))
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.cbc.*') ? 'active' : '' }}" href="{{ route('admin.cbc.index') }}"><i class="bi bi-award"></i> CBC Curriculum</a></li>
                @endif

                {{-- Communication: Bulk SMS today, room for email/notices/etc later --}}
                @php
                    $commsActive = request()->routeIs('admin.sms.*');
                    $commsLinks = [
                        ['perm' => 'send_sms', 'route' => 'admin.sms.index', 'routeIs' => 'admin.sms.*', 'icon' => 'bi-chat-dots', 'label' => 'Bulk SMS'],
                    ];
                    $visibleCommsLinks = collect($commsLinks)->filter(fn ($l) => $u->hasPermission($l['perm']));
                @endphp
                @if($visibleCommsLinks->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $commsActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#commsMenu" role="button" aria-expanded="{{ $commsActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-broadcast"></i> Communication</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ $commsActive ? 'show' : '' }}" id="commsMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            @foreach($visibleCommsLinks as $link)
                                <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs($link['routeIs']) ? 'active' : '' }}" href="{{ route($link['route']) }}"><i class="bi {{ $link['icon'] }}"></i> {!! $link['label'] !!}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Staff & Payroll: employees, payslips, staff attendance grouped together --}}
                @php
                    $staffActive = request()->routeIs('admin.employees.*')
                        || request()->routeIs('admin.payslips.*')
                        || request()->routeIs('admin.staff_attendance.*');
                    $staffLinks = [
                        ['perm' => 'manage_employees', 'route' => 'admin.employees.index', 'routeIs' => 'admin.employees.*', 'icon' => 'bi-person-lines-fill', 'label' => 'Employees'],
                        ['perm' => 'generate_payslips', 'route' => 'admin.payslips.index', 'routeIs' => 'admin.payslips.*', 'icon' => 'bi-wallet2', 'label' => 'Payroll &amp; Payslips'],
                        ['perm' => 'view_staff_attendance', 'route' => 'admin.staff_attendance.index', 'routeIs' => 'admin.staff_attendance.*', 'icon' => 'bi-fingerprint', 'label' => 'Staff Attendance'],
                    ];
                    $visibleStaffLinks = collect($staffLinks)->filter(fn ($l) => $u->hasPermission($l['perm']));
                @endphp
                @if($visibleStaffLinks->isNotEmpty())
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $staffActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#staffMenu" role="button" aria-expanded="{{ $staffActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-briefcase-fill"></i> Staff &amp; Payroll</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ $staffActive ? 'show' : '' }}" id="staffMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            @foreach($visibleStaffLinks as $link)
                                <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs($link['routeIs']) ? 'active' : '' }}" href="{{ route($link['route']) }}"><i class="bi {{ $link['icon'] }}"></i> {!! $link['label'] !!}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                @endif

                @if($u->hasPermission('view_parents') || $u->hasPermission('create_parent') || $u->hasPermission('delete_parent'))
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}" href="{{ route('admin.parents.index') }}"><i class="bi bi-people-fill"></i> Parents</a></li>
                @endif
                @if($u->hasPermission('manage_settings') || $u->hasPermission('manage_rights'))
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear-fill"></i> Settings</a></li>
                @endif
            @elseif(auth()->user()->role === 'teacher')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}" href="{{ route('teacher.attendance.index') }}"><i class="bi bi-calendar-check"></i> Attendance</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.clock.*') ? 'active' : '' }}" href="{{ route('teacher.clock.index') }}"><i class="bi bi-fingerprint"></i> Clock In/Out</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.timetable.*') ? 'active' : '' }}" href="{{ route('teacher.timetable.index') }}"><i class="bi bi-calendar-week"></i> My Timetable</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}" href="{{ route('teacher.results.index') }}"><i class="bi bi-clipboard-data"></i> Enter Results</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('teacher.cbc.*') ? 'active' : '' }}" href="{{ route('teacher.cbc.index') }}"><i class="bi bi-award"></i> CBC Assessment</a></li>
            @elseif(auth()->user()->role === 'student')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}"><i class="bi bi-speedometer2"></i> My Dashboard</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('student.cbc_report') ? 'active' : '' }}" href="{{ route('student.cbc_report') }}"><i class="bi bi-award"></i> CBC Report</a></li>
            @elseif(auth()->user()->role === 'parent')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}" href="{{ route('parent.dashboard') }}"><i class="bi bi-speedometer2"></i> My Children</a></li>
            @endif
        </ul>
    </div>
    @endauth

    <div class="main-content p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
