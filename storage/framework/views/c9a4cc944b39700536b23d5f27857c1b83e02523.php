<?php $__env->startSection('title', __('messages.Orders')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.Orders')); ?></h1>
        <a href="<?php echo e(route('orders.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(__('messages.Add_New_Order')); ?>

        </a>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Filter_Orders')); ?></h6>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('orders.index')); ?>" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="user_id"><?php echo e(__('messages.User')); ?></label>
                            <select class="form-control" id="user_id" name="user_id">
                                <option value=""><?php echo e(__('messages.All_Users')); ?></option>
                                <?php $__currentLoopData = $users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                                    <?php echo e($user->name); ?> (<?php echo e($user->phone ?? $user->email); ?>)
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="driver_id"><?php echo e(__('messages.Driver')); ?></label>
                            <select class="form-control" id="driver_id" name="driver_id">
                                <option value=""><?php echo e(__('messages.All_Drivers')); ?></option>
                                <?php $__currentLoopData = $drivers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($driver->id); ?>" <?php echo e(request('driver_id') == $driver->id ? 'selected' : ''); ?>>
                                    <?php echo e($driver->name); ?> (<?php echo e($driver->phone ?? $driver->email); ?>)
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="service_id"><?php echo e(__('messages.Service')); ?></label>
                            <select class="form-control" id="service_id" name="service_id">
                                <option value=""><?php echo e(__('messages.All_Services')); ?></option>
                                <?php $__currentLoopData = $services ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($service->id); ?>" <?php echo e(request('service_id') == $service->id ? 'selected' : ''); ?>>
                                    <?php echo e($service->name_en ?? $service->name); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status"><?php echo e(__('messages.Status')); ?></label>
                            <select class="form-control" id="status" name="status">
                                <option value="" <?php echo e(request('status') == '' ? 'selected' : ''); ?>><?php echo e(__('messages.All_Statuses')); ?></option>
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>><?php echo e(__('messages.Pending')); ?></option>
                                <option value="driver_accepted" <?php echo e(request('status') == 'driver_accepted' ? 'selected' : ''); ?>><?php echo e(__('messages.Driver_Accepted')); ?></option>
                                <option value="driver_go_to_user" <?php echo e(request('status') == 'driver_go_to_user' ? 'selected' : ''); ?>><?php echo e(__('messages.Driver_Going_To_User')); ?></option>
                                <option value="user_with_driver" <?php echo e(request('status') == 'user_with_driver' ? 'selected' : ''); ?>><?php echo e(__('messages.User_With_Driver')); ?></option>
                                <option value="delivered" <?php echo e(request('status') == 'delivered' ? 'selected' : ''); ?>><?php echo e(__('messages.Delivered')); ?></option>
                                <option value="user_cancel_order" <?php echo e(request('status') == 'user_cancel_order' ? 'selected' : ''); ?>><?php echo e(__('messages.User_Cancelled')); ?></option>
                                <option value="driver_cancel_order" <?php echo e(request('status') == 'driver_cancel_order' ? 'selected' : ''); ?>><?php echo e(__('messages.Driver_Cancelled')); ?></option>
                                <option value="cancel_cron_job" <?php echo e(request('status') == 'cancel_cron_job' ? 'selected' : ''); ?>><?php echo e(__('messages.Auto_Cancelled')); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="payment_method"><?php echo e(__('messages.Payment_Method')); ?></label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="" <?php echo e(request('payment_method') == '' ? 'selected' : ''); ?>><?php echo e(__('messages.All_Methods')); ?></option>
                                <option value="cash" <?php echo e(request('payment_method') == 'cash' ? 'selected' : ''); ?>><?php echo e(__('messages.Cash')); ?></option>
                                <option value="visa" <?php echo e(request('payment_method') == 'visa' ? 'selected' : ''); ?>><?php echo e(__('messages.Visa')); ?></option>
                                <option value="wallet" <?php echo e(request('payment_method') == 'wallet' ? 'selected' : ''); ?>><?php echo e(__('messages.Wallet')); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status_payment"><?php echo e(__('messages.Payment_Status')); ?></label>
                            <select class="form-control" id="status_payment" name="status_payment">
                                <option value="" <?php echo e(request('status_payment') == '' ? 'selected' : ''); ?>><?php echo e(__('messages.All')); ?></option>
                                <option value="pending" <?php echo e(request('status_payment') == 'pending' ? 'selected' : ''); ?>><?php echo e(__('messages.Pending')); ?></option>
                                <option value="paid" <?php echo e(request('status_payment') == 'paid' ? 'selected' : ''); ?>><?php echo e(__('messages.Paid')); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from"><?php echo e(__('messages.Date_From')); ?></label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo e(request('date_from')); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to"><?php echo e(__('messages.Date_To')); ?></label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo e(request('date_to')); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="is_hybrid_payment"><?php echo e(__('messages.Hybrid_Payment')); ?></label>
                            <select class="form-control" id="is_hybrid_payment" name="is_hybrid_payment">
                                <option value="" <?php echo e(request('is_hybrid_payment') == '' ? 'selected' : ''); ?>><?php echo e(__('messages.All')); ?></option>
                                <option value="1" <?php echo e(request('is_hybrid_payment') == '1' ? 'selected' : ''); ?>><?php echo e(__('messages.Yes')); ?></option>
                                <option value="0" <?php echo e(request('is_hybrid_payment') == '0' ? 'selected' : ''); ?>><?php echo e(__('messages.No')); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?php echo e(__('messages.Filter')); ?>

                        </button>
                        <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> <?php echo e(__('messages.Reset')); ?>

                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?php echo e(__('messages.Total_Orders')); ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($statistics['total_orders']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?php echo e(__('messages.Completed_Orders')); ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($statistics['completed_orders']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                <?php echo e(__('messages.Cancelled_Orders')); ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($statistics['cancelled_orders']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                                <?php echo e(__('messages.Total_Revenue')); ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                               JD <?php echo e(number_format($statistics['total_revenue'], 2)); ?>

                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Orders_List')); ?></h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(__('messages.ID')); ?></th>
                            <th><?php echo e(__('messages.Order_Number')); ?></th>
                            <th><?php echo e(__('messages.Date')); ?></th>
                            <th><?php echo e(__('messages.User')); ?></th>
                            <th><?php echo e(__('messages.Driver')); ?></th>
                            <th><?php echo e(__('messages.Service')); ?></th>
                            <th><?php echo e(__('messages.Route')); ?></th>
                            <th><?php echo e(__('messages.Distance')); ?></th>
                            <th><?php echo e(__('messages.Price')); ?></th>
                            <th><?php echo e(__('messages.Commission')); ?></th>
                            <th><?php echo e(__('messages.Status')); ?></th>
                            <th><?php echo e(__('messages.Payment')); ?></th>
                            <th><?php echo e(__('messages.Rating')); ?></th>
                            <th><?php echo e(__('messages.Complaints')); ?></th>
                            <th><?php echo e(__('messages.Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($order->id); ?></td>
                            <td>
                                <span class="font-weight-bold text-primary"><?php echo e($order->number ?? 'N/A'); ?></span>
                                <?php if($order->is_hybrid_payment): ?>
                                <br><span class="badge badge-info badge-sm">Hybrid</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($order->created_at->format('Y-m-d H:i')); ?></td>
                            <td>
                                <?php if($order->user): ?>
                                <a href="<?php echo e(route('users.show', $order->user_id)); ?>" class="text-decoration-none">
                                    <strong><?php echo e($order->user->name); ?></strong><br>
                                    <small class="text-muted"><?php echo e($order->user->phone ?? $order->user->email); ?></small>
                                </a>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('messages.Not_Available')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($order->driver): ?>
                                <a href="<?php echo e(route('drivers.show', $order->driver_id)); ?>" class="text-decoration-none">
                                    <strong><?php echo e($order->driver->name); ?></strong><br>
                                    <small class="text-muted"><?php echo e($order->driver->phone ?? $order->driver->email); ?></small>
                                </a>
                                <?php else: ?>
                                <span class="text-warning"><?php echo e(__('messages.Not_Assigned')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($order->service): ?>
                                <a href="<?php echo e(route('services.show', $order->service_id)); ?>" class="text-decoration-none">
                                    <?php echo e($order->service->name_en ?? $order->service->name); ?>

                                </a>
                                <?php else: ?>
                                <span class="text-muted"><?php echo e(__('messages.Not_Available')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="route-info">
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <i class="fas fa-map-marker-alt text-success"></i>
                                        <small class="mr-1"><?php echo e(Str::limit($order->pick_name, 18)); ?></small>
                                        <?php if($order->pick_lat && $order->pick_lng): ?>
                                        <a href="https://www.google.com/maps?q=<?php echo e($order->pick_lat); ?>,<?php echo e($order->pick_lng); ?>"
                                           target="_blank"
                                           class="btn btn-xs btn-outline-success py-0 px-1"
                                           title="<?php echo e(__('messages.View_On_Map')); ?>"
                                           style="font-size:0.65rem;border-radius:10px;">
                                            <i class="fas fa-map"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-center text-muted my-1">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <small class="mr-1"><?php echo e(Str::limit($order->drop_name, 18)); ?></small>
                                        <?php if($order->drop_lat && $order->drop_lng): ?>
                                        <a href="https://www.google.com/maps?q=<?php echo e($order->drop_lat); ?>,<?php echo e($order->drop_lng); ?>"
                                           target="_blank"
                                           class="btn btn-xs btn-outline-danger py-0 px-1"
                                           title="<?php echo e(__('messages.View_On_Map')); ?>"
                                           style="font-size:0.65rem;border-radius:10px;">
                                            <i class="fas fa-map"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info"><?php echo e($order->live_distance ?? 0); ?> km</span>
                            </td>
                            <td>
                                <div class="price-info">
                                    <?php if($order->discount_value > 0): ?>
                                    <div class="text-muted">
                                        <small><s>JD <?php echo e(number_format($order->total_price_before_discount, 2)); ?></s></small>
                                    </div>
                                    <div class="text-success font-weight-bold">
                                        JD <?php echo e(number_format($order->total_price_after_discount, 2)); ?>

                                    </div>
                                    <div>
                                        <span class="badge badge-warning">
                                            -JD <?php echo e(number_format($order->discount_value, 2)); ?> (<?php echo e($order->getDiscountPercentage()); ?>%)
                                        </span>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-success font-weight-bold">
                                        JD <?php echo e(number_format($order->total_price_after_discount, 2)); ?>

                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if($order->is_hybrid_payment): ?>
                                    <div class="mt-1">
                                        <small class="text-info">
                                            <i class="fas fa-wallet"></i> JD <?php echo e(number_format($order->wallet_amount_used, 2)); ?><br>
                                            <i class="fas fa-money-bill"></i> JD <?php echo e(number_format($order->cash_amount_due, 2)); ?>

                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="commission-info">
                                    <div class="text-primary">
                                        <small><?php echo e(__('messages.Driver')); ?>: JD <?php echo e(number_format($order->net_price_for_driver, 2)); ?></small>
                                    </div>
                                    <div class="text-info">
                                        <small><?php echo e(__('messages.Admin')); ?>: JD <?php echo e(number_format($order->commision_of_admin, 2)); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($order->getStatusClass()); ?>">
                                    <?php echo e($order->getStatusText()); ?>

                                </span>
                                <?php if($order->reason_for_cancel && $order->isCancelled()): ?>
                                <div class="mt-1">
                                    <small class="text-muted" title="<?php echo e($order->reason_for_cancel); ?>">
                                        <i class="fas fa-info-circle"></i> <?php echo e(__('messages.Reason_Available')); ?>

                                    </small>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="payment-info">
                                    <div>
                                        <span class="badge badge-primary"><?php echo e($order->getPaymentMethodText()); ?></span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge badge-<?php echo e($order->getPaymentStatusClass()); ?>">
                                            <?php echo e($order->getPaymentStatusText()); ?>

                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if($order->rating): ?>
                                <div>
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= $order->rating->rating): ?>
                                        <i class="fas fa-star text-warning"></i>
                                        <?php else: ?>
                                        <i class="far fa-star text-muted"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <br>
                                    <small class="text-muted">(<?php echo e($order->rating->rating); ?>/5)</small>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($order->complaints->count() > 0): ?>
                                <span class="badge badge-danger">
                                    <?php echo e($order->complaints->count()); ?>

                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group-vertical">
                                    <a href="<?php echo e(route('orders.show', $order->id)); ?>" class="btn btn-info btn-sm mb-1" title="<?php echo e(__('messages.View')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('orders.edit', $order->id)); ?>" class="btn btn-primary btn-sm mb-1" title="<?php echo e(__('messages.Edit')); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if(is_object($order->status) ? $order->status->value === 'driver_cancel_order' : $order->status === 'driver_cancel_order'): ?>
                                    <button type="button"
                                            class="btn btn-warning btn-sm mb-1"
                                            title="<?php echo e(__('messages.View_Notified_Drivers')); ?>"
                                            onclick="loadNotifiedDrivers(<?php echo e($order->id); ?>, '<?php echo e($order->number ?? $order->id); ?>')">
                                        <i class="fas fa-users"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if(method_exists($orders, 'links')): ?>
            <div class="d-flex justify-content-center">
                <?php echo e($orders->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .route-info {
        min-width: 120px;
    }
    
    .price-info {
        min-width: 120px;
    }
    
    .commission-info {
        min-width: 120px;
    }
    
    .payment-info {
        min-width: 100px;
    }
    
    .btn-group-vertical .btn {
        border-radius: 0.25rem !important;
        margin-bottom: 2px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    @media (max-width: 768px) {
        .btn-group-vertical {
            display: flex;
            flex-direction: row;
        }
        
        .btn-group-vertical .btn {
            margin-right: 2px;
            margin-bottom: 0;
        }
    }
</style>
<!-- Notified Drivers Modal -->
<div class="modal fade" id="notifiedDriversModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users mr-2"></i>
                    <?php echo e(__('messages.View_Notified_Drivers')); ?>

                    <span id="modal-order-number" class="text-muted ml-2" style="font-size:0.85em;"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="notified-drivers-loading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
                <div id="notified-drivers-content" style="display:none;">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th><?php echo e(__('messages.Driver')); ?></th>
                                <th><?php echo e(__('messages.Phone')); ?></th>
                                <th><?php echo e(__('messages.Distance')); ?></th>
                                <th><?php echo e(__('messages.Search_Radius')); ?></th>
                                <th><?php echo e(__('messages.Notified_At')); ?></th>
                            </tr>
                        </thead>
                        <tbody id="notified-drivers-tbody"></tbody>
                    </table>
                    <p id="no-drivers-msg" class="text-center text-muted" style="display:none;">
                        <?php echo e(__('messages.No_Drivers_Notified')); ?>

                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('messages.Close')); ?></button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('script'); ?>
<script>
function loadNotifiedDrivers(orderId, orderNumber) {
    document.getElementById('modal-order-number').textContent = '#' + orderNumber;
    document.getElementById('notified-drivers-loading').style.display = 'block';
    document.getElementById('notified-drivers-content').style.display = 'none';
    document.getElementById('no-drivers-msg').style.display = 'none';
    document.getElementById('notified-drivers-tbody').innerHTML = '';

    $('#notifiedDriversModal').modal('show');

    fetch('<?php echo e(url('admiinnnwmk/orders')); ?>/' + orderId + '/notified-drivers', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('notified-drivers-loading').style.display = 'none';
        document.getElementById('notified-drivers-content').style.display = 'block';

        const tbody = document.getElementById('notified-drivers-tbody');
        if (!data.drivers || data.drivers.length === 0) {
            document.getElementById('no-drivers-msg').style.display = 'block';
            return;
        }

        data.drivers.forEach((d, i) => {
            const row = `<tr>
                <td>${i + 1}</td>
                <td><a href="/admiinnnwmk/drivers/${d.id}" target="_blank">${d.name}</a></td>
                <td>${d.phone || '-'}</td>
                <td>${d.distance_km ? d.distance_km + ' km' : '-'}</td>
                <td>${d.search_radius_km ? d.search_radius_km + ' km' : '-'}</td>
                <td>${d.notified_at ? new Date(d.notified_at).toLocaleString() : '-'}</td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    })
    .catch(err => {
        document.getElementById('notified-drivers-loading').style.display = 'none';
        document.getElementById('notified-drivers-content').style.display = 'block';
        document.getElementById('notified-drivers-tbody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
    });
}
</script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\taxik\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>