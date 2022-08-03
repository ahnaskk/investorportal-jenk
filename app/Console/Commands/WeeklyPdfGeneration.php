<?php

namespace App\Console\Commands;

use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Settings;
use App\User;
use Illuminate\Console\Command;

class WeeklyPdfGeneration extends Command
{
    protected $signature = 'WeeklyPDFGeneration:weeklypdfgeneration';
    protected $description = 'Weekly PDF Generation';
    protected $role;
    protected $user;

    public function __construct(IRoleRepository $role, IUserRepository $user)
    {
        parent::__construct();
        $this->role = $role;
        $this->user = $user;
    }

    public function handle()
    {
        $investor_arr = array();
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $investors = $investors->where('user_has_roles.role_id', User::INVESTOR_ROLE);
        $investors = $investors->select('users.*')->get()->toArray();
        foreach($investors as $inv_arr){
            $recurrence = $inv_arr['notification_recurence'];
            $date_end = "";
            $date_start = "";
            $notificn_recurrence = "";
            switch ($recurrence) {
                case '3':
                    $notificn_recurrence = 1;
                    $date = date('Y-m-d');
                    $last_day = date('Y-m-d');
                    $date_end = date('Y-m-d');
                    $date_start = date('Y-m-d');
                    if(! empty($inv_arr) && $notificn_recurrence!=""){                        
                        $date_start = ET_To_UTC_Time($date_start.' 00:00:00', 'datetime');                        
                        $date_end = ET_To_UTC_Time($date_end.' 18:00:00', 'datetime'); 
                        $filters = [
                            'date_start'    => $date_start,
                            'date_end'      => $date_end,
                            'from'          => 'pdfGeneration',
                            'send_mail'     => false,
                            'merchants'     => '',
                            'recurrence'    => $notificn_recurrence,
                            'hide'          => 0,
                            'generationtype'=> 0,
                            'creator_id'    => 783,
                            
                        ];
                        
                        $arr[0]=$inv_arr;
                        $result = $this->user->generatePDFCSV($arr, $filters);
                        echo strip_tags($result);echo "\n <br>";
                           
                    }
                    break;
                case '1':
                    if(date('l')=="Saturday"){
                        $date_start = date('Y-m-d', strtotime('last monday'));
                        $date_end = date('Y-m-d', strtotime($date_start .' +5 day'));                        
                        $notificn_recurrence = 2;  
                        if(! empty($inv_arr) && $notificn_recurrence!=""){
                            $date_start = ET_To_UTC_Time($date_start.' 00:00:00', 'datetime');                        
                            $date_end = ET_To_UTC_Time($date_end.' 18:00:00', 'datetime'); 
                            $filters = [
                                'date_start'    => $date_start,
                                'date_end'      => $date_end,
                                'from'          => 'pdfGeneration',
                                'send_mail'     => false,
                                'merchants'     => '',
                                'recurrence'    => $notificn_recurrence,
                                'hide'          => 0,
                                'generationtype'=> 0,
                                'creator_id'    => 783,
                                
                            ];                            
                            $arr[0]=$inv_arr;
                            $result = $this->user->generatePDFCSV($arr, $filters);
                            echo strip_tags($result);echo "\n <br>";
                        }
                         
                        }
                    break;
                
                    default:                    
                    break;
            }     
       
        }
       
          
       
        
    }
}

