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
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p><?php echo e(__('messages.dashboard')); ?></p>
                    </a>
                </li>
                
                <!-- User Management Section -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['user-table', 'user-add', 'user-edit', 'user-delete', 'driver-table', 'driver-add', 'driver-edit', 'driver-delete'])): ?>
                <li class="nav-item <?php echo e(request()->is('admin/users*') || request()->is('admin/drivers*') ? 'menu-open' : ''); ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            <?php echo e(__('messages.user_management')); ?>

                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['user-table', 'user-add', 'user-edit', 'user-delete'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('users.index')); ?>" class="nav-link <?php echo e(request()->routeIs('users.index') ? 'active' : ''); ?>">
                                <i class="far fa-user nav-icon"></i>
                                <p><?php echo e(__('messages.users')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['driver-table', 'driver-add', 'driver-edit', 'driver-delete'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('drivers.index')); ?>" class="nav-link <?php echo e(request()->routeIs('drivers.index') ? 'active' : ''); ?>">
                                <i class="fas fa-car nav-icon"></i>
                                <p><?php echo e(__('messages.drivers')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Services & Coupons -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['service-table', 'service-add', 'service-edit', 'service-delete', 'coupon-table', 'coupon-add', 'coupon-edit', 'coupon-delete'])): ?>
                <li class="nav-item <?php echo e(request()->is('admin/services*') || request()->is('admin/coupons*') ? 'menu-open' : ''); ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-concierge-bell"></i>
                        <p>
                            <?php echo e(__('messages.service_management')); ?>

                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['service-table', 'service-add', 'service-edit', 'service-delete'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('services.index')); ?>" class="nav-link <?php echo e(request()->routeIs('services.index') ? 'active' : ''); ?>">
                                <i class="fas fa-handshake nav-icon"></i>
                                <p><?php echo e(__('messages.services')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['coupon-table', 'coupon-add', 'coupon-edit', 'coupon-delete'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('coupons.index')); ?>" class="nav-link <?php echo e(request()->routeIs('coupons.index') ? 'active' : ''); ?>">
                                <i class="fas fa-ticket-alt nav-icon"></i>
                                <p><?php echo e(__('messages.coupons')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Notifications -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['order-table', 'order-add', 'order-edit', 'order-delete'])): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('orders.index')); ?>" class="nav-link <?php echo e(request()->routeIs('orders.index') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-car"></i>
                        <p><?php echo e(__('messages.orders')); ?></p>
                    </a>
                </li>
                <?php endif; ?>
            
                <!-- Notifications -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['notification-table', 'notification-add', 'notification-edit', 'notification-delete'])): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('notifications.create')); ?>" class="nav-link <?php echo e(request()->routeIs('notifications.create') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-bell"></i>
                        <p><?php echo e(__('messages.notifications')); ?></p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Content Management -->
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['page-table', 'page-add', 'page-edit', 'page-delete'])): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('pages.index')); ?>" class="nav-link <?php echo e(request()->routeIs('pages.index') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p><?php echo e(__('messages.pages')); ?></p>
                    </a>
                </li>
                <?php endif; ?>
           
            

                   <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['wallet-table', 'wallet-add', 'wallet-edit', 'wallet-delete', 'withdrawal-table', 'withdrawal-add', 'withdrawal-edit', 'withdrawal-delete'])): ?>
                <li class="nav-item <?php echo e(request()->is('admin/users*') || request()->is('admin/drivers*') ? 'menu-open' : ''); ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            <?php echo e(__('messages.wallet_management')); ?>

                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">


                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['wallet-table', 'wallet-add', 'wallet-edit', 'wallet-delete'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('wallet_transactions.index')); ?>" class="nav-link <?php echo e(request()->routeIs('wallet_transactions.index') ? 'active' : ''); ?>">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p><?php echo e(__('messages.wallets')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['withdrawal-table', 'withdrawal-add', 'withdrawal-edit', 'withdrawal-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('withdrawals.index')); ?>" class="nav-link <?php echo e(request()->routeIs('withdrawals.index') ? 'active' : ''); ?>">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p><?php echo e(__('messages.withdrawals')); ?></p>
                                </a>
                            </li>
                        <?php endif; ?>


                    </ul>
                </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['banner-table', 'banner-add', 'banner-edit', 'banner-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('banners.index')); ?>" class="nav-link <?php echo e(request()->routeIs('banners.index') ? 'active' : ''); ?>">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p><?php echo e(__('messages.Banners')); ?></p>
                                </a>
                            </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['rating-table', 'rating-add', 'rating-edit', 'rating-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('ratings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('ratings.index') ? 'active' : ''); ?>">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p><?php echo e(__('messages.Ratings')); ?></p>
                                </a>
                            </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['complaint-table', 'complaint-add', 'complaint-edit', 'complaint-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('complaints.index')); ?>" class="nav-link <?php echo e(request()->routeIs('complaints.index') ? 'active' : ''); ?>">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p><?php echo e(__('messages.complaints')); ?></p>
                                </a>
                            </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['pos-table', 'pos-add', 'pos-edit', 'pos-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('pos.index')); ?>" class="nav-link <?php echo e(request()->routeIs('pos.index') ? 'active' : ''); ?>">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p><?php echo e(__('messages.pos_list')); ?></p>
                                </a>
                            </li>
                <?php endif; ?>
               
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['card-table', 'card-add', 'card-edit', 'card-delete'])): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('cards.index')); ?>" class="nav-link <?php echo e(request()->routeIs('cards.index') ? 'active' : ''); ?>">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p><?php echo e(__('messages.cards_list')); ?></p>
                                </a>
                            </li>
                <?php endif; ?>

                <!-- System Settings -->
                <li class="nav-item <?php echo e(request()->is('admin/settings*') || request()->is('admin/roles*') || request()->is('admin/employees*') ? 'menu-open' : ''); ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            <?php echo e(__('messages.system_settings')); ?>

                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?php echo e(route('app-configs.index')); ?>" class="nav-link <?php echo e(request()->routeIs('app-configs.index') ? 'active' : ''); ?>">
                                <i class="fas fa-wrench nav-icon"></i>
                                <p><?php echo e(__('messages.app_configurations')); ?></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo e(route('settings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('settings.index') ? 'active' : ''); ?>">
                                <i class="fas fa-wrench nav-icon"></i>
                                <p><?php echo e(__('messages.general_settings')); ?></p>
                            </a>
                        </li>
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['role-table', 'role-add', 'role-edit', 'role-delete'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.role.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.role.index') ? 'active' : ''); ?>">
                                <i class="fas fa-user-shield nav-icon"></i>
                                <p><?php echo e(__('messages.roles')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['employee-table', 'employee-add', 'employee-edit', 'employee-delete'])): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('admin.employee.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.employee.index') ? 'active' : ''); ?>">
                                <i class="fas fa-user-tie nav-icon"></i>
                                <p><?php echo e(__('messages.employees')); ?></p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Account -->
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.login.edit', auth()->user()->id)); ?>" class="nav-link <?php echo e(request()->routeIs('admin.login.edit') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p><?php echo e(__('messages.admin_account')); ?></p>
                    </a>
                </li>

               
            </ul>
        </nav>
    </div>
</aside><?php /**PATH C:\xampp\htdocs\taksi\resources\views/admin/includes/sidebar.blade.php ENDPATH**/ ?>