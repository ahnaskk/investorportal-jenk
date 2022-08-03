<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Artisan;
class RemoveDataByPaymentInvestor extends Command
{
    protected $signature = 'sync:payment_investors_data';
    protected $description = 'Update Merchant User And Merchant Data After Remove Payment Invsetors';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        echo "\n sync:merchant_user stated";
        Artisan::call('sync:merchant_user');
        echo "\n sync:merchant_user ended";
        echo "\n sync:profit_and_principal stated";
        Artisan::call('sync:profit_and_principal');
        echo "\n sync:profit_and_principal ended";
        echo "\n sync:user_meta stated";
        Artisan::call('sync:user_meta');
        echo "\n sync:user_meta ended";
        echo "\n update:MerchantCompletedPercentage stated";
        Artisan::call('update:MerchantCompletedPercentage');
        echo "\n update:MerchantCompletedPercentage ended";
        echo "\n Update:UserDetailLiquidity stated";
        Artisan::call('Update:UserDetailLiquidity');
        echo "\n Update:UserDetailLiquidity ended";
        return 0;
    }
}
