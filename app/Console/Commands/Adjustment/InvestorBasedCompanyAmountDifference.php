<?php

namespace App\Console\Commands\Adjustment;

use App\CompanyAmount;
use App\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InvestorBasedCompanyAmountDifference extends Command
{
    protected $signature = 'Update:InvestorBasedCompanyAmountDifference';
    protected $description = 'Update Investor`s Invested Amount To Company Amount';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $merchants = DB::table('participent_payments');
        // $merchants = $merchants->where('merchant_id',9731);
        $merchants = $merchants->select('merchant_id');
        $merchants = $merchants->join('merchants', 'merchants.id', 'participent_payments.merchant_id');
        $merchants = $merchants->whereIn('merchants.label', [1, 2]);
        // $merchants = $merchants->whereIn('merchants.sub_status_id', [1, 5]);
        $merchants = $merchants->pluck('merchant_id', 'merchant_id')->toArray();
        foreach ($merchants as $merchant_id) {
            echo $merchant_id."\n";
            $company_merchant_investor_amount = DB::table('company_amount_check_view')
            ->where('merchant_id', $merchant_id)
            ->get();
            CompanyAmount::updateOrCreate([
                'company_id'  => 284,
                'merchant_id' => $merchant_id,
            ], [
                'max_participant' => 0,
            ]);
            CompanyAmount::updateOrCreate([
                'company_id'  => 89,
                'merchant_id' => $merchant_id,
            ], [
                'max_participant' => 0,
            ]);
            CompanyAmount::updateOrCreate([
                'company_id'  => 58,
                'merchant_id' => $merchant_id,
            ], [
                'max_participant' => 0,
            ]);
            foreach ($company_merchant_investor_amount as  $merchantUser) {
                // if ($merchantUser->investor_company_diff) {
                if ($merchantUser->company) {
                    CompanyAmount::updateOrCreate([
                        'company_id'  => $merchantUser->company,
                        'merchant_id' => $merchant_id,
                    ], [
                        'max_participant' => $merchantUser->actual_amount,
                    ]);
                }
                // }
            }
        }

        return true;
    }
}
