<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountStatusToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'show_agent_account')) {
                $table->tinyInteger('show_agent_account')                
                ->default(1);
            }
            if (! Schema::hasColumn('settings', 'show_overpayment_account')) {
                $table->tinyInteger('show_overpayment_account')                
                ->default(1);
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
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'show_agent_account')) {
                $table->dropColumn('show_agent_account');
            }
            if (Schema::hasColumn('settings', 'show_overpayment_account')) {
                $table->dropColumn('show_overpayment_account');
            }
        });
    }
}
