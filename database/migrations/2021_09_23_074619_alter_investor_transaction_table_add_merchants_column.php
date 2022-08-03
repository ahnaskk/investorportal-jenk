<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvestorTransactionTableAddMerchantsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investor_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('investor_transactions', 'merchant_id')) {
                $table->integer('merchant_id')
                ->after('investor_id')
                ->nullable();
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
        Schema::table('investor_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('investor_transactions', 'merchant_id')) {
                $table->dropColumn('merchant_id');
            }
        });
    }
}
