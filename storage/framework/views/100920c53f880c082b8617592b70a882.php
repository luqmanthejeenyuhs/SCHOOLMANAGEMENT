<?php $__env->startSection('title', 'Teachers'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Teachers</h3>
    <a href="<?php echo e(route('admin.teachers.create')); ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Add Teacher</a>
</div>

<form method="GET" action="<?php echo e(route('admin.teachers.index')); ?>" class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-8">
            <label class="form-label mb-1 small text-muted">Search by teacher name or employee/staff number</label>
            <input type="text" name="search" value="<?php echo e($search); ?>" class="form-control" placeholder="e.g. Jane Wanjiku or EMP-1023">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
        </div>
        <?php if($search): ?>
        <div class="col-md-2">
            <a href="<?php echo e(route('admin.teachers.index')); ?>" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Qualification</th><th>Joined</th><th></th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><a href="<?php echo e(route('admin.teachers.show', $teacher)); ?>" class="fw-semibold text-decoration-none"><?php echo e($teacher->employee_id); ?></a></td>
                    <td><a href="<?php echo e(route('admin.teachers.show', $teacher)); ?>" class="text-decoration-none"><?php echo e($teacher->user->name); ?></a></td>
                    <td><?php echo e($teacher->user->email); ?></td>
                    <td><?php echo e($teacher->qualification ?? '—'); ?></td>
                    <td><?php echo e($teacher->joining_date?->format('d M Y') ?? '—'); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('admin.teachers.show', $teacher)); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="<?php echo e(route('admin.teachers.edit', $teacher)); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="<?php echo e(route('admin.teachers.destroy', $teacher)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Remove this teacher?');">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No teachers found<?php echo e($search ? ' for "'.$search.'"' : ''); ?>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3"><?php echo e($teachers->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/teachers/index.blade.php ENDPATH**/ ?>