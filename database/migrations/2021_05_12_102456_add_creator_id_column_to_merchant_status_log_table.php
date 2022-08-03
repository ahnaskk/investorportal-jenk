<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToMerchantStatusLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_status_log', function (Blueprint $table) {
            if (! Schema::hasColumn('merchant_status_log', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('old_status');
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
        Schema::table('merchant_status_log', function (Blueprint $table) {
            if (Schema::hasColumn('merchant_status_log', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
