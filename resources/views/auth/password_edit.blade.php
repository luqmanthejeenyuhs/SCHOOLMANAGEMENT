@extends('layouts.app')
@section('title', 'Change Password')
@section('content')

<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-key-fill"></i> Change Password</h3>
    <small class="text-muted">Only you can see or set this — not even your school admin can view your password.</small>
</div>

<div class="card p-4" style="max-width:480px;">
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('account.password.update') }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control" required minlength="6">
        </div>

        <button class="btn btn-dark">Update Password</button>
    </form>
</div>

@endsection
