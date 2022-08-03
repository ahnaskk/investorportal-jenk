<?php

namespace App;

use App\CarryForward;
//fzl laravel8 use Database\Seeders\Role;
use App\Jobs\CRMjobs;
use App\MerchantUser;
use App\Models\Views\MerchantUserView;
use Illuminate\Support\Facades\Schema;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\SubStatus;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;
use PayCalc;
use Spatie\Permission\Models\Role;
use App\Settings;


class Merchant extends Model implements AuditableContracts
{
    use Auditable;
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'merchants';

    protected $appends = ['encrypted_id'];
    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->rtr            = round($model->funded*$model->factor_rate,2);
            $model->payment_amount = ($model->pmnts!=0) ? round($model->rtr/$model->pmnts,2) : 0;
            $agent_fee_status = self::agentFeestatus($model);
            if($agent_fee_status==0){
            $model->agent_fee_applied = self::agentFeestatus($model);
            }
        });
        
    }

    public  function getEncryptedIdAttribute()
    {
        $hashids = new Hashids();

        return $hashids->encode($this->id);
    }
    public static function agentFeestatus($merchant_data){
        $sys_substaus = (Settings::where('keys', 'agent_fee_on_substtaus')->value('values'));
        $sys_substaus = json_decode($sys_substaus, true);
        $agent_fee_applied_status = 0;
        if(!empty($sys_substaus)){
        if (in_array($merchant_data->sub_status_id, $sys_substaus) && $merchant_data->max_participant_fund_per>=100) {
            $agent_fee_applied_status = 1;
        }else{
            $agent_fee_applied_status = 0;
        }
        }
        return $agent_fee_applied_status;

    }

    public static function getTableColumns()
    {
       // return DB::getSchemaBuilder()->getColumnListing('merchants');

        return [''=>'Select Filter','sub_status_id'=>'Substatus','industry_id'=>'Industry','lender_id'=>'Lender','state_id'=>'State','label'=>'Label','sub_status_flag'=>'Sub Status Flag','advance_type'=>'Advance Type'];

    }


    public function getDaysleftAttribute()
    {
        $future = strtotime($this->date_funded); //Future date.
        $timefromdb = strtotime(date('Y-m-d'));
        $timeleft = $future - $timefromdb;
        $daysleft = round((($timeleft / 24) / 60) / 60);

        return $daysleft;
    }

    public function getFundingAttribute()
    {
        $investor_data1 = MerchantUser::select(['merchant_user.id', 'users.name', 'merchant_user.created_at', 'merchant_user.user_id', 'amount', 'status', 'invest_rtr', 'actual_paid_participant_ishare', 'users.name', 'merchant_user.under_writing_fee', 'merchant_user.under_writing_fee_per', 'merchant_user.syndication_fee_percentage', 'liquidity', 'commission_amount', DB::raw('merchant_user.invest_rtr*merchant_user.mgmnt_fee/100 as mgmnt_fee_amount'),

        DB::raw('merchant_user.pre_paid'), 'merchant_user.mgmnt_fee', 'merchant_user.paid_mgmnt_fee', ])
        ->leftJoin('users', 'users.id', 'merchant_user.user_id')
        ->leftJoin('user_details', 'user_details.user_id', '=', 'merchant_user.user_id');
        $investor_data1 = $investor_data1->where('merchant_user.merchant_id', $this->id);
        $investor_data = $investor_data1->get();
        $merchant = self::find($this->id);
        $total_managmentfee = 0;
        $total_syndicationfee = 0;
        $total_underwrittingfee = 0;
        $syndication_fee = $part_total_amount = $management_fee = 0;
        foreach ($investor_data as $key => $investor) {
            $investor_data[$key]['tot_amount'] = $investor->amount + $investor->commission_amount + $investor->under_writing_fee + $investor->pre_paid;
            $investor_data[$key]['paid_back'] = $investor->actual_paid_participant_ishare;
            $total_managmentfee = $total_managmentfee + $investor->paid_mgmnt_fee;
            $total_syndicationfee = $total_syndicationfee + $investor->syndication_fee_amount;
            $total_underwrittingfee = $total_underwrittingfee + $investor->under_writing_fee;
            $part_total_amount = $part_total_amount + $investor->amount;
            if (! $merchant->m_s_prepaid_status) {
                $syndication_fee = $syndication_fee + (($merchant->rtr / $merchant->pmnts) * $investor->share / 100) * $investor->syndication_fee_percentage / 100;
            }

            if ($merchant->pmnts) {
                $management_fee = $management_fee + (($merchant->rtr / $merchant->pmnts) * $investor->share / 100) * $merchant->mgmnt_fee / 100;
            } else {
                $management_fee = 0;
            }
        }
        $data['investor_data'] = $investor_data;
        $data['part_total_amount'] = $part_total_amount;

        return $data;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function faqs()
    {
        return $this->hasMany(MerchantFaq::class);
    }

    public function investments()
    {
        return $this->belongsToMany(User::class, 'merchant_user');
    }

    public function investors()
    {
        return $this->hasMany(MerchantUser::class, 'merchant_id');
    }

    public function investmentData()
    {
        return $this->hasMany(MerchantUser::class);
    }

    public function investmentData3()
    {
        return $this->hasMany(MerchantUser::class, 'merchant_id');
    }

    public function investmentData1()
    {
        return $this->hasMany(MerchantUser::class);
    }

    public function getInvestorAttribute()
    {
        return $this->investors()->first();
    }

    public function payStatus()
    {
        return $this->belongsTo(SubStatus::class, 'sub_status_id');
    }

    public function lendor()
    {
        return $this->belongsTo(User::class, 'lender_id');
    }
    
    public function LabelModel()
    {
        return $this->belongsTo(Label::class, 'label');
    }
    
    public function Label()
    {
        return $this->belongsTo(Label::class, 'label');
    }

    /*
    Return single merchant status name from ID.
    */
    public function getpayStatusAttribute()
    {
        return $this->payStatus()->first()->name;
    }

    public function participantPayment()
    {
        return $this->hasMany(ParticipentPayment::class, 'merchant_id');
    }

    public function CarryForwardProfit()
    {
        return $this->hasMany(CarryForward::class, 'merchant_id')->where('carry_forwards.type', 2);
    }

    public function paymentInvestors()
    {
        return $this->hasMany(PaymentInvestors::class, 'merchant_id');
    }

    public function participantPayment1()
    {
        return $this->hasMany(ParticipentPayment::class, 'merchant_id');
    }

    public function participantPayment2()
    {
        return $this->hasMany(ParticipentPayment::class, 'merchant_id');
    }

    public function Payments()
    {
        return $this->hasMany(ParticipentPayment::class, 'merchant_id');
    }

    public function MerchantDetails()
    {
        return $this->hasOne(MerchantDetails::class, 'merchant_id');
    }

    public function participantPaymentGroup()
    {
        return $this->participantPayment()->groupBy('payment_date');
    }

    /* Get all the participant payment, user fileter condition iside model */
    public function ParticipantPaymentUser()
    {
        $id = 0;
        if (\Auth::check()) {
            $id = Auth::user()->id;
        }

        return $this->participantPayment()->where('user_id', '=', $id);
    }

    public function merchantPayment()
    {
        return $this->hasMany(PaymentInvestors::class, 'merchant_id');
    }

    public function merchantPaymentData()
    {
        return $this->hasMany(ParticipantPayment::class, 'merchant_id');
    }

    public function interestAccured()
    {
        return $this->hasMany(Interest::class, 'merchant_id');
    }

    /* All investors */
    public function marketplaceInvestors()
    {
        return $this->hasMany(MarketpalceInvestors::class, 'merchant_id'); //->sum('id');
    }

    public function marketplaceInvestor()
    {
        return $this->hasOne(MarketpalceInvestors::class, 'merchant_id'); //->sum('id');
    }

    /* Investors for the merchant */
    public function scopeinvestor($query, $user_id = 0)
    {
        return $this->hasMany(MarketpalceInvestors::class, 'merchant_id')->where('user_id', $user_id);
    }

    public function fundingRequests()
    {
        return $this->belongsTo(FundingRequests::class, 'id');
    }

    public function merchantNotes()
    {
        return $this->hasMany(MNotes::class, 'merchant_id');
    }

    public function merchantCompany()
    {
        return $this->hasMany(CompanyAmount::class, 'merchant_id');
    }

    public function industry()
    {
        return $this->belongsTo(Industries::class, 'industry_id');
    }

    public function getCurrentUser()
    {
        $user = false;
        if (! empty($this->user_id)) {
            $user = User::where('id', $this->user_id)->first();
        }
        if (! $user) {
            $user = $this->user;
        }

        return $user;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'merchant_id_m');
    }

    //Payment term
    public function paymentTerm()
    {
        return $this->hasOne(MerchantPaymentTerm::class)->where('status', 0)->latest();
    }

    //Payment terms
    public function paymentTerms()
    {
        return $this->hasMany(MerchantPaymentTerm::class);
    }

    //Bank accounts
    public function bankAccounts()
    {
        return $this->hasMany(\App\MerchantBankAccount::class);
    }

    //Bank accounts Debit
    public function bankAccountDebit()
    {
        return $this->hasOne(\App\MerchantBankAccount::class)->wheredefault_debit(1);
    }

    //Bank accounts Credit
    public function bankAccountCredit()
    {
        return $this->hasOne(\App\MerchantBankAccount::class)->wheredefault_credit(1);
    }

    //Payment Pause data
    public function paymentPause()
    {
        return $this->belongsTo(PaymentPause::class);
    }

    //All Payment Pause data
    public function paymentPauses()
    {
        return $this->hasMany(PaymentPause::class);
    }

    //All Term Payments
    public function termPayments()
    {
        return $this->hasMany(TermPaymentDate::class);
    }

    //Processing ACH credit payments
    public function ACHCreditPaymentProcessing()
    {
        return $this->hasMany(AchRequest::class)->where('transaction_type', 'credit')->where('ach_request_status', 1)->where('ach_status', 0);
    }

    public static function getAdvanceTypes()
    {
        return [
            'daily_ach'         => 'Daily ACH',
            'weekly_ach'        => 'Weekly ACH',
            'credit_card_split' => 'Credit Card Split',
            'variable_ach'      => 'Variable ACH',
            'lock_box'          => 'Lock Box',
            'hybrid'            => 'Hybrid',
        ];
    }

    public function getNameAttribute()
    {
        if ((! Auth::user() && request()->segment(1) == 'fundings') || (Auth::user() && Auth::user()->display_value == 'mid')) {
            $id = $this->id;

            return "Merchant ID (MID) : $id";
        }

        return strtoupper($this->attributes['name']);
    }

    public function MakeCtdAsRtrForChangingSubStatus()
    {
        try {
            $MerchantUser = MerchantUserView::where('merchant_id', $this->id)->where('investor_id', '!=', 504)->where('status', 1)->get();
            foreach ($MerchantUser as $key => $value) {
                DB::table('merchant_user')->where('id', $value->id)->update([
                    'invest_rtr'=>$value->paid_participant_ishare,
                ]);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function getNewCompletePercentageAttribute()
    {
        DB::beginTransaction();
        $this->MakeCtdAsRtrForChangingSubStatus();
        $investor_array = MerchantUserView::where('merchant_id', $this->merchant_id)->where('status', 1)->pluck('investor_id', 'investor_id')->toArray();
        $complete_per = PayCalc::completePercentage($this->id, $investor_array);
        DB::rollback();

        return $complete_per;
    }

    public function getNewFactorRateAttribute()
    {
        $new_factor_rate = DB::select(DB::raw('SELECT round(sum(paid_participant_ishare)/sum(amount),6) as factor_rate  FROM `merchant_user` WHERE merchant_id = :merchant_id'), [
            'merchant_id' => $this->id,
        ])[0]->factor_rate;

        return $new_factor_rate;
    }

    // Please Remove After jun-30-2021 SubStatusChange & modify_rtr
    public function SubStatusChange($sub_status_id)
    {
        $data_r = $logArray = [];
        $rtr_status = 0;
        $reverse_staus = 0;
        $substatus = SubStatus::select('name')->where('id', $sub_status_id)->first()->toArray();
        $logArray = ['merchant_id' => $this->id, 'old_status' => $this['sub_status_id'], 'current_status' => $sub_status_id, 'description' => 'Merchant Status changed to '.$substatus['name'].' by Admin', 'creator_id' => 1];
        MerchantStatusLog::create($logArray);
        $data_r['sub_status_id'] = $sub_status_id;
        if ($this['sub_status_id'] != $data_r['sub_status_id']) {
            $data_r['last_status_updated_date'] = date('Y-m-d h:i:sa');
        }
        $arrayList = [7627, 7641, 7642, 7664, 7670, 7674, 7688, 7694, 7719, 7731, 7741, 7756, 7764, 7765, 7773, 7817, 7824, 7854, 7871, 7884, 7894, 7909, 7910, 7925, 7935, 7963, 7981, 7993, 8000, 8065, 8071, 8091, 8098, 8102, 8124, 8152, 8158, 8164, 8173, 8199, 8212, 8219, 8224, 8241, 8256, 8260, 8272, 8289, 8302, 8340, 8372, 8402, 8405, 8415, 8422, 8461, 8516, 8526, 8593, 8685, 8719, 8724, 8730, 8738, 8774, 8825, 8830, 8841, 8869, 8887, 8898, 8923, 8938, 8998, 9005, 9007, 9031, 9037, 9065, 9093, 9129, 9144, 9194, 9197, 9232, 9246, 9276, 9298, 9351, 9372, 9390, 9401, 9453, 9497, 9507, 9597, 7741, 7981, 8152, 8158, 8164, 8173, 8199, 8212, 8219, 8224, 8238, 8263, 8279, 8293, 8298, 8319, 8465];
        // default cases start
        if (in_array($this['sub_status_id'], [4, 22]) && in_array($sub_status_id, [4, 22])) {
            $delete_flag = true;
            if (! in_array($this->id, $arrayList)) {
                $this->modify_rtr($this->id, $sub_status_id, $delete_flag);
            }
            $delete_flag = false;
            if (! in_array($this->id, $arrayList)) {
                $this->modify_rtr($this->id, $sub_status_id, $delete_flag);
            }
        }
        if (in_array($this['sub_status_id'], [4, 22]) && ! in_array($sub_status_id, [4, 22])) {
            $reverse_staus = 1;
            $delete_flag = true;
            if (! in_array($this->id, $arrayList)) {
                $this->modify_rtr($this->id, $sub_status_id, $delete_flag);
            }
        }
        if (! in_array($this['sub_status_id'], [4, 22]) && in_array($sub_status_id, [4, 22])) {
            $rtr_status = 1;
            $delete_flag = false;
            if (! in_array($this->id, $arrayList)) {
                $this->modify_rtr($this->id, $sub_status_id, $delete_flag);
            }
        }
        // default cases end
        // settled cases start
        if (in_array($this['sub_status_id'], [18, 19, 20]) && in_array($sub_status_id, [18, 19, 20])) {
            $delete_flag = true;
            if (! in_array($this->id, $arrayList)) {
                $this->modify_rtr($this->id, $sub_status_id, $delete_flag);
            }
            $delete_flag = false;
            if (! in_array($this->id, $arrayList)) {
                $this->modify_rtr($this->id, $sub_status_id, $delete_flag);
            }
        }
        if (in_array($this['sub_status_id'], [18, 19, 20]) && ! in_array($sub_status_id, [18, 19, 20])) {
            $data_r['old_factor_rate'] = 0;
            if ($this['old_factor_rate']) {
                $data_r['factor_rate'] = $this['old_factor_rate'];
            }
            $reverse_staus = 1;
            $delete_flag = true;
            if (! in_array($this->id, $arrayList)) {
                $this->modify_rtr($this->id, $sub_status_id, $delete_flag);
            }
        }
        if (! in_array($this['sub_status_id'], [18, 19, 20]) && in_array($sub_status_id, [18, 19, 20])) {
            $rtr_status = 1;
            $delete_flag = false;
            if (! in_array($this->id, $arrayList)) {
                $this->modify_rtr($this->id, $sub_status_id, $delete_flag);
            }
        }
        // settled cases end
        $Merchant = self::find($this->id);
        $Merchant = $Merchant->update($data_r);
        $Merchant = self::find($this->id);
        $substatus_name = SubStatus::where('id', $sub_status_id)->value('name');
        $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
        // update merchant status to CRM
        $form_params = [
            'method' => 'merchant_update',
            'username' => config('app.crm_user_name'),
            'password' => config('app.crm_password'),
            'investor_merchant_id'=>$this->id,
            'status'=>$substatus_name,
        ];
        try {
            $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
            dispatch($crmJob);
            //already configured delay here
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        /////////////
        if (in_array($this->id, $arrayList)) {
            return response()->json(['status' => 1, 'msg' => 'Merchant sub status updated Successfully.']);
        }
        if ($reverse_staus == 1 || $rtr_status == 1) {
            if ($reverse_staus == 1) {
                $investment_data = MerchantUser::where('merchant_id', $this->id)->where('merchant_user.status', 1)->get();
                foreach ($investment_data as $key => $investments) {
                    $invest_rtr = $Merchant->factor_rate * $investments->amount;
                    $updt_investor_rtr = MerchantUser::where('user_id', $investments->user_id)->where('merchant_id', $investments->merchant_id)->update(['invest_rtr' => $invest_rtr]);
                }
            }
            $investor_array = MerchantUser::where('merchant_id', $this->id)->where('status', 1)->pluck('user_id', 'user_id')->toArray();
            $complete_per = PayCalc::completePercentage($this->id, $investor_array);
            self::where('id', $Merchant->id)->update(['complete_percentage' => $complete_per]);
        }
    }

    public function modify_rtr($merchant_id, $sub_status_id, $delete_flag = false, $carry_delete_flag = true)
    {
        $merchant = self::find($merchant_id);
        if ($delete_flag) {
            $ParticipentPayments = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id)->where('payment', 0)->where('rcode', 0)->orderByDesc('created_at')->limit(1)->get();
            foreach ($ParticipentPayments as $key => $single) {
                $single->delete();
            }
            if ($carry_delete_flag) {
                CarryForward::where('merchant_id', $merchant_id)->where('type', 2)->delete();
            }
        } else {
            $substatus = SubStatus::where('id', $sub_status_id)->value('name');
            $investors = MerchantUser::select('id', 'user_id', DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'))->where('merchant_user.merchant_id', $merchant_id)->get()->toArray();
            $last_status_updated_date = ($merchant && $this['last_status_updated_date']) ? $this['last_status_updated_date'] : date('Y-m-d H:i:s');
            $ParticipentPaymentData = [
                'merchant_id'            => $merchant_id,
                'payment_date'           => $last_status_updated_date ? date('Y-m-d', strtotime($last_status_updated_date)) : date('Y-m-d'),
                'payment'                => 0,
                'transaction_type'       => 1,
                'status'                 => 1,
                'final_participant_share'=> 0,
                'rcode'                  => 0,
                'payment_type'           => 1,
                'investor_ids'           => implode(',', MerchantUser::where('merchant_id', $merchant_id)->where('useR_id', '!=', 504)->pluck('user_id', 'user_id')->toArray()),
                'reason'                 => 'Changed to '.$substatus,
                'creator_id'			 => Auth::user()->id ?? 1,
            ];
            if (in_array($sub_status_id, [4, 22, 18, 19, 20])) {
                $ParticipentPaymentData['is_profit_adjustment_added'] = 0;
            }
            $payment = ParticipentPayment::create($ParticipentPaymentData);
            $array = [];
            if (! empty($investors)) {
                foreach ($investors as $key => $investor) {
                    $profit = 0;
                    $principal = 0;
                    if (in_array($sub_status_id, [4, 22])) {
                        $profit = PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id', $investor['user_id'])->sum('profit');
                        $principal = $profit;
                        $profit = -$profit;
                    }
                    if (in_array($sub_status_id, [18, 19, 20])) {
                        $principal = PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id', $investor['user_id'])->sum('principal');
                        $adjuestmentAmount = $investor['investment_amount'] - $principal;
                        $profit = -$adjuestmentAmount;
                        $principal = $adjuestmentAmount;
                    }
                    $single['merchant_id'] = $merchant_id;
                    $single['investment_id'] = $investor['id'];
                    $single['user_id'] = $investor['user_id'];
                    $single['participent_payment_id'] = $payment->id;
                    $single['participant_share'] = 0;
                    $single['mgmnt_fee'] = 0;
                    $single['overpayment'] = 0;
                    $single['profit'] = $profit;
                    $single['principal'] = $principal;
                    PaymentInvestors::create($single);
                }
            }
        }

        return 1;
    }
    
    public function MerchantCompanyFunded()
    {
        $MerchantUserView    = MerchantUserView::where('merchant_id',$this->id);
        $CompanyFunded       = clone $MerchantUserView;
        $CompanyFunded       = $CompanyFunded->select([DB::raw('sum(amount) as company_funded'),'company']);
        $CompanyFunded       = $CompanyFunded->groupBy('company');
        $CompanyFundedAmount = clone $CompanyFunded;
        $CompanyFundedAmount = $CompanyFundedAmount->pluck('company_funded','company')->toArray();
        $totalFunded         = $MerchantUserView->sum('amount');
        $share=[];
        if($totalFunded){
            foreach ($CompanyFundedAmount as $company => $company_funded) {
                if($company_funded){
                    $share[$company]=$company_funded*100/$totalFunded;
                }
            }
        }
        $retrun['company_funded'] = $CompanyFundedAmount;
        $retrun['company_share']  = $share;
        return $retrun;
    }
}
