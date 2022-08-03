<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderToOauthClientsTable extends Migration
{
    public function up()
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            if (! Schema::hasColumn('oauth_clients', 'provider')) {
                $table->text('provider')->after('secret')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            if (Schema::hasColumn('oauth_clients', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
}
