<?php

namespace App\Library\Repository;

use App\CarryForward;
use App\CompanyAmount;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Jobs\PaymentCreateCRM;
use App\Library\Repository\Interfaces\IInvestorTransactionRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\LiquidityLog;
use App\Merchant;
use App\MerchantPaymentTerm;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\Models\Views\MerchantUserView;
use App\Models\Views\Reports\InvestmentReportView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\PaymentPause;
use App\Providers\DashboardServiceProvider;
use App\Rcode;
use App\ReassignHistory;
use App\Settings;
use App\SubStatus;
use App\Template;
use App\TermPaymentDate;
use App\User;
use App\UserDetails;
use App\MerchantDetails;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use FFM;
use GPH;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use PayCalc;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;

class MerchantRepository implements IMerchantRepository
{
    protected $user;

    public function __construct(IInvestorTransactionRepository $transaction, IRoleRepository $role, IUserRepository $user)
    {
        $this->transaction = $transaction;
        $this->table = new Merchant();
        $this->payment_table = new ParticipentPayment();
        $this->role = $role;
        $this->user = $user;
        $this->table1 = new User();
        $this->company = new CompanyAmount();
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }

    public function delinquentRateReport($lenders = null, $industry = null, $company = 0, $from_date = null, $to_date = null, $sub_status = null, $funded_date = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $subinvestors = [];
        $company_investors = [];
        $userId = Auth::user()->id;
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            if (Auth::user()->hasRole(['company'])) {
                $subadmininvestor = $investor->where('company', $userId);
            } else {
                $subadmininvestor = $investor->where('creator_id', $userId);
            }
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        if ($company != '') {
            $company_investors = User::where('company', $company)->pluck('id')->toArray();
        }
        $merchants = $this->table->whereHas('investments', function ($query) use ($subinvestors, $permission, $company, $company_investors) {
            $query->whereIn('merchant_user.status', [1, 3]);
            $query->groupBy('merchant_id');
            if (empty($permission)) {
                $query->whereIn('merchant_user.user_id', $subinvestors);
            }
            if ($company != 0) {
                $query->whereIn('merchant_user.user_id', $company_investors);
            }
        })->with(['investments' => function ($query) use ($subinvestors, $permission, $company, $company_investors) {
            $query->select(DB::raw('sum(merchant_user.amount+merchant_user.pre_paid+merchant_user.commission_amount) as total_invested,sum(merchant_user.invest_rtr-merchant_user.invest_rtr*merchant_user.mgmnt_fee/100)  as invest_rtr'))->whereIn('merchant_user.status', [1, 3]);
            $query->groupBy('merchant_id');
            if (empty($permission)) {
                $query->whereIn('merchant_user.user_id', $subinvestors);
            }
            if (! empty($company_investors)) {
                $query->whereIn('merchant_user.user_id', $company_investors);
            }
        }])->select('merchants.id', 'merchants.name as name', 'merchants.last_payment_date', 'lender.name as lender', 'industries.name as industry', 'merchants.creator_id', 'merchants.created_at')->whereHas('participantPayment', function ($q) use ($from_date, $to_date, $subinvestors, $permission, $company, $company_investors) {
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->groupBy('payment_investors.id');
            $q->select(DB::raw('sum( payment_investors.actual_participant_share-payment_investors.mgmnt_fee) as net_ctd'), 'participent_payments.merchant_id', 'payment_investors.id');
            $table_field = 'payment_date';
            if (empty($permission)) {
                $q->whereIn('payment_investors.user_id', $subinvestors);
            }
            if ($to_date != null) {
                $q->where($table_field, '<=', $to_date);
            }
            if ($from_date != null) {
                $q->where($table_field, '>=', $from_date);
            }
            if (! empty($company_investors)) {
                $q->whereIn('payment_investors.user_id', $company_investors);
            }
            $q->orderByDesc('participent_payment_id');
        })->with(['participantPayment' => function ($q) use ($from_date, $to_date, $subinvestors, $permission, $company, $company_investors) {
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->groupBy('payment_investors.id');
            $q->select(DB::raw('sum(payment_investors.profit) as profit_value , sum(payment_investors.actual_participant_share-payment_investors.mgmnt_fee) as net_ctd'), 'participent_payments.merchant_id', 'payment_investors.id', 'participent_payments.payment_date');
            $table_field = 'payment_date';
            if (empty($permission)) {
                $q->whereIn('payment_investors.user_id', $subinvestors);
            }
            if ($to_date != null) {
                $q->where($table_field, '<=', $to_date);
            }
            if ($from_date != null) {
                $q->where($table_field, '>=', $from_date);
            }
            if (! empty($company_investors)) {
                $q->whereIn('payment_investors.user_id', $company_investors);
            }
            $q->orderByDesc('participent_payment_id');
        }])->whereIn('sub_status_id', [4, 22])->join('users as lender', 'merchants.lender_id', 'lender.id')->join('industries', 'merchants.industry_id', 'industries.id');
        if (isset($industry[0]) && $industry[0]) {
            $merchants = $merchants->whereIn('merchants.industry_id', $industry);
        }
        if ($lenders) {
            $merchants = $merchants->whereIn('merchants.lender_id', $lenders);
        }

        return $merchants;
    }

    public function getAll($fields = null, $user_filter = null, $status = null, $user_id = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $setInvestors = [];
        $investors = Role::whereName('investor')->first()->users;
        if (empty($permission)) {
            $investors = $investors->where('company', $userId);
        }
        foreach ($investors as $key => $investor) {
            $setInvestors[] = $investor->id;
        }
        $this->table = $this->table->where('active_status', 1);
        if ($user_filter) {
            $merchants = $this->table->whereHas('participantPayment', function ($query) use ($setInvestors) {
                $query->join('payment_investors', 'participent_payments.id', 'payment_investors.participent_payment_id');
                if (empty($permission)) {
                    $query->whereIn('payment_investors.user_id', $setInvestors);
                }
            })->whereHas('investments', function ($query) use ($setInvestors, $user_id) {
                if (empty($permission)) {
                    $query->whereIn('merchant_user.user_id', $setInvestors);
                }
                $query->where('merchant_user.user_id', $user_id);
            });
            if (! empty($status) && is_array($status)) {
                $merchants->whereIn('sub_status_id', $status);
            }

            return $merchants = $merchants->orderBy('name')->get();
        }
        if ($fields != null) {
            return $this->table->select($fields)->orderBy('name')->get();
        }

        return $this->table->with('participantPayment')->orderBy('name')->get();
    }

    public function getAllInvestors($investor_id, $forceoverpay = 0)
    {
        $fields = null;
        $user_filter = null;
        $user = Auth::user();
        $userId = $user->id;
        if ($user_filter) {
            return $this->table->with('participantPayment')->whereHas('investmentData', function ($query) use ($userId, $forceoverpay) {
                $query->where('user_id', $userId);
                if (! $forceoverpay) {
                    $query->where('complete_per', '<', '99.99');
                }
            })->orderBy('name')->get();
        }
        if ($fields != null) {
            return $this->table->select($fields)->orderBy('name')->get();
        }

        return $this->table->with(['investmentData' => function ($query) use ($forceoverpay) {
            if (! $forceoverpay) {
                $query->where('complete_per', '<', '99.99');
            }
        }])->where('id', $investor_id)->orderBy('name')->first();
    }

    public function datatable($fields = null, $lender_id = null, $status_id = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        DB::statement(DB::raw('set @rownum=0'));
        $fields['row'] = DB::raw('@rownum  := @rownum  + 1 AS row');
        $setInvestors = [];
        $investors = Role::whereName('investor')->first()->users;
        if (empty($permission)) {
            $investors = $investors->where('creator_id', $userId);
        }
        foreach ($investors as $key => $investor) {
            $setInvestors[] = $investor->id;
        }
        $merchants = [];
        if (empty($permission)) {
            $merchants = MerchantUser::whereIn('user_id', $setInvestors)->pluck('merchant_id')->toArray();
        }
        if ($fields != null) {
            $return = $this->table->select($fields);
        } else {
            $return = $this->table;
        }
        if (empty($permission)) {
            if (count($merchants)) {
                $return->whereIn('id', $merchants);
            }
        }
        if ($lender_id) {
            $return->where('lender_id', $lender_id);
        }
        if ($status_id) {
            $return->where('sub_status_id', $status_id);
        }
        $return->with('investments');

        return $return;
    }

    public function investorDatatable1($userId, $fields, $lender_id = 0, $status_id = 0, $status_arr = [])
    {
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);

        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);

        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId_admin = Auth::user()->id;
        $subinvestors = [];
        if ($userId) {
            $ret1 = MerchantUser::leftJoin('merchants', 'merchants.id', 'merchant_user.merchant_id')->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->whereIn('merchant_user.status', [1, 3])->where('merchants.active_status', 1);
            if (empty($permission)) {
                if ($subinvestors) {
                    $ret1 = $ret1->whereIn('merchant_user.user_id', $subinvestors);
                }
            }
            $ret1 = $ret1->where('merchant_user.user_id', $userId)->select('merchants.creator_id', 'merchants.created_at','under_writing_fee','merchants.complete_percentage as complete_per', 'merchants.id', DB::raw('upper(merchants.name) as name'), 'sub_status_id','pmnts','advance_type','commission_amount','pre_paid', 'merchants.date_funded', 'merchant_user.commission_per as commission','merchant_user.up_sell_commission_per as up_sell_commission_per','merchant_user.up_sell_commission', 'annualized_rate', 'sub_statuses.name as sub_status_name', 'merchants.factor_rate', 'funded', 'merchant_user.amount', 'annualized_rate', 'invest_rtr', 'paid_participant_ishare', 'actual_paid_participant_ishare', 'paid_mgmnt_fee', 'merchants.last_payment_date',DB::raw('(actual_paid_participant_ishare/invest_rtr)*100 as complete_percentage'), DB::raw('merchant_user.invest_rtr*merchant_user.mgmnt_fee/100 as mag_fee'), DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as tot_investment
                '), DB::raw('

               ((((invest_rtr * (100-merchant_user.mgmnt_fee)/100)
                 -
                 (  merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)

                 )

               )   * IF(advance_type="weekly_ach",52, 255) / merchants.pmnts
            ) as tot_profit '));
        }
        if ($status_arr && is_array($status_arr)) {
            $ret1 = $ret1->wherein('sub_status_id', $status_arr);
        }
        if ($status_id != '') {
            $ret1 = $ret1->where('sub_status_id', $status_id);
        }
        if ($AgentFeeAccount) {
            if ($userId == $AgentFeeAccount->id) {
                $ret1 = $ret1->where('paid_participant_ishare', '<>', 0);
            }
        }

        if ($OverpaymentAccount) {
            if ($userId == $OverpaymentAccount->id) {
                $ret1 = $ret1->where('paid_participant_ishare', '<>', 0);
            }
        }

        $ret1_sum = clone $ret1;
        $ret1_sum = $ret1_sum->select(DB::raw('sum(amount) as amount, sum(invest_rtr) as invest_rtr, sum(paid_mgmnt_fee) as paid_mgmnt_fee,

            sum(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as mgmnt_fee_amount,

            sum(commission_amount) as commission_amount, sum(paid_participant_ishare-paid_mgmnt_fee) as paid_participant_ishare ,sum(actual_paid_participant_ishare-paid_mgmnt_fee) as actual_paid_participant_ishare'))->first();
        $return['list'] = $ret1;
        $return['sum'] = $ret1_sum;

        return $return;
    }

    public function investorDatatable($userId, $fields, $lender_id = 0, $status_id = 0, $status_arr = [])
    {
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);

        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId_admin = Auth::user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            if (Auth::user()->hasRole(['company'])) {
                $subadmininvestor = $investor->where('company', $userId_admin);
            } else {
                $subadmininvestor = $investor->where('creator_id', $userId_admin);
            }
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        if ($userId) {
            $ret1 = MerchantUser::select('merchant_user.amount', 'invest_rtr', 'paid_participant_ishare', 'actual_paid_participant_ishare', 'paid_mgmnt_fee', DB::raw('(actual_paid_participant_ishare/invest_rtr)*100 as complete_percentage'),DB::raw('merchant_user.invest_rtr*merchant_user.mgmnt_fee/100 as mag_fee'), 'merchant_user.merchant_id', 'merchant_user.user_id', 'commission_per', 'commission_amount','merchant_user.up_sell_commission_per','merchant_user.up_sell_commission','pre_paid', 'under_writing_fee')->whereIn('merchant_user.status', [1, 3])->with(['merchant' => function ($query) {
                $query->select('merchants.id', 'merchants.name', 'merchants.complete_percentage', 'sub_status_id', 'date_funded', 'commission', 'annualized_rate', 'sub_statuses.name as sub_statuses_name', 'factor_rate', 'funded', 'advance_type', 'pmnts');
                $query->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');
            }]);
            if (empty($permission)) {
                $ret1->whereIn('merchant_user.user_id', $subinvestors);
            }
            $ret1->whereHas('merchant', function ($query) use ($userId, $lender_id, $status_id, $status_arr) {
                $query->where('active_status', 1);
                if ($status_arr && is_array($status_arr)) {
                    $query->whereIn('sub_status_id', $status_arr);
                }
                if ($status_id != '') {
                    $query->where('sub_status_id', $status_id);
                }
            });
            $ret1 = $ret1->where('merchant_user.user_id', $userId);

            if ($AgentFeeAccount) {
                if ($userId == $AgentFeeAccount->id) {
                    $ret1 = $ret1->where('paid_participant_ishare', '<>', 0);
                }
            }

            if ($OverpaymentAccount) {
                if ($userId == $OverpaymentAccount->id) {
                    $ret1 = $ret1->where('paid_participant_ishare', '<>', 0);
                }
            }
            $ret1_sum = clone $ret1;
            $ret1_sum = $ret1_sum->select(DB::raw('sum(amount) as amount, sum(invest_rtr) as invest_rtr, sum(paid_mgmnt_fee) as paid_mgmnt_fee, sum(commission_amount) as commission_amount,sum(up_sell_commission) as up_sell_commission, sum(paid_participant_ishare-paid_mgmnt_fee) as paid_participant_ishare , sum(actual_paid_participant_ishare-paid_mgmnt_fee) as actual_paid_participant_ishare'), DB::raw('sum(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as t_mag_fee'))->first();
            $return['list'] = $ret1;
            $return['sum'] = $ret1_sum;
        }

        return $return;
    }

    public function investorDatatableAll($userId, $fields)
    {
        $return = MerchantUser::with('merchant')->whereHas('merchant', function ($query) use ($userId) {
        });

        return $return;
    }

    public function adminDatatable($userId, $fields)
    {
        $return = $this->table->with(['marketplaceInvestors' => function ($query) use ($userId) {
            $query->where('merchant_user.user_id', $userId);
        }])->with('participantPayment')->select($fields);

        return $return;
    }

    public function merchant_details($merchant_id, $company_id = 0, $investor_id = 0)
    {
        $settings = Settings::where('id',1)->first();
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;

         $merchant = $this->table->select('merchants.id', 'merchants.agent_fee_applied', 'merchants_details.agent_name', 'max_participant_fund_per', 'merchants.name', 'rcode.code', 'rcode.description', 'funded', 'factor_rate', 'old_factor_rate', 'rtr', 'payment_amount', 'advance_type', 'date_funded', 'pmnts', 'complete_percentage', 'sub_status_id', 'commission','up_sell_commission', 'm_s_prepaid_status', 'm_mgmnt_fee', 'm_syndication_fee', 'lender_id', 'first_payment', 'last_payment_date', 'sub_status_flag', 'max_participant_fund', 'payment_end_date', 'payment_pause_id', 'label', 'cell_phone', 'underwriting_fee', 'origination_fee', 'merchants.marketplace_status', 'merchants.created_at', 'merchants.creator_id as creator', 'merchants.last_status_updated_date', 'ach_pull')->where('merchants.id', $merchant_id)->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')->with(['lendor' => function ($query) {
             $query->select('name', 'id', 'lag_time');
        }])->with(['payStatus' => function ($query) {
            $query->select('name', 'id');
        }])->leftJoin('merchants_details','merchants.id','merchants_details.merchant_id')->first();

    
        if ($merchant->creator) {
            $creator = User::find($merchant->creator);
            if ($creator) {
                $merchant->creator = $creator->name;
            } else {
                $merchant->creator = null;
            }
        }
        $merchant->pmnts = ($merchant->pmnts) ? $merchant->pmnts : 1;
        $investor_ids = $this->role->allInvestors()->pluck('id');
        $disabled_company_investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name', 'investor')->whereHas('company_relation', function ($query) {
            $query->where('company_status', 0);
        })->pluck('users.id')->toArray();
      
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->pluck('users.id')->toArray();

        $merchant_view = DB::table('merchant_user')->select(DB::raw('sum(commission_amount+amount+pre_paid+under_writing_fee+up_sell_commission) as net_zero'), DB::raw('sum(((actual_paid_participant_ishare-invest_rtr)*(1-(merchant_user.mgmnt_fee)/100))) as overpayment'), DB::raw('sum((merchant_user.invest_rtr*merchant_user.mgmnt_fee/100)) as tot_managemnt_fee'), DB::raw('sum(merchant_user.paid_mgmnt_fee) as tot_paid_managemnt_fee'))->where('merchant_id', $merchant_id)
        ->join('users', 'users.id', 'merchant_user.user_id');

        if ($company_id != 0) {
            $merchant_view = $merchant_view->where('users.company', $company_id);
        }
        if ($investor_id != 0) {
            $merchant_view = $merchant_view->where('users.id', $investor_id);
        }
        if (empty($permission)) {
            $merchant_view = $merchant_view->whereIn('merchant_user.user_id', $investor_ids);
        }
        if (count($AgentFeeAccount) > 0) {
            $merchant_view = $merchant_view->whereNotIn('users.id', $AgentFeeAccount);
        }

        $merchant_view = $merchant_view->first();
        $net_zero = $merchant_view->net_zero;
        //$overpayments = $merchant_view->overpayment;
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        if ($OverpaymentAccount) {
            $overpayments = DB::table('merchant_user')->where('merchant_id', $merchant_id)->where('user_id', $OverpaymentAccount->id)->sum('actual_paid_participant_ishare');
           
        } else {
            $overpayments = 0;
        }
        // $overpayments = DB::table('merchant_user')->where('merchant_id', $merchant_id)->sum(DB::raw('invest_rtr-actual_paid_participant_ishare'));
        // if ($overpayments > 0) {
        //     $overpayments = 0;
        // }
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->pluck('users.id')->toArray();
        $total_managemnt_fee = $merchant_view->tot_managemnt_fee;
        $total_paid_managemnt_fee = $merchant_view->tot_paid_managemnt_fee;
        $tot_mgmnt_fee_balance = $total_managemnt_fee - $total_paid_managemnt_fee;
        $company_investors = MerchantUser::where('merchant_id', $merchant_id)
        ->join('users', 'users.id', 'merchant_user.user_id');
        if ($company_id != 0) {
            $company_investors = $company_investors->where('users.company', $company_id);
        }
        if ($investor_id != 0) {
            $company_investors = $company_investors->where('users.id', $investor_id);
        }

        $company_investors = $company_investors->count();
        $payment_unique_date = ParticipentPayment::where('payment_type', 1)
        ->where('participent_payments.is_payment', 1)
        ->where('participent_payments.merchant_id', $merchant_id)
        ->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')
        ->join('users', 'users.id', 'payment_investors.user_id');
        if ($company_id != 0) {
            $payment_unique_date = $payment_unique_date->where('users.company', $company_id);
        }

        if ($investor_id != 0) {
            $payment_unique_date = $payment_unique_date->where('users.id', $investor_id);
        }
        $payment_unique_date = $payment_unique_date->groupBy('participent_payments.id'); // same date payments considered as normal payment
        $revert_payment_unique_date = clone $payment_unique_date;
        $revert_payment_unique_date = $revert_payment_unique_date->where('payment', '<', 0)->get()->toArray();

        $credit_payment_unique_date = $payment_unique_date->where('payment', '>', 0)->get()->toArray();
        if ($company_investors == 0 && $company_id != 0) {
            $payment_left = 0;
        } else {
            $payment_left = $merchant->pmnts - count($credit_payment_unique_date) + count($revert_payment_unique_date);
        }


        $lag_time = isset($merchant->lendor->lag_time) ? $merchant->lendor->lag_time : 0;
        $first_payment_day_due = strtotime($merchant->first_payment);
        if (! $first_payment_day_due) {
            $first_payment_day_due = strtotime($merchant->date_funded);
        }
        $start = Carbon::now()->createFromTimestamp($first_payment_day_due);
        $end = Carbon::now()->now();

        if ($merchant->advance_type == 'weekly_ach') {
            $nu_payment_days = floor(($start->diffInDays()) / 7);
        } else {
            $nu_payment_days = PayCalc::calculateWorkingDays($start, $end);
        }

        $payment_total = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id)
        ->where('participent_payments.is_payment', 1)
        ->groupBy('payment_date')->pluck('payment')->toArray();
        $payment_total = array_sum($payment_total);
        $data1 = ParticipentPayment::select([
            'participent_payments.id',
            'participent_payments.reason',
            'participent_payments.payment_date',
            'payment',
            DB::raw('sum((actual_participant_share-payment_investors.mgmnt_fee)) as final_participant_share'),
            'merchants.factor_rate as factor_rate',
            'merchants.commission as commission',
            'merchants.m_s_prepaid_status as s_prepaid_status',
            DB::raw('sum(payment_investors.profit) as profit_value'),
            DB::raw('sum(payment_investors.principal) as principal'),
            DB::raw('sum(payment_investors.actual_participant_share) as participant_share'),
            DB::raw('sum(payment_investors.agent_fee) as agent_fee'),
            DB::raw('sum(payment_investors.actual_overpayment) as overpayment'),
            DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'),
        ])
        ->where('participent_payments.is_payment', 1)
        ->where('participent_payments.status', ParticipentPayment::StatusCompleted)
        ->with('paymentAllInvestors')
        ->leftjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')
        ->leftJoin('users', 'users.id', 'payment_investors.user_id');
        if ($company_id != 0) {
            $data1 = $data1->where('users.company', $company_id);
        }
        if (count($AgentFeeAccount) > 0) {
            $data1 = $data1->whereNotIn('payment_investors.user_id', $AgentFeeAccount);
        }

        if ($investor_id != 0) {
            $data1 = $data1->where('payment_investors.user_id', $investor_id);
        }
        $data1 = $data1->leftJoin('merchant_user', function ($join) {
            $join->on('payment_investors.user_id', 'merchant_user.user_id');
            $join->on('payment_investors.merchant_id', 'merchant_user.merchant_id');
        })->leftJoin('merchants', 'merchants.id', 'participent_payments.merchant_id')->groupBy('payment_investors.participent_payment_id');
        if (empty($permission)) {
            $data1 = $data1->whereIn('payment_investors.user_id', $investor_ids);
        }
        $payments_sum = $data1->where('participent_payments.merchant_id', $merchant_id);
        $p_sum=clone $payments_sum;
        $disabled_company_payments=clone $payments_sum;
        $disabled_company_payments = $disabled_company_payments->whereIn('payment_investors.user_id', $disabled_company_investors);
        $disabled_company_payments = $disabled_company_payments->get();
        $p_sum=$p_sum->where('participent_payments.payment_date','<',date('Y-m-d'));
        $p_sum=$p_sum->get();
        $payments_sum=$payments_sum->get();

        $total_mgmnt_paid = $total_syndication_paid = $paid_to_participant = $ctd_our_portion = $net_total_syndication_paid2 = $total_payment = $profit_sum = $ctd_sum = $final_participant_share = $participant_share_total = $profit_value = $principal = $t_mang_fee =$participant_share_t= 0;
       $participant_share_total = array_sum(array_column($payments_sum->toArray(), 'participant_share'));
       $participant_share_t = array_sum(array_column($p_sum->toArray(), 'participant_share'));
       
        $agent_fee_total = array_sum(array_column($payments_sum->toArray(), 'agent_fee'));
        $t_mang_fee = $t_mang_fee + array_sum(array_column($payments_sum->toArray(), 'mgmnt_fee'));
        $total_payment = $total_payment + array_sum(array_column($payments_sum->toArray(), 'payment'));
        $final_participant_share = $final_participant_share + array_sum(array_column($payments_sum->toArray(), 'final_participant_share'));
        $profit_value = $profit_value + array_sum(array_column($payments_sum->toArray(), 'profit_value'));
        $principal = $principal + array_sum(array_column($payments_sum->toArray(), 'principal'));
        $ctd_sum = $total_payment;
        $disabled_company_participant_share = array_sum(array_column($disabled_company_payments->toArray(), 'final_participant_share'));
        $disabled_company_mang_fee = array_sum(array_column($disabled_company_payments->toArray(), 'mgmnt_fee'));
        $ctd_our_portion = $final_participant_share;
        $total_payment1 = $total_payment;
        $total_payment = FFM::dollar($total_payment);
        $final_participant_share = FFM::dollar($final_participant_share);
        $profit_value = FFM::dollar($profit_value);
        $principal = FFM::dollar($principal);
        $SpecialAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount->whereIn('user_has_roles.role_id',[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id')->toArray();
        $merchant_arr = MerchantUser::join('merchants', 'merchants.id', 'merchant_user.merchant_id')->select(DB::raw('sum(amount) as amount, sum(invest_rtr) as invest_rtr, sum(actual_paid_participant_ishare) as paid_participant_ishare,
                ( invest_rtr ) * merchant_user.mgmnt_fee/100 as fee,sum((invest_rtr ) * merchant_user.mgmnt_fee/100) as m_fee, sum(commission_amount+under_writing_fee+pre_paid) as pre_paid,
                sum(amount+commission_amount+under_writing_fee+pre_paid+merchant_user.up_sell_commission) as total_invested
                '), 'merchants.rtr as rtr', 'merchants.funded', 'merchants.max_participant_fund')->join('users', 'users.id', 'merchant_user.user_id')->where('merchant_user.merchant_id', $merchant_id);
        if (empty($permission)) {
            $merchant_arr = $merchant_arr->whereIn('merchant_user.user_id', $investor_ids);
        }
        if ($company_id != 0) {
            $merchant_arr = $merchant_arr->where('users.company', $company_id);
        }
        if ($investor_id != 0) {
            $merchant_arr = $merchant_arr->where('users.id', $investor_id);
        }
        if(count($SpecialAccount)>0){
        $merchant_arr = $merchant_arr->whereNotIn('merchant_user.user_id',$SpecialAccount);
        }
        $disabled_company_merchant_arr=clone $merchant_arr;
        $disabled_company_merchant_arr = $disabled_company_merchant_arr->whereIn('users.id', $disabled_company_investors);
        $disabled_company_merchant_arr = $disabled_company_merchant_arr->first();
        $merchant_arr = $merchant_arr->first();
        $mgm_fee = PaymentInvestors::where('merchant_id', $merchant_id)->where('overpayment', '!=', 0)->sum('mgmnt_fee');
        $total_rtr = $merchant_arr->invest_rtr;
        $fee = $merchant_arr->fee;
        $balance = $merchant_arr->rtr - $payment_total;
        $paid_our_portion = $merchant_arr->paid_participant_ishare;
        $balance_our_portion = ($merchant_arr->invest_rtr) - $merchant_arr->paid_participant_ishare;//echo ($merchant_arr->invest_rtr) .'=='. $merchant_arr->paid_participant_ishare;exit;
        if ($investor_id != 0) {
             $balance_merchant =$merchant_arr->invest_rtr - $participant_share_total;
            
        }else
        {
             $balance_merchant = $merchant->rtr - $total_payment1;
        }
    
       
        $full_balance = 0;
        if ($overpayments < 1) {
            if ($merchant_arr->max_participant_fund != 0) {
                $full_balance = $merchant_arr->rtr - ($paid_our_portion / ($merchant_arr->max_participant_fund / $merchant_arr->funded));
            }
        }
        if ($overpayments < 1) {
            if ($merchant_arr->max_participant_fund != 0) {
                $full_balance = $balance_our_portion * ($merchant_arr->funded / $merchant_arr->max_participant_fund);
            }
        }
        $full_balance = round($full_balance, 2);
        $dates = [];
        $dates2 = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id)
        ->where('participent_payments.is_payment', 1)
        ->distinct('payment_date')->pluck('payment_date')->toArray();
        $net_total_syndication_paid = FFM::dollar($net_total_syndication_paid2);
        $total_principal = FFM::dollar($net_total_syndication_paid2 - $profit_sum);
        $total_profit = FFM::dollar($profit_sum);
        $paid_payments = $merchant->pmnts - $payment_left;
        $expected_amount = $merchant->payment_amount * $paid_payments;
        $amount_difference = abs($ctd_sum - $expected_amount);
        $part_total_per = $syndication_fee = $part_total_amount = $management_fee = 0;
        $investor_data1 = MerchantUser::select([
            'merchant_user.id',
            'merchant_user.created_at',
            'merchant_user.user_id',
            'amount',
            'status',
            'invest_rtr',
            'actual_paid_participant_ishare',
            'paid_principal',
            'paid_profit',
            'company',
             DB::raw('upper(users.name) as name'),
            'merchant_user.under_writing_fee',
            'merchant_user.under_writing_fee_per',
            'merchant_user.syndication_fee_percentage',
            'merchant_user.up_sell_commission_per',
            'liquidity',
            'commission_amount',
            'user_has_roles.role_id',
            DB::raw('merchant_user.invest_rtr*merchant_user.mgmnt_fee/100 as mgmnt_fee_amount', 'user_has_roles.role_id'),
            DB::raw('(amount+commission_amount+under_writing_fee+pre_paid+up_sell_commission) as total_invested'),
            DB::raw('merchant_user.pre_paid'),
            'merchant_user.mgmnt_fee',
            'merchant_user.paid_mgmnt_fee',
        ])
        ->orderByDesc('amount')
        ->leftJoin('users', 'users.id', 'merchant_user.user_id')
        ->leftJoin('user_has_roles', 'merchant_user.user_id', 'user_has_roles.model_id')
        ->leftJoin('user_details', 'user_details.user_id', '=', 'merchant_user.user_id');
        if ($company_id != 0) {
            $investor_data1 = $investor_data1->where('users.company', $company_id);
        }
        if ($investor_id != 0) {
            $investor_data1 = $investor_data1->where('users.id', $investor_id);
        }
        if($settings->show_agent_account==0){
            if($AgentFeeAccount){
            $investor_data1 = $investor_data1->whereNotIn('merchant_user.user_id', $AgentFeeAccount);
            }
        }
        if($settings->show_overpayment_account==0){
            if($OverpaymentAccount){
             $investor_data1 = $investor_data1->whereNotIn('merchant_user.user_id', $OverpaymentAccount);
            }
        }
        $investor_data1 = $investor_data1->where('merchant_user.merchant_id', $merchant_id);
        if (empty($permission)) {
            $investor_data1 = $investor_data1->whereIn('merchant_user.user_id', $investor_ids);
        }
        $investor_data = $investor_data1->get();
        $total_managmentfee = 0;
        $total_syndicationfee = 0;
        $total_underwrittingfee = 0;
        

        foreach ($investor_data as $key => $investor) {
            $investor_data[$key]['tot_amount'] = $investor->amount + $investor->commission_amount + $investor->under_writing_fee + $investor->pre_paid;
            $investor_data[$key]['paid_back'] = $investor->actual_paid_participant_ishare;
            $total_managmentfee = $total_managmentfee + $investor->paid_mgmnt_fee;
            $total_syndicationfee = $total_syndicationfee + $investor->syndication_fee_amount;
            $total_underwrittingfee = $total_underwrittingfee + $investor->under_writing_fee;
            if (!in_array($investor->user_id, $disabled_company_investors) || in_array($investor_id, $disabled_company_investors)){
            $part_total_amount = $part_total_amount + $investor->amount;
            }
            if (! $merchant->m_s_prepaid_status) {
                $syndication_fee = $syndication_fee + (($merchant->rtr / $merchant->pmnts) * $investor->share / 100) * $investor->syndication_fee_percentage / 100;
            }
            if ($merchant->pmnts) {
                $management_fee = $management_fee + (($merchant->rtr / $merchant->pmnts) * $investor->share / 100) * $merchant->mgmnt_fee / 100;
            } else {
                $management_fee = 0;
            }
        }
        $part_total_per = $syndication_percent = 0;
        if (isset($merchant->funded)) {
            if (! empty($merchant->funded) && $merchant->funded != 0) {
                $part_total_per = $part_total_amount / $merchant->funded * 100;
                $syndication_percent = $part_total_amount / $merchant->funded * 100;
            }
        }
        $syndication_payment = (($merchant->rtr / $merchant->pmnts) * $part_total_per / 100) - $syndication_fee - $management_fee;
        $syndication_amount = FFM::dollar($part_total_amount);
        $bal_rtr = $total_rtr - ($participant_share_total+$agent_fee_total);
        $bal_rtr_p=$total_rtr - ($participant_share_t+$agent_fee_total);

        if ($total_rtr > 0) {
            $actual_payment_left = ($merchant->rtr) ? $bal_rtr / (($total_rtr / $merchant->rtr) * ($merchant->rtr / $merchant->pmnts)) : 0;
            $actual_payment_left_p=($merchant->rtr) ? $bal_rtr_p / (($total_rtr / $merchant->rtr) * ($merchant->rtr / $merchant->pmnts)) : 0;

        } else {
            $actual_payment_left = 0;
            $actual_payment_left_p=0;
        }

        if ($company_investors == 0 && $company_id != 0) {
            $actual_payment_left = 0;
            $actual_payment_left_p=0;
        }
        $num_due_payments = $nu_payment_days < $merchant->pmnts ? $nu_payment_days : $merchant->pmnts;
        $payment_count = $merchant->pmnts - $actual_payment_left_p;
       
        $num_pace_payment = 0;
        $exact_num_pace_payment = 0;
        if ($total_rtr) {
            $exact_num_pace_payment = ($num_due_payments - floor($payment_count));
            $num_pace_payment = ($exact_num_pace_payment > 0) ? $exact_num_pace_payment : 0;
            //floor($exact_num_pace_payment);
            $exact_num_pace_payment = $num_pace_payment;
        }

        $m_balance = ($overpayments > 1) ? 0 : (($merchant_arr->max_participant_fund != 0) ? $balance_our_portion * ($merchant_arr->funded / $merchant_arr->max_participant_fund) : 0);
        $substatus = [11, 18, 19, 20];
        if (in_array($merchant_arr->sub_status_id, $substatus)) {
            $m_balance = 0;
        } else {
            $m_balance = $m_balance;
        }
        if ($num_due_payments && ! in_array($merchant->sub_status_id, [11, 18, 19, 20, 4, 22])) {
            $pace_amount = $exact_num_pace_payment * ($merchant->rtr / ($merchant->pmnts));
            $disabled_inv_rtr = isset($disabled_company_merchant_arr) ? $disabled_company_merchant_arr->invest_rtr : 0;
            $our_pace_amount = $num_pace_payment * (($merchant_arr->invest_rtr -$disabled_inv_rtr)/ ($merchant->pmnts));
            $num_pace_percentage = abs(100 - ($num_pace_payment / $num_due_payments * 100));
            if ($m_balance < $pace_amount) {
                $num_pace_percentage = 100 - $merchant->complete_percentage;
                $pace_amount = $m_balance;
            }
        } else {
            $pace_amount = 0;
            $our_pace_amount = 0;
            $num_pace_percentage = 100;
        }
        $fractional_part = fmod($actual_payment_left, 1);
        $act_paymnt_left = floor($actual_payment_left);
        if ($fractional_part > .09) {
            $act_paymnt_left = $act_paymnt_left + 1;
        }
        $missed_payments = ParticipentPayment::where('merchant_id', $merchant_id)
        ->where('participent_payments.is_payment', 1)
        ->where('mode_of_payment', 1)->where('rcode', '>', 0)->count();
        $rcode_last_payment_date = ParticipentPayment::where('merchant_id', $merchant_id)
        ->where('participent_payments.is_payment', 1)
        ->where('rcode', '>', 0)->orderByDesc('id')->value('payment_date');
        $agent_fee_accounts = $this->role->allAgentFeeAccount()->pluck('id')->toarray();
        $agent_fee = PaymentInvestors::where('merchant_id', $merchant_id)->whereIN('user_id', $agent_fee_accounts)->sum('actual_participant_share');
        $merchant_array = [
            'merchant' => $merchant,
            'payment_left' => $payment_left,
            'amount_difference' => $amount_difference,
            'syndication_percent' => $syndication_percent,
            'syndication_amount' => $syndication_amount,
            'syndication_payment' => $syndication_payment,
            'ctd_sum' => $ctd_sum,
            't_mang_fee' => $t_mang_fee,
            'balance_mgmnt_fee' => $tot_mgmnt_fee_balance,
            'm_fee' => $merchant_arr->m_fee,
            'overpayment_fee' => $mgm_fee,
            'net_zero' => $net_zero,
            'overpayments' => $overpayments,
            'profit_value' => $profit_value,
            'balance_our_portion' => $balance_our_portion,
            'ctd_our_portion' => $ctd_our_portion,
            'disabled_company_participant_share'=>$disabled_company_participant_share,
            'disabled_company_mang_fee'=>$disabled_company_mang_fee,
            'total_payment' => $total_payment,
            'final_participant_share' => $final_participant_share,
            'principal' => $principal,
            'investor_data' => $investor_data,
            'total_managmentfee' => $total_managmentfee,
            'total_syndicationfee' => $total_syndicationfee,
            'total_underwrittingfee' => $total_underwrittingfee,
            'dates2' => $dates2,
            'actual_payment_left' => ($act_paymnt_left > 0) ? $act_paymnt_left : 0,
            'balance_merchant' => $balance_merchant,
            'num_pace_payment' => ($merchant->first_payment) ? $num_pace_payment : 0,
            'num_pace_percentage' => ($merchant->first_payment) ? $num_pace_percentage : 0,
            'pace_amount' => ($merchant->first_payment) ? $pace_amount : '',
            'our_pace_amount' => ($merchant->first_payment) ? $our_pace_amount : 0,
            'prepaid' => $merchant_arr->pre_paid,
            'full_balance' => $full_balance,
            'missed_payments' => $missed_payments,
            'total_invested' => $merchant_arr->total_invested,
            'last_rcode_date' => $rcode_last_payment_date,
            'agent_fee' =>  $agent_fee,
            'total_invest_rtr'=>$merchant_arr->invest_rtr
        ];

        return $merchant_array;
    }

    public function merchant_payoff($merchant_id)
    {
        $today = date('Y-m-d');
        $Currentdate = FFM::date($today);
        $merchant = Merchant::where('id', $merchant_id)->first();
        $business_name = $merchant->first_name;
        $first_name = $merchant->first_name;
        $full_name = $merchant->first_name.' '.$merchant->last_name;
        $business_address = $merchant->business_address;
        $business_city = $merchant->city;
        $business_state = Merchant::select('us_states.state as state_name')->where('merchants.id', $merchant_id)->leftJoin('us_states', 'us_states.id', 'merchants.state_id')->first()->state_name;
        $business_zip = $merchant->zip_code;
        $rtr = $merchant->rtr;
        $ctd = ParticipentPayment::select(DB::raw('sum(participent_payments.payment) as ctd'))->where('participent_payments.merchant_id', $merchant_id)->where('participent_payments.is_payment', 1)->first()->ctd;
        $loan_balance = $rtr - $ctd;
        if ($loan_balance) {
            if ($loan_balance < 1) {
                $loan_balance = 0;
            }
        }
        $loan_balance = FFM::dollar($loan_balance);

        $merchant_payoff_array = ['Currentdate' => $Currentdate, 'business_name' => $business_name, 'full_name' => $full_name, 'business_address' => $business_address, 'business_city' => $business_city, 'business_state' => $business_state, 'business_zip' => $business_zip, 'loan_balance' => $loan_balance, 'first_name' => $first_name];

        return $merchant_payoff_array;
    }

    public function createRequest($request)
    {
        $settings = Settings::select('email', 'forceopay')->first();
        $email_id_arr = explode(',', $settings->email);
        $creator = Auth::user()->name;
        $data_r = $request->all();


        $user_r = [];
        if (! $request->m_s_prepaid_status) {
            $data_r['m_s_prepaid_status'] = 0;
        } else {
        }
        if (! $request->underwriting_status) {
            $data_r['underwriting_status'] = 0;
        } else {
        }
        $data_r['rtr'] = PayCalc::rtr($data_r['funded'], $data_r['factor_rate']);
        $data_r['m_mgmnt_fee'] = $data_r['m_mgmnt_fee'];
        $data_r['underwriting_fee'] = isset($data_r['underwriting_fee']) ? $data_r['underwriting_fee'] : 0;
        $data_r['underwriting_status'] = json_encode($data_r['underwriting_status'], true);
        $data_r['payment_amount'] = PayCalc::getPayment($data_r['rtr'], $data_r['pmnts']);
        $data_r['actual_payment_left'] = $data_r['pmnts'];
        $data_r['creator_id'] = $request->creator_id;
        $data_r['origination_fee'] = $request->origination_fee;
        $data_r['notification_email'] = $request->merchant_email;
        $data_r['email'] = $request->merchant_email;
        $data_r['funded'] = ($data_r['funded']) ? $data_r['funded'] : 0;
        $data_r['experian_intelliscore'] = ($data_r['experian_intelliscore']) ? $data_r['experian_intelliscore'] : null;
        $data_r['experian_financial_score'] = ($data_r['experian_financial_score']) ? $data_r['experian_financial_score'] : null;
        $data_r['first_name'] = ($data_r['first_name']) ? $data_r['first_name'] : null;
        $data_r['last_name'] = ($data_r['last_name']) ? $data_r['last_name'] : null;
        $data_r['monthly_revenue'] = 0;
        $amount = trim(str_replace(',', '', $data_r['funded']));
        if (is_numeric($amount)) {
            $data_r['funded'] = $amount;
        }

        // unset($data_r['email']);
        unset($data_r['company_per']);
        unset($data_r['company_max']);
        unset($data_r['user_id']);
        $merchant = $this->table->create($data_r);
      
        if($merchant){MerchantDetails::create(['merchant_id'=>$merchant->id]);}
        $company_max = $request->company_max;
        $company = $request->company_id;
        if (! empty($merchant->id)) {
            if (! empty($company_max)) {
                foreach ($company_max as $key => $value) {
                    $company_1[$key]['merchant_id'] = $merchant->id;
                    $company_1[$key]['company_id'] = $key;
                    $company_1[$key]['max_participant'] = ($value!=null) ? $value :0;
                    $this->company->create($company_1[$key]);
                }
                // $this->company->insert($company_1);
            }
        }
        $user_r['email'] = $request->email;
        $password = $this->generateRandomString(7);
        $user_r['name'] = $data_r['name'];
        $user_r['merchant_id_m'] = $merchant->id;
        $company_amounts = $this->company->select('max_participant', 'merchant_id', 'company_id', 'users.name')->where('merchant_id', $merchant->id)->whereNotNull('max_participant')->join('users', 'users.id', 'company_amount.company_id')->get()->toArray();
        $html = '';
        $company_arr = [];
        if (! empty($company_amounts)) {
            $i = 0;
            foreach ($company_amounts as $key => $value) {
                $per = ($merchant->max_participant_fund) ? (($value['max_participant'] / $merchant->max_participant_fund) * 100) : 0;
                $html .= '<tr>
                            <td class="content" style="border:0; text-align:center; padding: 0 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;">
                                <div style="background: #fdfdff; border-radius: 8px; border: 1px solid #eeedf5; color: #535777; font-weight: bold; line-height: 26px; padding: 15px 25px;">
                                    '.$value['name'].' Participated<span style="font-weight: bold; display: block; font-size: 20px;color: #28b76e;  margin: 7px 0 0;">
                                    '.\FFM::dollar($value['max_participant']).' ('.round($per, 2).'%).</span>
                                </div>
                            </td>
                        </tr>';
                // $html .= "<div style='background: #fdfdff; border-radius: 8px; border: 1px solid #eeedf5; color: #535777; font-weight: bold; line-height: 26px; padding: 15px 25px;'>".$value['name']." Participated <span style='font-weight: bold; display: block; font-size: 20px;     color: #28b76e;  margin: 7px 0 0;'> ".FFM::dollar($value['max_participant']).'(  '.FFM::percent($per).'). </span></div>';
                $company_arr[$i]['name'] = $value['name'];
                $company_arr[$i]['max_participant'] = $value['max_participant'];
                $company_arr[$i]['per'] = $per;
                $i++;
            }
        }
        $message['title'] = $merchant->name.' Details';
        $message['subject'] = $merchant->name.' Details';
        $message['content'] = $html;
        $message['to_mail'] = $email_id_arr;
        $message['status'] = 'merchant';
        $message['merchant_id'] = $merchant->id;
        $message['merchant_name'] = $merchant->name;
        $message['company_amounts'] = $company_arr;
        $message['creator'] = $creator;
        $message['merchant_details'] = $html;
        $message['unqID'] = unqID();
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'MERD'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $email_id_arr);
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
        $user = User::where('email', $user_r['email'])->first();
        if ($user) {
            $merchant->user_id = $user->id;
            $merchant->update();
        }
        if ($user_r['email'] and ! $user) {
            $user_r['password'] = $password;
            $user_r['creator_id'] = $request->creator_id;
            session_set('user_role', 'user_merchant');
            $user = $this->table1->create($user_r);
            $userDetails = UserDetails::create(['user_id' => $user->id, 'liquidity' => '0.000']);
            $merchant->user_id = $user->id;
            $merchant->update();
            $user->assignRole('merchant');
            if ($user) {
                $m_url = url('/merchants');
                if ($request->email_notification == 1) {
                    $message['title'] = 'Login Credentials for '.$merchant->name;
                    $message['subject'] = 'Login Credentials for '.$merchant->name;
                    $message['content'] = 'Merchant Name : <a href='.$m_url.'>'.$merchant->name." </a> \n <br> Email : ".$request->email." \n <br>  Password : ".$password;
                    $message['to_mail'] = $request->email;
                    $message['status'] = 'merchant_login';
                    $message['merchant_id'] = $merchant->id;
                    $message['merchant_name'] = $merchant->name;
                    $message['username'] = isset($request->email) ? $request->email : '';
                    $message['password'] = $password;
                    $message['unqID'] = unqID();
                    try {
                        $email_template = Template::where([
                            ['temp_code', '=', 'MERC'], ['enable', '=', 1],
                        ])->first();
                        if ($email_template) {
                            $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                            dispatch($emailJob);
                            $message['to_mail'] = $this->admin_email;
                            $emailJob = (new CommonJobs($message));
                            dispatch($emailJob);
                        }
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                        exit;
                    }
                }
            }
        }

        return $merchant;
    }

    public function modify_payments1($merchant_id)
    {
        $ovp_payments = ParticipentPayment::select('payment_date', 'participent_payments.id', 'payment_type', 'reason', DB::raw('sum(payment_investors.participant_share-mgmnt_fee) as final_participant_share'))->where('participent_payments.merchant_id', $merchant_id)->orderByDesc('payment_date')->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->limit(10)->groupBy('participent_payment_id');
        $overpayments = $ovp_payments->get()->toArray();
        if (! empty($overpayments)) {
            foreach ($overpayments as $key => $value) {
                $investors = DB::table('payment_investors')->where('participent_payment_id', $value['id'])->pluck('user_id')->toArray();
                $rcode = 0;
                $debit_reason = '';
                $debit_status = '';
                if ($value['payment_type'] == 0) {
                    $debit_status = 'yes';
                    $debit_reason = $value['reason'];
                }
                $net_payment_status = 'yes';
                $return_array = $this->profitPrincipalUpdate($merchant_id, [$value['payment_date']], $value['final_participant_share'], $net_payment_status, $debit_status, $debit_reason, $investors, $rcode);
            }
        }
    }

    public function modify_payments($merchant_id)
    {
        $investors = MerchantUser::select('user_id', DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'))->where('merchant_id', $merchant_id)->get()->toArray();
        if (! empty($investors)) {
            $payments = ParticipentPayment::select('id')->where('participent_payments.payment', '==', 0)->where('merchant_id', $merchant_id)->orderByDesc('id')->first()->toArray();
            if (! empty($payments)) {
                $array = [];
                if (! empty($investors)) {
                    foreach ($investors as $key => $investor) {
                        $principal = PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id', $investor['user_id'])->sum('principal');
                        $value = $investor['investment_amount'] - $principal;
                        $profit = -$value;
                        $principal = $value;
                        $test = PaymentInvestors::select('principal', 'profit')->where('participent_payment_id', $payments['id'])->where('merchant_id', $merchant_id)->where('user_id', $investor['user_id'])->get()->toArray();
                        foreach ($test as $key1 => $aa) {
                            $array['profit'] = $profit + $aa['profit'];
                            $array['principal'] = $principal + $aa['principal'];
                            PaymentInvestors::where('participent_payment_id', $payments['id'])->where('user_id', $investor['user_id'])->update($array);
                        }
                    }
                }
            }
        }
    }

    public function modify_rtr($merchant_id, $sub_status_id, $delete_flag = false, $carry_delete_flag = true)
    {
        $merchant = Merchant::find($merchant_id);
        if ($delete_flag) {
            $mode=Settings::where('keys', 'collection_default_mode')->value('values');
            if($mode==0)
            {
                $ParticipentPayments = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id);
                $ParticipentPayments = $ParticipentPayments->where('reason','LIKE','%Changed to%');
                $ParticipentPayments = $ParticipentPayments->where('payment', 0);
                $ParticipentPayments = $ParticipentPayments->where('rcode', 0);
                $ParticipentPayments = $ParticipentPayments->orderByDesc('created_at');
                $ParticipentPayments = $ParticipentPayments->get();
                foreach ($ParticipentPayments as $key => $single) {
                    $single->delete();
                }
                if ($carry_delete_flag) {
                    CarryForward::where('merchant_id', $merchant_id)->where('type', 2)->delete();
                }
            }
        } else {
            $substatus = SubStatus::where('id', $sub_status_id)->value('name');
            $investors = MerchantUser::select('id', 'user_id','invest_rtr','paid_participant_ishare','paid_principal','paid_profit', DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'))->where('merchant_user.merchant_id', $merchant_id)->get()->toArray();
            if(strtotime($merchant->last_payment_date)>strtotime(date('Y-m-d'))){
                throw new \Exception("For those merchants who have made a future payment, can’t change the status to $substatus.", 1);
            }
            if($merchant->last_payment_date){
                $last_payment_date = date('Y-m-d',strtotime($merchant->last_payment_date));
            } else {
                $last_payment_date = date('Y-m-d', strtotime($merchant->date_funded));
            }
            $currentTime = date("H:i:s");
            $ParticipentPaymentData = [
                'merchant_id'             => $merchant_id,
                'payment_date'            => $last_payment_date,
                'payment'                 => 0,
                'model'                   => \App\ParticipentPayment::class,
                'transaction_type'        => 1,
                'status'                  => ParticipentPayment::StatusCompleted,
                'final_participant_share' => 0,
                'rcode'                   => 0,
                'payment_type'            => 1,
                'investor_ids'            => implode(',', MerchantUser::where('merchant_id', $merchant_id)->where('user_id', '!=', 504)->pluck('user_id', 'user_id')->toArray()),
                'reason'                  => 'Changed to '.$substatus,  
                'created_at'              => date('Y-m-d H:i:s', strtotime($last_payment_date. $currentTime)),
                'updated_at'              => date('Y-m-d H:i:s', strtotime($last_payment_date. $currentTime)),
                'creator_id'			  => Auth::user()->id ?? 1,
            ];
            if (in_array($sub_status_id, [4, 22, 18, 19, 20])) {
                $ParticipentPaymentData['is_profit_adjustment_added'] = 0;
            }
            $payment = ParticipentPayment::create($ParticipentPaymentData);
            $array = [];
            if (! empty($investors)) {
                foreach ($investors as $key => $investor) {
                    $principal = 0;
                    $profit    = 0;
                    if($investor['invest_rtr']>$investor['paid_participant_ishare']){
                        if (in_array($sub_status_id, [4, 22, 18, 19, 20])) {
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
                        } else {
                            $profit    = 0;
                            $principal = 0;
                        }
                    }
                    $single['merchant_id']            = $merchant_id;
                    $single['investment_id']          = $investor['id'];
                    $single['user_id']                = $investor['user_id'];
                    $single['participent_payment_id'] = $payment->id;
                    $single['participant_share']      = 0;
                    $single['mgmnt_fee']              = 0;
                    $single['overpayment']            = 0;
                    $single['profit']                 = $profit;
                    $single['principal']              = $principal;
                    if($principal || $profit){
                        PaymentInvestors::create($single);
                    }
                }
            }
        }
        GPH::PaymentToMarchantUserSync($merchant_id);
        $userIds = DB::table('merchant_user')->where('merchant_id',$merchant_id)->pluck('user_id','user_id')->toArray();
        DashboardServiceProvider::addInvestorPaymentJob($userIds);
        return 1;
    }

    public function updateRequest($request)
    {
        $settings = Settings::select('email', 'forceopay')->first();
        $email_id_arr = explode(',', $settings->email);
        $creator = Auth::user()->name;
        $merchant = $this->table->find($request->merchant_id);
        $data_r = $request->all();
        if (isset($request->m_s_prepaid_status) && !$request->m_s_prepaid_status) {
           $data_r['m_s_prepaid_status'] = 0;
        }
       
        if(isset($data_r['underwriting_status']))
        {
            $data_r['underwriting_status'] = isset($data_r['underwriting_status'])?json_encode($data_r['underwriting_status'], true):'';
        }
        if (isset($request->pay_off)) {
            $data_r['pay_off'] = 0;
        }
        if (! $request->money_request_status) {
            $data_r['money_request_status'] = 0;
        }
        if ($merchant->sub_status_id != $request->sub_status_id) {
            $substatus = SubStatus::select('name')->where('id', $data_r['sub_status_id'])->first()->toArray();
            $logArray = ['merchant_id' => $request->merchant_id, 'old_status' => $merchant->sub_status_id, 'current_status' => $data_r['sub_status_id'], 'description' => 'Merchant changed to '.$substatus['name'].' by '.$creator, 'creator_id' => Auth::user()->id];
            $log = MerchantStatusLog::create($logArray);
            $data_r['last_status_updated_date'] = $log->created_at;
            $substatus_name = SubStatus::where('id', $request->sub_status_id)->value('name');
            $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
            // update merchant status to CRM
            $form_params = [
                'method' => 'merchant_update',
                'username' => config('app.crm_user_name'),
                'password' => config('app.crm_password'),
                'investor_merchant_id'=>$request->merchant_id,
                'status'=>$substatus_name,
            ];
            try {
                $crmJob = (new CRMjobs($form_params));
                dispatch($crmJob);
                //already configured delay here
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            /////////////
        }

        $reverse_staus = 0;
        $rtr_status = 0;
        $arrayList = [7627, 7641, 7642, 7664, 7670, 7674, 7688, 7694, 7719, 7731, 7741, 7756, 7764, 7765, 7773, 7817, 7824, 7854, 7871, 7884, 7894, 7909, 7910, 7925, 7935, 7963, 7981, 7993, 8000, 8065, 8071, 8091, 8098, 8102, 8124, 8152, 8158, 8164, 8173, 8199, 8212, 8219, 8224, 8241, 8256, 8260, 8272, 8289, 8302, 8340, 8372, 8402, 8405, 8415, 8422, 8461, 8516, 8526, 8593, 8685, 8719, 8724, 8730, 8738, 8774, 8825, 8830, 8841, 8869, 8887, 8898, 8923, 8938, 8998, 9005, 9007, 9031, 9037, 9065, 9093, 9129, 9144, 9194, 9197, 9232, 9246, 9276, 9298, 9351, 9372, 9390, 9401, 9453, 9497, 9507, 9597, 7741, 7981, 8152, 8158, 8164, 8173, 8199, 8212, 8219, 8224, 8238, 8263, 8279, 8293, 8298, 8319, 8465];
        if($merchant['sub_status_id']!=$request->sub_status_id){
        if (in_array($merchant['sub_status_id'], [4, 22]) && ! in_array($request->sub_status_id, [4, 22])) {
            
            $reverse_staus = 1;
            $delete_flag = true;
            if (! in_array($request->merchant_id, $arrayList)) {
                $this->modify_rtr($request->merchant_id, $request->sub_status_id, $delete_flag);
            }
        }
        if (! in_array($merchant['sub_status_id'], [4, 22]) && in_array($request->sub_status_id, [4, 22])) {
            
            $rtr_status = 1;
            $delete_flag = false;
            if (! in_array($request->merchant_id, $arrayList)) {
                $this->modify_rtr($request->merchant_id, $request->sub_status_id, $delete_flag);
            }
        }
        if (in_array($merchant['sub_status_id'], [18, 19, 20]) && in_array($request->sub_status_id, [18, 19, 20])) {
            $delete_flag = true;
            if (! in_array($request->merchant_id, $arrayList)) {
                $this->modify_rtr($request->merchant_id, $request->sub_status_id, $delete_flag);
            }
            $delete_flag = false;
            if (! in_array($request->merchant_id, $arrayList)) {
                $this->modify_rtr($request->merchant_id, $request->sub_status_id, $delete_flag);
            }
        }
        if (in_array($merchant['sub_status_id'], [18, 19, 20]) && ! in_array($request->sub_status_id, [18, 19, 20])) {
            $data_r['old_factor_rate'] = 0;
            if ($merchant['old_factor_rate']) {
                $data_r['factor_rate'] = $merchant['old_factor_rate'];
            }
            $reverse_staus = 1;
            $delete_flag = true;
            if (! in_array($request->merchant_id, $arrayList)) {
                $this->modify_rtr($request->merchant_id, $request->sub_status_id, $delete_flag);
            }
        }
        if (! in_array($merchant['sub_status_id'], [18, 19, 20]) && in_array($request->sub_status_id, [18, 19, 20])) {
            $rtr_status = 1;
            $delete_flag = false;
            if (! in_array($request->merchant_id, $arrayList)) {
                $this->modify_rtr($request->merchant_id, $request->sub_status_id, $delete_flag);
            }
        }
    }
        $payment_status = ParticipentPayment::where('participent_payments.merchant_id', $request->merchant_id)->count();
        if ($payment_status <= 0) {
            $data_r['actual_payment_left'] = $data_r['pmnts'];
        }
        $data_r['rtr'] = PayCalc::rtr($data_r['funded'], $data_r['factor_rate']);
        $data_r['payment_amount'] = PayCalc::getPayment($data_r['rtr'], $data_r['pmnts']);
       // $data_r['sub_status_flag'] = $data_r['sub_status_flag'];
        if(isset($data_r['underwriting_fee']))
        {
            $data_r['underwriting_fee'] = isset($data_r['underwriting_fee']) ? $data_r['underwriting_fee'] : '';
        }

        if(isset($data_r['marketplace_status']))
        {
            $data_r['marketplace_status'] = (bool) isset($data_r['marketplace_status']) ? $data_r['marketplace_status'] : '';
        }
    
        $data_r['funded'] = ($data_r['funded']) ? $data_r['funded'] : 0;
        $data_r['origination_fee'] = $request->origination_fee;
        $data_r['notification_email'] = $request->merchant_email;
        $data_r['first_name'] = ($data_r['first_name']) ? $data_r['first_name'] : null;
        $data_r['last_name'] = ($data_r['last_name']) ? $data_r['last_name'] : null;
        unset($data_r['email']);
        $amount = trim(str_replace(',', '', $data_r['funded']));
        if (is_numeric($amount)) {
            $data_r['funded'] = $amount;

        }
        if(!isset($request->notify_investors) || $data_r['marketplace_status']==0)
        {
           $data_r['notify_investors'] = 0; 
        }

        unset($data_r['email']);
        unset($data_r['user_id']);
        if($data_r['max_participant_fund_per']<100){
           $data_r['agent_fee_applied'] = 0; 
        }   
        if(!isset($data_r['notify_investors'])){
            $data_r['notify_investors'] = 0;
        } 
            
        $merchant2 = $merchant->update($data_r);
        $company_max = $request->company_max;


        $company = $request->company_id;

        if (! empty($request->merchant_id)) {
            $amountCompanies = [];
            if (! empty($company_max)) {
                foreach ($company_max as $key => $value) {
                    $companyAmount = CompanyAmount::updateOrCreate(
                        ['merchant_id' => $request->merchant_id, 'company_id' => $company[$key]],
                        ['max_participant' => ($company_max[$key]) ? $company_max[$key] : 0]
                    );
                    $amountCompanies[] = $company[$key];
                }
            }
            CompanyAmount::where('merchant_id', $request->merchant_id)->whereNotIn('company_id', $amountCompanies)->get()->map(function ($amount) {
                $amount->delete();
            });
        }

        $company_amounts = $this->company->select('max_participant', 'merchant_id', 'company_id', 'users.name')->where('merchant_id', $request->merchant_id)->whereNotNull('max_participant')->join('users', 'users.id', 'company_amount.company_id')->get()->toArray();

        $check = User::select('email')->orWhere('email', $request->email)->orWhere('merchant_id_m', $request->merchant_id);

        $user_r['email'] = $request->email;
        $password = $data_r['password'];
        $user_r['name'] = $data_r['name'];
        //$user_r['merchant_id_m'] = $request->merchant_id;
        //$user                    = $merchant->getCurrentUser();


        $user = User::where('email', $request->email);
        if (! $request->email) {
            $user_id = Merchant::where('id', $request->merchant_id)->value('user_id');
            if ($user_id) {
                Merchant::where('id', $request->merchant_id)->update(['user_id'=>'']);
            }
        }
        if ($user->count() > 0) {
            $user = $user->first();
            //$request->validate(['email' => 'unique:users,email,' . $user->id]);
            $user_r['email'] = $request->email;
            if (! empty($password)) {
                $user_r['password'] = $password;
            }
            $user = $this->table1->find($user->id)->update($user_r);
        } else {
            $user = User::where('email', $user_r['email'])->first();
            if (! empty($user_r['email']) and ! $user) {
                if ($user_r['email']) {
                    $user_r['password'] = ($password);
                }
                $user_r['email'] = $request->email;
                $user_r['creator_id'] = (Auth::check()) ? Auth::user()->id : '';
                session_set('user_role', 'user_merchant');
                $user = $this->table1->create($user_r);
                $userDetails = UserDetails::create(['user_id' => $user->id, 'liquidity' => '0.000']);
                Merchant::where('id', $request->merchant_id)->update(['user_id'=>$user->id]);
                $user->assignRole('merchant');
            }
        }

        if ($request->email_notification == 1) {
            if (! empty($password) && ($request->email) && $request->merchant_email) {
                $message['title'] = 'Login Credentials for '.$request->name;
                $message['subject'] = 'Login Credentials for '.$request->name;
                $message['content'] = 'Merchant Name : '.$request->name."\n Email :".$request->email." \n  Password :".$password;
                $message['to_mail'] = $request->merchant_email;
                $message['status'] = 'merchant_login';
                $message['merchant_id'] = $request->id;
                $message['merchant_name'] = $request->name;
                $message['username'] = isset($request->email) ? $request->email : '';
                $message['password'] = $password;
                $message['unqID'] = unqID();
                try {
                    $email_template = Template::where([
                        ['temp_code', '=', 'MERC'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['to_mail'] = $this->admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
        if ($reverse_staus == 1 || $rtr_status == 1) {
            if ($reverse_staus == 1) {
                $investment_data = MerchantUser::where('merchant_id', $request->merchant_id)->where('merchant_user.status', 1)->get();
                foreach ($investment_data as $key => $investments) {
                    $invest_rtr = $merchant->factor_rate * $investments->amount;
                    MerchantUser::where('user_id', $investments->user_id)->where('merchant_id', $investments->merchant_id)->update(['invest_rtr' => $invest_rtr]);
                }
            }
            $complete_per = PayCalc::completePercentage($request->merchant_id);
            Merchant::find($request->merchant_id)->update(['complete_percentage' => $complete_per]);
        }

        return $merchant;
    }

    public function paymentUpdateRequest($request)
    {
        echo 'Function temp disabled';
        exit();
        $payment = ParticipentPayment::find($request['merchant_id']);
        $payment->total_payment = $request['total_payment'];
        $payment->participant_share = $request['participant_share'];
        $payment->amount = $request['amount'];
        $payment->mgmnt_fee = $request['mgmnt_fee'];
        $payment->syndication_fee = $request['syndication_fee'];
        $payment->transaction_type = $request['transaction_type'];
        $payment->save();
        $merchant = Merchant::find($payment->merchant_id);
        $participantPayment = $merchant->participantPayment;
        $merchant->mgmnt_fee_paid = PayCalc::mgmntFeeTotal($participantPayment);
        $merchant->syndication_fee_paid = PayCalc::syndicationFeeTotal($participantPayment);
        $merchant->participant_paid = PayCalc::paidParticipantTotal($participantPayment);
        $merchant->pmnt_amount = PayCalc::getPayment($merchant->rtr, $merchant->pmnts);
        $merchant->save();
        $merchant = Merchant::find(69);
        $merchant->save();

        return $merchant;
        $merchant = Merchant::find($request['merchant_id']);
        $merchant->participant_paid = 11;
        $merchant->save();

        return $merchant;
    }

    public function generatePayment($merchant)
    {
        $merchant_users = MerchantUser::where('merchant_id', $merchant->id)->get();
        $investor_ids = $this->role->allInvestors()->pluck('id');
        foreach ($merchant_users as $investor) {
            $paymensts = PaymentInvestors::select(DB::raw('sum(participant_share) as participant_share'), DB::raw('sum(mgmnt_fee) as mgmnt_fee'))->where('payment_investors.merchant_id', $merchant->id)->where('payment_investors.user_id', $investor->user_id)->groupBy('merchant_id')->get();
            $paid_participant_ishare = PayCalc::paidParticipantShare($paymensts);
            $paid_mgmnt_fee = PayCalc::mgmntFeeTotal($paymensts);
            $paid_syndication_fee = $merchant->m_s_prepaid_status ? 0 : PayCalc::syndicationFeeTotal($paymensts);
            $complete_per = PayCalc::completePercentage($merchant->id, $investor_ids);
            $commission_amount = $merchant->commission / 100 * $investor->amount;
            Merchant::find($merchant->id)->update(['complete_percentage' => $complete_per]);
            MerchantUser::where('user_id', $investor->user_id)->where('merchant_id', $merchant->id)->each(function($row) use ($paid_participant_ishare, $paid_mgmnt_fee, $complete_per){
                $row->update(['paid_participant_ishare' => $paid_participant_ishare, 'paid_mgmnt_fee' => $paid_mgmnt_fee, 'complete_per' => $complete_per, 'actual_paid_participant_ishare' => $paid_participant_ishare]);
            });
        }
    }

    public function flush()
    {
        $this->table = new Merchant();
    }

    public function find($id)
    {
        return $this->table->find($id);
    }

    public function delete($id)
    {
        if ($merchant = $this->find($id)) {
            return $merchant->delete();
        }

        return false;
    }

    public function searchForLenderReport($lenders, $lenderId)
    {
        $builder = $this->table->whereHas('investments', function ($query) {
            $query->where('merchant_user.status', 1);
        })->with('investmentData')->select('id', 'name', 'id', 'date_funded');
        if ($lenderId != null) {
            $builder = $builder->where('lender_id', $lenderId);
        }

        return $builder;
    }

    public function move_invested($from_investor, $to_investor, $d_amount)
    {
        $investments_s_q = DB::table('merchant_user')->where('merchant_user.user_id', $from_investor)->whereIn('merchant_user.status', [1, 3])->where('merchant_user.invest_rtr', '>', 'merchant_user.actual_paid_participant_ishare')->where('merchant_user.complete_per', '<', 99)->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->where('merchants.sub_status_id', 1);
        $investments_s_q_copy = clone $investments_s_q;
        $investments_s = $investments_s_q->select(DB::raw('SUM( (merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission)-

                actual_paid_participant_ishare/invest_rtr * (merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission) ) AS current_invested'))->first();
        $moving_percentage = ($d_amount / $investments_s->current_invested);
        $investments = $investments_s_q_copy->select('merchant_user.id', 'merchant_user.status', 'merchant_user.merchant_id', 'merchant_user.user_id', 'merchant_user.share', 'merchant_user.paid_participant', 'merchant_user.paid_mgmnt_fee', 'merchant_user.requested_time', 'merchant_user.approved_time', 'merchant_user.deal_name', 'merchant_user.transaction_type', 'merchant_user.actual_paid_participant_ishare', 'merchant_user.creator_id', 'merchant_user.s_prepaid_status', 'merchant_user.mgmnt_fee', 'merchant_user.amount', 'merchant_user.invest_rtr', 'merchant_user.share', 'merchant_user.commission_amount', 'merchant_user.pre_paid', 'merchant_user.under_writing_fee', 'merchant_user.mgmnt_fee', DB::raw('1-actual_paid_participant_ishare/invest_rtr  as balance_percentage'))->get();
        foreach ($investments as $key => $investment) {
            $moving_amount = $investment->balance_percentage * $investment->amount * $moving_percentage;
            $moving_invest_rtr = $investment->balance_percentage * $investment->invest_rtr * $moving_percentage;
            $moving_share = $investment->balance_percentage * $investment->share * $moving_percentage;
            $moving_commission_amount = $investment->balance_percentage * $investment->commission_amount * $moving_percentage;
            $moving_pre_paid = $investment->balance_percentage * $investment->pre_paid * $moving_percentage;
            $moving_under_writing_fee = $investment->balance_percentage * $investment->under_writing_fee * $moving_percentage;
            $status = DB::table('merchant_user')
            ->where('user_id', $from_investor)
            ->where('id', $investment->id)
            ->update([
                'amount' => DB::raw("amount - $moving_amount"),
                'invest_rtr' => DB::raw("invest_rtr - $moving_invest_rtr"),
                'share'  => DB::raw("share - $moving_share"),
                'commission_amount' => DB::raw("commission_amount - $moving_commission_amount"),
                'pre_paid' => DB::raw("pre_paid - $moving_pre_paid"),
                'under_writing_fee' => DB::raw("under_writing_fee - $moving_under_writing_fee"),
                'under_writing_fee_per' => DB::raw("((under_writing_fee - $moving_under_writing_fee)/(amount - $moving_amount) )*100"),
                'commission_per' => DB::raw("((commission_amount - $moving_commission_amount)/(amount - $moving_amount) )*100"),
                'syndication_fee_percentage' => DB::raw("((pre_paid - $moving_pre_paid)/(amount - $moving_amount))*100"),
                'status' => 1,
                'creator_id' => (Auth::user()) ? Auth::user()->id : null,
            ]);
            $invest_dest = MerchantUser::where('merchant_id', $investment->merchant_id)->where('user_id', $to_investor)->first();
            if ($invest_dest) {
                $status = DB::table('merchant_user')
                ->where('id', $invest_dest->id)
                ->update([
                    'amount' => DB::raw("amount + $moving_amount"),
                    'invest_rtr' => DB::raw("invest_rtr + $moving_invest_rtr"),
                    'share' => DB::raw("share + $moving_share"),
                    'commission_amount' => DB::raw("commission_amount + $moving_commission_amount"),
                    'pre_paid' => DB::raw("pre_paid + $moving_pre_paid"),
                    'under_writing_fee' => DB::raw("under_writing_fee + $moving_under_writing_fee"),
                    'under_writing_fee_per' => DB::raw("((under_writing_fee + $moving_under_writing_fee)/(amount + $moving_amount))*100"),
                    'commission_per' => DB::raw("((commission_amount + $moving_commission_amount)/(amount + $moving_amount) )*100"),
                    'syndication_fee_percentage' => DB::raw("((pre_paid + $moving_pre_paid)/(amount + $moving_amount))*100"),
                    'status' => 1,
                    'creator_id' => (Auth::user()) ? Auth::user()->id : null,
                ]);
            } else {
                $status4 = MerchantUser::create([
                    'merchant_id' => $investment->merchant_id,
                    'user_id' => $to_investor,
                    'amount' => $moving_amount,
                    'invest_rtr' => $moving_invest_rtr,
                    'share' => $moving_share,
                    'commission_amount' => $moving_commission_amount,
                    'pre_paid' => $moving_pre_paid,
                    'under_writing_fee' => $moving_under_writing_fee,
                    'status' => 1,
                    'requested_time' => $investment->requested_time,
                    'approved_time' => $investment->approved_time,
                    'deal_name' => $investment->deal_name,
                    'transaction_type' => $investment->transaction_type,
                    'under_writing_fee_per' =>($moving_amount)? $moving_under_writing_fee / $moving_amount * 100:0,
                    'syndication_fee_percentage' => '',
                    'mgmnt_fee' => '',
                    'commission_per' => ($moving_amount)?$moving_commission_amount / $moving_amount * 100:0,
                    'syndication_fee_percentage' => ($moving_amount)?($moving_pre_paid / $moving_amount * 100):0,
                    'mgmnt_fee' => $investment->mgmnt_fee,
                    'creator_id' => (Auth::user()) ? Auth::user()->id : null,
                ]);
            }
        }

        return 1;
    }

    public function searchForGeneralReport($sDate, $eDate, $ids = null, $userId = null, $groupBy = null)
    {
        if (! $userId) {
            $user = Auth::user();
            $userId = $user->id;
        }
        $builder = $this->table->where('merchants.active_status', 1)->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')->whereHas('investments', function ($query) use ($userId) {
            $query->select('amount', 'invest_rtr', 'paid_mgmnt_fee', 'paid_participant_ishare', 'mgmnt_fee', 'merchant_user.user_id', 'merchant_user.merchant_id', DB::raw('sum(
IF(paid_participant_ishare > invest_rtr, (paid_participant_ishare-invest_rtr )*(1- (merchant_user.mgmnt_fee)/100 ), 0)
) as overpayment'));
            $query->where('user_id', $userId)->where('merchant_user.status', 1);
        })->select('merchants.id', 'name', 'date_funded', 'rcode.code as last_rcode', 'merchants.last_payment_date', DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id AND participent_payments.is_payment = 1  ORDER BY payment_date DESC limit 1) last_payment_amount'))->with(['investmentData' => function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }])->whereHas('participantPayment', function ($q) use ($sDate, $eDate, $ids, $userId, $groupBy) {
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.is_payment', 1)->where('payment_investors.user_id', $userId)->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
            if ($eDate != null) {
                $q->where('participent_payments.payment_date', '<=', $eDate);
            }
            if ($sDate != null) {
                $q->where('participent_payments.payment_date', '>=', $sDate);
            }
            if ($groupBy == 2) {
                $q->groupBy('merchant_id');
                $q->groupBy(DB::raw('MONTH(payment_date)'));
            } elseif ($groupBy == 1) {
                $q->groupBy('merchant_id');
                $q->groupBy(DB::raw('WEEK(payment_date)'));
            } elseif ($groupBy == 3) {
                $q->groupBy('merchant_id');
                $q->groupBy('payment_date');
            }
        })->with(['participantPayment' => function ($q) use ($sDate, $eDate, $ids, $userId, $groupBy) {
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('payment_investors.user_id', $userId)->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
            if ($eDate != null) {
                $q->where('participent_payments.payment_date', '<=', $eDate);
            }
            if ($sDate != null) {
                $q->where('participent_payments.payment_date', '>=', $sDate);
            }
            if ($groupBy == 2) {
                $q->groupBy('merchant_id');
                $q->groupByRaw('MONTH(payment_date)');
            } elseif ($groupBy == 1) {
                $q->groupBy('merchant_id');
                $q->groupBy(DB::raw('WEEK(payment_date)'));
            } elseif ($groupBy == 3) {
                $q->groupBy('merchant_id');
                $q->groupBy('payment_date');
            }
        }]);
        if ($ids != null) {
            $builder = $builder->whereIn('merchants.id', $ids);
        }

        return $builder;
    }

    public function searchForGeneralReport_original($sDate, $eDate, $ids = null, $userId = null, $groupBy = null)
    {
        if (! $userId) {
            $user = Auth::user();
            $userId = $user->id;
        }
        $builder = $this->table->where('merchants.active_status', 1)->whereHas('investments', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('merchant_user.status', 1);
        })->select('id', 'name', 'id', 'date_funded')->with(['investmentData' => function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }])->whereHas('participantPayment', function ($q) use ($sDate, $eDate, $ids, $userId) {
            $q->where('user_id', $userId);
            if ($eDate != null) {
                $q->where('participent_payments.payment_date', '<=', $eDate);
            }
            if ($sDate != null) {
                $q->where('participent_payments.payment_date', '>=', $sDate);
            }
            $q->select('merchant_id', 'payment_date', 'payment', 'participant_share', 'mgmnt_fee', 'syndication_fee', 'final_participant_share', DB::raw('(SELECT name FROM users WHERE users.id = payment_investors.user_id) name'), DB::raw('(SELECT name FROM merchants WHERE merchants.id = participent_payments.merchant_id) merchant_name'));
        })->with(['participantPayment' => function ($q) use ($sDate, $eDate, $ids, $userId, $groupBy) {
            $q->where('user_id', $userId);
            if ($eDate != null) {
                $q->where('participent_payments.payment_date', '<=', $eDate);
            }
            if ($sDate != null) {
                $q->where('participent_payments.payment_date', '>=', $sDate);
            }
            if ($groupBy) {
                $q->select('merchant_id', 'payment_date', DB::raw('SUM(participent_payments.payment) as total_payment'), DB::raw('SUM(participent_payments.participant_share) as total_participant_share'), DB::raw('SUM(payment_investors.mgmnt_fee) as total_mgmnt_fee'), DB::raw('SUM(participent_payments.final_participant_share) as total_final_participant_share'), DB::raw('(SELECT name FROM users WHERE users.id = payment_investors.user_id) name'), DB::raw('(SELECT name FROM merchants WHERE merchants.id = participent_payments.merchant_id) merchant_name'));
                $q->groupBy('merchant_id');
            }
        }]);
        if ($ids != null) {
            $builder = $builder->whereIn('id', $ids);
        }

        return $builder;
    }

    public function searchForPaymentReport_test($date_type = null, $sDate = null, $eDate = null, $ids = null, $userIds = null, $lids = null, $payment_type = null, $subinvestors = null, $owner = null, $sub_statuses = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if ($owner) {
            $permission = 0;
        }
        $builder = $this->table->whereHas('investments', function ($query) use ($userIds, $permission, $subinvestors) {
            if (empty($permission)) {
                $query->whereIn('merchant_user.user_id', $subinvestors);
            }
            if (is_array($userIds)) {
                $query->whereIn('user_id', $userIds);
            }
            $query->where('merchant_user.status', 1);
        })->select('id', 'name', 'id', 'date_funded', 's_prepaid_status', 'factor_rate', 'syndication_fee', 'commission')->with(['investmentData3' => function ($q) use ($sDate, $eDate, $ids, $userIds, $date_type, $permission, $subinvestors, $payment_type) {
            if (empty($permission)) {
                $q->whereIn('user_id', $subinvestors);
            }
            if (is_array($userIds)) {
                $q->whereIn('user_id', $userIds);
            }
            if ($eDate) {
                $q->where('created_at', '<=', $eDate);
            }
            if ($sDate) {
                $q->where('created_at', '>=', $sDate);
            }
        }]);
        if ($lids) {
            $builder->whereIn('lender_id', $lids);
        }
        if ($sub_statuses) {
            $builder->whereIn('sub_status_id', $sub_statuses);
        }
        if ($ids != null) {
            $builder = $builder->whereIn('id', $ids);
        }
        $builder = $builder->where('active_status', 1);

        return $builder;
    }

    public function searchForPaymentReporttest($date_type = null, $sDate = null, $eDate = null, $ids = null, $userIds = null, $lids = null, $payment_type = null, $subinvestors = null, $owner = null, $sub_statuses = null, $fields = null)
    {
        echo 'hitester';
        exit();
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $userId = (Auth::user()->id);
            $userId = explode(',', $userId);
            $subadmininvestor = $investor->whereIn('creator_id', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $merchants = Merchant::with(['investments' => function ($query) use ($userIds, $permission, $subinvestors) {
            $query->select(DB::raw('sum(merchant_user.mgmnt_fee) as total_mgmnt_fee'), DB::raw('sum(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid) as invested_amount'), DB::raw('sum(merchant_user.invest_rtr) as total_rtr'), DB::raw('sum((merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid))/

            sum(merchant_user.invest_rtr - merchant_user.mgmnt_fee/100*invest_rtr)




            as profit_per,



            (

            IF(merchant_user.s_prepaid_status=1, (merchant_user.pre_paid * paid_participant_ishare/invest_rtr),0)


            +

            IF(merchant_user.s_prepaid_status=2, (merchant_user.pre_paid * (paid_participant_ishare +paid_mgmnt_fee ) /invest_rtr),0)

        ) as pre_paid'));
            $query->groupBy('merchant_id');
            if (empty($permission)) {
                $query->whereIn('merchant_user.user_id', $subinvestors);
            }
            if (is_array($userIds)) {
                $query->whereIn('merchant_user.user_id', $userIds);
            }
        }])->with(['participantPayment' => function ($q) use ($permission, $subinvestors) {
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->groupBy('payment_investors.id');
            if (empty($permission)) {
                if ($array1 && is_array($subinvestors)) {
                    $q->whereIn('payment_investors.user_id', $subinvestors);
                }
            }
        }]);
        $merchants = $merchants->select('merchants.name', 'merchants.id', 'merchants.payment_amount', 'rtr', 'pmnts');
        $merchants = $merchants->join('users as lender', 'merchants.lender_id', 'lender.id');
        $merchants = $merchants->get();

        return $merchants;
    }

    public function investments_companywise_1()
    {
        $investments = DB::table('merchant_user')->join('users', 'users.id', 'merchant_user.user_id')->select('amount', 'company', 'merchant_id')->get();
        $m_investments = [];
        foreach ($investments as $key => $value) {
            if ($value->company == 89) {
                $m_investments[$value->merchant_id][89] = (isset($m_investments[$value->merchant_id][89]) ? ($m_investments[$value->merchant_id][89]) : 0) + $value->amount;
            } else {
                $m_investments[$value->merchant_id][58] = (isset($m_investments[$value->merchant_id][58]) ? ($m_investments[$value->merchant_id][58]) : 0) + $value->amount;
            }
        }

        return $m_investments;
    }

    public function investments_companywise()
    {
        $companies = $this->role->allCompanies()->pluck('id')->toArray();
        $investments = DB::table('merchant_user')->join('users', 'users.id', 'merchant_user.user_id')->select('amount', 'company', 'merchant_id')->get();
        $m_investments = [];
        foreach ($investments as $key => $value) {
            foreach ($companies as $key1 => $value1) {
                if ($value->company == $value1) {
                    $m_investments[$value->merchant_id][$value1] = (isset($m_investments[$value->merchant_id][$value1]) ? ($m_investments[$value->merchant_id][$value1]) : 0) + $value->amount;
                }
            }
        }

        return $m_investments;
    }

    public function merchants_companywise()
    {
        $companies = $this->role->allCompanies()->pluck('id')->toArray();
        $payments = DB::table('payment_investors')->join('users', 'users.id', 'payment_investors.user_id')->select('payment_investors.id', 'payment_investors.merchant_id', 'payment_investors.actual_participant_share as participant_share', 'users.company')->get();
        $m_payments = [];
        foreach ($payments as $key => $value) {
            foreach ($companies as $key1 => $value1) {
                if ($value->company == $value1) {
                    $m_payments[$value->merchant_id][$value1] = (isset($m_payments[$value->merchant_id][$value1]) ? ($m_payments[$value->merchant_id][$value1]) : 0) + $value->participant_share;
                }
            }
        }

        return $m_payments;
    }

    public function merchants_companywise_1()
    {
        $payments = DB::table('payment_investors')->join('users', 'users.id', 'payment_investors.user_id')->select('payment_investors.id', 'payment_investors.merchant_id', 'payment_investors.actual_participant_share as participant_share', 'users.company')->get();
        $m_payments = [];
        foreach ($payments as $key => $value) {
            if ($value->company == 89) {
                $m_payments[$value->merchant_id][89] = (isset($m_payments[$value->merchant_id][89]) ? ($m_payments[$value->merchant_id][89]) : 0) + $value->participant_share;
            } else {
                $m_payments[$value->merchant_id][58] = (isset($m_payments[$value->merchant_id][58]) ? ($m_payments[$value->merchant_id][58]) : 0) + $value->participant_share;
            }
        }

        return $m_payments;
    }

    public function searchForMerchantsPerDiffReport_dong()
    {
        $companies = $this->role->allCompanies()->pluck('id')->toArray();
        $investments_companywise = $this->investments_companywise($companies);
        $merchants_companywise = $this->merchants_companywise($companies);
        $merchants = [];
        foreach ($merchants_companywise as $key => $value) {
            foreach ($companies as $key1 => $company) {
                if (! isset($value[$company])) {
                    $value[$company] = 0;
                }
                if (! isset($investments_companywise[$key][$company])) {
                    $investments_companywise[$key][$company] = 0;
                }
                $diff_merchant = $this->merchants_diff($key);
                $vp_p_1 = $value[58];
                $velocity_p_1 = $value[89];
                $velocity_i_1 = $investments_companywise[$key][89];
                $vp_i_1 = $investments_companywise[$key][58];
                $diff_merchant = (($vp_p_1) / (($vp_p_1) + ($velocity_p_1))) * 100 - (($vp_i_1) / ($velocity_i_1 + $vp_i_1)) * 100;
                $round_val = round($diff_merchant, 2);
                $abs_value = abs($round_val);
                if ($abs_value > 0.1) {
                    if (! isset($merchants[$key]['velocity_i'])) {
                        $merchants[$key]['velocity_i'] = 0;
                    }
                    if (! isset($merchants[$key]['vp_i'])) {
                        $merchants[$key]['vp_i'] = 0;
                    }
                    if (! isset($merchants[$key]['velocity_p'])) {
                        $merchants[$key]['velocity_p'] = 0;
                    }
                    if (! isset($merchants[$key]['vp_p'])) {
                        $merchants[$key]['vp_p'] = 0;
                    }
                    $merchants[$key]['velocity_i'] = $merchants[$key]['velocity_i'] + $investments_companywise[$key][89];
                    $merchants[$key]['vp_i'] = $merchants[$key]['vp_i'] + $investments_companywise[$key][58];
                    $merchants[$key]['velocity_p'] = $merchants[$key]['velocity_p'] + $value[89];
                    $merchants[$key]['vp_p'] = $merchants[$key]['vp_p'] + $value[58];
                    $merchants[$key]['id'] = $key;
                    $merchants[$key]['diff'] = $round_val;
                } else {
                }
            }
        }

        return $merchants;
    }

    public function searchForMerchantsPerDiffReportold()
    {
        $companies = $this->role->allCompanies()->pluck('id')->toArray();
        $investments_companywise = $this->investments_companywise();
        $merchants_companywise = $this->merchants_companywise();
        $merchants = [];
        foreach ($merchants_companywise as $key => $value) {
            if (! isset($value[89])) {
                $value[89] = 0;
            }
            if (! isset($value[58])) {
                $value[58] = 0;
            }
            if (! isset($value[284])) {
                $value[284] = 0;
            }
            if (! isset($investments_companywise[$key][89])) {
                $investments_companywise[$key][89] = 0;
            }
            if (! isset($investments_companywise[$key][58])) {
                $investments_companywise[$key][58] = 0;
            }
            if (! isset($investments_companywise[$key][284])) {
                $investments_companywise[$key][284] = 0;
            }
            $vp_p_1 = $value[58];
            $velocity_p_1 = $value[89];
            $participant_p_1 = $value[284];
            $velocity_i_1 = $investments_companywise[$key][89];
            $vp_i_1 = $investments_companywise[$key][58];
            $participant_i_1 = $investments_companywise[$key][284];
            $diff_merchant = (($vp_p_1) / (($vp_p_1) + ($velocity_p_1) + ($participant_p_1))) * 100 - (($vp_i_1) / (($velocity_i_1) + ($vp_i_1) + ($participant_i_1))) * 100;
            $round_val = round($diff_merchant, 2);
            $abs_value = abs($round_val);
            if ($abs_value > 0.1) {
                if (! isset($merchants[$key]['velocity_i'])) {
                    $merchants[$key]['velocity_i'] = 0;
                }
                if (! isset($merchants[$key]['vp_i'])) {
                    $merchants[$key]['vp_i'] = 0;
                }
                if (! isset($merchants[$key]['participant_i'])) {
                    $merchants[$key]['participant_i'] = 0;
                }
                if (! isset($merchants[$key]['velocity_p'])) {
                    $merchants[$key]['velocity_p'] = 0;
                }
                if (! isset($merchants[$key]['vp_p'])) {
                    $merchants[$key]['vp_p'] = 0;
                }
                if (! isset($merchants[$key]['participant_p'])) {
                    $merchants[$key]['participant_p'] = 0;
                }
                $merchants[$key]['velocity_i'] = $merchants[$key]['velocity_i'] + $investments_companywise[$key][89];
                $merchants[$key]['vp_i'] = $merchants[$key]['vp_i'] + $investments_companywise[$key][58];
                $merchants[$key]['participant_i'] = $merchants[$key]['vp_i'] + $investments_companywise[$key][284];
                $merchants[$key]['velocity_p'] = $merchants[$key]['velocity_p'] + $value[89];
                $merchants[$key]['vp_p'] = $merchants[$key]['vp_p'] + $value[58];
                $merchants[$key]['participant_p'] = $merchants[$key]['participant_p'] + $value[284];
                $merchants[$key]['id'] = $key;
                $merchants[$key]['diff'] = $round_val;
            } else {
            }
        }

        return $merchants;
    }

    public function searchForMerchantsPerDiffReport()
    {
        $companies = $this->role->allCompanies()->pluck('id')->toArray();
        $investments_companywise = $this->investments_companywise();
        $merchants_companywise = $this->merchants_companywise();
        $merchants = [];
        foreach ($merchants_companywise as $key => $value) {
            foreach ($companies as $com) {
                if (! isset($value[$com])) {
                    $value[$com] = 0;
                }
                if (! isset($investments_companywise[$key][$com])) {
                    $investments_companywise[$key][$com] = 0;
                }
            }
            $payment_sum = array_sum($value);
            $investment_sum = array_sum($investments_companywise[$key]);
            $vp_p_1 = $value[58];
            $vp_i_1 = $investments_companywise[$key][58];
            $d1 = ($payment_sum > 0) ? ($vp_p_1 / $payment_sum) : 0;
            $d2 = ($investment_sum > 0) ? ($vp_i_1 / $investment_sum) : 0;
            $diff_merchant = $d1 * 100 - $d2 * 100;
            $round_val = round($diff_merchant, 2);
            $abs_value = abs($round_val);
            if ($abs_value > 0.1) {
                foreach ($companies as $com) {
                    if (! isset($merchants[$key]['company_i_'.$com])) {
                        $merchants[$key]['company_i_'.$com] = 0;
                    }
                    if (! isset($merchants[$key]['company_p_'.$com])) {
                        $merchants[$key]['company_p_'.$com] = 0;
                    }
                    $merchants[$key]['company_i_'.$com] = $merchants[$key]['company_i_'.$com] + $investments_companywise[$key][$com];
                    $merchants[$key]['company_p_'.$com] = $merchants[$key]['company_p_'.$com] + $value[$com];
                    $merchants[$key]['id'] = $key;
                    $merchants[$key]['diff'] = $round_val;
                }
            } else {
            }
        }

        return $merchants;
    }

    public function searchForOverPaymentReport($sdate = null, $edate = null, $merchants = null, $investors = null, $company = null, $lenders = null, $sub_statuses = null,$velocity_owned = false)
    {
        $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $company_users = DB::table('users')->whereNotIn('company',$disabled_companies);
        if ($company) {
            $company_users = $company_users->where('company', $company);
        }
        if($velocity_owned){
            $company_users = $company_users->where('velocity_owned', 1);  
        }
        $company_users = $company_users->pluck('id')->toArray();
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $overpayments = MerchantUserView::whereIn('status', [1, 3]);
        if (! empty($merchants) && is_array($merchants)) {
            $overpayments = $overpayments->whereIn('merchant_id', $merchants);
        }
        if (! empty($sub_statuses) && is_array($sub_statuses)) {
            $overpayments = $overpayments->whereIn('sub_status_id', $sub_statuses);
        }
        if (! empty($lenders) && is_array($lenders)) {
            $overpayments = $overpayments->whereIn('lender_id', $lenders);
        }
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
        if($OverpaymentAccount){
            $overpayments = $overpayments->where('investor_id','!=', $OverpaymentAccount->id);
        }
        if($AgentFeeAccount){
            $overpayments = $overpayments->where('investor_id','!=', $AgentFeeAccount->id);
        }
        $overpayments = $overpayments->whereIn('investor_id', $company_users);
        $overpayments = $overpayments->where('active_status', 1);
        if (! empty($investors) && is_array($investors)) {
            $overpayments = $overpayments->whereIn('investor_id', $investors);
        }
        $overpayments = $overpayments->select(
            'Merchant as name',
            'merchant_id',
            'sub_status_id',
            DB::raw('sum((invest_rtr*((IF(s_prepaid_status=0,0,0)+mgmnt_fee)/100))) as total_fee'),
            DB::raw('sum(invest_rtr) as total_rtr'),
            DB::raw('sum(amount) as invested_amount'),
            DB::raw('sum(IF(paid_participant_ishare>invest_rtr,(paid_participant_ishare-invest_rtr)*(1-(mgmnt_fee)/100),0)) as overpayment')
        );
        $over_p = DB::table('payment_investors');
        if (! empty($investors) && is_array($investors)) {
            $over_p = $over_p->whereIn('payment_investors.user_id', $investors);
        }
        if ((! empty($lenders) && is_array($lenders)) || ! empty($sub_statuses) && is_array($sub_statuses)) {
            $over_p = $over_p->join('merchants', 'merchants.id', 'payment_investors.merchant_id');
        }
        if ((! empty($lenders) && is_array($lenders))) {
            $over_p = $over_p->whereIn('merchants.lender_id', $lenders);
        }
        if (! empty($sub_statuses) && is_array($sub_statuses)) {
            $over_p = $over_p->whereIn('sub_status_id', $sub_statuses);
        }
        if ($sdate || $edate) {
            $over_p = $over_p->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id');
            if ($sdate) {
                $over_p = $over_p->where('payment_date', '>=', $sdate);
            }
            if ($edate) {
                $over_p = $over_p->where('payment_date', '<=', $edate);
            }
        }
        if (! empty($company_users) && is_array($company_users)) {
            $over_p = $over_p->whereIn('payment_investors.user_id', $company_users);
        }
        if (! empty($merchants) && is_array($merchants)) {
            $over_p = $over_p->whereIn('payment_investors.merchant_id', $merchants);
        }
        $over_p = $over_p->groupBy('payment_investors.merchant_id');
        $over_p = $over_p->pluck(DB::raw('sum(overpayment) as overpayment'), 'payment_investors.merchant_id');
        $over_p = $over_p->toArray();
        $data2 = clone $overpayments;
        $data2 = $data2->select(
            DB::raw('sum(invest_rtr) as t_rtr'),
            DB::raw('sum(IF(paid_participant_ishare>invest_rtr,(paid_participant_ishare-invest_rtr)*(1-(mgmnt_fee)/100),0)) as t_overpayment')
        );
        $table['total']        = $data2->first();
        $table['old_data']     = $overpayments->groupBy('merchant_id');
        $table['overpayments'] = $over_p;
        return $table;
    }

    public function searchForPaymentReportApi($sDate = null, $eDate = null, $rcode = null, $ids = null, $mercant_name = null)
    {
        $table_field = 'payment_date';
        $builder = $this->table->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->where('merchant_user.user_id', $ids)->join('participent_payments', 'participent_payments.merchant_id', 'merchants.id')->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
        if ($sDate != null) {
            $builder = $builder->where('participent_payments.payment_date', '>=', $sDate);
        }
        if ($eDate != null) {
            $builder = $builder->where('participent_payments.payment_date', '<=', $eDate);
        }
        if ($rcode != null) {
            $builder = $builder->where('participent_payments.rcode', $rcode);
        }
        if ($mercant_name != null) {
            $builder = $builder->where('merchants.name', 'LIKE', '%'.$mercant_name.'%');
        }
        $builder->where('payment_investors.user_id', $ids);
        $builder = $builder->where('merchants.active_status', 1);
        $data2 = clone $builder;
        if ($sDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND payment_date >= '$sDate') debited");
        } elseif ($eDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND payment_date <= '$eDate') debited");
        } elseif ($sDate != null && $eDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND payment_date >= '$sDate' AND '$table_field' <= '$eDate') debited");
        } else {
            $query_dt = DB::raw('(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id) debited');
        }
        $table['total'] = $data2->select(
            DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id ORDER BY payment_date DESC limit 1) last_payment_amount'),
            DB::raw('(SELECT SUM(invest_rtr) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) invest_rtr'),
            DB::raw('sum(payment_investors.profit) as t_profit,sum(payment_investors.principal) as t_pricipal, count(DISTINCT merchants.id) as count,sum(payment_investors.mgmnt_fee) as t_mgmnt_fee,
            sum(merchant_user.commission_amount) as t_commission_amount, sum(payment_investors.participant_share) as t_participant_share'),
            DB::raw('sum( IF(paid_participant_ishare > invest_rtr, (paid_participant_ishare-invest_rtr)*(1- (merchant_user.mgmnt_fee)/100 ), 0) ) as t_overpayment')
        );
        $table['total'] = $table['total']->first()->toArray();
        $table['data'] = $builder->groupBy('merchants.id')->select(
            'merchants.name',
            'merchants.date_funded',
            'merchants.id',
            'merchants.last_payment_date',
            'merchants.rtr',
            'rcode.code',
            DB::raw('sum(merchant_user.invest_rtr) as total_rtr'),
            DB::raw('sum(merchant_user.syndication_fee) as syndication_fee'),
            DB::raw('sum(merchant_user.under_writing_fee) as under_writing_fee'),
            DB::raw('sum(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid) as total_invested_amount'),
            DB::raw('(SELECT sum(invest_rtr) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) gross_participant_rtr'),
            DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id ORDER BY payment_date DESC limit 1) last_payment_amount'),
            DB::raw('sum(payment_investors.profit) as profit'),
            DB::raw('sum(merchant_user.mgmnt_fee) as total_mgmnt_fee'),
            DB::raw('sum(merchant_user.commission_amount) as commission_amount'),
            DB::raw('sum(payment_investors.principal) as principal'),
            DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'),
            DB::raw('sum( IF(paid_participant_ishare > invest_rtr, (paid_participant_ishare-invest_rtr)*(1- (merchant_user.mgmnt_fee)/100 ), 0) ) as overpayment'),
            DB::raw('sum(merchant_user.invest_rtr-payment_investors.participant_share) AS gross_participant_rtr_balance'),
            DB::raw('sum(payment_investors.participant_share) as participant_share'),
            DB::raw('sum(payment_investors.participant_share-payment_investors.mgmnt_fee) as final_participant_share'),
            $query_dt
        );
        $table['data'] = $table['data']->get()->toArray();
        return $table;
    }
    
    public function searchForPaymentReportOrg($date_type = null, $sDate = null, $eDate = null, $ids = null, $userIds = null, $lids = null, $payment_type = null, $subinvestors = null, $owner = null, $sub_statuses = null, $advance_type = null, $investor_type = null, $balance_report = null, $rcode = null)
    {
        $investors = $userIds;
        $type_investors = [];
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $company_users_q = DB::table('users')->where('company', $userId);
            }
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }
        if ($owner) {
            $company_users_q = $company_users_q->where('company', $owner);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        $company_users = $company_users_q->pluck('id')->toArray();
        if ($subinvestors) {
            $permission = 0;
        }
        $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'payment_date';
        if ($payment_type != null) {
            $payment_type = ($payment_type == 'credit') ? '1' : '0';
        }
        $builder = $this->table->with(['investments' => function ($query) use ($permission, $company_users) {
            $query->select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'), DB::raw('sum(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as mgmnt_fee_amount'), 'merchant_user.mgmnt_fee as invest_mgmnt_fee', DB::raw('sum(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid+merchant_user.under_writing_fee) as invested_amount'), DB::raw('sum(merchant_user.paid_participant_ishare) as paid_participant_ishare'), DB::raw('sum(merchant_user.amount) as amount'));
            $query->whereIn('merchant_user.user_id', $company_users);
            $query->groupBy('merchant_id');
        }])->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id');
        $builder->whereIn('merchant_user.user_id', $company_users);
        $builder->join('payment_investors', 'payment_investors.investment_id', 'merchant_user.id');
        $builder->whereIn('payment_investors.user_id', $company_users);
        if (! empty($userIds) && is_array($userIds)) {
            $builder->whereIn('payment_investors.user_id', $userIds);
        }
        $builder->join('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id');
        $builder->leftjoin('rcode', 'rcode.id', 'participent_payments.rcode');
        if ($sDate != null) {
            $builder = $builder->where($table_field, '>=', $sDate);
        }
        if ($eDate != null) {
            $builder = $builder->where($table_field, '<=', $eDate);
        }
        if ($payment_type != '') {
            $builder = $builder->where('payment_type', $payment_type);
        }
        if ($rcode != null) {
            $builder = $builder->whereIn('rcode', $rcode);
        }
        if ($investor_type != '') {
        }
        if (! empty($company_users)) {
        }
        if ($lids) {
            $builder->whereIn('lender_id', $lids);
        }
        if ($sub_statuses) {
            $builder->whereIn('sub_status_id', $sub_statuses);
        }
        if ($advance_type) {
            $builder->whereIn('advance_type', $advance_type);
        }
        if ($ids != null) {
            $builder = $builder->whereIn('merchants.id', $ids);
        }
        $builder = $builder->where('active_status', 1);
        $data2 = clone $builder;
        $payment_query = '';
        if ($payment_type != '') {
            $payment_query = "AND payment_type=$payment_type";
        }
        if ($rcode != null) {
            $rcode = implode(',', $rcode);
            $payment_query .= 'AND rcode in ('.$rcode.')';
        }
        $query_dt = '';
        if ($sDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND $table_field >= '$sDate' $payment_query) debited");
        } elseif ($eDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND $table_field <= '$eDate' $payment_query) debited");
        } elseif ($sDate != null && $eDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND $table_field >= '$sDate' AND '$table_field' <= '$eDate' $payment_query) debited");
        } else {
            $query_dt = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id $payment_query) debited");
        }
        if ($balance_report === 'true') {
            $table['total'] = $data2->select(
                DB::raw('count(DISTINCT merchants.id) as count,
                sum(payment_investors.participant_share) as t_participant_share,
                sum(payment_investors.profit) as t_profit'),
                'merchants.last_payment_date', 
                DB::raw('(SELECT SUM(invest_rtr) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) invest_rtr'), 
                DB::raw('(SELECT sum(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) invested_amount'), 
                DB::raw('(SELECT sum(merchant_user.mgmnt_fee) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) invest_mgmnt_fee'), 'participent_payments.rcode', 
                DB::raw('sum(payment_investors.principal) as t_principal'), 
                DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'), 
                DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id ORDER BY payment_date DESC limit 1) last_payment_amount'), 
                DB::raw('sum(merchant_user.commission_amount) as t_commission_amount'), 
                DB::raw('sum( IF(paid_participant_ishare > invest_rtr, (paid_participant_ishare-invest_rtr)*(1- (merchant_user.mgmnt_fee)/100 ), 0) ) as t_overpayment')
            );
            $table['total'] = $table['total']->first();
        } else {
            $table['total'] = $data2->select(
                DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id ORDER BY payment_date DESC limit 1) last_payment_amount'),
                DB::raw('(SELECT SUM(invest_rtr) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) invest_rtr'), 
                DB::raw('sum(payment_investors.profit) as t_profit,
                sum(payment_investors.principal) as t_pricipal, 
                count(DISTINCT merchants.id) as count,
                sum(payment_investors.mgmnt_fee) as t_mgmnt_fee,
                sum(merchant_user.commission_amount) as t_commission_amount, 
                sum(payment_investors.participant_share) as t_participant_share'), 
                DB::raw('sum( IF(paid_participant_ishare > invest_rtr, (paid_participant_ishare-invest_rtr)*(1- (merchant_user.mgmnt_fee)/100 ), 0) ) as t_overpayment')
            );
            $table['total'] = $table['total']->first();
        }
        $table['data'] = $builder->groupBy('merchants.id')->select(
            'merchants.name',
            'merchants.date_funded',
            'merchants.id',
            'merchants.last_payment_date',
            'merchants.rtr',
            DB::raw('sum(merchant_user.invest_rtr) as total_rtr'),
            DB::raw('sum(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid) as total_invested_amount'),
            DB::raw('(SELECT sum(invest_rtr) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) particaipant_rtr'),
            DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id ORDER BY payment_date DESC limit 1) last_payment_amount'),
            DB::raw('sum(payment_investors.profit) as profit'),
            DB::raw('sum(merchant_user.mgmnt_fee) as total_mgmnt_fee'),
            DB::raw('sum(merchant_user.commission_amount) as commission_amount'),
            DB::raw('sum(payment_investors.principal) as principal'),
            DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'),
            DB::raw('sum( IF(paid_participant_ishare > invest_rtr, (paid_participant_ishare-invest_rtr)*(1- (merchant_user.mgmnt_fee)/100 ), 0) ) as overpayment'),
            DB::raw('sum(payment_investors.participant_share) as participant_share'),
            DB::raw('sum(payment_investors.participant_share-payment_investors.mgmnt_fee) as final_participant_share'), 
            $query_dt
        );
        return $table;
    }

    public function searchForPaymentReport($date_type = null, $sDate = null, $eDate = null, $ids = null, $userIds = null, $lids = null, $payment_type = null, $subinvestors = null, $owner = null, $sub_statuses = null, $advance_type = null, $investor_type = null, $rcode = null, $overpayment = null, $label = null, $mode_of_payment = null, $payout_frequency = null, $investor_label = null, $historic_status = null, $filter_by_agent_fee = null, $active = null,$transaction_id=null,$velocity_owned = false)
    {
        $investors = $userIds;
        $type_investors = [];
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $company_users_q = DB::table('users')->where('company', $userId);
            }
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        $carry_forwards_query = '';
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
            $investors_implode = implode(',', $investors);
            $carry_forwards_query = ' AND carry_forwards.investor_id IN ('.implode(',', $investors).')';
        }
        $AgentFeeId = User::AgentFeeId();
        if ($AgentFeeId) {
            $company_users_q = $company_users_q->where('id', '<>', $AgentFeeId);
        }
        if ($owner) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }
        if ($payout_frequency) {
            $company_users_q = $company_users_q->whereIn('notification_recurence', $payout_frequency);
        }
        if ($active == 1) {
            $company_users_q = $company_users_q->where('active_status', 1);
        }
        if ($active == 2) {
            $company_users_q = $company_users_q->where('active_status', 0);
        }
        if ($velocity_owned) {
            $company_users_q = $company_users_q->where('velocity_owned', 1);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        if ($investor_label != null) {
            $investor_label = implode(',', $investor_label);
            $company_users_q = $company_users_q->whereRaw('json_contains(label, \'['.$investor_label.']\')');
        }
        $company_users = $company_users_q->pluck('id')->toArray();
        if (! empty($company_users)) {
            $carry_forwards_query = ' AND carry_forwards.investor_id IN ('.implode(',', $company_users).')';
        }
        if ($subinvestors) {
            $permission = 0;
        }
        $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'payment_date';
        if ($payment_type != null) {
            $payment_type = ($payment_type == 'credit') ? '1' : '0';
        }
        $payment_query = '';
        if ($payment_type != '') {
            $payment_query = "AND payment_type=$payment_type";
        }
        
        $rcode_query = '';
        $overpayment_query = '';
        $overpayment_query1 = '';
        $queryParticipantTransactionId='';
        if ($overpayment == 1) {
            $overpayment_query = ' AND payment_investors.overpayment!=0';
        }
        if ($rcode != null) {
            $rcode_val = implode(',', $rcode);
            $rcode_query .= 'AND rcode in ('.$rcode_val.')';
        }
        $ach_query = '';
        if ($mode_of_payment != null) {
            if ($mode_of_payment == 'ach') {
                $ach_query .= ' AND mode_of_payment = 1';
            }
            if ($mode_of_payment == 'manual') {
                $ach_query .= ' AND mode_of_payment = 0';
            }
            if ($mode_of_payment == 'credit_card') {
                $ach_query .= ' AND mode_of_payment = 2';
            }
        }
        if ($transaction_id) {
            $queryParticipantTransactionId .= ' AND participent_payments.id ='. $transaction_id;
        }
        $dateFormat = ($date_type == 'true') ? 'Y-m-d H:i:s' : 'Y-m-d';
        $outDateFormat = ($date_type == 'true') ? 'Y-m-d H:i:s' : 'Y-m-d';
        if (! empty($sDate)) {
            try {
                $sDate = Carbon::createFromFormat($dateFormat, $sDate)->format($outDateFormat);
            } catch (InvalidFormatException $e) {
                $sDate = @(explode(' ', $sDate))[0];
            }
        }
        if (! empty($eDate)) {
            try {
                $eDate = Carbon::createFromFormat($dateFormat, $eDate)->format($outDateFormat);
            } catch (InvalidFormatException $e) {
                $eDate = @(explode(' ', $eDate))[0];
            }
        }
        $query_dt = '';
        $queryParticipant = '';
        $date_query = '';
        if ($sDate != null && $eDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) AS debited, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND participent_payments.is_payment = 1 AND $table_field >= '$sDate' AND $table_field <= '$eDate' $payment_query $rcode_query $ach_query $queryParticipantTransactionId and participent_payments.status=1 GROUP BY participent_payments.merchant_id) AS debited_payments");
            $date_query = "AND $table_field >= '$sDate' AND $table_field <= '$eDate'";
        } elseif ($sDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) AS debited, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND participent_payments.is_payment = 1 AND $table_field >= '$sDate' $payment_query $rcode_query $ach_query $queryParticipantTransactionId and participent_payments.status=1 GROUP BY participent_payments.merchant_id)  AS debited_payments");
            $date_query = "AND $table_field >= '$sDate'";
        } elseif ($eDate != null) {
            $query_dt = DB::raw("(SELECT SUM(payment) AS debited, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND participent_payments.is_payment = 1 AND $table_field <= '$eDate' $payment_query $rcode_query $ach_query $queryParticipantTransactionId and participent_payments.status=1 GROUP BY participent_payments.merchant_id)  AS debited_payments");
            $date_query = "AND $table_field <= '$eDate'";
        } else {
            $query_dt = DB::raw("(SELECT SUM(payment) AS debited, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 And participent_payments.is_payment = 1 AND $payment_query $rcode_query $ach_query $queryParticipantTransactionId and participent_payments.status=1  GROUP BY participent_payments.merchant_id)  AS debited_payments");
        }
        $date_query_old        ='';
        $transaction_query_old ='';
        if ($transaction_id) {
            $transaction_query_old = "AND participent_payments.id < $transaction_id";
        } else {
            if ($sDate) {
                $date_query_old = "AND $table_field < '$sDate'";
            } else {
                $date_query_old = "AND $table_field >  '1970-01-01' ";
            }
        }
        if ($sDate && $eDate) {
            $carry_forwards_query .= "AND carry_forwards.date>='$sDate' AND carry_forwards.date <= '$eDate'";
        } elseif ($sDate) {
            $carry_forwards_query .= "AND carry_forwards.date >= '$sDate'";
        } elseif ($eDate) {
            $carry_forwards_query .= "AND carry_forwards.date <= '$eDate'";
        } else {
            $carry_forwards_query .= '';
        }
        
        if ($active == 1) {
            $users = User::where('active_status', 1)->pluck('id')->toArray();
        } elseif ($active == 2) {
            $users = User::where('active_status', 0)->pluck('id')->toArray();
        } else {
            $users = User::pluck('id')->toArray();
        }
        
        $investor_query = '';
        $merchantFilterQuery = '';
        $overpaymentFilterQuery='';
        $userQuery = '';
        $queryParticipantTransactionId = '';
        $builder = $this->table;
        $assigned_merchants = DB::table('participent_payments');
        if ($rcode != null || ! empty($userIds) || $overpayment == 1 || ! empty($active)) {
            $assigned_merchants->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
        }
        if ($rcode != null) {
            $assigned_merchants = $assigned_merchants->whereIn('rcode', $rcode);
        }
        if ($transaction_id) {
            $assigned_merchants = $assigned_merchants->where('participent_payments.id', $transaction_id);
        }
        if (! empty($userIds)) {
            $assigned_merchants = $assigned_merchants->whereIn('payment_investors.user_id', $userIds);
        } elseif($active) {
            $assigned_merchants = $assigned_merchants->whereIn('payment_investors.user_id', $users);
        }
        if ($overpayment == 1) {
            $assigned_merchants = $assigned_merchants->where('payment_investors.overpayment', '!=', 0);
            $overpaymentFilterQuery .=' AND paid_participant_ishare>invest_rtr';
            
        }
        
        $company_users_val = implode(',', $company_users);
        if (! $company_users_val) {
            $company_users_val = 0;
        }
        $assigned_merchants1 = $assigned_merchants->pluck('participent_payments.merchant_id')->unique();
        $merchantUserQuery='';
        $builder = $builder->whereIn('merchants.id', $assigned_merchants1);
        $investor_query    .= 'AND user_id IN ('.$company_users_val.')';
        $userQuery         .= 'AND user_id IN ('.$company_users_val.')';
        $merchantUserQuery .=' AND merchant_user.user_id IN ('.$company_users_val.')';
        if ($filter_by_agent_fee == 1) {
            $merchantUserQuery .=' AND merchant_user.actual_paid_participant_ishare !=0';
        }
        if ($lids) {
            $builder = $builder->whereIn('lender_id', $lids);
        }
        $builder = $builder->groupBy('merchants.id');
        $builder = $builder->orderBy('merchants.id')->orderBy('merchants.name');
        
        if ($historic_status != null && $eDate < date('Y-m-d')) {
            $eDate = ($eDate > date('Y-m-d')) ? ' ' : $eDate;
            $builder = $builder->join('merchant_status_log', 'merchant_status_log.merchant_id', 'merchants.id');
            if ($sub_statuses) {
                $builder = $builder->whereIn('merchant_status_log.old_status', $sub_statuses);
            }
            $builder = $builder->join('sub_statuses', 'sub_statuses.id', 'merchant_status_log.old_status');
            $builder = $builder->where('merchant_status_log.old_status', function ($query) use ($sDate, $eDate, $sub_statuses) {
                $query->select('merchant_status_log.old_status');
                $query = $query->from('merchant_status_log');
                if ($eDate) {
                    $query = $query->where('merchant_status_log.created_at', '>=', $eDate);
                } else {
                    $query = $query->where('merchant_status_log.created_at', '<=', $eDate);
                }
                $query->whereRaw('merchants.id = merchant_status_log.merchant_id');
                if ($eDate) {
                    $query = $query->orderBy('merchant_status_log.id', 'asc');
                } else {
                    $query = $query->orderBy('merchant_status_log.id', 'desc');
                }
                $query = $query->limit(1);
            });
        } else {
            $builder = $builder->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');
            if ($sub_statuses) {
                $builder = $builder->whereIn('sub_status_id', $sub_statuses);
            }
        }
        if ($advance_type) {
            $builder = $builder->whereIn('advance_type', $advance_type);
        }
        if ($label) {
            $builder = $builder->whereIn('label', $label);
        }
        if ($ids != null) {
            $merchantFilterQuery  .= ' AND participent_payments.merchant_id IN ('.implode(',', $ids).')';
            $carry_forwards_query .= ' AND carry_forwards.merchant_id IN ('.implode(',', $ids).')';
            $builder               = $builder = $builder->whereIn('merchants.id', $ids);
        }
        if ($payment_type != '') {
            $queryParticipant .= " AND payment_type = '$payment_type'";
        }
        if ($rcode != null) {
            $queryParticipant .= ' AND rcode IN ('.implode(',', $rcode).')';
        }
        if ($mode_of_payment != null) {
            if ($mode_of_payment == 'ach') {
                $queryParticipant .= ' AND mode_of_payment = 1';
            }
            if ($mode_of_payment == 'manual') {
                $queryParticipant .= ' AND mode_of_payment = 0';
            }
            if ($mode_of_payment == 'credit_card') {
                $queryParticipant .= ' AND mode_of_payment = 2';
            }
        }
        if ($transaction_id) {
            $queryParticipantTransactionId .= ' AND participent_payments.id ='. $transaction_id;
        }
        $start = request()->input('start');
        $limit = request()->input('length');
        $limitQuery = '';
        if (request()->input('report_totals') != 1 && ! empty($limit)) {
            $limitQuery = ' LIMIT '.$limit.' OFFSET '.($start * $limit);
        }
        $builder->where('active_status', 1)->leftJoin(DB::raw("(SELECT SUM(payment_investors.profit) as profit,
        round(SUM(payment_investors.principal),2) AS principal,
        SUM(payment_investors.mgmnt_fee) AS mgmnt_fee,
        SUM(payment_investors.participant_share) AS participant_share,
        round(SUM(payment_investors.agent_fee),2) AS agent_fee,
        round(SUM(payment_investors.actual_participant_share),2) AS actual_participant_share,
        SUM(payment_investors.overpayment) AS overpayment,
        participent_payments.merchant_id FROM payment_investors  
        LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id 
        WHERE participent_payments.merchant_id > 0 
        $merchantFilterQuery $payment_query $rcode_query $ach_query $investor_query $date_query $overpayment_query $queryParticipantTransactionId $userQuery $queryParticipant GROUP BY participent_payments.merchant_id) as merch_payment_sub"),'merch_payment_sub.merchant_id', '=', 'merchants.id');
        $builder = $builder->leftJoin(DB::raw("(SELECT SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as invested_amount,
        SUM(merchant_user.amount) as amount,
        SUM( IF(paid_participant_ishare > invest_rtr ,merchant_user.paid_participant_ishare-(merchant_user.paid_participant_ishare-invest_rtr), merchant_user.paid_participant_ishare ) ) AS paid_participant_ishare,
        SUM(merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100) AS mgmnt_fee_amount,
        SUM(merchant_user.invest_rtr) as invest_rtr,
        SUM((merchant_user.amount*old_factor_rate)) as settled_rtr,
        merchant_user.merchant_id FROM merchant_user  
        LEFT JOIN merchants on merchants.id=merchant_user.merchant_id 
        WHERE merchant_user.merchant_id > 0 
        $merchantUserQuery $overpaymentFilterQuery  GROUP BY merchant_user.merchant_id) as merchant_user_sub"),'merchant_user_sub.merchant_id', '=', 'merchants.id');
        $builder = $builder->leftJoin(DB::raw("(SELECT SUM(payment_investors.actual_participant_share - payment_investors.mgmnt_fee) as net_balance,SUM(payment_investors.agent_fee) as net_bal_agent_fee, participent_payments.merchant_id FROM payment_investors  LEFT JOIN participent_payments on
        payment_investors.participent_payment_id=participent_payments.id
        WHERE participent_payments.merchant_id > 0
        AND participent_payments.merchant_id IN (SELECT merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0
        $merchantFilterQuery  $date_query $queryParticipant )
        $merchantFilterQuery $date_query_old $transaction_query_old $payment_query  $ach_query $overpayment_query $userQuery GROUP BY participent_payments.merchant_id ORDER BY participent_payments.merchant_id ASC $limitQuery) as net_balance_payments"), 'net_balance_payments.merchant_id', '=', 'merchants.id');
        $builder = $builder->leftJoin(DB::raw("(SELECT SUM(payment_investors.actual_participant_share) as net_balance_1, participent_payments.merchant_id FROM payment_investors  LEFT JOIN participent_payments on
        payment_investors.participent_payment_id=participent_payments.id
        WHERE participent_payments.merchant_id > 0
        AND participent_payments.merchant_id IN (SELECT merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0
        $merchantFilterQuery  $date_query $queryParticipant )
        $merchantFilterQuery $date_query_old $transaction_query_old $payment_query $rcode_query $ach_query $investor_query $overpayment_query $userQuery GROUP BY participent_payments.merchant_id ORDER BY participent_payments.merchant_id ASC $limitQuery) as net_balance"), 'net_balance.merchant_id', '=', 'merchants.id');
        $builder = $builder->leftJoin(DB::raw("(SELECT SUM(carry_forwards.amount) as carry_profit , carry_forwards.merchant_id FROM carry_forwards  LEFT JOIN merchants on carry_forwards.merchant_id=merchants.id WHERE type=2 $carry_forwards_query group by carry_forwards.merchant_id) as user_carry_profit "), 'user_carry_profit.merchant_id', '=', 'merchants.id');
        $builder = $builder->leftJoin(DB::raw('(SELECT code, rcode.id FROM rcode GROUP BY rcode.id) code_merchant'), 'code_merchant.id', '=', 'merchants.last_rcode');
        $builder = $builder->leftJoin(DB::raw('(SELECT SUBSTRING_INDEX(GROUP_CONCAT(payment ORDER BY payment_date DESC), ",", 1) AS last_payment_amount,
        participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND payment > 0 AND participent_payments.payment_type != "" AND participent_payments.payment_type IS NOT NULL GROUP BY participent_payments.merchant_id ORDER BY payment_date DESC ) AS last_payment_amount_payments'), 'last_payment_amount_payments.merchant_id', '=', 'merchants.id');
        $builder = $builder->leftJoin($query_dt,'debited_payments.merchant_id', '=', 'merchants.id');
        $builder->join(DB::raw(" (SELECT merchant_id, id FROM participent_payments WHERE merchant_id > 0 $payment_query $merchantFilterQuery $date_query $queryParticipant  GROUP BY merchant_id) AS p_payment_sub "), 'p_payment_sub.merchant_id', '=', 'merchants.id');
        $builder = $builder->select(
            'merchants.id',
            'merchants.name',
            'merchants.rtr',
            'merchants.last_rcode',
            'merchants.last_payment_date',
            'merchants.date_funded',
            'merchants.creator_id',
            'merchants.created_at',
            'sub_statuses.name as substatus_name',
            DB::raw('(SELECT merchant_status_log.created_at FROM merchant_status_log WHERE merchant_status_log.merchant_id=merchants.id Order By created_at desc limit 1)  AS last_created_at'),
            DB::raw('(SELECT participent_payments.created_at FROM participent_payments WHERE participent_payments.merchant_id=merchants.id ORDER BY created_at DESC LIMIT 1) as last_payment_created_at'),
            DB::raw('(SELECT participent_payments.creator_id FROM participent_payments WHERE participent_payments.merchant_id=merchants.id ORDER BY created_at DESC LIMIT 1) as last_payment_creator_id'),
            DB::raw('debited_payments.debited, 
            merch_payment_sub.profit,
            merch_payment_sub.actual_participant_share,
            merch_payment_sub.principal, 
            merch_payment_sub.mgmnt_fee, 
            merch_payment_sub.agent_fee,
            merch_payment_sub.overpayment,
            IF(user_carry_profit.carry_profit,user_carry_profit.carry_profit,0) AS carry_profit,
            merch_payment_sub.participant_share, 
            merchant_user_sub.amount,
            merchant_user_sub.invest_rtr,
            merchant_user_sub.invested_amount,
            merchant_user_sub.invest_rtr,
            merchant_user_sub.mgmnt_fee_amount,
            merchant_user_sub.paid_participant_ishare,
            merchant_user_sub.settled_rtr,
            net_balance_payments.net_balance, 
            net_balance_payments.net_bal_agent_fee,
            net_balance.net_balance_1,
            code_merchant.code, 
            last_payment_amount_payments.last_payment_amount')
        );
        if ($historic_status != null) {
            $builder = $builder->withCount(['investmentData1 AS invested_amount1' => function ($query) use ($company_users) {
                $query->select(DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) AS invested_amount1'));
                $query->whereIn('merchant_user.user_id', $company_users);
            }]);
            $builder = $builder->addSelect('sub_statuses.id as substatus_id');
        }
        $builder = $builder->havingRaw('round(merch_payment_sub.principal,2) != round(merch_payment_sub.profit*-1,2) OR merch_payment_sub.actual_participant_share >= 0');
        $builder = $builder->get();
        return $builder;
    }

    public function findIfBelongsToUser($id, $userId)
    {
        return $this->table->with('lendor')->whereHas('investments', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['participantPayment' => function ($query) use ($userId) {
            $query->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
            $query->where('payment_investors.user_id', $userId);
        }])->whereId($id)->first();
    }

    public function countMerchants($value = '')
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $count = $this->table;
        if ($value) {
            $count->where('user_id', $value);
        }
        $count = $count->where('active_status', 1);
        $tcount = $count->count();

        return $tcount;
    }

    public function searchForInvestorReAssignmentReport($startDate, $endDate, $investors, $merchants)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $setInvestors = [];
        $investors_array = Role::whereName('investor')->first()->users;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $investors_array = $investors_array->where('company', $userId);
            } else {
                $investors_array = $investors_array->where('creator_id', $userId);
            }
        }
        foreach ($investors_array as $key => $investor) {
            $setInvestors[] = $investor->id;
        }
        $data = ReassignHistory::select(DB::raw('upper(merchants.name) as name'),'reassign_history.created_at as created_at_time','amount','investor1_total_liquidity','investor2_total_liquidity','liquidity_change','reassign_history.creator_id','reassign_history.merchant_id','reassign_history.investor2','reassign_history.investor1',DB::raw('upper(users.name) as investor_name') )
        ->leftJoin('merchants','merchants.id','reassign_history.merchant_id');
       
        if($merchants)
        {
            $data=$data->whereIn('reassign_history.merchant_id',$merchants);
        }
        $data=$data->leftJoin('users','users.id','reassign_history.investor1');
        if(empty($permission))
        {
             $data=$data->whereIn('users.id', $setInvestors);
        }

        // whereHas('investmentData1', function ($q1) use ($setInvestors, $permission, $investors) {
        //     if (empty($permission)) {
        //         $q1->whereIn('id', $setInvestors);
        //     }
        // })->with(['investmentData1' => function ($q1) use ($setInvestors, $permission, $investors) {
        //     if (empty($permission)) {
        //         $q1->whereIn('id', $setInvestors);
        //     }
        // }])

        $data=$data->with(['investmentData2' => function ($q2) use ($setInvestors, $permission, $investors) {
            if (empty($permission)) {
                $q2->whereIn('id', $setInvestors);
            }
        }])->whereHas('investmentData2', function ($q2) use ($setInvestors, $permission, $investors) {
            if (empty($permission)) {
                $q2->whereIn('id', $setInvestors);
            }
        });
        // ->with(['merchantData'=> function  ($q3) use ($merchants){
        //     $q3->where('active_status', 1);

        // }])->whereHas('merchantData', function ($q3) use ($merchants) {
        //     $q3->where('active_status', 1);
        // });

        if ($startDate) {
            $data = $data->where('reassign_history.created_at', '>=', date('Y-m-d', strtotime($startDate)));
        }
        if ($endDate) {
            $data = $data->where('reassign_history.created_at', '<=', date('Y-m-d', strtotime($endDate)));
        }
        if ($investors && is_array($investors)) {
            $data = $data->where(function ($q) use ($investors) {
                $q->where('investor1', $investors);
                $q->orWhere('investor2', $investors);
            });
        }
        if ($merchants && is_array($merchants)) {
            $data = $data->whereIn('merchant_id', $merchants);
        }
        $data = $data->orderByDesc('reassign_history.created_at');

        return $data;
    }

    public function searchForInvestorAssignmentReport($startDate, $endDate, $investors, $merchants)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            if (Auth::user()->hasRole(['company'])) {
                $subadmininvestor = $investor->where('company', $userId);
            } else {
                $subadmininvestor = $investor->where('creator_id', $userId);
            }
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $SpecialAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount->whereIn('user_has_roles.role_id',[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id')->toArray();
        $disabled_company_investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status', 0);
        })->pluck('users.id')->toArray();
        $report = MerchantUser::whereNotIn('merchant_user.user_id', $disabled_company_investors)->whereIn('merchant_user.status', [1, 3])->whereHas('merchant', function ($query) {
            $query->where('active_status', 1);
        })->with(['merchant']);
        if ($investors && is_array($investors)) {
            $report = $report->whereIn('merchant_user.user_id', $investors);
        }
        if ($merchants && is_array($merchants)) {
            $report = $report->whereIn('merchant_user.merchant_id', $merchants);
        }
        if ($startDate != 0) {
            // $startDate = $startDate.' 00:00:00';
            $report = $report->whereDate('merchant_user.created_at', '>=', $startDate);
        }
        if ($endDate != 0) {
            // $endDate = $endDate.' 23:23:59';
            $report = $report->whereDate('merchant_user.created_at', '<=', $endDate);
        }
        if(count($SpecialAccount) > 0){
            $report = $report->whereNotIn('merchant_user.user_id', $SpecialAccount);
        }
        if (empty($permission)) {
            $report = $report->whereIn('merchant_user.user_id', $subinvestors);
        }
        $data = $report->orderByDesc('merchant_user.created_at');
        $data = $report->where('status', '=', 1)->with('merchant');

        return $data;
    }
   public function searchForCommissionReport(
        $date_type,
        $startDate,
        $endDate,
        $investors,
        $merchants,
        $stime = null,
        $etime = null,
        $type = null,
        $owner = null,
        $velocity_owned = false
    ) {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }
        if ($owner) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }
        if($velocity_owned){
            $company_users_q = $company_users_q->where('velocity_owned', 1);
        }
       
       $company_users = $company_users_q->pluck('id')->toArray();
        if ($type == 'true') {
            if ($stime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$stime : null;
            }
            if ($etime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$etime : null;
            }
        }
        $this->table = $this->table->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->where('merchant_user.up_sell_commission_per','!=',0)
        ->whereIn('merchant_user.status', [1,3]);
        $table_field = ($date_type == 'true') ? 'merchants.created_at' : 'date_funded';
        if ($table_field == 'merchants.created_at') {
            if ($stime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$stime : null;
            }
            if ($etime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$etime : null;
            }
        }
        if ($merchants && is_array($merchants)) {
            $this->table = $this->table->whereIn('merchants.id', $merchants);
        }
      
        if ($startDate && $startDate != null) {
            $this->table = $this->table->where($table_field, '>=', $startDate);
        }
        if ($endDate && $endDate != null) {
            $this->table = $this->table->where($table_field, '<=', $endDate);
        }
      
        $this->table = $this->table->whereIn('merchant_user.user_id', $company_users);
        $this->table = $this->table->orderBy('merchants.date_funded', 'DESC');
        $this->table = $this->table->where('merchants.active_status', 1);
        $data2 = clone $this->table;
        $table['total'] = $data2->select(DB::raw('sum(commission_amount) as t_commission_amount,sum(merchant_user.up_sell_commission) as t_up_sell_commission,sum(merchant_user.under_writing_fee) as t_under_writing_fee, count(DISTINCT merchants.id) as count,sum(amount) as t_total_amount,sum(pre_paid) as t_pre_paid_amount,sum(invest_rtr) as t_invest_rtr,sum(invest_rtr * mgmnt_fee/100) as t_mgmnt_fee'))->first();
        $table['data'] = $this->table->groupBy('merchants.id')->select(DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid+merchant_user.up_sell_commission) as invested_amount'), 
            'merchants.id','merchants.funded', 'merchants.created_at', 
            'merchant_user.created_at as merchant_user_created_at', 'merchants.creator_id', 'merchants.name', 'merchants.date_funded', 'merchants.commission','merchants.up_sell_commission', DB::raw('sum(commission_amount) as commission_amount, sum(merchant_user.up_sell_commission) as m_up_sell_commission, sum(pre_paid) as pre_paid,sum(merchant_user.under_writing_fee) as under_writing_fee,  sum( invest_rtr * mgmnt_fee/100) as mgmnt_fee, sum(amount) as i_amount, sum(invest_rtr) as i_rtr', 'merchants.funded', 'merchants.underwriting_status'));

        return $table;
    }



    public function searchForInvestorReport(
        $date_type,
        $advance_type,
        $lenders,
        $startDate,
        $endDate,
        $investors,
        $merchants,
        $stime = null,
        $etime = null,
        $type = null,
        $industries = null,
        $owner = null,
        $statuses = null,
        $investor_type = null,
        $substatus_flag = null,
        $label = null,
        $investor_label = null,
        $order = null,
        $active = null,
        $velocity_owned = false
    ) {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }
        if ($owner) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }
        //dd($active);
        if ($active == '1') {
            $company_users_q = $company_users_q->where('active_status', 1);
        }
        if ($active == '2') {
            $company_users_q = $company_users_q->where('active_status', 0);
        }
        if ($velocity_owned) {
            $company_users_q = $company_users_q->where('velocity_owned', 1);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        if ($investor_label != null) {
            $company_users_q = $company_users_q->where(function ($q) use ($investor_label) {
                $i = 1;
                foreach ($investor_label as $inv_label) {
                    if ($i == 1) {
                        $q->whereRaw('json_contains(label, \'['.$inv_label.']\')');
                    } else {
                        $q->orWhereRaw('json_contains(label, \'['.$inv_label.']\')');
                    }
                    $i++;
                }
            });
        }
        $SpecialAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE, User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id', 'users.id')->toArray();
        if ($SpecialAccount) {
            $company_users_q = $company_users_q->whereNotIn('id', $SpecialAccount);
        }
        $company_users = $company_users_q->pluck('id')->toArray();
        if ($type == 'true') {
            if ($stime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$stime.':00' : null;
            }
            if ($etime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$etime.':59' : null;
            }
        }
        $this->table = $this->table->leftJoin('industries', 'industries.id', 'merchants.industry_id')->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->whereIn('merchant_user.status', [
            1,
            3,
        ]);
        $table_field = ($date_type == 'true') ? 'merchants.created_at' : 'date_funded';
        if ($table_field == 'merchants.created_at') {
            if ($stime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$stime.':00' : null;
            }
            if ($etime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$etime.':59' : null;
            }
        }
        if ($merchants && is_array($merchants)) {
            $this->table = $this->table->whereIn('merchants.id', $merchants);
        }
        if ($label) {
            $this->table = $this->table->whereIn('merchants.label', $label);
        }
        if ($startDate && $startDate != null) {
            $this->table = $this->table->where($table_field, '>=', $startDate);
        }
        if ($endDate && $endDate != null) {
            $this->table = $this->table->where($table_field, '<=', $endDate);
        }
        if ($lenders && is_array($lenders)) {
            $this->table = $this->table->whereIn('merchants.lender_id', $lenders);
        }
        $this->table = $this->table->whereIn('merchant_user.user_id', $company_users);
        $default_date = ! empty($endDate) ? $endDate : now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        if ($industries && is_array($industries)) {
            $this->table = $this->table->whereIn('merchants.industry_id', $industries);
        }
        if ($statuses && is_array($statuses)) {
            $this->table = $this->table->whereIn('merchants.sub_status_id', $statuses);
        }
        if ($advance_type && is_array($advance_type)) {
            $this->table = $this->table->whereIn('merchants.advance_type', $advance_type);
        }
        if ($substatus_flag) {
            $this->table = $this->table->whereIn('merchants.sub_status_flag', $substatus_flag);
        }
        $this->table = $this->table->where('merchants.active_status', 1);
        $data2 = clone $this->table;
        $array = [4,22];
        $array1 = implode(',', $array);
        $table['total'] = $data2->select(DB::raw('sum(commission_amount) as t_commission_amount,sum(merchant_user.up_sell_commission) as t_up_sell_commission,sum(merchant_user.under_writing_fee) as t_under_writing_fee, count(DISTINCT merchants.id) as count,sum(amount) as t_total_amount,sum(pre_paid) as t_pre_paid_amount,sum(invest_rtr) as t_invest_rtr,sum(invest_rtr * mgmnt_fee/100) as t_mgmnt_fee'))->first();
        $table['data'] = $this->table->groupBy('merchants.id')->select(DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid+merchant_user.up_sell_commission) as invested_amount,

            sum( merchant_user.actual_paid_participant_ishare- paid_mgmnt_fee ) as ctd,


                sum(
IF(actual_paid_participant_ishare > invest_rtr, (actual_paid_participant_ishare-invest_rtr )*(1- (merchant_user.mgmnt_fee)/100 ), 0)
) as overpayment'), DB::raw('sum(

 '.$merchant_day.'

           *   (


                          (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                                    -

                                 IF(
                                    (merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee),

                               (merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee),0
                                    )

                           )
            )
            as default_amountfff'),DB::raw('( '.$merchant_day.' * (
                sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                -
                ( sum( IF(sub_status_id IN ('.$array1.'),
                (merchant_user.actual_paid_participant_ishare - (IF(merchant_user.paid_mgmnt_fee,merchant_user.paid_mgmnt_fee,0)) ),
                ( IF((merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                <
                (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ),
                (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission),
                (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee )
                ))
                )
                )
                )
                )
                )
                as default_amount'), 'merchants.id', 'merchants.advance_type', 'industries.name as industry_name', 'merchants.funded', 'merchants.created_at', 'merchant_user.created_at as merchant_user_created_at', 'merchants.creator_id', 'merchants.name', 'merchants.date_funded', 'merchants.commission','merchants.up_sell_commission', 'merchants.sub_status_id', DB::raw('sum(commission_amount) as commission_amount, sum(merchant_user.up_sell_commission) as m_up_sell_commission, sum(pre_paid) as pre_paid,sum(merchant_user.under_writing_fee) as under_writing_fee,  sum( invest_rtr * mgmnt_fee/100) as mgmnt_fee, sum(amount) as i_amount, sum(invest_rtr) as i_rtr', 'merchants.funded', 'merchants.underwriting_status'));

        return $table;
    }

    public function searchForSyndicateReport($date_type, $advance_type, $lenders, $startDate, $endDate, $investors, $merchants, $stime = null, $etime = null, $type = null, $industries = null, $owner = null, $statuses = null, $investor_type = null, $substatus_flag = null, $label = null, $investor_label = null,$velocity_owned = false)
    {
        $overpayment_account = User::join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id')->join('roles', 'roles.id', '=', 'user_has_roles.role_id')->whereIn('roles.id', [User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE])->select('users.id')->get()->toArray();
        $overpayment_account_arr = array_unique(array_column($overpayment_account, 'id'));

        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }
        if ($owner) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        if($velocity_owned){
            $company_users_q = $company_users_q->where('users.velocity_owned', 1);
        }
        if ($investor_label != null) {
            $company_users_q = $company_users_q->where(function ($q) use ($investor_label) {
                $i = 1;
                foreach ($investor_label as $inv_label) {
                    if ($i == 1) {
                        $q->whereRaw('json_contains(label, \'['.$inv_label.']\')');
                    } else {
                        $q->orWhereRaw('json_contains(label, \'['.$inv_label.']\')');
                    }
                    $i++;
                }
            });
        }
        $company_users = $company_users_q->pluck('id')->toArray();
        // if ($type == 'true') {
        //     if ($stime != 0) {
        //         $startDate = ($startDate) ? $startDate.' '.$stime : null;
        //     }
        //     if ($etime != 0) {
        //         $endDate = ($endDate) ? $endDate.' '.$etime : null;
        //     }
        // }
        $datas = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->whereIn('merchant_user.status', [1, 3])->join('users', 'users.id', 'merchant_user.user_id')->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');
        $table_field = ($date_type == 'true') ? 'merchants.created_at' : 'date_funded';

        if ($table_field == 'merchants.created_at') {
            if ($stime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$stime : null;
            }
            if ($etime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$etime : null;
            }
        }
        if ($merchants && is_array($merchants)) {
            $datas = $datas->whereIn('merchants.id', $merchants);
        }
        if ($label) {
            $datas = $datas->whereIn('merchants.label', $label);
        }
        if ($startDate && $startDate != null) {
            $datas = $datas->where($table_field, '>=', $startDate);
        }
        if ($endDate && $endDate != null) {
            $datas = $datas->where($table_field, '<=', $endDate);
        }
        if ($lenders && is_array($lenders)) {
            $datas = $datas->whereIn('merchants.lender_id', $lenders);
        }
        $datas = $datas->whereIn('merchant_user.user_id', $company_users);
        if (count($overpayment_account_arr) > 0) {
            $datas = $datas->whereNotIn('merchant_user.user_id', $overpayment_account_arr);
        }
        // $default_date = !empty($endDate) ? $endDate : now();
        // $merchant_day = PayCalc::setDaysCalculation($default_date);
        if ($industries && is_array($industries)) {
            $datas = $datas->whereIn('merchants.industry_id', $industries);
        }
        if ($statuses && is_array($statuses)) {
            $datas = $datas->whereIn('merchants.sub_status_id', $statuses);
        }
        if ($advance_type && is_array($advance_type)) {
            $datas = $datas->whereIn('merchants.advance_type', $advance_type);
        }
        if ($substatus_flag) {
            $datas = $datas->whereIn('merchants.sub_status_flag', $substatus_flag);
        }
        $datas = $datas->where('merchants.active_status', 1)->select(
            'merchants.id',
            'merchants.name',
            'merchants.funded',
            'merchants.factor_rate',
            'merchants.rtr',
            'merchants.advance_type',
            'merchants.pmnts',
            'merchants.date_funded',
            'merchant_user.invest_rtr',
            'merchant_user.under_writing_fee',
            'merchant_user.under_writing_fee_per',
            'merchant_user.amount',
            DB::raw('upper(users.name) as investor_name'),
            DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid+merchant_user.up_sell_commission) as invested_amount'),
            'merchant_user.commission_amount',
            'merchant_user.up_sell_commission',
            'merchant_user.pre_paid',
            'merchant_user.syndication_fee_percentage',
            'merchant_user.mgmnt_fee',
            'merchant_user.paid_mgmnt_fee',
            'actual_paid_participant_ishare',
            'paid_mgmnt_fee',
            'sub_statuses.name as merchant_status'
        );
        $datas = $datas->orderByDesc('merchants.date_funded')->get()->toArray();

        return $datas;
    }

    public function searchForInvestorReportView($date_type, $advance_type, $lenders, $startDate, $endDate, $investors, $merchants, $stime = null, $etime = null, $type = null, $industries = null, $owner = null, $statuses = null, $investor_type = null, $substatus_flag = null, $label = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }
        if ($owner) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        $company_users = $company_users_q->pluck('id')->toArray();
        if ($type == 'true') {
            if ($stime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$stime : null;
            }
            if ($etime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$etime : null;
            }
        }
        $ReportTable = InvestmentReportView::orderBy('Merchant');
        $table_field = ($date_type == 'true') ? 'created_at' : 'date_funded';
        if ($table_field == 'created_at') {
            if ($stime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$stime : null;
            }
            if ($etime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$etime : null;
            }
        }
        if ($merchants && is_array($merchants)) {
            $ReportTable = $ReportTable->whereIn('merchant_id', $merchants);
        }
        if ($label) {
            $ReportTable = $ReportTable->where('label', $label);
        }
        if ($startDate && $startDate != null) {
            $ReportTable = $ReportTable->where($table_field, '>=', $startDate);
        }
        if ($endDate && $endDate != null) {
            $ReportTable = $ReportTable->where($table_field, '<=', $endDate);
        }
        if ($lenders && is_array($lenders)) {
            $ReportTable = $ReportTable->whereIn('lender_id', $lenders);
        }
        $ReportTable = $ReportTable->whereIn('investor_id', $company_users);
        $default_date = ! empty($endDate) ? $endDate : now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        if ($industries && is_array($industries)) {
            $ReportTable = $ReportTable->whereIn('industry_id', $industries);
        }
        if ($statuses && is_array($statuses)) {
            $ReportTable = $ReportTable->whereIn('sub_status_id', $statuses);
        }
        if ($advance_type && is_array($advance_type)) {
            $ReportTable = $ReportTable->whereIn('advance_type', $advance_type);
        }
        if ($substatus_flag) {
            $ReportTable = $ReportTable->whereIn('sub_status_flag', $substatus_flag);
        }
        $cloneData = clone $ReportTable;
        $table['total'] = $cloneData->select(DB::raw('
      sum(commission_amount)      AS t_commission_amount,
      sum(under_writing_fee)      AS t_under_writing_fee,
      count(DISTINCT merchant_id) AS count,
      sum(i_amount)               AS t_total_amount,
      sum(pre_paid)               AS t_pre_paid_amount,
      sum(i_rtr)                  AS t_invest_rtr,
      sum(mgmnt_fee)              AS t_mgmnt_fee
      '))->first();
        $table['data'] = $ReportTable->groupBy('merchant_id')->select(DB::raw('
        sum(invested_amount) AS invested_amount,
        sum(ctd)             AS ctd,
        sum(overpayment)     AS overpayment
        '), 'merchant_id', 'Merchant', 'Industry', 'date_funded', 'commission', 'sub_status_id', 'funded', 'underwriting_status', 'created_at', DB::raw('
        sum(commission_amount) AS commission_amount,
        sum(pre_paid)          AS pre_paid,
        sum(under_writing_fee) AS under_writing_fee,
        sum(mgmnt_fee)         AS mgmnt_fee,
        sum(i_amount)          AS i_amount,
        sum(i_rtr)             AS i_rtr
        '));

        return $table;
    }

    public function getCommissionData($row_merchant, $investors,$owner,$date_type, $startDate, $endDate, $stime, $etime)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $company_filter = 0;
        $userId = (Auth::user()->id);
        if ($owner && $permission == 0) {
            $permission = 0;
            $userId = $owner;
        }
        $array1 = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $company_filter = $owner;
            if (! is_array($userId)) {
                $userId = explode(',', $userId);
            }
            $subadmininvestor = $investor->whereIn('creator_id', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $array1[] = $value->id;
            }
        }
       
        if ($permission == 0) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }

        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }

        if (! empty($owner)) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }

        $SpecialAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE, User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id', 'users.id')->toArray();
        if ($SpecialAccount) {
            $company_users_q = $company_users_q->whereNotIn('id', $SpecialAccount);
        }
        $company_users = $company_users_q->pluck('id')->toArray();

        $this->table = $this->table->select('pre_paid', 'amount', 'commission_amount','merchant_user.up_sell_commission as m_up_sell_commission', 'under_writing_fee', 'merchant_user.user_id',DB::raw('upper(users.name) as username'), 'invest_rtr')->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id')
        ->where('merchant_user.up_sell_commission_per','!=',0);
        if (empty($permission)) {
            if ($array1 && is_array($array1)) {
                $this->table = $this->table->whereIn('merchant_user.user_id', $array1);
            }
        }
        $table_field = ($date_type == 'true') ? 'merchants.created_at' : 'merchants.date_funded';
        if ($table_field == 'merchants.created_at') {
            $startDate = ($startDate) ? $startDate.' '.$stime : null;
            $endDate = ($endDate) ? $endDate.' '.$etime : null;
        }
        if ($startDate && $startDate != null) {
            $this->table = $this->table->where($table_field, '>=', $startDate);
        }
        if ($endDate && $endDate != null) {
            $this->table = $this->table->where($table_field, '<=', $endDate);
        }
    
        if (! empty($company_users) && is_array($company_users)) {
            $this->table = $this->table->whereIn('merchant_user.user_id', $company_users);
        }
      
        $this->table = $this->table->where('merchants.active_status', 1);
        if ($row_merchant) {
            $this->table = $this->table->where('merchants.id', $row_merchant);
        }

        $result = $this->table->get();

        return $result;
    }

    public function getInvestorData($row_merchant, $investors, $industries, $investor_type, $owner, $advance_type, $lenders, $statuses, $date_type, $startDate, $endDate, $stime, $etime, $investor_label = null,$active=null,$velocity_owned = false)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $company_filter = 0;
        $userId = (Auth::user()->id);
        if ($owner && $permission == 0) {
            $permission = 0;
            $userId = $owner;
        }
        $array1 = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $company_filter = $owner;
            if (! is_array($userId)) {
                $userId = explode(',', $userId);
            }
            $subadmininvestor = $investor->whereIn('creator_id', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $array1[] = $value->id;
            }
        }
        // if ($date_type == 'true') {
        //     $startDate = ($startDate) ? $startDate.' '.$stime: null;
        //     $endDate = ($endDate) ? $endDate.' '.$etime: null;
        // }
        if ($permission == 0) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }

        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }

        if (! empty($owner)) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }
        if ($active == 1) {
            $company_users_q = $company_users_q->where('active_status', 1);
        }
        if ($active == 2) {
            $company_users_q = $company_users_q->where('active_status', 0);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        if($velocity_owned){
            $company_users_q = $company_users_q->where('velocity_owned', 1);
        }
        if ($investor_label != null) {
            $company_users_q = $company_users_q->where(function ($q) use ($investor_label) {
                $i = 1;
                foreach ($investor_label as $inv_label) {
                    if ($i == 1) {
                        $q->whereRaw('json_contains(label, \'['.$inv_label.']\')');
                    } else {
                        $q->orWhereRaw('json_contains(label, \'['.$inv_label.']\')');
                    }
                    $i++;
                }
            });
        }
        $SpecialAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE, User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id', 'users.id')->toArray();
        if ($SpecialAccount) {
            $company_users_q = $company_users_q->whereNotIn('id', $SpecialAccount);
        }
        $company_users = $company_users_q->pluck('id')->toArray();

        $this->table = $this->table->select('pre_paid', 'amount', 'commission_amount','merchant_user.up_sell_commission as m_up_sell_commission', 'under_writing_fee', 'merchant_user.user_id', DB::raw('upper(users.name) as username'), 'invest_rtr')->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id')->whereIn('merchant_user.status', [
            1,
            3,
        ]);
        if (empty($permission)) {
            if ($array1 && is_array($array1)) {
                $this->table = $this->table->whereIn('merchant_user.user_id', $array1);
            }
        }
        $table_field = ($date_type == 'true') ? 'merchants.created_at' : 'merchants.date_funded';
        if ($table_field == 'merchants.created_at') {
            $startDate = ($startDate) ? $startDate.' '.$stime.':00' : null;
            $endDate = ($endDate) ? $endDate.' '.$etime.':59' : null;
        }
        if ($startDate && $startDate != null) {
            $this->table = $this->table->where($table_field, '>=', $startDate);
        }
        if ($endDate && $endDate != null) {
            $this->table = $this->table->where($table_field, '<=', $endDate);
        }
        if ($lenders && is_array($lenders)) {
            $this->table = $this->table->whereIn('merchants.lender_id', $lenders);
        }
        if ($industries && $industries != null) {
            $this->table = $this->table->whereIn('merchants.industry_id', $industries);
        }
        if (! empty($company_users) && is_array($company_users)) {
            $this->table = $this->table->whereIn('merchant_user.user_id', $company_users);
        }
        if ($statuses && $statuses != null) {
            $this->table = $this->table->whereIn('merchants.sub_status_id', $statuses);
        }
        if ($advance_type && $advance_type != null) {
            $this->table = $this->table->whereIn('merchants.advance_type', $advance_type);
        }
        $this->table = $this->table->where('merchants.active_status', 1);
        if ($row_merchant) {
            $this->table = $this->table->where('merchants.id', $row_merchant);
        }

        $result = $this->table->get();

        return $result;
    }

    public function getCreatorId($merchant_id)
    {
        $creator_data = $this->table->where('id', '=', $merchant_id)->pluck('creator_id');
        $creator_id = isset($creator_data[0]) ? $creator_data[0] : 0;

        return $creator_id;
    }

    public function getProfitabilityReport($investors, $merchants)
    {
        $investor_admin = $this->role->allSubAdmin()->pluck('id');
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $array1 = [];
        $investor = $this->role->allInvestors();
        $subadmininvestor = $investor->whereIn('creator_id', $investor_admin);
        foreach ($subadmininvestor as $key1 => $value) {
            $array1[] = $value->id;
        }
        $this->table = $this->table->whereHas('investmentData', function ($q) use ($investors, $merchants, $userId, $permission, $array1) {
            $q->leftJoin('users', 'users.id', 'merchant_user.user_id');
            if ($investors && is_array($investors)) {
                $q->whereIn('merchant_user.user_id', $investors);
            }
            if ($array1 && is_array($array1)) {
                $q->whereIn('merchant_user.user_id', $array1);
            }
            $q->select('pre_paid', 'amount', 'user_id', 'merchant_id', 'interest_rate', 'commission_amount');
            $q->orderByDesc('merchant_user.created_at');
        })->with(['investmentData' => function ($q) use ($investors, $merchants, $userId, $permission, $array1) {
            $q->leftJoin('users', 'users.id', 'merchant_user.user_id');
            if ($investors && is_array($investors)) {
                $q->whereIn('user_id', $investors);
            }
            if ($array1 && is_array($array1)) {
                $q->whereIn('user_id', $array1);
            }
            $q->select(DB::raw('(SELECT SUM(amount) FROM investor_transactions WHERE investor_transactions.investor_id = merchant_user.user_id AND transaction_type=2 AND investor_transactions.status=1 ) total_credit_amount'), 'merchant_user.pre_paid', 'merchant_user.amount', 'merchant_user.user_id', 'merchant_user.merchant_id', 'users.interest_rate', 'commission_amount');
        }])->select('id', 'name', 'rtr', 'funded', 'created_at', 's_prepaid_status', 'commission', 'date_funded')->whereHas('participantPayment', function ($q) use ($investors, $merchants, $userId, $permission, $array1) {
            if ($investors && is_array($investors)) {
                $q->whereIn('payment_investors.user_id', $investors);
            }
            if ($array1 && is_array($array1)) {
                $q->whereIn('payment_investors.user_id', $array1);
            }
        })->with(['participantPayment' => function ($q) use ($investors, $merchants, $userId, $permission, $array1) {
            if ($investors && is_array($investors)) {
                $q->whereIn('user_id', $investors);
            }
            if ($array1 && is_array($array1)) {
                $q->whereIn('user_id', $array1);
            }
        }]);
        if ($merchants && is_array($merchants)) {
            $this->table = $this->table->whereIn('merchants.id', $merchants);
        }
        $this->table = $this->table->where('active_status', 1);

        return $this->table;
    }

    public function getProfitabilityReport4($merchants, $from_date, $to_date, $funded_date)
    {
        $pref_return_date = null;
        if (! $to_date) {
            $pref_return_date = date('Y-m-d');
        }
        if (! $from_date) {
            $from_date = '0000-00-00';
        }
        if ($from_date != 0 && $to_date != 0) {
            $payment_query = "AND participent_payments.payment_date>='$from_date' AND participent_payments.payment_date <= '$to_date'";
            $tran_query = "AND investor_transactions.date >= '$from_date' AND investor_transactions.date <= '$to_date'";
        } elseif ($from_date != 0) {
            $payment_query = "AND participent_payments.payment_date >= '$from_date'";
            $tran_query = "AND investor_transactions.date >= '$from_date'";
        } elseif ($to_date != 0) {
            $payment_query = "AND participent_payments.payment_date <= '$to_date'";
            $tran_query = "AND investor_transactions.date <= '$to_date'";
        } else {
            $payment_query = '';
            $tran_query = '';
        }
        $date_filter_table = 'merchants.last_status_updated_date';
        if ($funded_date == 'true') {
            $date_filter_table = 'merchants.date_funded';
        }
        $default_merchants = DB::table('merchants')->whereIn('merchants.sub_status_id', [4, 22]);
        if ($from_date != 0) {
            $default_merchants->whereDate($date_filter_table, '>=', $from_date);
        }
        if ($to_date != 0) {
            $default_merchants->whereDate($date_filter_table, '<=', $to_date);
        }
        $default_merchants = $default_merchants->select('id')->get()->toArray();
        $default_merchants = array_unique(array_column($default_merchants, 'id'));
        $default_merchants = implode(',', $default_merchants);
        if ($default_merchants == '') {
            $default_merchants = '0';
        }
        $profits1 = DB::table('users')
        ->where('investor_type', 2)->groupBy('users.id')->orderby('users.id')
        ->select(DB::raw("upper(users.name) as investor_name"), 'users.id',
        DB::raw('ctd_investor.ctd,   ctd_investor.total_profit, bills_trans.bills, bills_trans.profit_d_v, bills_trans.profit_d_i, bills_trans.profit_d_p, ctd_default_merchant.ctd_default,
		user_overpayment.overpayment,
		user_carry_profit.carry_profit,
		ctd_default_merchant.default_amnt,
		interest_investor.interest,
		interest_of_return_of_principal.interest as return_of_principal_interest'));
        if ($merchants) {
            $merchants = implode(',', $merchants);
        }
        $profits1 = $this->queryJoinProfitabilityReport($profits1, $from_date, $to_date, $payment_query, $tran_query, $default_merchants, false, $pref_return_date, $merchants);
        $userId = Auth::user()->id;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $profits1 = $profits1->where('company', $userId);
            }
        }
        if ($merchants) {
            $profits1 = $profits1->whereIn('merchants.id', explode(',', $merchants));
        }
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);//Investors under enabled company
        })->pluck('users.id')->toArray();
        $profits1 = $profits1->whereIn('users.id',$investors);
        $result['data'] = $profits1;

        return $result;
    }

    public function getProfitabilityReport4_original($merchants, $from_date, $to_date, $funded_date)
    {
        $default_date = ! empty($to_date) ? $to_date : now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        $to_date2 = $to_date;
        if (! $to_date) {
            $to_date = 0;
        }
        if (! $from_date) {
            $from_date = '0000-00-00';
        }
        $default_merchants = DB::table('merchants')->whereIn('merchants.sub_status_id', [4, 22]);
        if ($funded_date == 'true') {
            $date_filter_table = 'merchants.date_funded';
        }
        $date_filter_table = 'merchants.last_status_updated_date';
        if ($from_date != '') {
            $default_merchants->where($date_filter_table, '>=', $from_date);
        }
        if ($to_date != '') {
            $default_merchants->where($date_filter_table, '<=', $to_date);
        }
        $default_merchants = $default_merchants->select('id')->get()->toArray();
        $default_merchants = array_unique(array_column($default_merchants, 'id'));
        $default_merchants = implode(',', $default_merchants);
        if ($from_date != 0 && $to_date != 0) {
            $payment_query = "AND participent_payments.payment_date>='$from_date' AND participent_payments.payment_date<='$to_date'";
            $tran_query = "AND investor_transactions.date>='$from_date' AND investor_transactions.date<='$to_date'";
        } elseif ($from_date != 0) {
            $payment_query = "AND participent_payments.payment_date>='$from_date'";
            $tran_query = "AND investor_transactions.date>='$from_date'";
        } elseif ($to_date != 0) {
            $payment_query = "AND participent_payments.payment_date<='$to_date'";
            $tran_query = "AND investor_transactions.date<='$to_date'";
        } else {
            $payment_query = '';
            $tran_query = '';
        }
        if ($default_merchants == '') {
            $default_merchants = '0';
        }
        $profits1 = DB::table('users')->join('merchant_user', function ($join = '') {
            $join->on('merchant_user.user_id', 'users.id')->whereIn('merchant_user.status', [1, 3]);
        })->join('merchants', function ($join) {
            $join->on('merchants.id', '=', 'merchant_user.merchant_id');
        })->where('users.id', 19)->where('investor_type', 2)->groupBy('users.id')->orderby('users.id');
        $profits1 = $profits1->select('users.name as investor_name', 'users.id', DB::raw("(SELECT SUM(payment_investors.participant_share - payment_investors.mgmnt_fee) FROM payment_investors  LEFT JOIN participent_payments on
             payment_investors.participent_payment_id=participent_payments.id WHERE payment_investors.user_id=users.id $payment_query) as ctd"), DB::raw("(SELECT SUM(payment_investors.profit) FROM payment_investors  LEFT JOIN participent_payments on
             payment_investors.participent_payment_id=participent_payments.id WHERE payment_investors.user_id=users.id $payment_query) as total_profit"), DB::raw("(SELECT sum(investor_transactions.amount *users.interest_rate/100/365*  (TIMESTAMPDIFF(DAY, IF('$from_date' >= investor_transactions.date,'$from_date',investor_transactions.date),IF($to_date,'$to_date',CURDATE()) ) + 1 ) )  FROM investor_transactions WHERE investor_transactions.date <= IF($to_date,'$to_date',CURDATE()) AND
                     investor_transactions.investor_id = users.id AND investor_transactions.transaction_type=2) as interest"), DB::raw("(SELECT ABS(SUM(investor_transactions.amount)) FROM investor_transactions WHERE investor_transactions.investor_id = users.id AND transaction_category=10 $tran_query) as bills"), DB::raw("(SELECT ABS(SUM(investor_transactions.amount)) FROM investor_transactions WHERE investor_transactions.investor_id = users.id AND transaction_category=15 $tran_query) as profit_d_v"), DB::raw("(SELECT ABS(SUM(investor_transactions.amount)) FROM investor_transactions WHERE investor_transactions.investor_id = users.id AND transaction_category=16 $tran_query) as profit_d_i"), DB::raw("(SELECT ABS(SUM(investor_transactions.amount)) FROM investor_transactions WHERE investor_transactions.investor_id = users.id AND transaction_category=17 $tran_query) as profit_d_p"), DB::raw('(SELECT

                sum(

'.$merchant_day.'

*
  (

    (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                                    -

                                 IF(
                                    (merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee),

                               (merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee),0)) ) as default_amount

                            FROM merchant_user join merchants on merchants.id=merchant_user.merchant_id WHERE merchant_user.user_id = users.id AND merchant_user.merchant_id in ('.$default_merchants.') ) as default_amount'), DB::raw('(SELECT SUM(merchant_user.amount + merchant_user.pre_paid + merchant_user.commission_amount  + merchant_user.under_writing_fee) as invested_amount
               FROM merchant_user WHERE merchant_user.user_id = users.id AND merchant_user.merchant_id in ('.$default_merchants.')) as invested_amount'), DB::raw('(SELECT SUM(merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee)
               FROM merchant_user WHERE merchant_user.user_id = users.id AND merchant_id in ('.$default_merchants.') ) as ctd_default'), DB::raw('(SELECT
                SUM(merchant_user.amount + merchant_user.pre_paid + merchant_user.commission_amount  + merchant_user.under_writing_fee - merchant_user.paid_participant_ishare + merchant_user.paid_mgmnt_fee)
           FROM merchant_user WHERE merchant_user.user_id = users.id AND merchant_id in ('.$default_merchants.') ) as default_amnt'), 'merchant_user.merchant_id');
        $userId = Auth::user()->id;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $profits1 = $profits1->where('company', $userId);
            }
        }
        if ($merchants) {
            $profits1 = $profits1->whereIn('merchants.id', $merchants);
        }
        $overpayments = PaymentInvestors::join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id')->join('users', 'users.id', 'payment_investors.user_id')->where('investor_type', 2)->join('merchants', 'merchants.id', 'participent_payments.merchant_id');
        if ($from_date) {
            $overpayments = $overpayments->where('payment_date', '>=', $from_date);
        }
        if ($to_date) {
            $overpayments = $overpayments->where('payment_date', '<=', $to_date);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $overpayments = $overpayments->where('company', $userId);
            }
        }
        $overpayments = $overpayments->groupBy('payment_investors.user_id')->select(DB::raw('sum(overpayment) as overpayment'), 'users.id')->pluck('overpayment', 'users.id')->toArray();
        $result['data'] = $profits1->get();
        $result['overpayment'] = $overpayments;

        return $result;
    }

    public function getProfitabilityReport2($merchants, $from_date, $to_date, $funded_date, $investor_check)
    {
        $pref_return_date = '';
        if (! $to_date) {
            $to_date = date('Y-m-d', strtotime('+5 days'));
            $pref_return_date = date('Y-m-d');
        }
        if (! $from_date) {
            $from_date = '0000-00-00';
        }
        $date_filter_table = 'merchants.last_status_updated_date';
        if ($funded_date == 'true') {
            $date_filter_table = 'merchants.date_funded';
        }
        $default_merchants = DB::table('merchants')->whereIn('merchants.sub_status_id', [4, 22]);
        if ($from_date != 0) {
            $default_merchants->whereDate($date_filter_table, '>=', $from_date);
        }
        if ($to_date != 0) {
            $default_merchants->whereDate($date_filter_table, '<=', $to_date);
        }
        $default_merchants = $default_merchants->select('id')->get()->toArray();
        $default_merchants = array_unique(array_column($default_merchants, 'id'));
        $default_merchants = implode(',', $default_merchants);
        if ($from_date != 0 && $to_date != 0) {
            $payment_query = "AND participent_payments.payment_date>='$from_date' AND participent_payments.payment_date<='$to_date'";
            $tran_query = "AND investor_transactions.date>='$from_date' AND investor_transactions.date<='$to_date'";
        } elseif ($from_date != 0) {
            $payment_query = "AND participent_payments.payment_date>='$from_date'";
            $tran_query = "AND investor_transactions.date>='$from_date'";
        } elseif ($to_date != 0) {
            $payment_query = "AND participent_payments.payment_date<='$to_date'";
            $tran_query = "AND investor_transactions.date<='$to_date'";
        } else {
            $payment_query = '';
            $tran_query = '';
        }
        if ($default_merchants == '') {
            $default_merchants = '0';
        }
        $profits1 = DB::table('users');
        if ($merchants) {
            $merchants = implode(',', $merchants);
        }
        $profits1 = $this->queryJoinProfitabilityReport($profits1, $from_date, $to_date, $payment_query, $tran_query, $default_merchants, false, $pref_return_date, $merchants);
        if ($investor_check == 'true') {
            $profits1->whereIn('investor_type', [1, 2, 3]);
        } else {
            $profits1->where('investor_type', 1);
        }
        $profits1->groupBy('users.id')->orderby('users.id');
        $profits1 = $profits1->select(DB::raw('upper(users.name) as investor_name'), 'users.id', DB::raw('ctd_investor.ctd, ctd_investor.total_profit, bills_trans.bills, bills_trans.profit_d_v, bills_trans.profit_d_i, bills_trans.profit_d_p, ctd_default_merchant.ctd_default, ctd_default_merchant.default_amnt,
                interest_investor.interest,
                interest_of_return_of_principal.interest as return_of_principal_interest,
                user_carry_profit.carry_profit,
                user_overpayment.overpayment'));
        $userId = Auth::user()->id;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $profits1 = $profits1->where('company', $userId);
            }
        }
        $merchantQuery = 'SELECT  user_id FROM merchant_user  WHERE `merchant_user`.`status` IN (1, 3)';
        if ($merchants) {
            $merchantQuery .= ' AND merchant_id in ('.$merchants.')';
        }
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.id')->toArray();
        $profits1 = $profits1->whereIn('users.id',$investors);
        $profits1 = $profits1->get();
        $result['data'] = $profits1;

        return $result;
    }

    public function get_carry_forwards($from_date, $to_date, $type = 1)
    {
        if (! $to_date) {
            $to_date = date('Y-m-d', strtotime('+5 days'));
        }
        if (! $from_date) {
            $from_date = '0000-00-00';
        }
        $carry_forwards = DB::table('carry_forwards')->select('investor_id', DB::raw('sum(amount) as carry_amount'))->groupBy('investor_id')->whereBetween('date', [$from_date, $to_date])->get();
        $carry_forwards_data = [];
        foreach ($carry_forwards as $carry_forward) {
            $carry_forwards_data[$carry_forward->investor_id] = round($carry_forward->carry_amount, 2);
        }

        return $carry_forwards_data;
    }

    public function get_sum_carry_amount($result)
    {
        $carry_forwards = session('carry_forwards');
        $total = 0;
        foreach ($result as $data) {
            if (isset($data->id)) {
                $total += isset($carry_forwards[$data->id]) ? $carry_forwards[$data->id] : 0;
            }
        }

        return $total;
    }

    public function getProfitabilityReport3($merchants, $from_date, $to_date, $funded_date)
    {
        $to_date2 = $to_date;
        if (! $to_date2) {
            $to_date2 = 0;
        }
        if (! $to_date) {
            $to_date = '2020-12-31';
        }
        if (! $from_date) {
            $from_date = '0000-00-00';
        }
        if ((strtotime($to_date) > strtotime('2020/12/31'))) {
            $to_date = '2020-12-31';
        }
        if ($from_date != 0 && $to_date != 0) {
            $payment_query = "AND participent_payments.payment_date>='$from_date' AND participent_payments.payment_date <= '$to_date'";
            $tran_query = "AND investor_transactions.date >= '$from_date' AND investor_transactions.date <= '$to_date'";
        } elseif ($from_date != 0) {
            $payment_query = "AND participent_payments.payment_date >= '$from_date'";
            $tran_query = "AND investor_transactions.date >= '$from_date'";
        } elseif ($to_date != 0) {
            $payment_query = "AND participent_payments.payment_date <= '$to_date'";
            $tran_query = "AND investor_transactions.date <= '$to_date'";
        } else {
            $payment_query = '';
            $tran_query = '';
        }
        $date_filter_table = 'merchants.last_status_updated_date';
        if ($funded_date == 'true') {
            $date_filter_table = 'merchants.date_funded';
        }
        $default_merchants = DB::table('merchants')->whereIn('merchants.sub_status_id', [4, 22]);
        if ($from_date != 0) {
            $default_merchants->whereDate($date_filter_table, '>=', $from_date);
        }
        if ($to_date != 0) {
            $default_merchants->whereDate($date_filter_table, '<=', $to_date);
        }
        $default_merchants = $default_merchants->select('id')->get()->toArray();
        $default_merchants = array_unique(array_column($default_merchants, 'id'));
        $default_merchants = implode(',', $default_merchants);
        if ($default_merchants == '') {
            $default_merchants = '0';
        }
        $profits1 = DB::table('users')
        ->where('investor_type', 3)->groupBy('users.id')->orderby('users.id')->select(DB::raw('upper(users.name) as investor_name'), 'users.id', DB::raw('ctd_investor.ctd, ctd_investor.total_profit, bills_trans.bills, bills_trans.profit_d_v, bills_trans.profit_d_i, bills_trans.profit_d_p, ctd_default_merchant.ctd_default,
		user_overpayment.overpayment,
		user_carry_profit.carry_profit,
		ctd_default_merchant.default_amnt,
		interest_investor.interest,
		interest_of_return_of_principal.interest as return_of_principal_interest'));
        if ($merchants) {
            $merchants = implode(',', $merchants);
        }
        $profits1 = $this->queryJoinProfitabilityReport($profits1, $from_date, $to_date, $payment_query, $tran_query, $default_merchants, false, null, $merchants);
        $userId = Auth::user()->id;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if (empty($permission)) {
            $profits1 = $profits1->where('company', $userId);
        }
        if ($merchants) {
            $profits1 = $profits1->whereIn('merchants.id', explode(',', $merchants));
        }
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.id')->toArray();
        $profits1 = $profits1->whereIn('users.id',$investors);
        $profits1 = $profits1->get();
        $result['data'] = $profits1;

        return $result;
    }

    public function getProfitabilityReport21($merchants, $from_date, $to_date, $funded_date)
    {
        $to_date2 = $to_date;
        if (! $to_date2) {
            $to_date2 = 0;
        }
        if (! $to_date) {
            $to_date = date('Y-m-d', strtotime('+5 days'));
        }
        if (! $from_date) {
            $from_date = '2021-01-01';
        }
        if ((strtotime($from_date) < strtotime('2021/01/01'))) {
            $from_date = '2021-01-01';
        }
        if ($from_date != 0 && $to_date != 0) {
            $payment_query = "AND participent_payments.payment_date>='$from_date' AND participent_payments.payment_date <= '$to_date'";
            $tran_query = "AND investor_transactions.date >= '$from_date' AND investor_transactions.date <= '$to_date'";
        } elseif ($from_date != 0) {
            $payment_query = "AND participent_payments.payment_date >= '$from_date'";
            $tran_query = "AND investor_transactions.date >= '$from_date'";
        } elseif ($to_date != 0) {
            $payment_query = "AND participent_payments.payment_date <= '$to_date'";
            $tran_query = "AND investor_transactions.date <= '$to_date'";
        } else {
            $payment_query = '';
            $tran_query = '';
        }
        $date_filter_table = 'merchants.last_status_updated_date';
        if ($funded_date == 'true') {
            $date_filter_table = 'merchants.date_funded';
        }
        $default_merchants = DB::table('merchants')->whereIn('merchants.sub_status_id', [4, 22]);
        if ($from_date != 0) {
            $default_merchants->whereDate($date_filter_table, '>=', $from_date);
        }
        if ($to_date != 0) {
            $default_merchants->whereDate($date_filter_table, '<=', $to_date);
        }
        $default_merchants = $default_merchants->select('id')->get()->toArray();
        $default_merchants = array_unique(array_column($default_merchants, 'id'));
        $default_merchants = implode(',', $default_merchants);
        if ($default_merchants == '') {
            $default_merchants = '0';
        }
        $profits1 = DB::table('users')

        ->where('investor_type', 3)
        ->groupBy('users.id')
        ->orderby('users.id')
        ->select(DB::raw('upper(users.name) as investor_name'), 'users.id', DB::raw('ctd_investor.ctd,
		ctd_investor.total_profit,
		bills_trans.bills,
		bills_trans.profit_d_v,
		bills_trans.profit_d_i,
		bills_trans.profit_d_p,
		ctd_default_merchant.ctd_default,
		user_overpayment.overpayment,
        user_carry_profit.carry_profit,
		ctd_default_merchant.default_amnt,
		interest_investor.interest,
		interest_of_return_of_principal.interest as return_of_principal_interest
		'));
        if ($merchants) {
            $merchants = implode(',', $merchants);
        }
        $profits1 = $this->queryJoinProfitabilityReport($profits1, $from_date, $to_date, $payment_query, $tran_query, $default_merchants, false, null, $merchants);
        $userId = Auth::user()->id;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if (empty($permission)) {
            $profits1 = $profits1->where('company', $userId);
        }
        if ($merchants) {
            $profits1 = $profits1->whereIn('merchants.id', explode(',', $merchants));
        }
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.id')->toArray();
        $profits1 = $profits1->whereIn('users.id',$investors);
        $profits1 = $profits1->get();
        $result['data'] = $profits1;

        return $result;
    }
    public function calculatePrefRetun($user_id_arr,$from_date=null,$to_date=null){
          $result = User::whereIn('id',$user_id_arr)
          ->with(['investorRoiRate' => function ($query) use($from_date,$to_date){
            $query->where(function($q) use($from_date,$to_date){
                if($from_date!=null){
                return $q->where('from_date','<=',$from_date)->where('to_date', '>=',$from_date);
                }
            })->orWhere(function($q) use($from_date,$to_date){
                if($to_date!=null){
                return $q->where('from_date','<=',$to_date)->where('to_date', '>=',$to_date);
                }
            })
            ->orWhere(function($q) use($from_date,$to_date){
                if($from_date!=null){
                return $q->where('from_date','>=',$from_date);
                }
            })->orWhere(function($q) use($from_date,$to_date){
                if($to_date!=null){
                return $q->where('to_date','<=',$to_date)->orWhere('to_date',null);
                }else{
                return $q->where('to_date',null);

                }
            });
        }])->select('id')->get()->toArray();//print_r($result);exit;
        $result_arr = [];
        foreach($result as $res){
             $inv_pref_retun = 0;
            foreach($res['investor_roi_rate'] as $rates){
               
               if($from_date==null){
               $s_date = $rates['from_date'];
               }else{
               $s_date = ($from_date > $rates['from_date']) ? $from_date : $rates['from_date'];
               }
               if($to_date==null){
                
                if($rates['to_date']==null){
                    $e_date = date('Y-m-d');
                }else{
                    $e_date = $rates['to_date'];
                }
               }else{
                    if($rates['to_date']==null){
                        $e_date = $to_date;
                    }else{
                    $e_date = ($to_date < $rates['to_date']) ? $to_date : $rates['to_date'];
                    }
               };
               if($s_date<=$e_date){
               $pref_return = $this->getPrefReturn($res['id'],$s_date,$e_date,$rates['roi_rate']);
               $inv_pref_retun = $inv_pref_retun+$pref_return;
               }
               
            }
            $result_arr[$res['id']] =$inv_pref_retun; 
        }
        return $result_arr;
         
    }
    public function getPrefReturn($investor_id,$from_date,$to_date,$roi_rate){
            $amount = 0;
            $query = User::where('id',$investor_id);
            $query->leftJoin(DB::raw("(SELECT sum(investor_transactions.amount *'$roi_rate'/100/365* (TIMESTAMPDIFF(DAY, IF(
            ('$from_date' >= investor_transactions.date) ,'$from_date',investor_transactions.date),IF((investor_transactions.date <='$to_date'),'$to_date',CURDATE()  ) )  + 1 ) ) as interest, investor_transactions.investor_id  FROM investor_transactions
            LEFT JOIN users ON users.id = investor_transactions.investor_id
            WHERE
            investor_transactions.date <= IF('$to_date','$to_date',CURDATE()) AND
            investor_transactions.investor_id = users.id AND investor_transactions.transaction_type=2 AND investor_transactions.status=1 group by investor_transactions.investor_id ) as interest_investor"), 'interest_investor.investor_id', '=', 'users.id');
            $query->leftJoin(DB::raw("(SELECT sum(investor_transactions.amount *'$roi_rate'/100/365* (TIMESTAMPDIFF(DAY, IF(
              ('$from_date' >= investor_transactions.date) ,'$from_date',investor_transactions.date),IF((investor_transactions.date <='$to_date'),'$to_date',CURDATE()  ) )  + 1 ) ) as interest, investor_transactions.investor_id  FROM investor_transactions
              LEFT JOIN users ON users.id = investor_transactions.investor_id
              WHERE
              investor_transactions.date <= IF('$to_date','$to_date',CURDATE()) AND
              investor_transactions.investor_id = users.id AND investor_transactions.transaction_category=12 AND investor_transactions.status=1 group by investor_transactions.investor_id ) as interest_of_return_of_principal"), 'interest_of_return_of_principal.investor_id', '=', 'users.id');
            $query = $query->select('users.id', DB::raw(
              'interest_investor.interest,
              interest_of_return_of_principal.interest as return_of_principal_interest
              '));
            $query = $query->first(); 
            if($query) {
                $amount = $query->interest+$query->return_of_principal_interest;
            }  
        return $amount;

    }

    private function queryJoinProfitabilityReport($query, $from_date, $to_date, $payment_query, $tran_query, $default_merchants, $isReport4 = false, $pref_return_date = null, $merchants = null)
    {
        $merchantsQuerryForcarry_forwards = '';
        $merchantsQuerryForpayment_investors = '';
        $merchantsQuerryFormerchant_user = '';
        if ($merchants) {
            $merchantsQuerryForcarry_forwards = " AND carry_forwards.merchant_id IN ($merchants) ";
            $merchantsQuerryForpayment_investors = " AND payment_investors.merchant_id IN ($merchants) ";
            $merchantsQuerryFormerchant_user = " AND merchant_id IN ($merchants) ";
        }
        if ($from_date != 0 && $to_date != 0) {
            $carry_forwards_query = "AND carry_forwards.date>='$from_date' AND carry_forwards.date <= '$to_date'";
        } elseif ($from_date != 0) {
            $carry_forwards_query = "AND carry_forwards.date >= '$from_date'";
        } elseif ($to_date != 0) {
            $carry_forwards_query = "AND carry_forwards.date <= '$to_date'";
        } else {
            $carry_forwards_query = '';
        }
        $query->leftJoin(DB::raw("(
			SELECT
			SUM(payment_investors.actual_participant_share - payment_investors.mgmnt_fee) as ctd,
			SUM(payment_investors.profit) AS total_profit,
			payment_investors.user_id
			FROM payment_investors
			LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id
			JOIN merchants on payment_investors.merchant_id=merchants.id
			WHERE payment_investors.user_id > 0
			$merchantsQuerryForpayment_investors
			$payment_query
			group by payment_investors.user_id) as ctd_investor"), 'ctd_investor.user_id', '=', 'users.id')
            ->leftJoin(DB::raw("(SELECT investor_transactions.investor_id,
				abs(sum(CASE WHEN investor_transactions.transaction_category = 10 OR investor_transactions.transaction_category = 44 OR investor_transactions.transaction_category = 46 OR investor_transactions.transaction_category = 48 THEN investor_transactions.amount END)) 'bills',
				(sum(CASE WHEN investor_transactions.transaction_category = 15 THEN investor_transactions.amount END)) 'profit_d_v',
				(sum(CASE WHEN investor_transactions.transaction_category = 16 THEN investor_transactions.amount END)) 'profit_d_i',
				(sum(CASE WHEN investor_transactions.transaction_category = 17 THEN investor_transactions.amount END)) 'profit_d_p'
				FROM investor_transactions WHERE investor_transactions.investor_id > 0 AND investor_transactions.status = 1 $tran_query group by investor_transactions.investor_id) as bills_trans"), 'bills_trans.investor_id', '=', 'users.id')

            //->leftJoin(DB::raw("(SELECT SUM(payment_investors.profit) as total_profit, payment_investors.user_id FROM payment_investors  LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id WHERE payment_investors.user_id > 0 $payment_query group by payment_investors.user_id) as total_profit_investor"), 'total_profit_investor.user_id', '=', 'users.id')
            //->leftJoin(DB::raw("(SELECT ABS(SUM(investor_transactions.amount)) as bills, investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 AND transaction_category=10 $tran_query group by investor_transactions.investor_id) as bills_trans"), 'bills_trans.investor_id', '=', 'users.id')
            //->leftJoin(DB::raw("(SELECT ABS(SUM(investor_transactions.amount)) as profit_d_v, investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 AND transaction_category=15 $tran_query group by investor_transactions.investor_id ) as profit_d_v_trans"), 'profit_d_v_trans.investor_id', '=', 'users.id')
            ->leftJoin(DB::raw("(SELECT SUM(carry_forwards.amount) as overpayment , carry_forwards.investor_id as user_id FROM carry_forwards  LEFT JOIN users on carry_forwards.investor_id=users.id WHERE carry_forwards.investor_id > 0 AND type=1 $merchantsQuerryForcarry_forwards $carry_forwards_query group by carry_forwards.investor_id) as user_overpayment "), 'user_overpayment.user_id', '=', 'users.id')
            ->leftJoin(DB::raw("(SELECT SUM(carry_forwards.amount) as carry_profit, carry_forwards.investor_id as user_id FROM carry_forwards  LEFT JOIN users on carry_forwards.investor_id=users.id WHERE carry_forwards.investor_id > 0 AND type=2 $merchantsQuerryForcarry_forwards $carry_forwards_query group by carry_forwards.investor_id) as user_carry_profit"), 'user_carry_profit.user_id', '=', 'users.id')
            //->leftJoin(DB::raw("(SELECT ABS(SUM(investor_transactions.amount)) as profit_d_i, investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 AND transaction_category=16 $tran_query group by investor_transactions.investor_id ) as profit_d_i_trans"), 'profit_d_i_trans.investor_id', '=', 'users.id')
            //->leftJoin(DB::raw("(SELECT ABS(SUM(investor_transactions.amount)) as profit_d_p, investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 AND transaction_category=17 $tran_query group by investor_transactions.investor_id ) as profit_d_p_trans"), 'profit_d_p_trans.investor_id', '=', 'users.id')
            ->leftJoin(DB::raw('(SELECT SUM(merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee) as ctd_default, SUM(merchant_user.amount + merchant_user.pre_paid + merchant_user.commission_amount + merchant_user.under_writing_fee +merchant_user.up_sell_commission) AS default_amnt, merchant_user.user_id FROM merchant_user WHERE merchant_user.user_id > 0 AND merchant_id in ('.$default_merchants.') '.$merchantsQuerryFormerchant_user.' group by merchant_user.user_id ) as ctd_default_merchant'), 'ctd_default_merchant.user_id', '=', 'users.id');
        if (! $isReport4) {
            //$query->leftJoin(DB::raw('(SELECT SUM(merchant_user.amount + merchant_user.pre_paid + merchant_user.commission_amount  + merchant_user.under_writing_fee) as default_amnt, merchant_user.user_id FROM merchant_user WHERE merchant_user.user_id > 0 AND merchant_id in (' . $default_merchants . ') group by merchant_user.user_id ) as default_amnt_merchant'), 'default_amnt_merchant.user_id', '=', 'users.id');
        } else {
            $default_date = ! empty($to_date) ? $to_date : now();
            $merchant_day = PayCalc::setDaysCalculation($default_date);
            $query->leftJoin(DB::raw('(SELECT sum('.$merchant_day.' * ( (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) - IF( (merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee), (merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee),0)) ) as default_amount, merchant_user.user_id FROM merchant_user WHERE merchant_user.user_id > 0 AND merchant_id in ('.$default_merchants.') group by merchant_user.user_id ) as default_amnt_merchant'), 'default_amnt_merchant.user_id', '=', 'users.id');
        }
        if ($pref_return_date != null) {
            $to_date = $pref_return_date;
        }
        $query->leftJoin(DB::raw("(SELECT sum(investor_transactions.amount *users.interest_rate/100/365* (TIMESTAMPDIFF(DAY, IF(
        ('$from_date' >= investor_transactions.date) ,'$from_date',investor_transactions.date),IF((investor_transactions.date <='$to_date'),'$to_date',CURDATE()  ) )  + 1 ) ) as interest, investor_transactions.investor_id  FROM investor_transactions
        LEFT JOIN users ON users.id = investor_transactions.investor_id
        WHERE
        investor_transactions.date <= IF('$to_date','$to_date',CURDATE()) AND
        investor_transactions.investor_id = users.id AND investor_transactions.transaction_type=2 AND investor_transactions.status=1 group by investor_transactions.investor_id ) as interest_investor"), 'interest_investor.investor_id', '=', 'users.id');
        $query->leftJoin(DB::raw("(SELECT sum(investor_transactions.amount *users.interest_rate/100/365* (TIMESTAMPDIFF(DAY, IF(
          ('$from_date' >= investor_transactions.date) ,'$from_date',investor_transactions.date),IF((investor_transactions.date <='$to_date'),'$to_date',CURDATE()  ) )  + 1 ) ) as interest, investor_transactions.investor_id  FROM investor_transactions
          LEFT JOIN users ON users.id = investor_transactions.investor_id
          WHERE
          investor_transactions.date <= IF('$to_date','$to_date',CURDATE()) AND
          investor_transactions.investor_id = users.id AND investor_transactions.transaction_category=12 AND investor_transactions.status=1 group by investor_transactions.investor_id ) as interest_of_return_of_principal"), 'interest_of_return_of_principal.investor_id', '=', 'users.id');
        if ($isReport4) {
            $query->where('investor_type', 2);
        }
        $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $query->whereNotIn('company',$disabled_companies);
        return $query;
    }

    public function getInterestAccuredDetails($investors, $merchants)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $array1 = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->where('creator_id', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $array1[] = $value->id;
            }
        }
        $this->table = $this->table->whereHas('investmentData', function ($q) use ($investors, $merchants, $userId, $permission, $array1) {
            $q->leftJoin('users', 'users.id', 'merchant_user.user_id');
            if ($investors && is_array($investors)) {
                $q->whereIn('merchant_user.user_id', $investors);
            }
            if (empty($permission)) {
                if ($array1 && is_array($array1)) {
                    $q->whereIn('merchant_user.user_id', $array1);
                }
            }
            $q->select('pre_paid', 'amount', 'user_id', 'merchant_id', 'interest_rate', 'commission_amount');
            $q->orderByDesc('merchant_user.created_at');
        })->with(['investmentData' => function ($q) use ($investors, $merchants, $userId, $permission, $array1) {
            $q->leftJoin('users', 'users.id', 'merchant_user.user_id');
            if ($investors && is_array($investors)) {
                $q->whereIn('user_id', $investors);
            }
            if (empty($permission)) {
                if ($array1 && is_array($array1)) {
                    $q->whereIn('user_id', $array1);
                }
            }
            $q->select(DB::raw('(SELECT SUM(amount) FROM investor_transactions WHERE investor_transactions.investor_id = merchant_user.user_id AND transaction_type=2 AND investor_transactions.status=1 ) total_credit_amount'), 'merchant_user.pre_paid', 'merchant_user.amount', 'merchant_user.user_id', 'merchant_user.merchant_id', 'users.interest_rate', 'commission_amount');
        }])->select('id', 'name', 'rtr', 'funded', 'created_at', 's_prepaid_status', 'commission', 'date_funded')->whereHas('participantPayment', function ($q) use ($investors, $merchants, $userId, $permission, $array1) {
            if ($investors && is_array($investors)) {
                $q->whereIn('payment_investors.user_id', $investors);
            }
            if (empty($permission)) {
                if ($array1 && is_array($array1)) {
                    $q->whereIn('payment_investors.user_id', $array1);
                }
            }
        })->with(['participantPayment' => function ($q) use ($investors, $merchants, $userId, $permission, $array1) {
            if ($investors && is_array($investors)) {
                $q->whereIn('user_id', $investors);
            }
            if (empty($permission)) {
                if ($array1 && is_array($array1)) {
                    $q->whereIn('user_id', $array1);
                }
            }
        }]);
        if ($merchants && is_array($merchants)) {
            $this->table = $this->table->whereIn('merchants.id', $merchants);
        }
        $this->table = $this->table->where('active_status', 1);

        return $this->table;
    }

    public function profitPrincipalUpdate($merchant_id, $dates_array, $payment_amount, $net_payment_status, $debit_status, $debit_reason, $investor_id, $rcode = '')
    {
        $settings = Settings::select('email', 'forceopay')->first();
        $forceoverpay = $settings->forceopay;
        $description = '';
        if ($debit_status === 'yes') {
            $description = 'Debit Payment';
            $payment_type = 0;
            $this_payment_amount = $payment_amount;
        } else {
            $description = 'Payment';
            $payment_type = 1;
            $this_payment_amount = $payment_amount;
        }
        $count = [];
        $investor_ids = [];
        $merchant_curr = Merchant::select('funded', 'rtr', 'factor_rate', 'date_funded', 'commission', 'pmnts', 'payment_amount', 'sub_statuses.name as substatus_name', 'complete_percentage', 'merchants.id', 'merchants.name as name', 'advance_type', 'merchants.sub_status_id')->where('merchants.id', $merchant_id)->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->first();
        $funded_amount = $merchant_curr->funded;
        $rtr = $merchant_curr->rtr;
        $no_paymts = $merchant_curr->pmnts;
        $date_funded = $merchant_curr->date_funded;
        $total_payment = 0;
        $merchant_user_arr = MerchantUser::where('merchant_id', $merchant_id);
        if (! empty($investor_id)) {
            $merchant_user_arr = $merchant_user_arr->whereIn('merchant_user.user_id', $investor_id);
        }
        $merchant_user_arr = $merchant_user_arr->select(DB::raw('sum(paid_participant_ishare) as total_paid_amount, sum(amount * (100- (mgmnt_fee ))/100 ) as total_funded_on_merchant

                '))->first();
        $merchant_user_arr2 = MerchantUser::where('merchant_id', $merchant_id)->select(DB::raw('sum(paid_participant_ishare) as total_paid_amount, sum(amount * (100- (mgmnt_fee))/100 ) as total_funded_on_merchant

                '), DB::raw("sum('merchant_user.invest_rtr') as invest_rtr"))->whereIn('merchant_user.status', [1, 3])->first();
        $total_paid_amount = $merchant_user_arr->total_paid_amount;
        $total_funded_on_merchant = $merchant_user_arr->total_funded_on_merchant;
        $total_paid_amount2 = $merchant_user_arr2->total_paid_amount;
        $total_funded_on_merchant2 = $merchant_user_arr2->total_funded_on_merchant;
        $total_paid_amount = $total_paid_amount + $this_payment_amount;
        $userIds = [];
        if ($rtr >= $total_paid_amount || $forceoverpay) {
            $investors = MerchantUser::join('users', function ($join) {
                $join->on('users.id', '=', 'merchant_user.user_id');
            })->where('merchant_user.merchant_id', $merchant_id)->whereIn('merchant_user.status', [1, 3]);
            if (! $forceoverpay) {
                $investors = $investors->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchants.complete_percentage', '<', '100');
            }
            if (! empty($investor_id)) {
                $investors = $investors->whereIn('merchant_user.user_id', $investor_id);
            }
            $investors_amount_sum = clone $investors;
            $investors_amount_sum = $investors_amount_sum->sum('amount');
            $investors = $investors->select('merchant_user.id', 'merchant_user.user_id', 'merchant_user.invest_rtr', 'merchant_user.pre_paid', 'merchant_user.commission_amount', 'paid_participant_ishare', 'merchant_user.mgmnt_fee', 'merchant_user.amount', 'merchant_user.user_id', 'users.global_syndication', DB::raw("sum(merchant_user.mgmnt_fee)/(1/amount*'$investors_amount_sum')  as mangt_fee1"), DB::raw("sum('merchant_user.under_writing_fee') as under_writing_fee"))->orderby('merchant_user.user_id')->groupBy('merchant_user.user_id');
            $investors = $investors->get();
            $amount_t2 = array_sum(array_column($investors->toArray(), 'amount'));
            $paid_participant_ishare_t = array_sum(array_column($investors->toArray(), 'paid_participant_ishare'));
            $rtr = array_sum(array_column($investors->toArray(), 'invest_rtr'));
            $mangt_fee1 = array_sum(array_column($investors->toArray(), 'mangt_fee1'));
            $mgmnt_fee_percentage = $mangt_fee1;
            $fees = $mgmnt_fee_percentage;
            $pending_balance_total = ($amount_t2 - $paid_participant_ishare_t / $merchant_curr->factor_rate);
            $total_final_participant_share = [];
            $payment_array = [];
            if ($net_payment_status == 'yes') {
                $this_payment_amount = $this_payment_amount * $funded_amount / $total_funded_on_merchant;
                $this_payment_amount2 = $this_payment_amount * ($total_funded_on_merchant / $total_funded_on_merchant2);
            } else {
                $this_payment_amount2 = $this_payment_amount;
            }
            $first_payment_date = '';
            $per = 0;
            foreach ($dates_array as $key => $date) {
                $date = date('Y-m-d', strtotime($date));
                if ($date < $date_funded) {
                    return ['msg' => 'You cannot enter a payment before funding date'];
                } else {
                    $first_payment_status = ParticipentPayment::where('merchant_id', $merchant_id)->where('payment_date', '<', $date)->count();
                    if (! $first_payment_status) {
                        $first_payment_date = $date;
                    }
                    $this->table1 = new ParticipentPayment();
                    $this->table1->payment = $this_payment_amount2;
                    $this->table1->transaction_type = 1;
                    $this->table1->payment_date = $date;
                    $this->table1->merchant_id = $merchant_id;
                    $this->table1->status = 1;
                    $this->table1->payment_type = $payment_type;
                    $this->table1->reason = $debit_reason;
                    $this->table1->creator_id = Auth::user()->id;
                    $this->table1->rcode = $rcode;
                    $this->table1->save();
                    $investor_payment_arr = [];
                    $investor_balance = 0;
                    $total2 = $pending_balance_total * ($merchant_curr->funded / $amount_t2);
                    foreach ($investors as $key => $investor) {
                        $main_date[] = $date;
                        $complete_percentage = Merchant::select('complete_percentage')->where('merchants.id', $merchant_id)->value('complete_percentage');
                        $pending_balance = round($investor->amount - $investor->paid_participant_ishare / $merchant_curr->factor_rate, 3);
                        if ($net_payment_status != 'yes') {
                            $total = $total2 * ($total_funded_on_merchant / $total_funded_on_merchant2);
                        } else {
                            $total = $total2;
                        }
                        if ($total > 1) {
                            $investor_payment_arr[$investor->id]['participant_share'] = round($this->participant_share($pending_balance, $total, $this_payment_amount), 3);
                        } else {
                            $investor_payment_arr[$investor->id]['participant_share'] = $this_payment_amount * $investor->amount / $amount_t2 * ($amount_t2 / $merchant_curr->funded);
                        }
                        $syndication_fee = PayCalc::getSyndicationFee($investor_payment_arr[$investor->id]['participant_share'], $investor->syndication_fee);
                        $underwritting_fee = round(($investor->under_writing_fee * $investor_payment_arr[$investor->id]['participant_share']), 2);
                        $investor_payment_arr[$investor->id]['mgmnt_fee'] = PayCalc::getMgmntFee($investor_payment_arr[$investor->id]['participant_share'], $investor->mgmnt_fee);
                        $final_part_share = $investor_payment_arr[$investor->id]['participant_share'] - $investor_payment_arr[$investor->id]['mgmnt_fee'];
                        $date_stamp = strtotime($date);
                        if (! isset($total_final_participant_share[$date_stamp])) {
                            $total_final_participant_share[$date_stamp] = 0;
                        }
                        if (! isset($count[$date_stamp])) {
                            $count[$date_stamp] = 0;
                        }
                        $total_final_participant_share[$date_stamp] = $total_final_participant_share[$date_stamp] + $final_part_share;
                        $this->table1->final_participant_share = $this->table1->final_participant_share + $final_part_share;
                        $count[$date_stamp]++;
                        $investor_payment_arr[$investor->id]['investment_id'] = $investor->id;
                        $investor_payment_arr[$investor->id]['participent_payment_id'] = $this->table1->id;
                        $investor_payment_arr[$investor->id]['user_id'] = $investor->user_id;
                        $investor_payment_arr[$investor->id]['merchant_id'] = $merchant_id;
                        $investor_payment_arr[$investor->id]['overpayment'] = 0;
                        $investor_payment_arr[$investor->id]['balance'] = 0;
                        if ($investor_payment_arr[$investor->id]['participant_share']) {
                            $partipantvalue = round($investor_payment_arr[$investor->id]['participant_share'], 2);
                            $total_arr = MerchantUser::select('invest_rtr', 'paid_participant_ishare', 'mgmnt_fee')->where('merchant_id', $merchant_id)->where('user_id', $investor->user_id)->first()->toArray();
                            $paid_participant_ishare = $total_arr['paid_participant_ishare'];
                            $invest_rtr = $total_arr['invest_rtr'];
                            $fee = $total_arr['mgmnt_fee'];
                            $bal_check = PaymentInvestors::where('user_id', $investor->user_id)->where('merchant_id', $merchant_id)->orderByDesc('id');
                            $bal_count = $bal_check->count();
                            if ($bal_count <= 0) {
                                $investor_balance = $invest_rtr - $investor_payment_arr[$investor->id]['participant_share'];
                            } else {
                                $balance = $bal_check->first()->toArray();
                                $investor_balance = $invest_rtr - ($investor_payment_arr[$investor->id]['participant_share'] + $paid_participant_ishare);
                            }
                            $investor_payment_arr[$investor->id]['balance'] = $investor_balance;
                            $total = ($paid_participant_ishare) + ($investor_payment_arr[$investor->id]['participant_share']);
                            $total_after_fee = ($paid_participant_ishare * (100 - $fee) / 100);
                            if (($invest_rtr < $total) || $complete_percentage > 100) {
                                if (! isset($overpayment_value_inv[$investor->user_id])) {
                                    $overpayment_value_inv[$investor->user_id] = 0;
                                }
                                if ($complete_percentage > 100 || $per == 1) {
                                    $overpayment_value = ($investor_payment_arr[$investor->id]['participant_share'] - $investor_payment_arr[$investor->id]['mgmnt_fee']);
                                    if ($debit_status === 'yes') {
                                    }
                                } else {
                                    $overpayment_value = ($total - $invest_rtr) * (1 - $fee / 100) - $overpayment_value_inv[$investor->user_id];
                                }
                                $investor_payment_arr[$investor->id]['overpayment'] = $overpayment_value;
                            } else {
                                $investor_payment_arr[$investor->id]['overpayment'] = 0;
                            }
                        }
                        if (! in_array($investor->user_id, $investor_ids)) {
                            $investor_ids[] = $investor->user_id;
                        }
                    }
                    foreach ($investor_payment_arr as $investorPaymentInput) {
                        PaymentInvestors::create($investorPaymentInput);
                    }
                    $userIds = collect($investor_payment_arr)->pluck('user_id')->merge($userIds)->toArray();
                    $part_id = ParticipentPayment::select('id')->where('merchant_id', $merchant_id)->where('payment_date', $date)->orderBy('created_at')->first();
                    PaymentInvestors::where('participent_payment_id', $part_id->id)->delete();
                    ParticipentPayment::find($part_id->id)->delete();
                    ParticipentPayment::where('merchant_id', $merchant_id)->where('payment_date', $date)->where('id', $this->table1->id)->update(['final_participant_share' => $this->table1->final_participant_share]);
                    $participent_payment_id = $this->table1->id;
                    $payment_data = PaymentInvestors::select(['payment_investors.id', 'payment_investors.user_id', 'payment_investors.merchant_id', 'payment_investors.participant_share', 'payment_investors.mgmnt_fee', DB::raw('(merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount ) as invested_amount
                '), DB::raw('(merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount ) - (merchant_user.paid_participant_ishare) as net_value
                '), DB::raw('
                (
                participant_share-payment_investors.mgmnt_fee
                )
                -
               (
                (
                participant_share-payment_investors.mgmnt_fee )
                *
                    (merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount)
                    /
                    (merchant_user.invest_rtr- (merchant_user.mgmnt_fee/100)*merchant_user.invest_rtr)
                )
                as profit_value1
                '), DB::raw('(
                (
                participant_share-payment_investors.mgmnt_fee
                )
                -


                    (merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount)

                )
                as profit_value
                ')])->leftJoin('merchant_user', function ($join) {
                        $join->on('payment_investors.user_id', 'merchant_user.user_id');
                        $join->on('payment_investors.merchant_id', 'merchant_user.merchant_id');
                    })->where('payment_investors.participent_payment_id', $participent_payment_id)->get();
                    foreach ($payment_data as $dt) {
                        $profit = $dt->profit_value;
                        $principal = $dt->participant_share - $dt->mgmnt_fee - $dt->profit_value;
                        if ($debit_status == 'yes' && $dt->overpayment != 0) {
                            $principal = $dt->participant_share - $dt->mgmnt_fee - $dt->profit_value;
                        }
                        $total_principle = PaymentInvestors::where('user_id', $dt->user_id)->where('merchant_id', $dt->merchant_id)->sum('principal');
                        $total_principle = $total_principle + $principal;
                        if ($total_principle > $dt->invested_amount) {
                            if ($debit_status === 'yes') {
                                $profit = $dt->profit_value;
                                $principal = $dt->participant_share - $dt->mgmnt_fee - $dt->profit_value;
                            }
                            $balance_principle = round($total_principle, 2) - round($dt->invested_amount, 2);
                            $profit = round($profit, 2) + round($balance_principle, 2);
                            $principal = round($principal, 2) - round($balance_principle, 2);
                            if ($principal < 0) {
                                $principal = 0;
                            }
                        }
                        PaymentInvestors::where('id', $dt->id)->update(['profit' => $profit, 'principal' => $principal]);
                    }
                    $complete_per = PayCalc::completePercentage($merchant_curr->id, $investor_ids);
                    if ($complete_per > 100) {
                        $per = 1;
                    }
                }
            }
        } else {
        }
        $userIds = DB::table('payment_investors')->distinct()->select('user_id')->pluck('user_id')->toArray();

        return 1;
    }

    public function modify_payments11($merchant_id)
    {
        $ovp_payments = ParticipentPayment::select('participent_payments.id', 'payment_type')->where('participent_payments.merchant_id', $merchant_id)->orderBy('payment_date')->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->groupBy('participent_payment_id')->get()->toArray();
        foreach ($ovp_payments as $key => $value) {
            $payment_data1 = PaymentInvestors::select(['payment_investors.id', 'payment_investors.user_id', 'payment_investors.merchant_id', 'payment_investors.participant_share', 'payment_investors.mgmnt_fee', 'overpayment', DB::raw('(merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount ) as invested_amount
                '), DB::raw('(merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount ) - (merchant_user.paid_participant_ishare) as net_value
                '), DB::raw('
                    (
                (
                participant_share-payment_investors.mgmnt_fee
                )
                -
               (
                (
                participant_share-payment_investors.mgmnt_fee )
                *
                    (merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount)
                    /
                    (merchant_user.invest_rtr- (merchant_user.mgmnt_fee/100)*merchant_user.invest_rtr)
                )
                )
                as profit_value1
                '), DB::raw('(
                (
                participant_share-payment_investors.mgmnt_fee
                )
                -


                    (merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount)

                )
                as profit_value
                ')])->join('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id')->leftJoin('merchant_user', function ($join) {
                $join->on('payment_investors.user_id', 'merchant_user.user_id');
                $join->on('payment_investors.merchant_id', 'merchant_user.merchant_id');
            })->where('payment_investors.participent_payment_id', $value['id'])->orderBy('payment_date');
            $payment_data = $payment_data1->get();
            $total_overpayment = $payment_data1->sum('overpayment');
            foreach ($payment_data as $dt) {
                $profit = $dt->profit_value1;
                $principal = $dt->participant_share - $dt->mgmnt_fee - $dt->profit_value1;
                if ($value['payment_type'] == 0 && $dt->overpayment != 0) {
                    $principal = $dt->participant_share - $dt->mgmnt_fee - $dt->profit_value;
                }
                $total_principle = PaymentInvestors::where('user_id', $dt->user_id)->where('merchant_id', $dt->merchant_id)->sum('principal');
                $total_principle = round($total_principle, 2) + round($principal, 2);
                $invested_amount = round($dt->invested_amount, 2);
                if ($total_principle > $invested_amount) {
                    if ($value['payment_type'] == 0 && $dt->overpayment != 0) {
                        $profit = $dt->profit_value;
                        $principal = $dt->participant_share - $dt->mgmnt_fee - $dt->profit_value;
                    }
                    $balance_principle = round($total_principle, 2) - round($dt->invested_amount, 2);
                    $profit = round($profit, 2) + round($balance_principle, 2);
                    $principal = round($principal, 2) - round($balance_principle, 2);
                    if ($principal < 0) {
                        $principal = 0;
                    }
                } else {
                }
                if ($total_overpayment < 0) {
                    if ($value['payment_type'] == 0 && ($dt->overpayment <= 0)) {
                        $principal = 0;
                    }
                }
                PaymentInvestors::where('id', $dt->id)->update(['profit' => $profit, 'principal' => $principal]);
            }
        }
    }

    public function sortByOrder($a, $b)
    {
        return $a['old_completed_share'] - $b['old_completed_share'];
    }

    public function generatePaymentForLender($merchant_id, $dates_array, $payment_amount, $net_payment_status, $debit_status, $debit_reason, $investor_id, $rcode = '', $mode_of_payment = null, $send_permission = null, $isBulk = false)
    {
        if(!is_numeric($payment_amount)){ throw new \Exception("Please Enter Numeric No", 1); }
        $payment_amount=round($payment_amount,2);
        $settings = Settings::select('email', 'forceopay')->first();
        $forceoverpay = $settings->forceopay;
        $description = '';
        $creator_id = null;
        if (Auth::check()) {
            $creator_id = Auth::user()->id;
        } elseif (Session::has('credit_card_payment_creator')) {
            $creator_id = Session::get('credit_card_payment_creator');
        }
        if ($debit_status === 'yes') {
            $description = 'Debit Payment';
            $payment_type = 0;
            $this_payment_amount = -$payment_amount;
        } else {
            switch ($mode_of_payment) {
                case ParticipentPayment::ModeAchPayment:
                $description = 'ACH Payment';
                break;
                case ParticipentPayment::ModeCreditCard:
                $description = 'Credit Card Payment';
                break;
                default:
                $description = 'Payment';
                break;
            }
            $payment_type = 1;
            $this_payment_amount = $payment_amount;
        }
        $merchant_curr = Merchant::where('merchants.id', $merchant_id)
        ->select('funded', 'rtr', 'agent_fee_applied', 'factor_rate', 'date_funded', 'commission', 'pmnts', 'payment_amount', 'sub_statuses.name as substatus_name', 'complete_percentage', 'merchants.id', 'merchants.name as name', 'advance_type', 'merchants.sub_status_id')
        ->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
        ->first();
        $agent_fee_applied = $merchant_curr->agent_fee_applied;
        $funded_amount = $merchant_curr->funded;
        $rtr = $merchant_curr->rtr;
        $date_funded = $merchant_curr->date_funded;
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
        if($AgentFeeAccount){
            $MerchantUser = MerchantUser::where('merchant_id', $merchant_id)->where('user_id', $AgentFeeAccount->id)->first();
            if (! $MerchantUser) {
                $item = [
                    'user_id'                   =>$AgentFeeAccount->id,
                    'amount'                    =>0,
                    'merchant_id'               =>$merchant_id,
                    'status'                    =>1,
                    'invest_rtr'                =>0,
                    'mgmnt_fee'                 =>0,
                    'syndication_fee_percentage'=>0,
                    'commission_amount'         =>0,
                    'commission_per'            =>0,
                    'up_sell_commission_per'    =>0,
                    'up_sell_commission'        =>0,
                    'under_writing_fee'         =>0,
                    'under_writing_fee_per'     =>0,                    
                    'pre_paid'                  =>0,
                    's_prepaid_status'          =>1,
                    'creator_id'                => $creator_id,
                ];
                MerchantUser::create($item);
            }  
        }
        $merchant_user_arr = MerchantUser::where('merchant_id', $merchant_id);
        if (! is_array($investor_id)) {
            $investor_id = $investor_id->toArray();
        }
        if (! empty($investor_id)) {
            if ($OverpaymentAccount) {
                $investor_id = array_flip($investor_id);
                unset($investor_id[$OverpaymentAccount->id]);
                $investor_id = array_flip($investor_id);
            }
            if ($AgentFeeAccount) {
                $investor_id = array_flip($investor_id);
                unset($investor_id[$AgentFeeAccount->id]);
                $investor_id = array_flip($investor_id);
            }
            $merchant_user_arr = $merchant_user_arr->whereIn('merchant_user.user_id', $investor_id);
        }
        $merchant_user_arr = $merchant_user_arr->select(DB::raw('sum(paid_participant_ishare) as total_paid_amount,sum(amount*(100-(mgmnt_fee))/100) as total_funded_on_merchant'))->first();
        $merchant_user_arr2 = MerchantUser::where('merchant_id', $merchant_id)->whereIn('merchant_user.status', [1, 3])->select(DB::raw('sum(paid_participant_ishare) as total_paid_amount,sum(amount*(100-(mgmnt_fee))/100) as total_funded_on_merchant'), DB::raw("sum('merchant_user.invest_rtr') as invest_rtr"));
        if ($OverpaymentAccount) {
            $merchant_user_arr2 = $merchant_user_arr2->where('merchant_user.user_id', '!=', $OverpaymentAccount->id);
        }
        if ($AgentFeeAccount) {
            $merchant_user_arr2 = $merchant_user_arr2->where('merchant_user.user_id', '!=', $AgentFeeAccount->id);
        }
        $merchant_user_arr2 = $merchant_user_arr2->first();
        $total_paid_amount = round($merchant_user_arr->total_paid_amount,2);
        $total_funded_on_merchant = $merchant_user_arr->total_funded_on_merchant;
        $total_funded_on_merchant2 = $merchant_user_arr2->total_funded_on_merchant;
        $total_paid_amount += $this_payment_amount;
        try {
            if ($rtr >= $total_paid_amount || $forceoverpay) {
                $investorsMain = MerchantUser::where('merchant_user.merchant_id', $merchant_id)->whereIn('merchant_user.status', [1, 3]);
                if (! $forceoverpay) {
                    $investorsMain = $investorsMain->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchants.complete_percentage', '<', '100');
                }
                if (! empty($investor_id)) {
                    $investorsMain = $investorsMain->whereIn('merchant_user.user_id', $investor_id);
                }
                $investor_ids = clone $investorsMain;
                $investor_ids = $investor_ids->pluck('user_id', 'user_id')->toArray();
                $first_payment_date = '';
                foreach ($dates_array as $key => $date) {
                    $date = date('Y-m-d', strtotime($date));
                    if ($date < $date_funded) {
                        throw new \Exception('You cannot enter a payment before funding date', 1);
                    }
                    if ($debit_status === 'yes') {
                        $this_payment_amount = -$payment_amount;
                    } else {
                        $this_payment_amount = $payment_amount;
                    }
                    $investors = $investorsMain->select('merchant_user.id', 'merchant_user.user_id')
                    ->where('invest_rtr', '!=', 0)
                    ->orderby('merchant_user.user_id')
                    ->groupBy('merchant_user.user_id')
                    ->get();
                    if ($net_payment_status == 'yes') {
                        $this_payment_amount = $this_payment_amount * $funded_amount / $total_funded_on_merchant;
                        $this_payment_amount2 = $this_payment_amount * ($total_funded_on_merchant / $total_funded_on_merchant2);
                    } else {
                        $this_payment_amount2 = $this_payment_amount;
                    }
                    $date = Carbon::parse($date)->format('Y-m-d');
                    $m_rcode = ($rcode) ? $rcode : 0;
                    $this->table1 = new ParticipentPayment();
                    $this->table1->payment = round($this_payment_amount2, 2);
                    $this->table1->transaction_type = 1;
                    $this->table1->payment_date = $date;
                    $this->table1->merchant_id = $merchant_id;
                    switch ($mode_of_payment) {
                        case ParticipentPayment::PaymentModeManual:
                        $this->table1->status = ParticipentPayment::StatusCompleted;
                        break;
                        case ParticipentPayment::ModeAchPayment:
                        $this->table1->status = ParticipentPayment::StatusCompleted;
                        break;
                        case ParticipentPayment::ModeCreditCard:
                        $this->table1->status = ParticipentPayment::StatusPending;
                        break;
                        default:
                        $this->table1->status = ParticipentPayment::StatusCompleted;
                        break;
                    }
                    $this->table1->payment_type    = $payment_type;
                    $this->table1->reason          = $debit_reason;
                    $this->table1->mode_of_payment = $mode_of_payment;
                    $this->table1->model           = ParticipentPayment::class;
                    $this->table1->creator_id      = Auth::user()->id ?? null;
                    $this->table1->rcode           = $m_rcode;
                    $this->table1->investor_ids    = implode(',', $investor_ids);
                    $agent_fee_percentage          = 0;
                    if($merchant_curr->agent_fee_applied){
                        $agent_fee_percentage=Settings::select('agent_fee_per')->value('agent_fee_per')??0;
                    }
                    $this->table1->agent_fee_percentage = $agent_fee_percentage;
                    $this->table1->save();
                    $participent_payment_id = $this->table1->id;
                    if ($this->table1->status == ParticipentPayment::StatusCompleted) {
                        Session::put('payment_id', $this->table1->id);
                        $return_result = GPH::GPH_PaymentArea($this->table1);
                        if ($return_result['result'] != 'success') {
                            throw new \Exception($return_result['result'], 1);
                        }
                    }
                    GPH::GPH_MerchantArea($this->table1);
                }
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function PaymentInvestorsFunctionForOverpayment($PIData)
    {
        $this_payment_amount = $PIData['this_payment_amount'];
        $merchant_id = $PIData['merchant_id'];
        $investor_ids = $PIData['investor_ids'];
        $merchant_curr = $PIData['merchant_curr'];
        $investor_payment_arr = $PIData['investor_payment_arr'];
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        $MerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($OverpaymentAccount->id)->first();
        $investment_id = null;
        if ($MerchantUser) {
            $investment_id = $MerchantUser->id;
        }
        if (isset($investor_payment_arr[$OverpaymentAccount->id])) {
            $investor_payment_arr[$OverpaymentAccount->id]['amount'] += $this_payment_amount;
        } else {
            $investor_payment_arr[$OverpaymentAccount->id] = [
                'paid_participant_ishare' => 0,
                'actual_participant_share'=> 0,
                'participant_share'       => 0,
                'mgmnt_fee'               => 0,
                'mgmnt_fee_percentage'    => 0,
                'investment_id'           => $investment_id,
                'participent_payment_id'  => $this->table1->id,
                'user_id'                 => $OverpaymentAccount->id,
                'merchant_id'             => $merchant_id,
                'overpayment'             => 0,
                'actual_overpayment'      => 0,
                'amount'                  => $this_payment_amount,
            ];
        }
        $investor_payment_arr[$OverpaymentAccount->id]['participant_share'] = round($investor_payment_arr[$OverpaymentAccount->id]['amount'], 4);
        $investor_payment_arr[$OverpaymentAccount->id]['actual_participant_share'] = round($investor_payment_arr[$OverpaymentAccount->id]['amount'], 4);
        $investor_payment_arr[$OverpaymentAccount->id]['overpayment'] = round($investor_payment_arr[$OverpaymentAccount->id]['amount'], 4);
        $investor_payment_arr[$OverpaymentAccount->id]['actual_overpayment'] = round($investor_payment_arr[$OverpaymentAccount->id]['amount'], 4);
        $investor_ids = $OverpaymentAccount->id;

        return [
            'investor_ids'         => $investor_ids,
            'investor_payment_arr' => $investor_payment_arr,
        ];
    }

    public function PaymentInvestorsFunctionForInvestor($PIData)
    {
        $per = $PIData['per'];
        $investor = $PIData['investor'];
        $merchant_curr = $PIData['merchant_curr'];
        $net_payment_status = $PIData['net_payment_status'];
        $total_funded_on_merchant = $PIData['total_funded_on_merchant'];
        $total_funded_on_merchant2 = $PIData['total_funded_on_merchant2'];
        $total2 = $PIData['total2'];
        $invested_amount = $PIData['invested_amount'];
        $total_investor_funded = $invested_amount * $PIData['max_participant_fund_per'];
        $this_payment_amount = $PIData['this_payment_amount'];
        $complete_percentage = $PIData['complete_percentage'];
        $merchant_id = $PIData['merchant_id'];
        $investor_ids = $PIData['investor_ids'];
        $debit_status = $PIData['debit_status'];
        $round_off = $PIData['round_off'];
        $investor_payment_arr = $PIData['investor_payment_arr'];
        $payment_investors_overpayment = 0;
        $investor_payment_arr[$investor->id]['paid_participant_ishare'] = $investor->paid_participant_ishare ?? 0;
        $rtr = Merchant::select('rtr')->find($merchant_id)->rtr;
        $remaining_percentage = 100 - $complete_percentage;
        $remaing_payment_amount = $rtr * $remaining_percentage / 100;
        $diff = 0;
        $pending_balance = round($investor->amount - $investor->paid_participant_ishare / $merchant_curr->factor_rate, 4);
        $pending_balance = round($investor->invest_rtr - $investor->paid_participant_ishare, 4);
        if ($net_payment_status != 'yes') {
            $total = $total2 * ($total_funded_on_merchant / $total_funded_on_merchant2);
        } else {
            $total = $total2;
        }
        $remaining_participant_share = $investor->amount - $investor->paid_participant_ishare;
        $remaining_participant_share = $investor->remaining_participant_share;
        $overpayment = 0;
        if ($total > 1) {
            $participant_share = floor($this->participant_share($pending_balance, $total, $this_payment_amount) * 100) / 100;
            $participant_share = $this->participant_share($pending_balance, $total, $this_payment_amount);
            if ($participant_share) {
                if ($PIData['final_payment_flag']) {
                    if ($participant_share > $remaining_participant_share) {
                        $overpayment = round($participant_share - $remaining_participant_share, 4);
                        $participant_share = $remaining_participant_share;
                    } else {
                        $overpayment = round($remaining_participant_share - $participant_share, 4);
                        $participant_share += $overpayment;
                        $overpayment = $overpayment * -1;
                    }
                    $completed_payment = round($investor->paid_participant_ishare + $participant_share, 4);
                    if ($completed_payment > round($investor->invest_rtr, 4)) {
                        $diff = $completed_payment - $investor->invest_rtr;
                        dd('Over payment waiting for this case need to ckeck', 'diff greater than '.round($diff, 4), 'completed_payment '.$completed_payment, 'invest_rtr '.$investor->invest_rtr, 'paid_participant_ishare '.$investor->paid_participant_ishare, 'participant_share '.$participant_share);
                    }
                    if ($completed_payment < $investor->invest_rtr) {
                        $diff = $investor->invest_rtr - $completed_payment;
                        $participant_share += $diff;
                    }
                    $overpayment -= $diff;
                }
            }
            if ($participant_share > $remaining_participant_share) {
                $overpayment = round($participant_share - $remaining_participant_share, 4);
                $participant_share = $remaining_participant_share;
            }
            $investor_payment_arr[$investor->id]['participant_share'] = $participant_share;
        } else {
            $share_percentage = $this_payment_amount * $investor->amount;
            $investor_payment_arr[$investor->id]['participant_share'] = ($total_investor_funded > 0) ? round(($share_percentage / $total_investor_funded), 4) : 0;
        }
        $investor_payment_arr[$investor->id]['actual_participant_share'] = $investor_payment_arr[$investor->id]['participant_share'];
        $syndication_fee = PayCalc::calculateSyndicationFee($investor_payment_arr[$investor->id]['participant_share'], $investor->syndication_fee);
        $underwritting_fee = round(($investor->under_writing_fee * $investor_payment_arr[$investor->id]['participant_share']), 2);
        $investor_payment_arr[$investor->id]['mgmnt_fee'] = PayCalc::calculateMgmntFee($investor_payment_arr[$investor->id]['participant_share'], $investor->mgmnt_fee);
        $final_part_share = $investor_payment_arr[$investor->id]['participant_share'] - $investor_payment_arr[$investor->id]['mgmnt_fee'];
        $date_stamp = strtotime($date);
        if (! isset($total_final_participant_share[$date_stamp])) {
            $total_final_participant_share[$date_stamp] = 0;
        }
        if (! isset($count[$date_stamp])) {
            $count[$date_stamp] = 0;
        }
        $total_final_participant_share[$date_stamp] = $total_final_participant_share[$date_stamp] + $final_part_share;
        $this->table1->final_participant_share += $final_part_share;
        $count[$date_stamp]++;
        $investor_payment_arr[$investor->id]['mgmnt_fee_percentage'] = $investor->mgmnt_fee;
        $investor_payment_arr[$investor->id]['investment_id'] = $investor->id;
        $investor_payment_arr[$investor->id]['participent_payment_id'] = $this->table1->id;
        $investor_payment_arr[$investor->id]['user_id'] = $investor->user_id;
        $investor_payment_arr[$investor->id]['merchant_id'] = $merchant_id;
        $investor_payment_arr[$investor->id]['overpayment'] = $overpayment;
        $investor_payment_arr[$investor->id]['actual_overpayment'] = $overpayment;
        $investor_payment_arr[$investor->id]['balance'] = 0;
        $investor_payment_arr[$investor->id]['completed_share'] = 0;
        $investor_payment_arr[$investor->id]['old_completed_share'] = 0;
        $investor_payment_arr[$investor->id]['invest_rtr'] = 0;
        if ($investor_payment_arr[$investor->id]['participant_share']) {
            $total_arr = MerchantUser::select('invest_rtr', 'paid_participant_ishare', 'mgmnt_fee')->where('merchant_id', $merchant_id)->where('user_id', $investor->user_id)->first()->toArray();
            $paid_participant_ishare = $total_arr['paid_participant_ishare'];
            $invest_rtr = $total_arr['invest_rtr'];
            $fee = $total_arr['mgmnt_fee'];
            $investor_payment_arr[$investor->id]['invest_rtr'] = $invest_rtr;
            $bal_check = PaymentInvestors::where('user_id', $investor->user_id)->where('merchant_id', $merchant_id)->orderByDesc('id');
            $bal_count = $bal_check->count();
            if ($bal_count <= 0) {
                $investor_balance = $invest_rtr - $investor_payment_arr[$investor->id]['participant_share'];
            } else {
                $balance = $bal_check->first()->toArray();
                $investor_balance = $invest_rtr - ($investor_payment_arr[$investor->id]['participant_share'] + $paid_participant_ishare);
                if ($debit_status === 'yes') {
                    $total_investor_payments = ($investor_payment_arr[$investor->id]['participant_share'] + $paid_participant_ishare);
                    if ($total_investor_payments < 0) {
                        $investor_payment_arr[$investor->id]['overpayment'] = $total_investor_payments;
                        $investor_payment_arr[$investor->id]['actual_overpayment'] = $total_investor_payments;
                        $investor_payment_arr[$investor->id]['participant_share'] -= $total_investor_payments;
                        $investor_payment_arr[$investor->id]['mgmnt_fee'] = PayCalc::calculateMgmntFee($investor_payment_arr[$investor->id]['participant_share'], $investor->mgmnt_fee);
                    }
                }
                $payment_investors_overpayment = DB::table('payment_investors')->select('overpayment')->where('merchant_id', $merchant_id)->where('user_id', $investor_payment_arr[$investor->id]['user_id'])->sum('overpayment');
            }
            $investor_payment_arr[$investor->id]['balance'] = round($investor_balance, 4);
            $investor_payment_arr[$investor->id]['completed_share'] = round(($investor_payment_arr[$investor->id]['participant_share'] + $investor_payment_arr[$investor->id]['paid_participant_ishare']) / $invest_rtr * 100, 4);
            $investor_payment_arr[$investor->id]['old_completed_share'] = round($investor_payment_arr[$investor->id]['paid_participant_ishare'] / $invest_rtr * 100, 4);
            $total = $paid_participant_ishare + $investor_payment_arr[$investor->id]['participant_share'];
            $total_after_fee = ($paid_participant_ishare * (100 - $fee) / 100);
            if (($invest_rtr < $total) || $complete_percentage > 100) {
                if (! isset($overpayment_value_inv[$investor->user_id])) {
                    $overpayment_value_inv[$investor->user_id] = 0;
                }
                if ($complete_percentage > 100 || $per == 1) {
                    $overpayment_value = ($investor_payment_arr[$investor->id]['participant_share'] - $investor_payment_arr[$investor->id]['mgmnt_fee']);
                    $overpayment_value = $investor_payment_arr[$investor->id]['participant_share'];
                    if ($debit_status === 'yes') {
                        $overpayment_value = $total - $investor_payment_arr[$investor->id]['participant_share'] - $invest_rtr;
                        if ($overpayment_value > $payment_investors_overpayment) {
                            $overpayment_value = $payment_investors_overpayment;
                        }
                        $overpayment_value = $overpayment_value * -1;
                        if ($investor_payment_arr[$investor->id]['participant_share'] > $overpayment_value) {
                            $overpayment_value = $investor_payment_arr[$investor->id]['participant_share'] - $investor_payment_arr[$investor->id]['mgmnt_fee'];
                        }
                    }
                } else {
                    $overpayment_value = ($total - $invest_rtr) * (1 - $fee / 100) - $overpayment_value_inv[$investor->user_id];
                    $overpayment_value = ($total - $invest_rtr) - $overpayment_value_inv[$investor->user_id];
                }
                $investor_payment_arr[$investor->id]['overpayment'] = $overpayment_value;
                $investor_payment_arr[$investor->id]['actual_overpayment'] = $overpayment_value;
            } else {
            }
        }
        $investor_payment_arr[$investor->id]['actual_participant_share'] = $investor_payment_arr[$investor->id]['participant_share'];
        if (! in_array($investor->user_id, $investor_ids)) {
            $investor_ids[] = $investor->user_id;
        }
        if ($investor_payment_arr[$investor->id]['overpayment']) {
            // $PIData['this_payment_amount']  = $investor_payment_arr[$investor->id]['overpayment'];
            // $PIData['investor_payment_arr'] = $investor_payment_arr;
            // if ($investor_payment_arr[$investor->id]['overpayment'] < 0) {
            // 	if ($payment_investors_overpayment >= abs($investor_payment_arr[$investor->id]['overpayment'])) {
            // 		$PIData['this_payment_amount'] = 0;
            // 		goto skip_overpayment_transfer;
            // 	} else {
            // 		$investor_payment_arr[$investor->id]['overpayment']        = $payment_investors_overpayment * -1;
            // 		$investor_payment_arr[$investor->id]['actual_overpayment'] = $payment_investors_overpayment * -1;
            // 		$PIData['this_payment_amount'] += $payment_investors_overpayment;
            // 	}
            // } else {
            $investor_payment_arr[$investor->id]['overpayment'] = 0;
            $investor_payment_arr[$investor->id]['actual_overpayment'] = 0;
            // }
            // $return_result  = $this->PaymentInvestorsFunctionForOverpayment($PIData);
            // $count          = $return_result['count'];
            // $investor_ids[] = $return_result['investor_ids'];
            // if (isset($investor_payment_arr[$return_result['investor_ids']])) {
            // 	$investor_payment_arr[$return_result['investor_ids']] = $return_result['investor_payment_arr'][$return_result['investor_ids']];
            // } else {
            // 	$investor_payment_arr += $return_result['investor_payment_arr'];
            // }
            // skip_overpayment_transfer:
        }

        return [
            'count'                => $count,
            'main_date'            => $main_date,
            'investor_ids'         => $investor_ids,
            'investor_payment_arr' => $investor_payment_arr,
        ];
    }

    public function participant_share($p1, $total, $x)
    {
        if ($total != 0) {
            return ($p1 * $x) / ($total);
        } else {
            return 0;
        }
    }

    public function get_substatus_list($merchant = null)
    {
        if (! empty($merchant)) {
            $status_check = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'), DB::raw('sum(merchant_user.invest_rtr *(merchant_user.mgmnt_fee)/100 ) as mangt_fee'), DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'))->where('merchant_id', $merchant->id)->first()->toArray();
            $payments = PaymentInvestors::select(DB::raw('sum(participant_share-payment_investors.mgmnt_fee) as final_participant_share'))->where('merchant_id', $merchant->id)->first()->toArray();
            $payments_investors = PaymentInvestors::select(DB::raw('sum(payment_investors.participant_share) as participant_share'))->where('payment_investors.merchant_id', $merchant->id)->groupBy('merchant_id');
            $participant_share = $total_rtr = 0;
            if ($payments_investors->count() > 0) {
                $investors = $payments_investors->first()->toArray();
                $participant_share = $investors['participant_share'];
            }
            $merchant_array = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'))->where('merchant_id', $merchant->id)->groupBy('merchant_id');
            if ($merchant_array->count() > 0) {
                $investor_rtr = $merchant_array->first()->toArray();
                $total_rtr = $investor_rtr['invest_rtr'];
            }
            $bal_rtr = $total_rtr - $participant_share;
            $actual_payment_left = 0;
            if ($total_rtr > 0) {
                $actual_payment_left = ($merchant->rtr) ? $bal_rtr / (($total_rtr / $merchant->rtr) * ($merchant->rtr / $merchant->pmnts)) : 0;
            }
            $act_paymnt_left = floor($actual_payment_left);
            $actual_payment_left = ($act_paymnt_left > 0) ? $act_paymnt_left : 0;
            $statusList = [];
            if (empty($status_check['invest_rtr'])) {
                $statusList = [17, 1,23, $merchant->sub_status_id];
            }
            if ($payments['final_participant_share'] <= $status_check['investment_amount'] && ! empty($status_check['investment_amount'])) {
                array_push($statusList, 4, 22, 2, 5, 10, 12, 13, 15, 16, $merchant->sub_status_id);
            }
            if ((! empty($payments['final_participant_share']) && ($payments['final_participant_share'] < $status_check['invest_rtr'] && $payments['final_participant_share'] > $status_check['investment_amount'])) && $merchant->complete_percentage < 100) {
                array_push($statusList, 18, 19, 20, 2, 5, 10, 12, 13, 15, 16, $merchant->sub_status_id);
            }
            $status = [18, 19, 20];
            if ($merchant->complete_percentage >= 99 && in_array($merchant->sub_status_id, $status)) {
                array_push($statusList, 1,23, $merchant->sub_status_id);
            }
            if ($merchant->complete_percentage >= 100 && ! in_array($merchant->sub_status_id, $status)) {
                if ($actual_payment_left <= 0 && $merchant->complete_percentage >= 100) {
                    array_push($statusList, 11, $merchant->sub_status_id);
                }
            }
            if (! in_array($merchant->sub_status_id, $status)) {
                if (($merchant->complete_percentage < 100 && $merchant->complete_percentage >= 1)) {
                    array_push($statusList, 1,23, $merchant->sub_status_id);
                }
            }

             $mode=Settings::where('keys', 'collection_default_mode')->value('values');

             if($mode==1)
             {

                $status_1 = [18, 19, 20,4,22];
                 if (in_array($merchant->sub_status_id, $status_1) && ($merchant->complete_percentage <100 && $merchant->complete_percentage >= 1)) {
                     $statusList=[];
                    array_push($statusList,$merchant->sub_status_id);
                  }

             }
             if($merchant->sub_status_id==1){
                if (!in_array(23, $statusList)){
                    array_push($statusList,23);
                }
             }
           
            return $statusList;
        }
    }

    public function search_default_data($filter_merchants = null, $filter_investors = null, $lenders = null, $from_date = null, $to_date = null, $userId = null, $sub_status = null, $funded_date = null, $velocity_type = null, $active = null, $days = null, $investor_type = null,$velocity_owned = null, $permission = null, $search_key = null)
    {

        $ignore_merchants_old_settle = [];
        $array = [4, 22];
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $merchants9 = User::join('merchant_user', 'merchant_user.user_id', 'users.id')->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->whereIn('merchant_user.status', [1, 3])->whereNotIn('users.company',$disabled_companies);
        $SpecialAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE, User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id', 'users.id')->toArray();
        if ($SpecialAccount) {
            $merchants9 = $merchants9->whereNotIn('users.id', $SpecialAccount);
        }
        if ($filter_merchants) {
            $merchants9 = $merchants9->whereIn('merchants.id', $filter_merchants);
        }
        if ($search_key) {
            $merchants9 = $merchants9->where(function ($query) use ($search_key) {
                $query->where('users.name', 'like', '%'.$search_key.'%');
            });
        }
        if (! empty($sub_status) && is_array($sub_status)) {
            $merchants9 = $merchants9->whereIn('merchants.sub_status_id', $sub_status);
        }
        if ($velocity_type != 0) {
            $merchants9 = $merchants9->where('users.company', $velocity_type);
        }
        if (! empty($investor_type)) {
            $merchants9 = $merchants9->whereIn('users.investor_type', $investor_type);
        }
        if ($filter_investors) {
            $merchants9 = $merchants9->whereIn('merchant_user.user_id', $filter_investors);
        }
        if ($lenders) {
            $merchants9 = $merchants9->whereIn('merchants.lender_id', $lenders);
        }
        $date_filter_table = 'merchants.last_status_updated_date';
        if ($funded_date == 'on') {
            $date_filter_table = 'merchants.date_funded';
        } else {
            $date_filter_table = 'merchants.last_status_updated_date';
        }
        if ($from_date) {
            $merchants9 = $merchants9->whereDate($date_filter_table, '>=', $from_date);
        }
        $default_date = ! empty($to_date) ? $to_date : date('Y-m-d');
        if ($to_date) {
            $merchants9 = $merchants9->whereDate($date_filter_table, '<=', $default_date);
        }
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        if ($days != null && $days == 0) {
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=60");
        }
        if ($days == 61) {
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <= 90");
        }
        if ($days == 91) {
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=120");
        }
        if ($days == 121) {
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY))  <=150");
        }
        if ($days == 150) {
            $merchants9 = $merchants9->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >".$days);
        }
        if ($active == 1) {
            $merchants9 = $merchants9->where('users.active_status', 1);
        } elseif ($active == 2) {
            $merchants9 = $merchants9->where('users.active_status', 0);
        }
        if($velocity_owned){
            $merchants9 = $merchants9->where('users.velocity_owned', 1);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $merchants9 = $merchants9->where('users.company', $userId);
            } else {
                $merchants9 = $merchants9->where('users.creator_id', $userId);
            }
        }
        $invsestors_rtr = DB::table('merchant_user')->whereIn('merchant_user.status', [1, 3])->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id');
        if ($active == 1) {
            $invsestors_rtr = $invsestors_rtr->where('users.active_status', 1);
        } elseif ($active == 2) {
            $invsestors_rtr = $invsestors_rtr->where('users.active_status', 0);
        }
        if ($filter_merchants) {
            $invsestors_rtr = $invsestors_rtr->whereIn('merchants.id', $filter_merchants);
        }
        if (! empty($filter_investors)) {
            $invsestors_rtr = $invsestors_rtr->whereIn('merchant_user.user_id', $filter_investors);
        }
        if (! empty($lenders)) {
            $invsestors_rtr = $invsestors_rtr->whereIn('merchants.lender_id', $lenders);
        }
        $invsestors_rtr = $invsestors_rtr->groupBy('merchant_user.user_id')->pluck(DB::raw('sum((invest_rtr-(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100))+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)),0)) as total_rtr'), 'merchant_user.user_id');
        $investment_amount = DB::table('merchant_user')->whereIn('merchant_user.status', [1, 3])->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id');
        if ($active == 1) {
            $investment_amount = $investment_amount->where('users.active_status', 1);
        } elseif ($active == 2) {
            $investment_amount = $investment_amount->where('users.active_status', 0);
        }
        if (! empty($lenders)) {
            $investment_amount = $investment_amount->whereIn('merchants.lender_id', $lenders);
        }
        if (! empty($filter_investors)) {
            $investment_amount = $investment_amount->whereIn('merchant_user.user_id', $filter_investors);
        }
        if ($filter_merchants) {
            $investment_amount = $investment_amount->whereIn('merchants.id', $filter_merchants);
        }
        $investment_amount = $investment_amount->groupBy('merchant_user.user_id')->pluck(DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as total_investment'), 'merchant_user.user_id');
        $merchants = clone $merchants9;
        $merchants2 = clone $merchants;
        $array1 = implode(',', $array);
        $total = $merchants2->select(DB::raw('group_concat(distinct merchant_user.user_id) as user_ids_arr'),
        DB::raw('sum('.$merchant_day.'*((merchant_user.invest_rtr+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)),0))-(merchant_user.invest_rtr*(merchant_user.mgmnt_fee)/100+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)*(merchant_user.mgmnt_fee)/100),0))-(IF(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,0)))) as total_rtr'),
        DB::raw('(sum(merchant_user.amount)+sum(merchant_user.commission_amount)+sum(merchant_user.pre_paid)+sum(merchant_user.under_writing_fee)+sum(merchant_user.up_sell_commission)) as net_zero_sum'))->first();
        $users_arr = explode(',', $total->user_ids_arr);
        $overpayments = DB::table('carry_forwards')->join('merchants', 'merchants.id', 'carry_forwards.merchant_id')->join('users', 'users.id', 'carry_forwards.investor_id')->where('carry_forwards.type', 1)->whereIn('investor_id', $users_arr);
        if (! empty($filter_investors)) {
            $overpayments = $overpayments->whereIn('investor_id', $filter_investors);
        }
        if ($filter_merchants) {
            $overpayments = $overpayments->whereIn('merchant_id', $filter_merchants);
        }
        if ($active == 1) {
            $overpayments = $overpayments->where('users.active_status', 1);
        } elseif ($active == 2) {
            $overpayments = $overpayments->where('users.active_status', 0);
        }
        if (! empty($lenders)) {
            $overpayments = $overpayments->whereIn('merchants.lender_id', $lenders);
        }
        if ($from_date) {
            $overpayments = $overpayments->whereDate('date', '>=', $from_date);
        }
        if ($to_date) {
            $overpayments = $overpayments->whereDate('date', '<=', $to_date);
        }
        $overpayments = $overpayments->groupBy('investor_id')->pluck(DB::raw('sum(carry_forwards.amount) as overpayment'), 'carry_forwards.investor_id');
        $net_zero_sum = ! empty($total->net_zero_sum) ? ($total->net_zero_sum) : 0;
        $total_rtr = ! empty($total->total_rtr) ? ($total->total_rtr) : 0;
        $merchants = $merchants->select('users.id',
        'users.name as name',
        'old_factor_rate',
        'factor_rate',
        'sub_status_id',
        DB::raw('sum(merchant_user.mgmnt_fee) as old_mag_fee'),
        DB::raw('sum(merchant_user.amount + merchant_user.commission_amount +merchant_user.under_writing_fee + merchant_user.pre_paid+merchant_user.up_sell_commission) as invested_amount'),
        DB::raw('sum(merchant_user.invest_rtr *(merchant_user.mgmnt_fee)/100 +  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) *(merchant_user.mgmnt_fee)/100 ) , 0 ) ) as mangt_fee'),
        DB::raw('sum(merchant_user.invest_rtr + IF(old_factor_rate>factor_rate, ( merchant_user.amount * (old_factor_rate-factor_rate) ) , 0 ) ) as invest_rtr1'),
        DB::raw('(SELECT lag_time FROM users  WHERE merchants.lender_id=users.id) as lag_time'),
        DB::raw('sum( invest_rtr-((merchant_user.invest_rtr *(( IF (merchants.m_s_prepaid_status=0, 0, 0)+merchant_user.mgmnt_fee)/100 )))) as invest_rtr'),
        DB::raw('('.$merchant_day.'*(sum(merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission)-(sum(IF(sub_status_id IN('.$array1.'), (merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee), (IF((merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission)<(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee)'),
        DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission), (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ) )) ) ) ) ) ) as default_amount'),
        DB::raw('sum('.$merchant_day.'*((merchant_user.invest_rtr+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)),0))-(merchant_user.invest_rtr*(merchant_user.mgmnt_fee)/100+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)*(merchant_user.mgmnt_fee)/100),0))-(IF(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,0)))) as investor_rtr'),
        DB::raw('sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd_1'),
        DB::raw('sum((merchant_user.amount) + (merchant_user.commission_amount) + (merchant_user.pre_paid) + (merchant_user.under_writing_fee) ) as net_zero'),
        DB::raw('sum(((merchant_user.invest_rtr)-((merchant_user.invest_rtr*((merchant_user.mgmnt_fee)/100)))))-sum((merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee)) as collection_amount ')
        )->groupBy('users.id');
        $total_default_amount = array_sum(array_column($merchants->get()->toArray(), 'default_amount'));
        $rtr = $merchants9->sum('invest_rtr');

        return [
            'rtr'                  => $rtr,
            'invsestors_rtr'       => $invsestors_rtr,
            'investment_amount'    => $investment_amount,
            'overpayments'         => $overpayments,
            'merchants'            => $merchants,
            'total_default_amount' => $total_default_amount,
            'net_zero_sum'         => $net_zero_sum,
            'total_rtr'            => $total_rtr,
        ];
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function merchantDefaulRate($sDate, $eDate, $investors, $isos, $company, $sub_status, $funded_date, $days, $investor_type,$velocity_owned = false, $search_key = null)
    {
        $default_date = ! empty($eDate) ? $eDate : now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $array = [4,22];
        $array1 = implode(',', $array);
        $SpecialAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE, User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id', 'users.id')->toArray();
        $data = Merchant::select('merchants_details.agent_name','merchants.id',
        'merchants.name',
        'merchants.date_funded',
        'merchants.last_status_updated_date',
        'merchants.creator_id',
        'merchants.created_at',
        DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid) as invested_amount'),
        DB::raw('sum(merchant_user.invest_rtr * (100 - merchant_user.mgmnt_fee)/100 ) as rtr_after_fee'),
        DB::raw('SUM(merchant_user.actual_paid_participant_ishare - paid_mgmnt_fee ) as ctd'),
        DB::raw('sum('.$merchant_day.'*((merchant_user.invest_rtr+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)),0))-(merchant_user.invest_rtr*(merchant_user.mgmnt_fee)/100+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)*(merchant_user.mgmnt_fee)/100),0))-(IF(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,0)))) as investor_rtr'),
            DB::raw('( '.$merchant_day.' * (
				sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
				-
				( sum( IF(sub_status_id IN ('.$array1.'),
				(merchant_user.actual_paid_participant_ishare - (IF(merchant_user.paid_mgmnt_fee,merchant_user.paid_mgmnt_fee,0)) ),
				( IF((merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
				<
				(merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ),
				(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission),
				(merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee )
				))
				)
				)
				)
				)
				)
				as default_amount')
            );
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $data = $data->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->leftjoin('merchants_details','merchants_details.merchant_id','merchants.id')->join('users', 'users.id', 'merchant_user.user_id')->whereNotIn('users.company',$disabled_companies);
    
        if (! empty($investors)) {
            $data = $data->whereIn('merchant_user.user_id', $investors);
        }
        if (! empty($isos)) {
            $data = $data->whereIn('merchants_details.agent_name', $isos);
        }
        if ($SpecialAccount) {
            $data = $data->whereNotIn('merchant_user.user_id', $SpecialAccount);
        }
        if (! empty($search_key)) {
            $data = $data->where(function ($query) use ($search_key) {
                $query->where('merchants.name', 'like', '%'.$search_key.'%');
                $query->orWhere('merchants.id', 'like', '%'.$search_key.'%');
                $query->orWhere('merchants_details.agent_name', 'like', '%'.$search_key.'%');
            });
        }
        if (! empty($company)) {
            $data = $data->where('users.company', $company);
        }
        if($velocity_owned){
            $data = $data->where('users.velocity_owned', 1);
        }
        if (! empty($investor_type)) {
            $data = $data->whereIn('users.investor_type', $investor_type);
        }
        if (! empty($sub_status)) {
            $data = $data->whereIn('merchants.sub_status_id', $sub_status);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $data = $data->where('users.company', $userId);
            }
        }
        if ($funded_date == 1) {
            $date_filter_table = 'merchants.date_funded';
        } else {
            $date_filter_table = 'merchants.last_status_updated_date';
        }
        if ($sDate) {
            $data = $data->whereDate($date_filter_table, '>=', $sDate);
        }
        if ($eDate) {
            $data = $data->whereDate($date_filter_table, '<=', $eDate);
        }
        if ($days != null && $days == 0) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=60");
        }
        if ($days == 61) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <= 90");
        }
        if ($days == 91) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=120");
        }
        if ($days == 121) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY))  <=150");
        }
        if ($days == 150) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >".$days);
        }
        $data = $data->groupBy('merchants.id');

        return $data;
    }

    public function merchantDefaulRateForInvestor($sDate, $eDate, $investors, $company, $sub_status, $funded_date, $days, $investor_type, $search_key = null)
    {
        $default_date = ! empty($eDate) ? $eDate : now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $array = [4,22];
        $array1 = implode(',', $array);
        $data = Merchant::select('merchants.date_funded',DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid) as invested_amount,
          sum(merchant_user.invest_rtr * (100 - merchant_user.mgmnt_fee)/100 ) as rtr_after_fee,
            SUM(merchant_user.actual_paid_participant_ishare - paid_mgmnt_fee ) as ctd,

            merchants.id,

            sum(

  '.$merchant_day.'

*


    (

            merchant_user.invest_rtr +  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) ) , 0 )

             -

           merchant_user.invest_rtr *(merchant_user.mgmnt_fee)/100
            +  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) *(merchant_user.mgmnt_fee)/100  ) , 0 )

            -
               (IF(
              merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee,

              merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee,0


              ))


              )

            )

           as investor_rtr,


          (


'.$merchant_day.'

*


          (


                          sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                                    -

                             ( sum( IF(sub_status_id IN ('.$array1.'),

 (merchant_user.actual_paid_participant_ishare - (IF(merchant_user.paid_mgmnt_fee,merchant_user.paid_mgmnt_fee,0)) ),


                               ( IF((merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                    <
                    (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ),

                    (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission),


                    (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee )


                ))


                )


                 )

               )

               )

            )
            as default_amount,

           IF(display_value="mid",merchants.id,merchants.name) as name,
            merchants.last_status_updated_date'));
        $data = $data->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->join('users', 'users.id', 'merchant_user.user_id');
        if (! empty($investors)) {
            $data = $data->whereIn('merchant_user.user_id', $investors);
        }
        if (! empty($search_key)) {
            $data = $data->where(function ($query) use ($search_key) {
                $query->where('merchants.name', 'like', '%'.$search_key.'%');
            });
        }
        if (! empty($company)) {
            $data = $data->where('users.company', $company);
        }
        if (! empty($investor_type)) {
            $data = $data->whereIn('users.investor_type', $investor_type);
        }
        if (! empty($sub_status)) {
            $data = $data->whereIn('merchants.sub_status_id', $sub_status);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $data = $data->where('users.company', $userId);
            }
        }
        if ($funded_date == 1) {
            $date_filter_table = 'merchants.date_funded';
        } else {
            $date_filter_table = 'merchants.last_status_updated_date';
        }
        if ($sDate) {
            $data = $data->whereDate($date_filter_table, '>=', $sDate);
        }
        if ($eDate) {
            $data = $data->whereDate($date_filter_table, '<=', $eDate);
        }
        if ($days != null && $days == 0) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=60");
        }
        if ($days == 61) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <= 90");
        }
        if ($days == 91) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=120");
        }
        if ($days == 121) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=".$days);
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY))  <=150");
        }
        if ($days == 150) {
            $data = $data->whereRaw("DATEDIFF('".$default_date."',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >".$days);
        }
        $data = $data->groupBy('merchants.id');

        return $data;
    }

    public function deleteAllTerms($merchant_id)
    {
        $merchant = Merchant::select('id', 'name', 'pmnts', 'ach_pull')->where('id', $merchant_id)->first();
        $investors_count = MerchantUser::where('merchant_id', $merchant_id)->count();
        $status = false;
        if ($investors_count == 0 && $merchant) {
            $term_payments_delete = TermPaymentDate::where('merchant_id', $merchant_id)->each(function($row) {
                $row->delete();
            });
            $terms = MerchantPaymentTerm::where('merchant_id', $merchant_id)->each(function($row) {
                $row->delete();
            });
            $status = true;
        }

        return $status;
    }

    public function createTerms($merchant)
    {
        $first_payment_date = $merchant->first_payment;
        if ($first_payment_date) {
            $start_date = $first_payment_date;
        } else {
            $invested_date = MerchantUser::where('merchant_id', $merchant->id)->min('created_at');
            if ($invested_date) {
                $start_date = Carbon::parse($invested_date);
            } else {
                $start_date = Carbon::now();
            }
            if ($merchant->advance_type == 'weekly_ach') {
                $start_date = $start_date->addWeek();
            } elseif ($merchant->advance_type == 'biweekly_ach') {
                $start_date = $start_date->addWeeks(2);
            } elseif ($merchant->advance_type == 'monthly_ach') {
                $start_date = $start_date->addMonth();
            } else {
                $start_date = $start_date->addDay();
            }
            $start_date = PayCalc::getWorkingDay($start_date->toDateString());
        }
        $advance_type = $merchant->advance_type;
        $terms = $merchant->pmnts;
        $payment_amount = $merchant->payment_amount;
        $mid = $merchant->id;
        $end_date = $this->getEndDate($start_date, $advance_type, $terms);
        $payment_term = MerchantPaymentTerm::create(['merchant_id' => $mid, 'advance_type' => $advance_type, 'pmnts' => $terms, 'payment_amount' => $payment_amount, 'actual_payment_left' => $terms, 'start_at' => $start_date, 'end_at' => $end_date, 'status' => 0, 'created_by' => Auth::user() ? Auth::user()->name : '']);
        $term_dates = $this->storeTermdates($payment_term);
        $paid_payments = $payment_term->payments()->where('status', '>', 0)->count();
        if ($paid_payments) {
            $payment_term->update(['actual_payment_left' => $terms - $paid_payments]);
        }
        $latest_term = $merchant->paymentTerms()->orderByDesc('end_at')->first();
        if ($merchant->payment_end_date < $latest_term->end_at) {
            $merchant->payment_end_date = $latest_term->end_at;
            $merchant->update();
        }

        return $payment_term;
    }

    private function updateTerms($merchant)
    {
        $mid = $merchant->id;
        $payment_term = MerchantPaymentTerm::where('merchant_id', $mid)->latest()->first();
        $first_payment_date = $merchant->first_payment;
        if ($first_payment_date) {
            $start_date = $first_payment_date;
        } else {
            $lag_time = $merchant->lendor->lag_time ?? 0;
            $funded_date = Carbon::parse($merchant->date_funded);
            $start_date = $funded_date->addDays($lag_time);
            $start_date = PayCalc::getWorkingDay($start_date->toDateString());
        }
        $advance_type = $merchant->advance_type;
        $terms = $merchant->pmnts;
        $payment_amount = $merchant->payment_amount;
        $end_date = $this->getEndDate($start_date, $advance_type, $terms);
        if ($payment_term) {
            $payment_term->update(['merchant_id' => $mid, 'advance_type' => $advance_type, 'pmnts' => $terms, 'payment_amount' => $payment_amount, 'actual_payment_left' => $terms, 'start_at' => $start_date, 'end_at' => $end_date, 'status' => 0, 'updated_by' => Auth::user()->name]);
        }
        $payment_term3 = MerchantPaymentTerm::where('merchant_id', $mid)->latest()->first();
        if ($payment_term3) {
            $term_dates_old = $payment_term3->payments;
            $payment_term3->payments()->delete();
            $term_dates = $this->storeTermdates($payment_term3);
        }
        $latest_term = $merchant->paymentTerms()->orderByDesc('end_at')->first();
        if ($merchant->payment_end_date < $latest_term->end_at) {
            $merchant->payment_end_date = $latest_term->end_at;
            $merchant->update();
        }

        return $payment_term3;
    }

    public function getEndDate($start_date, $advance_type, $terms)
    {
        $end_date = new Carbon($start_date);
        if ($advance_type == 'weekly_ach') {
            $end_date = $end_date->addWeeks($terms - 1);
        } elseif ($advance_type == 'biweekly_ach') {
            $end_date = $end_date->addWeeks(($terms - 1) * 2);
        } elseif ($advance_type == 'monthly_ach') {
            $end_date = $end_date->addMonths($terms - 1);
        } else {
            for ($i = 1; $i < $terms; $i++) {
                $end_date = $end_date->addDay();
                $end_date = new Carbon(PayCalc::getWorkingDay($end_date));
            }
        }
        $end_date = PayCalc::getWorkingDay($end_date);

        return $end_date;
    }

    public function getTerms($start_date, $end_date, $advance_type)
    {
        if ($advance_type == 'weekly_ach') {
            $terms = PayCalc::calculateWeeks($start_date, $end_date);
        } elseif ($advance_type == 'biweekly_ach') {
            $terms = PayCalc::calculateBiWeeks($start_date, $end_date);
        } elseif ($advance_type == 'monthly_ach') {
            $terms = PayCalc::calculateMonths($start_date, $end_date);
        } else {
            $terms = PayCalc::calculateWorkingDaysCount($start_date, $end_date);
        }

        return $terms;
    }

    public function getWeekPaymentCount($start_date, $end_date, $term)
    {
        $term_start = Carbon::parse($term->start_at);
        $term_end = Carbon::parse($term->end_at);
        $from = Carbon::parse($start_date);
        $to = Carbon::parse($end_date);
        $count = 0;
        for ($term_start; $term_start < $term_end; $term_start->addWeek()) {
            if ($from <= $term_start && $to >= $term_start) {
                $count++;
            }
        }

        return $count;
    }

    public function storeTermdates($term)
    {
        $advance_type = $term->advance_type;
        $term_id = $term->id;
        $merchant_id = $term->merchant_id;
        $payment_amount = $term->payment_amount;
        $pmnts = $term->pmnts;
        $start_at = $term->start_at;
        $end_at = $term->end_at;
        $term_dates = [];
        $current_term_dates_count = $term->payments()->where('status', '>=', 0)->count();
        if ($current_term_dates_count == 0) {
            if ($advance_type == 'weekly_ach') {
                $i = 0;
                $j = $pmnts;
                $dates = [];
                for ($i; $i < $j; $i++) {
                    $date = Carbon::parse($start_at);
                    $date = $date->addWeeks($i);
                    $date = PayCalc::getWorkingDay($date->toDateString());
                    if ($date <= $end_at) {
                        $dates[] = $date;
                    }
                }
            } elseif ($advance_type == 'biweekly_ach') {
                $i = 0;
                $j = $pmnts;
                $dates = [];
                for ($i; $i < $j; $i++) {
                    $date = Carbon::parse($start_at);
                    $date = $date->addWeeks($i * 2);
                    $date = PayCalc::getWorkingDay($date->toDateString());
                    if ($date <= $end_at) {
                        $dates[] = $date;
                    }
                }
            } elseif ($advance_type == 'monthly_ach') {
                $i = 0;
                $j = $pmnts;
                $dates = [];
                for ($i; $i < $j; $i++) {
                    $date = Carbon::parse($start_at);
                    $date = $date->addMonths($i);
                    $date = PayCalc::getWorkingDay($date->toDateString());
                    if ($date <= $end_at) {
                        $dates[] = $date;
                    }
                }
            } else {
                $dates = PayCalc::getWorkingDays($start_at, $end_at);
            }
            foreach ($dates as $date) {
                $status = 0;
                $paid_payments = ParticipentPayment::where('merchant_id', $merchant_id)->where('payment_date', $date)->where('mode_of_payment', 1)->orderBy('payment')->get();
                if ($paid_payments) {
                    foreach ($paid_payments as $p) {
                        if ($p->rcode > 0) {
                            $status = TermPaymentDate::ACHCancelled;
                        }
                        if ($p->payment > 0) {
                            $status = TermPaymentDate::ACHPaid;
                        }
                    }
                }
                $term_date = TermPaymentDate::create(['merchant_id' => $merchant_id, 'term_id' => $term_id, 'payment_date' => $date, 'payment_amount' => $payment_amount, 'status' => $status]);
                $term_dates[] = $term_date;
            }

            return $term_dates;
        }

        return false;
    }

    public function updateTermdates($term, $payment_started = 0)
    {
        $advance_type = $term->advance_type;
        $term_id = $term->id;
        $merchant_id = $term->merchant_id;
        $payment_amount = $term->payment_amount;
        $pmnts = $term->pmnts;
        $start_at = $term->start_at;
        $end_at = $term->end_at;
        $term_dates = [];
        $existing_dates = $term->payments()->pluck('payment_date');
        if ($advance_type == 'weekly_ach') {
            $i = 0;
            $j = $pmnts;
            $dates = [];
            for ($i; $i < $j; $i++) {
                $date = Carbon::parse($start_at);
                $date = $date->addWeeks($i);
                $date = PayCalc::getWorkingDay($date->toDateString());
                if ($date <= $end_at) {
                    $dates[] = $date;
                }
            }
        } elseif ($advance_type == 'biweekly_ach') {
            $i = 0;
            $j = $pmnts;
            $dates = [];
            for ($i; $i < $j; $i++) {
                $date = Carbon::parse($start_at);
                $date = $date->addWeeks($i * 2);
                $date = PayCalc::getWorkingDay($date->toDateString());
                if ($date <= $end_at) {
                    $dates[] = $date;
                }
            }
        } elseif ($advance_type == 'monthly_ach') {
            $i = 0;
            $j = $pmnts;
            $dates = [];
            for ($i; $i < $j; $i++) {
                $date = Carbon::parse($start_at);
                $date = $date->addMonths($i);
                $date = PayCalc::getWorkingDay($date->toDateString());
                if ($date <= $end_at) {
                    $dates[] = $date;
                }
            }
        } else {
            $dates = PayCalc::getWorkingDays($start_at, $end_at);
        }
        $update_ex_date = [];
        foreach ($existing_dates as $ex_date) {
            if (in_array($ex_date, $dates)) {
                if (($key = array_search($ex_date, $dates)) !== false) {
                    unset($dates[$key]);
                    $exisiting_payment_dates[] = $key;
                    if ($payment_started == 0) {
                        $update_ex_date[] = $term->payments()->where('payment_date', $ex_date)->update(['payment_amount' => $payment_amount]);
                    }
                }
            } else {
                $delete_ex_date = $term->payments()->where('payment_date', $ex_date)->delete();
            }
        }
        foreach ($dates as $date) {
            $status = 0;
            $paid_payments = ParticipentPayment::where('merchant_id', $merchant_id)->where('payment_date', $date)->where('mode_of_payment', 1)->orderBy('payment')->get();
            if ($paid_payments) {
                foreach ($paid_payments as $p) {
                    if ($p->rcode > 0) {
                        $status = -1;
                    }
                    if ($p->payment > 0) {
                        $status = 1;
                    }
                }
            }
            $term_date = TermPaymentDate::create(['merchant_id' => $merchant_id, 'term_id' => $term_id, 'payment_date' => $date, 'payment_amount' => $payment_amount, 'status' => $status]);
            $term_dates[] = $term_date;
        }

        return $term_dates;
    }

    public function searchForProfitCarryForward($startDate, $endDate, $investors, $merchants, $type)
    {
        $report = CarryForward::with(['merchant']);
        if ($type && is_array($type)) {
            $report = $report->whereIn('carry_forwards.type', $type);
        }
        if ($investors && is_array($investors)) {
            $report = $report->whereIn('carry_forwards.investor_id', $investors);
        }
        if ($merchants && is_array($merchants)) {
            $report = $report->whereIn('carry_forwards.merchant_id', $merchants);
        }
        if ($startDate != 0) {
            $startDate = $startDate.' 00:00:00';
            $report = $report->whereDate('carry_forwards.date', '>=', $startDate);
        }
        if ($endDate != 0) {
            $endDate = $endDate.' 23:23:59';
            $report = $report->whereDate('carry_forwards.date', '<=', $endDate);
        }
        $data = $report->orderByDesc('carry_forwards.date')->with('merchant');

        return $data;
    }

    public function resumePayment($merchant, $resumed_by)
    {
        $resume_id = $merchant->payment_pause_id;
        $merchant->payment_pause_id = null;
        $merchant->update();
        $payment_resume = PaymentPause::find($resume_id);
        $payment_resume->resumed_by = $resumed_by;
        $payment_resume->resumed_at = Carbon::now();
        $payment_resume->update();
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $title = 'Payment Resumed';
        $msg['title'] = $title;
        $msg['content']['data'] = $payment_resume;
        $msg['merchant_name'] = $merchant->name;
        $msg['merchant_id'] = $merchant->id;
        $msg['to_mail'] = $emailArray;
        $msg['status'] = 'payment_resumed';
        $msg['subject'] = $title;
        $msg['unqID'] = unqID();
        $msg['resumed_by'] = $payment_resume->resumed_by;
        $msg['resumed_at'] = \FFM::datetime($payment_resume->resumed_at);
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'PYRS'], ['enable', '=', 1],
            ])->first();
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

        return $payment_resume;
    }

    public function getAgentFeeDetails($merchants = [], $from_date = null, $to_date = null)
    {
        $agent_fee_accounts = $this->role->allAgentFeeAccount()->pluck('id')->toArray();
        $details = $this->table->join('payment_investors', 'payment_investors.merchant_id', 'merchants.id')->join('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id')->where('participant_share', '<>', 0);
        $details = $details->whereIn('payment_investors.user_id', $agent_fee_accounts);
        if (! empty($merchants)) {
            $details = $details->whereIn('payment_investors.merchant_id', $merchants);
        }
        if (! empty($from_date)) {
            $details = $details->where('payment_date', '>=', $from_date);
        }
        if (! empty($to_date)) {
            $details = $details->where('payment_date', '<=', $to_date);
        }
        $details = $details->orderBy('payment_date', 'DESC');
        $data = clone $details;
        $table['total'] = $data->select(DB::raw('sum(payment) as t_payment,sum(participant_share) as t_agent_fee'))->first();
        $table['data'] = $details->select('name', 'payment_investors.merchant_id', 'payment_date', 'payment_investors.participant_share', 'payment', 'payment_investors.created_at', 'participent_payments.creator_id');

        return $table;
    }
}

function modelQuerySqlWithBinding($sql, $bindings)
{
    $sql_chunks = explode('?', $sql);
    $result = '';
    if (count($bindings) > 0 and count($sql_chunks) > 0) {
        foreach ($sql_chunks as $key => $sql_chunk) {
            if (isset($bindings[$key])) {
                $result .= $sql_chunk.'"'.$bindings[$key].'"';
            } else {
                $result .= $sql_chunk;
            }
        }
    } else {
        $result = $sql;
    }

    return $result;
}

function modelQuerySql($query)
{
    $sql = $query->toSql();
    $bindings = $query->getBindings();

    return modelQuerySqlWithBinding($sql, $bindings);
}
