<?php
namespace App\Console\Commands\Adjustment;
use Illuminate\Console\Command;
use App\Merchant;
use App\ParticipentPayment;
use App\Models\Views\MerchantUserView;
use App\PaymentInvestors;
use App\Settings;
use Illuminate\Support\Facades\DB;
class RemoveOldMissMatchedRcodePayment extends Command
{
    protected $signature = 'Update:MisMatchedPaymentAndFinalShare {greater_than=1}';
    protected $description = 'Command description';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $greater_than = $this->argument('greater_than') ?? '';
        $Merchant=new Merchant;
        $Merchant=$Merchant->whereNotIn('sub_status_id',[4,22,18,19,20]);
        // $Merchant=$Merchant->where('id',8012);
        if ($greater_than) {
            $Merchant = $Merchant->where('id', '>', $greater_than);
        }
        $Merchant=$Merchant->whereNotIn('id',[9783,7939,8020]);
        $Merchant=$Merchant->get(['id']);
        $count=count($Merchant->toArray());
        foreach ($Merchant as $key => $MerchantSingle) {
            DB::beginTransaction();
            $merchant_id=$MerchantSingle->id;
            echo $count-$key.")".$merchant_id."\n";
            $MerchantUserView=MerchantUserView::where('merchant_id',$merchant_id)->get();
            $max_participant_fund_per = MerchantUserView::select(DB::raw('funded/sum(amount) as max_participant_fund_per'));
            $max_participant_fund_per=$max_participant_fund_per->where('merchant_id', $merchant_id);
            $max_participant_fund_per=$max_participant_fund_per->first()->max_participant_fund_per;
            $ParticipentPayment = ParticipentPayment::where('payment', '!=', 0)
            ->where('merchant_id', $merchant_id)
            ->where('model',"App\ParticipentPayment")
            ->get();
            foreach ($ParticipentPayment as $value) {
                $participent_payment_id=$value->id;
                $mgmnt_fee=round(PaymentInvestors::where('participent_payment_id',$participent_payment_id)->sum('mgmnt_fee'),2);
                $participant_share=round(PaymentInvestors::where('participent_payment_id',$participent_payment_id)->sum('participant_share'),2);
                $expected_participant_share=round($value->payment/$max_participant_fund_per,2);
                if($expected_participant_share!=$participant_share){
                    $diff=$expected_participant_share-$participant_share;
                    $diff=round($diff,2);
                    if(abs($diff)>2){
                        $defected_percentage=round($diff/$expected_participant_share*100,2);
                        if($defected_percentage>=100){
                            echo "payment ".$value->payment." - Expected Share ".$expected_participant_share." Given Share ".$participant_share." Defected % ".$defected_percentage."\n";
                            $value->payment=0;
                            $value->rcode  = 83; //Rcode Not Found;
                            $value->reason = 'Rcode Not Found'; //Rcode Not Found;
                            $value->save();
                            DB::table('payment_investors')
                            ->where('participent_payment_id',$participent_payment_id)
                            ->update([
                                'participant_share'       =>0,
                                'actual_participant_share'=>0,
                                'principal'               =>0,
                                'profit'                  =>0,
                            ]);
                        }
                    }
                }
            }
            DB::commit();
        }
        return 0;
    }
}
