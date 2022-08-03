<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualRTRBalanceLogsTable extends Migration
{
    public function up()
    {
        Schema::create('manual_r_t_r_balance_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('user_id');
            $table->float('rtr_balance', 16, 4)->default(0);
            $table->float('rtr_balance_default', 16, 4)->default(0);
            $table->float('total', 16, 4)->default(0);
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manual_r_t_r_balance_logs');
    }
}
