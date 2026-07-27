<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card — {{ $student->user->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #eee; }
        .sheet { max-width: 800px; margin: 24px auto; background: #fff; padding: 40px; border: 1px solid #ccc; }
        .school-header { text-align: center; border-bottom: 3px double #333; padding-bottom: 12px; margin-bottom: 20px; }
        .school-header h2 { margin-bottom: 0; letter-spacing: 1px; }
        table.results th, table.results td { border: 1px solid #999; padding: 6px 8px; }
        .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 4px; text-align: center; }
        .stamp-box { border: 2px dashed #999; border-radius: 50%; width: 110px; height: 110px; display: flex; align-items: center; justify-content: center; text-align: center; color: #999; font-size: .75rem; margin: 0 auto; }
        @media print {
            body { background: #fff; }
            .sheet { border: none; margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3">
    <a href="{{ route('admin.exams.results', $exam) }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Results</a>
    <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="bi bi-printer"></i> Print</button>
    <a href="{{ route('admin.exams.report_card.pdf', [$exam, $student]) }}" class="btn btn-dark btn-sm"><i class="bi bi-file-earmark-pdf"></i> Download PDF</a>
</div>

<div class="sheet">
    <div class="school-header">
        <h2>{{ config('app.name') }}</h2>
        <div>Student Progress Report</div>
        <div class="text-muted">{{ $exam->name }} @if($exam->term) — {{ $exam->term }} @endif @if($exam->exam_date) — {{ $exam->exam_date->format('d M Y') }} @endif</div>
    </div>

    <table class="table table-borderless table-sm mb-4">
        <tr>
            <td class="text-muted" style="width:140px;">Name</td><td class="fw-bold">{{ $student->user->name }}</td>
            <td class="text-muted" style="width:140px;">Admission No.</td><td class="fw-bold">{{ $student->admission_no }}</td>
        </tr>
        <tr>
            <td class="text-muted">Class</td><td>{{ $student->schoolClass->name ?? '—' }}</td>
            <td class="text-muted">Stream</td><td>{{ $student->section->name ?? '—' }}</td>
        </tr>
    </table>

    <table class="table results mb-3">
        <thead class="table-light">
            <tr><th>Subject</th><th>Marks</th><th>Out of</th><th>%</th><th>Grade</th><th>Position</th></tr>
        </thead>
        <tbody>
        @foreach($subjects as $subject)
            @php $result = $row['results'][$subject->id] ?? null; @endphp
            <tr>
                <td>{{ $subject->name }}</td>
                <td>{{ $result?->marks_obtained ?? '—' }}</td>
                <td>{{ $result?->max_marks ?? '—' }}</td>
                <td>{{ $result ? $result->percentage().'%' : '—' }}</td>
                <td>{{ $result?->grade ?? '—' }}</td>
                <td>{{ $result ? ($row['subject_positions'][$subject->id] ?? '—') : '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="row mb-4">
        <div class="col-3 text-center">
            <div class="text-muted small">Total</div>
            <div class="fw-bold fs-5">{{ $row['total'] }}/{{ $row['possible'] }}</div>
        </div>
        <div class="col-3 text-center">
            <div class="text-muted small">Mean</div>
            <div class="fw-bold fs-5">{{ $row['mean'] }}%</div>
        </div>
        <div class="col-3 text-center">
            <div class="text-muted small">Overall Grade</div>
            <div class="fw-bold fs-5">{{ $row['grade'] ?? '—' }} @if($row['points']) ({{ $row['points'] }} pts) @endif</div>
        </div>
        <div class="col-3 text-center">
            <div class="text-muted small">Position</div>
            <div class="fw-bold fs-5">{{ $row['class_position'] }} / {{ $classSize }}</div>
        </div>
    </div>

    <div class="no-print">
        <form method="POST" action="{{ route('admin.exams.comment.store', [$exam, $student]) }}">
            @csrf
            <div class="mb-2">
                <label class="form-label small">Class Teacher's Comment</label>
                <textarea name="class_teacher_comment" class="form-control" rows="2">{{ $comment->class_teacher_comment }}</textarea>
            </div>
            <div class="mb-2">
                <label class="form-label small">Principal's Comment</label>
                <textarea name="principal_comment" class="form-control" rows="2">{{ $comment->principal_comment }}</textarea>
            </div>
            <button class="btn btn-dark btn-sm">Save Comments</button>
        </form>
    </div>

    <div class="d-none d-print-block mt-4">
        <p><strong>Class Teacher's Comment:</strong> {{ $comment->class_teacher_comment ?? '.................................................................' }}</p>
        <p><strong>Principal's Comment:</strong> {{ $comment->principal_comment ?? '.................................................................' }}</p>
    </div>

    <div class="row mt-5">
        <div class="col-4">
            <div class="signature-line">Class Teacher</div>
        </div>
        <div class="col-4 text-center">
            <div class="stamp-box">School Stamp</div>
        </div>
        <div class="col-4">
            <div class="signature-line">Principal</div>
        </div>
    </div>
</div>

</body>
</html>
