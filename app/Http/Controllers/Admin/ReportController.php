<?php

namespace App\Http\Controllers\Admin;

use MerchantHelper;
use App\Helpers\Report\PaymentReportHelper;
use App\Http\Controllers\Controller;
use App\Library\Helpers\ReportTableBuilder;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\ILiquidityLogRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Html\Builder;
use App\Models\EquityInvestorReport;
use App\Helpers\Report\MerchantReportHelper;
use App\Helpers\Report\InvestorReportHelper;
use App\Helpers\Report\ProfitReportHelper;
use LiquidityLogHelper;
use InvestorHelper;
use ReportHelper;
class ReportController extends Controller
{
    public $total_payments;
    public $rtr;
    public $rtr_paid;
    public $pay_id;
    public $user_id_dtwise;
    public $m_pay;
    public $ppid_dt;
    public $m_pay_added;
    public $m_pay_def;
    public $res_rtr;
    public $paym_id;
    public $filterval;
    public $users_id;
    public $sum_res;
    public $rcode_det;

    public function __construct(IRoleRepository $role, IMerchantRepository $merchant, ILiquidityLogRepository $log, ILabelRepository $label)
    {
        $this->role = $role;
        $this->merchant = $merchant;
        $this->log = $log;
        $this->label = $label;
    }


    public function payments(Request $request, Builder $tableBuilder, IRoleRepository $role, ILabelRepository $label)
    {
        $edate = ! empty($request->end_date) ? $request->end_date : date('Y-m-d', strtotime('+5 days'));
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $subinvestors = [];
        if ($request->owner) {
            $permission = 0;
            $userId = $request->owner;
        }
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->whereIn('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        if ($request->ajax() || $request->wantsJson()) {
            $date_type = 'false';
            if (isset($request->date_type)) { $date_type = $request->date_type; }
            if($date_type!='true'){ $request->time_start = $request->time_end = NULL; }
            return \MTB::paymentReport(
                $request->date_type,
                ET_To_UTC_Time($request->start_date.$request->time_start),
                ET_To_UTC_Time($edate.$request->time_end),
                $request->merchant_id,
                $request->investors,
                $request->lenders,
                $subinvestors,
                ET_To_UTC_Time($request->start_date.$request->time_start, 'time'),
                ET_To_UTC_Time($edate.$request->time_end, 'time'),
                $request->payment_type,
                $request->owner,
                $request->statuses,
                $request->advance_type,
                $request->fields,
                $request->investor_type,
                $request->rcode,
                $request->overpayment,
                $request->label,
                $request->mode_of_payment,
                $request->payout_frequency,
                $request->investor_label,
                $request->historic_status,
                $request->filter_by_agent_fee,
                $request->active_status,
                $request->transaction_id,
                $request->velocity_owned
            );
        }
        
        $response = PaymentReportHelper::paymentsReport($request,$tableBuilder,$role,$label);
        return view('admin.reports.index', $response);
    }

    public function overPaymentReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::overpaymentReport($request->start_date, $request->end_date, $request->merchants, $request->investors, $request->company, $request->lenders, $request->sub_statuses,$request->velocity_owned);
        }
        $response = InvestorReportHelper::overPaymentReport($request,$tableBuilder,$role);
        return view('admin.reports.overpayment', $response);
    }


    public function commissionReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        $date_type = 'false';
        if (isset($request->date_type)) {
            $date_type = $request->date_type;
        }
        if($date_type!='true'){
            $request->time_start = $request->time_end = NULL;
        }
        $eDate = ! empty($request->end_date) ? $request->end_date : date('Y-m-d', strtotime('+5 days'));
        $sDate = ! empty($request->start_date) ? ET_To_UTC_Time($request->start_date.$request->time_start) : null;
        // $request->time_end = date('H:i', strtotime('+1 minute', strtotime($request->time_end)));

        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::comissionReport($request->merchants, $request->date_type,$sDate, ET_To_UTC_Time($eDate.$request->time_end), $request->investors, false,ET_To_UTC_Time($request->start_date.$request->time_start, 'time'), ET_To_UTC_Time($eDate.$request->time_end, 'time'), $request->date_type1, $request->owner,$request->velocity_owned);
        }

        $response = MerchantReportHelper::commissionReport($request,$tableBuilder,$role);
        return view('admin.reports.commission_report',$response);
    }


    public function investorReport(Request $request, Builder $tableBuilder, IRoleRepository $role, ILabelRepository $label)
    {
        $eDate = ! empty($request->end_date) ? $request->end_date : date('Y-m-d', strtotime('+5 days'));
        $sDate = ! empty($request->start_date) ? ET_To_UTC_Time($request->start_date.$request->time_start) : null;
        // $request->time_end = date('H:i', strtotime('+1 minute', strtotime($request->time_end)));
        if ($request->ajax() || $request->wantsJson()) {
            $request->date_type=$request->date_type??null;
            if($request->date_type!='true'){
                $request->time_start = $request->time_end = "00:00";
            }
            return \MTB::investorReport($request->merchants, $request->date_type, $request->advance_type, $request->merchant_date, $sDate, ET_To_UTC_Time($eDate.$request->time_end), $request->investors, $request->lenders, false, ET_To_UTC_Time($request->start_date.$request->time_start, 'time'), ET_To_UTC_Time($eDate.$request->time_end, 'time'), $request->date_type1, $request->industries, $request->owner, $request->statuses, $request->investor_type, $request->sub_status_flag, $request->label, $request->investor_label,$request->input('order'),$request->active_status,$request->velocity_owned);
        }
        $response = InvestorReportHelper::investorReport($request,$tableBuilder,$role,$label);
        return view('admin.reports.investor',$response);
    }

    public function commissionExport(Request $request, IMerchantRepository $merchant, IRoleRepository $role)
    {
        $response = MerchantReportHelper::commissionExport($request,$merchant,$role);
        return Excel::download($response['export'], $response['fileName']);
    }

    public function investorExport(Request $request, IMerchantRepository $merchant, IRoleRepository $role)
    {
        $response = InvestorReportHelper::investorExport($request,$merchant,$role);
        return Excel::download($response['export'], $response['fileName']);
    }

    public function reAssignmentHistory(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::reAssignmentHistory($request->start_date, $request->end_date, $request->investors, $request->merchants);
        }
        $response = InvestorReportHelper::reAssignmentHistory($request,$tableBuilder,$role);
        return view('admin.reports.reassignment_history', $response);
    }

    public function investorAssignmentReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {       
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::investorAssignmentReport($request->start_date, $request->end_date, $request->investors, $request->merchants);
        }
        $response = InvestorReportHelper::investorAssignmentReport($request,$tableBuilder,$role);
        return view('admin.reports.investor_assignment', $response);
    }

    public function paymentExport(Request $request, IMerchantRepository $merchant, IRoleRepository $role)
    {
        $response = PaymentReportHelper::paymentExport($request,$merchant,$role);
        return Excel::download($response['export'], $response['fileName']);
    }

    public function liquidityReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::liquidityReport($request->start_date, $request->end_date, $request->type, $request->active_status, $request->company,$request->velocity_owned);
        }
        $response = InvestorReportHelper::liquidityReport($request,$tableBuilder,$role);
        return view('admin.reports.liquidity_report', $response);
    }

    public function liquidity_log(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $search_key = $request['search']['value'] ?? '';

            return \MTB::getLiquidityLogDetails($request->start_date, $request->end_date, $request->merchant_id, $request->investors, $request->groupbypay, $request->owner, $request->description, $request->label, false, $search_key, $request->accountType,$request->velocity_owned);
        }
        $response = LiquidityLogHelper::getLiquidityLog($request, $tableBuilder);
        return view('admin.reports.liquidity_log', $response);
    }

    public function merchant_liquidity_log(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::getMerchantLiquidityLogDetails($request->start_date, $request->end_date, $request->merchant_id, $request->investors, $request->owner, $request->description, $request->groupbypay, $request->accountType,false,$request->velocity_owned);
        }
        $response = LiquidityLogHelper::getMerchantLiqudityLog($request, $tableBuilder);

        return view('admin.reports.merchant_liquidity_log', $response);
    }

    public function investorAssignmentExport(Request $request, IMerchantRepository $merchant)
    {
        $response = InvestorReportHelper::investorAssignmentExport($request,$merchant);
        return Excel::download($response['export'], $response['fileName']);
    }
    public function liquidityLogExport(Request $request, IMerchantRepository $merchant)
    {
        $response = LiquidityLogHelper::liquidityLogExport($request,$merchant);
        return Excel::download($response['export'], $response['fileName']);
    }


    public function defaultReportDownload(Request $request, IMerchantRepository $merchant, IRoleRepository $role)
    {
        $response = MerchantReportHelper::defaultReportDownload($request,$merchant,$role);
        return Excel::download($response['export'], $response['fileName']);
    }

    public function defaultRateReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $search_key = $request['search']['value'] ?? '';
            $velocity_owned = (isset($request->velocity_owned)) ? true: false;
            return \MTB::defaultRateReport($request->lenders, $request->investors, $request->merchants, $request->rate_type, $request->velocity, $request->from_date, $request->to_date, $request->sub_status, $request->funded_date, $request->active_status, $request->overpayment, $request->days, $request->investor_type,$velocity_owned, null, $search_key);
        }

        $response = MerchantReportHelper::defaultRateReport($request,$tableBuilder,$role);
        return view('admin.reports.default_rate_report',$response);
    }


    public function equityInvestorReportUpdate()
    {
        return  EquityInvestorReport::EquityInvestorReportCheck(); //todo move to cron job
    }

    public function equityInvestorReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::equityInvestorReport($request->investors);
        }
        $response = InvestorReportHelper::equityInvestorReport($request,$tableBuilder,$role);
        return view('admin.reports.equity_investor_report', $response);
    }

    public function totalPortfolioEarnings(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::totalPortfolioEarnings($request->investors);
        }
        $response = InvestorReportHelper::totalPortfolioEarningsReport($request,$tableBuilder,$role);
        return view('admin.reports.dept_investor_report', $response);
    }

    public function profitabilityReport4(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        $sDate = ! empty($request->from_date) ? $request->from_date : '';
        $eDate = ! empty($request->to_date) ? $request->to_date : '';
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::profitabilityReport4($request->merchants, $request->from_date, $request->to_date, $request->funded_date);
        }
        $response = ProfitReportHelper::profitabilityReport4($request,$tableBuilder,$role);
        return view('admin.reports.profitability4',$response);
    }

    public function profitabilityReport2(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::profitabilityReport2($request->merchants, $request->from_date, $request->to_date, $request->funded_date, $request->investor_check);
        }
        $response = ProfitReportHelper::profitabilityReport2($request,$tableBuilder,$role);
        return view('admin.reports.profitability2',$response);
    }

    public function profitabilityReport3(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::profitabilityReport3($request->merchants, $request->from_date, $request->to_date, $request->funded_date);
        }
        $response = ProfitReportHelper::profitabilityReport3($request,$tableBuilder,$role);
        return view('admin.reports.profitability3',$response);
    }

    public function profitabilityReport21(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::profitabilityReport21($request->merchants, $request->from_date, $request->to_date, $request->funded_date);
        }
        $response = ProfitReportHelper::profitabilityReport21($request,$tableBuilder,$role);

        return view('admin.reports.profitability21', $response);
    }

    public function investorInterestAccuredReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::investorInterestAccuredReport($request->investors, $request->date_start, $request->date_end);
        }
        $response = InvestorReportHelper::investorInterestAccuredReport($request,$tableBuilder,$role);
        return view('admin.reports.investor_interest_accured', $response);
    }
    public function investorInterestAccuredDetails(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::investorInterestAccuredDetailAction($request->inv_id, $request->start_date, $request->end_date);
        }
        $response = InvestorReportHelper::investorInterestAccuredDetails($request, $tableBuilder);
        return view('admin.reports.investor_interest_accured_details', $response);
    }
    public function getCommissionData(Request $request)
    {
        $response = MerchantHelper::getCommissionData($request);
        return response()->json(['html' => $response]);

    }

    public function getInvesterData(Request $request)
    {

        $response = InvestorHelper::getInvesterData($request);
        return response()->json(['id' => 'hello', 'data' => 'data 222', 'html' => $response]);
    }


    public function getPaymentData(Request $request)
    {
        $response = PaymentReportHelper::getPaymentData($request);
        
        return response()->json(['id' => 'hello', 'data' => 'data 222', 'html' => $response]);
    }

    public function defaultRateMerchantReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        $response = MerchantReportHelper::defaultRateMerchantReport($role);
        return view('admin.reports.default_rate_merchant_report', $response);
    }

    public function defaultRateMerchantReportData(Request $request,IRoleRepository $role,IMerchantRepository $merchant)
    {
        $response = MerchantReportHelper::defaultRateMerchantReportData($request,$role,$merchant);
        return $response;
    }

    public function defaultRateMerchantReportExport(Request $request, IMerchantRepository $merchant)
    {
        $response = MerchantReportHelper::defaultRateMerchantReportExport($request,$merchant);
        return Excel::download($response['export'], $response['fileName']);
    }

    public function velocityProfitability(Request $request, Builder $tableBuilder, IRoleRepository $role, ILabelRepository $label)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::getVelocityProfitabilityReportDataTable($request->start_date, $request->end_date, $request->company, $request->investors, $request->label);
        }
        $response = ProfitReportHelper::velocityProfitabilityReport($request,$tableBuilder,$role,$label);

        return view('admin.reports.velocity_profitability', $response);
    }

    public function velocityProfitabilityDownload(Request $request)
    {
        $response = ProfitReportHelper::velocityProfitabilityDownload($request);
        return Excel::download($response['export'], $response['fileName']);
    }

    public function profitability2Export(Request $request, IMerchantRepository $merchant)
    {
        $response = ProfitReportHelper::profitability2Export($request,$merchant);
        return Excel::download($response['export'], $response['fileName']);
    }

    public function profitability3Export(Request $request, IMerchantRepository $merchant)
    {
        $response = ProfitReportHelper::profitability3Export($request,$merchant);
        return Excel::download($response['export'], $response['fileName']);
    }

    public function profitability21Export(Request $request, IMerchantRepository $merchant)
    {
        $response = ProfitReportHelper::profitability21Export($request,$merchant);
        
        return Excel::download($response['export'], $response['fileName']);
    }

    public function profitability4Export(Request $request, IMerchantRepository $merchant)
    {
        $response = ProfitReportHelper::profitability4Export($request,$merchant);
        return Excel::download($response['export'], $response['fileName']);
    }


    public function InvestorLiquidityLog(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        $ReportTableBuilder = new ReportTableBuilder;
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->from_date) {
                $requestData['from_date'] = $request->from_date;
            }
            if ($request->to_date) {
                $requestData['to_date'] = $request->to_date;
            }
            if ($request->company_id) {
                $requestData['company_id'] = $request->company_id;
            }
            if ($request->investor_id) {
                $requestData['investor_id'] = $request->investor_id;
            }
            $data = $ReportTableBuilder->getInvestorLiquidityLogList($requestData);

            return $data;
        }
        $response = InvestorReportHelper::InvestorLiquidityLog($request,$tableBuilder,$role);
        return view('admin.reports.InvestorLiquidityLog', $response);
    }

    public function InvestorLiquidityLogDownload(Request $request)
    {
        $response = InvestorReportHelper::InvestorLiquidityLogDownload($request);
        $export = $response['export'];
        $fileName = $response['fileName'];
        return Excel::download($export, $fileName);
    }

    public function InvestorLiquidityLogCreate()
    {
        try {
            DB::beginTransaction();
            \Artisan::call('create:liqduitylog');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }

        return redirect()->route('admin::reports::InvestorLiquidityLog')->withSuccess('Successfully Created');
    }

    public function InvestorLiquidityLogTruncate()
    {
        DB::table('manual_liquidity_logs')->truncate();

        return redirect()->route('admin::reports::InvestorLiquidityLog')->withSuccess('Successfully Deleted');
    }

    public function InvestorRTRBalanceLog(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        $ReportTableBuilder = new ReportTableBuilder;
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->from_date) {
                $requestData['from_date'] = $request->from_date;
            }
            if ($request->to_date) {
                $requestData['to_date'] = $request->to_date;
            }
            if ($request->company_id) {
                $requestData['company_id'] = $request->company_id;
            }
            if ($request->investor_id) {
                $requestData['investor_id'] = $request->investor_id;
            }
            $data = $ReportTableBuilder->getInvestorRTRBalanceLogList($requestData);

            return $data;
        }
        $response = InvestorReportHelper::InvestorRTRBalanceLog($request,$tableBuilder,$role);
        return view('admin.reports.InvestorRTRBalanceLog', $response);
    }

    public function InvestorRTRBalanceLogDownload(Request $request)
    {
        $response = InvestorReportHelper::InvestorRTRBalanceLogDownload($request);
        $export = $response['export'];
        $fileName = $response['fileName'];
        return Excel::download($export, $fileName);
    }

    public function InvestorRTRBalanceLogCreate()
    {
        try {
            DB::beginTransaction();
            \Artisan::call('create:rtrbalance');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }

        return redirect()->route('admin::reports::InvestorRTRBalanceLog')->withSuccess('Successfully Created');
    }

    public function InvestorRTRBalanceTruncate()
    {
        DB::table('manual_r_t_r_balance_logs')->truncate();

        return redirect()->route('admin::reports::InvestorRTRBalanceLog')->withSuccess('Successfully Deleted');
    }

    public function profitCarryForwardReport(Request $request, Builder $tableBuilder, IRoleRepository $role)
    {
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::profitCarryForwardReport($request->start_date, $request->end_date, $request->investors, $request->merchants, false, $request->type);
        }
        $response = ProfitReportHelper::profitCarryForwardReport($request, $tableBuilder);

        return view('admin.reports.profit_carry_forward', $response);
    }

    public function agentFeeReport(Request $request, Builder $tableBuilder)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::agentFeeReport($request->merchants, $request->from_date, $request->to_date);
        }
        $response = InvestorReportHelper::agentFeeReport($request,$tableBuilder);
        return view('admin.reports.agent_fee_report', $response);
    }
    
    public function advance_plus_investments($investor_id,$labels=null)
    {
        $page_title = 'Advance Plus Investments Report';
        $response = InvestorReportHelper::AdvancePlusInvestmentsReport($investor_id,$labels);
        $investors = $this->role->allInvestors()->pluck('name', 'id');
        return view('admin.reports.advance_plus_investments')
        ->with('page_title',$page_title)
        ->with('investor_id',$investor_id)
        ->with('MerchantUsers',$response['MerchantUsers'])
        ->with('Investor',$response['Investor'])
        ->with('data',$response['data'])
        ->with('dates',$response['dates'])
        ->with('investors',$investors)
        ->with('labels',$response['labels'])
        ;
    }

    public function TaxReport(Request $request, Builder $tableBuilder) {
        if ($request->ajax() || $request->wantsJson()) {
            return ReportHelper::TaxReportDataTable($request);
        }
        $response = ReportHelper::TaxPageDetails($tableBuilder);
        return view('admin.reports.tax_report', $response);
    }
    public function TaxReportExport(Request $request)
    {
        $response = ReportHelper::TaxReportExportData($request);
        return Excel::download($response['export'], $response['fileName']);
    }
}
