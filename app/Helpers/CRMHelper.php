<?php

namespace App\Helpers;

use App\CompanyAmount;
use App\Events\UserHasAssignedInvestor;
use App\Http\Controllers\Controller;
use App\Industries;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\InvestorTransactionRepository;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantDetails;
use App\MerchantUser;
use App\MNotes;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\Settings;
use App\Template;
use App\SubStatus;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Str;
use PayCalc;
use InvestorHelper;

class CRMHelper 
{
    public function __construct(IMerchantRepository $merchant,IRoleRepository $role)
    {
         $this->merchant = $merchant;
         $this->role = $role;  
   } 
  public function merchantCreate($request)
  {
  	try
  	{
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
  	    $input = $request->all();
        $validator = Validator::make($request->all(), ['crm_id' => 'unique:merchants_details']);
            if ($validator->fails()) {
                throw new Exception($validator->messages(), 1);
        }

        if (!isset($input['name']) || empty($input['name'])) {
        	throw new Exception("The name field is required", 1);
        }
        if (!isset($input['first_name']) || empty($input['first_name'])) {
        	throw new Exception("The first name field is required", 1);
        }
        if (!isset($input['industry']) || empty($input['industry'])) {
        	throw new Exception("The industry field is required", 1);
        }
        if (! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        	throw new Exception("The email field is invalid email address", 1);  
        }
        if (!isset($input['funded']) || empty($input['funded'])) {
        	throw new Exception("The funded amount field is required", 1); 
        }
        if (!isset($input['factor_rate']) || empty($input['factor_rate'])) {
        	throw new Exception("The factor rate field is required", 1);
        }
        if (!isset($input['date_funded']) || empty($input['date_funded'])) {
        	throw new Exception("The funded date field is required", 1);
        }
        if (!isset($input['advance_type']) || empty($input['advance_type'])) {
        	throw new Exception("The advance_type field is required", 1);
        }
        if (!isset($input['terms_in_days']) || empty($input['terms_in_days'])) {
        	throw new Exception("The terms_in_days field is required", 1);
        }
        $cell_phone='';
        if(isset($input['cell_phone']))
        {
             if(!preg_match("/^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/", $input['cell_phone'],$matches)) {

             	throw new Exception("Please Enter valid number.Ex:(417) 555-1234", 1);

               // return response()->json(['status' => 0, 'error' => 'Please Enter valid number.Ex:(417) 555-1234', 'data' => '']); 
             
           }
           preg_match("/^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/", $input['cell_phone'],$matches);
           $cell_phone = '('.$matches[1].')' . ' ' .$matches[2] . '-' . $matches[3];
            
        }

       if ($input['industry']) {
            $ignore_arr = ['_', '-', '.', ',', '/', ',',' '];
            $industry = str_replace($ignore_arr,' ', $input['industry']);
            $industries = [];
        }

        if (isset($input['state'])) {
            $states = DB::table('us_states')->select('id')->where('state_abbr', '=', $input['state'])->first();
        }

        if(isset($input['industry']))
        {
           $industries = DB::table('industries')->select('id')
            ->where('name','LIKE','%'. $input['industry'] .'%')->first();

        }

        if (isset($input['owner_state'])) {
            $owner_state = DB::table('us_states')->select('id')->where('state_abbr', '=', $input['owner_state'])
            ->first();
        }

        if (isset($input['partner_state'])) {
            $partner_state = DB::table('us_states')->select('id')->where('state_abbr', '=', $input['partner_state'])->first();
        }

        if (isset($input['source'])) {
            $merchant_source = DB::table('merchant_source')->select('id')->where('name', '=', $input['source'])->first();
        }
        if (($input['advance_type'] == 'weekly_ach')) {
            $terms = round($input['terms_in_days'] / 5);
        } else {
            $terms = $input['terms_in_days'];
        }
        $payment_amount = $input['funded'] * $input['factor_rate'] / $terms;
        $date_funded = (isset($input['date_funded']))?Carbon::createFromFormat('Y-m-d', $input['date_funded'])->format('Y-m-d'):'';
        $crm = isset($_REQUEST['PHP_AUTH_USER']) ? $_REQUEST['PHP_AUTH_USER'] : '';
        $crm_user = DB::table('user_has_roles')->where('role_id', User::CRM_ROLE)->select('model_id')->first();
        $creator_id = null;
        if ($crm && isset($crm_user)) {
            $creator_id = $crm_user->model_id;
        }
        $crm_user = DB::table('user_has_roles')->where('role_id', User::CRM_ROLE)->select('model_id')->first();
        $lender=User::where('id',74)->select('global_syndication','management_fee','s_prepaid_status','underwriting_fee','underwriting_status')->first();

        $values = [
                       'name' => $input['name'],
                       'first_name' => isset($input['first_name']) ? ($input['first_name']) : '',
                       'last_name' => isset($input['last_name']) ? ($input['last_name']) : '',
                       'business_address'=>isset($input['business_address'])?$input['business_address']:'',
                       'funded' => isset($input['funded']) ? ($input['funded']) : 0,
                       'max_participant_fund' => $input['funded'],
                       'rtr' => $input['funded'] * $input['factor_rate'],
                       'credit_score' => ! empty($input['credit_score']) ? $input['credit_score'] : ' ',
                       'factor_rate' => $input['factor_rate'],
                       'date_funded' => $date_funded,
                       'commission' => ! empty($input['commission']) ? $input['commission'] : ' ',
                       'advance_type' => ! empty($input['advance_type']) ? $input['advance_type'] : ' ',
                       'industry_id' => !empty($industries) ? (isset($industries)?$industries->id:'') : '',
                       'source_id' => ! empty($merchant_source->id) ? $merchant_source->id : ' ',
                       'state_id' => ! empty($states->id) ? (isset($states)?$states->id:'') : 2,
                       'sub_status_id' => 1,
                       'lender_id' => 74,
                       'm_syndication_fee'=>isset($lender)?$lender->global_syndication:'',
                        'm_mgmnt_fee'=>isset($lender)?$lender->management_fee:'',
                        'm_s_prepaid_status'=>isset($lender)?$lender->s_prepaid_status:0,
                     'underwriting_fee'=>isset($lender)?$lender->underwriting_fee:'',
                     'underwriting_status'=>isset($lender)?$lender->underwriting_status:'',
                     'pmnts' => $terms,
                        'payment_amount' => $payment_amount,
                         'origination_fee' => isset($input['origination_fee']) ? ($input['origination_fee']) : 0,
                         'email' => isset($input['email']) ? $input['email'] : null,
                         'creator_id' => $creator_id,
                         'up_sell_commission' => isset($input['up_sell_commission']) ? ($input['up_sell_commission']) : 0,
                          'phone' => isset($input['phone']) ? ($input['phone']) : null,
                          'cell_phone' => isset($input['cell_phone']) ? $cell_phone : null,
                           'experian_intelliscore' => isset($input['experian_intelliscore']) ? ($input['experian_intelliscore']) : null,
                           'experian_financial_score' => isset($input['experian_financial_score']) ? $input['experian_financial_score'] : null,
                           'notification_email' => isset($input['notification_email']) ? ($input['notification_email']) : null,
                            'zip_code' => isset($input['zip']) ? ($input['zip']) : null,
                            'city' => isset($input['city']) ? ($input['city']) : null ];
        $merchant_id = Merchant::create($values)->id;
        if ($merchant_id) {
            $crmValues = [
            'merchant_id'=>$merchant_id,
            'annual_revenue' => isset($input['annual_revenue']) ? ($input['annual_revenue']) : 0,
             'iso_name' => isset($input['iso_name']) ? ($input['iso_name']) : null,
             'deal_type' => isset($input['deal_type']) ? ($input['deal_type']) : null,
             'position' => isset($input['position']) ? ($input['position']) : null,
              'monthly_revenue' => isset($input['monthly_revenue']) ? ($input['monthly_revenue']) : 0,
             'withhold_percentage' => isset($input['withhold_percentage']) ? ($input['withhold_percentage']) : 0,
             'partner_credit_score' => isset($input['partner_credit_score']) ? ($input['partner_credit_score']) : 0,
              'owner_credit_score' => isset($input['owner_credit_score']) ? ($input['owner_credit_score']) : 0,
              'agent_name' => isset($input['agent_name']) ? ($input['agent_name']) : null,
              'date_business_started' => isset($input['date_business_started']) ? ($input['date_business_started']) : null,
              'under_writer' => isset($input['under_writer']) ? ($input['under_writer']) : null,
              'entity_type' => isset($input['entity_type']) ? ($input['entity_type']) : null,
              'exact_legal_company_name'=>isset($input['exact_legal_company_name']) ? ($input['exact_legal_company_name']) : null,
              'physical_address'=> isset($input['physical_address']) ? ($input['physical_address']) : null,
              'physical_address2'=> isset($input['physical_address2']) ? ($input['physical_address2']) : null,
              'work_phone'=>isset($input['work_phone']) ? ($input['work_phone']) : null,
              'fax'=>isset($input['fax']) ? ($input['fax']) : null,
              'federal_tax_id'=>isset($input['federal_tax_id']) ? ($input['federal_tax_id']) : null,
              'ownership_length'=>isset($input['ownership_length']) ? ($input['ownership_length']) : 0,
              'website'=>isset($input['website']) ? ($input['website']) : null,
              'use_of_proceeds'=>isset($input['use_of_proceeds']) ? ($input['use_of_proceeds']) : null,
              'requested_amount'=>isset($input['requested_amount']) ? ($input['requested_amount']) : 0,
              'lead_source'=>isset($input['lead_source']) ? ($input['lead_source']) : null,
              'campaign'=>isset($input['campaign']) ? ($input['campaign']) : null,
              'owner_first_name'=>isset($input['owner_first_name']) ? ($input['owner_first_name']) : null,
              'owner_last_name'=>isset($input['owner_last_name']) ? ($input['owner_last_name']) : null,
              'ownership_percentage'=>isset($input['ownership_percentage']) ? ($input['ownership_percentage']) : 0,
              'owner_email'=>isset($input['owner_email']) ? ($input['owner_email']) : null,
               'owner_home'=>isset($input['owner_home']) ? ($input['owner_home']) : null,
               'owner_address2'=>isset($input['owner_address2']) ? ($input['owner_address2']) : null,
              'home_address'=>isset($input['home_address']) ? ($input['home_address']) : null,
              'owner_city'=>isset($input['owner_city']) ? ($input['owner_city']) : 0,
              'owner_state_id'=>isset($input['owner_state']) ? (isset($owner_state)?$owner_state->id:0) : 0,
              'owner_zip'=>isset($input['owner_zip']) ? ($input['owner_zip']) : null,
              'owner_cell'=>isset($input['owner_cell']) ? ($input['owner_cell']) : null,
              'owner_cell2'=>isset($input['owner_cell2']) ? ($input['owner_cell2']) : null,
              'ssn'=>isset($input['ssn']) ? ($input['ssn']) : null,
              'dob'=>isset($input['dob']) ? ($input['dob']) : null,
              'partner_first_name'=>isset($input['partner_first_name']) ? ($input['partner_first_name']) : null,
              'partner_last_name'=>isset($input['partner_last_name']) ? ($input['partner_last_name']) : null,
              'partner_email'=>isset($input['partner_email']) ? ($input['partner_email']) : null,
              'partner_ownership_percentage'=>isset($input['partner_ownership_percentage']) ? ($input['partner_ownership_percentage']) : 0,
              'partner_home_address'=>isset($input['partner_home_address']) ? ($input['partner_home_address']) : null,
              'partner_city'=>isset($input['partner_city']) ? ($input['partner_city']) : 0,
              'partner_state_id'=>isset($input['partner_state_id']) ? (isset($partner_state->id)?$partner_state->id:0) : 0,
              'partner_zip'=>isset($input['partner_zip']) ? ($input['partner_zip']) : 0,
              'partner_ssn'=>isset($input['partner_ssn']) ? ($input['partner_ssn']) : null,
              'partner_dob'=>isset($input['partner_dob']) ? ($input['partner_dob']) : null,
              'partner_cell_hash'=>isset($input['partner_cell_hash']) ? ($input['partner_cell_hash']) : null,
              'partner_home_hash'=>isset($input['partner_home_hash']) ? ($input['partner_home_hash']) : null,
              'partner_address2'=>isset($input['partner_address2']) ? ($input['partner_address2']) : null,
              'partner_cell2'=>isset($input['partner_cell2']) ? ($input['partner_cell2']) : null,
              'disposition'=>isset($input['disposition']) ? ($input['disposition']) : null,
              'product_sold'=>isset($input['product_sold']) ? ($input['product_sold']) : null,
               'marketing_notification'=>isset($input['marketing_notification']) ? ($input['marketing_notification']) : null,
              'buy_rate'=>isset($input['buy_rate']) ? ($input['buy_rate']) : 0,
              'created_date'=>isset($input['created_date']) ? ($input['created_date']) : null,
              'payback_amount'=>isset($input['payback_amount']) ? ($input['payback_amount']) : null,
              'lender_email'=>isset($input['lender_email']) ? ($input['lender_email']) : null,
              'no_of_deposit'=>isset($input['no_of_deposit']) ? ($input['no_of_deposit']) : 0,
              'negative_days'=>isset($input['negative_days']) ? ($input['negative_days']) : 0,
              'nsf'=>isset($input['nsf']) ? ($input['nsf']) : 0,
              'fico_score_primary'=>isset($input['fico_score_primary']) ? ($input['fico_score_primary']) : 0,
              'fico_score_secondary'=>isset($input['fico_score_secondary']) ? ($input['fico_score_secondary']) : 0,
              'broker_commission'=>isset($input['broker_commission']) ? ($input['broker_commission']) : 0,
              'crm_id'=>isset($input['crm_id']) ? ($input['crm_id']) : 0,
              'terms_in_days'=>isset($input['terms_in_days']) ? ($input['terms_in_days']) : 0,

            ];

            MerchantDetails::create($crmValues);
            $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
            $company = [];
            if ($companies) {
                foreach ($companies as $key => $value) {
                    $company[$key]['merchant_id'] = $merchant_id;
                    $company[$key]['company_id'] = $value;
                    $company[$key]['max_participant'] = 0;
                }
                CompanyAmount::insert($company);
            }
        }
        $merchant = Merchant::find($merchant_id);
        $bank_name = isset($input['bank']) ? $input['bank'] : null;
        $account_holder_name = isset($input['account_name']) ? $input['account_name'] : null;
        $account_no = isset($input['account_no']) ? $input['account_no'] : 0;
        $routing_no = isset($input['routing_no']) ? $input['routing_no'] : null;
        $email = User::where('email', $input['email'])->count();
        if ($email == 0 && isset($input['email'])) {
            $user_arr = ['name' => $input['name'], 'email' => $input['email'], 'password' => $this->merchant->generateRandomString(7), 'creator_id' => $creator_id];
            session_set('user_role', 'user_merchant');
            $user = User::create($user_arr);
            $user->assignRole('merchant');
            Merchant::find($merchant_id)->update(['user_id' => $user->id]);
        } else {
            $email = User::where('email', $input['email'])->first();
            Merchant::find($merchant_id)->update(['user_id' => $email->id]);
        }
        if (!$merchant_id)  throw new Exception("Merchant not created", 1);
        
         if ($account_no != 0 && Str::length($account_no) > 3 && $routing_no != null && $bank_name != null && $account_holder_name != null) {
                $bank_params = ['account_number' => $account_no, 'routing_number' => $routing_no, 'bank_name' => $bank_name, 'account_holder_name' => $account_holder_name, 'merchant_id' => $merchant_id, 'default_credit' => 1, 'default_debit' => 1, 'type' => 'debit,credit'];
                $BankModel = new MerchantBankAccount;
                $create_bank_accnt = $BankModel->selfCreate($bank_params);
            }
            $bank_account_count = MerchantBankAccount::where('merchant_id', $merchant_id)->where('default_debit', 1)->count();
            if ($bank_account_count) {
                $ach_pull = isset($input['ach_pull']) ? ($input['ach_pull']) : null;
                if ($ach_pull != null) {
                    $ach_pull_update = $merchant->update(['ach_pull' => $ach_pull]);
                }
            }
            $emails = Settings::value('email');
            $email_id_arr = explode(',', $emails);
            $message['content'] = 'A new merchant, <b> <a href='.url('admin/merchants/view/'.$merchant_id).'>'.$input['name'].'</a> </b> has been created in the portal.';
            $message['title'] = 'Merchant Created';
            $message['to_mail'] = $email_id_arr;
            $message['from_mail'] = 'api@vgusa.com';
            $message['status'] = 'merchant_api';
            $message['template_type'] = 'merchant_create';
            $message['merchant_name'] = $input['name'];
            $message['merchant_id'] = $merchant_id;
            $message['subject'] = 'New merchant added from CRM.';
            $message['unqID'] = unqID();
            try {
                $email_template = Template::where([
                    ['temp_code', '=', 'CRMMC'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $email_id_arr);
                            $bcc_mails[] = $role_mails;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc'] = [];
                    $message['to_mail'] = $admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                }
            } catch (\Exception $e) {
                 return response()->json(['status' => 0, 'error' => $e->getMessage(), 'data' => '']);
            }

            $result['status']='success';
            $result['msg']='Merchant created successfully.';
            $result['data']=['merchant_id'=>$merchant_id];

           

        }  catch (\Exception $e) {

        	$result['status']=0;
            $result['error']=$e->getMessage();
           
         }

          return $result;

  } 

  public function merchantUpdate($request)
  { 
    $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
  	try
  	  {
  	    $input = $request->all();
        if (! isset($input['merchant_id']) || empty($input['merchant_id'])) {

        	throw new Exception("The merchant id field is required", 1);
    
         }
           
        $cell_phone='';
        
        if(isset($input['cell_phone']))
        {
             if(!preg_match("/^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/", $input['cell_phone'],$matches)) {
                 throw new Exception("Please Enter valid number.Ex:(417) 555-1234", 1);
           }
           preg_match("/^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/", $input['cell_phone'],$matches);
           $cell_phone = '('.$matches[1].')' . ' ' .$matches[2] . '-' . $matches[3];
            
        }

        $query = Merchant::where('id', $input['merchant_id']);
        if ($query->count() <=0) throw new Exception("The merchant not found", 1);

         $merchant = $query->first()->toArray();
                if (isset($input['email'])) {
                    if (! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("The email field is invalid email address", 1);
                    }
                }
                if (isset($input['industry'])) {
                    $ignore_arr = ['_', '-', '.', ',', '/', ',', ' '];
                    $industry = str_replace($ignore_arr, ' ', $input['industry']);
                    $industries = [];
                    $industries = DB::table('industries')->select('id')
                    ->where('name','LIKE','%'. $input['industry'] .'%')->first();


                }
               
                if (isset($input['state'])) {
                    $states = DB::table('us_states')->select('id')->where('state_abbr', '=', $input['state'])->first();
                }

                if (isset($input['owner_state'])) {
                    $owner_state = DB::table('us_states')->select('id')->where('state_abbr', '=', $input['owner_state'])->first();
                }

                if (isset($input['partner_state'])) {
                    $partner_state = DB::table('us_states')->select('id')->where('state_abbr', '=', $input['partner_state'])->first();
                }

                if (isset($input['source'])) {
                    $merchant_source = DB::table('merchant_source')->select('id')->where('name', '=', $input['source'])->first();
                }

                $max_fund_per = ($merchant['funded']) ? round($merchant['max_participant_fund'] / $merchant['funded'] * 100, 2) : 0;
                if (isset($input['funded']) && isset($input['factor_rate'])) {
                    $rtr = ($input['funded'] * $input['factor_rate']);
                    $max_fund_1 = $input['funded'] * $max_fund_per / 100;
                } elseif (isset($input['funded'])) {
                    $rtr = $input['funded'] * $merchant['factor_rate'];
                    $max_fund_1 = $input['funded'] * $max_fund_per / 100;
                } else {
                    $rtr = $merchant['funded'] * $merchant['factor_rate'];
                    $max_fund_1 = $merchant['funded'] * $max_fund_per / 100;
                }
                $company_amount = CompanyAmount::where('merchant_id', $request->merchant_id)->pluck('max_participant', 'company_id')->toArray();
                $company = [];
                if (isset($input['funded'])) {
                    foreach ($company_amount as $key => $value) {
                        $company[$key]['merchant_id'] = $input['merchant_id'];
                        $company[$key]['company_id'] = $key;
                        $company[$key]['comapmy_per'] = $value / $merchant['max_participant_fund'] * 100;
                        if ($company[$key]['comapmy_per']) {
                            $company[$key]['max_participant'] = $max_fund_1 * $company[$key]['comapmy_per'] / 100;
                        } else {
                            $company[$key]['max_participant'] = 0;
                        }
                        CompanyAmount::updateOrCreate(['company_id' => $company[$key]['company_id'], 'merchant_id' => $input['merchant_id']], ['max_participant' => $company[$key]['max_participant']]);
                    }
                }
                if (isset($input['date_funded'])) {
                    $date_funded = Carbon::createFromFormat('Y-m-d', $input['date_funded'])->format('Y-m-d');
                }
                
                $values = [
                    'name' => isset($input['name']) ? $input['name'] : $merchant['name'],
                    'first_name' => isset($input['first_name']) ? ($input['first_name']) : $merchant['first_name'],
                    'last_name' => isset($input['last_name']) ? ($input['last_name']) : $merchant['last_name'],
                    'business_address'=>isset($input['business_address'])?$input['business_address']:$merchant['business_address'],
                     'funded' => isset($input['funded']) ? $input['funded'] : $merchant['funded'],
                     'max_participant_fund' => $max_fund_1,
                     'rtr' => $rtr,
                     'credit_score' => ! empty($input['credit_score']) ? $input['credit_score'] : $merchant['credit_score'],
                      'factor_rate' => isset($input['factor_rate']) ? $input['factor_rate'] : $merchant['factor_rate'],
                       'date_funded' => isset($input['date_funded']) ? $date_funded : $merchant['date_funded'],
                        'commission' => ! empty($input['commission']) ? $input['commission'] : $merchant['commission'],
                        'advance_type' => ! empty($input['advance_type']) ? $input['advance_type'] : $merchant['advance_type'],
                         'industry_id' => isset($industries) ? $industries->id : $merchant['industry_id'],
                          'source_id' => ! empty($merchant_source->id) ? $merchant_source->id : $merchant['source_id'],
                          'state_id' => ! empty($states->id) ? $states->id : $merchant['state_id'],
                          'sub_status_id' => $merchant['sub_status_id'],
                           'origination_fee' => isset($input['origination_fee']) ? ($input['origination_fee']) : $merchant['origination_fee'],
                            'email' => isset($input['email']) ? $input['email'] : $merchant['email'],

                              'up_sell_commission' => isset($input['up_sell_commission']) ? ($input['up_sell_commission']) : $merchant['up_sell_commission'],
                               'phone' => isset($input['phone']) ? ($input['phone']) : $merchant['phone'],
                               'cell_phone' => isset($input['cell_phone']) ? ($cell_phone) : $merchant['cell_phone'],
                               'experian_intelliscore' => isset($input['experian_intelliscore']) ? ($input['experian_intelliscore']) : $merchant['experian_intelliscore'],
                                'experian_financial_score' => isset($input['experian_financial_score']) ? $input['experian_financial_score'] : $merchant['experian_financial_score'],
                                 'notification_email' => isset($input['notification_email']) ? ($input['notification_email']) : $merchant['notification_email'],
                                  'zip_code' => isset($input['zip']) ? ($input['zip']) : $merchant['zip_code'],
                                  'city' => isset($input['city']) ? ($input['city']) : $merchant['city'],
                                

                                   ];
                $merchant = Merchant::find($input['merchant_id'])
                                      ->update($values);
                $merchant = Merchant::find($input['merchant_id']);

                if ($merchant) {
                    $crm_merchant = MerchantDetails::where('merchant_id', $input['merchant_id']);
                    if ($crm_merchant->count() > 0) {
                        $crm_array = $crm_merchant->first()->toArray();
                        $crmValues = [

             'annual_revenue' => isset($input['annual_revenue']) ? ($input['annual_revenue']) : $crm_array['annual_revenue'],
             'iso_name' => isset($input['iso_name']) ? ($input['iso_name']) : $crm_array['iso_name'],
             'deal_type' => isset($input['deal_type']) ? ($input['deal_type']) : $crm_array['deal_type'],
             'position' => isset($input['position']) ? ($input['position']) : $crm_array['position'],
              'monthly_revenue' => isset($input['monthly_revenue']) ? ($input['monthly_revenue']) : $crm_array['monthly_revenue'],
             'withhold_percentage' => isset($input['withhold_percentage']) ? ($input['withhold_percentage']) : $crm_array['withhold_percentage'],
             'partner_credit_score' => isset($input['partner_credit_score']) ? ($input['partner_credit_score']) : $crm_array['partner_credit_score'],
              'owner_credit_score' => isset($input['owner_credit_score']) ? ($input['owner_credit_score']) : $crm_array['owner_credit_score'],
              'agent_name' => isset($input['agent_name']) ? ($input['agent_name']) : $crm_array['agent_name'],
              'date_business_started' => isset($input['date_business_started']) ? ($input['date_business_started']) : $crm_array['date_business_started'],
              'under_writer' => isset($input['under_writer']) ? ($input['under_writer']) : $crm_array['under_writer'],
              'entity_type' => isset($input['entity_type']) ? ($input['entity_type']) : $crm_array['entity_type'],
              'exact_legal_company_name'=>isset($input['exact_legal_company_name']) ? ($input['exact_legal_company_name']) : $crm_array['exact_legal_company_name'],
              'physical_address'=> isset($input['physical_address']) ? ($input['physical_address']) : $crm_array['physical_address'],
              'physical_address2'=> isset($input['physical_address2']) ? ($input['physical_address2']) : $crm_array['physical_address2'],
              'work_phone'=>isset($input['work_phone']) ? ($input['work_phone']) : $crm_array['work_phone'],
              'fax'=>isset($input['fax']) ? ($input['fax']) : $crm_array['fax'],
              'federal_tax_id'=>isset($input['federal_tax_id']) ? ($input['federal_tax_id']) : $crm_array['federal_tax_id'],
              'ownership_length'=>isset($input['ownership_length']) ? ($input['ownership_length']) : $crm_array['ownership_length'],
              'website'=>isset($input['website']) ? ($input['website']) : $crm_array['website'],
              'use_of_proceeds'=>isset($input['use_of_proceeds']) ? ($input['use_of_proceeds']) : $crm_array['use_of_proceeds'],
              'requested_amount'=>isset($input['requested_amount']) ? ($input['requested_amount']) : $crm_array['requested_amount'],
              'lead_source'=>isset($input['lead_source']) ? ($input['lead_source']) : $crm_array['lead_source'],
              'campaign'=>isset($input['campaign']) ? ($input['campaign']) : $crm_array['campaign'],
              'owner_first_name'=>isset($input['owner_first_name']) ? ($input['owner_first_name']) : $crm_array['owner_first_name'],
              'owner_last_name'=>isset($input['owner_last_name']) ? ($input['owner_last_name']) : $crm_array['owner_last_name'],
              'ownership_percentage'=>isset($input['ownership_percentage']) ? ($input['ownership_percentage']) : $crm_array['ownership_percentage'],
              'owner_email'=>isset($input['owner_email']) ? ($input['owner_email']) : $crm_array['owner_email'],
              'owner_address2'=>isset($input['owner_address2']) ? ($input['owner_address2']) : $crm_array['owner_address2'],
              'owner_home'=>isset($input['owner_home']) ? ($input['owner_home']) : $crm_array['owner_home'],
              'home_address'=>isset($input['home_address']) ? ($input['home_address']) : $crm_array['home_address'],
              'owner_city'=>isset($input['owner_city']) ? ($input['owner_city']) : $crm_array['owner_city'],
              'owner_state_id'=>isset($owner_state) ? ($owner_state->id) : $crm_array['owner_state_id'],
              'owner_zip'=>isset($input['owner_zip']) ? ($input['owner_zip']) : $crm_array['owner_zip'],
              'owner_cell'=>isset($input['owner_cell']) ? ($input['owner_cell']) : $crm_array['owner_cell'],
              'owner_cell2'=>isset($input['owner_cell2']) ? ($input['owner_cell2']) : $crm_array['owner_cell2'],
              'ssn'=>isset($input['ssn']) ? ($input['ssn']) : $crm_array['ssn'],
              'dob'=>isset($input['dob']) ? ($input['dob']) : $crm_array['dob'],
              'partner_first_name'=>isset($input['partner_first_name']) ? ($input['partner_first_name']) : $crm_array['partner_first_name'],
              'partner_last_name'=>isset($input['partner_last_name']) ? ($input['partner_last_name']) : $crm_array['partner_last_name'],
              'partner_email'=>isset($input['partner_email']) ? ($input['partner_email']) : $crm_array['partner_email'],
              'partner_ownership_percentage'=>isset($input['partner_ownership_percentage']) ? ($input['partner_ownership_percentage']) : $crm_array['partner_ownership_percentage'],
              'partner_home_address'=>isset($input['partner_home_address']) ? ($input['partner_home_address']) : $crm_array['partner_home_address'],
              'partner_city'=>isset($input['partner_city']) ? ($input['partner_city']) : $crm_array['partner_city'],
              'partner_cell2'=>isset($input['partner_cell2']) ? ($input['partner_cell2']) : $crm_array['partner_cell2'],
              'partner_address2'=>isset($input['partner_address2']) ? ($input['partner_address2']) : $crm_array['partner_address2'],
               'partner_home_hash'=>isset($input['partner_home_hash']) ? ($input['partner_home_hash']) : $crm_array['partner_home_hash'],
              'partner_state_id'=>isset($partner_state) ? ($partner_state->id) : $crm_array['partner_state_id'],
              'partner_zip'=>isset($input['partner_zip']) ? ($input['partner_zip']) : $crm_array['partner_zip'],
              'partner_ssn'=>isset($input['partner_ssn']) ? ($input['partner_ssn']) : $crm_array['partner_ssn'],
              'partner_dob'=>isset($input['partner_dob']) ? ($input['partner_dob']) : $crm_array['partner_dob'],
              'partner_cell_hash'=>isset($input['partner_cell_hash']) ? ($input['partner_cell_hash']) : $crm_array['partner_cell_hash'],
              'product_sold'=>isset($input['product_sold']) ? ($input['product_sold']) : $crm_array['product_sold'],
               'disposition'=>isset($input['disposition']) ? ($input['disposition']) : $crm_array['disposition'],
              'marketing_notification'=>isset($input['marketing_notification']) ? ($input['marketing_notification']) : $crm_array['marketing_notification'],
              'buy_rate'=>isset($input['buy_rate']) ? ($input['buy_rate']) : $crm_array['buy_rate'],
              'created_date'=>isset($input['created_date']) ? ($input['created_date']) : $crm_array['created_date'],
              'payback_amount'=>isset($input['payback_amount']) ? ($input['payback_amount']) : $crm_array['payback_amount'],
              'lender_email'=>isset($input['lender_email']) ? ($input['lender_email']) : $crm_array['lender_email'],
              'no_of_deposit'=>isset($input['no_of_deposit']) ? ($input['no_of_deposit']) : $crm_array['no_of_deposit'],
              'negative_days'=>isset($input['negative_days']) ? ($input['negative_days']) : $crm_array['negative_days'],
              'nsf'=>isset($input['nsf']) ? ($input['nsf']) : $crm_array['nsf'],
              'fico_score_primary'=>isset($input['fico_score_primary']) ? ($input['fico_score_primary']) : $crm_array['fico_score_primary'],
              'fico_score_secondary'=>isset($input['fico_score_secondary']) ? ($input['fico_score_secondary']) : $crm_array['fico_score_secondary'],
              'broker_commission'=>isset($input['broker_commission']) ? ($input['broker_commission']) : $crm_array['broker_commission'],
              'crm_id'=>isset($input['crm_id']) ? ($input['crm_id']) : $crm_array['crm_id'],
              'terms_in_days'=>isset($input['terms_in_days']) ? ($input['terms_in_days']) : $crm_array['terms_in_days'],

            ];

                        MerchantDetails::where('merchant_id', $input['merchant_id'])->update($crmValues);
                    }
                }

                $bank_name = isset($input['bank']) ? $input['bank'] : null;
                $account_holder_name = isset($input['account_name']) ? $input['account_name'] : null;
                $account_no = isset($input['account_no']) ? $input['account_no'] : 0;
                $routing_no = isset($input['routing_no']) ? $input['routing_no'] : null;
                $email_a = isset($input['email']) ? $input['email'] : '';
                $email = User::where('email', $email_a);
                if ($email->count() == 0 && isset($input['email'])) {
                    $crm = isset($_REQUEST['PHP_AUTH_USER']) ? $_REQUEST['PHP_AUTH_USER'] : '';
                    $crm_user = DB::table('user_has_roles')->where('role_id', User::CRM_ROLE)->select('model_id')->first();
                    $creator_id = null;
                    if ($crm && isset($crm_user)) {
                        $creator_id = $crm_user->model_id;
                    }
                    $user_arr = ['name' => isset($input['name']) ? $input['name'] : $merchant['name'], 'email' => $input['email'], 'password' => $this->merchant->generateRandomString(7), 'creator_id' => $creator_id];
                    session_set('user_role', 'user_merchant');
                    $user = User::create($user_arr);
                    $user->assignRole('merchant');
                    Merchant::find($input['merchant_id'])->update(['user_id' => $user->id]);
                } else {
                    if ($email->count()) {
                        $email_1=$email->first();
                        Merchant::find($input['merchant_id'])->update(['user_id' => $email_1->id]);
                    }
                }
               
                    if ($account_no != 0 && Str::length($account_no) > 3 && $routing_no != null && $bank_name != null && $account_holder_name != null) {
                        if (isset($input['bank_type']) && (Str::contains($input['bank_type'], 'debit') || Str::contains($input['bank_type'], 'credit'))) {
                        } else {
                            $input['bank_type'] = 'debit,credit';
                        }
                        $bank_params = ['account_number' => $account_no, 'routing_number' => $routing_no, 'bank_name' => $bank_name, 'account_holder_name' => $account_holder_name, 'merchant_id' => $input['merchant_id'], 'type' => isset($input['bank_type']) ? $input['bank_type'] : '', 'default_credit' => isset($input['default_credit_bank']) ? $input['default_credit_bank'] : null, 'default_debit' => isset($input['default_debit_bank']) ? $input['default_debit_bank'] : null];
                        $BankModel = new MerchantBankAccount;
                        $create_bank_accnt = $BankModel->selfCreate($bank_params);
                    }
                    $bank_account_count = MerchantBankAccount::where('merchant_id', $input['merchant_id'])->where('default_debit', 1)->count();
                    if ($bank_account_count) {
                        $ach_pull = isset($input['ach_pull']) ? ($input['ach_pull']) : null;
                        if ($ach_pull != null) {
                            $ach_pull_update = $merchant->update(['ach_pull' => $ach_pull]);
                        }
                    }
                    $merchant_name = isset($input['name']) ? $input['name'] : $merchant['name'];
                    $emails = Settings::value('email');
                    $email_id_arr = explode(',', $emails);
                    $message['content'] = 'Merchant <b> <a href='.url('admin/merchants/view/'.$input['merchant_id']).'>'.$merchant_name.'</a> </b> was updated in the portal.';
                    $message['title'] = 'Merchant Updated';
                    $message['to_mail'] = $email_id_arr;
                    $message['from_mail'] = 'api@vgusa.com';
                    $message['status'] = 'merchant_api';
                    $message['template_type'] = 'merchant_update';
                    $message['merchant_name'] = $merchant_name;
                    $message['merchant_id'] = $input['merchant_id'];
                    $message['subject'] = 'Merchant updated from CRM.';
                    $message['unqID'] = unqID();
                    try {
                        $email_template = Template::where([
                            ['temp_code', '=', 'CRMMU'], ['enable', '=', 1],
                        ])->first();
                        if ($email_template) {
                            if ($email_template->assignees) {
                                $template_assignee = explode(',', $email_template->assignees);
                                $bcc_mails = [];
                                foreach ($template_assignee as $assignee) {
                                    $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                    $role_mails = array_diff($role_mails, $email_id_arr);
                                    $bcc_mails[] = $role_mails;
                                }
                                $message['bcc'] = Arr::flatten($bcc_mails);
                            }
                            $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                            dispatch($emailJob);
                            $message['bcc'] = [];
                            $message['to_mail'] = $admin_email;
                            $emailJob = (new CommonJobs($message));
                            dispatch($emailJob);
                        }
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }

                    $result['status']='success';
                    $result['msg']='Merchant updated successfully.';
                    $result['data']=['merchant_id'=>$input['merchant_id']];



    } catch (\Exception $e) {

    	 $result['status']=0;
    	 $result['error']=$e->getMessage();


    }

    return $result;

     
  }

  public function investorCreate($request)
  {
    $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
  	 try
  	 {
        $validator = \Validator::make($request->all(), ['email' => 'required|email|unique:users', 'syndicate_company_name' => 'required']);
        if ($validator->fails()) {
        	throw new Exception($validator->messages(), 1);
        }
      if (isset($request->company_name) && ! empty($request->company_name)) {
            $company = User::select('id','name')->where(DB::raw("replace(replace(replace(replace(replace(replace(name,' ',''),'_','') , '-','') ,'.',''), ',',''),'/','')"), 'like', '%'.$request->company_name.'%')
            ->whereNull('company')
            ->first();
            $company_id = isset($company->id) ? $company->id : 0;
            if($company_id)
            {
            $check=User::where('name','like', '%Syndicates%')->whereNull('company')->where('id',$company->id);
            if ($check->count()<=0) {
                throw new Exception("Company not available for IP", 1);
            }

            }else
            {
                throw new Exception("Company not found for IP", 1);
            }
           
        }else
        {
            $check=User::select('id','name')->where('name','like', '%Syndicates%');
            if($check->count()>0)
             {
                $ex_company=$check->first();
             }
        }

        $crm = isset($_REQUEST['PHP_AUTH_USER']) ? $_REQUEST['PHP_AUTH_USER'] : '';
        $crm_user = DB::table('user_has_roles')->where('role_id', User::CRM_ROLE)->select('model_id')->first();
        $creator_id = null;
        if ($crm && isset($crm_user)) {
            $creator_id = $crm_user->model_id;
        }
        $request->merge(['name' => $request->syndicate_company_name,'contact_person' => $request->contact_name, 'creator_id' => $creator_id, 'investor_type' => isset($request->investor_type) ? $request->investor_type : 5, 'global_syndication' => isset($request->syndication_fee) ? $request->syndication_fee : 0, 'management_fee' => isset($request->management_fee) ? $request->management_fee : 0, 's_prepaid_status' => isset($request->prepaid_status) ? $request->prepaid_status : 2, 'interest_rate' => isset($request->interest_rate) ? $request->interest_rate : 0.5, 'notification_email' => isset($request->notification_email) ? $request->notification_email : 'testmail@vgusa.com', 'notification_recurence' => isset($request->notification_recurence) ? $request->notification_recurence : 1, 'company' => isset($company) ? $company->id : (!empty($ex_company)?$ex_company->id:0), 'active_status' => isset($request->active_status) ? $request->active_status : 1, 'file_type' => isset($request->file_type) ? $request->file_type : 1, 'email_notification' => 1, 'auto_generation' => 0, 'source_from' => 'crm', 'agreement_date' => isset($request->agreement_date) ? $request->agreement_date : NULL,'login_board'=>'new']);

        $user = User::create($request->only(['name', 'management_fee', 'global_syndication', 'interest_rate', 'email', 'password', 'investor_type', 'creator_id', 'notification_email', 'notification_recurence', 'groupby_recurence', 'active_status', 'company', 's_prepaid_status', 'file_type', 'auto_generation', 'cell_phone', 'source_from', 'agreement_date', 'contact_person','login_board']));
        $userDetails = UserDetails::create(['user_id' => $user->id]);
        $user->assignRole('investor');
        if ($userDetails) {
            if ($request->hasFile('file')) {
                $fileName = "documents/$user->id/doc_".time().'.'.$request->file->getClientOriginalExtension();
                $upload = Storage::disk('s3')->put($fileName, file_get_contents($request->file), config('filesystems.disks.s3.privacy'));
                if ($upload) {
                    $data = ['document_type_id' => 1, 'investor_id' => $user->id, 'title' => $request->file->getClientOriginalName(), 'file_name' => $fileName, 'status' => 1];
                    $result = InvestorDocuments::create($data);
                }
            }
            $emails = Settings::value('email');
            $email_id_arr = explode(',', $emails);
            $message['content'] = 'A new investor, <b> <a href='.url('admin/investors/portfolio/'.$user->id).'>'.$user->name.'</a> </b> has been created in the portal.';
            $message['title'] = 'investor created';
            $message['to_mail'] = $email_id_arr;
            $message['from_mail'] = 'api@vgusa.com';
            $message['status'] = 'investor_api';
            $message['template_type'] = 'investor_create';
            $message['investor_name'] = $user->name;
            $message['investor_id'] = $user->id;
            $message['subject'] = 'investor created from CRM.';
            $message['unqID'] = unqID();
            try {
                $email_template = Template::where([
                    ['temp_code', '=', 'CRMIC'], ['enable', '=', 1],
                ])->first();
                if ($email_template) {
                    if ($email_template->assignees) {
                        $template_assignee = explode(',', $email_template->assignees);
                        $bcc_mails = [];
                        foreach ($template_assignee as $assignee) {
                            $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                            $role_mails = array_diff($role_mails, $email_id_arr);
                            $bcc_mails[] = $role_mails;
                        }
                        $message['bcc'] = Arr::flatten($bcc_mails);
                    }
                    $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                    dispatch($emailJob);
                    $message['bcc'] = [];
                    $message['to_mail'] = $admin_email;
                    $emailJob = (new CommonJobs($message));
                    dispatch($emailJob);
                    
                }
            } catch (\Exception $e) {
                 return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
            }

         $result['status']='success';
    	 $result['msg']='Investor created successfully.';
    	 $result['data']=['investor_id'=>$user->id,'data' =>$user];

    	}

       }catch (\Exception $e) {

    	 $result['status']=0;
    	 $result['error']=$e->getMessage();
        }

    return $result;

  }

  public function investorUpdate($request)
  {
  	   try
  	   {
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        $validator = \Validator::make($request->all(), ['email' => 'unique:users,email,'.$request->participant_id, 'position' => 'nullable', 'password' => 'sometimes']);
        if ($validator->fails()) {
        	throw new Exception($validator->messages(), 1);
        }
        if (! isset($request->participant_id) || empty($request->participant_id)) {
            throw new Exception("The participant id field is required", 1);
        }
        if (isset($request->company_name) && ! empty($request->company_name)) {
            
            $company = User::select('id')->where(DB::raw("replace(replace(replace(replace(replace(replace(name,' ',''),'_','') , '-','') ,'.',''), ',',''),'/','')"), 'like', '%'.$request->company_name.'%')
            ->whereNull('company')
            ->first();
            $company_id = isset($company->id) ? $company->id : 0;
            
            if($company_id)
            {
                $check=User::where('name','like', '%Syndicates%')->whereNull('company')->where('id',$company->id);
                if ($check->count()<=0) {
                    throw new Exception("Company not available for IP", 1);
                }
            }
            else
            {
                 throw new Exception("Company not found for IP", 1);
            }
        }

        $data = $validator->validated();
        $investor = User::where('id', $request->participant_id);
        if ($investor->count()<=0)  throw new Exception("The participant not found", 1);

                $user = $investor->first();
                $user->name = isset($request->syndicate_company_name) ? $request->syndicate_company_name : $user->name;
                $user->login_board = isset($request->login_board) ? $request->login_board : $user->login_board;
                $user->email = isset($request->email) ? $request->email : $user->email;
                $user->investor_type = isset($request->investor_type) ? $request->investor_type : $user->investor_type;
                $user->management_fee = isset($request->management_fee) ? $request->management_fee : $user->management_fee;
                $user->global_syndication = isset($request->syndication_fee) ? $request->syndication_fee : $user->global_syndication;
                $user->s_prepaid_status = isset($request->s_prepaid_status) ? $request->s_prepaid_status : $user->s_prepaid_status;
                $user->interest_rate = isset($request->interest_rate) ? $request->interest_rate : $user->interest_rate;
                $user->notification_recurence = isset($request->notification_recurence) ? $request->notification_recurence : $user->notification_recurence;
                $user->notification_email = isset($request->notification_email) ? $request->notification_email : $user->notification_email;
                $user->company = !empty($company_id) ? $company->id : $user->company;
                $user->contact_person = isset($request->contact_name) ? $request->contact_name : $user->contact_person;
                $user->source_from = 'crm';
                $user->agreement_date = isset($request->agreement_date) ? $request->agreement_date : $user->agreement_date;
                $user->update();
                $message['content'] = 'An investor,<b> <a href='.url('admin/investors/portfolio/'.$user->id).'>'.$user->name.'</a> </b> has been updated in the portal.';
                $message['title'] = 'investor updated';
                $message['from_mail'] = 'api@vgusa.com';
                $message['status'] = 'investor_api';
                $message['template_type'] = 'investor_update';
                $message['investor_name'] = $user->name;
                $message['investor_id'] = $user->id;
                $message['subject'] = 'investor updated from CRM.';
                $message['unqID'] = unqID();
                try {
                    $email_template = Template::where([
                        ['temp_code', '=', 'CRMIU'], ['enable', '=', 1],
                    ])->first();
                    if ($email_template) {
                        $message['to_mail'] = $admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                        if ($email_template->assignees) {
                            $template_assignee = explode(',', $email_template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                $bcc_mails[] = $role_mails;
                            }
                            $message['bcc'] = Arr::flatten($bcc_mails);
                        }
                        $emails = Settings::value('email');
                        $email_id_arr = explode(',', $emails);
                        $message['to_mail'] = $email_id_arr;
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                    } 
                } catch (\Exception $e) {
                     return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
                }


          $result['status']='success';
          $result['msg']='Investor updated successfully.';
          $result['data']=['investor_id' => $user->id, 'data' => $user];
                
  	   }catch (\Exception $e) {

    	 $result['status']=0;
    	 $result['error']=$e->getMessage();
        }

    return $result;

  }

  public function merchantPaymentDetails($request)
  {
  	 try
  	 {
        $input = $request->all();
        if (empty($input['merchant_id']) || !isset($input['merchant_id'])) throw new Exception("merchant id required", 1);
        $merchant_id = $input['merchant_id'];
        $total_payment = 0;
            $payments = ParticipentPayment::select(['participent_payments.payment_date', 'participent_payments.payment', 'participent_payments.id as payment_id', 'participent_payments.reason as notes', DB::raw('
IF((rcode.id>0), (rcode.code), 0)
 as rcode'), DB::raw("IF(participent_payments.mode_of_payment=1,'ach','unknown')\n as mode_of_payment"), DB::raw('round(sum(payment_investors.actual_participant_share-payment_investors.mgmnt_fee),2 ) as participant_share')])->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode')->leftjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->leftJoin('merchant_user', function ($join) {
                $join->on('payment_investors.user_id', 'merchant_user.user_id');
                $join->on('payment_investors.merchant_id', 'merchant_user.merchant_id');
            })->leftJoin('merchants', 'merchants.id', 'participent_payments.merchant_id')->groupBy('payment_investors.participent_payment_id');
            $payments = $payments->where('participent_payments.merchant_id', $merchant_id)->get();
         
           // $participant_share_total = array_sum(array_column($payments->toArray(), 'participant_share'));
            $merchants = Merchant::select('funded', 'factor_rate', 'sub_status_id', 'date_funded', 'commission', 'rtr', 'complete_percentage', 'sub_statuses.name as status', 'pmnts', 'advance_type', 'payment_amount')->leftJoin('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->where('merchants.id', $merchant_id);
            $p_share=0;
            if ($merchants->count() <=0)  throw new Exception("Merchant not found", 1);
                $merchants = $merchants->first()->toArray();
                $payments_investors = PaymentInvestors::select(DB::raw('sum(payment_investors.participant_share) as participant_share'))->where('payment_investors.merchant_id', $merchant_id)->groupBy('merchant_id');
                if($payments_investors->count()>0)
                {
                    $p_investors=$payments_investors->first()->toArray();
                    $p_share=$p_investors['participant_share'];
                }
                $merchant_arr = MerchantUser::select(DB::raw('sum(merchant_user.invest_rtr) as invest_rtr,sum(paid_participant_ishare) as paid_participant_ishare,( invest_rtr ) * merchant_user.mgmnt_fee/100 as fee '))->where('merchant_id', $merchant_id)->groupBy('merchant_id');
                $invest_rtr=$paid_participant_ishare=0;
                if($merchant_arr->count()>0)
                {
                    $mer_arr=$merchant_arr->first();
                    $invest_rtr=$mer_arr->invest_rtr;
                    $paid_participant_ishare=$mer_arr->paid_participant_ishare;

                }

                $balance = 0;
                $substatus = [11, 18, 19, 20];
                $balance_our_portion = ($invest_rtr) - $paid_participant_ishare;
                if (in_array($merchants['sub_status_id'], $substatus)) {
                    $balance = 0;
                } else {
                    $balance = $balance_our_portion;
                }
                $total_payment = $total_payment + array_sum(array_column($payments->toArray(), 'payment'));
                $payment_unique_date = ParticipentPayment::where('participent_payments.merchant_id', $merchant_id)->groupBy('payment_date')->get()->toArray();
                $payment_left = $merchants['pmnts'] - count($payment_unique_date);
                $total_rtr = $invest_rtr;
                $bal_rtr = $total_rtr - $p_share ;
                if ($total_rtr > 0) {
                    $actual_payment_left = ($merchants['rtr']) ? $bal_rtr / (($total_rtr / $merchants['rtr']) * ($merchants['rtr'] / $merchants['pmnts'])) : 0;
                } else {
                    $actual_payment_left = 0;
                }
                $fractional_part = fmod($actual_payment_left, 1);
                $act_paymnt_left = floor($actual_payment_left);
                if ($fractional_part > .09) {
                    $act_paymnt_left = $act_paymnt_left + 1;
                }
                $actual_payment_left = ($act_paymnt_left > 0) ? $act_paymnt_left : 0;
                $merchant_details = [];
                $merchant_details = ['funded_amount' => $merchants['funded'], 'factor_rate' => $merchants['factor_rate'], 'rtr' => $merchants['rtr'], 'date_funded' => date('Y-m-d', strtotime($merchants['date_funded'])), 'commission' => $merchants['commission'], 'complete_percentage' => $merchants['complete_percentage'], 'substatus' => $merchants['status'], 'pmnts' => $merchants['pmnts'], 'advance_type' => $merchants['advance_type'], 'ctd' => round($total_payment), 'payment_left' => $actual_payment_left, 'balance' => ($balance > 0) ? round($balance, 2) : 0];
                $details = [];
                $details['payment_history'] = $payments->toArray();
                $details['merchant_details'] = $merchant_details;

                 if (empty($details['payment_history'])) throw new Exception("No payments found", 1);
                
                $result['status']='success';
                $result['result']=$details;

                // if (! empty($details['payment_history'])) {
                //     return response()->json(['status' => 'success', 'result' => $details]);
                // } else {
                //     return response()->json(['status' => 0, 'msg' => 'no payments found']);
                // }

  	 }catch (\Exception $e) {

    	 $result['status']=0;
    	 $result['msg']=$e->getMessage();
        }

        return $result;


  }

  public function merchantDetails($request)
  {
  	  try
  	  {
  	        $input = $request->all();
             if (empty($input['merchant']) || !isset($input['merchant'])) throw new Exception("Merchant Required", 1);
             $companyName = strtoupper($input['merchant']);
            
                $ignore_arr = ['INC', 'COMPANY', 'CO', 'LLC', 'LTD', ' ', '-', '.', ','];
                $companyName = str_replace($ignore_arr, [''], $companyName);
                $results = Merchant::select('merchants.id', 'merchants.created_at', 'merchants.name', 'sub_statuses.name as substatus')->join('merchants_details','merchants_details.merchant_id','merchants.id')
                ->join('sub_statuses', 'sub_statuses.id', 'merchants.sub_status_id')->where(DB::raw("replace(replace(replace(replace(replace(replace(replace(replace(replace(merchants.name,'INC',''),'COMPANY',''),'CO',''),'LLC',''),'LTD',''),' ',''),'-','') ,'.',''),',','')"), 'like', '%'.$companyName.'%');
                if (is_numeric($companyName)) {
                    $results = $results->orWhere('merchants.id', $companyName);
                } else {
                    $results = $results->where('merchants_details.crm_id', 0);
                }
                $results = $results->get()->toArray();
                $data = [];
                if ($results) {
                    foreach ($results as $key => $value) {
                        $data[$key]['id'] = $value['id'];
                        $data[$key]['created_at'] = $value['created_at'];
                        $data[$key]['name'] = $value['name'];
                        $data[$key]['substatus'] = str_replace(' ', '_', strtolower($value['substatus']));
                    }
                }
                if (!$results) throw new Exception("result not found", 1);

                $result['status']='success';
                $result['result']=$data;
	
                 //   return response()->json(['status' => 'success', 'result' => $data]);
                // } else {
                // 	$result['status']=0;
                // 	$result['result']='result not found';
                //    // return response()->json(['status' => 0, 'result' => 'result not found']);
                // }
            
      }catch (\Exception $e) {

    	 $result['status']=0;
    	 $result['msg']=$e->getMessage();
       }
      return $result;

  }

 public function updateCRMID($request)
  {
     try
     {
        $input = $request->all();
         $merchant_id = isset($input['merchant_id']) ? $input['merchant_id'] : '';
            $crm_id = isset($input['crm_id']) ? $input['crm_id'] : '';
            if (!$merchant_id) throw new Exception("No merchant_id here", 1);
            if (!$crm_id) throw new Exception("No crm_id here", 1);
            
            	//{
                $merchant = MerchantDetails::where('merchant_id', $merchant_id);
                if ($merchant->count() <= 0)  throw new Exception("No Merchant found", 1);
                
                	//{
                    $update = $merchant->update(['crm_id' => $crm_id]);
                    if (!$update)  throw new Exception("merchant crm_id not updated successfully", 1);
                    
                    	//{
                    	$result['status']='success';
                    	$result['msg']='Merchant crm_id updated successfully';
                       // return response()->json(['status' => 'success', 'msg' => '']);
                   // }
                // } else {
                //     return response()->json(['status' => 0, 'result' => 'no Merchant found']);
                // }
           // } 
            // else {
            //     return response()->json(['status' => 0, 'result' => 'No crm_id here ']);
            // }
          }
          catch (\Exception $e) {

         $result['status']=0;
         $result['msg']=$e->getMessage();
       }
      return $result;               

  }

  public function assignParticipants($request)
  {
      try
      {
            $merchant_id = $request->merchant_id;
            $participants = $request->participants;
            $merchant = Merchant::where('id', $merchant_id);
            if ($merchant->count() <= 0) {
                throw new Exception("Merchant not found", 1);

              //  return response()->json(['status' => 0, 'error' => 'Merchant not found', 'data' => '']);
            }
            $m_d = $merchant->first();
            if($m_d->sub_status_id==SubStatus::Cancelled)
            {
                 throw new Exception("This merchant is Cancelled for IP", 1);
               // return response()->json(['status' => 0, 'error' => 'This merchant is Cancelled for IP', 'data' => '']);
            }
            if (empty($participants)) {
                throw new Exception("The participants field is required", 1);
               // return response()->json(['status' => 0, 'error' => 'The participants field is required', 'data' => '']);
            }
            if (empty($merchant_id)) {
                 throw new Exception("The merchant_id field is required", 1);
               // return response()->json(['status' => 0, 'error' => 'The merchant_id field is required', 'data' => '']);
            }

               $data_r = [];
            $users_id = [];
            $companies_3 = DB::table('company_amount')->where('merchant_id', $merchant_id)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();
            $thrid_c = isset($companies_3[284]) ? $companies_3[284] : '11';
            if ($thrid_c == 11) {
                CompanyAmount::create(['merchant_id' => $merchant_id, 'company_id' => 284, 'max_participant' => 0]);
            }
            $total_amount = MerchantUser::where('merchant_id', $merchant_id)->whereIn('status', [1, 3])->sum('amount');
            $max_participant_fund = Merchant::where('id', $merchant_id)->value('max_participant_fund');
            $available_fund = $max_participant_fund - $total_amount;
            $created = [];
            $msg = [];
            $status = 0;
            $count = 0;
            if (! empty($participants)) {
                foreach ($participants as $key => $value) {
                    $companies_1 = DB::table('company_amount')->where('merchant_id', $merchant_id)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();
                    $participant_id = isset($value['participant_id']) ? $value['participant_id'] : 0;
                    $commission = isset($value['commission']) ? $value['commission'] : 0;
                    $up_sell_commission = isset($value['upsell_commission_per']) ? $value['upsell_commission_per'] : 0;
                    $syndication_fee = isset($value['syndication_fee']) ? $value['syndication_fee'] : 0;
                    $management_fee = isset($value['management_fee']) ? $value['management_fee'] : 0;
                    $underwritting_fee = isset($value['underwritting_fee']) ? $value['underwritting_fee'] : 0;
                    $amount = isset($value['amount']) ? $value['amount'] : 0;
                    $invetsor = User::where('id', $participant_id);
                    if ($invetsor->count() <= 0) {
                        $msg[$key]['status'] = 'error';
                        $status = $status + 1;
                        $msg[$key]['msg'] = $participant_id.' ,Participant not found';
                        $msg[$key]['participant_id'] = $participant_id;
                        continue;
                    }
                    $n_d = $invetsor->first();
                    $s_prepaid_status = $n_d->s_prepaid_status;
                    $company_name = User::where('id', $n_d->company)->value('name');
                    $liquidity = UserDetails::where('user_id', $participant_id)->value('liquidity');
                    $assigned_count = MerchantUser::where('user_id', $participant_id)->where('merchant_id', $merchant_id)->count();
                    if ($assigned_count > 0) {
                        $msg[$key]['status'] = 'error';
                        $status = $status + 1;
                        $msg[$key]['msg'] = $participant_id.' , Participant Already Assigned';
                        $msg[$key]['participant_id'] = $participant_id;
                        continue;
                    }
                    if ($n_d->company != 284) {
                        $msg[$key]['status'] = 'error';
                        $status = $status + 1;
                        $msg[$key]['msg'] = $participant_id.' , only participant can assign ';
                        $msg[$key]['participant_id'] = $participant_id;
                        continue;
                    }
                    if ($amount == 0) {
                        $msg[$key]['status'] = 'error';
                        $status = $status + 1;
                        $msg[$key]['msg'] = $participant_id.' , Participant Amount zero';
                        $msg[$key]['participant_id'] = $participant_id;
                        continue;
                    }
                    $companies = DB::table('company_amount')->where('merchant_id', $merchant_id)->where('company_id', $n_d->company)->pluck('max_participant', 'company_id')->toArray();
                    $max_fund_sum = array_sum($companies);
                    if ($max_fund_sum == 0 && $n_d->company != 284) {
                        $msg[$key]['status'] = 'error';
                        $status = $status + 1;
                        $msg[$key]['msg'] = $participant_id.',Participant Company '.$company_name.' , funded amount is not available for this merchant';
                        $msg[$key]['participant_id'] = $participant_id;
                        continue;
                    }
                    $company = isset($companies_3[$n_d->company]) ? $companies_3[$n_d->company] : 0;
                    $assign_amount = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->groupBy('users.company')->where('company', $n_d->company)->sum('amount');
                    if ($n_d->company != 284) {
                        if ($company < $assign_amount + $amount && $company > 0) {
                            $msg[$key]['status'] = 'error';
                            $status = $status + 1;
                            $msg[$key]['msg'] = $participant_id.'Participant , Above company participant amount,so assignment not possible';
                            $msg[$key]['participant_id'] = $participant_id;
                            continue;
                        }
                    }
                    if ($amount > $liquidity) {
                        $msg[$key]['status'] = 'error';
                        $status = $status + 1;
                        $msg[$key]['msg'] = $participant_id.',Participant , Cash in hand is only '.$liquidity.' it will changed to negative liquidity';
                        $msg[$key]['participant_id'] = $participant_id;
                        continue;
                    }
                    if ($max_participant_fund < $total_amount + $amount) {
                        $msg[$key]['status'] = 'error';
                        $status = $status + 1;
                        $msg[$key]['msg'] = $participant_id.',Participant ,Maximum Available Amount is  '.round($available_fund, 2);
                        $msg[$key]['participant_id'] = $participant_id;
                        continue;
                    }
                    if ($n_d->company == 284) {
                        $assign_amount = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)->where('company', '!=', 284)->groupBy('users.company')->pluck('users.company')->toArray();
                        if (count($assign_amount) > 1) {
                            $msg[$key]['status'] = 'error';
                            $status = $status + 1;
                            $msg[$key]['msg'] = $participant_id.',Participant ,Investor Assignment is not possible as another company has already been assigned to the merchant!';
                            $msg[$key]['participant_id'] = $participant_id;
                            continue;
                        }
                    }
                    if (isset($commission)) {
                        $data_r['commission_per'] = $commission;
                        $data_r['commission_amount'] = $commission / 100 * $amount;
                    }

                    if (isset($up_sell_commission)) {
                        $data_r['up_sell_commission_per'] = $up_sell_commission;
                        $data_r['up_sell_commission'] = $up_sell_commission / 100 * $amount;
                    }

                    if (isset($underwritting_fee)) {
                        $data_r['under_writing_fee_per'] = $underwritting_fee;
                        $data_r['under_writing_fee'] = $underwritting_fee / 100 * $amount;
                    }
                    if (isset($management_fee)) {
                        $data_r['mgmnt_fee'] = $management_fee;
                    }
                    if (isset($syndication_fee)) {
                        $data_r['syndication_fee_percentage'] = $syndication_fee;
                        $pre_paid = PayCalc::getsyndicationFee($syndication_fee, $amount);
                        $data_r['pre_paid'] = $pre_paid;
                    }
                    $data_r['s_prepaid_status'] = $s_prepaid_status;
                    $data_r['status'] = 1;
                    $rtr = ($amount * $m_d->factor_rate);
                    $data_r['invest_rtr'] = $rtr;
                    $data_r['merchant_id'] = $merchant_id;
                    $data_r['user_id'] = $participant_id;
                    $data_r['amount'] = $amount;
                    $users_id[$key] = $participant_id;
                    $msg[$key]['msg'] = $participant_id.', participant Assigned successfully';
                    $msg[$key]['participant_id'] = $participant_id;
                    $msg[$key]['data'] = MerchantUser::create($data_r);
                     unset($msg[$key]['data']['merchant']);
                     unset($msg[$key]['data']['Investor']);
                    if ($msg[$key]['data']) {
                        InvestorHelper::update_liquidity($participant_id, 'Assign Investor from CRM', $merchant_id);
                    }
                    $count = $count + 1;
                    $check_one = DB::table('company_amount')->where('merchant_id', $merchant_id)->where('company_id', 284)->pluck('max_participant', 'company_id')->toArray();
                    $check_fund = DB::table('company_amount')->where('merchant_id', $merchant_id)->where('company_id', $n_d->company)->pluck('max_participant', 'company_id')->toArray();
                    $company_amount_check_1 = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)->whereNotIn('company', [284])->groupBy('users.company')->pluck(DB::raw('sum(amount) as amount'), 'users.company')->toArray();
                    $except_companies = array_keys($company_amount_check_1);
                    if (count($except_companies) == 1) {
                        $qq = DB::table('company_amount')->where('merchant_id', $merchant_id)->whereIn('company_id', $except_companies)->orderByDesc('company_id')->pluck('max_participant')->toArray();
                        $per11 = $qq[0] / $max_participant_fund * 100;
                        $test_per = 100 - $per11;
                    }
                    array_push($except_companies, 284);
                    $companies_except = DB::table('company_amount')->where('merchant_id', $merchant_id)->whereNotIn('company_id', $except_companies)->orderByDesc('company_id')->pluck('max_participant', 'company_id')->toArray();
                    $test = [];
                    if ($companies_except) {
                        foreach ($companies_except as $key => $value) {
                            $test[$key] = $value / $max_participant_fund * 100;
                        }
                    }
                    $company_per_total = array_sum($test);
                    $company = [];
                    if (! empty($companies_1)) {
                        foreach ($companies_1 as $key1 => $value1) {
                            $company[$key1]['merchant_id'] = $merchant_id;
                            $company[$key1]['company_id'] = $key1;
                            if ($n_d->company == 284) {
                                $company_amount = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)->where('company', $n_d->company)->groupBy('users.company')->pluck(DB::raw('sum(amount) as amount'), 'users.company')->toArray();
                                if ($key1 == 284) {
                                    if ($value1 != 0) {
                                        if ($check_fund[$n_d->company] < $amount || $company_amount[$n_d->company] > $check_fund[$n_d->company]) {
                                            $company[$key1]['max_participant'] = $company_amount[$n_d->company];
                                        } else {
                                        }
                                    } elseif ($value1 == 0) {
                                        $company[$key1]['max_participant'] = $amount;
                                    }
                                    CompanyAmount::updateOrCreate(['company_id' => $company[$key1]['company_id'], 'merchant_id' => $merchant_id], ['max_participant' => isset($company[$key1]['max_participant']) ? $company[$key1]['max_participant'] : array_sum($check_one)]);
                                } else {
                                    $invest_check = DB::table('merchant_user')->where('merchant_id', $merchant_id)->join('users', 'users.id', 'merchant_user.user_id')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('user_has_roles.role_id', '!=', User::OVERPAYMENT_ROLE)->where('company', $company[$key1]['company_id'])->groupBy('users.company')->sum('amount');
                                    if ($invest_check <= 0) {
                                        if ($value1 != 0) {
                                            if ($check_fund[$n_d->company] < $amount) {
                                                $t_value = $company_amount[$n_d->company];
                                                $total = $company_amount[$n_d->company] - $companies_1[$n_d->company];
                                            } else {
                                                $total = $amount;
                                                $t_value = $total + $companies_1[$n_d->company];
                                            }
                                            $check_fund_1 = DB::table('company_amount')->where('merchant_id', $merchant_id)->where('company_id', 284)->pluck('max_participant', 'company_id')->toArray();
                                            $max_per1 = $check_fund_1[284] / $max_participant_fund * 100;
                                            if (count($test) < 2) {
                                                $reman_per = $test_per - $max_per1;
                                            } else {
                                                $reman_per = 100 - $max_per1;
                                            }
                                            $per = $value1 / $max_participant_fund * 100;
                                            $max_participant1 = ($max_participant_fund * $reman_per / 100) * $per / $company_per_total;
                                            $company[$key1]['max_participant'] = $max_participant1;
                                        } else {
                                            $company[$key1]['max_participant'] = 0;
                                        }
                                        CompanyAmount::updateOrCreate(['company_id' => $company[$key1]['company_id'], 'merchant_id' => $merchant_id], ['max_participant' => $company[$key1]['max_participant']]);
                                    }
                                }
                            }
                        }
                    }
                    $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
                    $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
                    $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
                    if ($OverpaymentAccount) {
                        $OverPaymentMerchantUser = MerchantUser::wheremerchant_id($merchant_id)->whereuser_id($OverpaymentAccount->id)->first();
                        if (! $OverPaymentMerchantUser) {
                            $item = ['user_id' => $OverpaymentAccount->id, 'amount' => 0, 'merchant_id' => $merchant_id, 'status' => 1, 'invest_rtr' => 0, 'mgmnt_fee' => 0, 'syndication_fee_percentage' => 0, 'commission_amount' => 0, 'commission_per' => 0, 'up_sell_commission_per'=>0,'up_sell_commission'=>0,'under_writing_fee' => 0, 'under_writing_fee_per' => 0, 'creator_id' => 0, 'pre_paid' => 0, 's_prepaid_status' => 1];
                            MerchantUser::create($item);
                        }
                    }
                }
            }
            if ($status == 1 && $count >= 1) {
                $status1 = 'partially-success';
            } elseif ($status >= 1) {
                $status1 = 'error';
            } else {
                $status1 = 'success';
            }
            if ($count > 0 && $m_d->paymentTerms->count() == 0 && $m_d->ach_pull) {
                $terms = $this->merchant->createTerms($m_d);
            }
            $result['status']='success';
            $result['status1']=$status1;
            $result['msg']=$msg;
      }
      catch (\Exception $e) {

         $result['status']=0;
         $result['msg']=$e->getMessage();
       }

       return $result;

  }

  public function mapParticipants($request)
  {
      try
      {
            $input = $request->all();
            if (empty($input['ip_id'])) {
               // return response()->json(['status' => 0, 'error' => 'The ip_id field is required', 'data' => '']);
               throw new Exception("The ip_id field is required", 1); 
            }
            if (empty($input['crm_participant_id'])) {
              //  return response()->json(['status' => 0, 'error' => 'The crm_participant_id field is required', 'data' => '']);
                throw new Exception("The crm_participant_id field is required", 1); 
            }
            $query1 = User::where('users.id', $input['ip_id'])->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'user_has_roles.role_id', 'roles.id')->where('roles.id', 2);
            $investor_count = $query1->count();
            if ($investor_count <= 0) throw new Exception("Participant with id not found", 1); 

             User::where('id', $input['ip_id'])->update(['crm_participant_id' => $input['crm_participant_id']]);

             $result['status']='success';
             $result['msg']='crm_participant_id updated successfully';

         }
          catch (\Exception $e) {

         $result['status']=0;
         $result['error']=$e->getMessage();
       }
      return $result;

  }

  public function getParticipants($request)
  {
      try{
            $input = $request->all();
            if (! isset($input['company_name'])) {
                 throw new Exception("No data found", 1); 
               // return response()->json(['status' => 0, 'error' => 'No data found', 'data' => '']);

            }
            if (empty($input['company_name'])) {
                throw new Exception("The company name field is required", 1); 
                //return response()->json(['status' => 0, 'error' => 'The company name field is required', 'data' => '']);
            }
            $ignore_arr = [' ', '-', '.', ','];
            $companyName = str_replace($ignore_arr, [''], $input['company_name']);
            $investors = User::select('users.id', 'users.name', 'users.created_at')->where('crm_participant_id', 0)->where(DB::raw("replace(replace(replace(replace(users.name,' ',''),'-','') ,'.',''),',','')"), 'like', '%'.$companyName.'%')->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->join('roles', 'user_has_roles.role_id', 'roles.id')->where('roles.id', 2)->get()->toArray();

              if (empty($investors)) throw new Exception("No data found", 1); 

              $result['status']='success';
              $result['result']=$investors;

        }catch (\Exception $e) {

         $result['status']=0;
         $result['error']=$e->getMessage();
       }

       return $result;

  }

  public function addMerchantNotes($request)
  {
      try{
            $input = $request->all();
            $merchant_id = isset($input['merchant_id']) ? $input['merchant_id'] : '';
            if (empty($merchant_id)) {
                throw new Exception("The Merchant id field is required", 1); 
            }
            if (empty($input['notes']) && is_numeric($input['notes'])) {
                 throw new Exception("The notes field is required", 1); 
            }
            $creator_id = null;
            $crm_user = DB::table('user_has_roles')->where('role_id', User::CRM_ROLE)->select('model_id')->first();
            if (isset($crm_user)) {
                $creator_id = $crm_user->model_id;
                $name=User::where('id',$creator_id)->value('name');
            }
            $notes = ['note' => $input['notes'], 'merchant_id' => $merchant_id,'added_by'=>$name];
            $result = MNotes::create($notes);
            if ($result) {
                $result = $result->toArray();
                $Mnotes = ['investor_merchant_id' => $merchant_id, 'notes_id' => $result['id'], 'notes' => $result['note'], 'created' => $result['created_at']];

                $result['status']="success";
                $result['msg']="Merchant Notes created successfully.";
                $result['data']=$Mnotes;


              //  return response()->json(['status' => 'success', 'msg' => 'Merchant Notes created successfully.', 'data' => $Mnotes]);
            }


      }catch (\Exception $e) {

         $result['status']=0;
         $result['error']=$e->getMessage();
       }

       return $result;

  }

  public function createMerchantBankAccount($request)
  {
       try {
            $input = $request->all();
            $validator = \Validator::make($request->all(), ['account_number' => 'required|min:4', 'bank_name' => 'required', 'account_holder_name' => 'required', 'routing_number' => 'required', 'type' => 'required']);
            if ($validator->fails()) {
                $error = '';
                foreach (array_values($validator->messages()->toArray()) as $msg) {
                    $error = $error.implode(' ', $msg);
                }
              throw new Exception($error, 1); 

               // return response()->json(['status' => 0, 'error' => $error, 'data' => '']);
            }
            $merchant_id = isset($input['merchant_id']) ? $input['merchant_id'] : '';
            if (!$merchant_id) throw new Exception("No merchant found", 1); 
            $merchant = Merchant::where('id', $merchant_id);
            if ($merchant->count() <= 0) {
                    throw new Exception('No merchant found', 1); 
                   // return response()->json(['status' => 0, 'result' => 'No merchant found']);
                }
          
            if (Str::contains($input['type'], 'debit') || Str::contains($input['type'], 'credit')) {
            } else {

                throw new Exception('invalid type', 1); 
                //return response()->json(['status' => 0, 'result' => 'invalid type']);
            }
            $params = ['account_number' => $input['account_number'], 'routing_number' => isset($input['routing_number']) ? $input['routing_number'] : null, 'bank_name' => $input['bank_name'], 'account_holder_name' => $input['account_holder_name'], 'merchant_id' => $input['merchant_id'], 'type' => $input['type'], 'default_credit' => isset($input['default_credit']) ? $input['default_credit'] : null, 'default_debit' => isset($input['default_debit']) ? $input['default_debit'] : null];
            $BankModel = new MerchantBankAccount;
            $status = $BankModel->selfCreate($params);

            if ($status['result']!= 'success') throw new Exception('Bank account not created.', 1); 
               
            $result['status']='success';
            $result['msg']='Bank account created successfully.';
            $result['data']=['account_number' => $input['account_number'], 'routing_number' => isset($input['routing_number']) ? $input['routing_number'] : null, 'bank_name' => $input['bank_name'], 'account_holder_name' => $input['account_holder_name'], 'merchant_id' => $input['merchant_id']];

        } catch (\Exception $e) {
            $result['status']=0;
            $result['msg']=$e->getMessage();
          
        }

        return $result;

  }

  public function updateMerchantBankAccount($request)
  {
       try{
            $input = $request->all();
            $validator = \Validator::make($request->all(), [
                // 'account_number' => 'required|min:4',
                //  'bank_name' => 'required',
                //  'account_holder_name' => 'required',
                //  'routing_number' => 'required',
                //  'type' => 'required',
                 'id' => 'required|exists:merchant_bank_accounts,id',
                //  'merchant_id' => 'required|exists:merchants,id'
            ]);
            if ($validator->fails()) {
                $error = '';
                foreach (array_values($validator->messages()->toArray()) as $msg) {
                    $error = $error.implode(' ',$msg);
                }
                throw new Exception($error, 1); 

               // return response()->json(['status' => 0, 'error' => $error, 'data' => '']);
            }
            if (!isset($input['id'])) throw new Exception('Id field is missing', 1); 
                // if (Str::contains($input['type'], 'debit') || Str::contains($input['type'], 'credit')) {
                // } else {
                //     //return response()->json(['status' => 0, 'result' => 'invalid type']);
                //    throw new Exception('invalid type', 1); 
                // }

        // $params = ['account_number' => $input['account_number'], 'routing_number' => isset($input['routing_number']) ? $input['routing_number'] : null, 'bank_name' => $input['bank_name'], 'account_holder_name' => $input['account_holder_name'], 'type' => $input['type'], 'merchant_id' => $input['merchant_id'], 'default_credit' => isset($input['default_credit']) ? $input['default_credit'] : null, 'default_debit' => isset($input['default_debit']) ? $input['default_debit'] : null];
                $BankModel = new MerchantBankAccount;
                $status = $BankModel->selfUpdate($input, $input['id']);
                $MerchantBankAccount=MerchantBankAccount::find($input['id']);

          if ($status['result']!= 'success') throw new Exception('Bank account not updated', 1); 
              
              $data=['account_number' => $MerchantBankAccount['account_number'], 'routing_number' => isset($MerchantBankAccount['routing_number']) ? $MerchantBankAccount['routing_number'] : null, 'bank_name' => $MerchantBankAccount['bank_name'], 'account_holder_name' => $MerchantBankAccount['account_holder_name']];

              $result['status']='success';
              $result['msg']='Bank account updated successfully.';
              $result['data']=$data;
                   // return response()->json(['status' => $this->successStatus, 'msg' => 'Bank account updated successfully.', 'data' =>   ]);
              
    }
    catch (\Exception $e) {

         $result['status']=0;
         $result['msg']=$e->getMessage();
       }

       return $result;

   }

   public function deleteMerchantBankAccount($request)
   {
        try {
            $input = $request->all();
            if (!isset($input['id'])) throw new Exception('Bank id required', 1);
                $exist = MerchantBankAccount::where('id', $input['id'])->count();
                if ($exist <= 0) {
                    throw new Exception('no data found', 1);
                   // return response()->json(['status' => 0, 'msg' => 'no data found', 'data' => '']);
                }
                $delete = MerchantBankAccount::find($input['id'])->delete();
                if (!$delete) throw new Exception('Bank account not deleted.', 1);
                    $result['status']='success';
                    $result['msg']='Bank account deleted successfully';
            
        } catch (\Exception $e) {
            $result['status']=0;
            $result['msg']=$e->getMessage();

          //  return response()->json(['status' => 0, 'msg' => $e->getMessage(), 'data' => '']);
        }

        return $result;

   }


}