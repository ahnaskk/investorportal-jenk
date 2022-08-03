<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\PaymentInvestors;
use DB;
class RcodePaymentProfitChange extends Command
{
    protected $signature = 'change:rcode_profit_value';
    protected $description = 'To Remove Unwanted Profit Value For Rcode Payment';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        DB::beginTransaction();
        $PaymentInvestors=PaymentInvestors::whereHas('ParticipentPayment', function ($query) {
            $query->where('rcode','!=',0);
        });
        $PaymentInvestors=$PaymentInvestors->where('profit','!=',0);
        $PaymentInvestors=$PaymentInvestors->get();
        foreach ($PaymentInvestors as $key => $value) {
            echo $value->merchant_id."-".$value->user_id."<->".$value->profit." - ";
            if(in_array($value->merchant->sub_status_id,[4,22])){
                $Adjust=new PaymentInvestors;
                $Adjust=$Adjust->where('merchant_id',$value->merchant_id);
                $Adjust=$Adjust->where('user_id',$value->user_id);
                $Adjust=$Adjust->with('ParticipentPayment');
                $Adjust=$Adjust->latest()->first();
                echo "<------->".$Adjust->ParticipentPayment->reason."\n";
                $Adjust->profit    -=$value->profit;
                $Adjust->principal +=$value->profit;
                $Adjust->save();
            }
            $value->profit    = 0;
            $value->save();
            echo "\n";
        }
        DB::commit();
        return 0;
    }
}
