<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class MerchantStatementHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'merchant.statement.helper';
    }
}