@extends('layouts.app')
@section('title', 'Change Password')
@section('content')
<h3 class="mb-3">Change Password</h3>

@if(auth()->user()->must_change_password)
<div class="alert alert-warning">
    Your account was created with a system-generated password. Please choose your own before continuing.
</div>
@endif

<div class="card p-4" style="max-width:480px;">
    <form method="POST" action="{{ route('account.password.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autofocus>
            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">At least 8 characters.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button class="btn btn-dark w-100">Update Password</button>
    </form>
</div>
@endsection
