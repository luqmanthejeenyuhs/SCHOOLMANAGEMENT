@extends('layouts.app')
@section('title', 'My Library')
@section('content')

<div class="mb-3">
    <h3 class="mb-0"><i class="bi bi-journal-bookmark"></i> My Library</h3>
</div>

<div class="card p-3 mb-3">
    <h6>Currently Borrowed</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Book</th><th>Copy</th><th>Issued</th><th>Due</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($currentLoans as $loan)
            @php $overdue = $loan->due_date && $loan->due_date->isPast(); @endphp
            <tr>
                <td>{{ $loan->copy->item->name ?? '—' }}</td>
                <td><code class="small">{{ $loan->copy->barcode ?? '—' }}</code></td>
                <td>{{ $loan->issued_at?->format('d M Y') }}</td>
                <td>{{ $loan->due_date?->format('d M Y') ?? '—' }}</td>
                <td>
                    @if($overdue) <span class="badge bg-danger">Overdue</span>
                    @else <span class="badge bg-success">On loan</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">You have no books currently borrowed.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card p-3">
    <h6>Past Loans</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Book</th><th>Copy</th><th>Issued</th><th>Returned</th></tr></thead>
        <tbody>
        @forelse($pastLoans as $loan)
            <tr>
                <td>{{ $loan->copy->item->name ?? '—' }}</td>
                <td><code class="small">{{ $loan->copy->barcode ?? '—' }}</code></td>
                <td>{{ $loan->issued_at?->format('d M Y') }}</td>
                <td>{{ $loan->returned_at?->format('d M Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">No past loan history yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
