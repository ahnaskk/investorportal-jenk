<?php

namespace App\Console\Commands\Adjustment;

use App\MerchantUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PennyInvestment extends Command
{
    protected $signature = 'Remove:PennyInvestment';
    protected $description = 'Remove the Penny Investments from merchant_user Table';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $PennyInvestor = DB::table('penny_investment_check_view')->get();
        foreach ($PennyInvestor as $value) {
            $MerchantUser = MerchantUser::find($value->id);
            $payment_investors = DB::table('payment_investors')->where('merchant_id', $value->merchant_id)->where('user_id', $value->investor_id)->get();
            foreach ($payment_investors as $payments) {
                // $HighestInvestor=DB::table('payment_investors')->where('participent_payment_id',$payments->participent_payment_id)->orderBy('participant_share','DESC')->first();
                // $HighestInvestor->participant_share        +=$payments->participant_share;
                // $HighestInvestor->actual_participant_share +=$payments->actual_participant_share;
                // $HighestInvestor->balance                  -=$payments->participant_share;
                // $HighestInvestor->mgmnt_fee                +=$payments->mgmnt_fee;
                $payments->delete();
            }
            $MerchantUser->delete();
        }
    }
}
