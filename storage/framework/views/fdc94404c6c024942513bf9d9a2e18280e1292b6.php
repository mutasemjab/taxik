<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="brand-link">
        <img src="<?php echo e(asset('assets/admin/dist/img/AdminLTELogo.png')); ?>" alt="App Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Taksi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <h4 style="color: white; margin:auto;"> <?php echo e(auth()->user()->name); ?></h4>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <!-- Dashboard -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('dashboard-view')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.dashboard')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p><?php echo e(__('messages.dashboard')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('dashboard-view')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('map')); ?>" class="nav-link <?php echo e(request()->routeIs('map') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p><?php echo e(__('messages.live_map')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- User Management Section -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['user-table', 'user-add', 'user-edit', 'user-delete', 'driver-table', 'driver-add',
                    'driver-edit', 'driver-delete', 'representive-table', 'representive-add', 'representive-edit',
                    'representive-delete'])): ?>
                    <li
                        class="nav-item <?php echo e(request()->is('admin/users*') || request()->is('admin/drivers*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                <?php echo e(__('messages.user_management')); ?>

                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('representive-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('representives.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('representives.index') ? 'active' : ''); ?>">
                                        <i class="far fa-representive nav-icon"></i>
                                        <p><?php echo e(__('messages.Representatives')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('user-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('users.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('users.index') ? 'active' : ''); ?>">
                                        <i class="far fa-user nav-icon"></i>
                                        <p><?php echo e(__('messages.users')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('driver-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('drivers.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('drivers.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-id-card nav-icon"></i>
                                        <p><?php echo e(__('messages.drivers')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Services & Coupons -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['service-table', 'service-add', 'service-edit', 'service-delete', 'coupon-table', 'coupon-add',
                    'coupon-edit', 'coupon-delete'])): ?>
                    <li
                        class="nav-item <?php echo e(request()->is('admin/services*') || request()->is('admin/coupons*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-concierge-bell"></i>
                            <p>
                                <?php echo e(__('messages.service_management')); ?>

                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('service-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('services.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('services.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-taxi nav-icon"></i>
                                        <p><?php echo e(__('messages.services')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('coupon-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('coupons.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('coupons.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-ticket-alt nav-icon"></i>
                                        <p><?php echo e(__('messages.coupons')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Orders -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('order-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('orders.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('orders.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p><?php echo e(__('messages.orders')); ?></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('spam-orders.index')); ?>">
                            <i class="fas fa-fw fa-trash-alt"></i>
                            <span><?php echo e(__('messages.Spam_Orders')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Challenges -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('challenge-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('challenges.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('challenges.*') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-trophy"></i>
                            <p><?php echo e(__('messages.Challenges')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Notifications -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('notification-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('notifications.create')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('notifications.create') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-bell"></i>
                            <p><?php echo e(__('messages.notifications')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Content Management -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('page-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('pages.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('pages.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p><?php echo e(__('messages.pages')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Wallet Management -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['wallet-table', 'wallet-add', 'wallet-edit', 'wallet-delete', 'withdrawal-table',
                    'withdrawal-add', 'withdrawal-edit', 'withdrawal-delete'])): ?>
                    <li
                        class="nav-item <?php echo e(request()->is('admin/wallet_transactions*') || request()->is('admin/withdrawals*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-wallet"></i>
                            <p>
                                <?php echo e(__('messages.wallet_management')); ?>

                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('wallet-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('wallet_transactions.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('wallet_transactions.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-money-bill-wave nav-icon"></i>
                                        <p><?php echo e(__('messages.wallets')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                       
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('distribution-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('wallet-distributions.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('wallet-distributions.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-money-bill-wave nav-icon"></i>
                                        <p><?php echo e(__('messages.wallet_distributions')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('withdrawal-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('withdrawals.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('withdrawals.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-hand-holding-usd nav-icon"></i>
                                        <p><?php echo e(__('messages.withdrawals')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Banners -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('banner-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('banners.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('banners.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-images"></i>
                            <p><?php echo e(__('messages.Banners')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Driver Alerts -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('driver_alert-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.driver_alerts.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('admin.driver_alerts.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-exclamation-triangle"></i>
                            <p><?php echo e(__('messages.driver_alerts')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Ratings -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rating-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('ratings.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('ratings.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-star"></i>
                            <p><?php echo e(__('messages.Ratings')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Complaints -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('complaint-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('complaints.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('complaints.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-comment-alt"></i>
                            <p><?php echo e(__('messages.complaints')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- POS -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pos-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('pos.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('pos.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p><?php echo e(__('messages.pos_list')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Cards -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('card-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('cards.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('cards.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-credit-card"></i>
                            <p><?php echo e(__('messages.cards_list')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('countryCharge-table')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('country-charges.index')); ?>"
                            class="nav-link <?php echo e(request()->routeIs('country-charges.index') ? 'active' : ''); ?>">
                            <i class="nav-icon fas fa-credit-card"></i>
                            <p><?php echo e(__('messages.Country Charges')); ?></p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Reports Section -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['report-table', 'report-view', 'report-export', 'financial-reports-list',
                    'financial-reports-export'])): ?>
                    <li
                        class="nav-item <?php echo e(request()->is('admin/reports*') || request()->is('admin/financial-reports*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>
                                <?php echo e(__('messages.reports')); ?>

                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report-view')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('reports.order-status-history')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('reports.order-status-history') || request()->routeIs('admin.reports.order-status-detail') ? 'active' : ''); ?>">
                                        <i class="fas fa-history nav-icon"></i>
                                        <p><?php echo e(__('messages.Order_Status_History')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report-view')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('reports.driver-acceptance')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('reports.driver-acceptance') ? 'active' : ''); ?>">
                                        <i class="fas fa-history nav-icon"></i>
                                        <p><?php echo e(__('messages.driver-acceptance')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Financial Reports Submenu -->
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('financial-reports-list')): ?>
                                <li class="nav-item <?php echo e(request()->is('admin/financial-reports*') ? 'menu-open' : ''); ?>">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-dollar-sign"></i>
                                        <p>
                                            <?php echo e(__('messages.Financial_Reports')); ?>

                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="<?php echo e(route('financial-reports.index')); ?>"
                                                class="nav-link <?php echo e(request()->routeIs('financial-reports.index') || request()->routeIs('financial-reports.driver-details') ? 'active' : ''); ?>">
                                                <i class="fas fa-users nav-icon"></i>
                                                <p><?php echo e(__('messages.Drivers_Financial_Reports')); ?></p>
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a href="<?php echo e(route('financial-reports.pos-report')); ?>"
                                                class="nav-link <?php echo e(request()->routeIs('financial-reports.pos-report') ? 'active' : ''); ?>">
                                                <i class="fas fa-store nav-icon"></i>
                                                <p><?php echo e(__('messages.POS_Financial_Report')); ?></p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- System Settings -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['admin-table', 'admin-add', 'admin-edit', 'admin-delete', 'app-config-table', 'app-config-add',
                    'app-config-edit', 'app-config-delete', 'setting-table', 'setting-edit', 'role-table', 'role-add',
                    'role-edit', 'role-delete', 'employee-table', 'employee-add', 'employee-edit', 'employee-delete'])): ?>
                    <li
                        class="nav-item <?php echo e(request()->is('admin/settings*') || request()->is('admin/admin*') || request()->is('admin/app-configs*') || request()->is('admin/roles*') || request()->is('admin/employees*') ? 'menu-open' : ''); ?>">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                <?php echo e(__('messages.system_settings')); ?>

                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.admin.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('admin.admin.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-shield-alt nav-icon"></i>
                                        <p><?php echo e(__('messages.Admins')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('app-config-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('app-configs.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('app-configs.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-mobile-alt nav-icon"></i>
                                        <p><?php echo e(__('messages.app_configurations')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setting-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('settings.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('settings.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-sliders-h nav-icon"></i>
                                        <p><?php echo e(__('messages.general_settings')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if(
                                $user->can('activityLog-table') ||
                                    $user->can('activityLog-add') ||
                                    $user->can('activityLog-edit') ||
                                    $user->can('activityLog-delete')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('activity-logs.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('activity-logs.*') ? 'active' : ''); ?>">
                                        <i class="far fa-id-badge nav-icon"></i>
                                        <p><?php echo e(__('messages.Activity Logs')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('role-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.role.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('admin.role.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-user-shield nav-icon"></i>
                                        <p><?php echo e(__('messages.roles')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employee-table')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.employee.index')); ?>"
                                        class="nav-link <?php echo e(request()->routeIs('admin.employee.index') ? 'active' : ''); ?>">
                                        <i class="fas fa-user-tie nav-icon"></i>
                                        <p><?php echo e(__('messages.employees')); ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Account -->
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.login.edit', auth()->user()->id)); ?>"
                        class="nav-link <?php echo e(request()->routeIs('admin.login.edit') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p><?php echo e(__('messages.admin_account')); ?></p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
<?php /**PATH C:\xampp\htdocs\taxik\resources\views/admin/includes/sidebar.blade.php ENDPATH**/ ?>