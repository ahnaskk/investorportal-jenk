<?php

namespace App\Helpers;

use App\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\User;
use App\UserDetails;
use FFM;
use App\Template;
use App\Industries;
use App\Jobs\CommonJobs;
use Carbon\Carbon;
use App\Bank;
use App\Settings;
class FundingHelper
{
	public function signup($request){
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
		
		$user = User::create(request(['name', 'email', 'password', 'cell_phone']));
        $usr = User::find($user->id);
        $usr->company = 284;
        $usr->investor_type = 5;
        $usr->active_status = 0;
        $usr->notification_recurence = 3;
        $usr->management_fee = 2;
        $usr->global_syndication = 2;
        $usr->s_prepaid_status = 2;
        $usr->file_type = 1;
        $usr->display_value = 'mid';
        $usr->creator_id = $user->id;
        $usr->save();
        $user_details = UserDetails::create(['user_id' => $user->id, 'liquidity' => 0.000, 'liquidity_adjuster' => 0.00]);
        \DB::table('user_has_roles')->insert([['user_id' => 0, 'model_id' => $user->id, 'model_type' => \App\User::class, 'role_id' => 2]]);
        $message['title'] = 'Investor Signup';
        $message['subject'] = 'Investor Created';
        $message['status'] = 'funding_investor_signup';
        $message['name'] = 'Admin';
        $message['template_type'] = 'admin';
        $message['email'] = $usr->email;
        $message['phone'] = $usr->cell_phone;
        $message['content'] = "A new investor, $usr->name has taken interest in our Business Crowdfunding to participate in the funding process. The profile details, as created on ".\FFM::datetime(Carbon::now()).', are as given below:';
        $message['to_mail'] = $admin_email;
        $message['investor_name'] = $usr->name;
        $message['date_time'] = \FFM::datetime(Carbon::now());
        $email_template = Template::where([
            ['temp_code', '=', 'INSUA'], ['enable', '=', 1],
        ])->first();
        if ($email_template) {
            $emailJob = (new CommonJobs($message));
            dispatch($emailJob);
            if ($email_template->assignees) {
                $template_assignee = explode(',', $email_template->assignees);
                $bcc_mails = [];
                foreach ($template_assignee as $assignee) {
                    $bcc_mails[] = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();    
                }
                $message['to_mail'] = Arr::flatten($bcc_mails);
                $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                dispatch($emailJob);
            }
        }
        $message['content'] = 'Thank you for showing interest and creating a profile in our Business Crowdfunding. Please note the following details of the profile  created on '.\FFM::datetime(Carbon::now()).'';
        $message['name'] = $usr->name;
        $message['to_mail'] = $usr->email;
        $message['template_type'] = 'others';
        $email_template = Template::where([
            ['temp_code', '=', 'INSUO'], ['enable', '=', 1],
        ])->first();
        if ($email_template) {
            $emailJob = (new CommonJobs($message));
            dispatch($emailJob);
        }
        return $user;
		}
        public function getBankCount($request){
           $count = Bank::where('investor_id', $request->user()->id)->count();
           return $count;
        }
        public function getIndustries(){
            $industries = Industries::pluck('name', 'id')->toArray();
            return $industries;
        }
        public function merchant_market_data($merchantID){                 
        $funds = Merchant::where('merchants.id', $merchantID)->leftjoin('merchants_details','merchants_details.merchant_id','merchants.id')->select('funded', 'payment_amount', 'pmnts', 'factor_rate', 'commission', 'merchants.m_mgmnt_fee', 'm_syndication_fee', 'm_s_prepaid_status', 'rtr', 'max_participant_fund', 'merchants.name as business_name', 'merchants.id', 'underwriting_fee', 'complete_percentage', 'marketplace_permission', 'credit_score', 'merchants_details.monthly_revenue', 'industries.name as industry_name', 'advance_type', 'experian_intelliscore', 'experian_financial_score', 'merchant_user.user_id')->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->Join('industries', 'industries.id', 'merchants.industry_id')->with('FundingRequests');
        $value = $funds->first();
        $invested_amount = round($value->marketplaceInvestors()->sum('amount'));
        if (($value->max_participant_fund > $invested_amount)) {
            $maximum_amount = $value->max_participant_fund - $value->marketplaceInvestors()->sum('amount');
            $gross_value = $maximum_amount;
            $prepaid_fees = $value->marketplaceInvestors()->sum('commission_amount') + $value->marketplaceInvestors()->sum('pre_paid') + $value->marketplaceInvestors()->sum('under_writing_fee');
            $max_per = ($value->funded > 0) ? ($maximum_amount / $value->funded * 100) : 0;
            $syndication_fee_per = 0;
            if (\request()->user()) {
                $fees = User::select('management_fee', 'global_syndication', 's_prepaid_status', 'underwriting_fee', 'underwriting_status')->where('id', \request()->user()->id)->first()->toArray();
                $value->m_syndication_fee = ($fees['global_syndication'] !== null) ? $fees['global_syndication'] : $value->m_syndication_fee;
                $value->m_mgmnt_fee = ($fees['management_fee'] !== null) ? $fees['management_fee'] : $value->m_mgmnt_fee;
            }
            if ($value->m_s_prepaid_status == 2) {
                $syndication_fee_per = $value->m_syndication_fee;
            } elseif ($value->m_s_prepaid_status == 1) {
                $syndication_fee_per = $value->m_syndication_fee * $value->factor_rate;
            }
            $prepaid_fee_per = $value->commission + $value->underwriting_fee + $syndication_fee_per;

            return $marketplaceData[] = ['id' => $value->id, 'name' => $value->business_name, 'fundingCompleted' => \FFM::percent(100 - $max_per), 'available' => \FFM::percent($max_per), 'editPermission' => $value->marketplace_permission ? true : false, 'maximumAmount' => \FFM::dollar($maximum_amount), 'yourAmount' => round($maximum_amount, 2), 'netValuePercent' => round($prepaid_fee_per, 2), 'maximumParticipationAvailable' => \FFM::dollar($maximum_amount), 'maximum_amount' => $maximum_amount, 'totalFundedAmount' => \FFM::dollar($value->funded), 'rtr' => \FFM::dollar($value->rtr), 'prepaid' => round($value->m_syndication_fee, 2), 'dailyPayment' => \FFM::dollar($value->payment_amount), 'numberOfPayments' => round($value->pmnts), 'factorRate' => round($value->factor_rate, 2), 'commissionPayable' => round($value->commission, 2), 'managementFee' => round($value->m_mgmnt_fee, 2), 'underwritingFee' => round($value->underwriting_fee, 2), 'payment_type' => ($value->advance_type == 'daily_ach') ? 'Daily' : 'Weekly', 'credit_score' => $value->credit_score, 'monthly_revenue' => $value->monthly_revenue, 'industry_name' => $value->industry_name, 'experian_intelliscore' => \FFM::percent($value->experian_intelliscore), 'experian_financial_score' => \FFM::percent($value->experian_financial_score), 'merchant_user_exist_count' => $value->merchant_user_exist_count];
        }
        }
	

}