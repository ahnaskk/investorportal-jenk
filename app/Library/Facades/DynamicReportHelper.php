<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class DynamicReportHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dynamic.report.helper';
    }
}
