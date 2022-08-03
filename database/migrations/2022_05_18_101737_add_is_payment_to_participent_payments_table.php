<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddIsPaymentToParticipentPaymentsTable extends Migration
{
    public function up() {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('participent_payments', 'is_payment')) {
                $table->tinyInteger('is_payment')
                ->after('model')
                ->default(0);
            }
        });
    }
    public function down() {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (Schema::hasColumn('participent_payments', 'is_payment')) {
                $table->dropColumn('is_payment');
            }
        });
    }
}
