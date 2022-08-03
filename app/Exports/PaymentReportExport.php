<?php

namespace App\Exports;

use App\Helpers\PaymentReportHelper;
use FFM;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class PaymentReportExport implements FromView
{
    public function __construct(
    string $investor_id,
    string $merchant_id,
    string $from_date,
    string $to_date
  ) {
        $this->investor_id = $investor_id;
        $this->merchant_id = $merchant_id;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function view(): View
    {
        list($total, $reportData) = PaymentReportHelper::investor(
      $this->from_date,//startDate
      $this->to_date,//endDate
      null,//rCode
      $this->investor_id,//investor
      $this->merchant_id//merchant
    );
        $totals = collect($total)->map(function ($value, $fieldName) use ($total) {
            return ($fieldName !== 'count') ? \FFM::dollar($value) : $value;
        });
        $totals['total_net_participant_payment'] = \FFM::dollar($total['total_participant_share'] - $total['total_mgmnt_fee']);
        $simpleReportFields = [
      'name',
      'date_funded',
      'id',
      'last_payment_date',
      'code',
    ];
        $Self = collect($reportData)
    ->map(function ($record) use ($simpleReportFields) {
        $record['net_participant_payment'] = $record['participant_share'] - $record['mgmnt_fee'];

        return collect($record)
      ->map(function ($value, $field) use ($simpleReportFields) {
          return ! in_array($field, $simpleReportFields) ? \FFM::dollar($value) : ($field == 'date_funded' || $field == 'last_payment_date' ? \FFM::date($value) : $value);
      })
      ->toArray();
    });

        return view('vue.Export.PaymentReportExport', compact('Self', 'totals'));
    }
}
