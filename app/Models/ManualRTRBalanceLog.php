<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualRTRBalanceLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'user_id',
        'rtr_balance',
        'rtr_balance_default',
        'total',
        'details',
    ];
    protected $guarded = [];

    public static function rules($id = 0, $merge = [])
    {
        return array_merge([
            'date'       => 'required',
            'user_id'    => 'required',
            'rtr_balance'=> 'required',
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
            $Self->rtr_balance = $data['rtr_balance'];
            $Self->rtr_balance_default = $data['rtr_balance_default'] ?? 0;
            $Self->details = $data['details'] ?? '';
            $Self->save();
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }
}
