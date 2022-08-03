<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToMerchantStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_statements', function (Blueprint $table) {
            if (! Schema::hasColumn('merchant_statements', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('to_date');
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
        Schema::table('merchant_statements', function (Blueprint $table) {
            if (Schema::hasColumn('merchant_statements', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
