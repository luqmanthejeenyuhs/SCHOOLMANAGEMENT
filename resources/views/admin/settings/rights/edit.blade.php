@extends('layouts.app')
@section('title', 'Manage Rights - '.$user->name)
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <nav style="--bs-breadcrumb-divider: '›';"><ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}" class="text-decoration-none">Settings</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.rights.index') }}" class="text-decoration-none">User Rights</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol></nav>
        <h3 class="mb-0"><i class="bi bi-person-gear"></i> {{ $user->name }}
            <span class="badge bg-secondary text-uppercase align-middle">{{ $user->role }}</span>
            @if($user->is_super_admin)
                <span class="badge bg-warning text-dark align-middle">Super Admin</span>
            @endif
        </h3>
        <small class="text-muted">{{ $user->email }}</small>
    </div>
    <a href="{{ route('admin.settings.rights.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Rights & Permissions</div>
            <div class="card-body">
                @if($user->is_super_admin)
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> This user is a <strong>super admin</strong> and automatically has every right in the system. Rights checkboxes only apply to regular accounts (admin, teacher, student, parent) that aren't marked as super admin.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.settings.rights.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <ul class="nav nav-tabs mb-3" id="rightsTabs" role="tablist">
                            @foreach($categories as $category => $items)
                                <li class="nav-item">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-{{ \Illuminate\Support\Str::slug($category) }}" type="button">
                                        {{ $category }}
                                        <span class="badge rounded-pill bg-light text-dark border ms-1 cat-count" data-category="{{ \Illuminate\Support\Str::slug($category) }}">0</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mb-3">
                            @foreach($categories as $category => $items)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ \Illuminate\Support\Str::slug($category) }}">
                                    <div class="d-flex justify-content-end mb-2">
                                        <button type="button" class="btn btn-sm btn-link p-0 me-3 select-all" data-category="{{ \Illuminate\Support\Str::slug($category) }}">Select all</button>
                                        <button type="button" class="btn btn-sm btn-link p-0 text-danger clear-all" data-category="{{ \Illuminate\Support\Str::slug($category) }}">Clear</button>
                                    </div>
                                    <div class="row">
                                        @foreach($items as $permission)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input right-checkbox" type="checkbox" data-category="{{ \Illuminate\Support\Str::slug($category) }}"
                                                           name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}"
                                                           {{ in_array($permission->key, $userPermissionKeys) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Save Rights</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-key"></i> Reset Password</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.rights.reset_password', $user) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">New password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to auto-generate" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Confirm password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password" minlength="6">
                    </div>
                    <button type="submit" class="btn btn-outline-dark w-100" onclick="return confirm('Reset the password for {{ $user->name }}?');">
                        <i class="bi bi-arrow-repeat"></i> Reset Password
                    </button>
                    <small class="text-muted d-block mt-2">Leave both fields blank to auto-generate a random temporary password — it will be shown once after resetting.</small>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateCatCounts() {
        document.querySelectorAll('.cat-count').forEach(function (badge) {
            var cat = badge.dataset.category;
            var checked = document.querySelectorAll('.right-checkbox[data-category="' + cat + '"]:checked').length;
            badge.textContent = checked;
        });
    }
    document.querySelectorAll('.right-checkbox').forEach(function (cb) {
        cb.addEventListener('change', updateCatCounts);
    });
    document.querySelectorAll('.select-all').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.right-checkbox[data-category="' + btn.dataset.category + '"]').forEach(function (cb) { cb.checked = true; });
            updateCatCounts();
        });
    });
    document.querySelectorAll('.clear-all').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.right-checkbox[data-category="' + btn.dataset.category + '"]').forEach(function (cb) { cb.checked = false; });
            updateCatCounts();
        });
    });
    updateCatCounts();
</script>
@endsection
