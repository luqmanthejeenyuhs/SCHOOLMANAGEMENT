@extends('layouts.app')
@section('title', 'Invoices & Payments')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Invoices &amp; Payments</h3>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#bulkInvoiceModal">
        <i class="bi bi-lightning-charge"></i> Bulk Generate (Term Billing)
    </button>
</div>

<div class="card p-3 mb-4">
    <h6>Generate Single Invoice</h6>
    <form method="POST" action="{{ route('admin.invoices.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-3">
            <label class="form-label small">Student</label>
            <select name="student_id" class="form-select" required>
                <option value="">Select student</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->admission_no }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Fee Type</label>
            <select name="fee_type_id" id="feeTypeSelect" class="form-select" required>
                <option value="">Select fee type</option>
                @foreach($feeTypes as $feeType)
                    <option value="{{ $feeType->id }}" data-amount="{{ $feeType->amount }}">{{ $feeType->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Amount</label>
            <input type="number" step="0.01" name="amount" id="amountInput" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Due Date</label>
            <input type="date" name="due_date" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100">Generate</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Student</th><th>Fee Type</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->student->user->name }}</td>
                    <td>{{ $invoice->feeType->name }}</td>
                    <td>KES {{ number_format($invoice->amount, 2) }}</td>
                    <td>KES {{ number_format($invoice->totalPaid(), 2) }}</td>
                    <td>KES {{ number_format($invoice->balance(), 2) }}</td>
                    <td>
                        @if($invoice->status === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($invoice->status === 'partially_paid')
                            <span class="badge bg-warning text-dark">Partial</span>
                        @else
                            <span class="badge bg-danger">Unpaid</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($invoice->status !== 'paid')
                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#mpesaModal{{ $invoice->id }}"><i class="bi bi-phone"></i> M-Pesa</button>
                        <div class="modal fade" id="mpesaModal{{ $invoice->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.invoices.mpesa_push', $invoice) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Send M-Pesa STK Push — {{ $invoice->student->user->name }}</h6></div>
                                        <div class="modal-body">
                                            <p class="small text-muted">Balance due: KES {{ number_format($invoice->balance(), 2) }}. The payer's phone will receive a prompt to enter their M-Pesa PIN.</p>
                                            <label class="form-label small">Phone Number (format 2547XXXXXXXX)</label>
                                            <input type="text" name="phone" class="form-control" placeholder="254712345678" pattern="2547[0-9]{8}" value="{{ $invoice->student->guardian_phone }}" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-success">Send STK Push</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payModal{{ $invoice->id }}">Record Payment</button>
                        <div class="modal fade" id="payModal{{ $invoice->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Record Payment — {{ $invoice->student->user->name }}</h6></div>
                                        <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label small">Amount Paid (balance: KES {{ number_format($invoice->balance(),2) }})</label>
                                                <input type="number" step="0.01" name="amount_paid" class="form-control" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">Payment Date</label>
                                                <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">Method</label>
                                                <select name="method" class="form-select">
                                                    <option value="cash">Cash</option>
                                                    <option value="mpesa">M-Pesa</option>
                                                    <option value="bank">Bank Transfer</option>
                                                    <option value="card">Card</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-dark">Save Payment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No invoices yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $invoices->links() }}</div>

<!-- Bulk Generate Invoices Modal (automated term billing) -->
<div class="modal fade" id="bulkInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.invoices.bulk_store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-lightning-charge"></i> Bulk Generate Invoices</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Apply a fee (e.g. Term 2 Tuition) to a whole class or the entire school at once, instead of one student at a time. Students who already have this exact invoice are skipped automatically, so it's safe to re-run.</p>
                    <div class="mb-2">
                        <label class="form-label">Fee Type</label>
                        <select name="fee_type_id" class="form-select" required>
                            <option value="">Select fee type</option>
                            @foreach($feeTypes as $feeType)
                                <option value="{{ $feeType->id }}">{{ $feeType->name }} — KES {{ number_format($feeType->amount, 2) }} ({{ $feeType->frequency }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Apply to</label>
                        <select name="scope" id="bulkScope" class="form-select" required>
                            <option value="all">All students in the school</option>
                            <option value="class">A specific class only</option>
                        </select>
                    </div>
                    <div class="mb-2" id="bulkClassWrap" style="display:none;">
                        <label class="form-label">Class</label>
                        <select name="school_class_id" class="form-select">
                            <option value="">Select class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Generate Invoices</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('feeTypeSelect').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    document.getElementById('amountInput').value = opt.dataset.amount || '';
});
document.getElementById('bulkScope').addEventListener('change', function () {
    document.getElementById('bulkClassWrap').style.display = this.value === 'class' ? 'block' : 'none';
});
</script>
@endsection
