<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    protected $table = 'merchants';

    protected $guarded = ['id'];

    public function marketplaceInvestors()
    {
        return $this->hasMany(MarketpalceInvestors::class, 'merchant_id');
    }

    public function investors_total()
    {
    }
}
