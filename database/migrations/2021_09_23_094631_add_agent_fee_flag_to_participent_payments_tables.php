<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddAgentFeeFlagToParticipentPaymentsTables extends Migration
{
    public function up()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('participent_payments', 'agent_fee_percentage')) {
                $table->double('agent_fee_percentage', 16, 2)
                ->after('revert_id')
                ->default(0);
            }
        });
    }
    public function down()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (Schema::hasColumn('participent_payments', 'agent_fee_percentage')) {
                $table->dropColumn('agent_fee_percentage');
            }
        });
    }
}
