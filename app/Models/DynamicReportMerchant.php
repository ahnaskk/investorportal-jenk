<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class DynamicReportMerchant extends Model
{
	protected $guarded = [];
    protected $connection = 'mongodb';

	public function getRtrAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['rtr']) : $this->attributes['rtr'];
	}

	public function getLastRcodeAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['last_rcode']) : $this->attributes['last_rcode'];
	}

	public function getDebitedAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['debited']) : $this->attributes['debited'];
	}

	public function getProfitAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['profit']) : $this->attributes['profit'];
	}

	public function getActualParticipantShareAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['actual_participant_share']) : $this->attributes['actual_participant_share'];
	}

	public function getPrincipalAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['principal']) : $this->attributes['principal'];
	}

	public function getMgmntFeeAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['mgmnt_fee']) : $this->attributes['mgmnt_fee'];
	}

	public function getOverpaymentAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['overpayment']) : $this->attributes['overpayment'];
	}

	public function getCarryProfitAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['carry_profit']) : $this->attributes['carry_profit'];
	}

	public function getParticipantShareAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['participant_share']) : $this->attributes['participant_share'];
	}

	public function getPaidParticipantIshareAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['paid_participant_ishare']) : $this->attributes['paid_participant_ishare'];
	}

	public function getMgmntFeeAmountAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['mgmnt_fee_amount']) : $this->attributes['mgmnt_fee_amount'];
	}

	public function getInvestRtrAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['invest_rtr']) : $this->attributes['invest_rtr'];
	}

	public function getInvestedAmountAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['invested_amount']) : $this->attributes['invested_amount'];
	}

	public function getCommissionAmountAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['commission_amount']) : $this->attributes['commission_amount'];
	}

	public function getNetBalanceAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['net_balance']) : $this->attributes['net_balance'];
	}

	public function getLastPaymentAmountAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['last_payment_amount']) : $this->attributes['last_payment_amount'];
	}

	public function getSettledRtrAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['settled_rtr']) : $this->attributes['settled_rtr'];
	}
	public function getAmountAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['amount']) : $this->attributes['amount'];
	}
	public function getPaymentDateAttribute()
	{
		return \FFM::date($this->attributes['payment_date']);
	}

	public function getSyndicateAttribute()
	{
		return session('accessor') ? \FFM::dollar($this->attributes['syndicate']) : $this->attributes['syndicate'];
	}
}
