<?php

namespace App\Helpers\Report;


use App\User;
use App\Merchant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Library\Helpers\ReportTableBuilder;
use DataTables;
use FFM;
use App\Exports\Data_arrExport;
use App\MerchantDetails;

class MerchantReportHelper
{
	public static function defaultRateReport($request,$tableBuilder,$role)
	{
		$page_title = 'Default Rate Report';
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api();o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.net_zero_sum);$(n.column(3).footer()).html(o.total_default_amount);$(n.column(4).footer()).html(o.total_collection);$(n.column(5).footer()).html(o.total_default_rate);$(n.column(6).footer()).html(o.total_collection_rate);
          $(n.column(7).footer()).html(o.total_overpayment);
        }']);
        $tableBuilder->ajax(['url' => route('admin::reports::default-rate-report-records'), 'type' => 'post', 'data' => 'function(d) {
          d._token        = "'.csrf_token().'";
          d.lenders       = $("#lenders").val();
          d.sub_status    = $("#sub_status").val();
          d.funded_date   = $("input[name=funded_date]:checked").val();
          d.investors     = $("#investors").val();
          d.merchants     = $("#merchants").val();
          d.rate_type     = $("#rate_type").val();
          d.velocity      = $("#velocity").val();
          d.from_date     = $("#from_date").val();
          d.to_date       = $("#to_date").val();
          d.active_status = $("input[name=active_status]:checked").val();
          d.overpayment   = $("#overpayment").val();d.days =  $("#days").val();
          d.investor_type = $("#investor_type").val();
          d.velocity_owned = $("input[name=velocity_owned]:checked").val();
        }']);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n          var info = this.dataTable().api().page.info();\n          var page = info.page;\n          var length = info.length;\n          var index = (page * length + (iDataIndex + 1));\n          $('td:eq(0)', nRow).html(index).addClass('txt-center');\n        }", 'pagingType' => 'input', 'aaSorting' => []]);
        $tableBuilder->columns(\MTB::defaultRateReport(null, null, null, null, null, null, null, null, null, null, null, null, null,null, true));
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();
        $lenders2 = $role->allLenders();
        foreach ($lenders2 as $key => $value1) {
            $lenders[$value1->id] = $value1->name;
        }
        $sub_statuses = DB::table('sub_statuses')->orderBy('name')->pluck('name', 'id')->toArray();
        $investor_types = User::getInvestorType();
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0] = 'All';
        ksort($companies);

		return [
            'lenders' => $lenders, 'page_title' => $page_title, 'tableBuilder' => $tableBuilder, 'investors' => $investors, 'sub_statuses' => $sub_statuses, 'companies' => $companies, 'investor_types' => $investor_types
        ];
	}

    public static function defaultReportDownload($request,$merchant,$role)
    {
        $userId = $request->user()->id;
        $investors = $role->allInvestors();
        $filter_investors = $request->investors;
        $filter_merchants = $request->merchants;
        $velocity_owned = isset($request->velocity_owned) ? true : false;
        $lenders = $request->lenders;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $active_status = $request->active_status;
        $over_staus = $request->overpayment;
        $days = $request->days;
        $investor_type = $request->investor_type;
        $to_date = ! empty($to_date) ? $to_date : date('Y-m-d', strtotime('+5 days'));
        $merchants_array = ($merchant->search_default_data($filter_merchants, $filter_investors, $lenders, $from_date, $to_date, $userId, $request->sub_status, $request->funded_date, $request->velocity, $active_status, $days, $investor_type,$velocity_owned));
        $merchants = $merchants_array['merchants'];
        $invsestors_rtr = ($merchants_array['invsestors_rtr'])->toArray();
        $investment_amount = ($merchants_array['investment_amount'])->toArray();
        $overpayment = ($merchants_array['overpayments'])->toArray();
        $merchants = $merchants->get();
        $i = 1;
        $over_p = 0;
        $excel_array[0][] = ['No', 'Investor', 'Default Invested Amount', 'Default RTR Amount', 'Default Invested Rate', 'Default RTR Rate', 'Overpayment'];
        $default_rtr_amount = 0;
        foreach ($merchants as $key => $data) {
            if ($over_staus == 0 || $over_staus == 2) {
                $over_p = isset($overpayment[$data['id']]) ? $overpayment[$data['id']] : 0;
            }
            $default_rtr_amount = $default_rtr_amount + $data['investor_rtr'] - $over_p;
        }
        //$total_default_rtr_amount = FFM::dollar($default_rtr_amount);
        $total_default_rtr_amount = 0;
        $merchants = $merchants->toArray();
        $total_default_invested_amount = 0;
        $total_overpayment = 0;
        if (! empty($merchants)) {
            foreach ($merchants as $key => $data) {
                if ($over_staus == 0 || $over_staus == 2) {
                    $over_p = isset($overpayment[$data['id']]) ? $overpayment[$data['id']] : 0;
                }
                $over_pp = isset($overpayment[$data['id']]) ? $overpayment[$data['id']] : 0;
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['User'] = $data['name'];
                $ctd1 = isset($ctd[$data['id']]) ? $ctd[$data['id']] : 0;
                $invest = isset($investment_amount[$data['id']]) ? $investment_amount[$data['id']] : 0;
               // if($data['ctd_1']>$data['invested_amount']){
               // $excel_array[$i]['Default Invested Amount'] = FFM::dollar(0);
               // }
              //  else{

                $excel_array[$i]['Default Invested Amount'] = FFM::dollar(($data['default_amount'] - $over_p));
                $total_default_invested_amount = $total_default_invested_amount + ($data['default_amount'] - $over_p);
               // }
                //if(($data['investor_rtr'] - $over_p)>0){
                $total_default_rtr_amount = $total_default_rtr_amount+round(($data['investor_rtr'] - $over_p),2);
              //  }
                $excel_array[$i]['Default RTR Amount'] = FFM::dollar($data['investor_rtr'] - $over_p);

               // (($data['investor_rtr'] - $over_p)>0)?FFM::dollar($data['investor_rtr'] - $over_p): FFM::dollar(0);
                $excel_array[$i]['Default Invested Rate'] = ($investment_amount[$data['id']]) ? FFM::percent(($data['default_amount'] - $over_p) / $investment_amount[$data['id']] * 100) : FFM::percent(0);
                $default_rtr_rate =($invsestors_rtr[$data['id']]) ? ($data['investor_rtr'] - $over_p) / $invsestors_rtr[$data['id']] * 100 : 0; 
                $excel_array[$i]['Default RTR Rate'] = ($default_rtr_rate>0) ? FFM::percent($default_rtr_rate) : FFM::percent(0);
                $excel_array[$i]['Overpayment'] = FFM::dollar($over_pp);
                $total_overpayment = $total_overpayment + $over_pp;
                $i++;
            }
        }
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['User'] = null;
        $excel_array[$i]['Default Invested Amount'] = FFM::dollar($total_default_invested_amount);
        $excel_array[$i]['Default RTR Amount'] = FFM::dollar($total_default_rtr_amount);
        $excel_array[$i]['Default Invested Rate'] = null;
        $excel_array[$i]['Default RTR Rate'] = null;
        $excel_array[$i]['Overpayment'] = FFM::dollar($total_overpayment);
        $export = new Data_arrExport($excel_array);
        $fileName = 'Default Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }
    public static function defaultRateMerchantReport($role)
    {
        $page_title = 'Default Rate Report (Merchant)';
        $isos = MerchantDetails::where('agent_name','!=','')->groupBy('agent_name')->pluck('agent_name','agent_name')->toArray();
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0] = 'All';
        ksort($companies);
        $sub_statuses = DB::table('sub_statuses')->orderBy('name')->pluck('name', 'id')->toArray();
        $investor_types = User::getInvestorType();
        return [
            'page_title' => $page_title,
            'investors' => $investors,
            'companies' => $companies,
            'sub_statuses' => $sub_statuses,
            'investor_types' => $investor_types,
            'isos' => $isos
        ];
    }
	public static function defaultRateMerchantReportData($request,$role,$merchant){
		$page_title = 'Default Rate Report (Merchant)';
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $subinvestors = [];
        if ($request->owner) {
            $permission = 0;
            $userId = $request->owner;
            $userId = explode(',', $userId);
        }
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subinvestors = $investor->whereIn('company', $userId);
            if ($request['investor_id']) {
                $subinvestors = $subinvestors->whereIn('id', $request['investor_id']);
            }
            $subinvestors = $subinvestors->pluck('id')->toArray();
        }
        $sDate = $request['start_date_def'];
        $eDate = $request['end_date_def'];
        $sub_status = $request['sub_status'];
        $investors = $request['investors'];
        $isos = $request['isos'];
        $company = $request['company'];
        $funded_date = $request['funded_date'];
        $days = $request['days'];
        $investor_type = $request['investor_type'];
        $search_key = $request['search']['value'] ?? '';
        $velocity_owned = $request['velocity_owned'];
        $data = $merchant->merchantDefaulRate($sDate, $eDate, $investors, $isos, $company, $sub_status, $funded_date, $days, $investor_type,$velocity_owned, $search_key);
        $resarray = $data->get()->toArray();
        $t_rtr = 0;//array_sum(array_column($resarray, 'investor_rtr'));
        $t_inv = 0;//array_sum(array_column($resarray, 'default_amount'));
        foreach($resarray as $res){
          if($res['default_amount']>=0){
              $def_amnt = $res['default_amount'];  
          }else{
              $def_amnt = 0;
          } 
          if($res['investor_rtr']>=0){
              $investor_rtr_amnt = $res['investor_rtr'];  
          }else{
              $investor_rtr_amnt = 0;
          } 

          
          $t_inv = $t_inv+$def_amnt; 
          $t_rtr = $t_rtr+$investor_rtr_amnt; 
        }
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());

        $res_table = Datatables::of($data)->editColumn('Def_Inv', function ($data) use ($request) {
            if($data->default_amount<0){
               return FFM::dollar(0); 
            }
            return FFM::dollar($data->default_amount);
        })
         ->editColumn('date_funded', function ($data) use ($request) {
            return \FFM::date($data->date_funded);
        })
        ->editColumn('Def Rtr', function ($data) use ($request) {
            if($data->investor_rtr<0){
               return FFM::dollar(0); 
            }
            return FFM::dollar($data->investor_rtr);
        })->editColumn('last_status_updated_date', function ($data) use ($request) {
            $created_date = 'Created On '.\FFM::datetime($data->last_status_updated_date).' by '.get_user_name_with_session($data->creator_id);

            return "<a title='$created_date'>".FFM::datetime($data->last_status_updated_date).'</a>';
        });
        $res_table = $res_table->addColumn('rowdetails', function ($data) use ($request, $sDate, $eDate) {
        })->rawColumns(['rowdetails', 'last_status_updated_date'])
        ->with('Total', 'Total:')->with('t_inv', FFM::dollar($t_inv))->with('t_rtr', FFM::dollar($t_rtr))->make(true);

        return $res_table;
	}

    public static function defaultRateMerchantReportExport($request,$merchant)
    {
        $sDate = $request['date_start'];
        $eDate = $request['date_end'];
        $investors = $request['investors'];
        $isos = $request['isos'];
        $company = $request['company'];
        $sub_status = $request['sub_status'];
        $funded_date = $request['funded_date'];
        $days = $request['days'];
        $investor_type = $request['investor_type'];
        $velocity_owned = isset($request['velocity_owned']) ? true : false;
        $data = $merchant->merchantDefaulRate($sDate, $eDate, $investors, $isos, $company, $sub_status, $funded_date, $days, $investor_type,$velocity_owned);
        $resarray = $data->get()->toArray();
        $excel_array[0] = ['Id', 'Merchant','Funded Date', 'Default Date', 'Default Invested Amount', 'Default RTR Amount','Name Of ISO'];
        $default_amount = $investor_rtr = 0;
        for ($i = 0, $j = 2; $i < count($resarray); $i++, $j++) {
            if($resarray[$i]['default_amount']>0){
            $default_amount += $resarray[$i]['default_amount'];
            }
            if($resarray[$i]['investor_rtr']>0){
            $investor_rtr += $resarray[$i]['investor_rtr'];
            }
            $excel_array[$j]['Id'] = $resarray[$i]['id'];
            $excel_array[$j]['Merchant'] = $resarray[$i]['name'];
            $excel_array[$j]['Date Funded'] = FFM::date($resarray[$i]['date_funded']);
            $excel_array[$j]['Default Date'] = FFM::datetime($resarray[$i]['last_status_updated_date']);
            $excel_array[$j]['Default Invested Amount'] = ($resarray[$i]['default_amount']>0) ? FFM::dollar($resarray[$i]['default_amount']) : FFM::dollar(0);
            $excel_array[$j]['Default RTR Amount'] = ($resarray[$i]['investor_rtr'] > 0) ? FFM::dollar($resarray[$i]['investor_rtr']) : FFM::dollar(0);;
            $excel_array[$j]['Name Of ISO'] = $resarray[$i]['agent_name'];
        }
        $excel_array[$j]['Id'] = '';
        $excel_array[$j]['Merchant'] = '';
        $excel_array[$j]['Date Funded'] = '';
        $excel_array[$j]['Default Date'] = '';
        $excel_array[$j]['Default Invested Amount'] = FFM::dollar($default_amount);
        $excel_array[$j]['Default RTR Amount'] = FFM::dollar($investor_rtr);
        $export = new Data_arrExport($excel_array);
        $fileName = 'DefaultRateMerchantReport '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }

	public static function commissionReport($request,$tableBuilder,$role){
        $page_title = 'Upsell Commission Report';

		$tableBuilder->ajax(['url' => route('admin::reports::commission-records'), 'type' => 'post', 'data' => 'function(data){
            data._token = "'.csrf_token().'";
            data.start_date = $("#date_start").val();
            data.end_date = $("#date_end").val();
            data.time_start = $("#time_start:visible").val();
            data.time_end = $("#time_end:visible").val();
            data.date_type = $("#date_type").is(\':checked\') ? true : false;
            data.investors = $("#investors").val();
            data.merchants = $("#merchants").val();
            data.owner = $("#owner").val();
            data.date_type1 = $("#date_type1").is(\':checked\') ? true : false;
            data.velocity_owned = $("input[name=velocity_owned]:checked").val();
        
        }']);

        $tableBuilder->parameters(['serverSide' => false, 'footerCallback' => 'function(t,o,a,l,m){pp = window.LaravelDataTables["dataTableBuilder"] ; if(typeof pp !== "undefined") { var n=this.api();console.log(pp);o=pp.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(4).footer()).html(o.gt_total),$(n.column(5).footer()).html(o.gt_up_sell_commission) }}', 'pagingType' => 'input']);    

        $tableBuilder->columns(\MTB::comissionReport(null, null, null, null, null, true, null, null));
        $userId = $request->user()->id;
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->where('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
        $query->where('company_status',1);
        })->select(DB::raw("upper(users.name) as name"), 'users.id')
        ->pluck('users.name','users.id')->toArray();
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();
        $companies = array_reverse($companies, true);

		return [
			'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'investors' =>$investors,'companies' => $companies
		];
    }

    public static function commissionExport($request,$merchant,$role)
    {
        $date_type = 'false';
        if (isset($request->date_type)) {
            $date_type = $request->date_type;
        }
        if($date_type!='true'){
            $request->time_start = $request->time_end = NULL;
        }
        $fileName = 'Upsell Commission Report_'.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $overpayment_account = User::join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id')->join('roles', 'roles.id', '=', 'user_has_roles.role_id')->whereIn('roles.id', [User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE])->select('users.id')->get()->toArray();
        $overpayment_account_arr = array_unique(array_column($overpayment_account, 'id'));
        $merchants = $request->merchants;
        $startDate = ET_To_UTC_Time($request->date_start.$request->time_start);
        $endDate = ET_To_UTC_Time($request->end_date.$request->time_end);
        $investors = $request->investors;
        $stime = ET_To_UTC_Time($request->date_start.$request->time_start, 'time');
        $etime = ET_To_UTC_Time($request->end_date.$request->time_end, 'time');
        $owner = $request->owner;
        $velocity_owned = false;
        if(isset($request->velocity_owned)){
            $velocity_owned = true;
        }
        $merchants = $merchant->searchForCommissionReport($date_type, $startDate, $endDate, $investors, $merchants, $stime, $etime, $request->date_type1,$owner,$velocity_owned);
        $merchants = $merchants['data']->get();
        if ($request->export_checkbox == '') {
        $sheet[] = (['Merchant Name', 'Merchant ID','investor', 'Invested Amount','Upsell Commission Amount']);
        } else {

        $sheet[] = (['Merchant Name', 'Merchant ID', 'Invested Amount','Upsell Commission Amount']);
        }
        $i = 1;
        $total_invested = $total_upsell_commission_amount=0;
        if (! empty($merchants->toArray())) {
            foreach ($merchants as $merchant) {
            $i++;
            if ($request->export_checkbox == '') {
                $sheet[] = [$merchant->name, $merchant->id,null, FFM::dollar($merchant->commission_amount + $merchant->i_amount + $merchant->pre_paid + $merchant->under_writing_fee+$merchant->m_up_sell_commission),FFM::dollar($merchant->m_up_sell_commission)];
            } else {

                $sheet[] = [$merchant->name, $merchant->id, FFM::dollar($merchant->commission_amount + $merchant->i_amount + $merchant->pre_paid + $merchant->under_writing_fee+$merchant->m_up_sell_commission),FFM::dollar($merchant->m_up_sell_commission)];
            }
            $total_upsell_commission_amount=$total_upsell_commission_amount+$merchant->m_up_sell_commission;
            $total_invested = $total_invested + ($merchant->commission_amount + $merchant->i_amount + $merchant->pre_paid + $merchant->under_writing_fee+$merchant->m_up_sell_commission);
            // investors details

            if ($request->export_checkbox == '') {
                $merchant_investmentData2 = DB::table('merchant_user')->select('merchant_user.user_id as user_id', 'amount', 'commission_amount', 'pre_paid','merchant_user.up_sell_commission', 'merchant_user.under_writing_fee', 'merchant_user.mgmnt_fee', 'merchant_user.invest_rtr')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')
                ->where('merchant_user.up_sell_commission_per','!=',0)
                ->where('merchants.id', $merchant->id);
                $table_field = ($date_type == 'true') ? 'merchants.created_at' : 'merchants.date_funded';
                if ($table_field == 'merchants.created_at') {
                    if ($stime != 0) {
                        $startDate = ($startDate) ? $startDate.' '.$stime.':00' : null;
                    }
                    if ($etime != 0) {
                        $endDate = ($endDate) ? $endDate.' '.$etime.':00' : null;
                    }
                }
                if ($merchants && is_array($merchants)) {
                    $merchant_investmentData2 = $merchant_investmentData2->whereIn('merchants.id', $merchants);
                }
                if ($startDate != null) {
                    $merchant_investmentData2 = $merchant_investmentData2->where($table_field, '>=', $startDate);
                }
                if ($endDate != null) {
                    $merchant_investmentData2 = $merchant_investmentData2->where($table_field, '<=', $endDate);
                }
                
                if ($investors) {
                    $merchant_investmentData2 = $merchant_investmentData2->whereIn('merchant_user.user_id', $investors);
                }
                if (count($overpayment_account_arr) > 0) {
                    $merchant_investmentData2 = $merchant_investmentData2->whereNotIn('merchant_user.user_id', $overpayment_account_arr);
                }
                $merchant_investmentData2 = $merchant_investmentData2->where('active_status', 1)->get();
                foreach ($merchant_investmentData2 as $investmentData) {
                    $user_name = User::where('id', $investmentData->user_id)->first();
                    $sheet[] = ([null, null,$user_name->name,FFM::dollar($investmentData->commission_amount + $investmentData->amount + $investmentData->pre_paid + $investmentData->under_writing_fee+$investmentData->up_sell_commission),
                    FFM::dollar($investmentData->up_sell_commission)]);
                    $i++;
                }
            }

        }
        $sheet[$i]['Merchant Name'] = null;
        $sheet[$i]['Merchant ID'] = null;
        if ($request->export_checkbox == '') {
            $sheet[$i]['Investor'] = null;
        }
        $sheet[$i]['Invested Amount'] = FFM::dollar($total_invested);
        $sheet[$i]['Upsell Commission Amount'] = FFM::dollar($total_upsell_commission_amount);

        }
        $export = new Data_arrExport($sheet);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }

    public static function anticipatedPayment($request,$tableBuilder)
    {
        $page_title = 'Anticipated Payment Report';
        $tableBuilder->ajax(['url' => route('admin::reports::anticipated-payment.records'), 'type' => 'post', 'data' => 'function(data) {
                    data._token = "'.csrf_token().'";
                    data.start_date = $("#date_start").val();
                    data.end_date = $("#date_end").val();
                    data.merchants_id = $("#merchants_id").val();
                    data.modified_term = $("#modified_term").prop("checked");
                }'])->parameters(['aaSorting' => [], 'columnDefs' => '[{orderable: true, targets: [1]}]']);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }", 'footerCallback' => 'function(t,o,a,l,m){
                var n=this.api(),o=table.ajax.json();
                $(n.column(0).footer()).html(o.Total);
                $(n.column(2).footer()).html(o.total_anticipated_amount);
                $(n.column(3).footer()).html(o.total_ctd);
            }', 'pagingType' => 'input', 'dom' => 'lrtip']);
        $tableBuilder->columns(\MTB::getAnticipatedPaymentReportDataTable(null, null, null, null, true));
        $filter['date_start'] = date('Y-m-d');
        $filter['date_end'] = date('Y-m-d', strtotime('1 days', strtotime($filter['date_start'])));
        $unwanted_sub_status = config('custom.unwanted_sub_status');
        $merchants_id = Merchant::whereNotIn('sub_status_id', $unwanted_sub_status)->pluck('name', 'id');
        $request->flash();

        return [
            'page_title' => $page_title, 'tableBuilder' => $tableBuilder, 'filter' => $filter, 'merchants_id' => $merchants_id
        ];
    }

    public static function anticipatedPaymentDownload($request)
    {
        if ($request->has('date_start')) {
            $start_date = $request->date_start;
        } else {
            $start_date = date('Y-m-d');
        }
        if ($request->has('date_end')) {
            $end_date = $request->date_end;
        } else {
            $end_date = date('Y-m-d');
        }
        $fileName = 'Anticipated Payment Report From '.FFM::date($start_date).' To '.FFM::date($end_date).'.csv';
        if ($request->has('modified_term') && $request->modified_term) {
            $modified_term = true;
        } else {
            $modified_term = null;
        }
        $merchants_id = null;
        if ($request->merchants_id) {
            $merchants_id = $request->merchants_id;
        }
        $details = \MTB::getAnticipatedPaymentReport($start_date, $end_date, $modified_term, $merchants_id);
        $excel_array[] = ['No', 'Merchant', 'Anticipated Payment', 'CTD'];
        $i = 1;
        $total_anticipated_payment = 0;
        $total_ctd = 0;
        if (! empty($details)) {
            foreach ($details as $data) {
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Merchant'] = $data->merchant_name;
                $excel_array[$i]['Anticipated Payment'] = FFM::dollar($data->anticipated_amount);
                $excel_array[$i]['CTD'] = FFM::dollar($data->ctd);
                $total_anticipated_payment = $total_anticipated_payment + $data->anticipated_amount;
                $total_ctd = $total_ctd + $data->ctd;
                $i++;
            }
            $excel_array[$i]['No'] = null;
            $excel_array[$i]['Merchant'] = 'TOTAL';
            $excel_array[$i]['Anticipated Payment'] = FFM::dollar($total_anticipated_payment);
            $excel_array[$i]['CTD'] = FFM::dollar($total_ctd);
        }
        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }
}