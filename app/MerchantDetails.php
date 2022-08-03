<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class MerchantDetails extends Model
{
    protected $guarded = ['id'];
    protected $table = 'merchants_details';

    // public function getMerchantIdAttribute()
    // {
    //     return (request()->segment(2) == 'report-data') ? get_user_name_with_session($this->attributes['merchant_id']) : $this->attributes['id'];
    // }

    public function user_meta()
    {
        return $this->hasMany(UserMeta::class, 'user_id', 'merchant_id');
    }
}
