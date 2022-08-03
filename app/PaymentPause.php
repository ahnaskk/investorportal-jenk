<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentPause extends Model
{
    protected $fillable = ['merchant_id', 'paused_by', 'paused_at', 'resumed_by', 'resumed_at'];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
