<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\Admin\GitpullAdminUserRequest;
use App\Http\Requests\Admin\RunCommandsAdminUserRequest;
use App\Library\Repository\Interfaces\IAdminUserRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Maatwebsite\Excel\Facades\Excel;

class AdminUserController extends Controller
{
    protected $role;
    protected $user;

    public function __construct(IAdminUserRepository $AdminUser, IRoleRepository $role, IUserRepository $user)
    {
        $this->role = $role;
        $this->user = $user;
        $this->AdminUser = $AdminUser;
    }

    public function index()
    {
        $returnData   = $this->AdminUser->index();
        return view('admin.admins.index', $returnData);
    }

    public function fullcalender()
    {
        $title = 'Calender For Holidays';
        return view('admin.fullcalender', compact('title'));
    }

    public function resetDbAction(Request $request)
    {
        try {
            $this->AdminUser->resetDbAction($request);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Failed', 'status' => 0]);
        }
    }

    public function changeDbAction(Request $request)
    {
        $data = $this->AdminUser->changeDbAction($request);
        return response()->json($data);
    }

    public function duplicateDb(Request $request)
    {
        $return = $this->AdminUser->duplicateDb($request);
        return view('admin.duplicate_db', $return);
    }

    public function duplicateDbAction(Request $request)
    {
        $this->user->duplicateDbGenerate($request);
        return response()->json(['msg' => 'success', 'status' => 1]);
    }

    public function getMerchants(Request $request)
    {
        $data = $this->AdminUser->getMerchants($request);
        return response()->json($data);
    }

    public function getCompanies(Request $request)
    {
        $data = $this->AdminUser->getCompanies($request);
        return response()->json($data);
    }

    public function getInvestorAdmins(Request $request)
    {
        $data = $this->AdminUser->getInvestorAdmins($request);
        return response()->json($data);
    }

    public function create_liquidity_adjuster(Request $request, $id)
    {
        $data = $this->AdminUser->create_liquidity_adjuster($id);
        return view('admin.create_liquidity_adjuster', $data);
    }

    public function getLiquidityAdjuster()
    {
        $returnData   = $this->AdminUser->getLiquidityAdjuster();
        return view('admin.liquidity_adjuster', $returnData);
    }

    public function rowDataLiquidityAdjuster()
    {
        return $this->AdminUser->rowDataLiquidityAdjuster();
    }

    public function view_user_roles()
    {
        $returnData = $this->AdminUser->view_user_roles();
        return view('admin.admins.view_user_roles', $returnData);
    }

    public function getCompanyWiseInvestors(Request $request)
    {
        $data = $this->AdminUser->getCompanyWiseInvestors($request);
        return response()->json($data);
    }

    public function getMerchantsForAgentFee()
    {
        $data = $this->AdminUser->getMerchantsForAgentFee();
        return response()->json($data);
    }

    public function getAssignedInvestors(Request $request)
    {
        $data = $this->AdminUser->getAssignedInvestors($request);
        return response()->json($data);
    }

    public function getInvestorsforOwner(Request $request)
    {
        $data = $this->AdminUser->getInvestorsforOwner($request);
        return response()->json($data);
    }

    public function getAllInvestors()
    {
        $data = $this->AdminUser->getAllInvestors();
        return response()->json($data);
    }

    public function getInvestors(Request $request)
    {
        $data = $this->AdminUser->getInvestors($request);
        return response()->json($data);
    }

    public function getInvestorAdmin()
    {
        $allsubadmin = $this->role->allSubAdmin();
        $data = ['total_count' => '', 'incomplete_results' => true, 'items' => $allsubadmin];
        return response()->json($data);
    }

    public function create()
    {
        $page_title = 'Create New Admin User';
        $action = 'create';
        return view('admin.admins.create', compact('page_title', 'action'));
    }

    public function create_lenders()
    {
        $data = $this->AdminUser->create_lenders();
        return view('admin.admins.create_lender', $data);
    }

    public function editLenders($id, Request $request)
    {
        $Lender = $this->user->findUser($id, 'lender');
        if (!$Lender) {
            $request->session()->flash('error', 'Invalid Lender Id');
            return redirect(route("admin::lenders::show_lenders"));
        } else {
            $arrayData = $this->AdminUser->editLenders($Lender);
            return view('admin.admins.create_lender', $arrayData);
        }
    }

    public function updateLenders(Requests\AdminUpdateLenderRequest $request, $id)
    {
        try {
            $request_arr = $request->all();
            $request_arr['underwriting_status'] = json_encode($request->underwriting_status, true);
            if ($this->user->updateUser($id, $request, 'lender')) {
                $request->session()->flash('message', 'Lender updated!');
            }

            return redirect()->route('admin::lenders::view_lender', ['id' => $id]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function viewLenders($id, Request $request)
    {
        $Lender = $this->user->findUser($id, 'lender');
        if (!$Lender) {
            $request->session()->flash('error', 'Invalid Lender Id');
            return redirect(route('admin::lenders::show_lenders'));
        } else {
            $arrayData = $this->AdminUser->viewLenders($Lender);
            return view('admin.admins.view_lender', $arrayData);
        }
    }

    public function saveLiquidityAdjuster(Request $request)
    {
        try {
            if ($this->user->createLiquidityAdjuster($request)) {
                $request->session()->flash('message', ' Liquidity Adjuster updated!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect()->back();
    }

    public function saveLenderData(Requests\AdminCreateAdminUserRequest $request)
    {
        try {
            $lenderSave = $this->user->createLenderUsers($request);
            if ($lenderSave) {
                $request->session()->flash('message', 'New Lender Created.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect()->route('admin::lenders::view_lender', ['id' => $lenderSave->id]);
    }

    public function view_lenders()
    {
        $arrData = $this->AdminUser->view_lenders();
        return view('admin.admins.view_lenders', $arrData);
    }

    public function storeCreate(Requests\AdminCreateAdminUserRequest $request)
    {
        try {
            if ($this->user->createAdminUsers($request)) {
                $request->session()->flash('message', 'New Admin User Created.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
        return redirect()->back();
    }

    public function edit($id)
    {
        try {
            if ($admin = $this->user->findAdminUsers($id)) {
                $page_title = 'Edit Admin user';
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
        $action = 'edit';
        return view('admin.admins.create', compact('page_title', 'admin', 'action'));
    }

    public function update(Requests\AdminUpdateAdminUserRequest $request, $id)
    {
        try {
            if ($this->user->updateAdminUsers($id, $request)) {
                $request->session()->flash('message', 'Admin user updated!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }

        return redirect()->back();
    }

    public function delete(Request $request, $id)
    {
        try {
            if ($this->user->deleteAdminUsers($id)) {
                if ($this->AdminUser->delete()) {
                    return redirect()->back();
                } else {
                    return redirect()->to('admin/admin/')->withErrors('Cannot Delete Admin!');
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function rowData()
    {
        return $this->AdminUser->rowData();
    }

    public function change_advanced_status()
    {
        $page_title = 'Change To Advance completed Status';
        return view('admin.change_advanced_status', compact('page_title'));
    }

    public function changeAdvancedStatusAction()
    {
        $arrData = $this->AdminUser->rowData();
        return response()->json($arrData);
    }

    public function advanced_status_check(Request $request)
    {
        $arrData = $this->AdminUser->advanced_status_check($request);
        return response()->json($arrData);
    }

    public function getallLenders()
    {
        return $this->AdminUser->getallLenders();
    }

    public function deleteUsers(Request $request, $id, $type)
    {
        $this->AdminUser->deleteUsers($request, $id, $type);
        return redirect()->back();
    }

    public function create_editors()
    {
        $page_title = 'Create New Editor';
        $action = 'create';
        return view('admin.admins.create_editor', compact('page_title', 'action'));
    }

    public function create_viewers()
    {
        $page_title = 'Create New Viewer';
        $action = 'create';
        return view('admin.admins.create_viewer', compact('page_title', 'action'));
    }

    public function saveEditorData(Requests\AdminCreateAdminUserRequest $request)
    {
        try {
            if ($this->user->createEditorUsers($request)) {
                $request->session()->flash('message', 'New Editor Created Successfully.');
            }
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function view_editors()
    {
        $arrData = $this->AdminUser->view_editors();
        return view('admin.admins.view_editors', $arrData);
    }

    public function view_viewers()
    {
        $arrData = $this->AdminUser->view_viewers();
        return view('admin.admins.view_viewers', $arrData);
    }

    public function saveViewerData(Requests\AdminCreateAdminUserRequest $request)
    {
        try {
            if ($this->user->createViewerUsers($request)) {
                $request->session()->flash('message', 'New Viewer Created Successfully.');
            }
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getallViewersData()
    {
        return $this->AdminUser->getallViewersData();
    }

    public function getallEditorsData()
    {
        return $this->AdminUser->getallEditorsData();
    }

    public function editEditors($id)
    {
        try {
            if ($lender = $this->user->findUser($id, 'editor')) {
                $page_title = 'Edit Editor';
                $action = 'edit';

                return view('admin.admins.create_editor', compact('page_title', 'lender', 'action'));
            }

            return redirect()->route('admin::dashboard::admins');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function editViewers($id)
    {
        try {
            if ($lender = $this->user->findUser($id, 'viewer')) {
                $page_title = 'Edit Viewer';
                $action = 'edit';

                return view('admin.admins.create_viewer', compact('page_title', 'lender', 'action'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete_editors(Request $request, $id, $type)
    {
        if (!$this->AdminUser->delete_editors($request, $id, $type))
            throw new \Exception('Somthing Went Wrong');
        return redirect()->back();
    }

    public function delete_viewers(Request $request, $id, $type)
    {
        if (!$this->AdminUser->delete_viewers($request, $id, $type))
            throw new \Exception('Somthing Went Wrong');
        return redirect()->back();
    }

    public function updateEditor(Requests\AdminUpdateAdminUserRequest $request, $id)
    {
        try {
            if ($this->user->updateUser($id, $request, 'editor')) {
                $request->session()->flash('message', 'Editor updated!');
            }
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updateViewer(Requests\AdminUpdateAdminUserRequest $request, $id)
    {
        try {
            if ($this->user->updateUser($id, $request, 'viewer')) {
                $request->session()->flash('message', 'Viewer updated!');
            }
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function permissionDenied()
    {
        return view('admin.permission_denied');
    }

    public function getLenderManagementAndSyndFee(Request $request)
    {
        $data = $this->AdminUser->getLenderManagementAndSyndFee($request);
        return response()->json($data);
    }

    public function getInvestorManagementAndSyndFee(Request $request)
    {
        $data = $this->AdminUser->getInvestorManagementAndSyndFee($request);
        return response()->json($data);
    }
    public function getMerchantFee(Request $request){
        $data = $this->AdminUser->getMerchantFee($request);
        return response()->json($data);
    }

    public function enable_disable_lender(Request $request)
    {
        $page_title = 'Enable/Disable';
        $lenders = $this->role->enabledDisabledLenders();
        return view('admin.lender_enable_disable', compact('page_title', 'lenders'));
    }

    public function updateLenderEnableDisable(Request $request)
    {
        $data = $this->AdminUser->updateLenderEnableDisable($request);
        return response()->json($data);
    }

    public function change_merchant_status()
    {
        $page_title = 'Change Merchant Status';
        return view('admin.change_merchant_status', compact('page_title'));
    }

    public function merchant_status_change(Request $request)
    {
        $data = $this->AdminUser->merchant_status_change($request);
        return response()->json($data);
    }

    public function merchantStatusCheckAction(Request $request)
    {
        $data = $this->AdminUser->merchantStatusCheckAction($request);
        return response()->json($data);
    }

    public function merchant_status_log(Request $request)
    {
        $sDate = !empty($request->start_date) ? $request->start_date : '';
        $eDate = !empty($request->end_date) ? $request->end_date : '';
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::getMerchantStatusLog($sDate, $eDate, $request->merchants, $request->status_id);
        }
        $data = $this->AdminUser->merchant_status_log($request);
        return view('admin.merchant_status_log', $data);
    }

    public function generatedPdfForInvestors()
    {
        $data = $this->AdminUser->generatedPdfForInvestors();
        return view('admin.pdf_generation', $data);
    }

    public function generatePdfPreview(Request $request)
    {
        $data = $this->AdminUser->generatePdfPreview($request);
        return response()->json($data);
    }

    public function send_mail_to_investors(Request $request)
    {
        $data = $this->AdminUser->send_mail_to_investors($request);
        return response()->json($data);
    }

    public function sendMailToInvestor(Request $request)
    {
        $data = $this->AdminUser->sendMailToInvestor($request);
        return response()->json($data);
    }

    public function sendInvestorPortal(Request $request)
    {
        $data = $this->AdminUser->sendInvestorPortal($request);
        return response()->json($data);
    }

    public function generatedFileLoader($file)
    {
        return $this->AdminUser->generatedFileLoader($file);
    }

    public function generatedCsvPdfManager(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::getAllStatements($request->start_date, $request->end_date, $request->investors);
        }
        $data = $this->AdminUser->generatedCsvPdfManager($request);
        return view('admin.generatedPdfCsvManager', $data);
    }

    public function delete_statements(Request $request)
    {
        $data = $this->AdminUser->delete_statements($request);
        return response()->json($data);
    }

    public function admin_bank_accounts()
    {
        $page_title = 'Admin Bank Accounts';
        $action = 'create';
        return view('admin.admins.bank_details', compact('page_title', 'action'));
    }

    public function edit_admin_bank_accounts($id)
    {
        $data = $this->AdminUser->delete_statements($id);
        return view('admin.admins.bank_details', $data);
    }

    public function re_assign(Request $request)
    {
        $data = $this->AdminUser->re_assign($request);
        return view('admin.re_assign', $data);
    }

    public function post_re_assign(Request $request)
    {
        $data = $this->AdminUser->post_re_assign($request);
        if ($data) {
            return redirect()->back()->with('message', 'successfully reassigned');
        } else {
            return redirect()->back()->with('message', 'Somthing Went Wrong');
        }
    }

    public function storeBankDetails(Requests\AdminManageBankRequest $request)
    {
        $data = $this->AdminUser->post_re_assign($request);
        if ($data) {
            return redirect()->back();
        } else {
            return redirect()->back()->withErrors('Somthing Went Wrong');
        }
    }

    public function view_bank_details()
    {
        $data = $this->AdminUser->view_bank_details();
        return view('admin.admins.view_bank_accounts', $data);
    }

    public function getAdminBankaccountDetails()
    {
        return $this->AdminUser->getAdminBankaccountDetails();
    }

    public function updateBankDetails(Requests\AdminManageBankRequest $request, $id)
    {
        try {
            if (!$this->AdminUser->updateBankDetails($request, $id)) throw new \Exception('Somthing Went Wrong');
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function deleteBankAccount(Request $request, $id)
    {
        if (!$this->AdminUser->deleteBankAccount($request, $id)) throw new \Exception('Somthing Went Wrong');
        return redirect()->back();
    }

    public function percentage_deal_graph(Request $request)
    {
        $data = $this->AdminUser->percentage_deal_graph($request);
        return view('admin.percentage_deal_graph', $data);
    }

    public function getPiechartValues(Request $request)
    {
        $data = $this->AdminUser->getPiechartValues($request);
        return response()->json($data);
    }

    public function downloadPiechartValues(Request $request)
    {
        $export = $this->AdminUser->downloadPiechartValues($request);
        return Excel::download($export, 'merchant_graph.xlsx');
    }

    public function default_amount($field, $attribute_arr, $lender = '', $investor = '')
    {
        return $this->AdminUser->default_amount($field, $attribute_arr, $lender = '', $investor = '');
    }

    public function view_roles()
    {
        $data = $this->AdminUser->view_roles();
        return view('admin.admins.view_roles', $data);
    }

    public function getallRolesData()
    {
        return $this->AdminUser->getallRolesData();
    }

    public function copyPermission(Request $request)
    {
        $data = $this->AdminUser->copyPermission($request);
        return response()->json($data);
    }
    public function copyPermissionToUser(Request $request)
    {
        $data = $this->AdminUser->copyPermissionToUser($request);
        return response()->json($data);
    }

    public function editPermissions($id)
    {
        $data = $this->AdminUser->editPermissions($id);
        return view('admin.admins.view_role_permissions', $data);
    }

    public function editUserPermissions($id)
    {
        $data = $this->AdminUser->editUserPermissions($id);
        return view('admin.admins.view_user_permissions', $data );
    }

    public function updateRole(Request $request, $id)
    {
        try {
            if ($this->user->updateModulePerm($id, $request)) {
                return redirect('/admin/role/edit/' . $id)->with('message', 'Permissions updated');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updateRoleUser(Request $request, $id)
    {
        try {
            if ($this->user->updateModulePermUser($id, $request)) {
                return redirect('/admin/role/user-user-permissions/edit/' . $id)->with('message', 'Permissions updated');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getallUserRolesData(Request $request)
    {
        return $this->AdminUser->getallUserRolesData($request);
    }

    public function editRoleUser($id, Request $request)
    {
        try {
            $Lender = $this->user->findUserRole($id);
            if (!$Lender) {
                $request->session()->flash('error', 'Invalid Id');
                return redirect(route("admin::roles::show-user-role"));
            } else {
                $data = $this->AdminUser->editRoleUser($id, $Lender);
                return view('admin.admins.create_user', $data);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function create_users()
    {
        $data = $this->AdminUser->create_users();
        return view('admin.admins.create_user', $data);
    }

    public function saveUserRoleData(Requests\AdminCreateAdminUserRequest $request)
    {
        try {
            if ($this->user->createRoleUsers($request)) {
                $request->session()->flash('message', 'New User Created Successfully.');
            }
            return redirect()->route('admin::roles::show-user-role');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updateUserRole(Requests\AdminUpdateAdminUserRequest $request, $id)
    {
        try {
            $msg = '';
            if ($this->user->updateUserRole($id, $request, 'viewer')) {
                if ($request->email_notification == 1) {
                    $msg .= ' <br> A reset link has been sent to merchant email address.';
                }
                $request->session()->flash('message', 'User updated!' . $msg);
            }
            return redirect()->route('admin::roles::show-user-role');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function gitpull()
    {
        return view('admin.others.create');
    }

    public function Postgitpull(GitpullAdminUserRequest $request)
    {
        $this->AdminUser->Postgitpull($request);
    }

    public function run_commands(RunCommandsAdminUserRequest $request)
    {
        $this->AdminUser->run_commands($request);
    }

    public function view_modules()
    {
        $data = $this->AdminUser->view_modules();
        return view('admin.admins.view_user_roles', $data);
    }

    public function getModuleData()
    {
        return $this->AdminUser->getModuleData();
    }

    public function create_modules()
    {
        $page_title = 'Create New Module';
        $action = 'create';
        return view('admin.admins.create_module', compact('page_title', 'action'));
    }
    public function edit_modules($id, Request $request)
    {
        $page_title = 'Edit Module';
        $module = $this->AdminUser->edit_modules($id);
        if (!$module) {
            $request->session()->flash('error', 'Invalid Module Id');
            return redirect(route("admin::roles::show-modules"));
        }
        $action = 'edit';
        return view('admin.admins.create_module', compact('page_title', 'action', 'module'));
    }

    public function updateModuleData(Requests\AdminModules $request, $id)
    {
        try {
            if (!($this->AdminUser->updateModuleData($request, $id))) throw new \Exception('Somthing Went Wrong');
            $request->session()->flash('message', 'Module Updated!');
            return redirect()->route('admin::roles::show-modules');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function saveModuleData(Requests\AdminModules $request)
    {
        try {
            $request->session()->flash('message', 'Module Created!');
            if (!($this->AdminUser->saveModuleData($request))) throw new \Exception('Somthing Went Wrong');
            return redirect()->route('admin::roles::show-modules');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete_module(Request $request, $id, $type)
    {
        if ($this->AdminUser->delete_module($request, $id, $type)) {
            return redirect()->back();
        } else {
            return redirect()->back()->withErrors('Something Went Wrong');
        }
    }

    public function create_roles()
    {
        $page_title = 'Create New Role';
        $action = 'create';
        return view('admin.admins.create_role', compact('page_title', 'action'));
    }

    public function saveRoleData(Requests\AdminRoles $request)
    {
        try {
            if ($this->AdminUser->saveRoleData($request)) {
                $request->session()->flash('message', 'Role Created!');
            }
            return redirect()->route('admin::roles::show-role');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function twoFactorAuthSettings(Request $request)
    {
        $page_title = 'Two Factor Authentication';
        $this->AdminUser->twoFactorAuthSettings($request);
        return view('admin.two_factor_authentication', compact('page_title'));
    }

    public function postTwoFactorAuthSettings(Request $request, DisableTwoFactorAuthentication $disable)
    {
        if ($this->AdminUser->postTwoFactorAuthSettings($request, $disable)) {
            return redirect()->to('/admin/save-recovery-key');
        } else {
            return redirect()->back()->with('error', 'Invalid code');
        }
    }

    public function saveRecoveryKey()
    {
        $page_title = 'Recovery Keys';
        return view('admin.save_recovery_key', compact('page_title'));
    }

    public function enableTwoFactorAuth(Request $request, EnableTwoFactorAuthentication $enable, DisableTwoFactorAuthentication $disable)
    {
        $page_title = 'Two Factor Authentication';
        $qrcode = $this->AdminUser->enableTwoFactorAuth($request, $enable, $disable);
        return view('admin.enable_two_factor_auth', compact('page_title', 'qrcode'));
    }

    public function recoveryKeyPdfView()
    {
        $pdf = $this->AdminUser->recoveryKeyPdfView();
        $filePDFName = 'recovery-key.pdf';
        return $pdf->stream($filePDFName);
    }
    public function getRoleUsers(Request $request)
    {
        return $this->role->getInvestorsFromCompany($request->company, $request->role_id,$request->velocity_owned)->pluck('id');
    }
    public function updateTwoFactorMandatoryStatus(Request $request){
        $data = $this->AdminUser->updateRoleWiseTwoFactorStatus($request);
        return response()->json($data);
    }
}
