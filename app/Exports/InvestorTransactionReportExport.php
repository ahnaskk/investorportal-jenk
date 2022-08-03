<?php

namespace App\Exports;

use App\InvestorTransaction;
use App\Models\InvestorAchRequest;
use App\Models\Views\InvestorAchTransactionView;
use FFM;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class InvestorTransactionReportExport implements FromView
{
    public function __construct(
    string $from_date,
    string $to_date,
    string $account_no
  ) {
        $this->account_no = $account_no;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function view(): View
    {
        $categories = \ITran::getAllOptions();
        $tran_method = InvestorTransaction::transactionMethodOptions();
        $tran_type = InvestorTransaction::transactionTypeOptions();
        $category_arr = [];
        $tran_type_arr = [];
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $investor_id = Auth::user()->id;
        $data = InvestorAchTransactionView::where('investor_id', Auth::user()->id)
        ->select('id', 'amount', 'investor_id', 'category_notes', 'transaction_category', 'transaction_method', 'transaction_type','account_no as account_no', 'date');

        if ($this->from_date) {
            $data->where('date', '>=', $this->from_date);
        }
        if ($this->to_date) {
            $data->where('date', '<=', $this->to_date);
        }
        if ($this->account_no) {
            $data->where('account_no', 'like', "%{$this->account_no}%");
        }

        $dataTotal = $data;
        $total_amount = array_sum(array_column($dataTotal->get()->toArray(), 'amount'));
        $Count = $data;
        $total_page = count($Count->get()->toArray());
        $data = $data->get();
        $total_amount = 0;
        foreach ($data as $key => $value) {
            $total_amount = $total_amount + $value->amount;
            $method_sort_col[] = isset($value->transaction_method) ? ($tran_method[$value->transaction_method]) : null;
            $data[$key]->amount = FFM::dollar($value->amount);
            $data[$key]->date = FFM::date($value->date);

            if (isset($value->transaction_category)) {
                $data[$key]->transaction_category = isset($categories[$value->transaction_category]) ? $categories[$value->transaction_category] : '';
            } else {
                $data[$key]->transaction_category = null;
            }
            $category_sort_col[] = $data[$key]->transaction_category;
            $data[$key]->TransactionMethod = isset($value->transaction_method) ? ($tran_method[$value->transaction_method]) : null;
            if (isset($value->transaction_type)) {
                if ($value->transaction_type == 'debit') {
                    $data[$key]->TransactionType = 'Credit';
                } elseif ($value->transaction_type == 'credit') {
                    $data[$key]->TransactionType = 'Debit';
                } else {
                    $data[$key]->TransactionType = $tran_type[$value->transaction_type];
                }
            } else {
                $data[$key]->TransactionType = null;
            }
            $type_sort_col[] = $data[$key]->TransactionType;
            if ($value->account_no != '') {
                $data[$key]->account_no = FFM::mask_cc($value->account_no);
            }
        }
        $total_amount=FFM::dollar($total_amount);
        $Self = $data;

        return view('vue.Export.InvestorTransactionReportExport', compact('Self', 'categories', 'total_amount'));
    }
}
