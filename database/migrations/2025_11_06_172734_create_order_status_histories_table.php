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
        Schema::create('order_status_histories', function (Blueprint $table) {
           $table->id();
           $table->unsignedBigInteger('order_id');
           $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
           $table->string('status');
           $table->timestamp('changed_at');
           $table->unsignedBigInteger('changed_by')->nullable(); // driver_id or user_id
           $table->string('changed_by_type')->nullable(); // 'driver' or 'user'
            
           // Index for fast queries
           $table->index(['order_id', 'status']);
           $table->index(['order_id', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_status_histories');
    }
};
