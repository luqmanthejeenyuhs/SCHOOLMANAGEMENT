@extends('layouts.app')
@section('title', 'Parents')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Parents / Guardians</h3>
    <a href="{{ route('admin.parents.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Add Parent</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Children Linked</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($parents as $parent)
                <tr>
                    <td><a href="{{ route('admin.parents.show', $parent) }}" class="fw-semibold text-decoration-none">{{ $parent->name }}</a></td>
                    <td>{{ $parent->email }}</td>
                    <td>{{ $parent->phone ?? '—' }}</td>
                    <td>{{ $parent->children_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.parents.show', $parent) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this parent account?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No parent accounts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $parents->links() }}</div>
@endsection
