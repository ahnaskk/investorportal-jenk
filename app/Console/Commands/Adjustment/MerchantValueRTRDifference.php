<?php

namespace App\Console\Commands\Adjustment;

use App\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MerchantValueRTRDifference extends Command
{
    protected $signature = 'Update:MerchantValueRTRDifference';
    protected $description = 'To Update Merchant RTR Difference';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $merchants = DB::table('participent_payments_check_view')->where('rtr_diff', '!=', 0)->pluck('merchant_id', 'merchant_id')->toArray();
        try {
            foreach ($merchants as $merchant_id) {
                $Merchant = Merchant::find($merchant_id);
                $Merchant->rtr = round($Merchant->funded * $Merchant->factor_rate, 4);
                $Merchant->funded = round($Merchant->Merchant->rtr / $Merchant->factor_rate, 4);
                $Merchant->payment_amount = round($Merchant->rtr / $Merchant->pmnts, 4);
                $Merchant->save();
            }

            return 'success';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}
