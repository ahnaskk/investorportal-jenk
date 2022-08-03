<?php

namespace App\Console\Commands;

use App\Bank;
use App\Library\Repository\Interfaces\IInvestorRepository;
use App\Settings;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Permissions;

class AchSyndicatePaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:syndicate';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investor Syndicate payment through ACH method';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IInvestorRepository $investor)
    {
        $this->investor = $investor;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ach_investor = (Settings::where('keys', 'ach_investor')->value('values'));
        $ach_investor = json_decode($ach_investor, true);
        $ach_syndicate_status = $ach_investor['ach_syndicate_status'] ?? 0;
        if ($ach_syndicate_status != 1) {
            echo "\nACH Syndicate payment can't be send since its disabled. \n";

            return false;
        }
        $today = Carbon::now();
        $checked_time = $today->toDateTimeString();
        echo "\nChecking time is $checked_time \n";
        $holidays = array_keys(config('custom.holidays'));
        if ($today->isWeekend() || in_array($today->toDateString(), $holidays)) {
            echo "\nACH Syndicate payment can't be send today since its holiday. \n";

            return false;
        }
        echo "\n Today is a working day. \n";
        $ach_user_id = config('settings.ach_user_id');
        if (Auth::loginUsingId($ach_user_id)) {
            echo "\n Authentication successful! \n";
            if (Permissions::isAllow('Syndication Payment', 'Edit')) {
                echo "\n User have Syndicate Payment permission. \n";
                $investors =  $this->investor->iSyndicationPaymentsData(null, $auto_syndicate_payment = 1);
                $investors_data = $investors['data'];
                $data_with_payments = [];
                foreach ($investors_data as $data) {
                    if ($data['syndication_check'] && $data['auto_syndicate_payment']) {
                        echo "\n".$data['investor_id'].' with payment of '.$data['syndication_amount'];
                        $data_with_payments[] = [
                            'investor_id' => $data['investor_id'],
                            'liquidity' => $data['liquidity'],
                            'net_amount' => $data['net_amount'],
                            'syndication_check' => $data['syndication_check'],
                            'syndication_amount' => $data['syndication_amount'],
                        ];
                    }
                }
                $total_payments = count($data_with_payments);
                if ($total_payments) {
                    echo "\n Requesting ".$total_payments." Transactions \n";
                    DB::beginTransaction();
                    try {
                        $transactions = [];
                        foreach ($data_with_payments as $payment) {
                            $single = [];
                            if ($payment['syndication_amount']) {
                                $type = 'credit';
                                if ($payment['syndication_amount'] < 0) {
                                    $type = 'debit';
                                }
                                $single['amount'] = $payment['syndication_amount'];
                                $single['investor_id'] = $payment['investor_id'];
                                $single['type'] = $type;
                                $transactions[] = $single;
                            }
                        }
                        $return_function = $this->investor->iSendSyndicationPaymentsFunction($transactions);
                        $success_message = $return_function['success_message'];
                        $error_message = $return_function['error_message'];
                        $success_count = $return_function['success_count'];
                        $error_count = $return_function['error_count'];
                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollback();
                        echo "\n error ".$e->getMessage();
                    }
                    if ($success_count) {
                        echo "\n Completed ".$success_count." Transactions \n";
                        echo "\n ".$success_message." \n";
                    }
                    if ($error_count) {
                        echo "\n".$error_count." Error(s) \n";
                        echo "\n ".$error_message." \n";
                    }
                } else {
                    $return_function = $this->investor->iSendSyndicationPaymentsMail($data_with_payments, $today->toDateString());
                    echo "\n No payments. \n";
                }
                Auth::logout();

                return true;
            } else {
                echo "\n Don't have Syndicate ACH permission. \n";
                Auth::logout();

                return false;
            }
        } else {
            echo "\n Authentication failed! \n";

            return false;
        }
    }
}
