<?php

namespace App\Models;

use App\Models\ActumDeclineCode;
use App\User;
use Illuminate\Database\Eloquent\Model;
//fzlupgrade8.0 use Database\Seeders\ActumDeclineCode;
use Illuminate\Support\Facades\Auth;

class InvestorAchRequest extends Model
{
    const AchRequestStatusProcessing = 1;
    const AchRequestStatusAccepted = 2;
    const AchRequestStatusDeclined = 3;
    const AchStatusProcessing = 1;
    const AchStatusAccepted = 2;
    const AchStatusDeclined = 3;
    const AchStatusPending = 4;
    const MethodByAdminCredit = 1;
    const MethodByAdminDebit = 2;
    const MethodByAutomaticDebit = 3;
    const MethodByMarketplaceCredit = 4;
    const MethodByParticipantCredit = 5;
    const MethodByParticipantDebit = 6;
    const MethodByAutomaticCredit = 7;
    const CategoryTransferToVelocity = 1;
    const CategoryTransferToBank = 4;
    const CategoryReturnOfPrincipal = 12;
    protected $fillable = [
        'date',
        'investor_id',
        'bank_id',
        'order_id',
        'transaction_type',
        'transaction_method',
        'transaction_category',
        'amount',
        'ach_status',
        'ach_request_status',
        'auth_code',
        'reason',
        'status_response',
        'response',
        'request_ip_address',
        'merordernumber',
        'creator_id',
        'transaction_id',
    ];
    protected $guarded = [];

    public static function rules($id = 0, $merge = [])
    {
        return array_merge([
            'investor_id' => 'required',
            'amount'      => 'required',
        ],
        $merge);
    }

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->order_id) {
                if ($model->ach_status == self::AchStatusPending) {
                    $model->ach_status = self::AchStatusAccepted;
                }
                if (! $model->ach_request_status) {
                    $model->ach_request_status = self::AchRequestStatusProcessing;
                }
            }
            if ($model->ach_request_status == self::AchRequestStatusAccepted) {
                $model->auth_code = '';
                $model->reason = '';
            }
            if ($model->auth_code) {
                $ActumDeclineCode = ActumDeclineCode::wherecode($model->auth_code)->first();
                if ($ActumDeclineCode) {
                    $authcode = $ActumDeclineCode->definition;
                    $model->status_response = $model->reason.'['.$authcode.']';
                }
            }
        });
    }

    public function Investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function Bank()
    {
        return $this->belongsTo(\App\Bank::class, 'bank_id');
    }

    public function selfCreate($data)
    {
        try {
            $data['date'] = date('Y-m-d');
            $data['creator_id'] = (Auth::check()) ? Auth::user()->id : null;
            $validator = \Validator::make($data, $this->rules());
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }
            $Self = self::create($data);
            $return['result'] = 'success';
            $return['key'] = $Self->id;
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function selfUpdate($data, $id)
    {
        try {
            $data['creator_id'] = (Auth::check()) ? Auth::user()->id : null;
            $validator = \Validator::make($data, $this->rules($id));
            // if($validator->fails())  { foreach ($validator->errors()->getMessages() as $key => $value) { throw new \Exception($value[0]); } }
            $Self = self::find($id);
            $Self->update($data);
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
            if (! $Self->delete($id)) {
                throw new \Exception('Cant Delete InvestorAchRequest', 1);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public static function transactionMethodOptions()
    {
        return $statuses = [
            self::MethodByAdminCredit       => 'Admin Credit',
            self::MethodByAdminDebit        => 'Admin Debit',
            self::MethodByAutomaticDebit    => 'Automatic Debit',
            self::MethodByMarketplaceCredit => 'Marketplace Credit',
            self::MethodByParticipantCredit => 'Participant Credit',
            self::MethodByParticipantDebit  => 'Participant Debit',
            self::MethodByAutomaticCredit    => 'Automatic Credit',
        ];
    }

    public function getTransactionMethodNameAttribute()
    {
        $statuses = $this->transactionMethodOptions();

        return $statuses[$this->transaction_method];
    }

    public static function achStatusOptions()
    {
        return $statuses = [
            self::AchStatusPending    => 'Pending',
            self::AchStatusProcessing => 'Processing',
            self::AchStatusAccepted   => 'Accepted',
            self::AchStatusDeclined   => 'Declined',
        ];
    }

    public function getAchStatusNameAttribute()
    {
        $statuses = $this->achStatusOptions();

        return $statuses[$this->ach_status];
    }

    public static function achRequestStatusOptions()
    {
        return $statuses = [
            self::AchRequestStatusProcessing => 'Processing',
            self::AchRequestStatusAccepted   => 'Accepted',
            self::AchRequestStatusDeclined   => 'Declined',
        ];
    }

    public function getAchRequestStatusNameAttribute()
    {
        $statuses = $this->achRequestStatusOptions();

        return $this->ach_request_status ? $statuses[$this->ach_request_status] : '';
    }

    public static function transactionTypeOptions()
    {
        return $statuses = [
            'debit'           => 'Debit',
            'same_day_debit'  => 'Same Day Debit',
            'credit'          => 'Credit',
            'same_day_credit' => 'Same Day Credit',
        ];
    }

    public static function InvertedtransactionTypeOptions()
    {
        return $statuses = [
            'debit'           => 'Credit',
            'same_day_debit'  => 'Same Day Credit',
            'credit'          => 'Debit',
            'same_day_credit' => 'Same Day Debit',
        ];
    }

    public function getTransactionTypeNameAttribute()
    {
        $statuses = self::transactionTypeOptions();

        return $this->transaction_type ? $statuses[$this->transaction_type] : '';
    }

    public function getInvertedTransactionTypeNameAttribute()
    {
        $statuses = self::InvertedtransactionTypeOptions();

        return $this->transaction_type ? $statuses[$this->transaction_type] : '';
    }

    public static function transactionCategoryOptions()
    {
        $categories = \ITran::getAllOptions();

        return $categories;
    }

    public function getTransactionCategoryNameAttribute()
    {
        $categories = $this->transactionCategoryOptions();

        return $categories[$this->transaction_category];
    }
}
