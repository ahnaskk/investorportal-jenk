<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipentPaymentViewsTable extends Migration
{
    public function up()
    {
        DB::statement($this->dropView());
        DB::statement($this->createView());
    }

    private function dropView(): string
    {
        return <<<'SQL'
        DROP VIEW IF EXISTS `participent_payment_views`;
        SQL;
    }

    private function createView(): string
    {
        return <<<'SQL'
        CREATE VIEW `participent_payment_views` AS
        SELECT
        T.id,
        T.payment_date AS date,
        IF(T.merchant_id,T.merchant_id,'') AS merchant_id,
        IF(T.merchant_id,M.name,'') AS Merchant,
        IF(IT.investor_id,IT.investor_id,'') AS investor_id,
        IF(IT.investor_id,U.name,'') AS Investor,
        IF(IT.investor_id,U.name,M.name) AS AccountHead,
        T.payment AS amount,
        IF(T.payment>=0,T.payment   ,0) AS credit,
        IF(T.payment<0 ,T.payment*-1,0) AS debit,
        T.status,
        T.payment_type,
        T.transaction_type,
        T.mode_of_payment,
        T.model,
        T.model_id,
        T.reason,
        T.creator_id,
        T.created_at,
        T.updated_at
        FROM `participent_payments`    AS T
        LEFT JOIN `merchants` AS M ON M.id = T.merchant_id
        LEFT JOIN `investor_transactions` AS IT ON IT.id = T.model_id
        LEFT JOIN `users` AS U ON U.id = IT.investor_id
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
