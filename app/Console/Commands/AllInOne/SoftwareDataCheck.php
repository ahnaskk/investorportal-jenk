<?php
namespace App\Console\Commands\AllInOne;
use Illuminate\Console\Command;
use App\Merchant;
use App\ParticipentPayment;
use App\User;
use App\Settings;
use FFM;
use App\Jobs\CommonJobs;
use Carbon\Carbon;
use App\Exports\Data_arrExport;
use Artisan;
use Illuminate\Support\Facades\Schema;
class SoftwareDataCheck extends Command
{
    protected $signature = 'check:all {merchantId=""}';
    protected $description = 'To Check All the table values';
    public function __construct() {
        if(Schema::hasTable('users')){
        $this->overpayment_id = User::OverpaymentId();
        }
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
        parent::__construct();
    }
    public function handle() {
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        Artisan::call('sync:last_payment_date '.$merchantId);
        echo "\n Merchant Sync Started \n";
        Artisan::call('sync:merchant_user '.$merchantId);
        echo "\n Merchant Sync ended";
        echo "\n sync:user_meta stated \n";
        Artisan::call('sync:user_meta');
        echo "\n sync:user_meta ended";
        echo "\n update:MerchantCompletedPercentage stated \n";
        Artisan::call('update:MerchantCompletedPercentage '.$merchantId);
        echo "\n update:MerchantCompletedPercentage ended";
        echo "\n Update:UserDetailLiquidity stated \n";
        Artisan::call('Update:UserDetailLiquidity');
        echo "\n Update:UserDetailLiquidity ended";
        return 0;
    }
}
