<?php

namespace App\Library\Repository;

use App\Events\UserHasAssignedInvestor;
use App\Exports\Data_arrExportPdfCsv;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\LiquidityLog;
use App\Merchant;
use App\MerchantUser;
use App\Models\EquityInvestorReport;
use App\Models\InvestorAchRequest;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Role_module;
use App\Settings;
use App\SubStatus;
use App\Template;
use App\Statements;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
//use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PayCalc;
use PDF;
use InvestorHelper;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;

class UserRepository implements IUserRepository
{
    protected $user;

    public function __construct(IRoleRepository $role)
    {
        ini_set('max_execution_time', 30000);
        $this->table = new User();
        $this->role = $role;
        if(Schema::hasTable('settings')){
        $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }

    public function SingleInvestorPDFCSVGenerator($investor, $filters)
    {
        $filters['label'] = '';
        if (isset($filters['from']) && $filters['from'] == 'syndication') {
            $filters['label'] = isset($investor->ExcludedLabelId) ? $investor->ExcludedLabelId : '';
        }
        $yesterday_date = date('Y-m-d', strtotime('-1 days'));
        $past_day_count = 0;
        $today = date('Y-m-d');
        $generation_time = date('Y-m-d', strtotime($investor['generation_time']));
        if ($filters['generationtype'] == 1 && ! empty($investor['generation_time'])) {
            switch ($investor['notification_recurence']) {
                case '1'://weekly
                $filters['date_start'] = date('Y-m-d', strtotime('-6 days', strtotime($today))); // -$days2 days
                $filters['date_end'] = $today;
                if ($filters['date_start'] <= $generation_time) {
                    $filters['date_start'] = date('Y-m-d', strtotime('1 day', strtotime($investor['generation_time'])));
                }
                if ($generation_time == $today) {
                    $filters['date_start'] = $today;
                    $filters['date_end'] = $today;
                }
                $dates = PayCalc::getWorkingDays($filters['date_start'], $filters['date_end']);
                if ($dates) {
                    $filters['date_start'] = min($dates);
                }
                break;
                case '2'://monthly
                $filters['date_start'] = date('Y-m-d', strtotime('-1 month', strtotime($today))); // -$days2 days
                $filters['date_end'] = $today;
                if ($filters['date_start'] <= $generation_time) {
                    $filters['date_start'] = date('Y-m-d', strtotime('1 day', strtotime($investor['generation_time'])));
                }
                if ($generation_time == $today) {
                    $filters['date_start'] = $today;
                    $filters['date_end'] = $today;
                }
                $dates = PayCalc::getWorkingDays($filters['date_start'], $filters['date_end']);
                if ($dates) {
                    $filters['date_start'] = min($dates);
                }
                break;
                case '3'://daily
                if ($generation_time != $today) {
                    $dates = PayCalc::getWorkingDays($today, $today);
                    if (empty($dates)) {
                        $filters['date_start'] = $today;
                        $filters['date_end'] = $today;
                    } else {
                        $new_date = max($dates);
                        $filters['date_start'] = $new_date;
                        $filters['date_end'] = $new_date;
                    }
                } else {
                    $filters['date_start'] = date('Y-m-d', strtotime($investor['generation_time'].' + 1 day'));
                    $filters['date_end'] = date('Y-m-d', strtotime($investor['generation_time'].' + 1 day'));
                }
                break;
                default:
                break;
            }
            User::where('id', $investor['id'])->update(['generation_time'=>date('Y-m-d h:i:s')]);
        } elseif ($filters['generationtype'] == 1 && empty($investor['generation_time'])) {
            switch ($investor['notification_recurence']) {
                case '1':
                $filters['date_end'] = $today; // -$days2 days
                $filters['date_start'] = date('Y-m-d', strtotime('-6 days', strtotime($filters['date_end']))); // -$days2 days
                break;
                case '2':
                $filters['date_end'] = $today;
                $filters['date_start'] = date('Y-m-d', strtotime('-1 months', strtotime($filters['date_end'])));
                break;
                case '3':
                $dates = PayCalc::getWorkingDays($today, $today);
                if (empty($dates)) {
                    $start = date('Y-m-d', strtotime($today.'- 5 days'));
                    $dates = PayCalc::getWorkingDays($start, $today);
                    $new_date = max($dates);
                    $filters['date_start'] = $new_date;
                    $filters['date_end'] = $new_date;
                } else {
                    $new_date = max($dates);
                    $filters['date_start'] = $new_date;
                    $filters['date_end'] = $new_date;
                }
                break;
                default:
                break;
            }
            User::where('id', $investor['id'])->update(['generation_time'=>date('Y-m-d h:i:s')]);
        }else
        {

            User::where('id', $investor['id'])->update(['generation_time'=>date('Y-m-d h:i:s')]);


        }

        $groupBy = $investor['notification_recurence'];
        $html = '';
        $query = '';
        $date_query_old = '';
        $date_query_1 = '';
        $date_query = '';
        $endDate = $filters['date_start'];
        $startdate = $filters['date_end'];
        $from_date = $filters['date_start'];
        $to_date = $filters['date_end'];

        if ($endDate) {
            $date_query_old = " AND participent_payments.created_at <= '$endDate'";
        } else {
            $date_query_old = " AND participent_payments.created_at <=  '1970-01-01' ";
        }

        if ($from_date) {
            $date_query_1 = " AND participent_payments.created_at >= '$from_date'";
        } else {
            $date_query_1 = " AND participent_payments.created_at <=  '1970-01-01' ";
        }
        if ($to_date) {
            $date_query_1 .= " AND participent_payments.created_at <= '$to_date'";
        }

        $query = ' AND user_id='.$investor['id'].' AND status in (1,3)';
        $payments = DB::table('participent_payments')
        ->where('participent_payments.is_payment', 1)
        ->where('participent_payments.rcode', 0);
        if ($investor['id']) {
            $payments = $payments->where('payment_investors.user_id', $investor['id']);
        }

        $payments = $payments->leftJoin('merchants', 'participent_payments.merchant_id', 'merchants.id');

        $merchants = $filters['merchants'];
        $payments = $payments->join('payment_investors', function ($join) use ($merchants) {
            $join->on('payment_investors.participent_payment_id', 'participent_payments.id');
            if (! empty($merchants)) {
                $join->whereIn('participent_payments.merchant_id', $merchants);
            }
        });

        if (! empty($filters['label'])) {
            $payments = $payments->whereIn('merchants.label', $filters['label']);
        }

        if (! empty($merchants)) {
            $payments = $payments->whereIn('merchants.id', $merchants);
        }
        if (! empty($filters['merchants'])) {
            $payments = $payments->whereIn('participent_payments.merchant_id', $merchants);
        }

        if ($filters['date_start']) {
            $payments = $payments->where('participent_payments.created_at', '>=', $filters['date_start']);
        }
        if ($filters['date_end']) {
            $payments = $payments->where('participent_payments.created_at', '<=', $filters['date_end']);
        }

        $payments = $payments->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');

        $payments = $payments->leftJoin('rcode', 'merchants.last_rcode', 'rcode.id');
        $payment2=clone $payments;
        $payment3=$payment2->select(DB::raw('MIN(payment_date) AS min_payment_date, MAX(payment_date) AS max_payment_date'))->first();
        $oldest_payment_date= $payment3->min_payment_date;
        $latest_payment_date= $payment3->max_payment_date;
        switch ($groupBy) {
            case '1':
            $payments = $payments->groupBy('participent_payments.merchant_id')->groupBy(DB::raw('WEEK(payment_date)'));
            break;
            case '2':
            $payments = $payments->groupBy('participent_payments.merchant_id')->groupBy(DB::raw('MONTH(payment_date)'));
            break;
            case '3':
            $payments->groupBy('participent_payments.merchant_id')
            ->groupBy('participent_payments.payment_date');
            break;
            default:
            $payments = $payments->groupBy('participent_payments.merchant_id');
            break;
        }
        $payments = $payments->whereRaw('principal+profit !=0');
        $payments = $payments->orderByDesc('last_payment_date');
        
        $payments=$payments->select(
            'participent_payments.id',
            'participent_payments.merchant_id',
            'payment_investors.actual_participant_share as p',
            'payment_date',
            'participent_payments.created_at',
             'sub_statuses.name',
            'rcode.code as last_rcode',
            'merchants.last_payment_date',
            'sub_statuses.name as sub_status_name',
            DB::raw('sum(payment_investors.actual_participant_share) as payment'),
            DB::raw('sum(actual_participant_share) as participant_share'),
            DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'),
            DB::raw('sum(payment_investors.profit) as profit'),
            //DB::raw('sum(participant_share-mgmnt_fee) as net_balance'),
            DB::raw("(SELECT SUM(payment_investors.actual_participant_share - mgmnt_fee) FROM payment_investors  LEFT JOIN participent_payments on
            payment_investors.participent_payment_id=participent_payments.id WHERE participent_payments.merchant_id=merchants.id $date_query_old $query GROUP BY payment_investors.merchant_id) as net_balance"),

            DB::raw("(SELECT SUM(payment_investors.actual_participant_share - mgmnt_fee) FROM payment_investors  LEFT JOIN participent_payments on
            payment_investors.participent_payment_id=participent_payments.id WHERE participent_payments.merchant_id=merchants.id $date_query_1 $query GROUP BY payment_investors.merchant_id) as net_participant_share"),

            DB::raw("(SELECT SUM(payment_investors.actual_participant_share - mgmnt_fee) FROM payment_investors  LEFT JOIN participent_payments on
            payment_investors.participent_payment_id=participent_payments.id WHERE payment_investors.merchant_id=merchants.id $date_query_old $query 
            GROUP BY payment_investors.merchant_id ) as net_balance_1"),

            DB::raw('sum(payment_investors.principal) as principal'),
            DB::raw("(SELECT SUM(merchant_user.invest_rtr) FROM merchant_user WHERE merchants.id=merchant_user.merchant_id $query GROUP BY merchant_user.merchant_id) as invest_rtr"),

            DB::raw("(SELECT SUM(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) FROM merchant_user  WHERE merchants.id=merchant_user.merchant_id $query GROUP BY merchant_user.merchant_id) as mgmnt_fee_amount"),
            DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id and participent_payments.is_payment = 1 and participent_payments.payment>0 ORDER BY payment_date DESC limit 1) last_payment_amount'),
            // DB::raw('sum(payment_investors.syndication_fee) as syndication_fee'),
            DB::raw('sum(final_participant_share) as final_participant_share'),
            DB::raw('upper(merchants.name) as merchant_name')
        );
        return [
            'payments'=>$payments,
            'filters' =>$filters,
            'oldest_payment_date' =>$oldest_payment_date,
            'latest_payment_date' =>$latest_payment_date,

        ];
    }
    public function singleSyndicatePaymentCalculation($investor, $filters)
    {
        $filters['label'] = '';
        if (isset($filters['from']) && $filters['from'] == 'syndication') {
            $filters['label'] = isset($investor->ExcludedLabelId) ? $investor->ExcludedLabelId : '';
        }
        $yesterday_date = date('Y-m-d', strtotime('-1 days'));
        $past_day_count = 0;
        $today = date('Y-m-d');
        $generation_time =  InvestorAchRequest::whereIn('transaction_method', [InvestorAchRequest::MethodByAutomaticDebit, InvestorAchRequest::MethodByAutomaticCredit])->where(['investor_id' => $investor['id'], 'ach_status' => InvestorAchRequest::AchStatusAccepted])->max('date') ?? null;
        if ($filters['generationtype'] == 1 && $generation_time) {
            switch ($investor['notification_recurence']) {
                case '1'://weekly
                $filters['date_start'] = date('Y-m-d', strtotime('-6 days', strtotime($today))); // -$days2 days
                $filters['date_end'] = $today;
                if ($filters['date_start'] <= $generation_time) {
                    $filters['date_start'] = date('Y-m-d', strtotime('1 day', strtotime($generation_time)));
                }
                if ($generation_time == $today) {
                    $filters['date_start'] = $today;
                    $filters['date_end'] = $today;
                }
                $dates = PayCalc::getWorkingDays($filters['date_start'], $filters['date_end']);
                if ($dates) {
                    $filters['date_start'] = min($dates);
                }
                break;
                case '2'://monthly
                $filters['date_start'] = date('Y-m-d', strtotime('-1 month', strtotime($today))); // -$days2 days
                $filters['date_end'] = $today;
                if ($filters['date_start'] <= $generation_time) {
                    $filters['date_start'] = date('Y-m-d', strtotime('1 day', strtotime($generation_time)));
                }
                if ($generation_time == $today) {
                    $filters['date_start'] = $today;
                    $filters['date_end'] = $today;
                }
                $dates = PayCalc::getWorkingDays($filters['date_start'], $filters['date_end']);
                if ($dates) {
                    $filters['date_start'] = min($dates);
                }
                break;
                case '3'://daily
                if ($generation_time != $today) {
                    $dates = PayCalc::getWorkingDays($today, $today);
                    if (empty($dates)) {
                        $filters['date_start'] = $today;
                        $filters['date_end'] = $today;
                    } else {
                        $new_date = max($dates);
                        $filters['date_start'] = $new_date;
                        $filters['date_end'] = $new_date;
                    }
                } else {
                    $filters['date_start'] = date('Y-m-d', strtotime($investor['generation_time'].' + 1 day'));
                    $filters['date_end'] = date('Y-m-d', strtotime($investor['generation_time'].' + 1 day'));
                }
                break;
                default:
                break;
            }
        } elseif ($filters['generationtype'] == 1 && $generation_time == null) {
            switch ($investor['notification_recurence']) {
                case '1':
                $filters['date_end'] = $today; // -$days2 days
                $filters['date_start'] = date('Y-m-d', strtotime('-6 days', strtotime($filters['date_end']))); // -$days2 days
                break;
                case '2':
                $filters['date_end'] = $today;
                $filters['date_start'] = date('Y-m-d', strtotime('-1 months', strtotime($filters['date_end'])));
                break;
                case '3':
                $dates = PayCalc::getWorkingDays($today, $today);
                if (empty($dates)) {
                    $start = date('Y-m-d', strtotime($today.'- 5 days'));
                    $dates = PayCalc::getWorkingDays($start, $today);
                    $new_date = max($dates);
                    $filters['date_start'] = $new_date;
                    $filters['date_end'] = $new_date;
                } else {
                    $new_date = max($dates);
                    $filters['date_start'] = $new_date;
                    $filters['date_end'] = $new_date;
                }
                break;
                default:
                break;
            }
        }

        $groupBy = $investor['notification_recurence'];
        $html = '';
        $query = '';
        $date_query_old = '';
        $date_query = '';
        $endDate = $filters['date_start'];
        $startdate = $filters['date_end'];

        if ($endDate) {
            $date_query_old = " AND participent_payments.created_at <= '$endDate'";
        } else {
            $date_query_old = " AND participent_payments.created_at <=  '1970-01-01' ";
        }

        $query = ' AND user_id='.$investor['id'].' AND status in (1,3)';
        $payments = DB::table('participent_payments')
        ->where('participent_payments.is_payment', 1)
        ->where('participent_payments.rcode', 0);
        if ($investor['id']) {
            $payments = $payments->where('payment_investors.user_id', $investor['id']);
        }

        $payments = $payments->leftJoin('merchants', 'participent_payments.merchant_id', 'merchants.id');

        $merchants = $filters['merchants'];
        $payments = $payments->join('payment_investors', function ($join) use ($merchants) {
            $join->on('payment_investors.participent_payment_id', 'participent_payments.id');
            if (! empty($merchants)) {
                $join->whereIn('participent_payments.merchant_id', $merchants);
            }
        });

        if (! empty($filters['label'])) {
            $payments = $payments->whereIn('merchants.label', $filters['label']);
        }

        if (! empty($merchants)) {
            $payments = $payments->whereIn('merchants.id', $merchants);
        }
        if (! empty($filters['merchants'])) {
            $payments = $payments->whereIn('participent_payments.merchant_id', $merchants);
        }

        if ($filters['date_start']) {
            $payments = $payments->whereDate('participent_payments.created_at', '>=', $filters['date_start']);
        }
        if ($filters['date_end']) {
            $payments = $payments->whereDate('participent_payments.created_at', '<=', $filters['date_end']);
        }

        $payments = $payments->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');

        $payments = $payments->leftJoin('rcode', 'merchants.last_rcode', 'rcode.id');
        $payment2=clone $payments;
        $payment3=$payment2->select(DB::raw('MIN(payment_date) AS min_payment_date, MAX(payment_date) AS max_payment_date'))->first();
        $oldest_payment_date= $payment3->min_payment_date;
        $latest_payment_date= $payment3->max_payment_date;
        switch ($groupBy) {
            case '1':
            $payments = $payments->groupBy('participent_payments.merchant_id')->groupBy(DB::raw('WEEK(payment_date)'));
            break;
            case '2':
            $payments = $payments->groupBy('participent_payments.merchant_id')->groupBy(DB::raw('MONTH(payment_date)'));
            break;
            case '3':
            $payments->groupBy('participent_payments.merchant_id')
            ->groupBy('participent_payments.payment_date');
            break;
            default:
            $payments = $payments->groupBy('participent_payments.merchant_id');
            break;
        }
        $payments = $payments->whereRaw('principal+profit !=0');
        $payments = $payments->orderByDesc('last_payment_date');
        
        $payments=$payments->select(
            'participent_payments.id',
            'participent_payments.merchant_id',
            'payment_investors.actual_participant_share as p',
            'payment_date',
            'participent_payments.created_at',
             'sub_statuses.name',
            'rcode.code as last_rcode',
            'merchants.last_payment_date',
            'sub_statuses.name as sub_status_name',
            DB::raw('sum(payment_investors.actual_participant_share) as payment'),
            DB::raw('sum(actual_participant_share) as participant_share'),
            DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'),
            DB::raw('sum(payment_investors.profit) as profit'),
            //DB::raw('sum(participant_share-mgmnt_fee) as net_balance'),
            DB::raw("(SELECT SUM(payment_investors.actual_participant_share - mgmnt_fee) FROM payment_investors  LEFT JOIN participent_payments on
            payment_investors.participent_payment_id=participent_payments.id WHERE participent_payments.merchant_id=merchants.id $date_query_old $query GROUP BY payment_investors.merchant_id) as net_balance"),

            DB::raw("(SELECT SUM(payment_investors.actual_participant_share) FROM payment_investors  LEFT JOIN participent_payments on
            payment_investors.participent_payment_id=participent_payments.id WHERE payment_investors.merchant_id=merchants.id $date_query_old $query 
            GROUP BY payment_investors.merchant_id ) as net_balance_1"),

            DB::raw('sum(payment_investors.principal) as principal'),
            DB::raw("(SELECT SUM(merchant_user.invest_rtr) FROM merchant_user WHERE merchants.id=merchant_user.merchant_id $query GROUP BY merchant_user.merchant_id) as invest_rtr"),

            DB::raw("(SELECT SUM(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) FROM merchant_user  WHERE merchants.id=merchant_user.merchant_id $query GROUP BY merchant_user.merchant_id) as mgmnt_fee_amount"),
            DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id and participent_payments.is_payment = 1 and participent_payments.payment>0 ORDER BY payment_date DESC limit 1) last_payment_amount'),
            // DB::raw('sum(payment_investors.syndication_fee) as syndication_fee'),
            DB::raw('sum(final_participant_share) as final_participant_share'),
            DB::raw('merchants.name as merchant_name')
        );
        return [
            'payments'=>$payments,
            'filters' =>$filters,
            'oldest_payment_date' =>$oldest_payment_date,
            'latest_payment_date' =>$latest_payment_date,

        ];
    }

    public function generatePDFCSV($investors, $filters) {
        $msg = $view = '';
        // sample link https://investor-portals.s3.amazonaws.com/syndication_report_26XXX/1651733620.xlsx
        // $yesterday_date='2020/11/29';
        // $holidays = array_keys(config('custom.holidays'));
        // $dates = [];
        if (! empty($investors)) {
            foreach ($investors as $key=>$investor) {
                $ALL_merchants = [];
                $ReturnResult = $this->SingleInvestorPDFCSVGenerator($investor, $filters);
                $payments = $ReturnResult['payments'];
                $filters = $ReturnResult['filters'];
                $oldest_payment_date=$ReturnResult['oldest_payment_date'];
                $latest_payment_date=$ReturnResult['latest_payment_date'];
                $payment1 = $payments->get();
                $key_array = array(); 
                $i = 0; 
                $groupBy = $investor['notification_recurence'];
                if ($groupBy == 3) {
                    $net_balance = 0;
                    if ($payment1) {
                        foreach ($payment1 as $key => $value) {
                            if ($filters['date_end'] != '' || $filters['date_start'] != '') {
                                if (!in_array($value->merchant_id, $key_array)) {
                                    $key_array[$i] = $value->merchant_id;
                                    $net_balance = $value->participant_share-$value->mgmnt_fee; 
                                } else {
                                    $key_array[$i]=$value->merchant_id;
                                    $net_balance = $net_balance + ($value->participant_share-$value->mgmnt_fee);
                                }
                                $payment1[$key]->total = ($value->invest_rtr-$value->mgmnt_fee_amount) - $net_balance;
                                $i++; 
                            } else {
                                if (!in_array($value->merchant_id, $key_array)) {
                                    $key_array[$i] = $value->merchant_id;
                                    $net_balance = $value->payment; 
                                } else {
                                    $key_array[$i]=$value->merchant_id;
                                    $net_balance = $net_balance + $value->payment;
                                }
                                $payment1[$key]->total = $value->invest_rtr - $net_balance;
                                $i++; 
                            }
                        }
                    }
                }
                
                $merchants11 = $payments->pluck('merchant_id')->toArray();
                // $rtr_new=array_sum($new_array);
                if (! empty($payment1->toArray())) {
                    //start merchant loop
                    $options      = '';
                    $commonName   = '';
                    $fileName     = '';
                    $templateType = '';
                    // file name set
                    switch ($filters['recurrence']) {
                        case '1':
                        // $options = 'last_day';
                        $options      = 'Last day syndication report has been generated,please see attachment.';
                        $templateType = 'pdf_recurrence';
                        $title        = 'Last Day';
                        $fileName     = 'last_day_report_'.$investor['id'].'_'. /* $investor->name */rand().'_'.date('m-d-Y', strtotime($filters['date_start'])).'_'.date('m-d-Y', strtotime($filters['date_end']));
                        $commonName   = 'last_day_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_day_report_'.time(); 
                        break;
                        case '2':
                        // $options = 'last_week';
                        $options      = 'Last week syndication report has been generated,please see attachment.';
                        $title        = 'Last Week';
                        $templateType = 'pdf_recurrence';
                        $fileName     = 'last_week_report_'.$investor['id'].'_'. /* $investor->name */rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end']));
                        $commonName   = 'last_week_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_week_report_'.time();
                        break;
                        case '3':
                        // $options = 'last_two_week';
                        $options      = 'Last two week syndication report has been generated,please see attachment.';
                        $title        = 'Last Two Week';
                        $templateType = 'pdf_recurrence';
                        $fileName     = 'last_two_week_report_'.$investor['id'].'_'. /* $investor->name */rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end']));
                        $commonName   = 'last_two_week_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_two_week_report_'.time();
                        break;
                        case '4':
                        //$options = 'last_month';
                        $options      = 'Last month syndication report has been generated,please see attachment.';
                        $title        = 'Last Month';
                        $templateType = 'pdf_recurrence';
                        $fileName     = 'last_month_report_'.$investor['id'].'_'. /* $investor->name */rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end']));
                        $commonName   = 'last_month_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_month_report_'.time();
                        break;
                        case '5':
                        // $options = 'last_year';
                        $options      = 'Last year syndication report has been generated,please see attachment.';
                        $title        = 'Last Year';
                        $templateType = 'pdf_recurrence';
                        $fileName     = 'last_year_report_'.$investor['id'].'_'. /* $investor->name */rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($date_end));
                        $commonName   = 'last_year_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_year_report_'.time();
                        break;
                        default:
                        //'for <a href="'.url('/investors/dashboard').'" style="text-decoration:none;color: black;">'.$investor['name'] .'</a>'.'. Statement is attached.';
                        $options      = 'Syndication Report has been generated,please see attachment.';
                        $templateType = 'pdf_normal';
                        $title        = 'Syndication Report for'.$investor['name'];
                        $fileName     = 'syndication_report_'.$investor['name'].'/'.time();
                        $commonName   = 'syndication_report_'.$investor['name'].'/'.time();
                        break;
                    }
                    // $totDebited = $totLlc = $totSynd = $totMgm = $totSyndf = $totPrincipal= $totProfit=$tinvest_rtr=0;
                    $investor_type = ($investor['investor_type'] == 1) ? 'Debt' : 'Equity';
                    $userId = $investor['id'];
                    $whole_portfolio = $investor['whole_portfolio'];
                    
                    $query = ' AND user_id='.$userId.' AND status in (1,3)';
                    $date_query_old = " AND participent_payments.created_at <  '1970/01/01' ";
                    if ($whole_portfolio == 1) {
                        $ALL_merchants_1 = DB::table('merchant_user');
                        $ALL_merchants_1 = $ALL_merchants_1->select(
                            'merchant_user.merchant_id',
                            'merchants.name',
                            'sub_statuses.name as status_name',
                            'merchants.last_payment_date',
                            'rcode.code as last_rcode',
                            DB::raw('sum(invest_rtr) as invest_rtr'),
                            DB::raw("(SELECT sum(payment) FROM participent_payments  LEFT JOIN payment_investors on payment_investors.participent_payment_id=participent_payments.id WHERE payment_investors.participent_payment_id=participent_payments.id and participent_payments.model LIKE '%ParticipentPayment%' $query group by participent_payments.merchant_id ORDER BY payment_date DESC limit 1) last_payment_amount"),
                        );
                        $ALL_merchants_1 = $ALL_merchants_1->where('merchant_user.user_id', $userId);
                        $ALL_merchants_1 = $ALL_merchants_1->whereIn('merchant_user.status', [1, 3]);
                        $ALL_merchants_1 = $ALL_merchants_1->whereNotIn('merchant_user.merchant_id', $merchants11);
                        $ALL_merchants_1 = $ALL_merchants_1->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
                        $ALL_merchants_1 = $ALL_merchants_1->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode');
                        $ALL_merchants_1 = $ALL_merchants_1->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');
                        $ALL_merchants_1 = $ALL_merchants_1->groupBy('merchant_user.merchant_id');
                        $ALL_merchants_1 = $ALL_merchants_1->pluck('merchant_user.merchant_id')->toArray();
                        $ALL_merchants = DB::table('participent_payments');
                        $ALL_merchants = $ALL_merchants->where('participent_payments.model','LIKE',"%ParticipentPayment%");
                        $ALL_merchants = $ALL_merchants->select(
                            'merchants.name',
                            'participent_payments.id',
                            'participent_payments.merchant_id',
                            'payment_date',
                            'participent_payments.created_at',
                            'rcode.code as last_rcode',
                            'merchants.last_payment_date',
                            'sub_statuses.name as status_name',
                            DB::raw('sum(payment_investors.actual_participant_share) as payment'),
                            DB::raw('sum(actual_participant_share) as participant_share'),
                            DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'),
                            //DB::raw('sum(participant_share-mgmnt_fee) as net_balance'),
                            DB::raw("(SELECT SUM(payment_investors.actual_participant_share - mgmnt_fee) FROM payment_investors  LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id WHERE participent_payments.merchant_id=merchants.id $date_query_old $query GROUP BY payment_investors.merchant_id) as net_balance"),
                            DB::raw("(SELECT SUM(payment_investors.actual_participant_share - mgmnt_fee) FROM payment_investors  LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id WHERE participent_payments.merchant_id=merchants.id $date_query_old $query GROUP BY payment_investors.merchant_id) as net_balance_1"),
                            DB::raw("(SELECT SUM(merchant_user.invest_rtr) FROM merchant_user WHERE merchants.id=merchant_user.merchant_id $query GROUP BY merchant_user.merchant_id) as invest_rtr"),
                            DB::raw("(SELECT SUM(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) FROM merchant_user  WHERE merchants.id=merchant_user.merchant_id $query GROUP BY merchant_user.merchant_id) as mgmnt_fee_amount"),
                            DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id and participent_payments.model LIKE "%ParticipentPayment%" ORDER BY payment_date DESC limit 1) last_payment_amount'),
                            // DB::raw('sum(payment_investors.syndication_fee) as syndication_fee'),
                            DB::raw('sum(final_participant_share) as final_participant_share'),
                            DB::raw('upper(merchants.name) as merchant_name')
                        );
                        $ALL_merchants = $ALL_merchants->whereNotIn('participent_payments.merchant_id', $merchants11);
                        // $payments      = $payments->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
                        $ALL_merchants = $ALL_merchants->join('payment_investors', function ($join) use ($ALL_merchants_1) {
                            $join->on('payment_investors.participent_payment_id', 'participent_payments.id');
                            if (! empty($ALL_merchants_1)) {
                                $join->whereIn('participent_payments.merchant_id', $ALL_merchants_1);
                            }
                        });
                        if ($userId) {
                            $ALL_merchants = $ALL_merchants->where('payment_investors.user_id', $userId);
                        }
                        
                        if (! empty($ALL_merchants_1)) {
                            $ALL_merchants = $ALL_merchants->whereIn('participent_payments.merchant_id', $ALL_merchants_1);
                        }
                        
                        $ALL_merchants = $ALL_merchants->leftJoin('merchants', 'merchants.id', 'participent_payments.merchant_id');
                        if (! empty($ALL_merchants_1)) {
                            $ALL_merchants = $ALL_merchants->whereIn('merchants.id', $ALL_merchants_1);
                        }
                        $ALL_merchants = $ALL_merchants->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')
                        ->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
                        ->groupBy('participent_payments.merchant_id');
                        $ALL_merchants = $ALL_merchants->orderByDesc('last_payment_date')->get()->toArray();
                    }
                    
                    /****   change merchant user query ******/
                    $investments = DB::table('merchant_user');
                    $investments = $investments->whereIn('merchant_user.status', [1, 3]);
                    $investments = $investments->where('merchant_user.user_id', $userId);
                    $investments = $investments->select(
                        DB::raw('sum(pre_paid) as pre_paid'),
                        DB::raw('sum(paid_mgmnt_fee) as paid_mgmnt_fee'),
                        DB::raw('sum(paid_participant_ishare) as paid_participant_ishare'),
                        DB::raw('sum(amount) as amount'),
                        DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee + merchant_user.up_sell_commission) as invested_amount'),
                        // DB::raw('sum(merchant_user.syndication_fee ) as syndication_fee '),
                        DB::raw('sum(merchant_user.mgmnt_fee) as mgmnt_fee'),
                        DB::raw('sum(invest_rtr) as invest_rtr')
                    );
                    
                    $investments = $investments->leftJoin('users', function ($join) use ($userId) {
                        $join->on('users.id', '=', 'merchant_user.user_id');
                        $join->where('users.id', $userId);
                    });
                    $arr = $investments->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->where('merchants.active_status', 1);
                    $array6 = $arr->first();
                    
                    $currentValueQuery = Merchant::join('merchant_user', 'merchant_user.merchant_id', '=', 'merchants.id');
                    $currentValueQuery = $currentValueQuery->select(
                        DB::raw(' SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.up_sell_commission + merchant_user.pre_paid) as invested_amount'),
                        DB::raw('SUM(merchant_user.actual_paid_participant_ishare) as paid_participant_ishare')
                    );
                    $currentValueQuery = $currentValueQuery->whereIn('merchant_user.status', [1, 3]);
                    $currentValueQuery = $currentValueQuery->where('merchant_user.user_id', $userId);
                    $currentValueQuery = $currentValueQuery->whereNotIn('merchants.sub_status_id', [4, 22])->first();
                    
                    $defaultMerchantIds = Merchant::whereIn('sub_status_id', [4, 22])->pluck('id')->toArray();
                    $paymentInvestorQuery = PaymentInvestors::whereNotIn('payment_investors.merchant_id', $defaultMerchantIds)->where('user_id', $userId);
                    $cost_for_ctd = $paymentInvestorQuery->sum('principal');
                    
                    $c_invested_amount = ($currentValueQuery->invested_amount - $cost_for_ctd);
                    
                    $investorArray = [
                        'investor_name' => $investor['name'],
                        'email'         => $investor['email'],
                        'investor_type' => $investor_type,
                        'brokerage'     => $investor['brokerage'],
                        'management_fee'=> $investor['management_fee'],
                        'startDate'     => $filters['date_start'],
                        'endDate'       => $filters['date_end'],
                        'display_value' => $investor['display_value'],
                    ];
                    $total_funded                  = 0;
                    $bleded_amount                 = 0;
                    $commission_total              = 0;
                    $pre_paid_total                = 0;
                    $ctd                           = 0;
                    $payment                       = 0;
                    $commission                    = 0;
                    $total_paid_syndication_fee    = 0;
                    $total_paid_mgmnt_fee          = 0;
                    $total_paid_participant_ishare = 0;
                    $total_syndication_fee         = 0;
                    $total_mgmnt_fee               = 0;
                    $value                         = 0;
                    $value1                        = 0;
                    $default_pay                   = 0;
                    $default_pay_rtr               = 0;
                    $default_rate                  = Settings::value('rate');
                    $default_rate                  = $default_rate;
                    $magt_fee                      = 0;
                    // $array = $investments->get();
                    // investment loop started
                    $default_payment = Settings::value('default_payment');
                    // $default_pay_rtr = ParticipentPayment::whereHas('paymentAllInvestors', function ($query) use ($userId) {
                    //     $query->where('user_id', $userId); // whre no default merchants.
                    // })
                    // ->whereHas('merchant', function ($query) {
                    //     $query->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1); // whre no default merchants.
                    // })->sum('final_participant_share');
                    //$tinvest_rtr=array_sum(array_column($array6->toArray(), 'invest_rtr'));
                    //$tinvest_rtr=0;
                    $array0          = $arr->get();
                    $ctd             = $array6->paid_participant_ishare - $array6->paid_mgmnt_fee;
                    $invested_amount = $array6->invested_amount;
                    $array1          = $arr->whereNotIn('merchants.sub_status_id', [4, 22])->get();
                    $bleded_amount   = 0;
                    $total_amount    = 0;
                    $invest_rtr      = 0;
                    $fees            = 0;
                    $amount          = 0;
                    $commission      = 0;
                    $rate            = Settings::value('rate');
                    $amount          = $array6->amount;
                    /****************      ********************/
                    $magt_fee   = $array6->mgmnt_fee;
                    $total_rtr  = 0;
                    $invest_rtr = $array6->invest_rtr;
                    /****************      ********************/
                    // $total_rtr = $total_amount - $fees + $default_pay_rtr;
                    //$commission = ;
                    $invest_rtr   = $invest_rtr - ($invest_rtr * ($rate / 100));
                    $total_amount = $total_amount + $invest_rtr;
                    $fees         = $magt_fee;
                    $commission_total += ($amount * ($commission / 100));
                    // $total_rtr = $total_amount - $fees + $default_pay_rtr;  //Fee added to dashboard RTR.
                    // $invested_amount  =
                    // changed queries start from here
                    $pre_paid_total = $array6->pre_paid;
                    //$total_paid_mgmnt_fee = array_sum(array_column($array0->toArray(), 'paid_mgmnt_fee'));
                    //  $total_paid_participant_ishare = array_sum(array_column($array0->toArray(), 'paid_participant_ishare'));
                    //  $ctd = 0;// $total_paid_participant_ishare - $total_paid_mgmnt_fee;
                    //$total_funded = array_sum(array_column($array0->toArray(), 'amount'));
                    // $invested_amount = //$total_funded + $commission_total + $pre_paid_total;
                    /************** removed blended rate  *************/
                    //  $blended_rate = $total_rtr ? $bleded_amount / $total_rtr * 100 : 0;
                    /************** removed blended rate  *************/
                    // investor liquidity single query
                    $liquidity_this_investor = UserDetails::where('user_id', $userId)->value('liquidity');
                    if (($liquidity_this_investor)) {
                        $cash_in_hands = $liquidity_this_investor;
                    } else {
                        $cash_in_hands = 0;
                    }
                    // $view = view('separatePDF')->with(compact('merchants','invested_amount', 'ctd', 'investorArray', 'cash_in_hands', 'total_rtr', 'blended_rate', 'options'));
                    // $view = view('testView')->with(compact('payment1','invested_amount', 'ctd', 'investorArray', 'cash_in_hands', 'total_rtr', 'options',
                    //     'whole_portfolio'));
                    if (! empty($payment1)) {
                        $totLlc                     = 0; // array_sum(array_column($payment1->toArray(), 'participant_share'));
                        $totMgm                     = 0; //array_sum(array_column($payment1->toArray(), 'mgmnt_fee'));
                        $totPrincipal               = 0; // array_sum(array_column($payment1->toArray(), 'principal'));
                        $totProfit                  = 0; //array_sum(array_column($payment1->toArray(), 'profit'));
                        //todo llc round value bit diffrent from report page.
                        $tinvest_rtr                = 0;
                        $whole_portfolio_rtr        = 0;
                        // $tlastpaymentamount      =array_sum(array_column($payments->toArray(), 'last_payment_amount'));
                        $totSynd                    = 0; //array_sum(array_column($payment1->toArray(), 'participant_share')) - array_sum(array_column($payment1->toArray(), 'mgmnt_fee'));
                        $tparticipant_share_balance = 0;
                        $whole_portfolio_balance    = 0;
                        $totSyndf                   = 0; //array_sum(array_column($payment1->toArray(), 'participant_share')) - array_sum(array_column($payment1->toArray(), 'mgmnt_fee'));
                    }
                    // Checked already generated pdf/csv
                    $fileUrl = '';
                    $fileType = '';
                    $PTD = InvestorTransaction::whereinvestor_id($investor['id']);
                    $PTD = $PTD->where('transaction_category', '4'); //Debited to investor
                    $PTD = $PTD->where('transaction_type', '1'); //debit
                    $PTD = $PTD->where('status', InvestorTransaction::StatusCompleted);
                    $PTD = $PTD->sum('amount');
                    $PTS = InvestorAchRequest::whereinvestor_id($investor['id']);
                    $PTS = $PTS->where('transaction_type', 'same_day_credit');
                    $PTS = $PTS->latest()->first();
                    $PTS = $PTS ? $PTS->amount : 0;
                    // if($investor['file_type']==1)
                    //   {
                    // $fileType='PDF';
                    $filePDFName = $commonName.'.pdf';
                    
                    $pdf = PDF::loadView('testView', compact('totLlc', 'totMgm', 'totSyndf', 'totSynd', 'totPrincipal', 'totProfit', 'tinvest_rtr', 'whole_portfolio_rtr', 'tparticipant_share_balance', 'whole_portfolio_balance', 'ALL_merchants', 'payment1', 'invested_amount', 'ctd', 'investorArray', 'cash_in_hands', 'total_rtr', 'options', 'whole_portfolio', 'PTD', 'PTS', 'c_invested_amount', 'groupBy','oldest_payment_date','latest_payment_date'));
                    // $view = $view->with(compact('totLlc', 'totMgm', 'totSyndf', 'totSynd','totPrincipal','totProfit','tinvest_rtr','tparticipant_share_balance','ALL_merchants'));
                    // $html .= $view->render();
                    //  $pdf = PDF::loadHTML($html);
                    $customPaper = [0, 0, 720, 1440];
                    $pdf->setPaper($customPaper, 'landscape'); //'landscape'
                    // dompdf
                    $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
                    $pdf->stream($filePDFName)->header('Content-Type', 'application/pdf');
                    $load = Storage::disk('s3')->put($filePDFName, $pdf->output(),config('filesystems.disks.s3.privacy'));
                    // $filePDFUrl = Storage::disk('s3')->url($filePDFName);
                    $filePDFUrl = Storage::disk('s3')->temporaryUrl($filePDFName,Carbon::now()->addMinutes(20));
                    //$filePDFName1 = 'investors/'.$commonName.'.pdf';
                    
                    // $pdf_for_investors = PDF::loadView('viewForInvestors',compact('totLlc', 'totMgm', 'totSyndf', 'totSynd', 'totPrincipal', 'totProfit', 'tinvest_rtr', 'whole_portfolio_rtr', 'tparticipant_share_balance', 'whole_portfolio_balance', 'ALL_merchants', 'payment1', 'invested_amount', 'ctd', 'investorArray', 'cash_in_hands', 'total_rtr', 'options', 'whole_portfolio', 'PTD', 'PTS', 'c_invested_amount', 'groupBy','oldest_payment_date','latest_payment_date'));
                    
                    // $pdf_for_investors->setPaper($customPaper, 'landscape'); //'landscape'
                    // // dompdf
                    // $pdf_for_investors->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
                    // $pdf_for_investors->stream($filePDFName1)->header('Content-Type', 'application/pdf');
                    // $load = Storage::disk('s3')->put($filePDFName1, $pdf_for_investors->output(), 'public');
                    // $filePDFUrl1 = asset(\Storage::disk('s3')->url($filePDFName1));
                    
                    $fileType = 'CSV';
                    if ($investorArray['startDate']) {
                        $stDate = FFM::date($investorArray['startDate']);
                    } else {
                        $stDate = '';
                    }
                    if ($investorArray['endDate']) {
                        $edDate = FFM::date($investorArray['endDate']);
                    } else {
                        $edDate = '';
                    }
                    
                    $excel_array[0] = ['', '', '', '', '', '', '', '', '', '', 'Investor Name', $investorArray['investor_name'], '', ''];
                    $excel_array[1] = ['', '', '', '', '', '', '', '', '', '', 'Email', $investorArray['email'], '', ''];
                    // $excel_array[2] = ['', '', '', '', '', '', '', '', '', '', 'Invested Amount', FFM::dollar($invested_amount), '', ''];
                    $excel_array[2] = ['', '', '', '', '', '', '', '', '', '', 'Current Invested Amount', FFM::dollar($c_invested_amount), '', ''];
                    // $excel_array[3] = ['', '', '', '', '', '', '', '', '', '', '(Cash to Date) CTD', FFM::dollar($ctd), '', ''];
                    // $excel_array[4] = ['','','','', '','', '','', '','','','','PTS', FFM::dollar($PTS)];
                    $excel_array[4] = ['', '', '', '', '', '', '', '', '', '', '(Paid to Date) PTD', FFM::dollar($PTD * -1)];
                    $excel_array[5] = ['', '', '', '', '', '', '', '', '', '', 'Liquidity', FFM::dollar($cash_in_hands)];
                    $excel_array[6] = ['', '', '', '', '', '', '', '', '', '', 'Generated Date', date(FFM::defaultDateFormat('db')), '', ''];
                    $excel_array[7] = ['', '', '', '', '', '', '', '', '', '', 'Posted From', FFM::date($oldest_payment_date), '', '', ''];
                    $excel_array[8] = ['', '', '', '', '', '', '', '', '', '', 'Posted To', FFM::date($latest_payment_date), '', '', ''];
                    // $excel_array[9] = ['No.', 'Merchant', 'Date', 'Total Payments', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last Rcode', 'Last Payment Date', 'Last Payment Amount', 'Participant RTR', 'Participant RTR Balance'];
                    $excel_array[9] = ['No.', 'Merchant', 'Date', 'Total Payments', 'Management Fee', 'Net Amount',  'Last Payment Date','Net Participant RTR', 'Net Participant RTR Balance'];
                    if ($whole_portfolio == 1) {
                        // $excel_array[9] = ['No.', 'Merchant', 'Status', 'Date', 'Total Payments', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last Rcode', 'Last Payment Date', 'Last Payment Amount', 'Participant RTR', 'Participant RTR Balance'];
                        $excel_array[9] = ['No.', 'Merchant', 'Status', 'Date', 'Total Payments', 'Management Fee', 'Net Amount','Last Payment Date','Net Participant RTR', 'Net Participant RTR Balance'];
                    }
                    //$payment1[$i]->sub_status_name
                    $t_participant = $t_participant1 = $t_mangt_fee = $t_mangt_fee1 = $t_netamount = $t_netamount1 = $t_profit = $t_principal = $rtr_balance = $t_rtr = $t_rtr1 = $rtr_balance1 = $t_mag_amount = $t_mag_amount1 = $t_net_balance = $t_net_balance1 = 0;
                    // $t_rtr=array_sum($rtr_total);
                    for ($i = 0,$j = 11; $i < count($payment1); $i++,$j++) {
                        //  $rtr_bal=$payment1[$i]->invest_rtr-$payment1[$i]->mgmnt_fee_amount-($payment1[$i]->net_balance+$payment1[$i]->participant_share-$payment1[$i]->mgmnt_fee);
                        
                        if ($groupBy == 3) {
                            $rtr_bal = $payment1[$i]->total;
                            //$payment1[$i]->invest_rtr - ($payment1[$i]->total);
                        } else {
                            $rtr_bal = $payment1[$i]->invest_rtr - ($payment1[$i]->net_balance_1 + $payment1[$i]->participant_share);
                        }
                        
                        $bal             = ($rtr_bal > 0) ? $rtr_bal : 0;
                        $t_participant  += $payment1[$i]->participant_share;
                        $t_mangt_fee    += $payment1[$i]->mgmnt_fee;
                        $t_mag_amount   += $payment1[$i]->mgmnt_fee_amount;
                        $t_netamount     = round($t_participant - $t_mangt_fee, 4);
                        $t_net_balance  += $payment1[$i]->net_balance;
                        $t_profit       += $payment1[$i]->profit;
                        $t_principal    += $payment1[$i]->principal;
                        $t_rtr          += $payment1[$i]->invest_rtr;
                        $rtr_balance    += $bal;
                        // $t_rtr - $t_mag_amount - ($t_net_balance + $t_netamount);
                        
                        $excel_array[$j]['SI'] = $i + 1;
                        if ($investor['display_value'] == 'mid') {
                            $excel_array[$j]['Merchant'] = $payment1[$i]->merchant_id;
                        } else {
                            $excel_array[$j]['Merchant'] = $payment1[$i]->merchant_name;
                        }
                        
                        if ($whole_portfolio == 1) {
                            $excel_array[$j]['Status'] = '';
                        }
                        
                        $excel_array[$j]['Date']           = FFM::date($payment1[$i]->payment_date);
                        $excel_array[$j]['Total Payments'] = FFM::dollar($payment1[$i]->participant_share);
                        $excel_array[$j]['Management Fee'] = FFM::dollar($payment1[$i]->mgmnt_fee);
                        $net_amount                        = $payment1[$i]->participant_share - $payment1[$i]->mgmnt_fee;
                        $excel_array[$j]['Net Amount']     = FFM::dollar($net_amount);
                        // $excel_array[$j]['Principal'] = FFM::dollar($payment1[$i]->principal);
                        // $excel_array[$j]['Profit'] = FFM::dollar($payment1[$i]->profit);
                        // $excel_array[$j]['Last Rcode'] = $payment1[$i]->last_rcode;
                        $excel_array[$j]['Last Payment Date'] = ($payment1[$i]->last_payment_date) ? date(\FFM::defaultDateFormat('db'), strtotime($payment1[$i]->last_payment_date)) : '';
                        // $excel_array[$j]['Last Payment Amount'] = FFM::dollar($payment1[$i]->last_payment_amount);
                        $excel_array[$j]['Net Participant RTR'] = FFM::dollar($payment1[$i]->invest_rtr-$payment1[$i]->mgmnt_fee_amount);
                        if ($groupBy == 3) {
                            
                            //$gross_balance = $payment1[$i]->invest_rtr - $payment1[$i]->total;
                            
                            // $gross_balance = $payment1[$i]->total;
                            $gross_balance = ($payment1[$i]->invest_rtr-$payment1[$i]->mgmnt_fee_amount) - ($payment1[$i]->net_balance_1 + $payment1[$i]->net_participant_share);  
                        } else {
                            $gross_balance = ($payment1[$i]->invest_rtr-$payment1[$i]->mgmnt_fee_amount) - ($payment1[$i]->net_balance_1 + $payment1[$i]->participant_share);
                        }  
                        $gross_balance = ($gross_balance>0) ? $gross_balance :0;                      
                        $excel_array[$j]['Net Participant RTR Balance'] = FFM::dollar($gross_balance);
                        
                        // $excel_array[$j]['Participant RTR Balance'] = FFM::dollar($payment1[$i]->invest_rtr - $payment1[$i]->mgmnt_fee_amount - ($payment1[$i]->net_balance + $payment1[$i]->participant_share - $payment1[$i]->mgmnt_fee));
                        
                        //    [$i+1, $payment1[$i]->merchant_name,$payment1[$i]->merchant_id,
                        //   date('m/d/Y',strtotime($payment1[$i]->payment_date)), FFM::dollar($payment1[$i]->participant_share),FFM::dollar($payment1[$i]->mgmnt_fee),
                        //   FFM::dollar($payment1[$i]->participant_share - $payment1[$i]->mgmnt_fee),FFM::dollar($payment1[$i]->principal),FFM::dollar($payment1[$i]->profit)
                        // ,$payment1[$i]->last_rcode, date('m/d/Y',strtotime($payment1[$i]->last_payment_date)),FFM::dollar($payment1[$i]->last_payment_amount),
                        //   FFM::dollar($payment1[$i]->invest_rtr),FFM::dollar($payment1[$i]->invest_rtr-$payment1[$i]->participant_share)];
                    }
                    $k = count($payment1);
                    $d = count($ALL_merchants);
                    //$count_t=$k+$d;
                    if ($whole_portfolio == 1) {
                        for ($m = 0,$n = $k + 11; $m < count($ALL_merchants); $m++,$n++) {
                            // $rtr_bal=$ALL_merchants[$m]->invest_rtr-$ALL_merchants[$m]->mgmnt_fee_amount-($ALL_merchants[$m]->net_balance+$ALL_merchants[$m]->participant_share-$ALL_merchants[$m]->mgmnt_fee);
                            $rtr_bal          = $ALL_merchants[$m]->invest_rtr - ($ALL_merchants[$m]->net_balance_1 + $ALL_merchants[$m]->participant_share);
                            $bal              = ($rtr_bal > 0) ? $rtr_bal : 0;
                            $t_participant1  += $ALL_merchants[$m]->participant_share;
                            $t_mangt_fee1    += $ALL_merchants[$m]->mgmnt_fee;
                            $t_mag_amount1   += $ALL_merchants[$m]->mgmnt_fee_amount;
                            $t_netamount1     = round($t_participant1 - $t_mangt_fee1, 4);
                            $t_net_balance1  += $ALL_merchants[$m]->net_balance;
                            $t_rtr1          += $ALL_merchants[$m]->invest_rtr;
                            $rtr_balance1    += $bal;
                            $excel_array[$n] = [
                                $i + 1,
                                ($investor['display_value'] == 'mid') ? $ALL_merchants[$m]->merchant_id : $ALL_merchants[$m]->name,
                                $ALL_merchants[$m]->status_name,
                                '',
                                '',
                                '',
                                '',
                                '',
                                '',
                                isset($ALL_merchants[$m]->last_rcode) ? $ALL_merchants[$m]->last_rcode : '--',
                                isset($ALL_merchants[$m]->last_payment_date) ? FFM::date($ALL_merchants[$m]->last_payment_date) : '--',
                                FFM::dollar($ALL_merchants[$m]->last_payment_amount),
                                FFM::dollar($ALL_merchants[$m]->invest_rtr),
                                FFM::dollar($bal),
                            ];
                            $i++;
                        }
                    }
                    // FFM::dollar($t_rtr1 + $t_rtr)
                    //FFM::dollar($rtr_balance1 + $rtr_balance)
                    if ($whole_portfolio == 1) {
                        // $excel_array[$k + 11 + $d] = ['', '', '', '', FFM::dollar($t_participant), FFM::dollar($t_mangt_fee), FFM::dollar($t_netamount), FFM::dollar($t_principal), FFM::dollar($t_profit), '', '', '', '', ''];
                        $excel_array[$k + 11 + $d] = ['', '', '', '', FFM::dollar($t_participant), FFM::dollar($t_mangt_fee), FFM::dollar($t_netamount), '', '', '', '', ''];
                    } else {
                        // $excel_array[11 + $k] = ['', '', '', FFM::dollar($t_participant), FFM::dollar($t_mangt_fee), FFM::dollar($t_netamount), FFM::dollar($t_principal), FFM::dollar($t_profit), '', '', '', '', ''];
                        $excel_array[11 + $k] = ['', '', '', FFM::dollar($t_participant), FFM::dollar($t_mangt_fee), FFM::dollar($t_netamount),'', '', '', '', ''];
                    }
                    //   $view = view('testView')->with(compact('payment1','invested_amount', 'ctd', 'investorArray', 'cash_in_hands', 'total_rtr', 'options',
                    //       'whole_portfolio','ALL_merchants'));
                    $footer = $k + 12;
                    $fileCSVName = $commonName.'.xlsx';
                    $export = new Data_arrExportPdfCsv($excel_array, $investorArray['investor_name'], $footer,$commonName);
                    $excel_array = [];
                    $fp = Excel::store($export, $fileCSVName, 'public');
                    $s3_file = Storage::disk('public')->get($fileCSVName);
                    $s3 = Storage::disk('s3');
                    $s3->put($fileCSVName, $s3_file, config('filesystems.disks.s3.privacy'));
                    // $fileCSVUrl = Storage::disk('s3')->url($fileCSVName);
                    $fileCSVUrl = Storage::disk('s3')->temporaryUrl($fileCSVName,Carbon::now()->addMinutes(20));
                    // $footer = $k + 12;
                    // $fileCSVName = 'investors/'.$commonName.'.xlsx';
                    // $export = new Data_arrExportPdfCsv($excel_array, $investorArray['investor_name'], $footer);
                    // $excel_array = [];
                    // $fp = Excel::store($export, $fileCSVName, 'public');
                    // $s3_file = Storage::disk('public')->get($fileCSVName);
                    // $s3 = Storage::disk('s3');
                    // $s3->put($fileCSVName, $s3_file, 'public');
                    // $fileCSVUrl = asset(\Storage::disk('s3')->url($fileCSVName));
                    // }
                    // $s3 = Storage::disk('s3');
                    // $s3->put($fileNamecsv,file_get_contents($fileNamecsv),'public');
                    //  Storage::disk('s3')->put($fileNamecsv, file_get_contents($export->store('csv', false, true)['full']),'public');
                    // Storage::disk('s3')->put($fileNamecsv, file_get_contents($export), 'public');
                    //$s3->put("/storage/" . $fileNamecsv, $s3_file);
                    // $load = Storage::disk('s3')->put($fileNamecsv, $pdf->output(), 'public');
                    if ($investor['file_type'] == 2) {
                        $fileUrl  = $fileCSVUrl;
                        $fileName = $fileCSVName;
                    }
                    if ($investor['file_type'] == 1) {
                        $fileUrl  = $filePDFUrl;
                        $fileName = $filePDFName;
                    }
                    $message = [];
                    $mail_status = 0;
                    $notification_id_arr = explode(',', $investor['notification_email']);
                    //$view='';
                    // $html='';
                    if ($filters['send_mail'] == 'true') {
                        $message['title']           = 'Payment Report Statement';
                        $message['content']         = $options.' Payment Statement Report';
                        $message['to_mail']         = ! empty($notification_id_arr) ? $notification_id_arr : [$investor['email']];
                        $message['options']         = $options;
                        $message['investor_name']   = $investor['name'];
                        $message['attach']          = $fileUrl;
                        $message['fileName']        = $fileName;
                        $message['link']            = "Please click<a rel='external' target='_blank' href=".url('AttachedFile/get/'.$fileName)."> here </a>";
                        $message['status']          = 'pdf_mail';
                        $message['heading']         = $options;
                        $message['subject']         = 'Syndication Report for '.$investor['name'];
                        $message['bcc']             = $this->admin_email;
                        $message['unqID']           = unqID();
                        $message['template_type']   = $templateType;
                        $message['recurrence_type'] = ucfirst(strtolower($title));
                        $mail_status = 1;
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['to_mail'] = $this->admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                        if ($filters['date_end']) {
                            $msg .= 'Statement Generated and Mail Sent Successfully for '.$investor['name'].' till '.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'<a class="btn btn-success" href='.$fileUrl.'>Click here to view</a><br>';
                        } else {
                            $msg .= 'Statement Generated Successfully for '.$investor['name'].'. <a class="btn btn-success" href='.$fileUrl.'>Click here to view</a><br>';
                        }
                    } else {
                        if ($filters['date_end']) {
                            $msg .= 'Statement Generated Successfully for '.$investor['name'].' till '.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'. <a class="btn btn-success" href='.$fileUrl.'>Click here to view</a><br>';
                        } else {
                            $msg .= 'Statement Generated Successfully for '.$investor['name'].'. <a class="btn btn-success" href='.$fileUrl.'>Click here to view</a><br>';
                        }
                    }
                    $payment_dt = DB::table('payment_investors')->leftJoin('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id')->where('payment_investors.user_id', $investor['id'])->select(DB::raw('MIN(participent_payments.created_at) as from_date'))->first();
                    // $statemnt_from_date = '';
                    // if($payment_dt){
                    //   $statemnt_from_date = $payment_dt->from_date;
                    // }
                    $Statements = Statements::create([
                        'file_name'      => $commonName,
                        'user_id'        => $investor['id'],
                        'from_date'      => ($filters['date_start'] != null) ? $filters['date_start'] : $payment_dt->from_date,
                        'to_date'        => ($filters['date_end'] != null) ? $filters['date_end'] : date('Y-m-d'),
                        'investor_portal'=> $filters['hide'],
                        'mail_status'    => $mail_status,
                        'creator_id'     => isset($filters['creator_id']) ? $filters['creator_id'] :Auth::user()->id,
                    ]);
                    $lastId=$Statements->id;
                } else {
                    if($filters['date_start']!=null && $filters['date_end']!=null){
                        if($filters['date_start'] > $filters['date_end']){
                            $msg .= 'No statement is generated for '.$investor['name'].' since '.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'<br>';   
                        } else {
                            $msg .= 'No statement is generated for '.$investor['name'].' during '.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).' to '.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'<br>';  
                        }
                    }
                    elseif ($filters['date_start']==null && $filters['date_end']!=null) {
                        $msg .= 'No statement is generated for '.$investor['name'].' till '.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'<br>';
                    }
                    elseif ($filters['date_start']!=null && $filters['date_end']==null) {
                        $msg .= 'No statement is generated for '.$investor['name'].' since '.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'<br>';
                    } else {
                        $msg .= 'No statement is generated for '.$investor['name'].'<br>';
                    }
                }
            }
        }
        return $msg;
    }
    /**
    $yesterday_date
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\User
     */
    public function createInvestor(Request $request) //Investor = Investor
    {
        if ($request->create_mode) {
            $auth_id = Auth()->user();
        } else {
            $auth_id = Auth()->user()->id;
        }

        if ($request->allocate_user) {
            $request->creator_id = $request->allocate_user;
        }

        $active_status = ($request->active_status == 1) ? 1 : 0;
        $auto_generation = ($request->auto_generation == 1) ? 1 : 0;
        $auto_invest = ($request->auto_invest == 1) ? 1 : 0;
        $label = json_encode($request->label);
        $label = str_replace('"', '', (string) $label);
        $request->merge(['active_status' =>$active_status]);
        $request->merge(['creator_id' =>$request->creator_id]);
        $request->merge(['notification_email' =>$request->notification_email]);
        //$request->merge(['groupby_recurence' =>$request->groupby_recurence]);
        $request->merge(['notification_recurence' =>$request->notification_recurence]);
        $request->merge(['company' =>$request->company]);
        $request->merge(['s_prepaid_status'=>$request->s_prepaid_status]);
        $request->merge(['file_type'=>$request->file_type]);
        $request->merge(['auto_generation'=>$auto_generation]);
        $request->merge(['auto_invest'=>$auto_invest]);
        $request->merge(['label'=>$label]);

        // $inside_array = [

        //     'attributes'=>$request->all(),

        // ];

        // $log_array = [

        //        'log_name'=>'Investor Creation',
        //        'description'=>'Investor Creation',
        //        'subject_id'=>rand(),
        //        'subject_type'=>\App\User::class,
        //        'causer_id'=>$auth_id,
        //        'causer_type'=>\App\User::class,
        //        'properties'=>$inside_array,

        // ];

        // $this->activityLogger($log_array);

        $request->notification_email = $request->notification_email;
        if ($request->investor_type != 2) {
            $request->interest_rate = 0;
        }

        $user = $this->table->create($request->only('name', 'management_fee', 'global_syndication', 'interest_rate', 'email', 'password', 'investor_type', 'creator_id', 'notification_email', 'notification_recurence', 'groupby_recurence', 'active_status', 'company', 's_prepaid_status', 'file_type', 'auto_generation', 'auto_invest', 'phone', 'cell_phone', 'label'));

        $userDetails = UserDetails::create(['user_id'=>$user->id]);
        $user->assignRole('investor');

        event(new UserHasAssignedInvestor($user, 'investor'));

        if ($userDetails) {
            if ($request->email_notification == 1) {
                // header('Content-type: text/plain');
                $message['title'] = $request->name.' Details';
                $message['subject'] = $request->name.' Details';
                $message['content'] = 'Investor Name : '.$request->name."\n Email :".$request->email." \n  Password :".$request->password;
                $message['to_mail'] = $request->email;
                $message['status'] = 'investor';
                $message['investor_name'] = $request->name;
                $message['username'] = $request->email;
                $message['password'] = $request->password;
                $message['unqID'] = unqID();

                try {
                    $email_template = Template::where([
                        ['temp_code', '=', 'INVTR'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['to_mail'] = $this->admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        return $user;
    }

    public function createAccount(Request $request)
    {
        if ($request->create_mode) {
            $auth_id = Auth()->user();
        } else {
            $auth_id = Auth()->user()->id;
        }
        if ($request->allocate_user) {
            $request->creator_id = $request->allocate_user;
        }
        $active_status = ($request->active_status == 1) ? 1 : 0;
        $auto_generation = ($request->auto_generation == 1) ? 1 : 0;
        $auto_invest = ($request->auto_invest == 1) ? 1 : 0;
        $auto_syndicate_payment = ($request->auto_syndicate_payment == 1) ? 1 : 0;
        $label = json_encode($request->label);
        $label = str_replace('"', '', (string) $label);
        $request->merge(['active_status' =>$active_status]);
        $request->merge(['creator_id' =>$request->creator_id]);
        $request->merge(['notification_email' =>$request->notification_email]);
        $request->merge(['company' =>$request->company]);
        $request->merge(['s_prepaid_status'=>$request->s_prepaid_status]);
        $request->merge(['auto_syndicate_payment'=>$auto_syndicate_payment]);
        $request->merge(['file_type'=>$request->file_type]);
        $request->merge(['notification_recurence' =>$request->notification_recurence]);
        $request->merge(['auto_generation'=>$auto_generation]);
        $request->merge(['auto_invest'=>$auto_invest]);
        $request->merge(['label'=>$label]);
        $request->merge(['velocity_owned'=>isset($request->velocity_owned) ? 1 :0]);
        $request->merge(['source_from'=>'admin']);
        $request->merge(['agreement_date'=>$request->agreement_date]);
        $request->merge(['login_board'=>$request->login_board]);

        if (isset($request->show_name_mid)) {
            $request->merge(['display_value' =>'mid']);
        } else {
            $request->merge(['display_value' =>'name']);
        }

        $request->notification_email = $request->notification_email;
        if ($request->investor_type != 2) {
            $request->interest_rate = 0;
        }
        $roleType = 'investor';
        if ($request->role_id) {
            $Roles = DB::table('roles')->find($request->role_id);
            $roleType = $Roles->name;
        }
        session_set('user_role', $roleType);
        $user = $this->table->create($request->only(
        'name',
        'management_fee',
        'global_syndication',
        'interest_rate',
        'email',
        'password',
        'investor_type',
        'creator_id',
        'notification_email',
        'notification_recurence',
        'groupby_recurence',
        'active_status',
        'company',
        's_prepaid_status',
        'file_type',
        'auto_generation',
        'auto_invest',
        'phone',
        'cell_phone',
        'label',
        'source_from',
        'auto_syndicate_payment',
        'display_value',
        'agreement_date',
        'contact_person',
        'login_board',
        'beneficiary',
        'velocity_owned'
        )
      );
        $TypeName = 'investor';
        $userDetails = UserDetails::create(['user_id'=>$user->id]);
        if ($request->role_id) {
            $Roles = DB::table('roles')->find($request->role_id);
            $TypeName = $Roles->name;
        }
        $user->assignRole($TypeName);
        event(new UserHasAssignedInvestor($user, 'investor'));
        if ($userDetails) {
            if ($request->email_notification == 1) {
                // header('Content-type: text/plain');
                $message['title'] = $request->name.' Details';
                $message['subject'] = $request->name.' Details';
                $message['content'] = $TypeName.' Name : '.$request->name."\n Email :".$request->email." \n  Password :".$request->password;
                $message['to_mail'] = $request->email;
                $message['status'] = 'account';
                $message['account_name'] = $request->name;
                $message['username'] = $request->email;
                $message['password'] = $request->password;
                $message['unqID'] = unqID();
                try {
                    $email_template = Template::where([['temp_code', '=', 'INVTR'], ['enable', '=', 1]])->first();
                    if ($email_template) {
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['to_mail'] = $this->admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        return $user;
    }

    /**
     * @param $id
     * @param $req
     *
     * @return bool
     */
    public function updateInvestor($id, $request)
    {
        if ($investor = $this->findInvestor($id)) {
            unset($investor['role_id']);
            if ($request->allocate_user) {
                $request->creator_id = $request->allocate_user;
            }

            $active_status = ($request->active_status == 1) ? 1 : 0;
            $auto_generation = ($request->auto_generation == 1) ? 1 : 0;
            $auto_invest = ($request->auto_invest == 1) ? 1 : 0;
            $label = json_encode($request->label);
            $label = str_replace('"', '', (string) $label);
            $auto_syndicate_payment = ($request->auto_syndicate_payment == 1) ? 1 : 0;

            //$kk = isset($request->funding_status) ? 1 : 0;
            //dd($kk);
            if (isset($request->show_name_mid)) {
                $request->merge(['display_value' =>'mid']);
            } else {
                $request->merge(['display_value' =>'name']);
            }

            $request->merge(['active_status' =>$active_status]);
            $request->merge(['funding_status' => isset($request->funding_status) ? 1 : 0]);
            // $request->merge(['creator_id' =>$request->creator_id]);
            $request->merge(['groupby_recurence' =>$request->groupby_recurence]);
            $request->merge(['notification_email' =>$request->notification_email]);
            $request->merge(['notification_recurence' =>$request->notification_recurence]);
            $request->merge(['company' =>$request->company]);
            $request->merge(['s_prepaid_status' =>$request->s_prepaid_status]);
            $request->merge(['file_type' =>$request->file_type]);
            $request->merge(['login_board' =>$request->login_board]);
            // $request->merge(['liquidity_exclude'=>$request->liquidity_exclude]);
            $request->merge(['whole_portfolio'=>$request->whole_portfolio]);
            $request->merge(['velocity_owned'=>$request->velocity_owned]);
            $request->merge(['auto_generation'=>$auto_generation]);
            $request->merge(['auto_invest'=>$auto_invest]);
            $request->merge(['label'=>$label]);
            $request->merge(['source_from'=>'admin']);
            $request->merge(['auto_syndicate_payment'=>$auto_syndicate_payment]);
            $request->merge(['agreement_date'=>$request->agreement_date]);
            if($request->investor_type!=1 && $request->investor_type!=3 && $request->investor_type!=4){
                $request->merge(['interest_rate'=>0]);   
            }

            $investor->update($request->only('name', 'funding_status', 'management_fee', 'global_syndication', 'interest_rate', 'email', 'investor_type', 'notification_email', 'notification_recurence', 'active_status', 'groupby_recurence', 's_prepaid_status', 'file_type', 'whole_portfolio', 'auto_generation', 'auto_invest', 'phone', 'cell_phone', 'label', 'source_from', 'auto_syndicate_payment', 'display_value', 'agreement_date', 'contact_person','login_board','beneficiary','velocity_owned'));
            if ($request->password != null) {
                $investor->password = $request->password;
            }
            if ($request->company != null) {
                $investor->company = $request->company;
            }
            $save = $investor->save();
            if ($request->email_notification == 1) {
                if ($request->password) {
                    // header('Content-type: text/plain');
                    $message['title'] = $request->name.' Details';
                    $message['subject'] = $request->name.' Details';
                    $message['content'] = 'Investor Name : '.$request->name."\n Email :".$request->email." \n  Password :".$request->password;
                    $message['to_mail'] = $request->email;
                    $message['status'] = 'investor';
                    $message['investor_name'] = $request->name;
                    $message['username'] = $request->email;
                    $message['password'] = $request->password;
                    $message['unqID'] = unqID();
                    //try

                    {
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['to_mail'] = $this->admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                    }
                    /* catch (\Exception $e) {
                         echo $e->getMessage();
                     }*/
                }
            }

            return true;
        }

        return false;
    }

    // public function activityLogger($log_array)
    // {
    //     $array = isset($log_array[0]) ? $log_array[0] : '';
    //     $log = '';

    //     if ($array) {
    //         $log = Activity::insert($log_array);
    //     } else {
    //         $log = Activity::create($log_array);
    //     }

    //     if ($log) {
    //         return 1;
    //     }
    // }

    /**
     * @param $id
     *
     * @return bool
     */
    public function findInvestor($id)
    {
        $user = $this->table->find($id);
        if (! empty($user->roles->toArray())) {
            if (! empty($user->roles[0]->toArray())) {
                $user['role_id'] = $user->roles[0]->id;
            }
        }

        return $user;

        return ($user->hasRole('investor')) ? $user : false;
    }

    public function findAccount($id)
    {
        $user = $this->table->find($id);
        if (! empty($user->roles->toArray())) {
            if (! empty($user->roles[0]->toArray())) {
                $user['role_id'] = $user->roles[0]->id;
            }
        }

        return $user;
    }

    public function duplicateDbGenerate(Request $request)
    {
        $ts = time();
        $date = date('d/m/Y');
        $test = explode('/', $date);
        $date_change = $test[0].'_'.$test[1].'_'.$test[2];
        $new_db_name = 'investor_portal_'.$date_change.'_'.$ts;
        $returnVar = null;
        $output = null;
        $ds = DIRECTORY_SEPARATOR;
        $db = session('DB_DATABASE');
        $database = ! empty($db) ? $db : config('app.database');
        $host = config('app.db_url');
        $username = config('app.username');  // user name
        $password = config('app.password'); // password
        $ts = time();
        DB::statement(' CREATE SCHEMA '.$new_db_name);

        // database duplication
        $command = 'mysqldump -h '.$host.' -u '.$username.' -p'.$password.' '.$database.' | mysql -h '.$host.' -u '.$username.' -p'.$password.' '.$new_db_name;
        $res = exec($command, $output, $returnVar); // execute db creation
        $msg = $new_db_name.' duplicate dp generated successfully';
        $request->session()->flash('message', $msg);

        return 1;
    }

    public function equityInvestorReport($investors)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $rate = Settings::value('rate');
	    EquityInvestorReport::EquityInvestorReportCheck();
        $data1 = new EquityInvestorReport();
        if ($investors && is_array($investors)) {
            $data1 = $data1->whereIn('investor_id', $investors);
        }
        $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $data1 = $data1->join('users','users.id','equity_investor_reports.investor_id')->whereNotIn('users.company',$disabled_companies);
        $data = $data1->get();

        return $data;
    }

    /*


      Calling this function in investor login, admin side should be the same function.
    */

    public function investorDashboard($userId, $investor_type)
    {
        $settings = Settings::first();
        $defaultPayment = $settings->default_payment;
        $default_rate = $settings->rate / 100;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $setInvestors = [];
        $investors = Role::whereName('investor')->first()->users;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $investors = $investors->where('company', $userId);
            } else {
                $investors = $investors->where('creator_id', $userId);
            }
        }
        foreach ($investors as $key => $investor) {
            $setInvestors[] = $investor->id;
        }
        /*
        Directely calculating following variables:
        $pre_paid_t
        $ctd2
        $total_funded
        $paid_syndication
        $fees
        $total_rtr
        */

        $over_payment_accounts = $this->role->allOverPaymentAccount()->pluck('id')->toArray();
        $agent_fee_accounts = $this->role->allAgentFeeAccount()->pluck('id')->toArray();
        $investments_sum = MerchantUser::whereIn('merchant_user.status', [1, 3])
        ->whereHas('investors', function ($query) use ($userId) {
            $query->where('merchant_user.user_id', $userId);
        })
        ->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
        ->with('merchant')
        ->whereHas('merchant', function ($query1) {
            $query1->where('active_status', 1);
        })->select(DB::raw('count(merchants.id) as merchant_count'),
        DB::raw('sum((merchant_user.invest_rtr*((merchant_user.mgmnt_fee)/100))) as total_fee'),
        DB::raw('sum(merchant_user.invest_rtr) as total_rtr'),
        DB::raw('sum(merchant_user.mgmnt_fee) as mgmnt_fee'),
        DB::raw('sum(merchant_user.amount) as invested_amount'),
        DB::raw('SUM(merchant_user.invest_rtr *('.$default_rate.') ) as default_rate_invest_rtr '),
        DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee + merchant_user.up_sell_commission) as total_investment'),
        DB::raw('sum(merchant_user.under_writing_fee) as under_writing_fee_total'),
        DB::raw('sum(merchant_user.up_sell_commission) as up_sell_commission_total'),
        // DB::raw('sum(merchant_user.under_writing_fee*merchant_user.amount/100) as under_writing_fee_total'),
        // DB::raw('sum(IF(s_prepaid_status=0,merchant_user.syndication_fee,0)) as pre_paid_t222'),
        DB::raw('sum(merchant_user.pre_paid) as pre_paid_t'),
        DB::raw('sum(merchant_user.commission_amount) as commission_total'),
        DB::raw('sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd,
        SUM( IF(actual_paid_participant_ishare>invest_rtr,(actual_paid_participant_ishare-invest_rtr)*(1- (merchant_user.mgmnt_fee)/100),0) ) as overpayment'),
        DB::raw('sum(((( (amount * IF(old_factor_rate,old_factor_rate,factor_rate) ) *(100-merchant_user.mgmnt_fee)/100)-(merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission)))*IF(advance_type="weekly_ach",52,255)/merchants.pmnts)/sum(merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission)*100 as bleded_i_rate')
        );
        if (in_array($userId, $agent_fee_accounts)) {
            $investments_sum = $investments_sum->where('merchant_user.actual_paid_participant_ishare', '<>', 0);
        }

        if (in_array($userId, $over_payment_accounts)) {
            $investments_sum = $investments_sum->where('merchant_user.actual_paid_participant_ishare', '<>', 0);
        }
        $invvstmnt_sum_for_blended = clone $investments_sum;

        $investments_sum = $investments_sum->first();
        /*
        blendedrate =  (profit / invested amount  * 100 ) * yearly days / terms days
        */
        $invvstmnt_sum_for_blended = $invvstmnt_sum_for_blended->whereIn('sub_status_id', [1, 5, 16, 2, 13, 12])->first();
        $blended_rate = $invvstmnt_sum_for_blended->bleded_i_rate; //Blended rate calculation

        /*
        Invested Amount
        */
        $invested_amount = $investments_sum->invested_amount + $investments_sum->pre_paid_t + $investments_sum->commission_total + $investments_sum->under_writing_fee_total + $investments_sum->up_sell_commission_total;
        /*
        Funded Amount
        */
        $funded_amount = $investments_sum->invested_amount;
        /*
        RTR
        */
        $overpayment = DB::table('payment_investors')->whereuser_id($userId)->sum('actual_overpayment');
        $overpayment+= DB::table('carry_forwards')->whereinvestor_id($userId)->where('carry_forwards.type', 1)->sum('amount');
        $total_rtr = $investments_sum->total_rtr + $overpayment - $investments_sum->total_fee;
        $total_rtr = FFM::adjustment($total_rtr, $userId);
        $total_investment_amount = $investments_sum->total_investment;
        $merchant_count = $investments_sum->merchant_count;
        // $bleded_amount = $investments_sum->bleded_amount;
        $under_writing_fee = $investments_sum->under_writing_fee_total;
        /*
        CTD
        */
        $ctd = $investments_sum->ctd;
        /*
        Find liquidity
        */
        $user_details = UserDetails::where('user_id', $userId)->first();
        if (($user_details)) {
            $liquidity = $user_details->liquidity;
            $reserved_liquidity = $user_details->reserved_liquidity_amount;
        //$liquidity=$liquidity-$under_writing_fee;
        } else {
            $liquidity = 0;
        }
        /*
        ########################################################
        ################# Default values #######################
        #######################################################
        */
        $default_date = now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        $default_investments_sum = MerchantUser::whereIn('merchant_user.status', [1, 3])->whereHas('investors', function ($query) use ($userId) {
            $query->where('merchant_user.user_id', $userId);
        })->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
        ->whereHas('merchant', function ($query1) {
            $query1->where('active_status', 1);
            // $query1->where('merchants.id', 9666);
            $query1->whereIn('sub_status_id', [4, 22]);
            $query1->where('old_factor_rate', 0);
        })
        ->select(DB::raw('sum(merchant_user.invest_rtr) as total_default_rtr'),
        DB::raw('sum( (merchant_user.invest_rtr * ((merchant_user.mgmnt_fee)/100) )  ) as total_default_fee'),
        DB::raw('sum(merchant_user.invest_rtr *('.$default_rate.') ) as default_rate_invest_rtr '),
        DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as total_investment'),
        DB::raw('sum(((merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission)-(IF((merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission)<(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee),(merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission),(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee))))) as default_amount'),
        DB::raw(' sum(((merchant_user.invest_rtr+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)),0))-(merchant_user.invest_rtr*(merchant_user.mgmnt_fee)/100+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)*(merchant_user.mgmnt_fee)/100),0))-(IF(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,0)))) as total_rtr'),
        DB::raw('sum(merchant_user.pre_paid) as pre_paid_t'),
        DB::raw('sum(merchant_user.commission_amount) as commission_total'),
        DB::raw('sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd')
        )
        ->first();
        $settled_investments_sum = MerchantUser::whereIn('merchant_user.status', [1, 3])->whereHas('investors', function ($query) use ($userId) {
            $query->where('merchant_user.user_id', $userId);
        })->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
        ->whereHas('merchant', function ($query1) {
            $query1->where('active_status', 1);
            $query1->whereIn('sub_status_id', [18, 19, 20]);
            $query1->where('old_factor_rate', 0);
        })
        ->select(
        DB::raw('sum(((merchant_user.invest_rtr)-(merchant_user.invest_rtr*(merchant_user.mgmnt_fee)/100)-(IF(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee,0)))) as total_rtr'),
        )
        ->first();
        $settings = Settings::first();
        $default_payment = $settings->default_payment;
        $defaulted_rtr = $default_investments_sum->total_default_rtr - $default_investments_sum->total_default_fee;
        $defaulted_ctd = $default_investments_sum->ctd;
        $default_rate_rtr = $investments_sum->default_rate_invest_rtr - $default_investments_sum->default_rate_invest_rtr;
        // $defaulted_balance = $defaulted_rtr - $defaulted_ctd;
        $defaulted_balance = $default_investments_sum->total_rtr;
        $settled_balance = $settled_investments_sum->total_rtr;
        $default_amount = $defaulted_rtr - $defaulted_ctd;
        $default_investments = $default_investments_sum->total_investment;
        $cost_for_ctd = DB::table('payment_investors')->join('merchants', 'merchants.id', 'payment_investors.merchant_id')
        ->where('payment_investors.user_id', $userId)
        ->whereNotIn('merchants.sub_status_id', [4, 22]);
        // if (empty($permission)) {
        //     $cost_for_ctd = $cost_for_ctd->whereIn('payment_investors.user_id', $setInvestors);
        // }
        $profit = $cost_for_ctd->sum('profit');
        $cost_for_ctd = $cost_for_ctd->sum('principal');
        $current_value = DB::table('merchants')->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
        ->where('merchant_user.user_id', $userId)->select(DB::raw(' sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.up_sell_commission + merchant_user.pre_paid) as invested_amount'), DB::raw('sum(merchant_user.actual_paid_participant_ishare) as paid_participant_ishare')
        )->whereIn('merchant_user.status', [1, 3])
        ->whereNotIn('merchants.sub_status_id', [4, 22])
        ->first();
        // $c_invested_amount = $default_investments - $cost_for_ctd;
        // $c_invested_amount = ($current_value->invested_amount - $cost_for_ctd)-($default_investments_sum->default_amount+$overpayment);
        $c_invested_amount = $current_value->invested_amount - $cost_for_ctd;
        if ($c_invested_amount < 0) {
            $c_invested_amount = 0;
        }
        if ($default_payment == 1) {
            $default_invested_amount = $default_investments_sum->total_investment - $defaulted_ctd - $overpayment;
            // $default_invested_amount = $default_investments_sum->default_amount - $overpayment;
            $default_percentage = 0;
            if ($total_investment_amount != 0) {
                $default_percentage = ($default_invested_amount > 0) ? ($default_invested_amount / ($total_investment_amount) * 100) : 0;
            }
        } elseif ($default_payment == 2) {
            $default_percentage = ($total_rtr > 0) ? (($default_amount - $overpayment) / ($total_investment_amount) * 100) : 0;
            //$default_percentage = ($total_rtr > 0) ? (($default_investments_sum->total_rtr - $overpayment) / ($total_investment_amount) * 100) : 0;
        }
        $total_rtr = $total_rtr - $defaulted_balance - $settled_balance;
        $total_requests = 0;
        $portfolio_value = (($total_rtr + $liquidity) - $ctd) - $overpayment;
        $substatus = SubStatus::orderBy('name')->pluck('name', 'id');
        // all investors credits
        $principal_investment = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->whereIn('transaction_category', [1, 12])->where('investor_id', $userId)->sum('amount');
        /*    $transaction_query3= InvestorTransaction::where('date', '<', NOW());
        $transaction_query3 = $transaction_query3->whereIn('transaction_category',[12,13,14])->select(
        DB::raw('sum(IF(transaction_category,amount,0)) as return_of_pr'))->first();
        */
        $principal_investment = $principal_investment; // + $transaction_query3->return_of_pr;
        /*
        Only for investor side login
        */
        $total_credit = InvestorTransaction::where('transaction_type', 2)->where('investor_id', $userId)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
        $pending_debit_ach_request = InvestorAchRequest::whereinvestor_id($userId)->whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['debit', 'same_day_debit'])->sum('amount');
        $pending_credit_ach_request = InvestorAchRequest::whereinvestor_id($userId)->whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['credit', 'same_day_credit'])->sum('amount');
        $average = 0;
        $velocity_dist = InvestorTransaction::where('transaction_category', 7)->where('investor_id', $userId)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
        $investor_dist = InvestorTransaction::where('transaction_category', 6)->where('investor_id', $userId)->where('status', InvestorTransaction::StatusCompleted)->sum('amount');
        $all_debits = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->where('transaction_type', 1)->where('investor_id', $userId)->sum('amount');
        $current_portfolio = ($portfolio_value > 0) ? $portfolio_value * 0.5 : 0;
        $debit_interest = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->where('transaction_category', 3)->where('investor_id', $userId)->sum('amount');
        $portfolio_earnings = $portfolio_value - $all_debits;
        if ($principal_investment > 0) {
            $irr = ($portfolio_earnings - $principal_investment) / $principal_investment * 100;
        } else {
            $irr = 0;
        }
        $transaction_query = InvestorTransaction::where('investor_id', $userId)->where('date', '<', NOW())->where('status', InvestorTransaction::StatusCompleted);
        //$transaction_query2 = clone $transaction_query;
        $transaction_query = $transaction_query->where('transaction_category', 1);
        $start_date = "'".$transaction_query->min('date')."'";
        $average_query = $transaction_query->select(DB::raw('sum(amount) as total_credit'),
        DB::raw("sum(amount* TIMESTAMPDIFF(day,investor_transactions.date,NOW()) / TIMESTAMPDIFF(day,$start_date,NOW())) as average"))
        ->first();
        $average = $average_query->average;
        $principal_investment_arr = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->whereIn('transaction_category', [1, 12])->where('investor_id', $userId)->select(DB::raw('MAX(DATEDIFF(NOW(),date)+1) as days'), DB::raw('sum(amount*(DATEDIFF(NOW(),date)+1)) as tot_amount'))->first();
        $average_principal_investment = '';
        if ($principal_investment_arr) {
            $average_principal_investment = ($principal_investment_arr->days != 0) ? $principal_investment_arr->tot_amount / $principal_investment_arr->days : $principal_investment_arr->tot_amount;
        }
        $total_profit = DB::table('payment_investors')->join('merchants', 'merchants.id', 'payment_investors.merchant_id')->where('payment_investors.user_id', $userId)->sum('profit');
        $bill_transaction = InvestorTransaction::getTransactionSum($userId, 10);
        $bill_transaction = -$bill_transaction;
        $default_investments = $default_investments_sum->total_investment;
        $default_amnt = $default_investments - $defaulted_ctd;
        $net_profit = $total_profit - $bill_transaction - $default_amnt;
        /*$overpayment_ids     = [59,60,61,62,63,64,65,66,76,81,82,83,84,85,86,87,88,121,144,145,146,147,148,149,150,151,157,158,167,177,178,179,180,189,190,191,192,197,198,199,200,201,202];
        $net_profit          = (in_array($userId,$overpayment_ids))? ($net_profit+$overpayment) : $net_profit;
        $portfolio_value     = (in_array($userId,$overpayment_ids))? ($portfolio_value+$overpayment) : $portfolio_value;*/
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        if ($OverpaymentAccount) {
            if ($OverpaymentAccount->id != $userId) {
                $net_profit += $overpayment;
            }
        }
        $portfolio_value += $overpayment;
        $portfolio_value   = round($portfolio_value,2);
        $all_debits = InvestorTransaction::getTransactionSum($userId, 0, 1, 1);
        $anticipated_rtr = $total_rtr - $ctd - $default_rate_rtr;
        if ($anticipated_rtr < 0) {
            $anticipated_rtr = 0;
        }
        $net_rtr = $investments_sum->total_rtr;
        $array = [
            'liquidity'                   =>$liquidity,
            'reserved_liquidity'          =>$reserved_liquidity,
            'invested_amount'             =>$invested_amount,
            'funded_amount'               =>$funded_amount,
            'net_rtr'                     =>$net_rtr,
            'ctd'                         =>$ctd,
            'blended_rate'                =>$blended_rate,
            'default_percentage'          =>$default_percentage,
            'merchant_count'              =>$merchant_count,
            'total_rtr'                   =>$total_rtr,
            'average'                     =>$average,
            'investor_type'               =>$investor_type,
            'velocity_dist'               =>$velocity_dist,
            'investor_dist'               =>$investor_dist,
            'total_requests'              =>$total_requests,
            'portfolio_value'             =>$portfolio_value,
            'principal_investment'        =>$principal_investment,
            'average_principal_investment'=>$average_principal_investment,
            'debit_interest'              =>$debit_interest,
            'irr'                         =>$irr,
            'total_credit'                =>$total_credit,
            'current_portfolio'           =>$current_portfolio,
            'substatus'                   =>$substatus,
            'overpayment'                 =>$overpayment,
            'c_invested_amount'           =>$c_invested_amount,
            'anticipated_rtr'             =>$anticipated_rtr,
            'profit'                      =>$net_profit,
            'pending_debit_ach_request'   =>$pending_debit_ach_request,
            'pending_credit_ach_request'  =>$pending_credit_ach_request,
            'paid_to_date'                => -$all_debits,
        ];

        return $array;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deleteInvestor($id)
    {
        if ($investor = $this->findInvestor($id)) {
            return $investor;
        }
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deleteBranchManager($id)
    {
        if ($branch = $this->findBranchManager($id)) {
            return $branch;
        }
    }

    public function deleteCollectionUser($id)
    {
        if ($branch = $this->findCollectonUser($id)) {
            return $branch;
        }
    }

    public function lenderReport()
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $subinvestors = [];

        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->where('creator_id', $userId);

            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }

        $data1 = User::select('users.id', 'users.name', 'users.interest_rate')
                 ->where('active_status', 1)
                 ->leftJoin('user_has_roles', function ($join) {
                     $join->on('users.id', '=', 'user_has_roles.model_id');
                     $join->where('user_has_roles.role_id', 4);
                 })
                ->withCount([
                   'merchants AS commission' => function ($query) {
                       $query->select(DB::raw('SUM(commission) as tcommission'));
                   },
                ]);
        //     ->withCount(['merchants'=> function ($query) use ($subinvestors,$permission) {
        //           $query->where('active_status',1);
        //           $query->whereHas('investmentData', function ($query1) use ($subinvestors,$permission) {
        //              if (empty($permission)) {
        //                  $query1->whereIn('user_id', $subinvestors);
        //              }
        //         });
        //      },
        // ]);

        return $data = $data1;
    }

    public function accuredInterestReport($investors)
    {
        $data = User::select('users.id', 'users.name', 'users.interest_rate')->where('users.investor_type', 1)
                   ->join('investor_transactions', 'investor_transactions.investor_id', 'users.id')
                   ->where('investor_transactions.status', 1)
                   ->where('investor_transactions.transaction_type', 2);
        if ($filter_investors && is_array($filter_investors)) {
            $data = $data->whereIn('id', $filter_investors);
        }
    }

    public function investorProfitReport($investors)
    {
        // removed paid_syndication_fee from merchnat_user table

        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $rate = Settings::value('rate');

        $data1 = User::where('investor_type', 1)->where('company', 1)->select(
            'users.id',
            'users.name',
            'users.interest_rate',
            DB::raw('(SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity'),
            DB::raw('(SELECT SUM(investor_transactions.amount) FROM investor_transactions WHERE users.id = investor_transactions.investor_id and transaction_type=2 and status=1 and transaction_category NOT IN (12,13,14) ) credit_amount'))->where('users.active_status', 1)
                 ->join('user_has_roles', function ($join) {
                     $join->on('users.id', '=', 'user_has_roles.model_id');
                     $join->where('user_has_roles.role_id', 2);
                 })
                ->withCount([
                   'participantPayment AS default_pay_rtr' => function ($query) {
                       $query->select(DB::raw('SUM(participant_share-mgmnt_fee) as default_pay_rtr'));
                       $query->whereHas('merchant', function ($query1) {
                           $query1->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1); // whre no default merchants.
                       });
                   },
                ])
               ->withCount([
                'investmentData1 AS ctd' => function ($query) {
                    $query->select(DB::raw('SUM(paid_participant_ishare - paid_mgmnt_fee)


+

 2*

SUM( IF(paid_participant_ishare>invest_rtr,(invest_rtr-(paid_participant_ishare))*(1- (merchant_user.mgmnt_fee)/100 ),0) )







                     as ctd')); //-paid_syndication_fee-paid_mgmnt_fee
                    $query->whereHas('merchant', function ($query1) {
                        $query1->where('active_status', '=', 1); // whre no default merchants.
                    });
                },

               ])
               ->withCount([

                'investmentData2 AS fees' => function ($query) use ($rate) {
                    $query->select(DB::raw("SUM(( (invest_rtr - invest_rtr* ((

                        IF(s_prepaid_status=0,0,0)
                        +merchant_user.mgmnt_fee)/100)

                      )-(invest_rtr * ($rate / 100) )   ))


                       as fees")

                );
                    $query->where('status', 1); //- (mgmnt_fee+syndication_fee)
                    $query->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
                    $query->where('active_status', '=', 1);
                    $query->whereNotIn('sub_status_id', [4, 22]);
                },

               ]);

        if ($investors && is_array($investors)) {
            $data1 = $data1->whereIn('users.id', $investors);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $data1 = $data1->where('company', $userId);
            } else {
                $data1 = $data1->where('creator_id', $userId);
            }
        }
        $data = $data1; //changed to all investors. ->where('users.investor_type', 1);

        return $data;
    }

    public function totalPortfolioEarnings($investors)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $rate = Settings::value('rate');

        $defaultPayRtrQuery = DB::table('payment_investors')
            ->select(DB::raw('SUM(actual_participant_share-mgmnt_fee) as default_pay_rtr, payment_investors.user_id'))
            ->join('merchants', 'merchants.id', '=', 'payment_investors.merchant_id')
            ->whereIn('merchants.sub_status_id', [4, 22])->where('merchants.active_status', '=', 1)
            ->groupBy('payment_investors.user_id');
        $underWritingFeeQuery = DB::table('merchant_user')
            ->select(DB::raw('SUM(under_writing_fee) as under_writing_fee, merchant_user.user_id, SUM(( invest_rtr - invest_rtr * ((merchant_user.mgmnt_fee) / 100) - (invest_rtr * (0 / 100))) ) AS fees'))
            ->join('merchants', 'merchants.id', '=', 'merchant_user.merchant_id')
            ->whereIn('merchant_user.status', [1, 3])
            ->whereNotIn('merchants.sub_status_id', [4, 22])
            ->where('merchants.active_status', '=', 1)
            ->groupBy('merchant_user.user_id');

        $overpaymentQuery = DB::table('payment_investors')
            ->select(DB::raw(' SUM(overpayment) AS overpayment, payment_investors.user_id'))
            ->groupBy('payment_investors.user_id');

        /**
         * - IF(
        actual_paid_participant_ishare > invest_rtr,
        (
        actual_paid_participant_ishare - invest_rtr
        ) * (1- (merchant_user.mgmnt_fee) / 100),
        0
        )
         */
        $ctdQuery = DB::table('merchant_user')
            ->select(DB::raw(' SUM(
			      actual_paid_participant_ishare - paid_mgmnt_fee ) AS ctd, merchant_user.user_id'))
            ->join('merchants', 'merchants.id', '=', 'merchant_user.merchant_id')
            ->whereIn('merchant_user.status', [1, 3])
            ->where('merchants.active_status', '=', 1)
            ->groupBy('merchant_user.user_id');

        $data1 = User::select(
            'users.id',
            'users.name',
            DB::raw('user_details.liquidity,
            bills_trans.bills,
            distributions_trans.distributions,
            credit_amount_trans.credit_amount,
            debit_amount_trans.debit_amount,
            default_pay_rtr_trans.default_pay_rtr,
            under_writing_fee_trans.under_writing_fee,
            under_writing_fee_trans.fees,
            ( ctd_trans.ctd ) as ctd
            
            ')
            //( ctd_trans.ctd  - overpayment_trans.overpayment) as ctd
        )
            ->join('user_has_roles', function ($join) {
                $join->on('users.id', '=', 'user_has_roles.model_id');
                $join->where('user_has_roles.role_id', 2);
            })
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->leftJoin(DB::raw('(SELECT ABS(SUM(investor_transactions.amount)) as bills   , investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 and investor_transactions.status=1 and transaction_type=1 and transaction_category IN (10) GROUP BY investor_transactions.investor_id) as bills_trans'), 'bills_trans.investor_id', '=', 'users.id')
            ->leftJoin(DB::raw('(SELECT SUM(investor_transactions.amount) as distributions, investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 and investor_transactions.status=1 and transaction_type=1 and transaction_category IN (6,7) GROUP BY investor_transactions.investor_id) as distributions_trans'), 'distributions_trans.investor_id', '=', 'users.id')
            ->leftJoin(DB::raw('(SELECT SUM(investor_transactions.amount) as credit_amount, investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 and investor_transactions.status=1 and transaction_type=2 GROUP BY investor_transactions.investor_id) as credit_amount_trans'), 'credit_amount_trans.investor_id', '=', 'users.id')
            ->leftJoin(DB::raw('(SELECT SUM(investor_transactions.amount) AS debit_amount , investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 and investor_transactions.status=1 and transaction_type=1 GROUP BY investor_transactions.investor_id) as debit_amount_trans'), 'debit_amount_trans.investor_id', '=', 'users.id')
            ->leftJoin(DB::raw('('.modelQuerySql($defaultPayRtrQuery).') as default_pay_rtr_trans'), 'default_pay_rtr_trans.user_id', '=', 'users.id')
            ->leftJoin(DB::raw('('.modelQuerySql($underWritingFeeQuery).') as under_writing_fee_trans'), 'under_writing_fee_trans.user_id', '=', 'users.id')
            ->leftJoin(DB::raw('('.modelQuerySql($ctdQuery).') as ctd_trans'), 'ctd_trans.user_id', '=', 'users.id');
        //->leftJoin(DB::raw('('.modelQuerySql($overpaymentQuery).') as overpayment_trans'), 'overpayment_trans.user_id', '=', 'users.id');

        if ($investors && is_array($investors)) {
            $data1 = $data1->whereIn('users.id', $investors);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $data1 = $data1->where('company', $userId);
            } else {
                $data1 = $data1->where('creator_id', $userId);
            }
        }
        $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
        $data1 = $data1->whereNotIn('users.company', $disabled_companies);
        $data1 = $data1->orderBy('credit_amount', 'DESC');
        $data = $data1;
        $data = $data->get();

        $data2 = DB::table('investor_transactions')->groupBy('investor_id')
            ->whereIn('transaction_category', [12, 13, 14]);

        if ($investors && is_array($investors)) {
            $data2 = $data2->whereIn('users.id', $investors);
        }

        if (empty($permission)) {
            $data2 = $data2->where('creator_id', $userId);
        }
        $data2 = $data2->pluck(DB::raw('sum(amount)'), 'investor_id')->toArray();
        // $data2 = $data2->where('users.investor_type', 2);


        foreach ($data as $key => $value) {
            $return_amount = 0;

            $return_amount = isset($data2[$data[$key]->id]) ? $data2[$data[$key]->id] : 0;
            $data[$key]->credit_amount = ($data[$key]->credit_amount) - $return_amount;
            // code...
        }

        return $data;
    }

    public function investorList($investor_type, $company, $active_status, $active_status_companies, $liquidity, $auto_invest_label, $role_id, $auto_generation = null, $notification_recurence = null,$velocity_owned = false)
    {
        if (\Auth::user()) {
            $default = Settings::first();
            $sub = $this->role->allSubAdmin();
            //  (Auth::user()->hasRole(['company']))?$permission = 0 : $permission = 1;
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;

            $return = User::select('users.id', 'users.interest_rate', 'users.created_at', 'users.email', 'users.name', 'users.updated_at', 'user_details.liquidity','user_details.reserved_liquidity_amount', 'roles.name as role_name')
            ->join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'user_has_roles.role_id');

            if ($role_id) {
                $return = $return->where('roles.id', $role_id);
            } else {
                $return = $return->whereIn('roles.id', [User::INVESTOR_ROLE, User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE]);
            }
            if($velocity_owned){
                $return = $return->where('users.velocity_owned', 1);
            }

            $return = $return->withCount(['investmentData1 AS rtr' => function ($query) use ($default) {
                $query->select(DB::raw('sum( (invest_rtr-(invest_rtr * (merchant_user.mgmnt_fee/100) ) ))
                  as rtr'));
                $query->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
                $query->where('merchants.active_status', '=', 1);
                //$query->whereNotIn('merchants.sub_status_id', [4, 22]);
                $query->whereIn('merchant_user.status', [1, 3]);
            },
            'investorTransactions AS amount' => function ($query) {
                $query->select(DB::raw('SUM(amount) as amount'));
                $query->whereIn('transaction_category', [1, 12])->where('status', InvestorTransaction::StatusCompleted);
            },
            'investmentData AS ctd' => function ($query) {
                $query->select(DB::raw('SUM(paid_participant_ishare - merchant_user.paid_mgmnt_fee)
              as ctd'));
                $query->whereHas('merchant', function ($query1) {
                    $query1->where('active_status', '=', 1); // whre no default merchants.
                });
            },
            'investmentData AS default_pay_rtr' => function ($query) {
                $query->select(DB::raw('SUM(paid_participant_ishare - merchant_user.paid_mgmnt_fee) as ctd'));
                $query->whereHas('merchant', function ($query1) {
                    $query1->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1); // whre no default merchants.
                });
            },
             'participantPayment AS overpayment' => function ($query) {
                 $query->select(DB::raw('sum(overpayment) as overpayment'));
             },

            'investmentData AS defaulted_balance' => function ($query) {
                $query->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
                $query->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1); // whre no default merchants.
                $query->select(DB::raw('

            
           sum(



    (

            (merchant_user.invest_rtr +  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) ) , 0 ))

             -

          ( merchant_user.invest_rtr *(merchant_user.mgmnt_fee)/100
            +  IF(old_factor_rate>factor_rate, ( merchant_user.amount* (old_factor_rate-factor_rate) *(merchant_user.mgmnt_fee)/100  ) , 0 ))

            -
              ( IF(
              merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee,

              merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee,0


              ))


              )

            )

           as defaulted_balance'
       ));
            // $query->whereHas('merchant', function ($query1) {
                //     $query1->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1); // whre no default merchants.
                // });
            },
             ]);
            if (empty($permission)) {
                if (Auth::user()->hasRole(['company'])) {
                    $return = $return->where('users.company', $userId);
                } else {
                    $return = $return->where('users.creator_id', $userId);
                }
            }

            if ($company != 0) {
                $return = $return->where('users.company', $company);
            }

            if ($auto_invest_label) {
                //$return = $return->where('users.auto_invest',1);
                $return = $return->whereRaw('JSON_CONTAINS(label,"'.$auto_invest_label.'")');
            }

            //  elseif ($auto_invest == 2) {
            //   $return = $return->where('users.auto_invest', 0);
            // }

            if ($auto_generation == 1) {
                $return = $return->where('users.auto_generation', 1);
            } elseif ($auto_generation == 2) {
                $return = $return->where('users.auto_generation', 0);
            }
            if ($notification_recurence) {
                $return = $return->where('users.notification_recurence', $notification_recurence);
            }

            if ($active_status == 1) {
                $return = $return->where('active_status', 1);
            } elseif ($active_status == 2) {
                $return = $return->where('active_status', 0);
            }

            if ($active_status_companies == 1) {
                $return = $return->whereHas('company_relation', function ($query) {
                    $query->where('company_status',1);
                });
            } elseif ($active_status_companies == 2) {
                $return = $return->whereHas('company_relation', function ($query) {
                    $query->where('company_status',0);
                });
            }

            if ($investor_type) {
                $return = $return->where('users.investor_type', $investor_type);
            }

            // if ($liquidity != '') {
            //     $return = $return->where('liquidity_exclude', $liquidity);
            // }

            // if ($subadmin) {
            //     $return = $return->where('users.creator_id', $subadmin);
            // }

            $return = $return->leftJoin('user_details', 'users.id', 'user_details.user_id');

            if ($default->hide == 1) {
                $return = $return->where('active_status', 1);
            }

            $return1 = clone $return;

            $total = $return1->select(DB::raw('sum(user_details.liquidity) as total_liquidity'))->first();

            $array['data'] = $return;
            $array['total'] = $total;

            return $array;
        }
    }

    public function accountsList($investor_type, $company, $active_status, $active_status_companies, $auto_invest_label, $auto_generation, $notification_recurence, $role_id, $search_key, $velocity_owned = false)
    {
        if (\Auth::user()) {
            $default = Settings::first();
            $sub = $this->role->allSubAdmin();
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            $return = User::join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id');
            $return = $return->join('roles', 'roles.id', '=', 'user_has_roles.role_id');
            $return = $return->select('users.creator_id', 'users.id', 'users.interest_rate', 'users.created_at', 'users.email', 'users.name', 'users.updated_at', 'user_details.liquidity', 'roles.name as RoleName','roles.id as role_id');
            if ($role_id) {
                $return = $return->where('roles.id', $role_id);
            } else {
                $return = $return->whereIn('roles.id', [User::INVESTOR_ROLE, User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE]);
            }
            if($velocity_owned){
                $return = $return->where('users.velocity_owned', 1);
            }
            $return = $return->withCount([
                'investmentData1 AS rtr' => function ($query) use ($default) {
                    $query->select(DB::raw('sum( (invest_rtr-(invest_rtr * (merchant_user.mgmnt_fee/100) ) ))
                    as rtr'));
                    $query->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
                    $query->where('merchants.active_status', '=', 1);
                    $query->whereIn('merchant_user.status', [1, 3]);
                },
                'investorTransactions AS amount' => function ($query) {
                    $query->select(DB::raw('SUM(amount) as amount'));
                    $query->whereIn('transaction_category', [1, 12])->where('status', InvestorTransaction::StatusCompleted);
                },
                'investmentData AS ctd' => function ($query) {
                    $query->select(DB::raw('SUM(paid_participant_ishare - merchant_user.paid_mgmnt_fee)
                    as ctd'));
                    $query->whereHas('merchant', function ($query1) {
                        $query1->where('active_status', '=', 1); // whre no default merchants.
                    });
                },
                'investmentData AS default_pay_rtr' => function ($query) {
                    $query->select(DB::raw('SUM(paid_participant_ishare - merchant_user.paid_mgmnt_fee) as ctd'));
                    $query->whereHas('merchant', function ($query1) {
                        $query1->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1); // whre no default merchants.
                    });
                },
                'investmentData AS defaulted_balance' => function ($query) {
                    $query->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
                    $query->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1); // whre no default merchants.
                    $query->select(DB::raw('
                    sum(((merchant_user.invest_rtr+IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)),0)) -
                        ( merchant_user.invest_rtr*(merchant_user.mgmnt_fee)/100 + IF(old_factor_rate>factor_rate,(merchant_user.amount*(old_factor_rate-factor_rate)*(merchant_user.mgmnt_fee)/100),0) ) -
                        (IF(merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee,merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee,0))))
                        as defaulted_balance'
                        )
                    );
                },
            ]);
            if (empty($permission)) {
                if (Auth::user()->hasRole(['company'])) {
                    $return = $return->where('users.company', $userId);
                } else {
                    $return = $return->where('users.creator_id', $userId);
                }
            }
            if ($company) {
                $return = $return->where('users.company', $company);
            }
            if ($auto_invest_label) {
                $return = $return->whereRaw('JSON_CONTAINS(label,"'.$auto_invest_label.'")');
            }
            if ($auto_generation == 1) {
                $return = $return->where('users.auto_generation', 1);
            } elseif ($auto_generation == 2) {
                $return = $return->where('users.auto_generation', 0);
            }

            if ($notification_recurence) {
                $return = $return->where('users.notification_recurence', $notification_recurence);
            }
            if ($search_key != '') {
                $return = $return->where(function ($query) use ($search_key) {
                    $query->where('users.name', 'like', '%'.$search_key.'%');
                    $query->orwhere('users.email', 'like', '%'.$search_key.'%');
                });
            }
            if ($active_status == 1) {
                $return = $return->where('active_status', 1);
            } elseif ($active_status == 2) {
                $return = $return->where('active_status', 0);
            }

            if ($active_status_companies == 1) {
                $return = $return->whereHas('company_relation', function ($query) {
                    $query->where('company_status',1);
                });
            } elseif ($active_status_companies == 2) {
                $return = $return->whereHas('company_relation', function ($query) {
                    $query->where('company_status',0);
                });
            }


            if ($investor_type) {
                $return = $return->where('users.investor_type', $investor_type);
            }
            // if ($liquidity != '') {
            //     $return = $return->where('liquidity_exclude', $liquidity);
            // }
            $return = $return->leftJoin('user_details', 'users.id', 'user_details.user_id');
            if ($default->hide == 1) {
                $return = $return->where('active_status', 1);
            }
            $return1 = clone $return;
            $total = $return1->select(DB::raw('sum(user_details.liquidity) as total_liquidity'))->first();
            $array['data'] = $return;
            $array['total'] = $total;

            return $array;
        }
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deleteAdminUsers($id)
    {
        if ($admin_user = $this->findAdminUsers($id)) {
            return $admin_user;
        }

        //return false;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\User
     */
    public function createBranchManager(Request $request) //BranchManager = Investor
    {
        $user = $this->table->create($request->only('name', 'management_fee', 'email', 'password', 'creator_id'));

        $user->assignRole('branch manager');

        return $user;
    }

    public function createCollectionUser(Request $request) //BranchManager = Investor
    {
        $creator_id = Auth::user()->id;
        $request->merge(['creator_id' =>$creator_id]);

        $user = $this->table->create($request->only('name', 'management_fee', 'email', 'password', 'creator_id'));

        $user->assignRole('collection user');

        return $user;
    }

    private function generateFileName($extension)
    {
        return 'doc_'.time().'.'.$extension;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\User
     */
    public function createSubAdmin(Request $request) //investor admin
    {
        // $request->merge(['attach_admin' =>isset($request->attach_admin) ? $request->attach_admin : 0]);

        $file_name = $request->file('logo');
        $fileName = $this->generateFileName($request->logo->getClientOriginalExtension());
        $fileName = time().'.'.request()->logo->getClientOriginalExtension();
        $upload_path = request()->logo->move('images', $fileName);
        $merchant_permission = isset($request->merchant_permission) ? $request->merchant_permission : 0;
        $company_status = isset($request->company_status) ? 1: 0;

        // $user = $this->table->create($request->only('name', 'management_fee', 'email', 'password', 'brokerage', 'logo','merchant_permission'));
        session_set('user_role', 'company');
        $user = User::create([
            'name' => $request->name,
            'management_fee' => $request->management_fee,
            'email' => $request->email,
            'password' => $request->password,
            'brokerage' => $request->brokerage,
            'logo' => 'images/'.$fileName,
            'merchant_permission' => $merchant_permission,
            'company_status' => $company_status,
            'creator_id' => Auth::user()->id,
            'syndicate'  =>($request->syndicate_company!=null) ? $request->syndicate_company:0
        ]);
        $user->assignRole('company');

        return $user;

        //$upload_path='';
        // $image = $file_name->getClientOriginalName();
        // $upload_path = 'images/'.$fileName;echo $upload_path;exit;
        // $file = $file_name->move($upload_path);
        // $request->merge(['logo' => $fileName]);
    }

    /**
     * @param $id
     * @param $req
     *
     * @return bool
     */
    public function updateSubAdmin($id, $req)
    {
        // dd($req->all());

        if ($sub_admin = $this->findSubAdmin($id)) {
            // $req->merge(['attach_admin' =>isset($req->attach_admin) ? $req->attach_admin : 0]);

            // $file_name = $req->file('logo');

            // $logo = $file_name->getClientOriginalName();

            // $upload = "images/".$logo;

            // $file = $file_name->move($upload);
            $company_status = isset($req->company_status) ? 1: 0;
            if ($req->hasFile('logo')) {
                $fileName = $this->generateFileName($req->logo->getClientOriginalExtension());

                $fileName = time().'.'.request()->logo->getClientOriginalExtension();
                $upload_path = request()->logo->move(('images'), $fileName);
                $data = [
               'name'          => $req->name,
               'brokerage'     => $req->brokerage,
               'logo'          => 'images/'.$fileName,
               'email'         => $req->email,
               'management_fee'=> $req->management_fee,
              // 'attach_admin'  => isset($req->attach_admin) ? $req->attach_admin : 0,
               'merchant_permission'=>isset($req->merchant_permission) ? $req->merchant_permission : 0,
               'company_status' => $company_status,
               'syndicate'  =>($req->syndicate_company!=null) ? $req->syndicate_company:0
               //'creator_id'    => Auth::user()->id,
            ];
            } else {
                $data = [
               'name'          => $req->name,
               'brokerage'     => $req->brokerage,
               'email'         => $req->email,
               'management_fee'=> $req->management_fee,
              // 'attach_admin'  => isset($req->attach_admin) ? $req->attach_admin : 0,
               'merchant_permission'=>isset($req->merchant_permission) ? $req->merchant_permission : 0,
               'company_status' => $company_status,
               'syndicate'  =>($req->syndicate_company!=null) ? $req->syndicate_company:0
               //'creator_id'    => Auth::user()->id,
            ];
            }
            $req->merge(['merchant_permission' =>isset($req->merchant_permission) ? $req->merchant_permission : 0]);

            $req->only('name', 'brokerage', 'logo', 'email', 'management_fee', 'company', 'merchant_permission');

            // $sub_admin->update($req->only('name', 'brokerage', 'logo', 'email', 'management_fee','merchant_permission'));

            $sub_admin->update($data);

            if ($req->password != null) {
                $sub_admin->password = $req->password;
            }

            return $sub_admin->save();
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function findBranchManager($id)
    {
        $user = $this->table->find($id);

        return ($user->hasRole('branch manager')) ? $user : false;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function findCollectonUser($id)
    {
        $user = $this->table->find($id);

        return ($user->hasRole('collection_user')) ? $user : false;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function findSubAdmin($id)
    {
        $user = $this->table->find($id);

        return ($user->hasRole('company')) ? $user : false;
    }

    /**
     * @param $id
     * @param $req
     *
     * @return bool
     */
    public function updateBranchManager($id, $req)
    {
        if ($branch_manager = $this->findBranchManager($id)) {
            $branch_manager->update($req->only('name', 'email', 'management_fee'));

            if ($req->password != null) {
                $branch_manager->password = $req->password;
            }

            return $branch_manager->save();
        }

        return false;
    }

    public function updateCollectionUser($id, $req)
    {
        if ($collection_user = $this->findCollectonUser($id)) {
            $collection_user->update($req->only('name', 'email'));

            if ($req->password != null) {
                $collection_user->password = $req->password;
            }

            return $collection_user->save();
        }

        return false;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\User
     */
    public function createAdminUsers(Request $request) //AdminUsers = Investor
    {
        $user = $this->table->create($request->only('name', 'management_fee', 'global_syndication', 'email', 'password'));

        $user->assignRole('admin');

        return $user;
    }

    public function createLiquidityAdjuster(Request $request)
    {
        $data = UserDetails::where('user_id', $request->user_id)->first();

        // if ($data) {
        //        $liquidity_old = $data->liquidity;
        //    }

        //$liquidity=$request->liquidity_adjuster+$data->liquidity;
        //$adjuster_liquidity=$data->liquidity_adjuster+$request->liquidity_adjuster;

        // $liquidity_change = $liquidity - $liquidity_old;

        // $liquidity_log=array();

        //  $aggregated_liquidity = UserDetails::join('users', 'users.id', 'user_details.user_id')
        // ->where('company', '>', 0)->where('liquidity_exclude', 0)->groupBy('company')->select(DB::raw('sum(liquidity) as liquidity,company'))->get()->toArray();
        $adjuster_liquidity = $request->liquidity_adjuster;
        InvestorHelper::update_liquidity($request->user_id, 'Liquidity Adjustor', null, $adjuster_liquidity);
        $users = UserDetails::where('user_id', $request->user_id)->each(function($row) use($adjuster_liquidity) {
            $row->update(['liquidity_adjuster'=>$adjuster_liquidity]);
        });

        //  $batch_id = rand(10000, 99999);
        //  $aggregated_liquidity = json_encode($aggregated_liquidity);

        //  $liquidity_log= array('member_id'          => $request->user_id,
        //                       'final_liquidity'     => $liquidity,
        //                       'liquidity_change'    => $liquidity_change,
        //                       'member_type'         => 'investor',
        //                       'aggregated_liquidity'=> $aggregated_liquidity,
        //                       'description'         => 'Liquidity Adjuster',
        //                       'batch_id'            => $batch_id,
        //                      );

        // $insert = LiquidityLog::create($liquidity_log);

        return $users;
    }

    public function createLenderUsers(Request $request)
    {
        $underwiting_status = json_encode($request->underwriting_status, true);
        $request->merge(['underwriting_status' =>$underwiting_status]);
        session_set('user_role', 'lender');
        $user = $this->table->create($request->only('name', 'email', 'password', 'creator_id', 'management_fee', 'global_syndication', 's_prepaid_status', 'lag_time', 'underwriting_fee', 'underwriting_status'));

        $user->assignRole('lender');

        return $user;
    }

    public function createEditorUsers(Request $request)
    {
        $user = $this->table->create($request->only('name', 'email', 'password', 'creator_id'));

        $user->assignRole('editor');

        return $user;
    }

    public function createViewerUsers(Request $request)
    {
        // commented to check and temp commit
        $user = $this->table->create($request->only('name', 'email', 'password', 'creator_id'));
        if ($request->roles) {
            foreach ($request->roles as $role_id) {
                $role = DB::table('roles')->where('id', $role_id)->first();
                $user->assignRole($role->name);

                // $user = $this->table->create($request->only('name', 'email', 'password', 'creator_id'));
    // $user->assignRole('viewer');
            }
        }// end count array
        // default
        $user->assignRole('viewer');

        return $user;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function findAdminUsers($id)
    {
        try {
            $user = $this->table->find($id);
        } catch (\ModelNotFoundException $e) {
            return redirect()->back()->withError($e->getMessage());
        }

        return ($user->hasRole('admin')) ? $user : false;
    }

    public function findUser($id, $type)
    {
        $user = $this->table->find($id);

        return ($user->hasRole($type)) ? $user : false;
    }

    /**
     * @param $id
     * @param $req
     *
     * @return bool
     */
    public function updateAdminUsers($id, $req)
    {
        if ($admin_user = $this->findAdminUsers($id)) {
            $admin_user->update($req->only('name', 'email', 'management_fee', 'global_syndication'));

            if ($req->password != null) {
                $admin_user->password = $req->password;
            }

            return $admin_user->save();
        }

        return false;
    }

    public function updateUser($id, $req, $type)
    {
        if ($lender = $this->findUser($id, $type)) {
            $lender->update($req->only('name', 'email', 'management_fee', 'global_syndication', 's_prepaid_status', 'lag_time', 'underwriting_fee', 'underwriting_status'));
            if ($req->password != null) {
                $lender->password = $req->password;
            }
            //   return $lender->save();

            // role management
            if ($req->roles) {
                $user = $this->table->find($id);
                $user->syncRoles([$type]);
                foreach ($req->roles as $role_id) {
                    $role = DB::table('roles')->where('id', $role_id)->first();
                    $user->assignRole($role->name);
                }
            } // end if req roles


            return $lender->save();
        }

        return false;
    }

    public function deleteLenders($id)
    {
        if ($lender = $this->findUser($id, 'lender')) {
            return $lender;
        }

        return false;
    }

    public function deleteSubAdmin($id)
    {
        if ($admin_user = $this->findUser($id, 'company')) {
            return $admin_user->delete();
        }

        return false;
    }

    public function resetPassword($id, $req)
    {
        $user = $this->table->find($id);

        if ($req->password != null) {
            $user->password = $req->password;
        }

        return $user->save();
    }

    public function findUserRole($id, $type=NULL)
    {
        $user = $this->table->find($id);

        return $user;
    }

    public function createRoleUsers(Request $request)
    {
        if ($request->roles) {
            foreach ($request->roles as $role_id) {
                $role = DB::table('roles')->where('id', $role_id)->first();
                if ($role->name == 'company') {
                    session_set('user_role', 'company');
                }
            }
        }

        // commented to check and temp commit
        $user = $this->table->create($request->only('name', 'email', 'password', 'creator_id'));
        if ($request->roles) {
            foreach ($request->roles as $role_id) {
                $role = DB::table('roles')->where('id', $role_id)->first();
                $user->assignRole($role->name);
            }
            //  $user->syncRoles($req->roles);
        }// end count array
   // default

        return $user;
    }

    public function updateUserRole($id, $req, $type)
    {  
        if ($lender = $this->findUserRole($id, $type)) {
            if ($req->password != null) {
                $lender->password = $req->password;
            }
            $lender->update($req->only('name', 'email'));
            $user = $this->table->find($id);

            if ($req->roles) {
                $user->syncRoles($req->roles);
            } else {
                $user->syncRoles([]);
            } // end if req roles

            $lender->save();

            //    role management
            if ($user->id == Auth::user()->id) {
                Auth::logout();

                return redirect('/login');
            }
        }

        return true;
    }

    public function updateModulePerm($id, $req)
    {
        Role_module::where('role_id', '=', $id)->each(function ($row) {
            $row->delete();
        });
        $permissions = $req->permissions;

        if ($permissions) {
            foreach ($permissions as $permission) {
                $arr = explode('/', $permission, 2);
                $pid = $arr[0];
                $mid = $arr[1];
                Role_module::create(
    ['permission_id' => $pid, 'role_id' => $id, 'module_id' => $mid]
      );
            }
        }

        return true;
    }

    public function updateModulePermUser($id, $req)
    {
        Role_module::where('user_id', '=', $id)->each(function ($row) {
            $row->delete();
        });
        $permissions = $req->permissions;

        if ($permissions) {
            foreach ($permissions as $permission) {
                $arr = explode('/', $permission, 2);
                $pid = $arr[0];
                $mid = $arr[1];
                Role_module::create(
    ['permission_id' => $pid, 'user_id' => $id, 'module_id' => $mid]
      );
            }
        }

        return true;
    }
}
