<?php $__env->startSection('title', 'Classrooms'); ?>

<?php $__env->startSection('content'); ?>
<div class="pb-16">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Classrooms</h1>
        <a href="<?php echo e(route('school_admin.classrooms.create')); ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            + Add Classroom
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4"><?php echo e($classroom->name); ?></td>
                    <td class="px-6 py-4"><?php echo e($classroom->teacher?->name ?? '-'); ?></td>
                    <td class="px-6 py-4"><?php echo e($classroom->students()->count()); ?></td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="<?php echo e(route('school_admin.classrooms.show', $classroom)); ?>" class="text-indigo-600 hover:text-indigo-800">View</a>
                        <a href="<?php echo e(route('school_admin.classrooms.edit', $classroom)); ?>" class="text-green-600 hover:text-green-800">Edit</a>
                        <form action="<?php echo e(route('school_admin.classrooms.destroy', $classroom)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" onclick="return confirm('Delete this classroom?')" class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No classrooms found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <?php echo e($classrooms->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/school_admin/classrooms/index.blade.php ENDPATH**/ ?>