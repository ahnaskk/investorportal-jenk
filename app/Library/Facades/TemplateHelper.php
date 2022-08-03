<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class TemplateHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'template';
    }
}
