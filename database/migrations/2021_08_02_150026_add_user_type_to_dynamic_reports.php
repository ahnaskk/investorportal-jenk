<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTypeToDynamicReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	public function up()
	{
			Schema::table('dynamic_reports', function (Blueprint $table) {
			if (! Schema::hasColumn('dynamic_reports', 'user_type')) {
				$table->integer('user_type')->default(1)->comment('1:Investor,2:Merchant');
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
		Schema::table('dynamic_reports', function (Blueprint $table) {
			if (Schema::hasColumn('dynamic_reports', 'user_type')) {
				$table->dropColumn('user_type');
			}
		});
	}
}
