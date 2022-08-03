<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use InvestorHelper;
use Exception;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use MTB;
use BankHelper;
use Yajra\DataTables\Html\Builder;
use App\Library\Repository\Interfaces\IInvestorRepository;
use App\InvestorRoiRate;
use App\PaymentInvestors;
use App\ParticipentPayment;
use App\User;

class InvestorController extends Controller
{
    protected $role;
    protected $user;
    use CreditCardStripe;
    
    public function __construct(IRoleRepository $role, IUserRepository $user, IMerchantRepository $merchant, ILabelRepository $label,IInvestorRepository $investor)
    {
        $this->merchant = $merchant;
        $this->role = $role;
        $this->user = $user;
        $this->label = $label;
        $this->InvestorRepository=$investor;
    }
    
    public function index(Request $request, Builder $tableBuilder)
    {
        $page_title = 'All Account';
        $ReturnData=$this->InvestorRepository->iIndex($request,$tableBuilder);
        if ($request->ajax() || $request->wantsJson()) {
            return $ReturnData;
        }
        return view('admin.investors.index')
        ->with('page_title',$page_title)
        ->with('tableBuilder',$ReturnData['tableBuilder'])
        ->with('investor_types',$ReturnData['investor_types'])
        ->with('companies',$ReturnData['companies'])
        ->with('recurrence_types',$ReturnData['recurrence_types'])
        ->with('label',$ReturnData['label'])
        ->with('Roles',$ReturnData['Roles'])
        ;
    }
    
    public function investorListDownload(Request $request)
    {
        $fileName = 'Account List '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $export = $this->InvestorRepository->iInvestorDownload($request);
        return Excel::download($export, $fileName);
    }
    
    public function create(Request $request, IRoleRepository $role)
    {
        $page_title  = 'Create New Accounts';
        $action      = 'create';
        $requestData = $this->InvestorRepository->iCreate($request);
        return view('admin.investors.create')
        ->with('page_title',$page_title)
        ->with('action',$action)
        ->with('investor_types',$requestData['investor_types'])
        ->with('investor_admin',$requestData['investor_admin'])
        ->with('recurrence_types',$requestData['recurrence_types'])
        ->with('bank',$requestData['bank'])
        ->with('groupBy',$requestData['groupBy'])
        ->with('companies',$requestData['companies'])
        ->with('company_permission',$requestData['company_permission'])
        ->with('user_id',$requestData['user_id'])
        ->with('label',$requestData['label'])
        ->with('Roles',$requestData['Roles'])
        ->with('fee_values',$requestData['fee_values'])
        ->with('roi_rates',$requestData['roi_rates'])
        ;
    }
    
    public function selectType(Request $request)
    {
        try {
            $investors=$this->InvestorRepository->iSelectType($request);
            if(!$investors){
                throw new \Exception("empty Result", 1);
            }
            return response()->json(['msg' => 'success', 'status' => 1, 'investors' => $investors]);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'success', 'status' => 0]);
        }
    }
    
    public function investorsLogList(Request $request)
    {
        try {
            $return_result=$this->InvestorRepository->iInvestorsLogList($request);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            $investors=$return_result['investors'];
            return response()->json(['status' => 1, 'result' => $investors]);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }
    }
    
    public function getSelect2Investors(Request $request)
    {
        $ReturnData      = $this->InvestorRepository->iGetSelect2Investors($request);
        $investors_array = $ReturnData['investors_array'];
        $pagination      = $ReturnData['pagination'];
        return response()->json(['results' => $investors_array, 'pagination' => $pagination]);
    }
    
    public function bankEdit($investor_id,Request $request)
    {
        try {
            $page_title = 'Create Bank Details';
            $action     = 'create';
            $return_result=$this->InvestorRepository->iBankCreate($investor_id,$request);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            return view('admin.investors.bank_details')
            ->with('page_title',$page_title)
            ->with('action',$action)
            ->with('investor_id',$investor_id)
            ->with('investor',$return_result['investor'])
            ;
        } catch (\Exception $e) {
            $request->session()->flash('error', $e->getMessage());
            return redirect(route("admin::investors::index"));
        }
    }
    
    public function bank($id,Request $request)
    {
        try {
            $page_title = 'Edit Bank Details';
            $action     = 'Edit';
            $returnData=$this->InvestorRepository->iBankEdit($request,$id);
            if($returnData['result'] != 'success') throw new \Exception($returnData['result']);
            return view('admin.investors.bank_details')
            ->with('page_title',$page_title)
            ->with('action',$action)
            ->with('id',$id)
            ->with('bank_details',$returnData['bank_details'])
            ->with('investor',$returnData['investor'])
            ->with('masked_accountNo',$returnData['masked_accountNo'])
            ;
        } catch (\Exception $e) {
            $request->session()->flash('error', $e->getMessage());
            return redirect(route("admin::investors::index"));
        }
    }
    public function auto_company_filter(Request $request)
    {
        $users = $this->InvestorRepository->IAuto_company_filter($request);
        return response()->json(['result' => $users]);
        
    }
    public function company_filter(Request $request)
    {
        $users = $this->InvestorRepository->ICompany_filter($request);
        return response()->json(['result' => $users]);
    }
    
    public function updateBank(Request $request)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->InvestorRepository->iBankCreatOrUpdate($request);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            $request->session()->flash('message', $return_result['message']);
            DB::commit();
            if ($request->bid) {
                return redirect('admin/investors/bank_details/'.$request->investor_id);
               // return redirect()->back();
            } else {
                return redirect('admin/investors/bank_details/'.$request->investor_id);
               // return redirect('admin/investors/bank/'.$return_result['id']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }
    
    public function bank_details_list($id,Request $request,Builder $tableBuilder)
    {
        try {
            $page_title = 'Bank Details List';
            $return_result=$this->InvestorRepository->iBankIndex($request,$tableBuilder,$id);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            return view('admin.investors.bank_details_list')
            ->with('page_title',$page_title)
            ->with('id',$id)
            ->with('tableBuilder',$return_result['tableBuilder'])
            ->with('investor',$return_result['investor'])
            ;
        } catch (\Exception $e) {
            $request->session()->flash('error', $e->getMessage());
            return redirect(route("admin::investors::index"));
        }
    }
    public function edit_admin_bank_accounts($id)
    {
        $page_title   = 'Edit Admin Bank Accounts';
        $action       = 'edit';
        $bank_details = BankHelper::BankDetails($id);
        return view('admin.admins.bank_details', compact('page_title', 'action', 'bank_details'));
    }
    
    public function getAdminBankaccountDetails($id)
    {
        $data['investor_id']=$id;
        $List = BankHelper::getList($data);
        return BankHelper::Datatable($List);
    }
    
    public function deleteBankAccountDetails(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            BankHelper::Delete($id);
            $request->session()->flash('message', 'Bank Account Deleted Successfully!');
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Bank Account Deletion Failed!');
        }
        
        return redirect()->back();
    }
    
    public function storeCreate(Requests\AdminCreateInvestorRequest $request)
    {
        try {
            DB::beginTransaction();
            $return_function=$this->InvestorRepository->iStore($request);
            if($return_function['result'] != 'success') throw new \Exception($return_function['result'], 1);
            $request->session()->flash('message', 'New Account Created!');
            DB::commit();
            return redirect()->route('admin::investors::portfolio',$return_function['user_id']);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }
    
    public function edit(Request $request, $id, IRoleRepository $role)
    {
        try {
            $page_title = 'Edit Account';
            $action     = 'edit';
            $returnData = $this->InvestorRepository->iEdit($request, $id);
            if($returnData['result'] != 'success') throw new \Exception($returnData['result'], 1);
            return view('admin.investors.create')
            ->with('action',$action)
            ->with('page_title',$page_title)
            ->with('bank',$returnData['bank'])
            ->with('investor',$returnData['investor'])
            ->with('recurrence_types',$returnData['recurrence_types'])
            ->with('investor_types',$returnData['investor_types'])
            ->with('investor_admin',$returnData['investor_admin'])
            ->with('groupBy',$returnData['groupBy'])
            ->with('companies',$returnData['companies'])
            ->with('company_permission',$returnData['company_permission'])
            ->with('user_id',$returnData['user_id'])
            ->with('label',$returnData['label'])
            ->with('Roles',$returnData['Roles'])
            ->with('fee_values',$returnData['fee_values'])
            ->with('roi_rates',$returnData['roi_rates'])
            ;
        } catch (\Exception $e) {
            $result=$e->getMessage();
            $request->session()->flash('error', $result);
            if($result=="Permission Denied"){
                return view('admin.permission_denied');
            }
            return redirect(route("admin::investors::index"));
        }
    }
    
    public function update(Requests\AdminUpdateInvestorRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->InvestorRepository->iUpdate($request, $id);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', 'Account updated');
            DB::commit();
            return redirect()->route('admin::investors::portfolio',$id);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }
    
    public function delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->InvestorRepository->iDelete($request, $id);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            DB::commit();
            $request->session()->flash('message', 'Account deleted!');
            return redirect()->to('admin/investors/')->with('message', 'Account Deleted!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    
    public function portfolioDownload(Request $request)
    {
        $fileName = 'Portfolio '.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $export = $this->InvestorRepository->iPortfolioDownload($request);
        return Excel::download($export, $fileName);
    }
    
    public function portfolio(Request $request, Builder $tableBuilder, $userId)
    {   
        try {
            $page_title = 'Portfolio';
            $ReturnData=$this->InvestorRepository->iPortfolio($request,$tableBuilder,$userId);
            if ($request->ajax() || $request->wantsJson()) {
                return $ReturnData;
            }
            if(($ReturnData['result']!='success')){
                throw new \Exception($ReturnData['result'], 1);

            }
            return view('admin.investors.portfolio')
            ->with('page_title',$page_title)
            ->with('substatus',$ReturnData['substatus'])
            ->with('tableBuilder',$ReturnData['tableBuilder'])
            ->with('chart_data',$ReturnData['chart_data'])
            ->with('merchant_count',$ReturnData['merchant_count'])
            ->with('liquidity',$ReturnData['liquidity'])
            ->with('reserved_liquidity',$ReturnData['reserved_liquidity'])
            ->with('pending_credit_ach_request',$ReturnData['pending_credit_ach_request'])
            ->with('pending_debit_ach_request',$ReturnData['pending_debit_ach_request'])
            ->with('invested_amount',$ReturnData['invested_amount'])
            ->with('blended_rate',$ReturnData['blended_rate'])
            ->with('total_default',$ReturnData['total_default'])
            ->with('default_percentage',$ReturnData['default_percentage'])
            ->with('total_requests',$ReturnData['total_requests'])
            ->with('ctd',$ReturnData['ctd'])
            ->with('total_rtr',$ReturnData['total_rtr'])
            ->with('investor',$ReturnData['investor'])
            ->with('portfolio_value',$ReturnData['portfolio_value'])
            ->with('principal_investment',$ReturnData['principal_investment'])
            ->with('userId',$ReturnData['userId'])
            ->with('overpayment',$ReturnData['overpayment'])
            ->with('c_invested_amount',$ReturnData['c_invested_amount'])
            ->with('funded_amount',$ReturnData['funded_amount'])
            ->with('net_rtr',$ReturnData['net_rtr'])
            ->with('average',$ReturnData['average'])
            ->with('average_principal_investment',$ReturnData['average_principal_investment'])
            ->with('profit',$ReturnData['profit'])
            ->with('paid_to_date',$ReturnData['paid_to_date'])
            ->with('roi',$ReturnData['roi'])
            ->with('anticipated_rtr',$ReturnData['anticipated_rtr'])
            ->with('existing_liquidity',$ReturnData['existing_liquidity'])
            ->with('actual_liquidity',$ReturnData['actual_liquidity'])
            ->with('carry',$ReturnData['carry'])
            ->with('investor_type',$ReturnData['investor_type'])
            ;
        } catch (\Exception $e) {
            $request->session()->flash('error', $e->getMessage());
            return redirect(route("admin::investors::index"));
        }
    }
    
    public function transactions(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Transaction Report';
        $returnData=$this->InvestorRepository->iTransactions($request,$tableBuilder);
        if ($request->ajax() || $request->wantsJson()) {
            return $returnData;
        }
        return view('admin.investors.transaction_report')
        ->with('page_title',$page_title)
        ->with('investors',$returnData['investors'])
        ->with('tableBuilder',$returnData['tableBuilder'])
        ->with('categories',$returnData['categories'])
        ->with('companies',$returnData['companies'])
        ->with('investor_types',$returnData['investor_types'])
        ->with('statuses',$returnData['statuses'])
        ->with('allMerchants',$returnData['allMerchants'])
        ;
    }
    
    public function transactionReportDownload(Request $request)
    {
        $fileName = 'Transaction Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $export   = $this->InvestorRepository->iTransactionReportDownload($request);
        return Excel::download($export, $fileName);
    }
    
    public function investorAchCheck_edit_ajax(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->InvestorRepository->iInvestorAchCheck_edit($request, $id);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            DB::commit();
            $return['result'] = 'success';
        } catch (Exception $e) {
            DB::rollback();
            $return['result'] = $e->getMessage();
        }
        return response()->json($return);
    }
    
    public function achRequest(Request $request, $id)
    {
        try {
            $page_title = 'Investor Ach Debit Request (Transfer To Velocity)';
            if (! $request->isMethod('post')) {
                $return_result=$this->InvestorRepository->iAchRequestPage($id);
                if($return_result['result'] != 'success') {
                    $request->session()->flash('error', $return_result['result']);
                    return redirect()->back();
                }
                return view('admin.investors.investor_ach_request')
                ->with('page_title',$page_title)
                ->with('Investor',$return_result['Investor'])
                ->with('BankDetails',$return_result['BankDetails'])
                ;
            }
            $Banktype = 'debit';
            $return_result=$this->InvestorRepository->iAchDebitRequest($request);
            if($return_result['result'] != 'success')  throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', $return_result['result']);
            return redirect()->back();
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
            $request->session()->flash('error', $return['result']);
            return redirect()->back()->withInput()->withErrors($return['result']);
        }
    }
    
    public function achCreditRequest(Request $request, $id)
    {
        try {
            $page_title = 'Investor Ach Credit Request (Transfer To Bank )';
            if (! $request->isMethod('post')) {
                $return_result=$this->InvestorRepository->iAchRequestPage($id);
                if($return_result['result'] != 'success') {
                    $request->session()->flash('error', $return_result['result']);
                    return redirect()->back();
                }
                $amount = $request->amount ?? 0;
                return view('admin.investors.investor_ach_credit_request')
                ->with('page_title',$page_title)
                ->with('amount',$amount)
                ->with('Investor',$return_result['Investor'])
                ->with('BankDetails',$return_result['BankDetails'])
                ->with('transaction_categories',$return_result['transaction_categories'])
                ;
            }
            DB::beginTransaction();
            $Banktype = 'credit';
            $return_result=$this->InvestorRepository->iAchCreditRequest($request);
            if($return_result['result'] != 'success')  throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', $return_result['result']);
        } catch (\Exception $e) {
            DB::rollback();
            $return['result'] = $e->getMessage();
            $request->session()->flash('error', $return['result']);
            return redirect()->back()->withInput()->withErrors($return['result']);
        }
        return redirect()->back();
    }
    
    public function SyndicationPayments(Request $request, Builder $tableBuilder)
    {
        $page_title = 'Investor Syndication Payments';
        $returnData=$this->InvestorRepository->iSyndicationPayments($tableBuilder);
        return view('admin.investors.syndication_payment')
        ->with('page_title',$page_title)
        ->with('investorsList',$returnData['investorsList'])
        ->with('tableBuilder',$returnData['tableBuilder'])
        ->with('recurrence_types',$returnData['recurrence_types'])
        ->with('recurrence_type',$returnData['recurrence_type'])
        ->with('paymentDate',$returnData['paymentDate'])
        ->with('same_day_button',$returnData['same_day_button'])
        ;
    }
    public function SyndicationPaymentsTable(Request $request)
    {
        return $this->InvestorRepository->iSyndicationPaymentsDataTable($request);
    }
    public function changeAutoSyndicatePaymentStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            $status  = 0;
            $return_result=$this->InvestorRepository->iChangeAutoSyndicatePaymentStatus($request);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            $investor = $return_result['investor'];
            $changed  = $return_result['changed'];
            $message  = 'Auto Syndicate Status of '.$investor.' turned '.$changed;
            $status   = 1;
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
        }
        return response()->json(['message' => $message, 'status' => $status]);
    }
    
    public function sendSyndicationPayments(Request $request)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->InvestorRepository->iSendSyndicationPayments($request);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $request->session()->flash('error', $e->getMessage());
        }
        return redirect()->back();
    }
    
    public function sendSyndicationPaymentSingle(Request $request)
    {
        try {
            $single = $request->all();
            if (! $single['amount']) { throw new Exception('Amount Required', 1); }
            $return_function = $this->InvestorRepository->iSyndicationPaymentSingleFunction($single);
            if ($return_function['result'] != 'success') {
                throw new \Exception($return_function['result'], 1);
            }
            $return['result'] = 'success';
            $return['message'] = $return_function['message'];
        } catch (Exception $e) {
            $return['result'] = $e->getMessage();
        }
        
        return response()->json($return);
    }
    
    public function investorSyndicationReport(Request $request, $invester_id)
    {
        $page_title = 'Investor Syndication Report';
        $ReturnData = $this->InvestorRepository->iInvestorSyndicationReport($request,$invester_id);
        return view('admin.investors.syndication_report')
        ->with('page_title',$page_title)
        ->with('invester_id',$invester_id)
        ->with('data',$ReturnData['data'])
        ->with('date_start',$ReturnData['date_start'])
        ->with('date_end',$ReturnData['date_end'])
        ;
    }
    
    public function liquidity_update($user_id)
    {
        DB::beginTransaction();
        $description = 'General Liquidity Update';
        InvestorHelper::update_liquidity([$user_id], $description, 0);
        DB::commit();
        return redirect()->route('admin::investors::portfolio', $user_id)->withSuccess('Successfully Updated');
    }
    public function NewPortfolio($investor_id)
    {
        $page_title = 'New Portfolio';
        $userId=$investor_id;
        $Investor = $this->InvestorRepository->PortfolioValues($userId);
        return view('admin.investors.new_portfolio')
        ->with('page_title',$page_title)
        ->with('Investor',$Investor)
        ->with('userId',$userId)
        ->with('investor_id',$investor_id)
        ->with('substatus',$Investor['data']['substatus'])
        ;
    }
    public function MerchantData(Request $request)
    {
        return $this->InvestorRepository->iMerchantDataTable($request);
    }
    public function PaymentData(Request $request,Builder $tableBuilder)
    {
        return $this->InvestorRepository->iPaymentData($request,$tableBuilder);
    }
    public function MerchantPaymentData(Request $request)
    {
        return $this->InvestorRepository->iMerchantPaymentData($request);
    }
    public function InvestorReAssignmentHistoryData(Request $request,Builder $tableBuilder)
    {
        return $this->InvestorRepository->iInvestorReAssignmentHistoryData($request,$tableBuilder);
    }
    public function InvestorReAssignmentMerchantHistoryData(Request $request)
    {
        $this->InvestorRepository->iInvestorReAssignmentMerchantHistoryData($request);
    }
    public function delete_transactions(Request $request)
    {
        try{
            $transactionDelete = $this->InvestorRepository->InvestorTransactionBulkDelete($request->multi_id);
            return response()->json(['status' => 1, 'msg' => $transactionDelete]);
        } catch (Exception $e){
            return response()->json(['status' => 0, 'msg' => "Transaction ID Required"]);
        }  
    }

    public function editRoiRate($id){
        $valid_user = $this->InvestorRepository->checkValidInvestorForRoi($id);
        if(!$valid_user){
            return redirect()->route('admin::investors::index');
        }
        $page_title = "Pref Return";
        $action = "Create";
        $roi_rates=FFM::fees_array(0,15);
        $investors = User::find($id);

        return view('admin.investors.roi_historic')
        ->with('page_title',$page_title)
        ->with('roi_rates',$roi_rates)
        ->with('user_id',$id)
        ->with('investors',$investors)
        ->with('action',$action);
        

    }
    public function editRoiRateDetails($user_id,$id){
        $valid_user = $this->InvestorRepository->checkValidInvestorForRoi($user_id);
        if(!$valid_user){
            return redirect()->route('admin::investors::index');
        }
        $page_title = "Edit Pref Return";
        $action = "Edit";
        $roi_data = array();
        if(isset($id)){
            if($id!=null){
                $roi_data = $this->InvestorRepository->investorRoiDetails($id,$user_id);
            }
        }
        $investors = User::find($user_id);

        $roi_rates=FFM::fees_array(0,15);
        return view('admin.investors.roi_historic')
        ->with('page_title',$page_title)
        ->with('roi_rates',$roi_rates)
        ->with('user_id',$user_id)
        ->with('id',$id)
        ->with('action',$action)
        ->with('investors',$investors)

        ->with('roi_data',$roi_data);
        
    }
    public function saveRoiRate(Request $request,$id){
        $valid_user = $this->InvestorRepository->checkValidInvestorForRoi($id);
        if(!$valid_user){
            return redirect()->route('admin::investors::index');
        }
        $data['user_id']=$id;
        $data['roi_rate'] = $request->roi_rate;
        $data['from_date'] = $request->date_start;
        $data['to_date'] = $request->date_end;
        try{
            DB::beginTransaction();
            $result = InvestorHelper::saveRoiRate($data);
            if(!$result) {
                throw new \Exception('Something went wrong');
            }
            DB::commit();
            return redirect()->route('admin::investors::investor-pref-return', $id)->withSuccess('Successfully Created');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withError($e->getMessage());
        }
    }
    public function updateRoiRate($id,Request $request){
        $data['roi_rate'] = $request->roi_rate;
        $data['from_date'] = $request->date_start;
        $data['to_date'] = $request->date_end;
        $data['id'] = $request->table_id;
        $data['user_id'] = $request->user_id;
        try{
            DB::beginTransaction();
            $result = InvestorHelper::updateRoiRate($data);
            if(!$result) {
                throw new \Exception('Something went wrong');
            }
            DB::commit();
            return redirect()->route('admin::investors::investor-pref-return', $request->user_id)->withSuccess('Successfully Updated');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withError($e->getMessage());
        }
       
        
    }
    public function updateReserveLiquidity($id,Request $request){
        $reserved_liquidity = $this->InvestorRepository->investorReservedLiquidityDetails($id);
        $data['reserve_percentage'] = (isset($request->reserve_percentage)) ? $request->reserve_percentage : $reserved_liquidity->reserve_percentage;
        $data['id'] = $request->table_id;
        $data['user_id'] = $request->user_id;
        $data['from_date'] = $request->date_start;
        $data['to_date'] = $request->date_end;
        
        try{
            DB::beginTransaction();
            $result = InvestorHelper::updateReserveLiquidity($data);
            if(!$result) {
                throw new \Exception('Something went wrong');
            }
            DB::commit();
            InvestorHelper::update_liquidity([$request->user_id],$description='');
            return redirect()->route('admin::investors::investor-reserve-liquidity', $request->user_id)->withSuccess('Successfully Updated');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withError($e->getMessage());
        }
       
        
    }
    public function investorRoiRate($id,Request $request, Builder $tableBuilder){
        $valid_user = $this->InvestorRepository->checkValidInvestorForRoi($id);
        $investors = User::find($id);
        if(!$valid_user){
            return redirect()->route('admin::investors::index');
        }
        $page_title = 'Pref Return';
        $ReturnData=$this->InvestorRepository->roiRateList($id,$request,$tableBuilder);
        if ($request->ajax() || $request->wantsJson()) {
            return $ReturnData;
        }
        return view('admin.investors.roi_rates')
        ->with('page_title',$page_title)
        ->with('tableBuilder',$ReturnData['tableBuilder'])
        ->with('investors',$investors)
        ->with('id',$id);
    }

    public function createReserveLiquidity($id){
        $page_title = "Reserve Liquidity";
        $action = "Create";
        $reserve_percentage=FFM::fees_array(0,100);
        $investors = User::find($id);
        return view('admin.investors.create_reserve_liquidity')
        ->with('page_title',$page_title)
        ->with('investors',$investors)
        ->with('reserve_percentage',$reserve_percentage)
        ->with('user_id',$id)
        ->with('action',$action);
    }
    public function editReserveLiquidityDetails($user_id,$id){
        $page_title = "Edit Reserve Liquidity";
        $investors = User::find($user_id);
        $action = "Edit";
        $reserved_liquidity= array();
        if(isset($id)){
            if($id!=null){
                $reserved_liquidity = $this->InvestorRepository->investorReservedLiquidityDetails($id);
                 }
        }
        
        $reserve_percentage=FFM::fees_array(0,100);//print_r($roi_rates);exit;
        return view('admin.investors.create_reserve_liquidity')
        ->with('page_title',$page_title)
        ->with('reserve_percentage',$reserve_percentage)
        ->with('user_id',$user_id)
        ->with('id',$id)
        ->with('action',$action)
        ->with('investors',$investors)
        ->with('reserved_liquidity_data',$reserved_liquidity);
        
    }
    public function saveReserveLiquidity(Request $request,$id){
        $data['user_id']=$id;
        $data['reserve_percentage'] = $request->reserve_percentage;
        $data['from_date'] = $request->date_start;
        $data['to_date'] = $request->date_end;
        DB::beginTransaction();
        InvestorHelper::saveReserveLiquidity($data);
        DB::commit();
        InvestorHelper::update_liquidity([$request->user_id],$description='');
        return redirect()->route('admin::investors::investor-reserve-liquidity', $id)->withSuccess('Successfully Updated');
       
    }
    public function investorReserveLiquidity($id,Request $request, Builder $tableBuilder)
    {
        $page_title = 'Reserve Liquidity';
        $ReturnData=$this->InvestorRepository->reserveLiquidityList($id,$request,$tableBuilder);
        if ($request->ajax() || $request->wantsJson()) {
            return $ReturnData;
        }
        $investors = User::find($id);

        return view('admin.investors.reserve_liquidities')
        ->with('page_title',$page_title)
        ->with('investors',$investors)
       ->with('tableBuilder',$ReturnData['tableBuilder'])
        ->with('id',$id)
        ;

    }

    public function checkDateForRoiRate(Request $request){
        $message = array();
        $status = 1;
        $error_message='';
        if($request->user_id){
            if(isset($request->tb_id)){
                    $roi_dates = DB::table('investor_roi_rate')->where('user_id',$request->user_id)->where('id','!=',$request->tb_id)->get();
                    $date_count1 = DB::table('investor_roi_rate')->where('user_id',$request->user_id)->where('id','!=',$request->tb_id)->where('from_date','>=',$request->date_start)->get();
                    if($request->date_end!=null){
                    $date_count2 = DB::table('investor_roi_rate')->where('user_id',$request->user_id)->where('id','!=',$request->tb_id)->where('to_date','<=',$request->date_end)->get();
                    }else{
                    $date_count2 = DB::table('investor_roi_rate')->where('user_id',$request->user_id)->where('id','!=',$request->tb_id)->where('to_date',null)->get();
        
                    }
            }else{
                    $roi_dates = DB::table('investor_roi_rate')->where('user_id',$request->user_id)->get();
                    $date_count1 = DB::table('investor_roi_rate')->where('user_id',$request->user_id)->where('from_date','>=',$request->date_start)->get();
                    if($request->date_end!=null){
                    $date_count2 = DB::table('investor_roi_rate')->where('user_id',$request->user_id)->where('to_date','<=',$request->date_end)->get();
                    }else{
                    $date_count2 = DB::table('investor_roi_rate')->where('user_id',$request->user_id)->where('to_date',null)->get();
                    }
            }
            //echo $date_count1."===".$date_count2;exit;
            if(count($roi_dates)>0){
                foreach($roi_dates as $dates){
                    if($request->date_start !=null){
                       if($request->date_start >=$dates->from_date && $request->date_start<=$dates->to_date){
                           $status = 0;
                           $error_message="Cannot select this date period because the Pref Return is already fixed for it.";
                       } 
                    }
                    if($request->date_end !=null){
                        if($request->date_end >=$dates->from_date && $request->date_end<=$dates->to_date){
                            $status = 0;
                            $error_message="Cannot select this date period because the Pref Return is already fixed for it.";
                        } 
                     }else{
                        if($request->date_start <=$dates->to_date){
                            $status = 0;
                            $error_message="Cannot select this date period because the Pref Return is already fixed for it.";
                        }

                     }
                     if($dates->to_date==null){
                        if($request->date_start >=$dates->from_date){
                            $status = 0;
                            $error_message="Cannot select this date period because the Pref Return is already fixed for it.";
                        } else{
                            if($request->date_end >=$dates->from_date){
                                $status = 0;
                                $error_message="Cannot select this date period because the Pref Return is already fixed for it.";
                            }  
                        }
                     }

                }
            }
            if(count($date_count1) > 0 && count($date_count2) >0){
                $date_range_exist1 = $date_range_exist2 = 0;
                foreach($date_count1 as $datecn1){
                    if($request->date_start < $datecn1->from_date && $request->date_end > $datecn1->to_date){
                    $date_range_exist1 = 1;
                    }
                    
                }
                foreach($date_count2 as $datecn2){
                    if($request->date_start < $datecn2->from_date && $request->date_end > $datecn2->to_date){
                        $date_range_exist2 = 1;
                    }
                    
                }
                if(($date_range_exist1 > 0) || ($date_range_exist2 > 0)){
                $status = 0;
                $error_message="Cannot select this date period because the Pref Return is already fixed for it...";
                }
            }
        }
        return response()->json(['msg' => 'success', 'status' => $status,'message'=>$error_message]);
    }
    public function deleteInvestorRoiRate($id,Request $request){
        try {
            $delete = $this->InvestorRepository->deleteRoiRate($id);

            if ($delete) {
                $request->session()->flash('message', 'Pref Return Deleted Successfully.');
            } else {
                $request->session()->flash('message', 'Pref Return Not Deleted Successfully.');
            }
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    public function deleteInvestorReserveLiquidity($id,Request $request){
        try {
            $delete = $this->InvestorRepository->deleteReserveLiquidity($id);

                if ($delete) {

                    $request->session()->flash('message', 'Reserved Percentage Deleted Successfully.');
                } else {
                    $request->session()->flash('message', 'Reserved Percentage Deleted Successfully.');

                }
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

    }
    public function checkDateForReserveLiquidity(Request $request){
        $message = array();
        $status = 1;
        $error_message='';
        if($request->user_id){
            if(isset($request->tb_id)){
                    $rl_dates = DB::table('reserve_liquidity')->where('user_id',$request->user_id)->where('id','!=',$request->tb_id)->get();
                    $date_count1 = DB::table('reserve_liquidity')->where('user_id',$request->user_id)->where('id','!=',$request->tb_id)->where('from_date','>=',$request->date_start)->get();
                    if($request->date_end!=null){
                    $date_count2 = DB::table('reserve_liquidity')->where('user_id',$request->user_id)->where('id','!=',$request->tb_id)->where('to_date','<=',$request->date_end)->get();
                    }else{
                    $date_count2 = DB::table('reserve_liquidity')->where('user_id',$request->user_id)->where('id','!=',$request->tb_id)->where('to_date',null)->get();
        
                    }
            }else{
                    $rl_dates = DB::table('reserve_liquidity')->where('user_id',$request->user_id)->get();
                    $date_count1 = DB::table('reserve_liquidity')->where('user_id',$request->user_id)->where('from_date','>=',$request->date_start)->get();
                    if($request->date_end!=null){
                    $date_count2 = DB::table('reserve_liquidity')->where('user_id',$request->user_id)->where('to_date','<=',$request->date_end)->get();
                    }else{
                    $date_count2 = DB::table('reserve_liquidity')->where('user_id',$request->user_id)->where('to_date',null)->get();
                    }
            }
            
            if(count($rl_dates)>0){
                foreach($rl_dates as $dates){
                    if($request->date_start !=null){
                       if($request->date_start >=$dates->from_date && $request->date_start<=$dates->to_date){
                           $status = 0;
                           $error_message="Cannot select this date period because the Reserve Liquidity Percentage is already fixed for it.";
                       } 
                    }
                    if($request->date_end !=null){
                        if($request->date_end >=$dates->from_date && $request->date_end<=$dates->to_date){
                            $status = 0;
                            $error_message="Cannot select this date period because the Reserve Liquidity Percentage is already fixed for it.";
                        } 
                     }else{
                        if($request->date_start <=$dates->to_date){
                            $status = 0;
                            $error_message="Cannot select this date period because the Reserve Liquidity Percentage is already fixed for it.";
                        }

                     }
                     if($dates->to_date==null){
                        if($request->date_start >=$dates->from_date){
                            $status = 0;
                            $error_message="Cannot select this date period because the Reserve Liquidity Percentage is already fixed for it.";
                        } else{
                            if($request->date_end >=$dates->from_date){
                                $status = 0;
                                $error_message="Cannot select this date period because the Reserve Liquidity Percentage is already fixed for it.";
                            }  
                        }
                     }

                }
            }
            
            if(count($date_count1) > 0 && count($date_count2) >0){
                $date_range_exist1 = $date_range_exist2 = 0;
                foreach($date_count1 as $datecn1){
                    if($request->date_start < $datecn1->from_date && $request->date_end > $datecn1->to_date){
                    $date_range_exist1 = 1;
                    }
                    
                }
                foreach($date_count2 as $datecn2){
                    if($request->date_start < $datecn2->from_date && $request->date_end > $datecn2->to_date){
                        $date_range_exist2 = 1;
                    }
                    
                }
                if(($date_range_exist1 > 0) || ($date_range_exist2 > 0)){
                $status = 0;
                $error_message="Cannot select this date period because the Reserve Liquidity Percentage is already fixed for it.";
                }
            }
            
        }
        return response()->json(['msg' => 'success', 'status' => $status,'message'=>$error_message]);
    }
     
}
