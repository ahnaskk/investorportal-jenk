<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMerordernumberToInvestorAchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investor_ach_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('investor_ach_requests', 'merordernumber')) {
                $table->string('merordernumber')->nullable()->after('status_response');
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
            if (Schema::hasColumn('investor_ach_requests', 'merordernumber')) {
                $table->dropColumn('merordernumber');
            }
        });
    }
}
