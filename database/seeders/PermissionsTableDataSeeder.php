<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsTableDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->whereNotNull('id')->delete();
        $data = ['View', 'Edit', 'Create', 'Delete', 'Download'];
        for ($item = 0; $item < count($data); $item++) {
            foreach ($data as $d) {
                DB::table('permissions')->insert(['id'=>$item + 1, 'name'=>$data[$item], 'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')]);
                $item += 1;
            }
        }
    }
}
