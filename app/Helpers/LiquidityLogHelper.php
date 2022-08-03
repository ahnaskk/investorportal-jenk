<?php

namespace App\Helpers;

use App\InvestorTransaction;
use App\Library\Repository\Interfaces\ILabelRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\LiquidityLog;
use App\MerchantUser;
use App\User;
use App\UserDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use FFM;
use App\Exports\Data_arrExport;


class LiquidityLogHelper
{
    public function __construct(IRoleRepository $role, ILabelRepository $label)
    {
        $this->role = $role;
        $this->label = $label;
    }
    public static function updateLog($user_ids = '', $description = '', $merchant_id = '')
    {
        $merchant_id = ($merchant_id) ? $merchant_id : 0;
        $liquidityInput = [];
        if (! is_array($user_ids)) {
            $user_ids = ['user_id' => $user_ids];
        }
        $batch_id = rand(10000, 99999);
        foreach ($user_ids as $key => $user_id) {
            $total_credits = InvestorTransaction::where('status', InvestorTransaction::StatusCompleted)->where('investor_id', $user_id)->sum('amount');
            $data = MerchantUser::where('user_id', $user_id)->where('merchant_user.status', '!=', 0)->whereHas('merchant')->select(DB::raw('SUM(paid_mgmnt_fee) as paid_mgmnt_fee'), DB::raw('SUM(amount) as amount'), DB::raw('SUM(commission_amount) as commission_amount'), DB::raw('SUM(under_writing_fee) as under_writing_fee'), DB::raw('SUM(pre_paid) as pre_paid'), DB::raw('SUM(paid_participant_ishare) as paid_participant_ishare'))->first()->toArray();
            $paid_mgmnt_fee = $data['paid_mgmnt_fee'];
            $ctd = $data['paid_participant_ishare'] - $paid_mgmnt_fee;
            $total_funded = $data['amount'];
            $commission_amount = $data['commission_amount'];
            $under_writing_fee = $data['under_writing_fee'];
            $pre_paid_amount = $data['pre_paid'];
            $liquidity = ($total_credits + $ctd) - ($total_funded + $commission_amount) - $pre_paid_amount - $under_writing_fee;
            $liquidity_old = '0.00';
            $user_details = UserDetails::where('user_id', $user_id)->first();
            if ($user_details) {
                $liquidity_old = $user_details->liquidity;
            }
            $liquidity_change = $liquidity - $liquidity_old;
            UserDetails::where('user_id', $user_id)->update(['liquidity' => $liquidity]);
            $aggregated_liquidity = UserDetails::join('users', 'users.id', 'user_details.user_id')->where('company', '>', 0)->groupBy('company')->select(DB::raw('sum(liquidity) as liquidity, company'))->get()->toArray();
            $aggregated_liquidity = json_encode($aggregated_liquidity);
            if (isset($merchant_id)) {
                array_push($liquidityInput, ['member_id' => $user_id, 'final_liquidity' => $liquidity, 'liquidity_change' => $liquidity_change, 'member_type' => 'investor', 'aggregated_liquidity' => $aggregated_liquidity, 'description' => $description, 'merchant_id' => $merchant_id, 'batch_id' => $batch_id, 'creator_id' => (Auth::check()) ? Auth::user()->id : null]);
            } else {
                array_push($liquidityInput, ['member_id' => $user_id, 'final_liquidity' => $liquidity, 'liquidity_change' => $liquidity_change, 'member_type' => 'investor', 'aggregated_liquidity' => $aggregated_liquidity, 'description' => $description, 'batch_id' => $batch_id, 'merchant_id' => $merchant_id, 'creator_id' => (Auth::check()) ? Auth::user()->id : null]);
            }
        }
        if (count($liquidityInput) > 0) {
            LiquidityLog::insert($liquidityInput);
        }
    }
    public function getLiquidityLog($request, $tableBuilder)
    {
        $page_title = 'Liquidity Log';
        $search_date = (isset($request->date)) ? $request->date : '';

        $tableBuilder->ajax(['type' => 'POST','url' => route('admin::reports::liquidity-log'), 'data' => 'function(d){ d._token = $("input[name=_token]").val(); d.start_date = $("#date_start").val(); d.end_date = $("#date_end").val();d.merchant_id = $("#merchant_id").val(); d.investors = $("#investor").val();d.groupbypay = $("#groupbypay").is(":checked");  d.owner = $("#owner").val();d.description = $("#description").val(); d.label = $("#label").val(); d.accountType = $("#accountType").val();d.velocity_owned = $("input[name=velocity_owned]:checked").val();}']);
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(5).footer()).html(o.t_liquidity_change)}', 'fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n             var info = this.dataTable().api().page.info();\n             var page = info.page;\n             var length = info.length;\n             var index = (page * length + (iDataIndex + 1));\n             $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }", 'pagingType' => 'input', 'deferRender' => true]);
        $tableBuilder->columns(\MTB::getLiquidityLogDetails(null, null, null, null, null, null, null, null, true));
        $userId = $request->user()->id;
        $investors = $this->role->allAccountsWithTrashed()->pluck('name', 'id');
        $companies = $this->role->allSubAdmins()->map(function ($cmp) {
            if ($cmp->company_status == 0) {
                $cmp->name .= ' - (Disabled)';
            }
            return $cmp;
        })->pluck('name', 'id')->toArray();
        $companies = array_reverse($companies, true);
        $label = $this->label->getAll()->pluck('name', 'id');
        $descriptions = LiquidityLog::descriptions();
        $active_companies = $this->role->allCompanies()->pluck('id')->toArray();
        $active_company_users = $this->role->getInvestorsFromCompany($active_companies)->pluck('id');
        $Roles = Role::whereIn('roles.id', [User::INVESTOR_ROLE, User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE])->pluck('name', 'id')->toArray();
        return [
            'page_title' => $page_title,
            'tableBuilder' => $tableBuilder,
            'investors' => $investors,
            'search_date' => $search_date,
            'companies' => $companies,
            'label' => $label,
            'descriptions' => $descriptions,
            'active_companies' => $active_companies,
            'active_company_users' => $active_company_users,
            'Roles' => $Roles
        ];
    }
    
    public static function liquidityLogExport($request,$merchant)
    {  
        $fileName = 'LiquidityLog '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $total_liquidity_change = 0;
        $start_date = $request->date_start;
        $end_date = $request->date_end;
        $merchant = $request->merchant_id;
        $investor = $request->investors;
        $groupbypay = ($request->groupbypay=='on')? true :false;
        $owner = $request->owner;
        $description = $request->description;
        $label = $request->label;
        $accountType = $request->accountType;
        $velocity_owned =  false;
        if($request->velocity_owned){
        $velocity_owned =  true;
        }

        $result = \MTB::getLiquidityLogExportDetails($start_date, $end_date, $merchant, $investor, $groupbypay, $owner, $description, $label, $accountType,$velocity_owned);

         $i = 1;
        $data = $result->toArray();	
        if ($groupbypay == 'true') {	
        $excel_array[] = ['No', 'Merchant', 'Description', 'Date', 'Liquidity Change','Investor Liquidity','Company Aggregated Liquidity','Aggregated Liquidity'];
        }else{
        $excel_array[] = ['No','Investor', 'Merchant', 'Description', 'Date', 'Liquidity Change','Investor Liquidity','Company Aggregated Liquidity','Aggregated Liquidity'];
        }
        if (! empty($data)) {
            foreach ($data as $key => $value) {
                $var_liquidity = '';
                if ($groupbypay == 'true') {
                    $log_data = LiquidityLog::select('aggregated_liquidity')->where('batch_id', $value['batch_id'])->orderByDesc('id')->first()->toArray();
                    $array1 = (array) json_decode($log_data['aggregated_liquidity']);
                    foreach ($array1 as $key1 => $value1) {
                        if (isset($value1->company)) {
                            $var_liquidity .= get_user_name_with_session($value1->company).'='.FFM::dollar($value1->liquidity).'  ';
                        } else {
                            $var_liquidity = \FFM::dollar($value1);
                        }
                    }
                } else {
                    $array1 = (array) json_decode($value['aggregated_liquidity']);
                    if (is_array($array1)) {
                        foreach ($array1 as $key1 => $value1) {
                            if (isset($value1->company)) {
                                $var_liquidity .= get_user_name_with_session($value1->company).'='.FFM::dollar($value1->liquidity).'  ';
                            } else {
                                $var_liquidity = \FFM::dollar($value1);
                            }
                        }
                    }
                }

                $aggregated_liquidity = 0;
            if ($groupbypay == 'true') {
                $data = LiquidityLog::select('aggregated_liquidity')->where('batch_id', $value['batch_id'])->orderByDesc('id')->first()->toArray();
                $array2 = (array) json_decode($data['aggregated_liquidity']);
                foreach ($array2 as $key2 => $value2) {
                    if (isset($value2->company)) {
                        $aggregated_liquidity = $aggregated_liquidity + $value2->liquidity;
                    } else {
                        $aggregated_liquidity = $aggregated_liquidity + $value2;
                    }
                }
            } else {
                $array2 = (array) json_decode($value['aggregated_liquidity']);
                if (is_array($array2) && isset($value2->liquidity)) {
                    foreach ($array2 as $key2 => $value2) {
                        if (isset($value2->liquidity)) {
                            $aggregated_liquidity = $aggregated_liquidity + $value2->liquidity;
                        } else {
                            $aggregated_liquidity = $aggregated_liquidity + $value2;
                        }
                    }
                }
            }
            
                $total_liquidity_change = $total_liquidity_change+$value['liquidity_change'];
                $excel_array[$i]['No'] = $i;
                if ($groupbypay != 'true') {
                    if ($value['user_name']) {
                        $investors = strtoupper($value['user_name']);
                    } elseif ($value['investor_id']){
                        $investors = strtoupper(get_user_name_with_session($value['investor_id']));
                    } else {
                        $investors = '';
                    }
                     $excel_array[$i]['Investor'] = $investors;
                }
                $excel_array[$i]['Merchant'] = ($value['merchant_name']!=null) ? strtoupper($value['merchant_name']) : '-';
                $excel_array[$i]['Description'] = $value['description'];
                $excel_array[$i]['Date'] = ($value['created_at']) ? \FFM::datetime($value['created_at']) : '';
                $excel_array[$i]['Liquidity Change'] = \FFM::dollar($value['liquidity_change']);
                $excel_array[$i]['Investor Liquidity'] = \FFM::dollar($value['final_liquidity']);
                $excel_array[$i]['Company Aggregated Liquidity'] = $var_liquidity;
                $excel_array[$i]['Aggregated Liquidity'] = \FFM::dollar($aggregated_liquidity);
                
                
                $i++;
            }
        }
        $excel_array[$i]['No'] = null;
        if ($groupbypay != 'true') {
            $excel_array[$i]['Investor'] = null;
        }        $excel_array[$i]['Merchant'] = null;
        $excel_array[$i]['Description'] = null;
        $excel_array[$i]['Date'] = null;
        $excel_array[$i]['Liquidity Change'] = \FFM::dollar($total_liquidity_change);
        $excel_array[$i]['Investor Liquidity'] = null;
        $excel_array[$i]['Company Aggregated Liquidity'] = null;
        $excel_array[$i]['Aggregated Liquidity'] = null;
                
        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }
    public function getMerchantLiqudityLog($request, $tableBuilder)
    {
        $page_title = 'Merchant Liquidity Log';
        $tableBuilder->ajax(['type' => 'POST','url' => route('admin::reports::liquidity-log-merchant'), 'data' => 'function(d){d._token = $("input[name=_token]").val(); d.start_date = $("#date_start").val(); d.end_date = $("#date_end").val();d.merchant_id = $("#merchant_id").val(); d.investors = $("#investor").val();d.owner = $("#owner").val(); d.description = $("#description").val(); d.groupbypay = $("#groupbypay").is(":checked"); d.accountType = $("#accountType").val();d.velocity_owned = $("input[name=velocity_owned]:checked").val(); }']);
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(4).footer()).html(o.t_liquidity_change)}', 'fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n           var info = this.dataTable().api().page.info();\n           var page = info.page;\n           var length = info.length;\n           var index = (page * length + (iDataIndex + 1));\n           $('td:eq(0)', nRow).html(index).addClass('txt-center');\n         }", 'pagingType' => 'input']);
        $tableBuilder->columns(\MTB::getMerchantLiquidityLogDetails(null, null, null, null, null, null, null, null, true,false));
        $userId = $request->user()->id;
        $investors = $this->role->allAccountsWithTrashed()->pluck('name', 'id');
        $companies = $this->role->allSubAdmins()->map(function ($cmp) {
            if ($cmp->company_status == 0) {
                $cmp->name .= ' - (Disabled)';
            }
            return $cmp;
        })->pluck('name', 'id')->toArray();
        $companies = array_reverse($companies, true);
        $descriptions = LiquidityLog::descriptions();
        $active_companies = $this->role->allCompanies()->pluck('id')->toArray();
        $active_company_users = $this->role->getInvestorsFromCompany($active_companies)->pluck('id');
        $Roles = Role::whereIn('roles.id', [User::INVESTOR_ROLE, User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE])->pluck('name', 'id')->toArray();
        
        return [
            'page_title' => $page_title,
            'tableBuilder' => $tableBuilder,
            'investors' => $investors,
            'companies' => $companies,
            'descriptions' => $descriptions,
            'active_companies' => $active_companies,
            'active_company_users' => $active_company_users,
            'Roles' => $Roles
        ];
    }
}
