<?php

namespace App\Http\Controllers;

use App\CarryForward;
use App\CompanyAmount;
use App\Exports\Data_arrExport;
use App\Exports\Merchant_Graph;
use InvestorHelper;
use App\Imports\MerchantsImport;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\InvestorTransactionRepository;
use Spatie\Permission\Models\Role;
use App\LiquidityLog;
use App\Mailboxrow;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantPaymentTerm;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\MNotes;
use App\Models\InvestorAchRequest;
use App\Models\Views\MerchantUserView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Rcode;
use App\SubStatus;
use App\Template;
use App\User;
use App\UserActivityLog;
use App\UserDetails;
use App\MerchantDetails;
use App\VelocityFee;
use Carbon\Carbon;
use App\UserMeta;
use Exception;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Events\DashboardJobSuccessEvent;
use PayCalc;
use PDF;
use App\Settings;
use App\Jobs\LiquidtyUpdate;
use App\Jobs\SyncMerchantUserJob;
use App\Providers\DashboardServiceProvider;
use MerchantHelper;
use Artisan;

class TestController extends Controller
{
    public function __construct(IRoleRepository $role, IMerchantRepository $merchant,InvestorTransactionRepository $transaction)
    {
        $this->role = $role;
        $this->merchant = $merchant;
        $this->transaction = $transaction;
    }

    public function importView()
    {
        return view('import');
    }

    public function import()
    {
        Excel::import(new MerchantsImport, request()->file('file'));

        return back()->with('success', 'CRM Merchants imported successfully');
    }

    public function merchant_deatils_updation()
    {
          $merchants=DB::table('merchants')->get()->toArray();
          $i=1;

          if($merchants)
          {
               foreach ($merchants as $key => $merchant) {

                   $details=MerchantDetails::where('merchant_id',$merchant->id);

                   $arr=[

                    'agent_name'=>$merchant->agent_name,
                    'annual_revenue'=>($merchant->annual_revenue)?$merchant->annual_revenue:0.00,
                    'deal_type'=>$merchant->deal_type,
                    'position'=>$merchant->position,
                    'withhold_percentage'=>($merchant->withhold_percentage)?$merchant->withhold_percentage:0.00,
                    'partner_credit_score'=>($merchant->partner_credit_score)?$merchant->partner_credit_score:0.00,
                    'owner_credit_score'=>($merchant->owner_credit_score)?$merchant->owner_credit_score:0.00,
                    'entity_type'=>$merchant->entity_type,
                    'under_writer'=>$merchant->under_writer,
                    'date_business_started'=>$merchant->date_business_started,
                    'monthly_revenue'=>($merchant->monthly_revenue)?$merchant->monthly_revenue:0.00,
                    'crm_id'=> ($merchant->crm_id)?$merchant->crm_id:0.00
                ];

                   if($details->count()>0)
                   {
                        $details= $details->where('merchant_id',$merchant->id)->update($arr);

                   }
                   else
                   {
                       $arr['merchant_id']=$merchant->id;
                       MerchantDetails::create($arr);

                   }

                   $i++;
                 
               }


                echo  $i ." merchants updated successfully!!"; 


          }
         


    }

    public function data_info()
    {
        $fileName = 'Data_Info_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $i = 1;

        $merchant_arr1=DB::table('merchant_user')
        ->where('merchant_user.user_id',602)
         ->leftJoin('users','users.id','merchant_user.user_id')
        ->whereNotIn('users.company',[58])
        ->groupBy('merchant_user.merchant_id')
        ->pluck('merchant_user.merchant_id')->toArray();

       
        $merchant_arr2=DB::table('merchant_user')->join('merchants','merchants.id','merchant_user.merchant_id')->whereNotIn('merchant_user.merchant_id',$merchant_arr1)->pluck('merchant_id')->toArray();

    
         $merchant_arr3=array_merge($merchant_arr1,$merchant_arr2);

          $ach_fees = VelocityFee::where('velocity_fees.status', 1)->select('velocity_fees.merchant_id', DB::raw('sum(velocity_fees.payment_amount) as ach_fees'), 'merchants.label')->join('merchants', 'merchants.id', 'velocity_fees.merchant_id')->whereIn('velocity_fees.merchant_id',$merchant_arr3);
           $ach_fees = $ach_fees->groupBy('velocity_fees.merchant_id')->pluck('ach_fees', 'velocity_fees.merchant_id')->toArray();

         $merchants = Merchant::select('merchants.id as merchant_id','merchants.name as merchant_name','merchants.date_funded','merchants.funded','merchants.rtr','merchants.origination_fee','merchants.commission','sub_statuses.name as substatus_name',DB::raw('sum(merchant_user.amount) as funding_amount'),DB::raw('sum(merchant_user.under_writing_fee) as under_writing_fee'),DB::raw('sum(merchant_user.under_writing_fee) as under_writing_fee'),
            DB::raw('sum(merchant_user.commission_amount) as commission_amount'),
            DB::raw('sum(merchant_user.paid_participant_ishare) as paid_participant_ishare'),
            DB::raw('sum(merchant_user.paid_mgmnt_fee) as paid_mgmnt_fee'),
            DB::raw('sum(merchant_user.pre_paid) as pre_paid'),

          )
         ->where('merchants.active_status', 1)
         ->where('merchants.date_funded','>=','2021-01-01')
         ->whereIn('merchants.id',$merchant_arr3)
        ->leftjoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
         ->leftjoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
        ->orderBy('merchants.id', 'desc')
         ->groupBy('merchants.id')
        ->get()->toArray();



         $excel_array[0] = ['No','Merchant Name', 'Funded', 'RTR', 'Originaion Fee','Underwriting Fee','Wire Fee', 'Commission', 'Syndicate Funded', 'Syndicate Broker', 'Syndicate Fee', 'Payments', 'Management Fee', 'Status'];
        if (! empty($merchants)) {
            foreach ($merchants as $key => $data) {
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Merchant Name'] = $data['merchant_name'];
                $excel_array[$i]['Funded'] = FFM::dollar($data['funded']);
                $excel_array[$i]['RTR'] = FFM::dollar($data['rtr']);
                $excel_array[$i]['Originaion Fee'] = FFM::percent($data['origination_fee']);
                $excel_array[$i]['Underwriting Fee'] = FFM::dollar($data['under_writing_fee']);
                $excel_array[$i]['Wire Fee'] = isset($ach_fees[$data['merchant_id']])?$ach_fees[$data['merchant_id']]:0;
                $excel_array[$i]['Commission'] = FFM::percent($data['commission']);
                $excel_array[$i]['Syndicate Funded'] = FFM::dollar($data['funding_amount']);
                $excel_array[$i]['Syndicate Broker'] = FFM::dollar($data['commission_amount']);
                $excel_array[$i]['Syndicate Fee'] = FFM::dollar($data['pre_paid']);
                $excel_array[$i]['Payments'] = FFM::dollar($data['paid_participant_ishare']);
                $excel_array[$i]['Management Fee'] = FFM::dollar($data['paid_mgmnt_fee']);
                $excel_array[$i]['Status'] =$data['substatus_name'];
               
                $i++;
            }
        }
        $export = new Data_arrExport($excel_array);

        return Excel::download($export, $fileName);

    }

    public function Data1()
    {
        $fileName = 'Data1_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $i = 1;
        $merchants = Merchant::select('merchants.id as merchant_id', 'merchants.name as merchant_name', 'sub_statuses.name as sub_status_name', 'merchants.advance_type', 'merchants.date_funded', 'merchants.funded', 'merchants.commission', 'factor_rate', 'up_sell_commission', 'deal_type', 'rtr', 'last_payment_date', 'payment_amount', 'sub_status_id', DB::raw('sum(((actual_paid_participant_ishare-invest_rtr)*(1-(merchant_user.mgmnt_fee)/100))) as overpayment'), DB::raw('sum(merchant_user.paid_participant_ishare) as paid_participant_ishare,sum(merchant_user.amount) as amount,sum(merchant_user.invest_rtr  - merchant_user.paid_participant_ishare) as balance,sum(merchant_user.amount) as funding_amount,sum(merchant_user.invest_rtr) as invest_rtr'))->leftjoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->whereIn('label', [1, 2])->where('merchants.active_status', 1)->orderBy('merchants.id', 'desc')->groupBy('merchants.id')->get()->toArray();
        $excel_array[0] = ['No', 'Advance Id', 'Bussiness Name', 'Advance Status', 'Advance Type', 'Funding Date', 'Funding Amount', 'Buy Rate', 'Factor', 'Payment', 'Freq', 'RTR', 'Total Amount Settled', 'Balance', 'Last Merchant Cleared Date'];
        if (! empty($merchants)) {
            foreach ($merchants as $key => $data) {
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Advance Id'] = $data['merchant_id'];
                $excel_array[$i]['Bussiness Name'] = $data['merchant_name'];
                $excel_array[$i]['Advance Status'] = $data['sub_status_name'];
                $excel_array[$i]['Advance Type'] = $data['deal_type'];
                $excel_array[$i]['Funding Date'] = $data['date_funded'];
                $excel_array[$i]['Funding Amount'] = FFM::dollar($data['funding_amount']);
                $excel_array[$i]['Buy Rate'] = $data['factor_rate'] - ($data['commission'] / 100);
                $excel_array[$i]['Factor'] = $data['factor_rate'];
                $excel_array[$i]['Payment'] = FFM::dollar($data['payment_amount']);
                $excel_array[$i]['Freq'] = $data['advance_type'];
                $excel_array[$i]['RTR'] = FFM::dollar($data['invest_rtr']);
                $excel_array[$i]['Total Amount Settled'] = FFM::dollar($data['paid_participant_ishare']);
                if ($data['overpayment'] > 0) {
                    $balance = '0.00';
                } else {
                    $balance = $data['balance'];
                }
                $excel_array[$i]['Balance'] = FFM::dollar($balance);
                $excel_array[$i]['Last Merchant Cleared Date'] = $data['last_payment_date'];
                $i++;
            }
        }
        $export = new Data_arrExport($excel_array);

        return Excel::download($export, $fileName);
    }

    public function Data2()
    {
        $fileName = 'Data2_'.date('Y-m-d').'_'.time().'.csv';
        $i = 1;
        $merchants = Merchant::select('merchants.id', 'merchants.name', 'merchants.last_payment_date', 'rtr', 'date_funded', 'rcode.code as last_rcode', DB::raw(' (SELECT participent_payments.payment_date FROM participent_payments WHERE participent_payments.merchant_id=merchants.id and participent_payments.payment=0 and rcode!=0 limit 1) AS last_rcode_date '), )->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')->whereHas('participantPayment', function ($q) {
            $q->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
            $q->join('users', 'users.id', 'payment_investors.user_id');
            $q->where('participent_payments.payment', '>', 0);
            $q->select('participent_payment_id', 'participent_payments.rcode', 'participent_payments.mode_of_payment', 'user_id', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.id', 'participent_payments.payment_date', 'users.name as user_name', 'reason', 'payment_investors.participant_share', 'payment_investors.mgmnt_fee', );
            $q->orderByDesc('participent_payments.payment_date');
        })->with(['participantPayment' => function ($q) {
            $q->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
            $q->join('users', 'users.id', 'payment_investors.user_id');
            $q->where('participent_payments.payment', '>', 0);
            $q->select('participent_payment_id', 'user_id', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.id', 'participent_payments.payment_date', 'users.name as user_name', 'reason', 'payment_investors.participant_share', 'payment_investors.mgmnt_fee', );
            $q->orderByDesc('participent_payments.payment_date');
        }]);
        $merchants = $merchants->whereIn('label', [1, 2])->orderBy('merchants.id', 'desc')->where('merchants.active_status', 1)->paginate(10);
        $excel_array[0] = ['No', 'Advance Id', 'Bussiness Name', 'Funding Date', 'Debit Amount', 'Debit Cleared Date', 'Return Date', 'Return Code', 'Return Reason', 'Syndicators Name', 'Payable Amt (Gross)', 'Payable Amt (Net)', 'Payable Cleared Date', 'Payable Process Date', 'Payable Status'];
        $array = [];
        if (! empty($merchants->toArray())) {
            foreach ($merchants as $key => $data) {
                $array[$key] = $data['id'];
                foreach ($data->participantPayment as $key => $value) {
                    $excel_array[$i]['No'] = $i;
                    $excel_array[$i]['Advance Id'] = $data['id'];
                    $excel_array[$i]['Bussiness Name'] = $data['name'];
                    $excel_array[$i]['Funding Date'] = $data['date_funded'];
                    $excel_array[$i]['Debit Amount'] = $value['payment'];
                    $excel_array[$i]['Debit Cleared Date'] = $value['payment_date'];
                    $excel_array[$i]['Return Date'] = $data['last_rcode_date'];
                    $excel_array[$i]['Return Code'] = $data['last_rcode'];
                    $excel_array[$i]['Return Reason'] = $value['reason'];
                    $excel_array[$i]['Syndicators Name'] = $value['user_name'];
                    $excel_array[$i]['Payable Amt (Gross)'] = $value['participant_share'];
                    $excel_array[$i]['Payable Amt (Net)'] = $value['participant_share'] - $value['mgmnt_fee'];
                    $excel_array[$i]['Payable Cleared Date'] = $value['payment_date'];
                    $excel_array[$i]['Payable Process Date'] = $value['payment_date'];
                    $excel_array[$i]['Payable Status'] = 'Cleared';
                    $i++;
                }
            }
        }
        $export = new Data_arrExport($excel_array);
        Excel::store(new Data_arrExport($excel_array), $fileName, 'data_uploads');

        return view('balance', ['merchants' => $array]);
    }

    public function missingPaymentsList()
    {
        $missingPaymentsList = DB::table('participent_payments')->where('payment_date', '>=', '2021-01-01')->where('payment_date', '<=', '2021-03-22')->whereNotIn('id', [748514])->orderByDesc('id')->pluck('id')->toArray();
        print_r($missingPaymentsList);
        dd();
    }

    public function CreatePaymentRequest(Request $request)
    {
        $payments = ParticipentPayment::select('merchant_id', 'payment', 'reason', 'payment_date', 'rcode', 'sub_status_id', 'funded', 'rtr', 'pmnts', 'commission', 'date_funded', 'factor_rate', 'payment_amount', 'advance_type', 'participent_payments.id', 'complete_percentage')->whereIn('participent_payments.id', [776758])->join('merchants', 'merchants.id', 'participent_payments.merchant_id')->get()->toArray();
        $array = [];
        $response_1 = [];
        foreach ($payments as $key => $value) {
            $substatus = SubStatus::where('id', $value['sub_status_id'])->value('name');
            $code = Rcode::where('id', $value['rcode'])->value('code');
            $payments_sum = ParticipentPayment::select(DB::raw('sum(payment) as payment'), DB::raw('sum(final_participant_share) as final_participant_share'))->where('participent_payments.merchant_id', $value['merchant_id'])->groupBy('merchant_id')->first();
            $total_payment = $payments_sum->payment;
            $merchant_arr = MerchantUser::select(DB::raw('sum(invest_rtr) as invest_rtr'), DB::raw('sum(paid_participant_ishare) as paid_participant_ishare'), DB::Raw('sum( IF(paid_participant_ishare > invest_rtr, (paid_participant_ishare-invest_rtr )*(1- (merchant_user.mgmnt_fee)/100 ), 0) ) as overpayment'))->whereIn('merchant_user.status', [1, 3])->where('merchant_user.merchant_id', $value['merchant_id']);
            $merchant_arr = $merchant_arr->first();
            $total_rtr = $merchant_arr->invest_rtr;
            $balance_our_portion = 0;
            if ($merchant_arr->overpayment) {
                $balance_our_portion = 0;
            } else {
                $balance_our_portion = ($merchant_arr->invest_rtr) - $merchant_arr->paid_participant_ishare;
            }
            $participant_share_total = $merchant_arr->paid_participant_ishare;
            $bal_rtr = $total_rtr - $participant_share_total;
            if ($total_rtr > 0) {
                $actual_payment_left = ($value['rtr']) ? $bal_rtr / (($total_rtr / $value['rtr']) * ($value['rtr'] / $value['pmnts'])) : 0;
            } else {
                $actual_payment_left = 0;
            }
            $fractional_part = fmod($actual_payment_left, 1);
            $act_paymnt_left = floor($actual_payment_left);
            if ($fractional_part > .09) {
                $act_paymnt_left = $act_paymnt_left + 1;
            }
            $act_paymnt_left = ($act_paymnt_left > 0) ? $act_paymnt_left : 0;
            $payment_unique_date = ParticipentPayment::where('payment_type', 1)->where('payment', '!=', 0)->where('participent_payments.merchant_id', $value['merchant_id']);
            $payment_unique_date = $payment_unique_date->groupBy('payment_date')->get()->toArray();
            $payment_left = $value['pmnts'] - count($payment_unique_date);
            if ($value['complete_percentage'] > 99) {
                $payment_left = 0;
            }
            $ctd_sum = $total_payment;
            $array['method'] = 'add_lead_payment';
            $array['username'] = config('app.crm_user_name');
            $array['password'] = config('app.crm_password');
            $array['payment_date'] = $value['payment_date'];
            $array['payment'] = $value['payment'];
            $array['investor_merchant_id'] = $value['merchant_id'];
            $array['funded_amount'] = $value['funded'];
            $array['factor_rate'] = round($value['factor_rate'], 2);
            $array['rtr'] = $value['rtr'];
            $array['payment_amount'] = $value['payment_amount'];
            $array['advance_type'] = $value['advance_type'];
            $array['date_funded'] = $value['date_funded'];
            $array['pmnts'] = $value['pmnts'] ? $value['pmnts'] : '--';
            $array['commission'] = $value['commission'] ? $value['commission'] : '--';
            $array['substatus'] = $substatus;
            $array['notes'] = $value['reason'];
            $array['payment_id'] = $value['id'];
            $array['notes'] = $value['reason'];
            $array['rcode'] = $code;
            $array['payment_left'] = $payment_left;
            $array['actual_payment_left'] = $act_paymnt_left;
            $array['complete_percentage'] = $value['complete_percentage'];
            $array['balance'] = round($balance_our_portion);
            $array['ctd'] = round($ctd_sum, 2);
            $client = new \GuzzleHttp\Client();
            $last_login_ip = $request->ip();
            $response = $client->request('POST', config('app.crm_url').'/api/service', ['form_params' => $array]);
            $json_encode = json_encode($array);
            $response_1[$key] = $json_encode;
        }
        if (! empty($array)) {
            return response()->json(['status' => 'success', 'result' => $response_1]);
        } else {
            return response()->json(['status' => 0, 'msg' => 'no payments found']);
        }
    }

    public function merchantsStatusChangedtoActive()
    {
        $arrayList = Merchant::select('id', 'factor_rate', 'old_factor_rate')->whereIn('id', [7684, 7761, 7955, 8069, 8076, 8192, 8193, 8207, 8224, 8241, 8256, 8260, 8271, 8272, 8275, 8289, 8291, 8303, 8310, 8315, 8316, 8317, 8319, 8322, 8368, 8369, 8389, 8397, 8401, 8405, 8418, 8421, 8422, 8430, 8459, 8505, 8517, 8524, 8549, 8562, 8582, 8650, 8652])->get()->toArray();
        $data_r = [];
        $j = 0;
        foreach ($arrayList as $key => $merchant) {
            $data_r['sub_status_id'] = 1;
            $data_r['old_factor_rate'] = 0;
            $data_r['factor_rate'] = $merchant['factor_rate'];
            Merchant::find($merchant['id'])->first()->update($data_r);
            $j++;
        }
        echo $j.' merchants changed to active advance <br>';
        dd();
    }

    public function merchantsStatusChangedtoAdvancedCompletedForLess()
    {
        $arrayList = Merchant::select('id', 'factor_rate', 'old_factor_rate')->whereIn('id', [8461, 7627, 7641, 7642, 7664, 7670, 7674, 7688, 7694, 7719, 7731, 7741, 7756, 7764, 7765, 7773, 7817, 7824, 7854, 7871, 7884, 7894, 7909, 7910, 7925, 7935, 7963, 7981, 7993, 8000, 8065, 8071, 8091, 8098, 8102, 8124, 8152, 8158, 8164, 8173, 8199, 8212, 8219, 8224, 8241, 8256, 8260, 8272, 8289, 8302, 8340, 8372, 8402, 8405, 8415, 8422, 8516, 8526, 8593, 8685, 8719, 8724, 8730, 8738, 8774, 8825, 8830, 8841, 8869, 8887, 8898, 8923, 8938, 8998, 9005, 9007, 9031, 9037, 9065, 9093, 9129, 9144, 9194, 9197, 9232, 9246, 9276, 9298, 9351, 9372, 9390, 9401, 9453, 9497, 9507, 9597, 7741, 7981, 8152, 8158, 8164, 8173, 8199, 8212, 8219, 8224, 8238, 8263, 8279, 8293, 8298, 8319, 8465])->get()->toArray();
        $data_r = [];
        $j = 0;
        foreach ($arrayList as $key => $merchant) {
            $data_r['sub_status_id'] = 20;
            $data_r['old_factor_rate'] = $merchant['factor_rate'];
            Merchant::find('id', $merchant['id'])->first()->update($data_r);
            $j++;
        }
        echo $j.' merchants changed to advanced completed for less <br>';
    }

    public function merchantsHaveNoRoleList()
    {
        $merchants = Merchant::select('users.id as user_id', 'merchants.id as merchant_id')->join('users', 'users.merchant_id_m', 'merchants.id')->get();
        $m_id = [];
        if ($merchants) {
            foreach ($merchants as $key => $value) {
                $count = DB::table('user_has_roles')->where('model_id', $value->user_id)->count();
                if ($count == 0) {
                    $m_id[$key] = $value->merchant_id;
                }
            }
        }
        print_r($m_id);
        dd();
    }

    public function merchantsHaveNoRoleChanged()
    {
        $merchants = Merchant::select('users.id as user_id', 'merchants.id as merchant_id')->join('users', 'users.merchant_id_m', 'merchants.id')->get();
        $m_id = [];
        $j = 0;
        if ($merchants) {
            foreach ($merchants as $key => $value) {
                $count = DB::table('user_has_roles')->where('model_id', $value->user_id)->where('role_id', 7)->count();
                if ($count == 0) {
                    $m_id[$key] = $value->merchant_id;
                    DB::table('user_has_roles')->insert(['model_id' => $value->user_id, 'role_id' => 7, 'model_type' => \App\User::class]);
                    $j++;
                }
            }
        }
        echo $j.' merchants updated successfully';
    }

    public function pending_merchants()
    {
        $merchants = Merchant::select('last_payment_date', 'merchants.id as merchant_id', 'merchants.name as merchant_name', 'lag_time', 'users.name as lender_name', 'company', 'sub_statuses.name as substatus_name', 'merchants.sub_status_id')->where('merchants.active_status', 1)->whereNOTIn('merchants.sub_status_id', [4, 11, 18, 19, 20, 22])->join('users', 'users.id', 'merchants.lender_id')->where('mail_send_status', '!=', 222)->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->where('merchants.complete_percentage', '<', 99)->where('merchants.complete_percentage', '>', 0)->orderByDesc('merchants.id')->where(function ($query) {
            $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+30) DAY)');
        })->orderByDesc('merchants.id')->get()->toArray();
        print_r($merchants);
        dd();
    }

    public function allAutoinvestchangetoLabels()
    {
        $users = User::where('auto_invest', 1)->whereNull('label')->pluck('id')->toArray();
        $i = 0;
        if ($users) {
            foreach ($users as $key => $value) {
                User::find($value)->update(['label' => [3, 4, 5]]);
                $i++;
            }
            echo $i.' investors label updated successfully ';
        } else {
            echo ' investors not found ';
        }
    }

    public function changePaymentDeleteDescripton()
    {
        $log = LiquidityLog::where('description', 'Delete Payment')->get()->toArray();
        $j = 0;
        if ($log) {
            foreach ($log as $key => $value) {
                LiquidityLog::where('id', $value['id'])->update(['description' => 'Payment Deletion']);
                $j++;
            }
            echo $j.' logs updated successfully';
        }
    }

    public function rundb()
    {
        $ar = [];
        $merchants_from_carry_forwards = CarryForward::select('merchant_id', DB::raw('sum(amount) as amount'))->groupBy('merchant_id')->get();
        foreach ($merchants_from_carry_forwards as $merchant) {
            $invest_rtr = MerchantUserView::where('merchant_id', $merchant['merchant_id'])->sum('invest_rtr');
            $mgmnt_fee = MerchantUserView::where('merchant_id', $merchant['merchant_id'])->sum('paid_mgmnt_fee');
            $final_participant_share = ParticipentPayment::where('merchant_id', $merchant['merchant_id'])->sum('final_participant_share');
            $data['old'] = ($invest_rtr - $mgmnt_fee) - $final_participant_share;
            $data['carry'] = $merchant['amount'];
            $data['merchant_id'] = $merchant['merchant_id'];
            if (abs($data['carry']) > abs($data['old'])) {
                array_push($ar, $data);
            }
        }

        return $ar;
        die;
        $data = json_decode(json_encode($CarryForwards), true);
        CarryForward::truncate();
        foreach (array_chunk($data, 1000) as $t) {
            CarryForward::insert($t);
        }

        return 'Inserted';
    }

    public function updateOldFactorRate()
    {
        $array = [9004 => '1.49', 9041 => '1.42', 9109 => '1.43', 9193 => '1.35', 9204 => '1.44', 9221 => '1.37', 9228 => '1.33', 9313 => '1.44', 8817 => '1.49'];
        $j = 0;
        if (! empty($array)) {
            foreach ($array as $key => $value) {
                Merchant::find($key)->update(['old_factor_rate' => $value]);
                $j++;
            }
            echo $j.'merchants old factor rate updated successfully';
        }
    }

    public function assignedDateUpdation()
    {
        $dates = MerchantUser::select('merchant_id', 'date_funded')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->whereNull('merchant_user.created_at')->get()->toArray();
        $i = 1;
        if (! empty($dates)) {
            foreach ($dates as $key => $value) {
                $date_funded = date('Y-m-d h:i:s', strtotime($value['date_funded']));
                MerchantUser::where('merchant_id', $value['merchant_id'])->update(['created_at' => $date_funded]);
                $i++;
            }
            echo $i.' assigned investors date updated successfully !!';
        } else {
            echo 'No data found';
        }
    }

    public function syndicateInvestors()
    {
        $merchants = MerchantUser::join('users', 'users.id', 'merchant_user.user_id')->where('users.company', 284)->distinct('merchant_user.merchant_id')->pluck('merchant_id')->toArray();
        print_r($merchants);
        dd();
    }

    public function merchantsCompanyAmountZeroList()
    {
        $merchants = DB::table('merchants')->pluck('id')->toArray();
        $list = [];
        if (! empty($merchants)) {
            foreach ($merchants as $key => $value) {
                $company_amount = CompanyAmount::where('merchant_id', $value)->sum('max_participant');
                if ($company_amount == 0) {
                    $list[$key] = $value;
                }
            }
        }
        print_r($list);
        dd();
    }

    public function merchantsCompanyAmountUpdation()
    {
        $merchants = DB::table('merchants')->whereIn('id', [7810])->pluck('max_participant_fund', 'id')->toArray();
        $i = 0;
        $company_amount = [];
        if (! empty($merchants)) {
            foreach ($merchants as $key1 => $value1) {
                CompanyAmount::where('merchant_id', $key1)->each(function($row) {
                    $row->delete();
                });
                $i++;
                $investments = DB::table('merchant_user')->where('merchant_user.merchant_id', $key1)->join('users', 'users.id', 'merchant_user.user_id')->groupBy('users.company')->pluck(DB::raw('sum(merchant_user.amount) as amount'), 'users.company')->toArray();
                $company = array_keys($investments);
                if (! empty($investments)) {
                    foreach ($investments as $key2 => $value2) {
                        $company_amount[$key2]['merchant_id'] = $key1;
                        $company_amount[$key2]['company_id'] = $key2;
                        if ($value2) {
                            $company_amount[$key2]['max_participant'] = $value2;
                        }
                    }
                }
                $companies = $this->role->allCompanies()->whereNotIn('id', $company)->pluck('id')->toArray();
                if (! empty($companies)) {
                    foreach ($companies as $key3 => $value3) {
                        $company_amount[$value3]['merchant_id'] = $key1;
                        $company_amount[$value3]['company_id'] = $value3;
                        $company_amount[$value3]['max_participant'] = 0;
                    }
                }
                CompanyAmount::insert($company_amount);
            }
        }
        echo $i.' merchants company amount updated successfully !!';
    }

    public function labelUpdation()
    {
        $merchants = DB::table('merchants')->pluck('label', 'id')->toArray();
        $i = 1;
        if (! empty($merchants)) {
            foreach ($merchants as $key => $value) {
                if ($value == 'MCA') {
                    $label = 1;
                } elseif ($value == 'Luthersales') {
                    $label = 2;
                } elseif ($value == 'Insurance') {
                    $label = 3;
                }
                $i++;
                Merchant::find($key)->update(['label' => $label]);
            }
            echo $i.' merchants label updated successfully !!';
        }
    }

    public function find_numbers(Request $request)
    {
        $min = $request->min;
        $max = $request->max;
        $denominator = $request->denominator;
        $numbers = '';
        for ($i = $min; $i <= $max; $i++) {
            if (($i % $denominator) > 0) {
                $numbers = $numbers.' '.$i;
            }
        }

        return 'the numbers between '.$min.' and '.$max.' which cannot be divided by '.$denominator.' are '.$numbers;
    }

    public function reasignedbasedInvestors()
    {
        $re_investors = MerchantUser::select('merchant_user.user_id', 'merchants.old_factor_rate', 'merchant_user.amount', 'merchant_user.merchant_id')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchant_user.status', 3)->whereIn('merchants.sub_status_id', [18, 19, 20])->groupBy('merchant_user.merchant_id')->get()->toArray();
        print_r($re_investors);
        dd();
    }

    public function settledMerchants()
    {
        $merchants = DB::table('merchant_user')->select('merchant_id')->whereIn('merchants.sub_status_id', [18, 19, 20])->where('merchant_user.status', 3)->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->groupBy('merchant_user.merchant_id')->pluck('merchant_user.merchant_id')->toArray();
        print_r($merchants);
        dd();
    }

    public function advancedCompletedMerchants()
    {
        $merchants = DB::table('merchant_user')->select('merchant_id')->where('merchants.sub_status_id', 11)->where(DB::raw('(invest_rtr- paid_participant_ishare )'), '<', 0)->where(DB::raw('old_factor_rate-factor_rate'), '>', 0)->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->groupBy('merchant_user.merchant_id')->pluck('merchant_user.merchant_id')->toArray();
        print_r($merchants);
        dd();
    }

    public function advancedressignedInvestors()
    {
        $re_investors = MerchantUser::select('merchant_user.user_id', 'merchants.old_factor_rate', 'merchant_user.amount', 'merchant_user.merchant_id')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchant_user.status', 3)->where(DB::raw('old_factor_rate-factor_rate'), '>', 0)->whereIn('merchants.sub_status_id', [11])->groupBy('merchant_user.merchant_id')->get()->toArray();
        $merchants = [];
        $i = 0;
        if (! empty($re_investors)) {
            foreach ($re_investors as $key => $value) {
                $merchants[] = $value['merchant_id'];
                $invest_rtr = $value['amount'] * $value['old_factor_rate'];
                MerchantUser::where('merchant_id', $value['merchant_id'])->where('user_id', $value['user_id'])->update(['invest_rtr' => $invest_rtr]);
                $i++;
            }
        }
        echo $i.' merchants reassigned investors rtr updated successfully';
    }

    public function settledressignedInvestors()
    {
        $re_investors = MerchantUser::select('merchant_user.user_id', 'merchants.old_factor_rate', 'merchant_user.amount', 'merchant_user.merchant_id')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchant_user.status', 3)->whereIn('merchants.sub_status_id', [18, 19, 20])->groupBy('merchant_user.merchant_id')->get()->toArray();
        $merchants = [];
        $i = 0;
        if (! empty($re_investors)) {
            foreach ($re_investors as $key => $value) {
                $merchants[] = $value['merchant_id'];
                $invest_rtr = $value['amount'] * $value['old_factor_rate'];
                MerchantUser::where('merchant_id', $value['merchant_id'])->where('user_id', $value['user_id'])->update(['invest_rtr' => $invest_rtr]);
                $i++;
            }
        }
        echo $i.' merchants reassigned investors rtr updated successfully';
    }

    public function merchantsOverpaymentsList1()
    {
        $assigned_investors = MerchantUser::select('merchant_id', 'merchant_user.user_id', 'invest_rtr', 'paid_participant_ishare', 'mgmnt_fee', 'complete_per')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->orderBy('merchant_id')->get()->toArray();
        $merchants = [];
        if ($assigned_investors) {
            foreach ($assigned_investors as $key => $value) {
                if (($value['invest_rtr'] < $value['paid_participant_ishare']) && $value['complete_per'] <= 0) {
                    $merchants[$key] = $value['merchant_id'];
                }
            }
            print_r(array_unique($merchants));
            dd();
        }
    }

    public function merchantsOverpaymentsList()
    {
        echo 'manual <br>';
        $overpayment1 = DB::table('merchant_user')->where(DB::raw('(invest_rtr- paid_participant_ishare )'), '<', 0)->orderByDesc('merchant_id')->groupBy('merchant_id')->pluck(DB::raw('sum(
         (paid_participant_ishare-invest_rtr )*(1- (merchant_user.mgmnt_fee)/100 )
           ) as overpayment'), 'merchant_id')->toArray();
        print_r($overpayment1);
        dd();
        echo 'table <br>';
        $overpayment2 = DB::table('payment_investors')->where('overpayment', '!=', 0)->orderByDesc('merchant_id')->groupBy('merchant_id')->pluck(DB::raw('sum(overpayment) as overpayment'), 'merchant_id')->toArray();
        print_r($overpayment2);
        dd();
    }

    public function url_list()
    {
        $title = 'Debug Url List';

        return view('debug', compact('title'));
    }

    public function checkCompletePercentage()
    {
    }

    public function moveCreatedAtToMerchantLastStatusDate()
    {
        $merchants = MerchantStatusLog::whereIn('merchant_id', [7741, 7765, 7979, 8012, 8041, 8056, 8158, 8173, 8206, 8263, 8268, 8293, 8332, 8405, 8422, 8581, 8590, 8622, 8920, 8962, 9111, 9275])->select('merchant_status_log.merchant_id as id', DB::raw('max(merchant_status_log.created_at) as created_at'))->groupBy('merchant_status_log.merchant_id')->get()->toArray();

        $i = 0;
        if (! empty($merchants)) {
            foreach ($merchants as $value) {
                Merchant::find($value['id'])
    ->update(['last_status_updated_date' =>$value['created_at']]);

                $i++;
            }
        }
        echo 'Total '.$i.' merchants last_status_updated_date updated successfully !!!';
    }

    public function moveDefaultDatetoMerchantAction()
    {
        $merchants = MerchantStatusLog::select('merchant_status_log.created_at', 'merchant_status_log.merchant_id as id', DB::raw('(merchant_status_log.created_at) as created_at'))->groupBy('merchant_status_log.created_at')->get()->toArray();
        $i = 1;
        if (! empty($merchants)) {
            foreach ($merchants as $value) {
                Merchant::find($value['id'])->update(['last_status_updated_date' => date('Y-m-d', strtotime($value['created_at']))]);
                $i++;
            }
        }
        echo 'Total '.$i.' merchants moved to update default date from merchants table successfully !!!';
    }

    public function perDiffMerchants()
    {
        $test = [];
        $payments = ParticipentPayment::select('participent_payments.merchant_id', 'max_participant_fund', 'complete_percentage')->distinct('participent_payments.merchant_id')->join('merchants', 'merchants.id', 'participent_payments.merchant_id')->where('participent_payments.merchant_id', 9402)->get()->toArray();
        if (! empty($payments)) {
            foreach ($payments as $key => $merchant) {
                $companies = CompanyAmount::select('company_id', 'max_participant')->where('merchant_id', $merchant['merchant_id'])->where('max_participant', '!=', 0)->get()->toArray();
                $c_count = count($companies);
                $inv = MerchantUser::select(DB::raw('sum(invest_rtr) as invest_rtr'), 'company')->where('merchant_id', $merchant['merchant_id'])->join('users', 'users.id', 'merchant_user.user_id')->groupBy('users.company');
                $investors = $inv->get()->toArray();
                if ($c_count > 1) {
                    foreach ($companies as $key1 => $company) {
                        $company_per = ($company['max_participant'] / $merchant['max_participant_fund']) * 100;
                        $expect_per = ($company_per / 100) * $merchant['complete_percentage'];
                        if (! empty($investors)) {
                            foreach ($investors as $key3 => $investor) {
                                $m_company = isset($company['company_id']) ? $company['company_id'] : '';
                                $in_company = isset($investor['company']) ? $investor['company'] : '';
                                $payment1 = ParticipentPayment::select(DB::raw('sum(participant_share) as participant_share'), DB::raw('sum(mgmnt_fee) as mgmnt_fee'), 'users.company')->where('participent_payments.merchant_id', $merchant['merchant_id'])->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->join('users', 'users.id', 'payment_investors.user_id')->where('users.company', $company['company_id']);
                                $payment1 = $payment1->groupBy('users.company');
                                $pay = $payment1->get()->toArray();
                                if (! empty($pay)) {
                                    foreach ($pay as $key4 => $value) {
                                        if ($investor['invest_rtr'] != 0) {
                                            $per_amount = (($value['participant_share'] - $value['mgmnt_fee']) / $investor['invest_rtr']) * 100;
                                            $per_diff = $expect_per - $per_amount;
                                            if ($per_amount > $expect_per) {
                                                $per_diff = str_replace('-', '', $per_diff);
                                                $test[$merchant['merchant_id']][$company['company_id']] = '+'.($per_diff);
                                            } elseif ($per_amount < $expect_per) {
                                                $test[$merchant['merchant_id']][$company['company_id']] = -$per_diff;
                                            }
                                        }
                                    }
                                } else {
                                    $test[$merchant['merchant_id']][$company['company_id']] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        print_r($test);
        dd();
    }

    public function missingInvestorsPayments()
    {
        $test = [];
        $date = isset($_GET['date']) ? $_GET['date'] : '';
        $payments = ParticipentPayment::where('payment_date', $date)->distinct('merchant_id')->pluck('merchant_id')->toArray();
        if ($payments) {
            foreach ($payments as $key1 => $merchant) {
                $companies = DB::table('company_amount')->where('merchant_id', $merchant)->get();
                $investors = MerchantUser::where('merchant_id', $merchant)->pluck('user_id')->toArray();
                $atleastonepayment = 0;
                foreach ($investors as $key => $investor) {
                    $company = User::where('id', $investor)->value('company');
                    $payments = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('payment_investors.merchant_id', $merchant)->where('payment_investors.user_id', $investor)->whereDate('participent_payments.payment_date', $date);
                    $nopayments = $payments->count();
                    if ($nopayments == 0 && $atleastonepayment) {
                        $test[$merchant][$key]['user_id'] = $investor;
                        $test[$merchant][$key]['company'] = User::where('id', $company)->value('name');
                    }
                    $atleastonepayment = $atleastonepayment + $nopayments;
                }
            }
        }
        print_r($test);
        dd();
    }

    public function settledRTR()
    {
        $rtr_balance = DB::table('merchant_user')->whereIn('merchant_user.status', [1, 3])->whereIn('merchants.sub_status_id', [18, 19, 20])->where('merchants.id', 9235)->where(DB::raw('old_factor_rate-factor_rate'), '>', 0)->join('merchants', 'merchant_user.merchant_id', 'merchants.id');
        $rtr_balance = $rtr_balance->groupBy('merchant_user.merchant_id')->pluck(DB::raw('sum(

            (

            (merchant_user.amount*old_factor_rate)

            -

            ((merchant_user.amount*old_factor_rate)

            *(merchant_user.mgmnt_fee)/100)

            )

            -

           (
           (merchant_user.amount*factor_rate)

           -((merchant_user.amount*factor_rate)*(merchant_user.mgmnt_fee)/100))



         ) as rtr_balance'), 'merchant_user.merchant_id')->toArray();
        print_r($rtr_balance);
        dd();
    }

    public function advanceToLessStatusChange()
    {
        $merchants = Merchant::select('sub_status_id', 'id')->whereIn('merchants.sub_status_id', [18, 19, 20])->where('old_factor_rate', '=', 0)->get()->toArray();
        $i = 0;
        if (! empty($merchants)) {
            foreach ($merchants as $merchant) {
                Merchant::find($merchant['id'])->update(['sub_status_id' => 23]);
                $i++;
            }
            echo $i.' Merchants change status to others  !!';
        } else {
            echo 'No merchants avilable now';
        }
    }

    public function changeOldtoNewFactorRate()
    {
        $merchants = Merchant::select('old_factor_rate', 'id', 'factor_rate', 'sub_status_id')->whereIn('merchants.sub_status_id', [18, 19, 20])->where('old_factor_rate', '=', 0)->get()->toArray();
        $i = 0;
        if (! empty($merchants)) {
            foreach ($merchants as $merchant) {
                $update = Merchant::find($merchant['id'])->update(['old_factor_rate' => $merchant['factor_rate']]);
                if ($update) {
                    $investment_data = MerchantUser::select('merchant_id', 'user_id', 'amount')->where('merchant_id', $merchant['id'])->get()->toArray();
                    if (! empty($investment_data)) {
                        foreach ($investment_data as $investments) {
                            $new_factor_rate = MerchantUser::where('merchant_id', $merchant['id'])->where('status', 1)->value(DB::raw('sum(paid_participant_ishare)/sum(amount)'));
                            $update1 = Merchant::find($merchant['id'])->update(['factor_rate' => $new_factor_rate]);
                            $invest_rtr = $new_factor_rate * $investments['amount'];
                            $updt_investor_rtr = MerchantUser::where('user_id', $investments['user_id'])->where('merchant_id', $investments['merchant_id'])->update(['invest_rtr' => $invest_rtr]);
                        }
                    }
                }
                $i++;
            }
            echo $i.'Merchants new factor rate updated successfully !!';
        }
    }

    public function changeFactorRate()
    {
        $merchants = Merchant::select('old_factor_rate', 'id', 'factor_rate')->where(DB::raw('old_factor_rate-factor_rate'), '<', 0)->where('old_factor_rate', '!=', 0)->where('old_factor_rate', '>', 1.1)->get()->toArray();
        $i = 0;
        if (! empty($merchants)) {
            foreach ($merchants as $merchant) {
                $update = Merchant::find($merchant['id'])->update(['factor_rate' => $merchant['old_factor_rate']]);
                $i++;
                if ($update) {
                    $investment_data = MerchantUser::select('merchant_id', 'user_id', 'amount')->where('merchant_id', $merchant['id'])->get()->toArray();
                    if (! empty($investment_data)) {
                        foreach ($investment_data as $investments) {
                            $invest_rtr = $merchant['old_factor_rate'] * $investments['amount'];
                            $updt_investor_rtr = MerchantUser::where('user_id', $investments['user_id'])->where('merchant_id', $investments['merchant_id'])->update(['invest_rtr' => $invest_rtr]);
                        }
                    }
                }
            }
            echo $i.'Merchants factor rate updated successfully !!';
        }
    }

    public function testFire1()
    {
        $merchants1 = DB::table('merchant_user')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->whereIn('merchants.sub_status_id', [18, 19, 20])->groupBy('merchant_user.merchant_id')->orderBy('merchant_user.merchant_id')->pluck(DB::raw('sum(amount+under_writing_fee+pre_paid+commission_amount) as amount'), 'merchant_user.merchant_id')->toArray();
        $merchants3 = $merchants1;
        $merchants2 = DB::table('payment_investors')->join('merchants', 'merchants.id', 'payment_investors.merchant_id')->whereIn('merchants.sub_status_id', [18, 19, 20])->orderBy('payment_investors.merchant_id')->groupBy('payment_investors.merchant_id')->pluck(DB::raw('sum(principal) as principal'), 'payment_investors.merchant_id')->toArray();
        $merchants = [];
        foreach ($merchants1 as $key => $value) {
            $amount = round($merchants1[$key]);
            $principal = round($merchants2[$key]);
            if ($amount < $principal) {
                $merchants[] = $key;
                $this->merchant->modify_payments($key);
            }
        }
        print_r($merchants);
        dd();
    }

    public function testFire()
    {
        $merchants1 = DB::table('merchant_user')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->whereIn('merchants.sub_status_id', [11])->groupBy('merchant_user.merchant_id')->orderBy('merchant_user.merchant_id')->pluck(DB::raw('sum(amount+under_writing_fee+pre_paid+commission_amount) as amount'), 'merchant_user.merchant_id')->toArray();
        $merchants3 = $merchants1;
        $merchants2 = DB::table('payment_investors')->join('merchants', 'merchants.id', 'payment_investors.merchant_id')->whereIn('merchants.sub_status_id', [11])->orderBy('payment_investors.merchant_id')->groupBy('payment_investors.merchant_id')->pluck(DB::raw('sum(principal) as principal'), 'payment_investors.merchant_id')->toArray();
        $merchants = [];
        foreach ($merchants1 as $key => $value) {
            $amount = round($merchants1[$key]);
            $principal = round($merchants2[$key]);
            if ($amount < $principal) {
                $merchants[] = $key;
            }
        }
        print_r($merchants);
        dd();
    }

    public function inBetweenPaymentMerchants()
    {
        $sort = ParticipentPayment::select('merchant_id', 'payment_date')->orderBy('participent_payments.id')->get()->toArray();
        $arr = [];
        foreach ($sort as $key => $value) {
            $arr[$value['merchant_id']][$key] = $value['payment_date'];
        }
        $merchants = [];
        if (! empty($arr)) {
            $flag = 0;
            foreach ($arr as $key1 => $value1) {
                $default = array_values($value1);
                sort($value1);
                foreach ($value1 as $key2 => $value2) {
                    if ($value2 != $default[$key2]) {
                        $per = Merchant::where('id', $key1)->value('complete_percentage');
                        if ($per > 99) {
                            $merchants[] = $key1;
                        }
                        break;
                    }
                }
            }
            print_r(array_values(array_unique($merchants)));
            dd();
        }
    }

    public function inBetweenPaymentMerchants1()
    {
        $payments = ParticipentPayment::select('participent_payments.merchant_id')->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->orderBy('participent_payments.id')->groupBy('participent_payments.id')->get()->toArray();
        if (! empty($payments)) {
            foreach ($payments as $key => $value) {
            }
        }
    }

    public function profitPrincipalChange()
    {
        $merchants = Merchant::select('id')->whereIn('id', [9053])->paginate(1)->toArray();
        foreach ($merchants['data'] as $key => $value) {
            $this->merchant->modify_payments11($value['id']);
        }

        return view('profit_pricipal', ['merchants' => $merchants]);
    }

    public function profitPrincipalChange11()
    {
        $merchant_array = [];
        $merchants = Merchant::select('merchants.id', 'sub_status_id')->whereIn('merchants.sub_status_id', [18, 19, 20])->where('old_factor_rate', '!=', 0)->where('complete_percentage', '<', 100)->get()->toArray();
        $i = 0;
        if (! empty($merchants)) {
            foreach ($merchants as $key => $value) {
                $count = ParticipentPayment::where('participent_payments.payment', '==', 0)->where('merchant_id', $value['id'])->orderByDesc('id')->count();
                if ($count == 0) {
                    $merchant_array[$key] = $value['id'];
                }
            }
        } else {
            echo 'No merchants avilable';
        }
        print_r($merchant_array);
        dd();
    }

    public function updateStatusDateForMerchant()
    {
        $merchants = Merchant::select('last_payment_date', 'id')->whereIn('merchants.sub_status_id', [18, 19, 20])->whereNull('last_status_updated_date')->get()->toArray();
        $i = 0;
        if (! empty($merchants)) {
            foreach ($merchants as $merchant) {
                $last_payment_date = ! empty($merchant['last_payment_date']) ? $merchant['last_payment_date'] : ''.'<br>';
                $new_date = date('Y-m-d', strtotime($last_payment_date.' 30 days'));
                Merchant::find($merchant['id'])->update(['last_status_updated_date' => $new_date]);
                $i++;
            }
            echo $i.' merchants last_status_updated_date updated successfully !!';
        } else {
            echo 'No merchants available here';
        }
    }

    public function zerooverpayments()
    {
        $merchants = PaymentInvestors::whereIn('merchant_id', [9395, 9363, 9139, 8825, 8290, 7763])->update(['overpayment' => 0]);
        echo 'overpayments updated to zero !!';
    }

    public function merchantsBalance()
    {
        $merchants = PaymentInvestors::select('merchant_id')->distinct('merchant_id')->where('balance', 0)->paginate(1)->toArray();
        $count = 0;
        if (! empty($merchants)) {
            foreach ($merchants['data'] as $key => $value) {
                $paymentInvestors = PaymentInvestors::Join('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id')->where('payment_investors.merchant_id', $value['merchant_id'])->select('payment_investors.id', 'payment_investors.user_id', 'payment_investors.merchant_id')->where('balance', 0)->orderBy('participent_payments.payment_date')->get();
                foreach ($paymentInvestors as $paymentInvestor) {
                    $resentPaymentInvestor = PaymentInvestors::where('payment_investors.merchant_id', $paymentInvestor->merchant_id)->leftJoin('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id')->where('payment_investors.user_id', $paymentInvestor->user_id)->where('payment_investors.balance', '!=', 0)->orderByDesc('participent_payments.payment_date')->first();
                    $resentBalance = ($resentPaymentInvestor) ? $resentPaymentInvestor->balance : 0;
                    DB::statement('
                        UPDATE payment_investors
                            INNER JOIN merchant_user ON merchant_user.`merchant_id` = payment_investors.`merchant_id` AND  merchant_user.user_id = payment_investors.user_id
                        SET payment_investors.balance = ( CASE WHEN '.$resentBalance.' != 0 THEN '.$resentBalance.' ELSE  merchant_user.invest_rtr END) - payment_investors.participant_share
                        WHERE payment_investors.id = '.$paymentInvestor->id.' AND payment_investors.overpayment = 0 AND payment_investors.balance = 0
                        ');
                }
            }
        }

        return view('balance', ['merchants' => $merchants]);
    }

    public function overpaymentsforinvestors()
    {
        $count = 0;
        $merchants_arr = Merchant::select('id')->whereIn('id', [8152])->paginate(1);
        $merchants_arr1 = ($merchants_arr->toArray())['data'];
        $merchants_arr2 = [];
        foreach ($merchants_arr1 as $key => $value) {
            $merchants_arr2[] = $value['id'];
        }
        if (! empty($merchants_arr2)) {
            $assigned_investors = MerchantUser::select('invest_rtr', 'merchant_id', 'user_id', 'paid_participant_ishare', 'mgmnt_fee')->where(DB::raw('(invest_rtr- paid_participant_ishare)'), '<', 0)->whereIn('merchant_id', $merchants_arr2)->orderBy('merchant_id')->get()->toArray();
            if ($assigned_investors) {
                foreach ($assigned_investors as $investor) {
                    $overpayments_sum = 0;
                    $participant_share11 = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->select('payment_investors.id as id2', 'payment_investors.participant_share', 'payment_investors.mgmnt_fee')->where('payment_investors.merchant_id', $investor['merchant_id'])->where('payment_investors.user_id', $investor['user_id'])->orderBy('payment_date')->get()->toArray();
                    if (! empty($participant_share11)) {
                        $total = $total_mag = 0;
                        foreach ($participant_share11 as $key => $value) {
                            $total = $total + ($value['participant_share']);
                            $total_mag = $total_mag + ($value['mgmnt_fee']);
                            $mag = ($value['mgmnt_fee']);
                            if ($investor['invest_rtr'] < $total) {
                                $new_value = ($total - $investor['invest_rtr']) * (1 - $investor['mgmnt_fee'] / 100) - $overpayments_sum;
                                $overpayments_sum = $overpayments_sum + $new_value;
                                PaymentInvestors::where('id', $value['id2'])->update(['overpayment' => $new_value]);
                            } else {
                                $new_value = 0;
                            }
                        }
                    }
                }
            }
        }

        return view('welcome', ['assigned_investors' => $merchants_arr]);
    }

    public function lessthan100per()
    {
        $merchants = DB::table('merchant_user')->where(DB::raw('( (amount+commission_amount+pre_paid+under_writing_fee)- paid_participant_ishare )'), '<', 0)->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchants.complete_percentage', '<', 99)->where('merchants.sub_status_id', 11)->groupBy('merchant_user.merchant_id')->pluck('merchant_user.merchant_id')->toArray();
        print_r($merchants);
        dd();
    }

    public function overpaymentMerchants()
    {
        $merchants = MerchantUser::join('merchants', 'merchants.id', 'merchant_user.merchant_id')->select('merchants.id')->where(DB::raw('complete_percentage -  (paid_participant_ishare / invest_rtr * 100 )'), '<', -1)->where(DB::raw('(invest_rtr- paid_participant_ishare )'), '<', -1)->groupBy('merchants.id');
        print_r($merchants->get()->toArray());
        dd();
    }

    public function changeStatusAction()
    {
        $merchants = Merchant::select('sub_status_id', 'id')->where('complete_percentage', '>=', 100)->where('sub_status_id', '!=', 11);
        if ($merchants->count() > 0) {
            $mers = $merchants->get()->toArray();
            if (! empty($mers)) {
                foreach ($mers as $key => $value) {
                    Merchant::find($value['id'])->update(['sub_status_id' => 11]);
                }
            }
            echo 'Changed all merchants status completed deal to advance completed successfully.';
        }
    }

    public function test()
    {
        $carry_forwards = DB::table('carry_forwards')->select('investor_id', DB::raw('sum(amount) as carry_amount'))->groupBy('investor_id')->get();
        foreach ($carry_forwards as $carry_forward) {
            $data[$carry_forward->investor_id] = round($carry_forward->carry_amount, 2);
        }

        return $data;
        $number = 1234567890.56;
        setlocale(LC_MONETARY, 'en_US');
        echo money_format('The price is %i', $number);
        exit();
        $my_file = 'file.txt';
        $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
        $data = 'Test data to see if this works!';
        fwrite($handle, $data);
        $storagePath = Storage::disk('s3')->put('investor-portal', $my_file, config('filesystems.disks.s3.privacy'));
    }

    public function api_test()
    {
        return response()->json(203, 204);
    }

    public function mail_log(Request $request)
    {
        $mid = 0;
        $input = $request->all();
        $new_arr = [];
        $merchants = Merchant::leftJoin('users', 'users.merchant_id_m', 'merchants.id')->where('merchants.active_status', 1)->where('sub_status_id', 1)->where('merchants.label', 1)->where('merchants.lender_id', 74)->select(['users.email', 'merchants.name', 'merchants.id', 'merchants.date_funded', 'merchants.notification_email', DB::raw('DATEDIFF(NOW(),date_funded) as diff')]);
        if (isset($input['merchants'])) {
            $mid = $input['merchants'];
            $merchants = $merchants->where('merchants.id', $mid);
        }
        $merchants = $merchants->get();
        $k = 0;
        $date1 = date('y-m-d', strtotime('-30 days'));
        $date2 = date('y-m-d');
        foreach ($merchants as $mer) {
            if ($mer->diff >= 30) {
                $days = $mer->diff;
                $cnt = $days / 30;
                $check = DB::table('mail_log')->where('to_id', $mer->id)->whereDate('created_at', '>=', $date1)->whereDate('created_at', '<=', $date2)->where('status', 'success')->count();
                if ($check <= 0) {
                    $new_arr[$k]['name'] = $mer->name;
                    $new_arr[$k]['id'] = $mer->id;
                    $new_arr[$k]['days'] = $mer->diff;
                    $new_arr[$k]['email'] = $mer->notification_email;
                    $new_arr[$k]['date_funded'] = $mer->date_funded;
                    $k++;
                }
            }
        }
        $merchant_list = Merchant::where('active_status', 1)->whereIn('sub_status_id', [1, 5]);
        $merchant_list = $merchant_list->pluck('name', 'id');

        return view('mail_log', ['merchants' => $new_arr, 'merchant_list' => $merchant_list, 'merchant_id' => $mid]);
    }

    public function resendReconciliation(Request $request)
    {
        $merchant_id = $request->mer_id;
        $merchants = Merchant::leftJoin('users', 'users.merchant_id_m', 'merchants.id')->where('merchants.id', $merchant_id)->select(['users.email', 'merchants.notification_email', 'merchants.name', 'merchants.id', 'merchants.date_funded', DB::raw('DATEDIFF(NOW(),date_funded) as diff')])->first();
        $day_diff = $merchants->diff;
        if ($merchants->notification_email == null) {
            $request->session()->flash('error_message', 'This merchant do not have an email id.');

            return redirect()->back();
        }
        if ($day_diff > 0) {
            if ($merchants->notification_email != null) {
                $message['title'] = 'Reconciliation request';
                $message['merchant_name'] = $merchants->name;
                $message['subject'] = 'Reconciliation request';
                $message['to_mail'] = $merchants->notification_email;
                $message['to_id'] = $merchants->id;
                $message['status'] = '30day merchant mail notification';
                $message['unqID'] = unqID();
                $message['days'] = $day_diff;
                try {
                    DB::table('mail_log')->insert(['title' => '30 days mail notification for merchants', 'to_mail' => $merchants->notification_email, 'to_user_type' => 'merchant', 'to_id' => $merchants->id, 'status' => 'success', 'creator_id' => $request->user()->id]);
                    $email_template = Template::where([
                        ['temp_code', '=', 'RECR'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        if ($email_template->assignees) {
                            $template_assignee = explode(',', $email_template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                                $bcc_mails[] = $role_mails;    
                            }
                            $message['bcc'] = Arr::flatten($bcc_mails);
                        }
                        $settings = Settings::where('keys', 'admin_email_address')->first();
                        $admin_email = $settings->values;
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['to_mail'] = $admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                        $request->session()->flash('success_message', 'Mail sent successfully');
                    }

                    return redirect()->back();
                } catch (\Exception $e) {
                    DB::table('mail_log')->insert(['title' => '30 days mail notification for merchants', 'to_mail' => $merchants->notification_email, 'status' => 'failed', 'to_user_type' => 'merchant', 'to_id' => $merchants->id, 'failed_message' => $e->getMessage(), 'creator_id' => $request->user()->id]);
                    $request->session()->flash('error_message', $e->getMessage());

                    return redirect()->back();
                }
            }
            $request->session()->flash('error_message', 'mail sending failed');

            return redirect()->back();
        }
    }

    public function balance_difference()
    {
        $i = 0;
        $merchants = Merchant::where('active_status', 1)->get();
        foreach ($merchants as $mer) {
            $balance = MerchantUser::where('merchant_id', $mer->id)->select(DB::raw('sum(
         (invest_rtr-paid_participant_ishare)) as balance'), 'merchant_id')->first();
            $payments = DB::table('participent_payments')->where('participent_payments.merchant_id', $mer->id)->orderBy('participent_payments.id')->select(DB::raw(' (select sum(payment_investors.balance) from payment_investors WHERE participent_payments.id=payment_investors.participent_payment_id) as balance'), 'participent_payments.id')->first();
            if ($payments) {
                if ($payments->balance == 0) {
                    $i++;
                    echo 'merchant id = '.$mer->id.'  ,  actual balance = '.$balance->balance;
                    echo '<br>';
                }
            }
        }
        echo 'no of merchants = '.$i;
    }

    public function merchantsBalanceUpdate()
    {
        $merchants = PaymentInvestors::select('merchant_id')->distinct('merchant_id')->where('balance', 0)->whereIn('merchant_id', [9106, 9186, 9209, 9278, 9292, 9354, 9365, 9504])->get()->toArray();
        $count = 0;
        if (! empty($merchants)) {
            foreach ($merchants as $key => $value) {
                $paymentInvestors = PaymentInvestors::Join('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id')->where('payment_investors.merchant_id', $value['merchant_id'])->select('payment_investors.id', 'payment_investors.user_id', 'payment_investors.merchant_id')->where('balance', 0)->orderBy('participent_payments.payment_date')->get();
                foreach ($paymentInvestors as $paymentInvestor) {
                    $resentPaymentInvestor = PaymentInvestors::where('payment_investors.merchant_id', $paymentInvestor->merchant_id)->leftJoin('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id')->where('payment_investors.user_id', $paymentInvestor->user_id)->where('payment_investors.balance', '!=', 0)->orderByDesc('participent_payments.payment_date')->first();
                    $resentBalance = ($resentPaymentInvestor) ? $resentPaymentInvestor->balance : 0;
                    DB::statement('
                        UPDATE payment_investors
                            INNER JOIN merchant_user ON merchant_user.`merchant_id` = payment_investors.`merchant_id` AND  merchant_user.user_id = payment_investors.user_id
                        SET payment_investors.balance = ( CASE WHEN '.$resentBalance.' != 0 THEN '.$resentBalance.' ELSE  merchant_user.invest_rtr END) - payment_investors.participant_share
                        WHERE payment_investors.id = '.$paymentInvestor->id.' AND payment_investors.overpayment = 0 AND payment_investors.balance = 0
                        ');
                }
            }
        }
        echo 'Done';
    }

    public function check_all_balance_zero()
    {
        $merchants = PaymentInvestors::select('merchant_id')->distinct('merchant_id')->get();
        foreach ($merchants as $mer) {
            $check_bal_updated = DB::table('payment_investors')->where('payment_investors.merchant_id', $mer->merchant_id)->where('balance', '>', 0)->count();
            if ($check_bal_updated <= 0) {
                echo $mer->merchant_id;
                echo '<br>';
            }
        }
    }

    public function getUpdateInvestorPayments()
    {
        $userIds = DB::table('payment_investors')->distinct()->select('user_id')->pluck('user_id')->toArray();
        foreach ($userIds as $userId) {
            InvestorHelper::updatePaymentValues($userId);
        }
    }

    public function getUpdateInvestorPrincipal()
    {
        $userIds = DB::table('merchant_user')->select('user_id')->pluck('user_id')->toArray();
        echo "<br> Loop Start";
        foreach ($userIds as $userId) {
            echo "<br>".$userId;
            set_time_limit(0);
            InvestorHelper::updateUserPrincipal($userId);
        }
        echo "<br>Loop End successfully";
    }

    public function currentInvestedForInvestors() {
        $investors = DB::table('merchant_user_views')
        ->orderBy('merchant_user_views.company')
        ->groupBy('merchant_user_views.investor_id')
        ->whereNotIn('merchant_user_views.sub_status_id',[4,22])
        ->pluck(DB::raw('sum(total_investment)'), 'investor_id')
        ->toArray();
        $payments = DB::table('payment_investors')
        ->join('merchants', 'merchants.id', 'payment_investors.merchant_id')
        ->whereNotIn('merchants.sub_status_id', [4, 22])
        ->groupBy('payment_investors.user_id')
        ->pluck(DB::raw('sum(payment_investors.principal) as principal'), 'payment_investors.user_id');
        $merchant_users = DB::table('merchant_user')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
        ->whereNotIn('merchants.sub_status_id', [4, 22])
        ->groupBy('merchant_user.user_id')
        ->pluck(DB::raw('sum(merchant_user.paid_principal) as principal'), 'merchant_user.user_id');
        $current_invested = [];
        if ($investors) {
            foreach ($investors as $user_id => $investment) {
                $UserMeta=\App\UserMeta::where('user_id', $user_id)->where('key', '_pi_normal_total_principal')->first();
                $_pi_normal_total_principal=0;
                if($UserMeta){ 
                    $_pi_normal_total_principal=$UserMeta->value;
                }
                $User=User::find($user_id);
                $single['user_id']                 = $user_id;
                $single['name']                    = $User->name;
                $single['company']                 = $User->company;
                $single['investment']              =$investment;
                $payment=isset($payments[$user_id]) ? $payments[$user_id] : 0;
                $merchant_user=isset($merchant_users[$user_id]) ? $merchant_users[$user_id] : 0;
                $single['payment']                 =round($investment-$payment,2);
                $single['merchant_user']           =round($investment-$merchant_user,2);
                $single['payment_merchant_user']   =round($single['payment']-$single['merchant_user'],2);
                $single['user_meta']               =round($investment-$_pi_normal_total_principal,2);
                $single['merchant_user_user_meta'] =round($single['merchant_user']-$single['user_meta'],2);
                $current_invested[]                =$single;
            }
        }
        echo \App\Settings::tableView($current_invested); exit;
    }

    public function UserMetaVsMerchantUserVsPayment() {
        $investor_ids    = Role::whereName('investor')->first()->users()->pluck('name','users.id')->toArray();
        $overpayments    = Role::whereName('Over Payment')->first()->users()->pluck('name','users.id')->toArray();
        $AgentFeeAccount = Role::whereName('Agent Fee Account')->first()->users()->pluck('name','users.id')->toArray();
        $users = $investor_ids+$overpayments+$AgentFeeAccount;
        $payments = DB::table('payment_investors')
        ->join('merchants', 'merchants.id', 'payment_investors.merchant_id')
        ->groupBy('payment_investors.user_id')
        ->pluck(DB::raw('sum(payment_investors.participant_share) as participant_share'), 'payment_investors.user_id');
        $paymentsfees = DB::table('payment_investors')
        ->join('merchants', 'merchants.id', 'payment_investors.merchant_id')
        ->groupBy('payment_investors.user_id')
        ->pluck(DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'), 'payment_investors.user_id');
        $merchant_users = DB::table('merchant_user')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
        ->groupBy('merchant_user.user_id')
        ->pluck(DB::raw('sum(merchant_user.paid_participant_ishare) as participant_share'), 'merchant_user.user_id');
        $merchant_users_fees = DB::table('merchant_user')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
        ->groupBy('merchant_user.user_id')
        ->pluck(DB::raw('sum(merchant_user.paid_mgmnt_fee) as mgmnt_fee'), 'merchant_user.user_id');
        $list = $total =[];
        if ($users) {
            $total['user_id']                         = '';
            $total['name']                            = '';
            $total['Seperator']                       = '';
            $total['payment_participant_share']       = 0;
            $total['merchant_user_participant_share'] = 0;
            $total['UserMeta_participant_share']      = 0;
            $total['payment_merchant_user']           = 0;
            $total['payment_UserMeta']                = 0;
            $total['Seperator1']                      = '';
            $total['payment_fee']                     = 0;
            $total['merchant_user_fee']               = 0;
            $total['UserMeta_fee']                    = 0;
            $total['payment_merchant_user_fee']       = 0;
            $total['payment_UserMeta_fee']            = 0;
            foreach ($users as $user_id => $name) {
                $UserMeta=UserMeta::where('user_id', $user_id)->where('key', '_pi_total_participant_share')->first();
                $_pi_total_participant_share     = 0;
                if($UserMeta){ 
                    $_pi_total_participant_share = $UserMeta->value;
                }
                $UserMeta=UserMeta::where('user_id', $user_id)->where('key', '_pi_total_mgmnt_fee')->first();
                $_pi_total_mgmnt_fee     = 0;
                if($UserMeta){ 
                    $_pi_total_mgmnt_fee = $UserMeta->value;
                }
                $single['user_id']                         = $user_id;
                $single['name']                            = $name;
                $payment                                   = isset($payments[$user_id]) ? $payments[$user_id] : 0;
                $paymentsfee                               = isset($paymentsfees[$user_id]) ? $paymentsfees[$user_id] : 0;
                $merchant_user                             = isset($merchant_users[$user_id]) ? $merchant_users[$user_id] : 0;
                $merchant_userfee                          = isset($merchant_users_fees[$user_id]) ? $merchant_users_fees[$user_id] : 0;
                $single['Seperator']                       = '';
                $single['payment_participant_share']       = round($payment,2);
                $single['merchant_user_participant_share'] = round($merchant_user,2);
                $single['UserMeta_participant_share']      = round($_pi_total_participant_share,2);
                $single['payment_merchant_user']           = round($single['payment_participant_share']-$single['merchant_user_participant_share'],2);
                $single['payment_UserMeta']                = round($single['payment_participant_share']-$single['UserMeta_participant_share'],2);
                $single['Seperator1']                      = '';
                $single['payment_fee']                     = round($paymentsfee,2);
                $single['merchant_user_fee']               = round($merchant_userfee,2);
                $single['UserMeta_fee']                    = round($_pi_total_mgmnt_fee,2);
                $single['payment_merchant_user_fee']       = round($single['payment_fee']-$single['merchant_user_fee'],2);
                $single['payment_UserMeta_fee']            = round($single['payment_fee']-$single['UserMeta_fee'],2);
                $list[] =$single;
                $total['payment_participant_share']       +=$single['payment_participant_share'];
                $total['merchant_user_participant_share'] +=$single['merchant_user_participant_share'];
                $total['UserMeta_participant_share']      +=$single['UserMeta_participant_share'];
                $total['payment_merchant_user']           +=$single['payment_merchant_user'];
                $total['payment_UserMeta']                +=$single['payment_UserMeta'];
                $total['payment_fee']                     +=$single['payment_fee'];
                $total['merchant_user_fee']               +=$single['merchant_user_fee'];
                $total['UserMeta_fee']                    +=$single['UserMeta_fee'];
                $total['payment_merchant_user_fee']       +=$single['payment_merchant_user_fee'];
                $total['payment_UserMeta_fee']            +=$single['payment_UserMeta_fee'];
            }
        }
        $list[]=$total;
        echo \App\Settings::tableView($list); exit;
    }
    public function CTDForInvestors()
    {
        $investors = DB::table('merchant_user_views')
        ->orderBy('merchant_user_views.company')
        ->groupBy('merchant_user_views.investor_id')
        ->pluck('investor_id', 'investor_id')
        ->toArray();
        $payment_investors = DB::table('payment_investors')
        ->groupBy('payment_investors.user_id')->pluck(DB::raw('sum(participant_share-mgmnt_fee) as ctd'), 'payment_investors.user_id');
        $merchant_users = DB::table('merchant_user')
        ->groupBy('merchant_user.user_id')->pluck(DB::raw('sum(paid_participant_ishare-paid_mgmnt_fee) as ctd'), 'merchant_user.user_id');
        $CTD = [];
        if ($investors) {
            foreach ($investors as $user_id => $investment) {
                $_pi_total_participant_share =\App\UserMeta::where('user_id', $user_id)->where('key', '_pi_total_participant_share')->first()->value??0;
                $_pi_total_mgmnt_fee         =\App\UserMeta::where('user_id', $user_id)->where('key', '_pi_total_mgmnt_fee')->first()->value??0;
                $UserMeta                    =round($_pi_total_participant_share-$_pi_total_mgmnt_fee,2);
                $User=User::find($user_id);
                $single['user_id']                 = $user_id;
                $single['name']                    = $User->name;
                $single['company']                 = $User->company;
                $payment_investor                  =isset($payment_investors[$user_id]) ? $payment_investors[$user_id] : 0;
                $merchant_user                     =isset($merchant_users[$user_id]) ? $merchant_users[$user_id] : 0;
                $single['payment']                 =round($payment_investor,2);
                $single['merchant_user']           =round($merchant_user,2);
                $single['payment_merchant_user']   =round($single['payment']-$single['merchant_user'],2);
                $single['user_meta']               =round($UserMeta,2);
                $single['merchant_user_user_meta'] =round($single['merchant_user']-$single['user_meta'],2);
                $CTD[]                             =$single;
            }
        }
        echo \App\Settings::tableView($CTD); exit;
    }

    public function test_stripe()
    {
        $kk = [];
        $kk[] = ('mysqldump -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').' -P25060 '.config('app.database').' > first.sql');
        $kk[] = (' yes | mysqladmin -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').'  -P25060  drop '.config('app.database2'));
        $kk[] = ('mysqladmin -h '.config('app.db_url').'   -u '.config('app.username').' -p'.config('app.password').'  -P25060  create '.config('app.database2'));
        $kk[] = ('mysqldump -h '.config('app.db_url').'  -u '.config('app.username').' -p'.config('app.password').'  -P25060 '.config('app.database2').' < first.sql');

        return $kk;

        return $carry_forwards = DB::table('payment_investors')->select(DB::raw('sum(overpayment) as overpayment'))->where('payment_investors.id', '>', '3584424')->where('payment_investors.user_id', '=', '59')->get();
        $carry_forwards = DB::table('carry_forwards')->select('investor_id', DB::raw('sum(amount) as carry_amount'))->groupBy('investor_id')->get();
        foreach ($carry_forwards as $carry_forward) {
            $data[$carry_forward->investor_id] = round($carry_forward->carry_amount, 2);
        }

        return $data;

        return date('Y-m-d');

        return datetime('2020-12-23 05:28:41');

        return config('app.stripe_key');
    }

    public function apitest(Request $request)
    {
        $lender = $request->lenders;
        $attribute = $request->attribute;
        $type = $request->graph_value;
        $subinvestors = [];
        $date_start = '2010-01-01';
        $date_end = date('Y-m-d');
        if (isset($request->date_start) && $request->date_start != '') {
            $date_start = $request->date_start;
        }
        if (isset($request->date_end) && $request->date_end != '') {
            $date_end = $request->date_end;
        }
        $query987 = \MTB::getChartData($request->attribute, $request->graph_value, 2);
        if (isset($request->label) && $request->label != '') {
            $query987 = $query987->where('merchants.label', $request->label);
        }
        if (isset($request->label) && $request->label != '') {
            $query987 = $query987->where('merchants.label', $request->label);
        }
        if ($request->date_start != '' || $request->date_end != '') {
            $query987 = $query987->whereBetween('merchants.date_funded', [$date_start, $date_end]);
        }
        $result_arr = ($query987->get())->toArray();
        if ($request->attribute == 8 || $request->attribute == 6 || $request->attribute == 5) {
            $header = [0 => ['Total', ''], 1 => ['Average', ''], 2 => ['', ''], 3 => ['Name', 'Amount']];
        } else {
            $header = [0 => ['Total', ''], 1 => ['Average', ''], 2 => ['', ''], 3 => ['', 'Name', 'Amount']];
        }
        array_unshift($result_arr, $header);
        $export = new Merchant_Graph($result_arr, count($result_arr), $request->attribute);

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'merchant_graph.xlsx');
    }

    public function pdfView()
    {
        $pdf = PDF::loadView('pdfview');
        $filePDFName = 'test.pdf';

        return $pdf->stream($filePDFName);
    }

    public function htmlView()
    {
        return view('htmlview');
    }

    public function paymentTest()
    {
        $data = DB::table('payment_investors')->select('investment_id')->distinct()->get();
        foreach ($data as $dt) {
            $cnt = DB::table('merchant_user')->where('id', $dt->investment_id)->count();
            if ($cnt <= 0) {
                print_r($dt);
                echo $cnt.'=='.$dt->investment_id;
                echo '<br>';
            }
        }
    }

    public function testVpPayments()
    {
        $vp_merchants = MerchantUser::join('users', 'users.id', 'merchant_user.user_id')->where('users.company', 58)->distinct('merchant_user.merchant_id')->pluck('merchant_id')->toArray();
        $merchants = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->whereIn('merchant_user.status', [1, 3])->groupBy('merchants.id')->select('merchants.id', 'merchants.name', 'merchants.date_funded', 'merchants.pmnts', 'merchants.sub_status_id', DB::raw('sum(invest_rtr) as i_rtr,sum( invest_rtr * mgmnt_fee/100) as mgmnt_fee'))->whereIn('merchants.id', $vp_merchants)->whereIn('merchants.label', [4, 5])->where('merchants.date_funded', '>=', '2020-10-02')->get();
        foreach ($merchants as $data) {
            $payment_unique_date = ParticipentPayment::where('payment_type', 1)->where('payment', '!=', 0)->where('participent_payments.merchant_id', $data['id']);
            $payment_unique_date = $payment_unique_date->groupBy('payment_date')->get()->toArray();
            $data1 = ParticipentPayment::select([DB::raw('sum(actual_participant_share-payment_investors.mgmnt_fee) as final_participant_share')])->where('participent_payments.merchant_id', $data['id'])->leftjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->first();
            $no_of_pyments_paid = count($payment_unique_date);
            $payment = (($data['i_rtr'] - $data['mgmnt_fee']) / $data['pmnts']) * $no_of_pyments_paid;
            echo 'merchant id = '.$data['id'].' , ';
            echo 'calculated payment amount = '.$payment.' , ';
            echo 'To participant share = '.$data1->final_participant_share;
            echo '<br>';
        }
    }

    public function updateMaxParticipantFund($id = null)
    {
        DB::beginTransaction();
        $merchants = DB::table('merchants');
        $merchants = $merchants->select('id', 'name', 'rtr', 'funded');
        $merchants = $merchants->whereIn('label', [4, 5]);
        if ($id != null) {
            $merchants = $merchants->where('id', $id);
        }
        $merchants = $merchants->get();
        $companies = DB::table('users')->where('company', '!=', 'null')->pluck('company', 'company');
        $table = [];
        foreach ($merchants as $data) {
            $singleRow['Merchant'] = $data->name;
            foreach ($companies as $company) {
                $singleRow['company'] = $company;
                $company_merchant_investor_amount = DB::table('merchant_user')->join('users', 'users.id', 'merchant_user.user_id')->where('users.company', $company)->where('merchant_id', $data->id)->sum('amount');
                $singleRow['invested_amount'] = round($company_merchant_investor_amount, 4);
                $company_amount = new CompanyAmount;
                $company_amount = $company_amount->where('merchant_id', $data->id);
                $company_amount = $company_amount->where('company_id', $company);
                $company_amount = $company_amount->first();
                $singleRow['company_amount'] = 0;
                if ($company_amount) {
                    $singleRow['company_amount'] = $company_amount->max_participant;
                    $company_amount->max_participant = $company_merchant_investor_amount;
                    $company_amount->save();
                }
                $singleRow['diffrence'] = round($singleRow['company_amount'] - $singleRow['invested_amount'], 4);
                $table[] = $singleRow;
            }
            $max_participant_fund = DB::table('company_amount')->where('merchant_id', $data->id)->sum('max_participant');
            DB::table('merchants')->where('id', $data->id)->update(['funded' => $max_participant_fund, 'max_participant_fund' => $max_participant_fund, 'rtr' => DB::raw('round(max_participant_fund*factor_rate,4)'), 'payment_amount' => DB::raw('(rtr/pmnts)')]);
        }
        DB::commit();
        echo \App\Settings::TableView($table);
        exit;
    }

    public function updateMaxParticipantFundOld()
    {
        $vp_merchants = MerchantUser::join('users', 'users.id', 'merchant_user.user_id')->where('users.company', 58)->distinct('merchant_user.merchant_id')->pluck('merchant_id')->toArray();
        $merchants = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->whereIn('merchant_user.status', [1, 3])->groupBy('merchants.id')->select('merchants.id', 'merchants.name', 'merchants.date_funded', 'merchants.pmnts', 'merchants.sub_status_id', DB::raw('sum(invest_rtr) as i_rtr'))->whereIn('merchants.id', $vp_merchants)->whereIn('merchants.label', [4, 5])->where('merchants.date_funded', '>=', '2020-10-02')->get();
        foreach ($merchants as $data) {
            echo $data['id'].'<br>';
            $max_participant_amount = DB::table('company_amount')->where('merchant_id', $data['id'])->sum('max_participant');
            DB::table('merchants')->where('id', $data['id'])->update(['max_participant_fund' => $max_participant_amount, 'funded' => $max_participant_amount]);
        }
    }

    public function timeformat()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        $tables = DB::select('SHOW TABLES');
        foreach (array_chunk($tables, 5) as $tabl) {
            foreach ($tabl as $table) {
                foreach ($table as $key => $value) {
                    $table = DB::table($value)->get();
                    foreach ($table as $val) {
                        $date = Carbon::createFromFormat('Y-m-d H:i:s', $val->created_at, 'America/New_york');
                        $val->created_at = $date->setTimezone('UTC');
                    }
                    $tab = $table->toArray();
                    $data = json_decode(json_encode($tab), true);
                    foreach (array_chunk($data, 1000) as $t) {
                        DB::table($value)->upsert($t, 'created_at');
                    }
                }
            }
        }

        return 'Time Zone Updated';
    }

    public static function profitToCarry()
    {
        $merchant_ids = Merchant::select('id')->whereIn('sub_status_id', [4, 22])->get();
        foreach ($merchant_ids as $key => $value) {
            $merchant_id = $value->id;
            $select_query = "SELECT
            last_status_updated_date,
            payment_investors.user_id,
            payment_investors.merchant_id,
            sum(profit) AS profit,
            '2' AS type
            FROM payment_investors
            JOIN merchants ON merchants.id=payment_investors.merchant_id 
           
            WHERE payment_investors.merchant_id=$merchant_id
            GROUP BY payment_investors.user_id";
            $tester = DB::query($select_query);
            $status_query = CarryForward::insertUsing(['date', 'investor_id', 'merchant_id', 'amount', 'type'], $select_query);
        }
        echo ' delete from carry_forwards where type=2;// Before re-run, Run it before php artisan change:oldDefaultMerchant ';
    }

    public function changeRcodeNotes()
    {
        $m_notes = MNotes::where('note', 'like', '%Rcode%')
            ->where('note', 'like', '%due to Rcode%')
            ->get();
        $count = 0;
        $data = [];
        foreach ($m_notes as $m_note) {
            $data[] = $m_note;
            $note = $m_note->note;
            $created_at = $m_note->created_at;
            $created_at_new = \FFM::datetime($created_at);
            $search = '#(merchant on).*?(due to Rcode)#';
            $new_note = preg_replace($search, '$1 '.$created_at_new.' $2', $note);
            $m_note->note = $new_note;
            $m_note->save();
            $count++;
        }
        $m_notes = MNotes::where('note', 'like', '%Rcode%')
            ->where('note', 'like', '%from the Merchant on%')
            ->get();
        foreach ($m_notes as $m_note) {
            $data[] = $m_note;
            $note = $m_note->note;
            $created_at = $m_note->created_at;
            $note_explode = explode('erchant on', $note, 2);
            $created_at_new = \FFM::datetime($created_at);
            $new_note = $note_explode[0].'erchant on '.$created_at_new;
            $m_note->note = $new_note;
            $m_note->save();
            $count++;
        }
        if ($count > 0) {
            $data['count'] = $count;

            return $data;
        }

        return 'Nothing to update';
    }

    public function deleteEmptyTerms()
    {
        $terms = MerchantPaymentTerm::doesntHave('payments')->get();
        foreach ($terms as $term) {
            $term->delete();
        }

        return $terms;
    }

    public function CompanyAmountShareDiffrence()
    {
        $Merchant = new Merchant;
        $Merchant = $Merchant->whereIn('label', [1, 2]);
        $Merchant = $Merchant->get();
        $data = [];
        foreach ($Merchant as $key => $value) {
            $merchant_id = $value->id;
            $CompanyAmount = CompanyAmount::where('merchant_id', $merchant_id)->sum('max_participant');
            $single['merchant_id'] = $merchant_id;
            $single['complete_percentage'] = $value->complete_percentage;
            $single['funded'] = $value->max_participant_fund;
            $single['company_share'] = $CompanyAmount;
            $single['company_shares'] = CompanyAmount::where('merchant_id', $merchant_id)->where('max_participant', '!=', 0)->pluck('max_participant', 'company_id')->toJson();
            $single['investor_shares'] = MerchantUserView::where('merchant_id', $merchant_id)->groupBy('company')->get(['company', DB::raw('amount')])->toJson();
            $single['diff'] = round($single['funded'] - $single['company_share']);
            if ($single['diff'] > 0) {
                $data[] = $single;
            }
        }
        echo \App\Settings::TableView($data);
        exit;
    }

    public function InvestorRTRShareDiffrence()
    {
        $Merchant = new Merchant;
        $Merchant = $Merchant->whereIn('label', [1, 2]);
        $Merchant = $Merchant->get();
        $data = [];
        foreach ($Merchant as $MerchantSingle) {
            $merchant_id = $MerchantSingle->id;
            $single['merchant_id'] = $merchant_id;
            $single['complete_percentage'] = $MerchantSingle->complete_percentage;
            $single['rtr'] = $MerchantSingle->rtr;
            $single['factor_rate'] = $MerchantSingle->factor_rate;
            $MerchantUserView = MerchantUserView::where('merchant_id', $merchant_id)->get();
            foreach ($MerchantUserView as $value) {
                $single['investor_id'] = $value['investor_id'];
                $single['current_invest_rtr'] = $value['invest_rtr'];
                $single['actual_invest_rtr'] = round($value['amount'] * $MerchantSingle->factor_rate, 5);
                $single['investor_rtr_diff'] = round($single['current_invest_rtr'] - $single['actual_invest_rtr'], 5);
                if ($single['investor_rtr_diff'] != 0) {
                    $data[] = $single;
                }
            }
        }
        echo \App\Settings::TableView($data);
        exit;
    }

    public function InvestorRTRShareDiffrenceGroup()
    {
        $Merchant = new Merchant;
        $Merchant = $Merchant->get();
        $data = [];
        foreach ($Merchant as $MerchantSingle) {
            $merchant_id = $MerchantSingle->id;
            $single['merchant_id'] = $merchant_id;
            $single['complete_percentage'] = $MerchantSingle->complete_percentage;
            $single['rtr'] = $MerchantSingle->max_participant_fund * $MerchantSingle->factor_rate;
            $single['factor_rate'] = $MerchantSingle->factor_rate;
            $MerchantUserView = MerchantUserView::where('merchant_id', $merchant_id)->get();
            $single['investor_rtr_current'] = round(MerchantUserView::where('merchant_id', $merchant_id)->sum('invest_rtr'), 5);
            $single['investor_rtr_actual'] = round(MerchantUserView::where('merchant_id', $merchant_id)->sum(DB::raw('amount*factor_rate')), 5);
            $single['investor_diff'] = round($single['investor_rtr_actual'] - $single['investor_rtr_current'], 5);
            $single['before_update_diff'] = round($single['rtr'] - $single['investor_rtr_current'], 5);
            $single['after_update_diff'] = round($single['rtr'] - $single['investor_rtr_actual'], 5);
            if ($single['investor_rtr_current'] != 0) {
                if ($single['investor_diff'] != 0 || $single['before_update_diff'] != 0 || $single['after_update_diff'] != 0) {
                    $data[] = $single;
                }
            }
        }
        echo 'UPDATE merchant_user inner join merchants as M on M.id=merchant_user.merchant_id SET merchant_user.invest_rtr = round((merchant_user.amount*M.factor_rate),5)';
        echo \App\Settings::TableView($data);
        exit;
    }

    public function NetEffectForPrinciplaProfitMngmentFeeAndShare()
    {
        $Merchant = new Merchant;
        $Merchant = $Merchant->whereIn('label', [1, 2]);
        $Merchant = $Merchant->whereNotIn('sub_status_id', [4, 18, 19, 20, 22]);
        $Merchant = $Merchant->get();
        $data = [];
        foreach ($Merchant as $MerchantSingle) {
            $merchant_id = $MerchantSingle->id;
            $PaymentInvestors = new PaymentInvestors;
            $PaymentInvestors = $PaymentInvestors->where('merchant_id', $merchant_id);
            $PaymentInvestors = $PaymentInvestors->where(DB::raw('round(participant_share-mgmnt_fee-principal-profit,2)'), '!=', 0);
            $PaymentInvestors = $PaymentInvestors->get();
            $single['merchant_id'] = $merchant_id;
            foreach ($PaymentInvestors as $key => $value) {
                $single['user_id'] = $value->user_id;
                $single['participant_share'] = $value->participant_share;
                $single['mgmnt_fee'] = $value->mgmnt_fee;
                $single['principal'] = $value->principal;
                $single['profit'] = $value->profit;
                $single['net_effect'] = $single['participant_share'];
                $single['net_effect'] -= $single['mgmnt_fee'];
                $single['net_effect'] -= $single['principal'];
                $single['net_effect'] -= $single['profit'];
                $single['net_effect'] = round($single['net_effect'], 2);
                $single['created_at'] = $value->created_at;
                if (abs($single['net_effect']) > 0.01) {
                    $data[] = $single;
                }
            }
        }
        echo \App\Settings::TableView($data);
        exit;
    }

    public function actualPaymentLeft()
    {
        $Merchant = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->select('merchants.id', 'merchants.name', 'merchants.rtr', 'merchants.pmnts', 'merchants.actual_payment_left', DB::raw('sum(actual_paid_participant_ishare ) as actual_paid_participant_ishare '), DB::raw('sum(invest_rtr) as total_rtr'))->groupBy('merchants.id')->where('complete_percentage', '<', 100)->get();
        foreach ($Merchant as $data) {
            echo $data->id.'===';
            if (count($Merchant) > 0) {
                $total_rtr = $data->total_rtr;
                $bal_rtr = $total_rtr - $data->actual_paid_participant_ishare;
                if ($data->pmnts != 0 && $total_rtr != 0) {
                    $actual_payment_left = ($data->rtr) ? $bal_rtr / (($total_rtr / $data->pmnts)) : 0;
                    echo $data->actual_payment_left.'===='.$actual_payment_left;
                    echo '<br>';
                }
            }
        }
    }

    public function correctMailboxrowsInvestorPayments($date = null)
    {
        $ach_user_id = config('settings.ach_user_id');

        if (Auth::loginUsingId($ach_user_id)) {
            try {
                if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
                    $filter_date = Carbon::createFromFormat('Y-m-d', $date);
                } else {
                    throw new Exception('No date found', 1);
                }
            } catch(Exception $e) {
                $filter_date = '2021-04-02';
            }
            $notifications = Mailboxrow::
            whereDate('created_at', '>=', $filter_date)
            ->where('type', 'investor_payments')
            ->orderBy('created_at', 'ASC')
            ->get();
            $data = [];

            foreach ($notifications as $notification) {
                $investor_id  = str_replace( array('[',']') , ''  , $notification->user_ids);
                $payments_from_notification = strstr((strstr($notification->content, '$')), ' ', true);
                $payments_from_notification = filter_var($payments_from_notification, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); 
                
                $date_of_notification = Carbon::parse($notification->getRawOriginal('created_at'));
                $end_date = $date_of_notification->format('Y-m-d');
                $start_date = $date_of_notification->copy()->subDay()->format('Y-m-d');
                $start_time_ny = $start_date.' 17:00:00';
                $start_time = Carbon::createFromFormat('Y-m-d H:i:s', $start_time_ny, 'America/New_york')->tz('UTC');
                $start_time_utc = $start_time->toDateTimeString();
                
                $end_time_ny = $end_date.' 17:00:00';
                $end_time = Carbon::createFromFormat('Y-m-d H:i:s', $end_time_ny, 'America/New_york')->tz('UTC');
                $end_time_utc = $end_time->toDateTimeString();

                //Sum query is very slow. that's why time out happening.
                // $payments = DB::select("SELECT SUM(`participant_share`) as sum  FROM `payment_investors` WHERE `user_id` = '$investor_id' AND `created_at` > '$start_time_utc' AND `created_at` <= '$end_time_utc'");
                // $payments = DB::table('payment_investors')
                // ->where('user_id', $investor_id)
                $payments = User::where('id', $investor_id)->first()->participantPayment()
                ->where('created_at', '>', $start_time_utc)
                ->where('created_at', '<=', $end_time_utc)
                ->sum('participant_share');

                $change = false;
                if ($payments != $payments_from_notification) {
                    $payment_formatted = \FFM::dollar($payments);
                    $old_notification =  $notification->content;
                    $new_notification = $payment_formatted . ' was collected from  05:00 PM';
                    $notification->uesr_id = $investor_id;
                    $notification->content = $new_notification;
                    //To update payment values and notification message.Currently commented.
                    // $notification->save();
                    $change = true;
                }
                if ($change) {
                    echo "\nValues are different for $investor_id on $end_date from $start_time_utc to $end_time_utc.\n";
                    echo "Notification Value is $payments_from_notification and actual is $payments\n";
                    $data[] = [
                        'investor_id' => $investor_id,
                        'notification_id' => $notification->id,
                        'checked_date' => $end_date,
                        'from' => $start_time_utc,
                        'to' => $end_time_utc,
                        'actual_payment' => $payments,
                        'notification_payment' => $payments_from_notification,
                        'old_notification' => $old_notification,
                        'new_notification' => $new_notification,
                    ];
                        
                } else {
                    echo "\nValues are matching for $investor_id on $end_date from $start_time_utc to $end_time_utc.\n";
                    $data['no_need_to_change'][] = [
                        'investor_id' => $investor_id,
                        'notification_id' => $notification->id,
                        'checked_date' => $end_date,
                        'from' => $start_time_utc,
                        'to' => $end_time_utc,
                        'actual_payment' => $payments,
                        'notification_payment' => $payments_from_notification,
                    ];
                }
            }
            file_put_contents(public_path('storage/correctMailboxrowsInvestorPayments'.time().'.json'), json_encode($data));

            return true;
        }
        return false;
    }
    public function setTransactionIdForInvestorAch()
    {
        
        $ach_user_id = config('settings.ach_user_id');

        if (Auth::loginUsingId($ach_user_id)) {
            $investor_achs = InvestorAchRequest::
            where('ach_request_status', InvestorAchRequest::AchRequestStatusAccepted)
            ->whereNull('transaction_id')
            ->orderBy('created_at', 'DESC')
            ->get();
            $data = $no_transaction = $transaction_ids= [];
            $count_single = $count_none = $count_multiple = 0;

            foreach ($investor_achs as $investor_ach) {
                $investor_id = $investor_ach->investor_id;
                $amount = $investor_ach->amount;
                $transaction_category = $investor_ach->transaction_category;
                $transaction_method = $investor_ach->transaction_method;
                $date = $investor_ach->date;
                if (in_array($investor_ach->transaction_type, ['credit', 'same_day_credit'])) {
                    $transaction_type = 1;
                    $amount = -1 * $amount;
                } else {
                    $transaction_type = 2;
                }

                $transaction = InvestorTransaction::
                where('investor_id', $investor_id)
                ->whereNotIn('id', $transaction_ids)
                ->where('amount', $amount)
                ->where('transaction_category', $transaction_category)
                ->where('transaction_type', $transaction_type)
                ->where('status', InvestorTransaction::StatusCompleted)
                ->where('date', '>=', $date)
                ->orderBy('created_at', 'ASC');
                switch ($transaction->count()) {
                    case 1:
                        $status = 'Single transaction found';
                        ++$count_single;
                        $transaction = $transaction->first();
                        break;
                    case 0:
                        $status = 'No transaction found';
                        ++$count_none;
                        $transaction = null;
                        break;
                    default:
                        $status = 'Multiple transaction found';
                        ++$count_multiple;
                        $transaction = $transaction->first();
                        break;
                }
                echo "\n $status for Investor-$investor_id with amount $amount";
                if ($transaction) {
                    $transaction_ids[] = $transaction->id;
                    $investor_ach->transaction_id = $transaction->id;
                    //To update data.
                    $investor_ach->save();
                    $data['success'][] = [
                        'investor_id' => $investor_id,
                        'ach_id' => $investor_ach->id,
                        'transaction_id' => $transaction->id,
                        'ach_date' => $date,
                        'transaction_date' => $transaction->date,
                        'amount' => $amount,
                        'status' => $status,
                    ];
                    
                } else {
                    $data['no_matches'][] = [
                        'investor_id' => $investor_id,
                        'ach_id' => $investor_ach->id,
                        'ach_date' => $date,
                        'amount' => $amount,
                        'status' => $status,
                    ];
                }
            }
            echo "\n Count single data = $count_single,  Count of multiple data = $count_multiple, Count of no data = $count_none";
            file_put_contents(public_path('storage/setTransactionIdForInvestorAch'.time().'.json'), json_encode($data));
            Auth::logout();
            
            return $data;
        }
    }
    public function addNameToActivityLog() {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        $user_details_logs = UserActivityLog::where('type', 'user_details')->whereNull('investor_id')->get();
        foreach ($user_details_logs as $logs) {
            $investor_id = null;
            $object = UserDetails::where('id', $logs->object_id)->first();
            if ($object) {
                $investor_id = $object->user_id;
            } else {
                $detail = json_decode($logs->detail, true);
                if (array_key_exists('user_id', $detail)) {
                    $investor_id = $detail['user_id'];
                }
            }
            if ($investor_id) {
                $logs->investor_id = $investor_id;
                $logs->save();    
            }
        }
        $velocity_fee = UserActivityLog::where('type', 'velocity_fee')->whereNull('merchant_id')->get();
        foreach ($velocity_fee as $log) {
            $merchant_id = null;
            $object = VelocityFee::where('id', $log->object_id)->first();
            if ($object) {
                $merchant_id = $object->merchant_id;
            } else {
                $detail = json_decode($log->detail, true);
                if (array_key_exists('merchant_id', $detail)) {
                    $merchant_id = $detail['merchant_id'];
                }
            }
            if ($merchant_id) {
                $log->merchant_id = $merchant_id;
                $log->save();    
            }
        }
        $company_amount = UserActivityLog::where('type', 'company_amount')->whereNull('merchant_id')->get();
        foreach ($company_amount as $log) {
            $merchant_id = null;
            $object = CompanyAmount::where('id', $log->object_id)->first();
            if ($object) {
                $merchant_id = $object->merchant_id;
            } else {
                $detail = json_decode($log->detail, true);
                if (array_key_exists('merchant_id', $detail)) {
                    $merchant_id = $detail['merchant_id'];
                }
            }
            if ($merchant_id) {
                $log->merchant_id = $merchant_id;
                $log->save();    
            }
        }
        $merchant = UserActivityLog::where('type', 'merchant')->whereNull('merchant_id')->get();
        foreach ($merchant as $log) {
            $merchant_id = null;
            $object = Merchant::withTrashed()->where('id', $log->object_id)->first();
            if ($object) {
                $merchant_id = $object->id;
            } else {
                $detail = json_decode($log->detail, true);
                if (array_key_exists('merchant_id', $detail)) {
                    $merchant_id = $detail['merchant_id'];
                }
            }
            if ($merchant_id) {
                $log->merchant_id = $merchant_id;
                $log->save();    
            }
        }
        $payment = UserActivityLog::where('type', 'payment')->whereNull('investor_id')->whereNull('merchant_id')->get();
        foreach ($payment as $log) {
            $merchant_id = null;
            $investor_id = null;
            $payment = ParticipentPayment::where('id', $log->object_id)->first();
            if ($payment) {
                if ($payment->merchant_id) {
                    $merchant_id = $payment->merchant_id;
                } elseif ($payment->merchant_id == 0) {
                    $investor_transaction = InvestorTransaction::where('id', $payment->model_id)->first();
                    if ($investor_transaction) {
                        $investor_id = $investor_transaction->investor_id;
                    }
                }
            } else {
                $detail = json_decode($log->detail, true);
                if (array_key_exists('merchant_id', $detail) && $detail['merchant_id']) {
                    $merchant_id = $detail['merchant_id'];
                }
            }
            $log->investor_id = $investor_id;
            $log->merchant_id = $merchant_id;
            $log->save();
        }
    }

  public function payment_left_repair()
    {
        $merchants = Merchant::select('id', 'rtr', 'payment_amount')->whereNull('actual_payment_left')->get()->toArray();
        if (! empty($merchants)) {
            foreach ($merchants as $key => $value) {
                $count = ParticipentPayment::where('merchant_id', $value['id'])->count();
                $payments_investors = PaymentInvestors::select(DB::raw('sum(payment_investors.participant_share) as participant_share'))->where('payment_investors.merchant_id', $value['id'])->groupBy('merchant_id');
                $test_count = $payments_investors->count();
                if ($test_count != 0) {
                    $payments_investors = $payments_investors->first();
                    $merchant_array = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'))->whereIn('merchant_user.status', [1, 3])->where('merchant_id', $value['id'])->groupBy('merchant_id')->first();
                    $total_rtr = $merchant_array->invest_rtr;
                    $bal_rtr = $total_rtr - $payments_investors->participant_share;
                    if ($total_rtr > 0) {
                        $actual_payment_left = $bal_rtr / (($total_rtr / $value['rtr']) * $value['payment_amount']);
                    } else {
                        $actual_payment_left = 0;
                    }
                    $actual_payment_left = round(($actual_payment_left > 0) ? $actual_payment_left : 0);
                    $merchnat_update = Merchant::where('id', $value['id'])->update(['actual_payment_left' => $actual_payment_left, 'paid_count' => $count]);
                }
            }
            echo 'actual payment left updated successfully';
        }
    }

   public function table_repair(Request $request, $value = '')
    {
        $merchant_status = MerchantUser::select('merchant_user.user_id')->whereIn('merchant_user.status', [1, 3])->groupBy('merchant_user.user_id')->update(['paid_participant_ishare' => DB::raw('(select sum(payment_investors.participant_share) as ctd from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'), 'actual_paid_participant_ishare' => DB::raw('(select sum(payment_investors.actual_participant_share) as ctd from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)'), 'paid_mgmnt_fee' => DB::raw('(select sum(payment_investors.mgmnt_fee) as mgmnt_fee from payment_investors where merchant_user.merchant_id = payment_investors.merchant_id and merchant_user.user_id = payment_investors.user_id)')]);
        $investors = $this->role->allInvestors();
        foreach ($investors as $key => $investor) {
            $liquidity_old = UserDetails::sum('liquidity');
            InvestorHelper::update_liquidity($investor->id, 'Table Repair');
            $liquidity_new = UserDetails::sum('liquidity');
            $liquidity_change = $liquidity_new - $liquidity_old;
            $final_liquidity = 0;
            $aggregated_liquidity = UserDetails::sum('liquidity');
            $creator_id = ($request->user()) ? $request->user()->id : null;
            $input_array = ['aggregated_liquidity' => $aggregated_liquidity, 'name_of_deal' => 'Table Repair', 'final_liquidity' => $final_liquidity, 'member_id' => '', 'liquidity_change' => $liquidity_change, 'member_type' => 'merchant', 'description' => 'Table Repair', 'creator_id' => $creator_id];
            if ($liquidity_change != 0) {
                $insert = LiquidityLog::insert($input_array);
            }
        }
        $log = 'updated liquidity';
    }

  public function payment_repair($value = '')
    {
        $uniq_data = ParticipentPayment::groupBy(DB::raw('DATE(payment_date)'))->groupBy('merchant_id')->orderBy('id')->get()->toArray();
        for ($i = 0; $i < 600000; $i++) {
            $partcipant_payment = ParticipentPayment::where('merchant_id', $uniq_data[$i]['merchant_id'])->where('payment_date', $uniq_data[$i]['payment_date'])->select('id')->get()->toArray();
            $updt_payment_investor = PaymentInvestors::whereIn('participent_payment_id_old', $partcipant_payment)->update(['participent_payment_id' => $uniq_data[$i]['id']]);
            $delete = ParticipentPayment::where('merchant_id', $uniq_data[$i]['merchant_id'])->where('payment_date', $uniq_data[$i]['payment_date'])->where('id', '!=', $uniq_data[$i]['id'])->delete();
        }
    }

       public function paymentDifference()
    {
        $merchant_user = MerchantUser::select('user_id', 'merchant_id', 'paid_participant_ishare', DB::raw('(SELECT SUM(participant_share) FROM payment_investors WHERE payment_investors.merchant_id = merchant_user.merchant_id AND payment_investors.user_id = merchant_user.user_id) invest_rtr'))->get();
        print_r($merchant_user->toArray());
        foreach ($merchant_user as $user) {
            $payment = DB::table('payment_investors')->where('user_id', $user->user_id)->where('merchant_id', $user->merchant_id)->sum('participant_share');
            if ((round($payment, 2) - $user->paid_participant_ishare) >= 1) {
                echo $user->paid_participant_ishare.'  ===  '.round($payment, 2);
                echo '<br>';
            }
        }
    }
  public function viewCompletePercentage()
    {
        $merchants = Merchant::get();
        $i = 1;
        foreach ($merchants as $mt) {
            $merchant_complete_percentage = $mt->complete_percentage;
            $merchant_id = $mt->id;
            $complete_per = PayCalc::completePercentage($merchant_id);
            if (($merchant_complete_percentage - $complete_per) >= .6 || ($merchant_complete_percentage - $complete_per) <= -.6) {
                $difference = $merchant_complete_percentage - $complete_per;
                echo $i.'. Merchant id ='.$merchant_id.'-'.$mt->name.'----'.$mt->created_at;
                echo '<br>';
                echo 'Current Percentage = '.$merchant_complete_percentage;
                echo '<br>';
                echo 'Actual Percentage = '.$complete_per;
                echo '<br>';
                echo 'Difference = '.$difference;
                echo '<br>';
                echo '-----------------';
                echo '<br>';
                $i++;
            }
        }
    }

    public function updatingUnderwritingFeePercentage()
    {
        DB::beginTransaction();
       $merchants=MerchantUser::where('under_writing_fee','>',0)->where('under_writing_fee_per','<=',0)->pluck('merchant_id','merchant_id')->toArray();
       $i=1;
       $serial='#';
       $mid='Merchant Id';
       $inv='Investors';
       $before='Before Underwriting Fee Percentage';
       $after='After Underwriting Fee Percentage';
        echo "<table border='1'>";
        echo "<tr>";
        echo"<td>".$serial."</td>";
        echo"<td>".$mid."</td>";
        echo"<td>".$inv."</td>";
        echo"<td>".$before."</td>";
        echo"<td>".$after."</td>";
        echo "</tr>";
       foreach($merchants as $key => $merchant){
         $investors=MerchantUser::select('id','user_id','under_writing_fee','under_writing_fee_per','amount')->where('merchant_id','=',$merchant)->where('under_writing_fee','>',0)->where('under_writing_fee_per','<=',0)->get();

        foreach($investors as $key => $investor){
            echo "<tr>";
            echo"<td>".$i++."</td>";
            echo "<td>".$merchant."</td>";
            echo "<td>".$investor->Investor->name."</td>";
            echo "<td>".$investor->under_writing_fee_per."</td>";

            $under_per=$investor->under_writing_fee/$investor->amount*100;
            $under_per=round($under_per);
            echo "<td>".$under_per."</td>";
            DB::table('merchant_user')
            ->where('merchant_user.id', $investor->id)
            ->update(['under_writing_fee_per'=>$under_per]);
        echo "</tr>";
        }
       }
       echo "</table>";

       DB::commit();
       exit;
    }
    public function AdjustMerchantsCompanyFundedAmount(Request $request)
    {
        $input_merchants = array('9709','9705','9722','9738','9737','9742','9741','9751','9752','9831','9834','9771','9773','9765','9764','9758','9759','9761','9757','9743','9729','9730','9760','9753','9754','9740','9736','9847','9835','9837','9838','9830','9824','9828','9827','9820','9823','9817','9822','9816','9815','9813','9814','9812','9811','9808','9807','9806','9803','9802','9797','9799','9796','9798','9791','9794','9793','9795','9788','9792','9787','9784','9785','9775','9770','9772','9848','9849','9850','9851','9854','9856','9858','9857','9855','9861','9862','9863','9864','9867','9869','9868','9873','9872','9876','9880','9881','9884','9887','9888','9889','9892','9893','9896','9897','9900','9904','9911');
        foreach($input_merchants as $merchant_id){
         try {
            DB::beginTransaction();
            $return_result = CompanyAmount::FinalizeCompanyShare($merchant_id);
            if($return_result['result'] == 'success'){
                echo "company fund updated for merchant ".$merchant_id;echo "<br>";
            }else{
                echo "Company updation failed for merchant ".$merchant_id;echo $return_result['result'];echo "<br>";
            }
            DB::commit();
            
        } catch (\Exception $e) {
            echo "Company updation failed for merchant ".$merchant_id;echo $e->getMessage();echo "<br>";           
            DB::rollback();
        }
        }

        
    }
    
    public function GenerateHistoricalData(Request $request) {
        if(Auth::guest()){
            return redirect(route('login'));
        }
        if (!$request->isMethod('post')) {
            return view('admin.historicalArea.historicalgeneration');
        } else {
            try {
                $user_id      = Auth::user()->id;
                $data         = $request->all();
                $date         = $data['date']??'';
                if(!$date) throw new \Exception("Please select any date", 1);
                $date         = date('Y-m-d H:i:s',strtotime($date));
                $payment_date = $data['payment_date']??false;
                $investor_ids    = Role::whereName('investor')->first()->users()->select('users.id')->pluck('id','id')->toArray();
                $overpayments    = Role::whereName('Over Payment')->first()->users()->select('users.id')->pluck('id','id')->toArray();
                $AgentFeeAccount = Role::whereName('Agent Fee Account')->first()->users()->select('users.id')->pluck('id','id')->toArray();
                $users=$investor_ids+$overpayments+$AgentFeeAccount;
                DB::beginTransaction();
                $message='';
                $InvestorTransaction = new InvestorTransaction;
                if(!$payment_date){
                    $InvestorTransaction = $InvestorTransaction->whereDate('created_at','>=',$date);
                } else {
                    $InvestorTransaction = $InvestorTransaction->whereDate('date','>=',$date);
                }
                $delete = $InvestorTransaction->delete();   
                if($delete) $message.= $delete." InvestorTransaction <br>";
                $MerchantUser = new MerchantUser;
                $MerchantUser = $MerchantUser->whereHas('Merchant', function ($query) use ($payment_date,$date) {
                    if(!$payment_date){
                        $query = $query->whereDate('merchant_user.created_at','>=',$date);
                    } else {
                        $query = $query->whereDate('merchants.date_funded','>=',$date);    
                    }
                });
                $CancelledMerchantUser = clone $MerchantUser;
                $CancelledMerchantUser = $CancelledMerchantUser->pluck('merchant_id','merchant_id')->toArray();
                Merchant::whereIn('id',$CancelledMerchantUser)->update(['sub_status_id'=>1]);//Active
                $delete = $MerchantUser->delete();   
                if($delete) $message.= $delete." MerchantUser <br>";
                
                $DefaultMerchants = new PaymentInvestors;
                $DefaultMerchants = $DefaultMerchants->whereHas('Merchant', function ($query) use ($date) {
                    $query = $query->whereIn('merchants.sub_status_id',[4,22,18,19,20]);    
                    $query = $query->whereDate('merchants.last_status_updated_date','>=',$date);
                });
                $DefaultMerchants = $DefaultMerchants->pluck('merchant_id','merchant_id')->toArray();
                foreach ($DefaultMerchants as $merchant_id) {
                    $return = MerchantHelper::changeSubStatusFn($merchant_id, 1);
                    $return = json_decode($return->content(), true);
                    $message.= $merchant_id." ".$return['msg']."  <br>";
                    $DefaultMerchantsLastPayment = PaymentInvestors::whereHas('ParticipentPayment', function ($query) {
                        $query = $query->where('participent_payments.payment',0);
                        $query = $query->where('participent_payments.rcode',0);
                    });
                    $DefaultMerchantsLastPayment = $DefaultMerchantsLastPayment->where('merchant_id',$merchant_id);
                    $participent_payments = clone $DefaultMerchantsLastPayment;
                    $participent_payments = $participent_payments->pluck('participent_payment_id','participent_payment_id')->toArray();
                    $DefaultMerchantsLastPayment = $DefaultMerchantsLastPayment->delete();
                    $ParticipentPayment = ParticipentPayment::whereIn('id',$participent_payments);
                    $ParticipentPayment = $ParticipentPayment->delete();
                }
                $PaymentInvestors = new PaymentInvestors;
                $PaymentInvestors = $PaymentInvestors->whereHas('ParticipentPayment', function ($query) use ($payment_date,$date) {
                    if(!$payment_date){
                        $query = $query->whereDate('participent_payments.created_at','>=',$date);
                    } else {
                        $query = $query->whereDate('payment_date','>=',$date);    
                    }
                });
                $Merchants = clone $PaymentInvestors;
                $Merchants = $Merchants->pluck('merchant_id','merchant_id')->toArray();
                foreach ($Merchants as $merchant_id) {
                    $Merchant = Merchant::find($merchant_id); 
                    SyncMerchantUserJob::dispatch($merchant_id);
                    if($Merchant){
                        if(in_array($Merchant->sub_status_id,[4,22,11])){
                            // Status will be updated based on last payment date 
                            $Merchant->sub_status_id = 1;//1 for active 5 for Collection
                            $Merchant->save();
                        }
                        if(in_array($Merchant->sub_status_id,[18,19,20])){
                            $Merchant->sub_status_id = 1;//Active
                            $Merchant->save();
                        }
                    }
                }
                $delete = $PaymentInvestors->delete();  
                if($delete) $message.= $delete." PaymentInvestors <br>";
                $ParticipentPayment = new ParticipentPayment;
                if(!$payment_date){
                    $ParticipentPayment = $ParticipentPayment->whereDate('created_at','>=',$date);
                } else {
                    $ParticipentPayment = $ParticipentPayment->whereDate('payment_date','>=',$date);
                }
                $delete = $ParticipentPayment->delete();
                if($delete) $message.= $delete." ParticipentPayment <br>";
                $description = 'Historical Data Updation';
                LiquidtyUpdate::dispatch($users, $description, $merchant_id = '', $liquidity_adjuster = '');
                DashboardServiceProvider::addInvestorPaymentJob($users);
                DB::commit();
                if($message){
                    $message.="<br> Please wait 5 minutes to update the related tables <br>";
                }
                $message.="Successfully Deleted";
                return redirect()->route('calicut78io/debug::generate-historical-data')->withInput()->withSuccess($message);
            } catch (\Exception $e) {
                DB::rollback();
                $message=$e->getMessage();
                return redirect()->route('calicut78io/debug::generate-historical-data')->withInput()->withError($message);
            }
        }
    }
    public function CheckAll() {
        Artisan::call('check:all');
    }
    /*
    Auther: Fasil 
    last payment date updation function. 
    */
    public function update_last_payment_date() {
        $merchants = DB::select("SELECT n.id, n.merchant_id, n.payment_date
            FROM participent_payments n 
            INNER JOIN (
                SELECT merchant_id, MAX(payment_date)  AS payment_date  FROM participent_payments where payment>0 GROUP BY merchant_id
            ) AS max USING (merchant_id, payment_date)  where   payment>0"
        );
        foreach ($merchants  as $key => $merchant) {
            $status=Merchant::where('id', $merchant->merchant_id)
            ->update(['last_payment_date' => $merchant->payment_date]);
            print_r($status);
            echo " <-> ";
            echo $merchant->merchant_id;
            echo "<br>";
        }
    }
    public function coming_soon(){
        return view('emails.coming_soon'); 
    }
}