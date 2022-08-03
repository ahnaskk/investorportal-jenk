<?php

namespace App\Exports;

use App\InvestorTransaction;
use App\Library\Repository\Interfaces\IMerchantRepository;
use FFM;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class DefaultRateMerchantReportExport implements FromView
{
    public function __construct(
    IMerchantRepository $merchant,
    string $from_date,
    string $to_date,
    string $days
  ) {
        $this->merchant = $merchant;
        $this->days = $days;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function view(): View
    {
        $investor_id = Auth::user()->id;
        $Self = $this->merchant->merchantDefaulRateForInvestor(
      $this->from_date ?? '',//sDate
      $this->to_date ?? '',//eDate
      [$investor_id],//investors
      '',//company
      [4,18,19,20,22],//sub_status
      '',//funded_date
      $this->days,//days
      ''//investor_type
    );
        $Self = $Self->get();

        return view('vue.Export.DefaultRateMerchantReportExport', compact('Self'));
    }
}
