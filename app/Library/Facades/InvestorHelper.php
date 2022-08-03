<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class InvestorHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'investor.helper';
    }
}
