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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country_code')->default('+962');
             $table->string('sos_phone')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->integer('passenger_number')->nullable();
            $table->text('fcm_token')->nullable();
            $table->double('balance')->default(0);
            $table->tinyInteger('activate')->default(1); // 1 yes //2 no
            $table->tinyInteger('status')->default(1); // 1 on //2 off
            // other information 
            // car
            $table->string('photo_of_car')->nullable();
            $table->string('model')->nullable();
            $table->string('production_year')->nullable();
            $table->string('color')->nullable();
            $table->string('plate_number')->nullable();
            // other document
            $table->string('driving_license_front')->nullable();
            $table->string('driving_license_back')->nullable();
            $table->string('car_license_front')->nullable();
            $table->string('car_license_back')->nullable();
            $table->string('no_criminal_record')->nullable();

            $table->unsignedBigInteger('representive_id')->nullable();
            $table->foreign('representive_id')->references('id')->on('representives')->onDelete('cascade');
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
        Schema::dropIfExists('drivers');
    }
};
