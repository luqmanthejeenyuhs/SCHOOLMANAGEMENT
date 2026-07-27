@extends('layouts.app')
@section('title', 'Textbooks')
@section('content')
<h3 class="mb-1">Textbook Lending</h3>
<p class="text-muted small">Each physical copy is tracked by a unique barcode/serial. Plug in a USB or Bluetooth barcode scanner — it types straight into the fields below like a keyboard, no special integration needed.</p>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Register a New Copy</h6>
            <form method="POST" action="{{ route('admin.textbooks.copies.store') }}">
                @csrf
                <div class="mb-2">
                    <select name="inventory_item_id" class="form-select" required>
                        <option value="">Select textbook title</option>
                        @foreach($textbookTitles as $title)
                            <option value="{{ $title->id }}">{{ $title->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">No title yet? Add one under Inventory &amp; Store as category "Textbook Title" first.</div>
                </div>
                <div class="mb-2">
                    <input type="text" name="barcode" class="form-control" placeholder="Scan or type barcode/serial" autofocus required>
                </div>
                <button class="btn btn-dark w-100">Add Copy to Library</button>
            </form>
        </div>

        <div class="card p-3 mt-3">
            <h6>Issue a Copy</h6>
            <form method="POST" action="{{ route('admin.textbooks.issue') }}">
                @csrf
                <div class="mb-2">
                    <input type="text" name="barcode" class="form-control" placeholder="Scan or type barcode" required>
                </div>
                <div class="mb-2">
                    <input type="text" name="admission_no" class="form-control" placeholder="Student Admission No" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Due back by</label>
                    <input type="date" name="due_date" class="form-control">
                </div>
                <button class="btn btn-outline-dark w-100">Issue Copy</button>
            </form>
        </div>

        <div class="card p-3 mt-3">
            <h6>Return a Copy</h6>
            <form method="POST" action="{{ route('admin.textbooks.return') }}">
                @csrf
                <div class="mb-2">
                    <input type="text" name="barcode" class="form-control" placeholder="Scan or type barcode" required>
                </div>
                <div class="mb-2">
                    <select name="condition_at_return" class="form-select" required>
                        <option value="good">Good condition</option>
                        <option value="fair">Fair condition</option>
                        <option value="damaged">Damaged — raise penalty</option>
                        <option value="lost">Lost — raise penalty</option>
                    </select>
                </div>
                <button class="btn btn-outline-danger w-100">Return / Check In</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">Active Loans</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Barcode</th><th>Title</th><th>Student</th><th>Issued</th><th>Due</th></tr></thead>
                    <tbody>
                    @forelse($activeLoans as $loan)
                        <tr>
                            <td class="small">{{ $loan->copy->barcode }}</td>
                            <td>{{ $loan->copy->item->name }}</td>
                            <td>{{ $loan->student->user->name ?? '—' }}</td>
                            <td class="small text-muted">{{ $loan->issued_at?->format('d M Y') }}</td>
                            <td class="small">{{ $loan->due_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No active loans right now.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">All Copies</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light"><tr><th>Barcode</th><th>Title</th><th>Condition</th><th>Status</th><th>Holder</th></tr></thead>
                    <tbody>
                    @forelse($copies as $copy)
                        <tr>
                            <td class="small">{{ $copy->barcode }}</td>
                            <td>{{ $copy->item->name }}</td>
                            <td>
                                @if($copy->condition === 'good') <span class="badge bg-success">Good</span>
                                @elseif($copy->condition === 'fair') <span class="badge bg-info text-dark">Fair</span>
                                @elseif($copy->condition === 'damaged') <span class="badge bg-warning text-dark">Damaged</span>
                                @else <span class="badge bg-danger">Lost</span>
                                @endif
                            </td>
                            <td>{{ $copy->status === 'issued' ? 'Issued' : 'In Store' }}</td>
                            <td class="small">{{ $copy->currentStudent->user->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No copies registered yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">{{ $copies->links() }}</div>
    </div>
</div>
@endsection
