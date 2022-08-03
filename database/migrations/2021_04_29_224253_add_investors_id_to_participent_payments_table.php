<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvestorsIdToParticipentPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('participent_payments', 'investor_ids')) {
                $table->text('investor_ids')
                ->after('creator_id')
                ->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (Schema::hasColumn('participent_payments', 'investor_ids')) {
                $table->dropColumn('investor_ids');
            }
        });
    }
}
