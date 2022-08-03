<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFactorRateToMerchantsTable extends Migration
{
    public function up()
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->float('factor_rate', 8, 6)->change();
            $table->float('old_factor_rate', 8, 6)->change();
        });
    }

    public function down()
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->float('factor_rate', 8, 4)->change();
            $table->float('old_factor_rate', 8, 4)->change();
        });
    }
}
