<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantPaymentTerm extends Model
{
    protected $guarded = [];

    protected $fillable = ['merchant_id', 'payment_amount', 'advance_type', 'pmnts', 'actual_payment_left', 'status', 'created_at', 'start_at', 'end_at', 'created_by', 'updated_by'];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function payments()
    {
        return $this->hasMany(TermPaymentDate::class, 'term_id');
    }
}
