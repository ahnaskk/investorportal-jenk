<?php

namespace App\Console\Commands;

use App\Merchant;
use App\Settings;
use EventHistory;
use FFM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SendPaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Notifications:sendpaymentnotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investor Payment Notifications';

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
        $investor_channel_id = config('app.channel_payment_id');
        $last_mob_notification_time = Settings::value('last_mob_notification_time');

        $payments = Merchant::select(['merchants.id', 'merchants.lender_id', 'merchants.name as merchant_name',
            'payment_investors.user_id', 'users.name as lender_name',
            DB::raw('sum(payment_investors.participant_share-payment_investors.mgmnt_fee) as net_amount'),
             //DB::raw('COUNT(merchants.id) as merchant_count'),
             //DB::raw('COUNT(merchants.lender_id) as lender_count'),

              DB::raw('(SELECT COUNT(merchants.lender_id) as merchant_count FROM participent_payments WHERE merchants.id = participent_payments.merchant_id GROUP BY lender_id)  lender_count'),

               DB::raw('(SELECT COUNT(merchants.id) as merchant_count FROM participent_payments WHERE merchants.id = participent_payments.merchant_id GROUP BY merchant_id)  merchant_count'),
           ])
           ->join('participent_payments', 'participent_payments.merchant_id', 'merchants.id')
           ->join('users', 'users.id', 'merchants.lender_id')
           ->join('payment_investors', 'participent_payments.id', 'payment_investors.participent_payment_id')
           ->where('participent_payments.created_at', '>', $last_mob_notification_time)
           ->groupBy('payment_investors.user_id')
           ->get()->toArray();

        if (! empty($payments)) {
            foreach ($payments as $key=>$data) {
                if ($data['net_amount'] != 0) {

                 /*
          $content=FFM::dollar($data['net_amount']) .' is collected by '.$data['lender_count'].' lenders  from '.$data['merchant_count'] .' merchants in between '.

                  */

                    // $content = FFM::dollar($data['net_amount']).' was collected from '.
                    //    FFM::datetime($last_mob_notification_time);
                    $content = FFM::dollar($data['net_amount']).' in payments were applied to your account today. ';
                    $data_array = [
                       'content'=>$content,
                               'user_id'=>$data['user_id'],
                               'title'=>'Payment Notifications',
                               'count'=>$data['merchant_count'],
                               'channel_id'=>$investor_channel_id,
                               'timestamp'=>time(),
                               'app_status'=>'investor_app',

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
