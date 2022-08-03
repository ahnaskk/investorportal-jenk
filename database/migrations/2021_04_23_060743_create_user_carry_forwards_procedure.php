<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCarryForwardsProcedure extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE user_carry_forwards_procedure (userId INT,endDate DATE)
        SELECT
        round(sum(CF.amount),4) as value
        FROM carry_forwards    AS CF
        where CF.investor_id=userId AND Cf.date <= endDate;
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
        DROP PROCEDURE IF EXISTS `user_carry_forwards_procedure`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
