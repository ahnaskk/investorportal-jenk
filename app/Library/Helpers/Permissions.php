<?php

namespace App\Library\Helpers;

use function App\Helpers\modelQuerySql;
use App\Module;
use App\Role_module;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Permissions
{
    public function __construct()
    {
    }

    public function checkAuth($module)
    {
        $roles = Auth::user()->getRoleNames();
        $user_id = Auth::user();
        if ($roles[0] == 'admin') {
            return true;
        } else {
            $role = Role::where('name', $roles[0])->first();
            $module = Module::where('name', $module)->first();
            $res = Role_module::select(['permission_id'])->where('module_id', $module->id)->where('role_id', $role->id)->where('permission_id', 1)->first();
            $res1 = Role_module::select(['permission_id'])->where('module_id', $module->id)->where('user_id', $user_id->id)->where('permission_id', 1)->first();
            if (isset($res->permission_id) || isset($res1->permission_id)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function isAllow($module, $perm)
    {
        $roles = Auth::user()->getRoleNames();
        $user_id = Auth::user();
        if ($roles[0] == 'admin') {
            return true;
        } else {
            $role = Role::where('name', $roles[0])->first();
            $module = Module::where('name', $module)->first();

            $permission = Permission::where('name', $perm)->first();

            $res = Role_module::select(['permission_id'])->where('role_id', $role->id)->where('module_id', $module->id)->where('permission_id', $permission->id)->first();
            $res1 = Role_module::select(['permission_id'])->where('user_id', $user_id->id)->where('module_id', $module->id)->where('permission_id', $permission->id)->first();

            if (isset($res->permission_id) || isset($res1->permission_id)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function isModule($modules)
    {
        $roles = Auth::user()->getRoleNames();
        $user_id = Auth::user();
        if ($roles[0] == 'investor' || $roles[0] == 'admin') {
            return true;
        } else {
            $role = Role::where('name', $roles[0])->first();
            if ($modules[0] == 'Reports') {
                $res = Role_module::select(['permission_id'])->where('role_id', $role->id)->whereIn('module_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21])->first();
                $res1 = Role_module::select(['permission_id'])->where('user_id', $user_id->id)->whereIn('module_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21])->first();
            }

            if ($modules[0] == 'Settings') {
                $m_list = Module::where('name', 'LIKE', '%'.$modules[0].'%')->pluck('id')
              ->toArray();
                $res = Role_module::select(['permission_id'])->where('role_id', $role->id)->whereIn('module_id', $m_list)->first();
                $res1 = Role_module::select(['permission_id'])->where('user_id', $user_id->id)->whereIn('module_id', $m_list)->first();
            }

            if (isset($res->permission_id) || isset($res1->permission_id) ) {
                return true;
            }
            
            foreach ($modules as $m) {
                $module = Module::where('name', $m)->first();
                $res1 = Role_module::select(['permission_id'])->where('user_id', $user_id->id)->where('module_id', $module->id)->first();
                $res = Role_module::select(['permission_id'])->where('role_id', $role->id)->where('module_id', $module->id)->first();
                if (isset($res->permission_id) || isset($res1->permission_id)) {
                    return true;
                }
            }

            return false;
        }
    }
}
