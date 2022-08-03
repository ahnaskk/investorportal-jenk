<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantStatusLog extends Model
{
    protected $guarded = [];

    protected $table = 'merchant_status_log';

    protected static $logFillable = true;

    protected static $logAttributes = ['*'];

    protected static $logName = 'Liquidity Log';
}
