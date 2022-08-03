<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFulltextOnModelToParticipentPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            DB::statement('Alter table participent_payments ADD FULLTEXT (model);');
        });
    }

    public function down()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
        });
    }
}
