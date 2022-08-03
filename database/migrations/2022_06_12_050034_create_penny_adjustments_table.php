<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePennyAdjustmentsTable extends Migration
{
    public function up()
    {
        DB::statement($this->dropView());
        DB::statement($this->createView());
    }

    private function dropView(): string
    {
        return <<<'SQL'
        DROP VIEW IF EXISTS `penny_diffrence_principal_and_profit`;
        SQL;
    }

    private function createView(): string
    {
        return <<<'SQL'
        CREATE VIEW `penny_diffrence_principal_and_profit` AS
        SELECT 
        PI.merchant_id,
        PI.user_id,
        M.complete_percentage,
        round(sum(IF(PI.participant_share,PI.participant_share,0)),4) as participant_share,
        round(sum(IF(PI.mgmnt_fee,PI.mgmnt_fee,0)),4) as mgmnt_fee,
        round(sum(IF(PI.principal,PI.principal,0)),4) as principal,
        round(sum(IF(PI.profit,PI.profit,0)),4) as profit,
        round(sum(IF(PI.profit,PI.profit,0))+sum(IF(PI.principal,PI.principal,0))+sum(IF(PI.mgmnt_fee,PI.mgmnt_fee,0))-sum(IF(PI.participant_share,PI.participant_share,0)),4) as diff,
        sum(IF(PI.principal,PI.principal,0))-MUV.total_investment as principal_diff
        FROM `payment_investors`         as PI
        INNER JOIN `merchants`           AS M ON M.id = PI.merchant_id
        INNER JOIN `merchant_user_views` AS MUV ON MUV.investor_id = PI.user_id AND MUV.merchant_id = PI.merchant_id
        WHERE M.complete_percentage < 100
        GROUP BY PI.merchant_id,PI.user_id
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
