<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModelIdToParticipentPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('participent_payments', 'model_id')) {
                $table->integer('model_id')->nullable()->after('payment_date');
            }
            if (! Schema::hasColumn('participent_payments', 'model')) {
                $table->string('model')->nullable()->default(\App\ParticipentPayment::class)->after('model_id');
            }
        });
    }

    public function down()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (Schema::hasColumn('participent_payments', 'model_id')) {
                $table->dropColumn('model_id');
            }
            if (Schema::hasColumn('participent_payments', 'model')) {
                $table->dropColumn('model');
            }
        });
    }
}
