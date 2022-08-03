<?php

namespace App\Console\Commands\SingleUse\ProductionAdjustment;

use App\Merchant;
use App\MerchantUser;
use App\Models\Views\MerchantUserView;
use App\PaymentInvestors;
use DB;
use GPH;
use Illuminate\Console\Command;

class AdjustUnmatchedProfitAndPrincipal extends Command
{
    protected $signature = 'adjust:profit_and_prinicpal {greater_than=1} {label=1} {merchantId=""} {investor_id=""}';
    protected $description = 'Update principal profit';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $label = $this->argument('label') ?? '';
        $merchantId = $this->argument('merchantId') ?? '';
        $investorId = $this->argument('investor_id') ?? '';
        $greater_than = $this->argument('greater_than') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $investorId = str_replace('"', '', $investorId);
        if (! $label) {
            $label = [2];
        } else {
            $label = [$this->argument('label')];
        }
        $Merchant = new Merchant;
        if ($merchantId) {
            $Merchant = $Merchant->where('id', $merchantId);
        }
        if ($greater_than) {
            $Merchant = $Merchant->where('id', '>', $greater_than);
        }
        if(!$merchantId){
            $Merchant = $Merchant->whereIn('label', $label);
        }
        $Merchant = $Merchant->whereNotIn('sub_status_id', [4, 18, 19, 20, 22]);
        $Merchants = $Merchant->get();
        $MerchantCount = $Merchant->count();
        $data = [];
        foreach ($Merchants as $Mcount => $MerchantSingle) {
            $Mcount++;
            $count = $MerchantCount - $Mcount;
            DB::beginTransaction();
            $positive_net_effect = 0;
            $negative_net_effect = 0;
            $merchant_id = $MerchantSingle->id;
            echo "\n $count)".$merchant_id.' -';
            $PaymentInvestors = new PaymentInvestors;
            $PaymentInvestors = $PaymentInvestors->where('merchant_id', $merchant_id);
            if ($investorId) {
                $PaymentInvestors = $PaymentInvestors->where('user_id', $investorId);
            }
            $PaymentInvestors=$PaymentInvestors->where(DB::raw('round(participant_share-mgmnt_fee-principal-profit,2)'),'!=',0);
            $PaymentInvestors = $PaymentInvestors->where('participant_share', '!=', 0);
            $PaymentInvestors = $PaymentInvestors->get();
            foreach ($PaymentInvestors as $key => $value) {
                // $MerchantUserView=new MerchantUserView;
                // $MerchantUserView=$MerchantUserView->where('investor_id',$value->user_id);
                // $MerchantUserView=$MerchantUserView->where('merchant_id',$merchant_id);
                // $MerchantUserView=$MerchantUserView->first(['total_investment','investor_id','merchant_id']);
                // $InvestorPrincipal=new PaymentInvestors;
                // $InvestorPrincipal=$InvestorPrincipal->where('merchant_id',$merchant_id);
                // $InvestorPrincipal=$InvestorPrincipal->where('user_id',$value->user_id);
                // $InvestorPrincipal=$InvestorPrincipal->sum('principal');
                // $total_investment=$MerchantUserView->total_investment;
                // $pending_principal=$total_investment-$InvestorPrincipal;
                // $principal_overpaid_amount=0;
                $net_effect = $value->participant_share;
                $net_effect -= $value->mgmnt_fee;
                $net_effect -= $value->principal;
                $net_effect -= $value->profit;
                $net_effect = round($net_effect, 2);
                if ($net_effect > 0) {
                    $positive_net_effect += $net_effect;
                } else {
                    $negative_net_effect += $net_effect;
                }
                // if($InvestorPrincipal>=$total_investment){
                //     $principal_overpaid_amount=round($InvestorPrincipal-$total_investment,2);
                // }
                // $net_amount=$value->participant_share-$value->mgmnt_fee;
                $return = MerchantUser::getPrincipalAndProfitByShare($merchant_id, $value->user_id, $value);
                if (
                    $value->principal != $return['principal'] ||
                    $value->profit != $return['profit']
                ) {
                    echo " user_id =".$value->user_id.",";
                    DB::table('payment_investors')
                    ->where('id', $value->id)
                    ->update([
                        'principal'=>$return['principal'],
                        'profit'   =>$return['profit'],
                    ]);
                }
                // if($value->balance<0){
                // dd($return,$value->toArray());
                // }
            }
            // $MerchantUserView=new MerchantUserView;
            // $MerchantUserView=$MerchantUserView->where('merchant_id',$merchant_id);
            // $MerchantUserView=$MerchantUserView->get(['total_investment','investor_id','merchant_id']);
            echo "\n   : positive_net_effect ".$positive_net_effect;
            echo "\n   : negative_net_effect ".$negative_net_effect;
            
            // foreach ($MerchantUserView as $key => $value) {
            //     $total_investment=$value->total_investment;
            //     $user_id=$value->investor_id;
            //     $total_principal = PaymentInvestors::where('user_id', $user_id)->where('merchant_id', $merchant_id)->sum('principal');
            //     if($total_principal>$total_investment){
            //         $extra=round($total_principal-$total_investment,2);
            //         $PaymentInvestors=new PaymentInvestors;
            //         $PaymentInvestors=$PaymentInvestors->where('user_id',$user_id);
            //         $PaymentInvestors=$PaymentInvestors->where('merchant_id',$merchant_id);
            //         $PaymentInvestors=$PaymentInvestors->where('participant_share','!=',0);
            //         $PaymentInvestors=$PaymentInvestors->where('principal','!=',0);
            //         $PaymentInvestors=$PaymentInvestors->latest()->first();
            //         echo "\n   : ".$user_id." - extra ".$extra;
            //         if($PaymentInvestors){
            //             if($PaymentInvestors->participant_share>0){
            //                 $PaymentInvestors->principal-=$extra;
            //                 $PaymentInvestors->profit   +=$extra;
            //                 $PaymentInvestors->save();
            //             } else {
            //                 dd($PaymentInvestors,$PaymentInvestors->principal,$extra);
            //                 $PaymentInvestors->principal+=$extra;
            //                 $PaymentInvestors->profit   -=$extra;
            //                 $PaymentInvestors->save();
            //             }
            //         }
            //     }
            // }
            DB::commit();
        }
        
        return 0;
    }
}
