<?php $__env->startSection('title', 'Students'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Students</h3>
    <a href="<?php echo e(route('admin.students.create')); ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Admit Student</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Admission No</th><th>Name</th><th>Class</th><th>Section</th><th>Guardian</th><th></th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><a href="<?php echo e(route('admin.students.show', $student)); ?>" class="fw-semibold"><?php echo e($student->admission_no); ?></a></td>
                    <td><?php echo e($student->user->name); ?></td>
                    <td><?php echo e($student->schoolClass->name ?? '—'); ?></td>
                    <td><?php echo e($student->section->name ?? '—'); ?></td>
                    <td><?php echo e($student->guardian_name ?? '—'); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('admin.students.show', $student)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <a href="<?php echo e(route('admin.students.edit', $student)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="<?php echo e(route('admin.students.destroy', $student)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Remove this student?');">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No students yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3"><?php echo e($students->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/students/index.blade.php ENDPATH**/ ?>