<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipentPaymentView extends Model
{
    use HasFactory;

    public function getStatusNameAttribute()
    {
        $Self = new \App\ParticipentPayment;
        $options = $Self->statusOptions();

        return $options[$this->status];
    }

    public function getPaymentMethodNameAttribute()
    {
        $Self = new \App\ParticipentPayment;
        $options = $Self->paymentMethodOptions();

        return $options[$this->mode_of_payment];
    }
}
