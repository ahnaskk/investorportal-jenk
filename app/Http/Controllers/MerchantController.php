<?php

namespace App\Http\Controllers;

use App\Jobs\CommonJobs;
use App\ReconciliationStatus;
use App\Settings;
use App\Template;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    public function index()
    {
        return view('vue.merchants');
    }

    public function reconciliationStatus($id)
    {   
        $settings = Settings::where('keys', 'admin_email_address')->first();
        $admin_email = $settings->values;
        $input_arr = unserialize((urldecode($id)));
        $merchant_id = $input_arr['merchant_id'];
        $status = ($input_arr['status'] == 1) ? 'yes' : 'no';
        $days = $input_arr['day'];
        $ip_address = $this->getUserIp();
        $values = ['merchant_id' => $merchant_id, 'reconciliation_status' => $status, 'days' => $days, 'ip' => $ip_address];
        ReconciliationStatus::create($values);
        if ($status == 'yes') {
            $merchant = DB::table('merchants')->where('id', $merchant_id)->value('name');
            $message['to_mail'] = DB::table('users')->where('id', 1)->value('email');
            $message['status'] = 'reconcilation request mail to admin';
            $message['title'] = 'Reconciliation Request';
            $message['subject'] = 'Reconciliation Request by merchant';
            $message['merchant_name'] = $merchant;
            $message['merchant_id'] = $merchant_id;
            $message['unqID'] = unqID();
            $email_template = Template::where([
                ['temp_code', '=', 'RERQA'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                dispatch($emailJob);
                $message['to_mail'] = $admin_email;
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
                $emails = Settings::value('email');
                $email_id_arr = explode(',', $emails);
                $message['to_mail'] = $email_id_arr;
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $email_id_arr);
                        $bcc_mails[] = $role_mails;    
                    }
                    $message['bcc'] = Arr::flatten($bcc_mails);
                }
                $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                dispatch($emailJob);
            }
        }

        return view('merchant.reconcilation_view', ['status' => $status]);
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
}
