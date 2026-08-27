@extends('layouts.app')
@section('title', 'Settings')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0"><i class="bi bi-gear-fill"></i> Settings</h3>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('admin.settings.school_profile.edit') }}" class="text-decoration-none text-dark">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-building"></i>
                    <div>
                        <h6 class="mb-1">School Profile</h6>
                        <small class="text-muted">Compound location, geofence radius, and expected clock-in/out times.</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('admin.settings.rights.index') }}" class="text-decoration-none text-dark">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-shield-lock"></i>
                    <div>
                        <h6 class="mb-1">User Rights</h6>
                        <small class="text-muted">Assign or remove what each user can do, and reset passwords.</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @if(auth()->user()->hasPermission('view_audit_logs'))
    <div class="col-md-4">
        <a href="{{ route('admin.settings.audit_logs.index') }}" class="text-decoration-none text-dark">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-clock-history"></i>
                    <div>
                        <h6 class="mb-1">Audit Trail</h6>
                        <small class="text-muted">See who signed in, when, and from which IP address.</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endif
    <div class="col-md-4">
        <a href="{{ route('admin.settings.audit_logs.index') }}" class="text-decoration-none text-dark">
            <div class="card stat-card h-100 p-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-clock-history"></i>
                    <div>
                        <h6 class="mb-1">Audit Trail</h6>
                        <small class="text-muted">See who logged in, when, and from which IP address.</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

@endsection
