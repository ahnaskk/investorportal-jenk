<?php

namespace App\Console\Commands;

use App\Exports\Data_arrExport;
use App\Label;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\MerchantUser;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IRoleRepository $role)
    {
        parent::__construct();
        $this->role = $role;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date_end = date('Y-m-d', strtotime('last saturday')); // weekly
        $date_start = date('Y-m-d', strtotime('last saturday', strtotime('last saturday')));

        $labels = Label::where('flag', 1)->pluck('id')->toArray(); // insurance labels
        $investor_label = implode(',', $labels);


        $companies = $this->role->allCompanies()->pluck('id')->toArray();   // companies

        $excel_array = [];
        $i = 0;

        if (! empty($companies)) {
            foreach ($companies as $key => $value) {
                $company_name = User::where('id', $value)->value('name');

                $excel_array[$i][] = $company_name;
                $excel_array[$i.'+1'] = ['No', 'Investor', 'Payment', 'Investment'];
                $j = 1;
                $p = 0;

                $insurance_investors = DB::table('users')->whereRaw('json_contains(label, \'['.$investor_label.']\')')
            ->where('company', $value)
            ->pluck('id')->toArray();

                $users = [];

                if (! empty($insurance_investors)) {
                    foreach ($insurance_investors as $key1 => $value1) {
                        foreach ($labels as $key2 => $value2) {
                            $payments = DB::table('payment_investors')
            ->where('merchants.label', $value2)
            ->where('participent_payments.payment_date', '>=', $date_start)
            ->where('participent_payments.payment_date', '<=', $date_end)
            ->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id')
            ->join('merchants', 'participent_payments.merchant_id', 'merchants.id')
            ->join('users', 'payment_investors.user_id', 'users.id')
            ->join('user_has_roles', 'user_has_roles.model_id', 'users.id')
            ->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)
            ->where('payment_investors.user_id', $value1)
            ->groupBy('users.id')->orderByDesc('users.id')->pluck(DB::raw('sum(actual_participant_share-mgmnt_fee) as net_amount'), 'users.id')->toArray();

                            print_r($payments);

                            // foreach ($payments as $key3 => $value3) {

            //     if($value2==4)
            //     {
            //          $users[$key1][$key2]['ins1_payment']=$value3;

            //     }else if($value2==5)
            //     {
            //          $users[$key1][$key2]['ins2_payment']=$value3;

            //     }

            // }
                        }
                    }
                }

                //  print_r($insurance_investors);

            // $insurance_investors = DB::table('users')->whereRaw('JSON_CONTAINS(label,"'.$value2.'")')
            // ->where('active_status', 1)
            // ->groupBy('users.id')
            // ->pluck('users.id')->toArray();



            // $test[$p]['payment']

            //payments

            // foreach ($payments as $key1 => $value1) {

            //          $investor_name=User::where('id',$key1)->value('name');
            //          $excel_array[$i.'+2']['No'] = $j;
            //          $excel_array[$i.'+2']['Investor'] = $investor_name;
            //          $excel_array[$i.'+2']['Payment'] = $value1;
            //          $excel_array[$i.'+2']['Investment'] = isset($investments[$key1])?$investments[$key1]:'0.00';

            //          $i++;
            //          $j++;

            //      }

          //  }
            }

            print_r($users);

           //  $investments=DB::table('merchant_user')
           // ->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
           // ->where('merchant_user.created_at', '>=', $date_start)
           // ->where('merchant_user.created_at', '<=', $date_end)
           // ->whereIn('merchants.label', $labels)
           // ->join('users','merchant_user.user_id', 'users.id')
           //  ->join('user_has_roles', 'user_has_roles.model_id', 'users.id')
           //  ->where('user_has_roles.role_id','!=', User::OVERPAYMENT_ROLE)
           //  ->where('users.company',$value)
           //  ->orderBy('users.id','desc')
           // ->groupBy('users.id')
           // ->pluck(DB::raw('sum(amount) as amount'),'users.id')->toArray(); // investment

        }

        // $fileCSVName = 'test'.'.xlsx';
            // $export = new Data_arrExport($excel_array);
            // $fp = Excel::store($export, $fileCSVName, 'public');
            // $s3_file = Storage::disk('public')->get($fileCSVName);
            // $s3 = Storage::disk('s3');
            // $s3->put($fileCSVName, $s3_file, 'public');
            // echo $fileCSVUrl = asset(\Storage::disk('s3')->url($fileCSVName));

        //return 0;
    }
}
