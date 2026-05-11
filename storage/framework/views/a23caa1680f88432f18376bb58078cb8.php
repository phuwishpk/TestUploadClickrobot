<?php $__env->startSection('title', 'นักเรียน'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex justify-between items-center flex-wrap gap-4">
    <h1 class="text-2xl font-bold text-gray-800">นักเรียน</h1>
    <div class="flex gap-2">
        <a href="<?php echo e(route('school_admin.students.create')); ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + เพิ่มนักเรียน
        </a>
    </div>
</div>

<div class="mb-4 flex flex-wrap gap-4 items-center">
    <form method="GET" action="<?php echo e(route('school_admin.students.index')); ?>" class="flex items-center gap-2">
        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="ค้นหาชื่อหรือรหัส..."
            class="border border-gray-300 rounded px-3 py-1.5 text-sm w-48">
        <select name="classroom_id" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
            <option value="">ทุกห้องเรียน</option>
            <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($classroom->id); ?>" <?php echo e($classroomId == $classroom->id ? 'selected' : ''); ?>>
                    <?php echo e($classroom->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button type="submit" class="bg-gray-100 border border-gray-300 px-3 py-1.5 rounded text-sm hover:bg-gray-200">
            ค้นหา
        </button>
        <?php if($search || $classroomId): ?>
            <a href="<?php echo e(route('school_admin.students.index')); ?>" class="text-sm text-red-600 hover:underline">ล้าง</a>
        <?php endif; ?>
    </form>
</div>

<?php if(session('success')): ?>
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัส</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ห้องเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">บัญชี</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono"><?php echo e($student->code); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo e($student->name); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-sm">
                        <?php echo e($student->classrooms->pluck('name')->take(2)->implode(', ')); ?>

                        <?php if($student->classrooms->count() > 2): ?>
                            <span class="text-gray-400">+<?php echo e($student->classrooms->count() - 2); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if($student->user): ?>
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">มี</span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">ไม่มี</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?php echo e(route('school_admin.students.show', $student)); ?>" class="text-indigo-600 hover:underline mr-3">ดู</a>
                        <a href="<?php echo e(route('school_admin.students.edit', $student)); ?>" class="text-green-600 hover:underline mr-3">แก้ไข</a>
                        <form action="<?php echo e(route('school_admin.students.destroy', $student)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการลบนักเรียนนี้?')">ลบ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <?php if($search || $classroomId): ?>
                            ไม่พบนักเรียนที่ค้นหา
                        <?php else: ?>
                            ยังไม่มีนักเรียน <a href="<?php echo e(route('school_admin.students.create')); ?>" class="text-indigo-600 hover:underline">เพิ่มนักเรียน</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($students->hasPages()): ?>
    <div class="mt-4">
        <?php echo e($students->withQueryString()->links()); ?>

    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/school_admin/students/index.blade.php ENDPATH**/ ?>