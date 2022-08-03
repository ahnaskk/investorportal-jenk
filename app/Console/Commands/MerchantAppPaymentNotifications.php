<?php

namespace App\Console\Commands;

use App\Merchant;
use App\Settings;
use EventHistory;
use FFM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MerchantAppPaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Notifications:sendMerchantAppPaymentNotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merchant App Payment Notifications';

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
     */
    public function handle()
    {
        $merchant_channel_id = config('app.merchant_payment_channel_id');
        $last_mob_notification_time = Settings::value('last_mob_notification_time');

        $payments = Merchant::select('merchants.name as merchant_name','merchants.id as merchant_id',
            'participent_payments.payment_date', DB::raw('sum(participent_payments.payment) as total_payment'), 'users.id as user_id')
                     ->join('participent_payments', 'participent_payments.merchant_id', 'merchants.id')
                     ->leftJoin('users', 'users.id', 'merchants.user_id')
                     ->where('participent_payments.created_at', '>', $last_mob_notification_time)
                     ->where('participent_payments.is_payment', 1)
                     ->groupBy('participent_payments.payment_date')
                     ->get()->toArray();


        if (! empty($payments)) {
            foreach ($payments as $key=>$value) {
                if ($value['total_payment'] != 0) {
                    $content = 'Payment of '.FFM::dollar(round($value['total_payment'], 2)).' was successfully updated by '.$value['merchant_name'].' in your account in between '.$last_mob_notification_time.' up untill today';

                    $data_array = [
                               'content'=>$content,
                               'user_id'=>$value['user_id'],
                               'title'=>'Merchant Payment Notifications',
                               'channel_id'=>$merchant_channel_id,
                               'timestamp'=>time(),
                               'app_status'=>'merchant_app',

                            ];
                    EventHistory::moveToOneSignal($data_array);
                }
            }

            Settings::update(['last_mob_notification_time'=>date('Y-m-d H:i:s')]);

            echo 'Payments notifications sent successfully till '.$last_mob_notification_time;
        } else {
            echo 'NO more payments on '.$last_mob_notification_time;
        }
    }
}
