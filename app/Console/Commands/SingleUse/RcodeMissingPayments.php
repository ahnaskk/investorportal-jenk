<?php

namespace App\Console\Commands\SingleUse;

use App\MerchantUser;
use App\ParticipentPayment;
use App\PaymentInvestors;
use Illuminate\Console\Command;

class RcodeMissingPayments extends Command
{
    protected $signature = 'rcode:missingpayments';
    protected $description = 'To Create Rcode Missing Payments';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $ParticipentPayment = ParticipentPayment::where('rcode', '!=', 0)->get();
        foreach ($ParticipentPayment as $key => $value) {
            if (empty($value->paymentInvestors->toArray())) {
                echo '------------ Create '.$value->merchant_id."\n";
                $MerchantUser = MerchantUser::where('merchant_id', $value->merchant_id)->pluck('user_id', 'id');
                foreach ($MerchantUser as $investment_id => $user_id) {
                    $single = [
                        'merchant_id'=>$value->merchant_id,
                        'investment_id'=>$investment_id,
                        'user_id'=>$user_id,
                        'participent_payment_id'=>$value->id,
                        'participant_share'=>0,
                        'actual_participant_share'=>0,
                        'mgmnt_fee'=>0,
                        'syndication_fee'=>0,
                        'actual_overpayment'=>0,
                        'overpayment'=>0,
                        'balance'=>0,
                        'principal'=>0,
                        'profit'=>0,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s'),
                    ];
                    PaymentInvestors::create($single);
                }
            }
        }

        return 0;
    }
}
