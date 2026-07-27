@extends('layouts.app')
@section('title', 'CBC Curriculum')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="mb-0">CBC Curriculum</h3>
        <p class="text-muted small mb-0">Competency Based Curriculum setup, assessment, and reporting — Ministry of Education / KICD structure.</p>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="cbcTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile" type="button"><i class="bi bi-person-badge"></i> Learner Profile</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-areas" type="button"><i class="bi bi-diagram-3"></i> Learning Areas &amp; Strands</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-competencies" type="button"><i class="bi bi-check2-square"></i> Core Competencies</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sba" type="button"><i class="bi bi-clipboard-data"></i> SBA &amp; Portfolio</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-values" type="button"><i class="bi bi-heart"></i> Values &amp; Behaviour</button>
    </li>
</ul>

<div class="tab-content">

    {{-- 1. LEARNER PROFILE --}}
    <div class="tab-pane fade show active" id="tab-profile">
        <p class="text-muted small">NEMIS Unique Personal Identifier (UPI) and, for senior school learners, their chosen pathway.</p>
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Admission No</th><th>Name</th><th>Class</th><th>UPI (NEMIS)</th><th>School Level</th><th>Pathway</th><th></th></tr></thead>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->admission_no }}</td>
                        <td>{{ $student->user->name }}</td>
                        <td>{{ $student->schoolClass->name ?? '—' }} {{ $student->section->name ?? '' }}</td>
                        <td>{{ $student->upi_number ?? '—' }}</td>
                        <td class="text-capitalize">{{ $student->school_level }}</td>
                        <td>{{ $student->pathway ?? '—' }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#profileModal{{ $student->id }}"><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>

                    <div class="modal fade" id="profileModal{{ $student->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.cbc.profile.update', $student) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $student->user->name }} — Learner Profile</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label small">UPI Number (NEMIS)</label>
                                            <input type="text" name="upi_number" class="form-control" value="{{ $student->upi_number }}" placeholder="e.g. 12345678AB">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">KNEC Assessment Number (KPSEA/KJSEA)</label>
                                            <input type="text" name="assessment_number" class="form-control" value="{{ $student->assessment_number }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">School Level</label>
                                            <select name="school_level" class="form-select" required>
                                                <option value="junior" @selected($student->school_level === 'junior')>Junior School</option>
                                                <option value="senior" @selected($student->school_level === 'senior')>Senior School</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small">Pathway (Senior School only)</label>
                                            <select name="pathway" class="form-select">
                                                <option value="">— Not applicable —</option>
                                                <option value="STEM" @selected($student->pathway === 'STEM')>STEM</option>
                                                <option value="Social Sciences" @selected($student->pathway === 'Social Sciences')>Social Sciences</option>
                                                <option value="Arts & Sports Science" @selected($student->pathway === 'Arts & Sports Science')>Arts &amp; Sports Science</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button class="btn btn-dark">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No students yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. LEARNING AREAS & STRANDS --}}
    <div class="tab-pane fade" id="tab-areas">
        <div class="d-flex justify-content-end gap-2 mb-3">
            <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addLearningAreaModal"><i class="bi bi-plus-lg"></i> Add Learning Area</button>
            <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addStrandModal"><i class="bi bi-plus-lg"></i> Add Strand</button>
            <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addSubStrandModal"><i class="bi bi-plus-lg"></i> Add Sub-strand</button>
        </div>

        <div class="accordion" id="learningAreasAccordion">
            @forelse($learningAreas as $la)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#la{{ $la->id }}">
                        {{ $la->name }}
                        <span class="badge bg-secondary ms-2">{{ ucfirst($la->school_level) }}</span>
                        @if($la->pathway)<span class="badge bg-info text-dark ms-1">{{ $la->pathway }}</span>@endif
                        <span class="text-muted small ms-2">{{ $la->strands->count() }} strand(s)</span>
                    </button>
                </h2>
                <div id="la{{ $la->id }}" class="accordion-collapse collapse" data-bs-parent="#learningAreasAccordion">
                    <div class="accordion-body">
                        <div class="text-end mb-2">
                            <form action="{{ route('admin.cbc.learning_areas.destroy', $la) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this learning area and all its strands?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete Learning Area</button>
                            </form>
                        </div>
                        @forelse($la->strands as $strand)
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="small">{{ $strand->name }}</strong>
                                    <form action="{{ route('admin.cbc.strands.destroy', $strand) }}" method="POST" onsubmit="return confirm('Delete this strand?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                </div>
                                <ul class="small text-muted mb-0 mt-1">
                                    @forelse($strand->subStrands as $sub)
                                        <li class="d-flex justify-content-between" style="max-width:320px;">
                                            {{ $sub->name }}
                                            <form action="{{ route('admin.cbc.sub_strands.destroy', $sub) }}" method="POST" onsubmit="return confirm('Delete this sub-strand?');" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-x-circle"></i></button>
                                            </form>
                                        </li>
                                    @empty
                                        <li>No sub-strands yet.</li>
                                    @endforelse
                                </ul>
                            </div>
                        @empty
                            <p class="text-muted small">No strands yet — add one using the button above.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
                <div class="alert alert-light border">No learning areas yet — click "Add Learning Area" above to get started.</div>
            @endforelse
        </div>
    </div>

    {{-- 3. CORE COMPETENCIES --}}
    <div class="tab-pane fade" id="tab-competencies">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted small mb-0">The 7 KICD core competencies, rated EE / ME / AE / BE per learner per term.</p>
            <a href="{{ route('admin.cbc.core_competencies.grid') }}" class="btn btn-dark btn-sm"><i class="bi bi-clipboard-check"></i> Record Competencies</a>
        </div>
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Student</th><th>Competency</th><th>Term</th><th>Level</th><th>Remarks</th></tr></thead>
                <tbody>
                @forelse($recentCompetencies as $record)
                    <tr>
                        <td>{{ $record->student->user->name }}</td>
                        <td>{{ $record->competencyLabel() }}</td>
                        <td>{{ $record->term }}</td>
                        <td><span class="badge bg-dark">{{ $record->performance_level }}</span></td>
                        <td class="text-muted small">{{ $record->remarks ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No competency ratings recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. SBA & PORTFOLIO --}}
    <div class="tab-pane fade" id="tab-sba">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">School-Based Assessments (Grades 4–6)</h6>
            <a href="{{ route('admin.cbc.sba.grid') }}" class="btn btn-dark btn-sm"><i class="bi bi-clipboard-data"></i> Record SBA Scores</a>
        </div>
        <p class="text-muted small">Each of the 3 SBA performance tasks contributes 20% (60% total) to the KPSEA exit profile, alongside the 40% summative exam.</p>
        <div class="card mb-4">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Student</th><th>Learning Area</th><th>Term</th><th>SBA</th><th>Score</th></tr></thead>
                <tbody>
                @forelse($recentSba as $record)
                    <tr>
                        <td>{{ $record->student->user->name }}</td>
                        <td>{{ $record->learningArea->name ?? '—' }}</td>
                        <td>{{ $record->term }}</td>
                        <td>SBA {{ $record->sba_number }}</td>
                        <td>{{ $record->score }}/{{ $record->max_score }} ({{ $record->percentage() }}%)</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No SBA scores recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Portfolio Evidence</h6>
            <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#uploadPortfolioModal"><i class="bi bi-cloud-upload"></i> Upload Evidence</button>
        </div>
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Student</th><th>Title</th><th>Type</th><th>Sub-strand</th><th>Term</th><th></th></tr></thead>
                <tbody>
                @forelse($portfolioItems as $item)
                    <tr>
                        <td>{{ $item->student->user->name }}</td>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->typeLabel() }}</td>
                        <td class="text-muted small">{{ $item->subStrand->name ?? '—' }}</td>
                        <td>{{ $item->term ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.cbc.portfolio.download', $item) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                            <form action="{{ route('admin.cbc.portfolio.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this portfolio item?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No portfolio evidence uploaded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 5. VALUES & BEHAVIOUR --}}
    <div class="tab-pane fade" id="tab-values">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted small mb-0">The 7 KICD national values, rated EE / ME / AE / BE per learner per term, with qualitative remarks.</p>
            <a href="{{ route('admin.cbc.values.grid') }}" class="btn btn-dark btn-sm"><i class="bi bi-heart"></i> Record Values</a>
        </div>
        <div class="card">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr><th>Student</th><th>Value</th><th>Term</th><th>Rating</th><th>Remarks</th></tr></thead>
                <tbody>
                @forelse($recentValues as $record)
                    <tr>
                        <td>{{ $record->student->user->name }}</td>
                        <td>{{ $record->valueLabel() }}</td>
                        <td>{{ $record->term }}</td>
                        <td><span class="badge bg-dark">{{ $record->rating }}</span></td>
                        <td class="text-muted small">{{ $record->remarks ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No values ratings recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Add Learning Area modal --}}
<div class="modal fade" id="addLearningAreaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.cbc.learning_areas.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Learning Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control" placeholder="e.g. Mathematics Activities" required>
                    </div>
                    <div class="mb-2">
                        <select name="school_level" class="form-select" required>
                            <option value="junior">Junior School</option>
                            <option value="senior">Senior School</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="pathway" class="form-control" placeholder="Pathway (Senior School only, e.g. STEM)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Strand modal --}}
<div class="modal fade" id="addStrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.cbc.strands.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Strand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <select name="cbc_learning_area_id" class="form-select" required>
                            <option value="">Select learning area</option>
                            @foreach($learningAreas as $la)
                                <option value="{{ $la->id }}">{{ $la->name }} ({{ ucfirst($la->school_level) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control" placeholder="e.g. Numbers" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Sub-strand modal --}}
<div class="modal fade" id="addSubStrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.cbc.sub_strands.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Sub-strand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <select name="cbc_strand_id" class="form-select" required>
                            <option value="">Select strand</option>
                            @foreach($learningAreas as $la)
                                @foreach($la->strands as $strand)
                                    <option value="{{ $strand->id }}">{{ $la->name }} › {{ $strand->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control" placeholder="e.g. Fractions" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Upload Portfolio Evidence modal --}}
<div class="modal fade" id="uploadPortfolioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.cbc.portfolio.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Portfolio Evidence</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->admission_no }} — {{ $student->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Volcano Model Project" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Evidence Type</label>
                        <select name="evidence_type" class="form-select" required>
                            @foreach(\App\Models\CbcPortfolioItem::TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Related Sub-strand (optional)</label>
                        <select name="cbc_sub_strand_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($learningAreas as $la)
                                @foreach($la->strands as $strand)
                                    @foreach($strand->subStrands as $sub)
                                        <option value="{{ $sub->id }}">{{ $la->name }} › {{ $strand->name }} › {{ $sub->name }}</option>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Term (optional)</label>
                        <input type="text" name="term" class="form-control" placeholder="e.g. Term 2 2026">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">File (PDF, image, audio, or video — max 20MB)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.mp3,.wav,.mp4,.mov" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-dark">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
