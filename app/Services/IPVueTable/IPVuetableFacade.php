<?php

namespace App\Services\IPVueTable;

use Illuminate\Support\Facades\Facade;

class IPVuetableFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ip-vuetable';
    }
}
