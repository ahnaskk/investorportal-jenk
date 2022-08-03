<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFirewallUserTableAddRoleId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('firewall_user', function (Blueprint $table) {
            if (! Schema::hasColumn('firewall_user','role_id')) {
                $table->bigInteger('role_id')->nullable()->after('user_id');
                $table->bigInteger('user_id')->nullable()->change();
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
        Schema::table('firewall_user', function (Blueprint $table) {
            if (Schema::hasColumn('firewall_user','role_id')) {
                 $table->dropColumn('role_id');
             }
         });
    }
}
