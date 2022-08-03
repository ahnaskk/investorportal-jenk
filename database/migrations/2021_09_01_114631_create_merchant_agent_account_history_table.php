<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantAgentAccountHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_agent_account_history', function (Blueprint $table) {
            $table->bigIncrements('id');
                $table->integer('merchant_id');                
                $table->dateTime('start_date')->default(null)->nullable();
                $table->dateTime('end_date')->default(null)->nullable();                
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_agent_account_history');
    }
}
