<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReCreateOldViewTables extends Migration
{
    public function up()
    {
        $path = base_path('database/sql/ViewTables.sql');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }

    private function down()
    {
    }
}
