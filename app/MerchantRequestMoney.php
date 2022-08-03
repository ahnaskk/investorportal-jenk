<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MerchantRequestMoney extends Model
{
    const PENDING = 0;
    const SUBMITTED = 1;  
    protected $fillable = ['merchant_id', 'amount', 'merchant_ip', 'source', 'status']; 
    
}
