<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReserveLiquidity extends Model
{
    protected $table = 'reserve_liquidity';

    protected $fillable = ['user_id','from_date','to_date','reserve_percentage'];
}
