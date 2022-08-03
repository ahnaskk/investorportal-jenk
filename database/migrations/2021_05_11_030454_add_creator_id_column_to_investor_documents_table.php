<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToInvestorDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('investor_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('investor_documents', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('status');
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
        Schema::table('investor_documents', function (Blueprint $table) {
            if (Schema::hasColumn('investor_documents', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
