<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\ParticipentPayment;
use App\PaymentInvestors;
use GPH;
class InvestorId extends Command
{
    protected $signature = 'update:investor_ids';
    protected $description = 'Update Investor Ids in payment investors';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $data=[['value'=>"1000",'sign'=>">="],['value'=>"1000",'sign'=>"<"]];
        foreach ($data as $level) {
            $ParticipentPayment = new ParticipentPayment;
            $ParticipentPayment = $ParticipentPayment->whereNull('investor_ids');
            $ParticipentPayment = $ParticipentPayment->where('merchant_id','!=',0);
            $ParticipentPayment = $ParticipentPayment->where('payment',$level['sign'],$level['value']);
            $ParticipentPayment = $ParticipentPayment->orderBy('merchant_id');
            $ParticipentPayment = $ParticipentPayment->get();
            echo "\n".$ParticipentPayment->count();
            foreach ($ParticipentPayment as $key => $value) {
                $merchant_id = $value->merchant_id;
                echo "\n ".$merchant_id;
                $participent_payment_id=$value->id;
                $investor_ids=PaymentInvestors::where('participent_payment_id', $participent_payment_id);
                $investor_ids=$investor_ids->pluck('user_id','user_id');
                $investor_ids=$investor_ids->toArray();
                if(empty($investor_ids)){
                    $investor_ids = GPH::getMerchantInvestorByMerchantId($merchant_id);
                }
                $value->investor_ids=implode(',',$investor_ids);
                $value->save();
            }
        }
        echo "\n End \n";
        return 0;
    }
}
