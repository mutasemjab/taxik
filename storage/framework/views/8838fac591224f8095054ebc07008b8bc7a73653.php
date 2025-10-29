<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link"><?php echo e(__('messages.Home')); ?></a>
      </li>
     
      <li class="nav-item d-none d-sm-inline-block">
        <a href="<?php echo e(route('admin.logout')); ?>" class="nav-link"><?php echo e(__('messages.Logout')); ?></a>
      </li>
        <?php $__currentLoopData = LaravelLocalization::getSupportedLocales(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $properties): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a class="nav-link"  hreflang="<?php echo e($localeCode); ?>" href="<?php echo e(LaravelLocalization::getLocalizedURL($localeCode, null, [], true)); ?>">
            <?php echo e($properties['native']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>


  </nav>
<?php /**PATH C:\xampp\htdocs\taksi\resources\views/admin/includes/navbar.blade.php ENDPATH**/ ?>