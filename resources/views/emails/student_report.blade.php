<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Progress report</title>
</head>
<body style="margin:0;padding:0;background:#f2f6f4;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f6f4;padding:32px 0;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;border-top:4px solid #C9972F;max-width:100%;">
    <tr><td style="background:#0F7A45;padding:24px 32px;">
        <span style="color:#fff;font-size:20px;font-weight:bold;">{{ $school->name }}</span>
    </td></tr>

    <tr><td style="padding:28px 32px;">
        <p style="font-size:16px;color:#222;margin-top:0;">Dear {{ $guardianName }},</p>
        <p style="font-size:15px;color:#444;line-height:1.5;">
            Please find the latest report for <strong>{{ $report['student_name'] }}</strong>
            ({{ $report['admission_no'] }}@if($report['class_label']), {{ $report['class_label'] }}@endif).
        </p>

        @if($note)
            <p style="font-size:14px;color:#0A5A33;background:#E8F5EC;border-radius:8px;padding:12px 14px;line-height:1.5;">{{ $note }}</p>
        @endif

        {{-- ATTENDANCE --}}
        @if($report['attendance'])
            @php $a = $report['attendance']; @endphp
            <h3 style="font-size:16px;color:#0A5A33;margin:24px 0 8px;">Attendance <span style="font-weight:normal;font-size:13px;color:#777;">({{ $a['range'] }})</span></h3>
            @if($a['total'] === 0)
                <p style="font-size:14px;color:#666;">No attendance was recorded in this period.</p>
            @else
                <table cellpadding="8" cellspacing="0" width="100%" style="background:#E8F5EC;border-radius:8px;text-align:center;">
                    <tr>
                        <td><div style="font-size:22px;font-weight:bold;color:#0A5A33;">{{ $a['rate'] }}%</div><div style="font-size:12px;color:#555;">Attendance rate</div></td>
                        <td><div style="font-size:22px;font-weight:bold;color:#0A5A33;">{{ $a['present'] }}</div><div style="font-size:12px;color:#555;">Present</div></td>
                        <td><div style="font-size:22px;font-weight:bold;color:#b26a00;">{{ $a['late'] }}</div><div style="font-size:12px;color:#555;">Late</div></td>
                        <td><div style="font-size:22px;font-weight:bold;color:#b00020;">{{ $a['absent'] }}</div><div style="font-size:12px;color:#555;">Absent</div></td>
                    </tr>
                </table>
                @if(count($a['absent_dates']))
                    <p style="font-size:13px;color:#555;margin:10px 0 0;">Days absent: {{ implode(', ', $a['absent_dates']) }}@if($a['absent'] > count($a['absent_dates'])) … @endif</p>
                @endif
            @endif
        @endif

        {{-- EXAM RESULTS --}}
        @if($report['results'] !== null || $report['results_requested'])
            <h3 style="font-size:16px;color:#0A5A33;margin:24px 0 8px;">Exam results
                @if($report['results'])<span style="font-weight:normal;font-size:13px;color:#777;">({{ $report['results']['exam'] }}@if($report['results']['term']), {{ $report['results']['term'] }}@endif)</span>@endif
            </h3>
            @if(! $report['results'])
                <p style="font-size:14px;color:#666;">No results have been recorded yet for this exam.</p>
            @else
                @php $r = $report['results']; @endphp
                <table cellpadding="6" cellspacing="0" width="100%" style="border-collapse:collapse;font-size:14px;">
                    <tr style="background:#f2f6f4;color:#0A5A33;text-align:left;">
                        <th style="padding:8px;">Subject</th><th style="padding:8px;text-align:right;">Marks</th><th style="padding:8px;text-align:right;">%</th><th style="padding:8px;text-align:center;">Grade</th>
                    </tr>
                    @foreach($r['subjects'] as $row)
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:8px;">{{ $row['subject'] }}</td>
                            <td style="padding:8px;text-align:right;">{{ rtrim(rtrim(number_format($row['marks'], 1), '0'), '.') }}/{{ rtrim(rtrim(number_format($row['max'], 1), '0'), '.') }}</td>
                            <td style="padding:8px;text-align:right;">{{ $row['percentage'] }}</td>
                            <td style="padding:8px;text-align:center;">{{ $row['grade'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight:bold;background:#E8F5EC;">
                        <td style="padding:8px;">Overall</td>
                        <td style="padding:8px;text-align:right;">{{ rtrim(rtrim(number_format($r['total'], 1), '0'), '.') }}/{{ rtrim(rtrim(number_format($r['possible'], 1), '0'), '.') }}</td>
                        <td style="padding:8px;text-align:right;">{{ $r['mean'] }}</td>
                        <td style="padding:8px;text-align:center;">{{ $r['grade'] ?? '—' }}</td>
                    </tr>
                </table>
                <p style="font-size:13px;color:#555;margin:10px 0 0;">
                    Class position: <strong>{{ $r['class_position'] }}</strong> of {{ $r['class_size'] }}
                    @if($r['stream_position']) &middot; Stream position: <strong>{{ $r['stream_position'] }}</strong>@endif
                </p>
            @endif
        @endif

        {{-- FEES --}}
        @if($report['fees'])
            @php $f = $report['fees']; @endphp
            <h3 style="font-size:16px;color:#0A5A33;margin:24px 0 8px;">Fee statement</h3>
            <table cellpadding="6" cellspacing="0" width="100%" style="font-size:14px;">
                <tr><td>Total billed</td><td align="right">KES {{ number_format($f['billed'], 2) }}</td></tr>
                <tr><td>Total paid</td><td align="right">KES {{ number_format($f['paid'], 2) }}</td></tr>
                <tr style="font-weight:bold;"><td>Balance</td><td align="right" style="color:{{ $f['balance'] > 0 ? '#b00020' : '#0A5A33' }};">KES {{ number_format($f['balance'], 2) }}</td></tr>
            </table>
        @endif

        <p style="font-size:14px;color:#444;line-height:1.5;margin-top:24px;">
            You can see more detail any time in the parent portal:
            <a href="{{ $portalUrl }}" style="color:#0F7A45;">{{ $portalUrl }}</a>
        </p>
        <p style="font-size:12px;color:#999;margin-top:24px;">
            Sent on {{ $report['generated_at'] }} by {{ $school->name }}. If this report is not meant for you, please let the school know.
        </p>
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
