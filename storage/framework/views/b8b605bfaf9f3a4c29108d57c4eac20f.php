<?php $__env->startSection('title', $media->original_name); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <a href="<?php echo e(route('admin.media.index')); ?>" class="text-indigo-600 hover:underline">← กลับไปไฟล์</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <h1 class="text-xl font-bold"><?php echo e($media->original_name); ?></h1>
        <p class="text-gray-500 text-sm mt-1">
            <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs mr-2">
                <?php echo e($media->classroom->school->name ?? 'No school'); ?>

            </span>
            <?php echo e($media->student->name ?? 'No student'); ?> | <?php echo e($media->classroom->name ?? 'No classroom'); ?> | <?php echo e($media->uploaded_date->format('d/m/Y H:i')); ?>

        </p>
        <p class="text-gray-400 text-xs mt-1">
            Uploaded by: <?php echo e($media->uploader->name ?? 'Unknown'); ?>

        </p>
    </div>

    <div class="bg-black flex items-center justify-center" style="min-height: 400px;">
        <?php if($media->type === 'image'): ?>
            <img src="<?php echo e($media->url); ?>" alt="<?php echo e($media->original_name); ?>" class="max-w-full max-h-[70vh] object-contain">
        <?php else: ?>
            <video controls class="max-w-full max-h-[70vh]" autoplay>
                <source src="<?php echo e($media->url); ?>" type="<?php echo e($media->mime_type); ?>">
                เบราว์เซอร์ไม่รองรับการเล่นวีดีโอ
            </video>
        <?php endif; ?>
    </div>

    <div class="p-4 border-t">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500">File Size:</span>
                <span class="ml-2"><?php echo e($media->formatted_size); ?></span>
            </div>
            <?php if($media->formatted_compression_change): ?>
            <div>
                <span class="text-gray-500">Compression:</span>
                <span class="ml-2 text-green-600"><?php echo e($media->formatted_compression_change); ?></span>
            </div>
            <?php endif; ?>
            <div>
                <span class="text-gray-500">Type:</span>
                <span class="ml-2 uppercase"><?php echo e($media->type); ?></span>
            </div>
            <div>
                <span class="text-gray-500">Format:</span>
                <span class="ml-2"><?php echo e($media->mime_type); ?></span>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/media/show.blade.php ENDPATH**/ ?>