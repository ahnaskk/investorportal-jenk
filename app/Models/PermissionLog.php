<?php

namespace App\Models;

use App\Module;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class PermissionLog extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'permission_logs';
    protected $fillable = [
        'modified_by',
        'user_id',
        'object_id',
        'type',
        'action',
        'detail',
        'role_id',
        'module_id'
    ];
    public static function prettyStatus($status)
    {
        $status = str_replace('_', ' ', $status);

        return ucwords($status);
    }
    public function modifiedUser()
    {
        return $this->belongsTo(\App\User::class, 'modified_by');
    }
    public function module() {
        return $this->belongsTo(Module::class, 'module_id');
    }
    public static function transformer($users)
    {
        $listing = [];
        if ($users) {
            foreach ($users as $user) {
                $listing[$user->id] = $user->name;
            }
        }

        return $listing;
    }
    public static function activity_actions()
    {
        return [
            'created' => 'Created',
            'deleted' => 'Deleted',
        ];
    }
    public static function modules() {
        return Module::pluck('name', 'id');
    }
    public static function activity_user() {
        $users = User::where('email', '!=', '')->select('id', 'name')->get();
        return self::transformer($users);
    }
    public static function activity_role_user()
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $roles = collect(Role::select('id', 'name')->get());
        $users = collect(User::withTrashed()->select('id', 'name')->get());
        $listing = [];
        if ($roles) {
            foreach ($roles as $user) {
                $listing[$user->id.'-role'] = $user->name;
            }
        }
        if ($users) {
            foreach ($users as $u) {
                $listing[$u->id.'-user'] = $u->name;
            }
        }
        return $listing;
    }
    public static function type() {
        return [
            'role_permission' => 'Role Permission',
            'user_permission' => 'User Permission'
        ];
    }
}
