<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultToMerchantBankAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_bank_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('merchant_bank_accounts', 'default_credit')) {
                $table->tinyInteger('default_credit')
                ->after('bank_name')
                ->nullable();
            }
            if (! Schema::hasColumn('merchant_bank_accounts', 'default_debit')) {
                $table->tinyInteger('default_debit')
                ->after('default_credit')
                ->nullable();
            }
            if (! Schema::hasColumn('merchant_bank_accounts', 'type')) {
                $table->text('type')
                ->after('default_debit')
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
        Schema::table('merchant_bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('merchant_bank_accounts', 'default_credit')) {
                $table->dropColumn('default_credit');
            }
            if (Schema::hasColumn('merchant_bank_accounts', 'default_debit')) {
                $table->dropColumn('default_debit');
            }
            if (Schema::hasColumn('merchant_bank_accounts', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
}
