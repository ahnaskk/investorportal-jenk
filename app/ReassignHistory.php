<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReassignHistory extends Model
{
    const Type1 = 1;
    const Type2 = 2;
    protected $fillable = [
        'id',
        'investor1',
        'investor2',
        'amount',
        'created_at',
        'updated_at',
        'payment',
        'investor1_new_liquidity',
        'investor2_new_liquidity',
        'investor1_old_liquidity',
        'investor2_old_liquidity',
        'investor1_total_liquidity',
        'investor2_total_liquidity',
        'merchant_id',
        'liquidity',
        'liquidity_change',
        'type',
        'creator_id',
    ];
    protected $table = 'reassign_history';
    public $timestamps = true;

    public function investmentData1()
    {
        return $this->belongsTo(User::class, 'investor1')->select(array('id','name'));
    }

    public function investmentData2()
    {
        return $this->belongsTo(User::class, 'investor2')->select(array('id','name'));
    }

    public function merchantData()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id')->select(array('id','name'));
    }

    public function merchantPayment()
    {
        return $this->hasMany(ParticipentPayment::class, 'merchant_id', 'investor1');
    }

    public function MerchantUserInvestorFrom()
    {
        return MerchantUser::where(function($q) {
            $q->where('user_id',$this->investor1)->where('merchant_id',$this->merchant_id);
        });
    }
    public function getMerchantUserInvestorFromAttribute()
    {
        return $this->MerchantUserInvestorFrom()->first();
    }

    public static function typeOptions()
    {
        return [
            self::Type1 => 'Without Payment Adjustment.',
            self::Type2 => 'With Payment Adjustment.',
        ];
    }

    public function getTypeNameAttribute()
    {
        $statuses = $this->typeOptions();

        return $this->type ? $statuses[$this->type] : '';
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
