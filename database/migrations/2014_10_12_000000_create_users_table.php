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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country_code')->default('+962');
            $table->string('sos_phone')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->text('fcm_token')->nullable();
            $table->double('balance')->default(0);
            $table->text('referral_code')->nullable();
            $table->tinyInteger('activate')->default(1); // 1 yes //2 no
            $table->unsignedBigInteger('user_id')->nullable();
            // new 
            $table->decimal('app_credit', 10, 2)->default(0);
            $table->decimal('app_credit_amount_per_order', 10, 2)->default(0);
            $table->integer('app_credit_orders_remaining')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
