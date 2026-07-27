@extends('layouts.app')
@section('title', 'Inventory & Store')
@section('content')
<h3 class="mb-1">Inventory &amp; Store</h3>
<p class="text-muted small">Track stationery and consumables. Issuing an item deducts it from stock instantly, and can append a charge straight to the student's fee bill — no paperwork.</p>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Item</h6>
            <form method="POST" action="{{ route('admin.inventory.store') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-8">
                        <input type="text" name="name" class="form-control" placeholder="e.g. Exercise Book 120pg" required>
                    </div>
                    <div class="col-4">
                        <input type="text" name="code" class="form-control" placeholder="SKU (opt.)">
                    </div>
                    <div class="col-6">
                        <select name="category" class="form-select" required>
                            <option value="consumable">Consumable</option>
                            <option value="textbook">Textbook Title</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.01" name="unit_price" class="form-control" placeholder="Unit price (KES)" required>
                    </div>
                    <div class="col-6">
                        <input type="number" name="quantity_in_stock" class="form-control" placeholder="Opening stock" required>
                    </div>
                    <div class="col-6">
                        <input type="number" name="reorder_level" class="form-control" placeholder="Reorder level" value="5" required>
                    </div>
                    <div class="col-12">
                        <input type="text" name="description" class="form-control" placeholder="Description (optional)">
                    </div>
                </div>
                <button class="btn btn-dark w-100 mt-3">Add to Inventory</button>
            </form>
        </div>

        <div class="card p-3 mt-3">
            <h6>Issue Item to Student</h6>
            <p class="small text-muted mb-2">Look up by admission number, pick the item, and issue. Tick "bill to fee account" for direct mid-term purchases — it appends an instant charge to the student's pending bill.</p>
            <form method="POST" action="" id="issueForm" onsubmit="return submitIssueForm(event)">
                @csrf
                <div class="mb-2">
                    <input type="text" name="admission_no" form="issueForm" class="form-control" placeholder="Admission No (e.g. ADM-0001)" required>
                </div>
                <div class="mb-2">
                    <select name="item_id" id="issueItemSelect" class="form-select" required>
                        <option value="">Select item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->unit_price }}">{{ $item->name }} ({{ $item->quantity_in_stock }} in stock)</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number" name="quantity" class="form-control" placeholder="Qty" value="1" min="1" required>
                    </div>
                    <div class="col-6 d-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="bill_to_fee_account" value="1" id="billCheck">
                            <label class="form-check-label small" for="billCheck">Bill to fee account</label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-dark w-100 mt-3">Issue Item</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Item</th><th>Category</th><th>Unit Price</th><th>Stock</th><th></th></tr></thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr class="{{ $item->isLowStock() ? 'table-warning' : '' }}">
                            <td>{{ $item->name }} @if($item->code)<span class="text-muted small">({{ $item->code }})</span>@endif</td>
                            <td><span class="badge bg-secondary text-capitalize">{{ $item->category }}</span></td>
                            <td>KES {{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $item->quantity_in_stock }} @if($item->isLowStock())<i class="bi bi-exclamation-triangle-fill text-warning" title="Low stock"></i>@endif</td>
                            <td class="text-end">
                                <form action="{{ route('admin.inventory.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this item?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No inventory items yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-3 mt-3">
            <h6>Recent Issues</h6>
            <table class="table table-sm mb-0">
                <thead><tr><th>Item</th><th>Student</th><th>Qty</th><th>Billed?</th><th>When</th></tr></thead>
                <tbody>
                @forelse($recentIssues as $issue)
                    <tr>
                        <td>{{ $issue->item->name }}</td>
                        <td>{{ $issue->student->user->name ?? '—' }}</td>
                        <td>{{ $issue->quantity }}</td>
                        <td>{{ $issue->billed_to_fee_account ? 'Yes' : 'No' }}</td>
                        <td class="small text-muted">{{ $issue->issued_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No items issued yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function submitIssueForm(e) {
    e.preventDefault();
    const select = document.getElementById('issueItemSelect');
    const itemId = select.value;
    if (!itemId) return false;
    const form = document.getElementById('issueForm');
    form.action = "{{ url('admin/inventory') }}/" + itemId + "/issue";
    form.submit();
}
</script>
@endsection
