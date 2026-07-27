<?php $__env->startSection('title', 'Add Employee'); ?>
<?php $__env->startSection('content'); ?>
<h3 class="mb-3">Add Employee</h3>
<div class="card p-4" style="max-width:800px;">
    <form method="POST" action="<?php echo e(route('admin.employees.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Job Title</label>
                <input type="text" name="job_title" class="form-control" value="<?php echo e(old('job_title')); ?>" placeholder="e.g. Bursar, Cook, Driver, Teacher" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Link to User Account (for self clock-in)</label>
                <select name="user_id" class="form-select">
                    <option value="">— Not linked —</option>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>" <?php if(old('user_id') == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?> (<?php echo e($user->role); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div class="form-text">Linking lets this person clock in/out themselves from their portal. Leave unlinked for staff who don't log into the system (e.g. cooks, drivers) — the admin can mark their attendance manually instead.</div>
            </div>
            <div class="col-md-6">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="is_teaching_staff" value="1" id="isTeaching">
                    <label class="form-check-label" for="isTeaching">This is a teaching staff member</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo e(old('phone')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">National ID Number</label>
                <input type="text" name="id_number" class="form-control" value="<?php echo e(old('id_number')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">KRA PIN</label>
                <input type="text" name="kra_pin" class="form-control" value="<?php echo e(old('kra_pin')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">NSSF Number</label>
                <input type="text" name="nssf_number" class="form-control" value="<?php echo e(old('nssf_number')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">SHIF Number</label>
                <input type="text" name="shif_number" class="form-control" value="<?php echo e(old('shif_number')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Employment Date</label>
                <input type="date" name="employment_date" class="form-control" value="<?php echo e(old('employment_date')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Basic Salary (KES)</label>
                <input type="number" step="0.01" name="basic_salary" class="form-control" value="<?php echo e(old('basic_salary')); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">House Allowance (KES)</label>
                <input type="number" step="0.01" name="house_allowance" class="form-control" value="<?php echo e(old('house_allowance', 0)); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Transport Allowance (KES)</label>
                <input type="number" step="0.01" name="transport_allowance" class="form-control" value="<?php echo e(old('transport_allowance', 0)); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Other Allowances (KES)</label>
                <input type="number" step="0.01" name="other_allowances" class="form-control" value="<?php echo e(old('other_allowances', 0)); ?>">
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-dark">Save Employee</button>
            <a href="<?php echo e(route('admin.employees.index')); ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/payroll/employees/create.blade.php ENDPATH**/ ?>