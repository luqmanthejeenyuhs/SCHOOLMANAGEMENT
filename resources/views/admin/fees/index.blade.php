@extends('layouts.app')
@section('title', 'Fee Types')
@section('content')
<h3 class="mb-3">Fee Types</h3>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Fee Type</h6>
            <form method="POST" action="{{ route('admin.fee_types.store') }}">
                @csrf
                <div class="mb-2">
                    <input type="text" name="name" class="form-control" placeholder="e.g. Tuition Fee" required>
                </div>
                <div class="mb-2">
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount (KES)" required>
                </div>
                <div class="mb-2">
                    <select name="frequency" class="form-select" required>
                        <option value="term">Per Term</option>
                        <option value="month">Monthly</option>
                        <option value="year">Annual</option>
                        <option value="one_time">One-time</option>
                    </select>
                </div>
                <button class="btn btn-dark w-100">Add Fee Type</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Name</th><th>Amount</th><th>Frequency</th><th></th></tr></thead>
                <tbody>
                @forelse($feeTypes as $feeType)
                    <tr>
                        <td>{{ $feeType->name }}</td>
                        <td>KES {{ number_format($feeType->amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_',' ', $feeType->frequency)) }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.fee_types.destroy', $feeType) }}" method="POST" onsubmit="return confirm('Delete this fee type?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No fee types yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
