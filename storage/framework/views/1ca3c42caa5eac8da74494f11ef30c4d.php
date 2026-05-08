<?php $__env->startSection('title', $student->name); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <a href="<?php echo e(route('teacher.students.index')); ?>" class="text-indigo-600 hover:underline">← กลับไปนักเรียน</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-xl font-bold"><?php echo e($student->name); ?></h1>
                    <p class="text-gray-500">รหัส: <?php echo e($student->code); ?> | <?php echo e($student->classroom->name); ?></p>
                </div>
                <div>
                    <a href="<?php echo e(route('teacher.students.edit', $student)); ?>" class="text-indigo-600 hover:underline">แก้ไข</a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-500">บัญชีผู้ใช้</p>
                    <p class="font-medium">
                        <?php if($student->user): ?>
                            <span class="text-green-600"><?php echo e($student->user->email); ?></span>
                        <?php else: ?>
                            <span class="text-gray-400">ไม่มีบัญชี</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-500">ไฟล์ที่อัปโหลด</p>
                    <p class="font-medium text-indigo-600"><?php echo e($student->media->count()); ?> ไฟล์</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">ผู้ปกครองที่เชื่อมโยง</h2>
            <?php if($student->parents->count() > 0): ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $student->parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div>
                                <p class="font-medium"><?php echo e($parent->name); ?></p>
                                <p class="text-sm text-gray-500"><?php echo e($parent->email); ?></p>
                            </div>
                            <a href="<?php echo e(route('teacher.parents.show', $parent)); ?>" class="text-indigo-600 hover:underline text-sm">ดู</a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">ยังไม่มีผู้ปกครองที่เชื่อมโยง</p>
            <?php endif; ?>
            <div class="mt-4">
                <a href="<?php echo e(route('teacher.links.create')); ?>" class="text-sm text-indigo-600 hover:underline">+ เพิ่มผู้ปกครอง</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">ไฟล์ล่าสุด</h2>
            <?php if($student->media->count() > 0): ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $student->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center p-2 bg-gray-50 rounded">
                            <div class="flex-shrink-0 mr-2 text-xs">
                                <?php if($media->type === 'image'): ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">IMG</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded">VID</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm truncate"><?php echo e($media->original_name); ?></p>
                                <p class="text-xs text-gray-400"><?php echo e($media->uploaded_date->format('d/m/Y')); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-sm">ยังไม่มีไฟล์</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/students/show.blade.php ENDPATH**/ ?>