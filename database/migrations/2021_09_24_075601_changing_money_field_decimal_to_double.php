<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangingMoneyFieldDecimalToDouble extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("alter table ach_requests modify payment_amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table bills modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table carry_forwards modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table company_amount modify max_participant DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table credit_card_logs modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table credit_card_logs modify actual_amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table funding_requests modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table funding_requests modify commission DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table funding_requests modify mgmnt_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table funding_requests modify pmnts DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table funding_requests modify syndication_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table investor_ach_requests modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table investor_transactions modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity1 DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity2 DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity3 DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity4 DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity5 DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity6 DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table liquidity_log modify final_liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table manual_liquidity_logs modify liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table manual_r_t_r_balance_logs modify rtr_balance DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table manual_r_t_r_balance_logs modify rtr_balance_default DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table manual_r_t_r_balance_logs modify total DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table merchant_payment_terms modify payment_amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table merchant_user modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify commission_amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify pre_paid DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify syndication_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify paid_syndication_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify under_writing_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify invest_rtr DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify mgmnt_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify paid_mgmnt_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify paid_participant DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify actual_paid_participant_ishare DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table participent_payments modify final_participant_share DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table participent_payments modify payment DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table payment_investors modify participant_share DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify actual_participant_share DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify mgmnt_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify syndication_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify actual_overpayment DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify overpayment DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify balance DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify principal DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify profit DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table reassign_history modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor1_new_liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor1_old_liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor1_total_liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor2_new_liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor2_old_liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor2_total_liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify liquidity_change DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify payment DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table reconciles modify actual_amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reconciles modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table term_payment_dates modify payment_amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table transactions modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table user_details modify liquidity DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table user_details modify liquidity_adjuster DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table velocity_fees modify payment_amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table wires modify amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table merchants modify balance DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify commission DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify funded DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify max_participant_fund DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify payment_amount DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify rtr DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify m_syndication_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify underwriting_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify m_mgmnt_fee DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        
        DB::statement("alter table merchants_details modify monthly_revenue DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants_details modify annual_revenue DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify up_sell_commission DOUBLE(16,2) NOT NULL DEFAULT '0.00'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("alter table ach_requests modify payment_amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table bills modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table carry_forwards modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table company_amount modify max_participant DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table credit_card_logs modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table credit_card_logs modify actual_amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table funding_requests modify amount DECIMAL(8,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table funding_requests modify commission DECIMAL(8,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table funding_requests modify mgmnt_fee DECIMAL(8,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table funding_requests modify pmnts DECIMAL(8,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table funding_requests modify syndication_fee DECIMAL(8,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table investor_ach_requests modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table investor_transactions modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity1 DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity2 DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity3 DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity4 DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity5 DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table investor_transactions modify entity6 DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table liquidity_log modify final_liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table manual_liquidity_logs modify liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table manual_r_t_r_balance_logs modify rtr_balance DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table manual_r_t_r_balance_logs modify rtr_balance_default DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table manual_r_t_r_balance_logs modify total DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table merchant_payment_terms modify payment_amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table merchant_user modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify commission_amount DECIMAL(12,4) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify pre_paid DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify syndication_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify paid_syndication_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify under_writing_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify invest_rtr DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify mgmnt_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify paid_mgmnt_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify paid_participant DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify paid_participant_ishare DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchant_user modify actual_paid_participant_ishare DECIMAL(16,4) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table participent_payments modify final_participant_share DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table participent_payments modify payment DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table payment_investors modify participant_share DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify actual_participant_share DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify mgmnt_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify syndication_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify actual_overpayment DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify overpayment DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify balance DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify principal DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table payment_investors modify profit DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table reassign_history modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor1_new_liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor1_old_liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor1_total_liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor2_new_liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor2_old_liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify investor2_total_liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify liquidity_change DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reassign_history modify payment DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table reconciles modify actual_amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table reconciles modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table term_payment_dates modify payment_amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table transactions modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table user_details modify liquidity DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table user_details modify liquidity_adjuster DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table velocity_fees modify payment_amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table wires modify amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");

        DB::statement("alter table merchants modify balance DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify commission DECIMAL(18,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify funded DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify max_participant_fund DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify payment_amount DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify rtr DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify m_syndication_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify underwriting_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify m_mgmnt_fee DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants_details modify monthly_revenue DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants_details modify annual_revenue DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
        DB::statement("alter table merchants modify up_sell_commission DECIMAL(16,2) NOT NULL DEFAULT '0.00'");
    }
}
