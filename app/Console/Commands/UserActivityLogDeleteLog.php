<?php

namespace App\Console\Commands;

use App\UserActivityLog;
use Illuminate\Console\Command;
use FFM;
class UserActivityLogDeleteLog extends Command
{

    // single purpose

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'useractivitylog:delete_unwanted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unwanted logs from user activity logs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2000000000M');
        $logs = UserActivityLog::all();
        foreach ($logs as $log) {
            try {
                $details = json_decode($log->detail, true);
            } catch (\ErrorException $e) {
                $details = [];
            }
            if ($log->action == 'updated') {
                $new_detail = [];
                foreach ($details as $key => $detail__) {
                    $omit = ['creator_id', 'id', 'company_id', 'last_status_updated_date', 'creator', 'merchant_permission', 'pmnts', 'term_id', 'is_fees', 'ach_request_id', 'model', 'model_id', 'auth_code', 'source_from', 'revert_id', 'position', 'annual_revenue', 'logo', 'liquidity_exclude', 'current_merchant_id', 'final_participant_share', 'old_factor_rate', 'paid_count', 'annualized_rate', 'last_rcode', 'updated_at', 'deleted_at', 'activation'];
                    if ($log->type != 'company') {
                        array_push($omit, 'company_status');
                    }
                    if ($log->type == 'investor_ach_request') {
                        array_push($omit, 'transaction_id');
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
                    if ($log->type == 'merchant') {
                        array_push($omit, 'liquidity', 'payment_pause_id', 'payment_pause');
                    }
                    if ($log->type == 'company_amount' && $detail__ && is_array($detail__) && array_key_exists('from', $detail__) && array_key_exists('to', $detail__)) {
                        if ($key == 'max_participant') {
                            $detail__['from'] = ($detail__['from'] <= 0) ? 0 : $detail__['from'];
                            $detail__['to'] = ($detail__['to'] <= 0) ? 0 : $detail__['to'];
                        }
                    }
                    if ($detail__ && is_array($detail__) && array_key_exists('from', $detail__) && array_key_exists('to', $detail__)) {
                        if ($detail__['from'] == $detail__['to']) { //not changed (same values, no need to show)
                            unset($details[$key]);
                        }
                    }
                    if (in_array($key, $omit)) {
                        unset($details[$key]);
                    }
                    $dollar_fields = ['amount', 'payment', 'commission_amount', 'funded', 'payment_amount', 'rtr', 'liquidity', 'under_writing_fee', 'credit_score', 'up_sell_commission', 'invest_rtr'];
                    if (is_array($detail__) && in_array($key, $dollar_fields)) {
                            $fromValue = FFM::dollar($detail__['from']);
                            $toValue = FFM::dollar($detail__['to']);
                            if ($fromValue == $toValue) { 
                                unset($details[$key]);
                            }
                    }
                    $percentageFields = ['commission', 'm_mgmnt_fee', 'management_fee', 'm_syndication_fee', 'syndication_fee', 'underwriting_fee', 'experian_intelliscore', 'experian_financial_score', 'origination_fee', 'commission_per', 'syndication_fee_percentage', 'mgmnt_fee', 'brokerage', 'complete_per', 'max_participant_fund_per', 'under_writing_fee_per', 'interest_rate', 'withhold_percentage', 'up_sell_commission_per', 'complete_percentage'];
                    if (is_array($detail__) && in_array($key, $percentageFields)) {
                        $fromValue = ($detail__['from'] != '') ? FFM::percent($detail__['from']) : '';
                        $toValue = ($detail__['to'] != '') ? FFM::percent($detail__['to']) : '';
                        if ($fromValue == $toValue) { unset($details[$key]);}
                    }
                    $new_detail = $details;
                }
                if (is_array($details)) {
                    $is_array = false;
                    foreach ($details as $detail) {
                        if (is_array($detail)) {
                            $is_array = true;
                        }
                    }
                    if (!$is_array) {
                        try {
                            $log->delete();
                        } catch (\Throwable $th) {
                            throw $th;
                        }
                    }
                }
                $log->detail = json_encode($new_detail);
                $log->save();
                echo "Removing unwanted entries from logs \n";
            }
            if ($log->detail == '[]') {
                try {
                    echo 'Deleting entry having ID.)'.$log->id;
                    $log->delete();
                } catch (\Throwable $th) {
                    throw $th;
                }
            }
        }
        $activity_log = UserActivityLog::onlyTrashed()->forceDelete();
        echo "Successfully deleted unwanted entries from logs \n";
        return 0;
    }
}
