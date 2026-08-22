@extends('layouts.app')
@section('title', 'Receipts')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Receipts</h3>
    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-dark btn-sm">Back to Invoices &amp; Payments</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Receipt No.</th>
                    <th>Student</th>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($receipts as $receipt)
                <tr>
                    <td class="fw-semibold">{{ $receipt->receiptNumber() }}</td>
                    <td>{{ $receipt->payment->invoice->student->user->name }}</td>
                    <td>{{ $receipt->payment->invoice->feeType->name }}</td>
                    <td>KES {{ number_format($receipt->payment->amount_paid, 2) }}</td>
                    <td class="text-capitalize">{{ str_replace('_', ' ', $receipt->payment->method) }}</td>
                    <td>{{ $receipt->payment->payment_date->format('d M Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No receipts issued yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $receipts->links() }}</div>
@endsection
