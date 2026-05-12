<?php $__env->startSection('title', 'ผู้ปกครอง'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">ผู้ปกครอง</h1>
    <a href="<?php echo e(route('teacher.parents.create')); ?>" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
        + เพิ่มผู้ปกครอง
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">อีเมล</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">นักเรียนที่ดูแล</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php $__empty_1 = true; $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo e($parent->name); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo e($parent->email); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php $__currentLoopData = $parent->parentStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-block px-2 py-1 mr-1 text-xs bg-blue-100 text-blue-700 rounded">
                                <?php echo e($ps->student->name ?? '-'); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?php echo e(route('teacher.parents.show', $parent)); ?>" class="text-indigo-600 hover:underline mr-3">ดู</a>
                        <a href="<?php echo e(route('teacher.parents.edit', $parent)); ?>" class="text-green-600 hover:underline mr-3">แก้ไข</a>
                        <form action="<?php echo e(route('teacher.parents.destroy', $parent)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการลบ?')">ลบ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">ยังไม่มีผู้ปกครอง</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/parents/index.blade.php ENDPATH**/ ?>