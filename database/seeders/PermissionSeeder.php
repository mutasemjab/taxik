<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate(); // optional

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permissions_admin = [
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

            // User & Driver Management
            'user-table',
            'user-add',
            'user-edit',
            'user-delete',

            'driver-table',
            'driver-add',
            'driver-edit',
            'driver-delete',

            'representive-table',
            'representive-add',
            'representive-edit',
            'representive-delete',

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

            // Content Management
            'page-table',
            'page-add',
            'page-edit',
            'page-delete',

            'banner-table',
            'banner-add',
            'banner-edit',
            'banner-delete',

            // Support & Reviews
            'rating-table',
            'rating-add',
            'rating-edit',
            'rating-delete',

            'complaint-table',
            'complaint-add',
            'complaint-edit',
            'complaint-delete',

            'driver_alert-table',
            'driver_alert-add',
            'driver_alert-edit',
            'driver_alert-delete',

            // Payments & Cards
            'pos-table',
            'pos-add',
            'pos-edit',
            'pos-delete',

            'card-table',
            'card-add',
            'card-edit',
            'card-delete',

            'countryCharge-table',
            'countryCharge-add',
            'countryCharge-edit',
            'countryCharge-delete',

            // Notifications
            'notification-table',
            'notification-add',
            'notification-edit',
            'notification-delete',

            // Settings & Configuration
            'setting-table',
            'setting-edit',

            'app-config-table',
            'app-config-add',
            'app-config-edit',
            'app-config-delete',
    
            'challenge-table',
            'challenge-add',
            'challenge-edit',
            'challenge-delete',

            // Reports
            'report-table',
            'report-view',
            'report-export',

            
        ];

        $data = array_map(function ($permission) {
            return ['name' => $permission, 'guard_name' => 'admin'];
        }, $permissions_admin);

        Permission::insert($data);
    }
}
