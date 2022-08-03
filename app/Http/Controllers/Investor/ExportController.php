<?php

namespace App\Http\Controllers\Investor;

use App\Exports\Data_arrExport;
use App\Http\Controllers\Controller;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Transformer\MerchantTransformer;
use App\Library\Transformer\ParticipantPaymentTransformer;
use Carbon\Carbon;
use Excel;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    private $merchant;

    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
    }

    public function merchantList(Request $request)
    {
        $userId = '';
        $status = $request->status;
        $user_id = $request->userId;
        $role = $request->user()->hasRole('investor');
        if (empty($role)) {
            $userId = $user_id;
        } else {
            $userId = $this->user->id;
        }
        $merchantTransformer = new MerchantTransformer();
        $fileName = $this->user->name.Carbon::today()->toDateString();
        $excel = Excel::create($fileName)->setTitle($this->user->name)->setCreator($this->user->name)->setDescription($this->user->name)->sheet('report');
        $sheet = $excel->getSheet();
        $sheet->fromModel($merchantTransformer->transform($this->merchant->getAll(null, 1, $status, $userId)));
        $excel->download('xls');
    }

    public function merchantDetails(Request $request)
    {
        $user = $request->user();
        $username = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $this->user->name);
        $fileName = $username.'_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $userid = $user->id;
        $details = \MTB::getAllByMerchantInvestorId($request->id, $this->user->id, true);
        $details = $details->join('merchants', function ($join) {
            $join->on('merchants.id', '=', 'participent_payments.merchant_id');
        })->orderByDesc('payment_date');
        $details = $details->get()->toArray();
        $total_payment = array_sum(array_column($details, 'payment'));
        $total_participant_share = array_sum(array_column($details, 'participant_share'));
        $total_mgmnt_fee = array_sum(array_column($details, 'mgmnt_fee'));
        $total_to_participant = $total_participant_share - $total_mgmnt_fee;
        $i = 1;
        $total_payment = $total_participant_share = $total_mgmnt_fee = $total_to_participant = 0;
        $excel_array[0] = ['No', 'Payment Date', 'Total Payment', 'Participant Share', 'Management Fee', 'To Participant', 'Payment Method', 'Rcode'];
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $mode = null;
                switch ($data['mode_of_payment']) {
                    case 1:
                        $mode = 'ACH';
                        break;

                    case 0:
                        $mode = 'Manual';
                        break;

                    case 2:
                        $mode = 'Credit Card Payment';
                        break;
                
                        default:                    
                        break;
                }
               
                $total_payment = $total_payment+$data['payment'];
                $total_participant_share = $total_participant_share+$data['participant_share'];
                $total_mgmnt_fee = $total_mgmnt_fee+$data['mgmnt_fee'];
                $total_to_participant = $total_to_participant+($data['participant_share'] - $data['mgmnt_fee']);
                $excel_array[$i]['No'] = $i;


                $excel_array[$i]['Payment Date'] = FFM::date($data['payment_date']);
                $excel_array[$i]['Total Payment'] = FFM::dollar($data['payment']);
                $excel_array[$i]['Participant Share'] = FFM::dollar($data['participant_share']);
                $excel_array[$i]['Management Fee'] = FFM::dollar($data['mgmnt_fee']);
                $excel_array[$i]['To Participant'] = FFM::dollar($data['participant_share'] - $data['mgmnt_fee']);
                $excel_array[$i]['Payment Method'] = $mode;

                $excel_array[$i]['Rcode'] = $data['code'];
                $i++;
            }
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Payment Date'] = null;
            $excel_array[$i]['Total Payment'] = FFM::dollar($total_payment);
            $excel_array[$i]['Participant Share'] = FFM::dollar($total_participant_share);
            $excel_array[$i]['Management Fee'] = FFM::dollar($total_mgmnt_fee);
            $excel_array[$i]['To Participant'] = FFM::dollar($total_to_participant);
            $excel_array[$i]['Payment Method'] = null;
        }
        if (count($details) <= 0) {
            $excel_array[0] = ['No', 'Payment Date', 'Total Payment', 'Participant Share', 'Management Fee', 'To Participant', 'Transaction Type', 'Rcode'];
            $excel_array[1] = ['No Details Found'];
        } else {
            $export = new Data_arrExport($excel_array);

            return Excel::download($export, $fileName);
        }
    }

    public function generalReport(Request $request)
    {
        $merchants = $this->merchant->searchForGeneralReport($request->date_start, $request->date_end, $request->merchant_id)->orderByDesc('date_funded')->get();
        $details = $merchants->toArray();
        $username = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $this->user->name);
        $fileName = $username.'_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $i = 1;
        $excel_array[] = ['No', 'Merchant', 'Funded Date', 'Merchant Id', 'Debited', 'Total Payments', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last Rcode', 'Last Payment Date', 'Last Payment Amount', 'Participant RTR', 'Participant RTR Balance'];
        $total_debited = $total_payment = $total_management_fee = $total_net_amount = $total_principal = $total_profit = 0;
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $debited = array_sum(array_column($data['participant_payment'], 'payment'));
                $payment = array_sum(array_column($data['participant_payment'], 'participant_share'));
                $management_fee = array_sum(array_column($data['participant_payment'], 'mgmnt_fee'));
                $principal = array_sum(array_column($data['participant_payment'], 'principal'));
                $profit = array_sum(array_column($data['participant_payment'], 'profit'));
                $rtr = array_sum(array_column($data['investment_data'], 'invest_rtr'));
                $particiapnt_rtr_balance = $rtr - $payment;
                $net_amount = $payment - $management_fee;
                $total_debited = $total_debited + $debited;
                $total_payment = $total_payment + $payment;
                $total_management_fee = $total_management_fee + $management_fee;
                $total_net_amount = $total_net_amount + $net_amount;
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Merchant'] = $data['name'];
                $excel_array[$i]['Funded Date'] = FFM::date($data['date_funded']);
                $excel_array[$i]['Merchant Id'] = $data['id'];
                $excel_array[$i]['Debited'] = round($debited, 2);
                $excel_array[$i]['Total Payments'] = round($payment, 2);
                $excel_array[$i]['Management Fee'] = round($management_fee, 2);
                $excel_array[$i]['Net Amount'] = round($net_amount, 2);
                $excel_array[$i]['Principal'] = round($principal, 2);
                $excel_array[$i]['Profit'] = round($profit, 2);
                $excel_array[$i]['Last Rcode'] = $data['last_rcode'];
                $excel_array[$i]['Last Payment Date'] = FFM::date($data['last_payment_date']);
                $excel_array[$i]['Last Payment Amount'] = round($data['last_payment_amount'], 2);
                $excel_array[$i]['Participant RTR'] = round($rtr, 2);
                $excel_array[$i]['Participant RTR Balance'] = round($particiapnt_rtr_balance, 2);
                $i++;
            }
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Merchant'] = null;
            $excel_array[$i]['Funded Date'] = null;
            $excel_array[$i]['Merchant Id'] = null;
            $excel_array[$i]['Debited'] = '=SUM(E2:E'.$i.')';
            $excel_array[$i]['Total Payments'] = '=SUM(F2:F'.$i.')';
            $excel_array[$i]['Management Fee'] = '=SUM(G2:G'.$i.')';
            $excel_array[$i]['Net Amount'] = '=SUM(H2:H'.$i.')';
            $excel_array[$i]['Principal'] = '=SUM(I2:I'.$i.')';
            $excel_array[$i]['Profit'] = '=SUM(J2:J'.$i.')';
            $excel_array[$i]['Last Rcode'] = null;
            $excel_array[$i]['Last Payment Date'] = null;
            $excel_array[$i]['Last Payment Amount'] = null;
            $excel_array[$i]['Participant RTR'] = '=SUM(N2:N'.$i.')';
            $excel_array[$i]['Participant RTR Balance'] = '=SUM(O2:O'.$i.')';
        }
        if (count($details) <= 0) {
            $excel_array[0] = ['No', 'Merchant', 'Funded Date', 'Merchant Id', 'Debited', 'Total Payments', 'Management Fee', 'Net Amount'];
            $excel_array[1] = ['No Details Found'];
        } else {
            $export = new Data_arrExport($excel_array);

            return Excel::download($export, $fileName);
        }
    }
}
