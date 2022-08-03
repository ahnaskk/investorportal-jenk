<?php

namespace App\Models\Views;

use FFM;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MerchantUserView extends Model
{
    public function getAmountAttribute($value)
    {
        return $value;

        return '$'.number_format($value, 4);
    }

    public function getCommissionAmountAttribute($value)
    {
        return '$'.number_format($value, 4);
    }

    public function getUpSellCommissionAttribute($value)
    {
        return '$'.number_format($value, 4);
    }

    public function getUnderWritingFeeAttribute($value)
    {
        return '$'.number_format($value, 4);
    }

    public function getPrePaidAttribute($value)
    {
        return '$'.number_format($value, 4);
    }

    public function getTotalInvestmentAttribute($value)
    {
        return $value;

        return '$'.number_format($value, 4);
    }

    public function getExpectedMgmntFeeAmountAttribute($value)
    {
        return $value;
        return '$'.number_format($value, 4);
    }

    public function getPaidMgmntFeeAttribute($value)
    {
        return $value;
        return '$'.number_format($value, 4);
    }

    public function getPaidParticipantIshareAttribute($value)
    {
        return $value;

        return '$'.number_format($value, 4);
    }

    public function getMgmntFeeDiffAttribute($value)
    {
        return $value;
        return '$'.number_format($value, 4);
    }

    public function getFundedAttribute($value)
    {
        return '$'.number_format($value, 4);
    }

    public function getInvestRtrAttribute($value)
    {
        return $value;

        return '$'.number_format($value, 4);
    }

    public function getUserBalanceAmountAttribute($value)
    {
        return $value;
        return '$'.number_format($value, 4);
    }

    public function getDateFundedAttribute($value)
    {
        return FFM::date($value);
    }

    public function getCreatedAtAttribute($value)
    {
        return FFM::datetime($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return FFM::datetime($value);
    }

    public function CompanyModal()
    {
        return $this->belongsTo(\App\User::class, 'company');
    }

    public function MerchantModal()
    {
        return $this->belongsTo(\App\Merchant::class, 'merchant_id');
    }

    public function getStatusNameAttribute()
    {
        $Self = new \App\MerchantUser;
        $options = $Self->statusOptions();

        return $options[$this->status];
    }

    public function getCTD($from, $to)
    {
        $return = DB::table('payment_investors');
        $return->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id');
        $return->where('payment_investors.user_id', $this->investor_id);
        if ($to) {
            $return->where('participent_payments.payment_date', '<=', $to);
        }
        if ($from) {
            $return->where('participent_payments.payment_date', '>=', $from);
        }
        $return = $return->sum(DB::raw('payment_investors.participant_share-payment_investors.mgmnt_fee'));

        return $return;
    }

    public function getCTDProcedure($from, $to)
    {
        return DB::select('CALL user_ctd_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }

    public function getCredit($from, $to)
    {
        $return = DB::table('investor_transactions');
        $return->where('investor_transactions.investor_id', $this->investor_id);
        $return->where('investor_transactions.status', 1);
        if ($to) {
            $return->where('investor_transactions.date', '<=', $to);
        }
        if ($from) {
            $return->where('investor_transactions.date', '>=', $from);
        }
        $return = $return->sum('investor_transactions.amount');

        return $return;
    }

    public function getCreditProcedure($from, $to)
    {
        return DB::select('CALL user_credit_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }

    public function getCommission($from, $to)
    {
        $return = DB::table('merchant_user');
        $return->where('merchant_user.user_id', $this->investor_id);
        $return->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
        $return->where('merchants.active_status', '=', 1); // whre no default merchants.
        if ($from) {
            $return->where('merchants.date_funded', '>=', $from);
        }
        if ($to) {
            $return->where('merchants.date_funded', '<=', $to);
        }
        $return = $return->sum('merchant_user.commission_amount');

        return $return;
    }

    public function getCommissionProcedure($from, $to)
    {
        return DB::select('CALL user_commission_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }

    public function getFunded($from, $to)
    {
        $return = DB::table('merchant_user_views');
        $return->where('investor_id', $this->investor_id);
        $return->where('active_status', '=', 1); // whre no default
        if ($from) {
            $return->where('date_funded', '>=', $from);
        }
        if ($to) {
            $return->where('date_funded', '<=', $to);
        }
        $return = $return->sum('amount');

        return $return;
    }

    public function getFundedProcedure($from, $to)
    {
        return DB::select('CALL user_funded_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }

    public function getRTR($from, $to)
    {
        $return = DB::table('merchant_user_views');
        $return->where('investor_id', $this->investor_id);
        $return->where('active_status', '=', 1); // whre no default
        if ($from) {
            $return->where('date_funded', '>=', $from);
        }
        if ($to) {
            $return->where('date_funded', '<=', $to);
        }
        $return = $return->sum('invest_rtr');

        return $return;
    }

    public function getRTRProcedure($from, $to)
    {
        return DB::select('CALL user_rtr_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }

    public function getPrePaid($from, $to)
    {
        $return = DB::table('merchant_user_views');
        $return->where('investor_id', $this->investor_id);
        $return->where('active_status', '=', 1); // whre no default
        if ($from) {
            $return->where('date_funded', '>=', $from);
        }
        if ($to) {
            $return->where('date_funded', '<=', $to);
        }
        $return = $return->sum('pre_paid');

        return $return;
    }

    public function getPrePaidProcedure($from, $to)
    {
        return DB::select('CALL user_pre_paid_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }

    public function getUnderWritingFee($from, $to)
    {
        $return = DB::table('merchant_user_views');
        $return->where('investor_id', $this->investor_id);
        $return->where('active_status', '=', 1); // whre no default
        if ($from) {
            $return->where('date_funded', '>=', $from);
        }
        if ($to) {
            $return->where('date_funded', '<=', $to);
        }
        $return = $return->sum('under_writing_fee');

        return $return;
    }

    public function getUnderWritingFeeProcedure($from, $to)
    {
        return DB::select('CALL user_under_write_fee_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }

    public function getMgmntFee($from, $to)
    {
        $return = DB::table('merchant_user_views');
        $return->where('investor_id', $this->investor_id);
        $return->where('active_status', '=', 1); // whre no default
        if ($from) {
            $return->where('date_funded', '>=', $from);
        }
        if ($to) {
            $return->where('date_funded', '<=', $to);
        }
        $return = $return->sum(DB::raw('sum((merchant_user.invest_rtr*((merchant_user.mgmnt_fee)/100))) as total_fee'));

        return $return;
    }

    public function getMgmntFeeProcedure($from, $to)
    {
        return DB::select('CALL user_mgmnt_fee_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }

    public function getDefaultValueProcedure($from, $to)
    {
        return DB::select('CALL user_default_value_procedure(?,?)', [$this->investor_id, $to])[0];
    }

    public function getCarryForwardProcedure($from, $to)
    {
        return DB::select('CALL user_carry_forwards_procedure(?,?)', [$this->investor_id, $to])[0]->value;
    }
}
