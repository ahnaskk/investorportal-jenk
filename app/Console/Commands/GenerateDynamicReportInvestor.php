<?php

namespace App\Console\Commands;

use App\Models\DynamicReportInvestor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDynamicReportInvestor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dynamicreport:investor {unit=1} {datetime=day}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generating Dynamic Report for investor';

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
        $unit =  $this->argument('unit');
        $datetime =  $this->argument('datetime');


        $start_date = date('Y-m-d', strtotime("-$unit $datetime"));
        $count = (round((time() - strtotime($start_date)) / (60 * 60 * 24))) - 2;

        for ($x = $count; $x >= 0; $x--) {
            $date = date('Y-m-d', strtotime("-$x day"));
            DynamicReportInvestor::where('payment_date',$date)->delete();
            //$data1 = DB::select("select `users`.`id`, `users`.`company`, `users`.`name`, '$date' as `payment_date`,  '0' as `profit`,'0'as principal, 0 as management_fee_earned,0 as syndication_fee ,0 as underwriting_fee_earned, 0 as origination_fee, 0 as up_sell_commission, (SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity, (SELECT liquidity_adjuster FROM user_details WHERE users.id = user_details.user_id) liquidity_adjuster, (select SUM(investor_transactions.amount) as credit from `investor_transactions` where `users`.`id` = `investor_transactions`.`investor_id` and `investor_transactions`.`status` = 1 and `investor_transactions`.`date` = '$date' ) as `credit_amount`, (select SUM(merchant_user.amount) as total_funded from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `total_funded`, (select SUM(merchant_user.commission_amount) as commission_amount from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `commission_amount`, (select SUM(merchant_user.under_writing_fee) as under_writing_fee from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `under_writing_fee`, (select SUM(merchant_user.pre_paid) as pre_paid from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `pre_paid`, (select SUM(merchant_user.invest_rtr- (merchant_user.mgmnt_fee/100)*merchant_user.invest_rtr) as invest_rtr from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `rtr`, (select sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `ctd` from `users` inner join `user_has_roles` on `users`.`id` = `user_has_roles`.`model_id` and `user_has_roles`.`role_id` in (2, 13) where `users`.`deleted_at` is null");
            $data1 = DB::select("select `users`.`id`, `users`.`company`, `users`.`name`, '$date' as `payment_date`,  '0' as `profit`,'0'as principal, 0 as management_fee_earned,0 as syndication_fee ,0 as underwriting_fee_earned, 0 as origination_fee, 0 as up_sell_commission, (SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity, (SELECT liquidity_adjuster FROM user_details WHERE users.id = user_details.user_id) liquidity_adjuster, (select SUM(investor_transactions.amount) as credit from `investor_transactions` where `users`.`id` = `investor_transactions`.`investor_id` and `investor_transactions`.`status` = 1 and `investor_transactions`.`date` = '$date' ) as `credit_amount`, (select SUM(merchant_user.amount) as total_funded from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `total_funded`, (select SUM(merchant_user.commission_amount) as commission_amount from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `commission_amount`, (select SUM(merchant_user.under_writing_fee) as under_writing_fee from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `under_writing_fee`, (select SUM(merchant_user.pre_paid) as pre_paid from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `pre_paid`, (select SUM(merchant_user.invest_rtr) as invest_rtr from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `rtr`, (select sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `ctd` from `users` inner join `user_has_roles` on `users`.`id` = `user_has_roles`.`model_id` and `user_has_roles`.`role_id` in (2, 13) where `users`.`deleted_at` is null");
            $data2 = DB::select(" select merchant_user.user_id as id,sum(IF(merchants.date_funded = '$date' = 1, merchant_user.pre_paid, IF(merchants.date_funded = '$date', merchant_user.pre_paid, 0))) as syndication_fee, sum(IF(merchants.date_funded = '$date' = 1, merchant_user.under_writing_fee, IF(merchants.date_funded = '$date', merchant_user.under_writing_fee, 0))) as underwriting_fee_earned, IF(merchants.date_funded = '$date' = 1,(merchants.origination_fee * merchants.funded) / 100, IF(merchants.date_funded = '$date', (merchants.origination_fee * merchants.funded) / 100, 0)) as origination_fee, IF(merchants.date_funded = '$date' = 1, merchants.up_sell_commission, IF(merchants.date_funded = '$date', merchants.up_sell_commission, 0)) as up_sell_commission, IF(merchants.date_funded = '$date' = 1, 350, IF(merchants.date_funded = '$date', 350, 0)) as underwriting_fee_flat, IF(merchants.date_funded = '$date' = 1, 'yes', IF(merchants.date_funded = '$date', 'yes', 'no')) as within_funded_date from `merchants` left join `merchant_user` on `merchants`.`id` = `merchant_user`.`merchant_id` inner join `user_has_roles`  on `merchant_user`.`user_id` = `user_has_roles`.`model_id` and `user_has_roles`.`role_id` in (2, 13) group by `merchant_user`.`user_id` order by `merchants`.`date_funded` desc");

            //$data3 = DB::select("select  sum(profit) as `profit`, sum(principal) as principal, sum(`payment_investors`.mgmnt_fee) as management_fee_earned, `payment_investors`.`user_id` as id from `payment_investors` LEFT JOIN `merchant_user` ON `merchant_user`.`user_id` = `payment_investors`.`user_id` inner join `participent_payments` on `payment_investors`.`participent_payment_id` = `participent_payments`.`id` where `payment_type` = 1 and `payment_date` = '$date' group by `payment_investors`.`user_id`");
            $data3 = DB::select("SELECT `user_id` as id, sum(`net_amount`) as ctd, sum(`profit`) as profit, sum(`principal`) as principal,sum(`mgmnt_fee`) as management_fee_earned FROM `payment_investors_views` WHERE `payment_date` = '$date' group by `user_id`");
            //$ctd = DB::select("SELECT `investor_id` as id, sum(ctd) FROM `investment_report_views` WHERE `date_funded` = '$date' group by `investor_id`");
            //$data3 = array_values(dynamic_report_array($data3,$ctd));



            $kk = array_values(dynamic_report_array(dynamic_report_array($data1, $data2), $data3));
	        $todate = date('Y-m-d', strtotime("+1 day", strtotime($date)));
	        $data4 = DB::select("select   `liquidity_log`.`member_id` as `id`,  sum(liquidity_log.liquidity_change) as credit_amount, sum(liquidity_log.final_liquidity) as final_liquidity  from `liquidity_log` left join `users` on `users`.`id` = `liquidity_log`.`member_id` left join `merchants` on `merchants`.`id` = `liquidity_log`.`merchant_id` where `liquidity_log`.`liquidity_change` != 0  and `liquidity_log`.`created_at` >= '$date 00:04:00' and `liquidity_log`.`created_at` <= '$todate 00:03:59' and `liquidity_log`.`batch_id` is not null group by `liquidity_log`.`member_id`");
	        $kk = array_values(dynamic_report_array($kk,$data4));

            DynamicReportInvestor::insert(json_decode(json_encode($kk,JSON_NUMERIC_CHECK),true));
            $cc = count($kk);
            echo "$date : $cc \n";
        }

        return 'done';
    }
}
