<?php
/**
* Created by Rahees.
* User: rahees_iocod
* Date: 13/11/20
* Time: 1:15 AM.
*/
namespace App\Library\Repository;
use App\Library\Repository\Interfaces\IInvestorRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Models\Views\InvestorAchRequestView;
use Illuminate\Support\Facades\Auth;
use App\UserDetails;
use App\Helpers\ActumRequest;
use App\MerchantUser;
use App\InvestorTransaction;
use App\PaymentInvestors;
use App\ParticipentPayment;
use App\Models\InvestorAchRequest;
use App\Settings;
use App\Models\Views\InvestorAchTransactionView;
use App\LiquidityLog;
use App\Statements;
use App\User;
use App\CarryForward;
use App\SubStatus;
use App\Template;
use App\Merchant;
use App\ReassignHistory;
use App\Bank;
use PayCalc;
use App\Models\Views\MerchantUserView;
use App\Models\Views\PaymentInvestorsView;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;
use App\Jobs\CommonJobs;
use DB;
use MTB;
use FFM;
use InvestorHelper;
use App\Exports\Data_arrExport;
use Carbon\Carbon;
use Exception;
use Form;
use App\InvestorRoiRate;
use App\ReserveLiquidity;
use Illuminate\Support\Facades\Schema;

class InvestorRepository implements IInvestorRepository
{
    public function __construct(IRoleRepository $role, ILabelRepository $label,IUserRepository $user)
    {
        $this->role  = $role;
        $this->label = $label;
        if(Schema::hasTable('users')){
        $this->user  = $user;
        }
        if(Schema::hasTable('settings')){
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }
    public function iIndex($request,$tableBuilder)
    {
        $recurrence_types = [0 => 'All', 1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily', 4 => 'On Demand'];
        $investor_types   = User::getInvestorType();
        $companies        = $this->role->allSubAdmins()->pluck('name', 'id')->toArray();
        $label            = $this->label->getAll()->pluck('name', 'id');
        $Roles = DB::table('roles');
        $Roles = $Roles->whereIn('id', [User::INVESTOR_ROLE, User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE]);
        $Roles = $Roles->pluck('name', 'id')->toArray();
        if ($request->ajax() || $request->wantsJson()) {
            $search_key = $request['search']['value'] ?? '';
            if(isset($request->velocity_owned)){
               $velocity_owned = true; 
            }else{
                $velocity_owned = false;  
            }
            return MTB::accountsList($request->investor_type, $request->velocity, $request->active_status, $request->active_status_companies, $request->auto_invest_label, $request->auto_generation, $request->notification_recurence, $request->role_id, $search_key,$velocity_owned);
        }
        $tableBuilder->ajax([
            'url'  => route('admin::investors::index'),
            'data' => 'function(d){
                d.investor_type           = $("#investor_type").val();
                d.active_status           = $("input[name=active_status]:checked").val();
                d.active_status_companies = $("input[name=active_status_companies]:checked").val();
                d.liquidity               = $("#liquidity").val();
                d.velocity_owned          = $("input[name=velocity_owned]:checked").val();
                d.notification_recurence  = $("#notification_recurence").val();
                d.auto_invest_label       = $("#auto_invest_label").val();
                d.velocity                = $("#velocity").val();
                d.role_id                 = $("#role_id").val();
                d.auto_generation         = $("input[name=auto_generation]:checked").val();
            }'
        ]);
        $tableBuilder->parameters([
            'footerCallback' => 'function(t,o,a,l,m){
                if(typeof table !== "undefined") {
                    var n=this.api(),o=table.ajax.json();
                    $(n.column(3).footer()).html(o.t_liq),$(n.column(4).footer()).html(o.t_amount)
                }
            }',
            'fnCreatedRow' => "function (nRow, aData, iDataIndex) {
                var info   = this.dataTable().api().page.info();
                var page   = info.page;
                var length = info.length;
                var index  = (page * length + (iDataIndex + 1));
                $('td:eq(0)', nRow).html(index).addClass('txt-center');
            }",
            'paging'     => true,
            'pagingType' => 'input',
            'serverSide' => true,
            'order'      => [[5,'desc']]
        ]);
        $tableBuilder = $tableBuilder->columns([
            [ 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'className' => 'details-control', 'orderable' => false, 'searchable' => false, 'defaultContent' => ''],
            [ 'data' => 'users.name', 'name' => 'users.name', 'title' => 'Syndicate Company Name'],
            [ 'data' => 'email', 'name' => 'email', 'title' => 'Email', 'orderable' => false],
            [ 'data' => 'user_details.liquidity', 'name' => 'user_details.liquidity', 'title' => 'Liquidity','className' =>'text-right'],
            [ 'data' => 'principal_balance', 'name' => 'principal_balance', 'title' => 'Principal Balance','className' =>'text-right','orderable' => false],
            [ 'data' => 'createdDate', 'name' => 'users.created_at', 'title' => 'Created At'],
            [ 'data' => 'users.updated_at', 'name' => 'updated_at', 'title' => 'Updated At'],
            [ 'data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
        ]);
        $return['recurrence_types'] = $recurrence_types;
        $return['investor_types']   = $investor_types;
        $return['companies']        = $companies;
        $return['label']            = $label;
        $return['tableBuilder']     = $tableBuilder;
        $return['Roles']            = $Roles;
        return $return;
    }
    public function iCreate($request)
    {
        $investor_types   = User::getInvestorType();
        $recurrence_types = [1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily', 4 => 'On Demand'];
        $groupBy          = [1 => 'GroupBy Weekly', 2 => 'GroupBy MOnthly', 3 => 'GroupBy Daily'];
        $investor_admin   = $this->role->allSubAdmin()->pluck('name', 'id')->toArray();
        $label = $this->label->getAll()->pluck('name', 'id');
        $Roles = DB::table('roles');
        $Roles->whereIn('id', [User::INVESTOR_ROLE]);
        $Roles = $Roles->pluck('name', 'id')->toArray();
        $otherRoles = ["Agent Fee Account","crm","Over Payment"];
        $fee_values=FFM::fees_array();
        $roi_rates=FFM::fees_array(0,15);
        foreach($otherRoles as $role1){
            $Roles1 = array();
            $accounttype = Role::whereName($role1)->first()->users()->whereNull('deleted_at')->get()->count();
            if($accounttype == 0){
                $Roles1 = Role::whereName($role1)->pluck('name', 'id')->toArray();
                $Roles[key($Roles1)] = $Roles1[key($Roles1)];
            }
        }
        $company_permission = $request->user()->hasRole(['company']);
        $user_id = $request->user()->id;
        if ($company_permission) {
            $companies = $this->role->allCompanies()->where('id', $user_id)->pluck('name', 'id')->toArray();
        } else {
            $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        }
        $super_admin_arr[1] = 'admin';
        $investor_admin = $super_admin_arr + $investor_admin;
        $bank = 0;
        $return['investor_types']     = $investor_types;
        $return['investor_admin']     = $investor_admin;
        $return['recurrence_types']   = $recurrence_types;
        $return['bank']               = $bank;
        $return['groupBy']            = $groupBy;
        $return['companies']          = $companies;
        $return['company_permission'] = $company_permission;
        $return['user_id']            = $user_id;
        $return['label']              = $label;
        $return['Roles']              = $Roles;
        $return['fee_values']         = $fee_values;
        $return['roi_rates']          = $roi_rates;
        return $return;
    }
    public function iStore($request)
    {
        try {
            $emails = explode(',', $request->notification_email);
            if($request->notification_email !=''){
            foreach ($emails as $email) {
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("Please enter valid email for notifications", 1);
                }
            }
                }
            $userAccount = $this->user->createAccount($request);
            if (!$userAccount) {
                throw new \Exception("Something went wrong", 1);
            }
            $return['result']  = 'success';
            $return['user_id'] = $userAccount->id;
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iEdit($request,$id)
    {
        try {
            $Investor=User::find($id);
            if(!$Investor)
            throw new \Exception("Invalid User Id", 1);
            $Roles = DB::table('roles');
            $Roles = $Roles->whereIn('id', [User::INVESTOR_ROLE, User::CRM_ROLE, User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE]);
            $Roles = $Roles->pluck('name', 'id')->toArray();
            if(!$Investor->hasRole($Roles)) {
                throw new \Exception("Invalid Investor Id", 1);
            }
            $fee_values=FFM::fees_array();
            $roi_rates=FFM::fees_array(0,15);
            if ($request->user()->hasRole(['company'])) {
                $id1 = $request->user()->id;
                $subinvestors = [];
                $inv = $this->role->allInvestors();
                $subadmininvestor = $inv->where('company', $id1);
                foreach ($subadmininvestor as $key1 => $value) {
                    $subinvestors[] = $value->id;
                }
                if (! in_array($id, $subinvestors)) {
                    throw new \Exception("This Investor not a company based", 1);
                }
            }
            ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = $request->user()->id;
            $investor = $this->user->findInvestor($id);
            if (!$investor) throw new \Exception("Invalid User Id", 1);
            $investor_types     = User::getInvestorType();
            $groupBy            = [1 => 'GroupBy Weekly', 2 => 'GroupBy Monthly', 3 => 'GroupBy Daily'];
            $recurrence_types   = [1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily', 4 => 'On Demand'];
            $label              = $this->label->getAll()->pluck('name', 'id');
            $bank               = Bank::where('investor_id', $id)->count();
            $company_permission = $request->user()->hasRole(['company']);
            $user_id = $request->user()->id;
            if ($company_permission) {
                $companies = $this->role->allCompanies()->where('id', $user_id)->pluck('name', 'id')->toArray();
            } else {
                $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
            }
            $investor_admin = $this->role->allSubAdmin()->pluck('name', 'id')->toArray();
            $super_admin_arr[1] = 'admin';
            $investor_admin = $super_admin_arr + $investor_admin;
            if (empty($permission) && ! $request->user()->hasRole(['company'])) {
                $investorAccess = User::where('id', $id)->where('creator_id', $userId)->first();
                if (empty($investorAccess)) throw new \Exception("Permission Denied", 1);
            }
            $return['bank']               = $bank;
            $return['investor']           = $investor;
            $return['recurrence_types']   = $recurrence_types;
            $return['investor_types']     = $investor_types;
            $return['investor_admin']     = $investor_admin;
            $return['groupBy']            = $groupBy;
            $return['companies']          = $companies;
            $return['company_permission'] = $company_permission;
            $return['user_id']            = $user_id;
            $return['label']              = $label;
            $return['Roles']              = $Roles;
            $return['fee_values']         = $fee_values;
            $return['roi_rates']         = $roi_rates;
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iUpdate($request,$id)
    {
        try {
            $emails = explode(',', $request->notification_email);
            if($request->notification_email !=''){
                foreach ($emails as $email) {
                    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception("Please enter valid emails for notifications", 1);
                    }
                }
           }
            if (!$this->user->updateInvestor($id, $request)) {
                throw new \Exception("Something went wrong", 1);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iDelete($request,$id)
    {
        try {
            if (! $this->user->deleteInvestor($id)) {
                throw new \Exception('Cannot delete Account!', 1);
            }
            $check             = InvestorTransaction::where('investor_id', $id)->count();
            $checkMerchantUser = MerchantUser::where('user_id', $id)->count();
            $checkACH          = InvestorAchRequest::where('investor_id', $id)->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing)->count();
            if($check || $checkMerchantUser || $checkACH){
                throw new \Exception('Cannot delete Account,already referred', 1);
            }
            InvestorHelper::update_liquidity($id, 'Delete Investor');
            if(!User::find($id)->delete()) throw new \Exception('Can not Delete User');
            InvestorAchRequest::where('investor_id', $id)->each(function($row) {
                $row->delete();
            });
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iPortfolio($request,$tableBuilder,$userId)
    {
        try {
            $Investor=User::find($userId);
            if(!$Investor) {
                throw new \Exception("Invalid User Id", 1);
            }
            $carry['profit'] = CarryForward::where('type', 2)->where('investor_id', $userId)->sum('amount');
            if ($request->user()->hasRole(['company'])) {
                ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
                $id = $request->user()->id;
                $subinvestors = [];
                $inv = $this->role->allInvestors();
                $subadmininvestor = $inv->where('company', $id);
                foreach ($subadmininvestor as $key1 => $value) {
                    $subinvestors[] = $value->id;
                }
                if (! in_array($userId, $subinvestors)) {
                    throw new \Exception("This Investor not a company based", 1);
                }
            }
            $portfolio_difference = FFM::portfolio_difference($userId);
            $investor = $this->user->findInvestor($userId);
            ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userid = $request->user()->id;
            if ($request->ajax() || $request->wantsJson()) {
                return MTB::adminMerchantListView($userId, $request->status);
            }
            $tableBuilder->ajax(['url' => route('admin::investors::portfolio', $userId), 'data' => 'function(d){ d.status = $("#status").val();}']);
            $tableBuilder->parameters([
                'footerCallback' => "function(t,o,a,l,m){
                    var n=this.api(),o=table.ajax.json();
                    $(n.column(0).footer()).html('Total:');
                    $(n.column(3).footer()).html(o.funded_total),$(n.column(6).footer()).html(o.rtr_total),$(n.column(8).footer()).html(o.ctd_total)
                }",
                'order' => [[2, 'desc'] ]
            ]);
            $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true]);
            $tableBuilder = $tableBuilder->columns([
                ['DT_RowIndex' => 'DT_RowIndex', 'defaultContent' => '', 'data' => 'DT_RowIndex', 'title' => '#', 'orderable' => false, 'searchable' => false],
                ['data' => 'merchants.name', 'name' => 'merchants.name', 'title' => 'Merchant'],
                ['data' => 'merchants.date_funded', 'name' => 'merchants.date_funded', 'title' => 'Date Funded'],
                ['data' => 'merchant_user.amount', 'name' => 'merchant_user.amount', 'title' => 'Funded'], 
                ['data' => 'merchant_user.commission_per', 'name' => 'merchant_user.commission_per', 'title' => 'Commission'],
                ['data' => 'merchant_user.up_sell_commission_per', 'name' => 'merchant_user.up_sell_commission_per', 'title' => 'Upsell Commission'],
                ['data' => 'invest_rtr', 'name' => 'invest_rtr', 'title' => 'RTR', 'searchable' => false],
                ['data' => 'merchants.factor_rate', 'name' => 'merchants.factor_rate', 'title' => 'Rate'],
                ['data' => 'paid_participant_ishare', 'name' => 'paid_participant_ishare', 'title' => 'CTD', 'searchable' => false],
                ['data' => 'annualized_rate', 'name' => 'annualized_rate', 'title' => 'Annualized rate', 'searchable' => false, 'orderable' => false],
                ['data' => 'merchants.complete_percentage', 'name' => 'merchants.complete_percentage', 'title' => 'Complete'],
                ['data' => 'sub_statuses.name', 'name' => 'sub_statuses.name', 'title' => 'Status']
            ]);
            $merchant_count = 0;
            $total_default  = 0;
            $month  = date('m', strtotime('0 month'));
            $year   = date('Y', strtotime('0 month'));
            $month1 = date('m', strtotime('-1 month'));
            $year1  = date('Y', strtotime('-1 month'));
            $month2 = date('m', strtotime('-2 month'));
            $year2  = date('Y', strtotime('-2 month'));
            $month3 = date('m', strtotime('-3 month'));
            $year3  = date('Y', strtotime('-3 month'));
            $month4 = date('m', strtotime('-4 month'));
            $year4  = date('Y', strtotime('-4 month'));
            $chart_data = [];
            $date1 = date('Y-m', strtotime('-4 month')).'-01';
            $date2 = date('Y-m-t', strtotime('0 month'));
            $fund_data = MerchantUser::whereIn('merchant_user.status', [1, 3]);
            $fund_data = $fund_data->leftJoin('merchants', 'merchant_user.merchant_id', 'merchants.id');
            $fund_data = $fund_data->where('merchant_user.user_id', $userId);
            $fund_data = $fund_data->groupBy(DB::raw('MONTH(merchants.date_funded)'));
            $fund_data = $fund_data->where('merchants.date_funded', '>=', $date1);
            $fund_data = $fund_data->where('merchants.date_funded', '<=', $date2);
            $fund_data = $fund_data->select(
                DB::raw('SUM(merchant_user.amount) as funded'),
                DB::raw('MONTH(merchants.date_funded) as month'),
                DB::raw('YEAR(merchants.date_funded) as year'),
                DB::raw('SUM(merchant_user.invest_rtr-(merchant_user.invest_rtr*((merchant_user.mgmnt_fee)/100))) as rtr_month')
            );
            $fund_data = $fund_data->get();
            $ctd_month_data = ParticipentPayment::join('payment_investors', 'participent_payments.id', 'payment_investors.participent_payment_id');
            $ctd_month_data = $ctd_month_data->join('users', 'users.id', 'payment_investors.user_id')->where('user_id', $userId);
            $ctd_month_data = $ctd_month_data->groupBy(DB::raw('MONTH(participent_payments.payment_date)'));
            $ctd_month_data = $ctd_month_data->where('participent_payments.payment_date', '>=', $date1);
            $ctd_month_data = $ctd_month_data->where('participent_payments.payment_date', '<=', $date2);
            $ctd_month_data = $ctd_month_data->select(
                DB::raw('SUM(payment_investors.participant_share-mgmnt_fee) as ctd_month'),
                DB::raw('YEAR(participent_payments.payment_date) as year'),
                DB::raw('MONTH(participent_payments.payment_date) as month')
            );
            $ctd_month_data = $ctd_month_data->get();
            $array1 = $fund_data->toArray();
            $count = count($array1);
            if ($count != 5) {
                for ($i = 0; $i >= -4; $i--) {
                    $month = date('m', strtotime($i.' month'));
                    $year = date('Y', strtotime($i.' month'));
                    if (array_search($month, array_column($array1, 'month')) === false) {
                        $newdata = ['funded' => 0, 'rtr_month' => 0, 'month' => $month, 'year' => $year];
                        array_push($array1, $newdata);
                    }
                }
            }
            $array2 = $ctd_month_data->toArray();
            $final = [];
            foreach ($array1 as $key1 => $data1) {
                foreach ($array2 as $key2 => $data2) {
                    if ($data1['month'] == $data2['month']) {
                        $final[] = $data1 + $data2;
                        unset($array1[$key1]);
                        unset($array2[$key2]);
                    } else {
                    }
                }
            }
            if (! empty($array1)) {
                foreach ($array1 as $value) {
                    $value['rtr_month'] = round($value['rtr_month'],2);
                    $final[] = $value;
                }
            }
            if (! empty($array2)) {
                foreach ($array2 as $value) {
                    $final[] = $value;
                }
            }
            $chart_data = $final;
            for ($i = 0; $i >= -4; $i--) {
                $month = date('m', strtotime($i.' month'));
                $year = date('Y', strtotime($i.' month'));
                if (array_search($month, array_column($chart_data, 'month')) === false) {
                    $newdata = ['funded' => 0, 'rtr_month' => 0, 'ctd_month' => 0, 'month' => $month, 'year' => $year];
                    array_push($chart_data, $newdata);
                }
            }
            foreach ($chart_data as $key => $part) {
                $part['payment_date'] = $part['year'].'-'.$part['month'];
                $sort[$key] = strtotime($part['payment_date']);
            }
            array_multisort($sort, SORT_ASC, $chart_data);
            $array                        = $this->user->investorDashboard($userId, $investor->investor_type);
            $liquidity                    = $array['liquidity'];
            $reserved_liquidity           = $array['reserved_liquidity'];
            $pending_debit_ach_request    = $array['pending_debit_ach_request'];
            $pending_credit_ach_request   = $array['pending_credit_ach_request'];
            $invested_amount              = $array['invested_amount'];
            $funded_amount                = $array['funded_amount'];
            $net_rtr                      = $array['net_rtr'];
            $ctd                          = $array['ctd'];
            $blended_rate                 = $array['blended_rate'];
            $default_percentage           = $array['default_percentage'];
            $merchant_count               = $array['merchant_count'];
            $total_rtr                    = $array['total_rtr'];
            $average                      = $array['average'];
            $investor_type                = $array['investor_type'];
            $velocity_dist                = $array['velocity_dist'];
            $investor_dist                = $array['investor_dist'];
            $total_requests               = $array['total_requests'];
            $portfolio_value              = $array['portfolio_value'];
            $principal_investment         = $array['principal_investment'];
            $average_principal_investment = $array['average_principal_investment'];
            $debit_interest               = $array['debit_interest'];
            $irr                          = $array['irr'];
            $total_credit                 = $array['total_credit'];
            $current_portfolio            = $array['current_portfolio'];
            $substatus                    = $array['substatus'];
            $overpayment                  = $array['overpayment'];
            $c_invested_amount            = $array['c_invested_amount'];
            $profit                       = $array['profit'];
            $paid_to_date                 = $array['paid_to_date'];
            $anticipated_rtr              = $array['anticipated_rtr'];
            $roi                          = ($array['average_principal_investment'] != 0) ? ($profit+$carry['profit']) / $array['average_principal_investment'] * 100 : 0;
            $existing_liquidity = UserDetails::where('user_id',$userId)->first()->liquidity;
            $total_credits      = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->where('investor_id', $userId)->sum('amount');
            $MerchantUser       = MerchantUser::where('user_id', $userId)->where('merchant_user.status', '!=', 0)->select(DB::raw('SUM(paid_mgmnt_fee) as paid_mgmnt_fee'), DB::raw('SUM(amount) as amount'), DB::raw('SUM(commission_amount) as commission_amount'), DB::raw('SUM(under_writing_fee) as under_writing_fee'), DB::raw('SUM(pre_paid) as pre_paid'), DB::raw('SUM(paid_participant_ishare) as paid_participant_ishare'))->first();
            $ctd                = $MerchantUser['paid_participant_ishare'] - $MerchantUser['paid_mgmnt_fee'];
            $total_funded       = $MerchantUser['amount'];
            $commission_amount  = $MerchantUser['commission_amount'];
            $under_writing_fee  = $MerchantUser['under_writing_fee'];
            $pre_paid_amount    = $MerchantUser['pre_paid'];
            $actual_liquidity   = ($total_credits + $ctd) - ($total_funded + $commission_amount) - $pre_paid_amount - $under_writing_fee;
            $actual_liquidity   = round($actual_liquidity, 2);
            $return['substatus']                    =$substatus;
            $return['investor_type']                =$investor_type;
            $return['tableBuilder']                 =$tableBuilder;
            $return['chart_data']                   =$chart_data;
            $return['merchant_count']               =$merchant_count;
            $return['liquidity']                    =$liquidity;
            $return['reserved_liquidity']           =$reserved_liquidity;
            $return['pending_credit_ach_request']   =$pending_credit_ach_request;
            $return['pending_debit_ach_request']    =$pending_debit_ach_request;
            $return['invested_amount']              =$invested_amount;
            $return['blended_rate']                 =$blended_rate;
            $return['total_default']                =$total_default;
            $return['default_percentage']           =$default_percentage;
            $return['total_requests']               =$total_requests;
            $return['ctd']                          =$ctd;
            $return['total_rtr']                    =$total_rtr;
            $return['investor']                     =$investor;
            $return['portfolio_value']              =$portfolio_value;
            $return['principal_investment']         =$principal_investment;
            $return['userId']                       =$userId;
            $return['overpayment']                  =$overpayment;
            $return['c_invested_amount']            =$c_invested_amount;
            $return['average']                      =$average;
            $return['average_principal_investment'] =$average_principal_investment;
            $return['profit']                       =$profit;
            $return['paid_to_date']                 =$paid_to_date;
            $return['roi']                          =$roi;
            $return['anticipated_rtr']              =$anticipated_rtr;
            $return['existing_liquidity']           =$existing_liquidity;
            $return['actual_liquidity']             =$actual_liquidity;
            $return['carry']                        =$carry;
            $return['funded_amount']                =$funded_amount;
            $return['net_rtr']                      =$net_rtr;
            $return['result']='success';
        } catch (\Exception $e) {
            $return['result']=$e->getMessage();
        }
        return $return;
    }
    public function iInvestorDownload($request)
    {   $velocity_owned = (isset($request->velocity_owned)) ? true :false;
        $user = $request->user();
        $details         = MTB::getInvestorList($request->investor_type, $request->velocity, $request->active_status, $request->active_status_companies, $request->liquidity, $request->auto_invest_label, $request->role_id, $request->auto_generation, $request->notification_recurence,$velocity_owned);
        $total_liquidity = $details['total']['total_liquidity'];
        $details         = $details['data']->get();
        $excel_array[0] = ['No', 'Name', 'Email', 'Liquidity','Available Liquidity','Reserved Liquidity', 'Principal Balance','Created at', 'Updated at'];
        $total_liquidity = $total_pinvestment = $total_reserved_liquidity = $total_available_liquidity = 0;
        $i = 1;
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $liquidity = ($data->liquidity) ? $data->liquidity : '0.00';
                $reserved_liquidity_amount = ($data->reserved_liquidity_amount > 0) ? $data->reserved_liquidity_amount: 0;
                $available_liquidity = $liquidity-$reserved_liquidity_amount;
                $total_liquidity = $total_liquidity + $data->liquidity;
                $total_pinvestment = $total_pinvestment+$data->amount;
                $total_reserved_liquidity = $total_reserved_liquidity+$reserved_liquidity_amount;
                $total_available_liquidity = $total_available_liquidity+$available_liquidity;
                $excel_array[$i]['No']         = $i;
                $excel_array[$i]['Name']       = $data->name;
                $excel_array[$i]['Email']      = $data->email;
                $excel_array[$i]['Liquidity']  = FFM::dollar($liquidity);
                $excel_array[$i]['Available Liquidity']  = FFM::dollar($available_liquidity);
                $excel_array[$i]['Reserved Liquidity']  = FFM::dollar($reserved_liquidity_amount);
                $excel_array[$i]['Principal Balance']  = FFM::dollar($data->amount);
                $excel_array[$i]['Created at'] = FFM::datetime($data->created_at);
                $excel_array[$i]['Updated at'] = FFM::datetime($data->updated_at);
                $i++;
            }
            $excel_array[$i]['No']        = null;
            $excel_array[$i]['Name']      = null;
            $excel_array[$i]['Email']     = 'TOTAL';
            $excel_array[$i]['Liquidity'] = FFM::dollar($total_liquidity);
            $excel_array[$i]['Available Liquidity'] = FFM::dollar($total_available_liquidity);
            $excel_array[$i]['Reserved Liquidity'] = FFM::dollar($total_reserved_liquidity);
            $excel_array[$i]['Principal Balance'] = FFM::dollar($total_pinvestment);

        }
        $export = new Data_arrExport($excel_array);
        return $export;
    }
    public function iSelectType($request)
    {
        $type = $request->get('type');
        $investors = $this->role->allInvestors();
        if ($type != 0) {
            $investors = $investors->where('investor_type', $type);
        }
        $investors = $investors->pluck('name', 'id');
        if (count($investors)) {
            return $investors->toArray();
        } else {
            return [];
        }
    }
    public function iInvestorsLogList($request)
    {
        try {
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            $batchId     = $request->batch_id;
            $merchant_id = $request->merchant_id;
            $log_id      = $request->id;
            
            $description = LiquidityLog::where('id', $log_id)->value('description');
            if (!$batchId) throw new \Exception("Empty Batch Id", 1);
            $investors = LiquidityLog::select('member_id', 'users.name as user_name', 'users.deleted_at as user_deleted_at');
            $investors = $investors->where('liquidity_change', '!=', 0);
            $investors = $investors->where('batch_id', $batchId);
            $investors = $investors->join('users', 'users.id', 'liquidity_log.member_id');
            if ($merchant_id > 0) {
                $investors = $investors->where('liquidity_log.merchant_id', $merchant_id);
            }
            if ($description != null) {
                $investors = $investors->where('liquidity_log.description', $description);
            }
            if(isset($request->velocity_owned)){
                $investors = $investors->where('users.velocity_owned', 1);
            }
            $investors = $investors->distinct('liquidity_log.member_id');
            if (empty($permission)) {
                if (Auth::user()->hasRole(['company'])) {
                    $investors = $investors->where('company', $userId);
                } else {
                    $investors = $investors->where('creator_id', $userId);
                }
            }
            $investors = $investors->get();
            $investors = $investors->toArray();
            if (empty($investors)) {
                throw new \Exception("No investors available here", 1);
            }
            $return['investors'] = $investors;
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iGetSelect2Investors($request)
    {
        $limit  = 20;
        $offset = $request->page ?? 0;
        $search = $request->search ?? null;
        if ($offset > 1) {
            $offset = $offset * $limit;
        } else {
            $offset = 0;
        }
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $investors = $investors->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor');
        $investors = $investors->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        });
        if (empty($permission)) {
            $investors = $investors->where('users.company',$request->user()->id);   
        }
        if ($search) {
            $investors = $investors->where(function ($query) use ($search) {
                $query->orWhere('users.name', 'like', '%'.$search.'%');
            });
        }
        $investors       = $investors->select(DB::raw('upper(users.name) as text'), 'users.id')->orderBy('text')->get();
        $count           = $investors->count();
        $investors_array = $investors->toArray();
        $investors_array = array_slice($investors_array, $offset, $limit);
        $not_ended = true;
        $offset += $limit;
        if ($offset >= $count) {
            $not_ended = false;
        }
        $pagination = (object) ['more' => $not_ended];
        $return['pagination']      = $pagination;
        $return['investors_array'] = $investors_array;
        return $return;
    }
    public function iBankIndex($request,$tableBuilder,$id)
    {
        try {
            $Investor=User::find($id);
            if(!$Investor) {
                throw new \Exception("Invalid User Id", 1);
            }    
            $investor = User::where('id', $id)->first();
            $tableBuilder->ajax(route('admin::investors::bankdata', ['id' => $id]));
            $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
            $tableBuilder->parameters([
                'fnCreatedRow' => "function (nRow, aData, iDataIndex) {
                    var info = this.dataTable().api().page.info();
                    var page = info.page;
                    var length = info.length;
                    var index = (page * length + (iDataIndex + 1));
                    $('td:eq(0)', nRow).html(index).addClass('txt-center');
                }",
                'pagingType' => 'input'
            ]);
            $tableBuilder = $tableBuilder->columns([
                ['className' => 'details-control', 'orderable' => false, 'data' => 'id', 'name' => 'id', 'defaultContent' => '', 'title' => '#'],
                ['data' => 'investor_name', 'name' => 'investor_name', 'title' => 'Investor Name'],
                ['data' => 'account_holder_name', 'name' => 'account_holder_name', 'title' => 'Account Holder Name'],
                ['data' => 'bank_name', 'name' => 'bank_name', 'title' => 'Bank Name'],
                ['data' => 'type', 'name' => 'type', 'title' => 'Type'],
                ['data' => 'default_debit', 'name' => 'default_debit', 'title' => 'Default Debit'],
                ['data' => 'default_credit', 'name' => 'default_credit', 'title' => 'Default Credit'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'width' => '15%']
            ]);
            $return['tableBuilder'] = $tableBuilder;
            $return['investor']     = $investor;
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iBankCreatOrUpdate($request)
    {
        try {
            $data = [
                'routing'             => $request->routing,
                'bank_address'        => $request->bank_address,
                'name'                => $request->name,
                'investor_id'         => $request->investor_id,
                'account_holder_name' => $request->account_holder_name,
                'default_debit'       => $request->default_debit,
                'default_credit'      => $request->default_credit
            ];
            if ($request->acc_number) {
                $data['acc_number'] = $request->acc_number;
            }
            $data['type'] = '';
            if (isset($request->type)) {
                $data['type'] = implode(',', $request->type);
            }
            $BankModel = new Bank;
            if ($request->bid) {
                $return_function = $BankModel->selfUpdate($data, $request->bid);
                if ($return_function['result'] != 'success') {
                    throw new \Exception($return_function['result'], 1);
                }
                $return['message'] = 'Bank Details Updated Successfully!';
            } else {
                $return_function = $BankModel->selfCreate($data);
                if ($return_function['result'] != 'success') {
                    throw new \Exception($return_function['result'], 1);
                }
                $return['id']      = $return_function['id'];
                $return['message'] = 'Bank Details Created Successfully!';
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iBankCreate($investor_id,$request)
    {
        try {
            $investor = User::where('id', $investor_id)->first();
            if(!$investor) throw new \Exception("Invalid User Id", 1);
            $return['investor'] = $investor;
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iBankEdit($request,$id)
    {
        try {
            $bank_details = Bank::find($id);
            if(!$bank_details) {
                throw new \Exception("Invalid Bank Id", 1);
            }
            $masked_accountNo = $bank_details->acc_number;
            if (strlen($masked_accountNo) >= 4) {
                $masked_accountNo = FFM::mask_cc($masked_accountNo);
            }
            $investor = User::where('id', $bank_details->investor_id)->first();
            $return['bank_details']     = $bank_details;
            $return['investor']         = $investor;
            $return['masked_accountNo'] = $masked_accountNo;
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function IAuto_company_filter($request)
    {
        $investors = $request->get('investors');
        $company   = $request->get('company');
        $users = User::select('id');
        if ($company) {
            $users = $users->where('company', $company);
        }
        $users = $users->whereNotNull('label');
        $users = $users->whereIn('id', $investors);
        $users = $users->get();
        if (! empty($users->toArray())) {
            $users = $users->toArray();
        }
        return $users;
    }
    public function iPortfolioDownload($request)
    {
        $user   = $request->user();
        $userid = $request->userId;
        $details = MTB::adminMerchantListViewExportData($userid, $request->status);
        $funded_total = $ctd_total = $rtr_total = $commission_total = 0;
        $details = $details->toArray();
        $excel_array = [];
        $funded_total = array_sum(array_column($details, 'amount'));
        $rtr_total    = array_sum(array_column($details, 'invest_rtr')) - array_sum(array_column($details, 'mag_fee'));
        $ctd_total    = array_sum(array_column($details, 'paid_participant_ishare')) - array_sum(array_column($details, 'paid_mgmnt_fee'));
        $i = 1;
        $excel_array[0] = ['No', 'Merchant', 'Date Funded', 'Funded', 'Commission','Upsell Commission', 'RTR', 'Rate', 'CTD', 'Annualized Rate', 'Complete', 'Status'];
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $no_of_payments = ($data['advance_type'] == 'weekly_ach') ? 52 : 255;
                $tot_profit = $data['invest_rtr'] - $data['mag_fee'] - ($data['amount'] + $data['commission_amount']+$data['up_sell_commission']+ $data['pre_paid'] + $data['under_writing_fee']);
                $tot_investment = $data['amount'] + $data['commission_amount'] + $data['pre_paid'] + $data['under_writing_fee']+$data['up_sell_commission'];
                $annualised_rate = 0;
                if ($tot_investment) {
                    $annualised_rate = ($tot_profit * $no_of_payments / $data['pmnts']) / $tot_investment * 100;
                }
                $excel_array[$i][] = $i;
                $excel_array[$i][] = $data['name'];
                $excel_array[$i][] = isset($data['date_funded']) ? FFM::date($data['date_funded']) : 0;
                $excel_array[$i][] = FFM::dollar($data['amount']);
                $excel_array[$i][] = FFM::percent($data['commission']);
                $excel_array[$i][] = FFM::percent($data['up_sell_commission_per']);
                $excel_array[$i][] = FFM::dollar(round($data['invest_rtr'] - $data['mag_fee'], 2));
                $excel_array[$i][] = round($data['factor_rate'], 2);
                $excel_array[$i][] = FFM::dollar($data['paid_participant_ishare'] - $data['paid_mgmnt_fee']);
                $excel_array[$i][] = FFM::percent($annualised_rate);
                $excel_array[$i][] = FFM::percent($data['complete_percentage']);
                $excel_array[$i][] = $data['sub_status_name'];
                $i++;
            }
        }
        $count = count($excel_array);
        $count = $count + 2;
        $excel_array[$count] = ['Total', '', '', FFM::dollar($funded_total), '','', FFM::dollar($rtr_total), '', FFM::dollar($ctd_total), '', '', ''];
        $export = new Data_arrExport($excel_array);
        return $export;
    }
    public function iTransactions($request,$tableBuilder)
    {
        $date_type = 'false';
        if (isset($request->date_type)) {
            $date_type = $request->date_type;
        }
        if($date_type!='true'){
            $request->time_start = $request->time_end = NULL;
        }
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $investors = $investors->join('roles', 'roles.id', 'user_has_roles.role_id');
        $investors = $investors->where('roles.name','investor');
        $investors = $investors->whereHas('company_relation',function ($query) {
            $query ->where('company_status',1);
        });
        $investors = $investors->select(DB::raw("upper(users.name) as name"), 'users.id');
        $investors = $investors->pluck('name','users.id');
        $investors = $investors->toArray();
        $sDate = ! empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : '';
        $eDate = ! empty($request->end_date) ? $request->end_date : date('Y-m-d', strtotime('+5 days'));
        if ($request->ajax() || $request->wantsJson()) {
            $search_key = $request['search']['value'] ?? '';
            $sDateTime = ($sDate) ? ET_To_UTC_Time($sDate.$request->time_start) : '';
            $eDateTime = ($eDate) ? ET_To_UTC_Time($eDate.$request->time_end) : '';
            return MTB::investorTransactions(
                $sDateTime,
                $eDateTime,
                $request->investors,
                $request->transaction_type,
                $request->categories,
                $request->owner,
                $request->investor_type,
                $request->date_type,
                ET_To_UTC_Time($sDate.$request->time_start, 'time'),
                ET_To_UTC_Time($eDate.$request->time_end, 'time'),
                null,
                $search_key,
                $request->status,
                $request->merchant,
                $request->velocity_owned
            );
        }
        $tableBuilder->ajax([
            'url'  => route('admin::investors::transaction-report-records'), 
            'type' => 'post',
            'data' => 'function(data){
                data._token           = "'.csrf_token().'";
                data.start_date       = $("#date_start").val();
                data.end_date         = $("#date_end").val();
                data.time_start       = $("#time_start:visible").val();
                data.time_end         = $("#time_end:visible").val();
                data.date_type        = $("#date_type").is(\':checked\') ? true : false;
                data.investors        = $("#investors").val();
                data.transaction_type = $("#transaction_type").val();
                data.categories       = $("#categories").val();
                data.owner            = $("#owner").val();
                data.investor_type    = $("#investor_type").val();
                data.status           = $("#status").val();
                data.merchant         = $("#merchant").val();
                data.velocity_owned = $("input[name=velocity_owned]:checked").val();
            }'
        ]);
        $tableBuilder->parameters([
            'footerCallback' => 'function(t,o,a,l,m){
                if(typeof table !== "undefined") { 
                    var n=this.api(),o=table.ajax.json();
                    $(n.column(0).footer()).html(o.Total);
                    $(n.column(4).footer()).html(o.total)
                }
            }'
        ]);
        $tableBuilder->parameters([
            'fnCreatedRow' => "function (nRow, aData, iDataIndex) {
                var info   = this.dataTable().api().page.info();
                var page   = info.page;
                var length = info.length;
                var index  = (page * length + (iDataIndex + 1));
                $('td:eq(0)', nRow).html(index).addClass('txt-center');
            }",
            'pagingType' => 'input',
            'order' => [[9, 'desc']]
        ]);
        $tableBuilder = $tableBuilder->columns(MTB::investorTransactions(null, null, null, null, null, null, null, null, null, null, true,null));
        $categories = InvestorAchTransactionView::transactionCategoryOptions();
        $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0] = 'All';
        $companies = array_reverse($companies, true);
        $investor_types = User::getInvestorType();
        $allMerchants = [ '0' => 'Select Merchant' ] + Merchant::pluck('name','id')->toArray();
        $statuses = [''=>'Select Status']+InvestorTransaction::statusOptions();
        $return['investors']      = $investors;
        $return['tableBuilder']   = $tableBuilder;
        $return['categories']     = $categories;
        $return['companies']      = $companies;
        $return['investor_types'] = $investor_types;
        $return['statuses']       = $statuses;
        $return['allMerchants']   = $allMerchants;
        return $return;
    }
    public function iTransactionReportDownload($request)
    {
        $transaction_type     = $request->transaction_type;
        $transaction_category = $request->categories;
        if ($transaction_type[0] == 0) {
            $transaction_type = null;
        }
        $date_type = 'false';
        if (isset($request->date_type)) {
            $date_type = $request->date_type;
        }
        if($date_type!='true'){
            $request->time_start = $request->time_end = NULL;
        }
        $user = $request->user();
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $sDate = ! empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : '';
        $eDate = ! empty($request->end_date) ? $request->end_date : date('Y-m-d', strtotime('+5 days'));
        $velocity_owned = false;
        if($request->velocity_owned){
        $velocity_owned = true;
        }
        $transactions = MTB::investorTransactionQuery(ET_To_UTC_Time($sDate.$request->time_start), ET_To_UTC_Time($eDate.$request->time_end), $request->investors, $transaction_type, $transaction_category, $request->owner, $request->investor_type, $request->date_type, ET_To_UTC_Time($sDate.$request->time_start.':00', 'time'), ET_To_UTC_Time($eDate.$request->time_end.':59', 'time'), null, null, $request->status,$request->merchant,$velocity_owned);
        $total = $transactions->sum('amount');
        $details = $transactions->orderBy('updated_at','desc')->get();
        $excel_array[] = ['No', 'Investor', 'Transaction Category', 'Transaction Type', 'Transaction Method', 'Amount', 'Status', 'Investment Date', 'Maturity date', 'Created at', 'Last Updated at', 'Notes'];
        $i = 1;
        $total_amount = 0;
        if (! empty($details)) {
            foreach ($details as $key => $data) {
                $total_amount = $total_amount + $data->amount;
                $excel_array[$i]['No']                   = $i;
                $excel_array[$i]['Investor']             = $data->name;
                $excel_array[$i]['Transaction Category'] = $data->transaction_category ? $data->TransactionCategoryName : '';
                $excel_array[$i]['Transaction Type']     = $data->transaction_type ? $data->TransactionTypeName : '';
                $excel_array[$i]['Transaction Method']   = $data->transaction_method ? $data->TransactionMethodName : '';
                $excel_array[$i]['Amount']               = FFM::dollar($data->amount);
                $excel_array[$i]['Status']               = $data->StatusName ?? '';
                $excel_array[$i]['Investment Date']      = ! empty($data->date) ? FFM::date($data->date) : '';
                $excel_array[$i]['Maturity date']        = ! empty($data->maturity_date) ? FFM::date($data->maturity_date) : '';
                $excel_array[$i]['Created at']           = ! empty($data->created_at) ? FFM::datetime($data->created_at) : '';
                $excel_array[$i]['Last Updated at']      = ! empty($data->updated_at) ? FFM::datetime($data->updated_at) : '';
                $excel_array[$i]['Notes']                = $data->category_notes;
                $i++;
            }
        }
        $excel_array[$i]['No']                   = null;
        $excel_array[$i]['Investor']             = null;
        $excel_array[$i]['Transaction Category'] = null;
        $excel_array[$i]['Transaction Type']     = null;
        $excel_array[$i]['Transaction Method']   = null;
        $excel_array[$i]['Amount']               = FFM::dollar($total_amount);
        $export = new Data_arrExport($excel_array);
        return $export;
    }
    public function iInvestorAchCheck_edit($request,$id)
    {
        try {
            $InvestorAchRequest = InvestorAchRequest::find($id);
            $InvestorAchRequest->order_id = $request['order_id'];
            $InvestorAchRequest->save();
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function ICompany_filter($request)
    {
        $investors = $request->get('investors');
        $company   = $request->get('company');
        $users = User::select('id');
        $users = $users->where('investor_type', '!=', 5);
        $users = $users->whereIn('id', $investors);
        if ($company) {
            $users = $users->where('company', $company);
        }
        $users = $users->get();
        if (! empty($users->toArray())) {
            $users = $users->toArray();
        }
        return $users;
    }
    public function iAchRequestPage($id)
    {
        try {
            $Investor = User::find($id);
            if(!$Investor) throw new \Exception("Invalid User Id", 1);
            $BankDetails = Bank::whereinvestor_id($id)->get();
            $transaction_categories = \ITran::getACHCreditOptions();
            $return['BankDetails']            = $BankDetails;
            $return['Investor']               = $Investor;
            $return['transaction_categories'] = $transaction_categories;
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iAchDebitRequest($request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'transaction_type'     => $request['transaction_type'],
                'transaction_method'   => InvestorAchRequest::MethodByAdminCredit,
                'transaction_category' => InvestorAchRequest::CategoryTransferToVelocity,
                'request_ip_address'   => $request->ip(),
                'bank_id'              => $request['bank_id'],
                'investor_id'          => $request['investor_id'],
                'amount'               => $request['amount']
            ];
            $ActumRequest = new ActumRequest;
            $return_result = $ActumRequest->RequestHandler($data);
            if ($return_result['InvestorAchRequest'] == 'created') {
                DB::commit();
            }
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $Investor = User::find($request['investor_id']);
            $message['title']        = 'Ach Debit Requested';
            $message['subject']      = $message['title'];
            $message['Investor']     = $Investor->name;
            $message['investor_id']  = $Investor->id;
            $message['amount']       = $request['amount'];
            $message['type']         = $request['transaction_type'];
            $message['date']         = FFM::date(date('Y-m-d'));
            $message['Creator']      = 'Admin';
            $message['creator_name'] = $request->user()->name;
            $message['to_mail']      = $Investor->notification_email;
            $message['status']       = 'investor_ach_request';
            if ($message['to_mail']) {
                $email_template = Template::where([ ['temp_code', '=', 'ACDR'], ['enable', '=', 1], ])->first();
                if ($email_template) {
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $emails     = Settings::value('email');
                    $emailArray = explode(',', $emails);
                    $message['to_mail'] = $emailArray;
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails  = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails  = array_diff($role_mails, $emailArray);
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
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            DB::rollback();
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iAchCreditRequest($request)
    {
        try {
            $data = [
                'transaction_type'     => $request['transaction_type'],
                'transaction_method'   => InvestorAchRequest::MethodByAdminDebit,
                'transaction_category' => $request['transaction_category'],
                'request_ip_address'   => $request->ip(),
                'bank_id'              => $request['bank_id'],
                'investor_id'          => $request['investor_id'],
                'amount'               => $request['amount']
            ];
            $ActumRequest = new ActumRequest;
            $return_result = $ActumRequest->RequestHandler($data);
            if ($return_result['InvestorAchRequest'] == 'created') {
                DB::commit();
            }
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $Investor                = User::find($request['investor_id']);
            $message['title']        = 'Ach Credit Requested';
            $message['subject']      = $message['title'];
            $message['Investor']     = $Investor->name;
            $message['investor_id']  = $Investor->id;
            $message['amount']       = $request['amount'];
            $message['type']         = $request['transaction_type'];
            $message['date']         = FFM::date(date('Y-m-d'));
            $message['Creator']      = 'Admin';
            $message['creator_name'] = $request->user()->name;
            $message['to_mail']      = $Investor->notification_email;
            $message['status']       = 'investor_ach_request';
            if ($message['to_mail']) {
                $email_template = Template::where([ ['temp_code', '=', 'ACDR'], ['enable', '=', 1], ])->first();
                if ($email_template) {
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $emails = Settings::value('email');
                    $emailArray = explode(',', $emails);
                    $message['to_mail'] = $emailArray;
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $emailArray);
                            $bcc_mails[] = $role_mails;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc']     = [];
                    $message['to_mail'] = $this->admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iSyndicationPayments($tableBuilder)
    {
        $investors = $this->role->allInvestors()->where('investor_type', 5);
        $investorsList = $investors->pluck('name', 'id')->toArray();
        $recurrence_types = [1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily'];
        $recurrence_type = [];
        $recurrence_type[] = '3';
        $paymentDate = date('Y-m-d');
        $paymentDateWorkingDay = PayCalc::getWorkingDays($paymentDate, $paymentDate);
        if (empty($paymentDateWorkingDay)) {
            $start = date('Y-m-d', strtotime($paymentDate.'- 5 days'));
            $paymentDateWorkingDay = PayCalc::getWorkingDays($start, $paymentDate);
            $newpaymentDate = max($paymentDateWorkingDay);
            $paymentDate = $newpaymentDate;
        }
        $today = date('Y-m-d');
        $friday = date('Y-m-d', strtotime('friday this week'));
        $HolidayCheckFriday = PayCalc::getWorkingDays($today, $friday);
        if ($HolidayCheckFriday) {
            $friday = max($HolidayCheckFriday);
        }
        if ($friday == $today) {
            $recurrence_type[] = '1';
        }
        $MonthEnd = date('Y-m-t');
        $HolidayCheckMonthEnd = PayCalc::getWorkingDays($today, $MonthEnd);
        if ($HolidayCheckMonthEnd) {
            $MonthEnd = max($HolidayCheckMonthEnd);
        }
        if ($MonthEnd == $today) {
            $recurrence_type[] = '2';
        }
        
        $flag_of_time = date('H', strtotime(ET_To_UTC_TimeOnly('12:00')));
        $now = date('H');
        $same_day_button = false;
        if ($now < $flag_of_time) {
            $same_day_button = true;
        }
        $tableBuilder->ajax([
            'url'  => route('admin::investors::syndication-payments-tabledata'),
            'type' => 'get',
            'data' => 'function(d){
                d.investor_id            = $("#investor_id").val();
                d.notification_recurence = $("#notification_recurence").val();
            }'
        ]);
        $tableBuilder->parameters([
            'fnRowCallback' => "function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.syndication_check) {
                    if (!aData.cell_phone) {
                        $('td', nRow).css('background-color', '#fb01011f');
                    }
                    else if (aData.Bank=='Empty') {
                        $('td', nRow).css('background-color', '#fb01011f');
                    }
                    else if (aData.auto_syndicate_payment==0) {
                        $('td', nRow).css('background-color', '#fb01011f');
                    }
                    else{
                        $('td',nRow).addClass('validSum');
                    }
                    
                } else {
                    $('td', nRow).css('background-color', 'rgb(246, 248, 251)');
                    if(aData.syndication_check_today=='Yes'){
                        $('td', nRow).css('background-color', 'rgb(160 216 170)');
                    }
                }
            }",
            'footerCallback' => 'function(t,o,a,l,m){
                var n=this.api(),o=table.ajax.json();
                $( n.column(0).footer()).html(o.Total);
                $( n.column(6).footer()).html(o.total_payments);
                $( n.column(7).footer()).html(o.total_ptd);
                // $(n.column(8).footer()).html(o.management_fee);
                $(n.column(13).footer()).html(o.net_amount);
                $(n.column(14).footer()).html(o.total_syndication_amount);
                $(n.column(15).footer()).html(o.total_syndication_sent);
            }',
            'lengthMenu' => [100, 50]
        ]);
        $tableBuilder->parameters([
            'responsive' => true,
            'autoWidth'  => false,
            'processing' => true,
            'searching'  => false,
            'paging'     => false,
            'ordering'   => false
        ]);
        $tableBuilder = $tableBuilder->columns([
            ['data' => 'DT_RowIndex', 'title' => '#', 'defaultContent' => '', 'searchable' => false, 'className' => 'details-control', 'width' => '5%'],
            ['data' => 'investor', 'title' => 'Investor'],
            ['data' => 'generation_time', 'title' => 'Last Generation Time', 'visible' => false],
            ['data' => 'from_date', 'title' => 'From'],
            ['data' => 'to_date', 'title' => 'To'],
            ['data' => 'notification_recurence', 'title' => 'Notification Recurrence', 'visible' => false],
            ['data' => 'total_payments', 'title' => 'Total Payments', 'className' => 'text-right'],
            ['data' => 'PTD', 'title' => 'PTD', 'className' => 'text-right'],
            ['data' => 'management_fee', 'title' => 'Management Fee', 'className' => 'text-right'],
            ['data' => 'principal', 'title' => 'Principal', 'visible' => false],
            ['data' => 'profit', 'title' => 'Profit', 'visible' => false],
            ['data' => 'participant_rtr', 'title' => 'Participant RTR', 'visible' => false],
            ['data' => 'participant_rtr_balance', 'title' => 'Participant RTR Balance', 'visible' => false],
            ['data' => 'net_amount', 'title' => 'Net Amount', 'className' => 'text-right'],
            ['data' => 'amount', 'title' => 'Syndicate Payment(PTS)', 'className' => 'text-right'],
            ['data' => 'liquidity', 'title' => 'Liquidity', 'className' => 'text-right'],
            ['data' => 'auto_syndicate_payment_action', 'title' => 'Auto ACH', 'width' => '5%'],
            ['data' => 'action', 'title' => 'Action', 'width' => '5%']
        ]);
        $return['investorsList']    = $investorsList;
        $return['tableBuilder']     = $tableBuilder;
        $return['recurrence_types'] = $recurrence_types;
        $return['recurrence_type']  = $recurrence_type;
        $return['paymentDate']      = $paymentDate;
        $return['same_day_button']  = $same_day_button;
        return $return;
    }
    public function iSyndicationPaymentsData($request,$auto_syndicate_payment=null)
    {
        DB::beginTransaction();
        $investors = $this->role->allInvestors()->where('investor_type', 5);
        $recurrence_types = [1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily'];
        if ($auto_syndicate_payment) {
            $investors = $investors->where('auto_syndicate_payment', $auto_syndicate_payment);
            $recurrence_type = [];
            $recurrence_type[] = '3';
            $paymentDate = date('Y-m-d');
            $paymentDateWorkingDay = PayCalc::getWorkingDays($paymentDate, $paymentDate);
            if (empty($paymentDateWorkingDay)) {
                $start = date('Y-m-d', strtotime($paymentDate.'- 5 days'));
                $paymentDateWorkingDay = PayCalc::getWorkingDays($start, $paymentDate);
                $newpaymentDate = max($paymentDateWorkingDay);
                $paymentDate = $newpaymentDate;
            }
            $today = date('Y-m-d');
            $friday = date('Y-m-d', strtotime('friday this week'));
            $HolidayCheckFriday = PayCalc::getWorkingDays($today, $friday);
            if ($HolidayCheckFriday) {
                $friday = max($HolidayCheckFriday);
            }
            if ($friday == $today) {
                $recurrence_type[] = '1';
            }
            $MonthEnd = date('Y-m-t');
            $HolidayCheckMonthEnd = PayCalc::getWorkingDays($today, $MonthEnd);
            if ($HolidayCheckMonthEnd) {
                $MonthEnd = max($HolidayCheckMonthEnd);
            }
            if ($MonthEnd == $today) {
                $recurrence_type[] = '2';
            }
            $investors = $investors->whereIn('notification_recurence', $recurrence_type);
        }
        if ($request) {
            if ($request->investor_id) {
                $investors = $investors->where('id', $request->investor_id);
            }
            if ($request->notification_recurence) {
                $investors = $investors->whereIn('notification_recurence', $request->notification_recurence);
            }
        }
        $data = [];
        $hide = (Settings::value('hide') == 1) ? 1 : 0;
        $total_payments = $management_fee = $net_amount = $total_ptd = $syndication_amount = $syndication_sent_total = 0;
        foreach ($investors as $key => $investor) {
            $workingDayCheck = true;
            $filters = [
                'date_start' => '',
                'date_end' => '',
                'send_mail' => false,
                'merchants' => '',
                'recurrence' => '',
                'hide' => $hide,
                'generationtype' => 1
            ];
            $user_details = UserDetails::where('user_id', $investor['id'])->first();
            $PTD = InvestorTransaction::whereinvestor_id($investor['id']);
            $PTD = $PTD->where('transaction_category', '4');
            $PTD = $PTD->where('transaction_type', '1');
            $PTD = $PTD->where('status', InvestorTransaction::StatusCompleted);
            $PTD = $PTD->sum('amount') * -1;
            $singleRow['SI']                      = $key + 1;
            $singleRow['investor_id']             = $investor['id'];
            $singleRow['PTD']                     = $PTD;
            $singleRow['investor']                = $investor['name'];
            $singleRow['generation_time']         = $investor['generation_time'];
            $singleRow['notification_recurence']  = $recurrence_types[$investor['notification_recurence']];
            $singleRow['liquidity']               = $user_details->liquidity;
            $singleRow['cell_phone']              = $investor['cell_phone'];
            $singleRow['label']                   = $investor->ExcludedLabelName;
            $singleRow['payment_from_date']       = '';
            $singleRow['payment_to_date']         = '';
            $singleRow['total_payments']          = 0;
            $singleRow['management_fee']          = 0;
            $singleRow['net_amount']              = 0;
            $singleRow['principal']               = 0;
            $singleRow['profit']                  = 0;
            $singleRow['participant_rtr']         = 0;
            $singleRow['participant_rtr_balance'] = 0;
            $singleRow['syndication_check']       = false;
            $singleRow['syndication_check_today'] = 'No';
            $singleRow['syndication_type']        = '';
            $singleRow['syndication_amount']      = 0;
            $singleRow['auto_syndicate_payment']  = $investor['auto_syndicate_payment'];
            $singleRow['syndication_sent_today']  = 0;
            if ($user_details->liquidity > 0) {
                $filters = [
                    'date_start'     => '',
                    'date_end'       => '',
                    'send_mail'      => false,
                    'from'           => 'syndication',
                    'merchants'      => '',
                    'label'          => $investor->ExcludedLabelId,
                    'recurrence'     => '',
                    'hide'           => $hide,
                    'generationtype' => 1
                ];
                $singleRow['syndication_check'] = true;
            }
            if (date('Y-m-d', strtotime($singleRow['generation_time'])) == date('Y-m-d')) {
                $singleRow['syndication_sent_today'] = InvestorAchRequest::whereIn('transaction_method', [InvestorAchRequest::MethodByAutomaticDebit, InvestorAchRequest::MethodByAutomaticCredit])->where(['investor_id' => $investor['id'], 'date' => date('Y-m-d'), 'ach_status' => InvestorAchRequest::AchStatusAccepted])->sum('amount');
                if ($singleRow['syndication_sent_today'] > 0) {
                    $singleRow['syndication_check']       = false;
                    $singleRow['syndication_check_today'] = 'Yes';
                    $syndication_sent_total += $singleRow['syndication_sent_today'];
                }
            }
            $ReturnResult = $this->user->singleSyndicatePaymentCalculation($investor, $filters);
            $payments = $ReturnResult['payments']->get();
            $filters  = $ReturnResult['filters'];
            $singleRow['from_date'] = $filters['date_start'];
            $singleRow['to_date']   = $filters['date_end'];
            $workingDays = PayCalc::getWorkingDays($singleRow['from_date'], $singleRow['to_date']);
            if (empty($workingDays)) {
                $workingDayCheck = false;
            }
            if (! empty($payments->toArray())) {
                foreach ($payments as $paymentKey => $single) {
                    $singleRow['total_payments']   += floatval($single->participant_share);
                    $singleRow['management_fee']   += floatval($single->mgmnt_fee);
                    $singleRow['net_amount']       += floatval($single->participant_share);
                    $singleRow['net_amount']       -= floatval($single->mgmnt_fee);
                    $singleRow['principal']        += floatval($single->principal);
                    $singleRow['profit']           += floatval($single->profit);
                    $singleRow['participant_rtr']  += floatval($single->invest_rtr);
                    $singleRow['participant_rtr_balance'] += $single->invest_rtr - ($single->net_balance_1 + $single->participant_share);
                }
                $singleRow['net_amount'] = sprintf('%.2f', $singleRow['net_amount']);
                if ($singleRow['net_amount'] == 0) {
                    $singleRow['syndication_check'] = false;
                }
            } else {
                $singleRow['syndication_check'] = false;
            }
            if (! $workingDayCheck) {
                $singleRow['syndication_check'] = false;
            }
            $type = '';
            $singleRow['syndication_valid'] = false;
            if ($singleRow['syndication_check']) {
                if ($singleRow['net_amount'] < 0) {
                    $type = 'debit';
                    $amount = $singleRow['net_amount'];
                    $BankDetail = Bank::whereinvestor_id($investor['id'])->wheredefault_debit(1)->first();
                } else {
                    $type = 'credit';
                    $amount = ($singleRow['net_amount'] > $singleRow['liquidity']) ? $singleRow['liquidity'] : $singleRow['net_amount'];
                    $BankDetail = Bank::whereinvestor_id($investor['id'])->wheredefault_credit(1)->first();
                }
                $singleRow['Bank'] = $BankDetail ? 'Available' : 'Empty';
                $singleRow['syndication_type'] = $type;
                $singleRow['syndication_amount'] = sprintf('%.2f', $amount);
                if ($singleRow['auto_syndicate_payment'] && $singleRow['cell_phone'] && $singleRow['Bank'] == 'Available') {
                    $syndication_amount += $singleRow['syndication_amount'];
                    $singleRow['syndication_valid'] = true;
                }
            }
            if ($singleRow['syndication_check'] || $singleRow['syndication_check_today'] == 'Yes') {
                $data[] = $singleRow;
                $total_ptd       += $PTD;
                $total_payments  += $singleRow['total_payments'];
                $management_fee  += $singleRow['management_fee'];
                $net_amount      += $singleRow['net_amount'];
            }
        }
        DB::rollback();
        $return = [
            'data'                   => $data,
            'syndication_amount'     => $syndication_amount,
            'total_payments'         => $total_payments,
            'management_fee'         => $management_fee,
            'net_amount'             => $net_amount,
            'total_ptd'              => $total_ptd,
            'syndication_sent_total' => $syndication_sent_total
        ];
        return $return;
    }
    public function iSyndicationPaymentsDataTable($request)
    {
        $get_data = $this->iSyndicationPaymentsData($request);
        $data                   = $get_data['data'];
        $syndication_amount     = $get_data['syndication_amount'];
        $total_payments         = $get_data['total_payments'];
        $management_fee         = $get_data['management_fee'];
        $net_amount             = $get_data['net_amount'];
        $total_ptd              = $get_data['total_ptd'];
        $syndication_sent_total = $get_data['syndication_sent_total'];
        $total_syndication_amount = '<span id="total_syndication_amount" title="Total of sendable syndicate payments">'.FFM::dollar(round($syndication_amount, 2)).'</span>';
        $flag_of_time = date('H', strtotime(ET_To_UTC_TimeOnly('12:00')));
        $syndication_sent_total_data = '';
        if ($syndication_sent_total > 0) {
            $syndication_sent_total_data = 'Total sent today: '.FFM::dollar($syndication_sent_total);
        }
        $now = date('H');
        $data = collect($data)->sortBy('investor')->sortByDesc('auto_syndicate_payment')->toArray();
        return \DataTables::of($data)
        ->editColumn('investor', function ($data) {
            $url = url('admin/investors/portfolio/'.$data['investor_id']);
            
            return '<a target="_blank" href="'.$url.'">'.$data['investor'].'</a>';
        })
        ->editColumn('from_date', function ($data) {
            return FFM::date($data['from_date']);
        })
        ->editColumn('to_date', function ($data) {
            return FFM::date($data['to_date']);
        })
        ->editColumn('generation_time', function ($data) {
            if ($data['generation_time']) {
                return FFM::datetime($data['generation_time']);
            } else {
                return;
            }
        })
        ->editColumn('total_payments', function ($data) {
            return FFM::dollar($data['total_payments']);
        })
        ->editColumn('PTD', function ($data) {
            return FFM::dollar($data['PTD']);
        })
        ->editColumn('management_fee', function ($data) {
            return FFM::dollar($data['management_fee']);
        })
        ->editColumn('net_amount', function ($data) {
            return FFM::dollar($data['net_amount']);
        })
        ->editColumn('amount', function ($data) use ($request) {
            if ($data['syndication_check']) {
                if ($data['syndication_valid']) {
                    $input_type = ' required';
                } else {
                    $input_type = ' disabled';
                }
                $amount = $data['syndication_amount'];
                switch ($data['syndication_type']) {
                    case 'credit':
                    return '<div class="input-group">
                    <span class="input-group-text"><i class="fa fa-dollar"></i></span>
                    <input type="hidden" name="data['.$data['investor_id'].'][type]" value="credit"'.$input_type.'>
                    <input type="number" style="width:100%;text-align:right" class="form-control SyndicateAmount" max="'.$data['liquidity'].'" min="0.01" step="0.01" name="data['.$data['investor_id'].'][amount]" value="'.$amount.'"'.$input_type.'> </div>';
                    break;
                    case 'debit':
                    return '<div class="input-group">
                    <span class="input-group-text"><i class="fa fa-dollar"></i></span>
                    <input type="hidden" name="data['.$data['investor_id'].'][type]" value="debit"'.$input_type.'>
                    <input type="number" style="width:100%;text-align:right" class="form-control SyndicateAmount" max="-0.01" min="-'.$data['liquidity'].'" step="0.01" name="data['.$data['investor_id'].'][amount]" value="'.$amount.'"'.$input_type.'>
                    <a href="#" class="help-link"> <i class="fa fa-question-circle " aria-hidden="true"></i><div class="tool-tip left">Some merchant payment got returned and there are not enough new payment to send. So it will be debited from the syndicate account.</div></a>
                    </div>
                    ';
                    break;
                }
            }
            if ($data['syndication_sent_today']) {
                return FFM::dollar($data['syndication_sent_today']);
            }
        })
        ->editColumn('liquidity', function ($data) {
            return FFM::dollar($data['liquidity']);
        })
        ->editColumn('principal', function ($data) {
            return FFM::dollar($data['principal']);
        })
        ->editColumn('profit', function ($data) {
            return FFM::dollar($data['profit']);
        })
        ->editColumn('participant_rtr', function ($data) {
            return FFM::dollar($data['participant_rtr']);
        })
        ->editColumn('participant_rtr_balance', function ($data) {
            return FFM::dollar($data['participant_rtr_balance']);
        })
        ->editColumn('auto_syndicate_payment_action', function ($data) {
            if ($data['auto_syndicate_payment']) {
                $return = '<a investor_id="'.$data['investor_id'].'" class="changeAutoSyndicateStatus"><label class="label bg-success">ON</label></a> ';
                
                return $return;
            }
            $return = '<a investor_id="'.$data['investor_id'].'" class="changeAutoSyndicateStatus"><label class="label bg-danger">OFF</label></a>';
            
            return $return;
            
            return 'OFF';
        })
        ->editColumn('action', function ($data) use ($flag_of_time, $now) {
            $return = '';
            if (!$data['syndication_valid']) {
                if ($data['syndication_type'] == 'credit') {
                    $return = '<i investor_id="'.$data['investor_id'].'" type="credit"  class="glyphicon glyphicon-send singleSendButton pointer_cursor" title="Send ACH payment to the investor through normal method"></i> &nbsp; &nbsp; &nbsp; &nbsp;';
                    if ($now < $flag_of_time) {
                        $return .= '<i investor_id="'.$data['investor_id'].'" type="same_day_credit"  class="glyphicon glyphicon-sd-video singleSendButton pointer_cursor" title="Send ACH payment to the investor through Same-Day method"></i>';
                    }
                } elseif ($data['syndication_type'] == 'debit') {
                    $return = '<i investor_id="'.$data['investor_id'].'" type="debit"  class="glyphicon glyphicon-send singleSendButton pointer_cursor" title="Send ACH payment from the investor through normal method"></i> &nbsp; &nbsp; &nbsp; &nbsp;';
                    if ($now < $flag_of_time) {
                        $return .= '<i investor_id="'.$data['investor_id'].'" type="same_day_debit"  class="glyphicon glyphicon-sd-video singleSendButton pointer_cursor" title="Send ACH payment from the investor through Same-Day method"></i>';
                    }
                }
            }
            
            return $return;
        })
        ->rawColumns([ 'investor', 'action', 'amount', 'auto_syndicate_payment_action' ])
        ->addIndexColumn()
        ->with('Total', 'Total:')
        ->with('total_payments', FFM::dollar($total_payments))
        ->with('management_fee', FFM::dollar($management_fee))
        ->with('net_amount', FFM::dollar($net_amount))
        ->with('total_ptd', FFM::dollar($total_ptd))
        ->with('total_syndication_amount', $total_syndication_amount)
        ->with('total_syndication_sent', $syndication_sent_total_data)
        ->make(true);
    }
    public function iChangeAutoSyndicatePaymentStatus($request)
    {
        try {
            $investor = User::find($request->investor_id);
            if (!$investor) throw new \Exception("Invalid User Id", 1);
            $auto_syndicate_payment = $investor->auto_syndicate_payment;
            if ($auto_syndicate_payment) {
                $investor->auto_syndicate_payment = 0;
                $investor->update();
                $changed = 'OFF';
            } else {
                $investor->auto_syndicate_payment = 1;
                $investor->update();
                $changed = 'ON';
            }
            $return['investor'] = $investor->name;
            $return['changed']  = $changed;
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iSendSyndicationPayments($request)
    {
        try {
            $message = '';
            $data    = $request['data'];
            if (empty($data)) { throw new Exception('Empty Request', 1); }
            $transactions     = [];
            $transaction_type = '';
            if ($request->type == 'same_day_') {
                $transaction_type = $request->type;
            }
            foreach ($data as $investor_id => $value) {
                if ($value['amount']) {
                    $investor = User::select('id', 'auto_syndicate_payment')->where('id', $investor_id)->first();
                    if ($investor && $investor->auto_syndicate_payment == 1) {
                        $single['amount']      = $value['amount'];
                        $single['investor_id'] = $investor_id;
                        $single['type']        = $transaction_type.$value['type'];
                        $transactions[] = $single;
                    }
                }
            }
            $return_function = $this->iSendSyndicationPaymentsFunction($transactions);
            if (count($transactions) == 0) {
                $request->session()->flash('error', 'No payments to be transferred.');
            }
            if ($return_function['success_message']) {
                $request->session()->flash('message', $return_function['success_count'].' Successfully Completed <br>'.$return_function['success_message']);
            }
            if ($return_function['error_message']) {
                $request->session()->flash('error', $return_function['error_count'].' Transaction Errors <br>'.$return_function['error_message']);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iSendSyndicationPaymentsFunction($transactions)
    {
        $success_message = '';
        $error_message   = '';
        $today        = Carbon::now()->toDateString();
        $payment_date = FFM   ::date($today);
        $data = [];
        $error_count   = 0;
        $success_count = 0;
        foreach ($transactions as $transaction) {
            $order_id = '';
            $auth_code = '';
            $transaction_method = '';
            $status = '';
            $investor = User::select('id', 'name', 'cell_phone')->where('id', $transaction['investor_id'])->first();
            $amount = number_format($transaction['amount'], 2, '.', '');
            if (! $investor->cell_phone) {
                $response = 'No Cell phone';
                $status = 'Failed';
                $error_message .= "<br>\n $response for ".$investor->name;
                $error_count++;
                goto skipFunction;
            }
            $ach_transaction_type = $transaction['type'];
            if ($ach_transaction_type == 'debit' || $ach_transaction_type == 'same_day_debit') {
                $bank = Bank::whereinvestor_id($transaction['investor_id'])->wheredefault_debit(1)->count();
                if ($bank == 0) {
                    $response       = 'No bank for debit';
                    $status         = 'Failed';
                    $error_message .= "<br>\n $response ".$investor->name;
                    $error_count++;
                    goto skipFunction;
                }
            } else {
                $bank = Bank::whereinvestor_id($transaction['investor_id'])->wheredefault_credit(1)->count();
                if ($bank == 0) {
                    $response       = 'No bank for credit';
                    $status         = 'Failed';
                    $error_message .= "<br>\n $response ".$investor->name;
                    $error_count++;
                    goto skipFunction;
                }
            }
            $return_function = $this->iSyndicationPaymentSingleFunction($transaction);
            if (isset($return_function['id'])) {
                $ach_id = $return_function['id'];
            } else {
                $response       = $return_function['message'];
                $status         = 'Failed';
                $error_message .= "<br>\n $response ".$investor->name;
                $error_count++;
                goto skipFunction;
            }
            $investor_ach_request = InvestorAchRequest::find($ach_id);
            if ($investor_ach_request) {
                $auth_code          = $investor_ach_request->auth_code;
                $order_id           = $investor_ach_request->order_id;
                $reason             = $investor_ach_request->reason;
                $ach_status         = $investor_ach_request->AchRequestStatusName;
                $transaction_type   = $investor_ach_request->InvertedTransactionTypeName;
                $transaction_method = $investor_ach_request->TransactionMethodName;
            } else {
                $response = $return_function['message'];
                $status   = 'Failed';
                goto skipFunction;
            }
            if ($return_function['result'] != 'success') {
                $status = 'Declined';
                $error_count++;
            } else {
                $status = 'Accepted';
                $success_count++;
            }
            $data[] = [
                'investor_id'        => $transaction['investor_id'],
                'investor_name'      => $investor->name,
                'status'             => $ach_status,
                'amount'             => $amount,
                'type'               => $transaction_type,
                'response'           => $reason,
                'payment_date'       => $payment_date,
                'order_id'           => $order_id,
                'auth_code'          => $auth_code,
                'transaction_method' => $transaction_method
            ];
            $success_message .= ' '.$return_function['message'];
            continue;
            skipFunction:
            $type = ucwords(str_replace('_', ' ', $transaction['type']));
            $data[] = [
                'investor_id'        => $transaction['investor_id'],
                'investor_name'      => $investor->name,
                'status'             => $status,
                'amount'             => $transaction['amount'],
                'type'               => $type,
                'response'           => $response,
                'payment_date'       => $payment_date,
                'order_id'           => $order_id,
                'auth_code'          => $auth_code,
                'transaction_method' => $transaction_method
            ];
        }
        $mail = $this->iSendSyndicationPaymentsMail($data, $payment_date);
        $return['success_count']   = $success_count;
        $return['error_count']     = $error_count;
        $return['data']            = $data;
        $return['error_message']   = $error_message;
        $return['success_message'] = $success_message;
        
        return $return;
    }
    public function iSendSyndicationPaymentsMail($transactions, $payment_date)
    {
        $emails           = Settings::value('email');
        $emailArray       = explode(',', $emails);
        $message['title'] = 'ACH Syndicate Sent report for '.$payment_date;
        $exportCSV        = $this->iGenerateACHSyndicationCSV($transactions, $payment_date);
        $fileName         = $message['title'].'.csv';
        $msg['atatchment']      = $exportCSV;
        $msg['atatchment_name'] = $fileName;
        $msg['title']           = $message['title'];
        $msg['content']         = 'success';
        $msg['to_mail']         = $emailArray;
        $msg['status']          = 'ach_syndication_sent_report';
        $msg['subject']         = $message['title'];
        $msg['payment_date']    = $payment_date;
        $msg['checked_time']    = FFM::datetime(Carbon::now()->toDateTimeString());
        $msg['unqID']           = unqID();
        $count_total = count($transactions);
        $count_total_processing = $total_processed = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['status'] == 'Processing') {
                $count_total_processing++;
                $total_processed += $transaction['amount'];
            }
        }
        $msg['total_processed']        = FFM::dollar($total_processed);
        $msg['count_total']            = $count_total;
        $msg['count_total_processing'] = $count_total_processing;
        try {
            $email_template = Template::where([ ['temp_code', '=', 'ACHSR'], ['enable', '=', 1], ])->first();
            if ($email_template) {
                if ($email_template->assignees) {
                    $template_assignee = explode(',', $email_template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = $this->role->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $emailArray);
                        $bcc_mails[] = $role_mails;
                    }
                    $msg['bcc'] = Arr::flatten($bcc_mails);
                }
                $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                dispatch($emailJob);
                $msg['bcc']     = [];
                $msg['to_mail'] = $this->admin_email;
                $emailJob = (new CommonJobs($msg));
                dispatch($emailJob);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $return['exportCSV']    = $exportCSV;
        $return['fileName']     = $fileName;
        $return['transactions'] = $transactions;
        return $return;
    }
    public function iGenerateACHSyndicationCSV($data, $payment_date)
    {
        $excel_array[] = ['No', 'Investor Name', 'Investor ID', 'Status', 'Payment', 'Auth Code', 'Payment Date', 'Transaction Type', 'Response', 'Order ID', 'Transaction Method'];
        $i = 1;
        foreach ($data as $key => $tr) {
            $excel_array[$i]['No']                 = $i;
            $excel_array[$i]['Investor Name']      = $tr['investor_name'];
            $excel_array[$i]['Investor ID']        = $tr['investor_id'];
            $excel_array[$i]['Status']             = $tr['status'];
            $excel_array[$i]['Payment']            = FFM::sr($tr['amount']);
            $excel_array[$i]['Auth Code']          = $tr['auth_code'];
            $excel_array[$i]['Payment Date']       = $payment_date;
            $excel_array[$i]['Transaction Type']   = $tr['type'];
            $excel_array[$i]['Response']           = $tr['response'];
            $excel_array[$i]['Order ID']           = $tr['order_id'];
            $excel_array[$i]['Transaction Method'] = $tr['transaction_method'];
            $i++;
        }
        $excel_array[$i]['No']            = null;
        $excel_array[$i]['Investor Name'] = null;
        $excel_array[$i]['Investor ID']   = null;
        $excel_array[$i]['Status']        = 'TOTAL';
        $excel_array[$i]['Payment']       = '=DOLLAR(SUM(E2:E'.$i.'))';
        $export = new Data_arrExport($excel_array);
        return $export;
    }
    public function iSyndicationPaymentSingleFunction($data)
    {
        try {
            DB::beginTransaction();
            $transaction_type = $data['type'];
            if ($transaction_type == 'debit' || $transaction_type == 'same_day_debit') {
                $transaction_method   = InvestorAchRequest::MethodByAutomaticCredit;
                $transaction_category = InvestorAchRequest::CategoryTransferToVelocity;
            } else {
                $transaction_method   = InvestorAchRequest::MethodByAutomaticDebit;
                $transaction_category = InvestorAchRequest::CategoryTransferToBank;
            }
            $ActumRequestData = [
                'investor_id'          => $data['investor_id'],
                'transaction_type'     => $transaction_type,
                'transaction_method'   => $transaction_method,
                'transaction_category' => $transaction_category,
                'request_ip_address'   => \Request::ip(),
                'amount'               => abs($data['amount']),
                'ach_status'           => InvestorAchRequest::AchStatusPending
            ];
            $DuplicateCheck = InvestorAchRequest::where('investor_id', $ActumRequestData['investor_id']);
            $DuplicateCheck = $DuplicateCheck->where('date', date('Y-m-d'));
            $DuplicateCheck = $DuplicateCheck->where('amount', $ActumRequestData['amount']);
            $DuplicateCheck = $DuplicateCheck->first();
            $ActumRequest = new ActumRequest;
            $return_result = $ActumRequest->RequestHandler($ActumRequestData);
            if ($return_result['InvestorAchRequest'] == 'created') {
                DB::commit();
            }
            $return['InvestorAchRequest'] = $return_result['InvestorAchRequest'] ?? null;
            $return['id']                 = $return_result['id'] ?? null;
            $return['message']            = $return_result['result'] ?? null;
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            $investors = User::where('id', $data['investor_id'])->get();
            $hide = (Settings::value('hide') == 1) ? 1 : 0;
            $filters = ['date_start' => '', 'date_end' => '', 'from' => 'syndication', 'send_mail' => true, 'merchants' => '', 'recurrence' => '', 'hide' => $hide, 'generationtype' => 1];
            if (! empty($investors)) {
                $result = $this->user->generatePDFCSV($investors, $filters);
                $return['message'] = $result;
            }
            $return['result'] = 'success';
        } catch (Exception $e) {
            DB::rollback();
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iInvestorSyndicationReport($request,$invester_id)
    {
        $Statement  = Statements::whereuser_id($invester_id)->latest()->first();
        $investor   = User::find($invester_id)->toArray();
        $hide       = (Settings::value('hide') == 1) ? 1 : 0;
        $date_start = $request->date_start ?? $Statement->from_date;
        $date_end   = $request->date_end ?? $Statement->to_date;
        $date_start = ($date_start != '0000-00-00') ? $date_start : '';
        $date_end   = ($date_end != '0000-00-00') ? $date_end : '';
        $filters = [
            'date_start'     => $date_start,
            'date_end'       => $date_end,
            'from'           => 'syndication',
            'send_mail'      => '',
            'merchants'      => [],
            'recurrence'     => '',
            'hide'           => $hide,
            'generationtype' => 0
        ];
        $ALL_merchants = [];
        $ReturnResult = $this->user->SingleInvestorPDFCSVGenerator($investor, $filters);
        $payment1     = $ReturnResult['payments']->get();
        $filters      = $ReturnResult['filters'];
        $merchants11  = $payment1->pluck('merchant_id')->toArray();
        $data = [];
        if (! empty($payment1->toArray())) {
            $options    = '';
            $commonName = '';
            $fileName   = '';
            switch ($filters['recurrence']) {
                case '1':
                $options    = 'last_day';
                $title      = 'Last Day';
                $fileName   = 'last_day_report_'.$investor['id'].'_'.rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end']));
                $commonName = 'last_day_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_report_'.time();
                break;
                case '2':
                $options    = 'last_week';
                $title      = 'Last Week';
                $fileName   = 'last_week_report_'.$investor['id'].'_'.rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end']));
                $commonName = 'last_week_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_week_report_'.time();
                break;
                case '3':
                $options    = 'last_two_week';
                $title      = 'Last Two Week';
                $fileName   = 'last_two_week_report_'.$investor['id'].'_'.rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end']));
                $commonName = 'last_two_week_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_two_week_report_'.time();
                break;
                case '4':
                $options    = 'last_month';
                $title      = 'Last Month';
                $fileName   = 'last_month_report_'.$investor['id'].'_'.rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end']));
                $commonName = 'last_month_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_month_report_'.time();
                break;
                case '5':
                $options    = 'last_year';
                $title      = 'Last Year';
                $fileName   = 'last_year_report_'.$investor['id'].'_'.rand().'_'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_start'])).'_'.date(\FFM::defaultDateFormat('db'), strtotime($date_end));
                $commonName = 'last_year_report/'.date(\FFM::defaultDateFormat('db'), strtotime($filters['date_end'])).'/'.$investor['id'].'/last_year_report_'.time();
                break;
                default:
                $options    = 'Syndication Report for '.$investor['name'];
                $title      = 'Syndication Report for '.$investor['name'];
                $fileName   = 'syndication_report_'.$investor['name'].'/'.time();
                $commonName = 'syndication_report_'.$investor['name'].'/'.time();
                break;
            }
            $investor_type = ($investor['investor_type'] == 1) ? 'Debt' : 'Equity';
            $userId = $investor['id'];
            $whole_portfolio = $investor['whole_portfolio'];
            if ($whole_portfolio == 1) {
                $ALL_merchants = DB::table('merchant_user');
                $ALL_merchants = $ALL_merchants->select('merchant_user.merchant_id', 'merchants.name', 'sub_statuses.name as status_name', 'merchants.last_payment_date', 'rcode.code as last_rcode');
                $ALL_merchants = $ALL_merchants->where('merchant_user.user_id', $userId);
                $ALL_merchants = $ALL_merchants->whereIn('merchant_user.status', [1, 3]);
                $ALL_merchants = $ALL_merchants->whereNotIn('merchant_user.merchant_id', $merchants11);
                $ALL_merchants = $ALL_merchants->join('merchants', 'merchants.id', 'merchant_user.merchant_id');
                $ALL_merchants = $ALL_merchants->leftJoin('rcode', 'rcode.id', 'merchants.last_rcode');
                $ALL_merchants = $ALL_merchants->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');
                $ALL_merchants = $ALL_merchants->get();
                $ALL_merchants = $ALL_merchants->toArray();
            }
            $investments = DB::table('merchant_user');
            $investments = $investments->whereIn('merchant_user.status', [1, 3])->where('merchant_user.user_id', $userId);
            $investments = $investments->select(
                DB::raw('sum(pre_paid) as pre_paid'),
                DB::raw('sum(paid_mgmnt_fee) as paid_mgmnt_fee'),
                DB::raw('sum(paid_participant_ishare) as paid_participant_ishare'),
                DB::raw('sum(amount) as amount'),
                DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as invested_amount'),
                DB::raw('sum(merchant_user.mgmnt_fee) as mgmnt_fee'),
                DB::raw('sum(invest_rtr) as invest_rtr')
            );
            $investments = $investments->leftJoin('users', function ($join) use ($userId) {
                $join->on('users.id', '=', 'merchant_user.user_id');
                $join->where('users.id', $userId);
            });
            $arr    = $investments->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->where('merchants.active_status', 1);
            $array6 = $arr->first();
            $investorArray = [
                'investor_name'  => $investor['name'],
                'email'          => $investor['email'],
                'investor_type'  => $investor_type,
                'brokerage'      => $investor['brokerage'],
                'management_fee' => $investor['management_fee'],
                'startDate'      => $filters['date_start'],
                'endDate'        => $filters['date_end']
            ];
            $total_funded                  = 0;
            $bleded_amount                 = 0;
            $commission_total              = 0;
            $pre_paid_total                = 0;
            $ctd                           = 0;
            $payment                       = 0;
            $commission                    = 0;
            $total_paid_syndication_fee    = 0;
            $total_paid_mgmnt_fee          = 0;
            $total_paid_participant_ishare = 0;
            $total_syndication_fee         = 0;
            $total_mgmnt_fee               = 0;
            $value                         = 0;
            $value1                        = 0;
            $default_pay                   = 0;
            $default_pay_rtr               = 0;
            $default_rate                  = Settings::value('rate');
            $default_rate                  = $default_rate;
            $magt_fee                      = 0;
            $default_payment = Settings::value('default_payment');
            $default_pay_rtr = ParticipentPayment::whereHas('paymentAllInvestors', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
            $default_pay_rtr = $default_pay_rtr->whereHas('merchant', function ($query) {
                $query->whereIn('sub_status_id', [4, 22])->where('active_status', '=', 1);
            });
            $default_pay_rtr = $default_pay_rtr->sum('final_participant_share');
            $array0 = $arr->get();
            $ctd = $array6->paid_participant_ishare - $array6->paid_mgmnt_fee;
            $invested_amount = $array6->invested_amount;
            $array1 = $arr->whereNotIn('merchants.sub_status_id', [4, 22])->get();
            $bleded_amount = 0;
            $total_amount  = 0;
            $invest_rtr    = 0;
            $fees          = 0;
            $amount        = 0;
            $commission    = 0;
            $rate         = Settings::value('rate');
            $amount       = $array6->amount;
            $magt_fee     = $array6->mgmnt_fee;
            $total_rtr    = 0;
            $invest_rtr   = $array6->invest_rtr;
            $invest_rtr   = $invest_rtr - ($invest_rtr * ($rate / 100));
            $total_amount = $total_amount + $invest_rtr;
            $fees = $magt_fee;
            $commission_total += ($amount * ($commission / 100));
            $pre_paid_total = $array6->pre_paid;
            $liquidity_this_investor = UserDetails::where('user_id', $userId)->value('liquidity');
            if (($liquidity_this_investor)) {
                $cash_in_hands = $liquidity_this_investor;
            } else {
                $cash_in_hands = 0;
            }
            if (! empty($payment1)) {
                $totLlc                     = 0;
                $totMgm                     = 0;
                $totPrincipal               = 0;
                $totProfit                  = 0;
                $tinvest_rtr                = 0;
                $totSynd                    = 0;
                $tparticipant_share_balance = 0;
                $totSyndf                   = 0;
            }
            $fileType = 'CSV';
            if ($investorArray['startDate']) {
                $stDate = date(\FFM::defaultDateFormat('db'), strtotime($investorArray['startDate']));
            } else {
                $stDate = '';
            }
            if ($investorArray['endDate']) {
                $edDate = date(\FFM::defaultDateFormat('db'), strtotime(($investorArray['endDate'])));
            } else {
                $edDate = '';
            }
            $data[0] = ['', '', '', '', '', '', '', '', '', '', 'Investor Name', $investorArray['investor_name'], '', ''];
            $data[1] = ['', '', '', '', '', '', '', '', '', '', 'Email', $investorArray['email'], '', ''];
            $data[2] = ['', '', '', '', '', '', '', '', '', '', 'Invested Amount', FFM::dollar($invested_amount), '', ''];
            $data[3] = ['', '', '', '', '', '', '', '', '', '', 'CTD', FFM::dollar($ctd), '', ''];
            $data[4] = ['', '', '', '', '', '', '', '', '', '', 'Generated Date', date(\FFM::defaultDateFormat('db')), '', ''];
            $data[6] = ['', '', '', '', '', '', '', '', '', '', 'From', $stDate, '', '', ''];
            $data[5] = ['', '', '', '', '', '', '', '', '', '', 'To', $edDate, '', '', ''];
            $data[7] = [''];
            $data[8] = ['#', 'Master Sheet Merchant', 'Merchant Id', 'Date', 'Total Payments', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last Rcode', 'Last Payment Date', 'Last Payment Amount', 'Participant RTR', 'Participant RTR Balance'];
            if ($whole_portfolio == 1) {
                $data[8] = ['No.', 'Master Sheet Merchant', 'Status', 'Merchant Id', 'Date', 'Total Payments', 'Management Fee', 'Net Amount', 'Principal', 'Profit', 'Last Rcode', 'Last Payment Date', 'Last Payment Amount', 'Participant RTR', 'Participant RTR Balance'];
            }
            $t_participant = $t_mangt_fee = $t_netamount = $t_profit = $t_principal = $rtr_balance = $t_rtr = $t_mag_amount = $t_net_balance = 0;
            for ($i = 0, $j = 11; $i < count($payment1); $i++, $j++) {
                $t_participant     += $payment1[$i]->participant_share;
                $t_mangt_fee       += $payment1[$i]->mgmnt_fee;
                $t_mag_amount      += $payment1[$i]->mgmnt_fee_amount;
                $t_netamount        = $t_participant - $t_mangt_fee;
                $t_net_balance     += $payment1[$i]->net_balance;
                $t_profit          += $payment1[$i]->profit;
                $t_principal       += $payment1[$i]->principal;
                $t_rtr             += $payment1[$i]->invest_rtr;
                $rtr_balance = $t_rtr - $t_mag_amount - ($t_net_balance + $t_netamount);
                $data[$j]['SI'] = $i + 1;
                $data[$j]['Master Sheet Merchant'] = $payment1[$i]->merchant_name;
                if ($whole_portfolio == 1) {
                    $data[$j]['Status'] = '';
                }
                $data[$j]['Merchant Id']             = $payment1[$i]->merchant_id;
                $data[$j]['Date']                    = FFM::date($payment1[$i]->payment_date);
                $data[$j]['Total Payments']          = FFM::dollar($payment1[$i]->participant_share);
                $data[$j]['Management Fee']          = FFM::dollar($payment1[$i]->mgmnt_fee);
                $data[$j]['Net Amount']              = FFM::dollar($payment1[$i]->participant_share - $payment1[$i]->mgmnt_fee);
                $data[$j]['Principal']               = FFM::dollar($payment1[$i]->principal);
                $data[$j]['Profit']                  = FFM::dollar($payment1[$i]->profit);
                $data[$j]['Last Rcode']              = $payment1[$i]->last_rcode;
                $data[$j]['Last Payment Date']       = $payment1[$i]->last_payment_date ? FFM::date($payment1[$i]->last_payment_date) : '';
                $data[$j]['Last Payment Amount']     = FFM::dollar($payment1[$i]->last_payment_amount);
                $data[$j]['Participant RTR']         = FFM::dollar($payment1[$i]->invest_rtr);
                $data[$j]['Participant RTR Balance'] = FFM::dollar($payment1[$i]->invest_rtr - $payment1[$i]->mgmnt_fee_amount - ($payment1[$i]->net_balance + $payment1[$i]->participant_share - $payment1[$i]->mgmnt_fee));
            }
            $k = count($payment1);
            $d = count($ALL_merchants);
            if ($whole_portfolio == 1) {
                for ($m = 0, $n = $k + 11; $m < count($ALL_merchants); $m++, $n++) {
                    $data[$n] = [$i + 1, $ALL_merchants[$m]->name, $ALL_merchants[$m]->status_name, $ALL_merchants[$m]->merchant_id, '', '', '', '', '', '', isset($ALL_merchants[$m]->last_rcode) ? $ALL_merchants[$m]->last_rcode : '--', isset($ALL_merchants[$m]->last_payment_date) ? FFM::date($ALL_merchants[$m]->last_payment_date) : '--', '', '', ''];
                    $i++;
                }
            }
            if ($whole_portfolio == 1) {
                $data[$k + 11 + $d] = ['', '', '', '', '', FFM::dollar($t_participant), FFM::dollar($t_mangt_fee), FFM::dollar($t_netamount), FFM::dollar($t_principal), FFM::dollar($t_profit), '', '', '', FFM::dollar($t_rtr), FFM::dollar($rtr_balance)];
            } else {
                $data[11 + $k] = ['', '', '', '', FFM::dollar($t_participant), FFM::dollar($t_mangt_fee), FFM::dollar($t_netamount), FFM::dollar($t_principal), FFM::dollar($t_profit), '', '', '', FFM::dollar($t_rtr), FFM::dollar($rtr_balance)];
            }
        }
        $return['data']       =$data;
        $return['date_start'] =$date_start;
        $return['date_end']   =$date_end;
        return $return;
    }
    public function getInvestorAchRequestAll($data = []) 
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $tableData = InvestorAchRequestView::query();
        if (isset($data['ach_status'])) {
            $tableData = $tableData->whereach_status($data['ach_status']);
        }
        if (isset($data['ach_request_status'])) {
            $tableData = $tableData->whereach_request_status($data['ach_request_status']);
        }
        if (isset($data['transaction_type'])) {
            $tableData = $tableData->wheretransaction_type($data['transaction_type']);
        }
        if (isset($data['transaction_method'])) {
            $tableData = $tableData->wheretransaction_method($data['transaction_method']);
        }
        if (isset($data['investor_id'])) {
            $tableData = $tableData->whereinvestor_id($data['investor_id']);
        }
        if (isset($data['order_id'])) {
            $tableData = $tableData->where('order_id', '!=', '');
        }
        if (isset($data['from_date'])) {
            $tableData = $tableData->where('date', '>=', date('Y-m-d', strtotime($data['from_date'])));
        }
        if (isset($data['to_date'])) {
            $tableData = $tableData->where('date', '<=', date('Y-m-d', strtotime($data['to_date'])));
        }
        $totalCountfilterd = $tableData->count();
        $return['data'] = $tableData;
        $return['count'] = $totalCountfilterd;
        return $return;
    }
    public function PortfolioValues($user_id) 
    {
        $investor_id     = $user_id;
        $Investor        = User::select('id','name')->find($investor_id);
        $settings        = Settings::first();
        $default_payment = $settings->default_payment;
        $default_rate    = $settings->rate / 100;
        $liquidity = UserDetails::where('user_id', $user_id)->first()->liquidity??0;
        $MerchantUserSum = MerchantUserView::whereIn('status', [1, 3]);
        $substatus = SubStatus::orderBy('name')->pluck('name', 'id');
        $MerchantUserSum = $MerchantUserSum->where('investor_id',$investor_id);
        $MerchantUserSum = $MerchantUserSum->where('merchants.active_status',1);
        $MerchantUserSum = $MerchantUserSum->join('merchants', 'merchants.id', 'merchant_user_views.merchant_id');
        $MerchantUserSum = $MerchantUserSum->select(
            DB::raw('sum(total_investment) as total_investment'),
            DB::raw('count(merchant_id) as merchant_count'),
            DB::raw('sum(invest_rtr) as total_rtr'),
            DB::raw('sum(amount) as invested_amount'),
            DB::raw('sum((invest_rtr*((mgmnt_fee)/100))) as total_fee'),
            // DB::raw('sum(mgmnt_fee) as mgmnt_fee'),
            // DB::raw('SUM(invest_rtr *('.$default_rate.') ) as default_rate_invest_rtr'),
            DB::raw('sum(under_writing_fee) as under_writing_fee_total'),
            DB::raw('sum(merchant_user_views.up_sell_commission) as up_sell_commission_total'),
            DB::raw('sum(pre_paid) as pre_paid_total'),
            DB::raw('sum(commission_amount) as commission_total'),
            DB::raw('sum(actual_paid_participant_ishare) as paid_participant_share'),
            DB::raw('sum(paid_mgmnt_fee) as paid_mgmnt_fee'),
            DB::raw('sum(net_amount) as ctd'),
            // DB::raw('SUM(IF(actual_paid_participant_ishare>invest_rtr,(actual_paid_participant_ishare-invest_rtr)*(1- (mgmnt_fee)/100),0) ) as overpayment'),
            DB::raw('sum(((((amount*merchants.factor_rate)*(100-mgmnt_fee)/100)-(total_investment)))*IF(merchant_user_views.advance_type="weekly_ach",52,255)/merchants.pmnts)/sum(total_investment)*100 as bleded_i_rate')
        );
        $agent_fee_accounts = $this->role->allAgentFeeAccount()->pluck('id')->toArray();
        if (in_array($investor_id, $agent_fee_accounts)) {
            $MerchantUserSum = $MerchantUserSum->where('actual_paid_participant_ishare', '<>', 0);
        }
        $MerchantUserSum_for_blended = clone $MerchantUserSum;
        $MerchantUserSum = $MerchantUserSum->first();
        $MerchantUserSum_for_blended = $MerchantUserSum_for_blended->whereIn('merchants.sub_status_id', [1, 5, 16, 2, 13, 12])->first();
        $blended_rate = $MerchantUserSum_for_blended->bleded_i_rate;
        $DefaultMerchantUserSum = MerchantUserView::whereIn('status', [1, 3]);
        $DefaultMerchantUserSum = $DefaultMerchantUserSum->where('investor_id',$investor_id);
        $DefaultMerchantUserSum = $DefaultMerchantUserSum->join('merchants', 'merchants.id', 'merchant_user_views.merchant_id');
        $DefaultMerchantUserSum = $DefaultMerchantUserSum->where('merchants.active_status',1);
        $DefaultMerchantUserSum = $DefaultMerchantUserSum->whereIn('merchants.sub_status_id', [4, 22]);
        $DefaultMerchantUserSum = $DefaultMerchantUserSum->where('merchants.old_factor_rate',0);
        $DefaultMerchantUserSum = $DefaultMerchantUserSum->select(
            DB::raw('sum(invest_rtr) as total_default_rtr'),
            DB::raw('sum((invest_rtr*((mgmnt_fee)/100))) as total_default_fee'),
            DB::raw('sum(invest_rtr *('.$default_rate.') ) as default_rate_invest_rtr '),
            DB::raw('sum(total_investment) as total_investment'),
            // DB::raw('sum(((total_investment)-(IF(total_investment<net_amount,total_investment,net_amount)))) as default_amount'),
            DB::raw('sum(invest_rtr-(invest_rtr*mgmnt_fee/100)-(IF(net_amount,net_amount,0))) as total_rtr'),
            // DB::raw('sum(pre_paid) as pre_paid_t'),
            // DB::raw('sum(commission_amount) as commission_total'),
            DB::raw('sum(net_amount) as ctd')
        );
        $DefaultMerchantUserSum=$DefaultMerchantUserSum->first();
        $SettledMerchantUserSum = MerchantUserView::whereIn('status', [1, 3]);
        $SettledMerchantUserSum = $SettledMerchantUserSum->where('investor_id',$investor_id);
        $SettledMerchantUserSum = $SettledMerchantUserSum->join('merchants', 'merchants.id', 'merchant_user_views.merchant_id');
        $SettledMerchantUserSum = $SettledMerchantUserSum->where('merchants.active_status',1);
        $SettledMerchantUserSum = $SettledMerchantUserSum->whereIn('merchants.sub_status_id', [18, 19, 20]);
        $SettledMerchantUserSum = $SettledMerchantUserSum->where('merchants.old_factor_rate',0);
        $SettledMerchantUserSum = $SettledMerchantUserSum->select(
            DB::raw('sum(((invest_rtr)-(invest_rtr*(mgmnt_fee)/100)-(IF(net_amount,net_amount,0)))) as total_rtr'),
        );
        $SettledMerchantUserSum =$SettledMerchantUserSum->first();
        $actual_overpayment = DB::table('payment_investors')->whereuser_id($user_id)->sum('actual_overpayment');
        $carry_overpayment  = DB::table('carry_forwards')->whereinvestor_id($user_id)->where('carry_forwards.type', 1)->sum('amount');
        $principal_investment = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->whereIn('transaction_category', [1, 12])->where('investor_id', $investor_id)->sum('amount');
        $overpayment        = $actual_overpayment+$carry_overpayment;
        $merchant_count    = $MerchantUserSum->merchant_count;
        $total_rtr         = $MerchantUserSum->total_rtr + $overpayment - $MerchantUserSum->total_fee;
        $array['grand_total_rtr']=$total_rtr;
        $default_total_rtr = round($DefaultMerchantUserSum->total_rtr,2);
        $settled_total_rtr = round($SettledMerchantUserSum->total_rtr,2);
        $total_rtr        -= round($default_total_rtr,2);
        $total_rtr        -= round($settled_total_rtr,2);
        $total_investment       = $MerchantUserSum->total_investment;
        $ctd                    = $MerchantUserSum->ctd;
        $paid_participant_share = $MerchantUserSum->paid_participant_share;
        $paid_mgmnt_fee         = $MerchantUserSum->paid_mgmnt_fee;
        $defaulted_ctd  = $DefaultMerchantUserSum->ctd;
        $defaulted_rtr  = $DefaultMerchantUserSum->total_default_rtr - $DefaultMerchantUserSum->total_default_fee;
        $default_amount = $defaulted_rtr - $defaulted_ctd;
        $default_total_investment=$DefaultMerchantUserSum->total_investment;
        if ($default_payment == 1) {
            $default_invested_amount = $default_total_investment - $defaulted_ctd - $overpayment;
            $default_percentage = 0;
            if ($total_investment != 0) {
                $default_percentage = ($default_invested_amount > 0) ? ($default_invested_amount / ($total_investment) * 100) : 0;
            }
        } elseif ($default_payment == 2) {
            $default_percentage = ($total_rtr > 0) ? (($default_amount - $overpayment) / ($total_investment) * 100) : 0;
        }       
        $portfolio_value = (($total_rtr + $liquidity) - $ctd);
        $invested_amount = new MerchantUserView;
        $invested_amount = $invested_amount->where('investor_id', $investor_id);
        $invested_amount = $invested_amount->whereIn('status', [1, 3]);
        $invested_amount = $invested_amount->whereNotIn('sub_status_id', [4, 22]);
        $invested_amount = $invested_amount->sum('total_investment');
        $cost_for_ctd = new MerchantUserView;
        $cost_for_ctd = $cost_for_ctd->where('investor_id', $investor_id);
        $cost_for_ctd = $cost_for_ctd->whereNotIn('sub_status_id', [4, 22]);
        $cost_for_ctd = $cost_for_ctd->sum('paid_principal');
        $current_invested_amount = $invested_amount - $cost_for_ctd;
        if ($current_invested_amount < 0) { $current_invested_amount = 0; }
        $InvestorTransactionQuerry = InvestorTransaction::where('investor_id', $investor_id);
        $InvestorTransactionQuerry = $InvestorTransactionQuerry->where('date', '<', NOW());
        $InvestorTransactionQuerry = $InvestorTransactionQuerry->where('status', InvestorTransaction::StatusCompleted);
        $InvestorTransactionQuerry = $InvestorTransactionQuerry->where('transaction_category', 1);
        $start_date = "'".$InvestorTransactionQuerry->min('date')."'";
        $average = $InvestorTransactionQuerry->sum( DB::raw("(amount* TIMESTAMPDIFF(day,investor_transactions.date,NOW()) / TIMESTAMPDIFF(day,$start_date,NOW()))"));
        $total_profit = new MerchantUser;
        $total_profit = $total_profit->where('user_id', $user_id);
        $total_profit = $total_profit->sum('paid_profit');
        $total_profit = round($total_profit,2);
        $bill_transaction = InvestorTransaction::getTransactionSum($investor_id, 10);
        $bill_transaction = -$bill_transaction;
        $carry_profit = CarryForward::where('type', 2)->where('investor_id', $investor_id)->sum('amount');
        $profit  = $total_profit - $bill_transaction - ($default_total_investment - $defaulted_ctd);
        $profit += $carry_profit;
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        if ($OverpaymentAccount) {
            if ($OverpaymentAccount->id != $user_id) {
                $profit += $overpayment;
            }
        }        
        $PrincipalInvestorTransaction = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->whereIn('transaction_category', [1, 12])->where('investor_id', $user_id)->select(DB::raw('MAX(DATEDIFF(NOW(),date)+1) as days'), DB::raw('sum(amount*(DATEDIFF(NOW(),date)+1)) as tot_amount'))->first();
        $average_principal_investment = 0;
        if ($PrincipalInvestorTransaction) {
            $average_principal_investment = ($PrincipalInvestorTransaction->days != 0) ? $PrincipalInvestorTransaction->tot_amount / $PrincipalInvestorTransaction->days : $PrincipalInvestorTransaction->tot_amount;
        }
        $roi=0;
        if($average_principal_investment){
            $roi =$profit / $average_principal_investment * 100;
        }
        $all_debits = InvestorTransaction::getTransactionSum($investor_id, 0, 1, 1);
        $default_rate_rtr = $MerchantUserSum->default_rate_invest_rtr - $DefaultMerchantUserSum->default_rate_invest_rtr;
        $anticipated_rtr = $total_rtr - $ctd - $default_rate_rtr;
        if ($anticipated_rtr < 0) { $anticipated_rtr = 0; }
        $pending_debit_ach_request  = InvestorAchRequest::whereinvestor_id($investor_id)->whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['debit', 'same_day_debit'])->sum('amount');
        $pending_credit_ach_request = InvestorAchRequest::whereinvestor_id($investor_id)->whereach_request_status(InvestorAchRequest::AchRequestStatusProcessing)->whereIn('transaction_type', ['credit', 'same_day_credit'])->sum('amount');
        $array['merchant_count']                =$merchant_count;
        $array['total_investment']              =$total_investment;
        $array['total_investment_invested']     =$MerchantUserSum->invested_amount;
        $array['total_investment_prepaid']      =$MerchantUserSum->pre_paid_total;
        $array['total_investment_commission']   =$MerchantUserSum->commission_total;
        $array['total_investment_underwriting'] =$MerchantUserSum->under_writing_fee_total;
        $array['total_investment_up_sell']      =$MerchantUserSum->up_sell_commission_total;
        $array['liquidity']                     =$liquidity;
        $array['blended_rate']                  =$blended_rate;
        $array['roi']                           =$roi;
        $array['overpayment_actual']            =$actual_overpayment;
        $array['overpayment_carry']             =$carry_overpayment;
        $array['overpayment']                   =$overpayment;
        $array['total_rtr_default']             =$default_total_rtr;
        $array['total_rtr_default_rate']        =$default_rate_rtr;
        $array['total_rtr_settled']             =$settled_total_rtr;
        $array['total_rtr']                     =$total_rtr;
        $array['default_total_investment']      =$default_total_investment;
        $array['default_ctd']                   =$defaulted_ctd;
        $array['default_investment']            =$default_invested_amount;
        $array['default_percentage']            =$default_percentage;
        $array['ctd']                           =$ctd;
        $array['paid_participant_share']        =$paid_participant_share;
        $array['paid_mgmnt_fee']                =$paid_mgmnt_fee;
        $array['portfolio_value']               =$portfolio_value;
        $array['principal_investment']          =$principal_investment;
        $array['current_invested_amount']       =$current_invested_amount;
        $array['invested_amount']               =$invested_amount;
        $array['cost_for_ctd']                  =$cost_for_ctd;
        $array['average']                       =$average;
        $array['average_principal_investment']  =$average_principal_investment;
        $array['profit']                        =$profit;
        $array['profit_total_profit']           =$total_profit;
        $array['profit_bill_transaction']       =$bill_transaction;
        $array['carry_profit']                  =$carry_profit;
        $array['paid_to_date']                  =-$all_debits;
        $array['anticipated_rtr']               =$anticipated_rtr;
        $array['pending_debit_ach_request']     =$pending_debit_ach_request;
        $array['pending_credit_ach_request']    =$pending_credit_ach_request;
        $array['substatus']                     =$substatus;
        $Investor['data']=$array;
        return $Investor;
    }
    public function getMerchants($data) 
    {
        $AgentFeeAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $AgentFeeAccount->where('user_has_roles.role_id', User::AGENT_FEE_ROLE);
        $AgentFeeAccount = $AgentFeeAccount->first(['users.id']);
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        if ($data['investor_id']) {
            $ret1 = new MerchantUser;
            $ret1 = $ret1->leftJoin('merchants', 'merchants.id', 'merchant_user.merchant_id');
            $ret1 = $ret1->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id');
            $ret1 = $ret1->whereIn('merchant_user.status', [1, 3]);
            $ret1 = $ret1->where('merchants.active_status', 1);
            $ret1 = $ret1->where('merchant_user.user_id', $data['investor_id']);
            $ret1 = $ret1->select(
                'merchants.creator_id',
                'merchants.created_at',
                'merchants.complete_percentage as complete_per',
                'merchants.id',
                'merchants.name',
                'merchants.paid_count',
                'merchants.rtr',
                'merchants.max_participant_fund_per',
                'sub_status_id',
                'merchants.date_funded',
                'merchant_user.commission_per as commission',
                'merchant_user.up_sell_commission_per as up_sell_commission_per',
                'annualized_rate',
                'sub_statuses.name as sub_status_name',
                'merchants.factor_rate',
                'funded',
                'merchant_user.amount',
                'invest_rtr',
                'paid_participant_ishare',
                'actual_paid_participant_ishare',
                'paid_mgmnt_fee',
                'merchants.last_payment_date',
                DB::raw('(actual_paid_participant_ishare/invest_rtr)*100 as complete_percentage'),
                DB::raw('(invest_rtr-actual_paid_participant_ishare) as balance'),
                DB::raw('merchant_user.invest_rtr*merchant_user.mgmnt_fee/100 as mag_fee'),
                DB::raw('(merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission) as tot_investment'),
                DB::raw('((((invest_rtr*(100-merchant_user.mgmnt_fee)/100)-(merchant_user.amount+merchant_user.commission_amount+merchant_user.pre_paid+merchant_user.under_writing_fee+merchant_user.up_sell_commission)))*IF(advance_type="weekly_ach",52,255)/merchants.pmnts) as tot_profit')
            );
        }
        if(isset($data['sub_status_id'])){
            if($data['sub_status_id']){
                if (is_array($data['sub_status_id'])) {
                    if(isset($data['exlcude_sub_status_id'])){
                        $ret1 = $ret1->whereNotIn('sub_status_id', $data['sub_status_id']);
                    } else {
                        $ret1 = $ret1->whereIn('sub_status_id', $data['sub_status_id']);
                    }
                }
            }
        }
        if(isset($data['label'])){
            if($data['label']){
                if (is_array($data['label'])) {
                    $ret1 = $ret1->whereIn('label', $data['label']);
                }
            }
        }
        if(isset($data['completed_percentage_value'])){
            if(isset($data['completed_percentage_option'])){
                if($data['completed_percentage_value']){
                    if($data['completed_percentage_option']){
                        $ret1 = $ret1->where('merchants.complete_percentage',$data['completed_percentage_option'], $data['completed_percentage_value']);
                    }
                }
            }
        }
        if ($AgentFeeAccount) {
            if ($data['investor_id'] == $AgentFeeAccount->id) {
                $ret1 = $ret1->where('paid_participant_ishare', '<>', 0);
            }
        }
        if ($OverpaymentAccount) {
            if ($data['investor_id'] == $OverpaymentAccount->id) {
                $ret1 = $ret1->where('paid_participant_ishare', '<>', 0);
            }
        }
        if(isset($data['overpayment_status'])){
            switch ($data['overpayment_status']) {
                case 'only_balance':
                $ret1 = $ret1->whereRaw(DB::raw('invest_rtr>paid_participant_ishare'));
                break;
                case 'overpayment_only':
                $ret1 = $ret1->whereRaw(DB::raw('invest_rtr<paid_participant_ishare'));
                break;
                case 'exclude_overpayment':
                $ret1 = $ret1->whereRaw(DB::raw('invest_rtr>=paid_participant_ishare'));
                break;
                case 'completed_payment':
                $ret1 = $ret1->whereRaw(DB::raw('invest_rtr=paid_participant_ishare'));
                break;
            }
        }
        $ret1_sum = clone $ret1;
        $ret1_sum = $ret1_sum->select(
            DB::raw('sum(amount) as amount'),
            DB::raw('sum(invest_rtr) as invest_rtr'),
            DB::raw('sum(paid_mgmnt_fee) as paid_mgmnt_fee'),
            DB::raw('sum(merchant_user.invest_rtr*merchant_user.mgmnt_fee/100) as mgmnt_fee_amount'),
            DB::raw('sum(commission_amount) as commission_amount'),
            DB::raw('sum(paid_participant_ishare) as paid_participant_ishare '),
            DB::raw('sum(actual_paid_participant_ishare) as actual_paid_participant_ishare')
        );
        $ret1_sum=$ret1_sum->first();
        $return['list']  = $ret1;
        $return['count'] = $ret1->count();
        $return['sum']   = $ret1_sum;
        return $return;
    }
    public function iMerchantDataTable($request)
    {
        $data = [];
        if ($request['merchant_id']) { $data['merchant_id'] = $request['merchant_id']; }
        if ($request['label']) { $data['label'] = $request['label']; }
        if ($request['sub_status_id']) { $data['sub_status_id'] = $request['sub_status_id']; }
        if ($request['overpayment_status']) { $data['overpayment_status'] = $request['overpayment_status']; }
        if ($request['completed_percentage_value']) { $data['completed_percentage_value'] = $request['completed_percentage_value']; }
        if ($request['completed_percentage_option']) { $data['completed_percentage_option'] = $request['completed_percentage_option']; }
        if ($request['exlcude_sub_status_id']=="true") { $data['exlcude_sub_status_id'] = $request['exlcude_sub_status_id']; }
        $investor_id=NULL;
        if ($request['investor_id']) { 
            $data['investor_id'] = $request['investor_id']; 
            $investor_id=$request['investor_id'];
        }
        $TableData = $this->getMerchants($data);
        $datas = clone $TableData['list'];
        $funded_total     = $TableData['sum']->amount??0;
        $commission_total = $TableData['sum']->commission_amount??0;
        $ctd_total        = $TableData['sum']->actual_paid_participant_ishare??0;
        $mag_fee          = $TableData['sum']->mgmnt_fee_amount??0;
        $rtr_total        = $TableData['sum']->invest_rtr??0;
        $balance          = $rtr_total-$ctd_total;
        return \DataTables::Eloquent($datas)
        ->editColumn('name', function ($data) {
            return '<a target="_blank" href="/admin/merchants/view/'.$data['id'].'">'.$data['name'].'</a>';
        })
        ->addColumn('invest_rtr', function ($data) {
            return FFM::dollar($data['invest_rtr']);
        })
        ->editColumn('sub_status_id', function ($data) {
            return isset($data['sub_status_name']) ? $data['sub_status_name'] : 0;
        })
        ->editColumn('last_payment_date', function ($data) {
            if ($data['last_payment_date']) {
                return date('m/d/Y', strtotime($data['last_payment_date']));
            } else {
                return '';
            }
        })
        ->editColumn('action', function ($merchant, $view_type = 'test') use ($investor_id) {
            if ($investor_id) {
                return ' <a class="btn btn-primary" href="'.route('investor::dashboard::view', ['id' => $merchant['id']]).'">View</a>';
            } else {
                return ' <a class="btn btn-primary" href="/admin/merchants/view/'.$merchant['id'].'">View</a>';
            }
        })
        ->editColumn('date_funded', function ($data) {
            return FFM::date($data['date_funded']);
        })
        ->addColumn('merchant_balance', function ($data) {
            $balance = $data->rtr;
            $balance-= ParticipentPayment::where('merchant_id',$data['id'])->where('model','App\ParticipentPayment')->sum('payment');
            $balance = $balance*$data->max_participant_fund_per/100;
            return FFM::dollar($balance);
        })
        ->editColumn('commission', function ($data) {
            return FFM::percent($data['commission']+$data['up_sell_commission_per']);
        })
        ->editColumn('paid_participant_ishare', function ($data) {
            return FFM::dollar($data['actual_paid_participant_ishare']);
        })
        ->editColumn('balance', function ($data) {
            return FFM::dollar($data['balance']);
        })
        ->editColumn('annualized_rate', function ($data) {
            $annualized_rate = 0;
            if($data['tot_investment']){
                $annualized_rate = $data['tot_profit'] / $data['tot_investment'] * 100;
            }
            return FFM::percent($annualized_rate);
            return '-';
        })
        ->editColumn('complete_per', function ($data) {
            return FFM::percent($data['complete_per']);
        })
        ->editColumn('amount', function ($data) {
            return FFM::dollar($data['amount']);
        })
        ->editColumn('factor_rate', function ($data) {
            return round($data['factor_rate'], 2);
        })
        ->filter(function ($query) {
            if(request()->input('search.value')) {
                $search=request()->input('search.value');
                $query->where('merchants.name' ,'like',"%{$search}%");
            }
        })
        ->addIndexColumn()
        ->rawColumns(['name'])
        ->with('funded_total'    , FFM::dollar($funded_total))
        ->with('commission_total', FFM::dollar($commission_total))
        ->with('rtr_total'       , FFM::dollar($rtr_total))
        ->with('ctd_total'       , FFM::dollar($ctd_total))
        ->with('balance'         , FFM::dollar($balance))
        ->make(true);
    }
    public function getPayments($data) 
    {
        if ($data['investor_id']) {
            $ret1 = new PaymentInvestors;
            $ret1 = $ret1->join('merchants', 'merchants.id', 'payment_investors.merchant_id');
            $ret1 = $ret1->where('merchants.active_status', 1);
            $ret1 = $ret1->where('payment_investors.user_id', $data['investor_id']);
            $ret1 = $ret1->select(
                'merchants.id',
                'payment_investors.user_id',
                'merchants.name',
                DB::raw('sum(payment_investors.participant_share) as participant_share'),
                DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'),
                DB::raw('sum(payment_investors.participant_share-payment_investors.mgmnt_fee) as net_amount'),
                DB::raw('sum(overpayment) as overpayment'),
                DB::raw('sum(principal) as principal'),
                DB::raw('sum(profit) as profit'),
            );
        }
        if (isset($data['exlcude_zero_payment'])) {
            $ret1 = $ret1->where('payment_investors.participant_share','!=',0);
        }
        $ret1_sum = clone $ret1;
        $ret1_sum = $ret1_sum->select(
            DB::raw('sum(participant_share) as participant_share'),
            DB::raw('sum(mgmnt_fee) as mgmnt_fee'),
            DB::raw('sum(participant_share-mgmnt_fee) as net_amount'),
            DB::raw('sum(overpayment) as overpayment'),
            DB::raw('sum(principal) as principal'),
            DB::raw('sum(profit) as profit'),
        );
        $ret1_sum=$ret1_sum->first();
        $ret1 = $ret1->groupBy('payment_investors.merchant_id','payment_investors.user_id');
        $return['list']  = $ret1;
        $return['count'] = $ret1->count();
        $return['sum']   = $ret1_sum;
        return $return;
    }
    public function iPaymentData($request,$tableBuilder) 
    {
        $data = [];
        if ($request['merchant_id']) { $data['merchant_id'] = $request['merchant_id']; }
        if ($request['investor_id']) { $data['investor_id'] = $request['investor_id']; }
        if ($request['exlcude_zero_payment']) { $data['exlcude_zero_payment'] = $request['exlcude_zero_payment']; }
        $TableData = $this->getPayments($data);
        $datas = clone $TableData['list'];
        $participant_share = $TableData['sum']->participant_share;
        $mgmnt_fee         = $TableData['sum']->mgmnt_fee;
        $net_amount        = $TableData['sum']->net_amount;
        $overpayment       = $TableData['sum']->overpayment;
        $principal         = $TableData['sum']->principal;
        $profit            = $TableData['sum']->profit;
        return \DataTables::Eloquent($datas)
        ->addColumn('single', function($single) use ($tableBuilder){
            return view('admin.investors.portfolioComponents.merchant_payment',compact('tableBuilder','single'));
        })
        ->editColumn('name', function ($data) {
            return '<a target="_blank" href="/admin/merchants/view/'.$data->id.'">'.$data->name.'</a>';
        })
        ->editColumn('participant_share', function ($data) {
            return FFM::dollar($data->participant_share);
        })
        ->editColumn('mgmnt_fee', function ($data) {
            return FFM::dollar($data->mgmnt_fee);
        })
        ->editColumn('net_amount', function ($data) {
            return FFM::dollar($data->net_amount);
        })
        ->editColumn('overpayment', function ($data) {
            return FFM::dollar($data->overpayment);
        })
        ->editColumn('principal', function ($data) {
            return FFM::dollar($data->principal);
        })
        ->editColumn('profit', function ($data) {
            return FFM::dollar($data->profit);
        })
        ->filter(function ($query) {
            if(request()->input('search.value')) {
                $search=request()->input('search.value');
                $query->where('merchants.name' ,'like',"%{$search}%");
            }
        })
        ->addIndexColumn()
        ->rawColumns(['name'])
        ->with('participant_share', FFM::dollar($participant_share))
        ->with('mgmnt_fee'        , FFM::dollar($mgmnt_fee))
        ->with('net_amount'       , FFM::dollar($net_amount))
        ->with('overpayment'      , FFM::dollar($overpayment))
        ->with('principal'        , FFM::dollar($principal))
        ->with('profit'           , FFM::dollar($profit))
        ->make(true);
    }
    public function getMerchantPayments($data)
    {
        if ($data['investor_id']) {
            $ret1 = new PaymentInvestorsView;
            $ret1 = $ret1->where('active_status', 1);
            $ret1 = $ret1->where('user_id', $data['investor_id']);
            $ret1 = $ret1->where('merchant_id', $data['merchant_id']);
            $ret1=$ret1->where(function($q) {
                $q->orWhere('participant_share','!=',0);
                $q->orWhere('mgmnt_fee','!=',0);
                $q->orWhere('principal','!=',0);
                $q->orWhere('profit','!=',0);
            });
            $ret1 = $ret1->select(
                'merchant_id',
                'Merchant',
                'user_id',
                'payment',
                'payment_date',
                'participant_share',
                'mgmnt_fee',
                DB::raw('(participant_share-mgmnt_fee) as net_amount'),
                DB::raw('(invest_rtr-sum(participant_share) OVER (ORDER BY payment_date ASC, participent_payment_id DESC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW)) AS balance'),
                DB::raw('IF((invest_rtr-sum(participant_share) OVER (ORDER BY payment_date ASC, participent_payment_id DESC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW))<0, IF( (invest_rtr-sum(participant_share) OVER (ORDER BY payment_date ASC, participent_payment_id DESC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW))*-1>participant_share, (participant_share-mgmnt_fee), ((invest_rtr-sum(participant_share) OVER (ORDER BY payment_date ASC, participent_payment_id DESC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW))*-1)+(1-investor_management_fee)/100 ) , 0 ) AS overpayment'),
                'principal',
                'profit',
            );
        }
        $ret1_sum = clone $ret1;
        $ret1_sum = $ret1_sum->select(
            DB::raw('sum(participant_share) as participant_share'),
            DB::raw('sum(mgmnt_fee) as mgmnt_fee'),
            DB::raw('sum(overpayment) as overpayment'),
            DB::raw('sum(principal) as principal'),
            DB::raw('sum(profit) as profit'),
        );
        $ret1_sum=$ret1_sum->first();
        $return['list']  = $ret1;
        $return['count'] = $ret1->count();
        $return['sum']   = $ret1_sum;
        return $return;
    }
    public function getInvestorReassignmentMerchants($data)
    {
        $returnData = new ReassignHistory;
        $returnData = $returnData->where('investor1', $data['investor_id']);
        $returnData = $returnData->select(
            'merchant_id',
            'investor1',
            DB::raw('sum(amount) as amount'),
            DB::raw('sum(liquidity_change) as liquidity_change'),
        );
        $returnData_sum = clone $returnData;
        $returnData_sum = $returnData_sum->select(
            DB::raw('sum(amount) as amount'),
            DB::raw('sum(liquidity_change) as liquidity_change'),
        );
        $returnData_sum  =$returnData_sum->first();
        $returnData      = $returnData->groupBy('merchant_id');
        $return['list']  = $returnData;
        $return['count'] = $returnData->count();
        $return['sum']   = $returnData_sum;
        return $return;
    }
    public function InvestorReAssignmentMerchantHistoryData($data)
    {
        $returnData = new ReassignHistory;
        $returnData = $returnData->where('investor1', $data['investor_id']);
        $returnData = $returnData->where('merchant_id', $data['merchant_id']);
        $returnData = $returnData->select(
            'merchant_id',
            'investor1',
            'investor2',
            'created_at',
            'creator_id',
            'amount',
            'liquidity_change',
            'liquidity_change',
        );
        $return['list']  = $returnData;
        $return['count'] = $returnData->count();
        return $return;
    }
    public function InvestorTransactionBulkDelete($data)
    {
        try {
            $user_ids = [];
            if($data){
                foreach($data as $transId){
                    $investorTransactions = InvestorTransaction::find($transId);
                    $user_id = $investorTransactions->investor_id;
                    array_push($user_ids,$user_id);
                    $investorTransactions->delete();
                }
                InvestorHelper::update_liquidity($user_ids, 'Delete Investor Transaction');
            } 
            $return = "success";
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $return = $e->getMessage();
        }
        return $return;
    }
    
    public function iMerchantPaymentData($request) 
    {
        $data = [];
        if ($request['merchant_id']) { $data['merchant_id'] = $request['merchant_id']; }
        if ($request['investor_id']) { $data['investor_id'] = $request['investor_id']; }
        $TableData = $this->getMerchantPayments($data);
        $datas = clone $TableData['list'];
        $participant_share = $TableData['sum']->participant_share;
        $mgmnt_fee         = $TableData['sum']->mgmnt_fee;
        $overpayment       = $TableData['sum']->overpayment;
        $principal         = $TableData['sum']->principal;
        $profit            = $TableData['sum']->profit;
        $net_amount        = array_sum(array_column($datas->get()->toArray(), 'net_amount'));
        return \DataTables::Eloquent($datas)
        ->editColumn('payment_date', function ($data) {
            return FFM::date($data['payment_date']);
        })
        ->editColumn('payment', function ($data) {
            return FFM::dollar($data['payment']);
        })
        ->editColumn('participant_share', function ($data) {
            return FFM::dollar($data['participant_share']);
        })
        ->editColumn('mgmnt_fee', function ($data) {
            return FFM::dollar($data['mgmnt_fee']);
        })
        ->editColumn('net_amount', function ($data) {
            return FFM::dollar($data['net_amount']);
        })
        ->editColumn('overpayment', function ($data) {
            return FFM::dollar($data['overpayment']);
        })
        ->editColumn('principal', function ($data) {
            return FFM::dollar($data['principal']);
        })
        ->editColumn('profit', function ($data) {
            return FFM::dollar($data['profit']);
        })
        ->editColumn('balance', function ($data) {
            return FFM::dollar($data['balance']);
        })
        ->filter(function ($query) {
            if(request()->input('search.value')) {
                $search=request()->input('search.value');
                $query->where(function($q) use ($search){
                    $q->where('merchants.name' ,'like',"%{$search}%");
                    $q->oRwhere('merchants.id' ,'like',"%{$search}%");
                    $q->oRwhere('participent_payments.payment' ,'like',"%{$search}%");
                    $q->oRwhere('participent_payments.payment_date' ,'like',"%{$search}%");
                    $q->oRwhere('payment_investors.participant_share' ,'like',"%{$search}%");
                    $q->oRwhere('payment_investors.mgmnt_fee' ,'like',"%{$search}%");
                    $q->oRwhere('payment_investors.overpayment' ,'like',"%{$search}%");
                    $q->oRwhere('payment_investors.principal' ,'like',"%{$search}%");
                    $q->oRwhere('payment_investors.profit' ,'like',"%{$search}%");
                });
            }
        })
        ->addIndexColumn()
        ->rawColumns(['name'])
        ->with('participant_share', FFM::dollar($participant_share))
        ->with('mgmnt_fee'        , FFM::dollar($mgmnt_fee))
        ->with('net_amount'       , FFM::dollar($net_amount))
        ->with('overpayment'      , FFM::dollar($overpayment))
        ->with('principal'        , FFM::dollar($principal))
        ->with('profit'           , FFM::dollar($profit))
        ->make(true);
    }
    public function iInvestorReAssignmentHistoryData($request,$tableBuilder)
    {
        $data = [];
        if ($request['merchant_id']) { $data['merchant_id'] = $request['merchant_id']; }
        if ($request['investor_id']) { $data['investor_id'] = $request['investor_id']; }
        $investor_id=$request['investor_id'];
        $TableData = $this->getInvestorReassignmentMerchants($data);
        $datas     = clone $TableData['list'];
        $amount           = $TableData['sum']->amount;
        $liquidity_change = $TableData['sum']->liquidity_change;
        return \DataTables::Eloquent($datas)
        ->addColumn('single', function($single) use ($tableBuilder,$investor_id){
            return view('admin.investors.portfolioComponents.merchant_reassignment',compact('investor_id','tableBuilder','single'));
        })
        ->editColumn('amount', function ($data) {
            return FFM::dollar($data->amount);
        })
        ->addColumn('merchant_balance', function ($data) {
            return FFM::dollar($data->MerchantUserInvestorFrom->invest_rtr-$data->MerchantUserInvestorFrom->paid_participant_ishare);
        })
        ->editColumn('liquidity_change', function ($data) {
            return FFM::dollar($data->liquidity_change);
        })
        ->addColumn('merchant', function ($data) {
            $merchant= isset($data->merchantData->name) ? $data->merchantData->name : '';
            return '<a target="_blank" href="/admin/merchants/view/'.$data->merchant_id.'">'.$merchant.'</a>';
        })
        ->orderColumn('merchant_balance', function ($query, $order) {
        })
        ->rawColumns(['merchant'])
        ->with('amount'           , FFM::dollar($amount))
        ->with('liquidity_change' , FFM::dollar($liquidity_change))
        ->make(true);
    }
    public function iInvestorReAssignmentMerchantHistoryData($request)
    {
        $data = [];
        if ($request['merchant_id']) { $data['merchant_id'] = $request['merchant_id']; }
        if ($request['investor_id']) { $data['investor_id'] = $request['investor_id']; }
        $TableData = $this->InvestorRepository->InvestorReAssignmentMerchantHistoryData($data);
        $datas     = clone $TableData['list'];
        return \DataTables::Eloquent($datas)
        ->addColumn('investor_from', function ($data) {
            return isset($data->investmentData1->name) ? $data->investmentData1->name : '';
        })
        ->addColumn('investor_to', function ($data) {
            return isset($data->investmentData2->name) ? $data->investmentData2->name : '';
        })
        ->editColumn('amount', function ($data) {
            return FFM::dollar($data->amount);
        })
        ->editColumn('liquidity_change', function ($data) {
            return FFM::dollar($data->liquidity_change);
        })
        ->addColumn('date', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);
            return ($data->created_at != '') ? "<a title='$created_date'>".FFM::datetimetodate($data->created_at).'</a>' : '';
        })
        ->rawColumns(['merchant', 'date'])
        ->make(true);
    }
    public function roiRateList($id,$request,$tableBuilder){
        $recurrence_types = [0 => 'All', 1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily', 4 => 'On Demand'];
        $investor_types   = User::getInvestorType();
        $companies        = $this->role->allSubAdmins()->pluck('name', 'id')->toArray();
        $label            = $this->label->getAll()->pluck('name', 'id');
        $Roles = DB::table('roles');
        $Roles = $Roles->whereIn('id', [User::INVESTOR_ROLE, User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE]);
        $Roles = $Roles->pluck('name', 'id')->toArray();
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getInvestorRoiRates($id,$request->investors);
        }
        $tableBuilder->ajax(['url' => route('admin::investors::investor-pref-return',['id' => $id]), 'data' => 'function(d){ }']);
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'name' => 'DT_RowIndex', 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'], 
        ['data' => 'name', 'name' => 'name', 'title' => 'Syndicate Company Name'], 
        ['data' => 'start_date', 'name' => 'start_date', 'title' => 'Start Date'], 
        ['data' => 'end_date', 'name' => 'end_date', 'title' => 'End Date'], 
        ['data' => 'roi_rate', 'name' => 'roi_rate', 'title' => 'Pref Return(%)'],
        ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
        ]);
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->select(DB::raw("upper(users.name) as name"), 'users.id')
        ->pluck('name','id')->toArray();
        
        $return['tableBuilder']     = $tableBuilder;
        $return['investors']     = $investors;

        return $return;
    }
    public function reserveLiquidityList($id,$request,$tableBuilder)
    {
        $recurrence_types = [0 => 'All', 1 => 'Weekly', 2 => 'Monthly', 3 => 'Daily', 4 => 'On Demand'];
        $investor_types   = User::getInvestorType();
        $companies        = $this->role->allSubAdmins()->pluck('name', 'id')->toArray();
        $label            = $this->label->getAll()->pluck('name', 'id');
        $Roles = DB::table('roles');
        $Roles = $Roles->whereIn('id', [User::INVESTOR_ROLE, User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE]);
        $Roles = $Roles->pluck('name', 'id')->toArray();
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getInvestorReserveLiquidity($id,$request->investors);
        }
        $tableBuilder->ajax(['url' => route('admin::investors::investor-reserve-liquidity',['id' => $id]), 'data' => 'function(d){ }']);
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'searchable' => false, 'name' => 'DT_RowIndex', 'data' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#'], 
            ['data' => 'name', 'name' => 'name', 'title' => 'Investor Name'], 
            ['data' => 'start_date', 'name' => 'start_date', 'title' => 'Start Date'], 
            ['data' => 'end_date', 'name' => 'end_date', 'title' => 'End Date'], 
            ['data' => 'reserve_percentage', 'name' => 'reserve_percentage', 'title' => 'Reserved Percentage'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
        ]);
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->select(DB::raw("upper(users.name) as name"), 'users.id')
        ->pluck('name','id')->toArray();
        
        $return['tableBuilder']     = $tableBuilder;
        $return['investors']     = $investors;

        return $return;
    }
    public function getInvestorRoiRates($id)
    {
        $data = DB::table('investor_roi_rate')->leftJoin('users', 'users.id', '=', 'investor_roi_rate.user_id')->where('investor_roi_rate.user_id',$id);
        $data = $data->select('investor_roi_rate.*','users.name');
       // $data = $data->orderByDesc('investor_roi_rate.id');

        return \DataTables::Collection($data->get())->editColumn('name', function ($data) {
            return "<a> ".strtoupper($data->name)." </a>";
        })->editColumn('start_date', function ($data) {
            if($data->from_date!='0000-00-00'){
            return FFM::date($data->from_date);
            }else{
                return;
            }
        })->editColumn('end_date', function ($data) {
            return FFM::date($data->to_date);
        })->editColumn('roi_rate', function ($data) {
            return FFM::percent($data->roi_rate);
        })->addColumn('action', function ($data) {
            $return = '<a href="'.route('admin::investors::edit-pref-return-data', [ 'user_id' => $data->user_id,'id'=>$data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a> ';
            $return .= Form::open(['route' => ['admin::investors::delete-investor-roi-rate', ['id' => $data->id]], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            return $return;
        })
        ->rawColumns(['name','action'])->addIndexColumn()->make(true);
    }
    public function getInvestorReserveLiquidity($id)
    {
        $data = DB::table('reserve_liquidity')->leftJoin('users', 'users.id', '=', 'reserve_liquidity.user_id')->where('reserve_liquidity.user_id',$id);
        $data = $data->select('reserve_liquidity.*','users.name');
        
        return \DataTables::Collection($data->get())
        ->editColumn('name', function ($data) {
            return strtoupper("<a> $data->name </a>");
        })->editColumn('start_date', function ($data) {
            if($data->from_date!='0000-00-00'){
            return FFM::date($data->from_date);
            }else{
                return;
            }
        })->editColumn('end_date', function ($data) {
            return FFM::date($data->to_date);
        })->editColumn('reserve_percentage', function ($data) {
            return FFM::percent($data->reserve_percentage);
        })->addColumn('action', function ($data) {
            $return = '<a href="'.route('admin::investors::edit-reserve-liquidity-data', [ 'user_id' => $data->user_id,'id'=>$data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a> ';
            $return .= \Form::open(['route' => ['admin::investors::delete-reserve-liquidity', ['id' => $data->id]], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).\Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).\Form::close();
            return $return;
        })
        ->rawColumns(['name','action'])->addIndexColumn()->make(true);
    }
    public function deleteRoiRate($id){
    
        if (InvestorRoiRate::find($id)->delete()) {
            return true;
        } else {
            return false;
        }
    }
    public function deleteReserveLiquidity($id){
        $reserve_liquidity = ReserveLiquidity::find($id);
        $user_id = $reserve_liquidity->user_id;
        if ($reserve_liquidity->delete()) {
            $reseved_liquidities = ReserveLiquidity::where('user_id',$user_id)->get();
            InvestorHelper::update_liquidity($user_id,'');
            return true;
        } else {
            return false;
        }

}
public function investorRoiDetails($id,$user_id){

    $data = DB::table('investor_roi_rate')->where('investor_roi_rate.id',$id)->where('investor_roi_rate.user_id',$user_id);
    $data = $data->first();
    return $data;

}
public function checkValidInvestorForRoi($user_id){
    $valid_user = User::where('id',$user_id)->whereIn('investor_type',[1,3,4])->first();
    if($valid_user){
        return true;
    }else{
        return false;
    }

}
   
    
    public function investorReservedLiquidityDetails($id){
        $data = DB::table('reserve_liquidity')->where('reserve_liquidity.id',$id);
        $data = $data->first();
        return $data;
    }
}
