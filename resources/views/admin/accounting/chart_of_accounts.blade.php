@extends('layouts.app')
@section('title', 'Chart of Accounts')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Chart of Accounts</h3>
</div>

<div class="card p-3 mb-4">
    <h6>Add Account</h6>
    <form method="POST" action="{{ route('admin.accounting.accounts.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-2">
            <label class="form-label small">Code</label>
            <input type="text" name="code" class="form-control" placeholder="e.g. 5100" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Utilities Expense" required>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Type</label>
            <select name="type" class="form-select" required>
                <option value="asset">Asset</option>
                <option value="liability">Liability</option>
                <option value="equity">Equity</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-dark w-100">Add Account</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Code</th><th>Name</th><th>Type</th><th>Balance</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($accounts as $account)
                <tr>
                    <td class="font-monospace">{{ $account->code }}</td>
                    <td>
                        {{ $account->name }}
                        @if($account->is_system)
                            <span class="badge bg-secondary ms-1" title="Used by automatic posting — don't repurpose this code">System</span>
                        @endif
                    </td>
                    <td><span class="text-capitalize">{{ $account->type }}</span></td>
                    <td class="font-monospace">
                        KES {{ number_format(abs($account->balance()), 2) }}
                        @if($account->balance() < 0)
                            <span class="text-danger small">(abnormal)</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.accounting.ledger', ['account_id' => $account->id]) }}" class="btn btn-sm btn-outline-dark">View Ledger</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
