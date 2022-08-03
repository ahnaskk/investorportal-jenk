<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'payment.helper';
    }
}