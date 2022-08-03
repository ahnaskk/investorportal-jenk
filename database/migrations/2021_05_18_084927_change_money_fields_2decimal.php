<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMoneyFields2decimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('alter table ach_requests modify payment_amount DOUBLE(16,2)');

        DB::statement('alter table carry_forwards modify amount DOUBLE(16,2)');

        DB::statement('alter table company_amount modify max_participant DOUBLE(16,2)');

        DB::statement('alter table credit_card_logs modify amount DOUBLE(16,2)');
        DB::statement('alter table credit_card_logs modify actual_amount DOUBLE(16,2)');

        DB::statement('alter table investor_ach_requests modify amount DOUBLE(16,2)');

        DB::statement('alter table manual_liquidity_logs modify liquidity DOUBLE(16,2)');

        DB::statement('alter table manual_r_t_r_balance_logs modify rtr_balance DOUBLE(16,2)');
        DB::statement('alter table manual_r_t_r_balance_logs modify rtr_balance_default DOUBLE(16,2)');
        DB::statement('alter table manual_r_t_r_balance_logs modify total DOUBLE(16,2)');

        DB::statement('alter table merchants modify balance DOUBLE(16,2)');
        DB::statement('alter table merchants modify max_participant_fund DOUBLE(16,2)');
        DB::statement('alter table merchants modify m_syndication_fee DOUBLE(16,2)');
        DB::statement('alter table merchants modify m_mgmnt_fee DOUBLE(16,2)');

        DB::statement('alter table merchant_user modify amount DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify pre_paid DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify syndication_fee DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify paid_syndication_fee DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify under_writing_fee DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify invest_rtr DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify paid_mgmnt_fee DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify paid_participant DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify mgmnt_fee DOUBLE(16,2)');
        DB::statement('alter table merchant_user modify paid_participant_ishare DOUBLE(16,4)');

        DB::statement('alter table participent_payments modify payment DOUBLE(16,2)');
        DB::statement('alter table participent_payments modify final_participant_share DOUBLE(16,2)');

        DB::statement('alter table payment_investors modify mgmnt_fee DOUBLE(16,2)');
        DB::statement('alter table payment_investors modify syndication_fee DOUBLE(16,2)');
        DB::statement('alter table payment_investors modify actual_overpayment DOUBLE(16,2)');
        DB::statement('alter table payment_investors modify overpayment DOUBLE(16,2)');
        DB::statement('alter table payment_investors modify balance DOUBLE(16,2)');
        DB::statement('alter table payment_investors modify principal DOUBLE(16,2)');
        DB::statement('alter table payment_investors modify profit DOUBLE(16,2)');
        DB::statement('alter table payment_investors modify participant_share DOUBLE(16,2)');
        DB::statement('alter table payment_investors modify actual_participant_share DOUBLE(16,2)');

        DB::statement('alter table reassign_history modify investor1_new_liquidity DOUBLE(16,2)');
        DB::statement('alter table reassign_history modify investor1_old_liquidity DOUBLE(16,2)');
        DB::statement('alter table reassign_history modify investor1_total_liquidity DOUBLE(16,2)');
        DB::statement('alter table reassign_history modify investor2_new_liquidity DOUBLE(16,2)');
        DB::statement('alter table reassign_history modify investor2_old_liquidity DOUBLE(16,2)');
        DB::statement('alter table reassign_history modify payment DOUBLE(16,2)');

        DB::statement('alter table reconciles modify actual_amount DOUBLE(16,2)');
        DB::statement('alter table reconciles modify amount DOUBLE(16,2)');

        DB::statement('alter table user_details modify liquidity DOUBLE(16,2)');

        DB::statement('alter table velocity_fees modify payment_amount DOUBLE(16,2)');

        DB::statement('alter table wires modify amount DOUBLE(16,2)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('alter table ach_requests modify payment_amount DOUBLE(16,4)');

        DB::statement('alter table carry_forwards modify amount DOUBLE(16,4)');

        DB::statement('alter table company_amount modify max_participant DOUBLE(16,4)');

        DB::statement('alter table credit_card_logs modify amount DOUBLE(16,4)');
        DB::statement('alter table credit_card_logs modify actual_amount DOUBLE(16,4)');

        DB::statement('alter table investor_ach_requests modify amount DOUBLE(8,2)');

        DB::statement('alter table manual_liquidity_logs modify liquidity DOUBLE(16,4)');

        DB::statement('alter table manual_r_t_r_balance_logs modify rtr_balance DOUBLE(16,4)');
        DB::statement('alter table manual_r_t_r_balance_logs modify rtr_balance_default DOUBLE(16,4)');
        DB::statement('alter table manual_r_t_r_balance_logs modify total DOUBLE(16,4)');

        DB::statement('alter table merchants modify balance DOUBLE(16,4)');
        DB::statement('alter table merchants modify max_participant_fund DOUBLE(16,3)');
        DB::statement('alter table merchants modify m_syndication_fee DOUBLE(8,2)');
        DB::statement('alter table merchants modify m_mgmnt_fee DOUBLE(8,2)');

        DB::statement('alter table merchant_user modify amount DOUBLE(16,4)');
        DB::statement('alter table merchant_user modify pre_paid DOUBLE(16,4)');
        DB::statement('alter table merchant_user modify syndication_fee DOUBLE(16,4)');
        DB::statement('alter table merchant_user modify paid_syndication_fee DOUBLE(12,4)');
        DB::statement('alter table merchant_user modify under_writing_fee DOUBLE(16,4)');
        DB::statement('alter table merchant_user modify invest_rtr DOUBLE(16,4)');
        DB::statement('alter table merchant_user modify paid_mgmnt_fee DOUBLE(12,4)');
        DB::statement('alter table merchant_user modify paid_participant DOUBLE(12,4)');
        DB::statement('alter table merchant_user modify mgmnt_fee DOUBLE(16,4)');
        DB::statement('alter table merchant_user modify paid_participant_ishare DOUBLE(12,4)');

        DB::statement('alter table participent_payments modify payment DOUBLE(16,4)');
        DB::statement('alter table participent_payments modify final_participant_share DOUBLE(16,4)');

        DB::statement('alter table payment_investors modify mgmnt_fee DOUBLE(16,4)');
        DB::statement('alter table payment_investors modify syndication_fee DOUBLE(16,4)');
        DB::statement('alter table payment_investors modify actual_overpayment DOUBLE(16,4)');
        DB::statement('alter table payment_investors modify overpayment DOUBLE(16,4)');
        DB::statement('alter table payment_investors modify balance DOUBLE(16,4)');
        DB::statement('alter table payment_investors modify principal DOUBLE(16,4)');
        DB::statement('alter table payment_investors modify profit DOUBLE(16,4)');
        DB::statement('alter table payment_investors modify participant_share DOUBLE(16,4)');
        DB::statement('alter table payment_investors modify actual_participant_share DOUBLE(16,4)');

        DB::statement('alter table reassign_history modify investor1_new_liquidity DOUBLE(12,2)');
        DB::statement('alter table reassign_history modify investor1_old_liquidity DOUBLE(12,2)');
        DB::statement('alter table reassign_history modify investor1_total_liquidity DOUBLE(12,2)');
        DB::statement('alter table reassign_history modify investor2_new_liquidity DOUBLE(50,2)');
        DB::statement('alter table reassign_history modify investor2_old_liquidity DOUBLE(12,2)');
        DB::statement('alter table reassign_history modify payment DOUBLE(12,2)');

        DB::statement('alter table reconciles modify actual_amount DOUBLE(16,5)');
        DB::statement('alter table reconciles modify amount DOUBLE(16,5)');

        DB::statement('alter table user_details modify liquidity DOUBLE(16,3)');

        DB::statement('alter table velocity_fees modify payment_amount DOUBLE(16,3)');

        DB::statement('alter table wires modify amount DOUBLE(12,2)');
    }
}
