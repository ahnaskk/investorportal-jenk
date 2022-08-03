<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Visitor extends Model
{
    protected $guarded = [];
    protected $table = 'visitors';
    public function Visitor() {
        return $this->belongsTo(\App\User::class, 'visitor_id');
    }
}
