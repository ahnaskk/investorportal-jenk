<?php

/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 3/11/17
 * Time: 10:32 AM.
 */

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\IRoleRepository;
use App\Models\Views\MerchantUserView;
use App\Module;
use App\Settings;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PayCalc;
use Spatie\Permission\Models\Role;

class RoleRepository implements IRoleRepository
{
    protected $table;

    public function __construct()
    {
    }

    public function allUsers()
    {
        return $return = (new Role())->whereIn('roles.name', ['admin', 'accounts', 'wire ach', 'editor'])
            ->join('user_has_roles', 'user_has_roles.role_id', 'roles.id')
            ->join('users', 'users.id', 'user_has_roles.model_id')->pluck('users.name', 'users.id');
    }

    public function lenderReport($lender_list = null, $industry = null, $merchants = null, $search_key = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $this->allInvestors();
            $subadmininvestor = $investor->where('creator_id', $userId);
            foreach ($subadmininvestor as $key1=> $value) {
                $subinvestors[] = $value->id;
            }
        }
        $default_date = now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        $lenders = (new Role())->where('roles.name', 'lender')->join('user_has_roles', 'user_has_roles.role_id', 'roles.id')->join('users', 'users.id', 'user_has_roles.model_id')->join('merchants', 'merchants.lender_id', 'users.id')
      ->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
      ->whereIn('merchant_user.status', [1, 3])
      ->where('users.active_status', 1)
      ->where('user_has_roles.role_id', 4)
      ->select('merchants.lender_id as lender_id', DB::raw('sum(merchant_user.amount) + sum(merchant_user.pre_paid)+ sum(merchant_user.commission_amount) + sum(merchant_user.under_writing_fee) as invested_amount'),
      DB::raw('sum(actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd_pp'),
      DB::raw('group_concat(distinct merchants.lender_id) as user_ids_arr'),
      DB::raw(' ( '.$merchant_day.' * ( sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) - sum(merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ) ) ) as default_amount'),
      DB::raw('sum( ( actual_paid_participant_ishare-paid_mgmnt_fee ) - ( ( actual_paid_participant_ishare-paid_mgmnt_fee) * (merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount) / (merchant_user.invest_rtr- (merchant_user.mgmnt_fee/100)*merchant_user.invest_rtr) )) as ctd_p '),
      'users.name as lender_name', 'users.id')
      ->join('users as users_investor', 'users_investor.id', 'merchant_user.user_id')/*->where('merchants.sub_status_id',4)*/;
        //->join('payment_investors','payment_investors.user_id','users_investor.id');
        //  $permission ='';
        if (empty($permission)) {
            $lenders = $lenders->where('users_investor.company', $userId);
        }
        if (! empty($industry)) {
            $lenders = $lenders->whereIn('merchants.industry_id', $industry);
        }
        if ($merchants) {
            $lenders = $lenders->whereIn('merchants.id', $merchants);
        }
        if ($search_key != null) {
            $lenders = $lenders->where(function ($query) use ($search_key) {
                $query->Where('users.name', 'like', '%'.$search_key.'%');
            });
        }
        if ($lender_list) {
            $lenders = $lenders->whereIn('merchants.lender_id', $lender_list);
        }
        $total = clone $lenders;
        $total = $total->first();
        $users_arr = explode(',', $total->user_ids_arr);
        $profit = DB::table('payment_investors')->join('merchants', 'payment_investors.merchant_id', 'merchants.id')->leftjoin('users as u1', 'u1.id', 'payment_investors.user_id')
        ->leftjoin('users as u2', 'u2.id', 'merchants.lender_id');
        if (! empty($lender_list)) {
            $profit = $profit->whereIn('merchants.lender_id', $lender_list);
        }
        if (! empty($industry)) {
            $profit = $profit->whereIn('merchants.industry_id', $industry);
        }
        if (! empty($merchants)) {
            $profit = $profit->whereIn('merchants.id', $merchants);
        }
        if ($search_key != null) {
            $profit = $profit->where(function ($query) use ($search_key) {
                $query->Where('u2.name', 'like', '%'.$search_key.'%');
            });
        }
        if (empty($permission)) {
            $profit = $profit->where('u1.company', $userId);
        }
        $profit = $profit->groupBy('merchants.lender_id')
      ->pluck(DB::raw('sum(payment_investors.profit) as profit'), 'merchants.lender_id')->toArray();
        $overpayments = DB::table('payment_investors')->join('merchants', 'merchants.id', 'payment_investors.merchant_id')->join('users', 'users.id', 'merchants.lender_id')
      ->whereIn('merchants.lender_id', $users_arr);
        //join('participent_payments', 'payment_investors.merchant_id', 'participent_payments.merchant_id')
        if (! empty($lender_list)) {
            $overpayments = $overpayments->whereIn('merchants.lender_id', $lender_list);
        }
        if (! empty($merchants)) {
            $overpayments = $overpayments->whereIn('merchants.id', $merchants);
        }
        if (! empty($industry)) {
            $overpayments = $overpayments->whereIn('merchants.industry_id', $industry);
        }
        // if (empty($permission)) {
        //     $overpayments = $overpayments->where('users.company', $userId);
        // }
        $overpayments = $overpayments->groupBy('merchants.lender_id')
      ->pluck(DB::raw('sum(payment_investors.actual_overpayment) as overpayment'), 'merchants.lender_id')->toArray();
        $CarryOverpayments = DB::table('carry_forwards')
      ->join('merchants', 'merchants.id', 'carry_forwards.merchant_id')
      ->join('users', 'users.id', 'carry_forwards.investor_id')
      ->whereIn('merchants.lender_id', $users_arr)
      ->where('carry_forwards.type', 1);
        if (! empty($lender_list)) {
            $CarryOverpayments = $CarryOverpayments->whereIn('merchants.lender_id', $lender_list);
        }
        if (! empty($merchants)) {
            $CarryOverpayments = $CarryOverpayments->whereIn('merchants.id', $merchants);
        }
        if (! empty($industry)) {
            $CarryOverpayments = $CarryOverpayments->whereIn('merchants.industry_id', $industry);
        }
        $CarryOverpayments = $CarryOverpayments->groupBy('merchants.lender_id')
      ->pluck(DB::raw('sum(carry_forwards.amount) as overpayment'), 'merchants.lender_id')->toArray();
        $result['overpayments'] = $overpayments;
        $result['CarryOverpayments'] = $CarryOverpayments;
        $result['profit'] = $profit;
        $result['lenders'] = $lenders;

        return $result;
    }

    public function allInvestors($investor_type = null, $velocity = null, $subadmin = null)
    {

        /* Todo filter based on investor_type */
        $return = (new Role())->whereName('investor')->first()->users->where('company_status',1);

        if (\Auth::user()) {
            $userId = Auth::user()->id;
            //If use logged in
            $investor_admin = $this->allSubAdmin()->pluck('id');
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;

            if (empty($permission)) {
                if (Auth::user()->hasRole(['company'])) {
                    $return = $return->where('company', $userId);
                } else {
                    $return = $return->where('creator_id', $userId);
                }
            }

            if ($investor_type) {
                $return = $return->where('investor_type', $investor_type);
            }

            if ($subadmin) {
                $return = $return->where('creator_id', $subadmin);
            }
        }

        return $return;
    }

    public function allBranchManager()
    {
        $userId = Auth::user()->id;
        $return = (new Role())->whereName('branch manager')->first()->users;
        return $return;
    }

    public function allCollectionUser()
    {
        $return = (new Role())->whereName('collection user')->first()->users;
        return $return;
    }

    public function allLenders()
    {
        $return = (new Role())->whereName('lender')->first()->users;
        $return = $return->where('active_status', 1);
        return $return;
    }

    public function enabledDisabledLenders()
    {
        $return = (new Role())->whereName('lender')->first()->users;
        return $return;
    }

    public function allEditors()
    {
        $return = (new Role())->whereName('editor')->first()->users;
        return $return;
    }

    public function allViewers()
    {
        $return = (new Role())->whereName('viewer')->first()->users;
        return $return;
    }

    public function allLendors()
    {
        return (new Role())->whereName('admin')->first()->users;
    }

    public function deptInvestorsLiquidity()
    {
        $investor = (new Role())->whereName('investor')->first()->users();
        $investor = $investor->where('investor_type', 1)->where('active_status', 1);
        $investors = $investor->with('userDetails');

        return $investors->get();
    }

    public function allInvestorsWithLiquidityModified($startDate, $endDate, $subadmin, $active, $company, $liquidity, $investor_id = null)
    {
        $userId = Auth::user()->id;
        $hide = Settings::value('hide');
        $data = MerchantUserView::select(
            'investor_id',
            'Investor',
            DB::raw('(SELECT liquidity          FROM user_details WHERE merchant_user_views.investor_id = user_details.user_id) liquidity'),
            DB::raw('(SELECT liquidity_adjuster FROM user_details WHERE merchant_user_views.investor_id = user_details.user_id) liquidity_adjuster')
        );
        if ($company != '') {
            $data = $data->where('company', $company);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $data = $data->where('company', $userId);
            } else {
                $data = $data->where('creator_id', $userId);
            }
        }
        if ($hide == 1) {
            $data = $data->where('active_status', 1);
        }
        if ($active == 1) {
            $data = $data->where('active_status', 1);
        } elseif ($active == 2) {
            $data = $data->where('active_status', 0);
        }
        $subadmin = $this->allSubAdmin();
        if ($subadmin == 'subadmin') {
            $data = $data->whereIn('creator_id', $subadmin);
        }
        $data = $data->groupBy('investor_id');
        if ($investor_id) {
            $data = $data->where('investor_id', $investor_id);
        }
        $data = $data->get();
        $lists = [];
        foreach ($data as $key => $value) {
            $single['id'] = $value->investor_id;
            $single['name'] = $value->Investor;
            $single['ctd'] = $value->getCTDProcedure($startDate, $endDate);
            $single['credit_amount'] = $value->getCreditProcedure($startDate, $endDate);
            $single['commission_amount'] = $value->getCommissionProcedure($startDate, $endDate);
            $single['total_funded'] = $value->getFundedProcedure($startDate, $endDate);
            $single['pre_paid'] = $value->getPrePaidProcedure($startDate, $endDate);
            $single['under_writing_fee'] = $value->getUnderWritingFeeProcedure($startDate, $endDate);
            $single['liquidity'] = $value->liquidity;
            $single['liquidity_adjuster'] = $value->liquidity_adjuster;
            $lists[] = $single;
        }

        return $lists;
    }

    public function allInvestorsWithLiquidity($startDate, $endDate, $subadmin, $active, $company,$velocity_owned)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $hide = Settings::value('hide');
        $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $data1 = User::select('users.id',
        'users.name',
        DB::raw('(SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity'),
        DB::raw('(SELECT liquidity_adjuster FROM user_details WHERE users.id = user_details.user_id) liquidity_adjuster'),
        )
        ->join('user_has_roles', function ($join) {
            $join->on('users.id', '=', 'user_has_roles.model_id');
            $join->whereIn('user_has_roles.role_id', [2, 13]);
        })
        ->whereNotIn('users.company',$disabled_companies)
        ->withCount([
            'investorTransactions AS credit_amount' => function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw('SUM(investor_transactions.amount) as credit'));
                $query->where('investor_transactions.status', 1);
                if ($startDate) {
                    $query->where('investor_transactions.date', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('investor_transactions.date', '<=', $endDate);
                }
            },
        ])
        ->withCount([
            'investmentData AS total_funded' => function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw('SUM(merchant_user.amount) as total_funded'))
                ->whereIn('merchant_user.status', [1, 3]);
                $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                    $query1->where('active_status', '=', 1); // whre no default merchants.
                    if ($startDate) {
                        $query1->where('merchants.date_funded', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query1->where('merchants.date_funded', '<=', $endDate);
                    }
                });
            },
        ])
        ->withCount([
            'investmentData AS commission_amount' => function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw('SUM(merchant_user.commission_amount) as commission_amount'))
                ->whereIn('merchant_user.status', [1, 3]);
                $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                    $query1->where('active_status', '=', 1); // whre no default merchants.
                    if ($startDate) {
                        $query1->where('merchants.date_funded', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query1->where('merchants.date_funded', '<=', $endDate);
                    }
                });
            },
        ])
        ->withCount([
            'investmentData AS under_writing_fee' => function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw('SUM(merchant_user.under_writing_fee) as under_writing_fee'))
                ->whereIn('merchant_user.status', [1, 3]);
                $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                    $query1->where('active_status', '=', 1); // whre no default merchants.
                    if ($startDate) {
                        $query1->where('merchants.date_funded', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query1->where('merchants.date_funded', '<=', $endDate);
                    }
                });
            },
        ])
        ->withCount([
            'investmentData AS pre_paid' => function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw('SUM(merchant_user.pre_paid) as pre_paid'))
                ->whereIn('merchant_user.status', [1, 3]);
                $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                    $query1->where('active_status', '=', 1); // whre no default merchants.
                    if ($startDate) {
                        $query1->where('merchants.date_funded', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query1->where('merchants.date_funded', '<=', $endDate);
                    }
                });
            },
        ])
        ->withCount([
            'investmentData AS rtr' => function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw('SUM(merchant_user.invest_rtr- (merchant_user.mgmnt_fee/100)*merchant_user.invest_rtr) as invest_rtr'))
                ->whereIn('merchant_user.status', [1, 3]);
                $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                    $query1->where('active_status', '=', 1); // whre no default merchants.
                    if ($startDate) {
                        $query1->where('merchants.date_funded', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query1->where('merchants.date_funded', '<=', $endDate);
                    }
                });
            },
        ])
        ->withCount([
            'investmentData AS ctd' => function ($query) use ($startDate, $endDate) {
                // $query->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id');
                // $query->join('payment_investors', function($join) {
                //     $join->on('payment_investors.user_id'       , '=', 'merchant_user.user_id');
                //     $join->where('payment_investors.merchant_id', '=', 'merchant_user.merchant_id');
                // });
                $query->select(DB::raw('sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd'));
                $query->whereIn('merchant_user.status', [1, 3]);
                $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                    $query1->where('active_status', '=', 1); // whre no default merchants.
                    if ($startDate) {
                        $query1->where('merchants.date_funded', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query1->where('merchants.date_funded', '<=', $endDate);
                    }
                });
            },
        ]);
        // if ($investors && is_array($investors)) {
        //     $data1 = $data1->whereIn('users.id', $investors);
        // }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $data1 = $data1->where('company', $userId);
            } else {
                $data1 = $data1->where('creator_id', $userId);
            }
        }
        if ($hide == 1) {
            $data1 = $data1->where('active_status', 1);
        }
        //$data1 = $data1->where('active_status', 1);
        if ($active == 1) {
            $data1 = $data1->where('active_status', 1);
        } elseif ($active == 2) {
            $data1 = $data1->where('active_status', 0);
        }
        if ($company != '') {
            $data1 = $data1->where('company', $company);
        }
        if($velocity_owned){
            $data1 = $data1->where('velocity_owned', 1);
        }
        
        $subadmin = $this->allSubAdmin();
        if ($subadmin == 'subadmin') {
            $data1 = $data1->whereIn('creator_id', $subadmin);
        }
        // $data = $data1->where('users.investor_type', 2);
        $data = $data1;

        return $data;
    }

    public function companyWiseInvestorsLiquidity($companies, $investor_ids)
    {
        $investor  = new User;
        $investor  = $investor->whereIn('company', $companies);
        $investor  = $investor->whereIn('users.id', $investor_ids);
        // $investor  = $investor->where('liquidity_exclude', 0);
        // $investor  = $investor->where('liquidity','>',0);
        $investor  = $investor->leftJoin('user_details', 'users.id', 'user_details.user_id');
        $investor  = $investor->select(DB::raw('sum(liquidity) as liquidity'), 'company');
        $investors = $investor->groupBy('company');

        return $investors->get()->toArray();
    }

    public function allInvestorsLiquidity1($creator_id = '', $order_by = '', $greater_than = '', $exception = '')
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $hide = Settings::value('hide');

        $investor = (new Role())->whereName('investor')->first()->users();
        if (empty($permission)) {
            $investor = $investor->where('creator_id', $userId);
        }

        $investor = $investor->where('active_status', 1);
        if ($exception) {
            $investor = $investor->where('id', '!=', $exception);
        }

        if ($hide == 1) {
            $investor = $investor->where('active_status', 1);
        }

        $investors = $investor->with('userDetails');
        if ($greater_than !== '') {
            $investors = $investors->whereHas('userDetails', function ($investor) use ($greater_than) {
                $investor->where('liquidity', '>', $greater_than);
            });
        }

        return $investors;
    }

    public function allInvestorsLiquidity($creator_id = '', $order_by = '', $greater_than = '', $exception = '',$company = '')
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $hide = Settings::value('hide');

        $investor = (new Role())->whereName('investor')->first()->users();
        if (empty($permission)) {
            $investor = $investor->where('creator_id', $userId);
        }

        $investor = $investor->where('active_status', 1);
        if ($exception) {
            $investor = $investor->where('users.id', '!=', $exception);
        }

        if ($hide == 1) {
            $investor = $investor->where('active_status', 1);
        }
        if($company && $company != 0){
            $investor = $investor->where('users.company', $company);
        }

        $investors = $investor->with('userDetails');
        if ($greater_than !== '') {
            $investors = $investors->whereHas('userDetails', function ($investor) use ($greater_than) {
                $investor->where('liquidity', '>', $greater_than);
            });
        }

        return $investors->get();
    }

    public function allLiquidityInvestors()
    {
        $investor = (new Role())->whereName('investor')->first()->users();
        $investors = $investor->with('userDetails');
        $investors = $investors->whereHas('userDetails', function ($investor) {
          //  $investor->where('liquidity', '>', 0);
        });

        return $investors->get();
    }

    public function allInvestorsLiquidityCredit($creator_id = '', $order_by = '', $greater_than = '', $exception = '')
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $max_assign_per = Settings::value('max_assign_per');
        $investor = DB::table('roles')
        ->where('roles.name', 'investor')
        ->join('user_has_roles', 'user_has_roles.role_id', 'roles.id')
        ->join('users', 'users.id', 'user_has_roles.model_id')
        ->join('investor_transactions', 'investor_transactions.investor_id', 'users.id')
        ->join('user_details', 'user_details.user_id', 'users.id')
        ->where('investor_transactions.transaction_type', '2')
        ->where('investor_transactions.status', 1)
        ->groupBy('users.id');
        if (empty($permission)) {
            $investor = $investor->where('creator_id', $userId);
        }
        $max_per_credit = $max_assign_per;
        $liqduitycheckMode=[
            1 => "Consider only Principal",
            2 => "Consider only Liquidity",
            3 => "Consider both Liquidity/Principal",
        ];
        $liquidityMode = 2;
        switch ($liquidityMode) {
            case '1':
            $liquidity_check_querry="if(liquidity<sum((investor_transactions.amount*$max_per_credit)/100), liquidity, sum((investor_transactions.amount*$max_per_credit)/100))";
            break;
            case '2':
            $liquidity_check_querry="((liquidity - user_details.reserved_liquidity_amount)*$max_per_credit/100)";
            break;
            case '3':
            $liquidity_check_querry="if(liquidity<sum((investor_transactions.amount*$max_per_credit)/100), liquidity, IF((liquidity*$max_per_credit/100)>sum((investor_transactions.amount*$max_per_credit)/100), (liquidity*$max_per_credit/100), sum((investor_transactions.amount*$max_per_credit)/100)))";
            break;
        }
        $investor = $investor->select(
            'users.id',
            'users.name',
            'auto_invest',
            'company',
            'users.s_prepaid_status',
            'users.management_fee',
            'users.global_syndication',
            'user_details.reserved_liquidity_amount',
            DB::raw($liquidity_check_querry." as actual_liquidity"),
            DB::raw($liquidity_check_querry." as liquidity"),
            DB::raw("sum((investor_transactions.amount*$max_per_credit)/100) as credit_amount"),
            DB::raw("user_details.liquidity as complete_liquidity")
        );
        
        return $investor;
    }

    public function allDebtInvestorsNetvalues($creator_id = '', $order_by = '', $greater_than = '')
    {

/*
        $investors = (new Role())->whereName('investor')->first()->users();
        $investors=$investors->where('investor_type',1);*/
        //  $investors = $investors->select('merchant_user.commission_amount');
        // $investors = $investors->with('userNetValues2');
        /*    $investors = $investors->with(array('userNetValues' => function($query)
            {
               $query->select(['commission_amount','id']);

            }));*/

        print_r(($investors->get())->toArray());
        exit();
        //  $investors=$investors->selectRaw('sum(merchant_user.amount) as net_zero');
        return $investors->get();
    }

    public function allSubadminInvestorsLiquidity($creator_id = '', $order_by = '', $greater_than = '')
    {
        $subadmin = $this->allSubAdmin();

        $investor = (new Role())->whereName('investor')->first()->users();
        $investor = $investor->whereIn('creator_id', $subadmin);
        $investors = $investor->with('userDetails');
        if ($greater_than !== '') {
            $investors = $investors->whereHas('userDetails', function ($investor) use ($greater_than) {
                $investor->where('liquidity', '>', $greater_than);
            });
        }

        return $investors->get();
    }

    public function nonAssignedInvestorsLiquidity($creator_id = '', $order_by = '', $merchant_id = '')
    {
        //not working now todo
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $investor = (new Role())->whereName('investor')->first()->users();
        if (empty($permission)) {
            $investor = $investor->where('creator_id', $userId);
        }
        if ($order_by) {
            //  $investor = $investor->orderBy('user_details.'.$order_by);
        }
        if ($creator_id !== '') {
            $investors = $investor->with('userDetails')->where('creator_id', '=', $creator_id)->get();
        } else {
            $investors = $investor->with('userDetails')->get();
        }

        return $investors;
    }

    public function countInvestors($company = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;

        $count = (new Role())->whereName('investor')->first()->users;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $count = $count->where('company', $userId);
            } else {
                $count = $count->where('creator_id', $userId);
            }
        }
        if (count($company) > 0) {
            $count = $count->whereIn('company', $company);
        }

        return $tcount = $count->count();
    }

    public function allCompanies()
    {
        return (new Role())->whereName('company')->first()->users->where('company_status',1);
    }

    public function allSubAdmin()
    {
        return (new Role())->whereName('company')->first()->users->where('company_status',1);
    }

    public function allSubAdmins()
    {
        return (new Role())->whereName('company')->first()->users;
    }

    public function getSubAdmin($id)
    {
        $name = (new Role())->whereName('company')->first()->users->where('company_status',1);
        if ($id) {
            $name = $name->where('id', $id);
        }

        return $name;
    }

    public function allAdminUsers()
    {
        return (new Role())->whereName('admin')->first()->users;
    }

    public function checkRole()
    {
        $details = (new Role())->whereName('company')->first()->users->where('company_status',1)->pluck('id');

        return $details;
    }

    public function allRoles()
    {
        return DB::table('roles')->whereNotIn('id', [2])->pluck('name', 'id')->toArray();
    }

    public function allMerchantUserRoleData()
    {
        $return = User::select('users.created_at', 'users.updated_at', 'users.name', 'users.email', 'users.id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id');

        $return = $return->where('role_id', User::MERCHANT_ROLE);

        $return = $return->get();

        return $return;
    }

    public function allUserRoleData($roles)
    {
        $return = User::select('users.creator_id', 'users.created_at', 'users.updated_at', 'users.name', 'users.email', 'users.id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id');
        if ($roles) {
            $return = $return->where('role_id', $roles);
        }
        $return = $return->where('company_status',1)->get();

        return $return;
    }
    public function allUserRoleDataTable($roles)
    {
        $return = User::select('users.creator_id', 'users.created_at', 'users.updated_at', 'users.name', 'users.email', 'users.id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id');
        if ($roles) {
            $return = $return->where('role_id', $roles);
        }
        $return = $return->where('company_status',1);

        return $return;
    }

    public function allModuleData()
    {
        $return = Module::all();

        return $return;
    }

    public function allAccounts()
    {
        return $return = (new Role())->whereIn('roles.name', ['investor', 'Agent Fee Account', 'Over Payment'])
            ->join('user_has_roles', 'user_has_roles.role_id', 'roles.id')
            ->join('users', 'users.id', 'user_has_roles.model_id')->pluck('users.name', 'users.id');
    }

    public function allAgentFeeAccount()
    {
        return (new Role())->whereName('Agent Fee Account')->first()->users;
    }

    public function allOverPaymentAccount()
    {
        return (new Role())->whereName('Over Payment')->first()->users;
    }

    public function allInvestorsWithTrashed()
    {
        return Role::whereName('investor')->first()->users()->withTrashed()->get();
    }
    public function allAccountsWithTrashed()
    {
        return Role::whereIn('roles.name', ['investor', 'Agent Fee Account', 'Over Payment'])->join('user_has_roles', 'user_has_roles.role_id', 'roles.id')
        ->join('users', 'users.id', 'user_has_roles.model_id')->select(DB::raw("upper(users.name) as name"), 'users.id');
    }
    public function getInvestorsFromCompany($companies, $role_id = [User::INVESTOR_ROLE],$velocity_owned=false)
    {
        if (Auth::user()->hasRole('company')) {
            $companies = [Auth::user()->id];
        }
        $role =  Role::join('user_has_roles', 'user_has_roles.role_id', 'roles.id')
        ->join('users', 'users.id', 'user_has_roles.model_id');
        if (! empty($role_id)) {
            $role = $role->whereIn('roles.id', $role_id);
        } else {
            $role = $role->whereIn('roles.id', [User::INVESTOR_ROLE]);
        }
        if (! empty($companies)) {
            $role = $role->whereIn('users.company', $companies);
        }
        if($velocity_owned){
            $role = $role->where('users.velocity_owned', 1);
        }
        return $role->select('users.name', 'users.id');
    }
}
