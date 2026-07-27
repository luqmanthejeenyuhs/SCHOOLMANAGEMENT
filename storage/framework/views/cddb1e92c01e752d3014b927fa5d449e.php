<?php $__env->startSection('title', 'Parents'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Parents / Guardians</h3>
    <a href="<?php echo e(route('admin.parents.create')); ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Add Parent</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Children Linked</th><th></th></tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><a href="<?php echo e(route('admin.parents.show', $parent)); ?>" class="fw-semibold text-decoration-none"><?php echo e($parent->name); ?></a></td>
                    <td><?php echo e($parent->email); ?></td>
                    <td><?php echo e($parent->phone ?? '—'); ?></td>
                    <td><?php echo e($parent->children_count); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('admin.parents.show', $parent)); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <form action="<?php echo e(route('admin.parents.destroy', $parent)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Remove this parent account?');">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No parent accounts yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3"><?php echo e($parents->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/parents/index.blade.php ENDPATH**/ ?>