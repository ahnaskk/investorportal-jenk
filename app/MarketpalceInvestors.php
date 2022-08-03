<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketpalceInvestors extends Model
{
    protected $table = 'merchant_user';

    protected $guarded = ['id'];

    public function merchants($value = '')
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function investor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
