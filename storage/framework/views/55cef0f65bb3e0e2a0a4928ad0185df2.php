<?php $__env->startSection('title', 'Exam Results'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0"><?php echo e($exam->name); ?> — <?php echo e($exam->schoolClass->name); ?></h3>
        <?php if($exam->term): ?><span class="text-muted"><?php echo e($exam->term); ?></span><?php endif; ?>
    </div>
    <div>
        <a href="<?php echo e(route('admin.exams.report_cards.pdf', $exam)); ?>" class="btn btn-dark btn-sm"><i class="bi bi-file-earmark-pdf"></i> Download All Report Cards (PDF)</a>
        <a href="<?php echo e(route('admin.grading_scales.index')); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-sliders"></i> Grading Scale</a>
        <a href="<?php echo e(route('admin.exams.index')); ?>" class="btn btn-outline-secondary btn-sm">Back to Exams</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th><?php echo e($subject->name); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th>Total</th>
                    <th>Mean %</th>
                    <th>Grade</th>
                    <th>Class Pos.</th>
                    <th>Stream Pos.</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($row['class_position']); ?></td>
                    <td><?php echo e($row['student']->user->name); ?></td>
                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $result = $row['results'][$subject->id] ?? null; ?>
                        <td>
                            <?php if($result): ?>
                                <?php echo e($result->marks_obtained); ?>/<?php echo e($result->max_marks); ?>

                                <span class="badge bg-secondary"><?php echo e($result->grade ?? '—'); ?></span>
                                <?php if(($row['subject_positions'][$subject->id] ?? null)): ?>
                                    <span class="text-muted small">(#<?php echo e($row['subject_positions'][$subject->id]); ?>)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td class="fw-bold"><?php echo e($row['total']); ?>/<?php echo e($row['possible']); ?></td>
                    <td class="fw-bold"><?php echo e($row['mean']); ?>%</td>
                    <td><span class="badge bg-dark"><?php echo e($row['grade'] ?? '—'); ?></span></td>
                    <td><?php echo e($row['class_position']); ?> / <?php echo e(count($rows)); ?></td>
                    <td><?php echo e($row['stream_position'] ?? '—'); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('admin.exams.report_card', [$exam, $row['student']])); ?>" class="btn btn-sm btn-outline-primary">Report Card</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="<?php echo e(7 + $subjects->count()); ?>" class="text-center text-muted py-4">No results entered yet. Teachers can enter marks from their "Enter Results" page.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2">Numbers in brackets next to a subject score show that student's position in the class for that subject.</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/results/show.blade.php ENDPATH**/ ?>