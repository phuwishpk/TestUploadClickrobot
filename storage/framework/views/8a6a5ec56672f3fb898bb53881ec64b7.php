<?php $__env->startSection('title', 'Media Library'); ?>

<?php $__env->startSection('content'); ?>
<div class="pb-16">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Media Library</h1>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">School</label>
                <select name="school_id" class="px-4 py-2 border rounded-md" onchange="this.form.submit()">
                    <option value="">All Schools</option>
                    <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($school->id); ?>" <?php echo e(request('school_id') == $school->id ? 'selected' : ''); ?>>
                            <?php echo e($school->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Type</label>
                <select name="type" class="px-4 py-2 border rounded-md" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="image" <?php echo e(request('type') == 'image' ? 'selected' : ''); ?>>Photos</option>
                    <option value="video" <?php echo e(request('type') == 'video' ? 'selected' : ''); ?>>Videos</option>
                </select>
            </div>
            <div>
                <a href="<?php echo e(route('admin.media.index')); ?>" class="px-4 py-2 border rounded-md hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <a href="<?php echo e(route('admin.media.show', $item)); ?>" class="bg-white rounded-lg shadow overflow-hidden group hover:shadow-lg transition">
            <div class="aspect-square bg-gray-100 relative">
                <?php if($item->type === 'image'): ?>
                    <img src="<?php echo e($item->url); ?>" alt="<?php echo e($item->original_name); ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <?php if($item->thumbnail_path): ?>
                        <img src="<?php echo e($item->thumbnail_url); ?>" alt="<?php echo e($item->original_name); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-red-50">
                            <svg class="w-16 h-16 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">VIDEO</div>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <p class="text-sm font-medium text-gray-800 truncate" title="<?php echo e($item->original_name); ?>"><?php echo e($item->original_name); ?></p>
                <p class="text-xs text-gray-500 mt-1">
                    <?php echo e($item->classroom->school->name ?? 'No school'); ?>

                </p>
                <p class="text-xs text-gray-400 mt-1">
                    <?php echo e($item->student->name ?? 'No student'); ?> | <?php echo e($item->classroom->name ?? 'No classroom'); ?>

                </p>
                <p class="text-xs text-gray-400 mt-1">
                    <?php echo e($item->uploaded_date->format('d/m/Y H:i')); ?>

                </p>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-4 text-center text-gray-500 py-8">
            No media found
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <?php echo e($media->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/media/index.blade.php ENDPATH**/ ?>