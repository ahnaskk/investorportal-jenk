<?php

namespace App\Console\Commands;

use App\Exports\Data_arrExport;
use App\Jobs\CommonJobs;
use App\Merchant;
use App\MerchantUser;
use App\ParticipentPayment;
use App\Settings;
use App\Template;
use App\TermPaymentDate;
use Carbon\Carbon;
use FFM;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AchDifferenceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:difference';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merchants with difference between ACH balance and actual balance';

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
        // echo "\n started \n";

        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $sub_statuses = DB::table('sub_statuses')->pluck('name', 'id')->toArray();
        $labels = DB::table('label')->pluck('name', 'id')->toArray();

        $timestamp = Carbon::now();
        $checked_time = $timestamp->toDateTimeString();
        $today = $timestamp->toDateString();

        echo "\nChecking time is $checked_time \n";

        $merchants = Merchant::whereNotIn('sub_status_id', $unwanted_sub_status)
            ->where('label', 1)//MCA label
            ->where('ach_pull', 1)//ACH pull selected
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('merchant_user')
                      ->whereColumn('merchant_user.merchant_id', 'merchants.id');
            })//check if investment
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('merchant_bank_accounts')
                      ->whereColumn('merchant_bank_accounts.merchant_id', 'merchants.id');
            })//Check it has bank account
            ->select('id', 'name', 'sub_status_id', 'ach_pull', 'complete_percentage', 'label', 'rtr')
            ->get();

        $data = [];

        foreach ($merchants as $merchant) {
            $mid = $merchant->id;
            $payment_total = ParticipentPayment::where('merchant_id', $mid)->where('is_payment', 1)->sum('payment');
            $balance_our_portion = $merchant->rtr - $payment_total;

            $future_payment_total = Merchant::find($mid)->termPayments()->where('payment_date', '>', $today)->where('status', 0)->sum('payment_amount');
            $processing_payment_total = Merchant::find($mid)->termPayments()->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');

            $anticipated_total = $future_payment_total + $processing_payment_total;
            $status = 0;
            $difference = $anticipated_total - $balance_our_portion;
            if ($balance_our_portion + 1 < $anticipated_total) {
                $status = 2;
                $type = 'ACH balance overage';
            } elseif ($balance_our_portion > $anticipated_total + 1) {
                $status = 1;
                $type = 'ACH balance deficit';
            }

            if ($status) {
                $data[] = [
                    'merchant_id' => $mid,
                    'merchant_name' => $merchant->name,
                    'sub_status' => $sub_statuses[$merchant->sub_status_id],
                    'balance' => FFM::dollar($balance_our_portion),
                    'anticipated_total' => FFM::dollar($anticipated_total),
                    'difference' => FFM::dollar($difference),
                    'status' => $status,
                    'type' => $type,
                    'complete_percentage' => FFM::percent($merchant->complete_percentage),
                    'label' => $labels[$merchant->label],
                ];
            }
        }
        if (count($data)) {
            $title = 'Merchants with difference between ACH balance and actual balance';
            echo "\n Sending mail for $title \n";

            $titles = ['No', 'Merchant ID', 'Merchant Name', 'Sub Status', 'Type', 'Balance', 'Anticipated Amount', 'Difference', 'Complete Percentage', 'Label'];
            $values = ['', 'merchant_id', 'merchant_name', 'sub_status', 'type', 'balance', 'anticipated_total', 'difference', 'complete_percentage', 'label'];
            $this->sendMail($data, $titles, $values, $title);
        } else {
            echo "\n No data. \n";
        }

        echo "\n Ended \n";

        return true;
    }

    /**
     * Execute Mail Function.
     *
     * @return int
     */
    public function sendMail($data, $titles, $values, $title)
    {
        $message['title'] = $title;
        $msg['title'] = $message['title'];
        $msg['status'] = 'merchant_unit_test';
        $msg['subject'] = $message['title'];
        $message['content']['date'] = FFM::date(Carbon::now()->toDateString());
        $message['content']['date_time'] = FFM::datetime(Carbon::now()->toDateTimeString());
        $emails = Settings::value('email');
        $emailArray = explode(',', $emails);
        $msg['to_mail'] = $emailArray;
        $msg['date_time'] = FFM::datetime(Carbon::now()->toDateTimeString());
        $msg['template_type'] = 'ach_difference';

        if (count($data)) {
            $message['content']['type'] = $title;
            $message['content']['count'] = count($data);
            $msg['count'] = count($data);
            $msg['unqID'] = unqID();
            $msg['content'] = $message['content'];
            $exportCSV = $this->generateCSV($data, $titles, $values);
            $fileName = $message['content']['type'].'.csv';
            $msg['atatchment_name'] = $fileName;
            $msg['atatchment'] = $exportCSV;

            try {
                $email_template = Template::where([
                    ['temp_code', '=', 'MACHD'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $emailArray);
                            $bcc_mails[] = $role_mails;
                        }
                        $msg['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $msg['bcc'] = [];
                    $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
                    $msg['to_mail'] = $admin_email;
                    $emailJob = (new CommonJobs($msg));
                    dispatch($emailJob);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    public function generateCSV($data, $titles, $values)
    {
        $excel_array[] = $titles;
        $i = 1;

        foreach ($data as $key => $tr) {
            $excel_array[$i][$titles[0]] = $i;
            $excel_array[$i][$titles[1]] = $tr[$values[1]];
            $excel_array[$i][$titles[2]] = $tr[$values[2]];
            $excel_array[$i][$titles[3]] = $tr[$values[3]];
            $excel_array[$i][$titles[4]] = $tr[$values[4]];
            $excel_array[$i][$titles[5]] = $tr[$values[5]];
            if (isset($titles[6])) {
                $excel_array[$i][$titles[6]] = $tr[$values[6]];
            }
            if (isset($titles[7])) {
                $excel_array[$i][$titles[7]] = $tr[$values[7]];
            }
            if (isset($titles[8])) {
                $excel_array[$i][$titles[8]] = $tr[$values[8]];
            }
            if (isset($titles[9])) {
                $excel_array[$i][$titles[9]] = $tr[$values[9]];
            }
            $i++;
        }

        $export = new Data_arrExport($excel_array);

        return $export;
    }
}
