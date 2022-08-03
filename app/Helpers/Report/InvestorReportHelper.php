<?php

namespace App\Helpers\Report;

use App\Merchant;
use App\User;
use App\Industries;
use App\Settings;
use App\PaymentInvestors;
use App\Label;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Library\Helpers\ReportTableBuilder;
use App\Exports\Data_arrExport;
use App\Models\Views\ManualLiquidityLogView;
use App\Models\Views\ManualRTRBalanceLogView;
use App\Models\Views\MerchantUserView;
use FFM;
use Illuminate\Support\Arr;
use Carbon\Carbon;
class InvestorReportHelper
{
    public static function investorReport($request,$tableBuilder,$role,$label)
    {
        $page_title = 'Investment Report';
        $selected_investor = ($request->user_id) ? $request->user_id : '';
        
        $tableBuilder->ajax(['url' => route('admin::reports::investor-records'), 'type' => 'post', 'data' => 'function(data){
            data._token = "'.csrf_token().'";
            data.start_date = $("#date_start").val();
            data.end_date = $("#date_end").val();
            data.time_start = $("#time_start:visible").val();
            data.time_end = $("#time_end:visible").val();
            data.date_type = $("#date_type").is(\':checked\') ? true : false;
            data.investors = $("#investors").val();
            data.merchants = $("#merchants").val();
            data.owner = $("#owner").val();
            data.lenders = $("#lenders").val();
            data.industries = $("#industries").val();
            data.statuses = $("#statuses").val();
            data.merchant_date = $("#merchant_date").val();
            data.date_type1 = $("#date_type1").is(\':checked\') ? true : false;
            data.investor_type = $("#investor_type").val();
            data.advance_type = $("#advance_type").val();
            data.sub_status_flag = $("#sub_status_flag").val();
            data.label = $("#label").val();
            data.investor_label = $("#investor_label").val();
            data.active_status =$("input[name=active_status]:checked").val();
            data.velocity_owned = $("input[name=velocity_owned]:checked").val();
        }']);
        $tableBuilder->parameters([
            'serverSide' => true,
            'order' => [4,'desc'],
            'footerCallback' => 'function(t,o,a,l,m){pp = window.LaravelDataTables["dataTableBuilder"] ; if(typeof pp !== "undefined") { var n=this.api();console.log(pp);o=pp.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(5).footer()).html(o.gt_funded),$(n.column(6).footer()).html(o.gt_rtr),$(n.column(7).footer()).html(o.gt_commission),$(n.column(9).footer()).html(o.gt_prepaid_amount),$(n.column(10).footer()).html(o.gt_total),$(n.column(11).footer()).html(o.gt_underwritter),$(n.column(12).footer()).html(o.gt_magnt) }}',
            'pagingType' => 'input'
        ]);

        $tableBuilder->columns(\MTB::investorReport(null, null, null, null, null, null, null, null, true, null, null,null,null));
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
        })->select(DB::raw("upper(users.name) as name"), 'users.id')->pluck('users.name','users.id')->toArray();
        
        $lenders = $role->allLenders()->pluck('name', 'id');
        $industries = Industries::pluck('name', 'id');
        $sub_statuses = DB::table('sub_statuses')->orderBy('name')->get();
        $label = $label->getAll()->pluck('name', 'id');
        $investor_types = User::getInvestorType();
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();
        $companies = array_reverse($companies, true);
        $substatus_flags =  DB::table('sub_status_flags')->pluck('name','id')->toArray();

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'investors' => $investors, 'lenders' => $lenders, 'industries' => $industries, 'sub_statuses' => $sub_statuses, 'selected_investor' => $selected_investor, 'investor_types' => $investor_types, 'companies' => $companies, 'substatus_flags' => $substatus_flags, 'label' => $label
        ];
    }

    public static function investorExport($request,$merchant,$role)
    {
        $date_type = 'false';
        if (isset($request->date_type)) {
            $date_type = $request->date_type;
        }
        if($date_type!='true'){
            $request->time_start = $request->time_end = NULL;
        }
        $advanceTypes = Merchant::getAdvanceTypes();
        $overpayment_account = User::join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id')->join('roles', 'roles.id', '=', 'user_has_roles.role_id')->whereIn('roles.id', [User::OVERPAYMENT_ROLE, User::AGENT_FEE_ROLE])->select('users.id')->get()->toArray();
        $overpayment_account_arr = array_unique(array_column($overpayment_account, 'id'));
        $merchants = $request->merchants;
        $industries = $request->industries;
        $endDate = ! empty($request->end_date) ? $request->end_date : date('Y-m-d', strtotime('+5 days'));
        $startDate = ! empty($request->date_start) ? ET_To_UTC_Time($request->date_start.$request->time_start) : null;
        $statuses = $request->statuses;
        $advance_type = $request->advance_type;
        $lenders = $request->lenders;
        $active = isset($request->active_status) ? $request->active_status : '';
        $investors = $request->investors;
        $stime = ET_To_UTC_Time($request->date_start.$request->time_start, 'time');
        $etime = date('H:i', strtotime('+1 minute', strtotime($request->time_end)));
        $endDate = ET_To_UTC_Time($endDate.$etime);
        $etime = ET_To_UTC_Time($endDate.$etime, 'time');
        $owner = $request->owner;
        $investor_type = $request->investor_type;
        $sub_status_flag = $request->sub_status_flag;
        $label = $request->label;
        $velocity_owned = false;
        if(isset($request->velocity_owned)){
            $velocity_owned = true;
        }
        $investor_label = $request->investor_label;
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        if (empty($permission)) {
            $company_users_q = DB::table('users')->where('company', $userId);
        } else {
            $disabled_companies = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','company')->where('company_status', 0)->pluck('users.id')->toArray();
            $company_users_q = DB::table('users')->whereNotIn('company',$disabled_companies);
        }
        if($active == 1){
            $company_users_q = $company_users_q->where('active_status', 1);
        }
        if($active == 2){
            $company_users_q = $company_users_q->where('active_status', 0);
        }
        if ($investors) {
            $company_users_q = $company_users_q->where('id', $investors);
        }
        if ($owner) {
            $company_users_q = $company_users_q->whereIn('company', $owner);
        }
        if ($active == 1) {
            $users = User::where('active_status', 1)->pluck('id')->toArray();
        } elseif ($active == 2) {
            $users = User::where('active_status', 0)->pluck('id')->toArray();
        } else {
            $users = User::pluck('id')->toArray();
        }
        $sheet = [];
        switch ($request->download) {
            case 'Download report':
                $fileName = 'Investment Report_'.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
                $investment_amount = DB::table('merchant_user')->whereIn('merchant_user.status', [1, 3])->join('merchants', 'merchant_user.merchant_id', 'merchants.id')->join('users', 'users.id', 'merchant_user.user_id');
                if (! empty($lenders)) {
                    $investment_amount = $investment_amount->whereIn('merchants.lender_id', $lenders);
                }
                if (! empty($investors)) {
                    $investment_amount = $investment_amount->whereIn('merchant_user.user_id', $investors);
                }
                if (! empty($industries)) {
                    $investment_amount = $investment_amount->whereIn('merchants.industry_id', $industries);
                }
                if (! empty($merchants)) {
                    $investment_amount = $investment_amount->whereIn('merchants.id', $merchants);
                }
                if($active == "1"){
                    $investment_amount = $investment_amount->where('users.active_status', 1);
                }
                if($active == "2"){
                    $investment_amount = $investment_amount->where('users.active_status', 0);
                }
                $investment_amount = $investment_amount->groupBy('merchant_user.merchant_id')->pluck(DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as total_investment'), 'merchant_user.merchant_id')->toArray();
                $merchants = $merchant->searchForInvestorReport($date_type, $advance_type, $lenders, $startDate, $endDate, $investors, $merchants, $stime, $etime, $request->date_type1, $industries, $owner, $statuses, $investor_type, $sub_status_flag, $label, $investor_label,'',$active,$velocity_owned);
                $merchants = $merchants['data']->get();
                 $overpayments = DB::table('carry_forwards')->where('carry_forwards.type', 1);
                if (! empty($investors)) {
                    $overpayments = $overpayments->whereIn('investor_id', $investors);
                }
                if ($merchants && is_array($merchants)) {
                    $overpayments = $overpayments->whereIn('merchant_id', $merchants);
                }
                
                 $overpayments = $overpayments->groupBy('merchant_id')->pluck(DB::raw('sum(carry_forwards.amount) as overpayment'), 'carry_forwards.merchant_id');

                if ($request->export_checkbox == '') {
                  $sheet[] = (['Merchant Name', 'Merchant ID', 'Industry Name', 'Investor', 'Date', 'Funded Amount', 'RTR', 'Commission Amount', 'Syndication Fee', 'Total Invested', 'Anticipated Management Fee', 'Underwriting Fee', 'Default Amount', 'Default Rate', 'Advance Type']);
                } else {
                    $sheet[] = (['Merchant Name', 'Merchant ID', 'Industry Name', 'Date', 'Funded Amount', 'RTR', 'Commission Amount','Syndication Fee', 'Total Invested', 'Anticipated Management Fee', 'Underwriting Fee', 'Default Amount', 'Default Rate', 'Advance Type']);

                }
                $i = 1;
                $total_funded = $total_rtr = $total_commission_amount = $total_pre_paid = $total_invested = $total_underwriting_fee = $total_mgmnt_fee = $total_default_amount = $total_upsell_commission_amount=$total_default_invest_amount=0;
                 
                if (! empty($merchants->toArray())) {
                    foreach ($merchants as $merchant) {
                        $default_amount = $default_rate = 0;
                        $advance_type = $advanceTypes[$merchant->advance_type];
                        $i++;
                        //echo "ppp";exit;
                        // if(empty($statuses)){
                        $default = [4,22];
                        $overpayment = (isset($overpayments[$merchant->id])) ? $overpayments[$merchant->id] : 0;
                        if (in_array($merchant->sub_status_id, $default)) {
                            $default_amount = ($merchant->default_amount);
                            if($default_amount<0){
                             $default_amount=0;
                            }
                            $default_rate = ($default_amount) / $investment_amount[$merchant->id] * 100;
                        }

                        // }else{
                        //     $selected_default = [4,18,19,20,22];
                        //     foreach($statuses as $st){
                        //         if (in_array($st, $selected_default)) {
                        //             $default[]=$st;
                        //         }
                        //         if(!empty($default)){
                        //             if (in_array($merchant->sub_status_id, $default)) {
                        //             $default_amount = ($merchant->default_amount);
                        //             if($default_amount<0){
                        //              $default_amount=0;
                                     
                        //             }
                        //             $default_rate = ($default_amount) / $investment_amount[$merchant->id] * 100;
                        //              }
                        //         }

                        //     }
                        // }
                        $default_rate = FFM::percent($default_rate);
                        if ($request->export_checkbox == '') {
                            $sheet[] = ([$merchant->name, $merchant->id, $merchant->industry_name, null, FFM::date($merchant->date_funded), FFM::dollar($merchant->i_amount), FFM::dollar($merchant->i_rtr),FFM::dollar($merchant->commission_amount+$merchant->m_up_sell_commission),FFM::dollar($merchant->pre_paid), FFM::dollar($merchant->commission_amount + $merchant->i_amount + $merchant->pre_paid + $merchant->under_writing_fee+$merchant->m_up_sell_commission), FFM::dollar($merchant->mgmnt_fee), FFM::dollar($merchant->under_writing_fee), FFM::dollar($default_amount), $default_rate, $advance_type]);
                        } else {
                            $sheet[] = ([$merchant->name, $merchant->id, $merchant->industry_name, FFM::date($merchant->date_funded), FFM::dollar($merchant->i_amount), FFM::dollar($merchant->i_rtr), FFM::dollar($merchant->commission_amount+$merchant->m_up_sell_commission), FFM::dollar($merchant->pre_paid), FFM::dollar($merchant->commission_amount + $merchant->i_amount + $merchant->pre_paid + $merchant->under_writing_fee+$merchant->m_up_sell_commission), FFM::dollar($merchant->mgmnt_fee), FFM::dollar($merchant->under_writing_fee), FFM::dollar($default_amount), $default_rate, $advance_type]);
                        }
                        $total_funded = $total_funded + $merchant->i_amount;
                        $total_rtr = $total_rtr + $merchant->i_rtr;
                        $total_commission_amount = $total_commission_amount + $merchant->commission_amount;
                        $total_upsell_commission_amount=$total_upsell_commission_amount+$merchant->m_up_sell_commission;
                        $total_pre_paid = $total_pre_paid + $merchant->pre_paid;
                        $total_invested = $total_invested + ($merchant->commission_amount + $merchant->i_amount + $merchant->pre_paid + $merchant->under_writing_fee+$merchant->m_up_sell_commission);
                        $total_underwriting_fee = $total_underwriting_fee + $merchant->under_writing_fee;
                        $total_mgmnt_fee = $total_mgmnt_fee + $merchant->mgmnt_fee;
                        $carry_forwards = DB::table('carry_forwards')->where('merchant_id', $merchant->id)->where('carry_forwards.type', 1)->sum('amount');
                        $total_default_amount = $total_default_amount + $default_amount;
                        $total_7[] = 'N'.$i.':N'.$i;
                        if ($request->export_checkbox == '') {
                            $merchant_investmentData2 = DB::table('merchant_user')->select('merchant_user.user_id as user_id', 'amount', 'commission_amount', 'pre_paid','merchant_user.up_sell_commission', 'merchant_user.under_writing_fee', 'merchant_user.mgmnt_fee', 'merchant_user.invest_rtr')->join('merchants', 'merchants.id', 'merchant_user.merchant_id')->leftjoin('users','users.id','merchant_user.user_id')->where('merchants.id', $merchant->id);
                            $table_field = ($date_type == 'true') ? 'merchants.created_at' : 'date_funded';
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
                            if ($lenders && is_array($lenders)) {
                                $merchant_investmentData2 = $merchant_investmentData2->whereIn('merchants.lender_id', $lenders);
                            }
                            if ($investors) {
                                $merchant_investmentData2 = $merchant_investmentData2->whereIn('merchant_user.user_id', $investors);
                            }
                            if ($active) {
                                $merchant_investmentData2 = $merchant_investmentData2->whereIn('merchant_user.user_id', $users);
                            }
                            if ($industries && is_array($industries)) {
                                $merchant_investmentData2 = $merchant_investmentData2->whereIn('merchants.industry_id', $industries);
                            }
                            if ($statuses && is_array($statuses)) {
                                $merchant_investmentData2 = $merchant_investmentData2->whereIn('merchants.sub_status_id', $statuses);
                            }
                            if ($advance_type && is_array($advance_type)) {
                                $merchant_investmentData2 = $merchant_investmentData2->whereIn('merchants.advance_type', $advance_type);
                            }
                            if (count($overpayment_account_arr) > 0) {
                                $merchant_investmentData2 = $merchant_investmentData2->whereNotIn('merchant_user.user_id', $overpayment_account_arr);
                            }
                            if($velocity_owned){
                                $merchant_investmentData2 = $merchant_investmentData2->where('velocity_owned',1);
                            }
                            $merchant_investmentData2 = $merchant_investmentData2->where('merchants.active_status', 1)->get();
                            foreach ($merchant_investmentData2 as $investmentData) {
                                $user_name = User::where('id', $investmentData->user_id)->first();
                                $sheet[] = ([null, null, null, $user_name->name, null, FFM::dollar($investmentData->amount), FFM::dollar($investmentData->invest_rtr), FFM::dollar($investmentData->commission_amount+$investmentData->up_sell_commission), FFM::dollar($investmentData->pre_paid), FFM::dollar($investmentData->commission_amount + $investmentData->amount + $investmentData->pre_paid + $investmentData->under_writing_fee+$investmentData->up_sell_commission), FFM::dollar(($investmentData->invest_rtr * $investmentData->mgmnt_fee / 100)), FFM::dollar($investmentData->under_writing_fee)]);
                                $i++;
                            }
                        }
                    }
                    $sheet[$i]['Merchant Name'] = null;
                    $sheet[$i]['Merchant ID'] = null;
                    $sheet[$i]['Industry Name'] = null;
                    if ($request->export_checkbox == '') {
                        $sheet[$i]['Investor'] = null;
                    }
                    $sheet[$i]['Date'] = null;
                    $sheet[$i]['Funded Amount'] = FFM::dollar($total_funded);
                    $sheet[$i]['RTR'] = FFM::dollar($total_rtr);
                    $sheet[$i]['Commission Amount'] = FFM::dollar($total_commission_amount+$total_upsell_commission_amount);
                    // $sheet[$i]['Upsell Commission Amount'] = FFM::dollar($total_upsell_commission_amount);
                    $sheet[$i]['Prepaid Amount'] = FFM::dollar($total_pre_paid);
                    $sheet[$i]['Total Invested'] = FFM::dollar($total_invested);
                    $sheet[$i]['Mangnt Fee'] = FFM::dollar($total_mgmnt_fee);
                    $sheet[$i]['Underwriting Fee'] = FFM::dollar($total_underwriting_fee);
                    $sheet[$i]['Default Amount'] = FFM::dollar($total_default_amount);
                    //$total_invested = array_sum($investment_amount);                    
                    $TotalDefaultRate=round($total_default_amount/$total_invested*100,2);
                    $sheet[$i]['Default Rate'] = FFM::percent($TotalDefaultRate);
                }
                $export = new Data_arrExport($sheet);

                return [
                    'fileName' => $fileName,'export' => $export
                ];
            case 'syndicate-report-download':
                $excel_array = [];
                $i = 0;
                //$investors = $role->allInvestors();
                $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
                    $query->where('company_status',1);
                });
                if (! empty($request->investor_type) && is_array($request->investor_type)) {
                    $investors = $investors->whereIn('users.investor_type', $request->investor_type);
                }
                if (! empty($request->investors)) {
                    $investors = $investors->whereIn('users.id', $request->investors);
                }
                if ($request->owner) {
                    $investors = $investors->whereIn('users.company', $request->owner);
                }
                if($active == 1){
                    $investors = $investors->where('users.active_status', 1);
                }
                if($active == 2){
                    $investors = $investors->where('users.active_status', 0);
                }
                if ($request->investor_label != null) {
                    $investor_label = implode(',', $request->investor_label);
                    $investors = $company_users_q->whereRaw('json_contains(label, \'['.$investor_label.']\')');
                }
                $investors = $investors->pluck('users.id')->toArray();
                $velocity_owned = false;
                if(isset($request->velocity_owned)){
                    $velocity_owned = true;
                }
                $reportData = $merchant->searchForSyndicateReport($date_type, $advance_type, $lenders, $startDate, $endDate, $investors, $merchants, $stime, $etime, $request->date_type1, $industries, $owner, $statuses, $investor_type, $sub_status_flag, $label, $request->investor_label,$velocity_owned);
                $excel_array[$i.'+1'] = ['No', 'Merchant Id', 'Merchant Name', 'Funding Date', 'Merchant Status', 'Total Funding', 'Factor Rate', 'Terms(month)', 'Frequency', 'Total RTR', 'Participant', 'Participant Amount', 'Participant %', 'Syndication Fee %', 'Syndication Fee','Underwriting Fee %','Underwriting Fee', 'Commission', 'Total Invested amount', 'Management Fee %', 'Participant Gross RTR', 'Participant Net RTR', 'CTD', 'Management Fee Paid', 'Participant RTR Remaining'];
                if (count($reportData) > 0) {
                    $k = 1;

                    foreach ($reportData as $value) {
                        $pmnts = $value['pmnts'];
                        $term_in_month = '';
                        $advance_type = $advanceTypes[$value['advance_type']];
                        if ($value['advance_type'] != 'weekly_ach') {
                            if ($pmnts % 22 != 0) {
                                $term_in_month = intval($pmnts / 22) + 1;
                            } else {
                                $term_in_month = ($pmnts / 22);
                            }
                        }
                        if ($value['advance_type'] == 'weekly_ach') {
                            if (($pmnts * 7) % 22 != 0) {
                                $term_in_month = intval($pmnts * 7 / 22) + 1;
                            } else {
                                $term_in_month = ($pmnts * 7 / 22);
                            }
                        }
                        if ($term_in_month == 0) {
                            $term_in_month = '0';
                        }
                        $excel_array[$i.'+2']['No'] = $k;
                        $excel_array[$i.'+2']['Merchant Id'] = $value['id'];
                        $excel_array[$i.'+2']['Merchant Name'] = $value['name'];
                        $excel_array[$i.'+2']['Funding Date'] = FFM::date($value['date_funded']);
                        $excel_array[$i.'+2']['Merchant Status'] = $value['merchant_status'];
                        $excel_array[$i.'+2']['Total Funding'] = FFM::dollar($value['funded']);
                        $excel_array[$i.'+2']['Factor Rate'] = ($value['factor_rate']) ? round($value['factor_rate'], 4) : '0.00';
                        $excel_array[$i.'+2']['Terms(month)'] = $term_in_month;
                        $excel_array[$i.'+2']['Freq'] = $advance_type;
                        $excel_array[$i.'+2']['Total RTR'] = FFM::dollar($value['rtr']);
                        $excel_array[$i.'+2']['Participant'] = $value['investor_name'];
                        $excel_array[$i.'+2']['Participant Amount'] = FFM::dollar($value['amount']);
                        $excel_array[$i.'+2']['Participant %'] = FFM::percent(($value['amount'] / $value['funded']) * 100);
                        $excel_array[$i.'+2']['Syndicate Fee %'] = FFM::percent($value['syndication_fee_percentage']);
                        $excel_array[$i.'+2']['Syndicate Fee'] = FFM::dollar($value['pre_paid']);
                        $excel_array[$i.'+2']['Underwriting Fee %'] = FFM::percent($value['under_writing_fee_per']);
                        $excel_array[$i.'+2']['Underwriting Fee'] = FFM::dollar($value['under_writing_fee']);
                        $excel_array[$i.'+2']['Commission'] = FFM::dollar($value['commission_amount']+$value['up_sell_commission']);
                        // $excel_array[$i.'+2']['Upsell Commission'] = FFM::dollar($value['up_sell_commission']);
                        $excel_array[$i.'+2']['Total Invested amount'] = FFM::dollar($value['invested_amount']);
                        $excel_array[$i.'+2']['Managemnt Fee %'] = FFM::percent($value['mgmnt_fee']);
                        $excel_array[$i.'+2']['Participant Gross RTR'] = FFM::dollar($value['invest_rtr']);
                        $excel_array[$i.'+2']['Participant Net RTR'] = FFM::dollar($value['invest_rtr'] - ($value['invest_rtr'] * $value['mgmnt_fee'] / 100));
                        $excel_array[$i.'+2']['Participant Net Paid-To-Date'] = FFM::dollar($value['actual_paid_participant_ishare']-$value['paid_mgmnt_fee']);
                        $excel_array[$i.'+2']['Managment Fee Paid'] = FFM::dollar($value['paid_mgmnt_fee']);
                        $excel_array[$i.'+2']['Participant RTR Remaining'] = FFM::dollar($value['invest_rtr'] - $value['actual_paid_participant_ishare']);
                        $i++;
                        $k++;
                    }
                    $i++;
                }
                $fileName = 'syndicate-Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
                $export = new Data_arrExport($excel_array);

                return [
                    'fileName' => $fileName,'export' => $export
                ];
        }
    }

    public static function investorAssignmentReport($request,$tableBuilder,$role)
    {
        $selected_investor = ($request->user_id) ? $request->user_id : '';
        $sdate = ($request->sdate) ? $request->sdate : '';
        $edate = ($request->edate) ? $request->edate : '';
        $page_title = 'Investor Assignment Report';
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        
        $tableBuilder->ajax(['url' => route('admin::reports::get-investor-assign-report-records'), 'type' => 'post', 'data' => 'function(data) {
	            data._token = "'.csrf_token().'";
	            data.start_date = $("#date_start").val();
	            data.end_date = $("#date_end").val();
	            data.investors = $("#investors").val();
	            data.merchants = $("#merchants").val();
            }']);
        $tableBuilder->columns(\MTB::investorAssignmentReport(null, null, null, null, true));
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(3).footer()).html(o.participant_amount)}', 'drawCallback' => "function(){ $('[data-toggle=\"popover\"]').popover();}", 'pagingType' => 'input']);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n             var info = this.dataTable().api().page.info();\n             var page = info.page;\n             var length = info.length;\n             var index = (page * length + (iDataIndex + 1));\n             $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }"]);
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

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'selected_investor' => $selected_investor, 'sdate' => $sdate, 'edate' => $edate
        ];
    }

    public static function reAssignmentHistory($request,$tableBuilder,$role)
    {
        $page_title = 'Reassignment Report';

        $tableBuilder->ajax([
            'url' => route('admin::reports::get-reassign-report-records'),
            'type' => 'post',
            'data' => 'function(data) {
	            data._token = "'.csrf_token().'";
	            data.start_date = $("#date_start").val();
	            data.end_date = $("#date_end").val();
	            data.investors = $("#investors").val();
	            data.merchants = $("#merchants").val();
            }',
        ]);
        $tableBuilder->parameters([
            'fnCreatedRow' => "function (nRow, aData, iDataIndex) {
                                    var info = this.dataTable().api().page.info();
                                    var page = info.page;    
                                    var length = info.length;  
                                    var index = (page * length + (iDataIndex + 1));
                                    $('td:eq(0)', nRow).html(index).addClass('txt-center');
                                 }",
            'pagingType' => 'input',
            'serverSide' => false,
        ]);
        $tableBuilder->columns([
            [
                'data' => 'DT_RowIndex', 
                'name' => 'DT_RowIndex',
                'defaultContent' => '',
                'title' => '#'
            ],
            ['data' => 'merchant', 'name' => 'merchant', 'title' => 'Merchant'],
            [
                'orderable' => false,
                'data' => 'investor_from',
                'name' => 'investor_from',
                'defaultContent' => '',
                'title' => 'Investor From',
            ],
            ['data' => 'investor_to', 'name' => 'investor_to', 'title' => 'Investor To'],
            ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
            [
                'orderable' => false,
                'data' => 'liquidity_change',
                'name' => 'liquidity_change',
                'defaultContent' => '',
                'title' => 'Liquidity Change',
            ],
            [
                'orderable' => true,
                'data' => 'investor1_final_liquidity',
                'name' => 'investor1_final_liquidity',
                'defaultContent' => '',
                'title' => 'Investor1 Final Liquidity',
            ],
            [
                'orderable' => true,
                'data' => 'investor2_final_liquidity',
                'name' => 'investor2_final_liquidity',
                'defaultContent' => '',
                'title' => 'investor2 Final Liquidity',
            ],
            ['data' => 'date', 'name' => 'date', 'defaultContent' => '', 'title' => 'Date'],
            //['data' => 'action', 'name' => 'action', 'orderable' => false, 'title' => 'Action', 'visible' => false],
        ]);
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

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title
        ];
    }

    public static function liquidityReport($request,$tableBuilder,$role)
    {
        if ($request->investor_filter) {
            $type = ($request->investor_filter == 'subadmin') ? $request->investor_filter : '';
        }
        $page_title = 'Liquidity Report';

        $type = 'subadmin';
        if ($type) {
            $tableBuilder->ajax(['url' => route('admin::reports::liquidity-report-records'), 'type' => 'post', 'data' => 'function(data){
                    data._token = "'.csrf_token().'";
                    data.start_date = $("#date_start").val();
                    data.end_date = $("#date_end").val();
                    data.type="subadmin";
                    data.active_status = $("input[name=active_status]:checked").val();
                    data.company = $("#company").val();
                    data.velocity_owned = $("input[name=velocity_owned]:checked").val();
                }']);
        } else {
            $tableBuilder->ajax(['url' => route('admin::reports::liquidity-report'), 'data' => 'function(d){ d.start_date = $("#date_start").val(); d.end_date = $("#date_end").val();d.active_status = $("input[name=active_status]:checked").val(); d.company = $("#company").val();}']);
        }
        $tableBuilder->parameters(['order' => [[4, 'desc']], 'footerCallback' => 'function(t,o,a,l,m){var n=this.api();o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.total_rtr_bal);$(n.column(3).footer()).html(o.total_ctd),$(n.column(4).footer()).html(o.total_credit),$(n.column(5).footer()).html(o.total_commission),$(n.column(6).footer()).html(o.total_fund),$(n.column(7).footer()).html(o.total_prepaid),$(n.column(8).footer()).html(o.total_liquidity),$(n.column(9).footer()).html(o.total_underwriting_fee)}', 'pagingType' => 'input']);
        $tableBuilder->columns(\MTB::liquidityReport(null, null, $type, null, null,false, true));
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();

        return [
            'page_title' => $page_title, 'tableBuilder' => $tableBuilder, 'companies' => $companies
        ];
    }
    
    public static function equityInvestorReport($request,$tableBuilder,$role)
    {
        $page_title = 'Equity Investor Report';

        $tableBuilder->ajax(['url' => route('admin::reports::equity-investor-report-records'), 'type' => 'post', 'data' => 'function(data) {
            data._token = "'.csrf_token().'";
            data.investors = $("#investors").val();
        }']);
        $tableBuilder->parameters(['serverSide' => false,'footerCallback' => 'function(t,o,a,l,m){ if(typeof table !== "undefined") {  var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.total_credit_amount);$(n.column(3).footer()).html(o.total_portfolio_value);$(n.column(4).footer()).html(o.total_velocity_profit);$(n.column(5).footer()).html(o.total_investor_porfit); }}', 'order' => [[2, 'desc']], 'drawCallback' => "function(){ if(typeof popover == 'function') {   $('[data-toggle=\"popover\"]').popover();}}"]);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n                 var info = this.dataTable().api().page.info();\n                 var page = info.page;\n                 var length = info.length;\n                 var index = (page * length + (iDataIndex + 1));\n                 $('td:eq(0)', nRow).html(index).addClass('txt-center');\n               }", 'pagingType' => 'input']);
        $tableBuilder->columns(\MTB::equityInvestorReport(null, true));
        $investor = $role->allInvestors();
        $investor = $investor->where('investor_type', 2);
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();

        return [
            'page_title' => $page_title, 'tableBuilder' => $tableBuilder, 'investors' => $investors
        ];
    }

    public static function investorInterestAccuredReport($request,$tableBuilder,$role)
    {
        $page_title = 'Accrued Pref Return';

        $tableBuilder->ajax(['url' => route('admin::reports::investor-interest-accured-report-records'), 'type' => 'post', 'data' => 'function(data){
            data._token = "'.csrf_token().'";
            data.investors = $("#investors").val();
            data.date_end = $("#date_end").val();
            data.date_start = $("#date_start").val();
        }']);
        $tableBuilder->parameters(['order' => [[3, 'desc']], 'footerCallback' => 'function(t,o,a,l,m){ if(typeof table !== "undefined") {n=this.api();o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.total_credit);$(n.column(4).footer()).html(o.total_interest_accrued)}}', 'pagingType' => 'input']);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n                 var info = this.dataTable().api().page.info();\n                 var page = info.page;\n                 var length = info.length;\n                 var index = (page * length + (iDataIndex + 1));\n                 $('td:eq(0)', nRow).html(index).addClass('txt-center');\n               }"]);
        $tableBuilder->columns(\MTB::investorInterestAccuredReport(null, null, null, true));
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
        $merchant = Merchant::where('active_status', 1);
        if (empty($permission)) {
            $merchant->whereHas('investmentData', function ($q) use ($subinvestors, $permission) {
                if (empty($permission)) {
                    $q->whereIn('user_id', $subinvestors);
                }
            });
        }
        $merchants = $merchant->pluck('name', 'id');
        $investors = User::where('users.investor_type', 1)->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();

        return[
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'investors' => $investors, 'merchants' => $merchants
        ];
    }

    public static function totalPortfolioEarningsReport($request,$tableBuilder,$role)
    {
        $page_title = 'Total Portfolio Earnings';
        $tableBuilder->ajax(['url' => route('admin::reports::dept-investor-report-records'), 'type' => 'post', 'data' => 'function(data) {
            data._token = "'.csrf_token().'";
            data.investors = $("#investors").val();
        }']);
        $tableBuilder->parameters(['serverSide' => false, 'footerCallback' => 'function(t,o,a,l,m){ if(typeof table !== "undefined") { var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.total_credit_amount); $(n.column(6).footer()).html(o.total_portfolio);$(n.column(4).footer()).html(o.total_bills);$(n.column(5).footer()).html(o.total_distributions); $(n.column(3).footer()).html(o.total_portfolio_earning);  }}', 'drawCallback' => "function(){ { if(typeof table !== \"undefined\") {   $('[data-toggle=\"popover\"]').popover();}}}"]);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n                 var info = this.dataTable().api().page.info();\n                 var page = info.page;\n                 var length = info.length;\n                 var index = (page * length + (iDataIndex + 1));\n                 $('td:eq(0)', nRow).html(index).addClass('txt-center');\n               }", 'pagingType' => 'input', 'stateSave'=>true]);
        $tableBuilder->columns(\MTB::totalPortfolioEarnings(null, true));
        $investor = $role->allInvestors();
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();

        return [
            'page_title' => $page_title, 'tableBuilder' => $tableBuilder, 'investors' => $investors
        ];
    }

    public static function overPaymentReport($request,$tableBuilder,$role)
    {
        $page_title = 'Overpayment Report';
        $permission = ($request->user()->hasRole(['company'])) ? 0 : 1;
        $userId = $request->user()->id;
        $sdate = $request->start_date;
        $edate = $request->end_date;
        $subinvestors = [];
        if (empty($permission)) {
            $investor = $role->allInvestors();
            $subadmininvestor = $investor->whereIn('company', $userId);
            foreach ($subadmininvestor as $key1 => $value) {
                $subinvestors[] = $value->id;
            }
        }

        $tableBuilder->ajax(['url' => route('admin::reports::overpayment-report-records'), 'type' => 'post', 'data' => 'function(data){
            data._token = "'.csrf_token().'";
            data.start_date = $("#date_start").val();
            data.end_date = $("#date_end").val();
            data.merchants = $("#merchants").val();
            data.sub_statuses = $("#sub_statuses").val();
            data.investors = $("#investors").val();
            data.company = $("#company").val();
            data.lenders = $("#lenders").val();
            data.velocity_owned = $("input[name=velocity_owned]:checked").val();
        }']);
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(0).footer()).html(o.Total);$(n.column(2).footer()).html(o.t_overpayment) }', 'order' => [[2, 'desc'], [1, 'desc'], [0, 'desc']], 'drawCallback' => "function(){ $('[data-toggle=\"popover\"]').popover();}", 'aoColumnDefs' => [['sClass' => 'hidden-column', 'aTargets' => []]], 'pagingType' => 'input']);
        $tableBuilder->columns([['orderable' => false, 'searchable' => false, 'data' => 'id', 'name' => 'id', 'title' => 'Merchant id'], ['data' => 'merchants.name', 'name' => 'merchants.name', 'title' => 'Merchant'], ['data' => 'overpayment', 'name' => 'overpayment', 'title' => 'Overpayment']]);
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
        })->pluck('users.name','users.id')->toArray();
        $lenders = $role->allLenders()->pluck('name', 'id');
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();
        $sub_statuses = DB::table('sub_statuses')->orderBy('name')->pluck('name', 'id')->toArray();

        return [
            'tableBuilder'=> $tableBuilder, 'page_title'=> $page_title, 'sdate'=> $sdate, 'edate'=> $edate, 'investors'=> $investors, 'companies'=> $companies, 'lenders'=> $lenders, 'sub_statuses'=> $sub_statuses
        ];
    }

    public static function InvestorLiquidityLog($request,$tableBuilder,$role)
    {
        $page_title = 'Investor Liquidity Log';
        $page_description = 'Investor Liquidity Log';
        $company = $role->allCompanies()->pluck('name', 'id')->toArray();
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
         })->pluck('users.name','users.id')->toArray();
        $ReportTableBuilder = new ReportTableBuilder;
        $requestData = [];

        $tableBuilder->ajax(['url' => route('admin::reports::InvestorLiquidityLogData'), 'type' => 'post', 'data' => 'function(data){
            data._token      = "'.csrf_token().'";
            data.from_date   = $("#from_date").val();
            data.to_date     = $("#to_date").val();
            data.company_id  = $("#company_id").val();
            data.investor_id = $("#investor_id").val();
        }']);
        $tableBuilder->parameters(['pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'asc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($ReportTableBuilder->getInvestorLiquidityLogList($requestData));

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'page_description' => $page_description, 'company' => $company, 'investors' => $investors
        ];
    }
    public static function InvestorLiquidityLogDownload($request)
    {
        $fileName = 'Investor Liquidity List_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $user = $request->user();
        $table = new ManualLiquidityLogView();
        $searchData = ['from_date' => $request->from_date, 'to_date' => $request->to_date, 'company_id' => $request->company_id, 'investor_id' => $request->investor_id];
        $data = $table->fetchResult($searchData);
        $investors = $data->pluck('Investor', 'investor_id');
        $dates = $data->pluck('date', 'date');
        $header = ['id', 'Investor'];
        foreach ($dates as $value) {
            $header[] = FFM::date($value);
        }
        $excel_array = [];
        if (! empty($data)) {
            foreach ($investors as $investor_id => $Investor) {
                $single['id'] = $investor_id;
                $single['Investor'] = $Investor;
                $excel_array[] = $single;
            }
            foreach ($excel_array as $key => $value) {
                $single['id'] = $value['id'];
                $single['Investor'] = $value['Investor'];
                foreach ($dates as $date) {
                    $single[$date] = 0;
                    $liquidity = ManualLiquidityLogView::where('date', $date)->where('investor_id', $single['id'])->first();
                    if ($liquidity) {
                        $single[$date] = FFM::dollar($liquidity->liquidity);
                    }
                }
                $excel_array[$key] = $single;
            }
        }
        $excel_array = Arr::prepend($excel_array, $header);
        $export = new Data_arrExport($excel_array);
        return [
            'export' => $export,
            'fileName' => $fileName
        ];
    }
    public static function InvestorRTRBalanceLog($request,$tableBuilder,$role)
    {
        $page_title = 'Investor RTR Balance Log';
        $page_description = 'Investor RTR Balance Log';
        $company = $role->allCompanies()->pluck('name', 'id')->toArray();
        $investors = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'roles.id', 'user_has_roles.role_id')->where('roles.name','investor')->whereHas('company_relation',function ($query) {
            $query->where('company_status',1);
         })->select(DB::raw("upper(users.name) as name"), 'users.id')
        ->pluck('users.name','users.id')->toArray();
        $ReportTableBuilder = new ReportTableBuilder;
        $requestData = [];

        $tableBuilder->ajax(['url' => route('admin::reports::InvestorRTRBalanceLogData'), 'type' => 'post', 'data' => 'function(data){
                data._token      = "'.csrf_token().'";
                data.from_date   = $("#from_date").val();
                data.to_date     = $("#to_date").val();
                data.company_id  = $("#company_id").val();
                data.investor_id = $("#investor_id").val();
            }']);
        $tableBuilder->parameters(['pagingType' => 'input']);
        $tableBuilder->parameters(['order' => [[1, 'asc']], 'pagingType' => 'input']);
        $requestData['columRequest'] = true;
        $tableBuilder->columns($ReportTableBuilder->getInvestorRTRBalanceLogList($requestData));

        return [
            'tableBuilder' => $tableBuilder, 'page_title' => $page_title, 'page_description' => $page_description, 'company' => $company, 'investors' => $investors
        ];
    }
    public static function InvestorRTRBalanceLogDownload($request) 
    {
        $fileName = 'Investor RTR Balance List_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $user = $request->user();
        $table = new ManualRTRBalanceLogView();
        $searchData = ['from_date' => $request->from_date, 'to_date' => $request->to_date, 'company_id' => $request->company_id, 'investor_id' => $request->investor_id];
        $data = $table->fetchResult($searchData);
        $investors = $data->pluck('Investor', 'investor_id');
        $dates = $data->pluck('date', 'date');
        $header = ['id', 'Investor'];
        foreach ($dates as $value) {
            $header[] = FFM::date($value);
        }
        $excel_array = [];
        if (! empty($data)) {
            foreach ($investors as $investor_id => $Investor) {
                $single['id'] = $investor_id;
                $single['Investor'] = $Investor;
                $excel_array[] = $single;
            }
            foreach ($excel_array as $key => $value) {
                $single['id'] = $value['id'];
                $single['Investor'] = $value['Investor'];
                foreach ($dates as $date) {
                    $single[$date] = 0;
                    $rtr_balance = ManualRTRBalanceLogView::where('date', $date)->where('investor_id', $single['id'])->first();
                    $single[$date] = 0;
                    if ($rtr_balance) {
                        $single[$date] = $rtr_balance->total;
                    }
                    $single[$date] = FFM::dollar($single[$date]);
                }
                $excel_array[$key] = $single;
            }
        }
        $excel_array = Arr::prepend($excel_array, $header);
        $export = new Data_arrExport($excel_array);
        return [
            'export' => $export,
            'fileName' => $fileName
        ];
    }
    public static function agentFeeReport($request,$tableBuilder)
    {
        $page_title = 'Agent Fee Report';
        $tableBuilder->ajax(['url' => route('admin::reports::agent-fee-report'), 'data' => 'function(d){  
            d.merchants = $("#merchant_id").val();   
            d.from_date     = $("#from_date").val();
            d.to_date       = $("#to_date").val();    
            }']);
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){
            if(typeof table !== "undefined") {
            var n=this.api(),o=table.ajax.json();
            $(n.column(3).footer()).html(o.total_fee),
            $(n.column(4).footer()).html(o.total_payment)
            }
            }', 'fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n             var info = this.dataTable().api().page.info();\n             var page = info.page;\n             var length = info.length;\n             var index = (page * length + (iDataIndex + 1));\n             $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }", 'paging' => true, 'pagingType' => 'input', 'serverSide' => false]);
        $tableBuilder = $tableBuilder->columns([['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => '#', 'className' => '', 'orderable' => false, 'searchable' => false, 'defaultContent' => ''], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant'], ['data' => 'date', 'name' => 'date', 'title' => 'Date'], ['data' => 'agent_fee', 'name' => 'agent_fee', 'title' => 'Agent Fee'], ['data' => 'total_amount', 'name' => 'total_amount', 'title' => 'Total Amount']]);

        return [
        'tableBuilder' => $tableBuilder, 'page_title' => $page_title
        ];
    }

    public static function investorAssignmentExport($request,$merchant)
    {       
        $fileName = 'Investor Assignment Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $user = $request->user();
        $investors = $request->investors;
        $merchants = $request->merchants;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $active = $request->active_status;
        $total_amount = 0;
        $data = $merchant->searchForInvestorAssignmentReport($startDate, $endDate, $investors, $merchants, $active)->join('users', 'users.id', 'merchant_user.user_id')->join('user_details', 'user_details.user_id', 'users.id')->select('merchant_user.user_id', 'merchant_user.amount', DB::raw('upper(users.name) as name'), 'merchant_user.id', 'merchant_user.merchant_id', 'user_details.liquidity', 'merchant_user.pre_paid', 'merchant_user.commission_amount','merchant_user.up_sell_commission','merchant_user.under_writing_fee', 'merchant_user.created_at as date')->get()->toArray();
        $total_participant = 0;
        foreach ($data as $key => $dat) {
            $total_participant = $total_participant + $dat['amount'] + $dat['pre_paid'] + $dat['commission_amount']+$dat['under_writing_fee']+$dat['up_sell_commission'];
        }
        $total_participant = FFM::dollar($total_participant);
        $commission_total = 0;
        $paid_syndication = 0;
        $i = 1;
        $excel_array[] = ['No', 'Investor', 'Merchant', 'Participant Amount', 'Liquidity', 'Date'];
        if (! empty($data)) {
            foreach ($data as $key => $total) {
                $liquidity = $total['liquidity'];
                $investor_name = $total['name'];
                $paritcipants = $total['amount'] + $total['pre_paid'] + $total['commission_amount']+$total['under_writing_fee']+$total['up_sell_commission'];
                $merchant_name = '';
                $excel_array[$i]['No'] = $i;
                $excel_array[$i]['Investor'] = (isset($investor_name) ? $investor_name : '');
                $excel_array[$i]['Merchant'] = $total['merchant']['name'];
                $excel_array[$i]['Participant Amount'] = FFM::dollar($paritcipants);
                $excel_array[$i]['Liquidity'] = (isset($liquidity) ? FFM::dollar($liquidity) : '0');
                $excel_array[$i]['Date'] = ($total['date']) ? FFM::datetimetodate($total['date']) : '';
                $i++;
            }
        }
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['Investor'] = null;
        $excel_array[$i]['Merchant'] = null;
        $excel_array[$i]['Participant Amount'] = $total_participant;
        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }
    public static function investorInterestAccuredDetails($request, $tableBuilder)
    {
        $input = $request->all();
        $investor_name = User::where('id', $request->id)->value('name');
        $page_title = 'Accrued ROI Details of '.$investor_name;
        $tableBuilder->ajax(['url' => route('admin::reports::investor-interest-accured-details'), 'type' => 'post', 'data' => 'function(data){
                data._token = "'.csrf_token().'";
                data.inv_id = $("#inv_id").val();
                data.start_date = $("#start_date").val();
                data.end_date = $("#end_date").val();
                
            }']);
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api();o=table.ajax.json();$(n.column(6).footer()).html(o.total_roi_accured);$(n.column(1).footer()).html(o.total_credit);}']);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n                 var info = this.dataTable().api().page.info();\n                 var page = info.page;\n                 var length = info.length;\n                 var index = (page * length + (iDataIndex + 1));\n                 $('td:eq(0)', nRow).html(index).addClass('txt-center');\n               }"]);
        $tableBuilder->columns(\MTB::investorInterestAccuredDetailAction(null, null, null, true));
        return [
            'tableBuilder' => $tableBuilder,
            'page_title' => $page_title,
            'input' => $input
        ];
    }
    
    public static function AdvancePlusInvestmentsReport($investor_id,$labels=null) 
    {
        if(!is_array($labels)){
            if($labels){
                $labels=explode(',',$labels);
            }
        }
        if(!$labels){
            $labels = Label::insurance()->pluck('id', 'id')->toArray();
        }
        $Investor      = User::find($investor_id);
        $MerchantUsers = new MerchantUserView;
        $MerchantUsers = $MerchantUsers->where('investor_id',$investor_id);
        if($labels){
            $MerchantUsers = $MerchantUsers->whereIn('label',$labels);
        }
        $MerchantUsers = $MerchantUsers->orderBy('date_funded');
        $date_fundeds = clone $MerchantUsers;
        $MerchantUsers = $MerchantUsers->get([
            'merchant_id',
            'Merchant',
            'amount',
            'date_funded',
            'merchant_completed_percentate',
        ]);
        $data=[];
        $date_fundeds = $date_fundeds->select(
            DB::raw('min(date_funded) as min'),
            DB::raw('max(date_funded) as max'),
        );
        $date_fundeds = $date_fundeds->first();
        $dateTo = Carbon::parse($date_fundeds->max);
        $day    = Carbon::parse($date_fundeds->min . ' next friday');
        $start = $date_fundeds->min;
        $next  = $day;
        $AllInvestment=[];
        while($day->lt($dateTo)) {
            $start_date = $start;
            $from       = $day->toDateString();
            $end_date   = $next;
            $Investments = new MerchantUserView;
            $Investments = $Investments->where('investor_id',$investor_id);
            if($labels){
                $Investments = $Investments->whereIn('label',$labels);
            }
            $AllInvestment[FFM::date($from)] = $Investments->whereBetween('date_funded',[$start_date,$end_date])->sum('amount');
            $start = date('Y-m-d',strtotime($next.'+1 days'));
            $day->addWeek();
            $next  = $day->toDateString();
        }
        if(!count($AllInvestment)){
            $Investments = new MerchantUserView;
            $Investments = $Investments->where('investor_id',$investor_id);
            if($labels){
                $Investments = $Investments->whereIn('label',$labels);
            }
            $AllInvestment[FFM::date($date_fundeds->max)] = $Investments->whereBetween('date_funded',[$date_fundeds->min,$date_fundeds->max])->sum('amount');
        }
        $Investment = $AllInvestment;
        foreach ($MerchantUsers as $MerchantUser){
            $single['Merchant']   = $MerchantUser->Merchant;
            $single['amount']     = FFM::dollar($MerchantUser->amount);
            $single['date']       = $MerchantUser->date_funded;
            $single['percentage'] = FFM::percent($MerchantUser->merchant_completed_percentate);
            $data[$MerchantUser->merchant_id]=$single;
        }
        foreach ($data as $merchant_id => $single){
            $PaymentInvestors = new PaymentInvestors;
            $PaymentInvestors = $PaymentInvestors->join('participent_payments','participent_payments.id','participent_payment_id');
            $PaymentInvestors = $PaymentInvestors->where('user_id',$investor_id);
            $PaymentInvestors = $PaymentInvestors->where('participent_payments.merchant_id',$merchant_id);
            $PaymentInvestorTotal = clone $PaymentInvestors;
            $PaymentInvestors = $PaymentInvestors->groupBy('payment_date');
            $PaymentInvestors = $PaymentInvestors->get([
                DB::raw('sum(participant_share-mgmnt_fee) as share'),
                'payment_date',
            ]);
            foreach ($PaymentInvestors as $PaymentInvestor){
                if($PaymentInvestor->share)
                $data[$merchant_id]['list'][FFM::date($PaymentInvestor->payment_date)]=FFM::dollar($PaymentInvestor->share);
            }
            $data[$merchant_id]['total'] = $PaymentInvestorTotal->sum(DB::raw('participant_share-mgmnt_fee'));
        }
        $dates = new PaymentInvestors;
        $dates = $dates->join('participent_payments','participent_payments.id','participent_payment_id');
        $dates = $dates->join('merchants','merchants.id','participent_payments.merchant_id');
        $dates = $dates->where('payment_investors.user_id',$investor_id);
        if($labels){
            $dates = $dates->whereIn('label',$labels);
        }
        $dates = $dates->groupBy('payment_date');
        $dates = $dates->select('payment_date');
        $dates = $dates->pluck('payment_date');
        foreach($dates as $key => $date){
            $dates[$key]=FFM::date($date);
        }
        $return['MerchantUsers'] = $MerchantUsers;
        $return['Investor']      = $Investor;
        $return['data']          = $data;
        $return['dates']         = $dates;
        $return['labels']        = $labels;
        $return['Investment']    = $Investment;
        return $return;
    }
}
