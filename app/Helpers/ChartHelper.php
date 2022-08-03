<?php

namespace App\Helpers;

use App\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ChartHelper
{
    public static function getPieChartData($attribute, int $type = null, int $flag = null):Builder
    {
        $query = Merchant::join('merchant_user', 'merchants.id', 'merchant_user.merchant_id')->whereIn('merchant_user.status', [1, 3]);
        $applyGroupBy = true;
        if ($attribute == 0) {
            $groupByColumn = 'merchants.label';
            $nameColumn = 'label as name';
            $query->join('sub_statuses', 'merchants.sub_status_id', 'sub_statuses.id');
        } elseif ($attribute == 1) {
            $nameColumn = 'sub_statuses.name as name';
            $groupByColumn = 'merchants.sub_status_id';
            $query->join('sub_statuses', 'merchants.sub_status_id', 'sub_statuses.id');
        } elseif ($attribute == 2) {
            $nameColumn = 'industries.name as name';
            $groupByColumn = 'merchants.industry_id';
            $query->leftjoin('industries', 'merchants.industry_id', 'industries.id');
        } elseif ($attribute == 3) {
            $nameColumn = 'users.name as name';
            $groupByColumn = 'merchant_user.user_id';
            $query->join('users', 'merchant_user.user_id', 'users.id');
        } elseif ($attribute == 4) {
            $nameColumn = 'users.name as name';
            $groupByColumn = 'merchants.lender_id';
            $query->leftjoin('users', 'merchants.lender_id', 'users.id');
        } elseif ($attribute == 5) {
            $nameColumn = 'merchants.commission as name';
            $groupByColumn = 'merchants.commission';
        } elseif ($attribute == 6) {
            $nameColumn = 'merchants.factor_rate as name';
            $groupByColumn = 'merchants.factor_rate';
        } elseif ($attribute == 7) {
            $nameColumn = 'us_states.state as name';
            $groupByColumn = 'merchants.state_id';
            $query->leftjoin('us_states', 'merchants.state_id', 'us_states.id');
        } elseif ($attribute == 8) {
            $applyGroupBy = false;
            $nameColumn = 'IF( 500 < 1000, "Total", "Total") as name';
            if ($flag == 1) {
                $nameColumn = 'merchants.state_id';
            }
            $groupByColumn = 'merchants.state_id';
        }
        if ($type == 0) {
            $query->where('merchants.active_status', 1)->select(DB::raw($groupByColumn), DB::raw($nameColumn), DB::raw('
						( 
							SUM( merchant_user.amount) + 
							SUM( merchant_user.commission_amount) + 
							SUM( merchant_user.pre_paid) + 
							SUM( merchant_user.under_writing_fee) 
						) as amount'));
        } elseif ($type == 2) {
            $query->where('active_status', 1)->select(DB::raw($groupByColumn), DB::raw($nameColumn), DB::raw('
						(
							SUM(merchant_user.invest_rtr) -
							SUM( merchant_user.paid_participant_ishare)
						) as amount'));
        } else {
            $query->whereIn('merchants.sub_status_id', [4, 22])->select(DB::raw($groupByColumn), DB::raw($nameColumn), DB::raw('
						(
							SUM(merchant_user.invest_rtr) -
							SUM( merchant_user.paid_participant_ishare) 
						) as amount'));
            if (in_array($attribute, [2, 3, 4, 5, 6, 7])) {
                $query->select(DB::raw($groupByColumn), DB::raw($nameColumn), DB::raw('
					(
						SUM( merchant_user.amount) +
						SUM( merchant_user.commission_amount) +
						SUM( merchant_user.pre_paid) +
						SUM( merchant_user.under_writing_fee) - 
						SUM( merchant_user.paid_participant_ishare)
					) as amount'));
            }
        }
        if ($applyGroupBy) {
            $query->groupBy(DB::raw($groupByColumn));
        }

        return $query;
    }
}
