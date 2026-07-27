@extends('layouts.app')
@section('title', 'Edit Teacher')
@section('content')
<div class="mb-3">
    <a href="{{ route('admin.teachers.show', $teacher) }}" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Back to Profile</a>
    <h3 class="mb-0 mt-1">Edit Teacher</h3>
</div>

<div class="card p-4" style="max-width:900px;">
    <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <h6 class="text-uppercase text-muted small mb-3">Personal Details</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $teacher->user->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $teacher->user->phone) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $teacher->user->email) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $teacher->address) }}">
            </div>
        </div>

        <h6 class="text-uppercase text-muted small mb-3 mt-4">Employment &amp; Identity</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Employee / Staff Number</label>
                <input type="text" class="form-control" value="{{ $teacher->employee_id }}" disabled>
                <div class="form-text">Auto-assigned, can't be changed.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">National ID / Passport Number</label>
                <input type="text" name="id_number" class="form-control" value="{{ old('id_number', $teacher->id_number) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">TSC Number</label>
                <input type="text" name="tsc_number" class="form-control" value="{{ old('tsc_number', $teacher->tsc_number) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Qualification</label>
                <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $teacher->qualification) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Joining Date</label>
                <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $teacher->joining_date?->format('Y-m-d')) }}">
            </div>
        </div>

        <h6 class="text-uppercase text-muted small mb-3 mt-4">Next of Kin</h6>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Full Name</label>
                <input type="text" name="next_of_kin_name" class="form-control" value="{{ old('next_of_kin_name', $teacher->next_of_kin_name) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="next_of_kin_phone" class="form-control" value="{{ old('next_of_kin_phone', $teacher->next_of_kin_phone) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Relationship</label>
                <input type="text" name="next_of_kin_relationship" class="form-control" value="{{ old('next_of_kin_relationship', $teacher->next_of_kin_relationship) }}">
            </div>
        </div>

        <h6 class="text-uppercase text-muted small mb-3 mt-4">Documents</h6>

        @if($teacher->documents->isNotEmpty())
        <div class="mb-3">
            <div class="text-muted small mb-2">Already uploaded:</div>
            <ul class="list-group">
                @foreach($teacher->documents as $document)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-earmark-text"></i> {{ $document->label() }} — <span class="text-muted small">{{ $document->original_name }}</span></span>
                    <span>
                        <a href="{{ route('admin.teachers.documents.download', [$teacher, $document]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                        <form action="{{ route('admin.teachers.documents.destroy', [$teacher, $document]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this document?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <p class="text-muted small">Uploading a new Passport Photo / National ID / Police Clearance below replaces the existing one of that type. "Other Documents" are added alongside what's already there.</p>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Passport Photo</label>
                <input type="file" name="passport_photo" class="form-control" accept="image/*">
            </div>
            <div class="col-md-4">
                <label class="form-label">National ID / Passport Copy</label>
                <input type="file" name="national_id_document" class="form-control" accept=".pdf,image/*">
            </div>
            <div class="col-md-4">
                <label class="form-label">Police Clearance Certificate</label>
                <input type="file" name="police_clearance" class="form-control" accept=".pdf,image/*">
            </div>
            <div class="col-12">
                <label class="form-label">Other Documents</label>
                <input type="file" name="other_documents[]" class="form-control" accept=".pdf,image/*" multiple>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-dark">Update Teacher</button>
            <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
