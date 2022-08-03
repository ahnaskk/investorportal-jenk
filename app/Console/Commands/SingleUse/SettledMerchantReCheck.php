<?php

namespace App\Console\Commands\SingleUse;

use App\Jobs\CRMjobs;
use App\Merchant;
use DB;
use Illuminate\Console\Command;
use Request;

class SettledMerchantReCheck extends Command
{
    protected $signature = 'recheck:settledmerchant';
    protected $description = 'Recheck the Settled Merchant Adjustment payment';

    public function handle()
    {
        $Merchant = Merchant::whereIn('sub_status_id', [18, 19, 20])->get();
        echo count($Merchant);
        foreach ($Merchant as $key => $value) {
            DB::beginTransaction();
            try {
                $sub_status_id = $value->sub_status_id;
                echo "\n".$key.')'.$value->id;
                $value->SubStatusChange(1);
                echo ' Changed To 1 - ';
                $value->SubStatusChange($sub_status_id);
                echo " Changed To $sub_status_id";
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
            }
        }

        return 0;
    }
}
