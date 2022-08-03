<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;

//use Spatie\Activitylog\Traits\LogsActivity;

class UserDetails extends Model implements AuditableContracts
{
    use Auditable;
    protected $guarded = [];
    protected $table = 'user_details';

    public function userDetails()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function investmentData()
    {
        return $this->hasMany(MerchantUser::class, 'user_id');
    }
}
