<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class CompanyHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'company.helper';
    }
}
