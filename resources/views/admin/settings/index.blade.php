@extends('layouts.app')
@section('title', 'Settings')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0"><i class="bi bi-gear-fill"></i> Settings</h3>
</div>

<div class="row g-3">
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
</div>

@endsection
