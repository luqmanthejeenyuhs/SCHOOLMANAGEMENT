<?php $__env->startSection('title', $subject->name); ?>
<?php $__env->startSection('content'); ?>

<div class="mb-3">
    <a href="<?php echo e(route('admin.subjects.index')); ?>" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Back to Subjects</a>
    <h3 class="mb-0 mt-1"><?php echo e($subject->name); ?></h3>
    <span class="text-muted"><?php echo e($subject->schoolClass->name ?? '—'); ?> <?php if($subject->code): ?> &middot; Code: <?php echo e($subject->code); ?> <?php endif; ?></span>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Teachers teaching this subject</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Teacher</th><th>Class</th><th>Section</th></tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <?php if($a->teacher): ?>
                                    <a href="<?php echo e(route('admin.teachers.show', $a->teacher)); ?>" class="text-decoration-none"><?php echo e($a->teacher->user->name); ?></a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($a->schoolClass->name ?? '—'); ?></td>
                            <td><?php echo e($a->section->name ?? 'All sections'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No teacher assigned to this subject yet. Assign one from the teacher's profile.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Performance by class</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Class</th><th>Average Score</th><th>Students Assessed</th><th>Results Recorded</th></tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $performanceByClass; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($perf['class']->name ?? '—'); ?></td>
                            <td class="fw-semibold"><?php echo e($perf['average']); ?>%</td>
                            <td><?php echo e($perf['students_assessed']); ?></td>
                            <td><?php echo e($perf['results_recorded']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No exam results recorded for this subject yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/subjects/show.blade.php ENDPATH**/ ?>