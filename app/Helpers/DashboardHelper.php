<?php

namespace App\Helpers;

use App\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\User;
use App\Settings;
use App\MerchantUser;
use App\InvestorTransaction;
use App\UserMeta;
use App\Models\InvestorAchRequest;
use FFM;
use App\ParticipentPayment;
use App\Label;


class DashboardHelper
{
    public function __construct(IRoleRepository $role)
        {        
            $this->role = $role;        
        }
        public function getDashboardDetails($request,$companyIds,$setInvestors,$merchantIds){
        $label = $request->label;
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $portfolio_difference = 0;
        $settings = Settings::first();
        $default_payment = $settings->default_payment;
        $default_rate = $settings->rate;
        $default_rate = $default_rate / 100;
        $investors = $this->role->countInvestors($companyIds);
        if ($request->account_filter == 'overpayment') {
            $investors = 0;
        }
        if ($request->account_filter == 'disabled' || $request->account_filter == 'enabled') {
            $investors = count($setInvestors);
        }
        $company = ($request->company) ? $request->company : [Auth::user()->id];
        $investorAdmin = $this->role->allSubAdmin()->pluck('id');
        $investorIds = $this->role->allInvestors()->pluck('id')->toArray();
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        if ($OverpaymentAccount) {
            $investorIds[$OverpaymentAccount->id] = $OverpaymentAccount->id;
        }
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id'); 
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']); 
        if ($AgentFeeAccount) {             
            $investorIds[] = $AgentFeeAccount->id;         
        }
        $investment = MerchantUser::join('users', 'users.id', '=', 'merchant_user.user_id')->with('investors');
        $overPaymentCarryForward = DB::table('carry_forwards')->join('users', 'users.id', 'carry_forwards.investor_id')->where('carry_forwards.type', 1);
        if ($OverpaymentAccount) {
            $overPaymentQuery = DB::table('merchant_user');
            $overPaymentQuery = $overPaymentQuery->where('user_id',$OverpaymentAccount->id);
        }
        $defaultPaymentQuery = MerchantUser::join('merchants', 'merchants.id', '=', 'merchant_user.merchant_id')->whereIn('merchant_user.status', [1, 3])->whereIn('merchants.sub_status_id', [4, 22])->where('merchants.active_status', '=', 1);
        $settledPaymentQuery = MerchantUser::join('merchants', 'merchants.id', '=', 'merchant_user.merchant_id')->whereIn('merchant_user.status', [1, 3])->whereIn('merchants.sub_status_id', [18, 19, 20])->where('merchants.active_status', 1)->where('merchants.old_factor_rate', 0);
        if ($label) {
            $defaultPaymentQuery = $defaultPaymentQuery->where('merchants.label', $label);
            $settledPaymentQuery = $settledPaymentQuery->where('merchants.label', $label);
        }
        $transactionQuery = InvestorTransaction::where('date', '<', NOW());
        $transactionQuery->where('status', InvestorTransaction::StatusCompleted);
        $merchantUserQuery = DB::table('merchant_user')->whereIn('merchant_user.status', [1, 3])->where('amount', '>', 0)->select('invest_rtr', 's_prepaid_status', 'merchant_user.mgmnt_fee as mgmnt_fee', 'amount', 'pmnts', DB::raw('(merchant_user.invest_rtr * merchant_user.mgmnt_fee /100 ) as management_fee'), DB::raw('(merchant_user.invest_rtr - merchant_user.invest_rtr *('.$default_rate.') ) as total_invest_rtr'), DB::raw('(((merchant_user.invest_rtr - merchant_user.invest_rtr *('.$default_rate.') -(merchant_user.amount + merchant_user.commission_amount))/(merchant_user.amount+merchant_user.commission_amount))/(merchants.pmnts/255)*merchant_user.invest_rtr - merchant_user.invest_rtr *('.$default_rate.') ) as blended_amount'), DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as tot_investment'), DB::raw('
                    (
                    (((  (amount * IF(old_factor_rate,old_factor_rate,factor_rate) ) * (100-merchant_user.mgmnt_fee)/100) 
                     -
                     (  merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) 
    
                     )
    
                   )   * IF(advance_type="weekly_ach",52, 255) / merchants.pmnts
                ) as tot_profit '))->join('merchants', 'merchants.id', '=', 'merchant_user.merchant_id');
        if ($label) {
            $merchantUserQuery = $merchantUserQuery->where('merchants.label', $label);
        }
        $isUpdating = UserMeta::find_it(1, 'dashboard_cost_ctd_update') == 'yes';
        $defaultMerchantIds = Merchant::whereIn('sub_status_id', [4, 22])->pluck('id')->toArray();
        $paymentInvestorQuery = MerchantUser::query();
        $paymentInvestorUserQuery = UserMeta::where('key', '_pi_normal_total_principal');
        $currentValueQuery = Merchant::join('merchant_user', 'merchant_user.merchant_id', '=', 'merchants.id')->select(DB::raw(' SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.up_sell_commission + merchant_user.pre_paid) as invested_amount'), DB::raw('SUM(merchant_user.actual_paid_participant_ishare) as paid_participant_ishare'))->whereIn('merchant_user.status', [1, 3])->whereNotIn('merchants.sub_status_id', [4, 22]);
        if ($label) {
            $currentValueQuery = $currentValueQuery->where('merchants.label', $label);
        }
        if (empty($permission) || count($companyIds) > 0) {
            $overpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $overpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
            $overpaymentAccount = $overpaymentAccount->first(['users.id', 'users.company']);
            if ($overpaymentAccount && $request->account_filter != 'disabled' && $request->account_filter != 'enabled') {
                if (in_array($overpaymentAccount->company, $companyIds)) {
                    $setInvestors[] = $overpaymentAccount->id;
                }
            }
            $merchantUserQuery->whereIn('merchant_user.user_id', $setInvestors);
            $paymentInvestorQuery=$paymentInvestorQuery->whereIn('user_id', $setInvestors);
            $paymentInvestorUserQuery->whereIn('user_id', $setInvestors);
            $transactionQuery->whereIn('investor_id', $setInvestors);
            $investment->whereIn('merchant_user.user_id', $setInvestors);
            if ($OverpaymentAccount) {
                $overPaymentQuery->whereIn('user_id', $setInvestors);
            }
            $overPaymentCarryForward->whereIn('carry_forwards.investor_id', $setInvestors);
            $defaultPaymentQuery->whereIn('merchant_user.user_id', $setInvestors);
            $settledPaymentQuery->whereIn('merchant_user.user_id', $setInvestors);
            $currentValueQuery->whereIn('merchant_user.user_id', $setInvestors);
        }
        $defaultPaymentQuery->select(DB::raw('sum(actual_paid_participant_ishare) as participant_share'), DB::raw('sum(actual_paid_participant_ishare - paid_mgmnt_fee) as final_participant_share'), DB::raw('sum(amount+commission_amount + merchant_user.under_writing_fee + merchant_user.up_sell_commission + pre_paid) as total_invested_def'), DB::raw('sum(invest_rtr) as total_invested_rtr'), DB::raw('sum(DATEDIFF(now(),`last_payment_date`)) as days'), DB::raw('
                sum(
                ( 
                    (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                    -
                    (
                        IF(
                            (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                            <
                            (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ),
                            (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission),
                            (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee )
                        ) 
                    )
                )
            ) as default_amount'), DB::raw('
            SUM(
                (
                    (
                        ( merchant_user.invest_rtr +  IF( old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate - factor_rate ) ) , 0 ) )
                        -
                        (
                            merchant_user.invest_rtr * ( merchant_user.mgmnt_fee ) / 100
                            +  
                            IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) *(merchant_user.mgmnt_fee)/100  ) , 0 )
                        )
                    )
                    -
                    ( 
                        IF( merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee,
                            merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee, 0 )
                    )
                )
            ) as total_rtr'), DB::raw('sum( merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ) as ctd3'), DB::raw('sum( (merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100 ) ) as mgt_fee'))->first();
        $settledPaymentQuery->select(DB::raw('
            SUM(
                (
                    (
                        merchant_user.invest_rtr-(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100)
                    )
                    -
                    ( 
                        IF( merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee,
                            merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee, 0 )
                    )
                )
            ) as total_rtr'), )->first();
        $defaultPaymentQuery = $defaultPaymentQuery->first();
        $settledPaymentQuery = $settledPaymentQuery->first();
        $default_pay_payment = $defaultPaymentQuery->participant_share;
        $ctd_defs = $defaultPaymentQuery->final_participant_share;
        $investments = $investment->whereIn('merchant_user.status', [1, 3])->whereHas('merchant', function ($inner) use ($label) {
            $inner->where('active_status', 1);
            if ($label) {
                $inner->where('label', $label);
            }
        })->with('merchant')->select(DB::raw('sum(actual_paid_participant_ishare) as paid_participant_ishare'), DB::raw('sum(under_writing_fee) as under_writing_fee'), DB::raw('sum(paid_mgmnt_fee+pre_paid) as all_paid_fee'), DB::raw('sum(amount + pre_paid + commission_amount + merchant_user.under_writing_fee + merchant_user.up_sell_commission ) as  invested_amount'), DB::raw('SUM(IF(actual_paid_participant_ishare > invest_rtr, ( actual_paid_participant_ishare - invest_rtr ) * ( 1 - (merchant_user.mgmnt_fee ) / 100 ), 0)) as overpayment'))->first();
        $current_value = $currentValueQuery->first();
        $balance = DB::table('merchant_user')->whereIn('merchant_user.status', [1, 3])->whereIn('sub_status_id', [11, 18, 19, 20])->join('users', 'users.id', 'merchant_user.user_id')->whereIn('users.company', $company)->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
        if ($label) {
            $balance = $balance->where('merchants.label', $label);
        }
        $balance = $balance->groupBy('merchant_user.merchant_id')->select(DB::raw('sum(merchant_user.invest_rtr * ((100 - merchant_user.mgmnt_fee) / 100)
                        - (merchant_user.paid_participant_ishare-(paid_mgmnt_fee)  )
                   ) as balance_after_fee'))->pluck('balance_after_fee')->toArray();
        $positive_integers = array_filter($balance, function ($value) {
            return $value > 0;
        });
        $t_balance = array_sum($positive_integers);
        $ctd2 = $investments->paid_participant_ishare;
        $overpayment = $investments->overpayment;
        if ($label) {
            if ($OverpaymentAccount) {
                $overpayment = $overPaymentQuery->whereIn('merchant_id', $merchantIds);
            }
        }
        $overpayment=0;
        if ($OverpaymentAccount) {
            $overpayment = $overPaymentQuery->sum('actual_paid_participant_ishare');
        }
        if ($label) {
            $overPaymentCarryForward = $overPaymentCarryForward->whereIn('carry_forwards.merchant_id', $merchantIds);
        }
        $overpayment += $overPaymentCarryForward->sum('carry_forwards.amount');
        $all_paid_fee = $investments->all_paid_fee;
        $total_rtr = 0;
        $investedAmount = $investments->invested_amount;
        $merchantUserQuery1 = clone $merchantUserQuery;
        $merchant_userss = $merchantUserQuery->get();
        $totalInvestmentRTR = $merchant_userss->pluck('total_invest_rtr')->sum();
        $total_a_rtr = $overpayment + $merchant_userss->pluck('total_invest_rtr')->sum();
        $management_fee = $merchant_userss->pluck('management_fee')->sum();
        $syndication_fee = $merchant_userss->pluck('synd_fee')->sum();
        $total_profit = $merchantUserQuery1->whereIn('sub_status_id', [1, 5, 16, 2, 13, 12])->get()->pluck('tot_profit')->sum();
        $tot_investment = $merchantUserQuery1->whereIn('sub_status_id', [1, 5, 16, 2, 13, 12])->get()->pluck('tot_investment')->sum();
        $bleded_amount = $merchant_userss->pluck('blended_amount')->sum();
        $fees2 = $management_fee;
        $total_a_rtr = ($totalInvestmentRTR - $management_fee) - ($defaultPaymentQuery->total_invested_rtr - $defaultPaymentQuery->mgt_fee) + $ctd_defs + $overpayment;
        $total_a_rtr -= $settledPaymentQuery->total_rtr;
        $blended_rate = $tot_investment ? $total_profit / $tot_investment * 100 : 0;
        if ($label) {
            $paymentInvestorQuery = $paymentInvestorQuery->whereIn('merchant_id', $merchantIds);
        } else {
            $paymentInvestorQuery = $paymentInvestorQuery->whereNotIn('merchant_id', $defaultMerchantIds);
        }
        if ($isUpdating || $label) {
            $cost_for_ctd = $paymentInvestorQuery->sum('paid_principal');
        } else {
            $cost_for_ctd = $paymentInvestorUserQuery->sum('value');
        }
        $c_invested_amount = round(($current_value->invested_amount - $cost_for_ctd),2);
        $total_invested_def = 0;
        if ($default_payment == 1) {
            $total_invested_def = $defaultPaymentQuery->total_invested_def;
            $default_rate = ($investedAmount > 0) ? ($total_invested_def - $ctd_defs - $overpayment) / $investedAmount * 100 : 0;
        } elseif ($default_payment == 2) {
            $total_invested_def = $defaultPaymentQuery->total_invested_rtr - $defaultPaymentQuery->mgt_fee - $ctd_defs;
            $default_rate = ($total_a_rtr > 0) ? ($total_invested_def - $overpayment) / $investedAmount * 100 : 0;
        }
        $pending_investor_ach_credit_requested_amount = InvestorAchRequest::whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing);
        if ($request->company) {
            $companies = $request->company;
            $pending_investor_ach_credit_requested_amount->whereHas('Investor', function ($query) use ($companies) {
                $query->whereIn('company', $companies);
            });
        }
        $pending_investor_ach_credit_requested_amount = $pending_investor_ach_credit_requested_amount->whereIn('transaction_type', ['credit', 'same_day_credit'])->sum('amount');
        $pending_investor_ach_debit_requested_amount = InvestorAchRequest::whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing);
        if ($request->company) {
            $companies = $request->company;
            $pending_investor_ach_debit_requested_amount->whereHas('Investor', function ($query) use ($companies) {
                $query->whereIn('company', $companies);
            });
        }
        $pending_investor_ach_debit_requested_amount = $pending_investor_ach_debit_requested_amount->whereIn('transaction_type', ['debit', 'same_day_debit'])->sum('amount');
        $data                                              = [
            'draw'                                         => $request->input('draw'),
            'total_rtr'                                    => FFM::dollar($total_a_rtr),
            'total_investors'                              => $investors,
            'total_merchants'                              => count($merchantIds),
            'invested_amount'                              => FFM::dollar($investedAmount),
            'blended_rate'                                 => FFM::percent($blended_rate),
            'current_invested_amount'                      => FFM::dollar($c_invested_amount),
            'overpayment'                                  => FFM::dollar($overpayment),
            'default_amount'                               => FFM::dollar($total_invested_def),
            'default_rate'                                 => ($default_rate>0)?FFM::percent($default_rate):FFM::percent(0),
            'bleded_amount'                                => FFM::dollar($bleded_amount),
            'pending_investor_ach_debit_requested_amount'  => FFM::dollar($pending_investor_ach_debit_requested_amount),
            'pending_investor_ach_credit_requested_amount' => FFM::dollar($pending_investor_ach_credit_requested_amount)
        ];
        return $data;
        }
        public function getDashboardIndex($request){
        $pendingRequests = ParticipentPayment::where('status', 2)->count();
        $subAdminPermission = $request->user()->hasRole(['company']);
        $subadmin_permission = $subAdminPermission;
        $companies = $this->role->allSubAdmin()->pluck('name', 'id')->toArray();
        $companyIds = $this->role->allCompanies()->pluck('id')->slice(0, 2)->toArray();
        if ($request->user()->hasRole(['company'])) {
            $companyIds = [];
        }
        $labels = Label::pluck('name', 'id');
            $data   = [
            'pendingRequests'       => $pendingRequests,
            'subAdminPermission'    => $subAdminPermission,
            'subadmin_permission'   => $subadmin_permission,
            'companies'             => $companies,
            'companyIds'            => $companyIds,
            'labels'                => $labels,
            
            ];
            return $data;

        }

}
