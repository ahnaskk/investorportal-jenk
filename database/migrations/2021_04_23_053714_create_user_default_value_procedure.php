<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDefaultValueProcedure extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE user_default_value_procedure (userId INT,endDate DATE)
        SELECT
        round(sum(MUV.invest_rtr),4) as total_default_rtr,
        round(sum((MUV.invest_rtr*((MUV.mgmnt_fee)/100))),4) as total_default_fee,
        round(sum(((MUV.invest_rtr+IF(MUV.old_factor_rate>factor_rate,(MUV.amount*(MUV.old_factor_rate-MUV.factor_rate)),0))-(MUV.invest_rtr*(MUV.mgmnt_fee)/100+IF(MUV.old_factor_rate>MUV.factor_rate,(MUV.amount*(MUV.old_factor_rate-MUV.factor_rate)*(MUV.mgmnt_fee)/100),0))-(IF(MUV.actual_paid_participant_ishare-MUV.paid_mgmnt_fee,MUV.actual_paid_participant_ishare-MUV.paid_mgmnt_fee,0)))),4) as total_rtr
        FROM merchant_user_views    AS MUV
        where MUV.investor_id=userId AND MUV.last_payment_date <= endDate AND MUV.active_status=1 AND MUV.sub_status_id IN (4,22)
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
        DROP PROCEDURE IF EXISTS `user_default_value_procedure`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
