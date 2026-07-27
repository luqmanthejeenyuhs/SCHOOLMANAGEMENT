<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card — {{ $student->user->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; color: #222; margin: 0; padding: 0 30px; }
        .header { text-align: center; border-bottom: 3px double #333; padding-bottom: 10px; margin-bottom: 18px; }
        .header h2 { margin: 0 0 4px; letter-spacing: 1px; }
        .header .sub { color: #555; font-size: 12px; }

        table.info { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 12px; }
        table.info td { padding: 3px 4px; }
        table.info td.label { color: #666; width: 110px; }
        table.info td.value { font-weight: bold; width: 240px; }

        table.results { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 12px; }
        table.results th, table.results td { border: 1px solid #999; padding: 5px 7px; text-align: left; }
        table.results th { background: #eef1f7; }

        table.summary { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.summary td { text-align: center; padding: 6px; width: 25%; }
        table.summary .lbl { color: #666; font-size: 11px; }
        table.summary .val { font-weight: bold; font-size: 15px; }

        .comments p { font-size: 12px; margin: 6px 0; }

        table.sign { width: 100%; margin-top: 50px; }
        table.sign td { text-align: center; font-size: 12px; padding-top: 4px; }
        .sign-line { border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 4px; }
        .stamp-box { border: 2px dashed #999; border-radius: 50%; width: 100px; height: 100px; text-align: center; color: #999; font-size: 10px; margin: 0 auto; padding-top: 40px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>{{ config('app.name') }}</h2>
        <div>Student Progress Report</div>
        <div class="sub">{{ $exam->name }} @if($exam->term) — {{ $exam->term }} @endif @if($exam->exam_date) — {{ $exam->exam_date->format('d M Y') }} @endif</div>
    </div>

    <table class="info">
        <tr>
            <td class="label">Name</td><td class="value">{{ $student->user->name }}</td>
            <td class="label">Admission No.</td><td class="value">{{ $student->admission_no }}</td>
        </tr>
        <tr>
            <td class="label">Class</td><td class="value">{{ $student->schoolClass->name ?? '—' }}</td>
            <td class="label">Stream</td><td class="value">{{ $student->section->name ?? '—' }}</td>
        </tr>
    </table>

    <table class="results">
        <thead>
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

    <table class="summary">
        <tr>
            <td>
                <div class="lbl">Total</div>
                <div class="val">{{ $row['total'] }}/{{ $row['possible'] }}</div>
            </td>
            <td>
                <div class="lbl">Mean</div>
                <div class="val">{{ $row['mean'] }}%</div>
            </td>
            <td>
                <div class="lbl">Overall Grade</div>
                <div class="val">{{ $row['grade'] ?? '—' }} @if($row['points']) ({{ $row['points'] }} pts) @endif</div>
            </td>
            <td>
                <div class="lbl">Position</div>
                <div class="val">{{ $row['class_position'] }} / {{ $classSize }}</div>
            </td>
        </tr>
    </table>

    <div class="comments">
        <p><strong>Class Teacher's Comment:</strong> {{ $comment->class_teacher_comment ?? '.................................................................' }}</p>
        <p><strong>Principal's Comment:</strong> {{ $comment->principal_comment ?? '.................................................................' }}</p>
    </div>

    <table class="sign">
        <tr>
            <td style="width:33%;"><div class="sign-line">Class Teacher</div></td>
            <td style="width:34%;"><div class="stamp-box">School Stamp</div></td>
            <td style="width:33%;"><div class="sign-line">Principal</div></td>
        </tr>
    </table>

</body>
</html>
