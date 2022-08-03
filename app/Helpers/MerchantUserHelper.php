<?php

namespace App\Helpers;

use App\AchRequest;
use App\Bank;
use App\CompanyAmount;
use App\Exports\Data_arrExport;
use App\FundingRequests;
use InvestorHelper;
use App\Helpers\MerchantHelper;
use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\Http\Controllers\Admin\Traits\DocumentUploader;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\Admin\StoryMerchantRequest;
use App\Http\Requests\AdminUpdateMerchantRequest;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Label;
use App\Library\Helpers\MerchantExcelBuilder;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\IMarketPlaceRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IMNotesRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Library\Repository\Interfaces\IUserActivityLogRepository;
use App\Library\Repository\InvestorTransactionRepository;
use App\Library\Repository\Interfaces\IInvestorTransactionRepository;
use App\Library\Repository\UserActivityLogRepository;
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

class MerchantUserHelper
{
    public function __construct(ISubStatusRepository $subStatus, IRoleRepository $role, IMerchantRepository $merchant, ILabelRepository $label,IParticipantPaymentRepository $payment)
    {
         $this->subStatus = $subStatus;
         $this->role = $role;
         $this->merchant = $merchant;
         $this->label = $label;
         $this->payment=$payment;
        
    }

    public function createSingleAssignInvestor($request,$merchant_id)
    {
        $labels = DB::table('label')->where('flag', 1)->pluck('id')->toArray();
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $max_participant_fund = Merchant::where('id', $merchant_id)->value('max_participant_fund');
        $total_amount = MerchantUser::where('merchant_id', $merchant_id)->sum('amount');
        $max_participant_fund = $max_participant_fund - $total_amount;
        $merchant = Merchant::where('id', $merchant_id)->first();
        if (! $permission) {
            return redirect()->back();
        }
        $merchants = Merchant::get();
        $statuses = $this->subStatus->getAll()->pluck('name', 'id');
        $creator_id = $this->merchant->getCreatorId($merchant_id);
        $investors = $this->role->allInvestorsLiquidity($creator_id);
        $investors_data = $investors->toArray();
        $companies = $this->role->allCompanies();
      
        if (count($investors_data) > 0) {
            foreach ($investors_data as $key => $row) {
                $return_fare[$key] = $row['user_details']['liquidity'];
            }
            array_multisort($return_fare, SORT_DESC, $investors_data);
        }

        $syndication_fee_values=FFM::fees_array();
        $upsell_commission_values=FFM::fees_array_without_decimal();
        $allCompanies = $this->role->allSubAdmins()->pluck('name', 'id')->toArray();

        return ['statuses'=>$statuses,'investors_data'=>$investors_data,'merchant'=>$merchant,'merchants'=>$merchants,'merchant_id'=>$merchant_id,'max_participant_fund'=>$max_participant_fund,'labels'=>$labels,'syndication_fee_values'=>$syndication_fee_values,'upsell_commission_values'=>$upsell_commission_values,'allCompanies'=>$allCompanies,'creator_id'=>$creator_id];
    }

   public function deleteInvestor($id)
    {
        DB::beginTransaction();
        $return_result = $this->MerchantInvestorDeleteFunction($id);
        if ($return_result['result'] != 'success') {
            throw new Exception($return_result['result'], 1);
        }
        DB::commit();
        return $return['result'] = 'success';

    }
   public function MerchantInvestorDeleteFunction($id)
    {
        try {
            beginningArea:
            $user_id = MerchantUser::where('id', $id)->value('user_id');
            $merchant_id = MerchantUser::where('id', $id)->value('merchant_id');
            $amount = MerchantUser::where('id', $id)->value('amount');
            $amount2 = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.merchant_id', '=', $merchant_id)->where('payment_investors.user_id', '=', $user_id)->sum('payment');
            if (MerchantUser::destroy($id)) {
                $inv_payment = PaymentInvestors::where('payment_investors.user_id', '=', $user_id)->where('payment_investors.merchant_id', $merchant_id)->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id')->get();
                if ($inv_payment) {
                    foreach ($inv_payment as $data) {
                        ParticipentPayment::where('id', $data->participent_payment_id)->update(['final_participant_share' => ($data->final_participant_share) - ($data->participant_share - $data->mgmnt_fee - $data->syndication_fee)]);
                    }
                    $investor = PaymentInvestors::where('user_id', $user_id)->where('merchant_id', $merchant_id)->delete();
                    InvestorHelper::updatePaymentValues($user_id);
                }
                $liquidity_old = UserDetails::sum('liquidity');
                InvestorHelper::update_liquidity($user_id, 'Return Investment', $merchant_id);
                $liquidity_new = UserDetails::sum('liquidity');
                $liquidity_change = $liquidity_new - $liquidity_old;
                $amount = -$amount;
                $model = Merchant::find($merchant_id);
                $aggregated_liquidity = UserDetails::sum('liquidity');
                $final_liquidity = $model->liquidity + $amount;
                $input_array = ['aggregated_liquidity' => $aggregated_liquidity, 'name_of_deal' => 'Investor Deletion', 'final_liquidity' => $final_liquidity, 'member_id' => $merchant_id, 'liquidity_change' => $liquidity_change, 'member_type' => 'merchant', 'description' => 'Delete Investor', 'investor_id' => $user_id];
                if ($liquidity_change != 0) {
                    $insert = LiquidityLog::insert($input_array);
                }
                $model->save();
            }
            $remaining_investors = MerchantUser::where('merchant_id', $merchant_id)->pluck('user_id', 'user_id')->count();
            $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
            $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
            $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
            $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
            $MerchantRemainingInvestor = MerchantUser::where('merchant_id', $merchant_id);
            if ($OverpaymentAccount) {
                $MerchantRemainingInvestor = $MerchantRemainingInvestor->where('user_id', '!=', $OverpaymentAccount->id);
            }
            if ($AgentFeeAccount) {
                $MerchantRemainingInvestor = $MerchantRemainingInvestor->where('user_id', '!=', $AgentFeeAccount->id);
            }
            $MerchantRemainingInvestor = $MerchantRemainingInvestor->count();
            if ($MerchantRemainingInvestor == 0) {
                if ($OverpaymentAccount) {
                    $Account = MerchantUser::where('merchant_id', $merchant_id)->where('user_id', $OverpaymentAccount->id)->first('id');
                    if ($Account) {
                        $id = $Account->id;
                        goto beginningArea;
                    }
                }
                if ($AgentFeeAccount) {
                    $Account1 = MerchantUser::where('merchant_id', $merchant_id)->where('user_id', $AgentFeeAccount->id)->first('id');
                    if ($Account1) {
                        $id = $Account1->id;
                        goto beginningArea;
                    }
                }
            }
            if ($remaining_investors == 0) {
                $ParticipentPayment = ParticipentPayment::where('merchant_id', $merchant_id)->get();
                foreach ($ParticipentPayment as $key => $value) {
                    $value->delete();
                }
            }
            $update_merchant = [];
            $complete_per = PayCalc::completePercentage($merchant_id);
            $update_merchant['complete_percentage'] = $complete_per;
            $p_count = ParticipentPayment::where('merchant_id', $merchant_id)->where('rcode', 0)->where('payment_type', 1)->count();
            $r_count = ParticipentPayment::where('merchant_id', $merchant_id)->where('rcode', '>', 0)->orderByDesc('id')->count();
            if ($r_count == 0) {
                $update_merchant['last_rcode'] = 0;
            }
            if ($p_count == 0) {
                $update_merchant['first_payment'] = null;
                $update_merchant['last_payment_date'] = null;
            }
            Merchant::where('id', $merchant_id)->update($update_merchant);
            $return['result'] = 'success';
        } catch (Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function editSingleAssignInvestor($request,$id)
    {   
        if ($merchant = MerchantUser::find($id)) {
            $s_prepaid_status = Merchant::where('id', $merchant->merchant_id)->value('m_s_prepaid_status');
            $payment_status = ParticipentPayment::where('participent_payments.merchant_id', $merchant->merchant_id)
            ->where('participent_payments.is_payment', 1)
            ->count();
            $pay_status = isset($payment_status) ? $payment_status : '';
            $p_status = ($pay_status > 0) ? 'readonly' : '';
            $p1_status = ($pay_status > 0) ? 'disabled' : '';
            $edit_investment_after_payment = Settings::where('id', 1)->value('edit_investment_after_payment'); 
            $p2_status = ($pay_status > 0) ? (($edit_investment_after_payment > 0) ? '' : 'disabled') : '';
            $commission_percentage = $merchant->commission_per;
            $up_sell_commission_per=($merchant->up_sell_commission_per)?$merchant->up_sell_commission_per:0;
            $under_writing_fee_per = $merchant->under_writing_fee_per;
            $statuses = $this->subStatus->getAll()->pluck('name', 'id');
            $investors = $this->role->allInvestors();
            $amount = $merchant->amount;
            $syndication_fee_values=FFM::fees_array();
            $upsell_commission_values=FFM::fees_array_without_decimal();
            $merchant_arr= Merchant::where('id', $merchant->merchant_id)->select('name','commission','id')->first();
            $UserDetails=UserDetails::where('user_id',$merchant->user_id)->first();
            $liquidity=$UserDetails->liquidity;
            return [
                'statuses'                 =>$statuses,
                'investors'                =>$investors,
                'merchant'                 =>$merchant,
                'MerchantUser'             =>$merchant,
                's_prepaid_status'         =>$s_prepaid_status,
                'commission_percentage'    =>$commission_percentage,
                'under_writing_fee_per'    =>$under_writing_fee_per,
                'p_status'                 =>$p_status,
                'p1_status'                =>$p1_status,
                'p2_status'                =>$p2_status,
                'amount'                   =>$amount,
                'up_sell_commission_per'   =>$up_sell_commission_per,
                'syndication_fee_values'   =>$syndication_fee_values,
                'merchant_arr'             =>$merchant_arr,
                'upsell_commission_values' =>$upsell_commission_values,
                'liquidity'                =>$liquidity,
            ];        
        }
    }
    public function storeAssignSingleInvestor($request)
    {   
            DB::beginTransaction();
            $checkInvestor = MerchantUser::where('user_id', $request->user_id)->where('merchant_id', $request->merchant_id)->first();

            if ($checkInvestor) {
                return redirect()->back()->withInput()->withErrors('Investor Already Assigned To This Merchant');
            }
            $this_merchant = Merchant::where('id', $request->merchant_id)->select('max_participant_fund', 'm_mgmnt_fee', 'm_syndication_fee', 'date_funded', 'commission', 'm_s_prepaid_status', 'factor_rate', 'underwriting_fee', 'underwriting_status', 'origination_fee', 'funded', 'label', 'pmnts', 'id', 'advance_type', 'payment_amount', 'first_payment', 'payment_end_date', 'ach_pull','up_sell_commission')->first();
            $user_company = User::select('users.company as company', 'users.name', 'companName.name as company_name')->leftjoin('users as companName', 'companName.id', 'users.company')->where('users.id', $request->user_id)->first();
            $user_company_syndicate = 0;
            if($user_company){
             if($user_company->company!=null){
             $user_company_syndicate = User::where('id',$user_company->company)->value('syndicate');
             } 
         }
            $companies_1 = DB::table('company_amount')->where('merchant_id', $request->merchant_id)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();
            $full_companies=DB::table('users')->whereNotNull('users.company')->where('company','!=',0)->pluck('company','company')->toArray();
            foreach ($full_companies as   $company_id) {
                if(!isset($companies_1[$company_id])){
                    CompanyAmount::firstOrCreate([
                        'merchant_id'=>$request->merchant_id,
                        'company_id'=>$company_id,
                        'max_participant'=>0,
                    ]);
                }
            }
            $companies_1 = DB::table('company_amount')->where('merchant_id', $request->merchant_id)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();
            $company_amount_check_1 = DB::table('merchant_user')->where('merchant_id', $request->merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->whereNotIn('users.company', [284])->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->whereNotIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE])->groupBy('users.company')->pluck(DB::raw('sum(merchant_user.amount) as amount'), 'users.company')->toArray();
            $except_companies = array_keys($company_amount_check_1);
            if (count($except_companies) == 1) {
                $qq = DB::table('company_amount')->where('merchant_id', $request->merchant_id)->whereIn('company_id', $except_companies)->orderByDesc('company_id')->pluck('max_participant')->toArray();
                $per11 = $qq[0] / $this_merchant->max_participant_fund * 100;
                $test_per = 100 - $per11;
            }
            array_push($except_companies, 284);
            $companies_except = DB::table('company_amount')->where('merchant_id', $request->merchant_id)->whereNotIn('company_id', $except_companies)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();
            $array = array_filter($companies_1);
            $company_count = count($array) - 1;
            $check_fund = DB::table('company_amount')->where('merchant_id', $request->merchant_id)->where('company_id', $user_company->company)->pluck('max_participant', 'company_id')->toArray();
            $company_amount = DB::table('merchant_user')->where('merchant_id', $request->merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->whereNotIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE])->where('company', $user_company->company)->groupBy('users.company')->pluck(DB::raw('sum(amount) as amount'), 'users.company')->toArray();
            if ($request->force_investment == 0) {
                if ($user_company->company == 284 && $user_company_syndicate==0) {
                    $company_a = isset($company_amount[$user_company->company]) ? $company_amount[$user_company->company] : 0;
                    $assign_amount = DB::table('merchant_user')->where('merchant_id', $request->merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->where('company', '!=', 284)->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->whereNotIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE])->groupBy('users.company')->pluck('users.company')->toArray();
                    if ((($check_fund[$user_company->company]) < $request->amount + $company_a) && isset($check_fund[$user_company->company]) != 0) {
                        return redirect()->back()->withInput()->withErrors('Investor Assignment is not possible as another company has already been assigned to the merchant!');
                    }
                }
                if ($user_company->company != 284 && $user_company_syndicate==0) {
                    $company_a = isset($company_amount[$user_company->company]) ? $company_amount[$user_company->company] : 0;
                    if (! isset($check_fund[$user_company->company])) {
                        return redirect()->back()->withErrors('No company participant available here!');
                    }
                    if ($check_fund[$user_company->company] != 0) {
                        if ($check_fund[$user_company->company] < $company_a + $request->amount) {
                            $ErrorMessage = 'Maximum Participant Amount for '.$user_company->company_name.' Company is  '.FFM::dollar(($check_fund[$user_company->company] - $company_a));
                            $ErrorMessage .= '<br> Requested Amount is '.FFM::dollar($request->amount);
                            $ErrorMessage .= '<br> So Investor Assignment not possible!';

                            return redirect()->back()->withInput()->withErrors($ErrorMessage);
                        }
                    } else {
                        return redirect()->back()->withInput()->withErrors('No company participant available here!');
                    }
                }
            }
            if ($request->force_investment == 0) {
                $company_name = User::where('id', $user_company->company)->value('name');
                $max_fund_sum = array_sum($check_fund);
            }
            $max_fund = Merchant::select('max_participant_fund')->where('id', $request->merchant_id)->first();
            $max_participant_fund = $max_fund->max_participant_fund;
            if ($this_merchant->funded == 0 && $request->force_investment == 0) {
                return redirect()->back()->withInput()->withErrors('Please Check Force investment and update maximum funded amount');
            }
            if ($request->force_investment == 0) {
                $test = [];
                if ($companies_except) {
                    foreach ($companies_except as $key => $value) {
                        $test[$key] = round($value / $max_participant_fund * 100, 2);
                    }
                }
                $company_per_total = array_sum($test);
            }
            $net_investment = $max_1 = $max_p = $total_amount_1 = 0;
            if ($request->force_investment == 1) {
                if ($this_merchant->funded == 0) {
                    if ($request->max_participant_fund >= $request->amount) {
                        $net_investment = $request->amount;
                    } else {
                        $net_investment = $request->amount - $request->max_participant_fund;
                    }
                } else {
                    if ($request->max_participant_fund < $request->amount) {
                        $total_amount_1 = $request->amount - $request->max_participant_fund;
                        $net_investment = $total_amount_1 + $this_merchant->funded;
                        $max_1 = $total_amount_1 + $this_merchant->max_participant_fund;
                    } else {
                        $net_investment = $this_merchant->funded + $request->amount;
                        $max_1 = $this_merchant->max_participant_fund + $request->amount;
                    }
                }
                $update_fund = [];
                if ($net_investment < 0) {
                    $message = 'Need more payment for this investors';
                    $request->session()->flash('error', $message);

                    return redirect()->back()->withInput();
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
                    Merchant::where('id', $request->merchant_id)->update($update_fund);
                }
            }
            $investor_date = $this_merchant->date_funded;
            $commission = $this_merchant->commission;
            $up_sell_commission=$request->up_sell_commission_per;
            $s_prepaid_status = $request->s_prepaid_status;
            $company_id = DB::table('users')->where('id', $request->user_id)->value('company');
            $underwriting_fee = 0;
            $companies = $this->role->allCompanies();
            $underwriting = $companies->pluck('id')->toArray();
            $company_arr = $companies->pluck('id')->toArray();
            if (! is_array($company_arr)) {
                $company_arr = [];
            }
            array_unshift($company_arr, '');
            unset($company_arr[0]);
            $underwriting_fee = $request->underwriting_fee;
            $total_amount = MerchantUser::where('merchant_id', $request->merchant_id)->whereIn('status', [1, 3])->sum('amount');
            $value = round($max_participant_fund - $total_amount, 4);
            if (($max_participant_fund >= round(($total_amount + $request->amount), 2)) || $request->force_investment == 1) {
                $request_arr = $request->only('user_id', 'merchant_id', 'deal_name', 'transaction_type', 'creator_id', 'mgmnt_fee', 's_prepaid_status', 'syndication_fee_percentage');
                $request_arr['amount'] = $request->amount;
                $request_arr['status'] = 1;
                $request_arr['invest_rtr'] = 0;
                $request_arr['mgmnt_fee'] = $request_arr['mgmnt_fee']??0;
                $rtr1 = ($request_arr['amount'] * $this_merchant['factor_rate']);
                $request_arr['pre_paid'] = 0;
                $syndication_fee = PayCalc::getsyndicationFee($request->syndication_fee, $s_prepaid_status == 2 ? $request_arr['amount'] : $rtr1);
                $request_arr['under_writing_fee_per'] = $underwriting_fee;
                $request_arr['commission_per'] = $commission;
                $request_arr['up_sell_commission_per'] = $up_sell_commission;
                if ($s_prepaid_status) {
                    $request_arr['pre_paid'] = $syndication_fee;
                    $request_arr['syndication_fee_percentage'] = $request->syndication_fee;
                }
                $request_arr['s_prepaid_status'] = $request->s_prepaid_status;
                $item = ['user_id' => $request_arr['user_id'], 'amount' => $request_arr['amount'], 'merchant_id' => $request_arr['merchant_id'], 'status' => 1, 'mgmnt_fee' => $request_arr['mgmnt_fee'], 'under_writing_fee_per' => $request_arr['under_writing_fee_per'], 'syndication_fee_percentage' => $request_arr['syndication_fee_percentage'] ?? 0, 'commission_amount' => 0, 'commission_per' => $request_arr['commission_per'],'up_sell_commission_per'=>$request_arr['up_sell_commission_per'], 'under_writing_fee' => 0, 'creator_id' => $request_arr['creator_id'], 'pre_paid' => 0, 's_prepaid_status' => $request_arr['s_prepaid_status'], 'creator_id' => ($request->user()) ? $request->user()->id : null];
                if ($request->force_investment != 1) {
                    $Investor = User::find($request_arr['user_id']);
                    if (! $Investor) {
                        throw new \Exception('Invalid Investor', 1);
                    }
                    $company_invested_amount = MerchantUserView::where('merchant_id', $request_arr['merchant_id'])->where('company', $Investor->company)->sum('amount');
                    $company_invested_amount += $request_arr['amount'];
                    $labels = DB::table('label')->where('flag', 1)->pluck('id')->toArray();
                    $merchant_label=$this_merchant->label;
                    if(isset($companies_1[$Investor->company])){
                        if ($company_invested_amount > $companies_1[$Investor->company]) {
                                
                            $CompanyUser = User::find($Investor->company);                            
                            if($user_company_syndicate==1){
                                 foreach ($companies_1 as $key => $value1) {
                                    $company[$key]['merchant_id'] = $request->merchant_id;
                                    $company[$key]['company_id'] = $key;
                            
                                    if ($key == $user_company->company) { 
                                            $inv_amnt = MerchantUserView::where('merchant_id', $request->merchant_id)->where('company', $user_company->company)->sum('amount');  
                                            if(($check_fund[$user_company->company]-$inv_amnt)>0){
                                                 $actual_amnt = $request->amount-($check_fund[$user_company->company]-$inv_amnt);

                                            } else{                           
                                            $actual_amnt = $request->amount;
                                            }
                                            if($actual_amnt>0){
                                            $company[$key]['max_participant'] = $value1 + $actual_amnt;
                                             $max_per = round(($company[$key]['max_participant']/$max_participant_fund)*100,2); 
                                        $company[$key]['max_participant'] = $max_participant_fund*$max_per/100; 
                                        CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $request->merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                                    }
                                    }
                                   }

                             $invested_companies = MerchantUserView::where('merchant_id', $request->merchant_id)->join('users','users.id','merchant_user_views.company')->where('amount','>',0)->where('users.syndicate', 0)->select('merchant_user_views.company')->get()->toArray();
                             if(count($invested_companies)>0){
                                $request->session()->flash('error', 'You can make syndication investment only before company investment.');
                                return redirect()->back()->withInput();
                             }else{
                             $other_companies = DB::table('company_amount')->join('users','users.id','company_amount.company_id')->where('merchant_id', $request->merchant_id)->where('syndicate', 0)->where('max_participant','>', 0)->whereNotIn('company_id',$invested_companies)->orderByDesc('company_id')->pluck('company_id')->toArray();
                             if(count($other_companies) > 0){
                                $other_companies_data = CompanyAmount::whereIn('company_id',$other_companies)->where('merchant_id',$request->merchant_id)->get();
                                $other_company_total_amount = array_sum(array_column($other_companies_data->toArray(),'max_participant'));
                                 if($other_company_total_amount >=$actual_amnt){
                                    foreach($other_companies_data as $ot_com){
                                        $new_max_participant = $ot_com->max_participant-(($ot_com->max_participant/$other_company_total_amount)*$actual_amnt);
                                         $max_per1 = round(($new_max_participant/$max_participant_fund)*100,2); 
                                        $new_max_participant = $max_participant_fund*$max_per1/100; 
                                        CompanyAmount::updateOrCreate(['company_id' => $ot_com->company_id, 'merchant_id' => $request->merchant_id], ['max_participant' => $new_max_participant]);
                                        
                                    }

                                 }
                                 
                             }else{
                               
                            $request->session()->flash('error', 'Dont have company share for adjusting syndicate investment');
                            return redirect()->back()->withInput();
                             }
                         }
                     
                        }else{
                            if (in_array($merchant_label,$labels)){
                            $request->session()->flash('error', $CompanyUser->name.' Company Share is '.$companies_1[$Investor->company].' '.'Please Change Before Add Investor Or Do Force investment');
                            }
                            else{
                            $request->session()->flash('error', $CompanyUser->name.' Company Share is '.$companies_1[$Investor->company]);
                            }
                            return redirect()->back()->withInput();

                        }

                        }
                    }
                }
                $merchant_user = MerchantUser::create($item);
                $merchant_id = $request['merchant_id'];
                $userId = $request['user_id'];
                $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
                $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
                $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
                if ($OverpaymentAccount) {
                    $OverPaymentMerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($OverpaymentAccount->id)->first();
                    if (! $OverPaymentMerchantUser) {
                        $item = ['user_id' => $OverpaymentAccount->id, 'amount' => 0, 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => 0, 'mgmnt_fee' => 0, 'syndication_fee_percentage' => 0, 'commission_amount' => 0, 'commission_per' => 0,'up_sell_commission_per'=>0,'up_sell_commission'=>0,'under_writing_fee' => 0, 'under_writing_fee_per' => 0, 'creator_id' => $userId, 'pre_paid' => 0, 's_prepaid_status' => 1, 'creator_id' => ($request->user()) ? $request->user()->id : null];
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
                $count = count($companies);
                $bal = $max_participant_fund - (isset($companies_1[$user_company->company]) + ($request->amount - isset($companies_1[$user_company->company])));
                $company = [];
                $i = 0;
                if (! empty($companies_1)) {
                    foreach ($companies_1 as $key => $value1) {
                        $company[$key]['merchant_id'] = $request->merchant_id;
                        $company[$key]['company_id'] = $key;
                        if ($request->force_investment == 1) {
                            if ($key == $user_company->company) {
                                if ($value1 == 0) {
                                    $company[$key]['max_participant'] = $request->amount;
                                } else {
                                    $z = ($total_amount_1) ? $total_amount_1 : $request->amount;
                                    $company[$key]['max_participant'] = $value1 + $z;
                                }
                                CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $request->merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                            }
                        } else {
                            if ($user_company->company == 284) {
                                $company_amount = DB::table('merchant_user')->where('merchant_id', $request->merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->whereNotIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE])->where('company', $user_company->company)->groupBy('users.company')->pluck(DB::raw('sum(amount) as amount'), 'users.company')->toArray();
                                if (! isset($check_fund[$user_company->company])) {
                                    $company[$key]['merchant_id'] = $request->merchant_id;
                                    $company[$key]['company_id'] = 284;
                                    $company[$key]['max_participant'] = $request->amount;
                                    CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $request->merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                                }
                                if (isset($check_fund[$user_company->company]) < $request->amount || isset($company_amount[$user_company->company]) > isset($check_fund[$user_company->company])) {
                                    if ($key == $user_company->company) {
                                        if ($value1 != 0) {
                                            if ($check_fund[$user_company->company] < $request->amount || $company_amount[$user_company->company] > $check_fund[$user_company->company]) {
                                                $company[$key]['max_participant'] = $company_amount[$user_company->company];
                                            }
                                        } elseif ($value1 == 0) {
                                            $company[$key]['max_participant'] = $request->amount;
                                        }
                                        CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $request->merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                                    } else {
                                        $company[$key]['merchant_id'] = $request->merchant_id;
                                        $company[$key]['company_id'] = $key;
                                        $invest_check = DB::table('merchant_user')->where('merchant_id', $request->merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->where('company', $company[$key]['company_id'])->groupBy('users.company')->sum('amount');
                                        if ($invest_check <= 0) {
                                            if ($value1 != 0) {
                                                if ($check_fund[$user_company->company] < $request->amount) {
                                                    $t_value = $company_amount[$user_company->company];
                                                    $total = $company_amount[$user_company->company] - $companies_1[$user_company->company];
                                                } else {
                                                    $total = $request->amount;
                                                    $t_value = $total + $companies_1[$user_company->company];
                                                }
                                                $check_fund_1 = DB::table('company_amount')->where('merchant_id', $request->merchant_id)->where('company_id', 284)->pluck('max_participant', 'company_id')->toArray();
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
                                            CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $request->merchant_id], ['max_participant' => $company[$key]['max_participant']]);
                                        }
                                    }
                                }
                            }
                        }
                        $i++;
                    }
                }
                $liquidity_old = UserDetails::sum('liquidity');
                InvestorHelper::update_liquidity($request->user_id, 'Assign Investor', $request->merchant_id);
                $request->session()->flash('message', 'Investor Assigned To Merchant'."<br> <a href='/admin/merchant_investor/edit/".$merchant_user->id."'>Click here to edit</a>");
                if ($this_merchant->paymentTerms->count() == 0) {
                    if ($this_merchant->ach_pull) {
                        $terms = $this->merchant->createTerms($this_merchant);
                    }
                }
            } else {
                $request->session()->flash('error', 'Investor Assignment Failed Because Maximum Available Participant Amount is '.FFM::dollar($value));

                return redirect()->back()->withInput();
            }
            DB::commit();

            return redirect()->route('admin::merchants::view',['id' => $request->merchant_id]);


    }

    public function updateAssignSingleInvestor($request)
    {
        try {
            $force_update=$request['force_update']??false;
            $invest_amount = round($request->amount,2);
            if($invest_amount<=0) throw new \Exception("Please enter valid funding amount, it should be greater than zero", 1);
            $this_merchant = Merchant::where('id', $request->merchant_id)->select('funded','max_participant_fund','factor_rate', 'max_participant_fund', 'date_funded', 'commission', 'm_s_prepaid_status', 'factor_rate', 'payment_amount')->first();
            $merchant_investor = MerchantUser::find($request->id);
            $model = Merchant::find($request->merchant_id);
            $paid_amount = ($this_merchant->factor_rate!=0) ? $merchant_investor->paid_participant_ishare / $this_merchant->factor_rate :0;
            if ($invest_amount <= $paid_amount) {
                throw new Exception('The funding amount should not be less than the principal amount which is extracted from the payment amount received.', 1);
            }
            $s_prepaid_status = $request->s_prepaid_status;
            $pre_paid = 0;
            if ($s_prepaid_status) {
                if ($s_prepaid_status == 1) {
                    $amount_pre = $invest_amount * $this_merchant->factor_rate;
                } else {
                    $amount_pre = $invest_amount;
                }
                $syndication_fee = PayCalc::getsyndicationFee($request->syndication_fee, $amount_pre);
                $merchant_investor->pre_paid = $syndication_fee;
            } else {
                $merchant_investor->pre_paid = 0;
            }
            $merchant_investor->mgmnt_fee                  = isset($request->mgmnt_fee) ? $request->mgmnt_fee               : $merchant_investor->mgmnt_fee;
            $merchant_investor->syndication_fee_percentage = isset($request->syndication_fee) ? $request->syndication_fee   : $merchant_investor->syndication_fee;
            $merchant_investor->s_prepaid_status           = isset($request->s_prepaid_status) ? $request->s_prepaid_status : $merchant_investor->s_prepaid_status;
            $merchant_investor->under_writing_fee_per      = isset($request->underwriting_fee) ? $request->underwriting_fee : $merchant_investor->underwriting_fee;
            $merchant_investor->under_writing_fee          = ($merchant_investor->under_writing_fee_per * $invest_amount) / 100;
            $merchant_investor->commission_per             = $this_merchant->commission;
            $merchant_investor->commission_amount          = ($this_merchant->commission * $invest_amount) / 100;
            $merchant_investor->up_sell_commission_per     = $request->up_sell_commission_per;
            $merchant_investor->up_sell_commission         = ($request->up_sell_commission_per * $invest_amount) / 100;
            $merchant_investor->invest_rtr                 = $invest_amount * $this_merchant->factor_rate;
            $merchant_investor->amount                     = $invest_amount;
            $merchant_investor->save();
            $total_funded_amount=MerchantUser::where('merchant_id',$request->merchant_id)->sum('amount');
            if(!$force_update){
                if($total_funded_amount>$this_merchant->max_participant_fund) throw new \Exception("Merchant have only ".FFM::dollar($this_merchant->max_participant_fund).' `Maximum Participant Share`, so value must be less than or equal to `Maximum Participant Share` for total investment', 1);
            }
            if($force_update){
                $merchant_investor->CompanyAmount->max_participant=$merchant_investor->CompanyOtherInvestors->sum('amount')+$merchant_investor->amount;
                $merchant_investor->CompanyAmount->save();
                $max_participant_fund = $merchant_investor->TotalCompanyAmounts->sum('max_participant');
                $funded               = $model->funded;
                if($max_participant_fund>$funded){
                    throw new \Exception("Merchant have only ".FFM::dollar($funded)." funded amount, so value must be less than or equal to `funded` for total investment", 1);
                }
                $model->update(['max_participant_fund'=>$max_participant_fund]);
            }
            $liquidity_old = UserDetails::sum('liquidity');
            InvestorHelper::update_liquidity($merchant_investor->user_id, 'Investor Details Updation', $request->merchant_id);
            $liquidity_new = UserDetails::sum('liquidity');
            $liquidity_change = $liquidity_new - $liquidity_old;
            $aggregated_liquidity = UserDetails::sum('liquidity');
            $final_liquidity = $model->liquidity + $liquidity_change;
            $input_array = ['aggregated_liquidity' => $aggregated_liquidity, 'name_of_deal' => 'Investor Details Updation', 'final_liquidity' => $final_liquidity, 'member_id' => $request->merchant_id, 'liquidity_change' => $liquidity_change, 'member_type' => 'merchant', 'description' => 'Investor Details Updation'];
            if ($liquidity_change != 0) {
                $insert = LiquidityLog::insert($input_array);
            }
            $model->liquidity += $liquidity_change;
            $model->save();
            $return['result']='success';
        } catch (\Exception $e) {
            $return['result']=$e->getMessage();
        }
        return $return;
    }

    public function getInvestmentSum(User $user, $statuses = [], $default_rate = 0, $sub_status = [],$label=[])
    {
        $details = MerchantUser::whereIn('merchant_user.status', $statuses)->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchants.active_status', 1)->where('merchant_user.user_id', $user->id)->whereHas('investors')->select(DB::raw('SUM( (merchant_user.invest_rtr *  (( merchant_user.mgmnt_fee)/100) )  ) as total_fee'), DB::raw('COUNT(merchants.id) as merchant_count'), DB::raw('SUM(merchant_user.invest_rtr) as total_rtr'),DB::raw('SUM(merchant_user.actual_paid_participant_ishare) as actual_paid_participant_ishare'), DB::raw('SUM(merchant_user.invest_rtr *('.$default_rate.') ) as default_rate_invest_rtr
                '), DB::raw('SUM(merchant_user.amount) as invested_amount'), DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as total_investment'), DB::raw('SUM(merchant_user.under_writing_fee) as under_writing_fee_total'), DB::raw('sum(merchant_user.pre_paid) as pre_paid_t'), DB::raw('sum(merchant_user.commission_amount) as commission_total'), DB::raw('sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd'),  DB::raw(' 
				SUM(  
					(  
						( 
							(invest_rtr * (100 - merchant_user.mgmnt_fee) /100 )  
							- 
							(  merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) 
	                    )
	                )  
	                * IF( advance_type="weekly_ach", 52, 255 ) / merchants.pmnts 
                ) 
                / SUM(  merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) * 100 

                AS bleded_i_rate '));
        if (! empty($sub_status)) {
            $details = $details->whereIn('sub_status_id', [1, 5, 16, 2, 13, 12]);
        }
        if(! empty($label)){
            $details = $details->whereIn('label', $label);
        }
        $details = $details->first();

        return $details;
    }

    public function getDefaultInvestmentSum(User $user, $statuses = [], $subStatusIds = [4, 22], $default_rate = 0)
    {
        return MerchantUser::whereIn('merchant_user.status', $statuses)->whereHas('investors', function ($query) use ($user) {
            $query->where('merchant_user.user_id', $user->id);
        })->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchants.active_status', 1)->whereIn('merchants.sub_status_id', $subStatusIds)->select(DB::raw('SUM( (merchant_user.invest_rtr *  ( merchant_user.mgmnt_fee/100) )  ) as total_default_fee'), DB::raw('SUM(merchant_user.invest_rtr) as total_default_rtr'), DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as total_investment'), DB::raw(' 
				SUM( 
					( 
						( merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                        -
                        ( 
                            IF ( 
                                ( merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission )
                                <
                                ( merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ), 
                                ( merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission ),
                                ( merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee )

                            ) 
                        )
                    )
                ) as default_amount'), DB::raw('
				SUM( 
					(
						( 
							merchant_user.invest_rtr 
							+ 
							IF( old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate - factor_rate) ) , 0 )
						)
						-
						( 
							merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100
							+  
							IF( old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate - factor_rate ) * merchant_user.mgmnt_fee / 100  ) , 0 )
						)
						-
						( 
							IF( merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee, merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee, 0 )
						)
                    )

                ) as total_rtr'), DB::raw('sum(merchant_user.pre_paid) as pre_paid_t'), DB::raw('sum(merchant_user.invest_rtr *('.$default_rate.') ) as default_rate_invest_rtr
                '), DB::raw('sum(merchant_user.commission_amount) as commission_total'), DB::raw('sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd'))->first();
    }

    public function getCurrentInvestment(int $userId, array $statuses = [1, 3], array $subStatusIds = [4, 22], bool $returnSum = true, float $investPercentage = 0)
    {
        $query = MerchantUser::where('merchant_user.user_id', $userId)->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->whereIn('merchant_user.status', $statuses)->whereNotIn('merchants.sub_status_id', $subStatusIds);
        if ($investPercentage > 0) {
            $query->where('merchant_user.complete_per', '<', 99);
        }
        if ($returnSum) {
            return $query->select(DB::raw(' SUM( merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid +merchant_user.up_sell_commission) as invested_amount'), DB::raw('SUM(merchant_user.commission_amount) AS commission_amount'), DB::raw('SUM(merchant_user.invest_rtr) AS invest_rtr'), DB::raw('count(merchants.id) AS merchants'), DB::raw('SUM( merchant_user.actual_paid_participant_ishare ) as paid_participant_ishare'))->first();
        }

        return $query->select('merchant_user.*')->get();
    }

    public function getFundsByDate(int $userId = 0, string $start_date = '', string $end_date = '')
    {
        return MerchantUser::leftJoin('merchants', 'merchant_user.merchant_id', 'merchants.id')->where('merchant_user.user_id', $userId)->whereIn('merchant_user.status', [1, 3])->groupBy(DB::raw('MONTH(merchants.date_funded)'))->where('merchants.date_funded', '>=', $start_date)->where('merchants.date_funded', '<=', $end_date)->select(DB::raw('SUM(merchant_user.amount) as funded'), DB::raw('MONTH(merchants.date_funded) as month'), DB::raw('YEAR(merchants.date_funded) as year'), DB::raw('SUM(merchant_user.invest_rtr-merchant_user.invest_rtr * merchant_user.mgmnt_fee /100) as rtr_month'))->get();
    }

    public function getInvestorMerchants(int $userId = 0, array $subInvestors = [], $subStatusIds = 0, $status = [1, 3], $merchantStatus = 1, $keyword = null, $request_from = null, $start_date = null, $end_date = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $query = MerchantUser::leftJoin('merchants', 'merchants.id', 'merchant_user.merchant_id')->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->whereIn('merchant_user.status', $status)->where('merchants.active_status', $merchantStatus);
        if (empty($permission)) {
            $query->whereIn('merchant_user.user_id', $subInvestors);
        }
        $query->where('merchant_user.user_id', $userId)->select('merchants.complete_percentage as complete_per', 'merchants.complete_percentage as complete_percentage', 'merchants.id', DB::raw('upper(merchants.name) as name'),'sub_status_id', 'date_funded', 'merchants.pmnts', 'merchants.rtr', 'merchants.annualized_rate', 'merchant_user.commission_per as commission','merchant_user.up_sell_commission','merchant_user.up_sell_commission_per', 'advance_type', 'sub_statuses.name as sub_status_name', 'factor_rate', 'funded', 'merchant_user.amount', 'invest_rtr', 'paid_participant_ishare', 'actual_paid_participant_ishare', 'paid_mgmnt_fee', 'merchants.last_payment_date', 'syndication_fee_percentage', 'under_writing_fee_per', 'commission_per', 'commission_amount', 'payment_amount', DB::raw('sum(actual_paid_participant_ishare - paid_mgmnt_fee) as ctd'), DB::raw('merchant_user.invest_rtr*merchant_user.mgmnt_fee/100 as mag_fee'), DB::raw('sum(merchant_user.pre_paid) as syndication_fee'), DB::raw('sum(merchant_user.commission_amount) as commission_amount'), DB::raw('sum(merchant_user.under_writing_fee) as under_writing_fee'), DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as tot_investment'), DB::raw(' 
				((( 
					( 
						( 
							( invest_rtr * ( 100 - merchant_user.mgmnt_fee ) / 100 ) 
                            -
                            (  merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) 

                        )
                    )   * IF(advance_type="weekly_ach",52, 255) / merchants.pmnts
                )/ (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) )*100)as annualized_rate'));
        if ($subStatusIds && is_array($subStatusIds)) {
            $query->wherein('sub_status_id', $subStatusIds);
        } elseif (! empty($subStatusIds)) {
            $query->where('sub_status_id', $subStatusIds);
        }
        if ($start_date != null) {
            $query->where('date_funded', '>=', $start_date);
        }
        if ($end_date != null) {
            $query->where('date_funded', '<=', $end_date);
        }
        $display_value = Auth::user()->display_value;
        if (! empty($keyword)) {
            $num_keyword = str_replace('$', '', $keyword);
            $amount_keyword = str_replace(',', '', $num_keyword);
            $perc_keyword = str_replace('%', '', $keyword);
            if ($request_from == 'web') {
                $query->where(function ($q) use ($keyword, $amount_keyword, $perc_keyword, $display_value) {
                    $q->where(DB::raw("IF('$display_value'='mid',merchant_id,merchants.name)"), 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw("DATE_FORMAT(`date_funded`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw('ROUND(amount,2)'), 'LIKE', '%'.$amount_keyword.'%')->orWhere(DB::raw('ROUND(commission_per,2)'), 'LIKE', '%'.$perc_keyword.'%')->orWhere('sub_statuses.name', 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw('ROUND(under_writing_fee,2)'), 'LIKE', '%'.$amount_keyword.'%')->orWhere(DB::raw('ROUND(pre_paid,2)'), 'LIKE', '%'.$amount_keyword.'%')->orWhere(DB::raw('ROUND(complete_percentage,2)'), 'LIKE', '%'.$perc_keyword.'%')->orWhere(DB::raw('ROUND(factor_rate,2)'), 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw('ROUND( 
				((( 
					( 
						( 
							( invest_rtr * ( 100 - merchant_user.mgmnt_fee ) / 100 ) 
                            -
                            (  merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) 

                        )
                    )   * IF(advance_type="weekly_ach",52, 255) / merchants.pmnts
                )/ (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) )*100),2)'), 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw('ROUND(`actual_paid_participant_ishare` - `paid_mgmnt_fee`,2)'), 'LIKE', '%'.$amount_keyword.'%')->orWhere(DB::raw('ROUND(`invest_rtr` - `invest_rtr`*`mgmnt_fee`/100)'), 'LIKE', '%'.$amount_keyword.'%')->orWhere(DB::raw("DATE_FORMAT(`last_payment_date`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%');
                });
            } else {
                $query->where('merchants.name', 'LIKE', '%'.$keyword.'%');
            }
        }
        $sumQuery = clone $query;
        $sumQuery->select(DB::raw('SUM(paid_mgmnt_fee) as paid_mgmnt_fee'), DB::raw('SUM(merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100) as mgmnt_fee_amount'), DB::raw('SUM(actual_paid_participant_ishare-paid_mgmnt_fee) as actual_paid_participant_ishare'), DB::raw('SUM(commission_amount) as commission_amount'), DB::raw('SUM(amount) as amount'), DB::raw('SUM(invest_rtr) as invest_rtr'), DB::raw('count(*) as count'))->first();

        return [$query, $sumQuery];
    }

    public function investorFunds(int $merchantId = 0, int $userId = 0)
    {
        $query = MerchantUser::where('id', '>', 0);
        if (! empty($merchantId)) {
            $query->where('merchant_id', $merchantId);
        }
        if (! empty($userId)) {
            $query->where('user_id', $userId);
        }

        return $query->sum('amount');
    }

    public function getInvestmentByFields(string $field, array $objects = [], int $lender = 0, string $filter = '')
    {
        $investors = User::investors()->where('company', Auth::user()->id)->pluck('id')->toArray();
        $result = [];
        foreach ($objects as $object) {
            $query = MerchantUser::where('status', 1)->whereHas('merchant', function ($inner) use ($object, $field, $lender) {
                $inner->where('active_status', 1)->where($field, $object->id);
                if (! empty($lender)) {
                    $inner->where('lender_id', $lender);
                }
            });
            if (\Auth::user()->hasRole('company')) {
                $query->whereIn('user_id', $investors);
            }
            if (isset($investor_filter) && $investor_filter != '') {
                $query->where('user_id', $investor_filter);
            }
            $investedAmount = $query->sum('amount');
            $result[] = ['name' => $object->name, 'amount' => round($investedAmount, 2)];
        }

        return $result;
    }

    public function getInvestorMerchantsViewDetails(int $userId = 0, int $merchantId = 0)
    {
        $query = MerchantUser::leftJoin('merchants', 'merchants.id', 'merchant_user.merchant_id')->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');
        $query = $query->where('merchant_user.user_id', $userId)->where('merchants.id', $merchantId)->select('merchants.id as mid', 'merchant_user.total_agent_fee', 'merchants.name', 'sub_status_id', 'sub_statuses.name as sub_status', 'date_funded', 'merchants.pmnts', 'merchants.rtr', 'factor_rate', 'funded', 'payment_amount',DB::raw('sum(commission_per+up_sell_commission_per) as commission_per'), 'amount', 'invest_rtr', 'advance_type', 'mgmnt_fee', 'pre_paid', 'paid_participant_ishare', 'actual_paid_participant_ishare', 'merchants.last_payment_date', 'syndication_fee', 'syndication_fee_percentage', 'under_writing_fee', 'under_writing_fee_per','merchant_user.up_sell_commission', 'commission_amount', DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as tot_investment
                '), DB::raw('(merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid + merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as total_fee
                '), DB::raw('(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as management_fee
                '), DB::raw('

               ((((invest_rtr * (100-merchant_user.mgmnt_fee)/100) 
                 -
                 (  merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) 

                 )

               )   * IF(advance_type="weekly_ach",52, 255) / merchants.pmnts
            ) as tot_profit '))->first();

        return $query;
    }


    public function getInvestedMarketplaceMerchants(int $userId = 0)
    {
        $query = MerchantUser::leftJoin('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchant_user.user_id', $userId)->where('active_status', 1)->where('marketplace_status', 1)->where('merchants.sub_status_id', '=', 1)->get();

        return $query;
    }

    public function getNotInvestedMarketplaceMerchants($req = null)
    {
        if (Auth::user()) {
            $filtered_merchants = MerchantUser::where('user_id', Auth::user()->id)->groupBy('id')->get();
            $merchants = Merchant::leftjoin('merchants_details','merchants_details.merchant_id','merchants.id')->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->select('merchants.*')->where('active_status', 1)->where('marketplace_status', 1)->where('merchants.sub_status_id', '=', 1)->whereNotIn('merchants.id', $filtered_merchants->pluck('merchant_id')->toArray())->distinct();
        } else {
            $merchants = $merchants = Merchant::leftjoin('merchants_details','merchants_details.merchant_id','merchants.id')->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->select('merchants.*')->where('active_status', 1)->where('marketplace_status', 1)->where('merchants.sub_status_id', '=', 1)->distinct();
        }
        if (isset($req['industry_id']) && $req['industry_id'] != 'null') {
            $merchants->where('industry_id', $req['industry_id']);
        }
        if (isset($req['monthly_revenue']) && $req['monthly_revenue'] != 'null') {
            $rray = explode('-', $req['monthly_revenue']);
            $merchants->where('merchants_details.monthly_revenue', '>=', $rray[0]);
            if (isset($rray[1])) {
                $merchants->where('merchants_details.monthly_revenue', '<=', $rray[1]);
            }
        }
        if (isset($req['factor_rate']) && $req['factor_rate'] != 'null') {
            $rray = explode(';', $req['factor_rate']);
            $merchants->where('factor_rate', '>=', $rray[0]);
            if (isset($rray[1])) {
                $merchants->where('factor_rate', '<=', $rray[1]);
            }
        }
        //$merchants->where('date_funded', '>', date('Y-m-d'));
        return $merchants->latest()->paginate(100);
    }

    public function getCurrentInvestmentByStatus(int $userId, array $statuses = [1, 3], array $subStatusIds = [4, 22])
    {
        $query = MerchantUser::where('merchant_user.user_id', $userId)->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->whereIn('merchant_user.status', $statuses)->join('industries', 'industries.id', 'merchants.industry_id')->whereNotIn('merchants.sub_status_id', $subStatusIds);
        $data = [];
        $inArray = [];
        $other = clone $query;
        for ($i = 7; $i <= 25; $i++) {
            $inArray[$i] = $i;
        }
        $other = $other->select(DB::raw(' SUM( merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid+merchant_user.up_sell_commission ) as invested_amount'))->whereIn('industry_id', $inArray);
        if ($other->count() > 0) {
            $other = $other->first()->toArray();
        } else {
            $other = [];
        }
        $query = $query->whereNotIn('industry_id', $inArray)->select('merchants.industry_id', 'industries.name', DB::raw(' SUM( merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid +merchant_user.up_sell_commission) as invested_amount'))->groupBy('merchants.industry_id')->get()->toArray();
        $data['other'] = $other;
        $data['list'] = $query;

        return $data;
    }

 
 
    public function deleteMultiInvestment($request)
    {
         try {
            DB::beginTransaction();
            $merchant_id = $request->merchant_id;

            $payment_count = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.merchant_id', '=', $merchant_id)->count();
            if ($payment_count > 0) {
                $request->session()->flash('error', 'If you want to delete investment you need to delete payments first');

                return response()->json(['status' => 0, 'msg' => 'If you want to delete investment you need to delete payments first']);
            }
            $id_array = $request->multi_id;
            if (! empty($id_array)) {
                foreach ($id_array as $id) {
                    $return_result = MerchantUserHelper::MerchantInvestorDeleteFunction($id);
                    if ($return_result['result'] != 'success') {
                        throw new Exception($return_result['result'], 1);
                    }
                }
                $merchant_dt = Merchant::select('complete_percentage', 'sub_status_id')->where('id', $request->merchant_id)->first();
                if ($merchant_dt->complete_percentage < 100 && $merchant_dt->sub_status_id != 1) {
                    $logArray = ['merchant_id' => $request->merchant_id, 'old_status' => $merchant_dt->sub_status_id, 'current_status' => 1, 'description' => 'Merchant Status changed to Active Advance by system ', 'creator_id' => $request->user()->id];
                    $log = MerchantStatusLog::create($logArray);
                    Merchant::where('id', $request->merchant_id)->update(['sub_status_id' => 1, 'last_status_updated_date'=>$log->created_at]);
                }
                $request->session()->flash('message', 'Assigned Investor deleted successfully !');
                $msg = 'Assigned Investor deleted successfully';
                $investors_count = MerchantUser::where('merchant_id', $merchant_id)->count();
                if ($investors_count == 0) {
                    $delete_ach_schedules = $this->merchant->deleteAllTerms($merchant_id);
                }
                \DB::commit();
                return response()->json(['status' => 1, 'msg' => $msg]);

            }
        } catch (\Exception $ex) {
            DB::rollback();
            $request->session()->flash('error', $ex->getMessage());

            return response()->json(['status' => 0, 'msg' => $ex->getMessage()]);
        }

    }

    public function investorMerchantStatus($request)
    {
        $update = MerchantUser::find($request->id)->update(['status' => $request->status]);
        $investoremail = User::select('email')->where('id', $request->investor_id)->first();
        $merchantdetails = Merchant::where('id', $request->merchant_id)->first();
        if ($update) {
            InvestorHelper::update_liquidity($request->investor_id, 'Assign Investor', $request->merchant_id);
            if ($request->status == 1) {
                $merchant = MerchantUser::where('id', $request->id)->first();
                if (Schema::hasTable('merchant_fund_details')) {
                    $merchant_fund = DB::table('merchant_fund_details')->where('merchant_id', $merchant->merchant_id)->first();
                }
                if ($merchantdetails->marketplace_permission == 1) {
                    Merchant::find($merchant->merchant_id)->update(['marketplace_permission' => 0, 'm_mgmnt_fee' => $merchant->mgmnt_fee, 'm_syndication_fee' => $merchant->syndication_fee_percentage, 'pmnts' => $merchant_fund->pmnts, 'commission' => $merchant->commission_per, 'underwriting_fee' => $merchant->under_writing_fee_per, 'factor_rate' => $merchant_fund->factor_rate, 'rtr' => $merchantdetails->funded * $merchant_fund->factor_rate]);
                }
                $new_mails[] = $investoremail;
                $message['to_mail'] = $new_mails;
                $message['subject'] = 'Funding request approved | Velocitygroupusa';
                $message['title'] = 'Funding request approved';
                $message['status'] = 'funding_approval';
                $message['content'] = 'Your fund request is approved!';
                $message['merchant_id'] = $request->merchant_id;
                $message['investor'] = $request->investor_name;
                $message['unqID'] = unqID();
                $email_template = Template::where([
                    ['temp_code', '=', 'FREQA'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $bcc_mails[] =  $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $settings = Settings::where('keys', 'admin_email_address')->first();
                    $admin_email = $settings->values;
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc'] = [];
                    $message['to_mail'] = $admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
                if ($merchantdetails->paymentTerms->count() == 0) {
                    $this_merchant = Merchant::select('id', 'pmnts', 'advance_type', 'payment_amount', 'date_funded', 'first_payment', 'payment_end_date', 'ach_pull', )->where('id', $request->merchant_id)->first();
                    if ($this_merchant->ach_pull) {
                        $terms = $this->merchant->createTerms($this_merchant);
                    }
                }
                $request->session()->flash('message', 'Investor funding  approved successfully!');
            } elseif ($request->status == 4) {
                $merchant_id = $request->merchant_id;
                $id_array[] = $request->id;
                $count = MerchantUser::where('merchant_id', $merchant_id)->count();
                $investor_count = count($id_array);
                if (! empty($id_array)) {
                    foreach ($id_array as $id) {
                        $user_id = MerchantUser::where('id', $id)->value('user_id');
                        $merchant_id = MerchantUser::where('id', $id)->value('merchant_id');
                        $amount = MerchantUser::where('id', $id)->value('amount');
                        $amount2 = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.merchant_id', '=', $merchant_id)->where('payment_investors.user_id', '=', $user_id)->sum('payment');
                        $return_result = $this->MerchantInvestorDeleteFunction($id);
                        if ($return_result['result'] == 'success') {
                        }
                    }
                    $display_value = User::where('id', $request->investor_id)->value('display_value');
                    $merchant = ($display_value == 'name') ? $merchantdetails->name : $request->merchant_id;
                    $new_mails[] = $investoremail;
                    $message['to_mail'] = $new_mails;
                    $message['subject'] = 'Funding request rejected | Velocitygroupusa';
                    $message['title'] = 'Funding request rejected';
                    $message['status'] = 'funding_reject';
                    $message['content'] = 'The Merchant <a href='.url('investors/merchants/'.$merchant_id).'>'.$merchant.'</a> did not fund and you did not participate in this deal. Please contact your admin for details if you have any questions.';
                    $message['merchant_id'] = $request->merchant_id;
                    $message['merchant_name'] = $merchant;
                    $message['investor'] = $request->investor_name;
                    $message['unqID'] = unqID();
                    $email_template = Template::where([
                        ['temp_code', '=', 'FREQR'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        $settings = Settings::where('keys', 'admin_email_address')->first();
                        $admin_email = $settings->values;
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['to_mail'] = $admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                    }
                    $request->session()->flash('message', 'Investment Status Changed to Rejected');
                }
            } elseif ($request->status == 0) {
                $request->session()->flash('message', 'Investment Status Changed to Pending!');
            } elseif ($request->status == 2) {
                $request->session()->flash('message', 'Investment Status Changed to Hide!');
            } elseif ($request->status == 3) {
                $request->session()->flash('message', 'Investment Status Changed to Re-assigned!');
            }
        }

          return redirect()->back();

    }
    public function getSettledInvestmentSum(User $user){
        $userId = $user->id;
        return MerchantUser::whereIn('merchant_user.status', [1, 3])->whereHas('investors', function ($query) use ($userId) {
            $query->where('merchant_user.user_id', $userId);
        })->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
        ->whereHas('merchant', function ($query1) {
            $query1->where('active_status', 1);
            $query1->whereIn('sub_status_id', [18, 19, 20]);
            $query1->where('old_factor_rate', 0);
        })
        ->select(
        DB::raw('sum(((merchant_user.invest_rtr)-(merchant_user.invest_rtr*(merchant_user.mgmnt_fee)/100)-(IF(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,0)))) as total_rtr'),
        )
        ->first();

    }

 



}
