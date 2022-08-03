<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class FaqHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'faq.helper';
    }
}