<?php

namespace App;

use App\Merchant;
use App\Models\Views\MerchantUserView;
use App\User;
//fzl laravel8 use Database\Seeders\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PayCalc;
use Spatie\Permission\Models\Role;

class MerchantUser extends Model
{
    protected $guarded = [];
    protected $table = 'merchant_user';

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $SpecialAccounts = DB::table('users')->join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $SpecialAccounts->whereIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE]);
            $SpecialAccounts = $SpecialAccounts->pluck('users.id', 'users.id')->toArray();
            if (in_array($model->user_id, $SpecialAccounts)) {
                $model->amount = 0;
                $model->invest_rtr = 0;
                $model->mgmnt_fee = 0;
                $model->syndication_fee_percentage = 0;
                $model->under_writing_fee_per = 0;
                $up_sell_commission_per =0;
            }
            if (! in_array($model->user_id, $SpecialAccounts)) {
              
                $model->mgmnt_fee = $model->mgmnt_fee ?? 0;
                $model->invest_rtr = round($model->amount * $model->merchant->factor_rate, 8);
                $model->commission_per = $model->commission_per??0;
                $model->commission_amount = round(($model->commission_per * $model->amount) / 100, 2);
                $model->up_sell_commission_per = $model->up_sell_commission_per??0;
                $model->up_sell_commission = round(($model->up_sell_commission_per * $model->amount) / 100, 2);
                // $model->s_prepaid_status = 0; // to reset While Saving
                // if ($model->Investor->global_syndication) {
                //     if ($model->Investor->s_prepaid_status) {
                //         $model->pre_paid = PayCalc::getsyndicationFee($model->Investor->s_prepaid_status == 2 ? $model->amount : $model->invest_rtr, $model->syndication_fee_percentage);
                //     }
                //     $model->s_prepaid_status = $model->Investor->s_prepaid_status;
                // } else {
                //     if ($model->merchant->m_s_prepaid_status) {
                //         $model->pre_paid = PayCalc::getsyndicationFee($model->merchant->m_s_prepaid_status == 2 ? $model->amount : $model->invest_rtr, $model->syndication_fee_percentage);
                //         $model->s_prepaid_status = $model->merchant->m_s_prepaid_status;
                //     }
                 
                // }
                if ($model->s_prepaid_status) {

                    $model->pre_paid = PayCalc::getsyndicationFee($model->s_prepaid_status == 2 ? $model->amount : $model->invest_rtr, $model->syndication_fee_percentage);
                     $model->s_prepaid_status = $model->s_prepaid_status;
                }
                $model->under_writing_fee_per = $model->under_writing_fee_per ?? 0;
                $model->under_writing_fee = round($model->under_writing_fee_per * $model->amount / 100, 2);
            }
        });
        
        static::saving(function ($model) {
            $SpecialAccounts = DB::table('users')->join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $SpecialAccounts->whereIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE]);
            $SpecialAccounts = $SpecialAccounts->pluck('users.id', 'users.id')->toArray();
            if (! in_array($model->user_id, $SpecialAccounts)) {

                $model->mgmnt_fee = $model->mgmnt_fee ?? 0;
                $model->invest_rtr = round($model->amount * $model->merchant->factor_rate, 8);
                $model->commission_per = $model->commission_per??0;
                $model->commission_amount = round(($model->commission_per * $model->amount) / 100, 2);
                $model->up_sell_commission_per = $model->up_sell_commission_per??0;
                $model->up_sell_commission = round(($model->up_sell_commission_per * $model->amount) / 100, 2);
                // if ($model->Investor->global_syndication) {
                //     if ($model->Investor->s_prepaid_status) {
                //         $model->pre_paid = PayCalc::getsyndicationFee($model->Investor->s_prepaid_status == 2 ? $model->amount : $model->invest_rtr, $model->syndication_fee_percentage);
                //     }
                //     $model->s_prepaid_status = $model->Investor->s_prepaid_status;
                // } else {
                    
                //     if ($model->merchant->m_s_prepaid_status) {
                //         $model->pre_paid = PayCalc::getsyndicationFee($model->merchant->m_s_prepaid_status == 2 ? $model->amount : $model->invest_rtr, $model->syndication_fee_percentage);
                //          $model->s_prepaid_status = $model->merchant->m_s_prepaid_status;
                //     }
                   
                // }
                if ($model->s_prepaid_status) {
                    $model->s_prepaid_status = $model->s_prepaid_status;
                    $model->pre_paid = PayCalc::getsyndicationFee($model->s_prepaid_status == 2 ? $model->amount : $model->invest_rtr, $model->syndication_fee_percentage);
                }
                $model->under_writing_fee_per = $model->under_writing_fee_per ?? 0;
                $model->under_writing_fee = round($model->under_writing_fee_per * $model->amount / 100, 2);
            }
        });
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function investors()
    {
        //should be investor
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Investor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getMerchantNameAttribute()
    {
        return isset($this->merchant()->first()->name) ? $this->merchant()->first()->name : 'No name';
    }

    public function getParticipantNameAttribute()
    {
        return isset($this->investor()->first()->name) ? $this->investor()->first()->name : 'No name';
    }

    public function participantPayment()
    {
        return $this->hasMany(ParticipentPayment::class, 'user_id');
    }

    public function TotalCompanyAmounts()
    {
        return $this->hasMany(CompanyAmount::class,'merchant_id','merchant_id');
    }

    public function getInvestmentTotalAttribute($value)
    {
        $investment =round($this->amount,2);
        $investment+=round($this->commission_amount,2);
        $investment+=round($this->under_writing_fee,2);
        $investment+=round($this->pre_paid,2);
        $investment+=round($this->up_sell_commission,2);
        return $investment;
    }

    public function CompanyAmount()
    {
        return $this->hasOne(CompanyAmount::class,'merchant_id','merchant_id')
        ->where( 'company_id', $this->Investor->company )
        ;
    }

    public function CompanyOtherInvestors()
    {
        return $this->hasMany(MerchantUser::class,'merchant_id','merchant_id')
        ->where('user_id','!=',$this->user_id)
        ->join('users','users.id','user_id')
        ->where('users.company',$this->Investor->company)
        ;
    }

    public function paymentInvestors()
    {
        return $this->hasMany(PaymentInvestors::class, 'merchant_id', 'merchant_id');
    }

    public function participantPayments()
    {
        return $this->hasMany(ParticipentPayment::class, 'merchant_id');
    }

    public function investmentData()
    {
        return $this->hasMany(self::class);
    }

    public static function getInvestorReport1($filterData)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
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
        $investors = Role::whereName('investor')->first()->users;
        if (empty($permission)) {
            $investors = $investors->where('creator_id', $userId);
        }
        $rownum = 1;
        $investor_array = [];
        $number_of_deals = 0;
        /*
        *    Loop 1, Investors Lists
        */
        foreach ($investors as $key => $investor) {  //each investors
            $expected_ctd = 0;
            $investor_ctd = 0;
            $merchants = Merchant::select('id', 'date_funded', 'payment_amount', 'pmnts', 'funded')->where('active_status', 1)
            ->with(['participantPayment' => function ($query) use ($investor) {
                $query->where('payment_investors.user_id', $investor->id);
            }]);
            if (empty($permission)) {
                $merchants = $merchants->where('creator_id', $userId);
            }
            $merchants = $merchants->with(['investmentData' => function ($query) use ($investor) {
                $query->where('merchant_user.user_id', $investor->id);
            }])->get();
            $merchants_ids = [];
            $new_merchants = [];
            foreach ($merchants as $key2 => $merchant) {
                //each merchants.
                $merchants_ids[] = $merchant->id;
                //each payments.
                $dates_av1 = [];
                $existing_payments = 0;
                $first_day_of_month = strtotime('1-'.$filterData['start_date']);
                $lastDay = strtotime(\DateTime::createFromFormat('Y-m-d', date('Y-m-d', $first_day_of_month))->format('Y-m-t'));
                foreach ($merchant->participantPayment as $key3 => $payment) {  //each merchants payments.
                    $new_merchants[] = $payment->merchant_id;
                    $first_day_of_month = strtotime('1-'.$filterData['start_date']);
                    if (strtotime($payment->payment_date) >= $first_day_of_month) {
                        $lastDay = strtotime(\DateTime::createFromFormat('Y-m-d', date('Y-m-d', $first_day_of_month))->format('Y-m-t'));
                        $last_day_of_month = strtotime(date('m-d-Y', $first_day_of_month));
                        if (strtotime($payment->payment_date) <= $lastDay) {
                            $investor_ctd = $investor_ctd + $payment->participant_share;
                        }
                    } else { //pas payments
                        if (! in_array($payment->payment_date, $dates_av1)) {
                            $existing_payments = $existing_payments + 1;
                        }
                    }
                    $dates_av1[] = $payment->payment_date;
                }
                // end merchant payment loop
                /* finding out pending payments for the month */
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
                //if end of the payment is in this month.
                $available_payments = $merchant->pmnts - $existing_payments;
                $syndication_amount = 0;
                $syndication_percent = 0;
                foreach ($merchant->investmentData as $key => $investment) {
                    $syndication_amount = $syndication_amount + $investment->amount;
                    $syndication_percent = $syndication_percent + $investment->share;
                }
                $expcted_payments = $available_payments < $dates2 ? $available_payments : $dates2;
                if ($merchant->funded) {
                    $expected_ctd = $expected_ctd + ($expcted_payments * ($merchant->payment_amount * ($syndication_amount / $merchant->funded)));
                } else {
                    $expected_ctd = $expected_ctd + 0;
                }
            }

            $investor_array[$investor->id]['actual_ctd'] = \FFM::dollar($investor_ctd);
            $investor_array[$investor->id]['number_of_deals'] = count(array_unique($new_merchants));
            $investor_array[$investor->id]['working_days'] = $days;
            $investor_array[$investor->id]['expected_ctd'] = \FFM::dollar($expected_ctd);
            $investor_array[$investor->id]['name'] = $investor->name;

            if ($expected_ctd) {
                $investor_array[$investor->id]['rate'] = \FFM::percent($investor_ctd / $expected_ctd * 100);
            } else {
                $investor_array[$investor->id]['rate'] = 0 .'%';
            }
            $merchant_userss = self::where('status', 1)->where('user_id', $investor->id)->whereIn('merchant_id', $merchants_ids)->with('merchant')->get();
            $bleded_amount = 0;
            $total_amount = 0;
            // changed queries here .......
            foreach ($merchant_userss as $key => $merchant_user) {
                if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                    $total_amount = $total_amount + $merchant_user->invest_rtr;
                    $bleded_amount = $bleded_amount + ((($merchant_user->invest_rtr - ($merchant_user->amount * (1 + $merchant_user->merchant->commission / 100))) / ($merchant_user->amount * (1 + $merchant_user->merchant->commission / 100))) / ($merchant_user->merchant->pmnts / 255) * $merchant_user->invest_rtr);
                }
            }
            $blended_rate = $total_amount ? $bleded_amount / $total_amount * 100 : 0;
            $investor_array[$investor->id]['blended_rate'] = \FFM::percent($blended_rate);
        }

        return $investor_array;
    }

    // changed function
    public static function getInvestorReport($filterData)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
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
        $investors = Role::whereName('investor')->first()->users;
        if (empty($permission)) {
            $investors = $investors->where('creator_id', $userId);
        }
        $rownum = 1;
        $investor_array = [];
        $number_of_deals = 0;
        /*
        *    Loop 1, Investors Lists
        */
        foreach ($investors as $key => $investor) {  //each investors
            $expected_ctd = 0;
            $investor_ctd = 0;
            $merchants = Merchant::select('id', 'date_funded', 'payment_amount', 'pmnts', 'funded')->where('active_status', 1)
            ->with(['participantPayment' => function ($query) use ($investor) {
                $query->where('payment_investors.user_id', $investor->id);
            }]);
            $merchants = $merchants->with(['investmentData' => function ($query) use ($investor) {
                $query->where('merchant_user.user_id', $investor->id);
            }])->get();
            $merchants_ids = [];
            $new_merchants = [];
            foreach ($merchants as $key2 => $merchant) {
                //each merchants.
                $merchants_ids[] = $merchant->id;
                //each payments.
                $dates_av1 = [];
                $existing_payments = 0;
                $first_day_of_month = strtotime('1-'.$filterData['start_date']);
                $lastDay = strtotime(\DateTime::createFromFormat('Y-m-d', date('Y-m-d', $first_day_of_month))->format('Y-m-t'));
                foreach ($merchant->participantPayment as $key3 => $payment) {  //each merchants payments.
                    $new_merchants[] = $payment->merchant_id;
                    $first_day_of_month = strtotime('1-'.$filterData['start_date']);
                    if (strtotime($payment->payment_date) >= $first_day_of_month) {
                        $lastDay = strtotime(\DateTime::createFromFormat('Y-m-d', date('Y-m-d', $first_day_of_month))->format('Y-m-t'));
                        $last_day_of_month = strtotime(date('m-d-Y', $first_day_of_month));
                        if (strtotime($payment->payment_date) <= $lastDay) {
                            $investor_ctd = $investor_ctd + $payment->participant_share;
                        }
                    } else { //pas payments
                        if (! in_array($payment->payment_date, $dates_av1)) {
                            $existing_payments = $existing_payments + 1;
                        }
                    }
                    $dates_av1[] = $payment->payment_date;
                }
                // end merchant payment loop
                /* finding out pending payments for the month */
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
                //if end of the payment is in this month.
                $available_payments = $merchant->pmnts - $existing_payments;
                $syndication_amount = 0;
                $syndication_percent = 0;
                // changed query here
                $syndication_amount = array_sum(array_column($merchant->investmentData->toArray(), 'amount'));
                $syndication_percent = array_sum(array_column($merchant->investmentData->toArray(), 'share'));

                $expcted_payments = $available_payments < $dates2 ? $available_payments : $dates2;
                if ($merchant->funded) {
                    $expected_ctd = $expected_ctd + ($expcted_payments * ($merchant->payment_amount * ($syndication_amount / $merchant->funded)));
                } else {
                    $expected_ctd = $expected_ctd + 0;
                }
            }

            $investor_array[$investor->id]['actual_ctd'] = \FFM::dollar($investor_ctd);
            $investor_array[$investor->id]['number_of_deals'] = count(array_unique($new_merchants));
            $investor_array[$investor->id]['working_days'] = $days;
            $investor_array[$investor->id]['expected_ctd'] = \FFM::dollar($expected_ctd);
            $investor_array[$investor->id]['name'] = $investor->name;

            if ($expected_ctd) {
                $investor_array[$investor->id]['rate'] = \FFM::percent($investor_ctd / $expected_ctd * 100);
            } else {
                $investor_array[$investor->id]['rate'] = 0 .'%';
            }
            $merchant_userss = self::where('status', 1)->where('user_id', $investor->id)->whereIn('merchant_id', $merchants_ids)

            ->whereHas('merchant', function ($q) {
                $q->where('active_status', 1);
            })
            ->get();
            $bleded_amount = 0;
            $total_amount = 0;
            // changed queries here .......
            $total_amount = array_sum(array_column($merchant_userss->toArray(), 'invest_rtr'));
            foreach ($merchant_userss as $key => $merchant_user) {
                if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                    $bleded_amount = $bleded_amount + ((($merchant_user->invest_rtr - ($merchant_user->amount * (1 + $merchant_user->merchant->commission / 100))) / ($merchant_user->amount * (1 + $merchant_user->merchant->commission / 100))) / ($merchant_user->merchant->pmnts / 255) * $merchant_user->invest_rtr);
                }
            }
            $blended_rate = $total_amount ? $bleded_amount / $total_amount * 100 : 0;
            $investor_array[$investor->id]['blended_rate'] = \FFM::percent($blended_rate);
        }

        return $investor_array;
    }

    public static function InvestmentAmountAdjuster($merchant_id)
    {
        try {
            $change_flag = false;
            $merchant = DB::table('merchants')->find($merchant_id);
            $company_amounts = DB::table('company_amount')->where('merchant_id', $merchant_id)->pluck('max_participant', 'company_id')->toArray();
            $return['message']='';
            foreach ($company_amounts as $company => $max_participant) {
                if ($max_participant) {
                    $MerchantUsers = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->where('company', $company)->get(['id', 'amount']);
                    $old_amount = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->where('company', $company)->sum('amount');
                    foreach ($MerchantUsers as $key => $value) {
                        $Self = self::find($value->id);
                        if ($Self->amount != round($Self->amount)) {
                            $change_flag = true;
                        }
                        $Self->amount = round($Self->amount);
                        $Self->save();
                    }
                    $new_amount = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->where('company', $company)->sum('amount');
                    $diff = round($company_amounts[$company] - $new_amount, 2);
                    $excluded=[];
                    $i=0;
                    $return['message'].= "$diff Change For $company,";
                    while ($diff) {
                        $max_investment_per = (Settings::where('keys', 'max_investment_per')->value('values')) ?? 0;
                        $max_assign_per = Settings::value('max_assign_per');
                        $maxMerchantFundingAmountForRule2 = ($merchant->funded * $max_investment_per) / 100;
                        $HighestInvestor = DB::table('merchant_user_views');
                        $HighestInvestor=$HighestInvestor->join('user_details','user_details.user_id', 'merchant_user_views.investor_id');
                        $HighestInvestor=$HighestInvestor->join('investor_transactions', 'investor_transactions.investor_id', 'merchant_user_views.investor_id');
                        if(!empty($excluded)){
                            $HighestInvestor=$HighestInvestor->whereNotIn('merchant_user_views.id', $excluded);
                        }
                        $HighestInvestor=$HighestInvestor->where('merchant_user_views.merchant_id', $merchant_id);
                        $HighestInvestor=$HighestInvestor->where('company', $company);
                        $HighestInvestor=$HighestInvestor->where('investor_transactions.transaction_type', '2');
                        $HighestInvestor=$HighestInvestor->where('investor_transactions.status', 1);
                        $HighestInvestor=$HighestInvestor->where('merchant_user_views.invest_rtr','!=', 0);
                        $HighestInvestor=$HighestInvestor->orderByDesc('actual_liquidity');
                        $HighestInvestor=$HighestInvestor->groupBy('merchant_user_views.investor_id');
                        $HighestInvestor = $HighestInvestor->select(
                            'merchant_user_views.id',
                            'merchant_user_views.investor_id',
                            'merchant_user_views.amount',
                            DB::raw("if(liquidity<sum((investor_transactions.amount*$max_assign_per)/100), liquidity, sum((investor_transactions.amount*$max_assign_per)/100)) as actual_liquidity"),
                            DB::raw("user_details.liquidity as complete_liquidity")
                        );
                        $HighestInvestor=$HighestInvestor->first();
                        if ($HighestInvestor) {
                            $i++;
                            $actual_liquidity=$HighestInvestor->actual_liquidity;
                            $complete_liquidity=$HighestInvestor->complete_liquidity;
                            $SelectedInvestor = self::find($HighestInvestor->id);
                            if($SelectedInvestor->amount==$maxMerchantFundingAmountForRule2){ 
                                $excluded[]=$SelectedInvestor->id;
                                continue; 
                            }
                            $merchant_user_views = DB::table('merchant_user_views')->find($SelectedInvestor->id);
                            if($HighestInvestor->actual_liquidity<=$merchant_user_views->total_investment){
                                $excluded[]=$SelectedInvestor->id;
                                continue; 
                            }
                            $balance_of_liquidity=round($actual_liquidity-$merchant_user_views->total_investment,2);
                            if($diff>=$balance_of_liquidity){
                                $diff-=$balance_of_liquidity;
                                $old=$SelectedInvestor->amount;
                                $SelectedInvestor->amount+=$balance_of_liquidity;
                                $SelectedInvestor->amount=round($SelectedInvestor->amount)-1;
                                $SelectedInvestor->save();
                                $excluded[]=$SelectedInvestor->id;
                            } else {
                                $SelectedInvestor->amount+=$diff;
                                $SelectedInvestor->amount=round($SelectedInvestor->amount);
                                $SelectedInvestor->save();
                                $diff=0;
                                $excluded[]=$SelectedInvestor->id;
                            }
                            if($SelectedInvestor->amount>$maxMerchantFundingAmountForRule2){ 
                                $change=$SelectedInvestor->amount-$maxMerchantFundingAmountForRule2;
                                $SelectedInvestor->amount =$maxMerchantFundingAmountForRule2;
                                $SelectedInvestor->save();
                                $diff+=$change;
                                $excluded[]=$SelectedInvestor->id;
                                continue; 
                            }
                            $merchant_user_views = DB::table('merchant_user_views')->find($SelectedInvestor->id);
                            if($merchant_user_views->total_investment>$actual_liquidity){
                                $new_investment_amount=$actual_liquidity;
                                $investing_amount=($new_investment_amount-$SelectedInvestor->pre_paid)/(1+($SelectedInvestor->Merchant->commission+$SelectedInvestor->Merchant->up_sell_commission+$SelectedInvestor->under_writing_fee_per)/100);
                                $investing_amount=round($investing_amount,2);
                                $Changed=$SelectedInvestor->amount-$investing_amount;
                                $SelectedInvestor->amount=$investing_amount;
                                $SelectedInvestor->amount=round($SelectedInvestor->amount)-1;
                                $SelectedInvestor->save();
                                $diff+=$Changed;
                            }
                        } else {
                            $diff=0;
                        }
                    }
                    $change_flag = true;
                }
            }
            $return['result'] = 'success';
            $return['change_flag'] = $change_flag;
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }

    public static function statusOptions()
    {
        return [
            '0' => 'Pending',
            '1' => 'Approved',
            '2' => 'Hide',
            '3' => 'Re-assigned',
            '4' => 'Rejected',
        ];
    }

    public function getNameAttribute()
    {
        if ((! Auth::user() && request()->segment(1) == 'fundings') || (Auth::user() && Auth::user()->display_value == 'mid')) {
            $id = $this->id;

            return "Merchant ID (MID) : $id";
        }

        return $this->attributes['name'];
    }

    public static function getPrincipalAndProfitByShare($merchant_id, $user_id, $PaymentInvestors)
    {
        $amount = $PaymentInvestors->participant_share - $PaymentInvestors->mgmnt_fee;
        $principal = $amount;
        $profit = 0;
        $Merchant = Merchant::find($merchant_id);
        $MerchantUserView = new MerchantUserView;
        $MerchantUserView = $MerchantUserView->where('investor_id', $user_id);
        $MerchantUserView = $MerchantUserView->where('merchant_id', $merchant_id);
        $MerchantUserView = $MerchantUserView->first(['total_investment', 'investor_id', 'merchant_id', 'invest_rtr', 'mgmnt_fee', 'paid_participant_ishare', 'user_balance_amount']);
        $total_investment = $MerchantUserView->total_investment;
        if ($MerchantUserView->invest_rtr > 0) {
            $profit = $amount - (($amount) * ($total_investment) / ($MerchantUserView->invest_rtr - ($MerchantUserView->mgmnt_fee / 100) * $MerchantUserView->invest_rtr));
        } else {
            $profit = $amount;
        }
        $principal -= $profit;

        // if($PaymentInvestors->balance==0){
        // dd('\n profit '.$profit,'principal '.$principal);
        // }
        $total_principal = PaymentInvestors::where('id', '<', $PaymentInvestors->id)->where('user_id', $user_id)->where('merchant_id', $merchant_id)->sum('principal');
        $total_principal += $principal;
        // if($PaymentInvestors->participant_share>0){
        //     if($total_principal>=$total_investment){
        //         $diff=round($total_principal-$total_investment,2);
        //         if($amount>=$diff){
        //             $principal-=$diff;
        //             $profit   +=$diff;
        //             $total_principal-=$diff;
        //         } else {
        //             $profit    =$amount;
        //             $principal =0;
        //             $total_principal = PaymentInvestors::where('id',"<",$PaymentInvestors->id)->where('user_id', $user_id)->where('merchant_id', $merchant_id)->sum('principal');
        //         }
        //     }
        // } else {
        //     $balance=round($MerchantUserView->invest_rtr-$MerchantUserView->paid_participant_ishare,2);
        //     if($balance<0){
        //         if(abs($balance)>=$amount){
        //             $profit    =$amount;
        //             $principal =0;
        //             $total_principal = PaymentInvestors::where('id',"<",$PaymentInvestors->id)->where('user_id', $user_id)->where('merchant_id', $merchant_id)->sum('principal');
        //         } else {
        //             dd('1');
        //         }
        //     }
        // }
        $profit = round($profit, 2);
        $principal = round($principal, 2);

        if ($PaymentInvestors->participant_share < 0) {
            // dd(' total_principal '.$total_principal,' total_investment '.$total_investment,' profit '.$profit,' principal '.$principal);
        }

        $net_effect = $amount;
        $net_effect -= $principal;
        $net_effect -= $profit;
        $net_effect = round($net_effect, 2);
        if ($net_effect != 0) {
            if ($net_effect < 0) {
                if (round($total_principal, 2) >= $total_investment) {
                    $profit += $net_effect;
                } else {
                    $principal += $net_effect;
                }
            } else {
                if (round($total_principal, 2) >= $total_investment) {
                    $profit += $net_effect;
                } else {
                    $principal += $net_effect;
                }
            }
        }

        return [
            'profit'   =>$profit,
            'principal'=>$principal,
        ];
    }
    public static function GetInvestorPrePaidAmount($data)
    {
        $pre_paid         = 0;
        $s_prepaid_status = 0;
        $Merchant         = $data['Merchant'];
        $investor_global_syndication = $data['investor_global_syndication']??NULL;
        $investor_s_prepaid_status   = $data['investor_s_prepaid_status']??NULL;
        $merchant_s_prepaid_status   = $Merchant->m_s_prepaid_status;
        $merchant_m_syndication_fee  = $Merchant->m_syndication_fee;
        $share                       = $data['share'];
        $rtr                         = round($share * $Merchant->factor_rate,2);
        if (! is_null($investor_global_syndication)) {
            $syndication_fee = $investor_global_syndication;
            if ($investor_s_prepaid_status) {
                $pre_paid = PayCalc::getsyndicationFee($investor_s_prepaid_status == 2 ? $share : $rtr, $syndication_fee);
            }
            $s_prepaid_status = $investor_s_prepaid_status;
        } else {
            $syndication_fee = $merchant_m_syndication_fee;
            if ($merchant_s_prepaid_status) {
                $pre_paid = PayCalc::getsyndicationFee($merchant_s_prepaid_status == 2 ? $share : $rtr, $syndication_fee);
            }
            $s_prepaid_status = $merchant_s_prepaid_status;
        }
        $return=[
            'pre_paid'         => round($pre_paid,2),
            'syndication_fee'  => $syndication_fee,
            's_prepaid_status' => $s_prepaid_status,
        ];
        return $return;
    }
    public static function AddOverpaymentAccount($merchant_id)
    {
        try {
            $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
            $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
            $MerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($OverpaymentAccount->id)->first();
            if (! $MerchantUser) {
                $item = [
                    'user_id'                    => $OverpaymentAccount->id,
                    'amount'                     => 0,
                    'merchant_id'                => $merchant_id,
                    'status'                     => 1,
                    'invest_rtr'                 => 0,
                    'mgmnt_fee'                  => 0,
                    'syndication_fee_percentage' => 0,
                    'commission_amount'          => 0,
                    'commission_per'             => 0,
                    'up_sell_commission_per'     => 0,
                    'up_sell_commission'         => 0,
                    'under_writing_fee'          => 0,
                    'under_writing_fee_per'      => 0,
                    'pre_paid'                   => 0,
                    's_prepaid_status'           => 1,
                    'creator_id'                 => (Auth::user()) ? Auth::user()->id : null
                ];
                Self::create($item);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public static function AddAgentFeeAccount($merchant_id)
    {
        try {
            $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
            $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
            $MerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($AgentFeeAccount->id)->first();
            if (! $MerchantUser) {
                $item = [
                    'user_id'                    => $AgentFeeAccount->id,
                    'amount'                     => 0,
                    'merchant_id'                => $merchant_id,
                    'status'                     => 1,
                    'invest_rtr'                 => 0,
                    'mgmnt_fee'                  => 0,
                    'syndication_fee_percentage' => 0,
                    'commission_amount'          => 0,
                    'commission_per'             => 0,
                    'up_sell_commission_per'     => 0,
                    'up_sell_commission'         => 0,
                    'under_writing_fee'          => 0,
                    'under_writing_fee_per'      => 0,
                    'pre_paid'                   => 0,
                    's_prepaid_status'           => 1,
                    'creator_id'                 => (Auth::user()) ? Auth::user()->id : null
                ];
                Self::create($item);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public static function getInvestmentAmount($data)
    {
        $Merchant           = $data['Merchant'];
        $underwriting_fee   = $data['underwriting_fee'];
        $up_sell_commission = $data['up_sell_commission']??0;
        $share              = $data['share'];
        $returnData = Self::GetInvestorPrePaidAmount($data);
        $pre_paid   = $returnData['pre_paid'];
        $type=1;
        if($type==1){
            $commissionAmount          = round($share*$Merchant->commission/100,2);
            $underwriting_fee_amount   = round($share*$underwriting_fee/100,2);
            $up_sell_commission_amount = round($share*$up_sell_commission/100,2);
            $TotalCommission           = $commissionAmount + $underwriting_fee_amount + $up_sell_commission_amount + $pre_paid;
            $investment_amount         = round($share + $TotalCommission,2);
        } else {
            $TotalCommission           = ($Merchant->commission + $underwriting_fee + $up_sell_commission)/100;
            $investment_amount         = $share + ($share * $TotalCommission) + $pre_paid;
            $investment_amount         = round($investment_amount,2);
        }
        $return=$returnData;
        $return['TotalCommission']   = $TotalCommission;
        $return['investment_amount'] = $investment_amount;
        return $return;
    }
}
