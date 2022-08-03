<?php
/**
* Created by Rahees.
* User: rahees_iocod
* Date: 4/01/21
* Time: 1:15 AM.
*/

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\IPennyAdjustmentRepository;
use App\Settings;
use App\Merchant;
use App\MerchantUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PennyAdjustmentRepository implements IPennyAdjustmentRepository
{
    public function __construct()
    {
        $this->liquidity_check_table = DB::table('user_details_liquidity_check_view');
        $this->participent_payments_table = DB::table('participent_payments_check_view');
        $this->company_amount_pivot_check_table = DB::table('company_amount_pivot_check_view');
        $this->zero_payment_amount_check_table = DB::table('zero_payment_amount_check_view');
        $this->final_participant_share_grouped_check_table = DB::table('final_participant_share_grouped_check_view');
        $this->investor_share_check_table = DB::table('investor_share_check_view');
        $this->merchants_fund_amount_check_table = DB::table('merchants_fund_amount_check_view');
        $this->penny_investment_check_table = DB::table('penny_investment_check_view');
        $this->investment_amount_check_table = DB::table('investment_amount_grouped_check_view');
    }
    
    public function getLiquidityDifference($data = [])
    {
        $totalCount = $this->liquidity_check_table->count();
        $tableData = $this->liquidity_check_table;
        if (isset($data['user_id'])) {
            $tableData = $tableData->whereuser_id($data['user_id']);
        }
        if (isset($data['diff'])) {
            $tableData = $tableData->where('diff', '!=', 0);
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['paid_mgmnt_fee'] = $tableData->sum('paid_mgmnt_fee');
        $return['paid_participant_ishare'] = $tableData->sum('paid_participant_ishare');
        $return['ctd'] = $tableData->sum('ctd');
        $return['total_funded'] = $tableData->sum('total_funded');
        $return['commission_amount'] = $tableData->sum('commission_amount');
        $return['under_writing_fee'] = $tableData->sum('under_writing_fee');
        $return['pre_paid'] = $tableData->sum('pre_paid');
        $return['total_credits'] = $tableData->sum('total_credits');
        $return['existing_liquidity'] = $tableData->sum('existing_liquidity');
        $return['actual_liquidity'] = $tableData->sum('actual_liquidity');
        $return['diff'] = $tableData->sum('diff');
        
        return $return;
    }
    
    public function getMerchantValueDifference($data = [])
    {
        $totalCount = $this->participent_payments_table->count();
        $tableData = $this->participent_payments_table->select(
            [
                'participent_payments_check_view.*',
                // 'actual_final_participant_share',
                // DB::raw('participent_payments_check_view.actual_final_participant_share-payment_investors_check_view.existing_final_participant_share as diff_final_participant_share')
            ]
        );
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->where('participent_payments_check_view.merchant_id', $data['merchant_id']);
        }
        if (isset($data['rtr_diff'])) {
            $tableData = $tableData->where('rtr_diff', '!=', 0);
        }
        // if (isset($data['diff_final_participant_share'])) { $tableData = $tableData->where('diff_final_participant_share','!=',0); }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['funded'] = $tableData->sum('funded');
        $return['max_participant_fund'] = $tableData->sum('max_participant_fund');
        $return['percentage'] = $tableData->sum('percentage');
        $return['factor_rate'] = $tableData->sum('factor_rate');
        $return['existing_rtr'] = $tableData->sum('existing_rtr');
        $return['actual_rtr'] = $tableData->sum('actual_rtr');
        $return['rtr_diff'] = $tableData->sum('rtr_diff');
        $return['payment'] = $tableData->sum('payment');
        $return['balance'] = $tableData->sum('balance');
        $return['existing_final_participant_share'] = $tableData->sum('existing_final_participant_share');
        // $return['actual_final_participant_share']=$tableData->sum('actual_final_participant_share');
        // $return['diff_final_participant_share']=$tableData->sum('actual_final_participant_share-existing_final_participant_share');
        return $return;
    }
    
    public function getCompanyAmountDifference($data = [])
    {
        $totalCount = $this->company_amount_pivot_check_table->count();
        $tableData = $this->company_amount_pivot_check_table;
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->where('merchant_id', $data['merchant_id']);
        }
        if (isset($data['merchant_company_diff'])) {
            $tableData = $tableData->where('merchant_company_diff', '!=', 0);
        }
        if (isset($data['invsetor_company_diff'])) {
            $tableData = $tableData->where('invsetor_company_diff', '!=', 0);
        }
        if (isset($data['percentage'])) {
            $tableData = $tableData->where('percentage', $data['percentage']);
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['merchant_company_diff'] = $tableData->sum('merchant_company_diff');
        $return['invsetor_company_diff'] = $tableData->sum('invsetor_company_diff');
        
        return $return;
    }
    
    public function getZeroParticipantAmount($data = [])
    {
        $totalCount = $this->zero_payment_amount_check_table->count();
        $tableData = $this->zero_payment_amount_check_table;
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->where('merchant_id', $data['merchant_id']);
        }
        if (isset($data['expected_existing_participant_share'])) {
            $tableData = $tableData->where('expected_existing_participant_share', $data['expected_existing_participant_share']);
        }
        if (isset($data['diff'])) {
            $tableData = $tableData->where('diff', $data['diff']);
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['amount'] = $tableData->sum('amount');
        
        return $return;
    }
    
    public function getFinalParticipantShare($data = [])
    {
        $totalCount = $this->final_participant_share_grouped_check_table->count();
        $tableData = $this->final_participant_share_grouped_check_table;
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->where('merchant_id', $data['merchant_id']);
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['diff'] = $tableData->sum('diff');
        $return['expected_existing_participant_share'] = $tableData->sum('expected_existing_participant_share');
        
        return $return;
    }
    
    public function getMerchantInvestorShareDifference($data = [])
    {
        $totalCount = $this->investor_share_check_table->count();
        $tableData = $this->investor_share_check_table;
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->where('merchant_id', $data['merchant_id']);
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['diff'] = $tableData->sum('diff');
        
        return $return;
    }
    
    public function getMerchantsFundAmountCheck($data = [])
    {
        $totalCount = $this->merchants_fund_amount_check_table->count();
        $tableData = $this->merchants_fund_amount_check_table;
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->where('merchant_id', $data['merchant_id']);
        }
        if (isset($data['percentage'])) {
            $tableData = $tableData->where('merchant_completed_percentate', $data['percentage']);
        }
        if (isset($data['mgmnt_fee_diff'])) {
            $tableData = $tableData->where('mgmnt_fee_diff', '!=', 0);
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['mgmnt_fee_diff'] = $tableData->sum('mgmnt_fee_diff');
        $return['user_balance_amount'] = $tableData->sum('user_balance_amount');
        
        return $return;
    }
    
    public function getInvestmentAmountCheck($data = [])
    {
        $totalCount = $this->investment_amount_check_table->count();
        $tableData = $this->investment_amount_check_table;
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->where('merchant_id', $data['merchant_id']);
        }
        if (isset($data['diff_amount'])) {
            $tableData = $tableData->where('diff_amount', '!=', 0);
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['diff_amount'] = $tableData->sum('diff_amount');
        $return['diff_invest_rtr'] = $tableData->sum('diff_invest_rtr');
        
        return $return;
    }
    
    public function getPennyInvestmentCheck($data = [])
    {
        $totalCount = $this->penny_investment_check_table->count();
        $tableData = $this->penny_investment_check_table;
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->where('merchant_id', $data['merchant_id']);
        }
        if (isset($data['percentage'])) {
            $tableData = $tableData->where('merchant_completed_percentate', $data['percentage']);
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $tableData->count();
        $return['amount'] = $tableData->sum('amount');
        $return['invest_rtr'] = $tableData->sum('invest_rtr');
        $return['under_writing_fee'] = $tableData->sum('under_writing_fee');
        $return['pre_paid'] = $tableData->sum('pre_paid');
        $return['commission_amount'] = $tableData->sum('commission_amount');
        $return['total_investment'] = $tableData->sum('total_investment');
        $return['expected_mgmnt_fee_amount'] = $tableData->sum('expected_mgmnt_fee_amount');
        $return['paid_mgmnt_fee'] = $tableData->sum('paid_mgmnt_fee');
        $return['mgmnt_fee_diff'] = $tableData->sum('mgmnt_fee_diff');
        $return['paid_participant_ishare'] = $tableData->sum('paid_participant_ishare');
        $return['user_balance_amount'] = $tableData->sum('user_balance_amount');
        
        return $return;
    }
    
    public function getMerchantRTRAndInvestorRtrCheck($data = [])
    {
        $Merchants  =new Merchant;
        $Merchants  =$Merchants->whereIn('label',[1,2]);
        $Merchants  =$Merchants->where('id','!=',9783);
        if (isset($data['merchant_id'])) {
            $Merchants = $Merchants->where('id', $data['merchant_id']);
        }
        $Merchants  =$Merchants->get();
        $totalCount =$Merchants->count();
        $totalCountfilterd = $Merchants->count();
        $datas=[];
        foreach ($Merchants as $key => $Merchant) {
            $merchant_id              =$Merchant->id;
            $max_participant_fund     =$Merchant->max_participant_fund;
            $funded                   =$Merchant->funded;
            $factor_rate              =$Merchant->factor_rate;
            $max_participant_fund_per =$funded/$max_participant_fund;
            $investor_rtr             =MerchantUser::where('merchant_id',$merchant_id)->sum('invest_rtr');
            $investor_rtr=round($investor_rtr,2);
            if($investor_rtr){
                $merchant_rtr =$Merchant->rtr/$max_participant_fund_per;
                $merchant_rtr =round($merchant_rtr,2);
                $diff         =round($merchant_rtr-$investor_rtr,2);
                if($diff){
                    $single['Merchant']               =$Merchant->name;
                    $single['merchant_id']            =$merchant_id;
                    $single['merchant_rtr']           =$merchant_rtr;
                    $single['syndication_percentage'] =$Merchant->max_participant_fund_per;
                    $single['investor_rtr']           =$investor_rtr;
                    $single['difference']             =$diff;
                    if(abs($diff)<0.5) {
                        $datas[]=$single;
                    }
                }
            }
        }
        $return['data']  = $datas;
        $return['count'] = count($datas);
        return $return;
    }
}
