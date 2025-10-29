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
        Schema::create('peak_time_releations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peak_time_id')->nullable();
            $table->foreign('peak_time_id')->references('id')->on('peak_times')->onDelete('cascade');
            $table->time('from_time');
            $table->time('to_time');
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
        Schema::dropIfExists('peak_time_releations');
    }
};
