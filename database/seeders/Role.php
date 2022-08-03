<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Role extends Seeder
{
    public function run()
    {
        DB::table('roles')->truncate();
        $data = [];
        $data[] = ['name'=>'admin', 'guard_name'=>'web'];
        $data[] = ['name'=>'investor', 'guard_name'=>'web'];
        $data[] = ['name'=>'branch manager', 'guard_name'=>'web'];
        $data[] = ['name'=>'lender', 'guard_name'=>'web'];
        $data[] = ['name'=>'editor', 'guard_name'=>'web'];
        $data[] = ['name'=>'company', 'guard_name'=>'web'];
        $data[] = ['name'=>'merchant', 'guard_name'=>'web'];
        $data[] = ['name'=>'viewer', 'guard_name'=>'web'];
        $data[] = ['name'=>'collection user', 'guard_name'=>'web'];
        $data[] = ['name'=>'wire ach', 'guard_name'=>'web'];
        $data[] = ['name'=>'accounts', 'guard_name'=>'web'];
        $data[] = ['name'=>'editor with creditcard access', 'guard_name'=>'web'];
        $data[] = ['name'=>'Over Payment', 'guard_name'=>'web'];
        //$data[] = ['name'=>'Fee Account', 'guard_name'=>'web'];
        $data[] = ['name'=>'crm', 'guard_name'=>'web'];
        $data[] = ['name'=>'Agent Fee Account', 'guard_name'=>'web'];
        DB::table('roles')->insert($data);
    }
}
