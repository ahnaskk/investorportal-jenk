<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Auth;
use App\InvestorTransaction;
use Illuminate\Support\Facades\DB;
use App\UserDetails;
use App\UserMeta;
use FFM;
use App\Library\Repository\Interfaces\IRoleRepository;
use Spatie\Permission\Models\Role;
use App\User;

class InvestorTransactionHelper
{   public function __construct(IRoleRepository $role)
    {        
        $this->role = $role;        
    }
    const INVESTOR_CREDITED = 1;
    const FUNDED_MERCHANT = 2;
    const INTEREST_CREATED = 3;
    const INVESTOR_DEBITED = 4;
    const BILLS_PAID = 10;
    const CARRY = 11;
    const GIFT_EQUITY = 13;
    const GIFT_EQUITY_D = 14;
    const EQUITY_DISTRIBUTION = 6;
    const EQUITY_DISTRIBUTION2 = 7;
    const EQUITY_DISTRIBUTION3 = 8;
    const SELECT_CATEGORIES = 0;
    const RETURN_OF_PRINCIPAL = 12;
    const V_PROFIT_DISTR = 15;
    const V_INVESTOR_DISTR = 16;
    const V_PACT_DISTR = 17;
    const VELOCITY_CONTRIBUTION = 18;

    public static function getAllOptions()
    {
        return [
            //self::SELECT_CATEGORIES     => 'Select Categories',
            self::INVESTOR_CREDITED     => 'Transfer To Velocity', //old Credited by investor
            self::RETURN_OF_PRINCIPAL   => 'Return of Principal',
            self::EQUITY_DISTRIBUTION   => 'Equity Distribution to investor',
            self::EQUITY_DISTRIBUTION2  => 'Equity Distribution to velocity',
            self::EQUITY_DISTRIBUTION3  => 'Equity Distribution to Pactolus',
            self::INTEREST_CREATED      => 'Interest generated',
            self::INVESTOR_DEBITED      => 'Transfer To Bank', // old Debited to investor
            self::BILLS_PAID            => ' Bills paid',
            self::CARRY                 => ' Carry',
            self::GIFT_EQUITY           => ' Allocation of equity',
            self::GIFT_EQUITY_D         => ' Allocation of equity (Debit)',
            self::V_PROFIT_DISTR        => 'Profit Distribution (velocity)',
            self::V_INVESTOR_DISTR      => 'Profit Distribution (Investor)',
            self::V_PACT_DISTR          => 'Profit Distribution (Pactolus)',
            self::VELOCITY_CONTRIBUTION => 'Velocity Contribution',
        ];
    }
    public function getInvestorTransactions($request,$setInvestors,$companyIds,$merchantIds){
        $label = $request->label;
        $pactolus_distribution = 0;
        $investor_distribution = 0;
        $velocity_distribution = 0;
        $portfolio_difference  = 0;
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $investorIds = $this->role->allInvestors()->pluck('id')->toArray();
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        if ($OverpaymentAccount) {
            $investorIds[] = $OverpaymentAccount->id;
        }
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id'); 
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']); 
        if ($AgentFeeAccount) {             
            $investorIds[] = $AgentFeeAccount->id;         
        }
        $transactionQuery = InvestorTransaction::where('date', '<', NOW())->where('investor_transactions.status', InvestorTransaction::StatusCompleted);
        if (empty($permission) || count($companyIds) > 0) {
            $transactionQuery->whereIn('investor_id', $setInvestors);
        }  
        $transactionQuery2 = clone $transactionQuery;
        $transactionQuery->where('transaction_category', 1);
        $startDate = "'".$transactionQuery->min('date')."'";
        $average_query = $transactionQuery->where('transaction_category', 1)->select(DB::raw('SUM(amount) as total_credit'), DB::raw("SUM( amount * TIMESTAMPDIFF(day, investor_transactions.date, NOW()) / TIMESTAMPDIFF(day, $startDate, NOW()) ) as average"))->first();
        $transactionQuery3 = InvestorTransaction::where('date', '<', NOW())->where('status', InvestorTransaction::StatusCompleted)->whereIn('transaction_category', [12, 13, 14])->select(DB::raw('SUM(IF(transaction_category,amount,0)) as return_of_pr'));
        $liquidityQuery = UserDetails::join('users', 'users.id', '=', 'user_details.user_id')->whereIn('user_id', $investorIds);
        if (empty($permission) || count($companyIds) > 0) {
            $transactionQuery3->whereIn('investor_id', $setInvestors);            
            $liquidityQuery->whereIn('user_id', $setInvestors);
        }
        $transactionQuery3 = $transactionQuery3->first();
        $cash_in_hands = $liquidityQuery->sum('liquidity');
        $average = $average_query->average;
        $total_credit = ($average_query->total_credit) + $transactionQuery3->return_of_pr;
        $transaction_values = $transactionQuery2->groupBy('transaction_category')->whereIn('transaction_category', [3, 6, 7, 8, 10])->pluck('transaction_category', DB::raw('sum(amount) as amount'));
        foreach ($transaction_values as $amount => $value) {
            if ($value == 3) {
                $interest_generated = $amount;
            }
            if ($value == 6) {
                $investor_distribution = $amount;
            }
            if ($value == 7) {
                $velocity_distribution = $amount;
            }
            if ($value == 8) {
                $pactolus_distribution = $amount;
            }
            if ($value == 10) {
                $bill_paid = $amount;
            }
        }
        $ctd_after_fee = 0;
        if (empty($permission) && empty($setInvestors)) {
            $ctd_after_fee = 0;
        } else {
            $investorQuery = UserMeta::where(function ($inner) use ($permission, $companyIds, $setInvestors) {
                if (empty($permission) || count($companyIds) > 0) {
                    $inner->whereIn('user_id', $setInvestors);
                } else {
                    $inner->where('id', '>', 0);
                }
            });
            if ($label) {
                $ctd_after_fee = DB::table('merchant_user')->whereIn('merchant_id', $merchantIds)->whereIn('user_id', $setInvestors)->sum(DB::raw('actual_paid_participant_ishare - paid_mgmnt_fee'));
            } else {
                $ctd_participant_share = (clone $investorQuery)->where('key', '_pi_total_participant_share')->sum('value');
                $ctd_mgmnt_fee = (clone $investorQuery)->where('key', '_pi_total_mgmnt_fee')->sum('value');
                $ctd_after_fee = $ctd_participant_share - $ctd_mgmnt_fee;
            }
        }
        $total_a_rtr = 0;
        $expected_rtr = $ctd_after_fee;
        $portfolio_value = $expected_rtr + $cash_in_hands;
        $data = [
            'velocity_distribution' => FFM::dollar($velocity_distribution * -1),
            'investor_distribution' => FFM::dollar($investor_distribution),
            'pactolus_distribution' => FFM::dollar($pactolus_distribution),
            'average_daily_balance' => FFM::dollar($average),
            'investor_portfolio'    => FFM::dollar($total_credit),
            'ctd_after_fee'         => FFM::dollar($ctd_after_fee),
            'portfolio_value'       => FFM::dollar($portfolio_value),
            'liquidity'             => FFM::dollar($cash_in_hands),
            'draw'                  => $request->input('draw')
        ];
        return $data;
    }
}
