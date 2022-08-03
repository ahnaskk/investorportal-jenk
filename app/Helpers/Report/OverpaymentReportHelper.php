<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use App\Merchant;
use App\MerchantUser;
use App\PaymentInvestors;
use App\User;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OverpaymentReportHelper
{
    public static function getColumns()
    {
        return [['data' => 'id', 'name' => 'id', 'title' => 'No', 'orderable' => false, 'searchable' => false], ['data' => 'merchant', 'name' => 'merchant', 'title' => 'Merchant'], ['data' => 'overpayment', 'name' => 'overpayment', 'title' => 'Overpayment'], ['data' => 'total_rtr', 'name' => 'total_rtr', 'title' => 'Total RTR']];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Overpayment Report');
    }

    public static function getReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $merchantIds = $request->input('merchants');
        $investorIds = $request->input('investors');
        $company = $request->input('company');
        $lenderIds = $request->input('lenders');
        $subStatusIds = $request->input('sub_statuses');
        $isExport = $request->input('is_export') == 'yes';
        $companyUserQuery = User::query();
        if (! empty($company)) {
            $companyUserQuery->where('company', $company);
        }
        $companyUserIds = $companyUserQuery->pluck('id')->toArray();
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $overPaymentQuery = MerchantUser::whereIn('merchant_user.status', [1, 3])->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
        if (! empty($merchantIds) && is_array($merchantIds)) {
            $overPaymentQuery->whereIn('merchants.id', $merchantIds);
        }
        if (! empty($subStatusIds) && is_array($subStatusIds)) {
            $overPaymentQuery->whereIn('merchants.sub_status_id', $subStatusIds);
        }
        if (! empty($lenderIds) && is_array($lenderIds)) {
            $overPaymentQuery->whereIn('merchants.lender_id', $lenderIds);
        }
        if (! empty($companyUserIds) && is_array($companyUserIds)) {
            $overPaymentQuery->whereIn('merchant_user.user_id', $companyUserIds);
        }
        $overPaymentQuery->with('merchant')->whereHas('merchant', function ($query1) {
            $query1->where('active_status', 1);
        });
        if (! empty($investorIds) && is_array($investorIds)) {
            $overPaymentQuery->whereIn('merchant_user.user_id', $investorIds);
        }
        $overPaymentQuery->select('merchants.name', 'merchants.id', 'merchants.sub_status_id', DB::raw('
				sum( 
					(
						merchant_user.invest_rtr *
                        (
                            (  IF( s_prepaid_status=0,0,0 ) + merchant_user.mgmnt_fee )
                        /100
                        ) 
                    )  
                ) as total_fee'), DB::raw('sum(merchant_user.invest_rtr) as total_rtr'), DB::raw('sum(merchant_user.amount) as invested_amount'), DB::raw('
			sum(
				IF(paid_participant_ishare > invest_rtr, ( paid_participant_ishare - invest_rtr ) * ( 1- ( merchant_user.mgmnt_fee ) / 100 ), 0)
            ) as overpayment'));
        $paymentInvestorQuery = PaymentInvestors::join('users', 'users.id', 'payment_investors.user_id');
        if (! empty($investorIds) && is_array($investorIds)) {
            $paymentInvestorQuery->whereIn('payment_investors.user_id', $investorIds);
        }
        if ((! empty($lenderIds) && is_array($lenderIds)) || ! empty($subStatusIds) && is_array($subStatusIds)) {
            $paymentInvestorQuery->join('merchants', 'merchants.id', 'payment_investors.merchant_id');
        }
        if ((! empty($lenderIds) && is_array($lenderIds))) {
            $paymentInvestorQuery->whereIn('merchants.lender_id', $lenderIds);
        }
        if (! empty($subStatusIds) && is_array($subStatusIds)) {
            $paymentInvestorQuery->whereIn('sub_status_id', $subStatusIds);
        }
        if (! empty($startDate) || ! empty($endDate)) {
            $paymentInvestorQuery->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id');
            if (! empty($startDate)) {
                $paymentInvestorQuery->where('payment_date', '>=', $startDate);
            }
            if (! empty($endDate)) {
                $paymentInvestorQuery->where('payment_date', '<=', $endDate);
            }
        }
        if (! empty($companyUserIds) && is_array($companyUserIds)) {
            $paymentInvestorQuery->whereIn('payment_investors.user_id', $companyUserIds);
        }
        if (! empty($merchantIds) && is_array($merchantIds)) {
            $paymentInvestorQuery->whereIn('payment_investors.merchant_id', $merchantIds);
        }
        $merchantOverPayments = $paymentInvestorQuery->groupBy('payment_investors.merchant_id')->select(DB::raw('sum(overpayment) as overpayment'), 'payment_investors.merchant_id')->pluck('overpayment', 'payment_investors.merchant_id')->toArray();
        $totalQuery = clone $overPaymentQuery;
        $table = [];
        $totalOverpayment = $totalQuery->select(DB::raw('sum(merchant_user.invest_rtr) as total_rtr'), DB::raw('sum(
				IF( paid_participant_ishare > invest_rtr, ( paid_participant_ishare-invest_rtr ) * ( 1 - ( merchant_user.mgmnt_fee ) /100 ), 0)
                ) as total_overpayment'))->first();
        $totalRTR = ($totalOverpayment and $totalOverpayment->total_overpayment != 0) ? $totalOverpayment->total_rtr : 0;
        $totalOverpayments = collect($merchantOverPayments)->sum();
        $overPayments = $overPaymentQuery->groupBy('merchant_user.merchant_id')->having('overpayment', '!=', 0)->get()->map(function ($overPayment, $index) use ($merchantOverPayments) {
            $hasMerchantOverPayment = isset($merchantOverPayments[$overPayment->id]) ? $merchantOverPayments[$overPayment->id] : 0;
            if ($hasMerchantOverPayment != 0) {
                return ['id' => $overPayment->id, 'merchant' => $overPayment->name, 'overpayment' => $hasMerchantOverPayment, 'total_rtr' => $overPayment->total_rtr];
            }
        })->filter(function ($overPayment) {
            return $overPayment;
        })->toArray();
        $datTable = \IPVueTable::of(collect($overPayments));
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('merchant', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data['id'])."'>".$data['merchant'].'</a>';
        })->addColumn('overpayment', function ($data) {
            return FFM::dollar($data['overpayment']);
        })->addColumn('total_rtr', function ($data) {
            return FFM::dollar($data['total_rtr']);
        })->with('total_rtr', FFM::dollar($totalRTR))->with('total_overpayment', FFM::dollar($totalOverpayments))->with('download-url', api_download_url('overpayment-download'))->make(true);
    }
}
