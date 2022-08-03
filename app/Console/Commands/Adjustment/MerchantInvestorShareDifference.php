<?php

namespace App\Console\Commands\Adjustment;

use App\MerchantUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MerchantInvestorShareDifference extends Command
{
    protected $signature = 'Update:MerchantInvestorShareDifference';
    protected $description = 'Update Investor`s Completed Share In Merchant User Table';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $merchants = DB::table('investor_share_check_view');
        // $merchants = $merchants->where('merchant_id',7618);
        $merchants = $merchants->select('merchant_id');
        $merchants = $merchants->pluck('merchant_id', 'merchant_id')->toArray();
        foreach ($merchants as $merchant_id) {
            echo "\n".$merchant_id;
            $investor_share_check_view = DB::table('investor_share_check_view')
            ->where('diff', '!=', 0)
            ->where('merchant_id', $merchant_id)
            ->get();
            foreach ($investor_share_check_view as  $single) {
                $Self = MerchantUser::where('merchant_id', $merchant_id)->where('user_id', $single->investor_id)->first();
                DB::table('merchant_user')
                ->where('id', $Self->id)
                ->update([
                    'complete_per'=>$single->actual_completed_percentage,
                ]);
                echo "\n".$single->investor_id.':';
                echo $single->actual_completed_percentage;
            }
        }

        return true;
    }
}
