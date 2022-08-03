<?php

namespace App\Console\Commands\SingleUse;

use App\Helpers\PaymentReportHelper;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Merchant;
use App\MerchantUser;
use App\Models\ManualRTRBalanceLog;
use App\Models\Views\MerchantUserView;
use App\Settings;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RTRBalanceLog extends Command
{
    protected $signature = 'create:rtrbalance';
    protected $description = 'Create All the RTR Balance for all the investors';

    public function __construct(IMerchantRepository $merchant, IRoleRepository $role)
    {
        parent::__construct();
        $this->merchant = $merchant;
        $this->role = $role;
    }

    public function handle()
    {
        Auth::login(User::first());
        $sDate = null;
        $dates = $this->dates();
        $company = 58;
        $investors = $this->role->allInvestors();
        if ($company) {
            $investors = $investors->whereIn('company', $company);
        }
        $investors = $investors->pluck('name', 'id');
        foreach ($investors as $investor_id => $investor_name) {
            echo "\n";
            print_r($investor_name);
            $ManualRTRBalanceLogModel = new ManualRTRBalanceLog;
            foreach ($dates as $date) {
                echo "\n";
                print_r($date);
                $eDate = date('Y-m-d', strtotime($date));
                $result = $this->AnticipatedRTRCalculation($investor_id, $sDate, $eDate);
                // dd($result);
                // $rtr_balance=0;
                $single['user_id'] = $investor_id;
                $single['date'] = $eDate;
                // $List= Self::CalculationResultTotal($sDate, $eDate,$investor_id);
                try {
                    DB::beginTransaction();
                    // if(!empty($List)){
                    //     $rtr_balance=array_sum(array_column($List, 'participant_rtr_balance'));
                    // }
                    echo '../'.$result['anticipated_rtr'];
                    $single['rtr_balance'] = $result['anticipated_rtr'];
                    $single['rtr_balance_default'] = 0;
                    $single['details'] = json_encode($result);
                    $Duplicate = ManualRTRBalanceLog::where('user_id', $single['user_id'])->where('date', $single['date'])->first();
                    if (! $Duplicate) {
                        $return_result = $ManualRTRBalanceLogModel->selfCreate($single);
                    } else {
                        $return_result = $ManualRTRBalanceLogModel->selfUpdate($single, $Duplicate->id);
                    }
                    if ($return_result['result'] != 'success') {
                        echo '<pre>';
                        throw new \Exception($return_result['result'], 1);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    dd($e->getMessage());
                }
                // $rtr_balance_default=0;
                // $single['user_id']=$investor_id;
                // $single['date']=$eDate;
                // $List= Self::CalculationResultWithDefault($sDate, $eDate,$investor_id);
                // try {
                //     DB::beginTransaction();
                //     $Duplicate=ManualRTRBalanceLog::where('user_id',$single['user_id'])->where('date',$single['date'])->first();
                //     if(!empty($List)){
                //         $rtr_balance_default=array_sum(array_column($List, 'investor_rtr'));
                //     }
                //     $single['rtr_balance_default']=$rtr_balance_default;
                //     if(!$Duplicate){
                //         $return_result=$ManualRTRBalanceLogModel->selfCreate($single);
                //     } else{
                //         echo '../'.$rtr_balance_default;
                //         $single['rtr_balance_default']=$rtr_balance_default;
                //         $return_result=$ManualRTRBalanceLogModel->selfUpdate($single,$Duplicate->id);
                //     }
                //     if($return_result['result']!='success') { echo "<pre>"; throw new \Exception($return_result['result'], 1); }
                //     DB::commit();
                // } catch (\Exception $e) {
                //     DB::rollback();
                //     dd($e->getMessage());
                // }
            }
        }

        return 0;
    }

    public function dates()
    {
        $dates = [];
        for ($y = 2017; $y <= date('Y'); $y++) {
            for ($m = 1; $m <= 12; $m++) {
                $m = sprintf('%02d', $m);
                $date = date('Y-m-t', strtotime(date($y.'-'.$m.'-01')));
                if ($date <= date('Y-m-d')) {
                    $dates[] = $date;
                }
                if ($y.'-'.$m == date('Y-m')) {
                    $date = date('Y-m-d', strtotime(date($y.'-'.$m.'-d')));
                    $dates[] = $date;
                }
            }
        }

        return $dates;
    }

    public function CalculationResultWithDefault($sDate, $eDate, $investor_id)
    {
        $sub_status = [4, 22];
        $lenders = $filter_merchants = null;
        $filter_investors = [$investor_id];
        $userId = Auth::user()->id;
        $merchants_arr = $this->merchant->search_default_data($filter_merchants, $filter_investors, $lenders, $sDate, $eDate, $userId, $sub_status);

        return $merchants_arr['merchants']->get()->toArray();
        // $date_type=false;
        // if ($date_type == 'true') {
        //     if ($stime != '') {
        //         $sDate = $sDate.' '.$stime;
        //     }
        //     if ($etime != '') {
        //         $eDate = $eDate.' '.$etime;
        //     }
        // }
        // $paymentQuery = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
        // ->where('merchant_user.user_id', $investor_id)
        // ->whereIn('sub_status_id', [4,22])
        // ->where('last_status_updated_date','<=', $eDate)
        // ->join('participent_payments', 'participent_payments.merchant_id', 'merchants.id')
        // ->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
        // if ($date_type == 'true') {
        //     if ($sDate != null) {
        //         $paymentQuery->where('participent_payments.created_at', '>=', $sDate);
        //     }
        //     if ($eDate != null) {
        //         $paymentQuery->where('participent_payments.created_at', '<=', $eDate);
        //     }
        // } else {
        //     if ($sDate != null) {
        //         $paymentQuery->where('participent_payments.payment_date', '>=', $sDate);
        //     }
        //     if ($eDate != null) {
        //         $paymentQuery->where('participent_payments.payment_date', '<=', $eDate);
        //     }
        // }
        // $paymentQuery->where('payment_investors.user_id', $investor_id);
        // $paymentQuery->where('merchants.active_status', 1);
        // $data = $paymentQuery->groupBy('merchants.id')
        // ->select( DB::raw('IF(((actual_paid_participant_ishare-invest_rtr)*(1-merchant_user.mgmnt_fee/100))<=0,(invest_rtr-merchant_user.invest_rtr*merchant_user.mgmnt_fee/100)-SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee),0) as participant_rtr_balance'))
        // ->get()
        // ->toArray();
        return $data;
    }

    public function CalculationResultTotal($sDate, $eDate, $investor_id)
    {
        $date_type = false;
        if ($date_type == 'true') {
            if ($stime != '') {
                $sDate = $sDate.' '.$stime;
            }
            if ($etime != '') {
                $eDate = $eDate.' '.$etime;
            }
        }
        $paymentQuery = Merchant::join('merchant_user', 'merchant_user.merchant_id', 'merchants.id')
        ->where('merchant_user.user_id', $investor_id)
        ->join('participent_payments', 'participent_payments.merchant_id', 'merchants.id')
        ->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id');
        if ($date_type == 'true') {
            if ($sDate != null) {
                $paymentQuery->where('participent_payments.created_at', '>=', $sDate);
            }
            if ($eDate != null) {
                $paymentQuery->where('participent_payments.created_at', '<=', $eDate);
            }
        } else {
            if ($sDate != null) {
                $paymentQuery->where('participent_payments.created_at', '>=', $sDate);
            }
            if ($eDate != null) {
                $paymentQuery->where('participent_payments.created_at', '<=', $eDate);
            }
        }
        $paymentQuery->where('payment_investors.user_id', $investor_id);
        $paymentQuery->where('merchants.active_status', 1);
        $data = $paymentQuery->groupBy('merchants.id')
        ->select(DB::raw('IF(((actual_paid_participant_ishare-invest_rtr)*(1-merchant_user.mgmnt_fee/100))<=0,(invest_rtr-merchant_user.invest_rtr*merchant_user.mgmnt_fee/100)-SUM(payment_investors.actual_participant_share-payment_investors.mgmnt_fee),0) as participant_rtr_balance'))
        ->get()
        ->toArray();

        return $data;
    }

    public function AnticipatedRTRCalculation($investor_id, $from, $to)
    {
        $MerchantUserView = MerchantUserView::whereinvestor_id($investor_id)->first();
        $overpayment = $MerchantUserView->getCarryForwardProcedure($from, $to);
        $total_rtr = $MerchantUserView->getRTRProcedure($from, $to);
        $mgmnt_fee = $MerchantUserView->getMgmntFeeProcedure($from, $to);
        $ctd = $MerchantUserView->getCTDProcedure($from, $to);
        $anticipated_rtr = ($total_rtr - $mgmnt_fee) - ($ctd - $overpayment);

        return [
            'anticipated_rtr'=>round($anticipated_rtr, 4),
            'total_rtr'      =>round($total_rtr, 4),
            'overpayment'    =>round($overpayment, 4),
            'mgmnt_fee'      =>round($mgmnt_fee, 4),
            'ctd'            =>round($ctd, 4),
        ];
    }
}
