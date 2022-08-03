<?php

namespace App;

//fzl laravel8 use Database\Seeders\Role;
use Illuminate\Database\Eloquent\Model;

class Role_module extends Model
{
    protected $fillable = ['role_id', 'permission_id', 'module_id','user_id'];
}
