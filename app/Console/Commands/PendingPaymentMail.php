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

class PendingPaymentMail extends Command
{
    protected $signature = 'PendingPaymentMail:PendingPaymentMail';
    protected $description = '1 months no payment pending payment';

    public function __construct(IMerchantRepository $merchant)
    {
        parent::__construct();
        $this->merchant = $merchant;
    }

    public function sortByOrder($a, $b)
    {
        return $a['delay'] - $b['delay'];
    }

    public function handle()
    {   
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        $emails = Settings::value('email');
        $email_id_arr = explode(',', $emails);
        $date = '';
        $update = [];
        $merchants = Merchant::select('last_payment_date', 'merchants.id as merchant_id', 'factor_rate', 'merchants.name as merchant_name', 'lag_time', 'users.name as lender_name', 'company', 'sub_statuses.name as substatus_name', 'merchants.sub_status_id')
        ->where('merchants.active_status', 1)
        ->whereNOTIn('merchants.sub_status_id', [4, 11, 18, 19, 20, 22])
        ->join('users', 'users.id', 'merchants.lender_id')
        ->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
        ->where('merchants.complete_percentage', '<', 99)
        ->where('merchants.complete_percentage', '>', 0)
        ->orderByDesc('merchants.id')
        ->where(function ($query) {
            $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+30) DAY)');
        })
        ->orderByDesc('merchants.id')->get()->toArray();
        $data_array = [];
        $i = 1;
        if (! empty($merchants)) {
            foreach ($merchants as $key=>$value) {
                echo $value['merchant_name']."\n";
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
                    $companies = CompanyAmount::where('merchant_id', $value['merchant_id'])->join('users', 'users.id', 'company_amount.company_id')
                    //->whereNotNull('max_participant')
                    ->where('max_participant', '!=', 0)
                    ->pluck('users.name', 'users.id')->toArray();
                    if ($days >= (30 + $value['lag_time'])) {
                        $data_array[] = [
                            'merchant'=>$value['merchant_name'],
                            'merchant_id'=>$value['merchant_id'],
                            'lender_name'=>$value['lender_name'],
                            'company'=>$companies,
                            'delay'=>$delay,
                            'substatus'=>$value['substatus_name'],
                        ];
                        $update = ['mail_send_status'=>111];
                        $status_check = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'),
                        DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'))
                        ->where('merchant_id', $value['merchant_id'])
                        ->first()->toArray();
                        $payments = PaymentInvestors::select(DB::raw('sum(participant_share-payment_investors.mgmnt_fee) as final_participant_share'))->where('merchant_id', $value['merchant_id'])
                        ->first()->toArray();
                        if (
                            $value['sub_status_id'] == 1 &&
                            ($payments['final_participant_share'] < $status_check['investment_amount']) &&
                            ! empty($status_check['investment_amount'])
                        ) {
                            $update['sub_status_id'] = 4;
                            if ($value['sub_status_id'] != 4) {
                                $logArray = [
                                    'merchant_id'=> $value['merchant_id'],
                                    'old_status'=>$value['sub_status_id'],
                                    'current_status'=>4,
                                    'description'=>'Merchant Status changed to Default by system!',
                                ];
                                $log = MerchantStatusLog::create($logArray);
                            }
                            $update['last_status_updated_date'] = $log->created_at;
                            $m_url = url('/admin/merchants/view/'.$value['merchant_id']);
                            $msg['title'] = 'Merchant Status changed to default!';
                            $msg['content'] = '<a href='.$m_url.'>'.$value['merchant_name'].'</a> Status changed to default.';
                            $msg['merchant_id'] = $value['merchant_id'];
                            $msg['to_mail'] = $email_id_arr;
                            $msg['status'] = 'merchant_change_status';
                            $msg['unqID'] = unqID();
                            $msg['merchant_name'] = $value['merchant_name'];
                            $msg['new_status'] = 'default';
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
                            // $new_factor_rate = (array) DB::select(DB::raw('SELECT round(sum(paid_participant_ishare)/sum(amount),4) as factor_rate  FROM `merchant_user` WHERE merchant_id = :merchant_id'), [
                            //     'merchant_id' => $value['merchant_id'],
                            // ]);
                            // $factor_rate = $new_factor_rate[0]->factor_rate;
                            // $update['old_factor_rate'] = $value['factor_rate'];
                            // $update['factor_rate'] = $factor_rate;
                            DB::table('merchants')->where('id', $value['merchant_id'])->update($update);
                            $this->merchant->modify_rtr($value['merchant_id'], $update['sub_status_id']);
                            $investor_array = [];
                            $investment_data = MerchantUser::where('merchant_id', $value['merchant_id'])->where('merchant_user.status', 1)->get();
                            foreach ($investment_data as $key => $investments) {
                                $investor_array[$key] = $investments->user_id;
                                $invest_rtr = $value['factor_rate'] * $investments->amount;
                                $updt_investor_rtr = MerchantUser::where('user_id', $investments->user_id)->where('merchant_id', $investments->merchant_id)->update(['invest_rtr' => $invest_rtr]);
                            }
                            $complete_per = PayCalc::completePercentage($value['merchant_id'], $investor_array);
                            $update['complete_percentage'] = $complete_per;
                        }
                        DB::table('merchants')->where('id', $value['merchant_id'])->update($update);
                        $substatus_name = SubStatus::where('id', 4)->value('name');
                        $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
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
                    }
                }
            }
            usort($data_array, function ($a, $b) {
                return $b['delay'] <=> $a['delay'];
            });
            $html = '';
            if ($data_array) {
                $html .= '<table class="table" width="100%" cellpadding="0" cellspacing="0">
                <tbody><tr>
                <td style="background: #eae9f1;">SI</td>
                <td style="background: #eae9f1;">Merchant</td>
                <td style="background: #eae9f1;">Lender</td>
                <td style="background: #eae9f1;">Company</td>
                <td style="background: #eae9f1;">Delay</td>
                <td style="background: #eae9f1;">Merchant Status</td>
                </tr>';
                foreach ($data_array as $key=>$value1) {
                    $url = url('/admin/merchants/view/'.$value1['merchant_id']);
                    $html .= '<tr>
                    <td>'.$i.'</td>
                    <td><a href="'.$url.'" style="color:#2d3aab;">'.$value1['merchant'].'</a></td>
                    <td>'.$value1['lender_name'].'</td><td>';
                    if ($value1['company']) {
                        foreach ($value1['company'] as $company) {
                            $html .= $company.'<br>';
                        }
                    }
                    $html .= '</td>
                    <td>'.$value1['delay'].'</td>
                    <td>'.$value1['substatus'].'</td>
                    </tr>';
                    $i++;
                }
                $html .= '</tbody></table>';
                // mail send option
                $message['title'] = 'Pending Payment';
                $message['subject'] = 'Pending Payment';
                $message['pending_payment_table'] = $html;
                $message['title'] = 'Pending Payment';
                $message['to_mail'] = $email_id_arr;
                $message['status'] = 'all_pending_payment';
                $message['unqID'] = unqID();
                try {
                    $email_template = Template::where([
                        ['temp_code', '=', 'PENDL'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        if ($email_template->assignees) {
                            $template_assignee = explode(',', $email_template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $role_mail = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                $role_mail = array_diff($role_mail, $email_id_arr);
                                $bcc_mails[] = $role_mail;   
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
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        echo ' Merchants pending payment mail sent sucessfully till '.date('Y-m-d h:i:sa')."\n";
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
