<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="{{ asset('assets/admin/dist/img/AdminLTELogo.png') }}" alt="App Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Taksi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
              <h4 style="color: white; margin:auto;"> {{ auth()->user()->name }}</h4>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{ __('messages.dashboard') }}</p>
                    </a>
                </li>
                
                <!-- User Management Section -->
                @canany(['user-table', 'user-add', 'user-edit', 'user-delete', 'driver-table', 'driver-add', 'driver-edit', 'driver-delete'])
                <li class="nav-item {{ request()->is('admin/users*') || request()->is('admin/drivers*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            {{ __('messages.user_management') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @canany(['user-table', 'user-add', 'user-edit', 'user-delete'])
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                                <i class="far fa-user nav-icon"></i>
                                <p>{{ __('messages.users') }}</p>
                            </a>
                        </li>
                        @endcanany
                        
                        @canany(['driver-table', 'driver-add', 'driver-edit', 'driver-delete'])
                        <li class="nav-item">
                            <a href="{{ route('drivers.index') }}" class="nav-link {{ request()->routeIs('drivers.index') ? 'active' : '' }}">
                                <i class="fas fa-car nav-icon"></i>
                                <p>{{ __('messages.drivers') }}</p>
                            </a>
                        </li>
                        @endcanany
                    </ul>
                </li>
                @endcanany
                
                <!-- Services & Coupons -->
                @canany(['service-table', 'service-add', 'service-edit', 'service-delete', 'coupon-table', 'coupon-add', 'coupon-edit', 'coupon-delete'])
                <li class="nav-item {{ request()->is('admin/services*') || request()->is('admin/coupons*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-concierge-bell"></i>
                        <p>
                            {{ __('messages.service_management') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @canany(['service-table', 'service-add', 'service-edit', 'service-delete'])
                        <li class="nav-item">
                            <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.index') ? 'active' : '' }}">
                                <i class="fas fa-handshake nav-icon"></i>
                                <p>{{ __('messages.services') }}</p>
                            </a>
                        </li>
                        @endcanany
                        
                        @canany(['coupon-table', 'coupon-add', 'coupon-edit', 'coupon-delete'])
                        <li class="nav-item">
                            <a href="{{ route('coupons.index') }}" class="nav-link {{ request()->routeIs('coupons.index') ? 'active' : '' }}">
                                <i class="fas fa-ticket-alt nav-icon"></i>
                                <p>{{ __('messages.coupons') }}</p>
                            </a>
                        </li>
                        @endcanany
                    </ul>
                </li>
                @endcanany

                <!-- Notifications -->
                @canany(['order-table', 'order-add', 'order-edit', 'order-delete'])
                <li class="nav-item">
                    <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-car"></i>
                        <p>{{ __('messages.orders') }}</p>
                    </a>
                </li>
                @endcanany
            
                <!-- Notifications -->
                @canany(['notification-table', 'notification-add', 'notification-edit', 'notification-delete'])
                <li class="nav-item">
                    <a href="{{ route('notifications.create') }}" class="nav-link {{ request()->routeIs('notifications.create') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>{{ __('messages.notifications') }}</p>
                    </a>
                </li>
                @endcanany

                <!-- Content Management -->
                @canany(['page-table', 'page-add', 'page-edit', 'page-delete'])
                <li class="nav-item">
                    <a href="{{ route('pages.index') }}" class="nav-link {{ request()->routeIs('pages.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>{{ __('messages.pages') }}</p>
                    </a>
                </li>
                @endcanany
           
            

                   @canany(['wallet-table', 'wallet-add', 'wallet-edit', 'wallet-delete', 'withdrawal-table', 'withdrawal-add', 'withdrawal-edit', 'withdrawal-delete'])
                <li class="nav-item {{ request()->is('admin/users*') || request()->is('admin/drivers*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            {{ __('messages.wallet_management') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">


                        @canany(['wallet-table', 'wallet-add', 'wallet-edit', 'wallet-delete'])
                        <li class="nav-item">
                            <a href="{{ route('wallet_transactions.index') }}" class="nav-link {{ request()->routeIs('wallet_transactions.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p>{{ __('messages.wallets') }}</p>
                            </a>
                        </li>
                        @endcanany
                        
                        @canany(['withdrawal-table', 'withdrawal-add', 'withdrawal-edit', 'withdrawal-delete'])
                            <li class="nav-item">
                                <a href="{{ route('withdrawals.index') }}" class="nav-link {{ request()->routeIs('withdrawals.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>{{ __('messages.withdrawals') }}</p>
                                </a>
                            </li>
                        @endcanany


                    </ul>
                </li>
                @endcanany

                @canany(['banner-table', 'banner-add', 'banner-edit', 'banner-delete'])
                            <li class="nav-item">
                                <a href="{{ route('banners.index') }}" class="nav-link {{ request()->routeIs('banners.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>{{ __('messages.Banners') }}</p>
                                </a>
                            </li>
                @endcanany

                @canany(['rating-table', 'rating-add', 'rating-edit', 'rating-delete'])
                            <li class="nav-item">
                                <a href="{{ route('ratings.index') }}" class="nav-link {{ request()->routeIs('ratings.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>{{ __('messages.Ratings') }}</p>
                                </a>
                            </li>
                @endcanany
                @canany(['complaint-table', 'complaint-add', 'complaint-edit', 'complaint-delete'])
                            <li class="nav-item">
                                <a href="{{ route('complaints.index') }}" class="nav-link {{ request()->routeIs('complaints.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>{{ __('messages.complaints') }}</p>
                                </a>
                            </li>
                @endcanany
                @canany(['pos-table', 'pos-add', 'pos-edit', 'pos-delete'])
                            <li class="nav-item">
                                <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>{{ __('messages.pos_list') }}</p>
                                </a>
                            </li>
                @endcanany
               
                @canany(['card-table', 'card-add', 'card-edit', 'card-delete'])
                            <li class="nav-item">
                                <a href="{{ route('cards.index') }}" class="nav-link {{ request()->routeIs('cards.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>{{ __('messages.cards_list') }}</p>
                                </a>
                            </li>
                @endcanany

                <!-- System Settings -->
                <li class="nav-item {{ request()->is('admin/settings*') || request()->is('admin/roles*') || request()->is('admin/employees*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            {{ __('messages.system_settings') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('app-configs.index') }}" class="nav-link {{ request()->routeIs('app-configs.index') ? 'active' : '' }}">
                                <i class="fas fa-wrench nav-icon"></i>
                                <p>{{ __('messages.app_configurations') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                                <i class="fas fa-wrench nav-icon"></i>
                                <p>{{ __('messages.general_settings') }}</p>
                            </a>
                        </li>
                        
                        @canany(['role-table', 'role-add', 'role-edit', 'role-delete'])
                        <li class="nav-item">
                            <a href="{{ route('admin.role.index') }}" class="nav-link {{ request()->routeIs('admin.role.index') ? 'active' : '' }}">
                                <i class="fas fa-user-shield nav-icon"></i>
                                <p>{{ __('messages.roles') }}</p>
                            </a>
                        </li>
                        @endcanany
                        
                        @canany(['employee-table', 'employee-add', 'employee-edit', 'employee-delete'])
                        <li class="nav-item">
                            <a href="{{ route('admin.employee.index') }}" class="nav-link {{ request()->routeIs('admin.employee.index') ? 'active' : '' }}">
                                <i class="fas fa-user-tie nav-icon"></i>
                                <p>{{ __('messages.employees') }}</p>
                            </a>
                        </li>
                        @endcanany
                    </ul>
                </li>

                <!-- Account -->
                <li class="nav-item">
                    <a href="{{ route('admin.login.edit', auth()->user()->id) }}" class="nav-link {{ request()->routeIs('admin.login.edit') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>{{ __('messages.admin_account') }}</p>
                    </a>
                </li>

               
            </ul>
        </nav>
    </div>
</aside>