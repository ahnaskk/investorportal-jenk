<?php

namespace App\Console\Commands;

use App\Jobs\CommonJobs;
use App\Merchant;
use App\Settings;
use Illuminate\Console\Command;

class MerchantDbAutoTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merchant:dbautotest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Merchant's Db Auto Test";

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
        $startdate = date('Y-m-d', strtotime('2016-01-01'));
        $enddate = date('Y-m-d', strtotime('+5 days'));

        $sub_status_ids = [4, 22, 18, 19, 20];

        $last_payment = Merchant::whereIn('sub_status_id', $sub_status_ids)
            ->where('created_at', '>=', $startdate)
            ->where('created_at', '<=', $enddate)
            ->where('last_payment_date', null)
            ->pluck('id');

        $last_status = Merchant::whereIn('sub_status_id', $sub_status_ids)
            ->where('created_at', '>=', $startdate)
            ->where('created_at', '<=', $enddate)
            ->where('last_status_updated_date', null)
            ->pluck('id');

        if ($last_payment->isNotEmpty() || $last_status->isNotEmpty()) {
            $this->sendMail($last_payment, $last_status);
        } else {
            echo "\n No data \n";
        }
    }

    /**
     * Execute Mail Function.
     *
     * @return int
     */
    public function sendMail($last_payment, $last_status)
    {
        $emails = Settings::where('keys', 'system_admin')->value('values');

        $emailArray = explode(',', $emails);
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';

        $message['title'] = 'Merchant DB Auto Test';
        $message['content']['last_payment'] = $last_payment;
        $message['content']['last_status'] = $last_status;

        foreach ($emailArray as $email) {
            $msg['title'] = $message['title'];
            $msg['content'] = $message['content'];
            $msg['to_mail'] = $email;
            $msg['status'] = 'merchant_db_auto_test';
            $msg['subject'] = $message['title'];
            $msg['unqID'] = unqID();
            $msg['last_payment'] = $last_payment;
            $msg['last_status'] = $last_status;
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
