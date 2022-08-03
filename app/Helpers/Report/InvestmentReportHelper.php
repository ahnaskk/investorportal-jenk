<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use App\Merchant;
use App\MerchantUser;
use App\ReassignHistory;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\Report\InvestorReportHelper;
use PayCalc;

class InvestmentReportHelper
{
    public static function getTableColumns()
    {
        return [['className' => 'details-control', 'orderable' => false, 'data' => null, 'defaultContent' => '', 'title' => ''], ['data' => 'id', 'name' => 'id', 'title' => 'Merchant Id'], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant Name'], ['data' => 'merchants.date_funded', 'name' => 'date_funded', 'title' => 'Funded Date'], ['orderable' => false, 'data' => 'merchant_user.funded', 'name' => 'merchant_user.funded', 'title' => 'Funded Amount'], ['orderable' => false, 'data' => 'rtr', 'name' => 'rtr', 'title' => 'RTR'], ['orderable' => false, 'data' => 'commission', 'name' => 'commission', 'title' => 'Commission'], ['orderable' => false, 'data' => 'share_t', 'name' => 'share_t', 'title' => 'Share (%)'], ['orderable' => false, 'data' => 'prepaid_amount_t', 'name' => 'prepaid_amount_t', 'title' => 'Prepaid Payment'], ['orderable' => false, 'data' => 'total_payment_t', 'name' => 'total_payment_t', 'title' => 'Total Invested'], ['orderable' => false, 'data' => 'under_writting_fee', 'name' => 'under_writting_fee', 'title' => 'Under Writing Fee'], ['orderable' => false, 'data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'title' => 'Management Fee'], ['data' => 'created_date', 'name' => 'created_date', 'title' => 'Created On']];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getTableColumns(), self::getReport($request), time().'-'.'Investment');
    }

    public static function downloadAssignmentReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getAssignmentColumns(), self::getAssignmentReport($request), time().'-'.'Investor Assignment');
    }

    public static function downloadReAssignmentReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getReAssignmentColumns(), self::getReAssignmentReport($request), time().'-'.'Investor Reassignment');
    }

    public static function getReport(Request $request)
    {
        $merchantIds = $request->input('merchants', []);
        $investorIds = $request->input('investors', []);
        $lenderIds = $request->input('lenders', []);
        $industryIds = $request->input('industries', []);
        $dateType = $request->input('date_type');
        $advanceTypes = $request->input('advance_type');
        $merchantDate = $request->input('merchant_date');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $startTime = $request->input('time_start');
        $endTime = $request->input('time_end');
        $dateType1 = $request->input('date_type1');
        $owner = $request->input('owner');
        $subStatusIds = $request->input('statuses');
        $investor_type = $request->input('investor_type');
        $subStatusFlag = $request->input('sub_status_flag');
        $label = $request->input('label');
        $isExport = $request->input('is_export') == 'yes';
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            $companyUserQuery = DB::table('users')->where('company', $userId);
        } else {
            $companyUserQuery = DB::table('users');
        }
        if (! empty($investorIds) && is_array($investorIds)) {
            $companyUserQuery = $companyUserQuery->whereIn('id', $investorIds);
        }
        if ($owner) {
            $companyUserQuery = $companyUserQuery->where('company', $owner);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $companyUserQuery = $companyUserQuery->whereIn('investor_type', $investor_type);
        }
        $companyUsers = $companyUserQuery->pluck('id')->toArray();
        if ($dateType1 == 'true') {
            if ($startTime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$startTime : null;
            }
            if ($endTime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$endTime : null;
            }
        }
        $merchantQuery = Merchant::leftJoin('industries', 'industries.id', 'merchants.industry_id')->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->whereIn('merchant_user.status', [1, 3]);
        $table_field = ($dateType == 'true') ? 'merchant_user.created_at' : 'date_funded';
        if ($table_field == 'merchant_user.created_at') {
            if ($startTime != 0) {
                $startDate = ($startDate) ? $startDate.' '.$startTime : null;
            }
            if ($endTime != 0) {
                $endDate = ($endDate) ? $endDate.' '.$endTime : null;
            }
        }
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $merchantQuery->whereIn('merchants.id', $merchantIds);
        }
        if ($label) {
            $merchantQuery->where('merchants.label', $label);
        }
        if ($startDate && ! empty($startDate)) {
            $merchantQuery->where($table_field, '>=', $startDate);
        }
        if ($endDate && ! empty($endDate)) {
            $merchantQuery->where($table_field, '<=', $endDate);
        }
        if ($lenderIds && is_array($lenderIds)) {
            $merchantQuery->whereIn('merchants.lender_id', $lenderIds);
        }
        $merchantQuery->whereIn('merchant_user.user_id', $companyUsers);
        $default_date = ! empty($endDate) ? $endDate : now();
        $merchantDayQuery = PayCalc::setDaysCalculation($default_date);
        if ($industryIds && is_array($industryIds)) {
            $merchantQuery->whereIn('merchants.industry_id', $industryIds);
        }
        if ($subStatusIds && is_array($subStatusIds)) {
            $merchantQuery->whereIn('merchants.sub_status_id', $subStatusIds);
        }
        if ($advanceTypes && is_array($advanceTypes)) {
            $merchantQuery->whereIn('merchants.advance_type', $advanceTypes);
        }
        if ($subStatusFlag) {
            $merchantQuery->whereIn('merchants.sub_status_flag', $subStatusFlag);
        }
        $merchantQuery->where('active_status', 1);
        $totalQuery = clone $merchantQuery;
        $total = $totalQuery->select(DB::raw('SUM(commission_amount) as total_commission_amount'), DB::raw('sum(merchant_user.under_writing_fee) as total_under_writing_fee'), DB::raw('count(DISTINCT merchants.id) as count'), DB::raw('sum(amount) as total_amount'), DB::raw('sum(pre_paid) as totatl_pre_paid_amount'), DB::raw('sum(invest_rtr) as total_invest_rtr'), DB::raw('sum(invest_rtr * mgmnt_fee / 100) as total_mgmnt_fee'))->first();
        $merchantQuery->groupBy('merchants.id')->select(DB::raw('SUM(merchant_user.paid_participant_ishare - paid_mgmnt_fee ) as ctd'), DB::raw('SUM(
					IF( paid_participant_ishare > invest_rtr, 
						(paid_participant_ishare-invest_rtr )*(1- (merchant_user.mgmnt_fee)/100 ), 
						0
					) ) as overpayment'), DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid) as invested_amount'), DB::raw('sum(
					'.$merchantDayQuery.'
                        *   (
                                (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                                -
                                IF( (merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee),
                                    (merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee),
                                    0
                                )
                            )
                    ) as default_amount'), DB::raw('SUM(commission_amount) as commission_amount'), DB::raw('sum(pre_paid) as pre_paid'), DB::raw('sum(merchant_user.under_writing_fee) as under_writing_fee'), DB::raw('sum( invest_rtr * mgmnt_fee/100) as mgmnt_fee'), DB::raw('sum(amount) as i_amount'), DB::raw('sum(invest_rtr) as invest_rtr'), 'merchants.id', 'industries.name as industry_name', 'merchants.funded', 'merchants.created_at', 'merchants.name', 'merchants.date_funded', 'merchants.commission', 'merchants.sub_status_id', 'merchants.underwriting_status');
        $datTable = \IPVueTable::of($merchantQuery);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('merchants.date_funded', function ($data) {
            return \FFM::date($data->date_funded);
        })->editColumn('name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>";
        })->editColumn('commission', function ($data) {
            return \FFM::dollar($data->commission_amount).' ('.\FFM::percent($data->commission).')';
        })->addColumn('prepaid_amount_t', function ($data) {
            return \FFM::dollar($data->pre_paid);
        })->addColumn('total_payment_t', function ($data) {
            $totalPaymentAmount = $data->pre_paid + $data->i_amount + $data->commission_amount + $data->under_writing_fee;

            return \FFM::dollar($totalPaymentAmount);
        })
        ->addColumn('mgmnt_fee', function ($data) {
            return \FFM::dollar($data->mgmnt_fee);
        })->addColumn('under_writting_fee', function ($data) {
            return \FFM::dollar($data->under_writing_fee);
        })->editColumn('rtr', function ($data) {
            return \FFM::dollar($data->invest_rtr);
        })->addColumn('share_t', function ($data) {
            return \FFM::percent($data->i_amount / $data->funded * 100);
        })->addColumn('merchant_user.funded', function ($data) {
            return \FFM::dollar($data->i_amount);
        })->editColumn('created_date', function ($data) {
            return $data->created_at->format('m-d-Y h:i:s A');
        })->with('gt_total_prepaid_amount', \FFM::dollar($total->total_pre_paid_amount))->with('gt_total_amount', \FFM::dollar($total->total_total_amount + $total->total_pre_paid_amount + $total->total_commission_amount + $total->total_under_writing_fee))->with('gt_total_commission', \FFM::dollar($total->total_commission_amount))->with('gt_total_funded', \FFM::dollar($total->total_total_amount))->with('gt_total_rtr', \FFM::dollar($total->total_invest_rtr))->with('gt_total_mgmnt_fee', \FFM::dollar($total->total_mgmnt_fee))->with('gt_total_under_writing_fee', \FFM::dollar($total->total_under_writing_fee))->with('download-url', api_download_url('investment-download'))->make(true);
    }

    public static function getAssignmentColumns()
    {
        return [['className' => 'details-control', 'data' => 'id', 'defaultContent' => '', 'title' => 'No'], ['orderable' => false, 'data' => 'investor_id', 'name' => 'investor_id', 'defaultContent' => '', 'title' => 'Investor'], ['data' => 'merchant_id', 'name' => 'merchant_id', 'title' => 'Merchant'], ['orderable' => false, 'data' => 'participant_amount', 'name' => 'participant_amount', 'title' => 'Participant Amount'], ['data' => 'liquidity', 'name' => 'liquidity', 'title' => 'Liquidity'], ['orderable' => false, 'data' => 'date', 'name' => 'date', 'defaultContent' => '', 'title' => 'Date']];
    }

    public static function getAssignmentReport(Request $request)
    {
        $merchantIds = $request->input('merchants', []);
        $investorIds = $request->input('investors', []);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $isExport = $request->input('is_export') == 'yes';
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $subInvestors = (empty($permission)) ? User::investors()->pluck('id')->toArray() : [];
        $merchantQuery = MerchantUser::whereIn('merchant_user.status', [1, 3])->whereHas('merchant', function ($query) {
            $query->where('active_status', 1);
        })->with(['merchant']);
        if (is_array($investorIds) and count($investorIds) > 0) {
            $merchantQuery->whereIn('merchant_user.user_id', $investorIds);
        }
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $merchantQuery->whereIn('merchant_user.merchant_id', $merchantIds);
        }
        if (! empty($startDate)) {
            $startDate = $startDate.' 00:00:00';
            $merchantQuery->whereDate('merchant_user.created_at', '>=', $startDate);
        }
        if (! empty($endDate)) {
            $endDate = $endDate.' 23:23:59';
            $merchantQuery->whereDate('merchant_user.created_at', '<=', $endDate);
        }
        if (empty($permission)) {
            $merchantQuery->whereIn('merchant_user.user_id', $subInvestors);
        }
        $merchantQuery->where('status', '=', 1)->with('merchant');
        $totals = $merchantQuery->get([DB::raw('SUM(merchant_user.amount) as amount'), DB::raw('SUM(merchant_user.commission_amount) as commission_amount'), DB::raw('SUM(merchant_user.pre_paid) as pre_paid')])->first()->toArray();
        $totalAmount = $totals['amount'];
        $paidSyndication = $totals['pre_paid'];
        $totalCommission = $totals['commission_amount'];
        $totalAmount = $totalAmount + $totalCommission + $paidSyndication;
        $datTable = \IPVueTable::of($merchantQuery);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('investor_id', function ($data) {
            $name = User::where('id', '=', $data->user_id)->value('name');

            return isset($name) ? $name : '';
        })->addColumn('merchant_id', function ($data) {
            return isset($data->merchant->name) ? $data->merchant->name : '';
        })->addColumn('participant_amount', function ($data) {
            return FFM::dollar(($data->amount + $data->pre_paid + $data->commission_amount));
        })->addColumn('date', function ($data) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('M j, Y');
        })->addColumn('liquidity', function ($data) {
            $liquidity = UserDetails::where('user_id', '=', $data->user_id)->value('liquidity');

            return $liquidity ? $liquidity : 0;
        })->with('participant_amount', FFM::dollar($totalAmount))->with('download-url', api_download_url('investor-assignment-download'))->make(true);
    }

    public static function getReAssignmentColumns()
    {
        return [['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => 'No'], ['orderable' => false, 'data' => 'investor_from', 'name' => 'investor_from', 'defaultContent' => '', 'title' => 'Investor From'], ['data' => 'investor_to', 'name' => 'investor_to', 'title' => 'Investor To'], ['data' => 'merchant', 'name' => 'merchant', 'title' => 'Merchant'], ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'], ['orderable' => false, 'data' => 'liquidity_change', 'name' => 'liquidity_change', 'defaultContent' => '', 'title' => 'Liquidity Change'], ['orderable' => true, 'data' => 'investor1_final_liquidity', 'name' => 'investor1_final_liquidity', 'defaultContent' => '', 'title' => 'Investor1 Final Liquidity'], ['orderable' => true, 'data' => 'investor2_final_liquidity', 'name' => 'investor2_final_liquidity', 'defaultContent' => '', 'title' => 'investor2 Final Liquidity'], ['orderable' => true, 'data' => 'date', 'name' => 'date', 'defaultContent' => '', 'title' => 'Date'], ['data' => 'action', 'name' => 'action', 'orderable' => false, 'title' => 'Action']];
    }

    public static function getReAssignmentReport(Request $request)
    {
        $merchantIds = $request->input('merchants', []);
        $investorIds = $request->input('investors', []);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $isExport = $request->input('is_export') == 'yes';
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        $subInvestors = User::investors()->pluck('id')->toArray();
        $investorQuery = ReassignHistory::whereHas('investmentData1', function ($q1) use ($subInvestors, $permission, $investorIds) {
            if (empty($permission)) {
                $q1->whereIn('id', $subInvestors);
            }
        })->with(['investmentData1' => function ($inner) use ($subInvestors, $permission, $investorIds) {
            if (empty($permission)) {
                $inner->whereIn('id', $subInvestors);
            }
        }])->with(['investmentData2' => function ($inner) use ($subInvestors, $permission, $investorIds) {
            if (empty($permission)) {
                $inner->whereIn('id', $subInvestors);
            }
        }])->whereHas('investmentData2', function ($inner) use ($subInvestors, $permission, $investorIds) {
            if (empty($permission)) {
                $inner->whereIn('id', $subInvestors);
            }
        })->with(['merchantData'])->whereHas('merchantData', function ($inner) use ($merchantIds) {
            $inner->where('active_status', 1);
        })->with(['merchantPayment'], function ($inner) {
        });
        if ($startDate) {
            $investorQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $investorQuery->whereDate('created_at', '<=', $endDate);
        }
        if (is_array($investorIds) and count($investorIds) > 0) {
            $investorQuery->whereIn('investor1', $investorIds);
        }
        if (is_array($investorIds) and count($investorIds) > 0) {
            $investorQuery->whereIn('investor2', $investorIds);
        }
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $investorQuery->wherein('merchant_id', $merchantIds);
        }
        $datTable = \IPVueTable::of($investorQuery);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('investor_from', function ($data) {
            return isset($data->investmentData1->name) ? $data->investmentData1->name : '';
        })->addColumn('investor_to', function ($data) {
            return isset($data->investmentData2->name) ? $data->investmentData2->name : '';
        })->addColumn('investor1_final_liquidity', function ($data) {
            return $data->investor1_total_liquidity;
        })->addColumn('amount', function ($data) {
            return $data->amount;
        })->addColumn('investor2_final_liquidity', function ($data) {
            return $data->investor2_total_liquidity;
        })->addColumn('liquidity_change', function ($data) {
            return $data->liquidity_change;
        })->addColumn('date', function ($data) {
            return ($data->created_at != '') ? Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('M j, Y') : '';
        })->addColumn('investor1_payment', function ($data) {
            $payment1 = $data->merchantPayment->where('user_id', $data->investor1)->sum('final_participant_share');

            return isset($payment1) ? $payment1 : 0;
        })->addColumn('investor2_payment', function ($data) {
            $payment2 = $data->merchantPayment->where('user_id', $data->investor2)->sum('final_participant_share');

            return isset($payment2) ? $payment2 : 0;
        })->addColumn('merchant', function ($data) {
            return isset($data->merchantData->name) ? $data->merchantData->name : '';
        })->addColumn('action', function ($data) {
            return '<a href ="#" onclick="undo_function('.$data->investor2.','.$data->merchant_id.');" class="btn btn-success">
        					<i class="glyphicon glyphicon-repeat"></i>Undo Re-assign</a>';
        })->with('download-url', api_download_url('investor-re-assignment-download'))->make(true);
    }

    public static function AdvancePlusInvestments(Request $request)
    {
        $investor_id = Auth::user()->id;
        $return = InvestorReportHelper::AdvancePlusInvestmentsReport($investor_id);
        return [
            'Investment' => $return['Investment'],
            'data'       => $return['data'],
            'dates'      => $return['dates'],
        ];
    }
}
