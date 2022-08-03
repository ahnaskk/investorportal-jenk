<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTimeZone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatetimezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2000000000M');

        $tables = DB::select('SHOW TABLES');
        foreach (array_chunk($tables, 5) as $tabl) {
            foreach ($tabl as $table) {
                foreach ($table as $key => $value) {
                    $arr = [/*"activity_log", "api_log", "liquidity_log", "user_activity_logs",*/'jobs', 'migrations', 'company_amount_check_view', 'company_amount_pivot_check_view', 'fee_report_views', 'final_participant_share_check_view', 'final_participant_share_grouped_check_view', 'investment_report_views', 'investor_ach_request_views', 'investor_transactions_view', 'merchant_fund_detail_views', 'merchant_user_views', 'merchant_views', 'participent_payments_check_view', 'payment_investors_check_view', 'profit_checks', 'user_details_liquidity_check_view', 'user_total_credits_view', 'zero_payment_amount_check_view', 'company_amount_views', 'investment_amount_check_view', 'investment_amount_grouped_check_view', 'investor_share_check_view', 'market_offers', 'merchants_fund_amount_check_view', 'penny_investment_check_view'];
                    if (! in_array($value, $arr)) {
                        echo "\n $value";
                        $table = DB::table($value)->get();
                        foreach ($table as $val) {
                            if (isset($val->created_at) && $val->created_at != null) {
                                $date = Carbon::createFromFormat('Y-m-d H:i:s', $val->created_at, 'America/New_york');
                                $val->created_at = $date->setTimezone('UTC');
                            }
                        }
                        $tab = $table->toArray();
                        $data = json_decode(json_encode($tab), true);
                        foreach (array_chunk($data, 500) as $t) {
                            DB::table($value)->upsert($t, 'created_at');
                        }
                    }
                }
            }
        }

        /*$result = json_decode(json_encode($tables), true);
        return  $result[0]['Tables_in_investorportal'];*/
        echo "\n All time zones updated ";
    }
}
