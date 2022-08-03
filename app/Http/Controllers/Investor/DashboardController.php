<?php

namespace App\Http\Controllers\Investor;

use App\Document;
use App\Exports\Data_arrExport;
use App\Http\Controllers\Controller;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Merchant;
use App\MerchantUser;
use App\ParticipentPayment;
use App\Settings;
use Carbon\Carbon;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use MTB;
use Yajra\DataTables\Html\Builder;

class DashboardController extends Controller
{
    private $merchant;
    private $user;

    public function __construct(IMerchantRepository $merchant, IParticipantPaymentRepository $partPay, IUserRepository $user)
    {
        $this->merchant = $merchant;
        $this->partPay = $partPay;
        $this->user1 = $user;
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
    }

    public function page($value = '')
    {
        return view('investor.index');
    }

    public function index(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::investorMerchantListView($this->user->id, '', '', $request->status);
        }
        $page_title = '';
        if ($request->user()->hasRole('merchant')) {
            $page_title = 'Merchant Dashboard | '.$this->user->name;
        } else {
            $page_title = 'Investor Dashboard | '.$this->user->name;
        }
        $tableBuilder->ajax(['url' => route('investor::dashboard::index'), 'data' => 'function(d){ d.status = $("#status").val();}']);
        $tableBuilder->parameters(['footerCallback' => "function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html('Total');$(n.column(3).footer()).html(o.funded_total),$(n.column(5).footer()).html(o.rtr_total),$(n.column(7).footer()).html(o.ctd_total)}", 'order' => [[2, 'desc']]]);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }"]);
        $tableBuilder = $tableBuilder->columns([['data' => 'id', 'title' => 'Sl No', 'orderable' => false], ['data' => 'name', 'name' => 'merchants.name', 'title' => 'Merchant', 'orderable' => false], ['data' => 'date_funded', 'name' => 'merchants.date_funded', 'title' => 'Date Funded', 'orderable' => true], ['data' => 'amount', 'name' => 'amount', 'title' => 'Funded', 'orderable' => false],
         ['data' => 'commission', 'name' => 'commission', 'title' => 'CMMSN', 'orderable' => false, 'searchable' => false],
         ['data' => 'invest_rtr', 'name' => 'invest_rtr', 'title' => 'RTR', 'orderable' => false, 'searchable' => false], ['data' => 'factor_rate', 'name' => 'factor_rate', 'title' => 'Rate', 'orderable' => false, 'searchable' => false], ['data' => 'paid_participant_ishare', 'name' => 'paid_participant_ishare', 'title' => 'CTD', 'orderable' => false, 'searchable' => false], ['data' => 'annualized_rate', 'name' => 'annualized_rate', 'title' => 'Annualized rate', 'orderable' => false, 'searchable' => false], ['data' => 'complete_per', 'name' => 'complete_per', 'title' => 'Complete', 'orderable' => false, 'searchable' => false], ['data' => 'sub_status_id', 'name' => 'sub_status_id', 'title' => 'Status', 'orderable' => false, 'searchable' => false], ['data' => 'last_payment_date', 'name' => 'last_payment_date', 'title' => 'Last Payment Date', 'orderable' => false, 'searchable' => false], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'searchable' => false, 'orderable' => false]]);
        $month = date('m', strtotime('0 month'));
        $year = date('Y', strtotime('0 month'));
        $month1 = date('m', strtotime('-1 month'));
        $year1 = date('Y', strtotime('-1 month'));
        $month2 = date('m', strtotime('-2 month'));
        $year2 = date('Y', strtotime('-2 month'));
        $month3 = date('m', strtotime('-3 month'));
        $year3 = date('Y', strtotime('-3 month'));
        $month4 = date('m', strtotime('-4 month'));
        $year4 = date('Y', strtotime('-4 month'));
        $userId = $this->user->id;
        $date1 = date('Y-m', strtotime('-4 month')).'-01';
        $date2 = date('Y-m-t', strtotime('0 month'));
        $fund_data = MerchantUser::leftJoin('merchants', 'merchant_user.merchant_id', 'merchants.id')->whereIn('merchant_user.status', [1, 3])->where('merchant_user.user_id', $userId)->groupBy(DB::raw('MONTH(merchants.date_funded)'))->where('merchants.date_funded', '>=', $date1)->where('merchants.date_funded', '<=', $date2)->select(DB::raw('SUM(merchant_user.amount) as funded'), DB::raw('MONTH(merchants.date_funded) as month'), DB::raw('YEAR(merchants.date_funded) as year'), DB::raw('SUM(merchant_user.invest_rtr) as rtr_month'))->get();
        $ctd_month_data = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('payment_investors.user_id', $userId)->groupBy(DB::raw('MONTH(participent_payments.payment_date)'))->where('participent_payments.payment_date', '>=', $date1)->where('participent_payments.payment_date', '<=', $date2)->select(DB::raw('SUM(payment_investors.actual_participant_share-mgmnt_fee) as ctd_month'), DB::raw('YEAR(participent_payments.payment_date) as year'), DB::raw('MONTH(participent_payments.payment_date) as month'))->get();
        $array1 = $fund_data->toArray();
        $array2 = $ctd_month_data->toArray();
        $final = [];
        foreach ($array1 as $key1 => $data1) {
            foreach ($array2 as $key2 => $data2) {
                if ($data1['month'] == $data2['month']) {
                    $final[] = $data1 + $data2;
                    unset($array1[$key1]);
                    unset($array2[$key2]);
                }
            }
        }
        if (! empty($array1)) {
            foreach ($array1 as $value) {
                $final[] = $value;
            }
        }
        if (! empty($array2)) {
            foreach ($array2 as $value) {
                $final[] = $value;
            }
        }
        $chart_data = $final;
        for ($i = 0; $i >= -4; $i--) {
            $month = date('m', strtotime($i.' month'));
            $year = date('Y', strtotime($i.' month'));
            if (array_search($month, array_column($chart_data, 'month')) === false) {
                $newdata = ['funded' => 0, 'rtr_month' => 0, 'ctd_month' => 0, 'month' => $month, 'year' => $year];
                array_push($chart_data, $newdata);
            }
        }
        foreach ($chart_data as $key => $part) {
            $part['payment_date'] = $part['year'].'-'.$part['month'];
            $sort[$key] = strtotime($part['payment_date']);
        }
        array_multisort($sort, SORT_ASC, $chart_data);
        $investor_type = $this->user->investor_type;
        $array = $this->user1->investorDashboard($userId, $investor_type);
        $portfolio_difference = FFM::portfolio_difference($userId);
        $liquidity = $array['liquidity'];
        $invested_amount = $array['invested_amount'];
        $ctd = $array['ctd'];
        $blended_rate = $array['blended_rate'];
        $default_percentage = $array['default_percentage'];
        $merchant_count = $array['merchant_count'];
        $total_rtr = $array['total_rtr'];
        $average = $array['average'];
        $investor_type = $array['investor_type'];
        $velocity_dist = $array['velocity_dist'];
        $investor_dist = $array['investor_dist'];
        $total_requests = $array['total_requests'];
        $portfolio_value = $array['portfolio_value'] + $portfolio_difference;
        $principal_investment = $array['principal_investment'];
        $debit_interest = $array['debit_interest'];
        $irr = $array['irr'];
        $total_credit = $array['total_credit'];
        $current_portfolio = $array['current_portfolio'];
        $substatus = $array['substatus'];
        $overpayment = $array['overpayment'];
        $c_invested_amount = $array['c_invested_amount'];

        return view('investor.dashboard.index', compact('default_percentage', 'average', 'total_credit', 'page_title', 'substatus', 'tableBuilder', 'chart_data', 'merchant_count', 'liquidity', 'invested_amount', 'blended_rate', 'total_requests', 'ctd', 'total_rtr', 'velocity_dist', 'investor_dist', 'investor_type', 'portfolio_value', 'current_portfolio', 'principal_investment', 'debit_interest', 'irr', 'overpayment', 'c_invested_amount'));
    }

    public function indexold(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::investorMerchantListView($this->user->id, '', '', $request->status);
        }
        $page_title = 'Investor Dashboard | '.$this->user->name;
        $tableBuilder->ajax(['url' => route('investor::dashboard::index'), 'data' => 'function(d){ d.status = $("#status").val();}']);
        $tableBuilder->parameters(['footerCallback' => "function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html('Total');$(n.column(3).footer()).html(o.funded_total),$(n.column(5).footer()).html(o.rtr_total),$(n.column(7).footer()).html(o.ctd_total)}", 'order' => [[2, 'desc']]]);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }"]);
        $tableBuilder = $tableBuilder->columns([['data' => 'merchant.id', 'title' => 'Sl No', 'orderable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant', 'orderable' => false], ['data' => 'date_funded', 'name' => 'merchant.date_funded', 'title' => 'Date Funded', 'orderable' => false], ['data' => 'amount', 'name' => 'amount', 'title' => 'Funded', 'orderable' => false], ['data' => 'merchant.commission', 'name' => 'merchant.commission', 'title' => 'CMMSN', 'orderable' => false, 'searchable' => false], ['data' => 'invest_rtr', 'name' => 'invest_rtr', 'title' => 'RTR', 'orderable' => false, 'searchable' => false], ['data' => 'merchant.factor_rate', 'name' => 'merchant.factor_rate', 'title' => 'Rate', 'orderable' => false, 'searchable' => false], ['data' => 'paid_participant_ishare', 'name' => 'paid_participant_ishare', 'title' => 'CTD', 'orderable' => false, 'searchable' => false], ['data' => 'complete_per', 'name' => 'complete_per', 'title' => 'Complete', 'orderable' => false, 'searchable' => false], ['data' => 'merchant.sub_status_id', 'name' => 'merchant.sub_status_id', 'title' => 'Status', 'orderable' => false, 'searchable' => false], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'searchable' => false, 'orderable' => false]]);
        $month = date('m', strtotime('0 month'));
        $year = date('Y', strtotime('0 month'));
        $month1 = date('m', strtotime('-1 month'));
        $year1 = date('Y', strtotime('-1 month'));
        $month2 = date('m', strtotime('-2 month'));
        $year2 = date('Y', strtotime('-2 month'));
        $month3 = date('m', strtotime('-3 month'));
        $year3 = date('Y', strtotime('-3 month'));
        $month4 = date('m', strtotime('-4 month'));
        $year4 = date('Y', strtotime('-4 month'));
        $userId = $this->user->id;
        $date1 = date('Y-m', strtotime('-4 month')).'-01';
        $date2 = date('Y-m-t', strtotime('0 month'));
        $fund_data = MerchantUser::leftJoin('merchants', 'merchant_user.merchant_id', 'merchants.id')->where('merchant_user.user_id', $userId)->groupBy(DB::raw('MONTH(merchants.date_funded)'))->where('merchants.date_funded', '>=', $date1)->where('merchants.date_funded', '<=', $date2)->select(DB::raw('SUM(merchant_user.amount) as funded'), DB::raw('MONTH(merchants.date_funded) as month'), DB::raw('YEAR(merchants.date_funded) as year'), DB::raw('SUM(merchant_user.invest_rtr) as rtr_month'))->get();
        $ctd_month_data = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('payment_investors.user_id', $userId)->groupBy(DB::raw('MONTH(participent_payments.payment_date)'))->where('participent_payments.payment_date', '>=', $date1)->where('participent_payments.payment_date', '<=', $date2)->select(DB::raw('SUM(payment_investors.participant_share-mgmnt_fee) as ctd_month'), DB::raw('YEAR(participent_payments.payment_date) as year'), DB::raw('MONTH(participent_payments.payment_date) as month'))->get();
        $array1 = $fund_data->toArray();
        $array2 = $ctd_month_data->toArray();
        $final = [];
        foreach ($array1 as $key1 => $data1) {
            foreach ($array2 as $key2 => $data2) {
                if ($data1['month'] == $data2['month']) {
                    $final[] = $data1 + $data2;
                    unset($array1[$key1]);
                    unset($array2[$key2]);
                }
            }
        }
        if (! empty($array1)) {
            foreach ($array1 as $value) {
                $final[] = $value;
            }
        }
        if (! empty($array2)) {
            foreach ($array2 as $value) {
                $final[] = $value;
            }
        }
        $chart_data = $final;
        for ($i = 0; $i >= -4; $i--) {
            $month = date('m', strtotime($i.' month'));
            $year = date('Y', strtotime($i.' month'));
            if (array_search($month, array_column($chart_data, 'month')) === false) {
                $newdata = ['funded' => 0, 'rtr_month' => 0, 'ctd_month' => 0, 'month' => $month, 'year' => $year];
                array_push($chart_data, $newdata);
            }
        }
        foreach ($chart_data as $key => $part) {
            $part['payment_date'] = $part['year'].'-'.$part['month'];
            $sort[$key] = strtotime($part['payment_date']);
        }
        array_multisort($sort, SORT_ASC, $chart_data);
        $investor_type = $this->user->investor_type;
        $array = $this->user1->investorDashboard($userId, $investor_type);
        $liquidity = $array['liquidity'];
        $invested_amount = $array['invested_amount'];
        $ctd = $array['ctd'];
        $blended_rate = $array['blended_rate'];
        $default_percentage = $array['default_percentage'];
        $merchant_count = $array['merchant_count'];
        $total_rtr = $array['total_rtr'];
        $average = $array['average'];
        $investor_type = $array['investor_type'];
        $velocity_dist = $array['velocity_dist'];
        $investor_dist = $array['investor_dist'];
        $total_requests = $array['total_requests'];
        $portfolio_value = $array['portfolio_value'];
        $principal_investment = $array['principal_investment'];
        $debit_interest = $array['debit_interest'];
        $irr = $array['irr'];
        $total_credit = $array['total_credit'];
        $current_portfolio = $array['current_portfolio'];
        $substatus = $array['substatus'];
        $overpayment = $array['overpayment'];

        return view('investor.dashboard.index', compact('default_percentage', 'average', 'total_credit', 'page_title', 'substatus', 'tableBuilder', 'chart_data', 'merchant_count', 'liquidity', 'invested_amount', 'blended_rate', 'total_requests', 'ctd', 'total_rtr', 'velocity_dist', 'investor_dist', 'investor_type', 'portfolio_value', 'current_portfolio', 'principal_investment', 'debit_interest', 'irr', 'overpayment'));
    }

    public function portfolioDownload(Request $request)
    {
        $user = $request->user();
        $username = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $this->user->name);
        $fileName = $username.'_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $userid = $user->id;
        $details = \MTB::investorMerchantListViewExportData($userid, '', '', $request->status);
        $funded_total = $ctd_total = $rtr_total = $commission_total = 0;
        $details = $details->get()->toArray();
        $funded_total = array_sum(array_column($details, 'amount'));
        $rtr_total = array_sum(array_column($details, 'invest_rtr')) - array_sum(array_column($details, 't_mag_fee'));
        $ctd_total = array_sum(array_column($details, 'paid_participant_ishare')) - array_sum(array_column($details, 'paid_mgmnt_fee'));
        $i = 1;
        $total_funded = $total_rtr = $total_ctd = 0;
        $excel_array[0] = ['No', 'Merchant', 'Date Funded', 'Funded', 'CMMSN', 'RTR', 'Rate', 'CTD', 'Annualized Rate', 'Complete', 'Status'];
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $no_of_payments = ($data['merchant']['advance_type'] == 'weekly_ach') ? 52 : 255;
                $tot_profit = $data['invest_rtr'] - $data['mag_fee'] - ($data['amount'] + $data['pre_paid'] + $data['commission_amount'] + $data['under_writing_fee']);
                $tot_investment = $data['amount'] + $data['pre_paid'] + $data['commission_amount'] + $data['under_writing_fee'];
                $annualised_rate = 0;
                if ($tot_investment > 0 && $data['merchant']['pmnts'] > 0) {
                    $annualised_rate = ($tot_profit * $no_of_payments / $data['merchant']['pmnts']) / $tot_investment * 100;
                }
                $total_funded = $total_funded+$data['amount'];
                $total_rtr = $total_rtr+($data['invest_rtr'] - $data['mag_fee']);
                $total_ctd = $total_ctd+($data['actual_paid_participant_ishare'] - $data['paid_mgmnt_fee']);
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Merchant'] = $data['merchant']['name'];
                $excel_array[$i]['Date Funded'] = isset($data['merchant']['date_funded']) ? FFM::date($data['merchant']['date_funded']) : 0;
                $excel_array[$i]['Funded'] = FFM::dollar($data['amount']);
                $excel_array[$i]['CMMSN'] = FFM::percent($data['commission_per']+$data['up_sell_commission_per']);
                $excel_array[$i]['RTR'] = FFM::dollar($data['invest_rtr'] - $data['mag_fee']);
                $excel_array[$i]['Rate'] = round($data['merchant']['factor_rate'], 2);
                $excel_array[$i]['CTD'] = FFM::dollar($data['actual_paid_participant_ishare'] - $data['paid_mgmnt_fee']);
                $excel_array[$i]['Annualized Rate'] = FFM::percent($annualised_rate);
                $excel_array[$i]['Complete'] = FFM::percent($data['merchant']['complete_percentage']);
                $excel_array[$i]['Status'] = isset($data['merchant']['sub_statuses_name']) ? $data['merchant']['sub_statuses_name'] : 999;
                $i++;
            }
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Merchant'] = null;
            $excel_array[$i]['Date Funded'] = null;
            $excel_array[$i]['Funded'] = FFM::dollar($total_funded);
            $excel_array[$i]['CMMSN'] = null;
            $excel_array[$i]['RTR'] = FFM::dollar($total_rtr);
            $excel_array[$i]['Rate'] = null;
            $excel_array[$i]['CTD'] = FFM::dollar($total_ctd);
            $excel_array[$i]['Annualized Rate'] = null;
            $excel_array[$i]['Complete'] = null;
            $excel_array[$i]['Status'] = null;
        }
        if (count($details) <= 0) {
            $excel_array[0] = ['No', 'Merchant', 'Date Funded', 'Funded', 'CMMSN', 'RTR', 'Rate', 'CTD', 'Annualized Rate', 'Complete', 'Status'];
            $excel_array[1] = ['No Details Found'];
        } else {
            $export = new Data_arrExport($excel_array);

            return Excel::download($export, $fileName);
        }
    }

    public function openItems(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::investorMerchantoOpenList($this->user);
        }
        $page_title = 'Open items';
        $tableBuilder->ajax(route('investor::openitems'));
        $tableBuilder = $tableBuilder->columns([['data' => 'name', 'name' => 'name', 'title' => 'Merchant'], ['data' => 'id', 'name' => 'id', 'title' => 'MID', 'searchable' => false], ['data' => 'date_funded', 'name' => 'date_funded', 'title' => 'Date Funded', 'searchable' => false], ['data' => 'pending_amount', 'name' => 'funded', 'title' => 'Pending amount', 'searchable' => false], ['data' => 'fund_collect_status', 'name' => 'funded', 'title' => 'FUNDS COLLECTED', 'searchable' => false], ['data' => 'signed_addenum', 'name' => 'funded', 'title' => 'SIGNED ADDENDUM', 'searchable' => false]]);

        return view('investor.dashboard.open-items', compact('page_title', 'tableBuilder'));
    }

    public function view(Request $request, Builder $tableBuilder, $m_id)
    {
        if ($merchant = $this->merchant->findIfBelongsToUser($m_id, $this->user->id)) {
            if ($request->ajax() || $request->wantsJson()) {
                return MTB::investorMerchantDetailsView($m_id, $this->user);
            }
            $page_title = 'Investor Dashboard';
            $tableBuilder->ajax(route('investor::dashboard::view', ['id' => $m_id]));
            $tableBuilder->parameters(['footerCallback' => "function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(1).footer()).html(o.payment_total),$(n.column(0).footer()).html('Total'),$(n.column(2).footer()).html(o.participant_share_total),$(n.column(3).footer()).html(o.mgmnt_fee_total),$(n.column(4).footer()).html(o.final_participant_share_total)}", 'lengthMenu' => [100, 50], 'order' => [[0, 'desc']]]);
            $investment11 = Merchantuser::select(DB::raw('sum(
                IF(paid_participant_ishare > invest_rtr, (paid_participant_ishare-invest_rtr)*(1- (merchant_user.mgmnt_fee)/100 ), 0)
                ) as overpayment'), 'commission_per','up_sell_commission_per', 'amount', 'invest_rtr', 'mgmnt_fee', 'pre_paid')->where('merchant_id', $m_id)->where('status', 1)->first();
            $overpayment = $investment11->overpayment;
            $investment = Merchantuser::select('commission_per','up_sell_commission_per', 'amount', 'invest_rtr', 'mgmnt_fee', 'pre_paid', 'paid_participant_ishare', DB::raw('merchant_user.invest_rtr *(merchant_user.mgmnt_fee)/100 as total_mangt_fee'))->where('merchant_id', $m_id)->where('user_id', $this->user->id)->where('status', 1)->first();
            $investor_overpayment = 0;
            if ($investment->paid_participant_ishare > $investment->invest_rtr) {
                $investor_overpayment = ($investment->paid_participant_ishare - $investment->invest_rtr) * (1 - ($investment->mgmnt_fee) / 100);
            }
            $user_id = $this->user->id;
            $payments = ParticipentPayment::where('participent_payments.merchant_id', $m_id)->join('payment_investors', function ($join) use ($user_id) {
                $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
                $join->where('payment_investors.user_id', '=', $user_id);
            })->where('status', 1)->get();
            $total_mgmnt_paid = $total_syndication_paid = $paid_to_participant = $ctd_sum = $paid_syndication_fee = 0;
            foreach ($payments as $key => $value) {
                $total_mgmnt_paid = $total_mgmnt_paid + $value->mgmnt_fee;
                $paid_to_participant = $paid_to_participant + ($value->participant_share - $value->mgmnt_fee);
                $ctd_sum = $ctd_sum + $value->payment;
            }
            if ($merchant->sub_status_id == 11) {
                $balance = 0;
            } else {
                $balance = ($merchant->rtr - $ctd_sum);
            }
            $tableBuilder->columns([['data' => 'payment_date', 'name' => 'payment_date', 'title' => 'Date Settled'], ['data' => 'payment', 'name' => 'payment', 'title' => 'Total Payment', 'searchable' => false], ['data' => 'participant_share_data', 'name' => 'participant_share', 'title' => 'Participant Share ', 'searchable' => false], ['data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'title' => 'Management fee', 'searchable' => false], ['data' => 'to_participant', 'name' => 'to_participant', 'title' => 'To Participant', 'searchable' => false], ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Payment Method', 'searchable' => false], ['data' => 'code', 'name' => 'code', 'title' => 'Rcode', 'searchable' => false]]);
            $start_date = Carbon::today()->subMonths(4);
            $end_date = Carbon::today();
            $payments = ParticipentPayment::where('participent_payments.merchant_id', $m_id)->join('payment_investors', function ($join) use ($user_id) {
                $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
                $join->where('payment_investors.user_id', '=', $user_id);
            })->where('payment_date', '>', $start_date)->get();
            $all_past_payment_sum = ParticipentPayment::where('participent_payments.merchant_id', $m_id)->join('payment_investors', function ($join) use ($user_id) {
                $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
                $join->where('payment_investors.user_id', '=', $user_id);
            })->where('payment_date', '<=', $start_date)->sum('payment_investors.participant_share');
            $graph_payments = [];
            foreach ($payments as $key => $payment) {
                $graph_payments[$payment->payment_date] = $payment->participant_share;
            }
            $chart_data = [];
            $graph_lable = $graph_data = [];
            $total_value = $all_past_payment_sum;
            $started = 0;
            for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
                if (isset($graph_payments[$date->format('Y-m-d')])) {
                    if ($started == 0) {
                        $graph_lable[] = '';
                        $graph_data[] = $all_past_payment_sum;
                        $started = 1;
                    }
                    $total_value = $total_value + $graph_payments[$date->format('Y-m-d')];
                    $graph_lable[] = $date->format('Y-m-d');
                    $graph_data[] = $total_value;
                } elseif ($started == 1) {
                    $graph_lable[] = $date->format('Y-m-d');
                    $graph_data[] = $total_value;
                }
            }

            return view('investor.dashboard.details', compact('graph_lable', 'graph_data', 'page_title', 'tableBuilder', 'merchant', 'total_mgmnt_paid', 'paid_to_participant', 'ctd_sum', 'chart_data', 'balance', 'investment', 'overpayment', 'paid_syndication_fee', 'm_id', 'investor_overpayment'));
        }
    }

    public function documents($merchantId, Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::documents($this->user->id, $merchantId);
        }
        $page_title = 'Investor Dashboard';
        $tableBuilder = $tableBuilder->columns(MTB::documents($this->user->id, $merchantId, true));
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }"]);

        return view('investor.dashboard.documents', compact('page_title', 'tableBuilder', 'merchantId'));
    }

    public function documentUpload($merchatId, Request $request)
    {
        $user_id = $this->user->id;
        $fileName = "marketplace/$merchatId/$user_id".$this->generateFileName($request->file->getClientOriginalExtension());
        $storge = Storage::disk('s3')->put($fileName, file_get_contents($request->file), config('filesystems.disks.s3.privacy'));
        $extension = $request->file->getClientOriginalExtension();
        $url = asset(\Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2)));
        $data = ['document_type_id' => 1, 'merchant_id' => $merchatId, 'investor_id' => $this->user->id, 'title' => $request->file->getClientOriginalName(), 'file_name' => $fileName];
        $merchant = Merchant::select('name', 'funded')->where('id', $merchatId)->first();
        $message['content'] = $this->user->name.' Uploaded New Document ( '.$merchatId.' )';
        $message['title'] = 'Document uploaded';
        $message['merchant_name'] = $merchant->name;
        $message['funded'] = $merchant->funded;
        $message['url'] = $url;
        $message['filename'] = $fileName;
        $message['extension'] = $extension;
        \EventHistory::pushNotifyAdmin($message, $this->user->id);
        Document::create($data);

        return response()->json(['message' => 'success']);
    }

    public function documentUpdate($merchantId, $documentId, Request $request)
    {
        if ($document = Document::find($documentId)) {
            $document->update(['title' => $request->title, 'document_type_id' => $request->type]);
        }
    }

    public function documentDelete($merchantId, $documentId)
    {
        if ($document = Document::find($documentId)) {
            unlink(storage_path('documents/'.$document->file_name));
            $document->delete();
        }
    }

    public function viewDoc($merchantId, $documentId)
    {
        if ($document = Document::find($documentId)) {
            $fileName = storage_path('documents/'.$document->file_name);
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            if (in_array($ext, FFM::viewableDocExtensions())) {
                return response()->file($fileName);
            } else {
                return response()->download($fileName);
            }
        }
    }

    private function generateFileName($extension)
    {
        return 'doc_'.time().'.'.$extension;
    }
}
