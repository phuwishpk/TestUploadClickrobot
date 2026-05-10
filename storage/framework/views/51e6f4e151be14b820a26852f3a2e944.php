<?php $__env->startSection('title', $school->name); ?>

<?php $__env->startSection('content'); ?>
<div class="pb-16">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo e($school->name); ?></h1>
        <div class="space-x-2">
            <a href="<?php echo e(route('admin.schools.edit', $school)); ?>" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Edit</a>
            <a href="<?php echo e(route('admin.schools.index')); ?>" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">School Info</h2>
            <dl class="space-y-2">
                <div class="flex">
                    <dt class="w-24 text-gray-500">Code:</dt>
                    <dd><?php echo e($school->code ?? '-'); ?></dd>
                </div>
                <div class="flex">
                    <dt class="w-24 text-gray-500">Slug:</dt>
                    <dd><?php echo e($school->slug); ?></dd>
                </div>
                <div class="flex">
                    <dt class="w-24 text-gray-500">Description:</dt>
                    <dd><?php echo e($school->description ?? '-'); ?></dd>
                </div>
                <div class="flex">
                    <dt class="w-24 text-gray-500">Status:</dt>
                    <dd>
                        <span class="px-2 py-1 text-xs rounded <?php echo e($school->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo e($school->is_active ? 'Active' : 'Inactive'); ?>

                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Users</h2>
            <ul class="space-y-2">
                <?php $__empty_1 = true; $__currentLoopData = $school->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="flex justify-between items-center">
                        <span><?php echo e($user->name); ?> (<?php echo e($user->role); ?>)</span>
                        <span class="text-sm text-gray-500"><?php echo e($user->email); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-gray-500">No users</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold">Classrooms</h2>
        </div>
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $school->classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="px-6 py-4"><?php echo e($classroom->name); ?></td>
                    <td class="px-6 py-4"><?php echo e($classroom->teacher?->name ?? '-'); ?></td>
                    <td class="px-6 py-4"><?php echo e($classroom->students()->count()); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No classrooms</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/schools/show.blade.php ENDPATH**/ ?>