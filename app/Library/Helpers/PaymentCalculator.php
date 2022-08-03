<?php

namespace App\Library\Helpers;

use App\Merchant;
use App\MerchantUser;
use App\ParticipentPayment;
use App\Settings;
use App\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentCalculator
{
    public function rtr($fund = 0, $factor = 0)
    {
        $factor = $factor ? $factor : 0;

        return round($fund * $factor, 2);
    }

    public function ctd($mgmPayments, $partPayment)
    {
        if ($mgmPayments instanceof Collection && $partPayment instanceof Collection) {
            return round($mgmPayments->sum('amount') + $partPayment->sum('amount'), 2);
        }

        return $mgmPayments + $partPayment;
    }

    public function balance($rtr, $ctd)
    {
        return round($rtr - $ctd, 2);
    }

    public function setDaysCalculation1($default_date)
    {
        $default_percentage_rule = (Settings::where('keys', 'default_percentage_rule')->value('values'));
        $default_percentage_rule = json_decode($default_percentage_rule, true);
        $day_arr = $default_percentage_rule;
        $days = 'IF(DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >= 31 && DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <= 60,'.$day_arr[30].',
									IF(DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=61 && DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=90 ,'.$day_arr[60].',
									IF(DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >= 91 && DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=120 ,'.$day_arr[90].',
									IF(DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=121 && DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=150,'.$day_arr[120].',
									IF(DATEDIFF('.$default_date.',DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) > 150,'.$day_arr[150].',1)) )) )';

        return $days;
    }

    public function setDaysCalculation($default_date)
    {
        $default_percentage_rule = (Settings::where('keys', 'default_percentage_rule')->value('values'));
        $default_percentage_rule = json_decode($default_percentage_rule, true);
        $day_arr = $default_percentage_rule;
        $days = 'IF(DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) > 0 && DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <= 60,'.$day_arr[30].', 
									IF(DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=61 && DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=90 ,'.$day_arr[60].',
									IF(DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >= 91 && DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=120 ,'.$day_arr[90].',
									IF(DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) >=121 && DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) <=150, '.$day_arr[120].',
									IF(DATEDIFF("'.$default_date.'",DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id=users.id) DAY)) > 150,'.$day_arr[150].',1)) )) )';

        return $days;
    }

    public function calculateWorkingDays($from, $to)
    {
        $holidays = array_keys(config('custom.holidays'));
        $interval = $to->diff($from);
        $days = $interval->days;
        $period = new DatePeriod($from, new DateInterval('P1D'), $to);
        foreach ($period as $dt) {
            $curr = $dt->format('D');
            if ($curr == 'Sat' || $curr == 'Sun') {
                $days--;
            } elseif (in_array($dt->format('Y-m-d'), $holidays)) {
                $days--;
            }
        }

        return $days;
        $nu_payment_days = $from->diffInDaysFiltered(function (Carbon $date) use ($holidays) {
            if ($holidays) {
                return $date->isWeekday() && in_array($date->toDateString(), $holidays);
            }
        }, $to);

        return $nu_payment_days;
    }

    public function getWorkingDays($from, $to)
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);
        $holidays = array_keys(config('custom.holidays'));
        $dates = [];
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            if ($date->isWeekday() && ! in_array($date->toDateString(), $holidays)) {
                $dates[] = $date->format('Y-m-d');
            }
        }

        return $dates;
    }

    public function calculateWorkingDaysCount($from, $to)
    {
        $from = Carbon::parse($from);
        $to = Carbon::parse($to);
        $holidays = array_keys(config('custom.holidays'));
        $count = 0;
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            if ($date->isWeekday() && ! in_array($date->toDateString(), $holidays)) {
                $count++;
            }
        }

        return $count;
    }

    public function calculateWeeks($from, $to)
    {
        $count = 0;
        $from = Carbon::parse($from);
        for ($from; $from->toDateString() <= $to; $from->addWeek()) {
            $count++;
            $end_date = $from->toDateString();
        }
        $end_date = $this->getWorkingDay($end_date);
        if ($end_date > $to && $end_date > 0) {
            $count--;
        }

        return $count;
    }

    public function calculateBiWeeks($from, $to)
    {
        $count = 0;
        $from = Carbon::parse($from);
        for ($from; $from->toDateString() <= $to; $from->addWeeks(2)) {
            $count++;
            $end_date = $from->toDateString();
        }
        $end_date = $this->getWorkingDay($end_date);
        if ($end_date > $to && $end_date > 0) {
            $count--;
        }

        return $count;
    }

    public function calculateMonths($from, $to)
    {
        $count = 0;
        $from = Carbon::parse($from);
        for ($from; $from->toDateString() <= $to; $from->addMonth()) {
            $count++;
            $end_date = $from->toDateString();
        }
        $end_date = $this->getWorkingDay($end_date);
        if ($end_date > $to && $end_date > 0) {
            $count--;
        }

        return $count;
    }

    public function checkWorkingDay($date)
    {
        $date = Carbon::parse($date);
        $holidays = array_keys(config('custom.holidays'));
        $status = false;
        if ($date->isWeekday() && ! in_array($date->toDateString(), $holidays)) {
            $status = true;
        }

        return $status;
    }

    public function getWorkingDay($date)
    {
        $date = Carbon::parse($date);
        $holidays = array_keys(config('custom.holidays'));
        $status = true;
        while ($status) {
            if ($date->isWeekday() && ! in_array($date->toDateString(), $holidays)) {
                $status = false;
                break;
            }
            $date = $date->addDay();
        }

        return $date->toDateString();
    }

    public function getPreviousWorkingDay($date)
    {
        $date = Carbon::parse($date);
        $holidays = array_keys(config('custom.holidays'));
        $status = true;
        while ($status) {
            if ($date->isWeekday() && ! in_array($date->toDateString(), $holidays)) {
                $status = false;
                break;
            }
            $date = $date->subDay();
        }

        return $date->toDateString();
    }

    public function getNthWorkingDay($date, $n)
    {
        $date = Carbon::parse($date);
        $holidays = array_keys(config('custom.holidays'));
        $status = true;
        $i = 0;
        $date = $date->addDay();
        while ($status) {
            if ($date->isWeekday() && ! in_array($date->toDateString(), $holidays)) {
                $i++;
                if ($i >= $n) {
                    $status = false;
                    continue;
                }
            }
            $date = $date->addDay();
        }

        return $date->toDateString();
    }

    public function participantRtr($participant_fund, $factor_rate, $mgmntfee)
    {
        $tmp = ($participant_fund) * $factor_rate;

        return round($tmp - ($mgmntfee / 100) * $tmp, 2);
    }

    public function paidParticipant($partPayment, $partShare = null)
    {
        if ($partPayment instanceof Collection) {
            return round($partPayment->sum('payment'), 2);
        }

        return $partPayment;
    }

    public function paidParticipantTotal($partPayment)
    {
        if ($partPayment instanceof Collection) {
            return round($partPayment->sum('payment'), 2);
        }

        return $partPayment;
    }

    public function paidParticipantShare($partPayment)
    {
        if ($partPayment instanceof Collection) {
            return round($partPayment->sum('participant_share'), 3);
        }

        return $partPayment;
    }

    public function mgmntFeeTotal($partPayment)
    {
        if ($partPayment instanceof Collection) {
            return round($partPayment->sum('mgmnt_fee'), 2);
        }

        return $partPayment;
    }

    public function syndicationFeeTotal($partPayment)
    {
        if ($partPayment instanceof Collection) {
            return round($partPayment->sum('syndication_fee'), 2);
        }

        return $partPayment;
    }

    public function participantShare($funded, $participantFunded)
    {
        if ($funded > $participantFunded) {
            return round(($participantFunded / $funded) * 100, 2);
        }

        return false;
    }

    public function getPayment($rtr, $pmnts)
    {
        if ((int) $pmnts == 0) {
            return $rtr;
        }
        $payment=$rtr/$pmnts;
        return ceil($payment*100)/100;
    }

    public function getParticipantShare($payment, $share)
    {
        return round(($share / 100) * $payment, 2);
    }

    public function getMgmntFee($payment, $mgmntPercent)
    {
        return round(($mgmntPercent / 100) * $payment, 2);
    }

    public function calculateMgmntFee($payment, $mgmntPercent)
    {
        return ($mgmntPercent / 100) * $payment;
    }

    public function getunderwrittingFee($payment, $underwrittingPercent)
    {
        return round(($underwrittingPercent / 100) * $payment, 2);
    }

    public function getSyndicationFee($payment, $syndicationPercent)
    {
        return round(($syndicationPercent / 100) * $payment, 2);
    }

    public function calculateSyndicationFee($payment, $syndicationPercent)
    {
        return ($syndicationPercent / 100) * $payment;
    }

    public function getParticipantPayment($totalPayment, $mgmntFee)
    {
        return round($totalPayment - $mgmntFee, 2);
    }

    public function getMerchantPayment($rtr, $pmnts, $mgmntFee, $participantShare)
    {
        $total = $this->getPayment($rtr, $pmnts);
        $partPayment = $this->getParticipantPayment($this->getParticipantShare($total, $participantShare), $this->getMgmntFee($total, $mgmntFee));

        return $total - $partPayment;
    }

    public function completedPercent2($merchant)
    {
        $totalPayment = array_sum(array_column(($merchant->participantPayment)->toArray(), 'participant_share'));
        $funded = array_sum(array_column(($merchant->marketplaceInvestors)->toArray(), 'amount'));
        $rtr1 = $funded * $merchant->factor_rate;
        $rtr = ($rtr1 - ($rtr1 * ($merchant->mgmnt_fee)) / 100);
        if ((int) $totalPayment == 0 || (int) $rtr == 0) {
            return 0;
        }
        $percent = (int) (round(((int) $totalPayment / $rtr) * 100, 2));
        if ($percent >= 100) {
            return $percent;
        } else {
            return round(((int) $totalPayment / (int) $rtr) * 100, 2);
        }
    }

    public function completePercentage($merchant_id, $investor_ids = null)
    {
        if (Auth::user()) {
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        } else {
            $permission = 1;
        }
        $payment = $rtr = 0;
        $payment = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id)
        ->join('payment_investors', function ($join) use ($permission, $investor_ids) {
            $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
            if (empty($permission) && $investor_ids) {
                $join->whereIn('payment_investors.user_id', '=', $investor_ids); // need to Discuss Here @fasil Why Whould we want investor Condition for merchant Complete Percentage Calculation
            }
        })->sum('participant_share');
        $MerchantUser = MerchantUser::where('merchant_id', $merchant_id);
        if (empty($permission) && $investor_ids) {
            $MerchantUser = $MerchantUser->whereIn('user_id', $investor_ids);
        }
        $rtr = $MerchantUser->sum('invest_rtr');
        $complete_per = ($rtr > 0) ? (($payment / $rtr) * 100) : 0;
        return round($complete_per, 2);
        if ($complete_per >= 100) {
            return $complete_per;
        } else {
            return round($complete_per, 2);
        }
    }

    public function completePercentageOld($merchant_id, $investor_ids = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $payment = $rtr = 0;
        $userId = Auth::user()->id;
        $dates = [];
        $payment = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id)->with(['paymentAllInvestors' => function ($q) use ($permission, $investor_ids) {
            if (empty($permission) && $investor_ids) {
                $q->whereIn('payment_investors.user_id', $investor_ids);
            }
        }])->sum('payment');
        $rtr = Merchant::where('id', $merchant_id)->value('rtr');
        $complete_per = ($rtr > 0) ? (($payment / $rtr) * 100) : 0;
        if ($complete_per >= 100) {
            return $complete_per;
        } else {
            return round($complete_per, 2);
        }
    }

    public function invCompletePercentage($merchant_id, $investor_id = null)
    {
        $merchant_user = MerchantUser::select('invest_rtr', 'paid_participant_ishare')->where('user_id', $investor_id)->where('merchant_id', $merchant_id)->first();
        if ($merchant_user) {
            return round($merchant_user->paid_participant_ishare / $merchant_user->invest_rtr * 100);
        } else {
            return 0;
        }
    }

    public function getParticipantShareValue($payment, $share, $merchant_amount)
    {
        if ($merchant_amount == 0) {
            return 0;
        }
        if (is_numeric($share) && is_numeric($merchant_amount) && is_numeric($payment)) {
            return $share / $merchant_amount * $payment;
        }
    }
}
