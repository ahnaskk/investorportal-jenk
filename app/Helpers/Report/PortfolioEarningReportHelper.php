<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use function App\Helpers\modelQuerySql;
use App\InvestorTransaction;
use App\MerchantUser;
use App\PaymentInvestors;
use App\Settings;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortfolioEarningReportHelper
{
    public static function getColumns()
    {
        return [['data' => 'id', 'name' => 'id', 'title' => 'Sl No', 'orderable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Investor'], ['data' => 'credit_amount', 'name' => 'credit_amount', 'title' => 'Credited Amount'], ['data' => 'total_portfolio_earnings', 'name' => 'total_portfolio_earnings', 'title' => 'Total Portfolio Earnings'], ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'], ['data' => 'distributions', 'name' => 'distributions', 'title' => 'Distributions'], ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value']];
    }

    public static function getDownload(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Portfolio Earning Rate');
    }

    public static function getReport(Request $request)
    {
        $investors = $request->input('investors');
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $isExport = $request->input('is_export') == 'yes';
        $userId = Auth::user()->id;
        $rate = Settings::value('rate');
        $defaultPayRtrQuery = PaymentInvestors::select(DB::raw('SUM(participant_share-mgmnt_fee) as default_pay_rtr, payment_investors.user_id'))->join('merchants', 'merchants.id', '=', 'payment_investors.merchant_id')->whereIn('merchants.sub_status_id', [4, 22])->where('merchants.active_status', '=', 1)->groupBy('payment_investors.user_id');
        $underWritingFeeQuery = MerchantUser::select('merchant_user.user_id', DB::raw('SUM(under_writing_fee) as under_writing_fee'), DB::raw('SUM(( invest_rtr - invest_rtr * ((merchant_user.mgmnt_fee) / 100) - (invest_rtr * (0 / 100))) ) AS fees'))->join('merchants', 'merchants.id', '=', 'merchant_user.merchant_id')->whereIn('merchant_user.status', [1, 3])->whereNotIn('merchants.sub_status_id', [4, 22])->where('merchants.active_status', '=', 1)->groupBy('merchant_user.user_id');
        $ctdQuery = MerchantUser::select(DB::raw('SUM(
                    paid_participant_ishare - paid_mgmnt_fee 
                    - 
                    IF( paid_participant_ishare > invest_rtr,
				        (
				          paid_participant_ishare - invest_rtr
				        ) * (1- (merchant_user.mgmnt_fee) / 100),
                        0
                    )
                ) AS ctd, merchant_user.user_id'))->join('merchants', 'merchants.id', '=', 'merchant_user.merchant_id')->whereIn('merchant_user.status', [1, 3])->where('merchants.active_status', '=', 1)->groupBy('merchant_user.user_id');
        $userQuery = User::select('users.id', 'users.name', 'user_details.liquidity', 'bills_trans.bills', 'distributions_trans.distributions', 'credit_amount_trans.credit_amount', 'debit_amount_trans.debit_amount', 'default_pay_rtr_trans.default_pay_rtr', 'under_writing_fee_trans.under_writing_fee', 'under_writing_fee_trans.fees', 'ctd_trans.ctd')->join('user_has_roles', function ($join) {
            $join->on('users.id', '=', 'user_has_roles.model_id');
            $join->where('user_has_roles.role_id', 2);
        })
        ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
        ->leftJoin(DB::raw('(SELECT ABS(SUM(investor_transactions.amount)) as bills   , investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 and transaction_type=1 and investor_transactions.status=1 and transaction_category IN (10) GROUP BY investor_transactions.investor_id) as bills_trans'), 'bills_trans.investor_id', '=', 'users.id')
        ->leftJoin(DB::raw('(SELECT SUM(investor_transactions.amount) as distributions, investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 and transaction_type=1 and investor_transactions.status=1 and transaction_category IN (6,7) GROUP BY investor_transactions.investor_id) as distributions_trans'), 'distributions_trans.investor_id', '=', 'users.id')
        ->leftJoin(DB::raw('(SELECT SUM(investor_transactions.amount) as credit_amount, investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 and transaction_type=2 and investor_transactions.status=1 GROUP BY investor_transactions.investor_id) as credit_amount_trans'), 'credit_amount_trans.investor_id', '=', 'users.id')
        ->leftJoin(DB::raw('(SELECT SUM(investor_transactions.amount) AS debit_amount , investor_transactions.investor_id FROM investor_transactions WHERE investor_transactions.investor_id > 0 and transaction_type=1 and investor_transactions.status=1 GROUP BY investor_transactions.investor_id) as debit_amount_trans'), 'debit_amount_trans.investor_id', '=', 'users.id')
        ->leftJoin(DB::raw('('.modelQuerySql($defaultPayRtrQuery).') as default_pay_rtr_trans'), 'default_pay_rtr_trans.user_id', '=', 'users.id')
        ->leftJoin(DB::raw('('.modelQuerySql($underWritingFeeQuery).') as under_writing_fee_trans'), 'under_writing_fee_trans.user_id', '=', 'users.id')
        ->leftJoin(DB::raw('('.modelQuerySql($ctdQuery).') as ctd_trans'), 'ctd_trans.user_id', '=', 'users.id');
        if ($investors && is_array($investors)) {
            $userQuery->whereIn('users.id', $investors);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $userQuery->where('company', $userId);
            } else {
                $userQuery->where('creator_id', $userId);
            }
        }
        $data = $userQuery->get();
        $transactionQuery = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->groupBy('investor_id')->whereIn('transaction_category', [12, 13, 14]);
        if ($investors && is_array($investors)) {
            $transactionQuery->whereIn('users.id', $investors);
        }
        if (empty($permission)) {
            $transactionQuery->where('creator_id', $userId);
        }
        $investorTransactions = $transactionQuery->select(DB::raw('sum(amount) as amount'), 'investor_id')->pluck('amount', 'investor_id')->toArray();
        foreach ($data as $key => $value) {
            $return_amount = 0;
            $return_amount = isset($investorTransactions[$data[$key]->id]) ? $investorTransactions[$data[$key]->id] : 0;
            $data[$key]->credit_amount = ($data[$key]->credit_amount) - $return_amount;
        }
        $totalPortfolio = $totalPortfolioValue = $totalCreditAmount = $totalBills = $totalDistributions = $totalPortfolioEarning = $totalIRR = $liquidity = $total_amount = 0;
        $totalInvestors = collect($data)->pluck('id')->unique()->toArray();
        $portfolio_difference = FFM::total_portfolio_difference($totalInvestors);
        $liquidity = collect($data)->pluck('liquidity')->sum();
        $default_pay_rtr = collect($data)->pluck('default_pay_rtr')->sum();
        $totalCreditAmount = collect($data)->pluck('credit_amount')->sum();
        $totalDistributions = collect($data)->pluck('distributions')->sum();
        $credited_amount = collect($data)->pluck('credit_amount')->sum();
        $fees = collect($data)->pluck('fees')->sum();
        $ctd = collect($data)->pluck('ctd')->sum();
        $rtr = $fees + $default_pay_rtr;
        $totalPortfolioValue = ($rtr + $liquidity) - $ctd;
        $portfolio_earning = ($totalPortfolio - $totalBills - $totalDistributions);
        $totalPortfolioEarning = ($totalPortfolioValue + $totalBills - $totalDistributions);
        $totalIRR = ! empty($credited_amount) ? ((($portfolio_earning - $credited_amount) / ($credited_amount)) * 100) : 0;
        $datTable = \IPVueTable::of($data);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('name', function ($data) {
            return $data->name;
        })->addColumn('bills', function ($data) {
            return FFM::dollar($data->bills);
        })->addColumn('distributions', function ($data) {
            return FFM::dollar(0 - $data->distributions);
        })->addColumn('credit_amount', function ($data) {
            return FFM::dollar($data->credit_amount);
        })->addColumn('total_portfolio_earnings', function ($data) {
            $total_amount = 0;
            $fees = $data->fees;
            $rtr = $fees + $data->default_pay_rtr;
            $ctd = $data->ctd;
            $portfolio_value = (float) ($rtr + $data->liquidity) - $ctd;
            $portfolio_difference = FFM::portfolio_difference($data->id);
            $portfolio_earning = ((float) $portfolio_value + (float) $data->bills - (float) $data->distributions);

            return FFM::dollar($portfolio_earning);
        })->addColumn('portfolio_value', function ($data) {
            $total_amount = 0;
            $fees = $data->fees;
            $rtr = $fees + $data->default_pay_rtr;
            $ctd = $data->ctd;
            $portfolio_value = ($rtr + $data->liquidity) - $ctd;

            return FFM::dollar($portfolio_value);
        })->with('total_credit_amount', \FFM::dollar($totalCreditAmount))->with('total_portfolio', \FFM::dollar($totalPortfolioValue))->with('total_bills', \FFM::dollar($totalBills))->with('total_distributions', \FFM::dollar($totalDistributions))->with('total_portfolio_earning', \FFM::dollar($totalPortfolioEarning))->with('download-url', api_download_url('portfolio-earning-download'))->make(true);
    }
}
