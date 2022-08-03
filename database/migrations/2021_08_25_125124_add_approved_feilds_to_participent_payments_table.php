<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovedFeildsToParticipentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('participent_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('approved_by','approved_at')) {
                $table->string('approved_by')->nullable()->after('investor_ids');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
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
        Schema::table('participent_payments', function (Blueprint $table) {
            if (Schema::hasColumn('approved_by','approved_at')) {
                $table->dropColumn(['approved_by', 'approved_at']);
            }
        });
    }
}
