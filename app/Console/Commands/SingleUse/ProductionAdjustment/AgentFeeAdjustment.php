<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\User;
use App\MerchantUser;
use App\ParticipentPayment;
use App\PaymentInvestors;
use GPH;
use DB;
class AgentFeeAdjustment extends Command
{
    protected $signature = 'agentfee:update {merchantId=""}';
    protected $description = 'Update the agent fee by latest calculation method';
    public function __construct() {
        parent::__construct();
    }
    public function handle() {
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $AgentFeeIds   = User::AgentFeeIds();
        $MerchantUsers = MerchantUser::whereIn('user_id',$AgentFeeIds);
        $MerchantUsers = $MerchantUsers->where('paid_profit','!=',0);
        $MerchantUsers = $MerchantUsers->orderBy('merchant_id');
        $MerchantUsers = $MerchantUsers->whereIn('merchant_id',[9042,9049,9088,9306,9323,9479,9498,9518,9709,9741,9747,9753,9754,9775,9784,9794,9814,9849,9862,9863,9938,9953]);
        
        if($merchantId){
            $MerchantUsers = $MerchantUsers->where('merchant_id',$merchantId);
        }
        $MerchantUsers = $MerchantUsers->get();
        foreach ($MerchantUsers as $key => $MerchantUser) {
            if(in_array($MerchantUser->merchant->sub_status_id,[4,18,20,21,22])){
                $LastParticipentPayment = new ParticipentPayment;
                $LastParticipentPayment = $LastParticipentPayment->where('merchant_id',$MerchantUser->merchant_id);
                $LastParticipentPayment = $LastParticipentPayment->orderByDesc('id');
                $LastParticipentPayment = $LastParticipentPayment->first();
                $PaymentInvestors = new PaymentInvestors;
                $PaymentInvestors = $PaymentInvestors->where('participent_payment_id',$LastParticipentPayment->id);
                $PaymentInvestors = $PaymentInvestors->where('merchant_id',$LastParticipentPayment->merchant_id);
                $PaymentInvestors = $PaymentInvestors->where('participant_share',0);
                $PaymentInvestors = $PaymentInvestors->where('principal','!=',0);
                $PaymentInvestors = $PaymentInvestors->whereRaw('principal+profit=0');
                $PaymentInvestors->update([
                    'profit'    => 0,
                    'principal' => 0,
                ]);
                GPH::PaymentToMarchantUserSync($MerchantUser->merchant_id);
            }
            echo "\n Merchant Id : ".$MerchantUser->merchant_id;
            $ParticipentPayments = new ParticipentPayment;
            $ParticipentPayments = $ParticipentPayments->where('merchant_id',$MerchantUser->merchant_id);
            $ParticipentPayments = $ParticipentPayments->where('agent_fee_percentage','!=',0);
            $ParticipentPayments = $ParticipentPayments->orderBy('id');
            $ParticipentPayments = $ParticipentPayments->get();
            foreach ($ParticipentPayments as $key => $ParticipentPayment) {
                $participent_payment_id = $ParticipentPayment->id;
                $PaymentInvestors = new PaymentInvestors;
                $PaymentInvestors = $PaymentInvestors->where('participent_payment_id',$ParticipentPayment->id);
                $PaymentInvestors = $PaymentInvestors->where('merchant_id',$ParticipentPayment->merchant_id);
                $PaymentInvestorsUpdate = clone $PaymentInvestors;
                $PaymentInvestors = $PaymentInvestors->get();
                $agent_fee        = $PaymentInvestors->sum('agent_fee');
                if(round($ParticipentPayment->payment)){
                    if($agent_fee>0){
                        $PaymentInvestorsUpdate->update([
                            'profit'    => 0,
                            'principal' => 0,
                        ]);
                        GPH::ProfitandPrincipleUpdate($participent_payment_id);
                        GPH::PaymentToMarchantUserSync($MerchantUser->merchant_id);
                    } else {
                        if($agent_fee){
                            $originalParticipentPayment = ParticipentPayment::where('revert_id',$ParticipentPayment->id)->first();
                            if($originalParticipentPayment){
                                foreach ($PaymentInvestors as $key => $PaymentInvestor) {
                                    $originalPaymentInvestors = PaymentInvestors::where('participent_payment_id',$originalParticipentPayment->id);
                                    $originalPaymentInvestors = $originalPaymentInvestors->where('merchant_id',$PaymentInvestor->merchant_id);
                                    $originalPaymentInvestors = $originalPaymentInvestors->where('user_id',$PaymentInvestor->user_id);
                                    $originalPaymentInvestors = $originalPaymentInvestors->first();
                                    if($originalPaymentInvestors){
                                        $PaymentInvestor->profit    = -1*$originalPaymentInvestors->profit;
                                        $PaymentInvestor->principal = -1*$originalPaymentInvestors->principal;
                                        $PaymentInvestor->save();
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if(in_array($MerchantUser->merchant->sub_status_id,[4,18,20,21,22])){
                        GPH::PaymentToMarchantUserSync($MerchantUser->merchant_id);
                        $PaymentInvestors = new PaymentInvestors;
                        $PaymentInvestors = $PaymentInvestors->where('participent_payment_id',$LastParticipentPayment->id);
                        $PaymentInvestors = $PaymentInvestors->where('merchant_id',$LastParticipentPayment->merchant_id);
                        $PaymentInvestorsUpdate = clone $PaymentInvestors;
                        $PaymentInvestors = $PaymentInvestors->get();
                        if($PaymentInvestorsUpdate->count()){
                            foreach ($PaymentInvestors as $key => $PaymentInvestor) {
                                $investor = MerchantUser::select('id', 'user_id','invest_rtr','paid_participant_ishare','paid_principal','paid_profit', DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'));
                                $investor = $investor->where('merchant_user.merchant_id', $PaymentInvestor->merchant_id);
                                $investor = $investor->where('merchant_user.user_id', $PaymentInvestor->user_id);
                                $investor = $investor->first();
                                $principal = 0;
                                $profit    = 0;
                                if($investor['invest_rtr']>$investor['paid_participant_ishare']){
                                    if($investor['paid_participant_ishare'] >= $investor['investment_amount']){
                                        $profit = $investor['investment_amount'] - $investor['paid_principal'];
                                        if($profit>$investor['paid_profit']){ $profit=$investor['paid_profit']; }
                                        if($profit>0){
                                            $profit = round(-$profit,2);
                                        } else {
                                            $profit = round($profit,2);
                                        }
                                    } else {
                                        $profit = -$investor['paid_profit'];
                                        if($profit>0){
                                            $profit = round(-$profit,2);
                                        } else {
                                            $profit = round($profit,2);
                                        }
                                    }
                                    $principal = $profit*-1;
                                }
                                $PaymentInvestor->profit    = $profit;
                                $PaymentInvestor->principal = $principal;
                                $PaymentInvestor->save();
                            }
                        }
                    }
                }
            }
        }
        return 0;
    }
}
