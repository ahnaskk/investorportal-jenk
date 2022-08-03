<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\ApiLog;
use App\Jobs\CommonJobs;
use FFM;
use App\Settings;


class NotifyApiErrorResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:notifyApiErrorResponse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify API Error Reponse';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {    
         $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
         $log_errors= ApiLog::whereIn('api_name',['/api/getMerchantDetails','/api/merchant_create','/api/merchant_update','/api/merchant_bank_account_update','/api/merchant_bank_account_create','/api/merchant_bank_account_delete','/api/update_CRMID','/api/merchantPaymentDetails','/api/add_merchant_notes','/api/get_participants','/api/map_participants','/api/update_investor','/api/create_investor','/api/assign_participants'])->where('mail_status',0);
             
           $log_normal_errors=clone $log_errors; 
           $log_errors=$log_errors->where(function($q){
                 $q->where(DB::raw('JSON_VALID(response)'),1);
                 $q->where(DB::raw('JSON_UNQUOTE(response-> "$.status")'),"0");
                 $q->orWhere('method',"GET");
                })

           ->get()->toArray();
           
           $log_normal_errors=$log_normal_errors->where('response','like','%ErrorException%')
                   ->get()->toArray();
           $log_normal_errors = array_merge($log_normal_errors,$log_errors);


          $i=0;           

        if(!empty($log_normal_errors))
         {

               foreach($log_normal_errors as $key=>$value)
                {
                      try {
                        $msg['title'] = 'Api Error Reponse';
                        $msg['subject'] = 'Notify Api Error Reponse';
                        $msg['content'] =['api_name'=>$value['api_name'],'method'=>$value['method'],'request'=>$value['request'],'response'=>$value['response'],'created_at'=>date('m-d-Y H:i:s',strtotime($value['date']))];
                        $msg['status'] = 'api_error_response';
                        $msg['unqID'] = unqID();
                        $msg['to_mail'] = $admin_email;
                        $emailJob = (new CommonJobs($msg));
                        dispatch($emailJob);
                        ApiLog::where('id',$value['id'])->update(['mail_status'=>1]); 

                      $i++; 

                    }
                    catch (\Exception $e) {
                                echo $e->getMessage();
                    }
               }

               echo 'Notifed '.$i." api error response from crm and sent mail successfully !!";
           
         }else
         {

             echo "No api error response";
         }
                 
            


        return 0;
    }
}
