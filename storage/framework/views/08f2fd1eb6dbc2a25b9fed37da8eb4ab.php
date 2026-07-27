<?php $__env->startSection('title', 'Record SBA Scores'); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.cbc.index')); ?>">CBC Curriculum</a></li>
        <li class="breadcrumb-item active">Record SBA Scores</li>
    </ol>
</nav>
<h3 class="mb-3">Record School-Based Assessment (SBA) Scores</h3>
<p class="text-muted small">Each SBA performance task contributes 20% (60% total across SBA 1–3) to the KPSEA exit profile for Grades 4–6.</p>

<div class="card p-3 mb-4">
    <form method="GET" action="<?php echo e(route('admin.cbc.sba.grid')); ?>" class="row g-2 align-items-end">
        <div class="col-md-2">
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
        <div class="col-md-3">
            <label class="form-label small">Learning Area</label>
            <select name="cbc_learning_area_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select learning area</option>
                <?php $__currentLoopData = $learningAreas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $la): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($la->id); ?>" <?php if($learningAreaId == $la->id): echo 'selected'; endif; ?>><?php echo e($la->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">SBA Task</label>
            <select name="sba_number" class="form-select" onchange="this.form.submit()">
                <option value="1" <?php if($sbaNumber == 1): echo 'selected'; endif; ?>>SBA 1</option>
                <option value="2" <?php if($sbaNumber == 2): echo 'selected'; endif; ?>>SBA 2</option>
                <option value="3" <?php if($sbaNumber == 3): echo 'selected'; endif; ?>>SBA 3</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Term</label>
            <input type="text" name="term" class="form-control" value="<?php echo e($term); ?>" onchange="this.form.submit()">
        </div>
    </form>
</div>

<?php if($classId && $learningAreaId): ?>
<form method="POST" action="<?php echo e(route('admin.cbc.sba.store')); ?>">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="cbc_learning_area_id" value="<?php echo e($learningAreaId); ?>">
    <input type="hidden" name="sba_number" value="<?php echo e($sbaNumber); ?>">
    <input type="hidden" name="term" value="<?php echo e($term); ?>">
    <div class="card p-3 mb-3" style="max-width:200px;">
        <label class="form-label small">Max Score</label>
        <input type="number" name="max_score" id="max_score" class="form-control" value="100" required>
    </div>
    <div class="card">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Admission No</th><th>Student</th><th style="width:150px;">Score</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $existing = $student->sbaRecords->first()?->score; ?>
                <tr>
                    <td><?php echo e($student->admission_no); ?></td>
                    <td><?php echo e($student->user->name); ?></td>
                    <td><input type="number" step="0.01" min="0" name="scores[<?php echo e($student->id); ?>]" class="form-control form-control-sm sba-input" value="<?php echo e($existing); ?>"></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="3" class="text-center text-muted py-3">No students in this class.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($students->count()): ?>
    <button class="btn btn-dark mt-3">Save Scores</button>
    <?php endif; ?>
</form>
<script>
    const maxInput = document.getElementById('max_score');
    function applyMax() { document.querySelectorAll('.sba-input').forEach(el => el.max = maxInput.value); }
    maxInput.addEventListener('input', applyMax);
    applyMax();
</script>
<?php else: ?>
<p class="text-muted">Select a class and a learning area to begin.</p>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\luqman\Desktop\SCHOOLMANAGEMENT\sms\resources\views/admin/cbc/sba.blade.php ENDPATH**/ ?>