<?php if(Session::has('error')): ?>
<div class="alert alert-danger" role="alert">
    <?php echo e(Session::get('error')); ?>

  </div>
  <?php endif; ?>
<?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="alert alert-danger" role="alert">
        <?php echo e($message); ?>

    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\xampp\htdocs\taksi\resources\views/admin/includes/alerts/error.blade.php ENDPATH**/ ?>