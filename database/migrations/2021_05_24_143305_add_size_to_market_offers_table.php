<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSizeToMarketOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('market_offers', function (Blueprint $table) {
            DB::statement('ALTER TABLE market_offers CHANGE type type VARCHAR(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('market_offers', function (Blueprint $table) {
            //
        });
    }
}
