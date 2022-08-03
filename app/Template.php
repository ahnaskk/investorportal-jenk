<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    const emailType = 'email';
    const mobileType = 'mobile';

    protected $guarded = [];
    protected $table = 'template';

    public static function getTypes()
    {
        return [
            self::emailType => 'Email',
            self::mobileType => 'Mobile app push notification',
        ];
    }
}
