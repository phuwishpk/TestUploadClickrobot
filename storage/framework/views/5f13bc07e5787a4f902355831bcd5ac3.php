<?php $__env->startSection('title', 'เชื่อมโยงผู้ปกครอง'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">เชื่อมโยงผู้ปกครอง-นักเรียน</h1>
    <a href="<?php echo e(route('teacher.links.create')); ?>" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
        + เพิ่มการเชื่อมโยง
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ผู้ปกครอง</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">นักเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ห้องเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php $__empty_1 = true; $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo e($link->parent->name ?? '-'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo e($link->student->name ?? '-'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?php echo e($link->student->classroom->name ?? '-'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <form action="<?php echo e(route('teacher.links.destroy', $link)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการยกเลิกการเชื่อมโยง?')">ยกเลิก</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">ยังไม่มีการเชื่อมโยง</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/links/index.blade.php ENDPATH**/ ?>