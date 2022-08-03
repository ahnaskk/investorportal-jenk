<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EquityInvestorReport extends Model
{
    use HasFactory;

    public static function EquityInvestorReportCheck(){
        /*$date  = Carbon::now()->subMinutes( 30);
        if(EquityInvestorReport::where( 'updated_at', '<=', $date )->count() || !EquityInvestorReport::count()){*/

            $data =  DB::select("select `users`.`id`, `users`.`name`, (SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity, (SELECT SUM(investor_transactions.amount) FROM investor_transactions WHERE users.id = investor_transactions.investor_id and transaction_category=1 and investor_transactions.status=1 ) credit_amount, (select SUM( actual_paid_participant_ishare-paid_mgmnt_fee)
+
2*
SUM( IF(actual_paid_participant_ishare>invest_rtr,(invest_rtr-(actual_paid_participant_ishare)),0) )
as ctd from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`deleted_at` is null)) as `ctd`, (select SUM((invest_rtr-(invest_rtr * (0 / 100)))-
invest_rtr * (( merchant_user.mgmnt_fee)/100)
) as fees from `merchant_user` inner join `merchants` on `merchants`.`id` = `merchant_user`.`merchant_id` where `users`.`id` = `merchant_user`.`user_id` and `status` = 1 and `merchants`.`active_status` = 1 and `merchant_user`.`status` in (1, 3) and `merchants`.`sub_status_id` not in (4, 22)) as `fees`, (select SUM(invest_rtr) as tinvest_rtr from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `status` = 1 and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `sub_status_id` not in (4, 22) and `merchants`.`deleted_at` is null)) as `tinvest_rtr`
  from `users` inner join `user_has_roles` on `users`.`id` = `user_has_roles`.`model_id` and `user_has_roles`.`role_id` = 2 where `users`.`investor_type` = 2 and `users`.`deleted_at` is null");

	    $data2 =  DB::select("select SUM(actual_participant_share-mgmnt_fee) as default_pay_rtr ,SUM(overpayment) as overpayment, users.id from `payment_investors`  join users on  `users`.`id` = `payment_investors`.`user_id` and `users`.`investor_type` = 2 and `users`.`deleted_at` is null group by `payment_investors`.`user_id`");

	    $dd = report_array($data,$data2);

	    EquityInvestorReport::truncate();
            foreach ($dd as $val){
                $equityInvestorReport = new EquityInvestorReport();
                $equityInvestorReport->investor_id = $val['id'];
                $equityInvestorReport->liquidity = $val['liquidity'];
                $equityInvestorReport->credit_amount = ($val['credit_amount']!=null) ? $val['credit_amount']: 0;
                $equityInvestorReport->ctd = ($val['ctd']!=null) ? $val['ctd'] : 0;
                $equityInvestorReport->fees = ($val['fees']!=null) ? $val['fees'] : 0;
                $equityInvestorReport->tinvest_rtr = ($val['tinvest_rtr']!=null) ? $val['tinvest_rtr'] : 0;;
                $equityInvestorReport->default_pay_rtr = isset($val['default_pay_rtr']) ? $val['default_pay_rtr'] :0;
                $equityInvestorReport->overpayment = isset($val['overpayment']) ? $val['overpayment']: 0;
                $equityInvestorReport->save();
            }
            return 'done';
        //}


    }

}
