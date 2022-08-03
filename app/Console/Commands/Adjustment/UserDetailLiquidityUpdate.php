<?php
namespace App\Console\Commands\Adjustment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InvestorHelper;
class UserDetailLiquidityUpdate extends Command
{
    protected $signature = 'Update:UserDetailLiquidity';
    protected $description = 'To Update UserDetail Liquidity Amount';
    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        $users = DB::table('user_details_liquidity_check_view')->where('diff', '!=', 0)->pluck('user_id', 'user_id')->toArray();
        try {
            $description = 'General Liquidity Update';
            InvestorHelper::update_liquidity($users, $description, 0);
            return 'success';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}
