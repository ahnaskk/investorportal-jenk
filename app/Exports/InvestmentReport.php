<?php

namespace App\Exports;

use App\Models\Views\Reports\InvestmentReportView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use App\Merchant;

class InvestmentReport implements FromView
{
    public function __construct(
    string $from_date,
    string $to_date,
    string $merchant_id
  ) {
        $this->merchant_id = $merchant_id;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function view(): View
    {
        $Self = InvestmentReportView::orderByDesc('date_funded');
        if ($this->from_date) {
            $Self->where('date_funded', '>=', $this->from_date);
        }
        if ($this->to_date) {
            $Self->where('date_funded', '<=', $this->to_date);
        }
        if ($this->merchant_id != null) {
            $merchant_id_arr = explode(',', $this->merchant_id);
            $Self->whereIn('merchant_id', $merchant_id_arr);
        }
        // if($this->merchant_id){
        //   $Self->where('merchant_id', $this->merchant_id);

        // }
        $Self->whereinvestor_id(Auth::user()->id);
        $datasTotal = $Self;
        //if($this->merchant_name) $datas->where('Merchant' ,'like',"%{$this->merchant_name}%");
        // $Self->join('users', 'users.id', 'investment_report_views.investor_id');
        $display_value = Auth::user()->display_value;

        $Self = $Self->select('investment_report_views.*', DB::raw("IF('$display_value'='mid',merchant_id,upper(Merchant)) as Merchant"),DB::raw('(investment_report_views.i_rtr-investment_report_views.mgmnt_fee) as i_rtr'),DB::raw('(investment_report_views.commission_amount+investment_report_views.up_sell_commission) as commission_amount'))->get();

        $datasTotal->select([DB::raw('
    ROUND(sum(i_amount),2) as i_amount,
    sum(i_rtr-mgmnt_fee) as i_rtr,
    sum(commission_amount+up_sell_commission) as commission_amount,
    sum(share_t) as share_t,
    sum(pre_paid) as pre_paid,
    sum(invested_amount) as invested_amount,
    sum(under_writing_fee) as under_writing_fee,
    sum(mgmnt_fee) as mgmnt_fee
    ')]);
        $datasTotal = $datasTotal->first();
$advanceTypes = Merchant::getAdvanceTypes();
        return view('vue.Export.InvestmentReport', compact('Self', 'datasTotal','advanceTypes'));
    }
}
