<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevertStatusToParticipentPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('participent_payments', 'revert_id')) {
                $table->integer('revert_id')
                ->after('is_profit_adjustment_added')
                ->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (Schema::hasColumn('participent_payments', 'revert_id')) {
                $table->dropColumn('revert_id');
            }
        });
    }
}
