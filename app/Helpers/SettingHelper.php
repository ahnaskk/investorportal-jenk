<?php

namespace App\Helpers;

use App\Merchant;
use App\Settings;
use App\User;
use Carbon\Carbon;
use Exception;
use FFM;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


class SettingHelper
{
    public $user_type = 0;
    public $route = 0;
    public $link = 0;

    public function __construct()
    {
        $arr[] = '';
        $arr[] = 'investors::';
        $arr[] = 'merchants::';
        $links[] = '';
        $links[] = 'investors/';
        $links[] = 'merchants/';
        $this->user_type = 0;
        if (request()->segment(2) == 'investors') {
            $this->user_type = 1;
        } elseif (request()->segment(2) == 'merchants') {
            $this->user_type = 2;
        }
        $this->route = $arr[$this->user_type];
        $this->link = $links[$this->user_type];
    }

    /*
    System Settings index page
    */
    public function systemSettingIndex()
    {
        $page_title = 'System Settings';
        $substatus = DB::table('sub_statuses')->orderBy('name')->pluck('name', 'id')->toArray();
        $sys_substaus = (Settings::where('keys', 'agent_fee_on_substtaus')->value('values'));
        $sys_substaus = json_decode($sys_substaus, true);
        $payment_mode = (Settings::where('keys', 'collection_default_mode')->value('values'));
        $revert_date_mode = (Settings::where('keys', 'revert_date_mode')->value('values'))??Settings::Revert_CurrentDate;
        if($revert_date_mode==Settings::Revert_CurrentDate){
            $revert_date_mode=true;   
        } else {
            $revert_date_mode=false;
        }
        $two_factor_required = (Settings::where('keys', 'two_factor_required')->value('values'));
        $two_factor_required_mode = false;
        if($two_factor_required==1){
            $two_factor_required_mode = true;
        }
        $deduct_agent_fee_from_profit_only = (Settings::where('keys', 'deduct_agent_fee_from_profit_only')->value('values'));
        $settings = Settings::where('id', 1)->first();

        return [
            'page_title'       => $page_title,
            'substatus'        => $substatus,
            'sys_substaus'     => $sys_substaus,
            'settings'         => $settings,
            'deduct_agent_fee_from_profit_only'=>$deduct_agent_fee_from_profit_only,
            'payment_mode'     => $payment_mode,
            'revert_date_mode' => $revert_date_mode,
            'two_factor_required_mode'=>$two_factor_required_mode

        ];
    }

    /*
    System Settings substatus update function
    */
    public function systemSettingUpdateAction($request)
    {
        $status = false;
        try {
            $selected_substatus = ($request->sub_status) ? $request->sub_status : [];
            $json_substatus = json_encode($selected_substatus);
            Merchant::whereNotIn('sub_status_id', $selected_substatus)->update(['agent_fee_applied' => 0]);
            $update = Settings::where('keys', 'agent_fee_on_substtaus')->update(['values' => $json_substatus]);

            if (!$update) {
                $message = 'Substatus created!';
                if (!Settings::create(['values' => $json_substatus, 'keys' => 'agent_fee_on_substtaus'])) {
                    throw new Exception('Unknown error occured', 1);
                }
            } else {
                $message = 'Substatus updated!';
            }
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message,
        ];
    }

    /*
    System Settings account view update function
    */
    public function accountViewStatusUpdate($request)
    {
        $status = false;
        try {
            $show_agent_fee_account = (isset($request->agent_fee_on_off)) ? 1 : 0;
            $show_overpayment_account = (isset($request->overpayment_on_off)) ? 1 : 0;
            $edit_investment_after_payment = (isset($request->edit_investment_after_payment)) ? 1 : 0;
            $deduct_agent_fee_from_profit_only = (isset($request->deduct_agent_fee_from_profit_only)) ? $request->deduct_agent_fee_from_profit_only : 0;
            $update = Settings::where('id', 1)->update([
                'show_agent_account' => $show_agent_fee_account,
                'edit_investment_after_payment' => $edit_investment_after_payment,
                'show_overpayment_account' => $show_overpayment_account
            ]);
            
                $update1 = Settings::where('keys', 'deduct_agent_fee_from_profit_only')->update([
                    'values' => $deduct_agent_fee_from_profit_only,
                   
                ]); 
            
            if (!$update || !$update1) {
                throw new Exception('Accounts view status updation failed!', 1);
            }
            $message = 'Accounts view status updated!';
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message,
        ];
    }

    /*
    System Settings payment mode update function
    */
    public function paymentModeUpdateAction($request)
    {
        $status = false;
        try {
            $collection_default_mode = (isset($request->payment_mode_on_off)) ? 1 : 0;
            $update = Settings::where('keys', 'collection_default_mode')->update(['values' => $collection_default_mode]);
            if (!$update) {
                throw new Exception('Updation failed!', 1);
            }
            $message = 'Updated!';
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message,
        ];
    }

    /*
    Advances Settings view and update function
    */
    public function settingUpdateAction($request)
    {
        $status = $updation = false;
        $message = '';
        $result =  [];
        try {
            $page_title = 'Advanced Settings';
            $default = Settings::first()->toArray();
            $default_payment = [0 => '', 1 => 'Invested amount', 2 => 'RTR'];
            $emails = Settings::pluck('email')->toArray();

            $default_percentage_rule = (Settings::where('keys', 'default_percentage_rule')->value('values'));
            $default_percentage_rule = json_decode($default_percentage_rule, true);

            if ($request->default_percentage_rule) {
                $default_percentage_rule = json_encode($request->default_percentage_rule);
                $update = Settings::where('keys', 'default_percentage_rule')->update(['values' => $default_percentage_rule]);
                if (!$update) {
                    $create = Settings::create(['values' => $default_percentage_rule, 'keys' => 'default_percentage_rule']);
                    if (!$create) {
                        throw new Exception('Something wrong with default rule values!', 1);
                    }
                }
                $message = 'Default amount rule updated!';
                $status = $updation = true;
            }

            if ($request->ach_merchant) {
                $ach_merchant = json_encode($request->ach_merchant);
                $update = Settings::where('keys', 'ach_merchant')->update(['values' => $ach_merchant]);
                if (!$update) {
                    $create = Settings::create(['values' => $ach_merchant, 'keys' => 'ach_merchant']);
                    if (!$create) {
                        throw new Exception('Something wrong with Merchant ACH values!', 1);
                    }
                }
                $message = 'Merchant ACH settings updated!';
                $status = $updation = true;
            }

            $ach_merchant = (Settings::where('keys', 'ach_merchant')->value('values'));
            $ach_merchant = json_decode($ach_merchant, true);

            if ($request->ach_investor) {
                $ach_investor = json_encode($request->ach_investor);
                $update = Settings::where('keys', 'ach_investor')->update(['values' => $ach_investor]);
                if (!$update) {
                    $create = Settings::create(['values' => $ach_investor, 'keys' => 'ach_investor']);
                    if (!$create) {
                        throw new Exception('Something wrong with ACH syndicate values!', 1);
                    }
                }
                $message = 'ACH syndicate settings updated!';
                $status = $updation = true;
            }

            $ach_investor = (Settings::where('keys', 'ach_investor')->value('values'));
            $ach_investor = json_decode($ach_investor, true);
            if ($request->system_admin_emails) {
                if ($request->system_admin_emails == '') {
                    throw new Exception('Please Enter System Admin Emails!', 1);
                }
                $system_admin_email_trim = (str_replace(' ', '', $request->system_admin_emails));
                $system_admin_email_array = explode(',', $system_admin_email_trim);
                foreach ($system_admin_email_array as $email) {
                    if (!preg_match('/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/', $email)) {
                        throw new Exception('Please Enter Valid System Admin Emails!', 1);
                    }
                }
                $update = Settings::where('keys', 'system_admin')->update(['values' => $system_admin_email_trim]);
                if (!$update) {
                    $create = Settings::create(['values' => $system_admin_email_trim, 'keys' => 'system_admin']);
                    if (!$create) {
                        throw new Exception('Something wrong with System Admin values!', 1);
                    }
                }
                $message = 'System Admin settings updated!';
                $status = $updation = true;
            }
            $system_admin_emails = (Settings::where('keys', 'system_admin')->value('values'));

            if (isset($request->minimum_investment_value) && isset($request->max_investment_per)) {
                $minimum_investment_value = $request->minimum_investment_value;
                $max_investment_per = $request->max_investment_per;
                $message = '';

                $update_1 = Settings::where('keys', 'minimum_investment_value')->update(['values' => $minimum_investment_value]);
                if (!$update_1) {
                    $create = Settings::create(['values' => $minimum_investment_value, 'keys' => 'minimum_investment_value']);
                    if (!$create) {
                        throw new Exception('Something wrong with minimum investment values!', 1);
                    }
                }
                $message .= 'Minimum Investment Amount,';

                $update_2 = Settings::where('keys', 'max_investment_per')->update(['values' => $max_investment_per]);
                if (!$update_2) {
                    $create_2 = Settings::create(['values' => $max_investment_per, 'keys' => 'max_investment_per']);
                    if (!$create_2) {
                        throw new Exception('Something wrong with minimum investment percentage!', 1);
                    }
                }
                $message .= 'Maximum Investment Percentage Settings updated!';
                $status = $updation = true;
            }

            $minimum_investment_value = (Settings::where('keys', 'minimum_investment_value')->value('values'));
            $max_investment_per = (Settings::where('keys', 'max_investment_per')->value('values'));
            $default_date_format = (User::where('id', $request->user()->id)->value('date_format'));
            $default_date_format = json_decode($default_date_format, true);
            $date_formats = collect([(object) ['dbFormat' => 'm-d-Y', 'format' => 'MM-DD-YYYY'], (object) ['dbFormat' => 'd-m-Y', 'format' => 'DD-MM-YYYY'], (object) ['dbFormat' => 'Y-m-d', 'format' => 'YYYY-MM-DD']]);
            $tzlist = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
            $default_timezone = (User::where('id', $request->user()->id)->value('timezone'));
            if ($request->mobile_app_from_date || $request->email || $request->rate || $request->forceopay || $request->payments || $request->date_start || $request->hide || $request->max_assign_per || $request->send_permission || $request->default_date_format || $request->default_timezone || $request->agent_fee_per) {
                $from_date = Carbon::createFromFormat(FFM::defaultDateFormat('db') . ' H:i:s', $request->mobile_app_from_date)->format('Y-m-d H:i:s');
                if ($request->email == '') {
                    throw new Exception('Please Enter Email ID', 1);
                }
                if ($request->agent_fee_per > 50 || $request->agent_fee_per < 0) {
                    throw new Exception('Please Enter value in between 0% to 50% as agent fee', 1);
                }
                $email_trim = (str_replace(' ', '', $request->email));
                $update = Settings::where('id', 1)->update(['rate' => $request->rate, 'forceopay' => $request->forceopay, 'default_payment' => $request->payments, 'portfolio_start_date' => $request->date_start, 'email' => $email_trim, 'hide' => $request->hide, 'last_mob_notification_time' => $from_date, 'max_assign_per' => $request->max_assign_per, 'send_permission' => $request->send_permission, 'agent_fee_per' => $request->agent_fee_per, 'historic_status' => $request->historic_status]);
                $user = User::find($request->user()->id);
                $format = Arr::first($date_formats, function ($value, $key) use ($request) {
                    return $value->dbFormat == $request->default_date_format;
                });
                $user->date_format = json_encode($format);
                $user->timezone = $request->default_timezone;
                $update2 = $user->save();
                if ($update && $update2) {
                    $message = 'Settings Updated Successfully!';
                    $status = $updation = true;
                } else {
                    throw new Exception('Settings Updation Failed!', 1);
                }
            }
            $config_ach_fee_types = config('custom.ach_fee_types');
            $ach_fee_types = [];
            foreach ($config_ach_fee_types as $ach_fee => $ach_fee_name) {
                $ach_fee_types[] = [
                    'name' => $ach_fee_name,
                    'type' => $ach_fee,
                    'db_name' => $ach_fee . '_amount',
                    'input_name' => 'ach_merchant[' . $ach_fee . '_amount]',
                    'input_value' => 'ach_merchant[\'' . $ach_fee . '_amount\']',
                ];
            }
            $status = true;
            $result = [
                'page_title' => $page_title,
                'default' => $default,
                'default_payment' => $default_payment,
                'emails' => $emails,
                'default_percentage_rule' => $default_percentage_rule,
                'ach_merchant' => $ach_merchant,
                'ach_investor' => $ach_investor,
                'system_admin_emails' => $system_admin_emails,
                'date_formats' => $date_formats,
                'default_date_format' => $default_date_format,
                'tzlist' => $tzlist,
                'default_timezone' => $default_timezone,
                'minimum_investment_value' => $minimum_investment_value,
                'max_investment_per' => $max_investment_per,
                'ach_fee_types' => $ach_fee_types
            ];
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message,
            'updation' => $updation,
            'result' => $result,
        ];
    }
    
    public function revertDateModeUpdateAction($request)
    {
        try {
            $status = false;
            (isset($request->revert_date_mode)) ? 1 : 0;
            $revert_date_mode = $request->revert_date_mode?Settings::Revert_CurrentDate:Settings::Revert_PaymentDate;
            $update = Settings::where('keys', 'revert_date_mode')->update(['values' => $revert_date_mode]);
            if (! $update) {
                $create = Settings::create(['values' => $revert_date_mode, 'keys' => 'revert_date_mode']);
                if (! $create) {
                    throw new Exception('Something wrong with default revert date!', 1);
                }
            }
            $message = 'Default revert date updated!';
            $status  = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status'  => $status,
            'message' => $message,
        ];
    }
    public function twoFactorRequiredUpdation($request){
        try {
            $status = false;
            $two_factor_required_status = (isset($request->two_factor_required_status)) ? 1 : 0;
           // $two_factor_required_status = $request->two_factor_required_status?Settings::Revert_CurrentDate:Settings::Revert_PaymentDate;
            $update = Settings::where('keys', 'two_factor_required')->update(['values' => $two_factor_required_status]);
            $two_factor_status = ($two_factor_required_status==1) ? true : false;
            
            if (! $update) {
                $create = Settings::create(['values' => $two_factor_required_status, 'keys' => 'two_factor_required']);
                
                if (! $create) {
                    throw new Exception('Something wrong with two factor!', 1);
                }
            }
            
            $message = 'Two factor required status updated!';
            $status  = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status'  => $status,
            'message' => $message,
        ];
    }
}
