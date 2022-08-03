<?php

namespace App\Console\Commands\SingleUse;

use App\MerchantUser;
use App\PaymentInvestors;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddInvestmentId extends Command
{
    protected $signature = 'add:investment_id';
    protected $description = 'Add Missing  Investment Id in PaymentInvestors';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $PaymentInvestors = PaymentInvestors::whereNull('investment_id')->get();
        if (! empty($PaymentInvestors->toArray())) {
            foreach ($PaymentInvestors as $key => $value) {
                echo $value->merchant_id.' - '.$value->user_id."\n";
                $MerchantUser = MerchantUser::where('merchant_id', $value->merchant_id)->where('user_id', $value->user_id)->first();
                if ($MerchantUser) {
                    DB::table('payment_investors')->where('id', $value->id)->update([
                        'investment_id' =>$MerchantUser->id,
                    ]);
                }
            }
        } else {
            echo 'Empty';
        }
    }
}
