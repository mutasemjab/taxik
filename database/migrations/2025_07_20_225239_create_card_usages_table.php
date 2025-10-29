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
        Schema::create('card_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->unsignedBigInteger('card_number_id')->nullable();
            $table->foreign('card_number_id')->references('id')->on('card_numbers')->onDelete('cascade');
            $table->unsignedBigInteger('wallet_transaction_id')->nullable();
            $table->foreign('wallet_transaction_id')->references('id')->on('wallet_transactions')->onDelete('cascade');
            $table->dateTime('used_at')->nullable();
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
        Schema::dropIfExists('card_usages');
    }
};
