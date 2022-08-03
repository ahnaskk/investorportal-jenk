<?php

namespace App\Console\Commands\Adjustment;

use App\ParticipentPayment;
use App\PaymentInvestors;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveZeroParticipantAmount extends Command
{
    protected $signature = 'Remove:ZeroParticipantAmount';
    protected $description = 'To Remove the Zero Paid To Final Participant Amount';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $participent_payments = ParticipentPayment::where('payment', '!=', 0)
        ->where('final_participant_share', '=', 0)
        ->where('model','App\ParticipentPayment')
        ->get();
        try {
            foreach ($participent_payments as $participent_payment) {
                $participent_payment->payment = 0;
                $participent_payment->rcode = 83; //Rcode Not Found;
                $participent_payment->reason = 'Rcode Not Found'; //Rcode Not Found;
                $participent_payment->save();
            }

            return 'success';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}
