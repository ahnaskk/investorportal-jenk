<?php
/**
* Created by Rahees.
* User: rahees_iocod
* Date: 02/02/2021
* Time: 7:15 PM.
*/

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\IReportRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Models\Views\ManualLiquidityLogView;
use App\Models\Views\ManualRTRBalanceLogView;
use App\Models\Views\Reports\FeeReportView;
use App\PaymentInvestors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportRepository implements IReportRepository
{
    public function __construct(IRoleRepository $role)
    {
        $this->role = $role;
        $this->loggedUser = Auth::user();
    }

    public function getFeesReport($data = [])
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $table = new FeeReportView;
        if (isset($data['from_date'])) {
            $table = $table->where('date', '>=', $data['from_date']);
        }
        if (isset($data['to_date'])) {
            $table = $table->where('date', '<=', $data['to_date']);
        }
        if (isset($data['merchant_id'])) {
            $table = $table->where('merchant_id', $data['merchant_id']);
        }
        $totalCountfilterd = $table->count();
        $table = $table->select([
            'merchant_id',
            'Merchant',
            'date',
            'fee',
            'creator_id',
            'created_at',
        ]);
        $return['count'] = $table->count();
        $return['data'] = $table;

        return $return;
    }

    public function getInvestorLiquidityLog($data = [])
    {
        $table = new ManualLiquidityLogView;
        if (isset($data['from_date'])) {
            $table = $table->where('date', '>=', $data['from_date']);
        }
        if (isset($data['to_date'])) {
            $table = $table->where('date', '<=', $data['to_date']);
        }
        if (isset($data['company_id'])) {
            $table = $table->where('company_id', $data['company_id']);
        }
        if (isset($data['investor_id'])) {
            $table = $table->where('investor_id', $data['investor_id']);
        }
        $totalCountfilterd = $table->count();
        $table = $table->select([
            'company_id',
            'investor_id',
            'Investor',
            'Company',
            'date',
            'liquidity',
            'creator_id',
            'created_at',
        ]);
        $return['count'] = $table->count();
        $return['data'] = $table;

        return $return;
    }

    public function getInvestorRTRBalanceLog($data = [])
    {
        $table = new ManualRTRBalanceLogView;
        if (isset($data['from_date'])) {
            $table = $table->where('date', '>=', $data['from_date']);
        }
        if (isset($data['to_date'])) {
            $table = $table->where('date', '<=', $data['to_date']);
        }
        if (isset($data['company_id'])) {
            $table = $table->where('company_id', $data['company_id']);
        }
        if (isset($data['investor_id'])) {
            $table = $table->where('investor_id', $data['investor_id']);
        }
        $totalCountfilterd = $table->count();
        $table = $table->select([
            'company_id',
            'investor_id',
            'Investor',
            'Company',
            'date',
            'rtr_balance',
            'rtr_balance_default',
            'total',
            'details',
            'created_at',
            'creator_id',
        ]);
        $return['count'] = $table->count();
        $return['data'] = $table;

        return $return;
    }
}
