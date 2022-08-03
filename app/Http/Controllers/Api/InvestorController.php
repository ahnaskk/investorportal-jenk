<?php

namespace App\Http\Controllers\Api;

use App\Bank;
use App\CarryForward;
use App\Events\UserHasAssignedInvestor;
use App\Exports\Merchant_Graph;
use App\Faq;
use App\Helpers\ActumRequest;
use InvestorHelper;
use App\Helpers\MailBoxHelper;
use App\Helpers\MerchantHelper;
use MerchantUserHelper;
use function App\Helpers\modelQuerySql;
use ParticipantPaymentHelper;
use App\Helpers\PaymentInvestorHelper;
use App\Helpers\PaymentReportHelper;
use App\Helpers\PdfDocumentHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Investor\StatementResource;
use App\Http\Resources\SubStatusResource;
use App\Http\Resources\SuccessResource;
use App\Industries;
use App\InvestorDocuments;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Label;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Mailboxrow;
use App\Merchant;
use App\MerchantUser;
use App\MNotes;
use App\Models\ActumDeclineCode;
use App\Models\InvestorAchRequest;
use App\Models\Views\InvestorAchTransactionView;
use App\Models\Views\MerchantUserView;
use App\Models\Views\Reports\AllTransactionsView;
use App\Models\Views\Reports\InvestmentReportView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Settings;
use App\Statements;
use App\SubStatus;
use App\Template;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use Excel;
use Exception;
use FFM;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use App\Helpers\Report\InvestmentReportHelper;
use PayCalc;
use Illuminate\Support\Facades\Schema;


class InvestorController extends Controller
{
    protected $user = false;
    protected $role = 'investor';

    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
        $this->mBilll = new InvestorTransaction();
        $this->mBilll = $this->mBilll->where('status', InvestorTransaction::StatusCompleted);
        $this->setDefaultAuth();
        $this->middleware(function ($request, $next) {
            $this->setDefaultAuth();

            return $next($request);
        });
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }

    private function setDefaultAuth()
    {
        if (! Auth::user()) {
            return false;
        }
        $this->user = Auth::user();
        $this->role = optional($this->user->roles()->first()->toArray())['name'] ?? '';
        if ($this->role !== 'investor') {
            abort(response()->json('Not found', 404));
        }
    }

    public function postCreate(Request $request)
    {
        if ($request->allocate_user) {
            $request->merge(['creator_id' => $request->allocate_user]);
        }
        $active_status = ($request->active_status == 1) ? 1 : 0;
        $auto_generation = ($request->auto_generation == 1) ? 1 : 0;
        $interest_rate = $request->investor_type != 2 ? 0 : $request->interest_rate;
        $password = $request->input('password');
        $request->merge(['active_status' => $active_status, 'auto_generation' => $auto_generation, 'interest_rate' => $interest_rate, 'source_from' => 'mobile', 'password' => $request->input('password')]);
        $user = User::create($request->only(['name', 'management_fee', 'global_syndication', 'interest_rate', 'email', 'password', 'investor_type', 'creator_id', 'notification_email', 'notification_recurence', 'groupby_recurence', 'active_status', 'company', 's_prepaid_status', 'file_type', 'auto_generation', 'source_from']));
        $userDetails = UserDetails::create(['user_id' => $user->id]);
        $user->assignRole('investor');
        event(new UserHasAssignedInvestor($user, 'investor'));
        if ($userDetails) {
            if ($request->email_notification == 1) {
                $message['title'] = $request->name.' Details';
                $message['subject'] = $request->name.' Details';
                $message['content'] = 'Investor Name : '.$request->name."\n Email :".$request->email." \n  Password :".$request->password;
                $message['to_mail'] = $request->email;
                $message['status'] = 'investor';
                $message['investor_name'] = $request->name;
                $message['username'] = $request->email;
                $message['password'] = $password;
                $message['unqID'] = unqID();
                try {
                    $email_template = Template::where([
                        ['temp_code', '=', 'INVTR'], ['enable', '=', 1],
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

        return $user;
    }

    public function postDashboard(Request $request)
    {
        $settings = Settings::first();
        $defaultPayment = $settings->default_payment;
        $default_rate = $settings->rate / 100;
        $labels = DB::table('label')->where('flag', 1)->pluck('id')->toArray();
        $investmentSum = MerchantUserHelper::getInvestmentSum($this->user, [1, 3], $default_rate);
        $investmentSumLabel = MerchantUserHelper::getInvestmentSum($this->user, [1, 3], $default_rate,null,$labels);
        
        $average_completion = ($investmentSumLabel->total_rtr!=0) ? (($investmentSumLabel->actual_paid_participant_ishare/$investmentSumLabel->total_rtr)*100) :0;
        $invSumForBlendedRoi = MerchantUserHelper::getInvestmentSum($this->user, [1, 3], $default_rate, [1, 5, 16, 2, 13, 12]);
        $blendedRate = $invSumForBlendedRoi->bleded_i_rate;
        $investedAmount = $investmentSum->invested_amount + $investmentSum->pre_paid_t + $investmentSum->commission_total + $investmentSum->under_writing_fee_total+$investmentSum->up_sell_commission;
        $overpayment = PaymentInvestors::select(DB::raw('sum(actual_overpayment) as overpayment'))->where('user_id', $this->user->id)->groupBy('user_id')->first();
        $overpayment = DB::table('carry_forwards')->whereinvestor_id($this->user->id)->where('carry_forwards.type', 1)->sum('amount');
        $total_rtr = $investmentSum->total_rtr + $overpayment - $investmentSum->total_fee;
        $total_rtr = FFM::adjustment($total_rtr, $this->user->id);
        $total_investment_amount = $investmentSum->total_investment;
        $merchant_count = $investmentSum->merchant_count;
        $under_writing_fee = $investmentSum->under_writing_fee_total;
        $ctd = $investmentSum->ctd;
        $user_details = UserDetails::where('user_id', $this->user->id)->first();
        if ($user_details) {
            $liquidity = $user_details->liquidity;
        } else {
            $liquidity = 0;
        }
        $default_date = now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        $userId = $this->user->id;
        $defaultInvestmentSum = MerchantUserHelper::getDefaultInvestmentSum($this->user, [1, 3], [4, 22], $default_rate);
        $settledInvestmentSum = MerchantUserHelper::getSettledInvestmentSum($this->user);
        $settledBalance = $settledInvestmentSum->total_rtr;
        $defaultRtr = $defaultInvestmentSum->total_default_rtr - $defaultInvestmentSum->total_default_fee;
        $defaultCtd = $defaultInvestmentSum->ctd;
        $defaultBalance = $defaultInvestmentSum->total_rtr;
        $defaultAmount = $defaultRtr - $defaultCtd;
        $default_rate_rtr = $investmentSum->default_rate_invest_rtr - $defaultInvestmentSum->default_rate_invest_rtr;
        $defaultInvestments = $defaultInvestmentSum->total_investment;
        $costForCtd = PaymentInvestorHelper::getPrincipalSum($this->user, [4, 22]);
        $currentValue = MerchantUserHelper::getCurrentInvestment($this->user->id, [1, 3], [4, 22]);
        $currentValueArr = MerchantUserHelper::getCurrentInvestmentByStatus($this->user->id, [1, 3], [4, 22]);
        $cInvestedAmount = $currentValue->invested_amount - $costForCtd;
        if ($cInvestedAmount < 0) {
            $cInvestedAmount = 0;
        }
        $costForCtdList = PaymentInvestorHelper::getPrincipalSumByStatus($this->user, [4, 22]);
        $costForCtdArr = $costForCtdList['list'];
        $costForCtdArrOther = $costForCtdList['other'];
        $category_status_arr = [];
        $i = 0;
        $j = 7;
        $industries = DB::table('industries')->pluck('name', 'id');
        if (count($currentValueArr['list']) > 0) {
            foreach ($currentValueArr['list'] as $value) {
                if (isset($costForCtdArr[$value['industry_id']])) {
                    $cur_val = $value['invested_amount'] - $costForCtdArr[$value['industry_id']];
                } else {
                    $cur_val = $value['invested_amount'];
                }
                $category_status_arr[$i]['status_id'] = $value['industry_id'];
                $category_status_arr[$i]['status_name'] = $industries[$value['industry_id']];
                $category_status_arr[$i]['current_invested_amount'] = $cur_val;
                $current_invested_amount_sort_col[] = $cur_val;
                $i++;
            }
            array_multisort($current_invested_amount_sort_col, SORT_DESC, $category_status_arr);
            $other_category_status_arr = array_slice($category_status_arr, 7);
            $other_cur_amount = array_sum(array_column($other_category_status_arr, 'current_invested_amount'));
            $category_status_arr = array_slice($category_status_arr, 0, 7);
        }
        $other_current_invested_amount = 0;
        if (isset($currentValueArr['other']['invested_amount'])) {
            if (($costForCtdArrOther->principal)) {
                $other_current_invested_amount = $costForCtdArrOther->principal;
            }
            $category_status_arr[7]['status_id'] = 7;
            $category_status_arr[7]['status_name'] = 'other';
            $category_status_arr[7]['current_invested_amount'] = ($currentValueArr['other']) ? ($currentValueArr['other']['invested_amount'] - $other_current_invested_amount) : 0;
        }
        $defaultPercentage = 0;
        if ($defaultPayment == 1) {
            $default_invested_amount = $defaultInvestmentSum->total_investment - $defaultCtd - $overpayment;
            $defaultPercentage = ($default_invested_amount > 0) ? ($default_invested_amount / ($total_investment_amount) * 100) : 0;
        } elseif ($defaultPayment == 2) {
            $defaultPercentage = ($total_rtr > 0) ? (($defaultAmount - $overpayment) / ($total_investment_amount) * 100) : 0;
        }
        $total_rtr = $total_rtr - $defaultBalance-$settledBalance;
        $total_requests = 0;
        $portfolioValue = (($total_rtr + $liquidity) - $ctd);
        $subStatuses = SubStatus::pluck('name', 'id');
        $principalInvestment = InvestorTransaction::getTransactionSum($this->user->id, [1, 12], 1);
        $total_credit = InvestorTransaction::getTransactionSum($this->user->id, 0, 0, 2);
        $velocity_dist = InvestorTransaction::getTransactionSum($this->user->id, 7);
        $investor_dist = InvestorTransaction::getTransactionSum($this->user->id, 6);
        $all_debits = InvestorTransaction::getTransactionSum($this->user->id, 0, 1, 1);
        $debit_interest = InvestorTransaction::getTransactionSum($this->user->id, 3, 1);
        $bill_transaction = InvestorTransaction::getTransactionSum($this->user->id, 10);
        $bill_transaction = -$bill_transaction;
        $inv_result = InvestorTransaction::where(function ($q) {
            $q->where('transaction_type', 2)->orWhere('transaction_category', 12);
        })->where('investor_id', $this->user->id);
        $inv_result = $inv_result->get();
        $today = date('Y-m-d');
        $credit_value = 0;
        if (count($inv_result) > 0) {
            foreach ($inv_result as $key => $value) {
                $this_date = $value->date;
                $number_of_dates_obj = date_diff(new \DateTime($this_date), new \DateTime($today));
                $this_number_of_dates = $number_of_dates_obj->format('%R%a');
                $proportion = $this_number_of_dates ? ($this_number_of_dates + 1) / 365 : 0;
                $total_credit = $total_credit + $value->amount;
                $credit_value = $credit_value + $value->amount * $proportion;
            }
        }
        $interest_accrued = $this->user->interest_rate ? $credit_value * $this->user->interest_rate / 100 : 0;
        $tot_profit = PaymentInvestorHelper::getTotalProfitSum($this->user);
        $default_amnt = $defaultInvestments - $defaultCtd;
        $carry_profit = CarryForward::where('investor_id', $this->user->id)->where('type', 2)->sum('amount');
        $profit = $tot_profit + $carry_profit - $bill_transaction - $default_amnt;
        $current_portfolio = ($portfolioValue > 0) ? $portfolioValue * 0.5 : 0;
        $portfolio_earnings = $portfolioValue - $all_debits;
        if ($principalInvestment > 0) {
            $irr = ($portfolio_earnings - $principalInvestment) / $principalInvestment * 100;
        } else {
            $irr = 0;
        }
        $latest_payments = PaymentInvestorHelper::merchantsLatestPayments($this->user->id);
        $latest_payments = collect($latest_payments)->map(function ($record) {
            return ['merchant_name' => (Auth::user()->display_value == 'mid') ? $record['id'] : $record['name'], 'payment_date' => FFM::date($record['payment_date']), 'amount' => FFM::dollar($record['actual_participant_share'] ?? 0), 'type' => ($record['code'] == null) ? $record['payment_type'] : $record['code']];
        });
        $last_generated_stmnt_arr = [];
        $last_generated_statements = Statements::where('user_id', $this->user->id)->orderByDesc('to_date')->where('investor_portal', 0)->first();
        if ($last_generated_statements) {
            $gen_from_date = ($last_generated_statements->from_date != '0000-00-00') ? $last_generated_statements->from_date : '00-00-0000';
            $gen_to_date = ($last_generated_statements->to_date != '0000-00-00') ? $last_generated_statements->to_date : '00-00-0000';
            $last_generated_stmnt_arr = ['generated_on' => FFM::datetime($last_generated_statements->created_at), 'period' => FFM::date(($gen_from_date)).' to '.FFM::date(($gen_to_date))];
        }
        $pending_ach = InvestorAchRequest::whereinvestor_id($this->user->id);
        $pending_ach->where('ach_status', InvestorAchRequest::AchStatusAccepted);
        $pending_ach->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing);
        $pending_ach = $pending_ach->sum('amount');
        $default_credit_bank = Bank::whereinvestor_id($this->user->id);
        $default_credit_bank->wheredefault_credit(1);
        $default_credit_bank = $default_credit_bank->first();
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        if ($OverpaymentAccount) {
            if ($OverpaymentAccount->id != $this->user->id) {
                $profit += $overpayment;
            }
        }
        [$dailyAverage, $total_credit] = InvestorTransaction::getDailyAverage($this->user->id, 1);
        $principal_investment_arr = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->whereIn('transaction_category', [1, 12])->where('investor_id', $this->user->id)->select(DB::raw('MAX((DATEDIFF(NOW(),date)+1)) as days'), DB::raw('sum(amount*(DATEDIFF(NOW(),date)+1)) as tot_amount'))->first();
        $average_principal_investment = '';
        if ($principal_investment_arr) {
            $average_principal_investment = ($principal_investment_arr->days != 0) ? $principal_investment_arr->tot_amount / $principal_investment_arr->days : $principal_investment_arr->tot_amount;
        }
        //$roi = ($average_principal_investment != 0) ? $profit / $average_principal_investment * 100 : 0;
        $debit_transactions = -$all_debits;
        $initial_investment = InvestorTransaction::getTransactionSum($this->user->id, [1], 1);
        $roi = ($initial_investment!=0) ? ((($portfolioValue+$debit_transactions-$initial_investment)/$initial_investment)*100) : 0;
        //((ANTICIPATED RTR + LIQUIDITY + DEBIT TRANSACTIONS - TRANSFER TO VELOCITY)/TRANSFER TO VELOCITY)*100

        $pending_debit_ach_request = InvestorAchRequest::whereinvestor_id($this->user->id)->whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['debit', 'same_day_debit'])->sum('amount');
        $pending_credit_ach_request = InvestorAchRequest::whereinvestor_id($this->user->id)->whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['credit', 'same_day_credit'])->sum('amount');
        $anticipated_rtr = $total_rtr - $ctd - $default_rate_rtr;
        if ($anticipated_rtr < 0) {
            $anticipated_rtr = 0;
        }

        return new SuccessResource(['liquidity' => FFM::dollar($liquidity), 'invested_amount' => FFM::dollar($investedAmount), 'c_invested_amount' => FFM::dollar($cInvestedAmount), 'ctd' => FFM::dollar($ctd), 'blended_rate' => FFM::percent($blendedRate), 'default_percentage' => FFM::percent($defaultPercentage), 'merchant_count' => $merchant_count, 'total_rtr' => FFM::dollar($total_rtr), 'anticipated_rtr' => FFM::dollar($anticipated_rtr), 'daily_average' => FFM::dollar($dailyAverage), 'pending_requests' => FFM::dollar($total_requests), 'portfolio_value' => FFM::dollar($portfolioValue), 'principal_investment' => FFM::dollar($principalInvestment), 'investor_type' => $this->user->investor_type, 'velocity_distribution' => FFM::dollar($velocity_dist), 'investor_distribution' => FFM::dollar($investor_dist), 'debit_interest' => FFM::dollar($debit_interest), 'irr' => FFM::percent($irr), 'total_credit' => FFM::dollar($total_credit), 'current_portfolio' => FFM::dollar($current_portfolio), 'sub_statuses' => $subStatuses, 'overpayment' => FFM::dollar($overpayment), 'profit' => FFM::dollar($profit), 'roi' => FFM::percent($roi), 'latest_payments' => $latest_payments, 'investor_name' => $this->user->name, 'pending_ach' => FFM::dollar($pending_ach), 'pending_ach_credit' => FFM::dollar($pending_credit_ach_request), 'pending_ach_debit' => FFM::dollar($pending_debit_ach_request), 'paid_to_date' => FFM::dollar(-$all_debits), 'default_credit_bank' => $default_credit_bank, 'last_generated_statements' => $last_generated_stmnt_arr, 'category_status_arr' => $category_status_arr,'average_completion'=>FFM::percent($average_completion)]);
    }

    public function postPaymentReport(Request $request)
    {
        $merchant = $request->input('merchant_id');
        $startDate = $request->input('sDate');
        $endDate = $request->input('eDate');
        $rCode = $request->input('rCode');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        $sort = $request->input('sort_order');
        $sort_by = $request->input('sort_by');
        $keyword = $request->input('keyword');
        $sort_order = null;
        if ($sort != null) {
            $sort_order = ($sort == 1) ? 'ASC' : 'DESC';
        }
        if (! empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
        }
        if (! empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
        }
        [$total, $reportData] = PaymentReportHelper::investor($startDate, $endDate, $rCode, $this->user->id, $merchant, $sort_by, $sort_order, $keyword);
        $totals = collect($total)->map(function ($value, $fieldName) use ($total) {
            return ($fieldName !== 'count') ? \FFM::dollar($value) : $value;
        });
        $totals['total_net_participant_payment'] = $total['total_participant_share'] - $total['total_mgmnt_fee'];
        $totals['total_net_participant_payment'] = \FFM::dollar($totals['total_net_participant_payment']);
        $simpleReportFields = ['name', 'date_funded', 'id', 'last_payment_date', 'code'];
        $data = collect($reportData)->map(function ($record) use ($simpleReportFields) {
            $record['net_participant_payment'] = $record['participant_share'] - $record['mgmnt_fee'];

            return collect($record)->map(function ($value, $field) use ($simpleReportFields) {
                return ! in_array($field, $simpleReportFields) ? \FFM::dollar($value) : ($field == 'date_funded' || $field == 'last_payment_date' ? \FFM::date($value) : $value);
            })->toArray();
        });
        $data = array_slice($data->toArray(), $offset, $limit);
        $total_page = count($reportData);
        $from = ($total_page != 0) ? $offset + 1 : 0;
        $to = ($total_page != 0) ? ($offset + count($data)) : 0;
        $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
        $no_of_pages = (int) ($total_page / $limit);
        if (($total_page % $limit) > 0) {
            $no_of_pages = $no_of_pages + 1;
        }
        $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];

        return new SuccessResource(['data' => ['total' => $totals, 'data' => $data, 'pagination' => $pagination, 'download-url' => url('api/investor/download/payment-report?token='.$this->user->getDownloadToken())]]);
    }

    public function postPaymentReportDetails(Request $request)
    {
        $merchant_id = $request->input('merchant_id');
        $startDate = $request->input('sDate');
        $endDate = $request->input('eDate');
        if (! empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
        }
        if (! empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
        }
        $paymentDetails = PaymentReportHelper::investorDetail($this->user->id, $merchant_id, $startDate, $endDate);
        $payments = collect($paymentDetails)->map(function ($record) {
            return ['participant' => $this->user->name, 'date' => \FFM::date($record['payment_date']), 'debited' => \FFM::dollar($record['payment'] ?? 0), 'participant_share' => \FFM::dollar($record['participant_share'] ?? 0), 'management_fee' => \FFM::dollar($record['mgmnt_fee'] ?? 0), 'net_amount' => \FFM::dollar($record['participant_share'] - $record['mgmnt_fee']), 'rcode' => $record['code']];
        });

        return new SuccessResource(['data' => $payments]);
    }

    public function collectionNotes(Request $request)
    {
        $data = [];
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        $notes = DB::table('m_notes')->select(DB::raw('DATE(m_notes.created_at) as date'))->join('merchant_user','merchant_user.merchant_id','m_notes.merchant_id')->join('merchants','merchants.id','merchant_user.merchant_id')->where('merchant_user.user_id',$this->user->id)->orderBy('m_notes.created_at','DESC')->groupBy('date');
        $Count = clone $notes;
        $notes = $notes->offset($offset);
        $notes = $notes->limit($limit);
        $notes = $notes->get();
        $i=0;
        foreach($notes as $note){
            $notes_data = $this->getNotes($note->date);
            if(!empty($notes_data)){
                $data[$i]['date'] = date('m-d-Y',strtotime($note->date));
                $data[$i]['data'] = $notes_data;
            }
            $i = $i + 1;
        }
        $total_page = count($Count->get()->toArray());
        $from = ($total_page != 0) ? $offset + 1 : 0;
        $to = ($total_page != 0) ? ($offset + count($notes)) : 0;
        $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
        $no_of_pages = (int) ($total_page / $limit);
        if (($total_page % $limit) > 0) {
            $no_of_pages = $no_of_pages + 1;
        }
        $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];
        return new SuccessResource(['total_notes_count' => $total_page, 'notes' => $data , 'pagination' => $pagination ]);
    }
    public function getNotes($date){

        return DB::table('m_notes')->select('merchants.name','m_notes.note')->join('merchant_user','merchant_user.merchant_id','m_notes.merchant_id')->join('merchants','merchants.id','merchant_user.merchant_id')->where('merchant_user.user_id',$this->user->id)->whereDate('m_notes.created_at',$date)->get()->toArray();
    
    }
    public function postInvestorChart(Request $request)
    {
        $xAxisLabels = [];
        for ($i = 4; $i > -1; $i--) {
            $xAxisLabels['month'.($i > 0 ? $i : '')] = Carbon::now()->subMonths($i)->format('M');
        }
        $startDate = Carbon::now()->subMonths(4)->format('Y-m-01');
        $endDate = Carbon::now()->format('Y-m-t');
        $merchantFunds = MerchantUserHelper::getFundsByDate($this->user->id, $startDate, $endDate)->toArray();
        $investorCtds = ParticipantPaymentHelper::getCtdByDate($this->user->id, $startDate, $endDate)->toArray();
        $chartData = [];
        for ($i = 4; $i > -1; $i--) {
            $month = Carbon::now()->subMonths($i)->format('m');
            $year = Carbon::now()->subMonths($i)->format('Y');
            $merchantFund = collect($merchantFunds)->filter(function ($record) use ($month, $year) {
                return (int) $record['month'] == (int) $month && $record['year'] == $year;
            })->first();
            $investorCtd = collect($investorCtds)->filter(function ($record) use ($month, $year) {
                return (int) $record['month'] == (int) $month && $record['year'] == $year;
            })->first();
            $chartData[] = ['month' => $month, 'year' => $year, 'funded' => optional($merchantFund)['funded'] ?? 0, 'rtr_month' => optional($merchantFund)['rtr_month'] ?? 0, 'ctd_month' => optional($investorCtd)['ctd_month'] ?? 0];
        }

        return new SuccessResource(['chart_data' => $chartData, 'x_data' => $xAxisLabels]);
    }

    public function postInvestorMerchantList(Request $request)
    {
        $sub_status = $request->input('sub_status', []);
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId_admin = $request->user()->id;
        $userId = $this->user->id;
        $request_from = $request->input('request_from') ?? null;
        $data = $request->all();
        $startDate = $data['sDate'] ?? null;
        $endDate = $data['eDate'] ?? null;
        if (! empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
        }
        if (! empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
        }
        $offset = $request->input('offset') ?? 0;
        $keyword = $request->input('keyword');
        $limit = $request->input('limit', 30);
        $isExport = $request->input('is_export', false);
        $sort_by = $request->input('sort_by');
        $sort = $request->input('sort_order');
        $sort_order = null;
        if ($sort != null) {
            $sort_order = ($sort == 1) ? 'ASC' : 'DESC';
        }
        $subInvestors = [];
        if (empty($permission)) {
            $investors = User::investors();
            $myInvestors = $investors->where('creator_id', $this->user->id);
            foreach ($myInvestors as $myInvestor) {
                $subInvestors[] = $myInvestor->id;
            }
        }
        [$query, $sumQuery] = MerchantUserHelper::getInvestorMerchants($this->user->id, $subInvestors, $sub_status, [1, 3], 1, $keyword, $request_from, $startDate, $endDate);
        if ($sort_by != null && $sort_order != null) {
            if ($sort_by == 'merchant') {
                $query->orderBy('merchants.name', $sort_order);
            }
            if ($sort_by == 'date_funded') {
                $query->orderBy('merchants.date_funded', $sort_order);
            }
            if ($sort_by == 'funded') {
                $query->orderBy('merchant_user.amount', $sort_order);
            }
            if ($sort_by == 'commission') {
                $query->orderBy('merchant_user.commission_per', $sort_order);
            }
            if ($sort_by == 'under_writing_fee') {
                $query->orderBy('merchant_user.under_writing_fee', $sort_order);
            }
            if ($sort_by == 'syndication_fee') {
                $query->orderBy('merchant_user.pre_paid', $sort_order);
            }
            if ($sort_by == 'rtr') {
                $query->orderBy(DB::raw('merchant_user.invest_rtr- merchant_user.invest_rtr*merchant_user.mgmnt_fee/100'), $sort_order);
            }
            if ($sort_by == 'rate') {
                $query->orderBy('merchants.factor_rate', $sort_order);
            }
            if ($sort_by == 'ctd') {
                $query->orderBy(DB::raw('actual_paid_participant_ishare - paid_mgmnt_fee'), $sort_order);
            }
            if ($sort_by == 'annualized_rate') {
                $query->orderBy(DB::raw('
				(((
					(
						(
							( invest_rtr * ( 100 - merchant_user.mgmnt_fee ) / 100 )
                            -
                            (  merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)

                        )
                    )   * IF(advance_type="weekly_ach",52, 255) / merchants.pmnts
                )/ (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) )*100)'), $sort_order);
            }
            if ($sort_by == 'complete') {
                $query->orderBy('merchants.complete_percentage', $sort_order);
            }
            if ($sort_by == 'status') {
                $query->orderBy('sub_statuses.name', $sort_order);
            }
            if ($sort_by == 'last_payment_date') {
                $query->orderBy('merchants.last_payment_date', $sort_order);
            }
        }
        if ($sort_by == null) {
            $query->orderByDesc('merchants.date_funded');
        }
        $query->groupBy('merchant_user.merchant_id');
        $total = $sumQuery->get()->toArray();
        $total_page = $total[0]['count'];
        $from = ($total_page != 0) ? $offset + 1 : 0;
        $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
        $no_of_pages = (int) ($total_page / $limit);
        if (($total_page % $limit) > 0) {
            $no_of_pages = $no_of_pages + 1;
        }
        if ($isExport == 'yes') {
            $results = $query->get();
        } else {
            $results = $query->limit($limit)->offset($offset)->get();
        }
        $to = ($total_page != 0) ? $offset + count($results->toArray()) : 0;
        $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];
        $total = collect($total)->map(function ($record) {
            return ['funded_total' => \FFM::dollar($record['amount'] ?? 0), 'ctd_total' => \FFM::dollar($record['actual_paid_participant_ishare'] ?? 0), 'rtr_total' => \FFM::dollar(($record['invest_rtr'] ?? 0) - ($record['mgmnt_fee_amount'] ?? 0))];
        })->first();
        $results = collect($results)->map(function ($result, $index) use ($keyword) {
            [$total_mgmnt_paid, $paid_to_participant, $ctd_sum, $participant_share] = ParticipantPaymentHelper::getMerchantExtra($result->id, $this->user->id);
            $balance = $result->invest_rtr - $result->mag_fee - $paid_to_participant;
            $bal_rtr = $result->invest_rtr - $participant_share;
            $actual_payment_left = 0;
            if ($result->invest_rtr > 0) {
                $actual_payment_left = $bal_rtr / (($result->invest_rtr / $result->rtr) * ($result->rtr / $result->pmnts));
            }
            $payment_unique_date = ParticipentPayment::where('payment_type', 1)->where('payment', '!=', 0)->where('participent_payments.merchant_id', $result->id)->join('payment_investors', 'participent_payments.id', 'payment_investors.participent_payment_id')->groupBy('payment_date')->count();
            $last_payment_date = ParticipantPaymentHelper::getMerchantLastPaymentDateToInvestor($result->id, $this->user->id);
            $payment_left = $result->pmnts - ($payment_unique_date);
            $paid_payments = $result->pmnts - $payment_left;
            $expected_amount = $result->payment_amount * $paid_payments;
            $amount_difference = abs($ctd_sum - $expected_amount);
            if ($result->complete_percentage > 99) {
                $payment_left = 'None';
            }
            $no_of_payments = ($result->advance_type == 'weekly_ach') ? 52 : 255;
            $tot_profit = $result->invest_rtr - $result->mag_fee - ($result->amount + $result->pre_paid + $result->commission_amount + $result->under_writing_fee+$result->up_sell_commission);
            $tot_investment = $result->amount + $result->pre_paid + $result->commission_amount + $result->under_writing_fee+$result->up_sell_commission;
            if($result->pmnts!=0 && $tot_investment!=0){
            $annualised_rate = ($tot_profit * $no_of_payments / $result->pmnts) / $tot_investment * 100;
            }else{
             $annualised_rate = 0;
            }
            $payment_amount = ($result->amount / $result->funded) * $result->payment_amount;
            $array['date_funded'] = \FFM::date($result->date_funded);
            $array['amount']['value'] = \FFM::dollar($result->amount);
            $array['amount']['value_percent'] = \FFM::percent(($result->amount / $result->funded) * 100);
            $array['invest_rtr'] = \FFM::dollar($result->invest_rtr- $result->mag_fee);
            $array['factor_rate'] = round($result->factor_rate, 2);
            $array['payment_amount'] = FFM::dollar($payment_amount);
            $array['ctd'] = \FFM::dollar($result->ctd);          
            $array['complete_percentage'] = ($result->invest_rtr!=0) ? \FFM::percent(($result->actual_paid_participant_ishare/$result->invest_rtr)*100) : \FFM::percent(0);
            $array['balance'] = ($balance < 0) ? \FFM::dollar(0) : \FFM::dollar($balance);
            $array['sub_statuses_name'] = $result->sub_status_name;
            $array['last_payment_date'] = \FFM::date($last_payment_date);
            $array['payment_type'] = ($result->advance_type == 'daily_ach') ? 'Daily' : 'Weekly';
            $array['id'] = $result->id;
            $array['name'] = (Auth::user()->display_value == 'mid') ? $result->id : $result->name;
            $array['participant_share'] = \FFM::percent(($result->amount / $result->funded) * 100);
            $array['gross_payment_amount'] = \FFM::dollar(($result->invest_rtr / $result->pmnts));
            $array['annualized_rate'] = \FFM::percent($result->annualized_rate);
            $array['payment_left'] = ($payment_left != 'None') ? round($payment_left, 2) : $payment_left;
            $array['actual_payment_left'] = ($actual_payment_left <= 0) ? 'None' : round($actual_payment_left);
            $array['syndication_fee']['value'] = \FFM::dollar($result->syndication_fee);
            $array['syndication_fee']['value_percent'] = \FFM::percent($result->syndication_fee_percentage);
            $array['under_writing_fee']['value'] = \FFM::dollar($result->under_writing_fee);
            $array['under_writing_fee']['value_percent'] = \FFM::percent($result->under_writing_fee_per);
            $array['commission']['value'] = \FFM::dollar($result->commission_amount+$result->up_sell_commission);
            $array['commission']['value_percent'] = \FFM::percent($result->commission_per+$result->up_sell_commission_per);
            $array['total_no_of_payments'] = $result->pmnts;

            return $array;
        })->toArray();

        return new SuccessResource(['data' => $results, 'download-url' => url('api/investor/download/merchant-list?token='.$this->user->getDownloadToken()), 'total' => $total, 'pagination' => $pagination]);
    }

    public function postDownloadInvestorMerchantList(Request $request)
    {
        $result = $this->postInvestorMerchantList($request);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename='.time().'-'.Str::slug('data').'-graph-data.csv');
        $fp = fopen('php://output', 'w');
        $data = optional($result)['data'] ?? [];
        foreach ($data as $item) {
            $item = collect($item)->map(function ($value) {
                return is_array($value) ? json_encode($value) : $value;
            })->toArray();
            fputcsv($fp, $item);
        }
        exit;

        return Excel::download(optional($result)['data'], 'download.csv');
    }

    public function postSubStatusList(Request $request)
    {
        return SubStatusResource::collection(SubStatus::get());
    }

    public function postMarketplace(Request $request)
    {
        $investor = $this->user;
        $investorId = $investor->id;
        $invested_merchants = MerchantUserHelper::getInvestedMarketplaceMerchants($investor->id);
        $invested_merchants_arr = array_unique(array_column($invested_merchants->toArray(), 'merchant_id'));
        $marketplaceData = [];
        $input = $request->all();
        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;
        $filter = $request->filter ?? 0;
        $industry = isset($input['filter']['Industry']['id']) ? $input['filter']['Industry']['id'] : null;
        $credit_score = isset($input['filter']['Credit Score']['id']) ? $input['filter']['Credit Score']['id'] : null;
        $factor_rate1 = null;
        if (isset($input['filter']['Factor Rate']['id'])) {
            $factor_rate_arr = explode('-', $input['filter']['Factor Rate']['id']);
            $factor_rate1 = $factor_rate_arr[0];
            $factor_rate2 = isset($factor_rate_arr[1]) ? $factor_rate_arr[1] : 0;
        }
        if (isset($input['filter']['Credit Score']['id'])) {
            $credit_score_arr = explode('-', $input['filter']['Credit Score']['id']);
            $credit_score1 = $credit_score_arr[0];
            $credit_score2 = $credit_score_arr[1];
        }
        if (isset($input['filter']['Monthly Revenue']['id'])) {
            $revenue = $input['filter']['Monthly Revenue']['id'];
            $monthly_revenue_arr = explode('-', $revenue);
            $monthly_revenue1 = $monthly_revenue_arr[0];
            $monthly_revenue2 = isset($monthly_revenue_arr[1]) ? $monthly_revenue_arr[1] : 0;
        }
        if ($this->user->investor_type == 5 or 1 == 1) {
            $funds = Merchant::leftJoin('merchants_details','merchants_details.merchant_id','merchants.id')->where('marketplace_status', 1)->select('funded', 'payment_amount', 'pmnts', 'factor_rate', 'commission','merchants.up_sell_commission', 'merchants.m_mgmnt_fee', 'm_syndication_fee', 'm_s_prepaid_status', 'rtr', 'max_participant_fund', 'merchants.name as business_name', 'merchants.id', 'underwriting_fee', 'complete_percentage', 'marketplace_permission', 'credit_score', 'merchants_details.monthly_revenue', 'industries.name as industry_name', 'advance_type', 'experian_intelliscore', 'experian_financial_score', 'merchant_user.user_id')->where('active_status', 1)->where('merchants.sub_status_id', '=', 1)->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->Join('industries', 'industries.id', 'merchants.industry_id')->groupBy('merchants.id')->with('FundingRequests');
            if ($filter == 2) {
                $funds = $funds->whereRaw('max_participant_fund=funded');
            } elseif ($filter == 1) {
                $funds = $funds->whereRaw('max_participant_fund<funded');
            }
            if (isset($input['filter']['Credit Score']['id'])) {
                $funds = $funds->where('credit_score', '>=', $credit_score1);
                $funds = $funds->where('credit_score', '<=', $credit_score2);
            }
            if (isset($input['filter']['Factor Rate']['id'])) {
                $funds = $funds->where('factor_rate', '>=', $factor_rate1);
                if ($factor_rate2 != 0) {
                    $funds = $funds->where('factor_rate', '<=', $factor_rate2);
                }
            }
            if (isset($input['filter']['Monthly Revenue']['id'])) {
                $funds = $funds->where('monthly_revenue', '>=', $monthly_revenue1);
                if ($monthly_revenue2 != 0) {
                    $funds = $funds->where('monthly_revenue', '<=', $monthly_revenue2);
                }
            }
            if ($industry != null) {
                $funds = $funds->where('industry_id', $industry);
            }
            if (count($invested_merchants_arr) > 0) {
                $funds = $funds->whereNotIn('merchants.id', $invested_merchants_arr);
            }
            $data = $funds->get();

            $data = collect($data)->map(function ($value, $index) use ($investorId) {
                $invested_amount = round($value->marketplaceInvestors()->sum('amount'));
                $fees = User::select('management_fee', 'global_syndication', 's_prepaid_status', 'underwriting_fee', 'underwriting_status')->where('id', $investorId)->first()->toArray();
                if (($value->max_participant_fund > $invested_amount)) {
                    $value->m_syndication_fee = ($fees['global_syndication'] !== null) ? $fees['global_syndication'] : $value->m_syndication_fee;
                    $value->m_mgmnt_fee = ($fees['management_fee'] !== null) ? $fees['management_fee'] : $value->m_mgmnt_fee;
                    $maximum_amount = $value->max_participant_fund - $value->marketplaceInvestors()->sum('amount');
                    $gross_value = $maximum_amount;
                    $prepaid_fees = $value->marketplaceInvestors()->sum('commission_amount')+$value->marketplaceInvestors()->sum('up_sell_commission') + $value->marketplaceInvestors()->sum('pre_paid') + $value->marketplaceInvestors()->sum('under_writing_fee');
                    $max_per = ($value->funded > 0) ? ($maximum_amount / $value->funded * 100) : 0;
                    $syndication_fee_per = 0;
                    $syndication_fee_on_amount = $fees['s_prepaid_status'] == 1 ? false : true;
                    if ($fees['global_syndication'] === null) {
                        $syndication_fee_on_amount = $value->m_syndication_fee == 1 ? false : true;
                    }
                    if ($fees['global_syndication'] === null) {
                        if ($value->m_s_prepaid_status == 2) {
                            $syndication_fee_per = $value->m_syndication_fee;
                        } elseif ($value->m_s_prepaid_status == 1) {
                            $syndication_fee_per = $value->m_syndication_fee * $value->factor_rate;
                        }
                    } else {
                        if ($fees['s_prepaid_status'] == 2) {
                            $syndication_fee_per = $value->m_syndication_fee;
                        } elseif ($fees['s_prepaid_status'] == 1) {
                            $syndication_fee_per = $value->m_syndication_fee * $value->factor_rate;
                        }
                    }
                    $prepaid_fee_per = $value->commission + $value->underwriting_fee + $syndication_fee_per+$value->up_sell_commission;
                    $documents = PdfDocumentHelper::getMerchantDocuments($value->id);                    
                    return $marketplaceData[] = [
                        'id' => $value->id,
                        'syndication_fee_on_amount' => $syndication_fee_on_amount,
                        'name' => (Auth::user()->display_value == 'mid') ? $value->id : $value->business_name,
                        'display_value' => Auth::user()->display_value,
                        'fundingCompleted' => \FFM::percent(100 - $max_per),
                        'available' => \FFM::percent($max_per),
                        'editPermission' => $value->marketplace_permission ? true : false,
                        'maximumAmount' => \FFM::dollar($maximum_amount),
                        'yourAmount' => round($maximum_amount, 2),
                        'netValuePercent' => round($prepaid_fee_per, 2),
                        'maximumParticipationAvailable' => \FFM::dollar($maximum_amount),
                        'totalFundedAmount' => \FFM::dollar($value->funded),
                        'rtr' => \FFM::dollar($value->rtr),
                        'prepaid' => round($value->m_syndication_fee, 2),
                        'dailyPayment' => \FFM::dollar($value->payment_amount),
                        'numberOfPayments' => round($value->pmnts),
                        'factorRate' => \FFM::percent($value->factor_rate),
                        'commissionPayable' => round($value->commission+$value->up_sell_commission, 2),
                        'upsell_commissionPayable' => round($value->up_sell_commission, 2),
                        'managementFee' => round($value->m_mgmnt_fee, 2),
                        'underwritingFee' => round($value->underwriting_fee, 2),
                        'payment_type' => ($value->advance_type == 'daily_ach') ? 'Daily' : 'Weekly',
                        'credit_score' => $value->credit_score,
                        'monthly_revenue' => $value->monthly_revenue,
                        'industry_name' => $value->industry_name,
                        'experian_intelliscore' => \FFM::percent($value->experian_intelliscore),
                        'experian_financial_score' => \FFM::percent($value->experian_financial_score),
                        'merchant_user_exist_count' => $value->merchant_user_exist_count,
                        'doc_count'=>count($documents->toArray()),
                    ];
                }
            })->filter(function ($marketplaceData) {
                return $marketplaceData;
            })->slice($offset, $limit)->toArray();

            return new SuccessResource(['data' => $data]);
        }

        return new ErrorResource(['message' => 'Not a Participant']);
    }

    public function postMarketplaceFilters(Request $request)
    {
        if ($this->user->investor_type == 5) {
            $filters = [];
            $creditScoreArr = $monthlyRevenueArr = $factorRateArr = $industryArr = [];
            $creditScoreFilters = [['id' => '0-500', 'name' => 'Below 500'], ['id' => '500-550', 'name' => '500-550'], ['id' => '551-600', 'name' => '551-600'], ['id' => '601-650', 'name' => '601-650'], ['id' => '651-700', 'name' => '651-700'], ['id' => '701-1200', 'name' => '700+']];
            $monthlyRevenueFilters = [['id' => '0-10', 'name' => '0-10K'], ['id' => '11-25', 'name' => '11-25K'], ['id' => '26-50', 'name' => '26-50K'], ['id' => '51-100', 'name' => '51-100K'], ['id' => '101', 'name' => '100K+']];
            $factorRateFilters = [['id' => '1.10-1.15', 'name' => '1.10-1.15'], ['id' => '1.16-1.20', 'name' => '1.16-1.20'], ['id' => '1.21-1.25', 'name' => '1.21-1.25'], ['id' => '1.26-1.30', 'name' => '1.26-1.30'], ['id' => '1.31-1.35', 'name' => '1.31-1.35'], ['id' => '1.36-1.40', 'name' => '1.36-1.40'], ['id' => '1.41-1.45', 'name' => '1.41-1.45'], ['id' => '1.46-1.50', 'name' => '1.46-1.50'], ['id' => '1.51', 'name' => '1.50+']];
            foreach ($creditScoreFilters as $creditscore) {
                $credit_score_arr = explode('-', $creditscore['id']);
                $credit_score1 = $credit_score_arr[0];
                $credit_score2 = $credit_score_arr[1];
                $count = Merchant::leftJoin('merchants_details','merchants_details.merchant_id','merchants.id')->where('marketplace_status', 1)->where('active_status', 1)->where('merchants.sub_status_id', '=', 1)->whereBetween('credit_score', [$credit_score1, $credit_score2])->count();
                if ($count > 0) {
                    $creditScoreArr[] = $creditscore;
                }
            }
            foreach ($monthlyRevenueFilters as $revenue) {
                $revenue_arr = explode('-', $revenue['id']);
                $revenue1 = $revenue_arr[0];
                $revenue2 = isset($revenue_arr[1]) ? $revenue_arr[1] : 0;
                $count = Merchant::leftJoin('merchants_details','merchants_details.merchant_id','merchants.id')->where('marketplace_status', 1)->where('active_status', 1)->where('merchants.sub_status_id', '=', 1);
                if ($revenue2 != 0) {
                    $count = $count->whereBetween('merchants_details.monthly_revenue', [$revenue1, $revenue2]);
                } else {
                    $count = $count->where('merchants_details.monthly_revenue', '>=', $revenue1);
                }
                $count = $count->count();
                if ($count > 0) {
                    $monthlyRevenueArr[] = $revenue;
                }
            }
            foreach ($factorRateFilters as $factor_rate) {
                $factor_rate_arr = explode('-', $factor_rate['id']);
                $factor_rate1 = $factor_rate_arr[0];
                $factor_rate2 = isset($factor_rate_arr[1]) ? $factor_rate_arr[1] : 0;
                $count = Merchant::where('marketplace_status', 1)->where('active_status', 1)->where('merchants.sub_status_id', '=', 1);
                if ($factor_rate2 != 0) {
                    $count = $count->whereBetween('factor_rate', [$factor_rate1, $factor_rate2]);
                } else {
                    $count = $count->where('factor_rate', '>=', $factor_rate1);
                }
                $count = $count->count();
                if ($count > 0) {
                    $factorRateArr[] = $factor_rate;
                }
            }
            $industryData = Industries::select('id', 'name')->get()->toArray();
            foreach ($industryData as $ind) {
                $count = Merchant::where('marketplace_status', 1)->where('active_status', 1)->where('merchants.sub_status_id', '=', 1);
                $count = $count->where('industry_id', $ind['id']);
                $count = $count->count();
                if ($count > 0) {
                    $industryArr[] = $ind;
                }
            }
            if (count($creditScoreArr) > 0) {
                $filters[0]['id'] = 1;
                $filters[0]['name'] = 'Credit Score';
                $filters[0]['filters'] = $creditScoreArr;
            }
            if (count($industryArr) > 0) {
                $filters[1]['id'] = 2;
                $filters[1]['name'] = 'Industry';
                $filters[1]['filters'] = $industryArr;
            }
            if (count($monthlyRevenueArr) > 0) {
                $filters[2]['id'] = 3;
                $filters[2]['name'] = 'Monthly Revenue';
                $filters[2]['filters'] = $monthlyRevenueArr;
            }
            if (count($factorRateArr) > 0) {
                $filters[3]['id'] = 4;
                $filters[3]['name'] = 'Factor Rate';
                $filters[3]['filters'] = $factorRateArr;
            }

            return new SuccessResource(['filters' => $filters]);
        }

        return new ErrorResource(['message' => 'Not a Participant']);
    }

    public function postMarketplaceFund(Request $request)
    {
        $merchantId = $request->input('merchantId');
        $investor = $this->user;
        $investorId = $investor->id;
        $fees = User::select('management_fee', 'global_syndication', 's_prepaid_status', 'underwriting_fee', 'underwriting_status')->where('id', $investorId)->first()->toArray();
        $user_details = UserDetails::where('user_id', $investorId)->first();
        if ($user_details) {
            $liquidity = $user_details->liquidity;
        } else {
            $liquidity = 0;
        }
        $PendingRequestedAmount = InvestorAchRequest::whereinvestor_id($investorId);
        $PendingRequestedAmount->where('ach_status', InvestorAchRequest::AchStatusAccepted);
        $PendingRequestedAmount->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing);
        $PendingRequestedAmount = $PendingRequestedAmount->sum('amount');
        if ($PendingRequestedAmount) {
            $liquidity += $PendingRequestedAmount;
        }
        $merchant = Merchant::where('id', $merchantId)->where('marketplace_status', 1)->first();
        if ($merchant) {
            $hasFunded = MerchantUserHelper::investorFunds($merchantId, $investorId);
            if ($hasFunded) {
                return new ErrorResource(['message' => 'You already funded this deal. Please contact admin if you want to make any changes.']);
            }
            $merchantFunds = MerchantUserHelper::investorFunds($merchantId);
            $maximum_amount = $merchant->max_participant_fund - $merchantFunds;
            $validator = \Validator::make($request->all(), ['amount' => "required|gt:0|lte:$maximum_amount"]);
            if ($validator->fails()) {
                return new ErrorResource($validator->messages());
            }
            $prepaid = $merchant->m_syndication_fee;
            $numberOfPayements = $merchant->pmnts;
            $factorRate = $merchant->factor_rate;
            $commissionPayable = ($merchant->commission != null) ? $merchant->commission : 0;
            $upsell_commissionPayable = ($merchant->up_sell_commission != null) ? $merchant->up_sell_commission : 0;
            $commissionPayable = $commissionPayable;

            $filterAmount = $request->input('amount');
            $total = $filterAmount;
            $rtr = $total * $factorRate;
            $managemnt_fee = ($fees['management_fee'] !== null) ? $fees['management_fee'] : $merchant->m_mgmnt_fee;
            $prepaid = ($fees['global_syndication'] !== null) ? $fees['global_syndication'] : $merchant->m_syndication_fee;
            $mgmnt_fee = ($managemnt_fee / 100) * $rtr;
            $rtr_net = $rtr - $mgmnt_fee;
            $part_pay_amount = $rtr / $numberOfPayements;
            $underwriting_fee = $merchant->underwriting_fee / 100 * $total;
            $m_syndication_fee_per = $prepaid;
            $commissionAmount = ($commissionPayable) / 100 * $total;
            $upsell_commissionAmount = $upsell_commissionPayable / 100 * $total;

            if (trim($merchant->advance_type) == 'weekly_ach') {
                $estimated_term_months = $numberOfPayements / 4;
            } else {
                $estimated_term_months = $numberOfPayements / 20;
            }
            $globalSyndication = 0;
            if ($prepaid != 0) {
                if ($fees['global_syndication'] !== null) {
                    $globalSyndication = $prepaid / 100 * ($fees['s_prepaid_status'] == 2 ? $filterAmount : $rtr);
                } else {
                    $globalSyndication = $prepaid / 100 * ($merchant->m_s_prepaid_status == 2 ? $total : $rtr);
                }
            }
            $gross_amount = $request->input('grossAmount');
            $totalDueAmount = $total + $commissionAmount + $globalSyndication + $underwriting_fee+$upsell_commissionAmount;
            $totalDueAmount = round($totalDueAmount, 2);
            if (($totalDueAmount - $gross_amount) < 1) {
                $totalDueAmount = $gross_amount;
            }
            $share = ($merchant->funded > 0) ? ($total * 100 / $merchant->funded) : 0;
            $date_en = \FFM::datetime(\Carbon\Carbon::now('UTC'));
            $ip_server = $this->getUserIp();
            if ($merchant->advance_type == 'credit_card_split') {
                $credit_card = 'Yes';
            } else {
                $credit_card = 'No';
            }
            if ($merchant->label == 1) {
                $mca = 'Yes';
            } else {
                $mca = 'No';
            }
            $details = [
                'merchant' => ($request->user()->display_value == 'mid') ? $merchant->id : $merchant->name,
                'merchant_id' => $merchant->id,
                'iid' => $investor->id,
                'participant' => $investor->name,
                'business_en_name' => ($request->user()->display_value == 'mid') ? $merchant->id : $merchant->name,
                'advance_type' => $merchant->advance_type,
                'investor_name' => $investor->investor_name,
                'merchant_date' => \FFM::date(date('Y-m-d')),
                'participant_date' => ($request->user()->agreement_date) ? FFM::date($request->user()->agreement_date) : '',
                'funded' => \FFM::dollar($merchant->funded),
                'date_funded' => \FFM::date($merchant->date_funded),
                'm_syndication_fee' => \FFM::dollar($globalSyndication),
                'm_syndication_fee_per' => \FFM::percent($m_syndication_fee_per),
                'rtr_gross' => \FFM::dollar($rtr),
                'rtr_net' => \FFM::dollar($rtr_net),
                'factor_rate' => round($factorRate, 2),
                'underwriting_fee_per' => \FFM::percent($merchant->underwriting_fee),
                'underwriting_fee' => \FFM::dollar($underwriting_fee),
                'daily_payment' => \FFM::dollar($merchant->payment_amount),
                'estimated_turns' => $merchant->pmnts,
                'upfront_commission_per' => \FFM::percent($commissionPayable+$upsell_commissionPayable),
                'upfront_commission' => \FFM::dollar(($commissionPayable * $total / 100)+($upsell_commissionPayable * $total / 100)), 
                'upsell_commission_per' => \FFM::percent($upsell_commissionPayable),
                'upsell_commission' => \FFM::dollar($upsell_commissionPayable * $total / 100),
                'participant_commission' => $commissionPayable * $total / 100,
                'management_fee_per' => \FFM::percent($managemnt_fee),
                'management_fee' => \FFM::dollar($mgmnt_fee),
                'participant_percent' => \FFM::percent($share),
                'participant_funded_amount' => \FFM::dollar($total),
                'participant_rtr' => $rtr,
                'duetotal' => \FFM::dollar($totalDueAmount),
                'user_id' => $this->user->id,
                'pmnts' => round($numberOfPayements),
                'date_en' => $date_en,
                'server' => $ip_server,
                'payment_amount' => \FFM::dollar($part_pay_amount),
                'estimated_term_months' => round($estimated_term_months, 2),
                'commission_amount' => $commissionAmount,
                'up_sell_commission'=>$upsell_commissionAmount,
                'mca' => $mca,
                'credit_card' => $credit_card,
                'rtr' => \FFM::dollar($merchant->rtr),
                'liquidity' => $liquidity,
            ];
            $pdfData = [
                'merchant' => ($request->user()->display_value == 'mid') ? $merchant->id : $merchant->name,
                'merchant_id' => $merchant->id,
                'iid' => $investor->id,
                'participant' => $investor->name,
                'business_en_name' => ($request->user()->display_value == 'mid') ? $merchant->id : $merchant->name,
                'advance_type' => $merchant->advance_type,
                'investor_name' => $investor->investor_name,
                'merchant_date' => \FFM::date(date('Y-m-d')),
                'participant_date' => ($request->user()->agreement_date) ? FFM::date($request->user()->agreement_date) : '',
                'funded' => $merchant->funded,
                'date_funded' => \FFM::date($merchant->date_funded),
                'm_syndication_fee' => $globalSyndication,
                'm_syndication_fee_per' => $m_syndication_fee_per,
                'rtr_gross' => $rtr,
                'rtr_net' => $rtr_net,
                'factor_rate' => \FFM::percent($factorRate),
                'underwriting_fee_per' => $merchant->underwriting_fee,
                'underwriting_fee' => $underwriting_fee,
                'daily_payment' => $merchant->payment_amount,
                'estimated_turns' => $merchant->pmnts,
                'upfront_commission_per' => $commissionPayable,
                'upfront_commission' => $commissionPayable * $total / 100,
                'participant_commission' => $commissionPayable * $total / 100,
                'management_fee_per' => $managemnt_fee,
                'management_fee' => $mgmnt_fee,
                'participant_percent' => $share,
                'participant_funded_amount' => $total,
                'participant_rtr' => $rtr,
                'duetotal' => $totalDueAmount,
                'user_id' => $this->user->id,
                'pmnts' => round($numberOfPayements),
                'date_en' => $date_en,
                'server' => $ip_server,
                'payment_amount' => $part_pay_amount,
                'estimated_term_months' => round($estimated_term_months, 2),
                'commission_amount' => $commissionAmount,
                'up_sell_commission'=>$upsell_commissionAmount,
                'upsell_commission_per'=>$upsell_commissionPayable,
                'mca' => $mca,
                'credit_card' => $credit_card,
                'rtr' => $merchant->rtr,
            ];
            $liquidity_status = true;
            $liquidity = round($liquidity, 2);
            if ($totalDueAmount > $liquidity) {
                $liquidity_status = false;
                $Bank = Bank::whereinvestor_id($investorId)->where('default_credit', 1)->first();
                $bank_account_no = '';
                if ($Bank) {
                    $bank_account_no = $Bank->acc_number;
                }

                return new ErrorResource([
                    'bank_account_no' => $bank_account_no,
                    'liquidity_status' => $liquidity_status,
                    'message' => 'You don not have enough liquidity to fund this deal.',
                    'data' => $details,
                    'balance_amount' => round($totalDueAmount - $liquidity, 2),
                ]);
            }
            if ($request->has('signed')) {
                $sign = $request->input('signed');
                $pdfURL = PdfDocumentHelper::fundMerchant($pdfData, $sign);

                return new SuccessResource([
                    'merchantId' => $merchantId,
                    'pdfUrl' => $pdfURL,
                    'liquidity_status' => $liquidity_status,
                    'message' => 'Fund Request Added.',
                ]);
            }

            return new SuccessResource(['liquidity_status' => $liquidity_status, 'data' => $details]);
        }

        return new ErrorResource([
            'liquidity_status' => $liquidity_status,
            'message' => 'No Merchant found with Permission',
        ]);
    }

    public function postMarketplaceDocuments(Request $request)
    {
        $merchantId = $request->input('merchantId');
        $documents = PdfDocumentHelper::getMerchantDocuments($merchantId);
        $documentData = [];
        foreach ($documents as $key => $document) {
            $fileUrl = Storage::disk('s3')->temporaryUrl($document->file_name,Carbon::now()->addMinutes(2));
            $fileExtension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
            $documentData[] = ['documentId' => $document->id, 'documentTitle' => $document->title, 'documentType' => $document->document_type, 'uploadDate' => $document->created_at->format('m-d-Y'), 'documentUrl' => $fileUrl, 'documentExtension' => $fileExtension];
        }
        if (count($documentData) > 0) {
            return new SuccessResource(['data' => $documentData]);
        }

        return new ErrorResource(['message' => 'No data found']);
    }

    public function postMerchantFilter(Request $request)
    {
        return new SuccessResource(['data' => MerchantHelper::getInvestorMerchant($this->user->id)->pluck(DB::raw('upper(name) as name'), 'id')->toArray()]);
    }

    public function postReportColumns(Request $request)
    {
        return new SuccessResource(['data' => InvestorHelper::getColumns()]);
    }

    public function postReport(Request $request)
    {
        return InvestorHelper::getReport($request);
    }

    public function postStatement(Request $request, $id = 0)
    {
        $keyword = $request->input('keyword');
        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;
        $sort = $request->input('sort_order');
        $sort_by = $request->input('sort_by');
        $sort_order = null;
        if ($sort != null) {
            $sort_order = ($sort == 1) ? 'ASC' : 'DESC';
        }
        if (! empty($id)) {
            return $this->statementDetail($id);
        }
        $statements = Statements::where('user_id', $this->user->id)->where('investor_portal', 0);
        if ($keyword != null) {
            $statements->where(function ($q) use ($keyword) {
                $q->Where(DB::raw("DATE_FORMAT(`created_at`, '%m-%d-%Y %H:%i:%s')"), 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw("DATE_FORMAT(`from_date`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw("DATE_FORMAT(`to_date`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%')->orWhere('file_name', 'LIKE', '%'.$keyword.'%');
            });
        }
        if ($sort_by != null && $sort_order != null) {
            if ($sort_by == 'csv_statement' || $sort_by == 'pdf_statement') {
                $statements = $statements->orderBy('file_name', $sort_order);
            }
            if ($sort_by == 'generated_date') {
                $statements = $statements->orderBy('created_at', $sort_order);
            }
        } else {
            $statements = $statements->orderByDesc('created_at');
        }
        $total_page = count($statements->get());
        $statements = $statements->offset($offset)->limit($limit)->get();
        $from = ($total_page != 0) ? $offset + 1 : 0;
        $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
        $no_of_pages = (int) ($total_page / $limit);
        if (($total_page % $limit) > 0) {
            $no_of_pages = $no_of_pages + 1;
        }
        $to = ($total_page != 0) ? $offset + count($statements->toArray()) : 0;
        $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];
        $two_factor_required = DB::table('roles')->where('name','investor')->value('two_factor_required');
        return StatementResource::collection($statements)->additional(['success' => true,'login_dashboard'=>(Auth::user()) ? Auth::user()->login_board : '','two_factor_mandatory'=>($two_factor_required==1 ? true :false), 'pagination' => $pagination]);
    }

    public function statementDetail($statementId)
    {
        $statement = Statements::findOrFail($statementId);
        $fileName = $statement->file_name;
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $url = '';
        $headers = ['Content-Description' => 'File Transfer', 'Content-Disposition' => "attachment; filename={$fileName}", 'filename' => $fileName];
        try {
            $file_contents = Storage::disk('s3')->get('/'.$fileName);

            return $response = response($file_contents, 200, ['Content-Type' => 'application/json', 'Content-Disposition' => 'attachment; filename="'.$file_name.'"']);
            $url = Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2));
        } catch (\ErrorException $e) {
            return new ErrorResource(['message' => $e->getMessage()]);
        }

        return new SuccessResource(['url' => $url]);
    }

    public function postInvestorMerchantView(Request $request)
    {
        $merchantId = $request->input('merchantId');
        $investor = $this->user;
        $investorId = $investor->id;
        $merchant = Merchant::where('id', $merchantId)->first();
        $Merchant = Merchant::find($merchantId);
        if ($Merchant) {
            $industry = $Merchant->industry->name;
        }
        if ($merchant) {
            $merchantData = MerchantUserHelper::getInvestorMerchantsViewDetails($investorId, $merchantId);
            $last_payment_date = ParticipantPaymentHelper::getMerchantLastPaymentDateToInvestor($merchantId, $investorId);
            $overpayment = 0;
            if (($merchantData->paid_participant_ishare+$merchantData->total_agent_fee) > $merchantData->invest_rtr) {
                $overpayment = ($merchantData->actual_paid_participant_ishare+$merchantData->total_agent_fee - $merchantData->invest_rtr) * (1 - ($merchantData->mgmnt_fee) / 100);
            }
            $participant_share_percent = ($merchantData->funded > 0) ? $merchantData->amount / $merchantData->funded * 100 : 0;
            $substatus = [11, 18, 19, 20];
            $investor_balance_amount = ($overpayment > 0) ? 0 : ($merchantData->invest_rtr - $merchantData->actual_paid_participant_ishare);
            [$total_mgmnt_paid, $paid_to_participant, $ctd_sum, $participant_share] = ParticipantPaymentHelper::getMerchantExtra($merchantId, $investorId);
            // $merchant_balance = (in_array($merchantData->sub_status_id, $substatus)) ? '0.0' : ($merchantData->invest_rtr - $participant_share);
            $merchant_balance = (in_array($merchantData->sub_status_id, $substatus)) ? '0.0' : (($merchantData->invest_rtr-$merchantData->management_fee) - $paid_to_participant);
            $profit = PaymentInvestorHelper::getProfitSum($investor, [4, 22], $merchantId);
            $annualised_rate = ($merchantData->tot_investment > 0) ? ($merchantData->tot_profit / $merchantData->tot_investment) * 100 : 0;
            if($merchantData->funded!=0){
            $payment_amount = $merchantData->payment_amount * ($merchantData->amount / $merchantData->funded);
            }else{
             $payment_amount = 0;   
            }


            $payment_unique_date = ParticipentPayment::where('payment_type', 1)->where('participent_payments.merchant_id', $merchantId)
            ->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
            $payment_unique_date = $payment_unique_date->where('payment_investors.user_id', $investorId);
            
           // $payment_unique_date = $payment_unique_date->groupBy('payment_date');
            $revert_payment_unique_date = clone $payment_unique_date;
            $revert_payment_unique_date = $revert_payment_unique_date->where('payment', '<', 0)->get()->toArray();

            $credit_payment_unique_date = $payment_unique_date->where('payment', '>', 0)->get()->toArray();
           
            $payment_remaining = $merchant->pmnts - count($credit_payment_unique_date) + count($revert_payment_unique_date);
            
            $actual_payment_remaining = ($payment_amount > 0) ? $merchant_balance / $payment_amount : 0;
            $fractional_part = fmod($payment_remaining, 1);
            $balance_payment = floor($payment_remaining);
            if ($fractional_part > .09) {
                $balance_payment = $balance_payment + 1;
            }
            if (in_array($merchant->sub_status_id, [4, 22, 18, 19, 20])) {
            $balance_payment = 0;
            }
            $pay_amount = bcadd(sprintf('%F', $payment_amount), '0', 2);
            $payment_cnt = ($pay_amount != 0) ? ($participant_share + $merchantData->total_agent_fee) / $pay_amount : 0;
            $payment_cnt = floor($payment_cnt);
            $payment_made = $payment_cnt;
            $advance_type = null;
            if ($merchantData->advance_type == 'daily_ach') {
                $advance_type = 'Daily';
            }
            if ($merchantData->advance_type == 'weekly_ach') {
                $advance_type = 'Weekly';
            }
            if ($merchantData->advance_type == 'credit_card_split') {
                $advance_type = 'Credit Card Split';
            }
            if ($merchantData->advance_type == 'variable_ach') {
                $advance_type = 'Variable ACH';
            }
            if ($merchantData->advance_type == 'lock_box') {
                $advance_type = 'Lock Box';
            }
            if ($merchantData->advance_type == 'hybrid') {
                $advance_type = 'Hybrid';
            }
            $investor_comp_per = ($merchantData->actual_paid_participant_ishare/$merchantData->invest_rtr)*100;
            $payment_remains = number_format(($balance_payment > 0) ? $balance_payment : 0);            
            if($investor_comp_per>99){
                $payment_remains = "None";
            }
            $dashboardView[] = ['merchant' => ($request->user()->display_value == 'mid') ? $merchantData->mid : $merchantData->name, 'display_value' => $request->user()->display_value, 'sub_status' => $merchantData->sub_status, 'mid' => $merchantData->mid, 'business_en_name' => ($request->user()->display_value == 'mid') ? $merchantData->mid : $merchantData->name, 'date_funded' => FFM::date($merchantData->date_funded), 'pmnts' => $merchantData->pmnts, 'rtr' => FFM::dollar($merchantData->invest_rtr-$merchantData->management_fee), 'factor_rate' => round($merchantData->factor_rate, 2), 'funded' => FFM::dollar($merchantData->funded), 'payment_amount' => FFM::dollar($payment_amount), 'commission_per' => FFM::percent($merchantData->commission_per), 'participant_rtr' => FFM::dollar($merchantData->invest_rtr), 'pre_paid' => FFM::dollar($merchantData->pre_paid), 'participant_funded' => FFM::dollar($merchantData->amount), 'overpayment' => FFM::dollar($overpayment), 'participant_share' => FFM::percent($participant_share_percent), 'investor_balance_amount' => FFM::dollar($investor_balance_amount), 'paid_to_participant' => FFM::dollar($paid_to_participant), 'management_fee_paid' => FFM::dollar($total_mgmnt_paid), 'ctd' => FFM::dollar($participant_share-$total_mgmnt_paid), 'balance' => FFM::dollar($merchant_balance), 'profit' => FFM::dollar($profit), 'last_payment_date' => FFM::date($last_payment_date), 'mgmnt_fee' => FFM::dollar($merchantData->management_fee), 'mgmnt_fee_percent' => FFM::percent($merchantData->mgmnt_fee), 'syndication_fee' => FFM::dollar($merchantData->pre_paid), 'syndication_fee_percent' => FFM::percent($merchantData->syndication_fee_percentage), 'under_writing_fee' => FFM::dollar($merchantData->under_writing_fee), 'under_writing_fee_percent' => FFM::percent($merchantData->under_writing_fee_per), 'commission' => FFM::dollar($merchantData->commission_amount+$merchantData->up_sell_commission),'up_sell_commission'=>FFM::dollar($merchantData->up_sell_commission), 'annualised_rate' => FFM::percent($annualised_rate), 'total_fee' => FFM::dollar($merchantData->total_fee), 'payment_remaining' => $payment_remains,'actual_payment_remaining'=> number_format(($actual_payment_remaining > 0) ? $actual_payment_remaining : 0), 'payment_made' => $payment_made, 'advance_type' => $advance_type, 'industry' => $industry];
            if (count($dashboardView) > 0) {
                return new SuccessResource(['data' => $dashboardView]);
            }
        }

        return new ErrorResource(['message' => 'No Merchant found']);
    }

    public function postInvestorMerchantPaymentView(Request $request)
    {
        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;
        $keyword = $request->input('keyword');
        $paymentData = [];
        $merchantId = $request->input('merchantId');
        $investor = $this->user;
        $investorId = $investor->id;
        $merchant = Merchant::where('id', $merchantId)->first();
        $sort_by = $request->input('sort_by');
        $sort_order = $request->input('sort_order');
        $s_order = null;
        if ($sort_order != null) {
            $s_order = ($sort_order == 1) ? 'ASC' : 'DESC';
        }
        if ($merchant) {
            [$query, $sumQuery] = ParticipantPaymentHelper::getMerchantPaymentDetails($this->user->id, $merchantId, $keyword, $sort_by, $s_order);
            $results = $query->offset($offset)->limit($limit)->get();
            $total = $sumQuery->get()->toArray();
            $total_page = $total[0]['count'];
            $from = ($total_page != 0) ? $offset + 1 : 0;
            $to = ($total_page != 0) ? $offset + count($results->toArray()) : 0;
            $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
            $no_of_pages = (int) ($total_page / $limit);
            if (($total_page % $limit) > 0) {
                $no_of_pages = $no_of_pages + 1;
            }
            $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];
            $total = collect($total)->map(function ($record) {
                return ['total_payment' => \FFM::dollar($record['total_payment'] ?? 0), 'total_participant_share' => \FFM::dollar($record['total_participant_share'] ?? 0), 'total_mgmnt_fee' => \FFM::dollar($record['total_mgmnt_fee'] ?? 0), 'total_to_participant' => \FFM::dollar($record['total_to_participant'] ?? 0)];
            })->first();
            $results = collect($results)->map(function ($result, $index) {
                return $paymentData[] = ['date_settled' => \FFM::date($result->payment_date), 'total_payment' => FFM::dollar($result->payment), 'participant_share' => FFM::dollar($result->participant_share), 'management_fee' => FFM::dollar($result->mgmnt_fee), 'to_participant' => FFM::dollar($result->participant_share - $result->mgmnt_fee), 'transaction_type' => $result->mode_of_payment, 'rcode' => $result->code];
            });

            return new SuccessResource(['data' => $results, 'total' => $total, 'pagination' => $pagination]);
        }

        return new ErrorResource(['message' => 'No Merchant found']);
    }

    public function postInvestorAchRequestSend(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = ['transaction_type' => 'debit', 'transaction_method' => InvestorAchRequest::MethodByMarketplaceCredit, 'request_ip_address' => $request->ip(), 'investor_id' => $request->user()->id, 'amount' => $request['amount'], 'bank_id' => $request['id'] ?? ''];
            if (isset($request['transaction_type'])) {
                $request['transaction_type'] = strtolower($request['transaction_type']);
                switch ($request['transaction_type']) {
                    case 'debit':
                        $data['transaction_type'] = 'debit';
                        $data['transaction_method'] = InvestorAchRequest::MethodByParticipantCredit;
                        $data['transaction_category'] = InvestorAchRequest::CategoryTransferToVelocity;
                        break;
                    case 'credit':
                        $data['transaction_type'] = 'credit';
                        $data['transaction_method'] = InvestorAchRequest::MethodByParticipantDebit;
                        $data['transaction_category'] = InvestorAchRequest::CategoryTransferToBank;
                        break;
                    default:
                        break;
                }
            } else {
                throw new Exception('Do not have Transaction Type in the request! ', 1);
            }
            if (isset($request['bank_id'])) {
                $data['bank_id'] = $request['bank_id'];
            }
            $ActumRequest = new ActumRequest;
            $return_result = $ActumRequest->RequestHandler($data);
            if ($return_result['InvestorAchRequest'] == 'created') {
                DB::commit();
            }
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $transaction_type = ucfirst($data['transaction_type']);
            $message['title'] = 'Ach '.$transaction_type.' Requested';
            $message['subject'] = $message['title'];
            $message['Investor'] = $request->user()->name;
            $message['investor_id'] = $request->user()->id;
            $message['amount'] = $request['amount'];
            $message['type'] = $transaction_type;
            $message['date'] = FFM::date(date('Y-m-d'));
            $message['to_mail'] = $request->user()->notification_email;
            $message['Creator'] = 'Investor';
            $message['creator_name'] = $request->user()->name;
            $message['status'] = 'investor_ach_request';
            if ($message['to_mail']) {
                $email_template = Template::where([
                    ['temp_code', '=', 'ACDR'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $emails = Settings::value('email');
                    $emailArray = explode(',', $emails);
                    $message['to_mail'] = $emailArray;
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $emailArray);
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
                
            }

            return new SuccessResource(['message' => 'Transaction Request Added Successfully', 'data' => $request->all()]);
        } catch (\Exception $e) {
            DB::rollback();

            return new ErrorResource(['message' => $e->getMessage(), 'data' => $request->all()]);
        }

        return response()->json($return);
    }

    public function postInvestmentReport(Request $request)
    {
        $data = $request->all();
        $merchant = $data['merchant_name'];
        $startDate = $data['sDate'] ?? null;
        $endDate = $data['eDate'] ?? null;
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        $keyword = $request->input('keyword');
        $merchant_id = $request->input('merchant_id');
        $sort = $request->input('sort_order');
        $sort_by = $request->input('sort_by');
        $sort_order = null;
        if ($sort != null) {
            $sort_order = ($sort == 1) ? 'ASC' : 'DESC';
        }
        $display_value = $request->user()->display_value;
        if (! empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
        }
        if (! empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
        }
        if ($sort_by != null && $sort_order != null) {
            if ($sort_by == 'merchant_name') {
                $datas = InvestmentReportView::orderBy('Merchant', $sort_order);
            }
            if ($sort_by == 'funded_date') {
                $datas = InvestmentReportView::orderBy('date_funded', $sort_order);
            }
            if ($sort_by == 'share') {
                $datas = InvestmentReportView::orderBy('share_t', $sort_order);
            }
            if ($sort_by == 'funded_amount') {
                $datas = InvestmentReportView::orderBy('i_amount', $sort_order);
            }
            if ($sort_by == 'rtr') {
                $datas = InvestmentReportView::orderBy(DB::raw('i_rtr - mgmnt_fee'), $sort_order);
            }
            if ($sort_by == 'management_fee') {
                $datas = InvestmentReportView::orderBy('mgmnt_fee', $sort_order);
            }
            if ($sort_by == 'commission') {
                $datas = InvestmentReportView::orderBy('commission_amount', $sort_order);
            }
            if ($sort_by == 'syndication_fee') {
                $datas = InvestmentReportView::orderBy('pre_paid', $sort_order);
            }
            if ($sort_by == 'under_writing_fee') {
                $datas = InvestmentReportView::orderBy('under_writing_fee', $sort_order);
            }
            if ($sort_by == 'total_invested') {
                $datas = InvestmentReportView::orderBy('invested_amount', $sort_order);
            }
            if ($sort_by == 'created_on') {
                $datas = InvestmentReportView::orderBy('investment_report_views.created_at', $sort_order);
            }
        } else {
            $datas = InvestmentReportView::orderByDesc('date_funded');
        }
        if ($keyword != null) {
            $datas->where(function ($q) use ($keyword, $display_value) {
                $num_keyword = str_replace('$', '', $keyword);
                $perc_keyword = str_replace('%', '', $keyword);
                $q->where(DB::raw("IF('$display_value'='mid',merchant_id,Merchant)"), 'LIKE', '%'.$keyword.'%')->orWhere('date_funded', 'LIKE', '%'.$keyword.'%')->orWhere('share_t', 'LIKE', '%'.$perc_keyword.'%')->orWhere('i_amount', 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere(DB::raw('i_rtr - mgmnt_fee'), 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere('mgmnt_fee', 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')
                //->orWhere('commission_amount', 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')
                ->orWhere('pre_paid', 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')
                //->orWhere('under_writing_fee', 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')
                ->orWhere('invested_amount', 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere(DB::raw("DATE_FORMAT(`created_at`, '%m-%d-%Y %H:%i:%s')"), 'LIKE', '%'.$keyword.'%');
               // ->orWhere('investment_report_views.created_at', 'LIKE', '%'.$keyword.'%');
            });
        }
        if ($startDate != null) {
            $datas->where('date_funded', '>=', $startDate);
        }
        if ($endDate != null) {
            $datas->where('date_funded', '<=', $endDate);
        }
        $datas->whereinvestor_id($request->user()->id);
        if ($merchant_id != null) {
            $merchant_id_arr = explode(',', $merchant_id);
            $datas->whereIn('merchant_id', $merchant_id_arr);
        }
        $datasTotal = clone $datas;
        $Count = $datas;
        $total_page = count($Count->get()->toArray());
        $datas->offset($offset);
        $datas->limit($limit);
        $datas = $datas->select('investment_report_views.*', DB::raw("IF('$display_value'='mid',merchant_id,upper(Merchant)) as Merchant"),DB::raw('(investment_report_views.i_rtr-investment_report_views.mgmnt_fee) as i_rtr'),DB::raw('(investment_report_views.commission_amount+investment_report_views.up_sell_commission) as commission_amount'))->get()->toArray();
        $datasTotal->select([DB::raw('
		ROUND(sum(i_amount),2) as i_amount,
		sum(i_rtr-mgmnt_fee) as i_rtr,
		sum(commission_amount+up_sell_commission) as commission_amount,
		sum(share_t) as share_t,
		sum(pre_paid) as pre_paid,
		sum(invested_amount) as invested_amount,
		sum(under_writing_fee) as under_writing_fee,
		sum(mgmnt_fee) as mgmnt_fee,
        sum(up_sell_commission) as t_up_sell_commission
		')]);
        $datasTotal = $datasTotal->first();
        $from = ($total_page != 0) ? $offset + 1 : 0;
        $to = ($total_page != 0) ? ($offset + count($datas)) : 0;
        $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
        $no_of_pages = (int) ($total_page / $limit);
        if (($total_page % $limit) > 0) {
            $no_of_pages = $no_of_pages + 1;
        }
        $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];

        return new SuccessResource(['data' => ['data' => $datas, 'total' => $datasTotal, 'pagination' => $pagination, 'download-url' => url('api/investor/download/investment-report?token='.$this->user->getDownloadToken())]]);
    }

    public function postInvestorDetails(Request $request)
    {
        $data = $request->all();
        $merchant_id = $data['merchant_id'];
        $startDate = $data['sDate'] ?? MerchantUserView::orderBy('date_funded')->first()->date_funded;
        $endDate = $data['eDate'] ?? date('Y-m-d');
        if (! empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
        }
        if (! empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
        }
        $datas = MerchantUserView::orderBy('Merchant');
        $datas->whereBetween('date_funded', [$startDate, $endDate]);
        $datas->wheremerchant_id($data['merchant_id']);
        $datas->whereinvestor_id($request->user()->id);
        $datas = $datas->get(['Investor', 'amount', 'commission_amount', 'under_writing_fee', 'pre_paid', 'total_investment']);

        return new SuccessResource(['data' => ['data' => $datas]]);
    }

    public function postTransactionReportOLd(Request $request)
    {
        $data = $request->all();
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        $sort = $request->input('sort_order');
        $sort_by = $request->input('sort_by');
        $keyword = $request->input('keyword');
        $isExport = $request->input('is_export', false);
        $sort_order = null;
        if ($sort != null) {
            $sort_order = ($sort == 1) ? 'ASC' : 'DESC';
        }
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $investor_id = $request->user()->id;
        $data = $this->mBilll;
        $data = $data->join('users', 'users.id', 'investor_transactions.investor_id');
        $data->where('investor_id', $investor_id);
        $data->select(['investor_transactions.batch', 'investor_transactions.id', DB::raw('sum(amount) as amount'), 'investor_transactions.investor_id', 'category_notes', 'transaction_category', 'transaction_method', 'transaction_type', 'account_no', 'date']);
        if (empty($permission)) {
            $data->where('company', $investor_id);
        }
        if ($request->sDate) {
            $data->where('date', '>=', $request->sDate);
        }
        if ($request->eDate) {
            $data->where('date', '<=', $request->eDate);
        }
        if ($request->companies) {
            $data->where('users.company', $request->companies);
        }
        if ($request->investor_type) {
            $data->whereIn('users.investor_type', $request->investor_type);
        }
        if ($request->categories) {
            $data->whereIn('transaction_category', $request->categories);
        }
        if ($request->account_no) {
            $data->where('investor_transactions.account_no', 'like', "%{$request->account_no}%");
        }
        $data->groupBy('batch');
        if ($sort_by != null && $sort_order != null) {
            switch ($sort_by) {
                case 'amount':
                    $data->orderBy(DB::raw('amount'), $sort_order);
                    break;
                case 'account_no':
                    $data->orderBy('investor_transactions.account_no', $sort_order);
                    break;
                default:
                    break;
            }
        }
        $categories = \ITran::getAllOptions();
        $tran_method = InvestorTransaction::transactionMethodOptions();
        $tran_type = InvestorTransaction::transactionTypeOptions();
        $category_arr = [];
        $tran_type_arr = [];
        if ($keyword != null) {
            foreach ($categories as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $category_arr[] = $key;
                }
            }
            foreach ($tran_type as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $tran_type_arr[] = $key;
                }
            }
            $data = $data->where(function ($q) use ($keyword, $category_arr, $tran_type_arr) {
                $num_keyword = str_replace('$', '', $keyword);
                $q->where(DB::raw('amount'), 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere(DB::raw("DATE_FORMAT(`date`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%')->orWhere('investor_transactions.account_no', 'LIKE', '%'.$keyword.'%')->orWhereIn('investor_transactions.transaction_category', $category_arr)->orWhereIn('investor_transactions.transaction_type', $tran_type_arr);
            });
        }
        $result_data = $data;
        if (! $request->account_no) {
            $data = DB::table('investor_ach_requests')->select(DB::raw('NULL as batch'), 'id', 'amount', 'investor_id', DB::raw('NULL as category_notes'), DB::raw('NULL as transaction_category'), 'transaction_method', 'transaction_type', DB::raw('NULL as account_no'), 'date')->where('investor_id', $investor_id)->union($result_data)->orderByDesc('date')->where(function ($q) use ($keyword, $category_arr, $tran_type_arr) {
                $num_keyword = str_replace('$', '', $keyword);
                $q->where(DB::raw('amount'), 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere(DB::raw("DATE_FORMAT(`date`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%')->orWhere('transaction_type', $keyword);
            });
            $data->whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['debit', 'credit', 'same_day_debit']);
            if ($request->sDate) {
                $data->where('investor_ach_requests.date', '>=', $request->sDate);
            }
            if ($request->eDate) {
                $data->where('investor_ach_requests.date', '<=', $request->eDate);
            }
        }
        if ($sort_by != null && $sort_order != null) {
            switch ($sort_by) {
                case 'amount':
                    $data->orderBy(DB::raw('amount'), $sort_order);
                    break;
            }
        }
        $dataTotal = $data;
        $total_amount = array_sum(array_column($dataTotal->get()->toArray(), 'amount'));
        $Count = $data;
        $total_page = count($Count->get()->toArray());
        $data = $data->get();
        foreach ($data as $key => $value) {
            $method_sort_col[] = isset($value->transaction_method) ? ($tran_method[$value->transaction_method]) : null;
            $date_sort_col[] = $value->date;
            $accnt_sort_col[] = $value->account_no;
            $data[$key]->amount = FFM::dollar($value->amount);
            $data[$key]->date = FFM::date($value->date);
            $amount_sort_col[] = $value->amount;
            if (isset($value->transaction_category)) {
                $data[$key]->transaction_category = isset($categories[$value->transaction_category]) ? $categories[$value->transaction_category] : '';
            } elseif (isset($value->transaction_type)) {
                if ($value->transaction_type == 'debit') {
                    $data[$key]->transaction_category = 'Pending to velocity';
                }
                if ($value->transaction_type == 'credit') {
                    $data[$key]->transaction_category = 'Pending to Bank';
                }
            } else {
                $data[$key]->transaction_category = null;
            }
            $category_sort_col[] = $data[$key]->transaction_category;
            $data[$key]->TransactionMethod = isset($value->transaction_method) ? ($tran_method[$value->transaction_method]) : null;
            if (isset($value->transaction_type)) {
                if ($value->transaction_type == 'debit') {
                    $data[$key]->TransactionType = 'Credit';
                } elseif ($value->transaction_type == 'credit') {
                    $data[$key]->TransactionType = 'Debit';
                } else {
                    $data[$key]->TransactionType = $tran_type[$value->transaction_type];
                }
            } else {
                $data[$key]->TransactionType = null;
            }
            $type_sort_col[] = $data[$key]->TransactionType;
        }
        $data = $data->toArray();
        if (count($data) > 0) {
            if ($sort_by != null && $sort_order != null) {
                switch ($sort_by) {
                    case 'category':
                        $sort_order = ($sort == 1) ? SORT_ASC : SORT_DESC;
                        array_multisort($category_sort_col, $sort_order, $data);
                        break;
                    case 'method':
                        $sort_order = ($sort == 1) ? SORT_ASC : SORT_DESC;
                        array_multisort($method_sort_col, $sort_order, $data);
                        break;
                    case 'type':
                        $sort_order = ($sort == 1) ? SORT_ASC : SORT_DESC;
                        array_multisort($type_sort_col, $sort_order, $data);
                        break;
                    case 'date':
                        $sort_order = ($sort == 1) ? SORT_ASC : SORT_DESC;
                        array_multisort($date_sort_col, $sort_order, $data);
                        break;
                    case 'account_no':
                        $sort_order = ($sort == 1) ? SORT_ASC : SORT_DESC;
                        array_multisort($accnt_sort_col, $sort_order, $data);
                        break;
                }
            }
        }
        if ($isExport != 'yes') {
            $data = array_slice($data, $offset, $limit);
        }
        $dataTotal = $dataTotal->first();
        if ($dataTotal) {
            $dataTotal->amount = FFM::dollar($total_amount);
        }
        $from = ($total_page != 0) ? $offset + 1 : 0;
        $to = ($total_page != 0) ? ($offset + count($data)) : 0;
        $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
        $no_of_pages = (int) ($total_page / $limit);
        if (($total_page % $limit) > 0) {
            $no_of_pages = $no_of_pages + 1;
        }
        $user_details = UserDetails::where('user_id', $this->user->id)->first();
        $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];
        $Bank = Bank::whereinvestor_id($investor_id)->where('default_debit', 1)->first();
        $default_credit_bank = Bank::whereinvestor_id($investor_id)->where('default_credit', 1)->first();
        $bank_account_no = '';
        if ($Bank) {
            $bank_account_no = $Bank->acc_number;
        }
        $default_credit_bank_acc_number = ($default_credit_bank) ? $default_credit_bank->acc_number : '';
        $recurrence_types = [['value' => '1', 'label' => 'Weekly'], ['value' => '2', 'label' => 'Monthly'], ['value' => '3', 'label' => 'Daily'], ['value' => '4', 'label' => 'On Demand']];

        return new SuccessResource(['data' => ['liquidity' => number_format($user_details->liquidity, 2), 'bank_account_no' => $bank_account_no, 'recurrence_types' => (array) $recurrence_types, 'data' => $data, 'total' => $dataTotal, 'pagination' => $pagination, 'notification_recurence' => $this->user->notification_recurence, 'default_credit_account_number' => $default_credit_bank_acc_number, 'download-url' => url('api/investor/download/investment-transaction-report?token='.$this->user->getDownloadToken())]]);
    }

    public function postTransactionDetails(Request $request)
    {
        $data = $request->all();
        $batch = $data['batch'];
        $datas = $this->mBilll;
        $datas = $datas->whereinvestor_id($request->user()->id);
        $datas = $datas->where('investor_transactions.batch', $request->batch);
        $datas = $datas->join('users', 'users.id', 'investor_transactions.investor_id');
        $datas = $datas->join('user_details', 'user_details.user_id', 'users.id');
        if ($request->sDate) {
            $datas = $datas->where('date', '>=', $request->sDate);
        }
        if ($request->eDate) {
            $datas = $datas->where('date', '<=', $request->eDate);
        }
        $datas = $datas->select('users.id', 'users.name', 'users.investor_type', 'liquidity', 'investor_transactions.amount', 'investor_transactions.investor_id');
        $datas = $datas->get();
        foreach ($datas as $key => $value) {
            $datas[$key]['amount'] = FFM::dollar($value->amount);
        }

        return new SuccessResource(['data' => ['data' => $datas]]);
    }

    public function postDefaultRateMerchantReport(Request $request)
    {
        $data = $request->all();
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        $sort = $request->input('sort_order');
        $sort_by = $request->input('sort_by');
        $keyword = $request->input('keyword');
        $sort_order = null;
        if ($sort != null) {
            $sort_order = ($sort == 1) ? 'ASC' : 'DESC';
        }
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $investor_id = Auth::user()->id;
        $default_date = ! empty($request['eDate']) ? $request['eDate'] : now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        $datas = $this->merchant->merchantDefaulRateForInvestor($request['sDate'] ?? '', $request['eDate'] ?? '', [Auth::user()->id], '', [4,18,19,20,22], '', $request['days'], '');
        $array1 = implode(',', [4,18,19,20,22]);
        if ($sort_by != null && $sort_order != null) {
            if ($sort_by == 'id') {
                $datas = $datas->orderBy('merchants.id', $sort_order);
            }
            if ($sort_by == 'merchant') {
                $datas = $datas->orderBy('merchants.name', $sort_order);
            }
            if ($sort_by == 'default_date') {
                $datas = $datas->orderBy('merchants.last_status_updated_date', $sort_order);
            }
            if ($sort_by == 'funded_date') {
                $datas = $datas->orderBy('merchants.date_funded', $sort_order);
            }
            if ($sort_by == 'default_invested_amount') {
                $datas = $datas->orderBy(DB::raw('(
	
	
	'.$merchant_day.'
	
	*
	
	
	(
	
	
	sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
	-
	
	( sum( IF(sub_status_id IN ('.$array1.'),
	
	(merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ),
	
	
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
	
	)'), $sort_order);
            }
            if ($sort_by == 'default_rtr_amount') {
                $datas = $datas->orderBy(DB::raw(' sum(
	
	'.$merchant_day.'
	
	*
	
	
	(
	
	merchant_user.invest_rtr +  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) ) , 0 )
	
	-
	
	merchant_user.invest_rtr *(merchant_user.mgmnt_fee)/100
	+  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) *(merchant_user.mgmnt_fee)/100  ) , 0 )
	
	-
	IF(
	merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee,
	
	merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee,0
	
	
	)
	
	
	)
	
	)
	'), $sort_order);
            }
        }
        if (! empty($keyword)) {
            $num_keyword = str_replace('$', '', $keyword);
            $datas->where(function ($q) use ($keyword, $merchant_day, $array1, $num_keyword) {
                $q->where('merchants.id', 'LIKE', '%'.$keyword.'%')->orWhere('merchants.name', 'LIKE', '%'.$keyword.'%')->orWhere('merchants.date_funded', 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw('(
	
	'.$merchant_day.'
	
	*
	
	
	(
	
	merchant_user.invest_rtr +  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) ) , 0 )
	
	-
	
	merchant_user.invest_rtr *(merchant_user.mgmnt_fee)/100
	+  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) *(merchant_user.mgmnt_fee)/100  ) , 0 )
	
	-
	IF(
	merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee,
	
	merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee,0
	
	
	)
	
	
	)
	
	)
	'), 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere(DB::raw('(
	
	
	'.$merchant_day.'
	
	*
	
	
	(
	
	
	(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
	-
	
	( ( IF(sub_status_id IN ('.$array1.'),
	
	(merchant_user.actual_paid_participant_ishare - merchant_user.paid_mgmnt_fee ),
	
	
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
	
	)'), 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere('merchants.last_status_updated_date', 'LIKE', '%'.$keyword.'%');
            });
        }
        $TotalDatas = $datas;
        $total_page = count($TotalDatas->get());
        $total['default_amount'] = 0;
        $total['investor_rtr'] = 0;
        $datas = $datas->get();
        foreach ($datas as $key => $value) {
            $total['default_amount'] += $value->default_amount;
            $total['investor_rtr'] += $value->investor_rtr;
            $datas[$key]['default_amount'] = FFM::dollar($value->default_amount);
            $datas[$key]['investor_rtr'] = FFM::dollar($value->investor_rtr);
            $datas[$key]['last_status_updated_date'] = FFM::datetime($value->last_status_updated_date);
            $datas[$key]['date_funded'] = FFM::date($value->date_funded);
        }
        $datas = array_slice($datas->toArray(), $offset, $limit);
        $total['default_amount'] = FFM::dollar($total['default_amount']);
        $total['investor_rtr'] = FFM::dollar($total['investor_rtr']);
        $from = ($total_page != 0) ? $offset + 1 : 0;
        $to = ($total_page != 0) ? ($offset + count($datas)) : 0;
        $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
        $no_of_pages = (int) ($total_page / $limit);
        if (($total_page % $limit) > 0) {
            $no_of_pages = $no_of_pages + 1;
        }
        $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];

        return new SuccessResource(['data' => ['data' => $datas, 'total' => $total, 'pagination' => $pagination, 'download-url' => url('api/investor/download/default-rate-merchant-report?token='.$this->user->getDownloadToken())]]);
    }

    public function postMerchantsList()
    {
        $datas = MerchantUser::leftJoin('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchant_user.user_id', $this->user->id)->join('users', 'users.id', 'merchant_user.user_id')->select(DB::raw("IF(display_value='mid',merchants.id,upper(merchants.name)) as name"), 'merchants.id')
        ->pluck('name', 'id');

        return new SuccessResource(['list' => $datas]);
    }

    public function postBanks(Request $request)
    {
        $Banks = Bank::whereinvestor_id($request->user()->id)->get();
        foreach ($Banks as $key => $value) {
            $Banks[$key]['type'] = explode(',', $value['type']);
            $Banks[$key]['acc_number'] = FFM::mask_cc($value['acc_number']);
        }
        $UserDetails = UserDetails::whereuser_id($request->user()->id)->first();
        $liquidity = $UserDetails->liquidity;

        return new SuccessResource(['data' => $Banks, 'liquidity' => $liquidity]);
    }

    public function postBankCreate(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $data['investor_id'] = $request->user()->id;
            $SelfModel = new Bank;
            $data['type'] = '';
            if (isset($request->type)) {
                $data['type'] = implode(',', $request->type);
            }
            $return_function = $SelfModel->selfCreate($data);
            if ($return_function['result'] != 'success') {
                throw new Exception($return_function['result'], 1);
            }
            DB::commit();

            return new SuccessResource(['message' => 'Bank Created Successfully!', 'data' => $data]);
        } catch (Exception $e) {
            DB::rollback();

            return new ErrorResource(['message' => $e->getMessage(), 'data' => $data]);
        }
    }

    public function postBankUpdate(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            if (! empty($request->acc_number)) {
                $data = $request->all();
            } else {
                $data = $request->except('acc_number');
            }
            $data['investor_id'] = $request->user()->id;
            $data['type'] = '';
            if (isset($request->type)) {
                $data['type'] = implode(',', $request->type);
            }
            $SelfModel = new Bank;
            $return_function = $SelfModel->selfUpdate($data, $id);
            if ($return_function['result'] != 'success') {
                throw new Exception($return_function['result'], 1);
            }
            DB::commit();

            return new SuccessResource(['message' => 'Bank Updated Successfully', 'data' => $data]);
        } catch (Exception $e) {
            DB::rollback();

            return new ErrorResource(['message' => $e->getMessage(), 'data' => $data]);
        }
    }
    public function postDefaultBankUpdate(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->except('acc_number');
            
            $data['investor_id'] = $request->user()->id;
            $data['type'] = '';
            if (isset($request->type)) {
                $data['type'] = implode(',', $request->type);
            }
            $SelfModel = new Bank;
            $return_function = $SelfModel->selfUpdate($data, $id);
            if ($return_function['result'] != 'success') {
                throw new Exception($return_function['result'], 1);
            }
            DB::commit();

            return new SuccessResource(['message' => 'Bank Updated Successfully', 'data' => $data]);
        } catch (Exception $e) {
            DB::rollback();

            return new ErrorResource(['message' => $e->getMessage(), 'data' => $data]);
        }
    }

    public function postBankDelete(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            if (! $request['id']) {
                throw new Exception('Empty ID From Request', 1);
            }
            $SelfModel = new Bank;
            $return_function = $SelfModel->selfDelete($request['id']);
            if ($return_function['result'] != 'success') {
                throw new Exception($return_function['result'], 1);
            }
            DB::commit();

            return new SuccessResource(['message' => 'Bank Deleted Successfully', 'data' => $data]);
        } catch (Exception $e) {
            DB::rollback();

            return new ErrorResource(['message' => $e->getMessage(), 'data' => $data]);
        }
    }

    public function postFaq()
    {
        $data['data'] = Faq::where('user_type', 1)->get();
        $data['status'] = true;

        return response()->json($data, 200);
    }

    public function postFaqApp()
    {
        try {
            return new SuccessResource(['data' => Faq::where('user_type', 1)->where('app', 1)->get()]);
        } catch (Exception $e) {
            return new ErrorResource(['message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function postUpdateUser(Request $request)
    {
        try {
            $data = $request->all();
            $User = User::find($request->user()->id);
            if (isset($data['notification_recurence'])) {
                $User->notification_recurence = $data['notification_recurence'];
            }
            $User->save();

            return new SuccessResource(['message' => 'User Updated Successfully', 'data' => $data]);
        } catch (Exception $e) {
            return new ErrorResource(['message' => $e->getMessage(), 'data' => $data]);
        }
    }

    public function getEditInvestor(Request $request)
    {
        try {
            $User = User::find($request->user()->id);

            return new SuccessResource(['data' => $User]);
        } catch (Exception $e) {
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }
    public function getTwoFactorMandatory(Request $request){
        $two_factor_required = DB::table('roles')->where('name','investor')->value('two_factor_required');
        $two_factor_mandatory = ($two_factor_required==1 ? true :false);
        return new SuccessResource(['two_factor_mandatory' => $two_factor_mandatory]);

    }

    public function postUpdateInvestor(Request $request)
    {
        try {
            $User = User::find($request->user()->id);
            $User->name = $request->name;
            $User->cell_phone = $request->cell_phone;
            $User->notification_email = $request->notification_email;
            $User->email = $request->email;
            if($request->password!=null){
            $User->password = $request->password;
            }
            $User->source_from = 'mobile';
            $User->save();

            return new SuccessResource(['message' => 'User Updated Successfully']);
        } catch (Exception $e) {
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function getGraphFilter(Request $request)
    {
        try {
            $attribute = [['value' => 0, 'label' => 'Label'], ['value' => 1, 'label' => 'Status'], ['value' => 2, 'label' => 'Industry'], ['value' => 3, 'label' => 'Investor'], ['value' => 4, 'label' => 'Lenders'], ['value' => 5, 'label' => 'Commissions'], ['value' => 6, 'label' => 'Factor rate'], ['value' => 7, 'label' => 'State'], ['value' => 8, 'label' => 'No Filter']];
            $graph_value = [['value' => 0, 'label' => 'Invested Amount'], ['value' => 1, 'label' => 'Default Amount']];
            $labels = Label::select('name as label', 'id as value')->get()->toArray();
            $count = count($labels);

            $labels[$count]['label'] = 'All';
            $labels[$count]['value'] = '';
            array_multisort(array_map(function ($labels) {
                return $labels['value'];
            }, $labels), SORT_ASC, $labels);

            // $labels = Label::pluck('name', 'id')->toArray();
            // $labels[' '] = 'All';
            // $labels = array_reverse($labels, true);print_r($labels);exit;
            $lenders = Merchant::select('name as label', 'id as value')->where('lender_id', $request->user()->id)->get();
            $data['lenders'] = $lenders;
            $data['attribute'] = $attribute;
            $data['graph_value'] = $graph_value;
            $data['labels'] = $labels;

            return new SuccessResource(['data' => $data]);
        } catch (Exception $e) {
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function postChartValues(Request $request)
    {
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $lender = $request->lender;
        $investor_filter = [$request->user()->id];
        $userId = $request->user()->id;
        $subinvestors = [];
        $date_start = '2010-01-01';
        $date_end = date('Y-m-d');
        if (isset($request->date_start) && $request->date_start != '') {
            $date_start = $request->date_start;
        }
        if (isset($request->date_end) && $request->date_end != '') {
            $date_end = $request->date_end;
        }
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $query987 = \MTB::getChartData($request->attribute, $request->graph_value, 1);
        if ($request->user()->hasRole('company')) {
            $query987 = $query987->whereIn('merchant_user.user_id', $subinvestors);
        }
        if (isset($lender) && $lender) {
            $query987 = $query987->where('merchants.lender_id', $lender);
        }
        if (isset($request->label) && $request->label != '') {
            $query987 = $query987->where('merchants.label', $request->label);
        }
        if (isset($investor_filter) && $investor_filter) {
            $query987 = $query987->whereIn('merchant_user.user_id', $investor_filter);
        }
        if (isset($request->label) && $request->label != '') {
            $query987 = $query987->where('merchants.label', $request->label);
        }
        if ($request->date_start != '' || $request->date_end != '') {
            $query987 = $query987->whereBetween('merchants.date_funded', [$date_start, $date_end]);
        }
        $result_arr = ($query987->get())->toArray();
        array_multisort(array_column($result_arr, 'name'), SORT_ASC, $result_arr);
        if ($request->attribute == 6) {
            $dd = $a = [];
            foreach ($result_arr as $key => $val) {
                $name = round($result_arr[$key]->name, 2);
                $amount = $result_arr[$key]->amount;
                for ($x = 0; $x <= 2; $x = $x + 0.05) {
                    if ($x > $name) {
                        if (in_array($x, $a)) {
                            $dd["$x"] = $dd["$x"] + $amount;
                        } else {
                            $dd["$x"] = $amount;
                            array_push($a, $x);
                        }
                        break;
                    }
                }
            }
            if (! empty($dd)) {
                foreach ($dd as $key => $val) {
                    $ff[] = ['name' => ($key - .05)." to $key", 'amount' => round($val, 2)];
                }
                $result_arr = $ff;
            }
        }

        return new SuccessResource(['data' => $result_arr]);
    }

    public function postDownloadChart(Request $request)
    {
        if ($request->all()) {
            ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $lender = $request->lenders;
            $investor_filter = [$request->user()->id];
            $attribute = $request->attribute;
            $type = $request->graph_value;
            $userId = $request->user()->id;
            $subinvestors = [];
            $date_start = '2010-01-01';
            $date_end = date('Y-m-d');
            if (isset($request->date_start) && $request->date_start != '') {
                $date_start = $request->date_start;
            }
            if (isset($request->date_end) && $request->date_end != '') {
                $date_end = $request->date_end;
            }
            if (empty($permission)) {
                $investor = $this->role->allInvestors();
                $subadmininvestor = $investor->where('company', $userId);
                foreach ($subadmininvestor as $key1 => $value) {
                    $subinvestors[] = $value->id;
                }
            }
            $query987 = \MTB::getChartData($request->attribute, $request->graph_value, 2);
            if ($request->user()->hasRole('company')) {
                $query987 = $query987->whereIn('merchant_user.user_id', $subinvestors);
            }
            if (isset($request->label) && $request->label != '') {
                $query987 = $query987->where('merchants.label', $request->label);
            }
            if (isset($investor_filter) && $investor_filter) {
                $query987 = $query987->whereIn('merchant_user.user_id', $investor_filter);
            }
            if (isset($request->label) && $request->label != '') {
                $query987 = $query987->where('merchants.label', $request->label);
            }
            if ($request->date_start != '' || $request->date_end != '') {
                $query987 = $query987->whereBetween('merchants.date_funded', [$date_start, $date_end]);
            }
            $result_arr = ($query987->get())->toArray();
            if ($request->attribute == 8 || $request->attribute == 6 || $request->attribute == 5) {
                $header = [0 => ['Total', ''], 1 => ['Average', ''], 2 => ['', ''], 3 => ['Name', 'Amount']];
            } else {
                $header = [0 => ['Total', ''], 1 => ['Average', ''], 2 => ['', ''], 3 => ['', 'Name', 'Amount']];
            }
            $ff = [];
            if ($request->attribute == 6) {
                $dd = $a = [];
                foreach ($result_arr as $key => $val) {
                    $name = round($result_arr[$key]->name, 2);
                    $amount = $result_arr[$key]->amount;
                    for ($x = 0; $x <= 2; $x = $x + 0.05) {
                        if ($x > $name) {
                            if (in_array($x, $a)) {
                                $dd["$x"] = $dd["$x"] + $amount;
                            } else {
                                $dd["$x"] = $amount;
                                array_push($a, $x);
                            }
                            break;
                        }
                    }
                }
                if (! empty($dd)) {
                    foreach ($dd as $key => $val) {
                        $ff[] = ['name' => ($key - .05)." to $key", 'amount' => round($val, 2)];
                    }
                    $result_arr = $ff;
                }
            }
            array_unshift($result_arr, $header);
            $export = new Merchant_Graph($result_arr, count($result_arr), $request->attribute);

            return \Maatwebsite\Excel\Facades\Excel::download($export, 'merchant_graph.xlsx');
        }
    }

    public function postCheckTwoFactor(Request $request)
    {
        $investor_id = $request->user()->id;
        if ($request->user()->two_factor_secret) {
            return new SuccessResource(['two_factor' => 1]);
        } else {
            return new SuccessResource(['two_factor' => 0]);
        }
    }

    public function getUserIp()
    {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    public function postDisableTwoFactor(Request $request)
    {
        if ($request->user()->two_factor_secret) {
            app(DisableTwoFactorAuthentication::class)($request->user());
        }

        return new SuccessResource(['message' => 'You have disabled two factor authetication']);
    }

    public function postEnableTwoFactorDetails(Request $request)
    {
        app(EnableTwoFactorAuthentication::class)($request->user());
        if ($request->user()->two_factor_secret) {
            $two_factor_secret = $request->user()->two_factor_secret;
            $two_factor_recovery_codes = $request->user()->two_factor_recovery_codes;
            Session::put('two_factor_secret', $request->user()->two_factor_secret);
            Session::put('two_factor_recovery_codes', $request->user()->two_factor_recovery_codes);
            $qrcode = $request->user()->twoFactorQrCodeSvg();
            app(DisableTwoFactorAuthentication::class)($request->user());
        }
        if ($qrcode) {
            return new SuccessResource(['qr_code' => $qrcode, 'two_factor_secret' => $two_factor_secret, 'two_factor_recovery_codes' => $two_factor_recovery_codes]);
        } else {
            return new ErrorResource(['qr_code' => null, 'two_factor_secret' => null, 'two_factor_recovery_codes' => null]);
        }
    }

    public function postConnectPhone(Request $request)
    {
        try {
            $verify = app(TwoFactorAuthenticationProvider::class)->verify(decrypt($request->two_factor_secret), $request->code);
        } catch (Exception $e) {
            return new ErrorResource(['message' => $e->getMessage()]);
        }
        if ($verify) {
            $two_factor_secret = $request->two_factor_secret;
            $two_factor_recovery_codes = $request->two_factor_recovery_codes;
            $recovery_code = json_decode(decrypt($request->two_factor_recovery_codes));
            User::whereId($request->user()->id)->update(['two_factor_secret' => $two_factor_secret, 'two_factor_recovery_codes' => $two_factor_recovery_codes]);
            $message['title'] = "You've enabled two-step verification";
            $message['subject'] = "You've enabled two-step verification";
            $message['status'] = 'two_step_enabled_verification_notification';
            $message['to_mail'] = $request->user()->email;
            $message['email'] = $request->user()->email;
            $message['unqID'] = unqID();
            $email_template = Template::where([
                ['temp_code', '=', 'TWFEN'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, [$request->user()->email]);
                        $bcc_mails[] = $role_mails;    
                    }
                    $message['to_mail'] = Arr::flatten($bcc_mails);
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob); 
                }
            }

            return new SuccessResource(['message' => 'success', 'recovery_code' => $recovery_code]);
        } else {
            return new ErrorResource(['message' => 'Invalid code', 'recovery_code' => null]);
        }
    }

    public function postTransactionReport11(Request $request)
    {
        $data = $request->all();
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        $sort = $request->input('sort_order');
        $sort_by = $request->input('sort_by');
        $keyword = $request->input('keyword');
        $isExport = $request->input('is_export', false);
        $sort_order = null;
        if ($sort != null) {
            $sort_order = ($sort == 1) ? 'ASC' : 'DESC';
        }
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $investor_id = $request->user()->id;
        $categories = \ITran::getAllOptions();
        $tran_method = InvestorTransaction::transactionMethodOptions();
        $tran_type = InvestorTransaction::transactionTypeOptions();
        $category_arr = [];
        $tran_type_arr = [];
        $data = AllTransactionsView::where('user_id', $request->user()->id)->select('id', 'amount', 'user_id as investor_id', 'category_notes', 'transaction_category', 'transaction_method', 'transaction_type', 'account_no', 'date')->where(function ($q) {
            $q->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing)->orWhere('ach_request_status', null);
        });
        if ($request->sDate) {
            $data->where('date', '>=', $request->sDate);
        }
        if ($request->eDate) {
            $data->where('date', '<=', $request->eDate);
        }
        if ($request->account_no) {
            $data->where('account_no', 'like', "%{$request->account_no}%");
        }
        $data = $data->orderByDesc(DB::raw('date'))->get();
        if ($keyword != null) {
            foreach ($categories as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $category_arr[] = $key;
                }
            }
            foreach ($tran_type as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $tran_type_arr[] = $key;
                }
            }
        }
        foreach ($data as $key => $value) {
            if (isset($value->transaction_category)) {
                $data[$key]->transaction_category = isset($categories[$value->transaction_category]) ? $categories[$value->transaction_category] : '';
            } elseif (isset($value->transaction_type)) {
                if ($value->transaction_type == 'debit') {
                    $data[$key]->transaction_category = 'Pending to Bank';
                }
                if ($value->transaction_type == 'credit') {
                    $data[$key]->transaction_category = 'Pending to velocity';
                }
            } else {
                $data[$key]->transaction_category = null;
            }
            $data[$key]->TransactionMethod = isset($value->transaction_method) ? ($tran_method[$value->transaction_method]) : null;
            if (isset($value->transaction_type)) {
                if ($value->transaction_type == 'debit') {
                    $data[$key]->TransactionType = 'Credit';
                } elseif ($value->transaction_type == 'credit') {
                    $data[$key]->TransactionType = 'Debit';
                } else {
                    $data[$key]->TransactionType = $tran_type[$value->transaction_type];
                }
            } else {
                $data[$key]->TransactionType = null;
            }
            $type_sort_col[] = $data[$key]->TransactionType;
        }
        $data = $data->toArray();
        $user_details = UserDetails::where('user_id', $this->user->id)->first();
        $Bank = Bank::whereinvestor_id($investor_id)->where('default_debit', 1)->first();
        $default_credit_bank = Bank::whereinvestor_id($investor_id)->where('default_credit', 1)->first();
        $bank_account_no = '';
        if ($Bank) {
            $bank_account_no = $Bank->acc_number;
        }
        $default_credit_bank_acc_number = ($default_credit_bank) ? $default_credit_bank->acc_number : '';
        $recurrence_types = [['value' => '1', 'label' => 'Weekly'], ['value' => '2', 'label' => 'Monthly'], ['value' => '3', 'label' => 'Daily'], ['value' => '4', 'label' => 'On Demand']];

        return new SuccessResource(['data' => ['liquidity' => number_format($user_details->liquidity, 2), 'bank_account_no' => $bank_account_no, 'recurrence_types' => (array) $recurrence_types, 'data' => $data, 'notification_recurence' => $this->user->notification_recurence, 'default_credit_account_number' => $default_credit_bank_acc_number, 'download-url' => url('api/investor/download/investment-transaction-report?token='.$this->user->getDownloadToken())]]);
    }

    public function postTransactionReport(Request $request)
    {
        $data = $request->all();
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        $sort = $request->input('sort_order');
        $sort_by = $request->input('sort_by');
        $keyword = $request->input('keyword');
        $isExport = $request->input('is_export', false);
        $sort_order = null;
        if ($sort != null) {
            $sort_order = ($sort == 1) ? 'ASC' : 'DESC';
        }
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $investor_id = $request->user()->id;
        $categories = \ITran::getAllOptions();
        $tran_method = InvestorTransaction::transactionMethodOptions();
        $tran_type = InvestorTransaction::transactionTypeOptions();
        $category_arr = [];
        $tran_type_arr = [];
        $data = InvestorAchTransactionView::where('investor_id', $request->user()->id)->select('id', 'amount', 'investor_id', 'category_notes', 'transaction_category', 'transaction_method', 'transaction_type','account_no', 'date');
        if ($request->sDate) {
            $data->where('date', '>=', $request->sDate);
        }
        if ($request->eDate) {
            $data->where('date', '<=', $request->eDate);
        }
        if ($request->account_no) {
            $data->where('account_no', 'like', "%{$request->account_no}%");
        }
        if ($sort_by == null) {
            $data = $data->orderByRaw('date DESC,id DESC');
        }
        if ($sort_by != null && $sort_order != null) {
            switch ($sort_by) {
                case 'amount':
                    $data->orderBy('amount', $sort_order);
                    break;
                case 'account_no':
                    $data->orderBy('account_no', $sort_order);
                    break;
                case 'date':
                    $data->orderBy('date', $sort_order);
                    break;
                default:
                    break;
            }
        }
        if ($keyword != null) {
            foreach ($categories as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $category_arr[] = $key;
                }
            }
            foreach ($tran_type as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $tran_type_arr[] = $key;
                }
            }
            $data = $data->where(function ($q) use ($keyword, $category_arr, $tran_type_arr) {
                $num_keyword = str_replace('$', '', $keyword);
                $q->where(DB::raw('amount'), 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere(DB::raw("DATE_FORMAT(`date`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%')
                //->orWhere('account_no', 'LIKE', '%'.$keyword.'%')
                ->orWhereIn('transaction_category', $category_arr)->orWhereIn('transaction_type', $tran_type_arr);
            });
        }
        $dataTotal = $data;
        $total_amount = array_sum(array_column($dataTotal->get()->toArray(), 'amount'));
        $Count = $data;
        $total_page = count($Count->get()->toArray());
        $data = $data->get();
        foreach ($data as $key => $value) {
            $method_sort_col[] = isset($value->transaction_method) ? ($tran_method[$value->transaction_method]) : null;
            $data[$key]->amount = FFM::dollar($value->amount);
            $data[$key]->date = FFM::date($value->date);
            if ($value->account_no != '') {
                $data[$key]->account_no = FFM::mask_cc($value->account_no);
            }
            if (isset($value->transaction_category)) {
                $data[$key]->transaction_category = isset($categories[$value->transaction_category]) ? $categories[$value->transaction_category] : '';
            } else {
                $data[$key]->transaction_category = null;
            }
            $category_sort_col[] = $data[$key]->transaction_category;
            $data[$key]->TransactionMethod = isset($value->transaction_method) ? ($tran_method[$value->transaction_method]) : null;
            if (isset($value->transaction_type)) {
                if ($value->transaction_type == 'debit') {
                    $data[$key]->TransactionType = 'Credit';
                } elseif ($value->transaction_type == 'credit') {
                    $data[$key]->TransactionType = 'Debit';
                } else {
                    $data[$key]->TransactionType = $tran_type[$value->transaction_type];
                }
            } else {
                $data[$key]->TransactionType = null;
            }
            $type_sort_col[] = $data[$key]->TransactionType;
        }
        $data = $data->toArray();
        if (count($data) > 0) {
            if ($sort_by != null && $sort_order != null) {
                switch ($sort_by) {
                    case 'category':
                        $sort_order = ($sort == 1) ? SORT_ASC : SORT_DESC;
                        array_multisort($category_sort_col, $sort_order, $data);
                        break;
                    case 'method':
                        $sort_order = ($sort == 1) ? SORT_ASC : SORT_DESC;
                        array_multisort($method_sort_col, $sort_order, $data);
                        break;
                    case 'type':
                        $sort_order = ($sort == 1) ? SORT_ASC : SORT_DESC;
                        array_multisort($type_sort_col, $sort_order, $data);
                        break;
                }
            }
        }
        if ($isExport != 'yes') {
            $data = array_slice($data, $offset, $limit);
        }
        $dataTotal = $dataTotal->first();
        if ($dataTotal) {
            $dataTotal->amount = FFM::dollar($total_amount);
        }
        $from = ($total_page != 0) ? $offset + 1 : 0;
        $to = ($total_page != 0) ? ($offset + count($data)) : 0;
        $current_page = ($total_page != 0) ? (int) ($offset / $limit) + 1 : 0;
        $no_of_pages = (int) ($total_page / $limit);
        if (($total_page % $limit) > 0) {
            $no_of_pages = $no_of_pages + 1;
        }
        $user_details = UserDetails::where('user_id', $this->user->id)->first();
        $pagination = ['from' => $from, 'to' => $to, 'current_page' => $current_page, 'last_page' => $no_of_pages, 'total' => $total_page];
        $Bank = Bank::whereinvestor_id($investor_id)->where('default_debit', 1)->first();
        $default_credit_bank = Bank::whereinvestor_id($investor_id)->where('default_credit', 1)->first();
        $bank_account_no = '';
        if ($Bank) {
            $bank_account_no = $Bank->acc_number;
        }
        $default_credit_bank_acc_number = ($default_credit_bank) ? $default_credit_bank->acc_number : '';
        $recurrence_types = [['value' => '1', 'label' => 'Weekly'], ['value' => '2', 'label' => 'Monthly'], ['value' => '3', 'label' => 'Daily'], ['value' => '4', 'label' => 'On Demand']];

        $pending_status = InvestorAchRequest::AchRequestStatusProcessing;
        $pending_amount = InvestorAchRequest::where('investor_id',$investor_id)->where('ach_request_status',$pending_status)->sum('amount'); 
        $available_liquidity = $user_details->liquidity-$pending_amount;    
        return new SuccessResource(['data' => ['liquidity' => number_format($user_details->liquidity, 2),'pending_amount'=>number_format($pending_amount, 2) ,'available_liquidity'=>number_format($available_liquidity,2), 'bank_account_no' => ($bank_account_no != null) ? FFM::mask_cc($bank_account_no) : null, 'recurrence_types' => (array) $recurrence_types, 'data' => $data, 'total' => $dataTotal, 'pagination' => $pagination, 'notification_recurence' => $this->user->notification_recurence, 'default_credit_account_number' => ($default_credit_bank_acc_number != null) ? FFM::mask_cc($default_credit_bank_acc_number) : null, 'download-url' => url('api/investor/download/investment-transaction-report?token='.$this->user->getDownloadToken())]]);
    }

    public function postAdvancePlusInvestments(Request $request)
    {
        return new SuccessResource(['data' => InvestmentReportHelper::AdvancePlusInvestments($request)]);
    }
}
