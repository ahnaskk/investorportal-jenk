<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MerchantLogController extends AdminAuthController
{
    public function getStatus(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $statusId = $request->input('status_id');
        $merchantIds = $request->input('merchants');
    }
}
