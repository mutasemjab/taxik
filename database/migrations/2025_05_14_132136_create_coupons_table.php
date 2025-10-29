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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->double('discount');
            $table->double('minimum_amount')->default(0);
            $table->integer('number_of_used')->nullable();
            $table->tinyInteger('activate')->default(1); // 1 active // 2 dis active
            $table->tinyInteger('discount_type')->default(1); // 1 fixed // 2 percentage
            $table->tinyInteger('coupon_type')->default(1); // 1 all // 2 first ride // 3 for specific service
             $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
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
        Schema::dropIfExists('coupons');
    }
};
