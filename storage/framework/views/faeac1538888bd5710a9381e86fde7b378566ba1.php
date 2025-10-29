<?php $__env->startSection('title', __('messages.Drivers')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.Drivers')); ?></h1>
        <a href="<?php echo e(route('drivers.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_New_Driver')); ?>

        </a>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Driver_List')); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(__('messages.ID')); ?></th>
                            <th><?php echo e(__('messages.Photo')); ?></th>
                            <th><?php echo e(__('messages.Name')); ?></th>
                            <th><?php echo e(__('messages.Phone')); ?></th>
                            <th><?php echo e(__('messages.Car')); ?></th>
                            <th><?php echo e(__('messages.Balance')); ?></th>
                            <th><?php echo e(__('messages.Status')); ?></th>
                            <th><?php echo e(__('messages.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($driver->id); ?></td>
                            <td>
                                <?php if($driver->photo): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->photo)); ?>" alt="<?php echo e($driver->name); ?>" width="50">
                                <?php else: ?>
                                <img src="<?php echo e(asset('assets/admin/img/no-image.png')); ?>" alt="No Image" width="50">
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($driver->name); ?></td>
                            <td><?php echo e($driver->country_code); ?> <?php echo e($driver->phone); ?></td>
                            <td>
                                <?php echo e($driver->model ?? 'N/A'); ?>

                                <?php if($driver->color): ?>
                                <span class="badge badge-info"><?php echo e($driver->color); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($driver->balance); ?></td>
                            <td>
                                <?php if($driver->activate == 1): ?>
                                <span class="badge badge-success"><?php echo e(__('messages.Active')); ?></span>
                                <?php else: ?>
                                <span class="badge badge-danger"><?php echo e(__('messages.Inactive')); ?></span>
                                <?php endif; ?>
                            </td>
                             <td>
                                <div class="d-flex">
                                    <a href="<?php echo e(route('drivers.show', $driver->id)); ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('drivers.edit', $driver->id)); ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#topUpModal<?php echo e($driver->id); ?>">
                                        <i class="fas fa-wallet"></i>
                                    </button>
                                    <a href="<?php echo e(route('drivers.transactions', $driver->id)); ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-money-bill"></i>
                                    </a>
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

<?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="topUpModal<?php echo e($driver->id); ?>" tabindex="-1" role="dialog" aria-labelledby="topUpModalLabel<?php echo e($driver->id); ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="topUpModalLabel<?php echo e($driver->id); ?>"><?php echo e(__('messages.Top_Up_Balance_For')); ?>: <?php echo e($driver->name); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo e(route('drivers.topUp', $driver->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <?php if($driver->photo): ?>
                        <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->photo)); ?>" alt="<?php echo e($driver->name); ?>" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                        <img src="<?php echo e(asset('assets/admin/img/no-image.png')); ?>" alt="No Image" class="img-profile rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php endif; ?>
                        <h5 class="mt-2"><?php echo e($driver->name); ?></h5>
                        <h6><?php echo e(__('messages.Current_Balance')); ?>: <span class="text-primary"><?php echo e($driver->balance); ?></span></h6>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount<?php echo e($driver->id); ?>"><?php echo e(__('messages.Amount')); ?> <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount<?php echo e($driver->id); ?>" name="amount" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="note<?php echo e($driver->id); ?>"><?php echo e(__('messages.Note')); ?></label>
                        <textarea class="form-control" id="note<?php echo e($driver->id); ?>" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('messages.Close')); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo e(__('messages.Add_To_Balance')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\taksi\resources\views/admin/drivers/index.blade.php ENDPATH**/ ?>