<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMgmntFeeProcedure extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE user_mgmnt_fee_procedure (userId INT,endDate DATE)
        SELECT
        round(sum(MUV.invest_rtr*(MUV.mgmnt_fee/100)),4) as value
        FROM merchant_user_views    AS MUV
        where MUV.investor_id=userId AND MUV.active_status=1 AND MUV.created_at <= endDate AND MUV.last_status_updated_date >= endDate
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
        DROP PROCEDURE IF EXISTS `user_mgmnt_fee_procedure`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
