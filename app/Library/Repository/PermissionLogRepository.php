<?php

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\IPermissionLogRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Models\PermissionLog;
use App\User;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use FFM;

class PermissionLogRepository implements IPermissionLogRepository
{
    public function __construct(IRoleRepository $role)
    {
        $this->table = new PermissionLog();
        $this->role = $role;
    }
    public function permissionLog($filter=[])
    {
        $module = $filter['module'];
        $user_id = $filter['user_id'];
        $action = $filter['action'];
        $from_date = $filter['from_date'];
        $to_date = $filter['to_date'];
        $action_user = $filter['action_user'];
        $start = $filter['start'];
        $limit = $filter['limit'];
        $type = $filter['type'];
        $search = $filter['search'];
        $order_col = $filter['order_col'];
        $order_by = $filter['order_by'];

        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $query = $this->table->where('object_id', '>', 0)->join('users', 'users.id', '=', 'permission_logs.modified_by');

        if (! empty($module)) {
            $query->where('permission_logs.module_id', $module);
        }
        if (! empty($user_id)) {
            $query->where('permission_logs.modified_by', $user_id);
        }
        if (! empty($type)) {
            if ($type == 'user_permission') {
                $query->where('permission_logs.user_id', '!=', 0);
            } elseif ($type == 'role_permission') {
                $query->where('permission_logs.role_id', '!=', 0);
            }
        }
        
        if (! empty($action)) {
            $query->where('permission_logs.action', $action);
        }
        if (! empty($from_date)) {
            $from_date = ET_To_UTC_Time($from_date.' 00:00', 'datetime');
            $query->where('permission_logs.created_at', '>=', $from_date);
        }
        if (! empty($to_date)) {
            $to_date = ET_To_UTC_Time($to_date.' 23:59', 'datetime');
            $query->where('permission_logs.created_at', '<=', $to_date);
        }
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('name', 'like', '%'.$search.'%')->orWhereRaw("DATE_FORMAT(permission_logs.created_at,'%m-%d-%Y') like ?", ["%$search%"])->orWhere('email', 'like', '%'.$search.'%')->orWhere('permission_logs.type', 'like', '%'.$search.'%')->orWhere(DB::raw('REPLACE(permission_logs.type, "_", " ")'), 'like', '%'.$search.'%')->orWhere('permission_logs.action', 'like', '%'.$search.'%')->orWhere('permission_logs.detail', 'like', '%'.$search.'%');
            });
        }
        if (! empty($order_col)) {
            if ($order_col == 0) {
                $sub_query = User::withTrashed()->select(DB::raw('id AS user_id'), 'name');
                $query->join(DB::raw('('.$sub_query->toSql().') AS sub_query'), 'sub_query.user_id', '=', 'permission_logs.modified_by');
                $query->orderBy('sub_query.name', $order_by);
            } elseif ($order_col == 1) {
                $query->orderBy('permission_logs.created_at', $order_by);
            } elseif ($order_col == 2) {
                $query->orderBy('permission_logs.detail', $order_by);
            } elseif ($order_col == 3) {
                $query->orderBy('permission_logs.type', $order_by);
            } elseif ($order_col == 4) {
                $query->orderBy('permission_logs.action', $order_by);
            }
        }
        if (! empty($action_user)) {
            $action_user = explode('-', $action_user);
            $action_user_type = $action_user[1];
            $action_user_id = $action_user[0];
            if ($action_user_type) {
                if ($action_user_type == 'role') {
                    $query->where('permission_logs.role_id', $action_user_id);
                } elseif ($action_user_type == 'user') {
                    $query->where('permission_logs.user_id', $action_user_id);
                }
            }
        }
        $query->select('permission_logs.*');
        $query->orderByDesc('permission_logs.id');
        $total_records = $query->count();
        $logs = $query->limit($limit)->offset($start)->get();
        $rows = [];
        if (count($logs) > 0) {
            foreach ($logs as $log) {
                $changes = '';
                try {
                    $details = json_decode($log->detail, true);
                } catch (\ErrorException $e) {
                    $details = [];
                }
                if (is_array($details) and count($details) > 0) {
                    foreach ($details as $field_name => $value) {
                        if ($log->action == 'created' || $log->action == 'deleted') {
                            $omit = ['id', 'updated_at'];
                            
                            if (in_array($field_name, $omit) || $value == '' || $value == "null") {
                                continue;
                            }
    
                            if (strpos(strtolower($field_name), 'date') !== false) {
                                $toFormat = (strpos($value, ':') !== false) ? 'm/d/Y h:i:s A' : FFM::defaultDateFormat('db');
                                try {
                                    $value = Carbon::parse($value)->format($toFormat);
                                } catch (InvalidFormatException $e) {
                                }
                            }
                            
                            $value = (is_float($value)) ? round($value, 2) : $value;
                            if (is_array($value)) {
                                $changes .= '<strong>'.PermissionLog::prettyStatus($field_name).'</strong> : '.json_encode($value).'<br>';
                            } elseif ($field_name == 'created_at' or $field_name == 'updated_at' or $field_name == 'deleted_at' or $field_name == 'paused_at' or $field_name == 'resumed_at') {
                                $changes .= '<strong>'.PermissionLog::prettyStatus($field_name).'</strong> : '.\FFM::datetime($value).'<br>';
                            } else {
                                $changes .= '<strong>'.PermissionLog::prettyStatus($field_name).'</strong> : '.str_replace('.000000Z', '', $value).'<br>';
                            }
                        }
                    }
                }
                $data = [0 => $log->modifiedUser->name, 1 => FFM::datetime($log->created_at), 2 => $changes, 3 => $log->module->name, 4 => ($log->role_id) ? 'Role Permission': 'User Permission', 5 => PermissionLog::prettyStatus($log->action)];
                ksort($data);
                $rows[] = array_values($data);
            }
        }
        return ['sEcho' => 0, 'recordsTotal' => $total_records, 'recordsFiltered' => $total_records, 'aaData' => $rows];
    }
}