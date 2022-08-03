<?php

namespace App\Library\Facades;

use Illuminate\Support\Facades\Facade;

class ParticipantPaymentHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'participant.payment.helper';
    }
}