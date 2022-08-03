<?php

namespace App\Helpers\Report;

use App\Console\Commands\dbSetupForLocal;
use function App\Helpers\api_download_url;
use App\Http\Resources\SuccessResource;
use App\Merchant;
use App\MerchantUser;
use App\PaymentInvestors;
use FFM;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PayCalc;

class DefaultRateReportHelper
{
    public static function getTableColumns()
    {
        return [
            ['name' => 'id', 'orderable' => false, 'data' => 'id', 'title' => 'No', 'searchable' => false],
            ['data' => 'name', 'name' => 'users.name', 'title' => 'Investor', 'orderable' => false],
            ['data' => 'net_zero', 'name' => 'net_zero', 'title' => 'Net Zero', 'orderable' => false, 'searchable' => false],
            ['data' => 'default_amount', 'name' => 'default_amount', 'title' => 'Default Invested Amount', 'orderable' => false, 'searchable' => false],
            ['data' => 'collection_amount', 'name' => 'collection_amount', 'title' => 'Default RTR Amount', 'orderable' => false, 'searchable' => false],
            ['data' => 'default_rate', 'name' => 'default_rate', 'title' => 'Default Invested Rate', 'orderable' => false, 'searchable' => false],
            ['data' => 'collection_rate', 'name' => 'collection_rate', 'title' => 'Default RTR Rate', 'orderable' => false, 'searchable' => false],
            ['data' => 'overpayment', 'name' => 'overpayment', 'title' => 'Overpayment', 'orderable' => false, 'searchable' => false],
        ];
    }

    public static function getMerchantTableColumns()
    {
        return [
            ['name' => 'id', 'orderable' => false, 'data' => 'id', 'title' => 'ID', 'searchable' => false],
            ['data' => 'name', 'name' => 'name', 'title' => 'Merchant', 'orderable' => true],
            ['data' => 'default_amount', 'name' => 'default_amount', 'title' => 'Default Invested Amount', 'orderable' => true],
            ['data' => 'investor_rtr', 'name' => 'investor_rtr', 'title' => 'Default RTR Amount', 'orderable' => true],
            ['data' => 'last_status_updated_date', 'name' => 'last_status_updated_date', 'title' => 'Default Date', 'orderable' => true],
        ];
    }

    public static function getMerchantReport(Request $request)
    {
        $merchantQuery = self::getMerchantQuery($request);
        $totalInvestorRTR = $merchantQuery->pluck('investor_rtr')->sum();
        $totalDefaultAmount = $merchantQuery->pluck('default_amount')->sum();
        $isExport = $request->input('is_export') == 'yes';
        $datTable = \IPVueTable::of($merchantQuery);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('default_amount', function ($data) use ($request) {
            return FFM::dollar($data->default_amount);
        })->editColumn('investor_rtr', function ($data) use ($request) {
            return FFM::dollar($data->investor_rtr);
        })->editColumn('last_status_updated_date', function ($data) use ($request) {
            return FFM::datetime($data->last_status_updated_date);
        })->addColumn('rowdetails', function ($data) {
        })->with('total_default_amount', FFM::dollar($totalDefaultAmount))
            ->with('total_investor_rtr', FFM::dollar($totalInvestorRTR))
            ->with('download-url', api_download_url('default-rate-merchant-download'))
            ->make(true);
    }

    public static function getMerchantQuery(Request $request)
    {
        $investorIds = $request->input('investors', []);
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');
        $subStatusId = $request->input('sub_status', []);
        $funded_date = $request->input('funded_date');
        $days = $request->input('days');
        $company = $request->input('company');
        $default_date = ! empty($toDate) ? $toDate : now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        $merchantQuery = Merchant::join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->join('users', 'users.id', 'merchant_user.user_id');
        if (is_array($investorIds) and count($investorIds)) {
            $merchantQuery->whereIn('merchant_user.user_id', $investorIds);
        }
        if (! empty($company)) {
            $merchantQuery->where('users.company', $company);
        }
        if (is_array($subStatusId) and count($subStatusId) > 0) {
            $merchantQuery->whereIn('merchants.sub_status_id', $subStatusId);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $merchantQuery->where('users.company', $userId);
            } else {
                $merchantQuery->where('users.creator_id', $userId);
            }
        }
        if ($funded_date == 1) {
            $date_filter_table = 'merchants.date_funded';
        } else {
            $date_filter_table = 'merchants.last_status_updated_date';
        }
        if ($fromDate) {
            $merchantQuery->whereDate($date_filter_table, '>=', $fromDate);
        }
        if ($toDate) {
            $merchantQuery->whereDate($date_filter_table, '<=', $toDate);
        }
        $merchantQuery->groupBy('merchants.id');
        $merchantQuery = self::putMerchantQueryDaysFilter($merchantQuery, $days, $default_date);
        $merchantQuery->select('merchants.id', 'merchants.name', 'merchants.last_status_updated_date', DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid) as invested_amount'), DB::raw('SUM(merchant_user.invest_rtr * ( 100 - merchant_user.mgmnt_fee ) /100 ) as rtr_after_fee'), DB::raw('SUM(merchant_user.paid_participant_ishare - paid_mgmnt_fee ) as ctd'), self::merchantInvestorAmountColumn($merchant_day, true), self::merchantDefaultAmountColumn($merchant_day));

        return $merchantQuery;
    }

    public static function getReport(Request $request)
    {
        $overPaymentStatus = $request->input('overpayment_status');
        $report = self::getSearchDefaultReport($request);
        $merchants = $report['merchants'] ?? [];
        $totalDefaultAmount = $report['total_default_amount'] ?? 0;
        $totalRTR = $report['total_rtr'] ?? 0;
        $totalNetZero = $report['net_zero_sum'] ?? 0;
        $investorRTR = $report['investor_rtr'] ?? 0;
        $investmentAmount = $report['investment_amount'] ?? 0;
        $totalRTRAmount = $report['total_rtr_amount'] ?? 0;
        $isExport = $request->input('is_export') == 'yes';
        $overPayments = $report['over_payments'] ?? collect([]);
        $overPaymentAmount = $overPayments->sum('overpayment');
        $overPayment = 0;
        if ($overPaymentStatus == 0 || $overPaymentStatus == 2) {
            $overPayment = $overPaymentAmount;
        }
        $overPayments = $overPayments->toArray();
        $totalDefaultAmount = $totalDefaultAmount - $overPayment;
        $totalRTR = $totalRTR - $overPayment;
        $datTable = \IPVueTable::of($merchants);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('name', function ($data) {
            return $data->name;
        })->editColumn('mangt_fee', function ($data) {
            return FFM::dollar($data->mangt_fee);
        })->editColumn('invest_rtr', function ($data) {
            return FFM::dollar($data->invest_rtr);
        })->editColumn('net_zero', function ($data) {
            return FFM::dollar($data->net_zero);
        })->editColumn('default_amount', function ($data) use ($overPayments, $overPaymentStatus) {
            $overPayment = 0;
            if ($overPaymentStatus == 0 || $overPaymentStatus == 2) {
                $overPayment = ($overPayments[$data->id]) ?? 0;
            }
            $default = $data->default_amount - $overPayment;

            return FFM::dollar($default);
        })->editColumn('collection_amount', function ($data) use ($overPayments, $overPaymentStatus) {
            $overPayment = 0;
            if ($overPaymentStatus == 0 || $overPaymentStatus == 2) {
                $overPayment = ($overPayments[$data->id]) ?? 0;
            }
            $def_rtr = round($data->investor_rtr - $overPayment, 2);

            return FFM::dollar($def_rtr);
        })->editColumn('overpayment', function ($data) use ($overPayments, $overPaymentStatus) {
            $over = ($overPayments[$data->id]) ?? 0;

            return FFM::dollar($over);
        })->editColumn('default_rate', function ($data) use ($investmentAmount, $overPayments, $overPaymentStatus) {
            $over_payment_investor_value = 0;
            if ($overPaymentStatus == 0 || $overPaymentStatus == 2) {
                $over_payment_investor_value = $overPayments[$data->id] ?? 0;
            }
            $invest = isset($investmentAmount[$data->id]) ? $investmentAmount[$data->id] : 0;

            return FFM::percent(round(((float) $data->default_amount - (float) $over_payment_investor_value) / (float) ($investmentAmount[$data->id]) * 100, 2));
        })->editColumn('collection_rate', function ($data) use ($investmentAmount, $overPayments, $overPaymentStatus) {
            $over_payment_investor_value = 0;
            if ($overPaymentStatus == 0 || $overPaymentStatus == 2) {
                $over_payment_investor_value = $overPayments[$data->id] ?? 0;
            }
            $collection_rate = round(($data->investor_rtr - $over_payment_investor_value) / ($investmentAmount[$data->id]) * 100, 2).'%';
            if ($collection_rate <= 0) {
                $collection_rate = '0%';
            }

            return $collection_rate;
        })->with('total_default_amount', \FFM::dollar($totalDefaultAmount))->with('total_collection', \FFM::dollar($totalRTR))->with('total_overpayment', \FFM::dollar($overPaymentAmount))->with('download-url', api_download_url('default-rate-download'))->make(true);
    }

    public static function getDownload(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getTableColumns(), self::getReport($request), time().'-'.'Default Rate');
    }

    public static function getMerchantDownload(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getMerchantTableColumns(), self::getMerchantReport($request), time().'-'.'Default Merchant Rate');
    }

    public static function getSearchDefaultReport(Request $request)
    {
        $investorIds = $request->input('investors', []);
        $merchantIds = $request->input('merchants', []);
        $lenderIds = $request->input('lenders', []);
        $rateType = $request->input('rate_type');
        $velocity = $request->input('velocity');
        $fromDate = $request->input('start_date');
        $toDate = $request->input('end_date');
        $subStatusId = $request->input('sub_status', []);
        $funded_date = $request->input('funded_date');
        $active_status = $request->input('active_status');
        $overpayment = $request->input('overpayment');
        $days = $request->input('days');
        $company = $request->input('company');
        $userId = Auth::user()->id;
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $merchantQuery = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->whereIn('merchant_user.status', [1, 3])->join('users', 'users.id', 'merchant_user.user_id');
        $ctdQuery = MerchantUser::whereIn('merchant_user.status', [1, 3])->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id');
        $overPaymentQuery = PaymentInvestors::join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id')->join('users', 'users.id', 'payment_investors.user_id')->join('merchants', 'merchants.id', 'participent_payments.merchant_id');
        if (count($merchantIds) > 0) {
            $merchantQuery->whereIn('merchants.id', $merchantIds);
            $ctdQuery->whereIn('merchants.id', $merchantIds);
            $overPaymentQuery->whereIn('merchants.id', $merchantIds);
        }
        if ($velocity) {
            $merchantQuery->where('users.company', $velocity);
        }
        if (count($investorIds) > 0) {
            $merchantQuery->whereIn('merchant_user.user_id', $investorIds);
            $ctdQuery->whereIn('merchant_user.user_id', $investorIds);
            $overPaymentQuery->whereIn('merchant_user.user_id', $investorIds);
        }
        if (count($lenderIds) > 0) {
            $merchantQuery->whereIn('merchants.lender_id', $lenderIds);
            $ctdQuery->whereIn('merchants.lender_id', $lenderIds);
            $overPaymentQuery->whereIn('merchants.lender_id', $lenderIds);
        }
        $date_filter_table = ($funded_date == 'true') ? 'merchants.date_funded' : 'merchants.last_status_updated_date';
        if ($fromDate) {
            $merchantQuery->whereDate($date_filter_table, '>=', $fromDate);
            $overPaymentQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $merchantQuery->whereDate($date_filter_table, '<=', $toDate);
            $overPaymentQuery->whereDate('payment_date', '<=', $toDate);
        }
        $default_date = ! empty($toDate) ? $toDate : now();
        $merchant_day = PayCalc::setDaysCalculation($default_date);
        $merchantQuery = self::putMerchantQueryDaysFilter($merchantQuery, $days, $default_date);
        if ($active_status == 1) {
            $merchantQuery->where('users.active_status', 1);
            $ctdQuery->where('users.active_status', 1);
            $overPaymentQuery->where('users.active_status', 1);
        } elseif ($active_status == 2) {
            $merchantQuery->where('users.active_status', 0);
            $ctdQuery->where('users.active_status', 0);
            $overPaymentQuery->where('users.active_status', 0);
        }
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $merchantQuery->where('users.company', $userId);
            } else {
                $merchantQuery->where('users.creator_id', $userId);
            }
        }
        if (! empty($subStatusId) && is_array($subStatusId)) {
            $merchantQuery->whereIn('merchants.sub_status_id', $subStatusId);
        }
        $investmentAmountQuery = clone $ctdQuery;
        $investorRtrQuery = clone $ctdQuery;
        $ctd = $ctdQuery->groupBy('merchant_user.user_id')->select(DB::raw('SUM(paid_participant_ishare) as ctd'), 'merchant_user.user_id')->pluck('ctd', 'merchant_user.user_id');
        $investorRTR = $investorRtrQuery->groupBy('merchant_user.user_id')->select(DB::raw('
				SUM( 
					(
						invest_rtr - (merchant_user.invest_rtr * merchant_user.mgmnt_fee/100)
                    )
					+  IF( old_factor_rate > factor_rate, ( merchant_user.amount * (old_factor_rate - factor_rate) ) , 0 )
                ) as total_rtr'), 'merchant_user.user_id')->pluck('total_rtr', 'merchant_user.user_id');
        $investmentAmount = $investmentAmountQuery->groupBy('merchant_user.user_id')->select(DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as total_investment'), 'merchant_user.user_id')->pluck('total_investment', 'merchant_user.user_id');
        $totalQuery = clone $merchantQuery;
        $total = $totalQuery->select(DB::raw('
				SUM(
					'.$merchant_day.' * 
					(
	
	                    ( merchant_user.invest_rtr +  IF(old_factor_rate > factor_rate, ( merchant_user.amount * (old_factor_rate - factor_rate) ) , 0) )
	                    -
	                        ( merchant_user.invest_rtr * ( merchant_user.mgmnt_fee ) / 100
	                    +  IF(old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate - factor_rate ) * ( merchant_user.mgmnt_fee) / 100  ) , 0 ))
	
	                    -
	                    ( IF( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee, merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee,0 ) )
	                )
				) as total_rtr'), DB::raw('group_concat(distinct merchant_user.user_id) as user_ids'), DB::raw('(
					SUM(merchant_user.amount) + 
					SUM(merchant_user.commission_amount) + 
					SUM(merchant_user.pre_paid) + 
					SUM(merchant_user.under_writing_fee)
					) as net_zero_sum
               '))->first();
        $userIds = explode(',', ($total ? $total->user_ids : []));
        $overPaymentQuery->whereIn('payment_investors.user_id', $userIds);
        if (! empty($fromDate)) {
            $overPaymentQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if (! empty($toDate)) {
            $overPaymentQuery->whereDate('payment_date', '>=', $toDate);
        }
        $overPayments = $overPaymentQuery->groupBy('payment_investors.user_id')->select(DB::raw('sum(overpayment) as overpayment'), 'users.id')->pluck('overpayment', 'users.id');
        $totalNetZero = ! empty($total->net_zero_sum) ? ($total->net_zero_sum) : 0;
        $totalRTR = ! empty($total->total_rtr) ? ($total->total_rtr) : 0;
        $merchantStatuses = [1, 4];
        $merchantStatuses = implode(',', $merchantStatuses);
        $merchants = $merchantQuery->select('users.id', 'users.name as name', 'old_factor_rate', 'factor_rate', DB::raw('SUM(merchant_user.mgmnt_fee) as old_mag_fee'), 'sub_status_id', DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid ) as invested_amount'), DB::raw('SUM(
						merchant_user.invest_rtr * ( merchant_user.mgmnt_fee ) / 100
                        +  
                        IF( old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate-factor_rate ) * ( merchant_user.mgmnt_fee ) / 100 ), 0 )
                    ) as mangt_fee'), DB::raw('
					SUM(
						merchant_user.invest_rtr + 
						IF( old_factor_rate > factor_rate, ( merchant_user.amount * (old_factor_rate - factor_rate) ) , 0 ) 
					) as invest_rtr1'), DB::raw('(SELECT lag_time FROM users  WHERE merchants.lender_id = users.id) as lag_time'), DB::raw('SUM( 
						invest_rtr - 
						( 
							( merchant_user.invest_rtr 
							* 
							( IF ( merchants.m_s_prepaid_status = 0,0,0) + merchant_user.mgmnt_fee ) / 100 
							)
						)	
					) as invest_rtr'), self::merchantDefaultAmountColumn(), self::merchantInvestorAmountColumn(), DB::raw('SUM( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee ) as ctd_1'), DB::raw('SUM( (merchant_user.amount) + (merchant_user.commission_amount) + (merchant_user.pre_paid) + (merchant_user.under_writing_fee) ) as net_zero'), DB::raw('SUM(
					( 
						( merchant_user.invest_rtr )
						-
                        (
                            ( 
                                merchant_user.invest_rtr *
                                (
                                    ( IF ( merchants.m_s_prepaid_status=0,0,0 ) + merchant_user.mgmnt_fee ) 
                                    / 100 
                                ) 
                                
                            ) 
                        )
                    )
                ) 
                - 
	            SUM( 
	                ( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee )
	            ) as collection_amount'))->groupBy('users.id');
        $totalDefaultAmount = array_sum(array_column($merchants->get()->toArray(), 'default_amount'));
        $totalRTRAmount = $merchantQuery->sum('invest_rtr');

        return ['total_rtr_amount' => $totalRTRAmount, 'investor_rtr' => $investorRTR, 'investment_amount' => $investmentAmount, 'over_payments' => $overPayments, 'merchants' => $merchants, 'total_default_amount' => $totalDefaultAmount, 'net_zero_sum' => $totalNetZero, 'user_ids' => $userIds, 'total_rtr' => $totalRTR, 'ctd' => $ctd];
    }

    public static function putMerchantQueryDaysFilter(Builder $merchantQuery, $days, $default_date)
    {
        if ($days !== null) {
            $endDays = ($days == 0) ? 60 : $days + 29;
            $merchantQuery->whereRaw("DATEDIFF('".$default_date."', DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id = users.id) DAY)) >=".$days);
            if ($days < 150) {
                $merchantQuery->whereRaw("DATEDIFF('".$default_date."', DATE_ADD(merchants.last_payment_date, INTERVAL (SELECT lag_time FROM users WHERE merchants.lender_id = users.id) DAY)) <= {$endDays}");
            }
        }

        return $merchantQuery;
    }

    public static function merchantDefaultAmountColumn($merchant_day = 0, string $merchantStatuses = '')
    {
        if (! empty($merchantStatuses)) {
            return DB::raw('( '.$merchant_day.'
					*
					(
						SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                        -
                        ( 
                            SUM( 
                                IF( 
                                    sub_status_id IN ('.$merchantStatuses.'),
                                    ( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee ),
                                    ( 
                                        IF( 
                                            (merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                                            <
                                            ( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee ),
                                            ( merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission),
                                            ( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee )

                                        )
                                    )
								)
							)
                        )
                    )
                ) as default_amount');
        }

        return DB::raw('SUM( '.$merchant_day.'
			*
			(
				( merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission)
                -
				IF( 
					(merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee),
					(merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee),
					0
                )

            )
        ) AS default_amount');
    }

    public static function merchantInvestorAmountColumn($merchant_day = 0, $isMerchant = false)
    {
        if ($isMerchant) {
            return DB::raw('SUM(
	                    '.$merchant_day.'
						*
						(
							merchant_user.invest_rtr 
							+  
							IF( old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate - factor_rate ) ) , 0) 
	                        -
	                        merchant_user.invest_rtr * ( merchant_user.mgmnt_fee ) / 100
	                        +  
                            IF( old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate - factor_rate ) * ( merchant_user.mgmnt_fee ) / 100 ), 0 )
	                        -
	                        IF ( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee, merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee, 0)
	                    )
					) as investor_rtr');
        }

        return DB::raw('SUM(
	                    '.$merchant_day.'
						*
						(
							( 
								merchant_user.invest_rtr +  IF( old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate - factor_rate ) ) , 0) 
							)
	                        -
	                        ( 
	                            merchant_user.invest_rtr * ( merchant_user.mgmnt_fee ) / 100
	                            +  
	                            IF( old_factor_rate > factor_rate, ( merchant_user.amount * ( old_factor_rate - factor_rate ) * ( merchant_user.mgmnt_fee ) / 100 ), 0 )
	                        )
	                        -
	                        ( 
	                            IF ( merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee, merchant_user.paid_participant_ishare - merchant_user.paid_mgmnt_fee, 0)
	                        )
	                    )
					) as investor_rtr');
    }
}
