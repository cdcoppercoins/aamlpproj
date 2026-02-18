<?php $__env->startSection('title', 'Gallery | MiniLicensePlates.com'); ?>

<?php $__env->startSection('content'); ?>
<div class="set-list set-width">
    <?php $__currentLoopData = $folderMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setName => $folderCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $enabled = !empty($availableSets[$setName]); ?>
        <a
            class="set-box<?php echo e($enabled ? '' : ' disabled'); ?>"
            <?php if($enabled): ?>
                href="<?php echo e(route('gallery.show', ['setName' => urlencode($setName)])); ?>"
            <?php else: ?>
                href="javascript:void(0)"
            <?php endif; ?>
        >
            <?php if($enabled && !empty($setThumbnails[$setName])): ?>
                <img
                    src="<?php echo e($setThumbnails[$setName]); ?>"
                    alt="<?php echo e(htmlspecialchars($setName, ENT_QUOTES, 'UTF-8')); ?> thumbnail"
                    class="set-thumb">
            <?php else: ?>
                <div class="set-thumb placeholder"></div>
            <?php endif; ?>
            <span class="set-label"><?php echo e($setName); ?></span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\aamlpproj\resources\views/gallery.blade.php ENDPATH**/ ?>