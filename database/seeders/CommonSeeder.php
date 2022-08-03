<?php
/**
* Created By Reshma
* Last executed Date : 18-05-2022
* added In sprint ID : 6
* querry Added By Rahees,
*/
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\SubStatus;
use Illuminate\Support\Facades\DB;
use App\Module;
use App\User;
use App\ParticipentPayment;
use App\Settings;

class CommonSeeder extends Seeder
{
    public function run()
    {   $id_exist = DB::table('settings')->where('id',18)->first();
        if(!$id_exist){
        DB::statement("INSERT INTO `settings` (`id`, `keys`, `values`, `email`, `default_payment`, `forceopay`, `hide`, `max_assign_per`, `rate`, `agent_fee_per`, `last_mob_notification_time`, `portfolio_start_date`, `send_permission`, `historic_status`, `created_at`, `updated_at`, `show_agent_account`, `show_overpayment_account`, `edit_investment_after_payment`) VALUES ('18', 'deduct_agent_fee_from_profit_only', '0', NULL, '0', NULL, '0', '100.00', '0.00', '0.00', NULL, NULL, '0', '0', NULL, NULL, '1', '1', '0')");
        echo "\n settings updated";
        }
        // DB::statement("UPDATE `participent_payments` SET `created_at` = '2022-04-15 21:57:35',`updated_at` = '2022-05-28 15:12:59' WHERE `participent_payments`.`merchants_id` = 9950 AND `participent_payments`.`reason` 'LIKE' '%Changed to Default%'");
        // DB::statement("UPDATE `participent_payments` SET `created_at` = '2022-04-15 21:57:35',`updated_at` = '2022-05-28 15:12:59' WHERE `participent_payments`.`merchants_id` = 9540 AND `participent_payments`.`reason` 'LIKE' '%Changed to Default%'");

        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` = '2022-07-08 17:19:28' WHERE `participent_payments`.`id` = 812447");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812429");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812486");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '452',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812481");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812446");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812480");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812479");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812478");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812476");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812477");
       
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812485");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-04 15:30:03' WHERE `participent_payments`.`id` = 812445");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812475");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812474");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-04 15:30:02' WHERE `participent_payments`.`id` = 812444");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812473");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812483");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812472");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812471");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812470");

        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812469");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '452',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812468");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812467");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812466");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812465");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812464");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812463");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812462");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812461");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812460");

        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812484");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '513',`updated_at` ='2022-04-16 00:11:27' WHERE `participent_payments`.`id` = 812482");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812459");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812458");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-04-29 17:00:02' WHERE `participent_payments`.`id` = 812457");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812456");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812455");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812454");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812453");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812442");

        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812452");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812451");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 808010");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` =812443 ");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-06 17:00:02' WHERE `participent_payments`.`id` = 812437");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812449");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812450");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-05-30 17:00:02' WHERE `participent_payments`.`id` = 812448");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-04-29 17:00:02' WHERE `participent_payments`.`id` = 812438");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-06-14 14:24:52' WHERE `participent_payments`.`id` = 812441");

        DB::statement("UPDATE `participent_payments` SET `creator_id` = '1',`updated_at` ='2022-04-25 17:00:01' WHERE `participent_payments`.`id` = 812439");
        DB::statement("UPDATE `participent_payments` SET `creator_id` = '441',`updated_at` ='2022-05-27 15:12:59' WHERE `participent_payments`.`id` = 812440");
        
        echo "\n creator id updated";

        $participant_payment_id_arr = [812447,812429,812486,812481,812446,812480,812479,812478,812476,812477,812485,812445,812475,812474,812444,812473,812483,812472,812471,812470,812469,812468,812467,812466,812465,812464,812463,812462,812461,812460,812484,812482,812459,812458,812457,812456,812455,812454,812453,812442,812452,812451,808010,812443,812437,812449,812450,812448,812438,812441,812439,812440];
        $payments = DB::table('participent_payments')->whereIn('id',$participant_payment_id_arr)->get();
        foreach($payments as $payment_arr){
            $update = DB::table('payment_investors')->where('participent_payment_id', $payment_arr->id)->update(array('updated_at' => $payment_arr->updated_at));
        }

        echo "\n updated_at updated";
             
        DB::statement("UPDATE `roles` SET `name` = 'branch manager', `created_at` = NULL, `updated_at` = NULL WHERE `roles`.`id` = 3");
        DB::statement("UPDATE `roles` SET `name` = 'collection user', `created_at` = NULL, `updated_at` = NULL WHERE `roles`.`id` = 9");
        DB::statement("UPDATE `roles` SET `name` = 'editor with creditcard access', `created_at` = NULL, `updated_at` = NULL WHERE `roles`.`id` = 12");
        echo "\n role name updated";


        $participant_id_arr = [811935,811892,812146,812147];
        $payment_data = DB::table('participent_payments')->whereIn('id',$participant_id_arr)->get();
        foreach($payment_data as $payment){
            $update = DB::table('payment_investors')->where('participent_payment_id', $payment->id)->update(array('created_at' => $payment->created_at,'updated_at'=>$payment->updated_at));
            echo "\n date changed for participant id ".$payment->id;
        }

    }
}



