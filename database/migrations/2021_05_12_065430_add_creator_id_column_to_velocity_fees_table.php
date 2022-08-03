<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorIdColumnToVelocityFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('velocity_fees', function (Blueprint $table) {
            if (! Schema::hasColumn('velocity_fees', 'creator_id')) {
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
        Schema::table('velocity_fees', function (Blueprint $table) {
            if (Schema::hasColumn('velocity_fees', 'creator_id')) {
                $table->dropColumn(['creator_id']);
            }
        });
    }
}
