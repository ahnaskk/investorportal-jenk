<?php

namespace App\Helpers;

use App\Http\Resources\SuccessResource;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Transformer\ParticipantPaymentTransformer;
use App\Merchant;
use App\PaymentInvestors;
use App\User;
use App\UserMeta;
use App\MerchantUser;
use App\Settings;
use App\UserDetails;
use App\Template;
use App\Jobs\CommonJobs;
use App\LiquidityLog;
use App\InvestorTransaction;
use Carbon\Carbon;
use FFM;
use Session;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\InvestorRoiRate;
use App\ReserveLiquidity;
use App\ParticipentPayment;

class InvestorHelper
{
    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
    }
    public function getColumns()
    {
        return [
            ['className' => 'details-control', 'orderable' => false, 'data' => null, 'defaultContent' => '', 'title' => ''],
            ['data' => 'name', 'name' => 'name', 'title' => 'Merchant'],
            ['data' => 'date_funded', 'name' => 'date_funded', 'defaultContent' => '', 'title' => 'Funded Date '],
            ['orderable' => false, 'data' => 'id', 'name' => 'id', 'defaultContent' => '', 'title' => 'Merchant Id'],
            ['orderable' => true, 'data' => 'TOTAL_DEBITED', 'name' => 'TOTAL_DEBITED', 'defaultContent' => '', 'title' => 'Debited'],
            ['orderable' => false, 'data' => 'TOTAL_COMPANY', 'name' => 'TOTAL_COMPANY', 'defaultContent' => '', 'title' => 'Total Payments'],
            ['orderable' => false, 'data' => 'TOTAL_MGMNT_FEE', 'name' => 'TOTAL_MGMNT_FEE', 'defaultContent' => '', 'title' => 'Management Fee'],
            ['orderable' => false, 'data' => 'TOTAL_SYNDICATE', 'name' => 'TOTAL_SYNDICATE', 'defaultContent' => '', 'title' => 'Net amount'],
            ['orderable' => false, 'data' => 'principal', 'name' => 'principal', 'defaultContent' => '', 'title' => 'Principal'],
            ['orderable' => false, 'data' => 'profit', 'name' => 'profit', 'defaultContent' => '', 'title' => 'Profit'],
            ['orderable' => false, 'data' => 'last_rcode', 'name' => 'last_rcode', 'defaultContent' => '', 'title' => 'Last Rcode'],
            ['orderable' => false, 'data' => 'last_payment_date', 'name' => 'last_payment_date', 'defaultContent' => '', 'title' => 'Last Payment Date'],
            ['orderable' => false, 'data' => 'last_payment_amount', 'name' => 'last_payment_amount', 'defaultContent' => '', 'title' => 'Last Payment Amount'],
            ['orderable' => false, 'data' => 'participant_rtr', 'name' => 'participant_rtr', 'defaultContent' => '', 'title' => 'Participant RTR'],
            ['orderable' => false, 'data' => 'participant_rtr_balance', 'name' => 'participant_rtr_balance', 'defaultContent' => '', 'title' => 'Participant RTR Balance'],
        ];
    }
    
    public function getReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $merchantIds = $request->input('merchant_id', []);
        $start = $request->input('start', 0);
        $limit = $request->input('length', 25);
        $isExport = $request->input('is_export', false);
        $limit = empty($limit) ? 25 : $limit;
        $groupBy = null;
        $user = Auth::user();
        $userId = $user->id;
        $merchantQuery = Merchant::where('merchants.active_status', 1)
        ->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')
        ->whereHas('investor', function ($query) use ($userId) {
            $query->select(
                'amount', 'invest_rtr', 'paid_mgmnt_fee', 'paid_participant_ishare', 'mgmnt_fee', 'merchant_user.user_id', 'merchant_user.merchant_id',
                DB::raw('sum( IF( paid_participant_ishare > invest_rtr, (paid_participant_ishare - invest_rtr ) * ( 1 - (merchant_user.mgmnt_fee) / 100 ), 0) ) as overpayment')
            );
            $query ->where('user_id', $userId)->where('merchant_user.status', 1);
        })
        ->select('merchants.id', 'name', 'date_funded', 'rcode.code as last_rcode', 'merchants.last_payment_date',
        DB::raw('( SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id AND participent_payments.is_payment = 1 ORDER BY payment_date DESC limit 1) last_payment_amount')
        )
        ->with(['investors' => function ($inner) use ($userId) {
            $inner->where('user_id', $userId);
        }])
        ->whereHas('participantPayment', function ($inner) use ($startDate, $endDate, $merchantIds, $userId, $groupBy) {
            $inner->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.is_payment', 1)->where('payment_investors.user_id', $userId)->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
            if ($endDate != null) {
                $inner->where('participent_payments.payment_date', '<=', $endDate);
            }
            if ($startDate != null) {
                $inner->where('participent_payments.payment_date', '>=', $startDate);
            }
            if ($groupBy == 2) {
                $inner->groupBy('merchant_id');
                $inner->groupBy(DB::raw('MONTH(payment_date)'));
            } elseif ($groupBy == 1) {
                $inner->groupBy('merchant_id');
                $inner->groupBy(DB::raw('WEEK(payment_date)'));
            } elseif ($groupBy == 3) {
                $inner->groupBy('merchant_id');
                $inner->groupBy('payment_date');
            }
        })->with(['participantPayment' => function ($inner) use ($startDate, $endDate, $merchantIds, $userId, $groupBy) {
            $inner->rightJoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('payment_investors.user_id', $userId)->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
            if ($endDate != null) {
                $inner->where('participent_payments.payment_date', '<=', $endDate);
            }
            if ($startDate != null) {
                $inner->where('participent_payments.payment_date', '>=', $startDate);
            }
            if ($groupBy == 2) {
                $inner->groupBy('merchant_id');
                $inner->groupByRaw('MONTH(payment_date)');
            } elseif ($groupBy == 1) {
                $inner->groupBy('merchant_id');
                $inner->groupBy(DB::raw('WEEK(payment_date)'));
            } elseif ($groupBy == 3) {
                $inner->groupBy('merchant_id');
                $inner->groupBy('payment_date');
            }
        }]);
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $merchantQuery->whereIn('merchants.id', $merchantIds);
        }
        $totDebited = $total_company = $total_syndicate = $total_mgmnt = $total_pricipal = $total_profit = $total_participant_rtr = $total_particaipant_rtr_balance = 0;
        $total_records = $merchantQuery->count();
        $rows = [];
        foreach ($merchantQuery->get() as $merchant) {
            $totDebited += $merchant->participantPayment->sum('payment');
            $total_company += $merchant->participantPayment->sum('participant_share');
            $total_mgmnt += $merchant->participantPayment->sum('mgmnt_fee');
            $total_pricipal += $merchant->participantPayment->sum('principal');
            $total_profit += $merchant->participantPayment->sum('profit');
            $total_participant_rtr += $merchant->investors->sum('invest_rtr');
            $total_particaipant_rtr_balance = $total_participant_rtr - $total_company;
        }
        $total_syndicate = $total_company - $total_mgmnt;
        if ($isExport != 'yes') {
            $merchantQuery->take($limit)->offset($start);
        }
        $merchants = $merchantQuery->get();
        $rows = $merchants->map(function ($merchant) {
            $participantPayment = collect($merchant->participantPayment);
            $investors = collect($merchant->investors);
            $participantShare = ($participantPayment)->pluck('participant_share')->sum();
            $participant_rtr = ($investors)->pluck('invest_rtr')->sum();
            $overPayments = ($investors)->pluck('overpayment')->sum();
            
            return ['id' => $merchant->id, 'name' => $merchant->name, 'date_funded' => $merchant->date_funded, 'last_rcode' => $merchant->last_rcode, 'TOTAL_DEBITED' => FFM::dollar(($participantPayment)->pluck('payment')->sum()), 'TOTAL_COMPANY' => FFM::dollar($participantShare), 'TOTAL_SYNDICATE' => FFM::dollar($participantShare - ($participantPayment)->pluck('mgmnt_fee')->sum()), 'principal' => FFM::dollar(($participantPayment)->pluck('principal')->sum()), 'profit' => FFM::dollar(($participantPayment)->pluck('profit')->sum()), 'last_payment_date' => FFM::date($merchant->last_payment_date), 'last_payment_amount' => FFM::dollar($merchant->last_payment_amount), 'participant_rtr' => FFM::dollar($participant_rtr), 'participant_rtr_balance' => FFM::dollar(($overPayments != 0) ? 0 : ($participant_rtr - $participantShare)), 'TOTAL_MGMNT_FEE' => FFM::dollar(($participantPayment)->pluck('mgmnt_fee')->sum())];
        });
        
        return ['sEcho' => 0, 'recordsTotal' => $total_records, 'recordsFiltered' => $total_records, 'data' => $rows, 'download-url' => url('api/investor/download/report?token='.$user->getDownloadToken()), 'total_debited' => FFM::dollar($totDebited), 'total_company' => FFM::dollar($total_company), 'total_syndicate' => FFM::dollar($total_syndicate), 'total_mgmnt' => FFM::dollar($total_mgmnt), 'total_profit' => FFM::dollar($total_profit), 'total_pricipal' => FFM::dollar($total_pricipal), 'total_participant_rtr' => FFM::dollar($total_participant_rtr), 'total_particaipant_rtr_balance' => FFM::dollar($total_particaipant_rtr_balance)];
    }
    
    public function updatePaymentValuesNew($userId)
    {
        if (! is_array($userId) and $userId < 1) {
            return false;
        }
        if (! is_array($userId)) {
            $userId = [$userId];
        }
        $userIds = $userId;
        foreach ($userIds as $userId) {
            $ctdFee = DB::select('CALL participant_share_mgmnt_fee_procedure(?)', [$userId])[0];
            set_time_limit(0);
            $participant_share = optional($ctdFee)->participant_share ?? 0;
            $mgmnt_fee = optional($ctdFee)->mgmnt_fee ?? 0;
            UserMeta::update_it($userId, '_pi_total_participant_share', $participant_share);
            UserMeta::update_it($userId, '_pi_total_mgmnt_fee', $mgmnt_fee);
        }
    }
    
    public function updatePaymentValues($userId)
    {
        if (! is_array($userId) and $userId < 1) {
            return false;
        }
        if (! is_array($userId)) {
            $userId = [$userId];
        }
        $userIds = $userId;
        $InvestorCtdFees = MerchantUser::whereIn('user_id', $userIds)->select('user_id', DB::raw('SUM(actual_paid_participant_ishare) as participant_share'), DB::raw('SUM(paid_mgmnt_fee) as mgmnt_fee'), )->groupBy('user_id')->get()->toArray();
        UserMeta::whereIn('user_id',$userIds)->where('key','_pi_total_participant_share')->update(['value'=>0]);
        UserMeta::whereIn('user_id',$userIds)->where('key','_pi_total_mgmnt_fee')->update(['value'=>0]);
        foreach ($InvestorCtdFees as $ctdFee) {
            set_time_limit(0);
            if (empty(optional($ctdFee)['user_id'])) {
                continue;
            }
            $userId = optional($ctdFee)['user_id'];
            $participant_share = optional($ctdFee)['participant_share'] ?? 0;
            $mgmnt_fee = optional($ctdFee)['mgmnt_fee'] ?? 0;
            UserMeta::update_it($userId, '_pi_total_participant_share', $participant_share);
            UserMeta::update_it($userId, '_pi_total_mgmnt_fee', $mgmnt_fee);
        }
    }
    
    public function updateUserPrincipal($userId, $defaultMerchantIds = [])
    {
        if (! is_array($userId)) {
            $userId = [$userId];
        }
        $userIds = $userId;
        $defaultMerchantIds = (count($defaultMerchantIds) < 1) ? Merchant::whereIn('sub_status_id', [4, 22])->pluck('id')->toArray() : $defaultMerchantIds;
        foreach ($userIds as $user_id) {
            set_time_limit(0);
            $ctdCost = MerchantUser::whereNotIn('merchant_user.merchant_id', $defaultMerchantIds)->where('user_id',$user_id)->select('user_id', DB::raw('SUM(paid_principal) as total_principal'))->first();
            if (! empty($ctdCost->user_id)) {
                UserMeta::update_it($ctdCost->user_id, '_pi_normal_total_principal', optional($ctdCost)->total_principal);
            } else {
                UserMeta::update_it($user_id, '_pi_normal_total_principal', 0);
            }
        }
    }
    public function getInvesterData($request)
    {
        $industries = isset($request->industries) ? $request->industries : null;
        $investor_type = isset($request->investor_type) ? $request->investor_type : null;
        $owner = isset($request->owner) ? $request->owner : null;
        $advance_type = isset($request->advance_type) ? $request->advance_type : null;
        $lenders = isset($request->lenders) ? $request->lenders : null;
        $statuses = isset($request->statuses) ? $request->statuses : null;
        $date_type = isset($request->date_type) ? $request->date_type : null;
        $velocity_owned = false;
        if($request->velocity_owned){
        $velocity_owned = true;
        }
        if($date_type!='true'){
            $request->time_start = $request->time_end = NULL;
        }
        $investor_label = isset($request->investor_label) ? $request->investor_label : null;
        $data = $this->merchant->getInvestorData(
            $request->row_merchant,
            $request->investors,
            $industries,
            $investor_type,
            $owner,
            $advance_type,
            $lenders,
            $statuses,
            $date_type,
            ET_To_UTC_Time($request->date_start.$request->time_start),
            ET_To_UTC_Time($request->date_end.$request->time_end),
            ET_To_UTC_Time($request->date_start.$request->time_start, 'time'),
            ET_To_UTC_Time($request->date_end.$request->time_end, 'time'),
            $investor_label,
            $request->active_status,
            $velocity_owned
        );
        $investmentData = '<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger"><td>Investor</td><td>Funded</td><td>RTR</td><td>Commission</td><td>Underwriting Fee</td><td>Syndication Fee</td><td>Total</td></tr>';
        foreach ($data as $dt) {
            if ($dt->amount) {
                $total_invested = $dt->pre_paid + $dt->amount + $dt->commission_amount + $dt->m_up_sell_commission+$dt->under_writing_fee;
                $investmentData .= '<tr><td><a href="'.route('admin::investors::portfolio', ['id' => $dt->user_id]).'">'.$dt->username.'</a></td><td>'.FFM::dollar($dt->amount).'</td><td>'.FFM::dollar($dt->invest_rtr).'</td><td>'.FFM::dollar($dt->commission_amount+$dt->m_up_sell_commission).'</td><td>'.FFM::dollar($dt->under_writing_fee).'</td><td>'.FFM::dollar($dt->pre_paid).'</td><td>'.FFM::dollar($total_invested).'</td></tr>';
            }
        }
        $investmentData .= '</table>';
        return $investmentData;
    }

    /*Liquidity for single user*/
    public function update_liquidity($user_ids = '', $description = '', $merchant_id = '', $liquidity_adjustor = 0)
    {
        $creator_id = null;
        if (Auth::check()) {
            $creator_id = Auth::user()->id;
        } elseif (Session::has('credit_card_payment_creator')) {
            $creator_id = Session::get('credit_card_payment_creator');
        }
        $settings = Settings::select('email')->first();
        $email_id_arr = explode(',', $settings->email);
        $merchant_id = ($merchant_id) ? $merchant_id : 0;
        $liquidity_arr = [];
        if (! is_array($user_ids)) {
            $user_ids = ['user_id'=>$user_ids];
        }
        $batch_id = rand(10000, 99999);
        $liquidity_change = $total_liquidity_change = 0;
        foreach ($user_ids as $key => $user_id) {
            $User = User::find($user_id);
            $total_credits = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->where('investor_id', $user_id)->sum('amount');
            $data = MerchantUser::where('user_id', $user_id);
            $data = $data->where('merchant_user.status', '!=', 0);
            $data = $data->select(
                DB::raw('SUM(paid_mgmnt_fee) as paid_mgmnt_fee'),
                DB::raw('SUM(amount) as amount'),
                DB::raw('SUM(commission_amount) as commission_amount'),
                DB::raw('SUM(under_writing_fee) as under_writing_fee'),
                DB::raw('SUM(up_sell_commission) as up_sell_commission'),
                DB::raw('SUM(pre_paid) as pre_paid'),
                DB::raw('SUM(paid_participant_ishare) as paid_participant_ishare')
            );
            $data = $data->first();
            $ctd  = $data['paid_participant_ishare'] - $data['paid_mgmnt_fee'];
            $total_funded       = $data['amount'];
            $commission_amount  = $data['commission_amount'];
            $under_writing_fee  = $data['under_writing_fee'];
            $up_sell_commission = $data['up_sell_commission'];
            $pre_paid_amount    = $data['pre_paid'];
            $liquidity  = ($total_credits + $ctd) - ($total_funded + $commission_amount) - $pre_paid_amount - $under_writing_fee;
            $liquidity -= $up_sell_commission;
            $user_details = UserDetails::where('user_id', $user_id)->first();
            if (! $user_details) {
                UserDetails::create(['user_id'=>$user_id]);
                $user_details = UserDetails::where('user_id', $user_id)->first();
            }
            if ($liquidity_adjustor != null) {
                $liquidity += $liquidity_adjustor;
            } elseif ($liquidity_adjustor == null) {
                $liquidity += $user_details->liquidity_adjuster;
            }
            $liquidity_old = $user_details->liquidity;
            $liquidity_change = round($liquidity - $liquidity_old, 2);
            $total_liquidity_change += $liquidity_change;
            if ($liquidity < -0.01 && (date('Y-m-d', strtotime($user_details->last_liquidity_alert_mail)) != date('Y-m-d'))) {
                $message['title']         = 'Liquidity -ve email alert for '.$User->name;
                $message['subject']       = 'Liquidity -ve email alert for '.$User->name;
                $message['content']       = $User->name.' liquidity went negative, and it is '.FFM::dollar($liquidity).'. <br><a href='.url('/admin/investor-transaction-log').'>Click here to view the transaction log.</a>';
                $message['to_mail']       = $email_id_arr;
                $message['investor_name'] = $User->name;
                $message['amount']        = $liquidity;
                $message['status']        = 'liquidty_alert';
                $message['heading']       = 'Liquidity -ve email alert for '.$User->name;
                $message['unqID']         = unqID();
                UserDetails::where('user_id', $user_id)->update(['last_liquidity_alert_mail'=>date('Y-m-d h:i:sa')]);
                try {
                    $email_template = Template::where([ ['temp_code', '=', 'LIQAL'], ['enable', '=', 1], ])->first();
                    if ($email_template) {
                        if ($email_template->assignees) {
                            $template_assignee = explode(',', $email_template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $role_mails  = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                $role_mails  = array_diff($role_mails, $email_id_arr);
                                $bcc_mails[] = $role_mails;
                            }
                            $message['bcc'] = Arr::flatten($bcc_mails);
                        }
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(1));
                        dispatch($emailJob);
                        $message['bcc']     = [];
                        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
                        $message['to_mail'] = $admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
            
            $UserDetails = UserDetails::where('user_id', $user_id)->first();
            $UserDetails->liquidity = round($liquidity, 2);
            
            $UserDetails->save();
            $aggregated_liquidity = UserDetails::join('users', 'users.id', 'user_details.user_id');
            $aggregated_liquidity = $aggregated_liquidity->where('company', '>', 0);
            $aggregated_liquidity = $aggregated_liquidity->groupBy('company');
            $aggregated_liquidity = $aggregated_liquidity->select(DB::raw('sum(liquidity) as liquidity,company'));
            $aggregated_liquidity = $aggregated_liquidity->get();
            $aggregated_liquidity = $aggregated_liquidity->toArray();
            $aggregated_liquidity = json_encode($aggregated_liquidity);
            $liquidity_arr[]=[
                'member_id'           => $user_id,
                'final_liquidity'     => $liquidity,
                'liquidity_change'    => $liquidity_change,
                'member_type'         => 'investor',
                'aggregated_liquidity'=> $aggregated_liquidity,
                'description'         => $description,
                'merchant_id'         => $merchant_id,
                'batch_id'            => $batch_id,
                'creator_id'          => $creator_id,
            ];

            $reserved_percentage = ReserveLiquidity::where('user_id', $user_id)->get();
            $total_reserved_liquidity_amount = 0;
            foreach ($reserved_percentage as $key => $value) {
                $UserDetails = UserDetails::where('user_id', $value->user_id)->first();
                $UserDetails->reserved_liquidity_amount = 0;
                $to_date = $value->to_date;
                $participant_payment = ParticipentPayment::join('payment_investors','payment_investors.participent_payment_id','participent_payments.id')->select('payment_investors.user_id','participent_payments.payment_date',DB::raw('SUM(payment_investors.participant_share) as participant_share'),DB::raw('SUM(payment_investors.mgmnt_fee) as mgmnt_fee'))
                ->where('payment_investors.user_id', $value->user_id)
                ->whereDate('participent_payments.payment_date' , '>=', date('Y-m-d',strtotime($value->from_date)))->where(function($q) use($to_date){
                    if($to_date != null){
                        return $q->whereDate('participent_payments.payment_date' , '<=', date('Y-m-d',strtotime($to_date)));
                    }
                })->groupBy('payment_investors.user_id')->get();
                if (count($participant_payment) > 0){
                    $payment_amount = $participant_payment[0]->participant_share;
                    $fee = $participant_payment[0]->mgmnt_fee;
                    $actual_payment = $payment_amount - $fee;
                    $reserved_liquidity_amount = 0;
                    $reserved_liquidity_amount = $value->reserve_percentage * $actual_payment / 100;
                    $total_reserved_liquidity_amount += $reserved_liquidity_amount;  
                }
            }
            $UserDetails->reserved_liquidity_amount = round($total_reserved_liquidity_amount, 2);
            $UserDetails->save(); 
        }
        

        
        if (round($total_liquidity_change, 2) != 0) {
            $LiquidityLogModel = new LiquidityLog;
            foreach ($liquidity_arr as $key => $single) {
                $return_result = $LiquidityLogModel->selfCreate($single);
                if ($return_result['result'] != 'success') {
                    return false; // wrong merthod need to change by actual merthod of throw catch
                    throw new \Exception($return_result['result'], 1);
                }
            }
        }
        return true;
        // insert as an array.
    }
    
    public function insertTransactionFunction($data)
    {
        try {
            $bank_details = array();
            if (isset($data['investor_id'])) {
                $bank_details = DB::table('bank_details')->where('investor_id', $data['investor_id']);
                if ($data['transaction_type'] == 1) {
                    $bank_details = $bank_details->where('default_debit', 1);
                }
                if ($data['transaction_type'] == 2) {
                    $bank_details = $bank_details->where('default_credit', 1);
                }
                $bank_details = $bank_details->first();
            }
            $transaction = new InvestorTransaction;
            $validator = \Validator::make($data,$transaction->rules());
            if($validator->fails())  { foreach ($validator->errors()->getMessages() as $key => $value) { throw new \Exception($value[0]); } }
            $amount = trim(str_replace(',', '', $data['amount']));
            if (is_numeric($amount)) {
                $data['amount'] = $amount;
            }
            $transaction->amount = $data['transaction_type'] == 1 ? -1 * abs($data['amount']) : $data['amount'];
            $transaction->batch = time();
            if (isset($data['date'])) {
                $transaction->date = $data['date'];
            }
            if (isset($data['category_notes'])) {
                $transaction->category_notes = $data['category_notes'];
            }
            if (isset($data['merchant_id'])) {
                $transaction->merchant_id = $data['merchant_id'];
            }
            if (isset($data['maturity_date'])) {
                $transaction->maturity_date = $data['maturity_date'];
            }
            if (isset($data['investor_id'])) {
                $transaction->investor_id = $data['investor_id'];
            }
            if (isset($data['transaction_type'])) {
                $transaction->transaction_type = $data['transaction_type'];
            }
            if (isset($data['transaction_method'])) {
                $transaction->transaction_method = $data['transaction_method'];
            }
            if (isset($data['transaction_category'])) {
                $transaction->transaction_category = $data['transaction_category'];
            }
            if (isset($data['account_no'])) {
                $transaction->account_no = $data['account_no'];
            }
            if (! isset($data['account_no'])) {
                if ($bank_details) {
                    $transaction->account_no = $bank_details->acc_number;
                }
            }
            $transaction->status = 1;
            if (isset($data['creator_id'])) {
                $transaction->creator_id = $data['creator_id'];
            }
            $transaction->save();
            $user_id = $data['investor_id'];
            $description = \ITran::getLabel($data['transaction_category']);
            if(isset($data['merchant_id'])){
                InvestorHelper::update_liquidity($user_id, $description, $data['merchant_id']); //Pass User Id
            } else {
                InvestorHelper::update_liquidity($user_id, $description); //Pass User Id
            }
            $return['result']         = 'success';
            $return['description']    = $description;
            $return['transaction_id'] = $transaction->id;
        } catch (Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }

    public function saveRoiRate($data){
        $create = InvestorRoiRate::create($data);
        return $create;
    }

    public function saveReserveLiquidity($data){
        $create = ReserveLiquidity::create($data);
        return $create;
    }
    public function updateRoiRate($data){
       $update =  InvestorRoiRate::where('id', $data['id'])->where('user_id',$data['user_id'])
       ->update([
           'from_date' => $data['from_date'],
           'to_date' => $data['to_date'],
           'roi_rate'=> $data['roi_rate']
        ]);
        return $update;

    }
    public function updateReserveLiquidity($data){
        $update =  ReserveLiquidity::where('id', $data['id'])->where('user_id',$data['user_id'])
        ->update([
            'reserve_percentage'=> $data['reserve_percentage'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],

         ]);
         return $update;
 
     }
    
}
