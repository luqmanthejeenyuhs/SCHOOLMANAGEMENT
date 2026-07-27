<?php $__env->startSection('title', 'Record Values & Behaviour'); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.cbc.index')); ?>">CBC Curriculum</a></li>
        <li class="breadcrumb-item active">Record Values &amp; Behaviour</li>
    </ol>
</nav>
<h3 class="mb-3">Record Values &amp; Behaviour</h3>

<div class="card p-3 mb-4">
    <form method="GET" action="<?php echo e(route('admin.cbc.values.grid')); ?>" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Class</label>
            <select name="school_class_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select class</option>
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($class->id); ?>" <?php if($classId == $class->id): echo 'selected'; endif; ?>><?php echo e($class->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Stream</label>
            <select name="section_id" class="form-select" onchange="this.form.submit()">
                <option value="">All streams</option>
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($classId == $class->id): ?>
                        <?php $__currentLoopData = $class->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($section->id); ?>" <?php if($sectionId == $section->id): echo 'selected'; endif; ?>><?php echo e($section->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Value</label>
            <select name="value_area" class="form-select" onchange="this.form.submit()">
                <option value="">Select value</option>
                <?php $__currentLoopData = \App\Models\CbcValueRecord::VALUES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if($valueArea === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Term</label>
            <input type="text" name="term" class="form-control" value="<?php echo e($term); ?>" onchange="this.form.submit()">
        </div>
    </form>
</div>

<?php if($classId && $valueArea): ?>
<form method="POST" action="<?php echo e(route('admin.cbc.values.store')); ?>">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="value_area" value="<?php echo e($valueArea); ?>">
    <input type="hidden" name="term" value="<?php echo e($term); ?>">
    <div class="card">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Admission No</th><th>Student</th><th style="width:220px;">Rating</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $existing = $student->valueRecords->first()?->rating; ?>
                <tr>
                    <td><?php echo e($student->admission_no); ?></td>
                    <td><?php echo e($student->user->name); ?></td>
                    <td>
                        <select name="ratings[<?php echo e($student->id); ?>]" class="form-select form-select-sm">
                            <option value="">— Not rated —</option>
                            <?php $__currentLoopData = \App\Models\CbcValueRecord::RATINGS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($code); ?>" <?php if($existing === $code): echo 'selected'; endif; ?>><?php echo e($code); ?> — <?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="3" class="text-center text-muted py-3">No students in this class.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($students->count()): ?>
    <button class="btn btn-dark mt-3">Save Ratings</button>
    <?php endif; ?>
</form>
<?php else: ?>
<p class="text-muted">Select a class and a value to begin.</p>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/cbc/values.blade.php ENDPATH**/ ?>