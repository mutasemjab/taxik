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
        Schema::create('order_spam', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('status')->default('user_cancel_order'); 
            $table->string('payment_method');
            $table->string('status_payment');
            $table->double('total_price_before_discount', 8, 2)->default(0);
            $table->double('total_price_after_discount', 8, 2)->default(0);
            $table->double('net_price_for_driver', 8, 2)->default(0);
            $table->double('commision_of_admin', 8, 2)->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->double('pick_lat', 10, 8);
            $table->double('pick_lng', 11, 8);
            $table->string('pick_name');
            $table->string('drop_name')->nullable();
            $table->double('drop_lat', 10, 8)->nullable();
            $table->double('drop_lng', 11, 8)->nullable();
            $table->string('estimated_time')->nullable();
            $table->timestamp('trip_started_at')->nullable();
            $table->timestamp('trip_completed_at')->nullable();
            $table->integer('actual_trip_duration_minutes')->nullable();
            $table->string('reason_for_cancel')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('original_order_id')->nullable();
            $table->timestamps();
            
            $table->index('original_order_id');
            $table->index(['user_id', 'status']);
            $table->index(['service_id']);
            $table->index(['driver_id']);
            $table->index('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_spam');
    }
};
