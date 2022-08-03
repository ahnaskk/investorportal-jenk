<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class MarketOfferHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'market.offer.helper';
    }
}
