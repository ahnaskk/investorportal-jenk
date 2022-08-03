<?php

namespace App\Console\Commands\SingleUse\ProductionAdjustment;

use App\Merchant;
use App\ParticipentPayment;
use Illuminate\Console\Command;

class ChangeDefaultDateForSelectedMerchant extends Command
{
    protected $signature = 'change:defaultDateForSelectedMerchant';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $Merchant = new Merchant;
        $Merchant = $Merchant->whereIn('sub_status_id', ['4', '22', '18', '19', '20']);
        $Merchant = $Merchant->get();
        foreach ($Merchant as $key => $value) {
            echo $key.' ) '.$value->id."\n";
            $lag_time = $value->lendor->lag_time + 30;
            $ParticipentPayment = ParticipentPayment::where('merchant_id', $value->id)->where('payment', '>', 0)->orderBy('payment_date', 'DESC')->first(['payment_date', 'created_at']);
            if ($ParticipentPayment) {
                $last_date = date('Y-m-d', strtotime($value->last_payment_date));
                $last_created_date = $ParticipentPayment->created_at;
                $default_date = date('Y-m-d', strtotime('+ '.$lag_time.' days', strtotime($last_date)));
                $value->last_status_updated_date = date('Y-m-d 18:i:s', strtotime($default_date));
                if ($value->last_payment_date) {
                    $last_payment_date = date('Y-m-d', strtotime($value->last_payment_date));
                } else {
                    $last_payment_date = date('Y-m-d', strtotime($value->date_funded));
                }
                $DefaultParticipentPayment = ParticipentPayment::where('merchant_id', $value->id)->where('payment', 0)->orderBy('payment_date', 'DESC')->first();
                if ($DefaultParticipentPayment) {
                    $DefaultParticipentPayment->payment_date = date('Y-m-d', strtotime($last_date));
                    $DefaultParticipentPayment->created_at = $last_created_date;
                    $DefaultParticipentPayment->updated_at = $last_created_date;
                    $DefaultParticipentPayment->save();
                }
                $value->save();
            }
        }

        return 0;
    }
}
