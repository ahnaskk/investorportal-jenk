<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class NotesHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'notes.helper';
    }
}
