<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentTermHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'payment.term.helper';
    }
}