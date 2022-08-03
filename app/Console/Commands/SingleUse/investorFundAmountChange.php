<?php

namespace App\Console\Commands\SingleUse;

use App\CompanyAmount;
use App\Merchant;
use App\MerchantUser;
use App\Models\Views\MerchantUserView;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class investorFundAmountChange extends Command
{
    protected $signature = 'change:investorfundAmount';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::beginTransaction();
        $Merchants = new Merchant;
        // $Merchants=$Merchants->where('id',9790);
        $Merchants = $Merchants->get();
        foreach ($Merchants as $no => $Merchant) {
            echo ++$no.')'.$Merchant->id."\n";
            // $CompanyAmountOld=MerchantUserView::where('merchant_id',$Merchant->id)->select(DB::raw('company,sum(amount) as amount'))->groupBy('company')->pluck('amount','company')->toArray();
            $MerchantUsers = MerchantUser::where('merchant_id', $Merchant->id)
            // ->where('user_id','!=',504)
            ->get();
            foreach ($MerchantUsers as $key => $value) {
                $singleData = [
                    'amount'            =>round($value->amount, 2),
                    'invest_rtr'        =>round($value->invest_rtr, 2),
                    'commission_amount' =>round($value->commission_amount, 2),
                    'under_writing_fee' =>round($value->under_writing_fee, 2),
                    'mgmnt_fee'         =>round($value->mgmnt_fee, 2),
                ];
                DB::table('merchant_user')->where('id', $value->id)->update($singleData);
            }
            // $CompanyAmountNew=MerchantUserView::where('merchant_id',$Merchant->id)->select(DB::raw('company,sum(amount) as amount'))->groupBy('company')->pluck('amount','company')->toArray();
            // foreach ($CompanyAmountNew as $company => $amount) {
            //     $diff=round($CompanyAmountOld[$company]-$amount,2);
            //     if($diff){
            //         echo $amount." Merchant User ".$diff." Updated on ". $company ."\n";
            //         $MerchantUserView=MerchantUserView::where('merchant_id',$Merchant->id)->where('company',$company)->orderBy('amount','DESC')->first();
            //         $MerchantUser=MerchantUser::find($MerchantUserView->id);
            //         $MerchantUser->amount+=$diff;
            //         $MerchantUser->save();
            //     }
            // }
            // $CompanyAmountAfter=MerchantUserView::where('merchant_id',$Merchant->id)->select(DB::raw('company,sum(amount) as amount'))->groupBy('company')->pluck('amount','company')->toArray();
        }
        DB::commit();
    }
}
