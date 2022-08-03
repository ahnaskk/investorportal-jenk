<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use InvestorHelper;
use DB;
use Spatie\Permission\Models\Role;
class SyncUserMeta extends Command
{
    protected $signature = 'sync:user_meta';
    protected $description = 'sync merchant_user to user_meta';
    public function __construct() {
        parent::__construct();
    }
    public function handle() {
        $defaultMerchantIds = DB::table('merchants')->whereIn('sub_status_id', [4, 22])->pluck('id')->toArray();
        $investor_ids    = Role::whereName('investor')->first()->users()->select('users.id')->pluck('id','id')->toArray();
        $overpayments    = Role::whereName('Over Payment')->first()->users()->select('users.id')->pluck('id','id')->toArray();
        $AgentFeeAccount = Role::whereName('Agent Fee Account')->first()->users()->select('users.id')->pluck('id','id')->toArray();
        $userIds=$investor_ids+$overpayments+$AgentFeeAccount;
        foreach ($userIds as $userId) {
            echo "\n".$userId;
            InvestorHelper::updateUserPrincipal($userId,$defaultMerchantIds);
            InvestorHelper::updatePaymentValues($userId);
        }
        return 0;
    }
}
