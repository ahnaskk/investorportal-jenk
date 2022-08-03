<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToReassignHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reassign_history', function (Blueprint $table) {
            if (! Schema::hasColumn('reassign_history', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('type');
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
        Schema::table('reassign_history', function (Blueprint $table) {
            if (Schema::hasColumn('reassign_history', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
