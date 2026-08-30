@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
<h3 class="mb-3">Edit Supplier</h3>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}">
    @csrf
    @method('PUT')
    <div class="card p-4" style="max-width:700px;">
        <div class="mb-3">
            <label class="form-label">Supplier Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">KRA PIN</label>
                <input type="text" name="kra_pin" class="form-control" value="{{ old('kra_pin', $supplier->kra_pin) }}">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Payment Terms (days)</label>
            <input type="number" name="payment_terms_days" class="form-control" value="{{ old('payment_terms_days', $supplier->payment_terms_days) }}" style="max-width:150px;" required>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $supplier->is_active))>
            <label class="form-check-label" for="isActive">Active</label>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-dark">Save Changes</button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
