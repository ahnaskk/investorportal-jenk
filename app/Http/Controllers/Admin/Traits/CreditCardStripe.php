<?php

namespace App\Http\Controllers\Admin\Traits;

use App\CreditCardLog;
use App\Jobs\CommonJobs;
use App\Library\Repository\InvestorTransactionRepository;
use App\MailLog;
use App\Merchant;
use App\Settings;
use App\Template;
use App\User;
use App\UserDetails;
use Exception;
use FFM;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use PaymentHelper;
use Stripe\Charge;
use Stripe\Stripe;

trait CreditCardStripe
{
    public function sendmail($decoded_id, $amount, $card_number, $user_type, $actual_amount = 0)
    {
        try {
            $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
            $creator_id = null;
            if (Auth::check()) {
                $creator_id = Auth::user()->id;
            } elseif (Session::has('credit_card_payment_creator')) {
                $creator_id = Session::get('credit_card_payment_creator');
            }
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
            $message['mail_to'] = 'user';
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
                    'creator_id' => $creator_id
                ];
                MailLog::create($values);
                return;
            }
            try {
                $values = [
                    'title' => $message['subject'],
                    'type' => 3,
                    'to_mail' => $Merchant->notification_email,
                    'to_user_type' =>   'merchant',
                    'to_id' => $Merchant->id,
                    'to_name' => $Merchant->name,
                    'status'  =>   'success',
                    'creator_id' => $creator_id
                ];
                $email_template = Template::where([
                    ['temp_code', '=', 'PYMNT'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    MailLog::create($values);
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, [$Merchant->notification_email]);
                            $bcc_mails[] = $role_mails;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc'] = [];
                    $message['to_mail'] = $admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
                $emails = Settings::value('email');
                $emailArray = explode(',', $emails);
                $message['to_mail'] = $emailArray;
                $message['mail_to'] = 'admin';
                $email_template = Template::where([
                    ['temp_code', '=', 'PYMNA'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
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
                }
                $credit_card_log = new CreditCardLog();
                $credit_card_log->description = '';
                $credit_card_log->amount = $amount;
                $credit_card_log->actual_amount = $actual_amount;
                $credit_card_log->notification_email = $Merchant->notification_email ? $Merchant->notification_email : '';
                $credit_card_log->name = $Merchant->name;
                $credit_card_log->card_number = mask_string($card_number);
                $credit_card_log->user_type = ($user_type == 'merchant') ? 'Merchant' : 'Investor';
                $credit_card_log->save();
                $message['to_mail'] = $admin_email;
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
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
                    'creator_id' => $creator_id
                ];
                MailLog::create($values);
            }
            
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function make_payment(Request $request, $id, $amount)
    {
        $request->session()->put('prev_url', URL::previous());
        $stripe_key = config('app.stripe_key');
        $hashids = new Hashids();
        $decoded_id = $hashids->decode($id);
        if (! isset($decoded_id[0])) {
            abort(403);
        }
        if ($request->type != 'investor') {
            $user_data = Merchant::find($decoded_id[0]);
            $investor = 0;
        } else {
            $user_data = User::find($decoded_id[0]);
            $investor = 1;
        }
        if (! $user_data) {
            abort(403);
        }
        session_set('process_payment', true);
        session_set('prev_url', URL::previous());

        return view('payment.index', compact('user_data', 'id', 'stripe_key', 'amount', 'investor'));
    }

    public function process_stripe()
    {
    }

    public function process_stripe_payment(Request $request, $id = 0)
    {
        if (session('process_payment')) {
            session_set('process_payment', false);
            Stripe::setApiKey(config('app.stripe_secret'));
            try {
                Charge::create([
                    'amount' => ($request->total_amount * 100),
                    'currency' => 'usd',
                    'source' => $request->stripeToken,
                    'description' => 'Credit Card payment',
                ]);
            } catch (Exception $e) {
                $error = $e->getMessage();

                return view('payment.successful', compact('error'));
            }
            $hashids = new Hashids();
            if ($id) {
                $id = $hashids->encode($id);
            } else {
                $id = $request->user_id;
            }
            $decoded_id = $hashids->decode($id);
            if (Auth::check()) {
                session_set('credit_card_payment_creator', Auth::user()->id);
            } else {
                $user_id = Merchant::where('id', $decoded_id[0])->value('user_id');
                session_set('credit_card_payment_creator', $user_id);
            }
            $add_payment = PaymentHelper::generateAchPayment($decoded_id[0], date('d-m-Y'), $request->amount, null, 2);
            if ($add_payment) {
                $this->sendmail($decoded_id[0], $request->total_amount, $request['card-number'], 'merchant', $request->amount);
            }
            Session::forget('credit_card_payment_creator');
        }

        return view('payment.successful');
    }

    public function process_stripe_payment_investor(Request $request)
    {
        if (session('process_payment')) {
            session_set('process_payment', false);
            Stripe::setApiKey(config('app.stripe_secret'));
            try {
                Charge::create([
                    'amount' => ($request->total_amount * 100),
                    'currency' => 'usd',
                    'source' => $request->stripeToken,
                    'description' => 'Test payment',
                ]);
            } catch (Exception $e) {
                $error = $e->getMessage();

                return view('payment.successful', compact('error'));
            }
            $hashids = new Hashids();
            $decoded_id = $hashids->decode($request->user_id);
            $request->merge([
                'transaction_type' => 2,
                'transaction_category' => 42,
                'investor_id' => $decoded_id[0],
                'creator_id' => 1,
                'date' => date('Y-m-d'),
                'mode_of_payment' => 2,
            ]);
            try {
                $transaction = new InvestorTransactionRepository();
                if ($transaction->insertTransaction($request)) {
                    $request->session()->flash('message', 'Transaction Created!');
                }
                $this->sendmail($decoded_id[0], $request->total_amount, $request['card-number'], 'investor', $request->amount);
            } catch (Exception $e) {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }

        return view('payment.successful');
    }
}
