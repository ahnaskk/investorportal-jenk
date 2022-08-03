<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class CollectionUserHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'collectionUser.helper';
    }
}
