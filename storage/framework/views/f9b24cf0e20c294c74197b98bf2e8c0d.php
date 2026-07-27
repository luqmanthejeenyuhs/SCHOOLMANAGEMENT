<?php $__env->startSection('title', 'Admit Student'); ?>
<?php $__env->startSection('content'); ?>
<h3 class="mb-3">Admit Student</h3>
<div class="card p-4" style="max-width:800px;">
    <form method="POST" action="<?php echo e(route('admin.students.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required>
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
                <label class="form-label">Admission No</label>
                <input type="text" name="admission_no" class="form-control" value="<?php echo e(old('admission_no')); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Class</label>
                <select name="school_class_id" id="classSelect" class="form-select" required>
                    <option value="">Select class</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php if(old('school_class_id') == $class->id): echo 'selected'; endif; ?>><?php echo e($class->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Section</label>
                <select name="section_id" id="sectionSelect" class="form-select">
                    <option value="">Select section</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control" value="<?php echo e(old('dob')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Guardian Name</label>
                <input type="text" name="guardian_name" class="form-control" value="<?php echo e(old('guardian_name')); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Guardian Phone</label>
                <input type="text" name="guardian_phone" class="form-control" value="<?php echo e(old('guardian_phone')); ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="<?php echo e(old('address')); ?>">
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-dark">Save Student</button>
            <a href="<?php echo e(route('admin.students.index')); ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
const classesData = <?php echo json_encode($classes->keyBy('id'), 15, 512) ?>;
const classSelect = document.getElementById('classSelect');
const sectionSelect = document.getElementById('sectionSelect');

function populateSections() {
    const classId = classSelect.value;
    sectionSelect.innerHTML = '<option value="">Select section</option>';
    if (classId && classesData[classId]) {
        classesData[classId].sections.forEach(function (section) {
            const opt = document.createElement('option');
            opt.value = section.id;
            opt.textContent = section.name;
            sectionSelect.appendChild(opt);
        });
    }
}
classSelect.addEventListener('change', populateSections);
populateSections();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/students/create.blade.php ENDPATH**/ ?>