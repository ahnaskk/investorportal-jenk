<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\PaymentController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Permissions;

class DoubleCheckAchStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:doublecheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Settled Merchant ACH request again for status change';

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
        echo "\nChecking time is $today \n";

        $holidays = array_keys(config('custom.holidays'));

        if ($today->isWeekend() || in_array($today->toDateString(), $holidays)) {
            echo "\nMerchant ACH can't be double checked today since its holiday. \n";

            return false;
        }
        echo "\n Today is working day. \n";

        $ach_user_id = config('settings.ach_user_id');

        if (Auth::loginUsingId($ach_user_id)) {
            echo "\n Authentication success. \n";

            if (Permissions::isAllow('ACH', 'Edit')) {
                echo "\n User have Merchant ACH permission. \n";

                $check_status = $this->payment->achDoubleCheckStatus();
                // echo " $check_status";
                Auth::logout();
                if (count($check_status)) {
                    echo "\n".count($check_status)." Merchant ACH status double checked successfully. \n";

                    return true;
                } else {
                    echo "\n No data to check. \n";
                }

                return false;
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
