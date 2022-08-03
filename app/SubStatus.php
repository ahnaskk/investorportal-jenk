<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubStatus extends Model
{
    const ActiveAdvance =1;
    const Collections   =5;
    const AdvanceCompleted=11;
    const Default=4;
    const DefaultLegal= 22;
    const Settled=18;
    const EarlyPayDiscount=19;
    const DefaultPlus =20;
    const Cancelled=17;
    protected $guarded = [];

    public static function getSubStatusName($id)
    {
        return DB::table('sub_statuses')->where('id',$id)->value('name'); 

    }
}
