<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Manager Role - Manage Users, Drivers, Orders, Services, Coupons
        $managerRole = Role::create([
            'name' => 'Manager',
            'guard_name' => 'admin'
        ]);

        $managerPermissions = [
            // Dashboard
            'dashboard-view',

            // System & Access Management
            'admin-table',
            'admin-add',
            'admin-edit',
            'admin-delete',

            'role-table',
            'role-add',
            'role-edit',
            'role-delete',

            'employee-table',
            'employee-add',
            'employee-edit',
            'employee-delete',

            // User Management
            'user-table',
            'user-add',
            'user-edit',
            'user-delete',

            // Driver Management
            'driver-table',
            'driver-add',
            'driver-edit',
            'driver-delete',

            // Orders Management
            'order-table',
            'order-add',
            'order-edit',
            'order-delete',

            // Services & Coupons
            'service-table',
            'service-add',
            'service-edit',
            'service-delete',

            'coupon-table',
            'coupon-add',
            'coupon-edit',
            'coupon-delete',

            // Wallet & Transactions
            'wallet-table',
            'wallet-add',
            'wallet-edit',
            'wallet-delete',

            'withdrawal-table',
            'withdrawal-add',
            'withdrawal-edit',
            'withdrawal-delete',
        ];

        $managerRole->syncPermissions(
            Permission::whereIn('name', $managerPermissions)
                ->where('guard_name', 'admin')
                ->get()
        );

        // Support Role - Handle Complaints, Ratings, Alerts
        $supportRole = Role::create([
            'name' => 'Support',
            'guard_name' => 'admin'
        ]);

        $supportPermissions = [
            // Dashboard (read-only)
            'dashboard-view',

            // Complaints
            'complaint-table',
            'complaint-edit',

            // Ratings
            'rating-table',

            // Driver Alerts
            'driver_alert-table',
            'driver_alert-edit',

            // View Users & Drivers (read-only)
            'user-table',
            'driver-table',
        ];

        $supportRole->syncPermissions(
            Permission::whereIn('name', $supportPermissions)
                ->where('guard_name', 'admin')
                ->get()
        );

        // Accountant Role - Handle Financial Operations
        $accountantRole = Role::create([
            'name' => 'Accountant',
            'guard_name' => 'admin'
        ]);

        $accountantPermissions = [
            // Dashboard
            'dashboard-view',

            // Wallet & Transactions (Full Access)
            'wallet-table',
            'wallet-add',
            'wallet-edit',
            'wallet-delete',

            // Withdrawals (Full Access)
            'withdrawal-table',
            'withdrawal-add',
            'withdrawal-edit',
            'withdrawal-delete',

            // Payments & Cards (View & Manage)
            'pos-table',
            'pos-add',
            'pos-edit',
            'pos-delete',

            'card-table',
            'card-add',
            'card-edit',
            'card-delete',

            // Reports (View & Export)
            'report-table',
            'report-view',
            'report-export',

            // View Users & Drivers (read-only)
            'user-table',
            'driver-table',
            'order-table',
        ];

        $accountantRole->syncPermissions(
            Permission::whereIn('name', $accountantPermissions)
                ->where('guard_name', 'admin')
                ->get()
        );

        // Content Manager Role - Manage Website Content
        $contentManagerRole = Role::create([
            'name' => 'Content Manager',
            'guard_name' => 'admin'
        ]);

        $contentManagerPermissions = [
            // Dashboard
            'dashboard-view',

            // Pages
            'page-table',
            'page-add',
            'page-edit',
            'page-delete',

            // Banners
            'banner-table',
            'banner-add',
            'banner-edit',
            'banner-delete',

            // Notifications
            'notification-table',
            'notification-add',
            'notification-edit',
            'notification-delete',

            // Settings (View & Edit)
            'setting-table',
            'setting-edit',

            // App Config
            'app-config-table',
            'app-config-add',
            'app-config-edit',
            'app-config-delete',
        ];

        $contentManagerRole->syncPermissions(
            Permission::whereIn('name', $contentManagerPermissions)
                ->where('guard_name', 'admin')
                ->get()
        );
    }
}