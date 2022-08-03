<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MerchantBankAccount extends Model
{
    const CREDIT = 'credit';
    const DEBIT = 'debit';
    protected $fillable = ['account_number', 'routing_number', 'bank_name', 'account_holder_name', 'merchant_id', 'default_credit', 'default_debit', 'type'];

    public function merchant()
    {
        return $this->belongsTo(\App\Merchant::class);
    }

    public static function rules($id = 0, $merge = [])
    {
        return array_merge([
      'bank_name'                => 'required',
      'account_holder_name' => 'required',
      'type'                => 'required',
    ],
    $merge);
    }

    public function selfUpdate($data, $id)
    {
        try {
            $Self = self::find($id);
            $dataInput['account_number']=$data['account_number']??$Self->account_number;
            $dataInput['routing_number']=$data['routing_number']??$Self->routing_number;
            $dataInput['bank_name']=$data['bank_name']??$Self->bank_name;
            $dataInput['account_holder_name']=$data['account_holder_name']??$Self->account_holder_name;
            $dataInput['merchant_id']=$data['merchant_id']??$Self->merchant_id;
            $dataInput['default_credit']=$data['default_credit']??$Self->default_credit;
            $dataInput['default_debit']=$data['default_debit']??$Self->default_debit;
            $dataInput['type']=$data['type']??$Self->type;
            $debit = false;
            $credit = false;
            $type = $dataInput['type'];
            if (Str::contains($dataInput['type'], self::DEBIT)) {
                $dataInput['type'] = self::DEBIT;
                $debit = true;
            }
            if (Str::contains($type, self::CREDIT)) {
                $credit = true;
                if ($debit) {
                    $dataInput['type'] .= ','.self::CREDIT;
                } else {
                    $dataInput['type'] = self::CREDIT;
                }
            }
            if (! $debit && ! $credit) {
                $dataInput['type'] = '';
            }
            $default_debitCheck = self::where('merchant_id', $dataInput['merchant_id'])->where('default_debit', 1)->first();
            if (! $default_debitCheck) {
                if (! isset($dataInput['default_debit'])) {
                    $dataInput['default_debit'] = 1;
                }
            }
            if (isset($dataInput['default_debit'])) {
                if (! $debit) {
                    $dataInput['default_debit'] = null;
                } else {
                    $dataInput['default_debit'] = 1;
                }
                if ($dataInput['default_debit']) {
                    $All = self::where('merchant_id', $Self->merchant_id)->where('id', '!=', $id);
                    $All->update(['default_debit'=>0]);
                }
            }
            $default_creditCheck = self::where('merchant_id', $dataInput['merchant_id'])->where('default_credit', 1)->first();
            if (! $default_creditCheck) {
                if (! isset($dataInput['default_credit'])) {
                    $dataInput['default_credit'] = 1;
                }
            }
            if (isset($dataInput['default_credit'])) {
                if (! $credit) {
                    $dataInput['default_credit'] = null;
                } else {
                    $dataInput['default_credit'] = 1;
                }
                if ($dataInput['default_credit']) {
                    $All = self::where('merchant_id', $Self->merchant_id)->where('id', '!=', $id);
                    $All->update(['default_credit'=>0]);
                }
            }
            $validator = \Validator::make($dataInput, $this->rules($id));
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }
            $Self->update($dataInput);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }
    
    public function selfCreate($data)
    {
        try {
            $debit = $credit = false;
            $type = $data['type'];
            if (Str::contains($data['type'], self::DEBIT)) {
                $data['type'] = self::DEBIT;
                $debit = true;
            }
            if (Str::contains($type, self::CREDIT)) {
                $credit = true;
                if ($debit) {
                    $data['type'] .= ','.self::CREDIT;
                } else {
                    $data['type'] = self::CREDIT;
                }
            }
            if (! $debit && ! $credit) {
                $data['type'] = '';
            }
            $BankCount = self::where('merchant_id', $data['merchant_id'])->count();
            if (! $BankCount) {
                if ($credit) {
                    $data['default_credit'] = 1;
                }
                if ($debit) {
                    $data['default_debit'] = 1;
                }
            }
            if (isset($data['default_debit'])) {
                if (! $debit) {
                    $data['default_debit'] = null;
                } else {
                    $data['default_debit'] = 1;
                }
            }
            if (isset($data['default_credit'])) {
                if (! $credit) {
                    $data['default_credit'] = null;
                } else {
                    $data['default_credit'] = 1;
                }
            }
            $validator = \Validator::make($data, $this->rules(0, ['account_number'=> 'required']));
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }

            $Self = self::create($data);
            if ($Self->default_debit) {
                $All = self::where('merchant_id', $Self->merchant_id)->where('id', '!=', $Self->id);
                $All->update(['default_debit'=>0]);
            }
            if ($Self->default_credit) {
                $All = self::where('merchant_id', $Self->merchant_id)->where('id', '!=', $Self->id);
                $All->update(['default_credit'=>0]);
            }
            $return['result'] = 'success';
            $return['id'] = $Self->id;
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }
}
