<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentInvestorsViewsTable extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE VIEW `payment_investors_views` AS
        SELECT
        PI.id,
        PI.merchant_id,
        MUV.Merchant,
        PI.user_id,
        MUV.Investor,
        PP.payment_date,
        PP.payment,
        PP.final_participant_share,
        PI.investment_id,
        PI.participent_payment_id,
        PI.participant_share,
        PI.actual_participant_share,
        PI.mgmnt_fee,
        PI.participant_share-PI.mgmnt_fee AS net_amount,
        PI.syndication_fee,
        PI.actual_overpayment,
        PI.overpayment,
        PI.balance,
        PI.principal,
        PI.profit
        FROM payment_investors    AS PI
        JOIN participent_payments AS PP  ON PP.id=PI.participent_payment_id
        JOIN merchant_user_views  AS MUV ON MUV.id=PI.investment_id
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
        DROP VIEW IF EXISTS `payment_investors_views`;
        SQL;
    }
}
