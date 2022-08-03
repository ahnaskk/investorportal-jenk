<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class makeMoneyFieldDefaultZero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'makeMoneyFieldDefaultZero';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changing all money field with null value to zero';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ach = DB::table('ach_requests')->where('payment_amount', null)->count();
        echo "\n Number of Null fields in ach request table is".' '.$ach;
        DB::table('ach_requests')->where('payment_amount', null)->update(['payment_amount'=>0.00]);

        $bills = DB::table('bills')->where('amount', null)->count();
        echo "\n Number of Null fields in bills table is".' '.$bills;
        DB::table('bills')->where('amount', null)->update(['amount'=>0.00]);

        $carry_forwards = DB::table('carry_forwards')->where('amount', null)->count();
        echo "\n Number of Null fields in carry_forwards table is".' '.$carry_forwards;
        DB::table('carry_forwards')->where('amount', null)->update(['amount'=>0.00]);

        $company_amount = DB::table('company_amount')->where('max_participant', null)->count();
        echo "\n Number of Null fields in company_amount table is".' '.$company_amount;
        DB::table('company_amount')->where('max_participant', null)->update(['max_participant'=>0.00]);

        $credit_card_logs = DB::table('credit_card_logs')->where('amount', null)->count() + DB::table('credit_card_logs')->where('actual_amount', null)->count();
        echo "\n Number of Null fields in credit_card_logs table is".' '.$credit_card_logs;
        DB::table('credit_card_logs')->where('amount', null)->update(['amount'=>0.00]);
        DB::table('credit_card_logs')->where('actual_amount', null)->update(['actual_amount'=>0.00]);

        $investor_ach_requests = DB::table('investor_ach_requests')->where('amount', null)->count();
        echo "\n Number of Null fields in investor_ach_requests table is".' '.$investor_ach_requests;
        DB::table('investor_ach_requests')->where('amount', null)->update(['amount'=>0.00]);

        $investor_transactions = DB::table('investor_transactions')->where('amount', null)->count() +
        DB::table('investor_transactions')->where('entity1', null)->count() +
        DB::table('investor_transactions')->where('entity2', null)->count() +
        DB::table('investor_transactions')->where('entity3', null)->count() +
        DB::table('investor_transactions')->where('entity4', null)->count() +
        DB::table('investor_transactions')->where('entity5', null)->count() +
        DB::table('investor_transactions')->where('entity6', null)->count();
        echo "\n Number of Null fields in investor_transactions table is".' '.$investor_transactions;
        DB::table('investor_transactions')->where('amount', null)->update(['amount'=>0.00]);
        DB::table('investor_transactions')->where('entity1', null)->update(['entity1'=>0.00]);
        DB::table('investor_transactions')->where('entity2', null)->update(['entity2'=>0.00]);
        DB::table('investor_transactions')->where('entity3', null)->update(['entity3'=>0.00]);
        DB::table('investor_transactions')->where('entity4', null)->update(['entity4'=>0.00]);
        DB::table('investor_transactions')->where('entity5', null)->update(['entity5'=>0.00]);
        DB::table('investor_transactions')->where('entity6', null)->update(['entity6'=>0.00]);

        $liquidity_log = DB::table('liquidity_log')->where('final_liquidity', null)->count();
        echo "\n Number of Null fields in liquidity_log table is".' '.$liquidity_log;
        DB::table('liquidity_log')->where('final_liquidity', null)->update(['final_liquidity'=>0.00]);

        $manual_liquidity_logs = DB::table('manual_liquidity_logs')->where('liquidity', null)->count();
        echo "\n Number of Null fields in manual_liquidity_logs table is".' '.$manual_liquidity_logs;
        DB::table('manual_liquidity_logs')->where('liquidity', null)->update(['liquidity'=>0.00]);

        $manual_r_t_r_balance_logs = DB::table('manual_r_t_r_balance_logs')->where('rtr_balance', null)->count() +
        DB::table('manual_r_t_r_balance_logs')->where('rtr_balance_default', null)->count() +
        DB::table('manual_r_t_r_balance_logs')->where('total', null)->count();
        echo "\n Number of Null fields in manual_r_t_r_balance_logs table is".' '.$manual_r_t_r_balance_logs;
        DB::table('manual_r_t_r_balance_logs')->where('rtr_balance', null)->update(['rtr_balance'=>0.00]);
        DB::table('manual_r_t_r_balance_logs')->where('rtr_balance_default', null)->update(['rtr_balance_default'=>0.00]);
        DB::table('manual_r_t_r_balance_logs')->where('total', null)->update(['total'=>0.00]);

        $merchant_payment_terms = DB::table('merchant_payment_terms')->where('payment_amount', null)->count();
        echo "\n Number of Null fields in merchant_payment_terms table is".' '.$merchant_payment_terms;
        DB::table('merchant_payment_terms')->whereIn('payment_amount', ['', null])->update(['payment_amount'=>0.00]);

        $merchant_user = DB::table('merchant_user')->where('amount', null)->count() +
        DB::table('merchant_user')->where('pre_paid', null)->count() +
        DB::table('merchant_user')->where('syndication_fee', null)->count() +
        DB::table('merchant_user')->where('paid_syndication_fee', null)->count() +
        DB::table('merchant_user')->where('under_writing_fee', null)->count() +
        DB::table('merchant_user')->where('invest_rtr', null)->count() +
        DB::table('merchant_user')->where('mgmnt_fee', null)->count() +
        DB::table('merchant_user')->where('paid_mgmnt_fee', null)->count() +
        DB::table('merchant_user')->where('paid_participant', null)->count() +
        DB::table('merchant_user')->where('paid_participant_ishare', null)->count();
        echo "\n Number of Null fields in merchant_user table is".' '.$merchant_user;
        DB::table('merchant_user')->where('amount', null)->update(['amount'=>0.00]);
        DB::table('merchant_user')->where('pre_paid', null)->update(['pre_paid'=>0.00]);
        DB::table('merchant_user')->where('syndication_fee', null)->update(['syndication_fee'=>0.00]);
        DB::table('merchant_user')->where('paid_syndication_fee', null)->update(['paid_syndication_fee'=>0.00]);
        DB::table('merchant_user')->where('under_writing_fee', null)->update(['under_writing_fee'=>0.00]);
        DB::table('merchant_user')->where('invest_rtr', null)->update(['invest_rtr'=>0.00]);
        DB::table('merchant_user')->where('mgmnt_fee', null)->update(['mgmnt_fee'=>0.00]);
        DB::table('merchant_user')->where('paid_mgmnt_fee', null)->update(['paid_mgmnt_fee'=>0.00]);
        DB::table('merchant_user')->where('paid_participant', null)->update(['paid_participant'=>0.00]);
        DB::table('merchant_user')->where('paid_participant_ishare', null)->update(['paid_participant_ishare'=>0.00]);

        $participent_payments = DB::table('participent_payments')->where('final_participant_share', null)->count() +
        DB::table('participent_payments')->where('payment', null)->count();
        echo "\n Number of Null fields in participent_payments table is".' '.$participent_payments;
        DB::table('participent_payments')->where('final_participant_share', null)->update(['final_participant_share'=>0.00]);
        DB::table('participent_payments')->where('payment', null)->update(['payment'=>0.00]);

        $payment_investors = DB::table('payment_investors')->where('participant_share', null)->count() +
        DB::table('payment_investors')->where('actual_participant_share', null)->count() +
        DB::table('payment_investors')->where('syndication_fee', null)->count() +
        DB::table('payment_investors')->where('actual_overpayment', null)->count() +
        DB::table('payment_investors')->where('overpayment', null)->count() +
        DB::table('payment_investors')->where('balance', null)->count() +
        DB::table('payment_investors')->where('principal', null)->count() +
        DB::table('payment_investors')->where('profit', null)->count() +
        DB::table('payment_investors')->where('mgmnt_fee', null)->count();
        echo "\n Number of Null fields in payment_investors table is".' '.$payment_investors;
        DB::table('payment_investors')->where('participant_share', null)->update(['participant_share'=>0.00]);
        DB::table('payment_investors')->where('actual_participant_share', null)->update(['actual_participant_share'=>0.00]);
        DB::table('payment_investors')->where('syndication_fee', null)->update(['syndication_fee'=>0.00]);
        DB::table('payment_investors')->where('actual_overpayment', null)->update(['actual_overpayment'=>0.00]);
        DB::table('payment_investors')->where('overpayment', null)->update(['overpayment'=>0.00]);
        DB::table('payment_investors')->where('balance', null)->update(['balance'=>0.00]);
        DB::table('payment_investors')->where('principal', null)->update(['principal'=>0.00]);
        DB::table('payment_investors')->where('profit', null)->update(['profit'=>0.00]);
        DB::table('payment_investors')->where('mgmnt_fee', null)->update(['mgmnt_fee'=>0.00]);

        $reassign_history = DB::table('reassign_history')->where('amount', null)->count() +
        DB::table('reassign_history')->where('investor1_new_liquidity', null)->count() +
        DB::table('reassign_history')->where('investor1_old_liquidity', null)->count() +
        DB::table('reassign_history')->where('investor1_total_liquidity', null)->count() +
        DB::table('reassign_history')->where('investor2_new_liquidity', null)->count() +
        DB::table('reassign_history')->where('investor2_old_liquidity', null)->count() +
        DB::table('reassign_history')->where('investor2_total_liquidity', null)->count() +
        DB::table('reassign_history')->where('liquidity', null)->count() +
        DB::table('reassign_history')->where('liquidity_change', null)->count() +
        DB::table('reassign_history')->where('payment', null)->count();
        echo "\n Number of Null fields in reassign_history table is".' '.$reassign_history;
        DB::table('reassign_history')->where('amount', null)->update(['amount'=>0.00]);
        DB::table('reassign_history')->where('investor1_new_liquidity', null)->update(['investor1_new_liquidity'=>0.00]);
        DB::table('reassign_history')->where('investor1_old_liquidity', null)->update(['investor1_old_liquidity'=>0.00]);
        DB::table('reassign_history')->where('investor1_total_liquidity', null)->update(['investor1_total_liquidity'=>0.00]);
        DB::table('reassign_history')->where('investor2_new_liquidity', null)->update(['investor2_new_liquidity'=>0.00]);
        DB::table('reassign_history')->where('investor2_old_liquidity', null)->update(['investor2_old_liquidity'=>0.00]);
        DB::table('reassign_history')->where('investor2_total_liquidity', null)->update(['investor2_total_liquidity'=>0.00]);
        DB::table('reassign_history')->where('liquidity', null)->update(['liquidity'=>0.00]);
        DB::table('reassign_history')->where('liquidity_change', null)->update(['liquidity_change'=>0.00]);
        DB::table('reassign_history')->where('payment', null)->update(['payment'=>0.00]);

        $reconciles = DB::table('reconciles')->where('actual_amount', null)->count() +
        DB::table('reconciles')->where('amount', null)->count();
        echo "\n Number of Null fields in reconciles table is".' '.$reconciles;
        DB::table('reconciles')->where('actual_amount', null)->update(['actual_amount'=>0.00]);
        DB::table('reconciles')->where('amount', null)->update(['amount'=>0.00]);

        $term_payment_dates = DB::table('term_payment_dates')->where('payment_amount', null)->count();
        echo "\n Number of Null fields in term_payment_dates table is".' '.$term_payment_dates;
        DB::table('term_payment_dates')->where('payment_amount', null)->update(['payment_amount'=>0.00]);

        DB::table('transactions')->whereIn('amount', ['', null])->update(['amount'=>0.00]);
        $transactions = DB::table('transactions')->where('amount', null)->count();
        echo "\n Number of Null fields in transactions table is".' '.$transactions;

        $user_details = DB::table('user_details')->where('liquidity', null)->count();
        echo "\n Number of Null fields in user_details table is".' '.$user_details;
        DB::table('user_details')->where('liquidity', null)->update(['liquidity'=>0.00]);

        $velocity_fees = DB::table('velocity_fees')->where('payment_amount', null)->count();
        echo "\n Number of Null fields in velocity_fees table is".' '.$velocity_fees;
        DB::table('velocity_fees')->where('payment_amount', null)->update(['payment_amount'=>0.00]);

        $wires = DB::table('wires')->where('amount', null)->count();
        echo "\n Number of Null fields in wires table is".' '.$wires;
        DB::table('wires')->where('amount', null)->update(['amount'=>0.00]);

        $merchants = DB::table('merchants')->where('balance', null)->count() +
        DB::table('merchants')->where('max_participant_fund', null)->count() +
        DB::table('merchants')->where('m_syndication_fee', null)->count() +
        DB::table('merchants')->where('m_mgmnt_fee', null)->count() +
        DB::table('merchants')->where('funded', null)->count() +
        DB::table('merchants')->where('payment_amount', null)->count() +
        DB::table('merchants')->where('rtr', null)->count() +
        DB::table('merchants')->where('underwriting_fee', null)->count() +
        DB::table('merchants_details')->where('monthly_revenue', null)->count() +
        DB::table('merchants_details')->where('annual_revenue', null)->count() +
        DB::table('merchants')->where('up_sell_commission', null)->count();
        echo "\n Number of Null fields in merchants table is".' '.$merchants.' ';
        DB::table('merchants')->where('balance', null)->update(['balance'=>0.00]);
        DB::table('merchants')->where('max_participant_fund', null)->update(['max_participant_fund'=>0.00]);
        DB::table('merchants')->where('m_syndication_fee', null)->update(['m_syndication_fee'=>0.00]);
        DB::table('merchants')->where('m_mgmnt_fee', null)->update(['m_mgmnt_fee'=>0.00]);
        DB::table('merchants')->where('funded', null)->update(['funded'=>0.00]);
        DB::table('merchants')->where('payment_amount', null)->update(['payment_amount'=>0.00]);
        DB::table('merchants')->where('rtr', null)->update(['rtr'=>0.00]);
        DB::table('merchants')->where('underwriting_fee', null)->update(['underwriting_fee'=>0.00]);
        DB::table('merchants_details')->where('monthly_revenue', null)->update(['monthly_revenue'=>0.00]);
        DB::table('merchants_details')->where('annual_revenue', null)->update(['annual_revenue'=>0.00]);
        DB::table('merchants')->where('up_sell_commission', null)->update(['up_sell_commission'=>0.00]);
    }
}
