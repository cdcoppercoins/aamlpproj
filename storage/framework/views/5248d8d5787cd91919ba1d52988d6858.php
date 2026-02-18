<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'MiniLicensePlates.com'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('main.css')); ?>" />
</head>
<body>
<?php echo $__env->make('components.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="content-wrapper">
<?php echo $__env->yieldContent('content'); ?>
</div>
<?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH D:\aamlpproj\resources\views/layouts/app.blade.php ENDPATH**/ ?>