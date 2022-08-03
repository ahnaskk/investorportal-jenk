<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class GeneratePaymentHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'generate.payment.helper';
    }
}
