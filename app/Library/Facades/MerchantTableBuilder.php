<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class MerchantTableBuilder extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'merchant.table.builder';
    }
}
