<?php

namespace App\Console\Commands;

use App\Merchant;
use App\Settings;
use EventHistory;
use FFM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SendMerchantPaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Notifications:sendmerchantpaymentnotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Merchant Payment Notifications';

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

        $payments = Merchant::select(['merchants.id', 'merchants.lender_id', 'merchants.name as merchant_name', 'merchants.user_id as merchant_user_id',
            'payment_investors.user_id', 'users.name as lender_name',
            DB::raw('sum(payment_investors.participant_share-payment_investors.mgmnt_fee) as net_amount'),
            DB::raw('COUNT(payment_investors.user_id) as investor_count'), ])
           ->join('participent_payments', 'participent_payments.merchant_id', 'merchants.id')
           ->join('users', 'users.id', 'merchants.lender_id')
           ->join('payment_investors', 'participent_payments.id', 'payment_investors.participent_payment_id')
           ->where('participent_payments.created_at', '>', $last_mob_notification_time)
           ->groupBy('payment_investors.merchant_id')
           ->get()->toArray();


        if (! empty($payments)) {
            $options = [
                  'cluster' => 'us2',
                  'useTLS' => true,
                 ];

            foreach ($payments as $key=>$data) {
                if ($data['net_amount'] != 0) {
                    $content = 'Payment of '.FFM::dollar(round($data['net_amount'], 2)).' was successfully updated by '.$data['merchant_name'].' to '.$data['investor_count'].' investors in between '.$last_mob_notification_time.' up untill today';

                    $data_array = [
                               'content'=>$content,
                               'user_id'=>$data['merchant_user_id'],
                               'title'=>'Payment Notifications',
                               'count'=>$data['investor_count'],
                               'channel_id'=>$merchant_channel_id,
                               'timestamp'=>time(),
                               'app_status'=>'merchant_app',
                            ];

                    EventHistory::moveToOneSignal($data_array);
                } else {
                    //echo "no ";
                }
            }

            Settings::where('id', 1)->update(['last_mob_notification_time'=>date('Y-m-d H:i:s')]);

            echo 'Payments notifications sent successfully till '.$last_mob_notification_time;
        } else {
            echo 'NO more payments on '.$last_mob_notification_time;
        }
    }
}
