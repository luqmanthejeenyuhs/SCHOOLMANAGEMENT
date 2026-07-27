<?php $__env->startSection('title', $section->schoolClass->name.' - '.$section->name); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.classes.index')); ?>">Classes</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.classes.show', $section->schoolClass)); ?>"><?php echo e($section->schoolClass->name); ?></a></li>
        <li class="breadcrumb-item active"><?php echo e($section->name); ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="mb-0"><?php echo e($section->schoolClass->name); ?> — <?php echo e($section->name); ?></h3>
        <span class="text-muted">
            <?php echo e($students->count()); ?> student<?php echo e($students->count() == 1 ? '' : 's'); ?>

            &middot; Class Teacher: <?php echo e($section->classTeacher->user->name ?? 'Not assigned'); ?>

        </span>
    </div>
    <a href="<?php echo e(route('admin.students.create')); ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Admit Student</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Admission No</th>
                    <th>Name</th>
                    <th>Guardian</th>
                    <th>Fee Balance</th>
                    <th>Exam Average</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><a href="<?php echo e(route('admin.students.show', $student)); ?>" class="fw-semibold"><?php echo e($student->admission_no); ?></a></td>
                    <td><?php echo e($student->user->name); ?></td>
                    <td><?php echo e($student->guardian_name ?? '—'); ?></td>
                    <td>
                        <?php if($student->fee_balance > 0): ?>
                            <span class="text-danger">KES <?php echo e(number_format($student->fee_balance, 2)); ?></span>
                        <?php else: ?>
                            <span class="text-success">Cleared</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($student->exam_average !== null ? $student->exam_average.'%' : '—'); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('admin.students.show', $student)); ?>" class="btn btn-sm btn-outline-secondary">View Profile</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No students in this stream yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/sections/show.blade.php ENDPATH**/ ?>