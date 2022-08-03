<?php
namespace App\Console\Commands\Adjustment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\ParticipentPayment;
use App\PaymentInvestors;
use FFM;
class CheckFinalParticipantShare extends Command
{
    protected $signature = 'check:final_participant_share';
    protected $description = 'Merchant Payment And Final Participant Share';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $ParticipentPayment=new ParticipentPayment;
        $ParticipentPayment=$ParticipentPayment->where('payment','!=',0);
        $ParticipentPayment=$ParticipentPayment->where('final_participant_share',0);
        $ParticipentPayment=$ParticipentPayment->where('model','App\ParticipentPayment');
        $ParticipentPayment=$ParticipentPayment->get();
        foreach ($ParticipentPayment as $key => $value) {
            $final_participant_share=PaymentInvestors::where('participent_payment_id',$value->id)->sum(DB::raw('participant_share-mgmnt_fee'));
            echo "\n".$value->merchant_id."/".FFM::date($value->payment_date)."/".$final_participant_share;
            $value->final_participant_share = $final_participant_share;
            $value->rcode                   = 0;
            $value->reason                  = '';
            $value->save();
        }
        return 0;
    }
}
