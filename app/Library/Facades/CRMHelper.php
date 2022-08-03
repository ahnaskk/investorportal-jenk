<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class CRMHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'crm.helper';
    }
}