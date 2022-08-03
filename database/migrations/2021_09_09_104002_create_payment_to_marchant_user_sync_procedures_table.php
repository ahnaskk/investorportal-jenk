<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentToMarchantUserSyncProceduresTable extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE payment_to_marchant_user_sync_procedure (merchantId INT)
        SELECT
        PI.user_id,
        sum(PI.participant_share) AS participant_share,
        sum(PI.mgmnt_fee) AS mgmnt_fee,
        sum(PI.agent_fee) AS agent_fee,
        sum(PI.principal) AS principal,
        sum(PI.profit) AS profit
        FROM payment_investors AS PI
        where PI.merchant_id=merchantId
        GROUP BY PI.user_id
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
        DROP PROCEDURE IF EXISTS payment_to_marchant_user_sync_procedure;
        SQL;
    }
    
    public function down()
    {
        DB::statement($this->dropView());
    }
}
