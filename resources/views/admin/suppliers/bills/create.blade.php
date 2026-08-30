@extends('layouts.app')
@section('title', 'New Supplier Bill')
@section('content')
<h3 class="mb-3">New Supplier Bill</h3>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.suppliers.bills.store') }}">
    @csrf
    <div class="card p-4" style="max-width:700px;">
        <div class="mb-3">
            <label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">Select supplier</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" placeholder="e.g. Stationery supplies for Term 2" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Bill Reference (optional)</label>
            <input type="text" name="bill_reference" class="form-control" placeholder="Supplier's invoice number">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Amount (excl. VAT)</label>
                <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Bill Date</label>
                <input type="date" name="bill_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_vatable" value="1" id="isVatable" onchange="updateTotal()">
            <label class="form-check-label" for="isVatable">This bill includes VAT ({{ $vatRate }}%)</label>
        </div>
        <div class="bg-light rounded p-3">
            <div class="d-flex justify-content-between"><span>Amount</span><span id="displayAmount">KES 0.00</span></div>
            <div class="d-flex justify-content-between"><span>VAT</span><span id="displayVat">KES 0.00</span></div>
            <div class="d-flex justify-content-between fw-bold border-top pt-1 mt-1"><span>Total Payable</span><span id="displayTotal">KES 0.00</span></div>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-dark">Save Bill</button>
        <a href="{{ route('admin.suppliers.bills.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<script>
    const vatRate = {{ $vatRate }};
    function updateTotal() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const vatable = document.getElementById('isVatable').checked;
        const vat = vatable ? amount * (vatRate / 100) : 0;
        document.getElementById('displayAmount').textContent = 'KES ' + amount.toFixed(2);
        document.getElementById('displayVat').textContent = 'KES ' + vat.toFixed(2);
        document.getElementById('displayTotal').textContent = 'KES ' + (amount + vat).toFixed(2);
    }
    document.getElementById('amount').addEventListener('input', updateTotal);
</script>
@endsection
