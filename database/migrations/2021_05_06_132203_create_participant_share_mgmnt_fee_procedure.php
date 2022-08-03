<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantShareMgmntFeeProcedure extends Migration
{
    private function createView(): string
    {
        return <<<'SQL'
        CREATE PROCEDURE participant_share_mgmnt_fee_procedure (userId INT)
        SELECT
        sum(participant_share) AS participant_share,
        sum(mgmnt_fee)         AS mgmnt_fee
        FROM payment_investors
        where user_id = userId
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
        DROP PROCEDURE IF EXISTS `participant_share_mgmnt_fee_procedure`;
        SQL;
    }

    public function down()
    {
        DB::statement($this->dropView());
    }
}
