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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->text('number')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');

            $table->string('estimated_time')->nullable();
            $table->string('pick_name');
            $table->double('pick_lat');
            $table->double('pick_lng');
   
            $table->string('drop_name')->nullable();
            $table->double('drop_lat')->nullable();
            $table->double('drop_lng')->nullable();

            $table->double('total_price_before_discount');
            $table->double('discount_value')->nullable();
            $table->double('total_price_after_discount');
            $table->double('net_price_for_driver');
            $table->double('commision_of_admin');
            $table->timestamp('trip_started_at')->nullable();
            $table->timestamp('trip_completed_at')->nullable();
            $table->double('actual_trip_duration_minutes')->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'on_the_way',
                'started',
                'waiting_payment',
                'completed',
                'user_cancel_order',
                'driver_cancel_order',
                'arrived',
            ])->default('pending');
    
            $table->text('reason_for_cancel')->nullable();

            $table->enum('payment_method', ['cash', 'visa', 'wallet'])
                  ->default('cash');

            // Change status_payment to ENUM
            $table->enum('status_payment', ['pending', 'paid'])
                  ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
