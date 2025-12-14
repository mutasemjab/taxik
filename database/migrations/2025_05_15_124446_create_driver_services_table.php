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
        Schema::create('driver_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->tinyInteger('status')->default(1); // 1 active // 2 dis active
              // 1 = primary (mandatory, can't be disabled by driver)
            // 2 = optional (driver can toggle on/off)
            // Note: If a service is not in driver_services table at all, it's "unavailable"
            $table->tinyInteger('service_type')->default(2)->comment('1=primary(mandatory), 2=optional(can toggle)');
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
        Schema::dropIfExists('driver_services');
    }
};
