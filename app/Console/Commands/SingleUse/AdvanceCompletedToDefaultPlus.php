<?php

namespace App\Console\Commands\SingleUse;

use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Merchant;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\SubStatus;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PayCalc;

class AdvanceCompletedToDefaultPlus extends Command
{
    protected $signature = 'change:AdvanceCompletedToDefaultPlus';
    protected $description = 'Change the Old Advanced Complted Status To Default Plus Status for Below 100 %';

    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
        parent::__construct();
    }

    public function handle()
    {
        // Moving Advance completed % to Default + for below 100 % merchant
        $Merchant = Merchant::whereIn('sub_status_id', [11])
        ->where('complete_percentage', '<', 100)
        ->get();
        $MerchantCount = Merchant::whereIn('sub_status_id', [11])->where('complete_percentage', '<', 100)->count();
        foreach ($Merchant as $key => $value) {
            try {
                echo $MerchantCount - $key.') '.$value->id.' => ';
                $author = User::first()->name;
                $substatus = SubStatus::select('name')->where('id', '20')->first()->toArray();
                if ($value->sub_status_id != 20) {
                    $logArray = ['merchant_id' => $value->id, 'old_status' => $value->sub_status_id, 'current_status' => 20, 'description' => 'Merchant Status changed to '.$substatus['name'].' by '.$author, 'creator_id' => 1];
                    $log = MerchantStatusLog::create($logArray);    
                }
                $Merchant = Merchant::find($value->id);
                $this->merchant->modify_rtr($value->id, $value->sub_status_id, $delete_flag = true, $carry_delete_flag = false);
                $this->merchant->modify_rtr($value->id, 20, $delete_flag = false);
                $investor_array = MerchantUser::where('merchant_id', $value->id)->where('status', 1)->pluck('user_id', 'user_id')->toArray();
                $complete_per = PayCalc::completePercentage($value->id);
                Merchant::find($value->id)->update([
                    'complete_percentage' => $complete_per,
                    'sub_status_id' => 20, 'last_status_updated_date'=>$log->created_at,
                ]);
                $return['result'] = 'success';
            } catch (\Exception $e) {
                $return['result'] = $e->getMessage();
            }
            echo $return['result']."\n";
        }
    }
}
