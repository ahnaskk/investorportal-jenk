<?php

namespace App\Models\Views;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestorAchTransactionView extends Model
{
    const MethodByAdminCredit = 1;
    const MethodByAdminDebit = 2;
    const MethodByAutomaticDebit = 3;
    const MethodByMarketplaceCredit = 4;
    const MethodByParticipantCredit = 5;
    const MethodByParticipantDebit = 6;
    const MethodByAutomaticCredit = 7;
    const DEBIT = 1;
    const CREDIT = 2;
    const StatusCompleted = 1;
    const StatusPending = 2;
    const StatusReturned = 3;
    use HasFactory;

    public static function transactionMethodOptions()
    {
        return [
            self::MethodByAdminCredit       => 'Admin Panel Credit',
            self::MethodByAdminDebit        => 'Admin Panel Debit',
            self::MethodByAutomaticDebit    => 'Automatic Debit',
            self::MethodByMarketplaceCredit => 'Marketplace Credit',
            self::MethodByParticipantCredit => 'Participant Credit',
            self::MethodByParticipantDebit  => 'Participant Debit',
            self::MethodByAutomaticCredit    => 'Automatic Credit',
        ];
    }
    public function getNameAttribute()
    {
       return strtoupper($this->attributes['name']);
        
    }

    public function getTransactionMethodNameAttribute()
    {
        $statuses = $this->transactionMethodOptions();

        return $statuses[$this->transaction_method];
    }

    public static function transactionTypeOptions()
    {
        return [
            self::DEBIT => 'Debit',
            self::CREDIT=> 'Credit',
        ];
    }

    public function getTransactionTypeNameAttribute()
    {
        $statuses = $this->transactionTypeOptions();

        return $statuses[$this->transaction_type];
    }

    public static function transactionCategoryOptions()
    {
        $categories = \ITran::getAllOptions();

        return $categories;
    }

    public function getTransactionCategoryNameAttribute()
    {
        $categories = $this->transactionCategoryOptions();
        $return = $categories[$this->transaction_category]??'Not Found';

        return $return;
    }

    public static function statusOptions()
    {
        return [
            self::StatusCompleted => 'Completed',
            self::StatusPending=> 'Pending',
            self::StatusReturned=> 'Returned',
        ];
    }

    public function getStatusNameAttribute()
    {
        $statuses = $this->statusOptions();

        return $statuses[$this->status];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function company_relation()
    {
        return $this->belongsTo(User::class, 'company');
    }
}
