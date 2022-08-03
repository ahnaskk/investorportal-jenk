<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToInvestorAchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investor_ach_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('investor_ach_requests', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('date');
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
        Schema::table('investor_ach_requests', function (Blueprint $table) {
            if (Schema::hasColumn('investor_ach_requests', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
