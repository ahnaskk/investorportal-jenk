<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use function App\Helpers\modelQuerySql;
use App\Merchant;
use App\PaymentInvestors;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PayCalc;
use Spatie\Permission\Models\Role;

class DelinquentRateReportHelper
{
    public static function getTableColumns()
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'ID', 'orderable' => false],
            ['data' => 'name', 'name' => 'name', 'title' => 'Merchant', 'orderable' => false],
            ['data' => 'last_payment_date', 'name' => 'last_payment_date', 'title' => 'Last payment date'],
            ['data' => 'total_invested', 'name' => 'total_invested', 'title' => 'Total Invested', 'orderable' => false, 'searchable' => false],
            ['data' => 'net_ctd', 'name' => 'net_ctd', 'title' => 'Net amount paid', 'orderable' => false, 'searchable' => false],
            ['data' => 'principal_paid', 'name' => 'principal_paid', 'title' => 'Principal Paid', 'orderable' => false, 'searchable' => false],
            ['data' => 'profit_paid', 'name' => 'profit_paid', 'title' => 'Profit Paid', 'orderable' => false, 'searchable' => false],
            ['data' => 'principal_less', 'name' => 'principal_less', 'title' => 'Principal less Principal Paid', 'orderable' => false, 'searchable' => false],
            ['data' => 'profit_less', 'name' => 'profit_less', 'title' => 'Principal less Principal & Profit Paid', 'orderable' => false, 'searchable' => false],
            ['data' => 'lender', 'name' => 'lender', 'title' => 'Lender', 'orderable' => false, 'searchable' => false],
            ['data' => 'industry', 'name' => 'industry', 'title' => 'Industry', 'orderable' => false, 'searchable' => false],
        ];
    }

    public static function getDownload(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getTableColumns(), self::getReport($request), time().'-'.'Delinquent Rate');
    }

    public static function getReport(Request $request)
    {
        $merchantQuery = self::getReportQuery($request);
        $merchants = $merchantQuery->get();
        $isExport = $request->input('is_export') == 'yes';
        $totalInvestedAmount = 0;
        foreach ($merchants as $merchant) {
            $totalInvestedAmount += $merchant->investments->sum('total_invested');
        }
        $datTable = \IPVueTable::of($merchants);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('profit_paid', function ($data) {
            $principalPaid = ($data->investments->sum('total_invested')) / ($data->investments->sum('invest_rtr')) * $data->participantPayment->sum('net_ctd');
            $profitPaid = $data->participantPayment->sum('net_ctd') - $principalPaid;

            return FFM::dollar($profitPaid);
        })->editColumn('total_invested', function ($data) {
            $totalInvested = $data->investments->sum('total_invested');

            return FFM::dollar($totalInvested);
        })->editColumn('net_ctd', function ($data) {
            $net_ctd = $data->participantPayment->sum('net_ctd');

            return FFM::dollar($net_ctd);
        })->editColumn('principal_paid', function ($data) {
            $principalPaid = ($data->investments->sum('total_invested')) / ($data->investments->sum('invest_rtr')) * $data->participantPayment->sum('net_ctd');

            return FFM::dollar($principalPaid);
        })->editColumn('principal_less', function ($data) {
            $principalPaid = ($data->investments->sum('total_invested')) / ($data->investments->sum('invest_rtr')) * $data->participantPayment->sum('net_ctd');
            $totalInvested = $data->investments->sum('total_invested');
            $principalLess = $totalInvested - $principalPaid;

            return FFM::dollar(-$principalLess);
        })->editColumn('profit_less', function ($data) {
            $totalInvested = $data->investments->sum('total_invested');
            $net_ctd = $data->participantPayment->sum('net_ctd');

            return FFM::dollar(-($totalInvested - $net_ctd));
        })->with('total_invested_amount', FFM::dollar($totalInvestedAmount))->with('download-url', api_download_url('delinquent-download'))->make(true);
    }

    public static function getReportQuery(Request $request)
    {
        $lenderIds = $request->input('lenders', []);
        $industry = $request->input('industry');
        $company = $request->input('company');
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');
        $fundedDate = $request->input('funded_date');
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        $subInvestors = (empty($permission)) ? User::investors()->pluck('id') : [];
        $companyInvestors = ($company) ? User::where('company', $company)->pluck('id')->toArray() : [];
        $merchantQuery = Merchant::whereHas('investments', function ($inner) use ($subInvestors, $permission, $company, $companyInvestors) {
            $inner->whereIn('merchant_user.status', [1, 3]);
            $inner->groupBy('merchant_id');
            if (empty($permission)) {
                $inner->whereIn('merchant_user.user_id', $subInvestors);
            }
            if ($company != null) {
                $inner->whereIn('merchant_user.user_id', $companyInvestors);
            }
        })->with(['investments' => function ($query) use ($subInvestors, $permission, $company, $companyInvestors) {
            $query->select(DB::raw('SUM(merchant_user.amount + merchant_user.pre_paid + merchant_user.commission_amount) as total_invested'), DB::raw('SUM(merchant_user.invest_rtr - merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100)  as invest_rtr'))->whereIn('merchant_user.status', [1, 3]);
            $query->groupBy('merchant_id');
            if (empty($permission)) {
                $query->whereIn('merchant_user.user_id', $subInvestors);
            }
            if ($company != null) {
                $query->whereIn('merchant_user.user_id', $companyInvestors);
            }
        }])->whereHas('participantPayment', function ($q) use ($fromDate, $toDate, $subInvestors, $permission, $company, $companyInvestors) {
            $q->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.is_payment', 1)->groupBy('payment_investors.id');
            $q->select(DB::raw('sum(payment_investors.participant_share - payment_investors.mgmnt_fee) as net_ctd'), 'participent_payments.merchant_id', 'payment_investors.id');
            if (empty($permission)) {
                $q->whereIn('payment_investors.user_id', $subInvestors);
            }
            if ($toDate != null) {
                $q->where('payment_date', '<=', $toDate);
            }
            if ($fromDate != null) {
                $q->where('payment_date', '>=', $fromDate);
            }
            if ($company != null) {
                $q->whereIn('payment_investors.user_id', $companyInvestors);
            }
            $q->orderByDesc('participent_payment_id');
        })->with(['participantPayment' => function ($inner) use ($fromDate, $toDate, $subInvestors, $permission, $company, $companyInvestors) {
            $inner->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.is_payment', 1)->groupBy('payment_investors.id');
            $inner->select('participent_payments.merchant_id', 'payment_investors.id', 'participent_payments.payment_date', DB::raw('sum(payment_investors.profit) as profit_value'), DB::raw('sum(payment_investors.participant_share - payment_investors.mgmnt_fee) as net_ctd'));
            if (empty($permission)) {
                $inner->whereIn('payment_investors.user_id', $subInvestors);
            }
            if ($toDate != null) {
                $inner->where('payment_date', '<=', $toDate);
            }
            if ($fromDate != null) {
                $inner->where('payment_date', '>=', $fromDate);
            }
            if ($company != null) {
                $inner->whereIn('payment_investors.user_id', $companyInvestors);
            }
            $inner->orderByDesc('participent_payment_id');
        }])->select('merchants.*')->whereIn('sub_status_id', [4, 22])->join('users as lender', 'merchants.lender_id', 'lender.id')->join('industries', 'merchants.industry_id', 'industries.id');
        if (isset($industry[0]) && $industry[0]) {
            $merchantQuery->whereIn('merchants.industry_id', $industry);
        }
        if (is_array($lenderIds) and count($lenderIds) > 0) {
            $merchantQuery->whereIn('merchants.lender_id', $lenderIds);
        }

        return $merchantQuery;
    }

    public static function getLenderReportColumns()
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'No', 'orderable' => false],
            ['data' => 'lender_name', 'name' => 'lender_name', 'title' => 'Lender'],
            ['data' => 'invested_amount', 'name' => 'invested_amount', 'title' => 'Invested Amount', 'orderable' => false],
            ['data' => 'share', 'name' => 'share', 'title' => 'Share %', 'orderable' => false],
            ['data' => 'default_invested', 'name' => 'default_invested', 'title' => 'Default Invested', 'orderable' => false],
            ['data' => 'default_ctd_profit', 'name' => 'default_ctd_profit', 'title' => 'CTD profit', 'orderable' => false],
            ['data' => 'default_per', 'name' => 'default_per', 'title' => 'Default (%)', 'orderable' => false],
        ];
    }

    public static function getLenderReport(Request $request)
    {
        $industryIds = $request->input('industry', []);
        $lenderIds = $request->input('lenders', []);
        $merchantIds = $request->input('merchants', []);
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        $subInvestors = (empty($permission)) ? User::investors()->pluck('id')->toArray() : [];
        $defaultDate = NOW();
        $merchantDayQuery = PayCalc::setDaysCalculation($defaultDate);
        $lenderQuery = Role::where('roles.name', 'lender')->join('user_has_roles', 'user_has_roles.role_id', '=', 'roles.id')->join('users', 'users.id', '=', 'user_has_roles.model_id')->join('merchants', 'merchants.lender_id', '=', 'users.id')->join('merchant_user', 'merchant_user.merchant_id', '=', 'merchants.id')->whereIn('merchant_user.status', [1, 3])->where('users.active_status', 1)->select('users.id as lender_id', DB::raw('
					SUM(merchant_user.amount) 
					+ 
					SUM(merchant_user.pre_paid) 
					+ 
					SUM(merchant_user.commission_amount) 
					+ 
					SUM(merchant_user.under_writing_fee) as invested_amount'), DB::raw('SUM(paid_participant_ishare - merchant_user.paid_mgmnt_fee) as ctd_pp'), DB::raw(' ( '.$merchantDayQuery.'
	                    *
	                    (
	                        SUM( merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
	                        -
	                        SUM( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee )
	                    ) 
                    ) as default_amount'), DB::raw('SUM(
					( paid_participant_ishare-paid_mgmnt_fee ) 
					- 
					(   
						( paid_participant_ishare-paid_mgmnt_fee ) 
						* 
						( merchant_user.pre_paid + commission_amount + under_writing_fee + merchant_user.amount )
                        /
                        ( merchant_user.invest_rtr - ( merchant_user.mgmnt_fee / 100 ) * merchant_user.invest_rtr)
                     
                    )
                ) as ctd_p'), 'users.name as lender_name', 'users.id')->join('users as users_investor', 'users_investor.id', '=', 'merchant_user.user_id');
        if (empty($permission)) {
            $lenderQuery->where('users_investor.company', $userId);
        }
        if (is_array($industryIds) and count($industryIds) > 0) {
            $lenderQuery->whereIn('merchants.industry_id', $industryIds);
        }
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $lenderQuery->whereIn('merchants.id', $merchantIds);
        }
        if (is_array($lenderIds) and count($lenderIds) > 0) {
            $lenderQuery->whereIn('merchants.lender_id', $lenderIds);
        }
        $profitQuery = PaymentInvestors::join('merchants', 'payment_investors.merchant_id', '=', 'merchants.id');
        if (is_array($lenderIds) and count($lenderIds) > 0) {
            $profitQuery->whereIn('merchants.lender_id', $lenderIds);
        }
        if (is_array($industryIds) and count($industryIds) > 0) {
            $profitQuery->whereIn('merchants.industry_id', $industryIds);
        }
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $profitQuery->whereIn('merchants.id', $merchantIds);
        }
        if (empty($permission)) {
            $profitQuery->leftJoin('users', 'users.id', '=', 'payment_investors.user_id');
            $profitQuery->where('users.company', $userId);
        }
        $overPaymentQuery = PaymentInvestors::join('participent_payments', 'payment_investors.participent_payment_id', '=', 'participent_payments.id')->where('participent_payments.is_payment', 1)->join('merchants', 'merchants.id', '=', 'participent_payments.merchant_id');
        if (is_array($lenderIds) and count($lenderIds) > 0) {
            $overPaymentQuery->whereIn('merchants.lender_id', $lenderIds);
        }
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $overPaymentQuery->whereIn('merchants.id', $merchantIds);
        }
        if (is_array($industryIds) and count($industryIds) > 0) {
            $overPaymentQuery->whereIn('merchants.industry_id', $industryIds);
        }
        if (empty($permission)) {
            $overPaymentQuery->join('users', 'users.id', '=', 'payment_investors.user_id');
            $overPaymentQuery->where('users.company', $userId);
        }
        $profits = $profitQuery->groupBy('merchants.lender_id')->select(DB::raw('sum(payment_investors.profit) as profit'), 'merchants.lender_id')->pluck('profit', 'merchants.lender_id')->toArray();
        $overPayments = $overPaymentQuery->groupBy('merchants.lender_id')->select(DB::raw('sum(overpayment) as overpayment'), 'merchants.lender_id')->pluck('overpayment', 'merchants.lender_id')->toArray();
        $totalOverPaymentAmount = collect($overPayments)->sum();
        $totalProfitAmount = collect($profits)->sum();
        $lenderSumQuery = clone $lenderQuery;
        $defaultLenderQuery = (clone $lenderQuery)->whereIn('merchants.sub_status_id', [4, 22]);
        $defaultLenderSumQuery = clone $defaultLenderQuery;
        $lenderQuery->groupBy('users.id');
        $defaultLenderQuery->groupBy('users.id');
        $lenders = $lenderQuery->get();
        $lenderSum = $lenderSumQuery->first();
        $defaultLenderSum = $defaultLenderSumQuery->first();
        $totalInvestedAmount = $lenderSum->invested_amount;
        $totalDefaultInvestedAmount = $defaultLenderSum->default_amount - $totalOverPaymentAmount;

        return \IPVueTable::of($lenders)->addColumn('lender_name', function ($lenders) {
            return $lenders->lender_name;
        })->addColumn('invested_amount', function ($lenders) {
            return FFM::dollar($lenders->invested_amount);
        })->addColumn('share', function ($lenders) use ($totalInvestedAmount) {
            return FFM::percent($lenders->invested_amount / $totalInvestedAmount * 100);
        })->addColumn('default_invested', function ($merchant) use ($overPayments) {
            return FFM::dollar($merchant->default_invested - $overPayments[$merchant->lender_id]);
        })->addColumn('default_ctd_profit', function ($merchant) use ($profits) {
            if (isset($profit[$merchant->lender_id])) {
                return FFM::dollar($profits[$merchant->lender_id]);
            } else {
                return FFM::dollar(0);
            }
        })->addColumn('default_per', function ($lenders) use ($overPayments) {
            try {
                return FFM::percent(($lenders->default_invested) / $lenders->invested_amount * 100);
            } catch (\ErrorException $e) {
                return FFM::percent(0);
            }
        })->with('total_invested_amount', FFM::dollar($totalInvestedAmount))
            ->with('total_default_invested', FFM::dollar($totalDefaultInvestedAmount))
            ->with('total_default_ctd_profit', FFM::dollar($totalProfitAmount))
            ->make(true);
    }
}
