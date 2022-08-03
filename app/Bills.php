<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bills extends Model
{
    protected $guarded = ['id'];

    public function investor()
    {
        return $this->belongsTo(User::class);
    }
}
