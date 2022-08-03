<?php

namespace App\Library\Helpers;

use App\AchRequest;
use App\Document;
use App\DocumentType;
use App\Interest;
use App\InvestorDocuments;
use App\InvestorTransaction;
use App\Library\Repository\Interfaces\ILiquidityLogRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Library\Transformer\MerchantTransformer;
use App\Library\Transformer\ParticipantPaymentTransformer;
use App\LiquidityLog;
use App\Merchant;
use App\MerchantPaymentTerm;
use App\MerchantStatement;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\Models\Views\InvestorAchTransactionView;
use App\Models\Views\LiquidityLogView;
use App\Models\Views\MerchantLiquidityLogView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Settings;
use App\Statements;
use App\SubStatus;
use App\TermPaymentDate;
use App\User;
use App\UserDetails;
use App\VelocityFee;
use Carbon\Carbon;
use DataTables;
use FFM;
use Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PayCalc;
use Permissions;
use Spatie\Permission\Models\Role;

class MerchantTableBuilder
{
    protected $merchant;

    public function __construct(IMerchantRepository $merchant, IRoleRepository $role, IParticipantPaymentRepository $partPay, IUserRepository $user, ILiquidityLogRepository $log)
    {
        $this->merchant = $merchant;
        $this->partPay = $partPay;
        $this->user = Auth::user();
        $this->role = $role;
        $this->user1 = $user;
        $this->log = $log;
    }

    public function generalReport($sDate = null, $eDate = null, $id = null)
    {
        $data = $this->merchant->searchForGeneralReport($sDate, $eDate, $id);
        $overpayments = DB::table('merchant_user')->groupBy('merchant_user.merchant_id')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->select(DB::raw('sum(
                IF((paid_participant_ishare+total_agent_fee) > invest_rtr, ((paid_participant_ishare+total_agent_fee)-invest_rtr )*(1- (merchant_user.mgmnt_fee)/100 ), 0)
                ) as overpayment'), 'merchants.id')->pluck('overpayment', 'merchants.id')->toArray();
        $totDebited = $totLlc = $totSynd = $t_mgmnt_fee = $t_pricipal = $t_profit = $t_participant_rtr = $t_participant_rtr_balance = 0;
        foreach ($data->get() as $tmp) {
            $totDebited += $tmp->participantPayment->sum('payment');
            $totLlc += $tmp->participantPayment->sum('actual_participant_share');
            $t_mgmnt_fee += $tmp->participantPayment->sum('mgmnt_fee');
            $t_pricipal += $tmp->participantPayment->sum('principal');
            $t_profit += $tmp->participantPayment->sum('profit');
            $t_participant_rtr += $tmp->investmentData->sum('invest_rtr');
            $t_participant_rtr_balance = $t_participant_rtr - $totLlc;
        }
        $totSynd = $totLlc - $t_mgmnt_fee;

        return DataTables::of($data)->editColumn('participant_payment', function ($partpayment) {
            return (new ParticipantPaymentTransformer('general_report'))->transform($partpayment->participantPayment);
        })->addColumn('TOTAL_DEBITED', function ($partpayment) {
            $total_payment = 0;
            foreach ($partpayment->participantPayment as $key => $value) {
                $total_payment = $total_payment + $value['payment'];
            }

            return FFM::dollar($total_payment);
        })->addColumn('TOTAL_COMPANY', function ($partpayment) {
            $TOTAL_COMPANY = 0;
            foreach ($partpayment->participantPayment as $key => $value) {
                $TOTAL_COMPANY = $TOTAL_COMPANY + ($value['actual_participant_share']);
            }

            return FFM::dollar($TOTAL_COMPANY);
        })->addColumn('TOTAL_SYNDICATE', function ($partpayment) {
            $TOTAL_SYNDICATE = 0;
            foreach ($partpayment->participantPayment as $key => $value) {
                $TOTAL_SYNDICATE = $TOTAL_SYNDICATE + ($value['actual_participant_share']) - $value['mgmnt_fee'];
            }

            return FFM::dollar($TOTAL_SYNDICATE);
        })->addColumn('principal', function ($partpayment) {
            $pricipal = 0;
            foreach ($partpayment->participantPayment as $key => $value) {
                $pricipal = $pricipal + ($value['principal']);
            }

            return FFM::dollar($pricipal);
        })->addColumn('profit', function ($partpayment) {
            $profit = 0;
            foreach ($partpayment->participantPayment as $key => $value) {
                $profit = $profit + ($value['profit']);
            }

            return FFM::dollar($profit);
        })->addColumn('last_payment_date', function ($partpayment) {
            return date('m/d/Y', strtotime($partpayment->last_payment_date));
        })->addColumn('last_payment_amount', function ($partpayment) {
            return FFM::dollar($partpayment->last_payment_amount);
        })->addColumn('participant_rtr', function ($partpayment) {
            $rtr = 0;
            foreach ($partpayment->investmentData as $key => $value) {
                $rtr = $rtr + ($value['invest_rtr']);
            }

            return FFM::dollar($rtr);
        })->addColumn('participant_rtr_balance', function ($partpayment) use ($overpayments) {
            $rtr = $overpayment = 0;
            foreach ($partpayment->investmentData as $key => $value) {
                $rtr = $rtr + ($value['invest_rtr']);
                $overpayment = $overpayment + ($value['overpayment']);
            }
            $participant = 0;
            $balance = 0;
            foreach ($partpayment->participantPayment as $key => $value) {
                $participant = $participant + ($value['actual_participant_share']);
            }
            if ($overpayment != 0) {
                $balance = 0;
            } else {
                $balance = $rtr - $participant;
            }

            return FFM::dollar($balance);
        })->addColumn('TOTAL_MGMNT_FEE', function ($partpayment) {
            $TOTAL_MGMNT_FEE = 0;
            foreach ($partpayment->participantPayment as $key => $value) {
                $TOTAL_MGMNT_FEE = $TOTAL_MGMNT_FEE + ($value['mgmnt_fee']);
            }

            return FFM::dollar($TOTAL_MGMNT_FEE);
        })->editColumn('date_funded', function ($partpayment) {
            return FFM::date($partpayment->date_funded);
        })->with('total_debited', FFM::dollar($totDebited))
                         ->with('total_company', FFM::dollar($totLlc))
                         ->with('total_syndicate', FFM::dollar($totSynd))
                         ->with('total_mgmnt', FFM::dollar($t_mgmnt_fee))
                         ->with('total_profit', FFM::dollar($t_profit))
                         ->with('total_pricipal', FFM::dollar($t_pricipal))
                         ->with('total_participant_rtr', FFM::dollar($t_participant_rtr))
                         ->with('total_particaipant_rtr_balance', FFM::dollar($t_participant_rtr_balance))
                         ->make(true);
    }

    public function getMerchantList($lender_id = null, $status_id = null, $marketplace = null, $not_started = null, $not_invested = null, $paid_off = null, $stop_payment = null, $over_payment = null, $user_id = null, $late_payment = null, $request_m = null, $from_date = null, $to_date = null, $advance_type = null, $label = null, $bank_account = null, $payment_pause = null, $mode_of_payment = null, $substatus_flag = null)
    {
        if ($lender_id) {
            $lender_id = $lender_id;
        }
        $status_id = isset($status_id) ? $status_id : '';
        $market_place_status = isset($marketplace) ? $marketplace : '';
        $stop_payment = isset($stop_payment) ? $stop_payment : '';
        $paid_off = isset($paid_off) ? $paid_off : '';
        $not_started = isset($not_started) ? $not_started : '';
        $not_invested = isset($not_invested) ? $not_invested : '';
        $over_payment = isset($over_payment) ? $over_payment : '';
        $bank_account = isset($bank_account) ? $bank_account : '';
        $payment_pause = isset($payment_pause) ? $payment_pause : '';
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
        $merchants_with_invest = MerchantUser::distinct('merchant_user.merchant_id')->pluck('merchant_id')->toArray();
        $data1 = Merchant::leftJoin('merchants_details','merchants_details.merchant_id','merchants.id')->select(['first_payment','centrex_advance_id', 'advance_type', 'funded as total_funded', 'payment_amount', 'users.name as lender_name', 'industry_id', 'industries.name as industry_name', 'us_states.state as state', 'merchants_details.agent_name', 'merchants_details.date_business_started', 'merchants_details.under_writer', 'merchants_details.entity_type', 'merchants_details.owner_credit_score', 'merchants_details.partner_credit_score', 'merchants_details.withhold_percentage', 'merchants_details.position', 'merchants_details.deal_type', 'merchants_details.iso_name', 'merchants_details.annual_revenue', 'merchants.phone', 'merchants.cell_phone', 'merchants.notification_email','monthly_revenue', 'first_name', 'last_name', DB::raw('sum(merchant_user.under_writing_fee) as underwriting_fee,  sum( invest_rtr * mgmnt_fee/100) as m_mgmnt_fee'),
            DB::raw('sum( 
                        (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) - 
                        (merchant_user.actual_paid_participant_ishare-(paid_mgmnt_fee) ) )  as default_amount '),

                         DB::raw(' 
                IF(advance_type="weekly_ach",
                    ( IF(  (DATEDIFF(current_date,first_payment)) * 0.142857 <= pmnts,(DATEDIFF(current_date,first_payment)) * 0.142857 ,pmnts) * payment_amount  )/rtr * 100, 
                    ( IF(  (DATEDIFF(current_date,first_payment)+lag_time) * 0.68 < pmnts,(DATEDIFF(current_date,first_payment)+lag_time) * 0.68 ,pmnts) * payment_amount  )/rtr * 100 
                ) as complete_percentage_p'), 'credit_score', 'factor_rate', 'annualized_rate', 'max_participant_fund', 'annualized_rate', 'merchants.id as mid2', 'sub_statuses.name as status', 'merchants.id', 'users.lag_time', 'merchants.last_payment_date', 'merchants.name', 'pmnts', 'first_payment', 'date_funded', 'marketplace_status', 'paid_count', 'commission', 'rtr', 'sub_status_id', 'merchants.complete_percentage', DB::raw('sum(merchant_user.amount) as amount, 
                        sum(merchant_user.invest_rtr  - merchant_user.paid_participant_ishare) as balance,

                        sum(  merchant_user.invest_rtr * ((100 - merchant_user.mgmnt_fee) / 100)
                        - (merchant_user.paid_participant_ishare-(paid_mgmnt_fee)  )
                   ) as balance_after_fee'),DB::raw('((sum(merchant_user.actual_paid_participant_ishare))/sum(merchant_user.invest_rtr)*100) as complete_per'), DB::raw('sum(merchant_user.pre_paid) as pre_paid'),DB::raw('sum(merchant_user.up_sell_commission) as up_sell_commission'), DB::raw('sum(merchant_user.commission_amount) as commission_amount'), DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'), DB::raw('sum(merchant_user.paid_mgmnt_fee) as paid_fee'),DB::raw('sum(merchant_user.total_agent_fee) as total_agent_fee'), DB::raw('sum(merchant_user.paid_participant_ishare) as participant_share'), ]);
        $data1 = $data1->leftjoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->leftJoin('industries', 'industries.id', 'merchants.industry_id')->leftJoin('us_states', 'us_states.id', 'merchants.state_id');
        if (Auth::user()->hasRole(['collection_user'])) {
        }
        $data1->leftjoin('users', 'users.id', 'merchants.lender_id')->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->orderByDesc('merchants.complete_percentage');
        $data1 = $data1->groupBy('merchants.id');
        if (empty($permission)) {
            $data1 = $data1->whereIn('merchant_user.user_id', $subinvestors);
        }
        if ($request_m != '') {
            if ($request_m == 'pay_off') {
                $data1 = $data1->where('merchants.pay_off', 1);
            } elseif ($request_m == 'money_request') {
                $data1 = $data1->where('merchants.money_request_status', 1);
            }
        }
        if ($lender_id != 0) {
            $data1 = $data1->whereIn('merchants.lender_id', $lender_id);
        }
        if ($advance_type) {
            $data1 = $data1->where('merchants.advance_type', $advance_type);
        }
        if ($label) {
            $data1 = $data1->whereIn('merchants.label', $label);
        }
        if ($user_id) {
            $specialAccounts = User::AgentAndOverpaymentIds();
            $result = array_intersect($user_id, $specialAccounts);
                if(count($result)>0){
                    $data1 = $data1->where(function($q){
                        $q->where('merchant_user.amount','>',0);
                        $q->orWhere('paid_participant_ishare','>',0);
        
                    });
                }
            $data1 = $data1->whereIn('merchant_user.user_id', $user_id);
        }
        if ($stop_payment == 'on') {
            $startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 10, date('Y')));
            $data1 = $data1->where('merchants.complete_percentage', '<', '99');
            $data1 = $data1->where('merchants.last_payment_date', '<', $startDate);
            $data1 = $data1->whereIn('merchants.sub_status_id', [4, 22]);
        }
        if ($over_payment == 'on') {
            $overpayment_accnt = User::OverpaymentIds();
            $overpayment_merchants = MerchantUser::distinct('merchant_user.merchant_id')->whereIn('user_id',$overpayment_accnt)->where('paid_participant_ishare','>',0)->pluck('merchant_id')->toArray();
            $data1 = $data1->whereIn('merchants.id', $overpayment_merchants);
        }
        $status = isset($status_id[0]) ? $status_id[0] : 0;
        if ($status != 0) {
            $data1 = $data1->whereIn('merchants.sub_status_id', $status_id);
        }
        if ($market_place_status == 'true') {
            $data1 = $data1->where('merchants.marketplace_status', 1);
        }
        if ($from_date != null) {
            $data1 = $data1->where('merchants.date_funded', '>=', $from_date);
        }
        if ($to_date != null) {
            $data1 = $data1->where('merchants.date_funded', '<=', $to_date);
        }
        if ($mode_of_payment) {
            if ($mode_of_payment == 'ach') {
                $data1 = $data1->whereHas('Payments', function (Builder $query) {
                    $query->where('mode_of_payment', 1);
                });
            }
            if ($mode_of_payment == 'manual') {
                $data1 = $data1->whereHas('Payments', function (Builder $query) {
                    $query->where('mode_of_payment', 0);
                });
            }
            if ($mode_of_payment == 'credit_card') {
                $data1 = $data1->whereHas('Payments', function (Builder $query) {
                    $query->where('mode_of_payment', 2);
                });
            }
        }
        if ($substatus_flag) {
            $data1 = $data1->whereIn('merchants.sub_status_flag', $substatus_flag);
        }
        if ($late_payment != null) {
            if ($late_payment != 90) {
                $start = $late_payment + 30;
                $end = $late_payment;
                $data1 = $data1->where(function ($query) use ($start, $end) {
                    $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+'.$end.') DAY)')->whereRaw('date(last_payment_date) >=  DATE_SUB(NOW(), INTERVAL (lag_time+'.$start.') DAY)');
                });
            } else {
                $data1 = $data1->where(function ($query) use ($late_payment) {
                    $query->whereRaw('date(last_payment_date) <=  DATE_SUB(NOW(), INTERVAL (lag_time+'.$late_payment.') DAY)');
                });
            }
        }
        $data1 = $data1->withCount(['investmentData AS invest_count' => function ($query) {
            $query->select(DB::raw('count(merchant_user.id) as invest_count'));
        }]);
        if ($paid_off == 'on') {
            $data1 = $data1->where('merchants.complete_percentage', '=', 100)->where('merchants.sub_status_id', 11);
        }
        if ($not_started == 'on') {
            $data1 = $data1->where('merchants.last_payment_date', '=', null);
        }
        if ($not_invested == 'on') {
            $data1 = $data1->whereNotIn('merchants.id', $merchants_with_invest);
        }
        if ($over_payment == 'on') {
            // $data1 = $data1->where('merchant_user.complete_per', '>', 101);
        }
        if ($bank_account == 'yes') {
            $data1 = $data1->whereHas('bankAccounts');
        }
        if ($bank_account == 'no') {
            $data1 = $data1->whereDoesntHave('bankAccounts');
        }
        if ($payment_pause == 'yes') {
            $data1 = $data1->whereNotNull('merchants.payment_pause_id');
        }
        if ($payment_pause == 'no') {
            $data1 = $data1->whereNull('merchants.payment_pause_id');
        }
        $data = $data1;
        $disabled_data = clone $data;
        $disabled_company_investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name', 'investor')->whereHas('company_relation', function ($query) {
            $query->where('company_status', 0);
        })->pluck('users.id')->toArray();
        $disabled_data = $disabled_data->whereIn('merchant_user.user_id',$disabled_company_investors);
        
        $data_arr['data'] = $data;
        $data_arr['disabled_data'] = $disabled_data;

        return $data_arr;
    }

    public function getAllStatements($start_date = null, $end_date = null, $investor = null, $column = false)
    {
        if ($column) {
            return [
                ['data' => 'checkbox', 'name' => 'checkbox', 'title' => '<label class="chc" title=""><input type="checkbox" name="delete_multi_statement"  id="checked_statement"><span class="checkmark checkk"></span></label>', 'orderable' => false, 'searchable' => false],
                ['data' => 'statement_id', 'title' => '#', 'orderable' => false, 'searchable' => false],
                ['data' => 'user_name', 'name' => 'users.name', 'title' => 'Investor', 'defaultContent' => ''],
                ['data' => 'pdf_name', 'name' => 'file_name', 'title' => 'PDF statement'],
                ['data' => 'csv_name', 'name' => 'file_name', 'title' => 'CSV statement'],
                ['data' => 'view', 'name' => 'view', 'title' => 'View', 'searchable' => false],
                ['data' => 'mail_status', 'name' => 'mail_status', 'title' => 'Mail Status', 'searchable' => false],
                ['data' => 'created_at', 'name' => 'statements.created_at', 'title' => 'Date'],
            ];
        }
        $userId = Auth::user()->id;
        $subinvestors = [];
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
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
        $data = Statements::select(DB::raw('upper(users.name) as user_name'),'file_name', 'statements.from_date', 'statements.to_date', 'statements.created_at', 'statements.id as statement_id', 'statements.id', 'statements.user_id', 'statements.mail_status', 'statements.creator_id')->join('users', 'users.id', 'statements.user_id');
        if (is_array($investor)) {
            $data = $data->whereIn('statements.user_id', $investor);
        }
        if ($start_date) {
            $start_date = $start_date;
            $data = $data->whereDate('statements.created_at', '>=', $start_date);
        }
        if ($end_date) {
            $end_date = $end_date;
            $data = $data->whereDate('statements.created_at', '<=', $end_date);
        }
        if (! empty($subinvestors)) {
            $data = $data->whereIn('user_id', $subinvestors);
        }
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($data)
        ->addColumn('checkbox', function ($data) {
            $id = $data->statement_id;
            return "<label class='chc'><input type='checkbox' class='checked_data' name='delete_statement[]' value='$id'><span class='checkmark checkk0'></span></label>";
        })
        ->editColumn('user_name', function ($data) {
            return $data->user_name;
        })
        ->editColumn('pdf_name', function ($data) {
            $file        = $data->file_name.'.pdf';
            $fileencrypt = encrypt($file);
            $fileUrl = route('admin::generated-file',[$fileencrypt]);
            return "<a href='".$fileUrl."'>".$file.'</a>';
        })
        ->editColumn('csv_name', function ($data) {
            $file        = $data->file_name.'.xlsx';
            $fileencrypt = encrypt($file);
            $fileUrl = route('admin::generated-file',[$fileencrypt]);
            return "<a href='".$fileUrl."'>".$file.'</a>';
        })
        ->addColumn('mail_status', function ($data) {
            $mail_status = '';
            if ($data->mail_status == 1) {
                $mail_status = "<span class='label label-success'>Mail Sent</span>";
            }
            return $mail_status;
        })
        ->addColumn('view', function ($data) {
            $url = url('/admin/investors/syndicationReport/'.$data->user_id);
            $url .= '?';
            $url .= 'date_start='.$data->from_date;
            $url .= '&';
            $url .= 'date_end='.$data->to_date;
            return "<a href='".$url."' target='_blank' class='label label-success'><i class='fa fa-eye'></i></a>";
        })
        ->addColumn('created_at', function ($data) {
            $creator = '';
            if ($data->creator_id!=null && $data->creator_id!=0) {
                $creator = get_user_name_with_session($data->creator_id);
            }
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.$creator;
            return "<a title='$created_date'>".FFM::datetimetodate($data->created_at).'</a>';
        })
        ->rawColumns(['checkbox', 'pdf_name', 'csv_name', 'mail_status', 'view', 'created_at'])
        ->filterColumn('name', function ($query, $keyword) {
            $sql = 'users.name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->make(true);
    }

    public function merchantList($lender_id = null, $status_id = null, $marketplace = null, $not_started = null, $paid_off = null, $stop_payment = null, $over_payment = null, $user_id = null, $late_payment = null, $request_m = null, $from_date = null, $to_date = null, $advance_type = null, $substatus_flag = null, $label = null, $not_invested = null, $bank_account = null, $payment_pause = null, $owner = null, $mode_of_payment = null)
    {   
        $merchants_with_invest = MerchantUser::distinct('merchant_user.merchant_id')->pluck('merchant_id')->toArray();
        if ($lender_id) {
            $lender_id = $lender_id;
        }
        $status_id = isset($status_id) ? $status_id : '';
        $market_place_status = isset($marketplace) ? $marketplace : '';
        $stop_payment = isset($stop_payment) ? $stop_payment : '';
        $paid_off = isset($paid_off) ? $paid_off : '';
        $not_started = isset($not_started) ? $not_started : '';
        $over_payment = isset($over_payment) ? $over_payment : '';
        $not_invested = isset($not_invested) ? $not_invested : '';
        $bank_account = isset($bank_account) ? $bank_account : '';
        $payment_pause = isset($payment_pause) ? $payment_pause : '';
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $collection_user_permission = 0;
        $userId = Auth::user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            if (! Auth::user()->hasRole(['company'])) {
                $subadmininvestor = $investor->where('creator_id', $userId);
            } else {
                $subadmininvestor = $investor;
            }
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }

        $data1 = Merchant::select(['sub_statuses.name as status_name','merchants.creator_id', 'merchants.created_at', 'merchants.id', 'users.lag_time', 'merchants.last_payment_date', 'merchants.name', 'pmnts', 'date_funded', 'marketplace_status', 'paid_count', 'rtr', 'sub_status_id', 'merchants.complete_percentage', DB::raw('sum(merchant_user.amount) as amount,sum(total_agent_fee) as total_agent_fee,

          sum(merchant_user.commission_amount) as commission_amount, sum(merchant_user.paid_participant_ishare) as paid_participant_ishare,
          sum(merchant_user.invest_rtr) as invest_rtr,
          sum( (merchant_user.invest_rtr-merchant_user.paid_participant_ishare)) as balance,
          sum(merchant_user.actual_paid_participant_ishare-(paid_mgmnt_fee)  - (merchant_user.invest_rtr -
            (merchant_user.mgmnt_fee/100*merchant_user.invest_rtr))) as balance1'), DB::raw('sum(merchant_user.under_writing_fee) as under_writing_fee,  sum( invest_rtr * mgmnt_fee/100) as mgmnt_fee'), DB::raw('sum((merchant_user.invest_rtr*merchant_user.mgmnt_fee/100)) as tot_managemnt_fee'),DB::raw('((sum(merchant_user.actual_paid_participant_ishare))/sum(merchant_user.invest_rtr)*100) as complete_per'), DB::raw('sum(merchant_user.paid_mgmnt_fee) as tot_paid_managemnt_fee'),DB::raw('sum(merchant_user.up_sell_commission) as up_sell_commission'), DB::raw('sum(merchant_user.pre_paid) as pre_paid'), DB::raw('sum(merchant_user.paid_mgmnt_fee) as paid_fee'), DB::raw('sum(merchant_user.paid_participant_ishare) as participant_share'), DB::raw('sum((((actual_paid_participant_ishare)-invest_rtr)*(1-(merchant_user.mgmnt_fee)/100))) as overpayment')])->leftjoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->leftjoin('users', 'users.id', 'merchants.lender_id')->join('sub_statuses','sub_statuses.id','merchants.sub_status_id')->groupBy('merchants.id');
        if (empty($permission)) {
            $data1 = $data1->whereIn('merchant_user.user_id', $subinvestors);
        }
        if ($lender_id != 0) {
            $data1 = $data1->whereIn('merchants.lender_id', $lender_id);
        }
        if ($label) {
            $data1 = $data1->whereIn('merchants.label', $label);
        }
        if ($advance_type) {
            $data1 = $data1->where('merchants.advance_type', $advance_type);
        }
        if ($mode_of_payment) {
            if ($mode_of_payment == 'ach') {
                $data1 = $data1->whereHas('Payments', function (Builder $query) {
                    $query->where('mode_of_payment', 1);
                });
            }
            if ($mode_of_payment == 'manual') {
                $data1 = $data1->whereHas('Payments', function (Builder $query) {
                    $query->where('mode_of_payment', 0);
                });
            }
            if ($mode_of_payment == 'credit_card') {
                $data1 = $data1->whereHas('Payments', function (Builder $query) {
                    $query->where('mode_of_payment', 2);
                });
            }
        }
        if ($request_m != '') {
            if ($request_m == 'pay_off') {
                $data1 = $data1->where('merchants.pay_off', 1);
            } elseif ($request_m == 'money_request') {
                $data1 = $data1->where('merchants.money_request_status', 1);
            }
        }
        if ($user_id) {
            $specialAccounts = User::AgentAndOverpaymentIds();
            $result = array_intersect($user_id, $specialAccounts);
                if(count($result)>0){
                    $data1 = $data1->where(function($q){
                        $q->where('merchant_user.amount','>',0);
                        $q->orWhere('paid_participant_ishare','>',0);
        
                    });
                }
            
            
            $data1 = $data1->whereIn('merchant_user.user_id', $user_id);
        }
        
        if ($collection_user_permission == 1) {
            $data1 = $data1->whereIn('merchants.sub_status_id', [4, 5]);
        }
        if ($stop_payment == 'true') {
            $startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 10, date('Y')));
            $data1 = $data1->where('merchants.complete_percentage', '<', '99');
            $data1 = $data1->where('merchants.last_payment_date', '<', $startDate);
            $data1 = $data1->whereIn('merchants.sub_status_id', [4, 22]);
        }
        if ($over_payment == 'true') {
            $overpayment_accnt = User::OverpaymentIds();
            $overpayment_merchants = MerchantUser::distinct('merchant_user.merchant_id')->whereIn('user_id',$overpayment_accnt)->where('paid_participant_ishare','>',0)->pluck('merchant_id')->toArray();
            $data1 = $data1->whereIn('merchants.id', $overpayment_merchants);
            
        }
        $status = isset($status_id[0]) ? $status_id[0] : 0;
        if ($status != 0) {
            $data1 = $data1->whereIn('merchants.sub_status_id', $status_id);
        }
        if ($market_place_status == 'true') {
            $data1 = $data1->where('merchants.marketplace_status', 1);
        }
        if ($substatus_flag) {
            $data1 = $data1->whereIn('merchants.sub_status_flag', $substatus_flag);
        }
        if ($late_payment != null) {
            if ($late_payment != 90) {
                $start = $late_payment + 30;
                $end = $late_payment;
                $data1 = $data1->where(function ($query) use ($start, $end) {
                    $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+'.$end.') DAY)')->whereRaw('date(last_payment_date) >=  DATE_SUB(NOW(), INTERVAL (users.lag_time+'.$start.') DAY)');
                });
            } else {
                $data1 = $data1->where(function ($query) use ($late_payment) {
                    $query->whereRaw('date(last_payment_date) <=  DATE_SUB(NOW(), INTERVAL (users.lag_time+'.$late_payment.') DAY)');
                });
            }
        }
        $data1 = $data1->withCount(['investmentData AS no_of_investor' => function ($query) {
            $query->join('user_has_roles', 'user_has_roles.model_id', 'merchant_user.user_id');
            $query->where('role_id', '!=', User::OVERPAYMENT_ROLE);
            $query->where('merchant_user.amount', '!=', 0);
            $query->where('role_id', '!=', User::AGENT_FEE_ROLE);
            $query->select(DB::raw('count(merchant_user.id) as no_of_investor'));
        }]);
        $data1 = $data1->withCount(['merchantNotes AS notes_count' => function ($query) {
            $query->select(DB::raw('count(m_notes.note) as notes_count'));
        }]);
        if ($paid_off == 'true') {
            $data1 = $data1->where('merchants.complete_percentage', '=', 100)->where('merchants.sub_status_id', 11);
        }
        if ($not_started == 'true') {
            $data1 = $data1->where('merchants.last_payment_date', '=', null);
        }
        if ($not_invested == 'true') {
            $data1 = $data1->whereNotIn('merchants.id', $merchants_with_invest);
        }
        if ($over_payment == 'true') {
            // $data1 = $data1->where('merchant_user.complete_per', '>', 101);
        }
        if ($from_date != null) {
            $data1 = $data1->where('date_funded', '>=', $from_date);
        }
        if ($to_date != null) {
            $data1 = $data1->where('date_funded', '<=', $to_date);
        }
        if ($bank_account == 'yes') {
            $data1 = $data1->whereHas('bankAccounts');
        }
        if ($bank_account == 'no') {
            $data1 = $data1->whereDoesntHave('bankAccounts');
        }
        if ($payment_pause == 'yes') {
            $data1 = $data1->whereNotNull('merchants.payment_pause_id');
        }
        if ($payment_pause == 'no') {
            $data1 = $data1->whereNull('merchants.payment_pause_id');
        }
        $data = $data1;
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($data)->addColumn('action', function ($data) {
            $edit = '';
            $view = '';
            $request = '';
            $del = '';
            if (Permissions::isAllow('Merchants', 'Edit')) {
                $edit = '<a href="'.route('admin::merchants::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>';
            }
            if (Permissions::isAllow('Merchants', 'View')) {
                // $view = '<a href="'.route('admin::merchants::view', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> View ('.$data->invest_count.')</a>';
                $request='<a onsubmit = "return confirm("Are you sure want to delete ?")" href="'.route('admin::merchants::requests::requests', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Requests</a>';
            }
            if (Permissions::isAllow('Merchants', 'Delete')) {
                $del = Form::open([
                    'route'    => ["admin::merchants::delete", 'id' => $data->id],
                    'method'   => 'POST',
                    'onsubmit' => 'return confirm("Are you sure want to delete ?")',
                    'class'    => 'btn-form-wrap'
                ]);
                $del.=Form::button('<i class="glyphicon glyphicon-trash"></i>',[
                    'type'  => "submit",
                    'title' => "Delete",
                    'class' => 'invest btn btn-xs btn-danger'
                ]);
                $del.=Form::close();
            }

            return $view.$request.$edit.$del;
        })->editColumn('name', function ($data) {
            $merchantName = $notes = '';
            if (Permissions::isAllow('Merchants', 'View')) {
                $merchantName .= '<a href="'.route('admin::merchants::view', ['id' => $data->id]).'">'.$data->name.'</a>';
            } else {
                $merchantName .= $data->name;
            }
            if (Permissions::isAllow('Notes', 'View')) {
                $notes .= '<a href="#" class="btn btn-xs btn-success" style="float:right" onclick="note('.$data->id.');" data-toggle="modal">Notes ('.$data->notes_count.')</a>';
            }

            return $merchantName.$notes;
        })->addColumn('invest_count', function ($data) {
            return $data->invest_count;
        })->addColumn('balance', function ($data) {
            if ($data->overpayment > 0) {
                $balance = FFM::dollar(0);
            } else {
                $balance_mg_fee = round($data->tot_managemnt_fee - $data->tot_paid_managemnt_fee, 2);
                $balance = FFM::dollar($data->balance);
            }

            return $balance;
        })->addColumn('overpayment', function ($data) {
            $overpayments = ($data->overpayment > 0) ? $data->overpayment : 0;

            return FFM::dollar($overpayments);
        })->editColumn('date_funded', function ($data) {
            $created_date = 'Created On '.\FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

            return "<a title='$created_date'>".FFM::date($data->date_funded).'</a>';
        })->editColumn('merchant_user.amount', function ($data) {
            return FFM::dollar($data->amount).'<span style="display:none">'.round($data->amount, 4).'</span>';
        })->editColumn('complete_percentage', function ($data) {
            return FFM::percent($data->complete_per);
        })->editColumn('pmnts', function ($data) {
            $now = time();
            $payment_amount = 0;
            $payments_count = 0;
            $last_payment_date = 0;
            $dates = [];
            $payments_count = $data->paid_count;
            $payment_amount = $data->paid_participant_ishare;
            $datediff = $now - strtotime($data->last_payment_date);
            $days = round($datediff / (60 * 60 * 24));
            if (! $data->last_payment_date) {
                return "<font color='#AA0'>".$data->pmnts.'</font>';
            } elseif ($payment_amount > $data->invest_rtr && $data->overpayment > 0 && $data->complete_percentage > 100) {
                return "<font color='blue'>".$data->pmnts.'</font>';
            } elseif (($payment_amount >= $data->rtr) || ($data->sub_status_id == 11) && $data->complete_percentage == 100) {
                return "<font color='green'>".$data->pmnts.'</font>';
            } elseif ((($days > 10) && ($data->complete_percentage < 100) && ($data->sub_status_id == 4 || $data->sub_status_id == 22))) {
                return "<font color='red'>".$data->pmnts.'</font>';
            } elseif ($data->sub_status_id == 1) {
                return $data->pmnts;
            } else {
                return $data->pmnts;
            }
        })->editColumn('paid_count', function ($data) {
            $now = time();
            $payment_amount = 0;
            $payments_count = 0;
            $last_payment_date = 0;
            $dates = [];
            $payments_count = ($data->paid_count>0)?$data->paid_count:0;
            $payment_amount = $data->paid_participant_ishare;
            $datediff = $now - strtotime($data->last_payment_date);
            $days = round($datediff / (60 * 60 * 24));
            if (! $payments_count) {
                return "<font color='#AA0'>".$payments_count.'</font>';
            } elseif ($payment_amount > $data->rtr) {
                return "<font color='blue'>".$data->pmnts.'</font>';
            } elseif ($payment_amount > $data->rtr && $data->sub_status_id == 11) {
                return "<font color='green'>".$payments_count.'</font>';
            } elseif ((($days > 10) && $data->complete_per < 100 && $data->sub_status_id == 1) || $data->sub_status_id == 4) {
                return "<font color='red'>".$payments_count.'</font>';
            } else {
                return $payments_count;
            }
        })->editColumn('net_zero', function ($data) {
            $total_invest_amount_with_commission = 0;
            $invested_amount_with_commission = $data->pre_paid + $data->amount + $data->commission_amount + $data->under_writing_fee+$data->up_sell_commission;
            $net_value = $invested_amount_with_commission;
            $ctd_sum = $total_payment = $paid_to_participant = 0;
            $dates = [];
            $paid_to_participant = $data->participant_share - $data->paid_fee;
            $net_zero_balance = (($net_value - $paid_to_participant) > 0) ? $net_value - $paid_to_participant : 0;

            return FFM::dollar($net_zero_balance);
        })->editColumn('last_payment_date', function ($data) {
            return FFM::date($data->last_payment_date);
        })->filterColumn('date_funded', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(date_funded,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })->filterColumn('merchant_user.amount', function ($query, $keyword) {
            $sql = 'merchant_user.amount like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('merchant.name', function ($query, $keyword) {
            $sql = 'merchant.name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->rawColumns(['date_funded', 'last_payment_date', 'action', 'paid_count', 'pmnts', 'name', 'merchant_user.amount'])->addIndexColumn()->make(true);
    }

    public function reconciliationRequestDetails($merchants = null, $reconciliation_status = null)
    {
        $data = DB::table('reconcilation_status')->leftJoin('merchants', 'merchants.id', '=', 'reconcilation_status.merchant_id')->select('reconciliation_status', 'name', 'date_funded', 'days', 'ip', 'reconcilation_status.created_at');
        if ($merchants) {
            $data = $data->whereIn('reconcilation_status.merchant_id', $merchants);
        }
        if ($reconciliation_status != null) {
            $data = $data->where('reconciliation_status', $reconciliation_status);
        }
        $data = $data->orderByDesc('date_funded');

        return $data;
    }

    public function ReconcilationRequest($merchants = null, $reconciliation_status = null)
    {
        $data = $this->reconciliationRequestDetails($merchants, $reconciliation_status);

        return DataTables::Collection($data->get())->editColumn('name', function ($data) {
            return "<a> $data->name </a>";
        })->editColumn('reconciliation_status', function ($data) {
            return $data->reconciliation_status;
        })->editColumn('date_funded', function ($data) {
            return FFM::date($data->date_funded);
        })->editColumn('days', function ($data) {
            return $data->days;
        })->editColumn('ip', function ($data) {
            return $data->ip;
        })->editColumn('date', function ($data) {
            if ($data->created_at != null) {
                return FFM::datetime($data->created_at);
            }
        })->rawColumns(['name'])->addIndexColumn()->make(true);
    }

    public function mailLog($merchants = null, $date_from = null, $date_to = null, $mail_type = null)
    {
        $data = $this->mailLogDetails($merchants, $date_from, $date_to, $mail_type);
        $mail_types = config('custom.mail_log_types');
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return DataTables::Collection($data->get())->editColumn('name', function ($data) {
            return isset($data->deleted_at) ? strtoupper($data->name) : '<a href="'.route('admin::merchants::view', ['id' => $data->to_id]).'">'.strtoupper($data->name).'</a>';
        })->editColumn('title', function ($data) {
            return $data->title;
        })->editColumn('type', function ($data) use ($mail_types) {
            return $mail_types[$data->type];
        })->editColumn('status', function ($data) {
            return $data->status;
        })->editColumn('to_email', function ($data) {
            return $data->to_mail;
        })->editColumn('failed_reason', function ($data) {
            return $data->failed_message;
        })->editColumn('date', function ($data) {
            if ($data->created_at != null) {
                if ($data->creator_id) {
                    $creator = get_user_name_with_session($data->creator_id);
                } else {
                    $creator = 'system';
                }
                $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.$creator;

                return "<a title='$created_date'>".FFM::datetime($data->created_at).'</a>';
            }
        })->rawColumns(['name', 'date'])->addIndexColumn()->make(true);
    }

    public function mailLogDetails($merchants = null, $date_from = null, $date_to = null, $mail_type = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $data = DB::table('mail_log')->join('merchants', 'merchants.id', '=', 'mail_log.to_id')->select('title', 'to_mail', 'status', 'to_id', 'mail_log.created_at', 'name', 'failed_message', 'type', 'to_name', 'mail_log.creator_id', 'merchants.deleted_at');
        if (empty($permission)) {
            $userId = Auth::user()->id;
            $investor = $this->role->allInvestors();
            if (Auth::user()->hasRole(['company'])) {
                $subadmininvestor = $investor->pluck('id')->toArray();
                $merchant_id = MerchantUser::whereIn('user_id', $subadmininvestor)->groupBy('merchant_id')->pluck('merchant_id')->toArray();
                $data = $data->whereIn('mail_log.to_id', $merchant_id);
            }
        }
        if ($merchants) {
            $data = $data->whereIn('mail_log.to_id', $merchants);
        }
        if ($mail_type) {
            $data = $data->where('mail_log.type', $mail_type);
        }
        if ($date_from) {
            $date_from = ET_To_UTC_Time($date_from.' 00:00', 'datetime');
            $data = $data->where('mail_log.created_at', '>=', $date_from);
        }
        if ($date_to) {
            $date_to = ET_To_UTC_Time($date_to.' 23:59', 'datetime');
            $data = $data->where('mail_log.created_at', '<=', $date_to);
        }
        $data = $data->orderByDesc('created_at');

        return $data;
    }

    public function investorList($investor_type = null, $company = null, $active_status = null, $liquidity = null, $auto_invest_label = null, $auto_generation = null, $notification_recurence = null)
    {
        $data = $this->user1->investorList($investor_type, $company, $active_status, $liquidity, $auto_invest_label, $auto_generation, $notification_recurence);
        $t_liq = $data['total']['total_liquidity'];

        return \DataTables::of($data['data'])->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Investors', 'View')) {
                $return .= '<a href="'.route('admin::investors::portfolio', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Portfolio</a>';
                $return .= '<a href="'.route('admin::investors::transaction::index', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Transactions </a>';
                $return .= '<a href="'.url('admin/merchant_investor/documents_upload/'.$data->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Documents </a>';
            }
            if (Permissions::isAllow('Investors', 'Edit')) {
                $return .= '<a href="'.route('admin::investors::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                $return .= '<a href="'.route('admin::investors::bank_details', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Bank</a>';
            }
            if (Permissions::isAllow('Investors', 'Delete')) {
                $return .= Form::open(['route' => ['admin::investors::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")', 'class' => 'btn-form-wrap']).Form::submit('Delete', ['class' => 'invest btn btn-xs btn-danger ']).Form::close();
            }
            if (@Permissions::isAllow('Generate PDF', 'Create')) {
                $return .= '<a href="'.route('admin::pdf_for_investors', ['id' => $data->id]).'" class="btn btn-xs btn-success"> Generate PDF</a>';
            }

            return $return;
        })->editColumn('name', function ($data) {
            $total_amount = 0;
            $fees = 0;
            $total_rtr = 0;
            $ctd = 0;
            $credited_amount = 0;
            $default_pay_rtr = $data->default_pay_rtr;
            $liquidity = $data->liquidity;
            $total_rtr = $data->rtr - $data->defaulted_balance + $data->overpayment;
            $ctd = $data->ctd;
            $value = ($total_rtr + $liquidity - $ctd);
            $value = ($value);
            $credited_amount = $data->amount;
            $credited_amount2 = $credited_amount;
            $credited_amount2 = ($credited_amount2);
            if (($value) > $credited_amount2) {
                $value = \FFM::dollar($value);
                $credited_amount2 = \FFM::dollar($credited_amount2);

                return " <a title=$value|$credited_amount2> $data->name </a>".'  '.' <img src='.asset('/images/greencheck.png')." width='25px' height='25px'>";
            } else {
                $value = \FFM::dollar($value);
                $credited_amount2 = \FFM::dollar($credited_amount2);

                return "<a title=$value|$credited_amount2 > $data->name </a>";
            }
        })->addColumn('global_syndication', function ($data) {
            return FFM::percent($data->global_syndication);
        })->addColumn('management_fee', function ($data) {
            return FFM::percent($data->management_fee);
        })->addColumn('users.created_at', function ($data) {
            return \FFM::datetime($data->created_at);
        })->addColumn('users.updated_at', function ($data) {
            return \FFM::datetime($data->updated_at);
        })->editColumn('email', function ($data) {
            return $data->email;
        })->editColumn('user_details.liquidity', function ($data) {
            return FFM::dollar($data->liquidity);
        })->filterColumn('name', function ($query, $keyword) {
            $query->where('name', 'like', ["%{$keyword}%"]);
        })->filterColumn('email', function ($query, $keyword) {
            $query->where('email', 'like', ["%{$keyword}%"]);
        })->rawColumns(['action', 'name'])->with('t_liq', FFM::dollar($t_liq))->addIndexColumn()->make(true);
    }

    public function accountsList($investor_type = null, $company = null, $active_status = null, $active_status_comapnies = null, $auto_invest_label = null, $auto_generation = null, $notification_recurence = null, $role_id = null, $search_key = null,$velocity_owned = false)
    {
        $data = $this->user1->accountsList($investor_type, $company, $active_status, $active_status_comapnies, $auto_invest_label, $auto_generation, $notification_recurence, $role_id, $search_key, $velocity_owned);
        $t_liq = $data['total']['total_liquidity'];
        $data_arr = $data['data']->get()->toarray();
        $total_amount = array_sum(array_column($data_arr, 'amount'));
        $count_total = $data['data'];
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($data['data'])->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Investors', 'View')) {
                if (env('APP_ENV') == 'local') {
                    $return .= '<a href="'.route('admin::investors::NewPortfolio', ['id' => $data->id]).'" class="btn btn-xs btn-success" title="New Portfolio"><i class="glyphicon glyphicon-view"></i> P</a>';
                }
                if($data['role_id']!=User::OVERPAYMENT_ROLE && $data['role_id']!=User::AGENT_FEE_ROLE){
                $return .= '<a href="'.route('admin::investors::transaction::index', ['id' => $data->id]).'" class="btn btn-xs btn-info" title="Transaction"><i class="glyphicon glyphicon-view"></i> T </a>';
                $return .= '<a href="'.url('admin/merchant_investor/documents_upload/'.$data->id).'" class="btn btn-xs btn-primary" title="Document"><i class="glyphicon glyphicon-view"></i> D </a>';
            }
            }
            if ($data['role_id'] != User::OVERPAYMENT_ROLE && $data['role_id'] != User::AGENT_FEE_ROLE) {
                if (Permissions::isAllow('Advance Plus Investments Report','view')) {
                    $return .= '<a href="' . route('admin::reports::AdvancePlusInvestments',$data->id) . '" class="btn btn-xs btn-secondary" title="Advance Plus Investments Report"><i class="glyphicon glyphicon-view"></i> APIR</a>';
                }
            }
           if($data['role_id']!=User::OVERPAYMENT_ROLE && $data['role_id']!=User::AGENT_FEE_ROLE){
           
            if (Permissions::isAllow('Investors', 'Edit')) {
                $return .= '<a href="'.route('admin::investors::bank_details', ['id' => $data->id]).'" class="btn btn-xs btn-secondary" title="Bank"><i class="glyphicon glyphicon-view"></i> B</a>';
            }

            if (@Permissions::isAllow('Generate PDF', 'Create')) {
                $return .= '<a href="'.route('admin::pdf_for_investors', ['id' => $data->id]).'" class="btn btn-xs btn-success"> Generate Statement</a>';
            }
        }
            if (Permissions::isAllow('Investors', 'Edit')) {
                $return .= '<a href="'.route('admin::investors::edit', ['id' => $data->id]).'" class="btn btn-xs btn-primary" title="Edit"><i class="glyphicon glyphicon-edit"></i></a>';
            }
            if (Permissions::isAllow('Investors', 'Delete')) {
                $return .= Form::open(['route' => ['admin::investors::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")', 'class' => 'btn-form-wrap']).Form::button('<i class="glyphicon glyphicon-trash"></i>', ['type'=>"submit",'title'=>"Delete",'class' => 'invest btn btn-xs btn-danger ']).Form::close();
            }
            return $return;
        })->editColumn('users.name', function ($data) {            $total_amount = 0;
            $fees = 0;
            $total_rtr = 0;
            $ctd = 0;
            $credited_amount = 0;
            $default_pay_rtr = $data->default_pay_rtr;
            $liquidity = $data->liquidity;
            $total_rtr = $data->rtr - $data->defaulted_balance + $data->overpayment;
            $ctd = $data->ctd;
            $value = ($total_rtr + $liquidity - $ctd);
            $value = ($value);
            $credited_amount = $data->amount;
            $credited_amount2 = $credited_amount;
            $credited_amount2 = ($credited_amount2);
            $url=route('admin::investors::portfolio', ['id' => $data->id]);
            if (($value) > $credited_amount2) {
                $value = \FFM::dollar($value);
                $credited_amount2 = \FFM::dollar($credited_amount2);
                return " <a href='".$url."' target='_blank' title=$value|$credited_amount2> $data->name </a>".'  '.' <img src='.asset('/images/greencheck.png')." width='25px' height='25px'>";
            } else {
                $value = \FFM::dollar($value);
                $credited_amount2 = \FFM::dollar($credited_amount2);
                return "<a href='".$url."' target='_blank' title=$value|$credited_amount2 > $data->name </a>";
            }
        })->addColumn('global_syndication', function ($data) {
            return FFM::percent($data->global_syndication);
        })->addColumn('management_fee', function ($data) {
            return FFM::percent($data->management_fee);
        })->addColumn('createdDate', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);
            return "<a title='".$created_date."'>".\FFM::datetime($data->created_at).'</a>';
        })->addColumn('users.updated_at', function ($data) {
            return \FFM::datetime($data->updated_at);
        })->editColumn('email', function ($data) {
            return $data->email;
        })->editColumn('user_details.liquidity', function ($data) {
            return FFM::dollar($data->liquidity);
        })->addColumn('principal_balance', function ($data) {
            return FFM::dollar($data->amount);
        })->filterColumn('users.name', function ($query, $keyword) {
            $query->where('users.name', 'like', ["%{$keyword}%"]);
        })->filterColumn('email', function ($query, $keyword) {
            $query->where('email', 'like', ["%{$keyword}%"]);
        })->rawColumns(['action', 'users.name', 'createdDate', 'users.updated_at'])->with('Total', 'Total:')->with('t_liq', FFM::dollar($t_liq))->with('t_amount', FFM::dollar($total_amount))
        ->addIndexColumn()
        ->make(true);
    }

    public function getInvestorList($investor_type = null, $company = null, $active_status = null, $active_status_comapnies = null, $liquidity = null, $auto_invest = null, $role_id = null, $auto_generation = null, $notification_recurence = null,$velocity_owned = false)
    {
        $data = $this->user1->investorList($investor_type, $company, $active_status, $active_status_comapnies , $liquidity, $auto_invest, $role_id, $auto_generation, $notification_recurence, $velocity_owned);

        return $data;
    }

    public function merchantsPerDiffReport($companies = [])
    {
        $data = $this->merchant->searchForMerchantsPerDiffReport();
        $result = \DataTables::of($data)->addColumn('id_m', function ($data) {
            return $data['id'];
        })->addColumn('diff', function ($data) {
            return round($data['diff'], 3);
        });
        foreach ($companies as $key => $com) {
            $result = $result->addColumn('company_payment'.$key, function ($data) use ($key) {
                return FFM::dollar($data['company_p_'.$key]);
            })->addColumn('company_investment'.$key, function ($data) use ($key) {
                return FFM::dollar($data['company_i_'.$key]);
            });
        }
        $result = $result->make(true);

        return $result;
    }

    public function sortByOrder($a, $b)
    {
        return $a['overpayment'] - $b['overpayment'];
    }

    public function overpaymentReport($sDate, $eDate, $merchants, $investors, $company, $lenders, $sub_statuses,$velocity_owned)
    {
        $data = $this->merchant->searchForOverPaymentReport($sDate, $eDate, $merchants, $investors, $company, $lenders, $sub_statuses,$velocity_owned);
        $company_users = DB::table('users');
        if ($company) {
            $company_users = $company_users->where('company', $company);
        }
        $company_users = $company_users->pluck('id')->toArray();
        $total = $data['total'];
        if ($total->t_overpayment != 0) {
            $total_rtr = $total->t_rtr;
        } else {
            $total_rtr = 0;
        }
        $result        = $data['old_data'];
        $overpayment   = $data['overpayments'];
        $result        = $result->get()->toArray();
        $t_overpayment = array_sum(array_column($result, 'overpayment'));
        $t_overpayment = 0;
        $new_array = [];
        $carry_forwards = DB::table('carry_forwards')->where('type', 1);
        $carry_forwards = $carry_forwards->join('merchants', 'merchants.id', 'carry_forwards.merchant_id');
        if (! empty($merchants) && is_array($merchants)) {
            $carry_forwards = $carry_forwards->whereIn('merchant_id', $merchants);
        }
        if (! empty($investors) && is_array($investors)) {
            $carry_forwards = $carry_forwards->whereIn('carry_forwards.investor_id', $investors);
        }
        if (! empty($sub_statuses) && is_array($sub_statuses)) {
            $carry_forwards = $carry_forwards->whereIn('merchants.sub_status_id', $sub_statuses);
        }
        if (! empty($lenders) && is_array($lenders)) {
            $carry_forwards = $carry_forwards->whereIn('merchants.lender_id', $lenders);
        }
        if ($sDate) {
            $carry_forwards = $carry_forwards->where('date', '>=', $sDate);
        }
        if ($eDate) {
            $carry_forwards = $carry_forwards->where('date', '<=', $eDate);
        }
        $carry_forwards = $carry_forwards->whereIn('carry_forwards.investor_id', $company_users);
        $carry_forwards = $carry_forwards->groupBy('merchant_id');
        $carry_forwards = $carry_forwards->pluck(DB::raw('sum(amount) as overpayment'), 'merchant_id as id')->toArray();
        $t_overpayment += array_sum($carry_forwards);
        $t_overpayment += array_sum($overpayment);
        foreach ($result as $key => $value) {
            $over_p   = isset($value['overpayment']) ? $value['overpayment']                                   : 0;
            $over_p   = 0;
            $over_p  += isset($carry_forwards[$value['merchant_id']]) ? $carry_forwards[$value['merchant_id']] : 0;
            $over_p  += isset($overpayment[$value['merchant_id']]) ? $overpayment[$value['merchant_id']]       : 0;
            if ($over_p != 0) {
                $new_array[$key]['merchant']    = $value['name'];
                $new_array[$key]['overpayment'] = $over_p;
                $new_array[$key]['total_rtr']   = $value['total_rtr'];
                $new_array[$key]['id']          = $value['merchant_id'];
            }
        }
        usort($new_array, function ($a, $b) {
            return $b['overpayment'] <=> $a['overpayment'];
        });
        return DataTables::of($new_array)
        ->editColumn('merchants.name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data['id'])."'>".strtoupper($data['merchant']).'</a>';
        })
        ->editColumn('overpayment', function ($data) {
            return FFM::dollar($data['overpayment']);
        })
        ->with('Total', 'Total:')
        ->with('t_overpayment', FFM::dollar($t_overpayment))
        ->rawColumns(['merchants.name'])
        ->make(true);
    }

    public function paymentReport($date_type, $sDate, $eDate, $mid, $iids, $lids, $subinvestors, $stime, $etime, $payment_type = null, $owner = null, $sub_statuses = null, $advance_type = null, $fields = null, $investor_type = null, $rcode = null, $overpayment = null, $label = null, $mode_of_payment = null, $payout_frequency = null, $investor_label = null, $historic_status = null, $filter_by_agent_fee = null, $active = null,$transaction_id=null,$velocity_owned=false)
    {
        if ($date_type == 'true') {
            if ($stime != '') {
                if ($sDate != '') {
                    $sDate = $sDate.' '.$stime.':00';
                }
            }

            if ($etime != '') {
                $eDate = $eDate.' '.$etime.':59';
            }
        }
        
        $data = $this->merchant->searchForPaymentReport($date_type, $sDate, $eDate, $mid, $iids, $lids, $payment_type, $subinvestors, $owner, $sub_statuses, $advance_type, $investor_type, $rcode, $overpayment, $label, $mode_of_payment, $payout_frequency, $investor_label, $historic_status, $filter_by_agent_fee, $active,$transaction_id,$velocity_owned);
        $filter_array = ['date_type' => $date_type, 'sDate' => $sDate, 'eDate' => $eDate, 'merchants' => $mid, 'investors' => $iids, 'lenders' => $lids, 'subinvestors' => $subinvestors, 'stime' => $stime, 'etime' => $etime, 'payment_type' => $payment_type, 'owner' => $owner, 'sub_statuses' => $sub_statuses, 'advance_type' => $advance_type, 'investor_type' => $investor_type, 'rcode' => $rcode, 'overpayment' => $overpayment, 'mode_of_payment' => $mode_of_payment];
        Session::put('search_filter', $filter_array);
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $total_payments = $total_profit = $total_pricipal = $total_net = $mag_fee = $t_rtr = $t_participant_share = $total_participant_rtr = $total_particaipant_rtr_balance = $total_net_balance = 0;

        if (request()->input('report_totals') == 1) {
            $total_carry_profit = array_sum(array_column($data->toArray(), 'carry_profit'));

            $total_profit = array_sum(array_column($data->toArray(), 'profit')); 
            $total_pricipal = array_sum(array_column($data->toArray(), 'principal'));

            if(empty($rcode))
            {
                   $total_profit += $total_carry_profit;
                   $total_pricipal -=  $total_carry_profit;
            }

            if ($historic_status != null) {
                $total_profit = array_sum(array_map(function ($var) {
                    if (in_array($var['substatus_id'], [4, 22])) {
                        $var['profit'] = $var['profit'] - $var['profit'];
                    } elseif (in_array($var['substatus_id'], [18, 19, 20])) {
                        $invested_amount = $var['invested_amount1'];
                        $adjuestmentAmount = $invested_amount - $var['principal'];
                        $var['profit'] = $var['profit'] - $adjuestmentAmount;
                    }

                    return $var['profit'];
                }, $data->toArray()));
                $total_pricipal = array_sum(array_map(function ($var) {
                    if (in_array($var['substatus_id'], [4, 22])) {
                        $var['principal'] = $var['principal'] + $var['profit'];
                    }
                    if (in_array($var['substatus_id'], [18, 19, 20])) {
                        $invested_amount = $var['invested_amount1'];
                        $adjuestmentAmount = $invested_amount - $var['principal'];
                        $var['principal'] = $var['principal'] + $adjuestmentAmount;
                    }

                    return $var['principal'];
                }, $data->toArray()));
            }
            
            $total_payments = array_sum(array_column($data->toArray(), 'actual_participant_share'));
            $mag_fee = array_sum(array_column($data->toArray(), 'mgmnt_fee'));
            $total_net = $total_payments - $mag_fee;
            $total_participant_rtr = array_sum(array_column($data->toArray(), 'invest_rtr'));
            if ($total_payments > $total_participant_rtr) {
                $total_particaipant_rtr_balance = '0.00';
            } else {
                $total_particaipant_rtr_balance = $total_participant_rtr - ($total_payments);
            }
        }
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        $dataTable = DataTables::of($data)->editColumn('participant_payment', function ($partpayment) {
        })->addColumn('debited', function ($data) {
            return FFM::dollar($data->debited);
        })->editColumn('actual_participant_share', function ($data) {

            return FFM::dollar($data->actual_participant_share);

        })->addColumn('particaipant_rtr_balance', function ($data) use($sDate){
            if($sDate==""){
                $net_balance = 0;
            }else{
                $net_balance = $data->net_balance;
            }
            if ($data->participant_share > $data->invest_rtr) {
                return FFM::dollar(0.00);
            } else {
                $bal_rtr = ($data->invest_rtr-$data->mgmnt_fee_amount) - ($net_balance + ($data->actual_participant_share-$data->mgmnt_fee));
                $bal_rtr = $bal_rtr;
                $bal_rtr = ($bal_rtr > 0) ? $bal_rtr : 0;
                return FFM::dollar($bal_rtr);
            }
        })->addColumn('substatus', function ($data) {
            return $data->substatus_name;
        })->addColumn('net_balance', function ($data)use($iids,$sDate) {
            if($sDate==""){
                $balance = 0;
            }else{
                $balance = $data->net_balance;
            }
            
            if (! empty($iids) && is_array($iids)) {
                $net_balance = ($data->invested_amount - ($balance + ($data->actual_participant_share) - $data->mgmnt_fee));
            } else {
                if($sDate!=""){
                    $balance = $balance+$data->net_bal_agent_fee;
                }
                $net_balance = ($data->invested_amount - ($balance + ($data->actual_participant_share) - $data->mgmnt_fee+$data->agent_fee));
            }
            return FFM::dollar($net_balance > 0 ? $net_balance : 0);
        })->addColumn('particaipant_rtr', function ($data) {
            $settled_rtr = isset($data->settled_rtr) ? $data->settled_rtr : 0;
            if ($settled_rtr != 0) {
                $settled_rtr = '( was '.FFM::dollar($data->settled_rtr).')';
            } else {
                $settled_rtr = '';
            }

            return FFM::dollar($data->invest_rtr-$data->mgmnt_fee_amount).$settled_rtr;
        })->addColumn('last_payment_amount', function ($data) {
            return FFM::dollar($data->last_payment_amount);
        })->addColumn('payments', function ($data) {
            return FFM::dollar($data->actual_participant_share);
        })->addColumn('syndicate', function ($data) {
            $amount = round($data->actual_participant_share - $data->mgmnt_fee, 4);

            return FFM::dollar($amount);
        })->addColumn('mgmnt_fee', function ($data) {
            return FFM::dollar($data->mgmnt_fee);
        })->addColumn('profit', function ($data) use ($historic_status,$rcode) {
            $profit = $data->profit + $data->carry_profit;
            $principal = $data->principal - $data->carry_profit;
            if ($historic_status != null && in_array($data->substatus_id, [4, 22])) {
                $profit = $profit - $profit;
            }
            if ($historic_status != null && in_array($data->substatus_id, [18, 19, 20])) {
                $invested_amount = $data->invested_amount1;
                $adjuestmentAmount = $invested_amount - $principal;
                $profit = $profit - $adjuestmentAmount;
            }

            if(!empty($rcode))
            {
               $profit=  $data->profit;

            }

            return FFM::dollar($profit);
        })->addColumn('principal', function ($data) use ($historic_status,$rcode) {
            $principal = $data->principal - $data->carry_profit;
            $profit = $data->profit + $data->carry_profit;
            if ($historic_status != null && in_array($data->substatus_id, [4, 22])) {
                $principal = $principal + $profit;
            }
            if ($historic_status != null && in_array($data->substatus_id, [18, 19, 20])) {
                $invested_amount = $data->invested_amount1;
                $adjuestmentAmount = $invested_amount - $principal;
                $principal = $principal + $adjuestmentAmount;
            }
            if(!empty($rcode))
            {
               $principal = $data->principal;

            }

            return FFM::dollar($principal);
        })->editColumn('name', function ($data) {
            return "<a target='blank' style = 'display:none'> $data->name</a><a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>";
        })->rawColumns(['name', 'last_payment_date', 'date_funded','net_balance'])->editColumn('date_funded', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

            return "<a title='$created_date'>".FFM::date($data->date_funded).'</a>';
        })->editColumn('last_payment_date', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->last_payment_created_at).' by '.get_user_name_with_session($data->last_payment_creator_id);

            return $data->last_payment_date ? "<a title='$created_date'>".FFM::date($data->last_payment_date).'</a>' : '';
        })->with('Total', 'Total:')->with('total_debited', FFM::dollar(0))->with('total_company', FFM::dollar($total_payments))->with('total_profit', FFM::dollar($total_profit))->with('total_principle', FFM::dollar($total_pricipal))->with('total_syndicate', FFM::dollar($total_net))->with('total_mgmnt', FFM::dollar($mag_fee))->with('total_particaipant_rtr_balance', FFM::dollar($total_particaipant_rtr_balance));

        return $dataTable->make();
    }

    public function paymentReport_second_section($date_type, $sDate, $eDate, $mid, $iids, $lids, $subinvestors, $stime, $etime, $payment_type = null, $balance_report = null, $owner = null, $sub_statuses = null)
    {
        $management_fee1 = 0;
        $syndication_fee1 = 0;
        $participant_share1 = 0;
        $final_participant_share1 = 0;
        $total_payment1 = 0;
        $dates_av1 = [];
        $data = $this->merchant->searchForPaymentReport($date_type, $sDate, $eDate, $mid, $iids, $lids, $payment_type, $subinvestors, $owner, $sub_statuses);
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $query_data = ParticipentPayment::leftJoin('merchants', 'merchants.id', '=', 'participent_payments.merchant_id');
        $query_data = $query_data->where('participent_payments.is_payment', 1);
        if (empty($permission) || $owner) {
            $query_data->whereIn('payment_investors.user_id', $subinvestors);
        }
        if (($iids)) {
            $query_data->whereIn('payment_investors.user_id', $iids);
        }
        $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'participent_payments.payment_date';
        if ($date_type == 'true') {
            $sDate = ($sDate) ? $sDate.' '.$stime : null;
            $eDate = ($eDate) ? $eDate.' '.$etime : null;
        }
        if ($eDate) {
            $query_data->where($table_field, '<=', $eDate);
        }
        if ($sDate) {
            $query_data->where($table_field, '>=', $sDate);
        }
        if ($lids) {
            $query_data->whereIn('merchants.lender_id', $lids);
        }
        if ($sub_statuses) {
            $query_data->whereIn('merchants.sub_status_id', $sub_statuses);
        }
        if ($mid != null) {
            $query_data->whereIn('participent_payments.merchant_id', $mid);
        }
        if ($payment_type != null) {
            $query_data->where('participent_payments.payment_type', $payment_type);
        }
        $syndication_fee1 = $query_data->sum('payment_investors.syndication_fee');
        $management_fee1 = $query_data->sum('payment_investors.mgmnt_fee');
        $participant_share1 = $query_data->sum('participent_payments.participant_share');
        $final_participant_share1 = $query_data->sum('participent_payments.final_participant_share');
        $debited = $query_data->groupBy('participent_payments.merchant_id')->groupBy('participent_payments.payment_date')->sum('participent_payments.payment');
        $date_dt = $query_data->select('participent_payments.payment_date', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.final_participant_share', 'merchants.m_s_prepaid_status', 'merchant_user.syndication_fee', 'merchants.factor_rate', 'merchants.commission')->get()->toArray();
        $total_pricipal = $total_profit = 0;
        foreach ($date_dt as $dt) {
            $total_payment1 = $total_payment1 + $dt['payment'];
            if ($balance_report === 'true') {
                $total_payment1 = $total_payment1;
                $total_rtr = $total_investor_rtr = $total_management_fee = $total_syndication_fee = $total_invest_rtr = 0;
                $i = 0;
                foreach ($query_data->select('participent_payments.merchant_id')->distinct()->get()->toArray() as $result) {
                    $total_rtr = $total_rtr + Merchant::where('id', $result['merchant_id'])->value('rtr');
                    $total_investor_rtr = $total_investor_rtr + MerchantUser::where('merchant_id', $result['merchant_id'])->sum('invest_rtr');
                    $investor_data_all = MerchantUser::where('merchant_id', $result['merchant_id'])->get()->toArray();
                    foreach ($investor_data_all as $investor) {
                        $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                        $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                        $total_invest_rtr = $total_invest_rtr + $investor['invest_rtr'];
                    }
                }
                $total_payment1 = $total_rtr - $total_payment1;
                $debited = $total_invest_rtr - $debited;
                $participant_share1 = $total_investor_rtr - $participant_share1;
                $management_fee1 = $total_management_fee - $management_fee1;
                $syndication_fee1 = $total_syndication_fee - $syndication_fee1;
                $final_part = ($debited - ($management_fee1 + $syndication_fee1));
            } else {
                $final_part = $final_participant_share1;
            }
            if ($dt['s_prepaid_status'] == 2) {
                $multiplier = 1 * $dt['syndication_fee'] / 100;
            } elseif ($dt['s_prepaid_status'] == 1) {
                $multiplier = $dt['factor_rate'] * $dt['syndication_fee'] / 100;
            } else {
                $multiplier = 0;
            }
            $principal = $final_part * (((1 + ($dt['commission']) / 100) + $multiplier) / $dt['factor_rate']);
            $profit = $final_part - ($final_part * (((1 + ($dt['commission']) / 100) + $multiplier) / $dt['factor_rate']));
            $total_pricipal = $principal;
            $total_profit = $profit;
        }

        return DataTables::of($data)->editColumn('participant_payment', function ($partpayment) {
            $array = [];
            $new_date = 0;
            $old_date = 0;
            $user_id = [];
            $payment_date = '';
            $payment_d = 0;
            $participant_share = 0;
            $final_participant_share = 0;
            $mgmnt_fee = 0;
            $syndication_fee = 0;
            foreach ($partpayment->participantPayment as $key => $payment) {
                if ($old_date == $payment->payment_date || $old_date == 0) {
                    $new_date = 1;
                } else {
                    $array[] = [
                        'participant'        => $user_id,
                        'id'                 => $merchant_id,
                        'ledger_date'        => FFM::date($payment_date),
                        'debited'            => FFM::dollar($payment_d),
                        'syndication_amount' => FFM::dollar($participant_share),
                        'to_syndicate'       => FFM::dollar($final_participant_share),
                        'mgmnt_fee'          => FFM::dollar($mgmnt_fee),
                        'syndication_fee'    => FFM::dollar($syndication_fee),
                    ];
                    $user_id = [];
                    $payment_date = '';
                    $payment_d = 0;
                    $participant_share = 0;
                    $final_participant_share = 0;
                    $mgmnt_fee = 0;
                    $syndication_fee = 0;
                    $new_date = 1;
                }
                $part_hover = "<p><b>ParticipantShare</b>:$payment->participant_share </p>
                                            <p><b>ManagementFee</b>:$payment->mgmnt_fee</p>
                                            <p><b>SyndicationFee</b>$payment->syndication_fee</p>
                                            <p><b>NetAmount</b>:$payment->final_participant_share</p>";
                $investor_detail = '<a data-html=true class=popoverButton data-toggle=popover data-trigger=hover title=participant >'.$payment->user_id.'</a>';
                $merchant_id = $payment->merchant_id;
                $old_date = $payment->payment_date;
                $user_id[] = ['link' => $investor_detail, 'info' => $part_hover];
                $payment_date = $payment->payment_date;
                $payment_d = $payment->payment;
                $participant_share = $participant_share + $payment->participant_share;
                $final_participant_share = $final_participant_share + $payment->final_participant_share;
                $mgmnt_fee = $mgmnt_fee + $payment->mgmnt_fee;
                $syndication_fee = $syndication_fee + $payment->syndication_fee;
            }
            if ($old_date) {
                $array[] = [
                    'participant' => $user_id,
                    'id' => $merchant_id,
                    'ledger_date' => FFM::date($payment_date),
                    'debited' => FFM::dollar($payment_d),
                    'syndication_amount' => FFM::dollar($participant_share),
                    'to_syndicate' => FFM::dollar($final_participant_share),
                    'mgmnt_fee' => FFM::dollar($mgmnt_fee),
                    'syndication_fee' => FFM::dollar($syndication_fee),
                ];
            }

            return $array;
        })->addColumn('TOTAL_DEBITED', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $rtr = Merchant::where('id', $merchant_id)->value('rtr');

                return FFM::dollar($rtr - ($partpayment->participantPayment->unique('payment_date')->sum('payment')));
            } else {
                return FFM::dollar($partpayment->participantPayment->unique('payment_date')->sum('payment'));
            }
        })->addColumn('TOTAL_COMPANY', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $investor_rtr = MerchantUser::where('merchant_id', $merchant_id)->sum('invest_rtr');

                return FFM::dollar($investor_rtr - ($partpayment->participantPayment->sum('participant_share')));
            } else {
                return FFM::dollar($partpayment->participantPayment->sum('participant_share'));
            }
        })->addColumn('TOTAL_SYNDICATE', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $total_management_fee = $total_syndication_fee = $total_invest_rtr = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                    $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                    $total_invest_rtr = $total_invest_rtr + $investor['invest_rtr'];
                }
                $balance_rtr = $total_invest_rtr - ($partpayment->participantPayment->sum('participant_share'));
                $balance_managmnt_fee = $total_management_fee - $partpayment->participantPayment->sum('mgmnt_fee');
                $balance_syndication_fee = $total_syndication_fee - $partpayment->participantPayment->sum('syndication_fee');

                return FFM::dollar($balance_rtr - ($balance_managmnt_fee + $balance_syndication_fee));
            } else {
                return FFM::dollar($partpayment->participantPayment->sum('final_participant_share'));
            }
        })->addColumn('TOTAL_MGMNT_FEE', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $total_management_fee = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                }

                return FFM::dollar($total_management_fee - $partpayment->participantPayment->sum('mgmnt_fee'));
            } else {
                return FFM::dollar($partpayment->participantPayment->sum('mgmnt_fee'));
            }
        })->addColumn('TOTAL_SYND_FEE', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $total_syndication_fee = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                }

                return FFM::dollar($total_syndication_fee - $partpayment->participantPayment->sum('syndication_fee'));
            } else {
                return FFM::dollar($partpayment->participantPayment->sum('syndication_fee'));
            }
        })->addColumn('profit', function ($data) use ($balance_report) {
            $merchant_id = $data->id;
            if ($balance_report === 'true') {
                $total_management_fee = $total_syndication_fee = $total_invest_rtr = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                    $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                    $total_invest_rtr = $total_invest_rtr + $investor['invest_rtr'];
                }
                $balance_rtr = $total_invest_rtr - ($data->participantPayment->sum('participant_share'));
                $balance_managmnt_fee = $total_management_fee - $data->participantPayment->sum('mgmnt_fee');
                $balance_syndication_fee = $total_syndication_fee - $data->participantPayment->sum('syndication_fee');
                $final_part = ($balance_rtr - ($balance_managmnt_fee + $balance_syndication_fee));
            } else {
                $final_part = $data->participantPayment->sum('final_participant_share');
            }
            if ($data->s_prepaid_status == 2) {
                $multiplier = 1 * $data->syndication_fee / 100;
            } elseif ($data->s_prepaid_status == 1) {
                $multiplier = $data->factor_rate * $data->syndication_fee / 100;
            } else {
                $multiplier = 0;
            }

            return FFM::dollar($final_part - ($final_part * (((1 + ($data->commission) / 100) + $multiplier) / $data->factor_rate)));
        })->addColumn('principal', function ($data) use ($balance_report) {
            $merchant_id = $data->id;
            if ($balance_report === 'true') {
                $total_management_fee = $total_syndication_fee = $total_invest_rtr = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                    $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                    $total_invest_rtr = $total_invest_rtr + $investor['invest_rtr'];
                }
                $balance_rtr = $total_invest_rtr - ($data->participantPayment->sum('participant_share'));
                $balance_managmnt_fee = $total_management_fee - $data->participantPayment->sum('mgmnt_fee');
                $balance_syndication_fee = $total_syndication_fee - $data->participantPayment->sum('syndication_fee');
                $final_part = ($balance_rtr - ($balance_managmnt_fee + $balance_syndication_fee));
            } else {
                $final_part = $data->participantPayment->sum('final_participant_share');
            }
            if ($data->s_prepaid_status == 2) {
                $multiplier = 1 * $data->syndication_fee / 100;
            } elseif ($data->s_prepaid_status == 1) {
                $multiplier = $data->factor_rate * $data->syndication_fee / 100;
            } else {
                $multiplier = 0;
            }

            return FFM::dollar($final_part * (((1 + ($data->commission) / 100) + $multiplier) / $data->factor_rate));
        })->editColumn('name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>";
        })->rawColumns(['name'])->editColumn('date_funded', function ($data) {
            return FFM::date($data->date_funded);
        })->with('total_debited', FFM::dollar($total_payment1))
            ->with('total_company', FFM::dollar($participant_share1))
            ->with('total_syndicate', FFM::dollar($participant_share1 - $management_fee1 - $syndication_fee1))
            ->with('total_mgmnt', FFM::dollar($management_fee1))
            ->with('total_synd_fee', FFM::dollar($syndication_fee1))
            ->with('total_principal', FFM::dollar($total_pricipal))
            ->with('total_profit', FFM::dollar($total_profit))
            ->make(true);
    }

    public function paymentReport_Original($date_type, $sDate, $eDate, $mid, $iids, $lids, $subinvestors, $stime, $etime, $payment_type = null, $balance_report = null, $owner = null, $sub_statuses = null)
    {
        $management_fee1 = 0;
        $syndication_fee1 = 0;
        $participant_share1 = 0;
        $total_payment1 = 0;
        $dates_av1 = [];
        $data = $this->merchant->searchForPaymentReport($date_type, $sDate, $eDate, $mid, $iids, $lids, $payment_type, $subinvestors, $owner, $sub_statuses);
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $query_data = ParticipentPayment::leftJoin('merchants', 'merchants.id', '=', 'participent_payments.merchant_id');
        $query_data = $query_data->where('participent_payments.is_payment', 1);
        if (empty($permission) || $owner) {
            $query_data->whereIn('payment_investors.user_id', $subinvestors);
        }
        if (($iids)) {
            $query_data->whereIn('payment_investors.user_id', $iids);
        }
        $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'participent_payments.payment_date';
        if ($date_type == 'true') {
            $sDate = ($sDate) ? $sDate.' '.$stime : null;
            $eDate = ($eDate) ? $eDate.' '.$etime : null;
        }
        if ($eDate) {
            $query_data->where($table_field, '<=', $eDate);
        }
        if ($sDate) {
            $query_data->where($table_field, '>=', $sDate);
        }
        if ($lids) {
            $query_data->whereIn('merchants.lender_id', $lids);
        }
        if ($sub_statuses) {
            $query_data->whereIn('merchants.sub_status_id', $sub_statuses);
        }
        if ($mid != null) {
            $query_data->whereIn('participent_payments.merchant_id', $mid);
        }
        if ($payment_type != null) {
            $query_data->where('participent_payments.payment_type', $payment_type);
        }
        $syndication_fee1 = $query_data->sum('payment_investors.syndication_fee');
        $management_fee1 = $query_data->sum('payment_investors.mgmnt_fee');
        $participant_share1 = $query_data->sum('participent_payments.participant_share');
        $date_dt = $query_data->select('participent_payments.payment_date', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.final_participant_share', 'merchants.m_s_prepaid_status', 'merchant_user.syndication_fee', 'merchants.factor_rate', 'merchants.commission')->groupBy('participent_payments.payment_date')->groupBy('merchant_id')->get()->toArray();
        $final_part = $total_pricipal = $total_profit = 0;
        foreach ($date_dt as $dt) {
            $total_payment1 = $total_payment1 + $dt['payment'];
            if ($balance_report === 'true') {
                $total_payment1 = $total_payment1;
                $total_rtr = $total_investor_rtr = $total_management_fee = $total_syndication_fee = 0;
                $i = 0;
                foreach ($query_data->select('participent_payments.merchant_id')->distinct()->get()->toArray() as $result) {
                    $total_rtr = $total_rtr + Merchant::where('id', $result['merchant_id'])->value('rtr');
                    $total_investor_rtr = $total_investor_rtr + MerchantUser::where('merchant_id', $result['merchant_id'])->sum('invest_rtr');
                    $investor_data_all = MerchantUser::where('merchant_id', $result['merchant_id'])->get()->toArray();
                    foreach ($investor_data_all as $investor) {
                        $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                        $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                    }
                    $i++;
                }
                $total_payment1 = $total_rtr - $total_payment1;
                $participant_share1 = $total_investor_rtr - $participant_share1;
                $management_fee1 = $total_management_fee - $management_fee1;
                $syndication_fee1 = $total_syndication_fee - $syndication_fee1;
                $final_part = ($total_payment1 - ($management_fee1 + $syndication_fee1));
            } else {
                $final_part = $final_part + $dt['final_participant_share'];
            }
            if ($dt['s_prepaid_status'] == 2) {
                $multiplier = 1 * $dt['syndication_fee'] / 100;
            } elseif ($dt['s_prepaid_status'] == 1) {
                $multiplier = $dt['factor_rate'] * $dt['syndication_fee'] / 100;
            } else {
                $multiplier = 0;
            }
            $principal = $final_part * (((1 + ($dt['commission']) / 100) + $multiplier) / $dt['factor_rate']);
            $profit = $final_part - ($final_part * (((1 + ($dt['commission']) / 100) + $multiplier) / $dt['factor_rate']));
            $total_pricipal = $principal;
            $total_profit = $profit;
        }

        return DataTables::of($data)->editColumn('participant_payment', function ($partpayment) {
            $array = [];
            $new_date = 0;
            $old_date = 0;
            $user_id = [];
            $payment_date = '';
            $payment_d = 0;
            $participant_share = 0;
            $final_participant_share = 0;
            $mgmnt_fee = 0;
            $syndication_fee = 0;
            foreach ($partpayment->participantPayment as $key => $payment) {
                if ($old_date == $payment->payment_date || $old_date == 0) {
                    $new_date = 1;
                } else {
                    $array[] = [
                        'participant' => $user_id,
                        'id' => $merchant_id,
                        'ledger_date' => FFM::date($payment_date),
                        'debited' => FFM::dollar($payment_d),
                        'syndication_amount' => FFM::dollar($participant_share),
                        'to_syndicate' => FFM::dollar($final_participant_share),
                        'mgmnt_fee' => FFM::dollar($mgmnt_fee),
                        'syndication_fee' => FFM::dollar($syndication_fee),
                    ];
                    $user_id = [];
                    $payment_date = '';
                    $payment_d = 0;
                    $participant_share = 0;
                    $final_participant_share = 0;
                    $mgmnt_fee = 0;
                    $syndication_fee = 0;
                    $new_date = 1;
                }
                $part_hover = "<p><b>ParticipantShare</b>:$payment->participant_share </p>
                                            <p><b>ManagementFee</b>:$payment->mgmnt_fee</p>
                                            <p><b>SyndicationFee</b>$payment->syndication_fee</p>
                                            <p><b>NetAmount</b>:$payment->final_participant_share</p>";
                $investor_detail = '<a data-html=true class=popoverButton data-toggle=popover data-trigger=hover title=participant >'.$payment->user_id.'</a>';
                $merchant_id = $payment->merchant_id;
                $old_date = $payment->payment_date;
                $user_id[] = ['link' => $investor_detail, 'info' => $part_hover];
                $payment_date = $payment->payment_date;
                $payment_d = $payment->payment;
                $participant_share = $participant_share + $payment->participant_share;
                $final_participant_share = $final_participant_share + $payment->final_participant_share;
                $mgmnt_fee = $mgmnt_fee + $payment->mgmnt_fee;
                $syndication_fee = $syndication_fee + $payment->syndication_fee;
            }
            if ($old_date) {
                $array[] = [
                    'participant' => $user_id,
                    'id' => $merchant_id,
                    'ledger_date' => FFM::date($payment_date),
                    'debited' => FFM::dollar($payment_d),
                    'syndication_amount' => FFM::dollar($participant_share),
                    'to_syndicate' => FFM::dollar($final_participant_share),
                    'mgmnt_fee' => FFM::dollar($mgmnt_fee),
                    'syndication_fee' => FFM::dollar($syndication_fee),
                ];
            }

            return $array;
        })->addColumn('TOTAL_DEBITED', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $rtr = Merchant::where('id', $merchant_id)->value('rtr');

                return FFM::dollar($rtr - ($partpayment->participantPayment->unique('payment_date')->sum('payment')));
            } else {
                return FFM::dollar($partpayment->participantPayment->unique('payment_date')->sum('payment'));
            }
        })->addColumn('TOTAL_COMPANY', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $investor_rtr = MerchantUser::where('merchant_id', $merchant_id)->sum('invest_rtr');

                return FFM::dollar($investor_rtr - ($partpayment->participantPayment->sum('participant_share')));
            } else {
                return FFM::dollar($partpayment->participantPayment->sum('participant_share'));
            }
        })->addColumn('TOTAL_SYNDICATE', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $total_management_fee = $total_syndication_fee = $total_invest_rtr = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                    $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                    $total_invest_rtr = $total_invest_rtr + $investor['invest_rtr'];
                }
                $balance_rtr = $total_invest_rtr - ($partpayment->participantPayment->sum('participant_share'));
                $balance_managmnt_fee = $total_management_fee - $partpayment->participantPayment->sum('mgmnt_fee');
                $balance_syndication_fee = $total_syndication_fee - $partpayment->participantPayment->sum('syndication_fee');

                return FFM::dollar($balance_rtr - ($balance_managmnt_fee + $balance_syndication_fee));
            } else {
                return FFM::dollar($partpayment->participantPayment->sum('final_participant_share'));
            }
        })->addColumn('TOTAL_MGMNT_FEE', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $total_management_fee = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                }

                return FFM::dollar($total_management_fee - $partpayment->participantPayment->sum('mgmnt_fee'));
            } else {
                return FFM::dollar($partpayment->participantPayment->sum('mgmnt_fee'));
            }
        })->addColumn('TOTAL_SYND_FEE', function ($partpayment) use ($balance_report) {
            $merchant_id = $partpayment->id;
            if ($balance_report === 'true') {
                $total_syndication_fee = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                }

                return FFM::dollar($total_syndication_fee - $partpayment->participantPayment->sum('syndication_fee'));
            } else {
                return FFM::dollar($partpayment->participantPayment->sum('syndication_fee'));
            }
        })->addColumn('profit', function ($data) use ($balance_report) {
            $merchant_id = $data->id;
            if ($balance_report === 'true') {
                $total_management_fee = $total_syndication_fee = $total_invest_rtr = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                    $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                    $total_invest_rtr = $total_invest_rtr + $investor['invest_rtr'];
                }
                $balance_rtr = $total_invest_rtr - ($data->participantPayment->sum('participant_share'));
                $balance_managmnt_fee = $total_management_fee - $data->participantPayment->sum('mgmnt_fee');
                $balance_syndication_fee = $total_syndication_fee - $data->participantPayment->sum('syndication_fee');
                $final_part = ($balance_rtr - ($balance_managmnt_fee + $balance_syndication_fee));
            } else {
                $final_part = $data->participantPayment->sum('final_participant_share');
            }
            if ($data->s_prepaid_status == 2) {
                $multiplier = 1 * $data->syndication_fee / 100;
            } elseif ($data->s_prepaid_status == 1) {
                $multiplier = $data->factor_rate * $data->syndication_fee / 100;
            } else {
                $multiplier = 0;
            }

            return FFM::dollar($final_part - ($final_part * (((1 + ($data->commission) / 100) + $multiplier) / $data->factor_rate)));
        })->addColumn('principal', function ($data) use ($balance_report) {
            $merchant_id = $data->id;
            if ($balance_report === 'true') {
                $total_management_fee = $total_syndication_fee = $total_invest_rtr = 0;
                $investor_data = MerchantUser::where('merchant_id', $merchant_id)->get()->toArray();
                foreach ($investor_data as $investor) {
                    $total_management_fee = $total_management_fee + $investor['invest_rtr'] * ($investor['mgmnt_fee_percentage'] / 100);
                    $total_syndication_fee = $total_syndication_fee + $investor['invest_rtr'] * ($investor['syndication_fee_percentage'] / 100);
                    $total_invest_rtr = $total_invest_rtr + $investor['invest_rtr'];
                }
                $balance_rtr = $total_invest_rtr - ($data->participantPayment->sum('participant_share'));
                $balance_managmnt_fee = $total_management_fee - $data->participantPayment->sum('mgmnt_fee');
                $balance_syndication_fee = $total_syndication_fee - $data->participantPayment->sum('syndication_fee');
                $final_part = ($balance_rtr - ($balance_managmnt_fee + $balance_syndication_fee));
            } else {
                $final_part = $data->participantPayment->sum('final_participant_share');
            }
            if ($data->s_prepaid_status == 2) {
                $multiplier = 1 * $data->syndication_fee / 100;
            } elseif ($data->s_prepaid_status == 1) {
                $multiplier = $data->factor_rate * $data->syndication_fee / 100;
            } else {
                $multiplier = 0;
            }

            return FFM::dollar($final_part * (((1 + ($data->commission) / 100) + $multiplier) / $data->factor_rate));
        })->editColumn('name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>";
        })->rawColumns(['name'])->editColumn('date_funded', function ($data) {
            return FFM::date($data->date_funded);
        })->with('total_debited', FFM::dollar($total_payment1))
            ->with('total_company', FFM::dollar($participant_share1))
            ->with('total_syndicate', FFM::dollar($participant_share1 - $management_fee1 - $syndication_fee1))
            ->with('total_mgmnt', FFM::dollar($management_fee1))->with('total_synd_fee', FFM::dollar($syndication_fee1))
            ->with('total_principal', FFM::dollar($total_pricipal))
            ->with('total_profit', FFM::dollar($total_profit))
            ->make(true);
    }

    public function lenderReport($lender_list = null, $industry = null, $merchants = null, $search_key = null)
    {
        $result = $this->role->lenderReport($lender_list, $industry, $merchants, $search_key);
        $lenders = $result['lenders'];
        $overpayments = $result['overpayments'];
        $CarryOverpayments = $result['CarryOverpayments'];
        $profit = $result['profit'];
        $sum_overpayment = array_sum($overpayments) + array_sum($CarryOverpayments);
        $sum_profit = array_sum($profit);
        $lenders_sum = clone $lenders;
        $lenders_d = clone $lenders;
        $lenders_d = $lenders_d->whereIn('merchants.sub_status_id', [4, 22]);
        $lenders_d_sum = clone $lenders_d;
        $lenders = $lenders->groupBy('merchants.lender_id');
        $lenders_d = $lenders_d->groupBy('merchants.lender_id');
        $lenders_d = $lenders_d->get();
        $lenders = $lenders->get();
        $overpayment11 = 0;
        foreach ($lenders as $key => $value) {
            foreach ($lenders_d as $key2 => $value2) {
                $lenders[$key]->default_invested = 0;
                $lenders[$key]->default_ctd_pp = 0;
                $lenders[$key]->default_ctd_p = 0;
                if ($value->lender_id == $value2->lender_id) {
                    $lenders[$key]->default_invested = $value2->default_amount - $overpayments[$value->lender_id];
                    if (isset($CarryOverpayments[$value->lender_id])) {
                        $lenders[$key]->default_invested -= $CarryOverpayments[$value->lender_id];
                    }
                    $lenders[$key]->default_ctd_pp = $value2->ctd_pp;
                    $lenders[$key]->default_ctd_p = $value2->ctd_p;
                    break;
                }
            }
        }
        $lenders_sum = ($lenders_sum->first());
        $lenders_d_sum = ($lenders_d_sum->first());
        $invested_amount_s = $lenders_sum->invested_amount;
        $default_invested_s = $lenders_d_sum->default_amount - $sum_overpayment;
        $default_ctd_pp = $lenders_d_sum->ctd_pp;
        $default_ctd_p = $lenders_d_sum->ctd_p;
        $lender_array = [];
        $total_invested_amount = 0;
        $total_ctd = 0;
        $total_amount = 0;

        return \DataTables::of($lenders)->addColumn('lender_name', function ($lenders) {
            return $lenders->lender_name;
        })->addColumn('invested_amount', function ($lenders) {
            return FFM::dollar($lenders->invested_amount);
        })->addColumn('share', function ($lenders) use ($invested_amount_s) {
            return FFM::percent($lenders->invested_amount / $invested_amount_s * 100);
        })->addColumn('default_invested', function ($merchant) use ($overpayments) {
            return FFM::dollar($merchant->default_invested);
        })->addColumn('default_ctd_pp', function ($merchant) {
            return FFM::dollar($merchant->default_ctd_pp);
        })->addColumn('default_ctd_p', function ($merchant) use ($profit) {
            if (isset($profit[$merchant->lender_id])) {
                return FFM::dollar($profit[$merchant->lender_id]);
            } else {
                return FFM::dollar(0);
            }
        })->addColumn('default_per', function ($lenders) use ($overpayments) {
            return FFM::percent(($lenders->default_invested) / $lenders->invested_amount * 100);
        })->with('Total', 'Total:')
            ->with('invested_amount_s', FFM::dollar($invested_amount_s))
            ->with('default_invested_s', FFM::dollar($default_invested_s))
            ->with('default_ctd_p', FFM::dollar($sum_profit))
            ->make(true);
    }

    public function lenderDelinquentData($lender_list = null, $industry = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->where('creator_id', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $lenders = Db::table('roles')->where('roles.name', 'lender')->join('user_has_roles', 'user_has_roles.role_id', 'roles.id')->join('users', 'users.id', 'user_has_roles.model_id')
            ->join('merchants', 'merchants.lender_id', 'users.id')->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->where('users.active_status', 1)
            ->select('users.id as lender_id', DB::raw('sum(merchant_user.amount) + sum(merchant_user.pre_paid)+ sum(merchant_user.commission_amount)  as invested_amount'),
                     DB::raw('sum(paid_participant_ishare) as ctd_pp'),
                     DB::raw('sum(paid_participant_ishare *  ( (merchant_user.amount) + (merchant_user.pre_paid)+ (merchant_user.commission_amount))/(invest_rtr - invest_rtr*merchant_user.mgmnt_fee/100 )) as ctd_p'), 'users.name as lender_name', 'users.id')
            ->join('users as users_investor', 'users_investor.id', 'merchant_user.user_id');
        if (empty($permission)) {
            $lenders = $lenders->where('users_investor.company', $userId);
        }
        if ($industry) {
            $lenders = $lenders->where('merchants.industry_id', $industry);
        }
        if ($lender_list) {
            $lenders = $lenders->whereIn('merchants.lender_id', $lender_list);
        }

        return $lenders;
    }

    public function investorMerchantoOpenList($investor)
    {
        $data = $this->merchant->investorDatatable($investor->id, ['id', 'name', 'rtr', 'id', 'date_funded', 'funded', 'commission', 'factor_rate', 'sub_status_id'])->where('open_item', 1);

        return DataTables::eloquent($data)->addColumn('complete', function ($merchant) {
            $data['total_rtr'] = DB::table('participent_payments')->where('user_id', $this->user->id)->sum('payment');

            return FFM::percent(PayCalc::completedPercent($merchant->participant_paid, $merchant->participant_rtr));
        })->addColumn('status', function ($merchant) {
            return $merchant->payStatus;
        })->addColumn('action', function ($merchant) {
            return ' <a class="btn btn-primary" href="'.route('investor::dashboard::view', ['id' => $merchant->id]).'">View</a>';
        })->addColumn('funded', function ($partpayment) {
            $funded = 0;
            foreach ($partpayment->participantPayment as $key => $value) {
                $funded = $funded + ($value['funded']);
            }

            return FFM::dollar($funded);
        })->editColumn('commission', '{{FFM::percent($commission)}}')->editColumn('pending_amount', '00')->editColumn('fund_collect_status', 'No')->editColumn('signed_addenum', 'No')->editColumn('factor_rate', '{{FFM::percent($factor_rate)}}')->removeColumn('id')->removeColumn('sub_status_id')->removeColumn('payment')->removeColumn('rtr')->make(true);
    }

    public function investorMerchantListViewExportData($investor_id = 0, $lender_id = 0, $status_id = 0, $status_arr = [])
    {
        $data = $this->merchant->investorDatatable($investor_id, ['amount'], $lender_id, $status_id, $status_arr);

        return $data['list'];
    }

    public function investorMerchantListView($investor_id = 0, $lender_id = 0, $status_id = 0, $status_arr = [])
    {
        $data = $this->merchant->investorDatatable1($investor_id, ['amount'], $lender_id, $status_id, $status_arr);
        $funded_total = $data['sum']->amount;
        $ctd_total = $data['sum']->actual_paid_participant_ishare;
        $mag_fee = $data['sum']->mgmnt_fee_amount;
        $rtr_total = $data['sum']->invest_rtr - $data['sum']->mgmnt_fee_amount;
        $commission_total = $data['sum']->commission_amount;

        return DataTables::Eloquent($data['list'])->editColumn('name', function ($data) {
            return isset($data['name']) ? $data['name'] : '';
        })->addColumn('invest_rtr', function ($data) {
            return FFM::dollar($data['invest_rtr'] - $data['mag_fee']);
        })->editColumn('sub_status_id', function ($data) {
            return isset($data['sub_status_name']) ? $data['sub_status_name'] : 0;
        })->editColumn('last_payment_date', function ($data) {
            if ($data['last_payment_date']) {
                return FFM::date($data['last_payment_date']);
            } else {
                return '';
            }
        })->editColumn('action', function ($merchant, $view_type = 'test') use ($investor_id) {
            if ($investor_id) {
                return ' <a class="btn btn-primary" href="'.route('investor::dashboard::view', ['id' => $merchant['id']]).'">View</a>';
            } else {
                return ' <a class="btn btn-primary" href="/admin/merchants/view/'.$merchant['id'].'">View</a>';
            }
        })->editColumn('date_funded', function ($data) {
            return FFM::date($data['date_funded']);
        })->editColumn('commission', function ($data) {
            return FFM::percent($data['commission']+$data['up_sell_commission_per']);
        })
        ->editColumn('paid_participant_ishare', function ($data) {
            return FFM::dollar($data['actual_paid_participant_ishare'] - $data['paid_mgmnt_fee']);
        })->editColumn('annualized_rate', function ($data) {
            $annualized_rate = $data['tot_profit'] / $data['tot_investment'] * 100;

            return FFM::percent($annualized_rate);

            return '-';
        })->editColumn('complete_per', function ($data) {
            return FFM::percent($data['complete_per']);
        })->editColumn('amount', function ($data) {
            return FFM::dollar($data['amount']);
        })->editColumn('factor_rate', function ($data) {
            return round($data['factor_rate'], 2);
        })->addIndexColumn()->with('funded_total', FFM::dollar($funded_total))->with('rtr_total', FFM::dollar($rtr_total))->with('ctd_total', FFM::dollar($ctd_total))->with('commission_total', FFM::dollar($commission_total))->make(true);
    }

    public function investorMerchantListViewold($investor_id = 0, $lender_id = 0, $status_id = 0, $status_arr = [])
    {
        $data = $this->merchant->investorDatatable($investor_id, ['amount'], $lender_id, $status_id, $status_arr);
        $funded_total = $data['sum']->amount;
        $ctd_total = $data['sum']->paid_participant_ishare;
        $rtr_total = $data['sum']->invest_rtr;
        $commission_total = $data['sum']->commission_amount;

        return DataTables::Eloquent($data['list'])->addColumn('name', function ($data) {
            return isset($data->merchant) ? $data->merchant->name : '';
        })->addColumn('merchant.id', function ($data) {
            return isset($data->merchant) ? $data->merchant->id : 0;
        })->addColumn('merchant.sub_status_id', function ($investment) {
            return isset($investment->merchant) ? $investment->merchant->payStatus : 999;
        })->addColumn('action', function ($merchant, $view_type = 'test') use ($investor_id) {
            if ($investor_id) {
                return ' <a class="btn btn-primary" href="'.route('investor::dashboard::view', ['id' => $merchant->merchant_id]).'">View</a>';
            } else {
                return ' <a class="btn btn-primary" href="/admin/merchants/view/'.$merchant->merchant_id.'">View</a>';
            }
        })->with('funded_total', FFM::dollar($funded_total))->with('rtr_total', FFM::dollar($rtr_total))->with('ctd_total', FFM::dollar($ctd_total))->with('commission_total', FFM::dollar($commission_total))->addColumn('date_funded', function ($data) {
            return isset($data->merchant) ? FFM::date($data->merchant->date_funded) : '';
        })->addColumn('merchant.commission', function ($data) {
            return isset($data->merchant) ? FFM::percent($data->merchant->commission) : '';
        })->addColumn('invest_rtr', function ($data) {
            return FFM::dollar($data->invest_rtr);
        })->addColumn('paid_participant_ishare', function ($data) {
            return FFM::dollar($data->paid_participant_ishare - $data->paid_mgmnt_fee);
        })->editColumn('merchant.annualized_rate', function ($data) {
            return FFM::percent($data->merchant->annualized_rate);
        })->editColumn('complete_per', function ($data) {
            return FFM::percent($data->merchant->complete_percentage);
        })->addColumn('amount', function ($data) {
            return FFM::dollar($data->amount);
        })->editColumn('merchant.factor_rate', function ($data) {
            return round($data->merchant->factor_rate, 2);
        })->addIndexColumn()->make(true);
    }

    public function adminMerchantListViewExportData($investor_id, $status)
    {
        $data = $this->merchant->investorDatatable1($investor_id, ['amount'], '', '', $status);
        $data = $data['list']->orderByDesc('date_funded')->get();

        return $data;
    }

    public function adminMerchantListView($investor_id, $status)
    {
        $data = $this->merchant->investorDatatable1($investor_id, ['amount'], '', '', $status);
        $funded_total = $data['sum']->amount;
        $ctd_total = $data['sum']->actual_paid_participant_ishare;
        $mag_fee = $data['sum']->mgmnt_fee_amount;
        $rtr_total = $data['sum']->invest_rtr - $data['sum']->mgmnt_fee_amount;
        $commission_total = $data['sum']->commission_amount;
        $commission_total = $data['sum']->commission_amount;

        return DataTables::of($data['list'])->editColumn('merchants.name', function ($data) {
            $merchant_name =isset($data['name']) ? $data['name'] : '';
            $merchant_id = $data['id'];
            return "<a target='_blank' href='".\URL::to('/admin/merchants/view', $merchant_id)."'>$merchant_name</a>";
        })->editColumn('invest_rtr', function ($data) {
            return FFM::dollar($data['invest_rtr'] - $data['mag_fee']);
        })->editColumn('sub_statuses.name', function ($data) {
            return isset($data['sub_status_name']) ? $data['sub_status_name'] : 0;
        })->editColumn('merchants.date_funded', function ($data) {
            if ($data['merchants.date_funded']) {
                return date('m/d/Y', strtotime($data['date_funded']));
            } else {
                return '';
            }
        })->editColumn('merchants.date_funded', function ($data) {
            $user = User::where('id', $data['creator_id'])->value('name');
            $user = ($user) ? $user : '--';
            $created_date = 'Created On '.FFM::datetime($data['created_at']).' by '.$user;

            return "<a title='".$created_date."'>".FFM::date($data['date_funded']).'</a>';
        })->editColumn('merchant_user.commission_per', function ($data) {
            return FFM::percent($data['commission']);
        })
        ->editColumn('merchant_user.up_sell_commission_per', function ($data) {
            return FFM::percent($data['up_sell_commission_per']);
        })->editColumn('paid_participant_ishare', function ($data) {
            return FFM::dollar($data['actual_paid_participant_ishare'] - $data['paid_mgmnt_fee']);
        })->editColumn('annualized_rate', function ($data) {
            $annualized_rate = 0;
            if ($data['tot_investment'] > 0) {
                $annualized_rate = $data['tot_profit'] / $data['tot_investment'] * 100;
            }

            return FFM::percent($annualized_rate);
        })->editColumn('merchants.complete_percentage', function ($data) {
            return FFM::percent($data['complete_percentage']);
        })->addColumn('merchant_user.amount', function ($data) {
            return FFM::dollar($data['amount']);
        })->addColumn('merchants.factor_rate', function ($data) {
            $factor_rate = ($data['factor_rate']) ? round($data['factor_rate'], 2) : '--';

            return $factor_rate;
        })->filterColumn('merchant_user.amount', function ($query, $keyword) {
            $query->orWhere(DB::raw('ROUND(merchant_user.amount,2)'), 'like', '%'.$keyword.'%');
        })->orderColumn('merchant_user.amount', function ($query, $order) {
            $query->orderBy('merchant_user.amount', $order);
        })->addIndexColumn()->rawColumns(['merchants.name','merchants.date_funded'])->with('funded_total', FFM::dollar($funded_total))->with('rtr_total', FFM::dollar($rtr_total))->with('ctd_total', FFM::dollar($ctd_total))->with('commission_total', FFM::dollar($commission_total))->make(true);
    }

    public function adminMerchantListViewold($investor_id, $status)
    {
        $data = $this->merchant->investorDatatable($investor_id, ['amount'], '', '', $status);
        $funded_total = $ctd_total = $rtr_total = $commission_total = 0;
        $funded_total = $data['sum']->amount;
        $ctd_total = $data['sum']->paid_participant_ishare;
        $rtr_total = $data['sum']->invest_rtr - $data['sum']->t_mag_fee;
        $commission_total = $data['sum']->commission_amount;
        $data = $data['list'];

        return DataTables::Collection($data->get())->with('funded_total', FFM::dollar($funded_total))->with('rtr_total', FFM::dollar($rtr_total))->with('ctd_total', FFM::dollar($ctd_total))->with('commission_total', FFM::dollar($commission_total))->editColumn('date_funded', function ($data) {
            return isset($data->merchant->date_funded) ? FFM::date($data->merchant->date_funded) : 0;
        })->editColumn('commission', function ($data) {
            return FFM::percent($data->commission_per);
        })->editColumn('name', function ($data) {
            return $data->merchant['name'];
        })->editColumn('invest_rtr', function ($data) {
            return FFM::dollar($data->invest_rtr - $data->mag_fee);
        })->editColumn('paid_participant_ishare', function ($data) {
            return FFM::dollar($data->paid_participant_ishare - $data->paid_mgmnt_fee);
        })->editColumn('annualized_rate', function ($data) {
            $no_of_payments = ($data->merchant['advance_type'] == 'weekly_ach') ? 52 : 255;
            $tot_profit = $data->invest_rtr - $data->mag_fee - ($data->amount + $data->commission_amount + $data->pre_paid + $data->under_writing_fee);
            $tot_investment = $data->amount + $data->commission_amount + $data->pre_paid + $data->under_writing_fee;
            $annualised_rate = ($tot_profit * $no_of_payments / $data->merchant['pmnts']) / $tot_investment * 100;

            return FFM::percent($annualised_rate);
        })->editColumn('merchant.sub_status_id', function ($data) {
            return $data->merchant['sub_statuses_name'];
        })->editColumn('amount', function ($data) {
            return FFM::dollar($data->merchant['amount']);
        })->editColumn('complete_per', function ($data) {
            return FFM::percent($data->merchant['complete_percentage']);
        })->editColumn('amount', function ($data) {
            return FFM::dollar($data->amount);
        })->editColumn('factor_rate', function ($data) {
            return round($data->merchant['factor_rate'], 2);
        })->addIndexColumn()->make(true);
    }

    public function getAllByMerchantInvestorId($merchantId, $investor_id)
    {
        $select = ['merchant_id', 'payment_date', 'payment', 'participant_share', 'mgmnt_fee', 'final_participant_share', 'code'];
        $data = $this->partPay->getAllByMerchantInvestorId($select, $merchantId, $investor_id, true);

        return $data;
    }

    public function investorMerchantDetailsView($merchantId, $investor)
    {
        if ($this->merchant->findIfBelongsToUser($merchantId, $investor->id)) {
            $select = ['merchant_id', 'payment_date', 'payment', 'participant_share', 'mgmnt_fee', 'final_participant_share', 'actual_participant_share'];
            $data = $this->partPay->getAllByMerchantInvestorId($select, $merchantId, $investor->id, true);
            $payment_total = array_sum(array_column(($data->get())->toArray(), 'payment'));
            $participant_share_total = array_sum(array_column(($data->get())->toArray(), 'actual_participant_share'));
            $mgmnt_fee_total = array_sum(array_column(($data->get())->toArray(), 'mgmnt_fee'));
            $syndication_fee_total = array_sum(array_column(($data->get())->toArray(), 'syndication_fee'));
            $final_participant_share_total = array_sum(array_column(($data->get())->toArray(), 'participant_share')) - $mgmnt_fee_total - $syndication_fee_total;

            return DataTables::eloquent($data)->editColumn('payment_date', function ($data) {
                return isset($data->payment_date) ? FFM::date($data->payment_date) : '';
            })->editColumn('transaction_type', function ($data) {
                $mode = null;
                switch ($data['mode_of_payment']) {
                    case 1:
                        $mode = 'ACH';
                        break;

                    case 0:
                        $mode = 'Manual';
                        break;

                    case 2:
                        $mode = 'Credit Card Payment';
                        break;
                
                        default:                    
                        break;
                }
                return $mode;
                
            })->editColumn('to_participant', function ($data) {
                return FFM::dollar($data->actual_participant_share - $data->syndication_fee - $data->mgmnt_fee);
            })->editColumn('participant_share_data', function ($data) {
                return FFM::dollar($data->actual_participant_share);
            })->editColumn('payment', '{{FFM::dollar($payment)}}')->editColumn('participant_share', '{{FFM::dollar($actual_participant_share)}}')->editColumn('mgmnt_fee', '{{FFM::dollar($mgmnt_fee)}}')->editColumn('final_participant_share', '{{FFM::dollar($final_participant_share)}}')->removeColumn('merchant_id')->with('payment_total', FFM::dollar($payment_total))->with('participant_share_total', FFM::dollar($participant_share_total))->with('mgmnt_fee_total', FFM::dollar($mgmnt_fee_total))->with('final_participant_share_total', FFM::dollar($final_participant_share_total))->make(true);
        }
    }

    public function adminMerchantDetailsView($merchantId, $investor)
    {
        if (1) {
            $select = ['merchant_id', 'payment_date', 'payment', 'participant_share', 'mgmnt_fee', 'final_participant_share', 'transaction_type'];
            $data = $this->partPay->getAllByMerchantId($select, $merchantId, true);
            $payment_total = array_sum(array_column(($data->get())->toArray(), 'payment'));
            $participant_share_total = array_sum(array_column(($data->get())->toArray(), 'participant_share'));
            $mgmnt_fee_total = array_sum(array_column(($data->get())->toArray(), 'mgmnt_fee'));
            $syndication_fee_total = array_sum(array_column(($data->get())->toArray(), 'syndication_fee'));
            $final_participant_share_total = array_sum(array_column(($data->get())->toArray(), 'final_participant_share'));

            return DataTables::eloquent($data)->addColumn('merchant', function ($data) {
                return isset($data->merchant->name) ? $data->merchant->name : '';
            })->editColumn('payment_date', '{{FFM::date($payment_date)}}')->editColumn('payment', '{{FFM::dollar($payment)}}')->editColumn('participant_share', '{{FFM::dollar($participant_share)}}')->editColumn('mgmnt_fee', '{{FFM::dollar($mgmnt_fee)}}')->editColumn('final_participant_share', '{{FFM::dollar($final_participant_share)}}')->removeColumn('merchant_id')->with('payment_total', FFM::dollar($payment_total))->with('mgmnt_fee_total', FFM::dollar($mgmnt_fee_total))->with('syndication_fee_total', FFM::dollar($syndication_fee_total))->with('final_participant_share_total', FFM::dollar($final_participant_share_total))->with('participant_share_total', FFM::dollar($participant_share_total))->make(true);
        }
    }

    public function getOpenItemsForAdmin($column = false)
    {
        if ($column) {
            if (! Auth::user()->hasRole('editor') && ! Auth::user()->hasRole('viewer')) {
                return [['data' => 'payment_date', 'name' => 'payment_date', 'title' => 'Payment Date'], ['data' => 'merchant', 'name' => 'merchant', 'title' => 'Merchant', 'orderable' => false, 'searchable' => false], ['data' => 'payment', 'name' => 'payment', 'title' => 'Total Payment'], ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]];
            } else {
                return [['data' => 'payment_date', 'name' => 'payment_date', 'title' => 'Payment Date'], ['data' => 'merchant', 'name' => 'merchant', 'title' => 'Merchant', 'orderable' => false, 'searchable' => false], ['data' => 'payment', 'name' => 'payment', 'title' => 'Total Payment'], ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type']];
            }
        }
        $data = $this->partPay->openItems(['id', 'payment', 'transaction_type', 'merchant_id', 'payment_date']);

        return \DataTables::eloquent($data)->addColumn('merchant', function ($data) {
            return $data->merchantName;
        })->addColumn('action', function ($data) {
            if (! Auth::user()->hasRole('editor')) {
                return Form::open(['route' => ['admin::payments::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }
        })->editColumn('payment_date', '{{FFM::date($payment_date)}}')->removeColumn('merchant_id')->editColumn('payment_date', function ($data) {
            return FFM::date($data->payment_date);
        })->filterColumn('payment_date', function ($query, $keyword) {
            $keyword = FFM::dbdate($keyword);
            $sql = 'payment_date  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->make(true);
    }

    public function getPaymentHistoryForAdmin($column = false)
    {
        if ($column) {
            return [
                ['data' => 'payment_date', 'name' => 'payment_date', 'title' => 'Payment Date'],
                ['data' => 'merchant', 'name' => 'merchant', 'title' => 'Merchant', 'orderable' => false, 'searchable' => false],
                ['data' => 'total_payment', 'name' => 'total_payment', 'title' => 'Total Payment'],
                ['data' => 'participant_share', 'name' => 'participant_share', 'title' => 'Participant Share'],
                ['data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'title' => 'MGMNT FEE'],
                ['data' => 'amount', 'name' => 'amount', 'title' => 'To Participant'],
                ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
            ];
        }
        $data = $this->partPay->openItems(['id', 'total_payment', 'participant_share', 'mgmnt_fee', 'amount', 'transaction_type', 'merchant_id', 'payment_date']);

        return \DataTables::eloquent($data)->addColumn('merchant', function ($data) {
            return $data->merchantName;
        })->addColumn('action', function ($data) {
            return Form::open(['route' => ['admin::payments::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'pay-bt btn btn-xs btn-danger']).Form::close();
        })->editColumn('payment_date', '{{FFM::date($payment_date)}}')->removeColumn('merchant_id')->make(true);
    }

    public function documents($investorId, $merchantId, $column = false)
    {
        if ($column) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'Id'],
                ['data' => 'title', 'name' => 'title', 'title' => 'Title', 'orderable' => false, 'searchable' => false],
                ['data' => 'document_type', 'name' => 'document_type', 'title' => 'Document type', 'orderable' => false, 'searchable' => false],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Upload Date'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
            ];
        }
        $documentTypes = DocumentType::pluck('name', 'id');
        $data = Document::select('id', 'created_at', 'document_type_id', 'title', 'file_name')->where('merchant_id', $merchantId)->where('investor_id', $investorId)->Orwhere('investor_id', 0);

        return \DataTables::eloquent($data)->addColumn('document_type', function ($data) use ($documentTypes) {
            return Form::select('type', $documentTypes, $data->document_type_id, ['id' => 'type_'.$data->id]);
        })->addColumn('action', function ($data) use ($merchantId) {
            return '&nbsp;&nbsp;'.'<a href="'.route('investor::documents_upload::view', ['id' => $merchantId, 'iid' => $data->id]).'" target="_blank" class="btn btn-xs btn-success">View</a>&nbsp;&nbsp;'.Form::button('update', ['class' => 'btn btn-xs btn-info updatedoc', 'data-url' => route('investor::dashboard::update-docs', ['id' => $merchantId, 'docid' => $data->id]), 'data-id' => $data->id]);
        })->editColumn('created_at', function ($data) {
            return $data->created_at->toFormattedDateString();
        })->editColumn('title', function ($data) {
            return Form::text('title', $data->title, ['id' => "title_{$data->id}"]);
        })->removeColumn('document_type_id')->make(true);
    }

    public function adminDocumentsView($investorId, $merchantId, $column = false)
    {
        if ($column) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'Id'],
                ['data' => 'title', 'name' => 'title', 'title' => 'Title', 'orderable' => false, 'searchable' => false],
                ['data' => 'document_type', 'name' => 'document_type', 'title' => 'Document type', 'orderable' => false, 'searchable' => false],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Upload Date'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
            ];
        }
        $documentTypes = DocumentType::pluck('name', 'id');
        $data = Document::select('id', 'created_at', 'document_type_id', 'title', 'file_name')->where('merchant_id', $merchantId)->where('investor_id', $investorId);

        return \DataTables::eloquent($data)->addColumn('document_type', function ($data) use ($documentTypes) {
            return Form::select('type', $documentTypes, $data->document_type_id, ['id' => 'type_'.$data->id, 'class' => 'form-control']);
        })->addColumn('action', function ($data) use ($merchantId, $investorId) {
            $return = '';
            if (Permissions::isAllow('Merchants', 'Delete')) {
                $return .= Form::button('Delete', [
                    'class' => 'btn btn-xs btn-danger deletedoc',
                    'data-url' => route('admin::merchant_investor::document::delete-docs', ['mid' => $merchantId, 'iid' => $investorId, 'docid' => $data->id]), ]);
            }
            if (Permissions::isAllow('Merchants', 'Edit')) {
                $return .= '<a href="'.route('admin::merchant_investor::document::view', ['mid' => $merchantId, 'iid' => $investorId, 'docid' => $data->id]).'" target="_blank" class="btn btn-xs btn-success">View</a>&nbsp;&nbsp;'.Form::button('update', ['class' => 'btn btn-xs btn-info updatedoc', 'data-url' => route('admin::merchant_investor::document::update-docs', ['mid' => $merchantId, 'iid' => $investorId, 'docid' => $data->id]), 'data-id' => $data->id]);
            }

            return $return;
        })->editColumn('created_at', function ($data) {
            return ($data->created_at != '') ? Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format(\FFM::defaultDateFormat('db')) : '';
        })->editColumn('title', function ($data) {
            return Form::text('title', $data->title, ['id' => "title_{$data->id}", 'class' => 'form-control']);
        })->removeColumn('document_type_id')->make(true);
    }

    public function investorDocumentsView($investorId, $column = false)
    {
        if ($column) {
            return [['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'className' => 'details-control', 'orderable' => false, 'searchable' => false, 'defaultContent' => ''], ['data' => 'title', 'name' => 'title', 'title' => 'Title', 'orderable' => false], ['data' => 'document_type', 'name' => 'document_type', 'title' => 'Document type', 'orderable' => false], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Upload Date'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]];
        }
        $documentTypes = DocumentType::pluck('name', 'id');
        $data = InvestorDocuments::leftjoin('document_types','document_types.id','document_type_id')->select('investor_documents.id as id', 'investor_documents.created_at as created_at', 'document_type_id', 'title', 'file_name', 'creator_id','document_types.name')->where('investor_id', $investorId);
        return \DataTables::eloquent($data)->addColumn('document_type', function ($data) use ($documentTypes) {
            $return = Form::select('type', $documentTypes, $data->document_type_id, ['id' => 'type_'.$data->id, 'class' => 'form-control', 'onchange' => 'myfunction('.$data->id.');']).'&nbsp;&nbsp';
            $return .= Form::text('other_type', '', ['id' => 'other_type_'.$data->id, 'class' => 'form-control', 'style' => 'display:none;', 'placeholder' => 'Enter other type here', 'maxlength' => 40]);

            return $return;
        })->addColumn('action', function ($data) use ($investorId) {
            $return = '';
            if (Permissions::isAllow('Investors', 'Edit')) {
                $return .= '<a href="'.route('admin::merchant_investor::documents_upload::view', ['iid' => $investorId, 'docid' => $data->id]).'" target="_blank" class="btn btn-xs btn-success">View</a>&nbsp;&nbsp;'.Form::button('update', ['class' => 'btn btn-xs btn-info updatedoc', 'data-url' => route('admin::merchant_investor::documents_upload::update-idocs', ['iid' => $investorId, 'docid' => $data->id]), 'data-id' => $data->id]).'&nbsp;&nbsp';
            }
            if (Permissions::isAllow('Investors', 'Delete')) {
                $return .= Form::button('Delete', ['class' => 'btn btn-xs btn-danger deletedoc', 'data-url' => route('admin::merchant_investor::documents_upload::delete-docs', ['iid' => $investorId, 'docid' => $data->id])]);
            }

            return $return;
        })->editColumn('created_at', function ($data) {
            $user = User::where('id', $data->creator_id)->value('name');
            $user = ($user) ? $user : '--';
            $created_date = 'Created On '.\FFM::datetime($data->created_at).' by '.$user;

            return ($data->created_at != '') ? "<a title='$created_date'>".\FFM::datetimetodate($data->created_at).'</a>' : '';
        })->editColumn('title', function ($data) {
            return Form::text('title', $data->title, ['id' => "title_{$data->id}", 'class' => 'form-control']);
        })->filterColumn('document_type', function ($query, $keyword) {
            $sql = 'document_types.name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->rawColumns(['document_type', 'action', 'created_at'])->removeColumn('document_type_id')->addIndexColumn()->make(true);
    }

    public function adminMarketPlaceDocumentsView($merchantId, $column = false)
    {
        if ($column) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => '#', 'searchable' => false],
                ['data' => 'title', 'name' => 'title', 'title' => 'Title', 'orderable' => false],
                ['data' => 'document_type', 'name' => 'document_type', 'title' => 'Document type', 'orderable' => false],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Upload Date'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
            ];
        }
        $documentTypes = DocumentType::pluck('name', 'id');
        $data = Document::select('id', 'created_at', 'document_type_id', 'title', 'file_name', 'creator_id')->where('merchant_id', $merchantId);

        return \DataTables::eloquent($data)->addColumn('document_type', function ($data) use ($documentTypes) {
            $return = Form::select('type', $documentTypes, $data->document_type_id, ['id' => 'type_'.$data->id, 'class' => 'form-control', 'onchange' => 'myfunction('.$data->id.');']).'&nbsp;&nbsp';
            $return .= Form::text('other_type', '', ['id' => 'other_type_'.$data->id, 'class' => 'form-control', 'style' => 'display:none;', 'placeholder' => 'Enter other type here', 'maxlength' => 40]);

            return $return;
        })->editColumn('file_name', function ($data) {
            return $data->file_name;
        })->addColumn('action', function ($data) use ($merchantId) {
            return Form::button('Delete', [
                'class' => 'btn btn-xs btn-danger deletedoc',
                'data-url' => route('admin::merchants::document::delete-docs', ['mid' => $merchantId, 'docid' => $data->id]),
            ]).'&nbsp;&nbsp;'.
            '<a href="'.route('admin::merchants::document::view', ['mid' => $merchantId, 'docid' => $data->id]).'" target="_blank" class="btn btn-xs btn-success">View</a>&nbsp;&nbsp;'.
            Form::button('update', ['class' => 'btn btn-xs btn-info updatedoc', 'data-url' => route('admin::merchants::document::update-docs', ['mid' => $merchantId, 'docid' => $data->id]), 'data-id' => $data->id]);
        })->editColumn('created_at', function ($data) {
            $user = User::where('id', $data->creator_id)->value('name');
            $user = ($user) ? $user : '--';
            $created_date = 'Created On '.\FFM::datetime($data->created_at).' by '.$user;

            return ($data->created_at != '') ? "<a title='$created_date'>".FFM::datetimetodate($data->created_at).'</a>' : '';
        })->editColumn('title', function ($data) {
            return Form::text('title', $data->title, ['id' => "title_{$data->id}", 'class' => 'form-control']);
        })->removeColumn('document_type_id')->rawColumns(['document_type', 'action', 'created_at'])->make(true);
    }

    public function marketPlaceDocumentsView($merchantId, $column = false)
    {
        if ($column) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'Id'],
                ['data' => 'title', 'name' => 'title', 'title' => 'Title', 'orderable' => false, 'searchable' => false],
                ['data' => 'document_type', 'name' => 'document_type', 'title' => 'Document type', 'orderable' => false, 'searchable' => false],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Upload Date'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
            ];
        }
        $documentTypes = DocumentType::pluck('name', 'id')->toArray();
        $data = Document::select('id', 'created_at', 'document_type_id', 'title', 'file_name')->where('merchant_id', $merchantId)->where('global_status', 1);

        return \DataTables::eloquent($data)->addColumn('document_type', function ($data) use ($documentTypes) {
            return isset($documentTypes[$data->document_type_id]) ? $documentTypes[$data->document_type_id] : '';
        })->addColumn('action', function ($data) use ($merchantId) {
            $url = url(Storage::disk('s3')->temporaryUrl($data->file_name,Carbon::now()->addMinutes(2)));
            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
            if ($ext == 'pdf' || in_array(strtolower($ext), FFM::viewableImageExtensions())) {
                return '<a data-toggle="modal" data-target="#fileModal" data-title="'.$data->title.'" data-ext="'.$ext.'" data-url="'.$url.'" class="btn btn-xs btn-success">View</a>&nbsp;&nbsp;';
            } elseif (in_array($ext, FFM::viewableDocExtensionsGoogle())) {
                $url = "https://docs.google.com/viewer?url=$url&embedded=true&pid=explorer&efh=false&a=v&chrome=false";

                return '<a data-toggle="modal" data-target="#'.$data->id.'Modal" class="btn btn-xs btn-success">View</a>&nbsp;&nbsp;
                            <div class="modal fade bd-example-modal-xl" id="'.$data->id.'Modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-capitalize" id="fileModalLabel">'.$data->title.'</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                                <object  type="application/pdf" width="100%" height="500px"  data="'.$url.'"></object>
                                        </div>
                                    </div>
                                </div>
                            </div>';
            }

            return '<a href="'.route('investor::marketplace::document::view', ['mid' => $merchantId, 'docid' => $data->id]).'" target="_blank" class="btn btn-xs btn-success">View</a>&nbsp;&nbsp;';
        })->editColumn('created_at', function ($data) {
            return $data->created_at->toFormattedDateString();
        })->removeColumn('document_type_id')->make(true);
    }

    public function investorTransactions($sdate = null, $edate = null, $investors = null, $transaction_type = null, $category = null, $company = null, $investor_type = null, $date_type = null, $stime = null, $etime = null, $column = false, $search_key = null, $status = null, $merchant_id = null,$velocity_owned = false)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if ($column == true) {
            if(Auth::user()->hasRole(['company'])){
                return [
                    ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'searchable' => false, 'orderable' => false],
                    
                    ['data' => 'name', 'name' => 'investor_ach_transaction_views.name', 'title' => 'Investor', 'orderable' => false],
                    ['data' => 'transaction_category', 'name' => 'transaction_category', 'title' => 'Transaction Category'],
                    ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
                    ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type'],
                    ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
                    ['data' => 'date', 'name' => 'date', 'title' => 'Investment Date '],
                    ['data' => 'maturity_date', 'name' => 'maturity_date', 'title' => 'Maturity date'],
                    ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
                    ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Last Updated At']
                ];
            }else{
            return [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'searchable' => false, 'orderable' => false],
                ['data' => 'delete_check', 'name' => 'delete_check', 'title' => '<input type="checkbox" id="checkAllButtont">', 'orderable' => false, 'searchable' => false],
                ['data' => 'name', 'name' => 'investor_ach_transaction_views.name', 'title' => 'Investor', 'orderable' => false],
                ['data' => 'transaction_category', 'name' => 'transaction_category', 'title' => 'Transaction Category'],
                ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
                ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type'],
                ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
                ['data' => 'date', 'name' => 'date', 'title' => 'Investment Date '],
                ['data' => 'maturity_date', 'name' => 'maturity_date', 'title' => 'Maturity date'],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
                ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Last Updated At'],
                ['data' => 'action', 'name' => 'action', 'orderable' => false, 'title' => 'Action', 'searchable' => false, 'visiblity' => false],
            ];
        }
        } else {
            $transactions = \MTB::investorTransactionQuery($sdate, $edate, $investors, $transaction_type, $category, $company, $investor_type, $date_type, $stime, $etime, null, $search_key, $status,$merchant_id,$velocity_owned);
            $total = $transactions->sum('amount');
            session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

            return \DataTables::of($transactions)->addColumn('transaction_category', function ($data) {
                $category = $data->transaction_category ? $data->TransactionCategoryName : '';

                return $category;
            })->editColumn('amount', function ($data) {
                return \FFM::dollar($data->amount);
            })->editColumn('delete_check', function ($data) {
                if(\Permissions::isAllow('Transaction Report','Delete')){
                    $checkbox_id = $data->id;
                    if ($data->status == 2 ) {
                        $checkbox_id = "Pending";
                    }
                    return '<input type="checkbox" id="'.$checkbox_id.'" class="delete_transactions" value="'.$checkbox_id.'" onclick="uncheckMainTransaction();">';
                } else {
                    return '';
                }
                
            })->addColumn('updated_at', function ($data) {

                return $data->updated_at ? \FFM::datetime($data->updated_at) : '';
            })->editColumn('created_at', function ($data) {
                $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

                return $data->created_at ? "<a title='$created_date'>".\FFM::datetime($data->created_at).'</a>' : '';
            })->editColumn('date', function ($data) {
                $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

                return "<a title='$created_date'>".\FFM::date($data->date).'</a>';
            })->editColumn('maturity_date', function ($data) {
                $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

                return "<a title='$created_date'>".\FFM::date($data->maturity_date).'</a>';
            })->editColumn('investor_ach_transaction_views.name', function ($data) {
                return "<a target='blank' href='".\URL::to('/admin/investors/portfolio', $data->investor_id)."'>$data->name</a>";
            })->editColumn('transaction_type', function ($data) {
                return $data->TransactionTypeName;
            })->editColumn('status', function ($data) {
                return $data->StatusName;
            })
            ->addColumn('action', function ($data) {

                $href=route('admin::investors::transaction::edit', [ 'id' => $data->investor_id, 'tid' => $data->id]);
 
                $return = "<a href='".$href."' class='btn btn-xs btn-primary invtr-bt'>Edit</a><br/>";
 
                return $return;
 
             })->filterColumn('date', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(date,'%m-%d-%Y') like ?", ["%$keyword%"]);
            })->filterColumn('maturity_date', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(maturity_date,'%m-%d-%Y') like ?", ["%$keyword%"]);
            })->filterColumn('amount', function ($query, $keyword) {
                $sql = 'amount like ?';
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })->filterColumn('name', function ($query, $keyword) {
                $sql = 'name like ?';
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })->filterColumn('transaction_type', function ($query, $keyword) {
                if (lcfirst($keyword) == 'debit') {
                    $keyword = 1;
                } elseif (lcfirst($keyword) == 'credit') {
                    $keyword = 2;
                }
                $sql = 'transaction_type like ?';
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })->rawColumns(['name', 'created_at', 'updated_at', 'date', 'maturity_date','delete_check','action'])->with('Total', 'Total:')->with('total', FFM::dollar($total))->addIndexColumn()->make(true);
        }
    }

    public function investorTransactionQuery($sdate = null, $edate = null, $investors = null, $transaction_type = null, $transaction_category = null, $company = null, $investor_type = null, $date_type = null, $stime = null, $etime = null, $column = false, $search_key = null, $status = null, $merchant = null,$velocity_owned = false)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $transactions = InvestorAchTransactionView::select('investor_ach_transaction_views.*');
        if ($search_key != null) {
            $transactions = $transactions->where(function ($query) use ($search_key) {
                $query->Where('investor_ach_transaction_views.name', 'like', '%'.$search_key.'%');
                $query->orWhere('amount', 'like', '%'.$search_key.'%');
            });
        }
        $transactions = $transactions->leftjoin('users', 'users.id', 'investor_id');
        if($velocity_owned){
        $transactions = $transactions->where('velocity_owned', 1);
        }
        if($merchant){
            $transactions = $transactions->where('merchant_id', $merchant);
        }
        if ($transaction_type) {
            $transactions = $transactions->where('transaction_type', $transaction_type);
        }
        if ($investor_type != null) {
            $transactions = $transactions->whereIn('investor_ach_transaction_views.investor_type', $investor_type);
        }
        if ($status != null) {
            $transactions = $transactions->where('status', $status);
        }
        if ($date_type == 'true') {
            if ($stime != 0) {
                $sdate = ($sdate) ? $sdate.' '.$stime.':00' : null;
            }
            if ($etime != 0) {
                $edate = ($edate) ? $edate.' '.$etime.':59' : null;
            }
        }
        $table_field = ($date_type == 'true') ? 'investor_ach_transaction_views.created_at' : 'date';
        if ($sdate != null) {
            $transactions = $transactions->where($table_field, '>=', $sdate);
        }
        if ($edate != null) {
            $transactions = $transactions->where($table_field, '<=', $edate);
        }
        if ($investors != null) {
            $transactions = $transactions->whereIn('investor_id', $investors);
        }
        if ($company) {
            $transactions = $transactions->where('investor_ach_transaction_views.company', $company);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $transactions = $transactions->where('investor_ach_transaction_views.company', $userId);
            } else {
                $transactions = $transactions->where('creator_id', $userId);
            }
        }
        $transactions = $transactions->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        });
        if (! empty($transaction_category)) {
            $transactions = $transactions->whereIn('transaction_category', $transaction_category);
        }

        return $transactions;
    }

    public function investorAssignmentReport($startDate = null, $endDate = null, $investors = null, $merchants = null, $column = false)
    {
        if ($column == true) {
            return [
                ['className' => 'details-control', 'data' => 'id', 'defaultContent' => '', 'title' => '#'],
                ['orderable' => false, 'data' => 'investor_id', 'name' => 'investor_id', 'defaultContent' => '', 'title' => 'Investor'],
                ['data' => 'merchant_id', 'name' => 'merchant_id', 'title' => 'Merchant'],
                ['orderable' => false, 'data' => 'participant_amount', 'name' => 'participant_amount', 'title' => 'Participant Amount'],
                ['data' => 'liquidity', 'name' => 'liquidity', 'title' => 'Liquidity'],
                ['orderable' => false, 'data' => 'date', 'name' => 'date', 'defaultContent' => '', 'title' => 'Date'],
            ];
        } else {
            $data = $this->merchant->searchForInvestorAssignmentReport($startDate, $endDate, $investors, $merchants);
            $total_amount = 0;
            $commission_total = 0;
            $paid_syndication = 0;
            $sum = $data->get([DB::raw('SUM(merchant_user.amount) as amount'), DB::raw('SUM(merchant_user.commission_amount) as commission_amount'),DB::raw('SUM(merchant_user.under_writing_fee) as under_writing_fee'),DB::raw('SUM(merchant_user.up_sell_commission) as up_sell_commission'), DB::raw('SUM(merchant_user.pre_paid) as pre_paid')])->first()->toArray();
            $total_amount = $sum['amount'];
            $paid_syndication = $sum['pre_paid'];
            $commission_total = $sum['commission_amount'];
            $up_sell_commission = $sum['up_sell_commission'];
            $under_writing_fee = $sum['under_writing_fee'];
            $total_amount = $total_amount + $commission_total + $paid_syndication+$up_sell_commission+$under_writing_fee;
            session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

            return \DataTables::of($data)->editColumn('investor_id', function ($data) {
                $user = User::find($data->user_id);
                return isset($user) ? $user->name : '';
            })->addColumn('merchant_id', function ($data) {
                return isset($data->merchant->name) ? $data->merchant->name : '';
            })->addColumn('participant_amount', function ($data) {
                return FFM::dollar(($data->amount + $data->pre_paid + $data->commission_amount + $data->under_writing_fee+$data->up_sell_commission));
            })->addColumn('date', function ($data) {
                $created_date = 'Assigned On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

                return "<a title='$created_date'>".FFM::datetimetodate($data->created_at).'</a>';
            })->addColumn('liquidity', function ($data) {
                $liquidity = UserDetails::where('user_id', '=', $data->user_id)->value('liquidity');

                return isset($liquidity) ? FFM::dollar($liquidity) : 0;
            })->with('Total', 'Total:')->with('participant_amount', FFM::dollar($total_amount))->addIndexColumn()->rawColumns(['date'])->make(true);
        }
    }

    public function liquidityReport($startDate = null, $endDate = null, $subadmin = null, $active = null, $company = null,$velocity_owned = false, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['orderable' => false, 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false],
                ['data' => 'users.name', 'name' => 'users.name', 'title' => 'Investor'],
                ['data' => 'rtr_balance', 'name' => 'rtr_balance', 'title' => 'RTR Balance', 'orderable' => false],
                ['data' => 'ctd', 'name' => 'ctd', 'title' => 'Ctd', 'orderable' => false],
                ['data' => 'credits', 'name' => 'credits', 'title' => 'Credits', 'orderable' => false],
                ['data' => 'commission_amount', 'name' => 'commission_amount', 'title' => 'Commission', 'orderable' => false],
                ['data' => 'total_funded', 'name' => 'total_funded', 'title' => 'Funded Amount', 'orderable' => false],
                ['data' => 'pre_paid_amount', 'name' => 'pre_paid_amount', 'title' => 'Syndication Fee', 'orderable' => false],
                ['data' => 'liquidity', 'name' => 'liquidity', 'title' => 'Liquidity', 'orderable' => false],
                ['data' => 'under_writing_fee', 'name' => 'under_writing_fee', 'title' => 'Underwriting Fee', 'orderable' => false],
            ];
        } else {
            $data = $this->getAllInvestorLiquidity($startDate, $endDate, $subadmin, $active, $company,$velocity_owned);

            return $data;
        }
    }

    public function lenderReportOnProgress()
    {
        try {
            $lenders = $this->user1->lenderReport();

            return \DataTables::of($lender_array)->addColumn('lender_name', function ($lender_array) {
                return $lender_array['lender_name'];
            })->addColumn('invested_amount', function ($lender_array) {
                return FFM::dollar($lender_array['invested_amount']);
            })->addColumn('share', function ($lender_array) {
                return FFM::percent($lender_array['share']);
            })->addColumn('ctd', function ($lender_array) {
                return FFM::dollar($lender_array['ctd']);
            })->with('total_invested', FFM::dollar($total_amount))->with('total_ctd', FFM::dollar($total_ctd))->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function totalPortfolioEarnings($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'searchable' => false, 'orderable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
                ['data' => 'credit_amount', 'name' => 'credit_amount', 'title' => 'Credited Amount'],
                ['data' => 'total_portfolio_earnings', 'name' => 'total_portfolio_earnings', 'title' => 'Total Portfolio Earnings'],
                ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'],
                ['data' => 'distributions', 'name' => 'distributions', 'title' => 'Distributions'],
                ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value'],
            ];
        } else {
            $userId = Auth::user()->id;
            $data = $this->user1->totalPortfolioEarnings($investors);
            $total_portfolio = $total_credit_amount = $total_bills = $total_distributions = $total_portfolio_earning = $total_IRR = $liquidity = $total_amount = 0;
            $array = $data->toArray();
            $inv_arr = array_unique(array_column($data->toArray(), 'id'));
            $portfolio_difference = FFM::total_portfolio_difference($inv_arr);
            $liquidity = array_sum(array_map(function ($var) {
                return $var['liquidity'];
            }, $array));
            $default_pay_rtr = array_sum(array_map(function ($var) {
                return $var['default_pay_rtr'];
            }, $array));
            $total_credit_amount = array_sum(array_map(function ($var) {
                return $var['credit_amount'];
            }, $array));
            $fees = array_sum(array_map(function ($var) {
                return $var['fees'];
            }, $array));
            $rtr = $fees + $default_pay_rtr;
            $ctd = array_sum(array_map(function ($var) {
                return $var['ctd'];
            }, $array));
            $total_portfolio_value = ($rtr + $liquidity) - $ctd;
            $total_bills = array_sum(array_map(function ($var) {
                return $var['bills'];
            }, $array));
            $total_distributions = array_sum(array_map(function ($var) {
                return $var['distributions'];
            }, $array));
            $portfolio_earning = ($total_portfolio - $total_bills - $total_distributions);
            $total_portfolio_earning = ($total_portfolio_value + $total_bills - $total_distributions);
            $credited_amount = array_sum(array_map(function ($var) {
                return $var['credit_amount'];
            }, $array));
            $total_IRR = ! empty($credited_amount) ? ((($portfolio_earning - $credited_amount) / ($credited_amount)) * 100) : 0;

            return \DataTables::of($data)->editColumn('name', function ($data) {
                return "<a href='/admin/investors/portfolio/$data->id'>$data->name</a>";
            })->addColumn('bills', function ($data) {
                return FFM::dollar($data->bills);
            })->addColumn('distributions', function ($data) {
                return FFM::dollar($data->distributions);
            })->addColumn('credit_amount', function ($data) {
                return FFM::dollar($data->credit_amount);
            })->addColumn('total_portfolio_earnings', function ($data) {
                $total_amount = 0;
                $fees = $data->fees;
                $rtr = $fees + $data->default_pay_rtr;
                $ctd = $data->ctd;
                $portfolio_value = ($rtr + $data->liquidity) - $ctd;
                $portfolio_difference = FFM::portfolio_difference($data->id);
                $portfolio_earning = ($portfolio_value + $data->bills - $data->distributions);

                return FFM::dollar($portfolio_earning);
            })->addColumn('portfolio_value', function ($data) {
                $total_amount = 0;
                $fees = $data->fees;
                $rtr = $fees + $data->default_pay_rtr;
                $ctd = $data->ctd;
                $portfolio_value = ($rtr + $data->liquidity) - $ctd;

                return FFM::dollar($portfolio_value);
            })->rawColumns(['name'])
                ->with('Total', 'Total:')
                ->with('total_credit_amount', \FFM::dollar($total_credit_amount))
                ->with('total_portfolio', \FFM::dollar($total_portfolio_value))
                ->with('total_bills', \FFM::dollar($total_bills))
                ->with('total_distributions', \FFM::dollar($total_distributions))
                ->with('total_portfolio_earning', \FFM::dollar($total_portfolio_earning))
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function totalPortfolioEarnings_previous_copy($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'Sl No', 'orderable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
                ['data' => 'credit_amount', 'name' => 'credit_amount', 'title' => 'Credited Amount'],
                ['data' => 'total_portfolio_earnings', 'name' => 'total_portfolio_earnings', 'title' => 'Total Portfolio Earnings'],
                ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'],
                ['data' => 'distributions', 'name' => 'distributions', 'title' => 'Distributions'],
                ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value'],
            ];
        } else {
            $userId = Auth::user()->id;
            $data = $this->user1->totalPortfolioEarnings_previous_copy($investors);
            $total_portfolio = $total_credit_amount = $total_bills = $total_distributions = $total_portfolio_earning = $total_IRR = $liquidity = $total_amount = 0;
            $array = $data->toArray();
            $liquidity = array_sum(array_map(function ($var) {
                return $var['liquidity'];
            }, $array));
            $default_pay_rtr = array_sum(array_map(function ($var) {
                return $var['default_pay_rtr'];
            }, $array));
            $total_credit_amount = array_sum(array_map(function ($var) {
                return $var['credit_amount'];
            }, $array));
            $fees = array_sum(array_map(function ($var) {
                return $var['fees'];
            }, $array));
            $rtr = $fees + $default_pay_rtr;
            $ctd = array_sum(array_map(function ($var) {
                return $var['ctd'];
            }, $array));
            $total_portfolio = ($rtr + $liquidity) - $ctd;
            $total_bills = array_sum(array_map(function ($var) {
                return $var['bills'];
            }, $array));
            $total_distributions = array_sum(array_map(function ($var) {
                return $var['distributions'];
            }, $array));
            $portfolio_earning = ($total_portfolio - $total_bills - $total_distributions);
            $total_portfolio_earning = $total_portfolio_earning + ($total_portfolio - $total_bills - $total_distributions);
            $credited_amount = array_sum(array_map(function ($var) {
                return $var['credit_amount'];
            }, $array));
            $total_IRR = ! empty($credited_amount) ? ((($portfolio_earning - $credited_amount) / ($credited_amount)) * 100) : 0;

            return \DataTables::of($data)->editColumn('name', function ($data) {
                return "<a href='/admin/investors/portfolio/$data->id'>$data->name</a>";
            })->addColumn('bills', function ($data) {
                return FFM::dollar(-$data->bills);
            })->addColumn('distributions', function ($data) {
                return FFM::dollar($data->distributions);
            })->addColumn('credit_amount', function ($data) {
                return FFM::dollar($data->credit_amount);
            })->addColumn('portfolio_value', function ($data) {
                $total_amount = 0;
                $fees = $data->fees;
                $rtr = $fees + $data->default_pay_rtr;
                $ctd = $data->ctd;
                $portfolio_value = ($rtr + $data->liquidity) - $ctd;
                $portfolio_earning = ($portfolio_value + $data->bills - $data->distributions);

                return FFM::dollar($portfolio_earning);
            })->addColumn('total_portfolio_earnings', function ($data) {
                return 'hi';
                $total_amount = 0;
                $fees = $data->fees;
                $rtr = $fees + $data->default_pay_rtr;
                $ctd = $data->ctd;
                $portfolio_value = ($rtr + $data->liquidity) - $ctd;

                return FFM::dollar($portfolio_value);
            })->addColumn('IRR', function ($data) {
                $total_amount = 0;
                $fees = $data->fees;
                $rtr = $fees + $data->default_pay_rtr;
                $ctd = $data->tpaid_participant_ishare - $data->tpaid_syndication_fee - $data->tpaid_mgmnt_fee;
                $ctd = $data->ctd;
                $portfolio_value = ($rtr + $data->liquidity) - $ctd;
                $portfolio_earning = ($portfolio_value + $data->bills - $data->distributions);
                $IRR = ! empty($data->credit_amount) ? ((($portfolio_earning - $data->credit_amount) / ($data->credit_amount)) * 100) : 0;

                return FFM::percent($IRR);
            })->rawColumns(['name'])->make(true);
        }
    }

    public function equityInvestorReport($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'searchable' => false, 'orderable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
                ['data' => 'credits', 'name' => 'credits', 'title' => 'Credit'],
                ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value'],
                ['data' => 'velocity_profit', 'name' => 'velocity_profit', 'title' => 'Velocity Profit'],
                ['data' => 'investor_profit', 'name' => 'investor_profit', 'title' => 'Investor Profit'],
            ];
        } else {
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            $data = $this->user1->equityInvestorReport($investors);
            $total_credits = $total_velocity_profit = $total_credit_amount = $total_investor_porfit = $total_portfolio = $total_amount = 0;
            $array = $data->toArray();
            foreach ($array as $dt) {
                $fees = $dt['fees'];
                $rtr = $fees + $dt['default_pay_rtr'];
                $ctd = $dt['ctd'];
                if (((($rtr - $ctd + $dt['liquidity']) - $dt['credit_amount']) * .5) > 0) {
                    $total_investor_porfit = $total_investor_porfit + ((($rtr - $ctd + $dt['liquidity']) - $dt['credit_amount']) * .5);
                }
            }
            $liquidity = array_sum(array_map(function ($var) {
                return $var['liquidity'];
            }, $array));
            $default_pay_rtr = array_sum(array_map(function ($var) {
                return $var['default_pay_rtr'];
            }, $array));
            $fees = array_sum(array_map(function ($var) {
                return $var['fees'];
            }, $array));
            $rtr = $fees + $default_pay_rtr;
            $ctd = array_sum(array_map(function ($var) {
                return $var['ctd'];
            }, $array));
            $total_portfolio = ($rtr + $liquidity) - $ctd;
            $total_credit_amount = array_sum(array_map(function ($var) {
                return $var['credit_amount'];
            }, $array));
            session_set('all_users', $users = User::select('id', 'name')->get()->getDictionary());

            return \DataTables::of($data)->addColumn('name', function ($data) {
                return '<a href="'.route('admin::investors::edit', ['id' => $data->investor_id]).'">'.get_user_name_with_session($data['investor_id']).'</a>';
            })->addColumn('credits', function ($data) {
                return FFM::dollar($data->credit_amount);
            })->rawColumns(['name'])->addColumn('portfolio_value', function ($data) {
                $total_amount = 0;
                $fees = $data->fees;
                $rtr = $fees + $data->default_pay_rtr;
                $ctd = $data->ctd;
                $portfolio_value = ($rtr + $data->liquidity) - $ctd;

                return FFM::dollar($portfolio_value);
            })->addColumn('velocity_profit', function ($data) {
                $total_amount = 0;
                $fees = $data->fees;
                $rtr = $fees + $data->default_pay_rtr;
                $ctd = $data->ctd;
                $velocity_profit = (($rtr - $ctd + $data->liquidity) - $data->credit_amount) * .5;
                $velocity_profit = $velocity_profit < 0 ? 0 : $velocity_profit;

                return FFM::dollar($velocity_profit);
            })->addColumn('investor_profit', function ($data) {
                $total_amount = 0;
                $fees = $data->fees;
                $rtr = $fees + $data->default_pay_rtr;
                $ctd = $data->ctd;
                $velocity_profit = (($rtr - $ctd + $data->liquidity) - $data->credit_amount) * .5;
                $velocity_profit = $velocity_profit < 0 ? 0 : $velocity_profit;

                return FFM::dollar($velocity_profit);
            })->with('Total', 'Total:')->with('total_credit_amount', \FFM::dollar($total_credit_amount))->with('total_portfolio_value', \FFM::dollar($total_portfolio))->with('total_velocity_profit', \FFM::dollar($total_investor_porfit))->with('total_investor_porfit', \FFM::dollar($total_investor_porfit))->addIndexColumn()->make(true);
        }
    }

    public function totalPortfolioEarningsOriginal($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [['data' => 'id', 'name' => 'id', 'title' => 'Sl No', 'orderable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Investor'], ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value'], ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'], ['data' => 'distributions', 'name' => 'distributions', 'title' => 'Distributions'], ['data' => 'total_portfolio_earnings', 'name' => 'total_portfolio_earnings', 'title' => 'Total Portfolio Earnings'], ['data' => 'IRR', 'name' => 'IRR', 'title' => 'IRR']];
        } else {
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            $data = $this->user1->totalPortfolioEarnings($investors);
            $rate = Settings::value('rate');
            $total_portfolio = $total_bills = $total_distributions = $total_portfolio_earning = $total_IRR = 0;
            foreach ($data->get() as $value) {
                $rtr = $ctd = $total_amount = $fees = 0;
                $liquidity = $value->userDetails->liquidity;
                $default_pay_rtr1 = array_sum(array_column($value->participantPayment->toArray(), 'participant_share'));
                $mgmnt_fee = array_sum(array_column($value->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($value->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                $invest_rtr = array_sum(array_column($value->investmentData2->toArray(), 'invest_rtr'));
                $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
                $total_amount = $total_amount + $total_invest_rtr;
                $rtr = $total_amount - $fees + $default_pay_rtr1;
                $mgt_fee = array_sum(array_column($value->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($value->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;
                $total_portfolio = $total_portfolio + $portfolio_value;
                $bills = array_sum(array_column($value->investorTransactions1->toArray(), 'amount'));
                $distributions = array_sum(array_column($value->investorTransactions2->toArray(), 'amount'));
                $total_bills = $total_bills + $bills;
                $total_distributions = $total_distributions + $distributions;
                $total_portfolio_earning = $total_portfolio_earning + ($portfolio_value - $bills - $distributions);
                $credited_amount = array_sum(array_column($value->investorTransactions->toArray(), 'amount'));
            }

            return \DataTables::eloquent($data)->addColumn('name', function ($data) {
                return $data->name;
            })->addColumn('portfolio_value', function ($data) use ($rate) {
                $ctd = $total_amount = $fees = $rtr = $ctd2 = 0;
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
                $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
                $total_amount = $total_amount + $total_invest_rtr;
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;

                return FFM::dollar($portfolio_value);
            })->addColumn('bills', function ($data) {
                $bills = array_sum(array_column($data->investorTransactions1->toArray(), 'amount'));

                return FFM::dollar($bills);
            })->addColumn('distributions', function ($data) {
                $distributions = array_sum(array_column($data->investorTransactions2->toArray(), 'amount'));

                return FFM::dollar($distributions);
            })->addColumn('total_portfolio_earnings', function ($data) use ($rate) {
                $bills = array_sum(array_column($data->investorTransactions1->toArray(), 'amount'));
                $distributions = array_sum(array_column($data->investorTransactions2->toArray(), 'amount'));
                $rtr = $ctd = $total_amount = $fees = 0;
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
                $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
                $total_amount = $total_amount + $total_invest_rtr;
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;
                $portfolio_earning = $portfolio_value - $bills - $distributions;

                return FFM::dollar($portfolio_earning);
            })->addColumn('IRR', function ($data) use ($rate) {
                $bills = array_sum(array_column($data->investorTransactions1->toArray(), 'amount'));
                $distributions = array_sum(array_column($data->investorTransactions2->toArray(), 'amount'));
                $all_debits = array_sum(array_column($data->investorTransactionsC->toArray(), 'amount'));
                $rtr = $ctd = $total_amount = $fees = 0;
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
                $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
                $total_amount = $total_amount + $total_invest_rtr;
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;
                $portfolio_earning = $portfolio_value - $bills - $distributions;
                $credited_amount = array_sum(array_column($data->investorTransactions->toArray(), 'amount'));
                $IRR = ((($portfolio_earning - $credited_amount) / ($credited_amount)) * 100);

                return FFM::percent($IRR);
            })->with('total_portfolio', \FFM::dollar($total_portfolio))->with('total_bills', \FFM::dollar($total_bills))->with('total_distributions', \FFM::dollar($total_distributions))->with('total_portfolio_earning', \FFM::dollar($total_portfolio_earning))->with('total_IRR', '')->addIndexColumn()->make(true);
        }
    }

    public function deptInvestorReportOld($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [['data' => 'DT_RowIndex', 'title' => 'No', 'orderable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Investor'], ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value'], ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'], ['data' => 'distributions', 'name' => 'distributions', 'title' => 'Distributions'], ['data' => 'total_portfolio_earnings', 'name' => 'total_portfolio_earnings', 'title' => 'Total Portfolio Earnings'], ['data' => 'IRR', 'name' => 'IRR', 'title' => 'IRR']];
        } else {
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            $data1 = User::select('users.id', 'users.name')->leftJoin('user_has_roles', function ($join) {
                $join->on('users.id', '=', 'user_has_roles.model_id');
                $join->where('user_has_roles.role_id', 2);
            })->leftJoin('roles', function ($join) {
                $join->on('user_has_roles.model_id', '=', 'roles.id');
                $join->where('roles.name', 'investor');
            })->whereHas('investmentData1', function ($query) {
                $query->where('status', 1);
                $query->whereHas('merchant', function ($query1) {
                    $query1->where('active_status', 1);
                });
            })->with(['investmentData2' => function ($query) {
                $query->where('status', 1);
                $query->whereHas('merchant', function ($query1) {
                    $query1->where('active_status', 1);
                    $query1->where('sub_status_id', '!=', 4);
                });
            }])->with(['userDetails' => function ($query) {
            }])->with(['investorTransactions1' => function ($query) {
                $query->where('transaction_type', 1);
                $query->where('transaction_category', 10);
            }])->with(['investorTransactions2' => function ($query) {
                $query->where('transaction_type', 1);
                $query->whereIn('transaction_category', [6, 7]);
            }])->with(['investorTransactionsC' => function ($query) {
                $query->where('transaction_type', 1);
            }])->with(['investorTransactions' => function ($query) {
                $query->where('transaction_type', 2);
            }])->with(['participantPayment' => function ($query) {
                $query->whereHas('merchant', function ($query1) {
                    $query1->where('sub_status_id', '=', 4)->where('active_status', '=', 1);
                });
            }]);
            if ($investors && is_array($investors)) {
                $data1 = $data1->whereIn('users.id', $investors);
            }
            if (empty($permission)) {
                $data1 = $data1->where('creator_id', $userId);
            }
            $data = $data1->where('users.investor_type', 1)->get();
            $rate = Settings::value('rate');
            $total_portfolio = $total_bills = $total_distributions = $total_portfolio_earning = $total_IRR = 0;
            foreach ($data->get() as $value) {
                $rtr = 0;
                $ctd = 0;
                $total_amount = 0;
                $fees = 0;
                $liquidity = $value->userDetails->liquidity;
                $default_pay_rtr1 = array_sum(array_column($value->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($value->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($value->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                foreach ($value->investmentData2 as $key => $merchant_user) {
                    if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                        $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                        $total_amount = $total_amount + $merchant_user->invest_rtr;
                    }
                }
                $rtr = $total_amount - $fees + $default_pay_rtr1;
                $mgt_fee = array_sum(array_column($value->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($value->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;
                $total_portfolio = $total_portfolio + $portfolio_value;
                $bills = array_sum(array_column($value->investorTransactions1->toArray(), 'amount'));
                $distributions = array_sum(array_column($value->investorTransactions2->toArray(), 'amount'));
                $total_bills = $total_bills + $bills;
                $total_distributions = $total_distributions + $distributions;
                $total_portfolio_earning = $total_portfolio - $bills - $distributions;
                $credited_amount = array_sum(array_column($value->investorTransactions->toArray(), 'amount'));
                $total_IRR = ((($total_portfolio_earning - $credited_amount) / ($credited_amount)) * 100);
            }

            return \DataTables::eloquent($data)->addColumn('name', function ($data) {
                return $data->name;
            })->editColumn('portfolio_value', function ($data) use ($rate) {
                $ctd = $total_amount = $fees = $rtr = $ctd2 = 0;
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                foreach ($data->investmentData2 as $key => $merchant_user) {
                    if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                        $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                        $total_amount = $total_amount + $merchant_user->invest_rtr;
                    }
                }
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;

                return FFM::dollar($portfolio_value);
            })->editColumn('bills', function ($data) {
                $bills = array_sum(array_column($data->investorTransactions1->toArray(), 'amount'));

                return FFM::dollar($bills);
            })->editColumn('distributions', function ($data) {
                $distributions = array_sum(array_column($data->investorTransactions2->toArray(), 'amount'));

                return FFM::dollar($distributions);
            })->editColumn('total_portfolio_earnings', function ($data) use ($rate) {
                $bills = array_sum(array_column($data->investorTransactions1->toArray(), 'amount'));
                $distributions = array_sum(array_column($data->investorTransactions2->toArray(), 'amount'));
                $rtr = 0;
                $ctd = 0;
                $total_amount = 0;
                $fees = 0;
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                foreach ($data->investmentData2 as $key => $merchant_user) {
                    if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                        $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                        $total_amount = $total_amount + $merchant_user->invest_rtr;
                    }
                }
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;
                $portfolio_earning = $portfolio_value - $bills - $distributions;

                return FFM::dollar($portfolio_earning);
            })->editColumn('IRR', function ($data) use ($rate) {
                $bills = array_sum(array_column($data->investorTransactions1->toArray(), 'amount'));
                $distributions = array_sum(array_column($data->investorTransactions2->toArray(), 'amount'));
                $rtr = $ctd = $total_amount = $fees = 0;
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                foreach ($data->investmentData2 as $key => $merchant_user) {
                    if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                        $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                        $total_amount = $total_amount + $merchant_user->invest_rtr;
                    }
                }
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;
                $portfolio_earning = $portfolio_value - $bills - $distributions;
                $credited_amount = array_sum(array_column($data->investorTransactions->toArray(), 'amount'));
                $IRR = ((($portfolio_earning - $credited_amount) / ($credited_amount)) * 100);

                return FFM::percent($IRR);
            })->with('total_portfolio', \FFM::dollar($total_portfolio))
                ->with('total_bills', \FFM::dollar($total_bills))
                ->with('total_distributions', \FFM::dollar($total_distributions))
                ->with('total_portfolio_earning', \FFM::dollar($total_portfolio_earning))
                ->with('total_IRR', FFM::percent(0))
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function investorProfitReport($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => '#', 'orderable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
                ['data' => 'credited_amount', 'name' => 'credited_amount', 'title' => 'Principal Investment'],
                ['data' => '3monthinterest', 'name' => '3monthinterest', 'title' => '3 month Interest'],
                ['data' => 'princ_interest', 'name' => 'princ_interest', 'title' => 'Principal + Interest'],
                ['data' => 'current_balance', 'name' => 'current_balance', 'title' => 'Current Balance'],
            ];
        } else {
            $data = $this->user1->investorProfitReport($investors);
            $newArray = [];

            return \DataTables::of($data)->addColumn('name', function ($data) {
                return "<a href=/admin/investors/portfolio/$data->id>".$data->name.'</a>';
            })->addColumn('3monthinterest', function ($data) {
                $creditWithInterest = (($data->credit_amount * $data->interest_rate / 100) / 365 * 90);

                return FFM::dollar($creditWithInterest);
            })->addColumn('credited_amount', function ($data) {
                return FFM::dollar($data->credit_amount);
            })->editColumn('princ_interest', function ($data) {
                $princ_interest = ($data->credit_amount + ($data->credit_amount * $data->interest_rate / 100) / 365 * 90);

                return FFM::dollar($princ_interest);
            })->editColumn('current_balance', function ($data) {
                $total_amount = 0;
                $princ_interest = ($data->credit_amount + ($data->credit_amount * $data->interest_rate / 100) / 365 * 90);
                $fees = $data->fees;
                $total_rtr = FFM::adjustment($fees + $data->default_pay_rtr, $data->id);
                $total_ctd = ($data->ctd);
                $current_balance = ($total_rtr + $data->liquidity - $total_ctd);
                $color = $princ_interest > $current_balance ? 'red' : 'green';

                return '<font color='.$color.'>'.FFM::dollar($current_balance).'</font>';
            })->rawColumns(['current_balance', 'name'])->make(true);
        }
    }

    public function investorProfitReportOriginal($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'No', 'orderable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
                ['data' => 'credited_amount', 'name' => 'credited_amount', 'title' => 'Principal Investment'],
                ['data' => '3monthinterest', 'name' => '3monthinterest', 'title' => '3 month Interest'],
                ['data' => 'princ_interest', 'name' => 'princ_interest', 'title' => 'Principal + Interest'],
                ['data' => 'current_balance', 'name' => 'current_balance', 'title' => 'Current Balance'],
            ];
        } else {
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            $data = Role::whereName('investor')->first()->users;
            if ($investors && is_array($investors)) {
                $data = $data->whereIn('id', $investors);
            }
            if (empty($permission)) {
                $data = $data->where('creator_id', $userId);
            }
            $data = $data->where('investor_type', 1);
            $rate = Settings::value('rate');

            return \DataTables::of($data)->addColumn('name', function ($data) {
                return $data->name;
            })->editColumn('3monthinterest', function ($data) {
                $credited_amount = InvestorTransaction::where('investor_id', $data->id)->where('transaction_type', 2)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
                $creditWithInterest = (($credited_amount * $data->interest_rate / 100) / 365 * 90);

                return FFM::dollar($creditWithInterest);
            })->editColumn('credited_amount', function ($data) {
                $credited_amount = InvestorTransaction::where('investor_id', $data->id)->where('transaction_type', 2)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
                $creditWithInterest = ($credited_amount);

                return FFM::dollar($creditWithInterest);
            })->editColumn('princ_interest', function ($data) {
                $credited_amount = InvestorTransaction::where('investor_id', $data->id)->where('transaction_type', 2)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
                $creditWithInterest = ($credited_amount + ($credited_amount * $data->interest_rate / 100) / 365 * 90);

                return FFM::dollar($creditWithInterest);
            })->editColumn('current_balance', function ($data) use ($rate) {
                $credited_amount = InvestorTransaction::where('investor_id', $data->id)->where('transaction_type', 2)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
                $creditWithInterest = ($credited_amount + ($credited_amount * $data->interest_rate / 100) / 365 * 90);
                $total_amount = 0;
                $fees = 0;
                $total_rtr = 0;
                $ctd = 0;
                $current_balance = 0;
                $default_pay_rtr = ParticipentPayment::where('user_id', $data->id)->whereHas('merchant', function ($query) {
                    $query->where('sub_status_id', '=', 4)->where('active_status', '=', 1);
                })->sum('participant_share');
                $merchant_users = MerchantUser::where('status', 1)->where('user_id', $data->id)->whereHas('merchant', function ($query) {
                    $query->where('active_status', 1);
                    $query->where('sub_status_id', '!=', 4);
                })->with('merchant')->get();
                if (! empty($merchant_users)) {
                    $mgmnt_fee = array_sum(array_column($merchant_users->toArray(), 'mgmnt_fee'));
                    $syndication_fee = array_sum(array_column($merchant_users->toArray(), 'syndication_fee'));
                    $fees = $mgmnt_fee + $syndication_fee;
                    foreach ($merchant_users as $key => $merchant_user) {
                        if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                            $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                            $total_amount = $total_amount + $merchant_user->invest_rtr;
                        }
                    }
                }
                $total_rtr = $total_amount - $fees + $default_pay_rtr;
                $liquidity = UserDetails::where('user_id', $data->id)->value('liquidity');
                $investments = MerchantUser::whereHas('investors', function ($query) use ($data) {
                    $query->where('user_id', $data->id);
                })->whereHas('merchant', function ($query1) {
                    $query1->where('active_status', 1);
                })->get();
                if (! empty($investments)) {
                    $paid_participant_ishare = array_sum(array_column($investments->toArray(), 'paid_participant_ishare'));
                    $paid_mgmnt_fee = array_sum(array_column($investments->toArray(), 'paid_mgmnt_fee'));
                    $ctd = $paid_participant_ishare - $paid_mgmnt_fee;
                }
                $current_balance = ($total_rtr + $liquidity - $ctd);
                $color = $creditWithInterest > $current_balance ? 'red' : 'green';

                return '<font color='.$color.'>'.FFM::dollar($current_balance).'</font>';
            })->rawColumns(['current_balance'])->make(true);
        }
    }

    public function equityInvestorReportOriginal($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => 'No', 'orderable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
                ['data' => 'credits', 'name' => 'credits', 'title' => 'Credit'],
                ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value'],
                ['data' => 'velocity_profit', 'name' => 'velocity_profit', 'title' => 'Velocity Profit'],
                ['data' => 'investor_profit', 'name' => 'investor_profit', 'title' => 'Investor Profit'],
            ];
        } else {
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            $data = $this->user1->equityInvestorReport($investors);
            $rate = Settings::value('rate');
            $total_credits = $total_velocity_profit = $total_credit_amount = $total_investor_porfit = $total_portfolio_value = 0;
            $newArray = [];
            foreach ($data->get() as $key => $value) {
                $liquidity = $value->userDetails->liquidity;
                $newArray[$key]['id'] = $value->id;
                $newArray[$key]['name'] = $value->name;
                $credited_amount = array_sum(array_column($value->investorTransactions->toArray(), 'amount'));
                $default_pay_rtr1 = array_sum(array_column($value->participantPayment->toArray(), 'final_participant_share'));
                $total_rtr = $total_fees = $total_amount1 = $total_ctd = $total_paid_fee = $total_mgt_fee = $total_participant_share = 0;
                $mgmnt_fee = array_sum(array_column($value->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($value->investmentData2->toArray(), 'syndication_fee'));
                $total_fees = $mgmnt_fee + $syndication_fee;
                $invest_rtr = array_sum(array_column($value->investmentData2->toArray(), 'invest_rtr'));
                $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
                $total_amount1 = $total_amount1 + $total_invest_rtr;
                $total_rtr = $total_amount1 - $total_fees + $default_pay_rtr1;
                $total_mgt_fee = array_sum(array_column($value->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $total_participant_share = array_sum(array_column($value->investmentData1->toArray(), 'paid_participant_ishare'));
                $total_ctd = $total_participant_share - $total_mgt_fee;
                $total_credit_amount = $total_credit_amount + $credited_amount;
                $investor_profit = ($total_rtr - $total_ctd + $liquidity - $credited_amount) / 2;
                $total_profit = ($investor_profit > 0) ? $investor_profit * 0.5 : 0;
                $portfolio_value = ($total_rtr + $liquidity) - $total_ctd;
                $velocity_profit = (($total_rtr - $total_ctd + $liquidity) - $credited_amount) * .5;
                $total_portfolio_value = $total_portfolio_value + $portfolio_value;
                $total_velocity_profit = $total_velocity_profit + $velocity_profit;
                $total_investor_porfit = $total_investor_porfit + $velocity_profit;
            }

            return \DataTables::of($data)->addColumn('name', function ($data) {
                return $data->name;
            })->addColumn('credits', function ($data) {
                $credited_amount = array_sum(array_column($data->investorTransactions->toArray(), 'amount'));

                return FFM::dollar($credited_amount);
            })->addColumn('portfolio_value', function ($data) use ($rate) {
                $rtr = $ctd = $total_amount = $fees = 0;
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
                $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
                $total_amount = $total_amount + $total_invest_rtr;
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;

                return FFM::dollar($portfolio_value);
            })->addColumn('velocity_profit', function ($data) use ($rate) {
                $rtr = $ctd = $velocity_profit = $total_amount = $fees = 0;
                $credited_amount = array_sum(array_column($data->investorTransactions->toArray(), 'amount'));
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
                $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
                $total_amount = $total_amount + $total_invest_rtr;
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $velocity_profit = (($rtr - $ctd + $liquidity) - $credited_amount) * .5;

                return FFM::dollar($velocity_profit);

                return FFM::dollar($return_data);
            })->addColumn('investor_profit', function ($data) use ($rate) {
                $rtr = $ctd = $investor_profit = $total_amount = $fees = $credited_amount = 0;
                $credited_amount = $credited_amount + array_sum(array_column($data->investorTransactions->toArray(), 'amount'));
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
                $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
                $total_amount = $total_amount + $total_invest_rtr;
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $velocity_profit = (($rtr - $ctd + $liquidity) - $credited_amount) * .5;

                return FFM::dollar($velocity_profit);

                return FFM::dollar($return_data);
            })->with('total_credit_amount', \FFM::dollar($total_credit_amount))
                ->with('total_portfolio_value', \FFM::dollar($total_portfolio_value))
                ->with('total_velocity_profit', \FFM::dollar($total_velocity_profit))
                ->with('total_investor_porfit', \FFM::dollar($total_investor_porfit))->addIndexColumn()
                ->make(true);
        }
    }

    public function equityInvestorReportOld($investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'DT_RowIndex', 'title' => 'No', 'orderable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
                ['data' => 'credits', 'name' => 'credits', 'title' => 'Credit'],
                ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value'],
                ['data' => 'velocity_profit', 'name' => 'velocity_profit', 'title' => 'Velocity Profit'],
                ['data' => 'investor_profit', 'name' => 'investor_profit', 'title' => 'Investor Profit'],
            ];
        } else {
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            $data1 = User::select('users.id', 'users.name')->leftJoin('user_has_roles', function ($join) {
                $join->on('users.id', '=', 'user_has_roles.model_id');
                $join->where('user_has_roles.role_id', 2);
            })->leftJoin('roles', function ($join) {
                $join->on('user_has_roles.model_id', '=', 'roles.id');
                $join->where('roles.name', 'investor');
            })->whereHas('investmentData1', function ($query) {
                $query->where('status', 1);
                $query->whereHas('merchant', function ($query1) {
                    $query1->where('active_status', 1);
                });
            })->with(['investmentData2' => function ($query) {
                $query->where('status', 1);
                $query->whereHas('merchant', function ($query1) {
                    $query1->where('active_status', 1);
                    $query1->where('sub_status_id', '!=', 4);
                });
            }])->with(['userDetails' => function ($query) {
            }])->with(['investorTransactions' => function ($query) {
                $query->where('transaction_type', 2);
            }])->with(['participantPayment' => function ($query) {
                $query->whereHas('merchant', function ($query1) {
                    $query1->where('sub_status_id', '=', 4);
                    $query1->where('active_status', '=', 1);
                });
            }]);
            if ($investors && is_array($investors)) {
                $data1 = $data1->whereIn('users.id', $investors);
            }
            if (empty($permission)) {
                $data1 = $data1->where('creator_id', $userId);
            }
            $data = $data1->where('users.investor_type', 2)->get();
            $rate = Settings::value('rate');
            $total_credits = $total_velocity_profit = $total_credit_amount = $total_investor_porfit = $total_portfolio_value = 0;
            foreach ($data as $value) {
                $liquidity = $value->userDetails->liquidity;
                $credited_amount = array_sum(array_column($value->investorTransactions->toArray(), 'amount'));
                $default_pay_rtr1 = array_sum(array_column($value->participantPayment->toArray(), 'final_participant_share'));
                $total_rtr = $total_fees = $total_amount1 = $total_ctd = $total_paid_fee = $total_mgt_fee = $total_participant_share = 0;
                $mgmnt_fee = array_sum(array_column($value->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($value->investmentData2->toArray(), 'syndication_fee'));
                $total_fees = $mgmnt_fee + $syndication_fee;
                foreach ($value->investmentData2 as $key => $merchant_user) {
                    if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                        $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                        $total_amount1 = $total_amount1 + $merchant_user->invest_rtr;
                    }
                }
                $total_rtr = $total_amount1 - $total_fees + $default_pay_rtr1;
                $total_mgt_fee = array_sum(array_column($value->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $total_participant_share = array_sum(array_column($value->investmentData1->toArray(), 'paid_participant_ishare'));
                $total_ctd = $total_participant_share - $total_mgt_fee;
                $total_credit_amount = $total_credit_amount + $credited_amount;
                $investor_profit = ($total_rtr - $total_ctd + $liquidity - $credited_amount) / 2;
                $total_profit = ($investor_profit > 0) ? $investor_profit * 0.5 : 0;
                $portfolio_value = ($total_rtr + $liquidity) - $total_ctd;
                $total_portfolio_value = $total_portfolio_value + $portfolio_value;
                $total_velocity_profit = $total_velocity_profit + $total_profit;
                $total_investor_porfit = $total_investor_porfit + $total_profit;
            }

            return \DataTables::of($data)->addColumn('name', function ($data) {
                return $data->name;
            })->addColumn('credits', function ($data) {
                $credited_amount = array_sum(array_column($data->investorTransactions->toArray(), 'amount'));

                return FFM::dollar($credited_amount);
            })->addColumn('portfolio_value', function ($data) use ($rate) {
                $rtr = 0;
                $ctd = 0;
                $total_amount = 0;
                $fees = 0;
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                foreach ($data->investmentData2 as $key => $merchant_user) {
                    if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                        $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                        $total_amount = $total_amount + $merchant_user->invest_rtr;
                    }
                }
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $portfolio_value = ($rtr + $liquidity) - $ctd;

                return FFM::dollar($portfolio_value);
            })->addColumn('velocity_profit', function ($data) use ($rate) {
                $rtr = 0;
                $ctd = 0;
                $velocity_profit = 0;
                $total_amount = 0;
                $fees = 0;
                $credited_amount = array_sum(array_column($data->investorTransactions->toArray(), 'amount'));
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                foreach ($data->investmentData2 as $key => $merchant_user) {
                    if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                        $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                        $total_amount = $total_amount + $merchant_user->invest_rtr;
                    }
                }
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $velocity_profit = ($rtr - $ctd + $liquidity - $credited_amount) / 2;
                $return_data = ($velocity_profit > 0) ? $velocity_profit * 0.5 : 0;

                return FFM::dollar($return_data);
            })->addColumn('investor_profit', function ($data) use ($rate) {
                $rtr = 0;
                $ctd = 0;
                $investor_profit = 0;
                $total_amount = 0;
                $fees = 0;
                $credited_amount = 0;
                $credited_amount = $credited_amount + array_sum(array_column($data->investorTransactions->toArray(), 'amount'));
                $liquidity = $data->userDetails->liquidity;
                $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
                $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
                $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
                $fees = $mgmnt_fee + $syndication_fee;
                foreach ($data->investmentData2 as $key => $merchant_user) {
                    if (isset($merchant_user->merchant) && $merchant_user->invest_rtr && $merchant_user->merchant->pmnts && $merchant_user->amount) {
                        $merchant_user->invest_rtr = $merchant_user->invest_rtr - ($merchant_user->invest_rtr * ($rate / 100));
                        $total_amount = $total_amount + $merchant_user->invest_rtr;
                    }
                }
                $rtr = $total_amount - $fees + $default_pay_rtr;
                $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
                $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
                $ctd = $participant_share - $mgt_fee;
                $investor_profit = ($rtr - $ctd + $liquidity - $credited_amount) / 2;
                $return_data = ($investor_profit > 0) ? $investor_profit * 0.5 : 0;

                return FFM::dollar($return_data);
            })->with('total_credit_amount', \FFM::dollar($total_credit_amount))->with('total_portfolio_value', \FFM::dollar($total_portfolio_value))->with('total_velocity_profit', \FFM::dollar($total_velocity_profit))->with('total_investor_porfit', \FFM::dollar($total_investor_porfit))->addIndexColumn()->make(true);
        }
    }

    public function generatedPdfCsvManager($columRequest = false)
    {
        if ($columRequest) {
            $tableBuilder->columns([['data' => 'DT_RowIndex', 'title' => 'No', 'orderable' => false], ['data' => 'file_name', 'name' => 'file_name', 'title' => 'Lender'], ['data' => 'user_id', 'name' => 'user_id', 'title' => 'Investor'], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Investor']]);
        } else {
            $data = Statements::get();

            return \DataTables::of($data)->addColumn('user_id', function ($data) {
                return $data->user_id;
            })->addColumn('file_name', function ($data) {
                return $data->file_name;
            })->addColumn('created_at', function ($data) {
                return $data->created_at;
            })->addIndexColumn()->make(true);
        }
    }

    public function getAllInvestorLiquiditytest($startDate = null, $endDate = null, $subadmin = null)
    {
        $i = 0;
        $sDate = $startDate;
        $eDate = trim($endDate);
        $report = [];
        $total_ctd = $total_credit = $total_commission = $total_fund_value = $total_prepaid = $total_liquidity = 0;
        if ($startDate) {
            $sDate = Carbon::createFromFormat('m/d/Y', $sDate)->format('Y-m-d');
            $startDate = $sDate.' 00:00:00';
        }
        if ($endDate) {
            $eDate = Carbon::createFromFormat('m/d/Y', $eDate)->format('Y-m-d');
            $endDate = $eDate.' 23:23:59';
        }
        if ($subadmin == 'subadmin') {
            $investors = $this->role->allSubadminInvestorsLiquidity()->toArray();
        } else {
            $investors = $this->role->allInvestorsLiquidity()->toArray();
        }
        $report = User::join('user_has_roles', function ($join) {
            $join->on('users.id', '=', 'user_has_roles.model_id');
            $join->where('user_has_roles.role_id', 2);
        })->with(['participantPayment' => function ($query) {
        }])->select('id', 'name')->get();

        return \DataTables::of($report)->addColumn('investor_name', function ($report) {
            return $report->name;
        })->addColumn('liquidity', function ($report) {
            return 2;
        })->addColumn('ctd', function ($report) {
            return 3;
        })->rawColumns(['ctd', 'credits', 'total_funded', 'commission_amount', 'investor_name'])->addColumn('credits', function ($report) {
            return 4;
        })->addColumn('commission_amount', function ($report) {
            return 5;
        })->addColumn('total_funded', function ($report) {
            return 6;
        })->addColumn('pre_paid_amount', function ($report) {
            return 7;
        })->addIndexColumn()
            ->with('total_ctd', \FFM::dollar($total_ctd))
            ->with('total_credit', \FFM::dollar($total_credit))
            ->with('total_commission', \FFM::dollar($total_commission))
            ->with('total_fund', \FFM::dollar($total_fund_value))
            ->with('total_prepaid', \FFM::dollar($total_prepaid))
            ->with('total_liquidity', \FFM::dollar($total_liquidity))
            ->make();
    }

    public function getAllInvestorLiquidity($startDate = null, $endDate = null, $subadmin = null, $active = null, $company = null,$velocity_owned = false)
    {
        $i = 0;
        $sDate = $startDate;
        $eDate = trim($endDate);
        $report = [];
        $total_ctd = $total_credit = $total_commission = $total_fund_value = $total_prepaid = $total_liquidity = $total_underwriting_fee = 0;
        $investors = $this->role->allInvestorsWithLiquidity($startDate, $endDate, $subadmin, $active, $company,$velocity_owned);
        $investors_array = $investors->get()->toArray();
        $total_rtr = array_sum(array_map(function ($var) {
            return $var['rtr'];
        }, $investors_array));
        $total_ctd = array_sum(array_map(function ($var) {
            return $var['ctd'];
        }, $investors_array));
        $total_rtr_bal = $total_rtr - $total_ctd;
        $total_credit = array_sum(array_map(function ($var) {
            return $var['credit_amount'];
        }, $investors_array));
        $total_commission = array_sum(array_map(function ($var) {
            return $var['commission_amount'];
        }, $investors_array));
        $total_fund_value = array_sum(array_map(function ($var) {
            return $var['total_funded'];
        }, $investors_array));
        $total_prepaid = array_sum(array_map(function ($var) {
            return $var['pre_paid'];
        }, $investors_array));
        $total_underwriting_fee = array_sum(array_map(function ($var) {
            return $var['under_writing_fee'];
        }, $investors_array));
        $total_liquidity = ($total_credit + $total_ctd) - ($total_fund_value + $total_commission) - $total_prepaid - $total_underwriting_fee;
        $liquidity_adjuster = array_sum(array_map(function ($var) {
            return $var['liquidity_adjuster'];
        }, $investors_array));
        $total_liquidity = $total_liquidity + $liquidity_adjuster;

        return \DataTables::of($investors)->addColumn('users.name', function ($report) {
            return '<a href="/admin/investors/portfolio/'.$report['id'].'">'.$report['name'].'</a>';
        })->addColumn('liquidity', function ($report) {
            $liquidity = ($report['credit_amount'] + $report['ctd']) - ($report['total_funded'] + $report['commission_amount']) - $report['pre_paid'] - $report['under_writing_fee'];
            $liquidity = $liquidity + $report['liquidity_adjuster'];

            return FFM::dollar($liquidity)."<span style='display:none'> ".round($liquidity, 2).' </span>';
        })->addColumn('ctd', function ($report) use ($startDate, $endDate) {
            return "<a data-actual='".round($report['ctd'], 2)."' target='_blank' href='".route('admin::reports::payments', ['id' => $report['id'], 'start_date' => $startDate, 'end_date' => $endDate])."'>".FFM::dollar($report['ctd']).'</a>';
        })->rawColumns(['ctd', 'credits', 'total_funded', 'commission_amount', 'users.name', 'under_writing_fee', 'pre_paid_amount', 'liquidity'])->addColumn('credits', function ($report) use ($startDate, $endDate) {
            return "<a data-actual='".round($report['credit_amount'], 2)."' target='_blank' href='".route('admin::investors::transaction::index', ['id' => $report['id'], 'sdate' => $startDate, 'edate' => $endDate])."'>".FFM::dollar($report['credit_amount']).'</a>';
        })->addColumn('commission_amount', function ($report) use ($startDate, $endDate) {
            return "<a data-actual='".round($report['commission_amount'], 2)."' target='_blank' href='".route('admin::reports::investor', ['id' => $report['id'], 'start_date' => $startDate, 'end_date' => $endDate])."'>".FFM::dollar($report['commission_amount']).'</a>';
        })->addColumn('total_funded', function ($report) use ($startDate, $endDate) {
            return "<a data-actual='".round($report['total_funded'], 2)."' target='_blank' href='".route('admin::reports::get-investor-assign-report', ['id' => $report['id'], 'sdate' => $startDate, 'edate' => $endDate])."'>".FFM::dollar($report['total_funded']).'</a>';
        })->addColumn('pre_paid_amount', function ($report) {
            return FFM::dollar($report['pre_paid'])."<span style='display:none'>".round($report['pre_paid'], 2).'</span>';
        })->addColumn('under_writing_fee', function ($report) {
            return FFM::dollar($report['under_writing_fee'])."<span style='display:none'>".round($report['under_writing_fee'], 2).'</span>';
        })->addColumn('rtr_balance', function ($report) {
            return FFM::dollar($report['rtr'] - $report['ctd']);
        })->filterColumn('users.name', function ($query, $keyword) {
            $sql = 'users.name like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->addIndexColumn()
            ->with('total_ctd', \FFM::dollar($total_ctd))
            ->with('total_credit', \FFM::dollar($total_credit))
            ->with('total_commission', \FFM::dollar($total_commission))
            ->with('total_fund', \FFM::dollar($total_fund_value))
            ->with('total_prepaid', \FFM::dollar($total_prepaid))
            ->with('total_liquidity', \FFM::dollar($total_liquidity))
            ->with('total_underwriting_fee', \FFM::dollar($total_underwriting_fee))
            ->with('total_rtr_bal', \FFM::dollar($total_rtr_bal))
            ->make();
    }

    public function getAllInvestorLiquidityold($startDate = null, $endDate = null, $subadmin = null)
    {
        $i = 0;
        $sDate = $startDate;
        $eDate = trim($endDate);
        $report = [];
        $total_ctd = $total_credit = $total_commission = $total_fund_value = $total_prepaid = $total_liquidity = 0;
        if ($startDate) {
            $sDate = Carbon::createFromFormat('m/d/Y', $sDate)->format('Y-m-d');
            $startDate = $sDate.' 00:00:00';
        }
        if ($endDate) {
            $eDate = Carbon::createFromFormat('m/d/Y', $eDate)->format('Y-m-d');
            $endDate = $eDate.' 23:23:59';
        }
        if ($subadmin == 'subadmin') {
            $investors = $this->role->allSubadminInvestorsLiquidity()->toArray();
        } else {
            $investors = $this->role->allInvestorsLiquidity()->toArray();
        }
        foreach ($investors as $inv) {
            $user_id = $inv['id'];
            $total_credits = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->where('investor_id', $user_id);
            if ($startDate) {
                $total_credits = $total_credits->where('investor_transactions.date', '>=', $startDate);
            }
            if ($endDate) {
                $total_credits = $total_credits->where('investor_transactions.date', '<=', $endDate);
            }
            $total_credits = $total_credits->sum('amount');
            $merchant_user = MerchantUser::where('status', 1)->where('merchant_user.user_id', $user_id)->whereHas('merchant', function ($query) use ($user_id, $startDate, $endDate) {
                $query->where('active_status', 1);
                if ($startDate) {
                    $query->where('merchants.date_funded', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('merchants.date_funded', '<=', $endDate);
                }
            })->select([DB::raw('sum(amount) as amount '), DB::raw('sum(paid_mgmnt_fee) as paid_mgmnt_fee '), DB::raw('sum(commission_amount) as commission_amount '), DB::raw('sum(pre_paid) as pre_paid')])->get();
            $paid_mgmnt_fee = $merchant_user[0]->paid_mgmnt_fee;
            $total_funded = $merchant_user[0]->amount;
            $commission_amount = $merchant_user[0]->commission_amount;
            $pre_paid_amount1 = $merchant_user[0]->pre_paid;
            $ctd = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('user_id', $user_id);
            if ($startDate) {
                $ctd = $ctd->where('participent_payments.payment_date', '>=', $startDate);
            }
            if ($endDate) {
                $ctd = $ctd->where('participent_payments.payment_date', '<=', $endDate);
            }
            $ctd = $ctd->whereHas('merchant', function ($query) {
                $query->where('active_status', 1);
            })->value(DB::raw('sum(payment_investors.participant_share - payment_investors.mgmnt_fee - payment_investors.syndication_fee)'));
            $liquidity = ($total_credits + $ctd) - ($total_funded + $commission_amount) - $pre_paid_amount1;
            $report[$i]['user_id'] = $user_id;
            $report[$i]['name'] = $inv['name'];
            $report[$i]['liquidity'] = $liquidity;
            $report[$i]['serial_no'] = $i + 1;
            $report[$i]['ctd'] = $ctd;
            $report[$i]['total_credits'] = $total_credits;
            $report[$i]['paid_mgmnt_fee'] = $paid_mgmnt_fee;
            $report[$i]['paid_syndication'] = $paid_syndication;
            $report[$i]['total_funded'] = $total_funded;
            $report[$i]['commission_amount'] = $commission_amount;
            $report[$i]['pre_paid_amount'] = $pre_paid_amount1;
            $report[$i]['start_date'] = $sDate;
            $report[$i]['end_date'] = $eDate;
            $total_ctd = $total_ctd + $ctd;
            $total_credit = $total_credit + $total_credits;
            $total_commission = $total_commission + $commission_amount;
            $total_fund_value = $total_fund_value + $total_funded;
            $total_prepaid = $total_prepaid + $pre_paid_amount1;
            $total_liquidity = $total_liquidity + $liquidity;
            $i++;
        }

        return \DataTables::of($report)->addColumn('investor_name', function ($report) {
            return '<a href="/admin/investors/portfolio/'.$report['user_id'].'">'.$report['name'].'</a>';
        })->addColumn('liquidity', function ($report) {
            return FFM::dollar($report['liquidity']);
        })->addColumn('ctd', function ($report) {
            return "<a target='_blank' href='".route('admin::reports::payments', ['user_id' => $report['user_id'], 'start_date' => $report['start_date'], 'end_date' => $report['end_date']])."'>".FFM::dollar($report['ctd']).'</a>';
        })->rawColumns(['ctd', 'credits', 'total_funded', 'commission_amount', 'investor_name'])->addColumn('credits', function ($report) {
            return "<a target='_blank' href='".route('admin::investors::transaction::index', ['user_id' => $report['user_id'], 'sdate' => $report['start_date'], 'edate' => $report['end_date']])."'>".FFM::dollar($report['total_credits']).'</a>';
        })->addColumn('commission_amount', function ($report) {
            return "<a target='_blank' href='".route('admin::reports::investor', ['user_id' => $report['user_id'], 'start_date' => $report['start_date'], 'end_date' => $report['end_date']])."'>".FFM::dollar($report['commission_amount']).'</a>';
        })->addColumn('total_funded', function ($report) {
            return "<a target='_blank' href='".route('admin::reports::get-investor-assign-report', ['user_id' => $report['user_id'], 'sdate' => $report['start_date'], 'edate' => $report['end_date']])."'>".FFM::dollar($report['total_funded']).'</a>';
        })->addColumn('pre_paid_amount', function ($report) {
            return FFM::dollar($report['pre_paid_amount']);
        })->addIndexColumn()
            ->with('total_ctd', \FFM::dollar($total_ctd))
            ->with('total_credit', \FFM::dollar($total_credit))
            ->with('total_commission', \FFM::dollar($total_commission))
            ->with('total_fund', \FFM::dollar($total_fund_value))
            ->with('total_prepaid', \FFM::dollar($total_prepaid))
            ->with('total_liquidity', \FFM::dollar($total_liquidity))
            ->make();
    }

    public function reAssignmentHistory($startDate = null, $endDate = null, $investors = null, $merchants = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => 'No', 'searchable' => false],
                ['orderable' => false, 'data' => 'investor_from', 'name' => 'investor_from', 'defaultContent' => '', 'title' => 'Investor From'],
                ['data' => 'investor_to', 'name' => 'investor_to', 'title' => 'Investor To'],
                ['data' => 'merchant', 'name' => 'merchant', 'title' => 'Merchant'],
                ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
                ['orderable' => false, 'data' => 'liquidity_change', 'name' => 'liquidity_change', 'defaultContent' => '', 'title' => 'Liquidity Change'],
                ['orderable' => true, 'data' => 'investor1_final_liquidity', 'name' => 'investor1_final_liquidity', 'defaultContent' => '', 'title' => 'Investor1 Final Liquidity'],
                ['orderable' => true, 'data' => 'investor2_final_liquidity', 'name' => 'investor2_final_liquidity', 'defaultContent' => '', 'title' => 'investor2 Final Liquidity'],
                ['orderable' => true, 'data' => 'date', 'name' => 'date', 'defaultContent' => '', 'title' => 'Date'],
                ['data' => 'action', 'name' => 'action', 'orderable' => false, 'title' => 'Action', 'searchable' => false, 'visiblity' => false],
            ];
        }
        $data = $this->merchant->searchForInvestorReAssignmentReport($startDate, $endDate, $investors, $merchants);
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        if ($data) {
            return \DataTables::of($data)->addColumn('investor_from', function ($data) {
                return isset($data->investor_name) ? $data->investor_name : '';
            })->addColumn('investor_to', function ($data) {
                return isset($data->investmentData2->name) ? $data->investmentData2->name : '';
            })->addColumn('investor1_final_liquidity', function ($data) {
                return FFM::dollar($data->investor1_total_liquidity);
            })->addColumn('amount', function ($data) {
                return FFM::dollar($data->amount);
            })->addColumn('investor2_final_liquidity', function ($data) {
                return FFM::dollar($data->investor2_total_liquidity);
            })->addColumn('liquidity_change', function ($data) {
                return FFM::dollar($data->liquidity_change);
            })->addColumn('date', function ($data) {
                $created_date = 'Created On '.FFM::datetime($data->created_at_time).' by '.get_user_name_with_session($data->creator_id);
                return ($data->created_at_time != '') ? "<a title='$created_date'>".FFM::datetimetodate($data->created_at_time).'</a>' : '';
            })->addColumn('merchant', function ($data) {
                return isset($data->name) ? $data->name : '';
            })->addColumn('action', function ($data) {
                return Form::open(['route' => ['admin::merchants::undo-reassign', 'investor_id' => $data->investor2, 'merchant_id' => $data->merchant_id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to undo reassign ?")']).Form::submit('Undo Re-assign', ['class' => 'btn btn-xs btn-success']).Form::close();
            })->rawColumns(['action', 'date'])->make(true);
        }
    }

    public function comissionReport($merchants = null, $date_type = null, $startDate = null, $endDate = null, $investors = null,$columRequest = false, $stime = null, $etime = null, $type = null,$owner = null,$velocity_owned = false)
    {
        if ($columRequest) {
            return [
                ['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'data' => null, 'defaultContent' => '', 'title' => ''],
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'searchable' => false, 'orderable' => false],
                ['data' => 'id', 'name' => 'id', 'title' => 'Merchant Id', 'searchable' => true],
                ['data' => 'merchants.name', 'name' => 'merchants.name', 'title' => 'Merchant Name'],
                ['data' => 'total_payment_t', 'name' => 'total_payment_t', 'title' => 'Invested Amount'],
                ['data' => 'merchant_user.up_sell_commission', 'name' => 'merchant_user.up_sell_commission', 'title' => 'Upsell Commission', 'searchable' => false],      
            ];
        }
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $data = $this->merchant->searchForCommissionReport($date_type,$startDate, $endDate, $investors, $merchants, $stime, $etime, $type,$owner,$velocity_owned);
        $total = $data['total'];
        $data = $data['data'];
       
       return \DataTables::of($data)->setTotalRecords($total->count)
       ->editColumn('merchants.name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>".'<span style="display:none">'.$data->name.'</span>';
        })->rawColumns(['merchants.name', 'total_payment_t'])
        ->editColumn('merchant_user.up_sell_commission', function ($data) {
            return \FFM::dollar($data->m_up_sell_commission);
        })
        ->addColumn('total_payment_t', function ($data) {
            $new_total_payemnt_t = $data->pre_paid + $data->i_amount + $data->commission_amount + $data->under_writing_fee+$data->m_up_sell_commission;

            return \FFM::dollar($new_total_payemnt_t).'<span style="display:none">'.round($new_total_payemnt_t, 2).'</span>';
        })
       
        ->filterColumn('merchants.name', function ($query, $keyword) {
            $sql = 'merchants.name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->with('Total', 'Total:')
        ->with('gt_total', \FFM::dollar($total->t_total_amount + $total->t_pre_paid_amount + $total->t_commission_amount + $total->t_under_writing_fee+$total->t_up_sell_commission))
        ->with('gt_up_sell_commission', \FFM::dollar($total->t_up_sell_commission))
        ->addIndexColumn()
        ->make(true);
    }

    public function investorReport($merchants = null, $date_type = null, $advance_type = null, $merchant_date = null, $startDate = null, $endDate = null, $investors = null, $lenders = null, $columRequest = false, $stime = null, $etime = null, $type = null, $industries = null, $owner = null, $statuses = null, $investor_type = null, $substatus_flag = null, $label = null, $investor_label = null,$order = null,$active = null,$velocity_owned = false)
    {
        if ($columRequest) {
            return [
                ['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'data' => null, 'defaultContent' => '', 'title' => ''],
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'searchable' => false, 'orderable' => false],
                ['data' => 'id', 'name' => 'id', 'title' => 'Merchant Id', 'searchable' => true],
                ['data' => 'merchants.name', 'name' => 'merchants.name', 'title' => 'Merchant Name'],
                ['data' => 'merchants.date_funded', 'name' => 'merchants.date_funded', 'title' => 'Funded Date', 'searchable' => false],
                ['orderable' => false, 'data' => 'merchant_user.amount', 'name' => 'merchant_user.amount', 'title' => 'Funded Amount', 'searchable' => false , 'class' => 'funded_amount'],
                ['orderable' => false, 'data' => 'merchant_user.invest_rtr', 'name' => 'merchant_user.invest_rtr', 'title' => 'RTR', 'searchable' => false],
                ['orderable' => false, 'data' => 'merchant_user.commission_amount', 'name' => 'merchant_user.commission_amount', 'title' => 'Commission', 'searchable' => false],
                ['orderable' => false, 'data' => 'share_t', 'name' => 'share_t', 'title' => 'Share (%)', 'searchable' => false],
                ['orderable' => false, 'data' => 'merchant_user.pre_paid', 'name' => 'merchant_user.pre_paid', 'title' => 'Syndication Fee', 'searchable' => false],
                ['data' => 'total_payment_t', 'name' => 'total_payment_t', 'title' => 'Total Invested', 'class' => 'total_invested'],
                ['orderable' => false, 'data' => 'merchant_user.under_writing_fee', 'name' => 'merchant_user.under_writing_fee', 'title' => 'Under Writing Fee', 'searchable' => false],
                ['orderable' => false, 'data' => 'merchant_user.mgmnt_fee', 'name' => 'merchant_user.mgmnt_fee', 'title' => 'Anticipated Management Fee', 'searchable' => false],
                ['data' => 'merchants.created_at', 'name' => 'merchants.created_at', 'title' => 'Created On', 'searchable' => false],
            ];
        }
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $data = $this->merchant->searchForInvestorReport($date_type, $advance_type, $lenders, $startDate, $endDate, $investors, $merchants, $stime, $etime, $type, $industries, $owner, $statuses, $investor_type, $substatus_flag, $label, $investor_label,$order, $active,$velocity_owned);
        $total = $data['total'];
        $data = $data['data'];
        $pre_paid1 = 0;
        $commission1 = 0;
        $funded1 = 0;
        $mangmt_fee1 = 0;
        $syndication_fee1 = 0;
        $total1 = 0;
        $commissionPercentage = 0;
        $netZero = 0;
        $array1 = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->where('creator_id', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $array1[] = $value->id;
            }
        }
        $total_net_zero = $gt_rtr = $total_net_zero_with_interest = $i = $net_zero = $total_net_zero_with_limited_interest = $rtr_sum = $funded_sum = 0;
        $investor_arr = [];
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($data)->setTotalRecords($total->count)->editColumn('merchants.date_funded', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

            return "<a title='$created_date'>".FFM::date($data->date_funded).'</a>';
        })->editColumn('merchants.name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>".'<span style="display:none">'.$data->name.'</span>';
        })->rawColumns(['merchants.name', 'total_payment_t', 'merchants.created_at', 'merchants.date_funded'])
        ->editColumn('merchant_user.commission_amount', function ($data) {
            return \FFM::dollar($data->commission_amount+$data->m_up_sell_commission);
        })
        ->editColumn('merchant_user.up_sell_commission', function ($data) {
            return \FFM::dollar($data->m_up_sell_commission);
        })
        ->editColumn('merchant_user.pre_paid', function ($data) {
            return \FFM::dollar($data->pre_paid);
        })->addColumn('total_payment_t', function ($data) {
            // $new_total_payemnt_t = $data->pre_paid + $data->i_amount + $data->commission_amount + $data->under_writing_fee+$data->m_up_sell_commission;
            $new_total_payemnt_t = $data->invested_amount;
            return \FFM::dollar($new_total_payemnt_t).'<span style="display:none">'.round($new_total_payemnt_t, 2).'</span>';
        })->orderColumn('total_payment_t', function ($query, $order) {
            $query->orderBy('invested_amount', $order);
        })
        ->editColumn('merchant_user.mgmnt_fee', function ($data) {
            return \FFM::dollar($data->mgmnt_fee);
        })->editColumn('merchant_user.under_writing_fee', function ($data) {
            return \FFM::dollar($data->under_writing_fee);
        })->editColumn('merchant_user.invest_rtr', function ($data) {
            $rtr_t = 0;

            return \FFM::dollar($data->i_rtr);
        })->addColumn('share_t', function ($data) {
            return ($data->funded != 0) ? \FFM::percent($data->i_amount / $data->funded * 100) : FFM::percent(0);
        })->editColumn('merchant_user.amount', function ($data) {
            return \FFM::dollar($data->i_amount);
        })->editColumn('merchants.created_at', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

            return "<a title='$created_date'>".FFM::datetime($data->created_at).'</a>';
        })->filterColumn('merchant_user.amount', function ($query, $keyword) {
            $sql = 'merchant_user.amount  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('merchants.name', function ($query, $keyword) {
            $sql = 'merchants.name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('merchant_user.invest_rtr', function ($query, $keyword) {
            $sql = 'merchant_user.invest_rtr  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('merchant_user.commission_amount', function ($query, $keyword) {
            $sql = 'merchant_user.commission_amount like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->with('Total', 'Total:')
            ->with('gt_prepaid_amount', \FFM::dollar($total->t_pre_paid_amount))
            ->with('gt_total', \FFM::dollar($total->t_total_amount + $total->t_pre_paid_amount + $total->t_commission_amount + $total->t_under_writing_fee+$total->t_up_sell_commission))
            ->with('gt_commission', \FFM::dollar($total->t_commission_amount+$total->t_up_sell_commission))
            //->with('gt_up_sell_commission', \FFM::dollar($total->t_up_sell_commission))
            ->with('gt_funded', \FFM::dollar($total->t_total_amount))
            ->with('gt_rtr', \FFM::dollar($total->t_invest_rtr))
            ->with('gt_magnt', \FFM::dollar($total->t_mgmnt_fee))
            ->with('gt_underwritter', \FFM::dollar($total->t_under_writing_fee))
            ->addIndexColumn()
            ->make(true);
    }

    public function investorReportView($merchants = null, $date_type = null, $advance_type = null, $merchant_date = null, $startDate = null, $endDate = null, $investors = null, $lenders = null, $columRequest = false, $stime = null, $etime = null, $type = null, $industries = null, $owner = null, $statuses = null, $investor_type = null, $substatus_flag = null, $label = null)
    {
        if ($columRequest) {
            return [['orderable' => false, 'searchable' => false, 'title' => '', 'data' => null, 'className' => 'details-control', 'defaultContent' => ''], ['orderable' => true, 'searchable' => true, 'title' => 'Merchant Id', 'data' => 'merchant_id', 'name' => 'merchant_id'], ['orderable' => true, 'searchable' => true, 'title' => 'Merchant Name', 'data' => 'Merchant', 'name' => 'Merchant'], ['orderable' => true, 'searchable' => true, 'title' => 'Funded Date', 'data' => 'date_funded', 'name' => 'date_funded'], ['orderable' => false, 'searchable' => true, 'title' => 'Funded Amount', 'data' => 'amount', 'name' => 'amount'], ['orderable' => false, 'searchable' => true, 'title' => 'RTR', 'data' => 'invest_rtr', 'name' => 'invest_rtr'], ['orderable' => false, 'searchable' => true, 'title' => 'Commission', 'data' => 'commission_amount', 'name' => 'commission_amount'], ['orderable' => false, 'searchable' => false, 'title' => 'Share (%)', 'data' => 'share_t', 'name' => 'share_t'], ['orderable' => false, 'searchable' => true, 'title' => 'Prepaid Payment', 'data' => 'pre_paid', 'name' => 'pre_paid'], ['orderable' => false, 'searchable' => false, 'title' => 'Total Invested', 'data' => 'total_payment_t', 'name' => 'total_payment_t'], ['orderable' => false, 'searchable' => true, 'title' => 'Under Writing Fee', 'data' => 'under_writing_fee', 'name' => 'under_writing_fee'], ['orderable' => false, 'searchable' => true, 'title' => 'Management Fee', 'data' => 'mgmnt_fee', 'name' => 'mgmnt_fee'], ['orderable' => true, 'searchable' => true, 'title' => 'Created On', 'data' => 'created_at', 'name' => 'created_at']];
        }
        $data = $this->merchant->searchForInvestorReportView($date_type, $advance_type, $lenders, $startDate, $endDate, $investors, $merchants, $stime, $etime, $type, $industries, $owner, $statuses, $investor_type, $substatus_flag, $label);
        $total = $data['total'];
        $data = $data['data'];

        return \DataTables::of($data)->setTotalRecords($total->count)->editColumn('Merchant', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->merchant_id)."'>$data->Merchant</a>";
        })->rawColumns(['Merchant'])->editColumn('commission_amount', function ($data) {
            return $data->commission_amount.'('.$data->commission.')';
        })->addColumn('total_payment_t', function ($data) {
            return $data->invested_amount;
        })
        ->editColumn('invest_rtr', function ($data) {
            return $data->i_rtr;
        })->addColumn('share_t', function ($data) {
            return FFM::percent($data->i_amount / $data->funded * 100);
        })->editColumn('amount', function ($data) {
            return '$'.\FFM::sr($data->i_amount);
        })->filterColumn('amount', function ($query, $keyword) {
            $sql = 'amount  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('invest_rtr', function ($query, $keyword) {
            $sql = 'invest_rtr  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('commission_amount', function ($query, $keyword) {
            $sql = 'commission_amount like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->with('gt_prepaid_amount', \FFM::dollar($total->t_pre_paid_amount))
            ->with('gt_total', \FFM::dollar($total->t_total_amount + $total->t_pre_paid_amount + $total->t_commission_amount + $total->t_under_writing_fee))
            ->with('gt_commission', \FFM::dollar($total->t_commission_amount))
            ->with('gt_funded', \FFM::dollar($total->t_total_amount))
            ->with('gt_rtr', \FFM::dollar($total->t_invest_rtr))
            ->with('gt_magnt', \FFM::dollar($total->t_mgmnt_fee))
            ->with('gt_underwritter', \FFM::dollar($total->t_under_writing_fee))
            ->make(true);
    }

    public function getNetZeroInterest($input_array = [])
    {
        $interest_rate = $input_array->interest_rate;
        $total_invested_amount = $interest = $total_invested_amount_with_interest = $total_interest_amount = $percentage = 0;
        $paid_to_participant = array_sum(array_column($input_array->participantPayment->toArray(), 'participant_share'));
        foreach ($input_array->investmentData as $investor) {
            $merchant_data = merchant::where('id', $investor->merchant_id)->first();
            $sub_status = $merchant_data->sub_status_id;
            $commission = $merchant_data->commission;
            $from = Carbon::createFromFormat('Y-m-d', $merchant_data->date_funded);
            $invested_amount = $investor->pre_paid + $investor->amount + ($investor->amount * $commission / 100);
            $total_invested_amount = $total_invested_amount + $invested_amount;
            if ($sub_status != 11) {
                $to = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            } else {
                $to = ParticipentPayment::where('user_id', $investor->user_id)->where('merchant_id', $investor->merchant_id)->orderByDesc('id')->value('payment_date');
                $to = (isset($to) ? $to : date('Y-m-d')).' 23:59:59';
                $to = Carbon::createFromFormat('Y-m-d H:i:s', $to);
            }
            $diff_in_days = $from->diffInDays($to);
            $interest = ($interest_rate / (365 * 100)) * $diff_in_days;
            $invested_amount_with_interest = $invested_amount + $invested_amount * $interest;
            $total_invested_amount_with_interest = $total_invested_amount_with_interest + $invested_amount_with_interest;
        }
        $paid_to_participant = array_sum(array_column($input_array->participantPayment->toArray(), 'participant_share'));
        $net_value = $paid_to_participant - $total_invested_amount;
        $net_value_with_interest = $paid_to_participant - $total_invested_amount_with_interest;
        $total_credit = array_sum(array_column($input_array->investorTransactions->toArray(), 'amount'));
        $limi_interest = $total_credit * ($interest_rate / (100 * 12));
        $total_net_interest_difference = $net_value - $net_value_with_interest;
        if ($total_net_interest_difference > $limi_interest) {
            $percentage = ($total_net_interest_difference > 0) ? ($limi_interest / $total_net_interest_difference) * 100 : 0;
        }
        $net_zero_with_limited_interest = ($percentage > 0) ? $net_value + ($net_value - $net_value_with_interest) * ($percentage / 100) : $net_value_with_interest;
        $result_arr['net_zero'] = $net_value;
        $result_arr['total_credit'] = $total_credit;
        $result_arr['investor_interest_rate'] = $interest_rate;
        $result_arr['net_zero_with_interest'] = $net_value_with_interest;
        $result_arr['net_zero_with_limited_interest'] = $net_zero_with_limited_interest;

        return $result_arr;
    }

    public function getNetZeroBasedDataTotal($merchant_val = [])
    {
        $result_arr = $dates = [];
        $total_interest_amount = $paid_to_participant = $ctd_sum = $total_payment = $invested_amount = $total_invested_amount_with_interest = $total_invested_amount = $total_amount = 0;
        $investor_array = array_map('unserialize', array_unique(array_map('serialize', $merchant_val->investmentData->toArray())));
        $sub_status = $merchant_val->sub_status_id;
        $from = Carbon::createFromFormat('Y-m-d', $merchant_val->date_funded);
        foreach ($merchant_val->investmentData as $investor) {
            $invested_amount = $investor->pre_paid + $investor->amount + ($investor->amount * $merchant_val->commission / 100);
            $total_invested_amount = $total_invested_amount + $invested_amount;
            if ($sub_status != 11) {
                $to = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            } else {
                $to = ParticipentPayment::where('user_id', $investor->user_id)->where('merchant_id', $merchant_val->id)->orderByDesc('id')->value('payment_date').'23:59:59';
                $to = ($to ? $to : date('Y-m-d').' 23:59:59');
                $to = Carbon::createFromFormat('Y-m-d H:i:s', $to);
            }
            $diff_in_days = $from->diffInDays($to);
            $interest_rate = $investor->interest_rate;
            $interest = ($interest_rate / (365 * 100)) * $diff_in_days;
            $total_interest_amount = $total_interest_amount + $invested_amount * $interest;
            $invested_amount_with_interest = $invested_amount + $invested_amount * $interest;
            $total_invested_amount_with_interest = $total_invested_amount_with_interest + $invested_amount_with_interest;
        }
        $paid_to_participant = array_sum(array_column($merchant_val->participantPayment->toArray(), 'participant_share'));
        $net_value = $paid_to_participant - ($total_invested_amount);
        $net_value_with_interest = $paid_to_participant - $total_invested_amount_with_interest;
        $percentage = $total_interest = $total_net_interest_difference = 0;
        if (count($investor_array) > 0) {
            foreach ($investor_array as $investor) {
                $total_credit = $investor['total_credit_amount'];
                $interest_value = $total_credit * ($investor['interest_rate'] / (100 * 12));
                $total_interest = $total_interest + $interest_value;
            }
            $total_net_interest_difference = $net_value - $net_value_with_interest;
            if ($total_net_interest_difference > $total_interest) {
                $percentage = ($total_net_interest_difference > 0) ? ($total_interest / $total_net_interest_difference) * 100 : 0;
            }
        }
        $net_zero_with_limited_interest = ($percentage > 0) ? $net_value + ($net_value - $net_value_with_interest) * ($percentage / 100) : $net_value_with_interest;
        $result_arr['net_zero'] = $net_value;
        $result_arr['net_zero_with_interest'] = $net_value_with_interest;
        $result_arr['net_zero_with_limited_interest'] = $net_zero_with_limited_interest;
        $result_arr['total_interest_amount'] = $total_interest_amount;
        $result_arr['limited_interest'] = ($net_value_with_interest - $net_value) * ($percentage / 100);

        return $result_arr;
    }

    public function documentsViewByInvestor($investorId, $column = false)
    {
        if ($column) {
            return [['data' => 'id', 'name' => 'id', 'title' => 'Id'], ['data' => 'title', 'name' => 'title', 'title' => 'Title', 'orderable' => false, 'searchable' => false], ['data' => 'document_type', 'name' => 'document_type', 'title' => 'Document type', 'orderable' => false, 'searchable' => false], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Upload Date']];
        }
        $documentTypes = DocumentType::pluck('name', 'id');
        $data = InvestorDocuments::select('id', 'created_at', 'document_type_id', 'title', 'file_name')->where('investor_id', $investorId);

        return \DataTables::eloquent($data)->addColumn('document_type', function ($data) use ($documentTypes) {
            return Form::select('type', $documentTypes, $data->document_type_id, ['id' => 'type_'.$data->id]);
        })->editColumn('created_at', function ($data) {
            return $data->created_at->toFormattedDateString();
        })->editColumn('title', function ($data) {
            return Form::text('title', $data->title, ['id' => "title_{$data->id}"]);
        })->removeColumn('document_type_id')->make(true);
    }

    public function getMerchantLiquidityLogDetails($start_date, $end_date, $merchants, $investors, $owner, $description, $groupbypay = null, $accountType = [User::INVESTOR_ROLE], $column = false,$velocity_owned = false)
    {
        if ($column) {
            return [['orderable' => false, 'data' => 'id', 'name' => 'id', 'title' => '#', 'searchable' => false], ['data' => 'merchants.name', 'name' => 'merchants.name', 'title' => 'Merchant Name', 'orderable' => false], ['data' => 'liquidity_log.created_at', 'name' => 'liquidity_log.created_at', 'title' => 'Date', 'orderable' => false], ['data' => 'liquidity_log.description', 'name' => 'liquidity_log.description', 'title' => 'Description', 'orderable' => false], ['data' => 'liquidity_log.liquidity_change', 'name' => 'liquidity_log.liquidity_change', 'title' => 'Amount', 'orderable' => false], ['data' => 'aggregated_liquidity', 'name' => 'aggregated_liquidity', 'title' => 'Aggregated Liquidity', 'orderable' => false, 'searchable' => false]];
        }
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;

        $t_liquidity_change = 0;
        $user_ids = array();
        if (is_array($investors)) {
            if(!empty($investors)){
            $user_ids = User::whereIn('users.id',$investors);
            if($velocity_owned){
                $user_ids = $user_ids->where('velocity_owned',1);   
            }
            $user_ids = $user_ids->pluck('users.id')->toArray();
            }
        }
        $log_data = MerchantLiquidityLogView::orderByDesc('id');
        if (is_array($merchants)) {
            $log_data = $log_data->whereIn('merchant_id', $merchants);
        }
        if (is_array($user_ids)) {
            if (!empty($user_ids)) {
                $log_data = $log_data->whereIn('member_id', $user_ids);
            }
        }
        if (is_array($description)) {
            $log_data = $log_data->whereIn('description', $description);
        }
        if ($start_date) {
            $start_date = ET_To_UTC_Time($start_date.' 00:00', 'datetime');
            $log_data = $log_data->where('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $end_date = ET_To_UTC_Time($end_date.' 23:59', 'datetime');
            $log_data = $log_data->where('created_at', '<=', $end_date);
        }
        if ($owner && $owner != '') {
            $company_users_owner = User::whereIn('company', $owner)->pluck('id');
            $log_data = $log_data->whereIn('user_id', $company_users_owner);
        }
        if ($accountType) {
            if($velocity_owned){

                $roleUsers = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');

                $roleUsers = $roleUsers->whereIn('user_has_roles.role_id', $accountType)->where('velocity_owned',1);

                $roleUsers = $roleUsers->pluck('model_id');

            }else{

                $roleUsers = DB::table('user_has_roles')->whereIn('role_id', $accountType)->pluck('model_id');

            }
            $log_data = $log_data->whereIn('user_id', $roleUsers);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $log_data = $log_data->where('company', $userId);
            } else {
                $log_data = $log_data->where('creator_id', $userId);
            }
        }
        if ($groupbypay == 'true') {
            $log_data = $log_data->whereNotNull('batch_id')->groupBy('batch_id', 'description');
            $log_data = $log_data->select('id', 'member_id', 'name_of_deal', 'member_type', 'merchant_id', 'investor_id', 'batch_id', 'description', DB::raw('sum(liquidity_change) as liquidity_change'), DB::raw('sum(final_liquidity) as final_liquidity'), DB::raw('(aggregated_liquidity) as aggregated_liquidity'), 'created_at', 'merchant_name', 'creator_id', 'merchant_deleted_at', 'liquidity_creator');

        } else {
            $log_data = $log_data->select('*');
        }
        $total_change = $log_data->pluck('liquidity_change')->toArray();
        $t_liquidity_change = array_sum($total_change);

        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($log_data)->editColumn('liquidity_log.liquidity_change', function ($log_data) {
            return \FFM::dollar($log_data['liquidity_change']);
        })->editColumn('liquidity_log.description', function ($log_data) {
            return $log_data['description'];
        })->editColumn('liquidity_log.created_at', function ($log_data) {
            $created_date = 'Created On '.\FFM::datetime($log_data['created_at']).' by '.get_user_name_with_session($log_data['liquidity_creator']);

            return "<a title='$created_date'>".FFM::datetime($log_data['created_at']).'</a>';
        })->addColumn('aggregated_liquidity', function ($log_data) use ($groupbypay) {
            $var_liquidity = '';
            if ($groupbypay == 'true') {
                $data = LiquidityLog::select('aggregated_liquidity')->where('batch_id', $log_data->batch_id)->orderByDesc('id')->first()->toArray();
                $array1 = (array) json_decode($data['aggregated_liquidity']);
                foreach ($array1 as $key => $value) {
                    if (isset($value->company)) {
                        $var_liquidity .= '<span class="label label-primary">'.get_user_name_with_session($value->company).'='.FFM::dollar($value->liquidity).'</span> ';
                    } else {
                        $var_liquidity .= \FFM::dollar($value);
                    }
                }
            } else {
                $array = (array) json_decode($log_data->aggregated_liquidity);
                if (is_array($array)) {
                    foreach ($array as $key => $value) {
                        if (isset($value->company)) {
                            $var_liquidity .= '<span class="label label-primary">'.get_user_name_with_session($value->company).'='.FFM::dollar($value->liquidity).'</span> ';
                        } else {
                            $var_liquidity .= \FFM::dollar($value);
                        }
                    }
                }
            }

            return $var_liquidity;
        })->addColumn('merchants.name', function ($log_data) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $log_data['created_at'])->format('Y-m-d');
            $url = \URL::to('/admin/merchants/view', $log_data['merchant_id']);
            $name = ($log_data['name_of_deal'] == 'Payment') ? "<a target='_blank' href='".route('admin::reports::liquidity-log', ['merchant_id' => $log_data['member_id'], 'date' => $date])."'>".(isset($log_data['merchant_name']) ? strtoupper($log_data['merchant_name']) : '').'</a>' : (isset($log_data['merchant_name']) ? "<a target='_blank' href='".$url."'>".strtoupper($log_data['merchant_name']).'</a>' : '-');
            if ($log_data['name_of_deal'] != 'Payment' && isset($log_data['merchant_deleted_at'])) {
                // Deleted merchant, show only name here
                $name = strtoupper($log_data['merchant_name']);
            }

            return $name;
        })->filterColumn('merchants.name', function ($query, $keyword) {
            $sql = 'merchant_name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('liquidity_log.liquidity_change', function ($query, $keyword) {
            $sql = 'liquidity_change  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('liquidity_log.description', function ($query, $keyword) {
            $sql = 'description  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('liquidity_log.created_at', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(created_at,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })->rawColumns(['name', 'aggregated_liquidity', 'merchants.name', 'liquidity_log.created_at'])
        ->with('Total', 'Total:')->with('t_liquidity_change', FFM::dollar($t_liquidity_change))
        ->setTotalRecords(count($total_change))
        ->make(true);
    }
    public function getLiquidityLogExportDetails($start_date = null, $end_date = null, $merchant = null, $investor = null, $groupbypay = null, $owner = null, $description = null, $label = null,$accountType = [User::INVESTOR_ROLE],$velocity_owned = false)
    {
        $log_data = $this->log->liquidiyLogReport($start_date, $end_date, $merchant, $investor, $groupbypay, $owner, $description, $label, null, $accountType,$velocity_owned);
        $log_data = $log_data->get();
        return $log_data;
    }

    public function getLiquidityLogDetails($start_date = null, $end_date = null, $merchant = null, $investor = null, $groupbypay = null, $owner = null, $description = null, $label = null, $column = false, $search_key = null, $accountType = [User::INVESTOR_ROLE],$velocity_owned = false)
    {
        if ($column) {
            return [['data' => 'id', 'name' => 'id', 'title' => '#', 'orderable' => false, 'searchable' => false], ['data' => 'user_name', 'name' => 'user_name', 'defaultContent' => '', 'title' => 'Investor', 'orderable' => false], ['data' => 'merchant_name', 'name' => 'merchant_name', 'title' => 'Merchant', 'orderable' => false], ['data' => 'description', 'name' => 'description', 'title' => 'Description', 'orderable' => false], ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Date', 'orderable' => false], ['data' => 'liquidity_change', 'name' => 'liquidity_change', 'title' => 'Liquidity Change', 'orderable' => false], ['data' => 'final_liquidity', 'name' => 'final_liquidity', 'title' => 'Investor Liquidity', 'orderable' => false], ['data' => 'company_aggregated_liquidity', 'name' => 'company_aggregated_liquidity', 'title' => 'Company Aggregated Liquidity', 'orderable' => false, 'searchable' => false], ['data' => 'total_aggregated_liquidity', 'name' => 'total_aggregated_liquidity', 'title' => 'Aggregated Liquidity', 'orderable' => false, 'searchable' => false]];
        }
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $t_liquidity_change = 0;
        $log_data = $this->log->liquidiyLogReport($start_date, $end_date, $merchant, $investor, $groupbypay, $owner, $description, $label, $search_key, $accountType,$velocity_owned);
        $total_change = $log_data->pluck('liquidity_change')->toArray();
        $t_liquidity_change = array_sum($total_change);
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($log_data)->rawColumns(['company_aggregated_liquidity', 'user_name', 'merchant_name', 'created_at'])->editColumn('user_name', function ($log_data) use ($groupbypay, $investor, $userId, $permission,$velocity_owned) {
            $investors = LiquidityLogView::where('batch_id', $log_data->batch_id)->where('liquidity_change', '!=', 0);
            if ($groupbypay == 'true') {
                $user_ids = array();
                if (is_array($investor)) {
                    if(!empty($investor)){
                    $user_ids = User::whereIn('users.id',$investor);
                    if($velocity_owned){
                        $user_ids = $user_ids->where('velocity_owned',1);   
                    }
                    $user_ids = $user_ids->pluck('users.id')->toArray();
                    }
                }
                if (is_array($user_ids)) {
                    if(!empty($user_ids)){
                    $investors = $investors->whereIn('member_id', $user_ids);
                    }
                }
                if ($log_data->merchant_id > 0) {
                    $investors = $investors->where('merchant_id', $log_data->merchant_id);
                }
                if ($log_data->description != null) {
                    $investors = $investors->where('description', $log_data->description);
                }
                if (empty($permission)) {
                    if (Auth::user()->hasRole(['company'])) {
                        $investors = $investors->where('company', $userId);
                    } else {
                        $investors = $investors->where('creator_id', $userId);
                    }
                }
                if (is_array($investor)) {
                    $investors = $investors->whereIn('member_id', $investor);
                }
                $investors = $investors->pluck('member_id')->toArray();
                $investors = array_unique($investors);
                $inv_arry = '';
                $investor_count = '('.count($investors).') investors';
                $total_investors = '<a href="#" class="btn btn-xs btn-success" style="float:right" onclick="investor_list('.$log_data->batch_id.','.$log_data->merchant_id.','.$log_data->id.');" data-toggle="modal">'.$investor_count.'</a>';

                return $total_investors;
            } else {
                if ($log_data->user_name) {
                    $investors = strtoupper($log_data->user_name);
                } elseif ($log_data->investor_id) {
                    $investors = strtoupper(get_user_name_with_session($log_data->investor_id));
                } else {
                    $investors = '';
                }

                return $investors;
            }
        })->editColumn('final_liquidity', function ($log_data) {
            return \FFM::dollar($log_data->final_liquidity);
        })->editColumn('liquidity_change', function ($log_data) {
            return \FFM::dollar($log_data->liquidity_change);
        })->editColumn('description', function ($log_data) {
            return $log_data->description;
        })->editColumn('created_at', function ($log_data) {
            $created_date = 'Created On '.\FFM::datetime($log_data->created_at).' by '.get_user_name_with_session($log_data->liquidity_creator);

            return "<a title='$created_date'>".\FFM::datetime($log_data->created_at).'</a>';
        })->addColumn('company_aggregated_liquidity', function ($log_data) use ($groupbypay) {
            $var_liquidity = '';
            if ($groupbypay == 'true') {
                $data = LiquidityLog::select('aggregated_liquidity')->where('batch_id', $log_data->batch_id)->orderByDesc('id')->first()->toArray();
                $array1 = (array) json_decode($data['aggregated_liquidity']);
                foreach ($array1 as $key => $value) {
                    if (isset($value->company)) {
                        $var_liquidity .= '<span class="label label-primary">'.get_user_name_with_session($value->company).'='.FFM::dollar($value->liquidity).'</span> ';
                    } else {
                        $var_liquidity = \FFM::dollar($value);
                    }
                }
            } else {
                $array = (array) json_decode($log_data->aggregated_liquidity);
                if (is_array($array)) {
                    foreach ($array as $key => $value) {
                        if (isset($value->company)) {
                            $var_liquidity .= '<span class="label label-primary">'.get_user_name_with_session($value->company).'='.FFM::dollar($value->liquidity).'</span> ';
                        } else {
                            $var_liquidity = \FFM::dollar($value);
                        }
                    }
                }
            }

            return $var_liquidity;
        })->addColumn('total_aggregated_liquidity', function ($log_data) use ($groupbypay) {
            $var_liquidity = 0;
            if ($groupbypay == 'true') {
                $data = LiquidityLog::select('aggregated_liquidity')->where('batch_id', $log_data->batch_id)->orderByDesc('id')->first()->toArray();
                $array1 = (array) json_decode($data['aggregated_liquidity']);
                foreach ($array1 as $key => $value) {
                    if (isset($value->company)) {
                        $var_liquidity = $var_liquidity + $value->liquidity;
                    } else {
                        $var_liquidity = $var_liquidity + $value;
                    }
                }
            } else {
                $array = (array) json_decode($log_data->aggregated_liquidity);
                if (is_array($array) && isset($value->liquidity)) {
                    foreach ($array as $key => $value) {
                        if (isset($value->liquidity)) {
                            $var_liquidity = $var_liquidity + $value->liquidity;
                        } else {
                            $var_liquidity = $var_liquidity + $value;
                        }
                    }
                }
            }

            return \FFM::dollar($var_liquidity);
        })->addColumn('merchant_name', function ($log_data) {
            return isset($log_data->merchant_deleted_at) ? strtoupper($log_data->merchant_name) : "<a target='blank' href='".\URL::to('/admin/merchants/view', $log_data->merchant_id)."'>".strtoupper($log_data->merchant_name)."</a>";
        })->filterColumn('created_at', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(created_at,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })->filterColumn('description', function ($query, $keyword) {
            $sql = 'description like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('liquidity_change', function ($query, $keyword) {
            $sql = 'liquidity_change like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('liquidity', function ($query, $keyword) {
            $sql = 'liquidity like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('merchant_name', function ($query, $keyword) {
            $sql = 'merchant_name like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('user_name', function ($query, $keyword) {
            $sql = 'user_name like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->with('Total', 'Total:')->with('t_liquidity_change', FFM::dollar($t_liquidity_change))
        ->setTotalRecords(count($total_change))
        ->make(true);
    }

    public function getMerchantStatusLog($start_date = null, $end_date = null, $merchant = null, $status_id = null, $column = false)
    {
        if ($column) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => '#', 'orderable' => false, 'searchable' => false],
                ['data' => 'merchants.name', 'name' => 'merchants.name', 'defaultContent' => '', 'title' => 'Merchant Name'],
                ['data' => 'old_status', 'name' => 'old_status', 'defaultContent' => '', 'title' => 'Old Status'],
                ['data' => 'current_status', 'name' => 'current_status', 'title' => 'Current Status'],
                ['data' => 'description', 'name' => 'description', 'title' => 'Description'],
                ['data' => 'merchant_status_log.created_at', 'name' => 'merchant_status_log.created_at', 'title' => 'Date'],
            ];
        }
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            if (! Auth::user()->hasRole(['company'])) {
                $subadmininvestor = $investor->where('creator_id', $userId);
            } else {
                $subadmininvestor = $investor;
            }
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $data = MerchantStatusLog::select('old_status', 'current_status', 'merchants.name as merchant_name', 'merchant_status_log.created_at', 'description', 'merchant_status_log.id as merchant_log_id', 'merchant_status_log.id', 'merchant_status_log.merchant_id', 'sub_statuses.name', 'merchant_status_log.creator_id', 'merchants.deleted_at as merchant_deleted_at')->join('merchants', 'merchant_status_log.merchant_id', 'merchants.id')->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->groupBy('merchant_status_log.id')->orderByDesc('merchant_status_log.created_at');
        if (empty($permission)) {
            $data = $data->whereIn('merchant_user.user_id', $subinvestors);
        }
        if (is_array($merchant)) {
            $data = $data->whereIn('merchant_status_log.merchant_id', $merchant);
        }
        if (($status_id)) {
            $data = $data->whereIn('merchant_status_log.current_status', $status_id);
        }
        if ($start_date) {
            $start_date = ET_To_UTC_Time($start_date.' 00:00', 'datetime');
            $data = $data->where('merchant_status_log.created_at', '>=', $start_date);
        }
        if ($end_date) {
            $end_date = ET_To_UTC_Time($end_date.' 23:59', 'datetime');
            $data = $data->where('merchant_status_log.created_at', '<=', $end_date);
        }
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($data)->editColumn('merchants.name', function ($data) {
            return isset($data->merchant_deleted_at) ? strtoupper($data->merchant_name) : "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->merchant_id)."'>".strtoupper($data->merchant_name)."</a>";
        })->editColumn('old_status', function ($data) {
            $substatus = SubStatus::select('name')->where('id', $data->old_status)->first()->toArray();

            return $substatus['name'];
        })->editColumn('current_status', function ($data) {
            $substatus = SubStatus::select('name')->where('id', $data->current_status)->first()->toArray();

            return $substatus['name'];
        })->editColumn('description', function ($data) {
            return $data->description;
        })->addColumn('merchant_status_log.created_at', function ($data) {
            $creator_ = get_user_name_with_session($data->creator_id);
            if (!$data->creator_id && strpos($data->description, 'by system') !== false) {
                $creator_ = 'system';
            }
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.$creator_;

            return "<a title='$created_date'>".FFM::datetime($data->created_at).'</a>';
        })->filterColumn('merchant_status_log.created_at', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(merchant_status_log.created_at,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })->filterColumn('current_status', function ($query, $keyword) {
            $id = SubStatus::where('name', 'like', '%'.$keyword.'%')->value('id');
            $sql = 'current_status like ?';
            $query->where('current_status', $id);
        })->filterColumn('old_status', function ($query, $keyword) {
            $id = SubStatus::where('name', 'like', '%'.$keyword.'%')->value('id');
            $sql = 'sub_statuses.name like ?';
            $query->where('old_status', $id);
        })->rawColumns(['merchants.name', 'merchant_status_log.created_at'])->make(true);
    }

    public function paymentData($date_type = null, $sDate = null, $eDate = null, $ids = null, $userIds = null, $lids = null, $payment_type = null, $owner = null, $sub_statuses = null, $investor_type = null, $fields = null, $rcode = null, $overpayment = null, $label = null, $export_checkbox = null, $mode_of_payment = null, $payout_frequency = null, $investor_label = null, $advance_type = null, $historic_status = null, $filter_by_agent_fee = null,$active=null,$transaction_id=null,$export_individual_checkbox=0,$velocity_owned = false)
    {
        if ($payment_type != null) {
            $payment_type = ($payment_type == 'credit') ? '1' : '0';
        }
        $investors = $userIds;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $end_date_query = '';
        $date_query = '';
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        if($active == 1){
            $company_users_q = $company_users_q->where('active_status', 1);
        }
        if($active == 2){
            $company_users_q = $company_users_q->where('active_status', 0);
        }
        if ($owner) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }
        if($velocity_owned){
            $company_users_q = $company_users_q->where('velocity_owned', 1);
        }
        // if ($filter_by_agent_fee == 1) {
        //     $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        //     $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        //     $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
        //     if ($AgentFeeAccount) {
        //         $company_users_q = $company_users_q->where('id', $AgentFeeAccount->id);
        //     }
        // }

        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
        if ($AgentFeeAccount) {
            $company_users_q = $company_users_q->where('id', '<>', $AgentFeeAccount->id);
        }

        if ($payout_frequency) {
            $company_users_q = $company_users_q->whereIn('notification_recurence', $payout_frequency);
        }
        if ($investor_label != null) {
            $investor_label = implode(',', $investor_label);
            $company_users_q = $company_users_q->whereRaw('json_contains(label, \'['.$investor_label.']\')');
        }
        $company_users_filter  = '';
        $previous_transaction_id = '';
        $company_users = $company_users_q->pluck('id')->toArray();
        $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'participent_payments.payment_date';
        if($transaction_id){
            $previous_transaction_id = "AND participent_payments.id < $transaction_id";
        } else {
            if ($sDate) {
                $end_date_query = " AND  $table_field < '$sDate' ";
            } else {
                $end_date_query = " AND  $table_field <= '1970-01-01' ";
            }
        }
        if ($sDate) {
            $date_query = " AND  $table_field >= '$sDate' ";
        }
        if ($company_users) {
            $user_ids2 = implode(', ', $company_users);
            $company_users_filter = " AND payment_investors.user_id IN ($user_ids2)";
        }
        if($eDate){
$date_query .= " AND  $table_field <= '$eDate' ";
        }
        $user_filter = '';
        if ($company_users) {
            $user_ids2 = implode(', ', $company_users);
            $user_filter = " AND payment_investors.user_id IN ($user_ids2)";
        }

        $merchants = Merchant::leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')
        ->with(['investments' => function ($query) use ($permission, $company_users, $overpayment) {
            $query->select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'),
            DB::raw('sum((merchant_user.amount*old_factor_rate)) as settled_rtr'),
            DB::raw('sum(merchant_user.actual_paid_participant_ishare) as paid_participant_ishare'),
            DB::raw('sum(merchant_user.mgmnt_fee) as total_mgmnt_fee'),
            DB::raw('sum(merchant_user.amount) as funded_amount'),
            DB::raw('sum(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid+ merchant_user.under_writing_fee+merchant_user.up_sell_commission) as invested_amount'),
            DB::raw('sum(merchant_user.invest_rtr) as total_rtr'),
            DB::raw('sum(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as mgmnt_fee_amount')
            )->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
            if ($overpayment == 1) {
                $query->whereColumn('paid_participant_ishare', '>', 'invest_rtr');
            }
            $query->groupBy('merchant_user.merchant_id');
            if (! empty($company_users)) {
                $query->whereIn('merchant_user.user_id', $company_users);
            }
        }])->select(
            'merchants.id',
            'merchants.name',
            'sub_statuses.name as sub_status_name',
            'sub_statuses.id as sub_status_id',
            'merchants.last_payment_date',
            'rcode.code',
            'merchants.last_rcode',
            'rtr',
            'payment_amount',
            'pmnts',
            'complete_percentage',
            'date_funded',
            'm_mgmnt_fee',
            'm_syndication_fee',

            DB::raw('(SELECT SUBSTRING_INDEX(GROUP_CONCAT(payment ORDER BY payment_date DESC), ",", 1) FROM participent_payments WHERE merchants.id = participent_payments.merchant_id and payment_type=1 AND payment > 0 and participent_payments.is_payment = 1 and rcode=0 GROUP BY participent_payments.merchant_id ORDER BY payment_date DESC limit 1) last_payment_amount'),
            DB::raw("(select sum(payment_investors.actual_participant_share-payment_investors.mgmnt_fee) from payment_investors INNER JOIN participent_payments ON participent_payments.id=payment_investors.participent_payment_id WHERE merchants.id = participent_payments.merchant_id  $end_date_query $previous_transaction_id $company_users_filter)  as full_final_participant_share"),
            DB::raw("(select sum(payment_investors.agent_fee) from payment_investors INNER JOIN participent_payments ON participent_payments.id=payment_investors.participent_payment_id WHERE merchants.id = participent_payments.merchant_id  $end_date_query $previous_transaction_id $company_users_filter)  as t_agent_fee"),
            DB::raw("(select sum(payment_investors.actual_participant_share) from payment_investors INNER JOIN participent_payments ON participent_payments.id=payment_investors.participent_payment_id WHERE merchants.id = participent_payments.merchant_id  $end_date_query $previous_transaction_id $company_users_filter)  as final_participant_share_with_fee"),

          

            DB::raw("(select sum(payment_investors.overpayment) from payment_investors where payment_investors.merchant_id=merchants.id $user_filter ) as overpayment"),


            DB::raw("(select sum(payment_investors.agent_fee) from payment_investors INNER JOIN participent_payments ON participent_payments.id=payment_investors.participent_payment_id WHERE merchants.id = participent_payments.merchant_id  $date_query $user_filter)  as agent_fee"),

            // DB::raw("(select sum(payment_investors.agent_fee) from payment_investors where payment_investors.merchant_id=merchants.id $user_filter ) as agent_fee"),

            )->groupBy('merchants.id')
            ->whereHas('participantPayment', function ($q) use ($sDate, $eDate, $ids, $userIds, $date_type, $payment_type, $permission, $company_users, $rcode, $overpayment, $mode_of_payment, $historic_status,$transaction_id,$export_individual_checkbox) {
                $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
                $q->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
                $q->where('participent_payments.is_payment', 1);

                if (! empty($rcode) && is_array($rcode)) {
                    $q->whereIn('participent_payments.rcode', $rcode);
                }
                if ($transaction_id != null) {
                     $q->where('participent_payments.id', $transaction_id);
                }
                if ($mode_of_payment != null) {
                    if ($mode_of_payment == 'ach') {
                        $q->where('mode_of_payment', 1);
                    }
                    if ($mode_of_payment == 'manual') {
                        $q->where('mode_of_payment', 0);
                    }
                    if ($mode_of_payment == 'credit_card') {
                        $q->where('mode_of_payment', 2);
                    }
                }
                if ($overpayment == 1) {
                    $q->where('payment_investors.overpayment', '!=', 0);
                }
                 if($export_individual_checkbox==1){
                    $q->groupBy('payment_investors.participent_payment_id');
                }else{
                    $q->groupBy('payment_investors.id');
                }
                $q->select(
                'participent_payment_id',
                'participent_payments.rcode',
                'participent_payments.mode_of_payment',
                'user_id',
                'participent_payments.payment',
                'participent_payments.merchant_id',
                'participent_payments.id',
                'participent_payments.payment_date',
                'rcode.code',
                DB::raw('sum(payment_investors.profit) AS profit'),
                DB::raw('sum(payment_investors.principal) AS principal'),
                DB::raw('sum(payment_investors.mgmnt_fee) AS mgmnt_fee'),
                //DB::raw('sum(payment_investors.agent_fee) AS agent_fee'),
                DB::raw('sum(payment_investors.actual_participant_share) AS participant_share'),
                DB::raw('sum(payment_investors.actual_participant_share-mgmnt_fee) AS final_participant_share')
            );
                $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'participent_payments.payment_date';
                if (empty($permission)) {
                }
                if ($eDate) {
                    $q->where($table_field, '<=', $eDate);
                }
                if ($sDate) {
                    $q->where($table_field, '>=', $sDate);
                }
                if ($payment_type != null) {
                    $q->where('payment_type', $payment_type);
                }
                $q->orderByDesc('participent_payment_id');
                $q->whereIn('payment_investors.user_id', $company_users);
            })->with(['participantPayment' => function ($q) use ($sDate, $eDate, $ids, $userIds, $date_type, $payment_type, $permission, $company_users, $end_date_query,$company_users_filter, $rcode, $overpayment, $mode_of_payment, $historic_status,$transaction_id,$export_individual_checkbox) {
                $q->where('participent_payments.is_payment', 1);
                $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
                $q->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
                if (! empty($rcode) && is_array($rcode)) {
                    $q->whereIn('participent_payments.rcode', $rcode);
                }
                if ($transaction_id != null) {
                     $q->where('participent_payments.id', $transaction_id);
                }
                if ($mode_of_payment != null) {
                    if ($mode_of_payment == 'ach') {
                        $q->where('mode_of_payment', 1);
                    }
                    if ($mode_of_payment == 'manual') {
                        $q->where('mode_of_payment', 0);
                    }
                    if ($mode_of_payment == 'credit_card') {
                        $q->where('mode_of_payment', 2);
                    }
                }

                if ($overpayment == 1) {
                    $q->where('payment_investors.overpayment', '!=', 0);
                }
                if($export_individual_checkbox==1){
                    $q->groupBy('payment_investors.participent_payment_id');
                }else{
                    $q->groupBy('payment_investors.id');
                }
                
                $q->select('participent_payment_id', 'user_id', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.id', 'participent_payments.payment_date', DB::raw('sum(payment_investors.profit) as profit,sum(payment_investors.principal) as principal,sum(payment_investors.overpayment) as overpayment,
                      sum(payment_investors.mgmnt_fee) as mgmnt_fee,
                      sum(payment_investors.actual_participant_share) as participant_share'), 'rcode.code', DB::raw(' sum(payment_investors.actual_participant_share-mgmnt_fee) as final_participant_share'));
                $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'payment_date';
                if ($eDate) {
                    $q->where($table_field, '<=', $eDate);
                }
                if ($sDate) {
                    $q->where($table_field, '>=', $sDate);
                }
                if ($payment_type != null) {
                    $q->where('payment_type', $payment_type);
                }
                if ($transaction_id != null) {
                     $q->where('participent_payments.id', $transaction_id);
                }
                $q->orderByDesc('participent_payment_id');
                $q->whereIn('payment_investors.user_id', $company_users);
            }])
        ->with(['CarryForwardProfit' => function ($q) use ($sDate, $eDate, $company_users) {
            $q->groupBy('carry_forwards.merchant_id');
            $q->select('carry_forwards.merchant_id', DB::raw('sum(carry_forwards.amount) as carry_profit'));
            if ($eDate) {
                $q->where('carry_forwards.date', '<=', $eDate);
            }
            if ($sDate) {
                $q->where('carry_forwards.date', '>=', $sDate);
            }
            $q->orderByDesc('carry_forwards.date');
            $q->whereIn('carry_forwards.investor_id', $company_users);
        }]);
        $merchants = $merchants->where('merchants.active_status', 1);
        $merchants = $merchants->join('users as lender', 'merchants.lender_id', 'lender.id');
        //->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');
        if ($lids) {
            $merchants->whereIn('merchants.lender_id', $lids);
        }
        if ($label) {
            $merchants->whereIn('merchants.label', $label);
        }

        if ($historic_status != null && $eDate < date('Y-m-d')) {
            $merchants = $merchants->join('merchant_status_log', 'merchant_status_log.merchant_id', 'merchants.id')
            ->join('sub_statuses', 'sub_statuses.id', 'merchant_status_log.old_status');
            if ($sub_statuses) {
                $merchants = $merchants->whereIn('merchant_status_log.old_status', $sub_statuses);
            }
            $merchants = $merchants->where('merchant_status_log.old_status', function ($query) use ($sDate, $eDate, $sub_statuses) {
                $query->select('merchant_status_log.old_status')
                      ->from('merchant_status_log');
                if ($eDate) {
                    $query = $query->where('merchant_status_log.created_at', '>=', $eDate);
                }
                $query->whereRaw('merchants.id = merchant_status_log.merchant_id');
                $query = $query->orderBy('merchant_status_log.id', 'asc')
                      ->limit(1);
            });
        } else {
            $merchants = $merchants->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');

            if ($sub_statuses) {
                $merchants = $merchants->whereIn('sub_status_id', $sub_statuses);
            }
        }
        if (is_array($advance_type) && ! empty($advance_type)) {
            $merchants->whereIn('merchants.advance_type', $advance_type);
        }
       
        if ($ids != null) {
            $merchants = $merchants->whereIn('merchants.id', $ids);
        }
        $merchants = $merchants->orderByDesc('date_funded');

        return $merchants;
    }

    public function paymentLeftLatestDetails($merchant = null, $lenders = null, $sub_status = null, $late_payment = null, $company = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $array1 = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $userId = (Auth::user()->id);
            $userId = explode(',', $userId);
            $subadmininvestor = $investor->whereIn('creator_id', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $array1[] = $value->id;
            }
        }
        $company_investors = [];
        if ($company) {
            $company_investors = User::where('company', $company)->select('id')->get()->toArray();
        }
        $merchants = Merchant::select('merchants.id', 'merchants.name', 'rtr', 'payment_amount', 'pmnts', 'complete_percentage')->with(['participantPayment' => function ($q) use ($permission, $array1, $company, $company_investors) {
            $q->where('participent_payments.is_payment', 1);
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->groupBy('payment_investors.id');
            $q->select('participent_payment_id', 'user_id', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.id', 'participent_payments.payment_date', DB::raw('sum(payment_investors.participant_share) as participant_share'));
            if ($company != null) {
                $q->whereIn('payment_investors.user_id', $company_investors);
            }
            $q->orderByDesc('participent_payment_id');
            if (empty($permission)) {
                if ($array1 && is_array($array1)) {
                    $q->whereIn('payment_investors.user_id', $array1);
                }
            }
        }]);
        $merchants = $merchants->where('merchants.active_status', 1);
        $merchants = $merchants->join('users as lender', 'merchants.lender_id', 'lender.id');
        if (is_array($merchant)) {
            $merchants = $merchants->whereIn('merchants.id', $merchant);
        }
        if ($sub_status && is_array($sub_status)) {
            $merchants = $merchants->whereIn('merchants.sub_status_id', $sub_status);
        }
        if (is_array($lenders)) {
            $merchants = $merchants->whereIn('merchants.lender_id', $lenders);
        }
        if ($late_payment != null) {
            if ($late_payment != 90) {
                $start = $late_payment + 30;
                $end = $late_payment;
                $merchants = $merchants->where(function ($query) use ($start, $end) {
                    $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+'.$end.') DAY)')->whereRaw('date(last_payment_date) >=  DATE_SUB(NOW(), INTERVAL (lag_time+'.$start.') DAY)');
                });
            } else {
                $merchants = $merchants->where(function ($query) use ($late_payment) {
                    $query->whereRaw('date(last_payment_date) <=  DATE_SUB(NOW(), INTERVAL (lag_time+'.$late_payment.') DAY)');
                });
            }
        }

        return $merchants;
    }

    public function paymentLeftReport($merchant = null, $lenders = null, $sub_status = null, $late_payment = null, $company = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => '#', 'orderable' => false, 'searchable' => false],
                ['data' => 'merchants.name', 'name' => 'merchants.name', 'title' => 'Merchant', 'orderable' => true],
                ['data' => 'rtr', 'name' => 'rtr', 'title' => 'RTR (Balance with fee)', 'orderable' => false, 'searchable' => false],
                ['data' => 'payment_amount', 'name' => 'payment_amount', 'title' => 'Payment Amount', 'orderable' => false, 'searchable' => false],
                ['data' => 'overpayment', 'name' => 'overpayment', 'title' => 'Over Payment', 'orderable' => false, 'searchable' => false],
                ['data' => 'pmnts', 'name' => 'pmnts', 'title' => 'Total Payments', 'orderable' => false],
                ['data' => 'rtr_by_payment_amount', 'name' => 'rtr_by_payment_amount', 'title' => 'Actual Payment Left', 'orderable' => false, 'searchable' => false],
                ['data' => 'payments_left', 'name' => 'payments_left', 'title' => 'Payments Left', 'orderable' => false, 'searchable' => false],
            ];
        }
        $merchants = $this->paymentLeftDetails($merchant, $lenders, $sub_status, $late_payment, $company);

        return \DataTables::of($merchants)->editColumn('merchants.name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>";
        })->rawColumns(['merchants.name'])->editColumn('rtr', function ($data) {
            $rtr = ($data->investments->sum('invest_rtr') - $data->participantPayment->sum('participant_share'));

            return FFM::dollar(($rtr > 0) ? $rtr : 0);
        })->editColumn('payment_amount', function ($data) {
            return FFM::dollar($data->participantPayment->sum('participant_share'));
        })->editColumn('pmnts', function ($data) {
            return $data->pmnts;
        })->editColumn('overpayment', function ($data) {
            $overpayment = $data->participantPayment->sum('overpayment');

            return FFM::dollar($overpayment);
        })->editColumn('rtr_by_payment_amount', function ($data) {
            if ($data->investments->sum('invest_rtr') > 0) {
                $bal_rtr = $data->investments->sum('invest_rtr') - $data->participantPayment->sum('participant_share');
                $actual_payment_left = ($data->rtr) ? $bal_rtr / (($data->investments->sum('invest_rtr') / $data->rtr) * ($data->rtr / $data->pmnts)) : 0;
            } else {
                $actual_payment_left = 0;
            }
            $fractional_part = fmod($actual_payment_left, 1);
            $act_paymnt_left = floor($actual_payment_left);
            if ($fractional_part > .09) {
                $act_paymnt_left = $act_paymnt_left + 1;
            }

            return ($act_paymnt_left > 0) ? $act_paymnt_left : 0;
        })->editColumn('payments_left', function ($data) {
            $paid_payments = count(array_unique(array_column($data->participantPayment->toArray(), 'participent_payment_id')));
            $payment_left = $data->pmnts - $paid_payments;
            if ($data->complete_percentage > 99) {
                return 0;
            } else {
                return round(($payment_left > 0) ? $payment_left : 0);
            }
        })->filterColumn('merchants.name', function ($query, $keyword) {
            $sql = 'merchants.name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->addIndexColumn()->make(true);
    }

    public function paymentLeftDetails($merchant = null, $lenders = null, $sub_status = null, $late_payment = null, $company = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $array1 = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $userId = (Auth::user()->id);
            $companyId = (Auth::user()->id);
            $userId = explode(',', $userId);
            if (Auth::user()->hasRole(['company'])) {
                $subadmininvestor = $investor->where('company', $companyId);
            } else {
                $subadmininvestor = $investor->whereIn('creator_id', $userId);
            }
            foreach ($subadmininvestor as $key1 => $value) {
                $array1[] = $value->id;
            }
        }
        $company_investors = [];
        $company_investors = User::query();
        if ($company) {
            $company_investors = $company_investors->where('company', $company);
        }
        $company_investors = $company_investors->pluck('id')->toArray();
        $merchants = Merchant::with(['investments' => function ($query) use ($permission, $array1, $company, $company_investors) {
            $query->select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'))->whereIn('merchant_user.status', [1, 3]);
            $query->groupBy('merchant_id');
            if (empty($permission)) {
                $query->whereIn('merchant_user.user_id', $array1);
            }
            $query->whereIn('merchant_user.user_id', $company_investors);
        }])->whereHas('investmentData', function ($query) use ($permission, $array1, $company, $company_investors) {
            $query->select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'))->whereIn('merchant_user.status', [1, 3]);
            $query->groupBy('merchant_id');
            if (empty($permission)) {
                $query->whereIn('merchant_user.user_id', $array1);
            }
            if ($company != null) {
                $query->whereIn('merchant_user.user_id', $company_investors);
            }
        })->select('merchants.id', 'merchants.name', 'rtr', 'payment_amount', 'pmnts', 'complete_percentage')->with(['participantPayment' => function ($q) use ($permission, $array1, $company, $company_investors) {
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->groupBy('payment_investors.id');
            $q->select('participent_payment_id', 'user_id', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.id', 'participent_payments.payment_date', DB::raw('sum(payment_investors.actual_participant_share) as participant_share'), DB::raw('sum(payment_investors.actual_overpayment) as overpayment'));
            if ($company != null) {
                $q->whereIn('payment_investors.user_id', $company_investors);
            }
            $q->where('participent_payments.payment', '>', 0);
            $q->where('participent_payments.is_payment', 1);
            $q->where('participent_payments.rcode', '=', 0);
            $q->orderByDesc('participent_payment_id');
            if (empty($permission)) {
                $q->whereIn('payment_investors.user_id', $array1);
            }
        }]);
        $merchants = $merchants->where('merchants.active_status', 1);
        $merchants = $merchants->join('users as lender', 'merchants.lender_id', 'lender.id');
        if (is_array($merchant)) {
            $merchants = $merchants->whereIn('merchants.id', $merchant);
        }
        if ($sub_status && is_array($sub_status)) {
            $merchants = $merchants->whereIn('merchants.sub_status_id', $sub_status);
        }
        if (is_array($lenders)) {
            $merchants = $merchants->whereIn('merchants.lender_id', $lenders);
        }
        if ($late_payment != null) {
            if ($late_payment != 90) {
                $start = $late_payment + 30;
                $end = $late_payment;
                $merchants = $merchants->where(function ($query) use ($start, $end) {
                    $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+'.$end.') DAY)')->whereRaw('date(last_payment_date) >=  DATE_SUB(NOW(), INTERVAL (lag_time+'.$start.') DAY)');
                });
            } else {
                $merchants = $merchants->where(function ($query) use ($late_payment) {
                    $query->whereRaw('date(last_payment_date) <=  DATE_SUB(NOW(), INTERVAL (lag_time+'.$late_payment.') DAY)');
                });
            }
        }

        return $merchants;
    }

    public function delinquentRateReport($lenders = null, $industry = null, $company = null, $from_date = null, $to_date = null, $sub_status = null, $funded_date = null, $columRequest = false)
    {
        if ($columRequest) {
            return [['data' => 'id', 'name' => 'id', 'title' => '#', 'orderable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant', 'orderable' => false], ['data' => 'last_payment_date', 'name' => 'last_payment_date', 'title' => 'Last payment date'], ['data' => 'total_invested', 'name' => 'total_invested', 'title' => 'Total Invested', 'orderable' => false, 'searchable' => false], ['data' => 'net_ctd', 'name' => 'net_ctd', 'title' => 'Net amount paid', 'orderable' => false, 'searchable' => false], ['data' => 'principal_paid', 'name' => 'principal_paid', 'title' => 'Principal Paid', 'orderable' => false, 'searchable' => false], ['data' => 'profit_paid', 'name' => 'profit_paid', 'title' => 'Profit Paid', 'orderable' => false, 'searchable' => false], ['data' => 'principal_less', 'name' => 'principal_less', 'title' => 'Principal less Principal Paid', 'orderable' => false, 'searchable' => false], ['data' => 'profitp_less', 'name' => 'profitp_less', 'title' => 'Principal less Principal & Profit Paid', 'orderable' => false, 'searchable' => false], ['data' => 'lender', 'name' => 'lender', 'title' => 'Lender', 'orderable' => false, 'searchable' => false], ['data' => 'industry', 'name' => 'industry', 'title' => 'Industry', 'orderable' => false, 'searchable' => false]];
        }
        $total_invested_amount = 0;
        $merchants = $this->merchant->delinquentRateReport($lenders, $industry, $company, $from_date, $to_date, $funded_date);
        foreach ($merchants->get() as $key => $result) {
            $total_invested_amount = $total_invested_amount + $result['investments'][0]['total_invested'];
        }

        return \DataTables::of($merchants)->addColumn('profit_paid', function ($data) {
            $principal_paid = ($data->investments->sum('total_invested')) / ($data->investments->sum('invest_rtr')) * $data->participantPayment->sum('net_ctd');
            $profit_paid = $data->participantPayment->sum('net_ctd') - $principal_paid;

            return FFM::dollar($profit_paid);
        })->addColumn('id', function ($data) {
        })->editColumn('total_invested', function ($data) {
            $total_invested = $data->investments->sum('total_invested');

            return FFM::dollar($total_invested);
        })->editColumn('last_payment_date', function ($data) {
            $last_payment = ParticipentPayment::where('merchant_id', $data->id)->latest()->select('creator_id', 'created_at')->first();
            $user = User::where('id', $last_payment->creator_id)->value('name');
            $user = ($user) ? $user : '--';
            $created_date = 'Created On '.FFM::datetime($last_payment->created_at).' by '.$user;
            $last_payment_date = FFM::date($data->last_payment_date);

            return "<a title='$created_date'>".$last_payment_date.'</a>';
        })->editColumn('net_ctd', function ($data) {
            $net_ctd = $data->participantPayment->sum('net_ctd');

            return FFM::dollar($net_ctd);
        })->editColumn('principal_paid', function ($data) {
            $principal_paid = ($data->investments->sum('total_invested')) / ($data->investments->sum('invest_rtr')) * $data->participantPayment->sum('net_ctd');

            return FFM::dollar($principal_paid);
        })->editColumn('principal_less', function ($data) {
            $principal_paid = ($data->investments->sum('total_invested')) / ($data->investments->sum('invest_rtr')) * $data->participantPayment->sum('net_ctd');
            $total_invested = $data->investments->sum('total_invested');
            $principal_less = $total_invested - $principal_paid;

            return FFM::dollar(-$principal_less);
        })->editColumn('profitp_less', function ($data) {
            $total_invested = $data->investments->sum('total_invested');
            $net_ctd = $data->participantPayment->sum('net_ctd');

            return FFM::dollar(-($total_invested - $net_ctd));
        })->with('Total', 'Total:')->with('total_invested_amount', FFM::dollar($total_invested_amount))->rawColumns(['last_payment_date'])->make(true);
    }

    public function defaultRateReport($lenders = null, $filter_investors = null, $filter_merchants = null, $default_payment = null, $velocity_type = null, $from_date = null, $to_date = null, $sub_status = null, $funded_date = null, $active = null, $overp_status = null, $days = null, $investor_type = null,$velocity_owned = null, $columRequest = false, $search_key = null)
    {
        if ($columRequest) {
            return [
                ['name' => 'id', 'orderable' => false, 'data' => 'id', 'title' => '#', 'searchable' => false],
                ['data' => 'name', 'name' => 'users.name', 'title' => 'Investor', 'orderable' => false],
                ['data' => 'net_zero', 'name' => 'net_zero', 'title' => 'Net Zero', 'orderable' => false, 'searchable' => false, 'visible'=>false], //please Remove after 2021-April-30
                ['data' => 'default_amount', 'name' => 'default_amount', 'title' => 'Default Invested Amount', 'orderable' => false, 'searchable' => false, 'class'=>'text-right'],
                ['data' => 'collection_amount', 'name' => 'collection_amount', 'title' => 'Default RTR Amount', 'orderable' => false, 'searchable' => false, 'class'=>'text-right'],
                ['data' => 'default_rate', 'name' => 'default_rate', 'title' => 'Default Invested Rate', 'orderable' => false, 'searchable' => false, 'class'=>'text-right'],
                ['data' => 'collection_rate', 'name' => 'collection_rate', 'title' => 'Default RTR Rate', 'orderable' => false, 'searchable' => false, 'class'=>'text-right'],
                ['data' => 'overpayment', 'name' => 'overpayment', 'title' => 'Overpayment', 'orderable' => false, 'searchable' => false, 'class'=>'text-right'],
            ];
        }
        $userId = Auth::user()->id;
        if (! $sub_status) {
            $sub_status = [];
        }
        $to_date = ! empty($to_date) ? $to_date : date('Y-m-d', strtotime('+5 days'));
        $merchants_arr = $this->merchant->search_default_data($filter_merchants, $filter_investors, $lenders, $from_date, $to_date, $userId, $sub_status, $funded_date, $velocity_type, $active, $days, $investor_type,$velocity_owned, null, $search_key);
        $merchants = $merchants_arr['merchants'];
        //$total_default_amount = $merchants_arr['total_default_amount'];
        $total_collection = $merchants_arr['total_rtr'];
        $over_p = 0;
        $overpayment_amount = array_sum(($merchants_arr['overpayments'])->toArray());
        if ($overp_status == 0 || $overp_status == 2) {
            $over_p = $overpayment_amount;
        }
        $overpayment = ($merchants_arr['overpayments'])->toArray();
        //$total_default_amount = $total_default_amount - $over_p;
        //$total_collection = $total_collection - $over_p;
        // if ($total_default_amount < 0) {
        //     $total_default_amount = 0;
        // }
        $net_zero_sum = $merchants_arr['net_zero_sum'];
        $invsestors_rtr = ($merchants_arr['invsestors_rtr'])->toArray();
        $investment_amount = ($merchants_arr['investment_amount'])->toArray();
        $rtr = ($merchants_arr['rtr']);
        $total_collection = $total_default_amount = 0;
        foreach($merchants->get()->toArray() as $mer_arr){
        $over_amnt = 0;
        if ($overp_status == 0 || $overp_status == 2) {
        $over_amnt = isset($overpayment[$mer_arr['id']]) ? ($overpayment[$mer_arr['id']]) : 0;
        }
       // if($mer_arr['investor_rtr']-$over_amnt>0){
        $total_collection = $total_collection+round($mer_arr['investor_rtr']-$over_amnt,2);
       //  }
         $total_default_amount = $total_default_amount+round($mer_arr['default_amount']-$over_amnt,2);
         //if($mer_arr['default_amount']-$over_amnt>0){
        //     if($mer_arr['ctd_1']<=$mer_arr['invested_amount']){
        // $total_default_amount = $total_default_amount+round($mer_arr['default_amount']-$over_amnt,2);
        // }
    }

        return \DataTables::of($merchants)->editColumn('name', function ($data) {
            return "<a href='/admin/investors/portfolio/$data->id'>$data->name</a>";
        })->editColumn('net_zero', function ($data) {
            return FFM::dollar($data->net_zero);
        })->editColumn('default_amount', function ($data) use ($overpayment, $overp_status, $sub_status) {
            // if($data->ctd_1>$data->invested_amount){
            // return FFM::dollar(0);
            // }
            
            $over = 0;
            if ($overp_status == 0 || $overp_status == 2) {
                $over = isset($overpayment[$data->id]) ? ($overpayment[$data->id]) : 0;
            }
            $array = [4, 22];
            $result = ! empty(array_intersect($array, $sub_status));
            $default = $data->default_amount - $over;
            if ($result == 0) {
                if ($default < 0) {
                    $default = 0;
                }
            }

            return FFM::dollar($default);
        })->editColumn('collection_amount', function ($data) use ($overpayment, $overp_status) {
            $over = 0;
            if ($overp_status == 0 || $overp_status == 2) {
                $over = isset($overpayment[$data->id]) ? $overpayment[$data->id] : 0;
            }
            $def_rtr = round($data->investor_rtr - $over, 2);
            // if($def_rtr<0){
            // return FFM::dollar(0);

            // }

            return FFM::dollar($def_rtr);
        })->editColumn('overpayment', function ($data) use ($overpayment, $overp_status) {
            $over = isset($overpayment[$data->id]) ? ($overpayment[$data->id]) : 0;

            return FFM::dollar($over);
        })->editColumn('default_rate', function ($data) use ($investment_amount, $overpayment, $overp_status) {
            // if($data->ctd_1>$data->invested_amount){
            // return FFM::percent(0);
            // }
            $over_payment_investor_value = 0;
            if ($overp_status == 0 || $overp_status == 2) {
                $over_payment_investor_value = isset($overpayment[$data->id]) ? $overpayment[$data->id] : 0;
            }
            $invest = isset($investment_amount[$data->id]) ? $investment_amount[$data->id] : 0;
            if ($invest) {
                return FFM::percent(($data->default_amount - $over_payment_investor_value) / ($investment_amount[$data->id]) * 100);
            } else {
                return FFM::percent(0);
            }
        })->editColumn('collection_rate', function ($data) use ($invsestors_rtr, $overpayment, $overp_status) {
            $over_payment_investor_value = 0;
            if ($overp_status == 0 || $overp_status == 2) {
                $over_payment_investor_value = isset($overpayment[$data->id]) ? $overpayment[$data->id] : 0;
            }
            if ($invsestors_rtr[$data->id]) {
                $collection_rate = FFM::percent(($data->investor_rtr - $over_payment_investor_value) / ($invsestors_rtr[$data->id]) * 100);
            } else {
                $collection_rate = FFM::percent(0);
            }
            // if ($collection_rate <= 0) {
            //     $collection_rate = FFM::percent(0);
            // }

            return $collection_rate;
        })->rawColumns(['name'])->addIndexColumn()->with('Total', 'Total:')
            ->with('total_default_amount', \FFM::dollar($total_default_amount))
            ->with('total_collection', \FFM::dollar($total_collection))
            ->with('total_overpayment', \FFM::dollar($overpayment_amount))->make(true);
    }

    public function profitabilityReport4($merchants, $from_date, $to_date, $funded_date = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'id', 'name' => 'id', 'title' => '#', 'orderable' => false, 'searchable' => false],
                ['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name', 'orderable' => false],
                ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD', 'searchable' => false],
                ['data' => 'total_profit', 'name' => 'total_profit', 'title' => 'Total Profit', 'searchable' => false],
                ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills', 'searchable' => false],
                ['data' => 'interest', 'name' => 'interest', 'title' => 'Pref Return'],
                ['searchable' => false, 'data' => 'default', 'name' => 'default', 'title' => 'Default'],
                ['searchable' => false, 'data' => 'net_proft', 'name' => 'net_proft', 'title' => 'Net Profit'],
                ['searchable' => false, 'data' => 'velocity', 'name' => 'velocity', 'title' => '50% Velocity'],
                ['searchable' => false, 'data' => 'investor', 'name' => 'investor', 'title' => '50% To Investor'],
            ];
        }
        $to_date = ! empty($to_date) ? $to_date : date('Y-m-d', strtotime('+5 days'));
        $data = $this->merchant->getProfitabilityReport4($merchants, $from_date, $to_date, $funded_date);
        $result = $data['data'];
        $result = $result->get()->toArray();
        $id_arr = array_unique(array_column($result, 'id'));
        $pref_return_arr = $this->merchant->calculatePrefRetun($id_arr,$from_date, $to_date);
        
        $total_interest = array_sum($pref_return_arr);
        $t_overpayment = array_sum(array_column($result, 'overpayment'));
        $total_profit = array_sum(array_column($result, 'total_profit'));
        $total_profit += array_sum(array_column($result, 'carry_profit'));
        $total_bills = array_sum(array_column($result, 'bills'));
        $total_default = array_sum(array_column($result, 'default_amnt')) - array_sum(array_column($result, 'ctd_default')) - $t_overpayment;
        //$total_interest = array_sum(array_column($result, 'interest')) + array_sum(array_column($result, 'return_of_principal_interest'));
        $total_ctd = array_sum(array_column($result, 'ctd'));
        $total_net_profit = ($total_profit - $total_default - $total_interest - $total_bills);
        $total_profit_d_v = array_sum(array_column($result, 'profit_d_v'));
        $total_profit_d_i = array_sum(array_column($result, 'profit_d_i'));
        $total_50_velocity = ($total_net_profit * 50 / 100) + $total_profit_d_v;
        $total_50_investor = ($total_net_profit * 50 / 100) + $total_profit_d_i;
        $total_profit_d_v = FFM::dollar(-$total_profit_d_v);
        $total_profit_d_i = FFM::dollar(-$total_profit_d_i);

        return \DataTables::of($result)->editColumn('ctd', function ($result) {
            return FFM::dollar($result->ctd);
        })->editColumn('investor_name', function ($result) {
            return "<a href='/admin/investors/portfolio/$result->id'>$result->investor_name</a>";
        })->rawColumns(['investor_name', 'velocity', 'investor', 'pactulos', 'net_proft', 'interest'])
        ->editColumn('bills', function ($result) {
            return FFM::dollar($result->bills);
        })->addColumn('interest', function ($result) use($pref_return_arr){
            //return FFM::dollar($result->interest + $result->return_of_principal_interest).'<span style="display:none;">'.round($result->interest + $result->return_of_principal_interest, 2).'</span>';
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            return FFM::dollar($pref_return);

        })->editColumn('total_profit', function ($result) {
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $default = $result->default_amnt - $result->ctd_default - $over_p;
            $default = 0;
            $profit = $result->total_profit + $carry_profit;

            return FFM::dollar($profit);
        })->editColumn('net_proft', function ($result) use($pref_return_arr){
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit - $pref_return - $result->bills + $carry_profit - $default_amunt1;
            if ($profit <= 0) {
                $profit = '<a title='.FFM::dollar(round($profit, 2)).'>'.FFM::dollar(0).'</a>';
            } else {
                $profit = FFM::dollar($profit);
            }

            return $profit;
        })->editColumn('default', function ($result) {
            $over_p = $result->overpayment ? $result->overpayment : 0;

            return FFM::dollar($result->default_amnt - $result->ctd_default - $over_p);
        })->editColumn('velocity', function ($result) use($pref_return_arr){
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit - $pref_return - $result->bills + $carry_profit - $default_amunt1;
            $profit_d_v = $result->profit_d_v;
            if ($result->profit_d_v == '') {
                $profit_d_v = 0;
            }
            $profit_d_v = FFM::dollar(-$profit_d_v);

            return FFM::dollar(($profit / 100 * 50) + $result->profit_d_v)."<br><font color='red'>   +$profit_d_v </color>";
        })->editColumn('investor', function ($result) use($pref_return_arr){
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit - $pref_return - $result->bills + $carry_profit - $default_amunt1;
            $profit_d_i = $result->profit_d_i;
            if ($result->profit_d_i == '') {
                $profit_d_i = 0;
            }
            $profit_d_i = FFM::dollar(-$profit_d_i);

            return FFM::dollar(($profit / 100 * 50) + $result->profit_d_i)."<br><font color='red'>   +$profit_d_i </color>";
        })->with('Total', 'Total:')
            ->with('total_ctd', FFM::dollar($total_ctd))
            ->with('total_profit_value', FFM::dollar($total_profit))
            ->with('total_bills', FFM::dollar($total_bills))
            ->with('total_interest', FFM::dollar($total_interest))
            ->with('total_default', FFM::dollar($total_default))
            ->with('total_net_profit', FFM::dollar($total_net_profit))
            ->with('total_50_velocity', FFM::dollar($total_50_velocity)."<br><font color='red'>   +$total_profit_d_v </color>")
            ->with('total_50_investor', FFM::dollar($total_50_investor)."<br><font color='red'>   +$total_profit_d_i </color>")
            ->make(true);
    }

    public function profitabilityReport2($merchants, $from_date, $to_date, $funded_date = null, $investor_check = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#'],
                ['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name', 'orderable' => false],
                ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD', 'orderable' => false],
                ['data' => 'total_profit', 'name' => 'total_profit', 'title' => 'Total Profit', 'orderable' => false],
                ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills', 'orderable' => false],
                ['data' => 'interest', 'name' => 'interest', 'title' => 'Pref Return', 'orderable' => false],
                ['searchable' => false, 'data' => 'default', 'name' => 'default', 'title' => 'Default', 'orderable' => false],
                ['searchable' => false, 'data' => 'net_proft', 'name' => 'net_proft', 'title' => 'Net Profit', 'orderable' => false],
                ['searchable' => false, 'data' => 'velocity', 'name' => 'velocity', 'title' => '65% Velocity', 'orderable' => false],
                ['searchable' => false, 'data' => 'investor', 'name' => 'investor', 'title' => '20% To Investor', 'orderable' => false],
                ['searchable' => false, 'data' => 'pactulos', 'name' => 'pactulos', 'title' => '15% Pactolus', 'orderable' => false],
            ];
        }
        $data = $this->merchant->getProfitabilityReport2($merchants, $from_date, $to_date, $funded_date, $investor_check);
        $result = $data['data'];
        $total_over = array_sum(array_column($result->toArray(), 'overpayment'));
        $total_profit = array_sum(array_column($result->toArray(), 'total_profit'));
        $total_profit += array_sum(array_column($result->toArray(), 'carry_profit'));
        $total_bills = array_sum(array_column($result->toArray(), 'bills'));
        $total_default = array_sum(array_column($result->toArray(), 'default_amnt')) - array_sum(array_column($result->toArray(), 'ctd_default')) - $total_over;
        $id_arr = array_unique(array_column($result->toArray(), 'id'));
        $pref_return_arr = $this->merchant->calculatePrefRetun($id_arr,$from_date, $to_date);
        $total_interest = array_sum($pref_return_arr);
      // $total_interest = array_sum(array_column($result->toArray(), 'interest')) + array_sum(array_column($result->toArray(), 'return_of_principal_interest'));
        $total_ctd = array_sum(array_column($result->toArray(), 'ctd'));
        $total_net_profit = ($total_profit - $total_interest - $total_default - $total_bills);
        $total_profit_d_v = array_sum(array_column($result->toArray(), 'profit_d_v'));
        $total_profit_d_p = array_sum(array_column($result->toArray(), 'profit_d_p'));
        $total_profit_d_i = array_sum(array_column($result->toArray(), 'profit_d_i'));
        $total_65_velocity = ($total_net_profit * 65 / 100) + $total_profit_d_v;
        $total_20_investor = ($total_net_profit * 20 / 100) + $total_profit_d_i;
        $total_15_pactulos = ($total_net_profit * 15 / 100) + $total_profit_d_p;
        $total_profit_d_v = FFM::dollar(-$total_profit_d_v);
        $total_profit_d_p = FFM::dollar(-$total_profit_d_p);
        $total_profit_d_i = FFM::dollar(-$total_profit_d_i);
        return \DataTables::of($result)->editColumn('ctd', function ($result) {
            return FFM::dollar($result->ctd);
        })->editColumn('investor_name', function ($result) {
            return "<a href='/admin/investors/portfolio/$result->id'>$result->investor_name</a>";
        })->rawColumns(['investor_name', 'velocity', 'investor', 'pactulos', 'net_proft'])->editColumn('bills', function ($result) {
            return FFM::dollar($result->bills);
        })->editColumn('interest', function ($result) use($pref_return_arr){
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            //return FFM::dollar($result->interest + $result->return_of_principal_interest);

            return FFM::dollar($pref_return);
        })->editColumn('total_profit', function ($result) {
            $over_p = isset($result->overpayment) ? $result->overpayment : 0;
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $default = $result->default_amnt - $result->ctd_default - $over_p;
            $profit = $result->total_profit + $carry_profit;

            return FFM::dollar($profit);
        })->editColumn('net_proft', function ($result) use($pref_return_arr){
            $over_p = isset($result->overpayment) ? $result->overpayment : 0;
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
           $profit = $result->total_profit - $pref_return - $default_amunt1 - $result->bills + $carry_profit;
            if ($profit <= 0) {
                $profit = '<a title='.FFM::dollar(round($profit, 2)).'>'.FFM::dollar(0).'</a>';
            } else {
                $profit = FFM::dollar($profit);
            }

            return $profit;
        })->editColumn('default', function ($result) {
            $over_p = isset($result->overpayment) ? $result->overpayment : 0;

            return FFM::dollar($result->default_amnt - $result->ctd_default - $over_p);
        })->editColumn('velocity', function ($result) use($pref_return_arr){
            $over_p = isset($result->overpayment) ? $result->overpayment : 0;
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit - $pref_return + $carry_profit - $result->bills - $default_amunt1;

            $profit_d_v = $result->profit_d_v;
            if ($result->profit_d_v == '') {
                $profit_d_v = 0;
            }
            $profit_d_v = FFM::dollar(-$profit_d_v);

            return FFM::dollar(($profit / 100 * 65) + $result->profit_d_v)."<br><font color='red'>   +$profit_d_v </color>";
        })->editColumn('investor', function ($result) use($pref_return_arr){
            $over_p = isset($result->overpayment) ? $result->overpayment : 0;
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit -$pref_return  + $carry_profit - $result->bills - $default_amunt1;
            $profit_d_i = $result->profit_d_i;
            if ($result->profit_d_i == '') {
                $profit_d_i = 0;
            }
            $profit_d_i = FFM::dollar(-$profit_d_i);

            return FFM::dollar(($profit / 100 * 20) + $result->profit_d_i)."<br><font color='red'>   +$profit_d_i </color>";
        })->editColumn('pactulos', function ($result) use($pref_return_arr){
            $over_p = isset($result->overpayment) ? $result->overpayment : 0;
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit - $pref_return + $carry_profit - $result->bills - $default_amunt1;
            $profit_d_p = $result->profit_d_p;
            if ($result->profit_d_p == '') {
                $profit_d_p = 0;
            }
            $profit_d_p = FFM::dollar(-$profit_d_p);

            return FFM::dollar(($profit / 100 * 15) + $result->profit_d_p)."<br><font color='red'>   +$profit_d_p </color>";
        })->addIndexColumn()->with('Total', 'Total:')
            ->with('total_ctd', FFM::dollar($total_ctd))
            ->with('total_profit_value', FFM::dollar($total_profit))
            ->with('total_bills', FFM::dollar($total_bills))
            ->with('total_interest', FFM::dollar($total_interest))
            ->with('total_default', FFM::dollar($total_default))
            ->with('total_net_profit', FFM::dollar($total_net_profit))
            ->with('total_65_velocity', FFM::dollar($total_65_velocity)."<br><font color='red'>   +$total_profit_d_v </color>")
            ->with('total_20_investor', FFM::dollar($total_20_investor)."<br><font color='red'>   +$total_profit_d_i </color>")
            ->with('total_15_pactulos', FFM::dollar($total_15_pactulos)."<br><font color='red'>   +$total_profit_d_p </color>")
            ->make(true);
    }

    public function profitabilityReport3($merchants, $from_date, $to_date, $funded_date = null, $columRequest = false)
    {
        if ($columRequest) {
            $data = [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#'],
                ['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name', 'orderable' => false],
                ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD'],
                ['data' => 'total_profit', 'name' => 'total_profit', 'title' => 'Total Profit'],
                ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'],
            ];
            $data[] = ['data' => 'interest', 'name' => 'interest', 'title' => 'Pref Return'];
            $data[] = ['searchable' => false, 'data' => 'default', 'name' => 'default', 'title' => 'Default'];
            $data[] = ['searchable' => false, 'data' => 'net_proft', 'name' => 'net_proft', 'title' => 'net profit'];
            $data[] = ['searchable' => false, 'data' => 'velocity', 'name' => 'velocity', 'title' => '50% Velocity'];
            $data[] = ['searchable' => false, 'data' => 'investor', 'name' => 'investor', 'title' => '30% To Investor'];
            $data[] = ['searchable' => false, 'data' => 'pactulos', 'name' => 'pactulos', 'title' => '20% Pactolus'];

            return $data;
        }
        $data = $this->merchant->getProfitabilityReport3($merchants, $from_date, $to_date, $funded_date);
        $result = $data['data'];
        $total_over = array_sum(array_column($result->toArray(), 'overpayment'));
        $total_over = 0;
        $t_overpayment = $total_over;
        $total_50_velocity = $total_profit_d_v = $total_profit_d_p = $total_20_pactulos = $total_profit_d_i = $total_30_investor = 0;
        $investor_30 = 0;
        $id_arr = array_unique(array_column($result->toArray(), 'id'));
        $pref_return_arr = $this->merchant->calculatePrefRetun($id_arr,$from_date, $to_date);
        foreach ($result as $value) {
            $over_p = $value->overpayment ? $value->overpayment : 0;
            $over_p = 0;
            $carry_profit = $value->carry_profit ? $value->carry_profit : 0;
            $default_amunt1 = $value->default_amnt - $value->ctd_default - $over_p;
           // $interest_final = ($value->interest + $value->return_of_principal_interest);
            $interest_final = isset($pref_return_arr[$value->id]) ? $pref_return_arr[$value->id]: 0;

            $profit = ($value->total_profit + $carry_profit - $interest_final - $value->bills) - $default_amunt1;
            $profit_d_v = $value->profit_d_v;
            $profit_d_p = $value->profit_d_p;
            if ($value->profit_d_v == '') {
                $profit_d_v = 0;
            }
            if ($value->profit_d_p == '') {
                $profit_d_p = 0;
            }
            if ($profit / 100 * 30 + $value->profit_d_i > $interest_final) {
                $velocity_50 = ($profit / 100 * 50) + $value->profit_d_v;
            } else {
                $velocity_50 = $profit / 70 * 50 + $value->profit_d_v;
            }
            $total_50_velocity = $total_50_velocity + $velocity_50;
            if ($profit / 100 * 30 + $value->profit_d_p > $interest_final) {
                $pactulos_20 = ($profit / 100 * 20) + $value->profit_d_p;
            } else {
                $pactulos_20 = ($profit / 70 * 20) + $value->profit_d_p;
            }
            $total_20_pactulos = $total_20_pactulos + $pactulos_20;
            $total_profit_d_p = $total_profit_d_p + $value->profit_d_p;
            $total_profit_d_i = $total_profit_d_i + $value->profit_d_i;
            $total_profit_d_v = $total_profit_d_v + $profit_d_v;
            if ($value->profit_d_i == '') {
                $profit_d_i = 0;
            }
            if ($profit / 100 * 30 + $value->profit_d_i > $interest_final) {
                $investor_30 = ($profit / 100 * 30) + $value->profit_d_i;
            } else {
                $investor_30 = 0;
            }
            $total_30_investor = $total_30_investor + $investor_30;
        }
        $total_profit = array_sum(array_column($result->toArray(), 'total_profit'));
        $total_profit += array_sum(array_column($result->toArray(), 'carry_profit'));
        $overpayment = array_sum(array_column($result->toArray(), 'overpayment'));
        $overpayment = 0;
        $total_bills = array_sum(array_column($result->toArray(), 'bills'));
        $total_default = array_sum(array_column($result->toArray(), 'default_amnt')) - array_sum(array_column($result->toArray(), 'ctd_default')) - $t_overpayment;
        
        $total_interest = array_sum($pref_return_arr);
        //$total_interest = array_sum(array_column($result->toArray(), 'interest')) + array_sum(array_column($result->toArray(), 'return_of_principal_interest'));
        $total_ctd = array_sum(array_column($result->toArray(), 'ctd'));
        $total_net_profit = $total_profit - $total_default - $total_interest - $total_bills;
        $total_profit_d_v = FFM::dollar(-$total_profit_d_v);
        $total_profit_d_p = FFM::dollar(-$total_profit_d_p);
        $total_profit_d_i = FFM::dollar(-$total_profit_d_i);
        $total_30_investor_field = ' - ';

        return \DataTables::of($result)->editColumn('ctd', function ($result) {
            return FFM::dollar($result->ctd);
        })->editColumn('investor_name', function ($result) {
            return "<a href='/admin/investors/portfolio/$result->id'>$result->investor_name</a>";
        })->rawColumns(['investor_name', 'velocity', 'investor', 'pactulos', 'net_proft'])->editColumn('bills', function ($result) {
            return FFM::dollar($result->bills);
        })->editColumn('interest', function ($result) use($pref_return_arr){
            //return FFM::dollar($result->interest + $result->return_of_principal_interest);
            $pref_return = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            return FFM::dollar($pref_return);
        })->editColumn('total_profit', function ($result) {
            $over_p = isset($result->overpayment) ? $result->overpayment : 0;
            $over_p = 0;
            $carry_profit = isset($result->carry_profit) ? $result->carry_profit : 0;
            $default = $result->default_amnt - $result->ctd_default - $over_p;
            $profit = $result->total_profit + $carry_profit;
            return FFM::dollar($profit);
        })->editColumn('net_proft', function ($result) use($pref_return_arr){
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $over_p = 0;
            $carry_profit = $result->carry_profit ? $result->carry_profit : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $interest_final = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit + $carry_profit - $default_amunt1 - $interest_final - $result->bills;
            
            if ($profit <= 0) {
                $profit = '<a title='.FFM::dollar(round($profit, 2)).'>'.FFM::dollar(0).'</a>';
            } else {
                $profit = FFM::dollar($profit);
            }

            return $profit;
        })->editColumn('default', function ($result) {
            $over_p = 0;
            $default = $result->default_amnt - $result->ctd_default - $over_p;

            return FFM::dollar($default);
        })->editColumn('velocity', function ($result) use($pref_return_arr){
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $over_p = 0;
            $carry_profit = $result->carry_profit ? $result->carry_profit : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            //$interest_final = ($result->interest + $result->return_of_principal_interest);
            $interest_final = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;

            $profit = $result->total_profit + $carry_profit - $interest_final - $result->bills - $default_amunt1;
            $profit_d_v = $result->profit_d_v;
            if ($result->profit_d_v == '') {
                $profit_d_v = 0;
            }
            if ($profit / 100 * 30 + $result->profit_d_i > $interest_final) {
                $new_profit = $profit / 100 * 50;
            } else {
                $new_profit = $profit / 70 * 50;
            }
            $profit_d_v = FFM::dollar(-$profit_d_v);

            return FFM::dollar($new_profit + $result->profit_d_v)."<br><font color='red'>   +$profit_d_v </color>";
        })->editColumn('investor', function ($result) use($pref_return_arr){
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $over_p = 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $carry_profit = $result->carry_profit ? $result->carry_profit : 0;
            //$interest_final = ($result->interest + $result->return_of_principal_interest);
            $interest_final = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit + $carry_profit - $result->bills - $default_amunt1;
            $net_profit = $profit - ($interest_final);
            $profit_d_i = $result->profit_d_i;
            if ($result->profit_d_i == '') {
                $profit_d_i = FFM::dollar(0);
            }
            if ($net_profit / 100 * 30 + $result->profit_d_i > $interest_final) {
                $new_profit = $net_profit / 100 * 30;
            } else {
                return '-';
                $new_profit = $net_profit / 70 * 30;
            }

            return FFM::dollar($new_profit + $result->profit_d_i)."<br><font color='red'>   +$profit_d_i </color>";
        })->editColumn('pactulos', function ($result) use($pref_return_arr){
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $over_p = 0;
            $carry_profit = $result->carry_profit ? $result->carry_profit : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            //$interest_final = ($result->interest + $result->return_of_principal_interest);
            $interest_final = isset($pref_return_arr[$result->id]) ? $pref_return_arr[$result->id]: 0;
            $profit = $result->total_profit + $carry_profit - $interest_final - $result->bills - $default_amunt1;
            $profit_d_p = $result->profit_d_p;
            if ($result->profit_d_p == '') {
                $profit_d_p = 0;
            }
            if ($profit / 100 * 30 + $result->profit_d_i > $interest_final) {
                $new_profit = $profit / 100 * 20;
            } else {
                $new_profit = $profit / 70 * 20;
            }
            $profit_d_p = FFM::dollar(-$profit_d_p);

            return FFM::dollar($new_profit + $result->profit_d_p)."<br><font color='red'>   +$profit_d_p </color>";
        })->addIndexColumn()
            ->with('Total', 'Total:')
            ->with('total_ctd', FFM::dollar($total_ctd))
            ->with('total_profit_value', FFM::dollar($total_profit))
            ->with('total_bills', FFM::dollar($total_bills))
            ->with('total_interest', FFM::dollar($total_interest))
            ->with('total_default', FFM::dollar($total_default))
            ->with('total_net_profit', FFM::dollar($total_net_profit))
            ->with('total_50_velocity', FFM::dollar($total_50_velocity)."<br><font color='red'>   +$total_profit_d_v </color>")
            ->with('total_30_investor', FFM::dollar($total_30_investor)."<br><font color='red'>   +$total_profit_d_i </color>")
            ->with('total_20_pactulos', FFM::dollar($total_20_pactulos)."<br><font color='red'>   +$total_profit_d_p </color>")
            ->make(true);
    }

    public function profitabilityReport21($merchants, $from_date, $to_date, $funded_date = null, $columRequest = false)
    {
        if ($columRequest) {
            $data = [
                ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#'],
                ['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name', 'orderable' => false],
                ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD'],
                ['data' => 'total_profit', 'name' => 'total_profit', 'title' => 'Total Profit'],
                ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'],
            ];
            $data[] = ['searchable' => false, 'data' => 'default', 'name' => 'default', 'title' => 'Default'];
            $data[] = ['searchable' => false, 'data' => 'net_proft', 'name' => 'net_proft', 'title' => 'net profit'];
            $data[] = ['searchable' => false, 'data' => 'velocity', 'name' => 'velocity', 'title' => '50% Velocity'];
            $data[] = ['searchable' => false, 'data' => 'investor', 'name' => 'investor', 'title' => '30% To Investor'];
            $data[] = ['searchable' => false, 'data' => 'pactulos', 'name' => 'pactulos', 'title' => '20% Pactolus'];

            return $data;
        }
        $data = $this->merchant->getProfitabilityReport21($merchants, $from_date, $to_date, $funded_date);
        $result = $data['data'];
        $total_over = array_sum(array_column($result->toArray(), 'overpayment'));
        $t_overpayment = $total_over;
        $total_50_velocity = $total_profit_d_v = $total_profit_d_p = $total_20_pactulos = $total_profit_d_i = $total_30_investor = 0;
        $investor_30 = 0;
        foreach ($result as $value) {
            $over_p = $value->overpayment ? $value->overpayment : 0;
            $carry_profit = $value->carry_profit ? $value->carry_profit : 0;
            $default_amunt1 = $value->default_amnt - $value->ctd_default - $over_p;
            $interest_final = ($value->interest + $value->return_of_principal_interest);
            $profit = $value->total_profit - $default_amunt1 - $interest_final - $value->bills + $carry_profit;
            $profit_d_v = $value->profit_d_v;
            $profit_d_p = $value->profit_d_p;
            if ($value->profit_d_v == '') {
                $profit_d_v = 0;
            }
            if ($value->profit_d_p == '') {
                $profit_d_p = 0;
            }
            if ($profit / 100 * 30 - $value->profit_d_i > $interest_final) {
                $velocity_50 = ($profit / 100 * 50) - $value->profit_d_v;
            } else {
                $velocity_50 = $profit / 70 * 50 - $value->profit_d_v;
            }
            if ($profit / 100 * 30 - $value->profit_d_p > $interest_final) {
                $pactulos_20 = ($profit / 100 * 20) - $value->profit_d_p;
            } else {
                $pactulos_20 = ($profit / 70 * 20) - $value->profit_d_p;
            }
            $total_profit_d_p = $total_profit_d_p + $value->profit_d_p;
            $total_profit_d_i = $total_profit_d_i + $value->profit_d_i;
            $total_profit_d_v = $total_profit_d_v + $profit_d_v;
            if ($value->profit_d_i == '') {
                $profit_d_i = 0;
            }
            if ($profit / 100 * 30 - $value->profit_d_i > $interest_final) {
                $investor_30 = ($profit / 100 * 30) - $value->profit_d_i;
            } else {
                $investor_30 = 0;
            }
        }
        $total_profit = array_sum(array_column($result->toArray(), 'total_profit'));
        $total_profit += array_sum(array_column($result->toArray(), 'carry_profit'));
        $overpayment = array_sum(array_column($result->toArray(), 'overpayment'));
        $total_bills = array_sum(array_column($result->toArray(), 'bills'));
        $total_default = array_sum(array_column($result->toArray(), 'default_amnt')) - array_sum(array_column($result->toArray(), 'ctd_default')) - $t_overpayment;
        $total_interest = array_sum(array_column($result->toArray(), 'interest')) + array_sum(array_column($result->toArray(), 'return_of_principal_interest'));
        $total_ctd = array_sum(array_column($result->toArray(), 'ctd'));
        $total_profit_d_v = FFM::dollar(-$total_profit_d_v);
        $total_profit_d_p = FFM::dollar(-$total_profit_d_p);
        $total_profit_d_i = FFM::dollar(-$total_profit_d_i);
        $total_30_investor_field = ' - ';
        $total_net_profit = $total_profit - $total_default - $total_bills;
        $total_50_velocity = percentage(50, $total_net_profit);
        $total_30_investor = percentage(30, $total_net_profit);
        $total_20_pactulos = percentage(20, $total_net_profit);
        $total_30_investor_field = FFM::dollar($total_30_investor)."<br><font color='red'>   +$total_profit_d_i </color>";

        return \DataTables::of($result)->editColumn('ctd', function ($result) {
            return FFM::dollar($result->ctd);
        })->editColumn('investor_name', function ($result) {
            return "<a href='/admin/investors/portfolio/$result->id'>$result->investor_name</a>";
        })->rawColumns(['investor_name', 'velocity', 'investor', 'pactulos', 'net_proft'])->editColumn('bills', function ($result) {
            session_set('bill', $result->bills);

            return FFM::dollar($result->bills);
        })->editColumn('total_profit', function ($result) {
            $carry_profit = $result->carry_profit ? $result->carry_profit : 0;
            $profit = $result->total_profit + $carry_profit;
            session_set('total_profit', $result->total_profit);

            return FFM::dollar($profit);
        })->editColumn('net_proft', function ($result) {
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $carry_profit = $result->carry_profit ? $result->carry_profit : 0;
            $default_amunt1 = $result->default_amnt - $result->ctd_default - $over_p;
            $profit = $result->total_profit - $result->bills + $carry_profit - $default_amunt1;
            session_set('profit', $profit);
            session_set('carry_profit', $carry_profit);
            session_set('overpayment', $over_p);
            if ($profit <= 0) {
                $profit = '<a title='.FFM::dollar(round($profit, 2)).'>'.FFM::dollar(0).'</a>';
            } else {
                $profit = FFM::dollar($profit);
            }

            return $profit;
        })->editColumn('default', function ($result) {
            $over_p = $result->overpayment ? $result->overpayment : 0;
            $default = $result->default_amnt - $result->ctd_default - $over_p;
            session_set('default', $default);

            return FFM::dollar($default);
        })->editColumn('velocity', function ($result) {
            $profit_d_v = $result->profit_d_v;
            if ($result->profit_d_v == '') {
                $profit_d_v = 0;
            }
            $profit_d_v = FFM::dollar(-$profit_d_v);

            return FFM::dollar(percentage(50, (session('total_profit') + session('carry_profit') - session('default') - session('bill'))))."<br><font color='red'>   +$profit_d_v </color>";
        })->editColumn('investor', function ($result) {
            $profit_d_i = $result->profit_d_i;
            if ($result->profit_d_i == '') {
                $profit_d_i = 0;
            }
            $profit_d_i = FFM::dollar(-$profit_d_i);

            return FFM::dollar(percentage(30, (session('total_profit') + session('carry_profit') - session('default') - session('bill'))))."<br><font color='red'>   +$profit_d_i </color>";
        })->editColumn('pactulos', function ($result) {
            $profit_d_p = $result->profit_d_p;
            if ($result->profit_d_p == '') {
                $profit_d_p = 0;
            }
            $profit_d_p = FFM::dollar(-$profit_d_p);

            return FFM::dollar(percentage(20, (session('total_profit') + session('carry_profit') - session('default') - session('bill'))))."<br><font color='red'>   +$profit_d_p </color>";
        })->with('Total', 'Total:')
            ->with('total_ctd', FFM::dollar($total_ctd))
            ->with('total_profit_value', FFM::dollar($total_profit))
            ->with('total_bills', FFM::dollar($total_bills))
            ->with('total_interest', FFM::dollar($total_interest))
            ->with('total_default', FFM::dollar($total_default))
            ->with('total_net_profit', FFM::dollar($total_net_profit))
            ->with('total_50_velocity', FFM::dollar($total_50_velocity)."<br><font color='red'>   +$total_profit_d_v </color>")
            ->with('total_30_investor', FFM::dollar($total_30_investor)."<br><font color='red'>   +$total_profit_d_i </color>")
            ->with('total_20_pactulos', FFM::dollar($total_20_pactulos)."<br><font color='red'>   +$total_profit_d_p </color>")
            ->addIndexColumn()
            ->make(true);
    }

    public function profitabilityReport3_previous_copy($merchants, $from_date, $to_date, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name'],
                ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD'],
                ['data' => 'total_profit', 'name' => 'total_profit', 'title' => 'Total Profit'],
                ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'],
                ['data' => 'interest', 'name' => 'interest', 'title' => 'Pref Return'],
                ['searchable' => false, 'data' => 'default', 'name' => 'default', 'title' => 'Default'],
                ['searchable' => false, 'data' => 'net_proft', 'name' => 'net_proft', 'title' => 'net profit'],
                ['searchable' => false, 'data' => 'velocity', 'name' => 'velocity', 'title' => '50% Velocity'],
                ['searchable' => false, 'data' => 'investor', 'name' => 'investor', 'title' => '30% To Investor'],
                ['searchable' => false, 'data' => 'pactulos', 'name' => 'pactulos', 'title' => '20% Pactolus'],
            ];
        }
        $data = $this->merchant->getProfitabilityReport3_previous_copy($merchants, $from_date, $to_date);
        $total_profit = array_sum(array_column($data->toArray(), 'total_profit'));
        $total_bills = array_sum(array_column($data->toArray(), 'bills'));
        $total_default = array_sum(array_column($data->toArray(), 'default'));
        $total_interest = array_sum(array_column($data->toArray(), 'interest'));
        $total_ctd = array_sum(array_column($data->toArray(), 'ctd'));
        $total_net_profit = $total_profit - $total_default - $total_bills;
        $total_50_velocity = $total_net_profit * 50 / 100;
        $total_30_investor = $total_net_profit * 30 / 100;
        $total_20_pactulos = $total_net_profit * 20 / 100;

        return \DataTables::of($data)->editColumn('ctd', function ($data) {
            return FFM::dollar($data->ctd);
        })->editColumn('investor_name', function ($data) {
            return "<a href='/admin/investors/portfolio/$data->id'>($data->investor_name)</a>";
        })->rawColumns(['investor_name', 'velocity', 'investor', 'pactulos'])->editColumn('bills', function ($data) {
            return FFM::dollar($data->bills);
        })->editColumn('interest', function ($data) {
            return FFM::dollar($data->interest);
        })->editColumn('total_profit', function ($data) {
            $profit = $data->total_profit;

            return FFM::dollar($profit);
        })->editColumn('net_proft', function ($data) {
            $profit = $data->total_profit - $data->default - $data->bills;

            return FFM::dollar($profit);
        })->editColumn('default', function ($data) {
            return FFM::dollar($data->default);
        })->editColumn('velocity', function ($data) {
            $profit = $data->total_profit - $data->default - $data->bills;
            $profit_d_v = $data->profit_d_v;
            if ($data->profit_d_v == '') {
                $profit_d_v = 0;
            }

            return FFM::dollar($profit / 100 * 50 - $data->profit_d_v)."<br><font color='red'>   +$profit_d_v </color>";
        })->editColumn('investor', function ($data) {
            $profit = $data->total_profit - $data->default - $data->bills;
            $profit_d_i = $data->profit_d_i;
            if ($data->profit_d_i == '') {
                $profit_d_i = 0;
            }

            return FFM::dollar($profit / 100 * 30 - $data->profit_d_i)."<br><font color='red'>   +$profit_d_i </color>";
        })->editColumn('pactulos', function ($data) {
            $profit = $data->total_profit - $data->default - $data->bills;
            $profit_d_p = $data->profit_d_p;
            if ($data->profit_d_p == '') {
                $profit_d_p = 0;
            }

            return FFM::dollar($profit / 100 * 20 - $data->profit_d_p)."<br><font color='red'>   +$profit_d_p </color>";
        })->with('total_ctd', FFM::dollar($total_ctd))
            ->with('total_profit_value', FFM::dollar($total_profit))
            ->with('total_bills', FFM::dollar($total_bills))
            ->with('total_interest', FFM::dollar($total_interest))
            ->with('total_default', FFM::dollar($total_default))
            ->with('total_net_profit', FFM::dollar($total_net_profit))
            ->with('total_50_velocity', FFM::dollar($total_50_velocity))
            ->with('total_30_investor', FFM::dollar($total_30_investor))
            ->with('total_20_pactulos', FFM::dollar($total_20_pactulos))
            ->make(true);
    }

    public function interestAccuredReport($merchants = null, $investors = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['className' => 'details-control', 'orderable' => false, 'data' => null, 'defaultContent' => '', 'title' => ''],
                ['data' => 'id', 'name' => 'id', 'title' => 'Merchant Id'],
                ['data' => 'name', 'name' => 'name', 'title' => 'Merchant Name'],
                ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD'],
                ['orderable' => false, 'data' => 'net_zero', 'name' => 'net_zero', 'title' => 'Net Zero'],
                ['searchable' => false, 'orderable' => false, 'data' => 'interest', 'name' => 'interest', 'title' => 'Interest'],
                ['orderable' => false, 'data' => 'net_zero_with_interest', 'name' => 'net_zero_with_interest', 'title' => 'Net Zero With Interest'],
                ['orderable' => false, 'data' => 'net_zero_with_limited_interest', 'name' => 'net_zero_with_limited_interest', 'title' => 'Net Zero With Limited Interest'],
            ];
        }
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if ($investors) {
            $max_yearly_interest = InvestorTransaction::where('transaction_type', 2)->whereIn('investor_id', $investors)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
        } else {
            $max_yearly_interest = InvestorTransaction::where('transaction_type', 2)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
        }
        $data = $this->merchant->getInterestAccuredDetails($investors, $merchants);
        $pre_paid1 = 0;
        $commission1 = 0;
        $funded1 = 0;
        $total1 = 0;
        $commissionPercentage = 0;
        $netZero = 0;
        $array1 = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->where('creator_id', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $array1[] = $value->id;
            }
        }
        $total_net_zero = $total_net_zero_with_interest = $i = $net_zero = $total_net_zero_with_limited_interest = $total_interest_amount = $total_limited_interest_amount = 0;
        $investor_arr = [];
        $total_limited_interest_amount = ($max_yearly_interest) * 10 / 100;

        return \DataTables::of($data)->editColumn('date_funded', function ($data) {
            return \FFM::date($data->date_funded);
        })->editColumn('investment_data', function ($data) {
            $new_arr = [];
            $transformer = new MerchantTransformer('interest_accured_report');
            $input_arr = $transformer->transform($data);
            foreach ($input_arr as $key => $row) {
                $new_arr[$key] = $row['created_at'];
            }
            array_multisort($new_arr, SORT_DESC, $input_arr);

            return $input_arr;
        })->editColumn('name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>";
        })->editColumn('interest', function ($data) {
            $result = $this->getNetZeroBasedDataTotal($data);

            return ($result['total_interest_amount']) ? \FFM::dollar($result['total_interest_amount']) : 0;
        })->editColumn('limited_interest', function ($data) {
            $result = $this->getNetZeroBasedDataTotal($data);

            return ($result['limited_interest']) ? \FFM::dollar($result['limited_interest']) : 0;
        })->editColumn('ctd', function ($data) {
            $dates = [];
            $ctd_sum = 0;
            $payments = $data->participantPayment;
            foreach ($payments as $key => $value) {
                if (! in_array($value->merchant_id.$value->payment_date.$value->payment_type, $dates)) {
                    $ctd_sum = $ctd_sum + $value->payment;
                }
                $dates[] = $value->merchant_id.$value->payment_date.$value->payment_type;
            }

            return \FFM::dollar($ctd_sum);
        })->addColumn('net_zero', function ($data) {
            $result = $this->getNetZeroBasedDataTotal($data);

            return ($result['net_zero']) ? \FFM::dollar($result['net_zero']) : '';
        })->addColumn('net_zero_with_interest', function ($data) {
            $result = $this->getNetZeroBasedDataTotal($data);

            return ($result['net_zero_with_interest']) ? \FFM::dollar($result['net_zero_with_interest']) : '';
        })->addColumn('net_zero_with_limited_interest', function ($data) {
            $result = $this->getNetZeroBasedDataTotal($data);

            return ($result['net_zero_with_limited_interest']) ? \FFM::dollar($result['net_zero_with_limited_interest']) : '';
        })->rawColumns(['name'])
            ->with('total_net_zero', \FFM::dollar($total_net_zero))
            ->with('total_net_zero_with_interest', \FFM::dollar($total_net_zero_with_interest))
            ->with('total_net_zero_with_limited_interest', \FFM::dollar($total_net_zero_with_limited_interest))
            ->with('total_interest', \FFM::dollar($total_interest_amount))
            ->with('total_limited_interest', \FFM::dollar($total_limited_interest_amount))
            ->make(true);
    }

    public function investorInterestAccuredDetailAction($user_id = null, $date_start = null, $date_end = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'DT_RowIndex', 'title' => '#', 'orderable' => false, 'searchable' => false],
                ['data' => 'total_credit', 'name' => 'total_credit', 'title' => 'Total Credit', 'orderable' => false],
                ['data' => 'interest_rate', 'name' => 'interest_rate', 'title' => 'ROI Rate', 'orderable' => false],
                ['data' => 'transaction_date', 'name' => 'transaction_date', 'title' => 'Transaction Date', 'orderable' => false],
                ['data' => 'from_date', 'name' => 'from_date', 'title' => 'From Date', 'orderable' => false],
                ['data' => 'to_date', 'name' => 'to_date', 'title' => 'To Date', 'orderable' => false],
                ['data' => 'roi_accured', 'name' => 'roi_accured', 'title' => 'ROI Accrued', 'orderable' => false],
            ];
        }
        $data = $this->getSingleInvestorInterestAccuredDetails($user_id, $date_start, $date_end);
        $total_interest_accrued = array_sum(array_column($data, 'interest'));
        $total_credit = array_sum(array_column($data, 'amount'));

        return \DataTables::of($data)->editColumn('name', function ($data) {
            return \FFM::dollar($data['amount']);
        })->addColumn('total_credit', function ($data) {
            return FFM::dollar($data['amount']).'<span style="display:none;">'.round($data['amount'], 2).'</span>';
        })->editColumn('from_date', function ($data) {
            return \FFM::date($data['from_date']);
        })->editColumn('to_date', function ($data) {
            return \FFM::date($data['to_date']);
        })->editColumn('roi_accured', function ($data) {
            return \FFM::dollar($data['interest']).'<span style="display:none;">'.round($data['interest'], 2).'</span>';
        })->editColumn('investor_name', function ($data) {
            return $data['name'];
        })->editColumn('interest_rate', function ($data) {
            return \FFM::percent($data['interest_rate']).'<span style="display:none;">'.round($data['interest_rate'], 2).'</span>';
        })->editColumn('transaction_date', function ($data) {
            return \FFM::date($data['transaction_date']);
        })->addIndexColumn()->rawColumns(['total_credit', 'interest_rate', 'roi_accured'])->with('total_roi_accured', \FFM::dollar($total_interest_accrued))->with('total_credit', \FFM::dollar($total_credit))->make(true);
    }

    public function getSingleInvestorInterestAccuredDetails($user_id = null, $date_start = null, $date_end = null)
    {
        $inv_result = InvestorTransaction::join('users', 'investor_transactions.investor_id', 'users.id')->where(function ($q) {
            $q->where('transaction_type', 2)->orWhere('transaction_category', 12);
        })
        ->where('investor_transactions.status', InvestorTransaction::StatusCompleted)
        ->where('investor_id', $user_id);
        if ($date_end) {
            $inv_result = $inv_result->where('date', '<=', $date_end);
        }
        $inv_result = $inv_result->orderByDesc('date');
        $proportion = 0;
        $inv_result = $inv_result->get();
        $result_arr = [];
        $i = 0;
        if ($date_end) {
            $today = $date_end;
        } else {
            $today = date('Y-m-d');
        }
        foreach ($inv_result as $key => $value) {
            $this_date = $value->date;
            if ($date_start >= $value->date) {
                $this_date = $date_start;
            } else {
                $this_date = $value->date;
            }
            $number_of_dates_obj = date_diff(new \DateTime($this_date), new \DateTime($today));
            $this_number_of_dates = $number_of_dates_obj->format('%R%a');
            $proportion = $this_number_of_dates ? ($this_number_of_dates + 1) / 365 : 0;
            $credit_value = $value->amount * $proportion;
            $interest_accrued = ($value->interest_rate > 0) ? ($credit_value * $value->interest_rate / 100) : 0;
            $result_arr[$i]['amount'] = $value->amount;
            $result_arr[$i]['interest'] = $interest_accrued;
            $result_arr[$i]['from_date'] = $this_date;
            $result_arr[$i]['to_date'] = $today;
            $result_arr[$i]['name'] = $value->name;
            $result_arr[$i]['interest_rate'] = $value->interest_rate;
            $result_arr[$i]['transaction_date'] = $value->date;
            $i++;
        }

        return $result_arr;
    }

    public function investorInterestAccuredReport($investors = null, $date_start = null, $date_end = null, $columRequest = false)
    {
        if ($columRequest) {
            return [['data' => 'DT_RowIndex', 'title' => '#', 'orderable' => false, 'searchable' => false], ['data' => 'investor_name', 'defaultContent' => '', 'title' => 'Investor Name', 'orderable' => false], ['data' => 'total_credit', 'defaultContent' => '', 'title' => 'Total Credit', 'orderable' => false], ['data' => 'investor_interest_rate', 'defaultContent' => '', 'title' => 'Investor ROI Rate', 'orderable' => false], ['data' => 'interest_accrued', 'name' => 'interest_accrued', 'title' => 'ROI Accrued', 'orderable' => false], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]];
        }
        $total_net_zero = $total_net_zero_with_interest = $total_net_zero_with_limited_interest = $total_inv_interest = $total_cre_interest = $total_credited_amount = $total_interest_accrued = 0;
        $data = $this->getInvestorInterestAccuredDetails($investors, $date_start, $date_end);
        $total_credited_amount = $total_credited_amount + array_sum(array_column($data, 'total_credit'));
        $total_interest_accrued = $total_interest_accrued + array_sum(array_column($data, 'interest_accrued'));

        return \DataTables::of($data)->addColumn('investor_name', function ($data) {
            return $data['name'];
        })->addColumn('interest_accrued', function ($data) {
            $data_html = $data['total_credit'].'*'.$data['interest_rate'].'/100/365*1='.$data['interest_accrued'];

            return \FFM::dollar($data['interest_accrued']).'<span style="display:none">'.round($data['interest_accrued'], 2).'</span>';
        })->addColumn('total_credit', function ($data) {
            return \FFM::dollar($data['total_credit']).'<span style="display:none;">'.round($data['total_credit'], 2).'</span';
        })->addColumn('investor_interest_rate', function ($data) {
            return \FFM::percent($data['interest_rate']);
        })->addColumn('action', function ($data) use ($date_start, $date_end) {
            return Form::open(['route' => ['admin::reports::investor-interest-accured-details', 'id' => $data['inv_id'], 'sdate' => $date_start, 'edate' => $date_end], 'target' => '_blank', 'method' => 'POST', 'class' => 'btn-form-wrap']).Form::submit('View', ['class' => 'invest btn btn-xs btn-primary ']).Form::close();
        })->addIndexColumn()->rawColumns(['interest_accrued', 'action', 'total_credit'])->with('Total', 'Total:')->with('net_zero', \FFM::dollar($total_net_zero))->with('net_zero_with_interest', \FFM::dollar($total_net_zero_with_interest))->with('net_zero_with_limited_interest', \FFM::dollar($total_net_zero_with_limited_interest))->with('interest_inv', \FFM::dollar($total_inv_interest))->with('interest_cre', \FFM::dollar($total_cre_interest))->with('total_credit', \FFM::dollar($total_credited_amount))->with('total_interest_accrued', \FFM::dollar($total_interest_accrued))->make(true);
    }

    public function getInvestorInterestAccuredDetails($filter_investors = [], $date_start = null, $date_end = null)
    {
        $userId = Auth::user()->id;
        $investor_merchants = [];
        $percentage = 0;
        $dinvestors = $this->role->allInvestors()->where('investor_type', 1);
        $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $dinvestors = $dinvestors->whereNotIn('company', $disabled_companies);
        if ($filter_investors && is_array($filter_investors)) {
            $dinvestors = $dinvestors->whereIn('id', $filter_investors);
        }
        if ($date_end) {
            $today = $date_end;
        } else {
            $today = date('Y-m-d');
        }
        if (Auth::user()->hasRole(['company'])) {
            $dinvestors = $dinvestors->where('company', $userId);
        }
        foreach ($dinvestors as $key => $dinvestor) {
            $investor_merchants[$dinvestor->id] = [];
            $investor_merchants[$dinvestor->id]['name'] = $dinvestor->name;
            $total_credit = $credit_value = 0;
            $first_date = '';
            $inv_result = InvestorTransaction::where(function ($q) {
                $q->where('transaction_type', 2)->orWhere('transaction_category', 12);
            })
            ->where('investor_transactions.status', InvestorTransaction::StatusCompleted)
            ->where('investor_id', $dinvestor->id);
            if ($date_end) {
                $inv_result = $inv_result->where('date', '<=', $date_end);
            }
            $inv_result = $inv_result->orderBy('date');
            $proportion = 0;
            $inv_result = $inv_result->get();
            foreach ($inv_result as $key => $value) {
                {
                    {
                        $this_date = $value->date;
                    }
                    if ($date_start >= $value->date) {
                        $this_date = $date_start;
                    } else {
                        $this_date = $value->date;
                    }
                    $number_of_dates_obj = date_diff(new \DateTime($this_date), new \DateTime($today));
                    $this_number_of_dates = $number_of_dates_obj->format('%R%a');
                    $proportion = $this_number_of_dates ? ($this_number_of_dates + 1) / 365 : 0;
                }
                $total_credit = $total_credit + $value->amount;
                $credit_value = $credit_value + $value->amount * $proportion;
            }
            $interest_accrued = $dinvestor->interest_rate ? $credit_value * $dinvestor->interest_rate / 100 : 0;
            $investor_merchants[$dinvestor->id]['interest_accrued'] = $interest_accrued;
            $investor_merchants[$dinvestor->id]['total_credit'] = $total_credit;
            $investor_merchants[$dinvestor->id]['interest_rate'] = $dinvestor->interest_rate;
            $investor_merchants[$dinvestor->id]['inv_id'] = $dinvestor->id;
        }

        return $investor_merchants;
    }

    public function getInvestorInterestAccuredDetails_original($filter_investors = [], $date_end = null)
    {
        $investor_merchants = [];
        $percentage = 0;
        $dinvestors = $this->role->allInvestors()->where('investor_type', 1);
        if ($filter_investors && is_array($filter_investors)) {
            $dinvestors = $dinvestors->whereIn('id', $filter_investors);
        }
        if ($date_end) {
            $today = $date_end;
        } else {
            $today = date('Y-m-d');
        }
        foreach ($dinvestors as $key => $dinvestor) {
            $investor_merchants[$dinvestor->id] = [];
            $investor_merchants[$dinvestor->id]['name'] = $dinvestor->name;
            $total_credit = $credit_value = 0;
            $first_date = '';
            $inv_result = InvestorTransaction::where('transaction_type', 2)->where('investor_id', $dinvestor->id);
            if ($date_end) {
                $inv_result = $inv_result->where('created_at', '<', $date_end);
            }
            $inv_result = $inv_result->where('investor_transactions.status', InvestorTransaction::StatusCompleted);
            $inv_result = $inv_result->orderBy('date');
            $inv_result = $inv_result->get();
            foreach ($inv_result as $key => $value) {
                {
                    $this_date = $value->date;
                    $number_of_dates_obj = date_diff(new \DateTime($this_date), new \DateTime($today));
                    $this_number_of_dates = $number_of_dates_obj->format('%R%a');
                    $proportion = $this_number_of_dates ? $this_number_of_dates / 365 : 0;
                }
                $total_credit = $total_credit + $value->amount;
                $credit_value = $credit_value + $value->amount * $proportion;
            }
            $interest_accrued = $dinvestor->interest_rate ? $credit_value * $dinvestor->interest_rate / 100 : 0;
            $investor_merchants[$dinvestor->id]['interest_accrued'] = $interest_accrued;
            $investor_merchants[$dinvestor->id]['total_credit'] = $total_credit;
            $investor_merchants[$dinvestor->id]['interest_rate'] = $dinvestor->interest_rate;
        }

        return $investor_merchants;
    }

    public function calculateNetZeroWithInterest($investor)
    {
        $result_arr = [];
        $total_invested_amount_with_interest = $netzero_with_interest = $total_interest_amount = 0;
        $investmentdata = MerchantUser::where('user_id', $investor->id)->whereHas('merchant', function ($query) {
            $query->where('active_status', 1);
        })->with('merchant')->get();
        foreach ($investmentdata as $investments) {
            $merchant_id = $investments->merchant->id;
            $from = Carbon::createFromFormat('Y-m-d', $investments->merchant->date_funded);
            if ($investments->merchant->sub_status_id != 11) {
                $to = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            } else {
                $to = ParticipentPayment::where('user_id', $investor->id)->where('merchant_id', $merchant_id)->orderByDesc('id')->value('payment_date');
                $to = (isset($to) ? $to : date('Y-m-d')).' 23:59:59';
                $to = Carbon::createFromFormat('Y-m-d H:i:s', $to);
            }
            $diff_in_days = $from->diffInDays($to);
            $interest = ($investor->interest_rate / (365 * 100)) * $diff_in_days;
            $invested_amount = $investments->pre_paid + $investments->amount + ($investments->amount * $investments->merchant->commission / 100);
            $invested_amount_with_interest = $invested_amount + $invested_amount * $interest;
            $total_invested_amount_with_interest = $total_invested_amount_with_interest + ($investments->paid_participant_ishare - $invested_amount_with_interest);
            $total_interest_amount = $total_interest_amount + ($invested_amount * $interest);
        }
        $result_arr['interest'] = $total_interest_amount;
        $result_arr['netzero_with_interest'] = $total_invested_amount_with_interest;

        return $result_arr;
    }

    public function investorDailyInterestAccuredReport($merchants = null, $investor = null, $date_end = null, $columRequest = false)
    {
        if ($columRequest) {
            return [
                ['data' => 'DT_RowIndex', 'title' => 'Serial No', 'orderable' => false, 'searchable' => false],
                ['data' => 'investor_name', 'defaultContent' => '', 'title' => 'Investor', 'orderable' => false],
                ['data' => 'netzero', 'name' => 'netzero', 'title' => 'Netzero', 'orderable' => false],
                ['data' => 'interest', 'defaultContent' => '', 'title' => 'Interest', 'orderable' => false],
                ['data' => 'netzero_with_interest', 'name' => 'netzero_with_interest', 'title' => 'Netzero with Interest', 'orderable' => false],
            ];
        } else {
            $all_investor = $this->role->allInvestors()->pluck('id');
            $interest_details = User::whereIn('id', $all_investor)->whereHas('investorInterests', function ($query) use ($investor, $merchants, $date_end) {
                if ($investor && is_array($investor)) {
                    $query->whereIn('investor_id', $investor);
                }
                if ($merchants && is_array($merchants)) {
                    $query->whereIn('merchant_id', $merchants);
                }
            })->withCount(['investorInterests AS interest_inv' => function ($query) use ($date_end) {
                $query->select(DB::raw('SUM(interest) as interest_sum'));
                if ($date_end) {
                    $query->where('created_at', '<', $date_end);
                }
            }])->get('investmentData', function ($query) use ($investor, $merchants, $date_end) {
                if ($investor && is_array($investor)) {
                    $query->whereIn('user_id', $investor);
                }
                if ($merchants && is_array($merchants)) {
                    $query->whereIn('merchant_id', $merchants);
                }
                if ($date_end) {
                    $query->where('created_at', '<', $date_end);
                }
            });
            if ($investor && is_array($investor)) {
                $interest_details = $interest_details->whereIn('id', $investor);
            }
            $data = $interest_details;
            $total_amount = $paid_to_participant = $total_interest = $total_amount_with_interest = 0;
            foreach ($data as $dt) {
                $total_amount = $total_amount + $dt->amount_sum + $dt->pre_paid_sum;
                $total_interest = $total_interest + $dt->interest_inv;
                $total_amount_with_interest = $total_amount + $total_interest;
            }
            $total_net_zero = $paid_to_participant - $total_amount;
            $total_netzero_with_interest = $paid_to_participant - $total_amount_with_interest;

            return \DataTables::of($data)->addColumn('investor_name', function ($data) {
                return '<a href='.\URL::to('admin/investors/portfolio/'.$data->id).'>'.$data->name.'</a>';
            })->addColumn('netzero', function ($data) {
                $paid_to_participant = array_sum(array_column($data->investmentData->toArray(), 'paid_participant_ishare')) - array_sum(array_column($data->investmentData->toArray(), 'paid_mgmnt_fee'));
                $amount = array_sum(array_column($data->investmentData->toArray(), 'amount'));
                $commission_amount = array_sum(array_column($data->investmentData->toArray(), 'commission_amount'));
                $pre_paid = array_sum(array_column($data->investmentData->toArray(), 'pre_paid'));
                $total_amount = $amount + $commission_amount + $pre_paid;

                return FFM::dollar($paid_to_participant - $total_amount);
            })->addColumn('interest', function ($data) {
                return FFM::dollar($data->interest_inv);
            })->addColumn('netzero_with_interest', function ($data) {
                $paid_to_participant = array_sum(array_column($data->investmentData->toArray(), 'paid_participant_ishare')) - array_sum(array_column($data->investmentData->toArray(), 'paid_mgmnt_fee'));
                $amount = array_sum(array_column($data->investmentData->toArray(), 'amount'));
                $commission_amount = array_sum(array_column($data->investmentData->toArray(), 'commission_amount'));
                $pre_paid = array_sum(array_column($data->investmentData->toArray(), 'pre_paid'));
                $interest = $data->interest_inv;
                $amount_with_interest = $amount + $commission_amount + $pre_paid + $interest;

                return FFM::dollar($paid_to_participant - $amount_with_interest);
            })->addIndexColumn()->rawColumns(['investor_name'])->with('total_interest', \FFM::dollar($total_interest))->with('total_net_zero', \FFM::dollar($total_net_zero))->with('total_net_zero_with_interest', \FFM::dollar($total_netzero_with_interest))->make(true);
        }
    }

    public function investorDailyInterestAccuredReportold($merchants = null, $investor = null, $columRequest = false)
    {
        if ($columRequest) {
            return [['data' => 'DT_RowIndex', 'title' => 'Serial No', 'orderable' => false, 'searchable' => false], ['data' => 'merchant_name', 'defaultContent' => '', 'title' => 'Merchant', 'orderable' => false], ['data' => 'netzero', 'name' => 'netzero', 'title' => 'Netzero', 'orderable' => false], ['data' => 'interest', 'defaultContent' => '', 'title' => 'Interest', 'orderable' => false], ['data' => 'netzero_with_interest', 'name' => 'netzero_with_interest', 'title' => 'Netzero with Interest', 'orderable' => false]];
        } else {
            $interest_details = Merchant::whereHas('interestAccured', function ($query) use ($investor) {
                if ($investor && is_array($investor)) {
                    $query->whereIn('investor_id', $investor);
                }
            })->with(['interestAccured' => function ($query) use ($investor) {
                if ($investor && is_array($investor)) {
                    $query->whereIn('investor_id', $investor);
                }
            }])->whereHas('investmentData', function ($query) use ($investor) {
                if ($investor && is_array($investor)) {
                    $query->whereIn('user_id', $investor);
                }
            })->with(['investmentData' => function ($query) use ($investor) {
                if ($investor && is_array($investor)) {
                    $query->whereIn('user_id', $investor);
                }
            }]);
            if ($merchants && is_array($merchants)) {
                $interest_details->whereIn('id', $merchants);
            }
            $data = $interest_details->get();
            $total_interest = $total_net_zero = $total_netzero_with_interest = 0;
            foreach ($data as $dt) {
                $interest = array_sum(array_column($dt->interestAccured->toArray(), 'interest'));
                $total_interest = $total_interest + $interest;
                $paid_to_participant = array_sum(array_column($dt->investmentData->toArray(), 'paid_participant_ishare'));
                $amount = array_sum(array_column($dt->investmentData->toArray(), 'amount'));
                foreach ($dt->investmentData as $investor) {
                    $total_amount = 0;
                    $invested_amount = $investor->pre_paid + $investor->amount + ($investor->amount * $dt->commission / 100);
                    $total_amount = $total_amount + $invested_amount;
                }
                $net_zero = $paid_to_participant - $total_amount;
                $total_net_zero = $total_net_zero + $net_zero;
                $amount_with_interest = $total_amount + $interest;
                $net_zero_with_interest = $paid_to_participant - $amount_with_interest;
                $total_netzero_with_interest = $total_netzero_with_interest + $net_zero_with_interest;
            }

            return \DataTables::of($data)->addColumn('merchant_name', function ($data) {
                return $data->name;
            })->addColumn('netzero', function ($data) {
                $paid_to_participant = array_sum(array_column($data->investmentData->toArray(), 'paid_participant_ishare'));
                $amount = array_sum(array_column($data->investmentData->toArray(), 'amount'));
                foreach ($data->investmentData as $investor) {
                    $total_amount = 0;
                    $invested_amount = $investor->pre_paid + $investor->amount + ($investor->amount * $data->commission / 100);
                    $total_amount = $total_amount + $invested_amount;
                }

                return $paid_to_participant - $total_amount;
            })->addColumn('interest', function ($data) {
                return array_sum(array_column($data->interestAccured->toArray(), 'interest'));
            })->addColumn('netzero_with_interest', function ($data) {
                $paid_to_participant = array_sum(array_column($data->investmentData->toArray(), 'paid_participant_ishare'));
                $amount = array_sum(array_column($data->investmentData->toArray(), 'amount'));
                foreach ($data->investmentData as $investor) {
                    $total_amount = 0;
                    $invested_amount = $investor->pre_paid + $investor->amount + ($investor->amount * $data->commission / 100);
                    $total_amount = $total_amount + $invested_amount;
                }
                $interest = array_sum(array_column($data->interestAccured->toArray(), 'interest'));
                $amount_with_interest = $total_amount + $interest;

                return $paid_to_participant - $amount_with_interest;
            })->addIndexColumn()->with('total_interest', \FFM::dollar($total_interest))->with('total_net_zero', \FFM::dollar($total_net_zero))->with('total_net_zero_with_interest', \FFM::dollar($total_netzero_with_interest))->make(true);
        }
    }

    public function reconcileReport($start_date = null, $end_date = null, $lenders = null)
    {
        $data = DB::table('reconciles')->whereNotNull('batch')->join('users', 'reconciles.lender_id', '=', 'users.id');
        if ($start_date) {
            $data = $data->where('day', '>', $start_date);
        }
        if ($end_date) {
            $data = $data->where('day', '<', $end_date);
        }
        if ($lenders) {
            $data = $data->whereIn('lender_id', $lenders);
        }
        $total_amount = $data->sum(DB::raw('reconciles.amount'));
        $total_actual_amount = $data->sum(DB::raw('reconciles.actual_amount'));
        $data1 = $data->select('users.name', DB::raw('group_concat(DATE_FORMAT(day, " %m-%d-%Y")) as day'), DB::raw('SUM(amount) as amount'), 'reconciles.created_at', 'reconciles.id', 'reconciles.batch', DB::raw('SUM(actual_amount) as actual_amount'))->whereNotNull('batch')->orderByDesc('reconciles.id')->groupBy('batch')->get()->toArray();

        return \DataTables::of($data1)->addColumn('action', function ($data) {
            $del = '';
            if (Permissions::isAllow('Reconcile', 'Delete')) {
                $del = Form::open(['route' => ['admin::reports::delete', 'id' => $data->batch], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }

            return $del;
        })->addColumn('amount', function ($data) {
            return \FFM::dollar($data->amount);
        })->addColumn('actual_amount', function ($data) {
            return \FFM::dollar($data->actual_amount);
        })->addColumn('created_at', function ($data) {
            return \FFM::datetime($data->created_at);
        })->addColumn('total_credit', function ($data) {
        })->addColumn('investor_interest_rate', function ($data) {
        })->with('amount', \FFM::dollar($total_amount))->with('actual_amount', \FFM::dollar($total_actual_amount))->make(true);
    }

    public function paymentReportDetails($merchants = null, $column = false)
    {
        $data = ['id' => 1];
        if ($column == true) {
            return [['className' => 'details-control', 'orderable' => false, 'data' => 'null', 'name' => 'investor', 'defaultContent' => '', 'title' => '', 'searchable' => false], ['data' => 'id', 'defaultContent' => '', 'title' => 'No'], ['data' => 'merchant_id', 'name' => 'merchant_id', 'title' => 'Merchant'], ['data' => 'payment_date', 'name' => 'payment_date', 'title' => 'Payment Date'], ['data' => 'payment', 'name' => 'payment', 'title' => 'Payment amount']];
        } else {
            $data = ParticipentPayment::whereHas('merchant', function ($query) use ($merchants) {
                $query->where('active_status', 1);
                if ($merchants && is_array($merchants)) {
                    $query->whereIn('merchant_id', $merchants);
                }
            })->with(['merchant']);
            $data = $data->whereHas('paymentInvestors', function ($query) {
            })->with(['paymentInvestors']);

            return \DataTables::of($data)->editColumn('investor', function ($data) {
                $array = [];
                foreach ($data->paymentInvestors as $dt) {
                    $array[] = ['investor' => $dt->user_id, 'participant_share' => $dt->participant_share, 'mgmnt_fee' => $dt->mgmnt_fee, 'syndication_fee' => $dt->syndication_fee];
                }

                return $array;
            })->addColumn('merchant_id', function ($data) {
                return $data->merchant->name;
            })->addColumn('payment_date', function ($data) {
                return $data->payment_date;
            })->addColumn('payment', function ($data) {
                return $data->payment;
            })->addIndexColumn()->make(true);
        }
    }

    public function netZeroMerchant($merchant_id)
    {
        $res = MerchantUser::where('merchant_id', $merchant_id)->select(DB::raw('sum(merchant_user.paid_participant_ishare) as participant_share'), DB::raw('sum(merchant_user.pre_paid) as pre_paid'), DB::raw('sum(merchant_user.amount) as amount'), DB::raw('sum(merchant_user.commission_amount) as commission_amount'), DB::raw('sum(merchant_user.paid_mgmnt_fee) as paid_fee'))->first();
        $paid_to_participant = $res->participant_share - $res->paid_fee;
        $invested_amount_with_commission = $res->pre_paid + $res->amount + $res->commission_amount;
        $net_value = $invested_amount_with_commission;
        $netzero = $paid_to_participant - $net_value;

        return $netzero;
    }

    public function paymentrepData($date_type = null, $sDate = null, $eDate = null, $ids = null, $userIds = null, $lids = null, $payment_type = null, $owner = null, $sub_statuses = null, $investor_type = null, $fields = null, $export_checkbox = null)
    {
        $investors = $userIds;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $company_users_q = DB::table('users');
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->where('id', $investors);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        if ($owner) {
            $company_users_q = $company_users_q->where('company', $owner);
        }
        $company_users = $company_users_q->pluck('id')->toArray();
        $merchants = Merchant::leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')->with(['investments' => function ($query) use ($permission, $company_users) {
            $query->select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'), DB::raw('sum(merchant_user.paid_participant_ishare) as paid_participant_ishare'), DB::raw('sum(merchant_user.mgmnt_fee) as total_mgmnt_fee'), DB::raw('sum(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid) as invested_amount'), DB::raw('sum(merchant_user.invest_rtr) as total_rtr'), DB::raw('sum(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as mgmnt_fee_amount'));
            $query->groupBy('merchant_id');
            if (! empty($company_users)) {
                $query->whereIn('merchant_user.user_id', $company_users);
            }
        }])->select('merchants.id', 'merchants.name', 'merchants.last_payment_date', 'rcode.code', 'merchants.last_rcode', 'rtr', 'payment_amount', 'pmnts', 'complete_percentage', 'date_funded', 'm_mgmnt_fee', 'm_syndication_fee', DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id ORDER BY payment_date DESC limit 1) last_payment_amount'))->whereHas('participantPayment', function ($q) use ($sDate, $eDate, $ids, $userIds, $date_type, $payment_type, $permission, $company_users) {
            $q->where('participent_payments.is_payment', 1);
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->groupBy('payment_investors.id');
            $q->select('participent_payment_id', 'user_id', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.id', 'participent_payments.payment_date', DB::raw(' sum(payment_investors.profit) as profit'), DB::raw(' sum(payment_investors.principal) as principal'), DB::raw(' sum(payment_investors.mgmnt_fee) as mgmnt_fee'), DB::raw('sum(payment_investors.participant_share) as participant_share'), DB::raw(' sum(payment_investors.participant_share-mgmnt_fee) as final_participant_share', 'participent_payments.rcode'));
            $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'participent_payments.payment_date';
            if (is_array($userIds)) {
                $q->whereIn('payment_investors.user_id', $userIds);
            }
            if (empty($permission)) {
            }
            if ($eDate) {
                $q->where($table_field, '<=', $eDate);
            }
            if ($sDate) {
                $q->where($table_field, '>=', $sDate);
            }
            if ($payment_type != null) {
                $q->where('payment_type', $payment_type);
            }
            $q->orderByDesc('participent_payment_id');
            $q->whereIn('payment_investors.user_id', $company_users);
        })->with(['participantPayment' => function ($q) use ($sDate, $eDate, $ids, $userIds, $date_type, $payment_type, $permission, $company_users) {
            $q->where('participent_payments.is_payment', 1);
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->groupBy('payment_investors.participent_payment_id');
            $q->select('participent_payment_id', 'user_id', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.id', 'participent_payments.payment_date', DB::raw('
                        sum(payment_investors.profit) as profit,sum(payment_investors.principal) as principal,
                        sum(payment_investors.mgmnt_fee) as mgmnt_fee
                        ,
                        sum(payment_investors.participant_share) as participant_share'), DB::raw(' sum(payment_investors.participant_share-mgmnt_fee) as final_participant_share'));
            $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'payment_date';
            if (is_array($userIds)) {
                $q->whereIn('payment_investors.user_id', $userIds);
            }
            if ($eDate) {
                $q->where($table_field, '<=', $eDate);
            }
            if ($sDate) {
                $q->where($table_field, '>=', $sDate);
            }
            if ($payment_type != null) {
                $q->where('payment_type', $payment_type);
            }
            $q->orderByDesc('participent_payment_id');
            $q->whereIn('payment_investors.user_id', $company_users);
        }]);
        $merchants = $merchants->where('merchants.active_status', 1);
        $merchants = $merchants->join('users as lender', 'merchants.lender_id', 'lender.id');
        if ($lids) {
            $merchants->whereIn('merchants.lender_id', $lids);
        }
        if ($sub_statuses) {
            $merchants->whereIn('merchants.sub_status_id', $sub_statuses);
        }
        if ($ids != null) {
            $merchants = $merchants->whereIn('merchants.id', $ids);
        }
        $merchants = $merchants->orderByDesc('date_funded');

        return $merchants;
    }

    public function getChartData($attribute = null, $type = null, $flag = null, $eDate = null)
    {
        switch ($attribute) {
            case 0:
                $query = DB::table('merchants')->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->join('sub_statuses', 'merchants.sub_status_id', 'sub_statuses.id')->whereIn('merchant_user.status', [1, 3]);
                if ($type == 0) {
                    $query = $query->where('active_status', 1)->join('label', 'merchants.label', 'label.id')->groupBy('merchants.label')->select('merchants.sub_status_id', 'label.name as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM( merchant_user.up_sell_commission) as amount'));
                } elseif ($type == 2) {
                    $query = $query->where('active_status', 1)->join('label', 'merchants.label', 'label.id')->groupBy('merchants.label')->select('merchants.sub_status_id', 'label.name as name', DB::raw('SUM( 
                                    merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                } else {
                    $array1 = implode(',', [4, 22]);
                    $default_date = $eDate;
                    $merchant_day = PayCalc::setDaysCalculation($default_date);
                    $query = $query->whereIn('merchants.sub_status_id', [4, 22])->join('label', 'merchants.label', 'label.id')->groupBy('merchants.label')->select('merchants.sub_status_id', 'label.name as name', DB::raw('('.$merchant_day.' *( sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) - ( sum( IF(sub_status_id IN ('.$array1.'), (merchant_user.actual_paid_participant_ishare - IF(merchant_user.paid_mgmnt_fee,merchant_user.paid_mgmnt_fee,0)), ( IF((merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) < (merchant_user.actual_paid_participant_ishare - IF(merchant_user.paid_mgmnt_fee,merchant_user.paid_mgmnt_fee,0) ), (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission), (merchant_user.actual_paid_participant_ishare - IF(merchant_user.paid_mgmnt_fee,merchant_user.paid_mgmnt_fee,0) ) )) ) ) ) ) ) as amount'));
                }
                break;
            case 1:
                $query = DB::table('merchants')->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->join('sub_statuses', 'merchants.sub_status_id', 'sub_statuses.id')->whereIn('merchant_user.status', [1, 3]);
                if ($type == 0) {
                    $query = $query->where('active_status', 1)->groupBy('merchants.sub_status_id')->select('merchants.sub_status_id', 'sub_statuses.name as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee) +SUM(merchant_user.up_sell_commission) as amount'));
                } elseif ($type == 2) {
                    $query = $query->where('active_status', 1)->groupBy('merchants.sub_status_id')->select('merchants.sub_status_id', 'sub_statuses.name as name', DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                } else {
                    $query = $query->whereIn('merchants.sub_status_id', [4, 22])->groupBy('merchants.sub_status_id')->select('merchants.sub_status_id', 'sub_statuses.name as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) - SUM( merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee) as amount'));
                }
                break;
            case 2:
                $query = DB::table('merchants')->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->leftjoin('industries', 'merchants.industry_id', 'industries.id')->whereIn('merchant_user.status', [1, 3]);
                if ($type == 0) {
                    $query = $query->where('active_status', 1)->groupBy('merchants.industry_id')->select('merchants.industry_id', 'industries.name as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) as amount'))->where('amount','!=',0);
                } elseif ($type == 2) {
                    $query = $query->where('active_status', 1)->groupBy('merchants.industry_id')->select('merchants.industry_id', 'industries.name as name', DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'))->where('amount','!=',0);
                } else {
                    $query = $query->whereIn('merchants.sub_status_id', [4, 22])->groupBy('merchants.industry_id')->select('merchants.industry_id', 'industries.name as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) - SUM( merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee) as amount'))->where('amount','!=',0);
                }
                break;
            case 3:
                $query = DB::table('merchants')->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->join('users', 'merchant_user.user_id', 'users.id')->whereIn('merchant_user.status', [1, 3]);
                if ($type == 0) {
                    $query = $query->where('merchants.active_status', 1)->whereNotIn('users.id', [600,504])->groupBy('merchant_user.user_id')->select('merchants.user_id', 'users.name as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) as amount'));
                } elseif ($type == 2) {
                    $query = $query->where('merchants.active_status', 1)->whereNotIn('users.id', [600,504])->groupBy('merchant_user.user_id')->select('merchants.user_id', 'users.name as name', DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                } else {
                    $array1 = implode(',', [4, 22]);
                    $default_date = $eDate;
                    $merchant_day = PayCalc::setDaysCalculation($default_date);
                    $query = $query->whereIn('merchants.sub_status_id', [4, 22])->whereNotIn('users.id', [600,504])->groupBy('merchant_user.user_id')->select('merchants.user_id', 'users.name as name', DB::raw('('.$merchant_day.' *( sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) - ( sum( IF(sub_status_id IN ('.$array1.'), (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee), ( IF((merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) < (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ), (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission), (merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ) )) ) ) ) ) ) as amount'));
                }
                break;
            case 4:
                $query = DB::table('merchants')->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->leftjoin('users', 'merchants.lender_id', 'users.id')->whereIn('merchant_user.status', [1, 3]);
                if ($type == 0) {
                    $query = $query->where('merchants.active_status', 1)->groupBy('merchants.lender_id')->select('merchants.lender_id', 'users.name as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) as amount'));
                } elseif ($type == 2) {
                    $query = $query->where('merchants.active_status', 1)->groupBy('merchants.lender_id')->select('merchants.lender_id', 'users.name as name', DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                } else {
                    $query = $query->whereIn('merchants.sub_status_id', [4, 22])->groupBy('merchants.lender_id')->select('merchants.lender_id', 'users.name as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) - SUM( merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee) as amount'));
                }
                break;
            case 5:
                $query = DB::table('merchants')->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id');
                if ($type == 0) {
                    $query = $query->whereIn('merchant_user.status', [1, 3])->where('merchants.active_status', 1)->groupBy('merchants.commission')->select('merchants.commission  as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) as amount'));
                } elseif ($type == 2) {
                    $query = $query->whereIn('merchant_user.status', [1, 3])->where('merchants.active_status', 1)->groupBy('merchants.commission')->select('merchants.commission  as name', DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                } else {
                    $query = $query->whereIn('merchant_user.status', [1, 3])->whereIn('merchants.sub_status_id', [4, 22])->groupBy('merchants.commission')->select('merchants.commission as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) - SUM( merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee) as amount'));
                }
                break;
            case 6:
                $query = DB::table('merchants')->join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->whereIn('merchant_user.status', [1, 3]);
                if ($type == 0) {
                    $query = $query->where('active_status', 1)->groupBy('merchants.factor_rate')->select('merchants.factor_rate as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) as amount'));
                } elseif ($type == 2) {
                    $query = $query->where('active_status', 1)->groupBy('merchants.factor_rate')->select('merchants.factor_rate as name', DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                } else {
                    $query = $query->whereIn('merchants.sub_status_id', [4, 22])->groupBy('merchants.factor_rate')->select('merchants.id', 'merchants.factor_rate', 'merchants.factor_rate as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) - SUM( merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee) as amount'));
                }
                break;
            case 7:
                $query = DB::table('merchants')->leftjoin('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->leftjoin('us_states', 'merchants.state_id', 'us_states.id')->whereIn('merchant_user.status', [1, 3]);
                if ($type == 0) {
                    $query = $query->where('active_status', 1)->groupBy('merchants.state_id')->select('merchants.state_id', 'us_states.state as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) as amount'));
                } elseif ($type == 2) {
                    $query = $query->whereIn('merchants.sub_status_id', [4, 22])->where('active_status', 1)->groupBy('merchants.state_id')->select('merchants.state_id', 'us_states.state as name', DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                } else {
                    $query = $query->whereIn('merchants.sub_status_id', [4, 22])->groupBy('merchants.state_id')->select('merchants.state_id', 'us_states.state as name', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) - SUM( merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee) as amount'));
                }
                break;
            case 8:
                $query = DB::table('merchants')->leftjoin('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->whereIn('merchant_user.status', [1, 3]);
                if ($type == 0) {
                    if ($flag == 1) {
                        $query = $query->where('active_status', 1)->select('merchants.state_id', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) as amount'));
                    } else {
                        $query = $query->where('active_status', 1)->select(DB::raw('IF(500<1000,"Total","Total") as name'), DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) as amount'));
                    }
                } elseif ($type == 2) {
                    if ($flag == 1) {
                        $query = $query->where('active_status', 1)->select('merchants.state_id', DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                    } else {
                        $query = $query->where('active_status', 1)->select(DB::raw('IF(500<1000,"Total","Total") as name'), DB::raw('SUM( merchant_user.invest_rtr) -SUM( merchant_user.paid_participant_ishare) as amount'));
                    }
                } else {
                    if ($flag == 1) {
                        $query = $query->whereIn('merchants.sub_status_id', [4, 22])->select('merchants.state_id', DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) - SUM( merchant_user.paid_participant_ishare) as amount'));
                    } else {
                        $query = $query->whereIn('merchants.sub_status_id', [4, 22])->select(DB::raw('IF(500<1000,"Total","Total") as name'), DB::raw('SUM( merchant_user.amount) +SUM( merchant_user.commission_amount)+SUM( merchant_user.pre_paid) +SUM( merchant_user.under_writing_fee)+SUM(merchant_user.up_sell_commission) - SUM( merchant_user.paid_participant_ishare) as amount'));
                    }
                }
                break;
        }

        return $query;
    }

    public function getAllStatementsMerchant($start_date = null, $end_date = null, $merchants = null, $column = false)
    {
        if ($column) {
            return [['data' => 'checkbox', 'name' => 'checkbox', 'title' => '<label class="chc" title=""><input type="checkbox" name="delete_multi_statement"  id="checked_statement"><span class="checkmark checkk"></span>
                          </label>', 'orderable' => false, 'searchable' => false], ['data' => 'statement_id', 'title' => '#', 'orderable' => false, 'searchable' => false], ['data' => 'merchants.name', 'name' => 'merchants.name', 'defaultContent' => '', 'title' => 'Merchant'], ['data' => 'file_name', 'name' => 'file_name', 'title' => 'PDF statement'], ['data' => 'merchant_statements.created_at', 'name' => 'created_at', 'title' => 'Date']];
        }
        $userId = Auth::user()->id;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $data = MerchantStatement::select(DB::raw('upper(merchants.name) as name'), 'file_name', 'merchant_statements.created_at', 'merchant_statements.id', 'merchant_statements.to_date', 'merchant_statements.id as statement_id', 'merchant_statements.mail_status', 'merchant_statements.creator_id')->join('merchants', 'merchants.id', 'merchant_statements.merchant_id');
        if (is_array($merchants)) {
            $data = $data->whereIn('merchant_statements.merchant_id', $merchants);
        }
        if ($start_date) {
            $start_date = $start_date;
            $data = $data->whereDate('merchant_statements.created_at', '>=', $start_date);
        }
        if ($end_date) {
            $end_date = $end_date;
            $data = $data->whereDate('merchant_statements.created_at', '<=', $end_date);
        }
        $data = $data->orderByDesc('merchant_statements.created_at');
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($data)->addColumn('checkbox', function ($data) {
            $id = $data->statement_id;

            return "<label class='chc'><input type='checkbox' class='checked_data' name='delete_statement[]' value='$id' onclick='uncheckMain();'><span class='checkmark checkk0'></span></label>";
        })->editColumn('merchants.name', function ($data) {
            return $data->name;
        })->editColumn('file_name', function ($data) {
            $file = $data->file_name.'.pdf';
            $fileencrypt = encrypt($file);
            $fileUrl = route('admin::generated-file',[$fileencrypt]);
            if (Storage::disk('s3')->has($file) == 'true') {
                return "<a href='".$fileUrl."'>".$file.'</a>';
            } else {
                return 'Not found';
            }
        })->addColumn('merchant_statements.created_at', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

            return "<a title='$created_date'>".FFM::datetimetodate($data->created_at).'</a>';
        })->rawColumns(['checkbox', 'file_name', 'merchant_statements.created_at'])->filterColumn('name', function ($query, $keyword) {
            $sql = 'merchants.name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('file_name', function ($query, $keyword) {
            $sql = 'file_name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->filterColumn('created_at', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(merchant_statements.created_at,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })->make(true);
    }

    public function getVelocityProfitabilityReportDataTableOLD($start_date = null, $end_date = null, $company = null, $lender = null, $investor = null, $column = false)
    {
        if ($column) {
            return [['data' => '', 'title' => 'Serial No', 'defaultContent' => '', 'orderable' => false, 'searchable' => false], ['data' => 'merchant_name', 'name' => 'merchant_name', 'title' => 'Merchant'], ['data' => 'origination_fee', 'name' => 'origination_fee', 'title' => 'Origination Fee', 'searchable' => false], ['data' => 'underwriting_fee_flat', 'name' => 'underwriting_fee_flat', 'title' => 'Flat Under Writing Fee', 'searchable' => false], ['data' => 'syndication_fee', 'name' => 'syndication_fee', 'title' => 'Syndication Fee', 'searchable' => false], ['data' => 'management_fee_earned', 'name' => 'management_fee_earned', 'title' => 'Management Fee', 'searchable' => false], ['data' => 'underwriting_fee_earned', 'name' => 'underwriting_fee_earned', 'title' => 'Under Writing Fee', 'searchable' => false], ['data' => 'date_funded', 'name' => 'date_funded', 'title' => 'Funded Date', 'searchable' => false]];
        }
        $data = $this->getVelocityProfitabilityReport($start_date, $end_date, $company, $lender, $investor);
        $data1 = $data->get();
        $total_origination_fee = $data1->sum('origination_fee');
        $total_underwriting_fee_flat = $data1->sum('underwriting_fee_flat');
        $total_syndication_fee = $data1->sum('syndication_fee');
        $total_management_fee_earned = $data1->sum('management_fee_earned');
        $total_underwriting_fee_earned = $data1->sum('underwriting_fee_earned');

        return \DataTables::of($data)->editColumn('date_funded', function ($data) {
            return FFM::date($data->date_funded);
        })->editColumn('origination_fee', function ($data) {
            return FFM::dollar($data->origination_fee);
        })->editColumn('underwriting_fee_flat', function ($data) {
            return FFM::dollar($data->underwriting_fee_flat);
        })->editColumn('syndication_fee', function ($data) {
            return FFM::dollar($data->syndication_fee);
        })->editColumn('management_fee_earned', function ($data) {
            return FFM::dollar($data->management_fee_earned);
        })->editColumn('underwriting_fee_earned', function ($data) {
            return FFM::dollar($data->underwriting_fee_earned);
        })->filterColumn('merchant_name', function ($query, $keyword) {
            $sql = 'merchants.name  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->with('total_origination_fee', FFM::dollar($total_origination_fee))->with('total_underwriting_fee_flat', FFM::dollar($total_underwriting_fee_flat))->with('total_syndication_fee', FFM::dollar($total_syndication_fee))->with('total_management_fee_earned', FFM::dollar($total_management_fee_earned))->with('total_underwriting_fee_earned', FFM::dollar($total_underwriting_fee_earned))->make(true);
    }

    public function getVelocityProfitabilityReportDataTable($start_date = null, $end_date = null, $company = null, $investor = null, $label = null, $column = false)
    {
        if ($column) {
            return [['data' => '', 'title' => '#', 'defaultContent' => '', 'orderable' => false, 'searchable' => false], ['data' => 'merchants.name', 'name' => 'merchants.name', 'title' => 'Merchant'], 
            ['data' => 'date_funded', 'name' => 'date_funded', 'title' => 'Funded Date','orderable' => true],
            ['data' => 'origination_fee', 'name' => 'origination_fee', 'title' => 'Origination Fee'], ['data' => 'up_sell_commission', 'name' => 'up_sell_commission', 'title' => 'Up sell commission'], ['data' => 'underwriting_fee_flat', 'name' => 'underwriting_fee_flat', 'title' => 'Flat Under Writing Fee'], ['data' => 'syndication_fee', 'name' => 'syndication_fee', 'title' => 'Syndication Fee'], ['data' => 'management_fee_earned', 'name' => 'management_fee_earned', 'title' => 'Management Fee'], ['data' => 'underwriting_fee_earned', 'name' => 'underwriting_fee_earned', 'title' => 'Under Writing Fee'], ['data' => 'ach_fee', 'name' => 'ach_fee', 'title' => 'Total ACH Fees']];
        }
        $result_data = $this->getVelocityProfitabilityReport($start_date, $end_date, $company, $investor, $label);
        $data = $result_data['data'];
        $mgmnt_fee = $result_data['mgmnt_fee'];
        $ach_fees = $result_data['ach_fees'];
        $data1 = $data->with('owner')->get();
        $result = $data1->toArray();
        $new_array = [];
        $total_management_fee_earned = 0;
        foreach ($result as $key => $value) {
            $management_fee = isset($mgmnt_fee[$value['id']]) ? $mgmnt_fee[$value['id']] : 0;
            $total_management_fee_earned = $total_management_fee_earned + $management_fee;
            $ach_fee = isset($ach_fees[$value['id']]) ? $ach_fees[$value['id']] : 0;
            $creator = isset($value['owner']) ? $value['owner']['name'] : '--';
            if ($value['within_funded_date'] == 'no' && ! isset($mgmnt_fee[$value['id']])) {
                if ($ach_fee) {
                    unset($ach_fees[$value['id']]);
                }
                continue;
            }
            $new_array[$key]['management_fee_earned'] = $management_fee;
            $new_array[$key]['ach_fee'] = $ach_fee;
            $new_array[$key]['origination_fee'] = $value['origination_fee'];
            $new_array[$key]['underwriting_fee_flat'] = $value['underwriting_fee_flat'];
            $new_array[$key]['syndication_fee'] = $value['syndication_fee'];
            $new_array[$key]['underwriting_fee_earned'] = $value['underwriting_fee_earned'];
            $new_array[$key]['date_funded'] = $value['date_funded'];
            $new_array[$key]['merchant_name'] = $value['merchant_name'];
            $new_array[$key]['monthly_revenue'] = $value['monthly_revenue'];
            $new_array[$key]['up_sell_commission'] = $value['up_sell_commission'];
            $new_array[$key]['merchant_id'] = $value['id'];
            $new_array[$key]['creator'] = $creator;
            $new_array[$key]['created_at'] = $value['created_at'];
        }
        $total_origination_fee = array_sum(array_column($new_array, 'origination_fee'));
        $total_underwriting_fee_flat = array_sum(array_column($new_array, 'underwriting_fee_flat'));
        $total_syndication_fee = array_sum(array_column($new_array, 'syndication_fee'));
        $total_underwriting_fee_earned = array_sum(array_column($new_array, 'underwriting_fee_earned'));
        $total_monthly_revenue = array_sum(array_column($new_array, 'monthly_revenue'));
        $total_up_sell_commission = array_sum(array_column($new_array, 'up_sell_commission'));
        $total_ach_fee = array_sum($ach_fees);
        $total_fees = $total_origination_fee + $total_up_sell_commission + $total_underwriting_fee_flat + $total_syndication_fee + $total_management_fee_earned + $total_underwriting_fee_earned + $total_ach_fee;

        return \DataTables::of($new_array)->editColumn('date_funded', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data['created_at']).' by '.$data['creator'];
            return "<span style='display:none;'>".$data['date_funded']."</span><a title='$created_date'>".FFM::date($data['date_funded']).'</a>';
        })->editColumn('origination_fee', function ($data) {
            return FFM::dollar($data['origination_fee']);
        })->editColumn('underwriting_fee_flat', function ($data) {
            return FFM::dollar($data['underwriting_fee_flat']);
        })->editColumn('syndication_fee', function ($data) {
            return FFM::dollar($data['syndication_fee']);
        })->editColumn('management_fee_earned', function ($data) {
            return FFM::dollar($data['management_fee_earned']);
        })->editColumn('underwriting_fee_earned', function ($data) {
            return FFM::dollar($data['underwriting_fee_earned']);
        })->editColumn('monthly_revenue', function ($data) {
            return FFM::dollar($data['monthly_revenue']);
        })->editColumn('up_sell_commission', function ($data) {
            return FFM::dollar($data['up_sell_commission']);
        })->editColumn('ach_fee', function ($data) {
            return FFM::dollar($data['ach_fee']);
        })
        ->editColumn('merchants.name', function ($data) {
            return '<a href="'.route('admin::merchants::view', ['id' => $data['merchant_id']]).'"><i class="glyphicon glyphicon-view"></i> '.$data['merchant_name'].'</a>';
        })->with('Total', 'Total:')->with('total_origination_fee', FFM::dollar($total_origination_fee))->with('total_underwriting_fee_flat', FFM::dollar($total_underwriting_fee_flat))->with('total_syndication_fee', FFM::dollar($total_syndication_fee))->with('total_management_fee_earned', FFM::dollar($total_management_fee_earned))->with('total_underwriting_fee_earned', FFM::dollar($total_underwriting_fee_earned))->with('total_monthly_revenue', FFM::dollar($total_monthly_revenue))->with('total_up_sell_commission', FFM::dollar($total_up_sell_commission))->with('total_ach_fee', FFM::dollar($total_ach_fee))->with('total_fees', FFM::dollar($total_fees))->rawColumns(['merchants.name', 'date_funded'])->addIndexColumn()->make(true);
    }

    public function getVelocityProfitabilityReportOLD($start_date = null, $end_date = null)
    {
        $data = Merchant::select('merchants.id', 'merchants.name as merchant_name', 'merchants.origination_fee', 'merchants.date_funded', DB::raw('
                sum(merchant_user.pre_paid) as syndication_fee, 
                sum(merchant_user.under_writing_fee) as underwriting_fee_earned,
                sum(merchant_user.paid_participant_ishare * (merchant_user.mgmnt_fee / 100)) as management_fee_earned,
                350 as underwriting_fee_flat
            '))->groupBy('merchant_user.merchant_id')->leftJoin('merchant_user', 'merchants.id', '=', 'merchant_user.merchant_id');
        if ($start_date) {
            $data = $data->whereDate('merchants.date_funded', '>=', $start_date);
        }
        if ($end_date) {
            $data = $data->whereDate('merchants.date_funded', '<=', $end_date);
        }

        return $data;
    }

    public function getVelocityProfitabilityReportnew($start_date = null, $end_date = null, $company = null, $lender = null, $investors = null)
    {
        $date_query = '';
        $userQuery = '';
        $companyQuery = '';
        $date_fund_query = 1;
        $investors_list = 0;
        $table_field = 'payment_date';
        $merchant_table_field = 'merchants.date_funded';
        if ($start_date != null && $end_date != null) {
            $date_query = "AND $table_field >= '$start_date' AND $table_field <= '$end_date'";
            $date_fund_query = "$merchant_table_field >= '$start_date' AND $merchant_table_field <= '$end_date'";
        } elseif ($start_date != null) {
            $date_query = "AND $table_field >= '$start_date'";
            $date_fund_query = "$merchant_table_field >= '$start_date'";
        } elseif ($end_date != null) {
            $date_query = "AND $table_field <= '$end_date'";
            $date_fund_query = "$merchant_table_field <= '$end_date'";
        }
        $company_users = [];
        if ($company) {
            $company_users = DB::table('users')->where('company', $company)->pluck('id')->toArray();
            if ($company_users) {
                $companylist = implode(',', $company_users);
                $companyQuery = 'AND user_id in ('.$companylist.')';
            }
        }
        if ($investors) {
            $investors_list = implode(',', $investors);
            $userQuery = 'AND user_id in ('.$investors_list.')';
        }
        $data = Merchant::select('merchants.id', 'merchants.lender_id', 'merchants.name as merchant_name', 'merchants.origination_fee', 'merchants.date_funded', DB::raw("merch_payment_sub.management_fee_earned,merch_payment_sub.payment_date,
                   sum(IF($date_fund_query =1,(IF(merchants.lender_id=74,merchant_user.pre_paid,0)),IF($date_fund_query,(IF(merchants.lender_id=74,merchant_user.pre_paid,0)),0))) as syndication_fee,                  
                 sum(IF($date_fund_query =1,(IF(merchants.lender_id=74,merchant_user.under_writing_fee,0)),IF($date_fund_query,(IF(merchants.lender_id=74,merchant_user.under_writing_fee,0)),0))) as underwriting_fee_earned,
                
                IF($date_fund_query =1,(IF(merchants.lender_id=74,350,0)),IF($date_fund_query,(IF(merchants.lender_id=74,350,0)),0)) as underwriting_fee_flat
            "))->groupBy('merchants.id')->leftJoin('merchant_user', 'merchants.id', '=', 'merchant_user.merchant_id')->leftJoin(DB::raw("(SELECT SUM(payment_investors.mgmnt_fee) AS management_fee_earned,
                participent_payments.merchant_id,participent_payments.payment_date FROM payment_investors  
                LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id WHERE participent_payments.merchant_id > 0 AND participent_payments.payment_type = 1 $date_query $userQuery $companyQuery
                 GROUP BY participent_payments.merchant_id) as merch_payment_sub"), 'merch_payment_sub.merchant_id', '=', 'merchants.id');
        if ($start_date) {
            $data->where(function ($query) use ($start_date) {
                $query->whereDate('merchants.date_funded', '>=', $start_date);
                $query->orWhereDate('merch_payment_sub.payment_date', '>=', $start_date);
            });
        }
        if ($end_date) {
            $data->where(function ($query) use ($end_date) {
                $query->whereDate('merchants.date_funded', '<=', $end_date);
                $query->orWhereDate('merch_payment_sub.payment_date', '<=', $end_date);
            });
        }
        if ($lender) {
            $data = $data->whereIn('lender_id', $lender);
        }
        if ($investors) {
            $data = $data->whereIn('merchant_user.user_id', $investors);
        }
        if ($company_users) {
            $data = $data->whereIn('merchant_user.user_id', $company_users);
        }

        return $data;
    }

    public function getVelocityProfitabilityReport($start_date = null, $end_date = null, $company = null, $investors = null, $label = null)
    {
        $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $company_users = DB::table('users')->whereNotIn('company',$disabled_companies);
        if ($company) {
            $company_users = $company_users->where('company', $company);
        }
        $company_users = $company_users->pluck('id')->toArray();
        $date_fund_query = 1;
        $table_field = 'payment_date';
        $merchant_table_field = 'merchants.date_funded';
        if ($start_date != null && $end_date != null) {
            $date_fund_query = "$merchant_table_field >= '$start_date' AND $merchant_table_field <= '$end_date'";
        } elseif ($start_date != null) {
            $date_fund_query = "$merchant_table_field >= '$start_date'";
        } elseif ($end_date != null) {
            $date_fund_query = "$merchant_table_field <= '$end_date'";
        }

        $ach_fees = VelocityFee::where('velocity_fees.status', 1)->select('velocity_fees.merchant_id', DB::raw('sum(velocity_fees.payment_amount) as ach_fees'), 'merchants.label')->join('merchants', 'merchants.id', 'velocity_fees.merchant_id');
        if ($start_date) {
            $ach_fees = $ach_fees->where('velocity_fees.payment_date', '>=', $start_date);
        }
        if ($end_date) {
            $ach_fees = $ach_fees->where('velocity_fees.payment_date', '<=', $end_date);
        }
        if ($label) {
            $ach_fees = $ach_fees->whereIn('merchants.label', $label);
        }
        if ($company) {
            $ach_fees = $ach_fees->whereHas('merchantUsers', function (Builder $query) use ($company_users) {
                $query->whereIn('user_id', $company_users);
            });
        }
        if ($investors) {
            $ach_fees = $ach_fees->whereHas('merchantUsers', function (Builder $query) use ($investors) {
                $query->whereIn('user_id', $investors);
            });
        }
        $ach_fees = $ach_fees->groupBy('velocity_fees.merchant_id')->pluck('ach_fees', 'velocity_fees.merchant_id')->toArray();
        $data = Merchant::leftjoin('merchants_details','merchants_details.merchant_id','merchants.id')->select('merchants.id', 'merchants_details.monthly_revenue', 'merchants.lender_id', 'merchants.name as merchant_name', 'merchants.date_funded', 'merchants.creator_id', 'merchants.created_at', DB::raw("
                sum(IF($date_fund_query =1,merchant_user.pre_paid,IF($date_fund_query,merchant_user.pre_paid,0))) as syndication_fee,                  
                 sum(IF($date_fund_query =1,merchant_user.under_writing_fee,IF($date_fund_query,merchant_user.under_writing_fee,0))) as underwriting_fee_earned, 
                IF($date_fund_query =1,(merchants.origination_fee*merchants.funded)/100,IF($date_fund_query,(merchants.origination_fee*merchants.funded)/100,0)) as origination_fee, 
               sum(IF($date_fund_query =1,merchant_user.up_sell_commission,IF($date_fund_query,merchant_user.up_sell_commission,0))) as up_sell_commission,  

                IF($date_fund_query =1,350,IF($date_fund_query,350,0)) as underwriting_fee_flat,
                IF($date_fund_query =1,'yes',IF($date_fund_query,'yes','no')) as within_funded_date            
            "));
        $data=$data ->where('lender_id',74)->leftjoin('merchant_user', 'merchants.id', '=', 'merchant_user.merchant_id')->groupBy('merchants.id')->orderByDesc('date_funded');
        if ($investors) {
            $data = $data->whereIn('merchant_user.user_id', $investors);
        }
        $data = $data->whereIn('merchant_user.user_id', $company_users);
        if ($label) {
            $data = $data->whereIn('merchants.label', $label);
        }
	    $mgmnt_fee = new PaymentInvestors();
	    if (! empty($investors) && is_array($investors)) {
		    $mgmnt_fee = $mgmnt_fee->whereIn('payment_investors.user_id', $investors);
	    }
	    $mgmnt_fee = $mgmnt_fee->leftjoin('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id');

	    if ($start_date) {
		    $mgmnt_fee = $mgmnt_fee->where('participent_payments.payment_date', '>=', $start_date);
	    }
	    if ($end_date) {
		    $mgmnt_fee = $mgmnt_fee->where('participent_payments.payment_date', '<=', $end_date);
	    }
	    if (! empty($company_users) && is_array($company_users)) {
		    $mgmnt_fee = $mgmnt_fee->whereIn('payment_investors.user_id', $company_users);
	    }
	    $mgmnt_fee = $mgmnt_fee->where('mgmnt_fee','!=',0)->groupBy('payment_investors.merchant_id')->select(DB::raw('sum(mgmnt_fee) as management_fee_earned'), 'payment_investors.merchant_id')->pluck('management_fee_earned', 'payment_investors.merchant_id')->toArray();
	    $table['data'] = $data;
	    $table['mgmnt_fee'] = $mgmnt_fee;
	    $table['ach_fees'] = $ach_fees;

	    return $table;
    }

    public function getAnticipatedPaymentReportDataTable($start_date = null, $end_date = null, $modified_term = null, $merchants_id = null, $column = false)
    {
        if ($column) {
            return [
                ['data' => '', 'title' => '#', 'defaultContent' => '', 'orderable' => false, 'searchable' => false],
                ['data' => 'merchant_name', 'name' => 'merchant_name', 'title' => 'Merchant'],
                ['data' => 'anticipated_amount', 'name' => 'anticipated_amount', 'title' => 'Anticipated Amount', 'searchable' => false, 'orderable' => false],
                ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD', 'searchable' => false, 'orderable' => false],
                ['data' => 'edit_term', 'name' => 'edit_term', 'title' => 'Edit Term', 'searchable' => false, 'orderable' => false],
            ];
        }
        $data = $this->getAnticipatedPaymentReport($start_date, $end_date, $modified_term, $merchants_id);
        $total_anticipated_amount = array_sum(array_column($data, 'anticipated_amount'));
        $total_ctd = array_sum(array_column($data, 'ctd'));

        return \DataTables::of($data)->editColumn('anticipated_amount', function ($data) {
            return FFM::dollar($data->anticipated_amount);
        })->editColumn('ctd', function ($data) {
            return FFM::dollar($data->ctd);
        })->editColumn('edit_term', function ($data) {
            return '<a class="btn btn-xs btn-primary" href="'.route('admin::merchants::payment-terms', ['mid' => $data->merchant_id]).'"><i class="glyphicon glyphicon-edit"></i></a>';
        })->with('Total', 'Total:')->with('total_anticipated_amount', FFM::dollar($total_anticipated_amount))->with('total_ctd', FFM::dollar($total_ctd))->rawColumns(['edit_term'])->make(true);
    }

    public function getAnticipatedPaymentReport($start_date = null, $end_date = null, $modified_term = null, $merchants_id = null)
    {
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $dates = PayCalc::getWorkingDays($start_date, $end_date);
        $data = [];

        $merchant_ids = Merchant::whereNotIn('sub_status_id', $unwanted_sub_status)
        ->where('ach_pull', 1)
        ->whereNull('payment_pause_id')
        ->wherehas('bankAccountDebit');
        if ($merchants_id) {
            $merchant_ids = $merchant_ids->whereIn('merchant_id', $merchants_id);
        }
        $merchant_ids = $merchant_ids->pluck('id')->toArray();
        $payments = TermPaymentDate::where('status', '>=', 0)
        ->whereIn('merchant_id', $merchant_ids)
        ->whereIn('payment_date', $dates);
        $merchant_with_payments = with(clone $payments)->groupBy('merchant_id')->pluck('merchant_id');
        foreach ($merchant_with_payments as $merchant) {
            $anticipated_amount = with(clone $payments)->where('merchant_id', $merchant)->sum('payment_amount');
            $merchant_name = Merchant::where('id', $merchant)->value('name');
            $recieved_payments = ParticipentPayment::where('merchant_id', $merchant)->whereIn('payment_date', $dates)->where('mode_of_payment', ParticipentPayment::ModeAchPayment)->sum('payment');
            $data[] = (object) [
                'merchant_id' => $merchant,
                'merchant_name' => $merchant_name,
                'anticipated_amount' => $anticipated_amount,
                'ctd' => $recieved_payments,
            ];
        }

        return $data;
    }

    public function getAchPayments($date = null, $sub_status_id = null, $manual_payment = null, $merchants_id = null)
    {
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $fee_types = config('custom.ach_fee_types');
        $ach_merchant_settings = (Settings::where('keys', 'ach_merchant')->value('values'));
        $ach_merchant_settings = json_decode($ach_merchant_settings, true);
        $ach_fee_amount = $ach_merchant_settings['ach_fee_amount'] ?? 0;
        if (! is_numeric($ach_fee_amount) || $ach_fee_amount < 0) {
            $ach_fee_amount = 0;
        }
        $data = [];
        $date = $date ?? Carbon::now()->toDateString();
        $year = explode('-', $date)[0];
        $month = explode('-', $date)[1];
        $current_time = Carbon::now();
        $today = $current_time->toDateString();
        if ($date < $today) {
            $disabled = true;
        } elseif ($date > $today) {
            $disabled = false;
        } elseif ($date == $today) {
            $date_time = $current_time;
            $cutoff_time = new Carbon($today.' 19:45:00');
            if ($date_time->lessThan($cutoff_time)) {
                $disabled = false;
            } else {
                $disabled = true;
            }
        }
        $terms = MerchantPaymentTerm::select('merchant_payment_terms.id', 'merchant_payment_terms.merchant_id', 'merchant_payment_terms.payment_amount', 'merchant_payment_terms.advance_type', 'merchant_payment_terms.start_at', 'merchant_payment_terms.end_at')
                                     ->where('start_at', '<=', $date)->where('end_at', '>=', $date);
        if ($merchants_id) {
            $terms = $terms->whereIn('merchant_id', $merchants_id);
        }
        if ($sub_status_id) {
            $terms = $terms->whereHas('merchant', function (Builder $query) use ($sub_status_id, $unwanted_sub_status) {
                $query->has('bankAccountDebit');
                $query->whereNotIn('sub_status_id', $unwanted_sub_status);
                $query->whereIn('sub_status_id', $sub_status_id);
                $query->where('ach_pull', 1);
            });
        } else {
            $terms = $terms->whereHas('merchant', function (Builder $query) use ($unwanted_sub_status) {
                $query->has('bankAccountDebit');
                $query->whereNotIn('sub_status_id', $unwanted_sub_status);
                $query->where('ach_pull', 1);
            });
        }
        $terms = $terms->doesntHave('merchant.paymentPause')->get();
        foreach ($terms as $term) {
            $term_date = $term->payments()->where('payment_date', $date)->where('status', '=', 0)->where('payment_amount', '>', 0)->first();
            if ($term_date) {
                $fees = VelocityFee::where('status', 0)->where('merchant_id', $term->merchant_id)->where('payment_date', $date);
                foreach ($fee_types as $fee_type => $fee_type_title) {
                    if ($fees->count()) {
                        $$fee_type = with(clone $fees)->where('fee_type', $fee_type)->sum('payment_amount');
                        \Log::info($fee_type);
                        \Log::info($$fee_type);
                    } else {
                        $$fee_type = null;
                    }
                }
                $first_month_payment_count = VelocityFee::where('status', '>=', 0)->where('merchant_id', $term->merchant_id)->where('fee_type', 'ach_fee')->whereyear('payment_date', $year)->whereMonth('payment_date', $month)->count();
                if ($first_month_payment_count == 0) {
                    $first_month_payment = $term->merchant->termPayments()->where('status', '>=', 0)->whereyear('payment_date', $year)->whereMonth('payment_date', $month)->orderBy('payment_date')->first();
                    if ($first_month_payment->payment_date == $date) {
                        $ach_fee = sprintf('%.2f', $ach_fee_amount);
                    }
                }
                $payment_amount = $term_date->payment_amount;
                $payment_total = ParticipentPayment::where('merchant_id', $term->merchant_id)->where('is_payment', 1)->sum('payment');
                $merchant_balance = $term->merchant->rtr - $payment_total;

                $processing_ach_payments = TermPaymentDate::where('merchant_id', $term->merchant_id)->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');
                $balance_final = $merchant_balance - $processing_ach_payments;
                if ($balance_final < $payment_amount) {
                    if ($balance_final <= 0) {
                        $payment_amount = 0;
                    } else {
                        $payment_amount = $balance_final;
                    }
                }
                $single = (object) [
                    'merchant_id' => $term->merchant_id,
                    'merchant_name' => $term->merchant->name,
                    'term_id' => $term->id,
                    'payment_amount' => sprintf('%.2f', $payment_amount),
                    'payment_date' => $date,
                    'disabled' => $disabled,
                    'ach_pull' => $term->merchant->ach_pull,
                ];
                foreach ($fee_types as $fee_type => $fee_type_title) {
                    if ($$fee_type) {
                        $single->$fee_type = sprintf('%.2f', $$fee_type);
                    }
                }
                $data[] = $single;
            }
        }

        return $data;
    }

    public function getAchRequests($filter)
    {
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $requests = AchRequest::query();
        if (isset($filter['from_date'])) {
            $requests = $requests->where('payment_date', '>=', $filter['from_date']);
        }
        if (isset($filter['to_date'])) {
            $requests = $requests->where('payment_date', '<=', $filter['to_date']);
        }
        if (isset($filter['status'])) {
            $requests = $requests->whereIn('ach_status', [$filter['status']]);
        }
        if (isset($filter['order_id'])) {
            $requests = $requests->where('order_id', '!=', '');
        }
        if (isset($filter['merchants_id'])) {
            $requests = $requests->whereIn('merchant_id', $filter['merchants_id']);
        }
        $requests = $requests->orderByDesc('payment_date')->get();
        $data = [];
        foreach ($requests as $req) {
            $request_status = 'Declined';
            $response_status = null;
            $payment_status = null;
            if ($req->ach_request_status == 1) {
                $request_status = 'Accepted';
                $response_status = 'Processing';
                if ($req->ach_status == 1) {
                    $response_status = 'Settled';
                } elseif ($req->ach_status == -1) {
                    $response_status = 'Returned';
                }
                if ($req->payment_status == 1) {
                    $payment_status = 'Success';
                } elseif ($req->payment_status == -1) {
                    $payment_status = 'Rcode';
                }
            }
            if ($req->transaction_type == 'credit') {
                $type = 'Payment Credit';
            } else {
                $type = $req->is_fees ? 'Fee Debit' : 'Payment Debit';
            }
            $data[] = (object)
            ['id' => $req->id,
             'order_id' => $req->order_id ?? '',
             'merchant_id' => $req->merchant_id,
             'merchant_name' => $req->merchant->name,
             'payment_amount' => $req->payment_amount,
             'payment_date' => $req->payment_date,
             'transaction_type' => $req->transaction_type,
             'type' => $type,
             'ach_request_status' => $req->ach_request_status,
             'request_status' => $request_status,
             'request_reason' => $req->reason,
             'auth_code' => $req->auth_code,
             'ach_status' => $req->ach_status,
             'response_reason' => $req->status_response,
             'response_status' => $response_status,
             'payment_status' => $payment_status,
             'created_at' => $req->created_at,
             'updated_at' => $req->updated_at,
             'creator_id' => $req->creator_id, ];
        }
        $return['data'] = $data;
        $return['total_amount'] = $requests->sum('payment_amount');
        $return['count'] = $requests->count();

        return $return;
    }

    public function getAchRequestsDataTable($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => false, 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'],
                ['orderable' => true, 'visible' => false, 'searchable' => false, 'title' => 'id', 'data' => 'id', 'name' => 'id'],
                ['orderable' => true, 'visible' => true, 'title' => 'merchant', 'data' => 'merchant_name', 'name' => 'merchant_name'],
                ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'Payment Date', 'data' => 'payment_date', 'name' => 'payment_date'],
                ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'amount', 'data' => 'payment_amount', 'name' => 'payment_amount', 'className' => 'text-right', 'type' => 'num'],
                ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'order id', 'data' => 'order_id', 'name' => 'order_id'],
                ['orderable' => true, 'visible' => true, 'searchable' => false, 'title' => 'type', 'data' => 'type', 'name' => 'is_fees'],
                ['orderable' => true, 'visible' => true, 'searchable' => false, 'title' => 'Settlement status', 'data' => 'ach_status', 'name' => 'ach_status'],
                ['orderable' => true, 'visible' => true, 'searchable' => false, 'title' => 'Request status', 'data' => 'request_status', 'name' => 'ach_request_status'],
                ['orderable' => true, 'visible' => true, 'searchable' => false, 'title' => 'Payment status', 'data' => 'payment_status', 'name' => 'payment_status'],
                ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'Updated At', 'data' => 'updated_at', 'name' => 'updated_at'],
                ['orderable' => false, 'visible' => true, 'searchable' => false, 'title' => 'action', 'data' => 'action', 'name' => 'action'], ];
        }
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $requests = $this->getAchRequests($data);
        $total_amount = $requests['total_amount'];
        $count = $requests['count'];
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($requests['data'])->setTotalRecords($count)->editColumn('merchant_name', function ($row) {
            return "<a href='".route('admin::merchants::payment-terms', ['mid' => $row->merchant_id])."'> ".$row->merchant_name.' </a>';
        })->editColumn('payment_date', function ($row) {
            $created_date = 'Created On '.FFM::datetime($row->created_at).' by '.get_user_name_with_session($row->creator_id);

            return "<a title='$created_date'>".FFM::date($row->payment_date).'</a>';
        })->editColumn('payment_amount', function ($row) {
            return FFM::dollar($row->payment_amount);
        })->editColumn('request_status', function ($row) {
            $authcode = $row->auth_code ? '('.$row->auth_code.')' : '';

            return "<span title='$row->request_reason $authcode '>$row->request_status<span>";
        })->editColumn('ach_status', function ($row) {
            return "<span title='$row->response_reason'>$row->response_status<span>";
        })->editColumn('type', function ($row) {
            return $row->type;
        })->editColumn('updated_at', function ($row) {

            return FFM::datetime($row->updated_at);
        })->addColumn('action', function ($row) {
            $return = '';
            if ($row->order_id) {
                if ($row->response_status == 'Processing') {
                    $return .= '<i ach_id="'.$row->id.'" class="glyphicon glyphicon-send check_status"></i>';
                }
            }

            return $return;
        })->rawColumns(['merchant_name', 'request_status', 'ach_status', 'action', 'payment_date', 'updated_at'])->with('Total', 'Total:')->with('total_amount', FFM::dollar($total_amount))->addIndexColumn()->make(true);
    }

    public function getAchFeesDataTable($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => false, 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false],
                ['orderable' => true, 'visible' => false, 'searchable' => false, 'title' => 'id', 'data' => 'id', 'name' => 'id'],
                ['orderable' => true, 'visible' => true, 'title' => 'Merchant', 'data' => 'merchant', 'name' => 'merchant'],
                ['orderable' => true, 'visible' => true, 'title' => 'date', 'data' => 'payment_date', 'name' => 'payment_date'],
                ['orderable' => true, 'visible' => true, 'title' => 'Amount', 'data' => 'payment_amount', 'name' => 'payment_amount', 'className' => 'text-right'],
                ['orderable' => true, 'visible' => true, 'title' => 'order id', 'data' => 'order_id', 'name' => 'order_id'],
                ['orderable' => true, 'visible' => true, 'title' => 'type', 'data' => 'type', 'name' => 'type'],
                ['orderable' => true, 'visible' => true, 'title' => 'status', 'data' => 'status', 'name' => 'status'],
                ['orderable' => true, 'visible' => true, 'title' => 'Created At', 'data' => 'created_at', 'name' => 'created_at'],
            ];
        }
        $requests = $this->getAchFees($data);
        $total_amount = $requests['total'];
        $count = $requests['count'];
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        return \DataTables::of($requests['data'])->setTotalRecords($count)->editColumn('merchant', function ($row) {
            return "<a href='".route('admin::merchants::payment-terms', ['mid' => $row->merchant_id])."'> ".$row->merchant_name.' </a>'.'<span style="display:none">'.$row->merchant_name.'</span>';
        })->editColumn('payment_date', function ($row) {
            $user = User::where('id', $row->creator_id)->value('name');
            $user = ($user) ? $user : '--';
            $created_date = 'Created On '.FFM::datetime($row->created_at).' by '.$user;

            return "<a title='$created_date'>".FFM::date($row->payment_date).'</a>';
        })->editColumn('type', function ($row) {
            return $row->type;
        })->editColumn('payment_amount', function ($row) {
            return FFM::dollar($row->payment_amount);
        })->editColumn('status', function ($row) {
            return "<span title='$row->status_response'>$row->status<span>";
        })->editColumn('created_at', function ($row) {
            $created_date = 'Created On '.FFM::datetime($row->created_at).' by '.get_user_name_with_session($row->creator_id);

            return "<a title='$created_date'>".FFM::datetime($row->created_at).'</a>';
        })->rawColumns(['merchant', 'status', 'payment_date', 'created_at'])->with('Total', 'Total:')->with('total_amount', FFM::dollar($total_amount))->addIndexColumn()->make(true);
    }

    public function getAchFees($filter)
    {
        $fee_types = config('custom.ach_fee_types');
        $fees = VelocityFee::query();
        $fees = $fees->where('status', '!=', 0);
        if (isset($filter['from_date'])) {
            $fees = $fees->where('payment_date', '>=', $filter['from_date']);
        }
        if (isset($filter['to_date'])) {
            $fees = $fees->where('payment_date', '<=', $filter['to_date']);
        }
        if (isset($filter['status'])) {
            $fees = $fees->whereIn('status', [$filter['status']]);
        }
        if (isset($filter['type']) != null) {
            $fees = $fees->whereIn('fee_type', [$filter['type']]);
        }
        if (isset($filter['merchants_id'])) {
            $fees = $fees->whereIn('merchant_id', $filter['merchants_id']);
        }
        $fees = $fees->latest()->get();
        $data = [];
        foreach ($fees as $fee) {
            $status = 'Processing';
            if ($fee->status == 1) {
                $status = 'Settled';
            } elseif ($fee->status == -1) {
                $status = 'Returned';
            }
            $data[] = (object) [
                'id' => $fee->id, 'order_id' => $fee->order_id ?? '',
                'merchant_id' => $fee->merchant_id, 'merchant_name' => $fee->merchant->name,
                'payment_amount' => $fee->payment_amount,
                'payment_date' => $fee->payment_date,
                'type' => $fee_types[$fee->fee_type],
                'status' => $status,
                'status_response' => $fee->achRequest->status_response ?? '',
                'created_at' => $fee->created_at,
                'updated_at' => $fee->updated_at,
                'creator_id' => $fee->creator_id,
            ];
        }
        $return['count'] = $fees->count();
        $return['total'] = $fees->sum('payment_amount');
        $return['data'] = $data;

        return $return;
    }

    public function syndicatePaymentReportDetails()
    {
        $investor = $this->role->allInvestors()->pluck('id');
        $payments = User::with(['participantPayment' => function ($q) {
            $q->rightjoin('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id');
        }])->get();
        print_r($payments->toArray());
        exit;
    }

    public function profitCarryForwardReport($startDate = null, $endDate = null, $investors = null, $merchants = null, $column = false, $type = null)
    {
        if ($column == true) {
            return [
                ['className' => 'details-control', 'data' => 'id', 'defaultContent' => '', 'title' => '#'],
                ['orderable' => false, 'data' => 'investor_id', 'name' => 'investor_id', 'defaultContent' => '', 'title' => 'Investor'],
                ['data' => 'merchant_id', 'name' => 'merchant_id', 'title' => 'Merchant'],
                ['orderable' => false, 'data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
                ['orderable' => true, 'data' => 'date', 'name' => 'date', 'title' => 'Date'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
                ['className' => 'details-control', 'orderable' => false, 'name' => 'delete',  'data' => 'delete', 'defaultContent' => '', 'title' => ' <label class="chc" title=""> <input type="checkbox" name="delete_multi_investment" id="delete_investment"> <span class="checkmark chek-m mtop"></span> </label>'],

            ];
        } else {
            $data = $this->merchant->searchForProfitCarryForward($startDate, $endDate, $investors, $merchants, $type);
            $sum = $data->get([DB::raw('SUM(carry_forwards.amount) as amount')])->first()->toArray();
            $total_amount = $sum['amount'];
            return \DataTables::of($data)->editColumn('investor_id', function ($data) {
                $name = User::where('id', '=', $data->investor_id)->first();
                return isset($name) ? $name->name : '';
            })->addColumn('merchant_id', function ($data) {
                return isset($data->merchant->name) ? $data->merchant->name : '';
            })->addColumn('amount', function ($data) {
                return FFM::dollar($data->amount);
            })->addColumn('date', function ($data) {
                return FFM::date($data->date);
            })->addColumn('delete', function ($data) {
                return '  <label class="chc" title=""><input type="checkbox" class="delete_bulk_investments" name="delete_bulk_investments[]" data-id="{{$investor->id}}" value="'.$data->id.'" onclick="uncheckMainInvestment();"> <span class="checkmark"></span> </label>';
            })->addColumn('action', function ($data) {
                $return = '';
                $delete = url('admin/carryforward/'.$data->id);
                $return .= '<form method="POST" action="'.$delete.'" accept-charset="UTF-8" style="display:inline">
	                                         '.method_field('DELETE').csrf_field().
                                            '<button type="submit" class="btn btn-xs btn-danger" title="Delete Faq" onclick="return confirm(&quot;Are you sure want to delete ?&quot;)">Delete</button>
											</form>';

                return $return;
            })
              ->with('Total', 'Total:')
              ->with('amount', FFM::dollar($total_amount))
              ->addIndexColumn()
              ->rawColumns(['date', 'action', 'delete'])
              ->make(true);
        }
    }

    public function agentFeeReport($merchants = null, $from_date = null, $to_date = null)
    {
        $details = $this->merchant->getAgentFeeDetails($merchants, $from_date, $to_date);
        $data = $details['data'];
        $total = $details['total'];
        $total_agent_fee = $total->t_agent_fee;
        $total_payment = $total->t_payment;

        return \DataTables::of($data)->editColumn('name', function ($data) {
            return $data->name;
        })->editColumn('date', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);

            return "<a title='$created_date'>".FFM::date($data->payment_date).'</a>';
            //return FFM::date($data->payment_date);
        })->editColumn('agent_fee', function ($data) {
            return FFM::dollar($data->participant_share);
        })->editColumn('name', function ($data) {
            return "<a target='blank' style = 'display:none'> $data->name</a><a target='blank' href='".\URL::to('/admin/merchants/view', $data->merchant_id)."'>$data->name</a>";
        })->editColumn('total_amount', function ($data) {
            return FFM::dollar($data->payment);
        })->rawColumns(['date', 'name'])
        ->with('total_fee', FFM::dollar($total_agent_fee))
        ->with('total_payment', FFM::dollar($total_payment))
        ->make(true);
    }
    public function getInvestorFeeDetails($data_arr=array(),$merchant_id=null){
         $merchant_data = Merchant::where('id',$merchant_id)->first();
         // $data = User::select('management_fee', 'global_syndication', 's_prepaid_status', 'underwriting_fee', 'underwriting_status','name','id')->whereIn('id', $investors)->get()->toArray();
         if($merchant_id!=null){
         $merchant = Merchant::where('id', $merchant_id)->first()->toArray();
         }
         $total_participant_amount = array_sum(array_column($data_arr, 'amount'));
         $total_underwriting_fee = 0;
         $total_upsell_commission = 0;
         $total_syndication_fee = 0;
         $total_mgmnt_fee = 0;
         foreach($data_arr as $arr){
           $total_underwriting_fee = $total_underwriting_fee+$arr['underwriting_fee_per']*$arr['amount']/100;
           $total_upsell_commission = $total_upsell_commission+($arr['upsell_commission_per']*$arr['amount'])/100;
           $invest_rtr = $arr['amount']*$merchant_data->factor_rate;
           $syndication_fee_amount= PayCalc::getsyndicationFee($arr['syndication_on'] == 2 ? $arr['amount'] : $invest_rtr, $arr['syndication_fee_per']);  
           $total_syndication_fee = $total_syndication_fee+$syndication_fee_amount;
           $total_mgmnt_fee = $total_mgmnt_fee+($invest_rtr*$arr['mgmnt_fee_per']/100);
         }
         $inv_ids = array_unique(array_column($data_arr, 'id'));
         $liquidity_arr = UserDetails::whereIn('user_id',$inv_ids)->pluck('liquidity','user_id')->toArray();
        return \DataTables::of($data_arr)
        ->editColumn('name', function ($data) use($liquidity_arr){
             $liduidity= (isset($liquidity_arr[$data['id']])) ? $liquidity_arr[$data['id']] : 0;
            return "<a target='blank' href='".\URL::to('/admin/investors/portfolio', $data['id'])."'>".strtoupper($data['name'])."</a><input type='hidden' value='".$data['id']."' id='participant_id'><input type='hidden' value='".$liduidity."' id='liquidity'>";
             
        })->editColumn('share', function ($data){
            $markup =  $this->inputMarkupBuilderForAmount($data['amount'], '$', 'amount','amount_details_class decimal','',true);
            $markup .=  $this->inputMarkupBuilder($data['share'], '%', 'share','share_details_class fee_percentage_class','readonly');
             
            return $markup;

        })
        // ->editColumn('commission', function ($data){ 
        //     $commission_amount =($data['commission']*$data['amount'])/100; 
        //     $markup =  $this->inputMarkupBuilder($commission_amount, '$', 'commission_amount','commission_amount_details_class',true);
        //     $markup .= $this->inputMarkupBuilder($data['commission'], '%', 'commission','commission_percent_details_class fee_percentage_class');

            
        //     return $markup;
        // })
        ->editColumn('mgmnt_fee', function ($data) use($merchant_data){  
          $syndication_fee_values=FFM::fees_array(); 
          $invest_rtr = $data['amount']*$merchant_data->factor_rate;
          $mgmnt_fee_amount = round($invest_rtr*$data['mgmnt_fee_per']/100,2); 
            $markup = $this->inputMarkupBuilderPercent($data['mgmnt_fee_per'],'%','mgmnt_fee','mgmnt_fee_per fee_percentage_class',$syndication_fee_values,true);
            $markup .= $this->inputMarkupBuilder($mgmnt_fee_amount, '$', 'mgmnt_fee_amount','mgmnt_fee_amount_details_class','readonly');
            return $markup;
        })
        ->editColumn('upsell_commission', function ($data){   
        $syndication_fee_values=FFM::fees_array(0,10,1);   
            $upsell_commission_amount = round(($data['upsell_commission_per']*$data['amount'])/100,2); 
            $markup = $this->inputMarkupBuilderPercent($data['upsell_commission_per'],'%','upsell_commission','upsell_commission_percent_details_class fee_percentage_class fee_percentage_class',$syndication_fee_values,true);
            $markup .= $this->inputMarkupBuilder($upsell_commission_amount, '$', 'upsell_commission_amount','upsell_commission_details_class','readonly');
             
            return $markup;
        })
        ->editColumn('underwriting_fee', function ($data){   
        $syndication_fee_values=FFM::fees_array();           
            $underwriting_fee_amount = round($data['underwriting_fee_per']*$data['amount']/100,2);
            
            $markup = $this->inputMarkupBuilderPercent($data['underwriting_fee_per'], '%', 'underwriting_fee','underwriting_fee_percent_detail_class fee_percentage_class',$syndication_fee_values,true);
            $markup .=  $this->inputMarkupBuilder($underwriting_fee_amount, '$', 'underwriting_fee_amount','underwriting_fee_amount_detail_class','readonly');
            return $markup;
        })
        ->editColumn('syndication_on', function ($data) {
            $fee_arr = array('1'=>'RTR','2'=>'Amount');
             
            $qry = '<select id="syndication_on" name="syndication_on" class="form-control js-placeholder-user_id syndication_on_details_class">';
            foreach($fee_arr as $key => $value) {
            if($key == $data['syndication_on']){
              $selected = 'selected';
            }else{
                $selected = '';
            }
            $qry .='<option '.$selected.' value="'.$key.'">'.$value.'</option>';
            }            
            $qry .='</select>';
            return $qry;
        })
        ->editColumn('syndication_fee', function ($data) use($merchant_data){ 
        $syndication_fee_values=FFM::fees_array();   
            $invest_rtr = $data['amount']*$merchant_data->factor_rate;
            $syndication_fee_amount= PayCalc::getsyndicationFee($data['syndication_on'] == 2 ? $data['amount'] : $invest_rtr, $data['syndication_fee_per']);     
            $markup = $this->inputMarkupBuilderPercent($data['syndication_fee_per'], '%', 'syndication_fee','syndication_fee_percent_details_class fee_percentage_class',$syndication_fee_values,true);
            $markup .=  $this->inputMarkupBuilder($syndication_fee_amount, '$', 'syndication_fee_amount','syndication_fee_details_class','readonly');
            
            return $markup;

        })
        ->addColumn('action', function ($data) {
                            
                $return = '
                <input type="button" value="Delete" class="btn btn-danger" id="delete_participant" class="delete_participant" onclick="deleteParticipant('.$data['id'].');">';

                return $return;
            })->rawColumns(['name','share','amount','mgmnt_fee','syndication_fee','underwriting_fee','commission','syndication_on','upsell_commission','action'])
       
        ->addIndexColumn()
        ->with('total_participant_amount', $this->inputMarkupBuilderForTotal(round($total_participant_amount,2), '$', 'total_participant_amount'))
        ->with('total_mgmnt_fee', $this->inputMarkupBuilderForTotal(round($total_mgmnt_fee,2), '$', 'total_mgmnt_fee'))
        ->with('total_underwriting_fee', $this->inputMarkupBuilderForTotal(round($total_underwriting_fee,2), '$', 'total_underwriting_fee'))
        ->with('total_upsell_commission', $this->inputMarkupBuilderForTotal(round($total_upsell_commission,2), '$', 'total_upsell_commission'))
        ->with('total_syndication_fee', $this->inputMarkupBuilderForTotal(round($total_syndication_fee,2), '$', 'total_syndication_fee'))        
        ->with('total', 'Total')->make(true);
    }
    /**
     * Creats a general markup for input fields.
     * @param int $value value of the input field.
     * @param string $measure what to show on placeholder field, could be either '%' or '$'.
     * @param string $id id of the input field.
     * @param boolean $next_row add trailing break, defautled to false.
     * @return string returns the input markup.
     */
    public function inputMarkupBuilder($value, $measure = '%', $id = '',$class = '',$readonly='', $next_row = false)
    {
        $markup = "
                <div class='form-group value-inv custom-width-100'>
                    <div class ='input-group no-wrap'>
                        <span class='input-group-text'>".$measure."</span>
                        <div class='grow max-ma pr'>
                            <input class='form-control form-inv $class' type='text' value='$value' id='$id' placeholder='".$measure."' $readonly/>

                        </div>
                    </div> 
                    <span style='color:red;' id='error_span".$id."'></span>
                </div>
            ";
        if ($next_row) {
            $markup .= '</br>';
        }
        return $markup;    
    }
    public function inputMarkupBuilderForAmount($value, $measure = '%', $id = '',$class = '',$readonly='', $next_row = false)
    {
        $markup = "
                <div class='form-group custom-width-100'>
                    <div class ='input-group no-wrap'>
                        <span class='input-group-text'>".$measure."</span>
                        <div class='grow max-ma pr'>
                            <input class='form-control $class' type='text' value='$value' id='$id' placeholder='".$measure."' $readonly/>

                        </div>
                    </div> 
                    <span style='color:red;' id='error_span".$id."'></span>
                </div>
            ";
        if ($next_row) {
            $markup .= '</br>';
        }
        return $markup;    
    }
     public function inputMarkupBuilderForTotal($value, $measure = '%', $id = '',$class = '', $next_row = false)
    {
        $markup = "
                <div class='form-group value-inv custom-width-100'>
                    <div class ='input-group no-wrap'>
                        <span class='input-group-text'>".$measure."</span>
                        <div class='grow max-ma pr'>
                            <input class='form-control form-inv $class' type='text' value='$value' id='$id' placeholder='".$measure."' readonly/>

                        </div>
                    </div> 
                    
                </div>
            ";
        if ($next_row) {
            $markup .= '</br>';
        }
        return $markup;    
    }
    public function inputMarkupBuilderPercent($value, $measure = '%', $id = '',$class = '',$fee_values=[], $next_row = false)
    {
        
        $markup = "
                <div class='form-group custom-width-100 drop-group'>
                    <div class ='input-group no-wrap'>
                        <span class='input-group-text'>".$measure."</span>
                        <div class='grow max-ma pr'>
                           <select id='$id' name='' class='form-control $class'>";
                           
                           foreach($fee_values as $fee_val){
                            if($fee_val==$value){
                                $selected = 'selected';
                            }else{
                              $selected = '';  
                            }
                           $markup .= "<option value='".$fee_val."' ".$selected.">".$fee_val."</option>";
                           }
                           $markup .= "</select>
                        </div>
                    </div> 
                     <span style='color:red;' id='error_span".$id."'></span>
                </div>
            ";
        if ($next_row) {
            $markup .= '</br>';
        }
        return $markup;    
    }
}
