<?php

namespace App\Console\Commands\SingleUse;

use App\Merchant;
use App\Models\Views\PaymentInvestorsView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Settings;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PayCalc;

class paymentAdjustment extends Command
{
    protected $signature = 'payment:adjustment';
    protected $description = 'To Adjust the current Payment By Investors';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Artisan::call('OverpaymentAccount:Add');
        // $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        // $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        // $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        DB::beginTransaction();
        $Merchants = new Merchant;
        // $Merchants=$Merchants->where('id',8808);
        $Merchants = $Merchants->get();
        $count = count($Merchants->toArray());
        foreach ($Merchants as $no => $Merchant) {
            echo $count.') Merchant ID '.$Merchant->id."\n";
            $count -= 1;
            $ParticipentPayment = new ParticipentPayment;
            $ParticipentPayment = $ParticipentPayment->where('merchant_id', $Merchant->id);
            // $ParticipentPayment=$ParticipentPayment->where('id',768542);
            $ParticipentPayment = $ParticipentPayment->get();
            $diff_1 = 0;
            foreach ($ParticipentPayment as $i => $singlePayment) {
                $diff = 0;
                $participent_payment_id = $singlePayment->id;
                $PaymentInvestors = PaymentInvestors::where('participent_payment_id', $participent_payment_id)->get();
                foreach ($PaymentInvestors as $j => $value) {
                    $diff1 = round($value->participant_share - round($value->participant_share, 2), 4);
                    $diff2 = round($value->actual_participant_share - round($value->actual_participant_share, 2), 4);
                    $diff3 = round($value->overpayment - round($value->overpayment, 2), 4);
                    $diff4 = round($value->actual_overpayment - round($value->actual_overpayment, 2), 4);
                    $diff5 = round($value->profit - round($value->profit, 2), 4);
                    $diff6 = round($value->principal - round($value->principal, 2), 4);
                    $diff7 = round($value->balance - round($value->balance, 2), 4);
                    $diff8 = round($value->mgmnt_fee - round($value->mgmnt_fee, 2), 4);
                    $diff_1 += $diff1 + $diff2 + $diff3 + $diff4 + $diff5 + $diff6 + $diff7 + $diff8;
                    if ($diff_1) {
                        $participant_share = round($value->participant_share, 2);
                        $overpayment = round($value->overpayment, 2);
                        $singleData = [
                            'participant_share'       =>round($value->participant_share, 2),
                            'actual_participant_share'=>round($value->actual_participant_share, 2),
                            'mgmnt_fee'               =>round($value->mgmnt_fee, 2),
                            'overpayment'             =>round($value->overpayment, 2),
                            'actual_overpayment'      =>round($value->actual_overpayment, 2),
                            'profit'                  =>round($value->profit, 2),
                            'principal'               =>round($value->principal, 2),
                            'balance'                 =>round($value->balance, 2),
                        ];
                        DB::table('payment_investors')->where('id', $value->id)->update($singleData);
                        // echo "Updated participant_share \n";
                    }
                }
                // $total_participant_share=DB::table('payment_investors')->where('participent_payment_id',$participent_payment_id)->sum('participant_share');
                // $payment=$singlePayment->payment/($Merchant->funded/$Merchant->max_participant_fund);
                // if($payment!=$total_participant_share){
                //     $overpyment=round($payment-$total_participant_share,2);
                //     if($overpyment){
                //         echo "Overpayment Amount Found \n";
                //         $investment_id=DB::table('merchant_user')->where('merchant_id',$Merchant->id)->where('user_id',$OverpaymentAccount->id)->value('id');
                //         if($investment_id){
                //             echo "Overpayment Account Found \n";
                //             $OverPaymentData=[
                //                 'user_id'                 =>$OverpaymentAccount->id,
                //                 'merchant_id'             =>$Merchant->id,
                //                 'participent_payment_id'  =>$participent_payment_id,
                //                 'investment_id'           =>$investment_id,
                //                 'participant_share'       =>$overpyment,
                //                 'actual_participant_share'=>$overpyment,
                //                 'mgmnt_fee'               =>0,
                //                 'overpayment'             =>$overpyment,
                //                 'actual_overpayment'      =>$overpyment,
                //                 'principal'               =>0,
                //                 'profit'                  =>$overpyment,
                //             ];
                //             DB::table('payment_investors')->insert($OverPaymentData);
                //         }
                //     }
                // }
                // if($diff_1)
                // {
                // echo "Profit And Principal Updation \n";
                // $payment_data1      = PaymentInvestorsView::select([
                //     'payment_investors_views.*',
                //     'payment',
                //     'user_id',
                //     'participant_share',
                //     'actual_participant_share',
                //     'net_amount',
                //     'payment_investors_views.mgmnt_fee as mgmnt_fee',
                //     'merchant_user_views.mgmnt_fee as mangt_fee_percentage',
                //     'merchant_user_views.invest_rtr',
                //     'overpayment',
                //     'balance',
                //     'total_investment as invested_amount',
                //     DB::raw('(net_amount)-((net_amount)*(total_investment)/(merchant_user_views.invest_rtr-(merchant_user_views.mgmnt_fee/100)*merchant_user_views.invest_rtr)) as profit_value1'),
                // ])
                // ->leftJoin('merchant_user_views', function($join) {
                //     $join->on('merchant_user_views.id','investment_id');
                // })
                // ->where('participent_payment_id', $participent_payment_id);
                // $payment_data       = $payment_data1->get();
                // foreach ($payment_data as $singlePI) {
                //     switch ($singlePI->user_id) {
                //         case $OverpaymentAccount ? $OverpaymentAccount->id : '':
                //         $profit    = $singlePI->overpayment;
                //         $principal = 0;
                //         break;
                //         default:
                //         $profit          = $singlePI->profit_value1;
                //         $principal       = $singlePI->net_amount - $profit;
                //         $total_principle = PaymentInvestors::where('user_id', $singlePI->user_id)->where('participent_payment_id','<',$participent_payment_id)->where('merchant_id', $singlePI->merchant_id)->sum('principal');
                //         $total_principle+= $principal;
                //         $total_principle = round($total_principle,2);
                //         $invested_amount = round($singlePI->invested_amount, 2);
                //         if ($total_principle > $invested_amount) {
                //             $balance_principle = round(($total_principle-$singlePI->invested_amount), 2);
                //             $profit            = round(($profit+$balance_principle), 2);
                //             $principal         = round(($principal-$balance_principle), 2);
                //         }
                //         if ($singlePI->balance < 0) {
                //             $principal = 0;
                //         }
                //         break;
                //     }
                //     $profit   =round($profit,2);
                //     $principal=round($principal,2);
                //     dd($profit,$principal,$singlePI->toArray(),$payment_data->toArray());
                //     DB::table('payment_investors')->where('id',$singlePI->id)->update([
                //         'profit'    => $profit,
                //         'principal' => $principal,
                //     ]);
                // }
                $final_part_share = DB::table('payment_investors')->where('participent_payment_id', $participent_payment_id)->sum(DB::raw('participant_share-mgmnt_fee'));
                ParticipentPayment::where('id', $participent_payment_id)->update(['final_participant_share' => round($final_part_share, 2)]);
                // echo "final_participant_share Updated \n";
                // }
            }
            if ($diff_1) {
                // echo "Merchant User Updating \n";
                $complete_per = PayCalc::completePercentage($Merchant->id);
                $merchant_status = DB::table('merchant_user')
                ->whereIn('merchant_user.status', [1, 3])
                ->select('merchant_user.user_id')
                ->groupBy('merchant_user.user_id')
                ->where('merchant_user.merchant_id', $Merchant->id)
                ->update([
                    'paid_participant_ishare'        => DB::raw('(select round(sum(payment_investors.participant_share),2)        as ps        from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                    'actual_paid_participant_ishare' => DB::raw('(select round(sum(payment_investors.actual_participant_share),2) as aps       from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                    'paid_mgmnt_fee'                 => DB::raw('(select round(sum(payment_investors.mgmnt_fee),2)                as mgmnt_fee from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'),
                    'complete_per'                   => $complete_per,
                ]);
                // echo "Merchant User Updated \n";
            }
        }
        DB::commit();
    }
}
