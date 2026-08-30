@extends('layouts.app')
@section('title', 'Add School')
@section('content')
<h3 class="mb-3">Add School</h3>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('superadmin.schools.store') }}">
    @csrf

    <div class="card p-4 mb-3" style="max-width:700px;">
        <h6 class="mb-3">School Details</h6>
        <div class="mb-3">
            <label class="form-label">School Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Little Heaven Academy" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Subdomain</label>
            <div class="input-group">
                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="littleheaven" pattern="[a-z0-9\-]+" required>
                <span class="input-group-text">.{{ config('school.platform_domain', 'taalumasms.co.ke') }}</span>
            </div>
            <div class="form-text">Lowercase letters, numbers, and hyphens only — this becomes their login URL.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Plan</label>
            <select name="plan" class="form-select" required>
                <option value="trial" @selected(old('plan', 'trial') === 'trial')>Trial</option>
                <option value="basic" @selected(old('plan') === 'basic')>Basic</option>
                <option value="premium" @selected(old('plan') === 'premium')>Premium</option>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">School Email (optional)</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">School Phone (optional)</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
        </div>
        <div class="mb-0">
            <label class="form-label">Address (optional)</label>
            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
        </div>
    </div>

    <div class="card p-4 mb-3" style="max-width:700px;">
        <h6 class="mb-1">First Admin Account</h6>
        <p class="text-muted small">This is who the school logs in as first. A secure temporary password is generated automatically and emailed directly to them — you never see it, and they'll set their own password the first time they log in.</p>
        <div class="mb-3">
            <label class="form-label">Admin Full Name</label>
            <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
        </div>
        <div class="mb-0">
            <label class="form-label">Admin Email</label>
            <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
            <div class="form-text">Their login link and temporary password go here — make sure it's correct.</div>
        </div>
    </div>

    <button class="btn btn-dark">Create School</button>
    <a href="{{ route('superadmin.schools.index') }}" class="btn btn-outline-secondary">Cancel</a>
</form>
@endsection
