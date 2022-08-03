<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Industries extends Model
{
    protected $table = 'industries';
    protected $guarded = [];

    public function merchants()
    {
        return $this->hasMany(Merchant::class, 'industry_id');
    }
}
