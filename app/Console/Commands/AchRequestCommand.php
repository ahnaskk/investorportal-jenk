<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\PaymentController;
use App\Settings;
use Carbon\Carbon;
use FFM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use MTB;
use PayCalc;
use PaymentHelper;
use Permissions;

class AchRequestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'merchant ACH automated debit request command for next working day';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PaymentController $payment)
    {
        $this->payment = $payment;
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
        $fee_types = config('custom.ach_fee_types');

        echo "\nChecking time is $checked_time \n";

        if ($today->isWeekend() || in_array($today->toDateString(), $holidays)) {
            echo "\nMerchant ACH can't be send today since its holiday. \n";

            return false;
        }
        echo "\n Today is working day. \n";

        $ach_automation_settings = (Settings::where('keys', 'ach_merchant')->value('values'));
        $ach_automation_settings = json_decode($ach_automation_settings, true);

        $ach_request_status = $ach_automation_settings['ach_request_status'] ?? 0;

        if ($ach_request_status != 1) {
            echo "\nMerchant ACH payment can't be requested since its disabled. \n";

            return false;
        }
        $ach_user_id = config('settings.ach_user_id');

        if (Auth::loginUsingId($ach_user_id)) {
            echo "\n Authentication success. \n";

            if (Permissions::isAllow('ACH', 'Edit')) {
                echo "\n User have Merchant ACH permission. \n";

                $next_working_day = PayCalc::getWorkingDay($today->addDay()->toDateString());
                $next_working_day1 = FFM::date($next_working_day);
                echo "\n Sending Merchant ACH requests for $next_working_day1. \n";

                $payments = MTB::getAchPayments($next_working_day);
                $ach_payments = [];
                foreach ($payments as $payment) {
                    $ach_payments[$payment->merchant_id]['amount'] = $payment->payment_amount;
                    echo "\n pa $payment->merchant_id $payment->payment_amount \n";

                    foreach ($fee_types as $key=> $fee_type) {
                        if (isset($payment->$key)) {
                            $ach_payments[$payment->merchant_id]['fees'][$key] = $payment->$key;
                            echo "\n pf $payment->merchant_id ".$payment->$key." \n";
                        }
                    }
                }
                $ip = \Request::ip();

                $payment_date = PayCalc::getWorkingDay(Carbon::now()->addDay()->toDateString());

                $send_ach = PaymentHelper::sendACH($payment_date, $ach_payments, $ip);
                echo "\n Completed with  ".count($send_ach['transactions'])." Transactions \n";

                Auth::logout();

                return true;
            } else {
                echo "\n Don't have Merchant ACH permission. \n";
                Auth::logout();

                return false;
            }
        } else {
            echo "\n Authentication failed. \n";

            return false;
        }
    }
}
