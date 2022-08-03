<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class FaqMerchantHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'faqMerchant.helper';
    }
}