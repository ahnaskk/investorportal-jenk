<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VelocityFee extends Model
{
    protected $fillable = ['merchant_id', 'ach_request_id', 'order_id', 'fee_type', 'payment_date', 'payment_amount', 'status', 'creator_id'];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function merchantUsers()
    {
        return $this->hasMany(MerchantUser::class, 'merchant_id', 'merchant_id');
    }

    public function achRequest()
    {
        return $this->belongsTo(AchRequest::class, 'ach_request_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
