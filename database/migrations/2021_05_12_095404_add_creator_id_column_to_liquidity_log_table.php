<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToLiquidityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('liquidity_log', function (Blueprint $table) {
            if (! Schema::hasColumn('liquidity_log', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('member_type');
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
        Schema::table('liquidity_log', function (Blueprint $table) {
            if (Schema::hasColumn('liquidity_log', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
