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
         Schema::create('app_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            
            $table->decimal('amount', 10, 2)->default(0);
            $table->tinyInteger('type_of_transaction')->default(1); // 1 = add, 2 = withdrawal
            $table->text('note')->nullable();
            
            // معلومات إضافية عن التوزيع
            $table->integer('orders_remaining_before')->nullable(); // عدد الرحلات قبل العملية
            $table->integer('orders_remaining_after')->nullable();  // عدد الرحلات بعد العملية
            $table->decimal('amount_per_order', 10, 2)->nullable(); // المبلغ المخصص لكل رحلة
            
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
        Schema::dropIfExists('app_credit_transactions');
    }
};
