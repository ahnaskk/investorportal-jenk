<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class EventHistory extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'event.history';
    }
}
