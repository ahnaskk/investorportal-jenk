<?php

namespace App\Console\Commands\SingleUse\ProductionAdjustment;

use App\Merchant;
use App\Models\Views\MerchantUserView;
use App\PaymentInvestors;
use DB;
use Illuminate\Console\Command;

class OverPrincipalAdjustment extends Command
{
    protected $signature = 'adjust:overpaidPrincipal {greater_than=1} {merchantId=""} {userID=""}';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $label = [1, 2];
        $greater_than = $this->argument('greater_than') ?? '';
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $userID = $this->argument('userID') ?? '';
        $userID = str_replace('"', '', $userID);
        $Merchant = new Merchant;
        if ($merchantId) {
            $Merchant = $Merchant->where('id', $merchantId);
        }
        if ($greater_than) {
            $Merchant = $Merchant->where('id', '>', $greater_than);
        }
        $Merchant = $Merchant->where('complete_percentage', '>=', '99.99');
        $Merchant = $Merchant->whereIn('label', $label);
        $Merchant = $Merchant->whereNotIn('sub_status_id', [4, 18, 19, 20, 22]);
        $Merchants = $Merchant->get();
        $MerchantCount = $Merchant->count();
        $data = [];
        foreach ($Merchants as $Mcount => $MerchantSingle) {
            if ($Mcount >= 100) {
                // break;
            }
            DB::beginTransaction();
            $Mcount++;
            $count = $MerchantCount - $Mcount;
            $merchant_id = $MerchantSingle->id;
            echo "\n $count ) $merchant_id -";
            $MerchantUserView = new MerchantUserView;
            $MerchantUserView = $MerchantUserView->where('merchant_id', $merchant_id);
            if ($userID) {
                $MerchantUserView = $MerchantUserView->where('investor_id', $userID);
            }
            $MerchantUserView = $MerchantUserView->get();
            foreach ($MerchantUserView as $Investor) {
                $total_investment = $Investor->total_investment;
                $InvestorPrincipal = new PaymentInvestors;
                $InvestorPrincipal = $InvestorPrincipal->where('merchant_id', $merchant_id);
                $InvestorPrincipal = $InvestorPrincipal->where('user_id', $Investor->investor_id);
                $InvestorPrincipal = $InvestorPrincipal->sum('principal');
                $diff = round($InvestorPrincipal - $total_investment, 2);
                $notInclude = [];
                if ($diff) {
                    echo "\n         ".$Investor->investor_id;
                    echo ':'.$diff;
                    if ($diff > 0) {
                        while ($diff != 0) {
                            $PaymentInvestors = new PaymentInvestors;
                            $PaymentInvestors = $PaymentInvestors->where('merchant_id', $merchant_id);
                            $PaymentInvestors = $PaymentInvestors->where('user_id', $Investor->investor_id);
                            $PaymentInvestors = $PaymentInvestors->where('participant_share', '!=', 0);
                            $PaymentInvestors = $PaymentInvestors->where('principal', '!=', 0);
                            $PaymentInvestors = $PaymentInvestors->whereNotIn('id', $notInclude);
                            $PaymentInvestors = $PaymentInvestors->orderByDesc('id')->first();
                            if ($PaymentInvestors) {
                                if ($PaymentInvestors->participant_share > 0) {
                                    if ($PaymentInvestors->principal >= $diff) {
                                        DB::table('payment_investors')
                                        ->where('id', $PaymentInvestors->id)
                                        ->update([
                                            'profit'    =>$PaymentInvestors->profit+$diff,
                                            'principal' =>$PaymentInvestors->principal-$diff,
                                        ]);
                                        $diff = 0;
                                    } else {
                                        $diff -= $PaymentInvestors->principal;
                                        DB::table('payment_investors')
                                        ->where('id', $PaymentInvestors->id)
                                        ->update([
                                            'profit'    =>$PaymentInvestors->profit+$PaymentInvestors->principal,
                                            'principal' =>0,
                                        ]);
                                    }
                                } else {
                                    if (abs($PaymentInvestors->principal) >= $diff) {
                                        DB::table('payment_investors')
                                        ->where('id', $PaymentInvestors->id)
                                        ->update([
                                            'principal' =>$PaymentInvestors->principal-$diff,
                                            'profit'    =>$PaymentInvestors->profit + $diff
                                        ]);
                                        $diff = 0;
                                    } else {
                                        $notInclude[] = $PaymentInvestors->id;
                                        // $PaymentInvestors->profit  +=$PaymentInvestors->principal;
                                        // $diff+=$PaymentInvestors->principal;
                                        // $PaymentInvestors->principal=0;
                                        // $PaymentInvestors->save();
                                    }
                                }
                            } else {
                                $diff = 0;
                            }
                        }
                    } else {
                        while ($diff != 0) {
                            $PaymentInvestors = new PaymentInvestors;
                            $PaymentInvestors = $PaymentInvestors->where('merchant_id', $merchant_id);
                            $PaymentInvestors = $PaymentInvestors->where('user_id', $Investor->investor_id);
                            $PaymentInvestors = $PaymentInvestors->where('participant_share', '!=', 0);
                            $PaymentInvestors = $PaymentInvestors->where('profit', '!=', 0);
                            $PaymentInvestors = $PaymentInvestors->whereNotIn('id', $notInclude);
                            $PaymentInvestors = $PaymentInvestors->orderByDesc('id')->first();
                            if ($PaymentInvestors) {
                                if ($PaymentInvestors->participant_share > 0) {
                                    if ($PaymentInvestors->profit >= $diff) {
                                        DB::table('payment_investors')
                                        ->where('id', $PaymentInvestors->id)
                                        ->update([
                                            'profit'    =>$PaymentInvestors->profit+$diff,
                                            'principal' =>$PaymentInvestors->principal-$diff,
                                        ]);
                                        $diff = 0;
                                    } else {
                                        $diff += $PaymentInvestors->profit;
                                        DB::table('payment_investors')
                                        ->where('id', $PaymentInvestors->id)
                                        ->update([
                                            'principal' =>$PaymentInvestors->principal-$PaymentInvestors->profit,
                                            'profit'    =>0,
                                        ]);
                                    }
                                } else {
                                    if ($diff >= $PaymentInvestors->profit) {
                                        DB::table('payment_investors')
                                        ->where('id', $PaymentInvestors->id)
                                        ->update([
                                            'profit'    =>$PaymentInvestors->profit+$diff,
                                            'principal' =>$PaymentInvestors->principal-$diff,
                                        ]);
                                        $diff = 0;
                                    } else {
                                        $notInclude[] = $PaymentInvestors->id;
                                        // $PaymentInvestors->principal  -=$PaymentInvestors->profit;
                                        // $diff+=$PaymentInvestors->profit;
                                        // $PaymentInvestors->profit=0;
                                        // $PaymentInvestors->save();
                                    }
                                }
                            } else {
                                $diff = 0;
                            }
                        }
                    }
                }
                if (! empty($notInclude)) {
                    echo ' --> Negative Payment Found';
                }
                $InvestorPrincipal = new PaymentInvestors;
                $InvestorPrincipal = $InvestorPrincipal->where('merchant_id', $merchant_id);
                $InvestorPrincipal = $InvestorPrincipal->where('user_id', $Investor->investor_id);
                $InvestorPrincipal = $InvestorPrincipal->sum('principal');
                $FinalDiff = round($InvestorPrincipal - $total_investment, 2);
                if ($FinalDiff != 0) {
                    $InvestorShare = new PaymentInvestors;
                    $InvestorShare = $InvestorShare->where('merchant_id', $merchant_id);
                    $InvestorShare = $InvestorShare->where('user_id', $Investor->investor_id);
                    $InvestorShare = $InvestorShare->sum('participant_share');
                    if ($InvestorShare != 0) {
                        if ($Investor->actual_completed_percentage >= 100) {
                            echo '/////Need To Check In Manually/////';
                            dd('block');
                        }
                    }
                }
            }
            DB::commit();
        }

        return 0;
    }
}
