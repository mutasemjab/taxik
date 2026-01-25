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
        Schema::create('wallet_distributions', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_amount', 10, 2); // المبلغ الإجمالي (مثلاً 2 JD)
            $table->integer('number_of_orders'); // عدد الرحلات (مثلاً 4)
            $table->decimal('amount_per_order', 10, 2); // المبلغ لكل رحلة (2÷4 = 0.5)
            $table->tinyInteger('activate')->default(1); // 1 = مفعل، 0 = معطل
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
        Schema::dropIfExists('wallet_distributions');
    }
};
