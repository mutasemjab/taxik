<?php $__env->startSection('title'); ?>
    <?php echo e(__('dashboard.home')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/dashboard.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contentheaderlink'); ?>
    <a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(__('dashboard.home')); ?></a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('contentheaderactive'); ?>
    <?php echo e(__('dashboard.view')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="db-wrap">

    
    <div class="db-header">
        <div class="row align-items-center">
            <div class="col">
                <h1><?php echo e(__('dashboard.main_dashboard')); ?></h1>
                <p><?php echo e(__('dashboard.comprehensive_statistics')); ?></p>
            </div>
            <div class="col-auto db-header-right">
                <div class="live-badge">
                    <span class="dot"></span>
                    <?php echo e(__('dashboard.online_now')); ?>: <strong><?php echo e($activeDriversNow); ?></strong> <?php echo e(__('dashboard.drivers')); ?>

                </div>
                <div class="db-date mt-2">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <?php echo e(\Carbon\Carbon::now()->format('l, d M Y')); ?>

                </div>
            </div>
        </div>
    </div>

    
    <div class="db-stats-row">

        
        <div class="stat-card c-primary">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-coins"></i></div>
                <span class="stat-badge <?php echo e($earningsGrowth >= 0 ? 'up' : 'down'); ?>">
                    <i class="fas fa-arrow-<?php echo e($earningsGrowth >= 0 ? 'up' : 'down'); ?>"></i>
                    <?php echo e(abs($earningsGrowth)); ?>%
                </span>
            </div>
            <div class="stat-label"><?php echo e(__('dashboard.total_earnings_today')); ?></div>
            <div class="stat-value">JD <?php echo e(number_format($todayEarnings, 2)); ?></div>
            <div class="stat-sub"><i class="fas fa-clock mr-1"></i><?php echo e(__('dashboard.from_yesterday')); ?></div>
        </div>

        
        <div class="stat-card c-success">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                <span class="stat-badge <?php echo e($usersGrowth >= 0 ? 'up' : 'down'); ?>">
                    <i class="fas fa-arrow-<?php echo e($usersGrowth >= 0 ? 'up' : 'down'); ?>"></i>
                    <?php echo e(abs($usersGrowth)); ?>%
                </span>
            </div>
            <div class="stat-label"><?php echo e(__('dashboard.total_users')); ?></div>
            <div class="stat-value"><?php echo e(number_format($usersCount)); ?></div>
            <div class="stat-sub">
                <i class="fas fa-user-plus mr-1 text-success"></i>
                +<?php echo e($newUsersToday); ?> <?php echo e(__('dashboard.new_users_today')); ?>

            </div>
        </div>

        
        <div class="stat-card c-warning">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-car"></i></div>
                <span class="stat-badge <?php echo e($driversGrowth >= 0 ? 'up' : 'down'); ?>">
                    <i class="fas fa-arrow-<?php echo e($driversGrowth >= 0 ? 'up' : 'down'); ?>"></i>
                    <?php echo e(abs($driversGrowth)); ?>%
                </span>
            </div>
            <div class="stat-label"><?php echo e(__('dashboard.total_drivers')); ?></div>
            <div class="stat-value"><?php echo e(number_format($driversCount)); ?></div>
            <div class="stat-sub">
                <i class="fas fa-circle mr-1" style="color:#2ecc71;font-size:.6rem;"></i>
                <?php echo e(__('dashboard.active_drivers')); ?>: <?php echo e($activeDriversToday); ?>

            </div>
        </div>

        
        <div class="stat-card c-danger">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-route"></i></div>
                <span class="stat-badge <?php echo e($ordersGrowth >= 0 ? 'up' : 'down'); ?>">
                    <i class="fas fa-arrow-<?php echo e($ordersGrowth >= 0 ? 'up' : 'down'); ?>"></i>
                    <?php echo e(abs($ordersGrowth)); ?>%
                </span>
            </div>
            <div class="stat-label"><?php echo e(__('dashboard.orders_today')); ?></div>
            <div class="stat-value"><?php echo e(number_format($todayOrders)); ?></div>
            <div class="stat-sub">
                <i class="fas fa-check-circle mr-1" style="color:#2ecc71;"></i><?php echo e($completedOrdersToday); ?>

                &nbsp;|&nbsp;
                <i class="fas fa-clock mr-1" style="color:#f39c12;"></i><?php echo e($pendingOrdersToday); ?>

                &nbsp;|&nbsp;
                <i class="fas fa-times-circle mr-1" style="color:#e74c3c;"></i><?php echo e($canceledOrdersToday); ?>

            </div>
        </div>

        
        <div class="stat-card c-info">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-percentage"></i></div>
                <span class="stat-badge neu"><i class="fas fa-chart-line"></i></span>
            </div>
            <div class="stat-label"><?php echo e(__('dashboard.admin_commission_today')); ?></div>
            <div class="stat-value">JD <?php echo e(number_format($adminCommissionToday, 2)); ?></div>
            <div class="stat-sub"><?php echo e(__('dashboard.from_total_orders')); ?></div>
        </div>

        
        <div class="stat-card c-purple sms-card">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-sms"></i></div>
            </div>
            <div class="stat-label">رسائل OTP المتبقية</div>
            <div class="stat-value" id="sms-balance-value">—</div>
            <div class="stat-sub" id="sms-balance-label">جاري التحميل...</div>
        </div>

    </div>

    
    <div class="db-two-col">

        
        <div class="db-card">
            <div class="db-card-header">
                <div class="title"><i class="fas fa-chart-bar"></i> <?php echo e(__('dashboard.monthly_statistics')); ?></div>
                <span style="font-size:.8rem;color:var(--text-muted);"><?php echo e(\Carbon\Carbon::now()->format('M Y')); ?></span>
            </div>
            <div class="monthly-grid">
                <div class="mini-stat-box">
                    <div class="msb-icon">💵</div>
                    <div class="msb-val">JD<?php echo e(number_format($monthlyEarnings, 0)); ?></div>
                    <div class="msb-lbl"><?php echo e(__('dashboard.monthly_earnings')); ?></div>
                </div>
                <div class="mini-stat-box">
                    <div class="msb-icon">📋</div>
                    <div class="msb-val"><?php echo e(number_format($monthlyOrders)); ?></div>
                    <div class="msb-lbl"><?php echo e(__('dashboard.monthly_orders')); ?></div>
                </div>
                <div class="mini-stat-box">
                    <div class="msb-icon">✅</div>
                    <div class="msb-val"><?php echo e($completionRate); ?>%</div>
                    <div class="msb-lbl"><?php echo e(__('dashboard.completion_rate')); ?></div>
                </div>
            </div>
            
            <div class="earnings-grid mt-2">
                <div class="earning-item">
                    <div class="ei-icon">🏆</div>
                    <div class="ei-label"><?php echo e(__('dashboard.total_earnings_all')); ?></div>
                    <div class="ei-value">JD<?php echo e(number_format($totalEarnings, 0)); ?></div>
                    <div class="ei-sub"><?php echo e(__('dashboard.since_beginning')); ?></div>
                </div>
                <div class="earning-item">
                    <div class="ei-icon">👨‍✈️</div>
                    <div class="ei-label"><?php echo e(__('dashboard.drivers_earnings_today')); ?></div>
                    <div class="ei-value">JD<?php echo e(number_format($driversEarningsToday, 2)); ?></div>
                    <div class="ei-sub"><?php echo e(__('dashboard.net_drivers_earnings')); ?></div>
                </div>
                <div class="earning-item">
                    <div class="ei-icon">📊</div>
                    <div class="ei-label"><?php echo e(__('dashboard.average_order_value')); ?></div>
                    <div class="ei-value">JD<?php echo e(number_format($averageOrderValue, 2)); ?></div>
                    <div class="ei-sub"><?php echo e(__('dashboard.for_this_month')); ?></div>
                </div>
            </div>
        </div>

        
        <div class="db-card">
            <div class="db-card-header">
                <div class="title"><i class="fas fa-tachometer-alt"></i> <?php echo e(__('dashboard.quick_statistics')); ?></div>
            </div>
            <div class="qm-list">
                <div class="qm-item">
                    <div class="qm-left">
                        <div class="qm-icon">🚗</div>
                        <div>
                            <div class="qm-title"><?php echo e(__('dashboard.active_driver_now')); ?></div>
                            <div class="qm-sub"><?php echo e(__('dashboard.online_now')); ?></div>
                        </div>
                    </div>
                    <div class="qm-value"><?php echo e($activeDriversNow); ?></div>
                </div>
                <div class="qm-item">
                    <div class="qm-left">
                        <div class="qm-icon">⏱️</div>
                        <div>
                            <div class="qm-title"><?php echo e(__('dashboard.average_delivery_time')); ?></div>
                            <div class="qm-sub"><?php echo e(__('dashboard.minutes')); ?></div>
                        </div>
                    </div>
                    <div class="qm-value"><?php echo e($averageOrderTime); ?></div>
                </div>
                <div class="qm-item">
                    <div class="qm-left">
                        <div class="qm-icon">❌</div>
                        <div>
                            <div class="qm-title"><?php echo e(__('dashboard.canceled_orders_today')); ?></div>
                            <div class="qm-sub"><?php echo e(__('dashboard.today')); ?></div>
                        </div>
                    </div>
                    <div class="qm-value" style="color:#e74c3c;"><?php echo e($canceledOrdersToday); ?></div>
                </div>
                <div class="qm-item">
                    <div class="qm-left">
                        <div class="qm-icon">🔄</div>
                        <div>
                            <div class="qm-title"><?php echo e(__('dashboard.active_drivers')); ?> <?php echo e(__('dashboard.today')); ?></div>
                            <div class="qm-sub"><?php echo e(__('messages.Completed')); ?> <?php echo e(__('dashboard.orders')); ?></div>
                        </div>
                    </div>
                    <div class="qm-value" style="color:#2ecc71;"><?php echo e($activeDriversToday); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="db-two-col">

        
        <div class="db-card">
            <div class="db-card-header">
                <div class="title"><i class="fas fa-list-alt"></i> آخر الطلبات</div>
                <a href="<?php echo e(route('orders.index')); ?>">عرض الكل <i class="fas fa-arrow-left fa-xs"></i></a>
            </div>
            <?php if($recentOrders->isEmpty()): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block" style="color:#d1d5db;"></i>
                    لا توجد طلبات
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>المستخدم</th>
                            <th>الحالة</th>
                            <th>السعر</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $sv = is_object($order->status) ? $order->status->value : $order->status;
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo e(route('orders.show', $order->id)); ?>" class="order-id">
                                    #<?php echo e($order->number ?? $order->id); ?>

                                </a>
                            </td>
                            <td>
                                <?php if($order->user): ?>
                                <div class="user-cell">
                                    <div class="user-avatar"><?php echo e(strtoupper(substr($order->user->name ?? 'U', 0, 1))); ?></div>
                                    <div>
                                        <div class="user-name"><?php echo e($order->user->name); ?></div>
                                        <div class="user-phone"><?php echo e($order->user->phone); ?></div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="s-badge <?php echo e($sv); ?>"><?php echo e(__('messages.' . ucwords(str_replace('_', ' ', $sv)))); ?></span></td>
                            <td style="font-weight:700;font-size:.85rem;">JD <?php echo e(number_format($order->total_price_after_discount, 2)); ?></td>
                            <td style="font-size:.78rem;color:var(--text-muted);"><?php echo e($order->created_at->format('d/m H:i')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        
        <div class="db-card">
            <div class="db-card-header">
                <div class="title"><i class="fas fa-trophy"></i> أفضل السائقين اليوم</div>
                <a href="<?php echo e(route('drivers.index')); ?>">عرض الكل <i class="fas fa-arrow-left fa-xs"></i></a>
            </div>
            <?php if($topDriversToday->isEmpty()): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-car fa-2x mb-2 d-block" style="color:#d1d5db;"></i>
                    لا يوجد سائقون نشطون اليوم
                </div>
            <?php else: ?>
            <div class="driver-list">
                <?php $__currentLoopData = $topDriversToday; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="driver-item">
                    <div class="driver-rank <?php echo e($i === 0 ? 'r1' : ($i === 1 ? 'r2' : ($i === 2 ? 'r3' : ''))); ?>">
                        <?php echo e($i + 1); ?>

                    </div>
                    <?php if($driver->photo): ?>
                        <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->photo)); ?>"
                             class="driver-avatar" alt="<?php echo e($driver->name); ?>">
                    <?php else: ?>
                        <div class="driver-avatar-ph"><?php echo e(strtoupper(substr($driver->name, 0, 1))); ?></div>
                    <?php endif; ?>
                    <div class="driver-info">
                        <div class="driver-name"><?php echo e($driver->name); ?></div>
                        <div class="driver-phone"><?php echo e($driver->phone); ?></div>
                    </div>
                    <div class="driver-trips">
                        <div class="trips-num"><?php echo e($driver->trips_count); ?></div>
                        <div class="trips-lbl"><?php echo e(__('dashboard.orders')); ?></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch('<?php echo e(route('admin.sms-balance')); ?>')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('sms-balance-value').textContent = data.balance;
                document.getElementById('sms-balance-label').textContent = 'رسالة متبقية';
            } else {
                document.getElementById('sms-balance-value').textContent = '—';
                document.getElementById('sms-balance-label').textContent = data.error ?? 'غير متاح';
            }
        })
        .catch(() => {
            document.getElementById('sms-balance-value').textContent = '—';
            document.getElementById('sms-balance-label').textContent = 'تعذر الاتصال';
        });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\taxik\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>