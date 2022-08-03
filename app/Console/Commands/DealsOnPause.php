<?php

namespace App\Console\Commands;

use App\CompanyAmount;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Merchant;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\PaymentInvestors;
use App\Settings;
use App\SubStatus;
use App\Template;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PayCalc;

class DealsOnPause extends Command
{
    protected $signature = 'DealsOnPause:DealsOnPause';
    protected $description = 'deals on pause';

    public function __construct(IMerchantRepository $merchant)
    {
        parent::__construct();
        $this->merchant = $merchant;
    }

    public function sortByOrder($a, $b)
    {
        return $a['delay'] - $b['delay'];
    }

    public function handle()
    {
        $emails = Settings::value('email');
        $email_id_arr = explode(',', $emails);
        $date = '';
        $update = [];
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        $merchants = Merchant::select('last_payment_date', 'merchants.id as merchant_id', 'factor_rate', 'merchants.name as merchant_name', 'lag_time', 'users.name as lender_name', 'company', 'sub_statuses.name as substatus_name', 'merchants.sub_status_id')
        ->where('merchants.active_status', 1)
        ->whereNOTIn('merchants.sub_status_id', [4, 11, 18, 19, 20, 22])
        ->join('users', 'users.id', 'merchants.lender_id')
        ->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
        ->where('merchants.complete_percentage', '<', 99)
        ->where('merchants.complete_percentage', '>', 0)
        ->where('merchants.payment_pause_id', '>', 0)
        ->whereNotIn('merchants.label', [3, 4, 5])
        ->orderByDesc('merchants.id')
        ->where(function ($query) {
            $query->whereRaw('date(last_payment_date) <  NOW()');
        })
        ->orderByDesc('merchants.id')->get()->toArray();
        $data_array = [];
        $i = 1;
        if (! empty($merchants)) {
            foreach ($merchants as $key=>$value) {
                //echo $value['merchant_name']."\n";
                $last_payment_date = ! empty($value['last_payment_date']) ? $value['last_payment_date'] : '';
                if ($last_payment_date) {
                    $date = date('Y-m-d', strtotime($last_payment_date));
                    $from = $date;
                    $to = date('Y-m-d');
                    $now = strtotime($to);
                    $current_date = strtotime($date);
                    $datediff = $now - $current_date;
                    $days = round($datediff / (60 * 60 * 24));

                    $delay = $days;

                    $companies = CompanyAmount::where('merchant_id', $value['merchant_id'])->join('users', 'users.id', 'company_amount.company_id')
                    //->whereNotNull('max_participant')
                    ->where('max_participant', '!=', 0)
                    ->pluck('users.name', 'users.id')->toArray();

                    if ($days > 0) {
                        $data_array[] = [
                            'merchant'=>$value['merchant_name'],
                            'merchant_id'=>$value['merchant_id'],
                            'lender_name'=>$value['lender_name'],
                            'company'=>$companies,
                            'delay'=>$delay,
                            'substatus'=>$value['substatus_name'],
                        ];
                    }
                }
            }
            usort($data_array, function ($a, $b) {
                return $b['delay'] <=> $a['delay'];
            });
            $html = '';
            if ($data_array) {
                $html .= '<table class="table" width="100%" cellpadding="0" cellspacing="0">
                <tbody><tr>
                <td style="background: #eae9f1;">SI</td>
                <td style="background: #eae9f1;">Merchant</td>
                <td style="background: #eae9f1;">Lender</td>                
                <td style="background: #eae9f1;">Delay</td>
                <td style="background: #eae9f1;">Merchant Status</td>
                </tr>';
                foreach ($data_array as $key=>$value1) {
                    $url = url('/admin/merchants/view/'.$value1['merchant_id']);
                    $html .= '<tr>
                    <td>'.$i.'</td>
                    <td><a href="'.$url.'" style="color:#2d3aab;">'.$value1['merchant'].'</a></td>
                    <td>'.$value1['lender_name'].'</td>';

                    $html .= '<td>'.$value1['delay'].'</td>
                    <td>'.$value1['substatus'].'</td>
                    </tr>';
                    $i++;
                }
                $html .= '</tbody></table>';
                // mail send option
                $message['title'] = 'Deals On Pause';
                $message['subject'] = 'Deals On Pause';
                $message['content'] = $html;
                $message['to_mail'] = $email_id_arr;
                $message['status'] = 'deals_on_pause';
                $message['unqID'] = unqID();
                try {
                    $email_template = Template::where([
                        ['temp_code', '=', 'DONP'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        if ($email_template->assignees) {
                            $template_assignee = explode(',', $email_template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                $role_mails = array_diff($role_mails, $email_id_arr);
                                $bcc_mails[] = $role_mails;    
                            }
                            $message['bcc'] = Arr::flatten($bcc_mails);
                        }
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['bcc'] = [];
                        $message['to_mail'] = $admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
        echo ' merchants deals on pause mail sent sucessfully';
    }
}
