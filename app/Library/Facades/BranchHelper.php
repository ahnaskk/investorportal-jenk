<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class BranchHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'branch.helper';
    }
}
