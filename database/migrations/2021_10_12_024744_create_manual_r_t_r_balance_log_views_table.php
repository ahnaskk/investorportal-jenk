<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualRTRBalanceLogViewsTable extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE VIEW `manual_r_t_r_balance_log_views` AS
        SELECT 
        MLL.date,
        MLL.user_id as investor_id,
        U.name AS Investor,
        U.company as company_id,
        C.name AS Company,
        MLL.rtr_balance,
        MLL.rtr_balance_default,
        MLL.total,
        MLL.details,
        MLL.creator_id,
        MLL.created_at
        FROM `manual_r_t_r_balance_logs` AS MLL
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
        DROP VIEW IF EXISTS `manual_r_t_r_balance_log_views`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
