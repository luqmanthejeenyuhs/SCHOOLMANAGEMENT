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

        <div class="mb-3">
            <label class="form-label">Interest Calculation Method</label>
            <select name="interest_method" id="interest_method" class="form-select" required>
                <option value="flat">Flat Rate — interest charged once on the full term, fixed equal payments</option>
                <option value="simple">Simple Interest — an annual rate, pro-rated for the loan's actual length</option>
                <option value="compound">Compound Interest — a monthly rate, interest builds on the outstanding balance</option>
            </select>
            <div class="form-text" id="methodHint">Interest is calculated once for the whole term and added to the principal immediately.</div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Principal</label>
                <input type="text" inputmode="numeric" name="principal" id="principal" class="form-control currency-input" placeholder="KSh 0.00" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" id="rateLabel">Interest Rate (% flat, whole term)</label>
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
            <div class="d-flex justify-content-between"><span>Total Interest</span><span id="dInterest">KSh 0.00</span></div>
            <div class="d-flex justify-content-between"><span>Total Repayable</span><span id="dTotal">KSh 0.00</span></div>
            <div class="d-flex justify-content-between fw-bold border-top pt-1 mt-1"><span>Monthly Installment</span><span id="dInstallment">KSh 0.00</span></div>
        </div>
        <p class="text-muted small mt-2 mb-0">The monthly installment is deducted automatically from this employee's payslip each month once generated, until the loan is fully repaid.</p>
    </div>
    <div class="mt-3">
        <button class="btn btn-dark">Disburse Loan</button>
        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<script>
    const methodHints = {
        flat: {
            hint: 'Interest is calculated once for the whole term and added to the principal immediately.',
            rateLabel: 'Interest Rate (% flat, whole term)',
        },
        simple: {
            hint: 'Interest rate is treated as an ANNUAL rate and pro-rated for how long this loan actually runs.',
            rateLabel: 'Interest Rate (% per year)',
        },
        compound: {
            hint: 'Interest rate is treated as a MONTHLY rate. The installment is fixed, but interest is calculated on the outstanding balance each month — unpaid balances grow faster.',
            rateLabel: 'Interest Rate (% per month)',
        },
    };

    function updateMethodUI() {
        const method = document.getElementById('interest_method').value;
        document.getElementById('methodHint').textContent = methodHints[method].hint;
        document.getElementById('rateLabel').textContent = methodHints[method].rateLabel;
        recalc();
    }

    function currency(n) {
        return 'KSh ' + n.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalc() {
        const principal = parseFloat(document.getElementById('principal').dataset.raw || '0') || 0;
        const rate = parseFloat(document.getElementById('interest_rate').value) || 0;
        const months = parseInt(document.getElementById('repayment_period_months').value) || 1;
        const method = document.getElementById('interest_method').value;

        let interest, total, installment;

        if (method === 'simple') {
            const years = months / 12;
            interest = principal * (rate / 100) * years;
            total = principal + interest;
            installment = months > 0 ? total / months : total;
        } else if (method === 'compound') {
            const r = rate / 100;
            if (r <= 0) {
                installment = months > 0 ? principal / months : principal;
                total = installment * Math.max(months, 1);
            } else {
                const factor = Math.pow(1 + r, months);
                installment = principal * r * factor / (factor - 1);
                total = installment * months;
            }
            interest = total - principal;
        } else {
            interest = principal * (rate / 100);
            total = principal + interest;
            installment = months > 0 ? total / months : total;
        }

        document.getElementById('dInterest').textContent = currency(interest);
        document.getElementById('dTotal').textContent = currency(total);
        document.getElementById('dInstallment').textContent = currency(installment);
    }

    document.getElementById('interest_method').addEventListener('change', updateMethodUI);
    ['interest_rate', 'repayment_period_months'].forEach(id => {
        document.getElementById(id).addEventListener('input', recalc);
    });
    // Listens for 'currency:change' (dispatched by the site-wide money mask
    // in layouts/app.blade.php) rather than the native 'input' event —
    // .currency-input's formatted value/dataset.raw are only finalized by
    // that mask's own document-level 'input' handler, which (due to normal
    // DOM bubble-phase ordering) always runs AFTER any listener attached
    // directly to this element. Listening for 'input' here would read a
    // stale, one-keystroke-behind dataset.raw.
    document.getElementById('principal').addEventListener('currency:change', recalc);
    updateMethodUI();
</script>
@endsection
