<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantRequestMoneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_request_money', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id')->default(0);
            $table->text('source')->nullable();
            $table->text('merchant_ip')->nullable();            
            $table->tinyInteger('status')->default(1);
            $table->double('amount', 16,2);
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
        Schema::dropIfExists('merchant_request_money');
    }
}
