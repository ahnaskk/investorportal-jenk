<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgentFeeToMerchantUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_user', function (Blueprint $table) {
            if (! Schema::hasColumn('merchant_user', 'total_agent_fee')) {
                $table->double('total_agent_fee', 16, 2)
                ->default(0);
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
        Schema::table('merchant_user', function (Blueprint $table) {
            if (Schema::hasColumn('merchant_user', 'total_agent_fee')) {
                $table->dropColumn('total_agent_fee');
            }
        });
    }
}
