<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesTableDataSeeder extends Seeder
{
    public function run()
    {
        echo "\n module create start";    

         $lists = [
          'FAQ',
          'Reconciliation',
          'MailBox'
   
         ];

        $data = [];
        foreach ($lists as $value) {
            $data[] = ['name'=> $value, 'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')];
        }
        DB::table('modules')->insert($data);

       
    }
}

