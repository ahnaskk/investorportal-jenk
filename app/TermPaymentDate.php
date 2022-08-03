<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TermPaymentDate extends Model
{
    const ACHNotPaid = 0;
    const ACHPaid = 1;
    const ACHProcessing = 2;
    const ACHCancelled = -1;
    protected $fillable = ['merchant_id', 'term_id', 'payment_date', 'payment_amount', 'status', 'pause_id'];

    public function paymentTerm()
    {
        return $this->belongsTo(MerchantPaymentTerm::class, 'term_id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function payments()
    {
        return $this->hasMany(ParticipentPayment::class, 'payment_date', 'payment_date')->where('merchant_id', $this->merchant_id)->where('mode_of_payment', 1);
    }

    public function ach()
    {
        return $this->hasOne(AchRequest::class, 'payment_schedule_id');
    }

    public static function statusOptions()
    {
        return [
            self::ACHNotPaid        =>'Not Paid',
            self::ACHPaid           =>'Paid',
            self::ACHProcessing     =>'Processing',
            self::ACHCancelled      =>'Cancelled',
        ];
    }

    public function getStatusNameAttribute()
    {
        $statuses = $this->statusOptions();

        return $statuses[$this->status];
    }
}
