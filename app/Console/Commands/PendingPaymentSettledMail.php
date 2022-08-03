<?php

namespace App\Console\Commands;

use App\CompanyAmount;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Merchant;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\PaymentInvestors;
use App\Settings;
use App\SubStatus;
use App\Template;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PayCalc;

class PendingPaymentSettledMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PendingPaymentSettledMail:PendingPaymentSettledMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1 months no payment pending payment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IMerchantRepository $merchant)
    {
        parent::__construct();
        $this->merchant = $merchant;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $emails = Settings::value('email');
        $email_id_arr = explode(',', $emails);
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';

        $merchants = Merchant::select('last_payment_date', 'merchants.id as merchant_id', 'merchants.name as merchant_name', 'lag_time', 'users.name as lender_name', 'company', 'sub_statuses.name as substatus_name', 'merchants.sub_status_id', 'merchants.factor_rate', 'merchants.old_factor_rate')
            ->where('merchants.active_status', 1)
            ->whereNOTIn('merchants.sub_status_id', [4, 11, 18, 19, 20, 22])
            ->join('users', 'users.id', 'merchants.lender_id')
            ->where('mail_send_status', '!=', 222)
            ->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
            ->where('merchants.complete_percentage', '<', 99)
            ->where('merchants.complete_percentage', '>', 0)
            ->orderByDesc('merchants.id')
            ->where(function ($query) {
                $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+30) DAY)');
            })
           ->orderByDesc('merchants.id')->get()->toArray();

        $i = 0;

        $data_r = [];

        if (! empty($merchants)) {
            foreach ($merchants as $key=>$value) {
                $last_payment_date = ! empty($value['last_payment_date']) ? $value['last_payment_date'] : '';

                if ($last_payment_date) {
                    $date = date('Y-m-d', strtotime($last_payment_date));

                    $from = $date;
                    $to = date('Y-m-d');
                    $now = strtotime($to);
                    $current_date = strtotime($date);
                    $datediff = $now - $current_date;
                    $days = round($datediff / (60 * 60 * 24));

                    $delay = $days - $value['lag_time'];

                    if ($days >= (30 + $value['lag_time'])) {

                    //$update = ['mail_send_status'=>222];

                        $status_check = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'),
                DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'))
                ->where('merchant_id', $value['merchant_id'])
                ->first()->toArray();

                        $payments = PaymentInvestors::select(DB::raw('sum(participant_share-payment_investors.mgmnt_fee) as final_participant_share'))->where('merchant_id', $value['merchant_id'])
                   ->first()->toArray();

                        if ($value['sub_status_id'] == 1 && ($payments['final_participant_share'] > $status_check['investment_amount']) &&
                            ! empty($status_check['investment_amount'])) {
                            // $new_factor_rate = MerchantUser::where('merchant_id', $value['merchant_id'])->where('status', 1)->value(DB::raw('sum(paid_participant_ishare)/sum(amount)'));

                            // merchant table updaton

                            // $data_r['old_factor_rate'] = $value['factor_rate'];
                            // $data_r['factor_rate'] = $new_factor_rate;
                            $data_r['mail_send_status'] = 222;
                            $data_r['sub_status_id'] = 20;
                            $data_r['last_status_updated_date'] = date('Y-m-d h:i:sa');

                            $changed = DB::table('merchants')->where('id', $value['merchant_id'])->update($data_r);

                            $substatus_name = SubStatus::where('id', 20)->value('name');
                            $substatus_name = str_replace(' ', '_', strtolower($substatus_name));

                            // notes added to crm

                            // update merchant status to CRM

                            $form_params = [
                                    'method' => 'merchant_update',
                                    'username' => config('app.crm_user_name'),
                                    'password' => config('app.crm_password'),
                                    'investor_merchant_id'=>$value['merchant_id'],
                                    'status'=>$substatus_name,
                               ];

                            try {
                                $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                                dispatch($crmJob);
                                //already configured delay here
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                            }

                            ////////////////////////////////

                            // merchant user table updaton

                            $investor_array = [];
                            // $investment_data = MerchantUser::where('merchant_id', $value['merchant_id'])->where('merchant_user.status', 1)->get();
                            // foreach ($investment_data as $key => $investments) {
                            //     $investor_array[$key] = $investments->user_id;
                            //     $invest_rtr = $data_r['factor_rate'] * $investments->amount;
                            //     $updt_investor_rtr = MerchantUser::where('user_id', $investments->user_id)->where('merchant_id', $investments->merchant_id)->update(['invest_rtr' =>$invest_rtr]);
                            // }

                            $complete_per = PayCalc::completePercentage($value['merchant_id']);

                            // complete per updaton

                            $this->merchant->modify_rtr($value['merchant_id'], $data_r['sub_status_id']);
                                if ($value['sub_status_id'] != 20) {
                                    $logArray = [

                                        'merchant_id'=> $value['merchant_id'],
                                        'old_status'=>$value['sub_status_id'],
                                        'current_status'=>20,
                                        'description'=>'Merchant Status changed to Default+ by system',
                                  ];
        
                                    $log = MerchantStatusLog::create($logArray);
                                }

                            Merchant::find($value['merchant_id'])
                             ->update(['complete_percentage'=>$complete_per, 'last_status_updated_date'=>$log->created_at]);

                            $m_url = url('/admin/merchants/view/'.$value['merchant_id']);

                            $msg['title'] = 'Merchant Status changed to Default+.';
                            $msg['content'] = '<a href='.$m_url.'>'.$value['merchant_name'].'</a> Status changed to Default+.';
                            $msg['merchant_id'] = $value['merchant_id'];
                            $msg['to_mail'] = $email_id_arr;
                            $msg['status'] = 'merchant_change_status';
                            $msg['unqID'] = unqID();
                            $msg['merchant_name'] = $value['merchant_name'];
                            $msg['new_status'] = 'Default+';
                            $msg['template_type'] = 'merchant_status_change_common';
                            try {
                                $email_template = Template::where([['temp_code', '=', 'MCSS'], ['enable', '=', 1]])->first();
                                if ($email_template) {
                                    if ($email_template->assignees) {
                                        $template_assignee = explode(',', $email_template->assignees);
                                        $bcc_mails = [];
                                        foreach ($template_assignee as $assignee) {
                                            $role_mail = $this->allUserRoleData($assignee)->pluck('email')->toArray();
                                            $role_mail = array_diff($role_mail, $email_id_arr);
                                            $bcc_mails[] = $role_mail;    
                                        }
                                        $msg['bcc'] = Arr::flatten($bcc_mails);
                                    }
                                    $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                                    dispatch($emailJob);
                                    $msg['bcc'] = [];
                                    $msg['to_mail'] = $admin_email;
                                    $emailJob = (new CommonJobs($msg));
                                    dispatch($emailJob);
                                }
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                            }

                            $i++;
                        }
                    }
                }
            }

            echo  $i.' merchants pending payment mail sent sucessfully';
        }
    }
    public function allUserRoleData($roles)
    {
        $return = User::select('users.creator_id', 'users.created_at', 'users.updated_at', 'users.name', 'users.email', 'users.id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id');
        if ($roles) {
            $return = $return->where('role_id', $roles);
        }
        $return = $return->where('company_status',1)->get();

        return $return;
    }
}
