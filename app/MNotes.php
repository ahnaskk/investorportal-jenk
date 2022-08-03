<?php

namespace App;

use App\Library\Helpers\FieldFormatter;
use Illuminate\Database\Eloquent\Model;

class MNotes extends Model
{
    protected $guarded = ['id'];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id')->withTrashed();
    }
}
