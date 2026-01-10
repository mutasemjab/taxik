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
        Schema::create('user_bans', function (Blueprint $table) {
            $table->id();
               $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('ban_reason');
            $table->text('ban_description')->nullable();
            $table->timestamp('banned_at');
            $table->timestamp('ban_until')->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('unbanned_at')->nullable();
            $table->foreignId('unbanned_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('unban_reason')->nullable();
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
        Schema::dropIfExists('user_bans');
    }
};
