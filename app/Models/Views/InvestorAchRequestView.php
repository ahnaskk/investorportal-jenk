<?php

namespace App\Models\Views;

use FFM;
use Illuminate\Database\Eloquent\Model;

class InvestorAchRequestView extends Model
{
    public function getDateAttribute($value)
    {
        return FFM::date($value);
    }
    public function getInvestorAttribute()
    {
       return strtoupper($this->attributes['Investor']);

    }

    public function getUpdatedAtAttribute($value)
    {
        return date('m-d-Y h:i A', strtotime($value));
    }

    public function getAmountAttribute($value)
    {
        return FFM::dollar($value);
    }

    public function getAchRequestStatusNameAttribute()
    {
        $Self = new \App\Models\InvestorAchRequest;
        $statuses = $Self->achRequestStatusOptions();

        return $this->ach_request_status ? $statuses[$this->ach_request_status] : $this->ach_request_status;
    }

    public function getAchStatusNameAttribute()
    {
        $Self = new \App\Models\InvestorAchRequest;
        $statusOptions = $Self->achStatusOptions();

        return $this->ach_status ? $statusOptions[$this->ach_status] : $this->ach_status;
    }

    public function getTransactionMethodNameAttribute()
    {
        $Self = new \App\Models\InvestorAchRequest;
        $statusOptions = $Self->transactionMethodOptions();

        return $statusOptions[$this->transaction_method];
    }

    public function getTransactionTypeNameAttribute()
    {
        $Self = new \App\Models\InvestorAchRequest;
        $statuses = $Self->transactionTypeOptions();

        return $this->transaction_type ? $statuses[$this->transaction_type] : '';
    }

    public function getInvertedTransactionTypeNameAttribute()
    {
        $Self = new \App\Models\InvestorAchRequest;
        $statuses = $Self->InvertedtransactionTypeOptions();

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
