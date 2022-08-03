<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToManualRtrBalanceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_r_t_r_balance_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('manual_rtr_balance_logs', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('details');
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
        Schema::table('manual_r_t_r_balance_logs', function (Blueprint $table) {
            if (Schema::hasColumn('manual_rtr_balance_logs', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
