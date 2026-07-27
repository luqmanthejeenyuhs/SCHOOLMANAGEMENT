<?php $__env->startSection('title', 'Add Teacher'); ?>
<?php $__env->startSection('content'); ?>
<div class="mb-3">
    <a href="<?php echo e(route('admin.teachers.index')); ?>" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left"></i> Back to Teachers</a>
    <h3 class="mb-0 mt-1">Add Teacher</h3>
</div>

<div class="card p-4" style="max-width:900px;">
    <form method="POST" action="<?php echo e(route('admin.teachers.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <h6 class="text-uppercase text-muted small mb-3">Personal Details &amp; Login</h6>
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
                <label class="form-label">Email (used to log in)</label>
                <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="<?php echo e(old('address')); ?>">
            </div>
        </div>

        <h6 class="text-uppercase text-muted small mb-3 mt-4">Employment &amp; Identity</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Employee / Staff Number</label>
                <input type="text" class="form-control" value="Auto-generated on save" disabled>
                <div class="form-text">Assigned automatically as <code>EMPLOYEE-&lt;id&gt;</code> — no need to type one in.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">National ID / Passport Number</label>
                <input type="text" name="id_number" class="form-control" value="<?php echo e(old('id_number')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">TSC Number</label>
                <input type="text" name="tsc_number" class="form-control" value="<?php echo e(old('tsc_number')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Qualification</label>
                <input type="text" name="qualification" class="form-control" value="<?php echo e(old('qualification')); ?>" placeholder="e.g. B.Ed Mathematics">
            </div>
            <div class="col-md-6">
                <label class="form-label">Joining Date</label>
                <input type="date" name="joining_date" class="form-control" value="<?php echo e(old('joining_date')); ?>">
            </div>
        </div>

        <h6 class="text-uppercase text-muted small mb-3 mt-4">Next of Kin</h6>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Full Name</label>
                <input type="text" name="next_of_kin_name" class="form-control" value="<?php echo e(old('next_of_kin_name')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="next_of_kin_phone" class="form-control" value="<?php echo e(old('next_of_kin_phone')); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Relationship</label>
                <input type="text" name="next_of_kin_relationship" class="form-control" value="<?php echo e(old('next_of_kin_relationship')); ?>" placeholder="e.g. Spouse">
            </div>
        </div>

        <h6 class="text-uppercase text-muted small mb-3 mt-4">Documents</h6>
        <p class="text-muted small mt-n2 mb-3">PDF, JPG or PNG. Passport photo max 5MB, other documents max 10MB each.</p>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Passport Photo</label>
                <input type="file" name="passport_photo" class="form-control" accept="image/*">
            </div>
            <div class="col-md-4">
                <label class="form-label">National ID / Passport Copy</label>
                <input type="file" name="national_id_document" class="form-control" accept=".pdf,image/*">
            </div>
            <div class="col-md-4">
                <label class="form-label">Police Clearance Certificate</label>
                <input type="file" name="police_clearance" class="form-control" accept=".pdf,image/*">
            </div>
            <div class="col-12">
                <label class="form-label">Other Documents (e.g. academic certificates)</label>
                <input type="file" name="other_documents[]" class="form-control" accept=".pdf,image/*" multiple>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-dark">Save Teacher</button>
            <a href="<?php echo e(route('admin.teachers.index')); ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/teachers/create.blade.php ENDPATH**/ ?>