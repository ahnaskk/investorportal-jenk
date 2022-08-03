<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquityInvestorReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equity_investor_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('investor_id');
            $table->string('name');
            $table->double('liquidity', 16,2);
            $table->double('credit_amount', 16,2);
            $table->double('ctd', 16,2);
            $table->double('fees', 16,2);
            $table->double('tinvest_rtr', 16,2);
            $table->double('default_pay_rtr', 16,2);
            $table->double('overpayment', 16,2);
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
        Schema::dropIfExists('equity_investor_reports');
    }
}
