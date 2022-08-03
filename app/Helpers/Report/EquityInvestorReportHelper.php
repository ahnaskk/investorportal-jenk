<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use App\Settings;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquityInvestorReportHelper
{
    public static function getColumns()
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'No', 'orderable' => false],
            ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
            ['data' => 'credits', 'name' => 'credits', 'title' => 'Credit'],
            ['data' => 'portfolio_value', 'name' => 'portfolio_value', 'title' => 'Portfolio Value'],
            ['data' => 'velocity_profit', 'name' => 'velocity_profit', 'title' => 'Velocity Profit'],
            ['data' => 'investor_profit', 'name' => 'investor_profit', 'title' => 'Investor Profit'],
        ];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Equity Investor');
    }

    public static function getReport(Request $request)
    {
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $investorIds = $request->input('investors');
        $isExport = $request->input('is_export') == 'yes';
        $userId = Auth::user()->id;
        $rate = Settings::value('rate');
        $userQuery = User::select('users.id', 'users.name', DB::raw('(SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity'), DB::raw('(SELECT SUM(investor_transactions.amount) FROM investor_transactions WHERE users.id = investor_transactions.investor_id and transaction_category=1 and investor_transactions.status=1 ) credit_amount'))->join('user_has_roles', function ($join) {
            $join->on('users.id', '=', 'user_has_roles.model_id');
            $join->where('user_has_roles.role_id', 2);
        })->withCount(['investmentData1 AS ctd' => function ($query) {
            $query->select(DB::raw('
							SUM(paid_participant_ishare - paid_mgmnt_fee)
							+
							2 * SUM( 
								IF( paid_participant_ishare>invest_rtr,
									( 
										invest_rtr - ( paid_participant_ishare ) 
									) 
									* 
									( 
										1 - ( merchant_user.mgmnt_fee ) / 100 
									),
									0
								) 
							)
                        as ctd'));
            $query->whereHas('merchant', function ($query1) {
                $query1->where('active_status', '=', 1);
            });
        }])->withCount(['investmentData2 AS fees' => function ($query) use ($rate) {
            $query->select(DB::raw("SUM(
							( invest_rtr - ( invest_rtr * ( $rate / 100 ) ) )
							- 
							invest_rtr * ( merchant_user.mgmnt_fee  / 100 ) 
                       ) as fees"));
            $query->where('status', 1);
            $query->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
            $query->where('merchants.active_status', '=', 1);
            $query->whereNotIn('merchants.sub_status_id', [4, 22]);
        }, 'investmentData2 AS tinvest_rtr'        => function ($query) {
            $query->select(DB::raw('SUM(invest_rtr) as tinvest_rtr'));
            $query->where('status', 1);
            $query->whereHas('merchant', function ($query1) {
                $query1->where('active_status', '=', 1);
                $query1->whereNotIn('sub_status_id', [4, 22]);
            });
        }])->withCount(['participantPayment AS default_pay_rtr' => function ($query) {
            $query->select(DB::raw('SUM(participant_share - mgmnt_fee) as default_pay_rtr'));
            $query->whereHas('merchant', function ($query1) {
                $query1->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1);
            });
        }]);
        if ($investorIds && is_array($investorIds)) {
            $userQuery->whereIn('users.id', $investorIds);
        }
        if (empty($permission)) {
            $userQuery->where('creator_id', $userId);
        }
        $userQuery->where('users.investor_type', 2);
        $total_credits = $totalVelocityProfit = $totalCreditAmount = $totalInvestorProfit = $totalPortfolioValue = 0;
        $users = $userQuery->get();
        $newArray = [];
        foreach ($users as $key => $user) {
            $liquidity = $user->userDetails->liquidity;
            $newArray[$key]['id'] = $user->id;
            $newArray[$key]['name'] = $user->name;
            $credited_amount = $user->investorTransactions()->sum('amount');
            $default_pay_rtr1 = 0;
            $mgmnt_fee = $user->merchantUser()->sum('mgmnt_fee');
            $syndication_fee = $user->merchantUser()->sum('syndication_fee');
            $invest_rtr = $user->merchantUser()->sum('invest_rtr');
            $total_mgt_fee = $user->merchantUser()->sum('paid_mgmnt_fee');
            $total_participant_share = $user->merchantUser()->sum('paid_participant_ishare');
            $total_fees = $mgmnt_fee + $syndication_fee;
            $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
            $total_rtr = $total_invest_rtr - $total_fees + $default_pay_rtr1;
            $total_ctd = $total_participant_share - $total_mgt_fee;
            $totalCreditAmount = $totalCreditAmount + $credited_amount;
            $investor_profit = ($total_rtr - $total_ctd + $liquidity - $credited_amount) / 2;
            $total_profit = ($investor_profit > 0) ? $investor_profit * 0.5 : 0;
            $portfolio_value = ($total_rtr + $liquidity) - $total_ctd;
            $velocity_profit = (($total_rtr - $total_ctd + $liquidity) - $credited_amount) * .5;
            $totalPortfolioValue = $totalPortfolioValue + $portfolio_value;
            $totalVelocityProfit = $totalVelocityProfit + $velocity_profit;
            $totalInvestorProfit = $totalInvestorProfit + $velocity_profit;
        }
        $datTable = \IPVueTable::of($users);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('name', function ($data) {
            return $data->name;
        })->addColumn('credits', function ($data) {
            $credited_amount = array_sum(array_column($data->investorTransactions->toArray(), 'amount'));

            return FFM::dollar($credited_amount);
        })->addColumn('portfolio_value', function ($data) use ($rate) {
            $rtr = $ctd = $total_amount = $fees = 0;
            $liquidity = $data->userDetails->liquidity;
            $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
            $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
            $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
            $fees = $mgmnt_fee + $syndication_fee;
            $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
            $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
            $total_amount = $total_amount + $total_invest_rtr;
            $rtr = $total_amount - $fees + $default_pay_rtr;
            $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
            $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
            $ctd = $participant_share - $mgt_fee;
            $portfolio_value = ($rtr + $liquidity) - $ctd;

            return FFM::dollar($portfolio_value);
        })->addColumn('velocity_profit', function ($data) use ($rate) {
            $rtr = $ctd = $velocity_profit = $total_amount = $fees = 0;
            $credited_amount = array_sum(array_column($data->investorTransactions->toArray(), 'amount'));
            $liquidity = $data->userDetails->liquidity;
            $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
            $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
            $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
            $fees = $mgmnt_fee + $syndication_fee;
            $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
            $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
            $total_amount = $total_amount + $total_invest_rtr;
            $rtr = $total_amount - $fees + $default_pay_rtr;
            $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
            $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
            $ctd = $participant_share - $mgt_fee;
            $velocity_profit = (($rtr - $ctd + $liquidity) - $credited_amount) * .5;

            return FFM::dollar($velocity_profit);
        })->addColumn('investor_profit', function ($data) use ($rate) {
            $rtr = $ctd = $investor_profit = $total_amount = $fees = $credited_amount = 0;
            $credited_amount = $credited_amount + array_sum(array_column($data->investorTransactions->toArray(), 'amount'));
            $liquidity = $data->userDetails->liquidity;
            $default_pay_rtr = array_sum(array_column($data->participantPayment->toArray(), 'final_participant_share'));
            $mgmnt_fee = array_sum(array_column($data->investmentData2->toArray(), 'mgmnt_fee'));
            $syndication_fee = array_sum(array_column($data->investmentData2->toArray(), 'syndication_fee'));
            $fees = $mgmnt_fee + $syndication_fee;
            $invest_rtr = array_sum(array_column($data->investmentData2->toArray(), 'invest_rtr'));
            $total_invest_rtr = $invest_rtr - ($invest_rtr * ($rate / 100));
            $total_amount = $total_amount + $total_invest_rtr;
            $rtr = $total_amount - $fees + $default_pay_rtr;
            $mgt_fee = array_sum(array_column($data->investmentData1->toArray(), 'paid_mgmnt_fee'));
            $participant_share = array_sum(array_column($data->investmentData1->toArray(), 'paid_participant_ishare'));
            $ctd = $participant_share - $mgt_fee;
            $velocity_profit = (($rtr - $ctd + $liquidity) - $credited_amount) * .5;

            return FFM::dollar($velocity_profit);
        })->with('total_credit_amount', \FFM::dollar($totalCreditAmount))->with('total_portfolio_value', \FFM::dollar($totalPortfolioValue))->with('total_velocity_profit', \FFM::dollar($totalVelocityProfit))->with('total_investor_porfit', \FFM::dollar($totalInvestorProfit))->with('download-url', api_download_url('equity-investor-download'))->make(true);
    }
}
