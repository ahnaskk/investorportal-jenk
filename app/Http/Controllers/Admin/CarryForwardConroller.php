<?php

namespace App\Http\Controllers\Admin;

use App\CarryForward;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;

class CarryForwardConroller extends Controller
{
    public function destroy($id)
    {
        if (CarryForward::destroy($id)) {
            \request()->session()->flash('message', 'FAQ deleted!');

            return redirect()->back();
        }
    }

    public function deletemultiple(Request $request)
    {
        CarryForward::whereIn('id', $request->multi_id)->delete();
    }

    public function deletemultiple_filter(Request $request)
    {
        $startDate = $request->date_start;
        $endDate = $request->date_end;
        $investors = $request->investors;
        $merchants = $request->merchants;
        $type = $request->type;
        $carry_forward = CarryForward::with(['merchant']);
        if ($type && is_array($type)) {
            $carry_forward = $carry_forward->whereIn('carry_forwards.type', $type);
        }
        if ($investors && is_array($investors)) {
            $carry_forward = $carry_forward->whereIn('carry_forwards.investor_id', $investors);
        }
        if ($merchants && is_array($merchants)) {
            $carry_forward = $carry_forward->whereIn('carry_forwards.merchant_id', $merchants);
        }
        if ($startDate != 0) {
            $startDate = $startDate.' 00:00:00';
            $carry_forward = $carry_forward->whereDate('carry_forwards.date', '>=', $startDate);
        }
        if ($endDate != 0) {
            $endDate = $endDate.' 23:23:59';
            $carry_forward = $carry_forward->whereDate('carry_forwards.date', '<=', $endDate);
        }
        $data = $carry_forward->delete();

        return $data;
    }
}
