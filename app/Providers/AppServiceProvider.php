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
            'user' => 'App\Models\User',
            'driver' => 'App\Models\Driver',
            'order' => 'App\Models\Order',
            'admin' => 'App\Models\Admin',
            'setting' => 'App\Models\Setting',
            'setting' => 'App\Models\Coupon',
            'setting' => 'App\Models\WalletTransaction',

        ]);
    
    }
}
