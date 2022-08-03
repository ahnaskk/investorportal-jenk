<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use App\InvestorTransaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccruedInterestReportHelper
{
    public static function getColumns()
    {
        return [
            ['data' => 'DT_RowIndex', 'title' => 'Serial No', 'orderable' => false, 'searchable' => false],
            ['data' => 'investor_name', 'defaultContent' => '', 'title' => 'Investor Name', 'orderable' => false],
            ['data' => 'total_credit', 'defaultContent' => '', 'title' => 'Total Credit', 'orderable' => false],
            ['data' => 'investor_interest_rate', 'defaultContent' => '', 'title' => 'Investor Interest Rate', 'orderable' => false],
            ['data' => 'interest_accrued', 'name' => 'interest_accrued', 'title' => 'Interest Accrued', 'orderable' => false],
        ];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Accrued Interest');
    }

    public static function getReport(Request $request)
    {
        $investorIds = $request->input('investors');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $isExport = $request->input('is_export') == 'yes';
        $userId = Auth::user()->id;
        $investorMerchants = [];
        $percentage = 0;
        $debitInvestors = User::investors()->where('investor_type', 1);
        if ($investorIds && is_array($investorIds)) {
            $debitInvestors = $debitInvestors->whereIn('id', $investorIds);
        }
        if ($endDate) {
            $today = $endDate;
        } else {
            $today = date('Y-m-d');
        }
        if (Auth::user()->hasRole(['company'])) {
            $debitInvestors = $debitInvestors->where('company', $userId);
        }
        $debitInvestors = $debitInvestors->get();
        foreach ($debitInvestors as $key => $debitInvestor) {
            $investorMerchants[$debitInvestor->id] = [];
            $investorMerchants[$debitInvestor->id]['name'] = $debitInvestor->name;
            $totalCreditAmount = 0;
            $totalCreditValue = 0;
            $first_date = '';
            $proportion = 0;
            $investorTransactionQuery = InvestorTransaction::where('transaction_type', 2)->where('status', InvestorTransaction::StatusCompleted)->where('investor_id', $debitInvestor->id);
            if ($endDate) {
                $investorTransactionQuery->where('date', '<=', $endDate);
            }
            $investorTransactionQuery->orderBy('date');
            $investorTransactions = $investorTransactionQuery->get();
            foreach ($investorTransactions as $key => $transaction) {
                if ($startDate >= $transaction->date) {
                    $this_date = $startDate;
                } else {
                    $this_date = $transaction->date;
                }
                $dateDifference = date_diff(new \DateTime($this_date), new \DateTime($today));
                $thisDateDifference = $dateDifference->format('%R%a');
                $proportion = $thisDateDifference ? ($thisDateDifference + 1) / 365 : 0;
                $totalCreditAmount = $totalCreditAmount + $transaction->amount;
                $totalCreditValue = $totalCreditValue + $transaction->amount * $proportion;
            }
            $interestAccrued = $debitInvestor->interest_rate ? $totalCreditValue * $debitInvestor->interest_rate / 100 : 0;
            $investorMerchants[$debitInvestor->id]['interest_accrued'] = $interestAccrued;
            $investorMerchants[$debitInvestor->id]['total_credit'] = $totalCreditAmount;
            $investorMerchants[$debitInvestor->id]['interest_rate'] = $debitInvestor->interest_rate;
        }
        $totalCreditAmount = collect($investorMerchants)->pluck('total_credit')->sum();
        $totalInterestAccrued = collect($investorMerchants)->pluck('interest_accrued')->sum();
        $datTable = \IPVueTable::of(collect($investorMerchants));
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('investor_name', function ($data) {
            return $data['name'];
        })->addColumn('interest_accrued', function ($data) {
            return \FFM::dollar($data['interest_accrued']);
        })->addColumn('total_credit', function ($data) {
            return \FFM::dollar($data['total_credit']);
        })->addColumn('investor_interest_rate', function ($data) {
            return \FFM::percent($data['interest_rate']);
        })->with('net_zero', \FFM::dollar(0))
            ->with('net_zero_with_interest', \FFM::dollar(0))
            ->with('net_zero_with_limited_interest', \FFM::dollar(0))
            ->with('interest_inv', \FFM::dollar(0))
            ->with('interest_cre', \FFM::dollar(0))
            ->with('total_credit', \FFM::dollar($totalCreditAmount))
            ->with('total_interest_accrued', \FFM::dollar($totalInterestAccrued))
            ->with('download-url', api_download_url('accrued-interest-download'))
            ->make(true);
    }
}
