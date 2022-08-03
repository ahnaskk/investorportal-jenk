<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToMailLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mail_log', function (Blueprint $table) {
            if (! Schema::hasColumn('mail_log', 'creator_id')) {
                $table->integer('creator_id')->nullable()
                ->after('failed_message');
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
        Schema::table('mail_log', function (Blueprint $table) {
            if (Schema::hasColumn('mail_log', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
