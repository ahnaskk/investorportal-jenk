<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\SubStatus;
use Illuminate\Support\Facades\DB;
use App\Module;
use App\User;
use App\ParticipentPayment;
use App\Settings;
use App\InvestorRoiRate;
class RoiRateSeeder extends Seeder
{
    public function run()
    {        
        $data=DB::table('users')->whereIn('investor_type',[1,3,4])->select('id','created_at','interest_rate')->get();
        //print_r($data->toArray());exit;
        $rate_arr = array();
        $i=0;
        if(count($data)>0){
        $details = $data->toArray();
            foreach($details as $dt){
               $new_date = DB::table('investor_transactions')->where('investor_id',$dt->id)->min('date');
                
                $rate_arr[$i]['user_id'] = $dt->id;
                $rate_arr[$i]['from_date'] = $new_date;
                $rate_arr[$i]['to_date'] = null;
                $rate_arr[$i]['roi_rate'] = $dt->interest_rate;
                $i++;

            }
            DB::table('investor_roi_rate')->insert($rate_arr);

        }
       


    }

}
