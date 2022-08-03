<?php

namespace App\Helpers;

use App\Merchant;
use App\ParticipentPayment;
use Illuminate\Support\Facades\DB;
use App\User;

class PaymentReportHelper
{
    public static function investor($sDate = null, $eDate = null, $rcode = null, int $userId = 0, $merchant_id = null, $sort_by = null, $sort_order = null, $keyword = null)
    {
        $merchant_arr = explode(',', $merchant_id);
        $paymentQuery = Merchant::leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')->join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id')->where('merchant_user.user_id', $userId)->join('participent_payments', 'participent_payments.merchant_id', 'merchants.id')->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
        if ($sDate != null) {
            $paymentQuery->where('participent_payments.payment_date', '>=', $sDate);
        }
        if ($eDate != null) {
            $paymentQuery->where('participent_payments.payment_date', '<=', $eDate);
        }
        if ($rcode != null) {
            $paymentQuery->where('participent_payments.rcode', $rcode);
        }
        if ($merchant_id != null) {
            $paymentQuery->whereIn('merchants.id', $merchant_arr);
        }
        $paymentQuery->where('payment_investors.user_id', $userId);
        $paymentQuery->where('merchants.active_status', 1);
        if ($sDate != null) {
            $debitedQuery = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND participent_payments.is_payment = 1 AND payment_date >= '$sDate') debited");
        } elseif ($eDate != null) {
            $debitedQuery = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND participent_payments.is_payment = 1 AND payment_date <= '$eDate') debited");
        } elseif ($sDate != null && $eDate != null) {
            $debitedQuery = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND participent_payments.is_payment = 1 AND payment_date >= '$sDate' AND payment_date <= '$eDate') debited");
        } else {
            $debitedQuery = DB::raw('(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND participent_payments.is_payment = 1) debited');
        }
        if ($keyword != null) {
            $paymentQuery->where(function ($q) use ($keyword) {
                $num_keyword = str_replace('$', '', $keyword);
                $q->where('merchants.name', 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw("DATE_FORMAT(`date_funded`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%')->orWhere('merchants.id', 'LIKE', '%'.$keyword.'%')->orWhere('actual_participant_share', 'LIKE', '%'.str_replace(',', '', $num_keyword).'%')->orWhere(DB::raw('(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id)'), 'LIKE', '%'.$keyword.'%')->orWhere('invest_rtr', 'LIKE', '%'.$keyword.'%')->orWhere('merchants.last_payment_date', 'LIKE', '%'.$keyword.'%');
            });
        }
        if ($sort_by != null && $sort_order != null) {
            if ($sort_by == 'merchant') {
                $paymentQuery->orderBy('merchants.name', $sort_order);
            }
            if ($sort_by == 'funded_date') {
                $paymentQuery->orderBy('merchants.date_funded', $sort_order);
            }
            if ($sort_by == 'merchant_id') {
                $paymentQuery->orderBy('merchants.id', $sort_order);
            }
            if ($sort_by == 'debited') {
                $paymentQuery->orderBy(DB::raw('(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id)'), $sort_order);
            }
            if ($sort_by == 'total_payments') {
                $paymentQuery->orderBy(DB::raw('SUM(payment_investors.actual_participant_share)'), $sort_order);
            }
            if ($sort_by == 'management_fee') {
                $paymentQuery->orderBy(DB::raw('SUM(payment_investors.mgmnt_fee)'), $sort_order);
            }
            if ($sort_by == 'net_amount') {
                $paymentQuery->orderBy(DB::raw('SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee)'), $sort_order);
            }
            if ($sort_by == 'principal') {
                $paymentQuery->orderBy(DB::raw('SUM(payment_investors.principal)'), $sort_order);
            }
            if ($sort_by == 'profit') {
                $paymentQuery->orderBy(DB::raw('SUM(payment_investors.profit)'), $sort_order);
            }
            if ($sort_by == 'last_rcode') {
                $paymentQuery->orderBy('rcode.code', $sort_order);
            }
            if ($sort_by == 'last_successful_payment_date') {
                $paymentQuery->orderBy('merchants.last_payment_date', $sort_order);
            }
            if ($sort_by == 'last_payment_amount') {
                $paymentQuery->orderBy(DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id and AND participent_payments.is_payment = 1 ORDER BY payment_date DESC,id DESC limit 1)'), $sort_order);
            }
            if ($sort_by == 'participant_rtr') {
                $paymentQuery->orderBy('invest_rtr', $sort_order);
            }
            if ($sort_by == 'participant_rtr_balance') {
                $paymentQuery->orderBy(DB::raw('IF((( actual_paid_participant_ishare-invest_rtr ) * ( 1 - merchant_user.mgmnt_fee / 100 ))<=0,(invest_rtr-merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100)-SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee),0)'), $sort_order);
            }
        } else {
            $paymentQuery->orderByDesc('merchants.last_payment_date');
        }
        $totalQuery = clone $paymentQuery;
        $total = $totalQuery->select(DB::raw('count(DISTINCT merchants.id) as count'),
        DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id AND participent_payments.is_payment = 1 ORDER BY payment_date DESC,id DESC limit 1) last_payment_amount'),
        DB::raw('(SELECT SUM(invest_rtr) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) invest_rtr'),
        DB::raw('SUM(payment_investors.profit) as total_profit'),
        DB::raw('SUM(payment_investors.principal) as total_pricipal'),
        DB::raw('SUM(payment_investors.mgmnt_fee) as total_mgmnt_fee'),
        DB::raw('SUM(merchant_user.commission_amount) as total_commission_amount'),
        DB::raw('SUM(payment_investors.actual_participant_share) as total_participant_share'),
        DB::raw(' SUM( IF(actual_paid_participant_ishare > invest_rtr, ( actual_paid_participant_ishare-invest_rtr ) * (1 - merchant_user.mgmnt_fee / 100 ), 0) ) as total_overpayment')
        )->first()->toArray();
        $data = $paymentQuery->groupBy('merchants.id')->select(DB::raw("IF(display_value='mid',
		merchants.id,
		merchants.name) as name"),
        'merchants.date_funded',
        'merchants.id',
        'merchants.rtr',
        'rcode.code',
       // 'invest_rtr as participant_rtr',
        DB::raw('(merchant_user.invest_rtr* ( 1 - merchant_user.mgmnt_fee / 100 )) as participant_rtr'),
  //       DB::raw('IF((invest_rtr-SUM(payment_investors.actual_participant_share))>=0,
		// (invest_rtr-SUM(payment_investors.actual_participant_share)),
		// 0) as participant_rtr_balance'),
        DB::raw('IF(((invest_rtr-merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100)-SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee))>=0, (invest_rtr-merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100)-SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee), 0) as participant_rtr_balance'),
        
        DB::raw('SUM(merchant_user.invest_rtr) as total_rtr'),
        DB::raw('SUM(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid) as total_invested_amount'),
        DB::raw('(SELECT SUM(invest_rtr) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) gross_participant_rtr'),
        DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id AND participent_payments.is_payment = 1 ORDER BY payment_date DESC,id DESC limit 1) last_payment_amount'),
        DB::raw('(SELECT payment_date FROM participent_payments LEFT JOIN payment_investors on payment_investors.participent_payment_id=participent_payments.id WHERE merchants.id = participent_payments.merchant_id AND participent_payments.is_payment = 1 AND payment > 0 AND user_id='.$userId.' ORDER BY payment_date DESC limit 1) last_payment_date'),
        DB::raw('SUM(payment_investors.profit) as profit'),
        DB::raw('SUM(merchant_user.mgmnt_fee) as total_mgmnt_fee'),
        DB::raw('SUM(merchant_user.commission_amount) as commission_amount'),
        DB::raw('SUM(payment_investors.principal) as principal'),
        DB::raw('SUM(payment_investors.mgmnt_fee) as mgmnt_fee'),
        DB::raw('SUM( IF(actual_paid_participant_ishare > invest_rtr, ( actual_paid_participant_ishare-invest_rtr ) * ( 1 - merchant_user.mgmnt_fee / 100 ), 0) ) as overpayment'),
        DB::raw('SUM(merchant_user.invest_rtr-payment_investors.actual_participant_share) AS gross_participant_rtr_balance'),
        DB::raw('SUM(payment_investors.actual_participant_share) as participant_share'),
        DB::raw('SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee) as final_participant_share'), $debitedQuery)->get()->toArray();

        return [$total, $data];
    }

    public static function investorDetail(int $userId = 0, int $merchantId = 0, $sDate = null, $eDate = null)
    {
        $query = ParticipentPayment::join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->leftjoin('rcode', 'rcode.id', 'participent_payments.rcode')->where('participent_payments.merchant_id', $merchantId)->where('payment_investors.user_id', $userId)->select(DB::raw('(participent_payments.payment) as payment,
				participent_payments.payment,
				participent_payments.final_participant_share as final_participant_share, 
				SUM(payment_investors.mgmnt_fee) as mgmnt_fee, 
				SUM(payment_investors.syndication_fee) as syndication_fee,
				SUM(payment_investors.actual_participant_share) as participant_share'), 'payment_investors.user_id', 'payment_investors.participent_payment_id', 'payment_date', 'payment_investors.merchant_id', 'rcode.code')->groupBy('payment_investors.participent_payment_id');
        if ($sDate != null) {
            $query = $query->where('participent_payments.payment_date', '>=', $sDate);
        }
        if ($eDate != null) {
            $query = $query->where('participent_payments.payment_date', '<=', $eDate);
        }
        $query = $query->where('participent_payments.is_payment', 1);
        $query = $query->orderByRaw('participent_payments.payment_date DESC,participent_payments.id DESC');
        $query = $query->get();

        return $query;
    }

    public static function investorDownloadData($sDate = null, $eDate = null, $rcode = null, int $userId = 0, $mercants = null, $payment_type = null, $lenders = null, $label = null, $mode_of_payment = null, $sub_statuses = null, $advance_type = null, $date_type = null, $stime = null, $etime = null, $payout_frequency = null, $historic_status = null,$overpayment=null,$active=null,$transaction_id=null)
    {

        if ($date_type == 'true') {
            if ($sDate != null) {
                if ($stime != '') {
                    $sDate = $sDate.' '.$stime;
                }
            }
            if ($eDate != null) {
                if ($etime != '') {
                    $eDate = $eDate.' '.$etime;
                }
            }
        }

        $end_date_query = $date_query = $merchantFilterQuery = '';
        $paymentQuery = Merchant::leftJoin('rcode', 'rcode.id', 'merchants.last_rcode')->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id')
        //->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
        ->where('merchant_user.user_id', $userId)->join('participent_payments', 'participent_payments.merchant_id', 'merchants.id')->where('participent_payments.is_payment', 1)->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');

        $table_field = ($date_type == 'true') ? 'participent_payments.created_at' : 'participent_payments.payment_date';

        if ($sDate != null) {
            $paymentQuery->where($table_field, '>=', $sDate);
            $end_date_query.= " AND  $table_field < '$sDate' ";
            $date_query.= "AND $table_field >= '$sDate'";
        } else {
            $end_date_query = " AND  $table_field < '1970-01-01' ";
        }
        if ($eDate != null) {
            $paymentQuery->where($table_field, '<=', $eDate);
            $date_query.= "AND $table_field <= '$eDate'";
        }

        $company_users_val = $userId;
        $userQuery = 'AND user_id in ('.$company_users_val.')';

        if ($historic_status != null && $eDate < date('Y-m-d')) {
            $eDate = ($eDate > date('Y-m-d')) ? ' ' : $eDate;
            $paymentQuery = $paymentQuery->join('merchant_status_log', 'merchant_status_log.merchant_id', 'merchants.id');
            if ($eDate) {
                $paymentQuery = $paymentQuery->where('merchant_status_log.created_at', '>=', $eDate);
            }

            $paymentQuery = $paymentQuery->join('sub_statuses', 'sub_statuses.id', 'merchant_status_log.old_status');
            if ($sub_statuses) {
                $paymentQuery = $paymentQuery->whereIn('merchant_status_log.old_status', $sub_statuses);
            }
            $paymentQuery = $paymentQuery->where('merchant_status_log.old_status', function ($query) use ($sDate, $eDate, $sub_statuses) {
                $query->select('merchant_status_log.old_status')
                      ->from('merchant_status_log');
                if ($eDate) {
                    $query = $query->where('merchant_status_log.created_at', '>=', $eDate);
                } else {
                    $query = $query->where('merchant_status_log.created_at', '<=', $eDate);
                }
                $query->whereRaw('merchants.id = merchant_status_log.merchant_id');

                if ($eDate) {
                    $query = $query->orderBy('merchant_status_log.id', 'asc');
                } else {
                    $query = $query->orderBy('merchant_status_log.id', 'desc');
                }

                $query = $query->limit(1);
            });
        } else {
            $paymentQuery = $paymentQuery->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');

            if ($sub_statuses) {
                $paymentQuery = $paymentQuery->whereIn('sub_status_id', $sub_statuses);
            }
        }
        if ($payout_frequency != null) {
            $paymentQuery->where('users.notification_recurence', $payout_frequency);
        }
       
        if ($active == 1) {
            $paymentQuery->where('users.active_status', 1);
        }
        if ($active == 2) {
            $paymentQuery->where('users.active_status', 0);
        }

        if (!empty($rcode)) {
            $paymentQuery->whereIn('participent_payments.rcode', $rcode);
        }
 

        if ($payment_type != null) {
            if ($payment_type == 'credit') {
                $paymentQuery->where('participent_payments.payment_type', 1);
            } elseif ($payment_type == 'debit') {
                $paymentQuery->where('participent_payments.payment_type', 0);
            }
        }

        $carry_forwards_query = ' AND carry_forwards.investor_id = '.$userId;
        if ($mercants != null) {
            $paymentQuery->whereIn('merchants.id', $mercants);
            $carry_forwards_query .= ' AND carry_forwards.merchant_id IN ('.implode(',', $mercants).')';
            $merchantFilterQuery .= ' AND participent_payments.merchant_id IN ('.implode(',', $mercants).')';
        }
        if ($lenders != null) {
            $paymentQuery->whereIn('merchants.lender_id', $lenders);
        }
        if ($advance_type != null) {
            $paymentQuery->whereIn('advance_type', $advance_type);
        }
        if ($label != null) {
            $paymentQuery->whereIn('merchants.label', $label);
        }
        if ($mode_of_payment != 0) {
            if ($mode_of_payment == 'ach') {
                $paymentQuery->where('participent_payments.mode_of_payment', 1);
            }
            if ($mode_of_payment == 'manual') {
                $paymentQuery->where('participent_payments.mode_of_payment', 0);
            }
            if ($mode_of_payment == 'credit_card') {
                $paymentQuery->where('participent_payments.mode_of_payment', 2);
            }
        }
        if ($transaction_id != null) {
                $paymentQuery->where('participent_payments.id', $transaction_id);
        }

        if ($overpayment == 1) {

         $SpecialAccounts = DB::table('users')->join('user_has_roles', 'user_has_roles.model_id', 'users.id');
         $SpecialAccounts->whereIn('user_has_roles.role_id', [User::OVERPAYMENT_ROLE]);
         $SpecialAccounts = $SpecialAccounts->pluck('users.id')->toArray();
         array_push($SpecialAccounts,$userId);

             $paymentQuery->whereIn('payment_investors.user_id', $SpecialAccounts);
             $paymentQuery->where('payment_investors.overpayment', '!=', 0);
            
         }
         else
         {
             $paymentQuery->where('payment_investors.user_id', $userId);

         }

        $paymentQuery->where('merchants.active_status', 1);

        if ($sDate != null) {
            $debitedQuery = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND payment_date >= '$sDate') debited");
            $carry_forwards_query = " AND carry_forwards.date >= '$sDate'";
        } elseif ($eDate != null) {
            $debitedQuery = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND payment_date <= '$eDate') debited");
            $carry_forwards_query = " AND carry_forwards.date <= '$eDate'";
        } elseif ($sDate != null && $eDate != null) {
            $debitedQuery = DB::raw("(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id AND payment_date >= '$sDate' AND payment_date <= '$eDate') debited");
            $carry_forwards_query = " AND carry_forwards.date>='$sDate' AND carry_forwards.date <= '$eDate'";
        } else {
            $debitedQuery = DB::raw('(SELECT SUM(payment) FROM participent_payments WHERE participent_payments.merchant_id = merchants.id) debited');
            $carry_forwards_query = '';
        }
        $paymentQuery->leftJoin(DB::raw("(SELECT SUM(carry_forwards.amount) as carry_profit , carry_forwards.merchant_id FROM carry_forwards  LEFT JOIN merchants on carry_forwards.merchant_id=merchants.id WHERE type=2 $carry_forwards_query) as user_carry_profit "), 'user_carry_profit.merchant_id', '=', 'merchants.id')
         ->leftJoin(DB::raw("(SELECT SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee) as net_balance, participent_payments.merchant_id FROM payment_investors  LEFT JOIN participent_payments on
        payment_investors.participent_payment_id=participent_payments.id 
        WHERE participent_payments.merchant_id > 0
        AND participent_payments.merchant_id IN (SELECT merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 
        $end_date_query $userQuery $merchantFilterQuery)
        $end_date_query $userQuery $merchantFilterQuery GROUP BY participent_payments.merchant_id ORDER BY participent_payments.merchant_id ASC) as net_balance_payments"), 'net_balance_payments.merchant_id', '=', 'merchants.id')
         ->leftJoin(DB::raw("(SELECT SUM(payment_investors.actual_participant_share) as gross_balance, participent_payments.merchant_id FROM payment_investors  LEFT JOIN participent_payments on
        payment_investors.participent_payment_id=participent_payments.id 
        WHERE participent_payments.merchant_id > 0
        AND participent_payments.merchant_id IN (SELECT merchant_id FROM participent_payments WHERE participent_payments.merchant_id > 0 
        $end_date_query $userQuery $merchantFilterQuery)
        $end_date_query $userQuery $merchantFilterQuery GROUP BY participent_payments.merchant_id ORDER BY participent_payments.merchant_id ASC) as gross_balance_payments"), 'gross_balance_payments.merchant_id', '=', 'merchants.id');
        $data = $paymentQuery->groupBy('merchants.id')->select(
            'merchants.name',
            'merchants.date_funded',
            'merchants.id',
            'merchants.old_factor_rate',
            'merchants.last_payment_date',
            'sub_statuses.name as sub_status_name',
            'sub_statuses.id as sub_status_id',
            'merchants.rtr',
            'rcode.code',
            'invest_rtr as participant_rtr',
            'net_balance_payments.net_balance',
            'gross_balance_payments.gross_balance',
            DB::raw('(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as mgmnt_fee_amount'),
            DB::raw('IF(user_carry_profit.carry_profit,user_carry_profit.carry_profit,0) AS carry_profit'),
            DB::raw('IF((( actual_paid_participant_ishare-invest_rtr ) * ( 1 - merchant_user.mgmnt_fee / 100 ))<=0, (invest_rtr-merchant_user.invest_rtr * merchant_user.mgmnt_fee / 100)-SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee), 0) as participant_rtr_balance'),
            DB::raw('SUM(merchant_user.invest_rtr) as total_rtr'),
            DB::raw('SUM(merchant_user.amount+ merchant_user.commission_amount + merchant_user.pre_paid) as total_invested_amount'),
            DB::raw('(SELECT SUM(invest_rtr) FROM merchant_user WHERE merchants.id = merchant_user.merchant_id) gross_participant_rtr'),
            DB::raw('(SELECT payment FROM participent_payments WHERE merchants.id = participent_payments.merchant_id AND participent_payments.is_payment = 1 AND payment_type=1 AND payment > 0 ORDER BY payment_date DESC limit 1) last_payment_amount'),
            DB::raw('SUM(payment_investors.profit) as profit'),
            DB::raw('SUM(merchant_user.mgmnt_fee) as total_mgmnt_fee'),
            DB::raw('SUM(merchant_user.commission_amount) as commission_amount'),
            DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'),
            DB::raw('merchant_user.amount'),
            DB::raw('SUM(payment_investors.principal) as principal'),
            DB::raw('SUM(payment_investors.mgmnt_fee) as mgmnt_fee'),
            DB::raw('SUM( IF(actual_paid_participant_ishare > invest_rtr, ( actual_paid_participant_ishare-invest_rtr ) * ( 1 - merchant_user.mgmnt_fee / 100 ), 0) ) as overpayment'),
            DB::raw('(merchant_user.amount*old_factor_rate) as settled_rtr'),

            DB::raw('SUM(merchant_user.invest_rtr-payment_investors.actual_participant_share) AS gross_participant_rtr_balance'),
            DB::raw('SUM(payment_investors.actual_participant_share) as participant_share'),
            DB::raw('SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee) as final_participant_share'),

            $debitedQuery
        );
        $data = $data->havingRaw('sum(payment_investors.profit*-1) != sum(payment_investors.principal) OR sum(payment_investors.actual_participant_share)>=0');

        $data = $data->get()->toArray();

        return [$data];
    }
}
