<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'School Management System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-blue: #1447e6;
            --brand-blue-dark: #0b2e8a;
            --brand-blue-light: #e8f0fe;
            --brand-blue-mid: #4d7cf5;
            --brand-white: #ffffff;
        }
        * { font-family: 'Poppins', sans-serif; }
        html, body { height: 100%; }
        body {
            background: linear-gradient(180deg, #eef3fc 0%, #f7f9fd 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Soft floating blobs behind everything - subtle school-blue ambience */
        body::before, body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
            pointer-events: none;
            opacity: .35;
        }
        body::before {
            width: 420px; height: 420px;
            top: -120px; right: -100px;
            background: radial-gradient(circle, var(--brand-blue-mid), transparent 70%);
            animation: floatBlob 16s ease-in-out infinite;
        }
        body::after {
            width: 380px; height: 380px;
            bottom: -140px; left: -100px;
            background: radial-gradient(circle, var(--brand-blue), transparent 70%);
            animation: floatBlob 20s ease-in-out infinite reverse;
        }
        @keyframes floatBlob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 40px) scale(1.08); }
        }

        /* Top navbar */
        .navbar {
            background: linear-gradient(90deg, var(--brand-blue-dark), var(--brand-blue), var(--brand-blue-mid)) !important;
            background-size: 200% 100%;
            animation: navShine 12s ease infinite;
            box-shadow: 0 2px 12px rgba(20, 71, 230, .25);
            position: relative;
            z-index: 3;
        }
        @keyframes navShine {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .navbar-brand { font-weight: 700; letter-spacing: .3px; }
        .navbar .badge { background: rgba(255,255,255,.18) !important; }
        .navbar .btn-outline-light:hover { background: #fff; color: var(--brand-blue-dark); }

        /* Layout shell */
        .app-shell { position: relative; z-index: 1; }

        /* Sidebar — fixed width always, never shrinks, own scroll if content is tall */
        .sidebar {
            flex: 0 0 230px;
            max-width: 230px;
            min-width: 230px;
            width: 230px;
            min-height: calc(100vh - 56px);
            max-height: calc(100vh - 56px);
            overflow-y: auto;
            background: var(--brand-white);
            border-right: 1px solid #e7ecf7;
            box-shadow: 6px 0 24px -6px rgba(0, 0, 0, .12), 2px 0 6px rgba(0, 0, 0, .06);
            position: sticky;
            top: 56px;
            z-index: 2;
        }
        .main-content {
            flex: 1 1 auto;
            min-width: 0; /* prevents flex item from forcing the sidebar to shrink */
        }
        .sidebar .nav-link {
            color: #445;
            font-weight: 500;
            padding: .65rem 1rem;
            border-radius: .55rem;
            transition: all .15s ease-in-out;
            white-space: nowrap;
        }
        .sidebar .nav-link i { color: var(--brand-blue); margin-right: .4rem; width: 1.1rem; text-align: center; }
        .sidebar .nav-link:hover {
            background: var(--brand-blue-light);
            color: var(--brand-blue-dark);
            transform: translateX(2px);
        }
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, var(--brand-blue), var(--brand-blue-dark));
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(20, 71, 230, .35);
        }
        .sidebar .nav-link.active i { color: #fff; }
        .sidebar .collapse .nav-link { font-size: .87rem; color: #5b6b8c; padding: .45rem .85rem; }
        .sidebar .collapse .nav-link:hover { background: var(--brand-blue-light); color: var(--brand-blue-dark); }
        .sidebar .collapse .nav-link.active { background: linear-gradient(90deg, var(--brand-blue), var(--brand-blue-dark)); color: #fff; box-shadow: none; }
        .sidebar [data-bs-toggle="collapse"] .bi-chevron-down { transition: transform .2s ease; }
        .sidebar [aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }

        /* Cards */
        .stat-card { border: none; border-radius: .9rem; box-shadow: 0 4px 16px rgba(20, 71, 230, .08); border-top: 3px solid var(--brand-blue); transition: transform .15s ease; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card i { font-size: 1.4rem; color: var(--brand-blue) !important; }
        .card { border-radius: .9rem; border: 1px solid #eaeffb; box-shadow: 0 2px 10px rgba(20, 71, 230, .05); }
        .card-header { background: var(--brand-blue-light); border-bottom: 1px solid #dbe6fb; font-weight: 600; color: var(--brand-blue-dark); border-radius: .9rem .9rem 0 0 !important; }

        /* Buttons */
        .btn-primary { background: var(--brand-blue); border-color: var(--brand-blue); }
        .btn-primary:hover { background: var(--brand-blue-dark); border-color: var(--brand-blue-dark); }
        .btn-dark { background: var(--brand-blue-dark); border-color: var(--brand-blue-dark); }
        .btn-dark:hover { background: var(--brand-blue); border-color: var(--brand-blue); }
        .btn-outline-dark { color: var(--brand-blue-dark); border-color: var(--brand-blue-dark); }
        .btn-outline-dark:hover { background: var(--brand-blue-dark); border-color: var(--brand-blue-dark); }
        .badge.bg-secondary { background: var(--brand-blue) !important; }

        /* Tables */
        .table thead { background: var(--brand-blue-light); color: var(--brand-blue-dark); }
    </style>
</head>
<body>
<nav class="navbar navbar-dark px-3">
    <span class="navbar-brand"><i class="bi bi-mortarboard-fill"></i> School Management System</span>
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
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}" href="{{ route('admin.teachers.index') }}"><i class="bi bi-person-badge"></i> Teachers</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people"></i> Students</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.classes.*') || request()->routeIs('admin.sections.*') || request()->routeIs('admin.activities.*') ? 'active' : '' }}" href="{{ route('admin.classes.index') }}"><i class="bi bi-building"></i> Classes</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" href="{{ route('admin.subjects.index') }}"><i class="bi bi-book"></i> Subjects</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.timetable.*') ? 'active' : '' }}" href="{{ route('admin.timetable.index') }}"><i class="bi bi-calendar-week"></i> Timetable</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}" href="{{ route('admin.exams.index') }}"><i class="bi bi-clipboard-check"></i> Exams &amp; Results</a></li>

                @php
                    $financeActive = request()->routeIs('admin.fee_types.*')
                        || request()->routeIs('admin.invoices.*')
                        || request()->routeIs('admin.finance.*')
                        || request()->routeIs('admin.inventory.*')
                        || request()->routeIs('admin.textbooks.*');
                @endphp
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $financeActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#financeMenu" role="button" aria-expanded="{{ $financeActive ? 'true' : 'false' }}">
                        <span><i class="bi bi-cash-stack"></i> Finance</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ $financeActive ? 'show' : '' }}" id="financeMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs('admin.fee_types.*') ? 'active' : '' }}" href="{{ route('admin.fee_types.index') }}"><i class="bi bi-cash-coin"></i> Fee Types</a></li>
                            <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}"><i class="bi bi-receipt"></i> Invoices &amp; Payments</a></li>
                            <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}" href="{{ route('admin.finance.ledger.index') }}"><i class="bi bi-bank"></i> Bank &amp; M-Pesa Ledger</a></li>
                            <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}"><i class="bi bi-box-seam"></i> Inventory &amp; Store</a></li>
                            <li class="nav-item"><a class="nav-link py-1 {{ request()->routeIs('admin.textbooks.*') ? 'active' : '' }}" href="{{ route('admin.textbooks.index') }}"><i class="bi bi-journal-bookmark"></i> Textbooks</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.cbc.*') ? 'active' : '' }}" href="{{ route('admin.cbc.index') }}"><i class="bi bi-award"></i> CBC Curriculum</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.sms.*') ? 'active' : '' }}" href="{{ route('admin.sms.index') }}"><i class="bi bi-chat-dots"></i> Bulk SMS</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.employees.*') || request()->routeIs('admin.payslips.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}"><i class="bi bi-wallet2"></i> Staff &amp; Payroll</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.staff_attendance.*') ? 'active' : '' }}" href="{{ route('admin.staff_attendance.index') }}"><i class="bi bi-fingerprint"></i> Staff Attendance</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}" href="{{ route('admin.parents.index') }}"><i class="bi bi-people-fill"></i> Parents</a></li>
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
</body>
</html>