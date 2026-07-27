@extends('layouts.app')
@section('title', 'Edit Student')
@section('content')
<h3 class="mb-3">Edit Student</h3>
<div class="card p-4" style="max-width:800px;">
    <form method="POST" action="{{ route('admin.students.update', $student) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $student->user->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $student->user->email) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Admission No</label>
                <input type="text" name="admission_no" class="form-control" value="{{ old('admission_no', $student->admission_no) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select name="school_class_id" id="classSelect" class="form-select" required>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('school_class_id', $student->school_class_id) == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Section</label>
                <select name="section_id" id="sectionSelect" class="form-select"></select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control" value="{{ old('dob', $student->dob?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Guardian Name</label>
                <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Guardian Phone</label>
                <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone', $student->guardian_phone) }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $student->address) }}">
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-dark">Update Student</button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
const classesData = @json($classes->keyBy('id'));
const currentSectionId = {{ $student->section_id ?? 'null' }};
const classSelect = document.getElementById('classSelect');
const sectionSelect = document.getElementById('sectionSelect');

function populateSections() {
    const classId = classSelect.value;
    sectionSelect.innerHTML = '<option value="">Select section</option>';
    if (classId && classesData[classId]) {
        classesData[classId].sections.forEach(function (section) {
            const opt = document.createElement('option');
            opt.value = section.id;
            opt.textContent = section.name;
            if (currentSectionId && section.id === currentSectionId) opt.selected = true;
            sectionSelect.appendChild(opt);
        });
    }
}
classSelect.addEventListener('change', populateSections);
populateSections();
</script>
@endsection
