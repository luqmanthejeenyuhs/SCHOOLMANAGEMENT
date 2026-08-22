@extends('layouts.app')
@section('title', 'Journal Entries')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Journal Entries</h3>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#newEntryModal">
        <i class="bi bi-plus-lg"></i> New Manual Entry
    </button>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Date</th><th>Memo</th><th>Reference</th><th>Source</th><th>Lines</th><th class="text-end">Amount</th></tr>
            </thead>
            <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($entry->date)->format('d M Y') }}</td>
                    <td>{{ $entry->memo }}</td>
                    <td class="text-muted small">{{ $entry->reference }}</td>
                    <td>
                        @if($entry->source_type === 'manual')
                            <span class="badge bg-secondary">Manual</span>
                        @else
                            <span class="badge bg-success">Auto — {{ ucfirst($entry->source_type) }}</span>
                        @endif
                    </td>
                    <td class="small">
                        @foreach($entry->lines as $line)
                            <div>{{ $line->account->code }} {{ $line->account->name }} —
                                @if($line->debit > 0) Dr {{ number_format($line->debit, 2) }}
                                @else Cr {{ number_format($line->credit, 2) }}
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td class="text-end font-monospace">KES {{ number_format($entry->totalDebit(), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No journal entries yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $entries->links() }}</div>

<div class="modal fade" id="newEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.accounting.journal_entries.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Manual Journal Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Total debits must equal total credits before this will save.</p>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Reference (optional)</label>
                            <input type="text" name="reference" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Memo</label>
                            <input type="text" name="memo" class="form-control" placeholder="e.g. May electricity bill" required>
                        </div>
                    </div>

                    <table class="table table-sm" id="linesTable">
                        <thead>
                            <tr><th>Account</th><th style="width:140px;">Debit</th><th style="width:140px;">Credit</th><th></th></tr>
                        </thead>
                        <tbody>
                            @for($i = 0; $i < 2; $i++)
                            <tr>
                                <td>
                                    <select name="account_id[]" class="form-select form-select-sm" required>
                                        <option value="">Select account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" name="debit[]" class="form-control form-control-sm"></td>
                                <td><input type="number" step="0.01" name="credit[]" class="form-control form-control-sm"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger removeLine">&times;</button></td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                    <button type="button" id="addLine" class="btn btn-sm btn-outline-secondary"><i class="bi bi-plus"></i> Add Line</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Post Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('addLine').addEventListener('click', function () {
    const tbody = document.querySelector('#linesTable tbody');
    const row = tbody.rows[0].cloneNode(true);
    row.querySelectorAll('input').forEach(i => i.value = '');
    row.querySelector('select').value = '';
    tbody.appendChild(row);
});
document.querySelector('#linesTable tbody').addEventListener('click', function (e) {
    if (e.target.classList.contains('removeLine')) {
        const tbody = document.querySelector('#linesTable tbody');
        if (tbody.rows.length > 2) e.target.closest('tr').remove();
    }
});
</script>
@endpush
