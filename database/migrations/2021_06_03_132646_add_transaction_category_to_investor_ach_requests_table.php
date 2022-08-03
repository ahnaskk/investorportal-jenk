<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTransactionCategoryToInvestorAchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investor_ach_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('investor_ach_requests', 'transaction_category')) {
                $table->tinyInteger('transaction_category')->nullable()->after('transaction_method');
            }
        });
        if (Schema::hasColumn('investor_ach_requests', 'transaction_category')) {
            DB::statement("UPDATE `investor_ach_requests` SET `transaction_category` = 4 WHERE (`transaction_type` = 'credit' AND `transaction_category` IS NULL) OR (`transaction_type` = 'same_day_credit' AND `transaction_category` IS NULL)");
            DB::statement("UPDATE `investor_ach_requests` SET `transaction_category` = 1 WHERE (`transaction_type` = 'debit' AND `transaction_category` IS NULL) OR (`transaction_type` = 'same_day_debit' AND `transaction_category` IS NULL)");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('investor_ach_requests', function (Blueprint $table) {
            if (Schema::hasColumn('investor_ach_requests', 'transaction_category')) {
                $table->dropColumn('transaction_category');
            }
        });
    }
}
