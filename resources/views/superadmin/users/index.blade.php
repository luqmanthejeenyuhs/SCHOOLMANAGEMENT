@extends('layouts.app')
@section('title', 'User Lookup')
@section('content')

<h3 class="mb-3"><i class="bi bi-people"></i> User Lookup</h3>
<p class="text-muted">Find any user across every school — useful for support requests where you don't yet know which school they belong to.</p>

<form method="GET" class="mb-3" style="max-width:400px;">
    <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Search by name, email, or username...">
</form>

<div class="card">
    <table class="table mb-0 align-middle">
        <thead class="table-light">
            <tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>School</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td><code class="small">{{ $user->username ?? '—' }}</code></td>
                <td>{{ $user->email }}</td>
                <td><span class="badge bg-secondary text-uppercase">{{ $user->role }}</span></td>
                <td>{{ $user->school?->name ?? '—' }}</td>
                <td>
                    @if($user->school)
                        <form method="POST" action="{{ route('superadmin.schools.impersonate', $user->school) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-dark">Impersonate School</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ $search ? 'No users match that search.' : 'No users yet.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $users->links() }}</div>

@endsection
