<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class SettingHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'setting.helper';
    }
}
