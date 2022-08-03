<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use PaymentHelper;
use Permissions;

class AchInvestorStatusReCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:investorrecheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Confirmed Investor ACH status re check for returns';

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
        echo "\nChecking time is $checked_time \n";
        $holidays = array_keys(config('custom.holidays'));

        if ($today->isWeekend() || in_array($today->toDateString(), $holidays)) {
            echo "\nACH investor status can't be re checked today since its holiday. \n";

            return false;
        }
        echo "\n Today is a working day. \n";
        $ach_user_id = config('settings.ach_user_id');

        if (Auth::loginUsingId($ach_user_id)) {
            echo "\n Authentication successful! \n";
            if (Permissions::isAllow('Investor Ach', 'Edit')) {
                echo "\n User have Investor ACH permission. \n";
                $check_status = PaymentHelper::investorAchReCheckStatus();
                Auth::logout();
                if (count($check_status)) {
                    echo "\n Investor ACH status rechecked successfully. \n";

                    return true;
                } else {
                    echo "\n No data to check. \n";
                }
            } else {
                echo "\n Don't have Investor ACH permission. \n";
                Auth::logout();
            }
        } else {
            echo "\n Authentication failed! \n";
        }
        return false;
    }
}
