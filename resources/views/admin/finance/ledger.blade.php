@extends('layouts.app')
@section('title', 'Bank & M-Pesa Ledger')
@section('content')
<h3 class="mb-1">Bank &amp; M-Pesa Ledger</h3>
<p class="text-muted small">Every deposit that lands here came in automatically — no bank slips, no manual receipt books. Parents deposit at any branch/agent or pay via Paybill using the student's <strong>Admission Number</strong> as the reference, and the webhook below credits the invoice and texts a receipt instantly.</p>

<div class="row g-3 mb-2">
    <div class="col-md-6">
        <div class="card p-3">
            <div class="text-muted small">Bank Webhook URL</div>
            <code class="small">{{ route('webhooks.bank.deposit') }}</code>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <div class="text-muted small">M-Pesa C2B Confirmation URL (register on Daraja)</div>
            <code class="small">{{ route('mpesa.c2b.confirmation') }}</code>
        </div>
    </div>
</div>

<h5 class="mt-4">Bank Deposits</h5>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Bank</th><th>Reference</th><th>Account Ref (typed)</th><th>Amount</th><th>Student</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($bankTransactions as $tx)
                <tr>
                    <td>{{ $tx->bank_name }}</td>
                    <td class="small">{{ $tx->bank_reference }}</td>
                    <td>{{ $tx->account_reference }}</td>
                    <td>KES {{ number_format($tx->amount, 2) }}</td>
                    <td>{{ $tx->student->user->name ?? '—' }}</td>
                    <td>
                        @if($tx->status === 'matched')
                            <span class="badge bg-success">Matched</span>
                        @else
                            <span class="badge bg-danger">Unmatched</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($tx->status !== 'matched')
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bankReconcile{{ $tx->id }}">Reconcile</button>
                        <div class="modal fade" id="bankReconcile{{ $tx->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.finance.ledger.bank.reconcile', $tx) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Reconcile KES {{ number_format($tx->amount,2) }} — {{ $tx->bank_reference }}</h6></div>
                                        <div class="modal-body">
                                            <label class="form-label small">The depositor typed "{{ $tx->account_reference }}" — match to the correct admission number:</label>
                                            <input type="text" name="admission_no" class="form-control" placeholder="ADM-0001" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-dark">Match &amp; Credit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No bank deposits received yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $bankTransactions->links() }}</div>

<h5 class="mt-4">M-Pesa Paybill (C2B) Payments</h5>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Transaction ID</th><th>Phone</th><th>Account Ref (typed)</th><th>Amount</th><th>Student</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($mpesaTransactions as $tx)
                <tr>
                    <td class="small">{{ $tx->transaction_id }}</td>
                    <td>{{ $tx->msisdn }}</td>
                    <td>{{ $tx->bill_ref_number }}</td>
                    <td>KES {{ number_format($tx->amount, 2) }}</td>
                    <td>{{ $tx->student->user->name ?? '—' }}</td>
                    <td>
                        @if($tx->status === 'matched')
                            <span class="badge bg-success">Matched</span>
                        @else
                            <span class="badge bg-danger">Unmatched</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($tx->status !== 'matched')
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mpesaReconcile{{ $tx->id }}">Reconcile</button>
                        <div class="modal fade" id="mpesaReconcile{{ $tx->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.finance.ledger.mpesa.reconcile', $tx) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Reconcile KES {{ number_format($tx->amount,2) }} — {{ $tx->transaction_id }}</h6></div>
                                        <div class="modal-body">
                                            <label class="form-label small">The payer typed "{{ $tx->bill_ref_number }}" at the Paybill menu — match to the correct admission number:</label>
                                            <input type="text" name="admission_no" class="form-control" placeholder="ADM-0001" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-dark">Match &amp; Credit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No M-Pesa Paybill payments received yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $mpesaTransactions->links() }}</div>
@endsection
