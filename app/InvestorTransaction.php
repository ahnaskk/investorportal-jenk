<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvestorTransaction extends Model
{
    const MethodByAdminCredit       = 1;
    const MethodByAdminDebit        = 2;
    const MethodByAutomaticDebit    = 3;
    const MethodByMarketplaceCredit = 4;
    const MethodByParticipantCredit = 5;
    const MethodByParticipantDebit  = 6;
    const MethodByAutomaticCredit   = 7;
    const DEBIT                     = 1;
    const CREDIT                    = 2;
    const StatusCompleted           = 1;
    const StatusPending             = 2;
    const StatusReturned            = 3;
    protected $fillable = [
        'creator_id',
        'investor_id',
        'account_no',
        'amount',
        'batch',
        'category_notes',
        'entity1',
        'entity2',
        'entity3',
        'entity4',
        'entity5',
        'entity6',
        'transaction_category',
        'transaction_method',
        'transaction_type',
        'status',
        'maturity_date',
        'date'
    ];
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->status = self::StatusCompleted;
        });
        static::created(function ($model) {
            $model->status = self::StatusCompleted;
            $ParticipentPaymentModel = new ParticipentPayment;
            $TransactionData = [
                'payment'          => $model->amount,
                'payment_date'     => $model->date,
                'model_id'         => $model->id,
                'model'            => self::class,
                'status'           => ParticipentPayment::StatusCompleted,
                'creator_id'       => (isset($model->creator_id)) ? $model->creator_id : null
            ];
            $ParticipentPaymentModel->selfCreate($TransactionData);
        });
        static::updated(function ($model) {
            $ParticipentPaymentModel = new ParticipentPayment;
            $Data = [
                'payment'     => $model->amount,
                'payment_date'=> $model->date,
            ];
            $ParticipentPaymentModel->selfUpdateByModelId($Data, $model->id);
        });
        static::deleted(function ($model) {
            $ParticipentPaymentModel = new ParticipentPayment;
            $ParticipentPaymentModel->selfDeleteByModelId($model->id);
        });
    }

    public static function rules ($id=0, $merge=[]) {
        return array_merge([
            'amount'               => 'required',
            'transaction_category' => 'required',
            'transaction_method'   => 'required',
            'date'                 => 'after:"2016-01-01"',
        ],
        $merge);
    }
    public function selfUpdateByModelId($data, $id)
    {
        try {
            $Self = self::find($id);
            if ($Self) {
                if (isset($data['status'])) {
                    $Self->status = $data['status'];
                }
                $Self->save();
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class)->withTrashed();
    }

    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id')->withTrashed();
    }

    public static function getTransactionSum(int $userId = 0, $categoryId = 0, int $status = 0, int $type = 0) : float
    {
        $query = self::where('investor_id', $userId);
        if (! is_array($categoryId) and $categoryId > 0) {
            $query->where('transaction_category', $categoryId);
        } elseif (is_array($categoryId) and count($categoryId) > 0) {
            $query->whereIn('transaction_category', $categoryId);
        }
        if ($type > 0) {
            $query->where('transaction_type', $type);
        }
        $query->where('status', self::StatusCompleted);

        return $query->sum('amount');
    }

    public static function getDailyAverage(int $userId = 0, int $categoryId = 0)
    {
        $query = self::where('investor_id', $userId)->where('date', '<', NOW());
        $query->where('status', self::StatusCompleted);
        if ($categoryId > 0) {
            $query->where('transaction_category', $categoryId);
        }
        $startDate = "'".$query->min('date')."'";
        $query->select(
            DB::raw('SUM(amount) as total_credit'),
            DB::raw("SUM(amount*TIMESTAMPDIFF(day,investor_transactions.date,NOW())/TIMESTAMPDIFF(day,$startDate,NOW())) as average")
        );
        $result = $query->first();

        return [
            $result ? $result->average : 0,
            $result ? $result->total_credit : 0,
        ];
    }

    public static function transactionMethodOptions()
    {
        return [
            self::MethodByAdminCredit       => 'Admin Panel Credit',
            self::MethodByAdminDebit        => 'Admin Panel Debit',
            self::MethodByAutomaticDebit    => 'Automatic Debit',
            self::MethodByMarketplaceCredit => 'Marketplace Credit',
            self::MethodByParticipantCredit => 'Participant Credit',
            self::MethodByParticipantDebit  => 'Participant Debit',
            self::MethodByAutomaticCredit   => 'Automatic Credit',
        ];
    }

    public static function transactionMethodOptionsDebit()
    {
        $debit_array = [
            self::MethodByAdminDebit       => 'Admin Panel Debit',
            self::MethodByAutomaticDebit   => 'Automatic Debit',
            self::MethodByParticipantDebit => 'Participant Debit',
        ];

        $options = '';
        foreach ($debit_array as $option_val=> $option_label) {
            $options .= "<option value='$option_val'>$option_label</option>";
        }

        return $options;
    }

    public static function transactionMethodOptionsCredit()
    {
        $credit_array = [
            self::MethodByAdminCredit       => 'Admin Panel Credit',
            self::MethodByMarketplaceCredit => 'Marketplace Credit',
            self::MethodByParticipantCredit => 'Participant Credit',
            self::MethodByAutomaticCredit   => 'Automatic Credit',
        ];

        $options = '';
        foreach ($credit_array as $option_val=> $option_label) {
            $options .= "<option value='$option_val'>$option_label</option>";
        }

        return $options;
    }

    public function getTransactionMethodNameAttribute()
    {
        $statuses = $this->transactionMethodOptions();

        return $statuses[$this->transaction_method];
    }

    public static function transactionTypeOptions()
    {
        return [
            self::DEBIT  => 'Debit',
            self::CREDIT => 'Credit',
        ];
    }

    public function getTransactionTypeNameAttribute()
    {
        $statuses = $this->transactionTypeOptions();

        return $statuses[$this->transaction_type];
    }

    public static function statusOptions()
    {
        return [
            self::StatusPending   => 'Pending',
            self::StatusCompleted => 'Completed',
            self::StatusReturned  => 'Returned',
        ];
    }

    public function getStatusNameAttribute()
    {
        $options = $this->statusOptions();

        return $options[$this->status];
    }
}
