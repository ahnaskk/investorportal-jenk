<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use function App\Helpers\modelQuerySql;
use App\Settings;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class LiquidityReportHelper
{
    public static function getColumns()
    {
        return [['orderable' => false, 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => 'No'], ['data' => 'investor_name', 'name' => 'investor_name', 'defaultContent' => '', 'title' => 'Investor', 'orderable' => false], ['data' => 'ctd', 'name' => 'ctd', 'title' => 'Ctd', 'orderable' => false], ['data' => 'credits', 'name' => 'credits', 'title' => 'Credits', 'orderable' => false], ['data' => 'commission_amount', 'name' => 'commission_amount', 'title' => 'Commission', 'orderable' => false], ['data' => 'total_funded', 'name' => 'total_funded', 'title' => 'Funded Amount', 'orderable' => false], ['data' => 'pre_paid_amount', 'name' => 'pre_paid_amount', 'title' => 'Prepaid Amount', 'orderable' => false], ['data' => 'liquidity', 'name' => 'liquidity', 'title' => 'Liquidity', 'orderable' => false], ['data' => 'under_writing_fee', 'name' => 'under_writing_fee', 'title' => 'Underwriting Fee', 'orderable' => false]];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Liquidity Report');
    }

    public static function getReport(Request $request)
    {
        $userId = Auth::user()->id;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $isExport = $request->input('is_export') == 'yes';
        $investors = self::getInvestorWithLiquidity();
        $investorsCollection = $investors;
        $totalCTD = $investorsCollection->pluck('ctd')->sum();
        $totalCredit = $investorsCollection->pluck('credit_amount')->sum();
        $totalCommission = $investorsCollection->pluck('commission_amount')->sum();
        $totalFundedAmount = $investorsCollection->pluck('total_funded')->sum();
        $totalPrepaid = $investorsCollection->pluck('pre_paid')->sum();
        $totalUnderwritingFee = $investorsCollection->pluck('under_writing_fee')->sum();
        $totalLiquidity = ($totalCredit + $totalCTD) - ($totalFundedAmount + $totalCommission) - $totalPrepaid - $totalUnderwritingFee;
        $datTable = \IPVueTable::of($investors);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('investor_name', function ($report) {
            $report->name;
        })->addColumn('liquidity', function ($report) {
            $liquidity = ($report->credit_amount + $report->ctd) - ($report->total_funded + $report->commission_amount) - $report->pre_paid - $report->under_writing_fee;

            return FFM::dollar($liquidity);
        })->addColumn('ctd', function ($report) use ($startDate, $endDate) {
            return FFM::dollar($report->ctd);
        })->addColumn('credits', function ($report) use ($startDate, $endDate) {
            return FFM::dollar($report->credit_amount);
        })->addColumn('commission_amount', function ($report) use ($startDate, $endDate) {
            return FFM::dollar($report->commission_amount);
        })->addColumn('total_funded', function ($report) use ($startDate, $endDate) {
            return FFM::dollar($report['total_funded']);
        })->addColumn('pre_paid_amount', function ($report) {
            return FFM::dollar($report->pre_paid);
        })->addColumn('under_writing_fee', function ($report) {
            return FFM::dollar($report->under_writing_fee);
        })->with('total_ctd', \FFM::dollar($totalCTD))->with('total_credit', \FFM::dollar($totalCredit))->with('total_commission', \FFM::dollar($totalCommission))->with('total_fund', \FFM::dollar($totalFundedAmount))->with('total_prepaid', \FFM::dollar($totalPrepaid))->with('total_liquidity', \FFM::dollar($totalLiquidity))->with('total_underwriting_fee', \FFM::dollar($totalUnderwritingFee))->with('download-url', api_download_url('liquidity-download'))->make(true);
    }

    public static function getInvestorWithLiquidity()
    {
        $request = request();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $active = $request->input('active_status');
        $company = $request->input('company');
        $liquidity = $request->input('liquidity');
        $userId = Auth::user()->id;
        $hide = Settings::value('hide');
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userQuery = User::select('users.id', 'users.name', DB::raw('(SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity'))->join('user_has_roles', function ($join) {
            $join->on('users.id', '=', 'user_has_roles.model_id');
            $join->where('user_has_roles.role_id', 2);
        })->withCount(['investorTransactions AS credit_amount' => function ($query) use ($startDate, $endDate) {
            $query->select(DB::raw('SUM(investor_transactions.amount) as credit'));
            $query->where('investor_transactions.status', '=', 1);
            if ($startDate) {
                $query->where('investor_transactions.date', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('investor_transactions.date', '<=', $endDate);
            }
        }])->withCount(['investmentData AS total_funded' => function ($inner) use ($startDate, $endDate) {
            $inner->select(DB::raw('SUM(merchant_user.amount) as total_funded'))->whereIn('merchant_user.status', [1, 3]);
            $inner->whereHas('merchant', function ($subQuery) use ($startDate, $endDate) {
                $subQuery->where('active_status', '=', 1);
                if ($startDate) {
                    $subQuery->where('merchants.date_funded', '>=', $startDate);
                }
                if ($endDate) {
                    $subQuery->where('merchants.date_funded', '<=', $endDate);
                }
            });
        }])->withCount(['investmentData AS commission_amount' => function ($query) use ($startDate, $endDate) {
            $query->select(DB::raw('SUM(merchant_user.commission_amount) as commission_amount'))->whereIn('merchant_user.status', [1, 3]);
            $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                $query1->where('active_status', '=', 1);
                if ($startDate) {
                    $query1->where('merchants.date_funded', '>=', $startDate);
                }
                if ($endDate) {
                    $query1->where('merchants.date_funded', '<=', $endDate);
                }
            });
        }])->withCount(['investmentData AS under_writing_fee' => function ($query) use ($startDate, $endDate) {
            $query->select(DB::raw('SUM(merchant_user.under_writing_fee) as under_writing_fee'))->whereIn('merchant_user.status', [1, 3]);
            $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                $query1->where('active_status', '=', 1);
                if ($startDate) {
                    $query1->where('merchants.date_funded', '>=', $startDate);
                }
                if ($endDate) {
                    $query1->where('merchants.date_funded', '<=', $endDate);
                }
            });
        }])->withCount(['investmentData AS pre_paid' => function ($query) use ($startDate, $endDate) {
            $query->select(DB::raw('SUM(merchant_user.pre_paid) as pre_paid'))->whereIn('merchant_user.status', [1, 3]);
            $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                $query1->where('active_status', '=', 1);
                if ($startDate) {
                    $query1->where('merchants.date_funded', '>=', $startDate);
                }
                if ($endDate) {
                    $query1->where('merchants.date_funded', '<=', $endDate);
                }
            });
        }])->withCount(['investmentData AS ctd' => function ($query) use ($startDate, $endDate) {
            $query->select(DB::raw('sum(merchant_user.paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd'))->whereIn('merchant_user.status', [1, 3]);
            $query->whereHas('merchant', function ($query1) use ($startDate, $endDate) {
                $query1->where('active_status', '=', 1);
                if ($startDate) {
                    $query1->where('merchants.date_funded', '>=', $startDate);
                }
                if ($endDate) {
                    $query1->where('merchants.date_funded', '<=', $endDate);
                }
            });
        }]);
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $userQuery->where('company', $userId);
            } else {
                $userQuery->where('creator_id', $userId);
            }
        }
        if ($hide == 1) {
            $userQuery->where('active_status', 1);
        }
        if ($active == 1) {
            $userQuery->where('active_status', 1);
        } elseif ($active == 2) {
            $userQuery->where('active_status', 0);
        }
        if ($company != '') {
            $userQuery->where('company', $company);
        }
        $subAdmin = self::allSubAdmin();
        if ($subAdmin == 'subadmin') {
            $userQuery->whereIn('creator_id', $subAdmin);
        }

        return $userQuery->get();
    }

    public static function allSubAdmin()
    {
        return Role::whereName('company')->first()->users->where('company_status',1);
    }
}
