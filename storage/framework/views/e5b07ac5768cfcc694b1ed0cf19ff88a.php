<?php $__env->startSection('title', 'เพิ่มนักเรียน'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="<?php echo e(route('teacher.students.index')); ?>" class="text-indigo-600 hover:underline">← กลับไปนักเรียน</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold mb-6">เพิ่มนักเรียนใหม่</h1>

        <form action="<?php echo e(route('teacher.students.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อนักเรียน</label>
                <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    required>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-4">
                <label for="classroom_ids" class="block text-sm font-medium text-gray-700 mb-1">ห้องเรียน (เลือกได้หลายห้อง)</label>
                <select name="classroom_ids[]" id="classroom_ids" multiple
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 <?php $__errorArgs = ['classroom_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    required>
                    <?php $__currentLoopData = $classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classroom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($classroom->id); ?>" <?php echo e(in_array($classroom->id, old('classroom_ids', [])) ? 'selected' : ''); ?>>
                            <?php echo e($classroom->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p class="mt-1 text-xs text-gray-500">กด Ctrl/Cmd เพื่อเลือกหลายห้อง</p>
                <?php $__errorArgs = ['classroom_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="create_account" value="1" <?php echo e(old('create_account') ? 'checked' : ''); ?>

                        class="w-4 h-4 text-indigo-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">สร้างบัญชีให้นักเรียน (เข้าสู่ระบบได้)</span>
                </label>
            </div>

            <div id="account_fields" class="<?php echo e(old('create_account') ? '' : 'hidden'); ?>">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                    <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="student@example.com">
                    <p class="mt-1 text-xs text-gray-500">รหัสผ่านเริ่มต้น: 12345</p>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="<?php echo e(route('teacher.students.index')); ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    ยกเลิก
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    เพิ่มนักเรียน
                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelector('input[name="create_account"]').addEventListener('change', function() {
    document.getElementById('account_fields').classList.toggle('hidden', !this.checked);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/teacher/students/create.blade.php ENDPATH**/ ?>