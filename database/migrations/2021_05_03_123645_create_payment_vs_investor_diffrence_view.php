<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentVsInvestorDiffrenceView extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE VIEW `payment_vs_investor_diffrence_view` AS
        SELECT
        PP.merchant_id,
        M.sub_status_id,
        participent_payment_id,
        PP.payment AS payment,
        sum(PI.participant_share) AS participant_share,
        M.funded AS funded,
        M.max_participant_fund AS max_participant_fund,
        round((M.max_participant_fund/M.funded)*100,2) AS percentage,
        round((PP.payment/(M.funded/M.max_participant_fund))-sum(PI.participant_share),2) AS diffrence
        FROM payment_investors  as PI
        JOIN participent_payments as PP ON PP.id=PI.participent_payment_id
        JOIN merchants as M ON M.id=PP.merchant_id
        WHERE M.sub_status_id = 1
        group by participent_payment_id
        having round((PP.payment/(M.funded/M.max_participant_fund))-sum(PI.participant_share),2) != 0
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
        DROP VIEW IF EXISTS `payment_vs_investor_diffrence_view`;
        SQL;
    }
}
