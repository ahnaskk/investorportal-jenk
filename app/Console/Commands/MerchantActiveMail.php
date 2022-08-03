<?php

namespace App\Console\Commands;

use App\Jobs\CommonJobs;
use App\MailLog;
use App\Merchant;
use App\Settings;
use App\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MerchantActiveMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MerchantActiveMail:merchantactivemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconciliation notification for every 30th day';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $template_data = DB::table('template')->where('temp_code', 'RECR')->first();
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        if ($template_data) {
            $title = $template_data->title;
            $subject = $template_data->subject;
        } else {
            $title = 'Reconciliation Notification';
            $subject = 'Reconciliation request';
        }
        $merchants = Merchant::leftJoin('users', 'users.id', 'merchants.user_id')->where('merchants.active_status', 1)->where('sub_status_id', 1)->where('merchants.label', 1)->where('merchants.lender_id', 74)
           ->select([DB::raw('IF(merchants.notification_email is null,users.email,merchants.notification_email) AS notification_email'),
          'merchants.name', 'merchants.id', 'merchants.date_funded', DB::raw('DATEDIFF(NOW(),date_funded) as diff')])->get();

        if (count($merchants) > 0) {
            foreach ($merchants as $mer) {
                $day_diff = $mer->diff;
                if ($day_diff > 0) {
                    if ($mer->notification_email == null) {
                        $values = [
                                   'title'   =>   $subject,
                                    'type' => 1,
                                    'to_mail' =>   '-',
                                    'status'  =>   'failed',
                                    'to_user_type' =>   'merchant',
                                    'to_id' => $mer->id,
                                    'to_name' => $mer->name,
                                    'failed_message'=> 'email is null',
                            ];
                        MailLog::create($values);
                    }
                    if ($day_diff % 30 == 0 && $mer->notification_email != null) {
                        $message['title'] = $title;
                        $message['merchant_name'] = $mer->name;
                        $message['subject'] = $subject;
                        $message['to_mail'] = $mer->notification_email;
                        $message['to_id'] = $mer->id;
                        $message['status'] = '30day merchant mail notification';
                        $message['days'] = $day_diff;
                        $message['unqID'] = unqID();
                        try {
                            $values = [
                                    'title'   =>   $subject,
                                    'type' => 1,
                                    'to_mail' =>   $mer->notification_email,
                                    'to_user_type' =>   'merchant',
                                    'to_id' => $mer->id,
                                    'to_name' => $mer->name,
                                    'status'  =>   'success',
                            ];
                            $email_template = Template::where([
                                ['temp_code', '=', 'RECR'], ['enable', '=', 1],
                            ])->first();
                            if ($email_template) {
                                MailLog::create($values);
                                $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                                dispatch($emailJob);
                                $message['to_mail'] = $admin_email;
                                $emailJob = (new CommonJobs($message));
                                dispatch($emailJob);
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
                            }
                        } catch (\Exception $e) {
                            $values = [
                                   'title'   =>   $subject,
                                   'type' => 1,
                                    'to_mail' =>   $mer->notification_email,
                                    'status'  =>   'failed',
                                    'to_user_type' =>   'merchant',
                                    'to_id' => $mer->id,
                                    'to_name' => $mer->name,
                                    'failed_message'=> $e->getMessage(),
                            ];
                            MailLog::create($values);
                            echo $e->getMessage();
                        }
                    } elseif ($day_diff % 30 == 0 && $mer->notification_email == null) {
                        $message['title'] = $title;
                        $message['merchant_name'] = $mer->name;
                        $message['subject'] = $subject;
                        $message['to_mail'] = $mer->notification_email;
                        $message['to_id'] = $mer->id;
                        $message['status'] = '30day merchant mail notification';
                        $message['days'] = $day_diff;
                        $message['unqID'] = unqID();
                        try {
                            $email_template = Template::where([
                                ['temp_code', '=', 'RECR'], ['enable', '=', 1],
                            ])->first();
                            if ($email_template) {
                                $message['to_mail'] = $admin_email;
                                $emailJob = (new CommonJobs($message));
                                dispatch($emailJob);
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
                            }
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}
