<?php

namespace App\Models\Views\Reports;

use FFM;
use Illuminate\Database\Eloquent\Model;

class FeeReportView extends Model
{
    public function getFeeAttribute($value)
    {
        return FFM::dollar($value);
    }
}
