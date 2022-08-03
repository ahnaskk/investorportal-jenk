<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use App\Merchant;
use App\MerchantUser;
use App\PaymentInvestors;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Collection;

class MerchantsPerDiffReportHelper
{
    public static function getColumns()
    {
        return [['orderable' => false, 'data' => 'id', 'name' => 'id', 'title' => 'No', 'searchable' => false], ['data' => 'id_m', 'name' => 'id_m', 'title' => 'Merchant id'], ['data' => 'vp_i', 'name' => 'vp_i', 'title' => 'VP investment'], ['data' => 'vp_p', 'name' => 'vp_p', 'title' => 'Vp payment'], ['data' => 'diff', 'name' => 'diff', 'title' => 'diff'], ['data' => 'velocity_i', 'name' => 'velocity_i', 'title' => 'velocity investment'], ['data' => 'velocity_p', 'name' => 'velocity_p', 'title' => 'Velocity payment']];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Merchant Per Diff Report');
    }

    public static function getReport(Request $request)
    {
        $isExport = $request->input('is_export') == 'yes';
        $investments = MerchantUser::join('users', 'users.id', 'merchant_user.user_id')->select('amount', 'company', 'merchant_id')->get();
        $merchantInvestments = [];
        foreach ($investments as $investment) {
            if ($investment->company == 89) {
                $merchantInvestments[$investment->merchant_id][89] = (optional($merchantInvestments)[$investment->merchant_id][89] ?? 0) + $investment->amount;
            } else {
                $merchantInvestments[$investment->merchant_id][58] = (optional($merchantInvestments)[$investment->merchant_id][58] ?? 0) + $investment->amount;
            }
        }
        $payments = PaymentInvestors::join('users', 'users.id', 'payment_investors.user_id')->select('payment_investors.id', 'payment_investors.merchant_id', 'payment_investors.participant_share', 'users.company')->get();
        $merchantPayments = [];
        foreach ($payments as $key => $payment) {
            if ($payment->company == 89) {
                $merchantPayments[$payment->merchant_id][89] = (optional($merchantPayments)[$payment->merchant_id][89] ?? 0) + $payment->participant_share;
            } else {
                $merchantPayments[$payment->merchant_id][58] = (optional($merchantPayments)[$payment->merchant_id][58] ?? 0) + $payment->participant_share;
            }
        }
        $merchants = collect($merchantPayments)->map(function ($merchantPayment, $merchantId) use ($merchantInvestments) {
            $merchantPayment[89] = optional($merchantPayment)[89] ?? 0;
            $merchantPayment[58] = optional($merchantPayment)[58] ?? 0;
            $vp_p_1 = $merchantPayment[58];
            $velocity_p_1 = $merchantPayment[89];
            $velocity_i_1 = optional($merchantInvestments)[$merchantId][89] ?? 0;
            $vp_i_1 = optional($merchantInvestments)[$merchantId][58] ?? 0;
            $diff_merchant = (($vp_p_1) / (($vp_p_1) + ($velocity_p_1))) * 100 - (($vp_i_1) / ($velocity_i_1 + $vp_i_1)) * 100;
            $round_val = round($diff_merchant, 2);
            $abs_value = abs($round_val);
            if ($abs_value > 0.1) {
                return ['velocity_i' => $velocity_i_1, 'vp_i' => $vp_i_1, 'velocity_p' => $velocity_p_1, 'vp_p' => $vp_p_1, 'diff' => $round_val, 'id' => $merchantId];
            }
        })->filter(function ($merchant) {
            return $merchant;
        })->toArray();
        $datTable = \IPVueTable::of($merchants);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->addColumn('id_m', function ($data) {
            return $data['id'];
        })->addColumn('vp_i', function ($data) {
            return FFM::dollar($data['vp_i']);
        })->addColumn('velocity_i', function ($data) {
            return FFM::dollar($data['velocity_i']);
        })->addColumn('vp_p', function ($data) {
            return FFM::dollar($data['vp_p']);
        })->addColumn('velocity_p', function ($data) {
            return FFM::dollar($data['velocity_p']);
        })->addColumn('diff', function ($data) {
            return round($data['diff'], 3);
        })->with('download-url', api_download_url('merchant-per-diff-download'))->make(true);
    }
}
