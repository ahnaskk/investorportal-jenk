<?php

namespace App\Console\Commands\Adjustment;

use App\CompanyAmount;
use App\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MerchantBasedCompanyAmountDifference extends Command
{
    protected $signature = 'Update:MerchantBasedCompanyAmountDifference';
    protected $description = 'Update Company Amount To Merchant Funded Amount';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $merchants = DB::table('company_amount_pivot_check_view');
        // $merchants = $merchants->where('merchant_id',9705);
        $merchants = $merchants->select('merchant_id');
        $merchants = $merchants->join('merchants', 'merchants.id', 'company_amount_pivot_check_view.merchant_id');
        $merchants = $merchants->whereIn('merchants.label', [1, 2]);
        // $merchants = $merchants->whereIn('merchants.sub_status_id', [1, 5]);
        // $merchants = $merchants->where('merchant_company_diff', '!=', '0');
        // $merchants = $merchants->where('percentage', '!=', '100');
        // $merchants = $merchants->where('company_amount_pivot_check_view.complete_percentage', '!=', '100');
        $merchants = $merchants->pluck('merchant_id', 'merchant_id')->toArray();
        foreach ($merchants as $merchant_id) {
            $company_merchant_investor_amount = DB::table('company_amount_check_view')
            ->where('merchant_id', $merchant_id)
            ->first();
            $max_participant_fund = DB::table('company_amount')->where('merchant_id', $merchant_id)->sum('max_participant');
            $Merchant = Merchant::find($merchant_id);
            $Merchant->max_participant_fund = $max_participant_fund;
            if ($Merchant->max_participant_fund != $max_participant_fund) {
                // if($company_merchant_investor_amount->percentage==100){
                //     $Merchant->funded         =round($Merchant->max_participant_fund/$company_merchant_investor_amount->percentage*100,4);
                // }
                // if($Merchant->max_participant_fund>$Merchant->funded){
                //  $Merchant->funded         =$Merchant->max_participant_fund;
                // }
                // $Merchant->rtr                 =round($Merchant->funded*$Merchant->factor_rate,4);
                // $Merchant->payment_amount      =round($Merchant->rtr/$Merchant->pmnts,4);
            }
            $Merchant->save();
        }

        return 0;
    }
}
