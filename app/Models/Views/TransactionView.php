<?php

namespace App\Models\Views;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionView extends Model
{
    use HasFactory;

    public function getStatusNameAttribute()
    {
        $Self = new \App\Models\Transaction;
        $options = $Self->statusOptions();

        return $options[$this->status];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
