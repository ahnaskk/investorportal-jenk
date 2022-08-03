<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(Role::class);
        $this->call(InitiatorSeeder::class);
        $this->call(ModulesTableDataSeeder::class);
        $this->call(PermissionsTableDataSeeder::class);
        $this->call(CRMSeeder::class);
        $this->call(RcodeTableSeeder::class);
        $this->call(ActumDeclineCode::class);
        $this->call(View::class);
        $this->call(TemplateSeeder::class);  
        $this->call(MerchantSeeder::class);

            }
}
