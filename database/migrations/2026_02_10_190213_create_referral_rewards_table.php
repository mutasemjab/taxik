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
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id'); // The one who referred
            $table->string('referrer_type'); // 'user' or 'driver'
            $table->unsignedBigInteger('referred_id'); // The one who was referred
            $table->string('referred_type'); // 'user' or 'driver'
            $table->integer('orders_completed')->default(0); // Track orders by referred user
            $table->boolean('reward_paid')->default(false); // Has reward been paid?
            $table->decimal('reward_amount', 10, 2)->nullable(); // Amount paid
            $table->timestamp('reward_paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['referrer_id', 'referrer_type']);
            $table->index(['referred_id', 'referred_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_rewards');
    }
};
