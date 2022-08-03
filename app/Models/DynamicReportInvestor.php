<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class DynamicReportInvestor extends Model
{
    protected $guarded = [];

    protected $connection = 'mongodb';

    public function getLiquidityAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['liquidity']) : $this->attributes['liquidity'];
    }

    public function getCommissionAmountAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['commission_amount']) : $this->attributes['commission_amount'];
    }

    public function getTotalFundedAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['total_funded']) : $this->attributes['total_funded'];
    }

	public function getOriginationFeeAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['origination_fee']) : $this->attributes['origination_fee'];
	}

    public function getUnderWritingFeeAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['under_writing_fee']) : $this->attributes['under_writing_fee'];
    }

    public function getRtrAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['rtr']) : $this->attributes['rtr'];
    }

    public function getCtdAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['ctd']) : $this->attributes['ctd'];
    }

    public function getSyndicationFeeAttribute()
    {
	    return (session('accessor')) ? \FFM::dollar($this->attributes['syndication_fee']) : $this->attributes['syndication_fee'];
    }

    public function getUnderwritingFeeEarnedAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['underwriting_fee_earned']) : $this->attributes['underwriting_fee_earned'];
    }

    public function getProfitAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['profit']) : $this->attributes['profit'];
    }

    public function getPrincipalAttribute()
    {
        return session('accessor') ? \FFM::dollar($this->attributes['principal']) : $this->attributes['principal'];
    }

	public function getCreditAmountAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['credit_amount']) : $this->attributes['credit_amount'];
	}

	public function getManagementFeeEarnedAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['management_fee_earned']) : $this->attributes['management_fee_earned'];
	}

	public function getPaymentDateAttribute()
	{
		return \FFM::date($this->attributes['payment_date']);
	}
}
