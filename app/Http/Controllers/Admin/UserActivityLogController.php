<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Library\Repository\Interfaces\IUserActivityLogRepository;
use Illuminate\Http\Request;
use App\Merchant;
class UserActivityLogController extends Controller
{
    public function __construct(IUserActivityLogRepository $activity_log) {
       $this->activityLog=$activity_log;
    }

    public function getIndex()
    {
        return view('admin.user_activity_log');
    }

    public function getInvestorTransactionLog()
    {
        return view('admin.investor_transaction_log');
    }

    public function getMerchantsRecords(Request $request)
    { 
        $search = $request->input('search');
        $order = $request->input('order');

        $filter=[
        'merchant_id' => $request->input('merchant_id'),
        'data_id' => $request->input('data_id'),
        'type' => $request->input('type'),
        'user_id' => $request->input('user_id'),
        'action' => $request->input('action'),
        'search_type' => $request->input('search_type'),
        'from_date' => $request->input('from_date'),
        'to_date' => $request->input('to_date'),
        'objectId' => $request->input('object_id'),
        'search' => $search['value'],
        'start' => $request->input('start'),
        'limit' => $request->input('length'),
        'order_col' => $order[0]['column'],
        'order_by' => $order[0]['dir'],

     ];

        $result=$this->activityLog->merchantActivityLog($filter);
        
        return ['sEcho' => $result['sEcho'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'aaData' => $result['aaData']];
    }

    public function getRecords(Request $request)
    {
        $search = $request->input('search');
        $order = $request->input('order');
        $filter = [
            'data_id' => $request->input('data_id'),
            'type' => $request->input('type'),
            'user_id' => $request->input('user_id'),
            'action' => $request->input('action'),
            'search_type' => $request->input('search_type'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'objectId' => $request->input('object_id'),
            'search' => $search['value'],
            'start' => $request->input('start'),
            'limit' => $request->input('length'),
            'order_col' => $order[0]['column'],
            'order_by' => $order[0]['dir'],
            'action_user' => $request->input('action_user')
        ];
        $result = $this->activityLog->userActivityLog($filter);
        
        return ['sEcho' => $result['sEcho'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'aaData' => $result['aaData']];
    }
  
    public function activity_logs($id)
    {
      
        $merchant_id = $id;
        $merchant = Merchant::where('id', $merchant_id)->first();

        return view('admin.merchants.merchant_log', compact('merchant_id', 'merchant'));
    }




}
