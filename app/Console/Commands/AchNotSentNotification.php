<?php

namespace App\Console\Commands;

use App\AchRequest;
use App\Jobs\CommonJobs;
use App\Settings;
use Carbon\Carbon;
use FFM;
use Illuminate\Console\Command;
use PayCalc;

class AchNotSentNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:requeststatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Ach Sent or not and send Notification';

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
        $today = Carbon::now();
        $checked_time = $today->toDateTimeString();
        $holidays = array_keys(config('custom.holidays'));
        echo "\nChecking time is $checked_time \n";

        if ($today->isWeekend() || in_array($today->toDateString(), $holidays)) {
            echo "\nMerchant ACH can't be send today since its holiday. \n";

            return false;
        }
        echo "\n Today is working day. \n";
        $next_working_day = PayCalc::getWorkingDay($today->addDay()->toDateString());
        $next_working_day1 = FFM::date($next_working_day);

        $ach_requests = AchRequest::where('payment_date', $next_working_day);
        $count = $ach_requests->count();

        if ($count) {
            $title = "$count Merchant ACH requests found for $next_working_day1";
            echo "\n $title. \n";
            $ach_requests_processing = with(clone $ach_requests)->where('ach_request_status', 1)->where('ach_status', 0)->count();
            echo "\n $ach_requests_processing Processing Merchant ACH requests found for $next_working_day. \n";
            $ach_requests_declined = with(clone $ach_requests)->where('ach_request_status', -1)->count();
            echo "\n $ach_requests_declined Declined Merchant ACH requests found for $next_working_day. \n";
            $data = [
                'total' => $count,
                'processing' => $ach_requests_processing,
                'declined' => $ach_requests_declined,
            ];
        // $this->sendMail($next_working_day, $title,$checked_time, $data);
        } else {
            $title = "Merchant ACH not sent for $next_working_day1";
            echo "\n $title. \n";
            echo "\n Sending notification mail. \n";
            $this->sendMail($next_working_day, $title, $checked_time);
        }

        return true;
    }

    /**
     * Execute Mail Function.
     *
     * @return int
     */
    public function sendMail($date, $title, $checked_time, $data = null)
    {
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        $message['title'] = $title;
        $msg['title'] = $message['title'];
        $msg['to_mail'] = $emailArray;
        $msg['status'] = 'ach_not_sent';
        $msg['subject'] = $message['title'];
        $message['content']['ach_date'] = FFM::date($date);
        $message['content']['checked_time'] = FFM::datetime($checked_time);
        $message['content']['count'] = $data['total'] ?? 0;
        $message['content']['processing'] = $data['processing'] ?? 0;
        $message['content']['declined'] = $data['declined'] ?? 0;
        $message['content']['type'] = ($data == null) ? 0 : 1;
        $msg['unqID'] = unqID();
        $msg['content'] = $message['content'];

        try {
            $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(15));
            dispatch($emailJob);
            $msg['to_mail'] = $admin_email;
            $emailJob = (new CommonJobs($msg));
            dispatch($emailJob);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
