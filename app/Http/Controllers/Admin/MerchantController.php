<?php

namespace App\Http\Controllers\Admin;

use App\AchRequest;
use App\CompanyAmount;
use App\Exports\Data_arrExport;
use App\FundingRequests;
use App\Http\Controllers\Admin\Traits\CreditCardStripe;
use App\Http\Controllers\Admin\Traits\DocumentUploader;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\Admin\StoryMerchantRequest;
use App\Http\Requests\AdminUpdateMerchantRequest;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\IMarketPlaceRepository;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IMNotesRepository;
use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Library\Repository\InvestorTransactionRepository;
use App\MarketpalceInvestors;
use App\MbatchMarchant;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantPaymentTerm;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\MNotes;
use App\Models\Transaction;
use App\Models\Views\MerchantUserView;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Settings;
use App\SubStatus;
use App\TermPaymentDate;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use Exception;
use FFM;
use Form;
use GPH;
use InvestorHelper;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PayCalc;
use PDF;
use Permissions;
use Spatie\Permission\Models\Role;
use Stripe\Charge;
use Stripe\Stripe;
use Yajra\DataTables\Html\Builder;
use MerchantHelper;
use PaymentTermHelper;
use InvestorAssignHelper;
use ParticipantPaymentHelper;

class MerchantController extends Controller
{
    use DocumentUploader;
    use CreditCardStripe;

    public function __construct(ISubStatusRepository $subStatus, IRoleRepository $role, IMerchantRepository $merchant, MarketpalceInvestors $merchant_user, IMarketPlaceRepository $marketplace, IParticipantPaymentRepository $payment, IMNotesRepository $mNotes, ILabelRepository $label, IUserRepository $user)
    {
        $this->subStatus = $subStatus;
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
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::merchantList($request->lender_id, $request->status_id, $request->market_place, $request->not_started, $request->paid_off, $request->stop_payment, $request->over_payment, $request->user_id, $request->late_payment, $request->request_m, $request->date_start, $request->date_end, $request->advance_type, $request->substatus_flag_id, $request->label, $request->not_invested, $request->bank_account, $request->payment_pause, $request->owner, $request->mode_of_payment);
        }

        $result=MerchantHelper::allMerchants($request);
        return view('admin.merchants.index',$result);
    }

   public function view(Request $request, $id)
    {
        $Merchant=Merchant::with('MerchantDetails')->find($id);
        if(!$Merchant) {
            $request->session()->flash('error', 'Invalid Merchant Id');
            return redirect(route("admin::merchants::index"));
        }
        $extra_arr=[
            'company_id'=>isset($_GET['company_id']) ? $_GET['company_id'] : 0,
            'investor_id'=> isset($_GET['investor_id']) ? $_GET['investor_id'] : 0,
            'id'=>$id ];
    
        $result=MerchantHelper::merchantView($request,$extra_arr);
        return view('admin.merchants.view',$result); 
    }

   public function create(Request $request)
    {
         $result=MerchantHelper::createMerchant($request);
         return view('admin.merchants.create', $result);
    }

   public function edit(Request $request, $id)
    {   
        try {
            $Merchant=Merchant::find($id);
            if(!$Merchant){
                $request->session()->flash('error','Invalid Merchant Id');
                return redirect(route('admin::merchants::index'));
            }
          $result=MerchantHelper::editMerchant($request,$id);
          return view('admin.merchants.create',$result);
        } catch (\PDOException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function requests(Builder $tableBuilder,Request $request,$mid)
    {
        $Merchant=Merchant::find($mid);
        if(!$Merchant){
            $request->session()->flash('error','Invalid Merchant Id');
            return redirect(route('admin::merchants::index'));
        }
        $page_title = 'List Investor Requests';
        $tableBuilder->ajax(route('admin::merchants::requests_data', $mid));
        $tableBuilder = $tableBuilder->columns([['data' => 'DT_RowIndex', 'title' => '#', 'orderable' => false], ['data' => 'user_id', 'name' => 'user_id', 'title' => 'Investor'], ['data' => 'factor_rate', 'name' => 'factor_rate', 'title' => 'Factor Rate'], ['data' => 'pmnts', 'name' => 'pmnts', 'title' => 'Payments'], ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'], ['data' => 'commission', 'name' => 'commission', 'title' => 'Commission'], ['data' => 'mgmnt_fee', 'name' => 'mgmnt_fee', 'title' => 'Management Fee'], ['data' => 'status', 'name' => 'status', 'title' => 'Status'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]])->parameters(['bSort' => true, 'dom' => 'lrtp', 'buttons' => ['export', 'print', 'reset', 'reload'], 'order' => [5, 'desc'], 'initComplete' => "function () {\n                this.api().columns().every(function () {\n                    var column = this;\n                    var input = document.createElement(\"input\");\n                    $(input).appendTo($(column.footer()).empty())\n                    .on('change', function () {\n                        column.search($(this).val(), false, false, true).draw();\n                        });\n                        });\n                    }"]);

        return view('admin.merchants.requests', compact('page_title', 'tableBuilder'));
    }

    public function merchantUser($id)
    {
        try {
            if ($merchant = $this->user1->findUserRole($id, 'viewer')) {
                $page_title = 'Edit Merchant User';
                $action = 'edit';
                $role_count = User::where('users.id', $id)->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('role_id', User::MERCHANT_ROLE)->count();
                $role_id = User::MERCHANT_ROLE;
                $roles = Role::all();

                return view('admin.merchants.merchant_user', compact('page_title', 'merchant', 'action', 'roles', 'role_count', 'role_id'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function selectMerchant(Request $request)
    {
        $status = $request->get('status');
        $merchants = Merchant::query();
        if ($status != 0) {
            $merchants = $merchants->where('sub_status_id', $status);
        }
        $merchants = $merchants->pluck('name', 'id')->toArray();
        if ($merchants) {
            return response()->json(['msg' => 'success', 'status' => 1, 'merchants' => $merchants]);
        } else {
            return response()->json(['msg' => 'success', 'status' => 0]);
        }
    }
    
    public function requestsApprove(Request $request, $id)
    {
        try {
            $merchant = FundingRequests::where('id', $id)->first();
            $mid = $merchant->mid;
            $user_id = $merchant->user_id;
            FundingRequests::where('user_id', $user_id)->where('mid', $mid)->update(['status' => 0]);
            FundingRequests::where('id', $id)->update(['status' => 1]);
            $total_amount = $this->merchant_user->where('merchant_id', $mid)->where('user_id', '!=', $user_id)->sum('amount');
            $full_merchant = Merchant::where('id', $mid)->select('max_participant_fund', 'funded', 'date_funded')->first();
            $max_participant_fund = $full_merchant->max_participant_fund;
            $full_amount = $full_merchant->funded;
            $investor_date = $full_merchant->date_funded;
            if ($max_participant_fund >= ($total_amount + $merchant->amount)) {
                $full_rtr = ($full_amount * $merchant->factor_rate);
                $update_merchant = Merchant::where('id', $mid)->update(['pmnts' => $merchant->pmnts, 'mgmnt_fee' => $merchant->mgmnt_fee, 'factor_rate' => $merchant->factor_rate, 'commission' => $merchant->commission, 'rtr' => $full_rtr]);
                $invest_rtr = ($merchant->amount * $merchant->factor_rate);
                $syndication_fee_val = PayCalc::getsyndicationFee($merchant->syndication_fee, $invest_rtr);
                $mgmnt_fee = PayCalc::getMgmntFee($merchant->mgmnt_fee, $invest_rtr);
                $share = PayCalc::participantShare($full_rtr, $invest_rtr);
                $merchant_user = $this->merchant_user->where(['user_id' => $user_id, 'merchant_id' => $mid])->delete();
                $merchant_user = $this->merchant_user->create(['user_id' => $user_id, 'merchant_id' => $mid, 'amount' => $merchant->amount, 'mgmnt_fee_percentage' => $merchant->mgmnt_fee, 'syndication_fee_percentage' => $merchant->syndication_fee, 'transaction_type' => 1, 'status' => 1, 'invest_rtr' => $invest_rtr, 'syndication_fee' => $syndication_fee_val, 'mgmnt_fee' => $mgmnt_fee, 'share' => $share, 'payment_date' => $investor_date]);
                if (1) {
                    $request->session()->flash('message', 'Investor assigned to merchant, Id:'.$merchant_user->id."<br> <a href='/admin/merchant_investor/edit/".$merchant_user->id."'>Click here to edit</a>");
                }
            } else {
                if (1) {
                    $request->session()->flash('error', 'Maximum Available Amount Is  '.$value);
                }
            }

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function AdjustInvestorFundedAmount(Request $request, $merchant_id)
    {
        try {
            DB::beginTransaction();
            $return_result = MerchantUser::InvestmentAmountAdjuster($merchant_id);
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $user_id = MerchantUser::where('merchant_id', $merchant_id)->pluck('user_id', 'user_id')->toArray();
            InvestorHelper::update_liquidity($user_id, 'Funded Amount Adjustment', $merchant_id);
            $request->session()->flash('message', 'Investor Fund Amount Changed Successfully');
            DB::commit();

            return redirect()->back();
        } catch (\Exception $e) {
            $request->session()->flash('message', $e->getMessage());
            DB::rollback();
        }

        return redirect()->back();
    }

    public function AdjustCompanyFundedAmount(Request $request, $merchant_id)
    {
        try {
            DB::beginTransaction();
            $return_result = CompanyAmount::FinalizeCompanyShare($merchant_id);
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $request->session()->flash('message', 'Company Fund Amount Changed Successfully');
            DB::commit();

            return redirect()->back();
        } catch (\Exception $e) {
            $request->session()->flash('error', $e->getMessage());
            DB::rollback();
        }

        return redirect()->back();
    }
    public function addMerchantInvestmentTransaction(Request $request, $merchant_id)
    {
        try {
            DB::beginTransaction();
            $Merchant = Merchant::find($merchant_id);
            $ParticipentPayment = ParticipentPayment::firstOrCreate(['merchant_id' => $merchant_id, 'model' => \App\MerchantUser::class], ['status' => ParticipentPayment::StatusCompleted, 'payment' => -$Merchant->funded, 'creator_id' => Auth::user()->id ?? '', 'transaction_type' => 2, 'mode_of_payment' => ParticipentPayment::PaymentModeSystemGenerated, 'payment_date' => $Merchant->date_funded]);
            $ParticipentPayment->update(['model_id' => $ParticipentPayment->id]);
            DB::commit();

            return redirect()->back();
        } catch (\Exception $e) {
            $request->session()->flash('message', $e->getMessage());
            DB::rollback();
        }

        return redirect()->back();
    }

    public function addMerchantPaymentTransaction(Request $request, $merchant_id)
    {
        try {
            DB::beginTransaction();
            $merchant = DB::table('merchants')->find($merchant_id);
            $ParticipentPayment = ParticipentPayment::where('merchant_id', $merchant_id)->get();
            foreach ($ParticipentPayment as $key => $value) {
                $TransactionModel = new Transaction;
                $data = ['amount' => $value['payment'], 'merchant_id' => $value['merchant_id'], 'date' => $value['payment_date'], 'model' => \App\ParticipentPayment::class, 'model_id' => $value->id, 'status' => Transaction::Completed];
                $TransactionModel->selfCreate($data);
            }
            DB::commit();

            return redirect()->back();
        } catch (\Exception $e) {
            $request->session()->flash('message', $e->getMessage());
            DB::rollback();
        }

        return redirect()->back();
    }



    public function payoff_letter_for_merchants($merchant_id)
    {
        $merchant_payoff  = $this->merchant->merchant_payoff($merchant_id);
        $Currentdate      = $merchant_payoff['Currentdate'];
        $business_name    = $merchant_payoff['business_name'];
        $full_name        = $merchant_payoff['full_name'];
        $business_address = $merchant_payoff['business_address'];
        $business_city    = $merchant_payoff['business_city'];
        $business_state   = $merchant_payoff['business_state'];
        $business_zip     = $merchant_payoff['business_zip'];
        $loan_balance     = $merchant_payoff['loan_balance'];
        $first_name       = $merchant_payoff['first_name'];
        $pdf_name         = 'payoff_letter'.$full_name.'/'.time();
        $filePDFName      = $pdf_name.'.pdf';
        $pdf = PDF::loadView('payoff_letter_pdf', compact('Currentdate', 'full_name', 'business_name', 'business_address', 'business_city', 'business_state', 'business_zip', 'loan_balance', 'first_name'));
        return $pdf->stream($merchant_id.'-payoff_letter.pdf');
        $load = Storage::disk('s3')->put($filePDFName, $pdf->output(), config('filesystems.disks.s3.privacy'));
        $filePDFUrl = asset(\Storage::disk('s3')->temporaryUrl($filePDFName,Carbon::now()->addMinutes(1)));
        return redirect($filePDFUrl);
        if ($load) {
            return Storage::disk('s3')->download($filePDFName);
        }
    }

    public function companyInvestors(Request $request)
    {
        if ($request->merchant_id) {
            $merchant_array = $this->merchant->merchant_details($request->merchant_id, $request->company_id);
            return response()->json(['status' => 1]);
        }
    }

    public function changeSubStatus(Request $request)
    {
        $req_merchant_id = $request->merchant_id;
        $req_sub_status_id = $request->sub_status_id;
        return MerchantHelper::changeSubStatusFn($req_merchant_id, $req_sub_status_id);
    }

    public function storeCreate(Requests\AdminCreateMerchantRequest $request)
    {
        try {
        $result=MerchantHelper::storeMerchant($request);
        if($result['result']!='success') throw new Exception($result['msg'], 1);
        if ($request->user()->hasRole(['company'])) {
            return redirect()->route('admin::merchants::index');
        } else {
             $request->session()->flash('message', 'New Merchant Created.'.$result['msg']);
            return redirect()->route('admin::merchants::view', ['id' => $result['merchant_id']]);
        }

        } catch (\Exception $e) {
           
          return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }
    public function view_merchant_user_roles()
    {
        $result=MerchantHelper::viewMerchantUserRoles();
        return view('admin.merchants.merchant_users',$result);
    }

    public function getallMerchantUserRolesData(Request $request)
    {
        return \DataTables::of($this->role->allMerchantUserRoleData())->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Users', 'Edit')) {
                $return .= '<a href="'.route('admin::merchants::merchantUser', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }

            return $return;
        })->editColumn('created_at', function ($data) {
            return \FFM::datetime($data->created_at);
        })->editColumn('updated_at', function ($data) {
            return \FFM::datetime($data->updated_at);
        })->addIndexColumn()->make(true);
    }

    private function sendResetEmail($email, $token)
    {
        $user = DB::table('users')->where('email', $email)->select('email')->first();
        $link = config('app.url').'password/reset/'.$token.'?email='.urlencode($user->email);
        try {
            return $link;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function update(AdminUpdateMerchantRequest $request)
    {
        try {
          $result=MerchantHelper::updateMerchant($request);
          if($result['result']!='success') throw new Exception($result['msg'], 1);
           if (isset($result['merchant_id'])) {
            $request->session()->flash('message', 'Merchant Updated.'.$result['msg']);
        }
          return redirect()->route('admin::merchants::view', ['id' => $result['merchant_id']]);
         
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }

 public function delete($id, Request $request)
    {
        try {
            $check = $this->merchant_user->where('merchant_id', $id);
            if (($check->count() <= 0)) {
                $ach_count = AchRequest::where('merchant_id', $id)->whereIn('ach_status', [AchRequest::AchStatusProcessing, AchRequest::AchStatusSettled])->count();
                if ($ach_count == 0) {
                    if (!$this->merchant->delete($id)) throw new Exception("Delete not possible", 1);
                        $this->user->where('merchant_id_m', $id)->delete();
                        CompanyAmount::where('merchant_id', $id)->delete();
                        ParticipentPayment::where('merchant_id', '=', $id)->delete();
                        $ach_delete = AchRequest::where('merchant_id', $id)->delete();
                    $this->merchant_user->where('merchant_id', '=', $id)->delete();
                    $request->session()->flash('message', 'Merchant deleted');
    
                    return redirect()->route('admin::merchants::index');
                } else {
                    return redirect()->to('admin/merchants/')->withErrors('Cannot Delete Merchant, ACH exists!');
                }
            } else {
                return redirect()->to('admin/merchants/')->withErrors('Cannot Delete Merchant,already referred !');
            }
        } catch (\PDOException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function requestsDelete(Request $request, $id)
    {
        try {
            if (FundingRequests::destroy($id)) {
                $request->session()->flash('message', 'Investor Request Deleted');
            }

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function rowDataRequests($mid)
    {
        $data = $this->marketplace->requests(['id', 'user_id', 'factor_rate', 'pmnts', 'amount', 'commission', 'syndication_fee', 'mgmnt_fee', 'status'], $mid);

        return \DataTables::of($data)->addColumn('action', function ($data) {
            return '<a href="'.route('admin::merchants::requests::delete', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Delete</a>'.Form::open(['route' => ['admin::merchants::requests::approve', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Do you sure want to approve ?")']).Form::submit('approve', ['class' => 'btn btn-xs btn-danger']).Form::close();
        })->editColumn('status', function ($data) {
            return $data->status == 1 ? 'Active' : ($data->status == 0 ? 'Rejected' : ($data->status == 2 ? 'New' : ''));
        })->filterColumn('date_funded', function ($query, $keyword) {
            $keyword = FFM::dbdate($keyword);
            $sql = 'date_funded  like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })->make(true);
    }

    public function view_investor($id)
    {
        try {
            if ($merchant = $this->merchant_user->find($id)) {
                $Self = MerchantUserView::find($id);
                $Payments = PaymentInvestors::where('merchant_id', $Self->merchant_id)->where('user_id', $Self->investor_id)->orderByDesc('participent_payment_id')->get();

                return view('admin.merchants.investor_view', compact('Self', 'Payments'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function rowDataMerchant($id = 0, $company_id = 0, $investor_id = 0)
    {
        $extra=['id'=>$id,'company_id'=>$company_id,'investor_id'=>$investor_id];         

        return MerchantHelper::merchantPayments($extra);
 
    }

    public function updateInvetment(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $MerchantUser = MerchantUser::find($data['merchant_user_id']);
            if (! $MerchantUser) {
                throw new \Exception('Empty Investment Table', 1);
            }
            $MerchantUser->amount = $data['amount'];
            $MerchantUser->save();
            $description = 'Custom Amount Changed';
            $merchant_id = $MerchantUser->merchant_id;
            $investor_ids = $MerchantUser->user_id;
            InvestorHelper::update_liquidity($investor_ids, $description, $merchant_id);
            DB::commit();
            $return['message'] = 'Successfully Updated the Funded Amount';
            $return['status'] = 1;
        } catch (\Exception $e) {
            DB::rollback();
            $return['status'] = 0;
            $return['message'] = $e->getMessage();
        }

        return response()->json($return);
    }

    public function re_assign(Request $request)
    {
        try {
             InvestorAssignHelper::reAssign($request);
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function undo_re_assign(Request $request)
    {
        try {
            InvestorAssignHelper::undoReAssign($request);
            return redirect()->back();
        
        } catch (\Exception $e) {
            $result['result'] = $e->getMessage();
            DB::rollback();
        }
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        } else {
            return redirect()->back();
        }
    }

    public function getInvestorLiquidityByCreator(Request $request, IRoleRepository $role)
    {
        if ($request->all()) {
            $investor_admin = $role->allSubAdmin()->pluck('id')->toArray();
            $max_participant_amount = $request->max_participant_amount;
            $subadmin_liquidity = UserDetails::whereHas('userDetails', function ($query) use ($investor_admin) {
                $query->whereIn('creator_id', $investor_admin);
            })->sum('liquidity');
            $subadmin_attach_liquidity = UserDetails::whereHas('userDetails', function ($query) use ($investor_admin) {
                $query->whereIn('creator_id', $investor_admin);
            })->sum('liquidity');
            $admin_liquidity = UserDetails::whereHas('userDetails', function ($query) use ($investor_admin) {
                $query->whereNotIn('creator_id', $investor_admin);
            })->sum('liquidity');
            $admin_liquidity = $admin_liquidity + $subadmin_attach_liquidity;
            $velocity1_max = ($subadmin_liquidity / $admin_liquidity) * $max_participant_amount;
            $velocity2_max = $max_participant_amount - $velocity1_max;
            $velocity1_per = ($max_participant_amount != 0) ? ($velocity1_max / $max_participant_amount) * 100 : 0;
            $velocity2_per = ($max_participant_amount != 0) ? ($velocity2_max / $max_participant_amount) * 100 : 0;

            return response()->json(['vp_max' => $subadmin_liquidity, 'velocity_max' => $admin_liquidity, 'velocity1_max' => round($velocity1_max, 2), 'velocity2_max' => round($velocity2_max, 2), 'velocity1_per' => round($velocity1_per, 2), 'velocity2_per' => round($velocity2_per, 2)]);
        }
    }

    public function calculateVelocitiesByPercentage(Request $request, IRoleRepository $role)
    {
        if ($request->all()) {
            $investor_admin = $role->allSubAdmin()->pluck('id')->toArray();
            $max_part_fund = $request->max_participant_amount;
            $number = $request->number1;
            $subadmin_liquidity = UserDetails::whereHas('userDetails', function ($query) use ($investor_admin) {
                $query->whereIn('creator_id', $investor_admin);
            })->sum('liquidity');
            $admin_liquidity = UserDetails::whereHas('userDetails', function ($query) use ($investor_admin) {
                $query->whereNotIn('creator_id', $investor_admin);
            })->sum('liquidity');
            if (isset($request->velocity1_per)) {
                $velocity1_per = $request->velocity1_per;
                $velocity2_per = $number - $request->velocity1_per;
                $velocity1_max = $max_part_fund * ($velocity1_per / 100);
                $velocity2_max = $max_part_fund * ($velocity2_per / 100);
            } elseif (isset($request->velocity2_per)) {
                $velocity2_per = $request->velocity2_per;
                $velocity1_per = $number - $request->velocity2_per;
                $velocity1_max = $max_part_fund * ($velocity1_per / 100);
                $velocity2_max = $max_part_fund * ($velocity2_per / 100);
            }

            return response()->json(['velocity1_per' => $velocity1_per, 'velocity2_per' => $velocity2_per, 'velocity1_max' => round($velocity1_max, 2), 'velocity2_max' => round($velocity2_max, 2), 'vp_max' => $subadmin_liquidity, 'velocity_max' => $admin_liquidity]);
        }
    }

    public function checkVelocityWithMaxFund(Request $request, IRoleRepository $role)
    {
        if ($request->all()) {
            $investor_admin = $role->allSubAdmin()->pluck('id')->toArray();
            $subadmin_liquidity = UserDetails::whereHas('userDetails', function ($query) use ($investor_admin) {
                $query->whereIn('creator_id', $investor_admin);
            })->sum('liquidity');
            $admin_liquidity = UserDetails::whereHas('userDetails', function ($query) use ($investor_admin) {
                $query->whereNotIn('creator_id', $investor_admin);
            })->sum('liquidity');
            $velocity1_max = $request->velocity1_max;
            $velocity2_max = $request->velocity2_max;
            $total = $velocity1_max + $velocity2_max;
            $max_participant_amount = $request->max_participant_fund;
            if ($total == $request->max_participant_fund) {
                $status = 1;
            } else {
                $status = 0;
            }
            $velocity1_per = ($max_participant_amount != 0) ? ($velocity1_max / $max_participant_amount) * 100 : 0;
            $velocity2_per = ($max_participant_amount != 0) ? ($velocity2_max / $max_participant_amount) * 100 : 0;
            $percentage = $velocity1_per + $velocity2_per;
            if ($percentage != 100) {
                $status = 0;
            } else {
                $status = 1;
            }

            return response()->json(['return_status' => $status, 'velocity1_per' => round($velocity1_per, 2), 'velocity2_per' => round($velocity2_per, 2), 'vp_max' => $subadmin_liquidity, 'velocity_max' => $admin_liquidity, 'velocity2_max' => round($velocity2_max, 2), 'velocity1_per_max' => round($velocity1_per_max, 2)]);
        }
    }

    public function getPercenatgeFromVelocity(Request $request)
    {
        if ($request->all()) {
            $max_part_fund = $request->max_participant_amount;
            $velocity1_max = $request->velocity1_max;
            $velocity2_max = $request->velocity2_max;
            $velocity1_per = ($velocity1_max / $max_part_fund) * 100;
            $velocity2_per = ($velocity2_max / $max_part_fund) * 100;

            return response()->json(['velocity1_per' => $velocity1_per, 'velocity2_per' => $velocity2_per]);
        }
    }

    public function merchantListDownload(Request $request)
    {
        $fileName = 'Merchant List_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $user = $request->user();
        $total_amount = 0;
        $details_arr = \MTB::getMerchantList($request->lender_id, $request->status_id, $request->market_place, $request->not_started, $request->not_invested, $request->paid_off, $request->stop_payment, $request->over_payment, $request->user_id, $request->late_payment, $request->request_m, $request->date_start, $request->date_end, $request->advance_type, $request->label, $request->bank_account, $request->payment_pause, $request->mode_of_payment, $request->sub_status_flag_id);
        $details = $details_arr['data']->get()->toArray();
        $disabled_details = $details_arr['disabled_data']->get()->toArray();
        $excel_array=MerchantHelper::allMerchantDownload($details,$disabled_details);
        $export = new Data_arrExport($excel_array);

        return Excel::download($export,$fileName);
    }

    public function paymentCheckForMerchant(Request $request)
    {
        $merchant_id = $request->merchant_id;
        $payment = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id)->count();
        if ($payment > 0) {
            return response()->json(['status' => 1]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function ttmail()
    {
        $new_mails[] = 'amitsudhans@gmail.com';
        $message['to_mail'] = $new_mails;
        $message['status'] = 'funding_approve';
        $message['unqID'] = unqID();
        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
        dispatch($emailJob);
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        $message['to_mail'] = $admin_email;
        $emailJob = (new CommonJobs($message));
        dispatch($emailJob);
    }

    public function changeSubStatusFlag(Request $request)
    {
        $validator = Validator::make($request->all(), ['merchant_id' => 'required|exists:merchants,id', 'sub_status_flag' => 'required|integer']);
        if ($validator->fails()) {
            $error = '';
            foreach (array_values($validator->messages()->toArray()) as $msg) {
                $error = $error.implode(' ', $msg);
            }
            return response()->json(['status' => 0, 'msg' => $error]);
        }
    
        $substatus_flags = DB::table('sub_status_flags')->pluck('name','id')->toArray();
        if (array_key_exists($request->sub_status_flag, $substatus_flags)) {
            $merchant = Merchant::where('id', $request->merchant_id)->first();
            if ($merchant) {
                  $merchant->sub_status_flag = $request->sub_status_flag;
                  $status = $merchant->update();
                 return response()->json(['status' => 1, 'msg' => 'Merchant sub status flag updated Successfully.']);
            }
        }

        return response()->json(['status' => 0, 'msg' => 'Invalid request.']);
    }

    public function getSelect2Merchants(Request $request)
    {   ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $company =  $request->user()->id;
        $company_merchants = array();
        if(empty($permission)){
        $company_merchants =merchantUser::leftjoin('users','users.id','merchant_user.user_id')->where('company',$company)->select('merchant_id')->groupBy('merchant_id')->get();
        if($company_merchants){
            $company_merchants = $company_merchants->toArray();
        }
        }
        $limit = 20;
        $offset = $request->page ?? 0;
        $search = $request->search ?? null;
        if ($offset > 1) {
            $offset = $offset * $limit;
        } else {
            $offset = 0;
        }
        $merchants = Merchant::where('active_status', 1);
        if ($search) {
            $merchants = $merchants->where(function ($query) use ($search) {
                $query->orWhere('name', 'like', '%'.$search.'%');
            });
        }
        if(empty($permission)){
            $merchants = $merchants->whereIn('id',$company_merchants);
        }
        $merchants = $merchants->select(DB::raw('upper(name) as text'), 'id')->orderBy('text')->get();
        $count = $merchants->count();
        $merchants_array = $merchants->toArray();
        $merchants_array = array_slice($merchants_array, $offset, $limit);
        $not_ended = true;
        $offset += $limit;
        if ($offset >= $count) {
            $not_ended = false;
        }
        $pagination = (object) ['more' => $not_ended];

        return response()->json(['results' => $merchants_array, 'pagination' => $pagination]);
    }

    public function getSelect2MerchantsWithDeleted(Request $request)
    {
        $limit = 20;
        $offset = $request->page ?? 0;
        $search = $request->search ?? null;
        if ($offset > 1) {
            $offset = $offset * $limit;
        } else {
            $offset = 0;
        }
        $merchants = Merchant::withTrashed()->where('active_status', 1);
        if ($search) {
            $merchants = $merchants->where(function ($query) use ($search) {
                $query->orWhere('name', 'like', '%'.$search.'%');
            });
        }
        $merchants = $merchants->select('name as text', 'id')->orderBy('text')->get();
        $count = $merchants->count();
        $merchants_array = $merchants->toArray();
        $merchants_array = array_slice($merchants_array, $offset, $limit);
        $not_ended = true;
        $offset += $limit;
        if ($offset >= $count) {
            $not_ended = false;
        }
        $pagination = (object) ['more' => $not_ended];

        return response()->json(['results' => $merchants_array, 'pagination' => $pagination]);
    }

    public function paymentTerms($mid, Request $request)
    {
        $result=PaymentTermHelper::paymentTerm($request,$mid);

        return view('admin.merchants.payment_term',$result);
    }

    public function createTerm($mid, Request $request)
    {
        $result=PaymentTermHelper::createPaymentTerm($request,$mid);

        return view('admin.merchants.payment_term_create',$result);
    }

    public function editTerm($mid, $id, Request $request)
    {
       $result=PaymentTermHelper::editPaymentTerm($request,$mid,$id);
        return view('admin.merchants.payment_term_edit', $result);
    }

    public function deleteTerm($mid, $id, Request $request)
    {
        $extra=[

         'mid'=>$mid,
         'id'=>$id,
         'request'=>$request

        ];

        PaymentTermHelper::deletePaymentTerm($extra);
        return redirect()->back();
    }

    public function storePaymentTerms($mid, Request $request)
    {
        PaymentTermHelper::storeMerchantPaymentTerm($request,$mid);
         return redirect()->back();

    }

    public function updatePaymentTerms($mid, Request $request)
    {
       PaymentTermHelper::updateMerchantPaymentTerm($request,$mid);
       return redirect()->back();
    }

    public function checkDate(Request $request)
    {
        $advance_types = array_keys(config('custom.ach_advance_types'));
        $validator = Validator::make($request->all(), ['advance_type' => ['required', Rule::in($advance_types)], 'terms' => 'required|integer', 'start_date' => 'required|date', 'end_date' => 'nullable|date', 'term_id' => 'nullable']);
        if ($validator->fails()) {
            $error = '';
            foreach (array_values($validator->messages()->toArray()) as $msg) {
                $error = $error.implode(' ', $msg);
            }

            return response()->json(['status' => 0, 'msg' => $error], 422);
        }
        if ($request->has('term_id')) {
            $id = $request->term_id;
            $term = MerchantPaymentTerm::find($id);
            $editable_date = Carbon::parse($term->start_at)->toDateString();
            $payments = $term->payments()->where('status', '>', 0)->orderByDesc('payment_date');
            $paid_payments = $payments->count();
            if ($payments->count()) {
                $last_payment_date = $payments->first()->payment_date;
                if ($last_payment_date > $editable_date) {
                    $editable_date = $last_payment_date;
                }
            }
            $last_payment = $term->merchant->last_payment_date;
            if ($last_payment) {
                if ($last_payment > $editable_date) {
                    $editable_date = $last_payment;
                }
            }
            $start_date = $request->start_date ?? $term->start_at;
            $advance_type = $request->advance_type ?? $term->advance_type;
            if ($request->has('end_date')) {
                $end_date = $request->end_date;
                if ($end_date >= $editable_date) {
                    $terms = $this->merchant->getTerms($start_date, $end_date, $advance_type);
                    if ($terms >= $paid_payments) {
                        $data['pmnts'] = $terms;
                        $data['endDate'] = $end_date;

                        return response()->json(['status' => 0, 'data' => $data, 'msg' => 'success'], 200);
                    }
                }
            } else {
                $terms = $request->terms;
                if ($terms >= $paid_payments) {
                    $end_date = $this->merchant->getEndDate($start_date, $advance_type, $terms);
                    if ($end_date >= $editable_date) {
                        $data['pmnts'] = $terms;
                        $data['endDate'] = $end_date;

                        return response()->json(['status' => 0, 'data' => $data, 'msg' => 'success'], 200);
                    }
                }
            }

            return response()->json(['status' => 1, 'msg' => 'failed'], 200);
        } else {
            $start_date = PayCalc::getWorkingDay($request->start_date);
            $advance_type = $request->advance_type;
            $terms = $request->terms;
            $end_date = $this->merchant->getEndDate($start_date, $advance_type, $terms);
            $data['startDate'] = $start_date;
            $data['endDate'] = $end_date;

            return response()->json(['status' => 0, 'data' => $data, 'msg' => 'success'], 200);
        }
    }
    public function setPaymentTerms()
    {
       return PaymentTermHelper::setPaymentTerms();

    }

    public function setPaymentTermForWhomDontHave()
    {
        return PaymentTermHelper::setPaymentTermForWhomDontHave();
        
    }

    public function setFirstPayment()
    {
        $merchants = Merchant::select('id', 'first_payment')->where('first_payment', null)->get();
        foreach ($merchants as $merchant) {
            $mid = $merchant->id;
            $first_payment_date = $merchant->first_payment;
            if (! $first_payment_date) {
                $first_payment = ParticipentPayment::where('merchant_id', $mid)->where('payment', '>', 0)->min('payment_date');
                if ($first_payment) {
                    $merchant->first_payment = $first_payment;
                    $merchant->save();
                    echo "$mid first payment set $first_payment\n";
                } else {
                    echo "$mid no first payment $first_payment\n";
                }
            } else {
                echo "$mid has first payment $first_payment_date\n";
            }
        }
        $merchants_with_invest = MerchantUser::distinct('merchant_user.merchant_id')->pluck('merchant_id')->toArray();
        $NotInvestedMerchantsWithFirstPaymentDate = Merchant::whereNotIn('id', $merchants_with_invest)->whereNotNull('first_payment')->get();
        foreach ($NotInvestedMerchantsWithFirstPaymentDate as $m) {
            echo "first payment $m->first_payment set null of $m->id\n";
            $m->first_payment = null;
            $m->update();
        }

        return true;
    }
    public function pausePayment(Request $request)
    {
        $validator = Validator::make($request->all(), ['merchant_id' => 'required|exists:merchants,id']);
        if ($validator->fails()) {
            $error = '';
            foreach (array_values($validator->messages()->toArray()) as $msg) {
                $error = $error.implode(' ', $msg);
            }
            return response()->json(['status' => 0, 'msg' => $error]);
        }
        $id = $request->merchant_id;
        $merchant = Merchant::find($id);
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        if (in_array($merchant->sub_status_id, $unwanted_sub_status)) {
            return response()->json(['status' => 0, 'msg' => 'Invalid Sub status']);
        }
        if ($merchant && $merchant->payment_pause_id == null) {
            $paused_by = $request->user()->name;
            $payment_pause = GPH::pausePayment($merchant, $paused_by, null);
            if ($payment_pause) {
                return response()->json(['status' => 1, 'msg' => 'Payment Paused']);
            }
        }
        return response()->json(['status' => 0, 'msg' => 'Invalid Request']);
    }
   
   public function resumePayment(Request $request)
    {
       return PaymentTermHelper::resumePayment($request);

    }
  
    public function postVerifyEmail(Request $request)
    {
        $email = ($request->input('email'));
        $user = User::where('email', $email)->first();
        $userId = $user ? $user->id : 0;

        return ['success' => true, 'user_id' => $userId];
    }

    public function story(StoryMerchantRequest $request, $id)
    {
        $title = 'Merchant Story';
        if (! $request->isMethod('post')) {
            $merchant = Merchant::find($id);

            return view('admin.merchants.story', compact('title', 'merchant'));
        }
        $requestData = $request->all();
        if ($request->hasFile('story_image')) {
            $requestData['story_image'] = $request->file('story_image')->store('uploads', 'public');
        }
        $merchant = Merchant::findOrFail($id);
        $merchant->update($requestData);

        return redirect("admin/merchants/view/$id")->with('message', 'Story updated!');
    }
    public function creditcard_payment(Request $request, $id)
    {
       return ParticipantPaymentHelper::creditcardPayment($request,$id);

    }
    public function updateTermPayment($mid, Request $request)
    {
        DB::beginTransaction();
        try {
            PaymentTermHelper::updatePaymentTerm($request,$mid);
            return redirect()->back();
        } catch (Exception $e) {
            DB::rollback();
            $request->session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }
    public function deleteTermPayment($mid, $tid, $id, Request $request)
    {
        $delete = PaymentTermHelper::deleteTermPaymentFunction($mid, $tid, $id);
        if ($delete['status']) {
            return redirect()->back()->with('message', $delete['message']);
        }

        return redirect()->back()->with('error', $delete['message']);
    }

    public function deleteACHPayments($mid, Request $request)
    {
        $ach_ids = $request->ach_ids;
        $deleted_count = 0;
        $status = 0;
        foreach ($ach_ids as $ach_delete) {
            $tid = $ach_delete['tid'];
            $id = $ach_delete['id'];
            $delete = PaymentTermHelper::deleteTermPaymentFunction($mid, $tid, $id);
            if ($delete['status']) {
                $deleted_count++;
            }
        }
        if ($deleted_count > 0) {
            $status = 1;
            $message = "$deleted_count ACH Schedules deleted successfully.";
        } else {
            $message = 'Can\'t delete selected ACH Schedules.';
        }

        return response()->json(['status' => $status, 'msg' => $message]);
    }

    public function addTermPayment($mid, Request $request)
    {
        $result=PaymentTermHelper::addPaymentTerm($request,$mid);
        
        return response()->json(['status' => $result['status'], 'msg' => $result['msg']]);
    }

    public function makeupPaymentTerms(Request $request)
    {
        $result=PaymentTermHelper::makeUpPaymentTerms($request);

        return response()->json(['msg' => $result['msg'], 'status' => $result['status']]);
    }

    public function updateMaxParticipantFund(Request $request, $id = null)
    {
        if ($id != null) {
            $payment_count = ParticipentPayment::where('participent_payments.merchant_id', $id)->count();
            if ($payment_count <= 0) {
                DB::beginTransaction();
                $merchants = DB::table('merchants');
                $merchants = $merchants->select('id', 'name', 'rtr', 'funded', 'max_participant_fund_per', 'pmnts', 'factor_rate');
                $merchants = $merchants->where('id', $id);
                $data = $merchants->first();
                $companies = DB::table('users')->where('company', '!=', 'null')->pluck('company', 'company');
                foreach ($companies as $company) {
                    $company_merchant_investor_amount = DB::table('merchant_user')->join('users', 'users.id', 'merchant_user.user_id')->where('users.company', $company)->where('merchant_id', $data->id)->sum('amount');
                    $company_amount = new CompanyAmount;
                    $company_amount = $company_amount->where('merchant_id', $data->id);
                    $company_amount = $company_amount->where('company_id', $company);
                    $company_amount = $company_amount->first();
                    if ($company_amount) {
                        $company_amount->max_participant = $company_merchant_investor_amount;
                        $company_amount->save();
                    }
                }
                $max_participant_fund = DB::table('company_amount')->where('merchant_id', $data->id)->sum('max_participant');
                if ($data->max_participant_fund_per > 0) {
                    $max_participant_fund_per = $data->max_participant_fund_per;
                    $funded = $max_participant_fund * 100 / $data->max_participant_fund_per;
                } else {
                    $max_participant_fund_per = 100;
                    $funded = $max_participant_fund;
                }
                $rtr = $funded * $data->factor_rate;
                $payment_amount = $rtr / $data->pmnts;
                DB::table('merchants')->where('id', $data->id)->update(['funded' => $funded, 'max_participant_fund' => $max_participant_fund, 'rtr' => DB::raw('round(funded*factor_rate,4)'), 'payment_amount' => $payment_amount, 'max_participant_fund_per' => $max_participant_fund_per]);
                DB::commit();
                $request->session()->flash('message', 'Funded details updated');

                return redirect()->back();
            }
        }
    }

    public function checkBankAccountsExist(Request $request)
    {
        $status = 0;
        $message = 'Error';
        if ($request->merchant_id) {
            $merchant = Merchant::select('id', 'name')->where('id', $request->merchant_id)->first();
            if ($merchant) {
                $bank_account_count = $merchant->bankAccountDebit()->count();
                if ($bank_account_count) {
                    $status = 1;
                    $message = 'Merchant has linked bank account.';
                } else {
                    $status = 2;
                    $message = 'Merchant has not linked bank account.';
                }
            }
        }

        return response()->json(['status' => $status, 'msg' => $message]);
    }

    public function FactorRateMerchnatUserUpdate($merchant_id, $factor_rate = null)
    {
        try {
            DB::beginTransaction();
            $merchant = Merchant::find($merchant_id);
            if ($factor_rate) {
                $merchant->factor_rate = $factor_rate;
                $merchant->save();
            }
            $investment_data = MerchantUser::where('merchant_id', $merchant_id)->where('merchant_user.status', 1)->get();
            foreach ($investment_data as $key => $investments) {
                $invest_rtr = $merchant->factor_rate * $investments->amount;
                MerchantUser::where('id', $investments->id)->update(['invest_rtr' => $invest_rtr]);
            }
            $investor_array = MerchantUser::where('merchant_id', $merchant_id)->where('status', 1)->pluck('user_id', 'user_id')->toArray();
            $complete_per = PayCalc::completePercentage($merchant_id, $investor_array);
            Merchant::where('id', $merchant_id)->update(['complete_percentage' => $complete_per]);
            DB::commit();
            $return['msg'] = 'Merchant updated Successfully';
        } catch (\Exception $e) {
            DB::rollback();
            $return['msg'] = $e->getMessage();
        }

        return response()->json($return);
    }
   
   public function update_agent_fee(Request $request)
    {
       return MerchantHelper::updateAgentFee($request);

    }
    public function deleteParticipantRow(Request $request){
       $participant_id = $request->participant_id;
       $data_arr = session('assign_investors_arr');
       foreach($data_arr as $key => $val) {
        if($val['id'] == $participant_id) {
            unset($data_arr[$key]);
        }
       }
       $data_arr = array_values($data_arr);
       Session::put(['assign_investors_arr' => $data_arr]);
      // session()->push('assign_investors_arr', $data_arr); 
      $data_arr = session('assign_investors_arr');

      return response()->json(['status' => 1,'message'=>'deleted successfully']);   
    }
    public function updateAssignInvestorSession(Request $request){
       $participant_id = $request->participant_id;
       $data_arr = session('assign_investors_arr');
       for($i=0;$i<count($data_arr);$i++) {
        if($data_arr[$i]['id'] == $participant_id) {
            $data_arr[$i]['underwriting_fee_per']  = round($request->underwriting_fee_percent,2);
            $data_arr[$i]['syndication_fee_per']  = round($request->syndiaction_fee_percent,2);
            $data_arr[$i]['syndication_on']  = $request->syndiaction_on;
            $data_arr[$i]['upsell_commission_per']  = round($request->upsell_commission_percent,2);
            $data_arr[$i]['mgmnt_fee_per']  = round($request->mgmnt_fee_percent,2);
            $data_arr[$i]['amount']  = round($request->amount,2);
            
        }
       }
       $data_arr = array_values($data_arr);
       Session::put(['assign_investors_arr' => $data_arr]);
      // session()->push('assign_investors_arr', $data_arr); 
      $data_arr = session('assign_investors_arr');

      return response()->json(['status' => 1,'message'=>'updated successfully']);   
    }
    public function cancelAllParticipantRow(){
        $data_arr = session('assign_investors_arr');
        if(!empty(($data_arr))){
        Session::put(['assign_investors_arr' => []]);
        return response()->json(['status' => 1,'message'=>'Cancelled successfully']);
        }
        else{
        return response()->json(['status' => 0]);
        }
    }

    public function date_wise_investor_payment($merchant_id)
    {
        $result=ParticipantPaymentHelper::dateWiseInvestorPayment($merchant_id);

        $fileName = 'Date vs Investor Payment '.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $export = new Data_arrExport($result);
        return Excel::download($export, $fileName);

    }
    public function listInvestorsBasedOnLiquidity(Request $request){
       try {

          $sess_arr=InvestorAssignHelper::investorsBasedOnLiquidity($request);
            
          Session::put(['assign_investors_arr' => $sess_arr]);
          return response()->json(['status' => 1,'message'=>'successfully']);
          DB::rollback();  
        } catch (\Exception $e) {
          DB::rollback();
          return response()->json(['status' => 0,'message'=>$e->getMessage()]);
            
        }
    }

    public function checkCompanyshare(Request $request){
    
      $investor_company = User::where('id',$request->user_id)->value('company');
      $syndicate= User::where('id',$investor_company)->value('syndicate');
      if($syndicate==0){
        return response()->json(['status' => 1, 'msg' => 'not a syndicate company']);
      }
      $company_invested_amount = MerchantUserView::where('merchant_id', $request->mechant_id)->where('company', $investor_company)->sum('amount');
      $company_invested_amount += $request->amount;
      $companies_1 = DB::table('company_amount')->where('merchant_id', $request->merchant_id)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();
      if(isset($companies_1[$investor_company])){
        if (($companies_1[$investor_company]-$company_invested_amount)<$request->amount) {
                $msg = 'Company Share is '.$companies_1[$investor_company];
               return response()->json(['status' => 0, 'msg' => $msg]);
                            
         }
       }
       return response()->json(['status' => 1, 'msg' => 'success']);
    }

    public function ExpectationVsGiven(Request $request,Builder $tableBuilder,$merchant_id)
    {
        return ParticipantPaymentHelper::ExpectationVsGiven($request,$tableBuilder,$merchant_id);

    }
    public function ExpectationVsGivenSingle(Request $request,$participent_payment_id)
    {
         return ParticipantPaymentHelper::ExpectationVsGivenSingle($participent_payment_id); 

    }
    
    public function LiquidityBasedInvestment($merchant_id)
    {
        $result=MerchantHelper::LiquidityBasedInvestment($merchant_id);
        return view('admin.merchants.investment.liquidity_based', $result);
    }
    public function LiquidityBasedShare(Request $request)
    {
        $data['investors']   = $request->investors;
        $merchant_id         = $request->merchant_id;
        $data['merchant_id'] = $merchant_id;
        $returnData          = MerchantHelper::LiquidityBasedShare($data);
        $datas               = $returnData['selectedData'];
        $datas               = array_values($datas);
        $company_wise_total  = array();
        foreach($datas as $single) {
            if(!isset($company_wise_total[ $single['company_id'] ])){ $company_wise_total[ $single['company_id'] ]=0; }
            $company_wise_total[ $single['company_id'] ] += $single['share'];
        }
        $current_investment  = FFM::dollar(array_sum($company_wise_total));
        $liquidity           = array_sum(array_column($datas, 'liquidity'));
        $available_liquidity = array_sum(array_column($datas, 'available_liquidity'));
        $share               = array_sum(array_column($datas, 'share'));
        $investment          = array_sum(array_column($datas, 'investment'));
        $balance             = $liquidity-$investment;
        $Merchant            = Merchant::select('funded')->find($merchant_id);
        $funded              = $Merchant->funded;
        return \DataTables::of($datas)
        ->addColumn('Investor', function ($data) { return $data['name']; })
        ->editColumn('share', function ($data) { return round($data['share'],2); })
        ->editColumn('liquidity', function ($data) { return round($data['liquidity'],2); })
        ->editColumn('available_liquidity', function ($data) { return round($data['available_liquidity'],2); })
        ->editColumn('investment', function ($data) { return round($data['investment'],2); })
        ->editColumn('commission', function ($data) { return FFM::percent($data['commission']); })
        ->editColumn('underwriting_fee', function ($data) { return FFM::percent($data['underwriting_fee']); })
        ->editColumn('syndication_fee', function ($data) { return FFM::percent($data['syndication_fee']); })
        ->addColumn('balance', function ($data) { return round($data['liquidity']-$data['investment'],2); })
        ->addColumn('share_percentage', function ($data) use($funded) { 
            $share_percentage=$data['share']/$funded*100;
            return FFM::percent($share_percentage);
        })
        ->rawColumns(['Investor'])
        ->addIndexColumn()
        ->with('liquidity', FFM::dollar($liquidity))
        ->with('available_liquidity', FFM::dollar($available_liquidity))
        ->with('share', FFM::dollar($share))
        ->with('investment', FFM::dollar($investment))
        ->with('balance', FFM::dollar($balance))
        ->with('company_wise_total', $company_wise_total)
        ->with('current_investment', $current_investment)
        ->make(true);
    }
    public function LiquidityBasedRejectedList(Request $request)
    {
        $interface =[
           'role' => $this->role,
        ];
        $data['investors']   = $request->investors;
        $merchant_id         = $request->merchant_id;
        $data['merchant_id'] = $merchant_id;
        $returnData          = MerchantHelper::LiquidityBasedShare($data,$interface);
        $datas               = $returnData['RejectedData'];
        $datas               = array_values($datas);
        $company_wise_total  = array();
        $liquidity           = array_sum(array_column($datas, 'liquidity'));
        $available_liquidity = array_sum(array_column($datas, 'available_liquidity'));
        return \DataTables::of($datas)
        ->addColumn('Investor', function ($data) { 
            return "<button investor_id='".$data['user_id']."' class='btn remove_investor btn-sm'>X</button> ".$data['name'];
         })
        ->editColumn('liquidity', function ($data) { return round($data['liquidity'],2); })
        ->editColumn('available_liquidity', function ($data) { return round($data['available_liquidity'],2); })
        ->editColumn('commission', function ($data) { return FFM::percent($data['commission']); })
        ->editColumn('underwriting_fee', function ($data) { return FFM::percent($data['underwriting_fee']); })
        ->editColumn('syndication_fee', function ($data) { return FFM::percent($data['syndication_fee']); })
        ->rawColumns(['Investor'])
        ->addIndexColumn()
        ->with('liquidity', FFM::dollar($liquidity))
        ->with('available_liquidity', FFM::dollar($available_liquidity))
        ->make(true);
    }
    public function AssignLiquidityBasedShare(Request $request)
    {
        try {
            DB::beginTransaction();
           
            $merchant_id       = $request->merchant_id;
            $data['investors'] = $request->all_investors;
            if(empty($data['investors'])){
                throw new \Exception("Please Select Investors", 1);
            }
            $data['merchant_id'] = $merchant_id;
            $return_function=MerchantHelper::AssignLiquidityBasedShare($data);
            if($return_function['result'] != 'success') throw new \Exception($return_function['result']);
            DB::commit();
            $request->session()->flash('message', 'Successfully Assigned');
            return redirect()->route('admin::merchants::view', ['id' => $merchant_id]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    public function investorTransactions(Request $request, IRoleRepository $role)
    {
        $amount = ""; $investors = []; $transaction_date = ""; $merchant = "";
        $transaction_category = ""; $notes = ""; $company = ""; $total_funded = '';
        $investor_filter = "1"; $company_merchant_flag = 0; $company_share = 0;
        $transaction_type = "";
        $split = 1;
        $companyflag = 0;
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $transaction_categories = \ITran::getAllOptions();
        $companies = $role->allCompanies();
        if ($permission == 0) {
            $company_users_q = DB::table('users')->where('company', $userId);
            $companies = $companies->where('id', $userId)->pluck('name', 'id')->toArray();
        } else {
            $companies = $companies->pluck('name', 'id')->toArray();
            $companies['0'] = 'ALL';
        }
        ksort($companies);
        $allMerchants = Merchant::pluck('name','id')->toArray();
        if($request->amount){
            $transaction_type = $request->tran_type;
            $amount = $request->amount;
            $transaction_date = $request->date_transaction;
            $split = $request->split_amount;
            $transaction_category = $request->transaction_category;
            $notes = $request->notes;
            $merchant = $request->merchant;
            $company = $request->company;
            if(!empty($merchant)){
                $total_funded=MerchantUser::where('merchant_id',$request->merchant)->sum('amount');
                $overpayment = User::select('users.id', 'name')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('role_id', User::OVERPAYMENT_ROLE)->first();
                $agent_fee = User::select('users.id', 'name')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('role_id', User::AGENT_FEE_ROLE)->first();
                $investors = MerchantUser::whereNotIn('user_id',[$overpayment->id,$agent_fee->id])->where('merchant_id',$merchant);
                if($company){
                    $company_merchant_flag = 1;
                    $investors = $investors->whereHas('Investor',function ($investor) use ($company) {
                        $investor->whereHas('Company',function ($query){
                            $query->where('company_status',1);
                        });
                        $investor->where('company', $company);
                    });
                    $investor1 = $investors->get();
                    foreach ($investor1 as $inv){
                        $investorShare = ($inv->amount*100)/$total_funded;
                        $company_share += round($investorShare,2);
                    }
                } else {
                    $investors = $investors->whereHas('Investor',function ($investor) {
                        $investor->whereHas('Company',function ($query){
                            $query->where('company_status',1);
                        });
                    });
                }
            } elseif(empty($merchant) && !empty($company)) {
                if($split == 1){
                    $split = 2;
                } 
                $companyflag = 1;
               $investors = User::select('users.*', 'users.id as user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->whereNotIn('users.id',[600,504])->where('users.company',$company); 
            } else {
                return redirect()->back()->withInput()->withErrors("Select Merchant Or Company");
            }
            
            $investors = $investors->get();
            if(count($investors) == 0){
                return redirect()->back()->withInput()->withErrors("No Investors Found");
            }
               
        }

        return view('admin.merchants.investor_transactions',compact('amount','investors','transaction_date','split','merchant','transaction_category','notes','companies','company','total_funded','allMerchants','companyflag','company_merchant_flag','company_share','transaction_categories','transaction_type'));
    }
    public function investorTransactionsStore(Request $request)
    {
        try {
            $investorTransactions = MerchantHelper::storeMultipleTransactions($request);
            if($investorTransactions){
                return redirect()->back()->withMessage("Successfully Added"); 
            } else {
                return redirect()->back()->withErrors("Transactions Not Added(Amount Should Be Greater Than 0)");
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }
    public function PaymentBasedInvestment($merchant_id)
    {
        $result=MerchantHelper::PaymentBasedInvestment($merchant_id);
        return view('admin.merchants.investment.payment_based', $result);
    }
    public function PaymentBasedShare(Request $request)
    {
        $data['investors']   = $request->investors;
        $data['from_date']   = $request->from_date;
        $data['end_date']    = $request->end_date;
        $merchant_id         = $request->merchant_id;
        $data['merchant_id'] = $merchant_id;
        $returnData          = MerchantHelper::PaymentBasedShare($data);
        $net_investment      = $returnData['net_investment'];
        $datas               = $returnData['selectedData'];
        $datas               = array_values($datas);
        $payment             = array_sum(array_column($datas, 'payment'));
        $investment          = array_sum(array_column($datas, 'investment'));
        $funded              = array_sum(array_column($datas, 'share'));
        $Merchant            = Merchant::select('factor_rate')->find($merchant_id);
        $rtr                 = round($funded*$Merchant->factor_rate,2);
        return \DataTables::of($datas)
        ->addColumn('Investor', function ($data) { return $data['name']; })
        ->editColumn('share', function ($data) { return round($data['share'],2); })
        ->editColumn('payment', function ($data) { return round($data['payment'],2); })
        ->editColumn('investment', function ($data) { return round($data['investment'],2); })
        ->editColumn('commission', function ($data) { return FFM::percent($data['commission']); })
        ->editColumn('underwriting_fee', function ($data) { return FFM::percent($data['underwriting_fee']); })
        ->editColumn('syndication_fee', function ($data) { return FFM::percent($data['syndication_fee']); })
        ->addColumn('balance', function ($data) { return round($data['payment']-$data['investment'],2); })
        ->addColumn('share_percentage', function ($data) use($payment) { 
            $share_percentage=$data['share']/$payment*100;
            return FFM::percent($share_percentage);
        })
        ->rawColumns(['Investor'])
        ->addIndexColumn()
        ->with('payment', FFM::dollar($payment))
        ->with('funded', FFM::dollar($funded))
        ->with('rtr', FFM::dollar($rtr))
        ->with('investment', FFM::dollar($investment))
        ->with('net_investment', FFM::dollar($net_investment))
        ->make(true);
    }
    public function PaymentBasedCompanyShare(Request $request)
    {
        $data['investors']   = $request->investors;
        $data['from_date']   = $request->from_date;
        $data['end_date']    = $request->end_date;
        $merchant_id         = $request->merchant_id;
        $data['merchant_id'] = $merchant_id;
        $returnData          = MerchantHelper::PaymentBasedShare($data);
        $datas               = $returnData['selectedData'];
        $datas               = array_values($datas);
        $company_wise_total  = array();
        foreach($datas as $single) {
            if(!isset($company_wise_total[ $single['company'] ])){ $company_wise_total[ $single['company'] ]=0; }
            $company_wise_total[ $single['company'] ] += $single['share'];
        }
        $companyData=[];
        foreach ($company_wise_total as $company => $share) {
            $single['company']         = $company;
            $single['max_participant'] = $share;
            $companyData[]=$single;
        }
        $max_participant = array_sum(array_column($datas,'share'));
        return \DataTables::of($companyData)
        ->editColumn('max_participant', function ($single) { return FFM::dollar($single['max_participant']); })
        ->addColumn('max_participant_percentage', function ($single) use($max_participant) { 
            $share_percentage=$single['max_participant']/$max_participant*100;
            return FFM::percent($share_percentage);
        })
        ->rawColumns(['company'])
        ->addIndexColumn()
        ->with('max_participant', FFM::dollar($max_participant))
        ->make(true);
    }
    public function AssignPaymentBasedShare(Request $request)
    {
        try {
            DB::beginTransaction();
            $merchant_id       = $request->merchant_id;
            $data['investors'] = $request->all_investors;
            if(empty($data['investors'])){
                throw new \Exception("Please Select Investors", 1);
            }
            $data['from_date']   = $request->date_start;
            $data['end_date']    = $request->date_end;
            $data['merchant_id'] = $merchant_id;
            $return_function=MerchantHelper::AssignPaymentBasedShare($data);
            if($return_function['result'] != 'success') throw new \Exception($return_function['result']);
            DB::commit();
            $request->session()->flash('message', $return_function['message']);
            return redirect()->route('admin::merchants::view', ['id' => $merchant_id]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
