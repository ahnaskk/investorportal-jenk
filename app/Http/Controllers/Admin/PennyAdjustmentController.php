<?php

namespace App\Http\Controllers\Admin;

use App\CompanyAmount;
use App\Http\Controllers\Controller;
use App\Library\Helpers\PennyAdjustmentTableBuilder;
use App\Library\Repository\Interfaces\IPennyAdjustmentRepository;
use App\MerchantUser;
use App\Models\Views\MerchantUserView;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Html\Builder;

class PennyAdjustmentController extends Controller
{
    use PennyAdjustmentTableBuilder;
    
    public function __construct(IPennyAdjustmentRepository $PennyAdjustment)
    {
        $this->PennyAdjustment = $PennyAdjustment;
    }
    
    public function LiquidityDifference(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Liquidity Difference';
        $page_description = 'Liquidity Difference';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->user_id) {
                $requestData['user_id'] = $request->user_id;
            }
            if ($request->diff) {
                $requestData['diff'] = $request->diff;
            }
            $data = $this->getLiqidityChangeList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::LiquidityDifferenceData'), 'type' => 'post', 'data' => 'function(data){
            data._token  = "'.csrf_token().'";
            data.user_id = $("#user_id").val();
            data.diff    = $("#diff").is(":checked")?1:0;
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            $(n.column(2).footer()).html(o.paid_mgmnt_fee);
            $(n.column(3).footer()).html(o.paid_participant_ishare);
            $(n.column(4).footer()).html(o.ctd);
            $(n.column(5).footer()).html(o.total_funded);
            $(n.column(6).footer()).html(o.commission_amount);
            $(n.column(7).footer()).html(o.under_writing_fee);
            $(n.column(8).footer()).html(o.pre_paid);
            $(n.column(9).footer()).html(o.total_credits);
            $(n.column(10).footer()).html(o.existing_liquidity);
            $(n.column(11).footer()).html(o.actual_liquidity);
            $(n.column(12).footer()).html(o.diff);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[12, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getLiqidityChangeList($requestData));
        $users = DB::table('user_details_liquidity_check_view')->pluck('Investor', 'user_id')->toArray();
        
        return view('admin.PennyAdjustment.LiquidityDifference', compact('tableBuilder', 'page_title', 'users', 'page_description'));
    }
    
    public function UpdateLiquidityDifference()
    {
        try {
            DB::beginTransaction();
            Artisan::call('Update:UserDetailLiquidity');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->route('PennyAdjustment::LiquidityDifference')->withSuccess('Successfully Updated');
    }
    
    public function MerchantValueDifference(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Merchant Value Difference';
        $page_description = 'Merchant Value Difference';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) {
                $requestData['merchant_id'] = $request->merchant_id;
            }
            if ($request->rtr_diff) {
                $requestData['rtr_diff'] = $request->rtr_diff;
            }
            if ($request->diff_final_participant_share) {
                $requestData['diff_final_participant_share'] = $request->diff_final_participant_share;
            }
            $data = $this->getMerchantValueDifferenceList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::MerchantValueDifferenceData'), 'type' => 'post', 'data' => 'function(data){
            data._token                       = "'.csrf_token().'";
            data.merchant_id                  = $("#merchant_id").val();
            data.rtr_diff                     = $("#rtr_diff").is(":checked")?1:0;
            data.diff_final_participant_share = $("#diff_final_participant_share").is(":checked")?1:0;
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            $(n.column(1).footer()).html(o.funded);
            $(n.column(2).footer()).html(o.max_participant_fund);
            $(n.column(3).footer()).html(o.percentage);
            $(n.column(4).footer()).html(o.factor_rate);
            $(n.column(5).footer()).html(o.existing_rtr);
            $(n.column(6).footer()).html(o.actual_rtr);
            $(n.column(7).footer()).html(o.rtr_diff);
            $(n.column(8).footer()).html(o.payment);
            // $(n.column(9).footer()).html(o.existing_final_participant_share);
            $(n.column(11).footer()).html(o.existing_final_participant_share);
            // $(n.column(12).footer()).html(o.actual_final_participant_share);
            // $(n.column(13).footer()).html(o.diff_final_participant_share);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getMerchantValueDifferenceList($requestData));
        $merchants = DB::table('participent_payments_check_view')->pluck('Merchant', 'merchant_id')->toArray();
        
        return view('admin.PennyAdjustment.MerchantValueDifference', compact('tableBuilder', 'page_title', 'merchants', 'page_description'));
    }
    
    public function UpdateMerchantValueRTRDifference()
    {
        try {
            DB::beginTransaction();
            Artisan::call('Update:MerchantValueRTRDifference');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->route('PennyAdjustment::MerchantValueDifference')->withSuccess('Successfully Updated');
    }
    
    public function CompanyAmountDifference(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Company Amount Difference';
        $page_description = 'Company Amount Difference';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) {
                $requestData['merchant_id'] = $request->merchant_id;
            }
            if ($request->merchant_company_diff) {
                $requestData['merchant_company_diff'] = $request->merchant_company_diff;
            }
            if ($request->invsetor_company_diff) {
                $requestData['invsetor_company_diff'] = $request->invsetor_company_diff;
            }
            if ($request->percentage) {
                $requestData['percentage'] = $request->percentage;
            }
            $data = $this->getCompanyAmountDifferenceList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::CompanyAmountDifferenceData'), 'type' => 'post', 'data' => 'function(data){
            data._token                = "'.csrf_token().'";
            data.merchant_id           = $("#merchant_id").val();
            data.invsetor_company_diff = $("#invsetor_company_diff").is(":checked")?1:0;
            data.merchant_company_diff = $("#merchant_company_diff").is(":checked")?1:0;
            data.percentage            = $("#percentage").val();
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            $(n.column(8).footer()).html(o.merchant_company_diff);
            $(n.column(9).footer()).html(o.invsetor_company_diff);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getCompanyAmountDifferenceList($requestData));
        $merchants = DB::table('company_amount_pivot_check_view')->pluck('Merchant', 'merchant_id')->toArray();
        $percentage = DB::table('company_amount_pivot_check_view')->orderBy('percentage')->pluck('percentage', 'percentage')->toArray();
        
        return view('admin.PennyAdjustment.CompanyAmountDifference', compact('tableBuilder', 'page_title', 'merchants', 'percentage', 'page_description'));
    }
    
    public function UpdateInvestorBasedCompanyAmountDifference()
    {
        try {
            DB::beginTransaction();
            Artisan::call('Update:InvestorBasedCompanyAmountDifference');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->route('PennyAdjustment::CompanyAmountDifference')->withSuccess('Successfully Updated');
    }
    
    public function UpdateMerchantBasedCompanyAmountDifference()
    {
        try {
            DB::beginTransaction();
            Artisan::call('Update:MerchantBasedCompanyAmountDifference');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->route('PennyAdjustment::CompanyAmountDifference')->withSuccess('Successfully Updated');
    }
    
    public function ZeroParticipantAmount(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Zero Participant Amount';
        $page_description = 'Zero Participant Amount';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) {
                $requestData['merchant_id'] = $request->merchant_id;
            }
            $data = $this->getZeroParticipantAmountList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::ZeroParticipantAmountData'), 'type' => 'post', 'data' => 'function(data){
            data._token      = "'.csrf_token().'";
            data.merchant_id = $("#merchant_id").val();
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            $(n.column(1).footer()).html(o.amount);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getZeroParticipantAmountList($requestData));
        $merchants = DB::table('zero_payment_amount_check_view')->pluck('Merchant', 'merchant_id')->toArray();
        
        return view('admin.PennyAdjustment.ZeroParticipantAmount', compact('tableBuilder', 'page_title', 'merchants', 'page_description'));
    }
    
    public function RemoveZeroParticipantAmount()
    {
        try {
            DB::beginTransaction();
            Artisan::call('Remove:ZeroParticipantAmount');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->route('PennyAdjustment::ZeroParticipantAmount')->withSuccess('Successfully Removed');
    }
    
    public function FinalParticipantShare(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Final Participant Share';
        $page_description = 'Final Participant Share';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) {
                $requestData['merchant_id'] = $request->merchant_id;
            }
            $data = $this->getFinalParticipantShareList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::FinalParticipantShareData'), 'type' => 'post', 'data' => 'function(data){
            data._token                              = "'.csrf_token().'";
            data.merchant_id                         = $("#merchant_id").val();
            data.diff                                = $("#diff").val();
            data.expected_existing_participant_share = $("#expected_existing_participant_share").val();
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            $(n.column(5).footer()).html(o.expected_existing_participant_share);
            $(n.column(7).footer()).html(o.diff);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getFinalParticipantShareList($requestData));
        $merchants = DB::table('merchants')->pluck('name', 'id')->toArray();
        
        return view('admin.PennyAdjustment.FinalParticipantShare', compact('tableBuilder', 'page_title', 'merchants', 'page_description'));
    }
    
    public function MerchantInvestorShareDifference(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Merchant Investor Share Difference';
        $page_description = 'Merchant Investor Share Difference';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) {
                $requestData['merchant_id'] = $request->merchant_id;
            }
            $data = $this->getMerchantInvestorShareDifferenceList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::MerchantInvestorShareDifferenceData'), 'type' => 'post', 'data' => 'function(data){
            data._token      = "'.csrf_token().'";
            data.merchant_id = $("#merchant_id").val();
            data.diff        = $("#diff").val();
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            // $(n.column(7).footer()).html(o.diff);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getMerchantInvestorShareDifferenceList($requestData));
        $merchants = DB::table('merchants')->pluck('name', 'id')->toArray();
        
        return view('admin.PennyAdjustment.MerchantInvestorShareDifference', compact('tableBuilder', 'page_title', 'merchants', 'page_description'));
    }
    
    public function UpdateMerchantInvestorShareDifference()
    {
        try {
            DB::beginTransaction();
            Artisan::call('Update:MerchantInvestorShareDifference');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->route('PennyAdjustment::MerchantInvestorShareDifference')->withSuccess('Successfully Updated');
    }
    
    public function MerchantsFundAmountCheck(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Merchants Fund Amount Check';
        $page_description = 'Merchants Fund Amount Check';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) {
                $requestData['merchant_id'] = $request->merchant_id;
            }
            if ($request->mgmnt_fee_diff) {
                $requestData['mgmnt_fee_diff'] = $request->mgmnt_fee_diff;
            }
            if ($request->percentage) {
                $requestData['percentage'] = $request->percentage;
            }
            $data = $this->getMerchantsFundAmountCheckList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::MerchantsFundAmountCheckData'), 'type' => 'post', 'data' => 'function(data){
            data._token         = "'.csrf_token().'";
            data.merchant_id    = $("#merchant_id").val();
            data.mgmnt_fee_diff = $("#mgmnt_fee_diff").val();
            data.percentage     = $("#percentage").val();
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            $(n.column(9).footer()).html(o.mgmnt_fee_diff);
            $(n.column(11).footer()).html(o.user_balance_amount);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getMerchantsFundAmountCheckList($requestData));
        $merchants = DB::table('merchants')->pluck('name', 'id')->toArray();
        $percentage = DB::table('merchants_fund_amount_check_view')->orderByDesc('merchant_completed_percentate')->pluck('merchant_completed_percentate', 'merchant_completed_percentate')->toArray();
        
        return view('admin.PennyAdjustment.MerchantsFundAmountCheck', compact('tableBuilder', 'page_title', 'merchants', 'page_description', 'percentage'));
    }
    
    public function InvestmentAmountCheck(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Investment Floor Amount Check';
        $page_description = 'Investment Floor Amount Check';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) {
                $requestData['merchant_id'] = $request->merchant_id;
            }
            if ($request->diff_amount) {
                $requestData['diff_amount'] = $request->diff_amount;
            }
            $data = $this->getInvestmentAmountCheckList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::InvestmentAmountCheckData'), 'type' => 'post', 'data' => 'function(data){
            data._token         = "'.csrf_token().'";
            data.merchant_id    = $("#merchant_id").val();
            data.diff_amount    = $("#diff_amount").is(":checked")?1:0;
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            $(n.column(4).footer()).html(o.diff_amount);
            $(n.column(7).footer()).html(o.diff_invest_rtr);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getInvestmentAmountCheckList($requestData));
        $merchants = DB::table('merchants')->pluck('name', 'id')->toArray();
        
        return view('admin.PennyAdjustment.InvestmentAmountCheck', compact('tableBuilder', 'page_title', 'merchants', 'page_description'));
    }
    
    public function InvestmentAmountAdjuster($id = null)
    {
        try {
            DB::beginTransaction();
            $merchants = DB::table('investment_amount_grouped_check_view');
            $merchants->where('diff_amount', '!=', 0);
            $merchants->where('merchant_id', $id);
            $merchants = $merchants->pluck('merchant_id', 'merchant_id');
            foreach ($merchants as $merchant_id) {
                $return_result = MerchantUser::InvestmentAmountAdjuster($merchant_id);
                if ($return_result['result'] != 'success') {
                    throw new \Exception($return_result['result'], 1);
                }
            }
            DB::commit();
            
            return redirect()->route('PennyAdjustment::InvestmentAmountCheck')->withSuccess('Successfully Removed');
        } catch (\Exception $e) {
            DB::rollback();
        }
    }
    
    public function PennyInvestment(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Penny Investment';
        $page_description = 'Penny Investment';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) {
                $requestData['merchant_id'] = $request->merchant_id;
            }
            if ($request->percentage) {
                $requestData['percentage'] = $request->percentage;
            }
            $data = $this->getPennyInvestmentList($requestData);
            
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::PennyInvestmentData'), 'type' => 'post', 'data' => 'function(data){
            data._token      = "'.csrf_token().'";
            data.merchant_id = $("#merchant_id").val();
            data.percentage  = $("#percentage").val();
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
            $(n.column(3).footer()).html(o.amount);
            $(n.column(4).footer()).html(o.invest_rtr);
            $(n.column(5).footer()).html(o.under_writing_fee);
            $(n.column(6).footer()).html(o.pre_paid);
            $(n.column(7).footer()).html(o.commission_amount);
            $(n.column(8).footer()).html(o.total_investment);
            $(n.column(10).footer()).html(o.paid_mgmnt_fee);
            $(n.column(12).footer()).html(o.paid_participant_ishare);
            $(n.column(13).footer()).html(o.user_balance_amount);
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getPennyInvestmentList($requestData));
        $merchants = DB::table('penny_investment_check_view')->pluck('Merchant', 'merchant_id')->toArray();
        $percentage = DB::table('penny_investment_check_view')->orderByDesc('merchant_completed_percentate')->pluck('merchant_completed_percentate', 'merchant_completed_percentate');
        
        return view('admin.PennyAdjustment.PennyInvestment', compact('tableBuilder', 'page_title', 'merchants', 'page_description', 'percentage'));
    }
    
    public function RemovePennyInvestment()
    {
        try {
            DB::beginTransaction();
            Artisan::call('Remove:PennyInvestment');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->route('PennyAdjustment::PennyInvestment')->withSuccess('Successfully Removed');
    }
    
    public function MerchantRTRAndInvestorRtr(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Merchant RTR & Investor RTR';
        $page_description = 'Difference Between Merchant RTR & Investor RTR';
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->merchant_id) { $requestData['merchant_id'] = $request->merchant_id; }
            $data = $this->getMerchantRTRAndInvestorRtrList($requestData);
            return $data;
        }
        $tableBuilder->ajax(['url' => route('PennyAdjustment::MerchantRTRAndInvestorRtrData'), 'type' => 'post', 'data' => 'function(data){
            data._token      = "'.csrf_token().'";
            data.merchant_id = $("#merchant_id").val();
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => 'function (nRow, aData, iDataIndex) {
            var n=this.api(),o=table.ajax.json();
        }', 'pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'desc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($this->getMerchantRTRAndInvestorRtrList($requestData));
        $merchants = DB::table('merchants')->pluck('name', 'id')->toArray();
        return view('admin.PennyAdjustment.MerchantRTRAndInvestorRtr', compact('tableBuilder', 'page_title', 'merchants', 'page_description'));
    }
    
    public function AdjustInvestorRtr($merchant_id=null)
    {
        try {
            DB::beginTransaction();
            if($merchant_id){
                Artisan::call('check:merchant_rtr_and_investor_rtr true '.$merchant_id);
            } else {
                Artisan::call('check:merchant_rtr_and_investor_rtr true');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->back()->withSuccess('Successfully Updated');
    }
    
    public function UpdateInvestorRtr($merchant_id=null)
    {
        try {
            DB::beginTransaction();
            if($merchant_id){
                Artisan::call('check:factor_rate_and_investor_rtr '.$merchant_id);
            } else {
                Artisan::call('check:factor_rate_and_investor_rtr');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        
        return redirect()->back()->withSuccess('Successfully Updated');
    }
}
