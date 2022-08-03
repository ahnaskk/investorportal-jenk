<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Library\Repository\Interfaces\IPermissionLogRepository;
use Illuminate\Http\Request;
class PermissionLogController extends Controller
{
    public function __construct(IPermissionLogRepository $permission_log) {
        $this->permissionLog = $permission_log;
    }

    public function getIndex()
    {
        return view('admin.permission_log');
    }

    public function getRecords(Request $request)
    {
        $search = $request->input('search');
        $order = $request->input('order');
        $filter = [
            'module' => $request->input('module'),
            'user_id' => $request->input('user_id'),
            'action' => $request->input('action'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'action_user' => $request->input('action_user'),
            'start' => $request->input('start'),
            'limit' => $request->input('length'),
            'type' => $request->input('type'),
            'search' => $search['value'],
            'order_col' => $order[0]['column'],
            'order_by' => $order[0]['dir']
        ];
        $result = $this->permissionLog->permissionLog($filter);

        return ['sEcho' => $result['sEcho'], 'recordsTotal' => $result['recordsTotal'], 'recordsFiltered' => $result['recordsFiltered'], 'aaData' => $result['aaData']];
    }
}
