<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgentFeeToPaymentInvestorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_investors', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_investors', 'agent_fee')) {
                $table->double('agent_fee', 16, 2)
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
        Schema::table('payment_investors', function (Blueprint $table) {
            if (Schema::hasColumn('payment_investors', 'agent_fee')) {
                $table->dropColumn('agent_fee');
            }
        });
    }
}
