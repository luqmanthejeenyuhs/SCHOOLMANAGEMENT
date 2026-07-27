@extends('layouts.app')
@section('title', 'Teacher Profile')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('admin.teachers.index') }}" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Back to Teachers</a>
        <h3 class="mb-0 mt-1">{{ $teacher->user->name }}</h3>
        <span class="text-muted">Employee ID: <strong>{{ $teacher->employee_id }}</strong></span>
    </div>
    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
</div>

<ul class="nav nav-tabs mb-3" id="teacherTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
            <i class="bi bi-person-vcard"></i> Overview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button" role="tab">
            <i class="bi bi-easel"></i> Classes &amp; Subjects
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">
            <i class="bi bi-calendar-check"></i> Attendance
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll" type="button" role="tab">
            <i class="bi bi-cash-coin"></i> Payroll
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance" type="button" role="tab">
            <i class="bi bi-graph-up"></i> Performance
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
            <i class="bi bi-file-earmark-text"></i> Documents
        </button>
    </li>
</ul>

<div class="tab-content" id="teacherTabsContent">

    {{-- OVERVIEW --}}
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
        <div class="card p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="text-muted small">Full Name</div>
                    <div class="fw-semibold">{{ $teacher->user->name }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Employee / Staff Number</div>
                    <div class="fw-semibold">{{ $teacher->employee_id }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Email</div>
                    <div class="fw-semibold">{{ $teacher->user->email }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Phone</div>
                    <div class="fw-semibold">{{ $teacher->user->phone ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Qualification</div>
                    <div class="fw-semibold">{{ $teacher->qualification ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Joined</div>
                    <div class="fw-semibold">{{ $teacher->joining_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">National ID / Passport Number</div>
                    <div class="fw-semibold">{{ $teacher->id_number ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">TSC Number</div>
                    <div class="fw-semibold">{{ $teacher->tsc_number ?? '—' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">Address</div>
                    <div class="fw-semibold">{{ $teacher->address ?? '—' }}</div>
                </div>
                <div class="col-12"><hr></div>
                <div class="col-12 text-muted small text-uppercase">Next of Kin</div>
                <div class="col-md-5">
                    <div class="text-muted small">Full Name</div>
                    <div class="fw-semibold">{{ $teacher->next_of_kin_name ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Phone</div>
                    <div class="fw-semibold">{{ $teacher->next_of_kin_phone ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Relationship</div>
                    <div class="fw-semibold">{{ $teacher->next_of_kin_relationship ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- DOCUMENTS --}}
    <div class="tab-pane fade" id="documents" role="tabpanel">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Document</th><th>File</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($teacher->documents as $document)
                        <tr>
                            <td>{{ $document->label() }}</td>
                            <td class="text-muted small">{{ $document->original_name }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.teachers.documents.download', [$teacher, $document]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i> Download</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No documents uploaded yet. <a href="{{ route('admin.teachers.edit', $teacher) }}">Add some</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CLASSES & SUBJECTS --}}
    <div class="tab-pane fade" id="classes" role="tabpanel">
        <div class="card mb-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Subject</th><th>Class</th><th>Section</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($teacher->assignments as $a)
                        <tr>
                            <td>{{ $a->subject->name ?? '—' }}</td>
                            <td>{{ $a->schoolClass->name ?? '—' }}</td>
                            <td>{{ $a->section->name ?? 'All sections' }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.teachers.assignments.destroy', [$teacher, $a]) }}" method="POST" onsubmit="return confirm('Remove this assignment?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No classes or subjects assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-3">
            <h6 class="text-uppercase text-muted small mb-3">Assign a Subject</h6>
            <form method="POST" action="{{ route('admin.teachers.assignments.store', $teacher) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small">Class</label>
                    <select name="school_class_id" id="assignClass" class="form-select" required>
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Subject</label>
                    <select name="subject_id" id="assignSubject" class="form-select" required>
                        <option value="">Select class first</option>
                        @foreach($subjectsAll as $subject)
                            <option value="{{ $subject->id }}" data-class="{{ $subject->school_class_id }}" class="d-none">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Section (optional)</label>
                    <select name="section_id" id="assignSection" class="form-select">
                        <option value="">All sections</option>
                        @foreach($sectionsAll as $section)
                            <option value="{{ $section->id }}" data-class="{{ $section->school_class_id }}" class="d-none">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-dark w-100"><i class="bi bi-plus-lg"></i></button>
                </div>
            </form>
        </div>

        <script>
            (function () {
                const classSelect = document.getElementById('assignClass');
                const subjectSelect = document.getElementById('assignSubject');
                const sectionSelect = document.getElementById('assignSection');

                function filterByClass(select) {
                    const classId = classSelect.value;
                    Array.from(select.options).forEach(opt => {
                        if (!opt.dataset.class) return; // keep the placeholder option
                        opt.classList.toggle('d-none', opt.dataset.class !== classId);
                    });
                    select.value = '';
                }

                classSelect.addEventListener('change', function () {
                    subjectSelect.options[0].textContent = classSelect.value ? 'Select subject' : 'Select class first';
                    filterByClass(subjectSelect);
                    filterByClass(sectionSelect);
                });
            })();
        </script>
    </div>

    {{-- ATTENDANCE --}}
    <div class="tab-pane fade" id="attendance" role="tabpanel">
        <div class="card">
            <div class="card-header">Attendance records marked by this teacher (latest 15)</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Student</th><th>Status</th><th>Remarks</th></tr>
                    </thead>
                    <tbody>
                        @forelse($attendanceMarked as $rec)
                        <tr>
                            <td>{{ $rec->date?->format('d M Y') }}</td>
                            <td>{{ $rec->student->user->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $rec->status === 'present' ? 'bg-success' : ($rec->status === 'late' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ ucfirst($rec->status) }}
                                </span>
                            </td>
                            <td>{{ $rec->remarks ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No attendance records marked by this teacher yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- PAYROLL --}}
    <div class="tab-pane fade" id="payroll" role="tabpanel">
        @if($employee)
        <div class="card p-4 mb-3">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-muted small">Basic Salary</div>
                    <div class="fw-semibold">{{ number_format($employee->basic_salary, 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">House Allowance</div>
                    <div class="fw-semibold">{{ number_format($employee->house_allowance, 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Transport Allowance</div>
                    <div class="fw-semibold">{{ number_format($employee->transport_allowance, 2) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Gross Pay</div>
                    <div class="fw-semibold">{{ number_format($employee->grossPay(), 2) }}</div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Recent Payslips</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Period</th><th>Gross Pay</th><th>Deductions</th><th>Net Pay</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($employee->payslips as $slip)
                        <tr>
                            <td>{{ $slip->periodLabel() }}</td>
                            <td>{{ number_format($slip->gross_pay, 2) }}</td>
                            <td>{{ number_format($slip->total_deductions, 2) }}</td>
                            <td class="fw-semibold">{{ number_format($slip->net_pay, 2) }}</td>
                            <td class="text-end"><a href="{{ route('admin.payslips.show', $slip) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No payslips generated yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card p-4 text-center text-muted">
            <i class="bi bi-cash-coin fs-2 mb-2"></i>
            <p class="mb-1">This teacher isn't linked to a payroll record yet.</p>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-primary mt-2">Add to Payroll</a>
        </div>
        @endif
    </div>

    {{-- PERFORMANCE --}}
    <div class="tab-pane fade" id="performance" role="tabpanel">
        <div class="card mb-3">
            <div class="card-header">Average performance by subject taught</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Subject</th><th>Results Recorded</th><th>Average Score</th></tr>
                    </thead>
                    <tbody>
                        @forelse($performanceBySubject as $row)
                        <tr>
                            <td>{{ $row->subject->name ?? '—' }}</td>
                            <td>{{ $row->total }}</td>
                            <td class="fw-semibold">{{ number_format($row->avg_pct, 1) }}%</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No exam results recorded for this teacher's subjects yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Recent exam results in this teacher's subjects</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Exam</th><th>Subject</th><th>Student</th><th>Marks</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentResults as $res)
                        <tr>
                            <td>{{ $res->exam->name ?? '—' }}</td>
                            <td>{{ $res->subject->name ?? '—' }}</td>
                            <td>{{ $res->student->user->name ?? '—' }}</td>
                            <td>{{ $res->marks_obtained }}/{{ $res->max_marks }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No results recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
