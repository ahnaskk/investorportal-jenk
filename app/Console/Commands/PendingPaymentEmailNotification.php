<?php
/*

    If the deal is active and its late. based on lag lender time. then everyday an email. (not just
              1=>15,
            2=>30,
            3=>45,
            4=>60,
            5=>75,
            6=>90,)


*/

namespace App\Console\Commands;

use App\Jobs\CommonJobs;
use App\Merchant;
use App\Settings;
use App\Template;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PayCalc;

class PendingPaymentEmailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PendingPaymentEmailNotification:pendingpaymentemailnotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'if no payment in last 10 days.';

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
     * @return mixed
     *
     * Sends email 10 days and 4 months when no payment.
     */
    public function handle()
    {

        /*        if($mail_status==1)
        {
                $days_ago="-10 days";

        }
        elseif ($mail_status) {
            # code...
              $days_ago="-4 months";

        }*/

        $this->sendMail();
    }

    public function sendMail($value = '')
    {
        $array_send_recc = [
            1=>15,
            2=>20,
            3=>25,
            // 4=>60,
            // 5=>75,
            // 6=>90,
        ];

        // 15, 20, 25, 30

        foreach ($array_send_recc as $mail_status => $value) {
            $emails = Settings::value('email');
            $email_id_arr = explode(',', $emails);
            $date = '';

            $days_ago2 = date('m-d-Y', strtotime("-$value days"));

            $merchant_data = Merchant::select(['merchants.id', 'merchants.name', 'funded', 'pmnts', 'date_funded', 'marketplace_status', 'paid_count', 'commission', 'rtr', 'merchants.complete_percentage', 'last_payment_date', 'users.lag_time'])
             ->where('mail_send_status', '!=', 111)
            // select past status merchants.
        ->leftjoin('users', 'users.id', 'merchants.lender_id')
        ->where('merchants.active_status', 1)
        //->where('merchants.id',9337)
        ->where('merchants.sub_status_id', 1)
        ->where('merchants.complete_percentage', '<', 99)
        //->where('merchants.last_payment_date', '<', $days_ago2)
        ->where(function ($query) use ($value) {
            $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+'.$value.') DAY)');
        })
        ->groupBy('merchants.id')->orderByDesc('merchants.id')->get();

            if (! empty($merchant_data)) {
                foreach ($merchant_data as $data) {
                    echo 'sending';
                    $last_payment_date = $data->last_payment_date;
                    $date = ! empty($last_payment_date) ? $last_payment_date : $data->date_funded;
                    $date = date('Y-m-d', strtotime($date));
                    $from = $date;
                    $to = date('Y-m-d');
                    $now = strtotime($to);
                    $your_date = strtotime($date);
                    $datediff = $now - $your_date;
                    $days = round($datediff / (60 * 60 * 24));

                    // $from = Carbon::parse($date);
                    // $to = Carbon::parse($to);
                    // $days = PayCalc::calculateWorkingDays($from, $to);

                    // echo $value+$data->lag_time;// included holidays

                    $delay = $days - $data->lag_time;

                    $display_days = $days + $data->lag_time;

                    $m_url = url('/admin/merchants/view/'.$data->id);

                    if ($days >= $value + $data->lag_time) {
                        $message['title'] = 'Pending Payment';
                        $message['content'] = ' Merchant <a href='.$m_url.'>'.$data->name.'</a> has payments pending for '.$delay.' days ';
                        $message['to_mail'] = $email_id_arr;
                        $message['status'] = 'pending_payment';
                        $message['merchant_name'] = $data->name;
                        $message['merchant_id'] = $data->id;
                        $message['subject'] = 'Pending Payment from '.date('m-d-Y', strtotime($last_payment_date));
                        $message['days'] = $delay;
                        $message['date'] = date('m-d-Y', strtotime($last_payment_date));
                        $message['unqID'] = unqID();

                        try {
                            $email_template = Template::where([
                                ['temp_code', '=', 'PENDP'], ['enable', '=', 1],
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
                                $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
                                $message['bcc'] = [];
                                $message['to_mail'] = $admin_email;
                                $emailJob = (new CommonJobs($message));
                                dispatch($emailJob);
                                DB::table('merchants')->where('id', $data->id)->update(['mail_send_status' =>111]);
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
