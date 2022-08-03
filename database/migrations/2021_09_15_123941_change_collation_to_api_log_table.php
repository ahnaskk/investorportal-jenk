<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCollationToApiLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_log', function (Blueprint $table) {
            DB::statement(' alter table api_log change response response  MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
            DB::statement('alter table api_log ADD mail_status TINYINT(1) NOT NULL DEFAULT 0 AFTER updated_at');
            DB::statement(' alter table api_log change request request  MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
            DB::statement(' update api_log SET mail_status=1');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_log', function (Blueprint $table) {
            

        });
    }
}
