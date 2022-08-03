<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualLiquidityLogsTable extends Migration
{
    public function up()
    {
        Schema::create('manual_liquidity_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('user_id');
            $table->float('liquidity', 16, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manual_liquidity_logs');
    }
}
