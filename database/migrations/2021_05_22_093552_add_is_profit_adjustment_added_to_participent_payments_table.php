<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsProfitAdjustmentAddedToParticipentPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('participent_payments', 'is_profit_adjustment_added')) {
                $table->tinyInteger('is_profit_adjustment_added')->default(1)
                ->after('mode_of_payment');
            }
        });
    }

    public function down()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (Schema::hasColumn('participent_payments', 'is_profit_adjustment_added')) {
                $table->dropColumn(['is_profit_adjustment_added']);
            }
        });
    }
}
