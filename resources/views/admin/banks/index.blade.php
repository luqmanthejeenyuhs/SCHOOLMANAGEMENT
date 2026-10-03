@extends('layouts.app')
@section('title', 'Banks')
@section('content')
<h3 class="mb-1">Banks</h3>
<p class="text-muted small">Banks added here appear in the dropdown when recording a Bank Transfer payment, so entries stay consistent (no "KCB" vs "K.C.B" vs "kcb bank" scattered across records).</p>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>Add Bank</h6>
            <form method="POST" action="{{ route('admin.banks.store') }}">
                @csrf
                <div class="mb-2">
                    <input type="text" name="name" class="form-control" placeholder="e.g. KCB Bank" required>
                </div>
                <button class="btn btn-dark w-100">Add Bank</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Bank Name</th><th></th></tr></thead>
                <tbody>
                @forelse($banks as $bank)
                    <tr>
                        <td>{{ $bank->name }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.banks.destroy', ['bank' => $bank]) }}" method="POST" onsubmit="return confirm('Remove this bank? Existing payment records already saved with this name are unaffected.');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted py-3">No banks added yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
