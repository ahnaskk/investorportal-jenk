<?php

namespace App\Console\Commands;

use App\Merchant;
use App\MerchantStatusLog;
use App\SubStatus;
use App\UserActivityLog;
use Illuminate\Console\Command;

class AdvancecompleteToDefaultPlusInLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:change_advance_complete_less_to_default_plus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change Advance completed less to default+ in logs.';

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
        $merchant_status_logs = MerchantStatusLog::where('description', 'like', '%Advance completed for less%')->get();
        $substatus = SubStatus::select('name')->where('id', '20')->first()->toArray();
        foreach ($merchant_status_logs as $log) {
            $log->description = str_replace('Advance Completed for Less', $substatus['name'], $log->description);
            $log->save();
        }
        $merchant_status_logs2 = MerchantStatusLog::where('description', 'like', '%Default plus%')->get();
        foreach ($merchant_status_logs2 as $log) {
            $log->description = str_replace('Default Plus', $substatus['name'], $log->description);
            $log->save();
        }
        MerchantStatusLog::where('current_status', 20)->where('description', 'like', '%settled%')->update(['description' => 'Merchant Status changed to '.$substatus['name']]);
        $merchant_status_logs3 = MerchantStatusLog::where('description', 'like', '%by automatically%')->get();
        foreach ($merchant_status_logs3 as $log) {
            $log->description = str_replace('by automatically', 'by system', $log->description);
            $log->save();
        }
        echo "Status Changed to Default+ from Advance completed for less in merchant status logs.";
        $user_act_logs = UserActivityLog::where('detail', 'like', '%Advance completed for less%')->get();
        foreach ($user_act_logs as $ulog) {
            $detail = json_decode($ulog->detail, true);
            // preg_match_all('~<%(.*?)%>~s',$string,$datas);
            $new_detail = [];
            foreach ($detail as $key => $d) {
                if (is_array($d)) {
                    if(in_array('Advance Completed for Less', $d)) {
                        $a = [];
                        foreach ($d as $k => $v) {
                            $v = str_replace('Advance Completed for Less', $substatus['name'], $v);
                            $a[$k] = $v;
                        }
                        $d = $a;
                    }
                } else {
                    if (strpos($d, 'Advance Completed for Less') !== false) {
                        $d = str_replace('Advance Completed for Less', $substatus['name'], $d);
                    }    
                }
                $new_detail[$key] = $d;
            }
            $ulog->detail = json_encode($new_detail);
            $ulog->save();
        }
        echo "\n";
        echo "Status Changed to Default+ from Advance completed for less in user activity logs.";
        return 0;
    }
}
