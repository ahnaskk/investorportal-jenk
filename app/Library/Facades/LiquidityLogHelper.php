<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class LiquidityLogHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'liquidity.log.helper';
    }
}
