<?php


namespace App\Library\Repository;

use App\AchRequest;
use App\Bank;
use App\Library\Repository\Interfaces\IUserActivityLogRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\UserActivityLog;
use App\InvestorTransaction;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Merchant;
use App\MerchantUser;
use App\Models\InvestorAchRequest;
use App\User;
use App\ParticipentPayment;
use App\UserDetails;
use App\VelocityFee;
use FFM;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class UserActivityLogRepository implements IUserActivityLogRepository
{

    public function __construct(IRoleRepository $role)
    {
        $this->table = new UserActivityLog();
        $this->role = $role;
    }

    public function merchantActivityLog($filter=[])
    {
        $merchant_id = $filter['merchant_id'];
        $data_id = $filter['data_id'];
        $type = $filter['type'];
        $user_id = $filter['user_id'];
        $action = $filter['action'];
        $search_type = $filter['search_type'];
        $from_date = $filter['from_date'];
        $to_date = $filter['to_date'];
        $objectId = $filter['objectId'];
        $search = $filter['search'];
        $start = $filter['start'];
        $limit = $filter['limit'];
        $order_col = $filter['order_col'];
        $order_by = $filter['order_by'];
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $otherInvestors = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestorsWithTrashed();
            $otherInvestors = $investor->whereNotIn('company', $userId);
            $otherInvestors = $otherInvestors->pluck('id')->toArray();
            $merchantUser = $investor->whereIn('company', $userId);
            $merchantUser = $merchantUser->pluck('id')->toArray();
        }
        $query = $this->table->where(function ($inner) use ($merchant_id, $otherInvestors, $permission) {
            if (empty($permission)) {
                $inner->where('merchant_id', $merchant_id)->whereNotIn('investor_id', $otherInvestors);
                $inner->orWhere('merchant_id', $merchant_id)->whereNull('investor_id');    
            } else {
                $inner->orWhere('merchant_id', $merchant_id);
                $inner->orWhere('object_id', $merchant_id);
            }
        });

        
        if (! empty($type)) {
            $query->where('user_activity_logs.type', $type);
        }
        if (! empty($user_id)) {
            $query->where('user_activity_logs.user_id', $user_id);
        }
        if (! empty($objectId) and $type == 'investor_transaction') {
            $transIds = InvestorTransaction::where('investor_id', $objectId)->pluck('id')->toArray();
            $query->where(function ($inner) use ($transIds, $objectId) {
                $inner->orWhere(DB::raw('JSON_UNQUOTE(detail-> "$.investor_id")'), $objectId);
                $inner->orWhereIn('object_id', $transIds);
            });
        }
        if (! empty($action)) {
            $query->where('user_activity_logs.action', $action);
        }
        if (! empty($from_date)) {
            $from_date = ET_To_UTC_Time($from_date.' 00:00', 'datetime');
            $query->where('user_activity_logs.created_at', '>=', $from_date);
        }
        if (! empty($to_date)) {
            $to_date = ET_To_UTC_Time($to_date.' 23:59', 'datetime');
            $query->where('user_activity_logs.created_at', '<=', $to_date);
        }
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('user_activity_logs.type', 'like', '%'.$search.'%')->orWhereRaw("DATE_FORMAT(user_activity_logs.created_at,'%m/%d/%Y') like ?", ["%$search%"])->orWhere('user_activity_logs.type', 'like', '%'.$search.'%')->orWhere(DB::raw('REPLACE(user_activity_logs.type, "_", " ")'), 'like', '%'.$search.'%')->orWhere('user_activity_logs.action', 'like', '%'.$search.'%')->orWhere('user_activity_logs.detail', 'like', '%'.$search.'%');
                $inner->orWhereIn('merchant_id', function($query) use ($search){
                    $query->select('id')
                    ->from('merchants')
                    ->where('name', 'like', '%'.$search.'%');
                });
                $inner->orWhereIn('investor_id', function($query) use ($search){
                    $query->select('id')
                    ->from('users')
                    ->where('name', 'like', '%'.$search.'%');
                });
            });
        }
        if (! empty($order_col)) {
            if ($order_col == 0) {
                $sub_query = User::withTrashed()->select(DB::raw('id AS user_id'), 'name');
                $query->join(DB::raw('('.$sub_query->toSql().') AS sub_query'), 'sub_query.user_id', '=', 'user_activity_logs.user_id');
                $query->orderBy('sub_query.name', $order_by);
            } elseif ($order_col == 1) {
                $query->orderBy('user_activity_logs.created_at', $order_by);
            } elseif ($order_col == 2) {
                $query->orderBy('user_activity_logs.detail', $order_by);
            } elseif ($order_col == 3) {
                $query->orderBy('user_activity_logs.type', $order_by);
            } elseif ($order_col == 4) {
                $query->orderBy('user_activity_logs.action', $order_by);
            }
        }
        $query->select('user_activity_logs.*');
        $query->orderByDesc('user_activity_logs.id');
        $total_records = $query->count();
        $logs = $query->limit($limit)->offset($start)->get();
        $rows = [];
        $totalAmount = 0;
        if (count($logs) > 0) {
            foreach ($logs as $log) {
                $amount = 0;
                $changes = '';
                try {
                    $details = json_decode($log->detail, true);
                } catch (\ErrorException $e) {
                    $details = [];
                }
                if ($log->action != 'updated') {
                    ksort($details);
                }
                $object = false;
                if (is_array($details) and count($details) > 0) {
                    $changes = $this->appendParentPrefix($log);
                    foreach ($details as $field_name => $value) {
                        if ($field_name == 'merchant_id') {
                            continue;
                        }
                        if ($log->action == 'updated') {
                            $field_name = $this->properFieldName($object, $field_name);
                            if ($field_name == 'updated_at' or $field_name == 'deleted_at' or $field_name == 'activation') {
                                continue;
                            }
                            $omit = ['merchant_id', 'creator_id', 'id', 'user_id', 'company_id', 'last_status_updated_date', 'creator', 'merchant_permission', 'term_id', 'is_fees', 'ach_request_id', 'fee_type', 'model', 'model_id', 'auth_code', 'source_from', 'revert_id', 'position', 'annual_revenue', 'logo', 'final_participant_share', 'old_factor_rate', 'paid_count', 'annualized_rate', 'last_rcode', 'paid_participant_ishare', 'actual_paid_participant_ishare'];
                            if ($log->type == 'velocity_fee') {
                                array_push($omit, 'order_id');
                            }
                            if (in_array($field_name, $omit)) {
                                continue;
                            }
                            if ($value && is_array($value) && array_key_exists('from', $value) && $value['from'] == '' && array_key_exists('to', $value) && $value['to'] == '') {
                                continue;
                            }
                            if ($log->type == 'company_amount' and $value and is_array($value) and array_key_exists('from', $value) and array_key_exists('to', $value)) {
                                if ($field_name == 'max_participant') {
                                    $value['from'] = max($value['from'], 0);
                                    $value['to'] = max($value['to'], 0);
                                }
                            }
                            if ($value && is_array($value) && array_key_exists('from', $value) && array_key_exists('to', $value)) {
                                if ($value['from'] == $value['to']) { //not changed (same values, no need to show)
                                    continue;
                                }
                            }
                            $fromValue = '';
                            $toValue = '';
                            if (is_array($value)) {
                                $fromValue = $value['from'];
                                $toValue = $value['to'];
                            } else {
                                if ($field_name == 'date' || strpos($field_name, 'date') !== false) {
                                    $value = FFM::date($value);
                                }
                                $avoid = ['merchant_id', 'user_id'];
                                if (in_array($field_name, $avoid)) {
                                    continue;
                                }
                                $dollar_fields = ['amount'];
                                if (in_array($field_name, $dollar_fields)) {
                                    $value = FFM::dollar($value);
                                }
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name) .'</strong>: '.$value.'<br>';
                                continue;
                            }
                            if ($field_name == 'first_payment') {
                               $field_name = 'first_payment_date';
                           }
                            $changes .= '<ul style="padding-left: 10px; margin: 0;"><li>';
                            if ($field_name == 'password') {
                                $effectedUser = User::withTrashed()->where('id', $log->object_id)->first();
                                $changes .= $effectedUser->name."'s Password Changed";
                                continue;
                            } elseif ($field_name == 'pay_off') {
                                $effectedUser = Merchant::withTrashed()->where('id', $log->object_id)->first();
                                if ($value['to'] == 1) {
                                    $changes .= $effectedUser->name.' Payoff Requested';
                                    continue;
                                } else {
                                    $changes .= $effectedUser->name.' Payoff had Reset';
                                    continue; 
                                }
                            }
                            if ($field_name == 'first_payment') {
                                $field_name = 'first_payment_date';
                            }
                            if ($field_name == 'date' || strpos($field_name, 'date') !== false) {
                                if ($value['from'] == '1970-01-01') {
                                    $value['from'] = '';
                                } elseif ($value['to'] == '1970-01-01') {
                                    $value['to'] = '';
                                }
                                $fromValue = FFM::date($value['from']);
                                $toValue = FFM::date($value['to']);
                            }
                            if ($field_name == 'created_at' || $field_name == 'approved_at') {
                                $fromValue = ($value['from'] != '') ? FFM::datetime($value['from']) : '';
                                $toValue = ($value['to'] != '') ? FFM::datetime($value['to']) : '';
                            }
                            if ($field_name == 'account_number') {
                                $fromValue = FFM::mask_cc($value['from']);
                                $toValue = FFM::mask_cc($value['to']);
                            }
                            
                            $dollar_fields = ['amount', 'payment', 'commission_amount', 'funded', 'payment_amount', 'rtr', 'liquidity', 'under_writing_fee', 'up_sell_commission', 'invest_rtr', 'paid_mgmnt_fee', 'max_participant_fund','final_participant_share'];
                            if (in_array($field_name, $dollar_fields)) {
                                $fromValue = FFM::dollar($fromValue);
                                $toValue = FFM::dollar($toValue);
                            }
                            $percentageFields = ['commission', 'm_mgmnt_fee', 'management_fee', 'm_syndication_fee', 'syndication_fee', 'underwriting_fee', 'experian_intelliscore', 'experian_financial_score', 'origination_fee', 'commission_per', 'syndication_fee_percentage', 'mgmnt_fee', 'brokerage', 'complete_per', 'max_participant_fund_per', 'under_writing_fee_per', 'interest_rate', 'withhold_percentage', 'up_sell_commission_per', 'complete_percentage', 'agent_fee_percentage'];
                            if (in_array($field_name, $percentageFields)) {
                                $fromValue = ($fromValue != '') ? FFM::percent($fromValue) : '';
                                $toValue = ($toValue != '') ? FFM::percent($toValue) : '';
                                if ($fromValue == $toValue) { continue;}
                            }
                            $removeId = ['source_id', 'state_id', 'sub_status_id', 'lender_id', 'industry_id', 'investor_ids', 'investor_id', 'payment_pause_id'];
                            if (in_array($field_name, $removeId)) {
                                $field_name = str_replace('_ids', '', $field_name);
                                $field_name = str_replace('_id', '', $field_name);
                            }
                            if ($field_name == 'pmnts') {
                                $field_name = 'Number Of Payments';
                            }
                            if ($field_name == 's_prepaid_status') {
                                $field_name = 'syndication_prepaid_status';
                            }
                            if ($field_name == 'zip_code') {
                                $fromValue = ($fromValue != '') ? (int)$fromValue : '';
                                $toValue = ($toValue != '') ? (int)$toValue : ''; 
                            }
                            if ($field_name == 'up_sell_commission_per') {
                                $field_name = 'up_sell_commission_percentage';
                            }
                            if ($log->type == 'company_amount') {
                                if ($field_name == 'max_participant') {
                                    $fromValue = FFM::dollar(sprintf("%.2f", max($fromValue, 0)));
                                    $toValue = FFM::dollar(sprintf("%.2f", max($toValue, 0)));    
                                }
                            }
                            if ($field_name == 'mgmnt_fee') {
                                $field_name = 'management_fee_percentage';
                            }
                            if ($log->type == 'merchant') {
                                if ($field_name == 'name') {
                                    $field_name = 'Business Name';
                                }
                                if ($field_name == 'm_syndication_fee') {
                                    $field_name = 'syndication_fee';
                                }
                                if ($field_name == 'max_participant_fund_per') {
                                    $field_name = 'maximum_participant_fund_percentage';
                                }
                                if ($field_name == 'max_participant_fund') {
                                    $field_name = 'maximum_participant';
                                }
                                if ($field_name == 'marketplace_status') {
                                    $field_name = 'marketplace';
                                }
                                if ($field_name == 'sub_status') {
                                    $field_name = 'status';
                                }
                                if ($field_name == 'underwriting_status' || $field_name == 'merchant_syndication_prepaid_status') {
                                    $fromValue = ($fromValue) ? $fromValue : '';
                                    $toValue = ($toValue) ? $toValue : '';
                                }
                                if ($field_name == 'm_s_prepaid_status' || $field_name == 'merchant_syndication_prepaid_status') {
                                    if ($fromValue == 'RTR') { $fromValue = 'On RTR';} elseif ($fromValue == 'Amount') { $fromValue = 'On Funding Amount';}
                                    if ($toValue == 'RTR') { $toValue = 'On RTR';} elseif ($toValue == 'Amount') { $toValue = 'On Funding Amount';}
                                }
                                if ($field_name == 'underwriting_fee') {
                                    $field_name = 'underwriting_fee (%)';
                                }
                                if ($field_name == 'syndication_fee') {
                                    $field_name = 'syndication_fee (%)';
                                }
                                if ($field_name == 'management_fee') {
                                    $field_name = 'management_fee (%)';
                                }
                                if ($field_name == 'origination_fee') {
                                    $field_name = 'origination_fee (%)';
                                }
                                if ($field_name == 'factor_rate') {
                                    $fromValue = round($fromValue, 4);
                                    $toValue = round($toValue, 4);
                                }
                                if ($field_name == 'actual_payment_left') {
                                    $fromValue = ($fromValue) ? $fromValue : 'None';
                                    $toValue = ($toValue) ? $toValue : 'None';
                                }
                            }
                            if ($log->type == 'merchant_user') {
                                if ($field_name == 'complete_per') {
                                    $field_name = 'complete_percentage';
                                }
                                if ($field_name == 'paid_mgmnt_fee') {
                                    $field_name = 'paid_management_fee_amount';
                                }
                                if ($field_name == 'pre_paid') {
                                    $field_name = 'syndication_fee';
                                    $fromValue = ($fromValue != '') ? FFM::dollar($fromValue) : '';
                                    $toValue = ($toValue != '') ? FFM::dollar($toValue) : '';
                                }
                            }
                            if ($field_name == 'under_writing_fee_per') {
                                $field_name = 'under_writing_fee_percentage';
                            }
                            if ($field_name == 'underwriting_status' || $field_name == 'merchant_syndication_prepaid_status') {
                                $fromValue = ($fromValue == 0) ? "" : $fromValue;
                                $toValue = ($toValue == 0) ? "" : $toValue;
                            }
                            $fromValue = (is_float($fromValue)) ? round($fromValue, 2) : $fromValue;
                            $toValue = (is_float($toValue)) ? round($toValue, 2) : $toValue;
                            if ($fromValue == '') {
                                $fromValue = 'Empty';
                            }
                            if ($toValue == '') {
                                $toValue = 'Empty';
                            }
                            if ($log->type == 'user') {
                                $field_name = is_array($field_name) ? json_encode($field_name) : $field_name;
                                $fromValue = is_array($fromValue) ? json_encode($fromValue) : $fromValue;
                                $toValue = is_array($toValue) ? json_encode($toValue) : $toValue;
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> changed from <span class="orange">"'.($fromValue).'"</span>  to <span class="blue">"'.($toValue).'"</span><br>';
                            } elseif ($log->type == 'payment_pause' || $log->type == 'payment_resume') {
                                $log->type = 'payment_resume';
                                if ($field_name == 'resumed_by') {
                                    $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : <span class="blue">'.($toValue).'</span><br>';
                                }
                                if ($field_name == 'resumed_at') {
                                    $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : <span class="blue">'.FFM::datetime($toValue).'</span><br>';
                                }
                            } else {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> changed from <span class="orange">"'.($fromValue).'"</span>  to <span class="blue">"'.($toValue).'"</span><br>';
                            }
                            $changes .= '</li></ul>';
                        } else {
                            $omit = ['merchant_id', 'creator_id', 'id', 'user_id', 'company_id', 'encrypted_id', 'merchant_id_m', 'is_profit_adjustment_added', 'updated_at', 'active_status', 'last_status_updated_date', 'batch', 'transaction_type', 'merchant_permission', 'creator', 'overpayment_status', 'term_id', 'model', 'model_id', 'merordernumber', 'is_fees', 'ach_request_id', 'entity1', 'entity2', 'entity3', 'entity4', 'entity5', 'entity6', 'source_from', 'revert_id', 'agent_fee_applied', 'crm_id', 'mail_send_status', 'move_status', 'notify_investors', 'marketplace_permission', 'position', 'annual_revenue', 'logo', 'payment_schedule_id', 'is_ach', 'monthly_revenue', 'share', 'paid_participant', 'actual_paid_participant_ishare', 'paid_profit', 'paid_principal', 'total_agent_fee', 'old_factor_rate', 'paid_count', 'annualized_rate', 'final_participant_share'];
                            if ($log->type != 'company') {
                                array_push($omit, 'company_status');
                            }
                            if ($log->type == 'user' || $log->type == 'investor') {
                                if (($key = array_search('active_status', $omit)) !== false) {
                                    unset($omit[$key]);
                                }
                                array_push($omit, 'underwriting_status');
                            }
                            if ($log->type !== 'ach_payment' && $log->type != 'payment') {
                                array_push($omit, 'status');
                            }
                            if ($log->type == 'ach_request' || $log->type == 'investor_ach_request') {
                                array_push($omit, 'response');
                            }
                            if ($log->type == 'payment_resume') {
                                array_push($omit, 'paused_by', 'paused_at', 'created_at');
                            }
                            if ($log->type == 'payment') {
                                array_push($omit, 'investor_ids');
                            }
                            if ($log->type == 'merchant_user') {
                                array_push($omit, 'investor_name', 'merchant_name', 'paid_syndication_fee', 'syndication_fee');
                            }
                            if ($log->type == 'merchant') {
                                array_push($omit, 'balance', 'liquidity', 'paid_count', 'notification_email');
                            }
                            if ($log->type == 'user_merchant') {
                                array_push($omit, 'name');
                            }
                            if (in_array($field_name, $omit) || $value == '' || $value == "null") {
                                continue;
                            }
                            if ($field_name == 'last_rcode') {
                                $value = ($value != 0 && $value != '') ? $value : '';
                                if ($value == '') {
                                    continue;
                                }
                            }
                            $dollar_fields = ['payment', 'amount', 'rtr', 'payment_amount', 'funded', 'invest_rtr', 'commission_amount', 'under_writing_fee', 'paid_mgmnt_fee', 'max_participant_fund', 'up_sell_commission','final_participant_share', 'monthly_revenue', 'liquidity'];
                            if (in_array($field_name, $dollar_fields)) {
                                $value = FFM::dollar($value);
                            }
                            $removeId = ['source_id', 'state_id', 'sub_status_id', 'lender_id', 'industry_id', 'investor_ids', 'investor_id'];
                            if (in_array($field_name, $removeId)) {
                                $field_name = str_replace('_ids', '', $field_name);
                                $field_name = str_replace('_id', '', $field_name);
                            }
                            $percentageFields = ['commission', 'm_mgmnt_fee', 'management_fee', 'm_syndication_fee', 'syndication_fee', 'underwriting_fee', 'experian_intelliscore', 'experian_financial_score', 'origination_fee', 'commission_per', 'syndication_fee_percentage', 'mgmnt_fee', 'brokerage', 'complete_per', 'max_participant_fund_per', 'under_writing_fee_per', 'complete_percentage', 'interest_rate', 'withhold_percentage', 'global_syndication', 'up_sell_commission_per', 'complete_percentage', 'agent_fee_percentage'];
                            if (in_array($field_name, $percentageFields)) {
                                $value = FFM::percent($value);
                            }
                            if ($field_name == 'account_number') {
                                $value = FFM::mask_cc($value);
                            }
                            if ($field_name == 'pmnts') {
                                $field_name = 'Number Of Payments';
                            }
                            if ($field_name == 'mgmnt_fee') {
                                $field_name = 'Management fee percentage';
                            }
                            if ($field_name == 'commission_per') {
                                $field_name = 'commission_percentage';
                            }
                            if ($field_name == 'up_sell_commission_per') {
                                $field_name = 'up_sell_commission_percentage';
                            }
                            if ($field_name == 'zip_code') {
                                $value = (int)$value;
                            }
                            if ($log->type == 'merchant') {
                                if ($field_name == 'name') {
                                    $field_name = 'Business Name';
                                }
                                if ($field_name == 'm_syndication_fee') {
                                    $field_name = 'syndication_fee';
                                }
                                if ($field_name == 'max_participant_fund_per') {
                                    $field_name = 'maximum_participant_fund_percentage';
                                }
                                if ($field_name == 'max_participant_fund') {
                                    $field_name = 'maximum_participant';
                                }
                                if ($field_name == 'marketplace_status') {
                                    $field_name = 'marketplace';
                                }
                                if ($field_name == 'sub_status') {
                                    $field_name = 'status';
                                }
                                if ($field_name == 'm_s_prepaid_status' || $field_name == 'merchant_syndication_prepaid_status') {
                                    if ($value == 'RTR') { $value = 'On RTR';} elseif ($value == 'Amount') { $value = 'On Funding Amount';}
                                }
                                if ($field_name == 'underwriting_fee') {
                                    $field_name = 'underwriting_fee (%)';
                                }
                                if ($field_name == 'syndication_fee') {
                                    $field_name = 'syndication_fee (%)';
                                }
                                if ($field_name == 'management_fee') {
                                    $field_name = 'management_fee (%)';
                                }
                                if ($field_name == 'origination_fee') {
                                    $field_name = 'origination_fee (%)';
                                }
                                if ($field_name == 'factor_rate') {
                                    $value = round($value, 4);
                                }
                            }
                            if ($log->type == 'merchant_user') {
                                if ($field_name == 'complete_per') {
                                    $field_name = 'complete_percentage';
                                }
                                if ($field_name == 'paid_mgmnt_fee') {
                                    $field_name = 'paid_management_fee_amount';
                                }
                                if ($field_name == 'pre_paid') {
                                    $field_name = 'syndication_fee';
                                    $value = ($value != '') ? FFM::dollar($value) : '';
                                }
                            }
                            if ($log->type == 'company_amount') {
                                if ($field_name == 'max_participant') {
                                    $value = ($value < 0) ? 0 : $value;
                                    $value = FFM::dollar(sprintf("%.2f", $value));
                                }
                            }
                            if ($field_name == 'under_writing_fee_per') {
                                $field_name = 'under_writing_fee_percentage';
                            }
                            if ($field_name == 'm_mgmnt_fee') {
                                $field_name = 'management_fee';
                            }
                            if ($field_name == 'm_syndication_fee') {
                                $field_name = 'syndication_fee';
                            }
                            if ($log->type == 'payment') {
                                if ($field_name == 'status') {
                                    $status = ParticipentPayment::statusOptions();
                                    if (array_key_exists($value, $status)) {
                                        $value = $status[$value];
                                    }
                                }
                            }
                            if (strpos(strtolower($field_name), 'date') !== false) {
                                $toFormat = (strpos($value, ':') !== false) ? 'm/d/Y h:i:s A' : FFM::defaultDateFormat('db');
                                try {
                                    $value = Carbon::parse($value)->format($toFormat);
                                } catch (InvalidFormatException $e) {
                                }
                            }
                            if ($log->type == 'ach_request') {
                                if ($field_name == 'ach_status') {
                                    $status = AchRequest::achStatusOptions();
                                    if ($value != '' && array_key_exists($value, $status)) {
                                        $value = $status[$value];
                                    }
                                }
                                if ($field_name == 'ach_request_status') {
                                    $status = AchRequest::achRequestStatusOptions();
                                    if ($value != '' && array_key_exists($value, $status)) {
                                        $value = $status[$value];
                                    }
                                }
                                if ($field_name == 'payment_status') {
                                    $status = AchRequest::paymentStatusOptions();
                                    if ($value != '' && array_key_exists($value, $status)) {
                                        $value = $status[$value];
                                    }
                                }
                            }
                            if ($field_name == 's_prepaid_status' && $value != '') {
                                $field_name = 'syndication_prepaid_status';
                            }
                            $value = (is_float($value)) ? round($value, 2) : $value;
                            if (is_array($value)) {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.json_encode($value).'<br>';
                            } elseif ($field_name == 'created_at' or $field_name == 'updated_at' or $field_name == 'deleted_at' or $field_name == 'paused_at' or $field_name == 'resumed_at' or $field_name == 'approved_at') {
                                if ($type == 'merchant_note') {
                                    if (strtotime($value)) {
                                        $value = \FFM::datetime($value);
                                    }
                                    $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.$value.'<br>';
                                } else {
                                    $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.\FFM::datetime($value).'<br>';
                                }
                            } elseif ($field_name == 'login_date') {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.\FFM::datetime($value).'<br>';
                            } else {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.str_replace('.000000Z', '', $value).'<br>';
                            }
                        }
                    }
                }
                $data = [0 => $log->user->name, 1 => FFM::datetime($log->created_at), 2 => $changes, 5 => UserActivityLog::prettyStatus($log->action)];
                if ($type == 'investor_transaction') {
                    $totalAmount += $amount;
                    $data[3] = FFM::dollar($amount);
                } else {
                    $data[4] = UserActivityLog::logTypePrettyStatus($log->type);
                }
                ksort($data);
                $rows[] = array_values($data);
            }
        }
        if ($type == 'investor_transaction') {
            $rows[] = ['<strong>Total</strong>', '', '', FFM::dollar($totalAmount), ''];
        }


         return ['sEcho' => 0, 'recordsTotal' => $total_records, 'recordsFiltered' => $total_records, 'aaData' => $rows];


    }
    public function userActivityLog($filter=[])
    {
        $data_id = $filter['data_id'];
        $type = $filter['type'];
        $user_id = $filter['user_id'];
        $action = $filter['action'];
        $search_type = $filter['search_type'];
        $from_date = $filter['from_date'];
        $to_date = $filter['to_date'];
        $objectId = $filter['objectId'];
        $search = $filter['search'];
        $start = $filter['start'];
        $limit = $filter['limit'];
        $order_col = $filter['order_col'];
        $order_by = $filter['order_by'];
        $action_user = $filter['action_user'];

        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $subinvestors = [];
        $submerchants = [];
        if (empty($permission)) {
            $investor = $this->role->allInvestorsWithTrashed();
            $subinvestors = $investor->whereIn('company', $userId);
            $subinvestors = $subinvestors->pluck('id')->toArray();
            $submerchants = MerchantUser::whereIn('user_id', $subinvestors)->groupBy('merchant_id')->pluck('merchant_id')->toArray();
            $otherInvestors = $investor->whereNotIn('company', $userId);
            $otherInvestors = $otherInvestors->pluck('id')->toArray();
        }
        $query = UserActivityLog::where('object_id', '>', 0)->join('users', 'users.id', '=', 'user_activity_logs.user_id');
        if (empty($permission)) {
            if (Auth::user()->hasRole(['company'])) {
                $query->where(function($inner) use ($userId, $subinvestors, $submerchants, $otherInvestors) {
                    $inner->orWhere('users.company', $userId);
                    $inner->orWhere('user_activity_logs.user_id', $userId);
                    $inner->orWhereIn('user_activity_logs.investor_id', $subinvestors);
                    $inner->orWhereIn('user_activity_logs.merchant_id', $submerchants)->whereNotIn('user_activity_logs.investor_id', $otherInvestors);
                    $inner->orWhereIn('user_activity_logs.merchant_id', $submerchants)->whereNull('user_activity_logs.investor_id');
                });
            }
        }
        if (! empty($type)) {
            $query->where('user_activity_logs.type', $type);
        } elseif (empty($type)) {
            $query->where('user_activity_logs.type', '!=', 'investor_transaction');
        }
        if (! empty($user_id)) {
            $query->where('user_activity_logs.user_id', $user_id);
        }
        if (! empty($objectId) and $type == 'investor_transaction') {
            $transIds = InvestorTransaction::where('investor_id', $objectId)->pluck('id')->toArray();
            $query->where(function ($inner) use ($transIds, $objectId) {
                $inner->orWhere(DB::raw('JSON_UNQUOTE(detail-> "$.investor_id")'), $objectId);
                $inner->orWhereIn('object_id', $transIds);
            });
        }
        if (! empty($action)) {
            $query->where('user_activity_logs.action', $action);
        }
        if (! empty($from_date)) {
            $from_date = ET_To_UTC_Time($from_date.' 00:00', 'datetime');
            $query->where('user_activity_logs.created_at', '>=', $from_date);
        }
        if (! empty($to_date)) {
            $to_date = ET_To_UTC_Time($to_date.' 23:59', 'datetime');
            $query->where('user_activity_logs.created_at', '<=', $to_date);
        }
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('name', 'like', '%'.$search.'%')->orWhereRaw("DATE_FORMAT(user_activity_logs.created_at,'%m-%d-%Y') like ?", ["%$search%"])->orWhere('email', 'like', '%'.$search.'%')->orWhere('user_activity_logs.type', 'like', '%'.$search.'%')->orWhere(DB::raw('REPLACE(user_activity_logs.type, "_", " ")'), 'like', '%'.$search.'%')->orWhere('user_activity_logs.action', 'like', '%'.$search.'%')->orWhere('user_activity_logs.detail', 'like', '%'.$search.'%');
                $inner->orWhereIn('merchant_id', function($query) use ($search){
                    $query->select('id')
                    ->from('merchants')
                    ->where('name', 'like', '%'.$search.'%');
                });
                $inner->orWhereIn('investor_id', function($query) use ($search){
                    $query->select('id')
                    ->from('users')
                    ->where('name', 'like', '%'.$search.'%');
                });
            });
        }
        if (! empty($order_col)) {
            if ($order_col == 0) {
                $sub_query = User::withTrashed()->select(DB::raw('id AS user_id'), 'name');
                $query->join(DB::raw('('.$sub_query->toSql().') AS sub_query'), 'sub_query.user_id', '=', 'user_activity_logs.user_id');
                $query->orderBy('sub_query.name', $order_by);
            } elseif ($order_col == 1) {
                $query->orderBy('user_activity_logs.created_at', $order_by);
            } elseif ($order_col == 2) {
                $query->orderBy('user_activity_logs.detail', $order_by);
            } elseif ($order_col == 3) {
                $query->orderBy('user_activity_logs.type', $order_by);
            } elseif ($order_col == 4) {
                $query->orderBy('user_activity_logs.action', $order_by);
            }
        }
        $query->select('user_activity_logs.*');
        $query->orderByDesc('user_activity_logs.id');
        if (! empty($action_user)) {
            $action_user = explode('-', $action_user);
            $action_user_type = $action_user[1];
            $action_user_id = $action_user[0];
            if ($action_user_type) {
                $log = $query->get();
                if ($action_user_type == 'investor') {
                    $query->where(function ($inner) use ($action_user_id) {
                        $inner->orWhere(DB::raw('JSON_UNQUOTE(detail-> "$.investor_id")'), $action_user_id);
                        $inner->orWhere(DB::raw('JSON_UNQUOTE(detail-> "$.user_id")'), $action_user_id);
                        $inner->orWhere('user_activity_logs.investor_id', $action_user_id);
                    });
                } elseif ($action_user_type == 'merchant') {
                    $query->where(function ($inner) use ($action_user_id) {
                        $inner->orWhere(DB::raw('JSON_UNQUOTE(detail-> "$.merchant_id")'), $action_user_id);
                        $inner->orWhere('user_activity_logs.merchant_id', $action_user_id);
                    });
                }
            }
        }
        $total_records = $query->count();
        $logs = $query->limit($limit)->offset($start)->get();
        $rows = [];
        $totalAmount = 0;
        if (count($logs) > 0) {
            foreach ($logs as $log) {
                $amount = 0;
                $changes = '';
                try {
                    $details = json_decode($log->detail, true);
                } catch (\ErrorException $e) {
                    $details = [];
                }
                $object = false;
                $original_details = [];
                if ($log->action != 'updated') {
                    ksort($details);
                }
                if (is_array($details) and count($details) > 0) {
                    $changes = $this->appendParentPrefix($log);
                    foreach ($details as $field_name => $value) {
                        if ($log->action == 'updated') {
                            $field_name = $this->properFieldName($object, $field_name);
                            if ($field_name == 'updated_at' or $field_name == 'deleted_at' or $field_name == 'activation') {
                                continue;
                            }
                            $omit = ['merchant_id', 'creator_id', 'id', 'user_id', 'company_id', 'last_status_updated_date', 'creator', 'merchant_permission', 'term_id', 'is_fees', 'ach_request_id', 'fee_type', 'model', 'model_id', 'auth_code', 'source_from', 'revert_id', 'position', 'annual_revenue', 'logo', 'liquidity_exclude', 'current_merchant_id', 'final_participant_share', 'old_factor_rate', 'paid_count', 'annualized_rate', 'last_rcode', 'auto_invest', 'paid_participant_ishare', 'actual_paid_participant_ishare'];
                            if ($log->type == 'investor_ach_request') {
                                array_push($omit, 'investor');
                            }
                            if ($log->type != 'company') {
                                array_push($omit, 'company_status');
                            }
                            if ($log->type == 'investor_ach_request') {
                                array_push($omit, 'investor', 'transaction_id');
                            }
                            if ($log->type == 'payment') {
                                array_push($omit, 'transaction_type', 'investor_ids');
                            }
                            if ($log->type == 'user' || $log->type == 'investor') {
                                array_push($omit, 'underwriting_status');
                            }
                            if ($log->type == 'velocity_fee') {
                                array_push($omit, 'order_id');
                            }
                            if ($log->type == 'bank_account') {
                                array_push($omit, 'investor');
                            }
                            if (in_array($field_name, $omit)) {
                                continue;
                            }
                            if ($value && is_array($value) && array_key_exists('from', $value) && $value['from'] == '' && array_key_exists('to', $value) && $value['to'] == '') {
                                continue;
                            }
                            if ($log->type == 'company_amount' and $value and is_array($value) and array_key_exists('from', $value) and array_key_exists('to', $value)) {
                                if ($field_name == 'max_participant') {
                                    $entry['from'] = max($value['from'], 0);
                                    $entry['to'] = max($value['to'], 0);
                                }
                            }
                            if ($value && is_array($value) && array_key_exists('from', $value) && array_key_exists('to', $value)) {
                                if ($value['from'] == $value['to']) { //not changed (same values, no need to show)
                                    continue;
                                }
                            }

                            $removeId = ['source_id', 'state_id', 'sub_status_id', 'lender_id', 'industry_id', 'investor_ids', 'investor_id'];
                            if (in_array($field_name, $removeId)) {
                                $field_name = str_replace('_ids', '', $field_name);
                                $field_name = str_replace('_id', '', $field_name);
                            }
                            $fromValue = '';
                            $toValue = '';
                            if (is_array($value)) {
                                $fromValue = $value['from'];
                                $toValue = $value['to'];
                            } else {
                                if ($field_name == 'date' || strpos($field_name, 'date') !== false) {
                                    $value = FFM::date($value);
                                }
                                $avoid = ['merchant_id', 'user_id'];
                                if (in_array($field_name, $avoid)) {
                                    continue;
                                }
                                $dollar_fields = ['amount'];
                                if (in_array($field_name, $dollar_fields)) {
                                    $value = FFM::dollar($value);
                                }
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name) .'</strong>: '.$value.'<br>';
                                continue;
                            }
                            $changes .= '<ul style="padding-left: 10px; margin: 0;"><li>';
                            if ($field_name == 'password') {
                                $effectedUser = User::withTrashed()->where('id', $log->object_id)->first();
                                $changes .= $effectedUser->name."'s Password Changed";
                                continue;
                            } elseif ($field_name == 'pay_off') {
                                $effectedUser = Merchant::withTrashed()->where('id', $log->object_id)->first();
                                if ($value['to'] == 1) {
                                    $changes .= $effectedUser->name. ' Payoff Requested';
                                    continue;
                                } else {
                                    $changes .= $effectedUser->name.' Payoff had Reset';
                                    continue; 
                                }
                            }
                            if ($field_name == 'first_payment') {
                                $field_name = 'first_payment_date';
                            }
                            if ($field_name == 'date' || strpos($field_name, 'date') !== false) {
                                if ($value['from'] == '1970-01-01') {
                                    $value['from'] = '';
                                } elseif ($value['to'] == '1970-01-01') {
                                    $value['to'] = '';
                                }
                                $fromValue = FFM::date($value['from']);
                                $toValue = FFM::date($value['to']);
                            }
                            if ($field_name == 'created_at' || $field_name == 'approved_at') {
                                $fromValue = ($value['from'] != '') ? FFM::datetime($value['from']) : '';
                                $toValue = ($value['to'] != '') ? FFM::datetime($value['to']) : '';
                            }
                            if ($field_name == 'account_number') {
                                $fromValue = FFM::mask_cc($value['from']);
                                $toValue = FFM::mask_cc($value['to']);
                            }
                            $dollar_fields = ['amount', 'payment', 'commission_amount', 'funded', 'payment_amount', 'rtr', 'liquidity', 'under_writing_fee', 'up_sell_commission', 'invest_rtr', 'paid_mgmnt_fee', 'max_participant_fund','final_participant_share'];
                            if (in_array($field_name, $dollar_fields)) {
                                $fromValue = FFM::dollar($fromValue);
                                $toValue = FFM::dollar($toValue);
                                if ($fromValue == $toValue) { continue;}
                            }
                            $percentageFields = ['commission', 'm_mgmnt_fee', 'management_fee', 'm_syndication_fee', 'syndication_fee', 'underwriting_fee', 'experian_intelliscore', 'experian_financial_score', 'origination_fee', 'commission_per', 'syndication_fee_percentage', 'mgmnt_fee', 'brokerage', 'complete_per', 'max_participant_fund_per', 'under_writing_fee_per', 'interest_rate', 'withhold_percentage', 'up_sell_commission_per', 'complete_percentage', 'agent_fee_percentage'];
                            if (in_array($field_name, $percentageFields)) {
                                $fromValue = ($fromValue != '') ? FFM::percent($fromValue) : '';
                                $toValue = ($toValue != '') ? FFM::percent($toValue) : '';
                                if ($fromValue == $toValue) { continue;}
                            }
                            if ($field_name == 'last_rcode') {
                                $fromValue = ($fromValue != 0 && $fromValue != '') ? $fromValue : '';
                                $toValue = ($toValue != 0 && $toValue != '') ? $toValue : '';
                            }
                            if ($field_name == 'pmnts') {
                                $field_name = 'Number Of Payments';
                            }
                            if ($field_name == 'mgmnt_fee') {
                                $field_name = 'Management fee percentage';
                            }
                            if ($field_name == 'commission_per') {
                                $field_name = 'commission_percentage';
                            }
                            if ($field_name == 'up_sell_commission_per') {
                                $field_name = 'up_sell_commission_percentage';
                            }
                            if ($field_name == 's_prepaid_status') {
                                $field_name = 'syndication_prepaid_status';
                                if ($fromValue == 'On Amount') { $fromValue = 'On Funding Amount';}
                                if ($toValue == 'On Amount') { $toValue = 'On Funding Amount';}
                            }
                            if ($field_name == 'zip_code') {
                                $fromValue = ($fromValue != '') ? (int)$fromValue : '';
                                $toValue = ($toValue != '') ? (int)$toValue : ''; 
                            }
                            if ($log->type == 'company_amount') {
                                if ($field_name == 'max_participant') {
                                    $fromValue = FFM::dollar(sprintf("%.2f", max($fromValue, 0)));
                                    $toValue = FFM::dollar(sprintf("%.2f", max($toValue, 0)));    
                                }
                            }
                            if ($log->type == 'merchant') {
                                if ($field_name == 'name') {
                                    $field_name = 'Business Name';
                                }
                                if ($field_name == 'm_syndication_fee') {
                                    $field_name = 'syndication_fee';
                                }
                                if ($field_name == 'max_participant_fund_per') {
                                    $field_name = 'maximum_participant_fund_percentage';
                                }
                                if ($field_name == 'max_participant_fund') {
                                    $field_name = 'maximum_participant';
                                }
                                if ($field_name == 'marketplace_status') {
                                    $field_name = 'marketplace';
                                }
                                if ($field_name == 'sub_status') {
                                    $field_name = 'status';
                                }
                                if ($field_name == 'underwriting_status' || $field_name == 'merchant_syndication_prepaid_status') {
                                    $fromValue = ($fromValue) ? $fromValue : '';
                                    $toValue = ($toValue) ? $toValue : '';
                                }
                                if ($field_name == 'm_s_prepaid_status' || $field_name == 'merchant_syndication_prepaid_status') {
                                    if ($fromValue == 'RTR') { $fromValue = 'On RTR';} elseif ($fromValue == 'Amount') { $fromValue = 'On Funding Amount';}
                                    if ($toValue == 'RTR') { $toValue = 'On RTR';} elseif ($toValue == 'Amount') { $toValue = 'On Funding Amount';}
                                }
                                if ($field_name == 'underwriting_fee') {
                                    $field_name = 'underwriting_fee (%)';
                                }
                                if ($field_name == 'syndication_fee') {
                                    $field_name = 'syndication_fee (%)';
                                }
                                if ($field_name == 'management_fee') {
                                    $field_name = 'management_fee (%)';
                                }
                                if ($field_name == 'origination_fee') {
                                    $field_name = 'origination_fee (%)';
                                }
                                if ($field_name == 'factor_rate') {
                                    $fromValue = round($fromValue, 4);
                                    $toValue = round($toValue, 4);
                                }
                                if ($field_name == 'actual_payment_left') {
                                    $fromValue = ($fromValue) ? $fromValue : 'None';
                                    $toValue = ($toValue) ? $toValue : 'None';
                                }
                            }
                            if ($field_name == 'underwriting_status') {
                                $fromValue = ($fromValue) ? $fromValue : '';
                                $toValue = ($toValue) ? $toValue : '';
                            }
                            if ($log->type == 'merchant_user') {
                                if ($field_name == 'complete_per') {
                                    $field_name = 'complete_percentage';
                                }
                                if ($field_name == 'paid_mgmnt_fee') {
                                    $field_name = 'paid_management_fee_amount';
                                }
                                if ($field_name == 'pre_paid') {
                                    $field_name = 'syndication_fee';
                                    $fromValue = ($fromValue != '') ? FFM::dollar($fromValue) : '';
                                    $toValue = ($toValue != '') ? FFM::dollar($toValue) : '';
                                }
                            }
                            if ($field_name == 'under_writing_fee_per') {
                                $field_name = 'under_writing_fee_percentage';
                            }
                            if ($log->type == 'investor' || $log->type == 'user' || $log->type == 'lender') {
                                if ($field_name == 'global_syndication') {
                                    $field_name = 'syndication_fee';
                                    $fromValue = ($fromValue != '') ? FFM::percent($fromValue) : '';
                                    $toValue = ($toValue != '') ? FFM::percent($toValue) : '';    
                                }
                            }
                            if ($log->type == 'investor' || $log->type == 'user') {
                                if ($field_name == 'notification_recurence') {
                                    $field_name = 'payout_frequency';
                                }
                                if ($field_name == 'auto_generation') {
                                    $field_name = 'auto_generate_syndicate_report';
                                }
                                if ($field_name == 'label') {
                                    $field_name = 'auto_invest_collected_amount_from';
                                }
                                if ($field_name == 'funding_status') {
                                    $field_name = 'Enable Investment Portal';
                                }
                                if ($field_name == 'active_status') {
                                    $field_name = 'Enable / Disable';
                                }
                                if ($field_name == 'auto_syndicate_payment') {
                                    $field_name = 'auto_syndication_payment';
                                }
                                if ($field_name == 's_prepaid_status') {
                                    $field_name = 'syndication_prepaid_status';
                                } elseif ($field_name == 'notification_recurence') {
                                    $field_name = 'payout_frequency';
                                }
                                if ($field_name == 'underwriting_status') {
                                    $fromValue = ($fromValue) ? $fromValue : '';
                                    $toValue = ($toValue) ? $toValue : '';
                                }
                            }
                            $fromValue = (is_float($fromValue)) ? round($fromValue, 2) : $fromValue;
                            $toValue = (is_float($toValue)) ? round($toValue, 2) : $toValue;
                            if ($fromValue == '' || $fromValue == 'null') {
                                $fromValue = 'Empty';
                            }
                            if ($toValue == '' || $toValue == 'null') {
                                $toValue = 'Empty';
                            }
                            if ($log->type == 'user' || $log->type == 'lender' || $log->type == 'investor') {
                                $field_name = is_array($field_name) ? json_encode($field_name) : $field_name;
                                $fromValue = is_array($fromValue) ? json_encode($fromValue) : $fromValue;
                                $toValue = is_array($toValue) ? json_encode($toValue) : $toValue;
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> changed from <span class="orange">"'.($fromValue).'"</span>  to <span class="blue">"'.($toValue).'"</span><br>';
                            } elseif ($log->type == 'payment_pause' || $log->type == 'payment_resume') {
                                $log->type = 'payment_resume';
                                if ($field_name == 'resumed_by') {
                                    $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : <span class="blue">'.($toValue).'</span><br>';
                                }
                                if ($field_name == 'resumed_at') {
                                    $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : <span class="blue">'.FFM::datetime($toValue).'</span><br>';
                                }
                            } else {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> changed from <span class="orange">"'.($fromValue).'"</span>  to <span class="blue">"'.($toValue).'"</span><br>';
                            }
                            $changes .= '</li></ul>';
                        } else {
                            $omit = [
                                'merchant_id', 'creator_id', 'id', 'user_id', 'company_id', 'encrypted_id', 'merchant_id_m',
                                'is_profit_adjustment_added', 'updated_at', 'active_status', 'last_status_updated_date', 
                                'batch', 'transaction_type', 'creator', 'overpayment_status', 'term_id', 'model', 'model_id', 
                                'merordernumber', 'is_fees', 'ach_request_id', 'entity1', 'entity2', 'entity3', 'entity4', 
                                'entity5', 'entity6', 'source_from', 'revert_id', 'agent_fee_applied', 'crm_id', 
                                'mail_send_status', 'move_status', 'notify_investors', 'marketplace_permission', 'position', 
                                'annual_revenue', 'logo', 'payment_schedule_id', 'is_ach', 'monthly_revenue', 'liquidity_exclude', 
                                'final_participant_share', 'old_factor_rate', 'annualized_rate', 'share', 'paid_participant', 
                                'actual_paid_participant_ishare', 'paid_profit', 'paid_principal', 'total_agent_fee', 'crm_participant_id', 'roles', 'whole_portfolio'];
                            if ($log->type != 'company') {
                                array_push($omit, 'company_status', 'merchant_permission');
                            }
                            if ($log->type == 'company') {
                                array_push($omit, 'display_value', 'file_type', 'notification_recurence', 'auto_generation', 'whole_portfolio', 'auto_invest', 'funding_status', 'auto_syndicate_payment');
                            }
                            if ($log->type == 'lender') {
                                array_push($omit, 'brokerage', 'display_value', 'file_type', 'auto_generation', 'auto_invest', 'funding_status', 'auto_syndicate_payment', 'syndicate');
                            }
                            if ($log->type == 'user' || $log->type == 'investor') {
                                if (($key = array_search('active_status', $omit)) !== false) {
                                    unset($omit[$key]);
                                }
                                array_push($omit, 'underwriting_status', 'auto_generation', 'auto_invest', 'brokerage', 'funding_status', 'syndicate');
                            }
                            if ($log->type !== 'ach_payment' && $log->type != 'payment') {
                                array_push($omit, 'status');
                            }
                            if ($log->type == 'ach_request' || $log->type == 'investor_ach_request') {
                                array_push($omit, 'response');
                            }
                            if ($log->type == 'payment_resume') {
                                array_push($omit, 'paused_by', 'paused_at', 'created_at');
                            }
                            if ($log->type == 'payment') {
                                if ($log->investor_id && $field_name == 'agent_fee_percentage') {
                                    array_push($omit, 'agent_fee_percentage');
                                }
                                array_push($omit, 'investor_ids');
                            }
                            if ($log->type == 'bank_account' || $log->type == 'investor_ach_request' || $log->type == 'investor_transaction') {
                                array_push($omit, 'investor', 'investor_id');
                            }
                            if ($log->type == 'user' || $log->type == 'investor' || $log->type == 'lender' || $log->type == 'login' || $log->type == 'company' || $log->type == 'user_merchant') {
                                array_push($omit, 'name');
                            }
                            if ($log->type == 'merchant_user') {
                                array_push($omit, 'investor_name', 'merchant_name', 'paid_syndication_fee', 'syndication_fee');
                            }
                            if ($log->type == 'merchant') {
                                array_push($omit, 'balance', 'liquidity', 'paid_count', 'notification_email');
                            }
                            if (in_array($field_name, $omit) || trim($value) == '' || $value == "null") {
                                $original_details = collect($details)->map(function ($detail_value, $names) use ($omit) {
                                    if ($detail_value && ! in_array($names, $omit)) {
                                        return $detail_value;
                                    }
                                })->reject(function ($detail_value) {
                                    return empty($detail_value);
                                });
                                continue;
                            }
                            $dollar_fields = ['payment', 'amount', 'rtr', 'payment_amount', 'funded', 'invest_rtr', 'commission_amount', 'up_sell_commission', 'under_writing_fee', 'monthly_revenue', 'liquidity', 'max_participant_fund','final_participant_share', 'paid_mgmnt_fee'];
                            if (in_array($field_name, $dollar_fields)) {
                                $value = FFM::dollar($value);
                            }
                            $removeId = ['source_id', 'state_id', 'sub_status_id', 'lender_id', 'industry_id', 'investor_ids', 'investor_id'];
                            if (in_array($field_name, $removeId)) {
                                $field_name = str_replace('_ids', '', $field_name);
                                $field_name = str_replace('_id', '', $field_name);
                            }
                            $percentageFields = ['commission', 'm_mgmnt_fee', 'management_fee', 'm_syndication_fee', 'syndication_fee', 'underwriting_fee', 'experian_intelliscore', 'experian_financial_score', 'origination_fee', 'commission_per', 'syndication_fee_percentage', 'mgmnt_fee', 'brokerage', 'complete_per', 'max_participant_fund_per', 'under_writing_fee_per', 'complete_percentage', 'interest_rate', 'withhold_percentage', 'global_syndication', 'up_sell_commission_per', 'complete_percentage', 'agent_fee_percentage'];
                            if (in_array($field_name, $percentageFields) && $value != '') {
                                $value = FFM::percent($value);
                            }
                            if ($field_name == 'last_rcode') {
                                $value = ($value != 0 && $value != '') ? $value : '';
                                if ($value == '') {
                                    continue;
                                }
                            }
                            if ($field_name == 'account_number' || $field_name == 'account_no') {
                                $value = FFM::mask_cc($value);
                            }
                            if ($field_name == 'pmnts') {
                                $field_name = 'Number Of Payments';
                            }
                            if ($field_name == 'mgmnt_fee') {
                                $field_name = 'Management fee percentage';
                            }
                            if ($field_name == 'commission_per') {
                                $field_name = 'commission_percentage';
                            }
                            if ($field_name == 'up_sell_commission_per') {
                                $field_name = 'up_sell_commission_percentage';
                            }
                            if ($field_name == 'zip_code') {
                                $value = (int)$value;
                            }
                            if ($log->type == 'merchant') {
                                if ($field_name == 'name') {
                                    $field_name = 'Business Name';
                                }
                                if ($field_name == 'm_syndication_fee') {
                                    $field_name = 'syndication_fee';
                                }
                                if ($field_name == 'max_participant_fund_per') {
                                    $field_name = 'maximum_participant_fund_percentage';
                                }
                                if ($field_name == 'max_participant_fund') {
                                    $field_name = 'maximum_participant';
                                }
                                if ($field_name == 'marketplace_status') {
                                    $field_name = 'marketplace';
                                }
                                if ($field_name == 'underwriting_status') {
                                    $value = ($value) ? $value : '';
                                }
                                if ($field_name == 'sub_status') {
                                    $field_name = 'status';
                                }
                                if ($field_name == 'm_s_prepaid_status' || $field_name == 'merchant_syndication_prepaid_status') {
                                    if ($value == 'RTR') { $value = 'On RTR';} elseif ($value == 'Amount') { $value = 'On Funding Amount';}
                                }
                                if ($field_name == 'underwriting_fee') {
                                    $field_name = 'underwriting_fee (%)';
                                }
                                if ($field_name == 'syndication_fee') {
                                    $field_name = 'syndication_fee (%)';
                                }
                                if ($field_name == 'management_fee') {
                                    $field_name = 'management_fee (%)';
                                }
                                if ($field_name == 'origination_fee') {
                                    $field_name = 'origination_fee (%)';
                                }
                                if ($field_name == 'factor_rate') {
                                    $value = round($value, 4);
                                }
                            }
                            if ($log->type == 'merchant_user') {
                                if ($field_name == 'complete_per') {
                                    $field_name = 'complete_percentage';
                                }
                                if ($field_name == 'paid_mgmnt_fee') {
                                    $field_name = 'paid_management_fee_amount';
                                }
                                if ($field_name == 'pre_paid') {
                                    $field_name = 'syndication_fee';
                                    $value = ($value != '') ? FFM::dollar($value) : '';
                                }
                            }
                            if ($field_name == 'under_writing_fee_per') {
                                $field_name = 'under_writing_fee_percentage';
                            }
                            if ($log->type == 'company_amount') {
                                if ($field_name == 'max_participant') {
                                    $value = ($value < 0) ? 0 : $value;
                                    $value = FFM::dollar(sprintf("%.2f", $value));
                                }
                            }
                            if ($log->type == 'investor' || $log->type == 'user') {
                                if ($field_name == 'notification_recurence') {
                                    $field_name = 'payout_frequency';
                                }
                                if ($field_name == 'auto_generation') {
                                    $field_name = 'auto_generate_syndicate_report';
                                }
                                if ($field_name == 'label') {
                                    $field_name = 'auto_invest_collected_amount_from';
                                }
                                if ($field_name == 'active_status') {
                                    $field_name = 'Enable / Disable';
                                }
                                if ($field_name == 'underwriting_status') {
                                    $value = ($value) ? $value : '';
                                }
                                if ($field_name == 'auto_syndicate_payment') {
                                    $field_name = 'auto_syndication_payment';
                                }
                            }
                            if ($field_name == 'underwriting_status') {
                                $value = ($value) ? $value : '';
                            }
                            if (strpos(strtolower($field_name), 'date') !== false) {
                                $toFormat = (strpos($value, ':') !== false) ? 'm/d/Y h:i:s A' : FFM::defaultDateFormat('db');
                                try {
                                    $value = Carbon::parse($value)->format($toFormat);
                                } catch (InvalidFormatException $e) {
                                }
                            }
                            if ($log->type == 'payment') {
                                if ($field_name == 'status') {
                                    $status = ParticipentPayment::statusOptions();
                                    if (array_key_exists($value, $status)) {
                                        $value = $status[$value];
                                    }
                                }
                            }
                            if ($log->type == 'ach_request') {
                                if ($field_name == 'ach_status') {
                                    $status = AchRequest::achStatusOptions();
                                    if ($value != '' && array_key_exists($value, $status)) {
                                        $value = $status[$value];
                                    }
                                }
                                if ($field_name == 'ach_request_status') {
                                    $status = AchRequest::achRequestStatusOptions();
                                    if ($value != '' && array_key_exists($value, $status)) {
                                        $value = $status[$value];
                                    }
                                }
                                if ($field_name == 'payment_status') {
                                    $status = AchRequest::paymentStatusOptions();
                                    if ($value != '' && array_key_exists($value, $status)) {
                                        $value = $status[$value];
                                    }
                                }
                            }
                            if ($field_name == 's_prepaid_status' && $value != '') {
                                $field_name = 'syndication_prepaid_status';
                                if ($value == 'On Amount') { $value = 'On Funding Amount';}
                            }
                            if ($log->type == 'user' || $log->type == 'investor' || $log->type == 'lender') {
                                if ($field_name == 'global_syndication' && $value != '') {
                                    $value = FFM::percent($value);
                                }
                            }
                            $value = (is_float($value)) ? round($value, 2) : $value;
                            if (is_array($value)) {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.json_encode($value).'<br>';
                            } elseif ($field_name == 'created_at' or $field_name == 'updated_at' or $field_name == 'deleted_at' or $field_name == 'paused_at' or $field_name == 'resumed_at' or $field_name == 'approved_at' or $field_name == 'generation_time') {
                                if ($type == 'merchant_note') {
                                    if (strtotime($value)) {
                                        $value = \FFM::datetime($value);
                                    }
                                    $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.$value.'<br>';
                                } else {
                                    $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.\FFM::datetime($value).'<br>';
                                }
                            } elseif ($field_name == 'login_date') {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.\FFM::datetime($value).'<br>';
                            } else {
                                $changes .= '<strong>'.UserActivityLog::prettyStatus($field_name).'</strong> : '.str_replace('.000000Z', '', $value).'<br>';
                            }
                        }
                        if ($type == 'investor_transaction' and $field_name == 'amount') {
                            if ($log->action == 'updated') {
                                $fromValue = '';
                                $toValue = '';
                                if (is_array($value)) {
                                    $fromValue = str_replace(['$', ','], ['', ''], $fromValue);
                                    $toValue = str_replace(['$', ','], ['', ''], $toValue);
                                    $fromValue = $value['from'];
                                    $toValue = $value['to'];
                                }
                                $amount = $toValue - $fromValue;
                            } elseif ($log->action == 'created') {
                                $value = str_replace(['$', ','], ['', ''], $value);
                                $amount = $value;
                            } elseif ($log->action == 'deleted') {
                                $value = str_replace(['$', ','], ['', ''], $value);
                                $amount = -$value;
                            }
                        }
                    }
                }
                $data = [0 => $log->user->name, 1 => FFM::datetime($log->created_at), 2 => $changes, 5 => UserActivityLog::prettyStatus($log->action)];
                if ($type == 'investor_transaction') {
                    $totalAmount += $amount;
                    $data[3] = FFM::dollar($amount);
                } else {
                    $data[4] = UserActivityLog::logTypePrettyStatus($log->type);
                }
                ksort($data);
                $rows[] = array_values($data);
            }
        }
        if ($type == 'investor_transaction') {
            $rows[] = ['<strong>Total</strong>', '', '', FFM::dollar($totalAmount), ''];
        }
        return ['sEcho' => 0, 'recordsTotal' => $total_records, 'recordsFiltered' => $total_records, 'aaData' => $rows];
    }
    private function appendParentPrefix($log)
    {
        $changes = '';
        if (($log->type == 'user' or $log->type == 'investor' or $log->type == 'lender' or $log->type == 'company' or $log->type == 'user_merchant')) {
            $entry = User::withTrashed()->where('id', $log->object_id)->first();
            $changes .= (($entry) ? '<strong>Name : </strong>'.$entry->name.'<br>' : '');
        } elseif ($log->type == 'payment') {
            if ($log->investor_id) {
                $user = User::withTrashed()->find($log->investor_id);
                $changes .= ($user) ? '<strong>Investor : </strong>'.$user->name.'<br>' : '';
            }
            if ($log->merchant_id) {
                $merchant = Merchant::withTrashed()->where('id', $log->merchant_id)->first();
                $changes .= ((isset($merchant)) ? '<strong>Merchant : </strong>'.$merchant->name.'<br>' : '');
            }
        } elseif ($log->type == 'merchant' or $log->type == 'merchant_note' or $log->type == 'merchant_ach_term' or $log->type == 'ach_payment' or $log->type == 'payment_pause' or $log->type == 'payment_resume' or $log->type == 'merchant_bank_account') {
            $object = Merchant::withTrashed()->where('id', $log->merchant_id)->first();
            $changes .= (($object) ? '<strong>Merchant : </strong>'.$object->name.'<br>' : '');
            $details = json_decode($log->detail, true);
            if (!$object and array_key_exists('merchant_id', $details)) {
                $object1 = Merchant::withTrashed()->where('id', $details['merchant_id'])->first();
                $changes .= (($object1) ? '<strong>Merchant : </strong>'.$object1->name.'<br>' : '');    
            }
        } elseif ($log->type == 'investor_transaction') {
            if (isset($log->investor_id)) {
                $user = User::withTrashed()->find($log->investor_id);
                $changes .= ($user) ? '<strong>Investor : </strong>'.$user->name.'<br>' : '';
            } else {
                $object = InvestorTransaction::where('investor_id', $log->investor_id)->first();
                $changes .= ($object && isset($object->investor)) ? '<strong>Investor : </strong>'.$object->investor->name.'<br>' : '';    
            }
        } elseif ($log->type == 'merchant_user') {
            $details = json_decode($log->detail, true);
            if (isset($log->merchant_id)) {
                $merchant = Merchant::withTrashed()->find($log->merchant_id);
                $changes .= (($merchant) ? '<strong>Merchant : </strong>'.$merchant->name.'<br>' : '');
            } elseif (array_key_exists('merchant_id', $details)) {
                $object1 = Merchant::withTrashed()->where('id', $details['merchant_id'])->first();
                $changes .= (($object1) ? '<strong>Merchant : </strong>'.$object1->name.'<br>' : '');    
            }
            if (isset($log->investor_id)) {
                $investor = User::withTrashed()->find($log->investor_id);
                $changes .= (($investor) ? '<strong>Investor : </strong>'.$investor->name.'<br>' : '');
            } elseif (array_key_exists('user_id', $details)) {
                $object1 = User::withTrashed()->where('id', $details['user_id'])->first();
                $changes .= (($object1) ? '<strong>Investor : </strong>'.$object1->name.'<br>' : '');    
            }
        } elseif ($log->type == 'velocity_fee') {
            $details = json_decode($log->detail, true);
            if (isset($log->merchant_id)) {
                $merchant = Merchant::withTrashed()->find($log->merchant_id);
                $changes .= (($merchant) ? '<strong>Merchant : </strong>'.$merchant->name.'<br>' : '');
            }
            if ($log->action == 'updated') {
                $object = VelocityFee::where('id', $log->object_id)->first();
                if (array_key_exists('order_id', $details)) {
                    if (is_array($details['order_id'])) {
                        $orderId = $details['order_id']['to'];
                    } else {
                        $orderId = $details['order_id'];
                    }
                    $changes .=  '<strong>Order ID : </strong>'.UserActivityLog::prettyStatus($orderId).'<br>';
                } else {
                    $changes .= (($object->order_id) ? '<strong>Order ID : </strong>'.UserActivityLog::prettyStatus($object->order_id).'<br>' : '');
                }
                if (array_key_exists('fee_type', $details)) {
                    $changes .= ((array_key_exists('fee_type', $details)) ? '<strong>Fee Type : </strong>'.UserActivityLog::prettyStatus($object->fee_type).'<br>' : '');
                } else {
                    $changes .= (($object && $object->fee_type) ? '<strong>Fee Type : </strong>'.UserActivityLog::prettyStatus($object->fee_type).'<br>' : '');
                }
            }
        } elseif ($log->type == 'investor_ach_request') {
            if (isset($log->investor_id)) {
                $investor = User::withTrashed()->find($log->investor_id);
                $changes .= (($investor) ? '<strong>Investor : </strong>'.$investor->name.'<br>' : '');
            }
            $object = InvestorAchRequest::where('id', $log->object_id)->first();
            if (strpos($log->detail, 'order_id') == false) {
                $changes .= ($object && $log->action == 'updated') ? '<strong>Order ID : </strong>'.$object->order_id.'<br>' : '';
            }
        } elseif ($log->type == 'user_details') {
            if (isset($log->investor_id)) {
                $investor = User::withTrashed()->find($log->investor_id);
                $changes .= (($investor) ? '<strong>Name : </strong>'.$investor->name.'<br>' : '');
            } else {
                $object = UserDetails::where('user_id', $log->investor_id)->first();
                $changes .= ($object && isset($object->userDetails)) ? '<strong>Name : </strong>'.$object->userDetails->name.'<br>' : '';    
            }
        } elseif ($log->type == 'bank_account') {
            if (isset($log->investor_id) and $log->action != 'updated') {
                $user = User::withTrashed()->where('id', $log->investor_id)->first();
                $changes .= ($user) ? '<strong>Name : </strong>'.$user->name.'<br>' : '';
            } else {
                $object = Bank::where('id', $log->object_id)->first();
                $changes .= ($object && isset($object->User)) ? '<strong>Name : </strong>'.$object->User->name.'<br>' : '';
            }
        } elseif ($log->type == 'login') {
            $object = User::withTrashed()->where('id', $log->object_id)->first();
            $changes .= ($object) ? '<strong>Name : </strong>'.$object->name.'<br>' : '';
        } elseif ($log->type == 'ach_request') {
            $object = AchRequest::where('id', $log->object_id)->first();
            if ($log->merchant_id) {
                $merchant = Merchant::withTrashed()->where('id', $log->merchant_id)->first();
            } elseif ($object) {
                $merchant = Merchant::withTrashed()->where('id', $object->merchant_id)->first();
            }
            $changes .= (($merchant) ? '<strong>Merchant : </strong>'.$merchant->name.'<br>' : '');
            if (strpos($log->detail, 'order_id') == false) {
                $changes .= ($object && $log->action == 'updated') ? '<strong>Order ID : </strong>'.$object->order_id.'<br>' : '';
            }
            if (strpos($log->detail, 'payment_date') == false) {
                $changes .= ($object && $log->action == 'updated') ? '<strong>Payment Date : </strong>'.FFM::date($object->payment_date).'<br>' : '';
            }
        } elseif ($log->type == 'company_amount') {
            $merchant = Merchant::withTrashed()->where('id', $log->merchant_id)->first();
            $changes .= ($merchant) ? '<strong>Merchant : </strong>'.$merchant->name.'<br>' : '';
        }

        return $changes;
    }

    private function properFieldName($object, $field_name)
    {
        return $field_name;
    }
}