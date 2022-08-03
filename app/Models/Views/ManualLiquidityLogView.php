<?php

namespace App\Models\Views;

use App\User;
use FFM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualLiquidityLogView extends Model
{
    use HasFactory;

    public function fetchResult($data = [])
    {
        $Self = new self;
        if (isset($data['from_date'])) {
            $Self = $Self->where('date', '>=', $data['from_date']);
        }
        if (isset($data['to_date'])) {
            $Self = $Self->where('date', '<=', $data['to_date']);
        }
        if (isset($data['company_id'])) {
            $Self = $Self->where('company_id', $data['company_id']);
        }
        if (isset($data['investor_id'])) {
            $Self = $Self->where('investor_id', $data['investor_id']);
        }

        return $Self;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
