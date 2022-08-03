<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LiquidityLogAddIndex extends Migration
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
            if (! $doctrineTable->hasIndex('liquidity_log_idx_member_type_liquidity_chang')) {
                $table->index(['member_type', 'liquidity_change'], 'liquidity_log_idx_member_type_liquidity_chang');
            }
        });
        Schema::table('merchants', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('merchants');
            if (! $doctrineTable->hasIndex('merchants_idx_active_status_id')) {
                $table->index(['active_status', 'id'], 'merchants_idx_active_status_id');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('users');
            if (! $doctrineTable->hasIndex('users_idx_id')) {
                $table->index(['id'], 'users_idx_id');
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
            if ($doctrineTable->hasIndex('liquidity_log_idx_member_type_liquidity_chang')) {
                $table->dropIndex('liquidity_log_idx_member_type_liquidity_chang');
            }
        });
        Schema::table('merchants', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('merchants');
            if ($doctrineTable->hasIndex('merchants_idx_active_status_id')) {
                $table->dropIndex('merchants_idx_active_status_id');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('users');
            if ($doctrineTable->hasIndex('users_idx_id')) {
                $table->dropIndex('users_idx_id');
            }
        });
    }
}
