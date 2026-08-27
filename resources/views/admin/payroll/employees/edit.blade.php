@extends('layouts.app')
@section('title', 'Edit Employee')
@section('content')
<h3 class="mb-3">Edit Employee</h3>
<div class="card p-4" style="max-width:800px;">
    <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Job Title</label>
                <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $employee->job_title) }}" placeholder="e.g. Bursar, Cook, Driver, Teacher" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Staff Number</label>
                <input type="text" class="form-control" value="{{ $employee->staff_number }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Link to User Account (for self clock-in)</label>
                <select name="user_id" class="form-select">
                    <option value="">— Not linked —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $employee->user_id) == $user->id)>{{ $user->name }} ({{ $user->role }})</option>
                    @endforeach
                </select>
                @if($employee->user_id)
                    <div class="form-text text-success"><i class="bi bi-check-circle"></i> Currently linked — this person can clock in/out themselves.</div>
                @else
                    <div class="form-text text-warning"><i class="bi bi-exclamation-triangle"></i> Not linked yet — this person will see "No staff record is linked to your account" if they try to clock in.</div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="is_teaching_staff" value="1" id="isTeaching" @checked(old('is_teaching_staff', $employee->is_teaching_staff))>
                    <label class="form-check-label" for="isTeaching">This is a teaching staff member</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">National ID Number</label>
                <input type="text" name="id_number" class="form-control" value="{{ old('id_number', $employee->id_number) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">KRA PIN</label>
                <input type="text" name="kra_pin" class="form-control" value="{{ old('kra_pin', $employee->kra_pin) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">NSSF Number</label>
                <input type="text" name="nssf_number" class="form-control" value="{{ old('nssf_number', $employee->nssf_number) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">SHIF Number</label>
                <input type="text" name="shif_number" class="form-control" value="{{ old('shif_number', $employee->shif_number) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Employment Date</label>
                <input type="date" name="employment_date" class="form-control" value="{{ old('employment_date', optional($employee->employment_date)->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Basic Salary (KES)</label>
                <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ old('basic_salary', $employee->basic_salary) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">House Allowance (KES)</label>
                <input type="number" step="0.01" name="house_allowance" class="form-control" value="{{ old('house_allowance', $employee->house_allowance) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Transport Allowance (KES)</label>
                <input type="number" step="0.01" name="transport_allowance" class="form-control" value="{{ old('transport_allowance', $employee->transport_allowance) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Other Allowances (KES)</label>
                <input type="number" step="0.01" name="other_allowances" class="form-control" value="{{ old('other_allowances', $employee->other_allowances) }}">
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-dark">Save Changes</button>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
