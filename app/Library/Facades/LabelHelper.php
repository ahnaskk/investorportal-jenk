<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class LabelHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'label.helper';
    }
}
