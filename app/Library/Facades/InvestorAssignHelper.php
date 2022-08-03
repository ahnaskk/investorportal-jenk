<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class InvestorAssignHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'investor.assign.helper';
    }
}