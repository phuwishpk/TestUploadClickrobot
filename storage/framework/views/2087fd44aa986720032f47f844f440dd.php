<?php $__env->startSection('title', 'ไฟล์ของฉัน'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">ไฟล์ของฉัน</h1>
    <p class="text-gray-600"><?php echo e($student->name); ?> | <?php echo e($student->classroom->name ?? ''); ?></p>
</div>

<?php if($media->count() > 0): ?>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <?php $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('student.media.show', $m)); ?>" class="block">
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition">
                    <?php if($m->type === 'image'): ?>
                        <img src="<?php echo e($m->url); ?>" alt="<?php echo e($m->original_name); ?>" class="w-full h-40 object-cover">
                    <?php else: ?>
                        <?php if($m->thumbnail_path): ?>
                            <img src="<?php echo e($m->thumbnail_url); ?>" alt="<?php echo e($m->original_name); ?>" class="w-full h-40 object-cover">
                        <?php else: ?>
                            <div class="w-full h-40 bg-red-50 flex items-center justify-center relative">
                                <svg class="w-16 h-16 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <div class="absolute bottom-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">VIDEO</div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="p-3">
                        <p class="text-sm truncate"><?php echo e($m->original_name); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($m->uploaded_date->format('d/m/Y')); ?></p>
                    </div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mt-6">
        <?php echo e($media->links()); ?>

    </div>
<?php else: ?>
    <div class="bg-gray-100 rounded-lg p-8 text-center">
        <p class="text-gray-500">ยังไม่มีไฟล์ที่อัปโหลด</p>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/student/media/index.blade.php ENDPATH**/ ?>