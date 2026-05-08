<?php $__env->startSection('title', $parent->name); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <a href="<?php echo e(route('teacher.parents.index')); ?>" class="text-indigo-600 hover:underline">← กลับไปผู้ปกครอง</a>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-xl font-bold"><?php echo e($parent->name); ?></h1>
            <p class="text-gray-500"><?php echo e($parent->email); ?></p>
        </div>
        <div>
            <a href="<?php echo e(route('teacher.parents.edit', $parent)); ?>" class="text-indigo-600 hover:underline">แก้ไข</a>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold mb-4">นักเรียนที่ดูแล</h2>
    <?php if($parent->parentStudents->count() > 0): ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $parent->parentStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium"><?php echo e($ps->student->name ?? '-'); ?></p>
                        <p class="text-sm text-gray-500">
                            <?php echo e($ps->student->code ?? ''); ?> | <?php echo e($ps->student->classroom->name ?? ''); ?>

                        </p>
                    </div>
                    <a href="<?php echo e(route('teacher.students.show', $ps->student)); ?>" class="text-indigo-600 hover:underline text-sm">ดูข้อมูลนักเรียน</a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500">ยังไม่มีนักเรียนที่เชื่อมโยง</p>
    <?php endif; ?>
    
    <div class="mt-4">
        <a href="<?php echo e(route('teacher.links.create')); ?>" class="text-sm text-indigo-600 hover:underline">+ เชื่อมโยงนักเรียน</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/parents/show.blade.php ENDPATH**/ ?>