<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\PaymentController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use PaymentHelper;
use Permissions;

class AchInvestorStatusCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:investorcheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investor ACH processing status check';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PaymentController $controller)
    {
        $this->controller = $controller;
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
        echo "\nChecking time is $checked_time \n";
        $holidays = array_keys(config('custom.holidays'));

        if ($today->isWeekend() || in_array($today->toDateString(), $holidays)) {
            echo "\nACH investor status can't be checked today since its holiday. \n";

            return false;
        }
        echo "\n Today is a working day. \n";
        $ach_user_id = config('settings.ach_user_id');

        if (Auth::loginUsingId($ach_user_id)) {
            echo "\n Authentication successful! \n";
            if (Permissions::isAllow('Investor Ach', 'Edit')) {
                echo "\n User have Investor ACH permission. \n";
                $check_status = PaymentHelper::achCheckStatusAutomatic();
                Auth::logout();
                if ($check_status) {
                    echo "\n Investor ACH status checked successfully \n";

                    echo "\n Checking Pending ACH to be removed \n";
                    $pending_ach_to_remove = PaymentHelper::removeInvestorACHPending();
                    if ($pending_ach_to_remove['status']) {
                        echo "\n ".$pending_ach_to_remove['count']." Pending ACH for more than 9 days.\n";
                        echo "\n Details will be mailed.\n";
                    } else {
                        echo "\n No Pending ACH for more than 9 days.\n";
                    }

                    return true;
                } else {
                    echo "\n No data to check. \n";
                }

                return false;
            } else {
                echo "\n Don't have Investor ACH permission. \n";
                Auth::logout();

                return false;
            }
        } else {
            echo "\n Authentication failed! \n";

            return false;
        }
    }
}
