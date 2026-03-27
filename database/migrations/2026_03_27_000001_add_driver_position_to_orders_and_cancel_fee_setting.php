<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add driver's location at time of acceptance to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('driver_accepted_lat', 10, 7)->nullable()->after('drop_lng');
            $table->decimal('driver_accepted_lng', 10, 7)->nullable()->after('driver_accepted_lat');
        });

        // Add new setting: fee to charge user when cancelling with driver more than halfway
        $exists = DB::table('settings')->where('key', 'fee_when_user_cancel_with_driver_halfway')->exists();
        if (!$exists) {
            DB::table('settings')->insert([
                'key'        => 'fee_when_user_cancel_with_driver_halfway',
                'value'      => 1.0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['driver_accepted_lat', 'driver_accepted_lng']);
        });

        DB::table('settings')->where('key', 'fee_when_user_cancel_with_driver_halfway')->delete();
    }
};
