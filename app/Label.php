<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Label extends Model
{
    const MCA         = 1;
    const LutherSales = 2;
    const Insurance   = 3;
    const Insurance1  = 4;
    const Insurance2  = 5;
    protected $guarded = [];
    protected $table = 'label';
    public static function getLabels() {
        return Self::pluck('name','id')->toArray();
    }
    public function scopeInsurance($query) {
        return $query->where('flag', 1);
    }
}
