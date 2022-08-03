<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAchAutomationIntoAchMerchantFromSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE `settings` SET `keys`='ach_merchant' WHERE `keys`='ach_automation'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE `settings` SET `keys`='ach_automation' WHERE `keys`='ach_merchant'");
    }
}
