@extends('layouts.app')
@section('title', 'Suppliers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Suppliers</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.suppliers.bills.index') }}" class="btn btn-outline-dark"><i class="bi bi-receipt"></i> All Bills</a>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Add Supplier</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Name</th><th>Contact</th><th>KRA PIN</th><th>Terms</th><th>Bills</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($suppliers as $supplier)
                <tr>
                    <td class="fw-semibold">{{ $supplier->name }}</td>
                    <td>{{ $supplier->contact_person }} @if($supplier->phone)<br><span class="text-muted small">{{ $supplier->phone }}</span>@endif</td>
                    <td>{{ $supplier->kra_pin ?? '—' }}</td>
                    <td>{{ $supplier->payment_terms_days }} days</td>
                    <td>{{ $supplier->bills_count }}</td>
                    <td>
                        @if($supplier->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No suppliers added yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $suppliers->links() }}</div>
@endsection
