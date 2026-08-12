@extends('layouts.app')
@section('title', 'User Rights')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <nav style="--bs-breadcrumb-divider: '›';"><ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}" class="text-decoration-none">Settings</a></li>
            <li class="breadcrumb-item active">User Rights</li>
        </ol></nav>
        <h3 class="mb-0"><i class="bi bi-shield-lock"></i> User Rights</h3>
    </div>
    <form method="GET" action="{{ route('admin.settings.rights.index') }}" class="d-flex" style="min-width:260px;">
        <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Search by name or email...">
        <button class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Rights Assigned</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><span class="badge bg-secondary text-uppercase">{{ $user->role }}</span></td>
                <td>
                    @if($user->is_super_admin)
                        <span class="text-muted small">All rights (super admin)</span>
                    @else
                        {{ $user->permissions_count }} assigned
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.settings.rights.edit', $user) }}" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-sliders"></i> Manage
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">No users match your search.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $users->links() }}</div>

@endsection
