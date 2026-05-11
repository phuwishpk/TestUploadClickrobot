<?php $__env->startSection('title', 'School Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="pb-16">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">ยินดีต้อนรับ, <?php echo e(auth()->user()->name); ?></h1>
        <p class="text-gray-600"><?php echo e($school->name); ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">ห้องเรียน</p>
                    <p class="text-3xl font-bold text-indigo-600"><?php echo e($stats['classrooms']); ?></p>
                </div>
                <div class="text-indigo-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">ครู</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo e($stats['teachers']); ?></p>
                </div>
                <div class="text-green-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">นักเรียน</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo e($stats['students']); ?></p>
                </div>
                <div class="text-purple-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-4">
            <a href="<?php echo e(route('school_admin.classrooms.index')); ?>" class="block bg-indigo-600 text-white p-4 rounded-lg hover:bg-indigo-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                </svg>
                จัดการห้องเรียน
            </a>
            <a href="<?php echo e(route('school_admin.teachers.index')); ?>" class="block bg-green-600 text-white p-4 rounded-lg hover:bg-green-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                จัดการครู
            </a>
            <a href="<?php echo e(route('school_admin.students.index')); ?>" class="block bg-purple-600 text-white p-4 rounded-lg hover:bg-purple-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                จัดการนักเรียน
            </a>
            <a href="<?php echo e(route('school_admin.upload.create')); ?>" class="block bg-red-600 text-white p-4 rounded-lg hover:bg-red-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                อัปโหลดไฟล์
            </a>
        </div>

        <div class="lg:col-span-3">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">ห้องเรียน</h2>
            <?php if($classrooms->count() > 0): ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900"><?php echo e($classroom->name); ?></p>
                                <p class="text-sm text-gray-500"><?php echo e($classroom->teacher?->name ?? 'ยังไม่มีครูประจำชั้น'); ?> | <?php echo e($classroom->students()->count()); ?> คน</p>
                            </div>
                            <a href="<?php echo e(route('school_admin.classrooms.show', $classroom)); ?>" class="text-indigo-600 hover:text-indigo-800">ดู</a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">ยังไม่มีห้องเรียน</p>
            <?php endif; ?>
        </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/school_admin/dashboard.blade.php ENDPATH**/ ?>