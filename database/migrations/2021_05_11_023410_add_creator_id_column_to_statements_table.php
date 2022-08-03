<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statements', function (Blueprint $table) {
            if (! Schema::hasColumn('statements', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('to_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statements', function (Blueprint $table) {
            if (Schema::hasColumn('statements', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
