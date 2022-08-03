<?php

/**
 * Created by Jabir.
 * User: jabiriocod
 * Date: 5/10/21
 * Time: 3:20 PM.
 */

namespace App\Library\Repository;

use Illuminate\Support\Facades\DB;
use App\Library\Repository\Interfaces\IAdminUserRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\BankDetails;
use App\Exports\Merchant_Graph;
use App\Jobs\CommonJobs;
use App\Jobs\CRMjobs;
use App\Label;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Library\Repository\InvestorTransactionRepository;
use App\Merchant;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\Module;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Role_module;
use App\Settings;
use App\Statements;
use App\SubStatus;
use App\Template;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use FFM;
use Form;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use PayCalc;
use PDF;
use Permissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\Process\Process;
use Yajra\DataTables\Html\Builder;
use InvestorHelper;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;



class AdminUserRepository implements IAdminUserRepository
{
    protected $role;
    protected $user;
    public function __construct(IRoleRepository $role, Builder $tableBuilder, IUserRepository $user, IMerchantRepository $merchant, ISubStatusRepository $subStatus)
    {
        $this->role = $role;
        $this->tableBuilder = $tableBuilder;
        $this->user = $user;
        $this->merchant = $merchant;
        $this->subStatus = $subStatus;
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
            }
        
    }

    public function index()
    {
        $title = 'Admin User';
        $this->tableBuilder->ajax(route('admin::admins::data'));
        $this->tableBuilder->parameters(
            [
                'responsive' => true,
                'autoWidth'  => false,
                'processing' => true,
                'aaSorting'  => [],
                'stateSave'  => true,
                'pagingType' => 'input',
            ]
        );
        $this->tableBuilder = $this->tableBuilder->columns(
            [
                ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'],
                ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'], ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false],
            ]
        );
        return ['title' => $title, 'tableBuilder' => $this->tableBuilder];
    }

    public function resetDbAction($request)
    {
        $db = session('DB_DATABASE');
        if ($db) {
            $database = config('app.database');
            if ($database) {
                \Config::set('database.connections.mysql.database', $database);
                DB::purge('mysql');
                $request->session()->put('DB_DATABASE', '');
                $request->session()->put('restore', 0);
                $msg = 'Database Reset Successfully!';
                $request->session()->flash('message', $msg);

                return response()->json(['msg' => 'success', 'status' => 1]);
            }
        }
    }

    public function changeDbAction($request)
    {
        try {
            $db = $request->get('db');
            if ($db) {
                $request->session()->put('DB_DATABASE', $db);
                $request->session()->put('restore', 1);
                $msg = $db . ' Database Changed Successfully!';
                $request->session()->flash('message', $msg);
                return ['msg' => 'success', 'status' => 1];
            }
        } catch (\Exception $e) {
            return ['msg' => $e->getMessage(), 'status' => 0];
        }
    }

    public function duplicateDb($request)
    {
        $page_title = 'Duplicate Database';
        $date_filter = $request->date_start;
        $change_date = date('d/m/Y', strtotime($date_filter));
        $test = explode('/', $change_date);
        $date_change = $test[0] . '_' . $test[1] . '_' . $test[2];
        $data_base = 'investor_portal_' . $date_change;
        $filter = !empty($date_filter) ? $data_base : '';
        $where = '';
        if ($filter) {
            $where = 'LIKE "%' . $filter . '%"';
        }
        $databases = DB::select('SHOW DATABASES ' . $where);
        $array = [];
        foreach ($databases as $key => $database) {
            foreach ($database as $key1 => $value) {
                $array[] = $value;
            }
        }
        return ['page_title' => $page_title, 'array' => $array];
    }

    public function getMerchants($request)
    {
        $search = $request->get('q');
        $merchants = Merchant::select('id', 'merchants.name as merchant_name');
        $merchants = $merchants->where(function ($query) use ($search) {
            $query->orWhere('name', 'like', '%' . $search . '%');
        });
        $merchants = $merchants->orderBy('name');
        $merchants = $merchants->get();
        $merchants = $merchants->toArray();
        $data = ['total_count' => Merchant::count(), 'incomplete_results' => true, 'items' => $merchants];
        return $data;
    }

    public function getCompanies($request)
    {
        $investorId = $request->get('investorId');
        $company = User::select('users.name as username', 'users.id')->where('id', $investorId);
        $companies = $company->get()->toArray();
        $list = [];
        foreach ($companies as $company) {
            $company_name = User::where('id', $company['company'])->value('name');
            $list[] = ['id' => $id, 'company' => $company_name];
        }
        $data = ['total_count' => User::count(), 'incomplete_results' => true, 'items' => $list];
        return $data;
    }

    public function getInvestorAdmins($request)
    {
        $company = $request->get('companyId');
        $company = ($company == 1) ? 1 : 0;
        $investoradmin = User::select('users.name as username', 'users.id')->where('company', $company)->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('role_id', 6);
        $investoradmins = $investoradmin->get()->toArray();
        $data = ['total_count' => $investoradmin->count(), 'incomplete_results' => true, 'items' => $investoradmins];
        return $data;
    }

    public function getLiquidityAdjuster()
    {
        $page_title = 'Liquidity Adjuster';
        $this->tableBuilder->ajax(
            [
                'url'  => route('admin::admins::liquidity_adjuster'),
                'type' => 'post',
                'data' => 'function(d){d._token = "' . csrf_token() . '";d.roles = $("#roles").val();}',
            ]
        );
        $this->tableBuilder = $this->tableBuilder->columns(
            [
                ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'user_details.liquidity_adjuster', 'name' => 'user_details.liquidity_adjuster', 'title' => 'Liquidity Adjuster'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
            ]
        );
        $this->tableBuilder->parameters(
            [
                'responsive' => true,
                'autoWidth'  => false,
                'processing' => true,
                'pagingType' => 'input',
            ]
        );
        return ['page_title' => $page_title, 'tableBuilder' => $this->tableBuilder];
    }

    public function rowDataLiquidityAdjuster()
    {
        $return = User::select('users.id', 'users.name', 'user_details.liquidity_adjuster')->join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id')->where('user_has_roles.role_id', 2);
        $return = $return->join('user_details', 'users.id', 'user_details.user_id');

        return \DataTables::of($return)->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Settings Liquidity Adjuster', 'Edit')) {
                $return .= '<a href="' . route('admin::admins::create_liquidity_adjuster', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }

            return $return;
        })->editColumn('name', function ($data) {
            return $data->name;
        })->editColumn('user_details.liquidity_adjuster', function ($data) {
            return FFM::dollar($data->liquidity_adjuster);
        })->addIndexColumn()->make(true);
    }

    public function view_user_roles()
    {
        $page_title = 'Users and Roles';
        $status = 0;
        $this->tableBuilder->ajax(['url' => route('admin::admins::userroledata'), 'data' => 'function(d){d.roles = $("#roles").val();}']);
        $this->tableBuilder->parameters(
            [
                'serverSide' => true,
                'responsive' => true,
                'autoWidth'  => false,
                'processing' => true,
                'pagingType' => 'input',
                'order' => [[4, 'desc']]
            ]
        );
        $this->tableBuilder = $this->tableBuilder->columns(
            [
                ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false],
                ['data' => 'name', 'name' => 'name', 'title' => 'Name', 'searchable' => true],
                ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
                ['data' => 'createdat', 'name' => 'users.created_at', 'title' => 'Created At', 'orderable' => true],
                ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
            ]
        );
        $roles = DB::table('roles')->pluck('name', 'id')->toArray();
        return ['tableBuilder' => $this->tableBuilder, 'roles' => $roles, 'status' => $status, 'page_title' => $page_title];
    }

    public function getCompanyWiseInvestors($request)
    {

        $company = $request->get('company');
        $payout_frequency = $request->get('payout_frequency');
        $velocity_owned = $request->get('velocity_owned');
        $list = [];
        $users = $this->role->allInvestors();
        if (!empty($company)) {
            $users = $users->whereIn('company', $company);
        }
        if (!empty($payout_frequency)) {
            $users = $users->whereIn('notification_recurence', $payout_frequency);
        }
        
        $users = $users->toArray();
        foreach ($users as $investor) {
            $list[] = ['id' => $investor['id'], 'investor_name' => strtoupper($investor['name'])];
        }
        $data = ['incomplete_results' => true, 'items' => $list, 'status' => 1];
        return $data;
    }

    public function getMerchantsForAgentFee()
    {
        $AgentFeeIds = User::AgentFeeIds();
        $merchantIds = MerchantUser::whereIn('user_id',$AgentFeeIds);
        $merchantIds = $merchantIds->where('paid_profit','!=',0);
        $merchantIds = $merchantIds->pluck('merchant_id')->toArray();
        $list = Merchant::whereIn('id',$merchantIds)->select('name','id')->get()->toArray();
        $data = ['items' => $list, 'status' => 1];
        return $data;
    }
    public function updateRoleWiseTwoFactorStatus($request)
    {

        $role_id = $request->get('role_id');
        $two_factor_required = $request->get('two_factor_mandatory');
        $update_qr = DB::table('roles')->where('id', $role_id)->update(array('two_factor_required' => $two_factor_required));  
        $two_factor_status = ($two_factor_required==1) ? true : false;
            
        if($update_qr){
            $data = ['status' => 1];
        }else{
            $data = ['status' => 0];
        }
        return $data;
    }

    public function getAssignedInvestors($request)
    {
        $merchantId = $request->get('merchantId');
        $company = $request->get('company');
        $list = [];
        $investors = MerchantUser::select('merchant_user.user_id', 'users.name as investor_name')
            ->leftJoin('users', 'users.id', 'merchant_user.user_id')
            ->where('merchant_user.status', 1)
            ->where(function ($query) use ($merchantId, $company) {
                if ($merchantId != null && !empty($merchantId)) {
                    $query->where('merchant_user.merchant_id', $merchantId);
                }
                if ($company != null && !empty($company)) {
                    $query->where('users.company', $company);
                }
            })->orderBy('users.name')->distinct('users.id')->get()->toArray();
        foreach ($investors as $investor) {
            $list[] = [
                'id'            => $investor['user_id'],
                'investor_name' => $investor['investor_name'],
            ];
        }
        $data = [
            'total_count'        => MerchantUser::count(),
            'incomplete_results' => true,
            'items'              => $list,
            'status'             => 1,
        ];
        return $data;
    }

    public function getInvestorsforOwner($request)
    {
        try {
            $company = $request->get('company');
            if ($company == 0) {
                $data = DB::table('users')->pluck('id');
            } else {
                $data = DB::table('users')->where('company', $company)->pluck('id');
            }
            return $data;
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function getAllInvestors()
    {
        try {
            $investors = $this->role->allInvestorsLiquidity();
            $list = [];
            foreach ($investors as $investor) {
                if ($investor->investor_type == 2) {
                    $type = 'Equity';
                } else {
                    $type = 'Debt';
                }
                $list[] = ['id' => $investor->id, 'investor_name' => $investor->name . '-' . $type . ' -' . $investor->userDetails['liquidity']];
            }
            $data = ['total_count' => '', 'incomplete_results' => true, 'items' => $list, 'status' => 1];
            return $data;
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function getInvestors($request)
    {
        try {
            $search = $request->get('q');
            $merchantId = $request->get('merchantId');
            if (is_array($merchantId)) {
                $merchantId = $request->get('merchantId');
            } else {
                $merchantId = [0 => $request->get('merchantId')];
            }
            $company = $request->get('company');
            $investor_type = $request->get('investor_type');
            if (!empty($merchantId[0]) || $company || $investor_type) {
                $list = [];
                $investors = MerchantUser::select('merchant_user.user_id', 'users.name as investor_name', 'user_details.liquidity', 'users.investor_type')->join('users', 'users.id', 'merchant_user.user_id')->join('user_details', 'users.id', 'user_details.user_id')->whereNOTIn('users.investor_type', [5])->where(function ($query) use ($merchantId, $company, $investor_type) {
                    if (!empty($merchantId[0])) {
                        $query->whereIn('merchant_user.merchant_id', $merchantId);
                    }
                    if ($company != null && !empty($company)) {
                        $query->where('users.company', $company);
                    }
                    if ($investor_type != 0) {
                        $query->where('users.investor_type', $investor_type);
                    }
                })->where(function ($query) use ($search) {
                    $query->orWhere('users.name', 'like', '%' . $search . '%');
                })->orderBy('users.name')->distinct('user_id')->get()->toArray();
                foreach ($investors as $investor) {
                    if ($investor['investor_type'] == 2) {
                        $type = 'Equity';
                    } else {
                        $type = 'Debt';
                    }
                    $list[] = ['id' => $investor['user_id'], 'investor_name' => $investor['investor_name'] . '-' . $type . ' -' . $investor['liquidity']];
                }
                $data = ['total_count' => MerchantUser::count(), 'incomplete_results' => true, 'items' => $list, 'status' => 1];

                return response()->json($data);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function enableTwoFactorAuth($request, $enable, $disable)
    {
        $enable($request->user());
        if ($request->user()->two_factor_secret) {
            $qrcode = $request->user()->twoFactorQrCodeSvg();
            $request->session()->put('two_factor_secret', $request->user()->two_factor_secret);
            $request->session()->put('two_factor_recovery_codes', $request->user()->two_factor_recovery_codes);
            $disable($request->user());
        }
        return $qrcode;
    }

    public function postTwoFactorAuthSettings($request, $disable)
    {
        $verify = app(TwoFactorAuthenticationProvider::class)->verify(decrypt($request->session()->get('two_factor_secret')), $request->code);
        if ($verify) {
            $two_factor_secret = $request->session()->get('two_factor_secret');
            $two_factor_recovery_codes = $request->session()->get('two_factor_recovery_codes');
            User::whereId($request->user()->id)->update(['two_factor_secret' => $two_factor_secret, 'two_factor_recovery_codes' => $two_factor_recovery_codes]);
            $message['title'] = "You've enabled two-step verification";
            $message['subject'] = "You've enabled two-step verification";
            $message['status'] = 'two_step_enabled_verification_notification';
            $message['to_mail'] = $request->user()->email;
            $message['email'] = $request->user()->email;
            $message['unqID'] = unqID();
            $email_template = Template::where([
                ['temp_code', '=', 'TWFEN'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $request->user()->email);
                        $bcc_mails[] = $role_mails;
                    }
                    $message['to_mail'] = Arr::flatten($bcc_mails);
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                }
            }
            return true;
        } else {
            $disable($request->user());
            return false;
        }
    }

    public function twoFactorAuthSettings($request)
    {
        if ($request->session()->get('status') == 'two-factor-authentication-disabled') {
            $message['title'] = "You've disabled two-step verification";
            $message['subject'] = "You've disabled two-step verification";
            $message['status'] = 'two_step_disabled_verification_notification';
            $message['to_mail'] = $request->user()->email;
            $message['email'] = $request->user()->email;
            $message['unqID'] = unqID();
            $email_template = Template::where([
                ['temp_code', '=', 'TWFD'], ['enable', '=', 1],
            ])->first();
            if ($email_template) {
                $emailJob = (new CommonJobs($message));
                dispatch($emailJob);
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, [$request->user()->email]);
                        $bcc_mails[] = $role_mails;
                    }
                    $message['to_mail'] = Arr::flatten($bcc_mails);
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                }
            }
        }
    }
    public function create_lenders()
    {
        $page_title = 'Create New Lender';
        $action = 'create';
        $companies = $this->role->allCompanies();
        $underwriting_company = $companies->pluck('name', 'id')->toArray();
        array_unshift($underwriting_company, '');
        unset($underwriting_company[0]);
        $fee_values = FFM::fees_array();
        $data = ['underwriting_company' => $underwriting_company, 'fee_values' => $fee_values, 'page_title' => $page_title, 'action' => $action];
        return $data;
    }

    public function delete_module($request, $id, $type)
    {
        try {
            if (Module::where('id', $id)->delete()) {
                Role_module::where('module_id', $id)->each(function ($row) {
                    $row->delete();
                });
                $request->session()->flash('message', 'Module deleted!');
            }
            return true;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function saveModuleData($request)
    {
        try {
            $module = Module::create(['name' => $request->name, 'creator_id' => $request->user()->id]);
            if ($module) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function recoveryKeyPdfView()
    {
        try {
            return PDF::loadView('admin.recovery_key_view');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function saveRoleData($request)
    {
        try {
            $role = Role::create(['name' => strtolower($request->name)]);
            if ($role) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updateModuleData($request, $id)
    {
        try {
            $module = Module::where('id', $id)->update(['name' => $request->name, 'creator_id' => $request->user()->id]);
            if ($module) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function edit_modules($id)
    {
        try {
            return Module::where('id', $id)->first();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getModuleData()
    {
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        return \DataTables::collection($this->role->allModuleData())->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Modules', 'Delete')) {
                $return .= Form::open(['route' => ['admin::roles::delete-module', ['id' => $data->id, 'type' => 'module']], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']) . Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']) . Form::close();
            }

            if (Permissions::isAllow('Modules', 'Edit')) {
                $return .= '<a href="' . route('admin::roles::edit-module', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }

            return $return;
        })->editColumn('created_at', function ($data) {
            $creator_ = (isset($data->creator_id)) ? get_user_name_with_session($data->creator_id) : 'system';
            $created_date = 'Created On ' . \FFM::datetime($data->created_at) . ' by ' . $creator_;

            return "<a title='$created_date'>" . \FFM::datetime($data->created_at) . '</a>';
        })->editColumn('updated_at', function ($data) {

            return \FFM::datetime($data->updated_at);
        })->addIndexColumn()->rawColumns(['created_at', 'updated_at', 'action'])->make(true);
    }

    public function view_modules()
    {
        $page_title = 'All Modules';
        $this->tableBuilder->ajax(route('admin::admins::moduledata'));
        $this->tableBuilder->parameters(
            [
                'responsive' => true,
                'autoWidth' => false,
                'processing' => true,
                'aaSorting' => [],
                'pagingType' => 'input',
            ]
        );
        $this->tableBuilder = $this->tableBuilder->columns(
            [
                ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'],
                ['data' => 'name', 'name' => 'name', 'title' => 'Name', 'searchable' => true],
                ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false],
            ]
        );
        $roles = DB::table('roles')->pluck('name', 'id')->toArray();
        $status = 1;
        $data = ['tableBuilder' => $this->tableBuilder, 'roles' => $roles, 'status' => $status, 'page_title' => $page_title];
        return $data;
    }

    public function run_commands($request)
    {
        if (!$request->isMethod('post')) {
            return view('admin.others.run-commands');
        }
        $process = Process::fromShellCommandline("$request->command");
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > ' . $buffer;
            } else {
                echo 'OUT > ' . $buffer;
            }
        });
    }

    public function Postgitpull($request)
    {
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        ini_set('max_execution_time', 0);
        $process = Process::fromShellCommandline("git reset --hard; git pull origin $request->branch;  cd .. ;  php artisan migrate;  php artisan config:clear; php artisan optimize:clear; php artisan db:seed --class=View; npm run prod;  ");
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo '<pre>' . 'ERR > ' . $buffer . '</pre>';
            } else {
                echo '<pre>' . 'OUT > ' . $buffer . '</pre>';
            }
        });
    }

    public function create_users()
    {
        try {
            $roles = Role::whereNotIn('name', ['investor', 'lender', 'merchant'])->get();
            $page_title = 'Create New User';
            $action = 'create';
            $role_count = '';
            return ['page_title' => $page_title, 'action' => $action, 'roles' => $roles, 'role_count' => $role_count];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function editLenders($lender)
    {
        try {
            $action = 'edit';
            $companies = $this->role->allCompanies();
            $underwriting_company = $companies->pluck('name', 'id')->toArray();
            array_unshift($underwriting_company, '');
            unset($underwriting_company[0]);
            $fee_values = FFM::fees_array();
            $page_title = 'Edit Lender';
            return ['action' => $action, 'fee_values' => $fee_values, 'underwriting_company' => $underwriting_company, 'page_title' => $page_title, 'lender' => $lender];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function viewLenders($lender)
    {
        try {
            $page_title = 'Lender View';
            $value = 0.0;
            while ($value <= 5) {
                $syndication_fee_values["$value"] = $value;
                $value = $value + 0.5;
            }
            $action = 'edit';
            $companies = $this->role->allCompanies();
            $underwriting_company = $companies->pluck('name', 'id')->toArray();
            array_unshift($underwriting_company, '');
            unset($underwriting_company[0]);
            return [
                'page_title' => $page_title,
                'syndication_fee_values' => $syndication_fee_values,
                'lender' => $lender,
                'action' => $action,
                'underwriting_company' => $underwriting_company
            ];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function view_lenders()
    {
        try {
            $page_title = 'All Lenders';
            $this->tableBuilder->ajax(route('admin::admins::lenderdata'));
            $this->tableBuilder->parameters(
                [
                    'responsive' => true,
                    'autoWidth'  => false,
                    'processing' => true,
                    'aaSorting'  => [],
                    'pagingType' => 'input',
                ]
            );
            $this->tableBuilder = $this->tableBuilder->columns(
                [
                    ['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'],
                    ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
                    ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
                    ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'],
                    ['data' => 'syndication_fee', 'name' => 'syndication_fee', 'title' => 'Syndication Fee %'],
                    ['data' => 'management_fee', 'name' => 'management_fee', 'title' => 'Management Fee %'],
                    ['data' => 'lag_time', 'name' => 'lag_time', 'title' => 'Lag Time'],
                    ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
                ]
            );
            return ['page_title' => $page_title, 'tableBuilder' => $this->tableBuilder];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete($request)
    {
        try {
            DB::table('user_has_roles')->where('model_id', $id)->delete();
            if (User::find($id)->delete()) {
                $request->session()->flash('message', 'Admin user deleted');
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function rowData()
    {
        try {
            session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
            return \DataTables::collection($this->role->allAdminUsers())->addColumn('action', function ($data) {
                $return = '';
                if (Permissions::isAllow('Admins', 'Edit')) {
                    $return .= '<a href="' . route('admin::admins::edit', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                }
                if (Permissions::isAllow('Admins', 'Delete')) {
                    $return .= Form::open(['route' => ['admin::admins::delete', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']) . Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']) . Form::close();
                }

                return $return;
            })->editColumn('created_at', function ($data) {
                $created_date = 'Created On ' . \FFM::datetime($data->created_at) . ' by ' . get_user_name_with_session($data->creator_id);

                return "<a title='$created_date'>" . \FFM::datetime($data->created_at) . '</a>';
            })->editColumn('updated_at', function ($data) {
                return \FFM::datetime($data->updated_at);
            })->addIndexColumn()->rawColumns(['action', 'created_at', 'updated_at'])->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function changeAdvancedStatusAction()
    {
        try {
            $merchant_data = Merchant::select(['merchants.id', 'name', 'funded', 'pmnts', 'date_funded', 'marketplace_status', 'paid_count', 'commission', 'rtr', 'merchants.complete_percentage'])
                ->leftJoin('merchant_user', 'merchants.id', 'merchant_user.merchant_id')
                ->where('merchants.active_status', 1)
                ->having(DB::raw('sum(merchant_user.invest_rtr)'), '<', DB::raw('sum(merchant_user.paid_participant_ishare)+sum(merchant_user.paid_mgmnt_fee)'))
                ->where('sub_status_id', '=', 1)
                ->where('merchants.complete_percentage', '>=', 99)
                ->where('merchants.complete_percentage', '>', 0)
                ->orderBy('merchants.complete_percentage')
                ->groupBy('merchants.id')
                ->get();
            $array = $merchant_data->toArray();
            $dealArray = [];
            if (!empty($array)) {
                foreach ($merchant_data as $data) {
                    $payments_investors = PaymentInvestors::select(DB::raw('sum(payment_investors.participant_share) as participant_share'))
                        ->where('payment_investors.merchant_id', $data->id)
                        ->groupBy('merchant_id')
                        ->first()
                        ->toArray();
                    $merchant_array = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'))->where('merchant_id', $data->id)->groupBy('merchant_id')->first()->toArray();
                    $total_rtr = $merchant_array['invest_rtr'];
                    $bal_rtr = $total_rtr - $payments_investors['participant_share'];
                    $actual_payment_left = 0;
                    if ($total_rtr > 0) {
                        $actual_payment_left = ($data->rtr) ? $bal_rtr / (($total_rtr / $data->rtr) * ($data->rtr / $data->pmnts)) : 0;
                    } else {
                        $actual_payment_left = 0;
                    }
                    $act_paymnt_left = floor($actual_payment_left);
                    $actual_payment_left = ($act_paymnt_left > 0) ? $act_paymnt_left : 0;
                    if ($actual_payment_left <= 0 && $data->complete_percentage >= 100) {
                        $dealArray[] = ['merchant_id' => $data->id, 'merchant_name' => $data->name, 'complete_percentage' => FFM::percent($data->complete_percentage)];
                    }
                }
            }

            return ['status' => 1, 'result' => $dealArray];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function advanced_status_check($request)
    {
        try {
            $emails = Settings::value('email');
            $email_id_arr = explode(',', $emails);
            $merchants = $request->merchants;
            $logArray = [];
            if (!empty($merchants)) {
                $count = count($merchants);
                $author = $request->user()->name;
                foreach ($merchants as $key => $merchant_id) {
                    $old_status = Merchant::where('id', '=', $merchant_id)->value('sub_status_id');
                    $merchant_name = Merchant::where('id', '=', $merchant_id)->value('name');
                    if ($old_status != 11) {
                        $logArray = [
                            'merchant_id' => $merchant_id,
                            'old_status' => $old_status,
                            'current_status' => 11,
                            'description' => 'To Change Status to "Advance completed" by ' . $author . ',  if 100% completed. ', 'creator_id' => $request->user()->id,
                        ];
                        $log = MerchantStatusLog::create($logArray);
                    }
                    Merchant::find($merchant_id)->update(['sub_status_id' => 11, 'last_status_updated_date' => $log->created_at]);
                    $substatus_name = SubStatus::where('id', 11)->value('name');
                    $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
                    $form_params = [
                        'method' => 'merchant_update',
                        'username' => config('app.crm_user_name'),
                        'password' => config('app.crm_password'),
                        'investor_merchant_id' => $merchant_id,
                        'status' => $substatus_name,
                    ];
                    try {
                        $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                        dispatch($crmJob);
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                    $message['title'] = 'Advance completed 100%';
                    $message['content'] = 'Merchant <a href=' . url('admin/merchants/view/' . $merchant_id) . '>' . $merchant_name . '</a> Advance completed 100%';
                    $message['to_mail'] = $email_id_arr;
                    $message['merchant_id'] = $merchant_id;
                    $message['status'] = 'merchant_change_status';
                    $message['unqID'] = unqID();
                    $message['template_type'] = 'advance_complete_100_percent';
                    $message['merchant_name'] = $merchant_name;
                    try {
                        $email_template = Template::where([
                            ['temp_code', '=', 'MSAC'], ['enable', '=', 1],
                        ])->first();
                        if ($email_template) {
                            if ($email_template->assignees) {
                                $template_assignee = explode(',', $email_template->assignees);
                                $bcc_mails = [];
                                foreach ($template_assignee as $assignee) {
                                    $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                                    $role_mails = array_diff($role_mails, $email_id_arr);
                                    $bcc_mails[] = $role_mails;
                                }
                                $message['bcc'] = Arr::flatten($bcc_mails);
                            }
                            $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                            dispatch($emailJob);
                            $message['bcc'] = [];
                            $message['to_mail'] = $this->admin_email;
                            $emailJob = (new CommonJobs($message));
                            dispatch($emailJob);
                        }
                    } catch (\Exception $e) {
                        $e->getMessage();
                    }
                }
                $msg = $count . ' Merchants status changed to Advance completed Successfully';
                $request->session()->flash('message', $msg);

                return ['status' => 1, 'msg' => $msg];
            } else {
                $msg = ' No Merchants Found';
                return ['status' => 0, 'msg' => $msg];
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getallLenders()
    {
        try {
            session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
            return \DataTables::collection($this->role->allLenders())->addColumn('action', function ($data) {
                $return = '';
                if (Permissions::isAllow('Lenders', 'View')) {
                    $return .= '<a href="' . route('admin::lenders::view_lender', ['id' => $data->id]) . '" class="btn btn-xs btn-success"><i class="glyphicon glyphicon-zoom-in"></i> View</a>';
                }
                if (Permissions::isAllow('Lenders', 'Edit')) {
                    $return .= '<a href="' . route('admin::lenders::edit_lender', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                }
                if (Permissions::isAllow('Lenders', 'Delete')) {
                    $return .= Form::open(
                        [
                            'route' => ['admin::lenders::delete_lender', ['id' => $data->id, 'type' => 'lender']],
                            'method' => 'POST',
                            'onsubmit' => 'return confirm("Are you sure that you want to delete ?")',
                        ]
                    ) . Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']) . Form::close();
                }

                return $return;
            })->editColumn('created_at', function ($data) {
                $created_date = 'Created On ' . \FFM::datetime($data->created_at) . ' by ' . get_user_name_with_session($data->creator_id);

                return "<a title='$created_date'>" . \FFM::datetime($data->created_at) . '</a>';
            })->editColumn('lag_time', function ($data) {
                return ($data->lag_time) ? $data->lag_time : 0;
            })->editColumn('updated_at', function ($data) {

                return \FFM::datetime($data->updated_at);
            })->editColumn('syndication_fee', function ($data) {
                return FFM::percent($data->global_syndication);
            })->editColumn('management_fee', function ($data) {
                return FFM::percent($data->management_fee);
            })->addIndexColumn()->rawColumns(['action', 'created_at', 'updated_at'])->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function deleteUsers($request, $id, $type)
    {
        try {
            $check = Merchant::where('lender_id', $id)->value('lender_id');
            if (empty($check)) {
                if (User::find($id)->delete()) {
                    DB::table('user_has_roles')->where('model_id', $id)->delete();
                    $request->session()->flash('message', 'Lender Deleted.');
                } else {
                    $request->session()->flash('message', 'Lender Not Deleted.');
                }
            } else {
                $request->session()->flash('error', 'Lender Not Deleted. Already Referred');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function view_editors()
    {
        try {
            $page_title = 'All Editors';
            $this->tableBuilder->ajax(route('admin::admins::editordata'));
            $this->tableBuilder->parameters(
                [
                    'responsive' => true,
                    'autoWidth' => false,
                    'processing' => true,
                    'aaSorting' => [],
                    'pagingType' => 'input',
                ]
            );
            $this->tableBuilder = $this->tableBuilder->columns(
                [
                    ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false],
                    ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
                    ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
                    ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
                    ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'],
                    ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
                ]
            );
            return ['page_title' => $page_title, 'tableBuilder' => $this->tableBuilder];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function view_viewers()
    {
        try {
            $page_title = 'All Viewers';
            $this->tableBuilder->ajax(route('admin::admins::viewerdata'));
            $this->tableBuilder->parameters(
                [
                    'responsive' => true,
                    'autoWidth' => false,
                    'processing' => true,
                    'aaSorting' => [],
                    'pagingType' => 'input',
                ]
            );
            $this->tableBuilder = $this->tableBuilder->columns(
                [
                    ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false],
                    ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
                    ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
                    ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
                    ['data' => 'updated_at', 'name' => 'updated_at', 'title' => 'Updated At'],
                    ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
                ]
            );
            return ['page_title' => $page_title, 'tableBuilder' => $this->tableBuilder];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getallViewersData()
    {
        try {
            session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
            return \DataTables::collection($this->role->allViewers())->addColumn('action', function ($data) {
                $return = '';
                if (Permissions::isAllow('Viewers', 'Edit')) {
                    $return .= '<a href="' . route('admin::viewers::edit-viewers', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                }
                if (Permissions::isAllow('Viewers', 'Delete')) {
                    $return .= Form::open(['route' => ['admin::viewers::delete-viewers', ['id' => $data->id, 'type' => 'viewer']], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']) . Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']) . Form::close();
                }

                return $return;
            })->editColumn('created_at', function ($data) {
                $created_date = 'Created On ' . \FFM::datetime($data->created_at) . ' by ' . get_user_name_with_session($data->creator_id);

                return "<a title='$created_date'>" . \FFM::datetime($data->created_at) . '</a>';
            })->editColumn('updated_at', function ($data) {
                return \FFM::datetime($data->updated_at);
            })->addIndexColumn()->rawColumns(['created_at', 'updated_at', 'action'])->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getallEditorsData()
    {
        try {
            session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
            return \DataTables::collection($this->role->allEditors())->addColumn('action', function ($data) {
                $return = '';
                if (Permissions::isAllow('Editors', 'Edit')) {
                    $return .= '<a href="' . route('admin::editors::edit_editors', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                }
                if (Permissions::isAllow('Editors', 'Delete')) {
                    $return .= Form::open(['route' => ['admin::editors::delete_editors', ['id' => $data->id, 'type' => 'editor']], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']) . Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']) . Form::close();
                }

                return $return;
            })->editColumn('created_at', function ($data) {
                $created_date = 'Created On ' . \FFM::datetime($data->created_at) . ' by ' . get_user_name_with_session($data->creator_id);

                return "<a title='$created_date'>" . \FFM::datetime($data->created_at) . '</a>';
            })->editColumn('updated_at', function ($data) {
                return \FFM::datetime($data->updated_at);
            })->addIndexColumn()->rawColumns(['action', 'created_at', 'updated_at'])->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete_editors($request, $id, $type)
    {
        try {
            $check = Merchant::where('lender_id', $id)->count();
            if ($check == 0) {
                DB::table('user_has_roles')->where('model_id', $id)->delete();
                if (User::find($id)->delete()) {
                    $request->session()->flash('message', 'Editor deleted.');
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete_viewers($request, $id, $type)
    {
        try {
            DB::table('user_has_roles')->where('model_id', $id)->delete();
            if (User::find($id)->delete()) {
                $request->session()->flash('message', 'Viewer deleted.');
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getLenderManagementAndSyndFee($request)
    {
        try {
            if ($request->all()) {
                $lender_id = $request->lender_id;
                $fees = User::select('management_fee', 'global_syndication', 's_prepaid_status', 'underwriting_fee', 'underwriting_status')->where('id', $lender_id)->first()->toArray();
                $underwriting_status = ($fees['underwriting_status'] != 'null') ? $fees['underwriting_status'] : 0;
                $management_fee = is_null($fees['management_fee']) ? '0.00' : number_format($fees['management_fee'], 2);
                $syndication_fee = is_null($fees['global_syndication']) ? '0.00' : number_format($fees['global_syndication'], 2);
                $underwriting_fee = is_null($fees['underwriting_fee']) ? '0.00' : number_format($fees['underwriting_fee'], 2);

                return ['management_fee' => $management_fee, 'syndication_fee' => $syndication_fee, 's_prepaid_status' => $fees['s_prepaid_status'], 'underwriting_fee' => $underwriting_fee, 'underwriting_status' => $underwriting_status];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getInvestorManagementAndSyndFee($request)
    {
        try {
            if ($request->all()) {
                $user_id = $request->user_id;
                $fees = User::select('management_fee', 'global_syndication', 's_prepaid_status', 'underwriting_fee', 'underwriting_status')->where('id', $user_id)->first()->toArray();
                $merchant = Merchant::where('id', $request->merchant_id)->first()->toArray();
                $management_fee = is_null($fees['management_fee']) ? number_format($merchant['m_mgmnt_fee'], 2) : number_format($fees['management_fee'], 2);
                $syndication_fee = is_null($fees['global_syndication']) ? number_format($merchant['m_syndication_fee'], 2) : number_format($fees['global_syndication'], 2);
                $s_prepaid_status = !empty($fees['s_prepaid_status']) ? $fees['s_prepaid_status'] : $merchant['m_s_prepaid_status'];

                return ['management_fee' => $management_fee, 'syndication_fee' => $syndication_fee, 's_prepaid_status' => $s_prepaid_status];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function getMerchantFee($request){
    try {
        if ($request->all()) {    
        $merchant = Merchant::where('id', $request->merchant_id)->first()->toArray();
        $management_fee = number_format($merchant['m_mgmnt_fee'], 2);
        $syndication_fee = number_format($merchant['m_syndication_fee'], 2);
        $s_prepaid_status = $merchant['m_s_prepaid_status'];
        $underwriting_fee = number_format($merchant['underwriting_fee'],2);
        $upsell_comm = number_format($merchant['up_sell_commission'],2);
        return ['status'=>1,'management_fee' => $management_fee, 'syndication_fee' => $syndication_fee, 's_prepaid_status' => $s_prepaid_status,'underwriting_fee' => $underwriting_fee,'upsell_comm'=>$upsell_comm];
            } 
    } catch (\Exception $e) {
        return ['status'=>0,'message'=>$e->getMessage()];
        
    }
    }

    public function updateLenderEnableDisable($request)
    {
        try {
            if ($request->all()) {
                $lender_id = $request->lender_id;
                $status = $request->status;
                $active_status = ($status == 'true') ? 1 : 0;
                $UpdateDetails = User::where('id', '=', $lender_id)->first();
                $UpdateDetails->active_status = $active_status;
                $UpdateDetails->save();
                Merchant::where('lender_id', $lender_id)->update(['active_status' => $active_status]);
                Merchant::where('creator_id', $lender_id)->update(['active_status' => $active_status]);

                return ['status' => 'success'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function merchant_status_change($request)
    {
        try {
            $sub_status_id = $request['sub_status_id']??1;
            $no_of_days    = $request['no_of_days']??30;
            $merchant_data = Merchant::select(['merchants.id', 'merchants.name', 'funded', 'pmnts', 'date_funded', 'marketplace_status', 'paid_count', 'commission', 'rtr', 'merchants.complete_percentage','sub_status_id', 'merchants.last_payment_date', 'lag_time', DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount')])
                ->where('merchants.complete_percentage', '<', 99)
                ->where('merchants.complete_percentage', '>', 0)
                ->where('merchants.sub_status_id', '=', $sub_status_id)
                ->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
                ->leftjoin('users', 'users.id', 'merchants.lender_id')
                ->groupBy('merchants.id')
                ->orderBy('last_payment_date')
                ->get();
            $dealArray = [];
            if (!empty($merchant_data)) {
                foreach ($merchant_data as $data) {
                    $payments = PaymentInvestors::select(DB::raw('sum(participant_share-payment_investors.mgmnt_fee) as final_participant_share'))->where('merchant_id', $data->id)->first()->toArray();
                    $last_payment_date2 = !empty($data->last_payment_date) ? $data->last_payment_date : $data->date_funded;
                    if ($last_payment_date2 != '') {
                        $date = date('Y-m-d', strtotime($last_payment_date2));
                        $to = date('Y-m-d');
                        $now = strtotime($to);
                        $your_date = strtotime($date);
                        $datediff = $now - $your_date;
                        $from = Carbon::parse($date);
                        $to = Carbon::parse($to);
                        $days = $from->diffInDays($to);
                        if ($days >= ($no_of_days + $data->lag_time) && $payments['final_participant_share'] < $data->investment_amount) {
                            $dealArray[] = [
                                'merchant_id' => $data->id,
                                'status'      => $data->payStatus,
                                'merchant_name' => $data->name,
                                'complete_per' => FFM::percent($data->complete_percentage),
                                'last_payment_date' => !empty($data->last_payment_date) ? FFM::date($data->last_payment_date) : '-',
                                'days' => $days - $data->lag_time,
                            ];
                        }
                    }
                }

                return ['status' => 1, 'result' => $dealArray];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function merchantStatusCheckAction($request)
    {
        try {
            $emails = Settings::value('email');
            $email_id_arr = explode(',', $emails);
            $merchants = $request->merchants;
            if (!empty($merchants)) {
                $diff_in_months = $request->diff_in_months;
                $count = count($merchants);
                $author = $request->user()->name;
                $sub_status_id = 4;
                foreach ($merchants as $key => $merchant_id) {
                    $old_status = Merchant::where('id', '=', $merchant_id)->value('sub_status_id');
                    $merchant_name = Merchant::where('id', '=', $merchant_id)->value('name');
                    if ($old_status != $sub_status_id) {
                        $logArray = ['merchant_id' => $merchant_id, 'old_status' => $old_status, 'current_status' => $sub_status_id, 'description' => ' Not received a payment in ' . $diff_in_months[$merchant_id] . ' days, therefore status changed to default by ' . $author, 'creator_id' => $request->user()->id];
                        $log = MerchantStatusLog::create($logArray);
                    }
                    $Merchant = Merchant::find($merchant_id);
                    Merchant::find($merchant_id)->update(['sub_status_id' => $sub_status_id, 'last_status_updated_date' => $log->created_at]);
                    $investor_array = [];
                    $investment_data = MerchantUser::where('merchant_id', $merchant_id)->where('merchant_user.status', 1)->get();
                    foreach ($investment_data as $key => $investments) {
                        $investor_array[$key] = $investments->user_id;
                        $invest_rtr = $Merchant->factor_rate * $investments->amount;
                        MerchantUser::where('user_id', $investments->user_id)->where('merchant_id', $merchant_id)->update(['invest_rtr' => $invest_rtr]);
                    }
                    $complete_per = PayCalc::completePercentage($merchant_id, $investor_array);
                    Merchant::find($merchant_id)->update(['complete_percentage' => $complete_per]);
                    $delete_flag = false;
                    $this->merchant->modify_rtr($merchant_id, $sub_status_id, $delete_flag);
                    $substatus_name = SubStatus::where('id', $sub_status_id)->value('name');
                    $substatus_name = str_replace(' ', '_', strtolower($substatus_name));
                    $form_params = ['method' => 'merchant_update', 'username' => config('app.crm_user_name'), 'password' => config('app.crm_password'), 'investor_merchant_id' => $merchant_id, 'status' => $substatus_name];
                    try {
                        $crmJob = (new CRMjobs($form_params))->delay(now()->addMinutes(1));
                        dispatch($crmJob);
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                    $message['title'] = 'Payment Pending';
                    $message['content'] = 'Merchant ' . $merchant_name . ' has payments pending for ' . $diff_in_months[$merchant_id] . ' days';
                    $message['to_mail'] = $email_id_arr;
                    $message['merchant_id'] = $merchant_id;
                    $message['status'] = 'merchant_change_status';
                    $message['unqID'] = unqID();
                    $message['template_type'] = 'pending_payment';
                    $message['merchant_name'] = $merchant_name;
                    $message['days'] = $diff_in_months[$merchant_id];
                    try {
                        $email_template = Template::where([
                            ['temp_code', '=', 'MSPP'], ['enable', '=', 1],
                        ])->first();
                        if ($email_template) {
                            if ($email_template->assignees) {
                                $template_assignee = explode(',', $email_template->assignees);
                                $bcc_mails = [];
                                foreach ($template_assignee as $assignee) {
                                    $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                                    $role_mails = array_diff($role_mails, $email_id_arr);
                                    $bcc_mails[] = $role_mails;
                                }
                                $message['bcc'] = Arr::flatten($bcc_mails);
                            }
                            $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                            dispatch($emailJob);
                            $message['bcc'] = [];
                            $message['to_mail'] = $this->admin_email;
                            $emailJob = (new CommonJobs($message));
                            dispatch($emailJob);
                        }
                    } catch (\Exception $e) {
                        $e->getMessage();
                    }
                }
                $msg = $count . ' Merchant/s Status Changed to Default Successfully!';
                $request->session()->flash('message', $msg);

                return ['status' => 1, 'msg' => $msg];
            } else {
                $msg = 'Select at least one merchant';
                $request->session()->flash('error', $msg);

                return ['status' => 0, 'msg' => $msg];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function merchant_status_log($request)
    {
        try {
            $page_title = 'Merchant Status Log';
            $this->tableBuilder->ajax(['url' => route('admin::merchant_status_log'), 'data' => 'function(d){ d.start_date = $("#date_start").val(); d.end_date = $("#date_end").val();d.status_id = $("#status_id").val();d.merchants = $("#merchants").val();}'])->parameters(['aaSorting' => [], 'columnDefs' => '[{orderable: false, targets: [0]}]', 'fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }", 'pagingType' => 'input']);
            $this->tableBuilder->columns(\MTB::getMerchantStatusLog(null, null, null, null, true));
            $sub_statuses = DB::table('sub_statuses')->orderBy('name')->get();
            $data = ['page_title' => $page_title, 'tableBuilder' => $this->tableBuilder, 'sub_statuses' => $sub_statuses];
            return $data;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function generatedPdfForInvestors()
    {
        try {
            $page_title = 'Generate Statement for Investors';
            $groupBy = [1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily'];
            $recurrence = [1 => 'Last Day', 2 => 'Last Week', 3 => 'Last Two Week', 4 => 'Last Month', 5 => 'Last Year'];
            $investor_types = User::getInvestorType();
            $statuses = $this->subStatus->getAll()->pluck('name', 'id')->toArray();
            $statuses = [0 => 'Select All'] + $statuses;
            $investors = $this->role->allInvestors()->pluck('name', 'id');
            return ['page_title' => $page_title, 'investors' => $investors, 'groupBy' => $groupBy, 'recurrence' => $recurrence, 'investor_types' => $investor_types, 'statuses' => $statuses];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function generatePdfPreview($request)
    {
        try {
            $investor_id = $request->investor;
            $date = PaymentInvestors::select(DB::raw('MIN(participent_payments.created_at) AS min_payment_date, MAX(participent_payments.created_at) AS max_payment_date'))->where('payment_investors.user_id', $investor_id)->leftJoin('participent_payments', 'participent_payments.id', 'payment_investors.participent_payment_id')->first();
            $oldest_payment_date = $date->min_payment_date;
            $latest_payment_date = $date->max_payment_date;
            $date_start = ($request->startDate) ? $request->startDate." 00:00:00" : $oldest_payment_date;
            $date_end = ($request->endDate) ? $request->endDate." 23:59:59" : $latest_payment_date;
            $send_mail = $request->send_mail;
            $merchants = $request->merchants;
            $recurrence = $request->recurrence;
            $hide = (Settings::value('hide') == 1) ? 1 : 0;
            if (!empty($investor_id)) {
                $investors = User::whereIn('id', $investor_id)->get();
                $filters = [

                    'date_start' => $date_start,
                    'from' => 'generatePdfPreview',
                    'date_end' => $date_end,

                    
                    'send_mail' => $send_mail,
                    'merchants' => $merchants,
                    'recurrence' => $recurrence,
                    'hide' => $hide,
                    'generationtype' => 0,
                ];
                if (!empty($investors)) {
                    $msg = $this->user->generatePDFCSV($investors, $filters);

                    return ['status' => 1, 'msg' => $msg];
                }
            } else {
                return ['status' => 0, 'msg' => 'Please select investors'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function send_mail_to_investors($request)
    {
        try {
            $id_array = $request->multi_id;
            $msg = '';
            if (!empty($id_array)) {
                foreach ($id_array as $id) {
                    $investor = Statements::select('name', 'statements.id', 'email', 'file_name', 'notification_email')
                        ->where('statements.id', $id)
                        ->join('users', 'users.id', 'statements.user_id')
                        ->first()
                        ->toArray();
                    if (!empty($investor)) {
                        $fileName = $investor['file_name'] . '.pdf';
                        $fileUrl = Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(1));
                        $message['title'] = 'Payment Report Statement';
                        $message['subject'] = 'Payment Report Statement';
                        $message['content'] = 'Payment Statement Report ';
                        $message['to_mail'] = ($investor['notification_email'] != null) ? $investor['notification_email'] : $investor['email'];
                        $message['options'] = 'Weekly';
                        $message['investor_name'] = $investor['name'];
                        $message['attach'] = $fileUrl;
                        $message['status'] = 'pdf_mail';
                        $message['fileName'] = $fileName;
                        $message['heading'] = 'Payment Report Statement';
                        $message['unqID'] = unqID();
                        $message['template_type'] = 'pdf_normal';
                        $email_template = Template::where([
                            ['temp_code', '=', 'GPDF'], ['enable', '=', 1],
                        ])->first();
                        if ($email_template) {
                            if ($email_template->assignees) {
                                $template_assignee = explode(',', $email_template->assignees);
                                $bcc_mails = [];
                                foreach ($template_assignee as $assignee) {
                                    $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                                    $bcc_mails[] = $role_mails;
                                }
                                $message['bcc'] = Arr::flatten($bcc_mails);
                            }
                            $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                            dispatch($emailJob);
                            $message['bcc'] = [];
                            $message['to_mail'] = $this->admin_email;
                            $emailJob = (new CommonJobs($message));
                            dispatch($emailJob);
                            $msg .= ' Mail Sent Successfully for ' . $investor['name'] . '<a class="btn btn-success" href=' . $fileUrl . '>  Click here to view </a><br>';
                            Statements::where('id', $id)->update(['mail_status' => 1]);
                        } else {
                            $msg .= 'Please enable mail template.';
                        }
                    }
                }
            }

            return ['status' => 1, 'msg' => $msg];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function sendMailToInvestor($request)
    {
        try {
            $message = $request->msg;
            $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
            dispatch($emailJob);
            $message['to_mail'] = $this->admin_email;
            $emailJob = (new CommonJobs($message));
            dispatch($emailJob);

            return ['status' => 1, 'msg' => 'Mail Sent To Investor Successfully'];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function sendInvestorPortal($request)
    {
        try {
            $last_id = $request->last_id;
            if ($last_id) {
                $update = \DB::table('statements')->where('id', $last_id)->update(['investor_portal' => 1]);
                if ($update) {
                    return ['status' => 1, 'msg' => 'Sent To Investor Portal Successfully'];
                } else {
                    return ['status' => 0, 'msg' => 'Will Be Visible From Investors Portal.'];
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function generatedFileLoader($fileName) {
        $fileName = decrypt($fileName); 
        $fileUrl  = Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2));
        return redirect($fileUrl);
    }

    public function generatedCsvPdfManager($request)
    {
        try {
            $page_title = 'Generated PDF/CSV Manager';
            $this->tableBuilder->ajax([
                'url'  => route('admin::generated-pdf-csv'),
                'data' =>'function(d){
                    d.start_date = $("#date_start").val();
                    d.end_date   = $("#date_end").val();
                    d.investors  = $("#investors").val();
                }'
            ]);
            $this->tableBuilder->parameters(
                ['aaSorting' => [],
                'columnDefs' => '[ { orderable: false, targets: [0] }]'
            ]);
            $this->tableBuilder->parameters([
                'pagingType' => 'input',
                'order'      => [[7, 'desc']]
            ]);
            $this->tableBuilder->columns(\MTB::getAllStatements(null, null, null, true));
            $investors = $this->role->allInvestors()->pluck('name', 'id');
            return ['page_title' => $page_title, 'investors' => $investors, 'tableBuilder' => $this->tableBuilder];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete_statements($request)
    {
        try {
            $id_array = $request->multi_id;
            if (!empty($id_array)) {
                foreach ($id_array as $id) {
                    $st = Statements::find($id);
                    if (Statements::destroy($id)) {
                        Storage::disk('s3')->delete($st->file_name);
                    }
                }
                $request->session()->flash('message', 'Statement Deleted Successfully!');
            }
            $msg = 'Statement delete successfully';

            return ['status' => 1, 'msg' => $msg];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function edit_admin_bank_accounts($id)
    {
        try {
            $bank_details = BankDetails::find($id);
            $page_title = 'Edit Admin Bank Accounts';
            $action = 'edit';
            return ['page_title' => $page_title, 'action' => $action, 'bank_details' => $bank_details];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function re_assign($request)
    {
        try {
            $page_title = 'Re-assign (Investment)';
            $investor_id = ($request->investor_id);
            $investors = $this->role->allLiquidityInvestors();
            $liquidity_sum = Db::table('user_details')->where('liquidity', '>', 0)->sum('liquidity');
            $investor_details = Db::table('merchant_user')
                ->where('user_id', $investor_id)
                ->whereIn('merchant_user.status', [1, 3])
                ->select(DB::raw('SUM(commission_amount) AS sum_commission_amount'), DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.under_writing_fee + merchant_user.pre_paid+merchant_user.up_sell_commission) AS total_invested_amount'), DB::raw('SUM(pre_paid) as sum_pre_paid'), DB::raw('SUM(under_writing_fee) as sum_under_writing_fee'), DB::raw('SUM(merchant_user.commission_amount) as sum_commission_amount'), DB::raw('SUM(merchant_user.amount) as sum_amount'), DB::raw('COUNT(id) as count_merchant'))
                ->first();
            return ['page_title' => $page_title, 'investor_id' => $investor_id, 'liquidity_sum' => $liquidity_sum, 'investor_details' => $investor_details, 'investors' => $investors];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function post_re_assign($request)
    {
        try {
            $lst = [];
            if ($request->balance_amount <= 0) {
                return redirect()->back()->with('error', 'No invested balance amount');
            }
            if (!$request->investor_id) {
                return redirect()->back()->with('error', 'Please select investor');
            }
            foreach ($request->amount as $key => $value) {
                if ($value['amount_per']) {
                    $lst[$key] = $value['amount_per'];
                }
            }
            if (empty($lst) || in_array(".", $lst)) {
                return redirect()->back()->with('error', 'Please Enter atleast Amount');
            }
            $investors99 = $request->amount;
            $log_investor_id = [];
            $investors = [];
            if ($investors99) {
                foreach ($investors99 as $key => $value) {
                    if ($value['amount_per']) {
                        $investors[$key]['amount_per'] = $value['amount_per'];
                        $investors[$key]['id'] = $value['investor'];
                        $log_investor_id[$key] = $key;
                    }
                }
            }
            foreach ($investors as $to_investor => $amount) {
                $this->merchant->move_invested($request->investor_id, $to_investor, $amount['amount_per']);
                InvestorHelper::update_liquidity($to_investor, 'Reassigned To New Investor');
            }
            InvestorHelper::update_liquidity($request->investor_id, 'Re-Assign');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function storeBankDetails($request)
    {
        try {
            if ($request->all()) {
                $input_arr['bank_name'] = $request->name;
                $input_arr['account_no'] = $request->acc_number;
                $status = BankDetails::create($input_arr);
                if ($status) {
                    $request->session()->flash('message', 'Bank Details Created Successfully');
                } else {
                    $request->session()->flash('error', 'Bank Details Updation Failed');
                }
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function view_bank_details()
    {
        try {
            $page_title = 'Bank Accounts';
            $this->tableBuilder->ajax(route('admin::bankdata'));
            $this->tableBuilder->parameters(
                [
                    'responsive' => true,
                    'autoWidth' => false,
                    'processing' => true,
                    'aaSorting' => [],
                    'pagingType' => 'input',
                ]
            );
            $this->tableBuilder = $this->tableBuilder->columns(
                [
                    ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false],
                    ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
                    ['data' => 'acc_no', 'name' => 'acc_no', 'title' => 'Account no'],
                    ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
                ]
            );
            return ['page_title' => $page_title, 'tableBuilder' => $this->tableBuilder];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getAdminBankaccountDetails()
    {
        try {
            $data = BankDetails::where('status', 1)->get();

            return \DataTables::collection($data)->addColumn('action', function ($data) {
                $edit = '';
                $del = '';
                if (Permissions::isAllow('Bank Details', 'Edit')) {
                    $edit = '<a href="' . route('admin::edit_bank', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                }
                if (Permissions::isAllow('Bank Details', 'Delete')) {
                    $del = Form::open(['route' => ['admin::delete_bank', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']) . Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']) . Form::close();
                }

                return $edit . $del;
            })->editColumn('created_at', function ($data) {
                return \FFM::datetime($data->created_at);
            })->editColumn('name', function ($data) {
                return $data->bank_name;
            })->editColumn('acc_no', function ($data) {
                return $data->account_no;
            })->addIndexColumn()->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updateBankDetails($request, $id)
    {
        try {
            $update = BankDetails::find($id)->update(['bank_name' => $request->name, 'account_no' => $request->acc_number]);
            if ($update) {
                $request->session()->flash('message', 'Bank Details Updated Successfully');
                return true;
            } else {
                $request->session()->flash('error', 'Bank Details Updation Failed');
                return false;
            }
        } catch (\Exception $e) {
            $request->session()->flash('error', 'Bank Details Updation Failed, ' . $e->getMessage());
            return false;
        }
    }

    public function deleteBankAccount($request, $id)
    {
        try {
            $update = BankDetails::find($id)->update(['status' => 0]);
            if ($update) {
                $request->session()->flash('message', 'Bank Account Deleted Successfully');
                return true;
            } else {
                $request->session()->flash('error', 'Bank Account Deletion Failed');
                return false;
            }
        } catch (\Exception $e) {
            $request->session()->flash('error', 'Bank Account Deletion Failed, ' . $e->getMessage());
            return false;
        }
    }

    public function percentage_deal_graph($request)
    {
        $page_title = 'Graph';
        $userId = $request->user()->id;
        $investors = [];
        $invested_amount = MerchantUser::select('user_id', DB::raw('SUM(merchant_user.amount) as invested_amount'), 'users.name')
            ->whereIn('merchant_user.status', [1, 3])
            ->leftJoin('users', 'users.id', 'merchant_user.user_id');
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        if ($permission == '') {
            $invested_amount = $invested_amount->where('users.company', $userId);
            $invested_amount = $invested_amount->groupBy('merchant_user.user_id')->get();
            foreach ($invested_amount as $key => $value) {
                $investors[$value->user_id] = $value->name . '-' . $value->invested_amount;
            }
        }
        $investor = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name', 'investor')->whereHas('company_relation', function ($query) {
            $query->where('company_status', 1);
        })->pluck('users.name', 'users.id')->toArray();
        $investors[0] = 'All';
        foreach ($investor as $key => $value) {
            $investors[$key] = $value;
        }
        $lenders = $this->role->allLenders();
        $lender[0][0] = 'All';
        foreach ($lenders as $key => $value1) {
            $lender[$value1->id][] = $value1->name;
        }
        $len = array_map('current', $lender);
        $attribute = [0 => 'Label', 1 => 'Status', 2 => 'Industry', 3 => 'Investor', 4 => 'Lenders', 5 => 'Commissions', 6 => 'Factor rate', 7 => 'State'];
        $graph_value = [0 => 'Invested Amount', 1 => 'Default Amount'];
        $states = DB::table('us_states')->select('state as name', 'id')->get();
        $result_arr = $this->invest_amount('state_id', $states);
        $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0] = 'All';
        $companies = array_reverse($companies, true);
        $labels = Label::pluck('name', 'id')->toArray();
        $labels[' '] = 'All';
        $labels = array_reverse($labels, true);

        return ['page_title' => $page_title, 'investors' => $investors, 'len' => $len, 'attribute' => $attribute, 'graph_value' => $graph_value, 'result_arr' => $result_arr, 'companies' => $companies, 'labels' => $labels];
    }

    public function invest_amount($field, $attribute_arr, $lender = '', $investor_filter = '')
    {
        $i = 0;
        $result_arr = [];
        $userId = Auth::user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestors();
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        foreach ($attribute_arr as $attribute) {
            $invested_amount = MerchantUser::where('status', 1)->whereHas('merchant', function ($query) use ($attribute, $field) {
                $query->where('active_status', 1)->where($field, $attribute->id);
                if (isset($lender) && $lender != '') {
                    $query->where('lender_id', $lender);
                }
            });
            if (Auth::user()->hasRole('company')) {
                $invested_amount = $invested_amount->whereIn('user_id', $subinvestors);
            }
            if (isset($investor_filter) && $investor_filter != '') {
                $invested_amount = $invested_amount->where('user_id', $investor_filter);
            }
            $invested_amount = $invested_amount->sum('amount');
            $result_arr[$i]['name'] = $attribute->name;
            $result_arr[$i]['amount'] = round($invested_amount, 2);
            $i++;
        }

        return $result_arr;
    }

    public function getPiechartValues($request)
    {
        try {
            if ($request->all()) {
                ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
                $lender = $request->lender;
                $investor_filter = $request->investor;
                $userId = $request->user()->id;
                $subinvestors = [];
                $date_start = '2010-01-01';
                $date_end = date('Y-m-d');
                if (isset($request->date_start) && $request->date_start != '') {
                    $date_start = $request->date_start;
                }
                if (isset($request->date_end) && $request->date_end != '') {
                    $date_end = $request->date_end;
                }
                if (empty($permission)) {
                    $investor = $this->role->allInvestors();
                    $subadmininvestor = $investor->where('company', $userId);
                    foreach ($subadmininvestor as $key1 => $value) {
                        $subinvestors[] = $value->id;
                    }
                }
                $disabled_company_investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name', 'investor')->whereHas('company_relation', function ($query) {
                    $query->where('company_status', 0);
                })->pluck('users.id')->toArray();
                $query987 = \MTB::getChartData($request->attribute, $request->graph_value, 1, $date_end);
                if ($request->user()->hasRole('company')) {
                    $query987 = $query987->whereIn('merchant_user.user_id', $subinvestors);
                }
                if (isset($lender) && $lender) {
                    $query987 = $query987->where('merchants.lender_id', $lender);
                }
                if (isset($request->label) && $request->label != '') {
                    $query987 = $query987->where('merchants.label', $request->label);
                }
                if (isset($investor_filter) && $investor_filter) {
                    $query987 = $query987->whereIn('merchant_user.user_id', $investor_filter);
                } else {
                    $query987 = $query987->whereNotIn('merchant_user.user_id', $disabled_company_investors);
                }
                if (isset($request->label) && $request->label != '') {
                    $query987 = $query987->where('merchants.label', $request->label);
                }
                if ($request->date_start != '' || $request->date_end != '') {
                    $query987 = $query987->whereBetween('merchants.date_funded', [$date_start, $date_end]);
                }
                $result_arr = ($query987->get())->toArray();
                array_multisort(array_column($result_arr, 'name'), SORT_ASC, $result_arr);
                if ($request->attribute == 6) {
                    $dd = $a = [];
                    foreach ($result_arr as $key => $val) {
                        $name = round($result_arr[$key]->name, 2);
                        $amount = $result_arr[$key]->amount;
                        for ($x = 0; $x <= 2; $x = $x + 0.05) {
                            if ($x > $name) {
                                if (in_array($x, $a)) {
                                    $dd["$x"] = $dd["$x"] + $amount;
                                } else {
                                    $dd["$x"] = $amount;
                                    array_push($a, $x);
                                }
                                break;
                            }
                        }
                    }
                    foreach ($dd as $key => $val) {
                        $ff[] = ['name' => ($key - .05) . " to $key", 'amount' => round($val, 2)];
                    }
                    $result_arr = $ff;
                }

                return $result_arr;
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function downloadPiechartValues($request)
    {
        try {
            if ($request->all()) {
                ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
                $lender = $request->lenders;
                $investor_filter = $request->invested_amount;
                $userId = $request->user()->id;
                $subinvestors = [];
                $date_start = '2010-01-01';
                $date_end = date('Y-m-d');
                if (isset($request->date_start) && $request->date_start != '') {
                    $date_start = $request->date_start;
                }
                if (isset($request->date_end) && $request->date_end != '') {
                    $date_end = $request->date_end;
                }
                if (empty($permission)) {
                    $investor = $this->role->allInvestors();
                    $subadmininvestor = $investor->where('company', $userId);
                    foreach ($subadmininvestor as $key1 => $value) {
                        $subinvestors[] = $value->id;
                    }
                }
                $disabled_company_investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name', 'investor')->whereHas('company_relation', function ($query) {
                    $query->where('company_status', 0);
                })->pluck('users.id')->toArray();
                $query987 = \MTB::getChartData($request->attribute, $request->graph_value, 2);
                if ($request->user()->hasRole('company')) {
                    $query987 = $query987->whereIn('merchant_user.user_id', $subinvestors);
                }
                if (isset($lender) && $lender) {
                    $query987 = $query987->where('merchants.lender_id', $lender);
                }
                if (isset($request->label) && $request->label != '') {
                    $query987 = $query987->where('merchants.label', $request->label);
                }
                if (isset($investor_filter) && $investor_filter) {
                    $query987 = $query987->whereIn('merchant_user.user_id', $investor_filter);
                } else {
                    $query987 = $query987->whereNotIn('merchant_user.user_id', $disabled_company_investors);
                }
                if (isset($request->label) && $request->label != '') {
                    $query987 = $query987->where('merchants.label', $request->label);
                }
                if ($request->date_start != '' || $request->date_end != '') {
                    $query987 = $query987->whereBetween('merchants.date_funded', [$date_start, $date_end]);
                }
                $result_arr = ($query987->get())->toArray();
                array_multisort(array_column($result_arr, 'name'), SORT_ASC, $result_arr);
                for ($i = 0; $i < count($result_arr); $i++) {
                    if (($result_arr[$i]->name === 0.0)) {
                        $result_arr[$i]->name = '0';
                    }
                }
                if (empty($result_arr)) {
                    return redirect()->back()->with(['message' => 'No Records']);
                }
                if (($result_arr[0]->name === 0.0) || ($result_arr[0]->name === 0)) {
                    $result_arr[0]->name = '0';
                }
                if ($request->attribute == 8 || $request->attribute == 6 || $request->attribute == 5) {
                    $header = [0 => ['Total', ''], 1 => ['Average', ''], 2 => ['', ''], 3 => ['Name', 'Amount']];
                } else {
                    $header = [0 => ['Total', ''], 1 => ['Average', ''], 2 => ['', ''], 3 => ['', 'Name', 'Amount']];
                }
                if ($request->attribute == 6) {
                    $dd = $a = [];
                    foreach ($result_arr as $key => $val) {
                        $name = round($result_arr[$key]->name, 2);
                        $amount = $result_arr[$key]->amount;
                        for ($x = 0; $x <= 2; $x = $x + 0.05) {
                            if ($x > $name) {
                                if (in_array($x, $a)) {
                                    $dd["$x"] = $dd["$x"] + $amount;
                                } else {
                                    $dd["$x"] = $amount;
                                    array_push($a, $x);
                                }
                                break;
                            }
                        }
                    }
                    foreach ($dd as $key => $val) {
                        $ff[] = ['name' => ($key - .05) . " to $key", 'amount' => round($val, 2)];
                    }
                    $result_arr = $ff;
                }
                array_unshift($result_arr, $header);
                $export = new Merchant_Graph($result_arr, count($result_arr), $request->attribute);

                return $export;
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function default_amount($field, $attribute_arr, $lender = '', $investor = '')
    {
        try {
            $i = 0;
            $result_arr = [];
            foreach ($attribute_arr as $attribute) {
                $ctd_def = ParticipentPayment::query();
                $ctd_def = $ctd_def->whereHas('merchant', function ($query) use ($attribute, $field) {
                    $query->where('sub_status_id', '=', 4)->where('active_status', '=', 1)->where($field, $attribute->id);
                    if (isset($lender)) {
                        $query->where('lender_id', $lender);
                    }
                });
                if (isset($investor)) {
                    $ctd_def = $ctd_def->where('user_id', $investor);
                }
                $ctd_defs = $ctd_def->sum('final_participant_share');
                $total_invested_def = MerchantUser::where('status', 1)->whereHas('merchant', function ($query) use ($attribute, $field) {
                    $query->where('active_status', 1)->where('sub_status_id', 4)->where($field, $attribute->id);
                    if (isset($lender)) {
                        $query->where('lender_id', $lender);
                    }
                });
                if (isset($investor)) {
                    $total_invested_def = $total_invested_def->where('user_id', $investor);
                }
                $total_invested_def = $total_invested_def->sum('amount') + $total_invested_def->sum('commission_amount') + $total_invested_def->sum('pre_paid');
                $result_arr[$i]['name'] = $attribute->name;
                $result_arr[$i]['amount'] = round($total_invested_def - $ctd_defs, 2);
                $i++;
            }
            return $result_arr;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function view_roles()
    {
        try {
            $page_title = 'Roles and Permissions';
            $this->tableBuilder->ajax(route('admin::admins::roledata'));
            $this->tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
            $this->tableBuilder = $this->tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'data' => 'id', 'name' => 'id', 'defaultContent' => '', 'title' => '#'], ['data' => 'name', 'name' => 'name', 'title' => 'Name'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false],['data' => 'two_factor_mandatory', 'name' => 'two_factor_mandatory', 'title' => 'Mandatory Two Factor Authentication', 'orderable' => false]]);
            return ['page_title' => $page_title, 'tableBuilder' => $this->tableBuilder];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getallRolesData()
    {
        try {
            return \DataTables::of(DB::table('roles'))->addColumn('action', function ($data) {
                $return = '';
                if (Permissions::isAllow('Permissions', 'Edit')) {
                    $return .= '<a href="' . route('admin::roles::edit-permissions', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Permissions</a>';
                }

                return $return;
            })->editColumn('updated_at', function ($data) {
                return \FFM::datetime($data->updated_at);
            })->addColumn('two_factor_mandatory', function ($data) {
                $return = '';
                $two_factor_required_mode = ($data->two_factor_required==1) ? true :false;
                //if($data->name=='admin' || $data->name=='investor' || $data->name=='editor' ||$data->name=='accounts' || $data->name=='company' || $data->name=='collection_user' || $data->name=='merchant'){
                //$return .= '<div class="row">
                $return .='<div class="col-sm-3">';
                if($data->two_factor_required==1){
                    $return .= '<input type="checkbox" if($two_factor_required_mode==true) checked @endif data-toggle="toggle" data-on="Yes" data-off="No" name="two_factor_required_status" id="two_factor_required_status'.$data->id.'" data-title="" onChange="updateTwoFactorStatus('.$data->id.');">'; 
                }else{
                    $return .= '<input type="checkbox"  data-toggle="toggle" data-on="Yes" data-off="No" name="two_factor_required_status" id="two_factor_required_status'.$data->id.'" data-title="" onChange="updateTwoFactorStatus('.$data->id.');">';   
                }
                $return .= '<div class="form-group collection-mode">
                    </div>
                </div>';
               // }
               

                return $return;
            })->rawColumns(['two_factor_mandatory', 'action'])->addIndexColumn()->make(true);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function copyPermission($request)
    {
        try {
            $s_role_id = $request->s_role_id;
            $c_role_id = $request->c_role_id;
            $roles_m = Role_module::where('role_id', $s_role_id)->get()->toArray();
            $array = [];
            if (!empty($roles_m)) {
                $roles_al = Role_module::where('role_id', $c_role_id)->count();
                if ($roles_al > 0) {
                    Role_module::where('role_id', $c_role_id)->each(function ($row) {
                        $row->delete();
                    });
                }
                foreach ($roles_m as $key => $value) {
                    $array[$key]['module_id'] = $value['module_id'];
                    $array[$key]['permission_id'] = $value['permission_id'];
                    $array[$key]['role_id'] = $c_role_id;
                }
                Role_module::insert($array);

                return ['status' => 1, 'msg' => 'Role permissions successfully'];
            } else {
                return ['status' => 0, 'msg' => 'role permissions not available'];
            }
        } catch (\Exception $e) {
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }

    public function copyPermissionToUser($request)
    {
        try {
            $s_role_id = $request->s_role_id;
            $c_user_id = $request->c_role_id;
            $roles_m = Role_module::where('role_id', $s_role_id)->get()->toArray();
            $array = [];
            if (!empty($roles_m)) {
                $roles_al = Role_module::where('user_id', $c_user_id)->count();
                if ($roles_al > 0) {
                    Role_module::where('user_id', $c_user_id)->each(function ($row) {
                        $row->delete();
                    });
                }
                foreach ($roles_m as $key => $value) {
                    $array[$key]['module_id'] = $value['module_id'];
                    $array[$key]['permission_id'] = $value['permission_id'];
                    $array[$key]['user_id'] = $c_user_id;
                }
                Role_module::insert($array);

                return ['status' => 1, 'msg' => 'Role permissions successfully'];
            } else {
                return ['status' => 0, 'msg' => 'role permissions not available'];
            }
        } catch (\Exception $e) {
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }

    public function editPermissions($id)
    {
        try {
            $iid = $id;
            $roles = DB::table('roles')->pluck('name', 'id')->toArray();
            $role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';
            $id = ($role_id) ? $role_id : $id;
            $page_title = 'Permissions';
            $action = 'edit';
            $role = Role::where('id', $iid)->first();
            $modules = Module::all();
            $module_ids = Module::pluck('id')->toArray();
            $checkvalues = [];
            foreach ($modules as $m) {
                $permissions = Permission::all();
                foreach ($permissions as $p) {
                    $res = Role_module::select(['permission_id', 'module_id'])->where('role_id', $id)->where('module_id', $m->id)->where('permission_id', $p->id)->first();
                    if (isset($res->permission_id)) {
                        $checkvalues[] = ['name' => $p->name, 'pid' => $p->id, 'mid' => $m->id, 'p_name' => $p->name, 'module_name' => $m->name, 'status' => 'yes'];
                    } else {
                        $checkvalues[] = ['name' => $p->name, 'pid' => $p->id, 'mid' => $m->id, 'p_name' => $p->name, 'module_name' => $m->name, 'status' => 'no'];
                    }
                }
            }
            return ['page_title' => $page_title, 'action' => $action, 'role' => $role, 'modules' => $modules, 'permissions' => $permissions, 'checkvalues' => $checkvalues, 'module_ids' => $module_ids, 'roles' => $roles];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function editUserPermissions($id)
    {
        try {
            $iid = $id;
            $roles = DB::table('roles')->pluck('name', 'id')->toArray();
            $role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';
            $id = ($role_id) ? $role_id : $id;
            $page_title = 'Permissions';
            $action = 'edit';
            $user = User::where('id', $iid)->first();
            $modules = Module::all();
            $module_ids = Module::pluck('id')->toArray();
            $checkvalues = [];
            foreach ($modules as $m) {
                $permissions = Permission::all();
                foreach ($permissions as $p) {
                    $res = Role_module::select(['permission_id', 'module_id'])->where('user_id', $iid)->where('module_id', $m->id)->where('permission_id', $p->id)->first();
                    if (isset($res->permission_id)) {
                        $checkvalues[] = ['name' => $p->name, 'pid' => $p->id, 'mid' => $m->id, 'p_name' => $p->name, 'module_name' => $m->name, 'status' => 'yes'];
                    } else {
                        $checkvalues[] = ['name' => $p->name, 'pid' => $p->id, 'mid' => $m->id, 'p_name' => $p->name, 'module_name' => $m->name, 'status' => 'no'];
                    }
                }
            }
            return ['page_title' => $page_title, 'action' => $action, 'user' => $user, 'modules' => $modules, 'permissions' => $permissions, 'checkvalues' => $checkvalues, 'module_ids' => $module_ids, 'roles' => $roles];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getallUserRolesData($request)
    {
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        return \DataTables::of($this->role->allUserRoleDataTable($request->roles))->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Users', 'Edit')) {
                $return .= '<a href="' . route('admin::roles::edit-role-user', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                $return .= '<a href="' . route('admin::roles::edit-user-permissions', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Permissions</a>';
                if (Permissions::isAllow('Firewall', 'Edit')) {
                    $return .= '<a href="' . route('admin::firewall::view', ['id' => $data->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Add IP</a>';
                }
            }

            return $return;
        })->addColumn('createdat', function ($data) use ($request) {
            if ($request->roles == 6 && !isset($data->creator_id)) {
                $creator_ = 'system';
            } else {
                $creator_ = get_user_name_with_session($data->creator_id);
            }
            $created_date = 'Created On ' . \FFM::datetime($data->created_at) . ' by ' . $creator_;

            return "<a title='$created_date'>" . \FFM::datetime($data->created_at) . '</a>';
        })->editColumn('updated_at', function ($data) {

            return \FFM::datetime($data->updated_at);
        })->addIndexColumn()->rawColumns(['createdat', 'updated_at', 'action'])->make(true);
    }

    public function editRoleUser($id, $lender)
    {
        try {
            $page_title = 'Edit User Role';
            $action = 'edit';
            $role_count = User::where('users.id', $id)->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('role_id', User::MERCHANT_ROLE)->count();
            $roles = Role::all();
            return ['page_title' => $page_title, 'lender' => $lender, 'action' => $action, 'roles' => $roles, 'role_count' => $role_count];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function create_liquidity_adjuster($id)
    {
        try {
            $page_title = 'Edit Liquidity Adjuster';
            $action = 'create';
            $users = UserDetails::where('user_id', $id)->first();
            return ['page_title' => $page_title, 'action' => $action, 'users' => $users, 'id' => $id];
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
