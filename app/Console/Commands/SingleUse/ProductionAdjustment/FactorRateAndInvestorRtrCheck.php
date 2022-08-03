<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\Merchant;
use App\MerchantUser;
use App\Views\MerchantUserView;
use DB;
class FactorRateAndInvestorRtrCheck extends Command
{
    protected $signature = 'check:factor_rate_and_investor_rtr {merchantId=""}';
    protected $description = 'To check and Update the existing investor_rtr with funded amount and factor_rate';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $Merchant      =new Merchant; 
        $Merchant      =$Merchant->whereIn('label',[1,2]);
        $Merchant      =$Merchant->where('id','!=',9783);
        if ($merchantId) {
            $Merchant = $Merchant->where('id', $merchantId);
        }
        $Merchants     =$Merchant->get();
        $MerchantCount =$Merchant->count();
        foreach ($Merchants as $key => $Merchant) {
            $merchant_id   =$Merchant->id;
            $funded        =$Merchant->funded;
            $factor_rate   =$Merchant->factor_rate;
            $count         =$MerchantCount-$key;
            $MerchantUsers =MerchantUser::where('merchant_id',$merchant_id)->get();
            foreach ($MerchantUsers as $MerchantUser) {
                $actual_investor_rtr=round($MerchantUser->amount*$factor_rate,2);
                $investor_rtr=$MerchantUser->invest_rtr;
                if($investor_rtr){
                    $investor_rtr=round($investor_rtr,2);
                    $diff=round($actual_investor_rtr-$investor_rtr,2);
                    if($diff){
                        echo $count." )".$merchant_id;
                        echo " Investor ".$MerchantUser->user_id." - ";
                        echo " Actual RTR ".$actual_investor_rtr." - ";
                        echo " Investor RTR ".$investor_rtr." - ";
                        echo " Diff ".$diff." - ";
                        echo "\n";
                    }
                    DB::table('merchant_user')
                    ->where('id', $MerchantUser->id)
                    ->update([
                        'invest_rtr'=>$actual_investor_rtr,
                    ]);
                }
            }
        }
        return 0;
    }
}
