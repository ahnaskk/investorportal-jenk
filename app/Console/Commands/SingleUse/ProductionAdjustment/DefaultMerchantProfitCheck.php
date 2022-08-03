<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\Merchant;
use App\PaymentInvestors;
use App\MerchantUser;
use DB;
class DefaultMerchantProfitCheck extends Command
{
    protected $signature = 'check:default_merchant_profit';
    protected $description = 'Default Merchant Profit Must Be Zero';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $Merchant=new Merchant;
        $Merchant=$Merchant->whereIn('sub_status_id',[4,22]);
        $Merchants=$Merchant->get();
        $MerchantCount=$Merchant->count();
        foreach ($Merchants as $key => $Merchant) {
            DB::beginTransaction();
            $count=$MerchantCount-$key;
            $merchant_id=$Merchant->id;
            echo $count.")".$merchant_id;
            $MerchantUser=MerchantUser::where('merchant_id',$merchant_id)->get();
            foreach ($MerchantUser as $key => $value) {
                $user_id=$value->user_id;
                echo "\n     ".$user_id;
                $profit=new PaymentInvestors;
                $profit=$profit->where('merchant_id',$merchant_id);
                $profit=$profit->where('user_id',$user_id);
                $profit=$profit->sum('profit');
                $profit=round($profit,2);
                if($profit!=0){
                    echo "<->".$profit;
                    $Adjust=new PaymentInvestors;
                    $Adjust=$Adjust->where('merchant_id',$merchant_id);
                    $Adjust=$Adjust->where('user_id',$user_id);
                    $Adjust=$Adjust->with('ParticipentPayment');
                    $Adjust=$Adjust->latest()->first();
                    $Adjust->profit    -=$profit;
                    $Adjust->principal =$Adjust->profit*-1;
                    $Adjust->save();
                }
                echo "\n";
            }
            DB::commit();
            echo "\n";
        }
        return 0;
    }
}
