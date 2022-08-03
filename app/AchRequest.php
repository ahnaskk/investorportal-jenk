<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AchRequest extends Model
{
    protected $fillable = ['merchant_id', 'order_id', 'transaction_type', 'payment_date', 'payment_amount', 'ach_status', 'ach_request_status', 'payment_status', 'is_fees', 'payment_schedule_id', 'auth_code', 'reason', 'status_response', 'request_ip_address', 'merordernumber', 'response', 'creator_id', 'revert_id'];

    const AchRequestStatusAccepted = 1;
    const AchRequestStatusDeclined = -1;
    const AchStatusProcessing = 0;
    const AchStatusSettled = 1;
    const AchStatusReturned = -1;
    const PaymenStatusSettled = 1;
    const PaymentStatusProcessing = 0;
    const PaymentStatusReturned = -1;

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TermPaymentDate::class, 'payment_schedule_id');
    }

    public function velocityFees()
    {
        return $this->hasMany(VelocityFee::class, 'ach_request_id');
    }
    public static function paymentStatusOptions()
    {
        return $statuses = [
            self::PaymenStatusSettled    => 'Settled',
            self::PaymentStatusProcessing => 'Processing',
            self::PaymentStatusReturned   => 'Returned',
        ];
    }
    public static function achStatusOptions()
    {
        return $statuses = [
            self::AchStatusProcessing => 'Processing',
            self::AchStatusSettled => 'Settled',
            self::AchStatusReturned => 'Returned',
        ];
    }
    public static function achRequestStatusOptions()
    {
        return $statuses = [
            self::AchRequestStatusAccepted => 'Accepted',
            self::AchRequestStatusDeclined => 'Declined',
        ];
    }

}
