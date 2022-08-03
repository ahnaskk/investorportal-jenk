<?php

namespace App\Library\Repository;

use App\CompanyAmount;
use App\Document;
use App\Exports\Data_arrExport;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\ITemplateRepository;
use App\Merchant;
use App\MerchantUser;
use App\Models\InvestorAchRequest;
use App\PaymentInvestors;
use App\PaymentPause;
use App\Rcode;
use App\Settings;
use App\Statements;
use App\Template;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PaymentHelper;
use Illuminate\Support\Facades\Schema;


class TemplateRepository implements ITemplateRepository
{
    public function __construct()
    {
        $this->table = new Template();
        if(Schema::hasTable('settings')){
        $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }

    public function createTemplate(Request $request)
    {
        try {
            if ($request->type == 'email') {
                $request->merge(['assignees' => ($request->roles) ? implode(',', $request->roles) : null]);
            }
            $template = $this->table->create($request->only('title', 'assignees', 'type', 'content', 'subject', 'template', 'temp_code', 'enable'));
            if ($template) {
                $return['result'] = 'success';
            } else {
                throw new Exception('Error occured during template creation');
            }
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
        
    }

    public function allTemplate($type = null)
    {
        $template = $this->table->when($type, function($query, $type){
          return $query->where('type', $type);
        })->select('title', 'type', 'content', 'id', 'assignees', 'subject', 'temp_code', 'enable')->get();

        return $template;
    }

    public function deleteTemplate($id)
    {
        if ($this->findTemplate($id)) {
            return $this->table->where('id', $id)->delete();
        }
    }

    public function findTemplate($id)
    {
        $user = $this->table->find($id);
        if ($user) {
            return $user;
        }

    }

    public function updateTemplate($id, $req)
    {
        try {
            $template = $this->findTemplate($id);
            if (!$template) {
                throw new Exception('Invalid Template Id');
            }
            if ($template) {
                if ($req->type == 'email') {
                    $req->merge(['assignees' => ($req->roles) ? implode(',', $req->roles) : null]);
                }
                $updateData = $req->only('title', 'assignees', 'type', 'content', 'subject', 'template', 'temp_code', 'enable');
                $update = $template->update($updateData);
                if ($update) {
                    $return['result'] = 'success';    
                } else {
                    throw new Exception('Error occured during update');
                }
            }
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function getTemplateName($temp_code)
    {
        switch ($temp_code) {
            case 'FREQA':
                return 'Fund Request Approval';
                break;
            case 'FREQR':
                return 'Fund Request Reject';
                break;
            case 'GPDF':
                return 'Generate PDF for Investors';
                break;
            case 'GRPDF':
                return 'Generate PDF for Investors with recurrence';
                break;
            case 'FUNDR':
                return 'Fund Request';
                break;
            case 'INVTR':
                return 'Investor Updation';
                break;
            case 'NOTES':
                return 'Merchant Notes';
                break;
            case 'MERC':
                return 'Merchant Login Credentials';
                break;
            case 'COMPC':
                return 'Create Company';
                break;
            case 'MCSS':
                return 'Merchant Change Status (Default/Default+)';
                break;
            case 'MSAC':
                return 'Merchant Change Status (Advance Completed 100%)';
                break;
            case 'MSPP':
                return 'Merchant Payment Pending';
                break;
            case 'PAYC':
                return 'Payment Completed Percentage (<100)';
                break;
            case 'PAYCO':
                return 'Payment Completed Percentage (>100)';
                break;
            case 'PENDP':
                return 'Pending Payment';
                break;
            case 'PENDL':
                return 'Pending Payment List';
                break;
            case 'MPLCE':
                return 'Enable Marketplace';
                break;
            case 'MERD':
                return 'Merchant Details';
                break;
            case 'RECR':
                return 'Reconciliation Request';
                break;
            case 'LIQAL':
                return 'Liquidity Alert (-ve)';
                break;
            case 'FREDT':
                return 'Fund Request Details';
                break;
            case 'MPSYF':
                return 'Merchant 100% Syndicate';
                break;
            case 'DONP':
              return 'Deals On Pause';
              break;
            case 'REQMM':
                return 'Request More Money';
                break;
            case 'REPOF':
                return 'Request PayOff';
                break;
            case 'CRMMU':
                return 'Merchant Update (CRM)';
                break;
            case 'CRMMC':
                return 'Merchant Create (CRM)';
                break;
            case 'CRMIC':
                return 'Investor Create (CRM)';
                break;
            case 'CRMIU':
                return 'Investor Update (CRM)';
                break;
            case 'INSUA':
                return 'Fundings Sign Up (sent to admin)';
                break;
            case 'INSUO':
                return 'Fundings Sign Up (sent to others)';
                break;
            case 'RERQA':
                return 'Reconciliation Request (sent to admin)';
                break;
            case 'MACHC':
                return 'Merchant ACH Status Check/Re-Check';
                break;
            case 'RCOML':
                return 'Merchant ACH Rcode Report (Check/Re-Check)';
                break;
            case 'MACHR':
                return 'Merchant ACH Sent Report';
                break;
            case 'ACHSR':
                return 'ACH Syndication Sent Report';
                break;
            case 'PYPS':
                return 'Payment Pause';
                break;
            case 'PYRS':
                return 'Payment Resume';
                break;
            case 'MRTD':
                return 'Missed Payment';
                break;
            case 'PYMNT':
                return 'Sending Payment';
                break;
            case 'PYMNA':
                return 'Sending Payment (sent to admin)';
                break;
            case 'IAPR':
                return 'Investor ACH Processing Report';
                break;
            case 'IARR':
                return 'Investor ACH Recheck Report';
                break;
            case 'ACRR':
                return 'ACH Returned Request';
                break;
            case 'ACDR':
                return 'ACH (Credit/Debit) Request';
                break;
            case 'ACSR':
                return 'ACH Settlement Request';
                break;
            case 'ACDP':
                return 'ACH Delete Pending';
                break;
            case 'MACHD':
                return 'Merchants with difference between ACH balance and actual balance';
                break;
            case 'TWFEN':
                return 'Two Factor Enabled';
                break;
            case 'TWFD':
                return 'Two Factor Disabled';
                break;
            case 'ACHDF':
                return 'ACH Deficit';
                break;
            case 'MACC':
                return 'Merchant ACH Credit Request';
                break;
            default:
                return 'NIL';
                break;
        }
    }

    public function getTemplateCodes($type = null)
    {
        $template_list = [
          'FREQA' =>  ['name' => 'Fund Request Approve', 'status' => 'funding_approval'],
          'FREQR' =>  ['name' => 'Fund Request Reject', 'status' => 'funding_reject'],
          'NOTES' =>  ['name' => 'Merchant Notes', 'status' => 'merchant_note'],
          'GPDF'  =>  ['name' => 'Generate PDF For investors', 'status' => 'pdf_mail', 'type' => 'pdf_normal'],
          'GRPDF' =>  ['name' => 'Generate PDF For investors with recurrence', 'status' => 'pdf_mail', 'type' => 'pdf_recurrence'],
          'FUNDR' =>  ['name' => 'Fund Request', 'status' => 'funding_request'],
          'FREDT' =>  ['name' => 'Fund Request Details', 'status' => 'funding_request_details'],
          'MERC'  =>  ['name' => 'Merchant Login Credentials', 'status' => 'merchant_login'],
          'INVTR' =>  ['name' => 'Investor Updation', 'status' => ['investor', 'account']],
          'COMPC' =>  ['name' => 'Create Company', 'status' => 'company'],
          'MCSS'  =>  ['name' => 'Change Merchant Status (Default/Default+)', 'status' => 'merchant_change_status', 'type' => 'merchant_status_change_common'],
          'MSAC'  =>  ['name' => 'Change Merchant Status (Advance Completed 100%)', 'status' => 'merchant_change_status', 'type' => 'advance_complete_100_percent'],
          'MSPP'  =>  ['name' => 'Merchant Payment Pending', 'status' => 'merchant_change_status', 'type' => 'pending_payment'],
          'PAYC'  =>  ['name' => 'Payment Completed Percentage(<100)', 'status' => 'payment_mail'],
          'PENDP' =>  ['name' => 'Pending Payment', 'status' => 'pending_payment'],
          'PENDL' =>  ['name' => 'Pending Payment List', 'status' => 'all_pending_payment'],
          'MPLCE' =>  ['name' => 'Enable Marketplace', 'status' => 'new_deal'],
          'MERD'  =>  ['name' => 'Merchant Details', 'status' => 'merchant'],
          'PAYCO' =>  ['name' => 'Payment Completed Percentage(>100)', 'status' => 'payment_mail'],
          'RECR'  =>  ['name' => 'Reconciliation Request', 'status' => '30day merchant mail notification'],
          'LIQAL' =>  ['name' => 'Liquidity Alert (-ve)', 'status' => 'liquidty_alert'],
          'MPSYF' =>  ['name' => 'Marketplace 100% Syndicate', 'status' => '100_percent_syndicated'],
          'DONP'  =>  ['name' => 'Deals On Pause', 'status' => 'deals_on_pause'],
          'REQMM' =>  ['name' => 'Request More Money', 'status' => 'merchant_api', 'type' => 'request_money'],
          'REPOF' =>  ['name' => 'Request PayOff', 'status' => 'merchant_api', 'type' => 'request_payoff'],
          'CRMMU' =>  ['name' => 'Merchant Update (CRM)', 'status' => 'merchant_api', 'type' => 'merchant_update'],
          'CRMMC' =>  ['name' => 'Merchant Create (CRM)', 'status' => 'merchant_api', 'type' => 'merchant_create'],
          'CRMIC' =>  ['name' => 'Investor Create (CRM)', 'status' => 'investor_api', 'type' => 'investor_create'],
          'CRMIU' =>  ['name' => 'Investor Update (CRM)', 'status' => 'investor_api', 'type' => 'investor_update'],
          'INSUA' =>  ['name' => 'Fundings Sign Up (sent to admin)', 'status' => 'funding_investor_signup', 'type' => 'admin'],
          'INSUO' =>  ['name' => 'Fundings Sign Up (sent to others)', 'status' => 'funding_investor_signup', 'type' => 'others'],
          'RERQA' =>  ['name' => 'Reconciliation Request (sent to admin)', 'status' => 'reconcilation request mail to admin'],
          'MACHC' =>  ['name' => 'Merchant ACH Status Check/Re-Check', 'status' => 'ach_status_check'],
          'RCOML' =>  ['name' => 'Merchant ACH Rcode Report (Check/Re-Check)', 'status' => 'ach_rcode_mail'],
          'MACHR' =>  ['name' => 'Merchant ACH Sent Report', 'status' => 'ach_sent_report'],
          'ACHSR' =>  ['name' => 'ACH Syndication Sent Report', 'status' => 'ach_syndication_sent_report'],
          'PYPS'  =>  ['name' => 'Payment Pause', 'status' => 'payment_paused'],
          'PYRS'  =>  ['name' => 'Payment Resume', 'status' => 'payment_resumed'],
          'MRTD'  =>  ['name' => 'Missed Payment', 'status' => 'merchant_returnd'],
          'PYMNT' =>  ['name' => 'Sending Payment', 'status' => 'payment_send'],
          'PYMNA' =>  ['name' => 'Sending Payment (sent to admin)', 'status' => 'payment_send'],
          'IAPR'  =>  ['name' => 'Investor ACH Processing Report', 'status' => 'investor_ach_request_send_report'],
          'IARR'  =>  ['name' => 'Investor ACH Recheck Report', 'status' => 'investor_ach_recheck_report'],
          'ACRR'  =>  ['name' => 'ACH Returned Request', 'status' => 'investor_ach_request_returned'],
          'ACDR'  =>  ['name' => 'ACH (Credit/Debit) Request', 'status' => 'investor_ach_request'],
          'ACSR'  =>  ['name' => 'ACH Settlement Request', 'status' => 'investor_ach_request_settlement'],
          'ACDP'  =>  ['name' => 'ACH Delete Pending', 'status' => 'pending_ach_delete_mail'],
          'MACHD' =>  ['name' => 'Merchants with difference between ACH balance and actual balance', 'status' => 'merchant_unit_test', 'type' => 'ach_difference'],
          'TWFEN' =>  ['name' => 'Two Factor Enabled', 'status' => 'two_step_enabled_verification_notification'],
          'TWFD'  =>  ['name' => 'Two Factor Disabled', 'status' => 'two_step_disabled_verification_notification'],
          'ACHDF' =>  ['name' => 'ACH Deficit', 'status' => 'merchant_ach_payments_deficit'],
          'MACC'  =>  ['name' => 'Merchant ACH Credit Request', 'status' => 'ach_merchant_credit_request'],
        ];
        if ($type == 'list') {
            return $template_list;
        }
        $templates_used = $this->table->pluck('temp_code')->toArray();
        $templates_used = array_combine($templates_used, $templates_used);
        $template_sel = array_diff_key($template_list, $templates_used);
        if (count($template_sel) > 0):
            $template_arr = [];
        foreach ($template_sel as $k => $temp) {
            $template_arr[$k] = $temp['name'];
        }

        return $template_arr; else:
            return ['' =>'No Templates Available'];
        endif;
    }

    public function getTemplateFromStatus($status = null, $type = null)
    {
        $templates = $this->getTemplateCodes('list');
        if ($status) {
            $templates = collect($templates)->filter(function ($value, $key) use ($status) {
                if (gettype($value['status']) == 'array') {
                    return in_array($status, $value['status']);
                }

                return $value['status'] == $status;
            });
            if ($type) {
                if ($status == 'payment_mail') {
                    if ($type < 99) {
                        return 'PAYC';
                    } else {
                        return 'PAYCO';
                    }
                } elseif ($status == 'payment_send') {
                    if ($type != 'admin') {
                        return 'PYMNT';
                    } else {
                        return 'PYMNA';
                    }
                }
                $templates = collect($templates)->filter(function ($value, $key) use ($type) {
                    return $value['type'] == $type;
                });
            }

            return $templates->keys()->first();
        }

        return $templates;
    }

    public function sendSample($temp_code, $template_id) {
        // sending sample mail from template edit page
        $msg = [];
        switch ($temp_code) {
            case 'MCSS':
                $merchant = Merchant::where('sub_status_id', 4)->orWhere('sub_status_id', 20)->first();
                if ($merchant) {
                    $status = 'default';
                    if ($merchant->sub_status_id == 20) { $status = 'Default+';}
                    $msg['title'] = 'Merchant Status changed to '.$status;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['status'] = 'merchant_change_status';
                    $msg['unqID'] = unqID();
                    $msg['merchant_name'] = $merchant->name;
                    $msg['new_status'] = $status;
                    $msg['template_type'] = 'merchant_status_change_common';
                }
                break;
            case 'MSAC':
                $merchant = Merchant::where('sub_status_id', 11)->first();
                if ($merchant) {
                    $msg['title'] = 'Advance completed 100%';
                    $msg['merchant_id'] = $merchant->id;
                    $msg['status'] = 'merchant_change_status';
                    $msg['unqID'] = unqID();
                    $msg['template_type'] = 'advance_complete_100_percent';
                    $msg['merchant_name'] = $merchant->name;    
                }
                break;
            case 'MSPP':
                $merchant = Merchant::first();
                if ($merchant) {
                    $monthDays = rand(1, 30);
                    $msg['merchant_id'] = $merchant->id;
                    $msg['status'] = 'merchant_change_status';
                    $msg['unqID'] = unqID();
                    $msg['template_type'] = 'pending_payment';
                    $msg['merchant_name'] = $merchant->name;
                    $msg['days'] = $monthDays;
                }
                break;
            case 'MPLCE':
                $merchant = Merchant::first();
                if ($merchant) {
                    $msg['timestamp'] = time();
                    $msg['title'] = 'New deal';
                    $msg['merchant_id'] = $merchant->id;
                    $msg['status'] = 'new_deal';
                    $msg['app_status'] = 'investor_app';
                    $msg['type'] = $merchant->type;
                    $msg['merchant_name'] = $merchant->name;   
                    $msg['unqID'] = unqID(); 
                }
                break;
            case 'LIQAL':
                $user = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                if ($user) {
                    $liquidity = rand(-1000, -5000);
                    $msg['title'] = 'Liquidity -ve email alert for '.$user->name;
                    $msg['subject'] = 'Liquidity -ve email alert for '.$user->name;
                    $msg['investor_name'] = $user->name;
                    $msg['amount'] = $liquidity;
                    $msg['status'] = 'liquidty_alert';
                    $msg['heading'] = 'Liquidity -ve email alert for '.$user->name;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'NOTES':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $merchant_name = $merchant->name;
                    $merchant_id = $merchant->id;
                    $msg['title'] = $merchant_name.' Notes';
                    $msg['subject'] = $merchant_name.' Notes';            
                    $msg['status'] = 'merchant_note';
                    $msg['merchant_id'] = $merchant_id;
                    $msg['merchant_name'] = $merchant_name;
                    $msg['note'] = 'Test Note';
                    $msg['author'] = auth()->user()->name;
                    $msg['unqID'] = unqID();
                    $msg['date_time'] = \FFM::datetime(\Carbon\Carbon::now('UTC'));  
                }
                break;
            case 'GPDF':
                $user = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                $investor = Statements::select('name', 'statements.id', 'email', 'file_name', 'notification_email')
                ->where('statements.id', $user->id)
                ->join('users', 'users.id', 'statements.user_id')
                ->first()
                ->toArray();
                if ($investor) {
                    $fileName = $investor['file_name'].'.pdf';
                    $fileUrl = asset(\Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2)));
                    $msg['title'] = 'Payment Report Statement';
                    $msg['options'] = 'Weekly';
                    $msg['investor_name'] = $investor['name'];
                    $msg['attach'] = $fileUrl;
                    $msg['status'] = 'pdf_mail';
                    $msg['fileName'] = $fileName;
                    $msg['heading'] = 'Payment Report Statement';
                    $msg['unqID'] = unqID();
                    $msg['template_type'] = 'pdf_normal';
                }
                break;
            case 'GRPDF':
                $recurrence = rand(1,5);
                $investor = Statements::select('name', 'users.id', 'email', 'file_name')
                ->join('users', 'users.id', 'statements.user_id')
                ->where('file_name', 'like', '%last_day_report_%')
                ->orWhere('file_name', 'like', '%last_week_report_%')
                ->orWhere('file_name', 'like', '%last_month_report_%')
                ->orWhere('file_name', 'like', '%last_two_week_report_%')
                ->orWhere('file_name', 'like', '%last_year_report_%')
                ->orWhere('file_name', 'like', '%date_range_report_%')->first();
                if ($investor) {
                    $fileName = $investor->file_name.'.pdf';
                    $fileUrl = asset(\Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2)));
                    $title = 'Last Day';
                    $msg['title'] = 'Payment Report Statement';
                    $msg['options'] = 'last_day';
                    $msg['investor_name'] = $investor['name'];
                    $msg['attach'] = $fileUrl;
                    $msg['status'] = 'pdf_mail';
                    $msg['fileName'] = $fileName;
                    $msg['heading'] = $title;
                    $msg['unqID'] = unqID();
                    $msg['template_type'] = 'pdf_recurrence';
                    $msg['recurrence_type'] = $title;
                }
                break;
            case 'FUNDR':
                $user = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                $merchant = Merchant::inRandomOrder()->first();
                if ($user && $merchant) {
                    $amount = rand(1000, 60000);
                    $msg['content'] = '<a href='.url('admin/investors/portfolio/'.$user->id).'>'.$user->name.'</a> Invested '.\FFM::dollar($amount).' in the merchant  <a href='.url('admin/merchants/view/'.$merchant->id).'>'.$merchant->name.'</a>';
                    $msg['investor'] = $user->name;
                    $msg['title'] = 'Investment request';
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['amount'] = $amount;
                    $msg['user_id'] = $user->id;
                    $msg['timestamp'] = time();
                    $msg['status'] = 'funding_request';
                    $msg['subject'] = 'Funding request | Velocitygroupusa';
                    $msg['unqID'] = unqID();
                }
                break;
            case 'FREDT':
                $merchant = Merchant::inRandomOrder()->join('documents', 'documents.merchant_id', 'merchants.id')->select('merchants.id', 'merchants.name', 'documents.file_name')->first();
                $user = null;
                if ($merchant) {
                    $m_user = MerchantUser::where('merchant_id', $merchant->id)->first();
                    if ($m_user) {
                        $user = User::where('id', $m_user->user_id)->first();
                    }
                }
                if ($user && $merchant) {
                    $amount = rand(1000, 60000);
                    $msg['content'] = 'You have successfully invested '.\FFM::dollar($amount).' in <a href='.url('admin/merchants/view/'.$merchant->id).'>'.$merchant->name.'</a> . Thank you for your participation.';
                    $msg['title'] = 'Funding Request Details';
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $file = Document::where('merchant_id', $merchant->id)->first();
                    $fileName = $file->file_name;
                    $msg['document_url'] = \Storage::disk('s3')->temporaryUrl($fileName,Carbon::now()->addMinutes(2));
                    $msg['timestamp'] = time();
                    $msg['status'] = 'funding_request_details';
                    $msg['subject'] = 'Funding Request Details';
                    $msg['unqID'] = unqID();
                    $msg['investor'] = $user->name;
                    $msg['amount'] = $amount;
                    $msg['fileName'] = $fileName;
                }
                break;
            case 'MPSYF':
                $merchant = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->inRandomOrder()->select('max_participant_fund', DB::raw('sum(amount) as invested_amount'), 'merchants.id as merchant_id', 'merchants.name as merchant_name', 'merchant_user.user_id')->first();
                if ($merchant) {
                    $msg['merchant_name'] = $merchant->merchant_name;
                    $msg['merchant_id'] = $merchant->merchant_id;
                    $msg['status'] = '100_percent_syndicated';
                    $msg['subject'] = '100% syndicated';
                    $msg['unqID'] = unqID();
                    $msg['content'] = 'Marketplace merchant  <a href='.url('admin/merchants/view/'.$merchant->merchant_id).'>'.$merchant->merchant_name.'</a> reaches 100% syndicated now';    
                }
                break;
            case 'PENDL':
                $merchants = Merchant::select('last_payment_date', 'merchants.id as merchant_id', 'factor_rate', 'merchants.name as merchant_name', 'lag_time', 'users.name as lender_name', 'company', 'sub_statuses.name as substatus_name', 'merchants.sub_status_id')
                ->where('merchants.active_status', 1)
                ->whereNOTIn('merchants.sub_status_id', [4, 11, 18, 19, 20, 22])
                ->join('users', 'users.id', 'merchants.lender_id')
                ->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
                ->where('merchants.complete_percentage', '<', 99)
                ->where('merchants.complete_percentage', '>', 0)
                ->orderByDesc('merchants.id')
                ->where(function ($query) {
                    $query->whereRaw('date(last_payment_date) <  DATE_SUB(NOW(), INTERVAL (lag_time+30) DAY)');
                })
                ->orderByDesc('merchants.id')->get()->toArray();
                $data_array = [];
                $i = 1;        
                if (! empty($merchants)) {
                    foreach ($merchants as $key=>$value) {
                        $last_payment_date = ! empty($value['last_payment_date']) ? $value['last_payment_date'] : '';
                        if ($last_payment_date) {
                            $date = date('Y-m-d', strtotime($last_payment_date));
                            $to = date('Y-m-d');
                            $now = strtotime($to);
                            $current_date = strtotime($date);
                            $datediff = $now - $current_date;
                            $days = round($datediff / (60 * 60 * 24));
                            $delay = $days - $value['lag_time'];
                            $companies = CompanyAmount::where('merchant_id', $value['merchant_id'])->join('users', 'users.id', 'company_amount.company_id')
                            ->where('max_participant', '!=', 0)
                            ->pluck('users.name', 'users.id')->toArray();
                            if ($days >= (30 + $value['lag_time'])) {
                                $data_array[] = [
                                    'merchant'=>$value['merchant_name'],
                                    'merchant_id'=>$value['merchant_id'],
                                    'lender_name'=>$value['lender_name'],
                                    'company'=>$companies,
                                    'delay'=>$delay,
                                    'substatus'=>$value['substatus_name'],
                                ];
                                // $update = ['mail_send_status'=>111];
                                $status_check = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'),
                                DB::raw('sum(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'))
                                ->where('merchant_id', $value['merchant_id'])
                                ->first()->toArray();
                                $payments = PaymentInvestors::select(DB::raw('sum(participant_share-payment_investors.mgmnt_fee) as final_participant_share'))->where('merchant_id', $value['merchant_id'])
                                ->first()->toArray();
                                if (
                                    $value['sub_status_id'] == 1 &&
                                    ($payments['final_participant_share'] < $status_check['investment_amount']) &&
                                    ! empty($status_check['investment_amount'])
                                ) {
                                    $investor_array = [];
                                    $investment_data = MerchantUser::where('merchant_id', $value['merchant_id'])->where('merchant_user.status', 1)->get();
                                    foreach ($investment_data as $key => $investments) {
                                        $investor_array[$key] = $investments->user_id;
                                    }
                                }
                            }
                        }
                    }
                }
                $html = '';
                    $html .= '<table class="table" width="100%" cellpadding="0" cellspacing="0">
                    <tbody><tr>
                    <td style="background: #eae9f1;">SI</td>
                    <td style="background: #eae9f1;">Merchant</td>
                    <td style="background: #eae9f1;">Lender</td>
                    <td style="background: #eae9f1;">Company</td>
                    <td style="background: #eae9f1;">Delay</td>
                    <td style="background: #eae9f1;">Merchant Status</td>
                    </tr>';
                    foreach ($data_array as $key=>$value1) {
                        $url = url('/admin/merchants/view/'.$value1['merchant_id']);
                        $html .= '<tr>
                        <td>'.$i.'</td>
                        <td><a href="'.$url.'" style="color:#2d3aab;">'.$value1['merchant'].'</a></td>
                        <td>'.$value1['lender_name'].'</td><td>';
                        if ($value1['company']) {
                            foreach ($value1['company'] as $company) {
                                $html .= $company.'<br>';
                            }
                        }
                        $html .= '</td>
                        <td>'.$value1['delay'].'</td>
                        <td>'.$value1['substatus'].'</td>
                        </tr>';
                        $i++;
                    }
                $html .= '</tbody></table>';
                $msg['title'] = 'Pending Payment';
                $msg['subject'] = 'Pending Payment';
                $msg['pending_payment_table'] = $html;
                $msg['title'] = 'Pending Payment';
                $msg['status'] = 'all_pending_payment';
                $msg['unqID'] = unqID();
                break;
            case 'DONP':
                $merchants = Merchant::select('last_payment_date', 'merchants.id as merchant_id', 'factor_rate', 'merchants.name as merchant_name', 'lag_time', 'users.name as lender_name', 'company', 'sub_statuses.name as substatus_name', 'merchants.sub_status_id')
                ->where('merchants.active_status', 1)
                ->whereNOTIn('merchants.sub_status_id', [4, 11, 18, 19, 20, 22])
                ->join('users', 'users.id', 'merchants.lender_id')
                ->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')
                ->where('merchants.complete_percentage', '<', 99)
                ->where('merchants.complete_percentage', '>', 0)
                ->where('merchants.payment_pause_id', '>', 0)
                ->whereNotIn('merchants.label', [3, 4, 5])
                ->orderByDesc('merchants.id')
                ->where(function ($query) {
                    $query->whereRaw('date(last_payment_date) <  NOW()');
                })
                ->orderByDesc('merchants.id')->get()->toArray();
                $data_array = [];
                $i = 1;

                if (! empty($merchants)) {
                    foreach ($merchants as $key=>$value) {
                        $last_payment_date = ! empty($value['last_payment_date']) ? $value['last_payment_date'] : '';
                        if ($last_payment_date) {
                            $date = date('Y-m-d', strtotime($last_payment_date));
                            $from = $date;
                            $to = date('Y-m-d');
                            $now = strtotime($to);
                            $current_date = strtotime($date);
                            $datediff = $now - $current_date;
                            $days = round($datediff / (60 * 60 * 24));
        
                            $delay = $days;
        
                            $companies = CompanyAmount::where('merchant_id', $value['merchant_id'])->join('users', 'users.id', 'company_amount.company_id')
                            ->where('max_participant', '!=', 0)
                            ->pluck('users.name', 'users.id')->toArray();
        
                            if ($days > 0) {
                                $data_array[] = [
                                    'merchant'=>$value['merchant_name'],
                                    'merchant_id'=>$value['merchant_id'],
                                    'lender_name'=>$value['lender_name'],
                                    'company'=>$companies,
                                    'delay'=>$delay,
                                    'substatus'=>$value['substatus_name'],
                                ];
                            }
                        }
                    }
                }
                usort($data_array, function ($a, $b) {
                    return $b['delay'] <=> $a['delay'];
                });
                $html = '';
                $html .= '<table class="table" width="100%" cellpadding="0" cellspacing="0">
                <tbody><tr>
                <td style="background: #eae9f1;">SI</td>
                <td style="background: #eae9f1;">Merchant</td>
                <td style="background: #eae9f1;">Lender</td>                
                <td style="background: #eae9f1;">Delay</td>
                <td style="background: #eae9f1;">Merchant Status</td>
                </tr>';
                foreach ($data_array as $key=>$value1) {
                    $url = url('/admin/merchants/view/'.$value1['merchant_id']);
                    $html .= '<tr>
                    <td>'.$i.'</td>
                    <td><a href="'.$url.'" style="color:#2d3aab;">'.$value1['merchant'].'</a></td>
                    <td>'.$value1['lender_name'].'</td>';

                    $html .= '<td>'.$value1['delay'].'</td>
                    <td>'.$value1['substatus'].'</td>
                    </tr>';
                    $i++;
                }
                $html .= '</tbody></table>';
                $msg['title'] = 'Deals On Pause';
                $msg['subject'] = 'Deals On Pause';
                $msg['content'] = $html;
                $msg['status'] = 'deals_on_pause';
                $msg['unqID'] = unqID();
                break;
            case 'PAYC':
                $merchant = Merchant::where('complete_percentage', '>=', 40)->where('complete_percentage', '<', 50)->first();
                if ($merchant) {
                    $new_completed_percenteage = \PayCalc::completePercentage($merchant->id);
                    $msg['title'] = $merchant->name.'  Passed 40% of payments';
                    $msg['status'] = 'payment_mail';
                    $msg['complete_per'] = $new_completed_percenteage;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'PAYCO':
                $merchant = Merchant::where('complete_percentage', '>=', 100)->first();
                if ($merchant) {
                    $new_completed_percenteage = \PayCalc::completePercentage($merchant->id);
                    $msg['title'] = $merchant->name.'  Passed '.round($new_completed_percenteage, 2).'% of payments';
                    $msg['status'] = 'payment_mail';
                    $msg['complete_per'] = $new_completed_percenteage;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'PENDP':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $day = rand(1, 28);
                    $last_payment_date = date('Y-m-d', strtotime("-".$day." days"));
                    $msg['title'] = 'Pending Payment';
                    $msg['status'] = 'pending_payment';
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['subject'] = 'Pending Payment from '.date('m-d-Y', strtotime($last_payment_date));
                    $msg['days'] = $day;
                    $msg['date'] = date('m-d-Y', strtotime($last_payment_date));
                    $msg['unqID'] = unqID();
                }
                break;
            case 'MERD':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) 
                {
                    $company_amounts = CompanyAmount::select('max_participant', 'merchant_id', 'company_id', 'users.name')->where('merchant_id', $merchant->id)->whereNotNull('max_participant')->join('users', 'users.id', 'company_amount.company_id')->get()->toArray();
                    $company_arr = [];
                    $html = '';
                    if (! empty($company_amounts)) {
                        $i = 0;
                        foreach ($company_amounts as $key => $value) {
                            $per = ($merchant->max_participant_fund) ? (($value['max_participant'] / $merchant->max_participant_fund) * 100) : 0;
                            $html .= '<tr>
                                <td class="content" style="border:0; text-align:center; padding: 0 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;">
                                    <div style="background: #fdfdff; border-radius: 8px; border: 1px solid #eeedf5; color: #535777; font-weight: bold; line-height: 26px; padding: 15px 25px;">
                                        '.$value['name'].' Participated<span style="font-weight: bold; display: block; font-size: 20px;color: #28b76e;  margin: 7px 0 0;">
                                        '.\FFM::dollar($value['max_participant']).' ('.round($per, 2).'%).</span>
                                    </div>
                                </td>
                            </tr>';
                            $company_arr[$i]['name'] = $value['name'];
                            $company_arr[$i]['max_participant'] = $value['max_participant'];
                            $company_arr[$i]['per'] = $per;
                            $i++;
                        }
                    }
                    $msg['title'] = $merchant->name.' Details';
                    $msg['subject'] = $merchant->name.' Details';
                    $msg['content'] = $html;
                    $msg['status'] = 'merchant';
                    $msg['merchant_id'] = $merchant->id;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['company_amounts'] = $company_arr;
                    $msg['creator'] = Auth::user()->name;
                    $msg['merchant_details'] = $html;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'MERC':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $m_url = url('/merchants');
                    $email = 'testmerchant@gmail.com';
                    $password = $this->generateRandomString(7);
                    $msg['title'] = 'Login Credentials for '.$merchant->name;
                    $msg['subject'] = 'Login Credentials for '.$merchant->name;
                    $msg['content'] = 'Merchant Name : <a href='.$m_url.'>'.$merchant->name." </a> \n <br> Email : ".$email." \n <br>  Password : ".$password;
                    $msg['status'] = 'merchant_login';
                    $msg['merchant_id'] = $merchant->id;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['username'] = $email;
                    $msg['password'] = $password;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'COMPC':
                $msg['title'] = 'Test Details';
                $msg['subject'] = 'Company Details';
                $msg['content'] = 'Test Company has been created successfully in the portal!';
                $msg['company'] = 'Test';
                $msg['status'] = 'company';
                $msg['unqID'] = unqID();
                break;
            case 'REQMM':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $msg['title'] = 'Request More Money';
                    $msg['subject'] = 'Request More Money';
                    $msg['status'] = 'merchant_api';
                    $msg['template_type'] = 'request_money';
                    $msg['merchant_id'] = $merchant->id;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['amount'] = 1000;
                    $msg['from_mail'] = 'test@gmail.com';
                    $msg['unqID'] = unqID();
                }
                break;
            case 'REPOF':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $msg['title'] = 'Request PayOff';
                    $msg['subject'] = 'Request PayOff';
                    $msg['status'] = 'merchant_api';
                    $msg['template_type'] = 'request_payoff';
                    $msg['merchant_id'] = $merchant->id;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['from_mail'] = 'test@gmail.com';
                    $msg['unqID'] = unqID();
                }
                break;
            case 'CRMMU':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $msg['title'] = 'Merchant Updated';
                    $msg['from_mail'] = 'api@vgusa.com';
                    $msg['status'] = 'merchant_api';
                    $msg['template_type'] = 'merchant_update';
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['subject'] = 'Merchant updated from CRM.';
                    $msg['unqID'] = unqID();
                }
                break;
            case 'CRMMC':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $msg['title'] = 'Merchant Created';
                    $msg['from_mail'] = 'api@vgusa.com';
                    $msg['status'] = 'merchant_api';
                    $msg['template_type'] = 'merchant_create';
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['subject'] = 'New merchant added from CRM.';
                    $msg['unqID'] = unqID();
                }
                break;
            case 'CRMIC':
                $user = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                if ($user) {
                    $msg['title'] = 'investor created';
                    $msg['from_mail'] = 'api@vgusa.com';
                    $msg['status'] = 'investor_api';
                    $msg['template_type'] = 'investor_create';
                    $msg['investor_name'] = $user->name;
                    $msg['investor_id'] = $user->id;
                    $msg['subject'] = 'investor created from CRM.';
                    $msg['unqID'] = unqID();
                }
                break;
            case 'CRMIU':
                $user = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                if ($user) {
                    $msg['title'] = 'investor updated';
                    $msg['from_mail'] = 'api@vgusa.com';
                    $msg['status'] = 'investor_api';
                    $msg['template_type'] = 'investor_update';
                    $msg['investor_name'] = $user->name;
                    $msg['investor_id'] = $user->id;
                    $msg['subject'] = 'investor updated from CRM.';
                    $msg['unqID'] = unqID();
                }
                break;
            case 'INSUA':
                $user = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->whereNotNull('users.email')->whereNotNull('users.cell_phone')->inRandomOrder()->select('users.id', 'users.name', 'users.cell_phone', 'users.email')->first();
                if ($user) {
                    $msg['title'] = 'Investor Signup';
                    $msg['subject'] = 'Investor Created';
                    $msg['status'] = 'funding_investor_signup';
                    $msg['name'] = 'Admin';
                    $msg['template_type'] = 'admin';
                    $msg['email'] = $user->email;
                    $msg['phone'] = $user->cell_phone;
                    $msg['to_mail'] = $this->admin_email;
                    $msg['investor_name'] = $user->name;
                    $msg['date_time'] = \FFM::datetime(Carbon::now());
                }
                break;
            case 'INSUO':
                $user = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->whereNotNull('users.email')->whereNotNull('users.cell_phone')->inRandomOrder()->select('users.id', 'users.name', 'users.cell_phone', 'users.email')->first();
                if ($user) {
                    $msg['title'] = 'Investor Signup';
                    $msg['subject'] = 'Investor Created';
                    $msg['status'] = 'funding_investor_signup';
                    $msg['name'] = $user->name;
                    $msg['template_type'] = 'others';
                    $msg['email'] = $user->email;
                    $msg['phone'] = $user->cell_phone;
                    $msg['to_mail'] = $user->email;
                    $msg['investor_name'] = $user->name;
                    $msg['date_time'] = \FFM::datetime(Carbon::now());
                }
                break;
            case 'INVTR':
                $user = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->whereNotNull('users.email')->inRandomOrder()->select('users.id', 'users.name', 'users.cell_phone', 'users.email')->first();
                if ($user) {
                    $password = $this->generateRandomString(7);
                    $msg['title'] = $user->name.' Details';
                    $msg['subject'] = $user->name.' Details';
                    $msg['to_mail'] = $user->email;
                    $msg['status'] = 'investor';
                    $msg['investor_name'] = $user->name;
                    $msg['username'] = $user->email;
                    $msg['password'] = $password;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'FREQA':
                $merchant = Merchant::inRandomOrder()->select('id')->first();
                $investor = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                if ($merchant && $investor) {
                    $msg['subject'] = 'Funding request approved | Velocitygroupusa';
                    $msg['title'] = 'Funding request approved';
                    $msg['status'] = 'funding_approval';
                    $msg['content'] = 'Your fund request is approved!';
                    $msg['merchant_id'] = $merchant->id;
                    $msg['investor'] = $investor->name;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'FREQR':
                $merchant = Merchant::inRandomOrder()->select('id', 'name')->first();
                $investor = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                if ($merchant && $investor) {
                    $msg['subject'] = 'Funding request rejected | Velocitygroupusa';
                    $msg['title'] = 'Funding request rejected';
                    $msg['status'] = 'funding_reject';
                    $msg['merchant_id'] = $merchant->id;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['investor'] = $investor->name;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'RECR':
                $merchant = Merchant::inRandomOrder()->select('id', 'name')->first();
                if ($merchant) {
                    $msg['title'] = 'Reconciliation Notification';
                    $msg['merchant_name'] = $merchant->name;
                    $msg['subject'] = 'Reconciliation request';
                    $msg['status'] = '30day merchant mail notification';
                    $msg['days'] = rand(1, 29);
                    $msg['unqID'] = unqID();
                }
                break;
            case 'RERQA':
                $merchant = Merchant::inRandomOrder()->select('id', 'name')->first();
                if ($merchant) {
                    $msg['status'] = 'reconcilation request mail to admin';
                    $msg['title'] = 'Reconciliation Request';
                    $msg['subject'] = 'Reconciliation Request by merchant';
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['unqID'] = unqID();
                }
                break;
            case 'MACHC':
                $merchant = Merchant::inRandomOrder()->select('id', 'name')->first();
                if ($merchant) {
                    $total_settled = 2000;
                    $total_settled_payment = 2000;
                    $total_settled_fee = 10;
                    $total_rcode = 10;
                    $total_rcode_amount = 10;
                    $total_rcode_fee = 10;
                    $current_time = Carbon::now();
                    $current_time_formatted = \FFM::datetime($current_time->toDateTimeString());   
                    $data = [[
                        'merchant_name' => $merchant->name,
                        'merchant_id' => $merchant->id,
                        'status' => 'success',
                        'payment_amount' => 2000,
                        'message' => 'Accepted Transaction',
                        'payment_date' => date('Y-m-d'),
                        'type' => 'Payment Debit'

                    ]];
                    $msg['atatchment'] = $this->generateACHCheckStatusCSV($data);
                    $msg['atatchment_name'] = 'test.csv';
                    $msg['title'] = 'Merchant ACH Status Check';
                    $msg['total_settled'] = \FFM::dollar($total_settled);
                    $msg['total_settled_payment'] = \FFM::dollar($total_settled_payment);
                    $msg['total_settled_fee'] = \FFM::dollar($total_settled_fee);
                    $msg['total_rcode'] = \FFM::dollar($total_rcode);
                    $msg['content'] = $data;
                    $msg['total_rcode_amount'] = \FFM::dollar($total_rcode_amount);
                    $msg['total_rcode_fee'] = \FFM::dollar($total_rcode_fee);
                    $msg['count_total'] = 2;
                    $msg['count_payment'] = 1;
                    $msg['count_fee'] = 1;
                    $msg['status'] = 'ach_status_check';
                    $msg['subject'] = 'Merchant ACH Status Check';
                    $msg['unqID'] = unqID();
                    $msg['checked_time'] = $current_time_formatted;
                }
                break;
            case 'RCOML':
                $merchant = Merchant::inRandomOrder()->select('id', 'name')->first();
                if ($merchant) {
                    $data = [[
                        'merchant_name' => $merchant->name,
                        'merchant_id' => $merchant->id,
                        'status' => 'Returned',
                        'payment_amount' => 2000,
                        'message' => 'Failed Transaction',
                        'payment_date' => date('Y-m-d'),
                        'type' => 'Payment Debit'

                    ]];
                    $like = 'Returned';
                    $rcode_ach = array_filter($data, function ($item) use ($like) {
                        if (stripos($item['status'], $like) !== false) {
                            return true;
                        }

                        return false;
                    });
                    if ($rcode_ach) {
                        $msg = null;
                        $msg['title'] = 'Merchant ACH Rcode report';
                        $html = '';
                        $html .= '<table width="100%" border="1" align="center" cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">#</th>
                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Merchant</th>
                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Amount</th>
                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Date</th>
                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Status</th>
                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Response</th>
                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Type</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $i = 1;
                        foreach ($rcode_ach as $key => $req) {
                            $html .= '<tr>
                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">'.$i++.'</td>
                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;"><a href="'.\URL::to('admin/merchants/view', $req['merchant_id']).'">'.$req['merchant_name'].'</a></td>
                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">'.\FFM::dollar($req['payment_amount']).'</td>
                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">'.\FFM::date($req['payment_date']).'</td>
                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">'.$req['status'].'</td>
                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">'.$req['message'].'</td>
                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">'.$req['type'].'</td>
                        </tr>';
                        }
                        $html .= '</tbody></table>';
                        $msg['rcode_report_table'] = $html;
                        $msg['subject'] = $msg['title'];
                        $msg['unqID'] = unqID();
                        $fileName = $msg['title'].'.csv';
                        $msg['status'] = 'ach_rcode_mail';
                        $exportCSV = $this->generateACHCheckStatusCSV($rcode_ach);
                        $msg['atatchment'] = $exportCSV;
                        $msg['atatchment_name'] = $fileName;
                    }
                }
                break;
            case 'MACHR':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $transaction = [];
                    $transactions = [];
                    $payment_date = date('Y-m-d');
                    $message['title'] = 'Merchant ACH Sent report for '.\FFM::date($payment_date);
                    $transactions[] = ['merchant_id' => $merchant->id, 'merchant_name' => $merchant->name, 'amount' => 2000, 'transaction' => $transaction, 'status' => $transaction['status'] ?? '', 'auth_code' => $transaction['authcode'] ?? '', 'reason' => $transaction['reason'] ?? '', 'type' => 'Payment'];
                    $exportCSV = $this->generateACHSentCSV($transactions, $payment_date);
                    $fileName = $message['title'].'.csv';
                    $msg['atatchment'] = $exportCSV;
                    $msg['atatchment_name'] = $fileName;
                    $msg['title'] = $message['title'];
                    $msg['content'] = $transactions;
                    $msg['status'] = 'ach_sent_report';
                    $msg['subject'] = $message['title'];
                    $msg['payment_date'] = \FFM::date($payment_date);
                    $msg['checked_time'] = \FFM::datetime(Carbon::now()->toDateTimeString());
                    $msg['unqID'] = unqID();
                    $msg['total_processed'] = \FFM::dollar(1100);
                    $msg['total_processed_payment'] = \FFM::dollar(1000);
                    $msg['total_processed_fee'] = \FFM::dollar(10);
                    $msg['count_total'] = 2;
                    $msg['count_payment'] = 1;
                    $msg['count_fee'] = 1;
                    $msg['count_total_processing'] = 2;
                    $msg['count_payment_processing'] = 1;
                    $msg['count_fee_processing'] = 1;
                }
                break;
            case 'ACHSR':
                $investor = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                if ($investor) {
                    $payment_date = date('Y-m-d');
                    $message['title'] = 'ACH Syndicate Sent report for '.$payment_date;
                    $data = [];
                    $data[] = ['investor_id' => $investor->id, 'investor_name' => $investor->name, 'status' => 'Processing', 'amount' => 1000, 'type' => 'same_day_', 'response' => 'accepted', 'payment_date' => $payment_date, 'order_id' => 1231232, 'auth_code' => 'checkauth-12312', 'transaction_method' => 'debit'];
                    $exportCSV = $this->generateACHSyndicationCSV($data, $payment_date);
                    $fileName = $message['title'].'.csv';
                    $msg['atatchment'] = $exportCSV;
                    $msg['atatchment_name'] = $fileName;
                    $msg['title'] = $message['title'];
                    $msg['content'] = 'success';
                    $msg['status'] = 'ach_syndication_sent_report';
                    $msg['subject'] = $message['title'];
                    $msg['payment_date'] = $payment_date;
                    $msg['checked_time'] = \FFM::datetime(Carbon::now()->toDateTimeString());
                    $msg['unqID'] = unqID();
                    $count_total = count($data);
                    $count_total_processing = $total_processed = 0;
                    foreach ($data as $transaction) {
                        if ($transaction['status'] == 'Processing') {
                            $count_total_processing++;
                            $total_processed += $transaction['amount'];
                        }
                    }
                    $msg['total_processed'] = \FFM::dollar($total_processed);
                    $msg['count_total'] = $count_total;
                    $msg['count_total_processing'] = $count_total_processing;
                }
                break;
            case 'PYPS':
                $title = 'Payment Paused';
                $payment_pause = PaymentPause::inRandomOrder()->first();
                $rcode = null;
                $merchant = Merchant::inRandomOrder()->first();
                if ($payment_pause && $merchant) {
                    $rcode = Rcode::where('description', $payment_pause->paused_by)->first();
                    $msg['title'] = $title;
                    $msg['content']['data'] = $payment_pause;
                    $msg['content']['rcode'] = $rcode;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['status'] = 'payment_paused';
                    $msg['subject'] = $title;
                    if ($rcode) {
                        $msg['paused_type'] = 'due to Rcode - '.$rcode->code;
                    } else {
                        $msg['paused_type'] = 'manually by';
                    }
                    $msg['paused_by'] = $payment_pause->paused_by;
                    $msg['paused_at'] = \FFM::datetime($payment_pause->paused_at);
                    $msg['unqID'] = unqID();
                }
                break;
            case 'PYRS':
                $title = 'Payment Resumed';
                $payment_resume = PaymentPause::whereNotNull('resumed_by')->inRandomOrder()->first();
                $merchant = Merchant::inRandomOrder()->first();
                if ($payment_resume && $merchant) {
                    $msg['title'] = $title;
                    $msg['content']['data'] = $payment_resume;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['status'] = 'payment_resumed';
                    $msg['subject'] = $title;
                    $msg['unqID'] = unqID();
                    $msg['resumed_by'] = $payment_resume->resumed_by;
                    $msg['resumed_at'] = \FFM::datetime($payment_resume->resumed_at);
                }
                break;
            case 'MRTD':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $msg['title'] = 'We noticed you missed a payment...';
                    $msg['status'] = 'merchant_returnd';
                    $msg['unqID'] = unqID();
                    $msg['subject'] = $msg['title'];
                    $msg['merchant_id'] = $merchant->id;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['amount'] = 1000;
                    $msg['to_mail'] = 'test@gmail.com';
                }
                break;
            case 'PYMNT':
                $merchant = Merchant::inRandomOrder()->first();
                $user_details = UserDetails::whereNotNull('liquidity')->inRandomOrder()->first();
                if ($merchant) {
                    $msg['title'] = 'Payment successful';
                    $msg['subject'] = 'Payment successful';
                    $msg['content'] = 'Successful Added The Payment';
                    $msg['to_mail'] = $merchant->notification_email;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['status'] = 'payment_send';
                    $msg['amount'] = 1000;
                    $msg['actual_amount'] = \FFM::dollar(1000);
                    $msg['date'] = \FFM::date(date('Y-m-d'));
                    $msg['wallet_amount'] = ($user_details) ? $user_details->liquidity : false;
                    $card_number = '424242424242';
                    $card_number = substr($card_number, -4);
                    $msg['card_number'] = $card_number;        
                    $msg['unqID'] = unqID();
                    $msg['mail_to'] = 'user';
                    if ($msg['wallet_amount']) {
                        $msg['content'] = 'This is the Accounting Department at Velocity Group USA. We have just received a Credit Card Payment (Card Number ** ** '.$card_number.') for adding fund to your wallet. The amount paid was '.\FFM::dollar($msg['amount']).' (inclusive a processing fee of 3.75%) on '.$msg['date'].'. Your wallet has been added with '.$msg['actual_amount'].' and at present stands at '.$msg['wallet_amount'].' .';
                    } else {
                        $msg['content'] = 'We have just received a Credit Card payment (Card Number ** ** '.$card_number.'). The amount paid was '.\FFM::dollar($msg['amount']).' on '.$msg['date'].'.';
                    }
                }
                break;
            case 'PYMNA':
                $merchant = Merchant::inRandomOrder()->first();
                $user_details = UserDetails::whereNotNull('liquidity')->inRandomOrder()->first();
                if ($merchant) {
                    $msg['title'] = 'Payment successful';
                    $msg['subject'] = 'Payment successful';
                    $msg['content'] = 'Successful Added The Payment';
                    $msg['to_mail'] = $merchant->notification_email;
                    $msg['merchant_id'] = $merchant->id;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['status'] = 'payment_send';
                    $msg['amount'] = 1000;
                    $msg['actual_amount'] = \FFM::dollar(1000);
                    $msg['date'] = \FFM::date(date('Y-m-d'));
                    $msg['wallet_amount'] = ($user_details) ? $user_details->liquidity : false;
                    $card_number = '424242424242';
                    $card_number = substr($card_number, -4);
                    $msg['card_number'] = $card_number;        
                    $msg['unqID'] = unqID();
                    $msg['mail_to'] = 'admin';
                    if ($msg['wallet_amount']) {
                        $msg['content'] = 'This is the Accounting Department at Velocity Group USA. We have just received a Credit Card Payment (Card Number ** ** '.$card_number.') for adding fund to your wallet. The amount paid was '.\FFM::dollar($msg['amount']).' (inclusive a processing fee of 3.75%) on '.$msg['date'].'. Your wallet has been added with '.$msg['actual_amount'].' and at present stands at '.$msg['wallet_amount'].' .';
                    } else {
                        $msg['content'] = 'We have just received a Credit Card payment (Card Number ** ** '.$card_number.'). The amount paid was '.\FFM::dollar($msg['amount']).' on '.$msg['date'].'.';
                    }
                }
                break;
            case 'IAPR':
                $date = \FFM::date(date('Y-m-d'));
                $msg['title'] = 'Investor ACH Processing Report For '.$date;
                $investor_ach_request_ids = InvestorAchRequest::inRandomOrder()->limit(3)->pluck('id');
                $CSVResponds = PaymentHelper::InvestorAchRequestGenerateCSV($investor_ach_request_ids);
                $exportCSV = $CSVResponds['export'];
                $fileName = $msg['title'] . '.csv';
                $msg['atatchment'] = $exportCSV;
                $msg['atatchment_name'] = $fileName;
                $msg['totalCount'] = $CSVResponds['count'];
                $msg['status'] = 'investor_ach_request_send_report';
                $msg['subject'] = $msg['title'];
                $msg['date'] = $date;
                $msg['checked_time'] = \FFM::datetime(Carbon::now());
                $msg['unqID'] = unqID();
                $msg['debitAcceptedAmount'] = \FFM::dollar($CSVResponds['debitAcceptedAmount']);
                $msg['creditAcceptedAmount'] = \FFM::dollar($CSVResponds['creditAcceptedAmount']);
                $msg['debitReturnedAmount'] = \FFM::dollar($CSVResponds['debitReturnedAmount']);
                $msg['creditReturnedAmount'] = \FFM::dollar($CSVResponds['creditReturnedAmount']);
                $msg['debitProcessingAmount'] = \FFM::dollar($CSVResponds['debitProcessingAmount']);
                $msg['creditProcessingAmount'] = \FFM::dollar($CSVResponds['creditProcessingAmount']);
                break;
            case 'IARR':
                $date = \FFM::date(date('Y-m-d'));
                $title = 'Investor ACH Recheck Report For ';
                $investor_ach_request_ids = InvestorAchRequest::inRandomOrder()->limit(2)->pluck('id');
                $CSVResponds = PaymentHelper::InvestorAchRequestGenerateCSV($investor_ach_request_ids);
                $msg['title'] = $title . $date;
                $exportCSV = $CSVResponds['export'];
                $fileName = $msg['title'] . '.csv';
                $msg['atatchment'] = $exportCSV;
                $msg['atatchment_name'] = $fileName;
                $msg['totalCount'] = $CSVResponds['count'];
                $msg['status'] = 'investor_ach_recheck_report';
                $msg['subject'] = $msg['title'];
                $msg['date'] = $date;
                $msg['checked_time'] = \FFM::datetime(Carbon::now());
                $msg['unqID'] = unqID();
                $msg['debitAcceptedAmount'] = \FFM::dollar($CSVResponds['debitAcceptedAmount']);
                $msg['creditAcceptedAmount'] = \FFM::dollar($CSVResponds['creditAcceptedAmount']);
                $msg['debitReturnedAmount'] = \FFM::dollar($CSVResponds['debitReturnedAmount']);
                $msg['creditReturnedAmount'] = \FFM::dollar($CSVResponds['creditReturnedAmount']);
                break;
            case 'ACRR':
                $type = 'debit';
                $investor = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                if ($investor) {
                    $UserDetails = UserDetails::where('user_id', $investor->id)->first();
                    $msg['title'] = 'Ach ' . $type . ' Request Returned';
                    $msg['subject'] = $msg['title'];
                    $msg['investor_name'] = $investor->name;
                    $msg['investor_id'] = $investor->id;
                    $msg['amount'] = 1000;
                    $msg['type'] = $type;
                    $msg['date'] = \FFM::date(date('Y-m-d'));
                    $msg['Creator'] = 'Admin';
                    $msg['liquidity'] = \FFM::dollar($UserDetails->liquidity);
                    $msg['status'] = 'investor_ach_request_returned';
                }
                break;
            case 'ACDR':
                $investor = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', User::INVESTOR_ROLE)->inRandomOrder()->select('users.id', 'users.name')->first();
                if ($investor) {
                    $msg['title'] = 'Ach Debit Requested';
                    $msg['subject'] = $msg['title'];
                    $msg['Investor'] = $investor->name;
                    $msg['investor_id'] = $investor->id;
                    $msg['amount'] = 10;
                    $msg['type'] = 'debit';
                    $msg['date'] = \FFM::date(date('Y-m-d'));
                    $msg['Creator'] = 'Admin';
                    $msg['creator_name'] = Auth::user()->name;
                    $msg['to_mail'] = $investor->notification_email;
                    $msg['status'] = 'investor_ach_request';
                }
                break;
            case 'ACSR':
                $ach_request = InvestorAchRequest::where('status_response', 'like', '%settled%')->inRandomOrder()->first();
                if ($ach_request) {
                    $UserDetails = UserDetails::where('user_id', $ach_request->investor_id)->first();
                    $msg['title'] = 'Ach '.$ach_request->TransactionTypeName.' Request Settled';
                    $msg['subject'] = $msg['title'];
                    $msg['investor_name'] = $ach_request->Investor->name;
                    $msg['investor_id'] = $ach_request->investor_id;
                    $msg['amount'] = $ach_request->amount;
                    $msg['type'] = $ach_request->TransactionTypeName;
                    $msg['date'] = \FFM::date($ach_request->date);
                    $msg['Creator'] = 'Admin';
                    $msg['liquidity'] = \FFM::dollar($UserDetails->liquidity);
                    $msg['to_mail'] = $ach_request->Investor->notification_email;
                    $msg['status'] = 'investor_ach_request_settlement';
                } 
                break;
            case 'ACDP':
                $today = date('Y-m-d');
                $date = \FFM::date($today);
                $InvestorAchRequests = InvestorAchRequest::orderby('id');
                $InvestorAchRequests->where('ach_status', '!=', InvestorAchRequest::AchStatusDeclined);
                $InvestorAchRequests->where('ach_request_status', InvestorAchRequest::AchRequestStatusProcessing);
                $investor_ach_request_ids = $InvestorAchRequests->pluck('id')->toArray();
                $CSV = PaymentHelper::InvestorAchRequestGenerateCSV($investor_ach_request_ids);
                $msg['title'] = 'Investor ACH Processing For More Than 9 Days as of '.$date;
                $exportCSV = $CSV['export'];
                $fileName = $msg['title'].'.csv';
                $msg['atatchment'] = $exportCSV;
                $msg['atatchment_name'] = $fileName;
                $msg['totalCount'] = $CSV['count'];
                $msg['status'] = 'pending_ach_delete_mail';
                $msg['subject'] = $msg['title'];
                $msg['date'] = $date;
                $msg['checked_time'] = \FFM::datetime(Carbon::now());
                $msg['unqID'] = unqID();
                $array_params['date'] = $today;
                $array_params['ach_ids'] = $investor_ach_request_ids;
                $array_params['status'] = 1;
                $encoded_array = urlencode(serialize($array_params));
                $confirm_url = route('admin::payments::investor-ach-status.delete-verification', $encoded_array);
                $msg['confirm_url'] = $confirm_url;
                break;
            case 'MACHD':
                $data = [];
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $sub_statuses = DB::table('sub_statuses')->pluck('name', 'id')->toArray();
                    $labels = DB::table('label')->pluck('name', 'id')->toArray();
                    $data[] = [
                        'merchant_id' => $merchant->id,
                        'merchant_name' => $merchant->name,
                        'sub_status' => $sub_statuses[$merchant->sub_status_id],
                        'balance' => \FFM::dollar(1000),
                        'anticipated_total' => \FFM::dollar(1000),
                        'difference' => \FFM::dollar(0),
                        'status' => 2,
                        'type' => 'ACH balance overage',
                        'complete_percentage' => \FFM::percent($merchant->complete_percentage),
                        'label' => $labels[$merchant->label],
                    ];
                }
                
                $title = 'Merchants with difference between ACH balance and actual balance';
                $msg['title'] = $title;
                $msg['status'] = 'merchant_unit_test';
                $msg['subject'] = $title;
                $msg['date_time'] = \FFM::datetime(Carbon::now()->toDateTimeString());
                $msg['template_type'] = 'ach_difference';
                $msg['count'] = count($data);
                $msg['unqID'] = unqID();
                $titles = ['No', 'Merchant ID', 'Merchant Name', 'Sub Status', 'Type', 'Balance', 'Anticipated Amount', 'Difference', 'Complete Percentage', 'Label'];
                $values = ['', 'merchant_id', 'merchant_name', 'sub_status', 'type', 'balance', 'anticipated_total', 'difference', 'complete_percentage', 'label'];    
                $exportCSV = app('App\Console\Commands\AchDifferenceCommand')->generateCSV($data, $titles, $values);
                $fileName = 'test.csv';
                $msg['atatchment_name'] = $fileName;
                $msg['atatchment'] = $exportCSV;
                break;
            case 'TWFEN':
                $msg['title'] = "You've enabled two-step verification";
                $msg['subject'] = "You've enabled two-step verification";
                $msg['status'] = 'two_step_enabled_verification_notification';
                $msg['to_mail'] = 'test@gmail.com';
                $msg['email'] = 'test@gmail.com';
                $msg['unqID'] = unqID();
                break;
            case 'TWFD':
                $msg['title'] = "You've disabled two-step verification";
                $msg['subject'] = "You've disabled two-step verification";
                $msg['status'] = 'two_step_disabled_verification_notification';
                $msg['to_mail'] = 'test@gmail.com';
                $msg['email'] = 'test@gmail.com';
                $msg['unqID'] = unqID();
                break;
            case 'ACHDF':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $email_title = 'No Scheduled ACH Payments left';
                    $msg['title'] = $email_title;
                    $msg['status'] = 'merchant_ach_payments_deficit';
                    $msg['subject'] = $email_title;
                    $msg['unqID'] = unqID();
                    $msg['future_payments_count'] = 0;
                    $msg['makeup_payments'] = 4;
                    $msg['merchant_name'] = $merchant->name;
                    $msg['default_payment_amount'] = \FFM::dollar(100);
                    $msg['url'] = route('admin::merchants::payment-terms', ['mid' => $merchant->id]);
                }
                break;
            case 'MACC':
                $merchant = Merchant::inRandomOrder()->first();
                if ($merchant) {
                    $msg['title'] = 'Merchant ACH Credit Requested';
                    $msg['status'] = 'ach_merchant_credit_request';
                    $msg['subject'] = 'Merchant ACH Credit Requested';
                    $msg['unqID'] = unqID();
                    $msg['checked_time'] = \FFM::datetime(Carbon::now()->toDateTimeString());
                    $msg['payment_amount'] = \FFM::dollar(100);
                    $msg['merchant_name'] = $merchant->name;
                    $msg['creator_name'] = Auth::user()->name;
                    $msg['merchant_view_link'] = route('admin::merchants::view', ['id' => $merchant->id]);
                }
                break;
            default:
                $template = Template::find($template_id);
                if ($template) {
                    $subject = $template->subject;
                    $title = $template->title;
                    if ($subject == '[title]') {
                        $subject = 'Sample Email';
                    }
                    if ($title == '[title]') {
                        $title = 'Sample Title';
                    }
                    $msg['subject'] = $subject;
                    $msg['name'] = 'Jhon';
                    $msg['title'] = $title;
                    $msg['offer'] = 'Sample Offer';
                    $msg['status'] = 'marketing_offer';
                    $msg['template'] = $template_id;
                    $msg['unqID'] = unqID();
                }
                break;
        }
        if (count($msg) > 0) {
            $emails = Settings::value('email');
            $email_id_arr = explode(',', $emails); 
            $msg['to_mail'] = $email_id_arr;
            $template = Template::find($template_id);
            if ($template && $template->assignees) {
                $avoid_mail = ['INVTR', 'MERC'];
                if (!in_array($template->temp_code, $avoid_mail)) {
                    $template_assignee = explode(',', $template->assignees);
                    $bcc_mails = [];
                    foreach ($template_assignee as $assignee) {
                        $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                        $role_mails = array_diff($role_mails, $email_id_arr);
                        $bcc_mails[] = $role_mails;
                    }
                    $msg['bcc'] = Arr::flatten($bcc_mails);
                }
            }
            $emailJob = (new CommonJobs($msg));
            dispatch($emailJob);
            return true;
        }
        return false;
    }
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
    public function generateACHSentCSV($data, $payment_date)
    {
        $excel_array[] = ['No', 'Merchant Name', 'Merchant ID', 'Status', 'Auth Code', 'Payment', 'Payment Date', 'Type'];
        $i = 1;
        $total_amount = 0;
        foreach ($data as $key => $tr) {
            $excel_array[$i]['No'] = $i;
            $excel_array[$i]['Merchant Name'] = $tr['merchant_name'];
            $excel_array[$i]['Merchant ID'] = $tr['merchant_id'];
            $excel_array[$i]['Status'] = $tr['status'];
            $excel_array[$i]['Auth Code'] = $tr['auth_code'];
            $excel_array[$i]['Payment'] = \FFM::dollar($tr['amount']);
            $excel_array[$i]['Payment Date'] = \FFM::date($payment_date);
            $excel_array[$i]['Type'] = $tr['type'];
            $total_amount = $total_amount + $tr['amount'];
            $i++;
        }
        $total_amount = \FFM::dollar($total_amount);
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['Merchant Name'] = null;
        $excel_array[$i]['Merchant ID'] = null;
        $excel_array[$i]['Status'] = null;
        $excel_array[$i]['Auth Code'] = 'TOTAL';
        $excel_array[$i]['Payment'] = $total_amount;
        $export = new Data_arrExport($excel_array);

        return $export;
    }
    public function generateACHCheckStatusCSV($data)
    {
        $excel_array[] = ['No', 'Merchant Name', 'Merchant ID', 'Status', 'Payment Amount', 'Response', 'Payment Date', 'Type'];
        $i = 1;
        $total_payment = 0;
        foreach ($data as $key => $tr) {
            $excel_array[$i]['No'] = $i;
            $excel_array[$i]['Merchant Name'] = $tr['merchant_name'];
            $excel_array[$i]['Merchant ID'] = $tr['merchant_id'];
            $excel_array[$i]['Status'] = $tr['status'];
            $excel_array[$i]['Payment Amount'] = \FFM::dollar($tr['payment_amount']);
            $excel_array[$i]['Response'] = $tr['message'];
            $excel_array[$i]['Payment Date'] = \FFM::date($tr['payment_date']);
            $excel_array[$i]['Type'] = $tr['type'];
            $total_payment = $total_payment + $tr['payment_amount'];
            $i++;
        }
        $total_payments = \FFM::dollar($total_payment);
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['Merchant Name'] = null;
        $excel_array[$i]['Merchant ID'] = null;
        $excel_array[$i]['Status'] = 'TOTAL';
        $excel_array[$i]['Payment Amount'] = count($data) ? $total_payments : \FFM::dollar(0);
        $export = new Data_arrExport($excel_array);

        return $export;
    }
    public function generateACHSyndicationCSV($data, $payment_date)
    {
        $excel_array[] = ['No', 'Investor Name', 'Investor ID', 'Status', 'Payment', 'Auth Code', 'Payment Date', 'Transaction Type', 'Response', 'Order ID', 'Transaction Method'];
        $i = 1;
        foreach ($data as $key => $tr) {
            $excel_array[$i]['No'] = $i;
            $excel_array[$i]['Investor Name'] = $tr['investor_name'];
            $excel_array[$i]['Investor ID'] = $tr['investor_id'];
            $excel_array[$i]['Status'] = $tr['status'];
            $excel_array[$i]['Payment'] = \FFM::sr($tr['amount']);
            $excel_array[$i]['Auth Code'] = $tr['auth_code'];
            $excel_array[$i]['Payment Date'] = $payment_date;
            $excel_array[$i]['Transaction Type'] = $tr['type'];
            $excel_array[$i]['Response'] = $tr['response'];
            $excel_array[$i]['Order ID'] = $tr['order_id'];
            $excel_array[$i]['Transaction Method'] = $tr['transaction_method'];
            $i++;
        }
        $excel_array[$i]['No'] = null;
        $excel_array[$i]['Investor Name'] = null;
        $excel_array[$i]['Investor ID'] = null;
        $excel_array[$i]['Status'] = 'TOTAL';
        $excel_array[$i]['Payment'] = '=DOLLAR(SUM(E2:E'.$i.'))';
        $export = new Data_arrExport($excel_array);

        return $export;
    }
}
