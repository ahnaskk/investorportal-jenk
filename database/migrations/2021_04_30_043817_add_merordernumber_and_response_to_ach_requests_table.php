<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMerordernumberAndResponseToAchRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ach_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('ach_requests', 'merordernumber')) {
                $table->string('merordernumber')->nullable()->after('is_fees');
            }
            if (! Schema::hasColumn('ach_requests', 'response')) {
                $table->longText('response')->nullable()->after('merordernumber');
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
            if (Schema::hasColumn('ach_requests', 'merordernumber')) {
                $table->dropColumn('merordernumber');
            }
            if (Schema::hasColumn('ach_requests', 'response')) {
                $table->dropColumn('response');
            }
        });
    }
}
