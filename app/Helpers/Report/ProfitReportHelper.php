<?php

namespace App\Helpers\Report;

use App\Exports\Data_arrExport;
use function App\Helpers\api_download_url;
use App\Industries;
use App\Merchant;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PayCalc;

class ProfitReportHelper
{
    public static function getProfit2Columns()
    {
        return [['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name', 'orderable' => false], ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD', 'orderable' => false], ['data' => 'total_profit', 'name' => 'total_profit', 'title' => 'Total Profit', 'orderable' => false], ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills', 'orderable' => false], ['data' => 'interest', 'name' => 'interest', 'title' => 'Pref Return', 'orderable' => false], ['searchable' => false, 'data' => 'default', 'name' => 'default', 'title' => 'Default', 'orderable' => false], ['searchable' => false, 'data' => 'net_proft', 'name' => 'net_proft', 'title' => 'Net Profit', 'orderable' => false], ['searchable' => false, 'data' => 'velocity', 'name' => 'velocity', 'title' => '65% Velocity', 'orderable' => false], ['searchable' => false, 'data' => 'investor', 'name' => 'investor', 'title' => '20% To Investor', 'orderable' => false], ['searchable' => false, 'data' => 'pactulos', 'name' => 'pactulos', 'title' => '15% Pactolus', 'orderable' => false]];
    }

    public static function profit2ReportDownload(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getProfit2Columns(), self::getProfit2Report($request), time().'-'.'Profitability2');
    }

    public static function profit3ReportDownload(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getProfit3Columns(), self::getProfit3Report($request), time().'-'.'Profitability3');
    }

    public static function profit4ReportDownload(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getProfit4Columns(), self::getProfit4Report($request), time().'-'.'Profitability4');
    }

    public static function getProfit2Report(Request $request)
    {
        $merchantIds = $request->input('merchants', []);
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');
        $isFundedDate = $request->input('funded_date');
        $isExport = $request->input('is_export') == 'yes';
        $userId = Auth::user()->id;
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        if ($isFundedDate == 'true') {
            $filterDateField = 'merchants.date_funded';
        }
        $defaultMerchantIds = self::getDefaultMerchantIds($fromDate, $toDate);
        $defaultMerchantIds = implode(',', $defaultMerchantIds);
        $defaultMerchantIds = ($defaultMerchantIds == '') ? '0' : $defaultMerchantIds;
        list($paymentQuery, $investorTransactionQuery) = self::getInvestorQueryFilters($fromDate, $toDate);
        $profitQuery = User::where('users.id', '>', 0);
        $profitQuery = self::queryJoinProfitabilityReport($profitQuery, $fromDate, $toDate, $paymentQuery, $investorTransactionQuery, $defaultMerchantIds);
        $profitQuery->where('investor_type', 1)->groupBy('users.id')->orderby('users.id');
        $profitQuery->select('users.name as investor_name', 'users.id', DB::raw('
				ctd_investor.ctd, 
				total_profit_investor.total_profit, 
				bills_trans.bills, 
				profit_d_v_trans.profit_d_v, 
				profit_d_i_trans.profit_d_i, 
				profit_d_p_trans.profit_d_p, 
				ctd_default_merchant.ctd_default, 
				default_amnt_merchant.default_amnt,
                interest_investor.interest,
                user_overpayment.overpayment'));
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $profitQuery->where('company', $userId);
            }
        }
        $merchantQuery = 'SELECT  user_id FROM merchant_user  WHERE `merchant_user`.`status` IN (1, 3)';
        if (is_array($merchantIds) and count($merchantIds)) {
            $merchantQuery .= ' AND merchant_id.id in ('.implode(',', $merchantIds).')';
        }
        $profitQuery->whereRaw('users.id in ('.$merchantQuery.')');
        $profits = $profitQuery->get();
        $profitCollection = collect($profits);
        $totalOverPayment = $profitCollection->pluck('overpayment')->sum();
        $totalProfit = $profitCollection->pluck('total_profit')->sum();
        $totalBills = $profitCollection->pluck('bills')->sum();
        $totalInterest = $profitCollection->pluck('interest')->sum();
        $totalCTD = $profitCollection->pluck('ctd')->sum();
        $totalDefaultAmount = ($profitCollection->pluck('default_amnt')->sum() - $profitCollection->pluck('ctd_default')->sum() - $totalOverPayment);
        $totalNetProfit = $totalProfit - $totalInterest - $totalDefaultAmount - $totalBills;
        $total_65_velocity = $totalNetProfit * 65 / 100;
        $total_20_investor = $totalNetProfit * 20 / 100;
        $total_15_pactulos = $totalNetProfit * 15 / 100;
        $datTable = \IPVueTable::of($profits);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('ctd', function ($result) {
            return FFM::dollar($result->ctd);
        })->editColumn('investor_name', function ($result) {
            return "<a href='/admin/investors/portfolio/$result->id'>($result->investor_name)</a>";
        })->editColumn('bills', function ($result) {
            return FFM::dollar($result->bills);
        })->editColumn('interest', function ($result) {
            return FFM::dollar($result->interest);
        })->editColumn('total_profit', function ($result) {
            $profit = $result->total_profit;

            return FFM::dollar($profit);
        })->editColumn('net_proft', function ($result) {
            $overPayment = isset($result->overpayment) ? $result->overpayment : 0;
            $defaultAmount = $result->default_amnt - $result->ctd_default - $overPayment;
            $profit = $result->total_profit - $result->interest - $defaultAmount - $result->bills;
            if ($profit <= 0) {
                $profit = '0.00';
            } else {
                $profit = FFM::dollar($profit);
            }

            return $profit;
        })->editColumn('default', function ($result) {
            $overPayment = isset($result->overpayment) ? $result->overpayment : 0;

            return FFM::dollar($result->default_amnt - $result->ctd_default - $overPayment);
        })->editColumn('velocity', function ($result) {
            $overPayment = isset($result->overpayment) ? $result->overpayment : 0;
            $defaultAmount = $result->default_amnt - $result->ctd_default - $overPayment;
            $profit = $result->total_profit - $result->interest - $defaultAmount - $result->bills;
            $profit_d_v = $result->profit_d_v;
            if ($result->profit_d_v == '') {
                $profit_d_v = 0;
            }

            return FFM::dollar(($profit / 100 * 65) - $result->profit_d_v)."<br><font color='red'>   +$profit_d_v </color>";
        })->editColumn('investor', function ($result) {
            $overPayment = isset($result->overpayment) ? $result->overpayment : 0;
            $defaultAmount = $result->default_amnt - $result->ctd_default - $overPayment;
            $profit = $result->total_profit - $result->interest - $defaultAmount - $result->bills;
            $profit_d_i = $result->profit_d_i;
            if ($result->profit_d_i == '') {
                $profit_d_i = 0;
            }

            return FFM::dollar(($profit / 100 * 20) - $result->profit_d_i)."<br><font color='red'>   +$profit_d_i </color>";
        })->editColumn('pactulos', function ($result) {
            $overPayment = isset($result->overpayment) ? $result->overpayment : 0;
            $defaultAmount = $result->default_amnt - $result->ctd_default - $overPayment;
            $profit = $result->total_profit - $result->interest - $defaultAmount - $result->bills;
            $profit_d_p = $result->profit_d_p;
            if ($result->profit_d_p == '') {
                $profit_d_p = 0;
            }

            return FFM::dollar(($profit / 100 * 15) - $result->profit_d_p)."<br><font color='red'>   +$profit_d_p </color>";
        })->with('total_ctd', FFM::dollar($totalCTD))->with('total_profit_value', FFM::dollar($totalProfit))->with('total_bills', FFM::dollar($totalBills))->with('total_interest', FFM::dollar($totalInterest))->with('total_default', FFM::dollar($totalDefaultAmount))->with('total_net_profit', FFM::dollar($totalNetProfit))->with('total_65_velocity', FFM::dollar($total_65_velocity))->with('total_20_investor', FFM::dollar($total_20_investor))->with('total_15_pactulos', FFM::dollar($total_15_pactulos))->with('download-url', api_download_url('profitability2-download'))->make(true);
    }

    public static function getProfit3Columns()
    {
        return [['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name', 'orderable' => false], ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD'], ['data' => 'total_profit', 'name' => 'total_profit', 'title' => 'Total Profit'], ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'], ['data' => 'interest', 'name' => 'interest', 'title' => 'Pref Return'], ['searchable' => false, 'data' => 'default', 'name' => 'default', 'title' => 'Default'], ['searchable' => false, 'data' => 'net_proft', 'name' => 'net_proft', 'title' => 'net profit'], ['searchable' => false, 'data' => 'velocity', 'name' => 'velocity', 'title' => '50% Velocity'], ['searchable' => false, 'data' => 'investor', 'name' => 'investor', 'title' => '30% To Investor'], ['searchable' => false, 'data' => 'pactulos', 'name' => 'pactulos', 'title' => '20% Pactolus']];
    }

    public static function getProfit3Report(Request $request)
    {
        $merchantIds = $request->input('merchants', []);
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');
        $isFundedDate = $request->input('funded_date');
        $isExport = $request->input('is_export') == 'yes';
        $userId = Auth::user()->id;
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        list($paymentQuery, $investorTransQuery) = self::getInvestorQueryFilters($fromDate, $toDate);
        if ($isFundedDate == 'true') {
            $filterDateField = 'merchants.date_funded';
        }
        $defaultMerchantIds = self::getDefaultMerchantIds($fromDate, $toDate);
        $defaultMerchantIds = implode(',', $defaultMerchantIds);
        if ($defaultMerchantIds == '') {
            $defaultMerchantIds = '0';
        }
        $profitQuery = User::join('merchant_user', function ($join = '') {
            $join->on('merchant_user.user_id', 'users.id')->whereIn('merchant_user.status', [1, 3]);
        })->join('merchants', function ($join) {
            $join->on('merchants.id', '=', 'merchant_user.merchant_id');
        })->where('investor_type', 3)->groupBy('users.id')->orderby('users.id')->select('users.name as investor_name', 'users.id', DB::raw('
					ctd_investor.ctd, 
					total_profit_investor.total_profit, 
					bills_trans.bills, 
					profit_d_v_trans.profit_d_v, 
					profit_d_i_trans.profit_d_i, 
					profit_d_p_trans.profit_d_p, 
					ctd_default_merchant.ctd_default, 
                    user_overpayment.overpayment,
                    default_amnt_merchant.default_amnt,
                    interest_investor.interest'), 'merchant_user.merchant_id');
        $profitQuery = self::queryJoinProfitabilityReport($profitQuery, $fromDate, $toDate, $paymentQuery, $investorTransQuery, $defaultMerchantIds);
        $userId = Auth::user()->id;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if (empty($permission)) {
            $profitQuery->where('company', $userId);
        }
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $profitQuery->whereIn('merchants.id', $merchantIds);
        }
        $profits = $profitQuery->get();
        $profitCollection = collect($profits);
        $totalOverPayment = $profitCollection->pluck('overpayment')->sum();
        $totalProfit = $profitCollection->pluck('total_profit')->sum();
        $totalBills = $profitCollection->pluck('bills')->sum();
        $totalInterest = $profitCollection->pluck('interest')->sum();
        $totalCTD = $profitCollection->pluck('ctd')->sum();
        $totalDefaultAmount = ($profitCollection->pluck('default_amnt')->sum() - $profitCollection->pluck('ctd_default')->sum() - $totalOverPayment);
        $totalNetProfit = $totalProfit - $totalInterest - $totalDefaultAmount - $totalBills;
        $total_50_velocity = $totalNetProfit * 50 / 100;
        $total_30_investor = $totalNetProfit * 30 / 100;
        $total_20_pactulos = $totalNetProfit * 20 / 100;
        $datTable = \IPVueTable::of($profits);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('ctd', function ($result) {
            return FFM::dollar($result->ctd);
        })->editColumn('investor_name', function ($result) {
            return "<a href='/admin/investors/portfolio/$result->id'>($result->investor_name)</a>";
        })->editColumn('bills', function ($result) {
            return FFM::dollar($result->bills);
        })->editColumn('interest', function ($result) {
            return FFM::dollar($result->interest);
        })->editColumn('total_profit', function ($result) {
            $profit = $result->total_profit;

            return FFM::dollar($profit);
        })->editColumn('net_proft', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;
            $defaultAmount = $result->default_amnt - $result->ctd_default - $overPayment;
            $profit = $result->total_profit - $defaultAmount - $result->interest - $result->bills;
            if ($profit <= 0) {
                $profit = 0.00;
            } else {
                $profit = FFM::dollar($profit);
            }

            return $profit;
        })->editColumn('default', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;

            return FFM::dollar($result->default_amnt - $result->ctd_default - $overPayment);
        })->editColumn('velocity', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;
            $defaultAmount = $result->default_amnt - $result->ctd_default - $overPayment;
            $profit = $result->total_profit - $defaultAmount - $result->interest - $result->bills;
            $profit_d_v = $result->profit_d_v;
            if ($result->profit_d_v == '') {
                $profit_d_v = 0;
            }
            if ($profit / 100 * 30 - $result->profit_d_i > $result->interest) {
                $new_profit = $profit / 100 * 50;
            } else {
                $new_profit = $profit / 70 * 50;
            }

            return FFM::dollar($new_profit - $result->profit_d_v)."<br><font color='red'>   +$profit_d_v </color>";
        })->editColumn('investor', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;
            $defaultAmount = $result->default_amnt - $result->ctd_default - $overPayment;
            $profit = $result->total_profit - $defaultAmount - $result->interest - $result->bills;
            $profit_d_i = $result->profit_d_i;
            if ($result->profit_d_i == '') {
                $profit_d_i = 0;
            }
            if ($profit / 100 * 30 - $result->profit_d_i > $result->interest) {
                $new_profit = $profit / 100 * 30;
            } else {
                $new_profit = 0;
            }

            return FFM::dollar($new_profit - $result->profit_d_i)."<br><font color='red'>   +$profit_d_i </color>";
        })->editColumn('pactulos', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;
            $defaultAmount = $result->default_amnt - $result->ctd_default - $overPayment;
            $profit = $result->total_profit - $defaultAmount - $result->interest - $result->bills;
            $profit_d_p = $result->profit_d_p;
            if ($result->profit_d_p == '') {
                $profit_d_p = 0;
            }
            if ($profit / 100 * 30 - $result->profit_d_i > $result->interest) {
                $new_profit = $profit / 100 * 20;
            } else {
                $new_profit = $profit / 70 * 20;
            }

            return FFM::dollar($new_profit - $result->profit_d_p)."<br><font color='red'>   +$profit_d_p </color>";
        })->with('total_ctd', FFM::dollar($totalCTD))->with('total_profit_value', FFM::dollar($totalProfit))->with('total_bills', FFM::dollar($totalBills))->with('total_interest', FFM::dollar($totalInterest))->with('total_default', FFM::dollar($totalDefaultAmount))->with('total_net_profit', FFM::dollar($totalNetProfit))->with('total_50_velocity', FFM::dollar($total_50_velocity))->with('total_30_investor', '-'.FFM::dollar($total_30_investor))->with('total_20_pactulos', FFM::dollar($total_20_pactulos))->with('download-url', api_download_url('profitability3-download'))->make(true);
    }

    public static function getProfit4Columns()
    {
        return [['data' => 'id', 'name' => 'id', 'title' => 'Sl No', 'orderable' => false], ['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name', 'orderable' => false], ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD'], ['data' => 'total_profit', 'name' => 'total_profit', 'title' => 'Total Profit'], ['data' => 'bills', 'name' => 'bills', 'title' => 'Bills'], ['data' => 'interest', 'name' => 'interest', 'title' => 'Pref Return'], ['searchable' => false, 'data' => 'default', 'name' => 'default', 'title' => 'Default'], ['searchable' => false, 'data' => 'net_profit', 'name' => 'net_profit', 'title' => 'Net Profit'], ['searchable' => false, 'data' => 'velocity', 'name' => 'velocity', 'title' => '50% Velocity'], ['searchable' => false, 'data' => 'investor', 'name' => 'investor', 'title' => '50% To Investor']];
    }

    public static function getProfit4Report(Request $request)
    {
        $merchantIds = $request->input('merchants', []);
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');
        $isFundedDate = $request->input('funded_date');
        $isExport = $request->input('is_export') == 'yes';
        $userId = Auth::user()->id;
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $defaultMerchantIds = self::getDefaultMerchantIds($fromDate, $toDate);
        $defaultMerchantIds = implode(',', $defaultMerchantIds);
        list($paymentQuery, $investorTransQuery) = self::getInvestorQueryFilters($fromDate, $toDate);
        $profitQuery = User::join('merchant_user', function ($join) {
            $join->on('merchant_user.user_id', 'users.id')->whereIn('merchant_user.status', [1, 3]);
        })->join('merchants', function ($join) {
            $join->on('merchants.id', '=', 'merchant_user.merchant_id');
        })->where('investor_type', 2)->groupBy('users.id')->orderby('users.id', 'asc');
        $profitQuery->leftJoin(DB::raw("
					(
						SELECT SUM(merchant_user.amount + merchant_user.pre_paid + merchant_user.commission_amount  + merchant_user.under_writing_fee) AS invested_amount, 
							merchant_user.user_id
						FROM merchant_user 
						WHERE merchant_user.user_id > 0 
							AND merchant_id in ('.$defaultMerchantIds.') 
						GROUP BY merchant_user.user_id
					) as invested_amount_merchant"), 'invested_amount_merchant.user_id', '=', 'users.id');
        $profitQuery->leftJoin(DB::raw("
					(
						SELECT SUM(payment_investors.participant_share - payment_investors.mgmnt_fee) as ctd, 
							SUM(payment_investors.profit) as total_profit,
							SUM(payment_investors.overpayment) AS overpayment, 
							payment_investors.user_id 
						FROM payment_investors  
						LEFT JOIN participent_payments on payment_investors.participent_payment_id = participent_payments.id 
						WHERE payment_investors.user_id > 0 
							$paymentQuery 
						GROUP BY payment_investors.user_id
					) as payment_investor_sub"), 'payment_investor_sub.user_id', '=', 'users.id')->leftJoin(DB::raw("
					(
						SELECT ABS(SUM(investor_transactions.amount)) as bills, 
							investor_transactions.investor_id 
						FROM investor_transactions 
						WHERE investor_transactions.investor_id > 0 
							AND transaction_category = 10 AND investor_transactions.status = 1 $investorTransQuery 
						GROUP BY investor_transactions.investor_id) as bills_trans"), 'bills_trans.investor_id', '=', 'users.id')->leftJoin(DB::raw("
					(
						SELECT ABS(SUM(investor_transactions.amount)) as profit_d_v, 
							investor_transactions.investor_id 
						FROM investor_transactions 
						WHERE investor_transactions.investor_id > 0 
							AND transaction_category = 15 AND investor_transactions.status = 1
							$investorTransQuery 
						GROUP BY investor_transactions.investor_id 
					) as profit_d_v_trans"), 'profit_d_v_trans.investor_id', '=', 'users.id')->leftJoin(DB::raw("
					(
						SELECT ABS(SUM(investor_transactions.amount)) as profit_d_i, 
							investor_transactions.investor_id 
						FROM investor_transactions 
						WHERE investor_transactions.investor_id > 0 
							AND transaction_category = 16 AND investor_transactions.status = 1
							$investorTransQuery 
						GROUP BY investor_transactions.investor_id 
					) as profit_d_i_trans"), 'profit_d_i_trans.investor_id', '=', 'users.id')->leftJoin(DB::raw("
					(
						SELECT ABS(SUM(investor_transactions.amount)) as profit_d_p, 
							investor_transactions.investor_id 
						FROM investor_transactions 
						WHERE investor_transactions.investor_id > 0 
							AND transaction_category = 17 AND investor_transactions.status = 1
							$investorTransQuery 
						GROUP BY investor_transactions.investor_id 
					) as profit_d_p_trans"), 'profit_d_p_trans.investor_id', '=', 'users.id')->leftJoin(DB::raw('
					(
						SELECT SUM(merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee) as ctd_default, 
							merchant_user.user_id 
						FROM merchant_user 
						WHERE merchant_user.user_id > 0 
							AND merchant_id in ('.$defaultMerchantIds.')  
						GROUP BY merchant_user.user_id 
					) as ctd_default_merchant'), 'ctd_default_merchant.user_id', '=', 'users.id');
        $defaultDate = ! empty($toDate) ? $toDate : now();
        $merchantDayQuery = PayCalc::setDaysCalculation($defaultDate);
        $profitQuery->leftJoin(DB::raw('
					(
						SELECT 
							SUM('.$merchantDayQuery.' 
								* 
								( 
									(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) 
									- 
									IF( 
										(merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee), 
										(merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee),
										0
									)
								) 
							) as default_amount, 
							merchant_user.user_id 
						FROM merchant_user 
						INNER JOIN merchants ON merchants.id = merchant_user.merchant_id 
						WHERE merchant_user.user_id > 0 
							AND merchant_id in ('.$defaultMerchantIds.') 
						GROUP BY merchant_user.user_id 
					) as default_amnt_merchant'), 'default_amnt_merchant.user_id', '=', 'users.id');
        $profitQuery->leftJoin(DB::raw("
					(
						SELECT SUM( investor_transactions.amount *users.interest_rate / 100 / 365 
								* (
									TIMESTAMPDIFF(DAY, IF( 
											('$fromDate' >= investor_transactions.date),
											'$fromDate',
											investor_transactions.date
										),
										IF(
											(investor_transactions.date <=$toDate),
											'$toDate',
											CURDATE()  
										) 
									)  + 1 
								) 
							) as interest, 
							investor_transactions.investor_id  
						FROM investor_transactions 
                        INNER JOIN users ON users.id = investor_transactions.investor_id
                        WHERE investor_transactions.date <= IF($toDate,'$toDate',CURDATE()) 
                            AND investor_transactions.investor_id = users.id 
                            AND investor_transactions.transaction_type = 2 
							AND investor_transactions.status = 1
                        GROUP BY investor_transactions.investor_id 
                    ) as interest_investor"), 'interest_investor.investor_id', '=', 'users.id');
        $profitQuery->select('users.name as investor_name', 'users.id', DB::raw('
				payment_investor_sub.ctd, 
				payment_investor_sub.total_profit, 
				bills_trans.bills, 
				profit_d_v_trans.profit_d_v, 
				profit_d_i_trans.profit_d_i, 
				profit_d_p_trans.profit_d_p, 
				ctd_default_merchant.ctd_default, 
				default_amnt_merchant.default_amount, 
				interest_investor.interest, 
				payment_investor_sub.overpayment, 
				invested_amount_merchant.invested_amount'), 'merchant_user.merchant_id');
        if (empty($permission)) {
            $profitQuery->where('company', $userId);
        }
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $profitQuery->whereIn('merchants.id', $merchantIds);
        }
        $profits = $profitQuery->get();
        $profitCollection = collect($profits);
        $totalOverPayment = $profitCollection->pluck('overpayment')->sum();
        $totalProfit = $profitCollection->pluck('total_profit')->sum();
        $totalBills = $profitCollection->pluck('bills')->sum();
        $totalInterest = $profitCollection->pluck('interest')->sum();
        $totalCTD = $profitCollection->pluck('ctd')->sum();
        $totalDefaultAmount = $profitCollection->pluck('default_amount')->sum();
        $totalDefaultAmount = $totalDefaultAmount - $totalOverPayment;
        $totalNetProfit = $totalProfit - $totalInterest - $totalDefaultAmount - $totalBills;
        $total_50_velocity = $totalNetProfit * 50 / 100;
        $total_50_investor = $totalNetProfit * 50 / 100;
        $datTable = \IPVueTable::of($profits);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('ctd', function ($result) {
            return FFM::dollar($result->ctd);
        })->editColumn('investor_name', function ($result) {
            return "<a href='/admin/investors/portfolio/$result->id'>($result->investor_name)</a>";
        })->editColumn('bills', function ($result) {
            return FFM::dollar($result->bills);
        })->editColumn('interest', function ($result) {
            return FFM::dollar($result->interest);
        })->editColumn('total_profit', function ($result) {
            $profit = $result->total_profit;

            return FFM::dollar($profit);
        })->editColumn('net_profit', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;
            $defaultAmount = $result->default_amount - $overPayment;
            $profit = $result->total_profit - $result->interest - $defaultAmount - $result->bills;
            if ($profit <= 0) {
                $profit = 0.00;
            } else {
                $profit = FFM::dollar($profit);
            }

            return $profit;
        })->editColumn('default', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;

            return FFM::dollar($result->default_amount - $overPayment);
        })->editColumn('velocity', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;
            $defaultAmount = $result->default_amount - $overPayment;
            $profit = $result->total_profit - $result->interest - $defaultAmount - $result->bills;
            $profit_d_v = $result->profit_d_v;
            if ($result->profit_d_v == '') {
                $profit_d_v = 0;
            }

            return FFM::dollar(($profit / 100 * 50) - $result->profit_d_v)."<br><font color='red'>   +$profit_d_v </color>";
        })->editColumn('investor', function ($result) {
            $overPayment = $result->overpayment ? $result->overpayment : 0;
            $defaultAmount = $result->default_amount - $overPayment;
            $profit = $result->total_profit - $result->interest - $defaultAmount - $result->bills;
            $profit_d_i = $result->profit_d_i;
            if ($result->profit_d_i == '') {
                $profit_d_i = 0;
            }

            return FFM::dollar(($profit / 100 * 50) - $result->profit_d_i)."<br><font color='red'>   +$profit_d_i </color>";
        })->with('total_ctd', FFM::dollar($totalCTD))->with('total_profit_value', FFM::dollar($totalProfit))->with('total_bills', FFM::dollar($totalBills))->with('total_interest', FFM::dollar($totalInterest))->with('total_default', FFM::dollar($totalDefaultAmount))->with('total_net_profit', FFM::dollar($totalNetProfit))->with('total_50_velocity', FFM::dollar($total_50_velocity))->with('total_50_investor', FFM::dollar($total_50_investor))->with('download-url', api_download_url('profitability4-download'))->make(true);
    }

    public static function getInvestorQueryFilters($fromDate = '', $toDate = '')
    {
        if (! empty($fromDate) and ! empty($toDate)) {
            $paymentQuery = "AND participent_payments.payment_date>='$fromDate' AND participent_payments.payment_date <= '$toDate'";
            $investorTransQuery = "AND investor_transactions.date >= '$fromDate' AND investor_transactions.date <= '$toDate'";
        } elseif (! empty($fromDate)) {
            $paymentQuery = "AND participent_payments.payment_date >= '$fromDate'";
            $investorTransQuery = "AND investor_transactions.date >= '$fromDate'";
        } elseif (! empty($toDate)) {
            $paymentQuery = "AND participent_payments.payment_date <= '$toDate'";
            $investorTransQuery = "AND investor_transactions.date <= '$toDate'";
        } else {
            $paymentQuery = '';
            $investorTransQuery = '';
        }

        return [$paymentQuery, $investorTransQuery];
    }

    public static function getDefaultMerchantIds($fromDate = '', $toDate = ''):array
    {
        $filterDateField = 'merchants.last_status_updated_date';
        $defaultMerchantQuery = Merchant::whereIn('merchants.sub_status_id', [4, 22]);
        if (! empty($fromDate)) {
            $defaultMerchantQuery->whereDate($filterDateField, '>=', $fromDate);
        }
        if (! empty($toDate)) {
            $defaultMerchantQuery->whereDate($filterDateField, '<=', $toDate);
        }
        $defaultMerchantIds = $defaultMerchantQuery->pluck('id')->unique()->toArray();

        return $defaultMerchantIds;
    }

    public static function queryJoinProfitabilityReport($profitQuery, $fromDate, $toDate, $paymentQuery, $investorTransactionQuery, $defaultMerchantIds, $isReport4 = false)
    {
        $profitQuery->leftJoin(DB::raw("(SELECT SUM(payment_investors.participant_share - payment_investors.mgmnt_fee) as ctd, payment_investors.user_id FROM payment_investors  LEFT JOIN participent_payments on 
           payment_investors.participent_payment_id=participent_payments.id WHERE payment_investors.user_id > 0 $paymentQuery group by payment_investors.user_id) as ctd_investor"), 'ctd_investor.user_id', '=', 'users.id')->leftJoin(DB::raw("(
					SELECT 
						SUM(payment_investors.profit) as total_profit, 
						payment_investors.user_id FROM payment_investors  
					LEFT JOIN participent_payments on payment_investors.participent_payment_id = participent_payments.id 
					WHERE payment_investors.user_id > 0 
						$paymentQuery 
					GROUP BY payment_investors.user_id
				) as total_profit_investor"), 'total_profit_investor.user_id', '=', 'users.id')->leftJoin(DB::raw("
					(
						SELECT 
							ABS(SUM(investor_transactions.amount)) as bills, 
							investor_transactions.investor_id 
						FROM investor_transactions 
						WHERE 
							investor_transactions.investor_id > 0 
							AND transaction_category = 10 
							AND investor_transactions.status = 1
							$investorTransactionQuery 
						GROUP BY investor_transactions.investor_id
					) as bills_trans"), 'bills_trans.investor_id', '=', 'users.id')->leftJoin(DB::raw("
					( 
						SELECT 
							ABS(SUM(investor_transactions.amount)) as profit_d_v, 
							investor_transactions.investor_id 
						FROM investor_transactions 
						WHERE investor_transactions.investor_id > 0 
							AND transaction_category=15 
							AND investor_transactions.status = 1
							$investorTransactionQuery 
						GROUP BY investor_transactions.investor_id 
					) as profit_d_v_trans"), 'profit_d_v_trans.investor_id', '=', 'users.id')->leftJoin(DB::raw("
					(
						SELECT 
							SUM(payment_investors.overpayment) as overpayment, 
							payment_investors.user_id FROM payment_investors  
						LEFT JOIN participent_payments on payment_investors.participent_payment_id = participent_payments.id 
						WHERE 
							payment_investors.user_id > 0 
							$paymentQuery 
						GROUP BY payment_investors.user_id
					) as user_overpayment"), 'user_overpayment.user_id', '=', 'users.id')->leftJoin(DB::raw("
					(
						SELECT 
							ABS(SUM(investor_transactions.amount)) as profit_d_i, 
							investor_transactions.investor_id FROM investor_transactions 
						WHERE investor_transactions.investor_id > 0 
							AND transaction_category = 16 
							AND investor_transactions.status = 1
							$investorTransactionQuery 
						GROUP BY investor_transactions.investor_id 
					) as profit_d_i_trans"), 'profit_d_i_trans.investor_id', '=', 'users.id')->leftJoin(DB::raw("
					(
						SELECT 
							ABS(SUM(investor_transactions.amount)) as profit_d_p, 
							investor_transactions.investor_id 
						FROM investor_transactions 
						WHERE investor_transactions.investor_id > 0 
							AND transaction_category = 17 
							AND investor_transactions.status = 1
							$investorTransactionQuery 
						GROUP BY investor_transactions.investor_id 
					) as profit_d_p_trans"), 'profit_d_p_trans.investor_id', '=', 'users.id')->leftJoin(DB::raw('
					(
						SELECT 
							SUM(merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee) as ctd_default, 
							merchant_user.user_id FROM merchant_user 
						WHERE merchant_user.user_id > 0 
							AND merchant_id in ('.$defaultMerchantIds.')  
						GROUP BY merchant_user.user_id 
					) as ctd_default_merchant'), 'ctd_default_merchant.user_id', '=', 'users.id');
        if (! $isReport4) {
            $profitQuery->leftJoin(DB::raw('
					(
						SELECT SUM(merchant_user.amount + merchant_user.pre_paid + merchant_user.commission_amount  + merchant_user.under_writing_fee) as default_amnt, 
							merchant_user.user_id 
						FROM merchant_user 
						WHERE merchant_user.user_id > 0 
							AND merchant_id in ('.$defaultMerchantIds.') 
						GROUP BY merchant_user.user_id 
					) as default_amnt_merchant'), 'default_amnt_merchant.user_id', '=', 'users.id');
        } else {
            $default_date = ! empty($toDate) ? $toDate : now();
            $merchant_day = PayCalc::setDaysCalculation($default_date);
            $profitQuery->leftJoin(DB::raw('(
					SELECT 
						SUM('.$merchant_day.' 
							* 
							( 
								( merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) 
								- 
								IF( 
									(merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee), 
									(merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee),
									0
								) 
							) 
						) as default_amount, 
						merchant_user.user_id 
					FROM merchant_user 
					WHERE merchant_user.user_id > 0 
						AND merchant_id in ('.$defaultMerchantIds.') 
					GROUP BY merchant_user.user_id 
				) as default_amnt_merchant'), 'default_amnt_merchant.user_id', '=', 'users.id');
        }
        $profitQuery->leftJoin(DB::raw("
					( 
						SELECT 
							SUM(
								investor_transactions.amount * users.interest_rate / 100 / 365 
								* 
								( 
									TIMESTAMPDIFF(DAY, IF( 
											( '$fromDate' >= investor_transactions.date ),
											'$fromDate',
											investor_transactions.date
										),
										IF( 
											(investor_transactions.date <='$toDate'),
											'$toDate',
											CURDATE()  
										) 
									)  + 1 
								) 
							) as interest, 
							investor_transactions.investor_id  
						FROM investor_transactions 
                        LEFT JOIN users ON users.id = investor_transactions.investor_id
                        WHERE investor_transactions.date <= IF('$toDate','$toDate',CURDATE()) 
                            AND investor_transactions.investor_id = users.id 
                            AND investor_transactions.transaction_type = 2 
							AND investor_transactions.status = 1
                        GROUP BY investor_transactions.investor_id 
                    ) as interest_investor"), 'interest_investor.investor_id', '=', 'users.id');

        return $profitQuery;
    }

    public static function profitabilityReport2($request,$tableBuilder,$role)
    {
        $page_title = 'Profitability Report';
        $tableBuilder->ajax(['url' => route('admin::reports::profitability2-records'), 'type' => 'post', 'data' => 'function(data) {
                    data._token = "'.csrf_token().'";
                    data.merchants = $("#merchants").val();
                    data.from_date = $("#from_date").val();
                    data.to_date = $("#to_date").val();
                    data.funded_date = $("#funded_date").is(":checked");
                    data.investor_check = $("#all_investors").is(":checked");
                }']);
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'footerCallback' => 'function(t,o,a,l,m){var n=this.api();o=table.ajax.json();$(n.column(0).footer()).html(o.Total),$(n.column(2).footer()).html(o.total_ctd),$(n.column(3).footer()).html(o.total_profit_value),$(n.column(4).footer()).html(o.total_bills),$(n.column(5).footer()).html(o.total_interest),$(n.column(6).footer()).html(o.total_default),$(n.column(7).footer()).html(o.total_net_profit),$(n.column(8).footer()).html(o.total_65_velocity),$(n.column(9).footer()).html(o.total_20_investor),$(n.column(10).footer()).html(o.total_15_pactulos)}', 'pagingType' => 'input']);
        $tableBuilder->columns(\MTB::profitabilityReport2(null, null, null, null, null, true));
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $merchant = Merchant::where('active_status', 1);
        if (empty($permission)) {
            $merchant->whereHas('investmentData', function ($q) use ($subinvestors, $permission) {
                if (empty($permission)) {
                    $q->whereIn('user_id', $subinvestors);
                }
            });
        }
        $merchants = $merchant->pluck('name', 'id');
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();
        $lenders = $role->allLenders()->pluck('name', 'id');
        $industries = Industries::pluck('name', 'id');

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'investors' => $investors, 'lenders' => $lenders, 'merchants' => $merchants, 'industries' => $industries
        ];
    }

    public static function profitabilityReport3($request,$tableBuilder,$role)
    {
        $page_title = 'Profitability Report';
        $tableBuilder->ajax(['url' => route('admin::reports::profitability3'), 'type' => 'post', 'data' => 'function(data) {
		        data._token = "'.csrf_token().'";
		        data.merchants = $("#merchants").val();
		        data.from_date = $("#from_date").val();
		        data.to_date = $("#to_date").val();
		        data.funded_date = $("#funded_date").is(":checked");
	        }']);
        $fcb = 'function(t,o,a,l,m){var n=this.api();o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.total_ctd),$(n.column(3).footer()).html(o.total_profit_value),$(n.column(4).footer()).html(o.total_bills),$(n.column(5).footer()).html(o.total_interest),$(n.column(6).footer()).html(o.total_default),$(n.column(7).footer()).html(o.total_net_profit),$(n.column(8).footer()).html(o.total_50_velocity),$(n.column(9).footer()).html(o.total_30_investor),$(n.column(10).footer()).html(o.total_20_pactulos)}';
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'footerCallback' => $fcb, 'pagingType' => 'input']);
        $tableBuilder->columns(\MTB::profitabilityReport3(null, null, null, null, true));
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $merchant = Merchant::where('active_status', 1);
        if (empty($permission)) {
            $merchant->whereHas('investmentData', function ($q) use ($subinvestors, $permission) {
                if (empty($permission)) {
                    $q->whereIn('user_id', $subinvestors);
                }
            });
        }
        $merchants = $merchant->pluck('name', 'id');
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();
        $lenders = $role->allLenders()->pluck('name', 'id');
        $industries = Industries::pluck('name', 'id');

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'investors' => $investors, 'lenders' => $lenders, 'merchants' => $merchants, 'industries' => $industries
        ];
    }

    public static function profitabilityReport4($request,$tableBuilder,$role)
    {
        $page_title = 'Profitability Report(Equity)';
        $sDate = ! empty($request->from_date) ? $request->from_date : '';
        $eDate = ! empty($request->to_date) ? $request->to_date : '';

        $tableBuilder->ajax(['url' => route('admin::reports::profitability4-records'), 'type' => 'post', 'data' => 'function(data) {
            data._token = "'.csrf_token().'";
            data.merchants = $("#merchants").val();
            data.from_date = $("#from_date").val();
            data.to_date = $("#to_date").val();
            data.funded_date = $("#funded_date").is(":checked");
        }']);
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'footerCallback' => 'function(t,o,a,l,m){var n=this.api();o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.total_ctd),$(n.column(3).footer()).html(o.total_profit_value),$(n.column(4).footer()).html(o.total_bills),$(n.column(5).footer()).html(o.total_interest),$(n.column(6).footer()).html(o.total_default),$(n.column(7).footer()).html(o.total_net_profit),$(n.column(8).footer()).html(o.total_50_velocity),$(n.column(9).footer()).html(o.total_50_investor)}']);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n                 var info = this.dataTable().api().page.info();\n                 var page = info.page;\n                 var length = info.length;\n                 var index = (page * length + (iDataIndex + 1));\n                 $('td:eq(0)', nRow).html(index).addClass('txt-center');\n               }", 'pagingType' => 'input']);
        $tableBuilder->columns(\MTB::profitabilityReport4(null, null, null, null, true));
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $merchant = Merchant::where('active_status', 1);
        if (empty($permission)) {
            $merchant->whereHas('investmentData', function ($q) use ($subinvestors, $permission) {
                if (empty($permission)) {
                    $q->whereIn('user_id', $subinvestors);
                }
            });
        }
        $merchants = $merchant->pluck('name', 'id');
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();
        $lenders = $role->allLenders()->pluck('name', 'id');
        $industries = Industries::pluck('name', 'id');

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'investors' => $investors, 'lenders' => $lenders, 'merchants' => $merchants, 'industries' => $industries
        ];
    }

    public static function profitabilityReport21($request,$tableBuilder,$role)
    {
        $page_title = 'Profitability Report';
        $tableBuilder->ajax(['url' => route('admin::reports::profitability21'), 'type' => 'post', 'data' => 'function(data) {
            data._token = "'.csrf_token().'";
            data.merchants = $("#merchants").val();
            data.from_date = $("#from_date").val();
            data.to_date = $("#to_date").val();
            data.funded_date = $("#funded_date").is(":checked");
        }']);
        $fcb = 'function(t,o,a,l,m){var n=this.api();o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.total_ctd),$(n.column(3).footer()).html(o.total_profit_value),$(n.column(4).footer()).html(o.total_bills),$(n.column(5).footer()).html(o.total_default),$(n.column(6).footer()).html(o.total_net_profit),$(n.column(7).footer()).html(o.total_50_velocity),$(n.column(8).footer()).html(o.total_30_investor),$(n.column(9).footer()).html(o.total_20_pactulos)}';
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'footerCallback' => $fcb, 'pagingType' => 'input']);
        $tableBuilder->columns(\MTB::profitabilityReport21(null, null, null, null, true));
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $merchant = Merchant::where('active_status', 1);
        if (empty($permission)) {
            $merchant->whereHas('investmentData', function ($q) use ($subinvestors, $permission) {
                if (empty($permission)) {
                    $q->whereIn('user_id', $subinvestors);
                }
            });
        }
        $merchants = $merchant->pluck('name', 'id');
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();
        $lenders = $role->allLenders()->pluck('name', 'id');
        $industries = Industries::pluck('name', 'id');

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'investors' => $investors, 'lenders' => $lenders, 'merchants' => $merchants, 'industries' => $industries
        ];
    }

    public static function velocityProfitabilityReport($request,$tableBuilder,$role,$label)
    {
        $page_title = 'Velocity Profitability Report';

        $tableBuilder->ajax(['url' => route('admin::reports::velocity-profitability-records'), 'type' => 'post', 'data' => 'function(data) {
            data._token = "'.csrf_token().'";
        data.start_date = $("#date_start").val();
        data.end_date = $("#date_end").val();
        data.company = $("#company").val();
        data.label = $("#label").val();
        data.investors = $("#investors").val();

                }'])->parameters(['aaSorting' => []]);
        $tableBuilder->parameters(['serverSide' => false, 'fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }", 'footerCallback' => 'function(t,o,a,l,m){
            if(typeof table !== "undefined") {
                var n=this.api(),o=table.ajax.json();
                $(n.column(0).footer()).html(o.Total);
                $(n.column(2).footer()).html("Total : "+o.total_fees);
                $(n.column(3).footer()).html(o.total_origination_fee);
                $(n.column(4).footer()).html(o.total_up_sell_commission);
                $(n.column(5).footer()).html(o.total_underwriting_fee_flat);
                $(n.column(6).footer()).html(o.total_syndication_fee);
                $(n.column(7).footer()).html(o.total_management_fee_earned);
                $(n.column(8).footer()).html(o.total_underwriting_fee_earned);
                $(n.column(9).footer()).html(o.total_ach_fee);
            
                }

            }', 'pagingType' => 'input']);
        $tableBuilder->columns(\MTB::getVelocityProfitabilityReportDataTable(null, null, null, null, null, true));
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0] = 'All';
        $companies = array_reverse($companies, true);
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
                    $query->where('company_status',1);
                    })->pluck('users.name','users.id')->toArray();
        $lenders = $role->allLenders()->pluck('name', 'id');
        $label = $label->getAll()->pluck('name', 'id');
        $selected_investor = ($request->user_id) ? $request->user_id : '';

        return [
            'page_title' => $page_title, 'tableBuilder' => $tableBuilder, 'companies' => $companies, 'investors' => $investors, 'lenders' => $lenders, 'selected_investor' => $selected_investor, 'label' => $label
        ];
    }

    public static function profitability2Export($request,$merchant)
    {
        $fileName = 'Profitability Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $funded_date = false;
        if ($request->funded_date == 'on') {
            $funded_date = true;
        }
        $investor_check = false;
        if ($request->all_investors == 'on') {
            $investor_check = true;
        }
        $data = $merchant->getProfitabilityReport2(null, $request->from_date, $request->to_date, $funded_date, $investor_check);
        $id_arr = array_unique(array_column($data['data']->toArray(), 'id'));
        $pref_return_arr = $merchant->calculatePrefRetun($id_arr,$request->from_date, $request->to_date);
        $details = $data['data'];
        $excel_array[] = ['No', 'Investor Name', 'Ctd', 'Total Profit', 'Bills', 'Pref Return', 'Default', 'Net Profit', '65% Velocity', '20% To Investor', '15% Pactolus'];
        $i = 1;
        $total_65_velocity = $total_20_investor = $total_15_pactulos = $total_ctd = $total_profit = $total_bill = $total_pref_return = $total_default = $total_net_profit = $total_profit_d_v = $total_profit_d_i = $total_profit_d_p = 0;
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $over_p = isset($data->overpayment) ? $data->overpayment : 0;
                $default_amount = $data->default_amnt - $data->ctd_default - $over_p;
                $carry_profit = isset($data->carry_profit) ? $data->carry_profit : 0;
                $data->total_profit += $carry_profit;
                $pref_return = isset($pref_return_arr[$data->id]) ? $pref_return_arr[$data->id]: 0;
                //$profit = $data->total_profit - ($data->interest + $data->return_of_principal_interest) - $default_amount - $data->bills;
                $profit = $data->total_profit - $pref_return - $default_amount - $data->bills;
                $profit_d_v = $data->profit_d_v;
                if ($profit_d_v == '') {
                    $profit_d_v = 0;
                }
                $profit_d_i = $data->profit_d_i;
                if ($profit_d_i == '') {
                    $profit_d_i = 0;
                }
                $profit_d_p = $data->profit_d_p;
                if ($profit_d_p == '') {
                    $profit_d_p = 0;
                }
                $default_amnt = $data->default_amnt - $data->ctd_default - $over_p;
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Investor Name'] = $data->investor_name;
                $excel_array[$i]['Ctd'] = FFM::dollar($data->ctd);
                $excel_array[$i]['Total Profit'] = FFM::dollar($data->total_profit);
                $excel_array[$i]['Bills'] = FFM::dollar($data->bills);
                $excel_array[$i]['Pref Return'] = FFM::dollar($pref_return);
                $excel_array[$i]['Default'] = FFM::dollar($default_amnt);
                $excel_array[$i]['Net Profit'] = FFM::dollar(round($profit, 2));
                $excel_array[$i]['65% Velocity'] = FFM::dollar(($profit / 100 * 65) + $profit_d_v).'   +'.FFM::dollar(-$profit_d_v);
                $excel_array[$i]['20% To Investor'] = FFM::dollar(($profit / 100 * 20) + $profit_d_i).'   +'.FFM::dollar(-$profit_d_i);
                $excel_array[$i]['15% Pactolus'] = FFM::dollar(($profit / 100 * 15) + $profit_d_p).'   +'.FFM::dollar(-$profit_d_p);
                $total_65_velocity = $total_65_velocity + (($profit / 100 * 65) + $profit_d_v);
                $total_20_investor = $total_20_investor + (($profit / 100 * 20) + $profit_d_i);
                $total_15_pactulos = $total_15_pactulos + (($profit / 100 * 15) + $profit_d_p);
                $total_ctd = $total_ctd + $data->ctd;
                $total_profit = $total_profit + $data->total_profit;
                $total_bill = $total_bill + $data->bills;
                //$total_pref_return = $total_pref_return + $data->interest + $data->return_of_principal_interest;
                $total_pref_return = $total_pref_return + $pref_return;
                $total_default = $total_default + $default_amnt;
                $total_net_profit = $total_net_profit + $profit;
                $total_profit_d_v = $total_profit_d_v + $profit_d_v;
                $total_profit_d_i = $total_profit_d_i + $profit_d_i;
                $total_profit_d_p = $total_profit_d_p + $profit_d_p;
                $i++;
            }
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Investor Name'] = null;
            $excel_array[$i]['Ctd'] = FFM::dollar($total_ctd);
            $excel_array[$i]['Total Profit'] = FFM::dollar($total_profit);
            $excel_array[$i]['Bills'] = FFM::dollar($total_bill);
            $excel_array[$i]['Pref Return'] = FFM::dollar($total_pref_return);
            $excel_array[$i]['Default'] = FFM::dollar($total_default);
            $excel_array[$i]['Net Profit'] = FFM::dollar($total_net_profit);
            $excel_array[$i]['65% Velocity'] = FFM::dollar($total_65_velocity).'  +'.FFM::dollar(-$total_profit_d_v);
            $excel_array[$i]['20% To Investor'] = FFM::dollar($total_20_investor).'  +'.FFM::dollar(-$total_profit_d_i);
            $excel_array[$i]['15% Pactolus'] = FFM::dollar($total_15_pactulos).'  +'.FFM::dollar(-$total_profit_d_p);
        }
        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }

    public static function profitability3Export($request,$merchant)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        if (! $to_date) {
            $to_date = '2020-12-31';
        }
        if (! $from_date) {
            $from_date = '0000-00-00';
        }
        if ((strtotime($to_date) > strtotime('2020/12/31'))) {
            $to_date = '2020-12-31';
        }
        $fileName = 'Profitability Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $funded_date = false;
        if ($request->funded_date == 'on') {
            $funded_date = true;
        }
        $data = $merchant->getProfitabilityReport3(null, $from_date, $to_date, $funded_date);
        $id_arr = array_unique(array_column($data['data']->toArray(), 'id'));
        $pref_return_arr = $merchant->calculatePrefRetun($id_arr,$from_date, $to_date);
        $details = $data['data'];
        $excel_array[] = ['No', 'Investor Name', 'Ctd', 'Total Profit', 'Bills', 'Pref Return', 'Default', 'Net Profit', '50% Velocity', '30% To Investor', '20% Pactolus'];
        $i = 1;
        $total_50_velocity = $total_30_investor = $total_20_pactulos = $total_ctd = $total_profit = $total_bill = $total_pref_return = $total_default = $total_net_profit = $total_profit_d_v = $total_profit_d_i = $total_profit_d_p = 0;
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $interest = isset($pref_return_arr[$data->id]) ? $pref_return_arr[$data->id]: 0;
                $over_p = 0;
                $default_amount = $data->default_amnt - $data->ctd_default - $over_p;
                $carry_profit = isset($data->carry_profit) ? $data->carry_profit : 0;
                $data->total_profit += $carry_profit;
                $profit = $data->total_profit - $interest - $default_amount - $data->bills;
                $profit_d_v = $data->profit_d_v;
                if ($profit_d_v == '') {
                    $profit_d_v = 0;
                }
                $profit_d_i = $data->profit_d_i;
                if ($profit_d_i == '') {
                    $profit_d_i = 0;
                }
                $profit_d_p = $data->profit_d_p;
                if ($profit_d_p == '') {
                    $profit_d_p = 0;
                }
                $default_amnt = $data->default_amnt - $data->ctd_default - $over_p;
                //$interest = $data->interest + $data->return_of_principal_interest;
                if ($profit / 100 * 30 + $profit_d_v > $interest) {
                    $velocity = $profit / 100 * 50+ $data->profit_d_v;
                } else {
                    $velocity = $profit / 70 * 50+ $data->profit_d_v;
                }
                if ($profit / 100 * 30 + $profit_d_i > $interest) {
                    $to_investor = $profit / 100 * 30;
                } else {
                    $to_investor = 0;
                }
                if ($profit / 100 * 30 + $profit_d_p > $interest) {
                    $pactolus = ($profit / 100 * 20) + $data->profit_d_p;
                } else {
                    $pactolus = ($profit / 70 * 20) + $data->profit_d_p;
                }
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Investor Name'] = $data->investor_name;
                $excel_array[$i]['Ctd'] = FFM::dollar($data->ctd);
                $excel_array[$i]['Total Profit'] = FFM::dollar($data->total_profit);
                $excel_array[$i]['Bills'] = FFM::dollar($data->bills);
                $excel_array[$i]['Pref Return'] = FFM::dollar($interest);
                $excel_array[$i]['Default'] = FFM::dollar($default_amnt);
                $excel_array[$i]['Net Profit'] = FFM::dollar(round($profit, 2));
                $excel_array[$i]['50% Velocity'] = FFM::dollar($velocity).'   +'.FFM::dollar(-$profit_d_v);
                $excel_array[$i]['30% To Investor'] = ($to_investor > 0) ? FFM::dollar($to_investor).'   +'.FFM::dollar(-$profit_d_i) : '-';
                $excel_array[$i]['20% Pactolus'] = FFM::dollar($pactolus).'   +'.FFM::dollar(-$profit_d_p);
                $total_50_velocity = $total_50_velocity + $velocity;
                $total_30_investor = $total_30_investor + $to_investor;
                $total_20_pactulos = $total_20_pactulos + $pactolus;
                $total_ctd = $total_ctd + $data->ctd;
                $total_profit = $total_profit + $data->total_profit;
                $total_bill = $total_bill + $data->bills;
                $total_pref_return = $total_pref_return + $interest;
                $total_default = $total_default + $default_amnt;
                $total_net_profit = $total_net_profit + $profit;
                $total_profit_d_v = $total_profit_d_v + $profit_d_v;
                $total_profit_d_i = $total_profit_d_i + $profit_d_i;
                $total_profit_d_p = $total_profit_d_p + $profit_d_p;
                $i++;
            }
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Investor Name'] = null;
            $excel_array[$i]['Ctd'] = FFM::dollar($total_ctd);
            $excel_array[$i]['Total Profit'] = FFM::dollar($total_profit);
            $excel_array[$i]['Bills'] = FFM::dollar($total_bill);
            $excel_array[$i]['Pref Return'] = FFM::dollar($total_pref_return);
            $excel_array[$i]['Default'] = FFM::dollar($total_default);
            $excel_array[$i]['Net Profit'] = FFM::dollar($total_net_profit);
            $excel_array[$i]['50% Velocity'] = FFM::dollar($total_50_velocity).'  +'.FFM::dollar(-$total_profit_d_v);
            $excel_array[$i]['30% To Investor'] = FFM::dollar($total_30_investor).'  +'.FFM::dollar(-$total_profit_d_i);
            $excel_array[$i]['20% Pactolus'] = FFM::dollar($total_20_pactulos).'  +'.FFM::dollar(-$total_profit_d_p);
        }
        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }

    public static function profitability4Export($request,$merchant)
    {
        $fileName = 'Profitability Report Equity '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $funded_date = false;
        if ($request->funded_date == 'on') {
            $funded_date = true;
        }
        $to_date = ! empty($request->to_date) ? $request->to_date : date('Y-m-d', strtotime('+5 days'));
        $data = $merchant->getProfitabilityReport4(null, $request->from_date, $to_date, $funded_date);
        $id_arr = array_unique(array_column($data['data']->get()->toArray(), 'id'));
        $pref_return_arr = $merchant->calculatePrefRetun($id_arr,$request->from_date, $request->to_date);
        
        $details = $data['data']->get();
        $excel_array[] = ['No', 'Investor Name', 'Ctd', 'Total Profit', 'Bills', 'Pref Return', 'Default', 'Net Profit', '50% Velocity', '50% To Investor'];
        $i = 1;
        $total_50_velocity = $total_50_investor = $total_ctd = $total_profit = $total_bill = $total_pref_return = $total_default = $total_net_profit = $total_profit_d_v = $total_profit_d_i = $total_profit_d_p = 0;
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $interest = isset($pref_return_arr[$data->id]) ? $pref_return_arr[$data->id]: 0;
                $over_p = isset($data->overpayment) ? $data->overpayment : 0;
                $default_amount = $data->default_amnt - $data->ctd_default - $over_p;
                $carry_profit = isset($data->carry_profit) ? $data->carry_profit : 0;
                $data->total_profit += $carry_profit;
                $profit = $data->total_profit - ($interest) - $default_amount - $data->bills;
                $profit_d_v = $data->profit_d_v;
                if ($profit_d_v == '') {
                    $profit_d_v = 0;
                }
                $profit_d_i = $data->profit_d_i;
                if ($profit_d_i == '') {
                    $profit_d_i = 0;
                }
                $default_amnt = $data->default_amnt - $data->ctd_default - $over_p;
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Investor Name'] = $data->investor_name;
                $excel_array[$i]['Ctd'] = FFM::dollar($data->ctd);
                $excel_array[$i]['Total Profit'] = FFM::dollar($data->total_profit);
                $excel_array[$i]['Bills'] = FFM::dollar($data->bills);
                $excel_array[$i]['Pref Return'] = FFM::dollar($interest);
                $excel_array[$i]['Default'] = FFM::dollar($default_amnt);
                $excel_array[$i]['Net Profit'] = FFM::dollar($profit);
                $excel_array[$i]['50% Velocity'] = FFM::dollar(($profit / 100 * 50) + $profit_d_v).'   +'.FFM::dollar(-$profit_d_v);
                $excel_array[$i]['50% To Investor'] = FFM::dollar(($profit / 100 * 50) + $profit_d_i).'   +'.FFM::dollar(-$profit_d_i);
                $total_50_velocity = $total_50_velocity + (($profit / 100 * 50) + $profit_d_v);
                $total_50_investor = $total_50_investor + (($profit / 100 * 50) + $profit_d_i);
                $total_ctd = $total_ctd + $data->ctd;
                $total_profit = $total_profit + $data->total_profit;
                $total_bill = $total_bill + $data->bills;
                $total_pref_return = $total_pref_return + $interest;
                $total_default = $total_default + $default_amnt;
                $total_net_profit = $total_net_profit + $profit;
                $total_profit_d_v = $total_profit_d_v + $profit_d_v;
                $total_profit_d_i = $total_profit_d_i + $profit_d_i;
                $i++;
            }
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Investor Name'] = null;
            $excel_array[$i]['Ctd'] = FFM::dollar($total_ctd);
            $excel_array[$i]['Total Profit'] = FFM::dollar($total_profit);
            $excel_array[$i]['Bills'] = FFM::dollar($total_bill);
            $excel_array[$i]['Pref Return'] = FFM::dollar($total_pref_return);
            $excel_array[$i]['Default'] = FFM::dollar($total_default);
            $excel_array[$i]['Net Profit'] = FFM::dollar($total_net_profit);
            $excel_array[$i]['50% Velocity'] = FFM::dollar($total_50_velocity).'  +'.FFM::dollar(-$total_profit_d_v);
            $excel_array[$i]['50% To Investor'] = FFM::dollar($total_50_investor).'  +'.FFM::dollar(-$total_profit_d_i);
        }
        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }

    public static function profitability21Export($request,$merchant)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        if (! $to_date) {
            $to_date = date('Y-m-d', strtotime('+5 days'));
        }
        if (! $from_date) {
            $from_date = '2021-01-01';
        }
        if ((strtotime($from_date) < strtotime('2021/01/01'))) {
            $from_date = '2021-01-01';
        }
        $fileName = 'Profitability Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $funded_date = false;
        if ($request->funded_date == 'on') {
            $funded_date = true;
        }
        $data = $merchant->getProfitabilityReport21(null, $from_date, $to_date, $funded_date);
        $details = $data['data'];
        $excel_array[] = ['No', 'Investor Name', 'Ctd', 'Total Profit', 'Bills', 'Default', 'Net Profit', '50% Velocity', '30% To Investor', '20% Pactolus'];
        $i = 1;
        $total_50_velocity = $total_30_investor = $total_20_pactulos = $total_ctd = $total_profit = $total_bill = $total_pref_return = $total_default = $total_net_profit = $total_profit_d_v = $total_profit_d_i = $total_profit_d_p = 0;
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $over_p = isset($data->overpayment) ? $data->overpayment : 0;
                $default_amount = $data->default_amnt - $data->ctd_default - $over_p;
                $carry_profit = isset($data->carry_profit) ? $data->carry_profit : 0;
                $data->total_profit += $carry_profit;
                $profit = $data->total_profit - $default_amount - $data->bills;
                $profit_d_v = $data->profit_d_v;
                if ($profit_d_v == '') {
                    $profit_d_v = 0;
                }
                $profit_d_i = $data->profit_d_i;
                if ($profit_d_i == '') {
                    $profit_d_i = 0;
                }
                $profit_d_p = $data->profit_d_p;
                if ($profit_d_p == '') {
                    $profit_d_p = 0;
                }
                $default_amnt = $data->default_amnt - $data->ctd_default - $over_p;
                $interest = $data->interest + $data->return_of_principal_interest;
                $velocity = percentage(50, $data->total_profit - $data->bills - $default_amount);
                $to_investor = percentage(30, $data->total_profit - $data->bills - $default_amount);
                $pactolus = percentage(20, $data->total_profit - $data->bills - $default_amount);
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Investor Name'] = $data->investor_name;
                $excel_array[$i]['Ctd'] = FFM::dollar($data->ctd);
                $excel_array[$i]['Total Profit'] = FFM::dollar($data->total_profit);
                $excel_array[$i]['Bills'] = FFM::dollar($data->bills);
                $excel_array[$i]['Default'] = FFM::dollar($default_amnt);
                $excel_array[$i]['Net Profit'] = FFM::dollar($profit);
                $excel_array[$i]['50% Velocity'] = FFM::dollar($velocity);
                $excel_array[$i]['30% To Investor'] = FFM::dollar($to_investor);
                $excel_array[$i]['20% Pactolus'] = FFM::dollar($pactolus);
                $total_50_velocity = $total_50_velocity + $velocity;
                $total_30_investor = $total_30_investor + $to_investor;
                $total_20_pactulos = $total_20_pactulos + $pactolus;
                $total_ctd = $total_ctd + $data->ctd;
                $total_profit = $total_profit + $data->total_profit;
                $total_bill = $total_bill + $data->bills;
                $total_pref_return = $total_pref_return + $data->interest + $data->return_of_principal_interest;
                $total_default = $total_default + $default_amnt;
                $total_net_profit = $total_net_profit + $profit;
                $total_profit_d_v = $total_profit_d_v + $profit_d_v;
                $total_profit_d_i = $total_profit_d_i + $profit_d_i;
                $total_profit_d_p = $total_profit_d_p + $profit_d_p;
                $i++;
            }
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Investor Name'] = null;
            $excel_array[$i]['Ctd'] = FFM::dollar($total_ctd);
            $excel_array[$i]['Total Profit'] = FFM::dollar($total_profit);
            $excel_array[$i]['Bills'] = FFM::dollar($total_bill);
            $excel_array[$i]['Default'] = FFM::dollar($total_default);
            $excel_array[$i]['Net Profit'] = FFM::dollar($total_net_profit);
            $excel_array[$i]['50% Velocity'] = FFM::dollar($total_50_velocity);
            $excel_array[$i]['30% To Investor'] = FFM::dollar($total_30_investor);
            $excel_array[$i]['20% Pactolus'] = FFM::dollar($total_20_pactulos);
        }
        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }
    public static function velocityProfitabilityDownload($request)
    {
        $fileName = 'Velocity Profitability Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $details = \MTB::getVelocityProfitabilityReport($request->date_start, $request->date_end, $request->company, $request->investors, $request->label);
        $mgmnt_fee = $details['mgmnt_fee'];
        $ach_fees = $details['ach_fees'];
        $result = $details['data']->get()->toArray();
        $total_origination_fees = 0;
        $total_up_sell_commissions = 0;
        $total_flat_under_writing_fees = 0;
        $total_syndication_fees = 0;
        $total_underwriting_fees = 0;
        if (! empty($details)) {
            foreach ($result as $key => $value) {
                $total_origination_fees = $total_origination_fees + $value['origination_fee'];
                $total_up_sell_commissions = $total_up_sell_commissions + $value['up_sell_commission'];
                $total_flat_under_writing_fees = $total_flat_under_writing_fees + $value['underwriting_fee_flat'];
                $total_syndication_fees = $total_syndication_fees + $value['syndication_fee'];
                $total_underwriting_fees = $total_underwriting_fees + $value['underwriting_fee_earned'];
            }
        }
        $total_origination_fees = $total_origination_fees;
        $total_up_sell_commissions = $total_up_sell_commissions;
        $total_flat_under_writing_fees = $total_flat_under_writing_fees;
        $total_syndication_fees = $total_syndication_fees;
        $total_underwriting_fees = $total_underwriting_fees;
        $total_mgmnt_fee = 0;
        $total_ach_fee = 0;
        $excel_array[] = ['No', 'Merchant', 'Funded Date', 'Origination Fee', 'Up Sell Commission', 'Flat Under Writing Fee', 'Syndication Fee', 'Management Fee', 'Under Writing Fee', 'Total ACH Fees'];
        $i = 1;
        if (! empty($details)) {
            foreach ($result as $key => $value) {
                $management_fee = isset($mgmnt_fee[$value['id']]) ? $mgmnt_fee[$value['id']] : 0;
                $ach_fee = isset($ach_fees[$value['id']]) ? $ach_fees[$value['id']] : 0;
                if ($value['within_funded_date'] == 'no' && ! isset($mgmnt_fee[$value['id']])) {
                    continue;
                }
                $total_mgmnt_fee = $total_mgmnt_fee + $management_fee;
                $total_ach_fee = $total_ach_fee + $ach_fee;
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Merchant'] = $value['merchant_name'];
                $excel_array[$i]['Funded Date'] = FFM::date($value['date_funded']);
                $excel_array[$i]['Origination Fee'] = FFM::dollar($value['origination_fee']);
                $excel_array[$i]['Up Sell Commission'] = FFM::dollar($value['up_sell_commission']);
                $excel_array[$i]['Flat Under Writing Fee'] = FFM::dollar($value['underwriting_fee_flat']);
                $excel_array[$i]['Syndication Fee'] = FFM::dollar($value['syndication_fee']);
                $excel_array[$i]['Management Fee'] = FFM::dollar($management_fee);
                $excel_array[$i]['Under Writing Fee'] = FFM::dollar($value['underwriting_fee_earned']);
                $excel_array[$i]['Total ACH Fees'] = FFM::dollar($ach_fee);
                
                $i++;
            }
            $total_sum=$total_origination_fees + $total_up_sell_commissions + $total_flat_under_writing_fees + $total_syndication_fees + $total_underwriting_fees+ $total_mgmnt_fee + $total_ach_fee;
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Merchant'] = 'TOTAL';
            $excel_array[$i]['Funded Date']='TOTAL:'.FFM::dollar($total_sum);
            $excel_array[$i]['Origination Fee'] = FFM::dollar($total_origination_fees);
            $excel_array[$i]['Up Sell Commission'] = FFM::dollar($total_up_sell_commissions);
            $excel_array[$i]['Flat Under Writing Fee'] = FFM::dollar($total_flat_under_writing_fees);
            $excel_array[$i]['Syndication Fee'] = FFM::dollar($total_syndication_fees);
            $excel_array[$i]['Management Fee'] = FFM::dollar($total_mgmnt_fee);
            $excel_array[$i]['Under Writing Fee'] = FFM::dollar($total_underwriting_fees);
            $excel_array[$i]['Total ACH Fees'] = FFM::dollar($total_ach_fee);
        }
        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }
    public static function profitCarryForwardReport($request, $tableBuilder)
    {
        $selected_investor = ($request->user_id) ? $request->user_id : '';
        $sdate = ($request->sdate) ? $request->sdate : '';
        $edate = ($request->edate) ? $request->edate : '';

        $page_title = 'Profit Carry Forward';
        $tableBuilder->ajax(['url' => route('admin::get-profit-carryforwards-data'), 'type' => 'post', 'data' => 'function(data) {
            data._token = "'.csrf_token().'";
            data.start_date = $("#date_start").val();
            data.end_date = $("#date_end").val();
            data.investors = $("#investors").val();
            data.merchants = $("#merchants").val();
            data.type = $("#type").val();
        }']);
        $tableBuilder->columns(\MTB::profitCarryForwardReport(null, null, null, null, true, null));
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){if(typeof table !== "undefined") {  var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(3).footer()).html(o.amount)}}', 'drawCallback' => "function(){ if(typeof popover == 'function') {  $('[data-toggle=\"popover\"]').popover();}}", 'pagingType' => 'input', 'pageLength' => 100]);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n             var info = this.dataTable().api().page.info();\n             var page = info.page;\n             var length = info.length;\n             var index = (page * length + (iDataIndex + 1));\n             $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }"]);
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();
        return [
            'tableBuilder' => $tableBuilder,
            'page_title' => $page_title,
            'investors' => $investors,
            'selected_investor' => $selected_investor,
            'sdate' => $sdate,
            'edate' => $edate
        ];
    }
}
