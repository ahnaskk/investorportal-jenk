<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class InvestorTransaction extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'investor.transaction';
    }
}
