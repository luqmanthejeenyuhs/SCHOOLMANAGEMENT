@extends('layouts.app')
@section('title', 'General Ledger')
@section('content')
<h3 class="mb-3">General Ledger</h3>

<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('admin.accounting.ledger') }}" class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label small">Account</label>
            <select name="account_id" class="form-select" onchange="this.form.submit()">
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" @selected($selectedAccount && $selectedAccount->id === $account->id)>
                        {{ $account->code }} — {{ $account->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>

@if($selectedAccount)
<div class="card">
    <div class="card-header bg-transparent fw-semibold" style="color:var(--brand-green-dark);">
        {{ $selectedAccount->code }} — {{ $selectedAccount->name }}
        <span class="badge bg-light text-dark text-capitalize ms-2">{{ $selectedAccount->type }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Date</th><th>Memo</th><th>Reference</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Running Balance</th></tr>
            </thead>
            <tbody>
            @forelse($lines as $line)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($line->entry->date)->format('d M Y') }}</td>
                    <td>{{ $line->entry->memo }}</td>
                    <td class="text-muted small">{{ $line->entry->reference }}</td>
                    <td class="text-end font-monospace">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                    <td class="text-end font-monospace">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                    <td class="text-end font-monospace fw-semibold">{{ number_format($line->running_balance, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No activity on this account yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
