<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use HasFactory;
    const Pending = 1;
    const Completed = 2;
    const Deleted = 3;
    protected $fillable = [
        'date',
        'merchant_id',
        'amount',
        'model',
        'model_id',
        'status',
        'created_by',
        'updated_by',
    ];

    public static function rules($id = 0, $merge = [])
    {
        return array_merge([
            'date'         => 'required',
            'merchant_id'  => 'required',
            'amount'       => 'required',
            'model'        => 'required',
        ],
        $merge);
    }

    public static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            $model->created_by = Auth::user()->id ?? 1;
            $model->updated_by = Auth::user()->id ?? 1;
        });
        static::saved(function ($model) {
            $model->updated_by = Auth::user()->id ?? 1;
        });
    }

    public function Merchant()
    {
        return $this->belongsTo(\App\Merchant::class);
    }

    public function selfCreate($data)
    {
        try {
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $validator = \Validator::make($data, self::rules());
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
            $Self->update($data);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function selfDeleteByModelId($id)
    {
        try {
            $Self = self::where('model_id', $id)->delete();
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function selfUpdateByModelId($data, $id)
    {
        try {
            $Self = self::where('model_id', $id)->first();
            if ($Self) {
                if (isset($data['status'])) {
                    $Self->status = $data['status'];
                    $Self->save();
                }
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function selfDelete($id)
    {
        try {
            $Self = self::find($id);
            if ($Self) {
                $Self->delete();
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public static function statusOptions()
    {
        return [
            self::Pending  =>'Pending',
            self::Completed=>'Completed',
            self::Deleted  =>'Deleted',
        ];
    }

    public function getStatusNameAttribute()
    {
        $options = self::statusOptions();

        return $options[$this->status];
    }
}
