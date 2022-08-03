<?php

namespace App\Http\Controllers\Api;

use App\CreditCardLog;
use App\Faq;
use MerchantHelper;
use function App\Helpers\modelQuerySql;
use ParticipantPaymentHelper;
use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PostChangeMerchantMerchantRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\MailLog;
use App\Merchant;
use App\MerchantDetails;
use App\MerchantStatement;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Settings;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use Exception;
use FFM;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use PayCalc;
use PaymentHelper;
use Stripe\Charge;
use Stripe\Stripe;
use App\MerchantRequestMoney;
use App\Template;
use Illuminate\Support\Arr;
use App\Providers\DashboardServiceProvider;
use Illuminate\Support\Facades\Schema;

class MerchantController extends Controller
{
    protected $user = false;
    protected $role = 'merchant';
    protected $merchantId = null;

    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
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
        if ($this->role !== 'merchant') {
            abort(response()->json('Not found', 404));
        }
        $this->merchantId = $this->user->current_merchant_id;
        if (empty($this->merchantId)) {
            $this->merchantId = optional($this->user->getMerchants()->pluck('id')->toArray())[0] ?? $this->user->merchant_id_m;
        }
    }

    public function postMerchantDetails(Request $request)
    {
        $merchantId = $this->merchantId;
        $add_payment_permission = 1;
        $sub_status_id = Merchant::where('id', $merchantId)->value('sub_status_id');
        if (in_array($sub_status_id, [18, 19, 20, 4, 22])) {
            $add_payment_permission = 0;
        }
        $merchantDetails = MerchantHelper::getDetails($merchantId, 0);
        $sub_status = [11, 18, 19, 20];
        $merchantDetails['balance_our_portion'] = (in_array($merchantDetails['merchant']->sub_status_id, $sub_status)) ? '0.00' : (($merchantDetails['overpayments'] ?? 0 < 0) ? $merchantDetails['balance_our_portion'] : 0);
        $merchantDetails['overpayments'] = ($merchantDetails['overpayments'] ?? 0 < 0) ? $merchantDetails['overpayments'] : 0;
        $merchantDetails['add_payment_permission'] = $add_payment_permission;
        
     
        return new SuccessResource(['data' => $merchantDetails]);
    }

    public function postFaqApp($app = 1)
    {
        try {
            return new SuccessResource(['data' => Faq::where('user_type', 2)->where('app', $app)->get()]);
        } catch (Exception $e) {
            return new ErrorResource(['message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function postPayments(Request $request)
    {
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);
        $merchantId = $this->merchantId;
        $merchant = Merchant::find($merchantId);
        $investorIds = User::investors()->pluck('id')->toArray();
        $payments = ParticipantPaymentHelper::getMerchantPaymentsByDate($merchantId, $investorIds, 0);
        $merchantPayments = ParticipantPaymentHelper::getMerchantPayments($merchantId, $investorIds, 0);
        $total_payment1 = array_sum(array_column($merchantPayments->toArray(), 'payment'));
        $balance_merchant = $merchant->rtr - $total_payment1;
        $totalPaymentsByDate = ParticipantPaymentHelper::getUniqueDatePayments($merchantId);
        $result = [];
        $total_payment = $total_participant_share = 0;
        $sort_col = [];
        foreach ($payments as $payment) {
            $sort_col[] = $payment->payment_date;
            $total_payment = $total_payment + $payment->payment;
            $total_participant_share = $total_participant_share + $payment->participant_share;
            $balance_rtr = $payment->invest_rtr - $total_participant_share;
            $result[] = ['id' => $payment->id, 'payment' => FFM::dollar($payment->payment), 'rcode' => $payment->rcode, 'payment_date' => Carbon::parse($payment->payment_date)->format('m/d/Y'), 'number_of_payments' => $totalPaymentsByDate, 'payment_left' => ! empty($payment->payment) ? round($payment->bal_rtr / $payment->payment, 2) : 0, 'count' => count($payments), 'payment_balance' => ($payment->overpayment > 0 || $payment->rcode_id != '') ? FFM::dollar(0) : FFM::dollar($balance_merchant)];
        }
        $result = array_slice($result, $offset, $limit);

        return new SuccessResource(['data' => $result]);
    }

    public function postCreditCardPayment(Request $request)
    {
        $validator = \Validator::make($request->all(), ['card_number' => 'required', 'exp_month' => 'required', 'exp_year' => 'required', 'cvc' => 'required', 'amount' => 'required', 'amount_to_be_paid' => 'required']);
        if ($validator->fails()) {
            return response()->json($this->getErrorMessages($validator->messages()), 422);
        }
        $id = $this->merchantId;
        Stripe::setApiKey(config('app.stripe_secret'));
        $response = \Stripe\Token::create(['card' => ['number' => $request->input('card_number'), 'exp_month' => $request->input('exp_month'), 'exp_year' => $request->input('exp_year'), 'cvc' => $request->input('cvc'), 'name' => $request->input('name')]]);
        try {
            Charge::create(['amount' => ($request->amount_to_be_paid * 100), 'currency' => 'usd', 'source' => $response->id, 'description' => 'Credit Card payment']);
        } catch (Exception $e) {
            $error = $e->getMessage();

            return new ErrorResource(['message' => $error]);
        }
        $hashids = new Hashids();
        if ($id) {
            $id = $hashids->encode($id);
        }
        $decoded_id = $hashids->decode($id);
        $add_payment = PaymentHelper::generateAchPayment($decoded_id[0], date('d-m-Y'), $request->amount, null, 2);
        if ($add_payment) {
            $this->sendmail($decoded_id[0], $request->amount_to_be_paid, $request['card_number'], 'merchant', $request->amount);

            return new SuccessResource(['message' => 'Payment added successfully']);
        } else {
            return new ErrorResource(['message' => 'Payment Failed']);
        }
    }

    public function postRequestMoreMoney(Request $request)
    {
        $merchantId = $this->merchantId;
        $mid = 0;
        $amount = $request->input('amount', 0);
        $source = $request->input('source', null);
        $merchant_ip = $request->input('merchant_ip', null);
        $merchant_data = MerchantDetails::where('merchant_id',$merchantId)->first();
        if($merchant_data){
            
            $mid = ($merchant_data) ? $merchant_data->crm_id: 0;
            if($mid!=0){
        
        if ($source != null) {
            $fields = [
            'method'                    => 'get_decision_logic_link',
            'username' => config('app.crm_user_name'),
            'password'                  => config('app.crm_password'),
            'mid'                  => $mid,
            'merchant_ip'    => $merchant_ip,
            'amount_requested' => $amount,
            'source'         => $source,

        ];
        } else {
            $fields = [
            'method'                    => 'get_decision_logic_link',
            'username' => config('app.crm_user_name'),
            'password'                  => config('app.crm_password'),
            'mid'                  => $mid,
            'merchant_ip'    => $merchant_ip,
            'amount_requested' => $amount,
        ];
        }
        $errors = [];
        $token = '';
        $insert_id='';
        $crm_url = config('app.crm_url').'/api/service';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $crm_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);
        if (isset($result->response_code)) {
            if ($result->response_code == 100) {
                $link = $result->link;
                $token_arr = explode('ctoken=', $link);
                if(isset($token_arr[1])){
                    $token = $token_arr[1];
                }
                $settings = Settings::select('email')->first();
                $emails = $settings->email;
                $toEmails = explode(',', $emails);
                $message = [];
                $errors = '';

                $merchantName = Merchant::where('id', $merchantId)->value('name');
                Merchant::find($merchantId)->update(['money_request_status' => 1]);
                $single = [
                        'merchant_id'=>$merchantId,
                        'amount'=>$amount,
                        'merchant_ip'=>$merchant_ip,
                        'source'=>$source,
                        'status'=>0,
                    ];
                    $insert_id = MerchantRequestMoney::create($single)->id;



                $message['title'] = 'Request More Money';
                $message['subject'] = 'Request More Money';
                $message['content'] = $merchantName.'  Requested '.FFM::dollar($amount);
                $message['to_mail'] = $toEmails;
                $message['status'] = 'merchant_api';
                $message['template_type'] = 'request_money';
                $message['merchant_id'] = $merchantId;
                $message['merchant_name'] = $merchantName;
                $message['amount'] = $amount;
                $message['from_mail'] = $this->user->email;
                $message['unqID'] = unqID();

                try {
                    $email_template = Template::where([
                        ['temp_code', '=', 'REQMM'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        if ($email_template->assignees) {
                            $template_assignee = explode(',', $email_template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                $role_mails = array_diff($role_mails, $toEmails);
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
                    $errors = $e->getMessage();
                }
                if (empty($errors)) {
                    return new SuccessResource(['message' => 'Requested more money successfully', 'link'=>$link,'token'=>$token,'id'=>$insert_id,'crm_id'=>$mid]);
                }
            } else {
                if (isset($result->response_data)) {
                    if (isset($result->response_data->errors)) {
                        $errors = $result->response_data->errors;
                    }
                }
            }
        }
    }
    else{
       $errors = ["This merchant don't have crm id"]; 
    }
    }else{
       $errors = ["Invalid Merchant"]; 
    }

        return new ErrorResource(['message' => $errors,'crm_id'=>$mid]);
    }
    public function postRequestMoreMoneyStatusUpdate(Request $request){
        $merchantId = $this->merchantId;
        $status = $request->input('status', 0);
        $id = $request->input('id', null);
        if($id!=null){

       
        $update = MerchantRequestMoney::where('id', $id)
       ->update([
           'status' => $status
        ]);
       }else{
        return new ErrorResource(['message' => "Invalid data"]);
       }
       if($update){
       return new SuccessResource(['message' => 'Requested more money status updated successfully']);
       }else{
       return new ErrorResource(['message' => "Status not updated"]);
       }
    }
    public function postCallCrmApi(Request $request){
        $token = $request->input('token', null);
        $id = $request->input('id', null);
        $merchantId = $this->merchantId;
        $merchant_data = MerchantDetails::where('merchant_id',$merchantId)->first();
        $mid = ($merchant_data) ? $merchant_data->crm_id: 0;
        if($mid==0){
          return new ErrorResource(['message' => ["This merchant don't have crm id",'status'=>'Failed']]);
        }else{
        $msg = '';
        $res =0;
        $status = '';
        $fields = [
            'method'                    => 'decision_logic_status',
            'username' => config('app.crm_user_name'),
            'password'                  => config('app.crm_password'),
            'token'                  => $token,
            'mid'                    => $mid
        ];

        $crm_url = config('app.crm_url').'/api/service';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $crm_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);
        if (isset($result->response_code)) {          
                if(isset($result->response_data)){
                    if(isset($result->response_data->data)){
                        if(isset($result->response_data->data->status)){
                            if($result->response_data->data->status=='success'){
                                $msg="Verification successfull";
                                $res =1;
                                $status = 'success';


                                $update = MerchantRequestMoney::where('id', $id)
                                   ->update([
                                       'status' => 1
                                    ]);

                            }
                            if($result->response_data->data->status==''){
                            $msg="No response is received yet";
                            $res =1;
                            $status = 'pending';


                            }

                            if($result->response_data->data->status=='failure'){
                                $msg="Verification failed";
                                $res =0;
                                $status = 'failed';
                            }
                       }else{
                                $msg="No response is received yet";
                                $res =1;
                                $status = 'pending';
                       }
                    }elseif($result->response_data->errors){
                        foreach($result->response_data->errors as $err){
                          $msg .= $err;
                          $res =0;                         
                        }
                         $status = 'failed';
                    }

                }
        }
    }
        if($res ==1){
        return new SuccessResource(['message' => $msg,'status'=>$status,'crm_id'=>$mid]);
        }else{
        return new ErrorResource(['message' => $msg,'status'=>$status,'crm_id'=>$mid]);
        }


    }
    public function postMerchantMoneyRequests(Request $request){
       $merchantId = $this->merchantId; 
       $limit = $request->input('limit', 10);
       $offset = $request->input('offset', 0);
       $request_data = MerchantRequestMoney::where('merchant_id',$merchantId)->limit($limit)->offset($offset)->orderBy('created_at','DESC')->get();
       $data = [];
        foreach ($request_data as $req) {
            if($req->status==0){
                $status = 'Pending';
               } 
               if($req->status==1){
                $status = 'Submitted';
               }  
             $data[] = ['merchant_id' => $req->merchant_id,'date' => Carbon::parse($req->created_at)->format('m-d-Y'),'status'=>$status,'status_id'=>$req->status,'amount'=>FFM::dollar($req->amount)];
           
        }

        return new SuccessResource(['data' => $data]);
       
    }

    public function postStatements(Request $request)
    {
        $validator = \Validator::make($request->all(), ['limit' => 'nullable|integer', 'offset' => 'nullable|integer', 'sort_field' => ['nullable', Rule::in(['created_at', 'to_date'])], 'sort_order' => ['nullable', Rule::in(['ASC', 'DESC'])]]);
        if ($validator->fails()) {
            return response()->json($this->getErrorMessages($validator->messages()), 422);
        }
        $merchantId = $this->merchantId;
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'DESC');
        $statements = MerchantStatement::where('merchant_id', $merchantId);
        if ($sortField) {
            $statements = $statements->orderBy($sortField, $sortOrder);
        }
        $statements = $statements->get();
        $data = [];
        foreach ($statements as $statement) {
            $file_url = asset(\Storage::disk('s3')->temporaryUrl($statement->file_name.'.pdf',Carbon::now()->addMinutes(2)));
            $data[] = ['id' => $statement->id, 'file_name' => substr($statement->file_name, strrpos($statement->file_name, '/') + 1), 'file_url' => $file_url, 'to_date' => Carbon::parse($statement->to_date)->format('m-d-Y'), 'from_date' => Carbon::parse($statement->from_date)->format('m-d-Y'), 'created_at' => Carbon::parse($statement->created_at)->format('m-d-Y')];
        }
        $data = collect($data)->slice($offset, $limit);

        return new SuccessResource(['statements' => $data]);
    }

    public function getErrorMessages($errors)
    {
        return ['status' => false, 'errors' => $errors];
    }

    public function postRequestPayOff(Request $request)
    {
        $merchantId = $this->merchantId;
        $settings = Settings::select('email')->first();
        $emails = $settings->email;
        $toEmails = explode(',', $emails);
        $message = [];
        $errors = '';
        $merchantName = Merchant::where('id', $merchantId)->value('name');
        $message['title'] = 'Request PayOff';
        $message['subject'] = 'Request PayOff';
        $message['content'] = $merchantName.' Requested to Payoff';
        $message['to_mail'] = $toEmails;
        $message['status'] = 'merchant_api';
        $message['template_type'] = 'request_payoff';
        $message['merchant_id'] = $merchantId;
        $message['merchant_name'] = $merchantName;
        $message['from_mail'] = $this->user->email;
        $message['unqID'] = unqID();
        try {
            $email_template = Template::where([
                ['temp_code', '=', 'REPOF'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $toEmails);
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
            $errors = $e->getMessage();
        }
        Merchant::find($merchantId)->update(['pay_off' => 1]);
        if (empty($errors)) {
            return new SuccessResource(['message' => 'Requested to payoff successfully']);
        }

        return new ErrorResource(['message' => $errors]);
    }

    public function postChangeMerchant(PostChangeMerchantMerchantRequest $request)
    {
        $data = $request->validated();
        $merchantId = $data['merchant_id'];
        if (in_array($merchantId, $this->user->getMerchants()->pluck('id')->toArray())) {
            $this->user->current_merchant_id = $merchantId;
            $this->user->update();
        }

        return new SuccessResource(['message' => 'Merchant has been changed successfully']);
    }

    public function sendmail($decoded_id, $amount, $card_number, $user_type, $actual_amount = 0)
    {
        try {
            $Merchant = ($user_type == 'merchant') ? Merchant::find($decoded_id) : User::find($decoded_id);
            $user_details = UserDetails::where('user_id', $decoded_id)->first();
            $message['title'] = 'Payment successful';
            $message['subject'] = 'Payment successful';
            $message['content'] = 'Successful Added The Payment';
            $message['to_mail'] = $Merchant->notification_email;
            $message['merchant_id'] = $Merchant->id;
            $message['merchant_name'] = $Merchant->name;
            $message['status'] = 'payment_send';
            $message['amount'] = $amount;
            $message['actual_amount'] = FFM::dollar($actual_amount);
            $message['date'] = FFM::date(date('Y-m-d'));
            $message['wallet_amount'] = ($user_details) ? $user_details->liquidity : false;
            $message['card_number'] = $card_number;
            $message['unqID'] = unqID();
            $card_number = substr($card_number, -4);
            $message['card_number'] = $card_number;
            if ($message['wallet_amount']) {
                $message['content'] = 'This is the Accounting Department at Velocity Group USA. We have just received a Credit Card Payment (Card Number ** ** '.$card_number.') for adding fund to your wallet. The amount paid was '.FFM::dollar($message['amount']).' (inclusive a processing fee of 3.75%) on '.$message['date'].'. Your wallet has been added with '.$message['actual_amount'].' and at present stands at '.$message['wallet_amount'].' .';
            } else {
                $message['content'] = 'We have just received a Credit Card payment (Card Number ** ** '.$card_number.'). The amount paid was '.FFM::dollar($message['amount']).' on '.$message['date'].'.';
            }
            $template_data = DB::table('template')->where('temp_code', 'PYMNT')->first();
            if ($template_data) {
                $message['subject'] = $template_data->subject;
            }
            $crm_user = DB::table('user_has_roles')->where('role_id', User::CRM_ROLE)->select('model_id')->first();
            if ($Merchant->notification_email == null) {
                $values = [
                    'title' => $message['subject'],
                    'type' => 3,
                    'to_mail' => '-',
                    'status' => 'failed',
                    'to_user_type' => 'merchant',
                    'to_id' => $Merchant->id,
                    'to_name' => $Merchant->name,
                    'failed_message'=> 'email is null',
                    'creator_id' => ($crm_user) ? $crm_user->model_id : null
                ];
                MailLog::create($values);
            }
            try {
                $email_template = Template::where([
                    ['temp_code', '=', 'PYMNT'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    $values = [
                        'title' => $message['subject'],
                        'type' => 3,
                        'to_mail' => $Merchant->notification_email,
                        'to_user_type' =>   'merchant',
                        'to_id' => $Merchant->id,
                        'to_name' => $Merchant->name,
                        'status'  =>   'success',
                        'creator_id' => ($crm_user) ? $crm_user->model_id : null
                    ];
                    MailLog::create($values);
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                }
                    $credit_card_log = new CreditCardLog();
                    $credit_card_log->description = '';
                    $credit_card_log->amount = $amount;
                    $credit_card_log->actual_amount = $actual_amount;
                    $credit_card_log->notification_email = $Merchant->notification_email;
                    $credit_card_log->name = $Merchant->name;
                    $credit_card_log->card_number = mask_string($card_number);
                    $credit_card_log->user_type = ($user_type == 'merchant') ? 'Merchant' : 'Investor';
                    $credit_card_log->save();
                if ($email_template) {
                    dispatch($emailJob);
                    $message['to_mail'] = $this->admin_email;
                    $emailJobs = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
            } catch (\Exception $e) {
                $values = [
                    'title' => $message['subject'],
                    'type' => 3,
                    'to_mail' => $Merchant->notification_email,
                    'status'  => 'failed',
                    'to_user_type' => 'merchant',
                    'to_id' => $Merchant->id,
                    'to_name' => $Merchant->name,
                    'failed_message'=> $e->getMessage(),
                    'creator_id' => ($crm_user) ? $crm_user->model_id : null
                ];
                MailLog::create($values);
                throw $e;
            }
            
        } catch (Exception $e) {
            echo $e->getMessage();
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

    public function postMerchantGraph()
    {
        $merchantId = $this->merchantId;
        $xAxisLabels = [];
        for ($i = 4; $i > -1; $i--) {
            $xAxisLabels['month'.($i > 0 ? $i : '')] = Carbon::now()->subMonths($i)->format('M');
        }
        $startDate = Carbon::now()->subMonths(4)->format('Y-m-01');
        $endDate = Carbon::now()->format('Y-m-t');
        $merchantFunds = MerchantHelper::getFundsByDate($merchantId, $startDate, $endDate)->toArray();
        $chartData = [];
        for ($i = 4; $i > -1; $i--) {
            $month = Carbon::now()->subMonths($i)->format('m');
            $year = Carbon::now()->subMonths($i)->format('Y');
            $merchantFund = collect($merchantFunds)->filter(function ($record) use ($month, $year) {
                return (int) $record['month'] == (int) $month && $record['year'] == $year;
            })->first();
            $chartData[] = ['month' => $month, 'year' => $year, 'funded' => optional($merchantFund)['funded'] ?? 0];
        }

        return new SuccessResource(['chart_data' => $chartData, 'x_data' => $xAxisLabels]);
    }

    public function postLatestPayments()
    {
        $merchantId = $this->merchantId;
        $latest_payments = MerchantHelper::merchantsLatestPayments($merchantId);
        $latest_payments = collect($latest_payments)->map(function ($record) {
            return ['payment_date' => FFM::date($record['payment_date']), 'amount' => FFM::dollar($record['payment'] ?? 0), 'type' => ($record['code'] == null) ? $record['payment_type'] : $record['code']];
        });

        return new SuccessResource(['data' => $latest_payments]);
    }
}
