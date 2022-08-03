<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCreditProcedure extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE user_credit_procedure (userId INT,endDate DATE)
        SELECT
        round(sum(IT.amount),4) as value
        FROM investor_transactions    AS IT
        where IT.investor_id=userId AND IT.created_at <= endDate
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
        DROP PROCEDURE IF EXISTS `user_credit_procedure`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
