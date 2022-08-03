<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class ChangeToDefaultParticipentPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('default_participent_payments', function (Blueprint $table) {
            DB::statement("ALTER TABLE participent_payments CHANGE model model VARCHAR(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT 'App\\\ParticipentPayment';");
        });
    }
    public function down()
    {
        Schema::table('default_participent_payments', function (Blueprint $table) {
            //
        });
    }
}
