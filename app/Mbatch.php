<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mbatch extends Model
{
    protected $guarded = ['id'];

    public function merchants()
    {
        return $this->belongsToMany(Merchant::class);
    }
}
