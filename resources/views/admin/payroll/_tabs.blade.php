<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
            <i class="bi bi-people"></i> Employees
        </a>
    </li>
    @if(auth()->user()->hasPermission('generate_payslips'))
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.payslips.*') ? 'active' : '' }}" href="{{ route('admin.payslips.index') }}">
            <i class="bi bi-wallet2"></i> Payroll
        </a>
    </li>
    @endif
    @if(auth()->user()->hasPermission('manage_employees'))
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.leave_requests.*') ? 'active' : '' }}" href="{{ route('admin.leave_requests.index') }}">
            <i class="bi bi-calendar-x"></i> Leave Requests
        </a>
    </li>
    @endif
    @if(auth()->user()->hasPermission('manage_loans'))
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.loans.*') ? 'active' : '' }}" href="{{ route('admin.loans.index') }}">
            <i class="bi bi-cash-stack"></i> Loans &amp; Advances
        </a>
    </li>
    @endif
    @if(auth()->user()->hasPermission('manage_employees'))
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.deduction-types.*') ? 'active' : '' }}" href="{{ route('admin.deduction-types.index') }}">
            <i class="bi bi-list-check"></i> Statutory Deductions
        </a>
    </li>
    @endif
</ul>
