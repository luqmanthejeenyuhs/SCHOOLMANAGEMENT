@extends('layouts.app')
@section('title', 'Receipt ' . $receipt->receiptNumber())
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h3 class="mb-0">Receipt</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.receipts.index') }}" class="btn btn-outline-dark btn-sm">Back to Receipts</a>
        <button class="btn btn-dark btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

<div class="card p-4" style="max-width:600px;">
    <div class="text-center mb-3">
        <h5 class="mb-0">PAYMENT RECEIPT</h5>
        <small class="text-muted">{{ $receipt->receiptNumber() }}</small>
    </div>

    <div class="row mb-3">
        <div class="col-6"><strong>Student:</strong> {{ $receipt->payment->invoice->student->user->name }}</div>
        <div class="col-6"><strong>Admission No:</strong> {{ $receipt->payment->invoice->student->admission_no }}</div>
        <div class="col-6 mt-1"><strong>Fee Type:</strong> {{ $receipt->payment->invoice->feeType->name }}</div>
        <div class="col-6 mt-1"><strong>Date:</strong> {{ $receipt->payment->payment_date->format('d M Y') }}</div>
        <div class="col-6 mt-1"><strong>Method:</strong> <span class="text-capitalize">{{ str_replace('_', ' ', $receipt->payment->method) }}</span></div>
        @if($receipt->payment->bank_name)
            <div class="col-6 mt-1"><strong>Bank:</strong> {{ $receipt->payment->bank_name }}</div>
        @endif
        @if($receipt->payment->reference)
            <div class="col-6 mt-1"><strong>Reference:</strong> {{ $receipt->payment->reference }}</div>
        @endif
        <div class="col-6 mt-1"><strong>Received By:</strong> {{ $receipt->issuedBy->name ?? '—' }}</div>
    </div>

    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-3">
        <strong>AMOUNT PAID</strong>
        <strong class="fs-4 text-success">KES {{ number_format($receipt->payment->amount_paid, 2) }}</strong>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <span class="text-muted">Invoice Balance After This Payment</span>
        <span class="fw-semibold">KES {{ number_format($receipt->payment->invoice->balance(), 2) }}</span>
    </div>

    <p class="small text-muted mt-4 mb-0 text-center">Thank you for your payment.</p>
</div>

<style>
@media print {
    .navbar, .sidebar, .no-print { display: none !important; }
}
</style>
@endsection
