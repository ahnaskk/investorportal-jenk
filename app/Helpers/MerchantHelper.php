<?php

namespace App\Helpers;

use App\AchRequest;
use App\CompanyAmount;
use App\Jobs\CommonJobs;
use App\Label;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\PaymentInvestors;
use App\MNotes;
use App\SubStatus;
use App\Jobs\CRMjobs;
use App\Models\Views\MerchantUserView;
use App\Models\Views\CompanyAmountView;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use App\Library\Repository\InvestorTransactionRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use Illuminate\Support\Facades\Validator;
use App\ParticipentPayment;
use App\PaymentPause;
use App\ReassignHistory;
use App\Settings;
use App\Template;
use App\TermPaymentDate;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use DateTime;
use Exception;
use FFM;
use Form;
use MTB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use PayCalc;
use Permissions;
use InvestorHelper;
use ParticipantPaymentHelper;
use Yajra\DataTables\Html\Builder;

class MerchantHelper 
{
    public function __construct(ISubStatusRepository $subStatus, IRoleRepository $role, IMerchantRepository $merchant, ILabelRepository $label,IParticipantPaymentRepository $payment, Builder $tableBuilder)
    {
         $this->subStatus = $subStatus;
         $this->role = $role;
         $this->merchant = $merchant;
         $this->label = $label;
         $this->payment=$payment;
         $this->tableBuilder=$tableBuilder;
    }
    public function allMerchants($request)
    {
        $page_title = 'All Merchants';
        $lender_id = $request->lender_id;
        $user_id = $request->user_id;
        $status_id = $request->status_id;
        $marketplace_status = $request->check;
        $stop_payment = $request->stop_payment;
        $paid_off = $request->paid_off;
        $not_started = $request->not_started;
        $not_invested = $request->not_invested;
        $over_payment = $request->over_payment;
        $request_m = $request->request_m;
        $bank_account = $request->bank_account;
        $payment_pause = $request->payment_pause;
        $owner = $request->owner;

       
         $tableBuilder=$this->tableBuilder->ajax(['url' => route('admin::merchants::index'), 'data' => 'function(d){d.user_id = $("#user_id").val();d.lender_id = $("#lender_id").val();d.status_id = $("#status_id").val();d.market_place = $("#market_place").is(\':checked\') ? true : false ;d.not_started = $("#not_started").is(\':checked\') ? true : false ;d.not_invested = $("#not_invested").is(\':checked\') ? true : false ; d.paid_off = $("#paid_off").is(\':checked\') ? true : false ; d.stop_payment = $("#stop_payment").is(\':checked\') ? true : false ;  d.over_payment = $("#over_payment").is(\':checked\') ? true : false ; d.late_payment= $("#late_payment").val();d.request_m= $("#request_m").val();d.date_start = $("#date_start").val();d.date_end = $("#date_end").val();d.advance_type = $("#advance_type").val();d.substatus_flag_id = $("#substatus_flag").val();d.label = $("#label").val();d.bank_account = $("#bank_account").val();d.payment_pause = $("#payment_pause").val();d.owner = $("#owner").val();d.mode_of_payment = $("#mode_of_payment").val();}']);
        $tableBuilder = $tableBuilder->columns([
            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'searchable' => false, 'orderable' => false], ['name' => 'id', 'orderable' => false, 'data' => 'id', 'title' => 'Merchant ID'], 
            ['data' => 'name', 'name' => 'name', 'title' => 'Name'], 
            ['data' => 'merchant_user.amount', 'name' => 'merchant_user.amount', 'title' => 'Funded Amount','className'=>"text-right"], 
            ['data' => 'pmnts', 'name' => 'pmnts', 'title' => 'Payments', 'searchable' => false,'className'=>"text-right"], 
            ['data' => 'paid_count', 'name' => 'paid_count', 'title' => 'Paid Count', 'searchable' => true,'className'=>"text-right"], 
            ['data' => 'no_of_investor', 'name' => 'no_of_investor', 'title' => 'No Of Investor', 'searchable' => false,'className'=>"text-right"], 
            ['data' => 'status_name', 'name' => 'sub_statuses.name', 'title' => 'Status'], 
            ['data' => 'last_payment_date', 'name' => 'last_payment_date', 'title' => 'Last Payment Date', 'searchable' => false], 
            ['data' => 'complete_percentage', 'name' => 'complete_percentage', 'title' => 'Complete %','className'=>"text-right"], 
            ['data' => 'date_funded', 'name' => 'date_funded', 'title' => 'Date Funded', 'searchable' => false], 
            ['data' => 'net_zero', 'name' => 'net_zero', 'title' => 'Net Zero Balance', 'orderable' => false, 'searchable' => false,'className'=>"text-right"], 
            ['data' => 'balance', 'name' => 'balance', 'title' => 'Balance', 'orderable' => false, 'searchable' => false,'className'=>"text-right"], 
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
        ]);

        $tableBuilder = $tableBuilder->parameters(['aaSorting' => [], 'columnDefs' => ['orderable' => false, 'targets' => [9, 10]], 'orderCellsTop' => true, 'dom' => 'Bfrtip', 'buttons' => ['export', 'print', 'reset', 'reload'], 'order' => [6, 'desc'], 'bStateSave' => 'true', 'fnStateSave' => "function (oSettings, oData) { localStorage.setItem('pages', JSON.stringify(oData)); }", 'fnStateLoad' => "function (oSettings) {\n          return JSON.parse(localStorage.getItem('pages'));\n        }", 'initComplete' => "function (index,counter) {\n          var counter =0 ;\n          this.api().columns().every(function () {\n            var column = this;\n            var state = this.state();\n            var input = document.createElement(\"input\");\n            input.value =state.columns[counter++].search.search;\n            $(input).appendTo($(column.footer()).empty()).on('change', function () {\n              column.search($(this).val(), false, false, true).draw();\n            });\n          });\n        }", 'pagingType' => 'input']);

        $lenders = $this->role->allLenders();
        $sub_statuses = DB::table('sub_statuses')->orderBy('name')->get();
        $label = $this->label->getAll()->pluck('name', 'id');
        $users = $this->role->allInvestors()->pluck('name', 'id')->toArray();

        $special_accnts = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->whereIn('role_id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE]);

        if($special_accnts->count()>0)
        {
            $special_accnts=$special_accnts->pluck('name', 'users.id')->toArray();
            $users = $users + $special_accnts;
        }
      
        $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0] = 'All';
        $companies = array_reverse($companies, true);
        $substatus_flags = DB::table('sub_status_flags')->pluck('name','id')->toArray();
        $payment_methods = ['ach' => 'ACH', 'manual' => 'Manual', 'credit_card' => 'Credit Card Payment'];



          return ['marketplace_status'=>$marketplace_status,'stop_payment'=>$stop_payment,'not_started'=>$not_started,'over_payment'=>$over_payment,'page_title'=>$page_title,'tableBuilder'=>$tableBuilder,'lenders'=>$lenders,'lender_id'=>$lender_id,'status_id'=>$status_id,'sub_statuses'=>$sub_statuses,'paid_off'=>$paid_off,'users'=>$users,'user_id'=>$user_id,'companies'=>$companies,'substatus_flags'=>$substatus_flags,'label'=>$label,'payment_methods'=>$payment_methods];

    }

    public function merchantView($request,$extra_arr=[])
    {       
        $company_id = isset($extra_arr['company_id']) ? $extra_arr['company_id'] : 0;
        $investor_id = isset($extra_arr['investor_id']) ? $extra_arr['investor_id'] : 0;
        $id=$extra_arr['id'];
         $payment_started = DB::table('participent_payments')->where('merchant_id', $id)
        ->where('participent_payments.is_payment', 1)
        ->count();
          
        $company_amount_update = 0;
        $company_amount = DB::table('merchant_user')->where('merchant_id', $id)->join('users', 'users.id', 'merchant_user.user_id')->groupBy('users.company')->pluck(DB::raw('sum(amount) as amount'), 'users.company')->toArray();
        $last_status_updated_date = MerchantStatusLog::where('merchant_id', $id)->orderBy('created_at', 'desc')->limit(1)->value('created_at');
        $merchant_investors = DB::table('merchant_user')->where('merchant_id', $id)->join('users', 'users.id', 'merchant_user.user_id');
        if ($company_id != 0) {
            $merchant_investors = $merchant_investors->where('users.company', $company_id);
        }
        $merchant_investors = $merchant_investors->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', '<>', User::OVERPAYMENT_ROLE)->where('user_has_roles.role_id', '<>', User::AGENT_FEE_ROLE)->groupBy('merchant_user.user_id')->pluck(DB::raw('upper(users.name) as name'), 'users.id')->toArray();
        $merchant_company_fund = DB::table('company_amount')->where('merchant_id', $id)->select('max_participant', 'company_id')->get()->toArray();
        $company_amount_difference = 0;
        if (count($company_amount) > 0) {
            foreach ($merchant_company_fund as $company_fund) {
                if (! isset($company_amount[$company_fund->company_id])) {
                    $company_amount[$company_fund->company_id] = 0;
                }
                $company_amount_difference = round($company_amount[$company_fund->company_id] - $company_fund->max_participant, 4);
                if ($company_amount_difference) {
                    $company_amount_update = 1;
                }
            }
        }
        $agent_fee_on_substatus = Settings::where('keys', 'agent_fee_on_substtaus')->value('values');
        $agent_fee_on_substatus = json_decode($agent_fee_on_substatus, true);
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $subinvestors = [];
        $investor = $this->role->allInvestors();
        if (empty($permission)) {
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
            $count = MerchantUser::whereIn('merchant_user.user_id', $subinvestors)->where('merchant_id', $id)->count();
            if ($count == 0) {
                return redirect()->to('admin/merchants/')->withErrors('This merchant not a company based');
            }
        }
        $userId = $request->user()->id;
        $investor_ids = $this->role->allInvestors()->pluck('id');
        $merchant_array = $this->merchant->merchant_details($id, $company_id, $investor_id);
        $merchant = $merchant_array['merchant'];
        $statusList = $this->merchant->get_substatus_list($merchant);
        $statuses = $this->subStatus->getAll()->whereIn('id', $statusList)->pluck('name', 'id');
        $amount_difference = $merchant_array['amount_difference'];
        $payment_left = $merchant_array['payment_left'];
        if (in_array($merchant['sub_status_id'], [4, 22, 18, 19, 20])) {
            $payment_left = 0;
        }
        $ctd_sum = $merchant_array['ctd_sum'];
        $syndication_percent = $merchant_array['syndication_percent'];
        $syndication_amount = $merchant_array['syndication_amount'];
        $syndication_payment = $merchant_array['syndication_payment'];
        $net_zero = $merchant_array['net_zero'];
        $total_invested = $merchant_array['total_invested'];
        $profit_value_net = $merchant_array['ctd_our_portion'] - $merchant_array['net_zero'];
        $profit_value_net = $profit_value_net > 0 ? $profit_value_net : 0;
        $balance_our_portion = $merchant_array['balance_our_portion'];
        $ctd_our_portion = $merchant_array['ctd_our_portion'];
        $disabled_company_participant_share = $merchant_array['disabled_company_participant_share'];
        $disabled_company_mang_fee = $merchant_array['disabled_company_mang_fee'];
        $full_balance = $merchant_array['full_balance'];
        $merchant_balance = $merchant_array['balance_merchant'];
        $total = 'Total:';
        $total_payment = $merchant_array['total_payment'];
        $final_participant_share = $merchant_array['final_participant_share'];
        $principal = $merchant_array['principal'];
        $profit_value = $merchant_array['profit_value'];
        $investor_data = $merchant_array['investor_data'];
        $total_managmentfee = $merchant_array['total_managmentfee'];
        $total_syndicationfee = $merchant_array['total_syndicationfee'];
        $total_underwrittingfee = $merchant_array['total_underwrittingfee'];
        $dates2 = $merchant_array['dates2'];
        $actual_payment_left = $merchant_array['actual_payment_left'];
        $complete_per = $merchant->complete_percentage;
        $overpayment = $merchant_array['overpayments'];
        $num_pace_payment = $merchant_array['num_pace_payment'] ? $merchant_array['num_pace_payment'] : 0;
        $num_pace_percentage = $merchant_array['num_pace_percentage'];
        $pace_amount = $merchant_array['pace_amount'];
        $our_pace_amount = $merchant_array['our_pace_amount'];
        $pre_paid = isset($merchant_array['prepaid']) ? $merchant_array['prepaid'] : 0;
        $t_mang_fee = $merchant_array['t_mang_fee'];
        $m_fee = $merchant_array['m_fee'];
        $overpayment_fee = $merchant_array['overpayment_fee'];
        $balance_mgmnt_fee = $merchant_array['balance_mgmnt_fee'];
        $missed_payments = $merchant_array['missed_payments'];
        $last_rcode_date = $merchant_array['last_rcode_date'];
        $agent_fee = $merchant_array['agent_fee'];
        $total_invest_rtr = $merchant_array['total_invest_rtr'];
        if($investor_id!=0){
        $net_zero_balance=($net_zero-$ctd_our_portion)>0?$net_zero-$ctd_our_portion:0;
        }else{
         $net_zero_balance=($net_zero-$ctd_our_portion-$agent_fee)>0?$net_zero-$ctd_our_portion-$agent_fee:0;   
        }
        if ($merchant) {
            $page_title = 'Merchant View';
            $tableBuilder=$this->tableBuilder->ajax(route('admin::merchants::merchant_data', [$id, $company_id, $investor_id]));
            $restricted_column_visibility = true;
            if (in_array($merchant->sub_status_id, [4, 22, 18, 19, 20])) {
                $restricted_column_visibility = false;
            }
            $tableBuilder = $tableBuilder->columns([
                ['data' => 'checkbox', 'type' => 'checkbox', 'name' => 'checkbox', 'title' => '<label class="chc" title=""><input type="checkbox" name="delete_multi_submit"  id="delete_payment"><span class="checkmark checkk"></span></label>', 'orderable' => false, 'searchable' => false, 'className' => 'checkbox11 delayHover', 'visible' => $restricted_column_visibility],
                ['data' => 'DT_RowIndex', 'className' => 'details-control', 'name' => 'participant_payment', 'orderable' => false, 'defaultContent' => '', 'title' => '', 'searchable' => false],
                ['data' => 'payment_date', 'name' => 'payment_date', 'title' => 'Payment Date'],
                ['data' => 'payment', 'name' => 'payment', 'title' => 'Payment', 'class' => 'text-right'],
                ['data' => 'final_participant_share', 'name' => 'final_participant_share', 'title' => 'To Participant', 'class' => 'text-right'],
                ['data' => 'principal', 'name' => 'principal', 'title' => 'Principal', 'class' => 'text-right'],
                ['data' => 'profit', 'name' => 'profit', 'title' => 'Profit', 'class' => 'text-right'],
                ['data' => 'debit_reason', 'name' => 'debit_reason', 'title' => 'Reason'],
                ['data' => 'mode_of_payment', 'name' => 'mode_of_payment', 'title' => 'Payment Method', 'orderable' => false, 'searchable' => false],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false, 'visible' => $restricted_column_visibility]
            ]);
            $tableBuilder->parameters(['footerCallback' => "function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html('$total');$(n.column(3).footer()).html('$total_payment');$(n.column(4).footer()).html('$final_participant_share');$(n.column(6).footer()).html('$profit_value');$(n.column(5).footer()).html('$principal'); }", 'lengthMenu' => [100, 50], 'order' => [[3, 'desc']]]);
            $investors = $this->role->allInvestorsLiquidity();
            $investors_data = $investors->toArray();
            $existing_investors1 = MerchantUser::where('merchant_id', $merchant->id);
            $paid_to_participant = 0;
            if (empty($permission)) {
                $existing_investors1 = $existing_investors1->whereIn('user_id', $investor_ids);
            }
            $existing_investors = $existing_investors1->pluck('user_id')->toArray();
            $no_liquidity = UserDetails::where('liquidity', '<=', 0);
            if ($permission) {
                $no_liquidity = $no_liquidity->whereIn('user_id', $investor_ids);
            }
            $investor_with_no_liquidity = $no_liquidity->pluck('user_id')->toArray();
            $investor_count = count($investors_data) - count($existing_investors);
            $merchant_id = $merchant->id;
            $total_amount = $prepaid_amount_sum = $total_invested_rtr= 0;
            $total_inveted_amount = $invest_amount_with_comm_sum = $total_invested_amount_with_interset = 0;
            $sub_status = Merchant::where('id', $merchant->id)->value('sub_status_id');
            foreach ($investor_data as $investor) {
                $total_amount = $total_amount + $investor->amount;
                $total_invested_rtr=$total_invested_rtr+$investor->invest_rtr; 
                $prepaid_amount = MerchantUser::where('merchant_id', $merchant->id)->where('merchant_user.user_id', $investor->user_id)->where('status', 1)->value('pre_paid');
                $prepaid_amount_sum += $prepaid_amount;
                $invested_amount = $prepaid_amount + $investor->amount + ($investor->amount * $merchant->commission / 100);
                $total_inveted_amount = $total_inveted_amount + $invested_amount;
                $from = Carbon::createFromFormat('Y-m-d', $merchant->date_funded);
                $dt = new DateTime($investor->created_at);
                $investment_date = $dt->format('Y-m-d');
                if ($sub_status != 11) {
                    $to = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
                } else {
                    $to = Merchant::where('id', $merchant->id)->value('last_payment_date');
                    $to = ($to ? $to : date('Y-m-d')).' 23:59:59';
                    $to = Carbon::createFromFormat('Y-m-d H:i:s', $to);
                }
                $diff_in_days = $from->diffInDays($to);
                $interest_rate = User::where('id', $investor->user_id)->value('interest_rate');
                $interest = ($interest_rate / (365 * 100)) * $diff_in_days;
                $invest_amount_with_comm = $prepaid_amount + $investor->amount + ($investor->amount * $merchant->commission / 100);
                $invest_amount_with_comm_sum = $invest_amount_with_comm_sum + $invest_amount_with_comm;
                $invested_amount_with_interest = $invest_amount_with_comm + ($invest_amount_with_comm * $interest);
                $total_invested_amount_with_interset = $total_invested_amount_with_interset + $invested_amount_with_interest;
            }
            $our_funded = $total_amount;
            $net_value = $invest_amount_with_comm_sum;
            $net_value = $paid_to_participant - $total_inveted_amount;
            $net_value_with_interest = $paid_to_participant - $total_invested_amount_with_interset;
            $paid_to_participant = FFM::dollar($paid_to_participant);
            $all_investors = $this->role->allInvestorsLiquidity('', '', 0)->whereNOTIn('id', $existing_investors)->whereNOTIn('id', $investor_with_no_liquidity)->where('investor_type', '!=', 5);
            $selected_investors = $this->role->allInvestorsLiquidity()->whereNOTIn('id', $existing_investors)->whereNOTIn('id', $investor_with_no_liquidity)->pluck('id')->toArray();
            $all_auto_investors = User::whereNotNull('label')->where('active_status', 1)->whereRaw('JSON_CONTAINS(label,"'.$merchant->label.'")')->get();
            $labels = DB::table('label')->where('flag', 1)->pluck('id')->toArray();
            $auto_investors = User::whereNotNull('label')->whereRaw('JSON_CONTAINS(label,"'.$merchant->label.'")')->where('active_status', 1)->pluck('users.id')->toArray();
            $avil_liquidity = UserDetails::whereIn('user_id', $auto_investors)->pluck('liquidity', 'user_id')->toArray();
            $date = MerchantUser::select('merchant_user.created_at')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchants.label', $merchant->label)->whereIn('merchant_user.user_id', $auto_investors)->orderByDesc('merchant_user.created_at')->first();
            $date_start = ($date) ? $date->created_at : date('Y-m-d h:i:s');
            $date_end = date('Y-m-d h:i:s');
            $payments = DB::table('payment_investors')->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id')->join('merchants', 'participent_payments.merchant_id', 'merchants.id')->join('users', 'payment_investors.user_id', 'users.id')->whereIn('payment_investors.user_id', $auto_investors)->where('merchants.label', $merchant->label)->where('participent_payments.created_at', '>=', $date_start)->where('participent_payments.created_at', '<=', $date_end);
            $total_payments = clone $payments;
            $total_payments = $total_payments->groupBy('payment_investors.user_id')->pluck(DB::raw('sum(actual_participant_share-mgmnt_fee) as net_amount'), 'payment_investors.user_id')->toArray();
            $collected_amount = [];
            if (! empty($auto_investors)) {
                foreach ($auto_investors as $key => $value) {
                    $fee_1 = $merchant->commission + $merchant->underwriting_fee + $merchant->origination_fee + $merchant->m_syndication_fee;
                    $pay = isset($total_payments[$value]) ? $total_payments[$value] : 0;
                    if ($pay) {
                        $investing_amount = $pay * 100 / (100 + $fee_1);
                        $collected_amount[$value] = $investing_amount;
                    }
                }
            }
            $reassign = ReassignHistory::where('merchant_id', $id)->count();
            $companyAmount = CompanyAmount::where('merchant_id', $id)->join('users', 'users.id', 'company_amount.company_id')->select('max_participant', 'company_id', 'name', 'users.company_status')->get()->toArray();
            $CompanyFundDiffrenceFlag=false;
            $CompanyFundDiffrence=CompanyAmount::CompanyFundDiffrence($id);
            foreach ($CompanyFundDiffrence as $key => $value) {
                $diff=round($value->company_funded-$value->max_participant,2);
                if($diff){ $CompanyFundDiffrenceFlag=true; break; } 
            }
            $company_d = [];
            $per = [];
            if (! empty($companyAmount)) {
                foreach ($companyAmount as $key => $value) {
                    $company_d[$value['company_id']]['status'] = $value['company_status'];
                    $company_d[$value['company_id']]['name'] = $value['name'];
                    $company_d[$value['company_id']]['amount'] = $value['max_participant'];
                    if (isset($merchant->max_participant_fund)) {
                        if (! empty($merchant->max_participant_fund)) {
                            if ($merchant->max_participant_fund && $merchant->max_participant_fund != 0) {
                                $company_d[$value['company_id']]['max_participant_percentage'] = ($value['max_participant'] / $merchant->max_participant_fund) * 100;
                            } else {
                                $company_d[$value['company_id']]['name'] = $value['name'];
                                $company_d[$value['company_id']]['max_participant_percentage'] = 0;
                            }
                        } else {
                            $company_d[$value['company_id']]['name'] = $value['name'];
                            $company_d[$value['company_id']]['max_participant_percentage'] = 0;
                        }
                    } else {
                        $company_d[$value['company_id']]['name'] = $value['name'];
                        $company_d[$value['company_id']]['max_participant_percentage'] = 0;
                    }
                    $company_d[$value['company_id']]['funded'] = DB::table('merchant_user_views')->where('merchant_id', $id)->where('company', $value['company_id'])->sum('amount');
                }
            }
            $SpecialAccounts = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $SpecialAccounts->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE, User::OVERPAYMENT_ROLE]);
            $SpecialAccounts = $SpecialAccounts->pluck('users.id')->toArray();
            $payment_share = DB::table('payment_investors')->where('payment_investors.merchant_id', $id)->join('users', 'payment_investors.user_id', 'users.id')->groupBy('users.company');
            if($SpecialAccounts){
                $payment_share = $payment_share->whereNotIn('payment_investors.user_id',$SpecialAccounts);   
            }
            $payment_share = $payment_share->pluck(DB::raw('sum((actual_participant_share) ) as part_share'), 'users.company');
            $payment_share = $payment_share->toArray();
            $pay_c = array_sum($payment_share);
            $per_data = DB::table('merchant_user')->where('merchant_user.merchant_id', $id)->join('users', 'users.id', 'merchant_user.user_id')->groupBy('users.company')->pluck(DB::raw('sum(merchant_user.invest_rtr) as in_rtr'), 'users.company');
            $per_data = $per_data->toArray();
            if (! empty($per_data)) {
                foreach ($per_data as $key => $value) {
                    if ($key) {
                        $per[$key]['rtr'] = $value;
                    }
                }
            }
            if (! empty($payment_share)) {
                foreach ($payment_share as $key => $value) {
                    $per[$key]['amount'] = $value;
                }
            }
            $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
            $companies[0] = 'All';
            $companies = array_reverse($companies, true);
            $mnotes_count = MNotes::where('merchant_id', $merchant_id)->count();
            $substatus_flags = DB::table('sub_status_flags')->pluck('name','id')->toArray();
            $advance_types = config('custom.advance_types');
            session_set('payment_amount', $merchant->payment_amount);
            $merchant_user_count = MerchantUser::whereIn('merchant_user.status', [1, 3])->where('merchant_id', $merchant_id)->count();
            $payment_status = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id)->count();
            if ($payment_status > 0) {
                $company_amount_update = 0;
            }
            $user = User::where('id', $merchant->user_id)->first();
            $unwanted_sub_status = config('custom.unwanted_sub_status');
            $ach_paused = false;
            $ach_active = false;
            if (!in_array($merchant->sub_status_id, $unwanted_sub_status)) {
                if ($merchant->ach_pull) {
                    $ach_active = true;
                } 
                if (isset($merchant->payment_pause_id)) {
                    $ach_paused = true;
                    $ach_pause = PaymentPause::find($merchant->payment_pause_id);
                    $ach_paused_date = Carbon::parse($ach_pause->paused_at)->toDateString();
                }
            }
            $fund_amount_change_flag = false;
            if ($payment_status <= 0 && in_array($merchant['label'], [1, 2])) {
                $MerchantUserAdjustedBefor = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->where('investor_id', '!=', 504)->get();
                DB::beginTransaction();
                MerchantUser::InvestmentAmountAdjuster($merchant_id);
                $MerchantUserAdjustedAfter = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->where('investor_id', '!=', 504)->get();
                DB::rollback();
                foreach ($MerchantUserAdjustedBefor as $key => $value) {
                    $diff = $MerchantUserAdjustedAfter[$key]->amount - $value->amount;
                    if ($diff) {
                        $fund_amount_change_flag = true;
                        break;
                    }
                }
            }
            $Transaction = ParticipentPayment::where('merchant_id', $merchant_id)->orderBy('payment_date')->get();
            $TotalCredit = ParticipentPayment::where('merchant_id', $merchant_id)->where('payment', '>', 0)->sum('payment');
            $TotalDebit = -ParticipentPayment::where('merchant_id', $merchant_id)->where('payment', '<', 0)->sum('payment');
            $TransactionFundedCheck = ParticipentPayment::where('merchant_id', $merchant_id)->where('model', \App\MerchantUser::class)->count();
            $merchant_funded_details = ['creator' => ($merchant && $merchant->creator) ? $merchant->creator : null, 'created_at' => ($merchant) ? $merchant->created_at : null];
            $merchant_payment_data = $this->payment->getMerchantPayments($id, $company_id, $investor_id);
            if ($merchant_payment_data) {
                $merchant_first_payment_data = collect($merchant_payment_data->get()->toArray())->last();
                if ($merchant_first_payment_data) {
                    $first_creator = User::find($merchant_first_payment_data['creator_id']);
                    $merchant_first_payment_data['creator'] = ($first_creator) ? $first_creator->name : '--';
                }
                $merchant_last_payment_data = collect($merchant_payment_data->get()->toArray())->first();
                if ($merchant_last_payment_data) {
                    $last_creator = User::find($merchant_last_payment_data['creator_id']);
                    $merchant_last_payment_data['creator'] = ($last_creator) ? $last_creator->name : '--';
                }
            } else {
                $merchant_first_payment_data = null;
                $merchant_last_payment_data = null;
            }
            $ach_payments_query = TermPaymentDate::where('merchant_id', $merchant_id)->orderBy('payment_date', 'desc')->get();
            $ach_payments = [];
            $today = Carbon::now()->tz('America/New_York')->toDateString();
            $merchant_status = $merchant->payStatus;
            foreach ($ach_payments_query as $term_payment) {
                $ach_payment_status = null;
                $ach_style = '';
                $total_ach_recieved = 0;
                if ($term_payment->status == 1) {
                    $ach_style = 'background-color: #c3e6cb';
                    $total_ach_recieved = $term_payment->payments()->sum('payment');
                } elseif ($term_payment->status == -1) {
                    $ach_style = 'background-color: #ff3434';
                    if ($term_payment->ach) {
                        $ach_payment_status = $term_payment->StatusName.' ('.$term_payment->ach->status_response.')';
                    }
                } elseif ($term_payment->status == 0) {
                    if ($ach_active) {
                        if ($ach_paused) {
                            if ($term_payment->payment_date >= $ach_paused_date) {
                                $ach_payment_status = 'Paused';
                            }
                        }
                    } else {
                        if ($term_payment->payment_date >= $today) {
                            $ach_payment_status = $merchant_status;
                        }
                    }
                }
                $total_ach_recieved = FFM::dollar($total_ach_recieved);
                $ach_payments[] = (object) ['id' => $term_payment->id, 'payment_date' => FFM::date($term_payment->payment_date), 'payment_amount' => FFM::dollar($term_payment->payment_amount), 'status_type' => $ach_payment_status ?? $term_payment->StatusName, 'ach_style' => $ach_style ?? '', 'total_payments' => $total_ach_recieved];
            }
            $agent_fee_per = (Settings::where('id', 1)->value('agent_fee_per')) ?? 0;

            $debit_ach_active = false;
            if ($merchant->ach_pull && $merchant->bankAccountCredit) {
                $unwanted_status_debit = config('custom.unwanted_sub_status_merchant_debit');
                if (! in_array($merchant->sub_status_id, $unwanted_status_debit)) {
                    $debit_ach_active = true;
                }
            }
            $paid_profit=DB::table('merchant_user')->wheremerchant_id($merchant_id)->sum('paid_profit');
            $paid_principal=DB::table('merchant_user')->wheremerchant_id($merchant_id)->sum('paid_principal');
            $expected_management_fee=DB::table('merchant_user')->wheremerchant_id($merchant_id)->sum(DB::raw('mgmnt_fee*invest_rtr/100'));
            $substatus = DB::table('sub_statuses')->orderBy('name')->pluck('name', 'id')->toArray();
            $sys_substaus = (Settings::where('keys', 'agent_fee_on_substtaus')->value('values'));
            $sys_substaus = json_decode($sys_substaus, true);

             return [
               'selected_investors'          => $selected_investors,
               'merchant_user_count'         => $merchant_user_count,
               'merchant_id'                 => $merchant_id,
               'all_investors'               => $all_investors,
               'ctd_sum'                     => $ctd_sum,
               'payment_left'                => $payment_left,
               'net_value'                   => $net_value,
               'net_value_with_interest'     => $net_value_with_interest,
               'ctd_our_portion'             => $ctd_our_portion,
               'disabled_company_participant_share'=> $disabled_company_participant_share,
               'disabled_company_mang_fee'   => $disabled_company_mang_fee,
               'balance_our_portion'         => $balance_our_portion,
               'paid_to_participant'         => $paid_to_participant,
               'merchant'                    => $merchant,
               'page_title'                  => $page_title,
               'tableBuilder'                => $tableBuilder,
               'investor_data'               => $investor_data,
               'syndication_percent'         => $syndication_percent,
               'syndication_payment'         => $syndication_payment,
               'investors'                   => $investors,
               'total_managmentfee'          => $total_managmentfee,
               'total_syndicationfee'        => $total_syndicationfee,
               'total_underwrittingfee'      => $total_underwrittingfee,
               'investor_count'              => $investor_count,
               'amount_difference'           => $amount_difference,
               'dates2'                      => $dates2,
               'reassign'                    => $reassign,
               'companies'                   => $companies,
               'profit_value'                => $profit_value,
               'net_zero'                    => $net_zero,
               'complete_per'                => $complete_per,
               'syndication_amount'          => $syndication_amount,
               'mnotes_count'                => $mnotes_count,
               'actual_payment_left'         => $actual_payment_left,
               'overpayment'                 => $overpayment,
               'profit_value_net'            => $profit_value_net,
               'num_pace_payment'            => $num_pace_payment,
               'num_pace_percentage'         => $num_pace_percentage,
               'pace_amount'                 => $pace_amount,
               'our_pace_amount'             => $our_pace_amount,
               'statuses'                    => $statuses,
               'company_d'                   => $company_d,
               'substatus_flags'             => $substatus_flags,
               'per'                         => $per,
               'advance_types'               => $advance_types,
               'pre_paid'                    => $pre_paid,
               'auto_investors'              => $auto_investors,
               'all_auto_investors'          => $all_auto_investors,
               'full_balance'                => $full_balance,
               'total_payments'              => $total_payments,
               't_mang_fee'                  => $t_mang_fee,
               'm_fee'                       => $m_fee,
               'overpayment_fee'             => $overpayment_fee,
               'avil_liquidity'              => $avil_liquidity,
               'collected_amount'            => $collected_amount,
               'balance_mgmnt_fee'           => $balance_mgmnt_fee,
               'missed_payments'             => $missed_payments,
               'labels'                      => $labels,
               'total_invested'              => $total_invested,
               'company_amount_update'       => $company_amount_update,
               'payment_status'              => $payment_status,
               'last_rcode_date'             => $last_rcode_date,
               'user'                        => $user,
               'ach_active'                  => $ach_active,
               'fund_amount_change_flag'     => $fund_amount_change_flag,
               'Transaction'                 => $Transaction,
               'TotalCredit'                 => $TotalCredit,
               'TotalDebit'                  => $TotalDebit,
               'TransactionFundedCheck'      => $TransactionFundedCheck,
               'merchant_first_payment_data' => $merchant_first_payment_data,
               'merchant_last_payment_data'  => $merchant_last_payment_data,
               'merchant_funded_details'     => $merchant_funded_details,
               'ach_payments'                => $ach_payments,
               'merchant_investors'          => $merchant_investors,
               'agent_fee_per'               => $agent_fee_per,
               'last_status_updated_date'    => $last_status_updated_date,
               'agent_fee'                   => $agent_fee,
               'debit_ach_active'            => $debit_ach_active,
               'total_amount'                => $total_amount,
               'total_invested_rtr'          => $total_invested_rtr,
               'merchant_balance'            => $merchant_balance,
               'paid_principal'              => $paid_principal,
               'paid_profit'                 => $paid_profit,
               'expected_management_fee'     => $expected_management_fee,
               'CompanyFundDiffrenceFlag'    => $CompanyFundDiffrenceFlag,
               'CompanyFundDiffrence'        => $CompanyFundDiffrence,
               'tableBuilder'                => $tableBuilder,
               'agent_fee_on_substatus'      => $agent_fee_on_substatus,
               'total_invest_rtr'            => $total_invest_rtr,
               'net_zero_balance'            => $net_zero_balance,
               'payment_started'             => $payment_started,
               'agent_fee_status'            => $sys_substaus
           ];
        }
    }

    public function createMerchant($request)
    {
        $page_title = 'Add Merchant';
        $lender_data = [];
        $industries = DB::table('industries')->select(['name', 'id'])->orderBy('name')->get();
        $states = DB::table('us_states')->select(['state', 'id'])->get();
        $merchant_source = DB::table('merchant_source')->select(['name', 'id'])->get();
        $statuses = $this->subStatus->getAll()->whereNotIn('id', [17, 11, 18, 19, 20])->pluck('name', 'id');
        $label = $this->label->getAll()->pluck('name', 'id');
        $investors = $this->role->allInvestors();
        $lender_login = $request->user()->hasRole(['lender']);
        $default_lender= User::where('id',74)->select('global_syndication','management_fee','s_prepaid_status','underwriting_fee','underwriting_status','id')->first();
        if ($lender_login) {
            $lender_data['id'] = $request->user()->id;
            $lender_data['name'] = $request->user()->name;
            $lender_data['management_fee'] = $request->user()->management_fee;
            $lender_data['syndication_fee'] = $request->user()->global_syndication;
        }
        $admins = $this->role->allLenders();
        $action = 'create';
        $company_id = 0;

        // get fee values 
        $syndication_fee_values=FFM::fees_array();

        if ($request->user()->merchant_permission == 1) {
            $company_id = $request->user()->id;
        }

        $companies1 = $this->role->allSubAdmins();

        if ($company_id != 0) {
            $companies1 = $companies1->where('id', $company_id);
        }
        $companies = $companies1->pluck('name', 'id')->toArray();
        $underwriting_company = $companies1->pluck('name', 'id')->toArray();
        if (! is_array($underwriting_company)) {
            $underwriting_company = [];
        }
        array_unshift($underwriting_company, '');
        unset($underwriting_company[0]);
        $company = $companies;

        return ['page_title'=>$page_title,'lender_login'=>$lender_login,'lender_data'=>$lender_data,'syndication_fee_values'=>$syndication_fee_values,'statuses'=>$statuses,'investors'=>$investors,'admins'=>$admins,'action'=>$action,'industries'=>$industries,'states'=>$states,'merchant_source'=>$merchant_source,'company'=>$company,'companies'=>$companies,'underwriting_company'=>$underwriting_company,'label'=>$label,'default_lender'=>$default_lender];


    }

    public function editMerchant($request,$id)
    {
           if ($merchant = $this->merchant->find($id)) {
                $payment_status = ParticipentPayment::where('participent_payments.merchant_id',$id)
                 ->where('participent_payments.is_payment', 1)->count();
                $investor_assign_status=MerchantUser::whereIn('merchant_user.status', [1, 3])->where('merchant_id',$id)->join('users','users.id','merchant_user.user_id')->join('user_has_roles','users.id','user_has_roles.model_id')->where('role_id','!=',User::OVERPAYMENT_ROLE)->count();
                $lender_data = [];
                $lender_login = $request->user()->hasRole(['lender']);
                $user = User::where('id', $merchant->user_id)->first();
                if ($lender_login) {
                    $lender_data['id'] = $request->user()->id;
                    $lender_data['name'] = $request->user()->name;
                    $lender_data['management_fee'] = $request->user()->management_fee;
                    $lender_data['syndication_fee'] = $request->user()->global_syndication;
                }
                $action = 'Edit';
                $industries = DB::table('industries')->select(['name', 'id'])->orderBy('name')->get();
                $states = DB::table('us_states')->select(['state', 'id'])->get();
                $merchant_source = DB::table('merchant_source')->select(['name', 'id'])->get();
                $assigned_investors_funded_amount = MerchantUser::where('merchant_id', $id)->sum('amount');
                $label = $this->label->getAll()->pluck('name', 'id');
                $statusList = $this->merchant->get_substatus_list($merchant);
                $statuses = $this->subStatus->getAll()->whereIn('id', $statusList)->pluck('name', 'id');
                $admins = $this->role->allLenders();
                $investors = $this->role->allInvestors();
                $syndication_fee_values=FFM::fees_array();
                $page_title = 'Edit Merchant';
                $title = 'Edit Merchant';
                $default_lender= User::where('id',74)->select('global_syndication','management_fee','s_prepaid_status','underwriting_fee','underwriting_status','id')->first();
                $liquidity = DB::table('user_details')->select(DB::raw('sum(user_details.liquidity) as liquidity'), 'users.company', 'users.name')->join('users', 'users.id', 'user_details.user_id')->groupBy('company')->get()->toArray();
                $companies = $this->role->allSubAdmins()->pluck('name', 'id')->toArray();
                $underwriting_company = $this->role->allCompanies()->pluck('name', 'id')->toArray();
                if (! is_array($underwriting_company)) {
                    $underwriting_company = [];
                }
                array_unshift($underwriting_company, '');
                unset($underwriting_company[0]);
                $company_d = [];
                $company = $companies;
                $companyAmount = CompanyAmount::where('merchant_id', $id)->pluck('max_participant', 'company_id')->toArray();
                if (! empty($companyAmount)) {
                    foreach ($companyAmount as $key => $value) {
                        $company_d[$key]['max_participant'] = $value;
                        if (isset($merchant->max_participant_fund)) {
                            if (! empty($merchant->max_participant_fund)) {
                                if ($merchant->max_participant_fund && $merchant->max_participant_fund != 0) {
                                    $company_d[$key]['max_participant_percentage'] = $value / $merchant->max_participant_fund * 100;
                                } else {
                                    $company_d[$key]['max_participant_percentage'] = 0;
                                }
                            } else {
                                $company_d[$key]['max_participant_percentage'] = 0;
                            }
                        } else {
                            $company_d[$key]['max_participant_percentage'] = 0;
                        }
                    }
                }
              
              $merchant->factor_rate = $merchant->factor_rate + 0;
              return ['lender_login'=>$lender_login,'lender_data'=>$lender_data,'action'=>$action,'syndication_fee_values'=>$syndication_fee_values,'statuses'=>$statuses,'investors'=>$investors,'merchant'=>$merchant,'admins'=>$admins,'merchant_source'=>$merchant_source,'states'=>$states,'industries'=>$industries,'assigned_investors_funded_amount'=>$assigned_investors_funded_amount,'page_title'=>$page_title,'liquidity'=>$liquidity,'user'=>$user,'payment_status'=>$payment_status,'company'=>$company,'company_d'=>$company_d,'companies'=>$companies,'underwriting_company'=>$underwriting_company,'label'=>$label,'investor_assign_status'=>$investor_assign_status,'default_lender'=>$default_lender];
          }

    }
    public function storeMerchant($request)
    {
      try
        {

        DB::beginTransaction();
        $user = User::where('email', $request->email)->first();
        $validator=$request->validate(['email' => ($user) ? 'unique:users,email,'.$user->id : 'unique:users']);
        $label=$request->label;
        if (in_array($label, [Label::MCA, Label::LutherSales])) {
            if (fmod($request->funded, 1) !== 0.00) {
                throw new Exception('Decimals in funded amount only for insurance merchants', 1);
            }
            if (fmod($request->max_participant_fund, 1) !== 0.00) {
                throw new Exception('Decimals in Maximum Participant Fund Amount allowed  only for Insurance Merchants!', 1);
            }
            if (fmod(($request->funded/100), 1) !== 0.00) {
                // throw new Exception("1's and 10's should be zero for MCA and LutherSales funded amount", 1);
            }
            if (fmod(($request->max_participant_fund/100), 1) !== 0.00) {
                // throw new Exception("1's and 10's should be zero for MCA and LutherSales max participant fund amount", 1);
            }
        }
        for ($iDecimals = 0; $request->factor_rate != round($request->factor_rate, $iDecimals); $iDecimals++);
        if ($iDecimals > 4) {
            throw new \Exception('Factor rate should not contain more than 4 decimals', 1);
        }
        $loop = 0;
        $company_max_array = $request->company_max;
        reset_company_amount:
        $company_max = round(array_sum($company_max_array), 4);
        $max_participant_fund = round($request->max_participant_fund, 4);
        if ($max_participant_fund != $company_max) {
            foreach ($request->company_per as $single_company => $single_company_percentage) {
                $company_max_array[$single_company] = $max_participant_fund * $single_company_percentage / 100;
            }
            $loop++;
            if ($loop == 1) {
                goto reset_company_amount;
            }
            if($max_participant_fund<$company_max){
                throw new \Exception('Company Share should not be greater than Maximum Participant Fund');
            }else{
                throw new \Exception('Company Share not completed for this merchant. Max Participant Fund  : '.FFM::dollar($max_participant_fund).' - Company Amount '.FFM::dollar($company_max), 1);
            }
        }
        $request->company_max = $company_max_array;

        $request->factor_rate = floatval($request->factor_rate);
        for ($iDecimals = 0; $request->factor_rate != round($request->factor_rate, $iDecimals); $iDecimals++);
        if ($iDecimals > 4) {
            throw new \Exception('Factor rate should not contain more than 4 decimals', 1);
        }
        if (in_array($request->label, [1, 2, 3, 4, 5])) {
            if ($request['funded'] <= 0) {
                throw new \Exception('Funded amount should be greater than zero', 1);
            }
        }
        $merchant = $this->merchant->createRequest($request);
        if ($request->account_number && $request->bank_name && $request->account_holder_name && $request->routing_number) {
            if ($request->type) {
                $bank_type = implode(',', $request->type);
                $bank_type .= ',debit';
            } else {
                $bank_type = 'debit';
            }
            $bank_params = [
                'account_number'      => $request->account_number,
                'routing_number'      => $request->routing_number,
                'bank_name'           => $request->bank_name,
                'account_holder_name' => $request->account_holder_name,
                'merchant_id'         => $merchant->id,
                'default_credit'      => $request->default_credit ?? 0,
                'default_debit'       => 1,
                'type'                => $bank_type
            ];
            $BankModel = new MerchantBankAccount;
            $return_function = $BankModel->selfCreate($bank_params);
        }
        $bank_account_count = MerchantBankAccount::where('merchant_id', $merchant->id)->where('default_debit', 1)->count();
        if ($bank_account_count) {
            if ($request->has('ach_pull')) {
                $ach_pull = 1;
            } else {
                $ach_pull = 0;
            }
            $ach_pull_update = $merchant->update(['ach_pull' => $ach_pull]);
        }
        if ($request->marketplace_status == 1 && $request->marketplace_status != 17 && $request->notify_investors == 1) {
            $message['timestamp'] = time();
            $message['title'] = 'New deal';
            $message['content'] = 'A new deal has been uploaded to the marketplace. It may be of interest to you.  Please login and have a look.';
            $message['merchant_id'] = $merchant->id;
            $message['status'] = 'new_deal';
            $message['app_status'] = 'investor_app';
            $message['type'] = $merchant->type;
            $message['merchant_name'] = $merchant->name;
            $investors = $this->role->allInvestors()->where('investor_type', 5)->where('active_status', 1)->pluck('id')->toArray();
            $emails = $this->role->allInvestors()->where('investor_type', 5)->where('active_status', 1)->pluck('notification_email')->toArray();
            $new_mails = [];
            if ($emails) {
                foreach ($emails as $email) {
                    $new_array = explode(',', $email);
                    $new_mails = array_merge($new_array, $new_mails);
                }
            }
            $message['investors'] = $investors;
            $message['to_mail'] = $new_mails;
            $message['bcc'] = $new_mails;
            $message['type'] = 'merchants';
            $message['unqID'] = unqID();
            $message['user_ids'] = json_encode($investors, true);
            $email_template = Template::where([ ['temp_code', '=', 'MPLCE'], ['enable', '=', 1], ])->first();
            if ($email_template) {
                $emails = Settings::value('email');
                $emailArray = explode(',', $emails);    
                $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                dispatch($emailJob);
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $new_mails);
                        $role_mails = array_diff($role_mails, $emailArray);
                        $bcc_mails[] = $role_mails;
                    }
                    $message['bcc'] = Arr::flatten($bcc_mails);
                }
                $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
                $message['to_mail'] = $emailArray;
                $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                dispatch($emailJob);
                $message['bcc'] = [];
                $message['to_mail'] = $admin_email;
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
            }
            $json_data = ['merchant_name' => $merchant->name, 'funded' => $merchant->funded, 'date_funded' => $merchant->date_funded];
            $message['json_data'] = json_encode($json_data, true);
            \EventHistory::pushNotifyInvestor($message);
        }
        $msg = '';
        if ($request->email_notification == 1 && ($request->email)) {
            $merchants = Merchant::select('merchants.name', 'users.email', 'merchants.id', 'merchants.notification_email', 'merchants.user_id')->where('merchants.id', $merchant->id)->leftJoin('users', 'users.id', 'merchants.id', 'merchants.user_id')->first()->toArray();
            $user = User::where('id', $merchants['user_id'])->count();
            if (($user) < 1) {
                return redirect()->back()->withErrors(['email' => trans('User does not exist')]);
            }
            $status = Password::sendResetLink($request->only('email'));
            $msg .= ' <br> A reset link has been sent to merchant email address.';
        }
       
        $result['result']='success';
        $result['msg']=$msg;
        $result['merchant_id']=$merchant->id;
        DB::commit();

    }
     catch (\Exception $e) {
         DB::rollback();

        $result['result']='failed';
        $result['msg']=$e->getMessage();

     }
       return $result;
    }
    public function updateMerchant($request)
    {
        try{
        DB::beginTransaction();
        $merchantId = $request->merchant_id;
        $merchant = \App\Merchant::findOrFail($merchantId);
        $payment_started = DB::table('participent_payments')->where('merchant_id', $merchantId)
        ->where('participent_payments.is_payment', 1)
        ->count();
        $update = false;
        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        $user_id = $request->input('user_id');
        $merchantEmail = $request->input('merchant_email');
        $merchant->email = $merchantEmail;
        $loop = 0;
        $company_max_array = $request->company_max;
        $company_max_array = array_map(function($val){return is_null($val)? 0 : $val;},$company_max_array);
        reset_company_amount:
        $company_max = round(array_sum($company_max_array), 4);
        $max_participant_fund = round($request->max_participant_fund, 4);
        if (! $payment_started) {
            if ($max_participant_fund != $company_max) {
                foreach ($request->company_per as $single_company => $single_company_percentage) {
                    $company_max_array[$single_company] = $max_participant_fund * $single_company_percentage / 100;
                }
                $loop++;
                if ($loop == 1) {
                    goto reset_company_amount;
                }
                if($max_participant_fund<$company_max){
                    throw new \Exception('Company Share should not be greater than Maximum Participant Fund.');
                }else{
                    throw new \Exception('Company Share not completed for this merchant. Max Participant Fund  : '.FFM::dollar($max_participant_fund).' - Company Amount '.FFM::dollar($company_max), 1);
                }
             
              //  return redirect()->back()->withInput()->withErrors('Company Share not completed for this merchant. Max Participant Fund  : '.FFM::dollar($max_participant_fund).' - Company Amount '.FFM::dollar($company_max));
            }
        }
        $request->company_max = $company_max_array;
        $request->factor_rate = floatval($request->factor_rate);
        for ($iDecimals = 0; $request->factor_rate != round($request->factor_rate, $iDecimals); $iDecimals++);
        if ($iDecimals > 4) {
            throw new \Exception('Factor rate should not contain more than 4 decimals', 1);
        }
        $admin_emails = $this->role->allAdminUsers()->pluck('email')->toArray();
        if (in_array($request->email, $admin_emails)) {
            throw new \Exception('Admin email id not possible to use  for this merchant !!', 1);
           // return redirect()->back()->withInput()->withErrors('Admin email id not possible to use  for this merchant !!');
        }
        if ($user) {
            $merchant->user_id = $user->id;
            $merchant->update();
        }
        if (isset($request->password) && $request->password != '') {
            $user_id = Merchant::where('id', $request->merchant_id)->value('user_id');
            $user = ! $user ? User::where('id', $user_id)->first() : $user;
            if ($user) {
                $user->password = ($request->password);
                $user->update();
            }
        }
        $merchant = $this->merchant->updateRequest($request);
        if (isset($request->label) && $request->label != '') {
            if (in_array($request->label, [Label::MCA, Label::LutherSales])) {
                $MerchantUserFunedAmount=MerchantUser::where('merchant_id', $request->merchant_id)->whereIn('status',[1, 3])->sum('amount');
                if($MerchantUserFunedAmount==0){
                    if (fmod($request->funded, 1) !== 0.00) {
                        throw new Exception('Decimals in funded amount only for insurance merchants', 1);
                    }
                    if (fmod($request->max_participant_fund, 1) !== 0.00) {
                        throw new Exception('Decimals in Maximum Participant Fund Amount allowed  only for Insurance Merchants!', 1);
                    }
                    if (fmod(($request->funded/100), 1) !== 0.00) {
                        // throw new Exception("1's and 10's should be zero for MCA and LutherSales funded amount", 1);
                    }
                    if (fmod(($request->max_participant_fund/100), 1) !== 0.00) {
                        // throw new Exception("1's and 10's should be zero for MCA and LutherSales max participant fund amount", 1);
                    }
                }
            }
            Merchant::where('id', $request->merchant_id)->update(['label' => $request->label]);
        }
        $userIds = MerchantUser::where('merchant_id', $request->merchant_id)->pluck('user_id');
        if ($request->marketplace_status == 1 && $request->sub_status_id != 17 && $request->notify_investors == 1) {
            $message['timestamp'] = time();
            $message['title'] = 'New deal';
            $message['content'] = 'A new deal has been uploaded to the marketplace. It may be of interest to you.  Please login and have a look.';
            $message['merchant_id'] = $request->merchant_id;
            $message['status'] = 'new_deal';
            $message['app_status'] = 'investor_app';
            $message['type'] = $request->type;
            $message['merchant_name'] = $request->name;
            $investors = $this->role->allInvestors()->where('investor_type', 5)->where('active_status', 1)->pluck('id')->toArray();
            $emails = $this->role->allInvestors()->where('investor_type', 5)->where('active_status', 1)->pluck('notification_email')->toArray();
            $new_mails = [];
            if ($emails) {
                foreach ($emails as $email) {
                    $new_array = explode(',', $email);
                    $new_mails = array_merge($new_array, $new_mails);
                }
            }
            $message['investors'] = $investors;
            $message['to_mail'] = $new_mails;
            $message['bcc'] = $new_mails;
            $message['type'] = 'merchants';
            $message['unqID'] = unqID();
            $message['user_ids'] = json_encode($investors, true);
            try {
                $email_template = Template::where([ ['temp_code', '=', 'MPLCE'], ['enable', '=', 1], ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $new_mails);
                            $bcc_mails[] = $role_mails;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc'] = [];
                    $message['to_mail'] = $admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        MerchantUpdate:
        if ($request->account_number && $request->bank_name && $request->account_holder_name && $request->routing_number) {
            if ($request->type) {
                $bank_type = implode(',', $request->type);
                $bank_type .= ',debit';
            } else {
                $bank_type = 'debit';
            }$bank_params = [
                'account_number' => $request->account_number,
                'routing_number' => $request->routing_number ?? '',
                'bank_name' => $request->bank_name,
                'account_holder_name' => $request->account_holder_name,
                'merchant_id' => $merchant->id,
                'default_credit' => $request->default_credit ?? 0,
                'default_debit' => 1,
                'type' => $bank_type
            ];
            $BankModel = new MerchantBankAccount;
            $return_function = $BankModel->selfCreate($bank_params);
        }
        $bank_account_count = MerchantBankAccount::where('merchant_id', $merchant->id)->where('default_debit', 1)->count();
        if ($bank_account_count) {
            if ($request->has('ach_pull')) {
                $ach_pull = 1;
            } else {
                $ach_pull = 0;
            }
            if ($merchant->ach_pull != $ach_pull)
            {
                $update = true;
                $merchant->ach_pull = $ach_pull;
            }
        }
        if ($update) {
            $merchant->save();
        }
        $msg = '';
        if ($request->email_notification == 1 && ($request->email)) {
            $status = Password::sendResetLink($request->only('email'));
            $msg .= '<br> A reset link has been sent to merchant email address.';
        }
        $result['result']='success';
        $result['msg']=$msg;
        $result['merchant_id']=$merchant->id;

        DB::commit();
    }
    catch (\Exception $e) {
            DB::rollback();
            $result['result']='failed';
            $result['msg']=$e->getMessage();

     }

     return $result;

       
    }

    public function merchantPayments($extra=[])
    {
        $id=$extra['id'];
        $company_id=$extra['company_id'];
        $investor_id=$extra['investor_id'];
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
        $data = $this->payment->getMerchantPayments($id, $company_id, $investor_id, $AgentFeeAccount->id);
        return \DataTables::of($data)->editColumn('participant_payment', function ($data) {
            $array = [];
            foreach ($data->paymentAllInvestors as $key => $value) {
                $name = substr($value->name, 0, 10).'..';
                $array[] = [
                    'participant'             => strtoupper($name),
                    'participant_full_name'   => $value->name,
                    'participant_id'          => $value->id,
                    'syndication_amount'      => FFM::dollar($value->actual_participant_share),
                    'real_syndication_amount' => '$'.number_format($value->actual_participant_share, 4),
                    'mgmnt_fee'               => FFM::dollar($value->mgmnt_fee),
                    'to_participant'          => FFM::dollar($value->actual_participant_share-$value->mgmnt_fee),
                    'principal'               => FFM::dollar($value->principal),
                    'profit'                  => FFM::dollar($value->profit),
                    'overpayment'             => FFM::dollar($value->actual_overpayment)
                ];
            }
            if (! empty($array)) {
                usort($array, function ($a, $b) {
                    return $b['participant_id'] <=> $a['participant_id'];
                });
            }

            return $array;
        })->addColumn('checkbox', function ($data) {
            $id = $data->id;
            $ach_revert = AchRequest::where('revert_id', $data->id)->count();
            if ($ach_revert == 0) {
                return "<label class='chc'><input type='checkbox' class='delete_bulk' name='delete_bulk[]' value='$id' onclick='uncheckMain();'><span class='checkmark checkk0'></span></label>";
            }
        })->addColumn('debit_reason', function ($data) {
            $rcode1 = $data->rcode_id ? ' - '.$data->rcode_id : '';
            $reason_rcode = $data->reason.$rcode1;

            return $reason_rcode;
        })->addColumn('action', function ($row) {
            $return = '';
            $ach_revert = AchRequest::where('revert_id', $row->id)->count();
            if ($row->mode_of_payment != ParticipentPayment::PaymentModeCreditCard) {
                if ($row->payment > 0 && ! $row->revert_id && $ach_revert == 0) {
                    if (Permissions::isAllow('Add Payment', 'Create')) {
                        $return .= '<span class="col-md-1"><button  date="'.FFM::date($row->payment_date).'" payment="'.FFM::dollar($row->payment).'" table_id="'.$row->id.'" class="btn btn-xs btn-info revert_button" title="Revert"><i class="glyphicon glyphicon-transfer"></i></button></span>';
                    } else {
                        $return.='<span class="col-md-1"></span>';
                    }
                } else {
                    $return.='<span class="col-md-1"></span>';
                }
            }
            if (env('APP_ENV') == 'local') {
                if (Permissions::isAllow('Add Payment', 'Delete')) {
                    $return .= '<span class="col-md-4">'.Form::open(['route' => ['admin::payments::reGeneratePayment', 'id' => $row->id, 'type'=>'All'], 'method' => 'GET', 'onsubmit' => 'return confirm("Are you sure want to Re Generate It With All the Investors?")']).Form::submit('ReG-All', ['class' => 'btn btn-xs btn-warning','restriction_disable'=>'true']).Form::close().'</span>';
                }
                if (Permissions::isAllow('Add Payment', 'Delete')) {
                    $return .= '<span class="col-md-4">'.Form::open(['route' => ['admin::payments::reGeneratePayment', 'id' => $row->id, 'type'=>'Selected'], 'method' => 'GET', 'onsubmit' => 'return confirm("Are you sure want to Re Generate It With Selected Investors?")']).Form::submit('ReG-Seleted', ['class' => 'btn btn-xs btn-warning','restriction_disable'=>'true']).Form::close().'</span>';
                }
            }
            if (Permissions::isAllow('Add Payment', 'Delete')) {
                if ($ach_revert == 0) {
                    $return .= '<span class="col-md-1 pull-right">'.Form::open(['route' => ['admin::payments::delete', 'id' => $row->id], 'method' => 'POST', 'id'=>'delete_payment_form'.$row->id, 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::button('<i class="glyphicon glyphicon-trash"></i>', ['type'=>"submit",'title'=>"Delete",'class' => 'btn btn-xs btn-danger','restriction_disable'=>'true']).Form::close().'</span>';
                } 
            }

            return $return;
        })->addColumn('profit', function ($data) {
            return FFM::dollar($data->profit_value);
        })->addColumn('principal', function ($data) {
            return FFM::dollar($data->principal);
        })->editColumn('payment', function ($data) {
            return "<span title='$".number_format($data->payment, 4)."'>".FFM::dollar($data->payment).'</span>';
        })->editColumn('final_participant_share', function ($data) use ($AgentFeeAccount) {
            $agent_fee = 0;
            $final_participant_share = $data->final_participant_share;
            foreach ($data->paymentAllInvestors as $key => $value) {
                if ($value->id == $AgentFeeAccount->id) {
                    $agent_fee = $value->actual_participant_share;
                }
            }
            if ($agent_fee != 0) {
                return '<span title="$'.number_format($data->mgmnt_fee + $final_participant_share, 4).' (With fee of $'.number_format($data->mgmnt_fee, 4).')">'.FFM::dollar($final_participant_share).'</span><div class="help-tip"></div>'.'<p>Agent Fee : '.FFM::dollar($agent_fee).'</p>';
            } else {
                return '<span title="$'.number_format($data->mgmnt_fee + $final_participant_share, 4).' (With fee of $'.number_format($data->mgmnt_fee, 4).')">'.FFM::dollar($final_participant_share).'</span>';
            }
        })->editColumn('payment_date', function ($data) {
            $mode = $creator = '';
            $user = ($data->owner) ? $data->owner->name : '--';
            $created_date = 'Created On '.FFM::datetime($data->created_at);
            if ($data->mode_of_payment == 1) {
                $mode = 'ACH';
            }
            if ($data->mode_of_payment == 0) {
                $mode = 'Manual';
                $creator = $created_date.' by '.$user;
            }
            if ($data->mode_of_payment == 2) {
                $mode = 'Credit Card Payment';
            }
            $creator = $created_date.' by '.$user;

            return "<a title='$creator' style='text-decoration: none;'>".FFM::date($data->payment_date).'</a>';
        })->editColumn('mode_of_payment', function ($data) {
            $mode = $creator = '';
            $user = ($data->owner) ? $data->owner->name : '--';
            $created_date = 'Created On '.FFM::datetime($data->created_at);
            if ($data->mode_of_payment == 1) {
                $mode = 'ACH';
            }
            if ($data->mode_of_payment == 0) {
                $mode = 'Manual';
                $creator = $created_date.' by '.$user;
            }
            if ($data->mode_of_payment == 2) {
                $mode = 'Credit Card Payment';
                if ($data->approved_at) {
                    $creator = 'Approved On ' . FFM::datetime($data->approved_at);
                }
                if ($data->approved_by) {
                    $approved_by = $data->approved_by;
                    $creator .= ' by ' . $approved_by;
                }
            }

            return "<a title='$creator' style='text-decoration: none;'>".$mode.'</a>';
        })->filterColumn('investor_name', function ($query, $keyword) {
            $sql = 'users.name like ?';
            $query->WhereRaw($sql, ["%$keyword%"]);
        })->filterColumn('payment_date', function ($query, $keyword) {
            $sql = 'DATE_FORMAT(payment_date, "%m-%d-%Y") like ?';
            $query->WhereRaw($sql, ["%$keyword%"]);
        })->rawColumns(['checkbox', 'action', 'payment_date', 'final_participant_share', 'payment', 'mode_of_payment'])->make(true);
        
    }

    public function isMarketPlace(int $merchantId)
    {
        return Merchant::where('id', $merchantId)->where('marketplace_status', 1)->first();
    }

    public function getDetails(int $merchantId, int $companyId = 0)
    {
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $merchant = Merchant::select('merchants.*')->where('merchants.id', $merchantId)->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')->with(['lendor' => function ($query) {
            $query->select('name', 'id', 'lag_time');
        }])->with(['payStatus' => function ($query) {
            $query->select('name', 'id');
        }])->first();
        $merchant->pmnts = ($merchant->pmnts) ? $merchant->pmnts : 1;
        $merchant->factor_rate = round($merchant->factor_rate, 2);
        $investorIds = User::investors()->pluck('id')->toArray();
        $overPaymentQuery = MerchantUser::where('merchant_id', $merchantId)->select(DB::raw('sum(commission_amount + amount + pre_paid + under_writing_fee) as net_zero'), DB::raw('
                sum(
                    ( 
                        ( (total_agent_fee+paid_participant_ishare) - invest_rtr ) * ( 1- ( merchant_user.mgmnt_fee) / 100 ) 
                    )
                ) as overpayment'));
        if (! empty($companyId)) {
            $overPaymentQuery->join('users', 'users.id', 'merchant_user.user_id')->where('users.company', $companyId);
        }
        if (empty($permission)) {
            $overPaymentQuery->whereIn('merchant_user.user_id', $investorIds);
        }
        $merchantOverPayment = $overPaymentQuery->first();
        $netZero = $merchantOverPayment ? $merchantOverPayment->net_zero : 0;
        $overPayments = $merchantOverPayment ? $merchantOverPayment->overpayment : 0;
        $companyInvestors = MerchantUser::where('merchant_id', $merchantId);
        if (! empty($companyId)) {
            $companyInvestors->join('users', 'users.id', 'merchant_user.user_id')->where('users.company', $companyId);
        }
        $companyInvestors = $companyInvestors->count();
        if ($companyInvestors == 0 && $companyId != 0) {
            $paymentLeft = 0;
        } else {
            $totalPayments = ParticipantPaymentHelper::getUniqueDatePayments($merchantId, 1, $companyId, true);
            $paymentLeft = $merchant->pmnts - $totalPayments;
        }
        if ($merchant->complete_percentage > 99) {
            $paymentLeft = 0;
        }
        $lag_time = isset($merchant->lendor->lag_time) ? $merchant->lendor->lag_time : 0;
        $start = Carbon::parse($merchant->first_payment);
        $end = Carbon::now()->now();
        if ($merchant->advance_type == 'weekly_ach') {
            $nu_payment_days = ceil(($start->diffInDays()) / 7);
        } else {
            $nu_payment_days = PayCalc::calculateWorkingDays($start, $end);
        }
        $paymentTotal = ParticipantPaymentHelper::getMerchantPaymentSum($merchantId);
        $merchantPayments = ParticipantPaymentHelper::getMerchantPayments($merchantId, $investorIds, $companyId);
        $total_mgmnt_paid = $total_syndication_paid = $paid_to_participant = $ctd_our_portion = $net_total_syndication_paid2 = $total_payment = $profit_sum = $ctd_sum = $final_participant_share = $totalParticipantShare = $profit_value = $principal = 0;
        $totalParticipantShare = array_sum(array_column($merchantPayments->toArray(), 'participant_share'));
        $total_payment += array_sum(array_column($merchantPayments->toArray(), 'payment'));
        $final_participant_share = $final_participant_share + array_sum(array_column($merchantPayments->toArray(), 'final_participant_share'));
        $profit_value = $profit_value + array_sum(array_column($merchantPayments->toArray(), 'profit_value'));
        $principal += array_sum(array_column($merchantPayments->toArray(), 'principal'));
        $ctd_sum = $total_payment;
        $ctd_our_portion = $final_participant_share;
        $total_payment1 = $total_payment;
        $total_payment = FFM::dollar($total_payment);
        $final_participant_share = FFM::dollar($final_participant_share);
        $profit_value = FFM::dollar($profit_value);
        $principal = FFM::dollar($principal);
        $investorSum = MerchantUser::join('merchants', 'merchants.id', 'merchant_user.merchant_id')->select(DB::raw('SUM(amount) as amount, SUM(invest_rtr) as invest_rtr, 
                    SUM(total_agent_fee) as total_agent_fee,SUM(paid_participant_ishare) as paid_participant_ishare,
                    ( invest_rtr ) * merchant_user.mgmnt_fee/100 as fee, 
                    SUM(commission_amount+under_writing_fee+pre_paid) as pre_paid'), 'merchants.rtr as rtr', 'merchants.funded', 'merchants.max_participant_fund')->where('merchant_user.merchant_id', $merchantId);
        if (empty($permission)) {
            $investorSum->join('users', 'users.id', 'merchant_user.user_id')->whereIn('merchant_user.user_id', $investorIds);
        }
        if ($companyId != 0) {
            $investorSum->where('users.company', $companyId);
        }
        $investorSum = $investorSum->first();
        $total_rtr = $investorSum->invest_rtr;
        $fee = $investorSum->fee;
        $balance = $investorSum->rtr - $paymentTotal;

        $balance_our_portion = ($investorSum->invest_rtr) - ($investorSum->paid_participant_ishare+$investorSum->total_agent_fee);
        $balance_merchant = $merchant->rtr - $total_payment1;
        if ($total_rtr == 0) {
            $balance_merchant = 0;
        }
        $dates = [];
        $dates2 = ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->where('participent_payments.is_payment', 1)->distinct('payment_date')->pluck('payment_date')->toArray();
        $no_of_payments=ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->where('participent_payments.is_payment', 1)->count();
        $net_total_syndication_paid = FFM::dollar($net_total_syndication_paid2);
        $total_principal = FFM::dollar($net_total_syndication_paid2 - $profit_sum);
        $total_profit = FFM::dollar($profit_sum);
        $paid_payments = $merchant->pmnts - $paymentLeft;
        $expected_amount = $merchant->payment_amount * $paid_payments;
        $amount_difference = abs($ctd_sum - $expected_amount);
        $part_total_per = $syndication_fee = $part_total_amount = $management_fee = 0;
        $investorData = MerchantUser::select(['merchant_user.id', 'merchant_user.created_at', 'user_id', 'amount', 'status', 'invest_rtr', 'paid_participant_ishare', 'users.name', 'merchant_user.under_writing_fee', 'merchant_user.under_writing_fee_per', 'merchant_user.syndication_fee_percentage', DB::raw('merchant_user.invest_rtr * merchant_user.mgmnt_fee /100 as mgmnt_fee_amount'), DB::raw('merchant_user.pre_paid'), 'merchant_user.mgmnt_fee'])->leftJoin('users', 'users.id', 'merchant_user.user_id');
        if ($companyId != 0) {
            $investorData = $investorData->where('users.company', $companyId);
        }
        $investorData = $investorData->where('merchant_user.merchant_id', $merchantId);
        if (empty($permission)) {
            $investorData = $investorData->whereIn('user_id', $investorIds);
        }
        $investorData = $investorData->get();
        $totalManagementFee = 0;
        $totalSyndicationFee = 0;
        $totalUnderWritingFee = 0;
        foreach ($investorData as $key => $investor) {
            $investorData[$key]['paid_back'] = $merchant->paid_participant_ishare;
            $totalManagementFee = 0;
            $totalSyndicationFee = $totalSyndicationFee + $investor->syndication_fee_amount;
            $totalUnderWritingFee = $totalUnderWritingFee + $investor->under_writing_fee;
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
        $part_total_per = $part_total_amount / $merchant->funded * 100;
        $syndication_percent = FFM::percent($part_total_amount / $merchant->funded * 100);
        $syndication_payment = FFM::dollar((($merchant->rtr / $merchant->pmnts) * $part_total_per / 100) - $syndication_fee - $management_fee);
        $syndication_amount = FFM::dollar($part_total_amount);
        $bal_rtr = $total_rtr - $totalParticipantShare;
        if ($total_rtr > 0) {
            $actual_payment_left = $bal_rtr / (($total_rtr / $merchant->rtr) * ($merchant->rtr / $merchant->pmnts));
        } else {
            $actual_payment_left = 0;
        }
        if ($companyInvestors == 0 && $companyId != 0) {
            $actual_payment_left = 0;
        }
        $num_due_payments = $nu_payment_days < $merchant->pmnts ? $nu_payment_days : $merchant->pmnts;
        $payment_count = $merchant->pmnts - $actual_payment_left;
        $num_pace_payment = ($total_rtr) ? floor($num_due_payments - $payment_count) : 0;
        if ($num_due_payments) {
            $pace_amount = $num_pace_payment * ($merchant->rtr / ($merchant->pmnts));
            $our_pace_amount = $num_pace_payment * ($investorSum->invest_rtr / ($merchant->pmnts));
            $num_pace_percentage = 100 - ($num_pace_payment / $num_due_payments * 100);
        } else {
            $pace_amount = 0;
            $our_pace_amount = 0;
            $num_pace_percentage = 100;
        }

        $processing_ach_payments = TermPaymentDate::where('merchant_id', $merchantId)->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');

        $balance_final = $balance_merchant - $processing_ach_payments;

        if ($balance_final < $merchant->payment_amount) {

            if ($balance_final <= 0) {

                $current_payment_amount = 0;

            } else {

                $current_payment_amount = $balance_final;

            }

        } else {
            $current_payment_amount = $merchant->payment_amount;
        }

        $merchant->date_funded = FFM::date($merchant->date_funded);
        $merchant->funded = FFM::dollar($merchant->funded);
        $merchant->payment_amount = FFM::dollar($merchant->payment_amount);
        $pmnts = $merchant->pmnts;
        $merchant->pmnts = number_format($merchant->pmnts);
        $merchant->rtr = FFM::dollar($merchant->rtr);
        $fractional_part = fmod($actual_payment_left, 1);
        $act_paymnt_left = floor($actual_payment_left);
        if ($fractional_part > .09) {
            $act_paymnt_left = $act_paymnt_left + 1;
        }
        $merchant->actual_payment_left = number_format($act_paymnt_left);
        if ($totalParticipantShare > 0) {
            $merchant->payments_done = number_format($pmnts - $act_paymnt_left);
        } else {
            $merchant->payments_done = 0;
        }
        $merchant->actual_payment_left = ($act_paymnt_left >= 0) ? $act_paymnt_left : 'None';

        return [
            'merchant'                => $merchant,
            'current_payment_amount'  => sprintf('%.2f', $current_payment_amount),
            'payment_left'            => $paymentLeft,
            'amount_difference'       => $amount_difference,
            'syndication_percent'     => $syndication_percent,
            'syndication_amount'      => $syndication_amount,
            'syndication_payment'     => $syndication_payment,
            'ctd_sum'                 => $ctd_sum, 'net_zero' => $netZero,
            'overpayments'            => $overPayments,
            'profit_value'            => $profit_value,
            'balance_our_portion'     => FFM::dollar($balance_our_portion),
            'ctd_our_portion'         => $ctd_our_portion,
            'total_payment'           => $total_payment,
            'final_participant_share' => $final_participant_share,
            'principal'               => $principal,
            'investor_data'           => $investorData,
            'total_managmentfee'      => $totalManagementFee,
            'total_syndicationfee'    => $totalSyndicationFee,
            'total_underwrittingfee'  => $totalUnderWritingFee,
            'dates2'                  => $dates2,
            'actual_payment_left'     => ($actual_payment_left > 0) ? $actual_payment_left : 0,
            'balance_merchant'        => ($balance_merchant > 0) ? FFM::dollar($balance_merchant) : FFM::dollar(0),
            'num_pace_payment'        => $num_pace_payment, 'num_pace_percentage' => $num_pace_percentage,
            'pace_amount'             => $pace_amount, 'our_pace_amount' => $our_pace_amount,
            'prepaid'                 => $investorSum->pre_paid,
            'no_of_payments'          => $no_of_payments
        ];
    }

    public function allMerchantDownload($details,$disable_arr=array())
    {
        $excel_array[0] = ['No', 'Merchant Id','Centrex Advance ID', 'First Name', 'Last Name', 'Name', 'Status', 'Full RTR', 'Our RTR', 'Funded Amount', 'Our Funded Amount', 'Payments', 'Payment Amount', 'Advance Type', 'Our Payment amount', 'Last Payment Date', 'Complete %', 'Date Funded', 'Net Zero Balance', 'Our Balance', 'Our Balance after fee', 'Total Balance', 'Factor rate', 'Commission', 'Syndication Fee', 'Max Participant Fund', 'Annualized Rate', 'Payment left (RTR/Payment)', 'Lender Name', 'Default Amount', 'Industry', 'State', 'Anticipated Management Fee', 'Pace payment', 'Our pace balance', 'Agent/ISO Name', 'Business started Date', 'Under Writer', 'Entity Type', 'Owner Credit Score', 'Partner Credit Score', 'Withhold Percentage', 'Position', 'Deal Type', 'Monthly Revenue', 'Phone', 'Cell Phone', 'Email'];
        $i = 1;
        $dis_company_arr = [];
        if(count($disable_arr)>0){
        foreach($disable_arr as $dis_arr){
        $dis_company_arr[$dis_arr['mid2']]['amount'] = $dis_arr['amount'];
        $dis_company_arr[$dis_arr['mid2']]['invest_rtr'] = $dis_arr['invest_rtr'];
        $dis_company_arr[$dis_arr['mid2']]['balance'] = $dis_arr['balance'];
        $dis_company_arr[$dis_arr['mid2']]['balance_after_fee'] = $dis_arr['balance_after_fee'];
        }
        }
        $disable_funded_amount = $disable_company_rtr = $disable_company_balance = $disable_company_balance_after_fee = 0;
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                if(!empty($dis_company_arr)){
                $disable_funded_amount = isset($dis_company_arr[$data['mid2']]) ? $dis_company_arr[$data['mid2']]['amount'] : 0;
                $disable_company_rtr = isset($dis_company_arr[$data['mid2']]) ? $dis_company_arr[$data['mid2']]['invest_rtr']: 0;
                $disable_company_balance = isset($dis_company_arr[$data['mid2']]) ? $dis_company_arr[$data['mid2']]['balance'] : 0;
                $disable_company_balance_after_fee = isset($dis_company_arr[$data['mid2']]) ? $dis_company_arr[$data['mid2']]['balance_after_fee'] : 0;

                }
                $total_invest_amount_with_commission = 0;
                $invested_amount_with_commission = ($data['pre_paid'] + $data['amount'] + $data['commission_amount'] + $data['underwriting_fee']+$data['up_sell_commission']);
                $net_value = $invested_amount_with_commission;
                $ctd_sum = $total_payment = $paid_to_participant = 0;
                $dates = [];
                $paid_to_participant = $data['participant_share'] - $data['paid_fee'];
                $net_zero = $net_value - $paid_to_participant;
                $our_payment_amounts = ($data['total_funded'])>0 ? (round($data['payment_amount'] * $data['amount'] / $data['total_funded'], 2)) : 0;
                $disabled_company_payment_amount = ($data['total_funded'])>0 ? (round($data['payment_amount'] * $disable_funded_amount / $data['total_funded'], 2)) : 0;
                $our_balance = (round(($data['balance']-$disable_company_balance) > 0 ? ($data['balance']-$disable_company_balance) : 0, 2));
                $our_balance_after_fee = (round(($data['balance_after_fee']-$disable_company_balance_after_fee) > 0 ? ($data['balance_after_fee']-$disable_company_balance_after_fee) : 0, 2));
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Merchant Id'] = $data['mid2'];
                $excel_array[$i]['Centrex Advance ID'] = $data['centrex_advance_id'];
                $excel_array[$i]['First Name'] = $data['first_name'];
                $excel_array[$i]['Last Name'] = $data['last_name'];
                $excel_array[$i]['Name'] = $data['name'];
                $excel_array[$i]['Status'] = $data['status'];
                $excel_array[$i]['Full RTR'] = FFM::dollar($data['rtr']);
                $excel_array[$i]['Invest RTR'] = FFM::dollar($data['invest_rtr']-$disable_company_rtr);
                $excel_array[$i]['Funded Amount'] = FFM::dollar($data['total_funded']);
                $excel_array[$i]['Our Funded Amount'] = FFM::dollar($data['amount']-$disable_funded_amount);
                $excel_array[$i]['Payments'] = $data['pmnts'];
                $excel_array[$i]['Payment Amount'] = FFM::dollar($data['payment_amount']);
                $excel_array[$i]['Advance Type'] = ($data['advance_type']);
                $excel_array[$i]['Our Payment amount'] = FFM::dollar($our_payment_amounts-$disabled_company_payment_amount);
                $excel_array[$i]['Last Payment Date'] = FFM::date($data['last_payment_date']);
                $excel_array[$i]['Complete %'] = FFM::percent($data['complete_per']);
                $excel_array[$i]['Date Funded'] = FFM::date($data['date_funded']);
                $net_zero_balance = ($net_zero > 0) ? $net_zero : 0;
                $excel_array[$i]['Net Zero Balance'] = FFM::dollar($net_zero_balance);
                $excel_array[$i]['Our Balance'] = FFM::dollar($our_balance);
                $excel_array[$i]['Our Balance after fee'] = FFM::dollar($our_balance_after_fee);
                $balance333 = (($data['total_funded']>0) ? ($data['amount'] / $data['total_funded']) : 0)? ($data['total_funded']>0 && ($data['amount'] / $data['total_funded']>0) )?(round($data['balance'] / ($data['amount'] / $data['total_funded']), 2)): '0.00' : '0.00';
                $balance = (round($balance333 > 0 ? $balance333 : 0, 2));
                $excel_array[$i]['Balance'] = FFM::dollar($balance);
                $excel_array[$i]['Factor rate'] = round($data['factor_rate'], 4);
                $excel_array[$i]['Commission'] = FFM::percent($data['commission']);
                $excel_array[$i]['Prepaid'] = FFM::dollar($data['pre_paid']);
                $excel_array[$i]['Max Participant Fund'] = FFM::dollar($data['max_participant_fund']);
                $excel_array[$i]['Annualized Rate'] = ($data['annualized_rate']) ? FFM::percent($data['annualized_rate']) : FFM::percent(0);
                $excel_array[$i]['Payment left (RTR/Payment)'] =
                (($data['total_funded']>0) ? ($data['amount'] / $data['total_funded']) : '0.00') ?($data['sub_status_id'] == 11) ? '0.00' :(($data['total_funded']>0 && ($data['payment_amount']>0) )?(round(($data['balance'] / ($data['amount'] / $data['total_funded'])) / $data['payment_amount'])) : '0.00') : '0.00';
                $excel_array[$i]['Payment left (RTR/Payment)'] = ($excel_array[$i]['Payment left (RTR/Payment)'] <= 0) ? '0.00' : $excel_array[$i]['Payment left (RTR/Payment)'];
                $excel_array[$i]['Lender Name'] = $data['lender_name'];
                $excel_array[$i]['Default Amount'] = $data['sub_status_id'] == 4 ? FFM::dollar($data['default_amount']) : FFM::dollar(0);
                $excel_array[$i]['Industry'] = $data['industry_name'];
                $excel_array[$i]['State'] = $data['state'];
                $excel_array[$i]['Anticipated Management Fee'] = FFM::dollar($data['m_mgmnt_fee']);
                $first_payment_day_due = strtotime($data['first_payment']);
                if (! $first_payment_day_due) {
                    $first_payment_day_due = strtotime($data['date_funded']);
                }
                $start = Carbon::now()->createFromTimestamp($first_payment_day_due);
                $end = Carbon::now()->now();
                if ($data['advance_type'] == 'weekly_ach') {
                    $nu_payment_days = floor(($start->diffInDays()) / 7);
                } else {
                    $nu_payment_days = PayCalc::calculateWorkingDays($start, $end);
                }
                $nu_payment_days = $nu_payment_days < $data['pmnts'] ? $nu_payment_days : $data['pmnts'];
                $total_rtr = $data['invest_rtr'];
                $participant_share_total = $data['participant_share'];
                $bal_rtr = $total_rtr - $participant_share_total;
                if ($total_rtr > 0) {
                    $actual_payment_left = ($data['rtr']) ? $bal_rtr / (($total_rtr / $data['rtr']) * ($data['rtr'] / $data['pmnts'])) : 0;
                } else {
                    $actual_payment_left = 0;
                }
                $payment_count = $data['pmnts'] - $actual_payment_left;
                $payment_amount = $data['invest_rtr'] / $data['pmnts'];
                $disable_payment_amount = $disable_company_rtr/$data['pmnts'];
                $actual_number_of_payments = $payment_amount ? $data['participant_share'] / $payment_amount : 0;
                if (in_array($data['sub_status_id'], [1, 5])) {
                    $pace_payments_number = ($nu_payment_days - floor($payment_count));
                    $excel_array[$i]['pace payments'] = ($data['first_payment']) ? (($pace_payments_number >= 0) ? $pace_payments_number : 0) : '';
                    $our_pace_balance = ($data['first_payment']) ? ((round($pace_payments_number * ($payment_amount), 2)) >= 0 ? round($pace_payments_number * ($payment_amount), 2) : 0) : '';
                    $disable_pace_balance = ($data['first_payment']) ? ((round($pace_payments_number * ($disable_payment_amount), 2)) >= 0 ? round($pace_payments_number * ($disable_payment_amount), 2) : 0) : '';
                    $excel_array[$i]['Our pace balance'] = ($data['first_payment']) ? FFM::dollar($our_pace_balance-$disable_pace_balance): '' ;
                } else {
                    $excel_array[$i]['pace payments'] = '-';
                    $excel_array[$i]['Our pace balance'] = '-';
                }
                $excel_array[$i]['Agent Name'] = $data['agent_name'];
                $excel_array[$i]['Business started Date'] = FFM::date($data['date_business_started']);
                $excel_array[$i]['Under Writer'] = $data['under_writer'];
                $excel_array[$i]['Entity Type'] = $data['entity_type'];
                $excel_array[$i]['Owner Credit Score'] = ($data['credit_score']) ? $data['credit_score'] : $data['owner_credit_score'];
                $excel_array[$i]['Partner Credit Score'] = $data['partner_credit_score'];
                $excel_array[$i]['Withhold Percentage'] = FFM::percent($data['withhold_percentage']);
                $excel_array[$i]['Position'] = $data['position'];
                $excel_array[$i]['Deal Type'] = $data['deal_type'];
                $excel_array[$i]['Monthly Revenue'] = FFM::dollar($data['monthly_revenue']);
                $excel_array[$i]['Phone'] = $data['phone'];
                $excel_array[$i]['Cell Phone'] = $data['cell_phone'];
                $excel_array[$i]['Email'] = $data['notification_email'];
                $i++;
            }
        }

        return $excel_array;


    }

    public function getInvestorMerchant(int $userId, $activeStatus = 1)
    {
        return Merchant::whereHas('investors', function ($inner) use ($userId) {
            $inner->where('user_id', $userId);
        })->where('active_status', $activeStatus);
    }

    public function getFundsByDate(int $merchantId = 0, string $start_date = '', string $end_date = '')
    {
        return MerchantUser::leftJoin('merchants', 'merchant_user.merchant_id', 'merchants.id')->where('merchant_user.merchant_id', $merchantId)->whereIn('merchant_user.status', [1, 3])->groupBy(DB::raw('MONTH(merchants.date_funded)'))->where('merchants.date_funded', '>=', $start_date)->where('merchants.date_funded', '<=', $end_date)->select(DB::raw('SUM(merchant_user.amount) as funded'), DB::raw('MONTH(merchants.date_funded) as month'), DB::raw('YEAR(merchants.date_funded) as year'), DB::raw('SUM(merchant_user.invest_rtr) as rtr_month'))->get();
    }

    public function merchantsLatestPayments(int $merchantId = 0)
    {
        return ParticipentPayment::leftjoin('merchants', 'merchants.id', 'participent_payments.merchant_id')->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode')->where('participent_payments.merchant_id', $merchantId)->groupBy('payment_investors.participent_payment_id')->select('payment_date', 'participent_payments.payment_type', 'rcode.code', 'payment')->orderByRaw('participent_payments.payment_date DESC, participent_payments.id DESC')->limit(4)->get();
    }
    public function LiquidityBasedInvestment(int $merchant_id = 0)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
       
        $page_title="Assign Investors Based On Liquidity";
        $merchant=Merchant::find($merchant_id);
        $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        $companies = array_reverse($companies, true);
        $existing_investors1 = MerchantUser::where('merchant_id', $merchant->id);
        $paid_to_participant = 0;
        $investor_ids = $this->role->allInvestors()->pluck('id');
        if (empty($permission)) {
            $existing_investors1 = $existing_investors1->whereIn('user_id', $investor_ids);
        }
        $existing_investors = $existing_investors1->pluck('user_id')->toArray();
        $no_liquidity = UserDetails::where('liquidity', '<=', 0);
        if ($permission) {
            $no_liquidity = $no_liquidity->whereIn('user_id', $investor_ids);
        }
        $investor_with_no_liquidity = $no_liquidity->pluck('user_id')->toArray();
        $all_investors = $this->role->allInvestorsLiquidity('', '', 0);
        $all_investors = $all_investors->whereNOTIn('id', $existing_investors);
        $all_investors = $all_investors->whereNOTIn('id', $investor_with_no_liquidity);
        $all_investors = $all_investors->where('investor_type', '!=', 5);
        $selected_investors = $this->role->allInvestorsLiquidity();
        $selected_investors = $selected_investors->whereNOTIn('id', $existing_investors);
        $selected_investors = $selected_investors->whereNOTIn('id', $investor_with_no_liquidity);
        $selected_investors = $selected_investors->pluck('id');
        $selected_investors = $selected_investors->toArray();
        $CompanyAmount = CompanyAmountView::where('merchant_id', $merchant_id)->where('max_participant','!=',0)->get();
        $selected_companies=[];
        foreach ($CompanyAmount as $value) {
            $CompanyInvested = new MerchantUserView;
            $CompanyInvested = $CompanyInvested->where('company',$value->company_id);
            $CompanyInvested = $CompanyInvested->where('merchant_id',$merchant_id);
            $CompanyInvested = $CompanyInvested->sum('amount');
            $Company=$value->Company;
            $single=[
              'company_id'      => $value->company_id,
              'company_share'   => $value->company_share,
              'CompanyInvested' => $CompanyInvested,
              'max_participant' => $value->max_participant,
              'remaining'       => round($value->max_participant-$CompanyInvested,2),
            ];
            $selected_companies[$Company] = $single;
        }
        $balance  = $merchant->max_participant_fund;
        $balance -= MerchantUser::where('merchant_id',$merchant_id)->sum('amount');
        $tableBuilder=$this->tableBuilder->ajax( [
            'url'  => route('admin::merchants::Investment::LiquidityBased::Share'),
            'type' => 'POST',
            'data' => 'function(d){
                d._token      = "'.csrf_token().'";
                d.investors   = $("#investors").val();
                d.merchant_id = $("#merchant_id").val();
            }'
        ]);
        $tableBuilder = $tableBuilder->columns([
            [ 'data' => 'DT_RowIndex', 'name'         => 'DT_RowIndex', 'title'         => '#', 'searchable'                  => false, 'orderable' => false],
            [ 'data' => 'company', 'name'             => 'company', 'title'             => 'Company' ],
            [ 'data' => 'Investor', 'name'            => 'Investor', 'title'            => 'Investor' ],
            [ 'data' => 'commission', 'name'          => 'commission', 'title'          => 'Commission', 'className'          =>"text-right" ,'visible'=>true],
            [ 'data' => 'underwriting_fee', 'name'    => 'underwriting_fee', 'title'    => 'Underwriting Fee', 'className'    =>"text-right" ,'visible'=>true],
            [ 'data' => 'syndication_fee', 'name'     => 'syndication_fee', 'title'     => 'Syndication Fee', 'className'     =>"text-right" ,'visible'=>true],
            [ 'data' => 'liquidity', 'name'           => 'liquidity', 'title'           => 'Liquidity', 'className'           =>"text-right" ],
            [ 'data' => 'available_liquidity', 'name' => 'available_liquidity', 'title' => 'Available Liquidity', 'className' =>"text-right" ],
            [ 'data' => 'share', 'name'               => 'share', 'title'               => 'Funded', 'className'              =>"text-right" ],
            [ 'data' => 'share_percentage', 'name'    => 'share_percentage', 'title'    => '%', 'className'                   =>"text-right" ],
            [ 'data' => 'investment', 'name'          => 'investment', 'title'          => 'Investment', 'className'          =>"text-right" ],
            [ 'data' => 'balance', 'name'             => 'balance', 'title'             => 'Balance', 'className'             =>"text-right" ],
        ]);
        $tableBuilder = $tableBuilder->parameters([
            'paging'    => false,
            'searching' => false,
            'bInfo'     => false,
        ]);
        $tableBuilder->parameters([
            'footerCallback' => "function(t,o,a,l,m){
                var n=this.api(),o=table.ajax.json();
                $(n.column(0).footer()).html('Total');
                column=5;
                column++; $(n.column(column).footer()).html(o.liquidity);
                column++; $(n.column(column).footer()).html(o.available_liquidity);
                column++; $(n.column(column).footer()).html(o.share);
                column++;
                column++; $(n.column(column).footer()).html(o.investment);
                column++; $(n.column(column).footer()).html(o.balance);
                function addCommas(nStr) {
                    nStr += '';
                    x = nStr.split('.');
                    x1 = x[0];
                    x2 = x.length > 1 ? '.' + x[1] : '';
                    var rgx = /(\d+)(\d{3})/;
                    while (rgx.test(x1)) {
                        x1 = x1.replace(rgx, '$1' + ',' + '$2');
                    }
                    return x1 + x2;
                }
                $('.companyAmount').text('$'+addCommas((0).toFixed(2)));
                $.each(o.company_wise_total,function(company_id,company_total){
                    $('#Company_'+company_id).text('$'+addCommas((company_total).toFixed(2)));
                });
                $('#Company_Total').text(o.current_investment);
                
            }",
            'fnRowCallback' => "function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                function addCommas(nStr) {
                    nStr += '';
                    x = nStr.split('.');
                    x1 = x[0];
                    x2 = x.length > 1 ? '.' + x[1] : '';
                    var rgx = /(\d+)(\d{3})/;
                    while (rgx.test(x1)) {
                        x1 = x1.replace(rgx, '$1' + ',' + '$2');
                    }
                    return x1 + x2;
                }
                column=5;
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['liquidity']).toFixed(2)));
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['available_liquidity']).toFixed(2)));
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['share']).toFixed(2)));
                column++;
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['investment']).toFixed(2)));
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['balance']).toFixed(2)));
            }",
            'pagingType' => 'input'
        ]);
        return [
            'page_title'         => $page_title,
            'tableBuilder'       => $tableBuilder,
            'all_investors'      => $all_investors,
            'selected_investors' => $selected_investors,
            'selected_companies' => $selected_companies,
            'merchant'           => $merchant,
            'balance'            => $balance,
            'companies'          => $companies,
            'merchant_id'        => $merchant_id
        ];
    }
    public function LiquidityBasedShare($data)
    {
        $selectedData=[];
        $RejectedData=[];
        $selected_investors = $data['investors'];
        $merchant_id        = $data['merchant_id'];
        $InvestedUsers      = MerchantUser::where('merchant_id',$merchant_id)->pluck('user_id','user_id')->toArray();
        if(!empty($InvestedUsers) && !empty($selected_investors)){
            $selected_investors = array_diff($selected_investors,$InvestedUsers);
        }
        if ($selected_investors) {
            $userId = Auth::user()->id;
            $max_investment_per       = (Settings::where('keys', 'max_investment_per')->value('values')) ?? 0;
            $minimum_investment_value = (Settings::where('keys', 'minimum_investment_value')->value('values')) ?? 0;
            $Merchant    = Merchant::find($merchant_id);
            $label       = $Merchant->label;
            $company_amount = CompanyAmount::where('merchant_id', $merchant_id);
            $company_amount = $company_amount->where('max_participant','!=',0);
            $company_amount = $company_amount->pluck('max_participant', 'company_id');
            $company_amount = $company_amount->toArray();
            $selected_companies_list = CompanyAmount::where('merchant_id', $merchant_id)->where('max_participant','!=',0)->pluck('company_id', 'company_id')->toArray();
            $companies = $this->role->allCompanies()->pluck('id')->toArray();
            /**************  APPLY Max 10% of credit   ****************/
            $liquidity_investors = $this->role->allInvestorsLiquidityCredit('', 'liquidity', 0)->whereIn('company',$selected_companies_list)->whereIn('users.id', $selected_investors)->where('active_status', 1);
            $liquidity_investors = $liquidity_investors->orderBy('liquidity',"DESC");
            $liquidity_investors = $liquidity_investors->get();
            $investors = $this->role->allInvestors();
            $investors = $investors->whereIn('id', $selected_investors);
            $remainingCompanyFund  = $company_amount;
            foreach ($remainingCompanyFund as $company_id => $value) {
                $investedCompanyShare = MerchantUserView::where('merchant_id',$merchant_id);
                $investedCompanyShare = $investedCompanyShare->where('company',$company_id);
                $investedCompanyShare = $investedCompanyShare->sum('amount');
                $remainingCompanyFund[$company_id]-=$investedCompanyShare;
            }
            // Initialise Investor
            foreach ($investors as $key => $value) {
                if (! is_null($value->management_fee)) {
                    $mgmnt_fee = $value->management_fee;
                } else {
                    $mgmnt_fee = $Merchant->m_mgmnt_fee;
                }
                $single['user_id']             = $value->id;
                $single['company_id']          = $value->Company->id;
                $single['company']             = $value->Company->name;
                $single['name']                = $value->name;
                $single['liquidity']           = $value->userDetails->liquidity - $value->userDetails->reserved_liquidity_amount;
                $single['reserved_liquidity_amount']= $value->userDetails->reserved_liquidity_amount;
                $single['global_syndication']  = $value->global_syndication;
                $single['s_prepaid_status']    = $value->s_prepaid_status;
                $single['commission']          = $Merchant->commission;
                $single['mgmnt_fee']           = $mgmnt_fee;
                $single['commission']          = 0;
                $single['underwriting_fee']    = 0;
                $single['pre_paid']            = 0;
                $single['syndication_fee']     = 0;
                $single['available_liquidity'] = 0;
                $single['share']               = 0;
                $single['investment']          = 0;
                $single['result']              = 'Pending';
                $single['max_funded_amount']   = 'Pending';
                $single['min_funded_amount']   = 'Pending';
                $single['liquidity_check']     = 'Pending';
                $single['eligible']            = 'No';
                $single['freeze']              = false;
                $selectedData[$value->id] = $single;
            }
            $underwriting_fee   = $Merchant->underwriting_fee??0;
            $up_sell_commission = 0;
            foreach ($liquidity_investors as $key => $value) {
                $available_liquidity=round($value->actual_liquidity,2);
                $selectedData[$value->id]['available_liquidity'] = $available_liquidity;
                if($available_liquidity>$minimum_investment_value){
                    $selectedData[$value->id]['eligible']        = 'Yes';
                    $selectedData[$value->id]['liquidity_check'] = 'Passed';
                }
            }
            $NewselectedData  = Arr::where($selectedData, function ($value, $key) { return $value['liquidity_check'] == 'Passed'; });
            $NewRejectedArray = Arr::where($selectedData, function ($value, $key) { return $value['liquidity_check'] != 'Passed'; });
            $selectedData     = $NewselectedData;
            $RejectedData     = $RejectedData+$NewRejectedArray;
            $maxMerchantFundingAmount = ($Merchant->funded * $max_investment_per) / 100;
            // CompanyWiseShare
            $liquidity=0;
            $companyWiseLiquidity=[];
            foreach ($selectedData as $key => $value) {
                $company_id=$value['company_id'];
                $liquidity += $value['available_liquidity'];
                if (!isset($companyWiseLiquidity[$value['company_id']])) {
                    $companyWiseLiquidity[$value['company_id']] = 0; 
                }
                $companyWiseLiquidity[$company_id] += $value['available_liquidity'];
            }
            foreach ($companyWiseLiquidity as $company_id => $ActualCompanyLiquidity) {
                $CompanyRejectedArray = Arr::where($RejectedData, function ($single) use($company_id) { return ($single['company_id'] === $company_id); });
                $CompanyRejectedInvestorsLiquidity = array_sum(array_column($CompanyRejectedArray,'available_liquidity'));
                $companyWiseLiquidity[$company_id] += $CompanyRejectedInvestorsLiquidity;
            }
            $loop=false;
            // $liquidity = array_sum(array_column($selectedData, 'available_liquidity'));
            // Investor Share Amount
            foreach ($companyWiseLiquidity as $company_id => $ActualCompanyLiquidity) {
                CompanyFundingLoopBegin:
                $companyLiquidity = $ActualCompanyLiquidity;
                $CompanyFund      = $remainingCompanyFund[$company_id];
                $CompanyinvestorFreezedArray = Arr::where($selectedData, function ($value, $key) use($company_id) { return ($value['company_id'] === $company_id && $value['freeze'] == true); });
                $CompanyinvestorFreezedFunded    = array_sum(array_column($CompanyinvestorFreezedArray,'share'));
                $CompanyinvestorFreezedLiquidity = array_sum(array_column($CompanyinvestorFreezedArray,'available_liquidity'));
                $CompanyRejectedArray = Arr::where($RejectedData, function ($value, $key) use($company_id) { return ($value['company_id'] === $company_id); });
                $CompanyRejectedInvestorsLiquidity = array_sum(array_column($CompanyRejectedArray,'available_liquidity'));
                $CompanyFund      -= $CompanyinvestorFreezedFunded;
                $companyLiquidity -= $CompanyinvestorFreezedLiquidity;
                $companyLiquidity -= $CompanyRejectedInvestorsLiquidity;
                $companyLiquidity  = round($companyLiquidity,2);
                $CompanyinvestorArray        = Arr::where($selectedData, function ($value, $key) use($company_id) { return ($value['company_id'] === $company_id && $value['freeze'] == false && $value['min_funded_amount']!='Failed' && $value['eligible']=='Yes'); });
                usort($CompanyinvestorArray, function($a, $b) { return $b['available_liquidity'] <=> $a['available_liquidity']; });
                $loopCount=0;
                foreach ($CompanyinvestorArray as $key => $value) {
                    $liquidity = $value['liquidity'];
                    $share     = $value['available_liquidity']/$companyLiquidity*$CompanyFund;
                    $share     = round($share,2);
                    InvestmentBeginningArea :
                    // Maximum Investment Check
                    if($share >= $maxMerchantFundingAmount){
                        $share = $maxMerchantFundingAmount;
                        $selectedData[$value['user_id']]['max_funded_amount'] = 'Reached';
                        $selectedData[$value['user_id']]['freeze']            = true;
                    }
                    // Minimum Investment Check
                    if($share < $minimum_investment_value){
                        $share = 0;
                        $loop  = true;
                        $selectedData[$value['user_id']]['min_funded_amount'] = 'Failed';
                        $selectedData[$value['user_id']]['result']            = 'Failed';
                    } else {
                        $selectedData[$value['user_id']]['min_funded_amount'] = 'Passed';
                    }
                    if($share){
                        // Maximum Available Liquidity Check
                        if($share > $value['available_liquidity']){
                            $share = $value['available_liquidity'];
                            $selectedData[$value['user_id']]['freeze'] = true;
                        }
                        if(in_array($label,[1,2])){
                            $share=round($share);
                        }
                        $getInvestmentAmount=[
                            'share'                       => $share,
                            'Merchant'                    => $Merchant,
                            'investor_global_syndication' => $value['global_syndication'],
                            'investor_s_prepaid_status'   => $value['s_prepaid_status'],
                            'underwriting_fee'            => $underwriting_fee,
                            'up_sell_commission'          => $up_sell_commission,
                        ];
                        $ReturnData=MerchantUser::getInvestmentAmount($getInvestmentAmount);
                        $pre_paid          = $ReturnData['pre_paid'];
                        $syndication_fee   = $ReturnData['syndication_fee'];
                        $s_prepaid_status  = $ReturnData['s_prepaid_status'];
                        $TotalCommission   = $ReturnData['TotalCommission'];
                        $investment_amount = $ReturnData['investment_amount'];
                        // Liquidity vs Investment Check
                        if($investment_amount>$value['liquidity']){
                            if($loopCount<=0){
                                $liquidity            -= 1;//-1 is to avoid -ve liquidity
                                $new_investment_amount = $liquidity;
                                $share                 = ($new_investment_amount-$pre_paid)/(1+$TotalCommission);
                                $share                 = round($share,2);
                                $selectedData[$value['user_id']]['freeze'] = true;
                                $loopCount++;
                                goto InvestmentBeginningArea;
                            }
                        }
                        $selectedData[$value['user_id']]['share']            = $share;
                        $selectedData[$value['user_id']]['commission']       = $Merchant->commission;
                        $selectedData[$value['user_id']]['underwriting_fee'] = $underwriting_fee;
                        $selectedData[$value['user_id']]['pre_paid']         = $pre_paid;
                        $selectedData[$value['user_id']]['syndication_fee']  = $syndication_fee;
                        $selectedData[$value['user_id']]['s_prepaid_status'] = $s_prepaid_status;
                        $selectedData[$value['user_id']]['investment']       = $investment_amount;
                        $selectedData[$value['user_id']]['result']           = 'Passed';
                        $companyLiquidity -= $value['available_liquidity'];
                        $companyLiquidity  = round($companyLiquidity,2);
                        $CompanyFund      -= $share;
                        $loopCount=0;
                    }
                }
                $CompanyinvestorNonFreezedArray = Arr::where($selectedData, function ($value, $key) use($company_id) { return ($value['company_id'] === $company_id && $value['freeze'] == false && $value['eligible'] == "Yes"); });
                $CompanyinvestorFinalArray      = Arr::where($selectedData, function ($value, $key) use($company_id) { return $value['company_id'] === $company_id; });
                $CompanyInvestedFund  = array_sum(array_column($CompanyinvestorFinalArray,'share'));
                $CompanyShareBalance  = round($remainingCompanyFund[$company_id]-$CompanyInvestedFund,2);
                if($CompanyShareBalance && count($CompanyinvestorNonFreezedArray)){
                    $NonEligibleInvestorList=Arr::where($selectedData, function ($value, $key) use($company_id) {
                        return (
                            $value['company_id'] === $company_id &&
                            $value['freeze']     == false &&
                            $value['eligible']   == "Yes" &&
                            $value['share']      == 0
                        ); 
                    });
                    if($NonEligibleInvestorList) {
                        usort($NonEligibleInvestorList, function($a, $b) { return $b['available_liquidity'] <=> $a['available_liquidity']; });
                        $removing_user=array_pop($NonEligibleInvestorList);
                        unset($selectedData[$removing_user['user_id']]);
                        $RejectedData[$removing_user['user_id']]=$removing_user;
                        foreach ($selectedData as $key => $value) {
                            $selectedData[$key]['min_funded_amount']="Pending";
                        }
                        goto CompanyFundingLoopBegin;
                    }
                }
            }
            $NewselectedData  = Arr::where($selectedData, function ($value, $key) { return $value['share'] != 0; });
            $NewRejectedArray = Arr::where($selectedData, function ($value, $key) { return $value['share'] == 0; });
            $selectedData     = $NewselectedData;
            $RejectedData     = $RejectedData+$NewRejectedArray;
            foreach ($remainingCompanyFund as $company_id => $companyBalanceFund) {
                $CompanyinvestorArray=Arr::where($selectedData, function ($value, $key) use($company_id) { return $value['company_id'] === $company_id; });
                usort($CompanyinvestorArray, function($a, $b) { return $b['share'] <=> $a['share']; });
                $CompanyFundedAmount=array_sum(array_column($CompanyinvestorArray,'share'));
                $diff=round($CompanyFundedAmount-$companyBalanceFund,2);
                if($diff>0){
                    foreach ($CompanyinvestorArray as $key => $value) {
                        if($diff==0) break;
                        if(!$value['freeze']){
                            $share = $value['share']-$diff;
                            $diff  = 0;
                            $getInvestmentAmount=[
                                'share'                       => $share,
                                'Merchant'                    => $Merchant,
                                'investor_global_syndication' => $value['global_syndication'],
                                'investor_s_prepaid_status'   => $value['s_prepaid_status'],
                                'underwriting_fee'            => $underwriting_fee,
                                'up_sell_commission'          => $up_sell_commission,
                            ];
                            $ReturnData=MerchantUser::getInvestmentAmount($getInvestmentAmount);
                            $investment_amount = $ReturnData['investment_amount'];
                            $selectedData[$value['user_id']]['share']      = $share;
                            $selectedData[$value['user_id']]['investment'] = $investment_amount;
                        }
                    }
                }
                if($diff<1){
                    if($CompanyFundedAmount)
                    foreach ($CompanyinvestorArray as $key => $value) {
                        if($diff==0) break;
                        if(!$value['freeze']){
                            $share = $value['share']-$diff;
                            $diff  = 0;
                            $getInvestmentAmount=[
                                'share'                       => $share,
                                'Merchant'                    => $Merchant,
                                'investor_global_syndication' => $value['global_syndication'],
                                'investor_s_prepaid_status'   => $value['s_prepaid_status'],
                                'underwriting_fee'            => $underwriting_fee,
                                'up_sell_commission'          => $up_sell_commission,
                            ];
                            $ReturnData=MerchantUser::getInvestmentAmount($getInvestmentAmount);
                            $investment_amount = $ReturnData['investment_amount'];
                            $selectedData[$value['user_id']]['share']      = $share;
                            $selectedData[$value['user_id']]['investment'] = $investment_amount;
                        }
                    }
                } 
            }
        }
        return [
            'selectedData' => $selectedData,
            'RejectedData' => $RejectedData,
        ];
    }
    public function AssignLiquidityBasedShare($data)
    {
       
        try {
            $userId=Auth::user()->id;
            $ReturnData=MerchantHelper::LiquidityBasedShare($data);
            $InvestorShare=$ReturnData['selectedData'];
            if(count($InvestorShare)==0){
                throw new \Exception("No Investors is Eligible For Investment", 1);
            }
            $merchant_id=$data['merchant_id'];
            $single['merchant_id']            = $merchant_id;
            $single['status']                 = 1;
            $single['creator_id']             = $userId;
            $single['up_sell_commission_per'] = 0;
            foreach ($InvestorShare as $key => $value) {
                $single['user_id']                    = $value['user_id'];
                $single['amount']                     = $value['share'];
                $single['mgmnt_fee']                  = $value['mgmnt_fee'];
                $single['pre_paid']                   = $value['pre_paid'];
                $single['s_prepaid_status']           = $value['s_prepaid_status'];
                $single['syndication_fee_percentage'] = $value['syndication_fee'];
                $single['commission_per']             = $value['commission'];
                $single['under_writing_fee_per']      = $value['underwriting_fee'];
                MerchantUser::create($single);
            }
            $MerchantUserCount=MerchantUser::where('merchant_id',$merchant_id);
            $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
            $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
            if($OverpaymentAccount){
                $MerchantUserCount = $MerchantUserCount->where('user_id','!=',$OverpaymentAccount->id);
            }
            $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
            $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
            if ($AgentFeeAccount) {
                $MerchantUserCount = $MerchantUserCount->where('user_id','!=',$AgentFeeAccount->id);
            }
            $MerchantUserCount=$MerchantUserCount->count();
            if($MerchantUserCount){
                $return_function=MerchantUser::AddOverpaymentAccount($merchant_id);
                if($return_function['result'] != 'success') throw new \Exception($return_function['result']);
                $return_function=MerchantUser::AddAgentFeeAccount($merchant_id);
                if($return_function['result'] != 'success') throw new \Exception($return_function['result']);
            }
            $merchantUsers = MerchantUser::where('merchant_id', $merchant_id)->pluck('user_id')->toArray();
            InvestorHelper::update_liquidity($merchantUsers, 'based_on_liquidity', $merchant_id);
            $Merchant = Merchant::find($merchant_id);
            if ($Merchant->paymentTerms->count() == 0) {
                if ($Merchant->ach_pull) {
                    $terms = $this->merchant->createTerms($Merchant);
                }
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    
    public function PaymentBasedInvestment(int $merchant_id = 0)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $page_title="Assign Investors Based On Payment";
        $merchant=Merchant::find($merchant_id);
        $existing_investors  = MerchantUser::where('merchant_id', $merchant_id)->pluck('user_id','user_id')->toArray();
        $all_auto_investors = User::whereNotNull('label');
        $all_auto_investor  = $all_auto_investors->where('active_status', 1);
        $all_auto_investors = $all_auto_investors->whereRaw('JSON_CONTAINS(label,"'.$merchant->label.'")');
        $all_auto_investors = $all_auto_investors->whereNotIn('id',$existing_investors);
        $auto_investors     = clone $all_auto_investors;
        $all_auto_investors = $all_auto_investors->get();
        $auto_investors     = $auto_investors->pluck('users.id');
        $auto_investors     = $auto_investors->toArray();
        $companies          = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0]       = 'All';
        $companies          = array_reverse($companies, true);
        $tableBuilder = $this->tableBuilder->ajax( [
            'url'  => route('admin::merchants::Investment::PaymentBased::Share'),
            'type' => 'POST',
            'data' => 'function(d){
                d._token      = "'.csrf_token().'";
                d.investors   = $("#investors").val();
                d.merchant_id = $("#merchant_id").val();
                d.from_date   = $("#date_start").val();
                d.end_date    = $("#date_end").val();
            }'
        ]);
        $tableBuilder = $tableBuilder->columns([
            [ 'data' => 'DT_RowIndex', 'name'      => 'DT_RowIndex', 'title'      => '#', 'searchable'               => false, 'orderable' => false],
            [ 'data' => 'company', 'name'          => 'company', 'title'          => 'Company' ,'width'=>"15%" ],
            [ 'data' => 'Investor', 'name'         => 'Investor', 'title'         => 'Investor','width'=>"25%" ],
            [ 'data' => 'commission', 'name'       => 'commission', 'title'       => 'Commission', 'className'       =>"text-right" ],
            [ 'data' => 'underwriting_fee', 'name' => 'underwriting_fee', 'title' => 'Underwriting Fee', 'className' =>"text-right" ],
            [ 'data' => 'syndication_fee', 'name'  => 'syndication_fee', 'title'  => 'Syndication Fee', 'className'  =>"text-right" ],
            [ 'data' => 'payment', 'name'          => 'payment', 'title'          => 'Payment', 'className'          =>"text-right" ],
            [ 'data' => 'share', 'name'            => 'share', 'title'            => 'Funded', 'className'           =>"text-right" ],
            [ 'data' => 'share_percentage', 'name' => 'share_percentage', 'title' => '%', 'className'                =>"text-right" ],
            [ 'data' => 'investment', 'name'       => 'investment', 'title'       => 'Investment', 'className'       =>"text-right" ],
            [ 'data' => 'balance', 'name'          => 'balance', 'title'          => 'Difference', 'className'          =>"text-right" ],
        ]);
        $tableBuilder = $tableBuilder->parameters([
            'paging'    => false,
            'searching' => false,
            'bInfo'     => false,
        ]);
        $tableBuilder=$tableBuilder->parameters([
            'footerCallback' => 'function(t,o,a,l,m){
                var n=this.api(),o=table.ajax.json();
                $("#Merchant_funded").html(o.funded);
                $("#Merchant_rtr").html(o.rtr);
                $("#payment_amount").html(o.payment);
                $("#net_investment").html(o.net_investment);
                $("#investment").html(o.investment);
                $(n.column(0).footer()).html("Total");
                column=5;
                column++; $(n.column(column).footer()).html(o.payment);
                column++; $(n.column(column).footer()).html(o.funded);
                column++; 
                column++; $(n.column(column).footer()).html(o.investment);
            }',
            'fnRowCallback' => "function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                function addCommas(nStr) {
                    nStr += '';
                    x = nStr.split('.');
                    x1 = x[0];
                    x2 = x.length > 1 ? '.' + x[1] : '';
                    var rgx = /(\d+)(\d{3})/;
                    while (rgx.test(x1)) {
                        x1 = x1.replace(rgx, '$1' + ',' + '$2');
                    }
                    return x1 + x2;
                }
                column=5;
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['payment']).toFixed(2)));
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['share']).toFixed(2)));
                column++; 
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['investment']).toFixed(2)));
                column++; $('td:eq('+column+')', nRow).text('$'+addCommas((aData['balance']).toFixed(2)));
            }",
            'pagingType' => 'input'
        ]);
        return [
            'page_title'         => $page_title,
            'tableBuilder'       => $tableBuilder,
            'all_auto_investors' => $all_auto_investors,
            'auto_investors'     => $auto_investors,
            'merchant'           => $merchant,
            'companies'          => $companies,
            'merchant_id'        => $merchant_id
        ];
    }
    public function PaymentBasedShare($data)
    {
        $selectedData=[];
        $RejectedData=[];
        $selected_investors = $data['investors'];
        $total_netamount    = 0;
        $net_investment     = 0;
        if ($selected_investors) {
            $userId = Auth::user()->id;
            $from_date  = date('Y-m-d',strtotime($data['from_date']));
            $end_date   = date('Y-m-d',strtotime($data['end_date']));
            $merchant_id   = $data['merchant_id'];
            $Merchant      = Merchant::find($merchant_id);
            $label         = $Merchant->label;
            $InvestedUsers = MerchantUser::where('merchant_id',$merchant_id)->pluck('user_id','user_id')->toArray();
            $payments = PaymentInvestors::where('merchants.label', $label);
            $payments = $payments->where('participent_payments.payment_date', '>=', $from_date);
            $payments = $payments->where('participent_payments.payment_date', '<=', $end_date);
            $payments = $payments->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id');
            $payments = $payments->join('merchants', 'participent_payments.merchant_id', 'merchants.id');
            $payments = $payments->join('users', 'payment_investors.user_id', 'users.id');
            $payments = $payments->whereIn('payment_investors.user_id',$selected_investors);
            // $payments = $payments->whereNotIn('payment_investors.user_id',$InvestedUsers);
            $total_payments = clone $payments;
            $total_payments = $total_payments->groupBy('payment_investors.user_id');
            $total_payments = $total_payments->select(DB::raw('sum(participant_share-mgmnt_fee) as net_amount'), 'payment_investors.user_id');
            $total_payments = $total_payments->pluck('net_amount', 'payment_investors.user_id');
            $total_payments = $total_payments->toArray();
            $total_netamount = array_sum($total_payments);
            $fee = $Merchant->commission + $Merchant->m_syndication_fee + $Merchant->underwriting_fee;
            $net_investment = $total_netamount * 100 / (100 + $fee);
            $net_investment = floor($net_investment * 100) / 100;
            if ($net_investment > 0) {
                $SelectedUsers = new User;
                $SelectedUsers = $SelectedUsers->whereIn('id',$selected_investors);
                $SelectedUsers = $SelectedUsers->get();
                foreach ($SelectedUsers as $key => $value) {
                    $user_id = $value->id;
                    $payment = $total_payments[$user_id]??0;
                    if (! $payment) { continue; }
                    $share = $payment * 100 / (100 + $fee);
                    ShareArea:
                    $share = round($share,2);
                    $getInvestmentAmount=[
                        'share'            => $share,
                        'Merchant'         => $Merchant,
                        'underwriting_fee' => $Merchant->underwriting_fee,
                    ];
                    $ReturnData        = MerchantUser::getInvestmentAmount($getInvestmentAmount);
                    $pre_paid          = $ReturnData['pre_paid'];
                    $syndication_fee   = $ReturnData['syndication_fee'];
                    $s_prepaid_status  = $ReturnData['s_prepaid_status'];
                    $TotalCommission   = $ReturnData['TotalCommission'];
                    $investment_amount = $ReturnData['investment_amount'];
                    $single['user_id']            = $value->id;
                    $single['company_id']         = $value->Company->id;
                    $single['company']            = $value->Company->name;
                    $single['name']               = $value->name;
                    $single['commission']         = $Merchant->commission;
                    $single['underwriting_fee']   = $Merchant->underwriting_fee;
                    $single['syndication_fee']    = $Merchant->m_syndication_fee;
                    $single['mgmnt_fee']          = $Merchant->m_mgmnt_fee;
                    $single['payment']            = round($payment,2);
                    $single['share']              = $share;
                    $single['up_sell_commission'] = 0;
                    $single['investment']         = round($investment_amount,2);
                    $single['global_syndication'] = $value->global_syndication;
                    $single['s_prepaid_status']   = $s_prepaid_status;
                    $single['pre_paid']           = $pre_paid;
                    $single['m_syndication_fee']  = $Merchant->m_syndication_fee;
                    if($single['investment']>$single['payment']){
                        $diff=round($single['investment']-$single['payment'],2);
                        if($diff){
                            $share-=$diff;
                            goto ShareArea;
                        }
                    }
                    $selectedData[$value->id]     = $single;
                }
                $share = array_sum(array_column($selectedData, 'share'));
                $diff  = round($net_investment-$share,2);
                if($diff){
                    $RemainingShare=$selectedData;
                    usort($RemainingShare, function($a, $b) { return $b['payment'] <=> $a['payment']; });
                    foreach ($RemainingShare as $key => $value) {
                        if($diff){
                            if($diff==0) break;
                            $share = $value['share']+$diff;
                            $diff  = 0;
                            $getInvestmentAmount=[
                                'share'            => $share,
                                'Merchant'         => $Merchant,
                                'underwriting_fee' => $Merchant->underwriting_fee,
                            ];
                            $ReturnData=MerchantUser::getInvestmentAmount($getInvestmentAmount);
                            $investment_amount = $ReturnData['investment_amount'];
                            $selectedData[$value['user_id']]['share']      = $share;
                            $selectedData[$value['user_id']]['investment'] = $investment_amount;
                        }
                    }
                }
            }
        }
        return [    
            'net_investment' => $net_investment,
            'payment'        => $total_netamount,
            'selectedData'   => $selectedData,
            'RejectedData'   => $RejectedData,
        ];
    }
    
    public function AssignPaymentBasedShare($data)
    {
        try {
            $userId=Auth::user()->id;
            $ReturnData=MerchantHelper::PaymentBasedShare($data);
            $payment       = $ReturnData['payment'];
            $InvestorShare = $ReturnData['selectedData'];
            if(count($InvestorShare)==0){
                throw new \Exception("No Investors is Eligible For Investments", 1);
            }
            $merchant_id = $data['merchant_id'];
            $Merchant    = Merchant::find($merchant_id);
            $single['merchant_id']            = $merchant_id;
            $single['status']                 = 1;
            $single['creator_id']             = $userId;
            $single['up_sell_commission_per'] = 0;
            foreach ($InvestorShare as $key => $value) {
                $single['user_id']                    = $value['user_id'];
                $single['amount']                     = $value['share'];
                $single['mgmnt_fee']                  = $value['mgmnt_fee'];
                $single['pre_paid']                   = $value['pre_paid'];
                $single['s_prepaid_status']           = $value['s_prepaid_status'];
                $single['syndication_fee_percentage'] = $value['syndication_fee'];
                $single['commission_per']             = $value['commission'];
                $single['under_writing_fee_per']      = $value['underwriting_fee'];
                MerchantUser::create($single);
            }
            $MerchantUserCount=MerchantUser::where('merchant_id',$merchant_id);
            $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
            $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
            if($OverpaymentAccount){
                $MerchantUserCount = $MerchantUserCount->where('user_id','!=',$OverpaymentAccount->id);
            }
            $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
            $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
            if ($AgentFeeAccount) {
                $MerchantUserCount = $MerchantUserCount->where('user_id','!=',$AgentFeeAccount->id);
            }
            $MerchantUserCount=$MerchantUserCount->count();
            if($MerchantUserCount){
                $return_function=MerchantUser::AddOverpaymentAccount($merchant_id);
                if($return_function['result'] != 'success') throw new \Exception($return_function['result']);
                $return_function=MerchantUser::AddAgentFeeAccount($merchant_id);
                if($return_function['result'] != 'success') throw new \Exception($return_function['result']);
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
            $rtr = $Merchant->factor_rate * $funded;
            $payment_amount = PayCalc::getPayment($rtr, $Merchant->pmnts);
            Merchant::where('id', $merchant_id)->update(['funded' => $funded, 'rtr' => $rtr, 'max_participant_fund' => $max_participant_fund, 'payment_amount' => $payment_amount]);
            $MerchantUsers=MerchantUser::where('merchant_id',$merchant_id)->pluck('user_id','user_id')->toArray();
            InvestorHelper::update_liquidity($MerchantUsers, 'based_on_payment', $merchant_id);
            if ($Merchant->paymentTerms->count() == 0) {
                if ($Merchant->ach_pull) {
                    $terms = $this->merchant->createTerms($Merchant);
                }
            }
            $return['result'] = 'success';
            $return['message'] = 'Payment of '.FFM::dollar($payment).' has been collected from '.FFM::date($data['from_date']).' till '.FFM::date($data['end_date']).' in the Insurance Category, which has been reinvested to '.$Merchant->name;
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }


    public function storeMultipleTransactions($request)
    {
        try{
            DB::beginTransaction();
            $count = count($request->investor_id);
            $investor_ids = $request->investor_id;
            $amount1 = $request->transaction_amount;
            $notes1 = $request->notes;
            if($request->transaction_type == 1){
                $trans_method = 2;
            } else {
                $trans_method = 1;
            }

            foreach($investor_ids as $i => $value){
                $data['amount'] = $amount1[$i];
                $data['investor_id'] = $investor_ids[$i];
                $data['transaction_type'] = $request->transaction_type;
                $data['date'] = $request->date;
                $data['category_notes'] = $request->category_notes;
                $data['transaction_category'] = $request->transaction_category;
                $data['creator_id'] = Auth::user()->id;
                $data['category_notes'] = $notes1[$i];
                $data['transaction_method'] = $trans_method;
                if( $request->merchant_id && $request->merchant_id !=""){
                    $data['merchant_id'] = $request->merchant_id;
                }
                if ($amount1[$i] && $amount1[$i] > 0) {
                    $return_function = InvestorHelper::insertTransactionFunction($data);
                    if ($return_function['result'] != 'success') {
                        throw new Exception($return_function['result'], 1);
                    }
                }
            }
            DB::commit();
            return true;
        } catch(Exception $e){
            DB::rollback();
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }

    public static function getMarketplaceMerchants($request){
        $funds = Merchant::select('funded', 'payment_amount', 'pmnts', 'factor_rate', 'commission', 'm_mgmnt_fee', 'm_syndication_fee', 'm_s_prepaid_status', 'rtr', 'max_participant_fund', 'merchants.name', 'merchants.id', 'underwriting_fee', 'complete_percentage', 'marketplace_permission', 'business_en_name')->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->groupBy('merchants.id')->where('marketplace_status', 1)->where('active_status', 1)->where('sub_status_id', '!=', 17)->with('FundingRequests');
        if ($request->filter == 2) {
            $funds = $funds->whereRaw('max_participant_fund=funded');
        } elseif ($request->filter == 1) {
            $funds = $funds->whereRaw('max_participant_fund<funded');
        }
        $funds = $funds->get();
        return $funds;

    }    

   public function changeSubStatusFn($req_merchant_id, $req_sub_status_id)
    {
        try {
            DB::beginTransaction();
            $data_r = $logArray = [];
            $rtr_status = 0;
            $reverse_staus = 0;
            $author = Auth::user()->name;
            $arrayList = [];
            $req_sub_status_id = (int) $req_sub_status_id;
            if ($req_merchant_id) {
                $merchant = Merchant::find($req_merchant_id)->toArray();
                $substatus = SubStatus::select('name')->where('id', $req_sub_status_id)->first()->toArray();
                if ($merchant['sub_status_id'] != $req_sub_status_id) {
                    $logArray = ['merchant_id' => $req_merchant_id, 'old_status' => $merchant['sub_status_id'], 'current_status' => $req_sub_status_id, 'description' => 'Merchant Status changed to '.$substatus['name'].' by '.$author, 'creator_id' => Auth::user()->id];
                    $log = MerchantStatusLog::create($logArray);
                }
                $data_r['sub_status_id'] = $req_sub_status_id;
                if ($merchant['sub_status_id'] != $data_r['sub_status_id']) {
                    $data_r['last_status_updated_date'] = $log->created_at;
                }

                if (in_array($merchant['sub_status_id'], [4, 22]) && in_array($req_sub_status_id, [4, 22])) {
                    $delete_flag = true;
                    if (! in_array($req_merchant_id, $arrayList)) {
                        $this->merchant->modify_rtr($req_merchant_id, $req_sub_status_id, $delete_flag);
                    }
                    $delete_flag = false;
                    if (! in_array($req_merchant_id, $arrayList)) {
                        $this->merchant->modify_rtr($req_merchant_id, $req_sub_status_id, $delete_flag);
                    }
                }
                if (in_array($merchant['sub_status_id'], [4, 22]) && ! in_array($req_sub_status_id, [4, 22])) {
                    $reverse_staus = 1;
                    $delete_flag = true;
                    if (! in_array($req_merchant_id, $arrayList)) {
                        $this->merchant->modify_rtr($req_merchant_id, $req_sub_status_id, $delete_flag);
                    }
                }
                if (! in_array($merchant['sub_status_id'], [4, 22]) && in_array($req_sub_status_id, [4, 22])) {
                    $rtr_status = 1;
                    $delete_flag = false;
                    if (! in_array($req_merchant_id, $arrayList)) {
                        $this->merchant->modify_rtr($req_merchant_id, $req_sub_status_id, $delete_flag);
                    }
                }
                if (in_array($merchant['sub_status_id'], [18, 19, 20]) && in_array($req_sub_status_id, [18, 19, 20])) {
                    $delete_flag = true;
                    if (! in_array($req_merchant_id, $arrayList)) {
                        $this->merchant->modify_rtr($req_merchant_id, $req_sub_status_id, $delete_flag);
                    }
                    $delete_flag = false;
                    if (! in_array($req_merchant_id, $arrayList)) {
                        $this->merchant->modify_rtr($req_merchant_id, $req_sub_status_id, $delete_flag);
                    }
                }
                if (in_array($merchant['sub_status_id'], [18, 19, 20]) && ! in_array($req_sub_status_id, [18, 19, 20])) {
                    $data_r['old_factor_rate'] = 0;
                    if ($merchant['old_factor_rate']) {
                        $data_r['factor_rate'] = $merchant['old_factor_rate'];
                    }
                    $reverse_staus = 1;
                    $delete_flag = true;
                    if (! in_array($req_merchant_id, $arrayList)) {
                        $this->merchant->modify_rtr($req_merchant_id, $req_sub_status_id, $delete_flag);
                    }
                }
                if (! in_array($merchant['sub_status_id'], [18, 19, 20]) && in_array($req_sub_status_id, [18, 19, 20])) {
                    $rtr_status = 1;
                    $delete_flag = false;
                    if (! in_array($req_merchant_id, $arrayList)) {
                        $this->merchant->modify_rtr($req_merchant_id, $req_sub_status_id, $delete_flag);
                    }
                }
                $Merchant = Merchant::find($req_merchant_id);
                $sys_substaus = (Settings::where('keys', 'agent_fee_on_substtaus')->value('values'));
                $sys_substaus = json_decode($sys_substaus, true);
                if(!is_array($sys_substaus)){
                    $sys_substaus=[];
                }
                if (! in_array($req_sub_status_id, $sys_substaus)) {
                    $data_r['agent_fee_applied'] = 0;
                }
                $Merchant = $Merchant->update($data_r);
                $Merchant = Merchant::find($req_merchant_id);
                $substatus_name = SubStatus::where('id', $req_sub_status_id)->value('name');
                $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
                $form_params = ['method' => 'merchant_update', 'username' => config('app.crm_user_name'), 'password' => config('app.crm_password'), 'investor_merchant_id' => $req_merchant_id, 'status' => $substatus_name];
                try {
                    $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                    dispatch($crmJob);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                if (in_array($req_merchant_id, $arrayList)) {
                    return response()->json(['status' => 1, 'msg' => 'Merchant status updated Successfully.']);
                }
                if ($reverse_staus == 1 || $rtr_status == 1) {
                    if ($reverse_staus == 1) {
                        $investment_data = MerchantUser::where('merchant_id', $req_merchant_id)->where('merchant_user.status', 1)->get();
                        foreach ($investment_data as $key => $investments) {
                            $invest_rtr = $Merchant->factor_rate * $investments->amount;
                            $updt_investor_rtr = MerchantUser::where('user_id', $investments->user_id)->where('merchant_id', $investments->merchant_id)->update(['invest_rtr' => $invest_rtr]);
                        }
                    }
                    if ($rtr_status == 1) {
                        if (in_array($Merchant->sub_status_id, [18, 19, 20])) {
                        }
                    }
                    $investor_array = MerchantUser::where('merchant_id', $req_merchant_id)->where('status', 1)->pluck('user_id', 'user_id')->toArray();
                    $complete_per = PayCalc::completePercentage($req_merchant_id, $investor_array);
                    Merchant::where('id', $Merchant->id)->update(['complete_percentage' => $complete_per]);
                }
            }
            DB::commit();
            $return['status'] = 1;
            $return['msg'] = 'Merchant status updated Successfully';
        } catch (\Exception $e) {
            DB::rollback();
            $return['status'] = 2;
            $return['msg'] = $e->getMessage();
        }

        return response()->json($return);
    }

    public function updateAgentFee($request)
    {
        try {
            DB::beginTransaction();
            Merchant::find($request->merchant_id)->update(['agent_fee_applied' => $request->agent_fee_status]);
            if ($request->agent_fee_status == 1) {
                $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
                $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
                $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
                if ($AgentFeeAccount) {
                    $MerchantUser = MerchantUser::where('merchant_id', $request->merchant_id)->where('user_id', $AgentFeeAccount->id)->first();
                    if (! $MerchantUser) {
                        $item = [
                            'user_id'                   =>$AgentFeeAccount->id,
                            'amount'                    =>0,
                            'merchant_id'               =>$request->merchant_id,
                            'status'                    =>1,
                            'invest_rtr'                =>0,
                            'mgmnt_fee'                 =>0,
                            'syndication_fee_percentage'=>0,
                            'commission_amount'         =>0,
                            'commission_per'            =>0,
                            'under_writing_fee'         =>0,
                            'under_writing_fee_per'     =>0,
                            'pre_paid'                  =>0,
                            's_prepaid_status'          =>1,
                            'creator_id'                => Auth::user()->id,
                        ];
                        MerchantUser::create($item);
                    }
                }
                DB::table('merchant_agent_account_history')->insert([
                    'merchant_id' => $request->merchant_id,
                    'start_date' => date('Y-m-d H:i:s'),
                ]);
                
            }else{
                DB::table('merchant_agent_account_history')
                ->where('merchant_id', $request->merchant_id)
                ->orderBy('id','DESC')  
                ->limit(1)  
                ->update(array('end_date' => date('Y-m-d H:i:s'))); 
            }
            DB::commit();
            $return['msg'] = 'Agent Fee Status Updated Successfully';
            $return['status'] = 1;
        } catch (\Exception $e) {
            DB::rollback();
            $return['msg'] = $e->getMessage();
            $return['status'] = 0;
        }

        return response()->json($return);


    }

    public function getCommissionData($request)
    {
        $owner = isset($request->owner) ? $request->owner : null;
        $date_type = isset($request->date_type) ? $request->date_type : null;
        if($date_type!='true'){
            $request->time_start = $request->time_end = NULL;
        }
        $sDate = ($date_type=='true') ? ET_To_UTC_Time($request->date_start.$request->time_start) : $request->date_start;       
        $eDate = ($date_type=='true') ? ET_To_UTC_Time($request->date_end.$request->time_end) : $request->date_end;
        $data = $this->merchant->getCommissionData($request->row_merchant, $request->investors ,$owner,$date_type,$sDate, $eDate, ET_To_UTC_Time($request->date_start.$request->time_start, 'time'), ET_To_UTC_Time($request->date_end.$request->time_end, 'time'));
        $investmentData = '<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger"><td>Investor</td><td>Invested Amount</td><td>Up sell Commission</td></tr>';
        foreach ($data as $dt) {
            if ($dt->amount) {
                $total_invested = $dt->pre_paid + $dt->amount + $dt->commission_amount + $dt->m_up_sell_commission+$dt->under_writing_fee;
                $investmentData .= '<tr><td><a href="'.route('admin::investors::portfolio', ['id' => $dt->user_id]).'">'.$dt->username.'</a></td><td>'.FFM::dollar($total_invested).'</td><td>'.FFM::dollar($dt->m_up_sell_commission).'</td></tr>';
            }
        }
        $investmentData .= '</table>';
        return $investmentData;
    }

    public function viewMerchantUserRoles()
    {
        $page_title = 'Merchant Users';
        $tableBuilder=$this->tableBuilder->ajax(['url' => route('admin::merchants::merchantuserroledata'), 'data' => 'function(d){d.roles = $("#roles").val();}']);
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'email', 'name' => 'email', 'title' => 'Email'], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'], ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);

        return ['page_title'=>$page_title,'tableBuilder'=>$tableBuilder];

    }



}
