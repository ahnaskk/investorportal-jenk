<?php
namespace App\Console\Commands\SingleUse;
use Illuminate\Console\Command;
use App\MerchantUser;
use App\Merchant;
use DB;
class SyncParticipantShareManagementFeeValue extends Command
{
    protected $signature = 'sync:participant_share_and_mgmnt_fee {greater_than=1} {merchantId=""}';
    protected $description = 'Sync Participant Share And Management Fee To merchant_user From payment investors ';
    public function __construct() {
        parent::__construct();
    }
    public function handle() {
        $greater_than = $this->argument('greater_than') ?? '';
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
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
            $Tparticipant_share =$Tpayment_investors->sum('participant_share');
            $Tmgmnt_fee         =$Tpayment_investors->sum('mgmnt_fee');
            $Tmerchant_user           =new MerchantUser;
            $Tmerchant_user           =$Tmerchant_user->where('merchant_user.merchant_id', $merchant_id);
            $Tpaid_participant_ishare =round($Tmerchant_user->sum('paid_participant_ishare'),2);
            $Tpaid_mgmnt_fee          =round($Tmerchant_user->sum('paid_mgmnt_fee'),2);
            if($Tpaid_participant_ishare || $Tpaid_mgmnt_fee){
                $diff1=round($Tpaid_participant_ishare-$Tparticipant_share,2);
                $diff2=round($Tpaid_mgmnt_fee-$Tmgmnt_fee,2);
                if($diff1 || $diff2){
                    echo "\n    Diff : S ".$diff1.' / F '.$diff2;
                    $MerchantUser=MerchantUser::where('merchant_id',$merchant_id)->pluck('user_id','user_id');
                    foreach ($MerchantUser as $user_id) {
                        $payment_investors =DB::table('payment_investors');
                        $payment_investors =$payment_investors->where('merchant_id',$merchant_id);
                        $payment_investors =$payment_investors->where('user_id',$user_id);
                        $participant_share =$payment_investors->sum('participant_share');
                        $mgmnt_fee         =$payment_investors->sum('mgmnt_fee');
                        $merchant_user=new MerchantUser;
                        $merchant_user=$merchant_user->where('merchant_user.user_id', $user_id);
                        $merchant_user=$merchant_user->where('merchant_user.merchant_id', $merchant_id);
                        $merchant_user=$merchant_user->first(['paid_participant_ishare','paid_mgmnt_fee']);
                        $single_diff1=round($merchant_user->paid_participant_ishare-$participant_share,2);
                        $single_diff2=round($merchant_user->paid_mgmnt_fee-$mgmnt_fee,2);
                        if( $single_diff1 || $single_diff2 ){
                            echo "\n user_id : ".$user_id." -> S ".$single_diff1 ." -> F ".$single_diff2;
                            DB::table('merchant_user')
                            ->where('merchant_user.user_id', $user_id)
                            ->where('merchant_user.merchant_id', $merchant_id)
                            ->update([
                                'paid_participant_ishare'        =>$participant_share,
                                'actual_paid_participant_ishare' =>$participant_share,
                                'paid_mgmnt_fee'                 =>$mgmnt_fee,
                            ]);
                        }
                    }
                }
            }
            DB::commit();
        }
        DB::table('merchant_user')
        ->select('merchant_user.user_id')
        ->whereNull('merchant_user.paid_participant_ishare')
        ->update([
            'paid_participant_ishare'=>0,
        ]);
        DB::table('merchant_user')
        ->select('merchant_user.user_id')
        ->whereNull('merchant_user.paid_mgmnt_fee')
        ->update([
            'paid_mgmnt_fee' =>0,
        ]);
        return 0;
    }
}
