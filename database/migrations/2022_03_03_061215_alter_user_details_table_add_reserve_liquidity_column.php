<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserDetailsTableAddReserveLiquidityColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'reserved_liquidity_amount')) {
                $table->double('reserved_liquidity_amount',18,2)
                ->after('liquidity')
                ->default(0);
            }
            if (! Schema::hasColumn('user_details', 'initial_reserved_amount')) {
                $table->double('initial_reserved_amount',18,2)
                ->after('reserved_liquidity_amount')
                ->default(0);
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
        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'reserved_liquidity_amount')) {
                $table->dropColumn('reserved_liquidity_amount');
            }
            if (Schema::hasColumn('users', 'initial_reserved_amount')) {
                $table->dropColumn('initial_reserved_amount');
            }
        });
    }
}
