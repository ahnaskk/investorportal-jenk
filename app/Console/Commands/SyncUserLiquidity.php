<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use InvestorHelper;
class SyncUserLiquidity extends Command
{
    protected $signature = 'sync:user_liquidity {description="Liqduity Update"} ';
    // php artisan sync:user_liquidity "Historical Data Updation";
    protected $description = 'To update user liquidity';
    public function __construct() {
        parent::__construct();
    }
    public function handle() {
        $investor_ids    = Role::whereName('investor')->first()->users()->select('users.id')->pluck('id','id')->toArray();
        $overpayments    = Role::whereName('Over Payment')->first()->users()->select('users.id')->pluck('id','id')->toArray();
        $AgentFeeAccount = Role::whereName('Agent Fee Account')->first()->users()->select('users.id')->pluck('id','id')->toArray();
        $users = $investor_ids+$overpayments+$AgentFeeAccount;
        $description = $this->argument('description');
        InvestorHelper::update_liquidity($users, $description, $merchant_id = '', $liquidity_adjuster = '');
        return 0;
    }
}
