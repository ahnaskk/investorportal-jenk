<?php

namespace App\Console\Commands\SingleUse;

use App\Jobs\CRMjobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Merchant;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\SubStatus;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PayCalc;

class OldDefaultMerchantChange extends Command
{
    protected $signature = 'change:oldDefaultMerchant';
    protected $description = 'Change the Old Default Merchant Status And Add Adjustment Payment';

    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
        parent::__construct();
    }

    public function handle()
    {
        $Merchant = Merchant::whereIn('sub_status_id', [4, 22])
        // ->where('id',9756)
        ->get();
        $MerchantCount = Merchant::whereIn('sub_status_id', [4, 22])->count();
        foreach ($Merchant as $key => $value) {
            try {
                echo $MerchantCount - $key.') '.$value->id.' => ';
                $author = User::first()->name;
                $substatus = SubStatus::select('name')->where('id', $value->sub_status_id)->first()->toArray();
                $logArray = ['merchant_id' => $value->id, 'old_status' => $value->sub_status_id, 'current_status' => $value->sub_status_id, 'description' => 'Merchant Status changed to '.$substatus['name'].' by '.$author, 'creator_id' => 1];
                MerchantStatusLog::create($logArray);
                // $new_factor_rate = (array) DB::select(DB::raw('SELECT round(sum(paid_participant_ishare)/sum(amount),6) as factor_rate  FROM `merchant_user` WHERE merchant_id = :merchant_id'), [
                //     'merchant_id' => $value->id,
                // ]);
                // $data_r['old_factor_rate'] = $value->factor_rate;
                // $data_r['factor_rate'] = $new_factor_rate[0]->factor_rate;
                // $Merchant = Merchant::find($value->id);
                // $Merchant->update($data_r);
                $Merchant = Merchant::find($value->id);
                $this->merchant->modify_rtr($value->id, $value->sub_status_id, $delete_flag = true, $carry_delete_flag = false);
                $this->merchant->modify_rtr($value->id, $value->sub_status_id, $delete_flag = false);
                // $return_result = $value->MakeCtdAsRtrForChangingSubStatus();
                // if ($return_result['result'] != 'success') {
                //     throw new \Exception($return_result['result'], 1);
                // }
                $substatus_name = SubStatus::where('id', $value->sub_status_id)->value('name');
                $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
                // update merchant status to CRM
                $form_params = [
                    'method'              => 'merchant_update',
                    'username'            => config('app.crm_user_name'),
                    'password'            => config('app.crm_password'),
                    'investor_merchant_id'=> $value->id,
                    'status'              => $substatus_name,
                ];
                try {
                    $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                    dispatch($crmJob);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                $investor_array = MerchantUser::where('merchant_id', $value->id)->where('status', 1)->pluck('user_id', 'user_id')->toArray();
                $complete_per = PayCalc::completePercentage($value->id, $investor_array);
                Merchant::find($value->id)->update(['complete_percentage' => $complete_per]);
                $return['result'] = 'success';
            } catch (\Exception $e) {
                $return['result'] = $e->getMessage();
            }
            echo $return['result']."\n";
        }

        return 0;
    }
}
