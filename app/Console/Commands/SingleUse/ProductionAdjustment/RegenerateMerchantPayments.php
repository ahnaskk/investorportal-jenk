<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\Merchant;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\MerchantUser;
use App\ReassignHistory;
use App\User;
use App\Models\Views\MerchantUserView;
use DB;
use Artisan;
use GPH;
use FFM;
use PayCalc;
use App\Providers\DashboardServiceProvider;
class RegenerateMerchantPayments extends Command
{
    protected $signature = 'regenerate:merchant_payments {greater_than=1} {merchantId=""} {investorId=""} {type="full"}';
    protected $description = 'Regenerate Merchant Payments';
    public function __construct() {
        parent::__construct();
    }
    public function handle() {
        echo "\n investor_ids Save Started";
        Artisan::call('update:investor_ids');
        echo "\n investor_ids Save ended";
        $ReassignHistoryMerchant_ids=ReassignHistory::pluck('merchant_id','merchant_id')->toArray();
        $label = [1, 2];
        $greater_than = $this->argument('greater_than') ?? '';
        $merchantId   = $this->argument('merchantId') ?? '';
        $investorId   = $this->argument('investorId') ?? '';
        $type         = $this->argument('type') ?? '';
        $merchantId   = str_replace('"', '', $merchantId);
        $investorId   = str_replace('"', '', $investorId);
        $type         = str_replace('"', '', $type);
        $Merchant = new Merchant;
        if ($merchantId) { $Merchant = $Merchant->where('id', $merchantId); }
        if ($greater_than) { $Merchant = $Merchant->where('id', '>', $greater_than); }
        // $Merchant = $Merchant->where('date_funded', '<', '2019-01-01');
        $Merchant = $Merchant->where('complete_percentage', '<=', '100');
        $Merchant = $Merchant->orderBy('paid_count');
        $Merchant = $Merchant->whereIn('label', $label);
        $Merchant = $Merchant->whereNotIn('id',$ReassignHistoryMerchant_ids);
        if ($investorId) { 
            $userMerchantIds = PaymentInvestors::where('user_id',$investorId);
            $userMerchantIds = $userMerchantIds->where('participant_share','!=',0);
            $userMerchantIds = $userMerchantIds->pluck('merchant_id','merchant_id')->toArray();
            $Merchant = $Merchant->whereIn('id', $userMerchantIds);
        }
        $Merchants     = $Merchant->get();
        $MerchantCount = $Merchant->count();
        $merchant_ids=[];
        foreach ($Merchants as $Mcount => $Merchant) {
            $Mcount++;
            $count = $MerchantCount - $Mcount;
            $merchant_id   = $Merchant->id;
            $sub_status_id = $Merchant->sub_status_id;
            $complete_percentage=$Merchant->complete_percentage;
            echo "\n $count ) $merchant_id";
            $ParticipentPayments = ParticipentPayment::where('merchant_id',$merchant_id);
            // $ParticipentPayments = $ParticipentPayments->where('id','>=',789652);
            $ParticipentPayments = $ParticipentPayments->where('model','=','App\ParticipentPayment');
            $ParticipentPayments = $ParticipentPayments->where('payment','!=',0);
            $ParticipentPaymentsAll = $ParticipentPayments->orderBy('id');
            $ParticipentPaymentsAll = $ParticipentPayments->get();
            $ParticipentPaymentCount = $ParticipentPayments->count();
            try {
                DB::beginTransaction();
                // $type="single";
                if($type=="full"){
                    PaymentInvestors::where('merchant_id', $merchant_id)->whereIn('participent_payment_id',$ParticipentPayments->pluck('id','id')->toArray())->delete();
                    GPH::PaymentToMarchantUserSync($merchant_id);
                }
                foreach ($ParticipentPaymentsAll as $Pcount => $ParticipentPayment) {
                    $revetedPayment         = false;
                    $payment                = $ParticipentPayment->payment;
                    $revert_id              = $ParticipentPayment->revert_id;
                    $PCount                 = $ParticipentPaymentCount - $Pcount;
                    $participent_payment_id = $ParticipentPayment->id;
                    if($type=="single"){
                        PaymentInvestors::where('participent_payment_id', $participent_payment_id)->delete();
                        GPH::PaymentToMarchantUserSync($merchant_id);
                    }
                    echo "\n $PCount)Date :".FFM::date($ParticipentPayment->payment_date)." ,";
                    echo "Payment :".$ParticipentPayment->payment." ,";
                    if($payment<0){
                        $RevertCheck=ParticipentPayment::whererevert_id($participent_payment_id)->first();
                        if($RevertCheck){
                            $revetedPayment  = true;
                        }
                    }
                    reGeneratePaymentBeginingArea:
                    if(!$revetedPayment){
                        $return_array = GPH::reGeneratePayment($participent_payment_id);
                        $complete_percentage=PayCalc::completePercentage($merchant_id);
                        echo " ,Percentage :".$complete_percentage;
                        if ($return_array['result'] != 'success') {
                            if (strpos($return_array['result'], 'Please select all companies') !== false) {
                                if ($complete_percentage >= 99.99) {
                                    $investor_ids = GPH::getMerchantInvestorByMerchantId($merchant_id);
                                    $ParticipentPayment->investor_ids=implode(',',$investor_ids);
                                    $ParticipentPayment->save();
                                    goto reGeneratePaymentBeginingArea;
                                }
                            }
                            throw new \Exception($return_array['result'], 1);
                        }
                    } else {
                        $this->RevertFunction($RevertCheck->id,$participent_payment_id);
                        GPH::PaymentToMarchantUserSync($merchant_id);
                    }
                    // if($complete_percentage<100){
                    //     $Overpayment=PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id',504)->sum('overpayment');
                    //     $Overpayment=round($Overpayment,2);
                    //     if($Overpayment){
                    //         $Overpayments=PaymentInvestors::where('merchant_id', $merchant_id)->select('user_id','participant_share','overpayment','participent_payment_id')->where('user_id',504)->get();
                    //         dd("\n Overpayment ".$Overpayment,$Overpayments->toArray());
                    //     }
                    // }
                    $MerchantUpdateData['complete_percentage'] = $complete_percentage;
                    $Merchant->update($MerchantUpdateData);
                    echo ", Final Check";
                    $FinalPaymentInvestors=PaymentInvestors::where('participent_payment_id',$participent_payment_id)->get();
                    foreach ($FinalPaymentInvestors as $key => $value) {
                        $flag=true;
                        $participant_share         =round($value->participant_share,2);
                        $expected_mgmnt_fee_amount =round($value->MerchantUser->mgmnt_fee*$value->participant_share/100,2);
                        $net_effect                =$participant_share-$value->mgmnt_fee-$value->principal-$value->profit;
                        $net_effect                =round($net_effect,2);
                        if($ParticipentPayment->payment!=0){
                            switch ($ParticipentPayment->payment) {
                                case ($ParticipentPayment->payment > 0) :
                                if($value->user_id==504){
                                    if($value->mgmnt_fee==0){ $check['mgmnt_fee']='Ok';} else { $check['mgmnt_fee']='Not Ok';$flag=false; }
                                    if($value->principal==0){ $check['principal']="Ok "; } else { $check['principal']="Not Ok "; $flag=false; }
                                } else {
                                    if($expected_mgmnt_fee_amount){
                                        if($value->mgmnt_fee>0){ $check['mgmnt_fee']='Ok';} else { $check['mgmnt_fee']='Not Ok';$flag=false; }
                                    } else {
                                        if($value->mgmnt_fee==0){ $check['mgmnt_fee']='Ok';} else { $check['mgmnt_fee']='Not Ok';$flag=false; }
                                    }
                                    if($value->principal>0){ $check['principal']="Ok "; } else { $check['principal']="Not Ok "; $flag=false; }
                                }
                                if($value->profit>0){ $check['profit']="Ok"; } else { $check['profit']="Not Ok"; $flag=false; }
                                break;
                                case ($ParticipentPayment->payment < 0) :
                                if($value->user_id==504){
                                    if($value->mgmnt_fee==0){ $check['mgmnt_fee']='Ok';} else { $check['mgmnt_fee']='Not Ok';$flag=false; }
                                    if($value->principal==0){ $check['principal']="Ok"; } else { $check['principal']="Not Ok"; $flag=false; }
                                } else {
                                    if($expected_mgmnt_fee_amount){
                                        if($value->mgmnt_fee<0){ $check['mgmnt_fee']='Ok';} else { $check['mgmnt_fee']='Not Ok';$flag=false; }
                                    } else {
                                        if($value->mgmnt_fee==0){ $check['mgmnt_fee']='Ok';} else { $check['mgmnt_fee']='Not Ok';$flag=false; }
                                    }
                                    if($value->principal<0){ $check['principal']="Ok"; } else { $check['principal']="Not Ok"; $flag=false; }
                                }
                                if($value->profit<0){ $check['profit']="Ok"; } else { $check['profit']="Not Ok"; $flag=false; }
                                break;
                            }
                        } else {
                            if($value->mgmnt_fee==0){ $check['mgmnt_fee']='Ok';} else { $check['mgmnt_fee']='Not Ok';$flag=false; }
                            if($value->principal==0){ $check['principal']="Ok "; } else { $check['principal']="Not Ok "; $flag=false; }
                            if($value->profit==0){ $check['profit']="Ok "; } else { $check['profit']="Not Ok "; $flag=false; }
                        }
                        if($net_effect==0){ $flag=true; }
                        if(!$flag){
                            echo "\n user_id ".$value->user_id." ".$ParticipentPayment->payment;
                            echo ' mgmnt_fee '.$check['mgmnt_fee'];
                            echo ' principal '.$check['principal'];
                            echo ' profit '.$check['profit'];
                            // dd($value->toArray());
                            // dd($value->user_id);
                        }
                    }
                    echo " => Success,";
                }
                if(in_array($sub_status_id,[4, 18, 19, 20, 22])){
                    $ParticipentPayment = ParticipentPayment::where('merchant_id',$merchant_id);
                    $ParticipentPayment = $ParticipentPayment->where('model','=','App\ParticipentPayment');
                    $ParticipentPayment = $ParticipentPayment->where('payment','=',0);
                    $ParticipentPayment = $ParticipentPayment->latest();
                    $ParticipentPayment = $ParticipentPayment->first();
                    if($ParticipentPayment){
                        $return_array=PaymentInvestors::DefaultMerchantAdjustmentValues($ParticipentPayment->id);
                        if ($return_array['result'] != 'success') {
                            throw new \Exception($return_array['result'], 1);
                        }
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                echo " => ".$e->getMessage().',';
                DB::rollback();
            }
            echo "\n";
            $merchant_ids[]=$merchant_id;
            echo "\n Merchant Sync Started \n";
            Artisan::call('sync:merchant_user '.$merchant_id);
            echo "Merchant Sync ended";
        }
        Artisan::call('Update:UserDetailLiquidity');
        $user_ids=MerchantUser::whereIn('merchant_id', $merchant_ids)->pluck('user_id','user_id')->toArray();
        DashboardServiceProvider::addInvestorPaymentJob($user_ids);
        return 0;
    }
    public function RevertFunction($participent_payment_id,$revert_participent_payment_id)
    {
        $PaymentInvestors   = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->get();
        $ParticipentPayment = ParticipentPayment::find($revert_participent_payment_id);
        foreach ($PaymentInvestors as $key => $value) {
            $balance=round($value->MerchantUser->invest_rtr - $value->MerchantUser->paid_participant_ishare + $value->participant_share, 2);
            $revertValue['user_id']                  = $value->user_id;
            $revertValue['merchant_id']              = $value->merchant_id;
            $revertValue['investment_id']            = $value->investment_id;
            $revertValue['participent_payment_id']   = $revert_participent_payment_id;
            $revertValue['participant_share']        = -$value->participant_share;
            $revertValue['actual_participant_share'] = -$value->actual_participant_share;
            $revertValue['mgmnt_fee']                = -$value->mgmnt_fee;
            $revertValue['syndication_fee']          = -$value->syndication_fee;
            $revertValue['actual_overpayment']       = -$value->actual_overpayment;
            $revertValue['agent_fee']                = -$value->agent_fee;
            $revertValue['overpayment']              = -$value->overpayment;
            $revertValue['balance']                  = $balance;
            $revertValue['principal']                = -$value->principal;
            $revertValue['profit']                   = -$value->profit;
            DB::table('payment_investors')->insert($revertValue);
        }
    }
}
          
