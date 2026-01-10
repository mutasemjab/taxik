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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en');
            $table->text('description_ar');
            $table->string('challenge_type'); // 'referral', 'trips', 'spending'
            $table->integer('target_count'); // العدد المطلوب (مثال: 5 أشخاص، 8 رحلات)
            $table->double('reward_amount'); // المكافأة بالدينار
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_completions_per_user')->default(1); // عدد المرات المسموح بها لكل مستخدم
            $table->string('icon')->nullable(); // أيقونة التحدي
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
        Schema::dropIfExists('challenges');
    }
};
