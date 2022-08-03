<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;
use App\ParticipentPayment;
class AgentFee extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
    // $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
    // $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
    // $AgentFeeAccount = $AgentFeeAccount->pluck('users.id')->toArray();

    // $merchants = DB::table('merchants')->leftjoin('merchant_agent_account_history','merchant_agent_account_history.merchant_id','merchants.id')->select('merchants.id','merchant_agent_account_history.start_date','merchant_agent_account_history.end_date')->get();
    // $date_arr = array();
    // $i=0;
    // foreach($merchants as $data){
    
    
    // if($data->start_date==null){
    // $payments = ParticipentPayment::leftjoin('payment_investors','payment_investors.participent_payment_id','participent_payments.id')->where('participent_payments.merchant_id',$data->id)->orderBy('participent_payments.id','ASC')->groupBy('payment_investors.participent_payment_id')->select(DB::raw('sum(agent_fee) as agent_fee'),'payment_investors.created_at')->get()->toArray();
    // foreach($payments as $pay){
    // if($pay['agent_fee']!=0){
    // $i++;
    // $date_arr[$i]['merchant_id']=$data->id;
    // $date_arr[$i]['start_date']=date('Y-m-d H:i:s',strtotime($pay['created_at']));
    // $date_arr[$i]['end_date']= null;
    
    // }
    // if(isset($date_arr[$i]['start_date'])){
    // if($date_arr[$i]['start_date']!=null){
    // if($pay['agent_fee']==0 && $date_arr[$i]['merchant_id']==$data->id){
    // $date_arr[$i]['end_date']=date('Y-m-d H:i:s',strtotime($pay['created_at']));
    
    // }
    
    // }
    // }

    // }
    
    // }
    

    // }
    // if(count($date_arr)>0){
    // $insert = DB::table('merchant_agent_account_history')->insert($date_arr); 
    // }
    }
}
