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
        // Define morph map for polymorphic relationships
        Relation::enforceMorphMap([
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
