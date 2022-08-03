<?php
namespace App\Library\Helpers;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Jobs\PaymentCreateCRM;
use App\Library\Repository\InvestorTransactionRepository;
use App\LiquidityLog;
use App\Merchant;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\Models\Transaction;
use App\Models\MerchantAgentAccountHistory;
use App\Models\Views\MerchantUserView;
use App\Models\Views\CompanyAmountView;
use App\Models\Views\PaymentInvestorsView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\PaymentPause;
use App\Rcode;
use App\Label;
use App\Settings;
use App\SubStatus;
use App\Template;
use App\TermPaymentDate;
use App\User;
use App\UserDetails;
use App\ReassignHistory;
use Artisan;
use FFM;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PayCalc;
use InvestorHelper;
use App\ReserveLiquidity;
use App\Jobs\LiquidtyUpdate;
use Illuminate\Support\Facades\Schema;
class GeneratePaymentHelper
{
    public function __construct()
    {   
        if(Schema::hasTable('users')){
        $this->overpayment_id = User::OverpaymentId();
        $this->agent_fee_id   = User::AgentFeeId();
        }
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }
    public function InvestorPaymentShare($merchant_id,$investors,$actual_payment,$overpayment_id)
    {
        $data=[];
        $total_invest_rtr = $investors->sum('invest_rtr');
        $total_balance    = round($investors->sum('balance'),2);
        $max_share        = MerchantUserView::select(DB::raw('funded/sum(amount) as max_share'))->where('merchant_id', $merchant_id)->first()->max_share;
        $payment          = ($max_share>0)?round(($actual_payment / $max_share), 2):0;
        $payment          = round($payment,2);
        $Based="RTR";
        if($total_invest_rtr){
            InvestorShareBegin :
            if($payment<0){
                $OverpaymentMerchantUser=MerchantUser::where('merchant_id', $merchant_id)->where('user_id',$overpayment_id)->first();
                $overpayment=$OverpaymentMerchantUser->paid_participant_ishare;
                if($overpayment){
                    if(abs($payment)>$overpayment){
                        $payment += $overpayment;
                        $payment  = round($payment,2);
                        $PaymentInvestorSingleDate['participant_share'] = -round($overpayment,2);
                    } else {
                        $PaymentInvestorSingleDate['participant_share'] = round($payment,2);
                        $payment = 0;
                    }
                    $PaymentInvestorSingleDate['balance']       = $overpayment;
                    $PaymentInvestorSingleDate['user_id']       = $OverpaymentMerchantUser->user_id;
                    $PaymentInvestorSingleDate['investment_id'] = $OverpaymentMerchantUser->id;
                    $PaymentInvestorSingleDate['overpayment']   = $PaymentInvestorSingleDate['participant_share'];
                    $data[$OverpaymentMerchantUser->user_id]    = $PaymentInvestorSingleDate;
                }
            }
            foreach ($investors as $single) {
                $MerchantUserView = MerchantUserView::wheremerchant_id($merchant_id)->whereinvestor_id($single->user_id)->first();
                $rtr_share         = $single->invest_rtr/$total_invest_rtr*100;
                $balance_share     = $total_balance?$single->balance/$total_balance*100:0;
                $balance_share     = round($balance_share,6);
                $participant_share = 0;
                if($Based=="RTR"){
                    $participant_share = round($payment*$rtr_share/100,2);
                } else {
                    if($balance_share){
                        $participant_share = round($payment*$balance_share/100,2);
                    }
                }
                $participant_share = PaymentInvestors::shareCheck($single->user_id,$participant_share,$MerchantUserView);
                $PaymentInvestorSingleDate['balance']           = $single->balance;
                $PaymentInvestorSingleDate['user_id']           = $single->user_id;
                $PaymentInvestorSingleDate['investment_id']     = $single->id;
                $PaymentInvestorSingleDate['overpayment']       = 0;
                $PaymentInvestorSingleDate['participant_share'] = round($participant_share,2);
                $data[$single->user_id]                         = $PaymentInvestorSingleDate;
            }
            $given_share = round(array_sum(array_column($data,'participant_share')),2);
            $diff        = $payment-$given_share;
            if(isset($data[$overpayment_id])){
                $diff   += $data[$overpayment_id]['participant_share'];
            }
            $diff        = round($diff,2);
            if($Based=="RTR"){
                if(abs($diff)>1){
                    $Based="Balance";
                    goto InvestorShareBegin;
                }
            }

            if($diff){
                foreach ($investors as $single) {
                    if($diff==0) break;
                    $MerchantUserView     = MerchantUserView::wheremerchant_id($merchant_id)->whereinvestor_id($single->user_id)->first();
                    $OldParticipant_share = $data[$single->user_id]['participant_share'];
                    $participant_share    = $OldParticipant_share+$diff;
                    $NewParticipant_share = $participant_share;
                    if($payment>0){
                        if($diff>0){
                            $NewParticipant_share = PaymentInvestors::shareCheck($single->user_id,$participant_share,$MerchantUserView);
                        }
                    } else {
                        if($diff<0){
                            $NewParticipant_share = PaymentInvestors::shareCheck($single->user_id,$participant_share,$MerchantUserView);
                        }
                    }
                    if($NewParticipant_share!=$OldParticipant_share){
                        $data[$single->user_id]['participant_share']=$NewParticipant_share;
                    }
                    $given_share = round(array_sum(array_column($data,'participant_share')),2);
                    $diff        = round($payment-$given_share,2);
                }
            }
        }
        return $data;
    }
    public function PaymentInvestorCreate($investors, $merchant_id, $participent_payment_id)
    {
        $ParticipentPayment = ParticipentPayment::find($participent_payment_id);
        $actual_payment     = $ParticipentPayment->payment;
        $investor_payment_arr = [];
        $PaymentInvestorSingleDate['merchant_id']            = $merchant_id;
        $PaymentInvestorSingleDate['participent_payment_id'] = $participent_payment_id;
        $PaymentInvestorSingleDate['overpayment']            = 0;
        $data=$this->InvestorPaymentShare($merchant_id,$investors,$actual_payment,$this->overpayment_id);
        foreach ($data as $key => $sinlge) {
            $sinlge += $PaymentInvestorSingleDate;
            $sinlge  = array_merge($sinlge,$PaymentInvestorSingleDate);
            PaymentInvestors::create($sinlge);
        }
    }
    public function AdjustmentOverPaymentfunction($Merchant, $participent_payment_id, $actual_payment, $max_participant_fund_per)
    {
        if (! $this->overpayment_id) {
            throw new \Exception('please create overpayment account', 1);
        }
        $merchant_id = $Merchant->id;
        $rtr = $Merchant->rtr;
        $rtr = round($rtr,2);
        $investor_paid_part = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->sum('participant_share');
        $selectedInvestors  = PaymentInvestors::where('participent_payment_id', $participent_payment_id);
        $selectedInvestors  = $selectedInvestors->where('user_id','!=',$this->overpayment_id);
        $selectedInvestors  = $selectedInvestors->where('participant_share','!=',0);
        $selectedInvestors  = $selectedInvestors->pluck('user_id','user_id')->toArray();
        $AllMerchantInvestors = MerchantUser::where('merchant_id', $merchant_id)->where('invest_rtr','!=',0)->pluck('user_id','user_id')->toArray();
        if(empty($selectedInvestors)){ $selectedInvestors=$AllMerchantInvestors; }
        $payment = ($max_participant_fund_per>0)?round($actual_payment / $max_participant_fund_per, 2):0;
        $overpayment = round(($payment - $investor_paid_part), 2);
        try {
            $PaymentInvestorSingleDate['merchant_id'] = $merchant_id;
            $PaymentInvestorSingleDate['participent_payment_id'] = $participent_payment_id;
            if ($overpayment) {
                $MerchantUser = new MerchantUserView;
                $MerchantUser = $MerchantUser->where('investor_id', '!=', $this->overpayment_id);
                $MerchantUser = $MerchantUser->where('investor_id', '!=', $this->agent_fee_id);
                $MerchantUser = $MerchantUser->whereIn('investor_id',$selectedInvestors);
                $MerchantUser = $MerchantUser->whereIn('status', [1, 3]);
                $MerchantUser = $MerchantUser->where('merchant_id', $merchant_id);
                $MerchantUser = $MerchantUser->where('invest_rtr', '!=', 0);
                $MerchantUser = $MerchantUser->select('share', 'investor_id', 'invest_rtr', 'paid_participant_ishare', 'user_balance_amount', 'total_agent_fee');
                if ($payment < 0) {
                    $OverpaymentUser = DB::table('merchant_user_views')->where('investor_id', $this->overpayment_id)->where('merchant_id', $merchant_id)->first(['paid_participant_ishare']);
                    $overPaidValue   = $OverpaymentUser->paid_participant_ishare;
                    $AdjustmentPaymentInvestors = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->where('user_id', $this->overpayment_id)->first();
                    if($AdjustmentPaymentInvestors){
                        $overPaidValue+=$AdjustmentPaymentInvestors->participant_share;
                    }
                    if ($overPaidValue > 0) {
                        if($AdjustmentPaymentInvestors){
                            $MerchantOverPaymentInvestor = MerchantUserView::wheremerchant_id($merchant_id)->whereinvestor_id($this->overpayment_id)->first();
                            $OldParticipant_share = $AdjustmentPaymentInvestors->participant_share;
                            $participant_share    = $OldParticipant_share+$overpayment;
                            $NewParticipant_share = PaymentInvestors::shareCheck($AdjustmentPaymentInvestors->user_id,$participant_share,$MerchantOverPaymentInvestor);
                            if($NewParticipant_share!=$OldParticipant_share){
                                $AdjustmentPaymentInvestors->participant_share=$NewParticipant_share;
                                $AdjustmentPaymentInvestors->save();   
                            }
                        } else {
                            if ($OverpaymentUser->paid_participant_ishare >= abs($overpayment)) {
                                $PaymentInvestorSingleDate['participant_share'] = $overpayment;
                            } else {
                                $PaymentInvestorSingleDate['participant_share'] = -$OverpaymentUser->paid_participant_ishare;
                            }
                            $PaymentInvestorSingleDate['overpayment']   = $PaymentInvestorSingleDate['participant_share'];
                            $PaymentInvestorSingleDate['user_id']       = $this->overpayment_id;
                            $PaymentInvestorSingleDate['investment_id'] = $MerchantOverPaymentInvestor->id;
                            PaymentInvestors::create($PaymentInvestorSingleDate);
                        }
                        $investor_paid_part= PaymentInvestors::where('participent_payment_id', $participent_payment_id)->sum('participant_share');
                        $overpayment = round(($payment - $investor_paid_part), 2);
                    }
                }
                $overpayment=$this->ReCheckInvestorBalanceToAddOverpayment($payment,$overpayment,$MerchantUser,$participent_payment_id);
                if ($overpayment < 0) {
                    throw new \Exception("Can't deduct more than the RTR amount paid by the merchant", 1);
                }
                if ($overpayment > 0) {
                    $AddedInvestors  = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->pluck('user_id','user_id')->toArray();
                    $InvestorBalance = new MerchantUserView;
                    $InvestorBalance = $InvestorBalance->whereNotIn('investor_id', $AddedInvestors);
                    $InvestorBalance = $InvestorBalance->where('merchant_id', $merchant_id);
                    $InvestorBalance = $InvestorBalance->where('invest_rtr','!=',0);
                    $InvestorBalance = $InvestorBalance->sum('user_balance_amount');
                    if($InvestorBalance<0){
                        $InvestorBalance*=-1;
                        throw new \Exception("The Balance amount of ".$Merchant->name." is ".FFM::dollar($InvestorBalance)." You cannot add overpayment to an individual company.  Please select all companies.",1);
                    }
                    $MerchantOverPaymentInvestor = MerchantUserView::wheremerchant_id($merchant_id)->whereinvestor_id($this->overpayment_id)->first();
                    if (! $MerchantOverPaymentInvestor) {
                        Artisan::call('OverpaymentAccount:Add');
                        $MerchantOverPaymentInvestor = MerchantUserView::wheremerchant_id($merchant_id)->whereinvestor_id($this->overpayment_id)->first();
                    }
                    $AdjustmentPaymentInvestors = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->where('user_id', $this->overpayment_id)->first();
                    if($AdjustmentPaymentInvestors){
                        $OldParticipant_share = $AdjustmentPaymentInvestors->participant_share;
                        $participant_share    = $OldParticipant_share+$overpayment;
                        $NewParticipant_share = PaymentInvestors::shareCheck($AdjustmentPaymentInvestors->user_id,$participant_share,$MerchantOverPaymentInvestor);
                        if($NewParticipant_share!=$OldParticipant_share){
                            $AdjustmentPaymentInvestors->participant_share=$NewParticipant_share;
                            $AdjustmentPaymentInvestors->save();   
                        }
                    } else {
                        $PaymentInvestorSingleDate['participant_share'] = $overpayment;
                        if ($payment < 0) {
                            if ($MerchantOverPaymentInvestor->paid_participant_ishare < abs($overpayment)) {
                                $PaymentInvestorSingleDate['participant_share'] = -$MerchantOverPaymentInvestor->paid_participant_ishare;
                            }
                        }
                        $PaymentInvestorSingleDate['overpayment']   = $PaymentInvestorSingleDate['participant_share'];
                        $PaymentInvestorSingleDate['user_id']       = $this->overpayment_id;
                        $PaymentInvestorSingleDate['investment_id'] = $MerchantOverPaymentInvestor->id;
                        PaymentInvestors::create($PaymentInvestorSingleDate);
                    }
                }
            } else {
                $payment=ParticipentPayment::where('merchant_id',$merchant_id)->where('is_payment', 1)->sum('payment');
                $payment=round($payment,2);
                if($payment>=$rtr){
                    $this->PaymentToMarchantUserSync($merchant_id);
                    $PendingBalancedMerchantInvestor=MerchantUserView::where('merchant_id',$merchant_id)->where('user_balance_amount','!=',0)->get(['investor_id','invest_rtr','paid_participant_ishare','user_balance_amount']);
                    if(!empty($PendingBalancedMerchantInvestor->toArray())){
                        foreach ($PendingBalancedMerchantInvestor as $key => $value) {
                            $AdjustmentPaymentInvestors = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->where('user_id', $value->investor_id)->first();
                            if($AdjustmentPaymentInvestors){
                                $NewParticipant_share=$AdjustmentPaymentInvestors->participant_share-$value->user_balance_amount;
                                $AdjustmentPaymentInvestors->participant_share=$NewParticipant_share;
                                $AdjustmentPaymentInvestors->save();
                            }
                        }
                    }
                }
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function ReCheckInvestorBalanceToAddOverpayment($payment,$overpayment,$MerchantUser,$participent_payment_id) 
    {
        if($payment>0){
            if ($overpayment > 0) {
                $MerchantUser = $MerchantUser->where('user_balance_amount','!=',0);
                $MerchantUser = $MerchantUser->orderBy('user_balance_amount');
            }
            $MerchantUser = $MerchantUser->orderBy('user_balance_amount');
        } else {
            $MerchantUser = $MerchantUser->where('paid_participant_ishare', '>', 0);
            $MerchantUser = $MerchantUser->orderByDesc('paid_participant_ishare');
        }
        $MerchantUsers = $MerchantUser->get();
        if (!empty($MerchantUsers->toArray())) {
            foreach ($MerchantUsers as $key => $MerchantUser) {
                if($overpayment==0) { break; }
                $AdjustmentPaymentInvestors     = PaymentInvestors::where('participent_payment_id', $participent_payment_id);
                if($payment>0){
                    $AdjustmentPaymentInvestors = $AdjustmentPaymentInvestors->where('participant_share','>',0);
                } else {
                    $AdjustmentPaymentInvestors = $AdjustmentPaymentInvestors->where('participant_share','<',0);
                }
                $AdjustmentPaymentInvestors = $AdjustmentPaymentInvestors->where('user_id', $MerchantUser->investor_id);
                $AdjustmentPaymentInvestors = $AdjustmentPaymentInvestors->first();
                if ($AdjustmentPaymentInvestors) {
                    $OldParticipant_share = $AdjustmentPaymentInvestors->participant_share;
                    $participant_share    = $OldParticipant_share+$overpayment;
                    $NewParticipant_share = PaymentInvestors::shareCheck($MerchantUser->investor_id,$participant_share,$MerchantUser);
                    if($NewParticipant_share!=$OldParticipant_share){
                        $AdjustmentPaymentInvestors->participant_share=$NewParticipant_share;
                        $AdjustmentPaymentInvestors->save();   
                    }
                } else {
                    if($payment<0){
                        $PaymentInvestorSingleDate['overpayment'] = 0;
                        if ($MerchantUser->paid_participant_ishare >= abs($overpayment)) {
                            $PaymentInvestorSingleDate['participant_share'] = $overpayment;
                        } else {
                            $PaymentInvestorSingleDate['participant_share'] = -$MerchantUser->paid_participant_ishare;
                        }
                        $PaymentInvestorSingleDate['user_id']       = $MerchantUser->investor_id;
                        $PaymentInvestorSingleDate['investment_id'] = $MerchantUser->id;
                        PaymentInvestors::create($PaymentInvestorSingleDate);
                    }
                }
                $investor_paid_part = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->sum('participant_share');
                $overpayment        = round(($payment - $investor_paid_part), 2);
            }
        }
        return $overpayment;
    }
    public function ProfitandPrincipleUpdate($participent_payment_id)
    { 
        
        $settings = Settings::whereIn('keys', ['collection_default_mode','deduct_agent_fee_from_profit_only'])->pluck('values','keys');
        $mode = isset($settings['collection_default_mode']) ? $settings['collection_default_mode']: 0;
        $deduct_agent_fee_from_profit_only = isset($settings['deduct_agent_fee_from_profit_only']) ? $settings['deduct_agent_fee_from_profit_only']: 0;
        $type=2;
        if($type==1){
            $PaymentInvestors = PaymentInvestors::select([
                'payment_investors.id',
                'payment_investors.user_id',
                'payment_investors.actual_participant_share',
                'payment_investors.mgmnt_fee',
                'merchants.sub_status_id',
                'payment_investors.merchant_id',
                'overpayment',
                'merchant_user_views.invest_rtr',
                'merchant_user_views.user_balance_amount as balance',
                'merchant_user_views.total_investment as invested_amount',
                DB::raw('(actual_participant_share-payment_investors.mgmnt_fee)-((actual_participant_share-payment_investors.mgmnt_fee)*(merchant_user_views.total_investment)/(merchant_user_views.invest_rtr-(merchant_user_views.mgmnt_fee/100)*merchant_user_views.invest_rtr)) as profit_value1'),
                DB::raw('sum(actual_participant_share-payment_investors.mgmnt_fee) as participant_share')
            ])
            ->join('merchants','merchants.id','payment_investors.merchant_id')
            ->leftJoin('merchant_user_views', function ($join) {
                $join->on('payment_investors.user_id', 'merchant_user_views.investor_id');
                $join->on('payment_investors.merchant_id', 'merchant_user_views.merchant_id');
            })
            ->where('payment_investors.participent_payment_id', $participent_payment_id)
            ->where('payment_investors.actual_participant_share', '!=', 0)
            ->where('merchant_user_views.invest_rtr', '!=', 0)
            ->get();
        } else {
            $PaymentInvestors=DB::select('CALL profit_and_principle_update_procedure(?)', [$participent_payment_id]);
        }
        foreach ($PaymentInvestors as $singlePI) {
            $user_id=$singlePI->user_id;
            switch ($user_id) {
                case $this->overpayment_id:
                $profit    = $singlePI->overpayment;
                $principal = 0;
                break;
                case $this->agent_fee_id:
                $profit    = $singlePI->actual_participant_share;
                $principal = 0;
                break;
                default:
                $profit      = round($singlePI->profit_value1, 2);
                $principal   = $singlePI->actual_participant_share - $singlePI->mgmnt_fee - $profit;
                $merchant_id = $singlePI->merchant_id;
                $sub_status_id=Merchant::where('id',$merchant_id)->value('sub_status_id');
                if($type==1){
                    $participant_share = PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id',$user_id)->sum('participant_share');
                } else{
                    $participant_share = DB::select('CALL merchant_investor_participant_share_sum_procedures(?,?)', [$merchant_id,$user_id])[0]->value;
                }
                $collection_amount=$singlePI->actual_participant_share-$singlePI->mgmnt_fee;
                $invest_rtr = $singlePI->invest_rtr;
                $investor_balance = round($invest_rtr - $participant_share, 2);
                if ($investor_balance < 0) {
                    if (round($investor_balance, 1) < 0) {
                        $profit += $principal;
                        $principal = 0;
                    }
                }
                if($type==1){
                    $total_principal = PaymentInvestors::where('user_id', $user_id)->where('merchant_id', $singlePI->merchant_id)->sum('principal');
                } else{
                    $total_principal = DB::select('CALL merchant_investor_principal_sum_procedures(?,?)', [$merchant_id,$user_id])[0]->value;
                }
                $total_principal += $principal;
                $total_principal = round($total_principal, 2);
                $invested_amount = round($singlePI->invested_amount, 2);
                if ($total_principal > $invested_amount) {
                    $balance_principle = $total_principal - $singlePI->invested_amount;
                    $profit            = $profit + $balance_principle;
                    $principal         = $principal - $balance_principle;
                    $total_principal  -= $balance_principle;
                }
                if ($investor_balance <= 0) {
                    if ($total_principal < $invested_amount) {
                        $balance_principle = $singlePI->invested_amount - $total_principal;
                        $principal_profit_adjustment=0;
                        if($balance_principle>=$profit){
                            $principal_profit_adjustment=$profit;
                        } else {
                            $principal_profit_adjustment=$balance_principle;
                        }
                        $principal        +=$principal_profit_adjustment;
                        $total_principal  +=$principal_profit_adjustment;
                        $profit           -=$principal_profit_adjustment;
                    }
                }
                $total_principal = round($total_principal, 2);
                if($deduct_agent_fee_from_profit_only==1){
                    $profit = $profit-$singlePI->agent_fee;
                    $principal = $principal+$singlePI->agent_fee;
                    if($profit<0){
                        
                        $principal = $principal-$profit;
                        $profit =0;  
                    }
                }
                $profit          = round($profit, 2);
                $principal       = round($principal, 2);
                $net_effect      = $singlePI->actual_participant_share;
                $net_effect     -= $singlePI->mgmnt_fee;
                $net_effect     -= $principal;
                $net_effect     -= $profit;
                $net_effect      = round($net_effect, 2);
                if ($net_effect != 0) {
                    if ($total_principal < $invested_amount) {
                        $principal += $net_effect;
                    } else {
                        $profit += $net_effect;
                    }
                }
                if($mode==1) {
                    if(in_array($sub_status_id,[SubStatus::Default,SubStatus::DefaultLegal,SubStatus::Settled,SubStatus::EarlyPayDiscount,SubStatus::DefaultPlus]))
                    {   $profit=0; 
                        $principal = 0;
                        $profit += $collection_amount;
                    }
                }
                break;
            }

            PaymentInvestors::find($singlePI->id)->update([
                'profit'    => round($profit, 2),
                'principal' => round($principal, 2),
            ]);
        }
        $PaymentInvestors = PaymentInvestorsView::select([
            'id',
            'user_id',
            'merchant_id',
            'overpayment',
            'actual_participant_share',
            'invest_rtr',
        ])
        ->where('participent_payment_id', $participent_payment_id)
        ->where('invest_rtr', 0)
        ->get();
        foreach ($PaymentInvestors as $singlePI) {
            foreach ($PaymentInvestors as $singlePI) {
                switch ($singlePI->user_id) {
                    case $this->overpayment_id:
                    $profit    = $singlePI->actual_participant_share;
                    $principal = 0;
                    break;
                    case $this->agent_fee_id:
                    $profit    = $singlePI->actual_participant_share;
                    $principal = 0;
                    break;
                    default:
                    $profit    = 0;
                    $principal = 0;
                    break;
                }
                PaymentInvestors::find($singlePI->id)->update([
                    'profit'    => round($profit, 2),
                    'principal' => round($principal, 2),
                ]);
            }
        }
    }
    public function MerchantCompletedPercentageMonitoring($merchant_id, $previous_completed_percentage)
    {
        $new_completed_percenteage = PayCalc::completePercentage($merchant_id);
        $settings = Settings::select('email', 'forceopay', 'send_permission')->first();
        $send_permission = $settings->send_permission;
        $admin_emails = explode(',', $settings->email);
        $Merchant = Merchant::find($merchant_id);
        $creator_id = null;
        if (Auth::check()) {
            $creator_id = Auth::user()->id;
        } elseif (Session::has('credit_card_payment_creator')) {
            $creator_id = Session::get('credit_card_payment_creator');
        }
        if ($previous_completed_percentage < 100 && $new_completed_percenteage >= 100) {
            if ($Merchant->sub_status_id != 11) {
                $logArray = ['merchant_id' => $Merchant->id, 'old_status' => $Merchant->sub_status_id, 'current_status' => 11, 'description' => 'Merchant Status changed to Advance Completed by system ', 'creator_id' => $creator_id];
                $log = MerchantStatusLog::create($logArray);
                $merchant_status = Merchant::find($Merchant->id)->update(['sub_status_id' => 11, 'last_status_updated_date'=>$log->created_at]);
                $substatus_name = SubStatus::where('id', 11)->value('name');
                $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
                // update merchant status to CRM
                $form_params = [
                    'method'              => 'merchant_update',
                    'username'            => config('app.crm_user_name'),
                    'password'            => config('app.crm_password'),
                    'investor_merchant_id'=> $Merchant->id,
                    'status'              => $substatus_name,
                ];
                try {
                    $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                    dispatch($crmJob);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
            if (($previous_completed_percentage < 100 && $new_completed_percenteage >= 100) && $send_permission == 1) {
                if ($previous_completed_percentage < 100) {
                    $message['title'] = $Merchant->name.'  Passed '.round($new_completed_percenteage, 2).'% of payments';
                    $message['content'] = '<a href='.url('admin/merchants/view/'.$Merchant->id).'>'.$Merchant->name.' </a>  Completed '.round($new_completed_percenteage, 2).'% of payments';
                    $message['to_mail'] = $admin_emails;
                    $message['status'] = 'payment_mail';
                    $message['complete_per'] = $new_completed_percenteage;
                    $message['merchant_name'] = $Merchant->name;
                    $message['merchant_id'] = $Merchant->id;
                    $message['unqID'] = unqID();
                    try {
                        $email_template = Template::where([ ['temp_code', '=', 'PAYCO'], ['enable', '=', 1], ])->first();
                        if ($email_template) {
                            if ($email_template->assignees) {
                                $template_assignee = explode(',', $email_template->assignees);
                                $bcc_mails = [];
                                foreach ($template_assignee as $assignee) {
                                    $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                    $role_mails = array_diff($role_mails, $admin_emails);
                                    $bcc_mails[] = $role_mails;
                                }
                                $message['bcc'] = Arr::flatten($bcc_mails);
                            }
                            $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                            dispatch($emailJob);
                            $message['bcc'] = [];
                            $message['to_mail'] = $this->admin_email;
                            $emailJob = (new CommonJobs($message));
                            dispatch($emailJob);
                        }
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
        if ($previous_completed_percentage < 40 && $new_completed_percenteage >= 40 && $send_permission == 1) {
            $message['title'] = $Merchant->name.'  Passed 40% of payments';
            $message['content'] = '<a href='.url('admin/merchants/view/'.$Merchant->id).'>'.$Merchant->name.'</a>  Completed '.round($new_completed_percenteage, 2).'% of payments';
            $message['status'] = 'payment_mail';
            $message['to_mail'] = $admin_emails;
            $message['complete_per'] = $new_completed_percenteage;
            $message['merchant_name'] = $Merchant->name;
            $message['merchant_id'] = $Merchant->id;
            $message['unqID'] = unqID();
            try {
                $email_template = Template::where([['temp_code', '=', 'PAYC'], ['enable', '=', 1]])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $admin_emails);
                            $bcc_mails[] = $role_mails;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc'] = [];
                    $message['to_mail'] = $this->admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
    public function CalculateAnnualizedRate($Merchant)
    {
        $merchant_id = $Merchant->id;
        $annualized_rate = 0;
        if (isset($Merchant->funded) && isset($Merchant->commission) && isset($Merchant->rtr) && isset($Merchant->pmnts)) {
            $annualized_rate = ((($Merchant->rtr - ($Merchant->funded * (1 + $Merchant->commission / 100))) / ($Merchant->funded * (1 + $Merchant->commission / 100))) / ($Merchant->pmnts / 255) * 100);
        }
        Merchant::find($merchant_id)->update(['annualized_rate' => $annualized_rate]);
    }
    public function PaymentToMarchantUserSync($merchant_id)
    {
        $type=2;
        $new_completed_percenteage = PayCalc::completePercentage($merchant_id);
        if($type==1){
            $data = [
                'paid_participant_ishare'        => DB::raw('(select sum(payment_investors.participant_share)        as ps        from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                'actual_paid_participant_ishare' => DB::raw('(select sum(payment_investors.actual_participant_share) as aps       from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                'paid_mgmnt_fee'                 => DB::raw('(select sum(payment_investors.mgmnt_fee)                as mgmnt_fee from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                'total_agent_fee'                => DB::raw('(select sum(payment_investors.agent_fee)                as agent_fee from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                'paid_principal'                 => DB::raw('(select sum(payment_investors.principal)                as principal from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                'paid_profit'                    => DB::raw('(select sum(payment_investors.profit)                   as profit    from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
            ];
            $data['complete_per'] = $new_completed_percenteage;
            DB::table('merchant_user')
            ->whereIn('merchant_user.status', [1, 3])
            ->select('merchant_user.user_id')
            ->groupBy('merchant_user.user_id')
            ->where('merchant_user.merchant_id', $merchant_id)
            ->update($data);
        } else {
            $return_result=DB::select('CALL payment_to_marchant_user_sync_procedure(?)', [$merchant_id]);
            $uesr_ids=array_column($return_result,'user_id');
            $single['complete_per'] = $new_completed_percenteage;
            if($return_result){
                foreach ($return_result as $key => $value) {
                    $single['user_id']                        = $value->user_id;
                    $single['paid_participant_ishare']        = $value->participant_share;
                    $single['actual_paid_participant_ishare'] = $single['paid_participant_ishare'];
                    $single['paid_mgmnt_fee']                 = $value->mgmnt_fee;
                    $single['total_agent_fee']                = $value->agent_fee;
                    $single['paid_principal']                 = $value->principal;
                    $single['paid_profit']                    = $value->profit;
                    DB::table('merchant_user')
                    ->where('merchant_id', $merchant_id)
                    ->where('user_id', $value->user_id)
                    ->update($single);
                }
                $MerchantUser=MerchantUser::wheremerchant_id($merchant_id)->whereNotIn('user_id',$uesr_ids)->get();
                foreach ($MerchantUser as $key => $value) {
                    $single['user_id']                        = $value->user_id;
                    $single['paid_participant_ishare']        = 0;
                    $single['actual_paid_participant_ishare'] = 0;
                    $single['paid_mgmnt_fee']                 = 0;
                    $single['total_agent_fee']                = 0;
                    $single['paid_principal']                 = 0;
                    $single['paid_profit']                    = 0;
                    DB::table('merchant_user')
                    ->where('merchant_id', $merchant_id)
                    ->where('user_id', $value->user_id)
                    ->update($single);
                }
            } else {
                $data = [
                    'paid_participant_ishare'        => DB::raw('(select sum(payment_investors.participant_share)        as ps        from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                    'actual_paid_participant_ishare' => DB::raw('(select sum(payment_investors.actual_participant_share) as aps       from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                    'paid_mgmnt_fee'                 => DB::raw('(select sum(payment_investors.mgmnt_fee)                as mgmnt_fee from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                    'total_agent_fee'                => DB::raw('(select sum(payment_investors.agent_fee)                as agent_fee from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                    'paid_principal'                 => DB::raw('(select sum(payment_investors.principal)                as principal from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                    'paid_profit'                    => DB::raw('(select sum(payment_investors.profit)                   as profit    from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                ];
                $data['complete_per'] = $new_completed_percenteage;
                DB::table('merchant_user')
                ->whereIn('merchant_user.status', [1, 3])
                ->select('merchant_user.user_id')
                ->groupBy('merchant_user.user_id')
                ->where('merchant_user.merchant_id', $merchant_id)
                ->update($data);
            }
        }
    }
    public function PaymentCreateCRMFunction($merchant_id, $this_payment_amount, $reason, $participent_payment_id, $rcode, $date, $previous_completed_percentage)
    {
        $Merchant = Merchant::find($merchant_id);
        $new_completed_percenteage = PayCalc::completePercentage($merchant_id);
        $payment_unique_date = ParticipentPayment::where('payment_type', 1)
        ->where('payment', '!=', 0)
        ->where('participent_payments.merchant_id', $merchant_id)
        ->groupBy('payment_date')->get()->toArray();
        if ($Merchant->complete_percentage >= 100) {
            $payment_left = 'None';
        } else {
            $payment_left = $Merchant->pmnts - count($payment_unique_date);
        }
        $data1 = ParticipentPayment::select('participent_payments.payment', DB::raw('sum(payment_investors.participant_share) as participant_share'))
        ->where('participent_payments.is_payment', 1)
        ->with('paymentAllInvestors')
        ->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')
        ->join('merchant_user', function ($join) {
            $join->on('payment_investors.user_id', 'merchant_user.user_id');
            $join->on('payment_investors.merchant_id', 'merchant_user.merchant_id');
            $join->whereIn('merchant_user.status', [1, 3]);
        })
        ->join('merchants', 'merchants.id', 'participent_payments.merchant_id')
        ->groupBy('payment_investors.participent_payment_id');
        $payments_sum = $data1->where('participent_payments.merchant_id', $merchant_id);
        $payments_sum = $payments_sum->sum('payment');
        $ctd_sum = $payments_sum;
        $MerchantUser = new MerchantUser;
        $MerchantUser = $MerchantUser->select(
            DB::raw('sum(invest_rtr-paid_participant_ishare-total_agent_fee) as balance'),
            DB::raw('sum((((total_agent_fee+actual_paid_participant_ishare)-invest_rtr)*(1-(merchant_user.mgmnt_fee)/100))) as overpayment')
        );
        $MerchantUser = $MerchantUser->whereIn('merchant_user.status', [1, 3]);
        $MerchantUser = $MerchantUser->where('merchant_user.merchant_id', $merchant_id);
        $MerchantUser = $MerchantUser->first();
        $balance_our_portion = 0;
        if ($MerchantUser->overpayment > 0) {
            $balance_our_portion = 0;
        } else {
            $balance_our_portion = $MerchantUser->balance;
        }
        $actual_payment_left = $Merchant->actual_payment_left;
        $code = Rcode::where('id', $rcode)->value('code');
        $substatus = $Merchant->substatus_name;
        if ($previous_completed_percentage < 100 && $new_completed_percenteage >= 100) {
            $substatus = SubStatus::where('id', 11)->value('name');
            Merchant::find($Merchant->id)->update(['payment_end_date' => $date]);
        }
        if ($previous_completed_percentage <= 100 && $new_completed_percenteage <= 100) {
            $substatus = SubStatus::where('id', 1)->value('name');
        }
        if ($Merchant->sub_status_id == 4) {
            $substatus = SubStatus::where('id', 5)->value('name');
        }
        $form_params = [
            'method'               => 'add_lead_payment',
            'username'             => config('app.crm_user_name'),
            'password'             => config('app.crm_password'),
            'payment_date'         => $date,
            'payment'              => round($this_payment_amount, 2),
            'investor_merchant_id' => $merchant_id,
            'funded_amount'        => $Merchant->funded,
            'factor_rate'          => round($Merchant->factor_rate, 2),
            'rtr'                  => $Merchant->rtr,
            'payment_amount'       => round($Merchant->payment_amount, 2),
            'advance_type'         => $Merchant->advance_type,
            'date_funded'          => $Merchant->date_funded,
            'pmnts'                => $Merchant->pmnts ? $Merchant->pmnts : '--',
            'payment_left'         => $payment_left,
            'actual_payment_left'  => $actual_payment_left,
            'commission'           => $Merchant->commission ? $Merchant->commission : '--',
            'substatus'            => $substatus,
            'notes'                => $reason,
            'complete_percentage'  => $new_completed_percenteage,
            'balance'              => round($balance_our_portion, 2),
            'ctd'                  => round($ctd_sum, 2),
            'rcode'                => ! empty($code) ? $code : '--',
            'payment_id'           => $participent_payment_id,
        ];
        try {
            $crmJob = (new PaymentCreateCRM($form_params))->delay(now()->addMinutes(1));
            dispatch($crmJob);
        } catch (\Exception $e) {
            $message['title'] = $Merchant->name.' CRM payments issue';
            $message['content'] = $e->getMessage();
            $message['subject'] = 'Payment CRM Issue';
            $message['to_mail'] = ['fasil@iocod.com'];
            $message['status'] = 'payment_crm_issue';
            $message['merchant_name'] = $Merchant->name;
            $message['merchant_id'] = $Merchant->id;
            $message['unqID'] = unqID();
            try {
                $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                dispatch($emailJob);
                $message['to_mail'] = $this->admin_email;
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
            } catch (\Exception $ee) {
                echo $ee->getMessage();
            }
        }
    }
    public function CalculateFinalParticipantShare($merchant_id, $participent_payment_id)
    {
        $final_part_share = DB::table('payment_investors')->where('participent_payment_id', $participent_payment_id)->sum(DB::raw('participant_share-mgmnt_fee'));
        ParticipentPayment::find($participent_payment_id)->update(['final_participant_share' => $final_part_share]);
        PaymentInvestors::whereHas('ParticipentPayment', function ($query) {
            $query->where('payment', '!=', 0);
        })->where('merchant_id', $merchant_id)->where('participant_share', 0)->where('profit', 0)->where('principal', 0)->delete();
    }
    public function LiquidityLogFunction($merchant_id, $mode_of_payment)
    {
        $liquidity_old = UserDetails::sum('liquidity');
        $aggregated_liquidity = $liquidity_new = UserDetails::sum('liquidity');
        $liquidity_change = $liquidity_new - $liquidity_old; //it will always 0 then why
        $model = Merchant::find($merchant_id);
        $final_liquidity = $model->liquidity + $liquidity_change;
        $creator_id = (Auth::check()) ? Auth::user()->id : null;
        $input_array = ['aggregated_liquidity' => $aggregated_liquidity, 'final_liquidity' => $final_liquidity, 'name_of_deal' => 'Payment', 'member_id' => $merchant_id, 'liquidity_change' => $liquidity_change, 'member_type' => 'merchant', 'description' => 'Payment', 'creator_id' => $creator_id];
        switch ($mode_of_payment) {
            case '1':
            $input_array['description'] = 'ACH Payment';
            break;
            case '2':
            $input_array['description'] = 'Credit Card Payment';
            break;
        }
        if ($liquidity_change != 0) {
            // it will always 0 then why
            dd('need to check it its working or not');
            // LiquidityLog::insert($input_array);
        }
        $model->save();
    }
    public function updatePennyValues($participent_payment_id){
        $SpecialAccounts = DB::table('users')->join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccounts->whereIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE]);
        $SpecialAccounts = $SpecialAccounts->pluck('users.id', 'users.id')->toArray();
        $PaymentInvestors = PaymentInvestors::where('participent_payment_id',$participent_payment_id)->where('participant_share', '!=', 0);
        if($SpecialAccounts){
        $PaymentInvestors = $PaymentInvestors->whereNotIn('user_id',$SpecialAccounts);
        }
        $PaymentInvestors = $PaymentInvestors->orderBy('payment_investors.participant_share');
        $PaymentInvestors = $PaymentInvestors->get();
        if(!empty($PaymentInvestors->toArray())){
            $total_diff1 = $total_diff2 = $total_profit = 0;
            foreach ($PaymentInvestors as $key => $single) {
                $payment_id = $single->id;
                $particiapnt_share = ($single->participant_share-$single->mgmnt_fee);
                $principal = $single->principal;
                $profit = $single->profit;
                $diff1 = $particiapnt_share-$principal;
                $total_profit +=$single->profit;
                $total_diff1                  += $diff1;
                //if($single->profit!=0){
                $diff2 = $particiapnt_share-$principal-$profit;
                $total_diff2                  += $diff2;
                //}
                
            }
            if($total_profit==0){
                if($total_diff1!=0){
                    $adjust_payment = PaymentInvestors::find($payment_id);
                    $adjust_payment->principal += $total_diff1;
                    $adjust_payment->save();
                }
            }else{
                if($total_diff2!=0){
                    $adjust_payment = PaymentInvestors::find($payment_id);
                    $adjust_payment->profit += $total_diff2;
                    $adjust_payment->save();
                }
            }
        }
        return;
    }
    public function AgentFeeEntry($Merchant, $participent_payment_id, $payment, $max_participant_fund_per)
    {
        $merchant_id                 = $Merchant->id;
        $ParticipentPayment          = ParticipentPayment::find($participent_payment_id);
        $agent_fee_percentage        = $ParticipentPayment->agent_fee_percentage;
        if ($ParticipentPayment->agent_fee_percentage) {
            {
                $PaymentInvestors = PaymentInvestors::select('id', 'participant_share','user_id','mgmnt_fee','investment_id','overpayment')
                ->where('participent_payment_id', $participent_payment_id)
                ->where('participant_share', '!=', 0)
                ->get();
                if(!empty($PaymentInvestors->toArray())){
                    $tatal_agent_fee=0;
                    foreach ($PaymentInvestors as $key => $single) {
                   
                        $agent_fee                   = ($agent_fee_percentage * $single->participant_share) / 100;
                        $tatal_agent_fee             += $agent_fee;
                        $agent_fee                   = round($agent_fee,2);
                        
                        $single->participant_share  -= $agent_fee;
                        $single->agent_fee           = $agent_fee;
                        if($single->user_id          == $this->overpayment_id){
                            $single->overpayment     = $single->participant_share; 
                        }
                        $single->save();
                    }
                    $MerchantAgentFeeInvestor = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($this->agent_fee_id)->first();
                    $data = [
                        'participant_share'        => $tatal_agent_fee,
                        'actual_participant_share' => $tatal_agent_fee,
                        'user_id'                  => $this->agent_fee_id,
                        'merchant_id'              => $merchant_id,
                        'overpayment'              => 0,
                        'participent_payment_id'   => $participent_payment_id,
                        'investment_id'            => $MerchantAgentFeeInvestor ? $MerchantAgentFeeInvestor->id : 0,
                    ];
                    PaymentInvestors::create($data);
                }
            }
        }
    }
    public function DefaultToCollectionSubStatusChange($Merchant)
    {
        $settings = Settings::select('email', 'forceopay', 'send_permission')->first();
        $admin_emails = explode(',', $settings->email);
        $send_permission = $settings->send_permission;
        $merchant_id = $Merchant->id;
        $creator_id = null;
        if (Auth::check()) {
            $creator_id = Auth::user()->id;
        } elseif (Session::has('credit_card_payment_creator')) {
            $creator_id = Session::get('credit_card_payment_creator');
        }
        if ($Merchant->sub_status_id == 4) {
            /* merchant changed status log */
            $logArray = [
                'merchant_id'   => $merchant_id,
                'old_status'    => $Merchant->sub_status_id,
                'current_status'=> 5,
                'description'   => 'Merchant Status changed to Collections by system',
                'creator_id'    => $creator_id,
            ];
            $log = MerchantStatusLog::create($logArray);
            ////////////////////////////////////////////
            $status = Merchant::find($merchant_id)->update(['sub_status_id'=>5, 'last_status_updated_date'=>$log->created_at]);
            $substatus_name = SubStatus::where('id', 5)->value('name');
            $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
            // update merchant status to crm
            $form_params = [
                'method'              => 'merchant_update',
                'username'            => config('app.crm_user_name'),
                'password'            => config('app.crm_password'),
                'investor_merchant_id'=> $merchant_id,
                'status'              => $substatus_name,
            ];
            try {
                $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                dispatch($crmJob);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            if ($status && $send_permission == 1) {
                // header('Content-type: text/plain');
                $message['title'] = 'Collection status added to '.$Merchant->name;
                $message['subject'] = 'Collection status added to '.$Merchant->name;
                $message['content'] = ' A new payment added for '.$Merchant->name.'. Status changed to Collection.';
                $message['to_mail'] = $admin_emails;
                $message['status'] = 'merchant_change_status';
                $message['merchant_id'] = $Merchant->id;
                $message['merchant_name'] = $Merchant->name;
                $message['unqID'] = unqID();
                $message['template_type'] = 'merchant_status_collection';
                /***************************** implement queue jobs here *********************************/
                try {
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['to_mail'] = $this->admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
        // $message['timestamp'] = time();
        // $message['title'] = 'Payment';
        // $message['content'] = 'Payment of '.$request->payment.' was successfully updated in your account ';
        // $message['merchant_id'] = $merchant_id;
        // $message['merchant_name'] = $merchant->name;
        // $message['payment'] = $request->payment;
        // $message['investors'] = $investor_id;
        // $message['dates_array'] = $dates_array;
        // $message['type']='investorpayments';
        // $message['user_ids'] = json_encode($investor_id, true);
        // $message['user_ids'] = str_replace('"', '', (string) $message['user_ids']);
        // $json_data = [
        //     'merchant_name'=>$merchant->name,
        //     'payment'=>$request->payment,
        // ];
        // $message['json_data'] = json_encode($json_data, true);
        //disabled merchant app notifications
        //\EventHistory::pushNotifyMerchant($message);
        // try {
        //     $client = new \GuzzleHttp\Client();
        //     $response = $client->request('POST', 'http://www.mca.media/dev/api/service', [
        //         'form_params' => [
        //             'method'               => 'add_lead_payment',
        //             'username'             => 'creative_user',
        //             'password'             => 'creative_pass',
        //             'payment_date'         => $request->payment_date,
        //             'payment'              => $request->payment,
        //             'payment'              => $payments['payment_total'],
        //             'investor_merchant_id' => $request->merchant_id
        //         ]
        //     ]);
        // } catch (RequestException $e) {
        //     echo Psr7\str($e->getRequest());
        //     if ($e->hasResponse()) {
        //         echo Psr7\str($e->getResponse());
        //     }
        // }
    }
    public function MerchantStatusLogFunction($merchant_id)
    {
        $creator_id = null;
        if (Auth::check()) {
            $creator_id = Auth::user()->id;
        } elseif (Session::has('credit_card_payment_creator')) {
            $creator_id = Session::get('credit_card_payment_creator');
        }
        $mode=Settings::where('keys', 'collection_default_mode')->value('values');
        if($mode==1)
        {
            $substatus_list=[SubStatus::ActiveAdvance,SubStatus::Collections,SubStatus::Default,SubStatus::DefaultLegal,SubStatus::Settled,SubStatus::EarlyPayDiscount,SubStatus::DefaultPlus];
        }else
        {
            $substatus_list=[SubStatus::ActiveAdvance,SubStatus::Collections];
        }
        $Merchant = Merchant::find($merchant_id);
        if ($Merchant->complete_percentage < 100 && !in_array($Merchant->sub_status_id,$substatus_list)) {
            $old_status=MerchantStatusLog::where('merchant_id',$merchant_id)->orderByDesc('id')->value('old_status');
            if($mode==0)
            {
                if(!in_array($old_status,[SubStatus::ActiveAdvance,SubStatus::Collections]))
                {
                    $old_status=SubStatus::ActiveAdvance;
                }
            }
            $substatus_name = SubStatus::getSubStatusName($old_status);
            $logArray = [
                'merchant_id'    => $merchant_id,
                'old_status'     => $Merchant->sub_status_id,
                'current_status' => $old_status,
                'description'    => 'Merchant Status changed to '.$substatus_name.' by system ',
                'creator_id'     => $creator_id,
            ];
            $log = MerchantStatusLog::create($logArray);
            Merchant::find($merchant_id)->update(['sub_status_id' => $log->current_status, 'last_status_updated_date'=>$log->created_at]);
            $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
            $form_params = [
                'method'              => 'merchant_update',
                'username'            => config('app.crm_user_name'),
                'password'            => config('app.crm_password'),
                'investor_merchant_id'=> $merchant_id,
                'status'              => $substatus_name,
            ];
            try {
                $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                dispatch($crmJob);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        if ($Merchant->actual_payment_left <= 0 && $Merchant->complete_percentage >= 100 && $Merchant->sub_status_id != 11) {
            $logArray = [
                'merchant_id'    => $merchant_id,
                'old_status'     => $Merchant->sub_status_id,
                'current_status' => SubStatus::AdvanceCompleted,
                'description'    => 'Merchant Status changed to Advance Completed by system ',
                'creator_id'     => $creator_id,
            ];
            $log = MerchantStatusLog::create($logArray);
            Merchant::find($merchant_id)->update(['sub_status_id' => SubStatus::AdvanceCompleted, 'last_status_updated_date'=>$log->created_at]);
            $substatus_name = SubStatus::where('id',SubStatus::AdvanceCompleted)->value('name');
            $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
            // update merchant status to crm
            $form_params = [
                'method'              => 'merchant_update',
                'username'            => config('app.crm_user_name'),
                'password'            => config('app.crm_password'),
                'investor_merchant_id'=> $merchant_id,
                'status'              => $substatus_name,
            ];
            try {
                $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                dispatch($crmJob);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
    public function MerchantUpdate($merchant_id, $payment, $debit_status = 'no')
    {
        $Merchant = Merchant::find($merchant_id);
        $participant_share = PaymentInvestors::where('merchant_id', $merchant_id)->sum('participant_share');
        $total_rtr = MerchantUser::where('merchant_id', $merchant_id)->sum('invest_rtr');
        $bal_rtr = $total_rtr - $participant_share;
        $merchant_rtr = $Merchant->max_participant_fund * $Merchant->factor_rate;
        $actual_payment_left = 0;
        if ($total_rtr > 0) {
            $actual_payment_left = ($merchant_rtr) ? $bal_rtr / (($total_rtr / $merchant_rtr) * ($merchant_rtr / $Merchant->pmnts)) : 0;
        } else {
            $actual_payment_left = 0;
        }
        $fractional_part = fmod($actual_payment_left, 1);
        $act_paymnt_left = floor($actual_payment_left);
        if ($fractional_part > .09) {
            $act_paymnt_left = $act_paymnt_left + 1;
        }
        $actual_payment_left = ($act_paymnt_left > 0) ? $act_paymnt_left : 0;
        $overpayment_id = $this->overpayment_id;
        $last_payment_date = ParticipentPayment::orderByDesc('payment_date')
        ->whereHas('paymentInvestors', function ($query) use ($overpayment_id) {
            $query->where('payment_investors.user_id', '!=', $overpayment_id)
            ->where('payment_investors.participant_share', '!=', 0);
        })
        ->where('participent_payments.merchant_id', $merchant_id)
        ->max('payment_date');
        
        $last_rcode = ParticipentPayment::where('merchant_id', $merchant_id)
        ->where('is_payment', 1)
        ->orderByDesc('payment_date')
        ->value('rcode');

        if (($payment > 0) && ($debit_status != 'yes') && empty($last_rcode)) {
            $MerchantUpdateData = ['actual_payment_left'=>$actual_payment_left, 'last_rcode'=>0];
        } else {
            if ($last_rcode != 0) {
                $MerchantUpdateData = ['last_rcode'=>$last_rcode];
            } else {
                $MerchantUpdateData = ['last_rcode'=>0, 'actual_payment_left'=>$actual_payment_left];
            }
        }
        if(($payment > 0) && ($debit_status != 'yes'))
        {
            $MerchantUpdateData['last_payment_date']=$last_payment_date;
        }

        $first_payment = PaymentInvestors::where('participent_payments.merchant_id', $merchant_id)
        ->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id')
        ->where('rcode', 0)
        ->orderBy('participent_payments.payment_date', 'ASC')
        ->value('payment_date') ?? NULL;
        $MerchantUpdateData['first_payment'] = $first_payment;
        $MerchantUpdateData['complete_percentage'] = PayCalc::completePercentage($merchant_id);
        $MerchantUpdateData['paid_count'] = PaymentInvestors::where('merchant_id', $merchant_id)->pluck('participent_payment_id', 'participent_payment_id')->count();
        $Merchant = Merchant::find($merchant_id);
        $Merchant->update($MerchantUpdateData);
    }
    public function InvestmentAmountStoreToTransaction($Merchant)
    {
        $merchant_id = $Merchant->id;
        $ParticipentPaymentCount = ParticipentPayment::where('merchant_id', $merchant_id)->count();
        if ($ParticipentPaymentCount == 0) {
            $ParticipentPayment = ParticipentPayment::firstOrCreate([
                'merchant_id'=>$merchant_id,
                'model'      =>\App\MerchantUser::class,
            ],
            [
                'status'           => ParticipentPayment::StatusCompleted,
                'payment'          => -$Merchant->funded,
                'creator_id'       => Auth::user()->id ?? '',
                'transaction_type' => 2,
                'mode_of_payment'  => ParticipentPayment::PaymentModeSystemGenerated,
                'payment_date'     => $Merchant->date_funded,
            ]);
            $ParticipentPayment->update([
                'model_id'         => $ParticipentPayment->id
            ]);
        }
    }
    public function ReAssignPaymentShareEquilization($id)
    {
        $PaymentInvestors = PaymentInvestors::where('participent_payment_id', $id)->get();
        foreach ($PaymentInvestors as $single_key => $singleIPA) {
            $reassign_history = DB::table('reassign_history')->where('merchant_id', $singleIPA['merchant_id'])->where('investor2', $singleIPA['user_id'])->first();
            if ($reassign_history) {
                if($reassign_history->type==ReassignHistory::Type1){ continue; }
                $parent_investor = DB::table('merchant_user')->where('merchant_id', $singleIPA['merchant_id'])->where('user_id', $reassign_history->investor1)->where('invest_rtr','!=',0)->first();
                if ($parent_investor) {
                    $ParentPaymentInvestor = PaymentInvestors::where('participent_payment_id', $id)->where('investment_id', $parent_investor->id)->first();
                    if ($ParentPaymentInvestor) {
                        $ParentParticipateShareOld = PaymentInvestors::where('participent_payment_id', '!=', $id)->where('investment_id', $parent_investor->id)->sum('participant_share');
                        $ChildParticipateShareOld = PaymentInvestors::where('participent_payment_id', '!=', $id)->where('investment_id', $singleIPA->investment_id)->sum('participant_share');
                        $parent_old_completed_share = round(($ParentParticipateShareOld / $parent_investor->invest_rtr) * 100, 2);
                        $child_old_completed_share = 0;
                        if ($ChildParticipateShareOld) {
                            $child_old_completed_share = round(($ChildParticipateShareOld / $singleIPA->MerchantUser->invest_rtr) * 100, 2);
                        }
                        if ($child_old_completed_share < $parent_old_completed_share) {
                            $reAssign_invest_rtr = $singleIPA->MerchantUser->invest_rtr + $parent_investor->invest_rtr;
                            $reAssign_paid = $singleIPA->MerchantUser->paid_participant_ishare + $parent_investor->paid_participant_ishare;
                            $reAssign_paid += $singleIPA->participant_share + $ParentPaymentInvestor->participant_share;
                            $reAssign_completed_percentage = round($reAssign_paid / $reAssign_invest_rtr * 100, 2);
                            $ChildParticipateShareOld = round(PaymentInvestors::where('investment_id', $singleIPA->investment_id)->sum('participant_share'),2);
                            $child_old_completed_share = 0;
                            if ($ChildParticipateShareOld) {
                                $child_old_completed_share = round(($ChildParticipateShareOld / $singleIPA->MerchantUser->invest_rtr) * 100, 2);
                            }
                            if ($reAssign_completed_percentage > $child_old_completed_share) {
                                $required_amount_for_equilization = $singleIPA->MerchantUser->invest_rtr * $reAssign_completed_percentage / 100;
                                $required_amount_for_equilization -= $singleIPA['participant_share'];
                                $required_amount_for_equilization -= $singleIPA->MerchantUser->paid_participant_ishare;
                                if ($required_amount_for_equilization > 0) {
                                    if ($required_amount_for_equilization >= $ParentPaymentInvestor->participant_share) {
                                        $required_amount_for_equilization = $ParentPaymentInvestor->participant_share;
                                    }
                                    $singleIPA->participant_share += $required_amount_for_equilization;
                                    $singleIPA->save();
                                    $ParentPaymentInvestor->participant_share -= $required_amount_for_equilization;
                                    $ParentPaymentInvestor->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    public function PausePaymentDateFunction($merchant_id, $date, $rcode)
    {
        $term_payment = TermPaymentDate::where('merchant_id', $merchant_id)->where('payment_date', $date)->whereIn('status', [TermPaymentDate::ACHNotPaid, TermPaymentDate::ACHProcessing])->first();
        if ($term_payment) {
            if ($rcode > 0) {
                $term_merchant = $term_payment->merchant;
                $paused_by = Rcode::find($rcode)->description;
                $pause_skipping_rcodes = [1, 9];
                if (in_array($rcode, $pause_skipping_rcodes)) {
                    // $previous_ach_payments = ParticipentPayment::orderByDesc('payment_date')
                    // ->select('rcode', 'id', 'merchant_id', 'payment_date')
                    // ->where('merchant_id', $merchant_id)
                    // ->where('payment_date', '<', $date)
                    // ->where('mode_of_payment', ParticipentPayment::ModeAchPayment)
                    // ->take(2)
                    // ->get();
                    // $specified_rcode_count = 1;
                    // foreach ($previous_ach_payments as $check_ach) {
                    //     if (in_array($check_ach->rcode, $pause_skipping_rcodes)) {
                    //         $specified_rcode_count++;
                    //     }
                    // }
                    // if ($specified_rcode_count == 3) {
                    //     $payment_pause = $this->pausePayment($term_merchant, $paused_by, $rcode);
                    // }
                } else {
                    $payment_pause = $this->pausePayment($term_merchant, $paused_by, $rcode);
                }
            }
        }
        return true;
    }
    public function pausePayment($merchant, $paused_by, $rcode = null)
    {
        if ($merchant->payment_pause_id == null) {
            $id = $merchant->id;
            if ($rcode) {
                $rcode = Rcode::find($rcode);
                $paused_by = $rcode->code.' '.$paused_by;
            }
            $payment_pause = PaymentPause::create(['merchant_id' => $id, 'paused_by' => $paused_by, 'paused_at' => Carbon::now()]);
            $merchant->payment_pause_id = $payment_pause->id;
            $merchant->update();
            $emails = Settings::value('email');
            $emailArray = explode(',', $emails);
            $title = 'Payment Paused';
            $msg['title'] = $title;
            $msg['content']['data'] = $payment_pause;
            $msg['content']['rcode'] = $rcode;
            $msg['merchant_name'] = $merchant->name;
            $msg['merchant_id'] = $merchant->id;
            $msg['to_mail'] = $emailArray;
            $msg['status'] = 'payment_paused';
            $msg['subject'] = $title;
            $msg['paused_type'] = 'manually by';
            $msg['paused_by'] = $payment_pause->paused_by;
            $msg['paused_at'] = \FFM::datetime($payment_pause->paused_at);
            if ($rcode) {
                $msg['paused_type'] = 'due to Rcode - ';
            }
            $msg['unqID'] = unqID();
            try {
                $email_template = Template::where([['temp_code', '=', 'PYPS'], ['enable', '=', 1], ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $emailArray);
                            $bcc_mails[] = $role_mails;
                        }
                        $msg['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $msg['bcc'] = [];
                    $msg['to_mail'] = $this->admin_email;
                    $emailJob = (new CommonJobs($msg));
                    dispatch($emailJob);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            return $payment_pause;
        }
    }
    public function GPH_PaymentArea($ParticipentPayment)
    {
        try {
            $merchant_id = $ParticipentPayment->merchant_id;
            $Merchant = $ParticipentPayment->merchant;
            $previous_completed_percentage = $Merchant->complete_percentage;
            $date = $ParticipentPayment->payment_date;
            $mode_of_payment = $ParticipentPayment->mode_of_payment;
            $rcode = $ParticipentPayment->rcode;
            $debit_reason = $ParticipentPayment->reason;
            $actual_payment = $ParticipentPayment->payment;
            $debit_status = 'no';
            switch ($mode_of_payment) {
                case ParticipentPayment::ModeAchPayment:
                $description = 'ACH Payment';
                break;
                case ParticipentPayment::ModeCreditCard:
                $description = 'Credit Card Payment';
                break;
                default:
                if ($actual_payment >= 0) {
                    $description = 'Payment';
                } else {
                    $description = 'Debit Payment';
                }
                break;
            }
            if ($actual_payment < 0) {
                $debit_status = 'yes';
            }
            $payment = $ParticipentPayment->payment;
            $participent_payment_id = $ParticipentPayment->id;
            $return_result = $this->MerchantFundedCompletionCheck($Merchant);
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $mode=Settings::where('keys', 'collection_default_mode')->value('values');
            if($mode==0)
            {
                $return_result = $this->RestrictedMerchantStatusCheck($merchant_id);
                if ($return_result['result'] != 'success') {
                    throw new \Exception($return_result['result'], 1);
                }
            }
            $investor_ids = $this->getMerchantInvestorByMerchantId($merchant_id, $ParticipentPayment->investor_ids);
            InvestorSelectionArea :
            $this->PaymentToMarchantUserSync($merchant_id);
            $investors = MerchantUser::whereIn('user_id', $investor_ids);
            $investors = $investors->where('merchant_id', $merchant_id);
            $investors = $investors->where('user_id','!=', $this->overpayment_id);
            $investors = $investors->where('user_id','!=', $this->agent_fee_id);
            if ($actual_payment > 0) {
                $investors = $investors->whereRaw('round(invest_rtr-paid_participant_ishare-total_agent_fee,2)>0');
            }
            $investors = $investors->whereIn('status', [1, 3]);
            $investors = $investors->select('id', 'user_id','invest_rtr',DB::raw('round(invest_rtr-paid_participant_ishare-total_agent_fee,2) as balance'));
            $investors = $investors->get();
            if(empty($investors->toArray())){
                $old_investors = $investor_ids;
                $new_investor_ids = $this->getMerchantInvestorByMerchantId($merchant_id);
                $diff=array_diff($new_investor_ids,$old_investors);
                if($diff){
                    $investor_ids = $new_investor_ids;
                    goto InvestorSelectionArea;
                }
            }
            $this->PausePaymentDateFunction($merchant_id, $date, $rcode);
            $this->PaymentInvestorCreate($investors, $merchant_id, $participent_payment_id);
            $this->ReAssignPaymentShareEquilization($participent_payment_id);
            $max_participant_fund_per = MerchantUserView::select(DB::raw('funded/sum(amount) as max_participant_fund_per'))->where('merchant_id', $merchant_id)->first()->max_participant_fund_per;
            
            $return_result = $this->AdjustmentOverPaymentfunction($Merchant, $participent_payment_id, $payment, $max_participant_fund_per);
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $this->AgentFeeEntry($Merchant, $participent_payment_id, $payment, $max_participant_fund_per);
            $this->CalculateFinalParticipantShare($merchant_id, $participent_payment_id);
            $this->ProfitandPrincipleUpdate($participent_payment_id);
            $this->PaymentToMarchantUserSync($merchant_id);
            $this->MerchantUpdate($merchant_id, $payment, $debit_status);
            $this->PaymentCreateCRMFunction($merchant_id, $payment, $debit_reason, $participent_payment_id, $rcode, $date, $previous_completed_percentage);
            \Log::info('payment added successfully');
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function GPH_MerchantArea($ParticipentPayment)
    {
        $Merchant = $ParticipentPayment->merchant;
        $previous_completed_percentage = $Merchant->complete_percentage;
        $mode_of_payment = $ParticipentPayment->mode_of_payment;
        $actual_payment = $ParticipentPayment->payment;
        $merchant_id = $Merchant->id;
        $debit_status = 'no';
        switch ($mode_of_payment) {
            case ParticipentPayment::ModeAchPayment:
            $description = 'ACH Payment';
            break;
            case ParticipentPayment::ModeCreditCard:
            $description = 'Credit Card Payment';
            break;
            default:
            if ($actual_payment >= 0) {
                $description = 'Payment';
            } else {
                $description = 'Debit Payment';
            }
            break;
        }
        if ($actual_payment < 0) {
            $debit_status = 'yes';
        }
        $this->CalculateAnnualizedRate($Merchant);
        $this->MerchantCompletedPercentageMonitoring($merchant_id, $previous_completed_percentage);
        $this->LiquidityLogFunction($merchant_id, $mode_of_payment);
        // $this->DefaultToCollectionSubStatusChange($Merchant);
        $this->MerchantStatusLogFunction($merchant_id);
        $investor_ids = PaymentInvestors::where('participent_payment_id', $ParticipentPayment->id)->pluck('user_id', 'user_id')->toArray();
        InvestorHelper::update_liquidity($investor_ids, $description, $merchant_id, $liquidity_adjuster = '');
        // if(Session::has('lender_base_payment')){
        //     if(Session::get('lender_base_payment')){
        //         LiquidtyUpdate::dispatch($investor_ids, $description, $merchant_id, $liquidity_adjuster = '');
        //     } else {
        //         goto update_liquidity_area;
        //     }
        // } else {
        //     update_liquidity_area :
        //     InvestorHelper::update_liquidity($investor_ids, $description, $merchant_id, $liquidity_adjuster = '');
        // }
    }
    public function getMerchantInvestorByMerchantId($merchant_id, $selected_investors = null)
    {
        $investor_ids = [];
        if ($selected_investors) {
            $investor_ids = explode(',', $selected_investors);
        } else {
            $investor_ids = MerchantUser::where('merchant_id', $merchant_id);
            $investor_ids = $investor_ids->whereIn('status', [1, 3]);
            $investor_ids = $investor_ids->where('amount','!=',0);
            $investor_ids = $investor_ids->pluck('user_id', 'user_id')->toArray();
        }
        return $investor_ids;
    }
    public function ApprovePaymentFunction($participent_payment_id)
    {
        try {
            $ParticipentPayment = ParticipentPayment::find($participent_payment_id);
            if (! $ParticipentPayment) {
                throw new \Exception('Empty ParticipentPayment', 1);
            }
            $investor_ids = $this->getMerchantInvestorByMerchantId($ParticipentPayment->merchant_id);
            $ParticipentPayment->investor_ids = implode(',', $investor_ids);
            $ParticipentPayment->save();
            $return_result = $this->GPH_PaymentArea($ParticipentPayment);
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $this->GPH_MerchantArea($ParticipentPayment);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function RevertPayment($participent_payment_id, $revert_participent_payment_id)
    {
        try {
            $PaymentInvestors = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->get();
            $ParticipentPayment = ParticipentPayment::find($revert_participent_payment_id);
            $Merchant = $PaymentInvestors[0]->merchant;
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
            $merchant_id = $ParticipentPayment->merchant_id;
            $selectedInvestors = PaymentInvestors::where('participent_payment_id', $participent_payment_id);
            $selectedInvestors = $selectedInvestors->pluck('user_id','user_id')->toArray();
            $investor_ids      = implode(',',$selectedInvestors);
            $ParticipentPayment->investor_ids = implode(',', $selectedInvestors);
            $ParticipentPayment->save();
            $payment                       = $ParticipentPayment->payment;
            $mode_of_payment               = $ParticipentPayment->mode_of_payment;
            $reason                        = $ParticipentPayment->reason;
            $rcode                         = $ParticipentPayment->rcode;
            $date                          = $ParticipentPayment->payment_date;
            $previous_completed_percentage = $ParticipentPayment->merchant->complete_percentage;
            $this->PaymentToMarchantUserSync($merchant_id);
            $this->MerchantUpdate($merchant_id, $payment, 'yes');
            //$this->MerchantStatusLogFunction($merchant_id);
            $this->PaymentCreateCRMFunction($merchant_id, $payment, $reason, $revert_participent_payment_id, $rcode, $date, $previous_completed_percentage);
            $this->GPH_MerchantArea($ParticipentPayment);
            $complete_percentage = PayCalc::completePercentage($merchant_id);
            if($complete_percentage<0){
                throw new \Exception("Cant revert this payment, Merchant completed percentage will be negative.", 1);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function ToCheckRealOverpayment($merchant_id)
    {
        $Merchant = Merchant::find($merchant_id);
        $paid_payment = ParticipentPayment::where('merchant_id', $merchant_id)
        ->where('participent_payments.is_payment', 1)
        ->where('status', ParticipentPayment::StatusCompleted)->sum('payment');
        $Mdiff = round($paid_payment - $Merchant->rtr, 2);
        $max_participant_fund_per = MerchantUserView::select(DB::raw('funded/sum(amount) as max_participant_fund_per'))->where('merchant_id', $merchant_id)->first()->max_participant_fund_per;
        if ($Mdiff) {
            $PaymentInvestorsAmount = round($Mdiff / $max_participant_fund_per, 2);
            $invest_rtr = MerchantUser::where('merchant_id', $merchant_id)->sum('invest_rtr');
            $paid_participant_ishare = MerchantUser::where('merchant_id', $merchant_id)->sum('actual_paid_participant_ishare');
            $Idiff = round($paid_participant_ishare - $invest_rtr, 2);
            $deduction_overpaymant = $Idiff - ($PaymentInvestorsAmount);
            if ($Idiff) {
                $PaymentInvestors = PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id', $this->overpayment_id)->first();
                if ($PaymentInvestors) {
                    $PaymentInvestors->participant_share -= $deduction_overpaymant;
                    $PaymentInvestors->overpayment -= $deduction_overpaymant;
                    $PaymentInvestors->save();
                    $participent_payment_id = $PaymentInvestors->participent_payment_id;
                    $this->CalculateFinalParticipantShare($merchant_id, $participent_payment_id);
                    $this->PaymentToMarchantUserSync($merchant_id);
                }
            }
        } else {
            $invest_rtr = MerchantUser::where('merchant_id', $merchant_id)->sum('invest_rtr');
            $paid_participant_ishare = MerchantUser::where('merchant_id', $merchant_id)->sum('actual_paid_participant_ishare');
            $Idiff = round($paid_participant_ishare - $invest_rtr, 2);
            if ($Idiff > 0) {
                $PaymentInvestors = PaymentInvestors::where('merchant_id', $merchant_id)->where('overpayment', $Idiff)->where('user_id', $this->overpayment_id)->first();
                if ($PaymentInvestors) {
                    $participent_payment_id = $PaymentInvestors->participent_payment_id;
                    $PaymentInvestors->delete();
                    $this->CalculateFinalParticipantShare($merchant_id, $participent_payment_id);
                    $this->PaymentToMarchantUserSync($merchant_id);
                }
            }
        }
    }
    public function reGeneratePayment($participent_payment_id)
    {
        try {
            $start = microtime(true);
            $ParticipentPayment = ParticipentPayment::find($participent_payment_id);
            if (! $ParticipentPayment) {
                throw new \Exception('Empty ParticipentPayment', 1);
            }
            $creator_id      = $ParticipentPayment->creator_id;
            $merchant_id     = $ParticipentPayment->merchant_id;
            $investor_ids    = explode(',',$ParticipentPayment->investor_ids);
            $Merchant        = $ParticipentPayment->merchant;
            $date            = $ParticipentPayment->payment_date;
            $mode_of_payment = $ParticipentPayment->mode_of_payment;
            $rcode           = $ParticipentPayment->rcode;
            $debit_reason    = $ParticipentPayment->reason;
            $actual_payment  = $ParticipentPayment->payment;
            $payment         = $ParticipentPayment->payment;
            $debit_status    = 'no';
            if ($actual_payment < 0) {
                $debit_status = 'yes';
            }
            $max_participant_fund_per      = MerchantUserView::select(DB::raw('funded/sum(amount) as max_participant_fund_per'))->where('merchant_id', $merchant_id)->first()->max_participant_fund_per;
            if($ParticipentPayment->payment<0){
                $ParentParticipentPayment = ParticipentPayment::where('revert_id',$participent_payment_id)->first();
                if ($ParentParticipentPayment) {
                    $revert_participent_payment_id = $ParentParticipentPayment->id;
                    $return_result = $this->RevertPayment($revert_participent_payment_id,$participent_payment_id,$date='');
                    if ($return_result['result'] != 'success') { throw new \Exception($return_result['result'], 1); }
                    goto revertArea;
                }
            } 
            InvestorSelectionArea :
            $this->PaymentToMarchantUserSync($merchant_id);
            $investors = MerchantUser::whereIn('user_id', $investor_ids)
            ->where('merchant_id', $merchant_id)
            ->where('user_id','!=', $this->overpayment_id)
            ->where('user_id','!=', $this->agent_fee_id)
            ->whereIn('status', [1, 3])
            ->select('id', 'user_id','invest_rtr',DB::raw('round(invest_rtr-paid_participant_ishare-total_agent_fee,2) as balance'))
            ->get();
            if(empty($investors->toArray())){
                $investor_ids = $this->getMerchantInvestorByMerchantId($merchant_id);
                goto InvestorSelectionArea;
            }
            // $this->PausePaymentDateFunction($merchant_id, $date, $rcode);
            $this->PaymentInvestorCreate($investors, $merchant_id, $participent_payment_id);
            $this->ReAssignPaymentShareEquilization($participent_payment_id);
            // $this->PaymentToMarchantUserSync($merchant_id);
            $return_result = $this->AdjustmentOverPaymentfunction($Merchant, $participent_payment_id, $payment, $max_participant_fund_per);
            if ($return_result['result'] != 'success') { throw new \Exception($return_result['result'], 1); }
            $this->AgentFeeEntry($Merchant, $participent_payment_id, $payment, $max_participant_fund_per);
            $this->CalculateFinalParticipantShare($merchant_id, $participent_payment_id);
            $this->ProfitandPrincipleUpdate($participent_payment_id);
            
            revertArea:
            $this->PaymentToMarchantUserSync($merchant_id);
            // $this->MerchantUpdate($merchant_id, $payment, $debit_status);
            echo " Time :".round(microtime(true) - $start,2);
            // $previous_completed_percentage = $Merchant->complete_percentage;
            // $this->PaymentCreateCRMFunction($merchant_id, $payment, $debit_reason, $participent_payment_id, $rcode, $date, $previous_completed_percentage);
            // $this->GPH_MerchantArea($ParticipentPayment);
            $ParticipentPayment->creator_id = $creator_id;
            $ParticipentPayment->save();
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function RestrictedMerchantStatusCheck($merchant_id)
    {
        try {
            $merchant = Merchant::select('sub_status_id')->where('id', $merchant_id)->first();
            if (in_array($merchant->sub_status_id, [18, 19, 20, 4, 22])) {
                throw new \Exception("Please change the merchant status to Collection before you add payment.", 1);
            }
            $return['result']='success';
        } catch (\Exception $e) {
            $return['result'] =$e->getMessage();
        }
        return $return;
    }
    public function UpdateMerchantAgentAppliedStatus($merchant_id)
    {
        $sys_substaus = (Settings::where('keys', 'agent_fee_on_substtaus')->value('values'));
        $sys_substaus = json_decode($sys_substaus, true);
        if(!is_array($sys_substaus)){
            $sys_substaus=[];
        }
        $Merchant = Merchant::find($merchant_id);
        if(!in_array($Merchant->sub_status_id,$sys_substaus)){
            $Merchant->update(array('agent_fee_applied'=>0));
        }
        return;
    }
    public function MerchantFundedCompletionCheck($Merchant)
    {
        try {
            $merchant_id=$Merchant->id;
            $payment_status = PaymentInvestors::where('merchant_id', $merchant_id)->count();
            if ($payment_status <= 0 && in_array($Merchant->label, [Label::MCA,Label::LutherSales])) {
                $MerchantUserCompanyShare = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->groupBy('company')->pluck(DB::raw('sum(amount) as funded'),'company')->toArray();
                $CompanyAmountView=CompanyAmountView::where('merchant_id',$merchant_id)->get();
                foreach ($CompanyAmountView as $key => $value) {
                    $companyInvested   = $MerchantUserCompanyShare[$value->company_id]??0;
                    $max_company_share = $value->max_participant;
                    $diff=round($max_company_share-$companyInvested,2);
                    if($diff) {
                        throw new \Exception("Payments can't be made until the shares are completely invested. ".$value->Company." has a total share of ".FFM::dollar($value->max_participant).", of which only ".FFM::dollar($companyInvested)." is invested so far.", 1);
                    }
                }
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
}
