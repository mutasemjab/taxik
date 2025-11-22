<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->double('value')->default(1);
            $table->timestamps();
        });
        DB::table('settings')->insert([
            ['key' => "minimum_money_in_wallet_driver_to_get_order", 'value' => 1],
            ['key' => "calculate_price_depend_on_time_or_distance_or_both", 'value' => 3], // 1 time // 2 distance // 3 both
            ['key' => "commission_of_admin", 'value' => 1], // percentage
            ['key' => "times_that_driver_cancel_orders_in_one_day", 'value' => 2], // fixed
            ['key' => "fee_when_driver_cancel_order_more_times", 'value' => 0.5], // fixed
            ['key' => "new_user_register_add_balance", 'value' => 0], // fixed
            ['key' => "new_driver_register_add_balance", 'value' => 0], // fixed
            ['key' => "find_drivers_in_radius", 'value' => 5], // km
            ['key' => "maximum_radius_to_find_drivers", 'value' => 20], // km
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
