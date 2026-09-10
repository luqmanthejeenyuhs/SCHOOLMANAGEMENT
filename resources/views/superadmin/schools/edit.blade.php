@extends('layouts.app')
@section('title', 'Edit School')
@section('content')
<h3 class="mb-3">Edit School</h3>

<form method="POST" action="{{ route('superadmin.schools.update', $school) }}">
    @csrf
    @method('PUT')

    <div class="card p-4" style="max-width:700px;">
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <div class="mb-3">
            <label class="form-label">School Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $school->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">URL Slug</label>
            <div class="input-group">
                <span class="input-group-text">/school/</span>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $school->slug) }}" pattern="[a-z0-9\-]+" required>
                <span class="input-group-text">/login</span>
            </div>
            <div class="form-text text-warning">Changing this changes the school's login link — let them know before you save.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Plan</label>
            <select name="plan" class="form-select" required>
                <option value="trial" @selected(old('plan', $school->plan) === 'trial')>Trial</option>
                <option value="basic" @selected(old('plan', $school->plan) === 'basic')>Basic</option>
                <option value="premium" @selected(old('plan', $school->plan) === 'premium')>Premium</option>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">School Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $school->email) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">School Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $school->address) }}">
        </div>
        <button class="btn btn-dark">Save Changes</button>
        <a href="{{ route('superadmin.schools.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
