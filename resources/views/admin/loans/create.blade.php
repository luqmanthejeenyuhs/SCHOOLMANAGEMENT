@extends('layouts.app')
@section('title', 'New Loan / Advance')
@section('content')
<h3 class="mb-3">New Staff Loan / Advance</h3>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.loans.store') }}">
    @csrf
    <div class="card p-4" style="max-width:700px;">
        <div class="mb-3">
            <label class="form-label">Employee</label>
            <select name="employee_id" class="form-select" required>
                <option value="">Select employee</option>
                @foreach($employees as $e)
                    <option value="{{ $e->id }}">{{ $e->name }} — {{ $e->job_title }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="loan_type" class="form-select" required>
                <option value="advance">Salary Advance (short-term, typically no interest)</option>
                <option value="loan">Loan (longer-term)</option>
            </select>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Principal (KES)</label>
                <input type="number" step="0.01" min="1" name="principal" id="principal" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Interest Rate (% flat, whole term)</label>
                <input type="number" step="0.01" min="0" max="100" name="interest_rate" id="interest_rate" class="form-control" value="0" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Repayment Period (months)</label>
                <input type="number" min="1" max="60" name="repayment_period_months" id="repayment_period_months" class="form-control" value="1" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Disbursement Method</label>
                <select name="disbursement_method" class="form-select">
                    <option value="bank">Bank Transfer</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
        </div>
        <div class="bg-light rounded p-3">
            <div class="d-flex justify-content-between"><span>Total Interest</span><span id="dInterest">KES 0.00</span></div>
            <div class="d-flex justify-content-between"><span>Total Repayable</span><span id="dTotal">KES 0.00</span></div>
            <div class="d-flex justify-content-between fw-bold border-top pt-1 mt-1"><span>Monthly Installment</span><span id="dInstallment">KES 0.00</span></div>
        </div>
        <p class="text-muted small mt-2 mb-0">The monthly installment is deducted automatically from this employee's payslip each month once generated, until the loan is fully repaid.</p>
    </div>
    <div class="mt-3">
        <button class="btn btn-dark">Disburse Loan</button>
        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<script>
    function recalc() {
        const principal = parseFloat(document.getElementById('principal').value) || 0;
        const rate = parseFloat(document.getElementById('interest_rate').value) || 0;
        const months = parseInt(document.getElementById('repayment_period_months').value) || 1;
        const interest = principal * (rate / 100);
        const total = principal + interest;
        const installment = months > 0 ? total / months : total;
        document.getElementById('dInterest').textContent = 'KES ' + interest.toFixed(2);
        document.getElementById('dTotal').textContent = 'KES ' + total.toFixed(2);
        document.getElementById('dInstallment').textContent = 'KES ' + installment.toFixed(2);
    }
    ['principal', 'interest_rate', 'repayment_period_months'].forEach(id => {
        document.getElementById(id).addEventListener('input', recalc);
    });
</script>
@endsection
