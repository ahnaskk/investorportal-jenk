<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ManualLiquidityLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'user_id',
        'liquidity',
    ];
    protected $guarded = [];

    public static function rules($id = 0, $merge = [])
    {
        return array_merge([
            'date'       => 'required',
            'user_id'    => 'required',
            'liquidity'  => 'required',
        ],
        $merge);
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function selfCreate($data)
    {
        try {
            $data['creator_id'] = (Auth::check()) ? Auth::user()->id : null;
            $validator = \Validator::make($data, $this->rules());
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }
            $Self = self::create($data);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function selfUpdate($data, $id)
    {
        try {
            $validator = \Validator::make($data, $this->rules());
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }
            $Self = self::find($id);
            $Self->liquidity = $data['liquidity'];
            $Self->save();
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }
}
