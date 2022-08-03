<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionIdToInvestorAchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investor_ach_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('investor_ach_requests', 'transaction_id')) {
                $table->bigInteger('transaction_id')
                ->after('order_id')
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
        Schema::table('investor_ach_requests', function (Blueprint $table) {
            if (Schema::hasColumn('investor_ach_requests', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
        });
    }
}
