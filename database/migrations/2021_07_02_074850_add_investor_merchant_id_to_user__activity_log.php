<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvestorMerchantIdToUserActivityLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_activity_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('user_activity_logs', 'investor_id')) {
                $table->integer('investor_id')->nullable();
            }
            if (! Schema::hasColumn('user_activity_logs', 'merchant_id')) {
                $table->integer('merchant_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_activity_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('user_activity_logs', 'investor_id')) {
                $table->dropColumn('investor_id');
            }
            if (! Schema::hasColumn('user_activity_logs', 'merchant_id')) {
                $table->dropColumn('merchant_id');
            }
        });
    }
}
