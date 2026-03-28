<?php $__env->startSection('title', __('messages.Services')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.Services')); ?></h1>
        <a href="<?php echo e(route('services.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_New_Service')); ?>

        </a>
    </div>

    <!-- Services Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Services_List')); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(__('messages.ID')); ?></th>
                            <th><?php echo e(__('messages.Photo')); ?></th>
                            <th><?php echo e(__('messages.Name')); ?></th>
                            <th><?php echo e(__('messages.Pricing')); ?></th>
                            <th><?php echo e(__('messages.Commission')); ?></th>
                            <th><?php echo e(__('messages.Payment_Method')); ?></th>
                            <th><?php echo e(__('messages.Capacity')); ?></th>
                            <th><?php echo e(__('messages.Status')); ?></th>
                            <th><?php echo e(__('messages.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($service->id); ?></td>
                            <td>
                                <?php if($service->photo): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $service->photo)); ?>" alt="<?php echo e($service->getName()); ?>" width="50" height="50" class="img-thumbnail">
                                <?php else: ?>
                                <img src="<?php echo e(asset('assets/admin/img/no-image.png')); ?>" alt="No Image" width="50" height="50" class="img-thumbnail">
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><strong><?php echo e($service->name_en); ?></strong></div>
                                <div class="text-muted small"><?php echo e($service->name_ar); ?></div>
                                <?php if($service->is_electric == 1): ?>
                                <span class="badge badge-success mt-1">
                                    <i class="fas fa-bolt"></i> <?php echo e(__('messages.Electric')); ?>

                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small">
                                    <strong><i class="fas fa-sun text-warning"></i> <?php echo e(__('messages.Morning')); ?>:</strong><br>
                                    <?php echo e(__('messages.Start')); ?>: <?php echo e($service->start_price_morning); ?><br>
                                    <?php echo e(__('messages.Per_KM')); ?>: <?php echo e($service->price_per_km_morning); ?>

                                </div>
                                <hr class="my-1">
                                <div class="small">
                                    <strong><i class="fas fa-moon text-info"></i> <?php echo e(__('messages.Evening')); ?>:</strong><br>
                                    <?php echo e(__('messages.Start')); ?>: <?php echo e($service->start_price_evening); ?><br>
                                    <?php echo e(__('messages.Per_KM')); ?>: <?php echo e($service->price_per_km_evening); ?>

                                </div>
                            </td>
                            <td>
                                <?php echo e($service->admin_commision); ?>

                                <span class="badge badge-info"><?php echo e($service->getCommisionTypeText()); ?></span>
                            </td>
                            <td>
                                <?php $__currentLoopData = $service->servicePayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge badge-primary mb-1"><?php echo e($payment->payment_method_text); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <i class="fas fa-users"></i> <?php echo e($service->capacity); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($service->activate == 1): ?>
                                    <span class="badge badge-success"><?php echo e(__('messages.Active')); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><?php echo e(__('messages.Inactive')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo e(route('services.show', $service->id)); ?>" class="btn btn-info btn-sm" title="<?php echo e(__('messages.View')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('services.edit', $service->id)); ?>" class="btn btn-primary btn-sm" title="<?php echo e(__('messages.Edit')); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-danger btn-sm" title="<?php echo e(__('messages.Delete')); ?>" onclick="event.preventDefault(); if(confirm('<?php echo e(__('messages.Delete_Confirm')); ?>')) document.getElementById('delete-form-<?php echo e($service->id); ?>').submit();">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <form id="delete-form-<?php echo e($service->id); ?>" action="<?php echo e(route('services.destroy', $service->id)); ?>" method="POST" style="display: none;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 25
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\taxik\resources\views/admin/services/index.blade.php ENDPATH**/ ?>