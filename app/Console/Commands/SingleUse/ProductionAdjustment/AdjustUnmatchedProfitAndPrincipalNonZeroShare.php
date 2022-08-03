<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\Merchant;
use App\MerchantUser;
use App\Models\Views\MerchantUserView;
use App\PaymentInvestors;
use DB;
use GPH;
use Artisan;
class AdjustUnmatchedProfitAndPrincipalNonZeroShare extends Command
{
    protected $signature   = 'adjust:non_zero_share_profit_and_principal {label=0} {merchantId=0} {investor_id=0}';
    protected $description = 'Update Zero Principal Profit for None Zero Payment';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $label = trim($this->argument('label')) ?? '';
        $merchantId = $this->argument('merchantId') ?? '';
        $investorId = $this->argument('investor_id') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $investorId = str_replace('"', '', $investorId);
        if (empty($label)) {
            $label = [1,2];
        } else {
            $label = [$this->argument('label')];
        }
        $Merchant = new Merchant;
        if ($merchantId) {
            $Merchant = $Merchant->where('id', $merchantId);
        }
        $Merchant=$Merchant->whereIn('label',$label);
        $Merchant = $Merchant->whereNotIn('sub_status_id', [4, 18, 19, 20, 22]);
        $Merchants = $Merchant->get();
        $MerchantCount = $Merchant->count();
        $data = [];
        foreach ($Merchants as $Mcount => $MerchantSingle) {
            $Mcount++;
            $count = $MerchantCount - $Mcount;
            $positive_net_effect = 0;
            $negative_net_effect = 0;
            $merchant_id = $MerchantSingle->id;
            echo "\n $count)".$merchant_id.' -';
            $PaymentInvestors = new PaymentInvestors;
            $PaymentInvestors = $PaymentInvestors->where('merchant_id', $merchant_id);
            if ($investorId) {
                $PaymentInvestors = $PaymentInvestors->where('user_id', $investorId);
            }
            $PaymentInvestors = $PaymentInvestors->where('participant_share','!=',0);
            $PaymentInvestors = $PaymentInvestors->whereRaw(DB::raw('(participant_share-mgmnt_fee-principal-profit)!=0'));
            $PaymentInvestor = $PaymentInvestors->pluck('merchant_id','merchant_id');
            if (! empty($PaymentInvestor->toArray())) {
                foreach ($PaymentInvestor as $merchant_id) {
                    Artisan::call("adjust:profit_and_prinicpal '' '' $merchant_id");
                }
            }
        }
        return 0;
    }
}
