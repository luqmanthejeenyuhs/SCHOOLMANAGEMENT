@extends('layouts.app')
@section('title', 'Fee Categories')
@section('content')
<h3 class="mb-1">Fee Categories</h3>
<p class="text-muted small">Group classes that share a fee schedule — e.g. Pre-Primary (PP1-PP2), Lower Primary (Grade 1-3), Upper Primary (Grade 4-6), Junior Secondary (Grade 7-9). Once set up, you can generate invoices for a whole category at once, and give each category its own fee amounts under Fees.</p>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Fee Category</h6>
            <form method="POST" action="{{ route('admin.fee_categories.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small">Category Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Lower Primary" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Classes in this category</label>
                    <div class="border rounded p-2" style="max-height:220px;overflow-y:auto;">
                        @forelse($classes as $class)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="school_class_ids[]" value="{{ $class->id }}" id="newCat_class{{ $class->id }}">
                                <label class="form-check-label small" for="newCat_class{{ $class->id }}">
                                    {{ $class->name }}
                                    @if($class->feeCategory)
                                        <span class="text-muted">(currently: {{ $class->feeCategory->name }})</span>
                                    @endif
                                </label>
                            </div>
                        @empty
                            <span class="text-muted small">No classes set up yet.</span>
                        @endforelse
                    </div>
                    <div class="form-text">Checking a class here moves it out of whichever category it's currently in.</div>
                </div>
                <button class="btn btn-dark w-100">Add Category</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Category</th><th>Classes</th><th>Fee Types</th><th></th></tr></thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td class="fw-semibold">{{ $category->name }}</td>
                        <td>
                            @forelse($category->schoolClasses as $class)
                                <span class="badge bg-light text-dark border me-1 mb-1">{{ $class->name }}</span>
                            @empty
                                <span class="text-muted small">No classes yet</span>
                            @endforelse
                        </td>
                        <td>{{ $category->fee_types_count }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCat{{ $category->id }}"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('admin.fee_categories.destroy', ['feeCategory' => $category]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category? Its classes and fee types will become uncategorized, not deleted.');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editCat{{ $category->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.fee_categories.update', ['feeCategory' => $category]) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header"><h6 class="modal-title">Edit {{ $category->name }}</h6></div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label small">Category Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Classes in this category</label>
                                            <div class="border rounded p-2" style="max-height:220px;overflow-y:auto;">
                                                @foreach($classes as $class)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="school_class_ids[]" value="{{ $class->id }}" id="editCat{{ $category->id }}_class{{ $class->id }}" @checked($class->fee_category_id === $category->id)>
                                                        <label class="form-check-label small" for="editCat{{ $category->id }}_class{{ $class->id }}">{{ $class->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button class="btn btn-dark">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No fee categories yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
