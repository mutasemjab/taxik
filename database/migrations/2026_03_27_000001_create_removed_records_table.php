<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('removed_records', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['user', 'driver']); // who was deleted
            $table->unsignedBigInteger('original_id');
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('country_code')->nullable();
            $table->string('email')->nullable();
            $table->decimal('balance', 10, 2)->nullable();
            $table->string('photo')->nullable();
            $table->tinyInteger('activate')->nullable();
            // Driver-specific fields
            $table->string('model')->nullable();           // car model
            $table->string('plate_number')->nullable();
            $table->string('color')->nullable();
            $table->string('production_year')->nullable();
            // Snapshot of the full original row
            $table->longText('full_data')->nullable();     // JSON of all fields
            // Who deleted & why
            $table->unsignedBigInteger('deleted_by_admin_id')->nullable();
            $table->string('delete_reason')->nullable();
            $table->timestamp('deleted_at_original')->nullable(); // when the real record was deleted
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('removed_records');
    }
};
