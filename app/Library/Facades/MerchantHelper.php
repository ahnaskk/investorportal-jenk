<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class MerchantHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'merchant.helper';
    }
}
