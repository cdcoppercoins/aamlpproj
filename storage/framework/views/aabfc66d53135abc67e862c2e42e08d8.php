<?php $__env->startSection('title', 'MiniLicensePlates.com'); ?>

<?php $__env->startSection('content'); ?>
<div class="set-width">
    <h1>MiniLicensePlates.com</h1>

    <p>
        A visual reference library of miniature license plate toys issued with candy, gum, and cereal — plus related
        bicycle vanity plates and other products.
    </p>

    <p>
        <a class="home-box" href="<?php echo e(route('gallery')); ?>">Enter the Gallery</a>
    </p>

    <p>
        New here? Start with <a href="<?php echo e(route('about')); ?>">About</a> or browse the <a href="<?php echo e(route('history')); ?>">History</a>.
    </p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\aamlpproj\resources\views/home.blade.php ENDPATH**/ ?>