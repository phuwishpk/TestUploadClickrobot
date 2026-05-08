<?php $__env->startSection('title', 'ห้องเรียน'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">ห้องเรียน</h1>
    <a href="<?php echo e(route('teacher.classrooms.create')); ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        + สร้างห้องเรียน
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อห้องเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จำนวนนักเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php $__empty_1 = true; $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="<?php echo e(route('teacher.classrooms.show', $classroom)); ?>" class="text-indigo-600 hover:underline font-medium">
                            <?php echo e($classroom->name); ?>

                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                        <?php echo e($classroom->students_count); ?> คน
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?php echo e(route('teacher.classrooms.show', $classroom)); ?>" class="text-indigo-600 hover:underline mr-3">ดู</a>
                        <a href="<?php echo e(route('teacher.classrooms.edit', $classroom)); ?>" class="text-green-600 hover:underline mr-3">แก้ไข</a>
                        <form action="<?php echo e(route('teacher.classrooms.destroy', $classroom)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการลบห้องเรียนนี้?')">ลบ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">ยังไม่มีห้องเรียน</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/classrooms/index.blade.php ENDPATH**/ ?>