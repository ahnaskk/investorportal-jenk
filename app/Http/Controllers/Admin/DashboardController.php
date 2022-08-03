<?php

namespace App\Http\Controllers\Admin;

use function App\Helpers\modelQuerySql;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessResource;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Merchant;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MTB;
use PayCalc;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Html\Builder;
use CompanyHelper;
use InvestorTransactionHelper;
use DashboardHelper;

class DashboardController extends Controller
{
    public function __construct(IMerchantRepository $merchant, IRoleRepository $role)
    {
        $this->merchant = $merchant;
        $this->user = Auth::user();
        $this->role = $role;
    }
    public function postDashboardTransaction(Request $request)
    {        
        list($companyIds, $setInvestors, $merchantIds) = CompanyHelper::getCompanyIds($request);              
        $data = InvestorTransactionHelper::getInvestorTransactions($request,$setInvestors,$companyIds,$merchantIds);
        return new SuccessResource($data);
    }

    public function postDashboard(Request $request)
    {        
        list($companyIds, $setInvestors, $merchantIds) = CompanyHelper::getCompanyIds($request);
        $data = DashboardHelper::getDashboardDetails($request,$companyIds,$setInvestors,$merchantIds);
        return new SuccessResource($data);
    }

    public function postCompanyDashboard(Request $request)
    {
        list($companyIds, $setInvestors, $merchantIds) = CompanyHelper::getCompanyIds($request);
        $subAdminLiquidity = CompanyHelper::getCompanyDetails($request,$companyIds,$setInvestors,$merchantIds); 
        return new SuccessResource(['success' => true, 'data' => $subAdminLiquidity, 'draw' => $request->input('draw')]);
    }

    public function index(Request $request, Builder $builder)
    {
        $title = 'Admin User';
        if ($request->user()->hasRole(['investor'])) {
            return redirect()->to('/investor/dashboard');
        }
        if ($request->user()->hasRole(['merchant'])) {
            Auth::logout();
            return redirect()->to('/login')->withErrors(['Login Disabled']);
        }
        $data = DashboardHelper::getDashboardIndex($request);        
        $filter_arr = ['disabled' => 'Disabled Investors', 'enabled' => 'Enabled Investors', 'overpayment' => 'Overpayment Account'];

        return view('admin.dashboard.index', compact('title', 'data','filter_arr'));
    }

    public function view(Request $request, Builder $tableBuilder, $id)
    {
        if (1) {
            $merchant = $this->merchant->find($id);
            if ($request->ajax() || $request->wantsJson()) {
                return MTB::adminMerchantDetailsView($id, $this->user);
            }
            $page_title = 'Investor Dashboard';
            $tableBuilder->ajax(route('admin::dashboard::view', ['id' => $id]));
            $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(2).footer()).html(o.payment_total),$(n.column(3).footer()).html(o.participant_share_total),$(n.column(5).footer()).html(o.syndication_fee_total),$(n.column(4).footer()).html(o.mgmnt_fee_total),$(n.column(6).footer()).html(o.final_participant_share_total)}', 'lengthMenu' => [100, 50]]);
            $payments = DB::table('participent_payments')->where('merchant_id', $id)->get();
            $investments = DB::table('merchant_user')->where('merchant_id', $id)->get();
            $investment = new \stdClass();
            $investment->amount = 0;
            $investment->rtr = 0;
            $investment->mgmnt_fee_percentage = 0;
            $investment->syndication_fee_percentage = 0;
            foreach ($investments as $key => $investment_single) {
                $investment->amount += $investment_single->amount;
                $investment->rtr += $investment_single->invest_rtr;
                $investment->mgmnt_fee_percentage += $investment_single->mgmnt_fee_percentage;
                $investment->syndication_fee_percentage += $investment_single->syndication_fee_percentage;
            }
            $total_mgmnt_paid = 0;
            $total_syndication_paid = 0;
            $paid_to_participant = 0;
            $ctd_sum = 0;
            $paid_syndication_fee = 0;
            foreach ($payments as $key => $value) {
                $total_mgmnt_paid = $total_mgmnt_paid + $value->mgmnt_fee;
                $total_syndication_paid = $total_syndication_paid + $value->syndication_fee;
                $paid_to_participant = $paid_to_participant + $value->final_participant_share;
                $ctd_sum = $ctd_sum + $value->payment;
                $paid_syndication_fee = $paid_syndication_fee + $value->syndication_fee;
            }
            $paid_to_participant = FFM::dollar($paid_to_participant);
            $balance = $merchant->rtr - $ctd_sum;
            $balance = FFM::dollar($balance);
            $total_mgmnt_paid = FFM::dollar($total_mgmnt_paid);
            $paid_syndication_fee = FFM::dollar($paid_syndication_fee);
            $tableBuilder->columns([['data' => 'payment_date', 'name' => 'payment_date', 'title' => 'Date Settled'], ['data' => 'merchant', 'name' => 'name', 'title' => 'Merchant', 'orderable' => false, 'searchable' => false], ['data' => 'payment', 'name' => 'payment', 'title' => 'Total Payment', 'searchable' => false], ['data' => 'participant_share', 'name' => 'participant_share', 'title' => 'Participant Share ', 'searchable' => false], ['data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'title' => 'MGMNT FEE', 'searchable' => false], ['data' => 'syndication_fee', 'name' => 'syndication_fee', 'title' => 'Syndication FEE', 'searchable' => false], ['data' => 'final_participant_share', 'name' => 'final_participant_share', 'title' => 'TO PARTICIPANT', 'searchable' => false], ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type', 'searchable' => false]]);
            $month = date('m', strtotime('0 month'));
            $year = date('Y', strtotime('0 month'));
            $month1 = date('m', strtotime('-1 month'));
            $year1 = date('Y', strtotime('-1 month'));
            $month2 = date('m', strtotime('-2 month'));
            $year2 = date('Y', strtotime('-2 month'));
            $month3 = date('m', strtotime('-3 month'));
            $year3 = date('Y', strtotime('-3 month'));
            $chart_data['0']['total_payment']['1'] = DB::table('participent_payments')->where('merchant_id', $id)->whereMonth('payment_date', '=', $month)->whereYear('payment_date', '=', $year)->sum('payment');
            $chart_data['1']['total_payment']['1'] = DB::table('participent_payments')->where('merchant_id', $id)->whereMonth('payment_date', '=', $month1)->whereYear('payment_date', '=', $year1)->sum('payment');
            $chart_data['2']['total_payment']['1'] = DB::table('participent_payments')->where('merchant_id', $id)->whereMonth('payment_date', '=', $month2)->whereYear('payment_date', '=', $year2)->sum('payment');
            $chart_data['3']['total_payment']['1'] = DB::table('participent_payments')->where('merchant_id', $id)->whereMonth('payment_date', '=', $month3)->whereYear('payment_date', '=', $year3)->sum('payment');
            $chart_data['0']['total_payment']['2'] = DB::table('participent_payments')->where('transaction_type', 99988)->where('merchant_id', $id)->whereMonth('payment_date', '=', $month)->whereYear('payment_date', '=', $year)->sum('payment');
            $chart_data['1']['total_payment']['2'] = DB::table('participent_payments')->where('transaction_type', 99988)->where('merchant_id', $id)->whereMonth('payment_date', '=', $month1)->whereYear('payment_date', '=', $year1)->sum('payment');
            $chart_data['2']['total_payment']['2'] = DB::table('participent_payments')->where('transaction_type', 99988)->where('merchant_id', $id)->whereMonth('payment_date', '=', $month2)->whereYear('payment_date', '=', $year2)->sum('payment');
            $chart_data['3']['total_payment']['2'] = DB::table('participent_payments')->where('transaction_type', 99988)->where('merchant_id', $id)->whereMonth('payment_date', '=', $month3)->whereYear('payment_date', '=', $year3)->sum('payment');
            $chart_data = array_reverse($chart_data);

            return view('admin.dashboard.details', compact('paid_syndication_fee', 'page_title', 'tableBuilder', 'merchant', 'total_mgmnt_paid', 'paid_to_participant', 'ctd_sum', 'chart_data', 'balance', 'investment'));
        }
    }
}
