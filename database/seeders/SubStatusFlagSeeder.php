<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubStatusFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $flags = [
        'Collections',
        'Bankruptcy',
        'Hardship',
        'Uncollectable',
        'Payment Modified',
        'Escalated to Legal',
        'DNC',
        'Settled for less' ];

       DB::table('sub_status_flags')->truncate();
        $data = [];
        foreach ($flags as $value) {
            $data[] = ['name'=> $value, 'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')];
        }
        DB::table('sub_status_flags')->insert($data);

    }
}
