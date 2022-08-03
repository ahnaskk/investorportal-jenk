<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

         // DB::statement('alter table merchants 
         //    DROP iso_name,
         //    DROP agent_name,
         //    DROP annual_revenue,
         //    DROP deal_type,
         //    DROP position,
         //    DROP withhold_percentage,
         //    DROP partner_credit_score,
         //    DROP owner_credit_score,
         //    DROP entity_type,
         //    DROP under_writer,
         //    DROP date_business_started,
         //    DROP monthly_revenue,
         //    DROP crm_id');


       
//        DB::statement('Update
//   merchants as m
//   LEFT JOIN (
//     select email,id
//     from users as u
//     where email is not NULL
//   ) as A on m.user_id = A.id
// set m.`notification_email` = A.email WHERE m.notification_email is null');



    }
}
