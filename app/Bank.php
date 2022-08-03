<?php

namespace App;

use App\Models\InvestorAchRequest;
use Exception;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    const CREDIT = 'credit';
    const DEBIT = 'debit';
    protected $guarded = [];
    protected $table = 'bank_details';

    public function User()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public static function rules($id = 0, $merge = [])
    {
        return array_merge([
      'name'                => 'required',
      'account_holder_name' => 'required',
      'investor_id'         => 'required',
      'type'                => 'required',
    ],
    $merge);
    }

    public function selfCreate($data)
    {
        try {
            $BankCount = self::whereinvestor_id($data['investor_id'])->count();
            $type = explode(',', $data['type']);
            if (! $BankCount) {
                if (in_array(self::CREDIT, $type)) {
                    $data['default_credit'] = 1;
                }
                if (in_array(self::DEBIT, $type)) {
                    $data['default_debit'] = 1;
                }
            }
            if (isset($data['default_debit'])) {
                if (! in_array(self::DEBIT, $type)) {
                    $data['default_debit'] = null;
                }
            }
            if (isset($data['default_credit'])) {
                if (! in_array(self::CREDIT, $type)) {
                    $data['default_credit'] = null;
                }
            }
            $validator = \Validator::make($data, $this->rules(0, ['acc_number'=> 'required|numeric']),$messages = [
                'numeric' => 'The account number must be a numeric value',
            ]);
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }
            $Self = self::create($data);
            if ($Self->default_debit) {
                $All = self::where('investor_id', $Self->investor_id)->where('id', '!=', $Self->id);
                $All->update(['default_debit'=>0]);
            }
            if ($Self->default_credit) {
                $All = self::where('investor_id', $Self->investor_id)->where('id', '!=', $Self->id);
                $All->update(['default_credit'=>0]);
            }
            $return['result'] = 'success';
            $return['id'] = $Self->id;
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function selfUpdate($data, $id)
    {
        try {
            $Self = self::find($id);
            $type = explode(',', $data['type']);
            if (! isset($data['investor_id'])) {
                $data['investor_id'] = $Self->investor_id;
            }
            $default_debitCheck = self::where('investor_id', $data['investor_id'])->where('default_debit', 1)->first();
            if (! $default_debitCheck) {
                if (! isset($data['default_debit'])) {
                    $data['default_debit'] = 1;
                }
            }
            if (isset($data['default_debit'])) {
                if (! in_array(self::DEBIT, $type)) {
                    $data['default_debit'] = null;
                }
                if ($data['default_debit']) {
                    $All = self::where('investor_id', $Self->investor_id)->where('id', '!=', $id);
                    $All->update(['default_debit'=>0]);
                }
            }
            $validator = \Validator::make($data, $this->rules(0, ['acc_number'=> 'numeric']),$messages = [
                'numeric' => 'The account number must be a numeric value',
            ]);      
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }
            $default_creditCheck = self::where('investor_id', $data['investor_id'])->where('default_credit', 1)->first();
            if (! $default_creditCheck) {
                if (! isset($data['default_credit'])) {
                    $data['default_credit'] = 1;
                }
            }
            if (isset($data['default_credit'])) {
                if (! in_array(self::CREDIT, $type)) {
                    $data['default_credit'] = null;
                }
                if ($data['default_credit']) {
                    $All = self::where('investor_id', $Self->investor_id)->where('id', '!=', $id);
                    $All->update(['default_credit'=>0]);
                }
            }
            $validator = \Validator::make($data, $this->rules($id));
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }
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

            $check_bank = InvestorAchRequest::where('bank_id', $id)->count();

            if ($check_bank > 0) {
                throw new Exception("Can't Delete Bank Account. Already referred!");
            }

            if (! $Self->delete()) {
                throw new Exception("Can't Delete Bank Account ".$id, 1);
            }
            if ($Self->default_credit) {
                $default_credit = self::where('investor_id', $Self->investor_id)->where('type', 'LIKE', '%credit%')->first(['id', 'investor_id', 'acc_number', 'account_holder_name', 'bank_address', 'name', 'routing', 'type', 'default_debit', 'default_credit']);
                if ($default_credit) {
                    $default_creditData = $default_credit->toArray();
                    $default_creditData['default_credit'] = 1;
                    $return_function = self::selfUpdate($default_creditData, $default_creditData['id']);
                    if ($return_function['result'] != 'success') {
                        throw new \Exception($return_function['result'], 1);
                    }
                }
            }
            if ($Self->default_debit) {
                $default_debit = self::where('investor_id', $Self->investor_id)->where('type', 'LIKE', '%debit%')->first(['id', 'investor_id', 'acc_number', 'account_holder_name', 'bank_address', 'name', 'routing', 'type', 'default_debit', 'default_credit']);
                if ($default_debit) {
                    $default_debitData = $default_debit->toArray();
                    $default_debitData['default_debit'] = 1;
                    $return_function = self::selfUpdate($default_debitData, $default_debitData['id']);
                    if ($return_function['result'] != 'success') {
                        throw new \Exception($return_function['result'], 1);
                    }
                }
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function getAccountHolderNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    public function getTypeAttribute($value)
    {
        if (! is_array($value)) {
            return strtolower($value);
        } else {
            return $value;
        }
    }
}
