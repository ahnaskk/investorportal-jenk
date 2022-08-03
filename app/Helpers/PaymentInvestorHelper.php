<?php

namespace App\Helpers;

use App\ParticipentPayment;
use App\PaymentInvestors;
use App\User;
use Illuminate\Support\Facades\DB;

class PaymentInvestorHelper
{
    public static function getPrincipalSum(User $user, $subStatusIds = [4, 22])
    {
        return PaymentInvestors::join('merchants', 'merchants.id', 'payment_investors.merchant_id')->where('payment_investors.user_id', $user->id)->whereNotIn('merchants.sub_status_id', [4, 22])->sum('principal');
    }

    public static function getProfitSum(User $user, $subStatusIds = [4, 22], int $merchantId = 0)
    {
        $query = PaymentInvestors::join('merchants', 'merchants.id', 'payment_investors.merchant_id')->where('payment_investors.user_id', $user->id)->whereNotIn('merchants.sub_status_id', $subStatusIds);
        if ($merchantId != 0) {
            $query = $query->where('payment_investors.merchant_id', $merchantId);
        }
        $query = $query->sum('profit');

        return $query;
    }

    public static function getTotalProfitSum(User $user, int $merchantId = 0)
    {
        $query = PaymentInvestors::join('merchants', 'merchants.id', 'payment_investors.merchant_id')->where('payment_investors.user_id', $user->id);
        if ($merchantId != 0) {
            $query = $query->where('payment_investors.merchant_id', $merchantId);
        }
        $query = $query->sum('profit');

        return $query;
    }

    public static function merchantsLatestPayments(int $userId = 0)
    {
        return ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->leftjoin('merchants', 'merchants.id', 'participent_payments.merchant_id')->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode')->where('payment_investors.user_id', $userId)->where('payment_investors.overpayment', 0)->select('payment_investors.id', 'payment_investors.participant_share', 'payment_date', 'payment_investors.merchant_id', DB::raw('upper(merchants.name) as name'), 'merchants.id', 'participent_payments.payment_type', 'actual_participant_share', 'rcode.code')->groupBy('payment_investors.participent_payment_id')->orderByRaw('participent_payments.payment_date DESC,participent_payments.id DESC')->limit(4)->get();
    }

    public static function getPrincipalSumByStatus(User $user, $subStatusIds = [4, 22])
    {
        $inArray = [];
        for ($i = 7; $i <= 25; $i++) {
            $inArray[$i] = $i;
        }
        $list = PaymentInvestors::join('merchants', 'merchants.id', 'payment_investors.merchant_id')->where('payment_investors.user_id', $user->id)->whereNotIn('merchants.sub_status_id', [4, 22])->whereNotIn('merchants.industry_id', $inArray)->groupBy('merchants.industry_id')->select(DB::raw('sum(principal) as principal'), 'merchants.industry_id')->pluck('principal', 'industry_id')->toArray();
        $other_list = PaymentInvestors::join('merchants', 'merchants.id', 'payment_investors.merchant_id')->where('payment_investors.user_id', $user->id)->whereNotIn('merchants.sub_status_id', [4, 22])->whereIn('merchants.industry_id', $inArray)->select(DB::raw('sum(principal) as principal'))->first();
        $data['list'] = $list;
        $data['other'] = $other_list;

        return $data;
    }
}
