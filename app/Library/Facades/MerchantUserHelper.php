<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class MerchantUserHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'merchant.user.helper';
    }
}