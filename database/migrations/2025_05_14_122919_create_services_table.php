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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('photo');
            $table->double('start_price_morning')->default(0);
            $table->double('start_price_evening')->default(0);
            $table->double('price_per_km_morning')->default(0);
            $table->double('price_per_km_evening')->default(0);
            $table->double('admin_commision')->default(0);
            $table->tinyInteger('type_of_commision')->default(1); // 1 fixed // 2 percent
            $table->tinyInteger('is_electric')->default(1); // 1 yes // 2 no
            $table->tinyInteger('activate')->default(1); // 1 active // 2 not active
            $table->integer('capacity')->default(0);
            $table->double('waiting_time')->default(0);
            $table->double('cancellation_fee')->default(0);
            // الاعمدة المسؤولة عن انتظار السائق للراكب لحين نزوله
            $table->integer('free_waiting_minutes')->default(3);
            $table->double('waiting_charge_per_minute')->default(0);

            // العمود المسؤول عن كلفة وقت الانتظار خلال الرحلة
            $table->double('waiting_charge_per_minute_when_order_active')->default(0);

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
        Schema::dropIfExists('services');
    }
};
