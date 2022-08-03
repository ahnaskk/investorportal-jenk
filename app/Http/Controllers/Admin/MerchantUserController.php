<?php

namespace App\Http\Controllers\Admin;

use App\AchRequest;
use App\Bank;
use App\CompanyAmount;
use App\Exports\Data_arrExport;
use App\FundingRequests;
use MerchantHelper;
use InvestorAssignHelper;
use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\Http\Controllers\Admin\Traits\DocumentUploader;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\Admin\StoryMerchantRequest;
use App\Http\Requests\AdminUpdateMerchantRequest;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Label;
use App\Library\Helpers\MerchantExcelBuilder;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\IMarketPlaceRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IMNotesRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\IInvestorTransactionRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Library\Repository\Interfaces\IUserActivityLogRepository;
use App\Library\Repository\InvestorTransactionRepository;
use App\Library\Repository\UserActivityLogRepository;
use App\LiquidityLog;
use App\MarketpalceInvestors;
use App\MbatchMarchant;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantPaymentTerm;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\MNotes;
use App\SubStatusFlag;
use App\Models\Transaction;
use App\Models\Views\MerchantUserView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\PaymentPause;
use App\ReassignHistory;
use App\Settings;
use App\SubStatus;
use App\Template;
use App\TermPaymentDate;
use App\User;
use App\UserActivityLog;
use App\UserDetails;
use App\VelocityFee;
use Carbon\Carbon;
use DateTime;
use Exception;
use FFM;
use Form;
use GPH;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use MTB;
use PayCalc;
use PDF;
use Permissions;
use Spatie\Permission\Models\Role;
use Stripe\Charge;
use Stripe\Stripe;
use Yajra\DataTables\Html\Builder;
use MerchantUserHelper;

class MerchantUserController extends Controller
{
	use DocumentUploader;
    use CreditCardStripe;

    public function __construct(ISubStatusRepository $subStatus, IRoleRepository $role, IMerchantRepository $merchant, MarketpalceInvestors $merchant_user, IMarketPlaceRepository $marketplace, IInvestorTransactionRepository $transaction, IParticipantPaymentRepository $payment, IMNotesRepository $mNotes, ILabelRepository $label, IUserRepository $user)
    {
        $this->subStatus = $subStatus;
        $this->transaction = $transaction;
        $this->role = $role;
        $this->merchant = $merchant;
        $this->payment = $payment;
        $this->marketplace = $marketplace;
        $this->merchant_user = $merchant_user;
        $this->user = new User();
        $this->mNotes = $mNotes;
        $this->label = $label;
        $this->user1 = $user;
       
    }
    public function create_investor(Request $request, $merchant_id = '')
    {
       $result= MerchantUserHelper::createSingleAssignInvestor($request,$merchant_id);
       return view('admin.merchants.investor_create',$result);
    }

    public function edit_investor($id,Request $request)
    {
        try {
            $merchantUser=MerchantUser::find($id);
            if(!$merchantUser){
                $request->session()->flash('error','Invalid Id');
                 return redirect(route('admin::merchants::index'));
            }
            $result= MerchantUserHelper::editSingleAssignInvestor($request,$id);
            return view('admin.merchants.investor_edit',$result);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

      }  

    public function store_investor(Request $request)
     {
        $rule = ['merchant_id' => 'required|exists:merchants,id', 'user_id' => 'required|exists:users,id', 'amount' => 'required|regex:/^\d*(\.\d{1,2})?$/', 'amount_field' => 'required|regex:/^\d*(\.\d{1,2})?$/'];
        if ($request->syndication_fee != 0) {
            $rule['s_prepaid_status'] = 'required';
        }
        $validator = Validator::make($request->all(), $rule);
            if ($validator->fails()) {
                return redirect('/admin/merchant_investor/create/'.$request->mer_id)->withErrors($validator)
                ->withInput();
            }
       return MerchantUserHelper::storeAssignSingleInvestor($request);
           
    }

    public function update_investor(Request $request)
	{
		try {
			DB::beginTransaction();
			$validator = Validator::make($request->all(), ['amount' => 'required|regex:/^-?[0-9]\d*(\.\d+)?$/']);
			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			$return_result=MerchantUserHelper::updateAssignSingleInvestor($request);
			if($return_result['result']!='success') throw new \Exception($return_result['result'], 1);
			$request->session()->flash('message', 'Investor details updated!');
			DB::commit();
			return redirect()->route('admin::merchants::view',['id' => $request->merchant_id]);
		} catch (\Exception $e) {
			DB::rollback();
			return redirect()->back()->withInput()->withErrors($e->getMessage());
		}
	}

   public function delete_investor($id)
    {
        try {
          
            MerchantUserHelper::deleteInvestor($id);
            return redirect()->back();
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

   public function delete_multi_investment(Request $request)
    {
         return MerchantUserHelper::deleteMultiInvestment($request);

    }

    public function assign_based_on_payment(Request $request, IRoleRepository $role)
    {
        InvestorAssignHelper::assignBasedOnPayment($request);

        return redirect()->back();
    }

   public function assign_investor_based_on_liquidity(Request $request, IRoleRepository $role)
    {
        try {

       $result= InvestorAssignHelper::assignBasedOnLiquidty($request);

        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', $e->getMessage());
        }
        
        return redirect()->back();
    }

    public function assignInvestor(Request $request,Builder $tableBuilder,$merchant_id){ 
        $merchant = Merchant::where('id', $merchant_id)->first();
        if(!$merchant){
            $request->session()->flash('error', "Invalid Merchant.");
            return redirect(route("admin::merchants::index"));
        }

        if ($request->ajax() || $request->wantsJson()) {
            $data_arr = session('assign_investors_arr');
            if(empty($data_arr)){
                $data_arr = array();

            }else{
                $id_arr = array_unique(array_column($data_arr, 'id'));
                $in_name_arr = User::whereIn('id',$id_arr)->pluck('name','id')->toArray();//print_r($data_arr);exit;
                for($i=0;$i<count($data_arr);$i++){
                    $data_arr[$i]['name']=$in_name_arr[$data_arr[$i]['id']];
                }
            }                           
            return \MTB::getInvestorFeeDetails($data_arr,$merchant_id);
        } 
      $result=InvestorAssignHelper::assignInvestors($request,$tableBuilder,$merchant_id);
      return view('admin.merchants.assign_investors',$result);
    }

     public function listInvestorForAssign(Request $request){

        return InvestorAssignHelper::listInvestorForAssign($request);
         
    }
    public function filteredInvestor(Request $request)
    {
        $company = $request->company;
        $creator_id = $request->creator_id;
        $investors_data = InvestorAssignHelper::investors_data($creator_id,$company);
        return $investors_data;
    }

    public function assignInvestorToMerchant(Request $request){

       return InvestorAssignHelper::assignInvestorToMerchant($request);

    }
   
    public function investorMerchantStatus(Request $request)
    {
        return MerchantUserHelper::investorMerchantStatus($request);
       
    }


}
