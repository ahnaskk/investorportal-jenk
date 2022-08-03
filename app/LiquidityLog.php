<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class LiquidityLog extends Model
{
    protected $guarded = [];

    protected $table = 'liquidity_log';

    public static function rules($id = 0, $merge = [])
    {
        return array_merge([
            // 'merchant_id' => 'required',
        ],
        $merge);
    }

    public function investmentData()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function merchantData()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function merchantData2()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
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

    public static function descriptions()
    {
        $desc = [
            'Payment' => 'Payment',
            'ACH Payment' => 'ACH Payment',
            'Payment Deletion' => 'Payment Deletion',
            'Debit Payment' => 'Debit Payment',
            'Assign Investor' => 'Assign Investor',
            'Return Investment' => 'Return Investment',
            'based_on_liquidity' => 'based_on_liquidity',
            'based_on_payment' => 'based_on_payment',
            'Debited to investor' => 'Debited to investor',
            'Table Repair' => 'Table Repair',
            'Investor Details Updation' => 'Investor Details Updation',
            'Reassigned To New Investor' => 'Reassigned To New Investor',
            'Re-Assign' => 'Re-Assign',
            'Assign Investor from CRM' => 'Assign Investor from CRM',
            'Remove Management Fee From Overpayment' => 'Remove Management Fee From Overpayment',
            'General Liquidity Update' => 'General Liquidity Update',
            // 'Delete Investor' => 'Delete Investor',
            'Velocity Distribution Updation' => 'Velocity Distribution Updation',
            'Funded Amount Adjustment' => 'Funded Amount Adjustment',
            'payment generation(multiple)' => 'payment generation(multiple)',
            'Payment Generation(CSV Upload)' => 'Payment Generation(CSV Upload)',
            'Delete Investor Transaction' => 'Delete Investor Transaction',
            'Liquidity Adjustor' => 'Liquidity Adjustor',
            'Generate Multiple Payment' => 'Generate Multiple Payment',
            'Generate Multiple Payment By Uploading Csv' => 'Generate Multiple Payment By Uploading Csv',
            'Custom Amount Changed' => 'Custom Amount Changed',
            'Lender Fee Transaction' => 'Lender Fee Transaction'
        ];
        $investor_transaction_label = collect(\ITran::getAllOptions())->reject(function ($e, $key) {
            return $key == 0;
        })->mapWithKeys(function ($el) {
            return [$el => $el];
        })->toArray();
        $desc = collect($desc)->merge($investor_transaction_label)->toArray();

        return $desc;
    }
}
