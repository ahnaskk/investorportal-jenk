<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserUnderWriteFeeProcedure extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE user_under_write_fee_procedure (userId INT,endDate DATE)
        SELECT
        round(sum(MUV.under_writing_fee),4) as value
        FROM merchant_user_views    AS MUV
        where MUV.investor_id=userId AND MUV.created_at <= endDate AND MUV.active_status=1
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
        DROP PROCEDURE IF EXISTS `user_under_write_fee_procedure`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
