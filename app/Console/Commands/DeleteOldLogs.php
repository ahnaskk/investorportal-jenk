<?php

namespace App\Console\Commands;

use App\ApiLog;
use App\Jobs\CommonJobs;
use App\LiquidityLog;
use App\MerchantStatusLog;
use App\Settings;
use FFM;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class DeleteOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete logs table data older than three months.';

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
        $before_date = date('Y-m-d', strtotime('-3 months'));
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        //Api Log
        $logs_api = ApiLog::where('created_at', '<=', $before_date);
        $logs_api_count = $logs_api->count();

        //Liquidity Log
        $logs_liquidity = LiquidityLog::where('created_at', '<=', $before_date);
        $logs_liquidity_count = $logs_liquidity->count();

        //Merchant Status Log
        $logs_merchant_status = MerchantStatusLog::where('created_at', '<=', $before_date);
        $logs_merchant_status_count = $logs_merchant_status->count();

        //Activity Log
        $logs_activity = Activity::where('created_at', '<=', $before_date);
        $logs_activity_count = $logs_activity->count();

        $date = FFM::date($before_date);
        if ($logs_api_count) {
            $logs_api_deleted = $logs_api->delete();

            $title = 'Api Logs';
            echo "\n $title deleted - $logs_api_deleted ";
            $this->sendMail($title, $logs_api_count, $logs_api_deleted, $date);
        }
        if ($logs_liquidity_count) {
            $logs_liquidity_deleted = $logs_liquidity->delete();

            $title = 'Liquidity Logs';
            echo "\n $title deleted - $logs_liquidity_deleted ";
            $this->sendMail($title, $logs_liquidity_count, $logs_liquidity_deleted, $date);
        }
        if ($logs_merchant_status_count) {
            $logs_merchant_status_deleted = $logs_merchant_status->delete();

            $title = 'Merchant Status Logs';
            echo "\n $title deleted - $logs_merchant_status_deleted ";
            $this->sendMail($title, $logs_merchant_status_count, $logs_merchant_status_deleted, $date);
        }
        if ($logs_activity_count) {
            $logs_activity_deleted = $logs_activity->delete();

            $title = 'Activity Logs';
            echo "\n $title deleted - $logs_activity_deleted ";
            $this->sendMail($title, $logs_activity_count, $logs_activity_deleted, $date);
        }
        echo "\n Task Completed \n";
    }

    /**
     * Execute Mail Function.
     *
     * @return int
     */
    public function sendMail($title, $delete_count, $deleted_count, $before_date)
    {
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);

        $message['title'] = $title;
        $message['content']['delete_count'] = $delete_count;
        $message['content']['deleted_count'] = $deleted_count;
        $message['content']['date'] = $before_date;

        foreach ($emailArray as $email) {
            $msg['title'] = $message['title'];
            $msg['content'] = $message['content'];
            $msg['to_mail'] = $email;
            $msg['status'] = 'delete_old_logs';
            $msg['subject'] = $message['title'];
            $msg['date'] = $before_date;
            $msg['total_count'] = $delete_count;
            $msg['deleted_count'] = $deleted_count;
            $msg['unqID'] = unqID();
            try {
                $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                dispatch($emailJob);
                $msg['to_mail'] = $admin_email;
                $emailJob = (new CommonJobs($msg));
                dispatch($emailJob);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
