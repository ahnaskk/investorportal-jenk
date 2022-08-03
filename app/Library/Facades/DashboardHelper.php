<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class DashboardHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dashboard.helper';
    }
}
