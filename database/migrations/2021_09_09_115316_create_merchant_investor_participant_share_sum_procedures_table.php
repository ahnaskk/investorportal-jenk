<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantInvestorParticipantShareSumProceduresTable extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE merchant_investor_participant_share_sum_procedures (merchantId INT,userId INT)
        SELECT
        sum(PI.participant_share) AS value
        FROM payment_investors AS PI
        where PI.merchant_id =merchantId
        AND PI.user_id       =userId
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
        DROP PROCEDURE IF EXISTS merchant_investor_participant_share_sum_procedures;
        SQL;
    }
    
    public function down()
    {
        DB::statement($this->dropView());
    }
}
