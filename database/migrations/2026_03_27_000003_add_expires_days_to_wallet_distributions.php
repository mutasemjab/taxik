<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('wallet_distributions', function (Blueprint $table) {
            // عدد أيام صلاحية الرصيد بعد التوزيع (null = بلا صلاحية)
            $table->unsignedInteger('expires_days')->nullable()->after('activate')
                ->comment('Number of days before app credit expires after distribution. NULL = no expiry.');
        });
    }

    public function down()
    {
        Schema::table('wallet_distributions', function (Blueprint $table) {
            $table->dropColumn('expires_days');
        });
    }
};
