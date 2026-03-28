<?php $__env->startSection('title', __('messages.Spam_Orders')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-trash-alt"></i> <?php echo e(__('messages.Spam_Orders')); ?>

        </h1>
        <div>
            <a href="<?php echo e(route('spam-orders.analytics')); ?>" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> <?php echo e(__('messages.Analytics')); ?>

            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?php echo e(__('messages.Total_Spam_Orders')); ?>

                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($stats['total'])); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                <?php echo e(__('messages.User_Cancelled')); ?>

                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($stats['user_cancelled'])); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <?php echo e(__('messages.Driver_Cancelled')); ?>

                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($stats['driver_cancelled'])); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car-crash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?php echo e(__('messages.Auto_Cancelled')); ?>

                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($stats['auto_cancelled'])); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-robot fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> <?php echo e(__('messages.Filters')); ?>

            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('spam-orders.index')); ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search"><?php echo e(__('messages.Order_Number')); ?></label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo e(request('search')); ?>" placeholder="<?php echo e(__('messages.Search')); ?>">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status"><?php echo e(__('messages.Status')); ?></label>
                            <select class="form-control" id="status" name="status">
                                <option value=""><?php echo e(__('messages.All')); ?></option>
                                <option value="user_cancel_order" <?php echo e(request('status') == 'user_cancel_order' ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.User_Cancelled')); ?>

                                </option>
                                <option value="driver_cancel_order" <?php echo e(request('status') == 'driver_cancel_order' ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Driver_Cancelled')); ?>

                                </option>
                                <option value="cancel_cron_job" <?php echo e(request('status') == 'cancel_cron_job' ? 'selected' : ''); ?>>
                                    <?php echo e(__('messages.Auto_Cancelled')); ?>

                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="from_date"><?php echo e(__('messages.Date_From')); ?></label>
                            <input type="date" class="form-control" id="from_date" name="from_date" 
                                   value="<?php echo e(request('from_date')); ?>">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="to_date"><?php echo e(__('messages.Date_To')); ?></label>
                            <input type="date" class="form-control" id="to_date" name="to_date" 
                                   value="<?php echo e(request('to_date')); ?>">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> <?php echo e(__('messages.Filter')); ?>

                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Spam Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Spam_Orders_List')); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(__('messages.ID')); ?></th>
                            <th><?php echo e(__('messages.Order_Number')); ?></th>
                            <th><?php echo e(__('messages.User')); ?></th>
                            <th><?php echo e(__('messages.Service')); ?></th>
                            <th><?php echo e(__('messages.Status')); ?></th>
                            <th><?php echo e(__('messages.Price')); ?></th>
                            <th><?php echo e(__('messages.Cancelled_At')); ?></th>
                            <th><?php echo e(__('messages.Reason')); ?></th>
                            <th><?php echo e(__('messages.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $spamOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($order->id); ?></td>
                            <td>
                                <span class="badge badge-secondary"><?php echo e($order->number); ?></span>
                            </td>
                            <td>
                                <?php if($order->user): ?>
                                    <a href="<?php echo e(route('users.show', $order->user_id)); ?>">
                                        <?php echo e($order->user->name); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($order->service): ?>
                                    <?php echo e($order->service->name_en); ?>

                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($order->status == 'user_cancel_order'): ?>
                                    <span class="badge badge-danger"><?php echo e(__('messages.User_Cancelled')); ?></span>
                                <?php elseif($order->status == 'driver_cancel_order'): ?>
                                    <span class="badge badge-warning"><?php echo e(__('messages.Driver_Cancelled')); ?></span>
                                <?php elseif($order->status == 'cancel_cron_job'): ?>
                                    <span class="badge badge-info"><?php echo e(__('messages.Auto_Cancelled')); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?php echo e(ucfirst($order->status)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(number_format($order->total_price_after_discount, 2)); ?> JD</td>
                            <td>
                                <?php if($order->cancelled_at): ?>
                                    <?php echo e($order->cancelled_at->format('Y-m-d H:i')); ?>

                                    <br>
                                    <small class="text-muted"><?php echo e($order->cancelled_at->diffForHumans()); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($order->reason_for_cancel): ?>
                                    <small><?php echo e(Str::limit($order->reason_for_cancel, 30)); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('spam-orders.show', $order->id)); ?>" 
                                   class="btn btn-sm btn-info" title="<?php echo e(__('messages.View_Details')); ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="<?php echo e(route('spam-orders.destroy', $order->id)); ?>" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('<?php echo e(__('messages.Confirm_Delete')); ?>');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" title="<?php echo e(__('messages.Delete')); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center"><?php echo e(__('messages.No_Spam_Orders_Found')); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                <?php echo e($spamOrders->appends(request()->query())->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\taxik\resources\views/admin/spam-orders/index.blade.php ENDPATH**/ ?>