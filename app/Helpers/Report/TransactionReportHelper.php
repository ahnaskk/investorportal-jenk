<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use App\InvestorTransaction;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionReportHelper
{
    public static function getColumns()
    {
        return [['data' => 'id', 'name' => 'id', 'title' => 'Id', 'orderable' => false, 'searchable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Investor', 'orderable' => false, 'searchable' => false], ['data' => 'transaction_category', 'name' => 'transaction_category', 'title' => 'Transaction Category'], ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Transaction Type'], ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'], ['data' => 'date', 'name' => 'date', 'title' => 'Investment Date '], ['data' => 'maturity_date', 'name' => 'maturity_date', 'title' => 'Maturity date '], ['data' => 'updated_date', 'name' => 'updated_date', 'title' => 'Last Updated At']];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Transaction Report');
    }

    public static function getReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $investorIds = $request->input('investors');
        $transactionType = $request->input('transaction_type');
        $categoryIds = $request->input('categories');
        $isExport = $request->input('is_export') == 'yes';
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        $transactionQuery = InvestorTransaction::with('merchant')->select('investor_transactions.id', 'users.name', 'maturity_date', 'investor_id', 'transaction_category', 'transaction_type', 'amount', 'date', 'investor_transactions.updated_at', 'investor_transactions.category_notes')->leftJoin('users', 'users.id', 'investor_transactions.investor_id');
        $transactionQuery->where('investor_transactions.status', InvestorTransaction::StatusCompleted);
        if ($transactionType) {
            $transactionQuery->where('transaction_type', $transactionType);
        }
        if ($startDate != null) {
            $transactionQuery->where('date', '>=', $startDate);
        }
        if ($endDate != null) {
            $transactionQuery->where('date', '<=', $endDate);
        }
        if ($investorIds != null) {
            $transactionQuery->whereIn('investor_id', $investorIds);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $transactionQuery->where('users.company', $userId);
            } else {
                $transactionQuery->where('users.creator_id', $userId);
            }
        }
        if (! empty($categoryIds)) {
            $transactionQuery->whereIn('investor_transactions.transaction_category', $categoryIds);
        }
        $totalAmount = $transactionQuery->sum('amount');
        $datTable = \IPVueTable::of($transactionQuery);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('transaction_category', function ($data) {
            return \ITran::getLabel($data->transaction_category);
        })->addColumn('amount', function ($data) {
            return \FFM::dollar($data->amount);
        })->addColumn('merchant', function ($data) {
            if ($data->merchant) {
                return $data->merchant->name;
            } else {
                return '-';
            }
        })->editColumn('updated_date', function ($data) {
            return $data->updated_at ? \FFM::datetime($data->updated_at) : '';
        })->editColumn('date', function ($data) {
            return \FFM::date($data->date);
        })->editColumn('maturity_date', function ($data) {
            return \FFM::date($data->maturity_date);
        })->editColumn('name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/investors/portfolio', $data->investor_id)."'>$data->name</a>";
        })->editColumn('transaction_type', function ($data) {
            if ($data->transaction_type == 1) {
                return 'Debit';
            } elseif ($data->transaction_type == 2) {
                return 'Credit';
            }
        })->with('total', FFM::dollar($totalAmount))->with('download-url', api_download_url('transaction-download'))->make(true);
    }
}
