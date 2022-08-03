<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToAchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ach_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('ach_requests', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('response');
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
            if (Schema::hasColumn('ach_requests', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
