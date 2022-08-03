<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class Permissions extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'roles.permissions';
    }
}
