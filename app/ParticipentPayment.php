<?php

namespace App;

use App\Models\Transaction;
//fzl laravel8 use Database\Seeders\Role;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class ParticipentPayment extends Model
{
    const StatusPending = 2;
    const StatusCompleted = 1;
    const ModeDirect = 0;
    const ModeAchPayment = 1;
    const ModeCreditCard = 2;
    const PaymentModeManual = 0;
    const PaymentModeACH = 1;
    const PaymentModeCreditCard = 2;
    const PaymentModeSystemGenerated = 3;
    const PaymentTypeCredit = 1;
    const PaymentTypeDebit = 0;
    protected $guarded = [];
    //protected $table = 'participent_payments';
    protected $fillable = [
        'total_payment',
        'transaction_type',
        'payment_date',
        'amount',
        'mgmnt_fee',
        'payment',
        'participant_share',
        'final_participant_share',
        'merchant_id',
        'rcode',
        'creator_id',
        'created_at',
        'updated_at',
        'reason',
        'id',
        'status',
        'payment_type',
        'mode_of_payment',
        'is_profit_adjustment_added',
        'is_payment',
        'investor_ids',
        'model',
        'model_id',
        'revert_id',
        'agent_fee_percentage',
    ];

    public static function transactionRules($id = 0, $merge = [])
    {
        return array_merge([
            'payment_date' => 'required',
            'payment'      => 'required',
            'model'        => 'required',
        ],
        $merge);
    }

    public static function paymentRules($id = 0, $merge = [])
    {
        return array_merge([
            'payment_date' => 'required',
            'merchant_id'  => 'required',
            'payment'      => 'required',
        ],
        $merge);
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            $model->PaymentInvestors()->delete();
        });
        static::creating(function ($model) {
            if ($model->model == \App\ParticipentPayment::class) {
                $model->is_payment = 1;
            } else {
                $model->is_payment = 0;
            }
        });
        static::updated(function ($model) {
            if ($model->model != \App\MerchantUser::class) {
                if ($model->model_id) {
                    $Data['status'] = $model->status;
                    $ParticipentPaymentModel = new $model->model;
                    $ParticipentPaymentModel->selfUpdateByModelId($Data, $model->model_id);
                }
            }
        });
    }

    public function getPaymentDateAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function Model()
    {
        return $this->belongsTo($this->model, 'model_id');
    }

    public function investors()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type');
    }

    public function transactionNameAttribute()
    {
        return '22222222'; //isset($this->transaction()->first()->name)?$this->transaction()->first()->name:'No name';
    }

    public function getMerchantNameAttribute()
    {
        return isset($this->merchant()->first()->name) ? $this->merchant()->first()->name : 'No name';
    }

    public function getParticipantNameAttribute()
    {
        return isset($this->investors()->first()->name) ? $this->investors()->first()->name : 'No name';
    }

    public function paymentAllInvestors()
    {
        return $this->hasMany(PaymentInvestors::class, 'participent_payment_id');
    }

    public function paymentAll()
    {
        return $this->hasMany(PaymentInvestors::class, 'merchant_id');
    }

    public function investments()
    {
        return $this->hasMany(MerchantUser::class, 'merchant_id');
    }

    public function paymentAllInvestorsUser()
    {
        return $this->hasMany(PaymentInvestors::class, 'participent_payment_id', 'id')->where('user_id', 59);
    }

    public function paymentInvestors()
    {
        return $this->hasMany(PaymentInvestors::class, 'participent_payment_id');
    }

    public static function statusOptions()
    {
        return [
            self::StatusPending  =>'Pending',
            self::StatusCompleted=>'Completed',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function selfCreate($data)
    {
        try {
            $data['date'] = $data['date'] ?? date('Y-m-d');
            $data['model'] = $data['model'] ?? self::class;
            if ($data['model'] == self::class) {
                $validator = \Validator::make($data, self::paymentRules());
            } else {
                $validator = \Validator::make($data, self::transactionRules());
            }
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

    public function selfDeleteByModelId($id)
    {
        try {
            $Self = self::where('model_id', $id)->each(function($row){
                $row->delete();
            });
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
                }
                if (isset($data['payment'])) {
                    $Self->payment = $data['payment'];
                }
                if (isset($data['payment_date'])) {
                    $Self->payment_date = $data['payment_date'];
                }
                $Self->save();
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public static function paymentMethodOptions()
    {
        return [
            self::PaymentModeManual         => 'Manual',
            self::PaymentModeACH            => 'ACH',
            self::PaymentModeCreditCard     => 'Credit Card Payment',
            self::PaymentModeSystemGenerated=> 'System Generated',
        ];
    }

    public function getPaymentMethodNameAttribute()
    {
        $options = $this->paymentMethodOptions();

        return $options[$this->mode_of_payment];
    }
}
