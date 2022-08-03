<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReasonToAchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ach_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('ach_requests', 'reason')) {
                $table->string('reason', 500)->nullable()->after('response');
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
            if (Schema::hasColumn('ach_requests', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
}
