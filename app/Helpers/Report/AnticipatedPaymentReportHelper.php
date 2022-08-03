<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use App\Merchant;
use App\MerchantPaymentTerm;
use App\MerchantUser;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\User;
use FFM;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PayCalc;

class AnticipatedPaymentReportHelper
{
    public static function getColumns()
    {
        return [
            ['data' => '', 'title' => 'Serial No', 'defaultContent' => '', 'orderable' => false, 'searchable' => false],
            ['data' => 'merchant_name', 'name' => 'merchant_name', 'title' => 'Merchant'],
            ['data' => 'anticipated_amount', 'name' => 'anticipated_amount', 'title' => 'Anticipated Amount', 'searchable' => false],
            ['data' => 'ctd', 'name' => 'ctd', 'title' => 'CTD', 'searchable' => false],
            ['data' => 'edit_term', 'name' => 'edit_term', 'title' => 'Edit Term', 'searchable' => false, 'orderable' => false],
        ];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Anticipated Payment Report');
    }

    public static function getReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $modified_term = $request->input('modified_term');
        $merchantIds = $request->input('merchants');
        $isExport = $request->input('is_export') == 'yes';
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $dates = PayCalc::getWorkingDays($startDate, $endDate);
        $data = [];
        $termQuery = MerchantPaymentTerm::select('merchant_payment_terms.id', 'merchant_payment_terms.merchant_id', 'merchant_payment_terms.payment_amount', 'merchant_payment_terms.advance_type', 'merchant_payment_terms.start_at', 'merchant_payment_terms.end_at');
        if ($merchantIds) {
            $termQuery->whereIn('merchant_id', $merchantIds);
        }
        $terms = $termQuery->whereHas('merchant', function (Builder $query) use ($unwanted_sub_status) {
            $query->whereNotIn('sub_status_id', $unwanted_sub_status);
        })->get();
        foreach ($terms as $term) {
            $payment_dates = $term->payments()->where('status', '>=', 0)->whereBetween('payment_date', [$startDate, $endDate])->pluck('payment_date');
            $payment_dates_count = count($payment_dates);
            if ($payment_dates_count > 0) {
                $hasMerchant = collect($data)->where('merchant_id', $term->merchant_id)->first();
                $data[$term->merchant_id] = (object) ['merchant_id' => $term->merchant_id, 'merchant_name' => $term->merchant->name, 'term_id' => [$term->id], 'anticipated_amount' => (optional($hasMerchant)->anticipated_amount ?? 0) + $term->payment_amount * $payment_dates_count, 'dates' => collect($payment_dates)->merge(optional($hasMerchant)->dates ?? [])->unique()->toArray()];
            }
        }
        foreach ($data as $key => $merchant) {
            $receivedPayments = ParticipentPayment::where('merchant_id', $merchant->merchant_id)->whereIn('payment_date', $merchant->dates)->sum('payment');
            $merchant->ctd = $receivedPayments;
            $data[$key] = $merchant;
        }
        $data = collect($data);
        $total_anticipated_amount = $data->pluck('anticipated_amount')->sum();
        $total_ctd = $data->pluck('ctd')->sum();
        $datTable = \IPVueTable::of($data);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('anticipated_amount', function ($data) {
            return FFM::dollar($data->anticipated_amount);
        })->editColumn('ctd', function ($data) {
            return FFM::dollar($data->ctd);
        })->editColumn('edit_term', function ($data) {
            return '<a class="btn btn-xs btn-primary" href="'.route('admin::merchants::payment-terms', ['mid' => $data->merchant_id]).'"><i class="glyphicon glyphicon-edit"></i></a>';
        })->with('total_anticipated_amount', FFM::dollar($total_anticipated_amount))->with('total_ctd', FFM::dollar($total_ctd))->with('download-url', api_download_url('anticipated-payment-download'))->make(true);
    }
}
