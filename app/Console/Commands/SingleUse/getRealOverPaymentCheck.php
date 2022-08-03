<?php

namespace App\Console\Commands\SingleUse;

use App\Merchant;
use App\Models\Views\MerchantUserView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\User;
use Auth;
use DB;
use GPH;
use Illuminate\Console\Command;
use Schema;

class getRealOverPaymentCheck extends Command
{
    protected $signature = 'get:nonoverpaymentmerchant';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
        if (! Schema::hasTable('last_payment_correction_data')) {
            Schema::create('last_payment_correction_data', function ($table) {
                $table->id();
                $table->integer('user_id');
                $table->integer('merchant_id');
                $table->decimal('old', 16, 2);
                $table->decimal('new', 16, 2);
            });
        }
    }

    public function handle()
    {
        Auth::login(User::first());
        $MerchantsQ = new MerchantUserView;
        $MerchantsQ = $MerchantsQ->where('merchant_id', '8213');
        // $MerchantsQ = $MerchantsQ->where('label', [1, 2]);
        // $MerchantsQ = $MerchantsQ->whereNotIn('sub_status_id', [4,22,18,19,20]);
        // $MerchantsQ = $MerchantsQ->where('merchant_completed_percentate','>=', 99.9);
        // $MerchantsQ = $MerchantsQ->where('user_balance_amount','>', 0);
        // $MerchantsQ = $MerchantsQ->where('user_balance_amount','<=', 1);
        // $MerchantsQ = $MerchantsQ->where('investor_id','!=', 504);
        $Merchants = $MerchantsQ->pluck('merchant_id', 'merchant_id');
        $MerchantCount = $Merchants->count();
        $data = [];
        $i = 0;
        foreach ($Merchants as $merchant_id) {
            DB::beginTransaction();
            $i++;
            try {
                $count = $MerchantCount - $i;
                $ParticipentPayment = ParticipentPayment::where('merchant_id', $merchant_id)->latest()->first();
                start_over:
                if ($ParticipentPayment) {
                    $payment_date = $ParticipentPayment->created_at;
                    if (strtotime($payment_date) > strtotime(date('2021-02-01'))) {
                        echo "\n".$count.') '.$merchant_id.' => ';
                        echo $payment_date;
                        echo " Yes \n";
                        $PaymentInvestorsOld = PaymentInvestors::where('participent_payment_id', $ParticipentPayment->id)->get();
                        if (count($PaymentInvestorsOld->toArray()) == 1) {
                            if ($PaymentInvestorsOld['0']['user_id'] == 504) {
                                $ParticipentPayment = ParticipentPayment::where('merchant_id', $merchant_id)->where('id', '!=', $ParticipentPayment->id)->latest()->first();
                                goto start_over;
                            }
                        }
                        foreach ($PaymentInvestorsOld as $key => $value) {
                            $single['user_id'] = $value['user_id'];
                            $single['merchant_id'] = $value['merchant_id'];
                            $single['old'] = $value['participant_share'];
                            $data[$value->merchant_id][$single['user_id']] = $single;
                        }
                        PaymentInvestors::where('participent_payment_id', $ParticipentPayment->id)->delete();
                        GPH::PaymentToMarchantUserSync($ParticipentPayment->merchant_id);
                        $return_function = GPH::ApprovePaymentFunction($ParticipentPayment->id);
                        if ($return_function['result'] != 'success') {
                            throw new \Exception($return_function['result'], 1);
                        }
                        $PaymentInvestorsNew = PaymentInvestors::where('participent_payment_id', $ParticipentPayment->id)->get();
                        foreach ($PaymentInvestorsNew as $key => $value) {
                            $data[$value->merchant_id][$value['user_id']]['merchant_id'] = $value->merchant_id;
                            $data[$value->merchant_id][$value['user_id']]['old'] = $data[$value->merchant_id][$value['user_id']]['old'] ?? 0;
                            $data[$value->merchant_id][$value['user_id']]['user_id'] = $value->user_id;
                            $data[$value->merchant_id][$value['user_id']]['new'] = $value->participant_share;
                        }
                    }
                }
                foreach ($data              as $merchant_id=>$SinglePayment) {
                    foreach ($SinglePayment as $user_id=>$single) {
                        $single['user_id'] = $single['user_id'];
                        $single['merchant_id'] = $single['merchant_id'];
                        $single['old'] = $single['old'];
                        $single['new'] = $single['new'];
                        if ($single['new'] != $single['old']) {
                            DB::table('last_payment_correction_data')->insert($single);
                        }
                    }
                }
                $return['result'] = 'success';
                // DB::commit();
            } catch (\Exception $e) {
                $return['result'] = $e->getMessage();
                dd($e->getMessage());
                DB::rollback();
            }
        }
    }
}
