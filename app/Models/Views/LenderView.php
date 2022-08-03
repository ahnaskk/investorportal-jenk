<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class LenderView extends Model
{
    public function getCtdPAttribute()
    {
        $amount = $ctd_pp - ($ctd_pp) * ($this->pre_paid + $this->commission_amount + $this->under_writing_fee + $this->amount) / ($this->invest_rtr - ($this->mgmnt_fee / 100) * $this->invest_rtr);

        return $amount;
    }

    public function getDefaultAmountAttribute()
    {
        $amount = $this->amount;
        $amount += $this->commission_amount;
        $amount += $this->pre_paid;
        $amount += $this->under_writing_fee;
        $amount -= $this->ctd_pp;

        return $amount;
    }
}
