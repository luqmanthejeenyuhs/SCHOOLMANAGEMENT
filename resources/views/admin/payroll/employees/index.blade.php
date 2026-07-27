@extends('layouts.app')
@section('title', 'Staff & Payroll')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Staff &amp; Payroll</h3>
    <div>
        <a href="{{ route('admin.payslips.index') }}" class="btn btn-outline-dark me-2">View Payslips</a>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Add Employee</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Name</th><th>Job Title</th><th>KRA PIN</th><th>Basic Salary</th><th>Gross Pay</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td>{{ $employee->name }} @if($employee->is_teaching_staff)<span class="badge bg-info text-dark">Teaching</span>@endif</td>
                    <td>{{ $employee->job_title }}</td>
                    <td>{{ $employee->kra_pin ?? '—' }}</td>
                    <td>KES {{ number_format($employee->basic_salary, 2) }}</td>
                    <td>KES {{ number_format($employee->grossPay(), 2) }}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payslipModal{{ $employee->id }}">Generate Payslip</button>
                        <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this employee?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>

                        <div class="modal fade" id="payslipModal{{ $employee->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.payslips.generate', $employee) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Generate Payslip — {{ $employee->name }}</h6></div>
                                        <div class="modal-body row g-2">
                                            <div class="col-6">
                                                <label class="form-label small">Month</label>
                                                <select name="month" class="form-select">
                                                    @foreach(range(1,12) as $m)
                                                        <option value="{{ $m }}" @selected($m == now()->month)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">Year</label>
                                                <input type="number" name="year" class="form-control" value="{{ now()->year }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-dark">Generate</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No employees yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $employees->links() }}</div>
@endsection
