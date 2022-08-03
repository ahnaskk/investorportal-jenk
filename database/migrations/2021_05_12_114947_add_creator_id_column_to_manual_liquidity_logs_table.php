<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToManualLiquidityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_liquidity_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('manual_liquidity_logs', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('liquidity');
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
        Schema::table('manual_liquidity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('manual_liquidity_logs', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
