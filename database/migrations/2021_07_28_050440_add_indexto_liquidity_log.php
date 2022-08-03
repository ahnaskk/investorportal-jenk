<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndextoLiquidityLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('liquidity_log', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('liquidity_log');
            if (! $doctrineTable->hasIndex('created_at')) {
                $table->index('created_at', 'created_at');
            }
            if (! $doctrineTable->hasIndex('description')) {
                $table->index('description', 'description');
            }
            if (! $doctrineTable->hasIndex('liquidity_change')) {
                $table->index('liquidity_change', 'liquidity_change');
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
        Schema::table('liquidity_log', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('liquidity_log');
            if ($doctrineTable->hasIndex('created_at')) {
                $table->dropIndex('created_at');
            }
            if ($doctrineTable->hasIndex('description')) {
                $table->dropIndex('description');
            }
            if ($doctrineTable->hasIndex('liquidity_change')) {
                $table->dropIndex('liquidity_change');
            }

        });
    }
}
