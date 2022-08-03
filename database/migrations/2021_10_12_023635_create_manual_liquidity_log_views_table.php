<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualLiquidityLogViewsTable extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE VIEW `manual_liquidity_log_views` AS
        SELECT 
        MLL.date,
        MLL.user_id as investor_id,
        U.name AS Investor,
        U.company as company_id,
        C.name AS Company,
        MLL.liquidity,
        MLL.creator_id,
        MLL.created_at
        FROM `manual_liquidity_logs` AS MLL
        INNER JOIN `users`  AS U ON U.id  = MLL.user_id
        INNER JOIN `users`  AS C ON C.id  = U.company
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
        DROP VIEW IF EXISTS `manual_liquidity_log_views`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
