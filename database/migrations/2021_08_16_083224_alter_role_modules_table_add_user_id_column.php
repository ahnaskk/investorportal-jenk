<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRoleModulesTableAddUserIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('role_modules', function (Blueprint $table) {
            if (! Schema::hasColumn('role_modules','user_id')) {
                $table->bigInteger('user_id')->nullable()->after('role_id');
                $table->bigInteger('role_id')->nullable()->change();
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
        Schema::table('role_modules', function (Blueprint $table) {
            if (Schema::hasColumn('role_modules','user_id')) {
                 $table->dropColumn('user_id');
             }
         });
    }
}
