<?php $__env->startSection('title', 'Contribute | MiniLicensePlates.com'); ?>

<?php $__env->startSection('content'); ?>
<div class="set-width">
    <h1>Contribute</h1>

    <p>
        Have an unlisted plate, a variety, better images, or historical information?
        Send the details below, or email <a href="mailto:cdcoppercoins@gmail.com">cdcoppercoins@gmail.com</a>.
    </p>

    <?php if(session('success')): ?>
        <p><strong><?php echo e(session('success')); ?></strong></p>
    <?php else: ?>
        <?php if(session('error')): ?>
            <p><strong><?php echo e(session('error')); ?></strong></p>
        <?php endif; ?>

        <form method="post" action="<?php echo e(route('contribute.store')); ?>">
            <?php echo csrf_field(); ?>
            <p>
                <label>Your name<br>
                    <input name="name" value="<?php echo e(old('name')); ?>" style="width:100%; padding:10px;" required>
                </label>
            </p>
            <p>
                <label>Your email<br>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" style="width:100%; padding:10px;" required>
                </label>
            </p>
            <p>
                <label>Message<br>
                    <textarea name="message" rows="10" style="width:100%; padding:10px;" required><?php echo e(old('message')); ?></textarea>
                </label>
            </p>

            <!-- honeypot (bots fill this, humans don't) -->
            <div style="position:absolute; left:-9999px;">
                <label>Company <input name="company" tabindex="-1" autocomplete="off"></label>
            </div>

            <p><button type="submit" style="padding:12px 18px;">Send</button></p>
        </form>

        <p>
            Postal address:<br>
            Minilicenseplates<br>
            PO Box 2364<br>
            Smithfield, NC 27577
        </p>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\aamlpproj\resources\views/contribute.blade.php ENDPATH**/ ?>