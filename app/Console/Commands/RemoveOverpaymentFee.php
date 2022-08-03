<?php

namespace App\Console\Commands;

use App\MerchantUser;
use App\ParticipentPayment;
use App\PaymentInvestors;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InvestorHelper;
class RemoveOverpaymentFee extends Command
{
    //Sprint 5;
    protected $signature = 'remove:overpaymentfees';
    protected $description = 'To Remove Overpayment Fee add Consider that also  as overpayment';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $payment_investors = DB::table('payment_investors')->where('user_id', 504)->where('mgmnt_fee', '!=', 0)->get(['id', 'merchant_id']);
        foreach ($payment_investors as $key => $value) {
            $PaymentInvestor = PaymentInvestors::find($value->id);
            $MerchantUser = MerchantUser::where('user_id', 504)->where('merchant_id', $value->merchant_id)->first();
            if ($MerchantUser) {
                $ParticipentPayment = ParticipentPayment::find($PaymentInvestor->participent_payment_id);
                $PaymentInvestor->profit = $PaymentInvestor->participant_share;
                $PaymentInvestor->investment_id = $MerchantUser->id;
                $PaymentInvestor->overpayment = $PaymentInvestor->participant_share;
                $PaymentInvestor->actual_overpayment = $PaymentInvestor->participant_share;
                $ParticipentPayment->final_participant_share = $PaymentInvestor->participant_share;
                $ParticipentPayment->save();
                $PaymentInvestor->mgmnt_fee = 0;
                $PaymentInvestor->save();
            } else {
                echo "\n Merchant User Not Found";
            }
            echo "\n";
            print_r($value->merchant_id);
        }
        $MerchantUsers = MerchantUser::where('user_id', 504)->where('mgmnt_fee', '!=', 0)->get(['id', 'merchant_id']);
        foreach ($MerchantUsers as $key => $value) {
            echo "\n";
            print_r($value->merchant_id);
            $MerchantUser = MerchantUser::find($value->id);
            $MerchantUser->paid_mgmnt_fee = 0;
            $MerchantUser->mgmnt_fee = 0;
            $MerchantUser->syndication_fee_percentage = 0;
            $MerchantUser->save();
        }
        $description = 'Remove Management Fee From Overpayment';
        echo "Liquidity Updating \n";
        InvestorHelper::update_liquidity([504], $description);
        echo "Liquidity Updated \n";
        echo 'End';
    }
}
