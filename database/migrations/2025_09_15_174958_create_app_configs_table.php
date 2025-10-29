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
        Schema::create('app_configs', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('google_play_link_user_app')->nullable();
            $table->string('google_play_link_driver_app')->nullable();
            $table->string('app_store_link_user_app')->nullable();
            $table->string('app_store_link_driver_app')->nullable();
            $table->string('hawawi_link_user_app')->nullable();
            $table->string('hawawi_link_driver_app')->nullable();
            $table->string('min_version_google_play_user_app')->nullable();
            $table->string('min_version_google_play_driver_app')->nullable();
            $table->string('min_version_app_store_user_app')->nullable();
            $table->string('min_version_app_store_driver_app')->nullable();
            $table->string('min_version_hawawi_user_app')->nullable();
            $table->string('min_version_hawawi_driver_app')->nullable();
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
        Schema::dropIfExists('app_configs');
    }
};
