<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Funding extends Model
{
    public function raised($id)
    {
        $investor_data1 = MerchantUser::select(['merchant_user.id', 'users.name', 'merchant_user.created_at', 'merchant_user.user_id', 'amount', 'status', 'invest_rtr', 'actual_paid_participant_ishare', 'users.name', 'merchant_user.under_writing_fee', 'merchant_user.under_writing_fee_per', 'merchant_user.syndication_fee_percentage', 'liquidity', 'commission_amount', DB::raw('merchant_user.invest_rtr*merchant_user.mgmnt_fee/100 as mgmnt_fee_amount'),

                                                DB::raw('merchant_user.pre_paid'), 'merchant_user.mgmnt_fee', 'merchant_user.paid_mgmnt_fee', ])
                                      ->leftJoin('users', 'users.id', 'merchant_user.user_id')
                                      ->leftJoin('user_details', 'user_details.user_id', '=', 'merchant_user.user_id');
        $investor_data1 = $investor_data1->where('merchant_user.merchant_id', $id)->where('amount', '!=', '0');
        $investor_data = $investor_data1->get();

        $merchant = Merchant::find($id);

        $total_managmentfee = 0;
        $total_syndicationfee = 0;
        $total_underwrittingfee = 0;
        $syndication_fee = $part_total_amount = $management_fee = 0;
        foreach ($investor_data as $key => $investor) {
            $investor_data[$key]['tot_amount'] = $investor->amount + $investor->commission_amount + $investor->under_writing_fee + $investor->pre_paid;
            $investor_data[$key]['paid_back'] = $investor->actual_paid_participant_ishare;
            $total_managmentfee = $total_managmentfee + $investor->paid_mgmnt_fee;
            $total_syndicationfee = $total_syndicationfee + $investor->syndication_fee_amount;
            $total_underwrittingfee = $total_underwrittingfee + $investor->under_writing_fee;
            $part_total_amount = $part_total_amount + $investor->amount;

            if (! $merchant->m_s_prepaid_status) {
                $syndication_fee = $syndication_fee + (($merchant->rtr / $merchant->pmnts) * $investor->share / 100) * $investor->syndication_fee_percentage / 100;
            }

            if ($merchant->pmnts) {
                $management_fee = $management_fee + (($merchant->rtr / $merchant->pmnts) * $investor->share / 100) * $merchant->mgmnt_fee / 100;
            } else {
                $management_fee = 0;
            }
        }
        $data['investor_data'] = $investor_data;
        $data['part_total_amount'] = $part_total_amount;

        return $data;
    }

    public static function merchant_market_data($merchantID)
    {
        $funds = Merchant::where('merchants.id', $merchantID)->select('funded', 'payment_amount', 'pmnts', 'factor_rate', 'commission', 'merchants.m_mgmnt_fee', 'm_syndication_fee', 'm_s_prepaid_status', 'rtr', 'max_participant_fund', 'merchants.name as business_name', 'merchants.id', 'underwriting_fee', 'complete_percentage', 'marketplace_permission', 'credit_score', 'merchants_details.monthly_revenue', 'industries.name as industry_name', 'advance_type', 'experian_intelliscore', 'experian_financial_score', 'merchant_user.user_id')->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
        ->leftJoin('merchants_details','merchants_details.merchant_id','merchants.id')
        ->Join('industries', 'industries.id', 'merchants.industry_id')->with('FundingRequests');
        $value = $funds->first();
        $invested_amount = round($value->marketplaceInvestors()->sum('amount'));
        if (($value->max_participant_fund > $invested_amount)) {
            return true;
        }

        return false;
    }

    protected function factor_rate()
    {
        return  Merchant::select('factor_rate')->groupBy('factor_rate')->orderByDesc('factor_rate')->limit(10)->get();
    }
}
