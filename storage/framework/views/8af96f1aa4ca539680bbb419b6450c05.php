<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('favicon.svg')); ?>">
    <title><?php echo $__env->yieldContent('title', 'ระบบ Upload รูป/วีดีโอ'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php if(auth()->guard()->check()): ?>
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?php echo e(route(auth()->user()->role . '.dashboard')); ?>" class="text-xl font-bold text-indigo-600">
                        ระบบ Upload
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600"><?php echo e(auth()->user()->name); ?></span>
                    <span class="px-2 py-1 text-xs font-semibold text-white bg-indigo-600 rounded">
                        <?php echo e(auth()->user()->role === 'teacher' ? 'ครู' : (auth()->user()->role === 'parent' ? 'ผู้ปกครอง' : 'นักเรียน')); ?>

                    </span>
                    <form action="<?php echo e(route('logout')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-gray-500 hover:text-red-600">
                            ออกจากระบบ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if(session('success')): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="bg-white shadow mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-gray-500 text-sm">
                ระบบ Upload รูป/วีดีโอ สำหรับโรงเรียน
            </p>
        </div>
    </footer>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>