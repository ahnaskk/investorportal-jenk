<?php

namespace App\Console\Commands\SingleUse\ProductionAdjustment;

use App\Merchant;
use GPH;
use Illuminate\Console\Command;

class PaymentToMarchantUserSync extends Command
{
    protected $signature = 'sync:merchant_from_payments';
    protected $description = 'update Merchant User Feilds By Payment Value';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $label = [1, 2];
        $Merchant = new Merchant;
        $Merchant = $Merchant->whereIn('label', $label);
        $Merchant = $Merchant->whereNotIn('sub_status_id', [4, 18, 19, 20, 22]);
        $Merchants = $Merchant->get();
        $MerchantCount = $Merchant->count();
        foreach ($Merchants as $Mcount => $MerchantSingle) {
            $Mcount++;
            $count = $MerchantCount - $Mcount;
            $merchant_id = $MerchantSingle->id;
            echo "\n $count)".$merchant_id.' -';
            GPH::PaymentToMarchantUserSync($merchant_id);
        }

        return 0;
    }
}
