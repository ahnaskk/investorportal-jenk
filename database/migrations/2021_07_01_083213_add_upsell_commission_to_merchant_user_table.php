<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpsellCommissionToMerchantUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_user', function (Blueprint $table) {

            if (! Schema::hasColumn('merchant_user', 'up_sell_commission_per')) {
                $table->double('up_sell_commission_per', 8, 4)
                ->after('commission_amount')
                ->default(0);
            }
           
            if (! Schema::hasColumn('merchant_user', 'up_sell_commission')) {
                $table->double('up_sell_commission', 12, 4)
                ->after('up_sell_commission_per')
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
            if (Schema::hasColumn('merchant_user', 'up_sell_commission')) {
                $table->dropColumn('up_sell_commission');
            }
            if (Schema::hasColumn('merchant_user', 'up_sell_commission_per')) {
                $table->dropColumn('up_sell_commission_per');
            }
        });
    }
}
