<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('permission_logs') ) {
            Schema::create('permission_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('modified_by')->index();
                $table->bigInteger('object_id');
                $table->string('type')->index();
                $table->string('action')->index();
                $table->json('detail');
                $table->integer('role_id')->nullable()->index();
                $table->integer('module_id')->index();
                $table->integer('user_id')->nullable()->index();
                $table->timestamps();
                $table->softDeletes($column = 'deleted_at', $precision = 0);
                $table->index(['created_at']);
            });
         }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_logs');
    }
}
