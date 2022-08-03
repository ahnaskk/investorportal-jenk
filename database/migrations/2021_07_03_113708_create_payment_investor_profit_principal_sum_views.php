<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentInvestorProfitPrincipalSumViews extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE VIEW payment_investor_profit_principal_sum_view AS
        SELECT
        payment_investors.user_id,
        sum(participant_share) as participant_share,
        sum(mgmnt_fee) as mgmnt_fee,
        sum(principal) as principal,
        sum(profit) as profit,
        round(sum((participant_share-mgmnt_fee-principal-profit)),2) as net_effect
        FROM `payment_investors`
        JOIN merchants on merchants.id=merchant_id
        where merchants.label in (1,2)
        AND merchants.sub_status_id NOT IN (4,18,19,20,22)
        GROUP BY payment_investors.user_id
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
        DROP VIEW IF EXISTS `payment_investor_profit_principal_sum_view`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
