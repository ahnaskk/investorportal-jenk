<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModelToUserMeta extends Migration
{
    public function up()
    {
        Schema::table('user_meta', function (Blueprint $table) {
            if (! Schema::hasColumn('user_meta', 'user_type')) {
                $table->integer('user_type')->default(1)->comment('1:Investor,2:Merchant');
            }
        });
    }

    public function down()
    {
        Schema::table('user_meta', function (Blueprint $table) {
            if (Schema::hasColumn('user_meta', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });
    }
}