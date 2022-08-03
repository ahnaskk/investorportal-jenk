<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class BankHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bank.helper';
    }
}
