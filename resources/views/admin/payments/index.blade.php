@extends('layouts.app')
@section('title', 'Invoices & Payments')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Invoices &amp; Payments</h3>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#generateInvoiceModal">
        <i class="bi bi-cash-coin"></i> Generate Invoice
    </button>
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
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.invoices.mpesa_push', ['invoice' => $invoice]) }}">
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
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.invoices.payments.store', ['invoice' => $invoice]) }}">
                                        @csrf
                                        <div class="modal-header"><h6 class="modal-title">Record Payment — {{ $invoice->student->user->name }}</h6></div>
                                        <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label small">Amount Paid (balance: KES {{ number_format($invoice->balance(),2) }})</label>
                                                <div class="input-group">
                                                    <input type="text" inputmode="numeric" name="amount_paid" class="form-control currency-input payment-amount-input" placeholder="0.00" required>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">Payment Date</label>
                                                <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">Method</label>
                                                <select name="method" class="form-select payment-method-select">
                                                    <option value="cash">Cash</option>
                                                    <option value="mpesa">M-Pesa</option>
                                                    <option value="bank">Bank Transfer</option>
                                                    <option value="card">Card</option>
                                                </select>
                                            </div>

                                            {{-- Only shown for Bank Transfer --}}
                                            <div class="mb-2 payment-method-fields d-none" data-method-fields="bank">
                                                <label class="form-label small">Bank</label>
                                                <select name="bank_name" class="form-select">
                                                    <option value="">Select bank</option>
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                                    @endforeach
                                                </select>
                                                @if($banks->isEmpty())
                                                    <div class="form-text">No banks added yet — add one under Finance &gt; Banks.</div>
                                                @endif
                                            </div>

                                            {{-- Shown for Bank Transfer, M-Pesa, and Card — label changes per method --}}
                                            <div class="mb-2 payment-method-fields d-none" data-method-fields="bank,mpesa,card">
                                                <label class="form-label small payment-reference-label">Reference Number</label>
                                                <input type="text" name="reference" class="form-control" placeholder="Transaction / cheque / reference code">
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

<!-- Generate Invoice Modal: Single or Bulk -->
<div class="modal fade" id="generateInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin"></i> Generate Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#singlePane" type="button">Single Student</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#bulkPane" type="button">Bulk (Whole Class / Category / School)</button></li>
                </ul>

                <div class="tab-content">
                    {{-- SINGLE --}}
                    <div class="tab-pane fade show active" id="singlePane">
                        <form method="POST" action="{{ route('admin.invoices.store') }}">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Filter by Grade (optional)</label>
                                    <select id="singleGradeFilter" class="form-select">
                                        <option value="">All grades</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Search Student</label>
                                    <input type="text" id="singleStudentSearch" class="form-control" placeholder="Type a name or admission no...">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small">Student</label>
                                    <select name="student_id" id="singleStudentSelect" class="form-select" size="6" required>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" data-class-id="{{ $student->school_class_id }}" data-search="{{ strtolower($student->user->name.' '.$student->admission_no) }}">
                                                {{ $student->user->name }} ({{ $student->admission_no }}) — {{ $student->schoolClass->name ?? 'No class' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Fee Type</label>
                                    <select name="fee_type_id" id="feeTypeSelect" class="form-select" required>
                                        <option value="">Select fee type</option>
                                        @foreach($feeTypes as $feeType)
                                            <option value="{{ $feeType->id }}" data-amount="{{ $feeType->amount }}">
                                                {{ $feeType->name }}{{ $feeType->feeCategory ? ' — '.$feeType->feeCategory->name : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KSh</span>
                                        <input type="text" inputmode="numeric" name="amount" id="amountInput" class="form-control currency-input" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Due Date</label>
                                    <input type="date" name="due_date" class="form-control">
                                </div>
                            </div>
                            <button class="btn btn-dark w-100 mt-3">Generate Invoice</button>
                        </form>
                    </div>

                    {{-- BULK --}}
                    <div class="tab-pane fade" id="bulkPane">
                        <form method="POST" action="{{ route('admin.invoices.bulk_store') }}">
                            @csrf
                            <p class="text-muted small">Students who already have this exact invoice are skipped automatically, so it's safe to re-run.</p>
                            <div class="mb-2">
                                <label class="form-label small">Fee Type</label>
                                <select name="fee_type_id" class="form-select" required>
                                    <option value="">Select fee type</option>
                                    @foreach($feeTypes as $feeType)
                                        <option value="{{ $feeType->id }}">
                                            {{ $feeType->name }}{{ $feeType->feeCategory ? ' — '.$feeType->feeCategory->name : '' }} — KES {{ number_format($feeType->amount, 2) }} ({{ $feeType->frequency }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Apply to</label>
                                <select name="scope" id="bulkScope" class="form-select" required>
                                    <option value="all">All students in the school</option>
                                    <option value="category">A fee category (e.g. Lower Primary)</option>
                                    <option value="class">A specific class only</option>
                                </select>
                            </div>
                            <div class="mb-2" id="bulkCategoryWrap" style="display:none;">
                                <label class="form-label small">Category</label>
                                <select name="fee_category_id" class="form-select">
                                    <option value="">Select category</option>
                                    @foreach($feeCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @if($feeCategories->isEmpty())
                                    <div class="form-text">No fee categories set up yet — add one under Finance &gt; Fee Categories.</div>
                                @endif
                            </div>
                            <div class="mb-2" id="bulkClassWrap" style="display:none;">
                                <label class="form-label small">Class</label>
                                <select name="school_class_id" class="form-select">
                                    <option value="">Select class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                            <button class="btn btn-dark w-100 mt-2">Generate Invoices</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('feeTypeSelect').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const amount = opt.dataset.amount || '';
    const amountInput = document.getElementById('amountInput');
    if (amount) {
        amountInput.value = 'KSh ' + parseFloat(amount).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        amountInput.dataset.raw = parseFloat(amount).toFixed(2);
    } else {
        amountInput.value = '';
        amountInput.dataset.raw = '';
    }
});

document.getElementById('bulkScope').addEventListener('change', function () {
    document.getElementById('bulkCategoryWrap').style.display = this.value === 'category' ? 'block' : 'none';
    document.getElementById('bulkClassWrap').style.display = this.value === 'class' ? 'block' : 'none';
});

// Single-invoice student picker: grade filter + text search, both narrowing
// the same <select> so you can either browse by grade or just type a name
// — no page reload, everything's already loaded in the option list.
function filterSingleStudentList() {
    const gradeId = document.getElementById('singleGradeFilter').value;
    const search = document.getElementById('singleStudentSearch').value.trim().toLowerCase();
    const options = document.getElementById('singleStudentSelect').options;

    for (const opt of options) {
        const matchesGrade = !gradeId || opt.dataset.classId === gradeId;
        const matchesSearch = !search || opt.dataset.search.includes(search);
        opt.hidden = !(matchesGrade && matchesSearch);
    }
}
document.getElementById('singleGradeFilter').addEventListener('change', filterSingleStudentList);
document.getElementById('singleStudentSearch').addEventListener('keyup', filterSingleStudentList);

// Record Payment modal: there's one of these per invoice row, so this uses
// delegated listeners scoped to the closest form rather than hardcoded IDs
// — works correctly no matter how many invoices are on the page.
const REFERENCE_LABELS = {
    bank: 'Bank Reference / Cheque No.',
    mpesa: 'M-Pesa Transaction Code',
    card: 'Card Reference / Last 4 Digits',
};

document.addEventListener('change', function (e) {
    if (!e.target.matches('.payment-method-select')) return;

    const form = e.target.closest('form');
    const method = e.target.value;

    form.querySelectorAll('.payment-method-fields').forEach(function (el) {
        const methods = el.dataset.methodFields.split(',');
        const show = methods.includes(method);
        el.classList.toggle('d-none', !show);
        el.querySelectorAll('input, select').forEach(function (input) {
            input.required = show && el.dataset.methodFields === 'bank'; // only Bank is strictly required
        });
    });

    const label = form.querySelector('.payment-reference-label');
    if (label) {
        label.textContent = REFERENCE_LABELS[method] || 'Reference Number';
    }
});
</script>
@endsection