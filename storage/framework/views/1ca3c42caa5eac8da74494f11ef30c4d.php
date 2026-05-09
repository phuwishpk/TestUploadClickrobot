<?php $__env->startSection('title', $student->name); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <a href="<?php echo e(route('teacher.students.index')); ?>" class="text-indigo-600 hover:underline">← กลับไปนักเรียน</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-xl font-bold"><?php echo e($student->name); ?></h1>
                    <p class="text-gray-500">รหัส: <?php echo e($student->code); ?></p>
                    <p class="text-gray-500">ห้องเรียน: <?php echo e($student->classrooms->pluck('name')->implode(', ')); ?></p>
                </div>
                <div>
                    <a href="<?php echo e(route('teacher.students.edit', $student)); ?>" class="text-indigo-600 hover:underline">แก้ไข</a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-500">บัญชีผู้ใช้</p>
                    <p class="font-medium">
                        <?php if($student->user): ?>
                            <span class="text-green-600"><?php echo e($student->user->email); ?></span>
                        <?php else: ?>
                            <span class="text-gray-400">ไม่มีบัญชี</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-500">ไฟล์ที่อัปโหลด</p>
                    <p class="font-medium text-indigo-600"><?php echo e($student->media->count()); ?> ไฟล์</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">ผู้ปกครองที่เชื่อมโยง</h2>
            <?php if($student->parents->count() > 0): ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $student->parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div>
                                <p class="font-medium"><?php echo e($parent->name); ?></p>
                                <p class="text-sm text-gray-500"><?php echo e($parent->email); ?></p>
                            </div>
                            <a href="<?php echo e(route('teacher.parents.show', $parent)); ?>" class="text-indigo-600 hover:underline text-sm">ดู</a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">ยังไม่มีผู้ปกครองที่เชื่อมโยง</p>
            <?php endif; ?>
            <div class="mt-4">
                <a href="<?php echo e(route('teacher.links.create')); ?>" class="text-sm text-indigo-600 hover:underline">+ เพิ่มผู้ปกครอง</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">ไฟล์ล่าสุด</h2>
            <?php if($student->media->count() > 0): ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $student->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center p-2 bg-gray-50 rounded-lg group">
                            <div class="flex-shrink-0 mr-2 text-xs w-12 h-12">
                                <?php if($media->type === 'image'): ?>
                                    <img src="<?php echo e($media->url); ?>" alt="<?php echo e($media->original_name); ?>" class="w-full h-full object-cover rounded">
                                <?php else: ?>
                                    <?php if($media->thumbnail_path): ?>
                                        <img src="<?php echo e($media->thumbnail_url); ?>" alt="<?php echo e($media->original_name); ?>" class="w-full h-full object-cover rounded">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-red-100 rounded flex items-center justify-center">
                                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm truncate"><?php echo e($media->original_name); ?></p>
                                <p class="text-xs text-gray-400"><?php echo e($media->uploaded_date->format('d/m/Y')); ?></p>
                            </div>
                            <form action="<?php echo e(route('teacher.media.destroy', $media)); ?>" method="POST" class="delete-form">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="delete-btn opacity-0 group-hover:opacity-100 bg-red-500 hover:bg-red-600 text-white p-1.5 rounded transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-sm">ยังไม่มีไฟล์</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-2">ยืนยันการลบ</h3>
        <p class="text-gray-600 mb-4">คุณต้องการลบไฟล์นี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้</p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">ยกเลิก</button>
            <button type="button" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">ลบ</button>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let deleteFormToSubmit = null;

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        deleteFormToSubmit = this;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    });
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteFormToSubmit) {
        deleteFormToSubmit.submit();
    }
});

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
    deleteFormToSubmit = null;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/students/show.blade.php ENDPATH**/ ?>