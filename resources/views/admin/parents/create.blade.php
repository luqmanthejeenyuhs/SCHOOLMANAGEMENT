@extends('layouts.app')
@section('title', 'Add Parent')
@section('content')
<h3 class="mb-3">Add Parent / Guardian</h3>

<div class="card p-4" style="max-width:640px;">
    <form method="POST" action="{{ route('admin.parents.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Relationship</label>
                <select name="relationship" class="form-select">
                    <option value="Parent">Parent</option>
                    <option value="Mother">Mother</option>
                    <option value="Father">Father</option>
                    <option value="Guardian">Guardian</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Link to Child(ren)</label>
                <select name="children[]" class="form-select" multiple size="8" required>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->admission_no }} — {{ $student->user->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select more than one child.</small>
            </div>
        </div>
        <button class="btn btn-dark mt-4"><i class="bi bi-check-lg"></i> Create Parent Account</button>
        <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary mt-4">Cancel</a>
    </form>
</div>
@endsection
