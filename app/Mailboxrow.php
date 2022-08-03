<?php

namespace App;

use App\Library\Helpers\FieldFormatter;
use Illuminate\Database\Eloquent\Model;

class Mailboxrow extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtAttribute($value)
    {
        if ($value != '') {
            return (new FieldFormatter)->datetime($value);
        }
    }
}
