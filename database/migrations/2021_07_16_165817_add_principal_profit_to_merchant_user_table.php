<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddPrincipalProfitToMerchantUserTable extends Migration
{
    public function up()
    {
        Schema::table('merchant_user', function (Blueprint $table) {
            if (! Schema::hasColumn('merchant_user', 'paid_principal')) {
                $table->decimal('paid_principal',16,2)
                ->after('actual_paid_participant_ishare')
                ->default(0)
                ->nullable();
            }
            if (! Schema::hasColumn('merchant_user', 'paid_profit')) {
                $table->decimal('paid_profit',16,2)
                ->after('actual_paid_participant_ishare')
                ->default(0)
                ->nullable();
            }
        });
    }
    public function down()
    {
        Schema::table('merchant_user', function (Blueprint $table) {
            if (Schema::hasColumn('merchant_user', 'paid_principal')) {
                $table->dropColumn('paid_principal');
            }
            if (Schema::hasColumn('merchant_user', 'paid_profit')) {
                $table->dropColumn('paid_profit');
            }
        });
    }
}
