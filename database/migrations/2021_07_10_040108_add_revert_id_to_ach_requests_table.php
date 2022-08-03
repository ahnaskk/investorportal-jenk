<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevertIdToAchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ach_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('ach_requests', 'revert_id')) {
                $table->integer('revert_id')
                ->after('creator_id')
                ->nullable();
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
        Schema::table('ach_requests', function (Blueprint $table) {
            if (Schema::hasColumn('ach_requests', 'revert_id')) {
                $table->dropColumn('revert_id');
            }
        });
    }
}
