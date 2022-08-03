<?php
namespace App\Library\Facades;
use Illuminate\Support\Facades\Facade;
class ReportHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'report.helper';
    }
}
