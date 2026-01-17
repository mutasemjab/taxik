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
        Schema::create('order_drivers_notified', function (Blueprint $table) {
             $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('search_radius_km')->nullable();
            $table->boolean('notified')->default(true);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['order_id', 'driver_id']);
            
            $table->index(['order_id']);
            $table->index(['driver_id']);
            $table->index(['notified_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_driver_notifieds');
    }
};
