<?php

namespace App\Console\Commands\SingleUse\ProductionAdjustment;

use App\Merchant;
use App\MerchantUser;
use App\Models\Views\MerchantUserView;
use App\PaymentInvestors;
use DB;
use GPH;
use Illuminate\Console\Command;

class AdjustUnmatchedProfitAndPrincipalZero extends Command
{
    protected $signature = 'adjust:profit_and_prinicpal_zero {label=1} {merchantId=""} {investor_id=""}';
    protected $description = 'Update principal profit for Zero Payment';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $label = $this->argument('label') ?? '';
        $merchantId = $this->argument('merchantId') ?? '';
        $investorId = $this->argument('investor_id') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $investorId = str_replace('"', '', $investorId);
        if (! $label) {
            $label = [2];
        } else {
            $label = [$this->argument('label')];
        }
        $Merchant = new Merchant;
        if ($merchantId) {
            $Merchant = $Merchant->where('id', $merchantId);
        }
        // $Merchant=$Merchant->whereIn('label',$label);
        $Merchant = $Merchant->whereNotIn('sub_status_id', [4, 18, 19, 20, 22]);
        $Merchants = $Merchant->get();
        $MerchantCount = $Merchant->count();
        $data = [];
        foreach ($Merchants as $Mcount => $MerchantSingle) {
            $Mcount++;
            $count = $MerchantCount - $Mcount;
            DB::beginTransaction();
            $positive_net_effect = 0;
            $negative_net_effect = 0;
            $merchant_id = $MerchantSingle->id;
            echo "\n $count)".$merchant_id.' -';
            $PaymentInvestors = new PaymentInvestors;
            $PaymentInvestors = $PaymentInvestors->where('merchant_id', $merchant_id);
            if ($investorId) {
                $PaymentInvestors = $PaymentInvestors->where('user_id', $investorId);
            }
            $PaymentInvestors = $PaymentInvestors->where('participant_share', 0);
            $PaymentInvestors = $PaymentInvestors->where(function ($query) {
                $query->where('profit', '!=', 0)
                ->orWhere('principal', '!=', 0);
            });
            $PaymentInvestor = $PaymentInvestors->get();
            $total_profit = $PaymentInvestors->sum('profit');
            $total_principal = $PaymentInvestors->sum('principal');
            if (! empty($PaymentInvestor->toArray())) {
                foreach ($PaymentInvestor as $key => $value) {
                    $value->profit = 0;
                    $value->principal = 0;
                    $value->save();
                }
                echo "\n   total_profit    : ".$total_profit;
                echo "\n   total_principal : ".$total_principal;
            }
            DB::commit();
        }

        return 0;
    }
}
