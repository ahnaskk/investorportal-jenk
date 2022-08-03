<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('merchants_details')) {
        Schema::create('merchants_details', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id');
            $table->integer('crm_id');
            $table->string('exact_legal_company_name', 255)->nullable();
            $table->string('physical_address', 255)->nullable();
            $table->string('physical_address2', 255)->nullable();
            $table->string('work_phone', 20)->nullable();
            $table->string('fax', 10)->nullable();
            $table->string('federal_tax_id', 10)->nullable();
            $table->date('date_business_started')->nullable();
            $table->string('ownership_length', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('use_of_proceeds', 255)->nullable();
            $table->double('requested_amount', 16, 2)->default(0);
            $table->string('lead_source', 255)->nullable();
            $table->string('campaign', 255)->nullable();
            $table->string('owner_first_name', 255)->nullable();
            $table->string('owner_last_name', 255)->nullable();
            $table->string('owner_home', 255)->nullable();
            $table->string('owner_address2', 255)->nullable();
            $table->double('ownership_percentage', 6, 2)->default(0);
            $table->double('owner_credit_score', 8, 2)->default(0);
            $table->string('owner_email', 50)->nullable();
            $table->string('home_address', 255)->nullable();
            $table->string('owner_city', 255)->nullable();
            $table->integer('owner_state_id')->default(0);
            $table->integer('owner_zip')->nullable();
            $table->string('owner_cell', 20)->nullable();
            $table->string('owner_cell2', 20)->nullable();
            $table->string('ssn', 10)->nullable();
            $table->date('dob')->nullable();
            $table->string('partner_first_name', 255)->nullable();
            $table->string('partner_last_name', 255)->nullable();
            $table->string('partner_cell2', 20)->nullable();
            $table->string('partner_email', 50)->nullable();
            $table->string('partner_address2', 255)->nullable();
            $table->double('partner_ownership_percentage', 16, 2)->default(0);
            $table->double('partner_credit_score', 8, 2)->default(0);
            $table->string('partner_home_address', 255)->nullable();
            $table->string('partner_home_hash', 255)->nullable();
            $table->string('partner_city', 255)->nullable();
            $table->integer('partner_state_id')->default(0);
            $table->integer('partner_zip')->nullable();
            $table->string('partner_ssn', 10)->nullable();
            $table->date('partner_dob')->nullable();
            $table->string('partner_cell_hash', 20)->nullable();
            $table->string('product_sold', 255)->nullable();
            $table->string('disposition', 5)->nullable();
            $table->string('marketing_notification', 5)->nullable();
            $table->double('buy_rate')->default(0);
            $table->timestamp('created_date')->nullable();
            $table->double('payback_amount', 16, 2)->default();
            $table->string('lender_email', 255)->nullable();
            $table->double('no_of_deposit', 16, 2)->nullable();
            $table->double('negative_days', 16, 2)->nullable();
            $table->double('nsf', 16, 2)->nullable();
            $table->double('fico_score_primary', 16, 2)->default(0);
            $table->double('fico_score_secondary', 16, 2)->default(0);
            $table->string('deal_type', 255)->nullable();
            $table->string('entity_type', 255)->nullable();
            $table->string('agent_name', 255)->nullable();
            $table->string('under_writer', 255)->nullable();
            $table->string('position', 255)->nullable();
            $table->string('iso_name', 255)->nullable();
            $table->double('annual_revenue', 16, 2)->default(0);
            $table->double('monthly_revenue', 16, 2)->default(0);
            $table->double('withhold_percentage', 16, 2)->default(0);
            $table->double('broker_commission', 16, 2)->default(0);
            $table->integer('terms_in_days')->default(0);
            $table->timestamps();
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
         if (Schema::hasTable('merchants_details')) {
           Schema::dropIfExists('merchants_details');
       }
    }
}
