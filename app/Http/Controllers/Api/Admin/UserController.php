<?php

namespace App\Http\Controllers\Api\Admin;

use App\BankDetails;
use App\Helpers\ChartHelper;
use App\Helpers\LiquidityLogHelper;
use App\Helpers\MerchantUserHelper;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BankAccountRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Jobs\CommonJobs;
use App\Merchant;
use App\MerchantStatusLog;
use App\MerchantUser;
use App\PaymentInvestors;
use App\Settings;
use App\Template;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends AdminAuthController
{
    public function postPermissionDenied()
    {
        return new ErrorResource(['message' => 'Permission Denied']);
    }

    public function postActivityLog(Request $request)
    {
        $users = User::getAdminRoleUsers()->pluck('name', 'id')->toArray();
    }

    public function getReAssign(Request $request)
    {
        $investorId = $request->input('investor_id', 0);
        $investors = User::investors('', '', '', true)->with('userDetails')->get();
        $liquiditySum = UserDetails::where('liquidity', '>', 0)->sum('liquidity');
        $investorDetails = MerchantUserHelper::getCurrentInvestment($investorId, [1, 3], []);
        $investorDetails = ['sum_commission_amount' => optional($investorDetails)->commission_amount ?? 0, 'paid_participant_ishare' => optional($investorDetails)->paid_participant_ishare ?? 0, 'invest_rtr' => optional($investorDetails)->invest_rtr ?? 0, 'merchants' => optional($investorDetails)->merchants ?? 0];

        return new SuccessResource(['investors' => $investors, 'investor' => $investorDetails, 'liquidity' => $liquiditySum]);
    }

    public function postReAssign(Request $request)
    {
        $balanceAmount = $request->input('balance_amount');
        $filterInvestorId = $request->input('investor_id');
        $investorAmounts = $request->input('amount');
        $investorDetails = MerchantUserHelper::getCurrentInvestment($filterInvestorId, [1, 3], []);
        $originalValue = optional($investorDetails)->commission_amount ?? 0 - optional($investorDetails)->paid_participant_ishare ?? 0;
        if ($originalValue < $balanceAmount) {
            return new ErrorResource(['message' => 'Maximum Amount To Be Moved is '.$originalValue]);
        }
        $investors = [];
        $investorAmounts = (is_array($investorAmounts)) ? $investorAmounts : [];
        foreach ($investorAmounts as $investorId => $investorAmount) {
            $investors[$investorId] = $investorAmount;
        }
        $investorInvestments = MerchantUserHelper::getCurrentInvestment($filterInvestorId, [1, 3], [1], false, 99);
        $totalTransferAmount = 0;
        foreach ($investorInvestments as $key => $investment) {
            if ($investment->invest_rtr > 1.01 * ($investment->paid_participant_ishare) && ($totalTransferAmount < $balanceAmount)) {
                $factorRate = ($investment->invest_rtr / $investment->amount);
                $balanceDoubt = $investment->amount - $investment->paid_participant_ishare / $factorRate;
                foreach ($investors as $investorId => $amount) {
                    $amount1 = trim($amount / 100);
                    if (($balanceAmount - $totalTransferAmount) < $balanceDoubt) {
                        $transferAmount = ($balanceAmount - $totalTransferAmount);
                    } else {
                        $transferAmount = $balanceDoubt;
                    }
                    $resAssignPer = ($amount1) * ($transferAmount / $investment->amount);
                    $re_assign_per_glob = 1 - $resAssignPer;
                    if ($resAssignPer > 0.00000000001 && $resAssignPer <= 1) {
                        $investorDestination = MerchantUser::where('merchant_id', $investment->merchant_id)->where('user_id', $investment->user_id)->whereIn('merchant_user.status', [1, 3])->first();
                        $completePercentage = $dest_amount = $dest_invest_rtr = $dest_share = $dest_commission_amount = $dest_pre_paid = $dest_paid_mgmnt_fee = $dest_paid_syndication_fee = $dest_paid_participant_ishare = 0;
                        if ($investorDestination) {
                            $dest_amount = $investorDestination->amount;
                            $dest_invest_rtr = $investorDestination->invest_rtr;
                            $dest_share = $investorDestination->share;
                            $dest_commission_amount = $investorDestination->commission_amount;
                            $dest_pre_paid = $investorDestination->pre_paid;
                            $dest_paid_participant_ishare = $investorDestination->paid_participant_ishare;
                            $dest_paid_mgmnt_fee = $investorDestination->paid_mgmnt_fee;
                        }
                        $status4 = MerchantUser::create(['user_id' => $investment->user_id, 'merchant_id' => $investment->merchant_id, 'requested_time' => $investment->requested_time, 'approved_time' => $investment->approved_time, 'deal_name' => $investment->deal_name, 'transaction_type' => $investment->transaction_type, 'paid_participant_ishare' => $dest_paid_participant_ishare, 'paid_mgmnt_fee' => $dest_paid_mgmnt_fee, 'creator_id' => $investment->creator_id, 's_prepaid_status' => $investment->s_prepaid_status, 'mgmnt_fee' => $investment->mgmnt_fee, 'amount' => $resAssignPer * $investment->amount + $dest_amount, 'invest_rtr' => $resAssignPer * $investment->invest_rtr + $dest_invest_rtr, 'share' => $resAssignPer * $investment->share + $dest_share, 'commission_amount' => $resAssignPer * $investment->commission_amount + $dest_commission_amount, 'pre_paid' => $resAssignPer * $investment->pre_paid + $dest_pre_paid, 'complete_per' => $completePercentage, 'status' => 1]);
                        if ($investorDestination and ! empty($dest_amount)) {
                            $delete1_status = MerchantUser::find($investorDestination->id)->delete();
                        }
                        if (! MerchantUser::find($investment->id)) {
                            continue;
                        }
                        $status4 = MerchantUser::find($investment->id)->update(['amount' => $investment->amount - $resAssignPer * $investment->amount, 'invest_rtr' => $investment->invest_rtr - $resAssignPer * $investment->invest_rtr, 'share' => $investment->share - $resAssignPer * $investment->share, 'commission_amount' => $investment->commission_amount - $resAssignPer * $investment->commission_amount, 'pre_paid' => $investment->pre_paid - $resAssignPer * $investment->pre_paid, 'status' => 1, 'complete_per' => $completePercentage]);
                        $totalTransferAmount += ($resAssignPer * $investment->amount);
                    }
                }
            }
        }
        LiquidityLogHelper::updateLog($filterInvestorId, 'Re-Assign');

        return new SuccessResource(['message' => 'Updated successfully!']);
    }

    public function getInvestors(Request $request)
    {
        $search = $request->input('search');
        $merchantId = $request->input('merchant_id');
        $company = $request->input('company');
        $investorType = $request->input('investor_type');
        $merchantId = (! is_array($merchantId)) ? [$merchantId] : $merchantId;
        $list = [];
        $totalRecords = [];
        if (! empty(optional($merchantId)[0]) || $company || $investorType) {
            $investors = MerchantUser::select('merchant_user.user_id', 'users.name as investor_name', 'user_details.liquidity', 'users.investor_type')->join('users', 'users.id', 'merchant_user.user_id')->join('user_details', 'users.id', 'user_details.user_id')->whereNotIn('users.investor_type', [5])->where(function ($query) use ($merchantId, $company, $investorType) {
                if (! empty($merchantId[0])) {
                    $query->whereIn('merchant_user.merchant_id', $merchantId);
                }
                if ($company != null && ! empty($company)) {
                    $query->where('users.company', $company);
                }
                if ($investorType != 0) {
                    $query->where('users.investor_type', $investorType);
                }
            })->where(function ($query) use ($search) {
                $query->orWhere('users.name', 'like', '%'.$search.'%');
            })->orderBy('users.name')->distinct('user_id')->get();
            $data = [];
            foreach ($investors as $investor) {
                $type = $investor->investor_type == 2 ? 'Equity' : 'Debt';
                $list[] = ['id' => $investor->user_id, 'investor_name' => $investor->investor_name.'-'.$type.' -'.$investor->liquidity];
            }
        }

        return new SuccessResource(['total_count' => $totalRecords, 'incomplete_results' => true, 'items' => $list]);
    }

    public function getAssignedInvestors(Request $request)
    {
        $merchantId = $request->input('merchant_id');
        $company = $request->input('company');
        $list = [];
        $investors = MerchantUser::select('merchant_user.user_id', 'users.name as investor_name')->leftJoin('users', 'users.id', 'merchant_user.user_id')->where('merchant_user.status', 1)->where(function ($query) use ($merchantId, $company) {
            if ($merchantId != null && ! empty($merchantId)) {
                $query->where('merchant_user.merchant_id', $merchantId);
            }
            if ($company != null && ! empty($company)) {
                $query->where('users.company', $company);
            }
        })->orderBy('users.name')->distinct('users.id')->get();
        foreach ($investors as $investor) {
            $list[] = ['id' => $investor->user_id, 'investor_name' => $investor->investor_name];
        }

        return new SuccessResource(['total_count' => MerchantUser::count(), 'incomplete_results' => true, 'items' => $list]);
    }

    public function getInvestorsForOwner(Request $request)
    {
        $merchantId = $request->input('merchant_id');
        $company = $request->input('company');
        if ($company == 0) {
            $data = User::pluck('id');
        } else {
            $data = User::where('company', $company)->pluck('id');
        }

        return new SuccessResource(['data' => $data]);
    }

    public function getCompanyWiseInvestors(Request $request)
    {
        $company = $request->input('company');
        $investors = User::investors()->where(function ($inner) use ($company) {
            $inner->where('users.id', '>', 0);
            if (! empty($company)) {
                $inner->where('company', $company);
            }
        })->pluck('users.name', 'users.id')->toArray();

        return new SuccessResource(['data' => $investors, 'incomplete_results' => true]);
    }

    public function getAllInvestors(Request $request)
    {
        $investors = User::investors()->with('userDetails')->get();
        $investors = collect($investors)->map(function ($investor) {
            $type = $investor->investor_type == 2 ? 'Equity' : 'Debt';

            return ['id' => $investor->id, 'investor_name' => $investor->name.' - '.$type.' - '.$investor->userDetails['liquidity']];
        });

        return new SuccessResource(['data' => $investors]);
    }

    public function getMerchants(Request $request)
    {
        $search = $request->get('search');
        $merchants = Merchant::where(function ($query) use ($search) {
            $query->orWhere('name', 'like', '%'.$search.'%');
        })->orderBy('name')->pluck('merchants.name', 'merchants.id')->toArray();

        return new SuccessResource(['total_count' => Merchant::count(), 'data' => $merchants, 'incomplete_results' => true]);
    }

    public function getInvestorAdmins(Request $request)
    {
        $search = $request->input('search');
        $company = $request->input('company');
        $company = ($company == 1) ? 1 : 0;
        $investorAdmins = User::where('company', $company)->join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('role_id', 6)->pluck('users.name as username', 'users.id')->toArray();

        return new SuccessResource(['total_count' => count($investorAdmins), 'data' => $investorAdmins, 'incomplete_results' => true]);
    }

    public function getCompanies(Request $request)
    {
        $search = $request->input('search');
        $investorId = $request->get('investor_id');
        $companies = User::where('id', $investorId)->get();
        $companies = collect($companies)->map(function ($company) {
            $companyName = User::where('id', $company->company)->value('name');

            return ['id' => $company->id, 'company' => $companyName];
        });

        return new SuccessResource(['data' => $companies, 'total_count' => User::where('id', $investorId)->count(), 'incomplete_results' => true]);
    }

    public function getPercentageDealGraph(Request $request)
    {
        $permission = ($request->user()->hasRole(['company'])) ? 0 : 1;
        $investedAmount = MerchantUser::select('user_id', DB::raw('SUM(merchant_user.amount) as invested_amount'), 'users.name')->whereIn('merchant_user.status', [1, 3])->leftJoin('users', 'users.id', 'merchant_user.user_id');
        $filterInvestors = [0 => 'All'];
        if ($request->user()->hasRole(['company'])) {
            $investedAmount->where('users.company', $this->user->id);
            $investedAmount->groupBy('merchant_user.user_id')->get();
            foreach ($investedAmount as $key => $value) {
                $filterInvestors[$value->user_id] = $value->name.'-'.$value->invested_amount;
            }
        }
        $investors = collect(User::investors()->pluck('name', 'id')->toArray())->merge($filterInvestors)->toArray();
        $lenders = User::getLenders()->pluck('id', 'name')->toArray();
        $lenders = array_unshift($lenders, 'All');
        $attributes = [0 => 'Label', 1 => 'Status', 2 => 'Industry', 3 => 'Investor', 4 => 'Lenders', 5 => 'Commissions', 6 => 'Factor rate', 7 => 'State', 8 => 'No Filter'];
        $graphLabels = [0 => 'Invested Amount', 1 => 'Default Amount'];
        $states = DB::table('us_states')->select('state as name', 'id')->get();
        $graphData = MerchantUserHelper::getInvestmentByFields('state_id', $states);
        $companies = User::getAllCompanies()->pluck('name', 'id')->toArray();
        $companies = array_unshift($companies, 'All');

        return new SuccessResource(['states' => $states, 'graph_data' => $graphData, 'companies' => $companies, 'attributes' => $attributes, 'graph_labels' => $graphLabels, 'lenders' => $lenders, 'investors' => $investors]);
    }

    public function postPieChartValues(Request $request)
    {
        $hasCompanyRole = ($this->user->hasRole(['company'])) ? 0 : 1;
        $lenderId = $request->input('lender_id');
        $investorIds = $request->input('investor');
        $graphLabel = $request->input('label');
        $userId = $this->user->id;
        $subInvestors = [];
        $startDate = $request->input('start_date', '01/01/2010');
        $endDate = $request->input('end_date', Carbon::now()->format('m/d/Y'));
        $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->format('Y-m-d');
        if (empty($hasCompanyRole)) {
            $subInvestors = User::investors()->where('company', $this->user->id)->pluck('id')->toArray();
        }
        $attributes = $request->input('attribute');
        $type = (int) $request->input('type');
        $pieChartQuery = ChartHelper::getPieChartData($attributes, $type, 1);
        if ($hasCompanyRole) {
            $pieChartQuery->whereIn('merchant_user.user_id', $subInvestors);
        }
        if (! empty($lenderId)) {
            $pieChartQuery->where('merchants.lender_id', $lenderId);
        }
        if (! empty($graphLabel)) {
            $pieChartQuery->where('merchants.label', $graphLabel);
        }
        if ($investorIds) {
            $pieChartQuery->whereIn('merchant_user.user_id', $investorIds);
        }
        if (! empty($startDate) and ! empty($endDate)) {
            $pieChartQuery->whereBetween('merchants.date_funded', [$startDate, $endDate]);
        }
        $pieChartData = $pieChartQuery->get();

        return new SuccessResource(['chart' => $pieChartData]);
    }

    public function postDownloadPieChart()
    {
    }

    public function postUpdateLenderStatus(Request $request)
    {
        $lenderId = $request->input('lender_id');
        $status = (int) $request->input('status');
        $lender = User::findOrFail($lenderId);
        $lender->active_status = $status;
        $lender->update();
        Merchant::where('lender_id', $lenderId)->update(['active_status' => $status]);
        Merchant::where('creator_id', $lenderId)->update(['active_status' => $status]);

        return new SuccessResource(['message' => 'Status has been updated successfully!']);
    }

    public function postMerchantStatusData(Request $request)
    {
        $merchants = Merchant::select('merchant.*', 'users.lag_time', DB::raw('SUM(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount'))->where('merchants.complete_percentage', '<', 99)->where('merchants.complete_percentage', '>', 0)->where('merchants.sub_status_id', '=', 1)->leftJoin('merchant_user', 'merchant_user.merchant_id', 'merchants.id')->leftjoin('users', 'users.id', 'merchants.lender_id')->groupBy('merchants.id')->get()->map(function ($merchant) {
            $payments = PaymentInvestors::select(DB::raw('SUM(participant_share - payment_investors.mgmnt_fee) as final_participant_share'))->where('merchant_id', $merchant->id)->first();
            $lastPaymentDate = ! empty($merchant->last_payment_date) ? $merchant->last_payment_date : $merchant->date_funded;
            if (! empty($lastPaymentDate)) {
                $fromDate = Carbon::parse($lastPaymentDate);
                $toDate = Carbon::now();
                $days = $fromDate->diffInDays($toDate);
                if ($days >= (30 + $merchant->lag_time) && $payments && $payments->final_participant_share < $merchant->investment_amount) {
                    return ['merchant_id' => $merchant->id, 'merchant_name' => $merchant->name, 'complete_per' => FFM::percent($merchant->complete_percentage), 'last_payment_date' => ! empty($merchant->last_payment_date) ? Carbon::parse($merchant->last_payment_date)->format('m/d/Y') : '-', 'days' => $days - $merchant->lag_time];
                }
            }
        });

        return new SuccessResource(['merchants' => $merchants]);
    }

    public function postMerchantStatus(Request $request)
    {
        $toEmails = Settings::value('email');
        $toEmails = explode(',', $toEmails);
        $merchantIds = $request->input('merchant_id');
        $diff_in_months = $request->input('diff_in_months');
        $totalMerchants = (is_array($merchantIds)) ? count($merchantIds) : 0;
        if (is_array($merchantIds) and count($merchantIds) > 0) {
            foreach ($merchantIds as $merchantId) {
                $merchant = Merchant::where('id', $merchantId)->first();
                if (! $merchant) {
                    continue;
                }
                $old_status = $merchant->sub_status_id;
                $merchantName = $merchant->name;
                $monthDays = $diff_in_months[$merchantId] ?? 0;
                if ($old_status != 4) {
                    $logArray = ['merchant_id' => $merchantId, 'old_status' => $old_status, 'current_status' => 4, 'description' => ' Not received a payment in '.$monthDays.' days, therefore status changed to default by '.$this->user->name, 'creator_id' => $this->user->id];
                    $log = MerchantStatusLog::create($logArray);    
                }
                $merchant->sub_status_id = 4;
                $merchant->last_status_updated_date = $log->created_at;
                $merchant->update();
                $merchant = ['title' => 'Payment Pending'];
                $message['content'] = 'Merchant '.$merchantName.' has payments pending for '.$monthDays.' days';
                $message['to_mail'] = $toEmails;
                $message['merchant_id'] = $merchantId;
                $message['status'] = 'merchant_change_status';
                $message['unqID'] = unqID();
                $message['template_type'] = 'pending_payment';
                $message['merchant_name'] = $merchantName;
                $message['days'] = $monthDays;
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
                                $role_mails = array_diff($role_mails, $toEmails);
                                $bcc_mails[] = $role_mails;  
                            }
                            $message['bcc'] = Arr::flatten($bcc_mails);
                        }
                        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
                        $emailJob = (new CommonJobs($message))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $message['bcc'] = [];
                        $message['to_mail'] = $admin_email;
                        $emailJob = (new CommonJobs($message));
                        dispatch($emailJob);
                    }
                } catch (\Exception $e) {
                    logger()->error($e->getMessage());
                }
            }
        }

        return new SuccessResource(['message' => $totalMerchants.' Merchant/s Status Changed to Default Successfully!']);
    }
}
