<?php $__env->startSection('title', $classroom->name); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <a href="<?php echo e(route('teacher.classrooms.index')); ?>" class="text-indigo-600 hover:underline">← กลับไปห้องเรียน</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-bold"><?php echo e($classroom->name); ?></h1>
                <a href="<?php echo e(route('teacher.classrooms.edit', $classroom)); ?>" class="text-indigo-600 hover:underline">แก้ไข</a>
            </div>
            <p class="text-gray-600">นักเรียน <?php echo e($students->count()); ?> คน</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">รายชื่อนักเรียน</h2>
                <a href="<?php echo e(route('teacher.students.create')); ?>?classroom_id=<?php echo e($classroom->id); ?>" class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                    + เพิ่มนักเรียน
                </a>
            </div>

            <?php if($students->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">รหัส</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">บัญชี</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-2 text-sm"><?php echo e($student->code); ?></td>
                                    <td class="px-4 py-2 text-sm"><?php echo e($student->name); ?></td>
                                    <td class="px-4 py-2 text-sm">
                                        <?php if($student->user): ?>
                                            <span class="text-green-600">มี</span>
                                        <?php else: ?>
                                            <span class="text-gray-400">ไม่มี</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="<?php echo e(route('teacher.students.show', $student)); ?>" class="text-indigo-600 hover:underline">ดู</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">ยังไม่มีนักเรียนในห้องนี้</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">ไฟล์ล่าสุด</h2>
            <?php if($media->count() > 0): ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center p-2 bg-gray-50 rounded">
                            <div class="flex-shrink-0 mr-2">
                                <?php if($m->type === 'image'): ?>
                                    <span class="text-blue-500">IMG</span>
                                <?php else: ?>
                                    <span class="text-red-500">VID</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm truncate"><?php echo e($m->original_name); ?></p>
                                <p class="text-xs text-gray-400"><?php echo e($m->student->name); ?></p>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/classrooms/show.blade.php ENDPATH**/ ?>