<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class InvestorTransactionHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'investor.transaction.helper';
    }
}
