<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentCalculator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'payment.calc';
    }
}
