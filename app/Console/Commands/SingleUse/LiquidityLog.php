<?php

namespace App\Console\Commands\SingleUse;

use App\Library\Repository\Interfaces\IRoleRepository;
use App\Models\ManualLiquidityLog;
use App\Models\Views\MerchantUserView;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LiquidityLog extends Command
{
    protected $signature = 'create:liqduitylog';
    protected $description = 'Create All the Liquidity Log for all the investors';

    public function __construct(IRoleRepository $role)
    {
        parent::__construct();
        $this->role = $role;
    }

    public function handle()
    {
        Auth::login(User::first());
        $company = 58;
        $startDate = null;
        $subadmin = 'subadmin';
        $active = null;
        $liquidity = null;
        $dates = $this->dates();
        $investors = new MerchantUserView;
        if ($company) {
            $investors = $investors->where('company', $company);
        }
        $investors = $investors->pluck('Investor', 'investor_id');
        foreach ($investors as $investor_id => $Investor) {
            echo "\n";
            echo $Investor;
            foreach ($dates as $date) {
                try {
                    echo "\n";
                    echo $date.' --';
                    DB::beginTransaction();
                    $single['user_id'] = $investor_id;
                    $endDate = date('Y-m-d', strtotime($date));
                    $single['date'] = $endDate;
                    $returnData = $this->role->allInvestorsWithLiquidityModified($startDate, $endDate, $subadmin, $active, $company, $liquidity, $investor_id);
                    foreach ($returnData as $value) {
                        echo $single['liquidity'] = ($value['credit_amount'] + $value['ctd']) - ($value['total_funded'] + $value['commission_amount']) - $value['pre_paid'] - $value['under_writing_fee'] + $value['liquidity_adjuster'];
                        $ManualLiquidityLogModel = new ManualLiquidityLog;
                        $Duplicate = ManualLiquidityLog::where('user_id', $single['user_id'])->where('date', $single['date'])->first();
                        if (! $Duplicate) {
                            $return_result = $ManualLiquidityLogModel->selfCreate($single);
                        } else {
                            $return_result = $ManualLiquidityLogModel->selfUpdate($single, $Duplicate->id);
                        }
                        if ($return_result['result'] != 'success') {
                            echo '<pre>';
                            throw new \Exception($return_result['result'], 1);
                        }
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    dd($e->getMessage());
                }
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
}
