<?php

namespace App\Console\Commands;

use App\CompanyAmount;
use App\Exports\Data_arrExport;
use App\Jobs\CommonJobs;
use App\Merchant;
use App\MerchantUser;
use App\ParticipentPayment;
use App\PaymentInvestors;
use Carbon\Carbon;
use FFM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Settings;

class MerchantUnitTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merchant:unittest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Merchant's Unit Test";

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
        $sub_statuses = DB::table('sub_statuses')->pluck('name', 'id')->toArray();

        //Check all defaulted merchant's CTD is lessthan invested amount.
        $default_status = [4, 22];
        $default_merchants = Merchant::select('id', 'name', 'funded', 'sub_status_id', 'complete_percentage')->whereIn('sub_status_id', $default_status)->get();
        $default_error_data = [];
        // echo "\n started \n";
        if ($default_merchants) {
            foreach ($default_merchants as $mer) {
                $merchant_id = $mer->id;
                $ctd = ParticipentPayment::where('model','App\ParticipentPayment')->where('merchant_id', $merchant_id)->sum('final_participant_share');
                $invested_amount = MerchantUser::where('merchant_id', $merchant_id)->sum(DB::raw('amount + commission_amount + under_writing_fee + pre_paid')); //pre_paid+commission_amount+under_writing_fee+amount refer investment report
                if ($ctd >= $invested_amount) {
                    $default_error_data[] = [
                        'merchant_id' => $merchant_id,
                        'merchant_name' => $mer->name,
                        'sub_status' => $sub_statuses[$mer->sub_status_id],
                        'ctd' => FFM::sr($ctd),
                        'invested_amount' => FFM::sr($invested_amount),
                        'complete_percentage' => FFM::percent($mer->complete_percentage),
                    ];
                }
            }
        }

        if (count($default_error_data)) {
            $title = 'Default merchants with CTD greater than Invested Amount';
            echo "\n Sending mail for $title \n";

            $titles = ['No', 'Merchant Name', 'Merchant ID', 'Sub Status', 'CTD', 'Invested Amount', 'Complete Percentage'];
            $values = ['', 'merchant_name', 'merchant_id', 'sub_status', 'ctd', 'invested_amount', 'complete_percentage'];
            $this->sendMail($default_error_data, $titles, $values, $title);
        }

        //invested amount lessthan or equal to funded amount.
        $invest_error_data = [];
        $all_merchants = Merchant::select('id', 'name', 'funded', 'max_participant_fund', 'sub_status_id', 'complete_percentage')
            // ->whereNotIn('sub_status_id', $default_status)
            ->get();
        if ($all_merchants) {
            foreach ($all_merchants as $mer) {
                $merchant_id = $mer->id;
                $invested_amount = MerchantUser::where('merchant_id', $merchant_id)->sum('amount');
                $funded_amount = $mer->max_participant_fund;
                if ($funded_amount + 1 < $invested_amount) {
                    $invest_error_data[] = [
                        'merchant_id' => $merchant_id,
                        'merchant_name' => $mer->name,
                        'sub_status' => $sub_statuses[$mer->sub_status_id],
                        'funded_amount' => FFM::sr($funded_amount),
                        'invested_amount' => FFM::sr($invested_amount),
                        'complete_percentage' => FFM::percent($mer->complete_percentage),
                        'type' => 'Total issue',
                    ];
                } else {
                    $comapny_invest_issue = false;
                    $companyAmounts = CompanyAmount::where('merchant_id', $merchant_id)
                    ->select('id', 'max_participant', 'company_id')->get();
                    foreach ($companyAmounts as $c) {
                        $c_funded_amount = $c->max_participant;

                        $c_invested_amount = MerchantUser::where('merchant_id', $merchant_id)
                        ->leftJoin('users', 'users.id', 'merchant_user.user_id')
                        ->where('users.company', $c->company_id)->sum('merchant_user.amount');
                        if ($c_funded_amount + 1 < $c_invested_amount) {
                            $comapny_invest_issue = true;
                        }
                    }
                    if ($comapny_invest_issue) {
                        $invest_error_data[] = [
                            'merchant_id' => $merchant_id,
                            'merchant_name' => $mer->name,
                            'sub_status' => $sub_statuses[$mer->sub_status_id],
                            'funded_amount' => FFM::sr($funded_amount),
                            'invested_amount' => FFM::sr($invested_amount),
                            'complete_percentage' => FFM::percent($mer->complete_percentage),
                            'type' => 'Company issue',
                        ];
                    }
                }
            }
        }

        if (count($invest_error_data)) {
            $title = 'Merchants with Invested Amount greater than Funded Amount';
            echo "\n Sending mail for $title \n";

            $titles = ['No', 'Merchant Name', 'Merchant ID', 'Sub Status', 'Invested Amount', 'Funded Amount', 'Complete Percentage', 'Type'];
            $values = ['', 'merchant_name', 'merchant_id', 'sub_status', 'invested_amount', 'funded_amount', 'complete_percentage', 'type'];

            $this->sendMail($invest_error_data, $titles, $values, $title);
        }

        //Check all settled merchant's RTR greater than CTD is lessthan invested amount.
        $settled_status = [18, 19, 20];
        $settled_merchants = Merchant::select('id', 'name', 'sub_status_id', 'last_payment_date', 'lender_id', 'complete_percentage')->whereNotIn('sub_status_id', $settled_status)->get();
        $settled_error_data = [];
        // echo "\n started \n";
        if ($settled_merchants) {
            foreach ($settled_merchants as $mer) {
                $merchant_id = $mer->id;
                $lag_days = $mer->lendor->lag_time ?? 0;
                $condition = $lag_days + 30;
                $last_payment_date = Carbon::parse($mer->last_payment_date);
                $difference = $last_payment_date->diffInDays(Carbon::now());
                if ($difference > $condition) {
                    $ctd = ParticipentPayment::where('model','App\ParticipentPayment')->where('merchant_id', $merchant_id)->sum('final_participant_share');
                    $invested_amount_with_fees = MerchantUser::where('merchant_id', $merchant_id)->sum(DB::raw('amount + commission_amount + under_writing_fee + pre_paid'));
                    $invested_rtr = MerchantUser::where('merchant_id', $merchant_id)->sum(DB::raw('invest_rtr - paid_mgmnt_fee'));
                    if ($invested_amount_with_fees + 1 < $ctd && $ctd + 1 < $invested_rtr) {
                        $settled_error_data[] = [
                            'merchant_id' => $merchant_id,
                            'merchant_name' => $mer->name,
                            'sub_status' => $sub_statuses[$mer->sub_status_id],
                            'ctd' => FFM::sr($ctd),
                            'invested_amount' => FFM::sr($invested_amount_with_fees),
                            'invested_rtr' => FFM::sr($invested_rtr),
                            'complete_percentage' => FFM::percent($mer->complete_percentage),
                        ];
                    }
                }
            }
        }

        if (count($settled_error_data)) {
            $title = 'Non Settled merchants with RTR greater than CTD greater than Invested Amount';
            echo "\n Sending mail for $title \n";

            $titles = ['No', 'Merchant Name', 'Merchant ID', 'Sub Status', 'Invested Amount', 'CTD', 'Invest RTR', 'Complete Percentage'];
            $values = ['', 'merchant_name', 'merchant_id', 'sub_status', 'invested_amount', 'ctd', 'invested_rtr', 'complete_percentage'];
            $this->sendMail($settled_error_data, $titles, $values, $title);
        }

        //Check all active merchant's Payment amount greater than RTR.
        $active_status = [1];
        $active_merchants = Merchant::select('id', 'name', 'rtr', 'sub_status_id', 'complete_percentage')->whereIn('sub_status_id', $active_status)->get();
        $active_error_data = [];
        // echo "\n started \n";
        if ($active_merchants) {
            foreach ($active_merchants as $mer) {
                $merchant_id = $mer->id;
                $payment_total = ParticipentPayment::where('model','App\ParticipentPayment')->where('merchant_id', $merchant_id)->sum('payment');
                $rtr = $mer->rtr;
                if ($payment_total > $rtr + 1) {
                    $active_error_data[] = [
                        'merchant_id' => $merchant_id,
                        'merchant_name' => $mer->name,
                        'sub_status' => $sub_statuses[$mer->sub_status_id],
                        'payment_amount' => FFM::sr($payment_total),
                        'rtr' => FFM::sr($rtr),
                        'complete_percentage' => FFM::percent($mer->complete_percentage),
                    ];
                }
            }
        }

        if (count($active_error_data)) {
            $title = 'Active merchants with Payment amount greater than RTR';
            echo "\n Sending mail for $title \n";

            $titles = ['No', 'Merchant Name', 'Merchant ID', 'Sub Status', 'Payment Amount', 'RTR', 'Complete Percentage'];
            $values = ['', 'merchant_name', 'merchant_id', 'sub_status', 'payment_amount', 'rtr', 'complete_percentage'];
            $this->sendMail($active_error_data, $titles, $values, $title);
        }

        //Check all advance completed merchant's Payment amount less than RTR.
        $advance_completed_status = [11];
        $advance_completed_merchants = Merchant::select('id', 'name', 'rtr', 'sub_status_id', 'complete_percentage')->whereIn('sub_status_id', $advance_completed_status)->get();
        $advance_completed_error_data = [];
        // echo "\n started \n";
        if ($advance_completed_merchants) {
            foreach ($advance_completed_merchants as $mer) {
                $merchant_id = $mer->id;
                $payment_total = ParticipentPayment::where('model','App\ParticipentPayment')->where('merchant_id', $merchant_id)->sum('payment');
                $rtr = $mer->rtr;
                if ($payment_total + 1 < $rtr) {
                    $advance_completed_error_data[] = [
                        'merchant_id' => $merchant_id,
                        'merchant_name' => $mer->name,
                        'sub_status' => $sub_statuses[$mer->sub_status_id],
                        'payment_amount' => FFM::sr($payment_total),
                        'rtr' => FFM::sr($rtr),
                        'complete_percentage' => FFM::percent($mer->complete_percentage),
                    ];
                }
            }
        }
        if (count($advance_completed_error_data)) {
            $title = 'Advance completed merchants with Payment amount less than RTR';
            echo "\n Sending mail for $title \n";

            $titles = ['No', 'Merchant Name', 'Merchant ID', 'Sub Status', 'Payment Amount', 'RTR', 'Complete Percentage'];
            $values = ['', 'merchant_name', 'merchant_id', 'sub_status', 'payment_amount', 'rtr', 'complete_percentage'];
            $this->sendMail($advance_completed_error_data, $titles, $values, $title);
        }

        //Check all settled and advance completed merchant's Principal amount greater than invested amount.
        $principal_status = [11, 18, 19, 20];
        $principal_merchants = Merchant::select('id', 'name', 'sub_status_id', 'complete_percentage')->whereIn('sub_status_id', $principal_status)->get();
        $principal_error_data = [];
        // echo "\n started \n";
        if ($principal_merchants) {
            foreach ($principal_merchants as $mer) {
                $merchant_id = $mer->id;
                $invested_amount_with_fees = MerchantUser::where('merchant_id', $merchant_id)->sum(DB::raw('amount + commission_amount + under_writing_fee + pre_paid'));
                $principal_amount = PaymentInvestors::where('merchant_id', $merchant_id)->sum('principal');
                if ($invested_amount_with_fees + 1 < $principal_amount) {
                    $principal_error_data[] = [
                        'merchant_id' => $merchant_id,
                        'merchant_name' => $mer->name,
                        'sub_status' => $sub_statuses[$mer->sub_status_id],
                        'invested_amount' => FFM::sr($invested_amount_with_fees),
                        'principal_amount' => FFM::sr($principal_amount),
                        'complete_percentage' => FFM::percent($mer->complete_percentage),
                    ];
                }
            }
        }

        if (count($principal_error_data)) {
            $title = 'Merchants with Principal amount greater than Invested Amount';
            echo "\n Sending mail for $title \n";

            $titles = ['No', 'Merchant Name', 'Merchant ID', 'Sub Status', 'Invested Amount', 'Principal amount', 'Complete Percentage'];
            $values = ['', 'merchant_name', 'merchant_id', 'sub_status', 'invested_amount', 'principal_amount', 'complete_percentage'];
            $this->sendMail($principal_error_data, $titles, $values, $title);
        }

        $merchant_company_fund = DB::table('company_amount')->leftJoin('merchants', 'merchants.id', 'company_amount.merchant_id')
        ->select(DB::raw('sum(max_participant) as max_participant'), 'merchant_id', 'merchants.name')
        ->groupBy('merchant_id')
        ->get()
        ->toArray();

        $company_amount = DB::table('merchant_user')
        ->groupBy('merchant_id')
        ->pluck(DB::raw('sum(amount) as amount'), 'merchant_id')
        ->toArray();
        $participant_amount_arr = [];
        if (count($merchant_company_fund) > 0) {
            foreach ($merchant_company_fund as $company_fund) {
                if (isset($company_amount[$company_fund->merchant_id])) {
                    $invested_amount = $company_amount[$company_fund->merchant_id];
                    $company_max_participant = $company_fund->max_participant;
                    $difference = $company_max_participant - $invested_amount;
                    if ($difference >= 1 || $difference <= -1) {
                        $participant_amount_arr[] = [
                        'merchant_id' => $company_fund->merchant_id,
                        'merchant_name' => $company_fund->name,
                        'invested_amount' => FFM::sr($invested_amount),
                        'company_max_participant' => FFM::sr($company_max_participant),
                        'difference' => FFM::sr($difference),
                    ];
                    }
                }
            }

            if (count($participant_amount_arr)) {
                $title = 'Merchants with difference in invested amount and company maximum participant amount';
                echo "\n Sending mail for $title \n";

                $titles = ['No', 'Merchant Name', 'Merchant ID', 'Invested Amount', 'Company Max Participant Amount', 'Difference'];
                $values = ['', 'merchant_name', 'merchant_id', 'invested_amount', 'company_max_participant', 'difference'];
                $this->sendMail($participant_amount_arr, $titles, $values, $title);
            }
        }

        // $participant_share_data = [];
        // $all_investments = MerchantUser::leftJoin('merchants', 'merchants.id', 'merchant_user.merchant_id')->select('merchant_id', 'merchant_user.user_id', 'merchant_id', 'actual_paid_participant_ishare','merchants.name','sub_status_id')->get();
        // if(count($all_investments) > 0){
        //     foreach($all_investments as $mer){
        //         $merchant_id = $mer->merchant_id;
        //         $user_id = $mer->user_id;
        //         $actual_participant_share = PaymentInvestors::where('merchant_id', $merchant_id)->where('user_id',$user_id)->sum('actual_participant_share');
        //         $difference = $actual_participant_share-$mer->actual_paid_participant_ishare;
        //         if($difference>0.5 || $difference <-0.5){

        //              $participant_share_data[] = [
        //                 'merchant_id' => $merchant_id,
        //                 'merchant_name' => $mer->name,
        //                 'sub_status' => $sub_statuses[$mer->sub_status_id],
        //                 'merchant_user_actual_participant_share' => FFM::sr($mer->actual_paid_participant_ishare),
        //                 'payment_investor_actual_participant_share' => FFM::sr($actual_participant_share),
        //                 'participant_share_difference' => FFM::sr($difference),
        //             ];

        //         }
        //         //echo $actual_participant_share.'==='.$mer->actual_paid_participant_ishare;echo"<br>";

        //     }
        //      if (count($participant_share_data)) {
        //     $title = 'Merchants with Participant share in merchant_user table and payment_investor tabe are different';
        //     echo "\n Sending mail for $title \n";

        //     $titles = ['No', 'Merchant Name', 'Merchant ID', 'Sub Status', 'Participant share in merchant_user', 'Participant share in payment_investor', 'Participant share difference'];
        //     $values = ['', 'merchant_name', 'merchant_id', 'sub_status', 'merchant_user_actual_participant_share', 'payment_investor_actual_participant_share', 'participant_share_difference'];
        //     $this->sendMail($participant_share_data, $titles, $values, $title);
        // }
        // }
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
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        $message['title'] = 'Merchant Unit Test';
        $msg['title'] = $message['title'];
        $msg['status'] = 'merchant_unit_test';
        $msg['subject'] = $message['title'];
        $message['content']['date'] = FFM::date(Carbon::now()->toDateString());
        $message['content']['date_time'] = FFM::datetime(Carbon::now()->toDateTimeString());
        $msg['date_time'] = FFM::datetime(Carbon::now()->toDateTimeString());
        $msg['template_type'] = 'merchant_unit';
        if (count($data)) {
            $message['content']['type'] = $title;
            $message['content']['count'] = count($data);
            $msg['count'] = count($data);
            $msg['type'] = $title;
            $msg['unqID'] = unqID();
            $msg['content'] = $message['content'];
            $exportCSV = $this->generateCSV($data, $titles, $values);
            $fileName = $message['content']['type'].'.csv';
            $msg['atatchment_name'] = $fileName;
            $msg['atatchment'] = $exportCSV;

            try {
                $msg['to_mail'] = $admin_email;
                $emailJob = (new CommonJobs($msg));
                dispatch($emailJob);
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
            $i++;
        }

        $export = new Data_arrExport($excel_array);

        return $export;
    }
}
