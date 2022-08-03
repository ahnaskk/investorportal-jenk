<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfitAndPrincipleUpdateProceduresTable extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE profit_and_principle_update_procedure (participentPaymentId INT)
        SELECT
        PI.id,
        PI.user_id,
        PI.actual_participant_share,
        PI.mgmnt_fee,
        PI.agent_fee,
        PI.merchant_id,
        overpayment,
        MUV.invest_rtr,
        MUV.user_balance_amount as balance,
        MUV.total_investment as invested_amount,
        (actual_participant_share-PI.mgmnt_fee)-((actual_participant_share-PI.mgmnt_fee)*(MUV.total_investment)/(MUV.invest_rtr-(MUV.mgmnt_fee/100)*MUV.invest_rtr)) as profit_value1
        FROM payment_investors AS PI
        JOIN merchant_user_views AS MUV  ON MUV.merchant_id = PI.merchant_id AND MUV.investor_id=PI.user_id
        WHERE PI.participent_payment_id=participentPaymentId
        AND PI.actual_participant_share != 0
        AND MUV.invest_rtr != 0
        SQL;
    }
    
    public function up()
    {
        DB::statement($this->dropView());
        DB::statement($this->createView());
    }
    
    private function dropView(): string
    {
        return <<<'SQL'
        DROP PROCEDURE IF EXISTS profit_and_principle_update_procedure;
        SQL;
    }
    
    public function down()
    {
        DB::statement($this->dropView());
    }
}
