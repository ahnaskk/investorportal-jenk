<?php

namespace App\Helpers;

use App\Bank;
use App\Models\ActumDeclineCode;
use App\Models\InvestorAchRequest;
use App\User;
use App\UserDetails;
use Illuminate\Support\Facades\Http;

class ActumRequest
{
    public static function sendDebitRequest($data)
    {
        try {
            $params = ['parent_id' => config('settings.actum_parent_id_investor_to_velocity'), 'sub_id' => config('settings.actum_sub_id_investor_to_velocity'), 'pmt_type' => 'chk', 'chk_acct' => $data['chk_acct'] ?? '', 'chk_aba' => $data['chk_aba'] ?? '', 'custname' => $data['custname'] ?? '', 'custphone' => $data['custphone'] ?? '', 'initial_amount' => $data['initial_amount'] ?? '', 'billing_cycle' => '-1', 'merordernumber' => 'D-'.md5(uniqid(rand(), true))];
            $return = self::api_link($params);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public static function sendSameDayDebitRequest($data)
    {
        try {
            $params = ['parent_id' => config('settings.actum_parent_id_investor_to_velocity'), 'sub_id' => config('settings.actum_sub_id_investor_to_velocity'), 'pmt_type' => 'chk', 'chk_acct' => $data['chk_acct'] ?? '', 'chk_aba' => $data['chk_aba'] ?? '', 'custname' => $data['custname'] ?? '', 'custphone' => $data['custphone'] ?? '', 'initial_amount' => $data['initial_amount'] ?? '', 'billing_cycle' => '-1', 'merordernumber' => 'DS-'.md5(uniqid(rand(), true)), 'trans_modifier' => 'S'];
            $return = self::api_link($params);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public static function sendCreditRequest($data)
    {
        try {
            $params = ['parent_id' => config('settings.actum_parent_id_velocity_to_investor'), 'sub_id' => config('settings.actum_sub_id_velocity_to_investor'), 'pmt_type' => 'chk', 'chk_acct' => $data['chk_acct'] ?? '', 'chk_aba' => $data['chk_aba'] ?? '', 'custname' => $data['custname'] ?? '', 'custphone' => $data['custphone'] ?? '', 'initial_amount' => $data['initial_amount'] ?? '', 'billing_cycle' => '-1', 'merordernumber' => 'C-'.md5(uniqid(rand(), true)), 'action_code' => 'P', 'creditflag' => '1'];
            $return = self::api_link($params);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public static function sendSameDayCreditRequest($data)
    {
        try {
            $params = ['parent_id' => config('settings.actum_parent_id_velocity_to_investor'), 'sub_id' => config('settings.actum_sub_id_velocity_to_investor'), 'pmt_type' => 'chk', 'chk_acct' => $data['chk_acct'] ?? '', 'chk_aba' => $data['chk_aba'] ?? '', 'custname' => $data['custname'] ?? '', 'custphone' => $data['custphone'] ?? '', 'initial_amount' => $data['initial_amount'] ?? '', 'billing_cycle' => '-1', 'merordernumber' => 'CS-'.md5(uniqid(rand(), true)), 'trans_modifier' => 'S', 'action_code' => 'P', 'creditflag' => '1'];
            $return = self::api_link($params);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public static function api_link($params)
    {
        $url = 'https://join.actumprocessing.com/cgi-bin/dbs/man_trans.cgi';
        $request = Http::asForm()->post($url, $params);
        $responses = $request->body();
        $responses = preg_split('/$\R?^/m', $responses);
        $return = [];
        foreach ($responses as $response) {
            $single = explode('=', $response);
            $return[$single[0]] = $single[1];
        }

        return $return;
    }

    public function statusCheck($order_id, $type = 'from_investor')
    {
        try {
            if ($type == 'to_investor') {
                $params = ['username' => config('settings.actum_username_velocity_to_investor'), 'password' => config('settings.actum_password_velocity_to_investor'), 'action_code' => 'A', 'order_id' => $order_id];
            } else {
                $params = ['username' => config('settings.actum_username_investor_to_velocity'), 'password' => config('settings.actum_password_investor_to_velocity'), 'action_code' => 'A', 'order_id' => $order_id];
            }
            $return = $this->api_link($params);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public function RequestHandler($data)
    {
        try {
            $return['InvestorAchRequest'] = 'empty';
            if (! isset($data['investor_id'])) {
                throw new \Exception('Investor Id Not Found', 1);
            }
            $Investor = User::find($data['investor_id']);
            if (! $Investor) {
                throw new \Exception('Investor Not Found For '.$data['investor_id'], 1);
            }
            if (empty($data['bank_id'])) {
                if (in_array($data['transaction_type'], ['credit', 'same_day_credit'])) {
                    $BankDetail = Bank::whereinvestor_id($data['investor_id']);
                    $BankDetail->wheredefault_credit(1);
                    $BankDetail = $BankDetail->first();
                } else {
                    $BankDetail = Bank::whereinvestor_id($data['investor_id']);
                    $BankDetail->wheredefault_debit(1);
                    $BankDetail = $BankDetail->first();
                }
                if (! $BankDetail) {
                    throw new \Exception('Empty Bank List For '.$Investor->name, 1);
                }
                $data['bank_id'] = $BankDetail->id;
            }
            if ($data['amount'] <= 0) {
                throw new \Exception('Amount Is Required', 1);
            }
            $BankDetail = Bank::find($data['bank_id']);
            if (! $BankDetail) {
                throw new \Exception('No Bank Account Found For '.$data['bank_id'], 1);
            }
            $ActumRequestData = ['initial_amount' => number_format($data['amount'], 2, '.', ''), 'chk_acct' => $BankDetail->acc_number, 'chk_aba' => $BankDetail->routing, 'custname' => $BankDetail->account_holder_name ?? $Investor->name, 'custphone' => $Investor->cell_phone];
            $IARData = ['transaction_type' => $data['transaction_type'], 'transaction_method' => $data['transaction_method'], 'request_ip_address' => $data['request_ip_address'], 'investor_id' => $data['investor_id'], 'amount' => $data['amount'], 'bank_id' => $data['bank_id'], 'transaction_category' => $data['transaction_category']];
            $user_details = UserDetails::where('user_id', $data['investor_id'])->first();
            $user_details->liquidity = round($user_details->liquidity, 2);
            if (! isset($data['transaction_type'])) {
                throw new \Exception('Transaction Type Needed');
            }
            $pending_status = InvestorAchRequest::AchRequestStatusProcessing;
            $pending_amount = InvestorAchRequest::where('investor_id',$data['investor_id'])->where('ach_request_status',$pending_status)->sum('amount');
            switch ($data['transaction_type']) {
                case 'debit':
                    $return_result = $this->sendDebitRequest($ActumRequestData);
                    break;
                case 'same_day_debit':
                    $return_result = $this->sendSameDayDebitRequest($ActumRequestData);
                    break;
                case 'credit':
                    if ($user_details->liquidity < ($data['amount']+$pending_amount)) {
                        if($pending_amount>0){
                            throw new \Exception('Insufficient Balance.You Have Pending Requests With Total Amount Of '.\FFM::dollar($pending_amount).', You Need '.\FFM::dollar($pending_amount+$data['amount'] - round($user_details->liquidity, 2)).' More Amount for This Request.You Have Only '.\FFM::dollar($user_details->liquidity), 1);
                        }else{
                        throw new \Exception('Insufficient Balance. You Need '.\FFM::dollar($pending_amount+$data['amount'] - round($user_details->liquidity, 2)).' More Amount for This Request', 1);
                         }
                    }
                    $return_result = $this->sendCreditRequest($ActumRequestData);
                    break;
                case 'same_day_credit':
                    if ($user_details->liquidity < $data['amount']) {
                        throw new \Exception('Insufficient Balance. You Need '.\FFM::dollar($data['amount'] - round($user_details->liquidity, 2)).' More Amount for This Request', 1);
                    }
                    $return_result = $this->sendSameDayCreditRequest($ActumRequestData);
                    break;
            }
            if ($return_result['result'] != 'success') {
                throw new \Exception($return_result['result'], 1);
            }
            switch ($return_result['status']) {
                case 'Accepted':
                    $IARData['ach_request_status'] = InvestorAchRequest::AchRequestStatusProcessing;
                    $IARData['ach_status'] = InvestorAchRequest::AchStatusAccepted;
                    break;
                case 'declined':
                    $IARData['ach_request_status'] = InvestorAchRequest::AchRequestStatusDeclined;
                    $IARData['ach_status'] = InvestorAchRequest::AchStatusDeclined;
                    break;
                default:
                    $IARData['ach_request_status'] = InvestorAchRequest::AchRequestStatusProcessing;
                    $IARData['ach_status'] = InvestorAchRequest::AchStatusProcessing;
                    break;
            }
            $IARData['order_id'] = $return_result['order_id'] ?? '';
            $IARData['auth_code'] = $return_result['authcode'] ?? '';
            $IARData['reason'] = $return_result['reason'] ?? '';
            $IARData['status_response'] = $return_result['status'];
            $IARData['merordernumber'] = $return_result['merordernumber'];
            $IARData['response'] = json_encode($return_result);
            $InvestorAchRequestModel = new InvestorAchRequest;
            if (! empty($data['InvestorAchRequest_ID'])) {
                $InvestorAchRequestCheck = InvestorAchRequest::find($data['InvestorAchRequest_ID']);
                if (! $InvestorAchRequestCheck) {
                    throw new \Exception('Empty InvestorAchRequestCheck', 1);
                }
                $return_function = $InvestorAchRequestModel->selfUpdate($IARData, $data['InvestorAchRequest_ID']);
                if ($return_function['result'] != 'success') {
                    throw new \Exception($return_function['result'], 1);
                }
                $return['InvestorAchRequest'] = 'updated';
                $return['id'] = $data['InvestorAchRequest_ID'];
                goto skipIsert;
            }
            if ($IARData['order_id']) {
                $InvestorAchRequestCheck = InvestorAchRequest::whereorder_id($IARData['order_id'])->first();
                if ($InvestorAchRequestCheck) {
                    $return['id'] = $InvestorAchRequestCheck->id;
                    goto skipIsert;
                }
            }
            $return_function = $InvestorAchRequestModel->selfCreate($IARData);
            if ($return_function['result'] != 'success') {
                throw new \Exception($return_function['result'], 1);
            }
            $return['InvestorAchRequest'] = 'created';
            $return['id'] = $return_function['key'];
            skipIsert:
            if ($return_result['status'] != 'Accepted') {
                $authcode = $return_result['authcode'] ?? '';
                if ($authcode) {
                    $ActumDeclineCode = ActumDeclineCode::wherecode($authcode)->first();
                    if ($ActumDeclineCode) {
                        $authcode = $authcode.'-'.$ActumDeclineCode->definition;
                    }
                    throw new \Exception($return_result['reason'].'['.$authcode.']', 1);
                }
                throw new \Exception($return_result['reason'], 1);
            }
            $return['result'] = 'success';
        } catch (\Exception $e) {
            if ($e->getMessage() == 'Phone number required.') {
                $return['result'] = 'Cell Phone number required';
            } else {
                $return['result'] = $e->getMessage();
            }
        }

        return $return;
    }
}
