<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Cards — <?php echo e($exam->schoolClass->name); ?> — <?php echo e($exam->name); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; color: #222; margin: 0; padding: 0 30px; }
        .sheet { page-break-after: always; padding-top: 20px; }
        .sheet:last-child { page-break-after: auto; }

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

<?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $comment = $comments->get($row['student']->id); ?>
    <div class="sheet">
        <div class="header">
            <h2><?php echo e(config('app.name')); ?></h2>
            <div>Student Progress Report</div>
            <div class="sub"><?php echo e($exam->name); ?> <?php if($exam->term): ?> — <?php echo e($exam->term); ?> <?php endif; ?> <?php if($exam->exam_date): ?> — <?php echo e($exam->exam_date->format('d M Y')); ?> <?php endif; ?></div>
        </div>

        <table class="info">
            <tr>
                <td class="label">Name</td><td class="value"><?php echo e($row['student']->user->name); ?></td>
                <td class="label">Admission No.</td><td class="value"><?php echo e($row['student']->admission_no); ?></td>
            </tr>
            <tr>
                <td class="label">Class</td><td class="value"><?php echo e($exam->schoolClass->name ?? '—'); ?></td>
                <td class="label">Stream</td><td class="value"><?php echo e($row['student']->section->name ?? '—'); ?></td>
            </tr>
        </table>

        <table class="results">
            <thead>
                <tr><th>Subject</th><th>Marks</th><th>Out of</th><th>%</th><th>Grade</th><th>Position</th></tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $result = $row['results'][$subject->id] ?? null; ?>
                <tr>
                    <td><?php echo e($subject->name); ?></td>
                    <td><?php echo e($result?->marks_obtained ?? '—'); ?></td>
                    <td><?php echo e($result?->max_marks ?? '—'); ?></td>
                    <td><?php echo e($result ? $result->percentage().'%' : '—'); ?></td>
                    <td><?php echo e($result?->grade ?? '—'); ?></td>
                    <td><?php echo e($result ? ($row['subject_positions'][$subject->id] ?? '—') : '—'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td>
                    <div class="lbl">Total</div>
                    <div class="val"><?php echo e($row['total']); ?>/<?php echo e($row['possible']); ?></div>
                </td>
                <td>
                    <div class="lbl">Mean</div>
                    <div class="val"><?php echo e($row['mean']); ?>%</div>
                </td>
                <td>
                    <div class="lbl">Overall Grade</div>
                    <div class="val"><?php echo e($row['grade'] ?? '—'); ?> <?php if($row['points']): ?> (<?php echo e($row['points']); ?> pts) <?php endif; ?></div>
                </td>
                <td>
                    <div class="lbl">Position</div>
                    <div class="val"><?php echo e($row['class_position']); ?> / <?php echo e($classSize); ?></div>
                </td>
            </tr>
        </table>

        <div class="comments">
            <p><strong>Class Teacher's Comment:</strong> <?php echo e($comment->class_teacher_comment ?? '.................................................................'); ?></p>
            <p><strong>Principal's Comment:</strong> <?php echo e($comment->principal_comment ?? '.................................................................'); ?></p>
        </div>

        <table class="sign">
            <tr>
                <td style="width:33%;"><div class="sign-line">Class Teacher</div></td>
                <td style="width:34%;"><div class="stamp-box">School Stamp</div></td>
                <td style="width:33%;"><div class="sign-line">Principal</div></td>
            </tr>
        </table>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</body>
</html>
<?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/results/report_cards_bulk_pdf.blade.php ENDPATH**/ ?>