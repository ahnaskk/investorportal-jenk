<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessResource;
use App\Merchant;
use App\ParticipentPayment;
use App\User;
use App\UserActivityLog;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserActivityLogController extends Controller
{
    protected $user = false;
    protected $role;

    public function __construct()
    {
        $this->setDefaultAuth();
        $this->middleware(function ($request, $next) {
            $this->setDefaultAuth();

            return $next($request);
        });
    }

    private function setDefaultAuth()
    {
        if (! Auth::user()) {
            return false;
        }
        $this->user = Auth::user();
        $this->role = optional($this->user->roles()->first()->toArray())['name'] ?? '';
        if (! Auth::user()->hasRole('admin')) {
            abort(response()->json('Not found', 404));
        }
    }

    public function getRecords(Request $request)
    {
        $time_zone = 'America/New_York';
        $data_id = $request->input('data_id');
        $type = $request->input('type');
        $user_id = $request->input('user_id');
        $action = $request->input('action');
        $search_type = $request->input('search_type');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $search = $request->input('search');
        $search = $search['value'];
        $start = $request->input('start');
        $limit = $request->input('length');
        $order = $request->input('order');
        $order_col = $order[0]['column'];
        $order_by = $order[0]['dir'];
        $query = UserActivityLog::where('object_id', '>', 0)->join('users', 'users.id', '=', 'user_activity_logs.user_id');
        if (! empty($type)) {
            $query->where('user_activity_logs.type', $type);
        }
        if (! empty($user_id)) {
            $query->where('user_activity_logs.user_id', $user_id);
        }
        if (! empty($action)) {
            $query->where('user_activity_logs.action', $action);
        }
        if (! empty($from_date)) {
            $from_date = Carbon::parse($from_date)->format('Y-m-d');
            $query->whereDate('user_activity_logs.created_at', '>=', $from_date);
        }
        if (! empty($to_date)) {
            $to_date = Carbon::parse($to_date)->format('Y-m-d');
            $query->whereDate('user_activity_logs.created_at', '<=', $to_date);
        }
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('user_activity_logs.type', 'like', '%'.$search.'%')->orWhere(DB::raw('REPLACE(user_activity_logs.type, "_", " ")'), 'like', '%'.$search.'%')->orWhere('user_activity_logs.action', 'like', '%'.$search.'%')->orWhere('user_activity_logs.detail', 'like', '%'.$search.'%');
            });
        }
        if (! empty($order_col)) {
            if ($order_col == 0) {
                $sub_query = User::select(DB::raw('id AS user_id'), 'name');
                $query->join(DB::raw('('.$sub_query->toSql().') AS sub_query'), 'sub_query.user_id', '=', 'user_activity_logs.user_id');
                $query->orderBy('sub_query.name', $order_by);
            } elseif ($order_col == 1) {
                $query->orderBy('user_activity_logs.created_at', $order_by);
            } elseif ($order_col == 2) {
                $query->orderBy('user_activity_logs.detail', $order_by);
            } elseif ($order_col == 3) {
                $query->orderBy('user_activity_logs.type', $order_by);
            } elseif ($order_col == 4) {
                $query->orderBy('user_activity_logs.action', $order_by);
            }
        }
        $query->select('user_activity_logs.*');
        $query->orderByDesc('user_activity_logs.id');
        $total_records = $query->count();
        $logs = $query->limit($limit)->offset($start)->get();
        $rows = [];
        if (count($logs) > 0) {
            foreach ($logs as $log) {
                $changes = $this->appendParentPrefix($log);
                try {
                    $details = json_decode($log->detail, true);
                } catch (\ErrorException $e) {
                    $details = [];
                }
                $object = false;
                if ($log->action == 'updated') {
                    if ($log->type == 'user' and $log->user_id == $log->object_id and strpos($log->detail, 'remember_token') !== false) {
                        continue;
                    }
                    if (strpos($log->detail, 'remember_token') !== false || strpos($log->detail, 'two_factor_secret') !== false || strpos($log->detail, 'two_factor_recovery_codes') !== false) {
                        continue;
                    }
                }
                if (is_array($details) and count($details) > 0) {
                    foreach ($details as $field_name => $value) {
                        if ($log->action == 'updated') {
                            $field_name = $this->properFieldName($object, $field_name);
                            if ($field_name == 'created_at' or $field_name == 'updated_at' or $field_name == 'deleted_at' or $field_name == 'activation') {
                                continue;
                            } elseif ($field_name == 'password') {
                                $effectedUser = User::where('id', $log->object_id)->first();
                                $changes = $effectedUser->name."'s Password Changed";
                                continue;
                            }
                            $changes .= '<ul style="padding-left: 10px; margin: 0;"><li>';
                            $fromValue = '';
                            $toValue = '';
                            if (is_array($value)) {
                                $fromValue = $value['from'];
                                $toValue = $value['to'];
                            }
                            $fromValue = (is_float($fromValue)) ? round($fromValue, 2) : $fromValue;
                            $toValue = (is_float($toValue)) ? round($toValue, 2) : $toValue;
                            if ($log->type == 'user') {
                                $field_name = is_array($field_name) ? json_encode($field_name) : $field_name;
                                $fromValue = is_array($fromValue) ? json_encode($fromValue) : $fromValue;
                                $toValue = is_array($toValue) ? json_encode($toValue) : $toValue;
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> changed from <span class="orange">"'.($fromValue).'"</span>  to <span class="blue">"'.($toValue).'"</span><br>';
                            } else {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> changed from <span class="orange">"'.($fromValue).'"</span>  to <span class="blue">"'.($toValue).'"</span><br>';
                            }
                            $changes .= '</li></ul>';
                        } else {
                            if (strpos(strtolower($field_name), 'date') !== false) {
                                $toFormat = (strpos($value, ':') !== false) ? 'm/d/Y h:i:s A' : 'm/d/Y';
                                try {
                                    $value = Carbon::parse($value)->format($toFormat);
                                } catch (InvalidFormatException $e) {
                                }
                            }
                            $value = (is_float($value)) ? round($value, 2) : $value;
                            if (is_array($value)) {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.json_encode($value).'<br>';
                            } elseif ($field_name == 'created_at' or $field_name == 'updated_at' or $field_name == 'deleted_at') {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.Carbon::parse($value)->format('m/d/Y h:i:s A').'<br>';
                            } elseif ($field_name == 'login_date') {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.Carbon::parse($value)->format('m/d/Y h:i:s A').'<br>';
                            } else {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.str_replace('.000000Z', '', $value).'<br>';
                            }
                        }
                    }
                }
                $rows[] = [$log->user->name, $log->created_at->format('m/d/Y h:i:s A'), $changes, UserActivityLog::logTypePrettyStatus($log->type), UserActivityLog::prettyStatus($log->action)];
            }
        }

        return new SuccessResource(['sEcho' => 0, 'data' => $rows, 'recordsTotal' => $total_records, 'recordsFiltered' => $total_records, 'aaData' => $rows]);
    }

    private function properFieldName($object, $field_name)
    {
        return $field_name;
    }

    private function appendParentPrefix($log)
    {
        $changes = '';
        if ($log->type == 'user' or $log->type == 'investor') {
            $entry = User::where('id', $log->object_id)->first();
            $changes .= (($entry) ? $entry->name.'<br>' : '');
        } elseif ($log->type == 'payment') {
            $object = ParticipentPayment::where('id', $log->object_id)->first();
            $changes .= ((isset($object->merchant->id)) ? '<strong>Merchant : </strong><a href="'.url('admin/merchants/view', $object->merchant->id).'" target="_blank">'.$object->merchant->name.'</a><br>' : '');
        } elseif ($log->type == 'merchant') {
            $object = Merchant::where('id', $log->object_id)->first();
            $changes .= (($object) ? '<strong>Merchant : </strong><a href="'.url('admin/merchants/view', $object->id).'" target="_blank">'.$object->name.'</a><br>' : '');
        }

        return $changes;
    }
}
