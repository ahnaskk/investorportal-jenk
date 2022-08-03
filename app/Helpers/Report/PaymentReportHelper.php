<?php

namespace App\Helpers\Report;

use function App\Helpers\api_download_url;
use function App\Helpers\modelQuerySql;
use App\Merchant;
use App\User;
use App\Settings;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Exports\Data_arrExport;
use App\Helpers\PaymentReportHelper as PaymentReportHelpers;

class PaymentReportHelper
{
    public static function getPaymentLeftReportColumns()
    {
        return [['data' => 'id', 'name' => 'id', 'title' => 'ID', 'orderable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant', 'searchable' => true, 'orderable' => true], ['data' => 'rtr', 'name' => 'rtr', 'title' => 'RTR (Balance with fee)', 'orderable' => false], ['data' => 'payment_amount', 'name' => 'payment_amount', 'title' => 'Payment Amount', 'orderable' => false], ['data' => 'overpayment', 'name' => 'overpayment', 'title' => 'Over Payment', 'orderable' => false, 'searchable' => false], ['data' => 'total_payments', 'name' => 'pmnts', 'title' => 'Total Payments', 'orderable' => false], ['data' => 'rtr_by_payment_amount', 'name' => 'rtr_by_payment_amount', 'title' => 'RTR/Payment Amount', 'orderable' => false, 'searchable' => false], ['data' => 'payments_left', 'name' => 'payments_left', 'title' => 'Payments Left', 'orderable' => false, 'searchable' => false]];
    }

    public static function downloadReport(Request $request)
    {
        $request->merge(['is_export' => true]);
        ExportCsvReportHelper::getDownload(self::getColumns(), self::getReport($request), time().'-'.'Payment Report');
    }

    public static function getPaymentLeftReport(Request $request)
    {
        $company = $request->input('company', 0);
        $merchantIds = $request->input('merchants', []);
        $lenderIds = $request->input('lenders', []);
        $subStatusIds = $request->input('sub_status', 0);
        $latePaymentDays = $request->input('late_payment', 0);
        $isExport = $request->input('is_export') == 'yes';
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $subInvestors = (empty($permission)) ? User::investors()->pluck('id')->toArray() : [];
        $companyInvestors = User::where('id', '>', 0);
        if ($company) {
            $companyInvestors->where('company', $company);
        }
        $companyInvestors = $companyInvestors->pluck('id')->toArray();
        $merchantQuery = Merchant::with(['investments' => function ($inner) use ($permission, $subInvestors, $company, $companyInvestors) {
            $inner->select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'))->whereIn('merchant_user.status', [1, 3]);
            $inner->groupBy('merchant_id');
            if (empty($permission)) {
                $inner->whereIn('merchant_user.user_id', $subInvestors);
            }
            $inner->whereIn('merchant_user.user_id', $companyInvestors);
        }])->whereHas('investmentData', function ($inner) use ($permission, $subInvestors, $company, $companyInvestors) {
            $inner->select(DB::raw('SUM(merchant_user.invest_rtr) as invest_rtr'))->whereIn('merchant_user.status', [1, 3]);
            $inner->groupBy('merchant_id');
            if (empty($permission)) {
                $inner->whereIn('merchant_user.user_id', $subInvestors);
            }
            if ($company != null) {
                $inner->whereIn('merchant_user.user_id', $companyInvestors);
            }
        })->select('merchants.id', 'merchants.name', 'rtr', 'payment_amount', 'pmnts', 'complete_percentage')->with(['participantPayment' => function ($inner) use ($permission, $subInvestors, $company, $companyInvestors) {
            $inner->where('participent_payments.is_payment', 1);
            $inner->rightjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->where('participent_payments.is_payment', 1)->groupBy('payment_investors.id');
            $inner->select('participent_payment_id', 'user_id', 'participent_payments.payment', 'participent_payments.merchant_id', 'participent_payments.id', 'participent_payments.payment_date', DB::raw('SUM(payment_investors.participant_share) as participant_share'), DB::raw('SUM(payment_investors.overpayment) as overpayment'));
            if ($company != null) {
                $inner->whereIn('payment_investors.user_id', $companyInvestors);
            }
            $inner->orderByDesc('participent_payment_id');
            if (empty($permission)) {
                $inner->whereIn('payment_investors.user_id', $subInvestors);
            }
        }]);
        $merchantQuery->where('merchants.active_status', 1);
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            $merchantQuery->whereIn('merchants.id', $merchantIds);
        }
        if (is_array($subStatusIds) and count($subStatusIds) > 0) {
            $merchantQuery->whereIn('merchants.sub_status_id', $subStatusIds);
        }
        if (is_array($lenderIds) and count($lenderIds) > 0) {
            $merchantQuery->whereIn('merchants.lender_id', $lenderIds);
        }
        if (! empty($latePaymentDays)) {
            if ($latePaymentDays != 90) {
                $start = $latePaymentDays + 30;
                $end = $latePaymentDays;
                $merchantQuery->where(function ($query) use ($start, $end) {
                    $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+'.$end.') DAY)')->whereRaw('date(last_payment_date) >=  DATE_SUB(NOW(), INTERVAL (lag_time+'.$start.') DAY)');
                });
            } else {
                $merchantQuery->where(function ($query) use ($latePaymentDays) {
                    $query->whereRaw('date(last_payment_date) <=  DATE_SUB(NOW(), INTERVAL (lag_time+'.$latePaymentDays.') DAY)');
                });
            }
        }
        $merchantQuery->select('merchants.*');
        $datTable = \IPVueTable::of($merchantQuery);
        if ($isExport) {
            $datTable->skipPaging();
        }

        return $datTable->editColumn('name', function ($data) {
            return "<a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>";
        })->editColumn('rtr', function ($data) {
            $rtr = ($data->investments->sum('invest_rtr') - $data->participantPayment->sum('participant_share'));

            return FFM::dollar(($rtr > 0) ? $rtr : 0);
        })->editColumn('payment_amount', function ($data) {
            return FFM::dollar($data->participantPayment->sum('participant_share'));
        })->editColumn('total_payments', function ($data) {
            return $data->pmnts;
        })->editColumn('overpayment', function ($data) {
            $overpayment = $data->participantPayment->sum('overpayment');

            return FFM::dollar($overpayment);
        })->editColumn('rtr_by_payment_amount', function ($data) {
            if ($data->investments->sum('invest_rtr') > 0) {
                $investmentBalance = (float) $data->investments->sum('invest_rtr') - (float) $data->participantPayment->sum('participant_share');
                try {
                    $payment_left = (float) $investmentBalance / (((float) $data->investments->sum('invest_rtr') / $data->rtr) * (float) $data->payment_amount);
                } catch (\ErrorException $e) {
                    $payment_left = 0;
                }

                return round(($payment_left > 0) ? $payment_left : 0);
            } else {
                return 0;
            }
        })->editColumn('payments_left', function ($data) {
            $paidPayments = collect($data->participantPayment)->pluck('participent_payment_id')->unique()->count();
            $remainingPayments = $data->pmnts - $paidPayments;
            if ($data->complete_percentage > 99) {
                return 0;
            } else {
                return round(($remainingPayments > 0) ? $remainingPayments : 0);
            }
        })->with('download-url', api_download_url('payment-download'))->make(true);
    }

    public static function getColumns($fields = null)
    {
        $columns = [['className' => 'details-control', 'orderable' => false, 'data' => 'null', 'name' => 'participant_payment', 'defaultContent' => '', 'title' => '', 'searchable' => false], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant', 'orderable' => true, 'sortable' => true], ['data' => 'date_funded', 'name' => 'date_funded', 'defaultContent' => '', 'title' => 'Funded Date'], ['data' => 'id', 'name' => 'id', 'defaultContent' => '', 'title' => 'Merchant Id'], ['orderable' => true, 'data' => 'debited', 'name' => 'debited', 'defaultContent' => '', 'title' => 'Debited'], ['orderable' => false, 'data' => 'payments', 'name' => 'payments', 'defaultContent' => '', 'title' => 'Total Payments'], ['orderable' => false, 'data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'defaultContent' => '', 'title' => 'Management Fee'], ['orderable' => false, 'data' => 'syndicate', 'name' => 'syndicate', 'defaultContent' => '', 'title' => 'Net amount'], ['orderable' => false, 'data' => 'principal', 'name' => 'principal', 'defaultContent' => '', 'title' => 'Principal'], ['orderable' => false, 'data' => 'profit', 'name' => 'profit', 'defaultContent' => '', 'title' => 'Profit'], ['orderable' => false, 'data' => 'code', 'name' => 'code', 'defaultContent' => '', 'title' => 'Last R code'], ['orderable' => true, 'data' => 'last_payment_date', 'name' => 'last_payment_date', 'defaultContent' => '', 'title' => 'Last Payment Date'], ['orderable' => true, 'data' => 'last_payment_amount', 'name' => 'last_payment_amount', 'defaultContent' => '', 'title' => 'Last Payment Amount'], ['orderable' => false, 'data' => 'participant_rtr', 'name' => 'participant_rtr', 'defaultContent' => '', 'title' => 'Participant RTR'], ['orderable' => false, 'data' => 'net_balance', 'name' => 'net_balance', 'defaultContent' => '', 'title' => 'Net Zero Balance'], ['orderable' => false, 'data' => 'participant_rtr_balance', 'name' => 'participant_rtr_balance', 'defaultContent' => '', 'title' => 'Participant RTR Balance']];
        if (! empty($fields[0])) {
            foreach ($fields as $key => $value) {
                foreach ($columns as $key1 => $value1) {
                    if (($value == $value1['data'])) {
                        $columns[$key1]['class'] = ' ';
                    } else {
                        $columns[$key1]['class'] = 'hidden-column';
                    }
                }
            }
        }

        return $columns;
    }

    public static function getReport(Request $request)
    {
        $dateType = $request->input('date_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $merchantIds = $request->input('merchants');
        $investorIds = $request->input('investors');
        $lenderIds = $request->input('lenders');
        $startTime = $request->input('time_start');
        $endTime = $request->input('time_end');
        $paymentType = $request->input('payment_type');
        $owner = $request->input('owner');
        $subStatusIds = $request->input('statuses');
        $advanceType = $request->input('advance_type');
        $fields = $request->input('fields');
        $investorTypes = $request->input('investor_type');
        $rCodeIds = $request->input('rcode');
        $overpayment = $request->input('overpayment');
        $labelId = $request->input('label');
        $subInvestors = (Auth::user()->hasRole(['company'])) ? User::investors()->pluck('id')->toArray() : [];
        if ($dateType == 'true') {
            if ($startTime != '') {
                $startDate = $startDate.' '.$startTime;
            }
            if ($endTime != '') {
                $endDate = $endDate.' '.$endTime;
            }
        }
        $type_investors = [];
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $companyUserQuery = DB::table('users')->where('company', $userId);
            }
        } else {
            $companyUserQuery = DB::table('users');
        }
        if (! empty($investorIds) && is_array($investorIds)) {
            $companyUserQuery = $companyUserQuery->whereIn('id', $investorIds);
        }
        if (! empty($investorTypes) && is_array($investorTypes)) {
            $companyUserQuery = $companyUserQuery->whereIn('investor_type', $investorTypes);
        }
        $company_users = $companyUserQuery->pluck('id')->toArray();
        if ($subInvestors) {
            $permission = 0;
        }
        $table_field = ($dateType == 'true') ? 'participent_payments.created_at' : 'payment_date';
        if ($paymentType != null) {
            $paymentType = ($paymentType == 'credit') ? '1' : '0';
        }
        $payment_query = '';
        if ($paymentType != '') {
            $payment_query = "AND payment_type=$paymentType";
        }
        $rCodeWhereQuery = '';
        $overPaymentWhereQuery = '';
        $overPaymentWhereQuery1 = '';
        if ($overpayment == 1) {
            $overPaymentWhereQuery = ' AND payment_investors.overpayment!=0';
            $overPaymentWhereQuery1 = ' AND merchant_user.paid_participant_ishare > merchant_user.invest_rtr';
        }
        if ($rCodeIds != null) {
            $rCodes = implode(',', $rCodeIds);
            $rCodeWhereQuery .= 'AND rcode in ('.$rCodes.')';
        }
        $dateFormat = ($dateType == 'true') ? 'Y-m-d H:i' : 'Y-m-d';
        $outDateFormat = ($dateType == 'true') ? 'Y-m-d H:i:s' : 'Y-m-d';
        if (! empty($startDate)) {
            try {
                $startDate = Carbon::createFromFormat($dateFormat, $startDate)->format($outDateFormat);
            } catch (InvalidFormatException $e) {
                $startDate = @(explode(' ', $startDate))[0];
            }
        }
        if (! empty($endDate)) {
            try {
                $endDate = Carbon::createFromFormat($dateFormat, $endDate)->format($outDateFormat);
            } catch (InvalidFormatException $e) {
                $endDate = @(explode(' ', $endDate))[0];
            }
        }
        $debitPaymentQuery = '';
        $queryParticipant = '';
        $dateWhereQuery = '';
        if ($startDate != null && $endDate != null) {
            $debitPaymentQuery = DB::raw("(SELECT SUM(payment) AS debited, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND participent_payments.is_payment = 1 AND $table_field >= '$startDate' AND $table_field <= '$endDate' $payment_query $rCodeWhereQuery GROUP BY participent_payments.merchant_id) AS debited_payments");
            $dateWhereQuery = "AND $table_field >= '$startDate' AND $table_field <= '$endDate'";
        } elseif ($startDate != null) {
            $debitPaymentQuery = DB::raw("(SELECT SUM(payment) AS debited, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND participent_payments.is_payment = 1 AND $table_field >= '$startDate' $payment_query $rCodeWhereQuery  GROUP BY participent_payments.merchant_id)  AS debited_payments");
            $dateWhereQuery = "AND $table_field >= '$startDate'";
        } elseif ($endDate != null) {
            $debitPaymentQuery = DB::raw("(SELECT SUM(payment) AS debited, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND participent_payments.is_payment = 1 AND $table_field <= '$endDate' $payment_query $rCodeWhereQuery  GROUP BY participent_payments.merchant_id)  AS debited_payments");
            $dateWhereQuery = "AND $table_field <= '$endDate'";
        } else {
            $debitPaymentQuery = DB::raw("(SELECT SUM(payment) AS debited, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND participent_payments.is_payment = 1 $payment_query $rCodeWhereQuery  GROUP BY participent_payments.merchant_id)  AS debited_payments");
        }
        if ($startDate) {
            $dateWhereQuery_old = "AND $table_field < '$startDate'";
        } else {
            $dateWhereQuery_old = "AND $table_field <  '1970/01/01' ";
        }
        $investorWhereQuery = '';
        $builder = Merchant::where('merchants.id', '>', 0);
        $assignedMerchantQuery = DB::table('merchant_user')->whereRaw('user_id in ('.modelQuerySql($companyUserQuery->select('id')).')');
        if ($overpayment == 1) {
            $assignedMerchantQuery->whereColumn('paid_participant_ishare', '>', 'invest_rtr');
        }
        $companyUserIds = implode(',', $company_users);
        if (! $companyUserIds) {
            $companyUserIds = 0;
        }
        $assignedMerchantIds = $assignedMerchantQuery->pluck('merchant_id')->unique();
        $builder->join('merchant_user', 'merchant_user.merchant_id', '=', 'merchants.id')->whereRaw('merchant_user.user_id in ('.$companyUserIds.')');
        $builder->whereIn('merchants.id', $assignedMerchantIds);
        $investorWhereQuery = 'AND user_id in ('.$companyUserIds.')';
        $investorWhereQuery = '';
        $merchantFilterQuery = '';
        $userQuery = 'AND user_id in ('.$companyUserIds.')';
        $builder->groupBy('merchants.id')->orderBy('merchants.id')->orderBy('merchants.name');
        if ($lenderIds) {
            $builder->whereIn('lender_id', $lenderIds);
        }
        if ($subStatusIds) {
            $builder->whereIn('sub_status_id', $subStatusIds);
        }
        if ($advanceType) {
            $builder->whereIn('advance_type', $advanceType);
        }
        if ($labelId) {
            $builder->where('label', $labelId);
        }
        if ($merchantIds != null) {
            $merchantFilterQuery = ' AND participent_payments.merchant_id IN ('.implode(',', $merchantIds).')';
            $builder = $builder->whereIn('merchants.id', $merchantIds);
        }
        if ($paymentType != '') {
            $queryParticipant = " AND payment_type = '$paymentType'";
        }
        if ($rCodeIds != null) {
            $queryParticipant = ' AND rcode IN ('.implode(', ', $rCodeIds).')';
        }
        $start = request()->input('start');
        $limit = request()->input('length');
        $limitQuery = '';
        if (request()->input('report_totals') != 1 && ! empty($limit)) {
            $limitQuery = ' LIMIT '.$limit.' OFFSET '.($start * $limit);
        }
        $builder->where('active_status', 1)->leftJoin(DB::raw("(SELECT SUM(payment_investors.profit) as profit,
                SUM(payment_investors.principal) AS principal, 
                SUM(payment_investors.mgmnt_fee) AS mgmnt_fee,
                SUM(payment_investors.participant_share) AS participant_share,
                participent_payments.merchant_id FROM payment_investors  
                LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id 
                WHERE participent_payments.merchant_id > 0 
                $merchantFilterQuery $payment_query $rCodeWhereQuery $investorWhereQuery $dateWhereQuery $overPaymentWhereQuery $userQuery 
                GROUP BY participent_payments.merchant_id) as merch_payment_sub"), 'merch_payment_sub.merchant_id', '=', 'merchants.id')->leftJoin(DB::raw("(SELECT SUM(payment_investors.participant_share - payment_investors.mgmnt_fee) as net_balance, participent_payments.merchant_id FROM payment_investors  LEFT JOIN participent_payments on
                payment_investors.participent_payment_id=participent_payments.id 
                WHERE participent_payments.merchant_id > 0
                AND participent_payments.merchant_id IN (SELECT merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 
                $merchantFilterQuery  $dateWhereQuery $queryParticipant )
                $merchantFilterQuery $dateWhereQuery_old $payment_query $rCodeWhereQuery $investorWhereQuery $overPaymentWhereQuery $userQuery GROUP BY participent_payments.merchant_id ORDER BY participent_payments.merchant_id ASC $limitQuery) as net_balance_payments"), 'net_balance_payments.merchant_id', '=', 'merchants.id')->leftJoin(DB::raw('(SELECT code, rcode.id FROM rcode GROUP BY rcode.id) code_merchant'), 'code_merchant.id', '=', 'merchants.last_rcode')->leftJoin(DB::raw('(SELECT SUBSTRING_INDEX(GROUP_CONCAT(payment ORDER BY payment_date DESC), ",", 1) AS last_payment_amount, participent_payments.merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 AND payment > 0 GROUP BY participent_payments.merchant_id ORDER BY payment_date DESC ) AS last_payment_amount_payments'), 'last_payment_amount_payments.merchant_id', '=', 'merchants.id')->leftJoin($debitPaymentQuery, 'debited_payments.merchant_id', '=', 'merchants.id');
        $builder->join(DB::raw(" (SELECT merchant_id, id FROM participent_payments WHERE merchant_id > 0 $merchantFilterQuery  $dateWhereQuery $queryParticipant GROUP BY merchant_id) AS p_payment_sub "), 'p_payment_sub.merchant_id', '=', 'merchants.id');
        $builder->select('merchants.id', 'merchants.name', 'merchants.rtr', 'merchants.last_rcode', 'merchants.last_payment_date', 'merchants.date_funded', DB::raw('debited_payments.debited, 
                merch_payment_sub.profit,
                merch_payment_sub.principal, 
                merch_payment_sub.mgmnt_fee, 
                merch_payment_sub.participant_share, 
                SUM(merchant_user.amount) as amount, 
                SUM(merchant_user.paid_participant_ishare) AS paid_participant_ishare,
                SUM(merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100) AS mgmnt_fee_amount,
                SUM(merchant_user.invest_rtr) as invest_rtr,
                SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) AS invested_amount,
                net_balance_payments.net_balance, 
                code_merchant.code, 
                last_payment_amount_payments.last_payment_amount,
                SUM( (merchant_user.amount*old_factor_rate) ) as settled_rtr
            '));
        $data = $payments = $builder->get();
        $result = $payments;
        $filter_array = ['date_type' => $dateType, 'sDate' => $startDate, 'eDate' => $endDate, 'merchants' => $merchantIds, 'investors' => $investorIds, 'lenders' => $lenderIds, 'subinvestors' => $subInvestors, 'stime' => $startTime, 'etime' => $endTime, 'payment_type' => $paymentType, 'owner' => $owner, 'sub_statuses' => $subStatusIds, 'advance_type' => $advanceType, 'investor_type' => $investorTypes, 'rcode' => $rCodeIds, 'overpayment' => $overpayment];
        Session::put('search_filter', $filter_array);
        $totalPayments = $totalProfit = $totalPrincipal = $totalNetBalance = $totalManagementFee = $t_rtr = $t_participant_share = $totalParticipantRtr = $totalParticipantRtrBalance = $totalNetBalance_balance = 0;
        if (request()->input('report_totals') == 1) {
            $paymentCollection = collect($payments);
            $totalProfit = collect($paymentCollection)->pluck('profit')->sum();
            $totalPrincipal = collect($paymentCollection)->pluck('principal')->sum();
            $participantShare = collect($paymentCollection)->pluck('participant_share')->sum();
            $totalManagementFee = collect($paymentCollection)->pluck('mgmnt_fee')->sum();
            $totalParticipantRtr = collect($paymentCollection)->pluck('invest_rtr')->sum();
            $totalNetBalance = $totalPayments - $totalManagementFee;
            if ($totalPayments > $totalParticipantRtr) {
                $totalParticipantRtrBalance = '0.00';
            } else {
                $totalParticipantRtrBalance = $totalParticipantRtr - ($totalPayments);
            }
        }

        return \IPVueTable::of($data)->editColumn('participant_payment', function ($partpayment) {
        })->addColumn('debited', function ($data) {
            return FFM::dollar($data->debited);
        })->addColumn('participant_rtr_balance', function ($data) {
            $rtr = $data->rtr;
            if ($data->participant_share > $data->invest_rtr) {
                return FFM::dollar(0.00);
            } else {
                return FFM::dollar($data->invest_rtr - $data->mgmnt_fee_amount - ($data->net_balance + $data->participant_share - $data->mgmnt_fee));
            }
        })->addColumn('net_balance', function ($data) {
            $net_balance = ($data->invested_amount - ($data->net_balance + $data->participant_share - $data->mgmnt_fee));

            return FFM::dollar($net_balance > 0 ? $net_balance : 0);
        })->addColumn('participant_rtr', function ($data) {
            $settled_rtr = isset($data->settled_rtr) ? $data->settled_rtr : 0;
            if ($settled_rtr != 0) {
                $settled_rtr = '( was '.FFM::dollar($data->settled_rtr).')';
            } else {
                $settled_rtr = '';
            }

            return FFM::dollar($data->invest_rtr).$settled_rtr;
        })->addColumn('last_payment_amount', function ($data) {
            return FFM::dollar($data->last_payment_amount);
        })->addColumn('payments', function ($data) {
            return FFM::dollar($data->participant_share);
        })->addColumn('syndicate', function ($data) {
            return FFM::dollar($data->participant_share - $data->mgmnt_fee);
        })->addColumn('mgmnt_fee', function ($data) {
            return FFM::dollar($data->mgmnt_fee);
        })->addColumn('profit', function ($data) {
            return FFM::dollar($data->profit);
        })->addColumn('principal', function ($data) {
            return FFM::dollar($data->principal);
        })->editColumn('name', function ($data) {
            return "<a target='blank' style = 'display:none'> $data->name</a><a target='blank' href='".\URL::to('/admin/merchants/view', $data->id)."'>$data->name</a>";
        })->editColumn('date_funded', function ($data) {
            return date('m-d-Y', strtotime($data->date_funded));
        })->editColumn('last_payment_date', function ($data) {
            return date('m-d-Y', strtotime($data->last_payment_date));
        })->with('total_debited', FFM::dollar(0))->with('total_company', FFM::dollar($totalPayments))->with('total_profit', FFM::dollar($totalProfit))->with('total_principle', FFM::dollar($totalPrincipal))->with('total_syndicate', FFM::dollar($totalNetBalance))->with('total_mgmnt', FFM::dollar($totalManagementFee))->with('total_participant_rtr_balance', FFM::dollar($totalParticipantRtrBalance))->make();
    }

    public static function paymentsReport($request,$tableBuilder,$role,$label)
    {
        $historic_status = Settings::value('historic_status');
        $selected_investor = ($request->user_id) ? $request->user_id : '';
        $sdate = $request->start_date;
        $edate = ! empty($request->end_date) ? $request->end_date : date('Y-m-d', strtotime('+5 days'));
        // $time_start =  ($request->time_start) ? $request->time_start : '00:00';
        //  $time_end =    ($request->time_end) ? $request->time_end : '23:59';
        $page_title = 'Payment Report';
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $subinvestors = [];
        if ($request->owner) {
            $permission = 0;
            $userId = $request->owner;
        }
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->whereIn('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        // $request->date_type = true;
        $tableBuilder->ajax([
            'url'  => route('admin::reports::payments-records'), 
            'type' => 'post',
            'data' => 'function(d) {
                d._token              = "'.csrf_token().'";
                d.date_type           = $("#date_type").is(\':checked\') ? true : false;
                d.time_start          = $("#time_start:visible").val();
                d.time_end            = $("#time_end:visible").val();
                d.start_date          = $("#date_start").val();
                d.end_date            = $("#date_end").val();
                d.merchant_id         = $("#merchant_id").val();d.investors = $("#investors").val();
                d.lenders             = $("#lenders").val();
                d.statuses            = $("#statuses").val();
                d.transaction_id      = $("#transaction_id").val();
                d.advance_type        = $("#advance_type").val();
                d.payment_type        = $("#payment_type").val();
                d.owner               = $("#owner").val();
                d.fields              = $("#fields").val();
                d.investor_type       = $("#investor_type").val();
                d.rcode               = $("#rcode").val();
                d.overpayment         = $("input[name=overpayment]:checked").val();
                d.report_totals       = $("input[name=report_totals]:checked").val();
                d.label               = $("#label").val();
                d.mode_of_payment     = $("#payment-method").val();
                d.payout_frequency    = $("#payout_frequency").val();
                d.investor_label      = $("#investor_label").val();
                d.historic_status     = $("input[name=historic_status]:checked").val();
                d.filter_by_agent_fee = $("input[name=filter_by_agent_fee]:checked").val();
                d.active_status       = $("input[name=active_status]:checked").val();
                d.velocity_owned      = $("input[name=velocity_owned]:checked").val();
            }'
        ]);
        $tableBuilder->parameters([
            'footerCallback' => 'function(t,o,a,l,m){
                var n=this.api(),o=table.ajax.json();( ($("input[name=report_totals]").is(":checked") ) ? ( $(n.column(0).footer()).html(o.Total),$(n.column(6).footer()).html(o.total_company),$(n.column(7).footer()).html(o.total_mgmnt),$(n.column(8).footer()).html(o.total_syndicate),$(n.column(9).footer()).html(o.total_principle),$(n.column(10).footer()).html(o.total_profit),$(n.column(13).footer()).html(o.total_participant_rtr) ) : ($(n.column(0).footer()).html(""),$(n.column(5).footer()).html(""),$(n.column(6).footer()).html(""),$(n.column(7).footer()).html(""),$(n.column(8).footer()).html(""),$(n.column(9).footer()).html(""),$(n.column(10).footer()).html(""),$(n.column(13).footer()).html("")) )
            }',
            'drawCallback' => "function(){ $('[data-toggle=\"popover\"]').popover();}", 
            'aoColumnDefs' => [['sClass' => 'hidden-column', 'aTargets' => []]], 
            'order' => [[0, 'asc']], 
            'pagingType' => 'input'
        ]);
        $columns = self::getColumnsReport();
        foreach ($columns as $index => $column) {
            if ($column['name'] == 'net_balance' or $column['name'] == 'particaipant_rtr_balance') {
                $column['orderable'] = false;
            } else {
                $column['orderable'] = true;
            }
            $columns[$index] = $column;
        }
        $tableBuilder->columns($columns);
        $merchant = Merchant::where('active_status', 1);
        if (empty($permission)) {
            $merchant->whereHas('investmentData', function ($q) use ($subinvestors, $permission) {
                if (empty($permission)) {
                    $q->whereIn('user_id', $subinvestors);
                }
            });
        }
        $merchants = $merchant->pluck('name', 'id');
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->select(DB::raw("upper(users.name) as name"), 'users.id')
        ->pluck('name','id')->toArray();

        $lenders = $role->allLenders()->pluck('name', 'id');
        $fields = ['date_funded' => 'Funded Date', 'id' => 'Merchant Id', 'debited' => 'Debited', 'payments' => 'Total Payments', 'mgmnt_fee' => 'Management Fee', 'synd_fee' => 'Syndication Fee', 'syndicate' => 'Net Amount', 'principal' => 'Principal', 'profit' => 'Profit'];
        $sub_statuses = DB::table('sub_statuses')->orderBy('name')->pluck('name', 'id')->toArray();
        $label = $label->getAll()->pluck('name', 'id');
        $investor_types = User::getInvestorType();
        $recurrence_types = [1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily', 4 => 'On Demand'];
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();
        $companies = array_reverse($companies, true);
        $rcodes = DB::table('rcode')->pluck(DB::raw("CONCAT(description,' (',code,') ') AS name"), 'id');
        $payment_methods = ['ach' => 'ACH', 'manual' => 'Manual', 'credit_card' => 'Credit Card Payment'];
        return [
            'tableBuilder'      => $tableBuilder,
            'page_title'        => $page_title,
            'merchants'         => $merchants,
            'investors'         => $investors,
            'lenders'           => $lenders,
            'selected_investor' => $selected_investor,
            'sdate'             => $sdate,
            'edate'             => $edate,
            'sub_statuses'      => $sub_statuses,
            'fields'            => $fields,
            'investor_types'    => $investor_types,
            'companies'         => $companies,
            'rcodes'            => $rcodes,
            'label'             => $label,
            'payment_methods'   => $payment_methods,
            'recurrence_types'  => $recurrence_types,
            'historic_status'   => $historic_status
        ];
    }
    
    public static function getColumnsReport($fields = null)
    {
        $array = [
            ['className' => 'details-control', 'orderable' => false, 'data' => 'null', 'name' => 'participant_payment', 'defaultContent' => '', 'title' => '', 'searchable' => false], 
            ['data' => 'name', 'name' => 'name', 'title' => 'Merchant', 'orderable' => true, 'sortable' => true],
            ['data' => 'id', 'name' => 'id', 'defaultContent' => '', 'title' => 'Merchant Id'],
            ['data' => 'substatus', 'name' => 'substatus', 'defaultContent' => '', 'title' => 'Status'],
            ['data' => 'date_funded', 'name' => 'date_funded', 'defaultContent' => '', 'title' => 'Funded Date'], 
            ['orderable' => true, 'data' => 'debited', 'name' => 'debited', 'defaultContent' => '', 'title' => 'Debited','className'=>'text-right'], 
            ['orderable' => false, 'data' => 'actual_participant_share', 'name' => 'actual_participant_share', 'defaultContent' => '', 'title' => 'Total Payments','className'=>'text-right'],
            ['orderable' => false, 'data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'defaultContent' => '', 'title' => 'Management Fee','className'=>'text-right'],
            ['orderable' => false, 'data' => 'syndicate', 'name' => 'syndicate', 'defaultContent' => '', 'title' => 'Net amount','className'=>'text-right'],
            ['orderable' => false, 'data' => 'principal', 'name' => 'principal', 'defaultContent' => '', 'title' => 'Principal','className'=>'text-right'],
            ['orderable' => false, 'data' => 'profit', 'name' => 'profit', 'defaultContent' => '', 'title' => 'Profit','className'=>'text-right'],
            ['orderable' => false, 'data' => 'code', 'name' => 'code', 'defaultContent' => '', 'title' => 'Last R code'],
            ['orderable' => true, 'data' => 'last_payment_date', 'name' => 'last_payment_date', 'defaultContent' => '', 'title' => 'Last Payment Date'],
            ['orderable' => true, 'data' => 'last_payment_amount', 'name' => 'last_payment_amount', 'defaultContent' => '', 'title' => 'Last Payment Amount','className'=>'text-right'],
            ['orderable' => false, 'data' => 'particaipant_rtr', 'name' => 'particaipant_rtr', 'defaultContent' => '', 'title' => 'Participant RTR','className'=>'text-right'],
            ['orderable' => false, 'data' => 'net_balance', 'name' => 'net_balance', 'defaultContent' => '', 'title' => 'Net Zero Balance','className'=>'text-right'],
            ['orderable' => false, 'data' => 'particaipant_rtr_balance', 'name' => 'particaipant_rtr_balance', 'defaultContent' => '', 'title' => 'Participant RTR Balance','className'=>'text-right']
        ];
        if (! empty($fields[0])) {
            foreach ($fields as $key => $value) {
                foreach ($array as $key1 => $value1) {
                    if (($value == $value1['data'])) {
                        $array[$key1]['class'] = ' ';
                    } else {
                        $array[$key1]['class'] = 'hidden-column';
                    }
                }
            }
        }
        return $array;
    }

    public static function paymentExport($request,$merchant,$role)
    {
        $date_type = 'false';
        if (isset($request->date_type)) {
            $date_type = $request->date_type;
        }
        if($date_type!='true'){
            $request->time_start = $request->time_end = NULL;
        }
        $investors = $request->investors;
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }
        if (! empty($request->investor_type) && is_array($request->investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $request->investor_type);
        }
        if ($request->owner) {
            $company_users_q = $company_users_q->whereIn('company', $request->owner);
        }
        if ($request->payout_frequency) {
            $company_users_q = $company_users_q->whereIn('notification_recurence', $request->payout_frequency);
        }
        $company_users = $company_users_q->pluck('id')->toArray();
        switch ($request->download) {
            case 'download':
                $fileName = 'Payment Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
                $user = $request->user();
                $total_amount = 0;
                if ($request->date_type == 'true') {
                        if($request->date_start==null && $request->time_start==null){
                            $startDate = "";
                        }
                        else{
                            $startDate = ET_To_UTC_Time($request->date_start.$request->time_start.':00', 'datetime');
                        }
                        if($request->date_end==null && $request->time_end==null){
                            $endDate = "";
                        }else{
                            $endDate = ET_To_UTC_Time($request->date_end.$request->time_end.':59', 'datetime');
                        }
                }else {
                    $startDate = ($request->date_start);
                    $endDate = ($request->date_end);
                }
                if($request->export_individual_checkbox){
                    $export_individual_checkbox = 1;
                }else{
                    $export_individual_checkbox = 0;
                }
                $velocity_owned = false;
                if(isset($request->velocity_owned)){
                $velocity_owned = true;
                }
                $details = \MTB::paymentData($request->date_type, $startDate, $endDate, $request->merchant_id, $request->investors, $request->lenders, $request->payment_type, $request->owner, $request->statuses, $request->investor_type, $request->fields, $request->rcode, $request->overpayment, $request->label, null, $request->mode_of_payment, $request->payout_frequency, $request->investor_label, $request->advance_type, $request->historic_status, $request->filter_by_agent_fee,$request->active_status,$request->transaction_id,$export_individual_checkbox,$velocity_owned);
                $details = $details->get();
                $total_count = count($details->toArray());
                $i = 1;
                $index = 0;
                $grand_payment = $grand_management_fee = $grand_syndication_fee = $grand_net_amount = 0;
                $total_1 = [];
                $total_2 = [];
                $total_3 = [];
                if ($request->export_checkbox == '') {
                    $excel_array[0] = ['No', 'Merchant', 'Merchant Id', 'Substatus', 'Funded Date', 'Investor', 'Payment Date', 'Debited', 'Total Payments', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last R Code', 'Last Payment Date', 'Last Payment Amount', 'Participant RTR', 'Old RTR', 'Net Zero Balance', 'Gross Participant RTR Balance','Net Participant RTR Balance'];
                } else {                    
                    $excel_array[0] = ['No', 'Merchant', 'Merchant Id', 'Substatus', 'Funded Date', 'Debited', 'Total Payments', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last R Code', 'Last Payment Date', 'Last Payment Amount', 'Participant RTR', 'Old RTR', 'Net Zero Balance', 'Gross Participant RTR Balance','Net Participant RTR Balance'];
                }
                if($request->export_individual_checkbox){
                    $excel_array[0] = ['No', 'Merchant', 'Merchant Id', 'Substatus','Our Funded Amount', 'Funded Date', 'Payment Date','Debited', 'Participant Share', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last R Code', 'Last Payment Date'];
                }
                $tot_payments = $tot_participant_rtr_bal = $tot_management_fee = $tot_net_amount = $tot_profit = $tot_principal = $tot_last_payment_amount = $tot_participant_rtr = $tot_old_rtr = $tot_netzero_balance = $tot_overpayment = $tot_participant_gross_rtr_bal = 0;
                $singleRow = [];
                $j = 1;

                if (! empty($details->toArray())) {
                    if($request->export_individual_checkbox){
                            $indvdl_tot_participant_share = $indvdl_tot_mgmnt_fee = $indvdl_tot_net_amount = $indvdl_tot_profit = $indvdl_tot_principal = 0;
                            foreach ($details as $key => $data) {
                                $debited = $data->participantPayment->unique('participent_payment_id')->sum('payment');
                                $data = $data->toArray();
                                $funded_amount = array_sum(array_column($data['investments'], 'funded_amount'));
                                foreach ($data['participant_payment'] as $partPayment) {
                                    $indvdl_tot_participant_share = $indvdl_tot_participant_share+$partPayment['participant_share'];
                                    $indvdl_tot_mgmnt_fee = $indvdl_tot_mgmnt_fee+$partPayment['mgmnt_fee'];
                                    $indvdl_tot_net_amount = $indvdl_tot_net_amount+$partPayment['final_participant_share'];
                                    $indvdl_tot_profit = $indvdl_tot_profit+$partPayment['profit'];
                                    $indvdl_tot_principal = $indvdl_tot_principal+$partPayment['principal'];
                                    $singleRow = [];
                                    $singleRow['No'] = $i;
                                    $singleRow['Merchant'] = $data['name'];
                                    $singleRow['Merchant Id'] = $data['id'];
                                    $singleRow['Substatus'] = $data['sub_status_name'];
                                    $singleRow['Our Funded Amount'] = FFM::dollar($funded_amount);
                                    $singleRow['Funded Date'] = date(\FFM::defaultDateFormat('db').'', strtotime($data['date_funded']));     
                                    $singleRow['Payment Date'] = FFM::date(($partPayment['payment_date']));                              
                                    $singleRow['Debited'] = FFM::dollar($partPayment['payment']);                                  
                                    $singleRow['Participant Share'] = FFM::dollar($partPayment['participant_share']);
                                    $singleRow['Management Fee'] = FFM::dollar($partPayment['mgmnt_fee']);
                                    $net_amount = $partPayment['final_participant_share'];
                                    $singleRow['Net amount'] = FFM::dollar($net_amount);
                                    $singleRow['Principal'] = FFM::dollar($partPayment['principal']);
                                    $singleRow['Profit'] = FFM::dollar($partPayment['profit']);
                                    $singleRow['Last R Code'] = $partPayment['code'];
                                    $singleRow['Last Payment Date'] = ($data['last_payment_date']!=null) ? date(\FFM::defaultDateFormat('db').'', strtotime($data['last_payment_date'])) :null;                                
                                    $excel_array[$i] = $singleRow;
                                    $i++;

                                }

                                if ($request->report_totals == 1) {                               
                                    $singleRow = [];   
                                    $singleRow['No'] = null;
                                    $singleRow['Merchant'] = null;
                                    $singleRow['Merchant Id'] = null;
                                    $singleRow['Substatus'] = null;
                                    $singleRow['Our Funded Amount'] = null;
                                    $singleRow['Funded Date'] = null;                                
                                    $singleRow['Payment Date'] = null;
                                    $singleRow['Debited'] = null;
                                    $singleRow['Participant Share'] = FFM::dollar($indvdl_tot_participant_share);
                                    $singleRow['Management Fee'] = FFM::dollar($indvdl_tot_mgmnt_fee);
                                    $singleRow['Net Amount'] = FFM::dollar($indvdl_tot_net_amount);
                                    $singleRow['Principal'] = FFM::dollar($indvdl_tot_principal);
                                    $singleRow['Profit'] = FFM::dollar($indvdl_tot_profit);
                                    $singleRow['Last R Code'] = null;
                                    $singleRow['Last Payment Date'] = null;                               
                                    $excel_array[$i] = $singleRow;
                                }
                            }

                    }else{
                    foreach ($details as $key => $data) {
                        $debited = $data->participantPayment->unique('participent_payment_id')->sum('payment');
                        $data = $data->toArray();
                        $invest_rtr = array_sum(array_column($data['investments'], 'invest_rtr'));
                        $inv_mgmnt_fee = array_sum(array_column($data['investments'], 'mgmnt_fee_amount'));
                        $settled_rtr = array_sum(array_column($data['investments'], 'settled_rtr'));
                        $investment_amount = array_sum(array_column($data['investments'], 'invested_amount'));
                        $participant_share = array_sum(array_column($data['participant_payment'], 'participant_share'));
                        $principal = array_sum(array_column($data['participant_payment'], 'principal'));
                        $profit = array_sum(array_column($data['participant_payment'], 'profit'));
                        $carry_profit = array_sum(array_column($data['carry_forward_profit'], 'carry_profit'));
                        if (round($principal, 2) != round($profit * -1, 2) || $participant_share >=0 ) {
                            $balance_rtr = $invest_rtr - $participant_share;
                            $managemnt_fee = array_sum(array_column($data['participant_payment'], 'mgmnt_fee'));
                            $syndication_fee = array_sum(array_column($data['participant_payment'], 'syndication_fee'));
                            $total_managemnt_fee = array_sum(array_column($data['investments'], 'mgmnt_fee_amount'));
                            $total_syndication_fee = array_sum(array_column($data['investments'], 'syndication_fee_amount'));
                            if(empty($request->rcode))
                            {
                                $principal -= $carry_profit;
                                $profit += $carry_profit;
                            }
                           
                            $debit = $debited;
                            $payments = $participant_share;
                            $syndication_fee = $syndication_fee;
                            $managemnt_fee = $managemnt_fee;
                            $net_amount = array_sum(array_column($data['participant_payment'], 'final_participant_share'));
                            $singleRow['No'] = $j;
                            $singleRow['Merchant'] = $data['name'];
                            $singleRow['Merchant Id'] = $data['id'];
                            $singleRow['Substatus'] = $data['sub_status_name'];
                            $singleRow['Funded Date'] = date(\FFM::defaultDateFormat('db').'', strtotime($data['date_funded']));
                            if ($request->export_checkbox == '') {
                                $singleRow['Investor'] = '';
                                $singleRow['Payment Date'] = '';
                            }
                            $singleRow['Debited'] = FFM::dollar($debit);
                            $singleRow['Total Payments'] = FFM::dollar($payments);
                            $singleRow['Management Fee'] = FFM::dollar($managemnt_fee);
                            $singleRow['Net Amount'] = FFM::dollar($net_amount);
                            if ($request->historic_status != null && in_array($data['sub_status_id'], [4, 22])) {
                                $principal = $principal + $profit;
                                $profit = $profit - $profit;
                            }
                            if ($request->historic_status != null && in_array($data['sub_status_id'], [18, 19, 20])) {
                                $adjuestmentAmount = $investment_amount - $principal;
                                $principal = $principal + $adjuestmentAmount;
                                $profit = $profit - $adjuestmentAmount;
                            }
                            $singleRow['Principal'] = FFM::dollar($principal);
                            $singleRow['Profit'] = FFM::dollar($profit);
                            $singleRow['Last R Code'] = $data['code'];
                            $singleRow['Last Payment Date'] = ($data['last_payment_date']!=null) ? date(\FFM::defaultDateFormat('db').'', strtotime($data['last_payment_date'])) : null;
                            $singleRow['Last Payment Amount'] = FFM::dollar($data['last_payment_amount']);
                            $singleRow['Participant RTR'] = FFM::dollar($invest_rtr-$inv_mgmnt_fee);
                            $singleRow['Old RTR'] = ($settled_rtr > $invest_rtr) ? FFM::dollar($settled_rtr) : FFM::dollar(0);
                             if (! empty($request->investors) && is_array($request->investors)) {
                                $net_balancd22 = ($investment_amount - ($data['full_final_participant_share'] + $net_amount));

                             }else{
                                $net_balancd22 = ($investment_amount - ($data['full_final_participant_share']+$data['t_agent_fee'] + $net_amount)-$data['agent_fee']);

                             }
                            
                            $singleRow['Net Zero Balance'] = $net_balancd22 > 0 ? FFM::dollar($net_balancd22) : FFM::dollar(0);
                            $netzero_balance = ($net_balancd22 > 0) ? $net_balancd22 : 0;
                            $old_rtr = ($settled_rtr > $invest_rtr) ? $settled_rtr : 0;
                            $net_zero = (($invest_rtr-$inv_mgmnt_fee) - ($data['full_final_participant_share'] + ($payments-$managemnt_fee)));
                            $net_zero = ($net_zero > 0) ? $net_zero : 0;
                            $gross_net_zero = $invest_rtr-($data['final_participant_share_with_fee']+$payments);
                            $gross_net_zero = ($gross_net_zero > 0) ? $gross_net_zero : 0;
                            $singleRow['Gross Participant RTR Balance'] = FFM::dollar($gross_net_zero);
                            $singleRow['Net Participant RTR Balance'] = FFM::dollar($net_zero);
                            $excel_array[$i] = $singleRow;

                            $tot_participant_rtr_bal += $net_zero;
                            $tot_participant_gross_rtr_bal += $gross_net_zero;
                            $tot_payments += $payments;
                            $tot_management_fee += $managemnt_fee;
                            $tot_net_amount += $net_amount;
                            $tot_profit += $profit;
                            $tot_principal += $principal;
                            $tot_last_payment_amount += $data['last_payment_amount'];
                            $tot_participant_rtr += ($invest_rtr-$inv_mgmnt_fee);
                            $tot_old_rtr += $old_rtr;
                            $tot_netzero_balance += $netzero_balance;
                            $i++;
                            $j++;

                            if ($request->export_checkbox == '') {
                                foreach ($data['participant_payment'] as $partPayment) {
                                    $singleRow = [];
                                    $singleRow['No'] = null;
                                    $singleRow['Merchant'] = null;
                                    $singleRow['Merchant Id'] = null;
                                    $singleRow['Substatus'] = null;
                                    $singleRow['Funded Date'] = null;
                                    $singleRow['Investor'] = strtoupper(User::where('id', $partPayment['user_id'])->value('name'));
                                    $singleRow['Payment Date'] = FFM::date(($partPayment['payment_date']));
                                    $singleRow['Debited'] = null;
                                    $singleRow['Total Payments'] = FFM::dollar($partPayment['payment']);
                                    $singleRow['Management Fee'] = FFM::dollar($partPayment['mgmnt_fee']);
                                    $net_amount = $partPayment['final_participant_share'];
                                    $singleRow['Net amount'] = FFM::dollar($net_amount);
                                    $singleRow['Principal'] = null;
                                    $singleRow['Profit'] = null;
                                    $singleRow['Last R Code'] = $partPayment['code'];
                                    $singleRow['Last Payment Date'] = null;
                                    $singleRow['Last Payment Amount'] = null;
                                    $singleRow['Participant RTR'] = null;
                                    $singleRow['Old RTR'] = null;
                                    $singleRow['Net Zero Balance'] = null;
                                    $singleRow['Gross Participant RTR Balance'] = null;
                                    $singleRow['Net Participant RTR Balance'] = null;
                                    $excel_array[$i] = $singleRow;
                                    $i++;
                                }
                            }
                        }

                        if ($request->report_totals == 1) {
                            if ($request->export_checkbox == '') {
                                $singleRow = [];
                            }

                            $singleRow['No'] = null;
                            $singleRow['Merchant'] = null;
                            $singleRow['Merchant Id'] = null;
                            $singleRow['Substatus'] = null;
                            $singleRow['Funded Date'] = null;
                            if ($request->export_checkbox == '') {
                                $singleRow['Investor)'] = null;
                            }
                            $singleRow['Payment Date'] = null;
                            $singleRow['Debited'] = null;
                            $singleRow['Total Payments'] = FFM::dollar($tot_payments);
                            $singleRow['Management Fee'] = FFM::dollar($tot_management_fee);
                            $singleRow['Net Amount'] = FFM::dollar($tot_net_amount);
                            $singleRow['Principal'] = FFM::dollar($tot_principal);
                            $singleRow['Profit'] = FFM::dollar($tot_profit);
                            $singleRow['Last R Code'] = null;
                            $singleRow['Last Payment Date'] = null;
                            $singleRow['Last Payment Amount'] = FFM::dollar($tot_last_payment_amount);
                            $singleRow['Participant RTR'] = FFM::dollar($tot_participant_rtr);
                            $singleRow['Old RTR'] = FFM::dollar($tot_old_rtr);
                            $singleRow['Net Zero Balance'] = FFM::dollar($tot_netzero_balance);
                            $singleRow['Gross Participant RTR Balance'] = FFM::dollar($tot_participant_gross_rtr_bal);
                            $singleRow['Net Participant RTR Balance'] = FFM::dollar($tot_participant_rtr_bal);
                            $excel_array[$i] = $singleRow;
                        } else {
                            if ($request->export_checkbox == '') {
                                $singleRow = [];
                            }

                            $singleRow['No'] = null;
                            $singleRow['Merchant'] = null;
                            $singleRow['Merchant Id'] = null;
                            $singleRow['Substatus'] = null;
                            $singleRow['Funded Date'] = null;
                            if ($request->export_checkbox == '') {
                                $singleRow['Investor)'] = null;
                            }
                            $singleRow['Payment Date'] = null;
                            $singleRow['Debited'] = null;
                            $singleRow['Total Payments'] = null; //FFM::dollar($tot_payments);
                            $singleRow['Management Fee'] = null; //FFM::dollar($tot_management_fee);
                            $singleRow['Net Amount'] = null; //FFM::dollar($tot_net_amount);
                            $singleRow['Principal'] = null; //FFM::dollar($tot_principal);
                            $singleRow['Profit'] = null; //FFM::dollar($tot_profit);
                            $singleRow['Last R Code'] = null;
                            $singleRow['Last Payment Date'] = null;
                            $singleRow['Last Payment Amount'] = null; //FFM::dollar($tot_last_payment_amount);
                            $singleRow['Participant RTR'] = null; //FFM::dollar($tot_participant_rtr);
                            $singleRow['Old RTR'] = null; //FFM::dollar($tot_old_rtr);
                            $singleRow['Net Zero Balance'] = null; //FFM::dollar($tot_netzero_balance);
                            $singleRow['Gross Participant RTR Balance'] = null;
                            $singleRow['Net Participant RTR Balance'] = null; //FFM::dollar($tot_participant_rtr_bal);
                            $excel_array[$i] = $singleRow;
                        }
                    }
                }
            }
                $export = new Data_arrExport($excel_array);

                return [
                    'fileName' => $fileName,'export' => $export
                ];
                break;
            case 'download-syndicate':
                $excel_array = [];
                $i = 0;
                $investors=array();
                if($request->overpayment!=1){
                $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id');
                $investors = $investors->where('user_has_roles.role_id',User::INVESTOR_ROLE);
                $investors = $investors->whereHas('company_relation',function ($query) {
                    $query->where('company_status',1);
                });

                if (! empty($request->investor_type) && is_array($request->investor_type)) {
                    $investors = $investors->whereIn('users.investor_type', $request->investor_type);
                }
                if (! empty($request->investors)) {
                    $investors = $investors->whereIn('users.id', $request->investors);
                }
                if ($request->owner) {
                    $investors = $investors->whereIn('users.company', $request->owner);
                }
                if ($request->investor_label != null) {
                    $investor_label = implode(',', $request->investor_label);
                    $investors = $company_users_q->whereRaw('json_contains(label, \'['.$investor_label.']\')');
                }
                if ($request->filter_by_agent_fee == 1) {
                    $investors = $role->allAgentFeeAccount();
                }
                if (($request->active_status) && $request->active_status == 1) {
                    $investors = $investors->where('active_status', 1);
                }
                if (($request->active_status) && $request->active_status == 2) {
                    $investors = $investors->where('active_status', 0);
                }
                if($request->velocity_owned){
                    $investors = $investors->where('velocity_owned', 1);
                }
                $investors = $investors->pluck('users.id',DB::raw('upper(users.name) as name'));
               }
                $rCode = $request->input('rCode');
                $offset = $request->input('offset', 0);
                $limit = $request->input('limit', 20);
                if (! empty($startDate)) {
                    $startDate = date('Y-m-d', strtotime($startDate));
                }
                if (! empty($endDate)) {
                    $endDate = date('Y-m-d', strtotime($endDate));
                }
                $excel_array['1'] = ['No', 'Merchant', 'Merchant Id', 'Funded Date', 'Total Payments', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last R Code', 'Last Payment Date', 'Last Payment Amount', 'Participant RTR', 'Old RTR', 'Net Zero Balance', 'Participant RTR Balance', 'Funded', 'Total Funded', 'Merchant Status'];
                if (! empty($investors)) {
                    foreach ($investors as $key => $inv_id) {
                        $investor_name = $key;
                        $date_type = 'false';
                        if (isset($request->date_type)) {
                            $date_type = $request->date_type;
                        }
                        if($date_type!='true'){
                            $request->time_start = $request->time_end = NULL;
                        }
                        list($reportData) = PaymentReportHelpers::investorDownloadData(ET_To_UTC_Time($request->date_start.$request->time_start), ET_To_UTC_Time($request->date_end.$request->time_end), $request->rcode, $inv_id, $request->merchant_id, $request->payment_type, $request->lenders, $request->label, $request->mode_of_payment, $request->statuses, $request->advance_type, $request->date_type, ET_To_UTC_Time($request->date_start.$request->time_start, 'time'), ET_To_UTC_Time($request->date_end.$request->time_end, 'time'), $request->payout_frequency, $request->historic_status,$request->overpayment,$request->active_status,$request->transaction_id);
                        $simpleReportFields = ['name', 'date_funded', 'id', 'last_payment_date', 'code'];
                        if (count($reportData) > 0) {
                            $excel_array[$i][] = $investor_name;
                            $k = 1;
                            $total_payment = $total_mgmnt_fee = $total_net_amount = $total_principal = $total_profit = $total_last_payment_amount = $total_funded = $total_participant_rtr = $total_participant_rtr_balance = $total_net_zero_bal = $total_invested = 0;
                            
                            foreach ($reportData as $key => $value) {
                                $net_zero_bal = ($value['investment_amount'] - ($value['net_balance'] + $value['final_participant_share']));
                                if(empty($request->rcode))
                                { 
                                    $value['profit'] += $value['carry_profit'];
                                    $value['principal'] -= $value['carry_profit'];

                                }
                                $netAmount = $value['final_participant_share'];
                                $net_bal = $net_zero_bal > 0 ? $net_zero_bal : 0;
                                $excel_array[$i.'+2']['No'] = $k;
                                $excel_array[$i.'+2']['Merchant'] = $value['name'];
                                $excel_array[$i.'+2']['Merchant Id'] = $value['id'];
                                $excel_array[$i.'+2']['Funded Date'] = \FFM::date($value['date_funded']);
                                $excel_array[$i.'+2']['Total Payments'] = \FFM::dollar($value['participant_share']);
                                $excel_array[$i.'+2']['Management Fee'] = \FFM::dollar($value['mgmnt_fee']);
                                $excel_array[$i.'+2']['Net Amount'] = \FFM::dollar($netAmount);
                                if ($request->historic_status != null && in_array($value['sub_status_id'], [4, 22])) {
                                    $value['principal'] = $value['principal'] + $value['profit'];
                                    $value['profit'] = $value['profit'] - $value['profit'];
                                }
                                if ($request->historic_status != null && in_array($value['sub_status_id'], [18, 19, 20])) {
                                    $adjuestmentAmount = $value['investment_amount'] - $value['principal'];
                                    $value['principal'] = $value['principal'] + $adjuestmentAmount;
                                    $value['profit'] = $value['profit'] - $adjuestmentAmount;
                                }
                                $excel_array[$i.'+2']['Principal'] = \FFM::dollar($value['principal']);
                                $excel_array[$i.'+2']['Profit'] = \FFM::dollar($value['profit']);
                                $excel_array[$i.'+2']['Last R Code'] = $value['code'];
                                $excel_array[$i.'+2']['Last Payment Date'] = \FFM::date($value['last_payment_date']);
                                $excel_array[$i.'+2']['Last Payment Amount'] = \FFM::dollar($value['last_payment_amount']);
                                $excel_array[$i.'+2']['Participant RTR'] = \FFM::dollar($value['participant_rtr']-$value['mgmnt_fee_amount']);
                                $excel_array[$i.'+2']['Old RTR'] = (in_array($value['sub_status_id'], [18, 19, 20])) ? \FFM::dollar($value['settled_rtr']) : \FFM::dollar(0);
                                $excel_array[$i.'+2']['Net Zero Balance'] = \FFM::dollar(($net_bal) ? $net_bal : '0.00');
                                $gross_balance = (($value['participant_rtr']-$value['mgmnt_fee_amount']) - ($value['net_balance'] + ($value['final_participant_share'])));
                                $excel_array[$i.'+2']['Participant RTR Balance'] = \FFM::dollar($gross_balance);
                                $excel_array[$i.'+2']['Funded'] = \FFM::dollar($value['amount']);
                                $excel_array[$i.'+2']['Total Funded'] = \FFM::dollar($value['investment_amount']);
                                $excel_array[$i.'+2']['Merchant Status'] = $value['sub_status_name'];
                                $total_payment = $total_payment + $value['participant_share'];
                                $total_mgmnt_fee = $total_mgmnt_fee + $value['mgmnt_fee'];
                                $total_net_amount = $total_net_amount + $netAmount;
                                $total_principal = $total_principal + $value['principal'];
                                $total_profit = $total_profit + $value['profit'];
                                $total_last_payment_amount = $total_last_payment_amount + $value['last_payment_amount'];
                                $total_funded = $total_funded + $value['investment_amount'];
                                $total_invested = $total_invested + $value['amount'];
                                $total_participant_rtr = $total_participant_rtr + ($value['participant_rtr']-$value['mgmnt_fee_amount']);
                                $total_participant_rtr_balance = $total_participant_rtr_balance + $gross_balance;
                                $total_net_zero_bal = $total_net_zero_bal + $net_bal;
                                $i++;
                                $k++;
                            }
                            $excel_array[$i.'+3']['No'] = null;
                            $excel_array[$i.'+3']['Merchant'] = null;
                            $excel_array[$i.'+3']['Merchant Id)'] = null;
                            $excel_array[$i.'+3']['Funded Date'] = null;
                            $excel_array[$i.'+3']['Total Payments'] = \FFM::dollar($total_payment);
                            $excel_array[$i.'+3']['Management Fee'] = \FFM::dollar($total_mgmnt_fee);
                            $excel_array[$i.'+3']['Net Amount'] = \FFM::dollar($total_net_amount);
                            $excel_array[$i.'+3']['Principal'] = \FFM::dollar($total_principal);
                            $excel_array[$i.'+3']['Profit'] = \FFM::dollar($total_profit);
                            $excel_array[$i.'+3']['Last R Code'] = null;
                            $excel_array[$i.'+3']['Last Payment Date'] = null;
                            $excel_array[$i.'+3']['Last Payment Amount'] = \FFM::dollar($total_last_payment_amount);
                            $excel_array[$i.'+3']['Participant RTR'] = \FFM::dollar($total_participant_rtr);
                            $excel_array[$i.'+3']['Old RTR'] = '';
                            $excel_array[$i.'+3']['Net Zero Balance'] = \FFM::dollar($total_net_zero_bal);
                            $excel_array[$i.'+3']['Participant RTR Balance'] = \FFM::dollar($total_participant_rtr_balance);
                            $excel_array[$i.'+3']['Funded'] = \FFM::dollar($total_invested);
                            $excel_array[$i.'+3']['Total Funded'] = \FFM::dollar($total_funded);
                            $i++;
                        }
                    }
                }
                $fileName = 'syndicate-Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
                $export = new Data_arrExport($excel_array);

                return [
                    'fileName' => $fileName,'export' => $export
                ];
                break;
        }
    }
    public static function getPaymentData($request)
    {
        $owner = isset($request->owner) ? $request->owner : null;
        $transaction_id = isset($request->transaction_id) ? $request->transaction_id : null;
        $date_type = isset($request->date_type) ? $request->date_type : null;
        $investors = isset($request->investors) ? $request->investors : null;
        $investor_type = isset($request->investor_type) ? $request->investor_type : null;
        $rcode = isset($request->rcode) ? $request->rcode : null;
        $mode_of_payment = isset($request->mode_of_payment) ? $request->mode_of_payment : null;
        $overpayment = isset($request->overpayment) ? $request->overpayment : null;
        $active_status = isset($request->active_status) ? $request->active_status : null;
        $payout_frequency = isset($request->payout_frequency) ? $request->payout_frequency : null;
        $SpecialAccounts = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccounts->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE]);

        $OverpaymentAccounts = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccounts->whereIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE]);
        $OverpaymentAccounts = $OverpaymentAccounts->pluck('users.id')->toArray();



        $SpecialAccounts = $SpecialAccounts->pluck('users.id')->toArray();
        $investor_label = isset($request->investor_label) ? $request->investor_label : null;
        $historic_status = isset($request->historic_status) ? $request->historic_status : null;
        $agent_fee_filetr_only = isset($request->filter_by_agent_fee) ? $request->filter_by_agent_fee : null;
        $velocity_owned = false;
        if($request->velocity_owned){
        $velocity_owned = true;
        }
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if (! empty($investor_type) && is_array($investor_type)) {
            $company_users_q = $company_users_q->whereIn('investor_type', $investor_type);
        }
        if (! empty($investors) && is_array($investors)) {
            $company_users_q = $company_users_q->whereIn('id', $investors);
        }
        if ($owner) {
            if (is_array($owner)) {
                $company_users_q = $company_users_q->whereIn('company', $owner);
            } else {
                $company_users_q = $company_users_q->where('company', $owner);
            }
        }
        if ($payout_frequency) {
            $company_users_q = $company_users_q->where('notification_recurence', $payout_frequency);
        }
        if($active_status == '1'){
            $company_users_q = $company_users_q->where('active_status', 1);
        } elseif($active_status == '2'){
            $company_users_q = $company_users_q->where('active_status', 0);
        }
        if($velocity_owned){
            $company_users_q = $company_users_q->where('velocity_owned', 1); 
        }

        if(count($SpecialAccounts)>0){
        $company_users_q = $company_users_q->whereNotIn('users.id', $SpecialAccounts);
        }

        if ($investor_label != null) {
            $investor_label = implode(',', $investor_label);
            $company_users_q = $company_users_q->whereRaw('json_contains(label, \'['.$investor_label.']\')');
        }
         if ($overpayment == 1) {
           $company_users_q = $company_users_q->whereIn('users.id', $OverpaymentAccounts);
            
         }
        $company_users = $company_users_q->pluck('id')->toArray();

        $payment_type = isset($request->payment_type) ? $request->payment_type : null;
        $from_date = $request->date_start;
        $to_date = $request->date_end;
        $fromDateFormat = 'Y-m-d';
        $endDateFormat = 'Y-m-d';
        $fromConvertFormat = 'Y-m-d';
        $endConvertFormat = 'Y-m-d';
        if ($date_type) {
            if ($request->time_start != null) {
                $from_date = ET_To_UTC_Time($from_date.' '.$request->time_start.':00', 'datetime');
                $fromDateFormat = 'Y-m-d H:i';
            }
            if ($request->time_end != null) {
                $to_date = ET_To_UTC_Time($to_date.' '.$request->time_end.':59', 'datetime');
                $endDateFormat = 'Y-m-d H:i';
            }
        }
        $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'payment_date';
        $paymentData = [];
        $query_dt = DB::table('participent_payments')->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->leftjoin('rcode', 'rcode.id', 'participent_payments.rcode')->where('participent_payments.merchant_id', $request->row_merchant);

        if ($from_date != null) {
            $query_dt->where($table_field, '>=', $from_date);
        }
        if ($to_date != null) {
            $query_dt->where($table_field, '<=', $to_date);
        }

        if ($payment_type != null) {
            $payment_type = ($payment_type == 'credit') ? '1' : '0';
            $query_dt = $query_dt->where('payment_type', $payment_type);
        }
        if ($transaction_id != null) {
            $query_dt = $query_dt->where('participent_payments.id', $transaction_id);
        }
        $query_dt = $query_dt->select(DB::raw('(participent_payments.payment) as payment,participent_payments.payment,participent_payments.final_participant_share as final_participant_share, sum(payment_investors.mgmnt_fee) as mgmnt_fee,sum(payment_investors.actual_participant_share) as participant_share'), 'payment_investors.user_id', 'payment_investors.participent_payment_id', 'payment_date', 'participent_payments.creator_id', 'participent_payments.created_at', 'payment_investors.merchant_id', 'rcode.code', 'participent_payments.mode_of_payment');
        $query_dt = $query_dt->whereIn('payment_investors.user_id', $company_users);
        $query_dt = $query_dt->where('participent_payments.is_payment', 1);
        if ($rcode != null) {
            $query_dt = $query_dt->whereIn('rcode', $rcode);
        }
        if ($mode_of_payment != null) {
            if ($mode_of_payment == 'ach') {
                $query_dt = $query_dt->where('mode_of_payment', 1);
            }
            if ($mode_of_payment == 'manual') {
                $query_dt = $query_dt->where('mode_of_payment', 0);
            }
            if ($mode_of_payment == 'credit_card') {
                $query_dt = $query_dt->where('mode_of_payment', 2);
            }
        }
        if ($overpayment == 1) {
            $query_dt = $query_dt->where('payment_investors.overpayment','>', 0);
        }
        $query_dt = $query_dt->groupBy('payment_investors.participent_payment_id')->orderByRaw('participent_payments.payment_date DESC,participent_payments.id DESC')->get();
        $paymentDataHTML = '';
        $paymentDataHTML = '<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger"><td class="partic" >Participant</td><td>Date</td><td>Merchant Id</td><td>Debited</td><td>Participant Share</td><td>Management fee</td>
      <td>Net amount</td><td>Rcode</td><td>Payment Method</td></tr>';
        foreach ($query_dt as $key => $data) {
            $rcode = $data->code;
            $html = '';
            $participant_share = $merchant_id = $debited = 0;
            $management_fee = 0;
            $syndiaction_fee = 0;
            $count = 0;
            $payment_date = '';
            $count++;
            if ($count % 10 == 0) {
                $html .= '<br>';
            }
            $users = self::get_users($data->participent_payment_id, $company_users);
            if (! empty($users)) {
                foreach ($users as $key => $value) {
                    $name = substr($value, 0, 4).'..';
                    $html .= "<div class='col-sm-4'><a href='".url('/admin/investors/portfolio/'.$key)."' data-html='true' class='popoverButton' data-toggle='popover' data-trigger='hover' title='".$value."'  data-original-title='participant' id=".$value.'>'.$name."</a><p style='display: inline'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></div>";
                }
            }
            $participant_share = $participant_share + $data->participant_share;
            $payment_date = $data->payment_date;
            $merchant_id = $data->merchant_id;
            $debited = $data->payment;
            $management_fee = $data->mgmnt_fee;
            $net_amount = $participant_share - $management_fee;
            if ($data->mode_of_payment == 1) {
                $payment_method = 'ACH';
            }
            if ($data->mode_of_payment == 0) {
                $payment_method = 'Manual';
            }
            if ($data->mode_of_payment == 2) {
                $payment_method = 'Credit Card Payment';
            }
            $user = User::where('id', $data->creator_id)->value('name');
            $user = ($user) ? $user : '--';
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.$user;
            $paymentDataHTML .= '<tr><td >'.$html.'</td><td><a title="'.$created_date.'" style="text-decoration:none;">'.FFM::date($payment_date).'</a></td><td>'.$merchant_id.'</td><td>'.FFM::dollar($debited).'</td><td>'.FFM::dollar($participant_share).'</td><td>'.FFM::dollar($management_fee).'</td><td>'.FFM::dollar($net_amount).'</td><td>'.$rcode.'</td><td>'.$payment_method.'</td></tr>';
        }
        $paymentDataHTML .= '</table>';
        return $paymentDataHTML;
    }
    public static function get_users($participant_id, $company_users)
    {
        if ($participant_id) {
            $users = DB::table('payment_investors')->join('users', 'users.id', 'payment_investors.user_id')->where('participent_payment_id', $participant_id)->whereIn('user_id', $company_users)->pluck(DB::raw('upper(name) as name'), 'user_id')->toArray();

            return $users;
        }
    }
}
