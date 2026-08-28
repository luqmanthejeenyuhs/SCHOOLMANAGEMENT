<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Taaluma SMS'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Taaluma brand — green, gold, white (see taalumasms.vercel.app) */
            --brand-green: #012622;
            --brand-green-dark: #01201D;
            --brand-green-darker: #010F0D;
            --brand-green-light: #EBEEED;
            --brand-green-mid: #4D6764;
            --brand-gold: #C9972F;
            --brand-gold-dark: #A97C1F;
            --brand-gold-light: #FBF1DC;
            --brand-white: #ffffff;
            --header-height: 64px;
        }
        * { font-family: 'Poppins', sans-serif; }
        html, body { height: 100%; }
        body {
            background: #f7f8f7;
            position: relative;
            overflow-x: hidden;
        }

        /* Thin, on-brand scrollbars everywhere — replaces the default
           chunky OS scrollbar that sat awkwardly next to the sidebar. */
        * { scrollbar-width: thin; scrollbar-color: var(--brand-gold-dark) transparent; }
        ::-webkit-scrollbar { width: 7px; height: 7px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--brand-gold-dark); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--brand-gold); }

        /* Soft floating blobs behind everything — green + gold ambience */
        body::before, body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            filter: blur(70px);
            z-index: -1;
            pointer-events: none;
            opacity: .12;
        }
        body::before {
            width: 420px; height: 420px;
            top: -120px; right: -100px;
            background: radial-gradient(circle, var(--brand-green-mid), transparent 70%);
        }
        body::after {
            width: 380px; height: 380px;
            bottom: -140px; left: -100px;
            background: radial-gradient(circle, var(--brand-gold), transparent 70%);
        }

        /* Top navbar — sticky so it never scrolls out of view, with a
           calm, static gradient (no distracting shine animation) that
           eases toward gold at the trailing edge, echoed by the sidebar's
           top accent strip just below it for a single continuous flow. */
        .navbar {
            background: linear-gradient(100deg, var(--brand-green-darker) 0%, var(--brand-green) 55%, var(--brand-green-mid) 100%) !important;
            box-shadow: 0 2px 16px rgba(1, 15, 13, .35);
            position: sticky;
            top: 0;
            z-index: 1030;
            height: var(--header-height);
            border-bottom: 1px solid rgba(201, 151, 47, .55);
        }
        .navbar-brand { font-weight: 700; letter-spacing: .3px; }
        .navbar-brand i { color: var(--brand-gold-light); }
        .navbar .badge { background: var(--brand-gold) !important; color: #2b2107 !important; font-weight: 600; letter-spacing: .03em; }
        .navbar .btn-outline-light { border-color: rgba(255,255,255,.5); }
        .navbar .btn-outline-light:hover { background: var(--brand-gold); border-color: var(--brand-gold); color: var(--brand-green-darker); }

        /* Layout shell */
        .app-shell { position: relative; align-items: flex-start; }

        /* Sidebar — fixed width always, never shrinks, own scroll if content is tall */
        .sidebar {
            flex: 0 0 246px;
            max-width: 246px;
            min-width: 246px;
            width: 246px;
            min-height: calc(100vh - var(--header-height));
            max-height: calc(100vh - var(--header-height));
            overflow-y: auto;
            overflow-x: hidden;
            background: linear-gradient(180deg, var(--brand-gold) 0%, var(--brand-gold-dark) 100%);
            box-shadow: 6px 0 24px -6px rgba(1, 38, 34, .25), 2px 0 6px rgba(1, 38, 34, .12);
            position: sticky;
            top: var(--header-height);
            z-index: 2;
        }
        /* Thin accent ribbon at the very top of the sidebar, echoing the
           navbar's green so the header visually flows straight into the
           sidebar instead of hitting a hard color edge. */
        .sidebar::before {
            content: "";
            display: block;
            height: 5px;
            background: linear-gradient(90deg, var(--brand-green-darker), var(--brand-green), var(--brand-gold));
        }
        .sidebar .nav { padding: .85rem .75rem; }
        .main-content {
            flex: 1 1 auto;
            min-width: 0; /* prevents flex item from forcing the sidebar to shrink */
            background: transparent;
        }
        .sidebar .nav-link {
            color: var(--brand-green-darker);
            font-weight: 500;
            font-size: .88rem;
            padding: .6rem .8rem;
            border-radius: .6rem;
            transition: all .15s ease-in-out;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar .nav-link i { color: var(--brand-green-darker); margin-right: .45rem; width: 1.1rem; text-align: center; }
        .sidebar .nav-link:hover {
            background: rgba(1, 38, 34, .14);
            color: var(--brand-green-darker);
            transform: translateX(2px);
        }
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, var(--brand-green), var(--brand-green-dark));
            color: var(--brand-gold-light) !important;
            box-shadow: 0 4px 12px rgba(1, 38, 34, .4);
        }
        .sidebar .nav-link.active i { color: var(--brand-gold-light); }
        .sidebar .nav-group-label {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(1, 38, 34, .65);
            padding: 1rem .8rem .3rem;
        }
        .sidebar .collapse .nav-link { font-size: .82rem; color: var(--brand-green-darker); padding: .48rem .8rem; }
        .sidebar .collapse .nav-link:hover { background: rgba(1, 38, 34, .14); color: var(--brand-green-darker); }
        .sidebar .collapse .nav-link.active { background: linear-gradient(90deg, var(--brand-green), var(--brand-green-dark)); color: var(--brand-gold-light); box-shadow: none; }
        .sidebar [data-bs-toggle="collapse"] .bi-chevron-down { transition: transform .2s ease; color: var(--brand-green-darker); flex: 0 0 auto; }
        .sidebar [aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
        /* Nested sub-menu (e.g. Finance > Ledger) — slightly indented and
           a touch dimmer so the hierarchy is visually obvious at a glance. */
        .sidebar .nav-subgroup { margin-left: .35rem; border-left: 2px solid rgba(1, 38, 34, .2); padding-left: .45rem; }
        .sidebar .nav-subgroup .nav-link { font-size: .8rem; padding: .42rem .65rem; }

        /* Cards — quieter shadow, crisper edges, a premium feel */
        .stat-card { border: none; border-radius: 1rem; box-shadow: 0 6px 20px rgba(1, 38, 34, .07); border-top: 3px solid var(--brand-gold); transition: transform .18s ease, box-shadow .18s ease; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 26px rgba(1, 38, 34, .12); }
        .stat-card i { font-size: 1.4rem; color: var(--brand-green) !important; }
        .stat-card .text-muted { color: var(--brand-gold-dark) !important; font-weight: 600; }
        .card { border-radius: 1rem; border: 1px solid #e9edea; box-shadow: 0 3px 14px rgba(1, 38, 34, .05); }
        .card-header { background: var(--brand-green-light); border-bottom: 1px solid #dde5e0; font-weight: 600; color: var(--brand-green-dark); border-radius: 1rem 1rem 0 0 !important; }

        /* Buttons */
        .btn { border-radius: .6rem; }
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
    <?php if(auth()->guard()->check()): ?>
        <div class="d-flex align-items-center gap-3">
            <span class="text-light small"><?php echo e(auth()->user()->name); ?> <span class="badge text-uppercase"><?php echo e(auth()->user()->role); ?></span></span>
            <a href="<?php echo e(route('account.password.edit')); ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-key-fill"></i> Password</a>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button class="btn btn-sm btn-outline-light">Logout</button>
            </form>
        </div>
    <?php endif; ?>
</nav>

<div class="d-flex app-shell">
    <?php if(auth()->guard()->check()): ?>
    <div class="sidebar p-3">
        <ul class="nav nav-pills flex-column gap-1">
            <?php if(auth()->user()->role === 'admin'): ?>
                <?php $u = auth()->user(); ?>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

                <?php if($u->hasPermission('view_teachers') || $u->hasPermission('create_teacher') || $u->hasPermission('edit_teacher') || $u->hasPermission('delete_teacher')): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('admin.teachers.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.teachers.index')); ?>"><i class="bi bi-person-badge"></i> Teachers</a></li>
                <?php endif; ?>

                <?php if($u->hasPermission('view_students') || $u->hasPermission('create_student') || $u->hasPermission('edit_student') || $u->hasPermission('delete_student')): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('admin.students.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.students.index')); ?>"><i class="bi bi-people"></i> Students</a></li>
                <?php endif; ?>

                <?php if($u->hasPermission('view_parents') || $u->hasPermission('create_parent') || $u->hasPermission('delete_parent')): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('admin.parents.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.parents.index')); ?>"><i class="bi bi-people-fill"></i> Parents/Guardians</a></li>
                <?php endif; ?>

                <?php if($u->hasPermission('manage_classes') || $u->hasPermission('manage_activities')): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('admin.classes.*') || request()->routeIs('admin.sections.*') || request()->routeIs('admin.activities.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.classes.index')); ?>"><i class="bi bi-building"></i> Classes</a></li>
                <?php endif; ?>

                
                <?php
                    $academicsActive = request()->routeIs('admin.subjects.*')
                        || request()->routeIs('admin.timetable.*')
                        || request()->routeIs('admin.exams.*');
                    $academicsLinks = [
                        ['perm' => 'manage_subjects', 'route' => 'admin.subjects.index', 'routeIs' => 'admin.subjects.*', 'icon' => 'bi-book', 'label' => 'Subjects'],
                        ['perm' => 'manage_timetable', 'route' => 'admin.timetable.index', 'routeIs' => 'admin.timetable.*', 'icon' => 'bi-calendar-week', 'label' => 'Timetable'],
                        ['perm' => null, 'perms' => ['manage_exams', 'enter_results', 'manage_grading_scales'], 'route' => 'admin.exams.index', 'routeIs' => 'admin.exams.*', 'icon' => 'bi-clipboard-check', 'label' => 'Exams'],
                    ];
                    $visibleAcademicsLinks = collect($academicsLinks)->filter(function ($l) use ($u) {
                        return $l['perm'] ? $u->hasPermission($l['perm']) : collect($l['perms'])->contains(fn ($p) => $u->hasPermission($p));
                    });
                ?>
                <?php if($visibleAcademicsLinks->isNotEmpty()): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center <?php echo e($academicsActive ? 'active' : ''); ?>" data-bs-toggle="collapse" href="#academicsMenu" role="button" aria-expanded="<?php echo e($academicsActive ? 'true' : 'false'); ?>">
                        <span><i class="bi bi-mortarboard"></i> Academics</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse <?php echo e($academicsActive ? 'show' : ''); ?>" id="academicsMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            <?php $__currentLoopData = $visibleAcademicsLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="nav-item"><a class="nav-link py-1 <?php echo e(request()->routeIs($link['routeIs']) ? 'active' : ''); ?>" href="<?php echo e(route($link['route'])); ?>"><i class="bi <?php echo e($link['icon']); ?>"></i> <?php echo $link['label']; ?></a></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php
                    $financeActive = request()->routeIs('admin.fee_types.*')
                        || request()->routeIs('admin.invoices.*')
                        || request()->routeIs('admin.finance.*')
                        || request()->routeIs('admin.accounting.*');
                    $financeLinks = [
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.overview', 'routeIs' => 'admin.accounting.overview', 'icon' => 'bi-graph-up-arrow', 'label' => 'Overview'],
                        ['perm' => 'manage_fee_types', 'route' => 'admin.fee_types.index', 'routeIs' => 'admin.fee_types.*', 'icon' => 'bi-cash-coin', 'label' => 'Fees'],
                        ['perm' => 'manage_invoices', 'route' => 'admin.invoices.index', 'routeIs' => 'admin.invoices.*', 'icon' => 'bi-receipt', 'label' => 'Invoices'],
                        ['perm' => 'manage_finance_ledger', 'route' => 'admin.finance.ledger.index', 'routeIs' => 'admin.finance.*', 'icon' => 'bi-bank', 'label' => 'M-Pesa'],
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.chart_of_accounts', 'routeIs' => 'admin.accounting.chart_of_accounts', 'icon' => 'bi-diagram-3', 'label' => 'Accounts'],
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.journal_entries', 'routeIs' => 'admin.accounting.journal_entries', 'icon' => 'bi-journal-text', 'label' => 'Journal'],
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.ledger', 'routeIs' => 'admin.accounting.ledger', 'icon' => 'bi-list-columns-reverse', 'label' => 'Ledger'],
                        ['perm' => 'manage_accounting', 'route' => 'admin.accounting.trial_balance', 'routeIs' => 'admin.accounting.trial_balance', 'icon' => 'bi-clipboard-check', 'label' => 'Balance'],
                    ];
                    $visibleFinanceLinks = collect($financeLinks)->filter(fn ($l) => $u->hasPermission($l['perm']));
                ?>
                <?php if($visibleFinanceLinks->isNotEmpty()): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center <?php echo e($financeActive ? 'active' : ''); ?>" data-bs-toggle="collapse" href="#financeMenu" role="button" aria-expanded="<?php echo e($financeActive ? 'true' : 'false'); ?>">
                        <span><i class="bi bi-cash-stack"></i> Finance</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse <?php echo e($financeActive ? 'show' : ''); ?>" id="financeMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            <?php $__currentLoopData = $visibleFinanceLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="nav-item"><a class="nav-link py-1 <?php echo e(request()->routeIs($link['routeIs']) ? 'active' : ''); ?>" href="<?php echo e(route($link['route'])); ?>"><i class="bi <?php echo e($link['icon']); ?>"></i> <?php echo $link['label']; ?></a></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php
                    $inventoryActive = request()->routeIs('admin.inventory.*') || request()->routeIs('admin.textbooks.*');
                    $inventoryLinks = [
                        ['perm' => 'manage_inventory', 'route' => 'admin.inventory.index', 'routeIs' => 'admin.inventory.*', 'icon' => 'bi-box-seam', 'label' => 'Store'],
                        ['perm' => 'manage_textbooks', 'route' => 'admin.textbooks.index', 'routeIs' => 'admin.textbooks.*', 'icon' => 'bi-journal-bookmark', 'label' => 'Textbooks'],
                    ];
                    $visibleInventoryLinks = collect($inventoryLinks)->filter(fn ($l) => $u->hasPermission($l['perm']));
                ?>
                <?php if($visibleInventoryLinks->isNotEmpty()): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center <?php echo e($inventoryActive ? 'active' : ''); ?>" data-bs-toggle="collapse" href="#inventoryMenu" role="button" aria-expanded="<?php echo e($inventoryActive ? 'true' : 'false'); ?>">
                        <span><i class="bi bi-box-seam"></i> Inventory</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse <?php echo e($inventoryActive ? 'show' : ''); ?>" id="inventoryMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            <?php $__currentLoopData = $visibleInventoryLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="nav-item"><a class="nav-link py-1 <?php echo e(request()->routeIs($link['routeIs']) ? 'active' : ''); ?>" href="<?php echo e(route($link['route'])); ?>"><i class="bi <?php echo e($link['icon']); ?>"></i> <?php echo $link['label']; ?></a></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if($u->hasPermission('manage_cbc')): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('admin.cbc.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.cbc.index')); ?>"><i class="bi bi-award"></i> CBC</a></li>
                <?php endif; ?>

                
                <?php
                    $commsActive = request()->routeIs('admin.sms.*');
                    $commsLinks = [
                        ['perm' => 'send_sms', 'route' => 'admin.sms.index', 'routeIs' => 'admin.sms.*', 'icon' => 'bi-chat-dots', 'label' => 'Bulk SMS'],
                    ];
                    $visibleCommsLinks = collect($commsLinks)->filter(fn ($l) => $u->hasPermission($l['perm']));
                ?>
                <?php if($visibleCommsLinks->isNotEmpty()): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center <?php echo e($commsActive ? 'active' : ''); ?>" data-bs-toggle="collapse" href="#commsMenu" role="button" aria-expanded="<?php echo e($commsActive ? 'true' : 'false'); ?>">
                        <span><i class="bi bi-broadcast"></i> Communication</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse <?php echo e($commsActive ? 'show' : ''); ?>" id="commsMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            <?php $__currentLoopData = $visibleCommsLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="nav-item"><a class="nav-link py-1 <?php echo e(request()->routeIs($link['routeIs']) ? 'active' : ''); ?>" href="<?php echo e(route($link['route'])); ?>"><i class="bi <?php echo e($link['icon']); ?>"></i> <?php echo $link['label']; ?></a></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                
                <?php
                    $staffActive = request()->routeIs('admin.employees.*')
                        || request()->routeIs('admin.payslips.*')
                        || request()->routeIs('admin.staff_attendance.*')
                        || request()->routeIs('admin.leave_requests.*')
                        || request()->routeIs('admin.loan_requests.*')
                        || request()->routeIs('admin.settings.school_profile.*');
                    $staffLinks = [
                        ['perm' => 'manage_employees', 'route' => 'admin.employees.index', 'routeIs' => 'admin.employees.*', 'icon' => 'bi-person-lines-fill', 'label' => 'Employees'],
                        ['perm' => 'generate_payslips', 'route' => 'admin.payslips.index', 'routeIs' => 'admin.payslips.*', 'icon' => 'bi-wallet2', 'label' => 'Payroll'],
                        ['perm' => 'view_staff_attendance', 'route' => 'admin.staff_attendance.index', 'routeIs' => 'admin.staff_attendance.*', 'icon' => 'bi-fingerprint', 'label' => 'Attendance'],
                        ['perm' => 'manage_leave_requests', 'route' => 'admin.leave_requests.index', 'routeIs' => 'admin.leave_requests.*', 'icon' => 'bi-calendar-x', 'label' => 'Leave'],
                        ['perm' => 'manage_loan_requests', 'route' => 'admin.loan_requests.index', 'routeIs' => 'admin.loan_requests.*', 'icon' => 'bi-cash-stack', 'label' => 'Loans'],
                        ['perm' => 'manage_settings', 'route' => 'admin.settings.school_profile.edit', 'routeIs' => 'admin.settings.school_profile.*', 'icon' => 'bi-geo-alt', 'label' => 'Location'],
                    ];
                    $visibleStaffLinks = collect($staffLinks)->filter(fn ($l) => $u->hasPermission($l['perm']));
                ?>
                <?php if($visibleStaffLinks->isNotEmpty()): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center <?php echo e($staffActive ? 'active' : ''); ?>" data-bs-toggle="collapse" href="#staffMenu" role="button" aria-expanded="<?php echo e($staffActive ? 'true' : 'false'); ?>">
                        <span><i class="bi bi-briefcase-fill"></i> Staff</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse <?php echo e($staffActive ? 'show' : ''); ?>" id="staffMenu">
                        <ul class="nav nav-pills flex-column gap-1 ms-3 mt-1">
                            <?php $__currentLoopData = $visibleStaffLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="nav-item"><a class="nav-link py-1 <?php echo e(request()->routeIs($link['routeIs']) ? 'active' : ''); ?>" href="<?php echo e(route($link['route'])); ?>"><i class="bi <?php echo e($link['icon']); ?>"></i> <?php echo $link['label']; ?></a></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if($u->hasPermission('manage_settings') || $u->hasPermission('manage_rights')): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings.index')); ?>"><i class="bi bi-gear-fill"></i> Settings</a></li>
                <?php endif; ?>
                <?php if(\App\Models\Employee::where('user_id', $u->id)->exists()): ?>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('staff.clock.*') ? 'active' : ''); ?>" href="<?php echo e(route('staff.clock.index')); ?>"><i class="bi bi-fingerprint"></i> Clock In/Out</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('staff.leave.*') ? 'active' : ''); ?>" href="<?php echo e(route('staff.leave.index')); ?>"><i class="bi bi-calendar-x"></i> Leave</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('staff.loans.*') ? 'active' : ''); ?>" href="<?php echo e(route('staff.loans.index')); ?>"><i class="bi bi-cash-stack"></i> Loans</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('staff.payslips.*') ? 'active' : ''); ?>" href="<?php echo e(route('staff.payslips.index')); ?>"><i class="bi bi-wallet2"></i> Payslips</a></li>
                <?php endif; ?>
            <?php elseif(auth()->user()->role === 'teacher'): ?>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('teacher.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('teacher.dashboard')); ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('teacher.attendance.*') ? 'active' : ''); ?>" href="<?php echo e(route('teacher.attendance.index')); ?>"><i class="bi bi-calendar-check"></i> Attendance</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('teacher.clock.*') ? 'active' : ''); ?>" href="<?php echo e(route('teacher.clock.index')); ?>"><i class="bi bi-fingerprint"></i> Clock In/Out</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('staff.leave.*') ? 'active' : ''); ?>" href="<?php echo e(route('staff.leave.index')); ?>"><i class="bi bi-calendar-x"></i> Leave</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('staff.loans.*') ? 'active' : ''); ?>" href="<?php echo e(route('staff.loans.index')); ?>"><i class="bi bi-cash-stack"></i> Loans</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('staff.payslips.*') ? 'active' : ''); ?>" href="<?php echo e(route('staff.payslips.index')); ?>"><i class="bi bi-wallet2"></i> Payslips</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('teacher.timetable.*') ? 'active' : ''); ?>" href="<?php echo e(route('teacher.timetable.index')); ?>"><i class="bi bi-calendar-week"></i> Timetable</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('teacher.results.*') ? 'active' : ''); ?>" href="<?php echo e(route('teacher.results.index')); ?>"><i class="bi bi-clipboard-data"></i> Results</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('teacher.cbc.*') ? 'active' : ''); ?>" href="<?php echo e(route('teacher.cbc.index')); ?>"><i class="bi bi-award"></i> CBC</a></li>
            <?php elseif(auth()->user()->role === 'student'): ?>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('student.dashboard')); ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.results.*') ? 'active' : ''); ?>" href="<?php echo e(route('student.results.index')); ?>"><i class="bi bi-clipboard-data"></i> Results</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.performance.*') ? 'active' : ''); ?>" href="<?php echo e(route('student.performance.index')); ?>"><i class="bi bi-graph-up-arrow"></i> Performance</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.cbc_report') ? 'active' : ''); ?>" href="<?php echo e(route('student.cbc_report')); ?>"><i class="bi bi-award"></i> CBC Report</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.fees.*') ? 'active' : ''); ?>" href="<?php echo e(route('student.fees.index')); ?>"><i class="bi bi-cash-coin"></i> Fees</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.class.*') ? 'active' : ''); ?>" href="<?php echo e(route('student.class.index')); ?>"><i class="bi bi-people"></i> My Class</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.teachers.*') ? 'active' : ''); ?>" href="<?php echo e(route('student.teachers.index')); ?>"><i class="bi bi-person-workspace"></i> Teachers</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.activities.*') ? 'active' : ''); ?>" href="<?php echo e(route('student.activities.index')); ?>"><i class="bi bi-stars"></i> Activities</a></li>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('student.library.*') ? 'active' : ''); ?>" href="<?php echo e(route('student.library.index')); ?>"><i class="bi bi-journal-bookmark"></i> Library</a></li>
            <?php elseif(auth()->user()->role === 'parent'): ?>
                <li class="nav-item"><a class="nav-link <?php echo e(request()->routeIs('parent.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('parent.dashboard')); ?>"><i class="bi bi-speedometer2"></i> My Children</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="main-content p-4">
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/layouts/app.blade.php ENDPATH**/ ?>