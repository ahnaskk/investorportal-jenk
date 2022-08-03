<?php

namespace Database\Seeders;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CRMSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $crm_user = Role::whereName('crm')->first()->users->count();
        if(!$crm_user)
        {
               $data = [
                 'email' => 'crm@mail.com',
                 'name' => 'crm_user',
                 'password' => '12345678',
                 'creator_id' => 1,
                 'created_at' => Carbon::now('UTC')->toDateString(),
                 'updated_at' => Carbon::now('UTC')->toDateString()
             ];

          $user = User::create($data);
          $user->assignRole('crm');

        }
      
      DB::statement("UPDATE `api_log` SET `mail_status`=1 where mail_status=0");

        
    }
}
