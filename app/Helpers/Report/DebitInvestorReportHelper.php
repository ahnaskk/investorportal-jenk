<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use App\Settings;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebitInvestorReportHelper
{
    public static function getColumns()
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'No', 'orderable' => false],
            ['data' => 'name', 'name' => 'name', 'title' => 'Investor'],
            ['data' => 'credited_amount', 'name' => 'credited_amount', 'title' => 'Principal Investment'],
            ['data' => '3monthinterest', 'name' => '3monthinterest', 'title' => '3 month Interest'],
            ['data' => 'princ_interest', 'name' => 'princ_interest', 'title' => 'Principal + Interest'],
            ['data' => 'current_balance', 'name' => 'current_balance', 'title' => 'Current Balance'],
        ];
    }

    public static function reportDownload(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Debit Investor Report');
    }

    public static function getReport(Request $request)
    {
        $investorIds = $request->input('investors');
        $isExport = $request->input('is_export') == 'yes';
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        $rate = Settings::value('rate');
        $userQuery = User::where('investor_type', 1)->where('company', 1)->select('users.id', 'users.name', 'users.interest_rate', DB::raw('(SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity'), DB::raw('(SELECT SUM(investor_transactions.amount) FROM investor_transactions WHERE users.id = investor_transactions.investor_id and transaction_type=2 and investor_transactions.status=1 and transaction_category NOT IN (12,13,14) ) credit_amount'))
                           ->where('users.active_status', 1)->join('user_has_roles', function ($join) {
                               $join->on('users.id', '=', 'user_has_roles.model_id');
                               $join->where('user_has_roles.role_id', 2);
                           })->withCount(['participantPayment AS default_pay_rtr' => function ($query) {
                               $query->select(DB::raw('SUM(participant_share-mgmnt_fee) as default_pay_rtr'));
                               $query->whereHas('merchant', function ($query1) {
                                   $query1->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1);
                               });
                           }])->withCount(['investmentData1 AS ctd' => function ($query) {
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
                               $query->select(DB::raw("
							SUM(
								( 
									( invest_rtr - invest_rtr * (
										(
											IF( s_prepaid_status = 0, 0, 0)
                                            + merchant_user.mgmnt_fee
                                        )
                                        /
                                        100
                                        ) 
                                    )
                                    -
                                    ( invest_rtr * ( $rate / 100) )   
                                )
                            ) as fees"));
                               $query->where('status', 1);
                               $query->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
                               $query->where('active_status', '=', 1);
                               $query->whereNotIn('sub_status_id', [4, 22]);
                           }]);
        if ($investorIds && is_array($investorIds)) {
            $userQuery->whereIn('users.id', $investorIds);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $userQuery->where('company', $userId);
            } else {
                $userQuery->where('creator_id', $userId);
            }
        }
        $datTable = \IPVueTable::of($userQuery);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('name', function ($data) {
            return "<a href=/admin/investors/portfolio/$data->id>".$data->name.'</a>';
        })->addColumn('3monthinterest', function ($data) {
            $creditWithInterest = (($data->credit_amount * $data->interest_rate / 100) / 365 * 90);

            return FFM::dollar($creditWithInterest);
        })->addColumn('credited_amount', function ($data) {
            return FFM::dollar($data->credit_amount);
        })->editColumn('princ_interest', function ($data) {
            $princ_interest = ($data->credit_amount + ($data->credit_amount * $data->interest_rate / 100) / 365 * 90);

            return FFM::dollar($princ_interest);
        })->editColumn('current_balance', function ($data) {
            $total_amount = 0;
            $princ_interest = ($data->credit_amount + ($data->credit_amount * $data->interest_rate / 100) / 365 * 90);
            $fees = $data->fees;
            $total_rtr = FFM::adjustment($fees + $data->default_pay_rtr, $data->id);
            $total_ctd = ($data->ctd);
            $current_balance = ($total_rtr + $data->liquidity - $total_ctd);
            $color = $princ_interest > $current_balance ? 'red' : 'green';

            return '<font color='.$color.'>'.FFM::dollar($current_balance).'</font>';
        })->with('download-url', api_download_url('debit-investor-download'))->make(true);
    }
}
