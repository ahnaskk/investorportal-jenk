<?php

namespace App\Console\Commands;

use App\Exports\Data_arrExport;
use App\Jobs\CommonJobs;
use App\Label;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\MerchantUser;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Settings;
use Carbon\Carbon;

class RollINSPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RollINSPayments:rollinspayemnts';

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
        echo $date_end = date('Y-m-d', strtotime('last saturday')); // weekly
        echo 'hh';

        echo $date_start = date('Y-m-d', strtotime('last saturday +1 day', strtotime('last saturday')));
        dd();

        $labels = Label::where('flag', 1)->pluck('id')->toArray(); // insurance labels
        $investor_label = implode(',', $labels);
        $message = [];
        $msg = '';
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';

        //print_r($investor_label);

        // print_r($users);

        // print_r($insurance_investors);

        $companies = $this->role->allCompanies()->pluck('id')->toArray();   // companies

        //print_r($companies);

        $excel_array = [];
        $i = 0;

        $k = 2;

        if (! empty($companies)) {
            foreach ($companies as $key => $value) {
                $users = [];

                $company_name = User::where('id', $value)->value('name');

                $excel_array[$i] = [$company_name, date('m/d/Y', strtotime($date_start)).'-'.date('m/d/Y', strtotime($date_end))];
                $excel_array[$i.'+1'] = ['No', 'Investor', 'Insurnace1 Payment', 'Insurance 1 Investment', 'Insurnace2 Payment', 'Insurance 2 Investment'];

                // foreach ($labels as $key6 => $value6) {

                //   $label_name=Label::where('id',$value6)->value('name');

                //    $excel_array[$i.'+1'][$value6][$k] = $label_name. 'Payment';
                //    $excel_array[$i.'+1'][$value6][$k] = $label_name. 'Investment';

                //    $k++;
                // }

                //  print_r($excel_array);


                $j = 1;
                $p = 0;

                $insurance_investors = DB::table('users')->whereRaw('json_contains(label, \'['.$investor_label.']\')')
           // ->where('active_status',1)
            ->where('company', $value)
            ->pluck('id')->toArray();

                if (! empty($insurance_investors)) {
                    foreach ($insurance_investors as $key1 => $value1) {
                        foreach ($labels as $key2 => $value2) {

                       // echo "------------------>start<---------------";

                            $payments = DB::table('payment_investors')
            ->where('merchants.label', $value2)
            ->where('participent_payments.payment_date', '>=', $date_start)
            ->where('participent_payments.payment_date', '<=', $date_end)
            ->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id')
            ->join('merchants', 'participent_payments.merchant_id', 'merchants.id')
            ->join('users', 'payment_investors.user_id', 'users.id')
            ->join('user_has_roles', 'user_has_roles.model_id', 'users.id')
            ->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)
            ->where('user_has_roles.role_id', '!=', User::AGENT_FEE_ROLE)
            ->where('payment_investors.user_id', $value1)
            ->where('users.company', $value)
            ->groupBy('users.id')->orderByDesc('users.id')
            ->pluck(DB::raw('sum(actual_participant_share-mgmnt_fee) as net_amount'), 'users.id')->toArray();

                            // print_r($payments);

                            $investments = DB::table('merchant_user')
           ->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
           ->where('merchants.date_funded', '>=', $date_start)
           ->where('merchants.date_funded', '<=', $date_end)
           ->where('merchants.label', $value2)
           ->join('users', 'merchant_user.user_id', 'users.id')
           ->where('users.company', $value)
           ->where('merchant_user.user_id', $value1)
            ->join('user_has_roles', 'user_has_roles.model_id', 'users.id')
            ->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)
            ->where('user_has_roles.role_id', '!=', User::AGENT_FEE_ROLE)
            ->orderByDesc('users.id')
           ->groupBy('users.id')
           ->pluck(DB::raw('sum(pre_paid+amount+commission_amount+under_writing_fee) as amount'), 'users.id')->toArray(); // investment

                            $label_name = Label::where('id', $value2)->value('name');

                            // print_r($payments);

                            foreach ($payments as $key3 => $value3) {
                                $users[$key3][$label_name.' payment'] = $value3;
                                $users[$key3][$label_name.' investment'] = isset($investments[$key3]) ? $investments[$key3] : '0.00';
                            }

                            // echo "------------------>start<---------------";
                        }
                    }
                }


                if ($users) {
                    foreach ($users as $key4 => $value4) {
                        $investor_name = User::where('id', $key4)->value('name');
                        $excel_array[$i.'+2']['No'] = $j;
                        $excel_array[$i.'+2']['Investor'] = $investor_name;

                        foreach ($value4 as $key5 => $value5) {
                            $excel_array[$i.'+2'][$key5] = isset($value5) ? $value5 : '0.0';
                        }

                        $i++;
                        $j++;
                    }
                }
            }


        }

        $fileCSVName = 'roll_ins_payment_report_'.time().'.xlsx';
        $export = new Data_arrExport($excel_array);
        $fp = Excel::store($export, $fileCSVName, 'public');
        $s3_file = Storage::disk('public')->get($fileCSVName);
        $s3 = Storage::disk('s3');
        $s3->put($fileCSVName, $s3_file, config('filesystems.disks.s3.privacy'));
        $fileCSVUrl = asset(\Storage::disk('s3')->temporaryUrl($fileCSVName,Carbon::now()->addMinutes(2)));

        $message['title'] = 'Roll INS Payments Report';
        $message['content'] = 'Roll INS Payments Report';
        $message['to_mail'] = $admin_email;
        $message['attach'] = $fileCSVUrl;
        $message['status'] = 'roll_mail';
        $message['heading'] = 'Roll INS Payments Report';
        $message['subject'] = 'Roll INS Payments Report from '.$date_start.'to'.$date_end;
        $message['unqID'] = unqID();
        $emailJob = (new CommonJobs($message));
        dispatch($emailJob);

        if ($fileCSVUrl) {
            echo $msg .= 'Report Generated and Mail Sent Successfully till '.$date_end.' url ->'.$fileCSVUrl;
        }

        //return 0;
    }
}
