<?php
namespace App\Console\Commands\SingleUse;
use Illuminate\Console\Command;
use App\MerchantUser;
use App\Merchant;
use DB;
class SyncProfitPrincipalValue extends Command
{
    protected $signature = 'sync:profit_and_principal {greater_than=1} {merchantId=""} {investor_id=""}';
    protected $description = 'Sync Profit And Principal Value To merchant_user From payment investors ';
    public function __construct() {
        parent::__construct();
    }
    public function handle() {
        $greater_than = $this->argument('greater_than') ?? '';
        $merchantId   = $this->argument('merchantId') ?? '';
        $investorId   = $this->argument('investor_id') ?? '';
        $greater_than = str_replace('"', '', $greater_than);
        $merchantId   = str_replace('"', '', $merchantId);
        $investorId   = str_replace('"', '', $investorId);
        $Merchant=new Merchant;
        $Merchants=$Merchant->get(['id']);
        if ($greater_than) {
            $Merchants = $Merchants->where('id', '>', $greater_than);
        }
        if ($merchantId) {
            $Merchants = $Merchants->where('id', $merchantId);
        }
        $MerchantCount=$Merchant->count();
        foreach ($Merchants as $Mkey => $value) {
            DB::beginTransaction();
            $merchant_id=$value->id;
            $count=$MerchantCount-$Mkey;
            echo "\n$count)".$merchant_id;
            $Tpayment_investors =DB::table('payment_investors');
            $Tpayment_investors =$Tpayment_investors->where('merchant_id',$merchant_id);
            if ($investorId) {
                $Tpayment_investors = $Tpayment_investors->where('user_id', $investorId);
            }
            $Tpaid_principal    =$Tpayment_investors->sum('principal');
            $Tpaid_profit       =$Tpayment_investors->sum('profit');
            $Tmerchant_user=new MerchantUser;
            $Tmerchant_user=$Tmerchant_user->where('merchant_user.merchant_id', $merchant_id);
            if ($investorId) {
                $Tmerchant_user = $Tmerchant_user->where('user_id', $investorId);
            }
            $Mpaid_principal=$Tmerchant_user->sum('paid_principal');
            $Mpaid_profit=$Tmerchant_user->sum('paid_profit');
            if($Mpaid_principal || $Mpaid_profit){
                $PrincipalDiff=round($Mpaid_principal-$Tpaid_principal,2);
                $ProfitDiff=round($Mpaid_profit-$Tpaid_profit,2);
                if($PrincipalDiff || $ProfitDiff){
                    echo "\n PrincipalDiff : ".$PrincipalDiff;
                    echo "\n ProfitDiff    : ".$ProfitDiff;
                    $MerchantUser=new MerchantUser;
                    $MerchantUser=$MerchantUser->where('merchant_id',$merchant_id);
                    if ($investorId) {
                        $MerchantUser = $MerchantUser->where('user_id', $investorId);
                    }
                    $MerchantUser=$MerchantUser->pluck('user_id','user_id');
                    foreach ($MerchantUser as $user_id) {
                        $payment_investors =DB::table('payment_investors');
                        $payment_investors =$payment_investors->where('merchant_id',$merchant_id);
                        $payment_investors =$payment_investors->where('user_id',$user_id);
                        $paid_principal    =$payment_investors->sum('principal');
                        $paid_profit       =$payment_investors->sum('profit');
                        $merchant_user=new MerchantUser;
                        $merchant_user=$merchant_user->where('merchant_user.user_id', $user_id);
                        $merchant_user=$merchant_user->where('merchant_user.merchant_id', $merchant_id);
                        $merchant_user=$merchant_user->first(['paid_principal','paid_profit']);
                        $single_Principal_diff=round($merchant_user->paid_principal-$paid_principal,2);
                        $single_Profit_diff=round($merchant_user->paid_profit-$paid_profit,2);
                        if( $single_Principal_diff || $single_Profit_diff){
                            echo "\n user_id : ".$user_id;
                            echo " -> Principal -> ".$single_Principal_diff;
                            echo " -> Profit -> ".$single_Profit_diff;
                            DB::table('merchant_user')
                            ->where('merchant_user.user_id', $user_id)
                            ->where('merchant_user.merchant_id', $merchant_id)
                            ->update([
                                'paid_principal' =>$paid_principal,
                                'paid_profit'    =>$paid_profit,
                            ]);
                        }
                    }
                }
            }
            DB::commit();
        }
        DB::table('merchant_user')
        ->select('merchant_user.user_id')
        ->whereNull('merchant_user.paid_principal')
        ->update([
            'paid_principal'=>0,
        ]);
        DB::table('merchant_user')
        ->select('merchant_user.user_id')
        ->whereNull('merchant_user.paid_profit')
        ->update([
            'paid_profit'   =>0,
        ]);
        return 0;
    }
}
