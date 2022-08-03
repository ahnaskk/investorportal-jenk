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

class VelocityProfitReportHelper
{
    public static function getColumns()
    {
        return [['data' => '', 'title' => 'Serial No', 'defaultContent' => '', 'orderable' => false, 'searchable' => false], ['data' => 'merchant_name', 'name' => 'merchant_name', 'title' => 'Merchant'], ['data' => 'origination_fee', 'name' => 'origination_fee', 'title' => 'Origination Fee', 'searchable' => false], ['data' => 'underwriting_fee_flat', 'name' => 'underwriting_fee_flat', 'title' => 'Flat Under Writing Fee', 'searchable' => false], ['data' => 'syndication_fee', 'name' => 'syndication_fee', 'title' => 'Syndication Fee', 'searchable' => false], ['data' => 'management_fee_earned', 'name' => 'management_fee_earned', 'title' => 'Management Fee', 'searchable' => false], ['data' => 'underwriting_fee_earned', 'name' => 'underwriting_fee_earned', 'title' => 'Under Writing Fee', 'searchable' => false], ['data' => 'date_funded', 'name' => 'date_funded', 'title' => 'Funded Date', 'searchable' => false]];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Velocity Profitability Report');
    }

    public static function getReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $company = $request->input('company');
        $investorIds = $request->input('investors');
        $label = $request->input('label');
        $isExport = $request->input('is_export') == 'yes';
        $companyUserQuery = User::query();
        if ($company) {
            $companyUserQuery->where('company', $company);
        }
        $companyUserIds = $companyUserQuery->pluck('id')->toArray();
        $date_fund_query = 1;
        $merchantDateField = 'merchants.date_funded';
        if (! empty($startDate) && ! empty($endDate)) {
            $date_fund_query = "$merchantDateField >= '$startDate' AND $merchantDateField <= '$endDate'";
        } elseif (! empty($startDate)) {
            $date_fund_query = "$merchantDateField >= '$startDate'";
        } elseif (! empty($endDate)) {
            $date_fund_query = "$merchantDateField <= '$endDate'";
        }
        $merchantQuery = Merchant::leftJoin('merchants_details','merchants_details.merchant_id','merchants.id')->select('merchants.id', 'merchants_details.monthly_revenue', 'merchants.up_sell_commission', 'merchants.lender_id', 'merchants.name as merchant_name', 'merchants.origination_fee', 'merchants.date_funded', DB::raw("SUM(
				IF($date_fund_query =1,merchant_user.pre_paid,IF($date_fund_query,merchant_user.pre_paid,0))
			) as syndication_fee"), DB::raw("SUM(
				IF($date_fund_query =1, merchant_user.under_writing_fee, 
					IF($date_fund_query, merchant_user.under_writing_fee, 0)
				)
			) as underwriting_fee_earned"), DB::raw("IF($date_fund_query = 1, 350, IF($date_fund_query,350,0)) as underwriting_fee_flat"), DB::raw("IF($date_fund_query = 1, 'yes', IF($date_fund_query,'yes','no')) as within_funded_date"))->where('lender_id', 74)->leftJoin('merchant_user', 'merchants.id', '=', 'merchant_user.merchant_id')->groupBy('merchants.id');
        if (is_array($investorIds) and count($investorIds) > 0) {
            $merchantQuery->whereIn('merchant_user.user_id', $investorIds);
        }
        if (is_array($companyUserIds) and count($companyUserIds) > 0) {
            $merchantQuery->whereIn('merchant_user.user_id', $companyUserIds);
        }
        if ($label) {
            $merchantQuery->where('merchants.label', $label);
        }
        $mgmntFeeQuery = PaymentInvestors::where('payment_type', 1);
        if (! empty($investorIds) && is_array($investorIds) && count($investorIds) > 0) {
            $mgmntFeeQuery->whereIn('payment_investors.user_id', $investorIds);
        }
        $mgmntFeeQuery->join('merchants', 'merchants.id', 'payment_investors.merchant_id');
        $mgmntFeeQuery->where('merchants.lender_id', 74);
        $mgmntFeeQuery->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id');
        if ($startDate) {
            $mgmntFeeQuery->where('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $mgmntFeeQuery->where('payment_date', '<=', $endDate);
        }
        if (! empty($companyUserIds) && is_array($companyUserIds)) {
            $mgmntFeeQuery->whereIn('payment_investors.user_id', $companyUserIds);
        }
        $mgmnt_fee = $mgmntFeeQuery->groupBy('payment_investors.merchant_id')->select(DB::raw('sum(mgmnt_fee) as management_fee_earned'), 'payment_investors.merchant_id')->pluck('management_fee_earned', 'payment_investors.merchant_id')->toArray();
        $merchants = $merchantQuery->get();
        $merchants = $merchants->map(function ($merchant) use ($mgmnt_fee) {
            $management_fee = isset($mgmnt_fee[$merchant->id]) ? $mgmnt_fee[$merchant->id] : 0;
            if ($merchant->within_funded_date == 'no' && ! isset($mgmnt_fee[$merchant->id])) {
                return null;
            }

            return ['management_fee_earned' => $management_fee, 'origination_fee' => $merchant->origination_fee, 'underwriting_fee_flat' => $merchant->underwriting_fee_flat, 'syndication_fee' => $merchant->syndication_fee, 'underwriting_fee_earned' => $merchant->underwriting_fee_earned, 'date_funded' => $merchant->date_funded, 'merchant_name' => $merchant->merchant_name, 'monthly_revenue' => $merchant->monthly_revenue, 'up_sell_commission' => $merchant->up_sell_commission, 'merchant_id' => $merchant->id];
        })->filter(function ($merchant) {
            return $merchant;
        });
        $total_origination_fee = $merchants->pluck('origination_fee')->sum();
        $total_underwriting_fee_flat = $merchants->pluck('underwriting_fee_flat')->sum();
        $total_syndication_fee = $merchants->pluck('syndication_fee')->sum();
        $total_underwriting_fee_earned = $merchants->pluck('underwriting_fee_earned')->sum();
        $total_monthly_revenue = $merchants->pluck('monthly_revenue')->sum();
        $total_up_sell_commission = $merchants->pluck('up_sell_commission')->sum();
        $total_management_fee_earned = collect($mgmnt_fee)->sum();
        $datTable = \IPVueTable::of($merchants);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('date_funded', function ($data) {
            return FFM::date($data['date_funded']);
        })->editColumn('origination_fee', function ($data) {
            return FFM::dollar($data['origination_fee']);
        })->editColumn('underwriting_fee_flat', function ($data) {
            return FFM::dollar($data['underwriting_fee_flat']);
        })->editColumn('syndication_fee', function ($data) {
            return FFM::dollar($data['syndication_fee']);
        })->editColumn('management_fee_earned', function ($data) {
            return FFM::dollar($data['management_fee_earned']);
        })->editColumn('underwriting_fee_earned', function ($data) {
            return FFM::dollar($data['underwriting_fee_earned']);
        })->editColumn('monthly_revenue', function ($data) {
            return FFM::dollar($data['monthly_revenue']);
        })->editColumn('up_sell_commission', function ($data) {
            return FFM::dollar($data['up_sell_commission']);
        })->editColumn('merchant_name', function ($data) {
            return $data['merchant_name'];
        })->with('total_origination_fee', FFM::dollar($total_origination_fee))->with('total_underwriting_fee_flat', FFM::dollar($total_underwriting_fee_flat))->with('total_syndication_fee', FFM::dollar($total_syndication_fee))->with('total_management_fee_earned', FFM::dollar($total_management_fee_earned))->with('total_underwriting_fee_earned', FFM::dollar($total_underwriting_fee_earned))->with('total_monthly_revenue', FFM::dollar($total_monthly_revenue))->with('total_up_sell_commission', FFM::dollar($total_up_sell_commission))->with('download-url', api_download_url('velocity-profitability-download'))->make(true);
    }
}
