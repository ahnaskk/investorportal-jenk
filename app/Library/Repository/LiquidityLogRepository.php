<?php

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\ILiquidityLogRepository;
use App\LiquidityLog;
use App\Models\Views\LiquidityLogView;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LiquidityLogRepository implements ILiquidityLogRepository
{
    public function __construct()
    {
        $this->table = new LiquidityLogView();
    }

    public function liquidiyLogReport($start_date, $end_date, $merchant, $investor, $groupbypay, $owner, $description, $label, $search_key, $accountType,$velocity_owned = false)
    {   $user_ids = array();
        if (is_array($investor)) {
            if(!empty($investor)){
            $user_ids = User::whereIn('users.id',$investor);
            if($velocity_owned){
                $user_ids = $user_ids->where('velocity_owned',1);   
            }
            $user_ids = $user_ids->pluck('users.id')->toArray();
            }
        }
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $log_data = $this->table->orderByDesc('id');
        if (is_array($merchant)) {
            $log_data = $log_data->whereIn('merchant_id', $merchant);
        }
        if (is_array($user_ids)) {
            if(!empty($user_ids)){
                $log_data = $log_data->whereIn('member_id', $user_ids);
            }
        }

        if (is_array($label)) {
            $log_data = $log_data->whereIn('label', $label);
        }
        if ($search_key) {
            $log_data = $log_data->where(function ($query) use ($search_key) {
                $query->where('merchant_name', 'like', '%'.$search_key.'%');
                $query->orWhere('description', 'like', '%'.$search_key.'%');
                $query->orWhere('user_name', 'like', '%'.$search_key.'%');
                $query->orWhere('liquidity_change', 'like', '%'.$search_key.'%');
            });
        }

        if (is_array($description)) {
            $log_data = $log_data->whereIn('description', $description);
        }

        if ($start_date) {
            $start_date = ET_To_UTC_Time($start_date.' 00:00', 'datetime');
            $log_data = $log_data->where('created_at', '>=', $start_date);
        }
        if ($end_date) {
            $end_date = ET_To_UTC_Time($end_date.' 23:59', 'datetime');
            $log_data = $log_data->where('created_at', '<=', $end_date);
        }
        if ($owner && $owner != '' && count($owner) > 0) {
            $company_users_owner = User::whereIn('company', $owner)->pluck('id');
            $log_data = $log_data->whereIn('user_id', $company_users_owner);
        }
        if ($accountType) {
            if($velocity_owned){
                $roleUsers = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
                $roleUsers = $roleUsers->whereIn('user_has_roles.role_id', $accountType)->where('velocity_owned',1);
                $roleUsers = $roleUsers->pluck('model_id');
            }else{
                $roleUsers = DB::table('user_has_roles')->whereIn('role_id', $accountType)->pluck('model_id');
            }
            $log_data = $log_data->whereIn('user_id', $roleUsers);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $log_data = $log_data->where('company', $userId);
            } else {
                $log_data = $log_data->where('creator_id', $userId);
            }
        }
        if ($groupbypay == 'true') {
            $log_data = $log_data->whereNotNull('batch_id')->groupBy('batch_id', 'description');
            $log_data = $log_data->select('id', 'member_id', 'name_of_deal', 'member_type', 'merchant_id', 'investor_id', 'batch_id', 'description', DB::raw('sum(liquidity_change) as liquidity_change'), DB::raw('sum(final_liquidity) as final_liquidity'), DB::raw('(aggregated_liquidity) as aggregated_liquidity'), 'created_at', 'user_name', 'merchant_name', 'creator_id', 'merchant_deleted_at', 'liquidity_creator');
        } else {
            $log_data = $log_data->select('*');
        }
        return $log_data;
    }
}
