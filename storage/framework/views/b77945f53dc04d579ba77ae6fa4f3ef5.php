<?php $__env->startSection('title', $media->original_name); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <a href="<?php echo e(route('student.media.index')); ?>" class="text-indigo-600 hover:underline">← กลับไปไฟล์</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <h1 class="text-xl font-bold"><?php echo e($media->original_name); ?></h1>
        <p class="text-gray-500 text-sm">
            <?php echo e($media->student->name); ?> | <?php echo e($media->classroom->name); ?> | <?php echo e($media->uploaded_date->format('d/m/Y')); ?>

        </p>
    </div>

    <div class="bg-black flex items-center justify-center" style="min-height: 400px;">
        <?php if($media->type === 'image'): ?>
            <img src="<?php echo e($media->url); ?>" alt="<?php echo e($media->original_name); ?>" class="max-w-full max-h-[70vh] object-contain">
        <?php else: ?>
            <video controls class="max-w-full max-h-[70vh]">
                <source src="<?php echo e($media->url); ?>" type="<?php echo e($media->mime_type); ?>">
                เบราว์เซอร์ไม่รองรับการเล่นวีดีโอ
            </video>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/student/media/show.blade.php ENDPATH**/ ?>