<?php

namespace App\Providers;

use App\Jobs\PermissionLogJob;
use App\Models\PermissionLog;
use App\Module;
use App\Role_module;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->rolePermission();
    }
    private function rolePermission() {
        Role_module::created(function ($modelInstance) {
            $role_name = Role::find($modelInstance->role_id);
            if (isset($modelInstance->user_id)) {
                $user = User::find($modelInstance->user_id);
                $type = $user->name;
            } else {
                $type = $role_name->name;
            }
            $this->permissionLog($modelInstance, $type, 'created');
        });
        Role_module::deleted(function ($modelInstance) {
            $role_name = Role::find($modelInstance->role_id);
            if (isset($modelInstance->user_id)) {
                $user = User::find($modelInstance->user_id);
                $type = $user->name;
            } else {
                $type = $role_name->name;
            }
            $this->permissionLog($modelInstance, $type, 'deleted');
        });
    }
    public function permissionLog($object, $type, $action = 'updated', $detail = [])
    {
        $crm = isset($_REQUEST['PHP_AUTH_USER']) ? $_REQUEST['PHP_AUTH_USER'] : '';

        $crm_user = DB::table('user_has_roles')->where('role_id', User::CRM_ROLE)->select('model_id')->first();
        // $module_id = 0;
        // $role_id = 0;
        // $user_id = 0;
        if (Auth::user() || $crm) {
            if ($action == 'created' || $action == 'deleted') {
                if (count($detail) <= 0) {
                    $detail = $object->toArray();
                }
                if (Arr::exists($detail, 'user_id') && isset($detail['user_id'])) {
                    // $user_id = $detail['user_id'];
                    $detail['user'] = User::where('id', $detail['user_id'])->value('name');
                    unset($detail['user_id']);
                }
                if (Arr::exists($detail, 'permission_id')) {
                    $detail['permission'] = Permission::where('id', $detail['permission_id'])->value('name');
                    unset($detail['permission_id']);
                }
                if (Arr::exists($detail, 'role_id')) {
                    // $role_id = $detail['role_id'];
                    $detail['role'] = Role::where('id', $detail['role_id'])->value('name');
                    unset($detail['role_id']);
                }
                if (Arr::exists($detail, 'module_id')) {
                    // $module_id = $detail['module_id'];
                    $detail['module'] = Module::where('id', $detail['module_id'])->value('name');
                    unset($detail['module_id']);
                }
            }
        }
        $auth_user = (Auth::user()) ? Auth::user()->id : $crm_user->model_id;
        $role_id = ($object->role_id) ? $object->role_id : 0;
        $module_id = ($object->module_id) ? $object->module_id : 0;
        $user_id = ($object->user_id) ? $object->user_id : 0;
        \Log::debug("Role: ".$role_id.', - Module: '.$module_id.', - User: '.$user_id);
        PermissionLogJob::dispatch($auth_user, $type, $action, $object->id, $detail, $role_id, $module_id, $user_id);
    }
    public static function savePermissionLog(int $modifier, string $type, string $action, int $objectId = 0, array $details = [], int $role_id = 0, int $moduleId = 0, int $user_id = 0) {
        PermissionLog::create([
            'modified_by' => $modifier,
            'object_id' => $objectId,
            'type' => $type,
            'action' => $action,
            'detail' => json_encode($details),
            'role_id' => $role_id,
            'module_id' => $moduleId,
            'user_id' => $user_id
        ]);
    }
}
