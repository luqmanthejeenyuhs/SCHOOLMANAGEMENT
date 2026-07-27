<?php $__env->startSection('title', 'Add Parent'); ?>
<?php $__env->startSection('content'); ?>
<h3 class="mb-3">Add Parent / Guardian</h3>

<div class="card p-4" style="max-width:640px;">
    <form method="POST" action="<?php echo e(route('admin.parents.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Relationship</label>
                <select name="relationship" class="form-select">
                    <option value="Parent">Parent</option>
                    <option value="Mother">Mother</option>
                    <option value="Father">Father</option>
                    <option value="Guardian">Guardian</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Link to Child(ren)</label>
                <select name="children[]" class="form-select" multiple size="8" required>
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($student->id); ?>"><?php echo e($student->admission_no); ?> — <?php echo e($student->user->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select more than one child.</small>
            </div>
        </div>
        <button class="btn btn-dark mt-4"><i class="bi bi-check-lg"></i> Create Parent Account</button>
        <a href="<?php echo e(route('admin.parents.index')); ?>" class="btn btn-outline-secondary mt-4">Cancel</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/parents/create.blade.php ENDPATH**/ ?>