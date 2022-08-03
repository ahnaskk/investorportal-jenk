<?php

namespace App;

use App\Merchant;
use App\MerchantUser;
//use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Views\MerchantUserView;
use App\Models\MerchantAgentAccountHistory;
use App\User;
//fzl laravel8 use Database\Seeders\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PayCalc;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class PaymentInvestors extends Model
{
    // protected $guarded = [];
    protected $fillable = [
        'user_id',
        'merchant_id',
        'investment_id',
        'participent_payment_id',
        'participant_share',
        'actual_participant_share',
        'mgmnt_fee',
        'syndication_fee',
        'actual_overpayment',
        'overpayment',
        'balance',
        'principal',
        'profit',
        'created_at',
        'agent_fee',
    ];
    protected $table = 'payment_investors';

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->participant_share        = Self::shareCheck($model->user_id,$model->participant_share,$model->MerchantUserView);
            $model->participant_share        = round($model->participant_share, 2);
            $model->actual_participant_share = $model->participant_share;
            if ($model->MerchantUser) {
                $model->mgmnt_fee = round(PayCalc::calculateMgmntFee($model->participant_share, $model->MerchantUser->mgmnt_fee), 2);
                $model->balance   = round($model->MerchantUser->invest_rtr - $model->MerchantUser->paid_participant_ishare - $model->participant_share, 2);
            }
            $model->actual_overpayment = $model->overpayment;
        });
        static::saving(function ($model) {
            $model->participant_share = round($model->participant_share, 2);
            $participant_share_change = $model->participant_share - $model->getOriginal('participant_share');
            if ($participant_share_change) {
                $model->balance -= round($participant_share_change, 2);
            }
            $model->actual_participant_share = $model->participant_share;
            if ($model->MerchantUser) {
                $model->mgmnt_fee = round(PayCalc::calculateMgmntFee($model->participant_share, $model->MerchantUser->mgmnt_fee), 2);
            }
            $model->actual_overpayment = $model->overpayment;
        });
        static::updated(function ($model) {
            $model->participant_share = round($model->participant_share, 2);
            $participant_share_change = $model->participant_share - $model->getOriginal('participant_share');
            if ($participant_share_change) {
                $model->balance -= round($participant_share_change, 2);
            }
            $model->actual_participant_share = $model->participant_share;
            if ($model->MerchantUser) {
                $model->mgmnt_fee = round(PayCalc::calculateMgmntFee($model->participant_share, $model->MerchantUser->mgmnt_fee), 2);
            }
            $model->actual_overpayment = $model->overpayment;
        });
    }
    public static function shareCheck($user_id,$participant_share,$MerchantUser)
    {
        if($participant_share){
            $AgentFeeAccount    = User::AgentFeeIds();
            $OverpaymentAccount = User::OverpaymentIds();
            $user_balance_amount = -$MerchantUser->user_balance_amount;
            $paid_participant_ishare = $MerchantUser->paid_participant_ishare;
            if(!in_array($user_id,$OverpaymentAccount+$AgentFeeAccount)){
                if($participant_share>0){
                    if($user_balance_amount>0){
                        if($participant_share>$user_balance_amount){
                            $participant_share = $user_balance_amount;
                        }
                    } else {
                        $participant_share = 0;
                    }
                } else {
                    if($paid_participant_ishare>0){
                        if(abs($participant_share)>$paid_participant_ishare){
                            $participant_share = -$paid_participant_ishare;
                        }
                    } else {
                        $participant_share = 0;
                    }
                }
                $after_payment=$MerchantUser->paid_participant_ishare+$participant_share+$MerchantUser->total_agent_fee;
                if($after_payment<0){ $participant_share=0; }
            }
            if(in_array($user_id,$OverpaymentAccount)){
                if($participant_share<$user_balance_amount){
                    $participant_share=$user_balance_amount;
                }
            }
        }
        return $participant_share;
    }
    public static function getParticipantShareValue($selectedInvestors, $MerchantUser, $actual_payment)
    {
        $participant_share = 0;
        if ($actual_payment) {
            $SpecialAccounts = DB::table('users')->join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $SpecialAccounts->whereIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE]);
            $SpecialAccounts = $SpecialAccounts->pluck('users.id', 'users.id')->toArray();
            $merchant_id = $MerchantUser->merchant_id;
            $Merchant = Merchant::select('rtr')->find($merchant_id);
            $rtr = $Merchant->rtr;
            $total_paid_amount = DB::table('participent_payments')->where('participent_payments.is_payment', 1)->where('merchant_id', $merchant_id)->sum('payment');
            $max_participant_fund_per = MerchantUserView::select(DB::raw('funded/sum(amount) as max_participant_fund_per'))->where('merchant_id', $merchant_id)->first()->max_participant_fund_per;
            $total_over_paid_amount = DB::table('payment_investors')->where('merchant_id', $merchant_id)->where('user_id','504')->sum('participant_share');
            $total_over_paid_amount = $total_over_paid_amount*$max_participant_fund_per;
            $total_paid_amount -= $total_over_paid_amount;
            $total_paid_amount  = round($total_paid_amount,2);
            $final_payment = false;
            if ($total_paid_amount >= $rtr) {
                $final_payment = true;
            }
            $payment = $actual_payment;
            $investor_pending_balance = $MerchantUser->invest_rtr - ($MerchantUser->actual_paid_participant_ishare + $MerchantUser->total_agent_fee);
            $investor_pending_balance = $MerchantUser->invest_rtr;
            $TotalBalance = DB::table('merchant_user')
            ->where('merchant_id', $merchant_id)
            ->whereRaw('(invest_rtr-paid_participant_ishare)>0')
            ->whereIn('user_id', $selectedInvestors)
            ->select(DB::raw('sum(invest_rtr) as pending_balance'))->value('pending_balance');
            $TotalBalance = round($TotalBalance, 2);
            if ($payment > 0) {
                if (! $final_payment) {
                    if ($TotalBalance) {
                        if ($actual_payment > $TotalBalance) {
                            $payment = $TotalBalance;
                        }
                        $participant_share = round(($investor_pending_balance * $payment) / $TotalBalance, 2);
                        $TotalPaidAmount = $participant_share + $MerchantUser->actual_paid_participant_ishare + $MerchantUser->total_agent_fee;
                        if ($TotalPaidAmount > $MerchantUser->invest_rtr) {
                            $participant_share = $MerchantUser->invest_rtr - ($MerchantUser->actual_paid_participant_ishare + $MerchantUser->total_agent_fee);
                        }
                    }
                } else {
                    $participant_share = $MerchantUser->invest_rtr - ($MerchantUser->actual_paid_participant_ishare + $MerchantUser->total_agent_fee);
                }
            } else {
                $overpayment = DB::table('payment_investors')
                    ->where('merchant_id', $merchant_id)
                    ->whereIn('user_id', $SpecialAccounts)
                    ->sum('overpayment');
                $TotalBalance = DB::table('merchant_user')
                    ->where('merchant_id', $merchant_id)
                    ->whereIn('user_id', $selectedInvestors)
                    ->select(DB::raw('sum(actual_paid_participant_ishare) as pending_balance'))->value('pending_balance');
                $investor_pending_balance = $MerchantUser->actual_paid_participant_ishare;
                $payment += $overpayment;
                if ($TotalBalance) {
                    if ($payment < 0) {
                        $participant_share = round(($investor_pending_balance * $payment) / $TotalBalance, 2);
                        $diff = $MerchantUser->actual_paid_participant_ishare + $participant_share;
                        if ($diff < 0) {
                            $participant_share = -$MerchantUser->actual_paid_participant_ishare;
                        }
                    }
                }
            }
        }

        return $participant_share;
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function investors()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Investor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function MerchantUser()
    {
        return $this->belongsTo(MerchantUser::class, 'investment_id');
    }

    public function MerchantUserView()
    {
        return $this->belongsTo(MerchantUserView::class, 'investment_id');
    }

    public function ParticipentPayment()
    {
        return $this->belongsTo(ParticipentPayment::class, 'participent_payment_id');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type');
    }

    public function transactionNameAttribute()
    {
        return '22222222'; //isset($this->transaction()->first()->name)?$this->transaction()->first()->name:'No name';
    }

    public function getMerchantNameAttribute()
    {
        return isset($this->merchant()->first()->name) ? $this->merchant()->first()->name : 'No name';
    }

    public function getParticipantNameAttribute()
    {
        return isset($this->investors()->first()->name) ? $this->investors()->first()->name : 'No name';
    }

    public static function getInvestorReport($filterData)
    {
        $workCount = '';
        $date = explode('-', $filterData['start_date']);
        $month1 = isset($date[0]) ? $date[0] : '';
        $year1 = isset($date[1]) ? $date[1] : '';
        $bleded_amount = 0;
        $workdays = [];
        $type = CAL_GREGORIAN;
        $month = ! empty($month1) ? $month1 : date('n');
        $year = ! empty($year1) ? $year1 : date('Y');
        $day_count = cal_days_in_month($type, $month, $year);
        // number of days in the month
        if ($filterData['dates']) {
            $dates = explode(',', $filterData['dates']);
            $weekscount = count($dates);
            $workCount = $day_count - $weekscount;
        }
        $days = ! empty($workCount) ? $workCount : count($workdays);
        $lenders = Role::whereName('investor')->first()->users;
        $rownum = 0;
        $lender_array = [];
        foreach ($lenders as $key => $lender) {  //each lenders
            // code...
            $expected_ctd = 0;
            $len_ctd = 0;
            $merchants = Merchant::select('id', 'date_funded', 'payment_amount', 'pmnts')->where('lender_id', $lender->id)->with('participantPayment')->with('investmentData')->get();
            $merchants_ids = [];
            $number_of_deals = count($merchants);
            foreach ($merchants as $key2 => $merchant) {  //each merchants.
                $merchants_ids[] = $merchant->id;
                //each payments.
                $dates_av1 = [];
                $existing_payments = 0;
                foreach ($merchant->participantPayment as $key3 => $payment) {  //each merchants.
                    {
                        $first_day_of_month = strtotime('1-'.$filterData['start_date']);
                        if (strtotime($payment->payment_date) >= $first_day_of_month) {
                            $lastDay = strtotime(\DateTime::createFromFormat('Y-m-d', date('Y-m-d', $first_day_of_month))->format('Y-m-t'));
                            $last_day_of_month = strtotime(date('m-d-Y', $first_day_of_month));
                            if (strtotime($payment->payment_date) < $lastDay + 1) {
                                $len_ctd = $len_ctd + $payment->final_participant_share;
                            }
                        } else { //pas payments
                            $existing_payments = $existing_payments + 1;
                        }

                    }
                }
                /*finding out pending payments for the month*/
                $dates_arr = explode(',', $filterData['dates']); //same in all loops. move top.
                if ($filterData['dates']) {
                    $dates2 = 0;
                    $day_count = cal_days_in_month($type, $month, $year);
                    while ($day_count > 0) {
                        $this_day = strtotime("$day_count-".$filterData['start_date']);
                        if ($this_day > strtotime($merchant->date_funded) && ! in_array(date('Y-m-d', $this_day), $dates_arr)) {//
                            $dates2++;
                        } else {
                            //before funded_date or holidays.
                        }
                        $day_count--;
                    }
                }
                //if end payment is in this month.
                $available_payments = $merchant->pmnts - $existing_payments;
                $syndication_percent = 0;
                foreach ($merchant->investmentData as $key => $investment) {
                    $syndication_percent = $syndication_percent + $investment->share;
                }
                $expcted_payments = $available_payments < $dates2 ? $available_payments : $dates2;
                $expected_ctd = $expected_ctd + ($expcted_payments * ($merchant->payment_amount * $syndication_percent / 100));
            }
            $rownum = $rownum + 1;
            $lender_array[$lender->id]['actual_ctd'] = \FFM::dollar($len_ctd);
            $lender_array[$lender->id]['number_of_deals'] = ($number_of_deals);
            $lender_array[$lender->id]['working_days'] = $days;
            $lender_array[$lender->id]['expected_ctd'] = \FFM::dollar($expected_ctd);
            $lender_array[$lender->id]['name'] = $lender->name;
            $lender_array[$lender->id]['rownum'] = $rownum;
            if ($expected_ctd) {
                $lender_array[$lender->id]['rate'] = \FFM::percent($len_ctd / $expected_ctd * 100);
            } else {
                $lender_array[$lender->id]['rate'] = 0;
            }
            $merchant_userss = MerchantUser::where('status', 1)->whereIn('merchant_id', $merchants_ids)->with('merchant')->get();
            $bleded_amount = 0;
            $total_amount = 0;
            foreach ($merchant_userss as $key => $merchant_user) {
                if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                    $total_amount = $total_amount + $merchant_user->invest_rtr;
                    $bleded_amount = $bleded_amount + ((($merchant_user->invest_rtr - ($merchant_user->amount * (1 + $merchant_user->merchant->commission / 100))) / ($merchant_user->amount * (1 + $merchant_user->merchant->commission / 100))) / ($merchant_user->merchant->pmnts / 255) * $merchant_user->invest_rtr);
                }
            }
            $blended_rate = $total_amount ? $bleded_amount / $total_amount * 100 : 0;
            $lender_array[$lender->id]['blended_rate'] = \FFM::percent($blended_rate);
        }

        return $lender_array;
        exit();
        /*Values are not correct*/
        $workCount = '';
        $date = explode('-', $filterData['start_date']);
        $month1 = isset($date[0]) ? $date[0] : '';
        $year1 = isset($date[1]) ? $date[1] : '';
        $bleded_amount = 0;
        $workdays = [];
        $type = CAL_GREGORIAN;
        $month = ! empty($month1) ? $month1 : date('n');
        $year = ! empty($year1) ? $year1 : date('Y');
        $day_count = cal_days_in_month($type, $month, $year);
        for ($i = 1; $i <= $day_count; $i++) {
            $date = $year.'/'.$month.'/'.$i;
            $get_name = date('l', strtotime($date));
            $day_name = substr($get_name, 0, 3);
            //if not a weekend add day to array
            if ($day_name != 'Sun' && $day_name != 'Sat') {
                $workdays[] = $i;
            }
        }
        if ($filterData['dates']) {
            $dates = explode(',', $filterData['dates']);
            $weekscount = count($dates);
            $workCount = $day_count - $weekscount;
        }
        $days = ! empty($workCount) ? $workCount : count($workdays);
    }

    public static function addAgentFee($data, $agent_fee)
    {
        DB::table('payment_investors')->insert(
             [
                    'participant_share'   =>   $agent_fee,
                    'actual_participant_share'   =>   $agent_fee,
                    'user_id'             =>574,
                    'merchant_id'         =>$data->merchant_id,
                    'overpayment'         =>0,
                    'participent_payment_id'=>$data->participent_payment_id,
             ]
        );
    }
    
    public static function DefaultMerchantAdjustmentValues($participent_payment_id) {
        try {
            $ParticipentPayment=ParticipentPayment::find($participent_payment_id);
            if(!$ParticipentPayment) throw new \Exception("Empty ParticipentPayment", 1);
            if(!$ParticipentPayment->Merchant) throw new \Exception("Empty Merchant", 1);
            $sub_status_id =$ParticipentPayment->Merchant->sub_status_id;
            $merchant_id   =$ParticipentPayment->merchant_id;
            $MerchantUser = new MerchantUserView;
            $MerchantUser = $MerchantUser->select('id','investor_id','total_investment');
            $MerchantUser = $MerchantUser->where('merchant_id', $merchant_id);
            $MerchantUsers = $MerchantUser->get();
            if(empty($MerchantUsers->toArray())) throw new \Exception("Empty MerchantUsers", 1);
            foreach ($MerchantUsers as $key => $investor) {
                $profit = $principal = 0;
                if (in_array($sub_status_id, [4, 22])) {
                    $profit = PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id', $investor['investor_id'])->sum('profit');
                    $principal = $profit;
                    $profit    = -$profit;
                }
                if (in_array($sub_status_id, [18, 19, 20])) {
                    $principal         = PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id', $investor['investor_id'])->sum('principal');
                    $adjuestmentAmount = $investor['total_investment'] - $principal;
                    $profit            = -$adjuestmentAmount;
                    $principal         = $adjuestmentAmount;
                }
                $single['merchant_id']            = $merchant_id;
                $single['investment_id']          = $investor['id'];
                $single['user_id']                = $investor['investor_id'];
                $single['participent_payment_id'] = $participent_payment_id;
                $single['participant_share']      = 0;
                $single['mgmnt_fee']              = 0;
                $single['overpayment']            = 0;
                $single['profit']                 = $profit;
                $single['principal']              = $principal;
                if($principal || $profit){
                    PaymentInvestors::create($single);
                }
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
}
