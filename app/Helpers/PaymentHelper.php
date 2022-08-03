<?php

namespace App\Helpers;

use App\AchRequest;
use App\CompanyAmount;
use App\Exports\Data_arrExport;
use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Jobs\PaymentCreateCRM;
use App\Library\Facades\InvestorHelper;
use App\Library\Facades\Permissions;
use App\Library\Helpers\InvestorTableBuilder;
use App\Library\Repository\Interfaces\IInvestorRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IMNotesRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Merchant;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\Models\InvestorAchRequest;
use App\Models\Message;
use App\Models\Views\InvestorAchRequestView;
use App\Models\Views\MerchantUserView;
use App\Models\Views\MerchantView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Providers\DashboardServiceProvider;
use App\Rcode;
use App\Settings;
use App\SubStatus;
use App\Template;
use App\TermPaymentDate;
use App\User;
use App\UserDetails;
use App\VelocityFee;
use Carbon\Carbon;
use Exception;
use FFM;
use GPH;
use Hashids\Hashids;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use MTB;
use PayCalc;
use Yajra\DataTables\DataTables;
use App\ReserveLiquidity;
use Illuminate\Support\Facades\Schema;


class PaymentHelper
{
    use CreditCardStripe;

    public function __construct(IParticipantPaymentRepository $participantPayment, IRoleRepository $role, IMerchantRepository $merchant, IMNotesRepository $mNotes, IInvestorRepository $investor)
    {
        $this->table = new ParticipentPayment();
        $this->participantPayment = $participantPayment;
        $this->role = $role;
        $this->mNotes = $mNotes;
        $this->merchant = $merchant;
        $this->investor = $investor;
        if(Schema::hasTable('settings')){
        $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }

    /**
     * Display a listing of the Open items in payments.
     *
     * @param  Yajra\DataTables\Html\Builder $tableBuilder
     * @return \Illuminate\Http\Response
     */
    public function openItems($tableBuilder)
    {
        $page_title = 'Open Items';
        $tableBuilder->ajax(route('admin::payments::openitems'));
        $tableBuilder = $tableBuilder->columns(MTB::getOpenItemsForAdmin(true));
        return [
            'tableBuilder' => $tableBuilder,
            'page_title' => $page_title
        ];
    }

    /**
     * Calculate net payment for lender payment generation.
     *
     * @param  \Illuminate\Http\Request  $list
     * @return \Illuminate\Http\Response
     */
    public function netPaymentAll($list)
    {
        foreach ($list as $key => $value) {
            $merchant_id = $value['merchant_id'];
            $rate        = $value['rate'];
            $length      = $value['length'] ?? 1;
            $merchant    = Merchant::where('id', $merchant_id)->first();
            if ($rate) {
                $payment_amount = $rate / $length;
            } else {
                $payment_amount = $merchant->payment_amount;
            }
            $debit_status = 0;
            $net_payment_status = 0;

            $user_ids = User::join('merchant_user', 'merchant_user.user_id', 'users.id')->where('status', 1)->where('merchant_id', $merchant_id)->pluck('users.id')->toArray();

            $result = $this->ParticipantShareData($merchant_id, $payment_amount, $debit_status, $user_ids, $net_payment_status);
            $net_payments[] = round($result['net_amount'], 2);
        }

        return $net_payments;
    }

    /**
     * Calculate without  net payment for lender payment generation.
     *
     * @param  \Illuminate\Http\Request  $list
     * @return \Illuminate\Http\Response
     */
    public function netPayment($list)
    {

        foreach ($list as $value) {
            $merchant_id = $value['merchant_id'];
            $rate        = $value['rate'];
            $length      = $value['length'];
            if ($rate) {
                $net_payment[] = floatval($rate / $length);
            } else {
                $merchant = Merchant::where('id', $merchant_id)->first();
                $net_payment[] = floatval($merchant->payment_amount);
            }
        }

        return $net_payment;
    }

    /**
     * Calculate participant share data.
     *
     * @param  
     * @return \Illuminate\Http\Response
     */
    public function ParticipantShareData($merchant_id, $actual_payment, $debit_status, $user_ids, $net_payment_status)
    {
        $agent_fee_id   = User::AgentFeeId();
        $overpayment_id = User::OverpaymentId();
        if (!empty($user_ids)) {
            if ($agent_fee_id) {
                $user_ids = array_flip($user_ids);
                unset($user_ids[$agent_fee_id]);
                $user_ids = array_flip($user_ids);
            }
            if ($overpayment_id) {
                $user_ids = array_flip($user_ids);
                unset($user_ids[$overpayment_id]);
                $user_ids = array_flip($user_ids);
            }
        }
        if ($debit_status) {
            $actual_payment = -$actual_payment;
        }
        $max_participant_fund_per = MerchantUserView::select(DB::raw('(funded/sum(amount)) as max_participant_fund_per'))->where('merchant_id', $merchant_id)->first()->max_participant_fund_per;
        $payment = $actual_payment / $max_participant_fund_per;
        $agent_fee_per = DB::table('settings')->where('id', 1)->value('agent_fee_per');
        $agent_fee_status = DB::table('merchants')->where('id', $merchant_id)->value('agent_fee_applied');
        $Merchant = Merchant::where('merchants.id', $merchant_id)->select('funded', 'rtr', 'factor_rate', 'date_funded', 'commission', 'pmnts', 'payment_amount', 'sub_statuses.name as substatus_name', 'complete_percentage', 'merchants.id', 'merchants.name as name', 'advance_type', 'merchants.sub_status_id', 'max_participant_fund')->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->first();
        $funded_amount = $Merchant->funded;
        $selctedMerchantUser = MerchantUser::where('merchant_id', $merchant_id);
        if (!empty($user_ids)) {
            $selctedMerchantUser = $selctedMerchantUser->whereIn('merchant_user.user_id', $user_ids);
        }
        $selectedInvestorsFunded = $selctedMerchantUser->sum(DB::raw('(amount*(100-(mgmnt_fee))/100)'));
        $selectedInvestorsFee = $selctedMerchantUser->sum(DB::raw('((' . $payment . '*mgmnt_fee)/100)'));
        $total_funded = MerchantUser::where('merchant_id', $merchant_id)->whereIn('merchant_user.status', [1, 3])->sum(DB::raw('(amount*(100-(mgmnt_fee))/100)'));
        if ($net_payment_status) {
            $payment = $payment * ($funded_amount / $selectedInvestorsFunded);
            $payment = $payment * ($selectedInvestorsFunded / $total_funded);
            // $payment = $payment*100/(100-agent_fee_per);
        } else {
            $payment = $payment;
        }
        $payment = round($payment, 2);
        $result['data']                     = [];
        $result['total_payment_amount']     = 0;
        $result['OverPayment']              = 0;
        $result['total_fee_amount']         = 0;
        $result['to_participant']           = 0;
        $result['payment']                  = 0;
        $result['net_amount']               = 0;
        $result['agent_fee']                = 0;
        $result['max_participant_fund_per'] = 0;
        $data = [];
        $total_payment_amount = $OverPayment = $total_payment_amount = $participant = $to_participant = $total_fee_amount = 0;
        $decimal_count = 2;
        if (!empty($user_ids)) {
            $MerchantUser = MerchantUserView::whereIn('investor_id', $user_ids);
            $MerchantUser = $MerchantUser->where('merchant_id', $merchant_id);
            $MerchantUser = $MerchantUser->where('investor_id', '!=', $overpayment_id);
            $MerchantUser = $MerchantUser->whereIn('status', [1, 3]);
            $MerchantUser = $MerchantUser->select('id', 'investor_id as user_id', 'Investor', 'mgmnt_fee', 'invest_rtr', DB::raw('-user_balance_amount as balance'));
            $MerchantUser = $MerchantUser->get();
            $MerchantUserBalance = DB::table('merchant_user_views')->where('merchant_id', $merchant_id)->whereIn('investor_id', $user_ids)->sum('user_balance_amount');
            $MerchantUserBalance += $payment;
            $data = GPH::InvestorPaymentShare($merchant_id, $MerchantUser, $actual_payment, $overpayment_id);
            foreach ($MerchantUser as $key => $singleMerchantUser) {
                if(isset($data[$singleMerchantUser->user_id])){
                    $data[$singleMerchantUser->user_id]['Name'] = $singleMerchantUser->Investor;
                    $sharePayment = $data[$singleMerchantUser->user_id]['participant_share'];
                    if ($agent_fee_status == 1 && !$net_payment_status) {
                        $sharePayment = $sharePayment - ($sharePayment * ($agent_fee_per / 100));
                    }
                    $sharePayment   = round($sharePayment, $decimal_count);
                    $management_fee = round(PayCalc::calculateMgmntFee($sharePayment, $singleMerchantUser->mgmnt_fee), $decimal_count);
                    $data[$singleMerchantUser->user_id]['payment']        = $sharePayment;
                    $data[$singleMerchantUser->user_id]['management_fee'] = $management_fee;
                }
            }
            if(isset($data[$overpayment_id])){
                $data[$overpayment_id]['Name'] = 'OverPayment';
                $sharePayment = $data[$overpayment_id]['participant_share'];
                if ($agent_fee_status == 1 && !$net_payment_status) {
                    $sharePayment = $sharePayment - ($sharePayment * ($agent_fee_per / 100));
                }
                $sharePayment   = round($sharePayment, $decimal_count);
                $management_fee = 0;
                $data[$overpayment_id]['payment']        = $sharePayment;
                $data[$overpayment_id]['management_fee'] = $management_fee;
            }
            $participant = array_sum(array_column($data, 'payment'));
            $agent_fee = $payment * ($agent_fee_per / 100);
            if ($agent_fee_status == 1 && !$net_payment_status) {
                $payment = $payment - ($payment * ($agent_fee_per / 100));
            }
            $OverPayment = round($payment - $participant, 2);
            if ($MerchantUserBalance <= 0) {
                $OverPayment = 0;
            }
            if ($OverPayment) {
                $single['Name']           = 'Overpayment';
                $single['balance']        = 0;
                $single['payment']        = $OverPayment;
                $single['management_fee'] = 0;
                $data[] = $single;
            }
            $total_payment_amount = array_sum(array_column($data, 'payment'));
            $total_fee_amount     = array_sum(array_column($data, 'management_fee'));
            $to_participant       = round($total_payment_amount - $total_fee_amount, $decimal_count);
            $result['data']                     = $data;
            $result['total_payment_amount']     = $total_payment_amount;
            $result['OverPayment']              = $OverPayment;
            $result['total_fee_amount']         = $total_fee_amount;
            $result['to_participant']           = $to_participant;
            $result['net_amount']               = $to_participant;
            $result['payment']                  = $payment;
            $result['agent_fee']                = $agent_fee;
            $result['max_participant_fund_per'] = $max_participant_fund_per;
        }
        return $result;
    }

    /**
     * DebitPaymentLimit function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function debitPaymentLimit($request)
    {
        $merchantId = $request->merchant_id;
        $weekly_payment = $request->weekly_payment;
        $daily_payment = $request->daily_payment;
        $msg = '';
        $ctd = ParticipentPayment::where('merchant_id', $merchantId)->sum('payment');
        if ($weekly_payment) {
            if ($ctd < $weekly_payment) {
                $msg = "Can't deduct more than the RTR amount paid by the merchant";
                return $msg;
            }
        }
        if ($daily_payment) {
            if ($ctd < $daily_payment) {
                $msg = "Can't deduct more than the RTR amount paid by the merchant";
                return $msg;
            }
        }
    }

    /**
     * Lender payment check function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function lenderPaymentCheck($request)
    {
        $array = [];
        $details = $request->all();

        $investorId = [];
        if ($details['company']) {
            $investorId = DB::table('users')->where('company', $details['company'])->pluck('id');
        }
        $i = 0;
        $html = '';
        $error = [];
        $error_type = 0;
        foreach ($details['merchant'] as $data) {
            if (isset($data['select_merchant'])) {
                $payment_date = isset($data['payment_date']) ? $data['payment_date'] : [];
                $payment_date_array = explode(',', $payment_date);
                if (!empty($payment_date_array)) {
                    foreach ($payment_date_array as $key => $value) {
                        $payment_date_array[$key] = $value;
                        $merchant = Merchant::where('id', $data['select_merchant'])->first();
                        $last_payment_date = isset($data['last_payment_date']) ? $data['last_payment_date'] : $merchant->date_funded;
                        $amount = isset($data['amount']) ? $data['amount'] : 0;
                        if ($amount <= 0 && $data['rcode'] == 0) {
                            $error[] = $data['name'];
                            $error_type = 1;
                        }

                        if ($last_payment_date > $payment_date_array[$key]) {
                            $error[] = $data['name'];
                            $error_type = 2;
                        }
                    }
                    $error = array_unique($error);
                }
            } else {
            }
        }


        if (!empty($error)) {
            return [
                'status' => 2,
                'result' => $error,
                'error_type' => $error_type
            ];
        } else {
            foreach ($details['merchant'] as $data) {
                if (isset($data['select_merchant'])) {
                    $merchantId = $data['select_merchant'];
                    if (isset($data['debit'])) {
                        if ($data['debit'] === 'yes') {
                            $payment_type = 0;
                        }
                    } else {
                        $payment_type = 1;
                    }
                    $payment_date_array = explode(',', $data['payment_date']);
                    foreach ($payment_date_array as $key => $value) {
                        $payment_date_array[$key] = $value;
                    }
                    $payment = ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->whereIn('participent_payments.payment_date', $payment_date_array);
                    $payment_count = clone $payment;
                    $payment_count = $payment_count->where('is_payment', 1)->count();
                    $payments = $payment->join('payment_investors', 'participent_payments.id', 'payment_investors.participent_payment_id')->join('merchants', 'merchants.id', 'participent_payments.merchant_id')->groupBy('participent_payments.merchant_id');
                    if ($investorId) {
                        $payments->whereIn('payment_investors.user_id', $investorId);
                    }
                    $paymentCheck = $payments->select('participent_payments.merchant_id', 'merchants.name', 'payment_date', 'payment')->first();
                    if ($paymentCheck) {
                        $merchant_name = Merchant::where('id', $merchantId)->value('name');
                        $html .= '<div style="padding-left:10px">There is a payment by <b>' . strtoupper($merchant_name);
                        $html .= '</b> on ' . FFM::date($paymentCheck['payment_date']) . ' is already (' . $payment_count . ') there.</div>';
                        $array[$i]['merchant_id'] = $merchant_name;
                        $array[$i]['payment_date'] = $paymentCheck['payment_date'];
                        $i++;
                    }
                }
            }
            if (count($array) > 0) {
                return ['status' => 1, 'result' => $html];
            } else {
                return ['status' => 0];
            }
        }
    }

    /**
     * Payment check function.
     *
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function paymentCheck($request)
    {
        $merchantId = $request->merchantId;
        $paymentDate = $request->paymentDate;
        $investorId = $request->investor_id;
        $dateArray = explode(',', $paymentDate);
        $debit_status = $request->debit_status;
        if ($debit_status === 'yes') {
            $payment_type = 0;
        } else {
            $payment_type = 1;
        }
        $msg = '';
        if (!empty($dateArray)) {
            foreach ($dateArray as $key => $date) {
                $payment_count = ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->where('participent_payments.is_payment', 1)->where('participent_payments.payment_date', $date)->count();
                $date_format = Carbon::parse($date)->format(FFM::defaultDateFormat('db'));
                if ($payment_count > 0) {
                    $msg .= $date_format . ' <b>(' . $payment_count . ') </b>';
                }
            }
        }
        $payment = ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->where('participent_payments.is_payment', 1)->whereIn('participent_payments.payment_date', $dateArray);
        $payments = $payment->join('merchants', 'merchants.id', 'participent_payments.merchant_id')->join('payment_investors', 'participent_payments.id', 'payment_investors.participent_payment_id')->join('users', 'users.id', 'payment_investors.user_id');
        if ($investorId) {
            $payments->whereIn('payment_investors.user_id', $investorId);
        }
        $paymentCheck = $payments->select('participent_payments.merchant_id', 'merchants.name', 'payment_date', 'payment', 'users.name as investor_name');
        if ($paymentCheck->count() != 0) {
            $data = $payments->get();

            return ['status' => 1, 'result' => $data, 'msg' => $msg];
        } else {
            return ['status' => 0];
        }
    }

    /**
     * Create payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function create($request, $tableBuilder, $merchant_id)
    {
        $error = $permission_error = false;
        $result = [];
        $message = '';
        $check = MerchantUser::whereIn('merchant_user.status', [1, 3])->where('merchant_id', $merchant_id)->count();
        if ($check == 0) {
            $error = true;
            $message = 'merchant have no assigned investors';
            return [
                'error' => $error,
                'permission_error' => $permission_error,
                'result' => $result,
                'message' => $message
            ];
        }
        $check = MerchantUser::whereIn('merchant_user.status', [1, 3])->where('merchant_id', $merchant_id)->sum('amount');
        if ($check == 0) {
            $error = true;
            $message = 'merchant have no assigned investors';
            return [
                'error' => $error,
                'permission_error' => $permission_error,
                'result' => $result,
                'message' => $message
            ];
        }
        $merchant = Merchant::select('sub_status_id', 'label', 'rtr')->where('id', $merchant_id)->first();
        $MerchantUser = MerchantUser::where('merchant_id', $merchant_id)->get();
        $MerchantView = MerchantView::find($merchant_id);
        $ParticipentPayment = ParticipentPayment::where('merchant_id', $merchant_id)->orderByDesc('payment_date')->get();

        $mode = Settings::where('keys', 'collection_default_mode')->value('values');

        if ($mode == 0) {
            if (in_array($merchant->sub_status_id, [18, 19, 20, 4, 22])) {
                $error = true;
                $message = 'Please change the merchant status to Collection before you add payment.';
                return [
                    'error' => $error,
                    'permission_error' => $permission_error,
                    'result' => $result,
                    'message' => $message
                ];
            }
        }

        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        if ($merchant_id == 0) {
            $merchants = Merchant::get();
            $investors = $this->role->allInvestors();
        } else {
            $overpayment_id = User::OverpaymentId();
            $agent_fee_id   = User::AgentFeeId();
            $this_merchants = Merchant::whereHas('investments', function ($query) use ($merchant_id) {
                $query->where('merchant_user.merchant_id', $merchant_id)->where('merchant_user.status', 1);
            })->with(['investments' => function ($query) use ($merchant_id, $overpayment_id, $agent_fee_id) {
                $query->where('merchant_user.merchant_id', $merchant_id);
                $query->whereIn('merchant_user.status', [1, 3]);
                if ($overpayment_id) {
                    $query->where('merchant_user.user_id', '!=', $overpayment_id);
                }
                if ($agent_fee_id) {
                    $query->where('merchant_user.user_id', '!=', $agent_fee_id);
                }
            }])->first();
            $payment = $this_merchants->payment_amount;

            $payment_total = ParticipentPayment::where('merchant_id', $merchant_id)->where('is_payment', 1)->sum('payment');
            $merchant_balance = $merchant->rtr - $payment_total;

            $processing_ach_payments = TermPaymentDate::where('merchant_id', $merchant_id)->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');
            $balance_final = $merchant_balance - $processing_ach_payments;
            if ($balance_final < $payment) {
                if ($balance_final <= 0) {
                    $payment = null;
                } else {
                    $payment = sprintf('%.2f', $balance_final);
                }
            }
            if (isset($this_merchants->investments)) {
                $investors = ($this_merchants->investors->where('amount','!=',0));
            } else {
                $error = true;
                $message = 'Assign an investor before you create payment';
                return [
                    'error' => $error,
                    'permission_error' => $permission_error,
                    'result' => $result,
                    'message' => $message
                ];
            }
            $merchants = Merchant::whereHas('investments', function ($query) use ($merchant_id) {
                $query->where('merchant_user.merchant_id', $merchant_id);
            })->with('investments')->get();
        }
        if (empty($permission)) {
            $merchantAccess = Merchant::where('id', $merchant_id)->where('creator_id', $userId)->first();
            if (empty($merchantAccess)) {
                $permission_error = $error = true;
                $message = 'permission_denied';
                return [
                    'error' => $error,
                    'permission_error' => $permission_error,
                    'result' => $result,
                    'message' => $message
                ];
            }
        }
        $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0] = 'All';
        $companies = array_reverse($companies, true);
        $rcodes = DB::table('rcode')->pluck(DB::raw("CONCAT(description,' (',code,') ') AS name"), 'id');
        $tableBuilder->ajax(['method' => 'post', 'url' => route('admin::payments::shareCheck'), 'data' => 'function(d){
                d._token      = "' . csrf_token() . '";
                d.user_id     = $("#user_id").val();
                d.merchant_id = ' . $merchant_id . ';
                d.net_payment = $("input[name=net_payment]:checked").val();
                d.debit       = $("input[name=debit]:checked").val();
                d.payment     = $("#payment").val();
            }']);
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){
                var n=this.api(),o=table.ajax.json();
                $(n.column(1).footer()).html("Total");
                $(n.column(2).footer()).html(o.total);
                $(n.column(3).footer()).html(o.Fee);
                $("#ToParticipantDisplay").text(o.ToParticipant);
                $("#OverPaymentDisplay").text(o.OverPayment);
                $("#AgentFee").text(o.AgentFee);
                $("#PaymentDisplay").text(o.payment);
            }', 'scrollY' => '22vh', 'searching' => false, 'paging' => false, 'bInfo' => false]);
        $tableBuilder = $tableBuilder->columns([['data' => 'Name', 'name' => 'Name', 'title' => 'Name', 'width' => '60%'], ['data' => 'balance', 'name' => 'balance', 'title' => 'Balance', 'class' => 'text-right'], ['data' => 'payment', 'name' => 'payment', 'title' => 'Payment', 'class' => 'text-right'], ['data' => 'management_fee', 'name' => 'management_fee', 'title' => 'Fee', 'class' => 'text-right']]);
        $merchant = Merchant::where('id', $merchant_id)->first();
        $ach_investor = (Settings::where('keys', 'ach_investor')->value('values'));
        $ach_investor = json_decode($ach_investor, true);
        $ach_syndicate_payment_time = $ach_investor['ach_syndicate_payment_time'] ?? '16:50';
        $curerent_time = Carbon::now()->tz('America/New_York');
        $cutoff_time = Carbon::createFromFormat('H:i', $ach_syndicate_payment_time, 'America/New_york');
        if ($curerent_time->greaterThan($cutoff_time->clone()->subMinutes(20))) {
            $cut_off_time = $cutoff_time->format('h:i A');
            $syndication_message = "Payment added after $cut_off_time will not be reflected in the Syndicate ACH payment.";
            $request->session()->flash('success', $syndication_message);
        }
        $result = [
            'merchants' => $merchants,
            'investors' => $investors,
            'merchant_id' => $merchant_id,
            'this_merchants' => $this_merchants,
            'companies' => $companies,
            'rcodes' => $rcodes,
            'payment' => $payment,
            'MerchantView' => $MerchantView,
            'MerchantUser' => $MerchantUser,
            'ParticipentPayment' => $ParticipentPayment,
            'tableBuilder' => $tableBuilder,
            'merchant' => $merchant
        ];

        return [
            'error' => $error,
            'permission_error' => $permission_error,
            'result' => $result,
            'message' => $message
        ];
    }

    /**
     * Share check data for datatable in create payment for merchant.
     *
     * @param  $request
     * @return \Illuminate\Http\Response
     */
    public function shareCheck($request)
    {
        $payment = $request->payment;
        if (!is_numeric($payment)) {
            $payment = 0;
        }
        $merchant_id = $request->merchant_id;
        $debit_status = $request->debit ?? 0;
        $user_id = $request->user_id;
        $net_payment_status = $request->net_payment ?? 0;
        $result = $this->ParticipantShareData($merchant_id, $payment, $debit_status, $user_id, $net_payment_status);
        $data = $result['data'] ?? [];
        $decimal_count = 2;
        return DataTables::of($data)
        ->editColumn('balance', function ($row) use ($decimal_count) {
            return '$' . number_format($row['balance'], 2);
        })
        ->editColumn('payment', function ($row) use ($decimal_count) {
            return '$' . number_format($row['payment'], $decimal_count);
        })
        ->editColumn('management_fee', function ($row) use ($decimal_count) {
            return '$' . number_format($row['management_fee'], $decimal_count);
        })
        ->rawColumns([])
        ->addIndexColumn()
        ->with('total', '$' . number_format($result['total_payment_amount'], $decimal_count))
        ->with('OverPayment', '$' . number_format($result['OverPayment'], $decimal_count))
        ->with('payment', '$' . number_format($result['payment'] * $result['max_participant_fund_per'], $decimal_count))
        ->with('ToParticipant', '$' . number_format($result['to_participant'], $decimal_count))
        ->with('AgentFee', '$' . number_format($result['agent_fee'], $decimal_count))
        ->with('Fee', '$' . number_format($result['total_fee_amount'], $decimal_count))
        ->make(true);
    }

    /**
     * Net amount calculation in create payment for merchant.
     *
     * @param  $request
     * @return \Illuminate\Http\Response
     */
    public function NetAmountCalculation($request)
    {
        $payment = $request->payment;
        if (!is_numeric($payment)) {
            $payment = 0;
        }
        $merchant_id = $request->merchant_id;
        $debit_status = $request->debit ?? 0;
        $user_ids = $request->investor_id;
        $net_payment_status = $request->net_payment ?? 0;
        $result = $this->ParticipantShareData($merchant_id, $payment, $debit_status, $user_ids, $net_payment_status);
        return $result;
    }

    /**
     * Create payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function store($request)
    {
        $status = false;
        $message = '';
        $result = [];
        try {
            $settings = Settings::select('send_permission')->first();
            $send_permission = $settings->send_permission;
            if ($request->rcode != '') {
                $request->payment = 0;
            }
            $merchant_id = $request->merchant_id;
            $merchant = DB::table('merchants')->find($merchant_id);
            $maximum_particiapant_fund = $merchant->max_participant_fund;
            $is_payment_made = DB::table('payment_investors')->where('merchant_id', $merchant_id)->first();
            $total_invested = DB::table('merchant_user')->where('merchant_id', $merchant_id)->sum('amount');
            // if (empty($is_payment_made)) {
            //     $return_array = $this->company_amount_adjustment($merchant_id);
            //     if ($return_array) {
            //         throw new \Exception('Need to Modify Funded Amount for Merchant(Max Participant Share) / Investor(Funded Amount) ['.FFM::dollar($return_array).']', 1);
            //     }
            // }
            $total_invested = DB::table('merchant_user')->where('merchant_id', $merchant_id)->sum('amount');
            $balance_share = round($merchant->max_participant_fund - $total_invested);
            $dates_array = (explode(',', $request->payment_date));
            asort($dates_array);
            $payment = str_replace(',', '', $request->payment);
            $net_payment_status = $request->net_payment;
            $debit_status = $request->debit;
            $debit_reason = ($request->debit_reason) ? $request->debit_reason : '';
            $investor_id = $request->user_id;
            if (!$investor_id) {
                throw new Exception('Empty Investors', 1);
            }
            GPH::InvestmentAmountStoreToTransaction($merchant);
            $return_array = $this->merchant->generatePaymentForLender($merchant_id, $dates_array, $payment, $net_payment_status, $debit_status, $debit_reason, $investor_id, $request->rcode, 0, $send_permission);
            if ($return_array['result'] != 'success') {
                throw new Exception($return_array['result'] ?? 'Something Went Wrong', 1);
            }
            $merchant = Merchant::select('name', 'rtr', 'payment_amount', 'sub_status_id', 'merchants.id', 'pmnts', 'complete_percentage')->where('id', $merchant_id)->first();
            // GPH::DefaultToCollectionSubStatusChange($merchant);
            GPH::MerchantUpdate($merchant_id, $payment, $debit_status);
            GPH::UpdateMerchantAgentAppliedStatus($merchant_id);
            $userIds = DB::table('merchant_user')->where('merchant_id', $merchant_id)->pluck('user_id', 'user_id')->toArray();
            DashboardServiceProvider::addInvestorPaymentJob($userIds);
            $status = true;
            $message = count($dates_array) . ' payments added successfully';
            $result = ['id' => $merchant_id];
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message,
            'result' => $result
        ];
    }

    /**
     * Regenerate payment for merchant function.
     *
     * @param  $participent_payment_id
     * @param  $type
     * @return \Illuminate\Http\Response
     */
    public function reGeneratePayment($participent_payment_id, $type)
    {
        $status = false;
        $message = '';
        $result = [];
        try {
            $ParticipentPayment = ParticipentPayment::find($participent_payment_id);
            if (!$ParticipentPayment) {
                throw new Exception('Empty ParticipentPayment', 1);
            }
            $AfterParticipentPayment = ParticipentPayment::where('merchant_id', $ParticipentPayment->merchant_id);
            $AfterParticipentPayment = $AfterParticipentPayment->where('id', '>=', $ParticipentPayment->id);
            $AfterParticipentPayment = $AfterParticipentPayment->where('participent_payments.is_payment', 1);
            $AfterParticipentPayment = $AfterParticipentPayment->orderByRaw('id ASC');
            $AfterParticipentPayment = $AfterParticipentPayment->get();
            foreach ($AfterParticipentPayment as $key => $value) {
                $investor_ids = new PaymentInvestors();
                $investor_ids = $investor_ids->where('participent_payment_id', $value->id);
                $investor_ids = $investor_ids->where('participant_share', '!=', 0);
                $investor_ids = $investor_ids->pluck('user_id', 'user_id')->toArray();
                if (isset($investor_ids[504])) unset($investor_ids[504]);
                $single[$value->id] = $value->payment;
                $value->payment = 0;
                if (empty($investor_ids)) {
                    $investor_ids = GPH::getMerchantInvestorByMerchantId($value->merchant_id);
                }
                if ($type == 'All') {
                    $investor_ids = GPH::getMerchantInvestorByMerchantId($value->merchant_id);
                }
                $value->investor_ids = implode(',', $investor_ids);
                $value->save();
                PaymentInvestors::where('participent_payment_id', $value->id)->delete();
            }
            foreach ($AfterParticipentPayment as $key => $value) {
                $value->payment = $single[$value->id];
                $value->save();
                $return_array = GPH::reGeneratePayment($value->id);
                if ($return_array['result'] != 'success') {
                    throw new Exception($return_array['result'] ?? 'Something Went Wrong', 1);
                }
            }

            $user_ids = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->pluck('user_id', 'user_id')->toArray();
            DashboardServiceProvider::addInvestorPaymentJob($user_ids);

            $status = true;
            $message = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    public function company_amount_adjustment($merchant_id)
    {
        $Companies = User::groupBy('company')->where('company', '!=', 0)->pluck('company', 'company');
        foreach ($Companies as $company) {
            $invested_amount = MerchantUserView::where('merchant_id', $merchant_id)
                ->where('company', $company)
                ->sum('amount');
            $CompanyAmount = CompanyAmount::firstOrCreate([
                'merchant_id' => $merchant_id,
                'company_id' => $company,
            ]);
            $CompanyAmount->max_participant = $invested_amount;
            $CompanyAmount->save();
        }
        $total_invested_amount_company_wise = CompanyAmount::where('merchant_id', $merchant_id)->sum('max_participant');
        $Merchant = Merchant::find($merchant_id);
        $max_participant_fund = $Merchant->max_participant_fund;
        $fund_diff = $max_participant_fund - $total_invested_amount_company_wise;

        return $fund_diff;
    }

    /**
     * Revert payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function RevertPayment($request)
    {
        $status = false;
        $message = '';
        $result = [];
        try {
            $data = $request->all();
            $ParticipentPayment = ParticipentPayment::find($data['participent_payment_id']);
            if (!$ParticipentPayment) {
                throw new Exception('Couldnt Find the Payment', 1);
            }
           // $data['date'] = date('Y-m-d');
            if (!$data['date']) {
                throw new Exception('Please select Revert Date', 1);
            }
            $data['date']=date('Y-m-d',strtotime($data['date']));
            if($ParticipentPayment->payment_date>$data['date']){
                throw new Exception('Revert date should be greater than or equal to paid date');
            }
            
            $merchant_id = $ParticipentPayment->merchant_id;
            $participent_payment_id = $ParticipentPayment->id;
            $OverPaymentUser = MerchantUser::where('merchant_id', $merchant_id)->where('user_id', 504)->first();
            if ($OverPaymentUser) {
                if ($OverPaymentUser->paid_participant_ishare) {
                    $OverPayment = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->where('user_id', 504)->first();
                    if (!$OverPayment) {
                        throw new Exception('In order to revert this payment, you have to revert the Overpayment', 1);
                    }
                }
            }
            $ach_revert = AchRequest::where('revert_id', $participent_payment_id)->count();
            if ($ach_revert > 0) {
                throw new Exception('ACH revert initiated.', 1);
            }
            $RevertParticipentPayment = $ParticipentPayment->toArray();
            $RevertParticipentPayment['final_participant_share'] = -$RevertParticipentPayment['final_participant_share'];
            $RevertParticipentPayment['payment']                 = -$RevertParticipentPayment['payment'];
            $RevertParticipentPayment['payment_date']            = date('Y-m-d', strtotime($data['date']));
            $RevertParticipentPayment['reason']                  = $data['reason'] ?? '';
            if ($request->has('initiate_ach') && $request->initiate_ach) {
                $debit_ach_params = (object) [
                    'payment_amount'         => abs($RevertParticipentPayment['payment']),
                    'merchant_id'            => $merchant_id,
                    'payment_date'           => $RevertParticipentPayment['payment_date'],
                    'reason'                 => $RevertParticipentPayment['reason'],
                    'ip'                     => $request->ip(),
                    'participent_payment_id' => $participent_payment_id,
                ];
                $return_result = $this->makeMerchantACHDebitPayment($debit_ach_params);
                if ($return_result['status'] != true) {
                    throw new Exception($return_result['msg'], 1);
                } else {
                    $message = 'Successfully Added Revert Payment ACH';
                }
            } else {
                unset($RevertParticipentPayment['id']);
                unset($RevertParticipentPayment['created_at']);
                unset($RevertParticipentPayment['updated_at']);
                $RevertParticipentPayment['mode_of_payment'] = ParticipentPayment::ModeDirect;
                $RevertParticipentPayment['creator_id'] = Auth::user()->id;

                $return_result = $this->RevertPaymentFunction($RevertParticipentPayment, $participent_payment_id);
                if ($return_result['result'] != 'success') {
                    throw new Exception($return_result['result'], 1);
                }
                $message = 'Successfully Added Revert Payment';
            }
            $status = true;
            $userIds = DB::table('merchant_user')->where('merchant_id', $merchant_id)->pluck('user_id', 'user_id')->toArray();
            DashboardServiceProvider::addInvestorPaymentJob($userIds);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    public function makeMerchantACHDebitPayment($request)
    {
        $status = false;
        $message = '';
        if (Permissions::isAllow('ACH', 'Edit')) {
            $merchant_id = $request->merchant_id;
            $merchant = Merchant::select('id', 'name', 'cell_phone', 'phone', 'payment_amount', 'ach_pull')->find($merchant_id);
            if ($merchant->ach_pull) {
                if ($merchant->cell_phone) {
                    $bank_account = $merchant->bankAccountCredit;
                    if ($bank_account) {
                        $payment_amount = $request->payment_amount;
                        $max_debit_ach = ParticipentPayment::where('merchant_id', $merchant_id)->where('participent_payments.is_payment', 1)->sum('payment');
                        $pending_debit_ach = $merchant->ACHCreditPaymentProcessing()->sum('payment_amount');
                        $max_debit_ach -= $pending_debit_ach;
                        if ($payment_amount <= $max_debit_ach) {
                            $reason = $request->reason;
                            $ip = $request->ip;
                            $payment_date = $request->payment_date;
                            $params = ['chk_acct' => $bank_account->account_number, 'chk_aba' => $bank_account->routing_number, 'custname' => $bank_account->account_holder_name ?? $merchant->name, 'custphone' => $merchant->cell_phone, 'initial_amount' => sprintf('%.2f', $payment_amount)];
                            $transaction = $this->creditRequest($params);
                            if (isset($transaction['status']) && $transaction['status'] == 'Accepted') {
                                if (isset($transaction['duplicatetrans']) && $transaction['duplicatetrans'] == 1) {
                                    $data = ['merchant_id' => $merchant_id, 'merchant_name' => $merchant->name, 'status' => 'Duplicate Request', 'auth_code' => $transaction['authcode'] ?? '', 'reason' => $transaction['reason'] ?? '', 'type' => 'Payment'];
                                    $message = 'Duplicate ACH debit request.';
                                } else {
                                    $status = true;
                                    $ach_request = AchRequest::create([
                                        'merchant_id' => $merchant_id,
                                        'payment_date' => $payment_date,
                                        'payment_amount' => $payment_amount,
                                        'transaction_type' => 'credit',
                                        'order_id' => $transaction['order_id'],
                                        'ach_request_status' => 1,
                                        'auth_code' => $transaction['authcode'] ?? '',
                                        'reason' => $transaction['reason'] ?? '',
                                        'merordernumber' => $transaction['merordernumber'] ?? '',
                                        'response' => json_encode($transaction),
                                        'request_ip_address' => $ip,
                                        'reason' => $reason,
                                        'creator_id' => (Auth::user()) ? Auth::user()->id : null,
                                        'revert_id' => $request->participent_payment_id,
                                    ]);
                                    $data = [
                                        'merchant_id' => $merchant_id,
                                        'merchant_name' => $merchant->name,
                                        'payment_date' => $payment_date,
                                        'payment_amount' => $payment_amount,
                                        'transaction_type' => 'credit',
                                        'status' => true,
                                        'auth_code' => $transaction['authcode'] ?? '',
                                        'reason' => $transaction['reason'] ?? '',
                                        'order_id' => $transaction['order_id'] ?? '',
                                        'creator_name' => (Auth::user()) ? Auth::user()->name : null,
                                    ];
                                    $send_mail = $this->merchantACHCreditSentMail($data);
                                    $message = 'ACH debit request sent successfully.';
                                }
                            } else {
                                $ach_request = AchRequest::create(['merchant_id' => $merchant_id, 'payment_date' => $payment_date, 'payment_amount' => $payment_amount, 'transaction_type' => 'credit', 'ach_request_status' => -1, 'ach_status' => -1, 'auth_code' => $transaction['authcode'] ?? '', 'reason' => $transaction['reason'] ?? '', 'merordernumber' => $transaction['merordernumber'] ?? '', 'response' => json_encode($transaction), 'request_ip_address' => $ip, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                                $message = 'ACH debit request Declined.';
                            }
                        } else {
                            $message = 'Amount is bigger than CTD.';
                        }
                    } else {
                        $message = 'No bank account for credit for merchant.';
                    }
                } else {
                    $edit_url = route('admin::merchants::edit', ['id' => $merchant_id]);
                    $message = 'Merchant doesnot have cell phone. <a class="btn btn-danger btn-sm" href="' . $edit_url . '">Edit</a>';
                }
            } else {
                $message = 'ACH Pull turned off.';
            }
        } else {
            $message = 'No Permission to make ACH payment.';
        }

        return ['status' => $status, 'msg' => $message];
    }

    public function merchantACHCreditSentMail($data)
    {
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $message['title'] = 'Merchant ACH Credit Requested';
        $msg['title'] = $message['title'];
        $msg['to_mail'] = $emailArray;
        $msg['status'] = 'ach_merchant_credit_request';
        $msg['subject'] = $message['title'];
        $msg['unqID'] = unqID();
        $msg['checked_time'] = FFM::datetime(Carbon::now()->toDateTimeString());
        $msg['payment_amount'] = FFM::dollar($data['payment_amount']);
        $msg['merchant_name'] = $data['merchant_name'];
        $msg['creator_name'] = $data['creator_name'];
        $msg['merchant_view_link'] = route('admin::merchants::view', ['id' => $data['merchant_id']]);
        try {
            $email_template = Template::where([['temp_code', '=', 'MACC'], ['enable', '=', 1]])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
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
                $message = 'Mail sent';
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return $message;
    }

    public function RevertPaymentFunction($RevertParticipentPayment, $participent_payment_id)
    {
        if($RevertParticipentPayment['payment']<0){
            $RevertParticipentPayment['payment_type'] = ParticipentPayment::PaymentTypeDebit;
        }
        $RevertParticipentPayment['created_at'] = now();
        $RevertParticipentPayment['updated_at'] = now();
        $RevertParticipentPayment['creator_id'] = Auth::user()->id ?? 1;
        $RevertParticipentPayment = ParticipentPayment::create($RevertParticipentPayment);
        $revert_participent_payment_id = $RevertParticipentPayment->id;
        $return_result = GPH::RevertPayment($participent_payment_id, $revert_participent_payment_id);
        if ($return_result['result'] != 'success') {
            return $return_result;
        }
        $ParticipentPayment = ParticipentPayment::find($participent_payment_id);
        $ParticipentPayment->revert_id = $revert_participent_payment_id;
        $ParticipentPayment->save();
        return $return_result;
    }

    public function RevertPaymentACHFunction($ach_request)
    {
        $participent_payment_id = $ach_request->revert_id;
        $ParticipentPayment = ParticipentPayment::find($participent_payment_id);
        $RevertParticipentPayment = $ParticipentPayment->toArray();
        $RevertParticipentPayment['final_participant_share'] = -$RevertParticipentPayment['final_participant_share'];
        $RevertParticipentPayment['payment'] = -$RevertParticipentPayment['payment'];
        $RevertParticipentPayment['payment_date'] = $ach_request->payment_date;
        $RevertParticipentPayment['reason'] = $ach_request->reason;
        unset($RevertParticipentPayment['id']);
        $RevertParticipentPayment['mode_of_payment'] = ParticipentPayment::ModeAchPayment;
        $return_result = $this->RevertPaymentFunction($RevertParticipentPayment, $participent_payment_id);
        return $return_result;
    }

    /**
     * Delete payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($request, $id)
    {
        $status = false;
        $message = '';
        try {
            $authId = Auth::user()->id ?? 1;
            $payment_detail = $this->table->find($id);
            $arry_user_id = DB::table('payment_investors')->whereIn('participent_payment_id', [$id])->pluck('user_id')->toArray();
            $merchant_id = $payment_detail->merchant_id;
            $return_result = $this->SinglePaymentDeleteFunction([$id], $merchant_id);
            if ($return_result['result'] != 'success') {
                throw new Exception($return_result['result'], 1);
            }
            GPH::PaymentToMarchantUserSync($merchant_id);
            GPH::MerchantStatusLogFunction($merchant_id);
            DashboardServiceProvider::addInvestorPaymentJob($arry_user_id);
            InvestorHelper::update_liquidity($arry_user_id, 'Payment Deletion', $merchant_id);

            $status = true;
            $message = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    public function SinglePaymentDeleteFunction($id_array, $merchant_id)
    {
        try {
            $start = microtime(true);
            $merchant = Merchant::find($merchant_id);
            foreach ($id_array as $payment_investors_id) {
                $ach_revert_count = AchRequest::where('revert_id', $payment_investors_id)->count();
                if ($ach_revert_count > 0) {
                    throw new Exception('In order to delete this payment, you have to complete ACH revert payment.', 1);
                }
            }
            $carry_forwards = [7624, 7626, 7628, 7629, 7632, 7633, 7639, 7659, 7661, 7662, 7666, 7673, 7676, 7678, 7679, 7680, 7681, 7684, 7697, 7699, 7700, 7702, 7710, 7718, 7724, 7731, 7737, 7751, 7755, 7757, 7758, 7763, 7766, 7772, 7783, 7803, 7804, 7805, 7820, 7828, 7838, 7842, 7846, 7862, 7863, 7866, 7873, 7874, 7877, 7881, 7883, 7888, 7889, 7891, 7893, 7907, 7912, 7913, 7918, 7923, 7924, 7928, 7930, 7938, 7939, 7941, 7942, 7944, 7948, 7949, 7954, 7955, 7956, 7958, 7960, 7961, 7965, 7969, 7970, 7979, 7982, 7984, 7988, 7990, 7994, 7995, 8003, 8005, 8006, 8008, 8009, 8014, 8017, 8019, 8021, 8026, 8027, 8028, 8034, 8036, 8038, 8040, 8043, 8045, 8046, 8049, 8053, 8054, 8057, 8058, 8076, 8077, 8083, 8089, 8093, 8095, 8096, 8100, 8106, 8121, 8123, 8126, 8127, 8134, 8135, 8136, 8137, 8138, 8147, 8149, 8153, 8156, 8163, 8165, 8167, 8169, 8173, 8174, 8179, 8180, 8184, 8185, 8188, 8190, 8194, 8195, 8196, 8197, 8198, 8201, 8203, 8205, 8214, 8216, 8217, 8218, 8222, 8223, 8227, 8228, 8230, 8232, 8233, 8234, 8240, 8242, 8245, 8246, 8247, 8249, 8250, 8254, 8255, 8257, 8259, 8268, 8271, 8273, 8275, 8276, 8278, 8280, 8285, 8286, 8287, 8291, 8304, 8305, 8306, 8307, 8312, 8313, 8315, 8316, 8317, 8322, 8331, 8339, 8341, 8342, 8348, 8353, 8354, 8356, 8361, 8363, 8364, 8367, 8368, 8369, 8370, 8380, 8386, 8387, 8388, 8389, 8396, 8397, 8398, 8401, 8403, 8406, 8407, 8410, 8411, 8412, 8416, 8418, 8421, 8424, 8426, 8427, 8430, 8432, 8438, 8439, 8440, 8442, 8451, 8452, 8458, 8473, 8481, 8482, 8486, 8487, 8489, 8490, 8496, 8497, 8498, 8499, 8502, 8503, 8504, 8517, 8518, 8523, 8524, 8527, 8531, 8535, 8537, 8539, 8541, 8542, 8543, 8545, 8546, 8548, 8549, 8562, 8565, 8569, 8571, 8573, 8574, 8577, 8578, 8586, 8587, 8588, 8599, 8603, 8609, 8610, 8614, 8618, 8635, 8639, 8640, 8644, 8646, 8649, 8650, 8654, 8665, 8667, 8669, 8670, 8671, 8673, 8675, 8680, 8681, 8684, 8687, 8689, 8691, 8692, 8695, 8696, 8698, 8699, 8700, 8704, 8706, 8707, 8708, 8710, 8715, 8717, 8718, 8720, 8721, 8725, 8726, 8727, 8728, 8729, 8731, 8732, 8733, 8734, 8735, 8736, 8738, 8740, 8742, 8746, 8747, 8751, 8753, 8755, 8756, 8757, 8759, 8760, 8762, 8763, 8765, 8770, 8772, 8773, 8778, 8779, 8780, 8782, 8783, 8784, 8785, 8786, 8787, 8791, 8796, 8797, 8800, 8802, 8803, 8804, 8805, 8809, 8814, 8815, 8819, 8820, 8821, 8822, 8824, 8825, 8826, 8827, 8828, 8832, 8835, 8839, 8842, 8843, 8845, 8847, 8848, 8849, 8851, 8854, 8855, 8856, 8860, 8862, 8865, 8866, 8869, 8873, 8874, 8875, 8877, 8878, 8881, 8883, 8884, 8885, 8887, 8889, 8891, 8895, 8896, 8897, 8900, 8907, 8909, 8912, 8919, 8926, 8927, 8928, 8929, 8932, 8937, 8939, 8941, 8945, 8950, 8952, 8954, 8955, 8957, 8958, 8959, 8960, 8961, 8962, 8963, 8965, 8967, 8971, 8972, 8975, 8978, 8982, 8983, 8987, 8989, 8991, 8992, 8994, 8996, 8998, 9000, 9002, 9006, 9008, 9011, 9012, 9014, 9016, 9017, 9021, 9022, 9023, 9024, 9029, 9030, 9032, 9034, 9035, 9040, 9043, 9044, 9047, 9048, 9049, 9050, 9051, 9053, 9071, 9073, 9074, 9097, 9099, 9100, 9103, 9113, 9114, 9116, 9117, 9126, 9130, 9131, 9139, 9142, 9146, 9148, 9151, 9159, 9160, 9169, 9172, 9173, 9179, 9181, 9185, 9186, 9187, 9189, 9191, 9192, 9197, 9198, 9199, 9205, 9206, 9207, 9213, 9218, 9220, 9230, 9238, 9239, 9241, 9242, 9243, 9250, 9251, 9257, 9259, 9264, 9270, 9274, 9275, 9277, 9279, 9281, 9282, 9287, 9289, 9299, 9300, 9304, 9305, 9308, 9309, 9312, 9314, 9315, 9316, 9317, 9320, 9321, 9322, 9325, 9327, 9332, 9333, 9334, 9335, 9340, 9342, 9343, 9347, 9350, 9351, 9352, 9354, 9355, 9357, 9359, 9360, 9361, 9363, 9364, 9365, 9366, 9368, 9369, 9370, 9372, 9373, 9374, 9376, 9377, 9380, 9381, 9382, 9384, 9385, 9387, 9389, 9395, 9397, 9401, 9403, 9404, 9408, 9409, 9410, 9411, 9412, 9413, 9414, 9415, 9416, 9417, 9418, 9419, 9420, 9422, 9423, 9427, 9429, 9430, 9431, 9438, 9441, 9443, 9444, 9445, 9447, 9449, 9453, 9456, 9458, 9459, 9461, 9462, 9466, 9467, 9468, 9469, 9470, 9472, 9474, 9475, 9477, 9481, 9482, 9483, 9484, 9489, 9491, 9501, 9503, 9506, 9507, 9508, 9511, 9512, 9514, 9515, 9552, 9570, 9590, 9600, 9665];
            if (in_array($merchant_id, $carry_forwards)) {
                $e_msg['title'] = 'Carry Forwards data deleted';
                $e_msg['to_mail'] = [$this->admin_email];
                $e_msg['status'] = 'common';
                $e_msg['name'] = 'Carry Forwards';
                $e_msg['subject'] = 'Carry Forwards data deleted';
                foreach ($id_array as $payment_investors_id) {
                    $participent_payment = ParticipentPayment::find($payment_investors_id);
                    $e_msg['unqID'] = unqID();
                    try {
                        $emailJob = (new CommonJobs($e_msg))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $e_msg['content'] = 'Carry Forwards data with Merchant id no . ' . $merchant_id . ' and amount ' . $participent_payment->payment . ' has been deleted now';
                        $e_msg['to_mail'] = $this->admin_email;
                        $emailJob = (new CommonJobs($e_msg));
                        dispatch($emailJob);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
            $investor_ids=PaymentInvestors::whereIn('participent_payment_id',$id_array)->pluck('user_id','user_id')->toArray();
            $dates = DB::table('participent_payments')->whereIn('id', $id_array)->where('merchant_id', $merchant_id)->pluck('payment_date', 'id')->toArray();
            $payment_count = ParticipentPayment::where('participent_payments.payment', '==', 0);
            $payment_count = $payment_count->where('participent_payments.is_payment', 1);
            $payment_count = $payment_count->where('rcode', 0)->where('merchant_id', $merchant_id)->orderByDesc('id')->count();
            if (in_array($merchant->sub_status_id, [4, 22])) {
                throw new Exception('In order to delete this payment, you have to change to Collection', 1);
            }
            if (in_array($merchant->sub_status_id, [18, 19, 20])) {
                throw new Exception('In order to delete this payment, you have to change to Active Advance/Collection', 1);
            }
            $last_payment_id = ParticipentPayment::where('merchant_id', $merchant_id)
                ->where('participent_payments.is_payment', 1)
                ->orderByDesc('payment_date')->value('id');
            if ($payment_count && (!in_array($last_payment_id, $id_array))) {
                throw new Exception('<br> In order to delete this payment please delete the Settlement Payment also and re-enter to recalculate.', 1);
            }
            $form_params = ['method' => 'delete_lead_payment', 'username' => config('app.crm_user_name'), 'password' => config('app.crm_password'), 'payment_id' => implode(',', $id_array)];
            try {
                $crmJob = (new PaymentCreateCRM($form_params))->delay(now()->addMinutes(1));
                dispatch($crmJob);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            $count = count($id_array);
            $totalPaidCount = Merchant::where('id', $merchant_id)->value('paid_count');
            $payment = ParticipentPayment::where('merchant_id', $merchant_id)->whereIn('id', $id_array);
            foreach ($payment->get() as $key => $single) {
                if ($single->revert_id) {
                    $RevertParticipentPayment = ParticipentPayment::where('id', $single->revert_id)->whereNotIn('id', $id_array)->first();
                    if ($RevertParticipentPayment) {
                        throw new Exception('In Order To Delete This Payment you Need To Delete its Revert Payment. [' . FFM::date($RevertParticipentPayment->payment_date) . '] [' . FFM::dollar($RevertParticipentPayment->payment) . ']', 1);
                    }
                }
                if (!$single->delete()) {
                    throw new Exception('Something went wrong', 1);
                }
                $RevertParticipentPayment = ParticipentPayment::where('revert_id', $single->id)->first();
                if ($RevertParticipentPayment) {
                    $RevertParticipentPayment->revert_id = '';
                    $RevertParticipentPayment->save();
                }
            }
            GPH::PaymentToMarchantUserSync($merchant_id);
            $overpayment_id = User::OverpaymentId();
            if ($overpayment_id) {
                $OverPaidAmount = 0;
                $MerchantUserView    = MerchantUserView::where('merchant_id', $merchant_id);
                $OverPaidAmount      = clone $MerchantUserView;
                $OverPaidAmount      = $OverPaidAmount->where('investor_id', $overpayment_id);
                $OverPaidAmount      = $OverPaidAmount->sum('user_balance_amount');
                $MerchantUserView    = $MerchantUserView->where('investor_id', '!=', $overpayment_id);
                $MerchantUserBalance = clone $MerchantUserView;
                $MerchantUserBalance = $MerchantUserBalance->sum('user_balance_amount');
                if ($OverPaidAmount) {
                    if ($MerchantUserBalance < 0) {
                        throw new Exception("If this payment is deleted, the investor will have some amount as balance and overpayment in Overpayment Account!", 1);
                    }
                }
            }
            $OverPaymentCheck = new MerchantUserView;
            $OverPaymentCheck = $OverPaymentCheck->where('merchant_id', $merchant_id);
            $OverPaymentCheck = $OverPaymentCheck->whereIn('investor_id', $investor_ids);
            $agent_fee_id   = User::AgentFeeId();
            if ($agent_fee_id) {
                $OverPaymentCheck = $OverPaymentCheck->where('investor_id', '!=', $agent_fee_id);
            }
            if ($overpayment_id) {
                $OverPaymentCheck = $OverPaymentCheck->where('investor_id', '!=', $overpayment_id);
            }
            $OverPaymentCheck = $OverPaymentCheck->get([
                'investor_id',
                'Investor',
                'invest_rtr',
                'paid_participant_ishare',
                'user_balance_amount',
            ]);
            foreach ($OverPaymentCheck as $key => $value) {
                $user_balance_amount = round($value->user_balance_amount, 2);
                if ($user_balance_amount > 0) {
                    throw new Exception("This Payment deletion for " . $value->Investor . " cannot be processed as RTR will get increased from the actual RTR!", 1);
                }
            }
            $this->merchant->generatePayment($merchant);
            $participant_share = PaymentInvestors::where('payment_investors.merchant_id', $merchant_id)->sum('participant_share');
            if($participant_share<0){
                throw new Exception('In order to delete this payment, you have to delete the debit payment.', 1);
            }
            $total_rtr = MerchantUser::where('merchant_id', $merchant_id)->sum('invest_rtr');
            $bal_rtr = $total_rtr - $participant_share;
            if ($total_rtr > 0 && $merchant->payment_amount > 0) {
                $actual_payment_left = $bal_rtr / (($total_rtr / $merchant->rtr) * $merchant->payment_amount);
            } else {
                $actual_payment_left = 0;
            }
            $actual_payment_left = round(($actual_payment_left > 0) ? $actual_payment_left : 0);
            $rcode = ParticipentPayment::where('merchant_id', $merchant_id)
                ->where('participent_payments.is_payment', 1)
                ->orderByDesc('created_at')->value('rcode');
            $last_payment_date = ParticipentPayment::where('merchant_id', $merchant_id)->where('payment', '>', 0)
                ->where('participent_payments.is_payment', 1)
                ->max('payment_date');
            $update_merchant = ['paid_count' => $totalPaidCount - $count, 'actual_payment_left' => $actual_payment_left, 'last_payment_date' => $last_payment_date];
            if ($rcode) {
                $update_merchant['last_rcode'] = $rcode;
            } else {
                $update_merchant['last_rcode'] = 0;
            }
            $mer = Merchant::where('id', $merchant_id)->first()->toArray();
            // if ($mer['complete_percentage'] < 100 && $mer['sub_status_id'] == 11) {
            //     $update_merchant['sub_status_id'] = 1;
            //     $logArray = ['merchant_id' => $merchant_id, 'old_status' => $mer['sub_status_id'], 'current_status' => 1, 'description' => 'Merchant Status changed to Active Advance by system ', 'creator_id' => Auth::user()->id];
            //     $log = MerchantStatusLog::create($logArray);
            //     $update_merchant['last_status_updated_date'] = $log->created_at;
            // }
            $old_complete_percentage = $merchant->complete_percentage;
            if ($old_complete_percentage >= 100 && $mer['complete_percentage'] < 100) {
                $end_payment = TermPaymentDate::where('merchant_id', $merchant_id)->where('status', TermPaymentDate::ACHNotPaid)->orderByDesc('payment_date')->first();
                if ($end_payment) {
                    $old_payment_end_date = $merchant->payment_end_date;
                    $end_payment_date = $end_payment->payment_date;
                    if ($old_payment_end_date != $end_payment_date) {
                        $end_date_upadate = Merchant::find($merchant->id)->update(['payment_end_date' => $end_payment_date]);
                    }
                }
            }
            $first_payment_date = ParticipentPayment::where('merchant_id', $merchant_id)
                ->where('participent_payments.is_payment', 1)
                ->orderBy('payment_date', 'asc')->value('payment_date');
            if ($first_payment_date) {
                $update_merchant['first_payment'] = $first_payment_date;
            }
            $p_count = ParticipentPayment::where('merchant_id', $merchant_id)->where('rcode', 0)
                ->where('participent_payments.is_payment', 1)
                ->where('payment_type', 1)->count();
            if ($p_count == 0) {
                $update_merchant['first_payment'] = null;
            }
            $r_count = ParticipentPayment::where('merchant_id', $merchant_id)->where('rcode', '>', 0)
                ->where('participent_payments.is_payment', 1)
                ->orderByDesc('id')->count();
            if ($r_count == 0) {
                $update_merchant['last_rcode'] = 0;
            }
            Merchant::find($merchant_id)->update($update_merchant);
            $return['result'] = 'success';
        } catch (Exception $ex) {
            $return['result'] = $ex->getMessage();
        }
        return $return;
    }

    /**
     * Delete multiple payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete_multi_payment($request)
    {
        $status = false;
        $message = '';
        try {
            $authId = Auth::user()->id ?? 1;
            $merchant_id = $request->merchant_id;
            $id_array = $request->multi_id;
            if ($request->days) {
                $days_arr = explode(',', $request->days);
                $id_array = $this->table->where('merchant_id', $merchant_id)->whereIn('payment_date', $days_arr)->pluck('id')->toArray();
            }
            $arry_user_id = DB::table('payment_investors')->whereIn('participent_payment_id', $id_array)->pluck('user_id')->toArray();
            $return_result = $this->SinglePaymentDeleteFunction($id_array, $merchant_id);
            if ($return_result['result'] != 'success') {
                throw new Exception($return_result['result'], 1);
            }
            GPH::PaymentToMarchantUserSync($merchant_id);
            GPH::MerchantStatusLogFunction($merchant_id);
            DashboardServiceProvider::addInvestorPaymentJob($arry_user_id);
            InvestorHelper::update_liquidity($arry_user_id, 'Payment Deletion', $merchant_id);
            if (!$request->days) {
                $message = 'Payment Deleted !';
            } else {
                $message = 'Payment Deleted for the selected dates';
            }
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    /**
     * Delete multiple payment for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @return \Illuminate\Http\Response
     */
    public function lender_payment_generation($tableBuilder, $request)
    {
        $status = false;
        $message = '';
        $result = [];
        try {

            $page_title = 'Payment Generation';
            $lenders = $this->role->allLenders()->pluck('name', 'id');
            $merchants_view_status = 0;
            $payment_date = '';
            $company = '';
            $merchant_details = $lender_arr = [];


            $SpecialAccounts = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $SpecialAccounts->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE, User::OVERPAYMENT_ROLE]);
            $SpecialAccounts = $SpecialAccounts->pluck('users.id')->toArray();
            $disabled_company_investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name', 'investor')->whereHas('company_relation', function ($query) {
                $query->where('company_status', 0);
            })->pluck('users.id')->toArray();
            if ($request->all()) {
                $lender_arr = $request->lenders;
                $payment_date = $request->payment_date;
                $company = $request->companies;
                $mode = Settings::where('keys', 'collection_default_mode')->value('values');
                $merchant_details = DB::table('merchant_user')->select('merchants.date_funded', 'merchants.id', 'merchants.agent_fee_applied', 'merchants.payment_amount', 'merchants.name', 'last_payment_date', 'date_funded', 'merchants.rtr', DB::raw('count(merchant_user.user_id) as investor_count'))->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id');
                if ($company != 0) {
                    $merchant_details = $merchant_details->where('users.company', $company);
                }
                if (count($SpecialAccounts) > 0) {
                    $merchant_details = $merchant_details->whereNotIn('merchant_user.user_id', $SpecialAccounts);
                }
                $merchant_details->where('merchants.active_status', 1);

                if ($mode == 0) {
                    $merchant_details = $merchant_details->where('sub_status_id', 1);
                } else {
                    $merchant_details = $merchant_details->whereIn('sub_status_id', [18, 19, 20, 4, 22]);
                }
                $merchant_details = $merchant_details->where('complete_percentage', '<', 100);
                $merchant_details = $merchant_details->where('complete_percentage', '>=', 0);
                $merchant_details = $merchant_details->whereIn('lender_id', $lender_arr);
                $merchant_details = $merchant_details->whereNotIn('merchant_user.user_id', $disabled_company_investors);
                $merchant_details = $merchant_details->orderBy('merchants.name')->groupBy('merchant_user.merchant_id')->get();
                if ($merchant_details->isEmpty()) {
                    throw new Exception('No merchants available');
                }
                foreach ($merchant_details as $merchant) {
                    $merchant_id = $merchant->id;
                    $payment = $merchant->payment_amount;

                    $payment_total = ParticipentPayment::where('merchant_id', $merchant_id)->where('is_payment', 1)->sum('payment');
                    $merchant_balance = $merchant->rtr - $payment_total;

                    $processing_ach_payments = TermPaymentDate::where('merchant_id', $merchant_id)->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');
                    $balance_final = $merchant_balance - $processing_ach_payments;
                    if ($balance_final < $payment) {
                        if ($balance_final <= 0) {
                            $merchant->payment_amount = 0;
                        } else {
                            $merchant->payment_amount = sprintf('%.2f', $balance_final);
                        }
                    }
                }
            }

            $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
            $companies[0] = 'All';
            $companies = array_reverse($companies, true);
            $rcodes = DB::table('rcode')->pluck(DB::raw("CONCAT(description,' (',code,') ') AS name"), 'id');
            $result = [
                'merchants_view_status' => $merchants_view_status,
                'page_title' => $page_title,
                'lenders' => $lenders,
                'merchant_details' => $merchant_details,
                'payment_date' => $payment_date,
                'lender_arr' => $lender_arr,
                'companies' => $companies,
                'payment_date' => $payment_date,
                'company' => $company,
                'rcodes' => $rcodes
            ];
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'result' => $result,
            'message' => $message
        ];
    }

    /**
     * Lender payment generate function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add_payments_for_lenders($request)
    {
        $status = false;
        $message = '';
        try {
            DB::beginTransaction();
            $merchant_array = [];
            $company = $request->company;
            if (!$request->all()) {
                throw new Exception("Empty Request Please Select Any Merchant.", 1);
            }
            $total_payment_generated_count = $total_merchant_generated_count = 0;
            $settings = Settings::select('email', 'forceopay', 'send_permission')->first();
            $email_id_arr = explode(',', $settings->email);
            $merchants_l = collect($request->merchant)->filter(function ($merchant) {
                if(isset($merchant['select_merchant'])){
                    return $merchant;
                }
            });
            $investor_id = [];
            $count = 0;
            $send_permission = $settings->send_permission;
            $merchants_id_list = [];
            foreach ($merchants_l as $key => $merchant_l) {
                $merchant_id = $key;
                $merchants_id_list[] = $merchant_id;
                $rcode = (isset($merchant_l['rcode'])) ? $merchant_l['rcode'] : null;
                if (isset($merchant_l['select_merchant'])) {
                    $net_payment_status = isset($merchant_l['net_payment']) ? 'yes' : 'no';
                    $debit_status = isset($merchant_l['debit']) ? $merchant_l['debit'] : 'no';
                    $dates_array = explode(',', $merchant_l['payment_date']);
                    foreach ($dates_array as $key => $value) {
                        $dates_array[$key] = $value;
                    }
                    $debit_reason = isset($merchant_l['debit_reason']) ? $merchant_l['debit_reason'] : '';
                    asort($dates_array);
                    if ($company) {
                        $investor_id = User::join('merchant_user', 'merchant_user.user_id', 'users.id')->where('status', 1)->where('merchant_id', $merchant_id)->where('company', $company)->pluck('users.id');
                    } else {
                        $investor_id = User::join('merchant_user', 'merchant_user.user_id', 'users.id')->where('status', 1)->where('merchant_id', $merchant_id)->pluck('users.id');
                    }
                    if ($rcode != null) {
                        $merchant_l['amount'] = 0;
                    }
                    $merchant_l['amount'] = isset($merchant_l['amount']) ? $merchant_l['amount'] : 0;
                    $payment_s_arr = $this->merchant->generatePaymentForLender($merchant_id, $dates_array, $merchant_l['amount'], $net_payment_status, $debit_status, $debit_reason, $investor_id, $rcode, 0, $send_permission, true);
                    if($payment_s_arr['result']!='success') throw new Exception($payment_s_arr['result'], 1);
                    $payment_done = 1;
                    $total_payment_generated_count = $total_payment_generated_count + $payment_done;
                    $total_merchant_generated_count++;
                    $last_rcode = ParticipentPayment::where('merchant_id', $merchant_id)->max('rcode');
                    $merchant_update_arr = [];
                    if ($merchant_l['amount'] > 0 && ($debit_status != 'yes')) {
                        if (isset($payment_s_arr['last_payment_date'])) {
                            $merchant_update_arr = ['last_payment_date' => $payment_s_arr['last_payment_date'], 'last_rcode' => 0];
                        }
                    } else {
                        if ($last_rcode != 0) {
                            $merchant_update_arr = ['last_rcode' => $last_rcode];
                        } else {
                            $merchant_update_arr = ['last_rcode' => 0];
                        }
                    }
                    $merchant = Merchant::where('id', $merchant_id)->first();
                    $payments_investors = PaymentInvestors::select(DB::raw('sum(payment_investors.participant_share) as participant_share'))
                        ->where('payment_investors.merchant_id', $merchant_id)
                        ->groupBy('merchant_id')
                        ->first();
                    $payments_investors = $payments_investors ? $payments_investors->toArray() : ['participant_share' => 0];
                    $merchant_array = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'))->where('merchant_id', $merchant_id)->groupBy('merchant_id')->first()->toArray();
                    $total_rtr = $merchant_array['invest_rtr'];
                    $bal_rtr = $total_rtr - $payments_investors['participant_share'];
                    $actual_payment_left = 0;
                    if ($total_rtr > 0) {
                        $actual_payment_left = ($merchant->rtr) ? $bal_rtr / (($total_rtr / $merchant->rtr) * ($merchant->rtr / $merchant->pmnts)) : 0;
                    } else {
                        $actual_payment_left = 0;
                    }
                    $fractional_part = fmod($actual_payment_left, 1);
                    $act_paymnt_left = floor($actual_payment_left);
                    if ($fractional_part > .09) {
                        $act_paymnt_left = $act_paymnt_left + 1;
                    }
                    $actual_payment_left = ($act_paymnt_left > 0) ? $act_paymnt_left : 0;
                    if ($actual_payment_left <= 0 && $merchant->complete_percentage >= 100 && $merchant->sub_status_id != 11) {
                        $logArray = ['merchant_id' => $merchant_id, 'old_status' => $merchant->sub_status_id, 'current_status' => 11, 'description' => 'Merchant Status changed to Advance Completed by system ', 'creator_id' => $request->user()->id];
                        $log = MerchantStatusLog::create($logArray);
                        $merchant_status = Merchant::find($merchant_id)->update(['sub_status_id' => 11, 'last_status_updated_date' => $log->created_at]);
                    }
                    if (count($merchant_update_arr) > 0) {
                        $merchant_update = Merchant::find($merchant_id)->update($merchant_update_arr);
                    }
                }
            }
            $userIds = DB::table('merchant_user')->whereIn('merchant_id', $merchants_id_list)->pluck('user_id', 'user_id')->toArray();
            DashboardServiceProvider::addInvestorPaymentJob($userIds);
            $message = ' Payments Generated for ' . $total_merchant_generated_count . ' Merchants.';
            $status = true;
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    /**
     * ACH payment send page function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function achConfirmation($request)
    {
        if ($request->input('date')) {
            $date = PayCalc::getWorkingDay($request->date);
        } else {
            $date = PayCalc::getWorkingDay(Carbon::now()->addDay()->toDateString());
        }
        $manual_payment = null;
        $sub_status_id = null;
        if ($request->sub_status_id) {
            $sub_status_id = $request->sub_status_id;
        }
        $merchants_id = null;
        if ($request->merchants_id) {
            $merchants_id = $request->merchants_id;
        }
        $payments = MTB::getAchPayments($date, $sub_status_id, $manual_payment, $merchants_id);
        $total_payment_amount = array_sum(array_column($payments, 'payment_amount'));
        $ach_sub_status = config('custom.ach_sub_status');
        $statuses = SubStatus::whereIn('id', $ach_sub_status)->pluck('name', 'id');
        $date_formatted = FFM::date($date);
        $page_title = "Send Merchant ACH of $date_formatted";
        $request->flash();
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $merchants_id = Merchant::whereNotIn('sub_status_id', $unwanted_sub_status)->pluck('name', 'id');
        $tomorrow = Carbon::now()->addDay()->toDateString();
        $fee_types = config('custom.ach_fee_types');
        $ach_merchant_settings = (Settings::where('keys', 'ach_merchant')->value('values'));
        $ach_merchant_settings = json_decode($ach_merchant_settings, true);
        $fee_amounts = [];
        foreach ($fee_types as $fee_type => $fee_type_name) {
            $fee_amount_variable = $fee_type . '_amount';
            ${$fee_amount_variable} = $ach_merchant_settings[$fee_amount_variable] ?? 0;
            if (!is_numeric($$fee_amount_variable) || $$fee_amount_variable < 0) {
                $$fee_amount_variable = 0;
            }
            $fee_amounts[$fee_type] = $$fee_amount_variable;
        }
        return [
            'page_title' => $page_title,
            'statuses' => $statuses,
            'payments' => $payments,
            'total_payment_amount' => $total_payment_amount,
            'date' => $date,
            'merchants_id' => $merchants_id,
            'tomorrow' => $tomorrow,
            'fee_types' => $fee_types,
            'fee_amounts' => $fee_amounts
        ];
    }

    /**
     * ACH payment send function from front end.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function achConfirmationStore($request)
    {
        $status = false;
        $message = '';
        $result = null;
        try {
            if (Permissions::isAllow('ACH', 'Edit')) {
                $ach_payments = $request->ach;
                $ip = $request->ip();
                if ($request->has('date')) {
                    $payment_date = PayCalc::getWorkingDay($request->date);
                } else {
                    $payment_date = PayCalc::getWorkingDay(Carbon::now()->addDay()->toDateString());
                }
                $fee_types = config('custom.ach_fee_types');
                $update = $this->updateAchPaymentsFunction($ach_payments, $payment_date);
                $merchants_id = array_keys($ach_payments);
                $payments = MTB::getAchPayments($payment_date, null, null, $merchants_id);
                $ach_final = [];
                foreach ($payments as $payment) {
                    $ach_final[$payment->merchant_id]['amount'] = $payment->payment_amount;
                    foreach ($fee_types as $key => $fee_type) {
                        if (isset($payment->$key)) {
                            $ach_final[$payment->merchant_id]['fees'][$key] = $payment->$key;
                        }
                    }
                }
                $result = $this->sendACH($payment_date, $ach_final, $ip);
            }
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'result' => $result,
            'message' => $message
        ];
    }

    /**
     * ACH payment send function.
     *
     * @param  $payment_date
     * @param  $ach_payments
     * @param  $ip
     * @return \Illuminate\Http\Response
     */
    public function sendACH($payment_date, $ach_payments, $ip)
    {
        $fee_types = config('custom.ach_fee_types');
        $total_processed = 0;
        $total_processed_payment = 0;
        $total_processed_fee = 0;
        $transactions = [];
        foreach ($ach_payments as $key => $ach_payment) {
            $merchant = Merchant::select('id', 'name', 'cell_phone', 'phone', 'payment_amount', 'rtr')->find($key);
            $bank_account = $merchant->bankAccountDebit;
            $payment = $ach_payment['amount'];
            if ($bank_account) {
                if ($payment > 0) {
                    $is_fees = null;
                    $total_fees = 0;
                    if (isset($ach_payment['fees'])) {
                        foreach ($fee_types as $fee_type => $fee_type_title) {
                            if (isset($ach_payment['fees']["$fee_type"])) {
                                $$fee_type = $ach_payment['fees']["$fee_type"];
                                if ($$fee_type > 0) {
                                    $total_fees += $$fee_type;
                                    $is_fees = 1;
                                }
                            }
                        }
                    }
                    $params = ['chk_acct' => $bank_account->account_number, 'chk_aba' => $bank_account->routing_number, 'custname' => $bank_account->account_holder_name ?? $merchant->name, 'custphone' => $merchant->cell_phone ?? '8935550893', 'initial_amount' => sprintf('%.2f', $payment)];
                    $payment_schedule = TermPaymentDate::where(['merchant_id' => $key, 'payment_date' => $payment_date])->first();
                    if ($payment_schedule->status == 0) {
                        $payment_schedule->update(['status' => 2]);
                        $transaction = $this->debitRequest($params);
                        if (isset($transaction['status']) && $transaction['status'] == 'Accepted') {
                            if (isset($transaction['duplicatetrans']) && $transaction['duplicatetrans'] == 1) {
                                $transactions[] = ['merchant_id' => $key, 'merchant_name' => $merchant->name, 'amount' => $payment, 'transaction' => $transaction, 'status' => 'Duplicate Request', 'auth_code' => $transaction['authcode'] ?? '', 'reason' => $transaction['reason'] ?? '', 'type' => 'Payment'];
                                $payment_schedule->update(['status' => 0]);
                                continue;
                            }
                            $ach_request = AchRequest::create(['merchant_id' => $key, 'payment_date' => $payment_date, 'payment_amount' => $payment, 'transaction_type' => 'debit', 'order_id' => $transaction['order_id'], 'ach_request_status' => 1, 'auth_code' => $transaction['authcode'] ?? '', 'reason' => $transaction['reason'] ?? '', 'merordernumber' => $transaction['merordernumber'] ?? '', 'response' => json_encode($transaction), 'payment_schedule_id' => $payment_schedule->id, 'request_ip_address' => $ip, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                            $total_processed_payment += $payment;
                            if ($total_fees > 0) {
                                $fee_params = $params;
                                $fee_params['initial_amount'] = sprintf('%.2f', $total_fees);
                                $fee_transaction = $this->debitRequest($fee_params);
                                $fee_ach_status = 0;
                                if (isset($fee_transaction['status']) && $fee_transaction['status'] == 'Accepted') {
                                    if (isset($fee_transaction['duplicatetrans']) && $fee_transaction['duplicatetrans'] == 1) {
                                        $transactions[] = ['merchant_id' => $key, 'merchant_name' => $merchant->name, 'amount' => $total_fees, 'transaction' => $fee_transaction, 'status' => 'Duplicate Request', 'auth_code' => $fee_transaction['authcode'] ?? '', 'reason' => $fee_transaction['reason'] ?? '', 'type' => 'Fee'];
                                    } else {
                                        $fee_ach_status = 2;
                                        $ach_request_fee = AchRequest::create(['merchant_id' => $key, 'payment_date' => $payment_date, 'payment_amount' => $total_fees, 'transaction_type' => 'debit', 'order_id' => $fee_transaction['order_id'], 'ach_request_status' => 1, 'auth_code' => $fee_transaction['authcode'] ?? '', 'reason' => $fee_transaction['reason'] ?? '', 'merordernumber' => $fee_transaction['merordernumber'] ?? '', 'response' => json_encode($fee_transaction), 'request_ip_address' => $ip, 'is_fees' => $is_fees, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                                        $total_processed_fee += $total_fees;
                                    }
                                } else {
                                    $fee_ach_status = -1;
                                    $ach_request_fee = AchRequest::create(['merchant_id' => $key, 'payment_date' => $payment_date, 'payment_amount' => $total_fees, 'transaction_type' => 'debit', 'ach_request_status' => -1, 'ach_status' => -1, 'auth_code' => $fee_transaction['authcode'] ?? '', 'reason' => $fee_transaction['reason'] ?? '', 'merordernumber' => $fee_transaction['merordernumber'] ?? '', 'response' => json_encode($fee_transaction), 'request_ip_address' => $ip, 'is_fees' => $is_fees, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                                }
                                $transactions[] = ['merchant_id' => $key, 'merchant_name' => $merchant->name, 'amount' => $total_fees, 'transaction' => $fee_transaction, 'status' => $fee_transaction['status'] ?? '', 'auth_code' => $fee_transaction['authcode'] ?? '', 'reason' => $fee_transaction['reason'] ?? '', 'type' => 'Fee'];
                                $fee_query = VelocityFee::where(['merchant_id' => $key, 'payment_date' => $payment_date, 'status' => 0]);
                                foreach ($fee_types as $fee_type => $fee_type_title) {
                                    if (isset($$fee_type) && $$fee_type > 0) {
                                        $fee_item = with(clone $fee_query)->where(['fee_type' => $fee_type])->first();
                                        if ($fee_item) {
                                            $create_fee[$fee_type] = $fee_item->update(['ach_request_id' => isset($ach_request_fee) ? $ach_request_fee->id : null, 'order_id' => $fee_transaction['order_id'] ?? null, 'status' => $fee_ach_status, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                                        } else {
                                            $create_fee[$fee_type] = VelocityFee::create(['merchant_id' => $key, 'fee_type' => $fee_type, 'ach_request_id' => isset($ach_request_fee) ? $ach_request_fee->id : null, 'order_id' => $fee_transaction['order_id'] ?? null, 'payment_date' => $payment_date, 'payment_amount' => $$fee_type, 'status' => $fee_ach_status, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                                        }
                                    }
                                }
                            }
                        } else {
                            $payment_schedule->update(['status' => 0]);
                            $ach_request = AchRequest::create(['merchant_id' => $key, 'payment_date' => $payment_date, 'payment_amount' => $payment, 'transaction_type' => 'debit', 'ach_request_status' => -1, 'ach_status' => -1, 'auth_code' => $transaction['authcode'] ?? '', 'reason' => $transaction['reason'] ?? '', 'merordernumber' => $transaction['merordernumber'] ?? '', 'response' => json_encode($transaction), 'payment_schedule_id' => $payment_schedule->id, 'request_ip_address' => $ip, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                        }
                        $transactions[] = ['merchant_id' => $key, 'merchant_name' => $merchant->name, 'amount' => $payment, 'transaction' => $transaction, 'status' => $transaction['status'] ?? '', 'auth_code' => $transaction['authcode'] ?? '', 'reason' => $transaction['reason'] ?? '', 'type' => 'Payment'];
                    } else {
                        $transactions[] = ['merchant_id' => $key, 'merchant_name' => $merchant->name, 'amount' => $payment, 'status' => 'Payment Processing, Paid or Cancelled.', 'auth_code' => '', 'reason' => '', 'type' => 'Payment'];
                    }
                    $check_ach_deficit = $this->checkAchDeficit($key, $merchant, $payment_date);
                } else {
                    $transactions[] = ['merchant_id' => $key, 'merchant_name' => $merchant->name, 'amount' => $payment, 'status' => "Can't send amount less than or equal to zero.", 'auth_code' => '', 'reason' => '', 'type' => ''];
                }
            } else {
                $transactions[] = ['merchant_id' => $key, 'merchant_name' => $merchant->name, 'amount' => $payment, 'status' => 'No bank account for debit.', 'auth_code' => '', 'reason' => '', 'type' => ''];
            }
        }
        $count_total = count($transactions);
        $count_payment = 0;
        $count_fee = 0;
        $count_total_processing = 0;
        $count_payment_processing = 0;
        $count_fee_processing = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['type'] == 'Payment') {
                $count_payment++;
                if ($transaction['status'] == 'Accepted') {
                    $count_payment_processing++;
                }
            } else {
                $count_fee++;
                if ($transaction['status'] == 'Accepted') {
                    $count_fee_processing++;
                }
            }
        }
        $count_total_processing = $count_payment_processing + $count_fee_processing;
        $total_processed = $total_processed_payment + $total_processed_fee;
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $message['title'] = 'Merchant ACH Sent report for ' . FFM::date($payment_date);
        $exportCSV = $this->generateACHSentCSV($transactions, $payment_date);
        $fileName = $message['title'] . '.csv';
        $msg['atatchment'] = $exportCSV;
        $msg['atatchment_name'] = $fileName;
        $msg['title'] = $message['title'];
        $msg['content'] = $transactions;
        $msg['to_mail'] = $emailArray;
        $msg['status'] = 'ach_sent_report';
        $msg['subject'] = $message['title'];
        $msg['payment_date'] = FFM::date($payment_date);
        $msg['checked_time'] = FFM::datetime(Carbon::now()->toDateTimeString());
        $msg['unqID'] = unqID();
        $msg['total_processed'] = FFM::dollar($total_processed);
        $msg['total_processed_payment'] = FFM::dollar($total_processed_payment);
        $msg['total_processed_fee'] = FFM::dollar($total_processed_fee);
        $msg['count_total'] = $count_total;
        $msg['count_payment'] = $count_payment;
        $msg['count_fee'] = $count_fee;
        $msg['count_total_processing'] = $count_total_processing;
        $msg['count_payment_processing'] = $count_payment_processing;
        $msg['count_fee_processing'] = $count_fee_processing;
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'MACHR'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
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
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $return['exportCSV'] = $exportCSV;
        $return['fileName'] = $fileName;
        $return['transactions'] = $transactions;

        return $return;
    }

    /**
     * ACH send payment update function.
     *
     * @param  $requst
     * @return \Illuminate\Http\Response
     */
    public function updateAchpayments($request)
    {
        $status = false;
        $message = '';
        try {
            if (Permissions::isAllow('ACH', 'Edit')) {
                if ($request->has('date')) {
                    $date = $request->date;
                }
                $editable = false;
                if ($date) {
                    $current_time = Carbon::now();
                    $next_working_day = PayCalc::getWorkingDay($current_time->addDay()->toDateString());
                    if ($date < $next_working_day) {
                        $editable = false;
                    } elseif ($date > $next_working_day) {
                        $editable = true;
                    } elseif ($date == $next_working_day) {
                        $date_time = $current_time;
                        $cutoff_time = new Carbon($next_working_day . ' 14:45:00');
                        if ($date_time->lessThan($cutoff_time)) {
                            $editable = true;
                        } else {
                            $editable = false;
                        }
                    }
                }
                if ($editable) {
                    $ach_data = $request->ach;
                    $update = $this->updateAchPaymentsFunction($ach_data, $date);
                    $message = $update['message'];
                    $status = true;
                } else {
                    throw new Exception("This payments can't be edited.", 1);
                }
            } else {
                throw new Exception("No permission.", 1);
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    public function updateAchPaymentsFunction($ach_data, $date)
    {
        $data = [];
        $fee_types = config('custom.ach_fee_types');
        $success_count = 0;
        $success_count_fee = 0;
        foreach ($ach_data as $key => $ach_payment) {
            $type = 'Payment';
            $payment = $ach_payment['amount'];
            $payment_schedule = TermPaymentDate::where(['merchant_id' => $key, 'payment_date' => $date])->first();
            if ($payment_schedule->status == 0) {
                if (isset($ach_payment['fees'])) {
                    foreach ($fee_types as $fee_type => $fee_type_title) {
                        if ($fee_type == 'ach_fee') {
                            continue;
                        }
                        $is_fees = 0;
                        $$fee_type = $ach_payment['fees']["$fee_type"];
                        if (isset($$fee_type) && $$fee_type > 0) {
                            $fee = VelocityFee::where(['merchant_id' => $key, 'fee_type' => $fee_type, 'payment_date' => $date])->first();
                            if ($fee) {
                                if ($fee->payment_amount != $$fee_type) {
                                    $fee->update(['payment_amount' => $$fee_type, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                                    $is_fees = 2;
                                    $fee_reason = 'Fee updated' . $fee_status = 'success';
                                    $success_count_fee++;
                                }
                            } elseif ($$fee_type > 0) {
                                $is_fees = 1;
                                $fee_status = 'success';
                                $fee_reason = 'Fee created';
                                $success_count_fee++;
                                $fee = VelocityFee::create(['merchant_id' => $key, 'fee_type' => $fee_type, 'ach_request_id' => null, 'order_id' => null, 'payment_date' => $date, 'payment_amount' => $$fee_type, 'status' => 0, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                            }
                        } else {
                            $fee = VelocityFee::where(['merchant_id' => $key, 'fee_type' => $fee_type, 'payment_date' => $date])->first();
                            if ($fee) {
                                $fee->delete();
                                $is_fees = 4;
                                $fee_reason = 'Invalid fee,Fee deleted' . $fee_status = 'success';
                                $success_count_fee++;
                            }
                        }
                        if ($is_fees) {
                            $data[] = ['merchant_id' => $key, 'amount' => $$fee_type, 'payment_date' => $date, 'term_id' => '', 'schedule_id' => '', 'fee_id' => $fee->id, 'status' => $fee_status, 'reason' => $fee_reason, 'type' => $fee_type];
                        }
                    }
                }
                if ($payment > 0 && $payment_schedule->payment_amount != $payment) {
                    $send_ach[$key]['amount'] = $payment;
                    $success_count++;
                    $status = 'success';
                    $reason = 'Payment Updated.';
                    $payment_schedule->update(['payment_amount' => $payment]);
                } else {
                    $send_ach[$key]['amount'] = $payment_schedule->payment_amount;
                    $status = 'failed';
                    $reason = 'No change in amount.';
                }
            } else {
                $status = 'failed';
                $reason = 'Payment not in updatable state.';
            }
            $data[] = ['merchant_id' => $key, 'amount' => $payment, 'payment_date' => $date, 'term_id' => $payment_schedule->term_id, 'schedule_id' => $payment_schedule->id, 'status' => $status, 'reason' => $reason, 'type' => $type];
        }
        $message = " $success_count payments updated.";
        if ($success_count_fee) {
            $message .= " $success_count_fee fees updated.";
        }
        $return['data'] = $data;
        $return['message'] = $message;

        return $return;
    }

    public function generateACHSentCSV($data, $payment_date)
    {
        $excel_array[] = ['No', 'Merchant Name', 'Merchant ID', 'Status', 'Auth Code', 'Payment', 'Payment Date', 'Type'];
        $i = 1;
        $total_amount = 0;
        foreach ($data as $key => $tr) {
            $excel_array[$i]['No'] = $i;
            $excel_array[$i]['Merchant Name'] = $tr['merchant_name'];
            $excel_array[$i]['Merchant ID'] = $tr['merchant_id'];
            $excel_array[$i]['Status'] = $tr['status'];
            $excel_array[$i]['Auth Code'] = $tr['auth_code'];
            $excel_array[$i]['Payment'] = FFM::dollar($tr['amount']);
            $excel_array[$i]['Payment Date'] = FFM::date($payment_date);
            $excel_array[$i]['Type'] = $tr['type'];
            $total_amount = $total_amount + $tr['amount'];
            $i++;
        }
        $total_amount = FFM::dollar($total_amount);
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['Merchant Name'] = null;
        $excel_array[$i]['Merchant ID'] = null;
        $excel_array[$i]['Status'] = null;
        $excel_array[$i]['Auth Code'] = 'TOTAL';
        $excel_array[$i]['Payment'] = $total_amount;
        $export = new Data_arrExport($excel_array);

        return $export;
    }

    public function checkAchDeficit($mid, $merchant, $payment_date)
    {
        $future_payments = TermPaymentDate::where('merchant_id', $mid)->where('payment_date', '>', $payment_date)->where('payment_amount', '>', 0)->where('status', TermPaymentDate::ACHNotPaid);
        $future_payments_count = $future_payments->count();
        if ($future_payments_count <= 2) {

            $payment_total = ParticipentPayment::where('merchant_id', $mid)->where('is_payment', 1)->sum('payment');
            $balance_our_portion = $merchant->rtr - $payment_total;
            $default_payment_amount = $merchant->payment_amount;
            if ($balance_our_portion > 1) {
                $processing_payment_total = TermPaymentDate::where('merchant_id', $mid)->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');
                $future_payments_total = $future_payments->sum('payment_amount');
                $ach_deficit_balance = $balance_our_portion - $processing_payment_total - $future_payments_total;
                if ($ach_deficit_balance > 1) {
                    switch ($future_payments_count) {
                        case 2:
                            $email_title = '2 Scheduled ACH Payments left';
                            break;
                        case 1:
                            $email_title = '1 Scheduled ACH Payments left';
                            break;
                        case 0:
                            $email_title = 'No Scheduled ACH Payments left';
                            $future_payments_count = 'No';
                            break;
                    }
                    $makeup_terms = round(abs($ach_deficit_balance) / $default_payment_amount, 2);
                    $msg['title'] = $email_title;
                    $msg['status'] = 'merchant_ach_payments_deficit';
                    $msg['subject'] = $email_title;
                    $content['makeup_payments'] = $makeup_terms;
                    $content['ach_deficit_balance'] = $ach_deficit_balance;
                    $content['default_payment_amount'] = FFM::dollar($default_payment_amount);
                    $content['merchant_name'] = $merchant->name;
                    $content['future_payments_count'] = $future_payments_count;
                    $content['future_payments_total'] = $future_payments_total;
                    $content['url'] = route('admin::merchants::payment-terms', ['mid' => $mid]);
                    $msg['content'] = $content;
                    $emails = Settings::value('email');
                    $emailArray = explode(',', $emails);
                    $msg['to_mail'] = $emailArray;
                    $msg['unqID'] = unqID();
                    $msg['future_payments_count'] = $future_payments_count;
                    $msg['makeup_payments'] = $makeup_terms;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['default_payment_amount'] = FFM::dollar($default_payment_amount);
                    $msg['url'] = route('admin::merchants::payment-terms', ['mid' => $mid]);
                    try {
                        $email_template = Template::where([
                            ['temp_code', '=', 'ACHDF'], ['enable', '=', 1],
                        ])->first();
                        if ($email_template) {
                            if ($email_template->assignees) {
                                $template_assignee = explode(',', $email_template->assignees);
                                $bcc_mails = [];
                                foreach ($template_assignee as $assignee) {
                                    $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
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
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ACH requests page data function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $tableBuilder
     * @return \Illuminate\Http\Response
     */
    public function achRequestsData($request)
    {
        if ($request->from_date) {
            $requestData['from_date'] = $request->from_date;
        }
        if ($request->to_date) {
            $requestData['to_date'] = $request->to_date;
        }
        if (isset($request->status)) {
            $requestData['status'] = $request->status;
        }
        if ($request->order_id) {
            $requestData['order_id'] = $request->order_id;
        }
        if ($request->merchants_id) {
            $requestData['merchants_id'] = $request->merchants_id;
        }
        $requests = MTB::getAchRequestsDataTable($requestData);
        return $requests;
    }

    /**
     * ACH requests page function.
     *
     * @param  $tableBuilder
     * @return \Illuminate\Http\Response
     */
    public function achRequests($tableBuilder)
    {
        $page_title = 'Merchant ACH Status Check';
        $page_description = 'Merchant ACH Status Check';
        $requestData = [];
        $tableBuilder->ajax(['url' => route('admin::payments::achRequests.datatable'), 'type' => 'post', 'data' => 'function(data){
                data._token              = "' . csrf_token() . '";
                data.merchants_id         = $("#merchants_id").val();
                data.order_id            = $("#order_id").is(":checked")?1:0;
                data.from_date           = $("#from_date").val();
                data.to_date             = $("#to_date").val();
                data.status  = $("#ach_status").val();
            }']);
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(4).footer()).html(o.total_amount) }', 'pagingType' => 'input', 'stateSave' => true]);
        $requestData['columRequest'] = true;
        $tableBuilder->columns(MTB::getAchRequestsDataTable($requestData));
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $merchants_id = Merchant::pluck('name', 'id');
        $statuses = [0 => 'Processing', 1 => 'Settled', -1 => 'Returned'];

        return [
            'tableBuilder' => $tableBuilder,
            'page_title' => $page_title,
            'page_description' => $page_description,
            'merchants_id' => $merchants_id,
            'statuses' => $statuses
        ];
    }

    /**
     * ACH requests export function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function achRequestsExport($request)
    {
        $requestData = [];
        if ($request->from_date) {
            $requestData['from_date'] = $request->from_date;
        }
        if ($request->to_date) {
            $requestData['to_date'] = $request->to_date;
        }
        if (isset($request->ach_status)) {
            $requestData['status'] = $request->ach_status;
        }
        if ($request->order_id) {
            $requestData['order_id'] = $request->order_id;
        }
        if ($request->merchants_id) {
            $requestData['merchants_id'] = $request->merchants_id;
        }
        $response = MTB::getAchRequests($requestData);
        $data = $response['data'];
        $payment_amount = 0;
        foreach ($data as $key => $dat) {
            $payment_amount = $payment_amount + $dat->payment_amount;
        }
        $total_payment_amount = FFM::dollar($payment_amount);
        $fileName = 'Merchant ACH Status Check Report ' . FFM::datetimeExcel(date('Y-m-d H:i:s')) . '.csv';
        $excel_array[] = ['No', 'Merchant Name', 'Merchant ID', 'Order ID', 'Payment', 'Payment Date', 'Transaction Type', 'Request Status', 'Request Response', 'Settlement Status', 'Final Response', 'Payment Status', 'Created At', 'Transaction Type'];
        $i = 1;
        foreach ($data as $key => $tr) {
            $excel_array[$i]['No'] = $i;
            $excel_array[$i]['Merchant Name'] = $tr->merchant_name;
            $excel_array[$i]['Merchant ID'] = $tr->merchant_id;
            $excel_array[$i]['Order ID'] = $tr->order_id;
            $excel_array[$i]['Payment'] = FFM::dollar($tr->payment_amount);
            $excel_array[$i]['Payment Date'] = FFM::date($tr->payment_date);
            $excel_array[$i]['Type'] = $tr->type;
            $excel_array[$i]['Request Status'] = $tr->request_status;
            $excel_array[$i]['Request Response'] = $tr->request_reason ? $tr->request_reason . ' ' : '' . $tr->auth_code;
            $excel_array[$i]['Settlement Status'] = $tr->response_status;
            $excel_array[$i]['Final Response'] = $tr->response_reason;
            $excel_array[$i]['Payment Status'] = $tr->payment_status;
            $excel_array[$i]['Created At'] = FFM::datetime($tr->created_at);
            $excel_array[$i]['Transaction Type'] = $tr->transaction_type;
            $i++;
        }
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['Merchant Name'] = null;
        $excel_array[$i]['Merchant ID'] = null;
        $excel_array[$i]['Order ID'] = 'TOTAL';
        $excel_array[$i]['Payment'] = $total_payment_amount;
        $export = new Data_arrExport($excel_array);

        return [
            'export' => $export,
            'fileName' => $fileName
        ];
    }

    /**
     * Merchant's ACH single payment status check function.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function achCheckSingleStatus($id)
    {
        $status = false;
        $message = '';
        $data = null;
        try {
            $req = AchRequest::find($id);
            $data = $this->checkAchStatus($req);

            $status = true;
            $message = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'result' => $data,
            'message' => $message
        ];
    }

    /**
     * Merchant's ACH single payment status check and payment update function.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function checkAchStatus($req)
    {
        $send_message = [];
        $rcodes = Rcode::pluck('description', 'id')->toArray();
        $type = 'Payment Debit';
        if ($req->is_fees) {
            $type = 'Fee Debit';
        }
        $mid = $req->merchant_id;
        if ($req->ach_request_status == 1 && $req->order_id) {
            $status = $this->achStatus($req->order_id);
            $req->update(['response' => json_encode($status)]);
            if (isset($status['curr_bill_status'])) {
                if ($status['curr_bill_status']) {
                    $req->update(['status_response' => $status['curr_bill_status']]);
                }
                if (Str::contains($status['curr_bill_status'], 'Settled') && $req->transaction_type == 'debit') {
                    $participant_amount = $req->payment_amount;
                    if ($req->is_fees) {
                        $velocity_fees = $req->velocityFees();
                        if ($velocity_fees->count()) {
                            $velocity_fees->update(['status' => 1, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                        }
                    } else {
                        $add_payment = $this->generateAchPayment($mid, $req->payment_date, $participant_amount, null);
                        $req->schedule()->update(['status' => 1]);
                        $term = $req->schedule->paymentTerm;
                        $term->actual_payment_left -= 1;
                        $term->update();
                    }
                    $req->update(['ach_status' => 1, 'payment_status' => 1]);
                    $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $mid, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => 'Accepted Transaction', 'type' => $type, 'status' => 'Success'];
                } elseif (Str::contains($status['curr_bill_status'], 'Credit') && $req->transaction_type == 'credit') {
                    $start_date = $req->payment_date;
                    $end_date = Carbon::now()->tz('America/New_York')->toDateString();
                    $no_of_days_passed = PayCalc::calculateWorkingDaysCount($start_date, $end_date);

                    $ach_merchant = (Settings::where('keys', 'ach_merchant')->value('values'));
                    $ach_merchant = json_decode($ach_merchant, true);
                    $ach_merchant_credit_lag_days = $ach_merchant['ach_merchant_credit_lag_days'] ?? 4;

                    if ($no_of_days_passed > $ach_merchant_credit_lag_days) {
                        if ($req->revert_id != null) {
                            try {
                                DB::beginTransaction();
                                $add_revert_payment = $this->RevertPaymentACHFunction($req);
                                if ($add_revert_payment['result'] == 'success') {
                                    $req->update(['ach_status' => 1, 'payment_status' => 1]);
                                } else {
                                    throw new \Exception($add_revert_payment['result'], 1);
                                }
                                DB::commit();
                            } catch (Exception $e) {
                                DB::rollback();
                            }
                        } else {
                            // $debit_status = 'yes';
                            // $reason = $req->reason ?? '';
                            // $mode_of_payment = 1;
                            // $add_debitpayment = $this->generateAchPayment($req->merchant_id, $req->payment_date, $req->payment_amount, null, $mode_of_payment, $debit_status, $reason);
                            // $req->update(['ach_status' => 1, 'payment_status' => 1]);
                        }
                        $data = ['ach_id' => $req->id, 'schedule_id' => '', 'merchant_id' => $mid, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => 'Accepted Transaction', 'type' => $type, 'status' => 'Success'];
                    } else {
                        $data = ['ach_id' => $req->id, 'schedule_id' => '', 'merchant_id' => $mid, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => $status['curr_bill_status'], 'type' => $type, 'status' => 'Pending'];
                    }
                } elseif (Str::contains($status['curr_bill_status'], 'Returned')) {
                    $rcode = 35;
                    $response_rcode = explode('-', $status['curr_bill_status'], 2);
                    if ($response_rcode[1]) {
                        $rcode = array_search(trim($response_rcode[1]), $rcodes);
                        if ($rcode == false) {
                            if (preg_match('#\((.*?)\)#', $response_rcode[1], $match)) {
                                $rcode = Rcode::where('code', $match[1])->value('id');
                                if (!$rcode) {
                                    $rcode = 35;
                                }
                            } else {
                                $rcode = 35;
                            }
                        }
                    }
                    if ($req->is_fees) {
                        if ($req->velocityFees->count()) {
                            $req->velocityFees()->update(['status' => -1, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                        }
                    } else {
                        if ($rcode) {
                            if ($req->revert_id == null) {
                                $add_payment = $this->generateAchPayment($mid, $req->payment_date, 0, $rcode);
                            } else {
                                $req->update(['revert_id' => null]);
                            }
                        }
                        $req->schedule()->update(['status' => -1]);
                    }
                    $req->update(['ach_status' => -1, 'payment_status' => -1]);
                    $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $mid, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => $status['curr_bill_status'], 'type' => $type, 'status' => 'Returned'];
                    $send_message = ['model_id' => $mid, 'mobile' => $req->merchant->cell_phone, 'email' => $req->merchant->notification_email, 'amount' => $req->payment_amount, 'status' => Message::PENDING, 'message' => $this->messageGeneration($mid, $req->payment_amount), 'creator_id' => (Auth::user()) ? Auth::user()->id : null];
                } else {
                    $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $mid, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => $status['curr_bill_status'], 'type' => $type, 'status' => 'Pending'];
                }
            } elseif (isset($status['error'])) {
                $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $mid, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => $status['error'], 'type' => $type, 'status' => 'Failed'];
            } else {
                $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $mid, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => 'Unknown response.', 'type' => $type, 'status' => 'Failed'];
            }
        } else {
            $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $mid, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'message' => 'Declined Request', 'type' => $type, 'status' => 'failed'];
        }
        if ($send_message) {
            $send_message['model_name'] = \App\Merchant::class;
            $rcode_db = Rcode::find($rcode);
            try {
                $mNotes = [
                    'merchant_id' => $send_message['model_id'],
                    'note' => " \"We recieved an Rcode ($rcode_db->code)-$rcode_db->description from the Merchant on " . \FFM::datetime(Carbon::now()) . '"',
                    'added_by' => Auth::user()->name,
                ];
                $this->mNotes->createRequest($mNotes);
                $MerchantEmail['title'] = 'We noticed you missed a payment...';
                $MerchantEmail['status'] = 'merchant_returnd';
                $MerchantEmail['unqID'] = unqID();
                $MerchantEmail['subject'] = $MerchantEmail['title'];
                $MerchantEmail['merchant_id'] = $mid;
                $MerchantEmail['merchant_name'] = $req->merchant->name;
                $MerchantEmail['amount'] = $req->payment_amount;
                $MerchantEmail['to_mail'] = $send_message['email'];
                if ($MerchantEmail['to_mail']) {
                    try {
                        $values = ['title' => $MerchantEmail['title'], 'type' => 2, 'to_mail' => $MerchantEmail['to_mail'], 'to_user_type' => 'merchant', 'to_id' => $mid, 'to_name' => $req->merchant->name, 'status' => 'success'];
                    } catch (Exception $e) {
                        $values = ['title' => $MerchantEmail['title'], 'type' => 2, 'to_mail' => $MerchantEmail['to_mail'], 'to_user_type' => 'merchant', 'to_id' => $mid, 'to_name' => $req->merchant->name, 'status' => 'failed', 'failed_message' => $e->getMessage()];
                    }
                } else {
                    $values = ['title' => $MerchantEmail['title'], 'type' => 2, 'to_mail' => '-', 'status' => 'failed', 'to_user_type' => 'merchant', 'to_id' => $mid, 'to_name' => $req->merchant->name, 'failed_message' => 'email is null'];
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        return $data;
    }

    /**
     * Merchant's ACH multiple payment status check  function.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function achCheckStatus()
    {
        $current_time = Carbon::now();
        $current_time_formatted = FFM::datetime($current_time->toDateTimeString());
        $requests = AchRequest::where('ach_status', 0)->oldest()->get();
        $data = [];
        foreach ($requests as $req) {
            $data[] = $this->checkAchStatus($req);
        }
        $total = 0;
        $total_settled = 0;
        $total_settled_payment = 0;
        $total_settled_fee = 0;
        $total_rcode = 0;
        $total_rcode_amount = 0;
        $total_rcode_fee = 0;
        $count_total = count($data);
        $count_payment = 0;
        $count_fee = 0;
        foreach ($data as $item) {
            if ($item['type'] == 'Payment Debit') {
                $count_payment++;
                if ($item['status'] == 'Success') {
                    $total_settled += $item['payment_amount'];
                    $total_settled_payment += $item['payment_amount'];
                } elseif ($item['status'] == 'Returned') {
                    $total_rcode += $item['payment_amount'];
                    $total_rcode_amount += $item['payment_amount'];
                }
            } else {
                $count_fee++;
                if ($item['status'] == 'Success') {
                    $total_settled += $item['payment_amount'];
                    $total_settled_fee += $item['payment_amount'];
                } elseif ($item['status'] == 'Returned') {
                    $total_rcode += $item['payment_amount'];
                    $total_rcode_fee += $item['payment_amount'];
                }
            }
            $total += $item['payment_amount'];
        }
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $message['title'] = 'Merchant ACH Status Check';
        $fileName = $message['title'] . '.csv';
        $exportCSV = $this->generateACHCheckStatusCSV($data);
        $msg['atatchment'] = $exportCSV;
        $msg['atatchment_name'] = $fileName;
        $msg['title'] = $message['title'];
        $msg['content'] = $data;
        $msg['total_settled'] = FFM::dollar($total_settled);
        $msg['total_settled_payment'] = FFM::dollar($total_settled_payment);
        $msg['total_settled_fee'] = FFM::dollar($total_settled_fee);
        $msg['total_rcode'] = FFM::dollar($total_rcode);
        $msg['total_rcode_amount'] = FFM::dollar($total_rcode_amount);
        $msg['total_rcode_fee'] = FFM::dollar($total_rcode_fee);
        $msg['count_total'] = $count_total;
        $msg['count_payment'] = $count_payment;
        $msg['count_fee'] = $count_fee;
        $msg['to_mail'] = $emailArray;
        $msg['status'] = 'ach_status_check';
        $msg['subject'] = $message['title'];
        $msg['unqID'] = unqID();
        $msg['checked_time'] = $current_time_formatted;
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'MACHC'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
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
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $like = 'Returned';
        $rcode_ach = array_filter($data, function ($item) use ($like) {
            if (stripos($item['status'], $like) !== false) {
                return true;
            }

            return false;
        });
        if ($rcode_ach) {
            $message['title'] = 'Merchant ACH Rcode report';
            $msg = null;
            $html = '';
            $html .= '<table width="100%" border="1" align="center" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">#</th>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Merchant</th>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Amount</th>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Date</th>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Status</th>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Response</th>
                    <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Type</th>
                </tr>
            </thead>
            <tbody>';
            $i = 1;
            foreach ($rcode_ach as $key => $req) {
                $html .= '<tr>
                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . $i++ . '</td>
                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;"><a href="' . \URL::to('admin/merchants/view', $req['merchant_id']) . '">' . $req['merchant_name'] . '</a></td>
                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . \FFM::dollar($req['payment_amount']) . '</td>
                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . \FFM::date($req['payment_date']) . '</td>
                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . $req['status'] . '</td>
                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . $req['message'] . '</td>
                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . $req['type'] . '</td>
            </tr>';
            }
            $html .= '</tbody></table>';
            $msg['rcode_report_table'] = $html;
            $msg['title'] = $message['title'];
            $msg['subject'] = $message['title'];
            $msg['unqID'] = unqID();
            $fileName = $message['title'] . '.csv';
            $msg['status'] = 'ach_rcode_mail';
            $msg['to_mail'] = $emailArray;
            $exportCSV = $this->generateACHCheckStatusCSV($rcode_ach);
            $msg['atatchment'] = $exportCSV;
            $msg['atatchment_name'] = $fileName;
            try {
                $email_template = Template::where([
                    ['temp_code', '=', 'RCOML'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
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
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        return $data;
    }

    /**
     * Merchant's ACH multiple payment status check  function.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function achCheckStatusCsv()
    {
        $status = false;
        $message = '';
        $result = null;
        try {
            $data = $this->achCheckStatus();
            $fileName = 'Merchant ACH Status Check Report ' . FFM::datetimeExcel(date('Y-m-d H:i:s')) . '.csv';
            $exportCSV = $this->generateACHCheckStatusCSV($data);
            $result = [
                'fileName' => $fileName,
                'exportCSV' => $exportCSV
            ];
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'result' => $result,
            'message' => $message
        ];
    }

    public function messageGeneration($merchant_id = null, $amount = 0)
    {
        $hashids = new Hashids();
        $merchant_id = $hashids->encode($merchant_id);
        $url = url("pm/$merchant_id/make-payment/" . $amount);
        $newBreakLine = '%0a';
        $return = 'Hope all is well. This is the Asset Recovery Department at Velocity Group USA, We received notice that there was an interruption to the delivery of receivables due to insufficient funds in your designated account today. Is everything OK with the business?' . $newBreakLine;
        $return .= 'If this was a one-time issue,you can make a one time credit card payment here ' . $url . $newBreakLine;
        $return .= 'If not, please make sure to have the necessary funds available in your designated account today to deliver the receivables generated yesterday in addition to your next ACH debit.' . $newBreakLine;
        $return .= "In the event you have experienced a problem with your designated bank account or if there's an issue with the business, please contact me immediately so that we can discuss the necessary steps to work with you to resolve." . $newBreakLine;
        $return .= 'Respectfully ,' . $newBreakLine;
        $return .= 'Lauren Esposito | Director of Collections' . $newBreakLine;
        $return .= 'lesposito@curepayment.com' . $newBreakLine;
        $return .= '(631) 953-2625 Ext. 502' . $newBreakLine;
        $return .= '(800) 519-2234' . $newBreakLine;
        $return .= 'Fax: (631) 953-2610' . $newBreakLine;
        $return .= 'lesposito@curepayment.com' . $newBreakLine;
        $return .= 'www.curepayment.com' . $newBreakLine;

        return $return;
    }

    public function generateACHCheckStatusCSV($data)
    {
        $excel_array[] = ['No', 'Merchant Name', 'Merchant ID', 'Status', 'Payment Amount', 'Response', 'Payment Date', 'Type'];
        $i = 1;
        $total_payment = 0;
        foreach ($data as $key => $tr) {
            $excel_array[$i]['No'] = $i;
            $excel_array[$i]['Merchant Name'] = $tr['merchant_name'];
            $excel_array[$i]['Merchant ID'] = $tr['merchant_id'];
            $excel_array[$i]['Status'] = $tr['status'];
            $excel_array[$i]['Payment Amount'] = FFM::dollar($tr['payment_amount']);
            $excel_array[$i]['Response'] = $tr['message'];
            $excel_array[$i]['Payment Date'] = FFM::date($tr['payment_date']);
            $excel_array[$i]['Type'] = $tr['type'];
            $total_payment = $total_payment + $tr['payment_amount'];
            $i++;
        }
        $total_payments = FFM::dollar($total_payment);
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['Merchant Name'] = null;
        $excel_array[$i]['Merchant ID'] = null;
        $excel_array[$i]['Status'] = 'TOTAL';
        $excel_array[$i]['Payment Amount'] = count($data) ? $total_payments : FFM::dollar(0);
        $export = new Data_arrExport($excel_array);

        return $export;
    }

    public function generateAchPayment($merchant_id, $payment_date, $payment_amount, $rcode = null, $mode_of_payment = 1, $debit_status = null, $reason = '')
    {
        $creator_id = null;
        if (Auth::check()) {
            $creator_id = Auth::user()->id;
        } elseif (Session::has('credit_card_payment_creator')) {
            $creator_id = Session::get('credit_card_payment_creator');
        }
        $settings_permission = Settings::select('send_permission')->first();
        $send_permission = $settings_permission->send_permission;
        $investor_id = User::join('merchant_user', 'merchant_user.user_id', 'users.id')->where('status', 1)->where('merchant_id', $merchant_id)->pluck('users.id');
        $return_array = $this->merchant->generatePaymentForLender($merchant_id, [$payment_date], $payment_amount, null, $debit_status, $reason, $investor_id, $rcode, $mode_of_payment, $send_permission);
        if (isset($return_array['last_payment_date'])) {
            $last_payment_date = ParticipentPayment::where('merchant_id', $merchant_id)->where('payment', '>', 0)->max('payment_date');
            if ($rcode) {
                $last_rcode = ParticipentPayment::where('merchant_id', $merchant_id)->where('is_payment', 1)->orderByDesc('created_at')->value('rcode');
                if ($last_rcode > 0) {
                    $merchant_update_arr = ['last_rcode' => $last_rcode];
                } else {
                    $merchant_update_arr = ['last_rcode' => 0];
                }
            } else {
                $merchant = Merchant::select('complete_percentage', 'name', 'rtr', 'payment_amount', 'sub_status_id', 'merchants.id')->where('id', $merchant_id)->first();
                if ($merchant->sub_status_id == 4) {
                    $logArray = ['merchant_id' => $merchant_id, 'old_status' => $merchant->sub_status_id, 'current_status' => 5, 'description' => 'Merchant Status changed to Collections by system', 'creator_id' => $creator_id];
                    $log = MerchantStatusLog::create($logArray);
                    $sys_substaus = (Settings::where('keys', 'agent_fee_on_substtaus')->value('values'));
                    $sys_substaus = json_decode($sys_substaus, true);
                    if (!in_array(5, $sys_substaus)) {
                        $status = Merchant::find($merchant_id)->update(['sub_status_id' => 5, 'last_status_updated_date' => $log->created_at, 'agent_fee_applied' => 0]);
                    } else {
                        $status = Merchant::find($merchant_id)->update(['sub_status_id' => 5, 'last_status_updated_date' => $log->created_at]);
                    }
                    if ($status) {
                        $settings = Settings::select('email', 'forceopay')->first();
                        $email_id_arr = explode(',', $settings->email);
                        $message['title'] = 'Collection status added to ' . $merchant->name;
                        $message['subject'] = 'Collection status added to ' . $merchant->name;
                        $message['content'] = ' A new payment added for ' . $merchant->name . '. Status changed to Collection.';
                        $message['to_mail'] = $email_id_arr;
                        $message['status'] = 'merchant_change_status';
                        $message['merchant_id'] = $merchant->id;
                        $message['merchant_name'] = $merchant->name;
                        $message['unqID'] = unqID();
                        $message['template_type'] = 'merchant_status_collection';
                        try {
                            $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                            dispatch($emailJob);
                            $message['to_mail'] = $this->admin_email;
                            $emailJob = (new CommonJobs($message));
                            dispatch($emailJob);
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                }
                $payments_investors = PaymentInvestors::select(DB::raw('sum(payment_investors.participant_share) as participant_share'))->where('payment_investors.merchant_id', $merchant_id)->groupBy('merchant_id')->first()->toArray();
                $merchant_array = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'))->where('merchant_id', $merchant_id)->groupBy('merchant_id')->first()->toArray();
                $total_rtr = $merchant_array['invest_rtr'];
                $bal_rtr = $total_rtr - $payments_investors['participant_share'];
                $actual_payment_left = 0;
                if ($total_rtr > 0) {
                    if ($merchant->payment_amount > 0) {
                        $actual_payment_left = $bal_rtr / (($total_rtr / $merchant->rtr) * $merchant->payment_amount);
                    }
                }
                $act_paymnt_left = floor($actual_payment_left);
                $actual_payment_left = ($act_paymnt_left > 0) ? $act_paymnt_left : 0;
                $merchant_update_arr = ['last_payment_date' => $last_payment_date, 'actual_payment_left' => $actual_payment_left, 'last_rcode' => null];
                if ($actual_payment_left <= 0 && $merchant->complete_percentage >= 100 && $merchant->sub_status_id != 11) {
                    $logArray = ['merchant_id' => $merchant_id, 'old_status' => $merchant->sub_status_id, 'current_status' => 11, 'description' => 'Merchant Status changed to Advance Completed by system ', 'creator_id' => $creator_id];
                    $log = MerchantStatusLog::create($logArray);
                    $merchant_status = Merchant::find($merchant_id)->update(['sub_status_id' => 11, 'last_status_updated_date' => $log->created_at]);
                }
            }
            $merchant_update = Merchant::find($merchant_id)->update($merchant_update_arr);
        }
        $userIds = DB::table('merchant_user')->where('merchant_id', $merchant_id)->pluck('user_id', 'user_id')->toArray();
        DashboardServiceProvider::addInvestorPaymentJob($userIds);
        return $return_array;
    }

    public function achDoubleCheckStatus()
    {
        $status = false;
        $message = '';
        $result = null;
        try {
            $current_time = Carbon::now();
            $current_time_formatted = FFM::datetime($current_time->toDateTimeString());
            $today = $current_time->toDateString();
            $requests = AchRequest::where('ach_status', 1)->oldest()->get();
            $data = [];

            $ach_merchant = (Settings::where('keys', 'ach_merchant')->value('values'));
            $ach_merchant = json_decode($ach_merchant, true);
            $ach_merchant_double_check_lag_days = $ach_merchant['ach_merchant_double_check_lag_days'] ?? 5;

            foreach ($requests as $req) {
                $cutoff_date = PayCalc::getNthWorkingDay($req->payment_date, $ach_merchant_double_check_lag_days);
                if ($cutoff_date >= $today) {
                    echo "checking Merchant ACH $req->id with p.d $req->payment_date and c.d $cutoff_date \n";
                    $data[] = $this->doubleCheckAchStatus($req);
                }
            }
            if (count($data)) {
                $total = 0;
                $total_settled = 0;
                $total_settled_payment = 0;
                $total_settled_fee = 0;
                $total_rcode = 0;
                $total_rcode_amount = 0;
                $total_rcode_fee = 0;
                $count_total = count($data);
                $count_payment = 0;
                $count_fee = 0;
                foreach ($data as $item) {
                    if ($item['type'] == 'Payment Debit') {
                        $count_payment++;
                        if ($item['status'] == 'Success') {
                            $total_settled += $item['payment_amount'];
                            $total_settled_payment += $item['payment_amount'];
                        } elseif ($item['status'] == 'Returned') {
                            $total_rcode += $item['payment_amount'];
                            $total_rcode_amount += $item['payment_amount'];
                        }
                    } else {
                        $count_fee++;
                        if ($item['status'] == 'Success') {
                            $total_settled += $item['payment_amount'];
                            $total_settled_fee += $item['payment_amount'];
                        } elseif ($item['status'] == 'Returned') {
                            $total_rcode += $item['payment_amount'];
                            $total_rcode_fee += $item['payment_amount'];
                        }
                    }
                    $total += $item['payment_amount'];
                }
                $emails = Settings::value('email');
                $emailArray = explode(',', $emails);
                $message['title'] = 'Merchant ACH Status Re-check';
                $fileName = $message['title'] . '.csv';
                $exportCSV = $this->generateACHCheckStatusCSV($data);
                $msg['atatchment'] = $exportCSV;
                $msg['atatchment_name'] = $fileName;
                $msg['title'] = $message['title'];
                $msg['content'] = $data;
                $msg['total_settled'] = FFM::dollar($total_settled);
                $msg['total_settled_payment'] = FFM::dollar($total_settled_payment);
                $msg['total_settled_fee'] = FFM::dollar($total_settled_fee);
                $msg['total_rcode'] = FFM::dollar($total_rcode);
                $msg['total_rcode_amount'] = FFM::dollar($total_rcode_amount);
                $msg['total_rcode_fee'] = FFM::dollar($total_rcode_fee);
                $msg['count_total'] = $count_total;
                $msg['count_payment'] = $count_payment;
                $msg['count_fee'] = $count_fee;
                // $msg['params'] = $params;
                $msg['to_mail'] = $emailArray;
                $msg['status'] = 'ach_status_check';
                $msg['subject'] = $message['title'];
                $msg['unqID'] = unqID();
                $msg['checked_time'] = $current_time_formatted;
                try {
                    $email_template = Template::where([
                        ['temp_code', '=', 'MACHC'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        if ($email_template->assignees) {
                            $template_assignee = explode(',', $email_template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                                $role_mails = array_diff($role_mails, $emailArray);
                                $bcc_mails[] = $role_mails;
                            }
                            $msg['bcc'] = Arr::flatten($bcc_mails);
                        }
                        $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $msg['to_mail'] = $this->admin_email;
                        $emailJob = (new CommonJobs($msg));
                        dispatch($emailJob);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $like = 'Returned';
                $rcode_ach = array_filter($data, function ($item) use ($like) {
                    if (stripos($item['status'], $like) !== false) {
                        return true;
                    }

                    return false;
                });
                if ($rcode_ach) {
                    $message['title'] = 'Merchant ACH Re-check Rcode report';
                    $msg = null;
                    $html = '';
                    $html .= '<table width="100%" border="1" align="center" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">#</th>
                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Merchant</th>
                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Amount</th>
                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Date</th>
                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Status</th>
                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Response</th>
                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Type</th>
                        </tr>
                    </thead>
                    <tbody>';
                    $i = 1;
                    foreach ($rcode_ach as $key => $req) {
                        $html .= '<tr>
                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . $i++ . '</td>
                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;"><a href="' . \URL::to('admin/merchants/view', $req['merchant_id']) . '">' . $req['merchant_name'] . '</a></td>
                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . \FFM::dollar($req['payment_amount']) . '</td>
                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . \FFM::date($req['payment_date']) . '</td>
                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . $req['status'] . '</td>
                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . $req['message'] . '</td>
                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">' . $req['type'] . '</td>
                    </tr>';
                    }
                    $html .= '</tbody></table>';
                    $msg['rcode_report_table'] = $html;
                    $msg['title'] = $message['title'];
                    $msg['subject'] = $message['title'];
                    $msg['unqID'] = unqID();
                    $fileName = $message['title'] . '.csv';
                    $msg['status'] = 'ach_rcode_mail';
                    $msg['to_mail'] = $emailArray;
                    $exportCSV = $this->generateACHCheckStatusCSV($rcode_ach);
                    $msg['atatchment'] = $exportCSV;
                    $msg['atatchment_name'] = $fileName;
                    try {
                        $email_template = Template::where([
                            ['temp_code', '=', 'RCOML'], ['enable', '=', 1],
                        ])->first();
                        if ($email_template) {
                            if ($email_template->assignees) {
                                $template_assignee = explode(',', $email_template->assignees);
                                $bcc_mails = [];
                                foreach ($template_assignee as $assignee) {
                                    $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                                    $role_mails = array_diff($role_mails, $emailArray);
                                    $bcc_mails[''] = $role_mails;
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
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
            $result = $data;
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'result' => $result,
            'message' => $message
        ];
    }

    public function doubleCheckAchStatus($req)
    {
        $send_message = [];
        $rcodes = Rcode::pluck('description', 'id')->toArray();
        $type = 'Payment Debit';
        if ($req->is_fees) {
            $type = 'Fee Debit';
        }
        if ($req->ach_status == 1 && $req->payment_status == 1 && $req->order_id) {
            $status = $this->achStatus($req->order_id);
            if (isset($status['curr_bill_status'])) {
                if ($status['curr_bill_status']) {
                    if (Str::contains($status['curr_bill_status'], 'Settled')) {
                        $participant_amount = $req->payment_amount;
                        $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $req->merchant_id, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => 'Accepted Transaction', 'type' => $type, 'status' => 'Success'];
                    } elseif (Str::contains($status['curr_bill_status'], 'Returned')) {
                        $req->update(['status_response' => $status['curr_bill_status'], 'response' => json_encode($status)]);
                        $rcode = 35;
                        $response_rcode = explode('-', $status['curr_bill_status'], 2);
                        if ($response_rcode[1]) {
                            $rcode = array_search(trim($response_rcode[1]), $rcodes);
                            if ($rcode == false) {
                                if (preg_match('#\((.*?)\)#', $response_rcode[1], $match)) {
                                    $rcode = Rcode::where('code', $match[1])->value('id');
                                    if (!$rcode) {
                                        $rcode = 35;
                                    }
                                } else {
                                    $rcode = 35;
                                }
                            }
                        }
                        if ($req->is_fees) {
                            if ($req->velocityFees->count()) {
                                $req->velocityFees()->update(['status' => -1, 'creator_id' => (Auth::user()) ? Auth::user()->id : null]);
                            }
                        } else {
                            if ($rcode) {
                                $debit_status = 'yes';
                                $rcode_code = Rcode::find($rcode)->code;
                                $reason = "ACH debit payment due to Rcode-($rcode_code)" . $response_rcode[1];
                                $mode_of_payment = 1;
                                $add_debitpayment = $this->generateAchPayment($req->merchant_id, $req->payment_date, $req->payment_amount, null, $mode_of_payment, $debit_status, $reason);
                                $add_rcode_payment = $this->generateAchPayment($req->merchant_id, $req->payment_date, 0, $rcode);
                            }
                            $req->schedule()->update(['status' => -1]);
                        }
                        $req->update(['ach_status' => -1, 'payment_status' => -1]);
                        $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $req->merchant_id, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => $status['curr_bill_status'], 'type' => $type, 'status' => 'Returned'];
                        $send_message = ['model_id' => $req->merchant_id, 'mobile' => $req->merchant->cell_phone, 'email' => $req->merchant->notification_email, 'amount' => $req->payment_amount, 'status' => Message::PENDING, 'message' => $this->messageGeneration($req->merchant_id, $req->payment_amount)];
                    } else {
                        $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $req->merchant_id, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => $status['curr_bill_status'], 'type' => $type, 'status' => 'Pending'];
                    }
                }
            } elseif (isset($status['error'])) {
                $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $req->merchant_id, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => $status['error'], 'type' => $type, 'status' => 'Failed'];
            } else {
                $data = ['ach_id' => $req->id, 'schedule_id' => $req->payment_schedule_id, 'merchant_id' => $req->merchant_id, 'merchant_name' => $req->merchant->name, 'payment_amount' => $req->payment_amount, 'payment_date' => $req->payment_date, 'order_id' => $req->order_id, 'data' => $status, 'message' => 'Unknown response.', 'type' => $type, 'status' => 'Failed'];
            }
        }
        if ($send_message) {
            $send_message['model_name'] = \App\Merchant::class;
            $rcode_db = Rcode::find($rcode);
            try {
                $mNotes = [
                    'merchant_id' => $send_message['model_id'],
                    'note' => " \"We recieved an Rcode ($rcode_db->code)-$rcode_db->description from the Merchant on " . \FFM::datetime(Carbon::now()) . '"',
                    'added_by' => Auth::user()->name,
                ];
                $this->mNotes->createRequest($mNotes);
                $MerchantEmail['title'] = 'We noticed you missed a payment...';
                $MerchantEmail['status'] = 'merchant_returnd';
                $MerchantEmail['unqID'] = unqID();
                $MerchantEmail['subject'] = $MerchantEmail['title'];
                $MerchantEmail['merchant_id'] = $req->merchant_id;
                $MerchantEmail['merchant_name'] = $req->merchant->name;
                $MerchantEmail['amount'] = $req->payment_amount;
                $MerchantEmail['to_mail'] = $send_message['email'];
                if ($MerchantEmail['to_mail']) {
                    try {
                        $values = ['title' => $MerchantEmail['title'], 'type' => 2, 'to_mail' => $MerchantEmail['to_mail'], 'to_user_type' => 'merchant', 'to_id' => $req->merchant_id, 'to_name' => $req->merchant->name, 'status' => 'success'];
                    } catch (Exception $e) {
                        $values = ['title' => $MerchantEmail['title'], 'type' => 2, 'to_mail' => $MerchantEmail['to_mail'], 'to_user_type' => 'merchant', 'to_id' => $req->merchant_id, 'to_name' => $req->merchant->name, 'status' => 'failed', 'failed_message' => $e->getMessage()];
                    }
                } else {
                    $values = ['title' => $MerchantEmail['title'], 'type' => 2, 'to_mail' => '-', 'status' => 'failed', 'to_user_type' => 'merchant', 'to_id' => $req->merchant_id, 'to_name' => $req->merchant->name, 'failed_message' => 'email is null'];
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        return $data;
    }

    public function achFeesData($request)
    {
        $requestData=[];
        if ($request->from_date) {
            $requestData['from_date'] = $request->from_date;
        }
        if ($request->to_date) {
            $requestData['to_date'] = $request->to_date;
        }
        if (isset($request->status)) {
            $requestData['status'] = $request->status;
        }
        if ($request->type) {
            $requestData['type'] = $request->type;
        }
        if ($request->merchants_id) {
            $requestData['merchants_id'] = $request->merchants_id;
        }
        $fees = MTB::getAchFeesDataTable($requestData);

        return $fees;
    }

    public function achFees($tableBuilder)
    {
        $requestData = [];
        $tableBuilder->ajax(['url' => route('admin::payments::ach-fees.datatable'), 'type' => 'post', 'data' => 'function(data){
                data._token              = "' . csrf_token() . '";
                data.merchants_id         = $("#merchants_id").val();
                data.from_date           = $("#from_date").val();
                data.to_date             = $("#to_date").val();
                data.type             = $("#type").val();
                data.status  = $("#status").val();
            }']);
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(4).footer()).html(o.total_amount) }', 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns(MTB::getAchFeesDataTable($requestData));
        $page_title = 'Merchant ACH Fees';
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $merchants_id = Merchant::whereNotIn('sub_status_id', $unwanted_sub_status)->pluck('name', 'id');
        $statuses = [2 => 'Processing', 1 => 'Settled', -1 => 'Returned'];
        $fee_types = config('custom.ach_fee_types');

        return [
            'page_title' => $page_title,
            'tableBuilder' => $tableBuilder,
            'merchants_id' => $merchants_id,
            'statuses' => $statuses,
            'fee_types' => $fee_types
        ];
    }

    /**
     * ACH Fees export function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function achFeesExport($request)
    {
        $requestData = [];
        if ($request->from_date) {
            $requestData['from_date'] = $request->from_date;
        }
        if ($request->to_date) {
            $requestData['to_date'] = $request->to_date;
        }
        if (isset($request->status)) {
            $requestData['status'] = $request->status;
        }
        if ($request->type) {
            $requestData['type'] = $request->type;
        }
        if ($request->merchants_id) {
            $requestData['merchants_id'] = $request->merchants_id;
        }
        $response = MTB::getAchFees($requestData);
        $data = $response['data'];
        $total_payments = 0;
        foreach ($data as $key => $total) {
            $total_payments = $total_payments + $total->payment_amount;
        }
        $total_payments = FFM::dollar($total_payments);
        $fileName = 'Merchant ACH Fees Report ' . FFM::datetimeExcel(date('Y-m-d H:i:s')) . '.csv';
        $excel_array[] = ['No', 'Merchant Name', 'Merchant ID', 'Order ID', 'Amount', 'Payment Date', 'Type', 'Status', 'Created At'];
        $i = 1;
        foreach ($data as $key => $tr) {
            $excel_array[$i]['No'] = $i;
            $excel_array[$i]['Merchant Name'] = $tr->merchant_name;
            $excel_array[$i]['Merchant ID'] = $tr->merchant_id;
            $excel_array[$i]['Order ID'] = $tr->order_id;
            $excel_array[$i]['Payment'] = FFM::dollar($tr->payment_amount);
            $excel_array[$i]['Payment Date'] = FFM::date($tr->payment_date);
            $excel_array[$i]['Type'] = $tr->type;
            $excel_array[$i]['Status'] = $tr->status;
            $excel_array[$i]['Created At'] = FFM::datetime($tr->created_at);
            $i++;
        }
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['Merchant Name'] = null;
        $excel_array[$i]['Merchant ID'] = null;
        $excel_array[$i]['Order ID'] = 'TOTAL';
        $excel_array[$i]['Payment'] = $total_payments;
        $export = new Data_arrExport($excel_array);

        return [
            'export' => $export,
            'fileName' => $fileName
        ];
    }

    public function actumApi($params)
    {
        $url = 'https://join.actumprocessing.com/cgi-bin/dbs/man_trans.cgi';
        $request = Http::asForm()->post($url, $params);
        $response = $request->body();
        $response = preg_split('/$\R?^/m', $response);
        $a = [];
        foreach ($response as $r) {
            $a1 = explode('=', $r);
            $a[$a1[0]] = $a1[1];
        }

        return $a;
    }

    public function debitRequest($request)
    {
        $params = ['parent_id' => config('settings.actum_parent_id_merchant'), 'sub_id' => config('settings.actum_sub_id_merchant'), 'pmt_type' => 'chk', 'chk_acct' => $request['chk_acct'], 'chk_aba' => $request['chk_aba'], 'custname' => $request['custname'], 'custphone' => $request['custphone'], 'initial_amount' => $request['initial_amount'], 'billing_cycle' => '-1', 'merordernumber' => 'D-' . md5(uniqid(rand(), true))];

        return $this->actumApi($params);
    }

    public function debitRequestSameDay()
    {
        $params = ['parent_id' => config('settings.actum_parent_id_merchant'), 'sub_id' => config('settings.actum_sub_id_merchant'), 'pmt_type' => 'chk', 'chk_acct' => '521743622', 'chk_aba' => '999999999', 'custname' => 'Bob Yakuza', 'custphone' => '8935550893', 'initial_amount' => '10.00', 'billing_cycle' => '-1', 'merordernumber' => 'DS-' . md5(uniqid(rand(), true)), 'trans_modifier' => 'S'];

        return $this->actumApi($params);
    }

    public function creditRequest($request)
    {
        $params = ['parent_id' => config('settings.actum_parent_id_merchant'), 'sub_id' => config('settings.actum_sub_id_merchant'), 'pmt_type' => 'chk', 'chk_acct' => $request['chk_acct'], 'chk_aba' => $request['chk_aba'], 'custname' => $request['custname'], 'custphone' => $request['custphone'], 'initial_amount' => $request['initial_amount'], 'billing_cycle' => '-1', 'merordernumber' => 'C-' . md5(uniqid(rand(), true)), 'action_code' => 'P', 'creditflag' => '1'];

        return $this->actumApi($params);
    }

    public function creditRequestSameDay()
    {
        $params = ['parent_id' => config('settings.actum_parent_id_merchant'), 'sub_id' => config('settings.actum_sub_id_merchant'), 'pmt_type' => 'chk', 'chk_acct' => '521743622', 'chk_aba' => '999999999', 'custname' => 'Bob Yakuza', 'custphone' => '8935550893', 'initial_amount' => '10.00', 'billing_cycle' => '-1', 'merordernumber' => 'CS-' . md5(uniqid(rand(), true)), 'trans_modifier' => 'S', 'action_code' => 'P', 'creditflag' => '1'];

        return $this->actumApi($params);
    }

    public function achStatus($order_id)
    {
        $params = ['username' => config('settings.actum_username_merchant'), 'password' => config('settings.actum_password_merchant'), 'action_code' => 'A', 'order_id' => $order_id];

        return $this->actumApi($params);
    }

    public function investorAchRequestsData($request)
    {
        $InvestorTableBuilder = new InvestorTableBuilder($this->investor);
        $requestData = [];
        if ($request->investor_id) {
            $requestData['investor_id'] = $request->investor_id;
        }
        if ($request->from_date) {
            $requestData['from_date'] = $request->from_date;
        }
        if ($request->to_date) {
            $requestData['to_date'] = $request->to_date;
        }
        if ($request->order_id) {
            $requestData['order_id'] = $request->order_id;
        }
        if ($request->transaction_type) {
            $requestData['transaction_type'] = $request->transaction_type;
        }
        if ($request->ach_request_status) {
            $requestData['ach_request_status'] = $request->ach_request_status;
        }
        if ($request->transaction_method) {
            $requestData['transaction_method'] = $request->transaction_method;
        }
        if ($request->ach_status) {
            $requestData['ach_status'] = $request->ach_status;
        }
        $data = $InvestorTableBuilder->getInvestorAchRequestAll($requestData);

        return $data;
    }

    public function investorAchRequests($tableBuilder)
    {
        $page_title = 'Investor ACH Status Check';
        $page_description = 'Investor ACH Status Check';
        $investors = $this->role->allInvestors()->pluck('name', 'id')->toArray();
        $InvestorTableBuilder = new InvestorTableBuilder($this->investor);
        $tableBuilder->ajax(['url' => route('admin::payments::investor-ach-requests.datatable'), 'type' => 'post', 'data' => 'function(data){
                data._token              = "' . csrf_token() . '";
                data.investor_id         = $("#investor_id").val();
                data.order_id            = $("#order_id").is(":checked")?1:0;
                data.from_date           = $("#from_date").val();
                data.to_date             = $("#to_date").val();
                data.ach_request_status  = $("#ach_request_status").val();
                data.transaction_type    = $("#transaction_type").val();
                data.transaction_method  = $("#transaction_method").val();
                data.ach_status          = $("#ach_status").val();
            }']);
        $tableBuilder->parameters(['order' => [[1, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($InvestorTableBuilder->getInvestorAchRequestAll($requestData));

        return [
            'tableBuilder' => $tableBuilder,
            'page_title' => $page_title,
            'investors' => $investors,
            'page_description' => $page_description
        ];
    }

    /**
     * Investor ACH requests export function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function investorAchRequestsExport($request)
    {
        $requestData = [];
        if ($request->investor_id) {
            $requestData['investor_id'] = $request->investor_id;
        }
        if (isset($request->transaction_type)) {
            $requestData['transaction_type'] = $request->transaction_type;
        }
        if (isset($request->transaction_method)) {
            $requestData['transaction_method'] = $request->transaction_method;
        }
        if (isset($request->order_id)) {
            $requestData['order_id'] = $request->order_id;
        }
        if (isset($request->ach_request_status)) {
            $requestData['ach_request_status'] = $request->ach_request_status;
        }
        if (isset($request->ach_status)) {
            $requestData['ach_status'] = $request->ach_status;
        }
        if (isset($request->from_date)) {
            $requestData['from_date'] = $request->from_date;
        }
        if (isset($request->to_date)) {
            $requestData['to_date'] = $request->to_date;
        }
        $data = $this->investor->getInvestorAchRequestAll($requestData);
        $datas = $data['data']->orderBy('date', 'desc')->get();
        $excel_array[] = ['#', 'Date', 'Investor', 'Order Id', 'Transaction Type', 'Transaction Method', 'Transaction Category', 'Amount', 'Request Status', 'Settlement Status', 'Auth Code', 'Status Response'];
        $i = 1;
        foreach ($datas as $key => $row) {
            $single['#'] = $key + 1;
            $single['Date'] = $row->date;
            $single['Investor'] = $row->Investor;
            $single['Order Id'] = $row->order_id;
            $single['Transaction Type'] = $row->InvertedTransactionTypeName;
            $single['Transaction Method'] = $row->TransactionMethodName;
            $single['Transaction Category'] = $row->TransactionCategoryName;
            $single['Amount'] = $row->amount;
            $single['Request Status'] = $row->AchStatusName;
            $single['Settlement Status'] = $row->AchRequestStatusName;
            $single['Auth Code'] = $row->auth_code;
            $single['Status Response'] = $row->status_response;
            $excel_array[] = $single;
        }
        $fileName = 'Investor ACH Requests ' . FFM::datetimeExcel(date('Y-m-d H:i:s')) . '.csv';
        $export = new Data_arrExport($excel_array);

        return [
            'export' => $export,
            'fileName' => $fileName
        ];
    }

    /**
     * Investors's ACH single payment status check function.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function investorAchCheckSingleStatus($id)
    {
        $return['status'] = false;
        try {
            $single = InvestorAchRequest::find($id);
            $return_function = $this->checkInvestorAchStatus($single);
            if ($return_function['result'] != 'success') {
                throw new Exception($return_function['result'], 1);
            }
            $return['data'] = $return_function['data'] ?? '';
            DB::commit();
            if ($return_function['data']['investor_ach_request_id']) {
                $mail = $this->InvestorSettlementProcessingRequestMailFunction([$return_function['data']['investor_ach_request_id']]);
            }
            if ($return_function['data']['status'] != 'success') {
                $return['result'] = $return_function['data']['message'];
            } else {
                $return['result'] = 'success';
                $status = true;
            }
        } catch (Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }

    public function checkInvestorAchStatus($single)
    {
        try {
            $ActumRequest = new ActumRequest;
            $send_message = [];
            $rcodes = Rcode::pluck('description', 'id')->toArray();
            $type = 'Payment';
            if ($single->order_id) {
                if ($single->transaction_type == 'credit' || $single->transaction_type == 'same_day_credit') {
                    $actum_type = 'to_investor';
                } else {
                    $actum_type = 'from_investor';
                }
                $status = $ActumRequest->statusCheck($single->order_id, $actum_type);
                if (isset($status['curr_bill_status'])) {
                    $single->status_response = $status['curr_bill_status'];
                } else {
                    $single->status_response = $status['error'];
                }
                $single->response = json_encode($status);
                $single->save();
            } else {
                DB::beginTransaction();
                $data = ['transaction_type' => $single->transaction_type, 'request_ip_address' => Auth::ip(), 'bank_id' => '', 'investor_id' => $single->investor_id, 'transaction_method' => $single->transaction_method, 'transaction_category' => $single->transaction_category, 'amount' => $single->amount, 'InvestorAchRequest_ID' => $single->id];
                $return_result = $ActumRequest->RequestHandler($data);
                if ($return_result['InvestorAchRequest'] != 'updated') {
                    DB::rollback();
                }
                DB::commit();
                if ($return_result['result'] != 'success') {
                    throw new \Exception($return_result['result'], 1);
                }
            }
            $data = ['investor_ach_request_id' => $single->id, 'type' => $type];
            switch (true) {
                case $single->order_id:
                    switch ($single->ach_request_status) {
                        case InvestorAchRequest::AchRequestStatusAccepted:
                            $data['message'] = 'Request Already Accepted';
                            $data['status'] = 'Failed';
                            break;
                        default:
                            $data['data'] = $status;
                            $data += $this->InvestorAchStatusCheck($single, $status);
                            break;
                    }
                    break;
                default:
                    $data['message'] = 'Declined Request';
                    $data['status'] = 'Failed';
                    break;
            }
            $return['result'] = 'success';
            $return['data'] = $data;
        } catch (Exception $e) {
            $return['result'] = $e->getMessage();
            $single->update(['status_response' => $return['result']]);
            $return['result'] = 'success';
        }

        return $return;
    }

    public function investorAchCheckAchRequestStatus($request)
    {
        try {
            $InvestorAchRequest = InvestorAchRequest::orderby('id');
            $InvestorAchRequest->where('ach_status', '!=', InvestorAchRequest::AchStatusDeclined);
            $InvestorAchRequest->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing);
            if ($request['from_date']) {
                $InvestorAchRequest->where('date', '>=', $request['from_date']);
            }
            if ($request['to_date']) {
                $InvestorAchRequest->where('date', '<=', $request['to_date']);
            }
            $InvestorAchRequest = $InvestorAchRequest->get();
            $investor_ach_request_ids = [];
            foreach ($InvestorAchRequest as $single) {
                $return_function = $this->checkInvestorAchStatus($single);
                if ($return_function['result'] != 'success') {
                    throw new Exception($return_function['result'], 1);
                }
                if ($return_function['data']['investor_ach_request_id']) {
                    $investor_ach_request_ids[] = $return_function['data']['investor_ach_request_id'];
                }
            }
            $return['result'] = 'success';
            if ($investor_ach_request_ids) {
                $return += $this->InvestorSettlementProcessingRequestMailFunction($investor_ach_request_ids);
            }
        } catch (Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function achCheckStatusAutomatic()
    {
        $InvestorAchRequest = InvestorAchRequest::orderby('id');
        $InvestorAchRequest->where('ach_status', '!=', InvestorAchRequest::AchStatusDeclined);
        $InvestorAchRequest->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing);
        $InvestorAchRequest = $InvestorAchRequest->get();
        $investor_ach_request_ids = [];
        try {
            foreach ($InvestorAchRequest as $single) {
                DB::beginTransaction();
                $return_function = $this->checkInvestorAchStatus($single);
                if ($return_function['result'] != 'success') {
                    throw new Exception($return_function['result'], 1);
                }
                if ($return_function['data']['investor_ach_request_id']) {
                    $investor_ach_request_ids[] = $return_function['data']['investor_ach_request_id'];
                }
                DB::commit();
            }
            $return['result'] = 'success';
        } catch (Exception $e) {
            DB::rollback();
            $return['result'] = $e->getMessage();
        }
        if ($investor_ach_request_ids) {
            $return += $this->InvestorSettlementProcessingRequestMailFunction($investor_ach_request_ids);
        }

        return response()->json($return, 200);
    }

    public function removeInvestorACHPending()
    {
        $InvestorAchRequests = InvestorAchRequest::orderby('id');
        $InvestorAchRequests->where('ach_status', '!=', InvestorAchRequest::AchStatusDeclined);
        $InvestorAchRequests->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing);
        $InvestorAchRequests = $InvestorAchRequests->get();
        $today = Carbon::now()->toDateString();
        $investor_ach_request_ids = [];
        $count = 0;
        foreach ($InvestorAchRequests as $request) {
            $cutoff_date = PayCalc::getNthWorkingDay($request->date, 10);
            if ($cutoff_date <= $today) {
                $investor_ach_request_ids[] = $request->id;
                $count++;
            }
        }
        $status = false;
        if ($count) {
            $status = true;
            $mail = $this->InvestorProcessingACHRequestDeleteMailFunction($investor_ach_request_ids, $today);
        }
        $return['count'] = $count;
        $return['status'] = $status;

        return $return;
    }

    public function InvestorProcessingACHRequestDeleteMailFunction($investor_ach_request_ids, $today)
    {
        $date = FFM::date($today);
        $CSV = $this->InvestorAchRequestGenerateCSV($investor_ach_request_ids);
        $emails = Settings::where('keys', 'system_admin')->value('values');
        $emailArray = explode(',', $emails);
        $msg['title'] = 'Investor ACH Processing For More Than 9 Days as of ' . $date;
        $exportCSV = $CSV['export'];
        $fileName = $msg['title'] . '.csv';
        $msg['atatchment'] = $exportCSV;
        $msg['atatchment_name'] = $fileName;
        $msg['totalCount'] = $CSV['count'];
        $msg['to_mail'] = $emailArray;
        $msg['status'] = 'pending_ach_delete_mail';
        $msg['subject'] = $msg['title'];
        $msg['date'] = $date;
        $msg['checked_time'] = FFM::datetime(Carbon::now());
        $msg['unqID'] = unqID();
        $array_params['date'] = $today;
        $array_params['ach_ids'] = $investor_ach_request_ids;
        $array_params['status'] = 1;
        $encoded_array = urlencode(serialize($array_params));
        $confirm_url = route('admin::payments::investor-ach-status.delete-verification', $encoded_array);
        $msg['confirm_url'] = $confirm_url;
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'ACDP'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
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
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $msg;
    }

    public function InvestorAchStatusCheck($single, $status)
    {
        try {
            switch (true) {
                case isset($status['curr_bill_status']):
                    switch (true) {
                        case Str::contains($status['curr_bill_status'], 'PreAuth'):
                            $single->update(['ach_request_status' => InvestorAchRequest::AchRequestStatusProcessing]);
                            $return['message'] = $status['curr_bill_status'];
                            $return['status'] = 'Processing';
                            break;
                        case Str::contains($status['curr_bill_status'], 'Settled') || Str::contains($status['curr_bill_status'], 'Credit'):
                            if (Str::contains($status['curr_bill_status'], 'Credit')) {
                                if ($single->transaction_type == 'credit') {
                                    $start_date = $single->date;
                                    $end_date = Carbon::now()->tz('America/New_York')->toDateString();
                                    $no_of_days_passed = PayCalc::calculateWorkingDaysCount($start_date, $end_date);

                                    $ach_investor = (Settings::where('keys', 'ach_investor')->value('values'));
                                    $ach_investor = json_decode($ach_investor, true);
                                    $ach_investor_credit_lag_days = $ach_investor['ach_investor_credit_lag_days'] ?? 2;

                                    if ($no_of_days_passed < $ach_investor_credit_lag_days) {
                                        $single->update(['ach_request_status' => InvestorAchRequest::AchRequestStatusProcessing]);
                                        $return['message'] = $status['curr_bill_status'];
                                        $return['status'] = 'Processing';
                                        break;
                                    }
                                }
                            }
                            $single->update(['ach_request_status' => InvestorAchRequest::AchRequestStatusAccepted]);
                            $return['message'] = 'Accepted Transaction';
                            $return['status'] = 'success';
                            $transactionData = [
                                'account_no' => $single->Bank ? $single->Bank->acc_number : null,
                                'investor_id' => $single->investor_id,
                                'date' => date('Y-m-d'),
                                'amount' => $single->amount,
                                'creator_id' => $single->creator_id ?? ((Auth::check()) ? Auth::user()->id : null),
                                'transaction_method' => $single->transaction_method,
                                'transaction_category' => $single->transaction_category,
                                'category_notes' => $single->TransactionMethodName
                            ];
                            if (in_array($single->transaction_type, ['credit', 'same_day_credit'])) {
                                $transactionData['transaction_type'] = 1;
                            } else {
                                $transactionData['transaction_type'] = 2;
                            }
                            $return_function = InvestorHelper::insertTransactionFunction($transactionData);
                            if ($return_function['result'] != 'success') {
                                throw new Exception($return_function['result'], 1);
                            }
                            $single->update(['transaction_id' => $return_function['transaction_id']]);
                            $UserDetails = UserDetails::where('user_id', $single->investor_id)->first();
                            $message['title'] = 'Ach ' . $single->TransactionTypeName . ' Request Settled';
                            $message['subject'] = $message['title'];
                            $message['investor_name'] = $single->Investor->name;
                            $message['investor_id'] = $single->investor_id;
                            $message['amount'] = $single->amount;
                            $message['type'] = $single->TransactionTypeName;
                            $message['date'] = FFM::date($single->date);
                            $message['Creator'] = 'Admin';
                            $message['liquidity'] = FFM::dollar($UserDetails->liquidity);
                            $message['to_mail'] = $single->Investor->notification_email;
                            $message['status'] = 'investor_ach_request_settlement';
                            if ($message['to_mail']) {
                                $email_template = Template::where([
                                    ['temp_code', '=', 'ACSR'], ['enable', '=', 1],
                                ])->first();
                                if ($email_template) {
                                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                                    dispatch($emailJob);
                                    $message['to_mail'] = $this->admin_email;
                                    $emailJob = (new CommonJobs($message));
                                    dispatch($emailJob);
                                }
                            }
                            break;
                        case Str::contains($status['curr_bill_status'], 'Returned'):
                            $single->update(['ach_request_status' => InvestorAchRequest::AchRequestStatusDeclined]);
                            $return['message'] = $status['curr_bill_status'];
                            $return['status'] = 'Returned';
                            break;
                        default:
                            $return['message'] = $status['curr_bill_status'];
                            $return['status'] = 'Failed';
                            break;
                    }
                    break;
                case isset($status['error']):
                    $return['message'] = $status['error'];
                    $return['status'] = 'Failed';
                    break;
                default:
                    $return['message'] = 'Unknown response.';
                    $return['status'] = 'Failed';
                    break;
            }
            $return['result'] = 'success';
        } catch (Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function InvestorSettlementProcessingRequestMailFunction($investor_ach_request_ids)
    {
        $date = FFM::date(date('Y-m-d'));
        $CSVResponds = $this->InvestorAchRequestGenerateCSV($investor_ach_request_ids);
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $msg['title'] = 'Investor ACH Processing Report For ' . $date;
        $exportCSV = $CSVResponds['export'];
        $fileName = $msg['title'] . '.csv';
        $msg['atatchment'] = $exportCSV;
        $msg['atatchment_name'] = $fileName;
        $msg['totalCount'] = $CSVResponds['count'];
        $msg['processedCount'] = $CSVResponds['processedCount'];
        $msg['to_mail'] = $emailArray;
        $msg['status'] = 'investor_ach_request_send_report';
        $msg['subject'] = $msg['title'];
        $msg['date'] = $date;
        $msg['checked_time'] = FFM::datetime(Carbon::now());
        $msg['unqID'] = unqID();
        $msg['debitAcceptedAmount'] = FFM::dollar($CSVResponds['debitAcceptedAmount']);
        $msg['creditAcceptedAmount'] = FFM::dollar($CSVResponds['creditAcceptedAmount']);
        $msg['debitProcessingAmount'] = FFM::dollar($CSVResponds['debitProcessingAmount']);
        $msg['creditProcessingAmount'] = FFM::dollar($CSVResponds['creditProcessingAmount']);
        $msg['debitReturnedAmount'] = FFM::dollar($CSVResponds['debitReturnedAmount']);
        $msg['creditReturnedAmount'] = FFM::dollar($CSVResponds['creditReturnedAmount']);
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'IAPR'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $emailArray);
                        $bcc_mails[] = $role_mails;
                    }
                    $msg['bcc'] = Arr::flatten($bcc_mails);
                }
                $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                dispatch($emailJob);
                $msg['to_mail'] = $this->admin_email;
                $emailJob = (new CommonJobs($msg));
                dispatch($emailJob);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $msg;
    }

    public function InvestorAchRequestGenerateCSV($investor_ach_request_ids)
    {
        $data = InvestorAchRequestView::whereIn('id', $investor_ach_request_ids)->get(['Investor', 'amount', 'date', 'ach_request_status', 'status_response', 'transaction_type']);
        $debitAcceptedAmount = InvestorAchRequestView::whereIn('id', $investor_ach_request_ids)->where('ach_request_status', InvestorAchRequest::AchRequestStatusAccepted)->whereIn('transaction_type', ['debit', 'same_day_debit'])->sum('amount');
        $creditAcceptedAmount = InvestorAchRequestView::whereIn('id', $investor_ach_request_ids)->where('ach_request_status', InvestorAchRequest::AchRequestStatusAccepted)->whereIn('transaction_type', ['credit', 'same_day_credit'])->sum('amount');
        $debitProcessingAmount = InvestorAchRequestView::whereIn('id', $investor_ach_request_ids)->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['debit', 'same_day_debit'])->sum('amount');
        $creditProcessingAmount = InvestorAchRequestView::whereIn('id', $investor_ach_request_ids)->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['credit', 'same_day_credit'])->sum('amount');
        $debitReturnedAmount = InvestorAchRequestView::whereIn('id', $investor_ach_request_ids)->where('ach_request_status', InvestorAchRequest::AchRequestStatusDeclined)->whereIn('transaction_type', ['debit', 'same_day_debit'])->sum('amount');
        $creditReturnedAmount = InvestorAchRequestView::whereIn('id', $investor_ach_request_ids)->where('ach_request_status', InvestorAchRequest::AchRequestStatusDeclined)->whereIn('transaction_type', ['credit', 'same_day_credit'])->sum('amount');
        $processedCount = InvestorAchRequestView::whereIn('id', $investor_ach_request_ids)->whereIn('ach_request_status', [InvestorAchRequest::AchRequestStatusAccepted, InvestorAchRequest::AchRequestStatusDeclined])->whereIn('transaction_type', ['credit', 'same_day_credit', 'debit', 'same_day_debit'])->count();
        $excel_array[] = ['#', 'Investor', 'Amount', 'Date', 'Status', 'Response', 'Type'];
        $i = 1;
        foreach ($data as $key => $row) {
            $single['#'] = $key + 1;
            $single['Investor'] = $row->Investor;
            $single['Amount'] = $row->amount;
            $single['Date'] = $row->date;
            $single['Status'] = $row->AchRequestStatusName;
            $single['Response'] = $row->status_response;
            $single['Type'] = ucfirst($row->transaction_type);
            $excel_array[] = $single;
        }
        $export = new Data_arrExport($excel_array);

        return ['export' => $export, 'count' => $key + 1, 'debitAcceptedAmount' => $debitAcceptedAmount, 'creditAcceptedAmount' => $creditAcceptedAmount, 'debitProcessingAmount' => $debitProcessingAmount, 'creditProcessingAmount' => $creditProcessingAmount, 'debitReturnedAmount' => $debitReturnedAmount, 'creditReturnedAmount' => $creditReturnedAmount, 'processedCount' => $processedCount];
    }

    /**
     * Function to recheck confirmed investor ACH transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function investorAchReCheckStatus()
    {
        $current_time = Carbon::now()->timezone('America/New_york');
        $current_time_formatted = FFM::datetime($current_time->toDateTimeString());
        $today = $current_time->toDateString();
        $investor_ach_requests = InvestorAchRequest::where('ach_request_status', InvestorAchRequest::AchRequestStatusAccepted)
            ->orderby('id', 'ASC')
            ->get();
        $data = $investor_ach_request_ids = [];

        $ach_investor = (Settings::where('keys', 'ach_investor')->value('values'));
        $ach_investor = json_decode($ach_investor, true);
        $ach_investor_double_check_lag_days = $ach_investor['ach_investor_double_check_lag_days'] ?? 5;

        foreach ($investor_ach_requests as $investor_ach_request) {
            $cutoff_date = PayCalc::getNthWorkingDay($investor_ach_request->date, $ach_investor_double_check_lag_days);
            if ($cutoff_date >= $today) {
                echo "checking Investor ACH $investor_ach_request->id with p.d $investor_ach_request->date and c.d $cutoff_date \n";
                $check_status = $this->investorAchReCheckStatusSingle($investor_ach_request);
                if ($check_status['status'] == 'Returned') {
                    echo " Investor ACH $investor_ach_request->id with p.d $investor_ach_request->date is returned \n";
                }
                $investor_ach_request_ids[] = $investor_ach_request->id;

                $data[] = $check_status;
            }
        }
        if ($investor_ach_request_ids) {
            $title = 'Investor ACH Recheck Report For ';
            $this->InvestorAchRechecktMailFunction($investor_ach_request_ids, $title);
        }
        return $data;
    }

    /*
    Function to recheck individual settled investor ACH transaction.
    */
    public function investorAchReCheckStatusSingle($single)
    {
        try {
            DB::beginTransaction();
            $ActumRequest = new ActumRequest;
            $rcodes = Rcode::pluck('description', 'id')->toArray();
            $type = 'Payment';
            if ($single->transaction_type == 'credit' || $single->transaction_type == 'same_day_credit') {
                $actum_type = 'to_investor';
            } else {
                $actum_type = 'from_investor';
            }
            $status = $ActumRequest->statusCheck($single->order_id, $actum_type);
            switch (true) {
                case isset($status['curr_bill_status']):
                    switch (true) {
                        case Str::contains($status['curr_bill_status'], 'Returned'):
                            $single->update([
                                'response' => json_encode($status),
                                'status_response' => $status['curr_bill_status'],
                                'ach_request_status' => InvestorAchRequest::AchRequestStatusDeclined
                            ]);

                            $return['message'] = $status['curr_bill_status'];
                            $return['status'] = 'Returned';

                            if (in_array($single->transaction_type, ['credit', 'same_day_credit'])) {
                                $transaction_type = 1;
                                $amount = -1 * abs($single->amount);
                            } else {
                                $transaction_type = 2;
                                $amount = $single->amount;
                            }
                            $user_id = $single->investor_id;

                            if ($single->transaction_id) {
                                $transaction = InvestorTransaction::find($single->transaction_id);
                                if ($transaction && $transaction->status == InvestorTransaction::StatusCompleted) {
                                    $transaction->update(['status' => InvestorTransaction::StatusReturned]);
                                    $description = 'ACH Returned';
                                    InvestorHelper::update_liquidity($user_id, $description);
                                }
                            }
                            $mail = $this->InvestorAchReturnedMailFunction($single);
                            break;
                        default:
                            $return['message'] = $status['curr_bill_status'];
                            $return['status'] = 'No changes';
                            break;
                    }
                    break;
                case isset($status['error']):
                    $return['message'] = $status['error'];
                    $return['status'] = 'Failed';
                    break;
                default:
                    $return['message'] = 'Unknown response.';
                    $return['status'] = 'Failed';
                    break;
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $return['status'] = $e->getMessage();
        }
        return $return;
    }

    /*
    Function to mail total report with attachment after investor ACH transaction recheck.
    */
    public function InvestorAchRechecktMailFunction($investor_ach_request_ids, $title)
    {
        $date = FFM::date(date('Y-m-d'));
        $CSVResponds = $this->InvestorAchRequestGenerateCSV($investor_ach_request_ids);
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $msg['title'] = $title . $date;
        $exportCSV = $CSVResponds['export'];
        $fileName = $msg['title'] . '.csv';
        $msg['atatchment'] = $exportCSV;
        $msg['atatchment_name'] = $fileName;
        $msg['totalCount'] = $CSVResponds['count'];
        $msg['to_mail'] = $emailArray;
        $msg['status'] = 'investor_ach_recheck_report';
        $msg['subject'] = $msg['title'];
        $msg['date'] = $date;
        $msg['checked_time'] = FFM::datetime(Carbon::now());
        $msg['unqID'] = unqID();
        $msg['debitAcceptedAmount'] = FFM::dollar($CSVResponds['debitAcceptedAmount']);
        $msg['creditAcceptedAmount'] = FFM::dollar($CSVResponds['creditAcceptedAmount']);
        $msg['debitReturnedAmount'] = FFM::dollar($CSVResponds['debitReturnedAmount']);
        $msg['creditReturnedAmount'] = FFM::dollar($CSVResponds['creditReturnedAmount']);
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'IARR'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
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
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $msg;
    }

    /*
    Function to mail invidiual returned report to investor after investor ACH transaction returned.
    */
    public function InvestorAchReturnedMailFunction($ach)
    {
        $UserDetails = UserDetails::where('user_id', $ach->investor_id)->first();
        $message['title'] = 'Ach ' . $ach->TransactionTypeName . ' Request Returned';
        $message['subject'] = $message['title'];
        $message['investor_name'] = $ach->Investor->name;
        $message['investor_id'] = $ach->investor_id;
        $message['amount'] = $ach->amount;
        $message['type'] = $ach->TransactionTypeName;
        $message['date'] = FFM::date($ach->date);
        $message['Creator'] = 'Admin';
        $message['liquidity'] = FFM::dollar($UserDetails->liquidity);
        $message['to_mail'] = $ach->Investor->notification_email;
        $message['status'] = 'investor_ach_request_returned';
        if ($message['to_mail']) {
            try {
                $email_template = Template::where([
                    ['temp_code', '=', 'ACRR'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $bcc_mails[] = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
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
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        return $message;
    }

    public function changeAutoAchStatusMerchant($request)
    {
        $status = false;
        try {
            $message = 'Auto ACH Status of ';
            $merchant = Merchant::find($request->merchant_id);
            if ($merchant) {
                $ach_pull = $merchant->ach_pull;
                if ($ach_pull) {
                    $merchant->ach_pull = 0;
                    $merchant->update();
                    $changed = ' turned OFF';
                } else {
                    if ($merchant->bankAccounts()->count()) {
                        $merchant->ach_pull = 1;
                        $merchant->update();
                        $changed = ' turned ON';
                    } else {
                        throw new Exception('Auto ACH Status of not changed since no linked bank Account.', 1);
                    }
                }
                $message .= $merchant->name . $changed;
                $status = true;
                DB::commit();
            } else {
                throw new Exception('No merchant found', 1);
            }
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
        }
        return [
            'message' => $message,
            'status' => $status
        ];
    }

    public function removeInvestorACHPendingVerification($data, $request)
    {
        $input_array = unserialize((urldecode($data)));
        $request_date = $input_array['date'];
        $ach_ids = $input_array['ach_ids'];
        $status = $input_array['status'];
        $InvestorAchRequestsPending = InvestorAchRequest::where('ach_status', '!=', InvestorAchRequest::AchStatusDeclined)->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing)->whereIn('id', $ach_ids);
        $pending_count = $InvestorAchRequestsPending->count();
        $count = 0;
        $page_title = 'Investor ACH Processing For More Than 9 Days';
        if ($pending_count) {
            $description = 'There are ' . $pending_count . ' Investor ACH processing transactions for more than 9 days as of ' . FFM::date($input_array['date']) . '. Click the following link to delete it.';
        } else {
            $ach_ids = null;
            $description = 'There are No pending ACH to delete as of ' . FFM::date($input_array['date']) . '.';
        }
        return [
            'page_title' => $page_title,
            'description' => $description,
            'ach_ids' => $ach_ids,
            'request_date' => $request_date
        ];
    }

    public function removeInvestorACHPendingFunction($request)
    {
        $status = false;
        try {
            $ach_ids = $request->ach_ids;
            $request_date = $request->request_date;
            $InvestorAchRequestsPending = InvestorAchRequest::where('ach_status', '!=', InvestorAchRequest::AchStatusDeclined)->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing)->whereIn('id', $ach_ids);
            $pending_count = $InvestorAchRequestsPending->count();
            $count = 0;
            if ($pending_count) {
                $InvestorAchRequestsPending = $InvestorAchRequestsPending->get();
                foreach ($InvestorAchRequestsPending as $ach) {
                    $cutoff_date = PayCalc::getNthWorkingDay($ach->date, 10);
                    if ($cutoff_date <= $request_date) {
                        $ach->delete();
                        $count++;
                    }
                }
            }
            if ($count > 0) {
                $message = "$count pending ACH deleted.";
            } else {
                throw new Exception('No pending ACH to delete.', 1);
            }
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'message' => $message,
            'status' => $status
        ];
    }
}
