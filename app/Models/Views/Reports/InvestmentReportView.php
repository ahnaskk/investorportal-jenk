<?php

namespace App\Models\Views\Reports;

use App\Library\Helpers\FieldFormatter;
use FFM;
use Illuminate\Database\Eloquent\Model;

class InvestmentReportView extends Model
{
    protected $fillable = [
    'merchant_id',
    'Merchant',
    'industry_id',
    'Industry',
    'investor_id',
    'Investor',
    'funded',
    'date_funded',
    'commission',
    'underwriting_status',
    'sub_status_id',
    'SubState',
    'invested_amount',
    'ctd',
    'overpayment',
    'commission_amount',
    'pre_paid',
    'under_writing_fee',
    'mgmnt_fee',
    'i_amount',
    'i_rtr',
    'share_t',
    'created_at',
  ];

    public function getDateFundedAttribute($value)
    {
        return FFM::date($value);
    }

    public function getAmountAttribute($value)
    {
        return FFM::dollar($value);
    }

    // public function getFundedAttribute($value) {
    //   return FFM::dollar($value);
    // }
    public function getCommissionAttribute($value)
    {
        return FFM::percent($value);
    }

    public function getInvestedAmountAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getCtdAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getOverpaymentAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getCommissionAmountAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getPrePaidAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getUnderWritingFeeAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getMgmntFeeAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getIAmountAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getIRtrAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getShareTAttribute($value)
    {
        return $value.'%';
    }

    public function getCreatedAtAttribute($value)
    {
        //return date('m-d-Y H:i:s', strtotime($value));
        return (new FieldFormatter)->datetime($value);
    }
    public function getUpSellCommissionAttribute($value)
    {
        return FFM::dollar($value);
    }
    public function getUpSellCommissionPerAttribute($value)
    {
        return FFM::percent($value);
    }
    public function getTUpSellCommissionAttribute($value)
    {
        return FFM::dollar($value);
    }
}
