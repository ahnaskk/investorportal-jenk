<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCtdProcedure extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE user_ctd_procedure (userId INT,endDate DATE)
        SELECT
        round(sum(PI.participant_share-PI.mgmnt_fee),4) as value
        FROM payment_investors    AS PI
        JOIN participent_payments AS PP ON PP.id = PI.participent_payment_id
        JOIN merchants AS M ON M.id = PI.merchant_id
        where PI.user_id=userId AND PP.created_at <= endDate AND M.last_status_updated_date >= endDate
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
        DROP PROCEDURE IF EXISTS `user_ctd_procedure`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
