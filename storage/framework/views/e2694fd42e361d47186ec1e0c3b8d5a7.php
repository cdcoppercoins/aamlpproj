<header class="site-header">
  <div class="header-frame">
    <div class="header-inner">
      <img src="<?php echo e(asset('header_banner.png')); ?>"
           alt="Header Banner"
           class="site-banner">
    </div>
  </div>

  <!-- NEW wrapper that is as wide as the content area and filled with white -->
  <div class="nav-outer">
    <nav class="main-nav">
      <ul>
        <li><a href="<?php echo e(route('home')); ?>">HOME</a></li>
        <li><a href="<?php echo e(route('gallery')); ?>">GALLERY</a></li>
        <li><a href="<?php echo e(route('about')); ?>">ABOUT</a></li>
        <li><a href="<?php echo e(route('history')); ?>">HISTORY</a></li>
        <li><a href="<?php echo e(route('contribute')); ?>">CONTRIBUTE</a></li>
        <li><a href="https://www.ebay.com/str/minilicenseplates" target="new">SHOP</a></li>
      </ul>
    </nav>
  </div>
</header>
<?php /**PATH D:\aamlpproj\resources\views/components/header.blade.php ENDPATH**/ ?>