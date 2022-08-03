<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionViewsTable extends Migration
{
    public function up()
    {
        DB::statement($this->dropView());
        DB::statement($this->createView());
    }

    private function dropView(): string
    {
        return <<<'SQL'
        DROP VIEW IF EXISTS `transaction_views`;
        SQL;
    }

    private function createView(): string
    {
        return <<<'SQL'
        CREATE VIEW `transaction_views` AS
        SELECT 
        T.id,
        T.date,
        T.merchant_id,
        M.name   AS Merchant,
        T.amount AS credit,
        0        AS debit,
        T.status,
        T.model,
        T.model_id,
        T.created_by,
        T.updated_by,
        T.created_at,
        T.updated_at
        FROM `transactions`    AS T
        INNER JOIN `merchants` AS M ON M.id = T.merchant_id
        WHERE amount >= 0
        UNION
        SELECT 
        T.id,
        T.date,
        T.merchant_id,
        M.name      AS Merchant,
        0           AS credit,
        T.amount*-1 AS debit,
        T.status,
        T.model,
        T.model_id,
        T.created_by,
        T.updated_by,
        T.created_at,
        T.updated_at
        FROM `transactions`    AS T
        INNER JOIN `merchants` AS M ON M.id = T.merchant_id
        WHERE amount < 0
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
