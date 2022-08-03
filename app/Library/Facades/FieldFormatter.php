<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class FieldFormatter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'field.formatter';
    }
}
