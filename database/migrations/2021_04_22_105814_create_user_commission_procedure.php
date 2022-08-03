<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCommissionProcedure extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE user_commission_procedure (userId INT,endDate DATE)
        SELECT
        round(sum(MU.commission_amount),4) as value
        FROM merchant_user    AS MU
        JOIN merchants        AS M  ON M.id = MU.merchant_id
        where MU.user_id=userId AND M.created_at <= endDate AND M.active_status=1
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
        DROP PROCEDURE IF EXISTS `user_commission_procedure`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
