<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class FundingHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'funding.helper';
    }
}
