<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarryForward extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['merchant_name'];

    public function investors()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function getMerchantNameAttribute()
    {
        return isset($this->merchant()->first()->name) ? $this->merchant()->first()->name : 'No name';
    }

    /*public function getMerchantNameAttribute()
    {
        return 12;
    }*/
}
