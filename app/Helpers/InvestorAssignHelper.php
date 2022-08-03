<?php

namespace App\Helpers;

use App\AchRequest;
use App\Bank;
use App\CompanyAmount;
use App\Exports\Data_arrExport;
use App\FundingRequests;
use MerchantHelper;
use MerchantUserHelper;
use App\Helpers\InvestorAssignHelper;
use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\Http\Controllers\Admin\Traits\DocumentUploader;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\Admin\StoryMerchantRequest;
use App\Http\Requests\AdminUpdateMerchantRequest;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IUserActivityLogRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Label;
use App\LiquidityLog;
use App\MarketpalceInvestors;
use App\MbatchMarchant;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantPaymentTerm;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\MNotes;
use App\SubStatusFlag;
use App\Models\Transaction;
use App\Models\Views\MerchantUserView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\PaymentPause;
use App\ReassignHistory;
use App\Settings;
use App\SubStatus;
use App\Template;
use App\TermPaymentDate;
use App\User;
use App\UserActivityLog;
use App\UserDetails;
use App\VelocityFee;
use Carbon\Carbon;
use DateTime;
use Exception;
use FFM;
use Form;
use GPH;
use InvestorHelper;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use MTB;
use PayCalc;
use PDF;
use Permissions;
use Spatie\Permission\Models\Role;
use Stripe\Charge;
use Stripe\Stripe;
use Yajra\DataTables\Html\Builder;



class InvestorAssignHelper
{
    public function __construct(ISubStatusRepository $subStatus, IRoleRepository $role, IMerchantRepository $merchant, ILabelRepository $label,IParticipantPaymentRepository $payment)
    {
         $this->subStatus = $subStatus;
         $this->role = $role;
         $this->merchant = $merchant;
         $this->label = $label;
         $this->payment = $payment;
        
    }

	 public function investorsBasedOnLiquidity($request)
	 {
	 	   $existing_investors1 = MerchantUser::where('merchant_id', $request->merchant_id);            
            // if (empty($permission)) {
            //     $existing_investors1 = $existing_investors1->whereIn('user_id', $investor_ids);
            // }
            $existing_investors = $existing_investors1->pluck('user_id')->toArray();
            $no_liquidity = UserDetails::where('liquidity', '<=', 0);
            // if ($permission) {
            //     $no_liquidity = $no_liquidity->whereIn('user_id', $investor_ids);
            // }
            $investor_with_no_liquidity = $no_liquidity->pluck('user_id')->toArray();
            $investors = $this->role->allInvestorsLiquidity('', '', 0)->whereNotIn('id', $existing_investors)->whereNotIn('id', $investor_with_no_liquidity)->where('investor_type', '!=', 5)->pluck('id');
            $investors_data = $investors->toArray();
            DB::beginTransaction();
            $userId = $request->user()->id;
            $total_liquidity = $sub_admin_tot_amnt = $admin_tot_amnt = $max_part_available_fund_subadmin = $max_part_available_fund_admin = 0;
            $minimum_investment_value = (Settings::where('keys', 'minimum_investment_value')->value('values')) ?? 0;
            $companies = $this->role->allCompanies();
            $underwriting = $companies->pluck('id')->toArray();
            $company_arr = $companies->pluck('id')->toArray();
            if (! is_array($underwriting)) {
                $underwriting = [];
            }
            array_unshift($underwriting, '');
            unset($underwriting[0]);
            if (! $request->all()) {
                throw new \Exception('Empty Request', 1);
            }
            $merchant_id = $request->merchant_id;
            $selected_investors = $investors_data;//$request->all_investors;
            
            if (! $selected_investors) {
                throw new \Exception('Please Select Investors', 1);
            }
            $this_merchant = Merchant::where('id', $merchant_id)->select('max_participant_fund', 'm_mgmnt_fee', 'm_syndication_fee', 'date_funded', 'commission','up_sell_commission', 'm_s_prepaid_status', 'factor_rate', 'underwriting_status','funded', 'underwriting_fee', 'label', 'origination_fee', 'pmnts', 'id', 'advance_type', 'payment_amount', 'first_payment', 'payment_end_date', 'ach_pull', )->first();
            $underwriting_fee = $this_merchant->underwriting_status;
            $underwriting_fee = json_decode($underwriting_fee, true);
            if (! is_array($underwriting_fee)) {
                $underwriting_fee = [];
            }
            array_unshift($underwriting_fee, '');
            unset($underwriting_fee[0]);
            $company_arr1 = $companies->pluck('id')->toArray();
            if (! is_array($company_arr1)) {
                $company_arr1 = [];
            }
            array_unshift($company_arr1, '');
            unset($company_arr1[0]);
            $status = [];
            foreach ($underwriting_fee as $key => $value) {
                $underwriting_fee1 = isset($company_arr1[$value]) ? $company_arr1[$value] : '';
                $status[$underwriting_fee1] = $underwriting_fee1;
            }
            $company_amount = CompanyAmount::where('merchant_id', $merchant_id)->pluck('max_participant', 'company_id')->toArray();
            $selected_companies_list = CompanyAmount::where('merchant_id', $merchant_id)->where('max_participant','!=',0)->pluck('company_id', 'company_id')->toArray();
            $companies = $this->role->allCompanies()->pluck('id')->toArray();
            $total_liquidity = 0;
            /**************  APPLY Max 10% of credit   ****************/
            $admin_investors = $this->role->allInvestorsLiquidityCredit('', 'liquidity', 0)->whereIn('company',$selected_companies_list)->whereIn('users.id', $selected_investors)->where('active_status', 1);
            $admin_investors = $admin_investors->orderBy('liquidity',"DESC");
            $admin_investors = $admin_investors->get();
            $MessageData=[];
            foreach ($admin_investors as $TableDataSingle) {
                $single['user_id']                  =$TableDataSingle->id;
                $single['name']                     =$TableDataSingle->name;
                $single['complete_liquidity']       =round($TableDataSingle->complete_liquidity,2);
                $single['actual_liquidity']         =round($TableDataSingle->actual_liquidity,2);
                $single['liquidity']                =round($TableDataSingle->liquidity,2);
                $single['credit_amount']            =round($TableDataSingle->credit_amount,2);
                $single['max_investment_liquidity'] =round($TableDataSingle->liquidity,2);
                $single['max_funded_amount']        ='Failed';
                $single['min_funded_amount']        ='Failed';
                $single['liquidity_check']          ='Passed';
                $single['reason']                   ='';
                $MessageData[$TableDataSingle->id]=$single;
            }
            /**************  APPLY MAX 10% of investment   ****************/
            $max_investors=InvestorAssignHelper::applyRule2MaxPercentageFundingAmount($admin_investors,$this_merchant);
            foreach ($max_investors as $TableDataSingle) {
                $MessageData[$TableDataSingle->id]['max_investment_liquidity']=round($TableDataSingle->liquidity,2);
                $MessageData[$TableDataSingle->id]['max_funded_amount']='Passed';
            }
            /**************  APPLY Minimum 1000$  ****************/
            $filtered_admin_investors=$InvestorAssignHelper::applyRule3MinimumInvestmentValue($max_investors,$this_merchant);
            foreach ($filtered_admin_investors as $TableDataSingle) {
                $MessageData[$TableDataSingle->id]['min_funded_amount']='Passed';
            }
            $company_investments = DB::table('merchant_user')
            ->join('users', 'users.id', 'merchant_user.user_id')
            ->join('user_has_roles', 'user_has_roles.model_id', 'users.id')
            ->where('user_has_roles.role_id', '!=', 13)
            ->whereIn('users.company', $companies)
            ->where('merchant_user.merchant_id', $merchant_id)
            ->groupBy('users.company')
            ->pluck(DB::raw('SUM(amount)'), 'company');
            $loop=0;
            repeatLoop :
            $users_array = [];
            $insert_array = [];
            $loop++;
            $removed_investor=[];
            $liquidity = [];
            foreach ($filtered_admin_investors as $key => $value) {
                $total_liquidity += $value->actual_liquidity;
                if (! isset($liquidity[$value->company])) {
                    $liquidity[$value->company] = 0;
                }
                $liquidity[$value->company] += $value->actual_liquidity;
            }
            $total_investing_amount=[];
            $CompanyWiseList = array();
            foreach ($filtered_admin_investors as $element) {
                $CompanyWiseList[$element->company][] = $element;
            }
            foreach ($CompanyWiseList as $company => $investors) {
                $company_amount1 = $company_amount[$company] ?? 0;
                $company_wise_investor_liquidity = $liquidity[$company] ?? 0;
                if(!$company_wise_investor_liquidity) { continue; }
                if(!$company_amount1){ continue; }
                foreach ($investors as $investor) {
                  $return_function=InvestorAssignHelper::LiquidityBasedInvestmentFunction($investor,$merchant_id,$this_merchant,$liquidity,$insert_array,$MessageData,$company_amount1,$total_investing_amount,$company_wise_investor_liquidity,$company_investments,$removed_investor,$users_array,$userId);
                    if($return_function['result']!='success') throw new \Exception($return_function['result'], 1);
                    $company_wise_investor_liquidity =$return_function['company_wise_investor_liquidity'];
                    $company_amount1                 =$return_function['company_amount1'];
                    $insert_array                    =$return_function['insert_array'];
                    $MessageData                     =$return_function['MessageData'];
                    $total_investing_amount          =$return_function['total_investing_amount'];
                    $removed_investor                =$return_function['removed_investor'];
                    $users_array                     =$return_function['users_array'];

                }
            }
            if(!empty($removed_investor)){
                if($loop<=2){
                    $filtered_admin_investors = array_filter($filtered_admin_investors, function ($single) use ($removed_investor) {
                        if(in_array($single->id,$removed_investor)){ return false; }
                        return true;
                    });
                    $removed_investor=[];
                    goto repeatLoop;
                }
            }
            // if(empty($insert_array)) throw new \Exception('No Data To Add Please Check Liquidity / Credit Amount / Company Share', 1);
            foreach ($insert_array as $item) {
                MerchantUser::create($item);
            }
            //////////////////////////////****************//////////////////////////
           $sess_arr = array();
           $k=0;
           
            foreach ($insert_array as $item) {
                $sess_arr[$k]['id'] = $item['user_id'];
                $sess_arr[$k]['share'] = ($item['amount']/$this_merchant->funded)*100;
                $sess_arr[$k]['amount'] = $item['amount'];
                $sess_arr[$k]['mgmnt_fee_per'] = $item['mgmnt_fee'];
                $sess_arr[$k]['underwriting_fee_per'] = $item['under_writing_fee_per'];
                $sess_arr[$k]['syndication_fee_per'] = $item['syndication_fee_percentage'];
                $sess_arr[$k]['commission'] = $item['commission_per'];
                $sess_arr[$k]['upsell_commission_per'] = $item['up_sell_commission_per'];
                $sess_arr[$k]['syndication_on'] = $item['s_prepaid_status'];
                $k++;
          
            }
            
            //////////////////////////////****************//////////////////////////
           // print_r($insert_array);exit;
            $CompanyFunded=CompanyAmount::leftjoin('merchant_user_views', function($join) use($merchant_id){
                $join->on('merchant_user_views.company', 'company_amount.company_id');
                $join->where('merchant_user_views.merchant_id', $merchant_id);
            })
            ->select(DB::raw('IF(amount,sum(amount),0) as company_funded'),'max_participant','company_amount.company_id')
            ->where('company_amount.merchant_id', $merchant_id)
            ->groupBy('company_amount.company_id')
            ->get()
            ->toArray();
            foreach ($CompanyFunded as $key => $value) {
                $company_balance_share=$value['max_participant']-$value['company_funded'];
                if($company_balance_share>0){
                    $liquidity=[];
                    foreach ($MessageData as $mkey => $single) {
                        if(
                            $single['max_funded_amount'] =="Passed" && 
                            $single['min_funded_amount'] =="Failed" && 
                            $single['liquidity_check']   =="Passed"
                        ){
                            if($company_balance_share>0){
                                $single_insert_array=[];
                                $investor = $this->role->allInvestorsLiquidityCredit('', 'liquidity', 0)->where('users.id', $single['user_id'])->first();
                                if($investor){
                                    if($investor->company==$value['company_id']){
                                        $company_amount1 = $company_balance_share;
                                        $liquidity[$value['company_id']] = $investor->actual_liquidity;
                                        $company_wise_investor_liquidity=$investor->actual_liquidity;
                                        if($company_wise_investor_liquidity<=0){ continue; }
                                        $return_function=InvestorAssignHelper::LiquidityBasedInvestmentFunction($investor,$merchant_id,$this_merchant,$liquidity,$single_insert_array,$MessageData,$company_amount1,$total_investing_amount,$company_wise_investor_liquidity,$company_investments,$removed_investor,$users_array,$userId);
                                        if($return_function['result']!='success') throw new \Exception($return_function['result'], 1);
                                        $total_investing_amount     =$return_function['total_investing_amount'];
                                        $users_array                =$return_function['users_array'];
                                        $company_amount1            =$return_function['company_amount1'];
                                        $single_insert_array        =$return_function['insert_array'];
                                        if(!empty($single_insert_array)){
                                            $MessageData            =$return_function['MessageData'];
                                            $item                   =$single_insert_array[0];
                                            $company_balance_share -=$item['amount'];
                                            $insert_array[]         =$item;
                                            foreach ($insert_array as $item) {
                                            $sess_arr[$k]['id'] = $item['user_id'];
                                            $sess_arr[$k]['share'] = ($item['amount']/$this_merchant->funded)*100;
                                            $sess_arr[$k]['amount'] = $item['amount'];
                                            $sess_arr[$k]['mgmnt_fee_per'] = $item['mgmnt_fee'];
                                            $sess_arr[$k]['underwriting_fee_per'] = $item['under_writing_fee_per'];
                                            $sess_arr[$k]['syndication_fee_per'] = $item['syndication_fee_percentage'];
                                            $sess_arr[$k]['commission'] = $item['commission_per'];
                                            $sess_arr[$k]['upsell_commission_per'] = $item['up_sell_commission_per'];
                                            $sess_arr[$k]['syndication_on'] = $item['s_prepaid_status'];                                            
                                            $k++;
                                      
                                            }
                                           
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $sess_arr;

	 }


     public function assignBasedOnLiquidty($request)
     {
           DB::beginTransaction();
            $userId = $request->user()->id;
            $total_liquidity = $sub_admin_tot_amnt = $admin_tot_amnt = $max_part_available_fund_subadmin = $max_part_available_fund_admin = 0;
            $minimum_investment_value = (Settings::where('keys', 'minimum_investment_value')->value('values')) ?? 0;
            $companies = $this->role->allCompanies();
            $underwriting = $companies->pluck('id')->toArray();
            $company_arr = $companies->pluck('id')->toArray();
            if (! is_array($underwriting)) {
                $underwriting = [];
            }
            array_unshift($underwriting, '');
            unset($underwriting[0]);
            if (! $request->all()) {
                throw new \Exception('Empty Request', 1);
            }
            $merchant_id = $request->merchant_id;
            $selected_investors = $request->all_investors;
            if (! $selected_investors) {
                throw new \Exception('Please Select Investors', 1);
            }
            $this_merchant = Merchant::where('id', $merchant_id)->select('max_participant_fund', 'm_mgmnt_fee', 'm_syndication_fee', 'date_funded', 'commission','up_sell_commission', 'm_s_prepaid_status', 'factor_rate', 'underwriting_status','funded', 'underwriting_fee', 'label', 'origination_fee', 'pmnts', 'id', 'advance_type', 'payment_amount', 'first_payment', 'payment_end_date', 'ach_pull', )->first();
            $underwriting_fee = $this_merchant->underwriting_status;
            $underwriting_fee = json_decode($underwriting_fee, true);
            if (! is_array($underwriting_fee)) {
                $underwriting_fee = [];
            }
            array_unshift($underwriting_fee, '');
            unset($underwriting_fee[0]);
            $company_arr1 = $companies->pluck('id')->toArray();
            if (! is_array($company_arr1)) {
                $company_arr1 = [];
            }
            array_unshift($company_arr1, '');
            unset($company_arr1[0]);
            $status = [];
            foreach ($underwriting_fee as $key => $value) {
                $underwriting_fee1 = isset($company_arr1[$value]) ? $company_arr1[$value] : '';
                $status[$underwriting_fee1] = $underwriting_fee1;
            }
            $company_amount = CompanyAmount::where('merchant_id', $merchant_id)->pluck('max_participant', 'company_id')->toArray();
            $selected_companies_list = CompanyAmount::where('merchant_id', $merchant_id)->where('max_participant','!=',0)->pluck('company_id', 'company_id')->toArray();
            $companies = $this->role->allCompanies()->pluck('id')->toArray();
            $total_liquidity = 0;
            /**************  APPLY Max 10% of credit   ****************/
            $admin_investors = $this->role->allInvestorsLiquidityCredit('', 'liquidity', 0)->whereIn('company',$selected_companies_list)->whereIn('users.id', $selected_investors)->where('active_status', 1);
            $admin_investors = $admin_investors->orderBy('liquidity',"DESC");
            $admin_investors = $admin_investors->get();
            $MessageData=[];
            foreach ($admin_investors as $TableDataSingle) {
                $single['user_id']                  =$TableDataSingle->id;
                $single['name']                     =$TableDataSingle->name;
                $single['complete_liquidity']       =round($TableDataSingle->complete_liquidity,2);
                $single['actual_liquidity']         =round($TableDataSingle->actual_liquidity,2);
                $single['liquidity']                =round($TableDataSingle->liquidity,2);
                $single['credit_amount']            =round($TableDataSingle->credit_amount,2);
                $single['max_investment_liquidity'] =round($TableDataSingle->liquidity,2);
                $single['max_funded_amount']        ='Failed';
                $single['min_funded_amount']        ='Failed';
                $single['liquidity_check']          ='Passed';
                $single['reason']                   ='';
                $MessageData[$TableDataSingle->id]=$single;
            }
            /**************  APPLY MAX 10% of investment   ****************/
            $max_investors=InvestorAssignHelper::applyRule2MaxPercentageFundingAmount($admin_investors,$this_merchant);
            foreach ($max_investors as $TableDataSingle) {
                $MessageData[$TableDataSingle->id]['max_investment_liquidity']=round($TableDataSingle->liquidity,2);
                $MessageData[$TableDataSingle->id]['max_funded_amount']='Passed';
            }
            /**************  APPLY Minimum 1000$  ****************/
            $filtered_admin_investors=InvestorAssignHelper::applyRule3MinimumInvestmentValue($max_investors,$this_merchant);
            foreach ($filtered_admin_investors as $TableDataSingle) {
                $MessageData[$TableDataSingle->id]['min_funded_amount']='Passed';
            }
            $company_investments = DB::table('merchant_user')
            ->join('users', 'users.id', 'merchant_user.user_id')
            ->join('user_has_roles', 'user_has_roles.model_id', 'users.id')
            ->where('user_has_roles.role_id', '!=', 13)
            ->whereIn('users.company', $companies)
            ->where('merchant_user.merchant_id', $merchant_id)
            ->groupBy('users.company')
            ->pluck(DB::raw('SUM(amount)'), 'company');
            $loop=0;
            repeatLoop :
            $users_array = [];
            $insert_array = [];
            $loop++;
            $removed_investor=[];
            $liquidity = [];
            foreach ($filtered_admin_investors as $key => $value) {
                $total_liquidity += $value->actual_liquidity;
                if (! isset($liquidity[$value->company])) {
                    $liquidity[$value->company] = 0;
                }
                $liquidity[$value->company] += $value->actual_liquidity;
            }
            $total_investing_amount=[];
            $CompanyWiseList = array();
            foreach ($filtered_admin_investors as $element) {
                $CompanyWiseList[$element->company][] = $element;
            }
            foreach ($CompanyWiseList as $company => $investors) {
                $company_amount1 = $company_amount[$company] ?? 0;
                $company_wise_investor_liquidity = $liquidity[$company] ?? 0;
                if(!$company_wise_investor_liquidity) { continue; }
                if(!$company_amount1){ continue; }
                foreach ($investors as $investor) {
                  $return_function=InvestorAssignHelper::LiquidityBasedInvestmentFunction($investor,$merchant_id,$this_merchant,$liquidity,$insert_array,$MessageData,$company_amount1,$total_investing_amount,$company_wise_investor_liquidity,$company_investments,$removed_investor,$users_array,$userId);
                    if($return_function['result']!='success') throw new \Exception($return_function['result'], 1);
                    $company_wise_investor_liquidity =$return_function['company_wise_investor_liquidity'];
                    $company_amount1                 =$return_function['company_amount1'];
                    $insert_array                    =$return_function['insert_array'];
                    $MessageData                     =$return_function['MessageData'];
                    $total_investing_amount          =$return_function['total_investing_amount'];
                    $removed_investor                =$return_function['removed_investor'];
                    $users_array                     =$return_function['users_array'];

                }
            }
            if(!empty($removed_investor)){
                if($loop<=2){
                    $filtered_admin_investors = array_filter($filtered_admin_investors, function ($single) use ($removed_investor) {
                        if(in_array($single->id,$removed_investor)){ return false; }
                        return true;
                    });
                    $removed_investor=[];
                    goto repeatLoop;
                }
            }
            // if(empty($insert_array)) throw new \Exception('No Data To Add Please Check Liquidity / Credit Amount / Company Share', 1);
            foreach ($insert_array as $item) {
                MerchantUser::create($item);
            }
            $CompanyFunded=CompanyAmount::leftjoin('merchant_user_views', function($join) use($merchant_id){
                $join->on('merchant_user_views.company', 'company_amount.company_id');
                $join->where('merchant_user_views.merchant_id', $merchant_id);
            })
            ->select(DB::raw('IF(amount,sum(amount),0) as company_funded'),'max_participant','company_amount.company_id')
            ->where('company_amount.merchant_id', $merchant_id)
            ->groupBy('company_amount.company_id')
            ->get()
            ->toArray();
            foreach ($CompanyFunded as $key => $value) {
                $company_balance_share=$value['max_participant']-$value['company_funded'];
                if($company_balance_share>0){
                    $liquidity=[];
                    foreach ($MessageData as $mkey => $single) {
                        if(
                            $single['max_funded_amount'] =="Passed" && 
                            $single['min_funded_amount'] =="Failed" && 
                            $single['liquidity_check']   =="Passed"
                        ){
                            if($company_balance_share>0){
                                $single_insert_array=[];
                                $investor = $this->role->allInvestorsLiquidityCredit('', 'liquidity', 0)->where('users.id', $single['user_id'])->first();
                                if($investor){
                                    if($investor->company==$value['company_id']){
                                        $company_amount1 = $company_balance_share;
                                        $liquidity[$value['company_id']] = $investor->actual_liquidity;
                                        $company_wise_investor_liquidity=$investor->actual_liquidity;
                                        if($company_wise_investor_liquidity<=0){ continue; }
                                        $return_function=InvestorAssignHelper::LiquidityBasedInvestmentFunction($investor,$merchant_id,$this_merchant,$liquidity,$single_insert_array,$MessageData,$company_amount1,$total_investing_amount,$company_wise_investor_liquidity,$company_investments,$removed_investor,$users_array,$userId);
                                        if($return_function['result']!='success') throw new \Exception($return_function['result'], 1);
                                        $total_investing_amount     =$return_function['total_investing_amount'];
                                        $users_array                =$return_function['users_array'];
                                        $company_amount1            =$return_function['company_amount1'];
                                        $single_insert_array        =$return_function['insert_array'];
                                        if(!empty($single_insert_array)){
                                            $MessageData            =$return_function['MessageData'];
                                            $item                   =$single_insert_array[0];
                                            $company_balance_share -=$item['amount'];
                                            $insert_array[]         =$item;
                                            $MerchantUser=MerchantUser::create($item);
                                            if(!empty($MerchantUser)){
                                                $MerchantUserView=MerchantUserView::find($MerchantUser->id);
                                                $MessageData[$investor->id]['min_funded_amount'] ='Passed';
                                                $MessageData[$investor->id]['funded']            =$MerchantUserView['amount'];
                                                $MessageData[$investor->id]['investment_amount'] =$MerchantUserView['total_investment'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (! empty($insert_array)) {
                $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
                $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
                $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
                if ($OverpaymentAccount) {
                    $OverPaymentMerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($OverpaymentAccount->id)->first();
                    if (! $OverPaymentMerchantUser) {
                        $item = ['user_id' => $OverpaymentAccount->id, 'amount' => 0, 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => 0, 'mgmnt_fee' => 0, 'syndication_fee_percentage' => 0, 'commission_amount' => 0, 'commission_per' => 0,
                        'up_sell_commission_per'=>0,'up_sell_commission'=>0,'under_writing_fee' => 0, 'under_writing_fee_per' => 0, 'creator_id' => $userId, 'pre_paid' => 0, 's_prepaid_status' => 1, 'creator_id' => ($request->user()) ? $request->user()->id : null];
                        MerchantUser::create($item);
                    }
                }
                $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
                $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
                $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
                if ($AgentFeeAccount) {
                    $AgentFeeMerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($AgentFeeAccount->id)->first();
                    if (! $AgentFeeMerchantUser) {
                        $item1 = ['user_id' => $AgentFeeAccount->id, 'amount' => 0, 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => 0, 'mgmnt_fee' => 0, 'syndication_fee_percentage' => 0, 'commission_amount' => 0, 'commission_per' => 0, 'up_sell_commission_per'=>0,'up_sell_commission'=>0,'under_writing_fee' => 0, 'under_writing_fee_per' => 0, 'creator_id' => $userId, 'pre_paid' => 0, 's_prepaid_status' => 1, 'creator_id' => ($request->user()) ? $request->user()->id : null];
                        MerchantUser::create($item1);
                    }
                }
            }
            if(in_array($this_merchant->label,[1,2])){
                MerchantUser::InvestmentAmountAdjuster($merchant_id); 
            }
            InvestorHelper::update_liquidity($users_array, 'based_on_liquidity', $merchant_id);
            $removed_investors = InvestorAssignHelper::check_diff_multi($max_investors, $filtered_admin_investors);
            $removed_minimum_investers=[];
            foreach ($removed_investors as $single) {
                $removed_minimum_investers[]=$single->id;
                // $message .= '<br> Not Included Investor '.$single->name.' Because the Final Liquidity is Below '.FFM::dollar($minimum_investment_value).' -> '.FFM::dollar($single->liquidity);
            }
            $ErrroMessage ="<table class='table'>";
            $max_assign_per = Settings::value('max_assign_per');
            $ErrroMessage.="<tr>";
            $ErrroMessage.="<td>Investor</td>";
            $ErrroMessage.="<td class='text-right'>Credit Amount (".$max_assign_per."%)</td>";
            $ErrroMessage.="<td class='text-right'>Liquidity</td>";
            $ErrroMessage.="<td>Reason</td>";
            $ErrroMessage.="</tr>";
            $errorCount=0;
            $SuccessMessage ="<table class='table'>";
            $SuccessMessage.="<tr>";
            $SuccessMessage.="<td colspan='5' class='text-center'>".count($insert_array)." investors assigned to merchant</td>";
            $SuccessMessage.="</tr>";
            if (env('APP_ENV') == 'local') {
                $SuccessMessage.="<tr>";
                $SuccessMessage.="<td>Investor</td>";
                $SuccessMessage.="<td class='text-right'>Funded</td>";
                $SuccessMessage.="<td class='text-right'>Investment</td>";
                $SuccessMessage.="<td class='text-right'>Credit Amount (".$max_assign_per."%)</td>";
                $SuccessMessage.="<td class='text-right'>Liquidity</td>";
                $SuccessMessage.="</tr>";
            }
            foreach ($MessageData as $key => $value) {
                if(
                    $value['max_funded_amount']=="Passed" &&
                    $value['min_funded_amount']=="Passed" &&
                    $value['liquidity_check']=="Passed"
                ){
                    if (env('APP_ENV') == 'local') {
                        $value['funded']=$value['funded']??0;
                        if($value['funded']){
                            $SuccessMessage.="<tr>";
                            $SuccessMessage.= '<td>'.$value['name'].'</td>';
                            $SuccessMessage.= '<td class="text-right">'.FFM::dollar($value['funded']).'</td>';
                            $SuccessMessage.= '<td class="text-right">'.FFM::dollar($value['investment_amount']).'</td>';
                            $SuccessMessage.= '<td class="text-right">'.FFM::dollar($value['credit_amount']).'</td>';
                            $SuccessMessage.= '<td class="text-right">'.FFM::dollar($value['complete_liquidity']).'</td>';
                            $SuccessMessage.="</tr>";
                        }
                    }
                } else {
                    $errorCount++;
                    $ErrroMessage.="<tr>";
                    $ErrroMessage.= '<td>'.$value['name'].'</td>';
                    $ErrroMessage.= '<td class="text-right">'.FFM::dollar($value['credit_amount']).'</td>';
                    $ErrroMessage.= '<td class="text-right">'.FFM::dollar($value['complete_liquidity']).'</td>';
                    if(in_array($value['user_id'],$removed_minimum_investers)){
                        if(empty($value['reason'])){
                            $ErrroMessage.= '<td>1 The Investment amount below Minimum Investment Amount '.FFM::dollar($minimum_investment_value).' -> '.FFM::dollar($value['complete_liquidity']).'</td>';
                        } else {
                            $ErrroMessage.= '<td>'.$value['reason'].'</td>';
                        }
                    } else {
                        if(empty($value['reason'])){
                            $ErrroMessage.= '<td>2 The Investment amount below Minimum Investment Amount '.FFM::dollar($minimum_investment_value).' -> '.FFM::dollar($value['complete_liquidity']).'</td>';
                        } else {
                            $ErrroMessage.= '<td>'.$value['reason'].'</td>';
                        }
                    }
                    $ErrroMessage.="</tr>";
                }
            }
            $SuccessMessage.="</table>";
            $ErrroMessage.="</table>";
            if (count($insert_array) <= 0) {
                throw new \Exception($ErrroMessage, 1);
            }
            if ($this_merchant->paymentTerms->count() == 0) {
                if ($this_merchant->ach_pull) {
                    $terms = $$this->merchant->createTerms($this_merchant);
                }
            }
            DB::commit();
            $request->session()->flash('message', $SuccessMessage);
            if($errorCount>0){
                $request->session()->flash('error', $ErrroMessage);
            }

            return 1;
          
     }

     public function check_diff_multi($array1, $array2)
    {
        $result = [];
        foreach ($array1 as $key => $val) {
            if (isset($array2[$key])) {
                if (is_array($val) && $array2[$key]) {
                    $result[$key] = check_diff_multi($val, $array2[$key]);
                }
            } else {
                $result[$key] = $val;
            }
        }

        return $result;
    }  


   public function applyRule2MaxPercentageFundingAmount($admin_investors,$this_merchant)
    {
        $max_investment_per = (Settings::where('keys', 'max_investment_per')->value('values')) ?? 0;
        $maxMerchantFundingAmountForRule2 = ($this_merchant->funded * $max_investment_per) / 100;
        $investors = [];
        if (!empty($admin_investors->toArray())) {
            foreach ($admin_investors->toArray() as $key => $value) {
                if ($value->liquidity > $maxMerchantFundingAmountForRule2){
                    $value->liquidity = $maxMerchantFundingAmountForRule2;
                    $investors[] = $value;
                } else {
                    $investors[] = $value;
                }
            }
        }
        return $investors;
    }

   public function applyRule3MinimumInvestmentValue($max_investors,$this_merchant)
    {
        $minimum_investment_value = (Settings::where('keys', 'minimum_investment_value')->value('values')) ?? 0;
        $filtered_admin_investors = array_filter($max_investors, function ($single) use ($minimum_investment_value) {
            return $single->liquidity >= $minimum_investment_value;
        });
        return $filtered_admin_investors; 
    }

       public function LiquidityBasedInvestmentFunction($investor,$merchant_id,$this_merchant,$liquidity,$insert_array,$MessageData,$company_amount1,$total_investing_amount,$company_wise_investor_liquidity,$company_investments,$removed_investor,$users_array,$userId) {
        try {
            $return['MessageData']                     =$MessageData;
            $return['company_wise_investor_liquidity'] =$company_wise_investor_liquidity;
            $return['company_amount1']                 =$company_amount1;
            $return['insert_array']                    =$insert_array;
            $return['total_investing_amount']          =$total_investing_amount;
            $return['removed_investor']                =$removed_investor;
            $return['users_array']                     =$users_array;
            $MessageData[$investor->id]['reason']='';
            unset($MessageData[$investor->id]['funded']);
            unset($MessageData[$investor->id]['investment_amount']);
            $i=0;
            $existing_amount     = 0;
            $investing_amount    = 0;
            $bal_amount          = 0;
            $existing_investment = $company_investments[$investor->company] ?? 0;
            $current_investment  = 0;
            $investing_amount    = $investor->actual_liquidity / $company_wise_investor_liquidity * ($company_amount1 - $existing_investment);
            InvestmentBeginningArea:
            $i++;
            $investments_list = DB::table('merchant_user')->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)->where('merchant_user.merchant_id', $merchant_id)->pluck('amount', 'users.id');
            if (isset($investments_list[$investor->id])) {
                $existing_amount    = $investments_list[$investor->id];
                $investing_amount  += $existing_amount;
            }
            $investing_amount = round($investing_amount);
            $max_investment_per = (Settings::where('keys', 'max_investment_per')->value('values')) ?? 0;
            $maxMerchantFundingAmountForRule2 = ($this_merchant->funded * $max_investment_per) / 100;
            if($investing_amount>$maxMerchantFundingAmountForRule2){
                $investing_amount=$maxMerchantFundingAmountForRule2;
            }
            if($investing_amount>$investor->actual_liquidity){
                //-1 is to avoid -ve liquidity
                $investing_amount=$investor->actual_liquidity-1;
            }
            $minimum_investment_value = (Settings::where('keys', 'minimum_investment_value')->value('values')) ?? 0;
            if($minimum_investment_value>$investing_amount){
                $removed_investor[]=$investor->id;
                // echo "<br>Cannot invest to $investor->id ($investing_amount) Becouse Minimum Investment Value is $minimum_investment_value";
                $message = "Cannot invest to $investor->id. ";
                $MessageData[$investor->id]['min_funded_amount']='Failed';
                $MessageData[$investor->id]['reason']='Investment amount below Minimum Investment Amount '.FFM::dollar($minimum_investment_value).' -> '.FFM::dollar($investing_amount);
                goto functionExit;
            }
            if (0 >= $investing_amount - $existing_amount) {
                // echo "Cannot invest to $investor->id";
                $message = "Cannot invest to $investor->id. ";
                $MessageData[$investor->id]['max_funded_amount']='Failed';
                $MessageData[$investor->id]['reason']="Investor Share (".FFM::dollar($investing_amount).") Greater Than Company Share (".FFM::dollar($existing_amount).")";
                $removed_investor[]=$investor->id;
                goto functionExit;
            }
            if (isset($investments_list[$investor->id])) {
                $status = DB::table('merchant_user')->where('user_id', $investor->id)->where('merchant_user.merchant_id', $merchant_id)->delete();
            }
            $pre_paid         = 0;
            $rtr5             = $investing_amount * $this_merchant->factor_rate;
            $rtr5             = round($rtr5, 4);
            $syndication_fee  = 0;
            $mgmnt_fee        = 0;
            $s_prepaid_status = 0;
            if (! is_null($investor->global_syndication)) {
                $syndication_fee = $investor->global_syndication;
                if ($investor->s_prepaid_status) {
                    $pre_paid = PayCalc::getsyndicationFee($investor->s_prepaid_status == 2 ? $investing_amount : $rtr5, $syndication_fee);
                }
                $s_prepaid_status = $investor->s_prepaid_status;
            } else {
                $syndication_fee = $this_merchant->m_syndication_fee;
                if ($this_merchant->m_s_prepaid_status) {
                    $pre_paid = PayCalc::getsyndicationFee($this_merchant->m_s_prepaid_status == 2 ? $investing_amount : $rtr5, $syndication_fee);
                }
                $s_prepaid_status = $this_merchant->m_s_prepaid_status;
            }
            if (! is_null($investor->management_fee)) {
                $mgmnt_fee = $investor->management_fee;
            } else {
                $mgmnt_fee = $this_merchant->m_mgmnt_fee;
            }
            $underwriting_fee = $this_merchant->underwriting_fee??0;
            if (! isset($total_investing_amount[$investor->company])) {
                $total_investing_amount[$investor->company] = 0;
            }
            $up_sell_commission =0;
            $investment_amount  =$investing_amount + $investing_amount * ( ($this_merchant->commission+$underwriting_fee+$up_sell_commission) / 100) + $pre_paid;
            $investment_amount  =round($investment_amount,2);
            $total_investing_amount[$investor->company] += $investing_amount;
            if ($total_investing_amount[$investor->company] > $liquidity[$investor->company]) {
                $company = DB::table('users')->where('id', $investor->company)->value('name');
                $throw_message = "Assigned investment for $company more than their liquidity.";
                if (env('APP_ENV') == 'local') {
                    $throw_message .= ' <i>[ investing amount = '.$investment_amount.' / company Investment Amount ='.$liquidity[$investor->company].']</i>';
                }
                $MessageData[$investor->id]['liquidity_check']='Failed';
                $MessageData[$investor->id]['reason']="Investment Amount $investing_amount ".FFM::dollar($investment_amount)." Company Liquidity ".FFM::dollar($liquidity[$investor->company])." Total Investment amount ".FFM::dollar($total_investing_amount[$investor->company]);
                $removed_investor[]=$investor->id;
                goto functionExit;
                // throw new \Exception($throw_message, 1);
            }
            if ($investment_amount > round($investor->actual_liquidity,2)) {
                if($i==1){
                    $total_investing_amount[$investor->company] -= $investing_amount;
                    $new_investment_amount =$investor->actual_liquidity;
                    $investing_amount      =($new_investment_amount-$pre_paid)/(1+($this_merchant->commission+$underwriting_fee+$up_sell_commission)/100);
                    //-1 is to avoid -ve liquidity
                    $investing_amount      =round($investing_amount,2)-1;
                    goto InvestmentBeginningArea;
                }
                $removed_investor[]=$investor->id;
                $MessageData[$investor->id]['liquidity_check']='Failed';
                $MessageData[$investor->id]['reason']="Share ".FFM::dollar($investing_amount).", Investment amount (".FFM::dollar($investment_amount).") Greater Than Liquidity";
                goto functionExit;
            }
            $users_array[] = $investor->id;
            $single = [
                'user_id'                    => $investor->id,
                'amount'                     => $investing_amount,
                'merchant_id'                => $merchant_id,
                'status'                     => 1,
                'invest_rtr'                 => $rtr5,
                'mgmnt_fee'                  => $mgmnt_fee,
                'syndication_fee_percentage' => $syndication_fee,
                'commission_amount'          => $this_merchant->commission / 100 * $investing_amount,
                'commission_per'             => $this_merchant->commission,
                'up_sell_commission_per'     => 0,
                'up_sell_commission'         => 0,
                'under_writing_fee'          => $underwriting_fee / 100 * $investing_amount,
                'under_writing_fee_per'      => $underwriting_fee,
                'creator_id'                 => $userId,
                'pre_paid'                   => $pre_paid,
                's_prepaid_status'           => $s_prepaid_status,
                'creator_id'                 => $userId??null
            ];
            if ($single['amount'] > 2) {
                $insert_array[] = $single;
                $company_amount1-=$investing_amount;
                $company_wise_investor_liquidity-=$investor->actual_liquidity;
                $company_wise_investor_liquidity=round($company_wise_investor_liquidity,2);
                $MessageData[$investor->id]['funded']=$investing_amount;
                $MessageData[$investor->id]['investment_amount']=$investment_amount;
            }
            functionExit:
            $return['MessageData']                     =$MessageData;
            $return['company_wise_investor_liquidity'] =$company_wise_investor_liquidity;
            $return['company_amount1']                 =$company_amount1;
            $return['insert_array']                    =$insert_array;
            $return['total_investing_amount']          =$total_investing_amount;
            $return['removed_investor']                =$removed_investor;
            $return['users_array']                     =$users_array;
            $return['result']                          ='success';
        } catch (\Exception $e) {
            $return['result']=$e->getMessage();
        }
        return $return;
    }

    public function reAssign($request)
    {
             DB::beginTransaction();
            $amount = trim(str_replace(',', '', $request->reassign_amount));
            if ($amount <= 0) {
                throw new \Exception('Amount Should Be Greater Than Zero', 1);
            }
            if (is_numeric($amount)) {
                $request->reassign_amount = $amount;
            }
            $reassign_amount = $request->reassign_amount;
            $invest = MerchantUser::find($request->investment_id);
            $Newinvest = User::find($request->new_investor);
            if($Newinvest->company!=$invest->investors->company) throw new \Exception("Please Select Same Company's Investor To Re Assign", 1);
            $merchant = Merchant::find($invest->merchant_id);
            $existing_amount = $invest->amount;
            $reassign_per = $reassign_amount / $existing_amount;
            $existing_payments = ParticipentPayment::where('payment_investors.user_id', $invest->user_id)->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.merchant_id', $invest->merchant_id)->get();
            $new_user_payments = ParticipentPayment::where('payment_investors.user_id', $request->new_investor)->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.merchant_id', $invest->merchant_id)->get();
            $invest_dest = MerchantUser::where('merchant_id', $invest->merchant_id)->where('user_id', $request->new_investor)->first();
            $deduct_from_ex = 0;
            $invest->share = $invest->amount / $merchant->funded * 100;
            if ($invest_dest) {
                $status4 = MerchantUser::find($invest_dest->id)->update(['amount' => $reassign_per * $invest->amount + $invest_dest->amount, 'invest_rtr' => $reassign_per * $invest->invest_rtr + $invest_dest->invest_rtr, 'commission_amount' => $reassign_per * $invest->commission_amount + $invest_dest->commission_amount, 'commission_per' => $invest->commission_per, 'under_writing_fee_per' => $invest->under_writing_fee_per, 'under_writing_fee' => $reassign_per * $invest->under_writing_fee + $invest_dest->under_writing_fee, 'pre_paid' => $reassign_per * $invest->pre_paid + $invest_dest->pre_paid,'up_sell_commission_per'=>$invest->up_sell_commission_per,'up_sell_commission'=>$reassign_per * $invest->up_sell_commission + $invest_dest->up_sell_commission, 'status' => 1]);
                if ($status4) {
                    if ($reassign_per == 1) {
                        MerchantUser::find($request->investment_id)->delete();
                    } else {
                        $deduct_from_ex = 1;
                    }
                }
            } else {
                $MerchantUserData = ['amount' => $reassign_per * $invest->amount, 'invest_rtr' => $reassign_per * $invest->invest_rtr, 'mgmnt_fee' => $invest->mgmnt_fee ? $invest->mgmnt_fee : $merchant->mgmnt_fee, 'syndication_fee_percentage' => $invest->syndication_fee_percentage ? $invest->syndication_fee_percentage : $merchant->m_syndication_fee, 's_prepaid_status' => $invest->s_prepaid_status, 'commission_amount' => $reassign_per * $invest->commission_amount, 'under_writing_fee' => $reassign_per * $invest->under_writing_fee, 'commission_per' => $invest->commission_per, 'under_writing_fee_per' => $merchant->underwriting_fee, 'pre_paid' => $reassign_per * $invest->pre_paid, 'user_id' => $request->new_investor, 'merchant_id' => $invest->merchant_id,
                    'up_sell_commission_per' => $invest->up_sell_commission_per,'up_sell_commission' => $reassign_per * $invest->up_sell_commission,

                   'status' => 1, 'creator_id' => ($request->user()) ? $request->user()->id : null];
                $status5 = MerchantUser::create($MerchantUserData);
                if ($status5) {
                    if ($reassign_per == 1) {
                        MerchantUser::find($request->investment_id)->delete();
                    } else {
                        $deduct_from_ex = 1;
                    }
                }
            }
            if ($deduct_from_ex) {
                if ($reassign_per == 1) {
                } else {
                    $ParentMerchantUserBefor = MerchantUser::find($request->investment_id);
                    $status4 = MerchantUser::find($request->investment_id)->update(['amount' => $invest->amount - $reassign_per * $invest->amount, 'invest_rtr' => $invest->invest_rtr - $reassign_per * $invest->invest_rtr, 'commission_amount' => $invest->commission_amount - $reassign_per * $invest->commission_amount, 'pre_paid' => $invest->pre_paid - $reassign_per * $invest->pre_paid,  'up_sell_commission' => $invest->up_sell_commission - $reassign_per * $invest->up_sell_commission,'status' => 1]);
                    $ParentMerchantUserAfter = MerchantUser::find($request->investment_id);
                    if ($ParentMerchantUserAfter->amount < 0) {
                        throw new Exception($ParentMerchantUserBefor->investors->name."'s Fund Amount Have Only ".$amount + $ParentMerchantUserAfter->amount, 1);
                    }
                }
            }
            $liquidity1_old = UserDetails::where('user_id', $invest->user_id)->select('liquidity')->first()->toArray();
            $liquidity2_old = UserDetails::where('user_id', $request->new_investor)->select('liquidity')->first()->toArray();
            $MerchantUser = MerchantUserView::wheremerchant_id($invest->merchant_id)->whereinvestor_id($request->new_investor)->first();
            if ($MerchantUser['total_investment'] > $liquidity2_old['liquidity']) {
                throw new \Exception("Available liquidity is insufficient. Increase liquidity to meet the requirements (Available Liquidity ".FFM::dollar($liquidity2_old['liquidity'])."- Required liquidity ".FFM::dollar($MerchantUser['total_investment']).")", 1);
            }
            InvestorHelper::update_liquidity($request->new_investor, 'Re-Assign', $invest->merchant_id);
            InvestorHelper::update_liquidity($invest->user_id, 'Reassigned To New Investor', $invest->merchant_id);
            $liquidity1 = UserDetails::where('user_id', '=', $invest->user_id)->select('liquidity')->first()->toArray();
            $liquidity2 = UserDetails::where('user_id', '=', $request->new_investor)->select('liquidity')->first()->toArray();
            $liquidity_change = $liquidity2_old['liquidity'] - $liquidity2['liquidity'];
            $input_array = ['investor1' => $invest->user_id, 'investor2' => $request->new_investor, 'amount' => $reassign_amount, 'payment' => 0, 'type' => $request->type, 'investor1_old_liquidity' => $liquidity1_old['liquidity'], 'investor2_old_liquidity' => $liquidity2_old['liquidity'], 'investor1_total_liquidity' => $liquidity1['liquidity'], 'investor2_total_liquidity' => $liquidity2['liquidity'], 'merchant_id' => $invest->merchant_id, 'liquidity_change' => $liquidity_change, 'creator_id' => ($request->user()) ? $request->user()->id : null];
            $insert = ReassignHistory::create($input_array);
            $request->session()->flash('message', 'Investor Re-assigned successfully');
            DB::commit();
            return 1;
    }

    public function undoReAssign($request)
    {
            DB::beginTransaction();
            $investor_id = $request->investor_id;
            if (! $investor_id) {
                throw new \Exception('Investor Id Required', 1);
            }
            $merchant_id = $request->merchant_id;
            if (! $merchant_id) {
                throw new \Exception('Merchant Id Required', 1);
            }
            $invest_rtr = 0;
            $commission_amount = 0;
            $undo = false;
            $history = ReassignHistory::where('investor2', $investor_id)->where('merchant_id', $merchant_id)->select('investor1', 'investor2', 'investor1_old_liquidity', 'investor2_old_liquidity', 'amount')->first();
            if (empty($history)) {
                throw new \Exception('Empty History', 1);
            }
            $merchant = Merchant::where('id', $merchant_id)->select('funded', 'factor_rate', 'commission', 'm_mgmnt_fee')->first()->toArray();
            $investment_amount = MerchantUser::where('merchant_id', $merchant_id)->whereIn('user_id', [$investor_id, $history['investor1']])->sum('amount');
            if ($investment_amount) {
                $invest_rtr = $investment_amount * $merchant['factor_rate'];
                $commission_amount = $merchant['commission'] / 100 * $investment_amount;
            }
            $MerchantUserCurrentInvestor = MerchantUser::where('user_id', $investor_id)->where('merchant_id', $merchant_id)->first();
            $MerchantUserCurrentInvestor->amount -= $history['amount'];
            $MerchantUserCurrentInvestor->save();
            if ($MerchantUserCurrentInvestor->amount <= 0) {
                $MerchantUserCurrentInvestor->delete();
            }
            $MerchantUserOldInvestor = MerchantUser::where('user_id', $history['investor1'])->where('merchant_id', $merchant_id)->first();
            if (! $MerchantUserOldInvestor) {
                $MerchantUserOldInvestorData = ['user_id' => $history['investor1'], 'amount' => $history['amount'], 'mgmnt_fee' => $history->investmentData1->management_fee ? $history->investmentData1->management_fee : $merchant['m_mgmnt_fee'], 'merchant_id' => $merchant_id, 'status' => 1, 'creator_id' => $request->user()->id];
                MerchantUser::create($MerchantUserOldInvestorData);
            } else {
                $MerchantUserOldInvestor->amount += $history['amount'];
                $MerchantUserOldInvestor->save();
            }
            ReassignHistory::where('merchant_id', $merchant_id)->where('investor2', $investor_id)->delete();
            $description = 'Undo Re-Assign';
            InvestorHelper::update_liquidity([$investor_id, $history['investor1']], $description, 0);
            $investor2_liquidity = UserDetails::where('user_id', $history['investor1'])->first(['liquidity'])->toArray();
            $result['result'] = 'success';
            $result['message'] = 'Investor Re-assigned Undo successfully';
            $request->session()->flash('message', $result['message']);
            DB::commit();
            return 1;
    }

    public function assignBasedOnPayment($request)
    {
         try {
            DB::beginTransaction();
            if (! $request->all()) {
                throw new \Exception('Empty Request', 1);
            }
            $userId = $request->user()->id;
            $merchant_id = $request->merchant_id;
            $selected_investors = $request->auto_investors;
            $auto_company = $request->auto_company;
            $this_merchant = Merchant::select('max_participant_fund', 'm_mgmnt_fee', 'm_syndication_fee', 'date_funded', 'commission','up_sell_commission', 'm_s_prepaid_status', 'factor_rate', 'underwriting_status', 'underwriting_fee', 'label', 'origination_fee', 'name', 'pmnts', 'funded', 'ach_pull', 'id', 'advance_type', 'payment_amount')->where('id', $merchant_id)->first();
            $select_investors = [];
            $date_start = ($request->date_start) ? $request->date_start : '';
            $date_end = ($request->date_end) ? $request->date_end : '';
            $datetime_end = $request->date_end;
            $datetime_start = $request->date_start;
            $payments = DB::table('payment_investors')->where('merchants.label', $this_merchant->label)->where('participent_payments.payment_date', '>=', $date_start)->where('participent_payments.payment_date', '<=', $date_end)->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id')->join('merchants', 'participent_payments.merchant_id', 'merchants.id')->join('users', 'payment_investors.user_id', 'users.id')->whereIn('payment_investors.user_id', $selected_investors);
            $total_payments = clone $payments;
            $total_payments = $total_payments->groupBy('payment_investors.user_id')->pluck(DB::raw('sum(participant_share-mgmnt_fee) as net_amount'), 'payment_investors.user_id')->toArray();
            $companies = $this->role->allSubAdmin()->pluck('id')->toArray();
            if ((count($total_payments) <= 0)) {
                throw new \Exception('No avaliable payments between '.FFM::date($datetime_start).' & '.FFM::date($datetime_end), 1);
            }
            $total_netamount = array_sum($total_payments);
            $fee = $this_merchant->commission + $this_merchant->m_syndication_fee + $this_merchant->underwriting_fee;
            $net_investment = $total_netamount * 100 / (100 + $fee);
            $net_investment = floor($net_investment * 100) / 100;
            if ($net_investment < 0) {
                throw new \Exception('Need more payment for Merchant Funding', 1);
            }
            $log_id = [];
            $insert_array = [];
            $total_funded_amount = $investing_amount = $tot_payment = 0;
            $liq = 0;
            if (empty($selected_investors)) {
                throw new \Exception('Empty Investors', 1);
            }
            foreach ($selected_investors as $key => $user_id) {
                $m_count = MerchantUser::where('user_id', $user_id)->where('merchant_id', $merchant_id)->count();
                if ($m_count > 0) {
                    continue;
                }
                $fee1 = $this_merchant->commission + $this_merchant->m_syndication_fee + $this_merchant->underwriting_fee;
                $pay = isset($total_payments[$user_id]) ? $total_payments[$user_id] : 0;
                if (! $pay) {
                    continue;
                }
                $investing_amount = $pay * 100 / (100 + $fee1);
                $pay_total = isset($total_payments[$user_id]) ? $total_payments[$user_id] : 0;
                $tot_payment = $tot_payment + $pay_total;
                $syndication_fee = $this_merchant->m_syndication_fee;
                $total_funded_amount += $investing_amount;
                $rtr1 = $this_merchant->factor_rate * $investing_amount;
                $pre_paid = 0;
                if ($this_merchant->m_s_prepaid_status) {
                    $pre_paid = PayCalc::getsyndicationFee($this_merchant->m_s_prepaid_status == 2 ? $investing_amount : $rtr1, $syndication_fee);
                }
                $s_prepaid_status = $this_merchant->m_s_prepaid_status;
                $insert_array = ['user_id' => $user_id, 'amount' => round($investing_amount, 2), 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => $rtr1, 'mgmnt_fee' => $this_merchant->m_mgmnt_fee, 'syndication_fee_percentage' => $syndication_fee, 'commission_amount' => $this_merchant->commission / 100 * $investing_amount, 'commission_per' => $this_merchant->commission, 
                    'up_sell_commission_per'=>0,
                    'up_sell_commission'=>0,
                    'under_writing_fee' => $this_merchant->underwriting_fee / 100 * $investing_amount, 'under_writing_fee_per' => $this_merchant->underwriting_fee, 'creator_id' => $userId, 'pre_paid' => $pre_paid, 's_prepaid_status' => $s_prepaid_status, 'creator_id' => ($request->user()) ? $request->user()->id : null];
                $log_id[] = $user_id;
                MerchantUser::create($insert_array);
            }
            $total_funded_amount = MerchantUser::where('merchant_id', $merchant_id)->sum('amount');
            if ($net_investment != $total_funded_amount) {
                $diff = $net_investment - $total_funded_amount;
                if($diff>0){
                    $HighestInvestor = MerchantUser::where('merchant_id', $merchant_id)->orderByDesc('amount')->first();
                    $HighestInvestor->amount += $diff;
                    $HighestInvestor->save();
                }
            }
            $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
            $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
            if ($OverpaymentAccount) {
                $OverPaymentMerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($OverpaymentAccount->id)->first();
                if (! $OverPaymentMerchantUser) {
                    $item = ['user_id' => $OverpaymentAccount->id, 'amount' => 0, 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => 0, 'mgmnt_fee' => 0, 'syndication_fee_percentage' => 0, 'commission_amount' => 0, 'commission_per' => 0,'up_sell_commission_per'=>0,'up_sell_commission'=>0, 'under_writing_fee' => 0, 'under_writing_fee_per' => 0, 'creator_id' => $userId, 'pre_paid' => 0, 's_prepaid_status' => 1, 'creator_id' => ($request->user()) ? $request->user()->id : null];
                    MerchantUser::create($item);
                }
            }
            $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
            $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
            if ($AgentFeeAccount) {
                $AgentFeeMerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($AgentFeeAccount->id)->first();
                if (! $AgentFeeMerchantUser) {
                    $item1 = ['user_id' => $AgentFeeAccount->id, 'amount' => 0, 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => 0, 'mgmnt_fee' => 0, 'syndication_fee_percentage' => 0, 'commission_amount' => 0, 'commission_per' => 0,'up_sell_commission_per'=>0,'up_sell_commission'=>0, 'under_writing_fee' => 0, 'under_writing_fee_per' => 0, 'creator_id' => $userId, 'pre_paid' => 0, 's_prepaid_status' => 1, 'creator_id' => ($request->user()) ? $request->user()->id : null];
                    MerchantUser::create($item1);
                }
            }
            $companies = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->pluck('company', 'company');
            if ($companies) {
                foreach ($companies as $company_id) {
                    $max_participant = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->where('company', $company_id)->sum('amount');
                    CompanyAmount::updateOrCreate(['company_id' => $company_id, 'merchant_id' => $merchant_id], ['max_participant' => $max_participant]);
                }
            }
            CompanyAmount::where('merchant_id', $merchant_id)->whereNotIn('company_id', $companies)->update(['max_participant' => 0]);
            $funded = $max_participant_fund = CompanyAmount::where('merchant_id', $merchant_id)->sum('max_participant');
            $rtr = $this_merchant->factor_rate * $funded;
            $payment_amount = PayCalc::getPayment($rtr, $this_merchant->pmnts);
            Merchant::where('id', $merchant_id)->update(['funded' => $funded, 'rtr' => $rtr, 'max_participant_fund' => $max_participant_fund, 'payment_amount' => $payment_amount]);
            InvestorHelper::update_liquidity($log_id, 'based_on_payment', $merchant_id);
            if ($total_funded_amount <= 0) {
                throw new \Exception('Already assigned to this merchant', 1);
            }
            if ($this_merchant->paymentTerms->count() == 0) {
                if ($this_merchant->ach_pull) {
                    $terms = $this->merchant->createTerms($this_merchant);
                }
            }
            $message = 'Payment of '.FFM::dollar($total_netamount).' has been collected from '.FFM::date($datetime_start).' till '.FFM::date($datetime_end).' in the Insurance Category, which has been reinvested to '.$this_merchant->name;
            $request->session()->flash('message', $message);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', $e->getMessage());
        }

    }

    public function assignInvestors($request,$tableBuilder,$merchant_id)
    {
        $page_title = 'Assign Investors';
        $fee_values=FFM::fees_array();
        $upsell_comm_values=FFM::fees_array(0,10,1);
        $creator_id = $this->merchant->getCreatorId($merchant_id);
        $funded_investors = MerchantUser::where('merchant_id',$merchant_id)->select('user_id')->pluck('user_id')->toArray();
        $investors = $this->role->allInvestorsLiquidity($creator_id,'','',$funded_investors);
        $investors_data = InvestorAssignHelper::investors_data($creator_id,'',$funded_investors);
        $merchant = Merchant::where('id', $merchant_id)->first();
        $max_participant_fund = $merchant->max_participant_fund;
        $allCompanies = $this->role->allSubAdmins()->pluck('name', 'id')->toArray();
        $total_amount = MerchantUser::where('merchant_id', $merchant_id)->sum('amount');
        $max_participant_fund = $max_participant_fund - $total_amount;
        $share_arr = array();
        $amount_arr = array();        
        
       $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) { $(nRow).addClass( 'row'+iDataIndex ); }",'paging'=>false,'searching'=>false,'footerCallback' => "function(t,o,a,l,m){table = window.LaravelDataTables['dataTableBuilder'] ; var n=this.api(),o=table.ajax.json();$(n.column(2).footer()).html(o.total_participant_amount);$(n.column(3).footer()).html(o.total_mgmnt_fee);o=table.ajax.json();$(n.column(0).footer()).html(o.total);o=table.ajax.json();$(n.column(4).footer()).html(o.total_underwriting_fee);o=table.ajax.json();$(n.column(5).footer()).html(o.total_upsell_commission);o=table.ajax.json();$(n.column(6).footer()).html(o.total_syndication_fee);}"]);

        $tableBuilder = $tableBuilder->columns([
            ['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'name' => 'DT_RowIndex', 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'],
            ['data' => 'name', 'name' => 'name', 'title' => 'Participant Name'], ['data' => 'share', 'name' => 'share', 'title' => 'Participation','orderable' => false, 'searchable' => false], 
            ['data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'title' => 'Management Fee','orderable' => false, 'searchable' => false],
            // ['data' => 'commission', 'name' => 'commission', 'title' => 'Commission','orderable' => false, 'searchable' => false],             
            ['data' => 'underwriting_fee', 'name' => 'underwriting_fee', 'title' => 'Underwriting Fee','orderable' => false, 'searchable' => false], 
            ['data' => 'upsell_commission', 'name' => 'upsell_commission', 'title' => 'Upsell commission','orderable' => false, 'searchable' => false], 
            ['data' => 'syndication_fee', 'name' => 'syndication_fee', 'title' => 'Syndication Fee','orderable' => false, 'searchable' => false],
            ['data' => 'syndication_on', 'name' => 'syndication_on', 'title' => 'Syndication On','orderable' => false, 'searchable' => false],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
            ]);
       
        return ['page_title'=>$page_title,'investors_data'=>$investors_data,'merchant_id'=>$merchant_id,'merchant'=>$merchant,'tableBuilder'=>$tableBuilder,'max_participant_fund'=>$max_participant_fund,'fee_values'=>$fee_values,'upsell_comm_values'=>$upsell_comm_values,'allCompanies'=>$allCompanies,'funded_investors'=>$funded_investors,'creator_id'=>$creator_id];

    }

    public function listInvestorForAssign($request)
    {
          $array = array('id'=>$request->user_id,'share'=>$request->share,'amount'=>$request->amount,'mgmnt_fee_per'=>$request->mgmnt_fee_per,'underwriting_fee_per'=>$request->underwriting_fee_per,'syndication_fee_per'=>$request->syndication_fee_per,'commission'=>$request->commission,'syndication_on'=>$request->syndication_on,'upsell_commission_per'=>$request->upsell_commission_per);
        $data_arr = session('assign_investors_arr');
        if(!empty($data_arr)){
            $participant_arr = array_column($data_arr, 'id'); 
            if(!in_array($request->user_id,$participant_arr)){
             session()->push('assign_investors_arr', $array); 
            }
        }else{
            session()->push('assign_investors_arr', $array); 
        }
          
         return response()->json(['status' => 1]);

    }

    public function investors_data($creator_id, $company = null,$funded_investors=null){
        $investors = $this->role->allInvestorsLiquidity($creator_id,'','',$funded_investors,$company);
        $investors_data = $investors->toArray();
        if (count($investors_data) > 0) {
            foreach ($investors_data as $key => $row) {
                $return_fare[$key] = $row['user_details']['liquidity'];
            }
            array_multisort($return_fare, SORT_DESC, $investors_data);
        }
        return $investors_data;
    }

    public function assignInvestorToMerchant($request)
    {
    
    $assign_investor_array = $request->data;
    $merchant_id=$request->merchant_id;
    $i=0;
    $result = $response_arr = array();    
    $success_message = '';
    $error_message = '';
    $error_flag = 0;
    $success_flag = 0;
    $success_user_ids = [];
    $k=0;
    
    if($assign_investor_array){
    $id_arr = array_unique(array_column($assign_investor_array, 'participant_id'));
    $in_name_arr = User::whereIn('id',$id_arr)->pluck('name','id')->toArray();
    
    if(count($assign_investor_array)>0){
        foreach($assign_investor_array as $key => $inv_arr){ 
        $error=0;
        $name = strtoupper($in_name_arr[$inv_arr['participant_id']]);
        $user_id = $inv_arr['participant_id']; 
        $amount = $inv_arr['amount']; 
        $mgmnt_fee_per = $inv_arr['mgmnt_fee']; 
        $s_prepaid_status = $inv_arr['syndication_on'];
        $syndication_fee_percentage = $inv_arr['syndication_fee'];  
        $commission_per = 0;//$inv_arr['commission'];  
        $underwriting_fee_per = $inv_arr['underwriting_fee'];
        $upsell_commission_per = $inv_arr['upsell_commission_per'];        
        $checkInvestor = MerchantUser::where('user_id', $user_id)->where('merchant_id', $request->merchant_id)->first();
          if (!$checkInvestor){
            try {
              DB::beginTransaction();
              $force_investment = 0;
              if($amount<=0){
                $error=1;
                $error_message .='Participation amount should be greater than zero <br>';
              }
              if($upsell_commission_per>10){
                $error=1;
                $error_message .='upsell commission percentage should be less than 10.it is greater than 10 for '.$name.'<br>';
              }
              if($mgmnt_fee_per>5){
                $error=1;
                $error_message .='Management Fee percentage should be less than 5.it is greter than 5 for '.$name.'<br>';
              }
              // if($commission_per>5){
              //   $error=1;
              //   $error_message .='Commission percentage should be less than 5.it is greter than 5 for '.$name.'<br>';
              // }
              if($error==0){
              $response_arr = InvestorAssignHelper::assignment($user_id,$merchant_id,$amount,$mgmnt_fee_per,$upsell_commission_per,$s_prepaid_status,$syndication_fee_percentage,            $underwriting_fee_per,$force_investment);
              }
               
              DB::commit();
            }catch (\Exception $e) {           
              DB::rollback();            
            }
            if(isset($response_arr['status'])){
                if($response_arr['status']==1){
                    $success_user_ids[$k] =$user_id; 
                    $k++;
                    $success_flag = 1;
                    $success_message .='Assignment successful for '.$name.'<br>';
                }
                else{
                 $error_flag = 1;
                 $error_message .='Assignment failed for '.$name.'<br>';
                 $error_message .=$response_arr['message'].'<br>';
                }
             
            }else{
             $error_flag = 1;
             $error_message .='Assignment failed for '.$name.'<br>';
             
            }
            $result[$i]=$response_arr; 
            $i++;   
            
          }else{
            $error_flag = 1;
            $error_message .= $name. " already assigned <br>";
          }
          // if($success_flag==1 && $error_flag==1){
          //   $message = $success_message.' '.$error_message;
          // }
          // if($success_flag==1 && $error_flag==0){
          //   $message = $success_message;
          // }
          // if($success_flag==0 && $error_flag==1){
          //   $message = $error_message;
          // }

          
         }
         $sess_arr = session('assign_investors_arr');
         foreach($sess_arr as $key => $arrys) {
           if(in_array($arrys['id'],$success_user_ids)){
                unset($sess_arr[$key]);
            }
         }
         $sess_arr = array_values($sess_arr);
         Session::put(['assign_investors_arr' => $sess_arr]);
         
        // Session::put(['assign_investors_arr' => []]);         
         return response()->json(['status' => 1,'success_message'=>$success_message,'error_message'=>$error_message]);


        }
    }else{
            return response()->json(['status' => 1,'success_message'=>"",'error_message'=>'Please select investor for assign']);
        }

    }

     public function assignment($user_id,$merchant_id,$amount=0,$mgmnt_fee=0,$upsell_commission_per=0,$s_prepaid_status=1,$syndication_fee_percentage=0,$underwriting_fee=0,$force_investment=0){

        {
            $this_merchant = Merchant::where('id', $merchant_id)->select('max_participant_fund', 'm_mgmnt_fee', 'm_syndication_fee', 'date_funded', 'commission', 'm_s_prepaid_status', 'factor_rate', 'underwriting_fee', 'underwriting_status', 'origination_fee', 'funded', 'label', 'pmnts', 'id', 'advance_type', 'payment_amount', 'first_payment', 'payment_end_date', 'ach_pull', )->first();

            $user_company = User::select('users.company as company', 'users.name', 'companName.name as company_name')->leftjoin('users as companName', 'companName.id', 'users.company')->where('users.id', $user_id)->first();
             $user_company_syndicate = 0;
            if($user_company){
             if($user_company->company!=null){
             $user_company_syndicate = User::where('id',$user_company->company)->value('syndicate');
             } 
            }
            $merchant_label=$this_merchant->label;
            $labels = DB::table('label')->where('flag', 1)->pluck('id')->toArray();

            $companies_1 = DB::table('company_amount')->where('merchant_id', $merchant_id)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();

            $full_companies=DB::table('users')->whereNotNull('users.company')->where('company','!=',0)->pluck('company','company')->toArray();
            foreach ($full_companies as   $company_id) {
                if(!isset($companies_1[$company_id])){
                    CompanyAmount::firstOrCreate([
                        'merchant_id'=>$merchant_id,
                        'company_id'=>$company_id,
                        'max_participant'=>0,
                    ]);
                }
            }
           
            $company_amount_check_1 = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->whereNotIn('users.company', [284])->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->whereNotIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE])->groupBy('users.company')->pluck(DB::raw('sum(merchant_user.amount) as amount'), 'users.company')->toArray();
            $except_companies = array_keys($company_amount_check_1);
            if (count($except_companies) == 1) {
                $qq = DB::table('company_amount')->where('merchant_id', $merchant_id)->whereIn('company_id', $except_companies)->orderByDesc('company_id')->pluck('max_participant')->toArray();
                $per11 = $qq[0] / $this_merchant->max_participant_fund * 100;
                $test_per = 100 - $per11;
            }
            array_push($except_companies, 284);
            $companies_except = DB::table('company_amount')->where('merchant_id', $merchant_id)->whereNotIn('company_id', $except_companies)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();
            $array = array_filter($companies_1);
            $company_count = count($array) - 1;
            $check_fund = DB::table('company_amount')->where('merchant_id', $merchant_id)->where('company_id', $user_company->company)->pluck('max_participant', 'company_id')->toArray();
            $company_amount = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->whereNotIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE])->where('company', $user_company->company)->groupBy('users.company')->pluck(DB::raw('sum(amount) as amount'), 'users.company')->toArray();
            if ($force_investment == 0) {
                if ($user_company->company == 284  && $user_company_syndicate==0) {
                    $company_a = isset($company_amount[$user_company->company]) ? $company_amount[$user_company->company] : 0;
                    $assign_amount = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->where('company', '!=', 284)->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)->groupBy('users.company')->pluck('users.company')->toArray();
                    if ((isset($check_fund[$user_company->company]) < $amount + $company_a) && isset($check_fund[$user_company->company]) != 0) {
                        //return redirect()->back()->withInput()->withErrors('investor assignment not possible.Other company already assigned to investors.');
                        $response_arr['status'] =0;
                        $response_arr['user_id'] =$user_id;
                        $response_arr['message'] = 'Investor Assignment is not possible as another company has already been assigned to the merchant!';
                        return $response_arr;
                    }
                }
                if ($user_company->company != 284  && $user_company_syndicate==0) {
                    $company_a = isset($company_amount[$user_company->company]) ? $company_amount[$user_company->company] : 0;
                    if (! isset($check_fund[$user_company->company])) {
                        $response_arr['status'] =0;
                        $response_arr['user_id'] =$user_id;
                        $response_arr['message'] = 'No company participant available here!';
                        return $response_arr;
                        //return redirect()->back()->withErrors('No company participant available here');
                    }
                    if ($check_fund[$user_company->company] != 0) {
                        if ($check_fund[$user_company->company] < $company_a + $amount) {
                            $ErrorMessage = 'Maximum Participant Amount for '.$user_company->company_name.' Company is  '.FFM::dollar(($check_fund[$user_company->company] - $company_a));
                            $ErrorMessage .= '<br> Requested Amount is '.FFM::dollar($amount);
                            $ErrorMessage .= '<br> So Investor Assignment not possible!';
                            $response_arr['status'] =0;
                            $response_arr['user_id'] =$user_id;
                            $response_arr['message'] = $ErrorMessage;
                        return $response_arr;

                            //return redirect()->back()->withInput()->withErrors($ErrorMessage);
                        }
                    } else {
                        $response_arr['status'] =0;
                        $response_arr['user_id'] =$user_id;
                        $response_arr['message'] = 'No company participant available here!';
                        return $response_arr;
                        // return redirect()->back()->withInput()->withErrors('No company participant available here');
                    }
                }
            }
            if ($force_investment == 0) {
                $company_name = User::where('id', $user_company->company)->value('name');
                $max_fund_sum = array_sum($check_fund);
            }
            $max_fund = Merchant::select('max_participant_fund')->where('id', $merchant_id)->first();
            $max_participant_fund = $max_fund->max_participant_fund;
            if ($this_merchant->funded == 0 && $request->force_investment == 0) {
                $response_arr['status'] =0;
                $response_arr['user_id'] =$user_id;
                $response_arr['message'] = 'Please Check Force investment and update maximum funded amount';
                return $response_arr;

                // return redirect()->back()->withInput()->withErrors('Please Check Force investment and update maximum funded amount');
            }
            if ($force_investment == 0) {
                $test = [];
                if ($companies_except) {
                    foreach ($companies_except as $key => $value) {
                        $test[$key] = round($value / $max_participant_fund * 100, 2);
                    }
                }
                $company_per_total = array_sum($test);
            }
            $net_investment = $max_1 = $max_p = $total_amount_1 = 0;
            if ($force_investment == 1) {
                if ($this_merchant->funded == 0) {
                    if ($request->max_participant_fund >= $amount) {
                        $net_investment = $amount;
                    } else {
                        $net_investment = $amount - $request->max_participant_fund;
                    }
                } else {
                    if ($request->max_participant_fund < $amount) {
                        $total_amount_1 = $amount - $request->max_participant_fund;
                        $net_investment = $total_amount_1 + $this_merchant->funded;
                        $max_1 = $total_amount_1 + $this_merchant->max_participant_fund;
                    } else {
                        $net_investment = $this_merchant->funded + $amount;
                        $max_1 = $this_merchant->max_participant_fund + $amount;
                    }
                }
                $update_fund = [];
                if ($net_investment < 0) {
                    $message = 'Need more payment for this investors';
                   // $request->session()->flash('error', $message);
                    $response_arr['status'] =0;
                    $response_arr['user_id'] =$user_id;
                    $response_arr['message'] = 'Need more payment for this investors';
                    return $response_arr;

                    //return redirect()->back()->withInput();
                } else {
                    $update_fund['funded'] = $net_investment;
                    if ($this_merchant->max_participant_fund == 0) {
                        $max_p = $net_investment;
                    } else {
                        $max_p = $max_1;
                    }
                    $update_fund['max_participant_fund'] = $max_p;
                    $rtr = $this_merchant->factor_rate * $net_investment;
                    $update_fund['rtr'] = $rtr;
                    $payment_amount = PayCalc::getPayment($rtr, $this_merchant->pmnts);
                    $update_fund['payment_amount'] = $payment_amount;
                    Merchant::where('id', $merchant_id)->update($update_fund);
                }
            }
            $investor_date = $this_merchant->date_funded;
            $commission = $this_merchant->commission;
            //$s_prepaid_status = $s_prepaid_status;
            $company_id = DB::table('users')->where('id', $user_id)->value('company');
            //$underwriting_fee = 0;
            $under_writing_status = $this_merchant->underwriting_status;
            $under_writing_status = json_decode($under_writing_status);
            $companies = $this->role->allCompanies();
            $underwriting = $companies->pluck('id')->toArray();
            $company_arr = $companies->pluck('id')->toArray();
            if (! is_array($company_arr)) {
                $company_arr = [];
            }
            array_unshift($company_arr, '');
            unset($company_arr[0]);
            if (! is_array($under_writing_status)) {
                $under_writing_status = [];
            }
            array_unshift($under_writing_status, '');
            unset($under_writing_status[0]);
           
            $total_amount = MerchantUser::where('merchant_id', $merchant_id)->whereIn('status', [1, 3])->sum('amount');
            $value = round($max_participant_fund - $total_amount, 4);
            if (($max_participant_fund >= round(($total_amount + $amount), 2)) || $force_investment == 1) {
                // $request_arr = $request->only('user_id', 'merchant_id', 'deal_name', 'transaction_type', 'creator_id', 'mgmnt_fee', 's_prepaid_status', 'syndication_fee_percentage');
                $request_arr['amount'] = $amount;
                $request_arr['status'] = 1;
                $request_arr['invest_rtr'] = 0;

                $request_arr['user_id'] = $user_id;
                $request_arr['merchant_id'] = $merchant_id;
                $request_arr['deal_name'] = '';
                $request_arr['transaction_type'] = 0;
                $request_arr['creator_id'] = 1;
                $request_arr['mgmnt_fee'] = $mgmnt_fee;
                $request_arr['s_prepaid_status'] = $s_prepaid_status;
                $request_arr['syndication_fee_percentage'] = $syndication_fee_percentage;
                $request_arr['mgmnt_fee'] = $request_arr['mgmnt_fee']??0;
                $rtr1 = ($request_arr['amount'] * $this_merchant['factor_rate']);
                $request_arr['pre_paid'] = 0;
                $syndication_fee = PayCalc::getsyndicationFee($syndication_fee_percentage, $s_prepaid_status == 2 ? $request_arr['amount'] : $rtr1);                
                $request_arr['under_writing_fee_per'] = $underwriting_fee;
                $request_arr['commission_per'] = $commission;
                $request_arr['up_sell_commission_per'] = $upsell_commission_per;
                if ($s_prepaid_status) {
                    $request_arr['pre_paid'] = $syndication_fee;
                    $request_arr['syndication_fee_percentage'] = $syndication_fee_percentage;
                }
                $request_arr['s_prepaid_status'] = $s_prepaid_status;
                $item = ['user_id' => $request_arr['user_id'], 'amount' => $request_arr['amount'], 'merchant_id' => $request_arr['merchant_id'], 'status' => 1, 'mgmnt_fee' => $request_arr['mgmnt_fee'], 'under_writing_fee_per' => $request_arr['under_writing_fee_per'], 'syndication_fee_percentage' => $request_arr['syndication_fee_percentage'] ?? 0, 'commission_per' => $request_arr['commission_per'], 'under_writing_fee' => 0, 'creator_id' => $request_arr['creator_id'], 'pre_paid' => $syndication_fee, 's_prepaid_status' => $request_arr['s_prepaid_status'], 'creator_id' => Auth::user()->id,'up_sell_commission_per'=>$upsell_commission_per];

                if ($force_investment != 1) {
                    $Investor = User::find($request_arr['user_id']);
                    if (! $Investor) {
                        $response_arr['status'] =0;
                            $response_arr['user_id'] =$user_id;
                            $response_arr['message'] = 'Invalid Investor';
                            return $response_arr;
                        //throw new \Exception('Invalid Investor', 1);
                    }
                    $company_invested_amount = MerchantUserView::where('merchant_id', $request_arr['merchant_id'])->where('company', $Investor->company)->sum('amount');
                    $company_invested_amount += $request_arr['amount'];
                    $user_com_new_max_participant=0;
                    if(isset($companies_1[$Investor->company])){
                        if ($company_invested_amount > $companies_1[$Investor->company]) {
                            $CompanyUser = User::find($Investor->company);
                            if($user_company_syndicate==1){
                                 foreach ($companies_1 as $key => $value1) {
                                    $company[$key]['merchant_id'] = $request_arr['merchant_id'];
                                    $company[$key]['company_id'] = $key;
                            
                                    if ($key == $user_company->company) { 
                                            $inv_amnt = MerchantUserView::where('merchant_id', $request_arr['merchant_id'])->where('company', $user_company->company)->sum('amount');  
                                            if(($check_fund[$user_company->company]-$inv_amnt)>0){
                                                 $actual_amnt = $request_arr['amount']-($check_fund[$user_company->company]-$inv_amnt);

                                            } else{                           
                                            $actual_amnt = $request_arr['amount'];
                                            }
                                             if($actual_amnt>0){
                                            $user_com_new_max_participant = $value1 + $actual_amnt;
                                            $company[$key]['max_participant'] = $value1 + $actual_amnt;
                                        
                                    }
                                            
                                    
                                    }
                                   }

                             $invested_companies = MerchantUserView::where('merchant_id', $request_arr['merchant_id'])->join('users','users.id','merchant_user_views.company')->where('amount','>',0)->where('users.syndicate', 0)->select('merchant_user_views.company')->get()->toArray();

                             if(count($invested_companies)>0){                                
                                 $response_arr['status'] =0;
                                 $response_arr['user_id'] =$user_id;
                                 $response_arr['message'] = 'You can make syndication investment only before company investment';

                                 return $response_arr;
                             }else{

                                //actual_amnt
                             $other_companies = DB::table('company_amount')->join('users','users.id','company_amount.company_id')->where('merchant_id', $request_arr['merchant_id'])
                             ->where('syndicate', 0)
                             ->where('max_participant','>=',0)->whereNotIn('company_id',$invested_companies)->orderByDesc('company_id')->pluck('company_id')->toArray();

                             if(count($other_companies) > 0){

                                if($user_com_new_max_participant>=0){

                                       $max_per = round(($user_com_new_max_participant/$max_participant_fund)*100,2); 
                                       $user_com_new_max_participant = $max_participant_fund*$max_per/100; 

                                        CompanyAmount::updateOrCreate(['company_id' =>$user_company->company, 'merchant_id' => $request_arr['merchant_id']], ['max_participant' => $user_com_new_max_participant]);
                                    }
                                            
                                $other_companies_data = CompanyAmount::whereIn('company_id',$other_companies)->where('merchant_id',$request_arr['merchant_id'])->get();


                                $other_company_total_amount = array_sum(array_column($other_companies_data->toArray(),'max_participant'));

                                 if($other_company_total_amount >=$actual_amnt){

                                    foreach($other_companies_data as $ot_com){
                                        $new_max_participant = $ot_com->max_participant-(($ot_com->max_participant/$other_company_total_amount)*$actual_amnt);

                                        $max_per1 = round(($new_max_participant/$max_participant_fund)*100,2); 
                                        $new_max_participant = $max_participant_fund*$max_per1/100; 
                                        CompanyAmount::updateOrCreate(['company_id' => $ot_com->company_id, 'merchant_id' => $request_arr['merchant_id']], ['max_participant' => $new_max_participant]);
                                        
                                    }

                                 }
                                 
                            }else{
                            
                                $response_arr['status'] =0;
                                $response_arr['user_id'] =$user_id;
                                $response_arr['message'] = 'Dont have company share for adjusting syndicate investment';

                                return $response_arr;
                             }
                         }
                           
                        }else{
                            if (in_array($merchant_label,$labels)){
                            $response_arr['status'] =0;
                            $response_arr['user_id'] =$user_id;
                            $response_arr['message'] = $CompanyUser->name.' Company Share is '.$companies_1[$Investor->company].' Please Change Before Add Investor Or Do Force investment';

                             return $response_arr;
                            }
                            else{
                             $response_arr['status'] =0;
                             $response_arr['user_id'] =$user_id;
                             $response_arr['message'] = $CompanyUser->name.' Company Share is '.FFM::dollar($companies_1[$Investor->company]);

                             return $response_arr;
                            }
                            

                        }
                           
                            
                         
                           // return redirect()->back()->withInput();
                        }
                    }
                }  

                
                $merchant_user = MerchantUser::create($item);
               // $merchant_id = $request['merchant_id'];
                $userId = $user_id;
                $pre_paid_amnt = PayCalc::getsyndicationFee($request_arr['s_prepaid_status'] == 2 ? $request_arr['amount'] : ($request_arr['amount']*$this_merchant['factor_rate']), $request_arr['syndication_fee_percentage']);
                 DB::table('merchant_user')->where('user_id', $userId)->where('merchant_id', $merchant_id)
               ->update([
                   's_prepaid_status' => $request_arr['s_prepaid_status'],'pre_paid'=>$pre_paid_amnt
                ]);
                $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
                $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
                $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
                if ($OverpaymentAccount) {
                    $OverPaymentMerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($OverpaymentAccount->id)->first();
                    if (! $OverPaymentMerchantUser) {
                        $item = ['user_id' => $OverpaymentAccount->id, 'amount' => 0, 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => 0, 'mgmnt_fee' => 0, 'syndication_fee_percentage' => 0, 'commission_amount' => 0, 'commission_per' => 0,'up_sell_commission_per'=>0,'up_sell_commission'=>0,'under_writing_fee' => 0, 'under_writing_fee_per' => 0, 'creator_id' => $userId, 'pre_paid' => 0, 's_prepaid_status' => 1, 'creator_id' => Auth::user()->id];
                        MerchantUser::create($item);
                    }
                }                
                $count = count($companies);
                $bal = $max_participant_fund - (isset($companies_1[$user_company->company]) + ($amount - isset($companies_1[$user_company->company])));
                $company = [];
                $i = 0;
                if (! empty($companies_1)) {
                    foreach ($companies_1 as $key => $value1) {
                        $company[$key]['merchant_id'] = $merchant_id;
                        $company[$key]['company_id'] = $key;
                        if ($force_investment == 1) {
                            if ($key == $user_company->company) {
                                if ($value1 == 0) {
                                    $company[$key]['max_participant'] = $amount;
                                } else {
                                    $z = ($total_amount_1) ? $total_amount_1 : $amount;
                                    $company[$key]['max_participant'] = $value1 + $z;
                                }
                                CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                            }
                        } else {
                            if ($user_company->company == 284) {
                                $company_amount = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)->where('company', $user_company->company)->groupBy('users.company')->pluck(DB::raw('sum(amount) as amount'), 'users.company')->toArray();
                                if (! isset($check_fund[$user_company->company])) {
                                    $company[$key]['merchant_id'] = $merchant_id;
                                    $company[$key]['company_id'] = 284;
                                    $company[$key]['max_participant'] = $amount;
                                    CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                                }
                                if (isset($check_fund[$user_company->company]) < $amount || isset($company_amount[$user_company->company]) > isset($check_fund[$user_company->company])) {
                                    if ($key == $user_company->company) {
                                        if ($value1 != 0) {
                                            if ($check_fund[$user_company->company] < $amount || $company_amount[$user_company->company] > $check_fund[$user_company->company]) {
                                                $company[$key]['max_participant'] = $company_amount[$user_company->company];
                                            }
                                        } elseif ($value1 == 0) {
                                            $company[$key]['max_participant'] = $amount;
                                        }
                                        CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                                    } else {
                                        $company[$key]['merchant_id'] = $merchant_id;
                                        $company[$key]['company_id'] = $key;
                                        $invest_check = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->where('company', $company[$key]['company_id'])->groupBy('users.company')->sum('amount');
                                        if ($invest_check <= 0) {
                                            if ($value1 != 0) {
                                                if ($check_fund[$user_company->company] < $amount) {
                                                    $t_value = $company_amount[$user_company->company];
                                                    $total = $company_amount[$user_company->company] - $companies_1[$user_company->company];
                                                } else {
                                                    $total = $amount;
                                                    $t_value = $total + $companies_1[$user_company->company];
                                                }
                                                $check_fund_1 = DB::table('company_amount')->where('merchant_id', $merchant_id)->where('company_id', 284)->pluck('max_participant', 'company_id')->toArray();
                                                $max_per1 = $check_fund_1[284] / $max_participant_fund * 100;
                                                if (count($test) < 2) {
                                                    $reman_per = $test_per - $max_per1;
                                                } else {
                                                    $reman_per = 100 - $max_per1;
                                                }
                                                $per = $value1 / $max_participant_fund * 100;
                                                $max_participant1 = 0;
                                                $max_participant1 = ($max_participant_fund * $reman_per / 100) * $per / $company_per_total;
                                                $company[$key]['max_participant'] = $max_participant1;
                                            } else {
                                                $company[$key]['max_participant'] = 0;
                                            }
                                            CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                                        }
                                    }
                                }
                            }
                        }
                        $i++;
                    }
                }
                $liquidity_old = UserDetails::sum('liquidity');
                InvestorHelper::update_liquidity($user_id, 'Assign Investor', $merchant_id);
                $response_arr['status'] =1;
                $response_arr['user_id'] =$user_id;
                $response_arr['message'] ='Investor Assigned To Merchant';                
                if ($this_merchant->paymentTerms->count() == 0) {
                    if ($this_merchant->ach_pull) {
                        $terms = $this->merchant->createTerms($this_merchant);
                    }
                }
               return $response_arr;
            } else {
                $response_arr['status'] =0;
                $response_arr['user_id'] =$user_id;
                $response_arr['message'] ='Investor Assignment Failed Because Maximum Available Participant Amount is '.FFM::dollar($value);                
            }
            

            return $response_arr;
        }

    }


 



}
