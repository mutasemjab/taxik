<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::USeBootstrap();
        // Define morph map for polymorphic relationships (used by ReferralReward)
        // NOTE: Using morphMap (NOT enforceMorphMap) because Spatie's activity_log table stores
        // full class names (App\Models\User) in subject_type/causer_type columns.
        // enforceMorphMap would throw a RuntimeException on those records → crashes requests → user gets logged out.
        Relation::morphMap([
            'user'               => \App\Models\User::class,
            'driver'             => \App\Models\Driver::class,
            'order'              => \App\Models\Order::class,
            'admin'              => \App\Models\Admin::class,
            'setting'            => \App\Models\Setting::class,
            'coupon'             => \App\Models\Coupon::class,
            'wallet_transaction' => \App\Models\WalletTransaction::class,
            'service'            => \App\Models\Service::class,
        ]);
    }
}
