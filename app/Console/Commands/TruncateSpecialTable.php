<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use DB;
class TruncateSpecialTable extends Command
{
    protected $signature = 'truncate:special_tables';
    protected $description = 'Truncate Table for Testers';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('actum_decline_codes')->truncate();
        echo "\n truncated actum_decline_codes";
        DB::table('payment_investors')->truncate();
        echo "\n truncated payment_investors";
        DB::table('activity_log')->truncate();
        echo "\n truncated activity_log";
        DB::table('term_payment_dates')->truncate();
        echo "\n truncated term_payment_dates";
        DB::table('participent_payments')->truncate();
        echo "\n truncated participent_payments";
        DB::table('mailboxrows')->truncate();
        echo "\n truncated mailboxrows";
        DB::table('api_log')->truncate();
        echo "\n truncated api_log";
        DB::table('user_details')->truncate();
        echo "\n truncated user_details";
        DB::table('user_activity_logs')->truncate();
        echo "\n truncated user_activity_logs";
        DB::table('merchant_user')->truncate();
        echo "\n truncated merchant_user";
        DB::table('investor_transactions')->truncate();
        echo "\n truncated investor_transactions";
        DB::table('carry_forwards')->truncate();
        echo "\n truncated carry_forwards";
        DB::table('company_amount')->truncate();
        echo "\n truncated company_amount";
        DB::table('ach_requests')->truncate();
        echo "\n truncated ach_requests";
        DB::table('mail_log')->truncate();
        echo "\n truncated mail_log";
        DB::table('reassign_history')->truncate();
        echo "\n truncated reassign_history";
        DB::table('documents')->truncate();
        echo "\n truncated documents";
        DB::table('statements')->truncate();
        echo "\n truncated statements";
        DB::table('merchant_status_log')->truncate();
        echo "\n truncated merchant_status_log";
        DB::table('audits')->truncate();
        echo "\n truncated audits";
        DB::table('telescope_entries')->truncate();
        echo "\n truncated telescope_entries";
        DB::table('merchant_payment_terms')->truncate();
        echo "\n truncated merchant_payment_terms";
        DB::table('transactions')->truncate();
        echo "\n truncated transactions";
        DB::table('m_notes')->truncate();
        echo "\n truncated m_notes";
        DB::table('wires_merchant')->truncate();
        echo "\n truncated wires_merchant";
        DB::table('user_meta')->truncate();
        echo "\n truncated user_meta";
        DB::table('payment_pauses')->truncate();
        echo "\n truncated payment_pauses";
        DB::table('merchant_statements')->truncate();
        echo "\n truncated merchant_statements";
        DB::table('merchant_market_offers')->truncate();
        echo "\n truncated merchant_market_offers";
        DB::table('jobs')->truncate();
        echo "\n truncated jobs";
        DB::table('failed_jobs')->truncate();
        echo "\n truncated failed_jobs";
        DB::table('liquidity_log')->truncate();
        echo "\n truncated liquidity_log";
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return 0;
    }
}
